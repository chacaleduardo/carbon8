<?
require_once "functions.php";
ini_set("display_errors",0);

/*
	@todo: Testar o tipo da coluna contra o valor informado para where
	@todo: Tratar chamadas consecutivas, resetando as propriedades da singleton
	@todo: Verificar possiveis erros quando se utiliza uma tabela configurada na _formobjetos
	@todo: Quando for o caso de tabela da _formobjetos, não validar se foi marcado como visivel
	@todo: Conferir funcionamento do FTS
	@todo: Conferir funcionamento do FDS

	Classe singleton para obtencao de dados dos modulos, seguindo as configuracoes e o dicionario de dados
	Importante: Todas as classes publicas irao reservar seu nome. Portanto o metodo magico para colunas com essas nomeclaturas nao funcionara, gerando o erro: q::err: Coluna não existente na tabela: [nomemetodo]

	Modos de uso:

	1) Usando metodo magico para o modulo
		Caso o primeiro metodo estatico invocado nao exista declarado na classe, considera-se que é o modulo desejado. Ex:
		Recuperar dados do modulo tag:
			q::tag()::fetch()

	2) Declarando manualmente o modulo
			q::mod("tag")::exec() ou q::m("tag")::fetch()

	3) Usando metodo magico para o modulo e fazer chaining das colunas desejadas para clausula where
			q::mod("tag")::idfluxostatus(24) //Para numeros
			q::mod("tag")::status("nao avaliado") //Para string. Aspas simples nao sao permitidas.

	4) Usando o metodo where
			q::mod("tag")::where("status=nao avaliado") ou q::mod("tag")::w("status=nao avaliado")
*/


class q{
	//Strings
	public static $erro="";
	private static $modulo;
	private static $tabela="";
	private static $fts;
	private static $hints="";

	//Array
	private static $modConf=[];
	public static $search=[];
	private static $filtros=[];
	private static $arrtabela;
	private static $colunas=[];
	private static $coldata=[];

	//Boolean
	public static $die=true;
	public static $debugSql=false;
	public static $setPostHeaders=true;
	public static $echoSql=false;
	public static $mostrarTodasColunas=false;

	//Controle
	private static $magiclevel=0;//Controla o chaining automatico, usando o primeiro magic para o modulo, e subsequentes para colunas

	public static function reset(){
		null;
	}

	private static function getIncludedFile(){
		$file = false;
		$backtrace =  debug_backtrace();
		$include_functions = array('include', 'include_once', 'require', 'require_once');
		for ($index = 0; $index < count($backtrace); $index++)
		{
			$function = $backtrace[$index]['function'];
			if (in_array($function, $include_functions))
			{
					$file = $backtrace[$index - 1]['file'];
					break;
			}
		}
		return $file;
	}

	public static function err($msg,$ln="",$fn=__METHOD__){
		self::$erro=$msg;
		header("CB-ERRO-LINE: ".$ln);
		header("CB-ERRO-FUNCTION: ".$fn);
		$local=str_replace("__","->",basename(self::getIncludedFile(),".php"));
		$local=$local==""?__METHOD__:$local;
		header("CB-ERRO-LOCAL: ".$local);
		if(self::$setPostHeaders){
			cbSetPostHeader("0","erro");
		}
		if(self::$die){
			die($local.": ". $msg);
		}else{
			return __CLASS__;
		}
	}

	/*
	 * Magic method: https://www.php.net/manual/en/language.oop5.magic.php
	 * Este metodo é chamado como uma especie de trigger, disparada quando um metodo estatico nao existente é invocado
	 */
	public static function __callStatic($magicmetodo, $args){
		//Se for o primeiro metodo invocado, e nao houver parametros, considerar como sendo o modulo
		if(self::$magiclevel==0){
			if(sizeof($args)===0){
				self::reset();
				self::$magiclevel++;
				self::m($magicmetodo);
			}
		}else{
			if(self::$magiclevel>0 and sizeof($args)===1){
				self::where($magicmetodo."=".$args[0]);
			}
		}
		return __CLASS__;
	}
	
	//Ajusta o módulo
	public static function m($inmod){
		self::$magiclevel++;
		self::$modulo=$inmod;
		return __CLASS__;
	}

	//Ajusta o módulo
	public static function mod($inmod){
		self::$magiclevel++;
		self::m($inmod);
		return __CLASS__;
	}

	public static function _idempresa(){
		self::$magiclevel++;
		//maf160211: Multi Empresas sempre concatenar o IDEMPRESA
		//maf160311: Excluir pagina de search para mtotabcol
		$arrbypassempresa = retbypassidempresa();

		if(!in_array(self::$modConf["tab"],$arrbypassempresa)){
			if(empty(cb::idempresa()) and cb::usr["FULLACCESS"]!="Y"){
				return self::err("Idempresa vazio.");
			}else{

				$clausula = share::moduloFiltrosPesquisa("a.".self::$modConf["chavefts"]);

				// Isso deve ser MUITO testado para abranger todos casos de uso
				if($clausula === false){
					self::$search["WHERE"]["idempresa"] = " 1 ".getidempresa('idempresa',self::$modulo);
				}else{
					self::$search["WHERE"]["idempresa"] = " EXISTS ".$clausula;
				}
			}
		}
	}

	/*
	 * Full Text search
	 * Realizar pesquisa em bancos de dados internos ou externos de FULL TEXT SEARCH, e permitir pesquisas booleanas
	 * Ela retorna os IDs da tabela (informada no módulo) para serem utilizados em cláusula 'in'
	 */
	public static function _fts($infts){
		self::$magiclevel++;

		$strPkFts="";
		$arrFk=array();
		$countArrFk=null;
		
		//Verifica se a PK é algum tipo de char, para colocar aspas nos elementos da cláusula in
		$aspa = (strpos(self::$arrtabela[self::$modConf["tab"]][self::$modConf["chavefts"]]["datatype"],"char"))?"'":"";
		
		if(!empty($infts)){

			$arrFk = retPkFullTextSearch(self::$modConf["tab"], $infts);

			$countArrFk=$arrFk["foundRows"];
			if($countArrFk>0){
				$strPkFts = $aspa . implode($aspa.",".$aspa, $arrFk["arrPk"]) . $aspa;
				self::$search["WHERE"][] = self::$modConf["chavefts"] . " in (".$strPkFts.")";
			}
		}else{
			if($infts != ""){
				return self::err("Informe um parâmetro válido para a pesquisa!");
			}
		}
	}

	/* @todo: INCOMPLETO
	 * Date search
	 * Realizar pesquisa em bancos de dados internos em colunas de tipo date/datetime
	 */
	private static function fds($infds){
		self::$magiclevel++;
		$_strwherefds="";
		if(!empty($_GET["_fds"])){
			if(sizeof(self::$coldata)==0){
				//cbSetPostHeader("0","alert");
				//die("Nenhuma coluna de data foi configurada para pesquisa neste Mà³dulo. \nNão informe nenhuma data no calendário.");
			}else{
				//ajusta preferencias do usuario
				userPref("u", self::$modulo."._fds", $_GET["_fds"]);
				
				$arrdatas = explode("-", $_GET["_fds"]);
				$arrdatas[0] =  validadate($arrdatas[0])." 00:00:00";
				$arrdatas[1] =  validadate($arrdatas[1])." 23:59:59";
		
				if(in_array($_GET["_fdscol"],self::$coldata)){
					$_strwherefds = $_GET["_fdscol"]." between '".$arrdatas[0]."' and '".$arrdatas[1]."'";
					self::$search["WHERE"][] = "(".$_strwherefds.")";
				}
				
			}
		}else{
			userPref("d", self::$modulo."._fds");
		}
		return __CLASS__;
	}

	public static function _cols($incols=""){
		if($incols!==""){
			self::$search["SELECT"][] = $incols;
		}elseif(self::$mostrarTodasColunas===true){
			self::$search["SELECT"][] = "*";
		}else{
			$arrvisres=[];
			foreach (self::$colunas as $col=>$conf){
				if($conf["visres"]=="Y"){
					$arrvisres[]=$col;
				}
			}
			if(sizeof($arrvisres)===0){
				return self::err("Nenhuma coluna foi configurada para ser mostrada na pesquisa");
			}else{
				self::$search["SELECT"][]=implode(", ",$arrvisres);
			}
		}
	}

	/*
	 * Inicializa clausula FROM default da tabela para pesquisa do mod
	 */
	public static function _from($infrom=""){
		if($infrom!==""){
			self::$search["FROM"][] = $infrom;
		}elseif(!empty(self::$tabela)){
			self::$search["FROM"][] = self::$tabela;
		}else{
			self::$search["FROM"][] = nomeTabela(self::$modConf["tab"])." a ";	
		}
	}

	public static function pk($inpk){
		self::init();
		if(!empty(self::$tabela)){
			$cpk=retarraytabdef(self::$tabela)["#pkfld"];
		}else{
			$cpk=retarraytabdef(self::$modConf["tab"])["#pkfld"];
		}
		self::where($cpk."=".$inpk);
		return __CLASS__;
	}

	public static function t($intab){
		return self::tab($intab);
	}
	public static function tab($intab){

		if(empty($intab)){
			return self::err("Tabela informada incorretamente");
		}else{
			if(empty(self::$modulo)){
				return self::err("Módulo informado incorretamente");
			}else{
				$rtp=d::b()->query("SELECT *
					FROM "._DBCARBON."._formobjetos o
					WHERE o.modulo = '".self::$modulo."'
						AND o.tipoobjeto in ('tabela','tabelacbpost')
						AND o.objeto = '".mysql_real_escape_string($intab)."'");
				if(!$rtp){
					return self::err("Erro ao verificar tabelas do Módulo informado");
				}else{
					if($rtp->num_rows===0){
						return self::err("O Módulo ".self::$modulo." deve possuir permissão à tabela ".$intab);
					}else{
						self::$tabela=mysql_real_escape_string($intab);
					}
				}
			}
		}

		return __CLASS__;
	}

	public static function w($inwhere){
		return self::where($inwhere);
	}

	public static function where($inwhere){
		self::init();
		$colw=explode("=",$inwhere);

		if(sizeof($colw)!==2){
			return self::err("Cláusula where inválida: ".$inwhere);
		}else{

			if(!array_key_exists($colw[0], self::$colunas)){
				return self::err("Coluna não existente na tabela: ".$colw[0]);
			}else{

				if(!is_numeric($colw[1])){
					$colw[1]=str_replace("'","",$colw[1]);
					$colw[1]="'".$colw[1]."'";
				}
				self::$search["WHERE"][]=$colw[0]."=".$colw[1];
			}
		}
		return __CLASS__;
	}

	public static function _hint($inhint){
		self::$hints.= " ".$inhint;
		return __CLASS__;
	}

	private static function init(){
		//Recupera configuracoes
		self::$modConf = retArrModuloConf(self::$modulo); 

		//Abre variaveis para facilitar digitacao
		if(empty(self::$tabela)){
			self::$filtros = retArrModuloConfFiltros(self::$modulo);
			self::$arrtabela = self::$filtros["tabela"];
			self::$coldata = self::$filtros["coldata"];
			self::$colunas=retarraytabdef(self::$modConf["tab"]);
		}else{
			self::$colunas=retarraytabdef(self::$tabela);
		}

	}

	/*
	 * Função para montar comando SQL
	 * calcfoundrows substuido por $hints
	 */
	public static function sql(){

		self::init();

		//Inicializa o select
		self::_cols();
		self::_from();

		//Inicializa *ORDER BY*
		if(trim(self::$modConf["orderby"])!==""){
			self::$search["ORDERBY"][]= self::$modConf["orderby"];
		}

		$_tmpsql = "";
		$_tmpsql .= (count(self::$search["SELECT"])>0)	? "\nSELECT " . self::$hints . " " . implode(", ",self::$search["SELECT"])	:"";
		$_tmpsql .= (count(self::$search["FROM"])>0)		? "\nFROM " . implode(" ",self::$search["FROM"])			:"";
		$_tmpsql .= (count(self::$search["WHERE"])>0)		? "\nWHERE " . implode(" and ",self::$search["WHERE"])	:"";
		$_tmpsql .= (count(self::$search["GROUPBY"])>0)	? "\nGROUP BY " . implode(" ",self::$search["GROUPBY"])	:"";
		$_tmpsql .= (count(self::$search["ORDERBY"])>0)	? "\nORDER BY " . implode(" ",self::$search["ORDERBY"])	:"";
		$_tmpsql .= (count(self::$search["LIMIT"])>0)		? "\nLIMIT " . implode(" ",self::$search["LIMIT"])		:"";

		if(self::$echoSql===true){
			echo PHP_EOL.$_tmpsql.PHP_EOL; 
		}

		return $_tmpsql;

	}

	//Executa a pesquisa
	public static function exec(){

		//_idempresa();

		$res=d::b()->query(self::sql());

		if(!$res){
			self::err(mysqli_error(d::b()));
		}else{
			return $res;
		}

	}

}
