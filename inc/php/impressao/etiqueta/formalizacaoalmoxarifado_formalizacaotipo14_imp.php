<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row=EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo14( $_OBJ["idlote"] );
if(!empty($row)){

    $_CONTEUDOIMPRESSAO .= EtiquetaController::$cabecalhoTSPL40x20;

    $altura = 10;
 
    $_CONTEUDOIMPRESSAO.='
        TEXT 8,'.$altura.',"2",0,1,1,"'.retira_acentos($row['partida']).'"';
    $altura += 30;

    $_CONTEUDOIMPRESSAO.='
    TEXT 8,'.$altura.',"2",0,1,1,"'.$row['registro'].'"';
    
    $_CONTEUDOIMPRESSAO.="
    PRINT 1
            ";

}
?>