<?
require_once("../../inc/php/functions.php");
$sql="SELECT idsgsetor, 
			setor
		FROM sgsetor 
       WHERE status = 'ATIVO' ".getidempresa('idempresa','setor')." 
	   order by setor";
$res = mysql_query($sql) or die(mysql_error()." Erro ao buscar setor prompt sql=".$sql);
$virg="";
$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idsgsetor'].'":"'.$row['setor'].'"}';
		$virg=",";
	}
$json.="]";
echo($json);
?>