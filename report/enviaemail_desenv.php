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

$envioerro=0;
$enviook=0;


 /* ========================================================================== Cotação Aprovada ==================================================================*/
$sql ="SELECT n.idnf,p.idpessoa,p.nome as cliente,n.emailorc as emailorc,n.comissao,n.alteradopor,n.tipoobjetosolipor,n.idobjetosolipor,n.idempresa
		FROM nf n,pessoa p
		where  p.idpessoa = n.idpessoa
		and n.tiponf !='C'
		and n.envioemailorc = 'Y'";
		
$sqlres = d::b()->query($sql) or die("A Consulta dos emails a enviar falhou : " . mysqli_error() . "<p>SQL: $sql");

// Gera identificador do envio do email.
$envioid = geraIdEnvioEmail();
$row = mysqli_fetch_array($sqlres);
$w = 0;
while ($w < 1){
    $w++;
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
		
		$sqlemail = "SELECT v.email_original as email
						FROM empresaemailobjeto e 
						JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
						WHERE e.tipoenvio = 'ORCPROD'
							AND v.status = 'ATIVO'
							AND e.tipoobjeto = 'nf'
							AND e.idobjeto = {$row["idnf"]}
							AND e.idempresa = {$row["idempresa"]}
						ORDER BY e.idempresaemailobjeto desc limit 1";
		$resemail=d::b()->query($sqlemail) or die("Erro ao buscar emails da empresa sql=".$sqlemail);
		$qtdemail=mysqli_num_rows($resemail);
		if($qtdemail>0){
			$rowemail = mysqli_fetch_assoc($resemail);
			$row["emailorc"].=",".$rowemail['email'];
			$dominio = $rowemail['email'];
		}else{
			$sqlempresaemail = "SELECT ev.email_original AS dominio 
								FROM empresaemails em 
								JOIN emailvirtualconf ev ON(em.idemailvirtualconf = ev.idemailvirtualconf)
								WHERE em.tipoenvio = 'ORCPROD' 
								AND ev.status = 'ATIVO'
								AND em.idempresa ={$row["idempresa"]}
								ORDER BY em.idempresaemails asc limit 1";
			$resempresaemail=d::b()->query($sqlempresaemail) or die("Erro ao buscar email da empresa sql=".$sqlempresaemail);
			$rowempresaemail = mysqli_fetch_assoc($resempresaemail);
			$dominio = $rowempresaemail['dominio'];
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
			$rodapeemailhtml = retpar("rodapevendasbasefor");
		}

	//da explode para pegar os emails
		$stremail = array_unique(explode(",",$row["emailorc"]));
		for($i=0;$i<count($stremail);$i++){
			$ret1="";
			 
			echo $stremail[$i];	
			
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
		//	$rodapeemailtxt = retpar("rodapeemailresultadostxt");

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
				$mail->AddAddress($emailDest,$emailDestNome);
				// Copia
				//$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); 
				//email copia oculta
				$mail->AddBCC($emailDestCCO, $emailDestCCONome);

				// Adicionar um anexo
				$mail->AddAttachment('/var/www/laudo/tmp/nfe/Orcamento_prod_'.$row['idnf'].'.pdf');
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
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$idempresa.",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$row["idnf"].",'orcamentoprod',".$row["idnf"].",'nf',".$_r["idpessoa"].",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."',sysdate(),'".$_r["usuario"]."','".$_r["usuario"]."',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
								// ---------------------------------------------------------------------
							}
						}else{
							while($_r = mysql_fetch_assoc($_resu)){
								
								// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$idempresa.",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$row["idnf"].",'orcamentoprod',".$row["idnf"].",'nf',1029,'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."',sysdate(),'sislaudo','sislaudo',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
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
					values (".$row["idnf"].",'nf','EMAILORCPROD','".$ret1."','SUCESSO',sysdate())";

					d::b()->query($sql);						
				}	
			}//$testeweb
		}//fim for email

		//coloca na cotação como se o envio fosse ok e o email erro
		$sqlu = "update nf set envioemailorc = 'O', logemail = concat(ifnull(logemail,''),' ".$ret." ') where idnf =".$row["idnf"];
		d::b()->query($sqlu) or die("erro ao alterar a pedido [".mysqli_error()."] ".$sqlu);
						
	}else{

		$ret.= " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
		$ret1= " ERRO ao tentar enviar o email. (Campo Email(s) vazio!) ";
		echo($ret);
			
		$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$row["idnf"].",'nf','EMAILORCPROD','".$ret1."','EMAILVAZIO',sysdate())";
			
		d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error()."]");
			
		//coloca na cotação como se o envio fosse ok e o email erro
		$sqlu = "update nf set envioemailorc = 'E', logemail = concat(ifnull(logemail,''),' ".$ret." ') where idnf =".$row["idnf"];
		d::b()->query($sqlu) or die("erro ao alterar a pedido [".mysqli_error()."] ".$sqlu);
			
	}	
}

/*
 * Busca os dados principais do cliente,idresultado e idregistro para envio
 * Obs: As Secretarias que não possuírem os emails configurados na tabela pessoa, serao colocadas como EMAILAUSENTE e I = impossivel enviar sem email na resultado
 * emailsec = N = não enviar / A = Aguardando envio / E = Enviado / I = impossivel enviar sem email
 */
 
 /* ========================================================================== Cotação Aprovada ==================================================================
$sql = "select p.emailresult as emailresult,p.nome,p.idpessoa,c.idnf,co.idcotacao,concat(co.idcotacao,'.',c.idnf) as ncompra,DATEDIFF(co.prazo,sysdate())+365 as prazo,dma(DATE_ADD(co.prazo, INTERVAL 365 DAY)) as dmaprazo,c.idobjetosolipor,c.tipoobjetosolipor,c.alteradopor,c.idempresa
		from cotacao co, nf c,pessoa p
		where co.idcotacao = c.idobjetosolipor
		and c.tipoobjetosolipor='cotacao' 
		and p.status = 'ATIVO'
		and c.idnf = 61627
		and p.idpessoa = c.idpessoa
		and c.status = 'APROVADO' ";

//die($sql);

$sqlres = d::b()->query($sql) or die("A Consulta do fornecedor falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");

// Gera identificador do envio do email.
$envioid = geraIdEnvioEmail();
$row = mysqli_fetch_array($sqlres);
$w = 0;
while ($w < 1){
    $w++;
        $sqlf = "update nf set emailaprovacao = 'O' where idnf =".$row["idnf"];
        $retf = d::b()->query($sqlf);

        if(!$retf){              
            echo("Erro ao atualizar status da cotacaoforn \n<br>".mysqli_error(d::b())."\n<br>".$sqlf);
            die();
        }
	
		// se o campo com os emails estiver preenchido	
		if(!empty($row["idpessoa"]) and !empty($row["idnf"]) and !empty($row["emailresult"])){
			
			$sqlemail = "SELECT v.email_original as email
						FROM empresaemailobjeto e 
							JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
						WHERE e.tipoenvio = 'COTACAOAPROVADA'
							and e.tipoobjeto = 'nf'
							and e.idobjeto = ".$row["idnf"]."
							and e.idempresa = ".$row["idempresa"]."
						ORDER BY e.idempresaemailobjeto desc limit 1";
			$resemail=d::b()->query($sqlemail) or die("Erro ao buscar emails da empresa sql=".$sqlemail);
			$qtdemail=mysqli_num_rows($resemail);
			if($qtdemail>0){
				$rowemail = mysqli_fetch_assoc($resemail);
				$row["emailresult"].=",".$rowemail['email'];
				$dominio = $rowemail['email'];
			}else{
				$sqlempresaemail = "SELECT ev.email_original as dominio FROM empresaemails em join emailvirtualconf ev 
					on (em.idemailvirtualconf = ev.idemailvirtualconf) WHERE em.tipoenvio = 'COTACAOAPROVADA' AND em.idempresa =".$row["idempresa"]." ORDER BY em.idempresaemails asc limit 1";
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
			$rodapeemailhtml = imagemtipoemailempresa("COTACAOAPROVADA",6,$dominio);
			// Caso a função imagemtipoemailempresa retorne FALSE
			if(!$rodapeemailhtml){
				$rodapeemailhtml = retpar("rodapesuprimentos");
			}
			
			//da explode para pegar os emails
			$stremail = explode(",",$row["emailresult"]);
			
			// roda no loop para enviar um email para cada endereço 
			for($i=0;$i<1;$i++){
				$messagetxt = "";
				$ret="";
				$emailunico = $stremail[$i];
				echo $emailunico;
				
				//echo("</br>");
			
				//Monta a data que ira expirar o acesso ao sistema  
				$date = date_create(date("Y-m-d"));
				date_add($date, date_interval_create_from_date_string($row["prazo"].' days'));
				$dataval=date_format($date, 'Y-m-d');
				
				//Monta a string que será encriptada
				$stringchave = ("_acao=u&usuario=token_cotacao&idpessoa=".$row["idpessoa"]."&idnf=".$row["idnf"]."&email=".$stremail[$i]."&datalimite=".$dataval);
				// Encripta a string
				$strchenc = enc($stringchave);
				
				$sqlempresa = "SELECT * FROM empresa WHERE idempresa = ".$row["idempresa"];
				$resempresa=d::b()->query($sqlempresa) or die("Erro ao buscar informações da empresa: ".mysqli_error(d::b()).$sqlempresa);
				$rowempresa=mysqli_fetch_assoc($resempresa);
				
				//monta a mensagem
				$linkpornf = retpar('urlemailcotacaoaprovada');
				//$message = retpar('textoemailcotacaoaprovada');
				$message = "
					Prezado Fornecedor nomefornecedor,

					<b>APROVAMOS</b>, conforme resultado da Orçamento <font color='blue'>XXX</font>, Cotação <font color='blue'>YYY</font>, 
					a aquisição dos materiais e/ou serviços relacionados.

					Clique urlcotacao para acessar o arquivo desta aprovação.

					Este link poderá ser acessado até: <b>prazo</b>. 
					Caso não consiga acessá-lo, favor entrar em contato conosco.

					<b>ENDEREÇO DE FATURAMENTO, COBRANÇA E ENTREGA:</b>
					".$rowempresa["razaosocial"]."
					CNPJ: ".formatarCPF_CNPJ($rowempresa["cnpj"],true)." | I.E: ".$rowempresa["inscestadual"]."
					".$rowempresa["xlgr"]." - ".$rowempresa["nro"]." - ".$rowempresa["xbairro"]."
					CEP ".formatarCEP($rowempresa["cep"],true)." - ".$rowempresa["xmun"]." (".$rowempresa["uf"].") - Tel.: (".$rowempresa["DDDPrestador"].") ".$rowempresa["TelefonePrestador"];
					
					if(!empty($rowempresa["refentrega"])){
						$message .= "Referência p/ entrega: ".$rowempresa["refentrega"];
					}
					if(!empty($rowempresa["localizacaomaps"])){
						$message .= "Localização: <a href='".$rowempresa["localizacaomaps"]."'><font color='blue'>".$rowempresa["localizacaomaps"]."</font></a>";
					}
						
					if(!empty($rowempresa["horariorecebimento"])){
						$message .= "<b>** HORÁRIO DE RECEBIMENTO DE MERCADORIAS **</b>
						".$rowempresa["horariorecebimento"];
					}

					$message .= "<br><font color='red'>OBS¹: </font> 
					Navegadores compatíveis: Google Chrome e Mozilla Firefox.

					<font color='red'>OBS²: </font>
					- Favor constar o número da solicitação no corpo da nota fiscal.
					- Enviar o(s) arquivo(s) da nota fiscal eletrônica (NFs, DANFE, XML, boletos) para: <b>".$rowempresa["emailnfe"]."</b>
					- Entregas de produtos em desacordo com pedido da empresa ".$rowempresa["nomefantasia"]." estarão sujeitas à devolução.

					Atenciosamente, 
				";
				
				
				//$rodapeemailhtml = retpar("rodapesuprimentos");
				//$rodapeemailtxt = retpar("rodapeemailcotacaotxt");
		
				/*
				 * Monta versao de Texto
				 */
				 
				 /*
				$messagetxt = $message;		
				$messagetxt = str_replace("prazo", 		$row["dmaprazo"], $messagetxt);
				$messagetxt = str_replace("XXX", 		$row["idcotacao"], $messagetxt);
				$messagetxt = str_replace("YYY", 		$row["idnf"], $messagetxt);
				$messagetxt = str_replace("urlcotacao",	$linkpornf, $messagetxt);
				$messagetxt = str_replace("nomefornecedor",	$row["nome"], $messagetxt);
				//$messagetxt = $messagetxt . $rodapeemailtxt;
				
				//die($messagetxt);
		
				/*
				 * Monta versao HTML
				 */
				 
				 /*
				$messagehtm = $message;
				$messagehtm = str_replace("prazo", 		$row["dmaprazo"], $messagehtm);
				$messagehtm = str_replace("XXX", 		$row["idcotacao"], $messagehtm);
				$messagehtm = str_replace("YYY", 		$row["idnf"], $messagehtm);
				$messagehtm = str_replace("nomefornecedor",	$row["nome"], $messagehtm);
				$urlhtm = $linkpornf.$strchenc;
				$linkpornf = "<a href='".$urlhtm."'><font color='blue'>aqui</font></a>";
				$messagehtm = str_replace("urlcotacao", $linkpornf, $messagehtm);
				$messagehtm = nl2br($messagehtm);
				$messagehtm = $messagehtm.$rodapeemailhtml;
				$messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$messagehtm."</body></html>"; 
		
				echo($messagehtm);
				//die($messagehtm); 
				
				$sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'COTACAOAPROVADA' AND idempresa =".$idempresa." ORDER BY idempresarodapeemail asc limit 1";
				$resrodapeemail=d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=".$sqlrodapeemail);
				$rowrodapeemail=mysqli_fetch_assoc($resrodapeemail);
				
				/************************CABECALHO E TEXTO**************************/
				/*** FROM***/
				/*
				$emailFrom=$dominio;
				$nomeFrom=$rowrodapeemail["nomeremetente"];
				/***DESTINATARIO***/
				
				/*
				$emailDest=$stremail[$i];
				$emailDestNome=$row['nome'];
				/***CCO***/
				/*
				if(empty($rowrodapeemail["comcopia"])){
					$auxCC = $dominio;
				}else{
					$auxCC = $rowrodapeemail["comcopia"];
				}
				$emailDestCCO=$auxCC;
				$emailDestCCONome=$rowrodapeemail["nomecc"];
				
				/*** ASSUNTO***/
				/*
				$infcomplementar = ( strpos( $rowrodapeemail["assunto"], "_info_" ) !== 0 );

				if ($infcomplementar) {
				   $rowrodapeemail["assunto"] = str_replace("_info_", $row['idcotacao'], $rowrodapeemail["assunto"]);
				}
				
				$assunto=$rowrodapeemail["assunto"];
				
				
				
				
				/******************************CONFIGURACOES E ENVIO*****************************************/
				/*
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
				$mail->AddAddress("gabrieltiburcio@laudolab.com.br",utf8_decode("Gabriel Tiburcio"));
				// Copia
				//$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); 
				//email copia oculta
			//	$mail->AddBCC($emailDestCCO, $emailDestCCONome);

				// Adicionar um anexo
				//$mail->AddAttachment('/var/www/laudo/tmp/nfe/Orcamento_INATA_'.$row['idnf'].'.pdf');
				
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
					
					echo "ERRO ao enviar email. (" . $mail->ErrorInfo. ")";
					$ret.= " ERRO ao enviar email APROVAÇÃO. (" . $mail->ErrorInfo. ") ";
					$envioerro=1;
					
						// insere o log de erro
					$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$row["idnf"].",'nf','EMAILCOTACAOAPROV','".$ret."','ERRO',sysdate())";
					
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
						$link = "?_modulo=cotacao&_acao=u&idcotacao=".$row["idobjetosolipor"];
					}

					$_sql1 = "select * from pessoa where usuario = '".$row["alteradopor"]."' and status = 'ATIVO' limit 1";
					$_resu = d::b()->query($_sql1) or die($_sql1);
					$_qtd = mysql_num_rows($_resu);
					if($_qtd > 0){
						while($_r = mysql_fetch_assoc($_resu)){
							// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
							$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,criadoem,criadopor,alteradopor,alteradoem) 
									values (".$row["idempresa"].",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$idobjetoaux.",'cotacaoaprovada',".$row["idnf"].",'nf',".$_r["idpessoa"].",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."',sysdate(),'".$_r["usuario"]."','".$_r["usuario"]."',sysdate())";

							d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
							// ---------------------------------------------------------------------
						}
					}else{
						while($_r = mysql_fetch_assoc($_resu)){
								
								// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$row["idempresa"].",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$idobjetoaux.",'cotacaoaprovada',".$row["idnf"].",'nf',1029,'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."',sysdate(),'sislaudo','sislaudo',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela mailfila ".$_sql);
								// ---------------------------------------------------------------------

							}
						}
						// ---------------------------------------------------------------------
					echo "Email de aprovação enviado com sucesso!";
					$ret .= "Email APROVAÇÃO: ".$stremail[$i]." ";
					$enviook=1;		
					
				
					// insere o log de sucesso após enviar o email
					$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
					values (".$row["idnf"].",'nf','EMAILCOTACAOAPROV','".$ret."','SUCESSO',sysdate())";
				
					d::b()->query($sql) or die("Erro ao inserir Log de SUCESSO [".mysqli_error(d::b())."]");				
				}				
			}//fim do loop de emails
			if($envioerro==1){
				//coloca no resultado como se o email fosse enviado
					$sqlu = "update nf set emailaprovacao = 'I' where idnf =".$row["idnf"];
					d::b()->query($sqlu) or die("erro ao alterar cotacaoforn [".mysqli_error(d::b())."]");				
			}elseif($enviook==1){
				//informa no resultado que o email foi enviado
					$sqlu = "update nf set emailaprovacao = 'O' where idnf =".$row["idnf"];
					d::b()->query($sqlu) or die("erro ao alterar cotacaoforn [".mysqli_error(d::b())."]");
			}
			
			
		}else{
				$ret= "ERRO ao tentar enviar o email. (Campo emailresult vazio!)";
				echo($ret);
	 
				$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
				values (".$row["idnf"].",'nf','EMAILCOTACAOAPROV','".$ret."','EMAILVAZIO',sysdate())";
		
				d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");
				
				//coloca no resultado como se o email fosse enviado
				$sqlu = "update nf set emailaprovacao = 'O' where idnf =".$row["idnf"];		
				d::b()->query($sqlu) or die("erro ao alterar nf [".mysqli_error(d::b())."]");
		}


}
*/


?>
