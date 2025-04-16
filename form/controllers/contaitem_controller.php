<?
require_once(__DIR__ . "/_controller.php");

// QUERYS
require_once(__DIR__ . "/../querys/_iquery.php");
require_once(__DIR__ . "/../querys/contaitem_query.php");
require_once(__DIR__ . "/../querys/modulohistorico_query.php");



class ContaItemController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarGrupoES($idobjeto, $tipoobjeto)
	{
		$contaItemPorCbUserIdempresa = share::otipo('cb::usr')::compartilharCbUserContaitem("c.idcontaitem");
		$contaItemPorCbUserIdempresa = $contaItemPorCbUserIdempresa ? $contaItemPorCbUserIdempresa : 'AND p.idempresa = ' . cb::idempresa();
		$results = SQL::ini(ContaItemQuery::buscarGrupoES(), [
			"idobjeto" => $idobjeto,
			"tipoobjeto" => $tipoobjeto,
			"getidempresa" => getidempresa('c.idempresa', 'contaitem'),
			"compartilharCbUserContaitem" => $contaItemPorCbUserIdempresa
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return [];
		} else {
			return (count($results->data) > 0) ? $results->data : "";
		}
	}

	public static function buscarContaItemProdservContaItem($idprodservs, $status = NULL, $tiponf = NULL, $idNf = NULL)
	{
		// nf tipo outros so deve lidatar somarelatorio não por causa do despesasitem e finextrato
		if ($tiponf == null) {
			$somarRelatorio = " AND  c.somarelatorio in ('N','Y') ";
		} elseif ($tiponf == 'O') {
			$somarRelatorio = " AND  c.somarelatorio ='N' ";
		} elseif ($tiponf != 'C') {
			$somarRelatorio = " AND  c.somarelatorio ='Y' ";
		}
		$union = "";
		if ($idNf) {
			$union = "UNION
				SELECT c.idcontaitem, c.contaitem, n.idprodserv, NULL AS idprodservcontaitem
				FROM nfitem n
				LEFT JOIN contaitem c ON c.idcontaitem = n.idcontaitem
				WHERE n.idnfitem = $idNf
				GROUP BY idcontaitem, contaitem, idprodserv, idprodservcontaitem";
		}
		$results = SQL::ini(ContaItemQuery::buscarContaItemProdservContaItem(), [
			"idprodservs" => $idprodservs,
			"somarRelatorio" => $somarRelatorio,
			"union" => $union
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarContaItemProdservContaItemPorNf($idNf, $tiponf = NULL)
	{
		// nf tipo outros so deve lidatar somarelatorio não por causa do despesasitem e finextrato
		$somarRelatorio = '';
		if ($tiponf == null) {
			$somarRelatorio = " AND  c.somarelatorio in ('N','Y') ";
		} elseif ($tiponf == 'O') {
			$somarRelatorio = " AND  c.somarelatorio ='N' ";
		} elseif ($tiponf != 'C') {
			$somarRelatorio = " AND  c.somarelatorio ='Y' ";
		}

		$results = SQL::ini(ContaItemQuery::buscarContaItemProdservContaItemPorNf(), [
			"idnf" => $idNf,
			"somarRelatorio" => $somarRelatorio
		])::exec();

		$arrNf = [];
		foreach ($results->data as $_nf) {
			$arrNf[$_nf['idnfitem']][$_nf['idcontaitem']]['idcontaitem'] = $_nf['idcontaitem'];
			$arrNf[$_nf['idnfitem']][$_nf['idcontaitem']]['contaitem'] = $_nf['contaitem'];
			$arrNf[$_nf['idnfitem']][$_nf['idcontaitem']]['idprodservcontaitem'] = $_nf['idprodservcontaitem'];
		}

		return $arrNf;
	}

	public static function buscarContaItem($idcontaitens)
	{
		$results = SQL::ini(ContaItemQuery::buscarContaItem(), [
			"idcontaitens" => $idcontaitens
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarContaItemAtivoShare($tiponf = NULL)
	{
		// nf tipo outros so deve lidatar somarelatorio não por causa do despesasitem e finextrato
		if ($tiponf) {
			if ($tiponf == 'O') {
				$somarRelatorio = " AND  c.somarelatorio ='N' ";
			} elseif ($tiponf != 'C') {
				$somarRelatorio = " AND  c.somarelatorio ='Y' ";
			}
		}
		$compartilharCbUserContaitem = share::otipo('cb::usr')::compartilharCbUserContaitem("c.idcontaitem");
		$contaItemPorCbUserIdempresa = $compartilharCbUserContaitem ? $compartilharCbUserContaitem : 'AND c.idempresa = ' . cb::idempresa();
		$results = SQL::ini(ContaItemQuery::buscarContaItemAtivoShare(), [
			"idempresa" => getidempresa('c.idempresa', 'contaitem'),
			"compartilharCbUserContaitem" => $contaItemPorCbUserIdempresa,
			"somarRelatorio" => $somarRelatorio
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarContaItemPorIdempresa($tiponf = NULL, $idempresa)
	{
		// nf tipo outros so deve lidatar somarelatorio não por causa do despesasitem e finextrato
		if ($tiponf) {
			if ($tiponf == 'O') {
				$somarRelatorio = " AND  c.somarelatorio ='N' ";
			} elseif ($tiponf != 'C') {
				$somarRelatorio = " AND  c.somarelatorio ='Y' ";
			}
		}
		// $compartilharCbUserContaitem = share::otipo('custom')::okey($idempresa)::compartilharCbUserContaitem("c.idcontaitem");
		$contaItemPorCbUserIdempresa =  'AND c.idempresa = ' . $idempresa;
		$results = SQL::ini(ContaItemQuery::buscarContaItemAtivoShare(), [
			"idempresa" => getidempresa('c.idempresa', 'contaitem'),
			"compartilharCbUserContaitem" => $contaItemPorCbUserIdempresa,
			"somarRelatorio" => $somarRelatorio
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return $results->data;
		}
	}

	public static function buscarHistoricoAlteracao($idobjeto, $campo)
	{
		if (is_array($campo)) {
			$campo = "AND campo in ('" . implode("','", $campo) . "')";
		}

		$results = SQL::ini(ModuloHistoricoQuery::buscarHistoricoAlteracao(), [
			"idobjeto" => $idobjeto,
			"tipoobjeto" => "contaitem",
			"campo" => $campo
		])::exec();

		if ($results->error()) {
			parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
			return "";
		} else {
			return (count($results->data) > 0) ? $results->data : "";
		}
	}
	// ----- FUNÇÕES -----
}
