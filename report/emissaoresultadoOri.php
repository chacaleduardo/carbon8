<?
session_start();

require_once("../inc/php/validaacesso.php");
require_once("../inc/php/graph/grafsoro.php");

require_once "reltesteinc.php";

$mostraass=true;//se deve mostrar a assinatura

$echosql = true;

$apagaDashboard=true;

$idpessoa = $_GET["idpessoa"];
$idregistrob=$_GET["idregistrob"];
$exerciciobiot	= $_GET["exerciciobiot"];

if(!empty($_GET["_vids"])){
	$clausula .= " r.idresultado in (" . mysql_real_escape_string($_GET["_vids"]) .") and ";
}

/*
 * Os par�metros abaixo est�o relacionados conforme os campos da pagina de pesquisa
 * Qualquer altera��o na pagina ou aqui deve ser colocada na ordem correta, para facilitar manuten��o
 */
if(!empty($_GET["exercicio"])){
	if(is_numeric($_GET["exercicio"])){
		$clausula .= " a.exercicio = " . $_GET["exercicio"] ." and ";
	}else{
		die ("O Exerc�cio informado possui caracteres inv�lidos: [".$_GET["exercicio"]."]");
	}
}

if (!empty($_GET["dataamostra_1"]) or !empty($_GET["dataamostra_2"])){
	$dataini = validadate($_GET["dataamostra_1"]);
	$datafim = validadate($_GET["dataamostra_2"]);
	if ($dataini and $datafim){
		$clausula .= " (a.dataamostra  BETWEEN '" . $dataini ."' and '" .$datafim ."')"." and ";
	}else{
		die ("A data informadas est�o em formato inv�lido. Verifique a formata��o (dd/mm/aaaa):\n<br>In�cio: ".$_GET["dataamostra_1"]."\n<br>T�rmino: ".$_GET["dataamostra_2"]);
	}
}


/*
 * quando for secretaria n�o colocar idpessoa, 
 * porque no token e informado o idpessoa da secretaria ent�o n�o pode usar ele para buscar no idpessoa da amostra,
 * este idpessoa e informado na pesquisa a selecionar um cliente para busca
 */ 

//print_r($arrtoken);print_r($_SESSION["SESSAO"]);die;

if(!empty($idpessoa) and $_SESSION["SESSAO"]["IDTIPOPESSOA"]!='4'  and $_SESSION["SESSAO"]["IDTIPOPESSOA"]!='10'){
	$clausula .=" a.idpessoa = ".$idpessoa." and ";
}

if(!empty($idpessoa) and $_SESSION["SESSAO"]["IDTIPOPESSOA"]=='4'){
	$clausula .=" r.idsecretaria = ".$idpessoa." and ";
}



if (!empty($_GET["idtipoteste"])){
	$clausula .= " r.idtipoteste = " .$_GET["idtipoteste"] ." and ";
}

if (!empty($_GET["lote"])){
	$clausula .= " a.lote = '" .$_GET["lote"] ."' and ";
}

if (!empty($_GET["nucleoamostra"])){
        $clausula .= " a.nucleoamostra = '" .$_GET["nucleoamostra"] ."' and ";
}

if (!empty($_GET["idtipoamostra"])){
	$clausula .= " a.idtipoamostra = " .$_GET["idtipoamostra"] ." and ";
}

$lacre=$_GET["lacre"];
$tc=$_GET["tc"];

if(!empty($lacre)){
	$clausula .=" a.lacre ='".$lacre."' and ";
}

if(!empty($_GET["idnucleo"]) or $_GET["idnucleo"]=="0"){
	$clausula .="  a.idnucleo = ".$_GET["idnucleo"]." and";

}

if (!empty($_GET["statusvisualizacao"])){

	$visualizadosjoin = " left join (select nvisualizado, idresultado from dashboardnucleopessoa where idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"].") d on (d.idresultado = r.idresultado) ";
	$clausula .= " if(ifnull(d.nvisualizado,0) >= 1,'N','Y') = '".$_GET["statusvisualizacao"]."' and ";

}

if (!empty($_GET["alerta"])){
	$clausula .= " r.alerta = '" .$_GET["alerta"] ."' and ";
}

//Esta condi��o deve ser exatamente igual � View que alimenta a pagina de pesquisa
if (!empty($_GET["flgoficial"])){
	$clausula .= " if((ifnull(trim(r.idsecretaria), '') = ''), 'N', 'Y') = '" .$_GET["flgoficial"] ."' and ";
}

if (!empty($_GET["idregistro_1"]) or !empty($_GET["idregistro_2"])){
	if (is_numeric($_GET["idregistro_1"]) and is_numeric($_GET["idregistro_2"])){
		$clausula .= " (a.idregistro BETWEEN " . $_GET["idregistro_1"] ." and " . $_GET["idregistro_2"] .")"." and ";
	}else{
		die ("Os N�s de Registro informados s�o inv�lidos: [".$_GET["idregistro_1"]."] e [".$_GET["idregistro_2"]."]");
	}
}

if(!empty($_GET["idcomunicacaoext"])){
		$clausula .=" r.idresultado in (
					select coi.idobjeto from comunicacaoextitem coi
					where coi.tipoobjeto = 'resultado'
					and coi.idobjeto = r.idresultado
					and coi.idcomunicacaoext = ".$_GET["idcomunicacaoext"].") and ";
}

if (!empty($_GET["idsubtipoamostra"])){
	$clausula .= " a.idsubtipoamostra = " .$_GET["idsubtipoamostra"] ." and ";
}

if (!empty($_GET["idade"])){
	$clausula .= " a.idade = '" .$_GET["idade"] ."' and ";
}

if (!empty($_GET["partida"])){
	$clausula .= " a.partida = '" .$_GET["partida"] ."' and ";
}

/*
 * Outros parametros
 */

//Para o caso onde o usuario clica em resultados em azul, o idresultado vem vazio. Ex: idresultado=
//Neste caso, deve-se mostrar mensagem de teste em analise
if(isset($_GET["idresultado"]) && empty($_GET["idresultado"])){

	// if(strlen()==0){
	die("Resultados em an�lise.");


}else{

	if(!empty($_GET["idresultado"])){
		$clausula .= " r.idresultado = ".$_GET["idresultado"]. " and ";	
	}
}

if(!empty($idregistrob) and !empty($exerciciobiot)){
	$fromnf=$fromnf." bioensaio b,servicoensaio s, ";
	$clausula.="  b.idregistro =".$idregistrob." and b.exercicio='".$exerciciobiot."' and b.idbioensaio =s.idobjeto and tipoobjeto='bioensaio' and r.idamostra =s.idamostra      ";
}

if(empty($clausula)){
	die("Nenhum par�metro enviado para a Emiss�o de Resultados.");
}

//parametros n�o utilizados 
/*
if (!empty($_GET["idregistro"])){
	if (is_numeric($_GET["idregistro"])){
		$clausula .= " a.idregistro = " .$_GET["idregistro"]." and ";

	}else{
		die ("O N� de Registro informado � inv�lido: [".$_GET["idregistro"]."]");
	}
}
if(!empty($_GET["controle"])){
	$clausula .= " nf.numerorps = ".$_GET["controle"]. " and ";	
}


if (!empty($_GET["idamostra"])){
	if (is_numeric($_GET["idamostra"])){
		$clausula .= " a.idamostra = " .$_GET["idamostra"]." and ";

	}else{
		die ("O idamostra informado � inv�lido: [".$_GET["idamostra"]."]");
	}
}

if (!empty($_GET["tc"])){
	$clausula .= " a.tc = '" .$_GET["tc"] ."' and ";
}
*/



/*
 * Restricao por tipo de usuario
 */
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==3 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==15){//usuariocliente

	$clausula .= " a.idpessoa in (select idpessoa from pessoacontato where idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"] .") and ";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==4){//contato oficial

	$clausula .= " r.idsecretaria in(".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"].") and ";
	$clausula .= " a.idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].") and ";
	$clausuladashboard = " and r.idsecretaria in (".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"].")";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==10){//Secretaria
	//TODO: Nesse caso deixar liberado. Verificar posteriormente as implica��es. Combinado com daniel em 031215 en car�ter urgente
}
/*else{
	die("[l:".__LINE__."]: idtipopessoa [".$_SESSION["SESSAO"]["IDTIPOPESSOA"]."] n�o previsto.");
}*/

/*
 * Restri��o conforme dashboard
 * Aten��o: caso o status da visualiza��o seja enviado, deve-se restringir a consulta pelo "idcliente" para que a quantidade de resultados seja equivalente ao dashboard
 */
if (!empty($_GET["statusvisualizacao"])){

	if(!empty($idpessoa)){
		$clausclidash = "and a.idpessoa = ".$idpessoa." -- O status de Visualiza��o foi enviado, portanto deve-se concatenar o cliente \n";
	}
	
	$visualizadosjoin = " left join dashboardnucleopessoa d 
								on (d.idresultado = r.idresultado 
									and d.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]." ".$clausuladashboard.") ";
	$clausula .= " if(ifnull(d.nvisualizado,0) >= 1,'N','Y') = '".$_GET["statusvisualizacao"]."' and ";

}



/*
 * TRATAMENTO E CONCATENACAO DO SQL PRINCIPAL
 */

if (!empty($clausula)){
	$clausula = " and " . substr($clausula,1,strlen($clausula) - 5);
}

//$sql = " select * from vwreltesteassinado a " . $clausula . " order by nome, exercicio, idregistro ".$_GET["sort"].", tipoteste ";

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
		,a.criadopor
		,a.criadoem
		,a.alteradopor
		,a.alteradoem
 		,a.estexterno
 		,a.clienteterceiro
 		,a.nucleoorigem
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
		,n.regoficial
		,a.nsvo
		,a.nucleoamostra
		,n.finalidade
		,r.interfrase
 		,tt.logoinmetro
		,ef.idespeciefinalidade
		,concat(ef.especie,'-',ef.tipoespecie,'-',ef.finalidade) as especiefinalidade
	from 
	(
		(
			(
				(".$fromnf."
				resultado r 
				,amostra a
				,pessoa p
				,tipoamostra t
				,subtipoamostra st
				,vwtipoteste tt) 
					left join nucleo n on (n.idnucleo = a.idnucleo)
				left join especiefinalidade ef on (ef.idespeciefinalidade=a.idespeciefinalidade)
			 ) left join notafiscalitens ni on (ni.idresultado = r.idresultado)
		) left join notafiscal nf on (nf.idnotafiscal = ni.idnotafiscal )
	)  ".$visualizadosjoin."

	where r.idamostra = a.idamostra
		and p.idpessoa = a.idpessoa
		and t.idtipoamostra = a.idtipoamostra
		and st.idsubtipoamostra = a.idsubtipoamostra
		and tt.idtipoteste = r.idtipoteste
		and r.status = 'ASSINADO'
		-- and a.exercicio = 2011
		-- and nf.idnotafiscal = ni.idnotafiscal 
		".$clausula."
		".$clausclidash."
		order by a.idpessoa, a.exercicio, a.idregistro , tt.tipoteste";

if($echosql){echo "<!-- " . $sql . "  -->";}

//echo '<pre>' . print_r(get_defined_vars(), true) . '</pre>';die;

$res = mysql_query($sql) or die("Falha no Relat�rio de Testes: " . mysql_error() . "<p>SQL: $sql");
$qtd = mysql_num_rows($res);
if (empty($qtd)){
?>
<link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
<br>
<div class="col-md-6">
	<div class="alert alert-warning aviso" role="alert">

	<strong><i class="glyphicon glyphicon-info-sign"></i> Esta amostra est� sendo processada.
	<br/>
	<br/>Em caso de d�vida, entre em contato conosco
	<br/>atrav�s do email: resultados@laudolab.com.br 
	<br/>ou telefone: (34) 3222 5700
	</div>
</div>
<?
}

/*
 * PARAMETROS GERAIS
 */
$codepress = md5(date('dmYHis')); //gera um codigo para a impressao

?>
<html>
<head>
<title>Resultado - Impresso [<?=$codepress;?>]</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
<body style="<?=$strstyle?>">
<style media="screen">
.btimprimir {
    background-color: window;
    background-image: url("../inc/img/printer38.png");
    background-position: 50% 6px;
    background-repeat: no-repeat;
    height: 55px;
    margin-right: 10px;
    position: fixed;
    right: 0;
    width: 56px;
	cursor: pointer;
	-webkit-border-bottom-right-radius: 4px;
	-webkit-border-bottom-left-radius: 4px;
	-moz-border-radius-bottomright: 4px;
	-moz-border-radius-bottomleft: 4px;
	border-bottom-right-radius: 4px;
	border-bottom-left-radius: 4px;
}
.btimprimir label {
    bottom: 0;
    color: #486b8a;
    font-size: 10px;
    position: absolute;
    text-align: center;
    width: 100%;
	margin-bottom: 2px;
}
	
.btimprimir:hover {
	box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}
</style>
<style media="print">
.btimprimir, .btimprimir label{
	display: none;
}
</style>

<div class="btimprimir" onClick="window.print()">
	<label>Imprimir</label>
</div>
<?
$ipage = 0; //controla o numero dap agina atual
$pb = ""; //controla a quebra de paginas
$boopb = false;

/*
 *maf200811: onforme orientacao de Andre, o n�mero de p�ginas deve ser resetado a cada teste.
 */
//$irestotal = mysql_num_rows($res); //aramazena o total de paginas

while($row = mysql_fetch_array($res)){

	//Se for uma amostar com idunidade = 4 trata-se de uma amostra do bioterio
	if($row['idunidade']==4 and !empty($row['idservicoensaio'])){
		//Buscar a idade para o bioterio
		$sqlb="SELECT (DATEDIFF(s.data,ss.data)) AS idade,if(s.dia is null,'',concat('D',s.dia)) as rotulo
			from servicoensaio ss,servicoensaio s
			where ss.servico = 'TRANSFERENCIA'
			and	ss.idbioensaio = s.idbioensaio
			and s.idservicoensaio =".$row['idservicoensaio'];
		$resb=mysql_query($sqlb) or die("Erro ao buscar a idade para o biot�rio sql".$sqlb);
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

	$ipage = 0; //controla o numero dap agina atual
	$irestotal = 1;

	/*
	 * As condicoes abaixo invocam as funcoes para montagem das emissoes conforme o tipo especial
	 */
	if($row["tipoespecial"]=="DESCRITIVO"){ 
		imppaginisup();//inicia o controle da impressao: paginacao e controle encriptado
		cabecalhores();//monta o cabe�alho
		reldescritivo($mostraass);
		imppagrodape();//finaliza a pagina
	}

	if($row["tipoespecial"]== "BRONQUITE" or $row["tipoespecial"]=="NEWCASTLE" or $row["tipoespecial"]=="GUMBORO" or $row["tipoespecial"]=="REOVIRUS" or $row["tipoespecial"]=="PNEUMOVIRUS"			
or $row["tipoespecial"]=='GUMBORO IND' or $row["tipoespecial"]=='BRONQUITE IND' or $row["tipoespecial"]=='NEWCASTLE IND' or $row["tipoespecial"]=='PNEUMOVIRUS IND' or $row["tipoespecial"]=='REOVIRUS IND'
or $row["tipoespecial"]=="PESAGEM" ){//Se for GMT
		imppaginisup();//inicia o controle da impressao: paginacao e controle encriptado
		cabecalhores();//monta o cabe�alho
		relespecial($mostraass);
		imppagrodape();//finaliza a pagina
	}//se GMT

	//Se o tipo especial for ELISA ou ELISASGMT (Elisa S/ GMT) adicionado a pedido do Daniel
	if($row["tipoespecial"]=="ELISA" or $row["tipoespecial"]=="ELISASGMT"){//Se for elisa, quebrar a tabela em partes iguais para nao gerar paginas 'soltas' na impressao


		relelisa($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"],$row["idespeciefinalidade"],$mostraass);


	}

	$boopb = true;//Indica inicio da quebra de paginas na segunda folha
	$ipage = 0;//reseta o numero de paginas atual

	/*
	 * INDICACAO DE VISUALIZACAO DO RESULTADO
	 */
        if($_SESSION["SESSAO"]["IDTIPOPESSOA"]!=1 and !$_SESSION["SESSAO"]["SUPERUSUARIO"]){//se nao for funcionario laudo

/*
                $sqlvisres = "update resultado set alteradoem = now(), statusvisualizacao = 'Y' where idresultado = " . $row["idresultado"];
                //echo "<!-- ".$sqlvisres." -->";
                $resvisres = mysql_query($sqlvisres);
                if(!$resvisres){
                     if($echosql){echo "<!-- ".mysql_error()." -->";}
                }
*/
			//Registra quando ocorreu a visualizacao
            $sqlvis = "insert into resultadovisualizacao (idresultado, idpessoa,criadoem) values (".$row["idresultado"].",".$_SESSION["SESSAO"]["IDPESSOA"].",now())";
            $resvis = mysql_query($sqlvis);
            if(!$resvis){
                 if($echosql){echo "<!-- ".mysql_error()." -->";}
            }

			//Retira notifica��o do dashboard
            if($apagaDashboard){
		        $sqldash = "delete from dashboardnucleopessoa where idresultado = ".$row["idresultado"]." and idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"];
		        $resdash = mysql_query($sqldash);
		        if(!$resdash){
		             if($echosql){echo "<!-- ".mysql_error()." -->";}
		        }
            }

        }


}//while($row = mysql_fetch_array($res)){
?>
</body>
</html>
