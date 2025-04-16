<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/conferenciaitem_query.php");
require_once(__DIR__."/../querys/nfconferenciaitem_query.php");

class ConferenciaItemController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarNfConferenciaItem($idnf)
	{
		$results = SQL::ini(NfConferenciaItemQuery::buscarNfConferenciaItem(), [
			"idnf" => $idnf
        ])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            $dados['dados'] = $results->data;
            $dados['qtdLinhas'] = $results->numRows();
            return $dados;
        }
	}

	public static function buscarConferenciaItem($tiponf)
	{
		$results = SQL::ini(ConferenciaItemQuery::buscarConferenciaItem(), [
			"tiponf" => $tiponf,
			"getidempresa" => getidempresa('idempresa', 'conferenciaitem')
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

	public static function inserirNfConferenciaItem($idempresa, $idnf, $tiponf)
	{
		$results = SQL::ini(NfConferenciaItemQuery::inserirNfConferenciaItem(), [
			"idempresa" => $idempresa,
			"idnf" => $idnf,
			"tiponf" => $tiponf
        ])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        }
	}
	// ----- FUNÇÕES -----
}
?>