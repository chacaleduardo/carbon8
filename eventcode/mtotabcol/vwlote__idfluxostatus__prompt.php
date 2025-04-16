<?
require_once("../../inc/php/functions.php");

$modulo = $_GET['_modulo'];

if(!empty($_GET["ocultar"])){
    $ocultar = "AND fs.ocultar IN ('" . implode("','", explode(',', $_GET["ocultar"])) . "')";
}else{
    $ocultar = "";
}


$sql = "SELECT fs.idfluxostatus, s.rotulo
          FROM fluxo f 
          JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
          JOIN carbonnovo._status s ON s.idstatus = fs.idstatus
         WHERE f.status = 'ATIVO' AND f.modulo = '$modulo' ".$ocultar."";

$res=mysql_query($sql) or die("Lote - Erro ao recuperar idfluxostatus: ".mysql_error());
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$row['idfluxostatus'].'":"'.$row['rotulo'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>  
