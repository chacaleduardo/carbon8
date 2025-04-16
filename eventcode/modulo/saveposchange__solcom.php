<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
 

$_idsolcom = $_SESSION["arrpostbuffer"]["1"]["u"]["solcom"]["idsolcom"];

//Reprova os itens que não tem vinculo com nenhuma cotação
$status = $_SESSION["arrpostbuffer"]["1"]["u"]["solcom"]["status"];
if($status == 'CANCELADO' || $status == 'REPROVADO')
{
    SolcomController::atualizarStatusSolcomItem($status, $_idsolcom);
}

//Itens a serem restaurados
$_status = $_SESSION["arrpostbuffer"]["sia"]["u"]["solcomitem"]["status"];
if($_status == 'REPROVADO' || $_status == 'CANCELADO')
{
    $qtdSolcom = SolcomController::buscarQtdItensSolcomItem($_idsolcom);

    if($qtdSolcom == 1)
    {
        SolcomController::atualizarStatusSolcom('REPROVADO', $_idsolcom);
        $rowFluxo = FluxoController::getFluxoStatusHist('solcom', 'idsolcom', $idsolcom, 'REPROVADO');
        FluxoController::alterarStatus('solcom', 'idsolcom', $idsolcom, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);	
    }
}

foreach($_POST as $k => $v) 
{
    if((preg_match("/_solmat/", $k, $res) || preg_match("/_soltag/", $k, $res)) && preg_match("/idsolcomitem/", $k, $res))
    {
        $indice = explode("_", $k);
        $idsolcomitem = $_SESSION['arrpostbuffer'][$indice[1]]['u']['solcomitem']['idsolcomitem'];
        $resSolmat = SolcomController::buscarDadosProdutoPorIdsolcomItem($idsolcomitem);
        $qtdSolmat = $resSolmat['qtdLinhas'];
        $rowSolmat = $resSolmat['dados'];

        if($qtdSolmat > 0)
        {
            $usuario = $_SESSION["SESSAO"]["USUARIO"];
            if(empty($idsolmat))
            {
                $tipo = preg_match("/_solmat/", $k, $res) ? 'MATERIAL' : 'EQUIPAMENTOS';
                $modulo = preg_match("/_solmat/", $k, $res) ? 'solmat' : 'soltag';
                $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'ABERTO');
                $unidadepadrao = getUnidadePadraoModulo($modulo, $rowSolmat['idempresa']);
                $idsolmat = SolcomController::inserirSolmat($rowSolmat['idempresa'], 'ABERTO', $idfluxostatus, $tipo, $rowSolmat['idunidade'], $unidadepadrao, $usuario);
            }
            
            if(preg_match("/_soltag/", $k, $res)){
                $qtd = $_POST['qtd'.$indice[1]];
                $qtdc = '1';
            } else {
                $qtd = 1;
                $qtdc = $rowSolmat['qtdc'];
            }

            for($i = 0; $i < $qtd; $i++){
                $arrayInsertSolmatItem = [
                    "idempresa" => $rowSolmat['idempresa'],
                    "idsolmat" => $idsolmat,
                    "qtdc" => $qtdc,
                    "idprodserv" => $rowSolmat['idprodserv'],
                    "descr" => $rowSolmat['descr'],
                    "obs" => '',
                    "un" => $rowSolmat['un'],
                    "usuario" => $_SESSION["SESSAO"]["USUARIO"]
                ];
                $idsolmatitem = SolcomController::inserirSolmatItem($arrayInsertSolmatItem);
            }

            if(preg_match("/_solmat/", $k, $res)) {
                SolcomController::atualizarSolmatSolcomItem($idsolmatitem, $idsolcomitem);
            } elseif(preg_match("/_soltag/", $k, $res)){
                SolcomController::atualizarSolmatSolTagItem($idsolmat, $idsolcomitem);
            }
            
        }
    }
}
?>