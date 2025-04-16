<? 
include_once("../../inc/php/functions.php");

$query = "SELECT qry.*
            FROM sgdepartamento sgdep
            JOIN (
                SELECT u.idunidade, u.unidade, uo.idobjeto as idsgdepartamento, uo.idobjeto as dep, uo.tipoobjeto
                FROM unidadeobjeto uo
                JOIN unidade u ON(u.idunidade = uo.idunidade)
                WHERE uo.tipoobjeto = 'sgdepartamento' 
                GROUP BY u.idunidade
                UNION
                -- Unidade dos setores
                SELECT u.idunidade, u.unidade, ov.idobjeto as idsgdepartamento, ov.idobjetovinc as idsgsetor, uo.tipoobjeto
                FROM objetovinculo ov 
                JOIN unidadeobjeto uo ON(uo.idobjeto = ov.idobjetovinc and uo.tipoobjeto = 'sgsetor' and ov.tipoobjetovinc  = 'sgsetor' and ov.tipoobjeto = 'sgdepartamento')
                JOIN unidade u ON(u.idunidade = uo.idunidade)
                GROUP BY u.idunidade
            ) as qry ON(qry.idsgdepartamento = sgdep.idsgdepartamento);";

$result = d::b()->query($query) or die("Error: ".mysql_error(d::b()));

$arrayJson= [];

while($item = mysql_fetch_assoc($result))
{

    array_push($arrayJson, '{"'.$item['idunidade'].'": "'.$item['unidade'].'"}');
}

echo "[".implode(',', $arrayJson)."]";
