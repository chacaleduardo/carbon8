<?
//[{"ABERTO":"Aberto"},{"PREVISAO":"Previsão"},{"APROVADO":"Aprovado"},{"RECEBIDO":"Recebido"},{"DIVERGENCIA":"Divergência"},{"CONCLUIDO":"Concluido"},{"CANCELADO":"Cancelado"}]
require_once("../../inc/php/functions.php");

$sql = "select
s.statustipo, s.rotulo
from 
fluxo f 
join fluxostatus fs on fs.idfluxo = f.idfluxo
join carbonnovo._status s on s.idstatus = fs.idstatus
where
f.status = 'ATIVO' and f.modulo = 'nfentrada'";

$res=mysql_query($sql) or die("NF Entrada - Erro ao recuperar status: ".mysql_error());


//monta o resultado em formato JSON para autocomplete
//$r[0]=primeira coluna select  /  $r[1]=segunda coluna select
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
$json.=$virg.'{"'.$row['rotulo'].'":"'.$row['rotulo'].'"}';
$virg=",";
}
//$retorno = json_encode($json);
echo("[".$json."]");
?>