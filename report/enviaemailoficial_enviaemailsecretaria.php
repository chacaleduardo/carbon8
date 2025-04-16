<?
ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
	include_once("/var/www/carbon8/inc/php/functions.php");
	include_once("/var/www/carbon8/inc/php/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}else{//se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
	include_once("../inc/php/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}

/*
 * MAF: PARA TESTAR: rodar no browser com parametro GET. O Email NAO sera enviado.
 */
$testeinterno = $_GET["testeinterno"];

$idnucleo = $_GET["idnucleo"];
$exercicio = $_GET["exercicio"];
$idsecretaria = $_GET["idsecretaria"];
$idpessoa = $_GET["idpessoa"];
$newidcomunicacao=trim($_GET["idcomunicacaoext"]);

IF(empty($newidcomunicacao)){
die("Esta vazio o id comunicacao");
}

conectabanco();
$envioerro=0;
$enviook=0;

/*
 * Busca os dados principais do cliente,idresultado e idregistro para envio
 * Obs: As Secretarias que não possuírem os emails configurados na tabela pessoa, serao colocadas como EMAILAUSENTE e I = impossivel enviar sem email na resultado
 * emailsec = N = não enviar / A = Aguardando envio / E = Enviado / I = impossivel enviar sem email
 */

		// se o campo com os emails estiver preenchido	
		if(!empty($idsecretaria) and !empty($exercicio) and !empty($idnucleo) and !empty($newidcomunicacao)){
			
			//buscar o tipo da comunicacão
			$sqlcom="select  tipo from comunicacaoext where idcomunicacaoext=".$newidcomunicacao;
			$rescom=mysql_query($sqlcom) or die("Erro ao buscar tipo de comunicacao sql=".$sqlcom);
			$rowcom= mysql_fetch_assoc($rescom);
									
			$sql = "select 	p.razaosocial as nome
					from pessoa p
					where p.status = 'ATIVO'
					and p.idpessoa = ".$idpessoa;
			//die($sql);

			$sqlres = mysql_query($sql) or die("A Consulta dos dados do cliente falhou : " . mysql_error() . "<p>SQL: $sql");

			$qtdres=mysql_num_rows($sqlres);
			if($qtdres<1){
				die("Não foi possivel localizar o cliente ".$idpessoa);
			}

			$row = mysql_fetch_array($sqlres);
			
			//$sqln="select * from nucleo where idnucleo=".$idnucleo;
			$sqln="select a.lote,a.nucleoamostra as nucleo,a.lacre,a.tc
					from comunicacaoextitem i  ,resultado r,amostra a
					where i.idcomunicacaoext = ".$newidcomunicacao."
					and i.tipoobjeto='resultado' 
					and r.idresultado=i.idobjeto
					and a.idamostra = r.idamostra";
			$resn=mysql_query($sqln) or die("Erro ao buscar informações do nucleo");
			$rown=mysql_fetch_assoc($resn);
			
		//da explode para pegar os emails
		//	$stremail = explode(",",$row["emailresult"]);
		
			if($rowcom['tipo']=="EMAILOFICIALPOS"){
				$positivo="Positivo ";
				//busca email dos oficiais positivos
				$sqlemail="select p.email,c.idcontato,receberes,p.nome
							from pessoa p,pessoacontato c
							where p.status='ATIVO'
							and (p.email !='' and p.email is not null)
							and receberes > ''
							and p.idpessoa = c.idcontato
							and c.idpessoa= ".$idsecretaria;				
			}else{
				$positivo="";
				//busca o email todos
				$sqlemail="select p.email,c.idcontato,receberestodos 	as receberes,p.nome
							from pessoa p,pessoacontato c
							where p.status='ATIVO'
							and (p.email !='' and p.email is not null)
							and receberestodos > ''
							and p.idpessoa = c.idcontato
							and c.idpessoa= ".$idsecretaria;			
			}
			
			$resemail=mysql_query($sqlemail) or die("Erro ao buscar configurações de email sql=".$sqlemail);
			$emailcopiaoculta="resultados@laudolab.com.br";
			// roda no loop para enviar um email para cada endereço
			while($rowemail=mysql_fetch_assoc($resemail)){
			
				$emailunico = $rowemail['email'];
				$strchenc="";
				//echo $emailunico;
				//echo("</br>");
			
				//Monta a data que ira expirar o acesso ao sistema  
				$date = date_create(date("Y-m-d"));
				date_add($date, date_interval_create_from_date_string('728 days'));
				$dataval=date_format($date, 'Y-m-d');
				
				if($rowemail['receberes']=='LINK'){				
					//Monta a string que será encriptada
					$stringchave = ("usuario=token_secretaria&idpessoa=".$idsecretaria.'&idcomunicacaoext='.$newidcomunicacao.'&idcontato='.$rowemail['idcontato'].'&email='.$rowemail['email']."&datalimite=".$dataval);
					// Encripta a string
					$strchenc = trim(enc($stringchave));
				}

				if(empty($strchenc) and $rowemail['receberes']=='LINK'){
				
					// insere o log de erro					
					$sqlu1="update comunicacaoext set status = 'ERRO',conteudo='A string do token esta vazia' where idcomunicacaoext = ".$newidcomunicacao;
					mysql_query($sqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu1);
										
				}else{
					//echo($strchenc);
										
					/*
					 * Monta versao de Texto
					 */
					//busca o texto para a mensagem
					if($rowcom['tipo']=="EMAILOFICIALPOS"){
						if($rowemail['receberes']=='LINK'){
							$linkpornf = retpar('urlemailresultadooficial');
							$message = retpar('textoemailresultadooficialpos');
						}else{
							$message = retpar('textoemailresultadooficialpdfp');
						}						
						//$messagetxt = $message;
					//	$messagetxt = str_replace("nome", 		$row["nome"], $messagetxt);
					//	$messagetxt = str_replace("exercicio", 		$exercicio, $messagetxt);
					//	$messagetxt = str_replace("numeroreg", 	$rown['nucleo'].'/'.$rown['lote'], $messagetxt);
					}else{						
						if($rowemail['receberes']=='LINK'){
							$linkpornf = retpar('urlemailresultadooficial');
							$message = retpar('textoemailsecretaria');
						}else{
							$message = retpar('textoemailsecretariapdf');
						}						
					//	$messagetxt = $message;
					//	$messagetxt = str_replace("nome", 		$row["nome"], $messagetxt);
					//	$messagetxt = str_replace("exercicio", 		$exercicio, $messagetxt);
					//	$messagetxt = str_replace("xnucleo", 	$rown['nucleo'], $messagetxt);
					//	$messagetxt = str_replace("xlote", 	$rown['lote'], $messagetxt);
					}
										
					$rodapeemailhtml = retpar("rodapeemailresultadosbasefor");
					//$rodapeemailtxt = retpar("rodapeemailresultadostxt");
															
				//	if($rowemail['receberes']=='LINK'){
				//	$messagetxt = str_replace("urlresultado",	$linkpornf.$strchenc, $messagetxt);
				//	}
					//$messagetxt = $messagetxt . $rodapeemailtxt;
					//die($messagetxt);
			
					/*
					 * Monta versao HTML
					 */
					$messagehtm = $message;
					$messagehtm = str_replace("nome", 		$row["nome"], $messagehtm);
					$messagehtm = str_replace("exercicio", 		$exercicio, $messagehtm);
					
					if($rowemail['receberes']=='LINK'){
						$urlhtm = $linkpornf.$strchenc;
						$linkpornf = "<a href='".$urlhtm."'>".$urlhtm."</a>";
						$messagehtm = str_replace("urlresultado", $linkpornf, $messagehtm);
					}
	
					$messagehtm = str_replace("xnucleo", 	$rown['nucleo'], $messagehtm);
					$messagehtm = str_replace("xlote", 	$rown['lote'], $messagehtm);
					
					$messagehtm = str_replace("xlacre", 	$rown['lacre'], $messagehtm);
					$messagehtm = str_replace("xtc", 	$rown['tc'], $messagehtm);
					
					//senão tiver nucleo tira a palavra Nucleo:
					if(empty($rown['nucleo'])){
						$messagehtm = str_replace("Nucleo:","", $messagehtm);
					}
					
					//senão tiver lote tira a palavra Lote:
					if(empty($rown['lote'])){
						$messagehtm = str_replace("Lote:","", $messagehtm);
					}
					
					//senão tiver lacre tira a palavra Lacre:
					if(empty($rown['lacre'])){
						$messagehtm = str_replace("Lacre:","", $messagehtm);
					}
					//senão tiver tc tira a palavra Termo de Coleta:
					if(empty($rown['tc'])){
						$messagehtm = str_replace("Termo de Coleta:","", $messagehtm);
					}
					
					$messagehtm = nl2br($messagehtm);
					$messagehtm = $messagehtm.$rodapeemailhtml;
					$messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$messagehtm."</body></html>"; 
					
					
					
					
					/************************CABECALHO E TEXTO**************************/
					/*** FROM***/
					$emailFrom="oficial@laudolab.com.br";
					$nomeFrom="Oficial - Laudo Laboratório";
					/***DESTINATARIO***/
					$emailDest=$rowemail['email'];
					$emailDestNome=$rowemail['nome'];
					/***CCO***/
				//	$emailDestCCO="resultados@laudolab.com.br";
				//	$emailDestCCONome="Resultados Laudo Laboratório";
					/*** ASSUNTO***/
					$assunto='Resultado Oficial '.$positivo.' - '.$row["nome"].',  Nucleo '.$rown['nucleo'].' / Lote '.$rown['lote'].' de '.$exercicio.' ';

					
					if($testeinterno!="Y"){//Se nao estiverem sendo executados testes pela web
					
						/******************************CONFIGURACOES E ENVIO*****************************************/

						$mail = new PHPMailer();
						$mail->IsSMTP();
						$mail->SMTPAuth  = true;
						$mail->Charset   = 'utf8_decode()';
						$mail->Host  = 'mail.laudolab.com.br';
						$mail->Port  = '587';
						$mail->Username  = "admin_laudolab";
						$mail->Password  = "37383738";
						$mail->From  = $emailFrom;
						$mail->FromName  = $nomeFrom;
						$mail->IsHTML(true);
						$mail->Subject  = $assunto;
						$mail->Body  = $messagehtm;
						//email destino
						$mail->AddAddress($emailDest,utf8_decode($emailDestNome));
						// Copia
						//$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); 
						//email copia oculta
					//	$mail->AddBCC($emailDestCCO, utf8_decode($emailDestCCONome));

						// Adicionar um anexo
						if($rowemail['receberes']=="PDF"){					
							$mail->AddAttachment('/var/www/laudo/tmp/resultadopdf/resultadocomext'.$newidcomunicacao.'.pdf');						
							//$ret.='/var/www/laudo/tmp/resultadopdf/resultadocomext'.$newidcomunicacao.'.pdf';
						}
						//enviar
						
						if(!$mail->Send()){						
							//echo "ERRO ao enviar email. (" .print($mail). ")";
							$ret.= " ERRO ao enviar email. (" . print($mail). ") ";
							$retx.= " ERRO ao enviar email. (" .print($mail). ") ";
							$envioerro=1;									
						} else {						
							//echo "Email enviado com sucesso!";
							$ret .= " Enviado email para ".$rowemail['email']." com sucesso! ";
							$retx .= " Enviado email para ".$rowemail['email']." com sucesso! Token(".$strchenc.") ";
							$enviook=1;						
						}	
						/***********************************************************************/
					
					}
					
					
					//echo($messagehtm);
					//die($messagehtm);
					//$crlf = "\n";
					// MAIL MIME
					/*
					$hdrs = array(
									'From'    => '"Resultados Laudo Laboratório" <resultados@laudolab.com.br>',
									'To' => $rowemail['email'],
									'Bcc' =>$emailcopiaoculta,
									'Subject' =>  'Resultado Oficial '.$positivo.''.$row["nome"].',  Nucleo / Lote '.$rown['nucleo'].'/'.$rown['lote'].' de '.$exercicio.' ' ,
									'X-Confirm-Reading-To' => 'resultados@laudolab.com.br',
									'Importance' => 'High'
					              );
					if($testeinterno!="Y"){//Se nao estiverem sendo executados testes pela web
						$mime = new Mail_mime();					
						$mime->setTXTBody($messagetxt);
						$mime->setHTMLBody($messagehtm);
			
						//adiciona a imagem do rodape para o email em HTML
						//este caminho deve ser o mesmo caminho colocado no rodapeemailhtml = retpar("rodapeemailresultadoshtml") que fica no banco de dados
						$mime->addHTMLimage('/var/www/laudo/img/Departamento Tecnico.png','image/png');
					
						if($rowemail['receberes']=="PDF"){					
							$mime->addAttachment('/var/www/laudo/tmp/resultadopdf/resultadocomext'.$newidcomunicacao.'.pdf','text/plain');						
							//$ret.='/var/www/laudo/tmp/resultadopdf/resultadocomext'.$newidcomunicacao.'.pdf';
						}
			
						//do not ever try to call these lines in reverse order
						$body = $mime->get();
						$hdrs = $mime->headers($hdrs);
			
						$params["host"] = "mail.laudolab.com.br";
						$params["port"] = "587";
						$params["auth"] = true;
						$params["username"] = "admin_laudolab";
						$params["password"] = "37383738";
						# $params['debug'] = 'true';
			
						$mail =& Mail::factory("smtp", $params);
						$mail->send($rowemail['email'], $hdrs, $body);
			
						if (PEAR::IsError($result)) {						
							//echo "ERRO ao enviar email. (" . $result->getMessage(). ")";
							$ret.= " ERRO ao enviar email. (" . $result->getMessage(). ") ";
							$retx.= " ERRO ao enviar email. (" . $result->getMessage(). ") ";
							$envioerro=1;
									
						} else {						
							//echo "Email enviado com sucesso!";
							$ret .= " Enviado email para ".$rowemail['email']." com sucesso! ";
							$retx .= " Enviado email para ".$rowemail['email']." com sucesso! Token(".$strchenc.") ";
							$enviook=1;						
						}						
										
					}//$testeweb	
					*/
				}
				#$emailcopiaoculta="";
			}	
		}else{
				$ret= "ERRO ao tentar enviar o email. (Campo nucleo, exercicio e secretaria devem ser preenchidos!)";
						
				$sqlu1="update comunicacaoext set status = 'ERRO',conteudo='ERRO ao tentar enviar o email. (Campo nucleo, exercicio e secretaria devem ser preenchidos!)a' where idcomunicacaoext = ".$newidcomunicacao;
				mysql_query($sqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu1);
	 			die($ret);
		}
			
		if($enviook==1){
			//echo($ret);			
				$sqlu1="update comunicacaoext set status = 'SUCESSO',conteudo='".$messagetxt."' where idcomunicacaoext = ".$newidcomunicacao;		
				mysql_query($sqlu1) or die("erro ao inserir Log de SUCESSO [".mysql_error()."] ".$sqlu1);
			
		}else{
			
			$sqlu1="update comunicacaoext set status = 'ERRO',conteudo='".$retx."' where idcomunicacaoext = ".$newidcomunicacao;
			mysql_query($sqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu1);			
			//echo($retx);
		}

echo($ret);
?>