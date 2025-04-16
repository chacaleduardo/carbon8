<?include_once("../../inc/php/functions.php");
$sql="select p.idplantel, concat(e.sigla,'-',p.plantel) as plantel
from plantel p join empresa e on(e.idempresa=p.idempresa)
where p.status='ATIVO' ".idempresaFiltros('p')."  order by  plantel asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar plantel sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idplantel'].'":"'.$row['plantel'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>