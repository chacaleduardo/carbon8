<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/solcom_query.php");
require_once(__DIR__."/../querys/solcomitem_query.php");
require_once(__DIR__."/../querys/objetolink_query.php");
require_once(__DIR__."/../querys/unidadevolume_query.php");
require_once(__DIR__."/../querys/arquivo_query.php");

//Controllers
require_once(__DIR__."/../controllers/prodserv_controller.php");
require_once(__DIR__."/../controllers/pessoa_controller.php");
require_once(__DIR__."/../controllers/unidade_controller.php");
require_once(__DIR__."/../controllers/cotacao_controller.php");
require_once(__DIR__."/../controllers/solmat_controller.php");

class SolcomController extends Controller
{
	// ----- FUNÇÕES -----
	public static function listarSolicitacaoCompraVincultadaCotacao($idobjeto, $modulo, $idempresa)
	{
		$results = SQL::ini(SolcomQuery::listarSolicitacaoCompraVincultadaCotacao(), [
			"idobjeto" => $idobjeto,
			"tipoobjeto" => $modulo,
			"idempresa" => $idempresa
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}
	
	public static function buscarItensSolcom($_idprodservs, $_idcotacao)
	{
		$results = SQL::ini(SolcomItemQuery::buscarItensSolcom(), [
			"idcotacao" => $_idcotacao,
			"idprodservs" => $_idprodservs
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function buscarQuantidadeItensSolcomPorIdSolcolmItem($idsolcomitem)
	{
		$results = SQL::ini(SolcomQuery::buscarQuantidadeItensSolcomPorIdSolcolmItem(), [
			"idsolcomitem" => $idsolcomitem
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function buscarSolcomQuantidadeItensSolcomCotacao($idsolcomitem, $idprodserv, $idcotacao)
	{
		$results = SQL::ini(SolcomItemQuery::buscarSolcomQuantidadeItensSolcomCotacao(), [
			"idsolcom" => $idsolcomitem,
			"idprodserv" => $idprodserv,
			"idcotacao" => $idcotacao
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function atualizarStatusIdcotacaoSolcomItem($idcotacao, $status, $idsolcomitem, $usuario)
	{
		$idcotacao = empty($idcotacao) ? 'NULL' : $idcotacao;
		$results = SQL::ini(SolcomItemQuery::atualizarStatusIdcotacaoSolcomItem(), [
			"idcotacao" => $idcotacao,
			"status" => $status,
			"idsolcomitem" => $idsolcomitem,
			"usuario" => $usuario
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function atualizarSolcomItensAssociados($novo_idcotacao, $idcotacao, $idprodserv, $usuario)
	{
		$results = SQL::ini(SolcomItemQuery::atualizarSolcomItensAssociados(), [
			"novo_idcotacao" => $novo_idcotacao,
			"idcotacao" => $idcotacao,
			"idprodserv" => $idprodserv,
			"usuario" => $usuario
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function buscarSolcomItemPorIdSolcomItem($idsolcomitem)
	{
		$results = SQL::ini(SolcomItemQuery::buscarSolcomItemPorIdSolcomItem(), [
			"idsolcomitem" => $idsolcomitem
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data[0];
		}
	}

	public static function buscarProdutosInseridosSolcomItem($idsolcom)
	{
		$results = SQL::ini(SolcomItemQuery::buscarProdutosInseridosSolcomItem(), [
			"idsolcom" => $idsolcom
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data[0];
		}
	}

	public static function listarProdutos($idsolcom)
	{
		$itensSolcomItem = self::buscarProdutosInseridosSolcomItem($idsolcom);
		if($itensSolcomItem['stidprodserv'] == ''){
			$nothere = '';
		}else{
			$nothere = " AND p.idprodserv not in(".$itensSolcomItem['stidprodserv'].")";
		};

		$itensSolcom = ProdServController::buscarProdutoOuServicoComprado($nothere);

		if($itensSolcom['qtdLinhas'] > 0)
		{
			$arrret = array();
			$i = 0; 
			foreach($itensSolcom['dados'] AS $_itens)
			{
				$itensSolcom[$i]["descr"] = $_itens["descr"];
				$itensSolcom[$i]["idprodserv"] = $_itens["idprodserv"];
				$itensSolcom[$i]["codprodserv"] = $_itens["codprodserv"];
				$itensSolcom[$i]["un"] = $_itens["un"];
				$i++;
			}
			return $itensSolcom;
			
		}else{
			return 0;
		}
	}

	public static function listarProdutosFabricados($idsolcom)
	{
		$itensSolcomItem = self::buscarProdutosInseridosSolcomItem($idsolcom);
		if($itensSolcomItem['stidprodserv'] == ''){
			$nothere = '';
		}else{
			$nothere = " AND p.idprodserv not in(".$itensSolcomItem['stidprodserv'].")";
		};

		$itensSolcom = ProdServController::buscarProdutoOuServicoFabricado($nothere);

		if($itensSolcom['qtdLinhas'] > 0)
		{
			$arrret = array();
			$i = 0; 
			foreach($itensSolcom['dados'] AS $_itens)
			{
				$itensSolcom[$i]["descr"] = $_itens["descr"];
				$itensSolcom[$i]["idprodservformula"] = $_itens["idprodservformula"];
				$itensSolcom[$i]["rotulo"] = $_itens["rotulo"];
				$itensSolcom[$i]["un"] = $_itens["un"];
				$i++;
			}
			return $itensSolcom;
			
		}else{
			return 0;
		}
	}
	
	public static function buscarPreferenciaPessoa($caminho, $idpessoa)
	{
		return PessoaController::buscarPreferenciaPessoa($caminho, $idpessoa);
	}

	public static function listarFillSelectUnidadeAtivo($tipoobjetovinc, $tipoobjeto, $idobjeto, $idpessoa, $comprasMaster)
	{
		return UnidadeController::listarFillSelectUnidadeAtivo($tipoobjetovinc, $tipoobjeto, $idobjeto, $idpessoa, $comprasMaster);
	}

	public static function buscarItensSolcomAssociadosSolmat($idsolcom, $tipo)
	{
		if($tipo == 'cadastrado')
		{
			$colunas = "CONCAT(IF(p.tipo = 'PRODUTO', 'PROD', 'SERV'),' - ', p.descr) AS descrprod, p.obs, ";
			$condicaoJoin = " JOIN prodserv p ON p.idprodserv = si.idprodserv";
			$condicaoAnd =" AND si.fabrica='N'";
		}elseif($tipo == 'fabricado'){

			$colunas = "CONCAT(IF(p.tipo = 'PRODUTO', 'PROD', 'SERV'),' - ', p.descr) AS descrprod, p.obs, ";
			$condicaoJoin = " JOIN prodserv p ON p.idprodserv = si.idprodserv";
			$condicaoAnd =" AND si.fabrica='Y'";
		}
		 else {
			$condicaoAnd = "AND (si.idprodserv is null or si.idprodserv = 0) ";
		}

		$results = SQL::ini(SolcomItemQuery::buscarItensSolcomAssociadosSolmat(), [
			"colunas" => $colunas,
			"condicaoJoin" => $condicaoJoin,
			"condicaoAnd" => $condicaoAnd,
			"idsolcom" => $idsolcom
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		}else{
			$dados['dados'] = $results->data;
			$dados['sql'] = $results->sql();
			$dados['qtdLinhas'] = $results->numRows();

			return $dados;
		}
	}

	public static function buscarItensSolcomCancelados($idsolcom)
	{
		$results = SQL::ini(SolcomItemQuery::buscarItensSolcomCancelados(), [
			"idsolcom" => $idsolcom
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->numRows();
		}
	}

	public static function buscarItensSolcomAssociadosCotacao($idcotacao, $idprodserv)
	{
		$results = SQL::ini(SolcomItemQuery::buscarItensSolcomAssociadosCotacao(), [
			"idcotacao" => $idcotacao,
			"idprodserv" => $idprodserv
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			$dados['dados'] = $results->data;
			$dados['qtdLinhas'] = $results->numRows();

			return $dados;
		}
	}

	public static function buscarLinksProdutos($idobjeto, $tipoobjeto)
	{
		$results = SQL::ini(ObjetoLinkQuery::buscarLinkPorTipoObjeto(), [
			"idobjeto" => $idobjeto,
			"tipoobjeto" => $tipoobjeto
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			$dados['dados'] = $results->data;
			$dados['qtdLinhas'] = $results->numRows();

			return $dados;
		}
	}

	public static function buscarComentarioSolcom($idsolcom)
	{
		$results = SQL::ini(SolcomQuery::buscarComentarioSolcom(), [
			"idsolcom" => $idsolcom
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			$dados['dados'] = $results->data;
			$dados['qtdLinhas'] = $results->numRows();

			return $dados;
		}
	}

	public static function buscarGrupoUnidadePorTipoObjeto($idobjeto, $tipoobjeto)
	{
		$unidade = UnidadeController::buscarGrupoUnidadePorTipoObjeto($idobjeto, $tipoobjeto);
		return $unidade['idunidade'];
	}

	public static function buscarCotacaoDisponivelPorGrupoEsTipoItem($idprodserv)
	{
		return CotacaoController::buscarCotacaoDisponivelPorGrupoEsTipoItem($idprodserv);
	}

	public static function buscarItensSolcomGerarSolmat($idsolcom)
	{
		$results = SQL::ini(SolcomItemQuery::buscarItensSolcomGerarSolmat(), [
			"idsolcom" => $idsolcom
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function buscarItensSolcomGerarSoltag($idsolcom)
	{
		$results = SQL::ini(SolcomItemQuery::buscarItensSolcomGerarSoltag(), [
			"idsolcom" => $idsolcom
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}

	public static function buscarQtdItensSolcomItem($idsolcom)
	{
		$results = SQL::ini(SolcomItemQuery::buscarQtdItensSolcomItem(), [
			"idsolcom" => $idsolcom
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->numRows();
		}
	}

	public static function buscarDadosProdutoPorIdsolcomItem($idsolcomitem)
	{
		$results = SQL::ini(SolcomQuery::buscarDadosProdutoPorIdsolcomItem(), [
			"idsolcomitem" => $idsolcomitem
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			$dados['dados'] = $results->data[0];
			$dados['qtdLinhas'] = $results->numRows();

			return $dados;
		}
	}

	public static function atualizarStatusSolcomItem($status, $idsolcom)
	{
		$results = SQL::ini(SolcomItemQuery::atualizarStatusSolcomItem(), [
			"status" => $status,
			"idsolcom" => $idsolcom
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function atualizarStatusSolcom($status, $idsolcom)
	{
		$results = SQL::ini(SolcomQuery::atualizarStatusSolcom(), [
			"status" => $status,
			"idsolcom" => $idsolcom
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function inserirSolmat($idempresa, $status, $idfluxostatus, $tipo, $idunidade, $unidade, $usuario)
	{
		return SolmatController::inserirSolmat($idempresa, $status, $idfluxostatus, $tipo, $idunidade, $unidade, $usuario);
	}

	public static function inserirSolmatItem($arrayInsertSolmatItem)
	{
		return SolmatController::inserirSolmatItem($arrayInsertSolmatItem);
	}

	public static function atualizarSolmatSolcomItem($idsolmatitem, $idsolcomitem)
	{
		$results = SQL::ini(SolcomItemQuery::atualizarSolmatSolcomItem(), [
			"idsolmatitem" => $idsolmatitem,
			"idsolcomitem" => $idsolcomitem
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function atualizarSolmatSolTagItem($idsoltagitem, $idsolcomitem)
	{
		$results = SQL::ini(SolcomItemQuery::atualizarSolmatSolTagItem(), [
			"idsoltagitem" => $idsoltagitem,
			"idsolcomitem" => $idsolcomitem
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function atualizarIdProdservPorIdSolcomItem($idprodserv, $idsolcomitem)
	{
		$results = SQL::ini(SolcomItemQuery::atualizarIdProdservPorIdSolcomItem(), [
			"idprodserv" => $idprodserv,
			"idsolcomitem" => $idsolcomitem
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE -----
	public static function listarUnidadeVolume()
	{
		$resultsUnidadeVolume = SQL::ini(UnidadeVolumeQuery::buscarUnidadeVolume())::exec();

        if($resultsUnidadeVolume->error()){
            parent::error(__CLASS__, __FUNCTION__, $resultsUnidadeVolume->errorMessage());
            return [];
        }else{
			$arrUnidadeVolume = [];
			foreach($resultsUnidadeVolume->data as $_dadosUnidadeVolume)
			{	
				$arrUnidadeVolume[$_dadosUnidadeVolume['un']] = $_dadosUnidadeVolume['un'];	
			}

            return $arrUnidadeVolume;
        }		
	}
	//----- AUTOCOMPLETE -----
}

?>