<?
require_once("../../inc/php/functions.php");
$sql="SELECT idstatustipo, 
			statustipo
		FROM "._DBCARBON."._statustipo 
       WHERE status = 'ATIVO' 
	   order by statustipo";
$res = mysql_query($sql) or die(mysql_error()." Erro ao buscar statustipo prompt sql=".$sql);
$virg="";
$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idstatustipo'].'":"'.$row['statustipo'].'"}';
		$virg=",";
	}
$json.="]";
echo($json);
?>