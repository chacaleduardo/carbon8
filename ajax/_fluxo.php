<?
require_once(__DIR__."/../inc/php/functions.php");
require_once(__DIR__."/../form/controllers/fluxo_controller.php");

$conf = FluxoController::mostrarBotao(
    FluxoController::validaModuloReal($_POST['_modulo']), 
    $_POST['_idobjeto']
);

$acao = $_POST['acao'];

switch($acao) {
    case 'verificarinicio':

        $info = FluxoController::verificarInicio(
            $conf['mod'], 
            $_POST['_primary'], 
            $_POST['_idobjeto']
        );

        if(is_array($info))
            cbSetPostHeader($info["resposta"], $info["formato"]);

        break;

    case 'carregarcadastrofluxo':

         $info = FluxoController::carregarCadastroFluxo(
            $conf['mod'], 
            $_POST['_primary'], 
            $_POST['_idobjeto'], 
            $_POST['_idempresa'], 
            $conf['mostrarBotao']
        );

        if(count($info) == 0 ) 
            cbSetPostHeader("0", "SEM STATUS");

        echo json_encode($info);
        break;

    case 'carregarfluxoobjeto':
        $info = FluxoController::carregarFluxoObjeto(
            $conf['mod'], 
            $_POST['_primary'], 
            $_POST['_idobjeto']
        );

        if(is_array($info))
            cbSetPostHeader($info["resposta"], $info["formato"]);

        echo json_encode($info['info']);
        break;

    case 'alterarstatus':
        $info = FluxoController::alterarStatus(
            $conf['mod'], 
            $_POST['_primary'], 
            $_POST['_idobjeto'], 
            $_POST['idfluxostatushist'], 
            $_POST['idstatusf'], 
            $_POST['statustipo'], 
            $_POST['idfluxostatuspessoa'], 
            $_POST['ocultar'], 
            $_POST['idfluxostatus'], 
            $_POST['idfluxo'], 
            $_POST['prioridade'], 
            $_POST['tipobotao'], 
            $_POST['idcarrimbo']
        );

        if(is_array($info)){
            $msg = "";
            $formato = $info["formato"];

            if($info["resposta"] == 0){
                $formato = 'alert';
                $msg = $info["formato"];
            }

            cbSetPostHeader($info["resposta"], $formato);
            echo $msg;
        }

        break;

    case 'restaurarfluxo':
       
        $info = FluxoController::restaurarFluxo(
            $conf['mod'], 
            $_POST['_primary'], 
            $_POST['_idobjeto'], 
            $_POST['status'], 
            $_POST['idfluxostatus'],
            $_POST['motivo'],
            $_POST['motivoobs'],
            $_POST['inativarconsumo']
        );

        if(is_array($info))
            cbSetPostHeader($info["resposta"], $info["formato"]);

        break;

    case 'listarestaurarfluxo':
        $info = FluxoController::listaRestaurarFluxo(
            $conf['mod'], 
            $_POST['_primary'],
            $_POST['_idobjeto']
        );

        if(is_array($info))
            cbSetPostHeader($info["resposta"], $info["formato"]);

        echo json_encode($info['info']);
        break;
        
    case 'getidfluxostatus':

        $info = FluxoController::getIdFluxoStatus(
            $conf['mod'], 
            $_POST['status'],
            $_POST['_idobjeto']
        );

        echo $info;
        break;
        
    case 'getidfluxostatusInativo':
        $info = FluxoController::getidfluxostatusInativo(
            $conf['mod'], 
            $_POST['status'],
            $_POST['_idobjeto']
        );

        echo $info;
        break;

    case 'getidfluxostatushist':
        $info = FluxoController::getIdFluxoStatusHist(
            $conf['mod'], 
            $_POST['_idobjeto']
        );

        echo $info;
        break;
    
    case 'carregaHistoricoRestaurar':
        $info = FluxoController::getHistoricoRestaurar(
            $conf['mod'], 
            $_POST['_idobjeto']
        );

        echo json_encode($info);
        break;
    
    case 'atualizarHistoricoRestaurar':
        $info = FluxoController::atualizarHistoricoRestaurar(
            $_POST['idfluxostatushistobs'], 
            $_POST['motivoobs']
        );

        echo $info == true ? ("OK") : ("");
        break;

    default:
        break;
}

?>