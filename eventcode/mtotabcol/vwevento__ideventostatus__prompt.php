<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="select  
                et.ideventostatus,concat(t.eventotipo,' - ',s.rotulo) as rotulo
            from eventotipostatus et 
            join eventostatus s on (s.ideventostatus = et.ideventostatus)
            join eventotipo t on (t.ideventotipo=et.ideventotipo)
            where s.status='ATIVO' 
             ".getidempresa('s.idempresa','eventostatus')." order by rotulo";
			
			//die($sql);
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipo do evento sql=".$sql);
	
	$virg="";
	$jsonz.="[";
	while($row=mysql_fetch_assoc($res)){
		$jsonz.=$virg.'{"'.$row['ideventostatus'].'":"'.$row['rotulo'].'"}';
		$virg=",";
	}
	$jsonz.="]";
	echo($jsonz);
}
?>