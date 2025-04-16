<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/entregaepi_query.php");

class EntregaEpiController extends Controller
{
	public static function buscarEntregaepiporId($identregaepi)
	{
		$results = SQL::ini(PessoaQuery::buscarPorChavePrimaria(), [
			"pkval" => $identregaepi,
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		} else {
			return $results->data[0];
		}
	}

	// ----- FUNÇÕES -----
	public static function buscaDadosColaborador($idPessoa)
	{
		$results = SQL::ini(EntregaEpi::buscaDadosColaborador(), ["idpessoa" => $idPessoa])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		} else {
			return $results->data[0];
		}
	}

	public static function buscaSolmatDisponivel($idempresa)
	{
		$results = SQL::ini(EntregaEpi::buscaSolmatDisponivel(), ["idempresa" => $idempresa])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		} else {
			return $results->data;
		}
	}
}
