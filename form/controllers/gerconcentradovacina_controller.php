<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/formalizacao_query.php");

//Controllers
require_once(__DIR__."/../controllers/nf_controller.php");

class GerConcentradoVacinaController extends Controller
{   
    // ----- FUNÇÕES -----
    public static function buscarVacinasProgramadas($clausulalote, $clausulad, $clausulapr, $status)
	{
		$results = SQL::ini(FormalizacaoQuery::buscarVacinasProgramadas(), [
			"idempresa" => getidempresa('p.idempresa','prodserv'),
			"clausulalote" => $clausulalote,
			"clausulad" => $clausulad,
            "clausulapr" => $clausulapr,
            "status" => $status
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

    public static function buscarSementesVacina($idpessoa, $clausulalote, $clausulad)
	{
		$results = SQL::ini(FormalizacaoQuery::buscarSementesVacina(), [
			"idpessoa" => $idpessoa,
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

    public static function buscarDataEnvioNfitem($idlote)
	{
        return nfController::buscarDataEnvioNfitem($idlote);
    }
    // ----- FUNÇÕES -----

    //----- AUTOCOMPLETE -----
    public static function listarPessoaVinculadaLote()
	{
		return PessoaController::listarPessoaVinculadaLote();
	}

    public static function buscarProdutoServicoEspecialVinculadoProdservPrProc()
	{
		$results = SQL::ini(ProdservQuery::buscarProdutoServicoEspecialVinculadoProdservPrProc(), [
			"idempresa" => getidempresa('p.idempresa', 'prodserv')
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
	public static $_status = array('SUFICIENTE' => 'Suficiente',
                                   'INSUFICIENTE' => 'Insuficiente');
	// ----- Variáveis de apoio -----
}
?>