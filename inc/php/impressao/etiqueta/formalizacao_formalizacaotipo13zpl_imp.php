<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row=EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo13( $_OBJ['idlote'] );
if(!empty($row)){

    $_CONTEUDOIMPRESSAO.='^XA^CF0,20';

    $altura = 10;

    $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FD'.retira_acentos($row['partida']).'^FS';
    $altura += 30;

    $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FD'.$row['registro'].'^FS';

    $altura += 90;
    $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FDINOCULO^FS^XZ';

}
?>