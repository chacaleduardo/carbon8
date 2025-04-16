<?
include_once("../../inc/php/functions.php");

if(!empty($_GET["ocultar"])){
    $ocultar = "AND fs.ocultar IN ('" . implode("','", explode(',', $_GET["ocultar"])) . "')";
}else{
    $ocultar = "";
}

$query = "SELECT fs.idfluxostatus, cs.rotulo
            FROM fluxostatus fs
            JOIN carbonnovo._status cs ON(fs.idstatus = cs.idstatus)
            WHERE fs.idfluxostatus IN(
                SELECT rhf.idfluxostatus
                FROM vwrhfolha rhf
            ) ".$ocultar.";";

$result = d::b()->query($query) or die("Error: ".mysql_error(d::b()));

$arrayJson= [];

while($item = mysql_fetch_assoc($result))
{
    array_push($arrayJson, '{"'.$item['idfluxostatus'].'": "'.$item['rotulo'].'"}');
}

echo "[".implode(',', $arrayJson)."]";