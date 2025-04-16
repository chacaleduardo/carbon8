<? 
include_once("../../inc/php/functions.php");

$query = "SELECT qry.*
            FROM sgarea a
            JOIN (
                SELECT u.idunidade, u.unidade, uo.idobjeto  as idsgarea
                FROM unidadeobjeto uo
                JOIN unidade u ON(u.idunidade = uo.idunidade)
                WHERE uo.tipoobjeto = 'sgarea' 
                GROUP BY u.idunidade
                UNION
                -- Unidade dos departamentos
                SELECT u.idunidade, u.unidade, ov.idobjeto  as idsgarea
                FROM objetovinculo ov
                JOIN unidadeobjeto uo ON(uo.idobjeto = ov.idobjetovinc and uo.tipoobjeto = 'sgdepartamento' AND ov.tipoobjeto = 'sgarea')
                JOIN unidade u ON(u.idunidade = uo.idunidade)
                GROUP BY u.idunidade
                UNION
                -- Unidade dos setores
                SELECT u.idunidade, u.unidade, ov.idobjeto  as idsgarea
                FROM objetovinculo ov 
                JOIN objetovinculo ov2 ON(ov2.idobjeto = ov.idobjetovinc and ov2.tipoobjeto = 'sgdepartamento' and ov.tipoobjeto = 'sgarea')
                JOIN unidadeobjeto uo ON(uo.idobjeto = ov2.idobjetovinc and uo.tipoobjeto = 'sgsetor' and ov2.tipoobjetovinc  = 'sgsetor' and ov2.tipoobjeto = 'sgdepartamento')
                JOIN unidade u ON(u.idunidade = uo.idunidade)
                GROUP BY u.idunidade
            ) as qry ON(qry.idsgarea = a.idsgarea);";

$result = d::b()->query($query) or die("Error: ".mysql_error(d::b()));

$arrayJson= [];

while($item = mysql_fetch_assoc($result))
{

    array_push($arrayJson, '{"'.$item['idunidade'].'": "'.$item['unidade'].'"}');
}

echo "[".implode(',', $arrayJson)."]";
