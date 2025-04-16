<?
include_once("../inc/php/functions.php");


/*
 * PARAMETROS GET
 */
$exercicio	= $_GET["exercicio"];
$idpessoa	= $_GET["idpessoa"];
$oficial	= $_GET["oficial"];
$numerorps	= $_GET["numerorps"];

if(empty($numerorps)){
	die("Informe o Número do RPS");
}

if($oficial=="S"){
	
	
		$sql="select ni.*,a.idregistro
 		from resultado r,notafiscal n,amostra a,notafiscalitens ni
		where ni.idresultado not in (select c.idresultado from controleimpressaoitem c where c.idresultado = ni.idresultado and c.oficial = 'S' and c.via >= 1 and c.status = 'ATIVO')
		and a.idamostra = ni.idamostra
		and r.status = 'ASSINADO'
		and (r.idresultado!= '' and  r.idsecretaria is not null)
		and r.idresultado = ni.idresultado
		and ni.idnotafiscal=n.idnotafiscal
		
		and n.numerorps = '".$numerorps."'";
	
		$sql2="select ni.*,a.idregistro
		from resultado r,notafiscal n,notafiscalitens ni,amostra a  
		where a.idamostra = ni.idamostra 
		and  r.status = 'ABERTO'
		and (r.idsecretaria!= '' and  r.idsecretaria is not null)
		and r.idresultado = ni.idresultado
		and ni.idnotafiscal=n.idnotafiscal
		
		and n.numerorps = '".$numerorps."'";
		
		$sql3="select ni.*,a.idregistro
		from resultado r,notafiscal n,notafiscalitens ni,amostra a  
		where a.idamostra = ni.idamostra 
		and  r.status = 'FECHADO'
		and (r.idsecretaria!= '' and  r.idsecretaria is not null)
		and r.idresultado = ni.idresultado
		and ni.idnotafiscal=n.idnotafiscal
		
		and n.numerorps = '".$numerorps."'";
		
		$sql4="select ni.quantidade,ni.descricao,dmahms(c.criadoem) as criadoem,c.criadopor,a.idregistro,c.via
		from notafiscal n,notafiscalitens ni,controleimpressaoitem c,amostra a,resultado r
		where a.idamostra = ni.idamostra
		and (r.idsecretaria!= '' and  r.idsecretaria is not null)
		and r.idresultado = c.idresultado
		and c.oficial = 'S'
		and c.idresultado = ni.idresultado
		and ni.idnotafiscal = n.idnotafiscal 
		
		and n.numerorps = '".$numerorps."'";
			
}else{	

		$sql="select ni.*,a.idregistro
 		from resultado r,notafiscal n,amostra a,notafiscalitens ni
		where ni.idresultado not in (select c.idresultado from controleimpressaoitem c where c.idresultado = ni.idresultado and c.oficial = 'N' and c.via >= 1 and c.status = 'ATIVO')
		and a.idamostra = ni.idamostra
		and r.status = 'ASSINADO'
		and r.idresultado = ni.idresultado
		and ni.idnotafiscal=n.idnotafiscal
		
		and n.numerorps = '".$numerorps."'";
		
		$sql2="select ni.*,a.idregistro
		from resultado r,notafiscal n,notafiscalitens ni,amostra a  
		where a.idamostra = ni.idamostra 
		and (r.idsecretaria is null or r.idsecretaria= '')
		and r.status = 'ABERTO'
		and r.idresultado = ni.idresultado
		and ni.idnotafiscal = n.idnotafiscal 
		
		and n.numerorps = '".$numerorps."'";
		
		$sql3="select ni.*,a.idregistro
		from resultado r,notafiscal n,notafiscalitens ni,amostra a  
		where a.idamostra = ni.idamostra 
		and (r.idsecretaria is null or r.idsecretaria= '')
		and r.status = 'FECHADO'
		and r.idresultado = ni.idresultado
		and ni.idnotafiscal = n.idnotafiscal  
		
		and n.numerorps ='".$numerorps."'"; 
			
		$sql4="select ni.quantidade,ni.descricao,dmahms(c.criadoem) as criadoem,c.criadopor,a.idregistro,c.via
		from notafiscal n,notafiscalitens ni,controleimpressaoitem c,amostra a,resultado r
		where a.idamostra = ni.idamostra
		and r.idresultado = c.idresultado
		and c.oficial = 'N'
		and c.idresultado = ni.idresultado
		and ni.idnotafiscal = n.idnotafiscal
		
		and n.numerorps ='".$numerorps."'";
		
}		

		$res = mysql_query($sql) or die("A Consulta da quantidade de resultados assinados falhou : " . mysql_error() . "<p>SQL: $sql");
		$disp = mysql_num_rows($res);
		############################## Resultados assinados disponiveis
	
				
		$res2 = mysql_query($sql2) or die("A Consulta dos resultados abertos falhou : " . mysql_error() . "<p>SQL: $sql2");
		$aberto = mysql_num_rows($res2);
		############################### Resultados abertos não assinados
		

		$res3 = mysql_query($sql3) or die("A Consulta dos resultados fechados falhou : " . mysql_error() . "<p>SQL: $sql3");
		$fechado = mysql_num_rows($res3);
		############################### Resultados fechados não assinados
		
		$res4 = mysql_query($sql4) or die("A Consulta dos resultados ja impressos falhou : " . mysql_error() . "<p>SQL: $sql4");
		$impressos = mysql_num_rows($res4);
		############################### Resultados fechados não assinados
		

?>
<html>
<head>
<title>Listagem de Impressão </title>
<link href="../inc/css/carbon.css" media="all" rel="stylesheet" type="text/css" />
<link href="../inc/css/fontawesome/font-awesome.min.css" media="all" rel="stylesheet" type="text/css" />
<script language="javascript">

</script>

</head>
<body>
	<div id="conteudo" style="display: table-cell; height: 100%; width: 100%;">
	<!-- conteudo aqui aparece a assinaresultado corpo -->
<?
if($aberto > 0){
?>
<fieldset style="background-color:#EEDD82;">
	<legend><?=$aberto?> Resultados Abertos Não Assinados</legend>
	<table class="table table-striped ">
	<tr >
            <th align="center">Registro</th>
            <th>Qtd.</td>
            <th>Descrição</th>
	</tr>
<?
	while($row2 = mysql_fetch_assoc($res2)){
?>
	<tr >
            <td align="center"><?=$row2["idregistro"]?></td>
            <td><?=$row2["quantidade"]?></td>
            <td><?=$row2["descricao"]?></td>
	</tr>	
<?		
	}
?>	
	</table>	
</fieldset>	
<?
}
if($fechado > 0){
?>
<fieldset style="background-color:#FFFF00;">
	<legend><?=$fechado?> Resultados Fechados Não Assinados</legend>
	<table class="table table-striped ">
	<tr >
            <th align="center">Registro</th>
            <th>Qtd.</th>
            <th>Descrição</th>
	</tr>
<?
	while($row3 = mysql_fetch_assoc($res3)){
?>
	<tr >
            <td align="center"><?=$row3["idregistro"]?></td>
            <td><?=$row3["quantidade"]?></td>
            <td><?=$row3["descricao"]?></td>
	</tr>	
<?		
	}
?>	
</table>	
</fieldset>
<?
}
if($impressos > 0){
?>
<fieldset style="background-color:#87CEEB;">
	<legend><?=$impressos?> Resultados Já impressos</legend>
	<table class="table table-striped ">
	<tr >
            <th align="center">Registro</th>
            <th>Via</th>
            <th>Qtd.</th>
            <th>Descrição</th>		
            <th>Por</th>
            <th>Em</th>
	</tr>
<?
	while($row4 = mysql_fetch_assoc($res4)){
?>
	<tr class="respreto">
            <td align="center"><?=$row4["idregistro"]?></td>
            <td align="center"><?=$row4["via"]?></td>
            <td><?=$row4["quantidade"]?></td>
            <td><?=$row4["descricao"]?></td>		
            <td><?=$row4["criadopor"]?></td>
            <td><?=$row4["criadoem"]?></td>
	</tr>	
<?		
	}
?>	
</table>	
</fieldset>
<?
}
if($disp > 0){
?>	
<fieldset style="background-color:#FF4500;">
	<legend><?=$disp?> Resultados disponiveis para impressão</legend>
	<table class="table table-striped ">
	<tr>
            <th align="center">Registro</th>
            <th>Qtd.</th>
            <th>Descrição</th>
	</tr>
	<?
	$i=0;
	while($row = mysql_fetch_assoc($res)){
	$i=$i+1;
?>
	<tr>
            <td align="center"><?=$row["idregistro"]?></td>
            <td><?=$row["quantidade"]?></td>
            <td><?=$row["descricao"]?></td>
	</tr>		
<?		
	}
	$sql5="select p.impresultado
		from pessoa p,notafiscal n
		where p.idpessoa = n.idpessoa 
		
		and n.numerorps = '".$numerorps."'";
	$res5=mysql_query($sql5) or die("Erro ao buscar se cliente imprime resultado sql=".$sql5);
	$row5=mysql_fetch_assoc($res5);
	if($row5["impresultado"]=="N"){
?>	
	<tr>
            <td colspan="50">&nbsp;</td>
	</tr>
	<tr><td align="center" colspan="3" ><font style="font-size: 25px;"  color="red">Cliente optou por não receber resultados impressos!!!!</font></td></tr>
<?
	}
?>
	<tr>
            <td colspan="50">&nbsp;</td>
	</tr>
	<tr>
            <td>
                <div style="margin-bottom: 15px;margin-top: 15px;">
                    <a  style="font-size: 12px;" class="btaz10" href="../report/emissaoresultado.php?controle=<?=$numerorps?>&chkoficial=<?=$oficial?>">&nbsp;Imprimir&nbsp;</a>
                </div>
            </td>		
	</tr> 
	</table>
</form>		
</fieldset>	
<?
}else{
	$sqlp="select * from pessoa where idpessoa =".$_SESSION["SESSAO"]["IDPESSOA"];
	$resp = mysql_query($sqlp) or die("Falha ao buscar dados do acesso: " . mysql_error() . "<p>SQL: $sqlp");
	$rowp=mysql_fetch_assoc($resp);
	if($rowp["visualizares"]=='S'){
?>
	<table>
	<tr>
            <td colspan="50">&nbsp;</td>
	</tr>
	<tr>
            <td>
                <div style="margin-bottom: 15px;margin-top: 15px;">
                    <a  style="font-size: 12px;" class="btaz10" href="../report/emissaoresultado.php?controle=<?=$numerorps?>&chkoficial=<?=$oficial?>">&nbsp;Imprimir&nbsp;</a>
                </div>
            </td>		
	</tr> 
	</table>
<?
	}
}
?>
    </div>
</body>
</html>