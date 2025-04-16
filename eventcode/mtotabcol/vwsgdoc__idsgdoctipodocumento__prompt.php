<?require_once("../../inc/php/functions.php");
if(logado()==true){
$sql="select a.idsgdoctipodocumento,concat(tipodocumento,'-',ifnull(t.rotulo,'')) as tipodocumento 
	from sgdoctipodocumento a left join sgdoctipo t on(t.idsgdoctipo = a.idsgdoctipo)
	where 1 and a.status='ATIVO'
	-- ".getidempresa('idempresa','sgdoctipodocumento')."
	order by tipodocumento";

	
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipodocumento sql=".$sql);
$virg="";
$json="";
$json.="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['idsgdoctipodocumento'].'":"'.$row['tipodocumento'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
}
?>
