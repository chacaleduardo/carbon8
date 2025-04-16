<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/prodserv_query.php");
require_once(__DIR__."/../querys/solmat_query.php");
require_once(__DIR__."/../querys/solmatitem_query.php");
require_once(__DIR__."/../querys/solmatitemobj_query.php");

//Controllers
require_once(__DIR__."/../controllers/_modulo_controller.php");
require_once(__DIR__."/tag_controller.php");
require_once(__DIR__."/pedido_controller.php");
require_once(__DIR__."/planejamentoprodserv_controller.php");

class SolmatController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarPorChavePrimaria($id)
	{
		$solmat = SQL::ini(SolMatQuery::buscarPorChavePrimaria(), [
			'pkval' => $id
		])::exec();

		if($solmat->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $solmat->errorMessage());
			return [];
		}

		return $solmat->data[0];
	}

	public static function inserirSolmat($idempresa, $status, $idfluxostatus, $tipo, $idunidade, $unidade, $usuario)
	{
		$results = SQL::ini(SolmatQuery::inserirSolmat(), [
			"idempresa" => $idempresa,
			"status" => $status,
			"idfluxostatus" => $idfluxostatus,
			"tipo" => $tipo,
			"idunidade" => $idunidade,
			"unidade" => $unidade,
			"usuario" => $usuario
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->lastInsertId();
		}
	}

	public static function inserirSolmatItem($arrayInsertSolmatItem)
	{
		$results = SQL::ini(SolmatItemQuery::inserirSolmatItem(), $arrayInsertSolmatItem)::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->lastInsertId();
		}
	}

	public static function buscarSolMatItemPorIdSolMatGroupConcat($idSolmat)
	{
		$solMat = SQL::ini(SolmatItemQuery::buscarSolMatItemPorIdSolMatGroupConcat(), [
			'idsolmat' => $idSolmat
		])::exec();

		if($solMat->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $solMat->errorMessage());
			return [];
		}

		return $solMat->data[0];
	}

	public static function buscarProdServESolMatItemPorIdSolMat($idSolmat)
	{
		$prodServESolMatItem = SQL::ini(SolmatItemQuery::buscarProdServESolMatItemPorIdSolMat(), [
			'idsolmat' => $idSolmat
		])::exec();

		if($prodServESolMatItem->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $prodServESolMatItem->errorMessage());
			return [];
		}

		return $prodServESolMatItem->data;
	}

	public static function buscarSolMatItemSemCadastroPorIdSolMat($idSolmat)
	{
		$solMatItemSemCadastro = SQL::ini(SolmatItemQuery::buscarSolMatItemSemCadastroPorIdSolMat(), [
			'idsolmat' => $idSolmat
		])::exec();

		if($solMatItemSemCadastro->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $solMatItemSemCadastro->errorMessage());
			return [];
		}

		return $solMatItemSemCadastro->data;
	}

	public static function buscarComentariosPorIdSolMat($idSolMat)
	{
		$comentarios = SQL::ini(SolMatQuery::buscarComentariosPorIdSolMat(), [
			'idsolmat' => $idSolMat
		])::exec();

		if($comentarios->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $comentarios->errorMessage());
			return [];
		}

		return $comentarios->data;
	}

	public static function buscarSolMatItemObjPorChavePrimaria($id)
	{
		$solMatItemObj = SQL::ini(SolMatItemObjQuery::buscarPorChavePrimaria(), [
			'pkval' => $id
		])::exec();

		if($solMatItemObj->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $solMatItemObj->errorMessage());
			return [];
		}

		return $solMatItemObj->data[0];
	}

	public static function buscarUnidadesPorModuloTipoGetIdEmpresaEIdTipoUnidade($moduloTipo, $getIdEmpresa, $idTipoUnidade)
	{
		return _moduloController::buscarUnidadesPorModuloTipoGetIdEmpresaEIdTipoUnidade($moduloTipo, $getIdEmpresa, $idTipoUnidade);
	}

	public static function buscarModulosComUnidadesVinculadasPorGetIdEmpresa($idtipounidade, $getIdEmpresa)
	{
		return _moduloController::buscarModulosComUnidadesVinculadasPorGetIdEmpresa($idtipounidade, $getIdEmpresa);
	}

	public static function buscarIdProdservFormulaPorIdProdserv($idprodserv)
	{
		return ProdservController::buscarIdProdservFormulaPorIdProdserv($idprodserv);
	}

	public static function buscarProdServFormulaPorIdProdServEStatus($idprodserv)
	{
		return ProdservformulaController::buscarProdServFormulaPorIdProdServEStatus($idprodserv, 'ATIVO', true);
	}

	public static function buscarLoteSolmatItem($idprodservformula, $status, $idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $idunidade)
	{
		if (!empty($idprodservformula)) {
			$andWhere = " AND p.fabricado = 'Y' AND l.idprodservformula = ".$idprodservformula;
		} else {
			$andWhere = "";
		}

		if ($status != 'CONCLUIDO') 
		{
			$lotes = LoteController::buscarLotesVinculadosPorTipoObjetoConsumoEspecComUnion($idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $andWhere, $idunidade);
		} else {
			$lotes = LoteController::buscarLotesVinculadosPorTipoObjetoConsumoEspec($idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $andWhere);
		}

		return $lotes;
	}

	public static function buscarConsumoLotePorTipoObjetoConsumoEspec($idlote, $idlotefracao, $idobjetoconsumoespec, $tipoobjetoconsumoespec)
	{
		return LoteController::buscarConsumoLotePorTipoObjetoConsumoEspec($idlote, $idlotefracao, $idobjetoconsumoespec, $tipoobjetoconsumoespec);
	}

	public static function buscarLocalizacaoLotePorIdLote($idlote, $tipoobjeto)
	{
		return LoteController::buscarLocalizacaoLotePorIdLote($idlote, $tipoobjeto);
	}

	public static function buscarSolMatItemPorIdSomatItem($idsolmatitem)
	{
		$results = SQL::ini(SolmatItemQuery::buscarSolMatItemPorIdSomatItem(), [ 
            "idsolmatitem" => $idsolmatitem
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            $dados['sql'] = $results->sql();
            return $dados;
        }
	}

	public static function buscarRandomico()
	{
		$results = SQL::ini(SolmatQuery::buscarRandomico())::exec();
        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
	}

	public static function buscarPlanejamentoPorIdProdservMesExercioUnidade($idunidade, $exercicio, $mes, $idsolmat)
	{
		return PlanejamentoProdServController::buscarPlanejamentoPorIdProdservMesExercioUnidade($idunidade, $exercicio, $mes, $idsolmat);
	}

	public static function buscarPlanejamentoPorIdProdservMesExercioUnidadePorProdserv($idprodserv, $idunidade, $exercicio, $mes)
	{
		return PlanejamentoProdServController::buscarPlanejamentoPorIdProdservMesExercioUnidadePorProdserv($idprodserv, $idunidade, $exercicio, $mes);
	}

	public static function buscarConsumoLoteMes($ano, $mes, $idunidade, $idprodserv)
    {
        return LoteController::buscarConsumoLoteMes($ano, $mes, $idunidade, $idprodserv);
    }

	public static function buscarSeLoteConsomeTransferencia($idlotefracao)
    {
        return LoteController::buscarSeLoteConsomeTransferencia($idlotefracao);
    }
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE -----
	public static function buscarProdutosPorUnidadeMeios($idsolmat, $idunidade)
	{	
		$arrProdutos = [];
		$prodserv = self::buscarSolMatItemPorIdSolMatGroupConcat($idsolmat);
		$grupoProdserv = $prodserv['stidprodserv'];

		$solmatPorSessionIdempresa = share::otipo('cb::usr')::solmatPorSessionIdempresa("p.idprodserv");
		$listarProdutos = SQL::ini(ProdservQuery::buscarprodServPorIdUnidadeEIdEmpresaAgrupadoPorDescricao(), [
			'idunidade' => $idunidade,
			'solmatPorSessionIdempresa' => $solmatPorSessionIdempresa
		])::exec();
		
		if($listarProdutos->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $listarProdutos->errorMessage());
			return [];
		} else {
			$arrGrupoProdserv = explode(",", $grupoProdserv);
			$i = 0;
			foreach($listarProdutos->data as $produto)
			{
				if (!in_array($produto["idprodserv"], $arrGrupoProdserv)) 
				{
					$arrProdutos[$i]["descr"] = $produto["descr"];
					$arrProdutos[$i]["idprodserv"] = $produto["idprodserv"];
					$arrProdutos[$i]["codprodserv"] = $produto["codprodserv"];
					$arrProdutos[$i]["un"] = $produto["un"];
					$i++;
				}
			}

			return $arrProdutos;
		}
	}

	public static function buscarUnidadesPorClausulaUnidadeGetIdEmpresaEUnion($idsolmat, $unidade, $unidadepadrao)
    {
        $arrUnidade = [];
		$union = '';
		if (!empty($idsolmat)) {
			$condWhere = " AND u.idunidade = ".$unidade;
			$getidempresa = "";
		} elseif (!empty($unidadepadrao)) {
			$tipounidadepadrao = traduzid('unidade', 'idunidade', 'idtipounidade', $unidadepadrao);
			$condWhere = " AND u.idtipounidade = ".$tipounidadepadrao;
			$getidempresa = share::otipo('cb::usr')::solmatUnidadeOrigemPorSessionIdempresa("u.idunidade");
		} else {
			$condWhere = "";
			$getidempresa = "";
		}

		if(cb::idempresa() == '4') {
			$union = " UNION ".SQL::mount(UnidadeQuery::buscarUnidadePorIdUnidade(), [
				'idunidade' => 553
			]);
		}

        $listarUnidades = SQL::ini(UnidadeQuery::buscarUnidadesPorClausulaUnidadeGetIdEmpresaEUnion(), [
            'clausulaunidade' => $condWhere,
			'getidempresa' => $getidempresa,
			'union'=> $union
        ])::exec();

        if($listarUnidades->error()){
            parent::error(__CLASS__, __FUNCTION__, $listarUnidades->errorMessage());
            return [];
        } else {
			foreach($listarUnidades->data as $unidade)
			{
				$arrUnidade[$unidade["idunidade"]] = $unidade["unidade"];
			}
			return $arrUnidade;
        }
    }

	public static function buscarLocalizacaoLoteSolmatItem($idsolmatitem, $idprodservformula, $status, $idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $idtipounidade)
	{
		if (!empty($idprodservformula)) {
			$andWhere = " AND p.fabricado = 'Y' AND l.idprodservformula = ".$idprodservformula;
		} else {
			$andWhere = "";
		}

		if ($status != 'CONCLUIDO') 
		{
			$lotes = LoteController::buscarLocalizacaoDeLotesVinculadosComUnion($idsolmatitem, $idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $andWhere, $idtipounidade);
		} else {
			$lotes = LoteController::buscarLocalizacaoDeLotesVinculados($idprodserv, $idobjetoconsumoespec, $tipoobjetoconsumoespec, $andWhere);
		}

		return $lotes;
	}

	public static function buscarConsumoPendenteDaSolmat($idsolmat)
	{
		$result = SQL::ini(LoteconsQuery::buscarConsumoPendenteDaSolmat(), [
            'idsolmat' => $idsolmat,
        ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        } else {
			return $result->data;
        }
	}


	public static function listarFillSelectUnidadeAtivo($tipoobjetovinc, $tipoobjeto, $idobjeto, $idpessoa, $comprasMaster)
	{
		return UnidadeController::listarFillSelectUnidadeAtivo($tipoobjetovinc, $tipoobjeto, $idobjeto, $idpessoa, $comprasMaster);
	}

	public static function listarFillSelectTagsPorIdTagClassIdTagTipoEIdUnidade($idTagClass, $idTagTipo, $idUnidade)
	{
		return UnidadeController::buscarTagsPorIdTagClassIdTagTipoEIdUnidade($idTagClass, $idTagTipo, $idUnidade, true);
	}

	public static function buscarTagPorIdTag($idtag)
	{
		return TagController::buscarTagPorIdTag($idtag);
	}

	public static function apagarTagSalaPorIdTag($idtag)
	{
		return TagController::apagarTagSalaPorIdTag($idtag);
	}

	public static function buscarLotefracaoPorIdloteIdunidade($idlote, $idunidade)
	{
		return PedidoController::buscarLotefracaoPorIdloteIdunidade($idlote, $idunidade);
	}

	public static function buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idLoteFracao)
	{
		return LoteController::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idLoteFracao);
	}

	public static function inserirLoteCons($arrayInsertLoteCons)
	{
		return LoteController::inserirLoteCons($arrayInsertLoteCons);
	}

	public static function inserirLoteFracao($arrayInsertLoteFracao)
	{
		return LoteController::inserirLoteFracao($arrayInsertLoteFracao);
	}

	public static function atualizarIdLoteFracaoOrigemIdLoteFracaoLotefracao($idlotefracao,$idlotefracaoorigem)
	{
		return LoteController::atualizarIdLoteFracaoOrigemIdLoteFracaoLotefracao($idlotefracao,$idlotefracaoorigem);
	}

	public static function atualizarLoteFracaoPorIdTransacao($qtdini, $idtransacao, $status)
	{
		return LoteController::atualizarLoteFracaoPorIdTransacao($qtdini, $idtransacao, $status);
	}

	public static function atualizarLoteConsPorIdTransacaoCredito($qtdc, $idtransacao, $status)
	{
		return LoteController::atualizarLoteConsPorIdTransacaoCredito($qtdc, $idtransacao, $status);
	}

	public static function atualizarLoteConsPorIdTransacaoDebito($qtdd, $idtransacao, $status)
	{
		return LoteController::atualizarLoteConsPorIdTransacaoDebito($qtdd, $idtransacao, $status);
	}

	public static function inserirLoteFracaoStatus($arrayInsertLoteFracao)
	{
		return LoteController::inserirLoteFracaoStatus($arrayInsertLoteFracao);
	}
	//----- AUTOCOMPLETE -----

	public static function buscarSolmatitemPendente($idsolmat)
	{
		$result = SQL::ini(SolmatItemQuery::buscarSolmatitemPendente(), [
            'idsolmat' => $idsolmat,
        ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        } else {
			return $result->data[0];
        }
	}

	public static function buscarSolmatitemEstoque($idsolmatitem)
	{
		$result = SQL::ini(SolmatItemQuery::buscarSolmatitemEstoque(), [
            'idsolmatitem' => $idsolmatitem,
        ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        } else {
			return $result->data[0];
        }
	}

	public static function buscarSolmatEstoque($idsolmat)
	{
		$result = SQL::ini(SolmatItemQuery::buscarSolmatEstoque(), [
            'idsolmat' => $idsolmat,
        ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        } else {
			return $result->data;
        }
	}

	public static function buscarSolmatitemPedente($idsolmat,$idsolmatitem)
	{
		$result = SQL::ini(SolmatItemQuery::buscarSolmatitemPedente(), [
            'idsolmat' => $idsolmat,
			'idsolmatitem'=>$idsolmatitem
        ])::exec();

        if($result->error()){
            parent::error(__CLASS__, __FUNCTION__, $result->errorMessage());
            return [];
        } else {
			return $result->data[0];
        }
	}



	// ----- Variáveis de apoio -----
	public static $tipoSolmat = array('MATERIAL' => 'Material');
	public static $tipoSoltag = array('EQUIPAMENTOS' => 'Equipamentos');
	public static $tipoSolmatMeios = array('ESTÉRIL' => 'Estéril',
											'MEIOS' => 'Meios');
	// ----- Variáveis de apoio -----
}
