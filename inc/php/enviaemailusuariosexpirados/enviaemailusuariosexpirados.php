<?
require_once("../functions.php");
require "../composer/vendor/autoload.php";
require_once "../composer/vendor/dompdf/dompdf/src/Autoloader.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$hostatual = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"];

if($_GET["teste"] != "Y"){
	$sql = "CREATE TEMPORARY TABLE tmp_pessoalog SELECT * FROM pessoalog where criadoem > date_sub(now(),interval 30 day);";
		echo $sql;
		echo "<br>";
		$res = d::b()->query($sql) or die("Falha na Consulta dos nomes. SQL = ".$sql);
		echo "Tabela temporária criada com sucesso.";
	
	
		$sqlemailnenviado = "SELECT 
								idpessoa,usuario,email,nome
							FROM
								pessoa p
							WHERE
								p.idtipopessoa = 3 AND p.idempresa = 1
								AND (p.usuario != '' OR p.usuario IS NOT NULL)
								and (p.email != '' and p.email != '.' )
								AND status IN ('ATIVO')
								AND NOT EXISTS( SELECT 
													1
												FROM
													tmp_pessoalog pl
												WHERE
													pl.idpessoa = p.idpessoa)
								AND NOT EXISTS( SELECT 
													1
												FROM
													log l
												WHERE
													l.idobjeto = p.idpessoa
													AND l.tipoobjeto = 'pessoa'
													AND l.tipolog = 'EMAILEXPIRACAO'
													AND l.status = 'SUCESSO');";
		$resemailnenviado = d::b()->query($sqlemailnenviado) or die("Consulta dos usuarios para envio de email = ".$sqlemailnenviado);
}else{
	$sqlemailnenviado = "SELECT 
							idpessoa,usuario,email,nome
						FROM
							pessoa p
						WHERE
							p.idpessoa = ".$_GET["idpessoa"];
	$resemailnenviado = d::b()->query($sqlemailnenviado) or die("Consulta dos usuarios para envio de email = ".$sqlemailnenviado);
}


    while($r1 = mysqli_fetch_assoc($resemailnenviado)){
        $emailFrom = "sac@laudolab.com.br";
        $nomeFrom = "Sistema Sislaudo";
        $emailDest = $r1["email"];
        $emailDestNome =$r1["nome"];

        //Cria uma data limite para a utilização do email de alteração de senha e monta um token encriptado
		$date = new DateTime();
		$date->modify('+15 day');
		$datalimite = $date->format('Y-m-d H:i:s');		
		$strToken = enc($r1["usuario"]."#".$datalimite);
		$urlRecuperacaoSenha = $hostatual."/ajax/recuperarSenhaEmail.php?passo=2&token=".$strToken;

		require_once("../composer/vendor/autoload.php");
		
		$mail = new PHPMailer(true);
		$mail->SMTPDebug=2;
		//$mail->SMTPDebug = 3;                               // Enable verbose debug output

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = '192.168.0.15';// Specify main and backup SMTP servers
		$mail->SMTPAuth = false;                               // Enable SMTP authentication
		$mail->SMTPAutoTLS = false;
		$mail->CharSet = "UTF-8";
		//$mail->Username = 'admin_laudolab';                 // SMTP username
		//$mail->Password = '37383738';                           // SMTP password
		//$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = '587';                                    // TCP port to connect to

		$mail->setFrom('sac@laudolab.com.br', 'Sistema Sislaudo');
		$mail->AddAddress($r1["email"]);     // Add a recipient

		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'Renovar senha para acessar o Sistema Sislaudo';

		$arquivoHtml = file_get_contents("enviaemailusuariosexpirados.html");
		$arquivoHtml = str_replace("{HREF}", $urlRecuperacaoSenha, $arquivoHtml);
		$arquivoHtml = str_replace("{HREFSERVER}", $_SERVER["SERVER_NAME"], $arquivoHtml);

		$mail->Body = $arquivoHtml;
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		$queueid = "";
		// GVT - 08/07/2020 - Adicionado array para armazenar assunto, mensagem e anexos do email.
		//					- O array é armazenado na tabela mailfila para ser utilizado posteriormente
		//					- para mostrar o que foi enviado no email e reenvio do mesmo.
		$aMsg=array();
		$aMsg["assunto"] = 'Renovar senha para acessar o Sistema Sislaudo';
		$aMsg["mensagem"] = $arquivoHtml;
		$mail->Debugoutput = function($debugstr, $level) {

			//printa tudo
			//echo "\n<br>".$debugstr;

			//printa somente o queueid
			$pattern='/(queued\ as\ )(.*)/';
			if (preg_match($pattern, $debugstr, $match)){
				global $queueid;
				$queueid=trim($match[2]);
				$GLOBALS["queueid"]=trim($queueid);
				//echo $queueid;
			}

		};

		//MAF: silenciar erro: o phpmailer gera um EXCEPTION 500 em caso de falha de SMTP
		try{
			$resenvio = $mail->send();

            $sql = "INSERT into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$r1["idpessoa"].",'pessoa','EMAILEXPIRACAO','".$r1["email"]."','SUCESSO',sysdate())";

			d::b()->query($sql);
		} catch (phpmailerException $e) {
			$sql = "INSERT into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$r1["idpessoa"].",'pessoa','EMAILEXPIRACAO','".$r1["email"]."','ERRO',sysdate())";

			d::b()->query($sql);
			//echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
			//echo $e->getMessage(); //Boring error messages from anything else!
		}    
        sleep(2);
    }
?>