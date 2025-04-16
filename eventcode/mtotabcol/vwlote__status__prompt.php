<?
require_once("../../inc/php/functions.php");

$_modulo = $_GET['_modulo'];

if(strpos($_modulo, 'lote') === false){
    $_modulo = 'semente';
}

$sql = "SELECT s.statustipo, s.rotulo
          FROM fluxo f 
          JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
          JOIN carbonnovo._status s ON s.idstatus = fs.idstatus
         WHERE f.status = 'ATIVO' AND f.modulo = '$_modulo'";

$res=mysql_query($sql) or die("Lote - Erro ao recuperar status: ".mysql_error());
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$row['statustipo'].'":"'.$row['rotulo'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>  
