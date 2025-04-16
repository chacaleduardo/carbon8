<?php
require_once("/var/www/carbon8/inc/php/validaacesso.php");
require "/var/www/carbon8/inc/php/composer/vendor/autoload.php";
require_once "/var/www/carbon8/inc/php/composer/vendor/dompdf/dompdf/src/Autoloader.php";

ob_start();

$echosql=false;

$simulacao=false; //Para teste: Não enviar os emails

/********************************************************************************************************
 *	GVT - 26/02/2020 - Implementando reenvio de emails oficiais											*
 * 																										*
 ********************************************************************************************************/

if(!empty($_SESSION["SESSAO"]["USUARIO"])){
	$_usuario = $_SESSION["SESSAO"]["USUARIO"];
}else{
	$_usuario = 'sislaudo';
}

if(!empty($_SESSION["SESSAO"]["IDPESSOA"])){
	$_idpessoa = $_SESSION["SESSAO"]["IDPESSOA"];
}else{
	$_idpessoa = 1029;
}
if ($_GET['echosql'] == 'Y') {
	$echosql = true;
}
if($_GET['simulacao'] == 'Y'){
	$simulacao=true;
}


if(!empty($_GET["idempresa"])){
	$__idempresa = $_GET["idempresa"];
}else{
	$__idempresa = 1;
}
Dompdf\Autoloader::register();
use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if($_GET["verifica"]=="Y"){

	$sql = "
	select sb.tipores, 
		sb.idpessoa,sb.idsecretaria,sb.idnucleo,sb.exercicio,sb.idempresa
		from 
		(
		select 'TODOS' AS tipores,
			a.idpessoa,s.idpessoa as idsecretaria,a.idnucleo,a.exercicio,a.idempresa
		from    
		(amostra a
		,resultado r 
		,pessoa p
		,pessoa s
		)           
		where p.idpessoa = a.idpessoa 
			and s.idpessoa = r.idsecretaria
			and a.idnucleo <> 0
			and  not exists  (
					select 1 
					from comunicacaoext c
					join comunicacaoextitem i on (c.idcomunicacaoext = i.idcomunicacaoext)
					where c.tipo = 'EMAILOFICIAL'
					and c.status = 'SUCESSO'
					and i.tipoobjeto = 'resultado'
					and i.idobjeto = r.idresultado
					)  
			and r.status = 'ASSINADO'
			and r.idamostra = a.idamostra 
			and r.idsecretaria != ''
			and (r.alteradoem BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()) 
		) as sb
		
		group by sb.idnucleo,sb.idsecretaria  union all 
		select sb1.tipores, 
		sb1.idpessoa,sb1.idsecretaria,sb1.idnucleo,sb1.exercicio,sb1.idempresa
		from 
		(   
			select 'POS' as tipores,
			a.idpessoa,s.idpessoa as idsecretaria,a.idnucleo,a.exercicio,a.idempresa
			from
			(amostra a
			,resultado r
			,pessoa p
			,pessoa s
			)
			where p.idpessoa = a.idpessoa
			and a.idnucleo <> 0
			and s.idpessoa = r.idsecretaria
			and  not exists  (
					select 1 
					from comunicacaoext c
					join comunicacaoextitem i on (c.idcomunicacaoext = i.idcomunicacaoext)
					where c.tipo = 'EMAILOFICIALPOS'
					and c.status = 'SUCESSO'
					and i.tipoobjeto = 'resultado'
					and i.idobjeto = r.idresultado
					)         
			and r.status = 'ASSINADO'
			and r.idamostra = a.idamostra             
			and r.alerta = 'Y'
			and r.idsecretaria != ''
			and (r.alteradoem BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW())
		) as sb1
		
		group by sb1.idnucleo,sb1.idsecretaria";

	$res = d::b()->query($sql) or die("A Consulta do email do xml nfe falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
	$auxhaemail = 0;
	echo "<table style='border:1px solid black;'><tr>
	<td style='border:1px solid black;'>Tipo Res</td>
	<td style='border:1px solid black;'>Idpessoa</td>
	<td style='border:1px solid black;'>Idsecretaria</td>
	<td style='border:1px solid black;'>Idnucleo</td>
	<td style='border:1px solid black;'>Exercício</td>
	<td style='border:1px solid black;'>Idempresa</td>
	</tr>";
	while($row = mysqli_fetch_array($res)){
		
		if($row["tipores"]=="POS"){
			$sqlalerta=" and r.alerta = 'Y' ";
			$sqlintipo="EMAILOFICIALPOS";
			$sqlconfemails="select email,p.receberes
					from pessoa p,pessoacontato c
					where p.status='ATIVO'
					and p.receberes is not null and p.receberes !=''
					and p.email is not null and p.email != ''
					and p.idpessoa = c.idcontato
					and c.idpessoa= ". $row["idsecretaria"];
		}else{
			$sqlalerta=" ";
			$sqlintipo="EMAILOFICIAL";
			$sqlconfemails="select email,p.receberestodos	as receberes
					from pessoa p,pessoacontato c
					where p.status='ATIVO'
					and p.receberestodos is not null and p.receberestodos !=''
					and p.idpessoa = c.idcontato
					and c.idpessoa= ". $row["idsecretaria"];
		}

		$resconfemails = d::b()->query($sqlconfemails) or die("A Consulta de contatos da Secretaria falhou. SQL: ".$sqlconfemails);

		$sqlemail="";
		$virg="";
		while($rowconfemails = mysqli_fetch_assoc($resconfemails)){
			$sqlemail.=$virg.$rowconfemails['email'];
			$virg=",";
		}

		if(trim($sqlemail)!="oficial@laudolab.com.br"){
			$auxhaemail += 1;
			$sqlf="select r.idresultado,a.idempresa
					from resultado r
						,amostra a
						,pessoa p
						,pessoa s
					where p.idpessoa = a.idpessoa
						and s.idpessoa = r.idsecretaria
						and r.status = 'ASSINADO'
						and r.idamostra = a.idamostra
						and r.alteradoem BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()
						".$sqlalerta."
						and r.idsecretaria = ".$row["idsecretaria"]."
						and a.idpessoa = ".$row["idpessoa"]."
						and a.idnucleo = ".$row["idnucleo"]."
						and a.exercicio = ".$row["exercicio"]."
						and not exists(
							select 1 from comunicacaoext c,comunicacaoextitem i
							where c.tipo = '".$sqlintipo."'
							and c.status ='SUCESSO'
							and c.idcomunicacaoext = i.idcomunicacaoext
							and i.tipoobjeto = 'resultado'
							and i.idobjeto = r.idresultado
						)
						and a.idempresa = ".$row["idempresa"];

			if ($echosql) echo "\n\n".$sqlf;

			$resf=d::b()->query($sqlf) or die("Erro ao buscar resultados enviados sql: ".$sqlf);

			$qtdresf=mysqli_num_rows($resf);
			
			if($qtdresf<1){
				echo "<tr style='color:red;'>";
				echo "<td style='border:1px solid black;'>".$row["tipores"]."</td><td style='border:1px solid black;'>".$row["idpessoa"]."</td>
				<td style='border:1px solid black;'>".$row["idsecretaria"]."</td>
				<td style='border:1px solid black;'>".$row["idnucleo"]."</td>
				<td style='border:1px solid black;'>".$row["exercicio"]."</td>
				<td style='border:1px solid black;'>".$row["idempresa"]."</td>";
				echo "</tr>";
				array_push($arrayenvio,[$row["tipores"],$row["idpessoa"],$row["idsecretaria"],$row["idnucleo"],$row["exercicio"],$row["idempresa"]]);
				//die("Resultado já enviado ou não existem resultados pendentes para envio. Verificar com administrador do sistema.");
			}else{
				echo "<tr style='color:blue;'>";
				echo "<td style='border:1px solid black;'>".$row["tipores"]."</td><td style='border:1px solid black;'>".$row["idpessoa"]."</td>
				<td style='border:1px solid black;'>".$row["idsecretaria"]."</td>
				<td style='border:1px solid black;'>".$row["idnucleo"]."</td>
				<td style='border:1px solid black;'>".$row["exercicio"]."</td>
				<td style='border:1px solid black;'>".$row["idempresa"]."</td>";
				echo "</tr>";
			}
		}
	}
	
	if($auxhaemail){
		echo "<tr><td colspan='6' style='border:1px solid black;'>Total: ".$auxhaemail." Email(s)</td></tr></table>";
	}else{
		echo "</table>";
		echo "<br><b>Não Há Emails</b><br>";
	}
	die();
}

	//d::b()->query("start transaction;") or die("Erro ao abrir transacao");//maf: testes durante migração

	/*******************************************************************************************************
	 *                                                                                                     * 
	 *                                        Cria-se a comunicacaoext                                     *
	 *                                                                                                     *
	 *******************************************************************************************************/

	//Concatena os emails de destino, e verifica se são resultados positivos (alerta=Y)
	if($_GET["alerta"]=="Y"){
		$sqlalerta=" and r.alerta = 'Y' ";
		$sqlintipo="EMAILOFICIALPOS";
		$sqlconfemails="select email,p.receberes
				from pessoa p,pessoacontato c
				where p.status='ATIVO'
				and p.receberes is not null and p.receberes !=''
				and p.email is not null and p.email != ''
				and p.idpessoa = c.idcontato
				and c.idpessoa= ". $_GET["idsecretaria"];
	}else{
		$sqlalerta=" ";
		$sqlintipo="EMAILOFICIAL";
		$sqlconfemails="select email,p.receberestodos	as receberes
				from pessoa p,pessoacontato c
				where p.status='ATIVO'
				and p.receberestodos is not null and p.receberestodos !=''
				and p.idpessoa = c.idcontato
				and c.idpessoa= ". $_GET["idsecretaria"];
	}

	if ($echosql) echo "\n".$sqlconfemails;

	$sqlres = d::b()->query($sqlconfemails) or die("A Consulta de contatos da Secretaria falhou. ");//.mysqli_error()."<p>SQL: $sql");
	//$sqlemail="resultados@laudolab.com.br";
	$sqlemail="";
	$virg="";
	while($row = mysqli_fetch_assoc($sqlres)){
		$sqlemail.=$virg.$row['email'];
		$virg=",";
	}

	//INSERIR DADOS DA COMUNICAÇÃO
	$sqlicom = "insert into comunicacaoext (idempresa,tipo,`from`,`to`,idobjeto,tipoobjeto,status,criadoem,criadopor)
				values (".$__idempresa.",'".$sqlintipo."','SISLAUDO','".$sqlemail."',".$_GET["idnucleo"].",'nucleo','ENVIANDO',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."')";
	d::b()->query($sqlicom) or die("Erro ao inserir Log ENVIANDO"); //[".mysql_error()."] ".$sqlicom);
	//$newidcomunicacao = mysqli_insert_id(d::b());
	$newidcomunicacao = d::b()->insert_id;

	if ($echosql) echo "\n\nnewidcomunicacao:[".$newidcomunicacao."]";

	//Recupera os resultados relacionados ao núcleo, e associa com a comunicação externa
	if(!empty($newidcomunicacao)){

		$sqlf="select r.idresultado
				from resultado r
					,amostra a
					,pessoa p
					,pessoa s
					,prodserv ps
				where p.idpessoa = a.idpessoa
					and s.idpessoa = r.idsecretaria
					-- MAF280819: de acordo com reunião com Jr./Fabio/Hermes/Cunha, optou-se por retirar a restrição de envio de emails parcialmente conforme a regra abaixo. Isto porque essa regra não era aplicada na emissãoresultadogerapdf.php no sistema antigo 
					-- and not exists(select 1 from resultado rr USE INDEX (idamostra),prodserv pp where pp.notoficial = 'Y' and rr.idtipoteste= pp.idprodserv and rr.idamostra = a.idamostra and rr.idsecretaria != '' and r.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO'))
					and r.status = 'ASSINADO'
					and r.idamostra = a.idamostra
					and r.idtipoteste = ps.idprodserv
					and r.alteradoem between '".$_GET["alterado_1"]."' and '".$_GET["alterado_2"]." 23:59:59'
					".$sqlalerta."
					and r.idsecretaria = ".$_GET["idsecretaria"]."
					and a.idpessoa = ".$_GET["idpessoa"]."
					and a.idnucleo = ".$_GET["idnucleo"]."
					and a.exercicio = ".$_GET["exercicio"]."
					and not exists(
						select 1 from comunicacaoext c,comunicacaoextitem i
						where c.tipo = '".$sqlintipo."'
						and c.status ='SUCESSO'
						and c.idcomunicacaoext = i.idcomunicacaoext
						and i.tipoobjeto = 'resultado'
						and i.idobjeto = r.idresultado
					)
					and not exists (
						select 1 from vwtipificacaosalm tp
						where tp.idamostra =a.idamostra
						and tp.idprodserv = 640 
						and tp.resultado like('%POSITIVO PARA SALMONELLA SPP%')
					)
					and ps.enviaemailoficial = 'Y'
					and a.idempresa = ".$__idempresa."";

		if ($echosql) echo "\n\n".$sqlf;

		$resf=d::b()->query($sqlf) or die("Erro ao buscar resultados enviados sql: ".$sqlf);

		$qtdresf=mysqli_num_rows($resf);
		if($qtdresf<1){
			$sqlu11="update comunicacaoext 
						set status = 'ATENCAO',
						conteudo='Resultado já enviado ou não existem resultados pendentes para envio. Tipo Envio:".$sqlintipo.", Núcleo:".$row["idnucleo"].", Secretaria:".$row["idsecretaria"].", Idpessoa:".$row["idpessoa"].",Exercício: ".$row["exercicio"]."' 
						where idcomunicacaoext = ".$newidcomunicacao;
			mysql_query($sqlu11) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu11);
			die("Resultado já enviado ou não existem resultados pendentes para envio. Verificar com administrador do sistema.");
		}else{
			$sqlu="";
			$avids=array();
			while($rowf=mysqli_fetch_assoc($resf)){
				$sqlu="INSERT INTO `comunicacaoextitem` (idempresa,idcomunicacaoext,idobjeto,tipoobjeto,criadopor,criadoem)
						values
						(".$__idempresa.",".$newidcomunicacao.",".$rowf['idresultado'].",'resultado','".$_SESSION["SESSAO"]["USUARIO"]."',now())";
				d::b()->query($sqlu) or die("erro ao vincular comunicação ao resultado erro [".mysqli_error(d::b())."] ".$sqlu);
				$avids[]=$rowf['idresultado'];
			}
		}
	}else{
		die("Falha ao gerar comunicação externa!");
	}


	/*******************************************************************************************************
	 *                                                                                                     * 
	 *     Invoca a emissaoresultado, com os parametros GET ajustados para emular uma requisição HTTP      *
	 *                                                                                                     *
	 *******************************************************************************************************/

	//Abre variaveis locais antes que os parametros sejam limpos. Estas variaveis servirao para alimentar a parte de envio do email
	$vIdnucleo=$_GET["idnucleo"];
	$vExercicio=$_GET["exercicio"];
	$vIdsecretaria=$_GET["idsecretaria"];
	$vIdpessoa=$_GET["idpessoa"];
	$vAlerta=$_GET["alerta"];
	$vIdcomunicacaoext=$newidcomunicacao;
	$vIdemailvirtualconf = $_GET["idemailvirtualconf"];


	/*
	* TRATAMENTO DOS PARAMETROS GET PARA CONCATENACAO POSTERIOR COM SQL
	* MAF: os parâmetros GET que vieram devem ser excluídos para que somente a comunicação externa seja utilizada pela emissaoresultado
	*/
	unset($_GET["idnucleo"]);
	unset($_GET["exercicio"]);
	unset($_GET["idsecretaria"]);
	unset($_GET["idpessoa"]);
	unset($_GET["alerta"]);
	unset($_GET["idemailvirtualconf"]);
	//$_GET["idcomunicacaoext"]=$newidcomunicacao;//Maf300719: este parâmetro não deve ser usado para chamar a emissão, porque o status ainda é de ENVIANDO

	//Utiliza somente o parametro _vids para filtrar os resultados a serem "impressos" 
	$_GET["_vids"]=implode(",",$avids);

	//Invoca a emissaoresultado
	require_once("emissaoresultado.php");

	//d::b()->query("rollback") or die("Erro ao efetuar rollback");//maf: testes durante migração


	/*******************************************************************************************************
	 *                                                                                                     * 
	 *                                        Geração do Arquivo .PDF                                      *
	 *                                                                                                     *
	 *******************************************************************************************************/


	//die; //Para testes, caso não se deseje enviar o email, parar neste ponto

	$html = ob_get_contents();

	ob_end_clean();

	
	$dompdf = new Dompdf();
	$dompdf->loadHtml($html);
	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render();

	//Nomeia o arquivo e abre uma variavel também para o caminho completo, para anexar posteriormente
	$nomearq="resultados_".$newidcomunicacao;
	$nomearqcompleto="/var/www/carbon8/upload/comunicacaoext/".$nomearq.".pdf";
	$link = str_replace("/var/www/carbon8", "", $nomearqcompleto);

	if($downloadpdf=='Y'){
		//Exibir para o usuário
		$dompdf->stream($nomearq.".pdf");
	}else{
		//Salvar no diretório  do sistema
		$output = $dompdf->output();
		file_put_contents($nomearqcompleto,$output);
	}


	/*******************************************************************************************************
	 *                                                                                                     * 
	 *                                     Enviar o arquivo por email                                      *
	 *                                                                                                     *
	 *******************************************************************************************************/
	//die;

	require "/var/www/carbon8/inc/php/composer/vendor/autoload.php";

	$idnucleo = $vIdnucleo;
	$exercicio = $vExercicio;
	$idsecretaria = $vIdsecretaria;
	$idpessoa = $vIdpessoa;
	$newidcomunicacao=$vIdcomunicacaoext;

	if(empty($newidcomunicacao)){
		die("Esta vazio o id comunicacao");
	}

	$envioerro=0;
	$enviook=0;

	/*
	* Busca os dados principais do cliente,idresultado e idregistro para envio
	* Obs: As Secretarias que não possuírem os emails configurados na tabela pessoa, serao colocadas como EMAILAUSENTE e I = impossivel enviar sem email na resultado
	* emailsec = N = não enviar / A = Aguardando envio / E = Enviado / I = impossivel enviar sem email
	*/
	if(!empty($idsecretaria) and !empty($exercicio) and !empty($idnucleo) and !empty($newidcomunicacao)){
				
		//buscar o tipo da comunicacão
		$sqlcom="select tipo from comunicacaoext where idcomunicacaoext=".$newidcomunicacao;
		$rescom=mysql_query($sqlcom) or die("Erro ao buscar tipo de comunicacao sql=".$sqlcom);
		$rowcom= mysql_fetch_assoc($rescom);

		$sql = "select 	p.razaosocial as nome
				from pessoa p
				where p.status in ('ATIVO','PENDENTE')
				and p.idpessoa = ".$idpessoa;

		$sqlres = mysql_query($sql) or die("A Consulta dos dados do cliente falhou : " . mysql_error() . "<p>SQL: $sql");

		$qtdres=mysql_num_rows($sqlres);
		if($qtdres<1){
			die("Não foi possivel localizar o cliente ".$idpessoa);
		}

		$row = mysql_fetch_array($sqlres);
				
		$sqln="select a.lote,a.nucleoamostra as nucleo,a.lacre,a.tc
				from comunicacaoextitem i  ,resultado r,amostra a
				where i.idcomunicacaoext = ".$newidcomunicacao."
				and i.tipoobjeto='resultado' 
				and r.idresultado=i.idobjeto
				and a.idamostra = r.idamostra";
		$resn=mysql_query($sqln) or die("Erro ao buscar informações do nucleo");
		$rown=mysql_fetch_assoc($resn);

		if($rowcom['tipo']=="EMAILOFICIALPOS"){
			$positivo="Positivo ";
			//busca email dos oficiais positivos
			$sqlemail="select p.email,c.idcontato,p.receberes,p.nome
						from pessoa p,pessoacontato c
						where p.status='ATIVO'
						and (p.email !='' and p.email is not null)
						and p.receberes > ''
						and p.idpessoa = c.idcontato
						and c.idpessoa= ".$idsecretaria;
		}else{
			$positivo="";
			//busca o email todos
			$sqlemail="select p.email,c.idcontato,p.receberestodos 	as receberes,p.nome
						from pessoa p,pessoacontato c
						where p.status='ATIVO'
						and (p.email !='' and p.email is not null)
						and p.receberestodos > ''
						and p.idpessoa = c.idcontato
						and c.idpessoa= ".$idsecretaria;
		}

		$resemail=mysql_query($sqlemail) or die("Erro ao buscar configurações de email sql=".$sqlemail);
	//	$emailcopiaoculta="resultados@laudolab.com.br";

		echo "\nO email será enviado para ".mysqli_num_rows($resemail)." destinatarios\n";

		$ret="";

		// Gera identificador do envio do email.
		$envioid = geraIdEnvioEmail();

		//envia 1 email para cada endereço
		while($rowemail=mysql_fetch_assoc($resemail)){
			$emails = explode(",",$rowemail['email']);
			foreach($emails as $emailunico){
				$emailunico = trim($emailunico);
				$strchenc="";
					
				//Monta a data que ira expirar o acesso ao sistema  
				$date = date_create(date("Y-m-d"));
				date_add($date, date_interval_create_from_date_string('728 days'));
				$dataval=date_format($date, 'Y-m-d');
						
				if($rowemail['receberes']=='LINK'){				
					//Monta a string que será encriptada
					$stringchave = ("usuario=token_secretaria&idpessoa=".$idsecretaria.'&idcomunicacaoext='.$newidcomunicacao.'&idcontato='.$rowemail['idcontato'].'&email='.$emailunico."&datalimite=".$dataval);
					// Encripta a string
					$strchenc = trim(enc($stringchave));
				}

				if(empty($strchenc) and $rowemail['receberes']=='LINK'){			
					// insere o log de erro
					$sqlu1="update comunicacaoext set status = 'ERRO',conteudo='A string do token esta vazia. Tipo Envio:".$sqlintipo.", Núcleo:".$row["idnucleo"].", Secretaria:".$row["idsecretaria"].", Idpessoa:".$row["idpessoa"].",Exercício: ".$row["exercicio"]."' where idcomunicacaoext = ".$newidcomunicacao;
					mysql_query($sqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu1);				
				}else{

					$reenvio = false;
					// Verifica se o email já foi enviado anteriormente
					$sqlverificaenvio = "SELECT 1
											from comunicacaoext c
											join comunicacaoextitem i on (c.idcomunicacaoext = i.idcomunicacaoext)
											where c.tipo in ('EMAILOFICIAL','EMAILOFICIALPOS')
											and c.status = 'SUCESSO/NOVAVERSAO'
											and i.tipoobjeto = 'resultado'
											and i.idobjeto in (".implode(",",$avids).")";
					$resverificaenvio = mysql_query($sqlverificaenvio);
					$qtdverificaenvio = mysql_num_rows($resverificaenvio);
					if($qtdverificaenvio>0){
						$reenvio = true;
					}

					//Monta versao TXT (texto puro)
					if($rowcom['tipo']=="EMAILOFICIALPOS"){
						if($rowemail['receberes']=='LINK'){
							$linkpornf = retpar('urlemailresultadooficial');
							$message = retpar('textoemailresultadooficialpos');
						}else{
							$message = retpar('textoemailresultadooficialpdfp');
						}
					}else{
						if($rowemail['receberes']=='LINK'){
							$linkpornf = retpar('urlemailresultadooficial');
							$message = retpar('textoemailsecretaria');
						}else{
							$message = retpar('textoemailsecretariapdf');
						}		
					}
					
					// PHOL - 18/12/2023 - Alterado o rodapé dos emails. Busca rodapé de email para reenvio de resultados oficiais.
					if($reenvio){
						if($rowcom['tipo']=="EMAILOFICIALPOS"){
							if($rowemail['receberes']=='LINK'){
								$linkpornf = retpar('urlemailresultadooficial');
								$message = retpar('textoreenvioemailresultadooficialpos');
							}else{
								$message = retpar('textoreenvioemailresultadooficialpdfp');
							}
						}else{
							if($rowemail['receberes']=='LINK'){
								$linkpornf = retpar('urlemailresultadooficial');
								$message = retpar('textoreenvioemailsecretaria');
							}else{
								$message = retpar('textoreenvioemailsecretariapdf');
							}		
						}
					}
					
					$sqldominio = "SELECT v.email_original as email
									FROM emailvirtualconf v 
									WHERE v.idemailvirtualconf = {$vIdemailvirtualconf}
									AND v.idempresa = {$__idempresa}
									AND v.status = 'ATIVO';";
					$resdominio=d::b()->query($sqldominio) or die("Erro ao buscar dominio de Venda da empresa sql=".$sqldominio);
					$rowdominio = mysqli_fetch_assoc($resdominio);
					$dominio = $rowdominio["email"];
					
					$sqlrodapeemail = "SELECT * FROM empresarodapeemail WHERE tipoenvio = 'RESULTADOOFICIAL' AND idempresa =".$__idempresa." ORDER BY idempresarodapeemail asc limit 1";
					$resrodapeemail=d::b()->query($sqlrodapeemail) or die("Erro ao buscar informações de email da empresa. sql=".$sqlrodapeemail);
					$rowrodapeemail=mysqli_fetch_assoc($resrodapeemail);
					
					$infcomplementar1 = ( strpos( $rowrodapeemail["assunto"], "_info_" ) !== 0 );
					$infcomplementar2 = ( strpos( $rowrodapeemail["assunto"], "_info1_" ) !== 0 );
					$infcomplementar3 = ( strpos( $rowrodapeemail["assunto"], "_info2_" ) !== 0 );
					$infcomplementar4 = ( strpos( $rowrodapeemail["assunto"], "_info3_" ) !== 0 );
					$infcomplementar5 = ( strpos( $rowrodapeemail["assunto"], "_info4_" ) !== 0 );
					
					if ($infcomplementar1) {
					   $rowrodapeemail["assunto"] = str_replace("_info_", $positivo, $rowrodapeemail["assunto"]);
					}
					if ($infcomplementar2) {
					   $rowrodapeemail["assunto"] = str_replace("_info1_", $row["nome"], $rowrodapeemail["assunto"]);
					}
					if ($infcomplementar3) {
					   $rowrodapeemail["assunto"] = str_replace("_info2_", $rown['nucleo'], $rowrodapeemail["assunto"]);
					}
					if ($infcomplementar4) {
					   $rowrodapeemail["assunto"] = str_replace("_info3_", $rown['lote'], $rowrodapeemail["assunto"]);
					}
					if ($infcomplementar5) {
					   $rowrodapeemail["assunto"] = str_replace("_info4_", $exercicio, $rowrodapeemail["assunto"]);
					}

					// GVT - 30/04/2020 - Alterado o rodapé dos emails. Busca o rodapé cadastrado no módulo empresa. Função se encontra em /inc/php/functions.php
					$rodapeemailhtml = imagemtipoemailempresa("RESULTADOOFICIAL",$__idempresa,$dominio);
					// Caso a função imagemtipoemailempresa retorne FALSE
					if(!$rodapeemailhtml){
						$rodapeemailhtml = "";
					}

					if(empty($dominio)){
						$sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem) 
						values (".$newidcomunicacao.",'comunicacaoext','EMAILOFICIAL','REMETENTE VAZIO','ERRO',sysdate())";
			
						d::b()->query($sql) or die("erro ao inserir Log de erro [".mysqli_error(d::b())."]");
						die("O remetente está vazio");
					}
					
					//$rodapeemailhtml = retpar("rodapeemailresultadosbasefor");
																	
					//Monta versao HTML
					$messagehtm = $message;
					$messagehtm = str_replace("nome", 		$row["nome"], $messagehtm);
					$messagehtm = str_replace("exercicio", 		$exercicio, $messagehtm);

					if($rowemail['receberes']=='LINK'){
						$urlhtm = $linkpornf.$strchenc;
						$linkpornf = "Para acessar o resultado, clique <a href='".$urlhtm."'>aqui</a>";
						$messagehtm = str_replace("urlresultado", $linkpornf, $messagehtm);
					}
			
					$messagehtm = str_replace("xnucleo", 	$rown['nucleo'], $messagehtm);
					$messagehtm = str_replace("xlote", 		$rown['lote'], $messagehtm);
					$messagehtm = str_replace("xlacre", 	$rown['lacre'], $messagehtm);
					$messagehtm = str_replace("xtc", 		$rown['tc'], $messagehtm);
							
					//se não tiver nucleo tira a palavra Nucleo:
					if(empty($rown['nucleo'])){
						$messagehtm = str_replace("Nucleo:","", $messagehtm);
					}
				
					//se não tiver lote tira a palavra Lote:
					if(empty($rown['lote'])){
						$messagehtm = str_replace("Lote:","", $messagehtm);
					}
							
					//se não tiver lacre tira a palavra Lacre:
					if(empty($rown['lacre'])){
						$messagehtm = str_replace("Lacre:","", $messagehtm);
					}

					//senão tiver tc tira a palavra Termo de Coleta:
					if(empty($rown['tc'])){
						$messagehtm = str_replace("Termo de Coleta:","", $messagehtm);
					}
							
					$messagehtm = nl2br($messagehtm);
					$messagehtm = $messagehtm.$rodapeemailhtml;
					$messagehtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$messagehtm."</body></html>"; 
							
					/************************CABECALHO E TEXTO**************************/

					//FROM
					$emailFrom=$dominio;
					$nomeFrom=$rowrodapeemail["nomeremetente"];

					//DESTINATARIO
					$emailDest=$emailunico;
					$emailDestNome=$rowemail['nome'];
					
					if(empty($rowrodapeemail["comcopia"])){
						$auxCC = $dominio;
					}else{
						$auxCC = $rowrodapeemail["comcopia"];
					}
					$emailDestCCO=$auxCC;
					$emailDestCCONome=$rowrodapeemail["nomecc"];
					//CCO
					//$emailDestCCO="resultados@laudolab.com.br";
					//$emailDestCCONome="Resultados Laudo Laboratório";

					//ASSUNTO
					$assunto=$rowrodapeemail["assunto"];

					//CONFIGURACOES E ENVIO
					$mail = new PHPMailer(true); //true habilita exceptions
					$mail->SMTPDebug=2; //maf120619: Recuperar o diálogo com o servidor IMAP: https://github.com/PHPMailer/PHPMailer/wiki/SMTP-Debugging

					// GVT - 08/07/2020 - Adicionado array para armazenar assunto, mensagem e anexos do email.
						//					- O array é armazenado na tabela mailfila para ser utilizado posteriormente
						//					- para mostrar o que foi enviado no email e reenvio do mesmo.
						$aMsg=array();
						$aMsg["assunto"] = $assunto;
						$aMsg["mensagem"] = $messagehtm;
						$aMsg["anexos"][0] = $nomearqcompleto;
						try{
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
							$mail->addCustomHeader('X-IDOBJETO',$newidcomunicacao);
							$mail->addCustomHeader('X-TIPOOBJETO','comunicacaoext');

							$mail->From  = $emailFrom;
							$mail->FromName  = $nomeFrom; //utf8_decode($nomeFrom);

							$mail->IsHTML(true);
							$mail->Subject  = $assunto;
							$mail->Body  = $messagehtm;
							//email destino
							$mail->AddAddress($emailDest,$emailDestNome);

							$mail->AddCustomHeader("X-MAIL-MOD:".strtoupper(explode(".",basename($_SERVER["SCRIPT_FILENAME"]))[0]));

							
							
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

							//Adicionar um anexo
							if($rowemail['receberes']=="PDF"){
								$mail->AddAttachment($nomearqcompleto);
							}

							if($simulacao===false){
								//Envia grazadeus
								if(!$mail->Send()){
									$ret.= " ERRO ao enviar email. (" . print($mail). ") ";
									$envioerro=1;									
								} else {						
									// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
									$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idsubtipoobjeto,subtipoobjeto,idpessoa,idenvio,enviadode,link,conteudoemail,criadoem,criadopor,alteradopor,alteradoem) 
											values (".$__idempresa.",'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$newidcomunicacao.",'comunicacaoext',".$idpessoa.",'cliente',".$_idpessoa.",'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."','".base64_encode(serialize($aMsg))."',sysdate(),'".$_usuario."','".$_usuario."',sysdate())";

									d::b()->query($_sql);
									// ---------------------------------------------------------------------

									
									$sqlstmp = "INSERT INTO `logmail`(`idobjeto`,`tipoobjeto`,`destinatario`,`queueid`,`log`,`criadoem`)
									VALUES (".$newidcomunicacao.",'comunicacaoext','".$emailDest."','".$GLOBALS["queueid"]."','',now());";
									d::b()->query($sqlstmp) or die("Erro ao gerar Log de Smtp [".mysqli_error(d::b())."] ".$sqlstmp);

									//echo "Email enviado com sucesso!";
									$ret .= " Enviado email com sucesso! [".$emailDest."] ";
									$retx .= " Enviado email para ".$emailunico." com sucesso! Token(".$strchenc.") ";
									$enviook=1;	

								}
							}else{
								//echo "\n\n-----------Mail getAllRecipientAddresses";
								//print_r($mail->getAllRecipientAddresses());
								//echo "\n\n-----------";
								
								$ret = "\n<Br>Simulação para ".$emailunico." executada com sucesso! ";
								$retx = "\n<br>Simulação de link para ".$emailunico." criado com sucesso! Token(".$strchenc.") ";
								$enviook=1;
								echo $ret;
								echo $retx;				
							}

						}catch (Exception $e) {
							echo "Erro do PEAR::MAIL -> {$mail->ErrorInfo}";
						}
						// Apaga conteúdo do array para a próxima iteração do loop
						unset($aMsg);
				}//if(empty($strchenc) and $rowemail['receberes']=='LINK'){	
			}			
		}//while($rowemail=mysql_fetch_assoc($resemail)){
	}else{
		$sqlu1="update comunicacaoext set status = 'ERRO',conteudo='ERRO ao tentar enviar o email. (Campo nucleo, exercicio e secretaria devem ser informados!). Tipo Envio:".$sqlintipo.", Núcleo:".$row["idnucleo"].", Secretaria:".$row["idsecretaria"].", Idpessoa:".$row["idpessoa"].",Exercício: ".$row["exercicio"]."' where idcomunicacaoext = ".$newidcomunicacao;
		mysql_query($sqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu1);
		echo "ERRO ao tentar enviar o email. (Campo nucleo, exercicio e secretaria devem ser informados!)";	
		die($ret);
	}//if(!empty($idsecretaria) and !empty($exercicio) and !empty($idnucleo) and !empty($newidcomunicacao)){

	if($enviook==1){

		if($GLOBALS["queueid"]==""){
			$messagetxt.="\nAtenção: QUEUEID não recuperado!";
		}

		$sqlu1="update comunicacaoext set status = 'SUCESSO' where idcomunicacaoext = ".$newidcomunicacao;		
		mysql_query($sqlu1) or die("erro ao inserir Log de SUCESSO [".mysql_error()."] ".$sqlu1);
		
	}else{
		$sqlu1="update comunicacaoext set status = 'ERRO',conteudo='".$retx."' where idcomunicacaoext = ".$newidcomunicacao;
		mysql_query($sqlu1) or die("erro ao inserir Log de ERRO [".mysql_error()."] ".$sqlu1);			
	}

	echo($ret);
?>