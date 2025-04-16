<? 
include_once("../../inc/php/functions.php");

$query = "SELECT c.idsgconselho, c.conselho
            FROM sgconselho c
            JOIN sgarea a ON(a.idsgconselho = c.idsgconselho)
            WHERE c.idempresa = ".cb::idempresa()."
            GROUP BY c.idsgconselho;";

$result = d::b()->query($query) or die("Error: ".mysql_error(d::b()));

$arrayJson= [];

while($item = mysql_fetch_assoc($result))
{
    array_push($arrayJson, '{"'.$item['idsgconselho'].'": "'.$item['conselho'].'"}');
}

echo "[".implode(',', $arrayJson)."]";
