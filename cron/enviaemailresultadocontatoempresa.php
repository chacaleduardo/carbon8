<?
session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
    include_once("/var/www/carbon8/inc/php/functions.php");
    include_once("/var/www/carbon8/inc/php/composer/vendor/autoload.php");
}else{//se estiver sendo executado via requisicao http
    include_once("../inc/php/functions.php");
    include_once("../inc/php/composer/vendor/autoload.php");
}
$grupo = rstr(8);

re::dis()->hMSet('cron:enviaemailresultadocontatoempresa',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailresultadocontatoempresa', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

/* ########################################################################################################################################## */
/*          #######   ###   ###   #######   #######   #######   #######   #######   ###   ##   #######   #######
               #      ## ### ##   ##   ##   ##   ##   ##   ##      #      ##   ##   ####  ##      #      ##
               #      ##  #  ##   #######   ##   ##   #######      #      #######   ## ## ##      #      ####
               #      ##     ##   ##        ##   ##   ##  ##       #      ##   ##   ##  ####      #      ##
            #######   ##     ##   ##        #######   ##   ##      #      ##   ##   ##   ###      #      #######
/* ########################################################################################################################################## */
/*
    URL's de exemplo:

        - inspecionar sem enviar email: 

            https://sislaudo.laudolab.com.br/cron/enviaemailresultadocontatoempresa.php?inspecionar=Y

        - gerar URL's de envio teste (altere o valor de _nurls_ para gerar quantas urls desejar e _destinatario_ para escolher o destinatario do email):

            https://sislaudo.laudolab.com.br/cron/enviaemailresultadocontatoempresa.php?_gerarurl_=Y&_nurls_=1&_destinatario_=seuemail@laudolab.com.br

*/

// SELECT inicial. Busca as pessoas e núcleos que "podem" ser enviados
function getResultadosEnviar(){
    $intervalo = (!empty($_GET["intervalo"])) ? $_GET["intervalo"] : "7";

    $alterado_1 = "DATE_SUB(NOW(), INTERVAL ".$intervalo." DAY)";
    $alterado_2 = "now()";
    
    $sql0 = "SELECT 
                'TODOS' AS tipores, p.idpessoa, a.idnucleo, a.exercicio, a.idempresa, ".$alterado_1." as alterado_1, ".$alterado_2." as alterado_2, r.idsecretaria
            FROM
                amostra a
                    JOIN
                pessoa p ON (a.idpessoa = p.idpessoa)
                    JOIN
                resultado r ON (a.idamostra = r.idamostra)
            WHERE
                r.status = 'ASSINADO'
                    AND a.idnucleo <> ''
                    AND a.idnucleo <> 0
                    AND (r.alteradoem BETWEEN ".$alterado_1." AND ".$alterado_2.")
            GROUP BY a.idnucleo 
            UNION ALL
            SELECT 
                'POS' AS tipores, p.idpessoa, a.idnucleo, a.exercicio , a.idempresa, ".$alterado_1." as alterado_1, ".$alterado_2." as alterado_2, r.idsecretaria
            FROM
                amostra a
                    JOIN
                pessoa p ON (a.idpessoa = p.idpessoa)
                    JOIN
                resultado r ON (a.idamostra = r.idamostra)
            WHERE
                r.alerta = 'Y' AND r.status = 'ASSINADO'
                    AND a.idnucleo <> ''
                    AND a.idnucleo <> 0
                    AND (r.alteradoem BETWEEN ".$alterado_1." AND ".$alterado_2.")
            GROUP BY a.idnucleo";

    
    $res0 = d::b()->query($sql0) or die("A Consulta dos núcleos e pessoas falhou: " . mysql_error() . "<p>SQL: $sql0");
    $num0 = mysql_num_rows($res0);
    if($_GET["inspecionar"] == 'Y') echo "<pre>".$sql0."</pre><hr><br>Número de resultados: ".$num0."<br><br>";
    if($num0 > 0){
        $i = 0;
        $arraytmp = array();

        $alterado_1 = date('Y-m-d', strtotime('-'.$intervalo.' day'));
        $alterado_2 = date('Y-m-d');

        while($row0 = mysql_fetch_assoc($res0)){
            $j = 0;

            if($row0["tipores"]=="POS"){
                $sql1="SELECT 
                        pc.idcontato, p.email, pc.receberes as tipo,p.usuario, pc.somenteoficial
                    FROM
                        pessoacontato pc
                            JOIN
                        pessoa p ON (pc.idcontato = p.idpessoa)
                    WHERE
                        pc.idpessoa = ".$row0["idpessoa"]." AND p.idtipopessoa = 3
                            AND p.email <> ''
                            AND pc.receberes <> ''
                            AND p.usuario <> ''
                            AND p.idempresa = ".$row0["idempresa"];
            }else{
                $sql1="SELECT 
                        pc.idcontato, p.email, pc.receberestodos as tipo,p.usuario, pc.somenteoficial
                    FROM
                        pessoacontato pc
                            JOIN
                        pessoa p ON (pc.idcontato = p.idpessoa)
                    WHERE
                        pc.idpessoa = ".$row0["idpessoa"]." AND p.idtipopessoa = 3
                            AND p.email <> ''
                            AND p.usuario <> ''
                            AND pc.receberestodos <> ''
                            AND p.idempresa = ".$row0["idempresa"];
            }

            $sqldominio = "SELECT email_original AS remetente 
                            FROM empresaemails e 
                            JOIN emailvirtualconf ev ON (e.idemailvirtualconf = ev.idemailvirtualconf) 
                            WHERE e.tipoenvio = 'EMAILCONTATOEMPRESA'
                            AND e.idempresa = {$row0["idempresa"]}
                            AND ev.status = 'ATIVO';";
            $resdominio = d::b()->query($sqldominio) or die("ERRO: Consulta do email para EMAIL CONTATO EMPRESA falhou" . mysqli_error(d::b()) . "<p>SQL: $sqldominio");
            $numdominio = mysql_num_rows($resdominio);
            $rowdominio = mysqli_fetch_array($resdominio);

            $res1 = d::b()->query($sql1) or die("A Consulta de contatos da Empresa falhou.");
            $num1 = mysql_num_rows($res1);
            if($num1 > 0 AND $numdominio > 0){

                while($row1 = mysqli_fetch_assoc($res1)){
                    if(($row1["somenteoficial"] == "Y" and !empty($row0["idsecretaria"])) or $row1["somenteoficial"] == "N"){
                        $arraytmp[$i]["destinatario"][$j]["idcontato"]  = $row1["idcontato"];
                        $arraytmp[$i]["destinatario"][$j]["email"]  = $row1["email"];
                        $arraytmp[$i]["destinatario"][$j]["usuario"]  = $row1["usuario"];
                        $arraytmp[$i]["destinatario"][$j]["tipo"]   = $row1["tipo"];
                        $j++;
                    }
                }

                if(!empty($arraytmp[$i]["destinatario"])){
                    $arraytmp[$i]["tipores"]        = $row0["tipores"];
                    $arraytmp[$i]["idpessoa"]       = $row0["idpessoa"];
                    $arraytmp[$i]["idnucleo"]       = $row0["idnucleo"];
                    $arraytmp[$i]["exercicio"]      = $row0["exercicio"];
                    $arraytmp[$i]["idempresa"]      = $row0["idempresa"];
                    $arraytmp[$i]["remetente"]      = $rowdominio["remetente"];
                    $arraytmp[$i]["alterado_1"]     = $alterado_1;
                    $arraytmp[$i]["alterado_2"]     = $alterado_2;
                    $arraytmp[$i]["_modulo"]        = "resultaves";
                    $i++;
                }
            
            }
        }
        if($_GET["inspecionar"] == 'Y') echo "Número de envios que serão feitos: ".$i."<br><br><hr>";
        // A partir desse momento o array está completo com todos os destinatários que receberão o email
        return $arraytmp;
    }else{
        return 0;
    }
}

$aRes = getResultadosEnviar();
//var_dump($aRes);die;

$jwt_ = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzaXNsYXVkbyIsImlkbHAiOiI5IiwiaWR0aXBvcGVzc29hIjoiMSIsImlkcGVzc29hIjoiMjI2NiIsInVzdWFyaW8iOiJqb3Nlc291c2EiLCJpZGVtcHJlc2EiOiIxIn0.csC1v_813FLCMtGJKv8iypyYfP_aKzbdMoKovK35vmU";

if($aRes != 0 AND count($aRes) != 0){
    $url = "https://sislaudo.laudolab.com.br/report/enviaemailresultado_contatoempresa.php?content=";
    //$url = "http://localhost/carbon8/report/enviaemailresultado_contatoempresa.php?content=";
    $j = 0;
    foreach ($aRes as $name => $value) {

        $content = $JSON->encode($value);
		
		// Cria a requisi�?o com o m�todo, conte�do e seta um header com o JWT que ser� recuperado na validaacesso.php

        if($_GET["_gerarurl_"] == 'Y' or $_GET["inspecionar"] == 'Y'){
            if(!empty($_GET["_nurls_"]) and $_GET["_nurls_"] == $j){
                die;
            }else{
                if(!empty($_GET["_destinatario_"])){
                    print_r("<br>".$url.$content."&_emailteste_=Y&_destinatario_=".$_GET["_destinatario_"]."<br>");
                }else{
                    print_r("<br>".$url.$content."&_emailteste_=Y<br>");
                }
            }
        }else{
            $context = stream_context_create(array(
                'http' => array(
                'method' => 'GET',
                'header'  => 'jwt: '.$jwt_,
                'content' => $content,
                ),
            ));
            $result = file_get_contents( $url.$content, null, $context );
            echo $result;
        }
        $j++;
	}
}else{
    echo "Não há resultados para serem enviados !!!";
	die();
}

re::dis()->hMSet('cron:enviaemailresultadocontatoempresa',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailresultadocontatoempresa', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


?>