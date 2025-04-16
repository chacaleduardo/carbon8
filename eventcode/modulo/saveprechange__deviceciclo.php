<?
// QUERYS
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/deviceciclo_query.php");

//Copiar o pedido;
if(!empty($_SESSION['arrpostbuffer']['x']['i']['deviceciclo']['iddeviceciclocop'])){
    $iddeviceciclo= $_SESSION['arrpostbuffer']['x']['i']['deviceciclo']['iddeviceciclocop'];
    
    $deviceCiclo = SQL::ini(DeviceCicloQuery::buscarDeviceCicloPorIdDeviceCiclo(), [
        'iddeviceciclo' => $iddeviceciclo
    ])::exec();

    $_SESSION['arrpostbuffer']['x']['i']['deviceciclo']['nomeciclo']=$deviceCiclo->data[0]['nomeciclo'];
    $_SESSION['arrpostbuffer']['x']['i']['deviceciclo']['status']=$deviceCiclo->data[0]['status'];
    $_SESSION['arrpostbuffer']['x']['i']['deviceciclo']['modelo']=$deviceCiclo->data[0]['modelo'];
     
 }
?>