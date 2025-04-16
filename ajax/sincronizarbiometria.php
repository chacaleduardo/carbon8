<?
require_once("../inc/php/functions.php");
$idpessoa = $_GET["idpessoa"];

	
if(!empty($idpessoa) || $idpessoa == 0){
	$sqlsincroniza = "UPDATE reppessoa p
				  INNER JOIN rep r ON (p.idrep = r.idrep)
				  INNER JOIN pessoa pe ON pe.idpessoa = p.idpessoa AND pis != '' AND pis != '000000000000'
					     SET p.status = 'ATUALIZARBD'
					   WHERE p.idpessoa = ".$idpessoa." AND r.tipo = 'MASTER'";

	d::b()->query($sqlsincroniza) or die("Erro ao atualizar o status do Colaborador ".$idpessoa." : ".mysqli_error(d::b())."<p>SQL: ".$sqlsincroniza);
}else{
	echo "Não foi possível sincronizar a biometria do Colaborador.";
}

?>