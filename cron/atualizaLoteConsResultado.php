<?
session_start();
$sessionid = session_id(); //PEGA A SESSÃO

ini_set("display_errors", "1");
error_reporting(E_ALL);

if (defined('STDIN')) { //se estiver sendo executao em linhade comando
	require_once("/var/www/carbon8/inc/php/functions.php");
} else { //se estiver sendo executado via requisicao http
	require_once("../inc/php/functions.php");
}

echo '<pre>';

$sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
		 		VALUES ('1', '".$grupo."', 'cron', 'atualizaTestes', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

$exercicio = (empty($_GET['exercicio'])) ?  '' : ' AND a.exercicio = '.$_GET['exercicio'];
//atualizar o estoque dos produtos
$sqlp = "SELECT r.idresultado FROM resultado r JOIN amostra a ON a.idamostra = r.idamostra
        WHERE EXISTS (SELECT 1 FROM lotecons lc WHERE lc.idobjeto = r.idresultado AND lc.tipoobjeto = 'resultado' AND lc.tipoobjetoconsumoespec IS NULL)
        $exercicio
        ORDER BY r.idresultado DESC";

$resp = d::b()->query($sqlp) or die("erro ao buscar resultado: ".mysqli_error(d::b())."<br>".$sqlp);
while ($rowp = mysqli_fetch_assoc($resp)) 
{
    $sqlFormula = "SELECT pf.idprodservformula, c.idlotecons, r.idresultado, r.idempresa
                    FROM resultado r JOIN amostra a ON a.idamostra = r.idamostra
                    JOIN prodservformula pf ON pf.idprodserv = r.idtipoteste
                    JOIN prodservformulains pfi ON pfi.idprodservformula = pf.idprodservformula
                    JOIN prodserv p ON p.idprodserv = pfi.idprodserv
                    JOIN lote l ON l.idprodserv  = p.idprodserv
                    JOIN lotefracao lf ON lf.idlote = l.idlote
                    JOIN lotecons c ON lf.idlote = c.idlote AND  lf.idlotefracao = c.idlotefracao AND c.idobjeto = r.idresultado AND c.tipoobjeto = 'resultado'
                    JOIN resultadoprodservformula rp ON rp.idresultado = r.idresultado AND rp.status = 'ATIVO' AND rp.idprodservformula = pf.idprodservformula
                    WHERE pfi.status = 'ATIVO' 
                    AND r.idresultado = ".$rowp['idresultado']."
                    AND c.tipoobjetoconsumoespec IS NULL;";

    echo $sqlFormula;
    echo '<br>';
    echo '<pre>';

    $resFormula = d::b()->query($sqlFormula) or die("erro ao buscar resultado: ".mysqli_error(d::b())."<br>".$sqlp);
    while ($rowFormula = mysqli_fetch_assoc($resFormula)) 
    {
        $sqlp1 = "UPDATE laudo.lotecons SET tipoobjetoconsumoespec = 'prodservformula', idobjetoconsumoespec = '".$rowFormula['idprodservformula']."' WHERE (`idlotecons` = '".$rowFormula['idlotecons']."');";
        echo $sqlp1;
        echo '<br>';
        echo '<pre>';

        $resp1 = d::b()->query($sqlp1) or die("erro ao atualizar quantidade em estoque: ".mysqli_error(d::b())."<br>".$sqlp1);
        
        $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                        VALUES ('".$rowFormula['idempresa']."', 'Formula: ".$rowFormula['idprodservformula']."', 'consumoTeste', '".$rowFormula['idresultado']."', 
                                'UPDATE laudo.lotecons SET tipoobjetoconsumoespec = NULL, idobjetoconsumoespec = NULL WHERE (idlotecons = \'".$rowFormula['idlotecons']."\');', 
                                'UPDATE laudo.lotecons SET tipoobjetoconsumoespec = \'prodservformula\', idobjetoconsumoespec = \'".$rowFormula['idprodservformula']."\' WHERE (`idlotecons` = \'".$rowFormula['idlotecons']."\');', 
                                'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
        echo $sqli;
        echo '<br>';
        echo '<pre>';
    }
}



