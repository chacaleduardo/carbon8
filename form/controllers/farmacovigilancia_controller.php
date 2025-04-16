<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/especiefinalidade_query.php");
require_once(__DIR__."/../querys/produtofarmacovigilancia_query.php");

//Controllers
require_once(__DIR__."/pessoa_controller.php");
require_once(__DIR__."/prodserv_controller.php");
require_once(__DIR__."/endereco_controller.php");
require_once(__DIR__."/nf_controller.php");

class FarmacovigilanciaController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarEnderecoPessoaPorIdEndereco($idendereco)
	{
		return EnderecoController::buscarEnderecoPessoaPorIdEndereco($idendereco);
	}

	public static function buscarEnderecoPorIdpessoaOption($idpessoa){
		return EnderecoController::buscarEnderecoPorIdpessoa($idpessoa);
	}
	
	public static function buscarEnderecoPorIdpessoaFiltrado($idpessoa){
		$results = EnderecoController::buscarEnderecoPorIdpessoa($idpessoa);
		foreach($results as $_endereco)
		{	
			if($_endereco["idtipoendereco"] == 1){
				$arrListarEndereco = $_endereco["endereco"];

			}
		}

		return $arrListarEndereco;
	}

	public static function buscarProdutos()
	{
        $prodServ = SQL::ini(ProdServQuery::buscarProdServPorCondicao(), [
            'condicao' => ' AND p.tipo IN ("SERVICO", "PRODUTO") AND p.status = "ATIVO"',
            'getidempresa' => getidempresa('p.idempresa', 'prodserv'),
            'orderby' => ' ORDER BY p.descr'
        ])::exec();

        if ($prodServ->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodServ->errorMessage());
            return [];
        } else {
			return $prodServ->data;
		}
    }
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE ----
    public static function listarClientes()
	{
        $listarClientes = SQL::ini(PessoaQuery::buscarClientesAmostra(), [
            'getidempresa' => getidempresa('p.idempresa', 'pessoa'),
        ])::exec();

        if ($listarClientes->error()) {
            parent::error(__CLASS__, __FUNCTION__, $listarClientes->errorMessage());
            return [];
        } else {
			foreach($listarClientes->data as $_clientes)
			{	
				$arrListarClientes[$_clientes["idpessoa"]]["nome"] = $_clientes["nome"];
				$arrListarClientes[$_clientes["idpessoa"]]["tipo"] = $_clientes["tipo"];
				$arrListarClientes[$_clientes["idpessoa"]]["razaosocial"] = $_clientes["razaosocial"];
			}

			return $arrListarClientes;
		}
    }

	public static function listarProdutos()
	{
        $prodServ = SQL::ini(ProdServQuery::buscarProdServPorCondicao(), [
            'condicao' => ' AND p.tipo = "PRODUTO" AND p.venda = "Y" ',
            'getidempresa' => getidempresa('p.idempresa', 'prodserv'),
            'orderby' => ' ORDER BY p.descr'
        ])::exec();

        if ($prodServ->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodServ->errorMessage());
            return [];
        } else {
			foreach($prodServ->data as $_prodserv)
			{	
				$arrListarProdserv[$_prodserv["idprodserv"]]["descr"] = $_prodserv["descr"];
			}

			return $arrListarProdserv;
		}
    }

	public static function burscarLotePorProdserv($idprodserv)
    {
		return LoteController::burscarLotePorProdserv($idprodserv);
	}

	public static function buscarEspeciefinalidade($idempresa)
    {
		$results = SQL::ini(EspecieFinalidadeQuery::buscarEspeciefinalidadeComPlantel(), [
            'idempresa' => $idempresa,
        ])::exec();

        if ($results->error()) {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        } else {
			foreach($results->data as $_especie)
			{	
				$arrListarProdserv[$_especie["idespeciefinalidade"]]["descr"] = $_especie["especie"].' - '.$_especie["finalidade"];
			}

			return $arrListarProdserv;
		}
	}

	public static function buscarPessoaPorIdnf($idnf){
		$results = NfController::buscarNfPessoaPorIdNf($idnf);
		return $results[0]["idpessoa"];
	}

	public static function buscarEnderecoPorIdpessoa($idpessoa){
		$results = EnderecoController::buscarEnderecoPorIdpessoa($idpessoa);
		foreach($results as $_endereco)
		{	
			$arrListarProdserv[$_endereco["idendereco"]]["descr"] = $_endereco["endereco"];
		}

		return $arrListarProdserv;
	}

	public static function buscarProdutosPorIdFarmacovigilancia($idfarmacovigilancia){
		$prodServ = SQL::ini(ProdutoFarmacovigilanciaQuery::buscarProdutosPorIdFarmacovigilancia(), [
            'idfarmacovigilancia' => $idfarmacovigilancia,
        ])::exec();

        if ($prodServ->error()) {
            parent::error(__CLASS__, __FUNCTION__, $prodServ->errorMessage());
            return [];
        } else {
			return $prodServ->data;
		}
	}
	
	//----- AUTOCOMPLETE ----

	// ----- Variáveis de apoio -----
	// ----- Variáveis de apoio -----
}
?>