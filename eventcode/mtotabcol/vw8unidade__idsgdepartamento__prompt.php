<?
require_once("../../inc/php/functions.php");
$sql="SELECT idsgdepartamento, 
			 departamento
		FROM sgdepartamento
       WHERE status = 'ATIVO' ".getidempresa('idempresa','departamento')."  
	   order by departamento";
$res = mysql_query($sql) or die(mysql_error()." Erro ao buscar prodserv sql=".$sql);
$virg="";
$json="";
$json.="[";
	while($row=mysql_fetch_assoc($res)){
		$json.=$virg.'{"'.$row['idsgdepartamento'].'":"'.$row['departamento'].'"}';
		$virg=",";
	}
$json.="]";
echo($json);
?>