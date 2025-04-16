<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

// QUERY
require_once(__DIR__."/../form/querys/_iquery.php");
require_once(__DIR__."/../form/querys/tagreserva_query.php");
require_once(__DIR__."/../form/querys/device_query.php");
require_once(__DIR__."/../form/querys/tag_query.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/prodservformularotulo_controller.php");

$action = $_GET['action'] ?? $_POST['action'];

if($action)
{
    $params = $_GET['params'] ?? $_POST['params'];

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

function buscarPorChavePrimaria($id) {
    $prodservFormulaRotulo = ProdservFormulaRotuloController::buscarPorChavePrimaria($id);

    if(!$prodservFormulaRotulo) {
        echo json_encode([
            'error' => 'Rótulo não encontrado!'
        ]);

        return;
    }

    echo json_encode($prodservFormulaRotulo);
} 

?>