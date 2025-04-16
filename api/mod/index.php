<?
require_once "../../inc/php/functions.php";
// GVT - 13/01/2022
if(validaToken()["sucesso"]===true){
    $_GET['_idempresa'] = $_headers["_idempresa"];
    if($_headers["cb-canal"]=="app"){
        echo json_encode(modulosMobile());
        $string = "cb-idpessoa: ".$_headers['cb-idpessoa']." - Módulos: ".json_encode(modulosMobile());
        $sqli = "INSERT INTO laudo.log (idempresa, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                                VALUES ('".$_headers['_idempresa']."', 'app', 'app-mode', 'validaToken - sucesso', '$string', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'));";
        d::b()->query($sqli);
    }else{
        echo json_encode(getModsUsr("SQLWHEREMOD"));
    }
}else{
    header("HTTP/1.0 401 Não autorizado");

    $string = "cb-idpessoa: ".$_GET['cb-idpessoa'];
    $sqli = "INSERT INTO laudo.log (idempresa, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
                            VALUES ('".$_GET['_idempresa']."', 'app', 'app-mode', 'HTTP - Não autorizado 401', '$string', 'SUCESSO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'));";
    d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
}