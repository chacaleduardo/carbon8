<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row = EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo8( $_OBJ["idlote"] );
if(!empty($row)){

    $_CONTEUDOIMPRESSAO.=EtiquetaController::$cabecalhoTSPL40x20;
    $altura = 10;
    if(strlen($row['nome']) > 22){
        $_CONTEUDOIMPRESSAO.='
            TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos(substr($row['nome'],0,22)).'"';
        $altura += 20;
        $_CONTEUDOIMPRESSAO.='
            TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos(substr($row['nome'],22)).'"';
    }else{
        $_CONTEUDOIMPRESSAO.='
            TEXT 10,'.$altura.',"2",0,1,1,"'.retira_acentos($row['nome']).'"';
    }
    $altura += 40;
    $_CONTEUDOIMPRESSAO.='
        TEXT 10,'.$altura.',"2",0,1,1,"PART:'.retira_acentos($row['partida']).'"';
    $altura += 40;
    $_CONTEUDOIMPRESSAO.='
        TEXT 10,'.$altura.',"2",0,1,1,"TESTE GRAM E"';
    $altura += 20;
    $_CONTEUDOIMPRESSAO.='
        TEXT 10,'.$altura.',"2",0,1,1,"INATIVACAO"';
    $_CONTEUDOIMPRESSAO.="
    PRINT 1
            ";
}
?>