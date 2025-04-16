<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/lote_query.php");
require_once(__DIR__ . "/../querys/indicacaouso_query.php");
require_once(__DIR__ . "/../querys/sgdoc_query.php");
require_once(__DIR__ . "/../querys/portaria_query.php");
require_once(__DIR__ . "/../querys/prodserv_query.php");
require_once(__DIR__ . "/../querys/prativobj_query.php");
require_once(__DIR__ . "/../querys/analiseteste_query.php");
require_once(__DIR__ . "/../querys/prodservcfop_query.php");
require_once(__DIR__ . "/../querys/prodservforn_query.php");
require_once(__DIR__ . "/../querys/tipoprodserv_query.php");
require_once(__DIR__ . "/../querys/interpretacao_query.php");
require_once(__DIR__ . "/../querys/unidadevolume_query.php");
require_once(__DIR__ . "/../querys/prodservformula_query.php");
require_once(__DIR__ . "/../querys/prodservtipoopcao_query.php");
require_once(__DIR__ . "/../querys/prodservtipoalerta_query.php");
require_once(__DIR__ . "/../querys/prodservtipoopcaoespecie_query.php");

//Controllers
require_once(__DIR__ . "/../controllers/_rep_controller.php");
require_once(__DIR__ . "/../controllers/natop_controller.php");
require_once(__DIR__ . "/../controllers/solcom_controller.php");
require_once(__DIR__ . "/../controllers/pessoa_controller.php");
require_once(__DIR__ . "/../controllers/empresa_controller.php");
require_once(__DIR__ . "/../controllers/plantel_controller.php");
require_once(__DIR__ . "/../controllers/unidade_controller.php");
require_once(__DIR__ . "/../controllers/contaitem_controller.php");
require_once(__DIR__ . "/../controllers/tipoprodserv_controller.php");
require_once(__DIR__ . "/../controllers/prodservformula_controller.php");
require_once(__DIR__ . "/../controllers/especiefinalidade_controller.php");

class ProdServController extends Controller
{
    // ----- FUNÇÕES -----
    public static function buscarPrAtivPorIdObjetoTipoObjetoETipo($idobjeto, $tipoobjeto, $tipo)
    {
        $atividades = SQL::ini(PrativObjQuery::buscarPrAtivPorIdObjetoTipoObjetoETipo(), [
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto,
            'tipo' => $tipo
        ])::exec();

        if ($atividades->error()) {
            parent::error(__CLASS__, __FUNCTION__, $atividades->errorMessage());
            return [];
        }

        return $atividades->data;
    }

    public static function buscarPorChavePrimaria($id)
    {
        $prodServ = SQL::ini(ProdServQuery::buscarPorChavePrimaria(), [
            'pkval' => $id
        ])::exec();

        if ($prodServ->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodServ->errorMessage());
            return [];
        }

        return $prodServ->data[0];
    }

    public static function listarProdservTipoProdServ($idprodservs)
    {
        $results = SQL::ini(TipoProdServQuery::listarProdservTipoProdServ(), [
            'idprodservs' => $idprodservs,
            "idempresa" => getidempresa('t.idempresa', 'contaitem')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarFornecedorProdserv($idpessoas, $idnfs, $idempresa)
    {
        $results = SQL::ini(ProdservQuery::buscarFornecedorProdserv(), [
            "idnfs" => $idnfs,
            "idempresa" => $idempresa,
            "idpessoas" => $idpessoas
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarProdutoSaida($idpessoa)
    {
        $prodservPorSessionIdempresa = share::otipo('cb::usr')::prodservPorSessionIdempresa("p.idprodserv");

        $prodservPorSessionIdempresa = $prodservPorSessionIdempresa ? $prodservPorSessionIdempresa : 'AND p.idempresa = ' . cb::idempresa();
        $limitProdutos = "";

        /*$verifcarProdutoCliente = self::verifcarProdutoCliente($idpessoa);
        if($verifcarProdutoCliente['qtdLinhas'] > 0) {
            $limitProdutos = "AND p.idprodserv IN (".$verifcarProdutoCliente['dados']['idprodserv'].")";
            $results = SQL::ini(ProdservQuery::buscarProdutoSaidaMateriais(), [
                "prodservPorSessionIdempresa" => $prodservPorSessionIdempresa,
                "limitProdutos" => $limitProdutos
            ])::exec();
        } else {*/
        $results = SQL::ini(ProdservQuery::buscarProdutoSaida(), [
            "prodservPorSessionIdempresa" => $prodservPorSessionIdempresa
        ])::exec();
        //}

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function verifcarProdutoCliente($idpessoa)
    {
        $results = SQL::ini(ProdservQuery::verifcarProdutoCliente(), [
            "idpessoa" => $idpessoa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            if ($results->data[0]['idprodserv'] == NULL) {
                $dados['dados'] = "";
                $dados['qtdLinhas'] = 0;
            } else {
                $dados['dados'] = $results->data[0];
                $dados['qtdLinhas'] = $results->numRows();
            }

            return $dados;
        }
    }

    public static function buscarValorProdutoFormulado($idprodservformula)
    {
        $results = SQL::ini(ProdservQuery::buscarValorProdutoFormulado(), [
            "idprodservformula" => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data[0];
        }
    }

    public static function buscarProdServPorTipoObjEGetIdEmpresa($tipoObj, $getIdEmpresa, $autocomplete = false, $toFillSelect = false, $coluna = 'descr', $orderBy = 'descr')
    {
        $orderBy = " ORDER BY p.$orderBy";

        $condicao = '';

        if ($tipoObj) {
            $condicao = ' AND (';
            $ou = '';

            if (!is_array($tipoObj)) {
                $tipoObj = explode(',', $tipoObj);
            }

            foreach ($tipoObj as $valor) {
                $condicao .= "$ou " . str_replace("'", '', $valor) . " = 'Y'";
                $ou = ' OR';
            }
            $condicao .= ')';
        }

        $prodServ = SQL::ini(ProdServQuery::buscarProdServPorCondicao(), [
            'condicao' => $condicao,
            'getidempresa' => '',
            'orderby' => $orderBy
        ])::exec();

        if ($prodServ->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodServ->errorMessage());
            return "";
        }

        if ($toFillSelect) {
            $arrRetorno = [];

            foreach ($prodServ->data as $item) {
                $arrRetorno[$item['idprodserv']] = $item[$coluna];
            }

            return $arrRetorno;
        }

        if ($autocomplete) {
            $arrRetorno = [];

            foreach ($prodServ->data as $key => $item) {
                $arrRetorno[$key]['value'] = $item['idprodserv'];
                $arrRetorno[$key]['label'] = $item['descr'];
            }

            return $arrRetorno;
        }

        return $prodServ->data;
    }

    public static function buscarProservVendaMaterial()
    {
        $results = SQL::ini(ProdservQuery::buscarProservVendaMaterial(), [
            "getidempresa" => getidempresa('idempresa', 'prodserv')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['idprodserv']] = $_valor['descrcurta'];
            }
            return $lista;
        }
    }

    public static function buscarUnidadeVolume()
    {
        $results = SQL::ini(UnidadeVolumeQuery::buscarUnidadeVolume())::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['un']] = $_valor['descr'];
            }
            return $lista;
        }
    }

    public static function buscarProdutoOuServicoComprado($nothere)
    {
        $solcomUnidadeCbUserIdempresa = share::otipo('cb::usr')::solcomUnidadeCbUserIdempresa("p.idprodserv");

        $results = SQL::ini(ProdservQuery::buscarProdutoOuServicoComprado(), [
            "solcomUnidadeCbUserIdempresa" => $solcomUnidadeCbUserIdempresa,
            "nothere" => $nothere
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarProdutoOuServicoFabricado($nothere)
    {
        $solcomUnidadeCbUserIdempresa = share::otipo('cb::usr')::solcomUnidadeCbUserIdempresa("p.idprodserv");

        $results = SQL::ini(ProdservQuery::buscarProdutoOuServicoFabricado(), [
            "solcomUnidadeCbUserIdempresa" => $solcomUnidadeCbUserIdempresa,
            "nothere" => $nothere
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarVersaoObjetoPorTipoObjetoEVersao($idobjeto, $tipoobjeto, $versaoobjeto)
    {
        $results = SQL::ini(ObjetoJsonQuery::buscarVersaoObjetoPorTipoObjetoEVersao(), [
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto,
            'versaoobjeto' => $versaoobjeto


        ])::exec();
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function inserirAuditoria($arrayAuditoria)
    {
        $results = SQL::ini(AuditoriaQuery::inserirAuditoriaFluxo(), $arrayAuditoria)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function inserirObjetoJson($arrayObjetoJson)
    {
        $results = SQL::ini(ObjetoJsonQuery::inserirObjetoJson(), $arrayObjetoJson)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function listarAnaliseBioterio($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::listarAnaliseBioterio(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarProdutosFormalizacao($tipo)
    {
        $tipoNegocio = ($tipo == 'PRODUTO') ? "AND p.fabricado = 'Y' " : "";
        $results = SQL::ini(ProdservQuery::buscarProdutosFormalizacao(), [
            "tipo" => $tipo,
            "getidempresa" => getidempresa('p.idempresa', 'prodserv'),
            "tipoNegocio" => $tipoNegocio
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function listarProcessosVinculados($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::listarProcessosVinculados(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function listarCertificadosProcesso($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::listarCertificadosProcesso(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function verificarSeExisteTipoUnidadeProducao($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::verificarSeExisteTipoUnidadeProducao(), [
            "idobjeto" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->numRows();
        }
    }

    public static function listarInterpretacoesRelacionadasServicoSelecionadas($idprodserv)
    {
        $results = SQL::ini(InterpretacaoQuery::listarInterpretacoesRelacionadasServicoSelecionadas(), [
            "idtipoteste" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function listarPlantelPorIdobjetoTipoobjetoProdservAtiva($idobjeto, $tipoobjeto)
    {
        return PlantelController::listarPlantelPorIdobjetoTipoobjetoProdservAtiva($idobjeto, $tipoobjeto);
    }

    public static function listarProdservFormulaPlantel($idprodserv)
    {
        return ProdservformulaController::listarProdservFormulaPlantel($idprodserv);
    }

    public static function listarProdservCfop($idprodserv)
    {
        $results = SQL::ini(ProdservCfopQuery::listarProdservCfop(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarAnaliseQst($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::buscarAnaliseQst(), [
            "idprodserv" => $idprodserv
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

    public static function buscarAnaliseTestePorIdAnaliseQst($idanaliseqst)
    {
        $results = SQL::ini(AnaliseTesteQuery::buscarAnaliseTestePorIdAnaliseQst(), [
            "idanaliseqst" => $idanaliseqst
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

    public static function buscarIdTipoTestePorTipoEIdTipoUnidade($idtipounidade, $tipo)
    {
        $arrTipoTeste = [];
        $results = SQL::ini(ProdservQuery::buscarIdTipoTestePorTipoEIdTipoUnidade(), [
            "idtipounidade" => $idtipounidade,
            "tipo" => $tipo,
            "getidempresa" => getidempresa('p.idempresa', 'prodserv')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $arrTipoTeste[$_valor['idtipoteste']] = $_valor['descr'];
            }
            return $arrTipoTeste;
        }
    }

    public static function listarFormulas($idprodserv)
    {
        return ProdservformulaController::listarFormulas($idprodserv);
    }

    public static function listarConfiguracaoAlerta($idprodserv)
    {
        $results = SQL::ini(ProdservTipoAlertaQuery::buscarConfiguracaoAlerta(), [
            "idtipoteste" => $idprodserv
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

    public static function listarConfiguracaoAgente($idprodserv)
    {
        $results = SQL::ini(ProdservTipoAlertaQuery::buscarConfiguracaoAgente(), [
            "idtipoteste" => $idprodserv
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

    public static function listarProdservTipoOpcaoEspecie($idprodserv)
    {
        $results = SQL::ini(ProdservTipoOpcaoEspecieQuery::listarProdservTipoOpcaoEspecie(), [
            "idprodserv" => $idprodserv
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

    public static function listarEspecieFinalidadePlantelOrdenadoPorPlantel($idempresa)
    {
        $arrPlantel = [];
        $listarPlantel = EspecieFinalidadeController::listarEspecieFinalidadePlantelOrdenadoPorPlantel($idempresa);
        foreach ($listarPlantel as $_plantel) {
            $arrPlantel[$_plantel["idespeciefinalidade"]] = $_plantel["nome"];
        }
        return $arrPlantel;
    }

    public static function listarTipoRelatorioPorIdProdserv($idprodserv)
    {
        return _RepController::listarTipoRelatorioPorIdProdserv($idprodserv);
    }

    public static function buscarProdservVinculoServico($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::buscarProdservVinculoServico(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarTagSalaVinculo($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::buscarTagSalaETagTipoVinculo(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarMediaDiaria($idprodserv, $idunidade, $idprodservformula = NULL, $consumodias = 60)
    {
        //trazer o valor de conversão
        $valconv = traduzid('prodserv', 'idprodserv', 'valconv', $idprodserv);
        ($valconv == 0) ? $auxconv = 1 : $auxconv = $valconv;

        //iniciar variaveis de total de calculo
        $tqtdd = 0;
        $tqtdc = 0;
        if (!empty($idprodservformula)) {
            $in_str = " AND l.idprodservformula = " . $idprodservformula;
        }

        //pegar os o consumo
        $results = SQL::ini(ProdservQuery::buscarMediaDiaria(), [
            "idunidade" => $idunidade,
            "in_str" => $in_str,
            "idprodserv" => $idprodserv,
            "consumodias" => $consumodias
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            $mediadiaria = 0;
            return $mediadiaria;
        } else {
            foreach ($results->data as $mediaDiaria) {
                $tqtdd += $mediaDiaria["qtdd"];
                $tqtdc += $mediaDiaria["qtdc"];
            }

            $mediadiaria = ($tqtdd) / $consumodias;
            if ($mediadiaria < 0) {
                $mediadiaria *= -1;
            }
            return $mediadiaria;
        }
    }

    public static function buscarProdutoServicoComprado()
    {

        $prodservPorSessionIdempresa = share::otipo('cb::usr')::prodservPorSessionIdempresa("p.idprodserv");

        $prodservPorSessionIdempresa = $prodservPorSessionIdempresa ? $prodservPorSessionIdempresa : 'AND p.idempresa = ' . cb::idempresa();

        $results = SQL::ini(ProdservQuery::buscarProdutoServicoComprado(), [
            "prodservPorSessionIdempresa" => $prodservPorSessionIdempresa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarProdutoServicoCompradoPorIdTipoProdserv($strtipo, $idtipoprodserv)
    {
        $results = SQL::ini(ProdservQuery::buscarProdutoServicoCompradoPorIdTipoProdserv(), [
            "idempresa" => getidempresa('p.idempresa', 'prodserv'),
            "strtipo" => $strtipo,
            "idtipoprodserv" => $idtipoprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data;
        }
    }

    public static function buscarProdServPorIdSolmatUnidadePadraoEIdEmpresa($idSolMat, $unidadePadrao, $idEmpresa)
    {
        $solMatItem = SolmatController::buscarSolMatItemPorIdSolMatGroupConcat($idSolMat);
        $clausulaProdServ = "AND p.idprodserv not in({$solMatItem['stidprodserv']})";

        if ($solMatItem['stidprodserv'] == '' || $_GET['modulo'] != 'soltag') {
            $clausulaProdServ = '';
        }

        $clausulaTipoUnidade = "";

        if ($unidadePadrao) {
            $tipounidadepadrao = traduzid('unidade', 'idunidade', 'idtipounidade', $unidadePadrao);
            $clausulaTipoUnidade .= " AND un.idtipounidade = $tipounidadepadrao ";
        }

        $clausulaTag = '';

        if ($_GET['_modulo'] == 'soltag') {
            $clausulaTag = ' AND p.idtipoprodserv IN(191,172,192,190,35,101,110)';
        }

        $prodServ = SQL::ini(ProdservQuery::buscarProdServPorClausulasProdServTipoUnidadeTagEIdEmpresa(), [
            'clausulaprodserv' => $clausulaProdServ,
            'clausulatipounidade' => $clausulaTipoUnidade,
            'clausulatag' => $clausulaTag,
            'idempresa' => $idEmpresa
        ])::exec();

        if ($prodServ->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodServ->errorMessage());
            return [];
        }

        return $prodServ->data;
    }

    public static function atualizarNcmProdServ($idnf)
    {
        $results = SQL::ini(ProdservQuery::atualizarNcmProdServ(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function buscarProdservfornPorId($idprodservforn)
    {
        $results = SQL::ini(ProdservfornQuery::buscarProdservfornPorId(), [
            "idprodservforn" => $idprodservforn
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            return $results->data[0];
        }
    }

    public static function buscarProdservfornPorIdprodservIdnf($idprodserv, $idnf)
    {
        $results = SQL::ini(ProdservfornQuery::buscarProdservfornPorIdprodservIdnf(), [
            "idprodserv" => $idprodserv,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarTipoProdservPorApp($id, $coluna)
    {
        if ($coluna == 'idtipoprodserv') {
            $condicao = " AND tp.idtipoprodserv = $id";
        }

        $results = SQL::ini(TipoProdServQuery::buscarTipoProdservPorApp(), [
            "condicao" => $condicao,
            "idobjeto" => getModsUsr("LPS")
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarRotuloFormulaPorId($idprodservformula)
    {
        $results = SQL::ini(ProdServFormulaQuery::buscarRotuloFormulaPorId(), [
            "idprodservformula" => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNaoFormuladosPorIdProdserv($idprodserv, $tipo)
    {
        $results = SQL::ini(ProdServQuery::buscarNaoFormuladosPorIdProdserv(), [
            "idprodserv" => $idprodserv,
            "tipo" => $tipo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarQtdFormulaPlantelObjeto($idplantelobjeto)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarFormulaPlantelObjeto(), [
            "idplantelobjeto" => $idplantelobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        } else {
            return $results->numRows();
        }
    }

    public static function buscarQtdProdservFornPorIdprodserv($idprodserv, $condicaoStatus = NULL)
    {
        $results = SQL::ini(ProdservfornQuery::buscarQtdProdservFornPorIdprodserv(), [
            "idprodserv" => $idprodserv,
            "condicaoStatus" => $condicaoStatus
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->numRows();
        }
    }

    public static function buscarProdservFornPorIdprodserv($idprodserv)
    {
        $results = SQL::ini(ProdservfornQuery::buscarProdservFornPorIdprodserv(), [
            "idprodserv" => $idprodserv
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

    public static function buscarProdservFornProdservPorIdprodserv($idprodserv)
    {
        $results = SQL::ini(ProdservfornQuery::buscarProdservFornProdservPorIdprodserv(), [
            "idprodserv" => $idprodserv
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

    public static function buscarIdProdservFornPorIdprodservIdForn($idprodserv, $idpessoa)
    {
        $results = SQL::ini(ProdservfornQuery::buscarIdProdservFornPorIdprodservIdForn(), [
            "idprodserv" => $idprodserv,
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarIdProdservPorIdpessoaIdCodForn($cprodforn, $idpessoa, $idempresa)
    {
        $results = SQL::ini(ProdservfornQuery::buscarIdProdservPorIdpessoaIdCodForn(), [
            "cprodforn" => $cprodforn,
            "idempresa" => $idempresa,
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function atualizarValoresCalculoEstoqueProdserv($arrayAtualizaProdserv)
    {
        $results = SQL::ini(ProdServQuery::atualizarValoresCalculoEstoqueProdserv(), $arrayAtualizaProdserv)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function buscarProdservFormulaInsPorIdProdservFormulaIns($idprodservformulains)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarProdservFormulaInsPorIdProdservFormulaIns(), [
            "idprodservformulains" => $idprodservformulains
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarIdunidadePorTipoUnidade($idtipounidade, $idempresa)
    {
        return UnidadeController::buscarIdunidadePorTipoUnidade($idtipounidade, $idempresa);
    }

    public static function buscarObjempresaPorIdObjempresa($idobjempresa)
    {
        return EmpresaController::buscarObjempresaPorIdObjempresa($idobjempresa);
    }

    public static function buscarUnidadeObjeto($idempresa, $idobjeto, $tipoobjeto)
    {
        return UnidadeController::buscarUnidadeObjeto($idempresa, $idobjeto, $tipoobjeto);
    }

    public static function atualizarIdTipoProdservPorIdProdserv($idprodserv)
    {
        return NfController::atualizarIdTipoProdservPorIdProdserv($idprodserv);
    }

    public static function atualizarProdservContaItemPorIdContaItem($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::atualizarProdservContaItemPorIdContaItem(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function atualizarIdContaItemPorIdProdserv($idprodserv)
    {
        return NfController::atualizarIdContaItemPorIdProdserv($idprodserv);
    }

    public static function atualizarIdProdservPorIdSolcomItem($idprodserv, $idsolcomitem)
    {
        return SolcomController::atualizarIdProdservPorIdSolcomItem($idprodserv, $idsolcomitem);
    }

    public static function buscarProdservPorTipoEStatusEIdEmpresa($status, $tipo)
    {
        $results = SQL::ini(ProdservQuery::buscarProdservPorTipoEStatusEIdEmpresa(), [
            "status" => $status,
            "tipo" => $tipo,
            "andIdempresa" => getidempresa('p.idempresa', 'prodserv')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $prodserv = [];
            foreach ($results->data as $_results) {
                foreach ($_results as $key => $value) {
                    $prodserv[$_results['idprodserv']][$key] = $value;
                }
            }

            return $prodserv;
        }
    }

    public static function buscarInsumosServicoEmAndamento($idprodserv, $idprodservformula, $satusFormula, $statusFormulaIns)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarInsumosServicoEmAndamento(), [
            'idprodserv' => $idprodserv,
            'idprodservformula' => $idprodservformula,
            'satusFormula' => $satusFormula,
            'statusFormulaIns' => $statusFormulaIns
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarInsumosAtividadeNaoAtivos($idprativ, $idprodservprproc)
    {
        $results = SQL::ini(ProdservFormulaInsQuery::buscarInsumosAtividadeNaoAtivos(), [
            "idprativ" => $idprativ,
            "idprodservprproc" => $idprodservprproc
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarProdutoPorIdProdserv($idprodserv)
    {
        $results = SQL::ini(ProdservQuery::buscarProdutoPorIdProdserv(), [
            'idprodserv' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarInsumoPorIdProdserv($idprodserv)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarInsumoPorIdProdserv(), [
            'idprodserv' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarProdservPrprocPorIdPrProc($idprproc)
    {
        $results = SQL::ini(ProdservQuery::buscarProdservPrprocPorIdPrProc(), [
            'idprproc' => $idprproc
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarProcessoLigadoFormula($idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarProcessoLigadoFormula(), [
            'idprodservformula' => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarQtdProcessoServico($idprodservformula)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarProcessoServico(), [
            'idprodservformula' => $idprodservformula
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function retornarSqlProdserv($idprodserv)
    {
        $sql = SQL::mount(ProdservQuery::buscarInfoProdserv(), [
            'idprodserv' => $idprodserv
        ]);

        return $sql;
    }

    public static function buscarprodServPorIdUnidadeEIdEmpresa($idUnidade, $idEmpresa)
    {
        $prodServ = SQL::ini(ProdservQuery::buscarprodServPorIdUnidadeEIdEmpresa(), [
            'idunidade' => $idUnidade,
            'idempresa' => $idEmpresa
        ])::exec();

        if ($prodServ->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodServ->errorMessage());
            return [];
        }

        return $prodServ->data;
    }

    public static function buscarProdServDisponivelParaVinculoEmUnidades($idUnidade, $idEmpresa)
    {
        $prodServ = SQL::ini(ProdservQuery::buscarProdServDisponivelParaVinculoEmUnidades(), [
            'idunidade' => $idUnidade,
            'idempresa' => $idEmpresa
        ])::exec();

        if ($prodServ->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodServ->errorMessage());
            return [];
        }

        return $prodServ->data;
    }

    public static function buscarIdProdservFormulaPorIdProdserv($idprodserv)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarIdProdservFormulaPorIdProdserv(), [
            'idprodserv' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarIdProdservFormulaPorIdProdservArray($idprodserv)
    {
        $results = SQL::ini(ProdservFormulaQuery::buscarIdProdservFormulaPorIdProdserv(), [
            'idprodserv' => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarProdservVinculadoAoLote()
    {
        $results = SQL::ini(ProdservQuery::buscarProdservVinculadoAoLote(), [
            'getidempresa' => getidempresa('p.idempresa', 'prodserv')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $prodservVinculadasLote = $results->data;
            foreach ($prodservVinculadasLote as $_prodserv) {
                $prodservs[$_prodserv['idprodserv']]['descr'] = $_prodserv['descr'];
            }

            return $prodservs;
        }
    }

    public static function listarEntradaSaidaProdserv($idprodserv, $data_inicial, $data_final)
    {
        $results = SQL::ini(ProdservQuery::buscarEntradaProdserv(), [
            "idprodserv" => $idprodserv,
            "datainicial" => implode("-", array_reverse(explode("/", $data_inicial))),
            "datafinal" => implode("-", array_reverse(explode("/", $data_final)))
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {

            $arrIdLote = [];
            foreach ($results->data as $_nf) {
                array_push($arrIdLote, $_nf['idlote']);
            }

            $_idlotes = implode(",", $arrIdLote);

            $arrNf['nf'] = $results->data;
            $arrNf['lote'] = empty($_idlotes) ? [] : self::buscarConsumoLotes($_idlotes);

            return $arrNf;
        }
    }

    public static function buscarConsumoLotes($_idlotes)
    {
        $results = SQL::ini(LoteQuery::buscarConsumoLotes(), [
            "arr_idlote" => $_idlotes
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            $arrIdProdServ = [];
            foreach ($results->data as $_lotes) {
                $i = 0;
                foreach ($_lotes as $_lotecol => $_lotevalorcol) {
                    $arrIdProdServ[$_lotes['idnf']][$_lotes['idlote']][$_lotes['idobjetoconsumoespec']][$_lotecol] = $_lotevalorcol;
                }
            }

            return $arrIdProdServ;
        }
    }


    public static function buscarProdservServicosExames()
    {
        $results = SQL::ini(ProdservQuery::buscarProdservServicosExames(), [])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }

    public static function verificaConsomeNaTransferencia($prodserv)
    {
        if ($prodserv['imobilizado'] == 'Y') {
            return false;
        }

        if ($prodserv['insumo'] == 'Y') {
            return false;
        }

        if ($prodserv['material'] == 'Y') {
            return true;
        }
    }
    public static function configuracaoTipoProduto($prodserv)
    {
        foreach ($prodserv as $key => $value) {
            if ($value === 'Y') {
                return $key;
            }
        }

        return null; // Retorna null se não encontrar nenhuma chave com 'Y'
    }
    public static function buscaforecastcriado($idempresa)
    {
        $results = SQL::ini(ProdservQuery::buscaForecastCriado(), [
            "idempresa" => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        $result = array_column($results->data, 'exercicio');
        //vamos retornar a string que o javascript vai usar
        $string = "'" . implode("', '", $result) . "'";

        return $string;
    }

    public static function BuscarForecastComprasLigadosForecastVenda($idforecastvenda)
    {
        $results = SQL::ini(ProdservQuery::BuscaForecastComprasLigadosForecastVenda(), [
            "idforecastvenda" => $idforecastvenda
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

    public static function BuscarSubcategoriaPorProdserv($idformalizacao, $idunidade, $idempresa)
    {
        $results = SQL::ini(ProdservQuery::buscarSubcategoriaPorProdserv(), [
            'idformalizacao' => $idformalizacao,
            'idunidade' => $idunidade,
            'idempresa' => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarHistoricoDeAlteração($idobjeto, $tipoobjeto, $campo)
    {
        $results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoAlteracao(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto,
            "campo" => " AND h.campo = '$campo'"
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }
    // ----- FUNÇÕES -----

    //----- AUTOCOMPLETE -----
    public static function listarProdservTipoOpcaoPorIdprodserv($idprodserv)
    {
        $results = SQL::ini(ProdservTipoOpcaoQuery::listarProdservTipoOpcaoPorIdprodserv(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function listarProdutosVinculados($tipo, $idprodserv)
    {
        $arrProdutos = [];
        $results = SQL::ini(ProdservQuery::listarProdutosVinculados(), [
            "tipo" => $tipo,
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            $i = 0;
            foreach ($results->data as $produtos) {
                $arrProdutos[$i]["value"] = $produtos["idprodserv"];
                $arrProdutos[$i]["label"] = $produtos["descr"];
                $i++;
            }
            return $arrProdutos;
        }
    }

    public static function listarServicosVinculados($tipo, $idprodserv)
    {
        $arrProdutos = [];
        $results = SQL::ini(ProdservQuery::listarServicosVinculados(), [
            "tipo" => $tipo,
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            $i = 0;
            foreach ($results->data as $produtos) {
                $arrProdutos[$i]["value"] = $produtos["idprodserv"];
                $arrProdutos[$i]["label"] = $produtos["descr"];
                $i++;
            }
            return $arrProdutos;
        }
    }

    public static function listarTagSalaVinculados($idempresa, $idprodserv)
    {
        $arrTagSala = [];
        $results = SQL::ini(ProdservQuery::listarTagSalaVinculados(), [
            "idempresa" => $idempresa,
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            $i = 0;
            foreach ($results->data as $tagSala) {
                $arrTagSala[$i]["value"] = $tagSala["idtag"];
                $arrTagSala[$i]["label"] = $tagSala["descricao"];
                $i++;
            }
            return $arrTagSala;
        }
    }

    public static function listarTagTipo()
    {

        $arrTagTipo = [];
        $results = SQL::ini(TagTipoQuery::buscarTodosTagTipo(), [])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            $i = 0;
            foreach ($results->data as $tagTipo) {
                $arrTagTipo['array'][$tagTipo["idtagtipo"]] = $tagTipo["tagtipo"];
                $arrTagTipo['json'][$i]["value"] = $tagTipo["idtagtipo"];
                $arrTagTipo['json'][$i]["label"] = $tagTipo["tagtipo"];
                $i++;
            }
            return $arrTagTipo;
        }
    }

    public static function listarInterpretacoesRelacionadasServico($idprodserv)
    {
        $arrInterpretacao = [];
        $results = SQL::ini(InterpretacaoQuery::listarInterpretacoesRelacionadasServico(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            $i = 0;
            foreach ($results->data as $_interpretacao) {
                $arrInterpretacao[$i]["value"] = $_interpretacao["idinterpretacao"];
                $arrInterpretacao[$i]["label"] = $_interpretacao["titulo"];
                $i++;
            }
            return $arrInterpretacao;
        }
    }

    public static function listarContaItemAtivoShare()
    {
        $i = 0;
        $arrCotacaoParaAlterar = [];
        $resultsContaItemAtivoShare = ContaItemController::buscarContaItemAtivoShare();

        foreach ($resultsContaItemAtivoShare as $_dadosContaItemAtivoShare) {
            $arrCotacaoParaAlterar[$i]["value"] = $_dadosContaItemAtivoShare["idcontaitem"];
            $arrCotacaoParaAlterar[$i]["label"] = $_dadosContaItemAtivoShare["contaitem"];
            $i++;
        }

        return $arrCotacaoParaAlterar;
    }

    public static function buscarEmpresaQueNaoExisteNaObjetoEmpresa($objeto, $idobjeto)
    {
        return EmpresaController::buscarEmpresaQueNaoExisteNaObjetoEmpresa($objeto, $idobjeto);
    }

    public static function listarEmpresaVinculadaObjetoEmpresa($objeto, $idobjeto)
    {
        return EmpresaController::listarEmpresaVinculadaObjetoEmpresa($objeto, $idobjeto);
    }

    public static function buscarUnidadesDisponiveisPorUnidadeObjeto($idobjeto, $tipoobjeto, $idempresa, $idtipounidade = NULL)
    {
        return UnidadeController::buscarUnidadesDisponiveisPorUnidadeObjeto($idobjeto, $tipoobjeto, $idempresa, $idtipounidade);
    }

    public static function buscarContaItemProdservContaItem($idprodserv)
    {
        $contaItem = ContaItemController::buscarContaItemProdservContaItem($idprodserv);
        return $contaItem[0];
    }

    public static function listarContaItemTipoProdservTipoProdServ($idcontaitem)
    {
        $arrTipoProdserv = [];
        $tipoProdserv = TipoProdServController::listarContaItemTipoProdservTipoProdServ($idcontaitem);
        foreach ($tipoProdserv as $_dadostipoProdserv) {
            $arrTipoProdserv[$_dadostipoProdserv["idtipoprodserv"]] = $_dadostipoProdserv["tipoprodserv"];
        }
        return $arrTipoProdserv;
    }

    public static function listarNatopCfop()
    {
        $arrCfop = [];
        $listarCfop = NatopController::listarNatopCfop();
        foreach ($listarCfop as $_cfop) {
            $arrCfop[$_cfop["idcfop"]] = $_cfop["ncfop"];
        }
        return $arrCfop;
    }

    public static function listarPortaria()
    {
        $arrPortaria = [];
        $results = SQL::ini(PortariaQuery::listarPortaria())::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return array();
        } else {
            foreach ($results->data as $_portaria) {
                $arrPortaria[$_portaria["idportaria"]] = $_portaria["portaria"];
            }
            return $arrPortaria;
        }
    }

    public static function buscarTipoProdservPorAppFillSelect()
    {
        $arrContaItemPessoa = [];
        $condicao = " AND tp.idempresa = " . cb::idempresa();
        $results = SQL::ini(TipoProdServQuery::buscarTipoProdservPorApp(), [
            "condicao" => $condicao,
            "idobjeto" => getModsUsr("LPS")
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_contaItemPessoa) {
                $arrContaItemPessoa[$_contaItemPessoa["idtipoprodserv"]] = $_contaItemPessoa['tipoprodserv'] . " - " . $_contaItemPessoa['contaitem'];
            }
        }

        return $arrContaItemPessoa;
    }

    public static function buscarProdservObjetoVinculoPorIdobjetoTipoobjetoTipo($idprodserv, $tipoobjeto, $tipo)
    {
        $results = SQL::ini(ObjetoVinculoQuery::buscarProdservObjetoVinculoPorIdobjetoTipoobjetoTipo(), [
            "idprodserv" => $idprodserv,
            "tipoobjeto" => $tipoobjeto,
            "tipo" => $tipo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function listarFillSelectProdutoPorTipoEAtivo($tipo)
    {
        $results = SQL::ini(ProdservQuery::buscarProdutoPorTipoEAtivo(), [
            "tipo" => $tipo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return parent::toFillSelect($results->data);
        }
    }

    public static function listarProdservPorTipoEIdEmpresa($tipo)
    {
        $results = SQL::ini(ProdservQuery::listarProdservPorTipoEIdEmpresa(), [
            "tipo" => $tipo,
            "getidempresa" => getidempresa('idempresa', 'prodserv')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        } else {
            $i = 0;
            $arrProdserv = [];
            foreach ($results->data as $prodserv) {
                $arrProdserv[$i]["idprodserv"] = $prodserv["idprodserv"];
                $arrProdserv[$i]["descr"] = $prodserv["descr"];
                $i++;
            }

            return $arrProdserv;
        }
    }

    public static function buscarHistoricoAlteracao($idobjeto, $campo)
    {
        if (is_array($campo)) {
            $campo = "AND campo in ('" . implode("','", $campo) . "')";
        }

        $results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoAlteracao(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => "prodserv",
            "campo" => $campo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return (count($results->data) > 0) ? $results->data : "";
        }
    }

    public static function buscarQuantidadeLotePorProduto($idprodserv)
    {

        $results = SQL::ini(LoteQuery::buscarQuantidadeLotePorProduto(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function listarDocsParaVinculo()
    {

        $results = SQL::ini(SgdocQuery::buscarSgDocDisponiveisParaVinculoEmProdserv(), [
            "idempresa" => cb::idempresa()
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $arrjson = [];
            foreach ($results->data as $k => $v) {
                $arrjson['formatado'][$v['idsgdoc']] = $v['titulo'];
            }
            $arrjson['json'] = parent::toJson($results->data);

            return ($arrjson);
        }
    }

    public static function buscarInidicacaoUso()
    {
        $results = SQL::ini(IndicacaoUsoQuery::buscarInidicacaoUsoAtivo())::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['idindicacaouso']] = $_valor['descricao'];
            }
            return $lista;
        }
    }

    public static function buscarQtdValorLoteProdserv($idprodserv, $dataInicial, $dataFinal)
    {
        $results = SQL::ini(ProdservQuery::buscarQtdValorLoteProdserv(), [
            "idprodserv" => $idprodserv,
            "dataInicial" => $dataInicial,
            "dataFinal" => $dataFinal
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarProdservAtivasSemVenda($idEmpresa, $autocomplete = false)
    {
        $prodserv = SQL::ini(ProdservQuery::buscarProdservAtivasSemVenda(), [
            'idempresa' => $idEmpresa
        ])::exec();

        if ($prodserv->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodserv->errorMessage());
            return [];
        }

        if ($autocomplete) {
            $arr = [];

            foreach ($prodserv->data as $key => $item) {
                $arr[$key] = [
                    'label' => $item['descr'],
                    'value' => $item['idprodserv']
                ];
            }

            return $arr;
        }

        return $prodserv->data;
    }

    public static function buscarProdservInsumoAtivasSemVenda($idEmpresa, $idunidade, $autocomplete = false)
    {
        $prodserv = SQL::ini(ProdservQuery::buscarProdservInsumoAtivasSemVenda(), [
            'idempresa' => $idEmpresa,
            'idunidade' => $idunidade
        ])::exec();

        if ($prodserv->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodserv->errorMessage());
            return [];
        }

        if ($autocomplete) {
            $arr = [];

            foreach ($prodserv->data as $key => $item) {
                $arr[$key] = [
                    'label' => $item['descr'],
                    'value' => $item['idprodserv']
                ];
            }

            return $arr;
        }

        return $prodserv->data;
    }

    public static function inserirVinculosNoTagTipo($idTagTipo, $idTag)
    {
        $results = SQL::ini(ObjetoVinculoQuery::inserirVinculosNoTagTipo(), [
            'idTagTipo' => $idTagTipo,
            'idTag' => $idTag
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }

        return true;
    }

    public static function removerVinculosNoTagTipo($idProdserv, $idTagTipo)
    {
        $results = SQL::ini(ObjetoVinculoQuery::removerVinculosNoTagTipo(), [
            'idTagTipo' => $idTagTipo,
            'idprodserv' => $idProdserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }

        return true;
    }

    public static function buscarVinculosTipoTagPorIdProdserv($idProdserv, $idTagTipo, $idTag)
    {
        $results = SQL::ini(ObjetoVinculoQuery::buscarVinculosTipoTagPorIdProdserv(), [
            'idTagTipo' => $idTagTipo,
            'idTag' => $idTag,
            'idprodserv' => $idProdserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }

    public static function buscarLotesVencidosOuProximos($intervalo, $idEmpresa, $idUnidadePadrao)
    {
        $intervaloSQL = "";
        $clausulaUnidade = '';

        if ($idUnidadePadrao) $clausulaUnidade = "AND f.idunidade IN($idUnidadePadrao)";

        if (intval($intervalo) > 0) $intervaloSQL = "OR (`l`.`vencimento` BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-%d') AND (DATE_FORMAT(CURDATE(), '%Y-%m-%d') + INTERVAL $intervalo DAY))";

        $produtos = SQL::ini(ProdservQuery::buscarLotesVencidosOuProximos(), [
            'idempresa' => $idEmpresa,
            'clausulaunidade' => $clausulaUnidade,
            'intervaloSQL' => $intervaloSQL
        ])::exec();

        if ($produtos->error()) {
            parent::error(__CLASS__, __FUNCTION__, $produtos->errorMessage());
            return [];
        }

        return $produtos->data;
    }

    public static function pegaCustoPeriodo($idprodserv, $datainicial, $datafinal)
    {

        $produtos = SQL::ini(ProdservQuery::pegaCustoPeriodo(), [
            'idprodserv' => $idprodserv,
            "datainicial" => implode("-", array_reverse(explode("/", $datainicial))),
            "datafinal" => implode("-", array_reverse(explode("/", $datafinal)))
        ])::exec();

        if ($produtos->error()) {
            parent::error(__CLASS__, __FUNCTION__, $produtos->errorMessage());
            return [];
        }

        return $produtos->data;
    }

    public static function listaProdservTranferencia($idempresa)
    {
        $results = SQL::ini(ProdservQuery::listaProdservTranferencia(), [
            "idempresa" => $idempresa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }
    public static function buscarExamesPorIdUnidade($idUnidade, $idPessoa)
    {
        $results = SQL::ini(ProdservQuery::buscarExamesPorIdUnidade(), [
            "idunidade" => $idUnidade,
            'idpessoa' => $idPessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }

    public static function buscarProdutosVendaPorEmpresa($idEmpresa, $tipoProdserv = '', $subcategoria = '')
    {
        $results = SQL::ini(ProdservQuery::buscarProdutosVendaPorEmpresa(), [
            "idempresa" => $idEmpresa,
            "tipoprodserv" => ($tipoProdserv != '' ? " AND t.tipoprodserv = '$tipoProdserv' " : ""),
            "subcategoria" => ($subcategoria != '' ? " AND p.idprodserv in ($subcategoria) " : "")
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }

    public static function buscaDadosProdutoForecast($idEmpresa, $idtipoProdserv = '', $especie = '', $subcategoria = '', $exercicio, $planejado = 0)
    {
        if ($idEmpresa == 1) {
            $results = SQL::ini(ProdservQuery::buscaDadosProdutoForecastLaudo(), [
                "exercicio" => $exercicio,
                "idempresa" => $idEmpresa,
                "especie" => $especie,
                "tipoprodserv" => ($idtipoProdserv != '' ? " AND t.idtipoprodserv = '$idtipoProdserv' " : ""),
                "subcategoria" => ($subcategoria != '' ? " AND a.idprodserv in ($subcategoria) " : ""),

                "planejado" => [
                    "", //todos
                    " AND a2.planejado = 0 ", //não planejado
                    " AND a2.planejado BETWEEN 1 and 11 ", // em planejamento
                    " AND a2.planejado = 12 "
                ][$planejado] //planejado
            ])::exec();
        } else {
            $results = SQL::ini(ProdservQuery::buscaDadosProdutoForecast(), [
                "exercicio" => $exercicio,
                "idempresa" => $idEmpresa,
                "especie" => $especie,
                "tipoprodserv" => ($idtipoProdserv != '' ? " AND t.idtipoprodserv = '$idtipoProdserv' " : ""),
                "subcategoria" => ($subcategoria != '' ? " AND a.idprodserv in ($subcategoria) " : ""),

                "planejado" => [
                    "", //todos
                    " AND a2.planejado = 0 ", //não planejado
                    " AND a2.planejado BETWEEN 1 and 11 ", // em planejamento
                    " AND a2.planejado = 12 "
                ][$planejado] //planejado
            ])::exec();
        }

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        $categorias = [];
        foreach ($results->data as $item) {
            if (!isset($categorias[$item['idtipoprodserv']])) {
                $categorias[$item['idtipoprodserv']]['tipoprodserv'] = $item['tipoprodserv'];
                $categorias[$item['idtipoprodserv']]['idtipoprodserv'] = $item['idtipoprodserv'];
                $categorias[$item['idtipoprodserv']]['contaitem'] = $item['contaitem'];
                $categorias[$item['idtipoprodserv']]['produtos'] = [];
            }
            $categorias[$item['idtipoprodserv']]['produtos'][] = $item;
        }

        return [$categorias, $results->numRows()];
    }

    //----- AUTOCOMPLETE -----

    // ----- Variáveis de apoio -----
    public static $tipoProdserv = array(
        'PRODUTO' => 'Produto',
        'SERVICO' => 'Serviço'
    );

    public static $status = array(
        'ATIVO' => 'Ativo',
        'INATIVO' => 'Inativo'
    );

    public static $CondicaoSimNaoVazio = array(
        '' => '',
        'Y' => 'Sim',
        'N' => 'Não'
    );

    public static $CondicaoSimNao = array(
        'S' => 'Sim',
        'N' => 'Não'
    );

    public static $CondicaoYesNo = array(
        'Y' => 'Sim',
        'N' => 'Não'
    );

    public static $prioridadeCompra = array(
        'ALTA' => 'Alta',
        'MEDIA' => 'Média',
        'BAIXA' => 'Baixa'
    );

    public static $finalidade = array(
        'COMERCIO' => 'Comércio',
        'INDUSTRIA' => 'Industria'
    );

    public static $origem = array(
        '0' => '[0]-Nacional',
        '1' => '[1]-Estrangeira  Importação',
        '2' => '[2]-Estrangeira Adquirida no mercado interno',
        '3' => '[3]-Nacional c/ cont. imp > 40% e < 70%',
        '5' => '[5]-Nacional c/ cont. imp menos ou igual 40%',
        '6' => '[6]-Estrangeira - Importação Direta - Sem Similar Nacional',
        '7' => '[7]-Estrangeira - Adquirida no mercado interno - Sem Similar Nacional'
    );

    public static $cst = array(
        '00' => '[00]-Integral',
        '10' => '[10]-Tributada e com cob. do ICMS por ST',
        '20' => '[20]-redução de BC',
        '40' => '[40]-Isenta',
        '41' => '[41]-Não tributada',
        '50' => '[50]-Suspensão',
        '51' => '[51]-Tributação com Diferimento',
        '90' => '[90]-Outras',
        '60' => '[60]-ICMS cobrado ant. por ST',
        '101' => '[101]-Simples Nacional e CSOSN=101',
        '102' => '[102]-Simples Nacional e CSOSN=102'
    );

    public static $modbc = array(
        '0' => '[0]-Margem Valor Agregado (%)',
        '1' => '[1]-Pauta (Valor)',
        '2' => '[2]-Preço Tabelado Máx. (valor)',
        '3' => '[3]-Valor da Operação'
    );

    public static $pisConfins = array(
        '01' => '[01]-Operação Tributável (base de cálculo = valor da operação alíquota normal (cumulativo/não cumulativo))',
        '02' => '[02]-Operação Tributável (base de cálculo = valor da operação (alíquota diferenciada))',
        '03' => '[03]-Operação Tributável (base de cálculo = quantidade vendida x alíquota por unidade de produto)',
        '04' => '[04]-Operação Tributável (tributação monofásica, alíquota zero)',
        '05' => '[05]-Operação Tributável (Substituição Tributária)',
        '06' => '[06]-Operação Tributável (alíquota zero)',
        '07' => '[07]-Operação Isenta da Contribuição',
        '08' => '[08]-Operação Sem Incidência da Contribuição',
        '09' => '[09]-Operação com Suspensão da Contribuição',
        '49' => '[49]-Outras Operações de Saída',
        '50' => '[50]-Operação com Direito a Crédito - Vinculada Exclusivamente a Receita Tributada no Mercado Interno',
        '51' => '[51]-Operação com Direito a Crédito - Vinculada Exclusivamente a Receita Não Tributada no Mercado Interno',
        '52' => '[52]-Operação com Direito a Crédito – Vinculada Exclusivamente a Receita de Exportação',
        '53' => '[53]-Operação com Direito a Crédito - Vinculada a Receitas Tributadas e Não-Tributadas no Mercado Interno',
        '54' => '[54]-Operação com Direito a Crédito - Vinculada a Receitas Tributadas no Mercado Interno e de Exportação',
        '55' => '[55]-Operação com Direito a Crédito - Vinculada a Receitas Não-Tributadas no Mercado Interno e de Exportação',
        '56' => '[56]-Operação com Direito a Crédito - Vinculada a Receitas Tributadas e Não-Tributadas no Mercado Interno, e de Exportação',
        '60' => '[60]-Crédito Presumido - Operação de Aquisição Vinculada Exclusivamente a Receita Tributada no Mercado Interno',
        '61' => '[61]-Crédito Presumido - Operação de Aquisição Vinculada Exclusivamente a Receita Não-Tributada no Mercado Interno',
        '62' => '[62]-Crédito Presumido - Operação de Aquisição Vinculada Exclusivamente a Receita de Exportação',
        '63' => '[63]-Crédito Presumido - Operação de Aquisição Vinculada a Receitas Tributadas e Não-Tributadas no Mercado Interno',
        '64' => '[64]-Crédito Presumido - Operação de Aquisição Vinculada a Receitas Tributadas no Mercado Interno e de Exportação',
        '65' => '[65]-Crédito Presumido - Operação de Aquisição Vinculada a Receitas Não-Tributadas no Mercado Interno e de Exportação',
        '66' => '[66]-Crédito Presumido - Operação de Aquisição Vinculada a Receitas Tributadas e Não-Tributadas no Mercado Interno, e de Exportação',
        '67' => '[67]-Crédito Presumido - Outras Operações',
        '70' => '[70]-Operação de Aquisição sem Direito a Crédito',
        '71' => '[71]-Operação de Aquisição com Isenção',
        '72' => '[72]-Operação de Aquisição com Suspensão',
        '73' => '[73]-Operação de Aquisição a Alíquota Zero',
        '74' => '[74]-Operação de Aquisição; sem Incidência da Contribuição',
        '75' => '[75]-Operação de Aquisição por Substituição Tributária',
        '98' => '[98]-Outras Operações de Entrada',
        '99' => '[99]-Outras Operações'
    );

    public static $ipi = array(
        '49' => '[49]-Outras Entradas',
        '50' => '[50]-Saída tributada',
        '51' => '[51]-Saída tributada com alíquota zero',
        '52' => '[52]-Saída Isenta',
        '53' => '[53]-Saída não-tributada',
        '54' => '[54]-Saída imune',
        '55' => '[55]-Saída com suspensão',
        '99' => '[99]-Outras Saídas'
    );

    public static $origemcfop = array(
        'DENTRO' => 'Dentro',
        'FORA' => 'Fora'
    );

    public static $tipocertanalise = array(
        'DROP' => 'Drop',
        'DESCRITIVO' => 'Descritivo'
    );

    public static $armazanagem = array(
        '' => '',
        'PLACAS' => 'Placas',
        'TUBOS' => 'Tubos',
        'FRASCOS' => 'Frascos',
        'CAIXA' => 'Caixa'
    );

    public static $modopart = array(
        'PC' => 'Partida Comum',
        'PP' => 'Partida Piloto',
        'EXP' => 'Experimental'
    );

    public static $formafarm = array(
        'LÍQUIDA' => 'Líquida',
        'LIOFILIZADA' => 'Liofilizada',
        'PÓ' => 'Pó',
        'SÓLIDA' => 'Sólida'
    );

    public static $modelo = array(
        'DESCRITIVO' => 'Descritivo',
        'DROP' => 'Drop',
        'SELETIVO' => 'Seletivo',
        'UPLOAD' => 'Upload',
        'DINÂMICO' => 'Dinâmico',
        'DINAMICOREFERENCIA' => 'Dinâmico de referência',
    );

    public static $modo = array(
        'IND' => 'Individual',
        'AGRUP' => 'Agrupado'
    );

    public static $tipogmt = array(
        'GMT' => 'GMT',
        'ART' => 'ART',
        'PERC' => 'PERC',
        'SOMA' => 'SOMA',
        'N/A' => 'N/A'
    );

    public static $_justificativa = array(
        '' => '',
        'ERRO NO CADASTRO' => 'Erro no cadastro',
        'OUTROS' => 'Outros'
    );

    //Colocar em ordem Alfabética: 
    public static $laboratorio = array(
        '' => '',
        'ANALISESTERCEIRIZADAS' => 'Análises Terceirizadas',
        'BACTERIOLOGIA' => 'Bacteriologia',
        'HISTOPATOLOGIA' => 'Histopatologia',
        'PCR' => 'PCR',
        'VIROLOGIA' => 'Virologia',
    );

    //Colocar em ordem Alfabética: 
    public static $tipoteste = array(
        '' => '',
        'ANTIBIOGRAMA' => 'Antibiograma',
        'CONFECCAODELAMINAS' => 'Confecção de Lâminas',
        'CONTAGEMCOLIFORMES' => 'Contagem Coliformes',
        'HEMOPARASITA' => 'Hemoparasita',
        'HISTOPATOLOGICO' => 'Histopatológico',
        'IMUNO-HISTOQUIMICA' => 'Imuno-Histoquímica',
        'ISOLAMENTO' => 'Isolamento',
        'MIC' => 'MIC',
        'MICOLOGICO' => 'Micológico',
        'PCRCOMPLEMENTARVIROLOGIA' => 'PCR Complementar Virologia',
        'PCRDIAGNOSTICO' => 'PCR Diagnóstico',
        'PCRDIAGNOSTICOBACTERIOLOGIA' => 'PCR Diagnóstico Bacteriologia',
        'PCRISOLBACTERIOLOGIA' => 'PCR ISOL Bacteriologia',
        'PCRISOLVIROLOGIA' => 'PCR ISOL Virologia',
        'PCRTIPBACTERIOLOGIA' => 'PCR TIP Bacteriologia',
        'PCRTIPIFICACAOBACTERIOLOGIA' => 'PCR Tipificação Bacteriologia',
        'PCRTRIAGEMVIROLOGIA' => 'PCR Triagem Virologia',
        'SEQUENCIAMENTO' => 'Sequenciamento',
        'SOROLOGIA' => 'Sorologia',
    );

    public static $tipoAgente = array(
        '' => '',
        'VIRAL' => 'Viral',
        'BACTERIANO' => 'Bacteriano',
        'VIRALBACTERIANO' => 'Viral + Bacteriano'
    );
    // ----- Variáveis de apoio -----
}
