<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row=EtiquetaController::buscarInfosEtiquetaImpetiquetaLote( $_OBJ["idlote"] );

if(!empty($row)){
    
    $_CONTEUDOIMPRESSAO .= EtiquetaController::$cabecalhoTSPL40x20;
    $_CONTEUDOIMPRESSAO.='
TEXT 20,20,"3",0,1,1,"'.$row['descr'].' "';
    $_CONTEUDOIMPRESSAO.='
TEXT 20,80,"3",0,1,1,"'.$row['descr'].' "';
    $_CONTEUDOIMPRESSAO.="
PRINT 1
    ";

}
?>