<?
session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

// GVT - 05/02/2020 - Usar outro PHPMailer para registrar os logs no servidor Hermes.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

////////////////////////////////////////////////////////////////////////////////////

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  require_once("/var/www/carbon8/inc/php/functions.php");
  require_once("/var/www/carbon8/inc/php/laudo.php");
  require_once("/var/www/carbon8/inc/php/composer/vendor/autoload.php");
  //require_once("/var/www/carbon8/inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}else{//se estiver sendo executado via requisicao http
  require_once("../inc/php/functions.php");
  require_once("../inc/php/laudo.php");
  require_once("../inc/php/composer/vendor/autoload.php");
  //require_once("../inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}

$grupo = rstr(8);

re::dis()->hMSet('cron:enviaemailcotacao',['inicio' => Date('d/m/Y H:i:s')]);

$redislog = re::dis()->get('cron:ativalog');
if($redislog)
{
	$redislog = 1;
} else {
	$redislog = 0;
}

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`,  `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailcotacao', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);



/*
 * MAF: PARA TESTAR: rodar no browser com parametro GET. O Email NAO sera enviado.
 */
$testeinterno = $_GET["testeinterno"];


/*
 * Busca os dados principais do cliente,idresultado e idregistro para envio
 * Obs: As Secretarias que não possuírem os emails configurados na tabela pessoa, serao colocadas como EMAILAUSENTE e I = impossivel enviar sem email na resultado
 * emailsec = N = não enviar / A = Aguardando envio / E = Enviado / I = impossivel enviar sem email
 */


$sql = "select co.idcotacao,p.emailresult as emailresult,p.emailresult as tememail,p.nome,p.idpessoa,c.idnf,concat(co.idcotacao,'.',c.idnf) as nsolicitacao,DATEDIFF(co.prazo,sysdate()) as prazo,dma(co.prazo) as dmaprazo,c.idobjetosolipor,c.tipoobjetosolipor,c.alteradopor,co.idempresa
	from nf c,cotacao co,pessoa p
	 where  c.tiponf in ('C','S', 'M', 'B')
	 and c.envioemailorc='Y'
	 and co.idcotacao = c.idobjetosolipor 
	 and c.tipoobjetosolipor ='cotacao'
	 and p.idpessoa = c.idpessoa";



//die($sql);

$sqlres = d::b()->query($sql) or die("A Consulta do fornecedor falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");


while ($row = mysqli_fetch_array($sqlres)){
	// Gera identificador do envio do email.
	$envioid = geraIdEnvioEmail();
        $envioerro=0;
        $enviook=0;
        $sqlf = "update nf set envioemailorc = 'A', status = 'ENVIADO', idfluxostatus = 1159 where idnf =".$row["idnf"];
        $retf = d::b()->query($sqlf);

        if(!$retf){              
            echo("Erro ao atualizar status da cotacao \n<br>".mysqli_error(d::b())."\n<br>".$sqlf);
            die();
        }

		// se o campo com os emails estiver preenchido	
		if(!empty($row["idpessoa"]) and !empty($row["idnf"]) and !empty($row["tememail"])){
			
			$sqlemail = "SELECT v.email_original as email
						FROM empresaemailobjeto e 
						JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
						WHERE e.tipoenvio = 'COTACAO'
							AND e.tipoobjeto = 'nf'
							AND e.idobjeto = ".$row["idnf"]."
							AND e.idempresa = ".$row["idempresa"]."
							AND v.status = 'ATIVO'
						ORDER BY e.idempresaemailobjeto desc limit 1";
			$resemail=d::b()->query($sqlemail) or die("Erro ao buscar emails da empresa sql=".$sqlemail);
			$qtdemail=mysqli_num_rows($resemail);
			if($qtdemail>0){
				$rowemail = mysqli_fetch_assoc($resemail);
				$row["emailresult"].=",".$rowemail['email'];
				$dominio = $rowemail['email'];
			}else{
				$sqlempresaemail = "SELECT ev.email_original as dominio 
									FROM empresaemails em
									JOIN emailvirtualconf ev on (em.idemailvirtualconf = ev.idemailvirtualconf)
									WHERE em.tipoenvio = 'COTACAO'
									AND em.idempresa = {$row["idempresa"]}
									AND ev.status = 'ATIVO'
									ORDER BY em.idempresaemails asc limit 1";
				$resempresaemail=d::b()->query($sqlempresaemail) or die("Erro ao buscar email da empresa sql=".$sqlempresaemail);
				$rowempresaemail = mysqli_fetch_assoc($resempresaemail);
				$dominio = $rowempresaemail['dominio'];
				$row["emailresult"].=",".$rowempresaemail['dominio'];
			}
			
			if(!empty($row["idempresa"])){
				$idempresa = $row["idempresa"];
			}else{
				$idempresa = 0;
			}
			
			// GVT - 30/04/2020 - Alterado o rodapé dos emails. Busca o rodapé cadastrado no módulo empresa. Função se encontra em /inc/php/functions.php
			$rodapeemailhtml = imagemtipoemailempresa("COTACAO",$idempresa,$dominio);
			// Caso a função imagemtipoemailempresa retorne FALSE
			if(!$rodapeemailhtml){
				$rodapeemailhtml = "";
			}
				
			//da explode para pegar os emails
			$stremail = explode(",",$row["emailresult"]);

			if(empty($dominio)){
				$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
				values (".$row["idnf"].",'nf','EMAILCOTACAO','REMETENTE VAZIO','ERRO',sysdate())";

				d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");
				die("O remetente está vazio");
			}
			
			// roda no loop para enviar um email para cada endereço 
			for($i=0;$i<count($stremail);$i++){
				$ret="";
				$emailunico = $stremail[$i];
				echo $emailunico;
				
				//echo("</br>");
			
				//Monta a data que ira expirar o acesso ao sistema  
				$date = date_create(date("Y-m-d"));
				date_add($date, date_interval_create_from_date_string($row["prazo"].' days'));
				$dataval=date_format($date, 'Y-m-d');
				
				//Monta a string que será encriptada
				$stringchave = ("_acao=u&usuario=token_cotacao&idpessoa=".$row["idpessoa"]."&idnf=".$row["idnf"]."&email=".$stremail[$i]."&_idempresa=".$row["idempresa"]."&datalimite=".$dataval);
				// Encripta a string
				$strchenc = enc($stringchave);

				$sqlempresa = "SELECT * FROM empresa WHERE idempresa = ".$row["idempresa"];
				$resempresa=d::b()->query($sqlempresa) or die("Erro ao buscar informações da empresa: ".mysqli_error(d::b()).$sqlempresa);
				$rowempresa=mysqli_fetch_assoc($resempresa);
				
				//monta a mensagem
				$linkpornf = retpar('urlemailcotacao');
				//$message = retpar('textoemailcotacao');
				$message = '
						 Prezado fornecedor nomefornecedor,

						A empresa '.$rowempresa["nomefantasia"].' o convida a participar do processo de Orçamento <b>_numcotacao</b>, Cotação <b> _idnf</b>.

						Clique urlcotacao para preenchimento de valores e condições comerciais dos materiais e/ou serviços relacionados. 
						Este Orçamento poderá ser acessado até <b>prazo</b>. Caso não consiga acessá-la, favor entrar em contato conosco.


						<font color="red">OBS¹: </font> 
						Navegadores compatíveis: Google Chrome e Mozilla Firefox.

						<font color="red">OBS²: </font>
						- Neste link haverá 8 campos que deverão ser preenchidos.
						- Em seguida, clique em ENVIAR e o documento será emitido para o sistema.
						- Caso a cotação seja aprovada, uma confirmação de compra será enviada posteriormente.';
						
						if(!empty($rowempresa["horariorecebimento"])){
						$message .= "<b>** HORÁRIO DE RECEBIMENTO DE MERCADORIAS **</b>
						".$rowempresa["horariorecebimento"];
						}
						$message .='
						Atenciosamente,
				';
				
				//$rodapeemailhtml = retpar("rodapesuprimentos");
				
				//$rodapeemailtxt = retpar("rodapeemailcotacaotxt");
		
				/*
				 * Monta versao de Texto
				 */
				$messagetxt = $message;		
				$messagetxt = str_replace("urlcotacao",	$linkpornf, $messagetxt);
				$messagetxt = str_replace("prazo", 		$row["dmaprazo"], $messagetxt);
				$messagetxt = str_replace("_numcotacao", 	$row["idcotacao"], $messagetxt);
				$messagetxt = str_replace("_idnf", 		$row["idnf"], $messagetxt);
				$messagetxt = str_replace("nomefornecedor", 		$row["nome"], $messagetxt);
				//$messagetxt = $messagetxt . $rodapeemailtxt;
				
				//die($messagetxt);
		
				/*
				 * Monta versao HTML
				 */
				$messagehtm = $message;
				$urlhtm = $linkpornf.$strchenc;
				$linkpornf = "<a href='".$urlhtm."'><font color='red'>aqui</font></a>";
				$messagehtm = str_replace("urlcotacao", $linkpornf, $messagehtm);
				$messagehtm = str_replace("prazo", 		$row["dmaprazo"], $messagehtm);
				$messagehtm = str_replace("_numcotacao", 	$row["idcotacao"], $messagehtm);
				$messagehtm = str_replace("_idnf", 		$row["idnf"], $messagehtm);
				$messagehtm = str_replace("nomefornecedor",	$row["nome"], $messagehtm);
				
				$messagehtm = nl2br($messagehtm);
				$messagehtm = $messagehtm.$rodapeemailhtml;
				$messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$messagehtm."</body></html>"; 
		
				echo($messagehtm);
				//die($messagehtm);

				$sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'COTACAO' AND idempresa =".$idempresa." ORDER BY idempresarodapeemail asc limit 1";
				$resrodapeemail=d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=".$sqlrodapeemail);
				$rowrodapeemail=mysqli_fetch_assoc($resrodapeemail);
				            
				/************************CABECALHO E TEXTO**************************/
				/*** FROM***/
				$emailFrom=$dominio;
				$nomeFrom=$rowrodapeemail["nomeremetente"];
				/***DESTINATARIO***/
				$emailDest=$stremail[$i];
				$emailDestNome=$row['nome'];
				/***CCO***/
				if(empty($rowrodapeemail["comcopia"])){
					$auxCC = $dominio;
				}else{
					$auxCC = $rowrodapeemail["comcopia"];
				}
				$emailDestCCO=$auxCC;
				$emailDestCCONome=$rowrodapeemail["nomecc"];
				
				/*** ASSUNTO***/
				$infcomplementar = ( strpos( $rowrodapeemail["assunto"], "_info_" ) !== 0 );
				$infcomplementar2 = ( strpos( $rowrodapeemail["assunto"], "_info1_" ) !== 0 );

				if ($infcomplementar) {
				   $rowrodapeemail["assunto"] = str_replace("_info_", $row['idcotacao'], $rowrodapeemail["assunto"]);
				}
				if ($infcomplementar2) {
				   $rowrodapeemail["assunto"] = str_replace("_info1_", $row["idnf"], $rowrodapeemail["assunto"]);
				}
				
				$assunto=$rowrodapeemail["assunto"];

                            if($testeinterno!="Y"){//Se nao estiverem sendo executados testes pela web

				/******************************CONFIGURACOES E ENVIO*****************************************/
				// GVT - 08/07/2020 - Adicionado array para armazenar assunto, mensagem e anexos do email.
				//					- O array é armazenado na tabela mailfila para ser utilizado posteriormente
				//					- para mostrar o que foi enviado no email e reenvio do mesmo.
				$aMsg=array();
				$aMsg["assunto"] = $assunto;
				$aMsg["mensagem"] = $messagehtm;
				
				/******************************CONFIGURACOES E ENVIO*****************************************/
				$mail = new PHPMailer(true);
				$mail->IsSMTP();
				$mail->SMTPDebug=2;
				$mail->SMTPAuth  = false;
				$mail->SMTPAutoTLS = false;
				//$mail->Charset   = 'utf8_decode()';
				$mail->CharSet = "UTF-8";
				$mail->Host  = '192.168.0.15';
				$mail->Port  = '587';
				//$mail->Username  = "admin_laudolab";
				//$mail->Password  = "37383738";
				$mail->From  = $emailFrom;
				$mail->FromName  = $nomeFrom;
				$mail->IsHTML(true);
				
				$mail->Subject  = $assunto;
				$mail->Body  = $messagehtm;
				//email destino
				$mail->AddAddress($emailDest,$emailDestNome);

				$mail->AddCustomHeader("X-MAIL-MOD:".strtoupper(explode(".",basename($_SERVER["SCRIPT_FILENAME"]))[0]));
				// Copia
				//$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); 
				//email copia oculta
			//	$mail->AddBCC($emailDestCCO, $emailDestCCONome);

				// Adicionar um anexo
				//
				if($emailDest == 'pedrolima@laudolab.com.br'){
					$sqlarq = "SELECT
						a.*
					from arquivo a 
					JOIN prodserv p on (p.idprodserv = a.idobjeto and a.tipoobjeto = 'arqCotacao')
					JOIN nfitem ni on (ni.idprodserv = p.idprodserv)
					JOIN nf n on (n.idnf = ni.idnf)
					where n.idnf=".$row['idnf'];
					$resarq = d::b()->query($sqlarq) or die("Erro ao buscar itens da nota:".mysqli_error($resarq));
					if(mysqli_num_rows($resarq) > 0){
						while($rowarq = mysqli_fetch_assoc($resarq)){
							$mail->AddAttachment('/var/www/carbon8/upload/'.$rowarq['nome']);	
						}
					}
				}
                                 
				$queueid = ""; 
				$mail->Debugoutput = function($debugstr, $level) {

					//printa tudo
					echo "\n<br>".$debugstr;

					//printa somente o queueid
					$pattern='/(queued\ as\ )(.*)/';
					if (preg_match($pattern, $debugstr, $match)){
						global $queueid;
						$queueid = trim($match[2]);
						echo($match[2]);
					}

				};
				
				if (!$mail->Send()) {
					
					echo "ERRO ao enviar email. (" . $mail->ErrorInfo. ")";
					$ret.= " ERRO ao enviar email COTAÇÃO. (" . $mail->ErrorInfo. ") ";
					$envioerro=1;
					// insere o log de erro
					$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$row["idnf"].",'nf','EMAILCOTACAO','".$ret."','ERRO',sysdate())";
					
					d::b()->query($sql);			
				
								
				} else {
					// GVT - 05/02/2020 - Verificação para impedir erro no insert em mailfila
					if(empty($row["idobjetosolipor"]) or empty($row["tipoobjetosolipor"])){
						$link = 'N';
						if(empty($row["idobjetosolipor"])){
							$idobjetoaux = 0;
						}else{
							$idobjetoaux = $row["idobjetosolipor"];
						}

						if(empty($row["tipoobjetosolipor"])){
							$tipoobjetoaux = 'sislaudo';
						}else{
							$tipoobjetoaux = $row["tipoobjetosolipor"];
						}
					}else{
						$idobjetoaux = $row["idobjetosolipor"];
						$tipoobjetoaux = $row["tipoobjetosolipor"];
						$link = "?_modulo=cotacao&_acao=u&idcotacao=".$row["idobjetosolipor"];
					}

					updateObjetojson($idobjetoaux, 'cotacao', $row["idnf"], 'nf', $envioid, 'mailfila');
					$sqlu = "update nf set envioemailorc = 'O' where idnf =".$row["idnf"];
					d::b()->query($sqlu) or die("erro ao alterar nf [".mysqli_error(d::b())."]");

					$_sql1 = "select * from pessoa where usuario = '".$row["alteradopor"]."' and status = 'ATIVO' limit 1";
					$_resu = d::b()->query($_sql1) or die($_sql1);
					$_qtd = mysql_num_rows($_resu);
					if($_qtd > 0){
						while($_r = mysql_fetch_assoc($_resu)){
							// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
							$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
									values (".$row["idempresa"].",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$idobjetoaux.",'cotacao',".$row["idnf"].",'nf',".$_r["idpessoa"].",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."','".base64_encode(serialize($aMsg))."',sysdate(),'".$_r["usuario"]."','".$_r["usuario"]."',sysdate())";

							d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
							// ---------------------------------------------------------------------
						}
					}else{
						while($_r = mysql_fetch_assoc($_resu)){
								
								// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$row["idempresa"].",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$idobjetoaux.",'cotacao',1029,".$row["idnf"].",'nf','".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."','".base64_encode(serialize($aMsg))."',sysdate(),'sislaudo','sislaudo',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
								// ---------------------------------------------------------------------

							}
					}

					echo "Email enviado com sucesso!";
					$ret .= "Email COTAÇÃO: ".$stremail[$i]." ";
					$enviook=1;
									
					// insere o log de sucesso após enviar o email
					$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$row["idnf"].",'nf','EMAILCOTACAO','".$ret."','SUCESSO',sysdate())";
				
					d::b()->query($sql);
					
				}
					// Apaga conteúdo do array para a próxima iteração do loop
					unset($aMsg);
                            }
				
			}//fim do loop de emails

			if($envioerro==1){
				//coloca na cotação como se o envio fosse ok e o email erro 
				$sqlu = "update nf set envioemailorc='E' where idnf =".$row["idnf"];
				d::b()->query($sqlu) or die("erro ao alterar nf [".mysqli_error(d::b())."]");
			
			}elseif($enviook==1){
				//informa no resultado que o email foi enviado OK
				
				
			}
			
                    }else{
				$ret= "ERRO ao tentar enviar o email COTAÇÃO. (Campo emailresult vazio!)";
				echo($ret);
	 
				$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
				values (".$row["idnf"].",'nf','EMAILCOTACAO','".$ret."','EMAILVAZIO',sysdate())";
		
				d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");
				
				//coloca na cotação como se o envio fosse ok e o email erro 
				$sqlu = "update nf set envioemailorc = 'E' where idnf =".$row["idnf"];		
				d::b()->query($sqlu) or die("erro ao alterar cotacaoforn [".mysqli_error(d::b())."]");
		}


}


re::dis()->hMSet('cron:enviaemailcotacao',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`,  `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailcotacao', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


?>
