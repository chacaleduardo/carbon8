<?include_once("../../inc/php/functions.php");
$sql="select p.idplantel as id,
concat(e.sigla,' - ',p.plantel) as valor 
from plantel p join empresa e on(e.idempresa = p.idempresa) 
where p.status='ATIVO'  order by  e.sigla,plantel asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar plantel sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['id'].'":"'.$row['valor'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>