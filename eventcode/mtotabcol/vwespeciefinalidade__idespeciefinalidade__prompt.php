<?include_once("../../inc/php/functions.php");
$sql="select p.idespeciefinalidade,p.especiefinalidade,e.sigla from vwespeciefinalidade p
join empresa e on (e.idempresa=p.idempresa)
where p.status='ATIVO'
".idempresaFiltros('p')."
order by e.idempresa, p.especiefinalidade asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar plantel sql=".$sql);
$virg="";
$json="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idespeciefinalidade'].'":"'.$row['sigla'].' - '.$row['especiefinalidade'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>