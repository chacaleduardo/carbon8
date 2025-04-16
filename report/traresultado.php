<?
require_once("../inc/php/validaacesso.php");
//ini_set("display_errors",true);
//error_reporting(E_ALL^E_NOTICE);
require_once("../inc/php/laudo.php");
require_once("reltesteinc.php");

/*
 * PARAMETROS GET
 */
$exercicio	= $_GET["exercicio"];
$idpessoa	= $_GET["idpessoa"];
$idsecretaria	= $_GET["idsecretaria"];
$idtipoteste	= $_GET["idtipoteste"];
$idresultado	= $_GET["idresultado"];
$controle	= $_GET["controle"];
$idnucleo	= $_GET["idnucleo"];
$nucleoamostra	= $_GET["nucleoamostra"];
$lote	      = $_GET["lote"];
$sort		= $_GET["sort"];
$tipogmt	= $_GET["tipogmt"];
$nome=$_GET["nome"];

$idunidade	= $_GET["idunidade"];
$idregistro	= $_GET["idregistro"]; //Para casos em que os parametros vierem de uma tela de filtro (listagem)
$idregistro_1	= $_GET["idregistro_1"];
$idregistro_2	= $_GET["idregistro_2"];

$dataamostra_1	= $_GET["dataamostra_1"];
$dataamostra_2	= $_GET["dataamostra_2"];

$idade			= $_GET["idade"];
$tc			= $_GET["tc"];
$partida		= $_GET["partida"];
$idtipoamostra	= $_GET["idtipoamostra"];
$idsubtipoamostra	= $_GET["idsubtipoamostra"];
$lacre=$_GET["lacre"];
$tc=$_GET["tc"];
$mostracabecalho = $_GET['mostracabecalho'];

$chkoficial = $_GET["chkoficial"];
$impoficial=$_GET['impoficial'];//vem da /forms/imprimirresoficial.php
$idcomunicacaoext=$GET_["idcomunicacaoext"];
$idregistrob=$_GET["idregistrob"];
$exerciciobiot	= $_GET["exerciciobiot"];
$status = $_GET['status'];

$echosql = false;

$mostraass=true;
$fromnf="";
$straight_join="";


$sqlp="select * from pessoa where idpessoa =".$_SESSION["SESSAO"]["IDPESSOA"];
$resp = d::b()->query($sqlp) or die("Falha ao buscar dados do acesso: " . mysql_error() . "<p>SQL: $sqlp");
$rowp=mysql_fetch_assoc($resp);

if($rowp['status']!='ATIVO'){
	die('Usuário inativo no sistema.');
}

/*
 * TRATAMENTO DOS PARAMETROS GET PARA CONCATENACAO POSTERIOR COM SQL
 */
if(!empty($controle)){
	// desta forma para dar index pelo nf
	$sqlin .= " and ni.idresultado = r.idresultado
			and  nf.idnotafiscal = ni.idnotafiscal 
			and nf.numerorps = '".$controle."'";
	
	$fromnf ="notafiscal nf
			,notafiscalitens ni 
			,";
	
	$straight_join=" /*! STRAIGHT_JOIN */ ";
	
}elseif(!empty($idresultado)){
	$sqlin .= " and r.idresultado =".$idresultado;	
	$straight_join=" /*! STRAIGHT_JOIN */ ";
}elseif(!empty($exercicio) and !empty($idregistro)){
	
	$sqlin .=" and a.idregistro = ".$idregistro;
	$sqlin .=" and a.exercicio ='".$exercicio."'";

}elseif(!empty($idregistro_1) and !empty($idregistro_2) and !empty($exercicio)){
	$sqlin .=" and a.idregistro BETWEEN ".$idregistro_1." AND ".$idregistro_2;
	$sqlin .=" and a.exercicio ='".$exercicio."'";	
	
}elseif(!empty($exercicio)){
	$sqlin .=" and a.exercicio ='".$exercicio."'";
}
if(!empty($idcomunicacaoext)){
		$sqlin .=" and r.idresultado in (
					select coi.idobjeto from comunicacaoextitem coi,comunicacaoext co
					where co.status = 'SUCESSO'
					and co.idcomunicacaoext = coi.idcomunicacaoext 
					and coi.tipoobjeto = 'resultado'
					and coi.idcomunicacaoext = ".$idcomunicacaoext.") ";
}

if(!empty($dataamostra_1) and !empty($dataamostra_2)){
	
	$dataini = validadate($dataamostra_1);
	$datafim = validadate($dataamostra_2);
	
	if ($dataini and $datafim){
		$sqlin .= " and (a.dataamostra  BETWEEN '" . $dataini ."' and '" .$datafim ."') ";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}

if(!empty($_GET["_vids"])){
	$sqlin .= " and  r.idresultado in (" . mysql_real_escape_string($_GET["_vids"]) .") ";
}


/*
 * quando for secretaria não colocar idpessoa, 
 * porque no token e informado o idpessoa da secretaria então não pode usar ele para buscar no idpessoa da amostra,
 * este idpessoa e informado na pesquisa a selecionar um cliente para busca
 */ 
if(!empty($idpessoa) and $rowp['idtipopessoa']!='10'  and $rowp['idtipopessoa']!='3'){
	$sqlin .=" and a.idpessoa = ".$idpessoa." ";
}

if(!empty($idpessoa) and $rowp['idtipopessoa']=='10'){
	$sqlin .=" and r.idsecretaria = ".$idpessoa." ";
}

if(!empty($idnucleo)){
	$sqlin .=" and a.idnucleo = ".$idnucleo." ";
}

if(!empty($status)){
	$sqlin.= " and r.status='".$status."' ";
}
if(!empty($idsecretaria)){
	$sqlin .=" and r.idsecretaria= ".$idsecretaria." ";
}

if(!empty($idtipoteste)){
	$sqlin .=" and r.idtipoteste = ".$idtipoteste." ";
}

if (!empty($nome)){
	$sqlin .= " and p.nome like ('%".$nome."%')  ";
}

if(!empty($idunidade)){
	$sqlin .=" and a.idunidade = ".$idunidade." ";
}

if(!empty($nucleoamostra)){
	$sqlin .=" and a.nucleoamostra like('%".$nucleoamostra."%') ";
}

if(!empty($lacre)){
	$sqlin .=" and a.lacre like('%".$lacre."%') ";
}

if(!empty($tc)){
	$sqlin .=" and a.tc like('%".$tc."%') ";
}

if(!empty($idregistrob) and !empty($exerciciobiot)){
	$fromnf=$fromnf." bioensaio b,servicoensaio s, ";
	$sqlin .=" and b.idregistro =".$idregistrob." and b.exercicio='".$exerciciobiot."'  and b.idbioensaio =s.idobjeto and tipoobjeto='bioensaio' and s.idamostra = r.idamostra "; 
}



if(empty($sqlin)){
	die ("Parâmentros necessarios não informados para a consulta.");
}

//verifica se o contato e do cliente para buscar o resultado em casos onde o acesso esta sendo realizado por um contato com um token
//(garantir que o usuario não veja resultados de outros clientes)
if($rowp["idtipopessoa"]==3 or $rowp["idtipopessoa"]==12){
	$sqlin.=" and exists (select 1 from pessoacontato c where c.idpessoa = a.idpessoa and c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"].") ";

}elseif($rowp["idtipopessoa"]==4){// e um contato oficial
	
	$sqlin.=" and exists (select 1 from pessoacontato c where c.idpessoa = r.idsecretaria and c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"].") ";

}elseif(($chkoficial=="S" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N')or($impoficial=="Y")){	//$impoficial vem da impressão de res oficial
		$sqlin .=" and r.status = 'ASSINADO'		
				and r.idsecretaria != ''
				and not exists(
					select 1
					from controleimpressaoitem c 
					where c.idresultado = r.idresultado 
						and c.oficial = 'S' 
						and c.via >= 1 and c.status = 'ATIVO'
				) ";	
}elseif($chkoficial=="N" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N'){
	$sqlin .=" and r.status = 'ASSINADO'	
				and not exists(
				select 1
				from controleimpressaoitem c 
				where c.idresultado = r.idresultado 
					and c.oficial = 'N' 
					and c.via >= 1 and c.status = 'ATIVO'
			) ";	
}elseif($chkoficial=="S"){
	$sqlin .=" and r.idsecretaria != '' ";
}

if($impoficial=="Y"){//$impoficial vem da impressão de res oficial
	$sqlin .=" and not exists(
						select 1 from resultado rr  USE INDEX (idamostra_status) ,amostra aa
						where rr.idamostra = aa.idamostra 
			 			and rr.idsecretaria != ''
						and rr.status in('FECHADO','ABERTO','PROCESSANDO','AGUARDANDO')
						and aa.idnucleo = a.idnucleo
						) ";

	$chkoficial="S";
}

// $sql = " select /*! STRAIGHT_JOIN */ * from vwreltesteassinado  where idamostra in(". $sqlin .") ".$sqloficial." order by idpessoa, exercicio, idregistro , tipoteste";

 $sql ="select ".$straight_join."
		r.idresultado
		,rj.jresultado
	from 
		
			(
				(".$fromnf."
				resultado r 
				,resultadojson rj
				,amostra a
				,pessoa p
				,tipoamostra t
				,subtipoamostra st
				,vwtipoteste tt) 
					left join nucleo n on (n.idnucleo = a.idnucleo))
				left join especiefinalidade ef on ef.idespeciefinalidade=a.idespeciefinalidade
	where r.idamostra = a.idamostra
		and rj.idresultado=r.idresultado
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

//buscar os resultados agrupar por TC e tipoamostra para montar a ultima folha da impressão com os dados dos resultado que foram impressos
if($impoficial=="Y"){
	// na impressão de resultado oficial deve-se buscar as informações antes de inserir a informação dos resultados ja impressos
	$sqlf1 ="select ".$straight_join."
		a.idempresa
 		,a.idunidade
		,p.nome
		,a.idregistro
		,a.idamostra
		,a.exercicio
		,a.idpessoa
		,a.idtipoamostra
		,a.idsubtipoamostra
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
		,a.datacoleta
		,a.tc
		,a.semana
		,a.notafiscal
		,a.vencimento
		,a.fabricante
		,a.sexadores
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
		,if((t.tipoamostra = st.subtipoamostra),t.tipoamostra,concat(t.tipoamostra,' Subtipo:',st.subtipoamostra)) AS tipoamostraformatado
		,r.quantidade as quantidadeteste
		,tt.textopadrao
		,tt.tipobact
		,r.idsecretaria
		,a.granja
		,n.idnucleo
		,n.nucleo
		,n.regoficial
		,a.nsvo
		,a.nucleoamostra
		,ef.finalidade
		,r.interfrase
 		,tt.logoinmetro
		,ef.idespeciefinalidade
		,concat(pl.plantel,'-',ef.finalidade) as especiefinalidade
		,rj.jresultado
		,r.versao
	from
	
			(
				(".$fromnf."
				resultado r
				,resultadojson rj
				,amostra a
				,pessoa p
				,tipoamostra t
				,subtipoamostra st
				,vwtipoteste tt)
				left join nucleo n on (n.idnucleo = a.idnucleo))
				left join especiefinalidade ef on (ef.idespeciefinalidade=a.idespeciefinalidade)
                                left join plantel on(pl.idplantel = ef.idplantel)
	where r.idamostra = a.idamostra
		and rj.idresultado=r.idresultado
		-- and r.status = 'ASSINADO'
		and p.idpessoa = a.idpessoa
		and t.idtipoamostra = a.idtipoamostra
		and st.idsubtipoamostra = a.idsubtipoamostra
		and tt.idtipoteste = r.idtipoteste
		".$sqlin."
		group by a.tc,tipoamostraformatado
		order by a.idpessoa, a.exercicio, a.idregistro desc ";
	$resf1 = mysql_query($sqlf1) or die("Falha no Relatório de Testes oficiais: " . mysql_error() . "<p>SQL: $sql");
	
}

/*
 * PARAMETROS GERAIS
 */
$codepress = md5(date('dmYHis')); //gera um codigo para a impressao

?>
<html>
<head>
<title>Resultado - Impresso [<?=$codepress;?>]</title>

	<link href="../inc/css/emissaoresultado.css" rel="stylesheet" type="text/css" />

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
//$irestotal = mysql_num_rows($res); //armazena o total de paginas

//se for funcionario e não visualizar resultado, a solicitação não vier da impressão de oficiais controla a impressão
if($rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N' and empty($impoficial)){
	controleimpressao($controle,$chkoficial);
}

while($resultado = mysql_fetch_array($res)){

	//Recupera os dados congelados no resultado, para serem apresentados na tela juntamente à versão gerada após STATUS=ASSINADO
	$rc= unserialize(base64_decode($resultado["jresultado"]));

	if(empty($rc)){
		echo "Teste não possui informação de resultado: [".$resultado["idresultado"]."]";
	}
	

	//Abre um $row com os dados da coluna jresultado
	$row["idempresa"]		=$rc["amostra"]["res"]["idempresa"];
	$row["idunidade"]		=$rc["amostra"]["res"]["idunidade"];
	$row["idregistro"]		=$rc["amostra"]["res"]["idregistro"];
	$row["idamostra"]		=$rc["amostra"]["res"]["idamostra"];
	$row["exercicio"]		=$rc["amostra"]["res"]["exercicio"];
	$row["idpessoa"]		=$rc["amostra"]["res"]["idpessoa"];
	$row["idtipoamostra"]	=$rc["amostra"]["res"]["idtipoamostra"];
	$row["idsubtipoamostra"]=$rc["amostra"]["res"]["idsubtipoamostra"];
	$row["datacoleta"]		=dma($rc["amostra"]["res"]["datacoleta"]);
	$row["nroamostra"]		=$rc["amostra"]["res"]["nroamostra"];
	$row["origem"]			=$rc["amostra"]["res"]["origem"];
	$row["lote"]			=$rc["amostra"]["res"]["lote"];
	$row["idade"]			=$rc["amostra"]["res"]["idade"];
	$row["tipoidade"]		=$rc["amostra"]["res"]["tipoidade"];
	$row["observacao"]		=$rc["amostra"]["res"]["observacao"];
	$row["descricao"]		=$rc["amostra"]["res"]["descricao"];
	$row["lacre"]			=$rc["amostra"]["res"]["lacre"];
	$row["galpao"]			=$rc["amostra"]["res"]["galpao"];
	$row["linha"]			=$rc["amostra"]["res"]["linha"];
	$row["responsavelof"]	=$rc["amostra"]["res"]["responsavelof"];
	$row["responsavel"]		=$rc["amostra"]["res"]["responsavel"];
	$row["tipo"]			=$rc["amostra"]["res"]["tipo"];
	$row["nroplacas"]		=$rc["amostra"]["res"]["nroplacas"];
	$row["diluicoes"]		=$rc["amostra"]["res"]["diluicoes"];
	$row["tipoaves"]		=$rc["amostra"]["res"]["tipoaves"];
	$row["identificacaochip"]=$rc["amostra"]["res"]["identificacaochip"];
	$row["sexo"]			=$rc["amostra"]["res"]["sexo"];
	$row["datafabricacao"]	=$rc["amostra"]["res"]["datafabricacao"];
	$row["partida"]			=$rc["amostra"]["res"]["partida"];
	$row["nrodoses"]		=$rc["amostra"]["res"]["nrodoses"];
	$row["especificacao"]	=$rc["amostra"]["res"]["especificacao"];
	$row["fornecedor"]		=$rc["amostra"]["res"]["fornecedor"];
	$row["localcoleta"]		=$rc["amostra"]["res"]["localcoleta"];
	$row["localexp"]		=$rc["amostra"]["res"]["localexp"];
	$row["tc"]				=$rc["amostra"]["res"]["tc"];
	$row["semana"]			=$rc["amostra"]["res"]["semana"];
	$row["notafiscal"]		=$rc["amostra"]["res"]["notafiscal"];
	$row["vencimento"]		=$rc["amostra"]["res"]["vencimento"];
	$row["fabricante"]		=$rc["amostra"]["res"]["fabricante"];
	$row["sexadores"]		=$rc["amostra"]["res"]["sexadores"];
	$row["cpfcnpjprod"]		=$rc["amostra"]["res"]["cpfcnpjprod"];
	$row["uf"]				=$rc["amostra"]["res"]["uf"];
	$row["cidade"]			=$rc["amostra"]["res"]["cidade"];
	$row["pedido"]			=$rc["amostra"]["res"]["pedido"];
	$row["criadopor"]		=$rc["amostra"]["res"]["criadopor"];
	$row["criadoem"]		=$rc["amostra"]["res"]["criadoem"];
	$row["alteradopor"]		=$rc["amostra"]["res"]["alteradopor"];
	$row["alteradoem"]		=$rc["amostra"]["res"]["alteradoem"];
	$row["estexterno"]		=$rc["amostra"]["res"]["estexterno"];
	$row["clienteterceiro"]	=$rc["amostra"]["res"]["clienteterceiro"];
	$row["nucleoorigem"]	=$rc["amostra"]["res"]["nucleoorigem"];
	$row["idwfxprocativ"]	=$rc["amostra"]["res"]["idwfxprocativ"];
	$row["dataamostra"]		=$rc["amostra"]["res"]["dataamostra"];
	$row["dataamostraformatada"]=dma($rc["amostra"]["res"]["dataamostra"]);
	$row["granja"]			=$rc["amostra"]["res"]["granja"];
	$row["nsvo"]			=$rc["amostra"]["res"]["nsvo"];
	$row["nucleoamostra"]	=$rc["amostra"]["res"]["nucleoamostra"];
	$row["idespeciefinalidade"]=$rc["especiefinalidade"]["res"]["idespeciefinalidade"];
	//$row["especiefinalidade"]=$rc["especiefinalidade"]["res"]["especie"]."-".$rc["especiefinalidade"]["res"]["tipoespecie"]."-".$rc["especiefinalidade"]["res"]["finalidade"];
	$row["especiefinalidade"]=$rc["especiefinalidade"]["res"]["especiefinalidade"];
	$row["finalidade"]		=$rc["especiefinalidade"]["res"]["finalidade"];
	$row["idnucleo"]		=$rc["nucleo"]["res"]["idnucleo"];
	$row["nucleo"]			=$rc["nucleo"]["res"]["nucleo"];
	$row["regoficial"]		=$rc["nucleo"]["res"]["regoficial"];
	$row["quantidadeteste"]      =$rc["resultado"]["res"]["quantidade"];
	$row["nome"]			=$rc["pessoa"]["res"]["nome"];
	$row["razaosocial"]		=$rc["pessoa"]["res"]["razaosocial"];
	$row["idservicoensaio"]	=$rc["resultado"]["res"]["idservicoensaio"];
	$row["gmt"]				=$rc["resultado"]["res"]["gmt"];
	$row["padrao"]			=$rc["resultado"]["res"]["padrao"];
	$row["descritivo"]		=$rc["resultado"]["res"]["descritivo"];
	$row["gmt"]				=$rc["resultado"]["res"]["gmt"];
	$row["idresultado"]		=$rc["resultado"]["res"]["idresultado"];
	$row["idt"]				=$rc["resultado"]["res"]["idt"];
	$row["idtipoteste"]		=$rc["resultado"]["res"]["idtipoteste"];
	$row["padrao"]			=$rc["resultado"]["res"]["padrao"];
	$row["q1"]				=$rc["resultado"]["res"]["q1"];
	$row["q10"]				=$rc["resultado"]["res"]["q10"];
	$row["q11"]				=$rc["resultado"]["res"]["q11"];
	$row["q12"]				=$rc["resultado"]["res"]["q12"];
	$row["q13"]				=$rc["resultado"]["res"]["q13"];
	$row["q2"]				=$rc["resultado"]["res"]["q2"];
	$row["q3"]				=$rc["resultado"]["res"]["q3"];
	$row["q4"]				=$rc["resultado"]["res"]["q4"];
	$row["q5"]				=$rc["resultado"]["res"]["q5"];
	$row["q6"]				=$rc["resultado"]["res"]["q6"];
	$row["q7"]				=$rc["resultado"]["res"]["q7"];
	$row["q8"]				=$rc["resultado"]["res"]["q8"];
	$row["q9"]				=$rc["resultado"]["res"]["q9"];
	$row["status"]			=$rc["resultado"]["res"]["status"];
	$row["var"]				=$rc["resultado"]["res"]["var"];
	$row["idsecretaria"]	=$rc["resultado"]["res"]["idsecretaria"];
	$row["interfrase"]		=$rc["resultado"]["res"]["interfrase"];
        $row["versao"]                  =$rc["resultado"]["res"]["versao"];
	$row["subtipoamostra"]	=$rc["subtipoamostra"]["res"]["subtipoamostra"];
	$row["normativa"]		=$rc["subtipoamostra"]["res"]["normativa"];
	$row["tipoamostra"]		=$rc["tipoamostra"]["res"]["tipoamostra"];
	$row["tipoamostraformatado"]=($row["tipoamostra"]==$row["subtipoamostra"])?$row["tipoamostra"]:$row["tipoamostra"]." Subtipo:".$row["subtipoamostra"];
	$row["tipoteste"]		=$rc["prodserv"]["res"]["tipoteste"];
	$row["sigla"]			=$rc["prodserv"]["res"]["sigla"];
	$row["tipogmt"]			=$rc["prodserv"]["res"]["tipogmt"];
	$row["tipoespecial"]	=$rc["prodserv"]["res"]["tipoespecial"];
	$row["geralegenda"]		=$rc["prodserv"]["res"]["geralegenda"];
	$row["geragraf"]		=$rc["prodserv"]["res"]["geragraf"];
	$row["geracalc"]		=$rc["prodserv"]["res"]["geracalc"];
	$row["textopadrao"]		=$rc["prodserv"]["res"]["textopadrao"];
	$row["tipobact"]		=$rc["prodserv"]["res"]["tipobact"];
	$row["logoinmetro"]		=$rc["prodserv"]["res"]["logoinmetro"];

	$rowbio		=$rc["bioensaio"]["res"];
	$rowend		=$rc["endereco"]["res"];
	$rtitulos	=$rc["titulos"]["res"];
	$arrgrafgmt	=$rc["hist_gmt"]["res"];
	$arrelisa	=$rc["resultadoelisa"]["res"];
	$arrelisagr1=$rc["resultadoelisa_graf1"]["res"];
	$arrelisagr2=$rc["resultadoelisa_graf2"]["res"];
	$arrassinat	=$rc["resultadoassinatura"]["res"];

	//Se for impressao oficial for funcionario e não vizualizar resultado controla a impressão
	if($impoficial=="Y" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N'){
		controleimpressaooficial($row['idregistro']);
	}

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
or $row["tipoespecial"]=="PESAGEM" or $row["tipoespecial"]=="ALFA" or $row["tipoespecial"]=="DESCRITIVO IND"){//Se for GMT
		imppaginisup();//inicia o controle da impressao: paginacao e controle encriptado
		cabecalhores();//monta o cabeçalho
		relespecial($mostraass);
		imppagrodape();//finaliza a pagina
	}//se GMT

	//Se o tipo especial for ELISA ou ELISASGMT (Elisa S/ GMT) adicionado a pedido do Daniel
	if($row["tipoespecial"]=="ELISA" or $row["tipoespecial"]=="ELISASGMT"){//Se for elisa, quebrar a tabela em partes iguais para nao gerar paginas 'soltas' na impressao


		relelisa($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"],$row["idespeciefinalidade"],$mostraass);


	}
	
	//se for funcionario e não visualizar resultado e a solicitação não vier da impressão oficial controla a impressão
	if($rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N' and empty($impoficial)){
		controleimpressaoitem($controle,$chkoficial,$row["idresultado"]);
	}
	//se vier da impressão dos oficiais for funcionario e não visualizar resultado controla a impressão
	if($impoficial=="Y" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N'){
		controleimpressaoitemoficial($row['idregistro'],$row["idresultado"]);
	}
	
	
	/* * INDICACAO DE VISUALIZACAO DO RESULTADO */ 				
	$sqlvis = "insert into resultadovisualizacao (idresultado, idpessoa,criadoem,email) 
	values (".$row["idresultado"].",".$_SESSION["SESSAO"]["IDPESSOA"].",now(),'".$_SESSION["SESSAO"]["EMAIL"]."')"; 
	$resvis = mysql_query($sqlvis); 
	if(!$resvis){
		if($echosql){
			echo "\n<!-- Resultado Visualização: ".mysqli_error(d::b())."\nSql:".$sqlvis." -->";
		} 
	} 

}//while($row = mysql_fetch_array($res)){

//imprimir relatorio da impressão para resultados oficiais
if($impoficial=="Y"){

	while($row=mysql_fetch_assoc($resf1)){

		if($cabecalho!=1){
?>
			<div class="nimptop" style="page-break-before: always;"></div>
			<br clear="all">	
			<div style="text-align: center; font-size: 20px; font-weight: bold;">RELAÇÃO DE TERMO(S) DE COLHEITA - TC</div>
			<table >
			<tr>			
				<td><p><br><p></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Aos cuidados do Dr(a). Fiscal,</td>
			</tr>
			<tr>
				<td nowrap >Segue relação do(s) cliente(s) e do(s) termo(s) de colheita (TC) que consta(m) neste:</td>
			</tr>
			</table>
		
			<table >
			<tr>			
				<td><p><br><p></td>
			</tr>
			<tr>
				<td nowrap style="font-weight:bold;">SECRETARIA OFICIAL:</td>
				<td ><?=traduzid("pessoa","idpessoa","nome",$row["idsecretaria"])?></td>
			</tr>
			</table>
	
	
			<table >
			<tr>
				<td nowrap style="font-weight:bold;" >CLIENTE:</td>
				<td ><?=$row["nome"]?></td>
			</tr>
			<tr>			
				<td><p><br></td>
			</tr>
			</table>
	<?
		}
	?>	
		<?if($row["tc"]!=$xtc){
			if(!empty($xtc)){
				echo("</table>");
			}
		?>
		<table style="display: inline; ">
		<?}?>
		
		<?if($row["tc"]!=$xtc){	
			$xtc=$row["tc"];
		?>
	
		<tr >			
			<td  style="width: 210px;"><p></td>
		</tr>
		<tr >
		<td nowrap="nowrap"  style="width: 210px;" ><font style="font-weight:bold;">TC:</font><?=$row["tc"]?></td>
		</tr>
		<?}?>
		<tr >
		<td nowrap="nowrap"  style="width: 210px;"><?=$row["tipoamostra"]?><?if($row["subtipoamostra"]!=$row["tipoamostra"]){?> <?=$row["subtipoamostra"]?><?}?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
		</tr>

	
	<?		
		$cabecalho=1;
	}
	?>
		</table>
		<table>
		<tr>			
			<td><p><br><p></td>
		</tr>
		<tr>			
			<td><p><br><p></td>
		</tr>
		<tr>			
			<td><p><br><p></td>
		</tr>
		</table>
		<table>
		<tr>			
			<td nowrap="nowrap" style="vertical-align:top;">Atenciosamente:</td>
		</tr>
		<tr>
			<td><p><br><p></td>
		</tr>
		<tr>			
			<td nowrap="nowrap" style="vertical-align:top;">_______________________________________</td>
		</tr>
		<tr>			
			<td nowrap="nowrap" style="vertical-align:top;">Laudo Laboratório Avícola Uberlândia Ltda</td>
		</tr>
		<tr>			
			<td nowrap="nowrap" style="vertical-align:top;">Data: <?echo(date("d/m/Y"));?></td>
		</tr>
	</table>	
	
	<?
}

?>
</body>
</html>
