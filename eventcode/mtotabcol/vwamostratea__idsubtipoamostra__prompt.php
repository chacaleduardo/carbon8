<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="select sta.idsubtipoamostra,  sta.subtipoamostra as subtipoamostra
			from subtipoamostra sta
			where sta.status='ATIVO' 				
			 ".getidempresa('idempresa','subtipoamostra')."
			order by  sta.subtipoamostra";
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar subtipoamostra sql=".$sql);
	
	$virg="";
	$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idsubtipoamostra'].'":"'.$row['subtipoamostra'].'"}';
		$virg=",";
	}
	$json.="]";
	echo($json);
}
?>