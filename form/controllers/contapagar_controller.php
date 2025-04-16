<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/confcontapagar_query.php");
require_once(__DIR__ . "/../querys/contapagar_query.php");
require_once(__DIR__ . "/../querys/contapagaritem_query.php");

class ContaPagarController extends Controller
{
    public static function inserirValoresContaPagarItem($idempresa, $status, $idpessoa, $idcontaitem, $idobjetoorigem, $tipoobjetoorigem, $tipo, $visivel, $idformapagamento, $parcela, $parcelas, $datapagto, $valor, $usuario)
    {
        $results = SQL::ini(ContaPagarItemQuery::inserirValoresContaPagarItem(), [
            "idempresa" => $idempresa,
            "status" => $status,
            "idpessoa" => $idpessoa,
            "idcontaitem" => $idcontaitem,
            "idobjetoorigem" => $idobjetoorigem,
            "tipoobjetoorigem" => $tipoobjetoorigem,
            "tipo" => $tipo,
            "visivel" => $visivel,
            "idformapagamento" => $idformapagamento,
            "parcela" => $parcela,
            "parcelas" => $parcelas,
            "datapagto" => $datapagto,
            "valor" => $valor,
            "usuario" => $usuario
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function inserirParcelaSemIdContaItem($idempresa, $status, $idpessoa, $idobjetoorigem, $tipoobjetoorigem, $tipo, $visivel, $idformapagamento, $parcela, $parcelas, $datapagto, $valor, $usuario)
    {
        $results = SQL::ini(ContaPagarItemQuery::inserirParcelaAbertaComissao(), [
            "idempresa" => $idempresa,
            "status" => $status,
            "idpessoa" => $idpessoa,
            "idobjetoorigem" => $idobjetoorigem,
            "tipoobjetoorigem" => $tipoobjetoorigem,
            "tipo" => $tipo,
            "visivel" => $visivel,
            "idformapagamento" => $idformapagamento,
            "parcela" => $parcela,
            "parcelas" => $parcelas,
            "datapagto" => $datapagto,
            "valor" => $valor,
            "usuario" => $usuario
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function inserirContaPagarComIdContaItem($idempresa, $idcontaitem, $idagencia, $idpessoa, $tipoobjeto, $idobjeto, $parcela, $parcelas, $valor, $datapagto, $datareceb, $status, $idfluxostatus, $idformapagamento, $tipo, $visivel, $intervalo, $usuario)
    {
        $results = SQL::ini(ContaPagarQuery::inserirContaPagarComIdContaItem(), [
            "idempresa" => $idempresa,
            "idcontaitem" => $idcontaitem,
            "idagencia" => $idagencia,
            "idpessoa" => $idpessoa,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "parcela" => $parcela,
            "parcelas" => $parcelas,
            "valor" => $valor,
            "datapagto" => $datapagto,
            "datareceb" => $datareceb,
            "status" => $status,
            "idfluxostatus" => $idfluxostatus,
            "idformapagamento" => $idformapagamento,
            "tipo" => $tipo,
            "visivel" => $visivel,
            "intervalo" => $intervalo,
            "usuario" => $usuario
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function inserirContaPagarComIdContaItemArray($arrayInsertContaPagar)
    {
        $results = SQL::ini(ContaPagarQuery::inserirContaPagarComIdContaItem(), $arrayInsertContaPagar)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->lastInsertId();
        }
    }

    public static function inserirValoresContaPagarItemArray($arrayInsertContaPagar)
    {
        $results = SQL::ini(ContaPagarItemQuery::inserirValoresContaPagarItem(), $arrayInsertContaPagar)::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function inserirContaPagarSemIdContaItem($idempresa, $idagencia, $idpessoa, $tipoobjeto, $idobjeto, $parcela, $parcelas, $valor, $datapagto, $datareceb, $status, $idfluxostatus, $idformapagamento, $tipo, $visivel, $intervalo, $usuario)
    {
        $results = SQL::ini(ContaPagarQuery::inserirContaPagarSemIdContaItem(), [
            "idempresa" => $idempresa,
            "idagencia" => $idagencia,
            "idpessoa" => $idpessoa,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto,
            "parcela" => $parcela,
            "parcelas" => $parcelas,
            "valor" => $valor,
            "datapagto" => $datapagto,
            "datareceb" => $datareceb,
            "status" => $status,
            "idfluxostatus" => $idfluxostatus,
            "idformapagamento" => $idformapagamento,
            "tipo" => $tipo,
            "visivel" => $visivel,
            "intervalo" => $intervalo,
            "usuario" => $usuario
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
    }

    public static function apagarParcelasExistentes($tipoobjeto, $idobjeto)
    {
        $resultContaPagarItemJoinContaPagar = SQL::ini(ContaPagarQuery::apagarContaPagarItemPorTipoObjetoOrigemJoinContaPagar(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();
        if ($resultContaPagarItemJoinContaPagar->error()) {
            parent::error(__CLASS__, __FUNCTION__, $resultContaPagarItemJoinContaPagar->errorMessage());
        }

        $resultContaPagarItemPorIdContaPagar = SQL::ini(ContaPagarQuery::apagarContaPagarItemPorIdContaPagarJoinContaPagar(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();
        if ($resultContaPagarItemPorIdContaPagar->error()) {
            parent::error(__CLASS__, __FUNCTION__, $resultContaPagarItemPorIdContaPagar->errorMessage());
        }

        $resultContaPagarItemPorIdContaPagar = SQL::ini(ContaPagarQuery::apagarContaPagarItemPorTipoObjetoOrigem(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();
        if ($resultContaPagarItemPorIdContaPagar->error()) {
            parent::error(__CLASS__, __FUNCTION__, $resultContaPagarItemPorIdContaPagar->errorMessage());
        }

        $resultContaPagar = SQL::ini(ContaPagarQuery::apagarContaPagarPorTipoObjeto(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();
        if ($resultContaPagar->error()) {
            parent::error(__CLASS__, __FUNCTION__, $resultContaPagar->errorMessage());
        }
    }

    public static function buscarQuantidadeParcelasPorStatusTipoObjeto($tipoobjeto, $idobjeto, $status)
    {
        /*
		 * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
		 */
        $results = SQL::ini(ContaPagarQuery::buscarQuantidadeParcelasPorStatusTipoObjeto(), [
            "status" => $status,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0]['quant'];
        }
    }

    public static function buscarQuantidadeBoletosRemessaItem($tipoobjeto, $idobjeto)
    {
        /*
		 * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
		 */
        $results = SQL::ini(ContaPagarQuery::buscarQuantidadeBoletosRemessaItem(), [
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0]['quant'];
        }
    }

    public static function buscarCreditoVencidoPorPessoa($idpessoa)
    {
        /*
		 * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
		 */
        $results = SQL::ini(ContaPagarQuery::buscarCreditoVencidoPorPessoa(), [
            "idpessoa" => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarContaPagarFormaPagamentoPorIdObejtoOrigem($idobjetoorigem, $tipoobjetoorigem, $tipoobjeto, $idobjeto)
    {
        $results = SQL::ini(ContaPagarQuery::buscarContaPagarFormaPagamentoPorIdObejtoOrigem(), [
            "idobjetoorigem" => $idobjetoorigem,
            "tipoobjetoorigem" => $tipoobjetoorigem,
            "tipoobjeto" => $tipoobjeto,
            "idobjeto" => $idobjeto
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

    public static function buscarFaturaPorId($idcontapagar)
    {
        $results = SQL::ini(ContaPagarQuery::buscarFaturaPorId(), [
            "idcontapagar" => $idcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarContaPagarItem($idcontapagaritem)
    {
        $results = SQL::ini(ContaPagaritemQuery::buscarContaPagarItem(), [
            "idcontapagaritem" => $idcontapagaritem
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarNfContaPagar($idcontapagar, $modulo, $idmodulo)
    {
        $results = SQL::ini(ContaPagarQuery::buscarNfContaPagar(), [
            "idcontapagar" => $idcontapagar,
            "modulo" => $modulo,
            "idmodulo" => $idmodulo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function atualizarIdContaPagarPorIdContaPagarItem($idcontapagar, $idcontapagaritem, $idempresa = NULL)
    {
        $results = SQL::ini(ContaPagarItemQuery::atualizarIdContaPagarPorIdContaPagarItem(), [
            "idcontapagar" => $idcontapagar,
            "idcontapagaritem" => $idcontapagaritem,
            "AndIdempresa" => empty($idempresa) ? '' : $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function buscarValorTotalContaPagarItem($idcontapagar)
    {
        $results = SQL::ini(ContaPagarItemQuery::buscarValorTotalContaPagarItem(), [
            "idcontapagar" => $idcontapagar
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function atualizarValorContaPagar($valor, $idcontapagar)
    {
        $results = SQL::ini(ContaPagarQuery::atualizarValorContaPagar(), [
            "valor" => $valor,
            "idcontapagar" => $idcontapagar,
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarFormaPagamentoPorIdEmpresaETipo($idempresa, $tipo)
    {
        $results = SQL::ini(ConfcontapagarQuery::buscarFormaPagamentoPorIdEmpresaETipo(), [
            "idempresa" => $idempresa,
            "tipo" => $tipo
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function atualizarContaPagarFormaPagamentoPorIdContaPagar($idagencia, $idformapagamento, $idcontapagar)
    {
        $results = SQL::ini(ContapagarQuery::atualizarContaPagarFormaPagamentoPorIdContaPagar(), [
            "idagencia" => $idagencia,
            "idformapagamento" => $idformapagamento,
            "idcontapagar" => $idcontapagar,
            "alteradopor" => $_SESSION["SESSAO"]["USUARIO"]
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function atualizarFormaPagamentoContaPagarItem($idformapagamento, $idcontapagaritem)
    {
        $results = SQL::ini(ContapagarItemQuery::atualizarFormaPagamentoContaPagarItem(), [
            "idformapagamento" => $idformapagamento,
            "idcontapagaritem" => $idcontapagaritem,
            "alteradopor" => $_SESSION["SESSAO"]["USUARIO"]
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function atualizarFormaPagamentoAgrupadoContaPagarItem($idformapagamento, $idcontapagaritem)
    {
        $results = SQL::ini(ContapagarItemQuery::atualizarFormaPagamentoAgrupadoContaPagarItem(), [
            "idformapagamento" => $idformapagamento,
            "idcontapagaritem" => $idcontapagaritem,
            "alteradopor" => $_SESSION["SESSAO"]["USUARIO"]
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }
    }

    public static function buscarExtratoAppPorEmpresaFormaPagamentoEPeriodo($idFormaPagamento, $idEmpresa, $dataInicio, $dataFim)
    {
        $extrato = SQL::ini(ContaPagarQuery::buscarExtratoAppPorEmpresaFormaPagamentoEPeriodo(), [
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'idEmpresa' => $idEmpresa,
            'idFormaPagamento' => $idFormaPagamento,
        ])::exec();

        if ($extrato->error()) {
            parent::error(__CLASS__, __FUNCTION__, $extrato->errorMessage());
            return [];
        }

        return $extrato->data;
    }

    public static function buscarExtratoAppPorContapagarIdFormaPagamentoEEmpresa($idContapagar, $idFormaPagamento, $idEmpresa)
    {
        $extrato = SQL::ini(ContaPagarQuery::buscarExtratoAppPorContapagarIdFormaPagamentoEEmpresa(), [
            'idempresa' => $idEmpresa,
            'idformapagamento' => $idFormaPagamento,
            'idcontapagar' => $idContapagar
        ])::exec();

        if ($extrato->error()) {
            parent::error(__CLASS__, __FUNCTION__, $extrato->errorMessage());
            return [];
        }

        return $extrato->data;
    }

    public static function buscarPorChavePrimaria($id)
    {
        $contaPagarItem = SQL::ini(ContaPagarItemQuery::buscarPorChavePrimaria(), [
            'pkval' => $id
        ])::exec();

        if ($contaPagarItem->error()) {
            parent::error(__CLASS__, __FUNCTION__, $contaPagarItem->errorMessage());
            return [];
        }

        return $contaPagarItem->data[0];
    }

    public static function buscarFaturasPorIdFormapagamento($idFormaPagamento, $idEmpresa, $idContapagar = false) {
        $union = '';

        if($idContapagar) {
            $union = "UNION
                    SELECT idcontapagar, idempresa,valor
                    FROM contapagar 
                    where idcontapagar = $idContapagar";
        }

        $faturas = SQL::ini(ContaPagarQuery::buscarFaturasPorIdFormapagamento(), [
            'idformapagamento' => $idFormaPagamento,
            'idempresa' => $idEmpresa,
            'union' => $union
        ])::exec();

        if ($faturas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $faturas->errorMessage());
            return [];
        }

        return $faturas->data;
    }
}
