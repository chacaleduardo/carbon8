<?
require_once("../../inc/php/functions.php");
if(logado()){
	
	$sql="select idtipoamostra, tipoamostra 
			from tipoamostra 
			where status='ATIVO' 
				 ".getidempresa('idempresa','tipoamostra')." order by tipoamostra";
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipoamostra sql=".$sql);
	
	$virg="";
	$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idtipoamostra'].'":"'.$row['tipoamostra'].'"}';
		$virg=",";
	}
	$json.="]";
	echo($json);
}
?>