<?
require_once("../inc/php/validaacesso.php");
require "../inc/php/composer/vendor/autoload.php";
require_once "../inc/php/composer/vendor/dompdf/dompdf/src/Autoloader.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


	$sqlemailnenviado = "SELECT 
							distinct(destinatario) as destinatario,idobjeto
						FROM
							mailfila a
						WHERE
							1
								AND (criadoem BETWEEN '2020-08-05 00:00' AND '2020-08-07 23:59:59')
								AND tipoobjeto = 'comunicacaoext' and idmailfila not in (53555,
53566,
53579,
53685,
53697,
53688)";
	$resemailnenviado = d::b()->query($sqlemailnenviado) or die("Consulta dos emails oficiais para reenvio. SQL = ".$sqlemailnenviado);

	while($r1 = mysqli_fetch_assoc($resemailnenviado)){
		$emailFrom = "oficial@laudolab.com.br";
		$nomeFrom = "Oficial - Laudo Laboratório";
		$sql = "select nome from pessoa where email = '".$r1["destinatario"]."' limit 1";
		$res = d::b()->query($sql) or die("Falha na Consulta dos nomes. SQL = ".$sql);
		$row = mysqli_fetch_assoc($res);
		$emailDest = $r1["destinatario"];
		$emailDestNome = $row["nome"];
		
		$messagehtm = "<div><p>
			Prezado Senhor (a),<br><br>

			Nos dias 05 e 06 de agosto de 2020, nosso sistema de envio de resultados gerou arquivos PDF desconfigurados, impossibilitando a leitura dos mesmos.</p>

			<p>Assim que fomos comunicados do problema, realizamos a correção necessária no sistema.<p>

			<p>Pedimos desculpa pelo ocorrido e comunicamos que todo resultado que você recebeu desconfigurado, será enviado em um novo disparo de e-mail, agora com o PDF configurado da forma correta.</p>

			<p>Agradecemos a compreensão e reforçamos o nosso compromisso em oferecer um serviço de excelência para nossos clientes e demais parceiros.<p>

			<p>Qualquer dúvida, favor entrar em contato.<br><br>

			Tenha um ótimo final de semana!
		</p></div>";
		$rodapeemailhtml = imagemtipoemailempresa("RESULTADOOFICIAL","1","oficial@laudolab.com.br");
		//die($messagehtm.$rodapeemailhtml);
		//CONFIGURACOES E ENVIO
		$mail = new PHPMailer(true); //true habilita exceptions
		$mail->SMTPDebug=2; //maf120619: Recuperar o diálogo com o servidor IMAP: https://github.com/PHPMailer/PHPMailer/wiki/SMTP-Debugging
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
			$mail->Subject  = "Errata - Laudo Laboratório";
			$mail->Body  = $messagehtm.$rodapeemailhtml;
			//email destino
			$mail->AddAddress($emailDest,$emailDestNome);

			
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

			$mail->Send();
	}
?>