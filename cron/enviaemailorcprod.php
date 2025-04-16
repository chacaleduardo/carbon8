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
  include_once("/var/www/carbon8/inc/php/functions.php");
  include_once("/var/www/carbon8/inc/php/composer/vendor/autoload.php");
  //include_once("/var/www/carbon8/inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}else{//se estiver sendo executado via requisicao http
  include_once("../inc/php/functions.php");
  include_once("../inc/php/composer/vendor/autoload.php");
  //include_once("../inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}

$grupo = rstr(8);

re::dis()->hMSet('cron:enviaemailorcproc',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailorcproc', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysql_error(d::b())."<br>".$sqli);




/*
 * MAF: PARA TESTAR: rodar no browser com parametro GET. O Email NAO sera enviado.
 */
$testeinterno = $_GET["testeinterno"];


$envioerro=0;
$enviook=0;
/*
  * busca os emails que estão aguardando envio
 */
$sql ="SELECT n.idnf,p.idpessoa,p.nome as cliente,n.emailorc as emailorc,n.comissao,n.alteradopor,n.tipoobjetosolipor,n.idobjetosolipor,n.idempresa
		FROM nf n,pessoa p
		where  p.idpessoa = n.idpessoa
		and n.tiponf ='V'
		and n.envioemailorc = 'Y'";

//die($sql);

$sqlres = d::b()->query($sql) or die("A Consulta dos emails a enviar falhou : " . mysql_error() . "<p>SQL: $sql");

	
	while ($row = mysqli_fetch_array($sqlres)){
		// Gera identificador do envio do email.
		$envioid = geraIdEnvioEmail();
		$ret="";
        	
        $sqlf = "update nf set envioemailorc = 'A' where idnf = ".$row['idnf'];
        $retf = d::b()->query($sqlf);

        if(!$retf){              
            echo("Erro ao atualizar status da nf \n<br>".mysql_error()."\n<br>".$sqlf);
            die();
        }
                
	if(!empty($row["emailorc"])){
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
		    $row["emailorc"].=",".$rowrep['email'];   
		    
		}
		
		$sqlemail = "SELECT v.email_original as email,v.tipoenvio
						FROM empresaemailobjeto e 
						JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
						WHERE e.tipoenvio = 'ORCPROD'
							AND e.tipoobjeto = 'nf'
							AND e.idobjeto = {$row["idnf"]}
							AND e.idempresa = {$row["idempresa"]}
							AND v.status = 'ATIVO'
						ORDER BY e.idempresaemailobjeto desc limit 1";
		$resemail=d::b()->query($sqlemail) or die("Erro ao buscar emails da empresa sql=".$sqlemail);
		$qtdemail=mysqli_num_rows($resemail);
		if($qtdemail>0){
			$rowemail = mysqli_fetch_assoc($resemail);
			$row["emailorc"].=",".$rowemail['email'];
			$tipoenvio = $rowemail['tipoenvio'];
			$dominio = $rowemail['email'];
		}else{
			$sqlempresaemail = "SELECT ev.email_original AS dominio,ev.tipoenvio
								FROM empresaemails em 
								JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
								WHERE em.tipoenvio = 'ORCPROD'
								AND em.idempresa = {$row["idempresa"]}
								AND ev.status = 'ATIVO'
								ORDER BY em.idempresaemails asc limit 1";
			$resempresaemail=d::b()->query($sqlempresaemail) or die("Erro ao buscar email da empresa sql=".$sqlempresaemail);
			$rowempresaemail = mysqli_fetch_assoc($resempresaemail);
			$dominio = $rowempresaemail['dominio'];
			$tipoenvio = $rowempresaemail['tipoenvio'];
			$row["emailorc"].=",".$rowempresaemail['dominio'];
		}
		
		if(!empty($row["idempresa"])){
			$idempresa = $row["idempresa"];
		}else{
			$idempresa = 0;
		}
		
		// GVT - 30/04/2020 - Alterado o rodapé dos emails. Busca o rodapé cadastrado no módulo empresa. Função se encontra em /inc/php/functions.php
		$rodapeemailhtml = imagemtipoemailempresa("ORCPROD",$idempresa,$dominio);
		// Caso a função imagemtipoemailempresa retorne FALSE
		if(!$rodapeemailhtml){
			$rodapeemailhtml = "";
		}

		//da explode para pegar os emails
		$stremail = array_unique(explode(",",$row["emailorc"]));
		
		if(empty($dominio)){
			$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
			values (".$row["idnf"].",'nf','EMAILORCAMENTOPRODUTO','REMETENTE VAZIO','ERRO',sysdate())";

			d::b()->query($sql) or die("erro ao inserir Log de erro [".mysql_error(d::b())."]");
			die("O remetente está vazio");
		}

		// for($i=0;$i<count($stremail);$i++){
			$ret1="";
			 
			// echo $stremail[$i];	
			
			$tabelares="Prezado  Cliente ".$row['cliente'].",<br>";
			$tabelares=$tabelares."<p><p><br>";
			
			$tabelares=$tabelares."Segue arquivo em anexo com o orçamento solicitado.<br><p>";
			$tabelares=$tabelares."À disposição.<br><p>";
			
			$tabelares=$tabelares."<p><p><br>";
			$tabelares=$tabelares."<p>";
			$sqlusr="select nome from pessoa where usuario = '".$row["alteradopor"]."' and idtipopessoa  =1  and status = 'ATIVO'";
			$resusr=d::b()->query($sqlusr);
			$rowusr=mysqli_fetch_assoc($resusr);
			if(!empty($rowusr["nome"])){
				$tabelares=$tabelares."Att. <br>";
				$tabelares=$tabelares.$rowusr["nome"];
			}
			//monta a mensagem
			
			//$rodapeemailhtml = retpar("rodapevendasbasefor");
			//$rodapeemailtxt = retpar("rodapeemailresultadostxt");

			$message = $tabelares;

			/*
			 * Monta versao HTML
			 */
			$messagehtm = $message;
			//$messagehtm = nl2br($messagehtm);
			$messagehtm = $messagehtm.$rodapeemailhtml;
			$messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$messagehtm."</body></html>"; 

			echo($messagehtm);
			//die($messagehtm);

			//$crlf = "\n";

			/************************CABECALHO E TEXTO**************************/
			/*** FROM***/
						
			$sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'ORCPROD' AND idempresa =".$idempresa." ORDER BY idempresarodapeemail asc limit 1";
			$resrodapeemail=d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=".$sqlrodapeemail);
			$rowrodapeemail=mysqli_fetch_assoc($resrodapeemail);
			
			/*
			$sqldominio = "SELECT ev.email_original as dominio FROM empresaemails em join emailvirtualconf ev 
				on (em.idemailvirtualconf = ev.idemailvirtualconf) WHERE em.tipoenvio = 'ORCPROD' AND em.idempresa =".$idempresa;
			$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar emails da empresa sql=".$sqldominio);
			$rowdominio=mysqli_fetch_assoc($resdominio);
			$dominio = $rowdominio["dominio"];
			*/
			
			$emailFrom=$dominio;
			$vnomeFrom=$rowrodapeemail["nomeremetente"];
			//$emailFrom="vendas@inata.com.br";
			//$nomeFrom="Vendas - INATA Produtos Biológicos";
			
			/***DESTINATARIO***/
			$emailDest=$stremail[$i];
			$emailDestNome=$row['cliente'];
			
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

			if ($infcomplementar) {
			   $rowrodapeemail["assunto"] = str_replace("_info_", $row['idnf'], $rowrodapeemail["assunto"]);
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
				$aMsg["anexos"][0] = '/var/www/laudo/tmp/nfe/Orcamento_prod_'.$row['idnf'].'.pdf';
				
				$mail = new PHPMailer(true);
				$mail->SMTPDebug=2;
				$mail->IsSMTP();
				$mail->SMTPAuth  = false;
				//$mail->Charset   = 'utf8_decode()';
				$mail->SMTPAutoTLS = false;
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
				if($tipoenvio == "CC"){
					foreach($stremail as $email){
						$mail->AddCC($email, $row['emailorc']);
						$mail->addAddress($email,$emailDestNome);
					}
				}elseif($tipoenvio == "CCO"){
					foreach($stremail as $email){
						$mail->AddBCC($email, $row['emailorc']);
						$mail->addAddress($email,$emailDestNome);
					}
				}else{
					foreach($stremail as $email){
						$mail->AddAddress($email,$emailDestNome);
					}
				}

				$mail->AddCustomHeader("X-MAIL-MOD:".strtoupper(explode(".",basename($_SERVER["SCRIPT_FILENAME"]))[0]));
				// Copia
				//$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); 
				//email copia oculta
				if(!$mail->AddBCC($emailDestCCO, $emailDestCCONome)){
					$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailorcproc', 'adicionarDestinatario', '$emailDestCCO, $emailDestCCONome', '$mail->ErrorInsfo', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

					d::b()->query($sqli) or die("erro ao inserir log: ".mysql_error(d::b())."<br>".$sqli);
				}

				// Adicionar um anexo
				if(file_exists('/var/www/laudo/tmp/nfe/Orcamento_prod_'.$row['idnf'].'.pdf')){
					$mail->AddAttachment('/var/www/laudo/tmp/nfe/Orcamento_prod_'.$row['idnf'].'.pdf');					
				} else {
					$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, info, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailorcproc', 'anexoArquivo', '/var/www/laudo/tmp/nfe/Orcamento_prod_".$row['idnf'].".pdf', 'Arquivo Não Encontrado.', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

					d::b()->query($sqli) or die("erro ao inserir log: ".mysql_error(d::b())."<br>".$sqli);
					exit;
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

					echo " ERRO ao enviar email. (" . $result->getMessage(). ") ";
					$ret.= " ERRO ao enviar email. (" . $result->getMessage(). ") ";
					$ret1= " ERRO ao enviar email. (" . $result->getMessage(). ") ";
					$envioerro=1;
					// insere o log de erro
					$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$row["idnf"].",'nf','EMAILORCPROD','".$ret1."','ERRO',sysdate())";

					d::b()->query($sql);			

				} else {				

					// GVT - 05/02/2020 - Verificação para impedir erro no insert em mailfila					
					$link = "?_modulo=pedido&_acao=u&idnf=".$row["idnf"];
					
					/////////////////////////////////////////////////////////////////////////
					$_sql1 = "select * from pessoa where usuario = '".$row["alteradopor"]."' and status = 'ATIVO' limit 1";
					$_resu = d::b()->query($_sql1) or die($_sql1);
					$_qtd = mysql_num_rows($_resu);
						if($_qtd > 0){
							while($_r = mysql_fetch_assoc($_resu)){
								
								// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$idempresa.",'".$emailFrom."','".$row['emailorc']."','".$queueid."','EM FILA',".$row["idnf"].",'orcamentoprod',".$row["idnf"].",'nf',".$_r["idpessoa"].",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."','".base64_encode(serialize($aMsg))."',sysdate(),'".$_r["usuario"]."','".$_r["usuario"]."',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
								// ---------------------------------------------------------------------
							}
						}else{
							while($_r = mysql_fetch_assoc($_resu)){
								
								// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$idempresa.",'".$emailFrom."','".$row['emailorc']."','".$queueid."','EM FILA',".$row["idnf"].",'orcamentoprod',".$row["idnf"].",'nf',1029,'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."','".base64_encode(serialize($aMsg))."',sysdate(),'sislaudo','sislaudo',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
								// ---------------------------------------------------------------------

							}
						}
						// ---------------------------------------------------------------------
						
					// Apaga conteúdo do array para a próxima iteração do loop
					unset($aMsg);
				
					echo " Email enviado com sucesso! ";
					$ret .= "  ".$row['emailorc']." ";
					$ret1= "  ".$row['emailorc']." ";
					$enviook=1;

					// insere o log de sucesso após enviar o email
					$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$row["idnf"].",'nf','EMAILORCPROD','".$ret1."','SUCESSO',sysdate())";

					d::b()->query($sql);						
				}	
			}//$testeweb
		// }//fim for email

		//coloca na cotação como se o envio fosse ok e o email erro
		$sqlu = "update nf set envioemailorc = 'O', logemail = concat(ifnull(logemail,''),' ".$ret." ') where idnf =".$row["idnf"];
		d::b()->query($sqlu) or die("erro ao alterar a pedido [".mysql_error()."] ".$sqlu);
						
	}else{

		$ret.= " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
		$ret1= " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
		echo($ret);
			
		$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$row["idnf"].",'nf','EMAILORCPROD','".$ret1."','EMAILVAZIO',sysdate())";
			
		d::b()->query($sql) or die("erro ao inserir Log de erro [".mysql_error()."]");
			
		//coloca na cotação como se o envio fosse ok e o email erro
		$sqlu = "update nf set envioemailorc = 'E', logemail = concat(ifnull(logemail,''),' ".$ret." ') where idnf =".$row["idnf"];
		d::b()->query($sqlu) or die("erro ao alterar a pedido [".mysql_error()."] ".$sqlu);
			
	}		
}


re::dis()->hMSet('cron:enviaemailorcproc',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailorcproc', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysql_error(d::b())."<br>".$sqli);

?>
