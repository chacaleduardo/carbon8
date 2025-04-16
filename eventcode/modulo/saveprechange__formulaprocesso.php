<?
$iu = $_SESSION['arrpostbuffer']['x']['i']['prodservformula']['idprodserv'] ? 'i' : 'u';

if($iu=='i' and !empty($_SESSION['arrpostbuffer']['x']['i']['prodservformula']['idprodserv'])){
    //buscar unidade de almoxarifado
    $unidadeAlmoxarifado = FormulaProcessoController::buscarUnidadePorIdtipoIdempresa(3, cb::idempresa());
    $unidade = FormulaProcessoController::buscarUnidadePorIdtipoIdempresa(5, cb::idempresa());
    
    if(empty($unidadeAlmoxarifado['idunidade'])){ die('Não configurada unidade de almoxarifado para a empresa.');}
    $_SESSION['arrpostbuffer']['x']['i']['prodservformula']['idunidadeest'] = $unidadeAlmoxarifado['idunidade'];
    $_SESSION['arrpostbuffer']['x']['i']['prodservformula']['idunidadealerta'] = $unidade['idunidade'];
}
