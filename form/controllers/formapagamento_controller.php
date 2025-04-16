
<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/formapagamento_query.php");
require_once(__DIR__ . "/../querys/tipoprodserv_query.php");

class FormaPagamentoController extends Controller
{
    // ----- FUNÇÕES -----
    public static function buscarInfFormapagamentoPorIdObjetoOrigemEIdObjeto($idobjetoorigem, $tipoobjetoorigem, $idobjeto, $tipoobjeto)
    {
        $results = SQL::ini(FormaPagamentoQuery::buscarInfFormapagamentoPorIdObjetoOrigemEIdObjeto(), [
            "idobjetoorigem" => $idobjetoorigem,
            "tipoobjetoorigem" => $tipoobjetoorigem,
            "idobjeto" => $idobjeto,
            "tipoobjeto" => $tipoobjeto
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

    public static function buscarFormaPagamentoPorStatusEIdEmpresa($status)
    {
        $results = SQL::ini(FormaPagamentoQuery::buscarFormaPagamentoPorStatusEIdEmpresa(), [
            "status" => $status,
            "getidempresa" => getidempresa('idempresa', 'formapagamento')
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

    public static function buscarInfFormapagamentoPorId($idformapagamento)
    {
        $results = SQL::ini(FormaPagamentoQuery::buscarInfFormapagamentoPorId(), [
            "idformapagamento" => $idformapagamento
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function buscarFormaPagamentoPorIdPessoa($idpessoa, $_idempresa)
    {
        $results = SQL::ini(FormaPagamentoQuery::buscarFormaPagamentoPorIdPessoa(), [
            "idpessoa" => $idpessoa,
            "idempresa" => $_idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data[0];
    }

    public static function buscarFormasPagamentoPorIdPessoa($idpessoa, $_idempresa)
    {
        $results = SQL::ini(FormaPagamentoQuery::buscarFormaPagamentoPorIdPessoa(), [
            "idpessoa" => $idpessoa,
            "idempresa" => $_idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }
    // ----- FUNÇÕES -----

    // ----- AUTOCOMPLETE -----
    public static function listarFormaPagamentoAtivo($orderBy = NULL)
    {

        $results = SQL::ini(FormaPagamentoQuery::listarFormaPagamentoAtivoPorLp(), [
            "idempresa" => share::otipo('cb::usr')::formapagamentofiltro("f.idformapagamento"),
            "idobjetovinc" => getModsUsr('LPS'),
            "tipoobjetovinc" => 'formapagamento',
            "orderBy" => $orderBy,
            "restricaoEmpresa" => ''
        ])::exec();
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function listarFormaPagamentoAtivoPorLP($orderBy = NULL, $andRestricao = NULL)
    {
        if(!empty($andRestricao)){
            $empresa =' ';
        }else{
            $empresa= share::otipo('cb::usr')::formapagamentofiltro("f.idformapagamento") ;
        }
              
        $results = SQL::ini(FormaPagamentoQuery::listarFormaPagamentoAtivoPorLp(), [
            "idempresa" => $empresa,
            "idobjetovinc" => getModsUsr('LPS'),
            "tipoobjetovinc" => 'formapagamento',
            "orderBy" => $orderBy,
            "restricaoEmpresa" => $andRestricao
        ])::exec();
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function listarFormaPagamentoAtivoDistinct()
    {
        $results = SQL::ini(FormaPagamentoQuery::listarFormaPagamentoAtivoDistinct(), [
            "idempresa" => share::otipo('cb::usr')::formapagamentofiltro("f.idformapagamento")
        ])::exec();
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data;
        }
    }

    public static function buscarConfiguracoesFormaPagamento($idformapagamento)
    {
        $results = SQL::ini(FormaPagamentoQuery::buscarConfiguracoesFormaPagamento(), [
            "idformapagamento" => $idformapagamento
        ])::exec();
        if($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }
    public static function buscarFormapagamentoAgenciaPorFormapagamento($idformapagamento)
    {
        $results = SQL::ini(FormaPagamentoQuery::buscarFormapagamentoAgenciaPorFormapagamento(), [
            "idformapagamento" => $idformapagamento
        ])::exec();
        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return $results->data[0];
        }
    }

    public static function listarFormapagamentoAgrupadoPorEmpresa()
    {

        $results = SQL::ini(FormapagamentoQuery::buscarFormapagamentoAgrupadoPorEmpresaPorLP(), [
            "idempresa" => cb::idempresa(),
            "tipoobjetovinc" => 'formapagamento',
            "idobjetovinc" => getModsUsr('LPS')

        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['idformapagamento']] = $_valor['descricao'];
            }
            return $lista;
        }
    }

    public static function buscarFormapagtoContaItemTipoProdservTipoProdServ($idcontaitem)
    {
        $results = SQL::ini(TipoProdServQuery::buscarContaItemTipoProdservTipoProdServ(), ["idcontaitem" => $idcontaitem])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_valor) {
                $lista[$_valor['idtipoprodserv']] = $_valor['tipoprodserv'];
            }
            return $lista;
        }
    }

    public static function buscarFormaPagamentoAtivaPorIdEmpresaEFormaPagamento($idEmpresa, $formaPagamento, $toFillSelect = false)
    {
        $formasPagamento = SQL::ini(FormaPagamentoQuery::buscarFormaPagamentoAtivaPorIdEmpresaEFormaPagamento(), [
            "idempresa" => $idEmpresa,
            "formapagamento" => $formaPagamento
        ])::exec();

        if ($formasPagamento->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formasPagamento->errorMessage());
            return [];
        }

        if ($toFillSelect) {
            $arrRetorno = [];

            foreach ($formasPagamento->data as $item) $arrRetorno[$item['idformapagamento']] = $item['descricao'];

            return $arrRetorno;
        }

        return $formasPagamento->data;
    }

    public static function listarFormaPagamentoPorEmpresa($idEmpresa, $toFillSelect = false)
    {
        $formasPagamento = SQL::ini(FormaPagamentoQuery::listarFormaPagamentoPorEmpresa(), [
            "idempresa" => getidempresa('idempresa', 'formapagamento'),
        ])::exec();

        if ($formasPagamento->error()) {
            parent::error(__CLASS__, __FUNCTION__, $formasPagamento->errorMessage());
            return [];
        }

        if ($toFillSelect) {
            $arrRetorno = [];

            foreach ($formasPagamento->data as $item) $arrRetorno[$item['idformapagamento']] = $item['descricao'];

            return $arrRetorno;
        }

        return $formasPagamento->data;
    }


    public static $ArrayVazioF = array('' => '');
    // ----- AUTOCOMPLETE ----- 
}
