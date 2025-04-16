<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");



$row = EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo16zpl($_OBJ["idlote"]);

if(!empty($row)){

    $_CONTEUDOIMPRESSAO .= "^XA";
    $_CONTEUDOIMPRESSAO .= "^CF0,150
    ^FO490,200^FD".$row['sigla']."^FS
    ^CF0,32
    ^FO10,20^FDCLIENTE: ".retira_acentos($row['nomeinicio'])."^FS
    ^FO10,50^FD".retira_acentos($row['nomefim'])."^FS
    ^FO10,200^FDPARTIDA:^FS
    ^FO10,240^FD".$row['partida']."^FS";

    

    $_CONTEUDOIMPRESSAO .= "^XZ";
}
?>