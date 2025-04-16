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
require_once(__DIR__."/../form/controllers/tag_controller.php");

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

function cancelarLocacao($idTagReserva)
{
   TagController::cancelarLocacaoPorIdTagReserva($idTagReserva);
}

function buscarLotePorIdTagDimEIdUnidade($dados)
{
    list($idUnidade, $idTagDim ) = explode('|', $dados);
    $idTagDim = explode(',', $idTagDim);
    $idTagDim = array_unique($idTagDim);
    $idTagDim = array_filter($idTagDim);
    $idTagDim = implode(",",$idTagDim);

    $idTagDim = str_replace('|', ',', $idTagDim);

    echo json_encode(TagController::buscarLoteLocalizacaoEFracaoPorIdTagDimEIdUnidade($idTagDim, $idUnidade));
}

function verificarSePossuiLoteFracaoPorIdTagDimEIdUnidade($dados, $echo = true)
{
    list($idTaDim, $idUnidade) = explode(',', $dados);
    $loteFracao = TagController::verificarSePossuiLoteFracaoPorIdTagDimEIdUnidade(str_replace('|', ',', $idTaDim), $idUnidade);

    if(!$echo)
        return $loteFracao;     
    
    if(!$idUnidade)
    {
        echo json_encode(['error' => 'Unidade não vinculada!']);
        return;
    }

    echo json_encode($loteFracao);
}

function verificarSePossuiLoteFracaoPorIdTagDimEIdEmpresaEIdUnidade($dados, $echo = true)
{
    list($idTaDim, $idUnidade, $idEmpresa) = explode(',', $dados);
    $loteFracao = TagController::verificarSePossuiLoteFracaoPorIdTagDimEIdEmpresaEIdUnidade(str_replace('|', ',', $idTaDim), $idUnidade, $idEmpresa);

    if(!$echo)
        return $loteFracao;     
    
    if(!$idEmpresa)
    {
        echo json_encode(['error' => 'Empresa não informada!']);
        return;
    }

    echo json_encode($loteFracao);
}


function buscarPrateleira($idTag)
{
    echo json_encode(TagController::buscarPorChavePrimaria($idTag));
}

function transferirLote($dados)
{
    list($idTagDimOrigem, $idTag, $idUnidade, $idEmpresa, $coluna, $linha) = explode(',', $dados);

    if(!$idTagDimOrigem)
    {
        echo json_encode(['error' => "idtagdimorigem inválido: [$idTagDimOrigem]"]);
        return;
    }

    if(!$coluna)
    {
        echo json_encode(['error' => "Coluna inválida: [$coluna]"]);
        return;
    }

    if(!$linha)
    {
        echo json_encode(['error' => "Linha inválida: [$linha]"]);
        return;
    }

    // if(!$caixa)
    // {
    //     echo json_encode(['error' => "Caixa inválida: [$caixa]"]);
    //     return;
    // }

    $idTagDimDestino = array_map(function($item) {
       return $item['idtagdim'];
    }, TagController::buscarTagDimPorIdTag($idTag, $coluna, $linha));

    if(count($idTagDimDestino) > 1)
    {
        echo json_encode(['error' => 'Multiplas localizações encontradas!']);
        return;
    }

    echo json_encode(TagController::transferirLote($idTagDimOrigem, $idTagDimDestino[0], $idUnidade, $idEmpresa));
}

function transferirLoteMultiplo($dados)
{
    list($idTagDimOrigem, $idTag, $idEmpresa, $idUnidade, $coluna, $linha) = explode(',', $dados);

    if(!$idTagDimOrigem)
    {
        echo json_encode(['error' => "idtagdimorigem inválido: [$idTagDimOrigem]"]);
        return;
    }

    if(!$coluna)
    {
        echo json_encode(['error' => "Coluna inválida: [$coluna]"]);
        return;
    }

    if(!$linha)
    {
        echo json_encode(['error' => "Linha inválida: [$linha]"]);
        return;
    }

    $linhasIdTagDimOrigem = explode('|', $idTagDimOrigem);
    $prateleiraOrigem = TagController::buscarTagDimPorIdTagDim(implode(',', $linhasIdTagDimOrigem));
    $linhasPrateleiraOrigem = array_map(function($item) {
        return $item['linha'];
    }, $prateleiraOrigem);
    $linhasPrateleiraDestino = TagController::buscarTagDimPorIdTag($idTag, $coluna, $linha);

    // if(count($caixasIdTagDimOrigem) > $linhasPrateleiraDestino[0]['maxcaixa'])
    // {
    //     echo json_encode(['error' => "Quantidade de caixas excede a prateleira de destino: [{$linhasPrateleiraDestino[0]['maxcaixa']}]"]);
    //     return;
    // }

    $linhasIdTagDimDestino = array_map(function($item) use($linhasPrateleiraOrigem){
        if(in_array($item['linha'], $linhasPrateleiraOrigem))
            return [
                'linha' => $item['linha'],
                'idlinha' => $item['idtagdim']
            ];
    }, $linhasPrateleiraDestino);
    
    $linhasIdTagDimAlterados = [];

    foreach($linhasIdTagDimDestino as $key => $linha)   
    {
        if($linha)
        {
            $caixaOrigem = array_filter($prateleiraOrigem, function($item) use($linha) {
                return $item['linha'] == $linha['linha'];
            });

            if($caixaOrigem)
                array_push($linhasIdTagDimAlterados, TagController::transferirLote($caixaOrigem[array_keys($caixaOrigem)[0]]['idtagdim'], $linha['idlinha'], $idUnidade, $idEmpresa));
        }
    }

    echo json_encode($linhasIdTagDimAlterados);
}

function buscarTagPorIdTag($idTag) {
    if(!$idTag) {
        json_encode([
            'error' => 'Idtag não informado'
        ]);

        return;
    }

    echo json_encode(TagController::buscarTagPorId($idTag, cb::idempresa()));
}

function iniciarViagem($dados) {
    list($idTag, $kmInicial, $idEmpresa, $usuario) = $dados['param'];

    if(!$idTag) {
        echo json_encode([
            'error' => 'Idtag não informado'
        ]);

        return;
    }

    $iniciarViagem = TagController::iniciarViagem($idTag, $kmInicial, $idEmpresa, $usuario);

    echo $iniciarViagem;
}

function finalizarViagem($idTag) {
    if(!$idTag) {
        echo json_encode([
            'error' => 'Idtag não informado'
        ]);

        return;
    }

    $finalizarViagem = TagController::finalizarViagem($idTag);

    echo $finalizarViagem;
}

function buscarPrateleiras($dados) {
    list($idEmpresa, $idUnidade) = explode(',', $dados);

    if(!$idEmpresa) {
        $idEmpresa = cb::idempresa();
    }

    if(!$idUnidade) {
        $idUnidade = 2;
    }

    echo json_encode(TagController::buscarPrateleiras($idEmpresa));
}

function buscarTagDimPorIDtag($idTag) {
    if(!$idTag) {
        echo json_encode(['error' => 'Id Tag não informao!']);

        return false;
    }

    $arrRetorno = [];
    $tagDim  = TagController::buscarTagDimPorIdTag($idTag);

    foreach($tagDim as $item) {
        $arrRetorno['colunas'][$item['coluna']]['label'] = $item['coluna'];
        $arrRetorno['colunas'][$item['coluna']]['linhas'][$item['linha']]['label'] = $item['linha'];
        $arrRetorno['colunas'][$item['coluna']]['linhas'][$item['linha']]['caixas'][$item['caixa']]['idtagdim'] = $item['idtagdim'];
        $arrRetorno['colunas'][$item['coluna']]['linhas'][$item['linha']]['caixas'][$item['caixa']]['label'] = $item['caixa'];
    }

    echo json_encode($arrRetorno);
}