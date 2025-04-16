<?
require_once("../../inc/php/functions.php");

$modulo = $_GET['_modulo'];

if(!empty($_GET["ocultar"])){
    $ocultar = "AND fs.ocultar IN ('" . implode("','", explode(',', $_GET["ocultar"])) . "')";
}else{
    $ocultar = "";
}

$sql = "SELECT fs.idfluxostatus, s.rotulo
                from
                fluxostatus fs
                 JOIN carbonnovo._status s ON s.idstatus = fs.idstatus
                 where exists (select 1 from fluxo f where fs.idfluxo = f.idfluxo  AND f.modulo = '$modulo') 
                 ".$ocultar."
                 order by fs.ordem asc";

$res=mysql_query($sql) or die("Cotação - Erro ao recuperar idfluxostatus: ".mysql_error());
$virg="";
$json = "";
while($row=mysql_fetch_assoc($res)){
    $json.=$virg.'{"'.$row['idfluxostatus'].'":"'.$row['rotulo'].'"}';
    $virg=",";
}
echo("[".$json."]");
?>  
