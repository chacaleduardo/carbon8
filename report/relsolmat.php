<?
include_once("../inc/php/validaacesso.php");

?>
<html>
<head>
<title>Funcion&aacute;rio</title>
</head>


<link href="../inc/css/mtorep.css?1" media="all" rel="stylesheet" type="text/css" />
<script language="JavaScript" src="../inc/js/functions.js"></script>


<body>
<?
		$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
		$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getidempresa('idempresa','empresa');
		
		if($_timbrado != 'N'){
	
			$_sqltimbrado="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'HEADERSERVICO'";
			$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
			$_figtimbrado=mysql_fetch_assoc($_restimbrado);

			$_sqltimbrado1="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMMARCADAGUA'";
			$_restimbrado1 = mysql_query($_sqltimbrado1) or die("Erro ao retornar figura do relatório: ".mysql_error());
			$_figtimbrado1=mysql_fetch_assoc($_restimbrado1);

			$_sqltimbrado2="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMRODAPE'";
			$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: ".mysql_error());
			$_figtimbrado2=mysql_fetch_assoc($_restimbrado2);
			
			$_timbradocabecalho = $_figtimbrado["caminho"];
			$_timbradomarcadagua = $_figtimbrado1["caminho"];
			$_timbradorodape = $_figtimbrado2["caminho"];
			
			if(!empty($_timbradocabecalho)){?>
				<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="90px" width="100%"></div>
			<?}
		}

$figurarelatorio = "../inc/img/repheader.png";
if (!empty($_REQUEST['idsolmat'])) {
	$id = $_REQUEST['idsolmat'];
}	

$sql="SELECT s.idsolmat, s.idempresa, s.status, p.nomecurto, s.criadoem, s.obsgeral, s.qtd, s.tipo,uo.idunidade, uo.unidade as origem,ud.unidade as destino,t.descricao  
	from solmat s
	left join unidade uo on uo.idunidade = s.unidade
	left join unidade ud on ud.idunidade = s.idunidade
	left join pessoa p on p.usuario = s.criadopor
	left join empresa e on e.idempresa = s.idempresa
	left join tag t on t.idtag = s.idtag 
	where idsolmat = '".$id."';";

$res = d::b()->query($sql) or die("Falha ao pesquisar Nota Fiscal : " . mysqli_error(d::b()) . "<p>SQL: $sql");
$row = mysqli_fetch_array($res);
$idpessoa = $row['idpessoa'];
?>

<br><br>

<table class="tbrepheader">
	<tr>
		<td class="header" pre-line>Id: <?=$row['idsolmat'];?></td>
	</tr>
	<tr>
		<td class="header" pre-line>Solicitante: <?=strtoupper($row['nomecurto']);?></td>
	</tr>
	<tr>
		<td class="header" pre-line>Origem: <?=$row['origem'];?></td>
	</tr>
	<tr>
		<td class="header" pre-line>Tipo: <?=$row['tipo'];?></td>
	</tr>
	<tr>
		<td class="header" pre-line>Destino: <?=$row['destino'];?></td>
	</tr>
	<tr>
		<td class="header" pre-line >Local de Entrega: <?=$row['descricao'];?></td>
	</tr>
	<tr>
		<td class="header" pre-line >Data de impressão: <?=dmahms(sysdate());?></td>
	</tr>
</table>

<br><br>
<?
$sql1= "SELECT  i.*
FROM solmatitem i
where i.idsolmat ='".$id."' AND i.idprodserv is null order by i.criadoem";

$qr1 = d::b()->query($sql1) or die("Erro ao buscar Item(ns)".mysql_error());
$rows1= mysqli_num_rows($qr1);
$sqlr= "SELECT  i.*
FROM solmatitem i
where i.idsolmat ='".$id."' AND i.idprodserv is not null order by i.criadoem";
$qr = d::b()->query($sqlr) or die("Erro ao buscar Item(ns)".mysql_error());
$rowsn= mysqli_num_rows($qr);
if($rowsn > 0){?>
<fieldset  style="border: none; border-top: 2px solid silver;">
		<legend>Item(ns) Cadastrado(s)</legend>
	</fieldset>	

	<p>&nbsp;</p>
	<table class="normal"> 
		<tr> 	
			<td class="header" >QTD</td>
			<td class="header" >UN</td>	
			<td class="header" >DESCRIÇÃO</td>		
		</tr>
<?}

while($rows = mysqli_fetch_array($qr)){
	$sqlc="SELECT l.idlote,l.partida,l.npartida,l.exercicio,p.un,f.qtd,c.qtdd,f.idlotefracao,c.idlotecons,o.idobjeto
	from lote l
	join lotefracao f on(f.idlote=l.idlote)
	join lotecons c on(c.idlote=l.idlote AND c.idlotefracao=f.idlotefracao AND c.idobjetoconsumoespec=".$rows['idsolmatitem']." AND c.tipoobjetoconsumoespec='solmatitem' AND c.qtdd>0)
	join prodserv p on(p.idprodserv=l.idprodserv)
	JOIN unidadeobjeto o ON (o.tipoobjeto = 'modulo'
	AND o.idobjeto LIKE ('lote%')
	AND o.idunidade = f.idunidade)
	JOIN carbonnovo._modulo m ON (m.modulo = o.idobjeto
	AND m.modulotipo = 'lote'
	AND m.status = 'ATIVO')
	where l.status='APROVADO'
	and l.idprodserv =".$rows['idprodserv']." ORDER BY  exercicio DESC ,npartida DESC";

$qr0 = d::b()->query($sqlc) or die("Erro ao buscar Item(ns)".mysql_error());
$rows0= mysqli_num_rows($qr0);

if($rows0 > 0){?>
		<?while($rows2 = mysql_fetch_array($qr0)){?>			
		<tr class="res">
			<td ><?=$rows2['qtdd'];?></td>
			<td ><?=$rows2['un'];?></td>
			<td ><?=$rows2['partida'];?></td>
		</tr>	
		<?}?>
<?}
}?>
</table>
<p>&nbsp;</p> 
	<?if($rows1 > 0){?>

<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>Item(ns) Nâo Cadastrado(s)</legend>
</fieldset>	

<p>&nbsp;</p>
<table class="normal"> 
	<tr> 	
		<td class="header">QTD</td>
		<td class="header">UN</td>	
		<td class="header">DESCRIÇÃO</td>	
		<td class="header">OBSERVAÇÃO</td>			
	</tr>
	<?while($qtdrows1 = mysql_fetch_array($qr1)){?>			
	<tr class="res">
		<td><?=$qtdrows1['qtdc'];?></td>
		<td><?=$qtdrows1['un'];?></td>
		<td><?=$qtdrows1['descr'];?></td>
		<td><?=$qtdrows1['obs'];?></td>
	</tr>	
	<?}?>				
</table>
<p>&nbsp;</p> 
<?}?>
	

<hr style="background-color: solid silver;">	
<p>&nbsp;</p>	
<?if(!empty($_timbradorodape)){?>
	<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="90px" width="100%"></div>
<?}?>
</body>
</html>


