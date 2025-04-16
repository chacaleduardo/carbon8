<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/_auditoria_query.php");
require_once(__DIR__."/../querys/objetojson_query.php");
require_once(__DIR__."/../querys/prodservloteservico_query.php");
require_once(__DIR__."/../querys/prodservloteservicoins_query.php");

//Controllers]
require_once(__DIR__."/../controllers/amostra_controller.php");
require_once(__DIR__."/../controllers/plantel_controller.php");
require_once(__DIR__."/../controllers/prativ_controller.php");
require_once(__DIR__."/../controllers/prodserv_controller.php");
require_once(__DIR__."/../controllers/prodservformula_controller.php");
require_once(__DIR__."/../controllers/prodservformularotulo_controller.php");
require_once(__DIR__."/../controllers/prproc_controller.php");
require_once(__DIR__."/../controllers/unidade_controller.php");
require_once(__DIR__."/../controllers/formularotulo_controller.php");

class FormulaProcessoController extends Controller
{
    // ----- FUNÇÕES -----
	public static function listarProdservFormulaPlantel($idprodserv)
    {
        return ProdservformulaController::listarProdservFormulaPlantel($idprodserv);
    }

    public static function buscarProdservPorTipoEStatusEIdEmpresa($status, $tipo)
    {
        return ProdservController::buscarProdservPorTipoEStatusEIdEmpresa($status, $tipo);
    }

    public static function buscarPlantelPorIdObjetoETipoObjeto($idobjeto, $tipoobjeto)
    {
        return PlantelController::buscarPlantelPorIdObjetoETipoObjeto($idobjeto, $tipoobjeto);
    }

    public static function buscarFormulaRotuloPorIdProdservFormula($idprodservformula)
    {
        return FormulaRotuloController::buscarFormulaRotuloPorIdProdservFormula($idprodservformula);
    }

    public static function buscarVersaoObjetoPorTipoObjeto($idobjeto, $tipoobjeto)
	{
		$results = SQL::ini(ObjetoJsonQuery::buscarVersaoObjetoPorTipoObjeto(), [
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

    public static function buscarObjetoPorTipoObjeto($idobjeto, $tipoobjeto)
	{
		$results = SQL::ini(ObjetoJsonQuery::buscarObjetoPorTipoObjeto(), [
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

    public static function buscarInsumosServicoEmAndamento($idtipoteste, $idprodservformula, $satusFormula, $statusFormulaIns)
    {
        return ProdServController::buscarInsumosServicoEmAndamento($idtipoteste, $idprodservformula, $satusFormula, $statusFormulaIns);
    }

    public static function buscarProcessosPorIdProdserv($idprodserv)
    {
        return PrProcController::buscarProcessosPorIdProdserv($idprodserv);
    }

    public static function buscarProcessosPorTipoEIdEmpresa($idprodserv)
    {
        return PrativController::buscarProcessosPorTipoEIdEmpresa($idprodserv);
    }

    public static function buscarInsumosAtividadeNaoAtivos($idprativ, $idprodservprproc)
    {
        return ProdservController::buscarInsumosAtividadeNaoAtivos($idprativ, $idprodservprproc);
    }

    public static function buscarUnidadePorIdtipoIdempresa($idprativ, $idprodservprproc)
    {
        return UnidadeController::buscarUnidadePorIdtipoIdempresa($idprativ, $idprodservprproc);
    }

    public static function buscarProdServFormulaPorIdProdServEStatus($idProdServ, $status)
    {
        return ProdservformulaController::buscarProdServFormulaPorIdProdServEStatus($idProdServ, $status);
    }

    public static function atualizarCustoArvoreProdservFormula($vlrcusto, $idprodservformula)
    {
        return ProdservformulaController::atualizarCustoArvoreProdservFormula($vlrcusto, $idprodservformula);
    }

    public static function atualizarArvoreProdservFormula($idprodservformula)
    {
        return ProdservformulaController::atualizarArvoreProdservFormula($idprodservformula);
    }

    public static function inserirProdservFormulaComSelect($idprodservformula)
    {
        return ProdservformulaController::inserirProdservFormulaComSelect($idprodservformula);
    }

    public static function buscarDadosProdservFormulaInsPorIdProdservFormula($idprodservformula, $status)
    {
        return ProdservformulaController::buscarDadosProdservFormulaInsPorIdProdservFormula($idprodservformula, $status);
    }

    public static function inserirProservFormulaIns($arrIsumos)
    {
        return ProdservformulaController::inserirProservFormulaIns($arrIsumos);
    }

    public static function buscarFilhosProdservFormulaInsPorIdProdservFormula($idprodservformulanova, $idprodservformula)
    {
        return ProdservformulaController::buscarFilhosProdservFormulaInsPorIdProdservFormula($idprodservformulanova, $idprodservformula);
    }

    public static function inserirInsumo($idnovo, $idprodservformula, $idprodservformulains)
    {
        return PrativController::inserirInsumo($idnovo, $idprodservformula, $idprodservformulains);
    }

    public static function retornarSqlProdserv($idprodserv)
    {
        return ProdServController::retornarSqlProdserv($idprodserv);
    }

    public static function inserirObjetoJson($arrayObjetoJson)
	{
		$results = SQL::ini(ObjetoJsonQuery::inserirObjetoJson(), $arrayObjetoJson)::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}

    public static function inserirAuditoria($arrayAuditoria)
	{
		$results = SQL::ini(AuditoriaQuery::inserirAuditoriaFluxo(), $arrayAuditoria)::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}

    public static function retornarProcessosPorIdProdserv($idprodserv)
    {
        return PrprocController::retornarProcessosPorIdProdserv($idprodserv);
    }

    public static function buscarInsumosEFormulasProdsev($idprativ)
    {
        return PrativController::buscarInsumosEFormulasProdsev($idprativ);
    }

    public static function atualizarVersaoProdservPrProc($idprodservprproc)
    {
        return PrProcController::atualizarVersaoProdservPrProc($idprodservprproc);
    }

    public static function buscarSqlProcessosPorIdProdservPrProc($idprodservprproc)
    {
        return PrativController::buscarSqlProcessosPorIdProdservPrProc($idprodservprproc);
    }

    public static function listarProdservLoteServicoPlantel($idprodserv, $idprodservloteservico = NULL)
    {
        if(!empty($idprodservloteservico))
        {
            $condicaoWhere = " AND f.idprodservloteservico = ".$idprodservloteservico;
        }
        
        $results = SQL::ini(ProdservLoteServicoQuery::buscarProdservLotesServico(), [          
            "idprodserv" => $idprodserv,
            "condicaoWhere" => $condicaoWhere          
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $arrret = [];
            foreach ($results->data as $_formula) 
            {
                foreach($_formula as $_formulacol => $_formulaval)
			    {
                    $arrret[$_formula['idprodservloteservico']][$_formulacol] = $_formulaval;
                }

                $arrret[$_formula['idprodservloteservico']]["prodservloteservico"] = self::listarProdservFormulaIns($_formula['idprodservloteservico']);
            }

            return $arrret;
        }
    }

    public static function listarProdservFormulaIns($idprodservloteservico)
    {
        $results = SQL::ini(ProdservLoteServicoInsQuery::buscarProdservLotesServicoIns(), [          
            "idprodservloteservico" => $idprodservloteservico        
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $arrret = [];
            foreach ($results->data as $_formulains) 
            {
                foreach($_formulains as $_formulaInscol => $_formulaInsval)
			    {
                    $arrret[$_formulains['idprodservloteservicoins']][$_formulaInscol] = $_formulaInsval;
                }
            }

            return $arrret;
        }
    }

    public static function listarFillSelectSubtipoamostraPorIdEmpresa()
    {
        return AmostraController::buscarSubtipoamostraEmpresaPorIdEmpresa();
    }
    // ----- FUNÇÕES -----

    //----- AUTOCOMPLETE ----
    public static function buscarFillSelectProcessosPorTipoEIdEmpresa($tipo, $idempresa)
    {
        return PrProcController::buscarFillSelectProcessosPorTipoEIdEmpresa($tipo, $idempresa);
    }

    public static function buscarValorServico($idprodserv)
	{
		$results = SQL::ini(ProdservLoteServicoQuery::buscarValorServico(), [
            'idprodserv' => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
	}
    public static function buscarValorServicoVenda($idprodserv)
	{
		$results = SQL::ini(ProdservLoteServicoQuery::buscarValorServicoVenda(), [
            'idprodserv' => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
	}


    //----- AUTOCOMPLETE ----
}

?>