<?
require_once("../inc/php/functions.php");

//Impedir mais de 10 tentativas repetidas dentro de 24 horas
if(alarmeCheck('emailsenha',1440)>10){
        cbSetPostHeader("0","erro");
        die("Tente mais tarde, ou entre em contato com o nosso suporte!");
}

cbSetPostHeader("0","alert");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$hostatual = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["SERVER_NAME"];

if($_GET["passo"]=="1"){
	if(!empty($_GET["usuarioemail"])){
		$_modulo = $_GET["modulo"];
		$_idobjeto = $_GET["idobjeto"];
		enviarEmailUsuario($_modulo,$_idobjeto);
	}
}if($_GET["passo"]=="2"){
	if(!empty($_GET["token"])){
		mostraRecuperacaoSenha();
	}
}else{
	cbSetPostHeader("0","alert");
	//Impedir mais de 10 tentativas repetidas dentro de 24 horas
    alarmeSet('Y','emailsenha', $_GET["usuarioemail"], "Solicitante: ".$_SESSION["SESSAO"]["USUARIO"]);
	die("Recuperação de email incorreta.");
}

/**
 * Primeiro passo: Enviar email para o usuário conforme usuário/email informado
 */
function enviarEmailUsuario($_modulo,$_idobjeto){
	global $hostatual;

	$db=d::b();
	
	$sqlUser = "select idpessoa, nome, email, usuario, idempresa
				from pessoa 
				where status='ATIVO' 
					and (usuario='".$db->real_escape_string($_GET["usuarioemail"])."' or email='".$db->real_escape_string($_GET["usuarioemail"])."')
					and usuario > ''";
	
	$res = $db->query($sqlUser) or die("Erro ao recuperar email: ".$db->error);
	
	$r = $res->fetch_array(MYSQLI_ASSOC);
	
	//Verifica se foi encontrado email para o usuário
	if($res->num_rows > 1 and strpos($_GET["usuarioemail"],'@')){
		cbSetPostHeader("0","alert");
		echo "O email informado está configurado para 2 pessoas em nosso sistema.\nUtilize o seu <b>Usuário</b> para recuperar a sua senha";
		//Impedir mais de 10 tentativas repetidas dentro de 24 horas
        alarmeSet('Y','emailsenha', $_GET["usuarioemail"], "Solicitante: ".$_SESSION["SESSAO"]["USUARIO"]);
		die;
	}elseif(!empty($r["email"]) and $res->num_rows > 0){

		//Cria uma data limite para a utilização do email de alteração de senha e monta um token encriptado
		$date = new DateTime();
		$date->modify('+1 day');
		$datalimite = $date->format('Y-m-d H:i:s');		
		$strToken = enc($r["usuario"]."#".$datalimite);
		$urlRecuperacaoSenha = $hostatual."/ajax/recuperarSenhaEmail.php?passo=2&token=".$strToken;

		require_once("../inc/php/composer/vendor/autoload.php");
		
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
		$mail->AddAddress($r["email"]);     // Add a recipient

		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'Criar nova senha para acessar o Sistema Sislaudo';

		$arquivoHtml = file_get_contents("recuperarSenhaEmailTemplate.html");
		$arquivoHtml = str_replace("{NOME}", $r["nome"], $arquivoHtml);
		$arquivoHtml = str_replace("{HREF}", $urlRecuperacaoSenha, $arquivoHtml);

		$mail->Body = $arquivoHtml;
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		$queueid = "";
		// GVT - 08/07/2020 - Adicionado array para armazenar assunto, mensagem e anexos do email.
		//					- O array é armazenado na tabela mailfila para ser utilizado posteriormente
		//					- para mostrar o que foi enviado no email e reenvio do mesmo.
		$aMsg=array();
		$aMsg["assunto"] = 'Criar nova senha para acessar o Sistema Sislaudo';
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
		} catch (phpmailerException $e) {
			//echo $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
			//echo $e->getMessage(); //Boring error messages from anything else!
		}

		if($resenvio){
			//die($queueid." aqui");
			cbSetPostHeader("1","alert");
			echo "<strong>Email enviado: Verifique sua caixa de emails!</strong>\n\nSugerimos que verifique também as caixas de\nSpam e Lixo Eletrônico.";
			// Gera identificador do envio do email.
			$envioid = geraIdEnvioEmail();
			// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
			$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
					values (".$r["idempresa"].",'sac@laudolab.com.br','".$r["email"]."','".$GLOBALS["queueid"]."','EM FILA',".$r["idpessoa"].",'recuperasenha',".$r["idpessoa"].",'".$_GET["modulo"]."',".$r["idpessoa"].",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','N','".base64_encode(serialize($aMsg))."',sysdate(),'".$r["usuario"]."','".$r["usuario"]."',sysdate())";

			d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
			// ---------------------------------------------------------------------

			die;
		}else{
			cbSetPostHeader("0","alert");
			echo "Mensagem não enviada!";
			echo "\nPor gentileza, informe a mensagem abaixo ao Administrador:";
			echo "\n\n" . $mail->ErrorInfo;
			die;
		}
		// Apaga conteúdo do array para a próxima iteração do loop
					unset($aMsg);
	}else{
		cbSetPostHeader("0","alert");
		//Impedir mais de 10 tentativas repetidas dentro de 24 horas
        alarmeSet('Y','emailsenha', $_GET["usuarioemail"], "Solicitante: ".$_SESSION["SESSAO"]["USUARIO"]);
		echo "O usuário/email não foi encontrado ou não está ativo.\nEntre em contato conosco através do email\nsac@laudolab.com.br";
		die;
	}
}

/**
 * Mostrar campos para recuperação de senha
 * @todo: validar a função de login
 */
function mostraRecuperacaoSenha(){
	global $hostatual;

	$strtoken = des($_GET["token"]);
	cbSetPostHeader("0","alert");
	
	if($strtoken){
		$arrToken = explode("#",$strtoken);
		
		if(!empty($arrToken[0]) and !empty($arrToken[1])){
			//echo $arrToken[0]."\n<br>".$arrToken[1];
			
			//Verifica se o token ainda é valido
			$datalimite = new DateTime($arrToken[1]);
			$datahoje = new DateTime();
			
			//Gera nova senha temporária
			$senhaTemp = $datahoje->format("Y-m-d H:i:s");
			$senhaTemp = enc($senhaTemp);
			
			if($datahoje < $datalimite){
				$db=d::b();
				$sqlSenha = "update pessoa set senha='".senha_hash($senhaTemp)."', tipoauth='bsp', expiraem=null where status='ATIVO' and usuario='".$db->real_escape_string($arrToken[0])."' limit 1";

				$res = $db->query($sqlSenha) or die("Erro ao criar nova senha temporária");//.$db->error);

				if($db->affected_rows==1){
					//Efetua login com a senha temporária gerada
					logincarbon($arrToken[0], $senhaTemp);
					
					//Sobe variável para ser verificada no módulo de alteração de senha
					$_SESSION["SESSAO"]["FORCAALTERACAOSENHA"]=$senhaTemp;

					//Redireciona o usuário para a tela de alteração de senha
					header("Location: ".$hostatual."/?_modulo=alterasenha");
				}else{
					echo "Erro criando senha temporária:\nNenhum registro encontrado.";die;
				}

			}else{
			    //Impedir mais de 10 tentativas repetidas dentro de 24 horas
                alarmeSet('Y','emailsenha', $_GET["usuarioemail"], "Solicitante: ".$_SESSION["SESSAO"]["USUARIO"]);
				echo "O email de recuperação de senha expirou.\nClique novamente na opção 'Esqueci minha senha'\nna tela de Login;";die;
			}
			
		}else{
		    //Impedir mais de 10 tentativas repetidas dentro de 24 horas
            alarmeSet('Y','emailsenha', $_GET["usuarioemail"], "Solicitante: ".$_SESSION["SESSAO"]["USUARIO"]);
			echo 'Erro ao decifrar Token!\nEntre em contato conosco através do email\nsac@laudolab.com.br';die;
		}
		
	}else{
	    //Impedir mais de 10 tentativas repetidas dentro de 24 horas
        alarmeSet('Y','emailsenha', $_GET["usuarioemail"], "Solicitante: ".$_SESSION["SESSAO"]["USUARIO"]);
		echo 'Token inválido!\nEntre em contato conosco através do email\nsac@laudolab.com.br';die;
	}
}

?>