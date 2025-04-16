<?
require_once("../../inc/php/functions.php");
$sql="SELECT idtipounidade, 
			 tipounidade
		FROM tipounidade
       WHERE status = 'ATIVO' 
	   order by tipounidade";
$res = mysql_query($sql) or die(mysql_error()." Erro ao buscar prodserv sql=".$sql);
$virg="";
$json="";
$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idtipounidade'].'":"'.$row['tipounidade'].'"}';
		$virg=",";
	}
$json.="]";
echo($json);
?>  