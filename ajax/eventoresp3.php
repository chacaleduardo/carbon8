<?

require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

require_once("../model/fluxostatuspessoa.php");

// QUERYS
require_once(__DIR__."/../form/querys/_iquery.php");
require_once(__DIR__."/../form/querys/evento_query.php");
require_once(__DIR__."/../form/querys/fluxostatuspessoa_query.php");
require_once(__DIR__."/../form/querys/fluxostatus_query.php");
require_once(__DIR__."/../form/querys/modulocom_query.php");

$obs        	= filter_input(INPUT_GET, "vobs");
$opcao      	= filter_input(INPUT_GET, "vopcao");
$assina      	= filter_input(INPUT_GET, "vassina");
$status     	= filter_input(INPUT_GET, "vstatus");
$idevento   	= filter_input(INPUT_GET, "videvento");
$ideventostatus = filter_input(INPUT_GET, "videventostatus");
$idfluxostatuspessoa = filter_input(INPUT_GET, "vidfluxostatuspessoa");
$idmodulocom   	= filter_input(INPUT_GET, "vidmodulocom");
$versao   		= filter_input(INPUT_GET, "vversao");
$datahora   	= filter_input(INPUT_GET, "vdatahora");
$prazo   		= filter_input(INPUT_GET, "vprazo");
$duracao	  	= filter_input(INPUT_GET, "duracaohms");
$diainteiro   	= filter_input(INPUT_GET, "diainteiro");
$idtag   		= filter_input(INPUT_GET, "idtag");
$inicio   		= filter_input(INPUT_GET, "vinicio");
$iniciohms   	= filter_input(INPUT_GET, "viniciohms");
$modulo   		= filter_input(INPUT_GET, "vmodulo");
$idmodulo   	= filter_input(INPUT_GET, "vidmodulo");

if (!empty($status) && $opcao == 'change' && !empty($idfluxostatuspessoa)) {

		// $su="UPDATE fluxostatuspessoa SET idstatus = '".$status."'
		// 	  WHERE idfluxostatuspessoa = ".$idfluxostatuspessoa;
		// $ru=d::b()->query($su);
		$atualizandoIdStatusPorIdFluxostatusPessoa = SQL::ini(FluxostatusPessoaQuery::atualizarIdStatusPorIdFluxostatusPessoa(), [
			'idstatus' => $status,
			'idfluxostatuspessoa' => $idfluxostatuspessoa
		])::exec();
			
		// coloca a mensagem como não visualizada novamente
		// $sql="SELECT novamensagem FROM fluxostatus WHERE idfluxostatus =".$status;
		// $rec=d::b()->query($sql) or die("saveposchange: ao verificar nessecidade de enviar uma nova mensagem:" . mysqli_error(d::b()) . "");
		// $roc=mysqli_fetch_assoc($rec);
		// $roc['novamensagem'];

		$fluxoStatus = SQL::ini(FluxoStatusQuery::buscarPorChavePrimaria(), [
			'pkval' => $status
		])::exec();

		if($fluxoStatus->data[0]['novamensagem']=="Y")
		{
			// $su="UPDATE fluxostatuspessoa r, fluxostatuspessoa r2  SET r2.visualizado = '0'
			// 		WHERE r.idmodulo = ".$idfluxostatuspessoa." 
			// 		AND r2.idevento = r.idevento 
			// 		AND r2.tipoobjeto = 'pessoa'
			// 		AND r2.idmodulo != ".$idfluxostatuspessoa." AND r2.modulo = 'evento'";

			// $ru=d::b()->query($su);

			$definindoEventoComoNaoVisualizado = SQL::ini(FluxostatuspessoaQuery::definirEventoComoNaoVisualizado(), [
				'idfluxostatuspessoa' => $idfluxostatuspessoa
			])::exec();
		}
		
		// $sqlx="SELECT s.rotulo,s.idstatus,e.idevento
		// 		 FROM evento e
		// 	   	 JOIN fluxostatuspessoa r on e.idevento = r.idevento
		// 	   	 JOIN fluxo ms ON e.ideventotipo = ms.idobjeto AND ms.modulo = 'evento'
		// 		 JOIN fluxostatus es ON es.idstatus = r.idstatus and es.idfluxo = ms.idfluxo
		// 	   	 JOIN "._DBCARBON."._status s ON s.idstatus = es.idstatus
		// 	    WHERE r.idfluxostatuspessoa = ".$idfluxostatuspessoa." 
		// 	   	  AND r.tipoobjeto ='pessoa' 
		// 	 ORDER BY es.ordem  desc limit 1";
		// $recx=d::b()->query($sqlx) or die("saveposchange: ao verificar nessecidade de enviar uma nova mensagem:" . mysqli_error(d::b()) . "");
		// $rocx=mysqli_fetch_assoc($recx);

		$buscandoStatusDaPessoaDeUmEvento = SQL::ini(EventoQuery::buscarStatusDaPessoaDeUmEvento(), [
			'idfluxostatuspessoa' => $idfluxostatuspessoa
		])::exec();
		
		if(!empty($buscandoStatusDaPessoaDeUmEvento->data[0]['idevento']) and !empty($buscandoStatusDaPessoaDeUmEvento->data[0]['idstatus']))
		{
			// echo $su = "update evento 
			// 			set idstatus = '".$buscandoStatusDaPessoaDeUmEvento->data[0]['idstatus']."'
			// 			where idevento = ".$buscandoStatusDaPessoaDeUmEvento->data[0]['idevento'];
			// $ru=d::b()->query($su);

			$atualizandoIdStatusPorIdEvento = SQL::ini(EventoQuery::atualizarIdStatusPorIdEvento(), [
				'idstatus' => $buscandoStatusDaPessoaDeUmEvento->data[0]['idstatus'],
				'idevento' => $buscandoStatusDaPessoaDeUmEvento->data[0]['idevento']
			]);
		}
		
}


if (!empty($obs) && $opcao == 'add') 
{	
	// $sql = "INSERT INTO modulocom	 
	// 			(idmodulocom, idempresa, idmodulo, modulo, descricao,status, criadopor, criadoem, alteradopor, alteradoem)
	// 			VALUES
	// 				(null, ".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idevento.", 'evento', '".htmlentities($obs)."','ATIVO','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now());";
	// $res = d::b()->query($sql) or die("Erro ao inserir comentário no histórico do evento: ".mysqli_error(d::b()));

	$dadosModulocom = [
		'idempresa' => $_SESSION["SESSAO"]["IDEMPRESA"],
		'idmodulo' => $idevento,
		'modulo' => 'evento',
		'descricao' => htmlentities($obs),
		'status' => 'ATIVO',
		'criadopor' => $_SESSION["SESSAO"]["USUARIO"],
		'criadoem' => 'now()',
		'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
		'alteradoem' => 'now()'
	];

	$inserindoModulocom = SQL::ini(ModulocomQuery::inserir(), $dadosModulocom)::exec();
	 
	//  $sql = "UPDATE fluxostatuspessoa
	// 			SET visualizado = 0 
	// 		  WHERE idmodulo = '".$idevento."' 
	// 		  	AND modulo = 'evento'
	// 			AND NOT idobjeto = '".$_SESSION["SESSAO"]["IDPESSOA"]."' 
	// 			and tipoobjeto = 'pessoa'";
					
	//  $res = d::b()->query($sql) or die("Erro ao setar não visualizado aos participantes após inserção de comentário: ".mysqli_error(d::b()));
	 
	 $atualizandoParaNaoVisualizadoPorIdEvento = SQL::ini(FluxostatusPessoaQuery::atualizarParaNaoVisualizadoPorIdEvento(), [
		'idevento' => $idevento,
		'idpessoa' => $_SESSION["SESSAO"]["IDPESSOA"]
	 ])::exec();

	 if (!empty($prazo)){
		//  $sql = "update evento 
		// 		set prazo = '".$prazo."', alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."',  alteradoem = now() 
		// 		where idevento = '".$idevento."';";
					
		// $res = d::b()->query($sql) or die("Erro ao atualizar prazo do evento: ".mysqli_error(d::b()));

		$atualizandoPrazoPorIdEvento = SQL::ini(EventoQuery::atualizarPrazoPorIdEvento(), [
			'idevento' => $idevento,
			'prazo' => $prazo,
			'alteradopor' => $_SESSION["SESSAO"]["USUARIO"]
		])::exec();
	 }
	 
	  if (!empty($inicio))
	  {
		  $inInicio = validadatetime($inicio.' '.$iniciohms); 

        if($diainteiro=='on'){  
            $_fimhms='23:59:00'; 
           
        }elseif(!empty($duracao)){
             $arrfim = explode(":",$duracao);    
            // Create a new \DateTime instance
            if (strlen($iniciohms) == 5) {
                $date = DateTime::createFromFormat('H:i', $iniciohms);   
            }else{
                $date = DateTime::createFromFormat('H:i:s', $iniciohms);   
            }
            // Modify the date
            $date->modify('+'.$arrfim['0'].' hours');
            // Modify the date
            $date->modify('+'.$arrfim['1'].' minutes');
            // Output
			$_fim= $date->format('Y-m-d'); 
            $_fimhms= $date->format('H:i:s'); 
        }else{
            $_fimhms='23:59:00'; 
        }

        /*
        $dateF = new DateTime($fim.' '.$_fimhms);
        $inFim=$dateF->format('Y-m-d H:i:s');
        */
        $inFim = validadatetime($_fim.' '.$_fimhms); 
		
		
		//  $sql = "update evento 
		//  		set inicio = '".$inicio."', iniciohms = '".$iniciohms."', fim = '".$_fim."', fimhms = '".$_fimhms."',  alteradopor = '".$_SESSION["SESSAO"]["USUARIO"]."',  alteradoem = now() 
		// 		where idevento = '".$idevento."';";
		
		// $res = d::b()->query($sql) or die("Erro ao atualizar prazo do evento: ".mysqli_error(d::b()));

		$atualizandoInicioEFim = SQL::ini(EventoQuery::atualizarInicioEFim(), [
			'inicio' => $inicio,
			'iniciohms' => $iniciohms,
			'fim' => $_fim,
			'fimhms' => $_fimhms,
			'alteradopor' => $_SESSION["SESSAO"]["USUARIO"],
			'idevento' => $idevento
		])::exec();
		
		$di = explode('-',$inicio);
		$df = explode('-',$inicio);
		$ii = explode(':',$iniciohms);
		$if = explode(':',$_fimhms);
		
		$current = strtotime(date("Y-m-d"));
		 $date    = strtotime($inicio);

		 $datediff = $date - $current;
		 $difference = floor($datediff/(60*60*24));
		 if($difference==0)
		 {
			$dataTarefa = 'HOJE '.substr($iniciohms,0,-3);
			$coricone = '#0f8041;background:#0f8041;color:#fff;';
		 }
		 else if($difference > 1)
		 {
			$dataTarefa = substr(dmahms($inicio.' '.$iniciohms),0,-3);
			$coricone = '#999;color:#999;';
		 }
		 else if($difference > 0)
		 {
			$dataTarefa = 'AMANHÃ '.substr($iniciohms,0,-3);
			$coricone = '#999;color:#999;';
		 }
		 else if($difference < -1)
		 {
			$dataTarefa = substr(dmahms($inicio.' '.$iniciohms),0,-3);
			$coricone = '#999;background:#999;color:#666;';
		 }
		 else
		 {
			$dataTarefa = 'ONTEM '.substr($iniciohms,0,-3);
			$coricone = '#999;background:#999;color:#666;';
		 } 
		 
		 
		echo '<i class="fa fa-calendar" style="font-size: 14px; line-height: 11px; margin-right: 2px; padding: 2px; "></i> '.$dataTarefa;
											
	 }
}

if (!empty($idevento) && $opcao == 'load') 
{
	// $sql = "SELECT idmodulocom, 
	// 				ec.idempresa, 
	// 				e.idevento, 
	// 				ec.descricao, 
	// 				ec.criadopor, 
	// 				ec.criadoem, 
	// 				ec.alteradopor, 
	// 				ec.alteradoem, 
	// 				nomecurto, 
	// 				es.rotuloresp AS STATUS,
	// 				et.anonimo, 
	// 				if(e.idpessoa = p.idpessoa, 'Y', 'N') AS dono
	// 			FROM modulocom ec JOIN evento e ON e.idevento = ec.idmodulo AND ec.modulo = 'evento'
	// 			JOIN fluxostatus fs ON fs.idfluxostatus = e.idfluxostatus
	// 	   LEFT JOIN "._DBCARBON."._status es ON es.idstatus = fs.idstatus
	// 			JOIN pessoa p ON p.usuario = ec.criadopor
	// 			JOIN eventotipo et ON et.ideventotipo = e.ideventotipo
	// 			WHERE ec.idmodulo = '".$idevento."'  AND ec.modulo = 'evento'
	// 			ORDER BY ec.criadoem DESC";

	// $res = d::b()->query($sql) or die("Erro carregar listaStatusEventos eventoresp3: ".mysqli_error(d::b()));

	$modulocom = SQL::ini(ModulocomQuery::buscarStatusDosEventos(), [
		'idevento' => $idevento
	])::exec();

	foreach($modulocom->data as $modulo)
	{
		if ($modulo["anonimo"] == 'Y' && $modulo["dono"] == 'Y')
		{
			$modulo["nomecurto"] = '<i><b>ANÔNIMO</b></i>';
		}
						

    	$return_arr[] = array(
			"idmodulocom" 	=> $modulo['idmodulocom'],
			"idempresa" 	=> $modulo['idempresa'],
			"idevento" 		=> $modulo['idevento'],
			"descricao" 	=> $modulo['descricao'],
			"status" 		=> $modulo['status'],
			"criadopor" 	=> $modulo['criadopor'],
			"criadoem" 		=> $modulo['criadoem'],
			"alteradopor" 	=> $modulo['alteradopor'],
			"alteradoem" 	=> $modulo['alteradoem'],
			"nomecurto" 	=> $modulo['nomecurto']
		);
	}
	
	echo json_encode($return_arr);
}

?>