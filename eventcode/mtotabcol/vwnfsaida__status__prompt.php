<?
require_once("../../inc/php/functions.php");

$sql = "SELECT s.statustipo, s.rotulo
          FROM fluxo f 
          JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
          JOIN carbonnovo._status s ON s.idstatus = fs.idstatus
         WHERE f.status = 'ATIVO' AND f.modulo = 'pedido'
         ORDER BY ordem";

$res=mysql_query($sql) or die("NF Entrada - Erro ao recuperar status: ".mysql_error());
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$row['rotulo'].'":"'.$row['rotulo'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>