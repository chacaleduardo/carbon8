<?
// CONTROLLERS
//marcelocunha - 21/02/23 - não irá mais atualizar os rateios de unidades inativadas
/*
require_once(__DIR__."/./../../form/controllers/unidade_controller.php");

if(strtoupper($_POST['_1_u_unidade_status']) == 'INATIVO')
{
    UnidadeController::atualizarUnidadeNoRateioItemDest($_POST['_1_u_unidade_idunidade']);
}
*/

if(($_POST['statusant'] != $_POST['_1_u_unidade_status']) && $_POST['_1_u_unidade_status'] == 'INATIVO' && $_POST['bloqueiainativar'] == 'Y'){
    die("Não é possível inativar esta unidade, há tags vinculados a ela! Favor contatar o setor da garantia de qualidade para remaneja-las.");
}

?>