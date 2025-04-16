<?
session_start();
$sessionid = session_id();//PEGA A SESS�O 

if (defined('STDIN')){//se estiver sendo executao em linhade comando

	include_once("/var/www/carbon8/inc/php/functions.php");	

}else{//se estiver seno executao via requisicao http
	include_once("../php/functions.php");
}


function LOGETAPA($inidnfslote,$inetapa){
	
	$msgerro = str_replace("'","",$_SESSION["errocon"]); 
	
		
	$sqllog = "insert into "._DBAPP.".nfslog (
				idnfslote
				,etapa
				,erro) 
				values (
					".$inidnfslote."
					,'".$inetapa."'
					,'".$msgerro."')";
	$retlog = d::b()->query($sqllog);
	if(!$retlog){
		die("Erro ao inserir LOG: \n<br>".mysqli_error(d::b())."\n<br>".$sqllog);
	}
}

function STATUSLOTE($inidnfslote, $instatus,$innrps){
	echo("entrou STATUSLOTE ".$instatus);
	//verifica se o status esta devidamente preenchido
	if(!$instatus){
		echo("Erro ao alterar status do LOTE: \n<br>O status informado esta VAZIO.");
		return false;
	}

	//armazena o texto existente na variavel xml
	//$xml = mysqli_real_escape_string($_SESSION["xml"]);
        $xml = str_replace("'","",$_SESSION["xml"]) ;
	/*
	 * altera o status do lote
	 */
	$sqllote = "update nfslote set status = '".$instatus."', xmlretconsult = '".$xml."',alteradoem = sysdate(),nnfe = '".$_SESSION["numeronfe"]."' where idnfslote = ".$inidnfslote;
	$retlote = d::b()->query($sqllote);
	if(!$retlote){
		//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
		echo("Erro ao alterar LOTE: \n<br>".mysqli_error(d::b())."\n<br>".$sqllote);
		return false;
	}
	
	//Caso seja sucesso inserir o numero da nfe na tabela de notasfiscais	
	//if($instatus=="SUCESSO"){	
	if (strpos("'".$instatus."'", 'SUCESSO') == true) {
		
		
		$sql1 = "select numerorps from nfslote
		where idnfslote = ".$inidnfslote;
		
		$res1 = d::b()->query($sql1) or die("A Consulta do numero do numero do RPS falhou : ". mysqli_error(d::b()) . "<p>SQL: $sql1");
		$row1 = mysqli_fetch_assoc($res1);
		
		$sqlnf="update notafiscal n
				set n.nnfe='".$_SESSION['numeronfe']."',n.codver= '".$_SESSION['codver']."',n.status = 'FATURADO'
		 		where n.numerorps='".$row1['numerorps']."'";		
		
		$retnf = d::b()->query($sqlnf);
		
		if(!$retnf){
			//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
			echo("Erro ao alterar NF: \n<br>".mysqli_error(d::b())."\n<br>".$sqlnf);
			return false;
		}else{
			return true;
		}	
	}else{
		echo("  (".$instatus.") ");
	}
	
}

$cpfCnpjRemetente = "23259427000104";//T14 //Cnpj do prestador do servi�o
$codcid = 5403;  //codigo da cidade prestador do servi�o

//Altera o status das notas PENDENTES para CONSULTANDO e reserva para a sess�o
$sqlc = "update "._DBAPP.".nfslote set status = 'CONSULTANDO', sessionid = '".$sessionid."',alteradoem = sysdate() where loteprefeitura is not null and status = 'PENDENTE'";
$retc = d::b()->query($sqlc);

if(!$retc){
	//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
	echo("Erro ao alterar LOTE para consulta: \n<br>".mysqli_error(d::b())."\n<br>".$sqllog);
	return false;
}


$sql= "SELECT * FROM "._DBAPP.".`nfslote`
		where status = 'CONSULTANDO'
		and sessionid = '".$sessionid."' order by numerorps asc";

$sqlres = d::b()->query($sql) or die("A Consulta dos lotes falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");

while ($row = mysqli_fetch_array($sqlres)){
	
	//INSERIR STATUS NA ETAPA COMO  CONSULTA
	LOGETAPA($row["idnfslote"],"CONSULTA");
	
	//$vurl= "http://udigital.uberlandia.mg.gov.br/WsNFe2/LoteRps.jws?wsdl";//produ��o
	//$vurl= "http://200.201.194.78/WsNFe2/LoteRps.jws?wsdl";//homologa��o
	
	//XML DE CONSULTA 
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
					. "<ns1:ReqConsultaLote xmlns:ns1=\"http://localhost:8080/WsNFe2/lote\" xmlns:tipos=\"http://localhost:8080/WsNFe2/tp\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://localhost:8080/WsNFe2/lote http://localhost:8080/WsNFe2/xsd/ReqConsultaLote.xsd\">"
					. "<Cabecalho><CodCidade>"
					. $codcid
					. "</CodCidade><CPFCNPJRemetente>"
					. $cpfCnpjRemetente
					. "</CPFCNPJRemetente>"
					. "<Versao>1</Versao><NumeroLote>" ."".$row["loteprefeitura"].""."</NumeroLote> 
					</Cabecalho>
					</ns1:ReqConsultaLote >";
						
	ini_set("soap.wsdl_cache_enabled", "0");
	
	//conex�o e envio SOAP
	$soapClient = new SoapClient(_URLNFSWS,array('trace' => 1));
	$res= $soapClient->__soapCall("consultarLote",array($xml));	
	//armazena o XML retornado
	$_SESSION["xml"] = $res;
	
	//l� o documento XML retornado
	$doc = DOMDocument::loadXML($res);
		
	if (!$doc){
		$errocon = "O WS n�o retornou XML v�lido:\n Lote - >".$row["loteprefeitura"]." MSG - >".$res."\n<BR>";
		$_SESSION["errocon"] = $errocon;
		STATUSLOTE($row["idnfslote"],"PENDENTE",$row["numerorps"]); 
		LOGETAPA($row["idnfslote"],"CONSULTA");	
		echo $errocon;		
	}else{
			//Pesquisa SOMENTE a PRIMEIRA ocorrencia (item 0) para Cabecalho do xml de retorno
			$cab = $doc->getElementsByTagName("Cabecalho")->item(0);
			//Pesquisa SOMENTE a PRIMEIRA ocorrencia (item 0) para Sucesso do xml de retorno
			$sucesso = $cab->getElementsByTagName("Sucesso")->item(0);
			//pega o valor da tag Sucesso
			$vsucesso =($sucesso->textContent); //->length;
			
			//se a tag Sucesso for true o lote foi processado com sucesso			
			if($vsucesso=="true"){	
				
				// pega o n�mero da notafiscal gerada
				$lista = $doc->getElementsByTagName("ListaNFSe")->item(0);
				$consulta = $lista->getElementsByTagName("ConsultaNFSe")->item(0);
				$numero = $consulta->getElementsByTagName("NumeroNFe")->item(0);
				$CodigoVerificacao =  $consulta->getElementsByTagName("CodigoVerificacao")->item(0);
				
				$numeronfe =($numero->textContent); //->length;
				$codver =($CodigoVerificacao->textContent); //->length;
				
				$_SESSION["numeronfe"] = $numeronfe;
				$_SESSION["codver"] = $codver;
				
				STATUSLOTE($row["idnfslote"],"SUCESSO",$row["numerorps"]); //("OK PROCESSADO");
				echo "Sucesso Lote ".$row["loteprefeitura"]." Nfe N:".$_SESSION["numeronfe"] ;				
								
			}elseif($vsucesso=="false"){//se for false pode ser que tenha erro ou alerta
								
				$Erros = $doc->getElementsByTagName("Erros")->item(0);
								
				$Erro = $Erros->getElementsByTagName("Erro")->item(0);
				
				if(!$Erro){//se a tag erro for vazia o false da tag Sucesso e referente a alertas 
					$errocon = ("LOTE COM ALERTA ->".$row["loteprefeitura"]);
					$_SESSION["errocon"] = $errocon;
					STATUSLOTE($row["idnfslote"],"PENDENTE",$row["numerorps"]); 
					LOGETAPA($row["idnfslote"],"CONSULTA");	
					echo $errocon;
									
				}else{//se a tag erro estiver preenchida o lote esta com erro					
					STATUSLOTE($row["idnfslote"],"ERRO",$row["numerorps"]);
					$errocon = ("LOTE COM ERRO - >".$row["loteprefeitura"]);
					$_SESSION["errocon"] = $errocon;
					LOGETAPA($row["idnfslote"],"CONSULTA");
					echo $errocon;
				}			
			}else{
				$errocon ("Valor da Tag Sucesso imprevisto - >".$row["loteprefeitura"]);
				$_SESSION["errocon"] = $errocon;
				STATUSLOTE($row["idnfslote"],"PENDENTE",$row["numerorps"]);
				LOGETAPA($row["idnfslote"],"CONSULTA");
				echo $errocon;
			}			
	}	
 //echo($res);
}

$sqlf = "update "._DBAPP.".nfslote set status = 'PENDENTE',alteradoem = sysdate() where status = 'CONSULTANDO' and sessionid = '".$sessionid."'";
$retf = d::b()->query($sqlf);

if(!$retf){
	//LOG($inidlote,$inetapa,$incoderro,$inerro,$inxml);
	echo("Erro ao voltar o status do LOTE consulta: \n<br>".mysqli_error(d::b())."\n<br>".$sqllog);
	return false;
}


?>
