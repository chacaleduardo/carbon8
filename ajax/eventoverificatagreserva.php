<?
require_once("../inc/php/functions.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/tag_controller.php");

$idevento=$_GET['idevento'];
$idsala= $_GET['idtag']; 
$execucao= $_GET['execucao']; 
$diainteiro=$_GET['diainteiro'];
$duracao=$_GET['duracaohms'];
$iniciohms=substr($_GET['iniciohms'], 0, 5);
$inicio=$_GET['inicio'];
$trava='Y'; 
$inicio.' '.$iniciohms;
$dataInicio = validadatetime($inicio.' '.$iniciohms); 

if($diainteiro=='false'){ 
    $arrfim = explode(":",$duracao);
    $fimDate  = DateTime::createFromFormat('d/m/Y H:i', $inicio.' '.$iniciohms);    
    $fimDate->modify('+'.$arrfim['0'].' hours');// Modify the date    
    $fimDate->modify('+'.$arrfim['1'].' minutes');// Modify the date    
    $_fimdate= $fimDate->format('d/m/Y H:i');// Output 
    $dataFim = validadatetime($_fimdate); 
}else{
    $_fimhms='23:59'; 
    $dataFim = validadatetime($inicio.' '.$_fimhms); 
}

$regraTrava = "";
$regraEvento = "";
 
if($trava=='N')
{
    $regraTrava=" and trava = 'Y' ";
}

if(!empty($idevento))
{
    $regraEvento=" and  objeto = 'evento' and idobjeto != ".$idevento; 
}

// $sql =  "SELECT 
//                 true as travado
//             FROM 
//                 tagreserva tr 
//             WHERE
//                 idtag=".$idsala."
//                 ".$instrev."
//                 ".$intrava."
//             and 
//                 (
//                     (
//                         if (tr.inicio<='".$inInicio."','".$inInicio."',tr.inicio) = '".$inInicio."' and 
//                         if(tr.fim>='".$inFim."','".$inFim."',tr.fim )= '".$inFim."'
//                     ) 
//                     or
//                     (
//                         (tr.inicio > '".$inInicio."' and tr.inicio < '".$inFim."') 
//                             or 
//                         (tr.fim > '".$inInicio."' and tr.fim < '".$inFim."')
//                     )
//                 )";

// $res = d::b()->query($sql) or die("EventoVerificaTagReserva: Erro ao verificar tag reserva: " . mysql_error() . "\nSQL: $sql");

$tagReservada = TagController::verificarReserva($idsala, $dataInicio, $dataFim, $regraEvento, $regraTrava);
//die($sql);
if ($tagReservada)
{
    echo('true');
    die;
}

echo('false');
		