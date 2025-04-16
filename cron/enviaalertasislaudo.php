<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$ler=true;//Gerar logs de erro
$rid="\n".rand()." - Sislaudo: ";
if($ler)error_log($rid.basename(__FILE__, '.php'));

session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
	$prefu="stdin_";
	include_once("/var/www/carbon8/inc/php/functions.php");
	include_once("/var/www/carbon8/inc/php/cmd.php");
}else{//se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
	include_once("../inc/php/cmd.php");
}
include_once(__DIR__."/controllers/enviaalertasislaudo_controller.php");

$grupo = rstr(8);

re::dis()->hMSet('cron:enviaalertasislaudo',['inicio' => Date('d/m/Y H:i:s')]);


EnviaAlertaSislaudoController::inserirLog(1,$grupo,'cron','envialertasislaudo','status','INICIO','SUCESSO','','now()',"DATE_FORMAT(NOW(), '%Y-%m-%d')");



echo "Início: ".date("d/m/Y H:i:s", time()).'<br>'; 
$sessionid = session_id();//PEGA A SESSÃO 

// Altera o status da configuracao dos alertas ABERTO para PROCESSANDO e reserva para a sessão
$retc = EnviaAlertaSislaudoController::atualizarAlertasParaProcessando($sessionid);

if(!$retc){
	//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
	$strerr="Erro ao alterar ALERTA para consulta: \n<br>".mysql_error()."\n<br>".$sqlc;
	echo($strerr);
	if($ler)error_log($rid.$strerr);
	return false;
}else{
	if($ler)error_log($rid."update immsgconf ok");
}

//busca  as configurações para envio da mensagem
$res = EnviaAlertaSislaudoController::buscarConfiguracoesDoEnvioDeMensagem($sessionid);
echo '<pre>';
echo $res->sql();
echo '</pre>';
echo '<br /><br />';	
	//die($sql);	
//  $res=d::b()->query($sql);

if(empty($res->data)){
	$strerr="A Consulta na immsgconf falhou : " . $res->errorMessage() . "<p>SQL: $sql";
	if($ler)error_log($rid.$strerr);
}else{
	if($ler)error_log($rid."Consulta immsgconf ok: ". $res->numRows() ." registros");
}
 
foreach($res->data as $k => $row){
	//busca os filtros para seleção
	// GVT - 13/02/2020 - Alterado o select para trazer valores nulos quando estão acompanhados do sinal 'is' ou 'is not'
	$resf=EnviaAlertaSislaudoController::buscarCamposDaConfiguracoesDoEnvioDeMensagem($row["idimmsgconf"]);
	echo '<br /><br />';
	$qtdf=count($resf);
	$and=" ";
	if($qtdf>0){
		$clausula="";
		foreach($resf as $k1 => $rowf){
			// GVT - 13/02/2020 - Alterado a ci=ondição para permitir valores nulos quando estão acompanhados do sinal 'is' ou 'is not'
			if(($rowf["valor"]!='null' and $rowf["valor"]!=' ' and $rowf["valor"]!='') or ($rowf["valor"]=='null' and ($rowf["sinal"]=='is' or $rowf["sinal"]=='!='))){
				if($rowf["valor"]=='now'){
					if(!empty($rowf["nowdias"])){
 						$rowf["nowdias"];
						$date=date("Y-m-d");
						$valor=date('Y-m-d', strtotime($date. ' - '.$rowf["nowdias"].' day'));
// '1';
					}else{
						$valor=date("Y-m-d"); 
// '2';
					}
				}else if($rowf["valor"]=='mais'){
					$date=date("Y-m-d");
					$valor=date('Y-m-d', strtotime($date. ' + '.$rowf["nowdias"].' day'));
// '3';
				}else if($rowf["valor"]=='menos'){
					$date=date("Y-m-d");
					$valor=date('Y-m-d', strtotime($date. ' - '.$rowf["nowdias"].' day'));
// '3';
				}else{
					$valor=$rowf["valor"];
// '3';
				}
				// $valor;

				if($rowf['sinal']=='in'){
					$strvalor = str_replace(",","','",$valor);
					$clausula.= $and." a.".$rowf["col"]." in ('".$strvalor."')";
				}elseif($rowf['sinal']=='like'){
					$clausula.= $and." a.".$rowf["col"]." like ('%".$valor."%')";
				}elseif($rowf['sinal']=='is'){
					$clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." ".$valor."";
				}elseif($rowf['sinal']=='sql'){
					$clausula.= $and." a.".$rowf["col"]." ".$valor."";									   
				}else{
					$clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." '".$valor."'";
				}
				$and=" and ";
			}else{
				if($ler)error_log($rid.'rowf[valor] não previsto');
			}
		}//while

		echo '<br /><br />';
		// busca na tabela configurada os ids
		$resx=EnviaAlertaSislaudoController::executaConfiguracaoDoAlerta($row['col'],$row["tab"],$clausula,$row['apartirde'],$row['modulo'],$row['idimmsgconf']);

		if($resx->error()){
			$strerr="A Consulta na tabela de origem dos dados falhou : " . $resx->errorMessage() . "<p>SQL: ".$resx->sql();
			if($ler)error_log($rid.$strerr);
		}else{
			if($ler)error_log($rid."immsgconflog ok: ".count($resx->data)." registros");
		}

		foreach($resx->data as $k2 => $rowx){ 
		$idevento = '';
			

			/****************************************************************
			 *			Verifica se o tipo de alerta é de ASSINATURA
			 ****************************************************************/
			
			/****************************************************************
			 *			Verifica os destinatários 
			 ****************************************************************/
			$resc=EnviaAlertaSislaudoController::buscaDestinatariosDoAlerta($row['idimmsgconf'],$row['tipo'],$row['assinar'],$rowx['idpk'],$row['modulo']);
			echo $resc->sql();
			echo '<br/><br/>';

			if($resc->error()){
				$strerr="A busca dos contatos falhou : " . $resc->errorMessage()."\n".$sqlc;
				if($ler)error_log($rid.$strerr);
			}else{
				if($ler)error_log($rid."immsgconfdest ok: ".($resc->numRows())." registros");
			}
			
			
			if ($resc->numRows() > 0){
				// insere um log
				$rlog=EnviaAlertaSislaudoController::insereLogEnviando(1,$row['idimmsgconf'],$rowx['idpk'],$row['modulo'],$prefu);
				echo $rlog->sql();

				echo '<br /><br />';

				if($rlog->error()){
					$strerr="Erro ao gerar log [immsgconflog]: ".$rlog->errorMessage()."\n".$sl;
					if($ler)error_log($rid.$strerr);
				}

				//recupera o ultimo ID inserido
				$idimmsgconflog = $rlog->lastInsertId();
				if(empty($idimmsgconflog)){
					//Erro: valor vazio ao recuperar insert_id
					if($ler)error_log($rid."VALOR_VAZIO_INSERTID_LOG");
					return '{"code":"VALOR_VAZIO_INSERTID_LOG"}';
				}

				/****************************************************************
				 *			Cria corpo da mensagem: Insere na msgbody
				 ****************************************************************/
				//$link="<a href=\"?_modulo=".$row['modulo']."&_acao=u&".$row['col']."=".$rowx['idpk']."\" target=\"_blank\">".$rowx['idpk']."</a>";

				$titulocurto 	= addslashes($row['titulocurto']).' '.$rowx['idpk'];
				$mensagem		= addslashes($row['mensagem']);
				$jsonresultado  = ('{"tags": [], "pessoas": [], "documentos": [], "tagsValores": [], "personalizados": [], "pessoasValores": [], "documentosValores": []}');
	 
				$resv=EnviaAlertaSislaudoController::buscarEventoAlerta($row['idimmsgconf'],$row['ideventotipo'],$row['modulo'],$rowx['idpk']); 
				echo $resv->sql();
				echo '<br /><br />';

				foreach($resv->data as $k4 => $rows){
					$idevento = $rows['idevento'];
					$idfluxostatus = $rows['idfluxostatus'];
					EnviaAlertaSislaudoController::atualizarStatusDoEvento($idevento,$status);
					if ($row['tipo']!="A" and $row['assinar']!="Y"){
						EnviaAlertaSislaudoController::atualizarParaNaoVisualizadoPorIdEvento($idevento,$status);
					}
				}
				
				if (empty($idevento)){
					if ($row['modulo'] == 'formalizacao') {
						$idmodulo = traduzid('formalizacao', 'idlote', 'idformalizacao', $rowx['idpk']);
					}else {
						$idmodulo = $rowx['idpk'];
					}
					// echo	$sm = "INSERT INTO evento 
					// 					(	ideventotipo, idempresa, idpessoa, modulo, idmodulo, evento, 
					// 						idfluxostatus, prazo,  descricao, inicio, iniciohms, fim, fimhms,
					// 						criadopor, criadoem, alteradopor, alteradoem
					// 					)
					// 				VALUES 		
					// 					(	'".$row['ideventotipo']."', '".$row['idempresa']."',1029,'".$row['modulo']."','".$idmodulo."','".$titulocurto."', 
					// 						'".$row['idfluxostatus']."','".$row['prazo']."', '".$mensagem."',  DATE_FORMAT(now(),'%Y-%m-%d'), DATE_FORMAT(now(),'%h:%m:%s'), DATE_FORMAT(now(),'%Y-%m-%d'), DATE_FORMAT(now(),'%h:%m:%s'),
					// 						'immsgconf',DATE_FORMAT(now(),'%Y-%m-%d %h:%m:%s'),'immsgconf',DATE_FORMAT(now(),'%Y-%m-%d %h:%m:%s')
					// 					)";
					$rmb = EnviaAlertaSislaudoController::criarEventoAlerta($row['ideventotipo'],$row['idempresa'],1029,$row['modulo'],$idmodulo,$titulocurto,$row['idfluxostatus'],$row['prazo'],$mensagem);
					echo '<br /><br />';
					
					if($rmb->error()){
						$strerr="Erro ao criar evento: ".$rmb->errorMessage()."\n".$sm;
						if($ler)error_log($rid.$strerr);
					}

					//recupera o ultimo ID inserido
					$idevento = $rmb->lastInsertId();
				
				}
				if(empty($idevento)){
					//Erro: valor vazio ao recuperar insert_id
					if($ler)error_log($rid."VALOR_VAZIO_INSERTID_INS");
					return '{"code":"VALOR_VAZIO_INSERTID"}'; 
				}
				
				$link="?_modulo=".$row['modulo']."&_acao=u&".$row['col']."=".$rowx['idpk'];
				$nome=$row['rotulomenu'].": ".$row['col']."=".$rowx['idpk'];

				// atualiza o log para sucesso
				$su="UPDATE immsgconflog set status='SUCESSO', idimmsgbody=".$idevento." where idimmsgconflog=".$idimmsgconflog;
				EnviaAlertaSislaudoController::atualizarLogParaSucesso($idevento,$idimmsgconflog);

				if($rlog->error()){
					$strerr="Erro ao atualizar log [immsgconflog] : ".$rlog->errorMessage()."\n".$su;
					if($ler)error_log($rid.$strerr);
				}
				
			
			$c = 0;
			$arrayFuncionario = array();
			$cmdFluxoStatusPessoa = new cmd();
			$cmdFluxoStatusPessoa->setPostHeaders = false;

			foreach($resc->data as $k3 => $rowc){
			
				/****************************************************************
				* Insere a mensagem na tabela de chat com o id da mensagem relacionada
				****************************************************************/

			/*	$rii = $cmdFluxoStatusPessoa->save([
					'_alerta_i_fluxostatuspessoa_idmodulo' 				=> $idevento,
					'_alerta_i_fluxostatuspessoa_modulo' 				=> 'evento',
					'_alerta_i_fluxostatuspessoa_idpessoa' 				=> 1029,
					'_alerta_i_fluxostatuspessoa_idempresa' 			=> $row['idempresa'],
					'_alerta_i_fluxostatuspessoa_idobjeto' 				=> $rowc['idpessoa'],
					'_alerta_i_fluxostatuspessoa_tipoobjeto' 			=> 'pessoa',
					'_alerta_i_fluxostatuspessoa_idfluxostatus' 		=> $row['idfluxostatus'],
					'_alerta_i_fluxostatuspessoa_oculto' 				=> 0,
					'_alerta_i_fluxostatuspessoa_inseridomanualmente' 	=> 'N',
				]);*/

				$rii= EnviaAlertaSislaudoController::inserirPessoasNoEventoAlerta($idevento,$row['idempresa'],$rowc['idpessoa'],$row['idfluxostatus']);
				echo $rii->sql();
				echo '<br /><br />';

				if($rii->error()){
					$strerr="Erro ao inserir msg: ".mysqli_error(d::b())."\n".$si;
					if($ler)error_log($rid.$strerr);
				}

				//recupera o ultimo ID inserido
				$idmsgins = $rii->lastInsertId();

				if(empty($idmsgins)){
					if($ler)error_log($rid."VALOR_VAZIO_INSERTID_MSGINS");
				}

				if($row['tipo']=="A" or $row['assinar']=="Y"){
				    /****************************************************************
				    * Insere uma assinatura pedente
				    	MAF: tabela com nome errado: caRRimbo @todo: alterar nome
				    ****************************************************************/
					$resv = EnviaAlertaSislaudoController::buscarAssinaturaDoEventoAlerta($rowx['idpk'],$row['modulo'],$rowc['idpessoa']);
					
					if ($resv->numRows() == 0){

						$rcar = EnviaAlertaSislaudoController::inserirAssinatura(1,$rowc['idpessoa'],$rowx['idpk'],$row['modulo'],null,null,'PENDENTE','immsgconf','immsgconf');

						if($rcar->error()){
									$strerr="Erro ao inserir msg: ".$rcar->error()."\n".$sa;
									if($ler)error_log($rid.$strerr);
						}
						//REMOVE O OCULTAR DO EVENTO E ALTERA O STATUS PARA INICIAL PARA O USUÁRIO QUE NÃO ASSINOU

						EnviaAlertaSislaudoController::removerOcultarDoEvento($row['ideventotipo'],$idevento,$rowc['idpessoa']);
						
							
					}
				}//Maf: Neste ponto poderia-se colocar um else com log
			$c++;
			}// while($rowc=mysqli_fetch_assoc($resc))
		
		}

			$val = json_encode($arrayFuncionario);		

		}// while($rowx=mysqli_fetch_assoc($resx))
	}else{
		if($ler)error_log($rid."immsgconffiltros: 0");
	}// if($qtdf>0)
}//while($row=mysqli_fetch_assoc($res))
            
// Altera o status da configuracao dos alertar de PROCESSANDO para ABERTO e reserva para a sessão
$retc = EnviaAlertaSislaudoController::atualizarAlertasParaAberto();

if($retc->error()){
       $strerr="Erro ao voltar status do  ALERTA para ABERTO: \n<br>".$retc->errorMessage()."\n<br>".$sqlc;
       echo $strerr;
       if($ler)error_log($rid.$strerr);
}

echo "Fim: ".date("d/m/Y H:i:s", time()).'<br>'; 

re::dis()->hMSet('cron:enviaalertasislaudo',['inicio' => Date('d/m/Y H:i:s')]);

EnviaAlertaSislaudoController::inserirLog(1,$grupo,'cron','envialertasislaudo','status','FIM','SUCESSO','','now()',"DATE_FORMAT(NOW(), '%Y-%m-%d')");

?>