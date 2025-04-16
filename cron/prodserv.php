<?//maf270320: Colocar a reconfiguracao da arvore de protuso e insumos em modo background
ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
  include_once("/var/www/carbon8/inc/php/laudo.php");
}else{//se estiver sendo executado via requisicao http
  include_once("../inc/php/functions.php");
  include_once("../inc/php/laudo.php");
}

$grupo = rstr(8);

//Controle de lock: evita que processos rodem em paralelo; Colocar a instrucao del no fim; Tempo em segundos
$rkey='cron:_lock:'.$_SERVER["SCRIPT_FILENAME"];
$iniLock=re::dis()->get($rkey);
if($iniLock===false){
        re::dis()->set($rkey,date('d/m/y h:i'),3600);
}else{
	echo 'Adiado. Rodando desde '.$iniLock;
	d::b()->query("INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `status`, `log`, `criadoem`, `data`) VALUES ('1', '','cron', 'prodserv', 'status', 'ADIADO', 'LOCKED. Rodando desde ".$iniLock."', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))") or die("Erro #1 ao inserir log: ".mysqli_error(d::b())."<br>".$sqll);	
	die;
}

re::dis()->hMSet('cron:prodserv',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'prodserv', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

//Efetuar echo de todas as consultas executadas
d::$echoSql=true;

$inicio = new DateTime();
//MAF010121: Isola a transacao para 'dirty mode'
d::b()->query("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;");

if(!empty($_GET["idprodserv"])){
  armazenaConfiguracaoArvoreInsumos($_GET["idprodserv"], false);//habilitar debug com echo
}else{
  armazenaConfiguracaoArvoreInsumos();//habilitar debug com echo
}

//Ao fim: remover lock
re::dis()->del($rkey);

$fim = new DateTime();
$sfim=$fim->format( 'H:i:s' );
$tempo = $inicio->diff($fim);

$tempo = $tempo->format( '%H:%I:%S' );


re::dis()->hMSet('cron:prodserv',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'prodserv', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
?>