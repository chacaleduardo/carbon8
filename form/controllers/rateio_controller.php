<?
require_once(__DIR__."/_controller.php");

// QUERYS
require_once(__DIR__."/../querys/_iquery.php");
require_once(__DIR__."/../querys/nfrateio_query.php");
require_once(__DIR__."/../querys/rateio_query.php");
require_once(__DIR__."/../querys/rateioitem_query.php");

//Controllers
require_once(__DIR__."/../controllers/rateioitemdest_controller.php");

class RateioController extends Controller
{
	// ----- FUNÇÕES -----
	public static function buscarNfRateioPorTipoObjetoRateioEIdObjetoRateio($tipoobjetorateio, $idobjetorateio)
	{
		$results = SQL::ini(NfRateioQuery::buscarNfRateioPorTipoObjetoRateioEIdObjetoRateio(), [
            "tipoobjetorateio" => $tipoobjetorateio,
            "idobjetorateio" => $idobjetorateio
        ])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->numRows();
        }
	}

	public static function atualizarValorNfRateio($tipoobjetorateio, $idobjetorateio, $valor)
	{
		$results = SQL::ini(NfRateioQuery::buscarNfRateioPorTipoObjetoRateioEIdObjetoRateio(), [
            "tipoobjetorateio" => $tipoobjetorateio,
            "idobjetorateio" => $idobjetorateio,
			"valor" => $valor
        ])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
		}
	}

	public static function inserirRateioRateioItemRateioItemDest($idnfitem, $arrInsrateio, $_idempresa)
	{
		$rateio = self::buscarNfitemRateio($idnfitem);
		/*
		if(empty($rateio['idrateio']))
		{
			$arrayRateio = [
				'idempresa' => $_idempresa,
				'idobjeto' => $rateio['idnf'],
				'tipoobjeto' => 'nf',
				'criadopor' => $_SESSION['SESSAO']['USUARIO'],
				'criadoem' => 'NOW()',
				'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
				'alteradoem' => 'NOW()'
			];
			$_idrateio = self::inserirRateio($arrayRateio);
			
		}else{
			$_idrateio = $rateio['idrateio'];
		}
*/
		if(empty($rateio['idrateioitem']))
		{
			$arrayRateio = [
				'idempresa' => $_idempresa,
				//'idrateio' => $_idrateio,
				'idobjeto' => $rateio['idnfitem'],
				'tipoobjeto' => 'nfitem',
				'criadopor' => $_SESSION['SESSAO']['USUARIO'],
				'criadoem' => 'NOW()',
				'alteradopor' => $_SESSION['SESSAO']['USUARIO'],
				'alteradoem' => 'NOW()'
			];
			$_idrateioitem = self::inserirRateioItem($arrayRateio);
		}else{
			$_idrateioitem = $rateio['idrateioitem'];
		}

		foreach($arrInsrateio as $index => $valor)
		{
			RateioItemDestController::inserirRateioItemDest($valor, $_idrateioitem, $_idempresa);
			//RateioItemDestController::inserirRateioItemDestOri($valor, $_idrateioitem, $_idempresa);
		}
	}

	public static function buscarNfitemRateio($idnfitem)
	{
		$results = SQL::ini(RateioQuery::buscarNfitemRateio(), [
            "idnfitem" => $idnfitem
        ])::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
            return "";
        }else{
            return $results->data[0];
        }
	}

	public static function inserirRateio($arrayRateio)
	{
        $results = SQL::ini(RateioQuery::inserir(), $arrayRateio)::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        } else{
            return $results->lastInsertId();
        }
    }

	public static function inserirRateioItem($arrayRateio)
	{
        $results = SQL::ini(RateioItemQuery::inserir(), $arrayRateio)::exec();

		if($results->error()){
            parent::error(__CLASS__, __FUNCTION__, $results->errorMessage());
        } else{
            return $results->lastInsertId();
        }
    }
	// ----- FUNÇÕES -----

	//----- AUTOCOMPLETE -----

	//----- AUTOCOMPLETE -----
}
?>