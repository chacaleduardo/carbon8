<?include_once("../../inc/php/functions.php");
if(logado()==true){
$sql="SELECT tt.idtagtipo,tt.tagtipo
FROM tagtipo tt
where status = 'ATIVO' and tagtipo != ''
and exists (select 1 from tag ta where ta.idtagtipo = tt.idtagtipo)
".getidempresa('idempresa','tagtipo')."
Group by tagtipo
ORDER BY tagtipo";


$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar pessoa sql=".$sql);
$virg="";
$json="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idtagtipo'].'":"'.$row['tagtipo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>