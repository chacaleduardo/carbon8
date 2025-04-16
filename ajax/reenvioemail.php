<?
session_start();
$sessionid = session_id();//PEGA A SESS?O

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

if(!empty($_GET["idmailfila"]) and !empty($_GET["usuario"]) and !empty($_GET["idusuario"])){
	$idmailfila = $_GET["idmailfila"];
	$usuario = $_GET["usuario"];
	$idpessoa = $_GET["idusuario"];
	
	// Consulta o registro do email passado na requisi��o GET
	$sql = "SELECT * FROM mailfila where idmailfila = ".$idmailfila;
	$res = d::b()->query($sql) or die("Erro ao buscar email para reenvio: ".mysqli_error());
	$row = mysqli_fetch_array($res);
	
	// Verifica se existe conte�do a ser reenviado n�o est� vazio
	if(empty($row["conteudoemail"])){
		
		die("0");
	}else{
		
		// Verifica se existe um destinat�rio para reenviar o email
		if(empty(trim($row["destinatario"]))){
			
			die("-1");
		}else{
			switch($row["tipoobjeto"]){
				case "cotacao": $tipoobjeto = "COTACAO"; $tipoobjeto1 = "COTACAO"; $flag=1; break;
				case "cotacaoaprovada": $tipoobjeto = "COTACAOAPROVADA"; $tipoobjeto1 = "COTACAOAPROVADA"; $flag=1; break;
				case "detalhamento": $tipoobjeto = "DETALHAMENTO"; $tipoobjeto1 = "DETALHAMENTO"; $flag=1; break;
				case "nfp": $tipoobjeto = "NFP"; $tipoobjeto1 = "NFP"; $flag=1; break;
				case "nfs": $tipoobjeto = "NFS"; $tipoobjeto1 = "NFS"; $flag=1; break;
				case "orcamentoprod": $tipoobjeto = "ORCPROD"; $tipoobjeto1 = "ORCPROD"; $flag=1; break;
				case "orcamentoserv": $tipoobjeto = "ORCSERV"; $tipoobjeto1 = "ORCSERV"; $flag=1; break;
				case "recuperasenha": $tipoobjeto = "RECUPERASENHA"; $tipoobjeto1 = "RECUPERASENHA"; $flag=1; break;
				case "comunicacaoext": $tipoobjeto = "RESULTADOOFICIAL"; $tipoobjeto1 = "RESULTADOOFICIAL"; $flag=1; break;
				default:
					// Caso n�o encontre o tipoobjeto, busque o nomefantasia da empresa
					$sqlempresa="select nomefantasia from empresa where idempresa = ".$row["idempresa"];
					$resempresa = d::b()->query($sqlempresa) or die("Erro ao buscar empresa para reenvio: ".mysqli_error());
					$rowempresa = mysqli_fetch_array($resempresa);
					$tipoobjeto = $rowempresa["nomefantasia"];
					$tipoobjeto1 = $rowempresa["nomefantasia"];
					$flag=0;
					break;
			}
			
			if($flag){
				$sqlnomeremetente = "SELECT nomeremetente FROM empresarodapeemail WHERE tipoenvio = '".$tipoobjeto."' AND idempresa = ".$row["idempresa"]." ORDER BY idempresarodapeemail asc limit 1";
				$resnomeremetente = d::b()->query($sqlnomeremetente) or die("Erro ao buscar email para reenvio: ".mysqli_error());
				$rownomeremetente = mysqli_fetch_array($resnomeremetente);
				$tipoobjeto = $rownomeremetente["nomeremetente"];
			}
			
			$rc = unserialize(base64_decode($row["conteudoemail"]));

			$assunto = $rc["assunto"];
			$messagehtm = $rc["mensagem"];
			
			$remetente = trim($row["remetente"]);
			$nomeremetente = $tipoobjeto;
			$destinatario = trim($row["destinatario"]);
			
			$mail = new PHPMailer(true);
			$mail->SMTPDebug=2;
			$mail->IsSMTP();
			$mail->SMTPAuth  = false;
			$mail->SMTPAutoTLS = false;
			$mail->CharSet = "UTF-8";
			$mail->Host  = '192.168.0.15';
			$mail->Port  = '587';
			$mail->From  = $remetente;
			$mail->FromName  = $nomeremetente;
			$mail->IsHTML(true);
			$mail->Subject  = $assunto;
			$mail->Body  = $messagehtm;
			$mail->addAddress($destinatario);
			
			if(array_key_exists("anexos",$rc)){
				//echo "<br>";
				foreach ($rc["anexos"] as $key => $value) {
					//echo "{$key} => {$value} ";
					//echo "<br>";
					$mail->AddAttachment($value);
				}
			}
			
			$queueid = ""; 
			$mail->Debugoutput = function($debugstr, $level) {
				//echo "\n<br>".$debugstr;
				$pattern='/(queued\ as\ )(.*)/';
				if (preg_match($pattern, $debugstr, $match)){
					global $queueid;
					$queueid = trim($match[2]);
				}
			};
												
			if (!$mail->Send()) {
				
				//echo "ERRO ao enviar email. (" . $mail->ErrorInfo. ")";
				$ret.= " ERRO ao enviar email (" . $mail->ErrorInfo. ") ";
				
				$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
				values (".$row["idobjeto"].",'".$row["tipoobjeto"]."','".$tipoobjeto1."','".$ret."','ERRO',sysdate())";
				
				d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");
			} else {
				$envioid = geraIdEnvioEmail();
				// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
				$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,log,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
						values (".$row["idempresa"].",'".$remetente."','".$destinatario."','".$queueid."','EM FILA',".$row["idobjeto"].",'".$row["tipoobjeto"]."',".$row["idsubtipoobjeto"].",'".$row["subtipoobjeto"]."',".$idpessoa.",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$row["link"]."','REENVIO DE EMAIL','".$row["conteudoemail"]."',sysdate(),'".$usuario."','".$usuario."',sysdate())";

				d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
				// ---------------------------------------------------------------------

				if(in_array($row["tipoobjeto"],["cotacao","cotacaoaprovada"])){
					$qr = "INSERT into objetojson(idempresa,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idobjetoext,tipoobjetoext,jobjeto,versaoobjeto,criadopor,criadoem,alteradopor,alteradoem)
						(select o.idempresa,o.idobjeto,o.tipoobjeto,o.idsubtipoobjeto,o.subtipoobjeto,'".$envioid."',o.tipoobjetoext,o.jobjeto,o.versaoobjeto,'sislaudo',now(),'sislaudo',now()
						from objetojson o
						where o.idsubtipoobjeto = ".$row["idsubtipoobjeto"]." 
						and o.subtipoobjeto = 'nf' 
						and o.tipoobjetoext = 'mailfila' 
						and o.tipoobjeto = '".$row["tipoobjeto"]."' 
						and o.idobjetoext = '".$row["idenvio"]."' 
						and o.idobjeto = ".$row["idobjeto"].")";
					d::b()->query($qr);
				}

				// insere o log de sucesso ap�s enviar o email
				$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
				values (".$row["idobjeto"].",'".$row["tipoobjeto"]."','".$tipoobjeto1."','".$ret."','SUCESSO',sysdate())";

				d::b()->query($sql) or die("Erro ao inserir Log de SUCESSO [".mysqli_error(d::b())."]");

				echo "1";
			}
		} // else -if(empty(trim($row["destinatario"])))
	} // else - if(empty($row["conteudoemail"]))
} // if(!empty($_GET["idmailfila"]))
	else{
		if(!empty($_GET["idmailfilareferencia"]) and !empty($_GET["destinatario"]) and !empty($_GET["usuario"]) and !empty($_GET["idusuario"])){
			$idmailfila = $_GET["idmailfilareferencia"];
			$destinatario = trim($_GET["destinatario"]);
			$usuario = $_GET["usuario"];
			$idpessoa = $_GET["idusuario"];
			
			// Consulta o registro do email passado na requisi��o GET
			$sql = "SELECT * FROM mailfila where idmailfila = ".$idmailfila;
			$res = d::b()->query($sql) or die("Erro ao buscar email para reenvio: ".mysqli_error());
			$row = mysqli_fetch_array($res);
			
			// Verifica se existe conte�do a ser reenviado n�o est� vazio
			if(empty($row["conteudoemail"])){
				
				die("0");
			}else{
				
				// Verifica se existe um destinat�rio para reenviar o email
				if(empty($destinatario)){
					
					die("-1");
				}else{
					switch($row["tipoobjeto"]){
						case "cotacao": $tipoobjeto = "COTACAO"; $tipoobjeto1 = "COTACAO"; $flag=1; break;
						case "cotacaoaprovada": $tipoobjeto = "COTACAOAPROVADA"; $tipoobjeto1 = "COTACAOAPROVADA"; $flag=1; break;
						case "detalhamento": $tipoobjeto = "DETALHAMENTO"; $tipoobjeto1 = "DETALHAMENTO"; $flag=1; break;
						case "nfp": $tipoobjeto = "NFP"; $tipoobjeto1 = "NFP"; $flag=1; break;
						case "nfs": $tipoobjeto = "NFS"; $tipoobjeto1 = "NFS"; $flag=1; break;
						case "orcamentoprod": $tipoobjeto = "ORCPROD"; $tipoobjeto1 = "ORCPROD"; $flag=1; break;
						case "orcamentoserv": $tipoobjeto = "ORCSERV"; $tipoobjeto1 = "ORCSERV"; $flag=1; break;
						case "recuperasenha": $tipoobjeto = "RECUPERASENHA"; $tipoobjeto1 = "RECUPERASENHA"; $flag=1; break;
						case "comunicacaoext": $tipoobjeto = "RESULTADOOFICIAL"; $tipoobjeto1 = "RESULTADOOFICIAL"; $flag=1; break;
						default:
							// Caso n�o encontre o tipoobjeto, busque o nomefantasia da empresa
							$sqlempresa="select nomefantasia from empresa where idempresa = ".$row["idempresa"];
							$resempresa = d::b()->query($sqlempresa) or die("Erro ao buscar empresa para reenvio: ".mysqli_error());
							$rowempresa = mysqli_fetch_array($resempresa);
							$tipoobjeto = $rowempresa["nomefantasia"];
							$tipoobjeto1 = $rowempresa["nomefantasia"];
							$flag=0;
							break;
					}
					
					if($flag){
						$sqlnomeremetente = "SELECT nomeremetente FROM empresarodapeemail WHERE tipoenvio = '".$tipoobjeto."' AND idempresa = ".$row["idempresa"]." ORDER BY idempresarodapeemail asc limit 1";
						$resnomeremetente = d::b()->query($sqlnomeremetente) or die("Erro ao buscar email para reenvio: ".mysqli_error());
						$rownomeremetente = mysqli_fetch_array($resnomeremetente);
						$tipoobjeto = $rownomeremetente["nomeremetente"];
					}
					
					$rc = unserialize(base64_decode($row["conteudoemail"]));

					$assunto = $rc["assunto"];
					$messagehtm = $rc["mensagem"];
					
					$remetente = trim($row["remetente"]);
					$nomeremetente = $tipoobjeto;
					
					$mail = new PHPMailer(true);
					$mail->SMTPDebug=2;
					$mail->IsSMTP();
					$mail->SMTPAuth  = false;
					$mail->SMTPAutoTLS = false;
					$mail->CharSet = "UTF-8";
					$mail->Host  = '192.168.0.15';
					$mail->Port  = '587';
					$mail->From  = $remetente;
					$mail->FromName  = $nomeremetente;
					$mail->IsHTML(true);
					$mail->Subject  = $assunto;
					$mail->Body  = $messagehtm;
					$mail->addAddress($destinatario);
					
					if(array_key_exists("anexos",$rc)){
						//echo "<br>";
						foreach ($rc["anexos"] as $key => $value) {
							//echo "{$key} => {$value} ";
							//echo "<br>";
							$mail->AddAttachment($value);
						}
					}
					
					$queueid = ""; 
					$mail->Debugoutput = function($debugstr, $level) {
						//echo "\n<br>".$debugstr;
						$pattern='/(queued\ as\ )(.*)/';
						if (preg_match($pattern, $debugstr, $match)){
							global $queueid;
							$queueid = trim($match[2]);
						}
					};
														
					if (!$mail->Send()) {
						
						//echo "ERRO ao enviar email. (" . $mail->ErrorInfo. ")";
						$ret.= " ERRO ao enviar email (" . $mail->ErrorInfo. ") ";
						
						$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
						values (".$row["idobjeto"].",'".$row["tipoobjeto"]."','".$tipoobjeto1."','".$ret."','ERRO',sysdate())";
						
						d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");
					} else {
						$envioid = $row["idenvio"];
						// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
						$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,log,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
								values (".$row["idempresa"].",'".$remetente."','".$destinatario."','".$queueid."','EM FILA',".$row["idobjeto"].",'".$row["tipoobjeto"]."',".$row["idsubtipoobjeto"].",'".$row["subtipoobjeto"]."',".$idpessoa.",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$row["link"]."','REENVIO DE EMAIL','".$row["conteudoemail"]."',sysdate(),'".$usuario."','".$usuario."',sysdate())";

						d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
						// ---------------------------------------------------------------------

						// insere o log de sucesso ap�s enviar o email
						$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
						values (".$row["idobjeto"].",'".$row["tipoobjeto"]."','".$tipoobjeto1."','".$ret."','SUCESSO',sysdate())";

						d::b()->query($sql) or die("Erro ao inserir Log de SUCESSO [".mysqli_error(d::b())."]");

						echo "1";
					}
				} // else -if(empty(trim($row["destinatario"])))
			} // else - if(empty($row["conteudoemail"]))
		}else{
			echo "2";
		}
	}
?>