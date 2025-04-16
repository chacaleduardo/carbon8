<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql ="SELECT a.idagencia,a.agencia from empresa e join agencia a on(a.idempresa = e.idempresa and a.status='ATIVO')
    where exists (select 1 from matrizconf m where m.idmatriz =".cb::idempresa()." and m.idempresa=e.idempresa) 
    and e.status='ATIVO'
				UNION
				SELECT aa.idagencia,aa.agencia from empresa ee join agencia aa on(aa.idempresa=ee.idempresa and aa.status='ATIVO')
                where ee.idempresa =".cb::idempresa()." order by agencia";
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar agencia sql=".$sql);
	
	$virg="";
	$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idagencia'].'":"'.$row['agencia'].'"}';
		$virg=",";
	}
	$json.="]";
	echo($json);
}
?>