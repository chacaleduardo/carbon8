<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="select  
               idimmsgconf, titulocurto
			from
				immsgconf
			where status = 'ATIVO'
            order by titulocurto";
			
			//die($sql);
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipo do evento sql=".$sql);
	
	$virg="";
	$jsonz.="[";
	while($row=mysql_fetch_assoc($res)){
		$jsonz.=$virg.'{"'.$row['idimmsgconf'].'":"'.$row['titulocurto'].'"}';
		$virg=",";
	}
	$jsonz.="]";
	echo($jsonz);
}
?>