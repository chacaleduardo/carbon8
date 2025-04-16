<?
require_once("../inc/php/functions.php");

$jwt = validaToken();

$exercicio = d::b()->real_escape_string($_GET["exercicio"]);
$idempresa = d::b()->real_escape_string($_GET["idempresa"]);

if(!empty($exercicio)){
    $produtosSemForecast  = "
        SELECT p.idprodserv, concat(t.tipoprodserv,' - ',SUBSTRING(p.descr, 1, 60))
        FROM laudo.prodserv p 
        INNER JOIN tipoprodserv t ON t.idtipoprodserv = p.idtipoprodserv
        WHERE p.status = 'ATIVO' AND p.venda = 'Y'  AND p.produtoacabado = 'Y'
        AND p.tipo='PRODUTO' AND p.idempresa = " . $idempresa  . "
        AND NOT EXISTS ( select 1 from planejamentoprodserv ps 
                            where p.idprodserv = ps.idprodserv 
                            and ps.exercicio = " . $exercicio . " )
        ORDER BY t.tipoprodserv,p.descr;
    ";

    echo fillselect($produtosSemForecast);
}

?>