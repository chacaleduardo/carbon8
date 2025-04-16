<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="select idpessoa,nome from pessoa 
		where idtipopessoa = 12 
		 ".getidempresa('idempresa','pessoa')."
		and status= 'ATIVO' order by nome";
	
	$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar representante sql=".$sql);
	
	$virg="";
	$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idpessoa'].'":"'.$row['nome'].'"}';
		$virg=",";
	}
	$json.="]";
	echo($json);
}
?>