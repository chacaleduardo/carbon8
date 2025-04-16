<?
ini_set("display_errors","1");
error_reporting(E_ALL);
require_once("../inc/php/validaacesso.php");
//ini_set("display_errors",true);
//error_reporting(E_ALL^E_NOTICE);
require_once("../inc/php/laudo.php");
require_once("reltesteinct2.php");


$gravaarquivo='N';
ob_start();

$sqlp="select * from pessoa where idpessoa =".$_SESSION["SESSAO"]["IDPESSOA"];
$resp = d::b()->query($sqlp) or die("Falha ao buscar dados do acesso: " . mysql_error() . "<p>SQL: $sqlp");
$rowp=mysql_fetch_assoc($resp);

if($rowp['status']!='ATIVO'){
	die('Usuário inativo no sistema.');
}

/*
 * PARAMETROS GET
*/
/* RETIRAR HERMES
$exercicio	= $_GET["exercicio"];
$idnucleo = $_GET["idnucleo"];
$exercicio = $_GET["exercicio"];
$idsecretaria = $_GET["idsecretaria"];
$idpessoa = $_GET["idpessoa"];
$alerta=$_GET["alerta"];


IF($alerta=="Y"){
	$sqlalerta=" and r.alerta = 'Y' ";
	$sqlintipo="EMAILOFICIALPOS";
	
	$sql="select email,receberes
						from pessoa p,pessoacontato c
						where p.status='ATIVO'
						and receberes is not null and receberes !=''
						and p.idpessoa = c.idcontato
						and c.idpessoa= ".$idsecretaria;
	
	
}else{
	$sqlalerta=" ";
	$sqlintipo="EMAILOFICIAL";
	
	$sql="select email,receberestodos	as receberes
						from pessoa p,pessoacontato c
						where p.status='ATIVO'
						and receberestodos is not null and receberestodos !=''
						and p.idpessoa = c.idcontato
						and c.idpessoa= ".$idsecretaria;
	
}



$echosql = true;

$mostraass=true;


//die($sql);

$sqlres = mysql_query($sql) or die("A Consulta de contatos da Secretaria falhou : " . mysql_error() . "<p>SQL: $sql");
$sqlemail="resultados@laudolab.com.br";

while($row = mysql_fetch_array($sqlres)){
	$sqlemail.=",".$row['email'];
}

/*
$sqln="select * from nucleo where idnucleo=".$idnucleo;
$resn=mysql_query($sqln) or die("Erro ao buscar informações do nucleo");
$rown=mysql_fetch_assoc($resn);
*/	
	
//INSERIR DADOS DA COMUNICAÇÃO
/* RETIRAR HERMES
$sql1 = "insert into comunicacaoext (idempresa,tipo,`from`,`to`,idobjeto,tipoobjeto,status,criadoem,criadopor)
								values (".$_SESSION["SESSAO"]["IDEMPRESA"].",'".$sqlintipo."','SISLAUDO','".$sqlemail."',".$idnucleo.",'nucleo','ENVIANDO',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."')";
mysql_query($sql1) or die("erro ao inserir Log na tabela de comunicação de ENVIANDO [".mysql_error()."] ".$sql1);
$newidcomunicacao = mysql_insert_id();
	
if(!empty($newidcomunicacao)){
		
	$sqlf=" select  r.idresultado
					from resultado r,amostra a,pessoa p,pessoa s
					where p.idpessoa = a.idpessoa
						and s.idpessoa = r.idsecretaria
						and not exists(select 1 from resultado rr USE INDEX (idamostra_status),prodserv pp  where  pp.notoficial = 'Y' and rr.idtipoteste= pp.idprodserv and rr.idamostra = a.idamostra  and rr.idsecretaria != '' and r.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO'))
						and r.status = 'ASSINADO'
						and r.idamostra = a.idamostra
						".$sqlalerta."
						and r.idsecretaria=".$idsecretaria."
						and a.idpessoa = ".$idpessoa."
						and a.idnucleo ='".$idnucleo."'
						and a.exercicio = '".$exercicio."'
						and not exists(
										select 1 from comunicacaoext c,comunicacaoextitem i
										where c.tipo = '".$sqlintipo."'
										and c.status ='SUCESSO'
										and c.idcomunicacaoext = i.idcomunicacaoext
										and i.tipoobjeto = 'resultado'
										and i.idobjeto = r.idresultado
										)
						and a.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."";
	$resf=mysql_query($sqlf) or die("Erro ao buscar resultados enviados sql=".$sqlf);
	
	$qtdresf=mysql_num_rows($resf);
	if($qtdresf<1){
		die("Resultado já enviado ou não existem resultados pendentes para envio... Verificar com administrador do sistema");
	}
	
	
	while ($rowf=mysql_fetch_assoc($resf)){
		$sqlu="INSERT INTO `comunicacaoextitem` (idempresa,idcomunicacaoext,idobjeto,tipoobjeto)
						values
						(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$newidcomunicacao.",".$rowf['idresultado'].",'resultado')";
			
		mysql_query($sqlu) or die("erro ao vincular comunicação ao resultado erro [".mysql_error()."] ".$sqlu);
	}
		
		
		
}else{
	die("Falha ao gerar comunicação externa!");
}

/*
 * TRATAMENTO DOS PARAMETROS GET PARA CONCATENACAO POSTERIOR COM SQL
 */
/* RETIRAR HERMES
if(!empty($newidcomunicacao)){
		$sqlin .=" and r.idresultado in (
					select coi.idobjeto from comunicacaoextitem coi
					where coi.tipoobjeto = 'resultado'
					and coi.idobjeto = r.idresultado
					and coi.idcomunicacaoext = ".$newidcomunicacao.") ";
		$nomearq="comext".$newidcomunicacao;
}


if(empty($sqlin)){
	die ("Parâmentros necessarios não informados para a consulta.");
}
 * RETIRAR HERMES
*/

$sqlin=' and r.idresultado='.$_GET["idresultado"];

 $sql ="select 
		a.idempresa
 		,a.idunidade
		,p.nome
 		,p.razaosocial
		,a.idregistro
		,a.idamostra
		,a.exercicio
		,a.idpessoa
		,a.idtipoamostra
		,a.idsubtipoamostra
		,dma(a.datacoleta) as datacoleta
		,a.dataamostra
		,a.nroamostra
		,a.origem
		,a.lote
		,a.idade
		,a.tipoidade
		,a.observacao
		,a.descricao
		,a.lacre
		,a.galpao
		,a.numgalpoes
		,a.alojamento
		,a.linha
		,a.responsavelof
		,a.responsavel
		,a.tipo
		,a.nroplacas
		,a.diluicoes
		,a.tipoaves
		,a.identificacaochip
		,a.sexo
		,a.datafabricacao
		,a.partida
		,a.nrodoses
		,a.especificacao
		,a.fornecedor
		,a.localcoleta
		,a.localexp
		,dma(a.datacoleta) as datacoleta
		,a.tc
		,a.semana
		,a.notafiscal
		,a.vencimento
		,a.fabricante
		,a.sexadores
		,a.cpfcnpjprod
		,a.uf
		,a.cidade
		,a.pedido
		,a.criadopor
		,a.criadoem
		,a.alteradopor
		,a.alteradoem
 		,a.estexterno
 		,a.clienteterceiro
 		,a.nucleoorigem
 		,a.idwfxprocativ
 		,r.idservicoensaio
		,r.gmt
		,r.padrao
		,t.tipoamostra
		,tt.tipoteste
		,tt.sigla
		,tt.tipogmt
		,tt.tipoespecial
		,tt.geralegenda
		,date_format(a.dataamostra,'%d/%m/%Y') AS dataamostraformatada
		,r.descritivo
		,r.gmt
		,r.idresultado
		,r.idt
		,r.idtipoteste
		,r.padrao
		,r.q1
		,r.q10
		,r.q11
		,r.q12
		,r.q13
		,r.q2
		,r.q3
		,r.q4
		,r.q5
		,r.q6
		,r.q7
		,r.q8
		,r.q9
		,r.status
		,r.var
		,tt.geragraf
		,tt.geracalc
		,st.subtipoamostra
		,st.normativa
		,if((t.tipoamostra = st.subtipoamostra),t.tipoamostra,concat(t.tipoamostra,' Subtipo:',st.subtipoamostra)) AS tipoamostraformatado
		,r.quantidade as quantidadeteste
		,tt.textopadrao
		,tt.tipobact
		,r.idsecretaria
		,a.granja
		,n.idnucleo
		,n.nucleo
		,a.regoficial
		,a.nsvo
		,a.nucleoamostra
		,ef.finalidade
		,r.interfrase
 		,tt.logoinmetro
		,ef.idespeciefinalidade
		,concat(ef.especie,'-',ef.tipoespecie,'-',ef.finalidade) as especiefinalidade
                ,r.versao
	from 
		
			(
				(resultado r 
				,amostra a
				,pessoa p
				,tipoamostra t
				,subtipoamostra st
				,vwtipoteste tt) 
					left join nucleo n on (n.idnucleo = a.idnucleo))
				left join especiefinalidade ef on (ef.idespeciefinalidade=a.idespeciefinalidade)
	where r.idamostra = a.idamostra
		-- and r.status = 'ASSINADO'
		and p.idpessoa = a.idpessoa
		and t.idtipoamostra = a.idtipoamostra
		and st.idsubtipoamostra = a.idsubtipoamostra
		and tt.idtipoteste = r.idtipoteste
		".$sqlin."	
		order by a.idpessoa, a.exercicio, a.idregistro desc , tt.tipoteste";

if($echosql){echo "<!-- " . $sql . "  -->";}


$res = mysql_query($sql) or die("Falha no Relatório de Testes: " . mysql_error() . "<p>SQL: $sql");
$qtd = mysql_num_rows($res);
if (empty($qtd)){
	echo 'Não existe nenhum registro para os parâmetros informados!';
	die;
}

/*
 * PARAMETROS GERAIS
 */
$codepress = md5(date('dmYHis')); //gera um codigo para a impressao

?>
<html>
<head>
<meta name="viewport" content="width=1024px">
<title>Resultado - Impresso [<?=$codepress;?>]</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../inc/css/emissaoresultadopdf.css" rel="stylesheet" type="text/css" />
		<link href="../inc/css/print2.css" rel="stylesheet" type="text/css" />

<style>
html{
	margin: 0px;
	padding: 0px;
}
body{
	margin: 0px;
	padding: 0px;
}

</style>

</head>
<body>
<?

$pb = ""; //controla a quebra de paginas
$irestotal = mysql_num_rows($res); //armazena o total de paginas

$ipage = 0; //controla o numero dap agina atual
//$irestotal = 1;

while($row = mysql_fetch_array($res)){



	//Se for uma amostar com idunidade = 4 trata-se de uma amostra do bioterio
	/*
	if($row['idunidade']==4 and !empty($row['idservicoensaio'])){
		//Buscar a idade para o bioterio
		$sqlb="SELECT (DATEDIFF(s.data,ss.data)) AS idade,if(s.dia is null,'',concat('D',s.dia)) as rotulo,dma(s.data) as dmadata
			from servicoensaio ss,servicoensaio s
			where ss.servico = 'TRANSFERENCIA'
			and	ss.idbioensaio = s.idbioensaio
			and s.idservicoensaio =".$row['idservicoensaio'];
		$resb=mysql_query($sqlb) or die("Erro ao buscar a idade para o biotério sql".$sqlb);
		$rowb=mysql_fetch_assoc($resb);
		if(empty($rowb['rotulo'])){
			$row['idade']=$rowb['idade'];
			$row['dataamostraformatada']=$rowb['dmadata'];
		}else{
			$row['idade']=$rowb['rotulo'];
			$row["tipoidade"]="";
			$row['dataamostraformatada']=$rowb['dmadata'];
		}
		//$row["nroamostra"]=$row['quantidadeteste'];
		//echo($sqlb);
	}
	*/
	// inserir informações do lote no resultado
	if(!empty($row['idwfxprocativ'])){		
		$row['partida']=		$rc["vwpartidaamostra"]["res"]["partidaext"];
		$row['nucleoamostra']=	$rc["vwpartidaamostra"]["res"]["partida"];
		$row['lote']=			$rc["vwpartidaamostra"]["res"]["partidaext"];
	}

	$ipage = 0; //controla o numero dap agina atual
	$irestotal = 1;

	/*
	 * As condicoes abaixo invocam as funcoes para montagem das emissoes conforme o tipo especial
	 */
	if($row["tipoespecial"]=="DESCRITIVO"){ 
		imppaginisup();//inicia o controle da impressao: paginacao e controle encriptado
		cabecalhores();//monta o cabeçalho
		reldescritivo($mostraass);
		imppagrodape(1);//finaliza a pagina
	}

	if($row["tipoespecial"]== "BRONQUITE" or $row["tipoespecial"]=="NEWCASTLE" or $row["tipoespecial"]=="GUMBORO" or $row["tipoespecial"]=="REOVIRUS" or $row["tipoespecial"]=="PNEUMOVIRUS"			
or $row["tipoespecial"]=='GUMBORO IND' or $row["tipoespecial"]=='BRONQUITE IND' or $row["tipoespecial"]=='NEWCASTLE IND' or $row["tipoespecial"]=='PNEUMOVIRUS IND' or $row["tipoespecial"]=='REOVIRUS IND'
or $row["tipoespecial"]=="PESAGEM" or $row["tipoespecial"]=="ALFA" or $row["tipoespecial"]=="DESCRITIVO IND" ){//Se for GMT
		imppaginisup();//inicia o controle da impressao: paginacao e controle encriptado
		cabecalhorespdf();//monta o cabeçalho
		relespecial($mostraass);
		imppagrodape();//finaliza a pagina
	}//se GMT

	//Se o tipo especial for ELISA ou ELISASGMT (Elisa S/ GMT) adicionado a pedido do Daniel
	if($row["tipoespecial"]=="ELISA" or $row["tipoespecial"]=="ELISASGMT"){//Se for elisa, quebrar a tabela em partes iguais para nao gerar paginas 'soltas' na impressao

		relelisa($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"],$row["idespeciefinalidade"],$mostraass,$row["textopadrao"]);


	}
	
	
	
	/* * INDICACAO DE VISUALIZACAO DO RESULTADO */ 				
	$sqlvis = "insert into resultadovisualizacao (idresultado, idpessoa,criadoem,email) 
	values (".$row["idresultado"].",".$lppag_idpessoa.",now(),'".$_SESSION["SESSAO"]["EMAIL"]."')"; 
	$resvis = mysql_query($sqlvis); 
	if(!$resvis){
		if($echosql){
			echo "<!-- ".mysql_error()." -->";
		} 
	} 

}//while($row = mysql_fetch_array($res)){
?>
</body>
</html>

<?

	$html = ob_get_contents();
	//limpar o codigo html
	$html = preg_replace('/>\s+</', "><", $html);

	ob_end_clean();
	define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");
	define("DOMPDF_ENABLE_HTML5PARSER", true);
	define("DOMPDF_ENABLE_FONTSUBSETTING", true);
	define("DOMPDF_UNICODE_ENABLED", true);
	define("DOMPDF_DPI", 120);
	define("DOMPDF_ENABLE_REMOTE", true);
	define("DEBUGCSS", true);
//echo $html;
//die();
	// Incluímos a biblioteca DOMPDF
require_once("../inc/dompdf/dompdf_config.inc.php");
	
	// require_once "../inc/dompdf3/vendor/autoload.php";
//	use Dompdf\Dompdf;

	// Instanciamos a classe
	$dompdf = new DOMPDF();
	 
	// Passamos o conteúdo que será convertido para PDF
	$html=preg_match("//u", $html)?utf8_decode($html):$html; //MAF060519: Converter para ISO8859-1. @todo: executar upgrade no dompdf
	$dompdf->load_html($html); 

	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	$dompdf->set_paper('A4','portrait');
	 
	// O arquivo é convertido
	 $dompdf->render();
	
	if($gravaarquivo=='Y'){
	// Salvo no diretório  do sistema
	    $output = $dompdf->output();
	    file_put_contents("/var/www/carbon8/tmp/resultadopdf/resultado".$nomearq.".pdf",$output);
	    echo($newidcomunicacao);
	}else{   
	// Exibido para o usuário
		$dompdf->stream("resultado_".$nomearq.".pdf");
	}	
?>
