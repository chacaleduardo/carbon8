<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/empresa_query.php");
require_once(__DIR__ . "/../querys/objempresa_query.php");
require_once(__DIR__ . "/../querys/empresacobranca_query.php");

class EmpresaController extends Controller
{
    public static function buscarEmpresaPorIdEmpresa($idempresa)
    {
        $results = SQL::ini(EmpresaQuery::buscarEmpresaPorIdEmpresa(), [
            'idempresa' => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
            return (count($results->data) > 0) ? $results->data[0] : [];
        }
    }

    public static function buscarEmpresaFilial($idpessoa)
    {

        $results = SQL::ini(EmpresaQuery::buscarEmpresaFilial(), [
            'idpessoa' => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_filial) {
                $listaFilial[$_filial['idempresa']] = $_filial['nomefantasia'];
            }
            return $listaFilial;
        }
    }

    public static function buscarFilial()
    {

        $results = SQL::ini(EmpresaQuery::buscarFilial(), [
            'idempresa' => cb::idempresa()
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            foreach ($results->data as $_filial) {
                $listaFilial[$_filial['idempresa']] = $_filial['nomefantasia'];
            }
            return $listaFilial;
        }
    }


    public static function buscarEmpresaQueNaoExisteNaObjetoEmpresa($objeto, $idobjeto)
    {
        $resultsEmpresa = SQL::ini(EmpresaQuery::buscarEmpresaQueNaoExisteNaObjetoEmpresa(), [
            "objeto" => $objeto,
            "idobjeto" => $idobjeto,
            'idempresa' => cb::idempresa()
        ])::exec();

        if ($resultsEmpresa->error()) {
            parent::error(__CLASS__, __FUNCTION__, $resultsEmpresa->errorMessage());
            return [];
        } else {
            $arrEmpresa = [];
            foreach ($resultsEmpresa->data as $_empresa) {
                $arrEmpresa[$_empresa['idempresa']] = $_empresa['empresa'];
            }

            return $arrEmpresa;
        }
    }

    public static function listarEmpresaVinculadaObjetoEmpresa($idobjeto, $objeto)
    {
          
        $results = SQL::ini(EmpresaQuery::listarEmpresaVinculadaObjetoEmpresa(), [
            'idobjeto' => $idobjeto,
            'objeto' => $objeto
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data;
        }
    }

    public static function buscarCorSistemaPorIdEmpresa($idEmpresa = null)
    {
        $corSistema = SQL::ini(EmpresaQuery::buscarCorSistemaPorIdEmpresa(), [
            'idempresa' => $idEmpresa ?? cb::idempresa()
        ])::exec();

        if ($corSistema->error()) {
            parent::error(__CLASS__, __FUNCTION__, $corSistema->errorMessage());
            return "";
        }

        return $corSistema->data[0]['corsistema'];
    }

    public static function buscarEmpresaPorIdEmpresaEClausulaModuloEUsuario($idEmpresa, $clausulaEmpresaModulo, $clausulaEmpresaUsuario)
    {
        $empresas = SQL::ini(EmpresaQuery::buscarEmpresaPorIdEmpresaEClausulaModuloEUsuario(), [
            'idempresa' => $idEmpresa,
            'clausulaempresamodulo' => $clausulaEmpresaModulo,
            'clausulaempresausuario' => $clausulaEmpresaUsuario
        ])::exec();

        if ($empresas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresas->errorMessage());
            return [];
        }

        return $empresas->data;
    }

    public static function buscarIdEmpresasVinculadasPorIdObjetoEObjeto($idObjeto, $objeto)
    {
        $idEmpresas = SQL::ini(EmpresaQuery::buscarIdEmpresasVinculadasPorIdObjetoEObjeto(), [
            'idobjeto' => $idObjeto,
            'objeto' => $objeto
        ])::exec();

        if ($idEmpresas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $idEmpresas->errorMessage());
            return [];
        }

        return $idEmpresas->data[0]['idempresa'];
    }

    public static function buscarObjempresaPorIdObjempresa($idobjempresa)
    {
        $results = SQL::ini(ObjEmpresaQuery::buscarObjempresaPorIdObjempresa(), [
            'idobjempresa' => $idobjempresa
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

    public static function buscarCaminhoImagemTipoHeaderProduto($idempresa, $andWhere = false)
    {
        $idempresaAnd = $andWhere == false ? " AND idempresa = $idempresa" : $idempresa;
        $results = SQL::ini(EmpresaImagemQuery::buscarCaminhoImagemTipoHeaderProduto(), [
            'idempresa' => $idempresaAnd
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        } else {
            return $results->data[0];
        }
    }

    public static function buscarEmpresasPorStatusEIdEmpresa($idEmpresa, $status)
    {
        $empresas = SQL::ini(EmpresaQuery::buscarEmpresasPorIdEmpresaEStatus(), [
            'idempresa' => $idEmpresa,
            'status' => $status
        ])::exec();

        if ($empresas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresas->errorMessage());
            return [];
        }

        return $empresas->data;
    }

    public static function listarEmpresasAtivas()
    {

        $empresas = SQL::ini(EmpresaQuery::listarEmpresasAtivas())::exec();

        if ($empresas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresas->errorMessage());
            return [];
        }

        return $empresas->data;
    }

    public static function listarEmpresasAtivasSemConfCobranca($idempresa)
    {

        $empresas = SQL::ini(EmpresaQuery::listarEmpresasAtivasSemConfCobranca(), [
            'idempresa' => $idempresa
        ])::exec();

        if ($empresas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresas->errorMessage());
            return [];
        }

        return $empresas->data;
    }
    public static function listarEmpresasAtivasComcobranca($idempresa)
    {

        $empresas = SQL::ini(EmpresaQuery::listarEmpresasAtivasComcobranca(), [
            'idempresa' => $idempresa
        ])::exec();

        if ($empresas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresas->errorMessage());
            return [];
        }

        return $empresas->data;
    }

    public static function buscarEmpresaPessoaValorNovaNF($idnf)
    {
        $empresas = SQL::ini(EmpresaQuery::buscarEmpresaPessoaValorNovaNF(), [
            'idnf' => $idnf
        ])::exec();

        if ($empresas->error()) {
            parent::error(__CLASS__, __FUNCTION__, $empresas->errorMessage());
            return [];
        }

        return $empresas->data[0];
    }

    public static function buscarCobrancaEmpresa($idempresa)
    {
        $results = SQL::ini(EmpresaCobrancaQuery::buscarCobrancasPorIdempresa(), [
            'idempresa' => $idempresa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }
    public static function buscarEmpresasVinculadasAPessoa($idpessoa)
    {
        $results = SQL::ini(EmpresaQuery::buscarEmpresasVinculadasAPessoa(), [
            'idpessoa' => $idpessoa
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }

        return $results->data;
    }

    public static $regimetrib = array(
        '1' => 'Simples Nacional',
        '2' => 'Simples Nacional - Excesso de Sublimite da Receita Bruta',
        '3' => 'Regime Normal',
        '4' => 'Simples Nacional - MEI'
    );
}
