<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
	$prefu="stdin_";
	include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
}

$_inspecionar_sql = ($_GET["_inspecionar_sql"]=="Y")?true:false;
echo "Início: ".date("d/m/Y H:i:s", time()).'<br>'; 
$sessionid = session_id();//PEGA A SESSÃO  


$sql = "INSERT INTO tempnfs (idempresa, idpessoa, nome, fechados, abertos, menordata, menordataymd, maiordata, exercicio, cobrar, criadopor, criadoem, alteradopor, alteradoem)
            (SELECT p.idempresa AS idempresa,
                    p.idpessoa AS idpessoa,
                    p.nome AS nome,
                    COUNT(0) AS fechados,
                    (SELECT COUNT(0) AS 'count(0)'
                        FROM tipoteste t JOIN resultado rr ON t.idtipoteste = rr.idtipoteste
                        JOIN amostra aa ON aa.idamostra = rr.idamostra
                        JOIN unidade uu ON uu.idunidade = aa.idunidade
                        WHERE ((rr.status IN ('ABERTO' , 'PROCESSANDO'))
                                AND (((uu.idtipounidade = 1)
                                AND (rr.cobrar = 'Y'))
                                OR ((uu.idtipounidade <> 1)
                                AND (rr.cobrancaobrig = 'Y')))
                                AND (aa.idpessoa = p.idpessoa))) AS abertos,
                    DATE_FORMAT(MIN(a.dataamostra), _utf8mb4 '%d/%m/%Y') AS menordata,
                    MIN(a.dataamostra) AS menordataymd,
                    DATE_FORMAT(MAX(a.dataamostra), _utf8mb4 '%d/%m/%Y') AS maiordata,
                    MAX(a.exercicio) AS exercicio,
                    r.cobrar AS cobrar,
                    'cron' AS criadopor,
                    now() AS criadoem,
                    'cron' AS alteradopor,
                    r.alteradoem AS alteradoem
                FROM (pessoa p JOIN amostra a ON a.idpessoa = p.idpessoa
                JOIN resultado r ON r.idamostra = a.idamostra
                JOIN unidade u ON ((u.idunidade = a.idunidade)))
                WHERE ((((u.idtipounidade = 1)
                        AND (r.cobrar = 'Y'))
                        OR ((u.idtipounidade <> 1)
                        AND (r.cobrancaobrig = 'Y')))
                    AND (r.status IN ('ABERTO' , 'PROCESSANDO', 'FECHADO', 'ASSINADO'))
                    AND EXISTS( SELECT 
                        1 AS '1'
                    FROM
                        (notafiscal nf JOIN notafiscalitens nfi ON nf.idnotafiscal = nfi.idnotafiscal)
                    WHERE
                        (nfi.idresultado = r.idresultado))
                    IS FALSE)
            GROUP BY p.idpessoa , p.nome , r.cobrar)
            ON DUPLICATE KEY 
            UPDATE tempnfs.idpessoa = tempnfs.idpessoa;";

d::b()->query($sql) or die("Erro ao atualizar NFS: ".mysql_error(d::b()));

if($_inspecionar_sql == 'Y')
{
    echo $sql;
}
echo "FIM: ".date("d/m/Y H:i:s", time()).'<br>'; 

?>