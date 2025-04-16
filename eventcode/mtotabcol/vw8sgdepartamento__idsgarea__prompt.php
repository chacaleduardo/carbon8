<?
require_once("../../inc/php/functions.php");
$sql="SELECT idsgarea, 
			area
		FROM sgarea
       WHERE status = 'ATIVO' ".getidempresa('idempresa','area')." 
	   order by area";
$res = mysql_query($sql) or die(mysql_error()." Erro ao buscar area prompt sql=".$sql);
$virg="";
$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idsgarea'].'":"'.$row['area'].'"}';
		$virg=",";
	}
$json.="]";
echo($json);
?>