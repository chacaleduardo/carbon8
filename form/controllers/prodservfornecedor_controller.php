<?
require_once(__DIR__."/_controller.php");


// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/prodserv_query.php");

//Controllers
require_once(__DIR__."/../controllers/nf_controller.php");
require_once(__DIR__."/../controllers/prodserv_controller.php");
require_once(__DIR__."/../controllers/pessoa_controller.php");

class ProdservFornecedorController  extends Controller
{
	// ----- FUNÇÕES -----
    public static function buscarProdservPorVendaMaterialIdTipoProdserv($idtipoprodserv)
	{
        $results = SQL::ini(ProdservQuery::buscarProdservPorVendaMaterialIdTipoProdserv(), [          
            "idtipoprodserv" => $idtipoprodserv
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }

	public static function buscarQtdProdservFornPorIdprodserv($idprodserv, $condicaoStatus = NULL)
	{
		return ProdServController::buscarQtdProdservFornPorIdprodserv($idprodserv, $condicaoStatus);
	}

	public static function buscarProdservFornPorIdprodserv($idprodserv)
	{
		return ProdServController::buscarProdservFornPorIdprodserv($idprodserv);
	}

	public static function buscarUnidadeVolume()
	{
		return ProdServController::buscarUnidadeVolume();
	}

	public static function buscarProdservFornProdservPorIdprodserv($idprodservforn)
	{
		return ProdServController::buscarProdservFornProdservPorIdprodserv($idprodservforn);
	}

	public static function buscarQtdFornecedorNfItem($idprodservforn)
	{
		return NfController::buscarQtdFornecedorNfItem($idprodservforn);
	}
	
	public static function listarPessoaIdempresaGrupoNulo()
	{
		return PessoaController::listarPessoaIdempresaGrupoNulo();
	}
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE -----
	public static function buscarFornecedorPorSessionIdEmpresaEIdTipoPessoa($idtipopessoa)
	{
		return PessoaController::buscarFornecedorPorSessionIdEmpresaEIdTipoPessoa($idtipopessoa);
	}
	//----- AUTOCOMPLETE -----
}
?>