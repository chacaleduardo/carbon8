<?//maf270320: Colocar a reconfiguracao da arvore de protuso e insumos em modo background
ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
  include_once("/var/www/carbon8/inc/php/laudoteste.php");
}else{//se estiver sendo executado via requisicao http
  include_once("../inc/php/functions.php");
  include_once("../inc/php/laudoteste.php");
}

openlog("CBPOST", 0, LOG_LOCAL0);

syslog(LOG_DEBUG, "ARVORE_PRODSERV: Iniciando armazenamento: ".date("H:m:s"));

$inicio = new DateTime();
armazenaConfiguracaoArvoreInsumos();
$fim = new DateTime();
$sfim=$fim->format( 'H:i:s' );
$tempo = $inicio->diff($fim);

$tempo = $tempo->format( '%H:%I:%S' );

syslog(LOG_DEBUG, "ARVORE_PRODSERV: Fim armazenamento: ".$sfim);
syslog(LOG_DEBUG, "ARVORE_PRODSERV: Tempo decorrido: ".$tempo);
