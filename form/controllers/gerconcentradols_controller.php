<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/prodserv_query.php");
require_once(__DIR__."/../querys/lote_query.php");

//Controllers
require_once(__DIR__."/../controllers/dashboard_controller.php");
require_once(__DIR__."/../controllers/pessoa_controller.php");

class GerconcentradolsController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarProdutosProgramadosGerenciamentoConcentrados($clausulalote, $clausulad)
	{
		$compartilharCbUserGerConcentradoProdServForn = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServForn("v.idprodserv");
		$results = SQL::ini(ProdservQuery::buscarProdutosProgramadosGerenciamentoConcentrados(), [
			"idempresa" => $compartilharCbUserGerConcentradoProdServForn,
			"clausulalote" => $clausulalote,
			"clausulad" => $clausulad
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarProdutosPedidosGerenciamentoConcentrados($clausulalote, $clausulad, $orderBy, $dataenvio = NULL)
	{	
		$dataenvioCondicao = empty($dataenvio) ? '' : "AND (n.envio IS NULL OR n.envio <= '$dataenvio')";
		$compartilharCbUserGerConcentradoProdServ = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServ("v.idprodserv");
		$results = SQL::ini(ProdservQuery::buscarProdutosPedidosGerenciamentoConcentrados(), [
			"idempresa" => $compartilharCbUserGerConcentradoProdServ,
			"clausulalote" => $clausulalote,
			"clausulad" => $clausulad,
			"dataenvioCondicao" => $dataenvioCondicao,
			"orderBy" => $orderBy
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarProdutosProgramadosPedidosGerenciamentoConcentrados($clausulalote, $clausulad,$dataenvio)
	{
		$compartilharCbUserGerConcentradoProdServForn = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServForn("v.idprodserv");
		$compartilharCbUserGerConcentradoProdServ = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServ("v.idprodserv");
		$results = SQL::ini(ProdservQuery::buscarProdutosProgramadosPedidosGerenciamentoConcentrados(), [
			"idempresa1" => $compartilharCbUserGerConcentradoProdServForn,
			"idempresa2" => $compartilharCbUserGerConcentradoProdServ,
			"clausulalote" => $clausulalote,
			"dataenvio"=>$dataenvio,
			"clausulad" => $clausulad
        ])::exec();

	
        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarConcenteradosProgramado($idpessoa, $idprodserv, $clausulalote, $clausulad)
	{
		$compartilharCbUserGerConcentradoProdServForn = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServForn("v.idprodserv");
		$results = SQL::ini(ProdservQuery::buscarConcenteradosProgramado(), [
			"idempresa" => $compartilharCbUserGerConcentradoProdServForn,
			"idpessoa" => $idpessoa,
			"idprodserv" => $idprodserv,
			"clausulalote" => $clausulalote,
			"clausulad" => $clausulad
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarConcenteradosPedido($idpessoa, $idprodserv, $clausulalote, $clausulad, $dataenvio = NULL)
	{
		$dataenvioCondicao = empty($dataenvio) ? '' : "AND (n.envio IS NULL OR n.envio <= '{$dataenvio}')";
		$compartilharCbUserGerConcentradoProdServ = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServ("p.idprodserv");
		$results = SQL::ini(ProdservQuery::buscarConcenteradosPedido(), [
			"idempresa" => $compartilharCbUserGerConcentradoProdServ,
			"idpessoa" => $idpessoa,
			"idprodserv" => $idprodserv,
			"clausulalote" => $clausulalote,
			"dataenvioCondicao" => $dataenvioCondicao,
			"clausulad" => $clausulad
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
			return $dados;
        }
	}

	public static function buscarConcenteradosProgramadoPedido($idpessoa, $idprodserv, $clausulalote, $clausulad,$dataenvio)
	{
		$compartilharCbUserGerConcentradoProdServForn = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServForn("v.idprodserv");
		$compartilharCbUserGerConcentradoProdServ = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServ("p.idprodserv");
		$results = SQL::ini(ProdservQuery::buscarConcenteradosProgramadoPedido(), [
			"idempresa" => $compartilharCbUserGerConcentradoProdServForn,
			"idempresa2" => $compartilharCbUserGerConcentradoProdServ,
			"clausulalote" => $clausulalote,
			"idpessoa" => $idpessoa,
			"dataenvio"=>$dataenvio,
			"idprodserv" => $idprodserv,
			"clausulad" => $clausulad
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarQtdLoteAbertoAutorizada($idpessoa, $idprodservvacina, $idprodservformula)
	{
		$results = SQL::ini(LoteQuery::buscarQtdLoteAbertoAutorizada(), [
			"idpessoa" => $idpessoa,
			"idprodservvacina" => $idprodservvacina,
			"idprodservformula" => $idprodservformula
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data[0];
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarSementes($idpessoa, $idprodserv,$idsolfab=null)
	{
		$compartilharCbUserGerConcentradoProdServ = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServ("p.idprodserv");

		$results = SQL::ini(LoteQuery::buscarSementes(), [
			"idempresa" => $compartilharCbUserGerConcentradoProdServ,
			"idpessoa" => $idpessoa,
			"idprodserv" => $idprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarConcentradosSementes($idlote)
	{
		$results = SQL::ini(LoteQuery::buscarConcentradosSementes(), [
			"idlote" => $idlote
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
			$dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}
	
	public static function buscarQuantidadeConcentradosSementes($idlote)
	{
		$results = SQL::ini(LoteQuery::buscarQuantidadeConcentradosSementes(), [
			"idlote" => $idlote
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}
	
	public static function buscarLotesDisponiveis($idpessoa, $idprodserv)
	{
		$compartilharCbUserGerConcentradoProdServ = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServ("pl.idprodserv");
		$results = SQL::ini(LoteQuery::buscarLotesDisponiveis(), [
			"idpessoa" => $idpessoa,
			"idprodserv" => $idprodserv,
			"idempresa" => $compartilharCbUserGerConcentradoProdServ
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarAlertaTipificacaoLote($idlote)
	{
		$results = SQL::ini(LoteQuery::buscarAlertaTipificacaoLote(), [
			"idlote" => $idlote
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
			$dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function atualizarDashboardGerencimentoConcentrados($haproduzir)
	{
		return DashboardController::atualizarDashboardGerencimentoConcentrados($haproduzir);
	}

	public static function calculapreciso($qtdi, $qtdi_exp, $qtdprod, $qtdpadrao = 1)
	{
		if(strpos(strtolower(recuperaExpoente($qtdi, $qtdi_exp)), "d"))
		{
			$arrExp = explode('d', strtolower(recuperaExpoente($qtdi, $qtdi_exp)));
			$vqtdpadrao = $arrExp[0];

			$v1 = (floatval($qtdprod) * floatval($vqtdpadrao)) / floatval($qtdpadrao);
			$v2 = $v1 * $arrExp[1];	
			$rotpreciso = $v2;
		}elseif(strpos(strtolower(recuperaExpoente($qtdi, $qtdi_exp)),"e")){
			$arrExp = explode('e', strtolower(recuperaExpoente($qtdi, $qtdi_exp)));
			$vqtdpadrao =  $arrExp[0];
			
			$v1 = (floatval($qtdprod) * floatval($vqtdpadrao)) / floatval($qtdpadrao);
			$v2 = $v1*$arrExp[1];	
			$rotpreciso = $v2;
		}else{
			$vqtdpadrao = (empty($qtdi) || $qtdi == 0) ? 1 : $qtdi; 

			$preciso = (floatval($qtdprod) * floatval($vqtdpadrao)) / floatval($qtdpadrao);
			$rotpreciso = $preciso;
		}
		
		return $rotpreciso;
	}
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE -----
	public static function listarPessoaVinculadaLote()
	{
		return PessoaController::listarPessoaVinculadaLote();
	}

	public static function buscarProdutoServicoEspecialVinculadoProdservPrProc()
	{
		$compartilharCbUserGerConcentradoProdServ = share::otipo('cb::usr')::compartilharCbUserGerConcentradoProdServ("p.idprodserv");
		$results = SQL::ini(ProdservQuery::buscarProdutoServicoEspecialVinculadoProdservPrProc(), [
			"idempresa" => $compartilharCbUserGerConcentradoProdServ
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            $prodservVinculadas = $results->data;
			foreach($prodservVinculadas as $_prodserv)
			{	
				$prodserv[$_prodserv['idprodserv']]['descr'] = $_prodserv['descr'];
			}

			return $prodserv;
        }
	}
	//----- AUTOCOMPLETE -----

	// ----- Variáveis de apoio -----
	public static $_status = array('FALTA' => 'Falta');

	public static $_tipo = array('PROGRAMADO' => 'Programado',
								 'PEDIDO' => 'Pedido',
								 'TODOS' => 'Todos');

	public static $tipoagente = array(
        '' => '',
        'VIRAL' => 'Viral',
        'BACTERIANO' => 'Bacteriano',
        'VIRALBACTERIANO' => 'Viral + Bacteriano'
    );

	// ----- Variáveis de apoio -----
}
?>