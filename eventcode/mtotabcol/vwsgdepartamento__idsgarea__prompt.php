<? 
include_once("../../inc/php/functions.php");

$query = "SELECT a.idsgarea, a.area
            FROM sgarea a
            JOIN sgdepartamento sgdep ON(a.idsgarea = sgdep.idsgarea)
            WHERE sgdep.idempresa = ".cb::idempresa()."
            GROUP BY a.idsgarea";

$result = d::b()->query($query) or die("Error: ".mysql_error(d::b()));

$arrayJson= [];

while($item = mysql_fetch_assoc($result))
{
    array_push($arrayJson, '{"'.$item['idsgarea'].'": "'.$item['area'].'"}');
}

echo "[".implode(',', $arrayJson)."]";
