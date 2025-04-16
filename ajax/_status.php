<?
require_once("../inc/php/functions.php");

$opcao              = filter_input(INPUT_GET, "vopcao");
$idfluxostatus      = filter_input(INPUT_GET, "vidfluxostatus");
$idfluxo            = filter_input(INPUT_GET, "vidfluxo");

if (empty($opcao)) {
    die("Opção: Variável POST não enviada corretamente Fluxo Status!");
} else {

    if ($opcao == "getLp") {
        $_sql_ = "SELECT * FROM (SELECT l.idlp,
                                        m.idfluxostatus,
                                        fs.idfluxo,
                                        CONCAT(e.sigla,' - ', l.descricao) AS descricao,
                                        IFNULL((SELECT permissao FROM fluxostatuslp fl WHERE fl.idfluxostatuslp = m.idfluxostatuslp), 'n') AS permissao, 
                                        IFNULL((SELECT permissaobotao FROM fluxostatuslp fl WHERE fl.idfluxostatuslp = m.idfluxostatuslp), 'n') AS permissaobotao,
                                        IFNULL((SELECT idfluxostatuslp FROM fluxostatuslp fl WHERE fl.idfluxostatuslp = m.idfluxostatuslp), 0) AS idfluxostatuslp
                                   FROM "._DBCARBON."._lp l JOIN "._DBCARBON."._lpobjeto lo ON lo.idlp = l.idlp
                                   JOIN empresa e ON e.idempresa = l.idempresa
                                   LEFT JOIN fluxostatuslp m ON (l.idlp = m.idlp) AND m.idfluxostatus = '$idfluxostatus' 
                                   LEFT JOIN fluxostatus fs ON fs.idfluxostatus = m.idfluxostatus AND fs.idfluxo = '$idfluxo'
                                  WHERE l.status = 'ATIVO' 
                                UNION 
                                 SELECT l.idlp, 
                                        '', 
                                        '', 
                                        CONCAT(e.sigla,' - ', l.descricao) AS descricao,
                                        'n' AS permissao, 
                                        'n' AS permissaobotao,
                                        0
                                   FROM "._DBCARBON."._lp l JOIN "._DBCARBON."._lpobjeto lo ON lo.idlp = l.idlp
                                   JOIN empresa e ON e.idempresa = l.idempresa
                                   JOIN "._DBCARBON."._lpmodulo lm on lm.idlp = l.idlp
                                   JOIN "._DBCARBON."._modulo m on m.modulo = lm.modulo
                                   JOIN fluxo f on f.modulo = m.modulo  AND f.idfluxo = '$idfluxo'
                                   JOIN fluxostatus fs on fs.idfluxo = f.idfluxo and fs.idfluxostatus = '$idfluxostatus'                                
                                  WHERE l.status = 'ATIVO') AS lp
                GROUP BY idlp
                ORDER BY permissao DESC, permissaobotao DESC, descricao ASC;";

        $_res_ = d::b()->query($_sql_) or die("Erro ao consultar LP's");

        $i = 0;

        if(mysql_num_rows($_res_) > 0){
            $arrtmp = array();
            while($_r_ = mysql_fetch_assoc($_res_)){
                $arrtmp[$i]["idlp"] = $_r_["idlp"];
                $arrtmp[$i]["descricao"] = $_r_["descricao"];
                $arrtmp[$i]["permissao"] = $_r_["permissao"];
                $arrtmp[$i]["permissaobotao"] = $_r_["permissaobotao"];
                $arrtmp[$i]["idfluxostatus"] = $_r_["idfluxostatus"];
                $arrtmp[$i]["idfluxostatuslp"] = $_r_["idfluxostatuslp"];
                $i++;
            }
            
            $jLp = json_encode($arrtmp);
        }else{
            $jLp = 0;
        }
    }

    print $jLp;
}
?>