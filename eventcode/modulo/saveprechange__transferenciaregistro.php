<?
$arrpostbuffer = $_SESSION["arrpostbuffer"];
$qtdreg= count($arrpostbuffer);
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once("../model/evento.php");
//print_r($arrpostbuffer ); die;

if($qtdreg > 0){	

	while (list($key, $value) = each($arrpostbuffer)) 
	{				
		if($_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["idregistro"] && $_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["idunidade"]){
			//Valida se a ação é u para validar as amostras, pois no array vem o fluxo com i								
			$idunidade = $_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["idunidade"];
			//Salvar o Idregistro Provisório na tabela Amostra - Lidiane (17-06-2020)
			$idregistroprovisorio = $_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["idregistro"];
		
			if(empty($idunidade)){
				die("Não foi possivel identificar a Unidade para gerar o Registro!!!");
			}
		
			d::b()->query("START TRANSACTION;");
		
			$status = $_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["status"];
		
		
			$exercicio = $_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["exercicio"];
			
			//Função para Atualizar e Inserir o próximo registra
			$rowexercicio = geraIdregistro($_SESSION["SESSAO"]["IDEMPRESA"], $idunidade);
		
			//se o idnucleo vier vazio o valor do mesmo e informado como 0 (zero) para atender a questoes de relatorios(OUTROS) do site
			$_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["exercicio"] = $rowexercicio["exercicio"];
			$_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["idregistro"] = $rowexercicio["idregistro"];
			//Salvar o Idregistro Provisório na tabela Amostra - Lidiane (17-06-2020)
			$_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["idregistroprovisorio"] = $idregistroprovisorio;

			$_SESSION["post"]["_1_u_amostra_exercicio"] = $rowexercicio["exercicio"];
			$_SESSION["post"]["_1_u_amostra_idregistro"] = $rowexercicio["idregistro"];

			retarraytabdef('fluxostatushist');
			
			
			//Atualiza o Status do Resultado (tabela, pk, valorpk, $statustipo, $modulotipo, $_primaryhist, status)
			$rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["idamostra"], 'ABERTO', 'amostra', '', '');
			FluxoController::alterarStatus($rowFluxo['modulo'], 'idamostra', $_SESSION["arrpostbuffer"][$key]["u"]["amostra"]["idamostra"], $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], '', 'Y', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
		}	
	}

}else{
	die("Nenhum registro selecionado para TRANSFERÊNCIA.");
}


