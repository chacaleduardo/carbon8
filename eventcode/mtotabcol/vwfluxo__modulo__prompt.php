<?include_once("../../inc/php/functions.php");
$sql="SELECT m.modulo, CONCAT(m.rotulomenu, ' - ', m.modulo) AS rotulo
FROM "._DBCARBON."._modulo m,"._DBCARBON."._mtotabcol tc
WHERE tc.primkey ='Y'
 AND exists (select 1 from "._DBCARBON."._mtotabcol t where t.tab = m.tab and col='alteradoem' )
 AND tc.tab = m.tab
 AND m.status = 'ATIVO'
 ORDER BY m.rotulomenu";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar unidade sql=".$sql);
$virg="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['modulo'].'":"'.$row['rotulo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>