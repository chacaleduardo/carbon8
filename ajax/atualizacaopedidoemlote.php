<?
require_once("../inc/php/validaacesso.php");
include_once("../form/controllers/fluxo_controller.php");
include_once("../form/controllers/pedidoemlote_controller.php");
include_once("../form/controllers/pedido_controller.php");
$arrErrorMsg = array();

foreach($_POST['data'] as $k => $item){

    if($item['statusnovo']){
        $statustipoNovo = PedidoEmLoteController::buscarStatustipo('pedido',$item['statusnovo']);
        $statustipoAntigo = PedidoEmLoteController::buscarStatustipo('pedido',$item['idstatusant']);
        $rowFluxonovo = FluxoController::getFluxoStatusHist('pedido', 'idnf', $item['idnf'],$statustipoNovo['statustipo']);
        FluxoController::alterarStatus('pedido', 'idnf', $item['idnf'], $rowFluxonovo['idfluxostatushist'], $rowFluxonovo['idfluxostatus'], $rowFluxonovo['statustipo'], null, 0, $rowFluxonovo['idfluxostatus'], $rowFluxonovo['idfluxo'], $rowFluxonovo['ordem'], $rowFluxonovo['tipobotao']);
    }
    if($item['transportadora']){
        $atualizatransp = PedidoEmLoteController::atualizarTransportadoraPedido($item['idnf'],$item['transportadora']);
    }
    if($item['statusnovo']){

        $rowped = PedidoEmLoteController::buscarPedidoAtualizado($item['idnf']);
        
        // Ao retirar uma nota do status contigencia liberar o envio novamente
        if($item['statusant']=="CONTINGENCIA" and !empty($rowped['idnf']) AND $rowped['status']!= "CONTINGENCIA" AND !empty($rowped['status']) ){
            PedidoEmLoteController::atualizarStatusNfePedido($item['idnf'],"PENDENTE");
        }
    
        // so vai para o status faturar apos consumir a reserva que esta no almoxarifado
        if($rowped['status'] == 'FATURAR'){
    
            $res = PedidoController::buscarReservaNfitemPorIdnf($rowped['idnf']);
            $qtdres=count($res);
            if($qtdres>0){
                foreach($res as $row){   
                    if(empty($row['idlotefracao'])){
                        $arrErrorMsg[$item["idnf"]] = "Não encontrado o produto reservado na Logística, Verificar estoque reservado.";
                        $rowFluxoAntigo = FluxoController::getFluxoStatusHist('pedido', 'idnf', $item['idnf'],$item['idstatusant']);
                        FluxoController::alterarStatus('pedido', 'idnf', $item['idnf'], $rowFluxoAntigo['idfluxostatushist'], $rowFluxoAntigo['idfluxostatus'], $rowFluxoAntigo['statustipo'], null, 0, $rowFluxoAntigo['idfluxostatus'], $rowFluxoAntigo['idfluxo'], $rowFluxoAntigo['ordem'], $rowFluxoAntigo['tipobotao']);
                    }else{
                        $inslotecons = new Insert();
                        $inslotecons->setTable("lotecons");
                        $inslotecons->idlote=$row['idlote'];
                        $inslotecons->idlotefracao=$row['idlotefracao'];
                        $inslotecons->idempresa=$_idempresa;
                        $inslotecons->qtdd=$row['qtd'];
                        $inslotecons->idobjeto=$row['idnfitem'];
                        $inslotecons->tipoobjeto='nfitem';      
                        $_idlotecons=$inslotecons->save();
                    }
                
                    PedidoController::liberarLotereservaPorId($row['idlotereserva']);          
            
                }        
            }
        }
        //Verifica se tem algum item marcado no pedido  quando o status for SOLICITADO
        if(($rowped['status'] == "SOLICITADO" || $rowped['status'] == "PEDIDO" || $rowped['status'] == "PRODUCAO" || $rowped['status'] == "EXPEDICAO" || $rowped['status'] == "FATURAR") && $item['statusant'] != $rowped['status']) {    
            $row = PedidoController::buscarNfitemDanfe($item['idnf']);
            if($row['contador'] == 0){
                $arrErrorMsg[$item["idnf"]] = "Selecionar ao menos um item para NFE.";
                $rowFluxoAntigo = FluxoController::getFluxoStatusHist('pedido', 'idnf', $item['idnf'],$item['idstatusant']);
                FluxoController::alterarStatus('pedido', 'idnf', $item['idnf'], $rowFluxoAntigo['idfluxostatushist'], $rowFluxoAntigo['idfluxostatus'], $rowFluxoAntigo['statustipo'], null, 0, $rowFluxoAntigo['idfluxostatus'], $rowFluxoAntigo['idfluxo'], $rowFluxoAntigo['ordem'], $rowFluxoAntigo['tipobotao']);
                // die("");
            }
        }
    }

}
die(json_encode($arrErrorMsg));