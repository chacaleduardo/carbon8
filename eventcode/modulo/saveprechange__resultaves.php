<? 
include_once '../../inc/php/validaacesso.php';

require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once("../model/evento.php");
 

//VERIFICA SE EXISTEM RESULTADOS POSITIVOS E SE O FLAG DE ALERTA ESTA MARCADO
if(($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["status"] == "CANCELADO")){
	$pattern = '/^cons/';
	foreach($_SESSION["arrpostbuffer"] as $k => $v){
		if (preg_match($pattern, $k)) {
			unset($_SESSION["arrpostbuffer"][$k]);
		}
		
	}
}

//VERIFICA SE EXISTEM RESULTADOS POSITIVOS E SE O FLAG DE ALERTA ESTA MARCADO
if(!empty($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["jsonresultado"])){

	$resultadoJSON = json_decode($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["jsonresultado"]);
	$contaResultadosPositivos = 0;
	
	foreach ($resultadoJSON -> INDIVIDUAL as $key => $inputsResultadosJSON) {
		foreach($inputsResultadosJSON as $key => $value) {
			if($value=='POSITIVO' && $key=='value'){
				$resultadoPositivo = true;
				$contaResultadosPositivos ++;
			}
		}
	}

	if($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["alerta"]!="Y" && $resultadoPositivo){
		cbSetCustomHeader('positivos',json_encode($contaResultadosPositivos));
	}
}







//die($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["descritivo"]);
//die($_SESSION['arrpostbuffer']['1'][$iu]['resultado']['positividade']);
$arrlanc = array();
$arrlanc = $_SESSION["arrpostbuffer"]["1"]["u"]["resultado"];

/*
if($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["status"] == 'FECHADO'){
	date_default_timezone_set('America/Sao_Paulo');
	$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["dataconclusao"] = date('d/m/Y H:i:s');
}*/

if($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["status"] == 'FECHADO'){
	// GVT - 10/01/2020 - Verifica se o resultado já possui uma data de conclusão,
	// caso não haja uma data, adiciona-lá na tabela,
	// caso haja data, não atualiza-lá
	$idresultado=$_POST["_1_u_resultado_idresultado"];
	$_res = InclusaoResultadoController::buscarDataconclusao($idresultado);
	if(($_res)){
		date_default_timezone_set('America/Sao_Paulo');
		$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["dataconclusao"] = date('d/m/Y H:i:s');
	}else{
		//echo "<!-- Não atualiza dataconclusao da tabela resultado -->";
	}
}

if($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["status"] == 'ABERTO'){
	$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["dataconclusao"] = NULL;
}

//Migrado de ajax/assinateste.php
if(!empty($_POST["_1_u_resultado_idresultado"]) 
		and !empty($_POST["acao"]) 
		and !empty($_POST["emailsec"]) 
		/*and !empty($_POST["frase"])*/
		and($_POST["acao"]=="assinar" or $_POST["acao"]=="retirar")
		){
	//print_r($_POST);die();
	
	$idresultado= $_POST["_1_u_resultado_idresultado"];
	$acao=$_POST["acao"];
	$frase=$_POST["frase"];
	$emailsec=$_POST["emailsec"];
	$modulo=$_POST["modulo"];	

	

		//verificar se usuario pode assinar o teste;	
		$resass = InclusaoResultadoController::verficaAssinateste($_SESSION["SESSAO"]["IDPESSOA"]);
	
		//se usuario puder assinar
		if($resass){
	
			//assinar
			if($acao=="assinar"){
				/* 
					feito para que o edison e marcio assinem resultados do CQ e do diagnostico autogena.
				*/
				if(!InclusaoResultadoController::verificaSeResultadoJaFoiAssinado($idresultado)){
					if(InclusaoResultadoController::verificarSeResultadoEInata($idresultado)){// e do cq ou do diag autogenas
						


						if($_SESSION["SESSAO"]["IDPESSOA"]==4138){//rui peretti
							$idpessoabkp=$_SESSION["SESSAO"]["IDPESSOA"];
							$usr_bkp=$_SESSION["SESSAO"]["USUARIO"];
							$_SESSION["SESSAO"]["IDPESSOA"]=5655;//jose renato
							$_SESSION["SESSAO"]["USUARIO"]='josebranco';
		
						}
						/*elseif($_SESSION["SESSAO"]["IDPESSOA"]==782){//Edison
							$idpessoabkp=$_SESSION["SESSAO"]["IDPESSOA"];
							$usr_bkp=$_SESSION["SESSAO"]["USUARIO"];
							$_SESSION["SESSAO"]["IDPESSOA"]=1098;// Hermes
							$_SESSION["SESSAO"]["USUARIO"]='hermesp';
		
						}*/
						else{
							$idpessoabkp=$_SESSION["SESSAO"]["IDPESSOA"];
							$usr_bkp=$_SESSION["SESSAO"]["USUARIO"];
							$_SESSION["SESSAO"]["IDPESSOA"]=$_SESSION["SESSAO"]["IDPESSOA"];
							$_SESSION["SESSAO"]["USUARIO"]=$_SESSION["SESSAO"]["USUARIO"];
						}
					}else{
						$idpessoabkp=$_SESSION["SESSAO"]["IDPESSOA"];
						$usr_bkp=$_SESSION["SESSAO"]["USUARIO"];
						$_SESSION["SESSAO"]["IDPESSOA"]=$_SESSION["SESSAO"]["IDPESSOA"];
						$_SESSION["SESSAO"]["USUARIO"]=$_SESSION["SESSAO"]["USUARIO"];
					}
					
		
					$res = mysql_query("START TRANSACTION");
					if(!$res){
						echo "ERRO AO ABRIR TRANSACAO PARA ASSINATURA DO RESULTADO: ".mysql_error()."";
						die();
					}

					
					//inserir a frase no resultado	
					$r = InclusaoResultadoController::atualizarInterfrasePorIdresultado($frase,$idresultado);
					//TRANSACAO 1
					$r = InclusaoResultadoController::inserirERetornarResultadoassinatura($_SESSION["SESSAO"]["IDEMPRESA"],$idresultado,$_SESSION["SESSAO"]["IDPESSOA"]);
		
					$qtdAssinatura = InclusaoResultadoController::buscarResultadoAssinaturaPorIdresultado($idresultado);
					if(!empty($r) && $qtdAssinatura == 0){
						$arrAss = assinaturaDigitalA1($r["idresultado"].$r["criadoem"].$r["idpessoa"],'');	
						InclusaoResultadoController::InserirAssinaturaResultado($_SESSION["SESSAO"]["IDEMPRESA"],$idresultado,$_SESSION["SESSAO"]["IDPESSOA"],$_SESSION["SESSAO"]["USUARIO"],$arrAss['assinatura']);                                                
					}
		
					//Congela o resultado
					congelaResultado($_POST["_1_u_resultado_idresultado"]);	
		
					//LTM (24/08/2021): Altera o status da Amostra para Assinar
					$idamostra = traduzid('resultado','idresultado','idamostra',$idresultado);	
					validarAssinaturaTesteTEA($idamostra);
		
					//TRANSACAO 2 
					// Retirei os alteradoem a pedido do andre o mesmo disse que no resultado ja tem a data da assinatura -hermes 02-12-2013
					$res1 = InclusaoResultadoController::atualizarResultadoParaAssinado($idresultado);
		
					$res2 = true;
					if(!empty(traduzid('resultado','idresultado','idsecretaria',$idresultado,false))){
						$res2 = InclusaoResultadoController::atualizarComunicacaoExtResultadosOficiais($idresultado);
					}
		
					//Atualiza o Status do Resultado (tabela, pk, valorpk, $statustipo, $modulotipo, $_primaryhist, status)
					$rowFluxo = FluxoController::getDadosResultadoAmostra('resultado', 'idresultado', $idresultado, 'ASSINADO', 'resultado', '', '');				
					FluxoController::alterarStatus($modulo, 'idresultado', $idresultado, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], '', 'Y', '', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
					
					//LTM (25/08/2021): O status Fechado é para as amostras diferente do TEA/TRA
					$idunidade = traduzid('amostra','idamostra','idunidade',$idamostra);	
		
					if($emailsec=='A'){
						$res2 = InclusaoResultadoController::atualizarEmailsecResultado($idresultado);
					}
		
					// feito para que o edison e marcio assiname resultados do cq e do diagnostico autogena
					if(!empty($idpessoabkp) and $_SESSION["SESSAO"]["IDPESSOA"]!=$idpessoabkp ){
						$_SESSION["SESSAO"]["IDPESSOA"]=$idpessoabkp;
						$_SESSION["SESSAO"]["USUARIO"]=$usr_bkp;
					}
					
					if(!$res or !$res1 or !$res2){
						//se alguma transacao falhar, efetuar rollback
						$res3 = mysql_query("ROLLBACK");
						if(!$res3){
							echo "ERRO AO EFETUAR [ROLLBACK] PARA A ALTERACAO: ".mysql_error();
							die();
						}
				}else{
						$res = mysql_query("COMMIT");
						if(!$res){
							echo "ERRO AO EFETUAR [COMMIT] PARA A ALTERACAO: ".mysql_error()."";
							die();
						}
		
						//Congela e versiona o resultado
						congelaResultado($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["idresultado"], true);
		
						echo "OK";
						die();
					}
					
				
				}else{
					echo "ERRO: Resultado já assinado!";
					die();
				}
			//retira assinatura
			}elseif($acao=="retirar"){
				$res = mysql_query("START TRANSACTION");
				if(!$res){
					echo "ERRO AO ABRIR TRANSACAO PARA ASSINATURA DO RESULTADO: ".mysql_error()."";
					die();
				}
	
				InclusaoResultadoController::deletarResultadoAssinaturaPorIdresultado($idresultado);
	
				$rowFluxo = FluxoController::getDadosResultadoAmostra('resultado', 'idresultado', $idresultado, 'FECHADO', 'resultado', '', '');
	
				//Ao remover a Assinatura Remover o Fluxohist também, pois está aparecendo a assinatura no resultado e confundindo, já que o resultado foi para Fechado.
				$idfluxostatusAss = FluxoController::getIdFluxoStatus($rowFluxo['modulo'],'ASSINADO');
				$res = InclusaoResultadoController::deletarFluxostatushist($idresultado,$rowFluxo['modulo'],$idfluxostatusAss);
				
				$idfluxostatus = FluxoController::getIdFluxoStatus($rowFluxo['modulo'],'FECHADO');
				$res1 = InclusaoResultadoController::atualizarResultadoParaFechado($idresultado,$idfluxostatus);
	
				if(!$res or !$res1){
					//se alguma transacao falhar, efetuar rollback
					$res3 = mysql_query("ROLLBACK");
					if(!$res3){
						echo "ERRO AO EFETUAR [ROLLBACK] PARA A ALTERACAO: ".mysql_error();
						die();
					}
				}else{
					$res = mysql_query("COMMIT");
					if(!$res){
						echo "ERRO AO EFETUAR [COMMIT] PARA A ALTERACAO: ".mysql_error()."";
						die();
					}
					echo "OK";
					die();
				}
	
			}else{
				echo "ERRO 2: parametro GET vazio! ";
			}
	
		}else{
			echo "Usuário não pode assinar testes";
			die();
		}
		die;

}

//Se salvar o Resultado, ele será reaberto "FECHADO" para assinar novamente
if($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["status"] == 'ASSINADO' || $_SESSION["arrpostbuffer"]["versionares"])
{

	if(cb::idempresa() == 1 && $_POST['_statusant_'] == "ASSINADO"){
		die("Não é possível salvar um resultado assinado! Para alterá-lo, altere o status!");
	}
	$idresultado = $_POST["_1_u_resultado_idresultado"];
	$status = 'FECHADO';
	if($_SESSION["arrpostbuffer"]["versionares"]["u"]["resultado"]["idresultado"]){
		$idresultado = $_SESSION["arrpostbuffer"]["versionares"]["u"]["resultado"]["idresultado"];
		$status = $_SESSION["arrpostbuffer"]["versionares"]["u"]["resultado"]['status'];
		
		$_SESSION["arrpostbuffer"]["versionares"]["i"]["fluxostatushistobs"]['idmodulo'] = $idresultado;
		$_SESSION["arrpostbuffer"]["versionares"]["i"]["fluxostatushistobs"]['modulo'] = $_GET['_modulo'];
		$_SESSION["arrpostbuffer"]["versionares"]["i"]["fluxostatushistobs"]['motivo'] = $_SESSION["arrpostbuffer"]["versionares"]["u"]["resultado"]['motivo'];
		$_SESSION["arrpostbuffer"]["versionares"]["i"]["fluxostatushistobs"]['motivoobs'] = $_SESSION["arrpostbuffer"]["versionares"]["u"]["resultado"]['desc'];
		$_SESSION["arrpostbuffer"]["versionares"]["i"]["fluxostatushistobs"]['status'] = $_SESSION["arrpostbuffer"]["versionares"]["u"]["resultado"]['status'];
		$_SESSION["arrpostbuffer"]["versionares"]["i"]["fluxostatushistobs"]['idfluxostatus'] = $_SESSION["arrpostbuffer"]["versionares"]["u"]["resultado"]['idfluxostatus'];
		$_SESSION["arrpostbuffer"]["versionares"]["i"]["fluxostatushistobs"]['versaoorigem'] = $_SESSION["arrpostbuffer"]["versionares"]["u"]["resultado"]['versaoatual'];
		$_SESSION["arrpostbuffer"]["versionares"]["i"]["fluxostatushistobs"]['versao'] = ($_SESSION["arrpostbuffer"]["versionares"]["u"]["resultado"]['versaoatual']) + 1;
		unset($_SESSION["arrpostbuffer"]["versionares"]["u"]);
		montatabdef();
	}	

    $resr = InclusaoResultadoController::buscarResultadoAssinado($idresultado);   
	
	//Alteração realizada a pedido do William: abrir o resultado quando salvar para versionamento. Setado na Session o novo status.
	$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["status"] = $status;
	$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["idresultado"] =$idresultado;

	//Atualiza o Status do Resultado (tabela, pk, valorpk, $statustipo, $modulotipo, $_primaryhist, status)
	$rowFluxo = FluxoController::getDadosResultadoAmostra('resultado', 'idresultado', $idresultado, $status, 'resultado', '', '');
	FluxoController::restaurarFluxo($rowFluxo['modulo'], 'idresultado', $idresultado, $rowFluxo['statustipo'], $rowFluxo['idfluxostatus']);

 
	InclusaoResultadoController::deletarResultadoAssinaturaPorIdresultadoComAmostra($idresultado);
	
	foreach($resr as $k => $rowr){    
		InclusaoResultadoController::inserirLogAuditoria($_SESSION["SESSAO"]["IDEMPRESA"],'1','u','resultado',$rowr['idresultado'],'status',$status,$_SESSION["SESSAO"]["USUARIO"],$_SERVER["HTTP_REFERER"]);
	}
	$resgs = InclusaoResultadoController::deletarResultadoAssinaturaPorIdresultado($idresultado);
}

if(!empty($_SESSION["arrpostbuffer"]["999"]["i"]["lote"])){

	$arramostra=getObjeto("resultado",$_SESSION['arrpostbuffer']['1']['u']['resultado']['idresultado'],"idresultado");
	//Se não houver TRA criado para a amostra, criar novo
	if(!getObjeto("tra", $arramostra["idamostra"], "idamostra")){
		$arramostra=getObjeto("resultado",$_SESSION['arrpostbuffer']['1']['u']['resultado']['idresultado'],"idresultado");
		$_SESSION["arrpostbuffer"]["1000"]["i"]["tra"]["idamostra"]=$arramostra["idamostra"];
		$_SESSION["arrpostbuffer"]["1000"]["i"]["tra"]["idempresa"]=$_SESSION['SESSAO']['IDEMPRESA'];
	}
}

// buscar quando for resultados individuais do biobox
if(!empty($_SESSION["arrpostbuffer"]["3"]["u"]["resultadoindividual"]["idresultadoindividual"])){

	$row = InclusaoResultadoController::buscarIformacoesProdservPorIdtipoteste($arrlanc["idresultado"]);
	$ngmtpad = $row["gmt"];//armazena o GMT padrao para a idade da amostra
	$tipogmt = $row["tipogmt"];//armazena o GMT padrao para a idade da amostra
	$idprodserv = $row["idprodserv"];//armazena o GMT padrao para a idade da amostra
	$tipocalc = $row["tipogmt"];
	$modelo = $row["modelo"];
		
	$strind=InclusaoResultadoController::buscarValorProdservTipoOpcao($idprodserv,true);

	$arrlancind = array();
	$arrlancind = $_SESSION["arrpostbuffer"];
	
	//print_r($_POST['tipoteste']);
	$vlres=0;
	$msoma=1;
	$nsoma=0;
	foreach ($arrlancind as &$arr) {
		
		$result=$arr["u"]["resultadoindividual"]["resultado"];
		//echo("\n<br>".$arr["u"]["resultadoindividual"]["resultado"]);
		

		// alterdo pois !empty == "0" == false
		if(isset($arr["u"]["resultadoindividual"]["resultado"]) and $tipocalc=="GMT"){//se não for pesagem
			//$pesagem="N";
			$nsoma=$nsoma+1;
			if ($modelo != 'SELETIVO'){
				$rowx = InclusaoResultadoController::buscarValorProdservTipoOpcaoPorValorEIdprodserv($result,$idprodserv);
				$vlres=$strind[$rowx['xresultado']];
			}else{

				$vlres=$strind[$result];
			}
			
			if($vlres==0){
				$vlres=1;
			}
			//echo("\n<br>vres=".$vlres);
			$msoma=$msoma*$vlres;
			
		}elseif(isset($arr["u"]["resultadoindividual"]["resultado"]) and $tipocalc=="ART"){//se for pesagem
			//$pesagem="Y";
			if(empty($xsoma)){
				$xsoma=1;
			}
			$nsoma=$nsoma+1;			
			$xsoma = $xsoma * $arr["u"]["resultadoindividual"]["resultado"];
		}elseif(isset($arr["u"]["resultadoindividual"]["resultado"]) and $tipocalc=="SOMA"){//se for pesagem
			//$pesagem="Y";
			if(empty($xsoma)){
				$xsoma=0;
			}
			$nsoma=$nsoma+1;			
			$xsoma = $xsoma + $arr["u"]["resultadoindividual"]["resultado"];
		}elseif(isset($arr["u"]["resultadoindividual"]["resultado"]) and $tipocalc=="PERC"){//se for pesagem
			//$pesagem="Y";
			if(empty($xsoma)){
				$xsoma=0;
			}
			$nsoma=$nsoma+1;			
			$xsoma = $xsoma + 1;
		}
	}
	
	
	
	if($tipocalc=="GMT"){//se não for pesagem
		//echo("\n<br>msoma=".$msoma);
		$div=1/$nsoma;
		//echo("\n<br>nsoma=".$div);
		
		$xgmt=pow($msoma,$div);
		
		$resgmt = round($xgmt,2);
		
		/*
		//echo "\n<br>nsoma:" .$nsoma;
		//echo "\n<br>xsoma:" .$xsoma;
		
		$nmedia = $xsoma/$nsoma;
		//echo "<br>" . $nmedia;// die();
		
		$xgmt = pow(2, $nmedia);
		
		//NEWCASTLE NÃO MULTIPLICA POR 2.5
		if($_POST['tipoteste']!="NEWCASTLE"){
		$xgmt = 2.5 * $xgmt;
		}
			
		
		//echo "\n<br>" . $xgmt; //die();;
		##################################### GMT
		if($xsoma==0){
			$resgmt = 0;
		}else{
			$resgmt = round($xgmt,2);
		}
		 
		 */
		//echo "\n<br>" .$resgmt;
	}elseif($tipocalc=="ART"){//se for pesagem
		
		//echo "\n<br>nsoma:" .$nsoma;
		//echo "\n<br>xsoma:" .$xsoma;
		$xgmt = pow($xsoma, 1/$nsoma);
		//echo "\n<br>" . $xgmt; //die();
		if($xsoma==0){
			$resgmt = 0;
		}else{
			$resgmt = round($xgmt,2);
		}
		//echo "\n<br>" .$resgmt;
	}elseif($tipocalc=="SOMA"){//se for pesagem
		
		
		if($xsoma==0){
			$resgmt = 0;
		}else{
			$resgmt = round($xsoma,2);
		}
		//echo "\n<br>" .$resgmt;
	}elseif($tipocalc=="PERC"){//se for pesagem
		
		
		if($xsoma==0){
			$resgmt = 0;
		}else{
			$resgmt = round(($xsoma*100)/$nsoma,2);
		}
		//echo "\n<br>" .$resgmt;
	}
	
	if($resgmt==1){
		$resgmt=0;
	}
	IF($tipocalc=="GMT" OR $tipocalc=="ART" OR $tipocalc=="SOMA" OR $tipocalc=="SOMA"){
		$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["gmt"] = $resgmt;
	}
	//die();
}

if(!empty($arrlanc) and empty($_SESSION["arrpostbuffer"]["3"]["u"]["resultadoindividual"]["idresultadoindividual"])){

$row = InclusaoResultadoController::buscarIformacoesProdservPorIdtipoteste($arrlanc["idresultado"]);
$ngmtpad = $row["gmt"];//armazena o GMT padrao para a idade da amostra
$tipogmt = $row["tipogmt"];//armazena o GMT padrao para a idade da amostra
$idprodserv = $row["idprodserv"];//armazena o GMT padrao para a idade da amostra
$tipocalc = $row["tipogmt"];
//print_r($arrlanc);
//die();
//echo $arrlanc;
//O subselect foi incluido ao inves de relacionamento, para nao restringir quando a idade nao for informada


//echo "\n<br> GMT: ".  $row["tipogmt"];
//echo "\n<br> Padrao: ".  $row["gmt"];
//echo "\n<br>";
//echo "\n<br> Orificios Originais: ";

array_multisort($arrlanc);
//print_r($arrlanc);
//echo "\n<br>";// die();

############################################################################################################## Calculo GMT


$nsoma = 0; //soma dos valores de todos os orifícios
$xsoma = 0; //valor do orifício multiplicado pela quantidade de vezes que ele aparece
$xdp1 = -1;
$xdp3 = 0;

$msoma=1;

/*

$arrgumb = array();

//Para Gumboro e necessario montar 15 orificios ao inves de 13, e desconsiderar o 2º e 3º orificios
//Porque assim o orificio que era "2" vira "4", o "3" vira "5" e isso diferencia o gumboro do bronquite
if($row["tipogmt"]=="GMT1"){
	$qorif = 15;

	
	for ($i = 1; $i <= $qorif; $i++) {
		if($i==1){
			$arrgumb["q".$i] = $arrlanc["q".$i];
		}elseif($i==2 or $i==3){
			$arrgumb["q".$i] = 0;
		}elseif($i>3){
			$arrgumb["q".$i] = $arrlanc["q".($i-2)];
		}
	}

	for ($i = 1; $i <= $qorif; $i++) {
		$arrlanc["q".$i] = $arrgumb["q".$i];
	}
}else{
	$qorif = 13;
}
*/



					$resi=InclusaoResultadoController::buscarValorProdservTipoOpcao2($idprodserv);
					$y = 1;
					$nsoma = '';
					foreach($resi as $k => $rowi){	
					
						if ( $rowi['valor'] == '0.0'){
							  $rowi['valor'] = 0;
						}
						
						if($arrlanc['q'.$y]>0){ 
							 $vlres=$rowi['valor'];
						
				
							
							if($vlres==0){
								$vlres=1;
							}
							
							 $xdado1 = $arrlanc['q'.$y]*1;
							
							
							 $vlresx=pow($vlres,$xdado1);
							
							//echo("\n<br>vres=".$vlresx);
							
							 $msoma=$msoma*$vlresx;
							
							//echo("\n<br>msoma=".$msoma);
							 $nsoma = $nsoma + $xdado1;
							
						}

						if ($nsoma == ''){
							$div = '0';
						}else{
							$div=1/$nsoma;
						}
						 
						
						//echo("\n<br>nsomadiv=".$div);
						
						 $xgmt=pow($msoma,$div);
						
						$resgmt = round($xgmt,2);

						
						$y++;
					}
					
					

 $resgmt;
//echo "\n<br>" .$resgmt; 
if($resgmt==1){
	$resgmt=0;
}

//die();
/*

$nmedia = $xsoma/$nsoma;
//echo "<br>" . $nmedia; //die();

$xgmt = pow(2, $nmedia);
//echo "<br>" . $xgmt; //die();
switch ($row["tipogmt"]) {
	//Anteriormente, era necessÃ¡rio multiplicar gumboro por 10, para compensar os orificios 2 e 3,
	//ApÃ³s mudanÃ§a de consideraÃ§Ã£o de 15 orifÃ­cios, o Ã­ndice passou a ser o mesmo
	case "GMT1":
		$xgmt = 2.5 * $xgmt;
		break;
	case "GMT2":
		$xgmt = 2.5 * $xgmt;
		break;
	case "GMT4":
		$xgmt = 2.5 * $xgmt;
		break;
        case "GMT5":
                $xgmt = 2.5 * $xgmt;
                break;
	default:
		break;
}

//echo "\n<br>" . $xgmt; //die();;
##################################### GMT
if($xsoma==0){
	$resgmt = 0;
}else{
	$resgmt = round($xgmt,2);
}
//echo "\n<br>" .$resgmt; //die();

##################################### IDT
$idt1 = $nsoma * (pow($nmedia, 2));
$idt2 = ($xdp3 - $idt1) / $nsoma;
$idt3 =  sqrt($idt2);

$residt = round($idt3,2);

##################################### CVAR
//echo "\n<br>resgmt:" . $resgmt;
//echo "\n<BR>ngmtpad:" . $ngmtpad;

$cvar = (($resgmt - $ngmtpad)/$ngmtpad) * 100;
$resvar = round($cvar, 2);

*/

//die();
//Coloca os resultados em upper
//$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["descritivo"] = str_replace('&NBSP;',' ',strtoupper($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["descritivo"]));
$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["gmt"] = $resgmt;
$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["idt"] = $residt;
$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["var"] = $resvar;
$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["padrao"] = $row["gmt"];
//print_r($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]);



//die();
/*
 * Verifica se os checkbox estão checked
 * Isto é necessário pelo seguinte motivo: inputs type='checkbox', não vêm no buffer se não estiverem checkados
 * criado para checked do meio componente (POSITIVIDADE)
 */

//$iu = $_SESSION['arrpostbuffer']['1']['u']['resultado']['idresultado'] ? 'u' : 'i';
//$_SESSION['arrpostbuffer']['1'][$iu]['resultado']['positividade'] = isset($_SESSION['arrpostbuffer']['1'][$iu]['resultado']['positividade']) ? 'checked' : '';
}
if(!empty($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["jsonresultado"])){

$row = InclusaoResultadoController::buscarIformacoesProdservPorIdtipoteste($arrlanc["idresultado"]);

$modelo = $row["modelo"];
}
// JSONRESULTADO CALCULOS
if(!empty($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["jsonresultado"]) && $modelo == 'DINÂMICO'){
	$_1_u_resultado_jsonresultado = $_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["jsonresultado"];
	$jsonresultado = json_decode($_1_u_resultado_jsonresultado);  

$row = InclusaoResultadoController::buscarProdservPorIdresultado($arrlanc["idresultado"]);
$ngmtpad = $row["gmt"];//armazena o GMT padrao para a idade da amostra
$tipogmt = $row["tipogmt"];//armazena o GMT padrao para a idade da amostra
$idprodserv = $row["idprodserv"];//armazena o GMT padrao para a idade da amostra
$tipocalc = $row["tipogmt"];
$modelo = $row["modelo"];

	$vlres=0;
	$msoma=1;
	$nsoma=0;
	$xsoma=0;

	foreach($jsonresultado->INDIVIDUAL as $campo){
		if($campo->calculo == "SIM"){
			$result=$campo->value;
			//echo "<br>".$result;
			// alterdo pois !empty == "0" == false
			if(isset($campo->value) and $tipocalc=="GMT"){//se não for pesagem
				$nsoma=$nsoma+1;
				$vlres=$result;
				if($vlres==0){
					$vlres=1;
				}
				//echo("\n<br>vres=".$vlres);
				$msoma=$msoma*$vlres;
				// alterdo pois !empty == "0" == false
			}elseif(isset($campo->value) and $tipocalc=="ART"){//se for pesagem
				//$pesagem="Y";
				$nsoma=$nsoma+1;			
				$xsoma = $xsoma + $campo->value;
				//echo "<br>xsoma ".$xsoma;
				//echo "<br>nsoma ".$nsoma;
			}elseif(isset($campo->value) and $tipocalc=="SOMA"){//se for pesagem
				//$pesagem="Y";
				$nsoma=$nsoma+1;			
				$xsoma = $xsoma + $campo->value;
				//echo "<br>xsoma ".$xsoma;
				//echo "<br>nsoma ".$nsoma;
			}elseif(isset($campo->value) and $tipocalc=="PERC"){//se for pesagem
				$nsoma=$nsoma+1;
				if(isset($campo->calculoop) and $campo->calculoop == "Y"){
					$xsoma = $xsoma + 1;
				}		
			}
		}
	}

	if($nsoma == 0){
		$nsoma = 1;
	}

	if($tipocalc=="GMT"){//se não for pesagem
		//echo("\n<br>msoma=".$msoma);
		$div=1/$nsoma;
		//echo("\n<br>nsoma=".$div);
		
		$xgmt=pow($msoma,$div);
		
		$resgmt = round($xgmt,2);

	}elseif($tipocalc=="ART"){//se for pesagem
		
		//echo "\n<br>nsoma:" .$nsoma;
		//echo "\n<br>xsoma:" .$xsoma;
		
		$xgmt = $xsoma/$nsoma;
		//echo "\n<br>" . $xgmt; //die();
		if($xsoma==0){
			$resgmt = 0;
		}else{
			$resgmt = round($xgmt,2);
		}
		//echo "\n<br>" .$resgmt;
	}elseif($tipocalc=="SOMA"){//se for pesagem
		
		$resgmt = $xsoma;
	
	}elseif($tipocalc=="PERC"){//se for pesagem

		$resgmt = ($xsoma*100)/$nsoma;
	
	}
	
	if($resgmt==1 and $tipocalc != "SOMA"){
		$resgmt=0;
	}
	if($tipocalc=="GMT" OR $tipocalc=="ART" OR $tipocalc=="SOMA" OR $tipocalc=="PERC"){
		$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["gmt"] = $resgmt;
	}

	//die($resgmt);

}

if(!empty($arrlanc) and empty($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["jsonresultado"]) && $modelo == 'DINÂMICO'){

$row = InclusaoResultadoController::buscarIformacoesProdservPorIdtipoteste($arrlanc["idresultado"]);
$ngmtpad = $row["gmt"];//armazena o GMT padrao para a idade da amostra
$tipogmt = $row["tipogmt"];//armazena o GMT padrao para a idade da amostra
$idprodserv = $row["idprodserv"];//armazena o GMT padrao para a idade da amostra
$tipocalc = $row["tipogmt"];

array_multisort($arrlanc);


############################################################################################################## Calculo GMT


$nsoma = 0; //soma dos valores de todos os orifícios
$xsoma = 0; //valor do orifício multiplicado pela quantidade de vezes que ele aparece
$xdp1 = -1;
$xdp3 = 0;

$msoma=1;

$y = 1;
$nsoma = '';
foreach($jsonresultado->INDIVIDUAL as $campo){
	if($campo->calculo == "SIM"){
		if ( $campo->value == '0.0'){
			$campo->value = 0;
		}

		if($arrlanc['q'.$y]>0){ 
			$vlres=$campo->value;
			if($vlres==0){
				$vlres=1;
			}
			$xdado1 = $arrlanc['q'.$y]*1;
			$vlresx=pow($vlres,$xdado1);

			//echo("\n<br>vres=".$vlresx);
			$msoma=$msoma*$vlresx;
			//echo("\n<br>msoma=".$msoma);
			$nsoma = $nsoma + $xdado1;
		}
		if ($nsoma == ''){
			$div = '0';
		}else{
			$div=1/$nsoma;
		}
		//echo("\n<br>nsomadiv=".$div);

		$xgmt=pow($msoma,$div);

		$resgmt = round($xgmt,2);
		$y++;
			
	}
}

	$resgmt;
	//echo "\n<br>" .$resgmt; 
	if($resgmt==1){
		$resgmt=0;
	}

//Coloca os resultados em upper
//$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["descritivo"] = str_replace('&NBSP;',' ',strtoupper($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["descritivo"]));
$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["gmt"] = $resgmt;
$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["idt"] = $residt;
$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["var"] = $resvar;
$_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["padrao"] = $row["gmt"];

//die();
/*
 * Verifica se os checkbox estão checked
 * Isto é necessário pelo seguinte motivo: inputs type='checkbox', não vêm no buffer se não estiverem checkados
 * criado para checked do meio componente (POSITIVIDADE)
 */
}
// CRIACAO DO AGENTE
//abre variavel com a acao que veio da tela
//$iu = $_SESSION['arrpostbuffer']['x']['i']['lote']['idprodserv'] ? 'i' : 'u';

//se for um insert, o prodserv tiver sido informado e o lote estiver vazio
if((!empty($_SESSION["arrpostbuffer"]['x']["i"]["lote"]["idprodserv"]) and empty($_SESSION["arrpostbuffer"]['x']["i"]["lote"]["partida"]))){

	$_idprodserv = $_SESSION["arrpostbuffer"]['x']["i"]["lote"]["idprodserv"];

	//colocar automatico o vencimento na semente
	$rowv=InclusaoResultadoController::buscarValidadeSemente($_idprodserv);

	$_arrlote = geraLote($_idprodserv);

	if(strlen($_arrlote[0])==0 or strlen($_arrlote[1])==0){
		die("Falha na geração da Partida (sequence). [".$_arrlote[0]."][".$_arrlote[1]."]");
	}else{
		$_numlote = $_arrlote[0].$_arrlote[1];

		//Enviar o campo para a pagina de submit
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["partida"] = $_numlote;
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["idpartida"] = $_numlote;
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["spartida"] = $_arrlote[0];
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["npartida"] = $_arrlote[1];
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["fabricacao"] =date('d/m/Y');
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["vencimento"] =$rowv['vencimento'];

		if(empty($_SESSION["arrpostbuffer"]['x']["i"]["lote"]["qtdprod"])){
			$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["qtdprod"]=200;
		}
            
		//LTM - 05-05-2021: Retorna o Idfluxo Lote Sementes - Esta informação vem da inclusão resultado para criação de Sementes criaragente()
		$idfluxostatus = FluxoController::getIdFluxoStatus('semente', 'ABERTO');    
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["idfluxostatus"] = $idfluxostatus;  

		//Atribuir o valor para retorno por session['post'] ah pagina anterior. OBS: o decode eh necessario porque o PHP pode forcar automaticamente caracteres diferentes
		$_SESSION["post"]["_x_u_lote_partida"] = $_numlote;		

		//rateia o resultado da semente para a unidade da amostra - apos aprovar a primeira solfab o custo deste rateio e vinculado ao produto da solfab
		geraRateioResultado($_SESSION["arrpostbuffer"]["x"]["i"]["lote"]["idobjetosolipor"],true);
		
		d::b()->query("COMMIT") or die("seqmeiolote: Falha ao efetuar COMMIT [sequence]: ".mysqli_error(d::b()));
	}
}

$e_status=$_SESSION['arrpostbuffer']['x']['u']['lotefracao']['status'];
$e_idlotefracao=$_SESSION['arrpostbuffer']['x']['u']['lotefracao']['idlotefracao'];

if($e_status=='ESGOTADO' and !empty($e_idlotefracao)){
    $row =InclusaoResultadoController::buscarQtdLote($e_idlotefracao);  
    if($row['qtd']>0){

        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['idlote']=$row['idlote'];
        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['idlotefracao']=$e_idlotefracao;
        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['obs']='Esgotado';
        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['qtdd']=recuperaExpoente(tratanumero($row['qtd']),$row['qtd_exp']);
        $_SESSION['arrpostbuffer']['es']['i']['lotecons']['idempresa']=$_SESSION["SESSAO"]["IDEMPRESA"];
        montatabdef(); 
    }


}

/*passada para o poschange
if($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["status"]=="FECHADO"){
	congelaResultado($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["idresultado"], false);
}
*/
?>