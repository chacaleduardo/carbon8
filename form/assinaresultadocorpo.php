<?
require_once("../inc/php/validaacesso.php");

require_once "../report/reltesteinct2.php";

$acao =$_GET["acao"];
$_modulo = $_GET["_modulo"];
//pega o UNIQUE_ID da pagina assinarresultado.php para controlar o select
//$struniqueid=$_GET["uniqueid"];
/* Retira a string de controle de acao para nomear a variavel de sessao que armazena o controle da posicao de registro
 * Isto permite que se abra 2 janelas com parametros diferentes porque o nome da variavel eh composto pela query string
 */

//echo($struniqueid." 1  <br>");
//echo($_SESSION[$struniqueid]["uniqueid"]." 2 <br>");
//print_r($_SERVER);
//verifica se esta no primeiro registro para o limit
$controleass = $_SESSION[$struniqueid]["controleass"];
//if((empty($controleass)) or (!isset($_SESSION[$struniqueid]["uniqueid"]))){//se o uniqueid ou a pagina de vizualização for outra faz o select novamente 
if($acao=="ini"){
	$controleass=1;//vai para o primeiro registro	
	// Executa a consulta completa somente 1 vez para recuperar a quantidade total de registros
	$booexeccount = true;
	//Atualiza o Uniqueid da pagina para guardar o ultima pagina utilizada
	//$_SESSION[$struniqueid]["uniqueid"] = $struniqueid;	
}else{
	if($acao=="prox"){
		$controleass=intval($controleass)+1;	
	}elseif($acao=="ant" and $controleass > 1){
		$controleass=intval($controleass)-1;
	}
}
//Apos o incremento da variavel, atribui para a ssesion o valor do proximo registro a ser chamado
$_SESSION[$struniqueid]["controleass"] = $controleass;
?>
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
	<script src="../inc/js/functions.js"></script>
<?
/*
 * Parametros para relatorio 
 */
$mostraass=false;//se deve mostrar a assinatura
$exercicio	= $_GET["exercicio"];
$idpessoa	= $_GET["idpessoa"];
$nome	= $_GET["cliente"];
$idtipoteste	= $_GET["idtipoteste"];
$tipoteste	= $_GET["teste"];
$idresultado	= $_GET["resultado"];
$controle	= $_GET["controle"];
$idnucleo	= $_GET["idnucleo"];
$nucleoamostra	= $_GET["nucleoamostra"];
$lote	      = $_GET["lote"];
$sort		= $_GET["sort"];
$tipogmt	= $_GET["tipogmt"];
$status	= $_GET["status"];
$idregistro	= $_GET["registro"]; //Para casos em que os parametros vierem de uma tela de filtro (listagem)
$idregistro_1	= $_GET["registro_1"];
$idregistro_2	= $_GET["registro_2"];
$dataamostra_1	= $_GET["dataamostra_1"];
$dataamostra_2	= $_GET["dataamostra_2"];
$idade			= $_GET["idade"];
$tc		= $_GET["tc"];
$partida		= $_GET["partida"];
$idtipoamostra	= $_GET["idtipoamostra"];
$idsubtipoamostra	= $_GET["idsubtipoamostra"];
$chkoficial = $_GET["oficial"];
$idunidade = $_GET["idunidade"];
$echosql = true;
/*
 * TRATAMENTO DOS PARAMETROS GET PARA CONCATENACAO POSTERIOR COM SQL
 */
if(!empty($exercicio)){
	if(is_numeric($exercicio)){
		$clausula .= " a.exercicio = " . $exercicio ." and ";
	}else{
		die ("O Exercício informado possui caracteres inválidos: [".$exercicio."]");
	}
}

if (!empty($idunidade)){
	$clausula .= " a.idunidade = " .$idunidade ." and ";
}


if ($chkoficial=="S"){
        $clausula .= " a.idsecretaria != '' and ";
}elseif($chkoficial=="N"){
        $clausula .= " (a.idsecretaria = '' or a.idsecretaria is null) and ";
}

if (!empty($idpessoa)){
	$clausula .= " a.idpessoa = " .$idpessoa ." and ";
}
if (!empty($nome)){
	$clausula .= " a.nome like ('%".$nome."%') and ";
}
if(!empty($status)){
	if($status == 'FECHADO'){
		$clausula .= " ((a.status = '" .$status ."' and a.conferenciares = 'N') OR (a.status = 'CONFERIDO')) and ";
	} else {
		$clausula .= " a.status = '" .$status ."' and ";
	}
}
if (!empty($idnucleo)){
	$clausula .= " a.idnucleo = " .$idnucleo ." and ";
}
if (!empty($nucleoamostra)){
        $clausula .= " a.nucleoamostra = '" .$nucleoamostra ."' and ";
}
if (!empty($lote)){
        $clausula .= " a.lote = '" .$lote ."' and ";
}
if (!empty($tipogmt)){
        $clausula .= " a.tipogmt = '" .$tipogmt ."' and ";
}
if (!empty($idtipoteste)){
	$clausula .= " a.idtipoteste = " .$idtipoteste ." and ";
}
if (!empty($idresultado)){
	$clausula .= " a.idresultado = " .$idresultado." and ";
}
if (!empty($controle)){
	$clausula .= " a.idresultado in (SELECT ni.idresultado FROM notafiscal nf, notafiscalitens ni WHERE ni.idnotafiscal = nf.idnotafiscal and nf.controle = ". $controle .") and ";
}
if (!empty($idregistro)){
	if (is_numeric($idregistro)){
		$clausula .= " a.idregistro = " .$idregistro." and ";
	}else{
		die ("O Nº de Registro informado é inválido: [".$idregistro."]");
	}
}
if (!empty($idregistro_1) or !empty($idregistro_2)){
	if (is_numeric($idregistro_1) and is_numeric($idregistro_2)){
		$clausula .= " (a.idregistro BETWEEN " . $idregistro_1 ." and " . $idregistro_2 .")"." and ";
	}else{
		die ("Os Nºs de Registro informados são inválidos: [".$idregistro_1."] e [".$idregistro_2."]");
	}
}
if (!empty($dataamostra_1) or !empty($dataamostra_2)){
	$dataini = validadate($dataamostra_1);
	$datafim = validadate($dataamostra_2);
	if ($dataini and $datafim){
		$clausula .= " (a.dataamostra  BETWEEN '" . $dataini ."' and '" .$datafim ."')"." and ";
	}else{
		die ("A Data informada é inválida!");
	}
}
if (!empty($idade)){
	$clausula .= " a.idade = '" .$idade ."' and ";
}
if(!empty($tipoteste)){
    $clausula .=" a.tipoteste like('%".$tipoteste."%') and ";
}
if (!empty($tc)){
	$clausula .= " a.tc = '" .$tc ."' and ";
}
if (!empty($partida)){
	$clausula .= " a.partida = '" .$partida ."' and ";
}
if (!empty($idtipoamostra)){
	$clausula .= " a.idtipoamostra = " .$idtipoamostra ." and ";
}
if (!empty($idsubtipoamostra)){
	$clausula .= " a.idsubtipoamostra = " .$idsubtipoamostra ." and ";
}
/*
 * TRATAMENTO E CONCATENACAO DO SQL PRINCIPAL
 */
if (!empty($clausula)){
	$clausula = 'where ' . substr($clausula,1,strlen($clausula) - 5);
}
	if($booexeccount==true){
		$sqlcount = "select a.idresultado,a.idempresa,a.criadoem,a.conferenciares,a.status from vwassinarresultado a " . $clausula . " order by exercicio, idregistro ".$sort.", tipoteste";
		
		echo "<!-- ".$sqlcount." -->";
		echo "<!-- ".$_modulo." -->";
		echo "<!-- ".getidempresa('a.idempresa',$_modulo)." -->";

		//echo($sqlcount.'<br>');
		$rescount = mysql_query($sqlcount) or die("Falha no Relatório de Testescount: " . mysql_error() . "<p>SQL: $sqlcount");
		$qtdcount = mysql_num_rows($rescount);
		
		if(empty($qtdcount)){
			echo '<br><br><br><div align="center">[i] Não existem mais registros.</div> <br> <div align="center">ou <div><br> <div align="center">Não há nenhum registro para os parà¢metros informados!</div>';
			
			die;
		}
				
		$arridresultado = array();
		$iarr = 0;
		//while para gravar todos os resultados para se poder navegar entre eles
		while($rowqtd = mysql_fetch_array($rescount)){
                    
                    if($rowqtd["conferenciares"]=='Y'  and $rowqtd['status']=='FECHADO'){
                       /* $sc="select count(*) as existe from carrimbo c 
                                where c.idobjeto = ".$rowqtd["idresultado"]." 
                                and c.tipoobjeto='resultado'
                                and c.status='CONFERIDO'";*/
								
							$sc = "select if(valor = 'CONFERIDO',1,0) as existe 
									from 
										_auditoria 
									where 
										objeto = 'resultado' 
										and idobjeto = ".$rowqtd["idresultado"]." 
										and coluna = 'status' 
									order by 
										idauditoria desc 
									limit 1";
                        $rc = mysql_query($sc) or die("Falha ao buscar carimbo de conferencia: " . mysql_error() . "<p>SQL: $sc");
                        $roc= mysql_fetch_assoc($rc);
                        if($roc["existe"]>0){
                            $confok="Y";
                        }else{
                            $confok="N";
                        }
                    }else{
                        $confok="Y";
                    }
                    if($confok=="Y"){
			$iarr++;
                        //congelar os resultados antes de mostrar para assinatura
                        congelaresultado($rowqtd["idresultado"]);
			$arridresultado[$iarr]=$rowqtd["idresultado"];			
			$booexeccount = false;
                    }//if($confok=="Y"){
		}//while($rowqtd = mysql_fetch_array($rescount)){
                		
		//total de registros da consulta
		$_SESSION[$vargetsess]["qtdreg"] = $iarr;
		//grava todos dos os ids de resultados da consulta
		$_SESSION[$vargetsess]["arridres"] = $arridresultado;
	}
	$arridresultado=$_SESSION[$vargetsess]["arridres"];
	
	$sql = " select /*! STRAIGHT_JOIN */ jresultado, versao from vwassinarresultado a where  a.idresultado =".$arridresultado[$controleass]." order by idpessoa, exercicio, idregistro ".$sort.", tipoteste";

	$qtdcount = $_SESSION[$vargetsess]["qtdreg"];	
	
if($qtdcount < $controleass){
	echo '<br><br><br><div align="center">[ii]Não existem mais registros.</div>';
	die;
}

$res = mysql_query($sql) or die("Falha no Relatório de Testes: " . mysql_error() . "<p>SQL: $sql");
$qtd = mysql_num_rows($res);

if($qtdcount < $controleass){
	echo '<br><br><br><div align="center">[iii]Não existem mais registros.</div>';
	die;
}
/*
 * PARAMETROS GERAIS
 */
$codepress = md5(date('dmYHis')); //gera um codigo para a impressao
$irestotal = mysql_num_rows($res); //aramazena o total de paginas
$resultado = mysql_fetch_assoc($res);

	//Recupera os dados congelados no resultado, para serem apresentados na tela juntamente à  versão gerada apà³s STATUS=ASSINADO
	$rc= unserialize(base64_decode($resultado["jresultado"]));

	if(empty($rc)){
		echo "Teste não possui informação de resultado: [".$resultado["idresultado"]."]";
	}
	

	//Abre um $row com os dados da coluna jresultado
	//Abre um $row com os dados da coluna jresultado
	foreach($rc["amostra"]["res"] as $key => $value){
		$row[$key] = $value;
	}
	foreach($rc["dadosamostra"]["res"] as $key => $value){
		$row["dadosamostra"][$value["objeto"]] = $value['valorobjeto'];
	}
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
	$row["numgalpoes"]			=$rc["amostra"]["res"]["numgalpoes"];
	$row["alojamento"]			=$rc["amostra"]["res"]["alojamento"];
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
	$row["especiefinalidade"]=$rc["especiefinalidade"]["res"]["especiefinalidade"];
	$row["finalidade"]		=$rc["especiefinalidade"]["res"]["finalidade"];
	$row["idnucleo"]		=$rc["nucleo"]["res"]["idnucleo"];
	$row["nucleo"]			=$rc["nucleo"]["res"]["nucleo"];
	$row["regoficial"]		=$rc["amostra"]["res"]["regoficial"];
	$row["quantidadeteste"] =$rc["resultado"]["res"]["quantidade"];
	$row["nome"]			=$rc["pessoa"]["res"]["nome"];
	$row["razaosocial"]		=$rc["pessoa"]["res"]["razaosocial"];
	$row["idservicoensaio"]	=$rc["resultado"]["res"]["idservicoensaio"];
	$row["gmt"]				=$rc["resultado"]["res"]["gmt"];
	$row["padrao"]			=$rc["resultado"]["res"]["padrao"];
	$row["descritivo"]		=$rc["resultado"]["res"]["descritivo"];
	$row["idresultado"]		=$rc["resultado"]["res"]["idresultado"];
	$row["idt"]				=$rc["resultado"]["res"]["idt"];
	$row["idtipoteste"]		=$rc["resultado"]["res"]["idtipoteste"];
	$row["criadoemres"]		=$rc["resultado"]["res"]["criadoem"];
	$row["idempresares"]	=$rc["resultado"]["res"]["idempresa"];
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
	$row["versao"]          =$rc["resultado"]["res"]["versao"];
	$row["alerta"]          =$rc["resultado"]["res"]["alerta"];
	$row["jsonresultado"]  	=$rc["resultado"]["res"]["jsonresultado"];
	$row["jsonconfig"]      =$rc["resultado"]["res"]["jsonconfig"];	
	$row["subtipoamostra"]	=$rc["subtipoamostra"]["res"]["subtipoamostra"];
	$row["normativa"]		=$rc["subtipoamostra"]["res"]["normativa"];
	$row["tipoamostra"]		=$rc["tipoamostra"]["res"]["tipoamostra"];
	$row["tipoamostraformatado"]=($row["tipoamostra"]==$row["subtipoamostra"])?$row["tipoamostra"]:$row["tipoamostra"]." Subtipo:".$row["subtipoamostra"];
	$row["tipoteste"]		=$rc["prodserv"]["res"]["tipoteste"];
	$row["sigla"]			=$rc["prodserv"]["res"]["sigla"];
	$row["tipoespecial"]	=$rc["prodserv"]["res"]["tipoespecial"];
	$row["geralegenda"]		=$rc["prodserv"]["res"]["geralegenda"];
	$row["geragraf"]		=$rc["prodserv"]["res"]["geragraf"];
	$row["geracalc"]		=$rc["prodserv"]["res"]["geracalc"];
	$row["textopadrao"]		=$rc["prodserv"]["res"]["textopadrao"];
	$row["textointerpretacao"]		=$rc["prodserv"]["res"]["textointerpretacao"];
	$row["tipobact"]		=$rc["prodserv"]["res"]["tipobact"];
	$row["logoinmetro"]		=$rc["prodserv"]["res"]["logoinmetro"];
	$row["modo"]			=$rc["prodserv"]["res"]["modo"];
	$row["modelo"]			=$rc["prodserv"]["res"]["modelo"];
	$row["tipogmt"]			=$rc["prodserv"]["res"]["tipogmt"];
	$row["comparativodelotes"]=$rc["prodserv"]["res"]["comparativodelotes"];
	$arrprodservtipoopcao 	=$rc["prodservtipoopcao"]["res"];
	$arrprodservtipoopcaoespecie =$rc["prodservtipoopcaoespecie"]["res"];
	$arrlotecons 			=$rc["lotecons"]["res"];
	$arrAmostraCampos 			=$rc["amostracampos"]["res"];
	
	$rowbio					=$rc["bioensaio"]["res"];
	$rowend					=$rc["endereco"]["res"];
	$rtitulos				=$rc["titulos"]["res"];
	$arrgrafgmt				=$rc["hist_gmt"]["res"];
	$arrelisa				=$rc["resultadoelisa"]["res"];
	$arrelisagr1			=$rc["resultadoelisa_graf1"]["res"];
	$arrelisagr2			=$rc["resultadoelisa_graf2"]["res"];
	$arrassinat				=$rc["resultadoassinatura"]["res"];

	$rowbio		=$rc["bioensaio"]["res"];
	$rowend		=$rc["endereco"]["res"];
	$rtitulos	=$rc["titulos"]["res"];
	$arrgrafgmt	=$rc["hist_gmt"]["res"];
	$arrelisa	=$rc["resultadoelisa"]["res"];
	$arrelisagr1=$rc["resultadoelisa_graf1"]["res"];
	$arrelisagr2=$rc["resultadoelisa_graf2"]["res"];
	$arrassinat	=$rc["resultadoassinatura"]["res"];


	if (!empty($rc["amostra"]["res"]["datachegada"])){
			
		$row["datachegada"]	= $rc["amostra"]["res"]["datachegada"];
		
		$row["datachegada"]	= date("d/m/Y", strtotime($row["datachegada"]));
	}
	
	
	$row["dataconclusao"]	=$rc["_auditoria"]["res"]["dataconclusao"];
	
	if (empty($row["dataconclusao"])){
		$sqla = "select DATE_FORMAT(max(criadoem), '%d/%m/%Y') as dataconclusao from _auditoria 
					where 
						objeto = 'resultado' and 
						idobjeto = '".$row["idresultado"]."' and 
						coluna = 'status' and 
						valor = 'FECHADO'";
		$resa=mysql_query($sqla) or die("Erro ao buscar as pesagens para listagem sql 1".$sqlind);
		$x = 0;
		while($linhaa=mysql_fetch_assoc($resa)){
			$row["dataconclusao"]			= $linhaa['dataconclusao'];
			
		}
	}

//Se for uma amostar com idunidade = 4 trata-se de uma amostra do bioterio
/*
if($row['idunidade']==4 and !empty($row['idservicoensaio'])){
	//Buscar a idade para o bioterio
	$sqlb="SELECT (DATEDIFF(s.data,ss.data)) AS idade,if(s.dia is null,'',concat('D',s.dia)) as rotulo
			from servicoensaio ss,servicoensaio s
			where ss.servico = 'TRANSFERENCIA'
			and	ss.idbioensaio = s.idbioensaio
			and s.idservicoensaio =".$row['idservicoensaio'];
	$resb=mysql_query($sqlb) or die("Erro ao buscar a idade para o biotério sql".$sqlb);
	$rowb=mysql_fetch_assoc($resb);
	if(empty($rowb['rotulo'])){
		$row['idade']=$rowb['idade'];
	}else{
		$row['idade']=$rowb['rotulo'];
		$row["tipoidade"]="";
	}
	//$row["nroamostra"]=$row['quantidadeteste'];
	//echo($sqlb);
}
*/
// inserir informaçàµes do lote no resultado
if(!empty($row['idwfxprocativ'])){
	$sqlp="SELECT * FROM vwpartidaamostra where idamostra =".$row['idamostra'];
	$resp=mysql_query($sqlp) or die("Erro ao buscar informaçàµes da partida da amostra sql".$sqlp);
	$rowp=mysql_fetch_assoc($resp);
	$row['partida']=$rowp['partidaext'];
	$row['nucleoamostra']=$rowp['partida'];
	$row['lote']=$rowp['partidaext'];
}

/*
 * maf050313: verifica se existe alguma assinatura previa, e mostra para o usuario um cabecalho com as informacoes de assinatura e o registro mais recente da _auditoria do carbon
 */
$sqlass = "SELECT p.nome, date_format(a.criadoem,'%d/%m/%y %H:%i') as dtass 
			FROM resultadoassinatura a, pessoa p
			WHERE p.idpessoa = a.idpessoa
			and idresultado = ".$arridresultado[$controleass]."
			ORDER BY a.criadoem desc";
$resass = mysql_query($sqlass) or die("assinaresultadocorpo.php: Erro ao recuperar assinaturas:".mysql_error());

if(mysql_num_rows($resass)>0){

	//Muda a cor da barra de Status para verde pois está assinado. Fica verde 
	$clsfooter="clsFootera";

	$ruass = mysql_fetch_assoc($resass);

	$sqlaud = "SELECT criadopor, date_format(criadoem,'%d/%m/%y %H:%i') as dtcriacao, valor
				FROM _auditoria
				WHERE objeto = 'resultado'
					and coluna = 'descritivo'
					and idobjeto = ".$arridresultado[$controleass]."
				ORDER BY criadoem desc 
				LIMIT 1,1";

	$resaud = mysql_query($sqlaud) or die("assinaresultadocorpo.php: Erro ao recuperar registros de auditoria: ".mysql_error);
	$rraud = mysql_fetch_assoc($resaud);	
?>

<div style="display: block; width: 100%; background-color: #ffdd66;">
<table>
<tr>
    <td style="font-size:12px" colspan="20"><img src="../inc/img/alerta8.gif">&nbsp;Resultado alterado desde última assinatura:</td>
</tr>
<tr>
    <td nowrap="" style="vertical-align:top;">
        <div style="display: inline-table;background-color:white;font-size:12px;color:gray;">
<span style="color:silver;text-decoration:underline;">Última Assinatura:</span>
<br><?=$ruass["dtass"]?> - <?=$ruass["nome"]?>
</div>
    </td>
    <td style="vertical-align:top;">
<div style="display:inline-table;background-color:white;font-size:12px;color:gray;">
<span style="color:silver;text-decoration:underline;">Resultado anterior:</span>
<br><span style="color:silver;text-decoration:underline;">Autor:</span>&nbsp;&nbsp;<?=$rraud["criadopor"]?>
<br><span style="color:silver;text-decoration:underline;">Data:</span>&nbsp;&nbsp;<?=$rraud["dtcriacao"]?>
<br><br>
<?=$rraud["valor"]?>
</div>
    </td>
</tr>
</table>
</div>
<?
} else {
	//Muda a cor da barra de Status para verde pois está assinado. Fica Cinza
	$clsfooter="clsFooterf";
}
?>
<div style="display:table-cell; width: 100%; height: 100%;">
<?


$templateinterpretacao = '<div id="interpretacao" style="display:table-cell; height: 100%; overflow:auto; border:1px;;width:668px">';

// (Conforme conversa com Edson)- Se a idade for diferente de Dia(s) este lote pode receber interpretaçàµes
// (Conforme nova conversa com Daniel e Edson) Lotes com idade em dias também poderão ter interpretação
if(!empty($row["idtipoteste"]) /*and $row["tipoidade"] <> "Dia(s)"*/){
			//documentos
			$sqlf ="select * 
			from interpretacao i,intertipoteste it
			where i.status = 'ATIVO' 
			and i.idinterpretacao = it.idinterpretacao 
			and it.idtipoteste = ".$row["idtipoteste"]." order by i.titulo";		
		  	$resf = mysql_query($sqlf) or die("A Consulta das interpretaçàµes falhou :".mysql_error()."<br>Sql:".$sqlf); 

		$i=0;
		while($rowf = mysql_fetch_array($resf)){
			$i++;			
			if($i==1){
				$cls="btintfoco";
			}else{ 
				$cls="btintnormal";		
			}	

		$templateinterpretacao .= '<div id="fraseint-'.$i.'"  class="'.$cls.'" onclick="selecionabt(this)">'.$rowf["frase"].'</div>';

		}
}else{		

	$templateinterpretacao .= '<div>Obs: Tipo do teste não definido.</div>';

}

 $templateinterpretacao .= '</div><br>';
?>
<div style="display:table-cell; width: 80%; height: 100%; overflow:auto; border:1px;">
<?
//print_r($row);
/*
 * As condicoes abaixo invocam as funcoes para montagem das emissoes conforme o tipo especial
 */
    //monta o cabeçalho
    
	if($row["idempresa"] == 1 && $row['criadoemres'] > date('2023-10-23 12:00:00')){
		cabecalhores1();
		relresultado1($mostraass, 1);
	}else{
		cabecalhores();
		relresultado($mostraass, 1);
	}
	
		//MOSTRAR ARQUIVOS	
		$sqlarq1 = "select a.*, dmahms(criadoem) as datacriacao
		from arquivo a
		where
		a.tipoobjeto = 'resultado'
		and a.idobjeto = ".$row["idresultado"]."
		and tipoarquivo = 'ANEXO'
		order by idarquivo asc";
		
		//echo $sqlarq."<br>";
		$res1 = mysql_query($sqlarq1) or die("Erro ao pesquisar arquivos:".mysql_error());
		$numarq1= mysql_num_rows($res1);
		
		if($numarq1>0){			
?>		
		<div>
		<iframe 
			src="arquivo.php?idobjeto=<?=$row["idresultado"]?>&tipoobjeto=resultado&mostraanexar=N&mostraarquivo=S&tipoarquivo=ANEXO&caminho=../upload/" 
			width="100%" frameborder="0">
                </iframe>
		</div>			
<?
		}
?>	
<div id="Footer" class="<?=$clsfooter?>">
<table width="100%">
<tr>
	<td style="font-size:12px;padding-left:15px;width:150px;"><?echo"Resultado ".$controleass." de ".$qtdcount;?></td>
	<td align="center">		
		<table align="center">
		<tr>
<?
						$stralerta = "";
						if($row["alerta"]=="Y"){
							$stralerta = "checked";
						}
						if(empty($row["idsecretaria"])){
							$varoficial = "N";
						}elseif(!empty($row["idsecretaria"])){
							$varoficial = "Y";
						}
?>		
			<td>
				&nbsp;&nbsp;&nbsp;
				<input type="button" tabindex="1" value="Editar" id="bteditar" class="bteditar" onfocus="this.className='bteditarfoco';" onblur="this.className='bteditar';" onClick="iniciaEdicao()">
				<input type="button" tabindex="2" value="Assinar" id="btassina" class="btassina" onfocus="this.className='btassinafoco';" onblur="this.className='btassina';" onClick="assina('assinar','<?=$row["idresultado"]?>','<?=$varoficial?>','<?=$row["alerta"]?>','<?=$row["tipoespecial"]?>');">
				<input type="button" tabindex="3" value="Retirar" id="btretira" class="btretira" onfocus="this.className='btretirafoco';" onblur="this.className='btretira';" onClick="assina('retirar','<?=$row["idresultado"]?>','N','N','<?=$row["tipoespecial"]?>');">
				&nbsp;&nbsp;&nbsp;
				<input type="checkbox" <?=$stralerta?> onClick="alertateste(<?=$row["idresultado"]?>,'<?=$varoficial?>',this);"><label style="font-size:12px;font-weight: bold;">Alerta!</label>
			</td>
		</tr>
		</table>
	</td>
	<td style="width:150px;"></td>
</tr>
</table>
</div>
</div>
</div>
 