<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$res = EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo3( $_OBJ["idlote"] );

if(count($res) > 0){

    $cabecalho = EtiquetaController::$cabecalhoTSPL40x20;   

    foreach($res as $k =>$row){

        $_CONTEUDOIMPRESSAO.=$cabecalho;

        $tamanho = strlen($row['descr']);
        if($tamanho > 22){
            $_CONTEUDOIMPRESSAO.='
TEXT 10,10,"2",0,1,1,"'.retira_acentos($row['descrinicio']).' "';
            $_CONTEUDOIMPRESSAO.='
TEXT 10,40,"2",0,1,1,"'.retira_acentos($row['descrfim']).' "';
            $_CONTEUDOIMPRESSAO.='
TEXT 10,60,"2",0,1,1,"Part.:'.retira_acentos($row['partida']).' "';
            $_CONTEUDOIMPRESSAO.='
TEXT 10,80,"2",0,1,1,"Fabr.:'.$row['fabricacao'].'"';
            $_CONTEUDOIMPRESSAO.='
TEXT 10,100,"2",0,1,1,"Venc.:'.$row['vencimento'].' "';

            $row3 = EtiquetaController::buscarVolumeFormulaDoLoteParaEtiquetaFormalizacao( $_OBJ['idlote'] );
            if(!empty($row3)){
                $_CONTEUDOIMPRESSAO.='
TEXT 10,120,"2",0,1,1,"Vol.:'.$row3['formula'].' "';
            }
            
            $_CONTEUDOIMPRESSAO.="
PRINT 1
                    ";
        }else{
            $_CONTEUDOIMPRESSAO.='
TEXT 10,10,"2",0,1,1,"'.retira_acentos($row['descr']).' "';
            $_CONTEUDOIMPRESSAO.='
TEXT 10,40,"2",0,1,1,"Part.:'.retira_acentos($row['partida']).' "';
            $_CONTEUDOIMPRESSAO.='
TEXT 10,60,"2",0,1,1,"Fabr.:'.$row['fabricacao'].'"';
            $_CONTEUDOIMPRESSAO.='
TEXT 10,80,"2",0,1,1,"Venc.:'.$row['vencimento'].' "';

            $row3 = EtiquetaController::buscarVolumeFormulaDoLoteParaEtiquetaFormalizacao( $_OBJ['idlote'] );
            if(!empty($row3)){
                $_CONTEUDOIMPRESSAO.='
TEXT 10,100,"2",0,1,1,"Vol.:'.$row3['formula'].' "';
            }
            $_CONTEUDOIMPRESSAO.="
PRINT 1
                    ";
        }
    }

}
?>