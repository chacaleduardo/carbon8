<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/nf_query.php");
require_once(__DIR__ . "/../querys/nfitem_query.php");
require_once(__DIR__ . "/../querys/nfitemxml_query.php");
require_once(__DIR__ . "/../querys/nfpendencia_query.php");
require_once(__DIR__ . "/../querys/nfitemacao_query.php");

// CONTROLLERS
require_once(__DIR__ . "/../controllers/unidade_controller.php");

class NfController extends Controller
{
    // ----- FUNÇÕES -----
    public static function buscarNfPorTipoObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor, $cancelado)
    {
        $results = SQL::ini(NfQuery::buscarNfPorTipoObjetoSoliPor(), [
            "idobjetosolipor" => $idobjetosolipor,
            "idempresa" => getidempresa('n.idempresa', 'nf'),
            "tipoobjetosolipor" => $tipoobjetosolipor,
            "cancelado" => ($cancelado == true ? " AND n.status IN ('CANCELADO', 'REPROVADO') "  : "")
        ])::exec();
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarNtItens($idnfs)
    {
        $results = SQL::ini(NfItemQuery::buscarNtItens(), [
            "idnfs" => $idnfs
        ])::exec();
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarValoresNfitem($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarValoresNfitem(), [
            "idnf" => $idnf,
            "idempresa" => getidempresa('i.idempresa', 'cotacao')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarValoresNfitemJoinNfitem($idnfitem)
    {
        $results = SQL::ini(NfItemQuery::buscarValoresNfitemJoinNfitem(), [
            "idnfitem" => $idnfitem,
            "idempresa" => getidempresa('i.idempresa', 'cotacao')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarCtePorIdNfe($idnfe, $idobjetosolipor)
    {
        $results = SQL::ini(NfItemQuery::buscarCtePorIdNfe(), [
            "idnfe" => substr($idnfe, 3),
            "idobjetosolipor" => $idobjetosolipor,
            "tipoobjetosolipor" => 'nf',
            "tiponf" => "'T', 'M'"
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarCte($idobjetosolipor)
    {
        $results = SQL::ini(NfItemQuery::buscarCte(), [
            "idobjetosolipor" => $idobjetosolipor,
            "tipoobjetosolipor" => 'nf',
            "tiponf" => "'T', 'M'"
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarProdservPelaNf($idcotacao)
    {
        $results = SQL::ini(NfQuery::buscarProdservPelaNf(), [
            "idobjetosolipor" => $idcotacao,
            "tipoobjetosolipor" => 'cotacao',
            "idempresa" => getidempresa('n.idempresa', 'nf')
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarItensNfPorIdProdserv($idcotacao, $idprodserv)
    {
        $results = SQL::ini(NfQuery::buscarItensNfPorIdProdserv(), [
            "idobjetosolipor" => $idcotacao,
            "tipoobjetosolipor" => 'cotacao',
            "idempresa" => getidempresa('n.idempresa', 'nf'),
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarSolicitacaoComprasAssociadoCotacao($idcotacao)
    {
        $results = SQL::ini(NfQuery::buscarSolicitacaoComprasAssociadoCotacao(), [
            "idobjetosolipor" => $idcotacao,
            "tipoobjetosolipor" => 'cotacao'
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarItensNfPorIdNf($idnf)
    {
        $results = SQL::ini(NfQuery::buscarItensNfPorIdNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarIdNfPorTipoObjetoStatusIdpessoa($idobjetosolipor, $tipoobjetosolipor, $status, $idpessoa)
    {
        $results = SQL::ini(NfQuery::buscarIdNfPorTipoObjetoStatusIdpessoa(), [
            "idobjetosolipor" => $idobjetosolipor,
            "tipoobjetosolipor" => $tipoobjetosolipor,
            "status" => $status,
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarFornecedoresPertencentesCotacao($idobjetosolipor, $tipoobjetosolipor, $cond_where, $idprodserv)
    {
        $results = SQL::ini(NfQuery::buscarFornecedoresPertencentesCotacao(), [
            "idobjetosolipor" => $idobjetosolipor,
            "tipoobjetosolipor" => $tipoobjetosolipor,
            "cond_where" => $cond_where,
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarNfPorIdnf($idnf)
    {
        $results = SQL::ini(NfQuery::buscarNfPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFornecedoresPertencentesIdnf($idnf, $idprodserv)
    {
        $results = SQL::ini(NfQuery::buscarFornecedoresPertencentesIdnf(), [
            "idnf" => $idnf,
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarDadosNfItemPorIdNfItem($idnfitem)
    {
        $results = SQL::ini(NfItemQuery::buscarDadosNfItemPorIdNfItem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfPorIdNfENfe($idnfitem)
    {
        $results = SQL::ini(NfQuery::buscarNfPorIdNfENfe(), [
            "idnf" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfPorId($idnf)
    {
        $results = SQL::ini(NfQuery::buscarNfPorId(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfitemPorIdnfitem($idnfitem)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemPorIdnfitem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarNfitemPorIdnf($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemPorIdnf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarItensPorIdNf($idnf, $dtemissao)
    {
        $results = SQL::ini(NfQuery::buscarItensPorIdNf(), [
            "dtemissao" => $dtemissao,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarDadosNfPorIdNfItem($idnfitem)
    {
        $results = SQL::ini(NfItemQuery::buscarDadosNfPorIdNfItem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigemNew($idnf, $tipoobjetoitem)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigemNew(), [
            "idnforigem" => $idnf,
            "tipoobjetoitem" => $tipoobjetoitem
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

    public static function buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigem($idobjetoitem, $tipoobjetoitem, $idnforigem)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemPorIdobjetoTipoobjetoEIdNfOrigem(), [
            "idobjetoitem" => $idobjetoitem,
            "tipoobjetoitem" => $tipoobjetoitem,
            "idnforigem" => $idnforigem
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

    public static function buscarNfPorIdNfDeslocamento($idobjetosolipor, $tipoobjetosolipor, $idpessoa, $sinal, $idnf)
    {
        $results = SQL::ini(NfQuery::buscarNfPorIdNfDeslocamento(), [
            "idobjetosolipor" => $idobjetosolipor,
            "tipoobjetosolipor" => $tipoobjetosolipor,
            "idpessoa" => $idpessoa,
            "sinal" => $sinal,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfPessoaPorIdNf($idnf)
    {
        $results = SQL::ini(NfQuery::buscarNfPessoaPorIdNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarProdutoNfPorIdNf($idnf)
    {
        $results = SQL::ini(NfQuery::buscarProdutoNfPorIdNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarRateioNfItem($idnotafiscal, $tipoobjeto)
    {
        $results = SQL::ini(NfItemQuery::buscarRateioNfItem(), [
            "idnotafiscal" => $idnotafiscal,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfItemIdPessoaNuloNfe($idnf, $nfe)
    {
        $results = SQL::ini(NfItemQuery::buscarNfItemIdPessoaNuloNfe(), [
            "idnf" => $idnf,
            "nfe" => $nfe
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->numRows();
        }
    }

    public static function buscarValorNfitemXmlNfItem($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarValorNfitemXmlNfItem(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfItemXml($idnfitemxml)
    {
        $results = SQL::ini(NfItemXmlQuery::buscarNfItemXml(), [
            "idnfitemxml" => $idnfitemxml
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfItemXmlNfItem($idnfitemxml, $idnf)
    {
        $results = SQL::ini(NfItemXmlQuery::buscarNfItemXmlNfItem(), [
            "idnfitemxml" => $idnfitemxml,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNFProdservForn($idprodserv, $idnf)
    {
        $results = SQL::ini(NfQuery::buscarNFProdservForn(), [
            "idprodserv" => $idprodserv,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarGrupoESTipoObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor, $idcontaitem)
    {
        $results = SQL::ini(NfQuery::buscarGrupoESTipoObjetoSoliPor(), [
            "idobjetosolipor" => $idobjetosolipor,
            "tipoobjetosolipor" => $tipoobjetosolipor,
            "idcontaitem" => $idcontaitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarNfPorRefnfeETipoNf($refnfe, $tiponf)
    {
        $results = SQL::ini(NfQuery::buscarNfPorRefnfeETipoNf(), [
            "refnfe" => $refnfe,
            "tiponf" => $tiponf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarXmlNfItem($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarXmlNfItem(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarProdutoItemProdservQueNaoExisteXml($idnf, $idnfitemxml, $idprodserv = '', $consumo)
    {

        if ($consumo != "Y") {
            $strand = " AND p.idprodserv NOT IN (SELECT x.idprodserv FROM nfitemxml x WHERE x.idnf = i.idnf AND x.status = 'Y' and (x.idprodserv = 0 OR x.idprodserv IS NOT NULL) group by x.prodservdescr having count(*) > 0 AND (x.idprodserv <> 0)) ";
        } else {
            $strand = "";
        }


        $results = SQL::ini(NfItemQuery::buscarProdutoItemProdservQueNaoExisteXml(), [
            "idnf" => $idnf,
            "idnfitemxml" => $idnfitemxml,
            "idprodserv" => $idprodserv,
            "strand" => $strand
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarSeExisteConversaoMoeda($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarSeExisteConversaoMoeda(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->numRows();
        }
    }

    public static function listarItensCadastrados($idnf, $idobjetosolipor)
    {
        $origem = self::buscarLoconsPorNfItem($idobjetosolipor);
        $consumo = ($origem > 0) ? "lc.idobjeto = i.idnfitem AND lc.tipoobjeto = 'nfitem'" : "lc.idobjetoconsumoespec = i.idnfitem AND lc.tipoobjetoconsumoespec = 'nfitem'";
        $results = SQL::ini(NfItemQuery::listarItensCadastrados(), [
            "idnf" => $idnf,
            "joinConsumo" => $consumo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarLoconsPorNfItem($idobjetosolipor)
    {
        $results = SQL::ini(NfQuery::buscarLoconsPorNfItem(), [
            "idobjetosolipor" => $idobjetosolipor
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->numRows();
        }
    }

    public static function listarItensSemCadastro($idnf)
    {
        $results = SQL::ini(NfItemQuery::listarItensSemCadastro(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarRateioNfItemProdserv($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarRateioNfItemProdserv(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarNfitemContaItem($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarNfitemContaItem(), [
            "idnf" => $idnf
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

    public static function buscarNfitemContaItemRateio($idnf, $idprodserv = NULL)
    {
        if (!empty($idprodserv)) {
            $andIdprodserv = " AND i.idprodserv IN ($idprodserv)";
        }

        $results = SQL::ini(NfItemQuery::buscarNfitemContaItemRateio(), [
            "idnf" => $idnf,
            "idprodserv" => $andIdprodserv
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

    public static function buscarNfContaItemRateio($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarNfContaItemRateio(), [
            "idnf" => $idnf
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

    public static function buscarNfItemSolcom($idnfitem)
    {
        $results = SQL::ini(NfItemQuery::buscarNfItemSolcom(), [
            "idnfitem" => $idnfitem
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

    public static function buscarNfItemPorNfe($idnf, $nfe)
    {
        $results = SQL::ini(NfItemQuery::buscarNfItemPorNfe(), [
            "idnf" => $idnf,
            "nfe" => $nfe
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

    public static function listarItensNfParaDuplicar($idnf)
    {
        $results = SQL::ini(NfItemQuery::listarItensNfParaDuplicar(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarNfItemContaPagar($idobjetoitem, $tipoobjetoitem)
    {
        $results = SQL::ini(NfItemQuery::buscarNfItemContaPagar(), [
            "idobjetoitem" => $idobjetoitem,
            "tipoobjetoitem" => $tipoobjetoitem
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

    public static function atualizarNfParaCanceladoComStatusDiferenteConcluido($idfluxostatus, $idobjetosolipor, $tipoobjetosolipor)
    {
        $results = SQL::ini(NfQuery::atualizarNfParaCanceladoComStatusDiferenteConcluido(), [
            "idfluxostatus" => $idfluxostatus,
            "idobjetosolipor" => $idobjetosolipor,
            "tipoobjetosolipor" => $tipoobjetosolipor
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function atualizarQtdNfItem($qtd, $idnfitem)
    {
        $results = SQL::ini(NfitemQuery::atualizarQtdNfItem(), [
            "qtd" => $qtd,
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function atualizarNfXmlRetEnvioNfe($xmlret, $envionfe, $idnf)
    {
        $results = SQL::ini(NfQuery::atualizarNfXmlRetEnvioNfe(), [
            "xmlret" => $xmlret,
            "envionfe" => $envionfe,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function apagarNfItemXmlPorIdNf($idnf)
    {
        $results = SQL::ini(NfItemXmlQuery::apagarNfItemXmlPorIdNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }
    public static function atualizarNfXmlVinculo($idnf)
    {
        $results = SQL::ini(NfQuery::atualizarNfXmlVinculo(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function atualizarNfTotalSubtotal($total, $subtotal, $idnf)
    {
        $results = SQL::ini(NfQuery::atualizarNfTotalSubtotal(), [
            "total" => $total,
            "subtotal" => $subtotal,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function buscarNfEFluxoStatusPorTipoObjetoSoliPor($idobjetosolipor, $tipoobjetosolipor)
    {
        $results = SQL::ini(NfQuery::buscarNfEFluxoStatusPorTipoObjetoSoliPor(), [
            "idobjetosolipor" => $idobjetosolipor,
            "tipoobjetosolipor" => $tipoobjetosolipor
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

    public static function buscarNfporServDesc($prodservdescr)
    {
        $results = SQL::ini(NfItemQuery::buscarNfporServDesc(), [
            "prodservdescr" => $prodservdescr
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

    public static function buscarNfPendencia($idnf)
    {
        $results = SQL::ini(NfPendenciaQuery::buscarNfPendencia(), [
            "idnf" => $idnf
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

    public static function buscarIdNfeNfItemPorObsNotNULLEIdNf($idnf)
    {
        $results = SQL::ini(NfItemQuery::buscarIdNfeNfItemPorObsNotNULLEIdNf(), [
            "idnf" => $idnf
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

    public static function buscarNfPessoaPorIdNfe($idnfe)
    {
        $results = SQL::ini(NfQuery::buscarNfPessoaPorIdNfe(), [
            "idnfe" => $idnfe
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

    public static function buscarIdunidadePorTipoUnidade($idtipounidade, $idempresa)
    {
        return UnidadeController::buscarIdunidadePorTipoUnidade($idtipounidade, $idempresa);
    }

    public static function inserirNf($idpessoa, $idempresa, $idobjetosolipor, $idfluxostatus, $idunidade, $tipoobjetosolipor, $status, $tpnf, $tiponf, $usuario)
    {
        $results = SQL::ini(NfQuery::inserirNf(), [
            "idpessoa" => $idpessoa,
            "idempresa" => $idempresa,
            "idobjetosolipor" => $idobjetosolipor,
            "idfluxostatus" => $idfluxostatus,
            "idunidade" => $idunidade,
            "tipoobjetosolipor" => $tipoobjetosolipor,
            "status" => $status,
            "tpnf" => $tpnf,
            "tiponf" => $tiponf,
            "usuario" => $usuario
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->lastInsertId();
        }
    }

    public static function inserirNfDuplicada($arrayInsertNf)
    {
        $results = SQL::ini(NfQuery::inserirNf(), $arrayInsertNf)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->lastInsertId();
        }
    }

    public static function inserirNfTransportadora($idpessoa, $idempresa, $idobjetosolipor, $idfluxostatus, $idunidade, $tipoobjetosolipor, $status, $tiponf, $usuario, $previsaoentrega, $idformapagamento, $subtotal, $total, $parcelas, $dtemissao)
    {
        $results = SQL::ini(NfQuery::inserirNfTransportadora(), [
            "idpessoa" => $idpessoa,
            "idempresa" => $idempresa,
            "idobjetosolipor" => $idobjetosolipor,
            "idfluxostatus" => $idfluxostatus,
            "idunidade" => $idunidade,
            "tipoobjetosolipor" => $tipoobjetosolipor,
            "status" => $status,
            "tiponf" => $tiponf,
            "usuario" => $usuario,
            "previsaoentrega" => $previsaoentrega,
            "idformapagamento" => $idformapagamento,
            "subtotal" => $subtotal,
            "total" => $total,
            "parcelas" => $parcelas,
            "dtemissao" => $dtemissao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->lastInsertId();
        }
    }

    public static function inserirNfItem($idnf, $tiponf, $idprodservforn, $idempresa, $un, $qtd, $nfe, $idprodserv, $idtipoprodserv, $idcontaitem, $usuario)
    {
        $results = SQL::ini(NfItemQuery::inserirNfItem(), [
            "idnf" => $idnf,
            "tiponf" => $tiponf,
            "idprodservforn" => $idprodservforn,
            "idempresa" => $idempresa,
            "un" => $un,
            "qtd" => $qtd,
            "qtdsol" => $qtd,
            "nfe" => $nfe,
            "idprodserv" => $idprodserv,
            "idtipoprodserv" => $idtipoprodserv,
            "idcontaitem" => $idcontaitem,
            "usuario" => $usuario
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function inserirNfItemAPP($arrayInsertNfItem)
    {
        $results = SQL::ini(NfItemQuery::inserirNfItemAPP(), $arrayInsertNfItem)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        } else {
            return $results->lastInsertId();
        }
    }

    public static function buscarPrevisaoEntregaPorIdNf($idnf)
    {
        $results = SQL::ini(NfQuery::buscarPrevisaoEntregaPorIdNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFornecedorPorNnfe($idpessoa, $nnfe, $idnf)
    {
        if (!empty($idnf)) {
            $condicao = " AND idnf != $idnf";
        } else {
            $condicao = "";
        }

        $results = SQL::ini(NfQuery::buscarFornecedorPorNnfe(), [
            "idpessoa" => $idpessoa,
            "nnfe" => $nnfe,
            "condicao" => $condicao
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarEnderecoPessoaNf($idnf)
    {
        $results = SQL::ini(NfQuery::buscarEnderecoPessoaNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function atualizarIdNfItemXmlNfItem($idprodserv, $idnfitemxml)
    {
        $results = SQL::ini(NfitemQuery::atualizarIdNfItemXmlNfItem(), [
            "idprodserv" => $idprodserv,
            "idnfitemxml" => $idnfitemxml
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function atualizarTransportadoraNf($idtransportadora, $idnf)
    {
        $results = SQL::ini(NfQuery::atualizarTransportadoraNf(), [
            "idtransportadora" => $idtransportadora,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function buscarLoteNfItemPorIndNf($valor, $idnf, $idprodserv = null)
    {
        $condicaoProdserv = empty($idprodserv) ? '' : " AND ni.idprodserv = $idprodserv";
        $results = SQL::ini(NfItemQuery::buscarLoteNfItemPorIndNf(), [
            "valor" => $valor,
            "idnf" => $idnf,
            "condicaoProdserv" => $condicaoProdserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarNfPorTipoNfEIdNf($tiponf, $idnf)
    {
        $results = SQL::ini(NfQuery::buscarNfPorTipoNfEIdNf(), [
            "tiponf" => $tiponf,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarEnvioLoteReservaPorIdLote($idlote, $tipoobjeto)
    {
        $results = SQL::ini(NfQuery::buscarEnvioLoteReservaPorIdLote(), [
            "idlote" => $idlote,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function atualizarNfIdnfeDtemissaoPorIdnf($idnfe, $dtemissao, $idnf)
    {
        $results = SQL::ini(NfQuery::atualizarNfIdnfeDtemissaoPorIdnf(), [
            "idnfe" => $idnfe,
            "dtemissao" => $dtemissao,
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function buscarNfePorIdNfItem($idnfitem)
    {
        $results = SQL::ini(NfQuery::buscarNfePorIdNfItem(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarDadosNfitemLote($idprodserv, $consumodiasgraf)
    {
        $results = SQL::ini(NfItemQuery::buscarDadosNfitemLote(), [
            "idprodserv" => $idprodserv,
            "consumodiasgraf" => $consumodiasgraf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarDadosNfitemServico($idprodserv, $consumodiasgraf)
    {
        $results = SQL::ini(NfItemQuery::buscarDadosNfitemServico(), [
            "idprodserv" => $idprodserv,
            "consumodiasgraf" => $consumodiasgraf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }

    public static function buscarFormuladosSemFormula($idprodserv, $consumodiasgraf)
    {
        $results = SQL::ini(NfItemQuery::buscarFormuladosSemFormula(), [
            "idprodserv" => $idprodserv,
            "consumodiasgraf" => $consumodiasgraf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarServicosFormuladosSemFormula($idprodserv, $consumodiasgraf)
    {
        $results = SQL::ini(NfItemQuery::buscarServicosFormuladosSemFormula(), [
            "idprodserv" => $idprodserv,
            "consumodiasgraf" => $consumodiasgraf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
    }

    public static function buscarCotacaoNfitem($idprodserv)
    {
        $results = SQL::ini(NfItemQuery::buscarCotacaoNfitem(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarQtdFornecedorNfItem($idprodservforn)
    {
        $results = SQL::ini(NfItemQuery::buscarQtdFornecedorNfItem(), [
            "idprodservforn" => $idprodservforn
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return 0;
        } else {
            return $results->numRows();
        }
    }

    public static function atualizarIdTipoProdservPorIdProdserv($idprodserv)
    {
        $results = SQL::ini(NfQuery::atualizarIdTipoProdservPorIdProdserv(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function atualizarIdContaItemPorIdProdserv($idprodserv)
    {
        $results = SQL::ini(NfQuery::atualizarIdContaItemPorIdProdserv(), [
            "idprodserv" => $idprodserv
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function buscarDataEnvioNfitem($idlote)
    {
        $results = SQL::ini(NfItemQuery::buscarDataEnvioNfitem(), [
            "idlote" => $idlote
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

    public static function buscarNnfePorIdNfItem($idnfitem)
    {
        $results = SQL::ini(NfItemQuery::buscarNnfePorIdNfItem(), [
            "idnfitem" => $idnfitem
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

    public static  function verificaFeriadoFds($timestamp)
    {
        $results = SQL::ini(NfQuery::verificaFeriadoFds(), [
            "timestamp" => $timestamp
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfPorIdpessoaIdempresaStatus($idpessoa, $idempresa, $status)
    {
        $results = SQL::ini(NfQuery::buscarNfPorIdpessoaIdempresaStatus(), [
            "idpessoa" => $idpessoa,
            "idempresa" => $idempresa,
            "status" => $status,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfItemAcao($idnfitem)
    {
        $results = SQL::ini(NfItemAcaoQuery::buscarNfItemAcao(), [
            "idnfitem" => $idnfitem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function inserirNfItemAcao($arrayInsertNfItemAcao)
    {
        $results = SQL::ini(NfItemAcaoQuery::inserirNfItemAcao(), $arrayInsertNfItemAcao)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function buscarUltimoValor($idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(NfItemAcaoQuery::buscarUltimoValor(), [
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFormaPagamentoPorIdNf($idnf)
    {
        $results = SQL::ini(NfQuery::buscarFormaPagamentoPorIdNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0]['formapagamento'];
        }
    }

    public static function buscarContaPagarItemPorIdNf($idnf)
    {
        $contaPagarItem = SQL::ini(NfQuery::buscarContaPagarItemPorIdNf(), [
            "idnf" => $idnf
        ])::exec();

        if ($contaPagarItem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $contaPagarItem->errorMessage());
            return [];
        }

        return $contaPagarItem->data[0];
    }

    public static function buscarValorImpostoTotalPorTotalItem($idnf)
    {
        $contaPagarItem = SQL::ini(NfQuery::buscarValorImpostoTotalPorTotalItem(), [
            "idnf" => $idnf
        ])::exec();

        if ($contaPagarItem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $contaPagarItem->errorMessage());
            return [];
        }

        return $contaPagarItem->data[0];
    }

    public static function buscarValorItem($idnf, $idprodserv)
    {
        $contaPagarItem = SQL::ini(NfQuery::buscarValorItem(), [
            "idnf" => $idnf,
            "idprodserv" => $idprodserv
        ])::exec();

        if ($contaPagarItem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $contaPagarItem->errorMessage());
            return [];
        }

        return $contaPagarItem->data[0]['valoritem'];
    }

    public static function buscarValorImpostoTotalItem($idprodserv, $tipo, $idnf = false)
    {
        $arrayImposto = [];

        $condicao = ($tipo == 'prodserv') ? " AND ni.idprodserv = '$idprodserv'" : " AND ni.idnf = '$idnf' AND ni.idprodserv = '$idprodserv'";
        $impostoItem = SQL::ini(NfQuery::buscarValorImpostoTotalItem(), [
            "condicao" => $condicao
        ])::exec();

        if ($impostoItem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $impostoItem->errorMessage());
            return [];
        }

        $dadosItem = $impostoItem->data[0];

        $impostoTotal = SQL::ini(NfQuery::buscarValorImpostoTotalPorTotalItem(), [
            "idnf" => $dadosItem['idnf']
        ])::exec();

        if ($impostoTotal->error()) {
            parent::error(__CLASS__, __FUNCTION__, $impostoTotal->errorMessage());
            return [];
        }

        $arrayImposto['idnf'] = $dadosItem['idnf'];
        $arrayImposto['internacional'] = $dadosItem['internacional'];
        $arrayImposto['vlritem'] = $dadosItem['vlritem'];
        $arrayImposto['valorcomimpostoitem'] = $dadosItem['valorcomimpostoitem'];
        $arrayImposto['valorcomimposto'] = $impostoTotal->data[0]['valorcomimposto'];

        return $arrayImposto;
    }

    public static function buscarCteVinculadasPorIdNf($idNf)
    {
        $cteVinculadas = SQL::ini(ObjetoVinculoQuery::buscarCteVinculadasPorIdNf(), [
            'idnf' => $idNf
        ])::exec();

        if ($cteVinculadas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $cteVinculadas->errorMessage());
            return [];
        }

        return $cteVinculadas->data;
    }

    public static function buscarComprasVinculadasPorIdNf($idNf)
    {
        $comprasVinculadas = SQL::ini(ObjetoVinculoQuery::buscarComprasVinculadasPorIdNf(), [
            'idnf' => $idNf
        ])::exec();

        if ($comprasVinculadas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $comprasVinculadas->errorMessage());
            return [];
        }

        return $comprasVinculadas->data;
    }
    // ----- FUNÇÕES ----- 
}
