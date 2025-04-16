<?
require(__DIR__."/../../form/controllers/emailvirtualconf_controller.php");

if(!empty($_SESSION["arrpostbuffer"]["1"]["u"]["emailvirtualconf"]["idemailvirtualconf"])){
    EmailVirtualConfController::atualizarEmailsDeDestinoPorIdEmailVirtualConf($_SESSION["arrpostbuffer"]["1"]["u"]["emailvirtualconf"]["idemailvirtualconf"]);
}
?>