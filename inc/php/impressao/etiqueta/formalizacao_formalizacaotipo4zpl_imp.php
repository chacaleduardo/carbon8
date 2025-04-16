<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

// $_OBJ['idlote'] = 6619545;
$res = EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo4( $_OBJ['idlote'] );
if(count($res) > 0){

    foreach($res as $k => $row){

        // $_CONTEUDOIMPRESSAO.= EtiquetaController::$cabecalhoTSPL40x20;             

        $_CONTEUDOIMPRESSAO.='^XA^CF0,26';
        $_CONTEUDOIMPRESSAO.='^FO10,10^FD'.retira_acentos($row['nome']).'^FS';
        $_CONTEUDOIMPRESSAO.='^FO10,50^FD'.retira_acentos($row['nomefim']).'^FS';
        $_CONTEUDOIMPRESSAO.='^FO10,90^FDC.: '.$row['partida'].'/'.$row['exercicio'].' ^FS';
        $_CONTEUDOIMPRESSAO.='^FO10,130^FDS.: '.$row['semente'].'/'.$row['exerciciosem'].'^FS^XZ';
        // $_CONTEUDOIMPRESSAO.='
        //     TEXT 10,10,"2",0,1,1,"'..' "';
        // $_CONTEUDOIMPRESSAO.='
        //     TEXT 10,40,"2",0,1,1,"'..' "';
        // $_CONTEUDOIMPRESSAO.='
        //     TEXT 10,80,"2",0,1,1,"C.:'..' "';
        // $_CONTEUDOIMPRESSAO.='
        //     TEXT 10,120,"2",0,1,1,"S.: "';			
        // $_CONTEUDOIMPRESSAO.="
        //     PRINT 1
        // ";

    }

}
?>