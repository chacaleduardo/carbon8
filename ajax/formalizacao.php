<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/formalizacao_controller.php");
require_once("../form/controllers/pedidoemlote_controller.php");
require_once("../form/controllers/amostra_controller.php");

$tipo = $_GET['tipo'];
$idprodserv = $_GET['idprodserv'];
$idprodservformula = $_GET['idprodservformula'];
$idpessoa = $_GET['idpessoa'];
$status = $_GET['status'];

if($tipo == 'buscarFormula' && !empty($idprodserv))
{   
    echo '<option></option>';
    fillselect(FormalizacaoController::buscarFormulaAtivaPorProdserv($idprodserv));
}

if($tipo == 'dadosProdserv' && !empty($idprodserv))
{
    $prodserv = FormalizacaoController::buscarProdutoPorIdProdserv($idprodserv);
    echo json_encode($prodserv[0]);
}


if($tipo == 'buscarSolfab' && !empty($idpessoa))
{
    $solfab = FormalizacaoController::buscarClientesSolicitacaoFabricacao($idprodservformula, $idpessoa, $status);
    echo $solfab;
}

if($tipo == 'interromperOP' && !empty($idLoteAtivArr))
{
    $solfab = FormalizacaoController::buscarClientesSolicitacaoFabricacao($idprodservformula, $idpessoa, $status);
    echo $solfab;
} 

// Cancelando testes vinculados as atividades
if($tipo = 'cancelarAtividades' && $_POST['idloteativ']) {
    $atividadeNaoConforme = false;

    if($_POST['atividadeNaoConforme']) $atividadeNaoConforme = true;

    // Passar oq for necessario via GET
    $idLote = $_POST['idlote'];
    $idUnidade = $_POST['idunidade'];
    $idLoteAtiv = explode(',', $_POST['idloteativ']);
    $idFluxoStatusOP = $_POST['idfluxostatus'];
    $idFormalizacao = $_POST['idformalizacao'];
    $moduloFormalizacao = $_POST['_modulo'];
    $idEmpresa = $_GET['_idempresa'];
    $motivo = $_POST['motivo'];
    $statusCancelado = 'CANCELADO';
    $idPrProc = $_POST['idprproc'];

    if(!$idLote || !$idUnidade) {
        echo json_encode([
            'error' => 'ID unidade ou ID lote não informado',
            'success' => false
        ]);
        exit;
    }
    
    if($idLoteAtiv) {
        $idAmostraCancelada = 0;
    
        foreach($idLoteAtiv as $id) {
            $testesVinculados = FormalizacaoController::buscarTestesPorIdLoteAtiv($id);
    
            foreach($testesVinculados as $teste) {
    
                $moduloResultado = FluxoController::getDadosResultadoAmostra('resultado', 'idresultado', $teste['idobjeto'], 'CANCELADO', 'resultado', '', '')['modulo'];
                $moduloAmostra = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $teste['idamostra'], 'CANCELADO', 'amostra', '', '')['modulo'];
                
                // Cancelando amostra
                if($idAmostraCancelada != $teste['idamostra']) {
                    $idFluxoStatusAmostraCancelado = FluxoController::getIdFluxoStatus($moduloAmostra, $statusCancelado);
                    $inserindoFluxoStatusHist = FluxoController::inserirFluxoStatusHist($moduloAmostra, $teste['idamostra'], $idFluxoStatusAmostraCancelado, $statusCancelado);
                    
                    $alterandoStatusAmostra = AmostraController::alterarStatus($teste['idamostra'], $idFluxoStatusAmostraCancelado, $statusCancelado);
                    $InsObs = SQL::ini(FluxostatushistobsQuery::inserirFluxoStatusHistObs(), [
                        'idempresa' => $idEmpresa,
                        'idmodulo' => $teste['idamostra'],
                        'modulo' => $moduloAmostra,
                        'motivo' => $motivo,
                        'motivoobs' => $motivo,
                        'status' => $statusCancelado,
                        'idfluxostatus' => $idFluxoStatusAmostraCancelado,
                        'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
                        'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
                    ])::exec();
    
                    $idAmostraCancelada = $teste['idamostra'];
                }
    
                $idFluxoStatusAtual = $teste['idfluxostatus'];
                $idFluxoStatuResultadoCancelado = FluxoController::getIdFluxoStatus($moduloResultado, 'CANCELADO');
        
                $statustipoAntigo = PedidoEmLoteController::buscarStatustipo($moduloResultado, $idFluxoStatusAtual);
                $statustipoNovo = PedidoEmLoteController::buscarStatustipo($moduloResultado, $idFluxoStatuResultadoCancelado);
        
                $rowFluxonovo = FluxoController::getFluxoStatusHist($moduloResultado, 'idresultado', $teste['idobjeto'],$statustipoNovo['statustipo']);
                FluxoController::inserirFluxoStatusHist($moduloResultado, $teste['idobjeto'], $idFluxoStatuResultadoCancelado, 'CANCELADO');
                // Motivo historico hist resultado
                $InsObs = SQL::ini(FluxostatushistobsQuery::inserirFluxoStatusHistObs(), [
                    'idempresa' => $idEmpresa,
                    'idmodulo' => $teste['idobjeto'],
                    'modulo' => $moduloResultado,
                    'motivo' => $motivo,
                    'motivoobs' => $motivo,
                    'status' => $statusCancelado,
                    'idfluxostatus' => $idFluxoStatuResultadoCancelado,
                    'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
                    'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
                ])::exec();
        
                // FormalizacaoController::atualizarStatusLoteAtiv($teste['idloteativ'], 'CANCELADO', $idFluxoStatusNovo);
                
                $alterandoStatus = FluxoController::alterarStatus(
                    $moduloResultado,
                    'idresultado', 
                    $teste['idobjeto'],
                    $rowFluxonovo['idfluxostatushist'],
                    $rowFluxonovo['idfluxostatus'], 
                    $rowFluxonovo['statustipo'], 
                    null, 
                    0, 
                    $rowFluxonovo['idfluxostatus'], 
                    $rowFluxonovo['idfluxo'], 
                    $rowFluxonovo['ordem'], 
                    $rowFluxonovo['tipobotao']
                );
            }
        }   
    }

    // Atualizar status da OP para 'Perda em processo'
    if($atividadeNaoConforme) {
        $idFluxostatus =  FluxoController::getIdFluxoStatus($moduloFormalizacao, 'INUTILIZADO', $idPrProc);
        $statusFormalizacao = 'Perda em processo';
        $atualizandoStatusFormalzacao = FormalizacaoController::atualizarStatusFormalizacao($idFormalizacao, $statusFormalizacao, $idFluxostatus);
        $inserindoFluxoStatusHist = FluxoController::inserirFluxoStatusHist($moduloFormalizacao, $idFormalizacao, $idFluxoStatusOP, $status);
    
        $InsObs = SQL::ini(FluxostatushistobsQuery::inserirFluxoStatusHistObs(), [
            'idempresa' => $idEmpresa,
            'idmodulo' => $idFormalizacao,
            'modulo' => $moduloFormalizacao,
            'motivo' => $motivo,
            'motivoobs' => $motivo,
            'status' => $statusFormalizacao,
            'idfluxostatus' => $idFluxostatus,
            'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
            'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
        ])::exec();
    }

    // Esgotar lote
    $loteFracao = LoteController::buscarLoteFracaoPorIdloteEIdUnidade($idLote, $idUnidade);

    if($loteFracao) {

        $arrLoteCons = [
            'idlote' => $idLote,
            'idlotefracao' => $loteFracao['idlotefracao'], 
            'idempresa' => $idEmpresa,
            'idobjeto' => 'null', 
            'tipoobjeto' => '',
            'obs' => $motivo,
            'idtransacao' => SolmatController::buscarRandomico()['idtransacao'],  
            'idobjetoconsumoespec' => 'null',
            'tipoobjetoconsumoespec' => '', 
            'status' => 'ATIVO', 
            'qtdd' => $loteFracao['qtd'],
            'usuario' => $_SESSION["SESSAO"]["USUARIO"],
        ];

        $esgotandoLote = LoteController::inserirLoteCons($arrLoteCons);

        if(!$esgotandoLote) {
            echo json_encode([
                'error' => 'Erro ao esgotar lote',
                'success' => false
            ]);
            exit;
        }
    }

    echo json_encode([
        'mensagem' => 'Lotes cancelados',
        'success' => true
    ]);
}

?>