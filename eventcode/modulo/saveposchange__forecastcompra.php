<?

$idempresa = $_SESSION["arrpostbuffer"]["1"]['u']["forecastcompra"]["idempresa"];
$exercicio = $_SESSION["arrpostbuffer"]["1"]['u']["forecastcompra"]["exercicio"];
$idforecastcompra = $_SESSION["arrpostbuffer"]["1"]['u']["forecastcompra"]["idforecastcompra"]; 

if($_POST['_1_i_forecastcompra_idempresa']){
    $controleVersao = 0;
}else{
    $controleVersao = $_POST['_1_u_forecastcompra_versao'];
}

if ($_POST['_statusant_'] != 'APROVADO' AND $_SESSION["arrpostbuffer"]["1"]['u']["forecastcompra"]["status"] == 'APROVADO')
{
    $forecastcomprasql = PlanejamentoProdServController::buscaCategoria($idempresa, '', $exercicio); 
          
    $arrayObjetoJson = [
        "idempresa" => $_SESSION['SESSAO']['IDEMPRESA'],
        "idobjeto" => $idforecastcompra,
        "tipoobjeto" => 'forecastcompra',
        "jobjeto" => base64_encode(serialize($forecastcomprasql)),
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
        "idobjeto" => $idforecastcompra,
        "coluna" => 'jobjeto',
        "valor" => base64_encode(serialize($forecastcomprasql)),
        "criadopor" => $_SESSION["SESSAO"]["USUARIO"],
        "tela" => $_SERVER["HTTP_REFERER"]
    ];
    ProdServController::inserirAuditoria($arrayAuditoria);
}
