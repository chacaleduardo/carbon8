<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="select ideventotipo,eventotipo from eventotipo 
                where status = 'ATIVO' 
                  order by eventotipo";
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipo do evento sql=".$sql);
	
	$virg="";
	$jsonx.="[";
	while($row=mysql_fetch_assoc($res)){
		$jsonx.=$virg.'{"'.$row['ideventotipo'].'":"'.$row['eventotipo'].'"}';
		$virg=",";
	}
	$jsonx.="]";
	echo($jsonx);
}
?>