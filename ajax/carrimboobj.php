<?
require_once("../inc/php/functions.php");
require_once("../model/evento.php");
require_once(__DIR__."/../form/controllers/fluxo_controller.php");

$idobjeto 	= $_POST["idobjeto"];
$tipoobjeto 	= $_POST["tipoobjeto"];
$acao 	= $_POST["acao"];
$tipo=$_POST["tipo"];

if(empty($idobjeto) or empty($tipoobjeto) OR empty($acao)){
	die("Erro, não foram enviados os parâmetros necessários para o carrimbo.");
}

if($tipo=='assinatura'){
    $statuspadrao="ASSINADO";    
}else{//conferencia
    $statuspadrao="CONFERIDO";    
}


if($acao=='inserir'){
    //TRANSACAO 1

    //Atualiza o Status do Resultado (tabela, pk, valorpk, $statustipo, $modulotipo, $_primaryhist, status)
    $rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idobjeto, $statuspadrao, 'amostra', 'idamostra', '');	

    if($rowFluxo['criadopor'] == $_SESSION['SESSAO']['USUARIO'] and $_SESSION["SESSAO"]["IDEMPRESA"]==1){
        die("Você não pode criar e conferir uma amostra!");
    }


    $sql = "insert into carrimbo
	(idempresa,idpessoa,idobjeto,tipoobjeto, idobjetoext, tipoobjetoext, status,criadopor,criadoem,alteradopor,alteradoem)
	    values
	    (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$_SESSION["SESSAO"]["IDPESSOA"].",".$idobjeto.",'".$rowFluxo['modulo']."', '".$rowFluxo['idfluxostatus']."', 'idfluxostatus', '".$statuspadrao."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
    //die($sql);
    $res = d::b()->query($sql) or die("ERRO inserir carrimbo: ".mysqli_error(d::b())."\n SQL: ".$sql);
    $sql1 = "update amostra
			set status = '".$statuspadrao."'
			where idamostra = " .$idobjeto;
		$res1 = mysql_query($sql1) or die("ERRO 2: ".mysql_error()."\n SQL: ".$sql1);
    
     $sqlaud = "INSERT INTO `_auditoria` (idempresa,linha,acao,objeto,idobjeto,coluna,valor,criadoem,criadopor,tela) 
	 values(1,'1','u','".$tipoobjeto."',".$idobjeto.",'status','".$statuspadrao."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."','".$_SERVER["HTTP_REFERER"]."')";
	    $resaud = mysql_query($sqlaud) or die("ERRO aud: ".mysql_error()."\n SQL: ".$sqlaud);
	    
    //Atualiza o Status do Resultado (tabela, pk, valorpk, $statustipo, $modulotipo, $_primaryhist, status)
    FluxoController::alterarStatus($rowFluxo['modulo'], 'idamostra', $idobjeto, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], '', 'Y', '', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);

}else{
    
    if($tipo=='assinatura'){
        $statusvoltar="DEVOLVIDO";    
    }else{
        $statusvoltar="ABERTO";    
    }

    $sql="update carrimbo set status='OBSOLETO' where idobjeto = ".$idobjeto." and tipoobjeto='".$tipoobjeto."' and status='".$statuspadrao."'";
     $res = d::b()->query($sql) or die("ERRO remover carrimbo: ".mysqli_error(d::b())."\n SQL: ".$sql);
     
     $sql1 = "update amostra
			set status = '".$statusvoltar."' 
			where idamostra = " .$idobjeto;
		$res1 = mysql_query($sql1) or die("ERRO 2: ".mysql_error()."\n SQL: ".$sql1);
	
	$sqlaud = "INSERT INTO `_auditoria` (idempresa,linha,acao,objeto,idobjeto,coluna,valor,criadoem,criadopor,tela) 
	 values(1,'1','u','".$tipoobjeto."',".$idobjeto.",'status','".$statusvoltar."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."','".$_SERVER["HTTP_REFERER"]."')";
	    $resaud = mysql_query($sqlaud) or die("ERRO aud: ".mysql_error()."\n SQL: ".$sqlaud);

    //LTM (13-04-2021): Atualiza o Status do Resultado (tabela, pk, valorpk, $statustipo, $modulotipo, $_primaryhist, status)
    $rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idobjeto, $statusvoltar, 'amostra', 'idamostra', '');
    FluxoController::alterarStatus($rowFluxo['modulo'], 'idamostra', $idobjeto, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], '', 'Y', '', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
}


ECHO('OK');		