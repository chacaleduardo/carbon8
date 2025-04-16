<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row=EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo14( $_OBJ['idlote'] );
if(!empty($row)){

    $_CONTEUDOIMPRESSAO.='^XA^CF0,20';

    $altura = 10;
    $_CONTEUDOIMPRESSAO.='^FO12,'.$altura.'^FD'.strtoupper($row['nomeinicio']).'^FS';
    $altura += 30;
    $_CONTEUDOIMPRESSAO.='^FO12,'.$altura.'^FD'.strtoupper($row['nomefim']).'^FS';
    $altura += 30;
    $_CONTEUDOIMPRESSAO.='^FO12,'.$altura.'^FD'.retira_acentos($row['partida']).'^FS';
    $altura += 30;

    $_CONTEUDOIMPRESSAO.='^FO12,'.$altura.'^FD'.$row['registro'].'^FS^XZ';

    



}
?>