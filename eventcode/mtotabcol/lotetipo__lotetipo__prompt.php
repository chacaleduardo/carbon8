<?include_once("../../inc/php/functions.php");
$sql="select distinct lotetipo from lotetipo l 
join empresa e on (e.idempresa=l.idempresa)
where l.status='ATIVO'
".idempresaFiltros('l')."
order by l.lotetipo asc";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar lotetipo sql=".$sql);
$virg="";
$json="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['lotetipo'].'":"'.$row['lotetipo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>