<?php 

require_once(__DIR__."/../../inc/php/functions.php");
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

$idempresa = $_SESSION["arrpostbuffer"]["1"]['u']["forecastvenda"]["idempresa"];
$exercicio = $_SESSION["arrpostbuffer"]["1"]['u']["forecastvenda"]["exercicio"];
$idforecastvenda = $_SESSION["arrpostbuffer"]["1"]['u']["forecastvenda"]["idforecastvenda"]; 

if($_SESSION['arrpostbuffer']['1']['i']['forecastvenda']['exercicio']){

    $idforecastvenda = $_SESSION["_pkid"];

    $idfluxostatus = FluxoController::getIdFluxoStatus('forecastcompra', 'ABERTO');
    $idempresa = $_SESSION['arrpostbuffer']['1']['i']['forecastvenda']['idempresa'];
    $exercicio = $_SESSION['arrpostbuffer']['1']['i']['forecastvenda']['exercicio'];
    $usuario = $_SESSION['SESSAO']['USUARIO'];
    $criadoem = date('Y-m-d H:i:s');

    d::b()->query("insert into forecastcompra (idempresa,exercicio,idforecastvenda,criadopor, criadoem, status, idfluxostatus) 
                    VALUES (".$idempresa.",'".$exercicio."', '".$idforecastvenda."', '".$usuario."', '".$criadoem."', 'ABERTO', '".$idfluxostatus."');")
                    or die("Erro ao criar o forecast de compra");

}

if($_POST['_1_i_forecastvenda_idempresa']){
    $controleVersao = 0;
}else{
    $controleVersao = $_POST['_1_u_forecastvenda_versao'];
}

if ($_POST['_statusant_'] != 'APROVADO' AND $_SESSION["arrpostbuffer"]["1"]['u']["forecastvenda"]["status"] == 'APROVADO')


{
    [$produtos["naoplanejados"], $naoplanejados]   = ProdServController::buscaDadosProdutoForecast($idempresa, '' ,'' ,'' , $exercicio, 1);
    [$produtos["emplanejamento"], $emplanejamento] = ProdServController::buscaDadosProdutoForecast($idempresa, '', '', '', $exercicio, 2);
    [$produtos["planejados"], $planejados]         = ProdServController::buscaDadosProdutoForecast($idempresa, '', '', '', $exercicio, 3);

    $resultado = 
    array(
            "naoplanejados" => $produtos["naoplanejados"],
            "emplanejamento" => $produtos["emplanejamento"],
            "planejados" => $produtos["planejados"]
        );

    $arrayObjetoJson = [
        "idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
        "idobjeto" => $idforecastvenda,
        "tipoobjeto" => 'forecastvenda',
        "jobjeto" => base64_encode(serialize($resultado)),
        "versaoobjeto" => $controleVersao,
        "criadopor" => $_SESSION['SESSAO']['USUARIO'],
        "criadoem" => 'now()',
        "alteradopor" => $_SESSION["SESSAO"]["USUARIO"],
        "alteradoem" => 'now()'

        
    ];

    ProdServController::inserirObjetoJson($arrayObjetoJson); 

    $arrayAuditoria = [
        "idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
        "linha" => 1,
        "acao" => 'i',
        "objeto" => 'objetojson',
        "idobjeto" => $idforecastvenda,
        "coluna" => 'jobjeto',
        "valor" => base64_encode(serialize($resultado)),
        "criadopor" => $_SESSION["SESSAO"]["USUARIO"],
        "tela" => $_SERVER["HTTP_REFERER"]
    ];
    ProdServController::inserirAuditoria($arrayAuditoria);
}
