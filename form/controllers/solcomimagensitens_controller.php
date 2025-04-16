<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/arquivo_query.php");

class SolcomImagensItensController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarArquivoPorTipoArquivoTipoobjetoIdobjeto($tipoarquivo, $tipoobjeto, $idobjeto)
	{
		$results = SQL::ini(ArquivoQuery::buscarArquivoPorTipoArquivoTipoobjetoIdobjeto(), [
			"tipoarquivo" => $tipoarquivo,
			"tipoobjeto" => $tipoobjeto,
			"idobjetos" => $idobjeto
		])::exec();

		if($results->error())
		{
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		}else{
			return $results->data;
		}
	}
	// ----- FUNÇÕES -----
}
?>