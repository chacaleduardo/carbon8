<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="select ideventotipo, concat(ideventotipo,' ', eventotipo) as eventotipo
			from eventotipo 
			where idempresa = ".cb::idempresa();
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipos de evento=".$sql);
	
	$virg="";
	$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['ideventotipo'].'":"'.$row['eventotipo'].'"}';
		$virg=",";
	}
	$json.="]";
	echo($json);
}
?>
