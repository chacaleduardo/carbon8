<?
// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/sgdoc_query.php");
require_once(__DIR__."/../querys/log_query.php");
require_once(__DIR__."/../querys/fluxostatuspessoa_query.php");


// CONTROLLERS
require_once(__DIR__."/_controller.php");

class SgdocumentoController extends Controller
{
    public static function inserir($dados)
    {
        $sgDoc = SQL::ini(SgdocQuery::inserir(), $dados)::exec();

        if($sgDoc->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $sgDoc->errorMessage());

			return [];
		}

        return $sgDoc->lastInsertId();
    }
    
    public static function buscarSgDocPorChavePrimaria($chavePrimaria)
    {
        $sgdoc = SQL::ini(SgdocQuery::buscarPorChavePrimaria(), [
            'pkval' => $chavePrimaria
        ])::exec();

        if($sgdoc->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $sgdoc->errorMessage());
			return [];
		}

        return $sgdoc->data[0];
    }

    public static function buscarSgdocPorIdSgdocTipoEGetIdEmpresa($idSgdocTipo, $getIdEmpresa, $toFillSelect = false, $autocomplete = false)
    {
        $idSgdocTipo = str_replace(',', '","', $idSgdocTipo);
        $sgdoc = SQL::ini(SgdocQuery::buscarSgdocPorIdSgdocTipoEGetIdEmpresa(), [
            'idsgdoctipo' => $idSgdocTipo,
            'getidempresa' => $getIdEmpresa
        ])::exec();

        if($sgdoc->error()){
			parent::error(__CLASS__, __FUNCTION__, $sgdoc->errorMessage());
			return [];
		}

        if($toFillSelect)
        {
            $arrRetorno = [];

            foreach($sgdoc->data as $doc)
            {
                $arrRetorno[$doc['idsgdoc']] = $doc['titulo'];
            }
        }

        if($autocomplete)
		{
			$arrRetorno = [];

			foreach($sgdoc->data as $key => $doc)
			{
				$arrRetorno[$key]['label'] = $doc['titulo'];
				$arrRetorno[$key]['value'] = $doc['idsgdoc'];
			}

			return $arrRetorno;
		}

        return $sgdoc->data;
    }

    public static function buscarAssinaturasPorIdPessoaIdObjetoETipoObjeto($idPessoa, $idObjeto, $tipoObjeto)
    {
        $assinatura = SQL::ini(SgdocQuery::buscarAssinaturaPorIdPessoaIdObjetoETipoObjeto(), [
            'idpessoa' => $idPessoa,
            'idobjeto' => $idObjeto,
            'tipoojeto' => $tipoObjeto
        ])::exec();

        if($assinatura->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $assinatura->errorMessage());
			return [];
		}

        return $assinatura->data[0];
    }

    public static function buscarAssinaturaPorStatusIdPessoaIdObjetoETipoObjeto($status, $idPessoa, $idObjeto, $tipoObjeto)
    {
        $assinatura = SQL::ini(CarimboQuery::buscarAssinaturaPorStatusIdPessoaIdObjetoETipoObjeto(), [
            'status' => $status,
            'idpessoa' => $idPessoa,
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoObjeto
        ])::exec();

        if($assinatura->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $assinatura->errorMessage());
			return [];
		}

        return $assinatura->data[0];
    }

    public static function buscarSgdocPorIdSgdocTipo($idSgdocTipo)
    {
        $documentos = SQL::ini(SgdocQuery::buscarSgdocPorIdSgdocTipo(), [
            'idsgdoctipo' => $idSgdocTipo
        ])::exec();

        if($documentos->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $documentos->errorMessage());
			return [];
		}

        return $documentos->data;
    }

    public static function buscarSgDocPorIdUnidadeEIdEmpresa($idUnidade, $idEmpresa)
    {
        $documentos = SQL::ini(SgdocQuery::buscarSgDocPorIdUnidadeEIdEmpresa(), [
            'idunidade' => $idUnidade,
            'idempresa' => $idEmpresa
        ])::exec();
        
        if($documentos->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $documentos->errorMessage());
			return [];
		}

        return $documentos->data;
    }

    public static function buscarSgDocDisponiveisParaVinculoEmUnidades($idUnidade, $idEmpresa)
    {
        $documentos = SQL::ini(SgdocQuery::buscarSgDocDisponiveisParaVinculoEmUnidades(), [
            'idempresa' => $idEmpresa,
            'idunidade' => $idUnidade
        ])::exec();

        if($documentos->error())
        {
			parent::error(__CLASS__, __FUNCTION__, $documentos->errorMessage());
			return [];
		}

        return $documentos->data;
    }

    public static function buscarDocumentosVinculadosPorIdSgSetor($idObjeto, $tipoObjeto)
    {
        $documentos = SQL::ini(FluxostatuspessoaQuery::buscarDocumentosPorIdObjetoETipoObjeto(), [
            'idobjeto' => $idObjeto,
            'tipoobjeto' => $tipoObjeto
        ])::exec();

        if($documentos->error()){
            parent::error(__CLASS__, __FUNCTION__, $documentos->errorMessage());
            return [];
        }

        return $documentos->data;
    }
}

?>