<?

// QUERY
require_once(__DIR__ . "/../../form/querys/_iquery.php");
require_once(__DIR__ . "/../../form/querys/tag_query.php");
require_once(__DIR__ . "/../../form/querys/tagdim_query.php");
require_once(__DIR__ . "/../../form/querys/tagclass_query.php");
require_once(__DIR__ . "/../../form/querys/fluxostatus_query.php");
require_once(__DIR__ . "/../../form/querys/tagreserva_query.php");
require_once(__DIR__ . "/../../form/querys/tagsala_query.php");
require_once(__DIR__ . "/../../form/querys/device_query.php");
require_once(__DIR__ . "/../../form/querys/pessoa_query.php");
require_once(__DIR__ . "/../../form/querys/eventoobj_query.php");
require_once(__DIR__ . "/../../form/querys/log_query.php");
require_once(__DIR__ . "/../../form/querys/sequence_query.php");
require_once(__DIR__ . "/../../form/querys/lotelocalizacao_query.php");
require_once(__DIR__ . "/../../form/querys/_modulo_query.php");
require_once(__DIR__ . "/../../form/querys/arquivo_query.php");
require_once(__DIR__ . "/../../form/querys/empresa_query.php");

// CONTROLLERS
require_once(__DIR__ . "/_controller.php");
require_once(__DIR__ . "/tagtipo_controller.php");
require_once(__DIR__ . "/log_controller.php");
require_once(__DIR__ . "/fluxo_controller.php");
require_once(__DIR__ . "/unidade_controller.php");

require_once(__DIR__ . "/../../model/prodserv.php");

class TagController extends Controller
{
    public static $linhas = [
        '1' => '01',
        '2' => '02',
        '3' => '03',
        '4' => '04',
        '5' => '05',
        '6' => '6',
        '7' => '07',
        '8' => '08',
        '9' => '09',
        '10' => '10',
        '11' => '11',
        '12' => '12',
        '13' => '13',
        '14' => '14',
        '15' => '15',
        '16' => '16',
        '17' => '17',
        '18' => '18',
        '19' => '19',
        '20' => '20',
        '21' => '21',
        '22' => '22',
        '23' => '23',
        '24' => '24'
    ];

    public static $colunas = [
        '1' => '01',
        '2' => '02',
        '3' => '03',
        '4' => '04',
        '5' => '05',
        '6' => '06',
        '7' => '07',
        '8' => '08',
        '9' => '09',
        '10' => '10',
        '11' => '11',
        '12' => '12',
        '13' => '13',
        '14' => '14',
        '15' => '15',
        '16' => '16',
        '17' => '17',
        '18' => '18',
        '19' => '19',
        '20' => '20',
        '21' => '21',
        '22' => '22',
        '23' => '23',
        '24' => '24',
    ];

    public static $caixas = [
        '1' => '01',
        '2' => '02',
        '3' => '03',
        '4' => '04',
        '5' => '05',
        '6' => '06',
        '7' => '07',
        '8' => '08',
        '9' => '09',
        '10' => '10',
        '11' => '11',
        '12' => '12',
        '13' => '13',
        '14' => '14',
        '15' => '15',
        '16' => '16',
        '17' => '17',
        '18' => '18',
        '19' => '19',
        '20' => '20',
        '21' => '21',
        '22' => '22',
        '23' => '23',
        '24' => '24'
    ];

    public static $carrocerias = [
        '00' => '00 - não aplicável',
        '01' => '01 - Aberta',
        '02' => '02 - Fechada/Baú',
        '03' => '03 - Granelera',
        '04' => '04 - Porta Container',
        '05' => '05 - Sider'
    ];

    public static $rodados = [
        '01' => '01 - Truck',
        '02' => '02 - Toco',
        '03' => '03 - Cavalo Mecânico',
        '04' => '04 - VAN',
        '05' => '05 - Utilitário',
        '06' => '06 - Outros'
    ];
    public static $sistemasOperacionais = [
        'Linux' => 'Linux',
        'Win 7 ativado' => 'Win 7 ativado',
        'Win 7 nao ativado' => 'Win 7 nao ativado',
        'Win 8 ativado' => 'Win 8 ativado',
        'Win 8 nao ativado' => 'Win 8 nao ativado',
        'Win 10 ativado' => 'Win 10 ativado',
        'Win 10 nao ativado' => 'Win 10 nao ativado'
    ];

    public static $tagOffice = [
        'DanielRossi@leek5uu.onmicrosoft.com' => 'DanielRossi@leek5uu.onmicrosoft.com',
        'laudo_licenca_1@hotmail.com' => 'laudo_licenca_1@hotmail.com',
        'laudo_licenca_2@hotmail.com' => 'laudo_licenca_2@hotmail.com',
        'laudo_licenca_3@hotmail.com' => 'laudo_licenca_3@hotmail.com',
        'laudo_licenca_4@hotmail.com' => 'laudo_licenca_4@hotmail.com',
        'laudo_licenca6@hotmail.com' => 'laudo_licenca6@hotmail.com',
        'laudo_licenca7@hotmail.com' => 'laudo_licenca7@hotmail.com',
        'laudo_licenca8@hotmail.com' => 'laudo_licenca8@hotmail.com',
        'laudo_licenca9@hotmail.com' => 'laudo_licenca9@hotmail.com',
        'laudo_licenca10@hotmail.com' => 'laudo_licenca10@hotmail.com',
        'laudolaboratorio@gmail.com' => 'laudolaboratorio@gmail.com'
    ];

    public static $alfabeto = [
        '0' => '0',
        '1' => 'A',
        '2' => 'B',
        '3' => 'C',
        '4' => 'D',
        '5' => 'E',
        '6' => 'F',
        '7' => 'G',
        '8' => 'H',
        '9' => 'I',
        '10' => 'J',
        '11' => 'K',
        '12' => 'L',
        '13' => 'M',
        '14' => 'N',
        '15' => 'O',
        '16' => 'P',
        '17' => 'Q',
        '18' => 'R',
        '19' => 'S',
        '20' => 'T',
        '21' => 'U',
        '22' => 'V',
        '23' => 'W',
        '24' => 'X',
        '25' => 'Y',
        '26' => 'Z'
    ];

    // ----- Variáveis de apoio -----
    public static $statusVeiculo = array(
        "ATIVO" => "Ativo",
        "INATIVO" => "Inativo",
        "DEVOLVIDO" => "Devolvido",
        "ADEVOLVER" => "A DEVOLVER",
        "PT" => "Perda Total"
    );

    public static $combustivel = array(
        "ALCOOL" => "Álcool",
        "DIESEL" => "Diesel",
        "FLEX" => "Flex",
        "GASOLINA" => "Gasolina"
    );

    public static $ufBr = array(
        '' => '',
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins'
    );

    public static $cores = array(
        '' => '',
        'AMARELO' => 'Amarelo',
        'AZUL' => 'Azul',
        'BRANCO' => 'Branco',
        'CINZA' => 'Cinza',
        'MARROM' => 'Marrom',
        'PRATA' => 'Prata',
        'PRETO' => 'Preto',
        'VERDE' => 'Verde',
        'VERMELHO' => 'Vermelho'
    );
    // ----- Variáveis de apoio -----

    public static function buscarPorChavePrimaria($id)
    {
        $tag = SQL::ini(TagQuery::buscarPorChavePrimaria(), [
            'pkval' => $id
        ])::exec();

        if ($tag->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tag->errorMessage());
            return [];
        }

        return $tag->data[0];
    }

    public static function atualizarOuInserirTagSequence($idempresa)
    {
        $verificarSeTagSequenceExiste = SQL::ini(SequenceQuery::verificarSeTagSequenceExiste(), [
            'sequence' => 'tag',
            'idempresa' => $idempresa
        ])::exec();

        if (!$verificarSeTagSequenceExiste->numRows()) {
            $inserindoSequence = SQL::ini(SequenceQuery::inserir(), [
                'sequence' => 'tag',
                'idempresa' => $idempresa,
                'chave1' => 1,
                'chave2' => 1,
                'chave3' => 1,
                'exercicio' => 'YEAR(CURDATE())',
                'descricao' => 'null'
            ])::exec();
        } else {
            $atualizandoSequence = SQL::ini(SequenceQuery::atualizarChavePorIdEmpresa(), [
                'idempresa' => $idempresa
            ])::exec();
        }
    }

    public static function buscarTagDimPorIdTag($idTag, $coluna = false, $linha = false)
    {
        $query = TagDimQuery::buscarTagDimPorIdTag();

        $ordenacao = 'td.coluna ASC, td.linha desc';

        if ($coluna !== false) {
            $query .= " AND td.coluna = $coluna";
        }

        if ($linha) {
            $query .= " AND td.linha = $linha";
        }


        $query .= " ORDER BY $ordenacao";

        $tagDim = SQL::ini($query, [
            'idtag' => $idTag
        ])::exec();

        if ($tagDim->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagDim->errorMessage());
            return [];
        }

        return $tagDim->data;
    }

    public static function buscarLoteLocalizacaoEFracaoPorIdTagDimEIdUnidade($idTagDim, $idUnidade)
    {
        $results = SQL::ini(LoteLocalizacaoQuery::buscarLoteLocalizacaoEFracaoPorIdTagDimEIdUnidade(), [
            'idtagdim' => $idTagDim,
            'idunidade' => $idUnidade
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        if (isset($_GET["_idempresa"])) {
            $_idempresa = $_GET["_idempresa"];
        } else if (!empty($_GET['idlote'])) {
            $_sqlidempresa = "select idempresa from lote where idlote = " . $_GET['idlote'] . ";";
            $_residempresa = d::b()->query($_sqlidempresa) or die("getProdutosFormalizacao: Erro: " . mysqli_error(d::b()) . "\n" . $_sqlidempresa);

            while ($_rowidempresa = mysqli_fetch_assoc($_residempresa)) {
                $_idempresa = $_rowidempresa['idempresa'];
            }
        } else {
            $_idempresa = $_SESSION["SESSAO"]["IDEMPRESA"];
        }


        $sqllink = UnidadeController::buscarModuloDaUnidadePorIdunidade($idUnidade);

        $link = "lotealmoxarifado";

        if ($sqllink) $link = $sqllink['modulo'];

        $prodservclass = new PRODSERV();
        $idunidadepadrao = getUnidadePadraoModulo($link, $_idempresa);
        $resultadoArr = $results->data;
        $arrunori = getObjeto('unidade', $idunidadepadrao);

        foreach ($resultadoArr as $key => $item) {
            $vlfim = 0;
            $qtdfrR = NULL;

            $unestoque = $prodservclass->getUnEstoque($item['idprodserv'], $idunidadepadrao, $item['converteest'], $item['unpadrao'], $item['unlote']);
            if (
                strpos(strtolower($item['qtd_exp']), "d")
                or strpos(strtolower($item['qtd_exp']), "e")
            ) {
                $vlst = recuperaExpoente(tratanumero($item["qtd"]), $item['qtd_exp']);
                $vlproduzida = recuperaExpoente(tratanumero($item['qtdprod']), $item['qtdprod_exp']);
                $stvalor = $vlst . ' - ' . $unestoque;
                $stqtdproduzida =    $vlproduzida . ' - ' . $unestoque;
                $nund = explode("d", $vlst);
                $nune = explode("e", $vlst);
                if (!empty($nund[1])) {
                    $vlfim = $nund[0];
                    $vlfim1 = "d" . $nund[1];
                } else {
                    $vlfim = $nune[0];
                    $vlfim1 = "e" . $nune[1];
                }
            } else {
                $qtdfr = $prodservclass->getEstoqueLote($item['idlotefracao']);
                if ($qtdfr < 0) {
                    $qtdfr = 0;
                }
                $stvalor = number_format(tratanumero($qtdfr), 2, ',', '.') . ' - ' . $unestoque;
                $stqtdparoduzida = number_format(tratanumero($item['qtdprod'] * $item['valconvori']), 2, ',', '.') . ' - ' . $unestoque;
                if ($item['vunpadrao'] != 'Y' || $arrunori['convestoque'] != 'N' || $item['converteest'] != 'Y')
                    $vlfim = $qtdfr;
            }

            if ($item['converteest'] == 'Y' and $arrunori['convestoque'] == 'N') {
                $qtdfrR = $prodservclass->getEstoqueLoteReal($item['idlotefracao']);

                $arrlotefracao = getObjeto('lotefracao', $item['idlotefracao']);

                if ($item['vunpadrao'] != 'N')
                    $vlfim = $arrlotefracao["qtd"];
            }

            $resultadoArr[$key]['qtdLote'] = $stvalor;
            // $resultadoArr[$key]['qtdProduzidoLote'] = $stqtdparoduzida;
            $resultadoArr[$key]['qtdFracaoFinal'] = intval($vlfim);
            $resultadoArr[$key]['qtdFracao'] = $qtdfrR;
        }

        return $resultadoArr;
    }

    public static function verificarSePossuiLoteFracaoPorIdTagDimEIdUnidade($idTagDim, $idUnidade)
    {
        $lotes = SQL::ini(LoteLocalizacaoQuery::verificarSePossuiLoteFracaoPorIdTagDimEIdUnidade(), [
            'idtagdim' => $idTagDim,
            'idunidade' => $idUnidade
        ])::exec();

        if ($lotes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return [];
        }

        return $lotes->data;
    }

    public static function verificarSePossuiLoteFracaoPorIdTagDimEIdEmpresaEIdUnidade($idTagDim, $idUnidade, $idEmpresa)
    {
        $lotes = SQL::ini(LoteLocalizacaoQuery::verificarSePossuiLoteFracaoPorIdTagDimEIdEmpresaEIdUnidade(), [
            'idtagdim' => $idTagDim,
            'idempresa' => $idEmpresa,
            'idunidade' => $idUnidade
        ])::exec();

        if ($lotes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $lotes->errorMessage());
            return [];
        }

        return $lotes->data;
    }

    public static function buscarVarCarbonPorIdTag($idTag)
    {
        $arrRetorno = [];

        $results = SQL::ini(TagQuery::buscarVarCarbonPorIdTag(), [
            'idtag' => $idTag,
            'getidempresa' => getidempresa('t.idempresa', 'tag')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return $arrRetorno;
        }

        foreach ($results->data as $result) {
            $arrRetorno[$result['id']] = $result['rot'];
        }

        return $arrRetorno;
    }

    public static function buscarArquivosNfPorIdNfItem($idNfItem)
    {
        $arquivosNf = SQL::ini(ArquivoQuery::buscarArquivosNfPorIdNfItem(), [
            'idnfitem' => $idNfItem
        ])::exec();

        if ($arquivosNf->error()) {
            parent::error(__CLASS__, __FUNCTION__, $arquivosNf->errorMessage());
            return [];
        }

        $arquivosNf->data;
    }

    public static function buscarPessoasDaEmpresaLogada($idpessoa)
    {
        $arrRetorno = [];

        $pessoas = SQL::ini(PessoaQuery::buscarPessoasDaEmpresaLogada(), [
            'getidempresa' => getidempresa("oe.empresa", $_GET['_modulo']),
            'idpessoa' => $idpessoa
        ])::exec();

        if ($pessoas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $pessoas->errorMessage());
            return $arrRetorno;
        }

        foreach ($pessoas->data as $pessoa) {
            $inativo = $pessoa['status'] == 'INATIVO' ? ' (Inativo)' : '';
            $arrRetorno[$pessoa['idpessoa']] = ($pessoa['nomecurto'] != null) ? $pessoa['nomecurto'] . $inativo : $pessoa['nome'] . $inativo;
        }

        return $arrRetorno;
    }

    public static function buscarTagClass($idtagclass)
    {
        $classificacoes = SQL::ini(TagClassQuery::buscarTagClass(), [
            "idtagclass" => $idtagclass
        ])::exec();

        foreach ($classificacoes->data as $key => $classificacao) {
            $arrRetorno[$classificacao['idtagclass']] = $classificacao['tagclass'];
        }

        if ($classificacoes->error()) {
            $arrRetorno['error'] = "[" . strtoupper($classificacoes->errorMessage()) . "]";
        }

        return $arrRetorno;
    }

    public static function buscarSequenciaDaTag($idempresa, $idTag = false)
    {
        $sequencia = SQL::ini(TagQuery::buscarSequencia(), [
            'idempresa' => $idempresa
        ])::exec();


        $sequenciaTag = ($sequencia->numRows() == 0) ? 1 : $sequencia->data[0]['chave1'];

        if ($idTag) {
            $sequencia = SQL::ini(TagQuery::buscarPeloIdTag(), [
                'idtag' => $idTag
            ])::exec();

            $sequenciaTag = "{$sequencia->data[0]['sigla']}-{$sequencia->data[0]['tag']}";
        }

        if ($sequencia->error()) {
            $sequenciaTag = "[" . strtoupper($sequencia->errorMessage()) . "]";
        }

        return $sequenciaTag;
    }

    public static function buscarNfPorIdObjetoOrigem($idObjetoOrigem)
    {
        $nf = SQL::ini(TagQuery::buscarNfPorIdObjetoOrigem(), [
            'idobjetoorigem' => $idObjetoOrigem
        ])::exec();

        if ($nf->error()) {
            parent::error(__CLASS__, __FUNCTION__, $nf->errorMessage());
            return [];
        }

        return $nf->data;
    }

    public static function buscarLocalizacoes()
    {
        $arrRetorno = [];

        $localizacoes = SQL::ini(TagQuery::buscarLocalizacoes())::exec();

        if ($localizacoes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $localizacoes->errorMessage());
            return [];
        }

        foreach ($localizacoes->data as $localizacao) {
            $arrRetorno[$localizacao['idtag']] = $localizacao['tagdescr'];
        }

        return $arrRetorno;
    }

    public static function buscarTagPaiOuFilhoPorIdTag($idTag, $colunaPaiOuFilho = 'idtag')
    {
        $colunaParaJoinComATag = $colunaPaiOuFilho == 'idtagpai'  ? 'idtag' : 'idtagpai';

        $tagPai = SQL::ini(TagQuery::buscarTagPaiOuFilhoPorIdTag(), [
            'colunaparajoincomtag' => $colunaParaJoinComATag,
            'colunapaioufilho' => $colunaPaiOuFilho,
            'idtag' => $idTag
        ])::exec();

        if ($tagPai->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagPai->errorMessage());
            return [];
        }

        return $tagPai->data;
    }

    public static function buscarArquivoDaLocacaoPorIdObjeto($idObjeto)
    {
        $arquivos = SQL::ini(TagReservaQuery::buscarArquivosDaTagLocadaPorIdObjeto(), [
            'idtag' => $idObjeto,
            'objeto' => 'tag'
        ])::exec();

        if ($arquivos->error()) {
            parent::error(__CLASS__, __FUNCTION__, $arquivos->errorMessage());
            return [];
        }

        return $arquivos->data;
    }

    public static function buscarTagsParaVincularPorIdTag($idTag)
    {
        $arrRetorno = [];

        $tags = SQL::ini(TagQuery::buscarTagsParaVincularPorIdTag(), [
            'getidempresa' => getidempresa('t.idempresa', 'tag'),
            'idtag' => $idTag
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return $arrRetorno;
        }

        foreach ($tags->data as $tag) {
            $arrRetorno[$tag['idtag']] = $tag['tag'];
        }

        return $arrRetorno;
    }

    public static function buscarDevicesPeloIdTag($idTag)
    {
        $devices = SQL::ini(DeviceQuery::buscarDevicePorIdTag(), [
            'idtag' => $idTag
        ])::exec();

        return $devices->data;
    }

    public static function buscarStatusRotuloPorTipo()
    {
        $rotuloDeInicio = SQL::ini(FluxoStatusQuery::buscarRotuloStatusFluxoPorTipoBotao(), [
            'modulo' => 'tag',
            'tipobotao' => 'INICIO'
        ])::exec();

        return $rotuloDeInicio->data[0];
    }


    public static function buscarLocacao($idTag)
    {
        $locacoesArr = [];

        $tagLocadas = SQL::ini(TagReservaQuery::buscarPeloIdTag(), [
            'idtag' => $idTag
        ])::exec();

        if (!$tagLocadas->numRows()) {
            return false;
        }

        foreach ($tagLocadas->data as $locacao) {
            $tagLocada = SQL::ini(TagQuery::buscarPeloIdTag(), [
                'idtag' => $locacao['idobjeto']
            ])::exec();

            array_push($locacoesArr, [
                'idtagreserva' => $locacao['idtagreserva'],
                'idtag' => $locacao['idobjeto'],
                'tag' => $tagLocada->data[0]['tag'],
                'descricao' => $tagLocada->data[0]['descricao'],
                'sigla' => $tagLocada->data[0]['sigla'],
                'status' => $locacao['status'],
                'inicio' => $locacao['inicio'],
                'fim' => $locacao['fim'] ?? date("d/m/Y", strtotime($locacao['inicio']))
            ]);
        }

        return $locacoesArr;
    }

    public static function verificarPermissaoParaLocar()
    {
        $arrPermissoes = explode(',', getModsUsr("SQLWHEREMOD"));

        foreach ($arrPermissoes as $key => $permissao) {
            $arrPermissoes[$key] = str_replace(["'"], '', $permissao);
        }

        return in_array('locacaotag', $arrPermissoes);
    }

    public static function buscarTagsQuePossuemVinculoComTipoTag($idTag)
    {
        $arrRetorno = [];

        $tags = SQL::ini(TagQuery::buscarTagsQuePossuemVinculoComTipo(), [
            'idtag' => $idTag
        ])::exec();

        foreach ($tags->data as $tag) {

            $arrRetorno[$tag['idtag']] = $tag['tag'];
        }

        return $arrRetorno;
    }

    public static function locacaoDeTag($idTagQueSeraLocada, $idEmpresaDoNovoLocal, $idUnidadeDoNovoLocal = null, $dataInicioLocacao, $dataFimLocacao, $tagVeioDeUmaLocacao)
    {
        $statusLocado = 'LOCADO';
        $statusAtivo   = 'ATIVO';
        $statusInativo = 'INATIVO';

        $idDoFluxoStatusLocado  = FluxoController::getIdFluxoStatus('tag', $statusLocado);
        $idDoFluxoStatusAtivo   = FluxoController::getIdFluxoStatus('tag', $statusAtivo);
        $idDoFluxoStatusInativo = FluxoController::getIdFluxoStatus('tag', $statusInativo);

        if ($tagVeioDeUmaLocacao) {
            SQL::ini(TagQuery::atualizarStatusDaTagPeloId(), [
                'idtag' => $idTagQueSeraLocada,
                'idfluxostatus' => $idDoFluxoStatusInativo,
                'status' => $statusInativo
            ])::exec();

            $idTagQueSeraLocada = $tagVeioDeUmaLocacao['idtag'];
        }

        $tagQueSeraLocada = SQL::ini(TagQuery::buscarPorChavePrimaria(), [
            'pkval' => $idTagQueSeraLocada
        ])::exec();

        if (!$tagVeioDeUmaLocacao && $tagQueSeraLocada->data[0]['status'] != 'LOCADO') {
            SQL::ini(TagQuery::atualizarStatusDaTagPeloId(), [
                'idtag' => $idTagQueSeraLocada,
                'idfluxostatus' => $idDoFluxoStatusLocado,
                'status' => $statusLocado
            ])::exec();
        }

        // Sequence da tag q sera locada
        $tagSequence = SQL::ini(TagQuery::buscarSequencia(), [
            'idempresa' => $idEmpresaDoNovoLocal
        ])::exec();

        if (!$tagSequence->numRows()) {
            $tagSequence->data[0]['chave1'] = 1;
        }

        // Verificar se ja existe uma tag inativa locada na empresa destino
        $tagReservaInativo = SQL::ini(TagQuery::buscarTagLocadaPeloIdEmpresa(), [
            'idtag' => $idTagQueSeraLocada,
            'idempresa' => $idEmpresaDoNovoLocal,
            'status' => $statusInativo
        ])::exec();

        // Inativar demais locacoes dessa tags que estejam ativas
        $tagReservaAtivo = SQL::ini(TagReservaQuery::buscarPeloIdTag(), [
            'idtag' => $idTagQueSeraLocada
        ])::exec();

        if ($tagReservaAtivo->numRows()) {
            foreach ($tagReservaAtivo->data as $tag) {
                SQL::ini(TagQuery::atualizarStatusDaTagPeloId(), [
                    'idtag' => $tag['idobjeto'],
                    'idfluxostatus' => $idDoFluxoStatusInativo,
                    'status' => $statusInativo
                ])::exec();

                $atualizarStatusTagReserva = SQL::ini(TagReservaQuery::atualizarStatusPeloId(), [
                    'idtagreserva' => $tag['idtagreserva'],
                    'status' => $statusInativo
                ])::exec();
            }
        }

        if ($tagReservaInativo->numRows()) {
            $tagRetorno = $tagReservaInativo;

            $idObjeto = $tagReservaInativo->data[0]['idobjeto'];

            // Ativar tag que esta sendo locada
            $ativarTagQueEstaSendoLocada = SQL::ini(TagQuery::atualizarStatusDaTagPeloId(), [
                'idtag' => $tagReservaInativo->data[0]['idobjeto'],
                'status' => $statusAtivo,
                'idfluxostatus' => $idDoFluxoStatusAtivo
            ])::exec();

            // Ativar tagreserva
            $ativandoTagReserva = SQL::ini(TagReservaQuery::atualizarStatusPeloId(), [
                'idtagreserva' => $tagReservaInativo->data[0]['idtagreserva'],
                'status' => $statusAtivo
            ])::exec();
        } else {
            $tagInsertData = [
                'idempresa' => $idEmpresaDoNovoLocal ?? "null",
                'idunidade' => $idUnidadeDoNovoLocal ?? "null",
                'tag' => $tagSequence->data[0]["chave1"] ?? 1,
                'idfluxostatus' => $idDoFluxoStatusAtivo ?? "null",
                'descricao' => $tagQueSeraLocada->data[0]['descricao'] ?? "null",
                'idtagclass' => $tagQueSeraLocada->data[0]['idtagclass'] ?? "null",
                'idtagtipo' => $tagQueSeraLocada->data[0]['idtagtipo'] ?? "null",
                'emuso' => $tagQueSeraLocada->data[0]['emuso'] ?? "null",
                'local' => $tagQueSeraLocada->data[0]['local'] ?? "null",
                'status' => $statusAtivo ?? "null",
                'fabricante' => $tagQueSeraLocada->data[0]['fabricante'] ?? "null",
                'modelo' => $tagQueSeraLocada->data[0]['modelo'] ?? "null",
                'numnfe' => $tagQueSeraLocada->data[0]['numnfe'] ?? "null",
                'nserie' => $tagQueSeraLocada->data[0]['nserie'] ?? "null",
                'workflow' => $tagQueSeraLocada->data[0]['workflow'] ?? "null",
                'obs' => addslashes($tagQueSeraLocada->data[0]['obs']) ?? "null",
                'exatidao' => $tagQueSeraLocada->data[0]['exatidao'] ?? "null",
                'padraotempmin' => $tagQueSeraLocada->data[0]['padraotempmin'] ?? "null",
                'padraotempmax' => $tagQueSeraLocada->data[0]['padraotempmax'] ?? "null",
                'linha' => $tagQueSeraLocada->data[0]['linha'] ?? "null",
                'coluna' => $tagQueSeraLocada->data[0]['coluna'] ?? "null",
                'varcarbon' => $tagQueSeraLocada->data[0]['varcarbon'] ?? "null",
                'placa' => $tagQueSeraLocada->data[0]['placa'] ?? "null",
                'renavam' => $tagQueSeraLocada->data[0]['renavam'] ?? "null",
                'tara' => $tagQueSeraLocada->data[0]['tara'] ?? "null",
                'tpRod' => $tagQueSeraLocada->data[0]['tpRod'] ?? "null",
                'tpCar' => $tagQueSeraLocada->data[0]['tpCar'] ?? "null",
                'uf' => $tagQueSeraLocada->data[0]['uf'] ?? "null",
                'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                'criadoem' => "NOW()",
                'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                'alteradoem' => "NOW()",
                'idprateleira' => $tagQueSeraLocada->data[0]['idprateleira'] ?? "null",
                'processador' => $tagQueSeraLocada->data[0]['processador'] ?? "null",
                'memoria' => $tagQueSeraLocada->data[0]['memoria'] ?? "null",
                'hd' => $tagQueSeraLocada->data[0]['hd'] ?? "null",
                'video' => $tagQueSeraLocada->data[0]['video'] ?? "null",
                'ip' => $tagQueSeraLocada->data[0]['ip'] ?? "null",
                'so' => $tagQueSeraLocada->data[0]['so'] ?? "null",
                'nchip' => $tagQueSeraLocada->data[0]['nchip'] ?? "null",
                'nemei' => $tagQueSeraLocada->data[0]['nemei'] ?? "null",
                'plano' => $tagQueSeraLocada->data[0]['plano'] ?? "null",
                'office' => $tagQueSeraLocada->data[0]['office'] ?? "null",
                'consumo' => $tagQueSeraLocada->data[0]['consumo'] ?? "null",
                'voltagem' => $tagQueSeraLocada->data[0]['voltagem'] ?? "null",
                'revisado' => $tagQueSeraLocada->data[0]['revisado'] ?? "null",
                'datacalibracao' => $tagQueSeraLocada->data[0]['datacalibracao'] ? "'" . $tagQueSeraLocada->data[0]['datacalibracao'] . "'" : "null",
                'dataqualificacao' => $tagQueSeraLocada->data[0]['dataqualificacao'] ? "'" . $tagQueSeraLocada->data[0]['dataqualificacao'] . "'" : "null",
                'calibracao' => $tagQueSeraLocada->data[0]['calibracao'] ?? "null",
                'qualificacao' => $tagQueSeraLocada->data[0]['qualificacao'] ?? "null",
                'macaddress' => $tagQueSeraLocada->data[0]['macaddress'] ?? "null",
                'temperaturam5' => $tagQueSeraLocada->data[0]['temperaturam5'] ?? "null",
                'umidadem5' => $tagQueSeraLocada->data[0]['umidadem5'] ?? "null",
                'pressaom5' => $tagQueSeraLocada->data[0]['pressaom5'] ?? "null",
                'certificado' => $tagQueSeraLocada->data[0]['certificado'] ?? "null",
                'idpessoa' => $tagQueSeraLocada->data[0]['idpessoa'] ?? "null",
                'idobjetoorigem' => $tagQueSeraLocada->data[0]['idobjetoorigem'] ?? "null",
                'tipoobjetoorigem' => $tagQueSeraLocada->data[0]['tipoobjetoorigem'] ?? "null",
                'lotacao' => $tagQueSeraLocada->data[0]['lotacao'] ?? "null",
                'multiensaio' => $tagQueSeraLocada->data[0]['multiensaio'] ?? "null",
                'tempo' => $tagQueSeraLocada->data[0]['tempo'] ?? "null",
                'ordem' => $tagQueSeraLocada->data[0]['ordem'] ?? "null",
                'remoto' => $tagQueSeraLocada->data[0]['remoto'] ?? "null",
                'linguagem' => $tagQueSeraLocada->data[0]['linguagem'] ?? "null",
                'indpressao' => 'N',
                'cor' => 'null'
            ];

            $inseriandoNovaTag = SQL::ini(TagQuery::inserir(), $tagInsertData)::exec();
            $idDaTagInserida = $inseriandoNovaTag->lastInsertId();

            $tagCriada = SQL::ini(TagQuery::buscarPorChavePrimaria(), [
                'pkval' => $idDaTagInserida
            ])::exec();

            $tagRetorno = $tagCriada;

            FluxoController::inserirFluxoStatusHist('tag', $tagCriada->data[0]['idtag'], $idDoFluxoStatusAtivo, $statusAtivo);

            //Atualiza Ou Insere a chave da tag
            $atualizaOuInsereSequenceTag = SELF::atualizarOuInserirTagSequence($idEmpresaDoNovoLocal);

            $idObjeto = $tagCriada->data[0]['idtag'];
        }

        $localizacaoDaTagQueEstaSendoLocada = SQL::ini(TagSalaQuery::buscarTagPaiPeloIdTagFilho(), [
            'idtag' => $idTagQueSeraLocada
        ])::exec();

        if (!SELF::verificarSePossuiTagPai($idObjeto) && $localizacaoDaTagQueEstaSendoLocada->numRows()) {
            $tagPaiOriginal = false;

            $tagPaiLocada = SQL::ini(TagReservaQuery::buscarPeloIdTag(), [
                'idtag' => $localizacaoDaTagQueEstaSendoLocada->data[0]['idtagpai']
            ])::exec();

            // Verificar se a tagsala é locada
            if (!$tagPaiLocada->numRows()) {
                $tagPaiOriginal = SQL::ini(TagReservaQuery::buscarPeloIdObjeto(), [
                    'idobjeto' => $localizacaoDaTagQueEstaSendoLocada->data[0]['idtagpai'],
                    'tipoobjeto' => 'tag'
                ])::exec();

                $tagPaiLocada = SQL::ini(TagReservaQuery::buscarPeloIdTag(), [
                    'idtag' => $tagPaiOriginal->data[0]['idtag']
                ])::exec();
            }

            if ($tagPaiOriginal !== false) {
                $tagPaiLocadaDaEmpresaDestino = ['idobjeto' => $tagPaiLocada->data[0]['idobjeto']];
            } else {
                $tagPaiLocadaDaEmpresaDestino = array_filter($tagPaiLocada->data, function ($item) use ($idEmpresaDoNovoLocal) {
                    return $item['idempresa'] == $idEmpresaDoNovoLocal;
                })[0];
            }

            $inserirTagSala = SQL::ini(TagSalaQuery::inserirTagSala(), [
                'idtagpai' => $tagPaiLocadaDaEmpresaDestino['idobjeto'],
                'idtag' => $idObjeto,
                'idempresa' => $idEmpresaDoNovoLocal,
                'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                'criadoem' => date('Y-m-d H:i:s'),
                'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                'alteradoem' => date('Y-m-d H:i:s')
            ])::exec();
        }

        // Inativar tag reserva anterior
        SQL::ini(TagReservaQuery::inativarPeloIdTag(), [
            'idtag' => $idTagQueSeraLocada,
            'idobjeto' => $tagCriada->data[0]['idtag']
        ])::exec();

        $queryInserirTagReserva = TagReservaQuery::inserir();
        $dadosInserirTagReserva = [
            'idtag' => $idTagQueSeraLocada,
            'idobjeto' => $idObjeto,
            'objeto' => 'tag',
            'inicio' => $dataInicioLocacao,
            'fim' => $dataFimLocacao,
            'trava' => "N",
            'status' => $statusAtivo,
            'criadopor' => $_SESSION['SESSAO']['USUARIO'],
            'criadoem' => "'" . date('Y-m-d H:i:s') . "'",
            'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
            'alteradoem' => "'" . date('Y-m-d H:i:s') . "'"
        ];

        if (!$dataFimLocacao) {
            $queryInserirTagReserva = TagReservaQuery::removerColuna('fim', $queryInserirTagReserva);
            unset($dadosInserirTagReserva['fim']);
        }

        // Criar vinculo da tag criada com tag original
        $tagReserva = SQL::ini($queryInserirTagReserva, $dadosInserirTagReserva)::exec();

        return $tagRetorno->data[0];
    }

    public static function buscarTagPorIdTagClassEIdTagTipo($idTagClass, $idTagTipo)
    {
        $arrRetorno = [];

        $tag = SQL::ini(TagQuery::buscarTagPorIdTagClassEIdTagTipo(), [
            'idtagclass' => $idTagClass,
            'idtagtipo' => $idTagTipo
        ])::exec();

        if ($tag->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tag->errorMessage());
            return $arrRetorno;
        }

        foreach ($tag->data as $tag) {
            $arrRetorno[$tag['idtag']] = $tag['descr'];
        }

        return $arrRetorno;
    }

    public static function buscarTagPorIdTagClassEIdEmpresa($idTagClass, $idUnidade, $idEmpresa)
    {
        $tags = SQL::ini(TagQuery::buscarTagPorIdTagClassEIdEmpresa(), [
            'idtagclass' => $idTagClass,
            'idempresa' => $idEmpresa,
            'idunidade' => $idUnidade
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function verificarSePossuiTagPai($idTag)
    {
        $resultado = SQL::ini(TagSalaQuery::buscarTagPaiPeloIdTagFilho(), [
            'idtag' => $idTag
        ])::exec();

        return $resultado->numRows() ? true : false;
    }

    public static function buscarTagsPorIdClassificacao()
    {
        $blocosAtivos = SQL::ini(TagQuery::buscarTagsPorIdClassificacao(), ['idtagclass' => 13])::exec()->data;
        $arrDeRetorno = [];

        foreach ($blocosAtivos as $bloco) {
            if (!$bloco['caminho']) {
                continue;
            }

            array_push($arrDeRetorno, $bloco);
        }

        return $arrDeRetorno;
    }

    public static function atualizarIdTagPaiPorIdTagFilho($idTagFilho, $novoIdTagPai)
    {
        $tag = SQL::ini(TagSalaQuery::buscarTagPaiPeloIdTagFilho(), [
            'idtag' => $idTagFilho
        ])::exec();

        if ($tag->numRows()) {
            $atualizarTagPai = SQL::ini(TagSalaQuery::atualizarIdTagPaiPeloIdTagSala(), [
                'idtagsala' => $tag->data[0]['idtagsala'],
                'idtagpai' => $novoIdTagPai
            ]);

            return true;
        }

        return false;
    }

    public static function cancelarLocacaoPorIdTagReserva($idTagReserva)
    {
        $tagReserva = SQL::ini(TagReservaQuery::buscarPorId(), [
            'idtagreserva' => $idTagReserva
        ])::exec()->data[0];

        $idTagQueSeraInativada = $tagReserva['idobjeto'];
        $idTagOriginal = $tagReserva['idtag'];

        $statusInativo = 'INATIVO';
        $statuAtivo = 'ATIVO';

        $idDoFluxosStatusInativo = FluxoController::getIdFluxoStatus('tag', $statusInativo);
        $idDoFluxosStatusAtivo   = FluxoController::getIdFluxoStatus('tag', $statuAtivo);

        // Inativando tag que estava locada
        SQL::ini(TagQuery::atualizarStatusDaTagPeloId(), [
            'idtag' => $idTagQueSeraInativada,
            'idfluxostatus' => $idDoFluxosStatusInativo,
            'status' => $statusInativo
        ])::exec();

        // Ativando tag origem que estava com o status locado
        SQL::ini(TagQuery::atualizarStatusDaTagPeloId(), [
            'idtag' => $idTagOriginal,
            'idfluxostatus' => $idDoFluxosStatusAtivo,
            'status' => $statuAtivo
        ])::exec();

        // Inativando status da reserva
        SQL::ini(TagReservaQuery::atualizarStatusPeloId(), [
            'idtagreserva' => $idTagReserva,
            'status' => $statusInativo
        ])::exec();

        // Atualizando o vinculo do device
        SQL::ini(DeviceQuery::atualizarColunaIdTagPeloIdTag(), [
            'idtag' => $idTagOriginal,
            'idtagantiga' => $idTagQueSeraInativada
        ])::exec();

        // Definir data de fim da locacao
        SQL::ini(TagReservaQuery::atualizarColunaFimAlocacao(), [
            'idtagreserva' => $idTagReserva,
            'fim' => date('Y-m-d H:i:s')
        ])::exec();
    }

    public static function buscarTagsQueNaoEstejamLocadasPorIdTagTipo($idTagTipo, $idEvento = false, $idEventoAdd = false, $autocomplete = false)
    {
        $clausulaNotExist = '';

        if ($idEvento && $idEventoAdd) {
            $clausulaNotExist = "AND NOT EXISTS(
                SELECT 1
                FROM eventoobj e
                WHERE e.idevento = $idEvento
                AND  e.ideventoadd = $idEventoAdd
                AND e.idobjeto = t.idtag
                AND e.objeto='tag' 
            )";
        }

        $tags = SQL::ini(TagQuery::buscarTagsQueNaoEstejamLocadasPorIdTagTipo(), [
            'idtagtipo' => $idTagTipo,
            'clausula' => $clausulaNotExist
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return false;
        }

        if ($autocomplete) {
            $arrRetorno = [];

            foreach ($tags->data as $key => $tag) {
                $arrRetorno[$key]['label'] = $tag['descrtag'];
                $arrRetorno[$key]['value'] = $tag['idtag'];
                $arrRetorno[$key]['sigla'] = $tag['sigla'];
            }

            return $arrRetorno;
        }

        return $tags->data;
    }

    public static function buscarListaDeTagsPorIdEventoEIdEventoTipoAdd($idEvento, $idEventoTipoAdd)
    {
        $tags = SQL::ini(TagQuery::buscarListaDeTagsPorIdEventoEIdEventoTipoAdd(), [
            'idevento' => $idEvento,
            'ideventotipoadd' => $idEventoTipoAdd
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return false;
        }

        return $tags->data;
    }

    public static function buscarTipoObjetoDasTagsPorIdEventoAddEIdEvento($idEventoAdd, $idEvento)
    {
        $arrRetorno = [];

        $tagsTipoObjeto = SQL::ini(EventoObjQuery::buscarTipoObjetoDasTagsPorIdEventoAddEIdEvento(), [
            'ideventoadd' => $idEventoAdd,
            'idevento' => $idEvento
        ])::exec();

        if ($tagsTipoObjeto->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagsTipoObjeto->errorMessage());
            return [];
        }

        foreach ($tagsTipoObjeto->data as $tag) {
            $arrRetorno[$tag['idtag']] = $tag;
        }


        return $arrRetorno;
    }

    public static function buscarTiposAtivosDeEquipamentos($toFillSelect = false)
    {
        $arrRetorno = [];

        $tags = SQL::ini(TagQuery::buscarTiposAtivosDeEquipamentos())::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return false;
        }

        if ($toFillSelect) {
            foreach ($tags->data as $tag) {
                $arrRetorno[$tag['idtagtipo']] = $tag['tagtipo'];
            };

            return $arrRetorno;
        }

        return $tags->data;
    }

    public static function atualizarTagPaiOuFilhoNaTagSala($coluna, $valor)
    {
        $tagSala = SQL::ini(TagSalaQuery::buscarTagSalaPorColunaEValor(), [
            'coluna' => $coluna,
            'valor' => $valor
        ])::exec();

        $i = 0;

        foreach ($tagSala->data as $tag) {
            $_SESSION["arrpostbuffer"][$i]["d"]["tagsala"]["idtagsala"] = $tag['idtagsala'];

            $i++;
        }

        unset($_SESSION["arrpostbuffer"]["x"]["u"]["mapaequipamento"]["idtagsala"]);
    }

    public static function buscarReservasPorIdTag($idTag, $data)
    {
        $tagReserva = SQL::ini(TagReservaQuery::buscarReservasPorIdTag(), [
            'idtag' => $idTag,
            'data' => $data,
            'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"]
        ])::exec();

        if ($tagReserva->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagReserva->errorMessage());
            return false;
        }

        return $tagReserva->data;
    }

    public static function buscarTagsFormatadasPorIdTagTipo($idTagTipo, $idempresa = false)
    {
        $idempresaClausula = '';
        if ($idempresa) $idempresaClausula = " AND t.idempresa = " . cb::idempresa();

        $tags = SQL::ini(TagQuery::buscarTagsFormatadasPorIdTagTipo(), [
            'idtagtipo' => $idTagTipo,
            'idempresa' => $idempresaClausula
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function buscarTagsFormatadasPorIdTagSalaEIdTagTipo($idTagPai, $idTagTipo)
    {
        $tags = SQL::ini(TagQuery::buscarTagsFormatadasPorIdTagSalaEIdTagTipo(), [
            'idtagtipo' => $idTagTipo,
            'idtagpai' => $idTagPai
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function atualizarValorMinimoMaximoTag($idevento, $ideventoadd, $idobjeto, $min, $max)
    {
        // echo $sql = "SELECT padraotempmin, padraotempmax FROM tag WHERE idtag = '".$idobjeto."';";
        // $res = d::b()->query($sql);
        $tag = self::buscarPorChavePrimaria($idobjeto);

        $atualizandoEventoObj = SQL::ini(EventoObjQuery::atualizarValorMinimoMaximoTag(), [
            'idevento' => $idevento,
            'ideventoadd' => $ideventoadd,
            'idobjeto' => $idobjeto,
            'minimo' => $min,
            'maximo' => $max
        ])::exec();
    }

    public static function verificarReserva($idTag, $inicio, $fim, $regraEvento = '', $regraTrava = '')
    {
        $verificacaoReserva = SQL::ini(TagReservaQuery::verificarReserva(), [
            'idtag'  => $idTag,
            'inicio' => $inicio,
            'fim' => $fim,
            'regraevento' => $regraEvento,
            'regratrava' => $regraTrava
        ])::exec();

        if ($verificacaoReserva->error()) {
            parent::error(__CLASS__, __FUNCTION__, $verificacaoReserva->errorMessage());
            return true;
        }

        return !$verificacaoReserva->numRows() ? false : true;
    }

    public static function buscarTagTagdim($idobjeto)
    {
        $tagReserva = SQL::ini(TagQuery::buscarTagTagdim(), [
            'idobjeto' => $idobjeto

        ])::exec();

        if ($tagReserva->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagReserva->errorMessage());
            return false;
        }

        return $tagReserva->data[0];
    }

    public static function validarPrazoLocacao($idTag, $idEmpresa, $dataInicio)
    {
        $tagsLocada = SQL::ini(TagReservaQuery::validarPrazoLocacao(), [
            'idtag' => $idTag,
            'idempresa' => $idEmpresa,
            'datainicio' => $dataInicio
        ])::exec();

        return $tagsLocada->numRows() ? true : false;
    }

    public static function buscarTagEmpresa($idobjetoorigem, $tipoobjetoorigem)
    {
        $results = SQL::ini(TagQuery::buscarTagEmpresa(), [
            "idobjetoorigem" => $idobjetoorigem,
            "tipoobjetoorigem" => $tipoobjetoorigem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarTagEmpresaPorIdNf($_idnf)
    {
        $results = SQL::ini(TagQuery::buscarTagEmpresaPorIdNf(), [
            "idnf" => $_idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $arrTag = [];
            foreach ($results->data as $tag) {
                $arrTag[$tag['idnfitem']][$tag['idtag']]['tag'] = $tag['tag'];
                $arrTag[$tag['idnfitem']][$tag['idtag']]['idtag'] = $tag['idtag'];
                $arrTag[$tag['idnfitem']][$tag['idtag']]['sigla'] = $tag['sigla'];
            }

            $dados['dados'] = $arrTag;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarTagPorIdTag($idtag)
    {
        $results = SQL::ini(TagSalaQuery::buscarTagPorIdTag(), [
            "idtag" => $idtag
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function apagarTagSalaPorIdTag($idTag)
    {
        $deletandoTagSalaPorIdTag = SQL::ini(TagSalaQuery::deletarTagSalaPorIdTag(), [
            'idtag' => $idTag
        ])::exec();

        if ($deletandoTagSalaPorIdTag->error()) {
            parent::error(__CLASS__, __FUNCTION__, $deletandoTagSalaPorIdTag->errorMessage());
        }
    }

    public static function buscarTagsAtivas()
    {
        $tags = SQL::ini(TagQuery::buscarTagsAtivas())::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function buscarBlocoDaTag($idTag)
    {
        $tagBloco = SQL::ini(TagQuery::buscarBlocoDaTag(), [
            'idtag' => $idTag
        ])::exec();

        if ($tagBloco->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagBloco->errorMessage());
            return [];
        }

        return $tagBloco->data[0];
    }

    public static function buscarTagPaiPeloIdTagFilho($idTagFilho)
    {
        $tag = SQL::ini(TagSalaQuery::buscarTagPaiPeloIdTagFilho(), [
            'idtag' => $idTagFilho
        ])::exec();

        if ($tag->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tag->errorMessage());
            return [];
        }

        return $tag->data[0];
    }

    public static function buscarTagsEmEstoquePorIdUnidadeIdProdServEIdTagTipo($idUnidade, $idProdServ, $idTagTipo, $notInUnidade = false)
    {
        $clausulaUnidade = '';

        if ($idUnidade) {
            $clausulaUnidade = "AND t.idunidade IN ($idUnidade)";
        }

        if ($idUnidade && $notInUnidade) {
            $clausulaUnidade = "AND t.idunidade NOT IN ($idUnidade)";
        }

        $tagsEmEstoque = SQL::ini(TagQuery::buscarTagsEmEstoquePorIdUnidadeIdProdServEIdTagTipo(), [
            'clausulaunidade' => $clausulaUnidade,
            'idprodserv' => $idProdServ,
            'idtagtipo' => $idTagTipo
        ])::exec();

        if ($tagsEmEstoque->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagsEmEstoque->errorMessage());
            return [];
        }

        return $tagsEmEstoque->data;
    }

    public static function buscarTodasTags($toFillSelect = false)
    {
        $arrRetorno = [];
        $tags = SQL::ini(TagQuery::buscarTodasTags())::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        if ($toFillSelect) {
            foreach ($tags->data as $tag) {
                $arrRetorno[$tag['idtag']] = $tag['descricao'];
            }

            return $arrRetorno;
        }

        return $tags->data;
    }

    public static function buscarTagPorVarCarbonEGetIdEmpresa($varCarbon, $getIdEmpresa)
    {
        $tags = SQL::ini(TagQuery::buscarTagPorVarCarbonEGetIdEmpresa(), [
            'varcarbon' => $varCarbon,
            'getidempresa' => $getIdEmpresa
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function buscarTagSalaPorIdTag($idTag)
    {
        $tagSala = SQL::ini(TagSalaQuery::buscarTagSalaPorIdTag(), [
            'idtag' => $idTag
        ])::exec();

        if ($tagSala->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagSala->errorMessage());
            return [];
        }

        return $tagSala->data[0];
    }

    public static function deletarTagSalaPorIdTag($idTag)
    {
        $deletandoTagSalaPorIdTag = SQL::ini(TagSalaQuery::deletarTagSalaPorIdTag(), [
            'idtag' => $idTag
        ])::exec();

        if ($deletandoTagSalaPorIdTag->error()) {
            parent::error(__CLASS__, __FUNCTION__, $deletandoTagSalaPorIdTag->errorMessage());

            $dadosLog = [
                'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
                'sessao' => session_id(),
                'tipoobjeto' => 'tag',
                'idobjeto' => $idTag,
                'tipolog' => 'saveprechange__soltag',
                'log' => 'Erro:' . $deletandoTagSalaPorIdTag->sql(),
                'status' => '',
                'info' => $deletandoTagSalaPorIdTag->errorMessage(),
                'criadoem' => "NOW()",
                'data' => "NOW()"
            ];

            $inserindoLog = LogController::inserir($dadosLog);
        }
    }

    public static function buscarDescricaoDasTags($toAutocomplete = false)
    {
        $tagsDescricao = SQL::ini(TagQuery::buscarDescricaoDasTags())::exec();
        $empresa = SQL::ini(EmpresaQuery::buscarPorChavePrimaria(), [
            'pkval' => $_GET['_idempresa'] ?? $_SESSION['SESSAO']['IDEMPRESA']
        ])::exec();

        if ($tagsDescricao->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagsDescricao->errorMessage());
            return [];
        }

        if ($toAutocomplete) {
            $arrRetorno = [];

            foreach ($tagsDescricao->data as $tag) {
                array_push($arrRetorno, [
                    'value' => "{$empresa->data[0]['sigla']} - {$tag['tagdescricao']}",
                    'label' => "{$empresa->data[0]['sigla']} - {$tag['tagdescricao']}"
                ]);
            }

            return $arrRetorno;
        }

        return $tagsDescricao->data;
    }

    public static function locacaoDeTagFilhos($idTagPai, $tags, $idEmpresaDoNovoLocal, $idUnidadeDoNovoLocal, $dataInicioLocacao, $dataFimLocacao)
    {
        if ($tags) {
            foreach ($tags as $tagFilho) {
                if ($tagFilho['status'] != 'INATIVO' && $tagFilho['status'] != 'DESAPARECIDO') {
                    $statusLocado  = 'LOCADO';
                    $idDoFluxoStatusLocado   = FluxoController::getIdFluxoStatus('tag', $statusLocado);

                    $tagVeioDeUmaLocacao = SQL::ini(TagReservaQuery::buscarPeloIdObjeto(), [
                        'idobjeto' => $tagFilho['idtag'],
                        'tipoobjeto' => 'tag'
                    ])::exec();

                    $tagCriadaOuAtivada = self::locacaoDeTag($tagFilho['idtag'], $idEmpresaDoNovoLocal, $idUnidadeDoNovoLocal, $dataInicioLocacao, $dataFimLocacao, $tagVeioDeUmaLocacao->data[0]);

                    $paiDaTagCriadaOuAtivada = SQL::ini(TagSalaQuery::buscarTagPaiPeloIdTagFilho(), [
                        'idtag' => $tagCriadaOuAtivada['idtag']
                    ])::exec();

                    if ($paiDaTagCriadaOuAtivada->numRows()) {
                        $atualizacaoRegistroTagSala = SQL::ini(TagSalaQuery::atualizarIdTagPaiPeloIdTagSala(), [
                            'idtagsala' => $paiDaTagCriadaOuAtivada->data[0]['idtagsala'],
                            'idtagpai' => $idTagPai
                        ])::exec();
                    } else {
                        $criacaoRegistroTagSala = SQL::ini(TagSalaQuery::inserirTagSala(), [
                            'idtagpai' => $idTagPai,
                            'idtag' => $tagCriadaOuAtivada['idtag'],
                            'idempresa' => $idEmpresaDoNovoLocal,
                            'criadopor' => $_SESSION['SESSAO']['USUARIO'],
                            'criadoem' => date('Y-m-d H:i:s'),
                            'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
                            'alteradoem' => date('Y-m-d H:i:s')
                        ])::exec();
                    }
                }
            }
        }
    }

    public static function buscarPorIdTagTipo($idtagtipo)
    {
        $results = SQL::ini(TagTipoQuery::buscarPorIdTagTipo(), [
            'idtagtipo' => $idtagtipo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarTagsPorIdunidadeEIdEmpresa($idUnidade, $idEmpresa)
    {
        $tags = SQL::ini(TagQuery::buscarTagsPorIdunidadeEIdEmpresa(), [
            'idunidade' => $idUnidade,
            'idempresa' => $idEmpresa
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function buscarTagsDisponiveisParaVinculoEmUnidades($idEmpresa)
    {
        $tags = SQL::ini(TagQuery::buscarTagsDisponiveisParaVinculoEmUnidades(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function buscarTagsDisponiveisParaVinculo($idEmpresa)
    {
        $tags = SQL::ini(TagQuery::buscarTagsDisponiveisParaVinculo(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }

    public static function atualizarReservaSalaLoteFormalizacao($arrayAtualizaReserva)
    {
        $results = SQL::ini(TagReservaQuery::atualizarReservaSalaLoteFormalizacao(), $arrayAtualizaReserva)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function inserirReservaSalaLoteFormalizacao($arrayInserirReserva)
    {
        $results = SQL::ini(TagReservaQuery::inserir(), $arrayInserirReserva)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "Falha ao Reservar Sala";
        }
    }

    public static function buscarImpressorasTipoZebraDoModulo($idmodulo)
    {
        $results = SQL::ini(_ModuloQuery::buscarImpressorasTipoZebraDoModulo(), ['idmodulo' => $idmodulo])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
        return parent::toFillSelect($results->data);
    }

    public static function transferirLote($idTagDimOrigem, $idTagDimDestino, $idUnidade, $idEmpresa)
    {
        $transferindoLote = SQL::ini(LoteLocalizacaoQuery::transferirLote(), [
            'idtagdimorigem' => $idTagDimOrigem,
            'idtagdimdestino' => $idTagDimDestino,
            'idempresa' => $idEmpresa,
            'idunidade' => $idUnidade
        ])::exec();

        if ($transferindoLote->error()) {
            parent::error(__CLASS__, __FUNCTION__, $transferindoLote->errorMessage());
            return ['error' => 'Erro ao transferir lote: ' . addslashes($transferindoLote->errorMessage())];
        }
        return ['idtagdimorigem' => $idTagDimOrigem, 'idtagdimdestino' => $idTagDimDestino];
    }

    public static function buscarTagDimPorIdTagDim($idTagDim)
    {
        $tagDim = SQL::ini(TagDimQuery::buscarTagDimPorIdTagDim(), [
            'idtagdim' => $idTagDim
        ])::exec();

        if ($tagDim->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tagDim->errorMessage());
            return ['error' => 'Erro ao transferir lote: ' . addslashes($tagDim->errorMessage())];
        }
        return $tagDim->data;
    }

    public static function inserirTagHistorico($arrayHistorico)
    {
        $results = SQL::ini(TagQuery::inserirTagHistorico(), $arrayHistorico)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarHistoricoTag($campovalue, $idtag)
    {
        $results = SQL::ini(TagQuery::buscarHistoricoTag(), [
            "campovalue" => $campovalue,
            "idtag" => $idtag
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function updateHistoricoTag($idtaghistorico)
    {
        $results = SQL::ini(TagQuery::updateHistoricoTag(), [
            "idtaghistorico" => $idtaghistorico
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }
    }

    public static function listarHistoricoTagVeiculo($idtag)
    {
        $results = SQL::ini(TagQuery::listarHistoricoTagVeiculo(), [
            "idtag" => $idtag
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarTagPorIdTagClass($idtagclass)
    {
        $results = SQL::ini(TagQuery::buscarTagPorIdTagClass(), [
            "idtagclass" => $idtagclass
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }

    public static function buscarFilhos($idTag)
    {
        $tipoTag = SQL::ini(TagQuery::buscarTagPorIdTagClass(), [
            "idtagclass" => $idTag
        ])::exec();

        if ($tipoTag->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tipoTag->errorMessage());
            return [];
        }

        return $tipoTag->data;
    }

    public static function buscarTipoPorIdTagSala($idTagSala, $idempresa, $toFillSelect = false)
    {
        $tipoTag = SQL::ini(TagTipoQuery::buscarTipoPorIdTagSala(), [
            "idtagpai" => $idTagSala,
            "idempresa" => $idempresa
        ])::exec();


        if ($tipoTag->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tipoTag->errorMessage());
            return [];
        }

        if ($toFillSelect) {
            foreach ($tipoTag->data as $tag) {
                $arrRetorno[$tag['idtagtipo']] = $tag['tagtipo'];
            }

            return $arrRetorno;
        }

        return $tipoTag->data;
    }

    public static function buscarTagPorId($idTag, $idempresa)
    {
        $tag = SQL::ini(TagQuery::buscarTagPorId(), [
            "idtag" => $idTag,
            "idempresa" => $idempresa
        ])::exec();


        if ($tag->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tag->errorMessage());
            return '';
        }

        return $tag->data[0];
    }

    public static function iniciarViagem($idTag, $kmInicial, $idEmpresa, $usuario)
    {
        $iniciarViagem = SQL::ini(TagQuery::iniciarViagem(), [
            'idtag' => $idTag,
            'kminicial' => $kmInicial,
            'idempresa' => $idEmpresa,
            'usuario' => $usuario
        ])::exec();

        if ($iniciarViagem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $iniciarViagem->errorMessage());
            return false;
        }

        return $iniciarViagem->lastInsertId();
    }

    public static function finalizarViagem($idControleViagem)
    {
        $iniciarViagem = SQL::ini(TagQuery::iniciarViagem(), [
            'idcontroleviagem' => $idControleViagem
        ])::exec();

        if ($iniciarViagem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $iniciarViagem->errorMessage());
            return false;
        }

        return $iniciarViagem->lastInsertId();
    }

    public static function buscarHistoricoAlteracao($idobjeto, $campo)
    {
        $results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoAlteracao(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => "tag",
            "campo" => " AND h.campo = '$campo'"
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarPrateleiras($idEmpresa)
    {
        $tags = SQL::ini(TagQuery::buscarPrateleiras(), [
            "idempresa" => $idEmpresa
        ])::exec();

        if ($tags->error()) {
            parent::error(__CLASS__, __FUNCTION__, $tags->errorMessage());
            return [];
        }

        return $tags->data;
    }
}
