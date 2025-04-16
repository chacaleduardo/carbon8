<?
require_once("../inc/php/functions.php");
require_once("../model/prodserv.php");

$tipo = $_GET['tipo'];
$idprodserv = $_GET['idprodserv'];

$jwt = validaTokenReduzido();

if ($jwt["sucesso"] !== true) {
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

// CONTROLLERS
require_once("../form/controllers/farmacovigilancia_controller.php");
require_once("../form/controllers/lote_controller.php");
require_once("../form/controllers/fluxo_controller.php");

$action = $_GET['action'] ?? $_POST['action'];
$idEmpresa = $_GET['_idempresa'];

if ($action) {
    $params = $_GET['params'] ?? $_POST['params'];

    if (!isset($params['typeParam'])) {
        $params['typeParam'] = false;
    }

    if (is_array($params) && ($params['typeParam'] != 'array')) {
        return $action(implode(',', $params));
    }

    return $action($params);
}

function burscarLotePorProdserv($idprodserv)
{
    $idprodserv = $_GET['params'][0];
    echo FarmacovigilanciaController::burscarLotePorProdserv($idprodserv);
}

function buscarLotesDisponivesPorIdProdserv($params)
{
    global $idEmpresa;
    list($idprodserv, $idunidade) = explode(',', $params);

    $lotes = LoteController::buscarLotesDisponivesPorIdProdserv($idprodserv, $idunidade);
    $prodservclass = new PRODSERV();

    $arrLotes = $lotes;

    foreach($lotes as $key => $lote) {
        $idunidadepadrao = getUnidadePadraoModulo($lote["modulo"], $idEmpresa);
        $loteFracao = LoteController::buscarLoteFracaoPorIdloteEIdUnidade($lote['idlote'], $idunidadepadrao);

        if (
            strpos(strtolower($loteFracao['qtd_exp']), "d")
            or strpos(strtolower($loteFracao['qtd_exp']), "e")
        ) {
            $valorEstoque = recuperaExpoente(tratanumero($loteFracao["qtd"]), $loteFracao['qtd_exp']);
            $qtdEstoque = $valorEstoque;

            $qtdEstoque = $lote['estoque'];
            $arrLotes[$key]['diluicao'] = $lote['estoque'];
        } else {
            $qtdEstoque = $prodservclass->getEstoqueLote($loteFracao['idlotefracao']);
            if (!$qtdEstoque) $qtdEstoque = 0;
            // $stvalor = number_format(tratanumero($qtdEstoque), 2, ',', '.') . ' - ' . $lote['unpadrao'];
            // $stqtdproduzida = number_format(tratanumero($lote['qtdprod'] * $lote['valconvori']), 2, ',', '.') . ' - ' . $lote['unpadrao'];
        }

        $arrLotes[$key]['estoque'] = $qtdEstoque;
    }

    echo json_encode($arrLotes);
}

function buscarLotesVencidosOuProximos($params)
{
    list($dataDeVencimento, $idUnidade) = explode(',', $params);
    $idUnidade = str_replace('|', ',', $idUnidade);

    if ($dataDeVencimento == null) {
        echo json_encode(['error' => 'Informa a data de vencimento!']);
        return false;
    }

    if (!$idUnidade) {
        echo json_encode(['error' => 'Informa a unidade.']);
        return false;
    }

    $produtos = ProdServController::buscarLotesVencidosOuProximos($dataDeVencimento, cb::idempresa(), $idUnidade);

    echo json_encode($produtos);
}

function buscarLoteIdPorFormalizacao($parametros)
{
    list($idFormalizacao, $idEmpresa) = explode(',',$parametros);

    if(!$idEmpresa) {
        echo json_encode([
            'error' => 'ID empresa não informado'
        ]);

        return false;
    }

    if (!$idFormalizacao) {
        echo json_encode(['error' => 'ID lote não informado.']);
        return false;
    }

    $loteAtiv = [];
    $loteInfo = buscarQuantidadeDisponivelLote($idFormalizacao, $idEmpresa);
    $lote = $loteInfo['lote'];

    if(!$lote) {
        echo json_encode(['error' => 'Lote não encontrado. Verifique a unidade configurada no módulo.']);
        return false;
    }

    $quantidadeDisponivelLote = $loteInfo['valor'];
    $quantidadeDisponivelLoteFormatada = $loteInfo['valorFormatado'];
    
    $lote['qtddisponivel'] = $quantidadeDisponivelLote;
    $lote['qtddisponivelformatada'] = $quantidadeDisponivelLoteFormatada;
    if ($lote) {
        $loteAtiv = LoteController::buscarLoteAtivAtual($lote['idlote']);
        // if ($loteAtiv['logistica '] != 'Y') {
        //     echo json_encode(['error' => 'Atividade é diferente logistica.']);
        //     return false;
        // }
    }

    echo json_encode([
        'lote' => $lote,
        'statusLoteAtiv' => $loteAtiv['status']
    ]);
}

function buscarQuantidadeDisponivelLote($idFormalizacao, $idEmpresa = 0) {
    if(!$idEmpresa) $idEmpresa = traduzid('formalizacao', 'idformalizacao', 'idempresa', $idFormalizacao);;

    //Chama a Classe prodserv
    $prodservclass = new PRODSERV();
    $idunidadepadrao = getUnidadePadraoModulo('camarafria', $idEmpresa);
    $lote  = LoteController::buscarLoteIdPorFormalizacao($idFormalizacao, $idunidadepadrao);

    $unestoque = $prodservclass->getUnEstoque($lote['idprodserv'], $idunidadepadrao, $lote['converteest'], $lote['unpadrao'], $lote['unlote']);
    // Dados do lotefracao
    if (
        strpos(strtolower($lote['qtd_exp']), "d")
        or strpos(strtolower($lote['qtd_exp']), "e")
    ) {
        $vlst = recuperaExpoente(tratanumero($lote["qtd"]), $lote['qtd_exp']);
        $stvalor = $vlst . ' - ' . $unestoque;
    } else {
        $qtdfr = $prodservclass->getEstoqueLote($lote['idlotefracao']);
        if ($qtdfr < 0) {
            $qtdfr = 0;
        }
        $stvalor = number_format(tratanumero($qtdfr), 2, ',', '.') . ' - ' . $unestoque;
    }

    return [
        'lote' => $lote,
        'valorFormatado' => $stvalor,
        'valor' => $qtdfr
    ];
}

/**
 * TODO: Adicionar vinculo com formalizacao e verificar se a atividade atual é logistica
 */
function devolverLoteProducao($idLote)
{
    if (!$idLote) {
        echo json_encode(['error' => 'ID lote não informado.']);
        return false;
    }

    $loteConsumo = LoteController::buscarUltimoConsumoPorIdlote($idLote);

    if (!$loteConsumo) {
        echo json_encode(['error' => 'ID lote não informado.']);
        return false;
    }

    echo json_encode(LoteController::inativarLoteCons($loteConsumo['idlotecons']));
}

function atualizarStatusAtividade($dados)
{
    list($idLote, $status, $bloquearStatus) = explode(',', $dados);

    if (!$idLote) {
        echo json_encode(['error' => 'ID lote não informado.']);
        return false;
    }

    if (!$status) {
        echo json_encode(['error' => 'Status não informado.']);
        return false;
    }

    if (!$bloquearStatus) {
        echo json_encode(['error' => 'Bloqueio de status não informado.']);
        return false;
    }

    $loteAtiv = LoteController::buscarLoteAtivAtual($idLote);
    $formalizacao = FormalizacaoController::buscarFormalizacaoPorIdLote($idLote);

    if (!$loteAtiv) {
        echo json_encode(['error' => 'Atividade lógistica não encontrada.']);
        return false;
    }

    // if ($loteAtiv['logistica'] != 'Y') {
    //     echo json_encode(['error' => 'Atividade é diferente logistica.']);
    //     return false;
    // }

    $verificaSeExisteVinculoSala = LoteController::buscarSalaVinculadaAoLoteAtiv($idLote, $loteAtiv['idloteativ']);
    $prodserv = LoteController::buscarProdservPorIdLote($idLote);

    if($status == 'CONCLUIDO') FluxoController::alterarStatusFormalizacao('formalizacao', $formalizacao['idformalizacao'], $loteAtiv['idloteativ']);

    if (!$verificaSeExisteVinculoSala) {
        echo json_encode(['error' => 'Nenhum sala vínculada à atividade.']);
        return false;
    }


    echo json_encode([
        'atualizandoAtividade' => LoteController::atualizarStatusAtividade($loteAtiv['idloteativ'], $status, $bloquearStatus),
        'prodserv' => $prodserv
    ]);
}


function reterLote($dados)
{
    list($idLote, $idTagDim, $qtd, $qtdAlocacao) = explode(',', $dados);

    $idEmpresa = traduzid('lote', 'idlote', 'idempresa', $idLote);

    if (!$idEmpresa) {
        echo json_encode(['error' => 'Id da Empresa não informado.']);
        return false;
    }

    // Pegar unidade producao
    $idUnidadeLogistica = getUnidadePadraoModulo('camarafria', $idEmpresa);

    if (!$idTagDim) {
        echo json_encode(['error' => 'Id da Posição da prateleira não informada.']);
        return false;
    }

    if (!$qtd) {
        echo json_encode(['error' => 'Quantidade a ser retida não informada.']);
        return false;
    }

    if (!$idUnidadeLogistica) {
        echo json_encode(['error' => 'Unidade logística não encontrada para empresa atual.']);
        return false;
    }

    // Buscar lote fracao da logistica
    $loteFracaoLogistica = LoteController::buscarLoteFracaoPorIdloteEIdUnidade($idLote, $idUnidadeLogistica);

    // Pegar unidade retem
    $unidadeRetem = UnidadeController::buscarUnidadePorIdtipoIdempresa(11, $idEmpresa);

    // Verificar se fracao já existe
    $loteFracaoRetem = LoteController::buscarLoteFracaoPorIdloteEIdUnidade($idLote, $unidadeRetem['idunidade']);
    $idLoteFracaoRetem = $loteFracaoRetem['idlotefracao'];

    /**
     * Quando a alocacao for para venda
     */
    if ($qtdAlocacao) {
        if (floatval($qtd) > floatval($loteFracaoLogistica['qtd'])) {
            echo json_encode(['error' => 'Quantidade excede disponível no lote.']);
            return false;
        }

        $idLoteFracaoMov = LoteController::reterLote($idTagDim, $idEmpresa, $qtd, 'DISPONIVEL', $loteFracaoLogistica['idlotefracao']);

        if (!$idLoteFracaoMov) {
            echo json_encode(['error' => 'Ocorreu um erro ao fracionar o lote.']);
            return false;
        }

        $loteFracaoLocalizacao = LoteController::buscarLoteFracaoMovPorIdLoteFracao($loteFracaoLogistica['idlotefracao'], $idEmpresa, $idLoteFracaoRetem);

        /**
         * Verificar se total alocado é o mesmo do lote
         */
        $totalProdutosAlocado  = array_reduce($loteFracaoLocalizacao, function ($carry, $item) {
            return $carry + ($item['retem'] != 'Y' ? $item['qtd'] : 0);
        }, 0);

        echo json_encode([
            'qtdAtingida' => $totalProdutosAlocado >= $loteFracaoLogistica['qtd'],
            'loteFracaoMov' => $loteFracaoLocalizacao,
            'quantidadeDisponivelLote' => floatval($loteFracaoLogistica['qtd']) - $totalProdutosAlocado
        ]);

        return true;
    }

    // Criar fracao na unidade retem
    /**
     * TODO: Validar se quantidade excede a disponivel
     */
    $idTransacao = SolTagController::buscarIdTransacao();

    $dadosLoteFracao = [
        'idempresa' => $idEmpresa,
        'idunidade' => $unidadeRetem['idunidade'],
        'qtd' => $qtd,
        'qtdini' => $qtd,
        'idlote' => $idLote,
        'idtransacao' => $idTransacao,
        'idlotefracaoorigem' => $loteFracaoLogistica['idlotefracao'],
        'usuario' => $_SESSION['SESSAO']['USUARIO'],
    ];

    if (!$idLoteFracaoRetem) $idLoteFracaoRetem = LoteController::inserirLoteFracao($dadosLoteFracao);

    // Debitar consumo do lotefracao da producao
    // Verificar se ja existe consumo no retem para esta fracao
    $loteConsumoRetem = LoteController::buscarConsumoLoteconsPorIdLoteEIdLoteFracao($idLoteFracaoRetem, 'lotefracao', $loteFracaoLogistica['idlotefracao'], $idLote, 'INATIVO');
    $idLoteCons = $loteConsumoRetem['dados']['id'];

    if (!$idLoteCons) $idLoteCons = LoteController::consumirFracao($idLote, $loteFracaoLogistica['idlotefracao'], $qtd,$idLoteFracaoRetem, 'lotefracao', $idTransacao, $idEmpresa, 'Retem');

    if (!$idLoteCons) {
        echo json_encode(['error' => 'Ocorreu um erro ao consumir fração da logística.']);
        return false;
    }

    if (!$idLoteFracaoRetem) {
        echo json_encode(['error' => 'Ocorreu um erro ao gerar fração do lote no retem.']);
        return false;
    }

    // Adicionar lotes como retem na lotefracaomov
    $loteFracaoMov = LoteController::buscarLoteMovPorIdLoteFracaoEIdTagDim($idLoteFracaoRetem, $idTagDim);
    $idLoteFracaoMov = $loteFracaoMov['idlotefracaomov'];

    if (!$idLoteFracaoMov) $idLoteFracaoMov = LoteController::reterLote($idTagDim, $idEmpresa, $qtd, 'DISPONIVEL', $idLoteFracaoRetem);

    if (!$idLoteFracaoMov) {
        echo json_encode(['error' => 'Ocorreu um erro ao fracionar o lote.']);
        return false;
    }

    $loteFracaoLocalizacao = LoteController::buscarLoteFracaoMovPorIdLoteFracao($idLoteFracaoRetem, $idEmpresa, $loteFracaoLogistica['idlotefracao']);

    /**
     * Verificar se total alocado é o mesmo do lote
     */
    $totalProdutosAlocado  = array_reduce($loteFracaoLocalizacao, function ($carry, $item) {
        return $carry + ($item['retem'] != 'Y' ? $item['qtd'] : 0);
    }, 0);

    $formalizacao = FormalizacaoController::buscarFormalizacaoPorIdLote($idLote);
    $loteInfo = buscarQuantidadeDisponivelLote($formalizacao['idformalizacao']);

    echo json_encode([
        'qtdAtingida' => $totalProdutosAlocado >= intval($loteFracaoLogistica['qtd'] ?? 0),
        'loteFracaoMov' => $loteFracaoLocalizacao,
        'quantidadeDisponivelLote' =>  floatval($loteInfo['lote']['qtd']) - $totalProdutosAlocado,
        'quantidadeDisponivelLoteFormatada' => $loteInfo['valorFormatado']
    ]);
}

function verificarSeExisteLotePosicao($idTagDim)
{
    /**
     * Verificar se posicao escaneada já possui lote
     */
    $loteFracaoMovExistente = LoteController::buscarLoteFracaoMovPorIdTagDim($idTagDim);

    echo json_encode($loteFracaoMovExistente);
}


function buscarTagDimEPosicoesPorIDtag($idTag)
{
    if (!$idTag) {
        echo json_encode(['error' => 'Id Tag não informado!']);

        return false;
    }

    $arrRetorno = [];
    $tagDim  = LoteController::buscarTagDimEPosicoesPorIDtag($idTag);

    foreach ($tagDim as $item) {
        $arrRetorno['colunas'][$item['coluna']]['label'] = $item['coluna'];
        $arrRetorno['colunas'][$item['coluna']]['linhas'][$item['linha']]['label'] = $item['linha'];
        $arrRetorno['colunas'][$item['coluna']]['linhas'][$item['linha']]['caixas'][$item['caixa'] ?? 1] = [
            'label' => $item['caixa'] ?? 1,
            'lotefracaoqtd' => $item['qtd'],
            'idtagdim' => $item['idtagdim']

        ];
    }

    echo json_encode($arrRetorno);
}

function buscarInfoPedido($idNfItem)
{
    if (!$idNfItem) {
        echo json_encode(['error' => 'Id Nfitem não informado!']);

        return false;
    }

    $pedido = LoteController::buscarInfoPedido($idNfItem);

    if (!$pedido) {
        echo json_encode(['error' => 'Pedido não encontrado!']);

        return false;
    }

    echo json_encode($pedido);
}

function retirarProduto($dados)
{
    list($idLoteFracaoMov, $qtdRetirada) = explode(',', $dados);

    if (!$idLoteFracaoMov) {
        echo json_encode(['error' => 'Posição na prateleira não informada.!']);

        return false;
    }

    if (!$qtdRetirada) {
        echo json_encode(['error' => 'Quantidade a ser retirada não informada.!']);

        return false;
    }

    echo json_encode(LoteController::retirarProduto($idLoteFracaoMov, $qtdRetirada));
}

function alocarProdutos($dados)
{
    global $idEmpresa;

    list($idFracaoLoteMovAnterior, $idLote, $idTagDim, $qtdAlocacao) = explode(',', $dados);

    if (!$idTagDim) {
        echo json_encode(['error' => 'Posição na prateleira não informada.!']);

        return false;
    }

    if (!$qtdAlocacao) {
        echo json_encode(['error' => 'Quantidade a ser alocada não informada.!']);

        return false;
    }

    $zerandoQtd = LoteController::atualizarQuantidadeLoteFracaoMov($idFracaoLoteMovAnterior, 0);

    if (!$zerandoQtd) {
        echo json_encode(['error' => 'Ocorreu um erro ao atualizar quantidade do estoque.!']);

        return false;
    }

    $idUnidadeLogistica = getUnidadePadraoModulo('camarafria', $idEmpresa);
    $loteFracaoLogistica = LoteController::buscarLoteFracaoPorIdloteEIdUnidade($idLote, $idUnidadeLogistica);

    echo json_encode(LoteController::alocarProdutos($loteFracaoLogistica['idlotefracao'], $idTagDim, $qtdAlocacao));
}

function revalidarLote($idLote) {
    if(!$idLote) {
        echo json_encode([
            'error' => 'Id lote não informado!'
        ]);

        return false;
    }

    $atualizandoLotes = LoteController::revalidarLote($idLote);

    if($atualizandoLotes->error()) {
        echo json_encode([
            'error' => $atualizandoLotes->errorMessage()
        ]);

        return;
    }

    echo json_encode([
        'message' => 'Lote revalidado com sucesso.'
    ]);
}

function atualizarStatusLote($dados) {
    list($idLote, $idFluxoStatusOld, $statusOld) = explode(',', $dados);


    if(!$idLote) {
        echo json_encode([
            'error' => 'Id lote não informado!'
        ]);

        return false;
    }

    if(!$idFluxoStatusOld) {
        echo json_encode([
            'error' => 'Id status antigo não informado!'
        ]);

        return false;
    }

    if(!$statusOld) {
        echo json_encode([
            'error' => 'Status antigo não informado!'
        ]);

        return false;
    }

    $atualizandoLotes = LoteController::atualizarStatusLote($idLote, $idFluxoStatusOld, $statusOld);

    if($atualizandoLotes->error()) {
        echo json_encode([
            'error' => $atualizandoLotes->errorMessage()
        ]);

        return;
    }

    echo json_encode([
        'message' => 'Status do lote atualizado com sucesso.'
    ]);
}