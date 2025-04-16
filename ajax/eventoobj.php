<?

require_once("../inc/php/validaacesso.php");
require_once("../inc/php/functions.php");


$idevento	= $_GET["videvento"];
$idobjeto	= $_GET["vidobjeto"];
$objeto		= $_GET["vobjeto"];
$opcao		= $_GET["vopcao"];


if (empty($opcao) or empty($objeto) or empty($idobjeto) or empty($idevento)){
	die("VariÃ¡vel POST nÃ£o enviada corretamente!");
} else {

	if ($opcao == "add"){
		$sql = "insert into eventoobj (idevento,idobjeto,objeto,idempresa,criadoem) values (".$idevento.",".$idobjeto.",'".$objeto."',".$_SESSION["SESSAO"]["IDEMPRESA"].",sysdate())";
		$res = d::b()->query($sql) or die("(1)".mysqli_error(d::b()).$sql);
		die("OK");

	}elseif ($opcao == "remove"){
		$sql = "delete from eventoobj where idevento=".$idevento." and objeto = '".$objeto."' and idobjeto = ".$idobjeto." and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"];
		$res = d::b()->query($sql) or die("(2)".mysqli_error(d::b()));
		die("OK");

	} else {
		die("<res>Erro</res>");
	}

}

?>