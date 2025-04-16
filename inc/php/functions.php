<?
ini_set("display_errors", false);
ini_set('default_charset', 'UTF-8');

header('Content-type: text/html; charset=utf-8');

session_start();

//Maf300320: comunicação com o database Redis, para integração com outras plataformas (node|iot)
require_once 'redis/redis.php';

$pid = getmypid();
$keybspt = 'backtracescript_' . $_SESSION["SESSAO"]["USUARIO"] . "_" . basename($_SERVER["SCRIPT_FILENAME"]);
re::dis()->publish($keybspt, $pid);
re::dis()->set('_estado:' . $_SESSION["SESSAO"]["IDPESSOA"] . ':_monit:pid:id', $pid, ['ex' => 1200, 'nx']);
re::dis()->set('_estado:' . $_SESSION["SESSAO"]["IDPESSOA"] . ':_monit:pid:backt', $keybspt, ['ex' => 1200, 'nx']);
re::dis()->set('_monit:' . basename($_SERVER["SCRIPT_FILENAME"]) . ':' . $_SESSION["SESSAO"]["IDPESSOA"] . ':pid', $pid, ['ex' => 1200, 'nx']);
if (!empty($_SESSION["SESSAO"]["IDPESSOA"])) {
	re::dis()->set('_estado:' . $_SESSION["SESSAO"]["IDPESSOA"] . ':pessoa:lastseen', date("Y-m-d H:i:s"), ['ex' => 600]);
}

// CONTROLLERS: MAF: remover deste ponto: execucoes de negocio nao devem ocorrer em bibliotecas em comum
require_once(__DIR__ . "/../../form/controllers/_snippet_controller.php");

//Variáveis static de configuração para o Carbon
require_once("appvar.php");

//Jwt
require_once 'jwt/firebase/php-jwt/src/BeforeValidException.php';
require_once 'jwt/firebase/php-jwt/src/ExpiredException.php';
require_once 'jwt/firebase/php-jwt/src/SignatureInvalidException.php';
require_once 'jwt/firebase/php-jwt/src/JWT.php';

use \Firebase\JWT\JWT;

//Maf271021: comunicacao com o modulo redis timeseries, para dados estatisticos em tempo real (multiplos inserts)
//require_once 'redists/redists.php';
//Maf291021: comunicacao com o modulo timescaledb
//$tsdb = pg_connect("host=192.168.0.1 port=5432 dbname=postgres user=postgres password=admin");

//Armazenar as sessions no DB para permitir compartilhamento com outras aplicações. Ex: NodeJS
//require_once("session.php");//Está apresentando problemas de aramazenamento quando existem caracteres latinos


//Recupera os headers para tentativa de recuperacao de token
if (!defined('STDIN')) {
	$_headers = getallheaders();
}

//Merge de arquivos CSS|JS para evitar múltiplas requisições http
require("magicmin/class.magic-min.php");

//Biblioteca para conversão de/para Json
require_once("json/services_json.php");

//Instancia classe para formatacao em json
$JSON = new Services_JSON();

if ($_SESSION["SESSAO"]["LOGADO"]) {
	//Esta sessão será utilizada no banco de dados para recuperar o ID do usuário Logado
	$sess = d::b()->query("set @session_sessao_idusuario := " . $_SESSION["SESSAO"]["IDPESSOA"]);
	if (!$sess) {
		echo d::b()->error();
		die("validacesso: Erro ao ajustar variável de sessão no DB. IDPESSOA:[" . $_SESSION["SESSAO"]["IDPESSOA"] . "]");
	}
}

//maf130519: Possibilitar, atravÃ©s de parÃ¢metros, alterar a conexÃ£o da singleton, e executar comandos em servidores remotos
class d extends MySQLi
{
	private static $instance = null;
	public static $dbserver = "";
	public static $conectado = false;
	public static $transaction = false;
	public static $echoSql = false;

	public static $unixTimeInicio = null;
	public static $tInicio = 0;
	public static $qMaisLenta = 0;
	public static $tTotal = 0;
	public static $qtdExec = 0;
	public static $listQuerysLentas = array();
	/*private function __construct($host, $user, $password, $database, $port){
		parent::__construct($host, $user, $password, $database, $port);
	}*/

	public static function b($S = false, $port = false, $U = false, $P = false, $D = false)
	{

		//Verificar configuracao por parametros. A principio nao esta sendo permitido ao desenvolvedor alterar o database de conexao para um modulo que possui configuracao distribuida
		self::$dbserver = !empty($S) ? $S : _DBSERVER;
		//Verificar configuracoes de databases distribuidos
		$arrModuloPin = unserialize(_MODPIN);
		self::$dbserver = (!empty($_GET["_modulo"]) and !empty($arrModuloPin[$_GET["_modulo"]]["_DBSERVER"])) ? $arrModuloPin[$_GET["_modulo"]]["_DBSERVER"] : _DBSERVER;

		if (self::$instance == null or !empty($S)) {
			//considerar informaÃ§Ã£o de database no formato server[:port]
			$S =		self::$dbserver;
			$U =		!empty($U) ? $U : _DBUSER;
			$P =		!empty($P) ? $P : _DBPASS;
			$D =		!empty($D) ? $D : _DBAPP;
			$port =	!empty($port) ? $port : "3306";

			$arrserver = explode(":", $S);
			if (sizeof($arrserver) == 2 && !empty($arrserver[1])) {
				$server = $arrserver[0];
				$port = "" . $arrserver[1];
			} else {
				$server = $S;
			}

			self::$instance = new self($server, $U, $P, $D, $port);
			$connerror = mysqli_connect_error();
			$connerrorn = mysqli_connect_errno();
			if ($connerror) {
				if ($connerrorn == 1045) {
					echo ("Falha de credenciais durante conexão");
					self::$instance = null;
				}
				if ($connerrorn == "2002") {
					cbSetPostHeader("0", "alert");
					$merr = "Nosso banco de dados está sob manutenção.\nPor favor, aguarde!";
				} else {
					cbSetPostHeader("0", "erro");
					$merr = "O sistema está em manutenção preventiva.\nPor favor, aguarde o retorno.";
				}
			} else {
				self::$conectado = true;
			}
		}
		return self::$instance;
	}

	//maf300420: estender da superclasse o metodo query, para incluir informacoes da sessao na consulta, e assim melhorar rastreamento
	public function query($query, $resultmode = NULL)
	{
		global $_modulo;

		//Capturar variaveis para inspecao e log, separando conexoes http de execucao via shell
		if (php_sapi_name() == 'cli') {

			if (isset($_SERVER['TERM'])) {
				$logip = $_SERVER["SSH_CLIENT"] ? explode(" ", $_SERVER["SSH_CLIENT"])[0] : "shell_" . basename($_SERVER["SCRIPT_FILENAME"]);
				$logu = "Shell";
				$logm = "Shell";
				$_SESSION["SESSAO"]["USUARIO"] = basename($_SERVER["SCRIPT_FILENAME"]);
			} else {
				$logip = basename($_SERVER["SCRIPT_FILENAME"]);
				$logu = "Cron";
				$logm = "Cron";
				$_SESSION["SESSAO"]["USUARIO"] = basename($_SERVER["SCRIPT_FILENAME"]);
			}
		} else {
			//Se a sessions estiver vazia, concatenar o nome do script
			$logip = !empty($_SESSION["SESSAO"]["USUARIO"]) ? $_SERVER['REMOTE_ADDR'] : explode(".", basename($_SERVER["SCRIPT_FILENAME"]))[0] . "_" . $_SERVER['REMOTE_ADDR'];
			$logu = !empty($_SESSION["SESSAO"]["USUARIO"]) ? $_SESSION["SESSAO"]["USUARIO"] : "USR_VAZIO";
			$logm = !empty($_modulo) ? $_modulo : explode(".", basename($_SERVER["SCRIPT_FILENAME"]))[0];
		}

		$queryoriginal = $query;
		$susr = $_SESSION["SESSAO"]["USUARIO"] ? $_SESSION["SESSAO"]["USUARIO"] : "";
		$srast = "\n#usr:" . $susr;
		$srast .= $_modulo ? "\n#mod:" . $_modulo : "?";
		$pid = getmypid();
		$srast .= "\n#pid:" . $pid;
		$srast .= "\n#file:" . $_SERVER['SCRIPT_FILENAME'];

		$query = $srast . "\n" . $query;

		if (class_exists("re")) {
			//MAF: publicar o backtrace do php para um canal de inspecao, por usuario
			re::dis()->publish('backtrace_' . $susr, print_r(debug_backtrace(), true));
			re::dis()->publish('backtracesql_' . $susr, "\n\n" . $queryoriginal . ";");

			//MAF: publicar todas as queries executadas para o redis
			//re::dis()->publish('log_queries', substr($query,0,10000));
		}

		self::$unixTimeInicio = time();

		//Efetua echo na consulta, com tempo de inicio e termino
		if (self::$echoSql) {
			echo PHP_EOL . '###:echoSql:inicio:' . date("h:i:s");
			echo PHP_EOL . $query;
		}

		//Executa a consulta
		$p = parent::query($query);

		$tempoDecorrido = time() - self::$unixTimeInicio;

		if (self::$echoSql) {
			echo PHP_EOL . '###:echoSql:fim:' . date("h:i:s") . " " . $tempoDecorrido . " seg";
		}

		self::$tTotal += $tempoDecorrido;
		self::$qtdExec += 1;

		if (self::$qMaisLenta < $tempoDecorrido) {
			self::$qMaisLenta = $tempoDecorrido;
			//array_unshift(self::$listQuerysLentas,$query);
		}

		$arrTmp = self::$listQuerysLentas;

		if (class_exists("re")) {
			re::dis()->publish('backtracerep_' . $susr, print_r([
				"unixTimeInicio" => self::$unixTimeInicio,
				"tTotal" => self::$tTotal,
				"qtdExec" => self::$qtdExec,
				"qMaisLenta" => self::$qMaisLenta
				//"queryMaisLentas" => array_slice($arrTmp, 0, 3)
			], true));

			//MAF: Realtimedata para monitoramento (temporario)
			$rktime = time();
			//           $redkiq="_stat:mysql:querystat:".$_SERVER['REMOTE_ADDR'].":".$rktime.":iqueries";
			$redktg = "_stat:mysql:querystat:" . $logip;
			/*
try {
	ts::add($redktg, $tempoDecorrido, ["u"=>$logu,"m"=>$logm,"o"=>"cb"], 900000);
} catch (Throwable $t){
	syslog(LOG_WARNING, "Erro Time Series Redis".$t);
}*/

			//Maf: realtime para monitoramento            
			//$sql="insert into mysql (time,tgasto,host,usuario,modulo,ident)".
			//" values".
			//" (now(),".$tempoDecorrido.",'sislaudo','".$logu."','".$logm."','".$logip."')";
			//$result = pg_query($sql);

		}
		return $p;
	}

	public function startTransaction()
	{
		if (!self::$transaction) {
			$trans = $this->query("START TRANSACTION");
			if ($trans) {
				self::$transaction = true;
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	public function endCommit()
	{
		return $this->query("COMMIT");
	}

	public function sel($query, $resultmode = NULL)
	{
		return $this->query($query, $resultmode = NULL);
	}

	public function ins($cmd)
	{
		return $this->query($cmd);
	}

	public function upd($cmd)
	{
		return $this->query($cmd);
	}

	public function del($cmd)
	{
		return $this->query($cmd);
	}

	public function cmd($cmd)
	{
		return $this->query($cmd);
	}
}

function mysql_query($insql)
{
	//$res =  d::b()->query($insql) or die(mysqli_error(d::b()));
	$res =  d::b()->query($insql);
	return $res;
}

function mysql_error()
{
	$erro = (d::b()->error) ? d::b()->error : d::b()->connect_error;
	return $erro;
}

function mysql_fetch_array($inres, $intype = MYSQL_BOTH)
{

	$newtype = false;

	if ($intype == MYSQL_ASSOC) {
		$newtype = MYSQLI_ASSOC;
	} elseif ($intype == MYSQL_NUM) {
		$newtype = MYSQLI_NUM;
	} else {
		$newtype = MYSQLI_BOTH;
	}

	return mysqli_fetch_array($inres, $newtype);
}

function mysql_fetch_assoc($r)
{
	return mysqli_fetch_assoc($r);
}

function mysql_num_rows($res)
{
	return mysqli_num_rows($res);
}

function mysql_insert_id()
{
	return mysqli_insert_id(d::b());
}

function mysql_num_fields($r)
{
	return mysqli_num_fields($r);
}

function mysql_fetch_field($r)
{
	return mysqli_fetch_field($r);
}

function mysql_errno()
{
	return mysqli_errno(d::b());
}
function mysql_real_escape_string($s)
{
	return addslashes(d::b()->real_escape_string($s));
}

function mysql_close($s)
{
	return mysqli_close(d::b());
}

/*
 * Verifica se o diretório ROOT configurado existe, para includes
 */
function verificacarbonroot()
{
	if (!is_dir(_CARBON_ROOT)) {
		cbSetPostHeader("0", "erro");
		die("Carbon root não encontrado: \n" . _CARBON_ROOT . ". \nAjustar /inc/appvar.php");
	}
}

/*
 * Esta function verifica se as triggers para encriptacao de senha existem no DB
 */
function verificatriggermd5()
{

	$sql = "show triggers where `Trigger` in ('ins_checkpassword','upd_checkpassword') and `Table` = 'pessoa'";
	$qr = mysql_query($sql) or die("Erro ao consultar Triggers de MD5:" . mysql_error() . " <BR> SQL: " . $sql);
	$it = mysql_num_rows($qr);
	if ($it <> 2) {
		cbSetPostHeader("0", "erro");
		die("Erro fatal: Triggers (ins_checkpassword e upd_checkpassword) para encriptação de senha não existem do Database!");
	};
}

/*
 * Verificar configuração de tamanho mínimo de strings para indexação em Full Text Search
 */
function verificaConfFullTextSearch()
{

	$tamanhoMinimoStrings = 1;
	$stopwords = "OFF";

	$sql = "show variables like 'ft_min_word_len'";
	$qr = mysql_query($sql) or die("Erro ao recuperar parametro ft_min_word_len:" . mysql_error() . " <BR> SQL: " . $sql);
	$i = mysql_num_rows($qr);
	$v = mysql_fetch_assoc($qr);
	if ($i != 1) {
		cbSetPostHeader("0", "erro");
		die("Erro: Parâmetro ft_min_word_len deve ser configurado no Servidor MySql (my.ini): \nhttps://dev.mysql.com/doc/refman/5.1/en/server-system-variables.html#sysvar_ft_min_word_len");
	} else {
		if ($v["Value"] != $tamanhoMinimoStrings) {
			cbSetPostHeader("0", "erro");
			die("Erro: O parâmetro ft_min_word_len deve ser ajustado para ft_min_word_len=" . $tamanhoMinimoStrings . " no Servidor MySql");
		}
	}

	$sql = "show variables like 'innodb_ft_enable_stopword'";
	$qr = mysql_query($sql) or die("Erro ao recuperar parametro innodb_ft_enable_stopword:" . mysql_error() . " <BR> SQL: " . $sql);
	$i = mysql_num_rows($qr);
	$v = mysql_fetch_assoc($qr);
	if ($i != 1) {
		cbSetPostHeader("0", "erro");
		die("Erro: Parâmetro innodb_ft_enable_stopword deve ser configurado no Servidor MySql (my.ini): \nhttps://dev.mysql.com/doc/refman/5.1/en/server-system-variables.html#sysvar_innodb_ft_enable_stopword");
	} else {
		if ($v["Value"] != $stopwords) {
			cbSetPostHeader("0", "erro");
			die("Erro: O parâmetro innodb_ft_enable_stopword deve ser ajustado para innodb_ft_enable_stopword=" . $stopwords . " no Servidor MySql");
		}
	}
}

/*
 * Verificar configuração de backslashes
 */
function verificaBackslashes()
{

	$sql = "show variables like 'sql_mode'";
	$qr = mysql_query($sql) or die("Erro ao recuperar parametro sql_mode:" . mysql_error() . "\n SQL: " . $sql);
	$i = mysql_num_rows($qr);
	$v = mysql_fetch_assoc($qr);

	if (strpos($v["Value"], "NO_BACKSLASH_ESCAPES") !== false) {
		cbSetPostHeader("0", "erro");
		die("Erro: O parâmetro sql_mode não deve conter a diretriz NO_BACKSLASH_ESCAPES no Servidor MySql");
	}
}

/*
 * Armazena na memoria RAM informacoes de alarme, para geracao de grafico ou acompanhamento de tentativas de invasao, etc
 */
function alarmeInit()
{

	logBinario(false);

	$sa = "CREATE TABLE IF NOT EXISTS " . _DBCARBON . ".\$alarme (
		alarme varchar(1) default '',
		tipo varchar(45),
		msg varchar(45) default null,
		log varchar(2000) default null,
		ip varchar(100) DEFAULT NULL,
		uri varchar(2000),
		browser varchar(500),
		criadoem timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		KEY ip using HASH (ip),
		KEY criadoem using BTREE (criadoem),
		KEY alarme_ip using BTREE (alarme, ip)
	) ENGINE=memory DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC;";

	$qr = d::b()->query($sa) or die(mysqli_error(d::b())); //(;

	logBinario(true);
}

/*
 * Verificar itens na tabela de alarme, conforme configurações
 */
function alarmeCheck($tipo, $minutosBloqueio = _ALARME_MINUTOS_BLOQUEIO_LOGIN)
{

	if (empty($tipo)) {
		return false;
	}

	$sc = "select count(*) as i
	from " . _DBCARBON . ".\$alarme force index(ip)
	where alarme = 'Y' 
	and tipo = '" . $tipo . "'
	and ip = '" . $_SERVER['REMOTE_ADDR'] . "'
	and criadoem > date_add(now(), interval -" . $minutosBloqueio . " minute)";

	$rc = d::b()->query($sc) or die("Erro ao realizar checagem do alarme: " . $tipo);

	$row = mysqli_fetch_assoc($rc);

	return $row["i"];
}

/*
 * Armazena em tabela memory alertas diversos do sistema
 */
function alarmeSet($alarme, $tipo, $msg = null, $log = null)
{

	$msg = $msg == null ? "null" : "'" . d::b()->real_escape_string($msg) . "'";
	$log = $log == null ? "null" : "'" . d::b()->real_escape_string($log) . "'";

	//Envia para o Redis para contagem (incrementa) e consulta posterior, ajustando o valor de expiracao
	$rkey = "_alarme:" . $alarme . ":" . $tipo . ":" . $_SERVER['REMOTE_ADDR'];
	re::dis()->incr($rkey);
	re::dis()->expire("_alarme:" . $alarme . ":" . $tipo . ":" . $_SERVER['REMOTE_ADDR'], 86400);

	logBinario(false);
	$sal = "insert into " . _DBCARBON . ".\$alarme (alarme,tipo,msg,log,ip,uri,browser) 
		values ('" . $alarme . "','" . $tipo . "'," . $msg . "," . $log . ",'" . $_SERVER['REMOTE_ADDR'] . "','" . $_SERVER['QUERY_STRING'] . "','" . $_SERVER['HTTP_USER_AGENT'] . "')";

	//        if($_POST["POST"]=="marcelos"){
	//                echo("<!-- ".$sal." -->");
	//        }


	d::b()->query($sal); //or die("Falha ao gerar alarme");//.d::b()->error);
	logBinario(true);


	if ($tipo == 'login') {
		setUserBlock($alarme, $log);
	}
}


/*
 * Insere alertas de login mal sucedidas na tabela
 */

function setUserBlock($alarme, $user)
{

	//VERIFICA E ATUALIZA O NÚMERO DE TENTATIVAS DE LOGIN
	$userCheck = "SELECT * FROM _bloqueiousuario WHERE ip='" . $_SERVER['REMOTE_ADDR'] . "' AND status = 'A'";
	$rUser = d::b()->query($userCheck) or die("Erro ao verificar se o ip esta bloqueado " . $userCheck);

	/*
		-------------------- STATUS ------------------------------
		* A = Alerta -> IP ainda não foi bloqueado
		* B = Bloqueado -> IP foi bloqueado
		* L = Liberado -> IP foi bloqueado e liberado

	*/

	$numr = mysqli_num_rows($rUser);
	$rowUserCheck = mysqli_fetch_assoc($rUser);

	if ($alarme == 'Y') {

		if ($numr == 0) { //SE NÃO HOUVER REGISTRO DO IP NA TABELA FAZ UM INSERT

			$userBlock = "INSERT INTO _bloqueiousuario (`status`, `usuario`, `count`, `ip`) 
			VALUES ('A'," . $user . ", '1' ,'" . $_SERVER['REMOTE_ADDR'] . "')";

			d::b()->query($userBlock) or die("Erro ao inserir usuario  bloqueado");
		} else if ($rowUserCheck['count'] >= _ALARME_QTD_TENTATIVAS_LOGIN) { // SE O NÚMERO DE TENTATIVAS DEFINIDO FOR ULTRAPASSADO, ALTERA O STATUS PARA BLOQUEADO E DA UM DIE

			$updUserBlock = "UPDATE _bloqueiousuario SET `usuario` = " . $user . " ,`status` = 'B'  WHERE ip='" . $_SERVER['REMOTE_ADDR'] . "' and status = 'A'";
			d::b()->query($updUserBlock) or die("Erro ao atualizar count do usuario  bloqueado");

			cbSetPostHeader("0", "erro");
			die("#1 Seu ip está bloqueado: " . $_SERVER['REMOTE_ADDR'] . "\nEntre em contato com o Administrador");
		} else if ($rowUserCheck['count'] < _ALARME_QTD_TENTATIVAS_LOGIN) { // DA UM UPDATE NA CONTAGEM DE TENTATIVAS SE O ALERTA FOR 'Y'

			$updUserBlock = "UPDATE _bloqueiousuario SET `usuario` = " . $user . ", `count` = (count+1) WHERE ip='" . $_SERVER['REMOTE_ADDR'] . "' and status = 'A'";
			d::b()->query($updUserBlock) or die("Erro ao atualizar count do usuario  bloqueado");
		}
	} else if ($alarme == 'N' && $numr > 0) { // DEFINE A CONTAGEM DE TENTATIVAS PARA 0 AO LOGIN COM SUCESSO DO IP

		$updUserBlock = "UPDATE _bloqueiousuario SET `usuario` = " . $user . ", `count` = 0 WHERE ip='" . $_SERVER['REMOTE_ADDR'] . "' and status = 'A'";
		d::b()->query($updUserBlock) or die("Erro ao atualizar count do usuario  bloqueado");
	}
}

/*
 * Verifica se o usuário está bloqueado na tabela _bloqueiousuario
 */
function checkUserBlock()
{

	$checkBlock = "SELECT 1 FROM _bloqueiousuario WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "' AND status = 'B'";

	$rc = d::b()->query($checkBlock) or die("Erro ao verificar se o ip esta bloqueado " . $checkBlock);

	$numr = mysqli_num_rows($rc);

	return ($numr);
}

/*
 * Testa URLs relativas para execução do Form de Login
 */
function returllogin()
{

	if (file_exists("form/_login.php")) {
		return "form/_login.php";
	} elseif (file_exists("../form/_login.php")) {
		return "../form/_login.php";
	} else {
		return "form/_login.php";
	}
}

function getUsr($inusr)
{ //@487013 - MULTI EMPRESA
	$sql = "SELECT 
				p.usuario
				,p.nome
				,p.nomecurto
				,p.senha
				,p.tipoauth
				,p.idpessoa
				,p.idempresa
				,p.idlp
				,p.idtipopessoa
				,l.flagobrigatoriocontato
				,p.email
				,p.ramalfixo
				,p.webmailusuario
				,p.idarquivoavatar
				,f.fullaccess
				,l.customcss
				,if(length(ifnull(ip.idimpessoa,''))>0,'Y','N') as permissaochat
				,p.acesso
				,e.habilitarmatriz
				,p.idempresa as idempresaDefault
				,e.idempresa as idmatriz
				,p.reppontoexterno
				,p.expiraem
				,if((select count(*)
					from reppessoa rp 
					join rep r on r.idrep=rp.idrep and r.tipo='REP-P'
					join empresa e on e.idempresa=r.idempresa and e.status='ATIVO'
					where rp.idpessoa=p.idpessoa) > 0,'Y','N') as pontoweb
			FROM
				" . _DBAPP . ".pessoa p
				LEFT JOIN " . _DBCARBON . "._lp l ON (l.idlp=p.idlp)
				LEFT JOIN  " . _DBCARBON . "._carbonadm f ON (p.usuario = f.usuario)
				LEFT JOIN  " . _DBAPP . ".impessoa ip ON ip.idpessoa = p.idpessoa
				LEFT JOIN  " . _DBAPP . ".empresa e on e.idempresa = p.idempresa
			WHERE
				p.usuario = '" . mysql_real_escape_string(stripslashes($inusr)) . "' AND p.status in ('ATIVO','PENDENTE')";
	$res = d::b()->query($sql) or die("Falha ao pesquisar usuario"); //nao mostrar o erro . mysql_error() . "<p>SQL: $sql");

	if ($res->num_rows !== 1) {
		alarmeSet('Y', 'login', 'Usuário Inválido', $inusr);
		cbSetPostHeader("0", "login");
		header("HTTP/1.1 401 Usuário ou Senha inválidos!");
		die("Usuário ou Senha inválidos!");
	}
	$row = mysql_fetch_assoc($res);

	$data = date("Y-m-d H:i:s");
	$expiraem = $row["expiraem"];
	if ($expiraem < $data && ($expiraem != "0000-00-00 00:00:00" && !empty($expiraem)) && $row["idtipopessoa"] == 3) {
		// alarmeSet('Y','login','Usuário Expirado',$inusr);
		cbSetPostHeader("0", "login");
		header("HTTP/1.1 401 Usuário expirado, favor entre em contato para reativá-lo!");
		die("Usuário Expirado, favor entre em contato para reativá-lo!");
	}



	return $row;
}

/**
 * MAF: Melhoria na autenticacao: bcrypt+salt+pepper
 */
function verificasenha($rowp, $pwd)
{
	header('X-vs1: ok');
	if (!defined('_PEPPER')) {
		header('X-vs2: nok');
		return false;
	} else {
		if ($rowp["tipoauth"] == "bsp") {
			header('X-vs3: ok');
			return password_verify($pwd . _PEPPER, $rowp["senha"]);
		} else {
			header('X-vs4: ok');
			return $rowp["senha"] == md5(stripslashes($pwd));
		}
	}
}
function senha_hash($insenha)
{
	return password_hash($insenha . _PEPPER, PASSWORD_BCRYPT);
}
/*
 * A função de Login pode ser executada a partir de qualquer tela, inclusive submit.php
 */
function logincarbon($inusr, $inpwd)
{
	global $_headers, $JSON;

	verificatriggermd5();

	verificacarbonroot();

	verificaConfFullTextSearch();

	verificaBackslashes();

	cbSetPostHeader("0", "alert");

	//Inicializa estrutura para armazenar alarmes globais
	alarmeInit();

	//Verifica se o ip está bloqueado 
	if (checkUserBlock() > 0) {
		cbSetPostHeader("0", "erro");
		die("#2 Seu ip está bloqueado: " . $_SERVER['REMOTE_ADDR'] . "\nEntre em contato com o Administrador");
	}

	$row = getUsr($inusr);

	if (verificaSuperUsuario($inusr)) {
		$_SESSION["SESSAO"]["SUPERUSUARIO"] = true;
		cbSetPostHeader("1", "home");
	}

	/*
	* Executa POS_LOGIN_ACESSO para verificar se o usuário registrou o ponto ou se tem acesso para acessar externamente
	*/

	if ($row['idtipopessoa'] == 1 && !$_SESSION["SESSAO"]["SUPERUSUARIO"]) {
		$arq_poslogin_acessso = "../eventcode/modulo/pos__login_acesso.php";

		if (file_exists($arq_poslogin_acessso)) {
			include_once($arq_poslogin_acessso);
		}
	}

	//Verifica se o usuário enviado vai ser trocado por motivo de super usuário

	if (verificasenha($row, $inpwd) or $_SESSION["SESSAO"]["SUPERUSUARIO"]) {

		$_SESSION["SESSAO"]["IDLP"] = (empty($row["idlp"])) ? $row["idlpdefault"] : $row["idlp"];
		$_SESSION["SESSAO"]["USUARIO"] = $inusr;
		$_SESSION["SESSAO"]["IDPESSOA"] = $row["idpessoa"];
		$_SESSION["SESSAO"]["IDTIPOPESSOA"] = $row["idtipopessoa"];

		if ($row["idtipopessoa"]  == 1) {
			$matrizPermissoes = buscarMatrizPermissoesPorIdPessoa($_SESSION["SESSAO"]["IDPESSOA"]);
			$_SESSION["SESSAO"]["MATRIZPERMISSOES"] = empty($matrizPermissoes) ? $row["idempresa"] : $matrizPermissoes;
		} else {
			$_SESSION["SESSAO"]["MATRIZPERMISSOES"] = $row["idempresa"];
		}

		$_SESSION["SESSAO"]["IDEMPRESA"] 		= $row["idempresa"];
		$_SESSION["SESSAO"]["IDEMPRESAMATRIZ"] 	= $row["idmatriz"];
		$_SESSION["SESSAO"]["HABILITARMATRIZ"] 	= $row["habilitarmatriz"];

		/*
		 * Modulos disponiveis para o usuario. Caso exista falha na configuração do usuário, não prosseguir com a montagem da Sessao ou token
		 * O array resultante será utilizado no POST para restricao de escrita nas tabelas
		 */
		$arrModulosUsuario = retArrayModulosUsuario();
		$arrModulosPorIdPessoa = arrayModulosPorIdPessoa($_SESSION["SESSAO"]["IDPESSOA"]);

		$imagemFuncionario = buscarImagemFuncionario($_SESSION["SESSAO"]["IDPESSOA"]);
		/*
		 * Cria token JWT e devolve ao cliente atavés do header Http
		 */
		setHdrToken(
			$row["idempresa"],
			$row["idpessoa"],
			$row["idtipopessoa"],
			$row["usuario"],
			$row["nome"],
			$row["ramalfixo"],
			$row["idarquivoavatar"],
			$imagemFuncionario,
			$row["permissaochat"]
		);

		/*
		 * Dados da Conta
		 */
		//print_r($_SESSION);
		$_SESSION["SESSAO"]["LOGADO"] = true;
		$_SESSION["SESSAO"]["USUARIO"] = $inusr;
		$_SESSION["SESSAO"]["NOME"] = $row["nome"];
		$_SESSION["SESSAO"]["NOMECURTO"] = $row["nomecurto"];
		$_SESSION["SESSAO"]["EMAIL"] = $row["email"];
		$_SESSION["SESSAO"]["RAMALFIXO"] = $row["ramalfixo"];
		$_SESSION["SESSAO"]["IDTIPOPESSOA"] = $row["idtipopessoa"];
		$_SESSION["SESSAO"]["FULLACCESS"] = $row["fullaccess"];
		$_SESSION["SESSAO"]["CUSTOMCSS"] = $row["customcss"];
		$_SESSION["SESSAO"]["WEBMAILUSUARIO"] = $row["webmailusuario"];
		$_SESSION["SESSAO"]["IDARQUIVOAVATAR"] = $row["idarquivoavatar"];
		$_SESSION["SESSAO"]["PERMISSAOCHAT"] = $row["permissaochat"];
		$_SESSION["SESSAO"]["SENHA"] = $row["senha"];
		$_SESSION["SESSAO"]["JWT"] = $jwt;
		$_SESSION["SESSAO"]["ACESSO"] = $row["acesso"];
		$_SESSION["SESSAO"]["PONTOWEB"] = $row["pontoweb"];
		$_SESSION["SESSAO"]["REPPONTOEXTERNO"] = $row["reppontoexterno"];

		//print_r($_SESSION);die();
		$_SESSION["SESSAO"]["SQLWHEREMOD"] = $arrModulosUsuario["SQLWHEREMOD"];
		$_SESSION["SESSAO"]["MODULOS"] = $arrModulosUsuario["MODULOS"];
		$_SESSION["SESSAO"]["LPS"] = $arrModulosUsuario["LPS"];
		$_SESSION["SESSAO"]["AGENCIAS"] = $arrModulosUsuario["AGENCIAS"];
		$_SESSION["SESSAO"]["CONTAITEM"] = $arrModulosUsuario["CONTAITEM"];

		$_SESSION["SESSAO"]["MIGRACAO"]["SQLWHEREMOD"] = $arrModulosPorIdPessoa["SQLWHEREMOD"];
		$_SESSION["SESSAO"]["MIGRACAO"]["MODULOS"] = $arrModulosPorIdPessoa["MODULOS"];
		$_SESSION["SESSAO"]["MIGRACAO"]["LPS"] = $arrModulosPorIdPessoa["LPS"];
		$_SESSION["SESSAO"]["MIGRACAO"]["AGENCIAS"] = $arrModulosPorIdPessoa["AGENCIAS"];
		$_SESSION["SESSAO"]["MIGRACAO"]["CONTAITEM"] = $arrModulosPorIdPessoa["CONTAITEM"];

		$_SESSION["SESSAO"]["JACESSOSUSUARIO"] = $JSON->encode($arrModulosUsuario["ARRACESSOSUSUARIO"]);

		//maf120421: Configurar singleton para o carbon, e evitar o uso de session, para posterior migracao ao CB9
		cb::$usr["LOGADO"] = $_SESSION["SESSAO"]["LOGADO"];
		cb::$usr["USUARIO"] = $_SESSION["SESSAO"]["USUARIO"];
		cb::$usr["NOME"] = $_SESSION["SESSAO"]["NOME"];
		cb::$usr["NOMECURTO"] = $_SESSION["SESSAO"]["NOMECURTO"];
		cb::$usr["EMAIL"] = $_SESSION["SESSAO"]["EMAIL"];
		cb::$usr["RAMALFIXO"] = $_SESSION["SESSAO"]["RAMALFIXO"];
		cb::$usr["IDTIPOPESSOA"] = $_SESSION["SESSAO"]["IDTIPOPESSOA"];
		cb::$usr["FULLACCESS"] = $_SESSION["SESSAO"]["FULLACCESS"];
		cb::$usr["CUSTOMCSS"] = $_SESSION["SESSAO"]["CUSTOMCSS"];
		cb::$usr["WEBMAILUSUARIO"] = $_SESSION["SESSAO"]["WEBMAILUSUARIO"];
		cb::$usr["IDARQUIVOAVATAR"] = $_SESSION["SESSAO"]["IDARQUIVOAVATAR"];
		cb::$usr["PERMISSAOCHAT"] = $_SESSION["SESSAO"]["PERMISSAOCHAT"];
		//cb::$usr["JWT"]=$_SESSION["SESSAO"]["JWT"];
		cb::$usr["SQLWHEREMOD"] = $_SESSION["SESSAO"]["SQLWHEREMOD"];
		cb::$usr["MODULOS"] = $_SESSION["SESSAO"]["MODULOS"];
		cb::$usr["LPS"] = $_SESSION["SESSAO"]["LPS"];
		cb::$usr["JACESSOSUSUARIO"] = $_SESSION["SESSAO"]["JACESSOSUSUARIO"];
		cb::$usr["MATRIZPERMISSOES"] = $_SESSION["SESSAO"]["MATRIZPERMISSOES"];

		getEmpresaPessoa();

		/*
		 * Log de Sessão
		 */
		alarmeSet('N', 'login', 'sucesso', $inusr);
		pessoaLog('L');

		/*
		 * Executa POS_LOGIN
		 */
		$arq_poslogin = "../eventcode/modulo/pos__login.php";

		if (file_exists($arq_poslogin)) {
			include_once($arq_poslogin);
		}

		/*
		 * Se o usuário nao possuir informação de Multi Empresa (não sendo usuário FULL), interromper
		 */
		if ((empty($row["idempresa"]) or $row["idempresa"] == 0) and $row["fullaccess"] != 'Y') {

			unset($_SESSION["SESSAO"]["USUARIO"]);
			unset($_SESSION["SESSAO"]["SENHA"]);
			$_SESSION["SESSAO"]["LOGADO"] = false;
			die("Usuário sem informação de Multi Empresa");
		}
		//Informa url para refresh via javascript
		header('X-CB-REDIR: ./');

		//Devolve o token para o caller com os dados do usuario. Não utilizar Sessions.
		$decoded = JWT::decode(cb::$usr["JWT"], _JWTKEY, array('HS256'));
		return $decoded;
	} else {
		alarmeSet('Y', 'login', 'Senha Inválida', $inusr);
		unset($_SESSION["SESSAO"]["USUARIO"]);
		unset($_SESSION["SESSAO"]["SENHA"]);
		$_SESSION["SESSAO"]["LOGADO"] = false;
		die("Login ou Senha inválidos.");
	}
}

/*
 * Classe generica para o carbon
 * - Substituirá o uso de $_SESSION para variáveis referentes ao usuário
 * - Deve receber propriedades dinamicamente: https://www.php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.indirect
 */
class cb
{
	public static $usr = [];
	public static $jwt = [];
	public static $session = [];

	public static function __callStatic($magicmetodo, $arguments)
	{
		if ($magicmetodo == "habilitarMatriz") {
			if (!empty($_GET["_idempresa"]) and empty(self::$usr["HABILITARMATRIZ"])) { // Verificar se as variáveis já foram instanciadas
				$qr = "SELECT habilitarmatriz FROM empresa WHERE idempresa = " . self::idempresa();
				$rs = d::b()->query($qr);
				if (!$rs or mysqli_num_rows($rs) < 1) {
					self::$usr["HABILITARMATRIZ"] = 'N';
				} else {
					$rw = mysqli_fetch_assoc($rs);
					self::$usr["HABILITARMATRIZ"] = $rw["habilitarmatriz"];
				}

				return self::$usr["HABILITARMATRIZ"];
			} else if (!empty($_GET["_idempresa"])) {
				return self::$usr["HABILITARMATRIZ"];
			} else {
				return self::$usr["HABILITARMATRIZ"] = $_SESSION["SESSAO"]["HABILITARMATRIZ"];
			}
		}
	}

	public static function idempresa()
	{
		if (!empty($_GET["_idempresa"])) { // BRUTE FORCE
			self::$usr["IDEMPRESA"] = d::b()->real_escape_string($_GET["_idempresa"]);
		} else {
			if ($_SERVER['SERVER_NAME'] == RESULTADOSURL) {
				self::$usr["IDEMPRESA"] = 1;
			} else if ($_SERVER['SERVER_NAME'] == "resultados.inata.com.br") {
				self::$usr["IDEMPRESA"] = 2;
			} else {
				self::$usr["IDEMPRESA"] = $_SESSION["SESSAO"]["IDEMPRESA"];
			}
		}
		return self::$usr["IDEMPRESA"];
	}
}

function buscarImagemFuncionario($idpessoa)
{
	$sqlAvatar = "SELECT nome FROM arquivo WHERE idobjeto = $idpessoa AND tipoobjeto = 'pessoa' AND tipoarquivo = 'AVATAR';";
	$resAvatar = d::b()->query($sqlAvatar) or die("Erro ao buscar Avatar" . mysqli_error(d::b()) . "");
	$num = mysqli_num_rows($resAvatar);
	if ($num > 0) {
		$rowAvatar = mysql_fetch_assoc($resAvatar);
		return $rowAvatar['nome'];
	}
}

function getModsUsr($key = "")
{
	$idempresa = cb::idempresa();

	if (!$idempresa) return null;

	if (!empty($key)) {
		if ($key == 'LPS' or $key == 'AGENCIAS') {
			return $_SESSION["SESSAO"]["MIGRACAO"][$key];
		} else {
			return $_SESSION["SESSAO"]["MIGRACAO"][$key][$idempresa];
		}
	} else {
		return $_SESSION["SESSAO"]["MIGRACAO"];
	}
}

function showControllerErrors($errorsList = [])
{
	if (count($errorsList) == 0) return;

	$errors = json_encode($errorsList);
	return "
		(function(){
			let _controllerErrors = {$errors};
			let layout = '';
			for(let error of _controllerErrors){
				console.error(error);
				layout += `<div class='alert alert-danger'><b>`+error+`</b></div>`;
			}

			$('#cbContainer').prepend(layout);
		})();
	";
}

function validaTokenReduzido()
{
	$_headers = getallheaders();
	//maf: o phpfpm converte as keys do array para camel-case
	$_headers = array_change_key_case($_headers, CASE_LOWER);
	if (!$_headers["authorization"] && !$_headers["Authorization"]) {
		$headerCokie = explode(" ", $_headers["Cookie"]);
		$headerCokieJwt = explode("=", $headerCokie[1]);
		if (empty($headerCokieJwt[1])) {
			return array("sucesso" => false, "Token não enviado");
		} else {
			$_headers["authorization"] = $headerCokieJwt[1];
		}
	}

	$sToken = empty($_headers["authorization"]) ? $_headers["Authorization"] : $_headers["authorization"];

	try {
		$decoded = JWT::decode($sToken, _JWTKEY, array('HS256'));
		return array("sucesso" => true, "token" => $decoded);
	} catch (\Exception $e) {
		$merro = $e->getMessage();
		return array("sucesso" => false, "erro" => $merro);
	}
}

/*
 * Validação de token JWT (e legado)
 */
function validaToken()
{
	global $_headers;

	//maf: o phpfpm converte as keys do array para camel-case
	$_headers = array_change_key_case($_headers, CASE_LOWER);

	$sToken = "";

	if (!$_headers["jwt"] and !$_GET["_jwt"] and !$_POST["_jwt"] and !$_COOKIE['jwt']) {
		return array("sucesso" => false, "Token não enviado");
	} else {
		//Recupera a informação de token na seguinte ordem: Headers, GET e por último POST
		if (!empty($_headers["jwt"])) {
			$sToken = $_headers["jwt"];
		} else if (!empty($_GET["_jwt"])) {
			$sToken = $_GET["_jwt"];
		} else if (!empty($_COOKIE["jwt"])) {
			$sToken = $_COOKIE["jwt"];
		} else {
			return array("sucesso" => false, "Token informado incorretamente");
		}
	}
	//Catch exception: https://github.com/firebase/php-jwt/issues/65
	try {
		$decoded = JWT::decode($sToken, _JWTKEY, array('HS256'));

		//Recupera os dados do usuário logado
		$row = getUsr($decoded->usuario);
		//Armazena os dados do JWT
		cb::$jwt = $decoded;
		cb::usr("IDEMPRESA", $_GET["_idempresa"]);
		cb::$usr["IDEMPRESA"] = $row["idempresa"];
		cb::$usr["IDPESSOA"] = $row["idpessoa"];
		//cb::$usr["OBRIGATORIOCONTATO"]=$row["flagobrigatoriocontato"];
		cb::$usr["IDTIPOPESSOA"] = $row["idtipopessoa"]; //maf121218: considerar tipo de pessoa antes de parametrizar unidade
		cb::$usr["USUARIO"] = $decoded->usuario;
		cb::$usr["FULLACCESS"] = $row["fullaccess"];
		cb::$usr["LOGADO"] = true;
		cb::$usr["NOME"] = $row["nome"];
		cb::$usr["NOMECURTO"] = $row["nomecurto"];
		cb::$usr["EMAIL"] = $row["email"];
		cb::$usr["RAMALFIXO"] = $row["ramalfixo"];
		cb::$usr["IDTIPOPESSOA"] = $row["idtipopessoa"];
		cb::$usr["SESSAO"]["CUSTOMCSS"] = $row["customcss"];
		cb::$usr["WEBMAILUSUARIO"] = $row["webmailusuario"];
		cb::$usr["IDARQUIVOAVATAR"] = $row["idarquivoavatar"];
		cb::$usr["PERMISSAOCHAT"] = $row["permissaochat"];

		if ($_SESSION['SESSAO']['SUPERUSUARIO'] != true) {
			$_SESSION["SESSAO"]["IDEMPRESA"] = $row["idempresa"];
			$_SESSION["SESSAO"]["IDPESSOA"] = $row["idpessoa"];
			//$_SESSION["SESSAO"]["OBRIGATORIOCONTATO"]=$row["flagobrigatoriocontato"];
			$_SESSION["SESSAO"]["IDTIPOPESSOA"] = $row["idtipopessoa"]; //maf121218: considerar tipo de pessoa antes de parametrizar unidade
			$_SESSION["SESSAO"]["USUARIO"] = $decoded->usuario;
			$_SESSION["SESSAO"]["FULLACCESS"] = $row["fullaccess"];
			$_SESSION["SESSAO"]["LOGADO"] = true;
			$_SESSION["SESSAO"]["NOME"] = $row["nome"];
			$_SESSION["SESSAO"]["NOMECURTO"] = $row["nomecurto"];
			$_SESSION["SESSAO"]["EMAIL"] = $row["email"];
			$_SESSION["SESSAO"]["RAMALFIXO"] = $row["ramalfixo"];
			$_SESSION["SESSAO"]["IDTIPOPESSOA"] = $row["idtipopessoa"];
			$_SESSION["SESSAO"]["SESSAO"]["CUSTOMCSS"] = $row["customcss"];
			$_SESSION["SESSAO"]["WEBMAILUSUARIO"] = $row["webmailusuario"];
			$_SESSION["SESSAO"]["IDARQUIVOAVATAR"] = $row["idarquivoavatar"];
			$_SESSION["SESSAO"]["PERMISSAOCHAT"] = $row["permissaochat"];
			$_SESSION["SESSAO"]["ACESSO"] = $row["acesso"];
			$_SESSION["SESSAO"]["PONTOWEB"] = $row["pontoweb"];
			$_SESSION["SESSAO"]["STATUSPONTO"] = $row["statusponto"];
		}

		$arrModulosUsuario = retArrayModulosUsuario();
		$arrModulosPorIdPessoa = arrayModulosPorIdPessoa($_SESSION["SESSAO"]["IDPESSOA"]);

		if ($_SESSION['SESSAO']['SUPERUSUARIO'] != true) {
			$_SESSION["SESSAO"]["SQLWHEREMOD"] = $arrModulosUsuario["SQLWHEREMOD"];
			$_SESSION["SESSAO"]["MODULOS"] = $arrModulosUsuario["MODULOS"];
			$_SESSION["SESSAO"]["LPS"] = $arrModulosUsuario["LPS"];

			$_SESSION["SESSAO"]["MIGRACAO"]["SQLWHEREMOD"] = $arrModulosPorIdPessoa["SQLWHEREMOD"];
			$_SESSION["SESSAO"]["MIGRACAO"]["MODULOS"] = $arrModulosPorIdPessoa["MODULOS"];
			$_SESSION["SESSAO"]["MIGRACAO"]["LPS"] = $arrModulosPorIdPessoa["LPS"];
			$_SESSION["SESSAO"]["MIGRACAO"]["AGENCIAS"] = $arrModulosPorIdPessoa["AGENCIAS"];
			$_SESSION["SESSAO"]["MIGRACAO"]["CONTAITEM"] = $arrModulosPorIdPessoa["CONTAITEM"];
		}

		cb::$usr["SQLWHEREMOD"] = $arrModulosUsuario["SQLWHEREMOD"];
		cb::$usr["MODULOS"] = $arrModulosUsuario["MODULOS"];
		cb::$usr["LPS"] = $arrModulosUsuario["LPS"];
		getClientesContato();

		return array("sucesso" => true, "token" => $decoded);
	} catch (\Exception $e) {
		$merro = $e->getMessage();
		return array("sucesso" => false, "erro" => $merro);
	}
}

function consultarAssinarCertificado($_idpessoa, $content, $_idempresa = null)
{
	$_senha = $_COOKIE[base64_encode('certificado')];
	$result = "";

	$_idempresa = (!empty($_idempresa)) ? $_idempresa : $_SESSION["SESSAO"]["IDEMPRESA"];

	// VERIFICA SE O USUÁRIO POSSUI SENHA DE CERTIFICADO NO COOKIE
	if (empty($_senha)) {
		$sql = "SELECT 1 FROM novocertificadodigital WHERE idobjeto = " . $_idpessoa . " and objeto = 'pessoa'";
		$rec = d::b()->query($sql) or die("Erro ao verificar existência de certificado digital" . mysqli_error(d::b()) . "");
		$num = mysqli_num_rows($rec);
		if ($num > 0) {

			// RETORNA PARA DIGITAR A SENHA
			cbSetPostHeader("0", "aut");
			die("Senha Requerida");
		} else {

			// ASSINAR COMO EMPRESA

			//---------------------------------------------------------------------------------------------------------------//

			$sqlce = "SELECT REPLACE(certificado,'..','inc/nfe/sefaz4') as caminho,senha FROM empresa WHERE idempresa = " . $_idempresa;
			$resce = mysql_query($sqlce) or die("Erro ao recuperar Certificado Digital: " . mysql_error());
			if (mysql_num_rows($resce) == 0) {
				cbSetPostHeader("-3", "aut");
				die("assinaturaDigitalA1: Nenhum certificado ATIVO foi encontrado.");
			} elseif (mysql_num_rows($resce) > 1) {
				cbSetPostHeader("-3", "aut");
				die("assinaturaDigitalA1: Mais de 1 certificado ATIVO foi encontrado.");
			} else {
				$rce = mysql_fetch_assoc($resce);

				$result = assinar($rce["caminho"], $rce["senha"], $content, "empresa");
			}
			//---------------------------------------------------------------------------------------------------------------//
		}
	} else {

		// ASSINAR COMO PESSOA

		//---------------------------------------------------------------------------------------------------------------//
		$sqlce = "SELECT REPLACE(caminho,'../','') as caminho FROM novocertificadodigital WHERE idobjeto = " . $_idpessoa . " AND objeto = 'pessoa'";
		$resce = mysql_query($sqlce) or die("Erro ao recuperar Certificado Digital: " . mysql_error());
		if (mysql_num_rows($resce) == 0) {
			cbSetPostHeader("-3", "aut");
			die("assinaturaDigitalA1: Nenhum certificado ATIVO foi encontrado.");
		} elseif (mysql_num_rows($resce) > 1) {
			cbSetPostHeader("-3", "aut");
			die("assinaturaDigitalA1: Mais de 1 certificado ATIVO foi encontrado. Certifique-se de deixar somente 1.");
		} else {
			$rce = mysql_fetch_assoc($resce);

			$result = assinar($rce["caminho"], base64_decode($_senha), $content, "pessoa");
		}
		//---------------------------------------------------------------------------------------------------------------//
	}

	if ($result == -2) {
		cbSetPostHeader("-2", "aut");
		die("Verifique o arquivo de certificado");
	} else if ($result == -1) {
		cbSetPostHeader("-1", "aut");
		die("Senha Inválida");
	} else if ((gettype($result) == "array") and !(empty($result))) {
		return $result;
	} else {
		cbSetPostHeader("-3", "aut");
		die("Variável 'result' está vazia");
	}
}

function inserirAtualizarCarrimbo($status, $result, $idcarrimbo = false, $idobj = false, $tipoobj = false, $alteradoem = false, $_idempresa = null)
{
	require_once("laudo.php");

	$_idempresa = (!empty($_idempresa)) ? $_idempresa : $_SESSION["SESSAO"]["IDEMPRESA"];
	//$cb = new CB();
	if (!$idcarrimbo) {
		// Inserir na carrimbo

		if (!empty($idobj) and !empty($tipoobj) and !empty($status) and !empty($result)) {

			if ($result["tipoassinatura"] == "empresa") {
				$idpessoa = 782;
			} else {
				$idpessoa = $_SESSION["SESSAO"]["IDPESSOA"];
			}

			// TODO: alterar para o novo CBPOST
			/*
			$arr = array(
				"_carrimbo_i_carrimbo_idobjeto" => $_SESSION["SESSAO"]["IDEMPRESA"]
				,"_carrimbo_i_carrimbo_idpessoa" => $idpessoa
				,"_carrimbo_i_carrimbo_idobjeto" => $idobj
				,"_carrimbo_i_carrimbo_tipoobjeto" => $tipoobj
				,"_carrimbo_i_carrimbo_idobjetoext" => $_SESSION["SESSAO"]["IDPESSOA"]
				,"_carrimbo_i_carrimbo_tipoobjetoext" => 'pessoa'
				,"_carrimbo_i_carrimbo_status" => $status
				,"_carrimbo_i_carrimbo_assinatura" => $result["assinatura"]
				,"_carrimbo_i_carrimbo_criadopor" => $_SESSION["SESSAO"]["USUARIO"]
				,"_carrimbo_i_carrimbo_criadoem" => 'now()'
				,"_carrimbo_i_carrimbo_alteradopor" => $_SESSION["SESSAO"]["USUARIO"]
				,"_carrimbo_i_carrimbo_alteradoem" => 'now()'
			);
			$res = $cb->save($arr);
		
			if(!$res){
				die($cb->erro);
			}
			*/

			$sql = "INSERT into carrimbo
			(idempresa,idpessoa,idobjeto,tipoobjeto,idobjetoext,tipoobjetoext,status,assinatura,criadopor,criadoem,alteradopor,alteradoem)
				values
				(" . $_idempresa . "," . $_SESSION["SESSAO"]["IDPESSOA"] . "," . $idobj . ",'" . $tipoobj . "'," . $_SESSION["SESSAO"]["IDPESSOA"] . ",'pessoa','" . $status . "','" . $result["assinatura"] . "','" . $_SESSION["SESSAO"]["USUARIO"] . "',now(),'" . $_SESSION["SESSAO"]["USUARIO"] . "',now())";
			$res = mysql_query($sql) or die("ERRO ao inserir na tabela carrimbo: " . mysql_error() . "\n SQL: " . $sql);

			return true;
		} else {
			return false;
		}
	} else {
		// Atualizar carrimbo

		if (!empty($idcarrimbo) and !empty($status) and !empty($result)) {

			// TODO: alterar para o novo CBPOST
			/*
			$arr = array(
				"_carrimbo_u_carrimbo_idcarrimbo" => $idcarrimbo
				,"_carrimbo_u_amostra_status" => $status
				,"_carrimbo_u_amostra_assinatura" => $result["assinatura"]
			);
			$res = $cb->save($arr);
		
			if(!$res){
				die($cb->erro);
			}
			*/

			//Após assinatura do TEA, atualiza o hist para ATIVO - A data alteradoem é de acordo com o datacoleta da Amostra
			if ($_POST['modulo'] == 'amostratraprovisorio' || $_POST['modulo'] == 'amostratra') {
				$sql = "SELECT datacoleta, alteradoem
						  FROM amostra 
						 WHERE idamostra = '" . $idobj . "'";
				$resdata = d::b()->query($sql);
				$rowdata = mysqli_fetch_assoc($resdata);

				if (!empty($rowdata['datacoleta'])) {
					$alter = $rowdata['datacoleta'];
				} else {
					$alter = $rowdata['alteradoem'];
				}
				$alteradoem = ", alteradoem = '" . $alter . "'";

				$sqlHist = "UPDATE fluxostatushist
						       SET status = 'ATIVO', alteradoem = now(), alteradopor = '" . $_SESSION["SESSAO"]["USUARIO"] . "'
						     WHERE idmodulo = " . $idobj . " AND modulo = 'amostratraprovisorio'";
				mysql_query($sqlHist) or die("ERRO ao atualizar a tabela carrimbo: " . mysql_error() . "\n SQL: " . $sqlHist);
			}

			$sql = "UPDATE carrimbo
					   SET status = '" . $status . "', assinatura = '" . $result["assinatura"] . "' $alteradoem
					 WHERE idcarrimbo = " . $idcarrimbo;
			$res = mysql_query($sql) or die("ERRO ao atualizar a tabela carrimbo: " . mysql_error() . "\n SQL: " . $sql);

			validarAssinaturaTesteTEA($idobj);

			return true;
		} else {
			return false;
		}
	}
}

// Retorna:
//		0 caso não consiga abrir o arquivo
//		-1 caso a senha esteja incorreta
//		Json caso de sucesso
function assinar($certificado, $senha, $content, $tipoass)
{
	global $JSON;
	$arrAssinatura = array();
	$conteudobin = file_get_contents(_CARBON_ROOT . $certificado);
	//echo $senha;die;
	if ($conteudobin) {
		//extrai o conteudo do certificado, para recuperar a chave PEM
		$results = array();
		$pemok = openssl_pkcs12_read($conteudobin, $results, $senha);
		//print_r($results);die;
		if ($pemok) {

			//Recupera a data de validade
			$certinfo = openssl_x509_parse($results['cert']);
			//print_r($certinfo);die;

			//Numero de Serie do certificado
			$validade = date('d/m/Y', $certinfo["validTo_time_t"]);
			$arrAssinatura["serial"] = $certinfo["serialNumber"];

			$arrAssinatura["tipoassinatura"] = $tipoass;

			//Assinatura do conteúdo da string
			openssl_sign($content, $signature, $results['pkey'], "md5");
			$arrAssinatura["assinatura"] = base64_encode(md5($signature));
			//fingerprint. Este deve bater com a visualização do certificado utilizando o browser de internet
			$output = null;
			$resource = openssl_x509_read($results['cert']);
			$result = openssl_x509_export($resource, $output);
			if ($result !== false) {
				$output = str_replace('-----BEGIN CERTIFICATE-----', '', $output);
				$output = str_replace('-----END CERTIFICATE-----', '', $output);

				$output = base64_decode($output);
				$arrAssinatura["fingerprint"] = implode(":", str_split(strtoupper(sha1($output)), 2)) . " ";
			}

			return $arrAssinatura;
		} else {
			return -1;
		}
	} else {
		return -2;
	}
}

/*
 * Verificacao de login
 */
function logado()
{
	if ($_SESSION["SESSAO"]["LOGADO"] && $_SESSION["SESSAO"]["USUARIO"] != "") {
		return true;
	} else {
		return false;
	}
}

function setClientesContato($inSqlClientes)
{
	//die($inSqlClientes);

	//Consulta os clientes do contato
	$_rescli = mysql_query($inSqlClientes) or die("Falha ocorrida ao recuperar lista de Clientes.");

	if (mysql_num_rows($_rescli) == 0) {
		die("\nNenhum cliente configurado para este contato: [" . $_SESSION["SESSAO"]["IDPESSOA"] . "][" . $_SESSION["SESSAO"]["NOME"] . "]\n");
	} else {

		$_strclicontato = "";
		$_arrcontatosecretaria = array(); //como cada secretaria pode estar relacionada a mais de 1 cliente, a string gerada estava repetindo a secretaria
		$_tv = "";
		while ($r = mysql_fetch_assoc($_rescli)) {
			$_strclicontato .= $_tv . $r["idpessoa"];
			$_arrcontatosecretaria[$r["idsecretaria"]] = $r["idsecretaria"];
			$_tv = ",";
		}
	}

	//variavel contendo todos os clientes dos quais o usuario logado foi associado como Contato
	$_SESSION["SESSAO"]["STRCONTATOCLIENTE"] = empty($_strclicontato) ? "null" : $_strclicontato;
	$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"] = implode(",", $_arrcontatosecretaria);

	//Verifica se algum dos clientes é fabricante, para alterar qualquer rótulo do sistema
	$sqlnuclcli = "select idpessoa
					,sum(if(idnucleotipo='G',1,0)) as qnucleos
					,sum(if(idnucleotipo='F',1,0)) as qprodutos
				from nucleo 
				where idpessoa in (" . $_SESSION["SESSAO"]["STRCONTATOCLIENTE"] . ")
				group by idpessoa";

	$resnuccli = mysql_query($sqlnuclcli) or die("logincarbon(): Erro ao recuperar informacoes de nucleo do cliente: " . mysql_error());

	$rnuccli = mysql_fetch_assoc($resnuccli);

	$_SESSION["SESSAO"]["QNUCLEOS"] = $rnuccli["qnucleos"];
	$_SESSION["SESSAO"]["QPRODUTOS"] = $rnuccli["qprodutos"];

	//print_r($_SESSION["SESSAO"]);die;

	//Instanciar as definições de mtotabcol para não serem sobrepostas posteriormente
	retarraytabdef("vwcliente_visualizarresultados");

	//Alterar definição de rótulos da mtotabcol para vwcliente_visualizarresultados (instanciada acima)
	if ($_SESSION["SESSAO"]["QNUCLEOS"] > 0 and $_SESSION["SESSAO"]["QPRODUTOS"] > 0) { //Fabricantes e Granjas

		cb::$session["arrtabledef"]["vwcliente_visualizarresultados"]["nucleo"]["rotcurto"] = "Produto/Núcleo";
		cb::$session["arrtabledef"]["vwcliente_visualizarresultados"]["nucleo"]["rotpsq"] = "Produto/Núcleo";
	} elseif ($_SESSION["SESSAO"]["QNUCLEOS"] > 0 and $_SESSION["SESSAO"]["QPRODUTOS"] == 0) { //Granjas

		cb::$session["arrtabledef"]["vwcliente_visualizarresultados"]["nucleo"]["rotcurto"] = "Núcleo";
		cb::$session["arrtabledef"]["vwcliente_visualizarresultados"]["nucleo"]["rotpsq"] = "Núcleo";
	} elseif ($_SESSION["SESSAO"]["QNUCLEOS"] == 0 and $_SESSION["SESSAO"]["QPRODUTOS"] > 0) { //Fabricantes

		cb::$session["arrtabledef"]["vwcliente_visualizarresultados"]["nucleo"]["rotcurto"] = "Produto";
		cb::$session["arrtabledef"]["vwcliente_visualizarresultados"]["nucleo"]["rotpsq"] = "Produto";
	}
}

function getClientesContato()
{

	//PHOL: 2022-05-05 Retirando Die: SQL das querys, as consultas são usadas no login e não podem cuspir sql como erro
	// pois facilitam na invasão do sistema
	if (empty(getModsUsr("LPS"))) {
		die("Entrar em contato com o Administrador: você não possui lista de permissão!");
	}

	$sqob = "select * from " . _DBCARBON . "._lp 
			where flagobrigatoriofiltro='Y' 
			and idlp in (" . getModsUsr("LPS") . ")";
	$reob = d::b()->query($sqob) or die("Erro ao verificar se há obrigatório contato!");
	$qtdobrigatorio = mysqli_num_rows($reob);
	$organograma = 'N';
	$flagobrigatoriocontato = 'N';
	$_SESSION["SESSAO"]["OBRIGATORIOCONTATO"] = 'N';
	cb::$usr["OBRIGATORIOCONTATO"] =	'N';
	$_sqlcli1 = '';

	if ($qtdobrigatorio > 0) {
		$flagobrigatoriocontato = 'Y';

		while ($rowob = mysqli_fetch_assoc($reob)) {
			if ($rowob['flagobrigatoriocontato'] == 'N') { // se obriga a ter contato
				$flagobrigatoriocontato = 'N';
			}
		}

		$_SESSION["SESSAO"]["OBRIGATORIOCONTATO"] = 'Y';
		cb::$usr["OBRIGATORIOCONTATO"] =	'Y';

		//BUSCAR NA LP INFORMACOES DE TIPO PESSOA E UNIDADE DE NEGOCIO
		// buscar as unidade de negocio do contato obrigatório
		$sqlp = "SELECT 
				u.idplantel
			from plantel u 
				join plantelobjeto p on( u.idplantel = p.idplantel and p.idobjeto in (" . getModsUsr("LPS") . ") and p.tipoobjeto = 'lp')
			where u.status='ATIVO'
			order by u.plantel";
		$repl = d::b()->query($sqlp) or die("Erro ao verificar unidades de negócio.");
		$qtplantel = mysqli_num_rows($repl);
		if ($qtplantel < 1) {
			die("Falha: Para FILTRAR POR UNIDADE DE NEGÓCIO e necessário selecionar na lista de permissão");
		} else {
			$arrPl = array();
			while ($rowpl = mysqli_fetch_assoc($repl)) {
				$arrPl[] = "'" . $rowpl["idplantel"] . "'";
			}
			$strIdplantel = implode(",", $arrPl);
		}
		// buscar os tipo pessoa do contato obrigatório
		$sqlf = "SELECT  a.idtipopessoa
				FROM tipopessoa a
				JOIN objetovinculo ov on ov.idobjetovinc = a.idtipopessoa AND ov.tipoobjetovinc = 'tipopessoa' AND ov.idobjeto in (" . getModsUsr("LPS") . ") AND ov.tipoobjeto = '_lp'
			where a.status='ATIVO'
			ORDER BY  a.tipopessoa";
		$repf = d::b()->query($sqlf) or die("Erro ao verificar tipo pessoa da lista de permissão.");
		$qtdtipopessoa = mysqli_num_rows($repf);
		if ($qtdtipopessoa < 1) {
			die("Falha: Para FILTRAR POR TIPO DE PESSOA é necessário selecionar na lista de permissão");
		} else {
			$arrTP = array();
			while ($rowf = mysqli_fetch_assoc($repf)) {

				// se incluir funcionario ira buscar os funcionarios do organograma
				if ($rowf["idtipopessoa"] == 1) {
					$organograma = 'Y';
					//$arrTP[]="'".$rowf["idtipopessoa"]."'";
				} else {
					$arrTP[] = "'" . $rowf["idtipopessoa"] . "'";
				}
			}
			$strIdtipopessoa = implode(",", $arrTP);
			// se incluir funcionario ira buscar os funcionarios do organograma
			if ($organograma == 'Y') {
				// buscar o organograma
				$andinidpessoa = getOrganogramaRep();
				$_sqlcli1 = " union select idpessoa,nome from pessoa where 1 " . $andinidpessoa;
			} //if($organograma=='Y'){	
		} //if($qtdtipopessoa<1){
	} //if($qtdobrigatorio>0){



	//Inicia com opção [null] para evitar falha de consultas
	//$_SESSION["SESSAO"]["STRCONTATOCLIENTE"] = "null";

	//Restringe os clientes conforme configuração
	/*No caso do representante sempre será cadastrado pelo 
	aumenos um cliente para que ele faça acesso hermesp 29-11-2017 solipor Daniel
    */

	if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 3  or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 9) { //usuariocliente

		if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 9 /*and  $_SESSION["SESSAO"]["OBRIGATORIOCONTATO"]=='Y'*/) { // se for consultor e for contato por tipo de plantel


			$_sqlcli = "select p.idpessoa ,nome 
                            from pessoacontato c,pessoa p
                            where p.idpessoa = c.idpessoa
                            and c.idcontato in (select idpessoa from pessoacontato where idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . " )
                                    union  
                            select  p.idpessoa ,nome  from pessoa p ,pessoacontato c
                            where p.idpessoa = c.idpessoa
                            and c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"];

			/* comentado para permitir que o plantel seja cadastrado no sistema nao sendo necessario alterar
                * a tabela pessoa 2020-06-15
                    $_sqlcli="select p.idpessoa,p.nome
					from pessoa p
					where status = 'ATIVO'
						and idtipopessoa in(2,3)
						and (contaves = (select contaves from pessoa c where c.idpessoa= " . $_SESSION["SESSAO"]["IDPESSOA"] . " and c.contaves ='Y')
                                                    or contagro = (select contagro from pessoa c where c.idpessoa= " . $_SESSION["SESSAO"]["IDPESSOA"] . " and c.contagro ='Y')
                                                    or contsuinos = (select contsuinos from pessoa c where c.idpessoa= " . $_SESSION["SESSAO"]["IDPESSOA"] . " and c.contsuinos ='Y')
                                                    or contbovinos = (select contbovinos from pessoa c where c.idpessoa= " . $_SESSION["SESSAO"]["IDPESSOA"] . " and c.contbovinos ='Y')
                                                )
                        order by p.nome";                
                */
			setClientesContato($_sqlcli);
		} else {
			//selecionar clientes relacionados ao contato que possuem alguma amostra registrada. clientes com amostra registrada evitam grupos vazios de clientes em lugares especificos.
			/*$_sqlcli ="select * from (
										select idpessoa, nome
												from pessoa p
												where status = 'ATIVO'
												and p.idtipopessoa =2
												and p.idpessoa in (select c.idpessoa from pessoacontato c where c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . ")
												-- and exists(select 1 from amostra a where a.idpessoa = p.idpessoa)
										union    
										select p.idpessoa ,nome from pessoacontato c,pessoa p
											where p.idpessoa = c.idcontato
											and c.idpessoa in (select idpessoa from pessoacontato where idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . " )) as a order by a.nome";*/

			//23/11/2018 MCC - CONSULTA ALTERADA PARA FICAR IGUAL À EXISTENTE EM RESULTADOS.LAUDOLAB.COM.BR 
			$_sqlcli = "select idpessoa, nome
								from pessoa p
								where p.status = 'ATIVO'
									and p.idtipopessoa in (2,3)
									and 
															(
															p.idpessoa in (select idpessoa from pessoacontato where idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . ")
									-- and exists(select 1 from amostra a where a.idpessoa = p.idpessoa)
															or exists (select 1 from vwcontatorepresentante c where c.idpessoa = p.idpessoa and c.idrepresentante=" . $_SESSION["SESSAO"]["IDPESSOA"] . ") 
										or ( p.idtipopessoa = 3 and not exists (select 1 from pessoacontato c where c.idcontato = p.idpessoa))
											)
								order by nome";

			setClientesContato($_sqlcli);
		}
	} elseif ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 4) { //oficial

		$_sqlcli = "select idpessoa, nome, idsecretaria
                            from pessoa p
							where status = 'ATIVO'
							" . getidempresa('p.idempresa', 'pessoa') . "
                                    and idtipopessoa = 2
                                    and idsecretaria in (
                                            select idpessoa 
                                            from pessoacontato 
                                            where idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
                                    )
                                    -- and exists(select 1 from resultado r where r.idsecretaria = p.idsecretaria)
                            order by nome";
		setClientesContato($_sqlcli);
	} elseif ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 16) {

		$_sqlcli = "select p.idpessoa ,nome 
                            from pessoacontato c,pessoa p
							where p.idpessoa = c.idpessoa
							" . getidempresa('p.idempresa', 'pessoa') . "
                            and c.idcontato in (select idpessoa from pessoacontato where idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . " )
						union  
                            select  p.idpessoa ,nome  from pessoa p ,pessoacontato c
							where p.idpessoa = c.idpessoa
							" . getidempresa('p.idempresa', 'pessoa') . "
                            and c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
						union  						
							select p.idpessoa ,p.nome
							from pessoacontato c,pessoa p
							where p.idpessoa = c.idcontato and p.idtipopessoa = 3
							" . getidempresa('p.idempresa', 'pessoa') . "
							and c.idpessoa in (select ct.idpessoa from pessoacontato cc join pessoacontato ct on(ct.idcontato=cc.idpessoa)
							where cc.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . " )";
		setClientesContato($_sqlcli);
	} elseif ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1 and  $_SESSION["SESSAO"]["OBRIGATORIOCONTATO"] == 'Y') { //caso o funcionario veja por tipo de plantel

		/*
			$sqldiv="select dp.idplantel from divisao d 
							join divisaoplantel dp on(dp.iddivisao = d.iddivisao)
								where d.status = 'ATIVO'  
								".getidempresa('d.idempresa','divisao')."
								and d.idpessoa =".$_SESSION["SESSAO"]["IDPESSOA"];
			
			$resdiv = d::b()->query($sqldiv) or die("Functions.php: erro ao buscar divisão. " . mysqli_error(d::b()) . "<p>SQL: ".$sqldiv);
            $qtddiv=mysqli_num_rows($resdiv);
            if($qtddiv>0){
				
                $virg='';
                while($rowdiv=mysqli_fetch_assoc($resdiv)){                    
					$stridpl.=$virg.$rowdiv['idplantel'];
                    $virg=',';
                }
				
				$_SESSION["SESSAO"]["INIDPLANTEL"]=$stridpl;
                
                $_sqlcli="select p.idpessoa,p.nome
                            from pessoa p
                            where 1  ".getidempresa('p.idempresa','pessoa')."
                            and exists(select 1 from plantelobjeto o 
                                        where o.tipoobjeto = 'pessoa' 
                                            and o.idobjeto = p.idpessoa 
                                            and o.idplantel in(".$stridpl.") 
                                       )                                   
                            order by p.nome";
				*/
		if ($flagobrigatoriocontato == 'N') {


			$_SESSION["SESSAO"]["INIDPLANTEL"] = $strIdplantel;

			$_sqlcli = "select p.idpessoa,p.nome
							from pessoa p
							where  p.idtipopessoa in (" . $strIdtipopessoa . ")
							and exists(select 1 from plantelobjeto o 
										where o.tipoobjeto = 'pessoa' 
											and o.idobjeto = p.idpessoa 
											and o.idplantel in(" . $strIdplantel . ")
										)" . $_sqlcli1;
			//die($_sqlcli);

		} else { //if($flagobrigatoriocontato=='N'){
			$_sqlcli = "select p.idpessoa ,nome 
                            from pessoacontato c,pessoa p
							where p.idpessoa = c.idpessoa
							and p.idtipopessoa in (" . $strIdtipopessoa . ")
							
                            and c.idcontato in (select idpessoa from pessoacontato where idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . " )
						union  
                            select  p.idpessoa ,nome  
							from pessoa p ,pessoacontato c
							where p.idpessoa = c.idpessoa
							and p.idtipopessoa in (" . $strIdtipopessoa . ")							
                            and c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
						union  select p.idpessoa ,p.nome
								from pessoa p
								where p.idtipopessoa in (" . $strIdtipopessoa . ")
								
								and p.idpessoa  in (select ct.idcontato from pessoacontato cc join pessoacontato ct on(ct.idpessoa=cc.idpessoa)
								where cc.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . " )" . $_sqlcli1;
		} //if($flagobrigatoriocontato=='N'){

		$_sqlcli .= " union select  p.idpessoa ,p.nome 
								from pessoacontato c
									join pessoa p on(p.idpessoa=c.idpessoa and p.idtipopessoa = 5) 
							where c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"];


		setClientesContato($_sqlcli);
	} elseif ($_SESSION["SESSAO"]["IDTIPOPESSOA"] != 1 and $_SESSION["SESSAO"]["IDTIPOPESSOA"] != 8 and $_SESSION["SESSAO"]["IDTIPOPESSOA"] != 5) {
		//echo("\n<!-- Idtipopessoa não previsto[" . $_SESSION["SESSAO"]["IDTIPOPESSOA"] . "] -->\n.");
	}
}

/* preencher uma SESSION com as unidades da pessoa para posteriormente controlar os menus disponiveis ao usuário
 * hermesp 10/09/2018
*/
/*
function getPessoaUnidade(){
        $insql=" select o.idunidade from unidadeobjeto o join unidade u on(o.idunidade = u.idunidade and u.idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"].") 
                where o.tipoobjeto ='PESSOA' and o.idobjeto =".$_SESSION["SESSAO"]["IDPESSOA"];
    //die($insql);
    	//Consulta os clientes do contato
	$_rescli = mysql_query($insql) or die("[f:" . __FILE__ . "][l:" . __LINE__ . "]: Erro ao recuperar lista de unidades do Cliente.<!-- " . mysql_error() . " -->");
	
	if (mysql_num_rows($_rescli)>0) {
            
		$_stridunidade = "";
		$_tv = "";
		while($r = mysql_fetch_assoc($_rescli)){
                    $_stridunidade .= $_tv.$r["idunidade"];
                    $_tv = ",";
		}
        }else{
		if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==14){//Ignorar robot
			return "null";
		}else{
	             die("Erro: usuario não possui unidade definida em seu cadastro.");
		}
        }

	//variavel contendo todas as unidades dos quais o usuario logado foi associado em seu cadastro
	$_SESSION["SESSAO"]["STRIDUNIDADE"] = empty($_stridunidade)?"null":$_stridunidade;
    
}//function getPessoaUnidade(){
*/
function getEmpresaPessoa()
{
	$insql = "select empresa as idempresa from objempresa where idobjeto = " . $_SESSION["SESSAO"]["IDPESSOA"] . " and objeto = 'pessoa'";
	//die($insql);
	//Consulta os clientes do contato
	$_rescli = mysql_query($insql) or die("[f:" . __FILE__ . "][l:" . __LINE__ . "]: Erro ao recuperar lista de empresas do usuario.<!-- " . mysql_error() . " -->");

	if (mysql_num_rows($_rescli) > 0) {

		while ($r = mysql_fetch_assoc($_rescli)) {
			$_SESSION["SESSAO"]["STRIDEMPRESA"][$r["idempresa"]] = $r["idempresa"];
		}
	} else {
		if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] != 1) { //Ignorar robot
			return "null";
		} else {
			die("Erro: usuario não possui empresa definida em seu cadastro.");
		}
	}
} //function getEmpresaPessoa(){

/*
 * Merge dos arquivos .css ou .js, para reduzir o número de requisições GET ao http server
 * https://github.com/bennettstone/magic-min
 * $minify: Para efeito de rastreamento de erros em Javascript: Caso false, não irá efetuar merge: irá somente gerar os includes separados 
 */
function mergeArquivos($minify, $intipo, $inArrayArquivos)
{

	if ($minify) {
		$vars = array(
			'echo' => false, //Return or echo the values
			'encode' => false, //base64 images from CSS and include as part of the file?
			'timer' => false, //Ouput script execution time
			'gzip' => false, //Output as php with gzip?
			'closure' => false, //Use google closure (utilizes cURL)
			'remove_comments' => false, //Remove comments,
			'hashed_filenames' => false, //Alterar o nome dos arquivos a cada requisição, a fim de impedir cache 
			'force_rebuild' => true, //Forçar merge a cada requisição. Durante desenvolvimento, caso true, não irá utilizar recurso de cache, aumentando em alguns milisegundos o tempo da requisição
			'output_log' => false //Logs no console do Javascript
		);
		//Atenção: o caminho dos arquivos é relativo ao script chamador, cuja pasta deve permitir escrita
		$minified = new Minifier($vars);
		switch ($intipo) {
			case "css":
				return "\n\t<link rel=\"stylesheet\" href=\"" . $minified->merge('inc/tmp/estilos.css', 'css', $inArrayArquivos) . "?v=1.2\" />";
				break;
			case "js":
				return "\n\t<script src=\"" . $minified->merge('inc/tmp/scripts.js', 'js', $inArrayArquivos) . "?v=1.2\"></script>";
				break;
			default:
				return "<!-- mergeArquivos: Erro: deve ser informada a extensão [js] ou [css] para merge de arquivos -->";
				break;
		}
	} else {
		$vArquivos = "";
		foreach ($inArrayArquivos as $k => $v) {
			//Transforma o caminho absoluto em relativo à pasta base do Carbon
			$varCaminho = "." . str_replace(_CARBON_ROOT, "", $v);

			switch ($intipo) {
				case "css":
					$vArquivos .= "\n\t<link rel=\"stylesheet\" href=\"" . $varCaminho . "\" />";
					break;
				case "js":
					$vArquivos .= "\n\t<script src=\"" . $varCaminho . "\"></script>";
					break;
				default:
					return "<!-- mergeArquivos: Erro: deve ser informada a extensão [js] ou [css] para merge de arquivos -->";
					break;
			}
		}
		return $vArquivos;
	}
}

/*
 * Enviar headers de resposta
 */
function cbSetCustomHeader($inHeader, $inValor)
{

	if (!empty($inHeader) and !empty($inValor)) {
		header($inHeader . ': ' . $inValor);
	} else {
		die("functions.php cbSetCustomHeader(): Parametros inválidos!");
	}
}

/*
 * maf: trabalhar com headers de resposta para controlar o retorno das paginas que realizam ajax/post para a pagina cbpost
 * $inresp: [0|1]
 * $inform: [html|alert|erro|bool|none]
 */
function cbSetPostHeader($inresp, $inform)
{

	//if($inresp!==0 and (!empty($inresp) or !empty($inform))){
	if (!empty($inresp) or !empty($inform)) {
		header('X-CB-RESPOSTA: ' . $inresp);
		header('X-CB-FORMATO: ' . $inform);
	} else {
		die("functions.php cbSetPostHeader(): Parametros numéricos inválidos!");
	}
}

/*
 * Recupera os modulos que o usuario tem permissao
 * Esta funcao geralmente eh utilizada após o login com sucesso, e na validação de Tokens
 */
function retArrayModulosUsuario($ignoraEmpresa = true)
{
	//print_r($_SESSION);die;
	//   if ($_SESSION["SESSAO"]["IDPESSOA"]=="6494") { 
	//unset(getModsUsr("MODULOS"));
	//die($sqlmod.$arrmod["SQLWHEREMODM"]);
	//	 }
	if (!empty($_SESSION["SESSAO"]["IDLP"]) and $_SESSION["SESSAO"]["IDLP"] !== "") {
		$strlp = "'" . $_SESSION["SESSAO"]["IDLP"] . "'";
	} else {
		$strlp = arrLpSetores(true, $ignoraEmpresa);
	}


	/*
	// GVT - 24/11/2021 - Removido
	$idempresaacesso = userPref("s","idempresaacesso", $_POST["idempresa"]);//Atualiza a preferencia do usuario com o idempresa
	$obj = json_decode($idempresaacesso);
	$idempresaacesso = $obj->{'idempresaacesso'};
	*/
	$idempresaacesso = cb::idempresa();


	if (empty($strlp)) {
		//die("O usuário não possui configuração de LP ou Setor de Atuação");
	} else {

		GetAssinaResultado($strlp);

		if ($_SESSION["SESSAO"]["FULLACCESS"] == "Y") {
			$sqlmod = "select m.modulo, m.modulopar, m.rotulomenu, m.cssicone, m.tipo, m.modulotipo, m.urldestino, m.ready, 'w' as permissao from " . _DBCARBON . "._modulo m where m.status='ATIVO'";
		} else {
			/* if(empty($_SESSION["SESSAO"]["STRIDUNIDADE"])){
                            die("Erro. Não foi possível identificar a unidade do usuário!!!");
                        }                        
                       
			$sqlmod = "select * from (
						  select m.modulo, m.modulopar, m.rotulomenu, m.cssicone, m.tipo, m.modvinculado, m.urldestino, m.ready, lm.permissao, m.ord
							from "._DBCARBON."._lpmodulo lm, "._DBCARBON."._modulo m,"._DBAPP.".unidadeobjeto o
							where (lm.idlp in (".$strlp.")  AND lm.permissao in ('r','i','w'))
                                                        and o.idobjeto=m.modulo 
                                                        and o.tipoobjeto = 'modulo'
                                                        and o.idunidade in(".$_SESSION["SESSAO"]["STRIDUNIDADE"].")
							and m.modulo = lm.modulo
                                                    UNION
                                                    select m.modulo, m.modulopar, m.rotulomenu, m.cssicone, m.tipo, m.modvinculado, m.urldestino, m.ready, lm.permissao, m.ord
                                                                        from "._DBCARBON."._lpmodulo lm, "._DBCARBON."._modulo m
                                                                        where (lm.idlp in (".$strlp.")  AND lm.permissao in ('r','i','w'))
                                                                        and not exists(select 1 from "._DBAPP.".unidadeobjeto o where  o.idobjeto=m.modulo  and o.idunidade is not null
                                                                                        and o.tipoobjeto = 'modulo')                           
                                                                        and m.modulo = lm.modulo        
                                                    UNION 
                                                    SELECT '_droplet','','','','BTINV','','','','w',99 -- Libera o modulo de droplets caso o dashboard esteja liberado para a LP
                                                                        FROM "._DBCARBON."._lpobjeto o 
                                                                        WHERE o.idlp in (".$strlp.") and o.idobjeto=2 and o.tipoobjeto='_snippet'
                                                ) aa
                        order by ord,modulo,permissao desc;";
                        
                       
                        $sqlmod = "select * from (
						    select m.modulo, m.modulopar, m.rotulomenu, m.cssicone, m.tipo, m.modvinculado, m.urldestino, m.ready, lm.permissao, m.ord
                                                                        FROM "._DBCARBON."._lpmodulo lm, "._DBCARBON."._modulo m
                                                                        WHERE (lm.idlp in (".$strlp.")  AND lm.permissao in ('r','i','w'))
                                                                        AND m.modulo = lm.modulo        
                                                    UNION 
                                                    SELECT '_droplet','','','','BTINV','','','','w',99 -- Libera o modulo de droplets caso o dashboard esteja liberado para a LP
                                                                        FROM "._DBCARBON."._lpobjeto o 
                                                                        WHERE o.idlp in (".$strlp.") and o.idobjeto=2 and o.tipoobjeto='_snippet'
                                                    UNION
                                                    select m.modulo, m.modulopar, m.rotulomenu, m.cssicone, m.tipo, m.modvinculado, m.urldestino, m.ready, 'w' as permissao, m.ord
                                                                        FROM "._DBCARBON."._modulo m
                                                                        WHERE tipo IN ('BTPR')     
                                                ) aa
                        order by ord,modulo,permissao desc;";
 */
			//incluir modulos de pesquisa de resultado para contatos externos caso os mesmos tenham os registros em diagnostico ou autogena
			//se for cliente nao precisa liberar os modulos de pesquisa na LP
			$sqlmodcliente = "";
			if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 3) {
				/*$sqlda="SELECT -- ".date("H:i:s.").gettimeofday()["usec"]." -- maf: marcar a consulta com uma data e hora para debug com show processlist
								(SELECT straight_join
										COUNT(*)
									FROM
										pessoacontato c
											JOIN
										amostra a ON (a.idpessoa = c.idpessoa)
									WHERE
										c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"]." AND a.idunidade = 1) AS diagnostico,
								(SELECT straight_join
										COUNT(*)
									FROM
										pessoacontato c
											JOIN
										amostra a ON (a.idpessoa = c.idpessoa)
									WHERE
										c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"]." AND a.idunidade in (6,9)) AS autogena";
				*/
				$sqlda = "select 
					(SELECT count(*)
					FROM pessoacontato c
					WHERE c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
						AND exists(select 1 from amostra a where a.idpessoa = c.idpessoa AND a.idunidade = 1) limit 1) as diagnostico
					, 
					(SELECT count(*)
					FROM pessoacontato c
					WHERE c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
						AND exists(select 1 from amostra a where a.idpessoa = c.idpessoa AND a.idunidade in (6,9)) limit 1) as autogena";

				//die($sqlda);
				$resad = mysql_query($sqlda) or die("Erro ao consultar resultados dos cliente do usuario: " . mysql_error());
				$rad = mysql_fetch_assoc($resad);
				$inmodam = "";
				if ($rad['diagnostico'] > 0 and $rad['autogena'] > 0) {
					$inmodam = "('cliente_filtrarresultados','cliente_filtrarresultadostra')";
				} elseif ($rad['diagnostico'] > 0) {
					$inmodam = "('cliente_filtrarresultados')";
				} elseif ($rad['autogena'] > 0) {
					$inmodam = "('cliente_filtrarresultadostra')";
				}
				if ($inmodam != "") {
					$sqlmodcliente = "UNION
									select m.modulo, m.modulopar, m.rotulomenu,m.divisor, m.cssicone, m.tipo, m.modulotipo, m.modvinculado, m.urldestino, m.ready,'r' as permissao, 'N',m.ord, oe.empresa as idempresa, '[Módulos padrão]', ''
									FROM " . _DBCARBON . "._modulo m
									left join objempresa oe on oe.objeto = 'modulo' and oe.idobjeto = m.idmodulo 
									WHERE  m.status='ATIVO' and m.modulo in " . $inmodam;
				}
			}
			if ($_POST['idempresa']) {
				$empresas = $_POST["idempresa"];
			} else {
				$empresas = cb::idempresa();
			}
			$sqlmod = "select * from (
												select m.modulo, m.modulopar, m.rotulomenu,m.divisor, m.cssicone, m.tipo, m.modulotipo, m.modvinculado, m.urldestino, m.ready, lm.permissao, lm.solassinatura,m.ord, oe.empresa as idempresa, l.descricao, l.idlp
                                                    FROM " . _DBCARBON . "._lpmodulo lm
													JOIN " . _DBCARBON . "._modulo m ON (m.modulo = lm.modulo and m.status='ATIVO')
													JOIN " . _DBCARBON . "._lp l on l.idlp = lm.idlp
													left join objempresa oe on oe.objeto = 'modulo' and oe.idobjeto = m.idmodulo  and (oe.empresa in (" . $empresas . ") or oe.empresa is null) 
                                                    WHERE (lm.idlp in (" . $strlp . ")  AND lm.permissao in ('r','i','w'))
													 " . getidempresa('l.idempresa', '_lp') . "  
                                                     and exists (select 1 from objempresa o where o.empresa =  l.idempresa and o.objeto = 'modulo' and o.idobjeto = m.idmodulo)
													-- AND case ifnull(m.modulopar,'')
													   -- when '' then 1
													   -- else (select count(*) from " . _DBCARBON . "._lpmodulo lmp where lmp.modulo=m.modulopar and lmp.idlp = lm.idlp)
													-- end = 1
												   AND m.tipo IN ('DROP','LINK','LINKHOME','MODVINC','BTINV','POPUP','SNIPPET')
												" . $sqlmodcliente . " 
												UNION 
												SELECT distinct '_droplet','','','N','','BTINV','','','','','w','N',99,'','','' -- Libera o modulo de droplets caso o dashboard esteja liberado para a LP
																	FROM " . _DBCARBON . "._lpobjeto o 
																	WHERE o.idlp in (" . $strlp . ") and o.idobjeto=2 and o.tipoobjeto='_snippet'
												UNION SELECT m.modulo, m.modulopar,m.rotulomenu,m.divisor,m.cssicone,m.tipo,m.modulotipo,m.modvinculado,m.urldestino,m.ready,lm.permissao,lm.solassinatura,m.ord," . $empresas . " AS idempresa,l.descricao,l.idlp
											FROM " . _DBCARBON . "._lpobjeto o
											JOIN " . _DBCARBON . "._lp l ON (o.idlp = l.idlp)
											JOIN " . _DBCARBON . "._lpmodulo lm ON (l.idlp = lm.idlp)
											JOIN " . _DBCARBON . "._modulo m ON (m.modulo = lm.modulo AND m.status = 'ATIVO')
											WHERE
												o.idlp IN (" . $strlp . ")
												AND o.tipoobjeto = 'empresa'
												AND o.idobjeto = " . $empresas . "
												UNION
													select m.modulo, m.modulopar, m.rotulomenu, m.divisor, m.cssicone, m.tipo, m.modulotipo, m.modvinculado, m.urldestino, m.ready, 'w' as permissao,'N' as solassinatura, m.ord, oe.empresa as idempresa, '[Módulos padrão]', ''
																		FROM " . _DBCARBON . "._modulo m
																		left join objempresa oe on oe.objeto = 'modulo' and oe.idobjeto = m.idmodulo
																		WHERE tipo IN ('BTPR') and m.status='ATIVO'    
											) aa
								order by ord,modulo,permissao desc;";
		}

		$resmod = mysql_query($sqlmod) or die("Erro ao pesquisar modulos do usuário - function: " . mysql_error());

		$arrmod = array();
		$modulo = '';
		$virg = '';
		while ($rm = mysql_fetch_assoc($resmod)) {
			//maf110221: possibilitar ao gestor de LPs visualizacao global dos acessos do usuario
			$arrmod["ARRACESSOSUSUARIO"][$rm["descricao"]][$rm["rotulomenu"]]["modulopar"] = $rm["modulopar"];
			$arrmod["ARRACESSOSUSUARIO"][$rm["descricao"]][$rm["rotulomenu"]]["idlp"] = $rm["idlp"];
			$arrmod["ARRACESSOSUSUARIO"][$rm["descricao"]][$rm["rotulomenu"]]["permissao"] = $rm["permissao"];
			$arrmod["SQLWHEREMODM"] .= $rm["modulo"] . ' - ' . $idempresaacesso . ' - ' . $rm["idempresa"] . '<br>';
			if ($modulo != $rm["modulo"] or $empresa != $rm["idempresa"]) {

				if ((empty($idempresaacesso) and !empty($rm["idempresa"])) or ($rm["idempresa"] == $idempresaacesso) or empty($rm["idempresa"])) {
					//if ( (!empty($idempresaacesso) and !empty($rm["idempresa"]) and $rm["idempresa"] == $idempresaacesso) or empty($rm["idempresa"]) or empty($idempresaacesso)){
					$arrmod["SQLWHEREMOD"] .= $virg . "'" . $rm["modulo"] . "'";
					//$arrmod["SQLWHEREMODM"] .= $virg."'".$rm["modulo"]."'*".$rm["idempresa"];
					//$arrmod["MODULOS"][$rm["modulo"]] = $rm["permissao"];

					// GVT - 22/06/2021 - @462185 Alterar todas as permissões de módulo p/ escrita quando for SUPERUSUARIO
					if ($_SESSION["SESSAO"]["SUPERUSUARIO"] == true) {
						// GVT - 01/02/2022 - Comentado temporariamente a pedido do Marcelo Cunha
						//$rm["permissao"] = 'r';
					}
					$arrmod["MODULOS"][$rm["modulo"]] = $rm;
					$arrmod["CUSTOMCSS"] = $rm["customcss"];
					$arrmod["LPS"] = $strlp;
					$virg = ",";
				}
			}
			$empresa = $rm["idempresa"];
			$modulo = $rm["modulo"];
		}


		//Retorna as Agencias configuradas na LP
		$sqlAgencia = "SELECT group_concat(idobjetovinc) AS idobjetovinc
			  	  		 FROM objetovinculo 
			 	 		WHERE idobjeto IN (" . $strlp . ") AND tipoobjeto = '_lp' AND tipoobjetovinc = 'agencia';";
		$resAgencia = mysql_query($sqlAgencia) or die("Erro ao pesquisar LP Agencia do usuário - function: " . mysql_error());
		$rowAgencia = mysql_fetch_assoc($resAgencia);
		$arrmod["AGENCIAS"] = $rowAgencia['idobjetovinc'];


		$sqlContaItem = "SELECT group_concat(idobjetovinc) AS idobjetovinc
			  	  		 FROM objetovinculo 
			 	 		WHERE idobjeto IN (" . $strlp . ") AND tipoobjeto = '_lp' AND tipoobjetovinc = 'contaitem';";
		$resContaItem = mysql_query($sqlContaItem) or die("Erro ao pesquisar LP ContaItem do usuário - function: " . mysql_error());
		$rowContaItem = mysql_fetch_assoc($resContaItem);
		$arrmod["CONTAITEM"] = $rowContaItem['idobjetovinc'];
		//incluir modulos de pesquisa de resultado para contatos externos caso os mesmos tenham os registros em diagnostico ou autogena
		//se for cliente nao precisa liberar os modulos de pesquisa na LP
		/*
		if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==3){
			$sqlda="SELECT 
							(SELECT 
									COUNT(*)
								FROM
									pessoacontato c
										JOIN
									amostra a ON (a.idpessoa = c.idpessoa)
								WHERE
									c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"]." AND a.idunidade = 1) AS diagnostico,
							(SELECT 
									COUNT(*)
								FROM
									pessoacontato c
										JOIN
									amostra a ON (a.idpessoa = c.idpessoa)
								WHERE
									c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"]." AND a.idunidade = 6) AS autogena";
			$resad = mysql_query($sqlda) or die("Erro ao consultar resultados dos cliente do usuario: ".mysql_error());
			$rad=mysql_fetch_assoc($resad);
			$inmodam="";
			if($rad['diagnostico']>0 and $rad['autogena']>0){
				$inmodam="('cliente_filtrarresultados','cliente_filtrarresultadostra')";
			}elseif($rad['diagnostico']>0){
				$inmodam="('cliente_filtrarresultados')";
			}elseif($rad['autogena']>0){
				$inmodam="('cliente_filtrarresultadostra')";				
			}
			if($inmodam!=""){
				$sqlmod="select m.modulo, m.modulopar, m.rotulomenu, m.cssicone, m.tipo, m.modvinculado, m.urldestino, m.ready,'r' as permissao, m.ord
							FROM "._DBCARBON."._modulo m
							WHERE m.modulo in ".$inmodam;
				$resmod = mysql_query($sqlmod) or die("Erro ao buscar modulos de pesquisa para clientes: ".mysql_error());
				while($rm=mysql_fetch_assoc($resmod)){

						$arrmod["SQLWHEREMOD"] .= $virg."'".$rm["modulo"]."'";
						//$arrmod["MODULOS"][$rm["modulo"]] = $rm["permissao"];
						$arrmod["MODULOS"][$rm["modulo"]] = $rm;
						$arrmod["CUSTOMCSS"] = $rm["customcss"];
						$arrmod["LPS"] = $strlp;
						$virg=",";

				}
			}
			
		}
*/
		return $arrmod;
	}
}

function getAllModsUsr()
{
	$inStr = "";
	$virg = "";
	$sqlWhereModEmpresa = getModsUsr()["SQLWHEREMOD"];

	foreach ($sqlWhereModEmpresa as $empresa => $sqlWhereMod) {
		$inStr .= $virg . $sqlWhereMod;
		$virg = ",";
	}

	return $inStr;
}

/*
 * Recupera os modulos que o usuario tem permissao
 * Esta funcao geralmente eh utilizada após o login com sucesso, e na validação de Tokens
 */
function arrayModulosPorIdPessoa($idpessoa)
{
	if (empty($idpessoa)) die("arrayModulosPorIdPessoa: IdPessoa não informado");

	$usuario = traduzid('pessoa', 'idpessoa', 'usuario', $idpessoa, false);
	$fullAccess = traduzid('_carbonadm', 'usuario', 'fullaccess', $usuario, false);
	$idtipopessoa = traduzid('pessoa', 'idpessoa', 'idtipopessoa', $idpessoa, false);

	$strlp = arrLpSetoresPorIdPessoa($idpessoa, true);

	GetAssinaResultado($strlp);

	if ($fullAccess == "Y") {
		$sqlmod = "SELECT m.modulo, m.modulopar, m.rotulomenu, m.cssicone, m.tipo, m.modulotipo, m.urldestino, m.ready, 'w' as permissao 
			FROM " . _DBCARBON . "._modulo m 
			WHERE m.status='ATIVO'";
	} else {
		//incluir modulos de pesquisa de resultado para contatos externos caso os mesmos tenham os registros em diagnostico ou autogena
		//se for cliente nao precisa liberar os modulos de pesquisa na LP
		$sqlmodcliente = "";

		if ($idtipopessoa == 3) {
			$sqlda = "SELECT 
				(SELECT count(*)
				FROM pessoacontato c
				WHERE c.idcontato = " . $idpessoa . "
					AND exists(SELECT 1 from amostra a where a.idpessoa = c.idpessoa AND a.idunidade = 1) limit 1) as diagnostico, 
				(SELECT count(*)
				FROM pessoacontato c
				WHERE c.idcontato = " . $idpessoa . "
					AND exists(SELECT 1 from amostra a where a.idpessoa = c.idpessoa AND a.idunidade in (6,9)) limit 1) as autogena";

			$resad = mysql_query($sqlda) or die("Erro ao consultar resultados dos cliente do usuario: " . mysql_error());
			$rad = mysql_fetch_assoc($resad);

			$inmodam = "";
			if ($rad['diagnostico'] > 0 and $rad['autogena'] > 0) {
				$inmodam = "('cliente_filtrarresultados','cliente_filtrarresultadostra')";
			} elseif ($rad['diagnostico'] > 0) {
				$inmodam = "('cliente_filtrarresultados')";
			} elseif ($rad['autogena'] > 0) {
				$inmodam = "('cliente_filtrarresultadostra')";
			}

			if ($inmodam != "") {
				$sqlmodcliente = "UNION
					SELECT m.modulo, m.idmodulo, m.modulopar, m.rotulomenu,m.divisor, m.cssicone, m.tipo, m.modulotipo, m.modvinculado, m.urldestino, m.ready,'r' as permissao, 'N',m.ord, oe.empresa as idempresa, '[Módulos padrão]', ''
					FROM " . _DBCARBON . "._modulo m
					LEFT JOIN objempresa oe ON oe.objeto = 'modulo' AND oe.idobjeto = m.idmodulo 
					WHERE m.status='ATIVO' AND m.modulo in " . $inmodam;
			}
		}

		$sqlmod = "SELECT 
				aa.modulo,
				aa.idmodulo,
				aa.modulopar,
				aa.rotulomenu,
				aa.divisor,
				if(a.nome is null, aa.cssicone, CONCAT('/upload/',a.nome)) as cssicone,
				aa.tipo,
				aa.modulotipo,
				aa.modvinculado,
				aa.urldestino,
				aa.ready,
				max(aa.permissao) as permissao,
				aa.solassinatura,
				aa.ord,
				aa.idempresa,
				aa.descricao,
				aa.idlp
			FROM
				(SELECT 
					m.modulo,
						m.idmodulo,
						m.modulopar,
						m.rotulomenu,
						m.divisor,
						m.cssicone,
						m.tipo,
						m.modulotipo,
						m.modvinculado,
						m.urldestino,
						m.ready,
						lm.permissao,
						lm.solassinatura,
						m.ord,
						l.idempresa,
						l.descricao,
						l.idlp
				FROM
					" . _DBCARBON . "._lpmodulo lm
				JOIN " . _DBCARBON . "._modulo m ON (m.modulo = lm.modulo
					AND m.status = 'ATIVO')
				JOIN " . _DBCARBON . "._lp l ON l.idlp = lm.idlp
				WHERE
					(lm.idlp IN (" . $strlp . ")
						AND lm.permissao IN ('r' , 'i', 'w'))
						AND EXISTS( SELECT 
							1
						FROM
							objempresa o
						WHERE
							o.empresa = l.idempresa
								AND o.objeto = 'modulo'
								AND o.idobjeto = m.idmodulo)
						AND m.tipo IN ('DROP' , 'LINK', 'LINKHOME', 'MODVINC', 'BTINV', 'POPUP', 'SNIPPET') UNION SELECT 
						m.modulo,
							m.idmodulo,
							m.modulopar,
							m.rotulomenu,
							m.divisor,
							m.cssicone,
							m.tipo,
							m.modulotipo,
							m.modvinculado,
							m.urldestino,
							m.ready,
							lm.permissao,
							lm.solassinatura,
							m.ord,
							o.idobjeto AS idempresa,
							l.descricao,
							l.idlp
					FROM
						" . _DBCARBON . "._lpobjeto o
					JOIN " . _DBCARBON . "._lp l ON (o.idlp = l.idlp)
					JOIN " . _DBCARBON . "._lpmodulo lm ON (l.idlp = lm.idlp)
					JOIN " . _DBCARBON . "._modulo m ON (m.modulo = lm.modulo
						AND m.status = 'ATIVO')
					WHERE
						o.idlp IN (" . $strlp . ")
							AND o.tipoobjeto = 'empresa' UNION SELECT 
					m.modulo,
						m.idmodulo,
						m.modulopar,
						m.rotulomenu,
						m.divisor,
						m.cssicone,
						m.tipo,
						m.modulotipo,
						m.modvinculado,
						m.urldestino,
						m.ready,
						'w' AS permissao,
						'N' AS solassinatura,
						m.ord,
						oe.empresa AS idempresa,
						'[Módulos padrão]',
						''
				FROM
					" . _DBCARBON . "._modulo m
				LEFT JOIN objempresa oe ON oe.objeto = 'modulo'
					AND oe.idobjeto = m.idmodulo
				WHERE
					tipo IN ('BTPR') AND m.status = 'ATIVO'
					" . $sqlmodcliente . " 
				) aa
				LEFT JOIN
					arquivo a ON (a.idobjeto = aa.idmodulo
						AND a.tipoobjeto = '_modulo'
						AND a.tipoarquivo = 'SVG')
			GROUP BY modulo , idempresa
			ORDER BY idempresa , ord , modulo , permissao DESC";
	}

	$resmod = mysql_query($sqlmod) or die("Erro ao pesquisar modulos do usuário - function: " . mysql_error());

	$arrmod = array(
		'LPS' => $strlp,
		'AGENCIAS' => '',
		'SQLWHEREMOD' => [],
		'MODULOS' => [],
	);

	$virg = '';
	$empresa = null;

	while ($rm = mysql_fetch_assoc($resmod)) {

		if ($empresa != $rm["idempresa"]) {
			$empresa = strval($rm["idempresa"]);
			$virg = "";
		}
		if (!empty($empresa)) {
			$arrmod["MODULOS"][$empresa][$rm["modulo"]] = $rm;
			$arrmod["SQLWHEREMOD"][$empresa] .= $virg . "'" . $rm["modulo"] . "'";
		}

		$virg = ",";
	}

	//Retorna as Agencias configuradas na LP
	$sqlAgencia = "SELECT group_concat(idobjetovinc) AS idobjetovinc
			FROM objetovinculo 
			WHERE tipoobjeto = '_lp' AND tipoobjetovinc = 'agencia' AND idobjeto IN (" . $strlp . ")";
	$resAgencia = mysql_query($sqlAgencia) or die("Erro ao pesquisar LP Agencia do usuário - function: " . mysql_error());
	$rowAgencia = mysql_fetch_assoc($resAgencia);
	$arrmod["AGENCIAS"] = $rowAgencia['idobjetovinc'];

	$sqlContaItem = "SELECT group_concat(idobjetovinc) AS idobjetovinc
			  	  		 FROM objetovinculo 
			 	 		WHERE idobjeto IN (" . $strlp . ") AND tipoobjeto = '_lp' AND tipoobjetovinc = 'contaitem';";
	$resContaItem = mysql_query($sqlContaItem) or die("Erro ao pesquisar LP ContaItem do usuário - function: " . mysql_error());
	$rowContaItem = mysql_fetch_assoc($resContaItem);
	$arrmod["CONTAITEM"] = $rowContaItem['idobjetovinc'];

	return $arrmod;
}

function arrLpSetoresPorIdPessoa($idpessoa, $inStr = false)
{
	if (empty($idpessoa)) die("arrLpSetoresPorIdPessoa: IdPessoa não informado");

	$slps = "SELECT l.idlp
		FROM pessoaobjeto ps, sgarea s, lpobjeto o, " . _DBCARBON . "._lp l
		WHERE ps.idpessoa = " . $idpessoa . "
			AND s.idsgarea = ps.idobjeto
			AND ps.tipoobjeto = 'sgarea'
			AND s.status = 'ATIVO'
			AND o.idobjeto = s.idsgarea
			AND o.tipoobjeto = 'sgarea'
			AND l.status = 'ATIVO'
			AND l.idlp = o.idlp
        UNION
			SELECT l.idlp
			FROM pessoaobjeto ps, sgdepartamento s, lpobjeto o, " . _DBCARBON . "._lp l
			WHERE ps.idpessoa = " . $idpessoa . "
				AND s.idsgdepartamento = ps.idobjeto
				AND ps.tipoobjeto = 'sgdepartamento'
				AND s.status = 'ATIVO'
				AND o.idobjeto = s.idsgdepartamento
				AND o.tipoobjeto = 'sgdepartamento'
				AND l.status = 'ATIVO'
				AND l.idlp = o.idlp
		UNION
			SELECT l.idlp
			FROM pessoaobjeto ps, sgconselho s, lpobjeto o, " . _DBCARBON . "._lp l
			WHERE ps.idpessoa = " . $idpessoa . "
				AND s.idsgconselho = ps.idobjeto
				AND ps.tipoobjeto = 'sgconselho'
				AND s.status = 'ATIVO'
				AND o.idobjeto = s.idsgconselho
				AND o.tipoobjeto = 'sgconselho'
				AND l.status = 'ATIVO'
				AND l.idlp = o.idlp
        UNION
			SELECT l.idlp
			FROM pessoaobjeto ps, sgsetor s, lpobjeto o, " . _DBCARBON . "._lp l
			WHERE ps.idpessoa = " . $idpessoa . "
				AND s.idsgsetor=ps.idobjeto
				AND ps.tipoobjeto = 'sgsetor'
				AND s.status = 'ATIVO'
				AND o.idobjeto = s.idsgsetor
				AND o.tipoobjeto = 'sgsetor'
				AND l.status = 'ATIVO'
				AND l.idlp = o.idlp
        UNION
			SELECT l.idlp
			FROM lpobjeto ps, " . _DBCARBON . "._lp l
			WHERE l.idlp = ps.idlp
				AND l.status = 'ATIVO'
				AND ps.tipoobjeto = 'pessoa' 
				AND ps.idobjeto = " . $idpessoa . "
		UNION
			SELECT o.idlp 
			FROM " . _DBCARBON . "._lpobjeto o
				JOIN " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' AND l.status = 'ATIVO') 
				JOIN lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'pessoa' AND ps.idobjeto = " . $idpessoa . ")
		UNION
			SELECT o.idlp 
			FROM " . _DBCARBON . "._lpobjeto o
				JOIN " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' AND l.status = 'ATIVO') 
				JOIN lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'sgsetor')
				JOIN pessoaobjeto po ON (po.tipoobjeto = 'sgsetor' AND po.idobjeto = ps.idobjeto AND po.idpessoa = " . $idpessoa . ")
		UNION
			SELECT o.idlp 
			FROM " . _DBCARBON . "._lpobjeto o
				JOIN " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' AND l.status = 'ATIVO') 
				JOIN lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'sgdepartamento')
				JOIN pessoaobjeto po ON (po.tipoobjeto = 'sgdepartamento' AND po.idobjeto = ps.idobjeto AND po.idpessoa = " . $idpessoa . ")
		UNION
			SELECT o.idlp 
			FROM " . _DBCARBON . "._lpobjeto o
				JOIN " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' AND l.status = 'ATIVO') 
				JOIN lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'sgarea')
				JOIN pessoaobjeto po ON (po.tipoobjeto = 'sgarea' AND po.idobjeto = ps.idobjeto AND po.idpessoa = " . $idpessoa . ")
	";

	$rlp = mysql_query($slps) or die("arrLpSetoresPorIdPessoa: " . mysql_error());

	$qtdlp = mysql_num_rows($rlp);

	if ($qtdlp == 0) { //buscar LPS vinculadas por tipopessoa
		$slps = "SELECT l.idlp
			FROM pessoa p, " . _DBCARBON . "._lp l
			WHERE l.idtipopessoa = p.idtipopessoa
				AND l.status='ATIVO'
				AND p.idpessoa = " . $idpessoa;
		$rlp = mysql_query($slps) or die("arrLpSetoresPorIdPessoa por tipopessoa: " . mysql_error());
	}

	$arrRet = [];
	$arrRetAspas = [];

	while ($r = mysql_fetch_assoc($rlp)) {
		$arrRet[] = $r["idlp"];
		$arrRetAspas[] = "'" . $r["idlp"] . "'";
	}

	if (empty($arrRet) && empty($arrRetAspas)) {
		die("Colaborador sem Lista de Permissão #1");
	}

	if ($inStr) {
		return implode(",", $arrRetAspas);
	} else {
		return $arrRet;
	}
}

/*
 * É possível realizar a configuração de LPs utilizando os setores de atuação do usuário, para facilitar permissões
 * Cada usuário, então, pode estar ligado a mais de 1 LP
 */
function arrLpSetores($inStr = false, $ignoraEmpresa = false, $somenteEmpresaAtual = false)
{
	$empresa = ($ignoraEmpresa) ? '' : ' and l.idempresa = ' . cb::idempresa();
	$idpessoa = (!empty($_SESSION["SESSAO"]["IDPESSOA"])) ? $_SESSION["SESSAO"]["IDPESSOA"] : cb::$usr["IDPESSOA"];

	if (!$somenteEmpresaAtual) {
		$empresa1 = ($ignoraEmpresa) ? '' : ' and o.idobjeto = ' . cb::idempresa();

		$select = "UNION
		SELECT o.idlp 
		FROM " . _DBCARBON . "._lpobjeto o
			join " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' " . $empresa1 . " AND l.status = 'ATIVO') 
			join lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'pessoa' AND ps.idobjeto = " . $idpessoa . ")
			UNION
		SELECT o.idlp 
		FROM " . _DBCARBON . "._lpobjeto o
			join " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' " . $empresa1 . " AND l.status = 'ATIVO') 
			join lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'sgsetor')
			join pessoaobjeto po ON (po.tipoobjeto = 'sgsetor' AND po.idobjeto = ps.idobjeto AND po.idpessoa = " . $idpessoa . ")
			UNION
		SELECT o.idlp 
		FROM " . _DBCARBON . "._lpobjeto o
			join " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' " . $empresa1 . " AND l.status = 'ATIVO') 
			join lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'sgdepartamento')
			join pessoaobjeto po ON (po.tipoobjeto = 'sgdepartamento' AND po.idobjeto = ps.idobjeto AND po.idpessoa = " . $idpessoa . ")
			UNION
		SELECT o.idlp 
		FROM " . _DBCARBON . "._lpobjeto o
			join " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' " . $empresa1 . " AND l.status = 'ATIVO') 
			join lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'sgarea')
			join pessoaobjeto po ON (po.tipoobjeto = 'sgarea' AND po.idobjeto = ps.idobjeto AND po.idpessoa = " . $idpessoa . ")";
	}

	$slps = "select l.idlp
                    from pessoaobjeto ps
                        ,sgarea s
                        ,lpobjeto o
                        ," . _DBCARBON . "._lp l
                    where ps.idpessoa=" . $idpessoa . "
					" . $empresa . "					
                    and s.idsgarea=ps.idobjeto
					and ps.tipoobjeto = 'sgarea'
                    and s.status='ATIVO'
                    and o.idobjeto = s.idsgarea
                    and o.tipoobjeto = 'sgarea'
                    and l.status='ATIVO'
                    and l.idlp=o.idlp
                UNION
				select l.idlp
                    from pessoaobjeto ps
                        ,sgdepartamento s
                        ,lpobjeto o
                        ," . _DBCARBON . "._lp l
                    where ps.idpessoa=" . $idpessoa . "
					" . $empresa . "					
                    and s.idsgdepartamento=ps.idobjeto
					and ps.tipoobjeto = 'sgdepartamento'
                    and s.status='ATIVO'
                    and o.idobjeto = s.idsgdepartamento
                    and o.tipoobjeto = 'sgdepartamento'
                    and l.status='ATIVO'
                    and l.idlp=o.idlp
				UNION
				select l.idlp
					from pessoaobjeto ps
						,sgconselho s
						,lpobjeto o
						," . _DBCARBON . "._lp l
					where ps.idpessoa=" . $idpessoa . "
					" . $empresa . "					
					and s.idsgconselho=ps.idobjeto
					and ps.tipoobjeto = 'sgconselho'
					and s.status='ATIVO'
					and o.idobjeto = s.idsgconselho
					and o.tipoobjeto = 'sgconselho'
					and l.status='ATIVO'
					and l.idlp=o.idlp
                UNION
				select l.idlp
                    from pessoaobjeto ps
                        ,sgsetor s
                        ,lpobjeto o
                        ," . _DBCARBON . "._lp l
                    where ps.idpessoa=" . $idpessoa . "
					" . $empresa . "					
                    and s.idsgsetor=ps.idobjeto
					and ps.tipoobjeto = 'sgsetor'
                    and s.status='ATIVO'
                    and o.idobjeto = s.idsgsetor
                    and o.tipoobjeto = 'sgsetor'
                    and l.status='ATIVO'
                    and l.idlp=o.idlp
                UNION
                SELECT l.idlp
                    FROM 
                        lpobjeto ps," . _DBCARBON . "._lp l
                    where l.idlp=ps.idlp
                    and l.status='ATIVO'
                     " . $empresa . "
                    and ps.tipoobjeto ='pessoa' 
                    and ps.idobjeto = " . $idpessoa . " 
					" . $select . "
					";

	$rlp = mysql_query($slps) or die("arrLpSetores: " . mysql_error());

	$qtdlp = mysql_num_rows($rlp);

	if ($qtdlp == 0) { //buscar LPS vinculadas por tipopessoa
		$slps = "select l.idlp
                    from pessoa p ," . _DBCARBON . "._lp l
                    where l.idtipopessoa = p.idtipopessoa
                    and l.status='ATIVO'
                     " . getidempresa('l.idempresa', '_lp') . "
                    and p.idpessoa=" . $_SESSION["SESSAO"]["IDPESSOA"];
		$rlp = mysql_query($slps) or die("arrLpSetores por tipopessoa: " . mysql_error());
	}

	$arrRet = array();
	$arrRetAspas = array();
	while ($r = mysql_fetch_assoc($rlp)) {
		$arrRet[] = $r["idlp"];
		$arrRetAspas[] = "'" . $r["idlp"] . "'";
	}

	if (empty($arrRet) && empty($arrRetAspas)) {
		die("Colaborador sem Lista de Permissão #2");
	}
	if ($inStr) {
		return implode(",", $arrRetAspas);
	} else {
		return $arrRet;
	}
}


function arrLpSetoresPessoas($idpessoa, $inStr = false, $ignoraEmpresa = false, $somenteEmpresaAtual = false)
{
	$empresa = ($ignoraEmpresa) ? '' : ' and l.idempresa= ' . cb::idempresa();

	if (!$somenteEmpresaAtual) {
		$empresa1 = ($ignoraEmpresa) ? '' : ' and o.idobjeto = ' . cb::idempresa();

		$select = "UNION
		SELECT o.idlp 
		FROM " . _DBCARBON . "._lpobjeto o
			join " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' " . $empresa1 . " AND l.status = 'ATIVO') 
			join lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'pessoa' AND ps.idobjeto = " . $idpessoa . ")
			UNION
		SELECT o.idlp 
		FROM " . _DBCARBON . "._lpobjeto o
			join " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' " . $empresa1 . " AND l.status = 'ATIVO') 
			join lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'sgsetor')
			join pessoaobjeto po ON (po.tipoobjeto = 'sgsetor' AND po.idobjeto = ps.idobjeto AND po.idpessoa = " . $idpessoa . ")
			UNION
		SELECT o.idlp 
		FROM " . _DBCARBON . "._lpobjeto o
			join " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' " . $empresa1 . " AND l.status = 'ATIVO') 
			join lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'sgdepartamento')
			join pessoaobjeto po ON (po.tipoobjeto = 'sgdepartamento' AND po.idobjeto = ps.idobjeto AND po.idpessoa = " . $idpessoa . ")
			UNION
		SELECT o.idlp 
		FROM " . _DBCARBON . "._lpobjeto o
			join " . _DBCARBON . "._lp l ON (o.idlp = l.idlp AND o.tipoobjeto = 'empresa' " . $empresa1 . " AND l.status = 'ATIVO') 
			join lpobjeto ps ON (ps.idlp = l.idlp AND ps.tipoobjeto = 'sgarea')
			join pessoaobjeto po ON (po.tipoobjeto = 'sgarea' AND po.idobjeto = ps.idobjeto AND po.idpessoa = " . $idpessoa . ")";
	}

	$slps = "select l.idlp
                    from pessoaobjeto ps
                        ,sgarea s
                        ,lpobjeto o
                        ," . _DBCARBON . "._lp l
                    where ps.idpessoa=" . $idpessoa . "
					" . $empresa . "					
                    and s.idsgarea=ps.idobjeto
					and ps.tipoobjeto = 'sgarea'
                    and s.status='ATIVO'
                    and o.idobjeto = s.idsgarea
                    and o.tipoobjeto = 'sgarea'
                    and l.status='ATIVO'
                    and l.idlp=o.idlp
                UNION
				select l.idlp
                    from pessoaobjeto ps
                        ,sgdepartamento s
                        ,lpobjeto o
                        ," . _DBCARBON . "._lp l
                    where ps.idpessoa=" . $idpessoa . "
					" . $empresa . "					
                    and s.idsgdepartamento=ps.idobjeto
					and ps.tipoobjeto = 'sgdepartamento'
                    and s.status='ATIVO'
                    and o.idobjeto = s.idsgdepartamento
                    and o.tipoobjeto = 'sgdepartamento'
                    and l.status='ATIVO'
                    and l.idlp=o.idlp
                UNION
				select l.idlp
                    from pessoaobjeto ps
                        ,sgsetor s
                        ,lpobjeto o
                        ," . _DBCARBON . "._lp l
                    where ps.idpessoa=" . $idpessoa . "
					" . $empresa . "					
                    and s.idsgsetor=ps.idobjeto
					and ps.tipoobjeto = 'sgsetor'
                    and s.status='ATIVO'
                    and o.idobjeto = s.idsgsetor
                    and o.tipoobjeto = 'sgsetor'
                    and l.status='ATIVO'
                    and l.idlp=o.idlp
                UNION
                SELECT l.idlp
                    FROM 
                        lpobjeto ps," . _DBCARBON . "._lp l
                    where l.idlp=ps.idlp
                    and l.status='ATIVO'
                     " . $empresa . "
                    and ps.tipoobjeto ='pessoa' 
                    and ps.idobjeto = " . $idpessoa . " 
					" . $select . "
					";

	$rlp = mysql_query($slps) or die("arrLpSetores: " . mysql_error());

	$qtdlp = mysql_num_rows($rlp);

	if ($qtdlp == 0) { //buscar LPS vinculadas por tipopessoa
		$slps = "select l.idlp
                    from pessoa p ," . _DBCARBON . "._lp l
                    where l.idtipopessoa = p.idtipopessoa
                    and l.status='ATIVO'
                     " . getidempresa('l.idempresa', '_lp') . "
                    and p.idpessoa=" . $idpessoa;
		$rlp = mysql_query($slps) or die("arrLpSetores por tipopessoa: " . mysql_error());
	}

	$arrRet = array();
	$arrRetAspas = array();
	while ($r = mysql_fetch_assoc($rlp)) {
		$arrRet[] = $r["idlp"];
		$arrRetAspas[] = "'" . $r["idlp"] . "'";
	}

	if ($inStr) {
		return implode(",", $arrRetAspas);
	} else {
		return $arrRet;
	}
}

/*
 * SE uma lp do usuario tiver o snippet 3 de assinatura o mesmo pode assinar resultados
 */
function GetAssinaResultado($strlp)
{
	//snnippet 3 e o de assinatura
	$sql = "select s.idsnippet,s.snippet	
            from " . _DBCARBON . "._snippet s
             join " . _DBCARBON . "._lpobjeto o on o.idlp in (" . $strlp . ")
                    and o.tipoobjeto='_snippet' 
                    and o.idobjeto=s.idsnippet
                    and s.idsnippet = 3
                    and s.status='ATIVO'";
	$rlp = mysql_query($sql) or die("GetAssinaResultado: " . mysql_error());

	$qtdlp = mysql_num_rows($rlp);

	if ($qtdlp > 0) {
		$_SESSION["SESSAO"]["ASSINARESULTADO"] = 'Y';
	} else {
		$_SESSION["SESSAO"]["ASSINARESULTADO"] = 'N';
	}
} //function GetAssinaResultado($strlp){

/*
 * Log de acesso
 */
function pessoaLog($inacao)
{

	logBinario(false);

	$_sqlacc = "insert into pessoalog (idpessoa,acao,pag,qs,browser,ip,criadoem)
				values (" . $_SESSION["SESSAO"]["IDPESSOA"] . ",'" . $inacao . "','" . mysql_real_escape_string($_SERVER['SCRIPT_NAME']) . "','" . mysql_real_escape_string($_SERVER['QUERY_STRING']) . "','" . mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']) . "','" . $_SERVER['REMOTE_ADDR'] . "',now())";
	//die($_sqlacc);
	$res = mysql_query($_sqlacc); //or die("<!-- \nErro: ".mysql_error()."\nSql:\n".$_sqlacc." -->");

	if (!$res) {
		//echo("<!-- FALHA AO ATUALIZAR SISTEMA DE PRESENCA ONLINE: ".mysql_error()." --> \n");
		die("Falha ao atualizar o log de acesso: " . mysql_error());
	}

	//Ativa novamente o log binário
	logBinario(true);
}

/*
 * Ativar ou desativar o log binário, para evitar informações desnecessárias no LOG;
 * ATENÇÃO: Utilizar sempre em conjunto: logBinario(false), e logo após o comando a ser ignorado, reativar utizando logBinario(true)
 *          Caso esta regra não seja seguida, a sincronização pode sofrer falhas
 */
function logBinario($inAtivar = true)
{
	$iAtivar = ($inAtivar) ? "1" : "0";
	d::b()->query("SET sql_log_bin = " . $iAtivar);
}

/*
 * Estruturar hierarquicamente os modulos: modulopar -> modulo (...)
 */
function formataArrayModulos($tree, $parent = "")
{
	$tree2 = array();
	foreach ($tree as $i => $item) {
		if ($item['modulopar'] == $parent  and ($item["tipo"] == 'DROP' or $item["tipo"] == 'LINK' or $item["tipo"] == 'LINKHOME' or $item["tipo"] == 'MODVINC') and !($item['modulopar'] == "" and $item["tipo"] == 'MODVINC')) {
			$tree2[$item['modulo']] = $item;
			$tree2[$item['modulo']]['sub'] = formataArrayModulos($tree, $item['modulo']);
		}
	}

	return $tree2;
}

/*
 * Recuperar botões de snippets, para serem colocados como ações de acesso rápido
 */
function getSnippets($tipo = '')
{
	global $_headers;

	$idempresa = cb::idempresa();
	$lps = getModsUsr('LPS');
	$arrRetorno = [];

	if (empty($lps))
		return [];

	if ($_headers["cb-canal"] == "app") {
		$mostrarMenu = '&_menu=N';
	}

	$snippets = _SnippetController::buscarSnippetsPorLpIdEmpresaEModulos($lps, $idempresa, getModsUsr('SQLWHEREMOD'), $tipo, $mostrarMenu);

	foreach ($snippets as $key => $snippet) {
		if ($snippet['modulopar']) {
			$arrRetorno[$snippet['modulopar']][$key] = $snippet;
		}

		$arrRetorno['padrao'][$key] = $snippet;
	}

	return $arrRetorno;
}

function modalAlterarEmpresaMenuSuperior(): String
{
	$cssLogado = var_export(logado(), true);
	$versaoSistema = $_SESSION["SESSAO"]["VERSAOSISTEMA"] ? "Versão sistema: " . $_SESSION["SESSAO"]["VERSAOSISTEMA"] : "";
	$modalAlteraEmpresaHTML = "";

	if (!empty($_SESSION["SESSAO"]["IDPESSOA"])) {
		$_sqlempresa = "SELECT idempresa,corsistema,iconelateral,iconemodal 
			FROM empresa 
			WHERE status='ATIVO'
				AND idempresa in (
					SELECT empresa 
					FROM objempresa 
					WHERE idobjeto=" . $_SESSION["SESSAO"]["IDPESSOA"] . " 
					AND objeto='pessoa'
				)";
	} else {
		$_sqlempresa = "SELECT idempresa,corsistema,iconelateral,iconemodal FROM empresa WHERE status='ATIVO'";
	}

	$_resempresa = d::b()->query($_sqlempresa) or die("Erro ao buscar Empresas: Erro: " . mysqli_error(d::b()) . "\n" . $_sqlempresa);
	$arrEmpresas = [];
	$ii = 0;

	while ($_rowempresa = mysqli_fetch_assoc($_resempresa)) {
		if ($_rowempresa["idempresa"] == cb::idempresa() and $_SESSION["SESSAO"]["SUPERUSUARIO"] != true) {
			$corSistema = $_rowempresa["corsistema"];
			$_url = "." . preg_replace('/(^\.+)/', '', $_rowempresa["iconelateral"]);
		}

		if ($_rowempresa["idempresa"] != cb::idempresa()) {
			$arrEmpresas[$ii]["idempresa"] = $_rowempresa["idempresa"];
			$arrEmpresas[$ii]["iconemodal"] = "." . preg_replace('/(^\.+)/', '', $_rowempresa["iconemodal"]);
			$ii++;
		}
	}

	$arrEmpresas = json_encode($arrEmpresas);

	$modalAlteraEmpresaHTML .= "	
	<script>
		$('#cbMenuSuperior').attr('style', 'background-color: $corSistema !important');
		$('#cbMenuSuperior').attr('idempresa', " . cb::idempresa() . ");
	</script>
	<a id='cblogomenu' href='javascript:alterarEmpresaModal($arrEmpresas);'>
		<li class='h-100 d-flex justify-content-center align-items-center px-3'>
			<div title='$versaoSistema'>
				<img src='$_url' alt=''>
			</div>
		</li>
	</a>";

	return $modalAlteraEmpresaHTML;
}

/*
 * Menu superior da aplicação
 */
function montaModulosMenuLateral(): String
{
	global $_headers;

	$mostrarMenu = "";
	$menuLateralHTML = "";
	$idempresa = cb::idempresa();

	$linkIdempresa = "&_idempresa=" . $idempresa;

	//maf: 181020: não montar dentro de webviews no app
	if ($_headers["cb-canal"] == "webview") {
		return false;
	} else if ($_headers["cb-canal"] == "app") {
		$mostrarMenu = '&_menu=N';
	}

	//Cria cor de back e foreground para o avatar do usuário
	$bg = str2Color($_SESSION["SESSAO"]["NOME"]);
	$fc = colorContrastYIQ($bg);

	$urllogout = "?_acao=logout";

	$modsEmpresa = getModsUsr("MODULOS");
	$sqlWhereMod = getModsUsr("SQLWHEREMOD");

	$qr = "SELECT 
			m.modulo
		FROM
			objempresa o
				JOIN
			" . _DBCARBON . "._modulo m ON (o.idobjeto = m.idmodulo)
		WHERE
			o.objeto = 'modulo' AND o.empresa = " . cb::idempresa() . "
				AND m.modulo IN (" . $sqlWhereMod . ")";
	$rs = d::b()->query($qr);
	$arrMod = array();
	while ($rw = mysqli_fetch_assoc($rs)) {
		$arrMod[] = $rw["modulo"];
	}
	foreach ($modsEmpresa as $i => $item) {
		if (!in_array($i, $arrMod))
			unset($modsEmpresa[$i]);

		if (in_array($i, $arrMod) && $i == 'contatomenurapido' && $_SESSION["SESSAO"]["IDPESSOA"] == 112378)
			unset($modsEmpresa[$i]);
	}

	$arrMenu = formataArrayModulos($modsEmpresa);

	foreach ($arrMenu as $modulo => $menu) {

		$iSubMenu = count($menu["sub"]);

		$classDrop = "nav-item px-3 rounded";
		$link = "?_modulo=" . $modulo . $linkIdempresa . $mostrarMenu;

		if ($iSubMenu > 0) {
			$link = "#menu-lateral-$modulo";
		}

		if (empty($menu["cssicone"])) {
			$icone = "<span class='visible-sm visible-xs'>" . strtoupper(substr($menu["rotulomenu"], 0, 2)) . "</span>";
		} else if (strpos($menu["cssicone"], "/upload/") === 0) {
			$icone = "<img style='width:16px;margin-right:6px;' src='." . $menu["cssicone"] . "'/>";
		} else {
			$icone = "<i class='" . $menu["cssicone"] . "'></i>";
		}

		$dataCollapse = $iSubMenu ? 'data-toggle="collapse"' : '';

		$menuLateralHTML .= "<div cbmodulo='$modulo' class='$classDrop' onclick='abrirMenu()'>
			<a href='$link' class='nav-link d-flex align-items-center px-0 rounded white-space-nowrap' $dataCollapse>
				<div class='menu-icon'>$icone</div>
				<span class='cbMenuSuperiorTitle ml-4'>{$menu['rotulomenu']}</span>";

		if ($iSubMenu > 0) {
			$menuLateralHTML .= "<i class='dropdown-icon transition fa fa-chevron-down'></i>";
		}

		$menuLateralHTML .= "</a>";

		if ($iSubMenu > 0) {
			$menuLateralHTML .= "<ul id='menu-lateral-$modulo' class='collapse pt-3 pl-0 w-100' role='menu'>";

			foreach ($menu['sub'] as $submenu => $item) {
				$bordaInferior = $item['divisor'] == 'Y' ? 'border-t-1 border-gray-50' : '';
				$menuLateralHTML .= "<a href='?_modulo={$item['modulo']}{$linkIdempresa}{$mostrarMenu}' class='nav-link px-0 white-space-nowrap'>
					<li cbmodulo='$submenu' class='rounded $bordaInferior'>
						<div class='w-100'>{$item['rotulomenu']}</div>
					</li>		
				</a>";
			}

			$menuLateralHTML .= "</ul>";
		}

		$menuLateralHTML .= "</div>";
	}

	$menuLateralHTML .= "
	<script>
		$('#ramais').click(function(){
			CB.modal({
				url:'?_modulo=ramalcolaboradores',
				header:'Ramais',
				menu: false
			});
		});
	</script>";

	if ($_SESSION["SESSAO"]["PERMISSAOCHAT"] == "Y" and $_SESSION["SESSAO"]["SUPERUSUARIO"] != true) {
		$menuLateralHTML .=	"<div id='cbNotificacoes' class='dropdown pull-right snippet' onclick='chat.abrirContainerChat();chat.montarContatos();chat.recuperarAvatar();chat.maximizar();'>
			<a id='cbBadge' href='#' class='fa fa-comment hide' data-toggle='dropdown' role='button' aria-expanded='false'>
				<span id='cbIBadge' class='badge fundovermelho' style='display: none;'></span>
			</a>
			<ul id='cbListaNotificacoes' class='Xdropdown-menu' role='Xmenu' style='padding: 0px;border: 0px;display: none;'>
				<li id='cbListaNotificacoesHeader' style='padding: 4px 10px;'>
					<table style='width: 100%;'>
						<tbody><tr><td style='color: silver;font-weight: bold;'>Notificações:</td>
							<td></td><td><span class='azul pointer' onclick='chat.marcarLida('*')'>Marcar todas como lidas</span></td>
							<td><a href='#'><i class='fa fa-cog azul pointer'></i></a></td>
						</tr>
					</tbody></table>
				</li>
				<li id='cbListaNotificacoesFooter' style='padding: 4px 10px;'>
					<table style='width: 100%;'>
					<tbody>
					<tr>
						<td></td>
						<td >
							<a href='javascript:chat.abrirContainerChat();chat.maximizar();chat.montarContatos();chat.recuperarAvatar();' class='pointer floatright'>Abrir janela de Chat</a>
						</td>
					</tr>
					</tbody>
					</table>
				</li>
			</ul>
		</div>";
	}

	return $menuLateralHTML;
}

function montarMenuSuperiorAntigo()
{
	global $_headers;

	$idempresa = cb::idempresa();
	$menuSuperiorHTML = '';

	$linkIdempresa = "&_idempresa=" . $idempresa;

	//maf: 181020: não montar dentro de webviews no app
	if ($_headers["cb-canal"]  == "webview") {
		return false;
	}

	//Cria cor de back e foreground para o avatar do usuário
	$bg = str2Color($_SESSION["SESSAO"]["NOME"]);
	$fc = colorContrastYIQ($bg);

	$urllogout = "?_acao=logout";

	$modsEmpresa = getModsUsr("MODULOS");
	$sqlWhereMod = getModsUsr("SQLWHEREMOD");

	$qr = "SELECT 
			m.modulo
		FROM
			objempresa o
				JOIN
			" . _DBCARBON . "._modulo m ON (o.idobjeto = m.idmodulo)
		WHERE
			o.objeto = 'modulo' AND o.empresa = " . cb::idempresa() . "
				AND m.modulo IN (" . $sqlWhereMod . ")";
	$rs = d::b()->query($qr);
	$arrMod = array();
	while ($rw = mysqli_fetch_assoc($rs)) {
		$arrMod[] = $rw["modulo"];
	}
	foreach ($modsEmpresa as $i => $item) {
		if (!in_array($i, $arrMod))
			unset($modsEmpresa[$i]);
	}

	$arrMenu = formataArrayModulos($modsEmpresa);

	foreach ($arrMenu as $modulo => $menu) {

		$iSubMenu = count($menu["sub"]);

		if ($iSubMenu > 0) {
			$classDrop = "dropdown";
			$link = "javascript:void(0)";
		} else {
			$classDrop = "";
			$link = "?_modulo=" . $modulo . $linkIdempresa;
		}

		if (empty($menu["cssicone"])) {
			$icone = "<span class='visible-sm visible-xs'>" . strtoupper(substr($menu["rotulomenu"], 0, 2)) . "</span>";
		} else if (strpos($menu["cssicone"], "/upload/") === 0) {
			$icone = file_get_contents("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$menu["cssicone"]}");
		} else {
			$icone = "<i class='" . $menu["cssicone"] . "'></i>";
		}

		$menuSuperiorHTML .= "<li cbmodulo='$modulo' class='$classDrop'>
		 <a href='$link'>
			 $icone
			 <span class='cbMenuSuperiorTitle'>
				 {$menu['rotulomenu']}
			 </span>
		 </a>";

		if ($iSubMenu > 0) {
			$menuSuperiorHTML .= "<ul class='dropdown-menu' style='max-height: 550px;overflow-y: scroll;' role='menu'>";

			foreach ($menu['sub'] as $submenu => $item) {
				$bordaInferior = $item['divisor'] == 'Y' ? 'border-b-1 border-gray-50' : '';

				$menuSuperiorHTML .=	"<li cbmodulo='$submenu' class='$bordaInferior'>
										<a href='?_modulo={$item['modulo']}$linkIdempresa'>{$item['rotulomenu']}</a>
									</li>";
			}

			$menuSuperiorHTML .= "</ul>";
		}

		$menuSuperiorHTML .= "</li>";
	}

	$menuSuperiorHTML .= "<li class='dropdown pull-right bold' cbidpessoa='{$_SESSION['SESSAO']['IDPESSOA']}' title='{$_SESSION['SESSAO']['NOME']} LP: {$_SESSION['SESSAO']['IDLP']}' style='order:999;background-color:#$bg;'>
							<a href='#' class='dropdown-toggle' style='color:$fc;' data-toggle='dropdown' role='button' aria-expanded='false'>" . substr(tirarAcentos($_SESSION['SESSAO']['NOME']), 0, 2) . "<span class='caret'></span></a>
							<ul class='dropdown-menu' role='menu'>";

	if ($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) {
		$menuSuperiorHTML .=	"
			<li><a href='javascript:janelamodal(\"report/relfuncionario.php\");'><i class='fa fa-info-circle vermelho'></i>&nbsp;Minhas Informações</a></li>
			<li class='divider'></li>
			<li><a href='?_modulo=eventoponto$linkIdempresa'><i class='fa fa-clock-o vermelho'></i>&nbsp;Ponto</a></li>
			<li class='divider'></li>
			<li id='ramais'><a href='#'><i class='fa fa-phone-square vermelho'></i>&nbsp;Ramais</a></li>
			<li class='divider'></li>
			<li><a href='?_modulo=linkaplicativo' class='svg-red'>
				<img src='inc/img/apple.png' alt='IOS' style='width: 20px; margin-left: -4px;'>
				<span style='margin-top: -19px; margin-left: 16px;'>APP IOS</span></a>
			</li>";
	}

	if ($_SESSION['SESSAO']['IDTIPOPESSOA'] == 15 or $_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) {
		$sql = 'SELECT p.idpessoa 
				from pessoacontato c
					join pessoa p on(p.idpessoa=c.idpessoa and p.idtipopessoa in (5,12)) 
			where c.idcontato = ' . $_SESSION['SESSAO']['IDPESSOA'];

		$res = d::b()->query($sql);
		$qtd = mysqli_num_rows($res);

		$row = mysqli_fetch_assoc($res);

		if (!empty($row['idpessoa']) and $qtd > 0) {
			$menuSuperiorHTML .= "
			<li class='divider'></li>
			<li><a href='?_modulo=comprasrhrestrito$linkIdempresa'><i class='fa fa-user-plus vermelho'></i>&nbsp;RH Restrito</a></li>";
		}
	}

	if ($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1 or $_SESSION['SESSAO']['IDTIPOPESSOA'] == 15 or $_SESSION['SESSAO']['IDTIPOPESSOA'] == 16) {
		$menuSuperiorHTML .= "
			<li class='divider'></li>
			<li title='Webmail'>
				<a target='_blank' href='form/webmail.php'>
					<i class='fa fa-envelope vermelho'></i>&nbsp;Webmail</a>
				</a>	
			</li>				 
			<li class='divider'></li>";
	}

	if ($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1 and array_key_exists('organograma', $modsEmpresa)) {
		$menuSuperiorHTML .=
			"<li title='Organograma'>
			<a target='_blank' href='report/organograma.php?_idempresa=$idempresa'>
				<i class='fa fa-sitemap vermelho'></i>&nbsp;Organograma</a>
			</a>
		</li>
		<li class='divider'></li>";
	}

	if ($_SESSION['SESSAO']['SUPERUSUARIO'] != true) {
		$menuSuperiorHTML .= "<li><a href='?_modulo=alterasenha'><i class='fa fa-key vermelho'></i>&nbsp;Alterar Senha</a></li>";
	}

	$iusercred = verificaSuperUsuario();

	if ($iusercred > 0) {
		$menuSuperiorHTML .= "
		<li class='divider'></li>
		<li title='Super usuário: utilizar como outro usuário'>						
			<a href='javascript:alterarUsuario()'><i class='fa fa-support vermelho'></i>&nbsp;Alternar usuário</a>
		</li>";
	}

	$menuSuperiorHTML .= "
	<li class='divider'></li>
			<li>
				<a href=javascript:localStorage.removeItem('jwt');Cookies.remove('jwt');Cookies.remove('PHPSESSID');window.location.href='$urllogout';>
					<i class='fa fa-power-off vermelho'></i>&nbsp;Logout
				</a>
			</li>
			<li class='divider'></li>
			<li title='Sobre o Sistema'>
				<a href='javascript:sobreOSistema()'>
					<i class='fa fa-question-circle-o azulclaro'></i>&nbsp;Sobre o sistema
				</a>
			</li>
		</ul>
	</li>";

	$menuSuperiorHTML .= "
	<script>
		$('#ramais').click(function(){
			CB.modal({
				url:'?_modulo=ramalcolaboradores',
				header:'Ramais',
				menu: false
			});
		});
	</script>";

	if ($_SESSION["SESSAO"]["PERMISSAOCHAT"] == "Y" and $_SESSION["SESSAO"]["SUPERUSUARIO"] != true) {
		$menuSuperiorHTML .=	"<div id='cbNotificacoes' class='dropdown pull-right snippet' onclick='chat.abrirContainerChat();chat.montarContatos();chat.recuperarAvatar();chat.maximizar();'>
			<a id='cbBadge' href='#' class='fa fa-comment hide' data-toggle='dropdown' role='button' aria-expanded='false'>
				<span id='cbIBadge' class='badge fundovermelho' style='display: none;'></span>
			</a>
			<ul id='cbListaNotificacoes' class='Xdropdown-menu' role='Xmenu' style='padding: 0px;border: 0px;display: none;'>
				<li id='cbListaNotificacoesHeader' style='padding: 4px 10px;'>
					<table style='width: 100%;'>
						<tbody><tr><td style='color: silver;font-weight: bold;'>Notificações:</td>
							<td></td><td><span class='azul pointer' onclick='chat.marcarLida('*')'>Marcar todas como lidas</span></td>
							<td><a href='#'><i class='fa fa-cog azul pointer'></i></a></td>
						</tr>
					</tbody></table>
				</li>
				<li id='cbListaNotificacoesFooter' style='padding: 4px 10px;'>
					<table style='width: 100%;'>
					<tbody>
					<tr>
						<td></td>
						<td >
							<a href='javascript:chat.abrirContainerChat();chat.maximizar();chat.montarContatos();chat.recuperarAvatar();' class='pointer floatright'>Abrir janela de Chat</a>
						</td>
					</tr>
					</tbody>
					</table>
				</li>
			</ul>
		</div>";
	}

	foreach (getSnippets()['padrao'] as $moduloPai => $s) {
		$onclick = "";
		if ($s["tipo"] == "PHP") {
			if (strlen(trim($s["msgconfirm"])) > 0) {
				$onclick = "if(confirm('" . $s["msgconfirm"] . "'))CB.snippet('{$s['idsnippet']}');";
			} else {
				$onclick = "CB.snippet('{$s['idsnippet']}');";
			}
		} elseif ($s["tipo"] == "LINK") {
			$onclick = "$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: '" . $s["code"] . "'});";
		} elseif ($s["tipo"] == "JS" or $s["tipo"] == "MOD") {
			$fname = "_" . md5(uniqid());
			$onclick = $fname . "()";

			$menuSuperiorHTML .=
				"<script>
				function $fname() {
					{$s['code']}
				}
				//# sourceURL=snippet_{$s['idsnippet']}
			</script>";
		}

		if (strpos($s["cssicone"], "/upload/") === 0) {
			$iconeSnippet = "<a onclick='javascript:{$onclick}'>";
			$iconeSnippet .= file_get_contents("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$s["cssicone"]}");
			$iconeSnippet .= "</a>";
		} else {
			$iconeSnippet = "<a href='javascript:" . $onclick . "' class='" . $s["cssicone"] . " snippet'></a>";
		}

		$menuSuperiorHTML .= "
		<li class='dropdown pull-right snippet' id='cbSnippet{$s['idsnippet']}' title='{$s['snippet']}' cbmodulo='{$s['modulo']}'>
			$iconeSnippet
			<span onclick='javascript:{$onclick}' id='cbIBadgeSnippet{$s['idsnippet']}' modulo='{$s['modulo']}' class='badge fundovermelho' style='' ibadge=''></span>
		</li>";
	}

	return $menuSuperiorHTML;
}

function montarSnippets($debugger = false): String
{
	$snippetsHTML = "";
	$urllogout = "?_acao=logout";

	$idempresa = cb::idempresa();
	$linkIdempresa = "&_idempresa=" . $idempresa;
	$modsEmpresa = getModsUsr("MODULOS");
	$bg = str2Color($_SESSION["SESSAO"]["NOME"]);
	$fc = colorContrastYIQ($bg);

	$snippetsHTML .= "<div class='d-flex mx-auto'>";
	$snippets = getSnippets();

	$snippetPrincipal = $snippets['padrao'];

	if (($_SESSION['SESSAO']['IDTIPOPESSOA'] == 1) && count($snippets['snippetprincipal'])) {
		$snippetPrincipal = $snippets['snippetprincipal'];
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	foreach ($snippetPrincipal as $key => $s) {
		$onclick = "";
		if ($s["tipo"] == "PHP") {
			if (strlen(trim($s["msgconfirm"])) > 0) {
				$onclick = "if(confirm('" . $s["msgconfirm"] . "'))CB.snippet('{$s['idsnippet']}');";
			} else {
				$onclick = "CB.snippet('{$s['idsnippet']}');";
			}
		} elseif ($s["tipo"] == "LINK") {
			$onclick = "$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: '" . $s["code"] . "'});";
		} elseif ($s["tipo"] == "JS" or $s["tipo"] == "MOD") {
			$fname = "_" . md5(uniqid());
			$onclick = $fname . "()";

			$snippetsHTML .= "<script>
								function $fname() {
									{$s['code']}
								}
								//# sourceURL=snippet_{$s['idsnippet']}
							</script>";
		}

		if (strpos($s["cssicone"], "/upload/") === 0) {
			// $iconeSnippet = "<img style='width:16px;align-self: center;' class='snippet' src='.".$s["cssicone"]."'/>";
			// Forma antiga de buscar o arquivo, porem está com erro de SSL
			// $iconeSnippet = file_get_contents("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$s["cssicone"]}");
			$url = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}{$s["cssicone"]}";
			curl_setopt($ch, CURLOPT_URL, $url);
			$response = curl_exec($ch);

			$iconeSnippet = $response;

			if (curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) == 404) {
				if ($debugger) echo 'Erro: ' . curl_error($ch);
				$iconeSnippet = '';
			}
		} else {
			$iconeSnippet = "<i class='{$s["cssicone"]}'></i>";
		}

		$snippetsHTML .= "<a href='javascript:$onclick' class='snippet'>
							<li class='d-flex flex-col align-items-center justify-content-center h-100 nav-item px-3 relative' id='cbSnippet{$s['idsnippet']}' title='{$s["snippet"]}' cbmodulo='{$s["modulo"]}'>
								$iconeSnippet
								<span id='cbIBadgeSnippet{$s['idsnippet']}' modulo='{$s["modulo"]}' class='nav-link pt-2' style='' ibadge=''>
									{$s["snippet"]}
								</span>
							</li>
						</a>";
	}

	curl_close($ch);

	$snippetsHTML .= "</div>";

	$snippetsHTML .= '<div class="inline-flex hidden" id="box-bloqueio" >
    <p class="d-flex align-items-center justify-content-center h-100 relative text-dark pr-5 pl-5" style="color: #212121; background: #E6E6E6;">
        <span class="mr-2 h5 text-black">Modo edição expira em: </span>
        <span class="h5 bloqueio-timer font-weight-bold">00:00</span>
		<a class="d-flex align-items-center bloqueio-icone " style="color: #D9534F"><i class="fa fa-info-circle ml-3 mb-0 " style="-webkit-box-shadow: 9px 10px 29px -8px rgba(0,0,0,1);
-moz-box-shadow: 9px 10px 29px -8px rgba(0,0,0,1);
box-shadow: 9px 10px 29px -8px rgba(0,0,0,1);"></i></a> 
    </p>
</div>';

	$snippetsHTML .= "<div class='inline-flex'>";

	// Snippet Acao
	if (count($snippets['snippetacao'])) {
		$snippetsHTML .= "<a href='#' class='snippet'>
							<li class='d-flex flex-col align-items-center justify-content-center h-100 nav-item px-3 relative' title='Módulos de ação' cbmodulo='snippetacao'>
								<i class='fa fa-plus'></i>
							</li>
						</a>";
	}


	foreach ($snippets['snippetsecundario'] as $key => $snippet) {
		$onclick = "";
		if ($s["tipo"] == "PHP") {
			if (strlen(trim($snippet["msgconfirm"])) > 0) {
				$onclick = "if(confirm('" . $snippet["msgconfirm"] . "'))CB.snippet('{$snippet['idsnippet']}');";
			} else {
				$onclick = "CB.snippet('{$snippet['idsnippet']}');";
			}
		} elseif ($snippet["tipo"] == "LINK") {
			$onclick = "$(CB.oModuloHeader, CB.oModuloHeaderBg).addClass('hidden');CB.loadUrl({urldestino: '" . $snippet["code"] . "'});";
		} elseif ($snippet["tipo"] == "JS" or $snippet["tipo"] == "MOD") {
			$fname = "_" . md5(uniqid());
			$onclick = $fname . "()";

			$snippetsHTML .= "<script>
								function $fname() {
									{$snippet['code']}
								}
								//# sourceURL=snippet_{$snippet['idsnippet']}
							</script>";
		}

		$iconeSnippet = "<i class='{$snippet["cssicone"]}'></i>";

		if (strpos($snippet["cssicone"], "/upload/") === 0) {
			$iconeSnippet = "<img style='width:16px;align-self: center;' class='snippet' src='." . $snippet["cssicone"] . "'/>";
		}

		$snippetsHTML .= "<a href='javascript:$onclick' class='snippet'>
							<li class='d-flex flex-col align-items-center justify-content-center h-100 nav-item px-3 relative' id='cbSnippet{$snippet['idsnippet']}' title='{$snippet["snippet"]}' cbmodulo='{$snippet["modulo"]}'>
								$iconeSnippet
								<span id='cbIBadgeSnippet{$snippet['idsnippet']}' modulo='{$snippet["modulo"]}' class='nav-link pt-2' style='' ibadge=''>
									{$snippet["snippet"]}
								</span>
							</li>
						</a>";
	}

	$snippetsHTML .= 	"<li class='dropdown flex flex-col align-items-center justify-content-center  h-100 nav-item py-2 px-3 relative bold' style='order:999;background-color:#$bg;' cbidpessoa='{$_SESSION["SESSAO"]["IDPESSOA"]}' title='{$_SESSION["SESSAO"]["NOME"]} LP: {$_SESSION['SESSAO']['IDLP']}'>
							<a href='#' class='dropdown-toggle' id='dropdownUser1' data-toggle='dropdown' role='button' aria-expanded='false' style='color: $fc;'>
								" . mb_substr($_SESSION["SESSAO"]["NOME"], 0, 2) . "
								<span class='caret'></span>
							</a>
							<ul class='dropdown-menu left-auto right-0' role='menu'>";

	if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1) {
		$snippetsHTML .=
			"<li><a href='javascript:janelamodal(\"report/relfuncionario.php\");' class='dropdown-item'><i class='fa fa-info-circle vermelho'></i>&nbsp;Minhas Informações</a></li>
									<li><a href='?_modulo=eventoponto$linkIdempresa' class='dropdown-item'><i class='fa fa-clock-o vermelho'></i>&nbsp;Ponto</a></li>
									<li id='ramais'><a href='#' class='dropdown-item'><i class='fa fa-phone-square vermelho'></i>&nbsp;Ramais</a></li>
									<li><a href='?_modulo=linkaplicativo' class='svg-red'>
										<img src='inc/img/apple.png' alt='IOS' style='width: 20px; margin-left: -4px;'>
										<span style='margin-top: -19px; margin-left: 16px;'>APP IOS</span></a>
									</li>";
	}

	if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1) {
		$sql = "SELECT p.idpessoa 
											from pessoacontato c
												join pessoa p on(p.idpessoa=c.idpessoa and p.idtipopessoa in (5,12)) 
										where c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"];

		$res = d::b()->query($sql);
		$qtd = mysqli_num_rows($res);

		$row = mysqli_fetch_assoc($res);

		if (!empty($row['idpessoa']) and $qtd > 0) {
			$snippetsHTML .= "<li class='divider'></li>
										<li><a href='?_modulo=comprasrhrestrito$linkIdempresa' class='dropdown-item'><i class='fa fa-user-plus vermelho'></i>&nbsp;RH Restrito</a></li>";
		}
	}

	if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 16) {
		$snippetsHTML .= "<li title='Webmail'>
														<a target='_blank' href='form/webmail.php' class='dropdown-item'>
															<i class='fa fa-envelope vermelho'></i>&nbsp;Webmail</a>
														</a>	
													</li>";
	}

	if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 1 and array_key_exists("organograma", $modsEmpresa)) {
		$snippetsHTML .= "<li title='Organograma'>
										<a target='_blank' href='report/organograma.php?_idempresa=$idempresa' class='dropdown-item'>
											<i class='fa fa-sitemap vermelho'></i>&nbsp;Organograma</a>
										</a>
									</li>";
	}

	if ($_SESSION["SESSAO"]["SUPERUSUARIO"] != true) {
		$snippetsHTML .= "<li><a href='?_modulo=alterasenha' class='dropdown-item'><i class='fa fa-key vermelho'></i>&nbsp;Alterar Senha</a></li>";
	}

	$iusercred = verificaSuperUsuario();

	if ($iusercred > 0) {
		$snippetsHTML .= "<li title='Super usuário: utilizar como outro usuário'>						
															<a href='javascript:alterarUsuario()' class='dropdown-item'><i class='fa fa-support vermelho'></i>&nbsp;Alternar usuário</a>
														</li>";
	}

	$snippetsHTML .= "<li>
									<a class='dropdown-item' href=javascript:localStorage.removeItem('jwt');Cookies.remove('jwt');Cookies.remove('PHPSESSID');window.location.href='$urllogout';>
										<i class='fa fa-power-off vermelho'></i>&nbsp;Logout
									</a>
								</li>
								<li title='Sobre o Sistema'>
									<a href='javascript:sobreOSistema()' class='dropdown-item'>
										<i class='fa fa-question-circle-o azulclaro'></i>&nbsp;Sobre o sistema
									</a>
								</li>
							</ul>
						</li>
					</div>";

	return $snippetsHTML;
}

/*
 * Verifica se o usuário logado pode realizar alteração de credencial, passando a utilizar o sistema como outro usuário do carbon
 */
function verificaSuperUsuario($inusuario = null)
{
	if (logado()) {
		$sqlUsuario = "";
		if (!empty($inusuario)) {
			$sqlUsuario =	"and (
						exists(-- Conforme o tipo de pessoa
							select 1 from pessoa p where p.usuario = '" . $inusuario . "' and p.idtipopessoa = s.objeto and s.tipoobjeto = 'tipopessoa'
						) or
						exists(-- Conforme a pessoa
							select 1 from pessoa p where p.usuario = '" . $inusuario . "' and p.usuario = s.objeto and s.tipoobjeto = 'pessoa'
						)
					)";
		}

		$sqlsu = "select * from " . _DBCARBON . "._superusuario s
					where usuario = '" . $_SESSION["SESSAO"]["USUARIO"] . "'
					and validade > now()
					" . $sqlUsuario . "
					and exists(-- Que o usuário logado seja um funcionário
						select 1 from pessoa p where (p.usuario='hermesp' or p.usuario='marcelo' or idtipopessoa=1) and status in ('ATIVO','PENDENTE')
					)";

		//die($sqlsu);

		$ressu = mysql_query($sqlsu) or die("Erro ao verificar Su.");

		$irows = mysql_num_rows($ressu);
		if ($irows > 0) {
			return $irows;
		} else {
			return false;
		}
	}
}

function buscarMatrizPermissoesPorIdPessoa($idpessoa)
{
	if (!$idpessoa) return false;

	$qr = "SELECT 
			group_concat(mp.idempresa) as matrizpermissoes 
		FROM pessoa p 
			JOIN matrizpermissao mp 
				ON mp.idpessoa = p.idpessoa 
		WHERE 
			p.idpessoa = " . $idpessoa;

	$rs = d::b()->query($qr) or die("buscarMatrizPermissoesPorIdPessoa: " . mysqli_error(d::b()));
	$rc = mysqli_fetch_assoc($rs);

	return $rc['matrizpermissoes'];
}

/*
 * Mostrar erro de acesso
 */
function erroacesso($tipo = "../img/lock16,png", $texto, $die = true, $cabecalho = "")
{
	echo
	"
<table style='border:1px solid gray;margin:15px; padding:2px;font-family: Arial;'>
<tr style='background:rgb(242,121,0);'>
	<td colspan='4'><font color='white' style='font-weight:bold;font-size:14px;'>" . $cabecalho . "</td>
</tr>
<tr>
	<td><img src='" . $tipo . "' border='0' alt=''></td>
	<td style='font-size:13px;'>" . $texto . "</td>
</tr>
<tr>
	<td colspan='10' align='right' style='font-size:13px;'>Lp: <a href='_lp.php?idlp=" . $_SESSION["SESSAO"]["IDLP"] . "'>" . $_SESSION["SESSAO"]["IDLP"] . "</a></td>
</tr>
<tr>
<td colspan='10' align='right' style='font-size:13px;'>Usu&aacute;rio: " . $_SESSION["SESSAO"]["USUARIO"] . "&nbsp;&nbsp;<a href='form/_login.php?_action=logout' style='font-size:10px;'>(Logout)</a> </td>
</tr>

</table>";
	if ($die) die;
}

function modInicial()
{

	$sqlWhereModEmpresa = getModsUsr('SQLWHEREMOD');
	$lps = getModsUsr('LPS');

	if (!$sqlWhereModEmpresa) die("mod_inicial: Você não está logado");

	//Geralmente utilizado após login: Inicializa Modulo "LINKHOME" OU recupera o primeiro modulo (filho ou não)
	$sqlmod = "select * from  (		
				SELECT -- Recuperar o PRIMEIRO módulo filho quando for DROP ou o próprio Módulo
					CASE m1.tipo
						WHEN 'DROP' then (
								select m2.modulo
								from " . _DBCARBON . "._modulo m2 join " . _DBCARBON . "._lpmodulo l2 on (l2.modulo=m2.modulo and l2.idlp in(" . $lps . "))
								where m2.modulopar = m1.modulo and m2.status = 'ATIVO'
								order by m2.ord,m2.rotulomenu asc
								limit 1
							)
						ELSE m1.modulo
					END as moduloinicial,
					m1.tipo,m1.ord,m1.moduloinicial as modin

				FROM " . _DBCARBON . "._modulo m1	
					JOIN " . _DBCARBON . "._lpmodulo l on (l.modulo=m1.modulo and l.idlp in(" . $lps . "))
				WHERE m1.tipo in ('LINKHOME','LINK','DROP','MODVINC','LINKUSUARIO','BTINV','SNIPPET') 
					-- Recupera primeiro somente os Módulos PAI
					-- AND ifnull(m1.modulopar,'') = ''
					AND m1.modulo in (" . $sqlWhereModEmpresa . ") and m1.status = 'ATIVO'
			)as u where u.moduloinicial is not null
				ORDER BY 
					-- Ordena-se primeiro por LINKHOME
					(CASE 
						WHEN u.modin ='Y' THEN -2
						WHEN u.tipo ='LINKHOME' THEN -1
						WHEN ord is null THEN 99
						ELSE u.ord
						END
					)
				LIMIT 1";

	$rmod = d::b()->query($sqlmod);
	$rowm = mysqli_fetch_assoc($rmod);

	if (empty($rowm["moduloinicial"])) {
		die("_moduloconf: Módulo inicial vazio");
	} else {
		return $rowm["moduloinicial"];
	}
}

//Recupera o Módulo real, em caso de Módulos Vinculados. Utilizado principalmente em eventos (save,search)
function getModReal($inmod)
{

	$sql = "select modvinculado from " . _DBCARBON . "._modulo where modulo = '" . $inmod . "' and tipo = 'MODVINC';";

	$res = d::b()->query($sql);
	$row = mysqli_fetch_assoc($res);

	if (empty($row["modvinculado"])) {

		if (getModsUsr("MODULOS")[$inmod]["tipo"] == "MODVINC") {
			return getModsUsr("MODULOS")[$inmod]["modvinculado"];
			//return $inmod;
		} else {
			return $inmod;
		}
	} else {
		return $row["modvinculado"];
	}
}
/*
 * Centralizar a consulta de Módulo
 * Evitar falhas em relação à Módulos Vinculados
 * Complementar com as colunas necessárias diretamente na consulta
 */
function retArrModuloConf($inModulo, $inbypass = false)
{

	if (empty($inModulo)) die("retArrModuloConf(1): Parâmetro inModulo não informado");

	$wheremod = is_numeric($inModulo) ? "WHERE m.idmodulo = " . $inModulo : "WHERE m.modulo = '" . $inModulo . "'";

	//Permite reaproveitamento sem verificação de segurança. Ex: Tela de _modulo necessita recuperar informações do módulo mesmo que não estejam devidamente atribuídas em alguma LP
	if ($inbypass !== true) {
		$modulos = ($_GET["_modulo"] == "_login") ? getModsUsr("SQLWHEREMOD") : getPessoaModulosDisponiveis();
		$joinLp = ($_SESSION["SESSAO"]["LOGADO"]) ? "left join " . _DBCARBON . "._lpmodulo l on (l.modulo=m.modulo and l.idlp='" . $_SESSION["SESSAO"]["IDLP"] . "')" : "";
		$whereMod = ($_SESSION["SESSAO"]["LOGADO"]) ? "and m.modulo in (" . $modulos . ")" : "";
		$ifrestaurar = ($modulos) ? ",IF(1=(select ('restaurar' in  (" . $modulos . "))),'Y','N') as oprestaurar" : "";
	}

	$smod = "SELECT m.idmodulo
				,m.modulo
				,m.descricao
				,m.rotulomenu
				,m.cssicone
				,m.csscustom
				,m.tipo
				,m.divisor
				,m.moduloinicial
				,m.ord
				,m.status
				,m.modulotipo
				,m.modvinculado
				,m.modulopar
				,m.cbheader
				,m.titulofiltros
				,m.largurafixa
				,m.novajanela
				,m.statusrest
				,m.tabrest
				,m.checkbox
				,m.criadopor
				,m.criadoem
				,m.alteradopor
				,m.alteradoem
				,m.disponivelapp
				,m.btncolorapp
				,m.botaoassinar
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.ready ELSE m.ready END as ready
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.tab ELSE m.tab END as tab
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.chavefts ELSE m.chavefts END as chavefts
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.btsalvar ELSE m.btsalvar END as btsalvar
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.btnovo ELSE m.btnovo END as btnovo
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.btimprimir ELSE m.btimprimir END as btimprimir
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.btimprimirconf ELSE m.btimprimirconf END as btimprimirconf
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.psqfull ELSE m.psqfull END as psqfull
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.urldestino ELSE m.urldestino END as urldestino
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.urlprint ELSE m.urlprint END as urlprint
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.postonenter ELSE m.postonenter END as postonenter
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.ajaxparalelo ELSE m.ajaxparalelo END as ajaxparalelo
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.ordenavel ELSE m.ordenavel END as ordenavel
				,CASE WHEN m.tipo='MODVINC' THEN mv.evento_saveprechange ELSE m.evento_saveprechange END as evento_saveprechange
				,CASE WHEN m.tipo='MODVINC' THEN mv.evento_saveposchange ELSE m.evento_saveposchange END as evento_saveposchange
				,CASE WHEN m.tipo='MODVINC' THEN mv.evento_presearch ELSE m.evento_presearch END as evento_presearch
				,CASE WHEN m.tipo='MODVINC' THEN mv.evento_possearch ELSE m.evento_possearch END as evento_possearch
				,CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.orderby ELSE m.orderby END as orderby
				,mpar.cssicone as cssiconepar
				,mpar.rotulomenu as rotulomenupar
				,cmr.modulo as configmoduloReal, m.timeout
                                " . $ifrestaurar . "
			FROM 
				" . _DBCARBON . "._modulo m 
				left join " . _DBCARBON . "._modulo mv on (mv.modulo=m.modvinculado)
				left join " . _DBCARBON . "._modulo mpar on (mpar.modulo=m.modulopar)
				left join (select distinct cmr.modulo From " . _DBCARBON . "._modulofiltros cmr) cmr on cmr.modulo =  m.modulo 
				" . $joinLp . "
				" . $wheremod . "
				" . $whereMod;

	//die($smod);

	$rmod = d::b()->query($smod);

	if (!$rmod) die("retArrModuloConf: Erro ao recuperar Módulo " .  mysqli_error(d::b()));

	$arrColunas = mysqli_fetch_fields($rmod);
	$r = mysqli_fetch_assoc($rmod);

	if ($r) {
		$arr = array();
		//para cada coluna resultante do select cria-se um item no array
		foreach ($arrColunas as $col) {
			$arr[$col->name] = $r[$col->name];
			//Armazena os parametros de modulo vinculado. @todo: concatenar nesta função os filtros, para evitar consulta posterior desnecessária
			if ($col->name == "tipo") $ctipo = $r[$col->name];
			if ($col->name == "modvinculado" and empty($col->configmoduloReal)) $cmodvinculado = $r[$col->name];
		}
		return $arr;
	} else {
		die("retArrModuloConf: Módulo [" . $inModulo . "] não inicializado corretamente");
	}
}

function retArrModuloConfFiltros($inModulo)
{

	if (empty($inModulo)) die("retArrModuloConfFiltros: Parâmetro inModulo não informado");

	$sfiltros = "SELECT
					CASE WHEN m.tipo='MODVINC' THEN mv.tab ELSE m.tab END as tab
					,mtc.col
					,mf.psqkey
					,mf.parget
					,mf.psqreq
					,mf.psqreqdefault
					,mf.visres
					,mf.visresapp
					,mf.oculto
					,mf.filtrodata
					,mf.entre
					,mf.ord
					,mf.align
					,mtc.ordpos
					,mtc.datatype
					,mtc.primkey
					,mtc.autoinc
					,mtc.nullable
					,mtc.rotcurto
					,mtc.rotlongo
					,mtc.rotpsq
					,mtc.prompt
					,mf.promptativo
					,mtc.`default`
					,mtc.code
					,mtc.codeeval
					,mtc.acsum
				FROM
					" . _DBCARBON . "._modulo m
					left join (select distinct cmr.modulo From " . _DBCARBON . "._modulofiltros cmr) cmr on cmr.modulo =  m.modulo 
					LEFT JOIN " . _DBCARBON . "._modulo mv on (mv.modulo=m.modvinculado)
					JOIN " . _DBCARBON . "._mtotabcol mtc on (mtc.tab = (CASE WHEN m.tipo='MODVINC' THEN mv.tab ELSE m.tab END) )
					LEFT JOIN " . _DBCARBON . "._modulofiltros mf on (mf.modulo = (CASE WHEN m.tipo='MODVINC' and cmr.modulo is null THEN mv.modulo ELSE m.modulo END) and mf.col = mtc.col)
					
				WHERE m.modulo = '" . $inModulo . "'
				ORDER BY mf.visres, mf.ord, mtc.ordpos";
	//die($sfiltros);

	$rfilt = d::b()->query($sfiltros);

	if (!$rfilt) die("retArrModuloConfFiltros: Erro ao recuperar Filtros " .  mysqli_error(d::b()));

	$arrColunas = mysqli_fetch_fields($rfilt);

	if (mysqli_num_rows($rfilt) > 0) {
		$arr = array();
		$i = 0;
		while ($r = mysqli_fetch_assoc($rfilt)) {
			$arr["tabela"][$r["tab"]][$r["col"]]["primkey"] = $r["primkey"];
			$arr["tabela"][$r["tab"]][$r["col"]]["datatype"] = $r["datatype"];
			$arr["tabela"][$r["tab"]][$r["col"]]["visres"] = $r["visres"];
			$arr["tabela"][$r["tab"]][$r["col"]]["visresapp"] = $r["visresapp"];
			$arr["tabela"][$r["tab"]][$r["col"]]["rotcurto"] = $r["rotcurto"];
			$arr["tabela"][$r["tab"]][$r["col"]]["rotpsq"] = $r["rotpsq"];
			$arr["tabela"][$r["tab"]][$r["col"]]["where"] = $r["where"];
			$arr["tabela"][$r["tab"]][$r["col"]]["prompt"] = $r["prompt"];
			$arr["tabela"][$r["tab"]][$r["col"]]["code"] = $r["code"];
			$arr["tabela"][$r["tab"]][$r["col"]]["entre"] = $r["entre"];
			$arr["tabela"][$r["tab"]][$r["col"]]["ord"] = $r["ord"];
			$arr["tabela"][$r["tab"]][$r["col"]]["align"] = $r["align"];
			$arr["tabela"][$r["tab"]][$r["col"]]["acsum"] = $r["acsum"];

			//Monta array com os campos que servem para montagem dos parametros GET a serem passados para a url de destino
			if ($r["parget"] == 'Y') {
				$arr["parget"][] = $r["col"];
			}
			//Array com as colunas de date/datetime/timestamp para _fds
			if (($r["datatype"] == "date" or $r["datatype"] == "datetime" or $r["datatype"] == "timestamp") and $r["filtrodata"] == "Y") {
				$arr["coldata"][] = $r["col"];
			}
			if ($r["primkey"] == 'Y') {
				$arr["primkey"] = $r["col"];
			}
		}
		return $arr;
	} else {
		die("retArrModuloConfFiltros: Filtros do Módulo não configurados corretamente");
	}
}


/*
 * As tabelas do DB citadas aqui não passarão pela exigência da coluna IDEMPRESA durante o post
 */
function retBypassIdempresa()
{

	/*
    return array(
		"_droplet"=>"_droplet"
		,"_formobjetos"=>"_formobjetos"
		,"_lp"=>"_lp"
		,"_lpmodulo"=>"_lpmodulo"
		,"_lppagina"=>"_lppagina"
		,"_modulo"=>"_modulo"
		,"_modulorep"=>"_modulorep"
		,"_modulofiltros"=>"_modulofiltros"
		,"_modulofiltroshl"=>"_modulofiltroshl"
		,"_mtotabcol"=>"_mtotabcol"
		,"_paraplweb"=>"_paraplweb"
		,"_rep"=>"_rep"
		,"_repcol"=>"_repcol"
		,"_empresa"=>"_empresa"
		,"_vwpsqdicionariodados"=>"_vwpsqdicionariodados"
	);
 */
	$sq = "select tabela from " . _DBAPP . "._passidempresa";
	$res = d::b()->query($sq) or die("Erro ao consultar tabelas ByPassIdempresa:" . mysqli_error(d::b()));
	$arrret = array();
	while ($row = mysqli_fetch_assoc($res)) {
		$arrret[$row["tabela"]] = $row["tabela"];
	}
	return $arrret;
}

/*
 * Full Text search
 * Realizar pesquisa em bancos de dados internos ou externos de FULL TEXT SEARCH, e permitir pesquisas booleanas
 * Ela retorna array de IDs encontrados na tabela (informada no módulo) para serem utilizados em cláusulas 'in'
 * http://dev.mysql.com/doc/refman/5.7/en/fulltext-boolean.html
 */
function retPkFullTextSearch($inTabela, $inStrSearch, $inPage = null/*, $inPageLimit*/)
{

	global $_inspecionar_sql;

	//Exclui espaços desnecessários
	$inStrSearch = str_replace("  ", " ", $inStrSearch);
	//Troca aspas simples por duplas
	$inStrSearch = str_replace("'", "\"", $inStrSearch);

	//Monta array com cada termo enviado
	$arrStrSearch = explode(" ", $inStrSearch);

	//Prepara um array com strings formatadas
	$arrPrepSearch = array();
	$eliminaFts = true; //CAso sejam enviada uma pesquisa específica que não seja FTS, descartar mecanismo FTS
	while (list($k, $v) = each($arrStrSearch)) {
		//Se o usuário informou uma frase literal(verificando aspas), não colocar o wildcard
		if (!empty($v)) {
			if (substr($v, 0, 1) == "\"" and substr($v, -1) == "\"") {
				$arrPrepSearch[] = "+" . $v;
				$eliminaFts = false;
			} else {
				//Valores numéricos realizam busca exata da palavra inteira
				if (is_numeric($v)) {
					$arrPrepSearch[] = "+" . $v;
				} else {
					$arrPrepSearch[] = "+*" . $v . "*";
				}
				$eliminaFts = false;
			}
		}
	}

	if ($eliminaFts) {
		unset($_GET["_fts"]);
	}

	$inStrSearch = implode(" ", $arrPrepSearch);

	//Utilizar SQL_CALC_FOUND_ROWS para conhecer o total de registros que foram encontrados
	$sqlFts = "SELECT f._fk
				FROM " . _DBCARBON . ".$" . $inTabela . " f
				WHERE MATCH(_conteudo) against ('" . $inStrSearch . "' IN BOOLEAN MODE)
				-- ORDER BY fk desc";

	//if($_inspecionar_sql||$_SESSION["SESSAO"]["USUARIO"]=="marcelo"){
	if ($_inspecionar_sql) {
		echo "\n", $sqlFts, "\n\n";
	}
	//die($sqlFts);

	/*
	//Paginacao
	$_pageOffset=intval($inPage)-1;
	$_pageOffset=$_pageOffset."00";
	$_pageOffset=intval($_pageOffset);
	$sqlFts = $sqlFts . " limit ".$_pageOffset.",".$inPageLimit;
	*/

	//MAF: Isolar a transacao para 'dirty mode'
	d::b()->query("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;");

	$resFts = mysql_query($sqlFts) or die("Erro ao efetuar Full Text Search: " . mysql_error());
	//die("\n<!-- ".$sqlFts." -->");

	$arrFk["foundRows"] = myFoundRows();

	$arrFk;
	while ($rfts = mysql_fetch_assoc($resFts)) {
		$arrFk["arrPk"][] = $rfts["_fk"];
	}

	if (mysql_num_rows($resFts) > 0) {
		return $arrFk;
	} else {
		return false;
	}
}

function retColPrimKeyTabByMod($inModulo)
{
	if (empty($inModulo)) die("retPrimKeyTabByMod: Parâmetro inModulo não informado");

	$qr = "SELECT 
			mtc.col
		FROM
			" . _DBCARBON . "._modulo m
				LEFT JOIN
			" . _DBCARBON . "._modulo mv ON (mv.modulo = m.modvinculado)
				JOIN
			" . _DBCARBON . "._mtotabcol mtc ON (mtc.tab = (CASE
				WHEN m.tipo = 'MODVINC' THEN mv.tab
				ELSE m.tab
			END))
		WHERE
			m.modulo = '" . $inModulo . "'
				AND mtc.primkey = 'Y'";

	$rs = d::b()->query($qr) or die("retColPrimKeyTabByMod: Erro ao recuperar Chave Primária da tabela do módulo " .  mysqli_error(d::b()));

	$rw = mysqli_fetch_assoc($rs);

	return $rw["col"];
}

/*
 * Função para montar comando SQL do modulofiltrospesquisa
 * As colunas do select e as clausulas (e opcoes) estarao todas dentro de arrays, que devem ser concatenados para execução
 */
function montaSearchFiltrosPesquisa($inArrSearch, $inFoundrows = "")
{

	$_tmpsql = "";
	$_tmpsql .= (count($inArrSearch["SELECT"]) > 0)	? "\nSELECT " . $inFoundrows . " " . implode(", ", $inArrSearch["SELECT"])	: "";
	$_tmpsql .= (count($inArrSearch["FROM"]) > 0)		? "\nFROM " . implode(" ", $inArrSearch["FROM"])			: "";
	$_tmpsql .= (count($inArrSearch["WHERE"]) > 0)		? "\nWHERE " . implode(" and ", $inArrSearch["WHERE"])	: "";
	$_tmpsql .= (count($inArrSearch["GROUPBY"]) > 0)	? "\nGROUP BY " . implode(" ", $inArrSearch["GROUPBY"])	: "";
	$_tmpsql .= (count($inArrSearch["ORDERBY"]) > 0)	? "\nORDER BY " . implode(" ", $inArrSearch["ORDERBY"])	: "";
	$_tmpsql .= (count($inArrSearch["LIMIT"]) > 0)		? "\nLIMIT " . implode(" ", $inArrSearch["LIMIT"])		: "";

	return $_tmpsql;
}

function retarraytabdef($tabnome)
{


	if (!is_array(cb::$session["arrtabledef"])) {
		cb::$session["arrtabledef"] = array();
	}

	if (!empty(cb::$session["arrtabledef"][$tabnome]) and _TABDEFSESSION == true) {
		return cb::$session["arrtabledef"][$tabnome];
	} else {

		////conectabanco();

		//$arrdb = $_SESSION["arrdb"]; #String array

		$sql1 = "select
				col,
				autoinc,
				nullable,
				dbdefault,
				acsum,
				primkey,
				datatype,
				rotcurto,
				rotlongo,
				rotpsq,
				prompt,
				code,
				checkbox,
				auditar,
				ramcache,
				ramcachetmr
				from " . _DBCARBON . "._mtotabcol where tab = '" . $tabnome . "'
				order by ordpos";

		//echo "<Br>". $sql1 . "<br>";

		$res = mysql_query($sql1) or die("A Consulta [1] ao dicion&aacute;rio de dados falhou: " . mysql_error() . "<p>SQL: $sql");


		$ipk = 0;
		$ires = 0;
		$iramcache = 0;
		$iramcachetmr = 0;
		$tabarr = array();
		############################## Retorna Array com a defini??o da Table
		while ($row = mysql_fetch_array($res)) {

			$parcampo = $row["col"];

			if (($row["autoinc"] == "")       or is_null($row["autoinc"]) or
				($row["nullable"] == "")  or is_null($row["nullable"]) or
				($row["datatype"] == "")  or is_null($row["datatype"]) or
				($row["primkey"] == "") or is_null($row["primkey"])
			) {

				die("O select com a Definição da Tabela [" . _DBAPP . "].[" . $tabnome . "] retornou alguma coluna vazia, ou os campos autoinc/nullable/datatype/primkey nao foram informados corretamente. Select:\n" . $sql1);
			} else {
				/*
				 * maf30112010: armazenar qual eh o campo pk diretamente, para nao ser necessario efetuar loop sempre que seja necessario descobrir qual eh o campo PK de cada tablela
				 * Sendo utilizado inicialmente na auditoria automatica
				 */
				if ($row["primkey"] == "Y") {
					$tabarr["#pkfld"] = $parcampo;
					$ipk++;
				}

				//maf310720: indicar se a tabela vai passar por auditoria
				if ($row["auditar"] == "Y") {
					$tabarr["#auditar"] = "Y";
				}

				//maf310720: configuracoes de cache em memoria ram
				if ($row["ramcache"] == "Y") {
					$iramcache++;
					$tabarr["#ramcache"] = $iramcache;
				}

				//maf110421: configuracoes de cache temporizado em memoria ram
				if ($row["ramcachetmr"] == "Y") {
					$iramcachetmr++;
					$tabarr["#ramcachetmr"] = $iramcache;
				}

				/*
				 * Criar um array com os campos NOTNULL para verificar se todos foram enviados por POST.
				 * Isto evita campos '' ou '0' na tabela que na maioria das vezes é um campo FK que o programador esqueceu de enviar
				 * maf241113: nao exigir campos PK AUTONUMBER para insert. caso o campo PK AUTONUMBER nao seja enviado para update ele continuara sendo exigido
				 */
				if (($row["nullable"] == "N" and $row["dbdefault"] != "Y") and $row["primkey"] != "Y" and $row["autoinc"]) {
					$tabarr["#arrnullable"][$row["col"]] = null;
				}

				$tabarr[$parcampo]["autoinc"]  		= $row["autoinc"];
				$tabarr[$parcampo]["null"] 			= $row["nullable"];
				$tabarr[$parcampo]["dbdefault"] 	= $row["dbdefault"];
				$tabarr[$parcampo]["acsum"]   		= $row["acsum"];
				$tabarr[$parcampo]["type"] 			= $row["datatype"];
				$tabarr[$parcampo]["primkey"]   	= $row["primkey"];
				$tabarr[$parcampo]["rotcurto"]  	= $row["rotcurto"];
				$tabarr[$parcampo]["rotlongo"]  	= $row["rotlongo"];
				$tabarr[$parcampo]["rotpsq"]   		= $row["rotpsq"];
				$tabarr[$parcampo]["prompt"]   		= $row["prompt"];
				$tabarr[$parcampo]["code"]   		= $row["code"];
				$tabarr[$parcampo]["checkbox"]  	= $row["checkbox"];
				$tabarr[$parcampo]["auditar"]   	= $row["auditar"];
				$tabarr[$parcampo]["ramcache"]  	= $row["ramcache"];
				$tabarr[$parcampo]["ramcachetmr"]   = $row["ramcachetmr"];
			}

			$ires++;
		}

		if ($ipk > 1) {
			die("Erro: A definicao da tabela [" . $tabnome . "] deve conter no minimo 1 e somente 1 campo Primary Key (ipk=" . $ipk . ")");
		} else {

			if (is_null($tabarr)) {
				die("retarraytabdef: A Tabela [" . $tabnome . "] não existe no Database [" . _DBAPP . "]");
			} else {
				//print_r($tabarr);
				cb::$session["arrtabledef"][$tabnome] = $tabarr;
				return $tabarr;
			}
		}
	}
}

/*
 * Efetua loop na pasta eventcode, verificando os prefixos dos arquivos, para recuperar somente arquivos que se referem a campos
 * de codigo automatico
 */
function retarrcolauto()
{

	if (!empty($_SESSION["arrcolauto"]) and _TABDEFSESSION == true) {

		return $_SESSION["arrcolauto"];
	} else {

		$arrcolauto = array();

		$dir = "../eventcode/colauto";
		$dh  = opendir($dir);

		//Verifica se o diretorio de colunas automaticas existe
		if (!$dh) {
			die("functions.php: retarrcolauto: Diretorio de execucao de codigos nao encontrado: [" . $dir . "] ");
		}

		$inicodefile = "";
		$arrfname = array();
		$filecode = "";
		while (false !== ($filename = readdir($dh))) {

			//Verifica se o arquivo inicia com o padrao para execução de codigo automatico para colunas
			$inicodefile = substr($filename, 0, 8);
			if ($inicodefile == "col__i__" or $inicodefile == "col__u__" or $inicodefile == "col__d__") {

				$arrfname = explode("__", $filename);
				if (sizeof($arrfname) == 3) {
					//print_r($arrfname);
					//$filecode = file_get_contents($dir."/".$filename);

					//formato: col__i__nomecoluna
					$arrcolauto[$arrfname[2]][$arrfname[1]] = true;
				} else {
					echo "functions.php: retarrcolauto: O nome do arquivo para execucao automatica de colunas esta com a nomeclatura errada: [" . $filename . "]";
				}
			}

			$inicodefile = "";
			$arrfname = array();
			$filecode = "";
		}

		$_SESSION["arrcolauto"] = $arrcolauto;

		return $_SESSION["arrcolauto"];
	}
}

/*
 * Recupera quais os eventcodes que estao armazenados no DB, para que possam ser referencia para executar (incluir) os arquivos do filesystem
 */
function sessionArrayEventCode($intipo, $inmodulo)
{


	if (!is_array($_SESSION["arreventcode"])) {
		$_SESSION["arreventcode"] = array();
	}

	if (!empty($_SESSION["arreventcode"][$intipo][$inmodulo]) and _TABDEFSESSION == true) {
		return $_SESSION["arreventcode"][$intipo][$inmodulo];
	} else {

		if (!empty($intipo) and !empty($inmodulo)) {

			if ($intipo == "modulo") {

				/*$sql1 ="select 
							  if(trim(ifnull(evento_saveprechange,'')) != '','Y','N') as evento_saveprechange
							, if(trim(ifnull(evento_saveposchange,'')) != '','Y','N') as evento_saveposchange
							, if(trim(ifnull(evento_presearch,'')) != '','Y','N') as evento_presearch
							, if(trim(ifnull(evento_possearch,'')) != '','Y','N') as evento_possearch
						from "._DBCARBON."._modulo
						where modulo = '".$inmodulo."'";

				$res = mysql_query($sql1) or die("A Consulta 1 a eventcodes falhou: " . mysql_error() . "<p>SQL: $sql");

				//array com os eventcodes
				$row = mysql_fetch_array($res);*/
				$aMod = retArrModuloConf($inmodulo);

				$evento_saveprechange = (trim($aMod["evento_saveprechange"]) != '') ? 'Y' : 'N';
				$evento_saveposchange = (trim($aMod["evento_saveposchange"]) != '') ? 'Y' : 'N';
				$evento_presearch = (trim($aMod["evento_presearch"]) != '') ? 'Y' : 'N';
				$evento_possearch = (trim($aMod["evento_possearch"]) != '') ? 'Y' : 'N';

				/*
				$_SESSION["arreventcode"]["modulo"][$inmodulo]["evento_saveprechange"]=$evento_saveprechange;
				$_SESSION["arreventcode"]["modulo"][$inmodulo]["evento_saveposchange"]=$evento_saveposchange;
				$_SESSION["arreventcode"]["modulo"][$inmodulo]["evento_presearch"]=$evento_presearch;
				$_SESSION["arreventcode"]["modulo"][$inmodulo]["evento_possearch"]=$evento_possearch;
                                 * 
                                 */
				$_SESSION["arreventcode"]["modulo"][getModReal($_GET["_modulo"])]["evento_saveprechange"] = $evento_saveprechange;
				$_SESSION["arreventcode"]["modulo"][getModReal($_GET["_modulo"])]["evento_saveposchange"] = $evento_saveposchange;
				$_SESSION["arreventcode"]["modulo"][getModReal($_GET["_modulo"])]["evento_presearch"] = $evento_presearch;
				$_SESSION["arreventcode"]["modulo"][getModReal($_GET["_modulo"])]["evento_possearch"] = $evento_possearch;
				$_SESSION["arreventcode"]["modulo"][getModReal($_GET["_modulo"])]["idmodulo"] = $aMod["idmodulo"];
			}
		}
	}

	return $_SESSION["arreventcode"];
}


/*
 * Highligth condicional em linhas de resultados de pesquisa
 */
function retsearchrescond($inmodulo, $inretsession = true)
{

	$arrhlcond = array();
	$arrhlcond = $_SESSION["arrhlcond"][$inmodulo][$intab];

	if ($inretsession and !empty($arrhlcond)) {
		return $arrhlcond;
	} else {
		//conectabanco();

		//O loop irá pegar a primeira condicao
		//Logo, valores menores sempre terão prioridade
		$sql = "select *
			from " . _DBCARBON . "._modulofiltroshl h
			where h.modulo = '" . getModReal($inmodulo) . "'
				and h.status = 'A'
			order by ord asc";
		//echo"<br>$sql<br>";
		$result = mysql_query($sql);
		if (!$result) {
			die("<font size='1'><strong>Erro pesquisando Condicao para a pagina - </strong></font><p>" . mysql_error());
		}

		$arrcond = array();
		$i = 0;
		while ($row = mysql_fetch_array($result)) {
			$arrhlcond[$inmodulo][$i]["col"] = $row["col"];
			$arrhlcond[$inmodulo][$i]["cond"] = $row["cond"];
			$arrhlcond[$inmodulo][$i]["tipo"] = $row["tipo"];
			$arrhlcond[$inmodulo][$i]["valor1"] = $row["valor1"];
			$arrhlcond[$inmodulo][$i]["valor2"] = $row["valor2"];
			$arrhlcond[$inmodulo][$i]["cor"] = $row["cor"];
			$arrhlcond[$inmodulo][$i]["legenda"] = $row["legenda"];
			$arrhlcond[$inmodulo][$i]["ord"] = $row["ord"];
			$i++;
		}
		//	print_r($arrhlcond);die;
		return $arrhlcond;
	}
}

/**
 * Função padrão para tratamento de inputs enviados via POST para serem tratados pela CBPOST.php
 */
function explodeInputNameCarbon($inNomeCampo)
{
	$ckey = "_"; //Caractere Chave para montagem do buffer

	/*
	 * Encontra o padrão do Carbon no input enviado via post
	 * Neste regex será divido em 4 grupos, para que o nome da tabela pudesse conter o caractere '_' no DB
	 * Padrão esperado: _[1|núm linha]_[u|i]_[nome tabela]_[nome do campo]"
	 * Exs: "_1_u_pessoa_nome" ou "_1_u__lp_idlp"
	 */
	//$pat="(_[\w]*?[^_]*)(_[\w]*?[^_]*)(_[\w]*)((?=_)\w*)";
	$pat = "_(\w+?|<\?=\$i\?>)_(\w+?|<\?=\$_acao\?>)_(\w+?)_(\w+)";
	$patcbpost = "_(\w+?)_(\w+?|<\?=\$_acao\?>)_(\w+?)_(\w+)(=)"; //Verifica se é uma chamada manual de cbpost pela presença do caractere '='
	$matches = array();
	$matchescbpost = array();
	$arrRet = array();

	preg_match("/$pat/", $inNomeCampo, $matches);
	preg_match("/$patcbpost/", $inNomeCampo, $matchescbpost);

	/*$s1=substr($matches[1],1);
	$s2=substr($matches[2],1);
	$s3=substr($matches[3],1);
	$s4=substr($matches[4],1);*/

	$s1 = $matches[1];
	$s2 = $matches[2];
	$s3 = $matches[3];
	$s4 = $matches[4];

	if ($s1 and $s2 and $s3 and $s4) {
		$arrRet[] = $s1;
		$arrRet[] = $s2;
		$arrRet[] = $s3;
		$arrRet[] = $s4;

		if ($matchescbpost[5] === "=") $arrRet["cbpost"] = true; //Verifica se é uma chamada manual de cbpost
		return $arrRet;
	} else {
		return false;
	}
}

/*
 * Preparar Strings para visualização no html
 */
function formatastringvisualizacao($invalor, $informato, $entquote = true)
{

	/*
	 * executa pre tratamento para formatacao de informacoes para visualização
	 * A pedido do Marcelo Amorim foi acrescentado $entquote pois ao retornar as informaçoes da pesquisa não usar o str_replace('"','&quot;', $invalor), pois estava dando conflito com "
	 */
	switch ($informato) {
		case "double":
			$invalor = tratanumerovisualizacao($invalor);
			break;
		case "decimal":
			$invalor = tratanumerovisualizacao($invalor);
			break;
		case "datetime":
			$invalor = formatadatadbweb($invalor);
			break;
		case "date":
			$invalor = dma($invalor);
			break;
		case "varchar";
			if ($entquote == true) {
				$invalor = str_replace('"', '&quot;', $invalor);
			}
			break;
		case "enum";
			if ($entquote == true) {
				$invalor = str_replace('"', '&quot;', $invalor);
			}
			break;
		case "json";
			if ($entquote == true) {
				$invalor = str_replace('"', '&quot;', $invalor);
			}
			break;
		default:
			$invalor = $invalor;
			break;
	}
	return $invalor;
}

/*
 * Retorna datas vindas do database para formatacao padronizada conforme o parametro para datas web
 */
function formatadatadbweb($indata)
{

	//Verificar se já está em formato PT-BR. Caso positivo não realizar nenhum tratamento
	$rDPt = '/(0[1-9]|[12][0-9]|3[01])(\/|\-)(0[1-9]|1[012])(\/|\-)(18|19|20|21)\d\d$/';
	$rDHPt = '/(0[1-9]|[12][0-9]|3[01])(\/|\-)(0[1-9]|1[012])(\/|\-)(18|19|20|21)\d\d \d\d:\d\d:\d\d$/';
	if (preg_match($rDPt, $indata) or preg_match($rDHPt, $indata)) return $indata;

	//if(!empty($indata)) die($indata);
	//Substitui o caractere padrao de banco para /
	$indata = str_replace("-", "/", $indata);
	$indata = str_replace("//", "/", $indata);

	//Tenta separar a data da hora
	$arrdatetime = explode(" ", $indata);

	//print_r($arrdatetime); die;

	$data = $arrdatetime[0];
	$hora = $arrdatetime[1]; //a hora sera testada. se nao estiver vazia, ira concatenar hora tambem

	if (!isset($_SESSION["parformatodataweb"])) {
		$_SESSION["parformatodataweb"] = strtolower(retpar("formatodataweb"));
	}

	if (!isset($_SESSION["parformatodatadb"])) {
		$_SESSION["parformatodatadb"] = strtolower(retpar("formatodatadb"));
	}

	$parformatodataweb = $_SESSION["parformatodataweb"];
	$parformatodatadb  = $_SESSION["parformatodatadb"];

	$idata = count($datapartes = explode('/', $data));

	//Separa em variaveis conforme o que retornar da configuracao de [formatodatadb]
	$dia = $datapartes[strpos($parformatodatadb, 'd')];
	$mes = $datapartes[strpos($parformatodatadb, 'm')];
	$ano = $datapartes[strpos($parformatodatadb, 'a')];

	if ((!is_numeric($dia))
		or (!is_numeric($mes))
		or (!is_numeric($ano))
	) {
		return false;
	}

	if (($idata != 3) or
		(strlen($ano) <> 4)
	) {
		return false;
	};

	if (!checkdate($mes, $dia, $ano)) {
		return false;
	}

	//Re-concatena os valores de DATA encontrados acima conforme a posicao dos caracteres [dma] ou [amd] retornados pela configuracao [formatodataweb]
	$dataformatada = "";
	$chrsep = "";
	for ($i = 0; $i <= 2; $i++) {

		$substritem = substr($parformatodataweb, $i, 1);
		switch ($substritem) {
			case "d":
				$dataformatada .= $chrsep . $dia;
				break;
			case "m":
				$dataformatada .= $chrsep . $mes;
				break;
			case "a":
				$dataformatada .= $chrsep . $ano;
				break;
			default:
				null;
				break;
		}

		$chrsep = "/";
	}

	//Valida e concatena a HORA apos a DATA
	if (!empty($hora)) {


		$parformatohoraweb = strtolower("hns");

		$ihora = count($horapartes = explode(':', $hora));

		$hor = $horapartes[0];
		$min = $horapartes[1];
		$seg = $horapartes[2];

		if ((!is_numeric($hor))
			or (!is_numeric($min))
			or (!is_numeric($seg))
		) {
			return false;
		}

		if (($ihora != 3) and //Aceita HH:MM:SS e HH:MM e HH
			($ihora != 2) and
			($ihora != 1)
		) {
			return false;
		};

		if (($hor < 0) or ($hor > 23) or
			($min < 0) or ($min > 59) or
			($seg < 0) or ($seg > 59)
		) {
			return false;
		}
	}

	$dataformatada .= " " . substr("00" . $hor, -2) . ":" .
		substr("00" . $min, -2) . ":" .
		substr("00" . $seg, -2);

	return $dataformatada;
}

/*
 * Formata números inteiros padrão DB para serem mostrados nos inputs html
 */
function tratanumerovisualizacao($innum)
{

	if (empty($innum)) {
		$innum = 0; //força a colocação de um zero
	} else {
		$innum = str_replace(".", ",", $innum); //troca virgulas por pontos para permitir entrada livre de dados
	}
	return $innum;
}

/*
 * Trata números antes de serem utilizados, validando se isnumeric, alterando vírgulas e separadores decimais, etc.
 */
function tratanumero($innum)
{

	if (is_nan($innum)) {
		$innum = "NULL";
	} elseif (empty($innum) and !is_numeric($innum)) { //!isnumeric para considerar zeros
		$innum = "NULL"; //maf100811: possibilitar o envio de valores nulos para a tabela
	} else {
		$innum = str_replace(",", ".", $innum); //troca virgulas por pontos para permitir entrada livre de dados
		$arrsep = explode(".", $innum);
		if (count($arrsep) > 2) {
			//die("Número inválido para o campo: ".$innum."\n<br>O valor informado possui mais de um separador decimal");
			//maf211116: tenta recuperar a primeira e a última parte do valor decimal separados por '.' caso sejam enviados incorretamente
			$innum = preg_replace('/\.(?=.*\.)/', '', $innum);
			if (!is_numeric($innum)) {
				die("Número inválido para o campo: " . $innum . "\n<br>Impossível recuperar valor decimal informado.");
			}
		}
	}
	return $innum;
}

/*
 * Trata números antes de serem utilizados, validando se isnumeric, alterando vírgulas e separadores decimais, etc.
 */
function tratadouble($innum)
{

	if (empty($innum) and !is_numeric($innum)) { //!isnumeric para considerar zeros
		return "NULL"; //maf100811: possibilitar o envio de valores nulos para a tabela
	} else {
		$innum = str_replace(",", ".", $innum); //troca virgulas por pontos para permitir entrada livre de dados
		//http://php.net/manual/pt_BR/function.is-float.php#116960
		if (is_float($innum + 0) or is_numeric($innum + 0)) {
			return $innum;
		} else {
			die("Número double inválido para a coluna: " . $innum);
		}
	}
}

/*
 * Formata os valores para compor comandos DML
 */
function evaltipocoldb($intbl, $incol, $intype, $invlr, $qt = "'")
{

	switch ($intype) {
		case 'varchar':
			if (empty($invlr)) {
				$invlr = "";
			};
			$outstr = $qt . safe_string_escape($invlr) . $qt;
			break;
		case 'enum':
			if (empty($invlr)) {
				$invlr = "";
			};
			$outstr = $qt . safe_string_escape($invlr) . $qt;
			break;
		case 'json':
			if (empty($invlr)) {
				$invlr = "";
			};
			$outstr = $qt . addslashes($invlr) . $qt;
			break;
		case 'bigint':
			$outstr = "" . tratanumero($invlr) . "";
			break;
		case 'char':
			if (empty($invlr)) {
				$invlr = "";
			};
			$outstr = $qt . safe_string_escape($invlr) . $qt;
			break;
		case 'datetime':
			if (empty($invlr)) {
				$outstr = "null";
			} else {
				$outstr = $qt . validadatetime($invlr) . $qt;
			}
			break;
		case 'date':
			if (empty($invlr)) {
				$outstr = "null";
			} else {
				$outstr = $qt . validadate($invlr) . $qt;
			}
			break;
		case 'int':
			$outstr = "" . tratanumero($invlr) . "";
			break;
		case 'smallint':
			$outstr = "" . tratanumero($invlr) . "";
			break;
		case 'timestamp':
			if (empty($invlr)) {
			};
			$retdata = validadatetime($invlr);
			if ($retdata <> false) {
				$outstr = $qt . $retdata . $qt;
			} else {
				die("Erro formatando datetime da coluna [" . $intbl . "][" . $incol . "] de valor [" . $invlr . "]");
			}
			break;
		case 'time':
			$outstr = $qt . $invlr . $qt;
			break;
		case 'tinyint':
			$outstr = "" . tratanumero($invlr) . "";
			break;
		case 'longtext':
			if (empty($invlr)) {
				$invlr = "";
			};
			$outstr = $qt . safe_string_escape($invlr) . $qt;
			break;
		case 'double':
			$outstr = "" . tratadouble($invlr) . "";
			break;
		case 'decimal':
			$outstr = "" . tratanumero($invlr) . "";
			break;
		case 'longtext':
			$outstr = $qt . safe_string_escape($invlr) . $qt;
			break;
		case 'text':
			$outstr = $qt . safe_string_escape($invlr) . $qt;
			break;
		default:
			die("evaltipocoldb: Tipo[" . $intype . "] da Coluna[" . $incol . "] da Tabela [" . $intbl . "] nao previsto;");
			break;
	}

	return $outstr;
}

/*
 * Função para substituir mysql_real_escape_string
 * Isto porque em alguns casos, a string pode ja ter sido escapada por algum processo anterior
 */
function safe_string_escape($str)
{
	$len = strlen($str);
	$escapeCount = 0;
	$targetString = '';
	for ($offset = 0; $offset < $len; $offset++) {
		switch ($c = $str[$offset]) {
			case "'":
				if ($escapeCount % 2 == 0) $targetString .= "\\";
				$escapeCount = 0;
				$targetString .= $c;
				break;
			case '"':
				if ($escapeCount % 2 == 0) $targetString .= "\\";
				$escapeCount = 0;
				$targetString .= $c;
				break;
			case '\\':
				$escapeCount++;
				$targetString .= $c;
				break;
			default:
				$escapeCount = 0;
				$targetString .= $c;
		}
	}
	return $targetString;
}

/*
 * Formatar datetime vindo do banco
 */
function dmahms($indatetime, $inshort = false)
{
	if ($indatetime) {

		//considerar data e hora em formato compacto
		$ano = ($inshort) ? substr($indatetime, 2, 2) : substr($indatetime, 0, 4);

		$mes = substr($indatetime, 5, 2);
		$dia = substr($indatetime, 8, 2);
		$hor = substr($indatetime, 11, 2);
		$min = substr($indatetime, 14, 2);
		$seg = substr($indatetime, 17, 2);

		if (!checkdate($mes, $dia, $ano)) {
			return $indatetime;
		}

		if (($hor < 0) or ($hor > 23) or
			($min < 0) or ($min > 59) or
			($seg < 0) or ($seg > 59)
		) {
			return $indatetime;
		}

		$dataformatada = substr("00" . $dia, -2) . "/" .
			substr("00" . $mes, -2) . "/" .
			$ano     . " " .
			substr("00" . $hor, -2) . ":" .
			substr("00" . $min, -2);

		//considerar data e hora em formato compacto
		$dataformatada .= ($inshort) ? "" : ":" . substr("00" . $seg, -2);

		return $dataformatada;
	} else {
		return ($indatetime);
	}
}

/*
 * Formatar date vindo do banco
 */
function dma($indatetime, $inshort = false)
{
	if (empty($indatetime) or $indatetime == null) {
		return "";
	} elseif ($indatetime) {
		//considerar data e hora em formato compacto
		$ano = ($inshort) ? substr($indatetime, 2, 2) : substr($indatetime, 0, 4);

		$mes = substr($indatetime, 5, 2);
		$dia = substr($indatetime, 8, 2);

		if (!checkdate($mes, $dia, $ano)) {
			return $indatetime;
		}

		$dataformatada = substr("00" . $dia, -2) . "/" .
			substr("00" . $mes, -2) . "/" .
			$ano;
		return $dataformatada;
	}
}

function validadatetime($indata)
{
	//echo $indata;
	$indata = str_replace("/", "-", $indata);
	$indata = str_replace("T", " ", $indata); //maf031213: considerar campos html datetime
	$indata = trim($indata);

	$arrdatetime = explode(" ", $indata);

	/*
	 * maf30042010: verifica se foi enviado mais de 1 espaco na data
	 * maf221116: nao realizar verificacao para valores vazios 
	 */
	if (empty($indata) or $indata == null) {
		return "";
	} else {

		if (count($arrdatetime) > 2) {
			echo "\nErro validadatetime: Mais de um espaco foi informado na data enviada [" . $indata . "]";
			return false;
		} elseif (count($arrdatetime) < 2) { //maf031213: caso a hora nao seja enviada, criar uma chave com hora zerada
			array_push($array, "00:00:00");
		}

		$data = $arrdatetime[0];
		$hora = $arrdatetime[1];

		if (!isset($_SESSION["parformatodataweb"])) {
			$_SESSION["parformatodataweb"] = strtolower(retpar("formatodataweb"));
		}
		//die($_SESSION["parformatodataweb"]);


		$parformatodataweb = $_SESSION["parformatodataweb"];
		$parformatohoraweb = strtolower("hns");

		$idata = count($datapartes = explode('-', $data));
		$ihora = count($horapartes = explode(':', $hora));

		$dia = intval($datapartes[strpos($parformatodataweb, 'd')]);
		$mes = intval($datapartes[strpos($parformatodataweb, 'm')]);
		$ano = intval($datapartes[strpos($parformatodataweb, 'a')]);
		$hor = intval($horapartes[strpos($parformatohoraweb, 'h')]);
		$min = intval($horapartes[strpos($parformatohoraweb, 'n')]);
		$seg = intval($horapartes[strpos($parformatohoraweb, 's')]);

		if (strlen($ano) == 2) {
			$ano = "20" . $ano;
		}

		if (($idata != 3) or
			(strlen($ano) <> 4)
		) {
			return false;
		};

		if (($ihora != 3) and //Aceita HH:MM:SS e HH:MM e HH
			($ihora != 2) and
			($ihora != 1)
		) {
			return false;
		};

		if (!checkdate($mes, $dia, $ano)) {
			return false;
		}

		if (($hor < 0) or ($hor > 23) or
			($min < 0) or ($min > 59) or
			($seg < 0) or ($seg > 59)
		) {
			return false;
		}

		$dataformatada =  $ano . "-" .
			substr("00" . $mes, -2) . "-" .
			substr("00" . $dia, -2) . " " .
			substr("00" . $hor, -2) . ":" .
			substr("00" . $min, -2) . ":" .
			substr("00" . $seg, -2);

		return $dataformatada;
	}
}

function validadate($indata)
{

	$indata = str_replace("/", "-", $indata);

	$datapartes = explode("-", $indata);

	if (!isset($_SESSION["parformatodataweb"])) {
		$_SESSION["parformatodataweb"] = strtolower(retpar("formatodataweb"));
	}

	$pformatodataweb = $_SESSION["parformatodataweb"];

	$idata = count($datapartes);

	$dia = intval($datapartes[strpos($pformatodataweb, 'd')]);
	$mes = intval($datapartes[strpos($pformatodataweb, 'm')]);
	$ano = intval($datapartes[strpos($pformatodataweb, 'a')]);

	if (($idata != 3) or
		(strlen($ano) <> 4)
	) {
		return false;
	};

	if (!checkdate($mes, $dia, $ano)) {
		return false;
	}

	$dataformatada =  $ano . "-" .
		substr("00" . $mes, -2) . "-" .
		substr("00" . $dia, -2);

	return $dataformatada;
}
/*
 * HPB 23/12/2010
 * validar campos tipo time 00:00:00 
 */
function validatime($intime)
{

	$timepartes = explode(":", $intime);

	$itime = count($timepartes);

	if ($itime > 3  or $itime < 2) {
		return false;
	};
	if ((!is_numeric($timepartes[0])) and  (strlen($timepartes[0]) <> 0)) {
		return false;
	}
	if ((!is_numeric($timepartes[1])) and  (strlen($timepartes[1]) <> 0)) {
		return false;
	}
	if ((!is_numeric($timepartes[2])) and  (strlen($timepartes[2]) <> 0)) {
		return false;
	}

	if ($idtime == 2) {
		$timepartes[2] = "00";
	};

	$timeformatada = $timepartes[0] . ":" . $timepartes[1] . ":" . $timepartes[2];

	return $timeformatada;
}

// GVT - 28/04/2020 - Função busca imagem padrão de email da empresa, converte em base64, 
//					- monta o layout da imagem do email com base no tipo de email e retorna 
//					- o código HTML, se não retorna FALSE.

function imagemtipoemailempresa($tipoemail, $idempresa = 0, $email)
{
	if (!empty($tipoemail) and !empty($idempresa) and $idempresa != 0 and !empty($email)) {

		if (($tipoemail == "EMAILCONTATOEMPRESA" or $tipoemail == "RESULTADOOFICIAL") and $idempresa == 1) {

			$sql = "SELECT a.nomefantasia as nome,e.caminho 
			FROM empresaimagem e join empresa a on (e.idempresa = a.idempresa) 
			WHERE e.tipoimagem = 'RODAPEEMAIL'
			AND e.idempresa = 1
			AND e.subtipo = '" . $tipoemail . "'";
			$res = d::b()->query($sql) or die("Falha ao buscar rodapé de resultados. SQL: " . $sql);

			$row = mysql_fetch_assoc($res);

			if (!empty($row["caminho"])) {
				$row["caminho"] = str_replace("../", "", $row["caminho"]);
				$caminho = _CARBON_ROOT . $row["caminho"];
			} else {
				return false;
			}

			$imagedata = file_get_contents($caminho);
			if ($imagedata) {
				$imageb64 = base64_encode($imagedata);
				$rodape = '
				<style>
					.info-laudo{
						color:rgb(151,151,151);
						background: rgb(245,245,245);
						padding: 3px 10px;
						font-weight: 600;
						font-size: 10px;
					}

					.a-link-laudo{
						margin-right:5px;
						margin-left 3px;
					}

					.link-laudo{
						color:rgb(151,151,151);
						text-decoration: none;
					}
				</style>
				<p>Atenciosamente,</p>
				<table style="font-family: Montserrat, sans-serif, Helvetica; width: 800px; height: 181px; vertical-align: top; background-repeat: no-repeat;">
				<tbody>
				<tr style="height: 170px;">
				<td id="_temp" style="position: relative;">
				<div id="_template" style="height: 100px; width: 800px; color: white;"><img style="width: 100%;" src="data:image/png;base64,' . $imageb64 . '" alt="" /></div>
				</td>
				</tr>
				<tr style="height: 20px;">
					<td>
						<div class="info-laudo">
							Rodovia BR 365, Km 615, S/N • Alvorada • Uberlândia/MG • <b><a href="http://www.laudolab.com.br" target="_blank" class="link-laudo">www.laudolab.com.br</a></b> • <a class="a-link-laudo link-laudo" href="https://www.instagram.com/laudolaboratorio/" target="_blank"><i class="fa fa-instagram"></i></a><a class="link-laudo" href="https://www.linkedin.com/company/laudolaboratorio/" target="_blank"><i class="fa fa-linkedin"></i></a> laudolaboratorio
						</div>
					</td>
				</tr>
				<tr style="height: 56px;">
				<td style="font-size: 10px; text-align: justify;" colspan="2">Laudo Laborat&oacute;rio Av&iacute;cola Uberl&acirc;ndia Ltda <br />CNPJ: 23.259.427/0001-04 - I.E: 7023871770001 - Rod. BR 365, KM 615 - S/N&ordm; - Alvorada - 38.407-180 - Uberl&acirc;ndia/MG <br />As informa&ccedil;&otilde;es contidas neste email e documentos anexos s&atilde;o particulares, sigilosos e de propriedade do Laudo Laborat&oacute;rio Av&iacute;cola. Se voc&ecirc; n&atilde;o for o destinat&aacute;rio ou se recebeu esta mensagem irregularmente ou por erro, apague o e-mail e avise o remetente. Este e-mail n&atilde;o pode ser divulgado, armazenado, utilizado, publicado ou copiado por qualquer um que n&atilde;o o(s) seu(s) destinat&aacute;rio(s).</td>
				</tr>
				</tbody>
				</table>';

				return $rodape;
			} else {
				return false;
			}
		}

		$dominio = $email;

		$sqlempresa = "SELECT * FROM empresa WHERE idempresa = " . $idempresa;
		$resempresa = d::b()->query($sqlempresa) or die("Erro ao buscar informações da empresa: " . mysqli_error(d::b()) . $sqlempresa);
		$rowempresa = mysqli_fetch_assoc($resempresa);

		$sqlarq = "SELECT * from empresaimagem where tipoimagem = 'IMAGEMEMAIL' and idempresa = " . $idempresa;

		$resarq = d::b()->query($sqlarq);
		$rowarq = mysqli_fetch_assoc($resarq);

		$sqlrodape = "select * from empresarodapeemail where tipoenvio = '" . $tipoemail . "' and idempresa=" . $idempresa . " limit 1";
		$resrodape = d::b()->query($sqlrodape) or die("Erro ao buscar rodapés de email : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlrodape);
		$qtdrodape = mysqli_num_rows($resrodape);
		if ($qtdrodape > 0) {
			$rowrodape = mysqli_fetch_assoc($resrodape);
			$_titulo = $rowrodape["titulo"];
			$_texto = $rowrodape["texto"];
			$_telefone = $rowrodape["telefone"];
		} else {
			$_titulo = $rowempresa["nomefantasia"];
			$_texto = "As informa&ccedil;&otilde;es contidas neste email e documentos anexos s&atilde;o particulares, sigilosos e de propriedade da empresa.";
			$_telefone = "";
		}

		$rowarq["caminho"] = str_replace("../", _CARBON_ROOT, $rowarq["caminho"]);
		$imagedata = file_get_contents($rowarq["caminho"]);
		if ($imagedata) {
			$imageb64 = base64_encode($imagedata);

			if (!empty($rowempresa["site"])) {
				$_link = '
						<tr>
							<td style="font-size: 11px; cursor: pointer;" colspan="2"><a href="http://' . $rowempresa["site"] . '">http://' . $rowempresa["site"] . '</a></td>
						</tr>';
			} else {
				$_link = '';
			}

			$textini = '
						<table style="font-family: Arial, SANS-SERIF, Helvetica; width: 600px; height:100px; vertical-align: top; background-repeat: no-repeat;background-size: 600px 87px;" background="data:image/png;base64,' . $imageb64 . '">
							<tbody>
							
							<tr>
							<td style="font-weight: bold; padding-left: 10px; font-size: 16px; padding-top:2px; padding-bottom: 0px; vertical-align: middle; line-height: 20px; height: 10px;">';
			$textmeio = '
						</td>
							</tr>
							<tr>
							<td style=" padding-left: 10px; font-size: 12px; padding-bottom: 5px;    vertical-align: middle; line-height: 10px; height: 0px; padding-top: 0px;">';
			$textfim = '
					</td>
						</tr>
						<tr>
						<td style="padding: 0px; padding-left: 10px;  padding-bottom: 25px;  font-size: 11px; font-weight: bold; vertical-align: top; line-height: 13px; color: #1866a5;">(' . $rowempresa["DDDPrestador"] . ') ' . $rowempresa["TelefonePrestador"] . ' <br />' . $_telefone . '<br></td>
						</tr>
						</tbody>
						</table>
						<table style="font-family: Arial, SANS-SERIF, Helvetica; width: 600px; vertical-align: top; background-repeat: no-repeat;">
							<tr>
							<td style="font-size: 10px; text-align: justify;" colspan="2">' . $rowempresa["razaosocial"] . ' <br />CNPJ: ' . formatarCPF_CNPJ($rowempresa["cnpj"], true) . ' - I.E: ' . $rowempresa["inscestadual"] . ' - ' . $rowempresa["xlgr"] . ' - ' . $rowempresa["nro"] . ' - ' . $rowempresa["xbairro"] . ' - ' . formatarCEP($rowempresa["cep"], true) . ' - ' . $rowempresa["xmun"] . '/' . $rowempresa["uf"] . ' 
							<br />' . $_texto . '
							<br /> Se voc&ecirc; n&atilde;o for o destinat&aacute;rio ou se recebeu esta mensagem irregularmente ou por erro, apague o e-mail e avise o remetente. <br />Este e-mail n&atilde;o pode ser divulgado, armazenado, utilizado, publicado ou copiado por qualquer um que n&atilde;o o(s) seu(s) destinat&aacute;rio(s).</td>
							</tr>
							' . $_link . '
						</table>
						<br>';

			$rodape = $textini . $_titulo . $textmeio . $dominio . $textfim;
		} else {
			echo "Não foi possível encontrar o conteúdo da imagem";
			return false;
		}
	} else {
		echo "Parâmetros da função imagemtipoemailempresa inválidos";
		return false;
	}
	return $rodape;
}

function retpar($inparametro)
{

	//SELECT parametro, tipo FROM paraplweb p;

	//conectabanco();

	$sql = "select p.tipo, p.valor from " . _DBCARBON . "._paraplweb p where parametro = '" . $inparametro . "'";

	$result = mysql_query($sql);
	if (!$result) {
		die("<font size='1'><strong>Erro pesquisando Par?metro -" . $inparametro . "-</strong></font><p>" . mysql_error());
	}

	if (mysql_num_rows($result) == 0) {
		die("<font size='1'><strong>Configura??o do tipo -$inparametro- n?o retornou resultado.</strong></font><p>SQL: " . $sql);
	} else {
		while ($row = mysql_fetch_array($result)) {
			$ctipo  = $row["tipo"];
			$cvalor = ltrim(rtrim($row["valor"]));
		}
	}

	if ($ctipo == "texto") {
		return ($cvalor);
	} elseif ($ctipo == "inteiro") {
		return (int)$cvalor;
	} elseif ($ctipo == "booleano") {
		$toint = (int)$cvalor;
		$tobool = (bool)$toint;
		return ($tobool);
	} elseif ($ctipo == "datetime") {
		return ($cvalor);
	} elseif ($ctipo == "hyperlink") {
		return ($cvalor);
	} elseif ($ctipo == "html") {
		return ($cvalor);
	}
}

function retparerr($inerro)
{
	$sql = "select 
				erro
				, descricao, solucao 
			from " . _DBAPP . "._parerr 
			where erro = '" . $inerro . "'
			and status = 'ATIVO'";

	$result = d::b()->query($sql);
	if (!$result) {
		die("retparerr: Erro ao pesquisar tabela de Erros o item [" . $inerro . "]" . mysqli_error(d::b()));
	} else {
		$numr = mysqli_num_rows($result);
		if ($numr > 0) {
			$r = mysqli_fetch_assoc($result);
			$msgerro = $r["descricao"] . "\n\n" . $r["solucao"];
			return $msgerro;
		} else {
			return "Erro [" . $inerro . "] nao configurado na tabela de Erros;";
		}
	}
}

function retidnotafiscal($inidpessoa, $_idempresa)
{
	require_once(__DIR__ . "/../../form/controllers/fluxo_controller.php");

	$idfluxostatus = FluxoController::getIdFluxoStatus('nfs', 'ABERTO');

	$usuario = $_SESSION["SESSAO"]["USUARIO"];

	if (empty($inidpessoa)) {
		die("Id do Cliente não informado ao retornar ID da Nota Fiscal aberta");
	}
	//echo $inidpessoa; die;
	############################################################ LOCK
	/*	
	$lock = d::b()->query("LOCK TABLES notafiscal WRITE;");

	if(!$lock){
		echo "Erro ao efetuar [LOCK NOTAFISCAL]<br>\nErro: " . mysql_error();
		d::b()->query("UNLOCK TABLES;");
		die();
	}
*/
	############################################################ Captura sempre a última Nota fiscal aberta existente
	$sqlnf = "SELECT count(idnotafiscal) as quant, max(idnotafiscal) as idnotafiscal
			FROM notafiscal
			WHERE idpessoa = " . $inidpessoa . " AND idempresa=" . $_idempresa . " AND status = 'ABERTO'";
	$resnf = d::b()->query($sqlnf);
	$row = mysqli_fetch_assoc($resnf);
	$i = $row["quant"];

	if ($i > 0) { # Se existir pelo menos uma notafiscal em ABERTO
		//echo "entrou1";die;
		$idnotafiscal = $row["idnotafiscal"];
	} elseif (($i == 0) or (empty($i))) { # Se nao retornar nenhum registro, inserir uma nota fiscal em ABERTO

		$sqlv = "select f.mesmocnpj 
		from pessoa p 
			left join preferencia f on(f.idpreferencia=p.idpreferencia and f.status='ATIVO')
		where p.idpessoa= " . $inidpessoa . " limit 1 ";
		$resv = d::b()->query($sqlv);
		$rpref = mysqli_fetch_assoc($resv);

		if ($rpref['mesmocnpj'] == 'Y') {
			$mesmocnpj = 'Y';
		} else {
			$mesmocnpj = 'N';
		}


		//echo "entrou2";die;
		$sql = "INSERT INTO notafiscal (idempresa,idpessoa,exercicio,status,idfluxostatus,mostramesmocnpj,criadopor,criadoem) values (" . $_idempresa . "," . $inidpessoa . ",'" . date("Y") . "','ABERTO'," . $idfluxostatus . ",'" . $mesmocnpj . "','" . $usuario . "',sysdate());";
		$resins = d::b()->query($sql);
		if (!$resins) {
			echo "Erro inserindo Nota Fiscal aberta:<br>\n SQL: " . $sql . "<br>\n ERRO:" . mysql_error();
			//d::b()->query("UNLOCK TABLES;");
			die();
		}
		//Executa novamente a query da nota fiscal

		//$resnf = mysql_query($sqlnf);
		$resnf = d::b()->query("select LAST_INSERT_ID() as idnotafiscal");

		//$i = $row["quant"];
		$row = mysqli_fetch_assoc($resnf);

		$idnotafiscal = $row["idnotafiscal"];


		//if($i == 1){# Se existir somente uma notafiscal em ABERTO
		if (empty($idnotafiscal)) {
			echo "Nenhuma linha retornada ao recuperar nova nota fiscal. Imposs?vel continuar:<br>\n SQL: " . $sqlnf . "<br>\n";
			//d::b()->query("UNLOCK TABLES;");
			die();
		}

		$insnfconfpagar = new Insert();
		$insnfconfpagar->setTable("nfsconfpagar");
		$insnfconfpagar->idnotafiscal = $idnotafiscal;
		$idnfconfpagar = $insnfconfpagar->save();



		FluxoController::inserirFluxoStatusHist('nfs', $idnotafiscal, $idfluxostatus, 'PENDENTE');
	}/* Esta condição caiu em desuso por ser possível um cliente ter mais de uma NF em aberto
	elseif($i > 1){# Existe mais de uma nota fiscal aberta para o mesmo cliente
	echo "Existe mais de 1 nota fiscal ABERTA para o mesmo cliente. Impossível continuar.<br> Entre em contato com o Administrador do Sistema.<br>\n SQL: " . $sqlnf;
	mysql_query("UNLOCK TABLES;");
	die();
	}*/

	//d::b()->query("UNLOCK TABLES;");

	return $idnotafiscal;
}


/*
 * Recupera a quantidade total de registros da última query executada, desconsiderando a cláusula LIMIT
 * https://dev.mysql.com/doc/refman/5.0/en/information-functions.html
 */
function myFoundRows()
{
	$sqlFound = "SELECT FOUND_ROWS() as foundrows";
	$resFound = mysql_query($sqlFound) or die("Erro ao recuperar quantidade de registros com FOUND_ROWS(): " . mysql_error());
	$rFround = mysql_fetch_assoc($resFound);
	return $rFround["foundrows"];
}

/*
 * Cria lista de options para tag <select> a partir de SQL ou Array informado
 */
function fillselect($tmpsql_arr, $tmpintselected = '', $evento = false)
{

	$booencontrou = false;

	if (is_array($tmpsql_arr)) {

		while (list($key, $vlr) = each($tmpsql_arr)) {
			if (empty($tmpintselected)) {
				echo "<option value='" . $key . "'>" . $vlr . "</option>\n";
			} else {
				if ($key == $tmpintselected) {
					$booencontrou = true;
					echo "<option value='" . $key . "' selected>" . $vlr . "</option>\n";
				} else {
					echo "<option value='" . $key . "'>" . $vlr . "</option>\n";
				}
			}
		}
	} else {

		//echo($tmpsql_arr);
		$result = d::b()->query($tmpsql_arr);
		if (!$result) {
			echo ("<option value='' cbstat='fillsel2'>* ERRO FILLSELECT *</option>\n<!-- " .  mysqli_error(d::b()) . " -->\n");
			return;
		}

		while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
			if (empty($tmpintselected)) {
				echo "<option value='" . $row[0] . "'>" . $row[1] . "</option>\n";
			} else {
				if ($row[0] == $tmpintselected) {
					$booencontrou = true;
					echo "<option value='" . $row[0] . "' selected>" . $row[1] . "</option>\n";
				} else {
					echo "<option value='" . $row[0] . "'>" . $row[1] . "</option>\n";
				}
			}
		}
	}

	//maf150513: Caso o valor do DB nao seja encontrado, colocar aviso para o usuario no final
	if (!empty($tmpintselected) and $booencontrou == false and $evento == false) {
		echo "<option value='" . $tmpintselected . "' selected cbstat='fillsel'>* ERRO: VALOR [" . $tmpintselected . "] NÃO EXISTENTE! *</option>\n";
	}
}

/*
 * cria lista de options para tag <select> a partir de SQL ou Array informado para campos deletados
 */
function fillselectdeletado($tmpsql_arr, $tmpintselected = '')
{

	$booencontrou = false;

	if (is_array($tmpsql_arr)) {

		while (list($key, $vlr) = each($tmpsql_arr)) {
			if (empty($tmpintselected)) {
				echo "<option value='" . $key . "'>" . $vlr . "</option>\n";
			} else {
				if ($key == $tmpintselected) {
					$booencontrou = true;
					echo "<option value='" . $key . "' selected>" . $vlr . "</option>\n";
				}
			}
		}
	} else {

		//echo($tmpsql_arr);
		$result = d::b()->query($tmpsql_arr);
		if (!$result) {
			echo ("<option value='' cbstat='fillsel2'>* ERRO FILLSELECT *</option>\n<!-- " .  mysqli_error(d::b()) . " -->\n");
			return;
		}

		while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
			if (empty($tmpintselected)) {
				echo "<option value='" . $row[0] . "'>" . $row[1] . "</option>\n";
			} else {
				if ($row[0] == $tmpintselected) {
					$booencontrou = true;
					echo "<option value='" . $row[0] . "' selected>" . $row[1] . "</option>\n";
				}
			}
		}
	}
}

/*
 * Cria lista de options para plugin SELECT2 a partir de SQL ou Array informado
 */
function fillselect2($tmpsql_arr, $tmpcolunaverificacao = '')
{

	$booencontrou = false;

	if (is_array($tmpsql_arr)) {

		while (list($key, $vlr) = each($tmpsql_arr)) {
			if (empty($tmpintselected)) {
				echo "<option value='" . $key . "'>" . $vlr . "</option>\n";
			} else {
				if ($key == $tmpintselected or $vlr == $tmpintselected) {
					$booencontrou = true;
					echo "<option value='" . $key . "' selected>" . $vlr . "</option>\n";
				} else {
					echo "<option value='" . $key . "'>" . $vlr . "</option>\n";
				}
			}
		}
	} else {

		$result = mysql_query($tmpsql_arr);
		if (!$result) {
			echo ("<option value='' cbstat='fillsel3'>* ERRO FILLSELECT *</option>\n<!-- " .  mysql_error() . " -->\n");
			return;
		}

		while ($row = mysql_fetch_assoc($result)) {
			$icoluna = 0;
			$strvalue = "";
			$stratributos = "";
			$strdisplay = "";
			$strselected = "";
			while (list($key, $vlr) = each($row)) {
				if ($icoluna == 0) {
					$strvalue = $vlr;
				}
				if ($key == $tmpcolunaverificacao and !empty($vlr)) {
					$strselected = "selected";
					$booencontrou = true;
				}
				$stratributos .= $key . "='" . $vlr . "' ";
				$strdisplay = $vlr;
				$icoluna++;
			}

			echo "<option " . $strselected . " value='" . $strvalue . "' " . $stratributos . ">" . $strdisplay . "</option>\n";
		}
	}

	//Caso o valor nao seja encontrado, colocar aviso para o usuario no final
	if (!empty($tmpintselected) and $booencontrou == false) {
		echo "<option value='" . $tmpintselected . "' selected cbstat='fillsel4'>* ERRO: VALOR [" . $tmpintselected . "] NÃO EXISTENTE! *</option>\n";
	}
}


/*
 * Cria lista de <option>s e <optgroup>s para tag <select> a partir de SQL. Não aceita arrays.
 */
function fillSelectOptGroup($tmpsql_arr)
{

	$booencontrou = false;

	$result = mysql_query($tmpsql_arr);
	if (!$result) {
		echo ("<option value='' cbstat='fillopt'>* ERRO FILLSELECT *</option>\n<!-- " .  mysql_error() . " -->\n");
		return;
	}

	$novoGrupo = false;
	$fechaGrupo = "";
	$grupoAnterior = "";
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		if ($grupoAnterior != $row[1]) {
			echo $fechaGrupo . "<optgroup label='" . $row[1] . "'>";
			$grupoAnterior = $row[1];
		}

		echo "<option value='" . $row[0] . "'>" . $row[2] . "</option>\n";
	}

	//maf150513: Caso o valor do DB nao seja encontrado, colocar aviso para o usuario no final
	if (!empty($tmpintselected) and $booencontrou == false) {
		echo "<option value='" . $tmpintselected . "' selected cbstat='fillopt2'>* ERRO: VALOR [" . $tmpintselected . "] NÃO EXISTENTE! *</option>\n";
	}
}

function fillselectNoError($tmpsql_arr, $tmpintselected = '')
{

	$booencontrou = false;

	if (is_array($tmpsql_arr)) {

		while (list($key, $vlr) = each($tmpsql_arr)) {
			if (empty($tmpintselected)) {
				echo "<option value='" . $key . "'>" . $vlr . "</option>\n";
			} else {
				if ($key == $tmpintselected) {
					$booencontrou = true;
					echo "<option value='" . $key . "' selected>" . $vlr . "</option>\n";
				} else {
					echo "<option value='" . $key . "'>" . $vlr . "</option>\n";
				}
			}
		}
	} else {

		//echo($tmpsql_arr);
		$result = d::b()->query($tmpsql_arr);
		if (!$result) {
			echo ("<option value='' cbstat='fillsel2'>* ERRO FILLSELECT *</option>\n<!-- " .  mysqli_error(d::b()) . " -->\n");
			return;
		}

		while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
			if (empty($tmpintselected)) {
				echo "<option value='" . $row[0] . "'>" . $row[1] . "</option>\n";
			} else {
				if ($row[0] == $tmpintselected) {
					$booencontrou = true;
					echo "<option value='" . $row[0] . "' selected>" . $row[1] . "</option>\n";
				} else {
					echo "<option value='" . $row[0] . "'>" . $row[1] . "</option>\n";
				}
			}
		}
	}

	//maf150513: Caso o valor do DB nao seja encontrado, colocar opção já selecionada para o usuario no final
	if (!empty($tmpintselected) and $booencontrou == false) {
		echo "<option value='" . $tmpintselected . "' selected>" . $tmpintselected . "</option>\n";
	}
}

/*
 * Monta estrutura de radio buttons com opcao para campo de especificacao <text>
 * Devem ser enviados os seguintes parametros
 * Obrigatorio -> Nome dos campos INPUT RADIO (mesmo nome conforme padrão html)
 * Obrigatorio -> Select com 3 colunas contendo: valor, descricao, e se aciona ou o campo de especificacao
 * Obrigatorio -> Valor para checked (valor que retorna nas variaveis automaticas do Carbon)
 * Nao Obrigatorio -> Quebra de linha <br>?
 */
function fillradio($tmpname, $tmpsql, $tmpchecked = "", $tmpseparador = "")
{

	$result = mysql_query($tmpsql);
	$br = "";
	while ($row = mysql_fetch_array($result)) {
		if (empty($tmpchecked)) {
			echo $br . "<input type='radio' name='" . $tmpname . "' value='" . $row[0] . "' >" . $row[1] . "\n";
		} else {
			if ($row[0] == $tmpchecked) {
				echo $br . "<input type='radio' name='" . $tmpname . "' value='" . $row[0] . "' checked='checked'>" . $row[1] . "\n";
			} else {
				echo $br . "<input type='radio' name='" . $tmpname . "' value='" . $row[0] . "' 	>" . $row[1] . "\n";
			}
		}
		$br = $tmpseparador;
	}
}

/*
 * Gerenciar as preferências do usuário armazenando em Json
 */
function userPref($inAcao, $inPath, $inValor = null)
{
	if ($_SESSION["SESSAO"]["SUPERUSUARIO"]) {
		return "{}";
	}
	/**
	 * @todo: validar UTF8
	 * 
		if(!mb_detect_encoding($item, 'utf-8', true)){
                //$item = ($item);
        }
	 */
	//$strValor = (mb_detect_encoding($strValor, 'utf-8', true))?($strValor):$strValor;
	//Alterar para $strValor = (mb_detect_encoding($inValor, 'utf-8', true))?($inValor):$inValor;

	$strValor = ($inValor === null) ? "JSON_OBJECT()" : "'" . $inValor . "'";;
	switch ($inAcao) {
		case "u":
			$sqlj = "update pessoa 
			set jsonpreferencias = json_set(jsonpreferencias,'$." . $inPath . "'," . $strValor . ")
			where  usuario='" . $_SESSION["SESSAO"]["USUARIO"] . "'";
			break;
		case "d":
			$sqlj = "update pessoa 
			set jsonpreferencias = json_remove(jsonpreferencias,'$." . $inPath . "')
			where  usuario='" . $_SESSION["SESSAO"]["USUARIO"] . "'";
			break;
		case "i":
			$sqlj = "update pessoa 
			set jsonpreferencias = JSON_INSERT(jsonpreferencias,'$." . $inPath . "',JSON_OBJECT())
			where usuario='" . $_SESSION["SESSAO"]["USUARIO"] . "'  
				  -- and JSON_CONTAINS_PATH(jsonpreferencias,'all','$.$inPath')=0";
			break;
		case "m":
			//A função JSON_MERGE_PATCH existe somente a partir do mysql 5.7.22
			$sqlj = "update pessoa 
				set jsonpreferencias=JSON_MERGE_PATCH(jsonpreferencias,'" . $inPath . "')
				where usuario ='" . $_SESSION["SESSAO"]["USUARIO"] . "'";
		default:
			break;
	}

	//MAF: Isolar a transacao para 'dirty mode'
	d::b()->query("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;");

	//die($sqlj);
	//echo($sqlj."\n");
	logBinario(false);
	//MCC 30/08/2019 comentada a execução pois está gerando lentidão no banco de dados.
	//maf: re-ativado para verificação se o problema foi resolvido
	//MCC 27/11/2019 recomentada a execução pois está gerando lentidão no banco de dados novamente.
	//GVT 23/12/2019 re-ativado.
	$res = d::b()->query($sqlj);
	logBinario(true);

	if ($inAcao == "m") {
		$afrows = mysqli_affected_rows(d::b());
		return '{"merge":"' . $afrows . '"}';
	} else {
		//Confirma a atualização
		$sqljc = "SELECT json_extract(jsonpreferencias,'$." . $inPath . "') as jsonpref 
				from pessoa where usuario='" . $_SESSION["SESSAO"]["USUARIO"] . "'";

		$resc = d::b()->query($sqljc) or die("userPref: " . mysqli_error(d::b()) . "\n" . $inAcao . ":" . $inPath . ":" . $inValor);
		$rc = mysqli_fetch_assoc($resc);

		if ($inAcao != "d") {
			return "{\"" . $inPath . "\":" . $rc["jsonpref"] . "}";
		} else {
			return "{}";
		}
	}
}
/*
 * Converter arrays ou strings para UTF8
 * Geralmente utilizado em conjunto (antes de) com a função json_encode, que só aceita UTF8
 */
function utf8($d)
{
	if (is_array($d)) {
		foreach ($d as $k => $v) {
			$d[$k] = utf8($v);
		}
	} else if (is_string($d)) {
		return ($d);
	}
	return $d;
}

/*
 * Gerar strings Json a partir de SQL
 * $inGeraDefinicao: gerar objeto json adicional, contendo o nome e ordenação das colunas
 */
function sql2json($insql, $inGeraDefinicao = true)
{

	//Executa o SQL
	$res = mysql_query($insql) or die("Erro str2json: " . mysql_error() . " \nSQL:" . $insql);

	//Substituirá o nome de colunas sem nomeclatura definida
	$padraoRegex = "/[^A-Za-z0-9]/";

	//Gera array com os nomes de colunas da consulta
	if ($inGeraDefinicao) {
		for ($i = 0; $i < mysql_num_fields($res); $i++) {
			$nomecol = mysql_field_name($res, $i);
			//Substitui caracteres especiais. Necessário em caso de colunas sem alias [ex: concat(col1,col2)]
			$names[] = preg_replace($padraoRegex, '', $nomecol);
		}
	}

	//Gera array com o resultado da consulta
	$arrRes = array();
	$i = 0;
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		foreach ($row as $key => $value) {
			//Substitui caracteres especiais. Necessário em caso de colunas sem alias [ex: concat(col1,col2)]
			$key = preg_replace($padraoRegex, '', $key);
			$arrRes[$i][$key] = $value;
		}
		$i++;
	}

	//Retorna o objeto json sozinho ou com os nomes das colunas (definicao)
	if ($inGeraDefinicao) {
		$arrTmp["cols"] = $names;
		$arrTmp["rows"] = $arrRes;

		$strJson = json_encode(utf8($arrTmp));
		$strJson = json_encode($arrTmp);
	} else {
		$strJson = json_encode(utf8($arrRes));
		$strJson = json_encode($arrRes);
	}

	return $strJson;
}

/*
 * Gerar cores CSS a partir de uma string. Geralmente utilizado para colorir o Avatar do usuário
 */
function str2Color($initial)
{
	$checksum = sha1($initial); //Obs: md5 gerou muitos "rosas"

	return (substr($checksum, 0, 2)) . (substr($checksum, 2, 2)) . (substr($checksum, 4, 2));

	/* versão RGB:
  return 
    "rgb(".hexdec(substr($checksum, 0, 2)).",".
    hexdec(substr($checksum, 2, 2)).",".
    hexdec(substr($checksum, 4, 2)).")";*/
}

/*
 * Gerar cor contrastante com a informada
 */
function colorContrastYIQ($hexcolor)
{
	$r = hexdec(substr($hexcolor, 0, 2));
	$g = hexdec(substr($hexcolor, 2, 2));
	$b = hexdec(substr($hexcolor, 4, 2));
	$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
	return ($yiq >= 128) ? 'black' : 'white';
}

/*
 * Tratar caracteres invalidos de estruturas json
 */
function jsonTrataValor($invlr)
{

	//trata aspas duplas atrapalham a string json
	$invlr = str_replace('"', '', $invlr);

	//remove quebras de linha
	$invlr = str_replace(array("\r\n", "\r", "\n"), '', $invlr);

	//remove quebras de linha
	$invlr = str_replace(array("\t"), '', $invlr);

	return $invlr;
}

/*
 * maf180914: Utilizando a chave privada do arquivo de certificado .pfx (armazenado no DB), assina-se qualquer string enviada
 * Isto garante que qualquer falsificação feita fora do sislaudo não gerará uma assinatura válida conforme o arquivo .pfx usado
 * Geralmente é interessante concatenar na string a assinar, o usuário que está assinando, para dificultar falsificações
 * Esta função retorna um array contendo os dados do certificado e a assinatura gerada
 */
function assinaturaDigitalA1($inStringAssinar)
{

	$arrAssinatura = array();

	$sqlce = "SELECT autorizacaoserpro, conteudo_p12, password FROM certificadodigital where status = 'ATIVO' and tipo = 'assinaturaresultado'";
	$resce = mysql_query($sqlce) or die("Erro ao recuperar Certificado Digital: " . mysql_error());
	if (mysql_num_rows($resce) == 0) {
		die("assinaturaDigitalA1: Nenhum certificado ATIVO foi encontrado.");
	} elseif (mysql_num_rows($resce) > 1) {
		die("assinaturaDigitalA1: Mais de 1 certificado ATIVO foi encontrado. Certifique-se de deixar somente 1 com status ATIVO.");
	} else {
		$rce = mysql_fetch_assoc($resce);

		$conteudobin = $rce["conteudo_p12"];
		$password = $rce["password"];

		$arrAssinatura["autorizacaoserpro"] = $rce["autorizacaoserpro"];

		//extrai o conteudo do certificado, para recuperar a chave PEM
		$results = array();
		$pemok = openssl_pkcs12_read($conteudobin, $results, $password);
		//print_r($results);die;

		if ($pemok) {

			//Recupera a data de validade
			$certinfo = openssl_x509_parse($results['cert']);
			//print_r($certinfo);die;

			//Numero de Serie do certificado
			$validade = date('d/m/Y', $certinfo["validTo_time_t"]);
			$arrAssinatura["serial"] = $certinfo["serialNumber"];

			//Assinatura do conteúdo da string
			openssl_sign($inStringAssinar, $signature, $results['pkey'], "md5");
			$arrAssinatura["assinatura"] = base64_encode(md5($signature));
			//fingerprint. Este deve bater com a visualização do certificado utilizando o browser de internet
			$output = null;
			$resource = openssl_x509_read($results['cert']);
			$result = openssl_x509_export($resource, $output);
			if ($result !== false) {
				$output = str_replace('-----BEGIN CERTIFICATE-----', '', $output);
				$output = str_replace('-----END CERTIFICATE-----', '', $output);

				$output = base64_decode($output);
				$arrAssinatura["fingerprint"] = implode(":", str_split(strtoupper(sha1($output)), 2)) . " ";
			}

			return $arrAssinatura;
		} else {
			echo "Erro ao ler PKEY: ", openssl_error_string();
		}
	}
}

/**
 * Função que retorna o valor de coluna de tabela do banco conforme valores passados
 * @param string $intab Tabela a ser pesquisada
 * @param int $incampoid Campo da cláusula where (WHERE [incampoid] = [inid]) 
 * @param string $incampo Campo que será retornado no SELECT
 * @param int $inid Valor para cláusula where (WHERE [incampoid] = [inid])
 */
function traduzid($intab, $incampoid, $incampo, $inid, $mostraerros = true)
{

	if (empty($inid) or ($inid === 0) or ($inid === "")) return "";

	$inid = (is_numeric($inid)) ? $inid : "'" . $inid . "'";

	if (!empty($inid)) {

		$sql = "select ifnull(" . $incampo . ",'') as campo from " . nomeTabela($intab) . " where " . $incampoid . "=" . $inid;

		$res = d::b()->query($sql);

		if (!$res && $mostraerros) {
			echo ("Erro ao traduzir ID: <BR> SQL: " . $sql);
		}
		$resp = mysqli_fetch_assoc($res);
		$ret = $resp["campo"];
		if (empty($ret) && $ret !== "") {
			return $mostraerros ? ("Traduzid[" + $inid + "]: Nenhum valor encontrado para [" . $incampo . "] <BR> SQL:" . $sql) : "";
		} else {
			return $ret;
		}
	}
}

/**
 * Função de encriptação 2 way
 */
function enc($string)
{
	if ((isset($string)) && (is_string($string))) {
		$enc_string = base64_encode($string);
		$enc_string = str_replace("=", "", $enc_string);
		$enc_string = strrev($enc_string);
		$md5 = md5($string);
		$enc_string = substr($md5, 0, 3) . $enc_string . substr($md5, -3);
	} else {
		$enc_string = "Parametro incorreto ou inexistente!";
	}
	return $enc_string;
}

/**
 * Função de des-encriptação 2 way
 */
function des($string)
{
	if ((isset($string)) && (is_string($string))) {
		$ini = substr($string, 0, 3);
		$end = substr($string, -3);
		$des_string = substr($string, 0, -3);
		$des_string = substr($des_string, 3);
		$des_string = strrev($des_string);
		$des_string = base64_decode($des_string);
		$md5 = md5($des_string);
		$ver = substr($md5, 0, 3) . substr($md5, -3);
		if ($ver != $ini . $end) {
			$des_string = "Erro na desencriptacao!";
			return false;
		}
	} else {
		$des_string = "des: Parametro incorreto ou inexistente!";
	}
	return $des_string;
}

/**
 * Tratar acentuação para evitar erros de strings inválidas em Json
 */
function acentos2ent($inStr, $inCodificacao = "UTF-8")
{
	return htmlentities($inStr, ENT_QUOTES, $inCodificacao);
}

function tirarAcentos($string)
{
	return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/", "/(Ç)/"), explode(" ", "a A e E i I o O u U n N c C"), $string);
}

/**
 * Formatar erros
 */
function mostraErro($inTitulo, $inTexto)
{
	die($inTitulo . "\n" . $inTexto);
}

function getobjempresaPessoa($idpessoa, $alias)
{
	$str = "-- CONDIÇÃO PARA FILTRAR EMPRESAS DO USUARIO
	AND EXISTS
	(
		select 1 from objempresa oe where oe.objeto = 'pessoa' 
		and oe.idobjeto = $idpessoa
		and oe.empresa = " . $alias . "idempresa
	) ";
	return $str;
}

function getobjempresaMod($modulo, $alias)
{

	$str = "-- CONDICAO PARA FILTRAR EMPRESAS DE ACORDO COM A CONFIGURAÇÃO MODULO   
			AND EXISTS( SELECT 
				1
			FROM

			" . _DBCARBON . "._modulo m 
			JOIN
			objempresa oe on oe.objeto = 'modulo' and oe.idobjeto = m.idmodulo
			WHERE
			m.modulo = '$modulo'
			and oe.empresa = " . $alias . "idempresa) ";

	return $str;
}

/**
 * Verifica se será utilizado prefixo de DB na tabela
 */
function nomeTabela($intab)
{
	$arrCustomTabDb = unserialize(_CUSTOMTABDB);
	if (array_key_exists($intab, $arrCustomTabDb)) {
		return $arrCustomTabDb[$intab] . "." . $intab;
	} else {
		return $intab;
	}
}

/**
 * Recuperar o banco de dados conforme a tabela informada. Utilizado em locais onde se faz consulta na information_schema
 */
function getDbTabela($intab)
{
	$arrCustomTabDb = unserialize(_CUSTOMTABDB);
	if (array_key_exists($intab, $arrCustomTabDb)) {
		return $arrCustomTabDb[$intab];
	} else {
		return _DBAPP;
	}
}

/*
 * Verificar se a tabela possui permissao de bypass no sistema
 */
function bypass($intab)
{
	$arrBypass = unserialize(_BYPASSMOD);
	if (array_key_exists($intab, $arrBypass)) {
		return true;
	} else {
		return false;
	}
}

/* Formatar ou retirar formatação de cpf ou cnpj - hermesp 23/08/2013
print formatarCPF_CNPJ("01001001000101",true);
 retorna 01.001.001/0001-01
print formatarCPF_CNPJ("01.001.001/0001-01",false); 
retorna 01001001000101
 */
function formatarCPF_CNPJ($campo, $formatado = true)
{
	//retira formato
	$codigoLimpo = preg_replace("/\D/", '', $campo);
	// pega o tamanho da string menos os digitos verificadores
	$tamanho = (strlen($codigoLimpo) - 2);
	//verifica se o tamanho do código informado é válido
	if ($tamanho != 9 && $tamanho != 12) {
		return false;
	}

	if ($formatado) {
		// seleciona a máscara para cpf ou cnpj
		$mascara = ($tamanho == 9) ? '###.###.###-##' : '##.###.###/####-##';

		$indice = -1;
		for ($i = 0; $i < strlen($mascara); $i++) {
			if ($mascara[$i] == '#') $mascara[$i] = $codigoLimpo[++$indice];
		}
		//retorna o campo formatado
		$retorno = $mascara;
	} else {
		//se não quer formatado, retorna o campo limpo
		$retorno = $codigoLimpo;
	}

	return $retorno;
}

//função para retirar acentos de uma string - hermesp 25-09-2013

function retira_acentos($texto)
{
	$array1 = array(
		"á",
		"à",
		"â",
		"ã",
		"ä",
		"é",
		"è",
		"ê",
		"ë",
		"í",
		"ì",
		"î",
		"ï",
		"ó",
		"ò",
		"ô",
		"õ",
		"ö",
		"ú",
		"ù",
		"û",
		"ü",
		"ç",
		"Á",
		"À",
		"Â",
		"Ã",
		"Ä",
		"É",
		"È",
		"Ê",
		"Ë",
		"Í",
		"Ì",
		"Î",
		"Ï",
		"Ó",
		"Ò",
		"Ô",
		"Õ",
		"Ö",
		"Ú",
		"Ù",
		"Û",
		"Ü",
		"Ç"
	);
	$array2 = array(
		"a",
		"a",
		"a",
		"a",
		"a",
		"e",
		"e",
		"e",
		"e",
		"i",
		"i",
		"i",
		"i",
		"o",
		"o",
		"o",
		"o",
		"o",
		"u",
		"u",
		"u",
		"u",
		"c",
		"A",
		"A",
		"A",
		"A",
		"A",
		"E",
		"E",
		"E",
		"E",
		"I",
		"I",
		"I",
		"I",
		"O",
		"O",
		"O",
		"O",
		"O",
		"U",
		"U",
		"U",
		"U",
		"C"
	);
	return str_replace($array1, $array2, $texto);
}

function retira_acentos_esp($texto)
{
	$array1 = array(
		"á",
		"à",
		"â",
		"ã",
		"ä",
		"é",
		"è",
		"ê",
		"ë",
		"í",
		"ì",
		"î",
		"ï",
		"ó",
		"ò",
		"ô",
		"õ",
		"ö",
		"ú",
		"ù",
		"û",
		"ü",
		"ç",
		"Á",
		"À",
		"Â",
		"Ã",
		"Ä",
		"É",
		"È",
		"Ê",
		"Ë",
		"Í",
		"Ì",
		"Î",
		"Ï",
		"Ó",
		"Ò",
		"Ô",
		"Õ",
		"Ö",
		"Ú",
		"Ù",
		"Û",
		"Ü",
		"Ç",
		"%",
		"$",
		"@",
		"&",
		"º",
		"'",
		'"',
		"´",
		"`",
		"?",
		"!"
	);
	$array2 = array(
		"a",
		"a",
		"a",
		"a",
		"a",
		"e",
		"e",
		"e",
		"e",
		"i",
		"i",
		"i",
		"i",
		"o",
		"o",
		"o",
		"o",
		"o",
		"u",
		"u",
		"u",
		"u",
		"c",
		"A",
		"A",
		"A",
		"A",
		"A",
		"E",
		"E",
		"E",
		"E",
		"I",
		"I",
		"I",
		"I",
		"O",
		"O",
		"O",
		"O",
		"O",
		"U",
		"U",
		"U",
		"U",
		"C",
		"",
		"",
		"",
		"",
		"",
		"",
		"",
		"",
		"",
		"",
		""
	);
	return str_replace($array1, $array2, $texto);
}


//função para acentos em uppstring - pedrolima 10-07-2024
function upperCaseAcentos($texto)
{
	$array1 = array("á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç");
	$array2 = array("Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç");
	return str_replace($array1, $array2, $texto);
}
//Recuperar expoentes/customizações conforme input original do usuário
function recuperaExpoente($inDouble, $inDoubleOriginal)
{

	//Caso não haja expoente/customizacao padrão, ou já esteja formatado
	//if(empty($inDoubleOriginal) or strpos(strtolower($inDouble),"e") or strpos(strtolower($inDouble),"d")){
	if (empty($inDoubleOriginal)) {
		return $inDouble;
	} else {

		if (strpos(strtolower($inDoubleOriginal), "d")) {
			$arrExp = explode('d', strtolower($inDoubleOriginal));
			return round($inDouble / $arrExp[1], 2) . "d" . $arrExp[1];
		} else {
			$arrExp = explode('e', strtolower($inDoubleOriginal));

			//return ((float)$inDouble / ("1e".$arrExp[1]+0))."e".$arrExp[1];
			//return (round($inDouble)/pow(10, $arrExp[1]))."e".$arrExp[1];
			//comentado e habilitada a linha com tratanumero pois o tratanumerovisualizacao esta tirando ponto e colocando virgula
			//return round(tratanumerovisualizacao($inDouble/pow(10, $arrExp[1])),2)."e".$arrExp[1];
			return round(tratanumero($inDouble / pow(10, $arrExp[1])), 2) . "e" . $arrExp[1];
			//399.05246299377575/pow(10,2.3)
		}
	}
}


/*
 * Recuperar informações de "objetos" genericamente
 * @param string		$inTipoobjeto String contendo o nome da tabela/view
 * @param int/string	$inIdobjeto Integer ou String com o valor da Primary Key a ser pesquisado na tabela
 * @param string		$inColuna Opcional: Nome da coluna a ser utilizada para consultar o ID desejado. Utilizado em caso de Views ou colunas alternativas à PK
 */
function getObjeto($inTipoobjeto, $inIdobjeto, $inColuna = false)
{
	if (empty($inTipoobjeto) or empty($inIdobjeto)) {
		return false;
	} else {
		if ($inTipoobjeto == "@todo:customizar qualquer retorno desejado neste ponto") {
			return false;
		} else {
			//Recupera nome da coluna PK
			$aTD = retarraytabdef($inTipoobjeto);

			if (count($aTD) == 0) {
				echo "getObjeto: Dicionário de dados para a tabela [" . $inTipoobjeto . "] não está configurado ou tabela não existe no DB";
				return false;
			}

			if (empty($aTD["#pkfld"]) || $inColuna) {
				if (!$inColuna) {
					echo "getObjeto: Tabela informada deve conter 1 coluna PK[" . $inTipoobjeto . "]";
					return false;
				} else {
					$aTD["#pkfld"] = $inColuna;
				}
			}

			//Verifica se conterá aspas na coluna PK
			$aspa = aspaTipoCol($aTD[$aTD["#pkfld"]]["type"]);

			//Verifica se possui coluna de IDEMPRESA
			/*
			if(!array_key_exists($inTipoobjeto, retBypassIdempresa())){
				$strEmpresa="idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]." and ";
			}
			*/
			//Recupera o conteúdo do objeto
			$sql = "select * from " . nomeTabela($inTipoobjeto) . "
				where " . $strEmpresa . "
					" . $aTD["#pkfld"] . "=" . $aspa . $inIdobjeto . $aspa;
		}
	}
	//Recupera os subitens do objeto conforme sqldet
	if ($sql) {
		$res = d::b()->query($sql) or die("getObjeto: Erro ao recuperar objeto[" . $inTipoobjeto . "]: " . mysqli_error(d::b()) . "\nSQL: " . $sql);
		$arrCol = mysqli_fetch_fields($res);
		$i = 0;
		while ($ritem = mysqli_fetch_assoc($res)) {
			$i++;
			//para cada coluna resultante do select cria-se um item no array
			foreach ($arrCol as $col) {
				$arrret[$col->name] = $ritem[$col->name];
			}
		}
	}

	return $arrret;
}

/*
 * Retorna aspa em caso de colunas de string
 */
function aspaTipoCol($inTipocol)
{
	switch ($inTipocol) {
		case "varchar":
			return "'";
		case "char":
			return "'";
		case "longtext":
			return "'";
		default:
			return '';
	}
}

/*
 * Sinalizar a presença do debug
 */
function verificaXDebug()
{
	if ((!empty($_COOKIE["XDEBUG_SESSION"]) || !empty($_GET["XDEBUG_SESSION_START"])) && ($_SERVER["HTTP_HOST"] == "localhost" || $_SERVER["HTTP_HOST"] == "127.0.0.1")) {
		return true;
	}
}

function espaco2nbsp($inStr)
{
	return str_replace(" ", "&nbsp;", $inStr);
}

function escape_string($inp)
{
	if (is_array($inp))
		return array_map(__METHOD__, $inp);

	if (!empty($inp) && is_string($inp)) {
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
	}

	return $inp;
}

class logger
{
	public $deferred = false;
	public $texto = "";
	public $tipolog = "";

	function armazenaDb()
	{

		$txtlog = escape_string($this->texto);
		$tipolog = escape_string($this->tipolog);

		$sqllog = "insert into log 
			(idempresa,sessao,tipolog,log,criadoem) values
			(1,'" . session_id() . "','" . $tipolog . "','" . $txtlog . "',now())";
		$rlog = d::b()->query($sqllog) or die("logger: Falha:\n" . mysqli_error(d::b()) . "\n" . $sqllog);
	}

	function log($inStr, $inTipo = "logger")
	{

		$lb = (empty($this->texto)) ? "" : "\n";
		$this->tipolog = $inTipo;

		if ($this->deferred) {
			$now = DateTime::createFromFormat('U.u', microtime(true));
			$now = "#" . $now->format("m-d-Y H:i:s.u");

			$this->texto .= $lb . $now . ":\n" . $inStr;
		} else {
			$this->texto = $inStr;
			$this->armazenaDb();
		}
	}

	function start()
	{
		$this->deferred = true;
	}

	function stop()
	{
		$this->deferred = false;
		$this->armazenaDb();
	}
}

$LOGGER = new logger();

/*
 * Formatar CEP
 * print formatarCEP("38407180",true); 
 * retorna 38407-180
 * print formatarCEP("38407-180",false);
 *  retorna 38407180
 */
function formatarCEP($campo, $formatado = true)
{
	//retira formato
	$codigoLimpo = preg_replace("[' '-./ t]", '', $campo);
	// pega o tamanho da string menos os digitos verificadores
	$tamanho = (strlen($codigoLimpo) - 2);
	//verifica se o tamanho do código informado é válido
	if ($tamanho != 6) {
		return false;
	}

	if ($formatado) {
		// seleciona a máscara para cpf ou cnpj
		$mascara =  '#####-###';

		$indice = -1;
		for ($i = 0; $i < strlen($mascara); $i++) {
			if ($mascara[$i] == '#') $mascara[$i] = $codigoLimpo[++$indice];
		}
		//retorna o campo formatado
		$retorno = $mascara;
	} else {
		//se não quer formatado, retorna o campo limpo
		$retorno = $codigoLimpo;
	}

	return $retorno;
}
/*
 * Classe com as colunas default de segurança do Carbon, para economizar código
 */
class ColunasAuditoria
{
	public $idempresa, $criadopor, $criadoem, $alteradopor, $alteradoem;
	public function __construct()
	{
		$this->idempresa	= $_SESSION["SESSAO"]["IDEMPRESA"];
		$this->criadopor	= $_SESSION["SESSAO"]["USUARIO"];
		$this->criadoem	= sysdate();
		$this->alteradopor	= $_SESSION["SESSAO"]["USUARIO"];
		$this->alteradoem	= sysdate();
	}
}

/*
 * Abstração de Insert
 */
class Insert extends ColunasAuditoria
{

	//Armazenar o nome da tabela
	protected $__tableName;
	protected $__sql;

	//Inicializa a classe, construindo as propriedades de ColunasAuditoria (padrão Carbon)
	public function __construct()
	{
		//Instancia colunas de auditoria
		parent::__construct();
	}

	//Armazena a tabela
	public function setTable($tableName)
	{
		$this->__tableName = $tableName;
	}

	//Monta o SQL
	public function getSQL()
	{
		$this->__sql = "INSERT INTO " . $this->__tableName . " (\n";
		$virg = "";
		//Colunas
		foreach ($this as $k => $v) {
			if (strpos($k, "__") !== 0) { //Desconsiderar propriedades de "controle" (nome tabela, etc)
				$this->__sql .= $virg . $k;
				$virg = ",";
			}
		}
		$this->__sql .= "\n)values(\n";
		$virg = "";
		//Values
		foreach ($this as $k => $v) {
			if (strpos($k, "__") !== 0) { //Desconsiderar propriedades de "controle" (nome tabela, etc) 
				//Hermesp - 16/02/2021 - Insere todos os caracteres com aspas pois entendia 01 como string
				/*if(is_numeric($v)){
					$this->__sql.=$virg.$v;
				}else{*/
				$this->__sql .= $virg . "'" . $v . "'";
				//}
				$virg = ",";
			}
		}
		$this->__sql .= ")";

		return $this->__sql;
	}

	//Executa o comando
	public function save()
	{
		$sql = $this->getSQL();
		$rsql = d::b()->query($sql) or die("Insert tabela [" . $this->__tableName . "]: " . mysqli_error(d::b()));
		$iid = mysqli_insert_id(d::b());
		return $iid;
	}
}

function sysdate()
{
	return date('Y-m-d H:i:s');
}

/*
 * Casos de servidor Nginx
 */
if (!function_exists('getallheaders')) {
	function getallheaders()
	{
		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}
/*
 * @todo: implementar caminho completo
 */
function getNomeArquivo($inArquivo)
{
	return end(explode(DIRECTORY_SEPARATOR, $inArquivo));
}

function getConfRelatorio($inIdrep)
{

	$sqlrep = "SELECT distinct
            r.idrep,
            r.rep,
            r.idreptipo,
            r.cssicone,
            r.url,
            r.showfilters,
            r.header,
            r.footer,
            r.tab,
            tc.code,
            r.newgrouppagebreak,
            r.pbauto,
            r.showtotalcounter,
            rc.col,
            rc.psqkey,
            rc.psqreq,
            if(tc.rotcurto>'',tc.rotcurto,rc.col) as rotulo,
            rc.idrepcol,
            rc.visres,
            rc.align,
            rc.grp,
            rc.ordseq,
            rc.ordtype,
            rc.tsum,
            rc.acsum,
            rc.acavg,
            rc.tavg,
            rc.mascara,
            rc.hyperlink,
            rc.entre,
            rc.inseridomanualmente,
            rc.calendario,
            rc.like,
            rc.inval,
            rc.in,
            rc.findinset,
            rc.json,
            tc.datatype,
            r.compl,
            rc.ordcol,
            rc.eixograph,
            r.tipograph,
            r.descr,
            r.rodape
        FROM
            " . _DBCARBON . "._rep r
            JOIN " . _DBCARBON . "._repcol rc ON (rc.idrep = r.idrep AND r.idrep = " . $inIdrep . ")
            LEFT JOIN " . _DBCARBON . "._mtotabcol tc ON (tc.tab = r.tab AND rc.col = tc.col)
            ORDER BY rc.ordcol";

	$rrep = d::b()->query($sqlrep) or die("[getConfRelatorio] Erro ao recuperar relatórios do módulo: " . mysql_error(d::b()));

	$arrRepConf = array();
	while ($r = mysql_fetch_assoc($rrep)) {

		$nomeColCan = strtolower(retira_acentos($r["col"]));


		$arrRepConf[$r["idrep"]]["rep"]                 = $r["rep"];
		$arrRepConf[$r["idrep"]]["tab"]                 = $r["tab"];
		$arrRepConf[$r["idrep"]]["url"]                 = $r["url"];
		$arrRepConf[$r["idrep"]]["ord"]                 = $r["ord"];
		$arrRepConf[$r["idrep"]]["idrep"]               = $r["idrep"];
		$arrRepConf[$r["idrep"]]["compl"]               = $r["compl"];
		$arrRepConf[$r["idrep"]]["descr"]               = $r["descr"];
		$arrRepConf[$r["idrep"]]["header"]              = $r["header"];
		$arrRepConf[$r["idrep"]]["footer"]              = $r["footer"];
		$arrRepConf[$r["idrep"]]["rodape"]              = $r["rodape"];
		$arrRepConf[$r["idrep"]]["pbauto"]              = $r["pbauto"];
		$arrRepConf[$r["idrep"]]["cssicone"]            = $r["cssicone"];
		$arrRepConf[$r["idrep"]]["tipograph"]           = $r["tipograph"];
		$arrRepConf[$r["idrep"]]["showfilters"]         = $r["showfilters"];
		$arrRepConf[$r["idrep"]]["showtotalcounter"]    = $r["showtotalcounter"];
		$arrRepConf[$r["idrep"]]["newgrouppagebreak"]   = $r["newgrouppagebreak"];
		//Colunas
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["in"]                     = $r["in"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["col"]                    = $r["col"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["grp"]                    = $r["grp"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["like"]                   = $r["like"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["code"]                   = $r["code"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["tsum"]                   = $r["tsum"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["tavg"]                   = $r["tavg"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["entre"]                  = $r["entre"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inval"]                  = $r["inval"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["align"]                  = $r["align"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["acsum"]                  = $r["acsum"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["acavg"]                  = $r["acavg"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["rotulo"]                 = $r["rotulo"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqreq"]                 = $r["psqreq"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqkey"]                 = $r["psqkey"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["visres"]                 = $r["visres"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["ordcol"]                 = $r["ordcol"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["mascara"]                = $r["mascara"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["datatype"]               = $r["datatype"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["hyperlink"]              = $r["hyperlink"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["eixograph"]              = $r["eixograph"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["findinset"]              = $r["findinset"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["calendario"]             = $r["calendario"];
		$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inseridomanualmente"]    = $r["inseridomanualmente"];

		if (($r["datatype"] == 'date' or $r["datatype"] == 'datetime') and $r["psqkey"] == 'Y') {
			$arrRepConf[$r["idrep"]]["_datas"][] = $nomeColCan;
		}
		//Colunas visíveis no relatório
		if ($r["visres"] == "Y") {
			if ($r["ordcol"] == 0) $r["ordcol"] = '';

			$arrRepConf[$r["idrep"]]["_colvisiveis"][$r["ordcol"]] = $nomeColCan;
		}
		//Colunas para order by
		if (!empty($r["ordtype"])) {
			if (empty($r["ordseq"]) and $r["ordseq"] !== 0)
				die("[getConfRelatorio]: Erro: A coluna [" . $nomeColCan . "] está configurada para ORDER BY, mas não possui uma posição informada");

			$arrRepConf[$r["idrep"]]["_orderby"][$r["ordseq"]] = $nomeColCan . " " . $r["ordtype"];
		}

		if ($r["grp"] == "Y") {
			$arrRepConf[$r["idrep"]]["_groupby"][] = $nomeColCan;
		}

		if (strlen(trim($r["json"])) > 0) {
			$fp = fopen("php://temp/", 'w');
			fputs($fp, $r["json"]);
			rewind($fp);
			ob_start();
			require "data://text/plain;base64," . base64_encode(stream_get_contents($fp));
			$jsoncol = ob_get_clean();
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["json"] = $jsoncol;
		}

		if (strlen(trim($r["code"])) > 0) {
			$fp = fopen("php://temp/", 'w');
			fputs($fp, $r["code"]);
			rewind($fp);
			ob_start();
			require "data://text/plain;base64," . base64_encode(stream_get_contents($fp));
			$codecol = ob_get_clean();
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["code"] = $codecol;
		}
	}

	return $arrRepConf;
}

/*
 * Recuperar informações de relatórios configurados
 */
function getConfRelatoriosModulo($inMod, $inCompleto = false, $inIdrep = false, $ordercol = 'rc.ordcol')
{

	$wrep = $inIdrep ? " and r.idrep=" . $inIdrep : " ";

	$sqlrep = "SELECT distinct
			r.idrep,
			r.rep,
			r.idreptipo,
			r.cssicone,
			r.url,
			r.showfilters,
			r.header,
			r.footer,
			r.tab,
			tc.code,
			r.newgrouppagebreak,
			r.pbauto,
			r.showtotalcounter,
			rc.col,
			rc.psqkey,
			rc.psqreq,
			if(tc.rotcurto>'',tc.rotcurto,rc.col) as rotulo,
			rc.idrepcol,
			rc.idrep,
			rc.visres,
			rc.align,
			rc.grp,
			rc.ordseq,
			rc.ordtype,
			rc.tsum,
			rc.acsum,
			rc.acavg,
			rc.tavg,
			rc.mascara,
			rc.hyperlink,
			rc.entre,
			rc.inseridomanualmente,
			rc.calendario,
			rc.entre,
			rc.like,
			rc.inval,
			rc.in,
			rc.findinset,
			rc.json,
			mr.ord,
			tc.datatype,
			r.compl,
			rc.ordcol,
			rc.eixograph,
			r.tipograph,
			r.descr,
			m.tab as tabfull,
			lr.flgunidade,
            CASE WHEN m.tipo='MODVINC' THEN mv.chavefts ELSE m.chavefts END as chavefts,
			rodape
		FROM
			" . _DBCARBON . "._rep r
			LEFT JOIN " . _DBCARBON . "._lprep lr on (lr.idrep = r.idrep and lr.flgunidade='Y')
			JOIN " . _DBCARBON . "._repcol rc on rc.idrep=r.idrep " . $wrep . "
			JOIN " . _DBCARBON . "._modulorep mr ON mr.modulo='" . $inMod . "' and mr.idrep=r.idrep
			LEFT JOIN " . _DBCARBON . "._mtotabcol tc on tc.tab = r.tab AND rc.col = tc.col 
			LEFT JOIN " . _DBCARBON . "._modulo m ON m.modulo= mr.modulo
			LEFT JOIN " . _DBCARBON . "._modulo mv ON (mv.modulo=m.modvinculado)
			order by $ordercol";
	//die($sqlrep);
	$rrep = d::b()->query($sqlrep) or die("Erro ao recuperar relatórios do módulo: " . mysql_error(d::b()));
	$arrRepConf = array();
	while ($r = mysql_fetch_assoc($rrep)) {
		$nomeColCan = strtolower(retira_acentos(str_replace("", "", $r["col"])));
		//$nomeColCan=strtolower(retira_acentos(str_replace(" ", "", $r["col"])));

		//Caso seja necessário recuperar todos as colunas da consulta
		if ($inCompleto) {
			//Configurações do relatório
			$arrRepConf[$r["idrep"]]["header"] = $r["header"];
			$arrRepConf[$r["idrep"]]["footer"] = $r["footer"];
			$arrRepConf[$r["idrep"]]["rep"] = $r["rep"];
			$arrRepConf[$r["idrep"]]["tab"] = $r["tab"];
			$arrRepConf[$r["idrep"]]["compl"] = $r["compl"];
			$arrRepConf[$r["idrep"]]["descr"] = $r["descr"];
			$arrRepConf[$r["idrep"]]["rodape"] = $r["rodape"];
			$arrRepConf[$r["idrep"]]["newgrouppagebreak"] = $r["newgrouppagebreak"];
			$arrRepConf[$r["idrep"]]["pbauto"] = $r["pbauto"];
			$arrRepConf[$r["idrep"]]["showtotalcounter"] = $r["showtotalcounter"];
			$arrRepConf[$r["idrep"]]["tabfull"] = $r["tabfull"];
			$arrRepConf[$r["idrep"]]["chavefts"] = $r["chavefts"];
			$arrRepConf[$r["idrep"]]["tipograph"] = $r["tipograph"];
			$arrRepConf[$r["idrep"]]["flgunidade"] = $r["flgunidade"];
			//Colunas
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["rotulo"] = $r["rotulo"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["col"] = $r["col"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqreq"] = $r["psqreq"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["calendario"] = $r["calendario"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["entre"] = $r["entre"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["like"] = $r["like"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inval"] = $r["inval"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["in"] = $r["in"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["findinset"] = $r["findinset"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqkey"] = $r["psqkey"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["code"] = $r["code"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["datatype"] = $r["datatype"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["visres"] = $r["visres"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["align"] = $r["align"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["hyperlink"] = $r["hyperlink"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["grp"] = $r["grp"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["tsum"] = $r["tsum"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["acsum"] = $r["acsum"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["acavg"] = $r["acavg"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["tavg"] = $r["tavg"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["mascara"] = $r["mascara"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["entre"] = $r["entre"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inseridomanualmente"] = $r["inseridomanualmente"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["ordcol"] = $r["ordcol"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["eixograph"] = $r["eixograph"];

			if (($r["datatype"] == 'date' or $r["datatype"] == 'datetime') and $r["psqkey"] == 'Y') {
				$arrRepConf[$r["idrep"]]["_datas"][] = $nomeColCan;
			}
			//Colunas visíveis no relatório
			if (
				$r["visres"] == "Y" //and $r["inseridomanualmente"]=="N"
			) {
				if ($r["ordcol"] == 0) {
					$r["ordcol"] = '';
				}
				$arrRepConf[$r["idrep"]]["_colvisiveis"][$r["ordcol"]] = $nomeColCan;
			}
			//Colunas para order by
			if (!empty($r["ordtype"])) {
				if (empty($r["ordseq"]) and $r["ordseq"] !== 0) die("getConfRelatoriosModulo: Erro: A coluna [" . $nomeColCan . "] está configurada para ORDER BY, mas não possui uma posição informada");
				$arrRepConf[$r["idrep"]]["_orderby"][$r["ordseq"]] = $nomeColCan . " " . $r["ordtype"];
			}
			if ($r["grp"] == "Y") {
				$arrRepConf[$r["idrep"]]["_groupby"][] = $nomeColCan;
			}
		}

		//Montar colunas específicas para devolver ao browser. Isto evita enviar colunas desnecessárias por motivo de segurança e perormance
		$arrRepConf[$r["idrep"]]["idrep"] = $r["idrep"];
		$arrRepConf[$r["idrep"]]["rep"] = $r["rep"];
		$arrRepConf[$r["idrep"]]["cssicone"] = $r["cssicone"];
		$arrRepConf[$r["idrep"]]["showfilters"] = $r["showfilters"];
		$arrRepConf[$r["idrep"]]["ord"] = $r["ord"];
		$arrRepConf[$r["idrep"]]["url"] = $r["url"];
		if ($r["psqkey"] == "Y") {
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["rotulo"] = $r["rotulo"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["psqreq"] = $r["psqreq"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["calendario"] = $r["calendario"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["entre"] = $r["entre"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["like"] = $r["like"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["inval"] = $r["inval"];
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["findinset"] = $r["findinset"];
		}

		if (strlen(trim($r["json"])) > 0) {
			//Cria arquivo em memória ram com o código php a ser executado, para evitar uso de eval e também evitar a necessidade de geração de arquivos externos na pasta "eventcode"
			$fp = fopen("php://temp/", 'w');
			fputs($fp, $r["json"]);
			rewind($fp);
			ob_start();	//Não gerar saída para o browser
			require "data://text/plain;base64," . base64_encode(stream_get_contents($fp));
			$jsoncol = ob_get_clean(); //Limpa a saída antes que seja enviada para o browser
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["json"] = $jsoncol; //Recupera os valores gerados pelo include

		}
		if (strlen(trim($r["code"])) > 0) {
			//Cria arquivo em memória ram com o código php a ser executado, para evitar uso de eval e também evitar a necessidade de geração de arquivos externos na pasta "eventcode"
			$fp = fopen("php://temp/", 'w');
			fputs($fp, $r["code"]);
			rewind($fp);
			ob_start();	//Não gerar saída para o browser
			require "data://text/plain;base64," . base64_encode(stream_get_contents($fp));
			$codecol = ob_get_clean(); //Limpa a saída antes que seja enviada para o browser
			$arrRepConf[$r["idrep"]]["_filtros"][$nomeColCan]["code"] = $codecol; //Recupera os valores gerados pelo include
		}
	}
	if ($_SESSION["SESSAO"]["USUARIO"] == "marcelo") {
		//	print_r($arrRepConf);die;
	}
	return $arrRepConf;
}

/*
 * Retorna datas vindas do database para formatacao padronizada conforme o parametro para datas web
 */
function validadatadbweb($indata)
{

	//Substitui o caractere padrao de banco para /
	$indata = str_replace("-", "/", $indata);
	$indata = str_replace("//", "/", $indata);

	//Tenta separar a data da hora
	$arrdatetime = explode(" ", $indata);

	//print_r($arrdatetime); die;

	$data = $arrdatetime[0];
	$hora = $arrdatetime[1]; //a hora sera testada. se nao estiver vazia, ira concatenar hora tambem

	if (!isset($_SESSION["parformatodataweb"])) {
		$_SESSION["parformatodataweb"] = strtolower(retpar("formatodataweb"));
	}

	if (!isset($_SESSION["parformatodatadb"])) {
		$_SESSION["parformatodatadb"] = strtolower(retpar("formatodatadb"));
	}

	$parformatodataweb = $_SESSION["parformatodataweb"];
	$parformatodatadb  = $_SESSION["parformatodatadb"];

	$idata = count($datapartes = explode('/', $data));

	//Separa em variaveis conforme o que retornar da configuracao de [formatodatadb]
	$dia = $datapartes[strpos($parformatodatadb, 'd')];
	$mes = $datapartes[strpos($parformatodatadb, 'm')];
	$ano = $datapartes[strpos($parformatodatadb, 'a')];

	if ((!is_numeric($dia))
		or (!is_numeric($mes))
		or (!is_numeric($ano))
	) {
		return false;
	}

	if (($idata != 3) or
		(strlen($ano) <> 4)
	) {
		return false;
	};

	if (!checkdate($mes, $dia, $ano)) {
		return false;
	}

	//Re-concatena os valores de DATA encontrados acima conforme a posicao dos caracteres [dma] ou [amd] retornados pela configuracao [formatodataweb]
	$dataformatada = "";
	$chrsep = "";
	for ($i = 0; $i <= 2; $i++) {

		$substritem = substr($parformatodataweb, $i, 1);
		switch ($substritem) {
			case "d":
				$dataformatada .= $chrsep . $dia;
				break;
			case "m":
				$dataformatada .= $chrsep . $mes;
				break;
			case "a":
				$dataformatada .= $chrsep . $ano;
				break;
			default:
				null;
				break;
		}

		$chrsep = "/";
	}

	//Valida e concatena a HORA apos a DATA
	if (!empty($hora)) {


		$parformatohoraweb = strtolower("hns");

		$ihora = count($horapartes = explode(':', $hora));

		$hor = $horapartes[0];
		$min = $horapartes[1];
		$seg = $horapartes[2];

		if ((!is_numeric($hor))
			or (!is_numeric($min))
			or (!is_numeric($seg))
		) {
			return false;
		}

		if (($ihora != 3) and //Aceita HH:MM:SS e HH:MM e HH
			($ihora != 2) and
			($ihora != 1)
		) {
			return false;
		};

		if (($hor < 0) or ($hor > 23) or
			($min < 0) or ($min > 59) or
			($seg < 0) or ($seg > 59)
		) {
			return false;
		}
	}

	$dataformatada .= " " . substr("00" . $hor, -2) . ":" .
		substr("00" . $min, -2) . ":" .
		substr("00" . $seg, -2);

	return $dataformatada;
}

/*
 * Recuperar arquivos anexos do chat
 */
function getImArquivos($inIdimmsgbody, $inFormato = "HTML")
{
	if (empty($inIdimmsgbody)) return false;

	$sa = "select * 
			from " . _DBAPP . ".imarq a
			where 1 -- a.idempresa = " . $_SESSION["SESSAO"]["IDEMPRESA"] . "
				and a.idimmsgbody=" . d::b()->real_escape_string($inIdimmsgbody) . "
				and a.tipo in ('H','L')";

	$ra = d::b()->query($sa) or die("Erro ao recuperar arquivos: " . mysql_error(d::b()));

	$aarq = array();
	while ($r = mysql_fetch_assoc($ra)) {
		$aarq[$r["idimarq"]]["arq"] = $r["arq"];
		$aarq[$r["idimarq"]]["nome"] = $r["nome"];
	}
	return $aarq;
}


/*
     * Centralizar a consulta de Módulo
     * Evitar falhas em relação à  Módulos Vinculados
     * Complementar com as colunas necessárias diretamente na consulta
     */
function RetornaChaveModuloEvento($inModulo, $inbypass = false)
{
	if (empty($inModulo)) die("retArrModuloConf(2): Parâmetro inModulo não informado");

	//Permite reaproveitamento sem verificação de segurança. Ex: Tela de _modulo necessita recuperar informaçàµes do módulo mesmo que não estejam devidamente atribuà­das em alguma LP
	if ($inbypass !== true) {

		$joinLp = ($_SESSION["SESSAO"]["LOGADO"]) ? "left join " . _DBCARBON . "._lpmodulo l on (l.modulo=m.modulo and l.idlp='" . $_SESSION["SESSAO"]["IDLP"] . "')" : "";
		$whereMod = ($_SESSION["SESSAO"]["LOGADO"]) ? "and m.modulo in (" . getModsUsr("SQLWHEREMOD") . ")" : "";
		$ifrestaurar = (getModsUsr("SQLWHEREMOD")) ? ",IF(1=(select ('restaurar' in  (" . getModsUsr("SQLWHEREMOD") . "))),'Y','N') as oprestaurar" : "";
	}

	/* $smod = "SELECT
                      CASE WHEN m.tipo='MODVINC' THEN mv.chavefts ELSE m.chavefts END as chavefts
                      FROM "._DBCARBON."._modulo m 
                 left join "._DBCARBON."._modulo mv on (mv.modulo=m.modvinculado)
                 left join "._DBCARBON."._modulo mpar on (mpar.modulo=m.modulopar)
                 ".$joinLp."
                     WHERE m.modulo = '".$inModulo."'
                     ".$whereMod;*/

	$smod = "SELECT col as chavefts from " . _DBCARBON . "._modulo m
                   JOIN " . _DBCARBON . "._mtotabcol mt on mt.tab = m.tab and primkey = 'Y'
                  WHERE m.modulo = '" . $inModulo . "'";
	$rmod = d::b()->query($smod);
	if (!$rmod) die("retArrModuloConf: Erro ao recuperar Módulo " .  mysqli_error(d::b()));
	$rows = mysqli_fetch_assoc($rmod);
	return ($rows['chavefts']);
}
function idempresa()
{
	return $_SESSION["SESSAO"]["IDEMPRESA"];
}


function getidempresa($incampo, $inmodulo, $ignora = false)
{
	//Parametro ignora passado pois em alguns casos, é necessário trazer dados da matriz
	//   print_r($_SESSION);die;
	$alias = explode('.', $incampo);

	if (!empty($alias[1])) {
		$alias = $alias[0] . '.';
	} else {
		$alias = '';
	}

	//LTM 09092020 - Valida a sessão, pois quando o módulo possui mais de uma empresa e precisa de setar a unidade, quando passava no while setava o campo somente com o idempresa.
	if (isset($_GET['_idempresa'])) {
		$session = "and o.idempresa = " . $_GET['_idempresa'];
	} elseif (!empty($_SESSION["SESSAO"]["IDEMPRESA"])) {
		$session = "and o.idempresa = " . $_SESSION["SESSAO"]["IDEMPRESA"];
	} else {
		$session = "";
	}

	if ($_SERVER['SERVER_NAME'] == 'resultados.inata.com.br') {
		$session = "and o.idempresa = 2";
	} else if ($_SERVER['SERVER_NAME'] == RESULTADOSURL) {
		$session = "and o.idempresa = 1";
	}

	$sql = "select 
                ifnull(t.id_passidempresa,0) as id_passidempresa ,ifnull(u.idempresa,0) as idempresa,m.tab, o.idunidade
            from " . _DBCARBON . "._modulo m 
                left join " . _DBAPP . "._passidempresa  t on(m.tab = t.tabela)
                left join " . _DBAPP . ".unidadeobjeto o on (tipoobjeto='modulo' and o.idobjeto = m.modulo) " . $session . "
                left join " . _DBAPP . ".unidade u on(u.idunidade=o.idunidade and u.status='ATIVO')
            where m.modulo='" . $inmodulo . "'";
	$res = d::b()->query($sql) or die("erro functions: getidempresa: " . mysqli_error(d::b()) . "\n" . $sql);
	$qtdr = mysqli_num_rows($res);
	$_idun = '';
	$_virg = '';
	while ($row = mysqli_fetch_assoc($res)) {

		$str = '';


		if ($row['idempresa'] == $_SESSION["SESSAO"]["IDEMPRESA"]) {
			if ($_SESSION["SESSAO"]["HABILITARMATRIZ"] == "Y") { // @487013 - MULTI EMPRESA
				if (isset($_GET['_idempresa']) && $ignora == false) {
					$str .= ' and ' . $incampo . ' =' . $_GET['_idempresa'] . ' ';
					if ($row['idunidade'] > 0) {
						$_idun .= $_virg . $row['idunidade'];
						$_virg = ',';
					}
				} else {
					if ($row['idempresa'] > 0) {
						$str .= ' and ' . $incampo . ' in (' . $row['idempresa'] . ',' . $_SESSION['SESSAO']['MATRIZPERMISSOES'] . ') ';
					} else if ($row['id_passidempresa'] == 0) {
						$str .= ' and ' . $incampo . ' in (' . $_SESSION["SESSAO"]["IDEMPRESA"] . ',' . $_SESSION['SESSAO']['MATRIZPERMISSOES'] . ') ';
					}
				}
			} else {
				if (isset($_GET['_idempresa']) && $ignora == false) {
					$str .= ' and ' . $incampo . ' = ' . $_GET['_idempresa'] . ' ';
				} else {
					if ($row['idempresa'] > 0) {
						$str .= ' and ' . $incampo . '=' . $row['idempresa'] . ' ';
					} else if ($row['id_passidempresa'] == 0) {
						$str .= ' and ' . $incampo . '=' . $_SESSION["SESSAO"]["IDEMPRESA"] . ' ';
					}

					if ($row['idunidade'] > 0) {
						$_idun .= $_virg . $row['idunidade'];
						$_virg = ',';
					}
				} //retirar depois

				if ($row['idunidade'] > 0) {
					$_idun .= $_virg . $row['idunidade'];
					$_virg = ',';
				}
			}
		} else {
			if ($row['id_passidempresa'] > 0) {
				return;
			} else {
				if (isset($_GET['_idempresa']) && $ignora == false) {
					$str .= ' and ' . $incampo . ' = ' . $_GET['_idempresa'] . ' ';
					if ($row['idunidade'] > 0) {
						$_idun .= $_virg . $row['idunidade'];
						$_virg = ',';
					}
				} else {
					if ($_SESSION["SESSAO"]["HABILITARMATRIZ"] == "Y") { //@487013 - MULTI EMPRESA
						$str .= ' and ' . $incampo . ' in (' . $_SESSION["SESSAO"]["IDEMPRESA"] . ',' . $_SESSION['SESSAO']['MATRIZPERMISSOES'] . ') ';
					} else {
						$str .= ' and ' . $incampo . '=' . $_SESSION["SESSAO"]["IDEMPRESA"] . ' ';
					}
				}
			}
		}
	}

	if (!empty($_idun)) {
		$str .= ' and ' . $alias . 'idunidade  in (' . $_idun . ') ';
	}



	//echo "<!-- ";print_r($_SESSION);echo "  -->";die;
	//die($str);

	return $str;

	/*
    if($row['id_passidempresa']==0 and $row['idempresa']==0 and !empty($row['tab'])){    
      	return ' and '.$incampo.'='.$_SESSION["SESSAO"]["IDEMPRESA"].' ';
    }elseif($row['id_passidempresa']>0){
        return ;
    }elseif($row['idempresa']>0){
         return ' and '.$incampo.'='.$row['idempresa'].' ';
    }else{
        return ;
    }
	
    */
}

function getModuloTab($_modulo)
{
	$sql = "SELECT tabde 
              FROM " . _DBCARBON . "._modulorelac
             WHERE modulo = '" . $_modulo . "'";
	$res = d::b()->query($sql) or die("Erro function getModuloTab: " . mysqli_error(d::b()) . "\n" . $sql);
	$row = mysqli_fetch_assoc($res);
	return $row['tabde'];
}

/*
 * Executa um comando SQL e retorna o array resultante
 */
function sql2array($insql, $ignoraColunasVazias = true, $arrColunasIgnorar = array(), $multiplasLinhas = false)
{
	$res = d::b()->query($insql) or die("sql2array: Erro: " . mysqli_error(d::b()) . "\n" . $insql);

	$arrColunas = mysqli_fetch_fields($res);
	$i = 0;
	$arrret = array();
	while ($r = mysqli_fetch_assoc($res)) {
		$i++;
		//para cada coluna resultante do select cria-se um item no array
		foreach ($arrColunas as $col) {
			if ($ignoraColunasVazias == false or ($ignoraColunasVazias == true && strlen($r[$col->name]))) {
				if (!in_array($col->name, $arrColunasIgnorar)) { //Verifica se a coluna será ignorada
					if ($multiplasLinhas === true) {
						$arrret[$i][$col->name] = $r[$col->name];
					} else {
						$arrret[$col->name] = $r[$col->name];
					}
				}
			}
		}
	}
	return $arrret;
}

//Passados vários parâmetros (variáveis, strings, etc), retorna o primeiro valor não-nulo e não-vazio da lista de parâmetros
function coalesce()
{
	$args = func_get_args();
	foreach ($args as $arg) {
		if (!empty($arg)) {
			return $arg;
		}
	}
	return NULL;
}


function getRelatorios($inmodulo)
{
	if (empty($inmodulo)) return false;

	$sa = "SELECT 
				r.idrep, 
				r.rep, 
				r.url 
			FROM
				" . _DBCARBON . "._modulorep m
			JOIN 
				" . _DBCARBON . "._rep r on (r.idrep = m.idrep)
			WHERE 
				modulo = '" . $inmodulo . "'
			ORDER BY	
				r.rep;";


	$ra = d::b()->query($sa) or die("Erro ao recuperar relatórios: " . mysql_error(d::b()));

	$aarq = array();
	while ($r = mysql_fetch_assoc($ra)) {
		$aarq[$r["idrep"]]["rep"] = $r["rep"];
		$aarq[$r["idrep"]]["url"] = $r["url"];
	}
	return $aarq;
}


function get_current_month_range($out = null, $day = "", $month = "", $year = "")
{
	$tday = ($day == "") ? "01" : $day;
	$tmonth = ($month == "") ? date("m") : $month;
	$tyear = ($year == "") ? date("Y") : $year;



	if ($out) {
		$month_sd = date("d/m/y", strtotime($tmonth . '/' . $tday . '/' . $tyear . ' 00:00:00'));
		$month_ed = date("d/m/y", strtotime('-1 second', strtotime('+1 month', strtotime($tmonth . '/' . $tday . '/' . $tyear . ' 00:00:00'))));
		return "$month_sd $month_ed";
	} else {
		$month_sd = date("d/m/Y", strtotime($tmonth . '/' . $tday . '/' . $tyear . ' 00:00:00'));
		$month_ed = date("d/m/Y", strtotime('-1 second', strtotime('+1 month', strtotime($tmonth . '/' . $tday . '/' . $tyear . ' 00:00:00'))));
		return "$month_sd-$month_ed";
	}
}

//retorna data no formato 08:30
function convertHoras($horasDecimais)
{

	// Define o formato de saída
	$formato = '%02d:%02d';

	// Converte para minutos
	$minutos = $horasDecimais * 60;

	// Arredonda para o número inteiro mais próximo
	$minutosArredondados = round($minutos);

	// Converte para o formato hora
	$horas = floor($minutosArredondados / 60);
	$minutos = ($minutosArredondados % 60);

	// Retorna o valor
	return sprintf($formato, $horas, $minutos);
}

//retorna data no formato decimal
function converterHorasParaDecimal($hora)
{
	$partes = explode(':', $hora);
	$horas = $partes[0];
	$minutos = $partes[1];
	$segundos = $partes[2] ?? 0;

	$decimal = $horas + ($minutos / 60) + ($segundos / 3600);

	return $decimal;
}

// funcao que retorna a solicitacoes de fabricacao do cliente que possuem as sementes da formula
// hermesp 27-03-2019
function listaSolfabCliente($idprodservformula, $inidpessoa, $idprodserv = NULL)
{
	if (empty($idprodservformula) or empty($inidpessoa)) {
		die("function.php - listaSolfabCliente faltam parametros básicos para consulta.");
	}

	$sql = "SELECT distinct(sem.idprodserv) as idprodserv
        from prodservformula lf 
        join prodservformulains f on(f.idprodservformula=lf.idprodservformula)
        join prodserv p on(p.idprodserv =f.idprodserv and p.especial ='Y' and p.status='ATIVO')
        join prodservformula ps on(lf.idplantel = ps.idplantel and p.idprodserv = ps.idprodserv and ps.status='ATIVO')
        join prodservformulains psi on(ps.idprodservformula=psi.idprodservformula)
        join prodserv sem on(sem.idprodserv = psi.idprodserv and sem.descr like 'semente%' and sem.status='ATIVO' and sem.especial ='Y' and sem.idprodserv not in(2567,2568,2659,2574,3882,3881))
        where lf.idprodservformula=" . $idprodservformula . "
		and psi.status='ATIVO'
        and lf.status='ATIVO'
		UNION
		SELECT distinct(sem1.idprodserv) as idprodserv
        from prodservformula lf 
        join prodservformulains f on(f.idprodservformula=lf.idprodservformula)
        join prodserv p on(p.idprodserv =f.idprodserv and p.especial ='Y' and p.status='ATIVO')
        join prodservformula ps on(lf.idplantel = ps.idplantel and p.idprodserv = ps.idprodserv and ps.status='ATIVO')
        join prodservformulains psi on(ps.idprodservformula=psi.idprodservformula)
        join prodserv sem on(sem.idprodserv = psi.idprodserv and sem.descr not like 'semente%' and sem.status='ATIVO' and sem.especial ='Y' and sem.idprodserv not in(2567,2568,2659,2574,3882,3881))
        join prodservformula psf on (sem.idprodserv = psf.idprodserv and psf.status = 'ATIVO')
        join prodservformulains psfi on (psf.idprodservformula = psfi.idprodservformula)
        join prodserv sem1 on(sem1.idprodserv = psfi.idprodserv and sem1.descr like 'semente%' and sem1.status='ATIVO' and sem1.especial ='Y' and sem1.idprodserv not in(2567,2568,2659,2574,3882,3881)) 
        where lf.idprodservformula=" . $idprodservformula . "
		and psi.status='ATIVO'
        and lf.status='ATIVO'
		";
	$arrins = array();
	$res = d::b()->query($sql) or die("Erro Sql ao buscar insumos da formula. - " . $sql);

	while ($row = mysqli_fetch_assoc($res)) {
		$arrins[$row['idprodserv']] = $row['idprodserv']; //array com os insumos    
		$idprodservArray[] = $row['idprodserv'];
	}
	$idprodservArray = implode(", ", $idprodservArray);

	// print_r($arrins);
	//  echo('<br>');

	if (!empty($idprodserv)) {
		$WherePedido = " AND s.idsolfab IN (SELECT s2.idsolfab FROM solfab s2 JOIN lote l2 ON s2.idlote = l2.idlote WHERE l2.idprodserv = $idprodserv)";
	}

	$sql1 = "select s.idsolfab,l.idprodserv
            from  solfab s
            join solfabitem si  on(s.idsolfab = si.idsolfab)
            join lote l on(l.idlote=si.idobjeto and si.tipoobjeto = 'lote')
            where s.idpessoa=" . $inidpessoa . " 
            and s.status not in ('REPROVADO','CANCELADO')
			$WherePedido
            order by idprodserv";
	$arrsem = array();
	$res1 = d::b()->query($sql1) or die("Erro sql ao buscar as sementes presentes em solicitacao de fabricacao - " . $sql1);

	while ($row1 = mysqli_fetch_assoc($res1)) {
		$arrsem[$row1['idsolfab']][$row1['idprodserv']] = $row1['idprodserv']; //array com as sementes da solicitacao de fabricacao do cliente   
	}

	//print_r($arrsem);
	//echo('depois');
	foreach ($arrsem as $idsolfab => $arrsolfabitem) {

		reset($arrins);
		foreach ($arrins as $semente) {
			if (!in_array($arrins[$semente], $arrsolfabitem)) { // se semente nao estiver nos itens da solicitacao de fabricacao
				unset($arrsem[$idsolfab]); //retira a solicitacao de fabricacao do array
			}
		}
	}

	$arrRetAspas = array();
	reset($arrsem);
	// print_r($arrsem);
	foreach ($arrsem as $idsolfab => $arrsfi) { // roda no array de solicitacao de fabricacao que sobraram no array
		$arrRetAspas[] = "'" . $idsolfab . "'";
		//  echo($idsolfab);
	}

	return implode(",", $arrRetAspas); // retorna as solicitacoes

} //function listaSolfabCliente(){

// Cria um identificador aleatório para o envio de um email.
function geraIdEnvioEmail()
{
	$size = 5;
	//String com valor possíveis do resultado, os caracteres pode ser adicionado ou retirados conforme sua necessidade
	$basic = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

	$return = "";

	for ($count = 0; $size > $count; $count++) {
		//Gera um caracter aleatorio
		$return .= $basic[rand(0, strlen($basic) - 1)];
	}

	return $return;
}

//maf: gerar identificar único seguro. Ex: nomear dispositivos conectados (autenticados) ao sislaudo
function uuid($lenght = 16)
{
	if (function_exists("random_bytes")) {
		$bytes = random_bytes(ceil($lenght / 2));
	} elseif (function_exists("openssl_random_pseudo_bytes")) {
		$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
	} else {
		throw new Exception("Nenhuma funcao criptografica segura foi encontrada");
	}
	return substr(bin2hex($bytes), 0, $lenght);
}

//maf020120: PHP7+: Função para gerar uma string com caracteres aleatórios (random string) com tamanho controlado. Ex: rstr(5)
function rstr($length)
{
	$token = "";
	$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
	$codeAlphabet .= "0123456789";
	$max = strlen($codeAlphabet);

	for ($i = 0; $i < $length; $i++) {
		$token .= $codeAlphabet[random_int(0, $max - 1)];
	}

	return $token;
}

function validaStatus($modulo)
{
	$sql = "SELECT idfluxo
			  FROM fluxo
			 WHERE modulo = '" . $modulo . "' AND status = 'ATIVO'";
	$res = d::b()->query($sql) or die("[Laudo:] Erro ao buscar se existe modulo cadastrado no status : " . mysql_error() . "<p>SQL: " . $sql);
	return mysql_num_rows($res);
}

function getStatusFluxo($tabela, $_primary, $idobjeto)
{
	$statusFluxo = "SELECT s.rotulo, t.status, s.statustipo FROM $tabela t JOIN fluxostatus fs ON t.idfluxostatus = fs.idfluxostatus
					JOIN " . _DBCARBON . "._status s ON fs.idstatus = s.idstatus
					WHERE t.$_primary = '" . $idobjeto . "'";
	$rescFluxo = d::b()->query($statusFluxo) or die($statusFluxo . " Erro ao lista status" . mysql_error());
	$rowcFluxo = mysqli_fetch_assoc($rescFluxo);

	return $rowcFluxo;
}

/*
 * Controle de requisicoes, para evitar que varias abas forcem o processamento via ajax indesejado da mesma api, causando sobreprocessamento
 */
function requestControl($jwt)
{
	$_headers = getallheaders();

	//MAF: Utilizar somente se o header do fetch-controller for enviado
	if (empty($_headers["hdrctrlreq"])) {
		return false;
	}

	$nameKey = 'reqcontroller';              // Nome da 'pasta' do Redis que irá conter os identificadores das requisições controladas
	$idPessoa = $jwt["token"]->idpessoa;    // Identificador do usuário
	$idClient = $jwt["token"]->jwt_uniq;    // Identificador do dispositivo/navegador que o usuário está utilizando: varias abas do msm browser enviam o msm identificador
	$reqName = $_headers["hdrctrlreq"];     // Nome da requisição que está sendo realizada
	$idTab = $_headers["hdrctrlid"];          // Identificador da aba que realizou a requisição (NÃO ESTÁ SENDO UTILIZADO NO MOMENTO)

	$timerCtrl = (int)$_headers["hdrctrltimer"] - 1; // Tempo de expiração da chave do Redis determinado pelo client

	if (empty($nameKey) or empty($idPessoa) or empty($idClient) or empty($reqName) or empty($idTab) or empty($timerCtrl)) {
		header("hdrctrlresp: 297"); // Requisição interrompida por variaveis vazias
		die;
	} else {

		$sucess = re::dis()->set($nameKey . ':' . $idPessoa . ':' . $idClient . ':' . $reqName, $idTab, ['ex' => $timerCtrl, 'nx']); // add request name ex: bim, chat

		if (!$sucess) {
			header("hdrctrlresp: 299"); // Requisição Nsegada
			die;
		} else {
			header("hdrctrlresp: 298"); // Requisição Aceita
		}
	}
}

/*
 * Seta header http com o token para devolver ao cliente
 */
function setHdrToken(
	$idempresa,
	$idpessoa,
	$idtipopessoa,
	$usuario,
	$nome,
	$ramalfixo = "",
	$idarquivoavatar = "",
	$imagemFuncionario = "",
	$permissaochat = "N"
) {

	$token = array(
		"iss" => "sislaudo",
		"exp" => time() + (7 * 24 * 60 * 60),
		"ramalfixo" => $ramalfixo,
		"idtipopessoa" => $idtipopessoa,
		"idpessoa" => $idpessoa,
		"usuario" => $usuario,
		"nome" => $nome,
		"jwt_uniq" => rstr(10),
		"idempresa" => $idempresa,
		"idarquivoavatar" => $idarquivoavatar,
		"linkavatar" => $imagemFuncionario,
		"permissaochat" => $permissaochat,
		"endpoint_modulos_disponiveis" => "/api/mod/",
		"endpoint_preferencias" => "/api/pref/",
	);

	$jwt = JWT::encode($token, _JWTKEY, 'HS256');

	//Armazena o jwt para o usuario
	cb::$usr["JWT"] = $jwt;

	//Isto evita chamadas em qualquer script que ja tenha devolvido headers
	header("jwt: " . $jwt);
}

function getIdlpMatriz($idpessoa, $idempresa)
{
	$sqlLP = "SELECT idlpobjeto
				FROM lpobjeto lo
				JOIN " . _DBCARBON . "._lp l on l.idlp = lo.idlp
				WHERE idobjeto = '$idpessoa' AND tipoobjeto = 'pessoa' AND l.idempresa = " . $idempresa . "
			UNION 
				SELECT lo.idlpobjeto
				FROM lpobjeto lo JOIN sgsetor s ON s.idsgsetor = lo.idobjeto AND tipoobjeto = 'sgsetor'
				JOIN pessoaobjeto po ON po.idobjeto = s.idsgsetor AND po.tipoobjeto = 'sgsetor' AND po.idpessoa = '$idpessoa'
				JOIN " . _DBCARBON . "._lp l on l.idlp = lo.idlp
				WHERE l.idempresa = " . $idempresa . " AND s.status = 'ATIVO'
			UNION 
				SELECT lo.idlpobjeto
				FROM lpobjeto lo JOIN sgarea s ON s.idsgarea = lo.idobjeto AND tipoobjeto = 'sgarea'
				JOIN pessoaobjeto po ON po.idobjeto = s.idsgarea AND po.tipoobjeto = 'sgarea' AND po.idpessoa = '$idpessoa'
				JOIN " . _DBCARBON . "._lp l on l.idlp = lo.idlp
				WHERE l.idempresa = " . $idempresa . " AND s.status = 'ATIVO'
			UNION 
				SELECT lo.idlpobjeto
				FROM lpobjeto lo JOIN sgdepartamento s ON s.idsgdepartamento = lo.idobjeto AND tipoobjeto = 'sgdepartamento'
				JOIN pessoaobjeto po ON po.idobjeto = s.idsgdepartamento AND po.tipoobjeto = 'sgdepartamento' AND po.idpessoa = '$idpessoa'
				JOIN " . _DBCARBON . "._lp l on l.idlp = lo.idlp
				WHERE l.idempresa = " . $idempresa . " AND s.status = 'ATIVO'
			UNION  
				SELECT l.idlp AS idlpobjeto
					FROM pessoa p JOIN " . _DBCARBON . "._lp l ON l.idtipopessoa = p.idtipopessoa AND p.status='ATIVO'
				WHERE p.idpessoa = '$idpessoa';";

	$resLP = d::b()->query($sqlLP) or die("A Consulta de empresa e pessoa falhou : " . mysql_error() . "<p>SQL: $sqlLP");
	$rowLP = mysqli_fetch_assoc($resLP);
	return $rowLP;
}

function getImagemRelatorio($tabela, $_primary, $idobjeto)
{
	$sql = "SELECT e.idempresa 
			  FROM $tabela t JOIN empresa e ON e.idempresa = t.idempresa
			 WHERE t.$_primary = '" . $idobjeto . "'";
	$res = d::b()->query($sql) or die($sql . " Erro ao lista status" . mysql_error());
	$row = mysqli_fetch_assoc($res);

	if ($row['idempresa'] && $_SESSION["SESSAO"]["HABILITARMATRIZ"] == "Y") {
		$idempresa = " AND idempresa = " . $row['idempresa'];
	} else {
		$idempresa = getidempresa('idempresa', 'empresa');
	}
	return $idempresa;
}

function getPessoaModulosDisponiveis()
{

	$qr = "SELECT 
			m.modulo
		FROM
			" . _DBCARBON . "._modulo m
		WHERE
			tipo IN ('BTPR') AND status = 'ATIVO' 
		UNION ALL SELECT 
			lm.modulo
		FROM
			" . _DBCARBON . "._lpmodulo lm
		WHERE
			EXISTS( SELECT 
					1
				FROM
					" . _DBCARBON . "._lp l
				WHERE
					l.idlp = lm.idlp
						AND EXISTS( SELECT 
							1
						FROM
							(SELECT 
								l.idlp
							FROM
								pessoaobjeto ps, sgconselho s, lpobjeto o, " . _DBCARBON . "._lp l
							WHERE
								ps.idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
									AND s.idsgconselho = ps.idobjeto
									AND ps.tipoobjeto = 'sgconselho'
									AND s.status = 'ATIVO'
									AND o.idobjeto = s.idsgconselho
									AND o.tipoobjeto = 'sgconselho'
									AND l.status = 'ATIVO'
							AND l.idlp = o.idlp UNION SELECT 
								l.idlp
							FROM
								pessoaobjeto ps, sgarea s, lpobjeto o, " . _DBCARBON . "._lp l
							WHERE
								ps.idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
									AND s.idsgarea = ps.idobjeto
									AND ps.tipoobjeto = 'sgarea'
									AND s.status = 'ATIVO'
									AND o.idobjeto = s.idsgarea
									AND o.tipoobjeto = 'sgarea'
									AND l.status = 'ATIVO'
									AND l.idlp = o.idlp UNION SELECT 
								l.idlp
							FROM
								pessoaobjeto ps, sgdepartamento s, lpobjeto o, " . _DBCARBON . "._lp l
							WHERE
								ps.idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
									AND s.idsgdepartamento = ps.idobjeto
									AND ps.tipoobjeto = 'sgdepartamento'
									AND s.status = 'ATIVO'
									AND o.idobjeto = s.idsgdepartamento
									AND o.tipoobjeto = 'sgdepartamento'
									AND l.status = 'ATIVO'
									AND l.idlp = o.idlp UNION SELECT 
								l.idlp
							FROM
								pessoaobjeto ps, sgsetor s, lpobjeto o, " . _DBCARBON . "._lp l
							WHERE
								ps.idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
									AND s.idsgsetor = ps.idobjeto
									AND ps.tipoobjeto = 'sgsetor'
									AND s.status = 'ATIVO'
									AND o.idobjeto = s.idsgsetor
									AND o.tipoobjeto = 'sgsetor'
									AND l.status = 'ATIVO'
									AND l.idlp = o.idlp UNION SELECT 
								l.idlp
							FROM
								lpobjeto ps, " . _DBCARBON . "._lp l
							WHERE
								l.idlp = ps.idlp AND l.status = 'ATIVO'
									AND ps.tipoobjeto = 'pessoa'
									AND ps.idobjeto = " . $_SESSION["SESSAO"]["IDPESSOA"] . " UNION SELECT 
								l.idlp
							FROM
								pessoa p, " . _DBCARBON . "._lp l
							WHERE
								l.idtipopessoa = p.idtipopessoa
									AND l.status = 'ATIVO'
									AND p.idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"] . ") AS u
						WHERE
							u.idlp = l.idlp))
		GROUP BY lm.modulo;";
	//die($qr);
	$rs = d::b()->query($qr) or die("[getPessoaModulosDisponiveis] Erro ao consultar permissão no módulo [" . $_GET['_modulo'] . "]");
	$modulos = "";
	$vrg = "";
	while ($rw = mysqli_fetch_assoc($rs)) {
		$modulos .= $vrg . "'" . $rw["modulo"] . "'";
		$vrg = ",";
	}

	if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 3) {
		$sqlda = "select 
			(SELECT count(*)
			FROM pessoacontato c
			WHERE c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
				AND exists(select 1 from amostra a where a.idpessoa = c.idpessoa AND a.idunidade = 1) limit 1) as diagnostico
			, 
			(SELECT count(*)
			FROM pessoacontato c
			WHERE c.idcontato = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
				AND exists(select 1 from amostra a where a.idpessoa = c.idpessoa AND a.idunidade in (6,9)) limit 1) as autogena";

		//die($sqlda);
		$resad = mysql_query($sqlda) or die("Erro ao consultar resultados dos cliente do usuario: " . mysql_error());
		$rad = mysql_fetch_assoc($resad);
		if ($rad['diagnostico'] > 0 and $rad['autogena'] > 0) {
			$modulos .= $vrg . "'cliente_filtrarresultados','cliente_filtrarresultadostra'";
		} else if ($rad['diagnostico'] > 0) {
			$modulos .= $vrg . "'cliente_filtrarresultados'";
		} else if ($rad['autogena'] > 0) {
			$modulos .= $vrg . "'cliente_filtrarresultadostra'";
		}
	}

	return $modulos;
}

/** ************>>>> ESTA CLASSE NAO DEVE SER ALTERADA SEM AUTORIZACAO

	Esta classe e usada para mostrar prompts html em qualquer area do software, utilizando uma combinacao de variaveis. $_SERVER
	Como é um recurso que envolve seguranca, ele foi dividido em 2 fases de validacoes:

	1 - verificaExecucaoPrompt
		Os parametros de execucao primarios estao na tabela carbon._prompt. Nesta tabela serao definidos os parametros para execucao de um primeiro select verificador
		se existe alguma regra para prompt ATIVA, com possibilidade de execucao. Caso nao haja nenhum prompt ativo, a programacao segue normalmente, sem impacto de performance

	2 - verificaExecucaoPromptPorVariaveis
		Os parametros para esta segunda execucao estao na tabela laudo.promptvar
		Apos verificacao que naquele ponto/combinacao existe uma regra ATIVA, é feita uma segunda consulta para saber se, por exemplo, aquele determinado usuario esta
		configurado para ver o prompt
		Caso positivo, os dados do prompt estarao em um JSON na tabela carbon._prompt, com alguns recursos de data limite (die), o conteudo para apresentacao (html) e futuramente a devolucao de headers ao client que fez a requisicao

	Exemplo de execucao minima: prompt::get('forca_troca_senha')::executa();

 */
abstract class prompt
{
	//Controla o encadeamento da execucao dos metodos da classe. caso seja falso, o metodo simplesmente ira retornar a classe para permitir a execucao do chaining sem erros
	private static $executa = false;
	//Dados do prompt
	private static $idprompt = null;
	private static $nome = "";
	public static $ptipo = "";
	public static $pkey = "";
	public static $pvalue = "";
	public static $pvalueexcecao = "";
	public static $pjacao = "";
	public static $pdatalimite = null;

	//Dados de validacao de variaveis globais
	public static $tipo = "session";
	//public static $key="USUARIO";

	//Metodo nativo para recuperar os valores de propriedades por reflection
	public static function printClass()
	{
		$reflection = new ReflectionClass(__CLASS__);
		$pr = $reflection->getStaticProperties();
		print_r($pr);
	}

	//Ajusta o tipo de prompt a ser verificado. Atencao: este metodo é invocado desta maneira: promptvar::tipo
	//Caso se deseje acessar a PROPRIEDADE $tipo, deve-se utilizar: promptvar::$tipo
	public static function tipo($intipo)
	{
		if (self::$executa === false) return __CLASS__;
		self::$tipo = $intipo;
		return __CLASS__;
	}

	//Ajusta o tipo de variavel global de ambiente a ser utilizado na clausula where
	public static function key($inkey)
	{
		if (self::$executa === false) return __CLASS__;
		self::$key = $inkey;
		return __CLASS__;
	}

	public static function get($inprompt)
	{
		if (empty($inprompt)) {
			die("prompt vazio");
			return false;
		}
		$sql = "SELECT p.idprompt, p.vartipo, p.varkey, p.varvalue, p.jacao, p.varvalueexcecao, p.datalimite
			FROM " . _DBCARBON . "._prompt p
			WHERE p.status='ATIVO'
			AND p.nome='" . $inprompt . "'
            AND p.datalimite > now()";

		$res = d::b()->query($sql);

		if (mysqli_num_rows($res) == 1) {
			self::$nome = $inprompt;
			//Recupera os parametros do DB
			$row = mysqli_fetch_assoc($res);
			self::$idprompt = $row["idprompt"];
			self::$ptipo = $row["vartipo"];
			self::$pkey = $row["varkey"];
			self::$pvalue = $row["varvalue"];
			self::$pvalueexcecao = $row["varvalueexcecao"];
			self::$pdatalimite = $row["datalimite"];
			self::$pjacao = json_decode($row["jacao"], JSON_OBJECT_AS_ARRAY);

			//Marca a classe como executavel
			self::$executa = true;

			return __CLASS__;
		} else {
			self::$executa = false;
		}
		return __CLASS__;
	}

	//Este metodo retorna somente true/false, e nao deve ser exposto publicamente
	private static function verificaExecucaoPrompt()
	{

		if (self::$executa === false) return false;

		//self::printClass();
		if (self::$ptipo == "server") {
			//Aplica um regex para NEGACAO: verificando se existe alguma condicao para NAO mostrar a mensagem do prompt
			//Ex: Caso de mensagem de obrigacao de alteracao de senha, deve ocorrer em todos os formularios, menos no propriamente dito /form/alterasenha.php
			if (!empty(self::$pvalueexcecao) and preg_match("/" . self::$pvalueexcecao . "/", $_SERVER[self::$pkey])) {
				return false;
			} else {

				//Aplica um regex no conteudo da coluna varvalue, para verificar se a execucao sera feita neste ponto
				if (preg_match("/" . self::$pvalue . "/", $_SERVER[self::$pkey])) {
					self::$executa = true;
					return true;
				} else {
					header("x-prompt-pregm2-cnfval: " . self::$pvalue);
					header("x-prompt-pregm2-keyval: " . $_SERVER[self::$pkey]);
					return false;
				}
			}
		} else {
			self::$executa = false;
			return false;
		}
	}

	//Este metodo retorna somente true/false, e nao deve ser exposto publicamente
	private static function verificaExecucaoPromptPorVariaveis()
	{
		//self::printClass();

		if (self::$tipo == "session" or self::$tipo == "server") {
			//Primeiro se verifica uma chave simples de session. ex: idtipopessoa, ou idpessoa.
			//Em seguida executa-se os matches, onde todos devem resultar em TRUE
			$sql = "SELECT pv.varkey, pv.varvalue, pv.matches
				FROM " . _DBAPP . ".promptvar pv
				WHERE pv.idprompt=" . self::$idprompt . "
					AND pv.status='ATIVO'
					AND pv.vartipo='" . self::$tipo . "'";

			$res = d::b()->query($sql);

			//@todo: implementar checagem de multiplas variaveis, sendo cada variavel, cada registro encontrado na promptvar
			if (mysqli_num_rows($res) > 0) {
				$checkr = false;

				while ($r = mysqli_fetch_assoc($res)) {

					$varkey = null;
					switch (self::$tipo) {
						case 'session':
							$varkeyval = $_SESSION["SESSAO"][$r["varkey"]];
							break;
						case 'server':
							$varkeyval = $_SERVER[$r["varkey"]];
							break;
						default:
							$varkeyval = null;
							break;
					}

					if ($varkeyval == $r["varvalue"]) {
						$matches = json_decode($r["matches"]);
						$imatches = 0;
						foreach ($matches as $sk => $sv) {
							$origemmatch = explode("#", $sk)[0];
							$varmatch = explode("#", $sk)[1];
							//@todo: implementar validacao de variaveis para match por server ou outras
							if ($origemmatch == "SESSAO") {
								$imatches += ($_SESSION["SESSAO"][$varmatch] == $sv) ? 1 : 0;
							} elseif ($origemmatch == "ESTADOPESSOA") {
								$imatches += (re::dis()->hGet('_estado:' . $_SESSION["SESSAO"]["IDPESSOA"] . ':pessoa', 'statusponto') == $sv) ? 1 : 0;
							}
						}
						//Se a qtd de true for a mesma quantidade de matches cadastrados, executar o prompt
						if ($imatches == sizeof((array)$matches)) {
							$checkr = true;
							break;
						} else {
							continue;
						}
					} else {
						header("x-prompt-verexecvar-cnfval: " . $r["varvalue"]);
						header("x-prompt-verexecvar-keyval: " . $varkeyval);
						continue;
					}
				}
				return $checkr;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	//Executa com os parametros default por usuario. Caso seja necessario customizar, estender a class com metodos chained, de alteracao de propriedades, e executar executa ao fim
	//Este metodo nao aceita chaining: deve ser o ultimo da cadeia de aninhamento
	public static function executa()
	{

		if (self::$executa === false) return false; //fim

		//Valida se o prompt sera executado para a regra encontrada
		if (self::verificaExecucaoPrompt() === true) {
			//self::printClass();
			//Valida se o prompt sera executado conforme o segundo nivel de regras, geralmente aplicadas a usuarios ou modulos especificos
			if (self::verificaExecucaoPromptPorVariaveis()) {
				if (self::$pjacao["acao"] == "html") {
					header("x-prompt: html");
					header("x-prompt-name: " . self::$nome);
					echo self::$pjacao["html"];
				} elseif (self::$pjacao["acao"] == "require_once") {
					header("x-prompt: import");
					header("x-prompt-name: " . self::$nome);
					$_arquivo = _CARBON_ROOT . self::$pjacao["filename"];
					if (!file_exists($_arquivo)) {
						die("prompt: Arquivo [" . $_arquivo . "] inexistente.\nidprompt: " . self::$idprompt);
					} else {
						//Este ponto deve ser require_once, para evitar de o arquivo ser incluído nele mesmo (recursividade).
						require_once($_arquivo);
					}
				} elseif (self::$pjacao["acao"] == "header") {
					header("x-prompt: header");
					header("x-prompt-name: " . self::$nome);
					header(self::$pjacao["header"]);
					if (self::$pjacao["pos_acao"] == "die") {
						die();
					}
				}

				if (self::$pjacao["pos_acao"] == "die") {
					header("x-prompt-stop: stopped");
					header("x-prompt-name: " . self::$nome);
					die();
				}
			}
		} else {
			return false;
		}
	}

	//	public static function __callStatic($magicmethod, $arguments){
	//		die("\n\n".$magicmethod);
	//		return self::sqlPerm();
	//		return __CLASS__;
	//	}
}


function montaFiltroEmpresaRelatorio($idrep)
{
	if (!in_array(cb::idempresa(), [12, 8, 9, 25])) {
		$sq_empresa = "and e.idempresa=" . cb::idempresa();
	} else {
		$sq_empresa = "";
	}

	$getEmpresa = "SELECT 
				distinct idempresa, nomefantasia
			FROM
				empresa e
			WHERE
				filial ='N'
				and status = 'ATIVO'
				$sq_empresa
				-- CONDICAO PARA FILTRAR EMPRESAS DE ACORDO COM A CONFIGURAÇÃO RELATORIO/MODULO
					AND EXISTS( SELECT 
						1
					FROM
						" . _DBCARBON . "._rep r
							JOIN
						" . _DBCARBON . "._modulorep mr ON mr.idrep = r.idrep
							JOIN
						" . _DBCARBON . "._modulo m ON m.modulo = mr.modulo
							JOIN
						objempresa oe on oe.objeto = 'modulo' and oe.idobjeto = m.idmodulo
					WHERE
						-- entrada
						r.idrep = $idrep
						and oe.empresa = e.idempresa)
					-- CONDIÇÃO PARA FILTRAR EMPRESAS DO USUARIO
					AND EXISTS
					(
						select 1 from objempresa oe where oe.objeto = 'pessoa' 
						-- ENTRADA
						and oe.idobjeto = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
						and oe.empresa = e.idempresa
					)				
			ORDER BY e.empresa";


	$res = d::b()->query($getEmpresa) or die("Erro FUNCTION montaFiltroEmpresaRelatorio: ao consultar sigla das empresas.");
	$virg = "";
	$json = "";

	while ($row = mysql_fetch_assoc($res)) {
		$json .= $virg . '{"' . $row['idempresa'] . '":"' . $row['nomefantasia'] . '"}';
		$virg = ",";
	}

	return "[" . $json . "]";
}


function clausulaEmpresaRelatorio($idrep)
{
	$getEmpresa = "SELECT 
				distinct idempresa
			FROM
				empresa e
			WHERE
				status = 'ATIVO'
				-- CONDICAO PARA FILTRAR EMPRESAS DE ACORDO COM A CONFIGURAÇÃO RELATORIO/MODULO
					AND EXISTS( SELECT 
						1
					FROM
						" . _DBCARBON . "._rep r
							JOIN
						" . _DBCARBON . "._modulorep mr ON mr.idrep = r.idrep
							JOIN
						" . _DBCARBON . "._modulo m ON m.modulo = mr.modulo
							JOIN
						objempresa oe on oe.objeto = 'modulo' and oe.idobjeto = m.idmodulo
					WHERE
						-- entrada
						r.idrep = $idrep
						and oe.empresa = e.idempresa)
					-- CONDIÇÃO PARA FILTRAR EMPRESAS DO USUARIO
					AND EXISTS
					(
						select 1 from objempresa oe where oe.objeto = 'pessoa' 
						-- ENTRADA
						and oe.idobjeto = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
						and oe.empresa = e.idempresa
					)					
			ORDER BY e.empresa";


	$res = d::b()->query($getEmpresa) or die("Erro FUNCTION clausulaEmpresaRelatorio.");
	$virg = "";
	$cl = "in (";

	while ($row = mysql_fetch_assoc($res)) {
		$cl .= $virg . $row['idempresa'];
		$virg = ",";
	}
	$cl .= ")";
	return $cl;
}

function baseToGet($getp)
{
	$pget = base64_decode($getp);
	$arrget = explode("&", $pget);
	$p = "";

	foreach ($arrget as $key => $value) {
		$arrval = explode("=", $value);

		foreach ($arrval as $key => $value) {
			if ($key == 0) {
				$p = $value;
			} else if ($key == 1) {
				$_GET[$p] = $value;
				$_REQUEST[$p] = $value;
			}
		}
	}
}


function  montaFiltroUnidadeRelatorio($idrep)
{
	$SqlLpRep = "select * from " . _DBCARBON . "._lprep lr where lr.idrep = " . $idrep . " and lr.flgunidade = 'Y' and lr.idlp in (" . getModsUsr("LPS") . ")
		and exists(select 1 from " . _DBCARBON . "._lp l where l.idlp=lr.idlp and l.idempresa=" . cb::idempresa() . ")
		";
	$res = d::b()->query($SqlLpRep) or die("Erro FUNCTION montaFiltroUnidadeRelatorio.");
	$nrlp = mysqli_num_rows($res);

	if ($nrlp >= 1) {

		$resUnidade = "SELECT 
				u.idunidade, u.unidade
			FROM
				unidade u
			WHERE
				EXISTS( SELECT 
						1
					FROM
						vw8PessoaUnidade pu
					WHERE
						pu.idunidade = u.idunidade
							AND pu.idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"] . ") order by u.unidade";
	} else {

		if (cb::idempresa() == 8 || cb::idempresa() == 9) {

			$gidempresa = "SELECT 
							distinct idempresa
						FROM
							empresa e
						WHERE
							status = 'ATIVO'
							-- CONDICAO PARA FILTRAR EMPRESAS DE ACORDO COM A CONFIGURAÇÃO RELATORIO/MODULO
								AND EXISTS( SELECT 
									1
								FROM
									" . _DBCARBON . "._rep r
										JOIN
									" . _DBCARBON . "._modulorep mr ON mr.idrep = r.idrep
										JOIN
									" . _DBCARBON . "._modulo m ON m.modulo = mr.modulo
										JOIN
									objempresa oe on oe.objeto = 'modulo' and oe.idobjeto = m.idmodulo
								WHERE
									-- entrada
									r.idrep = $idrep
									and oe.empresa = e.idempresa)
								-- CONDIÇÃO PARA FILTRAR EMPRESAS DO USUARIO
								AND EXISTS
								(
									select 1 from objempresa oe where oe.objeto = 'pessoa' 
									-- ENTRADA
									and oe.idobjeto = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
									and oe.empresa = e.idempresa
								)					
						ORDER BY e.empresa";

			$residempresa = d::b()->query($gidempresa);
			$idempresa = "";
			$vg = "";

			while ($row = mysql_fetch_assoc($residempresa)) {
				$idempresa .= $vg . $row['idempresa'];
				$vg = ",";
			}

			$idempresa = "idempresa in($idempresa)";

			$resUnidade = "SELECT idunidade, unidade FROM unidade WHERE $idempresa  and status = 'ATIVO'  order by unidade";
		} else {

			$idempresa = "idempresa =" . cb::idempresa();
			$resUnidade = "SELECT idunidade, unidade FROM unidade WHERE $idempresa  and status = 'ATIVO'  order by unidade";
		}
	}

	$res = d::b()->query($resUnidade) or die("Erro FUNCTION montaFiltroUnidadeRelatorio: ao consultar recuperar o idunidade.");
	$virg = "";
	$json = "";

	while ($row = mysql_fetch_assoc($res)) {
		$json .= $virg . '{"' . $row['idunidade'] . '":"' . $row['unidade'] . '"}';
		$virg = ",";
	}

	return "[" . $json . "]";
}

//verifica se o valor está em formato brasileiro;
function formatoReal($valor)
{
	$valor = (string)$valor;
	$regra = "/[0-9]{1,3}([.]([0-9]{3}))*[,]([.]{0})[0-9]{0,2}$/";
	if (preg_match($regra, $valor)) {
		return true;
	} else {
		return false;
	}
}

function formatCnpjCpf($value)
{
	$cnpj_cpf = preg_replace("/\D/", '', $value);

	if (strlen($cnpj_cpf) === 11) {
		return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
	}

	return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
}

function aplicaMascara($mascara, $val)
{
	if ($mascara != "" && $mascara != "N") {

		if ($mascara == 'MOEDA') {

			if (formatoReal($val)) {
				$value = "R$ $val";
			} else {
				$value = number_format($val, 2, ',', '.');
				empty($value) ? $value = "Erro ao converter moeda - verificar datatype enviado" : $value = $value;
				$value = "R$ $value";
			}
		} else if ($mascara == 'CPF/CJPJ') {
			$value = formatCnpjCpf($val);
		}
	} else {
		$value = $val;
	}

	return $value;
}


function idempresaFiltros($alias)
{

	/** Se a empresa for diferente da G8, retorna a empresa informada no parametro GET se houver ou a empresa da sessão do usuário */

	if (cb::idempresa() == 8 || cb::idempresa() == 9) {
		$empresa = "";
	} else {
		$empresa = "and $alias.idempresa=" . cb::idempresa();
	}

	return $empresa;
}

function consultaLogsSmtp($intipo, $inidobjeto, $informato = "array")
{
	//Recupera os dados de logs de smtp
	$ssmtp = "select
			m.idobjeto,
			trim(m.destinatario) as destinatario
			,m.status
			,m.criadoem
			,if(m.status='ENVIADO',1,0) as ienviado
			,if(m.status='ENVIADO','',l.message) as message
		from mailfila m left join mailfilalog l on l.queueid=m.queueid and l.destinatario=m.destinatario
		where m.tipoobjeto = '" . $intipo . "' and idobjeto = " . $inidobjeto;

	$ressmtp = d::b()->query($ssmtp) or die("Erro ao pesquisar log do servidor smtp");

	$arrret = [];
	while ($r = mysqli_fetch_assoc($ressmtp)) {
		$arrret[$r["destinatario"]]["log"][$r["criadoem"]]["status"] = $r["status"];
		$arrret[$r["destinatario"]]["log"][$r["criadoem"]]["message"] = $r["message"];
		$arrret[$r["destinatario"]]["ienviado"] += (int)$r["ienviado"];
		$arrret[$r["destinatario"]]["statusenviado"] = $arrret[$r["destinatario"]]["statusenviado"] > 0 ? "Y" : "N";
		//$arrret[$r["destinatario"]]["corstatus"]=$arrret[$r["destinatario"]]["statusenviado"]>0?"verde":"cinzaclaro";
	}

	if ($informato == "array") {
		return $arrret;
	} elseif ($informato == "table") {

		$strret = "<table>";
		foreach ($arrret as $dest => $log) {

			$cori = "cinza";
			$stricos = "";
			foreach ($log["log"] as $datalog => $statussmtp) {
				$cori = $statussmtp["status"] == "ENVIADO" ? "verde" : "cinzaclaro";
				$stricos .= "<i class='fa fa-circle " . $cori . "' title='" . $statussmtp["status"] . ": " . dmahms($datalog) . "\n\n" . htmlentities($statussmtp["message"]) . "'></i>";
			}
			$strret .= "\n<tr>\n <td>" . $stricos . "</td>\n <td>" . $dest . "</td>\n</tr>";
		} //foreach
		$strret .= "</table>\n";

		return $strret;
	}
}

/*
1283- * Recupera os módulos configurados para o APP
1284- */
function modulosMobile()
{

	$idempresa = (!empty($_GET['_idempresa'])) ? "&_idempresa=" . $_GET['_idempresa'] : "";

	$smm = "SELECT 
			'webview' as modo_app,
			m.modulo as modulo,
			m.rotulomenu as rotulomenu,
			m.btncolorapp as bt_color,
			CONCAT('https://sislaudo.laudolab.com.br/?_modulo=',m.modulo,'" . $idempresa . "&cb-canal=app&_menu=N') as rota_app,
			if(a.nome is null, '', CONCAT('/upload/',a.nome)) as endpoint_bt_icon,
			'' as endpoint_search,
			'' as endpoint_clique_pesquisa
		FROM 
			" . _DBCARBON . "._modulo m
			LEFT JOIN arquivo a ON (a.idobjeto = m.idmodulo AND a.tipoobjeto = '_modulo' AND a.tipoarquivo = 'SVG')
		WHERE
			m.status = 'ATIVO'
			AND m.disponivelapp = 'Y' 
			AND m.modulo in (" . getModsUsr("SQLWHEREMOD") . ")";

	$res = d::b()->query($smm);

	//die($smm);

	$arrmod = [];

	while ($r = mysql_fetch_assoc($res)) {
		$arrmod[] = $r;
	}

	return $arrmod;
}

function getOrganogramaRep($colIdPessoa = "idpessoa")
{

	$sql = "select idpessoa, idcontato from vw8organograma where  idpessoa = " . $_SESSION["SESSAO"]["IDPESSOA"] . "
    union
    select " . $_SESSION["SESSAO"]["IDPESSOA"] . " as idpessoa, " . $_SESSION["SESSAO"]["IDPESSOA"] . " as idcontato
    ";

	$res = d::b()->query($sql) or die("Erro Ao recuperar Organograma (function getOrganogramaRep)");
	$row = mysql_fetch_assoc($res);

	return " and $colIdPessoa in (" . $row['idcontato'] . ")";


	/*
	$sql="SELECT 
			*
		FROM
			(SELECT 
				p.idpessoa, p.idobjeto, p.tipoobjeto, p.responsavel
			FROM
				pessoaobjeto p
			WHERE
				p.tipoobjeto = 'sgsetor' UNION ALL SELECT 
				p.idpessoa, p.idobjeto, p.tipoobjeto, p.responsavel
			FROM
				pessoaobjeto p join pessoaobjeto po on (po.idobjeto = p.idobjeto) and po.tipoobjeto = 'sgsetor'
			WHERE
				p.tipoobjeto = 'sgsetor'
					AND p.responsavel = 'Y' UNION ALL SELECT 
				p.idpessoa, p.idobjeto, p.tipoobjeto, p.responsavel
			FROM
				pessoaobjeto p
			WHERE
				p.tipoobjeto = 'sgdepartamento' UNION ALL SELECT 
				p.idpessoa, p.idobjeto, p.tipoobjeto, p.responsavel
			FROM
				pessoaobjeto p
			WHERE
				p.tipoobjeto = 'sgarea') AS a
		WHERE
			a.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]."
		";


	$res = d::b()->query($sql) or die("Erro Ao recuperar Organograma (function getOrganogramaRep)");
	$row=mysql_fetch_assoc($res);

	if($row['tipoobjeto']=='sgsetor' and $row['idobjeto']=='72'){
		return '';
	} else {
		if($row['tipoobjeto']=='sgsetor' and $row['responsavel']=='N'){

			$idpessoa=$row['idpessoa'];

		} else if ($row['tipoobjeto']=='sgsetor' and $row['responsavel']=='Y'){

			$sqlGetSetor="select idpessoa from pessoaobjeto where tipoobjeto='sgsetor' and idobjeto=".$row['idobjeto']."";
			$resSetor = d::b()->query($sqlGetSetor) or die("Erro Ao recuperar pessoas do setor (function getOrganogramaRep)");
			while($rs=mysql_fetch_assoc($resSetor)){
				$idpessoa.= $virg . $rs['idpessoa'];
				$virg=',';
			}

		} else if ($row['tipoobjeto']=='sgdepartamento' and $row['responsavel']=='Y'){

			$resSetorDepartamento="select idobjetovinc from objetovinculo where idobjeto = ".$row['idobjeto']." and tipoobjeto = 'sgdepartamento' and tipoobjetovinc = 'sgsetor' ";
			$res = d::b()->query($resSetorDepartamento) or die("Erro Ao recuperar setores do departamento (function getOrganogramaRep)");
			while($rd=mysql_fetch_assoc($res)){
				$sqlGetSetor="select idpessoa from pessoaobjeto where tipoobjeto='sgsetor' and idobjeto=".$rd['idobjetovinc']."";
				$resSetor = d::b()->query($sqlGetSetor) or die("Erro Ao recuperar pessoas do setor (function getOrganogramaRep)");
				while($rs=mysql_fetch_assoc($resSetor)){
					$idpessoa.= $virg . $rs['idpessoa'];
					$virg=',';
				}
			}
			$idpessoa.=",".$row['idpessoa'];

		} else if ($row['tipoobjeto']=='sgarea' and $row['responsavel']=='Y'){
			$getIdPessoaInArea="select idobjetovinc from objetovinculo where idobjeto = ".$row['idobjeto']." and tipoobjeto = 'sgarea' and tipoobjetovinc = 'sgdepartamento'";
			$resgetIdPessoaInArea = d::b()->query($getIdPessoaInArea) or die("Erro Ao recuperar setores do departamento (function getOrganogramaRep)");

			while($ra=mysql_fetch_assoc($resgetIdPessoaInArea)){
				$SetorDepartamento="select idobjetovinc from objetovinculo where idobjeto = ".$ra['idobjetovinc']." and tipoobjeto = 'sgdepartamento' and tipoobjetovinc = 'sgsetor' ";
				$resSetorDepartamento = d::b()->query($SetorDepartamento) or die("Erro Ao recuperar setores do departamento (function getOrganogramaRep)");

				while($rd=mysql_fetch_assoc($resSetorDepartamento)){
					$getIdpessoaInDepartamento= "select idpessoa from pessoaobjeto where tipoobjeto='sgsetor' and idobjeto=".$rd['idobjetovinc']."";
					$resIdPessoaDep = d::b()->query($getIdpessoaInDepartamento) or die("Erro Ao recuperar pessoas do setor (function getOrganogramaRep)");
					while($rid=mysql_fetch_assoc($resIdPessoaDep)){
						$idpessoa.= $virg . $rid['idpessoa'];
						$virg=',';
					}
					
				}
			}
			$idpessoa.=",".$row['idpessoa'];
		}	

		return " and $colIdPessoa in ($idpessoa)";
	}
	*/
}

function getTableDashFilters($iddashcard, $alias = "")
{

	$ler = true; //Gerar logs de erro
	$rid = "\n" . rand() . " - Sislaudo: ";
	if ($ler) error_log($rid . basename(__FILE__, '.php'));
	$clausula = "";
	$and = " and ";


	$sqlf = "SELECT 
		col, sinal, valor, nowdias, iddashcardfiltros
	FROM
		dashcardfiltros
	WHERE
		TRIM(valor) != ''
			AND ((valor = 'null'
			AND (sinal = 'is' OR sinal = 'is not'))
			OR valor != 'null')
			AND valor IS NOT NULL
			AND iddashcard = " . $iddashcard . " ";

	$resf = d::b()->query($sqlf) or die("A Consulta na dashcardfiltros falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlf");



	while ($rowf = mysqli_fetch_assoc($resf)) {

		// GVT - 13/02/2020 - Alterado a ci=ondição para permitir valores nulos quando estão acompanhados do sinal 'is' ou 'is not'
		if (($rowf["valor"] != 'null' and $rowf["valor"] != ' ' and $rowf["valor"] != '') or ($rowf["valor"] == 'null' and ($rowf["sinal"] == 'is' or $rowf["sinal"] == 'is not' or $rowf["sinal"] == 'session'))) {
			if ($rowf["valor"] == 'now') {
				if (!empty($rowf["nowdias"])) {
					$rowf["nowdias"];
					$date = date("Y-m-d H:i:s");
					$valor = date('Y-m-d H:i:s', strtotime($date . ' - ' . $rowf["nowdias"] . ' day'));
				} else {
					$valor = date("Y-m-d H:i:s");
				}
			} else if ($rowf["valor"] == 'mais') {
				$date = date("Y-m-d H:i:s");
				$valor = date('Y-m-d H:i:s', strtotime($date . ' + ' . $rowf["nowdias"] . ' day'));
			} else if ($rowf["valor"] == 'menos') {
				$date = date("Y-m-d H:i:s");
				$valor = date('Y-m-d H:i:s', strtotime($date . ' - ' . $rowf["nowdias"] . ' day'));
			} elseif ($rowf["valor"] == 'nowdate') {
				if (!empty($rowf["nowdias"])) {
					$rowf["nowdias"];
					$date = date("Y-m-d");
					$valor = date('Y-m-d', strtotime($date . ' - ' . $rowf["nowdias"] . ' day'));
				} else {
					$valor = date("Y-m-d");
				}
			} else if ($rowf["valor"] == 'maisdate') {
				$date = date("Y-m-d");
				$valor = date('Y-m-d', strtotime($date . ' + ' . $rowf["nowdias"] . ' day'));
			} else if ($rowf["valor"] == 'menosdate') {
				$date = date("Y-m-d");
				$valor = date('Y-m-d', strtotime($date . ' - ' . $rowf["nowdias"] . ' day'));
			} else {
				$valor = $rowf["valor"];
			}


			if ($rowf['sinal'] == 'in' || $rowf['sinal'] == 'not in') {
				$strvalor = str_replace(",", "','", $valor);
				$clausula .= $and . " " . $alias . $rowf["col"] . " " . $rowf['sinal'] . " ('" . $strvalor . "')";
			} elseif ($rowf['sinal'] == 'like') {
				$clausula .= $and . " " . $alias . $rowf["col"] . " like ('%" . $valor . "%')";
			} elseif ($rowf['sinal'] == 'is') {
				$clausula .= $and . " " . $alias . $rowf["col"] . " " . $rowf['sinal'] . " " . $valor . "";
			} elseif ($rowf['sinal'] == 'session') {
				//$clausula.= $and." ".$rowf["col"]." = '".$_SESSION["SESSAO"][$valor]."'";
			} elseif ($rowf['sinal'] == 'find_in_set') {
				$clausula .= $and . " find_in_set(" . $valor . " , " . $alias . $rowf["col"] . ")";
			} elseif ($rowf['sinal'] == '=  cod' || $rowf['sinal'] == '>  cod' || $rowf['sinal'] == '>=  cod' || $rowf['sinal'] == '<  cod' || $rowf['sinal'] == '<=  cod') {
				$clausula .= $and . " " . $alias . $rowf["col"] . " " . str_replace("  cod", "", $rowf['sinal']) . " " . $valor . "";
			} else {
				$clausula .= $and . " " . $alias . $rowf["col"] . " " . $rowf['sinal'] . " '" . $valor . "'";
			}
		} else {
			if ($ler) error_log($rid . 'rowf[valor] não previsto');
		}
	}

	return $clausula;
}


function getClausulaCodeDash($code, $cron, $alias = "")
{

	$clausula = "";
	$ignoraClausula = false;

	if ($cron == "N") {
		if ($code == '$idpessoa') {
			$clausula .= " and " . $alias . "idpessoa = '" . $_SESSION["SESSAO"]["IDPESSOA"] . "' ";
		}

		if (strpos($code, "idunidade") !== false) {
			$clausula .= " and exists (select 1 from vw8PessoaUnidade a where a.idpessoa  = '" . $_SESSION["SESSAO"]["IDPESSOA"] . "' and a.idunidade = " . $alias . "idunidade) ";
		}
	}


	if (array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"])) {
		$pessoas =  " and " . $alias . "idpessoa in( " . $_SESSION["SESSAO"]["STRCONTATOCLIENTE"] . "," . $_SESSION["SESSAO"]["IDPESSOA"] . ") ";
	} else {
		$pessoas =  '';
		$ignoraClausula  = true;
	}


	if ($code == '$pessoas') {
		$clausula .= " " . $pessoas . " ";
	}
	if (strpos($code, "estemes_criadoem") !== false) {
		$clausula .= " and " . $alias . "criadoem between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
	}
	if (strpos($code, "estemes_dtemissao") !== false) {
		$clausula .= " and " . $alias . "dtemissao between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
	}
	if (strpos($code, "estemes_datareceb") !== false) {
		$clausula .= " and " . $alias . "datareceb between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
	}
	if (strpos($code, "esteeproximomes_datareceb") !== false) {
		$clausula .= " and " . $alias . "datareceb between DATE_FORMAT(NOW() ,'%Y-%m-01') AND LAST_DAY(DATE_ADD(DATE_FORMAT(NOW() ,'%Y-%m-01'),interval 1 month)) ";
	}

	if (strpos($code, "estemes_fabricacao") !== false) {
		$clausula .= " and " . $alias . "fabricacao between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
	}
	if (strpos($code, "estemes_fabricacao") !== false) {
		$clausula .= " and " . $alias . "fabricacao between DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW() ";
	}
	if (strpos($code, "groupby_idunidade") !== false) {
		$group = " group by " . $alias . "idunidade ";
	}

	if (!$clausula && !$ignoraClausula)
		$clausula = $code;


	$arr['clausula'] = $clausula;
	$arr['group'] = $group;

	return $arr;
}

function modulocustom($modulo, $idpessoa)
{
	if (!empty($idpessoa)) {
		$sqlCustom = "SELECT col, val FROM " . _DBCARBON . "._modulocustom WHERE modulo = '$modulo' AND idpessoa = $idpessoa";
		$rowCustom = d::b()->query($sqlCustom);
		if (!$rowCustom) die("_modulocustom: Erro ao recuperar " .  mysqli_error(d::b()));
		$arrayCustom = [];
		while ($r = mysqli_fetch_assoc($rowCustom)) {
			$arrayCustom[$r['col']] = $r['val'];
		}
	}

	return $arrayCustom;
}

//Verifica o status do ponto
//@todo: MAF: melhorar essa lógica, considerando a escala de trabalho do funcionario
function statusPonto($inidpessoa)
{

	$sp = "select status
	from ponto p 
	where alteradoem > '" . date('Y-m-d') . " 00:00:00'
	and p.idpessoa = " . $inidpessoa . " 
	and status in ('E','S')
	order by idponto desc limit 1";

	$resp = d::b()->query($sp);

	if (mysqli_num_rows($resp) == 0) {
		//Caso nenhum registro seja encontrado, considerar saída
		return "Descansando";
	} else {
		//Verifica batida mais recente
		$rbatidarecente = mysqli_fetch_assoc($resp);
		//Esta condicao previne que a consulta seja alterada e ouros status nao previstos preencham essa variavel
		if ($rbatidarecente["status"] == "S") {
			return "Descansando";
		} else {
			return "Trabalhando";
		}
	}
}

function montaSnippetsAcao()
{
	global $_headers;

	$idempresa = cb::idempresa();
	$lps = getModsUsr('LPS');
	$snippetAcaoHTML = "";

	if ($_headers["cb-canal"] == "app") {
		$mostrarMenu = 'N';
	} else {
		$mostrarMenu = 'Y';
	}

	$snippets = _SnippetController::buscarSnippetsPorLpIdEmpresaEModulos($lps, $idempresa, getModsUsr('SQLWHEREMOD'), '"snippetacao"');

	if (!$snippets) return $snippetAcaoHTML;

	$onclick = (cb::habilitarMatriz() == 'Y' ? 'montaModalEmpresa(this)' : 'abrirEmNovaGuia(this)');

	foreach ($snippets as $snippet) {
		$icone = strpos($snippet['cssicone'], '/') === false ? "<i class='{$snippet['cssicone']} mb-3'></i>" : "<img src='{$snippet['cssicone']}' class='mb-3' />";

		$snippetAcaoHTML .= "<div class='bloco-snippet-action pointer p-2' onclick='" . ($snippet['modulo'] == 'evento' ? 'novaTarefa()' : $onclick) . "' data-modulo='{$snippet['modulo']}' data-menu='{$mostrarMenu}'>
								<div class='text-center'>
									$icone
									<span>{$snippet['snippet']}</span>
								</div>
							</div>";
	}

	return $snippetAcaoHTML;
}
function convertTimeoutToSeconds($timeout)
{
	list($minutes, $seconds) = explode(':', $timeout);
	return ($minutes * 60) + $seconds;
}

function consultaBloqueioRedis($chave)
{
	// Verifica se a chave existe no Redis
	$conteudo = re::dis()->get($chave);

	// Retorna o conteúdo da chave ou false se a chave não existir
	return $conteudo !== false ? $conteudo : null;
}

function geraBloqueioTela()
{
	$timeout = retArrModuloConf($_GET['_modulo'])["timeout"];
	if ($timeout && $timeout != '' && $timeout != '00:00') {


		$ttl = convertTimeoutToSeconds($timeout);
		$pk = $_REQUEST['id' . $_GET['_modulo']];
		$modulo = $_GET['_modulo'];
		$chave = 'lock:' . $modulo . ':' . $pk;
		// Dados da sessão e nome curto
		$idPessoa = $_SESSION["SESSAO"]["IDPESSOA"];
		$nomeCurto = $_SESSION["SESSAO"]["NOMECURTO"]; // Substitua pelo valor real

		if ($pk) {
			
			$verificaChaveExiste = consultaBloqueioRedis($chave);
			

			if ($verificaChaveExiste) {

				$resultadoJson = json_decode($verificaChaveExiste);


				if ($resultadoJson->idpessoa != $idPessoa) {
					// Tem bloqueio e não é desse usuario.
					return array(
						"status" => true,
						"me" => false,
						"timeout" => re::dis()->ttl($chave),
						"nome" => $resultadoJson->nome
					);
				}
				return array(
					"status" => true,
					"me" => true,
					"timeout" => re::dis()->ttl($chave),
					"nome" => $resultadoJson->nome
				);
			} else {
				// Não tem bloqueio Cria um bloqueio
				// Criar o valor JSON
				$valor = json_encode([
					"idpessoa" => $idPessoa,
					"nome" => $nomeCurto
				]);

				re::dis()->set($chave, $valor, ['ex' => $ttl]);
				return array(
					"status" => true,
					"me" => true,
					"timeout" => re::dis()->ttl($chave),
					"nome" => $nomeCurto
				);
			}
		}
	}else{
		return false;
	}
}

function renovaBloqueioTela($pk, $modulo)
{
	$timeout = retArrModuloConf($modulo)["timeout"];
	$ttl = convertTimeoutToSeconds($timeout);
	$chave = 'lock:' . $modulo . ':' . $pk;
	$verificaChaveExiste = consultaBloqueioRedis($chave);
	$idPessoa = $_SESSION["SESSAO"]["IDPESSOA"];
	if ($verificaChaveExiste) {
		$resultadoJson = json_decode($verificaChaveExiste);
		if ($resultadoJson->idpessoa == $idPessoa) {
			re::dis()->expire($chave,$ttl);
			return $resultadoJson;
		}else{
			return false;
		}
	}else{
		return true;
	}
	
}

function removeBloqueioTela($pk, $modulo)
{

	$chave = 'lock:' . $modulo . ':' . $pk;
	$verificaChaveExiste = consultaBloqueioRedis($chave);
	$idPessoa = $_SESSION["SESSAO"]["IDPESSOA"];
	if ($verificaChaveExiste) {
		$resultadoJson = json_decode($verificaChaveExiste);
		if ($resultadoJson->idpessoa == $idPessoa) {
			re::dis()->del($chave);
		}
	}
}

function hexParaRgb($hex)
{
	// Remove o caractere '#' se presente
	$hex = ltrim($hex, '#');

	// Converte os componentes de cor hexadecimal para decimal
	$red = hexdec(substr($hex, 0, 2));
	$green = hexdec(substr($hex, 2, 2));
	$blue = hexdec(substr($hex, 4, 2));

	return array($red, $green, $blue);
}
