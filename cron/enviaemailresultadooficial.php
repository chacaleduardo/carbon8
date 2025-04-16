<?
// IMPORTANTE:
//		- ESSE CÓGIDO ATÉ O MOMENTO É EXECUTADO A CADA 5 MINUTOS PELA CRON
//		- N?O ADICIONAR O VALIDAACESSO.PHP, POIS NESSE PONTO DO CÓDIGO N?O TEMOS UM USUÁRIO SETADO

// Incluído para utilizar as funções do banco de dados e do JWT
require_once("/var/www/carbon8/inc/php/functions.php");
error_reporting(E_ALL);
ini_set('display_errors',1);

$grupo = rstr(8);

re::dis()->hMSet('cron:enviaemailresultadooficial',['inicio' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailresultadooficial', 'status', 'INICIO', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);




// Recupera resultados que irão ser enviados
function getResultadosEnviar(){
	
	// Consulta resultados nos mesmos moldes da enviaemailoficial_migracao.php, com intervalo de 30 dias.
	$sql = "SELECT sb.tipores,
                sb.idpessoa,sb.idsecretaria,sb.idnucleo,sb.exercicio,sb.idempresa,DATE_SUB(NOW(), INTERVAL 30 DAY) as alterado_1,now() as alterado_2
            from 
            (
            select 'TODOS' AS tipores,
                a.idpessoa,s.idpessoa as idsecretaria,a.idnucleo,a.exercicio,a.idempresa
            from    
            (amostra a
            ,resultado r 
            ,pessoa p
            ,pessoa s
            )           
            where p.idpessoa = a.idpessoa 
				and a.idnucleo <> 0
                and s.idpessoa = r.idsecretaria
                and  not exists  (
					select 1 
					from comunicacaoext c
					join comunicacaoextitem i on (c.idcomunicacaoext = i.idcomunicacaoext)
					where c.tipo = 'EMAILOFICIAL'
					and c.status = 'SUCESSO'
					and i.tipoobjeto = 'resultado'
					and i.idobjeto = r.idresultado
					)  
                and r.status = 'ASSINADO'
                and r.idamostra = a.idamostra 
                and r.idsecretaria != ''
                and (r.alteradoem BETWEEN DATE_SUB(NOW(), INTERVAL 40 DAY) AND NOW()) 
            ) as sb
            
            group by sb.idnucleo,sb.idsecretaria  union all 
            select sb1.tipores, 
            sb1.idpessoa,sb1.idsecretaria,sb1.idnucleo,sb1.exercicio,sb1.idempresa,DATE_SUB(NOW(), INTERVAL 30 DAY) as alterado_1,now() as alterado_2
            from 
            (   
                select 'POS' as tipores,
                a.idpessoa,s.idpessoa as idsecretaria,a.idnucleo,a.exercicio,a.idempresa
                from
                (amostra a
                ,resultado r
                ,pessoa p
                ,pessoa s
                )
                where p.idpessoa = a.idpessoa
				and a.idnucleo <> 0
                and s.idpessoa = r.idsecretaria
				and  not exists  (
					select 1 
					from comunicacaoext c
					join comunicacaoextitem i on (c.idcomunicacaoext = i.idcomunicacaoext)
					where c.tipo = 'EMAILOFICIALPOS'
					and c.status = 'SUCESSO'
					and i.tipoobjeto = 'resultado'
					and i.idobjeto = r.idresultado
					)         
                and r.status = 'ASSINADO'
                and r.idamostra = a.idamostra             
                and r.alerta = 'Y'
                and r.idsecretaria != ''
                and (r.alteradoem BETWEEN DATE_SUB(NOW(), INTERVAL 40 DAY) AND NOW())
            ) as sb1
            
            group by sb1.idnucleo,sb1.idsecretaria";

    $res = d::b()->query($sql) or die("A Consulta do email do xml nfe falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");

    $arrtmp=array();
    $i=0;

    $num = mysqli_num_rows($res);
    if($num > 0){
        while($row = mysqli_fetch_array($res)){
			
            if($row["tipores"]=="POS"){
                $vAlerta = 'Y';
                $sqlalerta=" and r.alerta = 'Y' ";
                $sqlintipo="EMAILOFICIALPOS";
                $sqlconfemails="select email,p.receberes
                        from pessoa p,pessoacontato c
                        where p.status='ATIVO'
                        and p.receberes is not null and p.receberes !=''
                        and p.email is not null and p.email != ''
                        and p.idpessoa = c.idcontato
                        and c.idpessoa= ". $row["idsecretaria"];
            }else{
                $vAlerta = 'N';
                $sqlalerta=" ";
                $sqlintipo="EMAILOFICIAL";
                $sqlconfemails="select email,p.receberestodos	as receberes
                        from pessoa p,pessoacontato c
                        where p.status='ATIVO'
                        and p.receberestodos is not null and p.receberestodos !=''
                        and p.idpessoa = c.idcontato
                        and c.idpessoa= ". $row["idsecretaria"];
            }
    
            $resconfemails = d::b()->query($sqlconfemails) or die("A Consulta de contatos da Secretaria falhou. ");
    
            $sqlemail="";
            $virg="";
            while($rowconfemails = mysqli_fetch_assoc($resconfemails)){
                $sqlemail.=$virg.$rowconfemails['email'];
                $virg=",";
            }
    
            if(trim($sqlemail)!="oficial@laudolab.com.br"){
				
				// Consulta o domínio da empresa
				$sqldominio = "SELECT idemailvirtualconf 
                                FROM empresaemails
                                WHERE tipoenvio = 'RESULTADOOFICIAL'
                                AND idempresa = {$row["idempresa"]};";
				$resdominio = d::b()->query($sqldominio) or die("ERRO: Consulta do email para RESULTADO OFICIAL falhou" . mysqli_error(d::b()) . "<p>SQL: $sqldominio");
				$rowdominio = mysqli_fetch_array($resdominio);
				$vIdemailvirtualconf = $rowdominio["idemailvirtualconf"];
				
				
                $arrtmp[$i]["idnucleo"]=$row["idnucleo"];
                $arrtmp[$i]["exercicio"]=$row["exercicio"];
                $arrtmp[$i]["idsecretaria"]=$row["idsecretaria"];
                $arrtmp[$i]["idpessoa"]=$row["idpessoa"];
                $arrtmp[$i]["idempresa"]=$row["idempresa"];
                $arrtmp[$i]["alterado_1"]=$row["alterado_1"];
                $arrtmp[$i]["alterado_2"]=$row["alterado_2"];
                $arrtmp[$i]["idemailvirtualconf"]=$vIdemailvirtualconf;
				$arrtmp[$i]["alerta"]=$vAlerta;
                $i++;
            }
        }
        return $arrtmp;
    }else{
        return 0;
    }
}

// Gera Token JWT para autenticação na validaacesso.php
/*
// JWT para autenticar o usuário no validaacesso.php
$token_ = array(
	"iss" => "sislaudo",
//	"exp" => time() + (7 * 24 * 60 * 60),
	"idlp" => "9",
	"idtipopessoa" => "1",
	"idpessoa" => "2266",
	"usuario" => 'josesousa',
	"idempresa" => "1"
);
$jwt_ = JWT::encode($token_, _JWTKEY, 'HS256');
die($jwt_);
*/

// JWT com as informações do usuário que fazia o envio dos emails oficiais. Esse JWT n?o expira.
$jwt_ = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzaXNsYXVkbyIsImlkbHAiOiI5IiwiaWR0aXBvcGVzc29hIjoiMSIsImlkcGVzc29hIjoiMjI2NiIsInVzdWFyaW8iOiJqb3Nlc291c2EiLCJpZGVtcHJlc2EiOiIxIn0.csC1v_813FLCMtGJKv8iypyYfP_aKzbdMoKovK35vmU";

// Retorna registros que devem ser enviados
$aRes = getResultadosEnviar();
if($_GET['insparray'] == 'Y'){
    print_r($aRes);
    echo '<br><br><br>';
    foreach ($aRes as $name => $value) {

    // Monta parâmetros que ser?o enviados por GET
    $content = http_build_query(array(
        'idnucleo' => $value["idnucleo"],
        'exercicio' => $value["exercicio"],
        'idsecretaria' => $value["idsecretaria"],
        'idpessoa' => $value["idpessoa"],
        'idempresa' => $value["idempresa"],
        'alerta' => $value["alerta"],
        'alterado_1' => $value["alterado_1"],
        'alterado_2' => $value["alterado_2"],
        'idemailvirtualconf' => $value["idemailvirtualconf"],
        '_modulo' => 'resultaves'
    ));

    // Cria a requisição com o método, conteúdo e seta um header com o JWT que será recuperado na validaacesso.php
    $context = stream_context_create(array(
        'http' => array(
        'method' => 'GET',
        'header'  => 'jwt: '.$jwt_,
        'content' => $content,
        ),
    ));
    echo 'https://sislaudo.laudolab.com.br/report/enviaemailoficial_emissaogerapdf.php?'.$content;
    echo '<br>';
    // Alterar para enviar um teste de requisição, caso positivo, será inserido na tabela LOG
    $result = file_get_contents('https://sislaudo.laudolab.com.br/ajax/testerequisicao.php?'.$content, false, $context);

    // Envia a requisição para o arquivo de envio de email oficial com os parâmetros necessários
    //$result = file_get_contents('https://sislaudo.laudolab.com.br/report/enviaemailoficial_emissaogerapdf.php?'.$content, null, $context);
    print_r($result);

    }

}else{
    if($aRes != 0){

        foreach ($aRes as $name => $value) {

                // Monta parâmetros que ser?o enviados por GET
                $content = http_build_query(array(
                    'idnucleo' => $value["idnucleo"],
                    'exercicio' => $value["exercicio"],
                    'idsecretaria' => $value["idsecretaria"],
                    'idpessoa' => $value["idpessoa"],
                    'idempresa' => $value["idempresa"],
                    'alerta' => $value["alerta"],
                    'alterado_1' => $value["alterado_1"],
                    'alterado_2' => $value["alterado_2"],
                    'idemailvirtualconf' => $value["idemailvirtualconf"],
                    '_modulo' => 'resultaves'
                ));
                
                // Cria a requisição com o método, conteúdo e seta um header com o JWT que será recuperado na validaacesso.php
                $context = stream_context_create(array(
                    'http' => array(
                    'method' => 'GET',
                    'header'  => 'jwt: '.$jwt_,
                    'content' => $content,
                    ),
                ));
                //echo 'https://sislaudo.laudolab.com.br/report/enviaemailoficial_emissaogerapdf.php?'.$content;die;
                // Alterar para enviar um teste de requisição, caso positivo, será inserido na tabela LOG
                //$result = file_get_contents('https://sislaudo.laudolab.com.br/ajax/testerequisicao.php?'.$content, null, $context);
                
                // Envia a requisição para o arquivo de envio de email oficial com os parâmetros necessários
                try {
                    $result = file_get_contents('https://sislaudo.laudolab.com.br/report/enviaemailoficial_emissaogerapdf.php?'.$content, false, $context);
                } catch (\Throwable $th) {
                    throw $th;
                }
                
                
                print_r($result);
        }
    }else{
        echo "Não há resultados para serem enviados !!!";
        die();
    }
}


re::dis()->hMSet('cron:enviaemailresultadooficial',['fim' => Date('d/m/Y H:i:s')]);

$sqli = "INSERT INTO `laudo`.`log` (`idempresa`, `sessao`, `tipoobjeto`, `idobjeto`, `tipolog`, `log`, `status`, `criadoem`, `data`) 
							VALUES ('1', '".$grupo ."','cron', 'enviaemailresultadooficial', 'status', 'FIM', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";

d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);


?>
