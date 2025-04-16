<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/objetojson_query.php");
require_once(__DIR__."/../querys/prodservprproc_query.php");
require_once(__DIR__."/../querys/prproc_query.php");
require_once(__DIR__."/../querys/prprocprativ_query.php");

//Controllers
require_once(__DIR__."/../controllers/amostra_controller.php");
require_once(__DIR__."/../controllers/etapa_controller.php");
require_once(__DIR__."/../controllers/formulaprocesso_controller.php");
require_once(__DIR__."/../controllers/fluxo_controller.php");
require_once(__DIR__."/../controllers/prativ_controller.php");
require_once(__DIR__."/../controllers/tagtipo_controller.php");
require_once(__DIR__."/../controllers/unidade_controller.php");

class PrProcController extends Controller
{
	public static $arrCores = [
		"silver", 
		"#cc0000", 
		"#0000cc", 
		"#00cc00", 
		"#990000", 
		"#ff6600", 
		"#fcd202", 
		"#b0de09", 
		"#0d8ecf",  
		"#cd0d74",
		"#00ffec",
		"#460878",
		"#ff00e5",
		"#5e9208"
	];
	
	// ----- FUNÇÕES -----
	public static function buscarProcessosPorIdProdserv($idprodserv)
	{
		$results = SQL::ini(ProdservPrProcQuery::buscarProcessosPorIdProdserv(), [
            'idprodserv' => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

	public static function retornarProcessosPorIdProdserv($idprodserv)
	{
		return SQL::mount(ProdservPrProcQuery::buscarProcessosPorIdProdserv(), [
			"idprodserv" => $idprodserv
		]);
	}

	public static function buscarSqlProcessos($idprproc)
	{
		return SQL::mount(PrProcQuery::buscarProcessos(), [
			"idprproc" => $idprproc
		]);
	}

	public static function atualizarVersaoProdservPrProc($idprodservprproc)
	{
		$results = SQL::ini(ProdservPrProcQuery::atualizarVersaoProdservPrProc(), [
            'idprodservprproc' => $idprodservprproc
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}

	public static function buscarFillSelectSubtipoFormalizacao()
	{
        return FormalizacaoController::buscarFillSelectSubtipoFormalizacao();
    }

	public static function buscarProcessosPorTipoEIdEmpresa($idprproc)
	{
        return PrativController::buscarProcessosPorTipoEIdEmpresa($idprproc);
    }

	public static function buscarTagClassPorTipoObjetoEIdPrativ($idtagclass, $tipoobjeto, $idprativ)
	{
        return TagTipoController::buscarTagClassPorTipoObjetoEIdPrativ($idtagclass, $tipoobjeto, $idprativ);
    }

	public static function buscarPrativObjPorTipoObjeto($tipoobjeto, $idprativ, $tipo)
	{
        return PrativController::buscarPrativObjPorTipoObjeto($tipoobjeto, $idprativ, $tipo);
    }

	public static function buscarATividadesPorIdPrativETipoObjeto($tipoobjeto, $idprativ)
	{
        return PrativController::buscarATividadesPorIdPrativETipoObjeto($tipoobjeto, $idprativ);
    }

	public static function buscarOrdemPrProcPrativPorIdPrProc($idprproc)
	{
		$results = SQL::ini(PrProcPrativQuery::buscarOrdemPrProcPrativPorIdPrProc(), [
            'idprproc' => $idprproc
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

	public static function buscarProdservPrprocPorIdPrProc($idprproc)
	{
        return ProdservController::buscarProdservPrprocPorIdPrProc($idprproc);
    }

	public static function buscarObjetoPorTipoObjeto($idobjeto, $tipoobjeto)
	{
		return FormulaProcessoController::buscarObjetoPorTipoObjeto($idobjeto, $tipoobjeto);
	}

	public static function buscarStatusPorIdFluxoStatus($idfluxostatus)
	{
		return FluxoController::buscarStatusPorIdFluxoStatus($idfluxostatus);
	}

	public static function buscarUnidadePorIdtipoIdempresa($idtipounidade, $idempresa)
	{
		return UnidadeController::buscarUnidadePorIdtipoIdempresa($idtipounidade, $idempresa);
	}

	public static function buscarSqlAtividadePorIdProProc($idprproc)
	{
		return PrativController::buscarSqlAtividadePorIdProProc($idprproc);
	}

	public static function buscarVersaoObjetoPorTipoObjetoEVersao($idobjeto, $tipoobjeto, $versaoobjeto)
	{
		$results = SQL::ini(ObjetoJsonQuery::buscarVersaoObjetoPorTipoObjetoEVersao(), [
            'idobjeto' => $idobjeto,
            'tipoobjeto' => $tipoobjeto,
			'versaoobjeto' => $versaoobjeto
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarProcessos($idprproc)
	{
		$results = SQL::ini(PrProcQuery::buscarProcessos(), [
            'idprproc' => $idprproc
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
	}

	public static function inserirObjetoJson($arrayObjetoJson)
	{
		$results = SQL::ini(ObjetoJsonQuery::inserirObjetoJson(), $arrayObjetoJson)::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}

	public static function atualizarJobjetoObjetoJsonPorIdobjetojson($jobjeto, $idobjetojson)
	{
		$results = SQL::ini(ObjetoJsonQuery::atualizarJobjetoObjetoJsonPorIdobjetojson(), [
			"jobjeto" => $jobjeto,
			"idobjetojson" => $idobjetojson
		])::exec();

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

	public static function buscarAtividadesGrupo($idprproc)
	{
		$results = SQL::ini(PrProcQuery::buscarAtividadesGrupo(), [
            'idprproc' => $idprproc
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
	}

	public static function buscarPrProPrativComHoraEstimada($idprproc)
	{
		$results = SQL::ini(PrProcPrativQuery::buscarPrProPrativComHoraEstimada(), [
            'idprproc' => $idprproc
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return false;
        }else{
            $time = $results->data[0]['tempoestimado'];
			return $time;
        }
	}
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE ----
	public static function buscarFillSelectProcessosPorTipoEIdEmpresa($tipo, $idempresa)
	{
		$results = SQL::ini(PrProcQuery::buscarProcessosPorTipoEIdEmpresa(), [
            'tipo' => $tipo,
			'idempresa' => $idempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $arrProcessos = [];
			foreach($results->data as $_processos)
			{	
				$arrProcessos[$_processos['idprproc']] = $_processos['proc'];
			}

			return $arrProcessos;
        }
	}

	public static function buscarFillSelectFluxoPorModuloETipoObjeto($modulo, $tipoobjeto, $idobjeto)
	{
        return FluxoController::buscarFillSelectFluxoPorModuloETipoObjeto($modulo, $tipoobjeto, $idobjeto);
    }

	public static function buscarEtapaPorTipoObjeto($modulo, $tipoobjeto, $idobjeto)
	{
        return EtapaController::buscarEtapaPorTipoObjeto($modulo, $tipoobjeto, $idobjeto);
    }

	public static function listarFillSelectTagPorIdTagClass($idtagclass)
	{
        return TagTipoController::listarFillSelectTagPorIdTagClass($idtagclass);
    }

	public static function listarFillSelectTagPorIdTagClassEStatus($idtagclass)
	{
        return TagTipoController::listarFillSelectTagPorIdTagClassEStatus($idtagclass);
    }

	public static function buscarPrativOpcaoPorTipo($tipoobjeto, $idprativ, $tipo)
	{
        return PrativController::buscarPrativOpcaoPorTipo($tipoobjeto, $idprativ, $tipo);
    }

	public static function listarFillSelectSubtipoamostraPorIdEmpresa()
	{
        return AmostraController::listarFillSelectSubtipoamostraPorIdEmpresa();
    }

	public static function listarFillSelectProdutoPorTipoEAtivo($tipo)
	{
        return ProdservController::listarFillSelectProdutoPorTipoEAtivo($tipo);
    }

	public static function listarAtividadesPorIdempresaEAtividadeNaoNulo()
	{
        return PrativController::listarAtividadesPorIdempresaEAtividadeNaoNulo();
    }
	//----- AUTOCOMPLETE ----

	// ----- Variáveis de apoio -----
    public static function tipoProdserv()
	{
        return ProdServController::$tipoProdserv;
    }
    // ----- Variáveis de apoio -----
}
