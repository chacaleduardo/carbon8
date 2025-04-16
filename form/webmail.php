<?

require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");


$pagvaltabela = "pessoa";
$pagvalcampos = array(
	"idpessoa" => "pk"
);

$_GET["_acao"]="u";
$_GET["idpessoa"]=$_SESSION["SESSAO"]["IDPESSOA"];

$pagsql = "select * from pessoa where idpessoa = '#pkid'";
include_once("../inc/php/controlevariaveisgetpost.php");


$strtoken =enc("usuario=".$_1_u_pessoa_webmailusuario."&senha=".$_SESSION["SESSAO"]["SENHA"]."&validade=".date("Ymd"));
//die($strtoken);

/* /maf160520: validar o novo webmail temporariamente com usuarios especificos
$users="marcelo_nashsolucoes|marcelocunha_laudolab|fabio_laudolab|gabrieltiburcio_laudolab|guilhermealves_laudolab";

if(preg_match('/'.$users.'/',$_1_u_pessoa_webmailusuario)){
	$urlwebmail = "http://mail2.laudolab.com.br/";
}else{
	$urlwebmail = "http://mail.laudolab.com.br/";
}*/

$urlwebmail = "https://mail.laudolab.com.br/";
$urlwebmail = $urlwebmail."?_autologin=1&jwt=".$_SESSION["SESSAO"]["JWT"]."&_token=".$strtoken;

header("Location: ".$urlwebmail);

?>
