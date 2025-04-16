<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="select sta.idsubtipoamostra, concat(ta.tipoamostra, ' / ', sta.subtipoamostra) as subtipoamostra
			from tipoamostra ta, subtipoamostra sta
			where sta.status='ATIVO' 
				and ta.idtipoamostra = sta.idtipoamostra
				 ".getidempresa('ta.idempresa','tipoamostra')."
			order by ta.tipoamostra, sta.subtipoamostra";
	
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