<?
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/feriado_query.php");

/*
 * gerar os itens da folha de pagamento
 * 
 */
session_start();

$feriados = SQL::ini(FeriadoQuery::buscarFeriadoPorIdEmpresaEShare(), [
  'idempresa' => cb::idempresa(),
  'share' => share::otipo('session')::feriado('idferiado')
])::exec();

$response = array();
$posts = array();

$virgula = "";

foreach($feriados->data as $feriado)
{ 
  $dataferiado.= $virgula."'".$feriado['dataferiado']."'";  
  $calendarioferiado .=$virgula.'{"feriado":"true",allDay:"true",display:"background",backgroundColor:"#ff99a8",id:'.$feriado['idferiado'].',start:"'.$feriado['dataferiado'].'",textColor:"white",title:"<span>'.$feriado['obs'].'</span>","className":"event-full"}';

 
  $virgula = ",";
}  

$dataferiado = 'var v_feriados = ['.$dataferiado.'];';
$calendarioferiado = 'var v_calendarioferiados = ['.$calendarioferiado.'];';

$fp = fopen(__DIR__.'/../../inc/tmp/feriado.js', 'w');
if ( !$fp ) {
        throw new Exception('File open failed.');
      } 
fwrite($fp,($dataferiado));
fclose($fp);
$fp = fopen(__DIR__.'/../../inc/tmp/calendarioferiado.js', 'w');
if ( !$fp ) {
        throw new Exception('File open failed.');
      } 
fwrite($fp,($calendarioferiado));
fclose($fp);
?>