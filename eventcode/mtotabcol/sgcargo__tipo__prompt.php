<?
require_once("../../inc/php/functions.php");
$sql="
    select 'ADVOGADO' as id,'ADVOGADO' as tipo union
    select 'ANALISTA','ANALISTA' union
    select 'ASSISTENTE','ASSISTENTE' union
    select 'AUXILIAR','AUXILIAR' union
    select 'COZINHEIRO','COZINHEIRO' union
    select 'DIRETOR','DIRETOR' union
    select 'ENGENHEIRO','ENGENHEIRO' union
    select 'ESPECIALISTA','ESPECIALISTA' union
    select 'GERENTE','GERENTE' union
    select 'JOVEM APRENDIZ','JOVEM APRENDIZ' union
    select 'MESTRE DE OBRAS','MESTRE DE OBRAS' union
    select 'MOTORISTA','MOTORISTA' union
    select 'PATOLOGISTA','PATOLOGISTA' union
    select 'PEDREIRO','PEDREIRO' union
    select 'PINTOR','PINTOR' union
    select 'PROGRAMADOR','PROGRAMADOR' union
    select 'SUPERVISOR','SUPERVISOR' union
    select 'TÉCNICO','TÉCNICO' ";
$res=mysql_query($sql) or die(mysql_error()." Erro ao buscar tipo sql=".$sql);
$virg="";
$json="[";
while($row=mysql_fetch_assoc($res)){
	$json.=$virg.'{"'.$row['id'].'":"'.$row['tipo'].'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>