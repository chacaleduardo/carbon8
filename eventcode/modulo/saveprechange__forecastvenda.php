<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

retarraytabdef('forecastcompra');

if ($_POST['_statusant_'] != 'APROVADO' AND $_SESSION["arrpostbuffer"]["1"]['u']["forecastvenda"]["status"] == 'APROVADO')
{
    $_SESSION["arrpostbuffer"]["1"]['u']["forecastvenda"]["versao"] = $_SESSION["arrpostbuffer"]["1"]['u']["forecastvenda"]["versao"] + 1;

    $idforecastvenda = $_SESSION["arrpostbuffer"]["1"]['u']["forecastvenda"]["idforecastvenda"];

    $_listarForecastCompras =  ProdServController::BuscarForecastComprasLigadosForecastVenda($idforecastvenda);

    
    if($_listarForecastCompras['qtdLinhas'] > 0){

        $i = 0;

        foreach($_listarForecastCompras['dados'] as $Fcompras){

            $exercicio = $Fcompras['exercicio'];
            $_1_u_forecastvenda_versao=$_POST['_1_u_forecastvenda_versao']+1;
            if(empty($_1_u_forecastvenda_versao)){$_1_u_forecastvenda_versao=1;}
            $idforecastcompra = $Fcompras['idforecastcompra'];

            $_SESSION["arrpostbuffer"]["xx".$i.""]['u']["forecastcompra"]["idforecastcompra"] = $idforecastcompra;
			$_SESSION["arrpostbuffer"]["xx".$i.""]['u']["forecastcompra"]["versao"] = $processos["versao"] + 1;
            
			$forecastcomprasql = PlanejamentoProdServController::buscaCategoria($_GET['_idempresa'], '', $exercicio); 

			$arrayObjetoJson = [
                "idempresa" => cb::idempresa(),
                "idobjeto" => $idforecastcompra,
                "tipoobjeto" => 'forecastcompra',
                "jobjeto" => base64_encode(serialize($forecastcomprasql)),
                "versaoobjeto" => $Fcompras["versao"] + 1,
                "criadopor" => $_SESSION['SESSAO']['USUARIO'],
                "criadoem" => 'now()',
                "alteradopor" => $_SESSION["SESSAO"]["USUARIO"],
                "alteradoem" => 'now()'
            ];
            ProdServController::inserirObjetoJson($arrayObjetoJson);

			$arrayAuditoria = [
				"idempresa" => cb::idempresa(),
				"linha" => 1,
				"acao" => 'i',
				"objeto" => 'objetojson',
				"idobjeto" => $idforecastcompra,
				"coluna" => 'jobjeto',
				"valor" => base64_encode(serialize($forecastcomprasql)),
				"criadopor" => $_SESSION["SESSAO"]["USUARIO"],
				"tela" => $_SERVER["HTTP_REFERER"]
			];
			ProdServController::inserirAuditoria($arrayAuditoria);

			$forecastcomprasql = null;
			$i++;
		}
	}
}

