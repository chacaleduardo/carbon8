<?//Enviar email para os clientes com a nota de produto em pdf e com o xml - HERMESP 02082013-
ini_set("display_errors","1");
error_reporting(E_ALL);

// GVT - 05/02/2020 - Usar outro PHPMailer para registrar os logs no servidor Hermes.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../inc/php/composer/vendor/autoload.php";
////////////////////////////////////////////////////////////////////////////////////

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
  //include_once("/var/www/carbon8/inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
  include_once("/var/www/carbon8/inc/nfe/sefaz4/libs/NFe/DanfeNFePHP.class.php");
}else{//se estiver sendo executado via requisicao http
  include_once("../inc/php/functions.php");
  //include_once("../inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
  require_once('../inc/nfe/sefaz4/libs/NFe/DanfeNFePHP.class.php');//biblioteco do NFE para gerar o pdf	
}


	$sql = "select   n.emaildadosnfe,n.emaildadosnfemat,n.tipoenvioemail,
				p.razaosocial,n.idnf,SUBSTRING(n.idnfe,4) as idnfe,n.nnfe,n.idnf,n.emaildanfe,n.emailboleto,n.emailxml,n.enviarastreador,n.rastreador
				,dma(prazo) as envio,obsenvio as previsao,t.nome as transportadora,t.url,p.idpessoa,n.comissao,n.tipoobjetosolipor,n.idobjetosolipor,n.alteradopor
			from pessoa p,nf n left join  pessoa t on (t.idpessoa = n.idtransportadora)
			where p.idpessoa = n.idpessoa
			and n.xmlret is not null
			and n.envionfe = 'CONCLUIDA' 
			and n.envioemail = 'Y'";
 


        $sql="select   -- n.emaildadosnfe
'marcelo' as emaildadosnfe,n.emaildadosnfemat,n.tipoenvioemail,
				p.razaosocial,n.idnf,SUBSTRING(n.idnfe,4) as idnfe,n.nnfe,n.idnf,n.emaildanfe,n.emailboleto,n.emailxml,n.enviarastreador,n.rastreador
				,dma(prazo) as envio,obsenvio as previsao,t.nome as transportadora,t.url,p.idpessoa,n.comissao,n.tipoobjetosolipor,n.idobjetosolipor,n.alteradopor
			from pessoa p,nf n left join  pessoa t on (t.idpessoa = n.idtransportadora)
			where p.idpessoa = n.idpessoa
			and n.xmlret is not null
			and n.envionfe = 'CONCLUIDA' 
			-- and n.envioemail = 'Y'
            and n.idnf=37636";
 
 
	$sqlres = d::b()->query($sql) or die("A Consulta do email do xml nfe falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
	
	// Gera identificador do envio do email.
	$envioid = geraIdEnvioEmail();
	
	while($row = mysqli_fetch_assoc($sqlres)){
		$sqlf = "update nf set envioemail = 'A' where idnf = ".$row['idnf'];
		$retf = d::b()->query($sqlf);

		if(!$retf){              
		    echo("Erro (1) ao atualizar status da nf \n<br>".mysqli_error(d::b())."\n<br>".$sqlf);
		    die();
		}
		$ret="";
		//Busca o xml e o idnfe no banco de dados para gerar os arquivos
		$sql1="select xmlret,SUBSTRING(idnfe,4) as idnfe,nnfe from nf where idnf=".$row['idnf'];
		$res1=d::b()->query($sql1) or die("erro ao buscar xml-sql:".$sql1);
		$row1=mysqli_fetch_assoc($res1);	
		
		if(empty($row1['xmlret']) or empty($row1['nnfe'])){
		    die("Falha ao buscar o XML é o número da notafiscal.");
		}
		
		if($row['emailxml']=="Y"){
		    //###INICIO### gera o arquivo xml
		    $fp1 = fopen('/var/www/laudo/tmp/nfe/'.$row1['idnfe'].'.xml', 'w');
		    fwrite($fp1,$row1['xmlret']);
		    fclose($fp1);
		    //###FIM### gera o arquivo xml	
		}
		if($row['emaildanfe']=="Y"){
		    //###INICIO### gera o arquivo PDF da nota
		    $docxml =$row1["xmlret"];
		    $danfe = new DanfeNFePHP($docxml, 'P', 'A4','/var/www/carbon8/inc/img/logolateral.jpg','I','');   
		    $id = $danfe->montaDANFE();
		    //$arquivo = $danfe->printDANFE($id.'.pdf','F');	
		    //$fp = fopen('/var/www/nfe/producao/enviadas/aprovadas/'.$id.'.pdf', 'w');
		    //fwrite($fp,$arquivo);//GRAVA O ARQUIVO NO DIRETORIO
		    //fclose($fp);
		    //###FIM### gera o arquivo PDF da nota	
		}
		if($row['emailxml']=="N" and $row['emaildanfe']=="N"){
				
		    $ret= " Não foram selecionados os anexos para enviar por email. ";
		    echo($ret);

		    $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
		    values (".$row["idnf"].",'nf','EMAILNFE',' ".$ret." ','Email Xml ou email Danfe não selecionado',sysdate())";

		    d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");

		    //coloca na cotação como se o envio fosse ok e o email erro 
		    $sqlu = "update nf set envioemail = 'E', logemail = concat(ifnull(logemail,''),'".$ret."') where idnf =".$row["idnf"];		
		    d::b()->query($sqlu) or die("erro ao alterar a nf [".mysqli_error(d::b())."] ".$sqlu);
		
		// se o campo com os emails estiverem devidamente preenchido	
		}elseif(($row["tipoenvioemail"]=="VENDA" and !empty($row["emaildadosnfe"]))
			or ($row["tipoenvioemail"]=="MATERIAL" and !empty($row["emaildadosnfemat"]))){
			
		    $sqlrep="SELECT c.participacaoprod,c.participacaoserv,p.idpessoa,p.nome,p.email
				FROM pessoacontato c,pessoa p
				where c.idpessoa = ".$row["idpessoa"]." 
				and p.email is not null
				and p.email !=''
				and p.idtipopessoa = 12
				and c.idcontato = p.idpessoa";
		    $resrep=d::b()->query($sqlrep) or die("Erro ao buscar representante do cliente sql=".$sqlrep);
		    $qtdrep=mysqli_num_rows($resrep);
		    if($qtdrep>0 and $row["comissao"]=="Y"){
			$rowrep= mysqli_fetch_assoc($resrep);
			$row["emaildadosnfe"].=",".$rowrep['email'];   

		    }
		    
		    if($row["tipoenvioemail"]=="VENDA"){
			$emaildados=$row["emaildadosnfe"].",vendas@inata.com.br";
			
			$vemailFrom="vendas@inata.com.br";
			$vnomeFrom="Vendas - INATA Produtos Biológicos";
			$vassunto="NFe -".$row['nnfe']."- INATA Produtos Biológicos";
			
		    }elseif($row["tipoenvioemail"]=="MATERIAL"){
			$emaildados=$row["emaildadosnfemat"].",material@laudolab.com.br";
			
			$vemailFrom="material@laudolab.com.br";
			$vnomeFrom="Material - Laudo Laboratório";
			$vassunto="Envio de Material(is) Solicitado(s)";
		    }
				
		    //da explode para pegar os emails
		    $stremail = array_unique(explode(",",$emaildados));
		    
		    $sqlf = "update nf set envioemail = 'O'  where idnf =".$row["idnf"];		     
		    $retf = d::b()->query($sqlf);
				
		    // roda no loop para enviar um email para cada endereço 
		    for($i=0;$i<count($stremail);$i++){
			$ret1="";

			echo $stremail[$i];

			//monta a mensagem
			//$message = retpar('textoemailnfp');
			$rodapeemailhtml = retpar("rodapedepartamentoadmprod");
			/*
			 * Monta versao HTML
			 */
			$messagehtm="<table style='font-size: 12px;'><tr><td>Prezado cliente <b>".$row["razaosocial"]."</b>, </td></tr></table><p><p>";
			$messagehtm=$messagehtm."<table style='font-size: 12px;'><tr><td>Segue(m) arquivo(s) referente(s) a NFe <b>".$row["nnfe"]."</b>.</td></tr></table><p>";
			$messagehtm=$messagehtm."<table style='font-size: 12px;'>";
			if(!empty($row['envio']) and !empty($row['previsao'])){
			$messagehtm = $messagehtm."<tr><td>Data envio: <b>".$row['envio']."</b> - Previsão Entrega: <b>".$row['previsao']."</b>.</td></tr>";
			}
			if(!empty($row['transportadora'])){
				$messagehtm = $messagehtm."<tr><td>Transportadora: <b>".$row['transportadora']."</b>.</td></tr>";
			}
			if($row['enviarastreador']=='Y' and !empty($row['rastreador']) and !empty($row['url'])){
			    $messagehtm = $messagehtm."<tr><td>Código de rastreamento: <b>".$row['rastreador']."</b><br>
Site para consulta: <a href='".$row['url']."'> ".$row['url']."</a> </td></tr>";
			}
			$messagehtm=$messagehtm."</table><p>";

			$sqlend="select c.cidade,c.uf,e.logradouro,e.cep,e.endereco,e.numero,
				e.complemento,e.bairro,e.obsentrega
				from endereco e,nfscidadesiaf c
				where e.idtipoendereco in (3,2)
				and c.codcidade = e.codcidade
				and e.status='ATIVO'
				and e.idpessoa=".$row['idpessoa']." order by e.idtipoendereco desc limit 1";
			$resend=d::b()->query($sqlend) or die("A Consulta do endereço falhou : " . mysqli_error(d::b()) . "<p>SQL:".$sqlend);
			$qtdend=mysqli_num_rows($resend);
			if($qtdend>0){
			    $rowend=mysqli_fetch_assoc($resend);
			    $messagehtm=$messagehtm."<table style='font-size: 12px;'>
								    <tr>
								    <td><b>ENDEREÇO DE ENTREGA</b></td>
								    </tr>
								    ";
			    $messagehtm=$messagehtm."	<tr>
								    <td>End.: <b>".$rowend['logradouro']." ".$rowend['endereco']."</b>  <b>".$rowend['complemento']."</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nº: <b>".$rowend['numero']."</b></td>
								    </tr>";
			    $messagehtm=$messagehtm."<tr><td>";
			    if(!empty($rowend['bairro'])){
				    $messagehtm=$messagehtm."Bairro: <b>".$rowend['bairro']."</b>";
			    }
			    if(!empty($rowend['cep'])){
				    $messagehtm=$messagehtm."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CEP.: <b>".$rowend['cep']."</b>";
			    }
			    $messagehtm=$messagehtm."</td></tr>";
			    $messagehtm=$messagehtm."<tr>
								    <td>Cidade: <b>".$rowend['cidade']."</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UF: <b>".$rowend['uf']."</b></td>
								    </tr>";
			    if(!empty($rowend['obsentrega'])){
				    $messagehtm=$messagehtm."<tr>
								    <td><font color='red'>OBS:</font><b> ".$rowend['obsentrega']."</b></td>
								    </tr>";
			    }
			    $messagehtm=$messagehtm."</table><p>";
			}					
					
			$messagehtm = $messagehtm."<table style='font-size: 12px;'>
									    <tr>
									    <td>Atenciosamente,</td>
									    </tr></table>";

			//$messagehtm = nl2br($messagehtm);
			$messagehtm = $messagehtm.$rodapeemailhtml;
			$messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$messagehtm."</body></html>"; 

			echo($messagehtm);

			/************************CABECALHO E TEXTO**************************/
			/*** FROM***/
			$emailFrom=$vemailFrom;
			$nomeFrom=$vnomeFrom;
			/***DESTINATARIO***/
			$emailDest=$stremail[$i];
			$emailDestNome=$row['cliente'];
			/***CCO***/
			//$emailDestCCO="nfs@inata.com.br";
			//$emailDestCCONome="NFs - INATA Produtos Biológicos";
			/*** ASSUNTO***/
			$assunto=$vassunto;		

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
			$mail->AddAddress("marcelobaxo@hotmail.com","marcelo - ".$i);
			// Copia
			//$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); 
			//email copia oculta
			//$mail->AddBCC($emailDestCCO, $emailDestCCONome);

			// Adicionar um anexo
			$queueid = ""; 
			$mail->Debugoutput = function($debugstr, $level) {

				//printa tudo
				echo "\n<br>".$debugstr;

				//printa somente o queueid
				$pattern='/(queued\ as\ )(.*)/';
				if (preg_match($pattern, $debugstr, $match)){
					global $queueid;
					$queueid = trim($match[2]);
					//echo($match[2]);
				}

			};

			if($row['emailxml']=="Y"){
			    $mail->addAttachment('/var/www/laudo/tmp/nfe/'.$row['idnfe'].'.xml');	
			}
			if($row['emaildanfe']=="Y"){
			    $mail->addAttachment('/var/www/laudo/tmp/nfe/'.$id.'.pdf');
			}
			if($row['emailboleto']=="Y"){
			    $sqlp ="select idcontapagar,parcela,parcelas
			    from contapagar 
			    where idobjeto =".$row['idnf']."
			    and boletopdf='Y'
			    and tipoobjeto = 'nf'";
			    $qrp = d::b()->query($sqlp) or die("Erro ao buscar parcelas da nota:".mysqli_error(d::b()));
			    while ($rowp = mysqli_fetch_array($qrp)){
				 $mail->addAttachment("/var/www/laudo/tmp/nfe/Boleto_NF_".$row['nnfe']."_Parc_".$rowp['parcela']."_de_".$rowp['parcelas'].".pdf");
			    }
			}

			
			$sqlc="select 
				l.idlote,l.partidaext,dma(l.vencimento) as vencimento,ni.cert,REPLACE(concat(convert(lpad(replace(l.partida,p.codprodserv,''),'3', '0')using latin1),'-',l.exercicio), '/', '.') as npart,p.codprodserv
				from lotecons i,nfitem ni,lote l,prodserv p 
				where l.idlote = i.idlote
				and p.assinatura ='S'
				and p.idprodserv = l.idprodserv
				and ni.cert ='Y'
				and i.tipoobjeto='nfitem'                
				and i.qtdd>0
				and i.idobjeto = ni.idnfitem
				and ni.idnf=".$row['idnf']."
				and (l.idassinadopor is not null 
						or 	
						exists (select 1 from carrimbo c
									where c.idobjeto =  l.idlote 
								and c.tipoobjeto in ('lotealmoxarifado','lotecq','lotediagnostico','lotediagnosticoautogenas','loteproducao','loteretem')
								)
					)";
			$resc=d::b()->query($sqlc) or die("Erro ao selecionar certificado sql=".$sqlc);
			while($rowc=mysqli_fetch_assoc($resc)){
			    $mail->addAttachment("/var/www/carbon8/upload/nfe/Certificado_".$rowc['codprodserv']."-part".$rowc['npart'].".pdf");
			}
						
			if (!$mail->Send()) {

			    echo " ERRO ao enviar email. (" . $mail->ErrorInfo. ") ";
			    $ret.= " ERRO ao enviar email. (" .$mail->ErrorInfo. ") ";
			    $ret1= " ERRO ao enviar email. (" . $mail->ErrorInfo. ") ";
			    $envioerro=1;
			    // insere o log de erro
			    $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
			    values (".$row["idnf"].",'nf','EMAILNFE','".$ret1."','ERRO',sysdate())";

			    d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");			

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
						$link = "?_modulo=".$row["tipoobjetosolipor"]."&_acao=u&id".$row["tipoobjetosolipor"]."=".$row["idobjetosolipor"];
					}

				$_sql1 = "select * from pessoa where usuario = '".$row["alteradopor"]."' and status = 'ATIVO' limit 1";
					$_resu = d::b()->query($_sql1) or die($_sql1);
					$_qtd = mysql_num_rows($_resu);
						if($_qtd > 0){
							while($_r = mysql_fetch_assoc($_resu)){
								
								// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idpessoa,idenvio,enviadode,link,criadoem,criadopor,alteradopor,alteradoem) 
										values (1,'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$idobjetoaux.",'".$tipoobjetoaux."',".$_r["idpessoa"].",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."',sysdate(),'".$_r["usuario"]."','".$_r["usuario"]."',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela filaemail ".$_sql);
								// ---------------------------------------------------------------------
							}
						}else{
							while($_r = mysql_fetch_assoc($_resu)){
								
								// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idpessoa,idenvio,enviadode,link,criadoem,criadopor,alteradopor,alteradoem) 
										values (1,'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$idobjetoaux.",'".$tipoobjetoaux."',1029,'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."',sysdate(),'sislaudo','sislaudo',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela filaemail ".$_sql);
								// ---------------------------------------------------------------------

							}
						}
						// ---------------------------------------------------------------------
				
			    echo " Email enviado com sucesso! ";
			    $ret .= "  ".$stremail[$i]." ";
			    $ret1= "  ".$stremail[$i]." ";
			    $enviook=1;

			    // insere o log de sucesso após enviar o email
			    $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
			    values (".$row["idnf"].",'nf','EMAILNFE','".$ret1."','SUCESSO',sysdate())";

			    d::b()->query($sql) or die("Erro ao inserir Log de SUCESSO [".mysqli_error(d::b())."]");						
			}					
		    }//fim do loop de emails	
				
		    //coloca na cotação como se o envio fosse ok e o email erro 
		    $sqlu = "update nf set envioemail = 'O', logemail = concat(ifnull(logemail,''),' ".$ret." ') where idnf =".$row["idnf"];		
		    d::b()->query($sqlu) or die("erro ao alterar a nf [".mysqli_error(d::b())."] ".$sqlu);

		}else{
		    $ret.= " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
		    $ret1= " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
		    echo($ret);

		    $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
		    values (".$row["idnf"].",'nf','EMAILNFE','".$ret1."','EMAILVAZIO VER CONFIGURACAO',sysdate())";

		    d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");

		    //coloca na cotação como se o envio fosse ok e o email erro 
		    $sqlu = "update nf set envioemail = 'E', logemail = concat(ifnull(logemail,''),' ".$ret." ') where idnf =".$row["idnf"];		
		    d::b()->query($sqlu) or die("erro ao alterar a nf [".mysqli_error(d::b())."] ".$sqlu);
		}
	}
?>
	
