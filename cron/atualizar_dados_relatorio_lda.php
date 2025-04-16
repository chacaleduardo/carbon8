<?
ini_set("display_errors", "1");
error_reporting(E_ALL);

echo 'Início: '.date('Y-m-d H:i:s');

if (defined('STDIN')) { //se estiver sendo executao em linhade comando
    include_once("/var/www/carbon8/inc/php/functions.php");
    include_once("/var/www/carbon8/inc/php/laudo.php");
} else { //se estiver sendo executado via requisicao htt
    include_once("../inc/php/functions.php");
    include_once("../inc/php/laudo.php");
}

echo '<pre>';

$debug = false;
if (isset($_GET["_inspecionar_sql"])) {
    $debug = true;
}

if($_GET['exercicio']) {
    $condicaoWhere = " AND a.exercicio =   '".$_GET['exercicio']."'";
} else {
    $condicaoWhere = ' AND r.alteradoem  >= NOW() - INTERVAL 24 HOUR';
}

            
$sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                VALUES ('2', 'hora', 'cron', 'atualizarelatoriolda', 'HoraInicial', '".date('Y-m-d H:i:s')."', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

$sqlBuscarTestes = "SELECT idresultado FROM resultado r JOIN amostra a ON a.idamostra = r.idamostra WHERE a.idunidade = 9 AND a.idempresa = 2 $condicaoWhere  ORDER BY r.idresultado";

if ($debug) {
    echo '<pre>';
    echo 'Query Busca Testes: '.$sqlBuscarTestes;
    echo '</pre>';
}

$testesDeletadosArray = [];

$resBuscarTestes = d::b()->query($sqlBuscarTestes) or die("Erro ao buscar Testes sql: ".mysqli_error(d::b())."<br><br><br>".$sqlBuscarTestes);

while ($rowBuscarTestes = mysqli_fetch_assoc($resBuscarTestes)) {

    $sqlRel = "SELECT CONCAT(r.idresultado, '', IFNULL(amt.indice, 0)) AS idprkey,
                    CONCAT(a.idregistro, '/', a.exercicio) AS tra,
                    a.idregistro,
                    a.criadoem AS dataamostra,
                    a.idpessoaresponsavelof,
                    a.responsavelof,
                    a.descricao,
                    a.exercicio,
                    a.idamostra,
                    p.idpessoa,
                    p.nome,
                    pd.idprodserv,
                    pd.descr,
                    pd.tipoteste,
                    pd.laboratorio,
                    ve.tipoespecie,
                    ve.especie,
                    ve.finalidade,
                    ve.especietipofinalidade,
                    ve.tipoespeciefinalidade,
                    ve.idplantel,
                    r.idresultado,
                    r.criadoem as datateste,
                    r.status,
                    s.name as nomesemente, 
                    s.value as valorsemente,
                    s.titulo as titulosemente,
                    res.name as nomeresultado, 
                    res.value as valorresultado,
                    res.titulo as tituloresultado,
                    obs.name as nomeobs, 
                    obs.value as valorobs,
                    obs.titulo as tituloobs,
                    amt.name as nomeamostra, 
                    amt.value as valoramostra,
                    amt.titulo as tituloamostra,
                    amt.indice as indice, 
                    tp.name as nometipificacao, 
                    tp.value as valortipificacao,
                    tp.titulo as titulotipificacao,
                    ep.name as nomeespecie, 
                    ep.value as valorespecie,
                    ep.titulo as tituloespecie,
                    a.idempresa,
                    (SELECT fh.criadoem FROM fluxostatushist fh JOIN fluxostatus f ON f.idfluxostatus = fh.idfluxostatus 
                        JOIN carbonnovo._status s ON s.idstatus = f.idstatus WHERE fh.idmodulo = r.idresultado AND fh.modulo = 'resultsuinos' AND s.statustipo = 'FECHADO'
                        AND fh.status <> 'INATIVO' LIMIT 1) as datafechado, 
                    IF(j1.titulo IS NULL, '', REGEXP_REPLACE(GROUP_CONCAT(CONCAT(j1.titulo, ': ', j1.valor) ORDER BY j1.name SEPARATOR ' <br /> '), ' / OBSERVAÇÃO: .*', '')) AS dadosresult,
                    j1.*,
                    a.criadoem,
                    a.criadopor,
                    a.alteradoem,
                    a.alteradopor
                    FROM resultado r LEFT JOIN JSON_TABLE(r.jsonresultado, '$' 
                                        COLUMNS (NESTED PATH '$.INDIVIDUAL[*]' 
                                        COLUMNS (name VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.name', 
                                                value VARCHAR(255) CHARACTER SET utf8mb4 PATH '$.value', 
                                                titulo VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.titulo',
                                                indice INT PATH '$.indice'))) amt ON amt.titulo = 'TIPO DE AMOSTRA'
                                    LEFT JOIN JSON_TABLE(r.jsonresultado, '$' 
                                        COLUMNS (NESTED PATH '$.INDIVIDUAL[*]' 
                                        COLUMNS (name VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.name', 
                                                value VARCHAR(255) CHARACTER SET utf8mb4 PATH '$.value', 
                                                titulo VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.titulo',
                                                indice INT PATH '$.indice'))) res ON (res.titulo = 'RESULTADO' OR res.titulo = 'DETECÇÃO') AND res.indice = amt.indice
                                    LEFT JOIN JSON_TABLE(r.jsonresultado, '$' 
                                        COLUMNS (NESTED PATH '$.INDIVIDUAL[*]' 
                                        COLUMNS (name VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.name', 
                                                value VARCHAR(255) CHARACTER SET utf8mb4 PATH '$.value', 
                                                titulo VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.titulo',
                                                indice INT PATH '$.indice'))) obs ON obs.titulo = 'OBSERVAÇÃO' AND obs.indice = amt.indice
                                    LEFT JOIN JSON_TABLE(r.jsonresultado, '$' 
                                        COLUMNS (NESTED PATH '$.INDIVIDUAL[*]' 
                                        COLUMNS (name VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.name', 
                                                value VARCHAR(255) CHARACTER SET utf8mb4 PATH '$.value', 
                                                titulo VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.titulo',
                                                indice INT PATH '$.indice'))) s ON s.titulo = 'SEMENTE' AND s.indice = amt.indice
                                    LEFT JOIN JSON_TABLE(r.jsonresultado, '$' 
                                        COLUMNS (NESTED PATH '$.INDIVIDUAL[*]' 
                                        COLUMNS (name VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.name', 
                                                value VARCHAR(255) CHARACTER SET utf8mb4 PATH '$.value', 
                                                titulo VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.titulo',
                                                indice INT PATH '$.indice'))) tp ON tp.titulo = 'TIPIFICAÇÃO' AND tp.indice = amt.indice
                                    LEFT JOIN JSON_TABLE(r.jsonresultado, '$' 
                                        COLUMNS (NESTED PATH '$.INDIVIDUAL[*]' 
                                        COLUMNS (name VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.name', 
                                                value VARCHAR(255) CHARACTER SET utf8mb4 PATH '$.value', 
                                                titulo VARCHAR(45) CHARACTER SET utf8mb4 PATH '$.titulo',
                                                indice INT PATH '$.indice'))) ep ON ep.titulo = 'ESPÉCIE' AND ep.indice = amt.indice
                                    LEFT JOIN JSON_TABLE(r.jsonresultado, 
                                                '$.INDIVIDUAL[*]' 
                                            COLUMNS (
                                                name VARCHAR(255) PATH '$.name',
                                                titulo VARCHAR(255) PATH '$.titulo',
                                                valor VARCHAR(255) PATH '$.value',
                                                indice INT PATH '$.indice')) j1 ON j1.indice = amt.indice AND j1.titulo NOT IN('RESULTADO', 'SEMENTE', 'TIPIFICAÇÃO', 'ESPÉCIE', 'DETECÇÃO', 'OBSERVAÇÃO')
                    JOIN amostra a ON a.idamostra = r.idamostra
                    JOIN pessoa p ON p.idpessoa = a.idpessoa
                    JOIN subtipoamostra sta ON sta.idsubtipoamostra = a.idsubtipoamostra
                    LEFT JOIN vwespeciefinalidade ve ON ve.idespeciefinalidade = a.idespeciefinalidade
                    JOIN prodserv pd ON pd.idprodserv = r.idtipoteste
                    JOIN fluxostatus f ON f.idfluxostatus = a.idfluxostatus
                    JOIN carbonnovo._status s ON s.idstatus = f.idstatus
                    WHERE r.idresultado = '".$rowBuscarTestes['idresultado']."'
                    GROUP BY amt.indice";
    
    $resDadosTestes = d::b()->query($sqlRel) or die("Erro ao buscar Dados para Relatório sql: ".mysqli_error(d::b())."<br><br><br>".$sqlRel);
    $qtdTestes = mysqli_num_rows($resDadosTestes);

    while($rowDadosTeste = mysqli_fetch_assoc($resDadosTestes)){
        // Verificar se já existe o registro
        $checkSql = "SELECT COUNT(*) as count FROM relatorioldatra WHERE idprkey = '{$rowDadosTeste['idprkey']}'";
        $checkResult = d::b()->query($checkSql) or die("Erro ao buscar Contador Testes sql: ".mysqli_error(d::b())."<br><br><br>".$checkSql);
        $checkRow = mysqli_fetch_assoc($checkResult);

        $result = explode("/", $rowDadosTeste['dadosresult']);
        $dadosresult = ($result[1] == "" && $result['tituloresultado'] != 'RESULTADO') ? "" : $rowDadosTeste['dadosresult'];
        
        $idpessoaresponsavelof = empty($rowDadosTeste['idpessoaresponsavelof']) ? 'NULL' : "'".$rowDadosTeste['idpessoaresponsavelof']."'";
        $indice = empty($rowDadosTeste['indice']) ? 0 : "'".$rowDadosTeste['indice']."'";

        if($checkRow['count'] > 0 && !empty($rowDadosTeste['idprkey'])){

            array_push($testesDeletadosArray, $rowDadosTeste['idprkey']);
            
            $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                            VALUES ('2', '{$rowDadosTeste['idresultado']}-{$rowDadosTeste['exercicio']}', 'cron', 'atualizarelatoriolda', 'ids', '".$rowDadosTeste['idprkey']."', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
            d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
            
            $update = "UPDATE relatorioldatra 
                        SET idpessoaresponsavelof = $idpessoaresponsavelof,
                            responsavelof = '".addslashes($rowDadosTeste['responsavelof'])."',
                            descricao = '".addslashes($rowDadosTeste['descricao'])."',
                            idpessoa = '{$rowDadosTeste['idpessoa']}',
                            nome = '".addslashes($rowDadosTeste['nome'])."',
                            descr = '{$rowDadosTeste['descr']}',
                            tipoteste = '{$rowDadosTeste['tipoteste']}',
                            laboratorio = '{$rowDadosTeste['laboratorio']}',
                            tipoespecie = '{$rowDadosTeste['tipoespecie']}',
                            especie = '{$rowDadosTeste['especie']}',
                            finalidade = '{$rowDadosTeste['finalidade']}',
                            especietipofinalidade = '{$rowDadosTeste['especietipofinalidade']}',
                            tipoespeciefinalidade = '{$rowDadosTeste['tipoespeciefinalidade']}',
                            idplantel = '{$rowDadosTeste['idplantel']}',
                            idresultado = '{$rowDadosTeste['idresultado']}',
                            status = '{$rowDadosTeste['status']}',
                            nomesemente = '{$rowDadosTeste['nomesemente']}', 
                            valorsemente = '{$rowDadosTeste['valorsemente']}',
                            titulosemente = '{$rowDadosTeste['titulosemente']}',
                            nomeresultado = '{$rowDadosTeste['nomeresultado']}', 
                            valorresultado = '{$rowDadosTeste['valorresultado']}',
                            tituloresultado = '{$rowDadosTeste['tituloresultado']}',
                            nomeobs = '{$rowDadosTeste['nomeobs']}', 
                            valorobs = '".str_replace(['<p>', '</p>', '<br>', '<br/>', '<br />', '<br data-mce-bogus=&quot;1&quot;>'], '', $rowDadosTeste['valorobs'])."',
                            tituloobs = '{$rowDadosTeste['tituloobs']}',
                            nomeamostra = '".addslashes($rowDadosTeste['nomeamostra'])."', 
                            valoramostra = '".addslashes($rowDadosTeste['valoramostra'])."',
                            tituloamostra = '{$rowDadosTeste['tituloamostra']}',
                            indice = $indice, 
                            nometipificacao = '{$rowDadosTeste['nometipificacao']}', 
                            valortipificacao = '{$rowDadosTeste['valortipificacao']}',
                            titulotipificacao = '{$rowDadosTeste['titulotipificacao']}',
                            nomeespecie = '{$rowDadosTeste['nomeespecie']}', 
                            valorespecie = '{$rowDadosTeste['valorespecie']}',
                            tituloespecie = '{$rowDadosTeste['tituloespecie']}',
                            datafechado = '{$rowDadosTeste['datafechado']}', 
                            dadosresult = '".addslashes($dadosresult)."',
                            name = '{$rowDadosTeste['name']}',
                            titulo = '{$rowDadosTeste['titulo']}',
                            valor = '".addslashes($rowDadosTeste['valor'])."',
                            alteradoem = '{$rowDadosTeste['alteradoem']}',
                            alteradopor = '{$rowDadosTeste['alteradopor']}'
                        WHERE idprkey = '{$rowDadosTeste['idprkey']}'";
            
            d::b()->query($update) or ("Erro ao atualizar. sql: ".mysqli_error(d::b())."<br><br><br>".$update);
            if ($debug && !empty(mysqli_error(d::b()))) {
                echo "Update: ".$update."<br>";
            }

            if ($debug && !empty(mysqli_error(d::b()))) {
                $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                                    VALUES ('2', '{$rowDadosTeste['idresultado']}', 'cron', 'atualizarelatoriolda', 'erroatualizar', '".addslashes(mysqli_error(d::b()))."', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
                d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

                die();
            }

        } elseif($qtdTestes > 0) {
            array_push($testesDeletadosArray, $rowDadosTeste['idprkey']);

            $insertDados = "INSERT INTO relatorioldatra (idprkey,
                                                        tra,
                                                        idregistro,
                                                        dataamostra,
                                                        idpessoaresponsavelof,
                                                        responsavelof,
                                                        descricao,
                                                        idamostra,
                                                        idpessoa,
                                                        nome,
                                                        idprodserv,
                                                        descr,
                                                        tipoteste,
                                                        laboratorio,
                                                        tipoespecie,
                                                        especie,
                                                        finalidade,
                                                        especietipofinalidade,
                                                        tipoespeciefinalidade,
                                                        idplantel,
                                                        idresultado,
                                                        datateste,
                                                        status,
                                                        nomesemente, 
                                                        valorsemente,
                                                        titulosemente,
                                                        nomeresultado, 
                                                        valorresultado,
                                                        tituloresultado,
                                                        nomeobs, 
                                                        valorobs,
                                                        tituloobs,
                                                        nomeamostra, 
                                                        valoramostra,
                                                        tituloamostra,
                                                        indice, 
                                                        nometipificacao, 
                                                        valortipificacao,
                                                        titulotipificacao,
                                                        nomeespecie, 
                                                        valorespecie,
                                                        tituloespecie,
                                                        idempresa,
                                                        datafechado, 
                                                        dadosresult,
                                                        name,
                                                        titulo,
                                                        valor,
                                                        criadoem,
                                                        criadopor,
                                                        alteradoem,
                                                        alteradopor) 
                                                VALUES ('{$rowDadosTeste['idprkey']}',
                                                        '{$rowDadosTeste['tra']}',
                                                        '{$rowDadosTeste['idregistro']}',
                                                        '{$rowDadosTeste['dataamostra']}',
                                                        $idpessoaresponsavelof,
                                                        '".addslashes($rowDadosTeste['responsavelof'])."',
                                                        '".addslashes($rowDadosTeste['descricao'])."',
                                                        '{$rowDadosTeste['idamostra']}',
                                                        '{$rowDadosTeste['idpessoa']}',
                                                        '".addslashes($rowDadosTeste['nome'])."',
                                                        '{$rowDadosTeste['idprodserv']}',
                                                        '{$rowDadosTeste['descr']}',
                                                        '{$rowDadosTeste['tipoteste']}',
                                                        '{$rowDadosTeste['laboratorio']}',
                                                        '{$rowDadosTeste['tipoespecie']}',
                                                        '{$rowDadosTeste['especie']}',
                                                        '{$rowDadosTeste['finalidade']}',
                                                        '{$rowDadosTeste['especietipofinalidade']}',
                                                        '{$rowDadosTeste['tipoespeciefinalidade']}',
                                                        '{$rowDadosTeste['idplantel']}',
                                                        '{$rowDadosTeste['idresultado']}',
                                                        '{$rowDadosTeste['datateste']}',
                                                        '{$rowDadosTeste['status']}',
                                                        '{$rowDadosTeste['nomesemente']}', 
                                                        '{$rowDadosTeste['valorsemente']}',
                                                        '{$rowDadosTeste['titulosemente']}',
                                                        '{$rowDadosTeste['nomeresultado']}', 
                                                        '{$rowDadosTeste['valorresultado']}',
                                                        '{$rowDadosTeste['tituloresultado']}',
                                                        '{$rowDadosTeste['nomeobs']}',
                                                        '".str_replace(['<p>', '</p>', '<br>', '<br/>', '<br />', '<br data-mce-bogus=&quot;1&quot;>'], '', $rowDadosTeste['valorobs'])."',
                                                        '{$rowDadosTeste['tituloobs']}',
                                                        '".addslashes($rowDadosTeste['nomeamostra'])."', 
                                                        '".addslashes($rowDadosTeste['valoramostra'])."',
                                                        '{$rowDadosTeste['tituloamostra']}',
                                                        $indice, 
                                                        '{$rowDadosTeste['nometipificacao']}', 
                                                        '{$rowDadosTeste['valortipificacao']}',
                                                        '{$rowDadosTeste['titulotipificacao']}',
                                                        '{$rowDadosTeste['nomeespecie']}', 
                                                        '{$rowDadosTeste['valorespecie']}',
                                                        '{$rowDadosTeste['tituloespecie']}',
                                                        '{$rowDadosTeste['idempresa']}',
                                                        '{$rowDadosTeste['datafechado']}', 
                                                        '".addslashes($dadosresult)."',
                                                        '{$rowDadosTeste['name']}',
                                                        '{$rowDadosTeste['titulo']}',
                                                        '".addslashes($rowDadosTeste['valor'])."',
                                                        '{$rowDadosTeste['criadoem']}',
                                                        '{$rowDadosTeste['criadopor']}',
                                                        '{$rowDadosTeste['alteradoem']}',
                                                        '{$rowDadosTeste['alteradopor']}')";
            d::b()->query($insertDados) or ("Erro ao Inserir Dados. sql: ".mysqli_error(d::b())."<br><br><br>".$insertDados);
            if ($debug  && !empty(mysqli_error(d::b()))) {
                echo "Update: ".$insertDados."<br>";
            }

            if ($debug && !empty(mysqli_error(d::b()))) {
                $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                                    VALUES ('2', '{$rowDadosTeste['idresultado']}', 'cron', 'atualizarelatoriolda', 'erroatualizar', '".addslashes(mysqli_error(d::b()))."', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
                d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

                die();
            }
        }
    }

    if($qtdTestes > 0){ 
        $testesDeletados = implode(", ", $testesDeletadosArray);
        //Caso tenha deletado alguma linha do teste. Será excluído tb na Tabela temporária
        $sqlRelExcluir = "SELECT idresultado, idrelatorioldatra, idprkey
                            FROM relatorioldatra
                            WHERE idresultado = '".$rowBuscarTestes['idresultado']."'
                            AND idprkey NOT IN ($testesDeletados)";

        $resRelExcluir = d::b()->query($sqlRelExcluir) or ("Erro ao buscar Testes para excluir sql: ".mysqli_error(d::b())."<br><br><br>".$sqlRelExcluir);
        
        if ($debug && !empty(mysqli_error(d::b()))) {
            $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                                VALUES ('2', '{$rowDadosTeste['idresultado']}', 'cron', 'atualizarelatoriolda', 'buscarTestesApagar', '".addslashes(mysqli_error(d::b()))."', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
            d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

            die();
        }

        while ($rowRelExcluir = mysqli_fetch_assoc($resRelExcluir)) {
            $sqlDeletar = "DELETE FROM laudo.relatorioldatra WHERE idprkey = '".$rowRelExcluir['idprkey']."';";
            d::b()->query($sqlDeletar) or ("Erro ao deletar item. sql: ".mysqli_error(d::b())."<br><br><br>".$sqlDeletar);
            if ($debug) {
                echo "Update: ".$sqlDeletar."<br>";
            }

            if ($debug && !empty(mysqli_error(d::b()))) {
                $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                                    VALUES ('2', '{$rowRelExcluir['idprkey']}', 'cron', 'atualizarelatoriolda', 'erroDeletar', '".addslashes(mysqli_error(d::b()))."', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
                d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

                die();
            }
        }
    }
    
    $testesDeletadosArray = [];
}

$sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                VALUES ('2', 'hora', 'cron', 'atualizarelatoriolda', 'HoraFinal', '".date('Y-m-d H:i:s')."', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

echo 'FIM: '.date('Y-m-d H:i:s');

echo '</pre>';
?>