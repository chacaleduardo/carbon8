<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row=EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo15( $_OBJ["idlote"] );
if(!empty($row)){

		$_CONTEUDOIMPRESSAO .= EtiquetaController::$cabecalhoTSPL50x30;
        $_CONTEUDOIMPRESSAO.='
        TEXT 10,10,"1",0,1,1,"'.retira_acentos($row['nomeinicio']).' "';
        $_CONTEUDOIMPRESSAO.='
        TEXT 10,30,"1",0,1,1,"'.retira_acentos($row['nomemeio']).' "';
        $_CONTEUDOIMPRESSAO.='
        TEXT 10,50,"1",0,1,1,"'.retira_acentos($row['nomefim']).' "';
        $_CONTEUDOIMPRESSAO.='
        TEXT 10,80,"3",0,1,1,"'.retira_acentos($row['descr']).' "';
        $_CONTEUDOIMPRESSAO.='
        TEXT 10,120,"2",0,1,1,"V: '.$row['vencimento'].' "';
        $_CONTEUDOIMPRESSAO.="
        PRINT 1
                ";
	
}
?>