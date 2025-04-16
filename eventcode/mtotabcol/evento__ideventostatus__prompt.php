<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="select ideventostatus, concat(ideventostatus,' ', rotulo) as eventostatus
			from eventostatus 
			where idempresa = ".cb::idempresa();
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipos de evento=".$sql);
	
	$virg="";
	$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['ideventostatus'].'":"'.$row['eventostatus'].'"}';
		$virg=",";
	}
	$json.="]";
	echo($json);
}
?>
