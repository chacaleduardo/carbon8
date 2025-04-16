<?
include_once("../inc/php/validaacesso.php");


$idsgdoc=$_GET['idsgdoc'];

?>
<html>
<head>
<title>Lista Documentos</title>

<link href="../inc/css/mtorep.css?1" media="all" rel="stylesheet" type="text/css" />
<script language="JavaScript" src="../inc/js/functions.js"></script>

</head>
<body>
<?
IF(empty($idsgdoc)){
 echo "ID documento não foi informado";

}else{
	$sql ="SELECT ss.des,sg.alteradopor,dmahms(sg.alteradoem) as alteradoem,sg.idsgdocupd,sg.idsgdoc,sg.versao,sg.revisao
			FROM sgdocupd sg left join sgdocstatus ss on( ss.idsgdocstatus = sg.status)
			where  sg.alteradopor is not null
			and sg.idsgdoc = ".$idsgdoc." group by versao order by sg.idsgdocupd desc";
	
	$res = mysql_query($sql) or die("A Consulta das versões falhou :".mysql_error()."<br>Sql:".$sql); 
	$qtdrow= mysql_num_rows($res);
	
	if($qtdrow > 0){
?> 
<fieldset>
	<legend>Versàµes do documentto</legend>
	<table align="left" class="normal"> 
			<tr>
				<td class="header">Versão</td>	
				<td class="header">Status</td>	
				<td class="header">Alterado Por</td>
				<td class="header">Alterado Em</td>
											
			</tr>	
<?				
	
		while($row = mysql_fetch_array($res)){	
?>
			<tr class="respreto">
				<td><a href="javascript:janelamodal('sgdocupd.php?_acao=u&idsgdocupd=<?=$row["idsgdocupd"]?>')"><font color="Blue" style="font-weight: bold;">[<?=$row["versao"]?>.<?=$row["revisao"]?>]</font></a></td>
				<td><?=$row["des"]?></td>
				<td><?=$row["alteradopor"]?></td>
				<td><?=$row["alteradoem"]?></td>
				
			</tr>	
<?		
		}
?>		
	</table>
<?	
	}else{
		echo "O Documento ainda não possui versão";	
	}
	
?>
</fieldset>
<?	
}		
?>
</body>
</html>

