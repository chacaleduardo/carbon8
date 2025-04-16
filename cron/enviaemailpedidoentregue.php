<?
session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
  include_once("/var/www/carbon8/inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}else{//se estiver sendo executado via requisicao http
  include_once("../inc/php/functions.php");
  include_once("../inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}

$grupo = rstr(8);

re::dis()->hMSet('cron:enviaemailrepresentante',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailrepresentante', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


/*
 * MAF: PARA TESTAR: rodar no browser com parametro GET. O Email NAO sera enviado.
 */
$testeinterno = $_GET["testeinterno"];


    $sql = "SELECT n.idnf,p.nome,p1.nome as cliente,p.email as emailrep,n.emailrepresentante,n.idvendedor,n.alteradopor,n.idempresa
                FROM nf n,pessoa p, pessoa p1
                where p.idpessoa = n.idvendedor
				and p1.idpessoa = n.idpessoa
                and n.emailrepresentante ='Y'
                and n.status IN ('CONCLUIDO')"; 
	
    

    $sqlres = d::b()->query($sql) or die("A Consulta do email do xml nfe falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
	
	
	
    while($row = mysqli_fetch_assoc($sqlres)){
		// Gera identificador do envio do email.
		$envioid = geraIdEnvioEmail();
        $ret="";

        if(!empty($row["emailrep"])){
			
			$sqldominio = "SELECT v.email_original as email,v.tipoenvio
							FROM empresaemailobjeto e
							JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
							WHERE e.tipoenvio = 'PEDIDOENTREGUE'
								AND e.tipoobjeto = 'nf'
								AND e.idobjeto = {$row["idnf"]}
								AND e.idempresa = {$row["idempresa"]}
								AND v.status = 'ATIVO'
							ORDER BY e.idempresaemailobjeto desc limit 1";
					$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar dominio de Venda da empresa sql=".$sqldominio);
					$qtdemail=mysqli_num_rows($resdominio);
					if($qtdemail>0){
						$rowdominio = mysqli_fetch_assoc($resdominio);
						$tipoenvio = $rowdominio['tipoenvio'];
						$dominio = $rowdominio["email"];
						$row["emailrep"]=$row["emailrep"].",".$dominio;
					}else{
						$sqlempresaemail = "SELECT ev.email_original AS dominio,ev.tipoenvio
											FROM empresaemails em
											JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
											WHERE em.tipoenvio = 'PEDIDOENTREGUE'
											AND em.idempresa = {$row["idempresa"]}
											AND ev.status = 'ATIVO'
											ORDER BY em.idempresaemails asc limit 1";
						$resempresaemail=d::b()->query($sqlempresaemail) or die("Erro ao buscar email da empresa sql=".$sqlempresaemail);
						$rowempresaemail = mysqli_fetch_assoc($resempresaemail);
						$dominio = $rowempresaemail['dominio'];
						$tipoenvio = $rowempresaemail['tipoenvio'];
						$row["emailrep"]=$row["emailrep"].",".$dominio;
					}
					
					$sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'PEDIDOENTREGUE' AND idempresa =".$row["idempresa"]." ORDER BY idempresarodapeemail asc limit 1";
					$resrodapeemail=d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=".$sqlrodapeemail);
					$rowrodapeemail=mysqli_fetch_assoc($resrodapeemail);
				
				$vemailFrom=$dominio;
				$vnomeFrom=$rowrodapeemail["nomeremetente"];
				
				$infcomplementar1 = ( strpos( $rowrodapeemail["assunto"], "_info_" ) !== 0 );
				$infcomplementar2 = ( strpos( $rowrodapeemail["assunto"], "_info1_" ) !== 0 );
				
				if ($infcomplementar1) {
				   $rowrodapeemail["assunto"] = str_replace("_info_", $row["idnf"], $rowrodapeemail["assunto"]);
				}
				if ($infcomplementar2) {
				   $rowrodapeemail["assunto"] = str_replace("_info1_", $row["nome"], $rowrodapeemail["assunto"]);
				}
				
				$vassunto=$rowrodapeemail["assunto"];
				
				if(empty($rowrodapeemail["comcopia"])){
					$auxCC = $dominio;
				}else{
					$auxCC = $rowrodapeemail["comcopia"];
				}
				$emailDestCCO=$auxCC;
				$emailDestCCONome=$rowrodapeemail["nomecc"];
				
				// GVT - 30/04/2020 - Alterado o rodapé dos emails. Busca o rodapé cadastrado no módulo empresa. Função se encontra em /inc/php/functions.php
				$rodapeemailhtml = imagemtipoemailempresa("PEDIDOENTREGUE",$idempresa,$dominio);
				// Caso a função imagemtipoemailempresa retorne FALSE
				if(!$rodapeemailhtml){
					$rodapeemailhtml = "";
				}
				
            //da explode para pegar os emails
            $stremail = explode(",",$row["emailrep"]);
	    
	    // $sqlf = "update nf set enviaemaildetalhe = 'A', logemailnfe = concat(ifnull(logemailnfe,''),'- ENVIANDO EMAIL - ') where idnf =".$row["idnf"];		
	    // d::b()->query($sqlf) or die("erro ao alterar a nf [".mysqli_error(d::b())."] ".$sqlf);
				
		if(empty($dominio)){
			$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
			values (".$row["idnf"].",'nf','PEDIDOENTREGUE','REMETENTE VAZIO','ERRO',sysdate())";

			d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");
			die("O remetente está vazio");
		}
            // roda no loop para enviar um email para cada endereço 
            //for($i=0;$i<count($stremail);$i++){ 
                $ret1="";

                // echo $stremail[$i];
				
				if(!empty($row["idempresa"])){
					$idempresa = $row["idempresa"];
				}else{
					$idempresa = 0;
				}
				
					
                //monta a mensagem
                //$message = retpar('textoPEDIDOENTREGUE');
				
				$sqlempresa = "SELECT * FROM empresa WHERE idempresa = ".$row["idempresa"];
				$resempresa=d::b()->query($sqlempresa) or die("Erro ao buscar informações da empresa: ".mysqli_error(d::b()).$sqlempresa);
				$rowempresa=mysqli_fetch_assoc($resempresa);
				
				$message = "
					Prezado (a) xrepresentante,

					Informamos que o pedido de número do cliente ".$row["cliente"]." de nota ".$row["idnf"]." foi entregue com sucesso.

					Atenciosamente,
					".$rowempresa["nomefantasia"].".
				";
                //$rodapeemailhtml = retpar("rodapedetalhamento");
                //$rodapeemailtxt = retpar("rodapeemailcotacaotxt");

                /*
                 * Monta versao de Texto
                 */
                $messagetxt = $message;		
                $messagetxt = str_replace("xrepresentante",$row["nome"], $messagetxt);

                /*
                 * Monta versao HTML
                 */
                $messagehtm = $message;
                $messagehtm = str_replace("xrepresentante",$row["nome"], $messagehtm);

                $messagehtm = nl2br($messagehtm);
                $messagehtm = $messagehtm.$rodapeemailhtml;
                $messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$messagehtm."</body></html>"; 

                echo($messagehtm);
                //die($messagehtm);
                                        
                /************************CABECALHO E TEXTO**************************/
                /*** FROM***/
                $emailFrom=$vemailFrom;
                $nomeFrom=$vnomeFrom;
                /***DESTINATARIO***/
                // $emailDest=$stremail[$i];
                $emailDestNome=$row["nome"];
                /***CCO***/
            //	$emailDestCCO="resultados@laudolab.com.br";
            //	$emailDestCCONome="Resultados Laudo Laboratório";
                /*** ASSUNTO***/
                $assunto= $vassunto;

                if($testeinterno!="Y"){//Se nao estiverem sendo executados testes pela web
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
					$emails = explode(",",$row["emailrep"]);
					if($tipoenvio == "CC"){
						foreach($emails as $email){
							$mail->AddAddress($email,$emailDestNome);
							$mail->AddCC($email,$emailDestNome);
						}
					}elseif($tipoenvio == "CCO"){
						foreach($emails as $email){
							$mail->AddAddress($email,$emailDestNome);
							$mail->AddBCC($email,$emailDestNome);
						}
					}else{
						foreach($emails as $email){
							$mail->AddAddress($email,$emailDestNome);
						}
					}

                    // $mail->AddAddress($emailDest,$emailDestNome);
					
					$mail->AddCustomHeader("X-MAIL-MOD:".strtoupper(explode(".",basename($_SERVER["SCRIPT_FILENAME"]))[0]));
                    // Copia
                    //$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); 
                    //email copia oculta
            //	$mail->AddBCC($emailDestCCO, $emailDestCCONome);

                    // Adicionar um anexo
                    $mail->AddAttachment('/var/www/laudo/tmp/nfe/Detalhamento_Det_'.$row['idnf'].'.pdf');
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
                                       
                    if (!$mail->Send()) {
                        echo " ERRO ao enviar email. (" . $mail->ErrorInfo. ") ";
                        $ret.= " ERRO ao enviar email. (" . $mail->ErrorInfo. ") ";
                        $ret1= " ERRO ao enviar email. (" . $mail->ErrorInfo. ") ";
                        $envioerro=1;
                        // insere o log de erro
						//coloca na nf como se o envio fosse ok e o email erro 
						$sqlu = "update nf set enviaemaildetalhe = 'E', logemailnfe = concat(ifnull(logemailnfe,''),' ".$ret." ') where idnf =".$row["idnf"];		
						d::b()->query($sqlu) or die("erro ao alterar a nf [".mysqli_error(d::b())."] ".$sqlu);

                        $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
                        values (".$row["idnf"].",'nf','PEDIDOENTREGUE','".$ret1."','ERRO',sysdate())";

                        d::b()->query($sql);			

                    } else {
						// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
						
						$_sql1 = "select * from pessoa where usuario = '".$row["alteradopor"]."' and status = 'ATIVO' limit 1";
						$_resu = d::b()->query($_sql1) or die($_sql1);
						$_qtd = mysql_num_rows($_resu);
						if($_qtd > 0){
							while($_r = mysql_fetch_assoc($_resu)){
								
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$row["idempresa"].",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$row["idnf"].",'detalhamento',".$row["idnf"].",'nf',".$_r["idpessoa"].",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','?_modulo=nfs&_acao=u&idnf=".$row["idnf"]."','".base64_encode(serialize($aMsg))."',sysdate(),'".$_r["usuario"]."','".$_r["usuario"]."',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela filaemail ".$_sql);
								
							}
						}else{
							while($_r = mysql_fetch_assoc($_resu)){
								
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$row["idempresa"].",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$row["idnf"].",'detalhamento',".$row["idnf"].",'nf',1029,'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','?_modulo=nfs&_acao=u&idnf=".$row["idnf"]."','".base64_encode(serialize($aMsg))."',sysdate(),'sislaudo','sislaudo',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela filaemail ".$_sql);
								
							}
						}
						// ---------------------------------------------------------------------
						
                        echo " Email enviado com sucesso! ";
                        $ret .= "  ".$stremail[$i]." ";
                        $ret1= "  ".$stremail[$i]." ";
                        $enviook=1;
						$envioerro=0;

                        // insere o log de sucesso após enviar o email
                        $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
                        values (".$row["idnf"].",'nf','PEDIDOENTREGUE','".$ret1."','SUCESSO',sysdate())";

                        d::b()->query($sql);						
                    }
					// Apaga conteúdo do array para a próxima iteração do loop
					unset($aMsg);
                }//$testeweb//if($testeinterno!="Y")
                                    
            //}//fim do loop de emails//for($i=0;$i<count($stremail);$i++)
						
            if($envioerro!=1 or $enviook==1){
                //coloca na nf como se o envio fosse ok e o email erro 
                $sqlu = "update nf set emailrepresentante = 'O', logemailnfe = concat(ifnull(logemailnfe,''),' ".$ret." ') where idnf =".$row["idnf"];		
                d::b()->query($sqlu) or die("erro ao alterar a nf [".mysqli_error(d::b())."] ".$sqlu);
            }else{
                //coloca na nf como se o envio fosse ok e o email erro 
                $sqlu = "update nf set emailrepresentante = 'E', logemailnfe = concat(ifnull(logemailnfe,''),' ".$ret." ') where idnf =".$row["idnf"];		
                d::b()->query($sqlu) or die("erro ao alterar a nf [".mysqli_error(d::b())."] ".$sqlu);
            }
				
	}else{// if(!empty($row["emailrep"]))
            $ret.= " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
            $ret1= " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
            echo($ret);

            $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
            values (".$row["idnf"].",'nf','PEDIDOENTREGUE','".$ret1."','EMAILVAZIO',sysdate())";

            d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");

            //coloca na nf como se o envio fosse ok e o email erro 
            $sqlu = "update nf set emailrepresentante = 'E', logemailnfe = concat(ifnull(logemailnfe,''),' ".$ret." ') where idnf =".$row["idnf"];		
            d::b()->query($sqlu) or die("erro ao alterar a nf [".mysqli_error(d::b())."] ".$sqlu);
	}
    }//while($row = mysqli_fetch_assoc($sqlres))

		
re::dis()->hMSet('cron:enviaemailrepresentante',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailrepresentante', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);




?>
	
