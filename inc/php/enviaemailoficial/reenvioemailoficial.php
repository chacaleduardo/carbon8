<?php
require_once("../inc/php/validaacesso.php");
require "../inc/php/composer/vendor/autoload.php";
require_once "../inc/php/composer/vendor/dompdf/dompdf/src/Autoloader.php";

ob_start();

$echosql=false;

$simulacao=false; //Para teste: Não enviar os emails

/********************************************************************************************************
 *	GVT - 26/02/2020 - Implementando reenvio de emails oficiais											*
 * 																										*
 ********************************************************************************************************/


$_usuario = 'josesousa';
$_idpessoa = 2266;
$k = 0;
//Dompdf\Autoloader::register();
//use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
/*
	URL PARA TESTE
	https://sislaudo.laudolab.com.br/tmp/reenvioemailoficial.php?reenvio=Y
*/
if($_GET["reenvio"]=="Y"){
	
	$sqlemailnenviado = "select * 
	from mailfila 
	where
		idmailfila = 59463";
	$resemailnenviado = d::b()->query($sqlemailnenviado) or die("Consulta dos emails oficiais para reenvio. SQL = ".$sqlemailnenviado);

	while($r1 = mysqli_fetch_assoc($resemailnenviado)){
		if((!empty($r1["idobjeto"])) and (!empty($r1["destinatario"]))){
			if(!empty($r1["idempresa"])){
				$_idempresa = $r1["idempresa"];
			}else{
				$_idempresa = 1;
			}
			// Consulta a comunicação externa
			$_rsql = "select * from comunicacaoext where idcomunicacaoext = ".$r1["idobjeto"];
			$_rres = d::b()->query($_rsql) or die("Consulta da comunicação externa falhou. SQL = ".$_rsql);

			$_rrow = mysqli_fetch_assoc($_rres);

			// Consulta a registro de log de mailfila
			$_rsql2 = "select * from mailfila where idobjeto = ".$r1["idobjeto"]." and tipoobjeto = 'comunicacaoext' and destinatario = '".$r1["destinatario"]."'";
			$_rres2 = d::b()->query($_rsql2) or die("Consulta da comunicação externa falhou. SQL = ".$_rsql2);

			$_rrow2 = mysqli_fetch_assoc($_rres2);
			
			$avids=array();
			$sqlu1 = "SELECT * FROM comunicacaoextitem WHERE idcomunicacaoext = ".$r1["idobjeto"];
			$resu1 = d::b()->query($sqlu1) or die("erro ao vincular buscar resultados da comunicacaoext [".mysqli_error()."] ".$sqlu1);
			while($rowu1 = mysqli_fetch_assoc($resu1)){
				$avids[] = $rowu1["idobjeto"];
			}
			
			$nomearq="resultados_".$r1["idobjeto"];
			$nomearqcompleto="/var/www/carbon8/upload/comunicacaoext/".$nomearq.".pdf";
			$link = str_replace("/var/www/carbon8", "", $nomearqcompleto);
			
			// Consulta o nome do cliente para acrescentar no email
			$_rsql3 = "select p.razaosocial as nome from pessoa p where p.status in ('ATIVO','PENDENTE') and p.idpessoa = ".$_rrow2["idsubtipoobjeto"];
			$_rres3 = mysql_query($_rsql3) or die("A Consulta dos dados do cliente falhou : " . mysql_error() . "<p>SQL: ".$_rsql3);

			$_rqtdres=mysql_num_rows($_rres3);
			$_rrow3 = mysql_fetch_array($_rres3);

			if($_rqtdres<1){
				die("Não foi possivel localizar o cliente ".$_rrow2["idsubtipoobjeto"]);
			}
					
			// Consulta informações do resultado e da amostra relacionadas com comunicacaoextitem
			$_rsql4="select a.lote,a.nucleoamostra as nucleo,a.lacre,a.tc
					from comunicacaoextitem i  ,resultado r,amostra a
					where i.idcomunicacaoext = ".$r1["idobjeto"]."
					and i.tipoobjeto='resultado' 
					and r.idresultado=i.idobjeto
					and a.idamostra = r.idamostra";
			$_rres4=mysql_query($_rsql4) or die("Erro ao buscar informações do nucleo. SQL = ".$_rsql4);
			$_rrow4=mysql_fetch_assoc($_rres4);

			// Consulta a registro de log de mailfila
			$_rsql5 = "select r.idsecretaria, a.exercicio from resultado r, comunicacaoextitem ci, amostra a where ci.idobjeto = r.idresultado and r.idamostra = a.idamostra and ci.tipoobjeto = 'resultado' and ci.idcomunicacaoext = ".$r1["idobjeto"]." limit 1";
			$_rres5 = d::b()->query($_rsql5) or die("Consulta da comunicação externa falhou. SQL = ".$_rsql5);

			$_rrow5 = mysqli_fetch_assoc($_rres5);

			if($_rrow['tipo']=="EMAILOFICIALPOS"){
				$positivo="Positivo ";
				//busca email dos oficiais positivos
				$sqlemail="select p.email,c.idcontato,receberes,p.nome
							from pessoa p,pessoacontato c
							where p.status='ATIVO'
							and (p.email !='' and p.email is not null)
							and receberes > ''
							and p.idpessoa = c.idcontato
							and p.email = '".$r1["destinatario"]."'
							and c.idpessoa= ".$_rrow5["idsecretaria"];
			}else{
				$positivo="";
				//busca o email todos
				$sqlemail="select p.email,c.idcontato,receberestodos 	as receberes,p.nome
							from pessoa p,pessoacontato c
							where p.status='ATIVO'
							and (p.email !='' and p.email is not null)
							and receberestodos > ''
							and p.idpessoa = c.idcontato
							and p.email = '".$r1["destinatario"]."'
							and c.idpessoa= ".$_rrow5["idsecretaria"];
			}
			$resemail=mysql_query($sqlemail) or die("Erro ao buscar configurações de email sql=".$sqlemail);
			// Gera identificador do envio do email.
			$envioid = geraIdEnvioEmail();
			
			//envia 1 email para cada endereço
			while($rowemail=mysql_fetch_assoc($resemail)){
					
				$strchenc="";
					
				//Monta a data que ira expirar o acesso ao sistema  
				$date = date_create(date("Y-m-d"));
				date_add($date, date_interval_create_from_date_string('728 days'));
				$dataval=date_format($date, 'Y-m-d');
						
				if($rowemail['receberes']=='LINK'){			
					//Monta a string que será encriptada
					$stringchave = ("usuario=token_secretaria&idpessoa=".$_rrow5["idsecretaria"].'&idcomunicacaoext='.$r1["idobjeto"].'&idcontato='.$rowemail['idcontato'].'&email='.$rowemail['email']."&datalimite=".$dataval);
					// Encripta a string
					$strchenc = trim(enc($stringchave));
				}

				if(empty($strchenc) and $rowemail['receberes']=='LINK'){			
					// insere o log de erro					
					$_rsqlu1="update comunicacaoext set status = 'ERRO',conteudo='A string do token esta vazia' where idcomunicacaoext = ".$r1["idobjeto"];
					mysql_query($_rsqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$_rsqlu1);
				}else{
					//Monta versao TXT (texto puro)
					if($_rrow['tipo']=="EMAILOFICIALPOS"){
						if($rowemail['receberes']=='LINK'){
							$linkpornf = retpar('urlemailresultadooficial');
							$message = retpar('textoemailresultadooficialpos');
						}else{
							$message = retpar('textoemailresultadooficialpdfp');
						}
					}else{
						if($rowemail['receberes']=='LINK'){
							$linkpornf = retpar('urlemailresultadooficial');
							$message = retpar('textoemailsecretaria');
						}else{
							$message = retpar('textoemailsecretariapdf');
						}		
					}
					
					$sqldominio = "SELECT v.email_original as email
									FROM emailvirtualconf v 
									WHERE v.idemailvirtualconf = 37 
									AND v.idempresa =1
									AND v.status = 'ATIVO';";
					$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar dominio de Venda da empresa sql=".$sqldominio);
					$rowdominio = mysqli_fetch_assoc($resdominio);
					$dominio = $rowdominio["email"];
					
					$sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'RESULTADOOFICIAL' AND idempresa =".$_idempresa." ORDER BY idempresarodapeemail asc limit 1";
					$resrodapeemail=d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=".$sqlrodapeemail);
					$rowrodapeemail=mysqli_fetch_assoc($resrodapeemail);
					
					$infcomplementar1 = ( strpos( $rowrodapeemail["assunto"], "_info_" ) !== 0 );
					$infcomplementar2 = ( strpos( $rowrodapeemail["assunto"], "_info1_" ) !== 0 );
					$infcomplementar3 = ( strpos( $rowrodapeemail["assunto"], "_info2_" ) !== 0 );
					$infcomplementar4 = ( strpos( $rowrodapeemail["assunto"], "_info3_" ) !== 0 );
					$infcomplementar5 = ( strpos( $rowrodapeemail["assunto"], "_info4_" ) !== 0 );

					if ($infcomplementar1) {
					   $rowrodapeemail["assunto"] = str_replace("_info_", $positivo, $rowrodapeemail["assunto"]);
					}
					if ($infcomplementar2) {
					   $rowrodapeemail["assunto"] = str_replace("_info1_", $_rrow3["nome"], $rowrodapeemail["assunto"]);
					}
					if ($infcomplementar3) {
					   $rowrodapeemail["assunto"] = str_replace("_info2_", $_rrow4['nucleo'], $rowrodapeemail["assunto"]);
					}
					if ($infcomplementar4) {
					   $rowrodapeemail["assunto"] = str_replace("_info3_", $_rrow4['lote'], $rowrodapeemail["assunto"]);
					}
					if ($infcomplementar5) {
					   $rowrodapeemail["assunto"] = str_replace("_info4_", $_rrow5["exercicio"], $rowrodapeemail["assunto"]);
					}

					// GVT - 30/04/2020 - Alterado o rodapé dos emails. Busca o rodapé cadastrado no módulo empresa. Função se encontra em /inc/php/functions.php
					$rodapeemailhtml = imagemtipoemailempresa("RESULTADOOFICIAL",$_idempresa,$dominio);
					// Caso a função imagemtipoemailempresa retorne FALSE
					if(!$rodapeemailhtml){
						$rodapeemailhtml = "";
					}
																	
					//Monta versao HTML
					$messagehtm = $message;
					$messagehtm = str_replace("nome", 		$_rrow3["nome"], $messagehtm);
					$messagehtm = str_replace("exercicio", 		$_rrow5["exercicio"], $messagehtm);

					if($rowemail['receberes']=='LINK'){
						$urlhtm = $linkpornf.$strchenc;
						$linkpornf = "<a href='".$urlhtm."'>".$urlhtm."</a>";
						$messagehtm = str_replace("urlresultado", $linkpornf, $messagehtm);
					}

					$messagehtm = str_replace("xnucleo", 	$_rrow4['nucleo'], $messagehtm);
					$messagehtm = str_replace("xlote", 		$_rrow4['lote'], $messagehtm);
					$messagehtm = str_replace("xlacre", 	$_rrow4['lacre'], $messagehtm);
					$messagehtm = str_replace("xtc", 		$_rrow4['tc'], $messagehtm);

					//se não tiver nucleo tira a palavra Nucleo:
					if(empty($_rrow4['nucleo'])){
						$messagehtm = str_replace("Nucleo:","", $messagehtm);
					}
				
					//se não tiver lote tira a palavra Lote:
					if(empty($_rrow4['lote'])){
						$messagehtm = str_replace("Lote:","", $messagehtm);
					}
							
					//se não tiver lacre tira a palavra Lacre:
					if(empty($_rrow4['lacre'])){
						$messagehtm = str_replace("Lacre:","", $messagehtm);
					}

					//senão tiver tc tira a palavra Termo de Coleta:
					if(empty($_rrow4['tc'])){
						$messagehtm = str_replace("Termo de Coleta:","", $messagehtm);
					}
							
					$messagehtm = nl2br($messagehtm);
					$messagehtm = $messagehtm.$rodapeemailhtml;
					$messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$messagehtm."</body></html>"; 
							
					/************************CABECALHO E TEXTO**************************/

					//FROM
					$emailFrom=$dominio;
					$nomeFrom=$rowrodapeemail["nomeremetente"];

					//DESTINATARIO
					$emailDest=$rowemail['email'];
					$emailDestNome=$rowemail['nome'];

					//CCO
					//$emailDestCCO="resultados@laudolab.com.br";
					//$emailDestCCONome="Resultados Laudo Laboratório";

					//ASSUNTO
					$assunto=$rowrodapeemail["assunto"];

					//CONFIGURACOES E ENVIO
					$mail = new PHPMailer(true); //true habilita exceptions
					$mail->SMTPDebug=2; //maf120619: Recuperar o diálogo com o servidor IMAP: https://github.com/PHPMailer/PHPMailer/wiki/SMTP-Debugging

					$aMsg=array();
					$aMsg["assunto"] = $assunto;
					$aMsg["mensagem"] = $messagehtm;
					$aMsg["anexos"][0] = $nomearqcompleto;
					
						try{
							$mail->IsSMTP();
							$mail->SMTPAuth  = false;
							$mail->SMTPAutoTLS = false; //somente para testes
							$mail->CharSet = "UTF-8";
							$mail->Host  = '192.168.0.15';
							$mail->Port  = '587';
							
							//$mail->Username  = "admin_laudolab";
							//$mail->Password  = "37383738";

							//Espaço em branco para desativar o header 'PHPMAILER version' http://phpmailer.github.io/PHPMailer/classes/PHPMailer.PHPMailer.PHPMailer.html#property_XMailer
							$mail->XMailer = " ";

							//Rastrear headers em caixas de email
							$mail->addCustomHeader('X-IDOBJETO',$r1["idobjeto"]);
							$mail->addCustomHeader('X-TIPOOBJETO','comunicacaoext');

							$mail->From  = $emailFrom;
							$mail->FromName  = $nomeFrom; //utf8_decode($nomeFrom);

							$mail->IsHTML(true);
							$mail->Subject  = $assunto;
							$mail->Body  = $messagehtm;
							//email destino
							$mail->AddAddress("gabrieltiburcio@laudolab.com.br",$emailDestNome);

							
							/*
								MAF080919: Esta função é uma callback executado durante o diálogo do protocolo SMTP.
								Neste caso está sendo recuperado o ID gerado pelo servidor SMTP Postfix. Isto servirá para consultar no postfix remoto, se a mensagem foi realmente enviada
								Atenção para o escopo incassível de variáveis: foi utilizado o recurso de $GLOBALS
							*/
							$queueid="";
							$mail->Debugoutput = function($debugstr, $level) {
								//echo $debugstr; //mostra TUDO na tela. inclusive o conteudo binario do arquivo anexo
								$pattern='/(queued\ as\ )(.*)/';//Resposta SMTP de sucesso (https://regex101.com/r/rUqXH5/1)
								if (preg_match($pattern, $debugstr, $match)){
									//echo($match[2]);
									global $queueid;
									$queueid=trim($match[2]);
									$GLOBALS["queueid"]=trim($queueid);
								}
							};

							//Adicionar um anexo
							if($rowemail['receberes']=="PDF"){
								$mail->AddAttachment($nomearqcompleto);
							}

							if($simulacao===false){
								//Envia grazadeus
								if($rowemail['receberes']=='PDF'){
								if(!$mail->Send()){
									$ret.= " ERRO ao enviar email. (" . print($mail). ") ";
									$envioerro=1;									
								} else {	
									$k++;
									// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
									$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
											values (".$_idempresa.",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$r1["idobjeto"].",'comunicacaoext',".$_rrow2["idsubtipoobjeto"].",'cliente',".$_rrow2["idpessoa"].",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."','".base64_encode(serialize($aMsg))."',sysdate(),'".$_usuario."','".$_usuario."',sysdate())";

									d::b()->query($_sql);
									// ---------------------------------------------------------------------

									
									$_sqlstmp = "INSERT INTO `logmail`(`idobjeto`,`tipoobjeto`,`destinatario`,`queueid`,`log`,`criadoem`)
									VALUES (".$r1["idobjeto"].",'comunicacaoext','".$emailDest."','".$GLOBALS["queueid"]."','',now());";
									d::b()->query($_sqlstmp) or die("Erro ao gerar Log de Smtp [".mysqli_error(d::b())."] ".$_sqlstmp);

									//echo "Email enviado com sucesso!";
									$ret .= " Enviado email com sucesso! [".$emailDest."] ";
									$retx .= " Enviado email para ".$rowemail['email']." com sucesso! Token(".$strchenc.") ";
									$enviook=1;	

								}
								}
							}else{
								//echo "\n\n-----------Mail getAllRecipientAddresses";
								//print_r($mail->getAllRecipientAddresses());
								//echo "\n\n-----------";
								
								$ret = "\n<Br>Simulação para ".$rowemail['email']." executada com sucesso! ";
								$retx = "\n<br>Simulação de link para ".$rowemail['email']." criado com sucesso! Token(".$strchenc.") ";
								$enviook=1;
								echo $ret;
								echo $retx;				
							}

						}catch (Exception $e) {
							echo "Erro do PEAR::MAIL -> {$mail->ErrorInfo}";
						}
					// Apaga conteúdo do array para a próxima iteração do loop
					unset($aMsg);	
				}//if(empty($strchenc) and $rowemail['receberes']=='LINK'){				
			}//while($rowemail=mysql_fetch_assoc($resemail)){
				
		}else{//if(!empty($_GET["idobjeto"]) or !empty($_GET["destinatario"]))
			die("Parâmetros GET inválidos");
		}
	}
}echo "Foram enviados ".$k." Emails";
?>