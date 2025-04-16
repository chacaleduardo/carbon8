<? //Enviar email para os clientes com a nota de produto em pdf e com o xml - HERMESP 02082013-
ini_set("display_errors", "1");
error_reporting(E_ALL);

include_once("Mail.php");
include_once("Mail/mime.php");

if (defined('STDIN')) { //se estiver sendo executao em linhade comando
	include_once("/var/www/carbon8/inc/php/functions.php");
	include_once("/var/www/carbon8/inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
} else { //se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
	include_once("../inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}

$grupo = rstr(8);

re::dis()->hMSet('cron:enviaemailnfs', ['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '" . $grupo . "','cron', 'enviaemailnfs', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysql_error(d::b())."<br>".$sqli);

$sql = "SELECT n.nnfe,n.numerorps,dma(n.emissao) as emissao,p.razaosocial,n.idnotafiscal,n.emailnfe,n.idempresa
					,n.enviadetalhenfe,n.enviadanfnfe,n.emailboleto,n.emaildsimplesnac,convert( lpad(n.nnfe, '8', '0') using latin1) as numeronfe,p.idpessoa,left(p.cpfcnpj,8) as ncnpj,p.idpessoa,n.alteradopor
					FROM notafiscal n,pessoa p
					where p.idpessoa = n.idpessoa
					and (n.enviadetalhenfe = 'Y' or n.enviadanfnfe = 'Y' or emailboleto='Y')
					and n.emailnfe is not null
					and n.enviaemailnfe='Y'
					and n.status = 'CONCLUIDO'";
$sqlres = d::b()->query($sql) or die("A Consulta do email do xml nfe falhou : " . mysql_error() . "<p>SQL: $sql");



while ($row = mysqli_fetch_assoc($sqlres)) {
	// Gera identificador do envio do email.
	$envioid = geraIdEnvioEmail();

	$sqlf = "update notafiscal set enviaemailnfe = 'A' where idnotafiscal = " . $row['idnotafiscal'];
	$retf = d::b()->query($sqlf);

	try {
		$ret = "";

		if (!empty($row["emailnfe"])) {

			$sqldominio = "SELECT v.email_original as email, v.tipoenvio
								FROM empresaemailobjeto e 
								JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
								WHERE e.tipoenvio = 'NFS'
									AND e.tipoobjeto = 'nfs'
									AND e.idobjeto = {$row["idnotafiscal"]}
									AND e.idempresa = {$row["idempresa"]}
									AND v.status = 'ATIVO'
								ORDER BY e.idempresaemailobjeto desc limit 1";
			$resdominio = d::b()->query($sqldominio) or die("Erro ao buscar dominio de Venda da empresa sql=" . $sqldominio);
			$qtdemail = mysqli_num_rows($resdominio);
			if ($qtdemail > 0) {
				$rowdominio = mysqli_fetch_assoc($resdominio);
				$dominio = $rowdominio["email"];
				$tipoenvio = $rowdominio["tipoenvio"];
				$row["emailnfe"] = $row["emailnfe"] . "," . $dominio;
			} else {
				$sqlempresaemail = "SELECT ev.email_original AS dominio,ev.tipoenvio
												FROM empresaemails em 
												JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
												WHERE em.tipoenvio = 'NFS'
												AND em.idempresa = {$row["idempresa"]}
												AND ev.status = 'ATIVO'
												ORDER BY em.idempresaemails asc limit 1";
				$resempresaemail = d::b()->query($sqlempresaemail) or die("Erro ao buscar email da empresa sql=" . $sqlempresaemail);
				$rowempresaemail = mysqli_fetch_assoc($resempresaemail);
				$dominio = $rowempresaemail['dominio'];
				$tipoenvio = $rowempresaemail['tipoenvio'];
				$row["emailnfe"] = $row["emailnfe"] . "," . $dominio;
			}

			echo "Emails = " .$row["emailnfe"];

			$sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'NFS' AND idempresa =" . $row["idempresa"] . " ORDER BY idempresarodapeemail asc limit 1";
			$resrodapeemail = d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=" . $sqlrodapeemail);
			$rowrodapeemail = mysqli_fetch_assoc($resrodapeemail);

			$vemailFrom = $dominio;
			$vnomeFrom = $rowrodapeemail["nomeremetente"];

			$infcomplementar = (strpos($rowrodapeemail["assunto"], "_info_") !== 0);

			if ($infcomplementar) {
				$rowrodapeemail["assunto"] = str_replace("_info_", $row["numeronfe"], $rowrodapeemail["assunto"]);
			}

			$vassunto = $rowrodapeemail["assunto"];

			if (empty($rowrodapeemail["comcopia"])) {
				$auxCC = $dominio;
			} else {
				$auxCC = $rowrodapeemail["comcopia"];
			}
			$emailDestCCO = $auxCC;
			$emailDestCCONome = $rowrodapeemail["nomecc"];

			if (!empty($row["idempresa"])) {
				$idempresa = $row["idempresa"];
			} else {
				$idempresa = 0;
			}

			// GVT - 30/04/2020 - Alterado o rodapé dos emails. Busca o rodapé cadastrado no módulo empresa. Função se encontra em /inc/php/functions.php
			$rodapeemailhtml = imagemtipoemailempresa("NFS", $idempresa, $dominio);
			// Caso a função imagemtipoemailempresa retorne FALSE
			if (!$rodapeemailhtml) {
				$rodapeemailhtml = "";
			}

			//da explode para pegar os emails
			$stremail = explode(",", $row["emailnfe"]);
			$emailArray = $row["emailnfe"];
			echo "Emails Array = " .$stremail.'<br>';
			
			if (empty($dominio)) {
				$sql = "INSERT INTO log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
						values (" . $row["idnotafiscal"] . ",'nfs','EMAILNFS','REMETENTE VAZIO','ERRO',sysdate())";

				d::b()->query($sql) or die("erro ao inserir Log de erro [" . mysql_error(d::b()) . "]");
				die("O remetente está vazio");
			}

			// roda no loop para enviar um email para cada endereço 
			// for($i=0;$i<count($stremail);$i++){
			$ret1 = "";

			// if(!empty($stremail[$i])) {
			// echo $stremail[$i];

			//monta a mensagem
			$message = retpar('textoemailnfp');


			//$rodapeemailhtml = retpar("rodapedepartamentoadministrati");
			//$rodapeemailtxt = retpar("rodapeemailcotacaotxt");

			/*
			* Monta versao de Texto
			*/
			$messagetxt = $message;
			$messagetxt = str_replace("Xcliente", $row["razaosocial"], $messagetxt);
			$messagetxt = str_replace("Xnnfe", "<b>" . $row["numeronfe"] . "</b>", $messagetxt);
			$messagetxt = str_replace("NFe", "NFSe", $messagetxt);
			$messagetxt = str_replace("xrastreador", " ", $messagetxt);
			//$messagetxt = $messagetxt . $rodapeemailtxt;
			$messagetxt = str_replace("xtransportadora", " ", $messagetxt);
			$messagetxt = str_replace("xenvio", " ", $messagetxt);
			/*
			* Monta versao HTML
			*/
			$messagehtm = $message;
			$messagehtm = str_replace("Xcliente", $row["razaosocial"], $messagehtm);
			$messagehtm = str_replace("Xnnfe", "<b>" . $row["numeronfe"] . "</b>", $messagehtm);
			$messagehtm = str_replace("NFe", "NFSe", $messagehtm);
			$messagehtm = str_replace("xrastreador", " ", $messagehtm);
			$messagehtm = str_replace("xtransportadora", " ", $messagehtm);
			$messagehtm = str_replace("xenvio", " ", $messagehtm);

			$messagehtm = $messagehtm . "<p>";
			$messagehtm = $messagehtm . "<b>Favor confirmar o recebimento deste email. Obrigado!</b>";
			$messagehtm = $messagehtm . "<p>";
			$messagehtm = $messagehtm . "Atenciosamente,<br>";

			$messagehtm = nl2br($messagehtm);
			$messagehtm = $messagehtm . $rodapeemailhtml;
			$messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>" . $messagehtm . "</body></html>";

			echo ($messagehtm.'<br>');
			
			//die($messagehtm);                                       					

			//**********************************************************************************

			/************************CABECALHO E TEXTO**************************/
			/*** FROM***/
			$emailFrom = $vemailFrom;
			$nomeFrom = $vnomeFrom;
			/***DESTINATARIO***/
			$emailDest = trim($stremail[$i]);
			$emailDestNome = $row["razaosocial"];
			/***CCO***/
			//$emailDestCCO="nfs@inata.com.br";
			//$emailDestCCONome="NFs - INATA Produtos Biológicos";
			/*** ASSUNTO***/
			$assunto = $vassunto;

			// GVT - 08/07/2020 - Adicionado array para armazenar assunto, mensagem e anexos do email.
			//					- O array é armazenado na tabela mailfila para ser utilizado posteriormente
			//					- para mostrar o que foi enviado no email e reenvio do mesmo.
			$aMsg = array();
			$aMsg["assunto"] = $assunto;
			$aMsg["mensagem"] = $messagehtm;

			/******************************CONFIGURACOES E ENVIO*****************************************/

			$mail = new PHPMailer(true);
			$mail->IsSMTP();
			$mail->SMTPDebug = 2;
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
			$emails = explode(",", $row["emailnfe"]);
			if ($tipoenvio == "CC") {
				foreach ($emails as $email) {
					$mail->AddAddress(trim($email), $emailDestNome);
					$mail->AddCC(trim($email), $emailDestNome);
				}
			} elseif ($tipoenvio == "CCO") {
				foreach ($emails as $email) {
					$mail->AddAddress(trim($email), $emailDestNome);
					$mail->AddBCC(trim($email), $emailDestNome);
				}
			} else {
				foreach ($emails as $email) {
					$mail->AddAddress(trim($email), $emailDestNome);
				}
			}
			// $mail->AddAddress($emailDest,$emailDestNome);

			$mail->AddCustomHeader("X-MAIL-MOD:" . strtoupper(explode(".", basename($_SERVER["SCRIPT_FILENAME"]))[0]));
			// Copia
			//$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); 
			//email copia oculta
			//$mail->AddBCC($emailDestCCO, $emailDestCCONome);
			$queueid = "";
			$mail->Debugoutput = function ($debugstr, $level) {

				//printa tudo
				echo "\n<br>" . $debugstr.'\n<br>';

				//printa somente o queueid
				$pattern = '/(queued\ as\ )(.*)/';
				if (preg_match($pattern, $debugstr, $match)) {
					global $queueid;
					$queueid = trim($match[2]);
					echo ($match[2]);
				}
			};

			$countanexos = 0;
			if ($row['enviadanfnfe'] == "Y") {
				$aMsg["anexos"][$countanexos] = '/var/www/laudo/tmp/nfe/NFSe_' . $row['nnfe'] . '.pdf';
				$mail->addAttachment('/var/www/laudo/tmp/nfe/NFSe_' . $row['nnfe'] . '.pdf');
				$countanexos++;
			}

			if ($row['enviadetalhenfe'] == "Y") {
				$aMsg["anexos"][$countanexos] = '/var/www/laudo/tmp/nfe/Detalhamento_' . $row['nnfe'] . '.pdf';
				$mail->addAttachment('/var/www/laudo/tmp/nfe/Detalhamento_' . $row['nnfe'] . '.pdf');
				$countanexos++;
			}

			if ($row['emailboleto'] == "Y") {
				$sqlp = "select idcontapagar,parcela,parcelas
								from contapagar 
								where idobjeto =" . $row['idnotafiscal'] . "
								and boletopdf='Y'
								and tipoobjeto = 'notafiscal'";
				$qrp = d::b()->query($sqlp);
				while ($rowp = mysqli_fetch_array($qrp)) {
					$aMsg["anexos"][$countanexos] = "/var/www/laudo/tmp/nfe/Boleto_NF_" . $row['nnfe'] . "_Parc_" . $rowp['parcela'] . "_de_" . $rowp['parcelas'] . ".pdf";
					$mail->addAttachment("/var/www/laudo/tmp/nfe/Boleto_NF_" . $row['nnfe'] . "_Parc_" . $rowp['parcela'] . "_de_" . $rowp['parcelas'] . ".pdf");
					$countanexos++;
				}
			}

			if ($row['emaildsimplesnac'] == "Y") {
				$aMsg["anexos"][$countanexos] = '/var/www/carbon8/inc/docs/nsimples' . $row['ncnpj'] . '.pdf';
				$mail->addAttachment('/var/www/carbon8/inc/docs/nsimples' . $row['ncnpj'] . '.pdf');
				$countanexos++;
			}
			//**********************************************************************************                                        

			print_r("Anexos: " . $aMsg["anexos"]);

			if (!$mail->Send()) {

				echo " ERRO ao enviar email. (" . $mail->ErrorInfo . ") \n<br>";
				$ret .= " ERRO ao enviar email. (" . $mail->ErrorInfo . ") ";
				$ret1 = " ERRO ao enviar email. (" . $mail->ErrorInfo . ") ";
				$envioerro = 1;

				//coloca na notafiscal como se o envio fosse ok e o email erro 
				$sqlu = "update notafiscal set enviaemailnfe = 'E', logemailnfe = concat(ifnull(logemailnfe,''),' " . $ret . " ') where idnotafiscal =" . $row["idnotafiscal"];
				d::b()->query($sqlu) or die("erro ao alterar a nf [" . mysql_error() . "] " . $sqlu);

				// insere o log de erro
				$sql = "INSERT INTO log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
								values (" . $row["idnotafiscal"] . ",'notafiscal','EMAILNFSE','" . $ret1 . "','ERRO',sysdate())";

				d::b()->query($sql) or die("erro ao inserir Log de erro [" . mysql_error() . "]");
			} else {
				// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020

				$_sq = "select * from pessoa where usuario = '" . $row["alteradopor"] . "' and status = 'ATIVO' limit 1";
				$_rsq = d::b()->query($_sq) or die($_sq);
				$_qtdsq = mysql_num_rows($_rsq);
				$_rsqq = mysql_fetch_assoc($_rsq);
				if ($_qtdsq > 0) {
					$_sqlq = "INSERT INTO mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
								VALUES (" . $row["idempresa"] . ",'" . $emailFrom . "','" . $emailArray . "','" . $queueid . "','EM FILA'," . $row["idnotafiscal"] . ",'nfs'," . $row["idnotafiscal"] . ",'notafiscal'," . $_rsqq["idpessoa"] . ",'" . $envioid . "','" . $_SERVER["SCRIPT_NAME"] . "','?_modulo=nfs&_acao=u&idnotafiscal=" . $row["idnotafiscal"] . "','" . base64_encode(serialize($aMsg)) . "',sysdate(),'" . $_rsqq["usuario"] . "','" . $_rsqq["usuario"] . "',sysdate())";

					d::b()->query($_sqlq) or die("erro ao inserir email na tabela mailfila " . $_sqlq);
				} else {
					$_sqlq = "INSERT INTO mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
									VALUES (" . $row["idempresa"] . ",'" . $emailFrom . "','" . $emailArray . "','" . $queueid . "','EM FILA'," . $row["idnotafiscal"] . ",'nfs'," . $row["idnotafiscal"] . ",'notafiscal',1029,'" . $envioid . "','" . $_SERVER["SCRIPT_NAME"] . "','?_modulo=nfs&_acao=u&idnotafiscal=" . $row["idnotafiscal"] . "','" . base64_encode(serialize($aMsg)) . "',sysdate(),'sislaudo','sislaudo',sysdate())";

					d::b()->query($_sqlq) or die("erro ao inserir email na tabela mailfila " . $_sqlq);
				}

				// ---------------------------------------------------------------------
				echo "SQL = " . $_sq.'\n<br>';
				echo "SQL = " . $_sqlq.'\n<br>';
				echo "SQL = " . $_qtdsq.'\n<br>';
				echo " Email enviado com sucesso! \n<br>";
				$ret .= "  " . $stremail[$i] . " ";
				$ret1 = "  " . $stremail[$i] . " ";
				$enviook = 1;

				// insere o log de sucesso após enviar o email
				$sql = "INSERT INTO log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
								values (" . $row["idnotafiscal"] . ",'notafiscal','EMAILNFSE','" . $ret1 . "','SUCESSO',sysdate())";

				d::b()->query($sql) or die("Erro ao inserir Log de SUCESSO [" . mysql_error() . "]");
			}
			// Apaga conteúdo do array para a próxima iteração do loop
			unset($aMsg);
			// }//fim do loop de emails
			// }


			if ($envioerro != 1) {
				//coloca na notafiscal como se o envio fosse ok e o email erro 
				$sqlu = "update notafiscal set enviaemailnfe = 'O', logemailnfe = concat(ifnull(logemailnfe,''),' " . $ret . " ') where idnotafiscal =" . $row["idnotafiscal"];
				d::b()->query($sqlu) or die("erro ao alterar a nf [" . mysql_error() . "] " . $sqlu);
			} else {

				//coloca na notafiscal como se o envio fosse ok e o email erro 
				$sqlu = "update notafiscal set enviaemailnfe = 'E', logemailnfe = concat(ifnull(logemailnfe,''),' " . $ret . " ') where idnotafiscal =" . $row["idnotafiscal"];
				d::b()->query($sqlu) or die("erro ao alterar a nf [" . mysql_error() . "] " . $sqlu);
			}

			echo "Envio Erro = " . $envioerro.'\n<br>';
			echo "SQL = " . $sqlu.'\n<br>';
			echo "Update com Sucesso! ";
		} else {
			$ret .= " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
			$ret1 = " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
			echo ($ret.'\n<br>');

			$sql = "INSERT INTO log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
						values (" . $row["idnotafiscal"] . ",'notafiscal','EMAILNFSE','" . $ret1 . "','EMAILVAZIO',sysdate())";

			d::b()->query($sql) or die("erro ao inserir Log de erro [" . mysql_error() . "]");

			//coloca na notafiscal como se o envio fosse ok e o email erro 
			$sqlu = "update notafiscal set enviaemailnfe = 'E', logemailnfe = concat(ifnull(logemailnfe,''),' " . $ret . " ') where idnotafiscal =" . $row["idnotafiscal"];
			d::b()->query($sqlu) or die("erro ao alterar a notafiscal [" . mysql_error() . "] " . $sqlu);
		}
	} catch (Exception $e) {
		$sqlf1 = "update notafiscal set enviaemailnfe = 'E' where idnotafiscal = " . $row['idnotafiscal'];
		$retf1 = d::b()->query($sqlf1);
	}
}

re::dis()->hMSet('cron:enviaemailnfs', ['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '" . $grupo . "','cron', 'enviaemailnfs', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysql_error(d::b())."<br>".$sqli);
