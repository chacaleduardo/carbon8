<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="SELECT a.idformapagamento,a.descricao from empresa e join formapagamento a on(a.idempresa = e.idempresa and a.status='ATIVO')
    where exists (select 1 from matrizconf m where m.idmatriz =".cb::idempresa()."  and m.idempresa=e.idempresa) 
    and e.status='ATIVO'
				UNION
				SELECT aa.idformapagamento,aa.descricao from empresa ee join formapagamento aa on(aa.idempresa=ee.idempresa and aa.status='ATIVO')
                where ee.idempresa =".cb::idempresa()."  order by descricao";
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar formapagamento sql=".$sql);
	
	$virg="";
	$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idformapagamento'].'":"'.$row['descricao'].'"}';
		$virg=",";
	}
	$json.="]";
	echo($json);
}
?>