<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row = EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo8( $_OBJ['idlote'] );
if(!empty($row)){

    $_CONTEUDOIMPRESSAO.='^XA^CF0,20';
    $altura = 10;
    if(strlen($row['nome']) > 22){
        $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FD'.strtoupper(retira_acentos(substr($row['nome'],0,30))).'^FS';
        $altura +=40;
        $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FD'.strtoupper(retira_acentos(substr($row['nome'],30))).'^FS';
    }else{
        $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FD'.strtoupper(retira_acentos($row['nome'])).'^FS';
    }

    $altura += 40;
    $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FDPART:'.retira_acentos($row['partida']).'^FS';
    $altura += 40;
    $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FDTESTE GRAM E INATIVACAO^FS^XZ';
    // $altura += 25;
    // $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FDINATIVACAO^FS^XZ';
}
?>