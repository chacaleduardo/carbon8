<? 
include_once("../../inc/php/functions.php");

$query = "SELECT u.idunidade, u.unidade
            FROM unidadeobjeto uo
            JOIN sgsetor s ON(s.idsgsetor = uo.idobjeto and uo.tipoobjeto = 'sgsetor')
            JOIN unidade u ON(u.idunidade = uo.idunidade)
            WHERE u.status = 'ATIVO';";

$result = d::b()->query($query) or die("Error: ".mysql_error(d::b()));

$arrayJson= [];

while($item = mysql_fetch_assoc($result))
{

    array_push($arrayJson, '{"'.$item['idunidade'].'": "'.$item['unidade'].'"}');
}

echo "[".implode(',', $arrayJson)."]";
