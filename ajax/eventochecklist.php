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
require_once(__DIR__."/../form/querys/eventochecklist_query.php");
require_once(__DIR__."/../form/querys/eventochecklistitem_query.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/evento_controller.php");

$action = $_POST['action'] ?? $_POST['action'];

if($action)
{
    $params = $_POST['params'];

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

function inserirEventoChecklistItem(array $params)
{
    echo json_encode(EventoController::inserirEventoChecklistItem($params['dados']));
}

function removerEventoChecklistItem($id)
{
    EventoController::removerEventoChecklistItemPorChavePrimaria($id);
}

function atualizarEventoChecklistItem(array $params)
{
    if(isset($params['dados']['titulo']))
    {
        return EventoController::atualizarTituloEventoChecklistItem($params['dados']);
    }

    EventoController::atualizarCheckedEventoChecklistItem($params['dados']);
}

?>