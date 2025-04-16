<? 
include_once("../../inc/php/functions.php");

$query = "SELECT qry.*
            FROM sgconselho c
            JOIN (
                -- Unidades dos conselho
                SELECT u.idunidade, u.unidade, uo.idobjeto as idsgconselho
                FROM unidadeobjeto uo
                JOIN unidade u ON(u.idunidade = uo.idunidade AND uo.tipoobjeto = 'sgconselho')
                WHERE u.status = 'ATIVO'
                UNION
                -- Unidades das areas
                SELECT u.idunidade, u.unidade, ov.idobjeto as idsgconselho
                FROM objetovinculo ov
                JOIN objetovinculo ov2 ON(ov2.idobjeto = ov.idobjetovinc and ov2.tipoobjeto = 'sgarea' and ov.tipoobjeto = 'sgconselho')
                JOIN unidadeobjeto uo ON(uo.idobjeto = ov2.idobjeto AND uo.tipoobjeto = 'sgarea')
                JOIN unidade u ON(u.idunidade = uo.idunidade)
                WHERE u.status = 'ATIVO'
                GROUP BY u.idunidade
                UNION
                -- Unidade dos departamentos
                SELECT u.idunidade, u.unidade, ov.idobjeto as idsgconselho
                FROM objetovinculo ov
                JOIN objetovinculo ov2 ON(ov2.idobjeto = ov.idobjetovinc and ov2.tipoobjeto = 'sgarea' and ov.tipoobjeto = 'sgconselho')
                JOIN unidadeobjeto uo ON(uo.idobjeto = ov2.idobjetovinc and uo.tipoobjeto = 'sgdepartamento' AND ov2.tipoobjeto = 'sgarea')
                JOIN unidade u ON(u.idunidade = uo.idunidade)
                WHERE u.status = 'ATIVO'
                GROUP BY u.idunidade
                UNION
                -- Unidade dos setores
                SELECT u.idunidade, u.unidade, ov.idobjeto as idsgconselho
                FROM objetovinculo ov 
                JOIN objetovinculo ov2 ON(ov2.idobjeto = ov.idobjetovinc and ov2.tipoobjeto = 'sgarea' and ov.tipoobjeto = 'sgconselho')
                JOIN objetovinculo ov3 ON(ov3.idobjeto = ov2.idobjetovinc and ov3.tipoobjeto = 'sgdepartamento' and ov2.tipoobjeto = 'sgarea')
                JOIN unidadeobjeto uo ON(uo.idobjeto = ov3.idobjetovinc and uo.tipoobjeto = 'sgsetor' and ov3.tipoobjetovinc  = 'sgsetor' and ov3.tipoobjeto = 'sgdepartamento')
                JOIN unidade u ON(u.idunidade = uo.idunidade)
                WHERE u.status = 'ATIVO'
                GROUP BY u.idunidade
            ) as qry ON(qry.idsgconselho = c.idsgconselho);";

$result = d::b()->query($query) or die("Error: ".mysql_error(d::b()));

$arrayJson= [];

while($item = mysql_fetch_assoc($result))
{

    array_push($arrayJson, '{"'.$item['idunidade'].'": "'.$item['unidade'].'"}');
}

echo "[".implode(',', $arrayJson)."]";
