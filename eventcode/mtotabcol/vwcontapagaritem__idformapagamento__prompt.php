<?
require_once("../../inc/php/functions.php");
if(logado()==true){
	
	$sql="select idformapagamento,descricao from formapagamento where 1
		 ".getidempresa('idempresa','pessoa')."
		and status= 'ATIVO' order by descricao";
	
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