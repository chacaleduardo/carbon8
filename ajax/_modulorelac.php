<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

// CONTROLLERS
require_once(__DIR__."/../form/controllers/_modulorelac_controller.php");
require_once(__DIR__."/../form/controllers/_mtotabcol_controller.php");
require_once(__DIR__."/../form/controllers/_rep_controller.php");

$action = $_GET['action'];

if($action)
{
    $params = $_GET['params'];

    if(!isset($params['typeParam']))
    {
        $params['typeParam'] = false;
    }

    if(is_array($params) && ($params['typeParam'] != 'array'))
    {
        return $action(implode(',', $params));
    }

    return $action($params);
}

function buscarRegistroPorTabDeColDeTabParaColPara(array $params)
{
    [$idRep, $tabDe, $colDe, $tabPara, $colPara, $pkVal] = $params['dados'];
    
    $chavePrimariaTabOrigem = _MtoTabColController::buscarChavePrimariaPorTabela($tabDe);
    $chavePrimariaTabDestino = _MtoTabColController::buscarChavePrimariaPorTabela($tabPara);

    $colunaRep = _RepController::buscarRepColPorIdRepTabEColuna($idRep, $tabPara, $chavePrimariaTabDestino);

    $registros = [];
    $registros['dados'] = _ModuloRelacController::buscarRegistroPorTabDeColDeTabParaColPara($tabDe, $colDe, $tabPara, $colPara, $pkVal);

    $registros['colunasPrimarias'] = [
        'tabelaOrigem' => $chavePrimariaTabOrigem,
        'tabelaDestino' => $chavePrimariaTabDestino,
    ];

    $registros['colunaRep'] = $colunaRep;

    echo json_encode($registros);
}

?>