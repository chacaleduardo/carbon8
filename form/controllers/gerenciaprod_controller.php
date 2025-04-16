<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/prodserv_query.php");
require_once(__DIR__."/../querys/lote_query.php");

//Controllers
require_once(__DIR__."/../controllers/pessoa_controller.php");
require_once(__DIR__."/../controllers/prodserv_controller.php");
require_once(__DIR__."/../controllers/_snippet_controller.php");

class GerenciaProdController extends Controller
{
    public static function listarPessoaVinculadaLote()
	{
        return PessoaController::listarPessoaVinculadaLote();
    }

    public static function buscarProdservVinculadoAoLote()
	{
        return ProdservController::buscarProdservVinculadoAoLote();
    }

    public static function buscarSnippetPorNotificacaoEEmpresa()
	{
        return _SnippetController::buscarSnippetPorNotificacaoEEmpresa();
    }

    public static function buscarProdutoComFormula($invalidacao, $strinplantel, $clausulad, $strvalidacao, $clausulalote)
	{
        $results = SQL::ini(ProdservQuery::buscarProdutoComFormula(), [
            "invalidacao" => $invalidacao,
			"strinplantel" => $strinplantel,
			"clausulad" => $clausulad,
            "strvalidacao" => $strvalidacao,
            "clausulalote" => $clausulalote
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
    
    public static function buscarProdutoPorIdProdserv($idprodserv)
	{
        return ProdservController::buscarProdutoPorIdProdserv($idprodserv);
    }

    public static function buscarFormulaPorFornecedor($clausulalote, $strplantel, $strvalidacao, $idprodserv)
    {
        $results = SQL::ini(LoteQuery::buscarFormulaPorFornecedor(), [ 
            "clausulalote" => $clausulalote,
            "strplantel" => $strplantel,
            "strvalidacao" => $strvalidacao,
            "idprodserv" => $idprodserv
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarFormulaEAmostraPorIdProdserv($clausulals, $clausulastatusfracao, $idpessoa, $idprodserv, $strplantel)
    {
        $results = SQL::ini(LoteQuery::buscarFormulaEAmostraPorIdProdserv(), [ 
            "clausulals" => $clausulals,
            "clausulastatusfracao" => $clausulastatusfracao,
            "idpessoa" => $idpessoa,
            "idprodserv" => $idprodserv,
            "strplantel" =>$strplantel
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }

    public static function buscarProdservFormulaEAmostraPorIdProdserv($idpessoa, $idprodservant, $idprodserv, $idprodservformula, $strplantel)
    {
        $results = SQL::ini(LoteQuery::buscarProdservFormulaEAmostraPorIdProdserv(), [ 
            "idpessoa" => $idpessoa,
            "idprodservant" => $idprodservant,
            "idprodserv" => $idprodserv,
            "idprodservformula" => $idprodservformula,            
            "strplantel" =>$strplantel
        ])::exec();

        if($results->error())
        {
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['sql'] = $results->sql();
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
    }
}
?>