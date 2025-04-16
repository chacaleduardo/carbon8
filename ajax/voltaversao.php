<?
require_once("../inc/php/validaacesso.php");

$versao         =$_POST['versao_desejada'];
$versaoatual    =$_POST['versao_atual'];
$status         =$_POST['status'];
$idfluxostatus  =$_POST['idfxstatus'];
$descricao      =$_POST['descricao_motivo'];
$idsgdoc        =$_POST['iddoc'];

$descfluxostatushistobs= "INSERT into fluxostatushistobs (idempresa, idmodulo, modulo, motivo, motivoobs,versaoorigem,versao, status, idfluxostatus, criadoem, criadopor, alteradoem, alteradopor) values (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idsgdoc.",'documento','Restauração','".$descricao."',".$versaoatual.",".$versao.",'".$status."','".$idfluxostatus."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."')";
$insfluxostatusshit = d::b()->query($descfluxostatushistobs) or die("A atualização da tabela sgdoc falhou :".mysql_error(d::b())."<br>Sql:".$descfluxostatushistobs);


$sqlsgdoc="UPDATE sgdoc SET conteudo =(select conteudo from sgdocupd where idsgdoc= ".$idsgdoc." and versao = '".$versao."'), versao = '".$versao."', revisao = 0 , alteradoem = now(), alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."' WHERE idsgdoc = ".$idsgdoc." ";
$updatesgdoc = d::b()->query($sqlsgdoc) or die("A atualização da tabela sgdoc falhou :".mysql_error(d::b())."<br>Sql:".$sqlsgdoc);


$sqlsgdocupd ="DELETE FROM sgdocupd WHERE idsgdoc=".$idsgdoc." AND versao > ".$versao." ";
$deletesgdoc = d::b()->query($sqlsgdocupd) or die("A delete na tabela sgdocupd falhou :".mysql_error(d::b())."<br>Sql:".$sqlsgdocupd);

$sqlsgdocupdt = "UPDATE sgdocupd SET status = 'APROVADO', alteradoem = now(), alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."' WHERE idsgdoc = ".$idsgdoc." and versao = ".$versao." ORDER BY idsgdocupd DESC LIMIT 1";
$updatesgdocupd = d::b()->query($sqlsgdocupdt) or die("A atualização da tabela sgdocupd falhou :".mysql_error(d::b())."<br>Sql:".$sqlsgdocupdt);


$sqlcarrimboupd = "UPDATE carrimbo SET versao = ".$versao.", status = 'PENDENTE', alteradoem = now(), alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."' WHERE idobjeto = ".$idsgdoc." and versao > ".$versao."";
$updatecarrimbo = d::b()->query($sqlcarrimboupd) or die("A atualização da tabela carrimbo falhou :".mysql_error(d::b())."<br>Sql:".$sqlcarrimboupd);
