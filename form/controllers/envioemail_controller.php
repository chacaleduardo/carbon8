
<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/mailfila_query.php");
require_once(__DIR__."/../querys/empresaemails_query.php");
require_once(__DIR__."/../querys/empresaemailobjeto_query.php");

class EnvioEmailController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarMailFila($_idnfs)
	{
		$results = SQL::ini(MailFilaQuery::buscarMailFila(), [
            "idempresa" => getidempresa('m.idempresa','envioemail'),
			"idnfs" => $_idnfs
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "Não foi possível retornar Informações sobre o MailFila";
        }else{
            return $results->data;
        }
	}

    public static function buscarDominio($idempresa)
	{
		$results = SQL::ini(EmpresaEmailsQuery::buscarDominio(), [
            "idempresa" => $idempresa
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "Não foi possível retornar Informações sobre o Domínio";
        }else{
            return $results->data;
        }
	}

    public static function buscarEmpresaemailobjeto($idnf)
    {
        $results = SQL::ini(EmpresaEmailObjetoQuery::buscarEmpresaemailobjeto(), [
            "idnfs" => $idnf,
            "idempresa" => getidempresa('idempresa','empresa')
        ])::exec();

        if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data;
        }
    }
    // ----- FUNÇÕES -----
}

?>