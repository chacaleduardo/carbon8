<?
require_once("../inc/php/functions.php");
require_once("../api/nf/index.php");
require_once("../form/controllers/nfvolume_controller.php");
require_once(__DIR__."/../form/controllers/fluxo_controller.php");

function errorMessage( $msg = "" ){
    return '{"error":"'.$msg.'"}';
}

$jwt = validaTokenReduzido();

cbSetPostHeader("0","error");

if( $jwt["sucesso"] !== true ){
    echo errorMessage("Falha na autenticação");
	die;
}

if( empty($_POST['idnf']) || empty($_POST['volumeinfo']) ){
	echo errorMessage("Dados do volume estão incompletos");
	die;
}

try {
    $idnf = $_POST['idnf'];
    $volumeInfo = json_decode($_POST['volumeinfo'], true);
    unset($_POST);

    $nfvolume = NfVolumeController::buscarNfVolumePendente($idnf);

    if(count($nfvolume) == 0){
        echo errorMessage("Nenhum volume pendente encontrado para o Pedido [".$idnf."]");
        die;
    }

    if( count($nfvolume) != $volumeInfo['tvol'] || count($nfvolume) != count($volumeInfo['arrVols']) ){
        echo errorMessage("Número de volumes do Pedido [".$idnf."] está inconsistente");
        die;
    }

    $idNfVolume = array();
    foreach( $nfvolume as $k => $rw ){
        $idNfVolume[] = $rw["idnfvolume"];
        if(!in_array($rw["volume"], $volumeInfo['arrVols'])){
            echo errorMessage("Volume [".$rw["volume"]." de ".$volumeInfo['tvol']."] não encontrado para o Pedido [".$idnf."]");
            die;
        }
    }

    $fluxoNf = NfVolumeController::verificarStatusEnviarNf($idnf);

    if( !$fluxoNf ){
        echo errorMessage("O Pedido [".$idnf."] não está no status de 'Expedição Iniciada'");
        die;
    }

    $idempresa = traduzid( 'nf', 'idnf', 'idempresa', $idnf );

    cbSetPostHeader("cnf","error");

    cnf::$idempresa = $idempresa;
    cnf::atualizafat( $idnf );

    $comissao = traduzid( 'nf', 'idnf', 'comissao', $idnf );

    if( $comissao == 'Y' ){//inserir o item da comissao oculto na nota
        $gerarComissao = NfVolumeController::gerarComissao($idnf, cnf::$idempresa);

        if(!$gerarComissao){
            echo errorMessage("Não foi possível gerar a comissão do Pedido [".$idnf."]");
            die;
        }

        cnf::agrupaCP();
    }

    cbSetPostHeader("0","error");

    $fluxoEnviadoNf = NfVolumeController::buscarStatusTipoEnviadoFluxo($fluxoNf['idfluxo']);

    if( !$fluxoEnviadoNf ){
        echo errorMessage("Não foi encontrado o status 'ENVIADO' no fluxo do Pedido [".$idnf."]");
        die;
    }

    cbSetPostHeader("fluxo","error");

    FluxoController::alterarStatus('pedido', 'idnf', $idnf, $fluxoNf['idfluxostatushist'], $fluxoEnviadoNf['idfluxostatus'], 'ENVIADO', NULL, $fluxoNf['ocultar'], $fluxoNf['idfluxostatus'], $fluxoNf['idfluxo'], $fluxoNf['ordem']);
    cbSetPostHeader("0","error");

    $atualizarStatusNfVolume = NfVolumeController::atualizarStatusNfVolume(implode(",", $idNfVolume), $idnf);

    if( !$atualizarStatusNfVolume ){
        echo errorMessage("Não foi possível atualizar o status do NF Volume do Pedido [".$idnf."]");
        die;
    }

    echo '{"success":"Volumes do Pedido ['.$idnf.'] enviados com sucesso"}';

} catch (\Throwable $th) {
    echo errorMessage("Falha ao ler conteúdo enviado");
}
?>