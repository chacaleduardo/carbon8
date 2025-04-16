<?
require_once("../../inc/php/functions.php");

if(!empty($_GET["ocultar"])){
    $ocultar = "AND fs.ocultar IN ('" . implode("','", explode(',', $_GET["ocultar"])) . "')";
}else{
    $ocultar = "";
}

$sql = "SELECT fs.idfluxostatus, s.rotulo
          FROM fluxo f 
          JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
          JOIN carbonnovo._status s ON s.idstatus = fs.idstatus
         WHERE f.status = 'ATIVO' AND f.modulo = 'solmat' ".$ocultar."
         ORDER BY ordem";

$res=mysql_query($sql) or die("solmat Saída - Erro ao recuperar status: ".mysql_error());
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$row['idfluxostatus'].'":"'.$row['rotulo'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>