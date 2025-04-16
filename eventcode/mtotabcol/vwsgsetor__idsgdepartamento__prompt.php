<? 
include_once("../../inc/php/functions.php");

$query = "SELECT sgdep.idsgdepartamento, sgdep.departamento
            FROM sgdepartamento sgdep
            JOIN sgsetor s ON(s.idsgdepartamento = sgdep.idsgdepartamento)
            WHERE sgdep.idempresa = ".cb::idempresa()."
            GROUP BY sgdep.idsgdepartamento;";

$result = d::b()->query($query) or die("Error: ".mysql_error(d::b()));

$arrayJson= [];

while($item = mysql_fetch_assoc($result))
{
    array_push($arrayJson, '{"'.$item['idsgdepartamento'].'": "'.$item['departamento'].'"}');
}

echo "[".implode(',', $arrayJson)."]";
