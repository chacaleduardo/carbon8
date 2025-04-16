<?
//die("Em manutençãoo. Aguarde. Entrar em contato com Hermes Pedro ou Marcelos");

ini_set('memory_limit', '-1');
require_once("inc/php/validaacesso.php");

function getRegistrosEmailOficial($idobjeto){
	global $JSON;
	
	$sql="SELECT *,date_format(criadoem,'%d/%m/%Y %H:%i:%s') as criadoem1 FROM mailfila WHERE idobjeto = ".$idobjeto." and tipoobjeto = 'comunicacaoext' and remover = 'N'";
	$res = mysql_query($sql) or die("Falha ao pesquisar emails relacionados: " . mysql_error() . "<p>SQL: ".$sql);
	$nres = mysql_num_rows($res);
	//echo "<!-- \n".$sql."\n -->";
	if($nres == 0){
		return 0;
	}else{
		$arrtmp=array();
		$i=0;
		while ($rs = mysqli_fetch_assoc($res)) {
			$_sql = "select message from mailfilalog where queueid like '%".trim($rs["queueid"])."%' and datetime > '".$rs["criadoem"]."' and destinatario = '".trim($rs["destinatario"])."' order by idmailfilalog desc limit 1";
			$_res = mysql_query($_sql) or die("Falha ao pesquisar resposta do servidor de email: " . mysql_error() . "<p>SQL: ".$_sql);
			$_nres = mysql_num_rows($_res);
			$_row=mysqli_fetch_assoc($_res);
			
			if($_nres == 0 or empty($_row["message"])){
				$respostaservidor = "Não há mensegem de resposta";
			}else{
				$respostaservidor = str_replace('"',"",$_row["message"]);
				$respostaservidor = str_replace("'","",$respostaservidor);
			}
			
			switch($rs["status"]){
				case "ENVIADO": $cor = "class = 'table-success'"; break;
				case "NAO ENVIADO": $cor = "class = 'table-danger'"; break;
				case "ADIADO": $cor = "class = 'table-warning'"; break;
				case "EM FILA": $cor = "class = 'table-active'"; break;
				default: $cor = ""; break;
			}
			$arrtmp[$i]["remetente"]=$rs["remetente"];
			$arrtmp[$i]["destinatario"]= $rs["destinatario"];
			$arrtmp[$i]["status"]=$rs["status"];
			$arrtmp[$i]["criadoem"]= $rs["criadoem1"];
			$arrtmp[$i]["criadopor"]= $rs["criadopor"];
			$arrtmp[$i]["reenvio"]= $rs["reenvio"];
			$arrtmp[$i]["enviadode"]= $rs["enviadode"];
			$arrtmp[$i]["idsubtipoobjeto"]= $rs["idsubtipoobjeto"];
			$arrtmp[$i]["idmailfila"]= $rs["idmailfila"];
			$arrtmp[$i]["idobjeto"]= $rs["idobjeto"];
			$arrtmp[$i]["cor"]= $cor;
			$arrtmp[$i]["respostaservidor"]= $respostaservidor;
			$i++;
		}

		return $JSON->encode($arrtmp);
	}
}

function getRegistrosEmail($tipoobjeto,$idobjeto,$subtipo){
	global $JSON;
	// Recupera todos os envios de email relacionados ao idobjeto
	$sqlmail="SELECT remetente,destinatario,status,date_format(criadoem,'%d/%m/%Y %H:%i:%s') as criadoem1,criadopor,criadoem,reenvio,enviadode,idsubtipoobjeto,idmailfila,idobjeto,queueid
		FROM mailfila 
		WHERE idsubtipoobjeto = ".$idobjeto." 
		and tipoobjeto = '".$tipoobjeto."' 
		and subtipoobjeto = '".$subtipo."' 
		and remover = 'N'
		".getidempresa('idempresa','envioemail')."
		ORDER BY criadoem desc";
	$resmail = mysql_query($sqlmail) or die("Falha ao pesquisar emails relacionados: " . mysql_error() . "<p>SQL: ".$sqlmail);
	$nresmail = mysql_num_rows($resmail);
	
	if($nresmail == 0){
		return 0;
	}else{
		$arrtmp=array();
		$i=0;
		while ($rs = mysqli_fetch_assoc($resmail)) {
			$_sql = "select message from mailfilalog where queueid like '%".trim($rs["queueid"])."%' and datetime > '".$rs["criadoem"]."' and destinatario = '".trim($rs["destinatario"])."' order by idmailfilalog desc limit 1";
			$_res = mysql_query($_sql) or die("Falha ao pesquisar resposta do servidor de email: " . mysql_error() . "<p>SQL: ".$_sql);
			$_nres = mysql_num_rows($_res);
			$_row=mysqli_fetch_assoc($_res);
			
			if($_nres == 0 or empty($_row["message"])){
				$respostaservidor = "Não há mensegem de resposta";
			}else{
				$respostaservidor = str_replace('"',"",$_row["message"]);
				$respostaservidor = str_replace("'","",$respostaservidor);
			}
			
			switch($rs["status"]){
				case "ENVIADO": $cor = "class = 'table-success'"; break;
				case "NAO ENVIADO": $cor = "class = 'table-danger'"; break;
				case "ADIADO": $cor = "class = 'table-warning'"; break;
				case "EM FILA": $cor = "class = 'table-active'"; break;
				default: $cor = ""; break;
			}
			$arrtmp[$i]["remetente"]=$rs["remetente"];
			$arrtmp[$i]["destinatario"]= $rs["destinatario"];
			$arrtmp[$i]["status"]=$rs["status"];
			$arrtmp[$i]["criadoem"]= $rs["criadoem1"];
			$arrtmp[$i]["criadopor"]= $rs["criadopor"];
			$arrtmp[$i]["reenvio"]= $rs["reenvio"];
			$arrtmp[$i]["enviadode"]= $rs["enviadode"];
			$arrtmp[$i]["idsubtipoobjeto"]= $rs["idsubtipoobjeto"];
			$arrtmp[$i]["idmailfila"]= $rs["idmailfila"];
			$arrtmp[$i]["idobjeto"]= $rs["idobjeto"];
			$arrtmp[$i]["cor"]= $cor;
			$arrtmp[$i]["respostaservidor"]= $respostaservidor;
			$i++;
		}

		return $JSON->encode($arrtmp);
	}
}

function getRespostas($idobjeto){
	global $JSON;
	
	// Recupera todos os envios de email relacionados ao idobjeto
	$sqlmail="SELECT ifnull(group_concat(Distinct(idsubtipoobjeto)),0) as idsubtipoobjeto
		FROM mailfila 
		WHERE idobjeto = ".$idobjeto." 
		and (tipoobjeto = 'cotacao' or tipoobjeto = 'cotacaoaprovada')
		and subtipoobjeto = 'nf'
		and remover = 'N'
		".getidempresa('idempresa','envioemail')."
		ORDER BY criadoem desc";
	$resmail = mysql_query($sqlmail) or die("Falha ao pesquisar emails relacionados: " . mysql_error() . "<p>SQL: ".$sqlmail);

	$nresmail = mysql_num_rows($resmail);
	if($nresmail == 0){
		return 0;
	}else{
		$rs = mysql_fetch_assoc($resmail);
		$sqleo="select * from (	
					select valor as log,'SUCESSO' as status,date_format(criadoem,'%d/%m/%Y %H:%i:%s') as criadoem,idobjeto
						from _auditoria 
						where objeto = 'nf' 
						and idobjeto in (".$rs["idsubtipoobjeto"].")
						".getidempresa('idempresa','_auditoria')."
						and coluna='status' 
						and valor='RESPONDIDO'
					)as u order by u.criadoem";
		$reseo=d::b()->query($sqleo) or die("Erro ao buscar emails de cotação APROVADA sql=".$sqleo);
		$qtdeo= mysqli_num_rows($reseo);

		if($qtdeo>0){
			
			$arrtmp=array();
			$i=0;
			while($roweo= mysqli_fetch_assoc($reseo)){			  
				$arrtmp[$i]["log"]=$roweo["log"];
				$arrtmp[$i]["status"]=$roweo["status"];
				$arrtmp[$i]["criadoem"]= $roweo["criadoem"];
				$arrtmp[$i]["idobjeto"]= $roweo["idobjeto"];
				$i++;
			}

			return $JSON->encode($arrtmp);
		}else{
			return 0;
		}
	}
}

function getRegistrosEmailCotacao($idobjeto){
	global $JSON;
	// Recupera todos os envios de email relacionados ao idobjeto
	$sqlmail="SELECT remetente,destinatario,status,date_format(criadoem,'%d/%m/%Y %H:%i:%s') as criadoem1,criadopor,criadoem,reenvio,enviadode,idsubtipoobjeto,idmailfila,idobjeto,tipoobjeto,queueid
		FROM mailfila 
		WHERE idobjeto = ".$idobjeto." 
		and (tipoobjeto = 'cotacao' or tipoobjeto = 'cotacaoaprovada')
		and subtipoobjeto = 'nf'
		and remover = 'N'
		".getidempresa('idempresa','envioemail')."
		ORDER BY criadoem asc";
	$resmail = mysql_query($sqlmail) or die("Falha ao pesquisar emails relacionados: " . mysql_error() . "<p>SQL: ".$sqlmail);
	$nresmail = mysql_num_rows($resmail);
	
	if($nresmail == 0){
		return 0;
	}else{
		$arrtmp=array();
		$i=0;
		while ($rs = mysqli_fetch_assoc($resmail)) {
			$_sql = "select message from mailfilalog where queueid like '%".trim($rs["queueid"])."%' and datetime > '".$rs["criadoem"]."' and destinatario = '".trim($rs["destinatario"])."' order by idmailfilalog desc limit 1";
			$_res = mysql_query($_sql) or die("Falha ao pesquisar resposta do servidor de email: " . mysql_error() . "<p>SQL: ".$_sql);
			$_nres = mysql_num_rows($_res);
			$_row=mysqli_fetch_assoc($_res);
			
			if($_nres == 0 or empty($_row["message"])){
				$respostaservidor = "Não há mensegem de resposta";
			}else{
				$respostaservidor = str_replace('"',"",$_row["message"]);
				$respostaservidor = str_replace("'","",$respostaservidor);
			}
			
			switch($rs["status"]){
				case "ENVIADO": $cor = "class = 'table-success'"; break;
				case "NAO ENVIADO": $cor = "class = 'table-danger'"; break;
				case "ADIADO": $cor = "class = 'table-warning'"; break;
				case "EM FILA": $cor = "class = 'table-active'"; break;
				default: $cor = ""; break;
			}
			$arrtmp[$i]["remetente"]=$rs["remetente"];
			$arrtmp[$i]["destinatario"]= $rs["destinatario"];
			$arrtmp[$i]["status"]=$rs["status"];
			$arrtmp[$i]["criadoem"]= $rs["criadoem1"];
			$arrtmp[$i]["criadopor"]= $rs["criadopor"];
			$arrtmp[$i]["reenvio"]= $rs["reenvio"];
			$arrtmp[$i]["enviadode"]= $rs["enviadode"];
			$arrtmp[$i]["idsubtipoobjeto"]= $rs["idsubtipoobjeto"];
			$arrtmp[$i]["idmailfila"]= $rs["idmailfila"];
			$arrtmp[$i]["tipoobjeto"]= $rs["tipoobjeto"];
			$arrtmp[$i]["idobjeto"]= $rs["idobjeto"];
			$arrtmp[$i]["cor"]= $cor;
			$arrtmp[$i]["respostaservidor"]= $respostaservidor;
			$i++;
		}
		return $JSON->encode($arrtmp);
	}
}

function getInit($idobjeto){
	global $JSON;
	// Recupera todos os envios de email relacionados ao idobjeto
	$sqlmail="SELECT Distinct(m.idsubtipoobjeto),(select if(n.status = 'REPROVADO',CONCAT(p.nome,' - <b style=\'color:red;\'>',n.status,'</b>'),CONCAT(p.nome,' - <b>',n.status,'</b>')) from pessoa p join nf n on (p.idpessoa = n.idpessoa) where n.idnf = m.idsubtipoobjeto) as nome
		FROM mailfila m
		WHERE m.idobjeto = ".$idobjeto." 
		and (m.tipoobjeto = 'cotacao' or m.tipoobjeto = 'cotacaoaprovada')
		and m.subtipoobjeto = 'nf'
		and m.remover = 'N'
		".getidempresa('m.idempresa','envioemail')."
		ORDER BY m.criadoem desc";
		
	$resmail = mysql_query($sqlmail) or die("Falha ao pesquisar emails relacionados: " . mysql_error() . "<p>SQL: ".$sqlmail);
	$nresmail = mysql_num_rows($resmail);
	
	if($nresmail == 0){
		return 0;
	}else{
		$arrtmp=array();
		$i=0;
		while ($rs = mysqli_fetch_assoc($resmail)) {
			$arrtmp[$i]["idsubtipoobjeto"]=$rs["idsubtipoobjeto"];
			$arrtmp[$i]["nome"]=$rs["nome"];
			$i++;
		}
		return $JSON->encode($arrtmp);
	}
}

	// Recupera parâmetros $GET
	$tipoobjeto  = $_GET["tipoobjeto"];
	$idobjeto  = $_GET["idobjeto"];
	
	if(!empty($_GET["idnf"])){
		$idnf = $_GET["idnf"];
	}else{
		$idnf = 0;
	}
	
	if(!empty($_GET["criadoem"])){
		$criado = $_GET["criadoem"];
	}else{
		$criado = "";
	}

	// Verifica parâmetros $GET
	if (empty($tipoobjeto) or empty($idobjeto)){
		die("Parâmetros GET inválidos");
	}else{
		$jRegistros = 0;
		$jRelacionados = 0;
		$jResposta = 0;
		$jCotacao = 0;
		$jInit = 0;
		switch($tipoobjeto){
			case 'cotacao':
				$jInit = getInit($idobjeto);
				$jCotacao = getRegistrosEmailCotacao($idobjeto);
				$jResposta = getRespostas($idobjeto);
				$tituloRegistro = "Emails Enviados do Orçamento <b>Nº".$idobjeto."</b> - Data Criação: ".$criado;
				$display = "style='display:none;'";
				$display2 = "style='display:none;'";
				break;
			case 'cotacaoaprovada':
				$jInit = getInit($idobjeto);
				$jCotacao = getRegistrosEmailCotacao($idobjeto);
				$jResposta = getRespostas($idobjeto);
				$tituloRegistro = "Emails Enviados do Orçamento <b>Nº".$idobjeto."</b> - Data Criação: ".$criado;
				$display = "style='display:none;'";
				$display2 = "style='display:none;'";
				break;
			case 'nfp':
				$jRegistros = getRegistrosEmail($tipoobjeto,$idobjeto,'nf');
				$jRelacionados = getRegistrosEmail('orcamentoprod',$idobjeto,'nf');
				$tituloRegistro = "Emails Enviados do Pedido ".$idobjeto;
				$tituloRelacionados = "Orçamento de Produto ".$idobjeto;
				$display = "style='display:none;'";
				$display2 = "";
				break;
			case 'orcamentoprod':
				$jRegistros = getRegistrosEmail($tipoobjeto,$idobjeto,'nf');
				$jRelacionados = getRegistrosEmail('nfp',$idobjeto,'nf');
				$tituloRegistro = "Emails Enviados do Orçamento de Produto ".$idobjeto;
				$tituloRelacionados = "Pedido ".$idobjeto;
				$display = "style='display:none;'";
				$display2 = "";
				break;
			case 'nfs':
				$jRegistros = getRegistrosEmail($tipoobjeto,$idobjeto,'notafiscal');
				$jRelacionados = getRegistrosEmail('detalhamento',$idobjeto,'nf');
				$tituloRegistro = "Emails Enviados da Nota Fiscal de Serviço ".$idobjeto;
				$tituloRelacionados = "Detalhamento ".$idobjeto;
				$display = "style='display:none;'";
				$display2 = "";
				break;
			case 'detalhamento':
				$jRegistros = getRegistrosEmail($tipoobjeto,$idobjeto,'notafiscal');
				$jRelacionados = getRegistrosEmail('nfs',$idobjeto,'nf');
				$tituloRegistro = "Emails Enviados do Detalhamento ".$idobjeto;
				$tituloRelacionados = "Nota Fiscal de Serviço ".$idobjeto;
				$display = "style='display:none;'";
				$display2 = "";
				break;
			case 'orcamentoserv':
				$jRegistros = getRegistrosEmail($tipoobjeto,$idobjeto,'orcamento');
				$tituloRegistro = "Emails Enviados do Orçamento de Serviço ".$idobjeto;
				$display = "style='display:none;'";
				$display2 = "style='display:none;'";
				break;
			case 'comunicacaoext': 
				$jRegistros = getRegistrosEmailOficial($idobjeto);
				$tituloRegistro = "Emails Enviados do Email Oficial ".$idobjeto;
				$display = "style='display:none;'";
				$display2 = "style='display:none;'";
				break;
			default: break;
		}
	?>

<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
		<script src="https://kit.fontawesome.com/a076d05399.js"></script>
		<title>Emails Enviados</title>
	</head>
	<style>
		body{
			margin: 20px;
		}
		
		.borda{
			border-bottom: 1px solid #e6e6e6;
		}
		
		.fas{
			cursor: pointer;
		}
		
		.popover{
			max-width: 95%;
		}
		
		.popover {
			border-color: gray;
		}
		
		.tabela:hover {
		  background-color: #DCDCDC;
		  transition-duration: .6s;
		}
		
	</style>
	<body>
	
		<div class="container-fluid">
			<?if($tipoobjeto != 'cotacao' and $tipoobjeto != 'cotacaoaprovada'){?>
			<div>
				<fieldset class="borda">
					<table style="width:100%;">
						<tr>
							<td><legend><?=$tituloRegistro?></legend></td>
							<td style="width:2%;"></td>
						</tr>
					</table>
				</fieldset>
				<fieldset id="conteudo"></fieldset>
				<br>
			</div>
			<?}else{?>
				<div>
					<fieldset class="borda">
						<table style="width:100%;">
							<tr>
								<td><legend><?=$tituloRegistro?></legend></td>
							</tr>
						</table>
					</fieldset>
					<br>
					<fieldset id="conteudo1"></fieldset>
					<br>
				</div>
			<?}?>
			<div <?=$display?>>
				<fieldset class="borda">
					<table style="width:100%;">
						<tr>
							<td><legend>Resposta do Fornecedor:</legend></td>
							<td style="width:2%;"></td>
						</tr>
					</table>
				</fieldset>
				<fieldset id="resposta"></fieldset>
				<br>
			</div>
			<div <?=$display2?>>
				<fieldset class="borda">
					<table style="width:100%;">
						<tr>
							<td><legend>Emails Relacionados: <?=$tituloRelacionados?></legend></td>
							<td style="width:2%;"><i class="fas fa-minus"></i></td>
						</tr>
					</table>
				</fieldset>
				<fieldset id="relacionados"></fieldset>
			</div>
		</div>
		<script>
			jInit = <?=$jInit?>;
			jRegistros = <?=$jRegistros?>;
			jRelacionados = <?=$jRelacionados?>;
			jResposta = <?=$jResposta?>;
			jCotacao = <?=$jCotacao?>;
			idnf = <?=$idnf?>;
			
			if(jInit === 0){
				$("#conteudo1").html("Não há registros de emails enviados");
			}else{
				$("#conteudo1").ready(function(){
					if(jCotacao === 0){
						$("#conteudo1").html("Não há registros de emails enviados");
					}else{
						var estrutura = "";
						var jAuxC = "";
						var jAuxCA = "";
						var jAuxCotacao = "";
						var show = "";
						$.each(jInit, function(i, item) {
							jAuxCotacao = $(jCotacao).filter(function (i,n){
								return n.idsubtipoobjeto===item.idsubtipoobjeto;
							});
							
							if(idnf == item.idsubtipoobjeto){
								show = "show";
							}else{
								show = "";
							}
							
							estrutura += "<table class='table table-condensed'><thead><tr style='cursor:pointer;' class='tabela' data-toggle='collapse' data-target='#id"+item.idsubtipoobjeto+"' aria-expanded='false' aria-controls='id"+item.idsubtipoobjeto+"'><td>Cotação "+item.idsubtipoobjeto+" - Fornecedor: "+item.nome+"</td></tr></thead>";
							
							estrutura += "<tr><td><table class='table collapse "+show+"' id='id"+item.idsubtipoobjeto+"'>"+
								"<thead><tr><th>Data Envio</th><th>Tipo Email</th><th>Status</th><th>Remetente</th><th>Destinatário</th>"+
								"<th>Enviado por</th><th>Reenviar</th>"+
								"<th>Remover</th></tr></thead>";
							
							jAuxC = jAuxCotacao.filter(function (i,n){
								return n.tipoobjeto==='cotacao';
							});
							jAuxCA = jAuxCotacao.filter(function (i,n){
								return n.tipoobjeto==='cotacaoaprovada';
							});

							$.each(jAuxC, function(j, n) {
								estrutura += "<tr "+n.cor+" data-toggle='popover' title='Última Resposta' data-placement='bottom' data-trigger='click' data-content='"+n.respostaservidor+"'><td>"+
								n.criadoem+"</td><td>Cotação</td><td>"+n.status+"</td><td>"+n.remetente+"</td><td>"+n.destinatario+"</td><td>"+n.criadopor+"</td>";
								
								if(n.status == 'NAO ENVIADO' && n.reenvio == 'Y'){
									estrutura += "<td><i class='fas fa-envelope' title='Reenviar Email' onclick=\'reenviaremail(\""+n.enviadode+"\","+n.idsubtipoobjeto+","+n.idmailfila+","+n.idobjeto+",\""+n.destinatario+"\")\';></i></td>";
									estrutura += "<td><i class='fas fa-trash' title='Remover da Lista' onclick='removerdalista("+n.idmailfila+");'></i></td><tr>";
								}else if(n.status == 'NAO ENVIADO' && n.reenvio == 'N'){
									estrutura += "<td>-</td>";
									estrutura += "<td>Reenviado</td><tr>";
								}else{
									estrutura += "<td>-</td><td>-</td></tr>";
								}
							});
							
							if(jResposta === 0){
								estrutura += "<tr><td colspan = '8' align='center'><b>Não há respostas do fornecedor</b></td></tr>";
							}else{
								jAuxResposta = $(jResposta).filter(function (i,n){
									return n.idobjeto===item.idsubtipoobjeto;
								});
								
								if(jAuxResposta.length == 0){
									estrutura += "<tr><td colspan = '8' align='center'><b>Não há respostas do fornecedor</b></td></tr>";
								}else{
									$.each(jResposta, function(i, n) {
										estrutura += "<tr><td>"+n.criadoem+
										"</td><td>Cotação</td><td>"+n.log+"</td><td colspan='5'></td></tr>";
									});
								}
							}
							
							$.each(jAuxCA, function(j, n) {
								estrutura += "<tr "+n.cor+" data-toggle='popover' title='Última Resposta' data-placement='bottom' data-trigger='click' data-content='"+n.respostaservidor+"'><td>"+
								n.criadoem+"</td><td>Cotação Aprovada</td><td>"+n.status+"</td><td>"+n.remetente+"</td><td>"+n.destinatario+"</td><td>"+n.criadopor+"</td>";
								
								if(n.status == 'NAO ENVIADO' && n.reenvio == 'Y'){
									estrutura += "<td><i class='fas fa-envelope' title='Reenviar Email' onclick=\'reenviaremail(\""+n.enviadode+"\","+n.idsubtipoobjeto+","+n.idmailfila+","+n.idobjeto+",\""+n.destinatario+"\")\';></i></td>";
									estrutura += "<td><i class='fas fa-trash' title='Remover da Lista' onclick='removerdalista("+n.idmailfila+");'></i></td><tr>";
								}else if(n.status == 'NAO ENVIADO' && n.reenvio == 'N'){
									estrutura += "<td>-</td>";
									estrutura += "<td>Reenviado</td><tr>";
								}else{
									estrutura += "<td>-</td><td>-</td></tr>";
								}
							});
							
							estrutura += "</table></td></tr></table>";
							
							$("#conteudo1").html(estrutura);
						});
					}
				});
			}
			
			if(jRegistros === 0){
				$("#conteudo").html("Não há registros de emails relacionados");
			}else{
				$("#conteudo").ready(function(){
					
					var estrutura = "<table class='table table-condensed'>"+
					"<thead><tr><th>Remetente</th><th>Destinatário</th><th>Status</th>"+
					"<th>Data Envio</th><th>Email Enviado por</th><th>Reenviar</th>"+
					"<th>Remover</th></tr></thead>";
					
					$.each(jRegistros, function(i, item) {
						estrutura += "<tr "+item.cor+" data-toggle='popover' title='Última Resposta' data-placement='bottom' data-trigger='click' data-content='"+item.respostaservidor+"'><td>"+item.remetente+"</td><td>"+
						item.destinatario+"</td><td>"+item.status+"</td><td>"+item.criadoem+
						"</td><td>"+item.criadopor+"</td>";
						
						if(item.status == 'NAO ENVIADO' && item.reenvio == 'Y'){
							estrutura += "<td><i class='fas fa-envelope' title='Reenviar Email' onclick=\'reenviaremail(\""+item.enviadode+"\","+item.idsubtipoobjeto+","+item.idmailfila+","+item.idobjeto+",\""+item.destinatario+"\")\';></i></td>";
							estrutura += "<td><i class='fas fa-trash' title='Remover da Lista' onclick='removerdalista("+item.idmailfila+");'></i></td><tr>";
						}else if(item.status == 'NAO ENVIADO' && item.reenvio == 'N'){
							estrutura += "<td>-</td>";
							estrutura += "<td>Reenviado</td><tr>";
						}else{
							estrutura += "<td>-</td><td>-</td></tr>";
						}
					});
					estrutura += "</table>";
					
					$("#conteudo").html(estrutura);
				});
			}

			if(jRelacionados === 0){
				$("#relacionados").html("Não há registros de emails relacionados");
			}else{
				$("#relacionados").ready(function(){
					
					var estrutura = "<table class='table table-condensed'>"+
					"<thead><tr><th>Remetente</th><th>Destinatário</th><th>Status</th>"+
					"<th>Email Enviado em</th><th>Email Enviado por</th><th>Reenviar</th>"+
					"<th>Remover</th></tr></thead>";
					
					$.each(jRelacionados, function(i, item) {
						estrutura += "<tr "+item.cor+" data-toggle='popover' title='Última Resposta' data-placement='bottom' data-trigger='click' data-content='"+item.respostaservidor+"'><td>"+item.remetente+"</td><td>"+
						item.destinatario+"</td><td>"+item.status+"</td><td>"+item.criadoem+
						"</td><td>"+item.criadopor+"</td>";
						
						if(item.status == 'NAO ENVIADO' && item.reenvio == 'Y'){
							estrutura += "<td><i class='fas fa-envelope' title='Reenviar Email' onclick=\'reenviaremail(\""+item.enviadode+"\","+item.idsubtipoobjeto+","+item.idmailfila+","+item.idobjeto+",\""+item.destinatario+"\")\';></i></td>";
							estrutura += "<td><i class='fas fa-trash' title='Remover da Lista' onclick='removerdalista("+item.idmailfila+");'></i></td><tr>";
						}else if(item.status == 'NAO ENVIADO' && item.reenvio == 'N'){
							estrutura += "<td>-</td>";
							estrutura += "<td>Reenviado</td><tr>";
						}else{
							estrutura += "<td>-</td><td>-</td></tr>";
						}
					});
					estrutura += "</table>";
					
					$("#relacionados").html(estrutura);
				});
			}

			$(document).ready(function(){
				$('[data-toggle="popover"]').popover({container: 'body'});   
			});
			
			function reenviaremail(enviadode,idsubtipoobjeto,idmailfila,idobjeto,destinatario){
				if(confirm("Deseja realmente reenviar o email?")){
					if(enviadode === '' || enviadode === undefined || enviadode === 'sislaudo' || idsubtipoobjeto === 0 || idmailfila === 0 || idobjeto === 0 || destinatario === ''){
						alert("Não foi possível reenviar email");
						console.warn("Parâmetros da função 'reenviaremail' inválidos");
					}else{
						var tabela;
						var campo;
						
						switch(enviadode){
							case "/var/www/carbon8/cron/enviaemailcotacao.php":
								tabela = 'nf';
								campo = 'envioemailorc';
								break;
							case "/var/www/carbon8/cron/enviaemailcotacaoaprovada.php":
								tabela = 'nf';
								campo = 'emailaprovacao';
								break;
							case "/var/www/carbon8/cron/enviaemaildetalhe.php":
								tabela = 'notafiscal';
								campo = 'enviaemaildetalhe';
								break;
							case "/var/www/carbon8/cron/enviaemailnfp.php":
								tabela = 'nf';
								campo = 'envioemail';
								break;
							case "/var/www/carbon8/cron/enviaemailorcprod.php":
								tabela = 'nf';
								campo = 'envioemailorc';
								break;
							case "/var/www/carbon8/cron/enviaemailorcserv.php":
								tabela = 'orcamento';
								campo = 'envioemail';
								break;
							case "/var/www/carbon8/cron/enviaemailnfs.php":
								tabela = 'notafiscal';
								campo = 'enviaemailnfe';
								break;
							case "/report/enviaemailoficial_emissaogerapdf.php":
								tabela = 'comunicacaoext';
								campo = 'comunicacaoext';
								break;
							default:
								tabela = '';
								campo = '';
								break;
						}
						if(tabela === '' || campo === ''){
							alert("Não foi possível reenviar email");
							console.warn(enviadode+" não encontrado");
						}else{
							if(tabela === 'comunicacaoext' && campo === 'comunicacaoext'){
								window.open('report/enviaemailoficial_emissaogerapdf.php?reenvio=Y&idobjeto='+idobjeto+'&destinatario='+destinatario);
							}else{
								$.ajax({
									type: "get",
									url : "ajax/reenviaemail.php",
									data: { 
										tabela : tabela,
										campo: campo,
										idsubtipoobjeto: idsubtipoobjeto,
										idmailfila: idmailfila
									},
									success:
										alert("O email será reenviado em breve"),
									error: function(objxmlreq){
										alert('Erro:<br>'+objxmlreq.status);
									}
								});//$.ajax
								console.log("Email reenviado");
							}
						}
					}
				}
			}

			function removerdalista(idmailfila){
				if(idmailfila != 0){
					$.ajax({
						type: "get",
						url : "ajax/reenviaemail.php",
						data: { 
							remover: 'Y',
							idmailfila: idmailfila
						},
						success:
							alert("O email será removido dessa lista"),
						error: function(objxmlreq){
							alert('Erro:<br>'+objxmlreq.status);
						}
					});//$.ajax
					$("#"+idmailfila).hide();
				}else{
					alert("Não foi possível remover esse email");
					console.warn("Parâmetros da função 'removerdalista' inválidos");
				}
			}
		</script>
	</body>
</html>
<?}?>