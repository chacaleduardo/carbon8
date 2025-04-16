<?
if(!empty($_POST['idunidadeobjeto']) && !empty($_POST['_ajax_d_objempresa_idobjempresa']))
{
    _moduloController::deletaUnidadeObjeto($_POST['idunidadeobjeto']);
}
?>