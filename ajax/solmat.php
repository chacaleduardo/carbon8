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
require_once(__DIR__."/../form/querys/lotecons_query.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/solmat_controller.php");
require_once(__DIR__."/../form/controllers/lote_controller.php");

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

/**
 * Buscar fração consumida
 * @param idlotecons
 * @param status
 */
function atualizarStatusLoteCons(string $dados)
{
    list($idLoteCons, $status, $idunidade) = explode(',', $dados);
    $idloteorigem = traduzid('lotecons','idlotecons','idlote',$idLoteCons);
    $idlotefracaoori = traduzid('lotecons','idlotecons','idlotefracao',$idLoteCons);
    $qtdpedida = traduzid('lotecons','idlotecons','qtdd',$idLoteCons);
    $consomeun = traduzid('unidade', 'idunidade', 'consomeun', $idunidade);  
    $idtransacao = traduzid('lotecons','idlotecons','idtransacao',$idLoteCons);
    $listarLoteFracao = SolmatController::buscarLotefracaoPorIdloteIdunidade($idloteorigem, $idunidade);
    //adiciona um credItio na destino
    $arrayInsertLoteCons = [
        "idempresa" => cb::idempresa(),
        "idlote" => $idloteorigem,
        "idlotefracao" => $listarLoteFracao['idlotefracao'],
        "idobjeto" => $idlotefracaoori,
        "tipoobjeto" => 'lotefracao',
        "obs" => 'crédito via solicitação de materiais.',
        "idtransacao" => $idtransacao,
        "idobjetoconsumoespec" => 'null',
        "tipoobjetoconsumoespec" => '',
        "qtdd" => 0,
        "qtdc" => str_replace(",", ".", $qtdpedida),
        "usuario" => $_SESSION["SESSAO"]["USUARIO"],
        "status" => $status
    ];
    SolmatController::inserirLoteCons($arrayInsertLoteCons);
    //TODO mover para quando bipar
    $arrayInsertLoteCons = array();
    
    $rowconv = SolmatController::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idlotefracaoori);        
    
    //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
    if(($rowconv["consometransf"] == 'Y' || $consomeun == 'Y') && $rowconv["imobilizado"] != 'Y')
    {
        $arrayInsertLoteCons = [
            "idempresa" => cb::idempresa(),
            "idlote" => $idloteorigem,
            "idlotefracao" => $listarLoteFracao['idlotefracao'],
            "idobjeto" => $idlotefracaoori,
            "tipoobjeto" => 'lotefracao',
            "obs" => 'crédito via solicitação de materiais.',
            "idtransacao" => $idtransacao,
            "qtdd" => str_replace(",", ".", $qtdpedida),
            "idobjetoconsumoespec" => 'null',
            "tipoobjetoconsumoespec" => '',
            "qtdc" => 0,
            "usuario" => $_SESSION["SESSAO"]["USUARIO"],
            "status" => $status
        ];
        SolmatController::inserirLoteCons($arrayInsertLoteCons);
    }

    $atualizandoStatusLoteCons = LoteController::atualizarStatusLoteCons($idLoteCons, $status);

    if(!$atualizandoStatusLoteCons)
    {
        echo json_encode(['error' => 'Errou ao atualizar lotecons']);
    }
}
?>