<?

// CONTROLLERS
require_once(__DIR__."/../../form/controllers/deviceciclo_controller.php");

if(!empty($_SESSION['arrpostbuffer']['x']['i']['deviceciclo']['iddeviceciclocop']) and !empty($_SESSION["_pkid"])){
    $iddevciclo = $_SESSION["_pkid"];
    $iddevciclocop = $_SESSION['arrpostbuffer']['x']['i']['deviceciclo']['iddeviceciclocop'];

    DeviceCicloController::inserirDeviceCicloAtivEDeviceCicloAtivacao($iddevciclo, $iddevciclocop);
}

?>