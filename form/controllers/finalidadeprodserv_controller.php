
<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/finalidadeprodserv_query.php");

class FinalidadeProdservController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarFinalidadeProdservPorIdPessoa($_idpessoas,$idempresa = null)
	{

		if($idempresa==null){
			$_idempresa=getidempresa('c.idempresa', 'finalidadeprodserv');
		}else{
			$_idempresa=' and fe.idempresaobj='.$idempresa;
		}
		$results = SQL::ini(FinalidadeProdservQuery::buscarFinalidadeProdservPorIdPessoa(), [
            "idpessoas" => $_idpessoas,
			"status" => " AND c.status = 'ATIVO'",
			"idempresa" => $_idempresa
        ])::exec();
		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
	}

	public static function buscarFinalidadeProdserv()
	{
		$results = SQL::ini(FinalidadeProdservQuery::buscarFinalidadeProdserv(), [
			"idempresa" => getidempresa('c.idempresa', 'finalidadeprodserv')
        ])::exec();
		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return [];
        }else{
            return $results->data;
        }
	}
	// ----- FUNÇÕES ----- 
}
?>