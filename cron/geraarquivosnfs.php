<?
require_once("/var/www/carbon8/inc/php/functions.php");

//require_once("../inc/php/functions.php");

$grupo = rstr(8);

// Início log de execução no Redis e MySql
re::dis()->hMSet('cron:geraarquivosnfs',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'geraarquivosnfs', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

// Consulta inicial
$sql = "SELECT n.idnotafiscal as id, n.nnfe, n.idempresa, n.enviadetalhenfe, n.enviadanfnfe, n.emailboleto, pf.decsimplesn
            FROM notafiscal n 
                JOIN pessoa p ON (n.idpessoa = p.idpessoa)
                LEFT JOIN preferencia pf ON (pf.idpreferencia=p.idpreferencia)
            WHERE (n.enviadetalhenfe = 'G' OR n.enviadanfnfe = 'G' OR n.emailboleto='G')
            AND n.enviaemailnfe != 'O'
            AND n.status = 'CONCLUIDO'";

$res = d::b()->query($sql) or die("Erro ao recuperar NFS para gerar arquivos");


while($r = mysqli_fetch_assoc($res)){
    // array de respostas das funções p/ teste posterior
    $responses = [];

    // Gerar arquivo de DETALHAMENTO
    // retorna true ou false
    if($r["enviadetalhenfe"] == 'G'){
        array_push($responses, gerarDetalhamento($r["id"], $r["nnfe"], $r["idempresa"], $grupo));
    }

    // Gerar arquivo de DANFE
    // retorna true ou false
    if($r["enviadanfnfe"] == 'G'){
        array_push($responses, gerarDanfe($r["id"], $r["idempresa"], $grupo));
    }

    // Gerar arquivo de BOLETO
    // retorna true ou false
    if($r["emailboleto"] == 'G'){
        array_push($responses, gerarBoleto($r["id"], $r["idempresa"], $grupo));
    }

    // Testa o array, caso nenhuma função retorne false e o array não esteja vazio, alterar status de envio de e-mail
    if(!empty($responses) and !in_array(false, $responses)){
        $simplesnac = ($r["decsimplesn"] == "Y") ? " and emaildsimplesnac = 'Y'" : "";

        $upd = "UPDATE notafiscal SET enviaemailnfe = 'Y' WHERE enviaemailnfe != 'O' ".$simplesnac." and idnotafiscal = ".$r["id"];
        d::b()->query($upd);
    }else{
        $simplesnac = ($r["decsimplesn"] == "Y") ? " and emaildsimplesnac = 'Y'" : "";

        $upd = "UPDATE notafiscal SET enviaemailnfe = 'N' WHERE enviaemailnfe != 'O' ".$simplesnac." and idnotafiscal = ".$r["id"];
        d::b()->query($upd);
    }

    unset($responses);
}

// Início log de execução no Redis e MySql
re::dis()->hMSet('cron:geraarquivosnfs',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'geraarquivosnfs', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";


d::b()->query($sqli);
// Gera PDF de detalhamento
// retorna TRUE caso sucesso
// retorna FALSE caso erro
function gerarDetalhamento ($inId, $nnfe, $idempresa, $grupo) {

    // Monta parâmetros que serão enviados por GET
    $content = http_build_query(array(
        'nnfe' => $nnfe,
        'geraarquivo' => 'Y',
        'gravaarquivo' => 'Y',
        '_timbradoidempresa' => $idempresa
    ));

    // Cria a requisição com o método e conteúdo
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'GET',
            'content' => $content,
        ),
    ));

    // Envia a requisição para o arquivo de geração de detalhamento com os parâmetros necessários
    //$result = file_get_contents('http://localhost/carbon8/report/reldetalhenf.php?'.$content, FILE_TEXT, $context);
    $result = file_get_contents('https://sislaudo.laudolab.com.br/report/reldetalhenf.php?'.$content, false, $context);

    if($result != "OK"){
        $q1 = "UPDATE notafiscal SET enviadetalhenfe = 'E' WHERE idnotafiscal = ".$inId;
        d::b()->query($q1);

        $error = error_get_last();
        $q2 = "INSERT INTO log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, info, criadoem, data) 
                VALUES (".$idempresa.",'".$grupo."','notafiscal',".$inId.", 'geraarquivosnfs','".json_encode($result)." \n ".json_encode($error)."', 'Erro ao gerar arquivo Detalhamento', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($q2);
        return false;
    }else{
        $q1 = "UPDATE notafiscal SET enviadetalhenfe = 'Y' WHERE idnotafiscal = ".$inId;
        d::b()->query($q1);
        return true;
    }
    
}

// Gera PDF de danfe
// retorna TRUE caso sucesso
// retorna FALSE caso erro
function gerarDanfe ($inId, $idempresa, $grupo) {

    // Monta parâmetros que serão enviados por GET
    $content = http_build_query(array(
        'idnotafiscal' => $inId,
        'gravaarquivo' => 'Y',
        '_idempresa'   => $idempresa
    ));

    // Cria a requisição com o método e conteúdo
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'GET',
            'content' => $content,
        ),
    ));

    // Envia a requisição para o arquivo de geração de danfe com os parâmetros necessários
    //$result = file_get_contents('http://localhost/carbon8/form/geradanfse.php?'.$content, FILE_TEXT, $context);
    $result = file_get_contents('https://sislaudo.laudolab.com.br/form/geradanfse.php?'.$content, false, $context);

    if($result != "OK"){
        $q1 = "UPDATE notafiscal SET enviadanfnfe = 'E' WHERE idnotafiscal = ".$inId;
        d::b()->query($q1);
        
        $error = error_get_last();
        $q2 = "INSERT INTO log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, info, criadoem, data) 
                    VALUES (".$idempresa.",'".$grupo."','notafiscal',".$inId.", 'geraarquivosnfs', '".json_encode($result)." \n ".json_encode($error)."', 'Erro ao gerar arquivo Danfe', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($q2);
        return false;
    }else{
        $q1 = "UPDATE notafiscal SET enviadanfnfe = 'Y' WHERE idnotafiscal = ".$inId;
        d::b()->query($q1);
        return true;
    }
}

// Gera PDF de cada boleto
// retorna TRUE caso sucesso ou caso não possua boletos
// retorna FALSE caso erro ao gerar um dos boletos
function gerarBoleto ($inId, $idempresa, $grupo) {

    // Busca dos boletos da notafiscal
    // Baseado nas consultas existentes em form/nfs.php
    // Primeira consulta de boletos
    $sqlx1 ="SELECT i.idcontapagar,a.boleto
                FROM contapagaritem i 
                JOIN formapagamento f on(f.idformapagamento = i.idformapagamento)
                join agencia a on(a.idagencia=f.idagencia)
                WHERE i.idobjetoorigem = ".$inId." and i.tipoobjetoorigem like 'notafiscal'";
    $resx1 = d::b()->query($sqlx1);
    $qtdx1 = mysqli_num_rows($resx1);

    if($qtdx1 == 0){

        // Caso a primeira consulta não houver resultados
        // Segunda consulta de boletos
        $sqlx2 = "SELECT c.idcontapagar,a.boleto
                    FROM contapagar c join agencia a on(a.idagencia=c.idagencia)
                    WHERE c.tipoobjeto = 'notafiscal' 
                        AND c.idobjeto = ".$inId;
        $resx2 = d::b()->query($sqlx2);
        $qtdx2= mysqli_num_rows($resx2);

        if($qtdx2 == 0){

            // Caso a segunda consulta não houver resultados
            // notafiscal não possui boletos
            $q1 = "UPDATE notafiscal SET emailboleto = 'N' WHERE idnotafiscal = ".$inId;
            d::b()->query($q1);
            return true;
            
        }else{
            $resx = $resx2;
        }
    }else{
        $resx = $resx1;
    }

    // Variável p/ controle de boletos gerados com sucesso
    $iOk = 0;
    $qtdx = 0;
    while($rx = mysqli_fetch_assoc($resx)){
        $sqlx3 = "SELECT 1 
                    FROM notafiscal n 
                        JOIN formapagamento f ON (n.idformapagamento = f.idformapagamento)
                    WHERE f.formapagamento = 'BOLETO'
                        AND n.idnotafiscal = ".$inId;
        $resx3 = d::b()->query($sqlx3) or die("Erro ao buscar boleto sql=".$sqlx3);
        $qtdx3 = mysqli_num_rows($resx3);
        $qtdx += $qtdx3;
        if($qtdx3 == 1){

            // Monta parâmetros que serão enviados por GET
            $content = http_build_query(array(
                'idcontapagar' => $rx['idcontapagar'],
                'geraarquivo' => 'Y',
                'gravaarquivo' => 'Y',
                '_idempresa'   => $idempresa
            ));

            // Cria a requisição com o método e conteúdo
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'content' => $content,
                ),
            ));

            // Envia a requisição para o arquivo de geração de boleto com os parâmetros necessários
            //$result = file_get_contents('http://localhost/carbon8/inc/boletophp/boleto_itau.php?'.$content, FILE_TEXT, $context);
            $result = file_get_contents('https://sislaudo.laudolab.com.br/inc/boletophp/'.$rx['boleto'].'.php?'.$content, false, $context);
            
            if($result != "OK"){
                $q1 = "UPDATE contapagar SET boletopdf = 'N' WHERE idcontapagar = ".$rx['idcontapagar'];
                d::b()->query($q1);
                
                $error = error_get_last();
                $q2 = "INSERT INTO log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, info, criadoem, data) 
                        VALUES (".$idempresa.",'".$grupo."','notafiscal',".$inId.", 'geraarquivosnfs', '".json_encode($result)." \n ".json_encode($error)."', 'Erro ao gerar arquivo Boleto. Idcontapagar: ".$rx['idcontapagar']."', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
                d::b()->query($q2);
            }else{
                $iOk++;
                $q1 = "UPDATE contapagar SET boletopdf = 'Y' WHERE idcontapagar = ".$rx['idcontapagar'];
                d::b()->query($q1);
            }
        }
    }

    // Verifica se o número de boletos encontrados é o mesmo número de boletos gerados com sucesso
    if($iOk != $qtdx){
        $q1 = "UPDATE notafiscal SET emailboleto = 'E' WHERE idnotafiscal = ".$inId;
        d::b()->query($q1);
        return false;
    }else{
        $q1 = "UPDATE notafiscal SET emailboleto = 'Y' WHERE idnotafiscal = ".$inId;
        d::b()->query($q1);
        return true;
    }   
}
?>