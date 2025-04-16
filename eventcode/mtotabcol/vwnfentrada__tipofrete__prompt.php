<?
#MAF: Este cabecalho deve estar presente em qualquer evento
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

?>
<?
if (cb::idempresa() == 1){
	echo '[{"SUPRIMENTOS":"Suprimentos"},{"RECEBIMENTO":"Recebimento de Materiais"},{"ENVIO":"Envio de Produtos"}]';
}else{
echo '[{"":""}]';
}
?>

