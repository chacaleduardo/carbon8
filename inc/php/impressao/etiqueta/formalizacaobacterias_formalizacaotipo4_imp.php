<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$res = EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo4( $_OBJ['idlote'] );
if(count($res) > 0){

    foreach($res as $k => $row){

        $_CONTEUDOIMPRESSAO.= EtiquetaController::$cabecalhoTSPL40x20;             

        $_CONTEUDOIMPRESSAO.='
            TEXT 10,10,"2",0,1,1,"'.retira_acentos($row['nome']).' "';
        $_CONTEUDOIMPRESSAO.='
            TEXT 10,40,"2",0,1,1,"'.retira_acentos($row['nomefim']).' "';
        $_CONTEUDOIMPRESSAO.='
            TEXT 10,80,"2",0,1,1,"C.:'.$row['partida'].'/'.$row['exercicio'].' "';
        $_CONTEUDOIMPRESSAO.='
            TEXT 10,120,"2",0,1,1,"S.:'.$row['semente'].'/'.$row['exerciciosem'].' "';			
        $_CONTEUDOIMPRESSAO.="
            PRINT 1
        ";

    }

}
?>