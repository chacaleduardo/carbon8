<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$arrpostbuffer = $_SESSION["arrpostbuffer"];
//print_r($arrpostbuffer);
//$status=$_POST['status'];

$qtdreg= count($arrpostbuffer);
if($qtdreg > 0){			
    while (list($key, $value) = each($arrpostbuffer)) {									
        $idobjeto=$_SESSION['arrpostbuffer'][$key]['i']['carrimbo']['idobjeto'];
        
        if(!empty($idobjeto))
        {
            $sqlaud = "INSERT INTO `_auditoria` (idempresa,linha,acao,objeto,idobjeto,coluna,valor,criadoem,criadopor,tela) 
            values(".$_SESSION["SESSAO"]["IDEMPRESA"].",'1','u','resultado',".$idobjeto.",'status','CONFERIDO',now(),'".$_SESSION["SESSAO"]["USUARIO"]."','".$_SERVER["HTTP_REFERER"]."')";
            $resaud = mysql_query($sqlaud) or die("ERRO aud: ".mysql_error()."\n SQL: ".$sqlaud);

            //Atualiza o Status do Resultado
            $sqlFluxo = "SELECT fs.idfluxostatus, 
                                f.idfluxo,
                                (SELECT idfluxostatushist FROM fluxostatushist fh WHERE fh.idmodulo = r.idamostra AND fh.modulo = f.modulo ORDER BY idfluxostatushist DESC LIMIT 1) AS idfluxostatushist,
                                fs.ordem,
                                s.statustipo,
                                s.tipobotao,
                                m.modulo,
                                r.idamostra
                            FROM resultado r JOIN amostra a ON a.idamostra = r.idamostra
                            JOIN unidadeobjeto uo ON a.idunidade = uo.idunidade AND uo.tipoobjeto = 'modulo'
                            JOIN carbonnovo._modulo m on m.modulo = uo.idobjeto and m.modulotipo = 'resultado' 
                            JOIN fluxo f ON f.modulo = uo.idobjeto AND f.status = 'ATIVO'
                            JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
                            JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus AND s.statustipo = 'CONFERIDO'
                            WHERE r.idresultado = '".$idobjeto."'";
            $resFluxo = d::b()->query($sqlFluxo) or die(mysqli_error(d::b())." Erro ao buscar fluxo $statuspadrao ".$sqlFluxo);
            $rowFluxo = mysqli_fetch_assoc($resFluxo);
             
            FluxoController::alterarStatus($rowFluxo['modulo'], 'idresultado', $idobjeto, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], '', 'Y', '', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);	
        }
    }

}else{
	die("Nenhum registro selecionado para CONFERENCIA.");
}
