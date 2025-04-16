<?//Enviar email para os clientes com a nota de produto em pdf e com o xml - HERMESP 02082013-
ini_set("display_errors","1");
error_reporting(E_ALL);

// GVT - 05/02/2020 - PHPMailer para registrar os logs no servidor Hermes.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


////////////////////////////////////////////////////////////////////////////////////

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
  include_once("/var/www/carbon8/inc/php/composer/vendor/autoload.php");
  //include_once("/var/www/carbon8/inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
  include_once("/var/www/carbon8/inc/nfe/sefaz4/libs/NFe/DanfeNFePHP.class.php");
}else{//se estiver sendo executado via requisicao http
	include_once ("../inc/php/composer/vendor/autoload.php");
  include_once("../inc/php/functions.php");
  //include_once("../inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
  require_once('../inc/nfe/sefaz4/libs/NFe/DanfeNFePHP.class.php');//biblioteco do NFE para gerar o pdf	
}

$grupo = rstr(8);

re::dis()->hMSet('cron:enviaemailnfp',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailnfp', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);



	$sql = "select   n.emaildadosnfe,n.emaildadosnfemat,n.tipoenvioemail,
				p.razaosocial,n.idnf,SUBSTRING(n.idnfe,4) as idnfe,n.nnfe,n.idnf,n.emaildanfe,n.emailboleto,n.emailxml,n.enviarastreador,n.rastreador,n.idempresa
				,dma(prazo) as envio,obsenvio as previsao,t.nome as transportadora,t.url,p.idpessoa,n.comissao,n.tipoobjetosolipor,n.idobjetosolipor,n.alteradopor,n.idendrotulo
			from pessoa p,nf n left join  pessoa t on (t.idpessoa = n.idtransportadora)
			where p.idpessoa = n.idpessoa
			and n.xmlret is not null
			and n.envionfe = 'CONCLUIDA' 
			and n.envioemail = 'Y'";
 

/*
        $sql="select   -- n.emaildadosnfe
'hermespedro@yahoo.com.br' as emaildadosnfe,n.emaildadosnfemat,n.tipoenvioemail,
				p.razaosocial,n.idnf,SUBSTRING(n.idnfe,4) as idnfe,n.nnfe,n.idnf,n.emaildanfe,n.emailboleto,n.emailxml,n.enviarastreador,n.rastreador
				,dma(prazo) as envio,obsenvio as previsao,t.nome as transportadora,t.url,p.idpessoa,n.comissao
			from pessoa p,nf n left join  pessoa t on (t.idpessoa = n.idtransportadora)
			where p.idpessoa = n.idpessoa
			and n.xmlret is not null
			and n.envionfe = 'CONCLUIDA' 
			-- and n.envioemail = 'Y'
            and n.idnf=24974";
 
 */
	$sqlres = d::b()->query($sql) or die("A Consulta do email do xml nfe falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
	
	
	
	while($row = mysqli_fetch_assoc($sqlres)){
		// Gera identificador do envio do email.
	$envioid = geraIdEnvioEmail();
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
			if(!(file_exists('/var/www/laudo/tmp/nfe/'.$row1['idnfe'].'.pdf'))){
				
				$sqlimagemdanfe="select caminho from empresaimagem where idempresa = ".$row["idempresa"]." and tipoimagem = 'IMAGEMEMPRESADANFE'";
				$resimagemdanfe=d::b()->query($sqlimagemdanfe) or die("Erro ao buscar figura da danfe da empresa sql=".$sqlimagemdanfe);
				$rowimagemdanfe= mysqli_fetch_assoc($resimagemdanfe);
				if(!empty($rowimagemdanfe["caminho"])){
					$rowimagemdanfe["caminho"] = str_replace("..", "", $rowimagemdanfe["caminho"]);
					$logo = "/var/www/carbon8".$rowimagemdanfe["caminho"];
				}else{
					$logo = '';
				}
				//###INICIO### gera o arquivo PDF da nota
				$docxml =$row1["xmlret"];
				$danfe = new DanfeNFePHP($docxml, 'P', 'A4',$logo,'I','');
				$id = $danfe->montaDANFE();
				$arquivo = $danfe->printDANFE($id.'.pdf','F');	
				//$fp = fopen('/var/www/nfe/producao/enviadas/aprovadas/'.$id.'.pdf', 'w');
				//fwrite($fp,$arquivo);//GRAVA O ARQUIVO NO DIRETORIO
				//fclose($fp);
				//###FIM### gera o arquivo PDF da nota
			}else{
				$id = $row1['idnfe'];
			}
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
				$sqldominio = "SELECT v.email_original as email, v.tipoenvio
								FROM empresaemailobjeto e 
								JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
								WHERE e.tipoenvio = 'NFP'
									AND e.tipoobjeto = 'nf'
									AND e.idobjeto = {$row["idnf"]}
									AND e.idempresa = {$row["idempresa"]}
									AND v.status = 'ATIVO'
								ORDER BY e.idempresaemailobjeto desc limit 1";
				$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar dominio de Venda da empresa sql=".$sqldominio);
				$qtdemail=mysqli_num_rows($resdominio);
				if($qtdemail>0){
					$rowdominio = mysqli_fetch_assoc($resdominio);
					$dominio = $rowdominio["email"];
					$tipoenvio = $rowdominio["tipoenvio"];
					$emaildados=$row["emaildadosnfe"].",".$dominio;
				}else{
					$sqlempresaemail = "SELECT ev.email_original AS dominio,ev.tipoenvio
										FROM empresaemails em
										JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
										WHERE em.tipoenvio = 'NFP'
										AND em.idempresa = {$row["idempresa"]}
										AND ev.status = 'ATIVO'
										ORDER BY em.idempresaemails asc limit 1";
					$resempresaemail=d::b()->query($sqlempresaemail) or die("Erro ao buscar email da empresa sql=".$sqlempresaemail);
					$rowempresaemail = mysqli_fetch_assoc($resempresaemail);
					$dominio = $rowempresaemail['dominio'];
					$tipoenvio = $rowempresaemail['tipoenvio'];
					$emaildados=$row["emaildadosnfe"].",".$dominio;
				}
				
				$sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'NFP' AND idempresa =".$row["idempresa"]." ORDER BY idempresarodapeemail asc limit 1";
				$resrodapeemail=d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=".$sqlrodapeemail);
				$rowrodapeemail=mysqli_fetch_assoc($resrodapeemail);
				
				$vemailFrom=$dominio;
				$vnomeFrom=$rowrodapeemail["nomeremetente"];
				
				$infcomplementar = ( strpos( $rowrodapeemail["assunto"], "_info_" ) !== 0 );

				if ($infcomplementar) {
				   $rowrodapeemail["assunto"] = str_replace("_info_", $row['nnfe'], $rowrodapeemail["assunto"]);
				}
				
				
				$vassunto=$rowrodapeemail["assunto"];
				
				if(empty($rowrodapeemail["comcopia"])){
					$auxCC = $dominio;
				}else{
					$auxCC = $rowrodapeemail["comcopia"];
				}
				$emailDestCCO=$auxCC;
				$emailDestCCONome=$rowrodapeemail["nomecc"];

				/*
				$emaildados=$row["emaildadosnfe"].",vendas@inata.com.br";
				
				$vemailFrom="vendas@inata.com.br";
				$vnomeFrom="Vendas - INATA Produtos Biológicos";
				$vassunto="NFe -".$row['nnfe']."- INATA Produtos Biológicos";
				*/
				
				if(!empty($row["idempresa"])){
					$idempresa = $row["idempresa"];
				}else{
					$idempresa = 0;
				}
				
				// GVT - 30/04/2020 - Alterado o rodapé dos emails. Busca o rodapé cadastrado no módulo empresa. Função se encontra em /inc/php/functions.php
				$rodapeemailhtml = imagemtipoemailempresa("NFP",$idempresa,$dominio);
				// Caso a função imagemtipoemailempresa retorne FALSE
				if(!$rodapeemailhtml){
					$rodapeemailhtml = "";
				}
				
		    }elseif($row["tipoenvioemail"]=="MATERIAL"){
				$sqldominio = "SELECT v.email_original as email, v.tipoenvio
								FROM empresaemailobjeto e 
								JOIN emailvirtualconf v ON (e.idemailvirtualconf = v.idemailvirtualconf) 
								WHERE e.tipoenvio = 'NFPS'
									and e.tipoobjeto = 'nf'
									and e.idobjeto = {$row["idnf"]}
									and e.idempresa = {$row["idempresa"]}
									AND v.status = 'ATIVO'
								ORDER BY e.idempresaemailobjeto desc limit 1";
				$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar dominio de Venda da empresa sql=".$sqldominio);
				$qtdemail=mysqli_num_rows($resdominio);
				if($qtdemail>0){
					$rowdominio = mysqli_fetch_assoc($resdominio);
					$dominio = $rowdominio["email"];
					$tipoenvio = $rowdominio["tipoenvio"];
					$emaildados=$row["emaildadosnfemat"].",".$dominio;
				}else{
					$sqlempresaemail = "SELECT ev.email_original AS dominio,ev.tipoenvio
										FROM empresaemails em 
										JOIN emailvirtualconf ev ON (em.idemailvirtualconf = ev.idemailvirtualconf)
										WHERE em.tipoenvio = 'NFPS'
										AND em.idempresa = {$row["idempresa"]}
										AND ev.status = 'ATIVO'
										ORDER BY em.idempresaemails asc limit 1";
					$resempresaemail=d::b()->query($sqlempresaemail) or die("Erro ao buscar email da empresa sql=".$sqlempresaemail);
					$rowempresaemail = mysqli_fetch_assoc($resempresaemail);
					$dominio = $rowempresaemail['dominio'];
					$tipoenvio = $rowempresaemail['tipoenvio'];
					$emaildados=$row["emaildadosnfemat"].",".$dominio;
				}
				
				$sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'NFPS' AND idempresa =".$row["idempresa"]." ORDER BY idempresarodapeemail asc limit 1";
				$resrodapeemail=d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=".$sqlrodapeemail);
				$rowrodapeemail=mysqli_fetch_assoc($resrodapeemail);
				
				$vemailFrom=$dominio;
				$vnomeFrom=$rowrodapeemail["nomeremetente"];
				
				$infcomplementar = ( strpos( $rowrodapeemail["assunto"], "_info_" ) !== 0 );

				if ($infcomplementar) {
				   $rowrodapeemail["assunto"] = str_replace("_info_", $row['nnfe'], $rowrodapeemail["assunto"]);
				}
				
				
				$vassunto=$rowrodapeemail["assunto"];
				
				if(empty($rowrodapeemail["comcopia"])){
					$auxCC = $dominio;
				}else{
					$auxCC = $rowrodapeemail["comcopia"];
				}
				$emailDestCCO=$auxCC;
				$emailDestCCONome=$rowrodapeemail["nomecc"];
				
				if(!empty($row["idempresa"])){
					$idempresa = $row["idempresa"];
				}else{
					$idempresa = 0;
				}
				
				// GVT - 30/04/2020 - Alterado o rodapé dos emails. Busca o rodapé cadastrado no módulo empresa. Função se encontra em /inc/php/functions.php
				$rodapeemailhtml = imagemtipoemailempresa("NFPS",$idempresa,$dominio);
				// Caso a função imagemtipoemailempresa retorne FALSE
				if(!$rodapeemailhtml){
					$rodapeemailhtml = "";
				}
				/*
				$emaildados=$row["emaildadosnfemat"].",material@laudolab.com.br";
				
				$vemailFrom="material@laudolab.com.br";
				$vnomeFrom="Material - Laudo Laboratório";
				$vassunto="Envio de Material(is) Solicitado(s)";
				*/
		    }
				
		    //da explode para pegar os emails
		    $stremail = array_unique(explode(",",$emaildados));
		    
			if(empty($dominio)){
				$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
				values (".$row["idnf"].",'nf','EMAILNFE','REMETENTE VAZIO','ERRO',sysdate())";

				d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");
				die("O remetente está vazio");
			}
				
		    // roda no loop para enviar um email para cada endereço 
		    // foreach($stremail as $dest){
			$ret1="";

			// echo $dest;

			//monta a mensagem
			//$message = retpar('textoemailnfp');
			//$rodapeemailhtml = retpar("rodapedepartamentoadmprod");
			
			
			
			/*
			 * Monta versao HTML
			 */
			$messagehtm="<table style='font-size: 12px;'><tr><td>Prezado cliente <b>".$row["razaosocial"]."</b>, </td></tr></table><p><p>";
			//$messagehtm=$messagehtm."<table style='font-size: 12px;'><tr><td>Devido a alteração de CNPJ para emissão de notas fiscais de Laudo Laboratório Avícola Uberlândia Ltda (23.259.427/0001-04) para Inata Produtos Biológicos Ltda (39.978.746/0001-00), foi necessária a troca de instituições financeiras; de Itaú Unibanco para Sicredi S.A. Entretanto, durante esta mudança, tivemos que fazer alguns ajustes que influenciaram na geração equivocada dos boletos.</td></tr></table><p>";
			//$messagehtm=$messagehtm."<table style='font-size: 12px;'><tr><td><b>Diante disso, pedimos que desconsiderem os boletos do Sicredi S.A. enviado na ERRATA anterior, pois houve alteração de agência no boleto, impossibilitando a localização da fatura para pagamento. Portanto, gentileza considerar apenas o arquivo deste e-mail.</b></td></tr></table><p>";
			//$messagehtm=$messagehtm."<table style='font-size: 12px;'><tr><td>Pedimos desculpas pelo transtorno e estamos trabalhando para que esta mudança tenha menos impacto operacional possível.</td></tr></table><p>";
			$messagehtm=$messagehtm."<table style='font-size: 12px;'><tr><td>Segue(m) arquivo(s) referente(s) a NFe <b>".$row["nnfe"]."</b>.</td></tr></table><p>";
			$messagehtm=$messagehtm."<table style='font-size: 12px;'>";
			if(!empty($row['envio']) and !empty($row['previsao'])){
			$messagehtm = $messagehtm."<tr><td>Data envio: <b>".$row['envio']."</b> - Previsão Entrega: <b>".$row['previsao']."</b>.</td></tr>";
			}
			if(!empty($row['transportadora'])){
				$messagehtm = $messagehtm."<tr><td>Transportadora: <b>".$row['transportadora']."</b>.</td></tr>";
			}
			if($row['enviarastreador']=='Y' and !empty($row['rastreador'])){
			    $messagehtm = $messagehtm."<tr><td>Código de rastreamento: <b>".$row['rastreador']."</b><br>";
				if(!empty($row['url'])){
					$messagehtm = $messagehtm."Site para consulta: <a href='".$row['url']."'> ".$row['url']."</a> </td></tr>";
				}
			}
			$messagehtm=$messagehtm."</table><p>";

			$sqlend="select c.cidade,c.uf,e.logradouro,e.cep,e.endereco,e.numero,
				e.complemento,e.bairro,e.obsentrega
				from endereco e,nfscidadesiaf c
				where e.idtipoendereco in (3,2)
				and c.codcidade = e.codcidade
				and e.status='ATIVO'
				and e.idendereco = ".$row['idendrotulo']."
				and e.idpessoa=".$row['idpessoa']."";
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
			$emailDest=$dest;
			$emailDestNome=$row['cliente'];
			/***CCO***/

			/*** ASSUNTO***/
			$assunto=$vassunto;		

			/******************************CONFIGURACOES E ENVIO*****************************************/
			// GVT - 08/07/2020 - Adicionado array para armazenar assunto, mensagem e anexos do email.
			//					- O array é armazenado na tabela mailfila para ser utilizado posteriormente
			//					- para mostrar o que foi enviado no email e reenvio do mesmo.
			$aMsg=array();
			$aMsg["assunto"] = $assunto;
			$aMsg["mensagem"] = $messagehtm;
			
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
			if($tipoenvio == "CCO"){
				foreach($stremail as $dest){
					$mail->AddBCC($dest,$emailDestNome);
					$mail->AddAddress($dest,$emailDestNome);
				}
			}elseif($tipoenvio == "CC"){
				foreach($stremail as $dest){
					$mail->AddCC($dest,$emailDestNome);
					$mail->AddAddress($dest,$emailDestNome);
				}
			}else{
				foreach($stremail as $dest){
					$mail->AddAddress($dest,$emailDestNome);
				} 
			}

			$mail->AddCustomHeader("X-MAIL-MOD:".strtoupper(explode(".",basename($_SERVER["SCRIPT_FILENAME"]))[0]));
			// Copia
			//$mail->AddCC('destinarario@dominio.com.br', 'Destinatario');
			//$mail->AddBCC($emailDestCCO, $emailDestCCONome);
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

			// Adicionar um anexo
			$contanexos = 0;
			if($row['emailxml']=="Y"){
				$aMsg["anexos"][$contanexos] = '/var/www/laudo/tmp/nfe/'.$row['idnfe'].'.xml';
			    $mail->addAttachment('/var/www/laudo/tmp/nfe/'.$row['idnfe'].'.xml');
				$contanexos++;			
			}
			if($row['emaildanfe']=="Y"){
				$aMsg["anexos"][$contanexos] = '/var/www/laudo/tmp/nfe/'.$id.'.pdf';
			    $mail->addAttachment('/var/www/laudo/tmp/nfe/'.$id.'.pdf');
				$contanexos++;
			}
			if($row['emailboleto']=="Y"){
			    $sqlp ="select idcontapagar,parcela,parcelas
			    from contapagar 
			    where idobjeto =".$row['idnf']."
			    and boletopdf='Y'
			    and tipoobjeto = 'nf'";
			    $qrp = d::b()->query($sqlp) or die("Erro ao buscar parcelas da nota:".mysqli_error(d::b()));
			    while ($rowp = mysqli_fetch_array($qrp)){
					$aMsg["anexos"][$contanexos] = "/var/www/laudo/tmp/nfe/Boleto_NF_".$row['nnfe']."_Parc_".$rowp['parcela']."_de_".$rowp['parcelas'].".pdf";
					$mail->addAttachment("/var/www/laudo/tmp/nfe/Boleto_NF_".$row['nnfe']."_Parc_".$rowp['parcela']."_de_".$rowp['parcelas'].".pdf");
					$contanexos++;
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
						exists (select 1 from lote l2 join carrimbo c on(c.idobjeto = l2.idlote and c.tipoobjeto like('lote%') and c.status in ('ATIVO','ASSINADO'))
						join pessoacrmv pc on (c.idpessoa = pc.idpessoa)
									where l2.partida=l.partida and l2.exercicio=l.exercicio
								)
					)";
			$resc=d::b()->query($sqlc) or die("Erro ao selecionar certificado sql=".$sqlc);
			while($rowc=mysqli_fetch_assoc($resc)){
				$aMsg["anexos"][$contanexos] = "/var/www/carbon8/upload/nfe/Certificado_".$rowc['codprodserv']."-part".$rowc['npart'].".pdf";
			    $mail->addAttachment("/var/www/carbon8/upload/nfe/Certificado_".$rowc['codprodserv']."-part".$rowc['npart'].".pdf");
				$contanexos++;
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
				$link = "?_modulo=pedido&_acao=u&idnf=".$row["idnf"];

				$_sql1 = "select * from pessoa where usuario = '".$row["alteradopor"]."' and status = 'ATIVO' limit 1";
					$_resu = d::b()->query($_sql1) or die($_sql1);
					$_qtd = mysql_num_rows($_resu);
						if($_qtd > 0){
							while($_r = mysql_fetch_assoc($_resu)){
								
								// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$row['idempresa'].",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$row["idnf"].",'nfp',".$row["idnf"].",'nf',".$_r["idpessoa"].",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."','".base64_encode(serialize($aMsg))."',sysdate(),'".$_r["usuario"]."','".$_r["usuario"]."',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela filaemail ".$_sql);
								// ---------------------------------------------------------------------
							}
						}else{
							while($_r = mysql_fetch_assoc($_resu)){
								
								// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
								$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
										values (".$row['idempresa'].",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$row["idnf"].",'nfp',".$row["idnf"].",'nf',1029,'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."','".base64_encode(serialize($aMsg))."',sysdate(),'sislaudo','sislaudo',sysdate())";

								d::b()->query($_sql) or die("erro ao inserir email na tabela filaemail ".$_sql);
								// ---------------------------------------------------------------------

							}
						}
						// ---------------------------------------------------------------------
				

			    echo " Email enviado com sucesso! ";
			    $ret .= "  ".$dest." ";
			    $ret1= "  ".$dest." ";
			    $enviook=1;

			    // insere o log de sucesso após enviar o email
			    $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
			    values (".$row["idnf"].",'nf','EMAILNFE','".$ret1."','SUCESSO',sysdate())";

			    d::b()->query($sql) or die("Erro ao inserir Log de SUCESSO [".mysqli_error(d::b())."]");						
			}
			// Apaga conteúdo do array para a próxima iteração do loop
			unset($aMsg);
		    // }//fim do loop de emails	
				
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

	
re::dis()->hMSet('cron:enviaemailnfp',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."', 'cron', 'enviaemailnfp', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


?>
	
