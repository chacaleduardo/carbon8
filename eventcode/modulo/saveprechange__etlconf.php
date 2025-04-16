<?
// CONTROLLERS
require_once(__DIR__."/../../form/controllers/etlconf_controller.php");

$idetlconffiltros=$_SESSION['arrpostbuffer']['ajax']['u']['etlconffiltros']['idetlconffiltros'];

if(!empty($idetlconffiltros)){
    $idetlconf=traduzid("etlconffiltros","idetlconffiltros","idetlconf",$idetlconffiltros);

    if(!empty($_SESSION['arrpostbuffer']['ajax']['u']['etlconffiltros']['tsum'])){
        EtlConfController::desabilitarTSumPorIdEtlConf($idetlconf);

    }elseif(!empty($_SESSION['arrpostbuffer']['ajax']['u']['etlconffiltros']['separador'])){
        EtlConfController::desabilitarSeparadorPorIdEtlConf($idetlconf);
    }
        

}

?>