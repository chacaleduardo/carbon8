<?
require_once("../inc/php/functions.php");
session_start();

$acao = $_GET["acao"];
$idresultado = $_GET["idresultado"];
$emailsec = $_GET["emailsec"];

if(empty($acao)  or empty($idresultado) or empty($emailsec)){
    echo "ERRO: PARAMETRO GET VAZIO ";
    die();
}

if($acao=="true"){

	$res = d::b()->query("START TRANSACTION");
	if(!res){
		echo "ERRO AO ABRIR TRANSACAO PARA INCLUIR ALERTA NO RESULTADO: ".mysqli_error(d::b())."";
		//die();
	}

	$sql = "update resultado
		set alerta = 'Y',emailsec='".$emailsec."', alteradoem = now(), alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."'
		where idresultado = " .$idresultado;
	$res = d::b()->query($sql) or die("ERRO : ".mysql_error(d::b())."\n SQL: ".$sql);

	if(!$res){
		$res = d::b()->query("ROLLBACK");
		if(!$res){
			echo "ERRO AO EFETUAR [ROLLBACK] PARA A ALTERACAO: ".mysql_error()."";
			die();
		}
		echo "ERRO AO INCLUIR ALERTA PARA O RESULTADO: ".mysql_error(d::b());
		die();
	}else{
		$res = d::b()->query("COMMIT");
		if(!$res){
			echo "ERRO AO EFETUAR [COMMIT] PARA A ALTERACAO: ".mysql_error(d::b())."";
			die();
		}
		echo "OK";

		$sqlaud = "insert into _auditoria 
			(idempresa, linha, acao, objeto, idobjeto, coluna, valor, criadoem, criadopor, tela)
			values
			(".$_SESSION["SESSAO"]["IDEMPRESA"]." ,1,'u','resultado',".$idresultado.",'alerta','Y',now(),'".$_SESSION["SESSAO"]["USUARIO"]."','alertateste.php')
			,(".$_SESSION["SESSAO"]["IDEMPRESA"]." ,1,'u','resultado',".$idresultado.",'alteradopor','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."','alertateste.php')";

                //die($sqlaud);
		d::b()->query($sqlaud) or die("Erro ao gerar auditoria");

		//die();
	}

}elseif($acao=="false"){
	$res = d::b()->query("START TRANSACTION");
	if(!res){
		echo "ERRO AO ABRIR TRANSACAO PARA INCLUIR ALERTA NO RESULTADO: ".mysql_error(d::b())."";
		die();
	}

	$sql = "update resultado
		set alerta = 'N',emailsec = 'N', alteradoem = now(), alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."'
		where idresultado = " .$idresultado;
	$res = d::b()->query($sql);

	if(!$res){
		$res = d::b()->query("ROLLBACK");
		if(!$res){
			echo "ERRO AO EFETUAR [ROLLBACK] PARA A ALTERACAO: ".mysql_error(d::b())."";
			die();
		}
		echo "ERRO AO RETIRAR O ALERTA DO RESULTADO: ".mysql_error(d::b());
		die();
	}else{
		$res = d::b()->query("COMMIT");
		if(!$res){
			echo "ERRO AO EFETUAR [COMMIT] PARA A ALTERACAO: ".mysql_error(d::b())."";
			die();
		}
		echo "OK";

		$sqlaud = "insert into _auditoria 
			(idempresa, linha, acao, objeto, idobjeto, coluna, valor, criadoem, criadopor, tela)
			values
			(".$_SESSION["SESSAO"]["IDEMPRESA"]." ,1,'u','resultado',".$idresultado.",'alerta','N',now(),'".$_SESSION["SESSAO"]["USUARIO"]."','alertateste.php')
			,(".$_SESSION["SESSAO"]["IDEMPRESA"]." ,1,'u','resultado',".$idresultado.",'alteradopor','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."','alertateste.php')";

		d::b()->query($sqlaud) or die("Erro ao gerar auditoria");


		//die();
	}
}



?>
