<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$idunidadepadrao = getUnidadePadraoModulo($_MOD,cb::idempresa());
if (!empty($_OBJ['idloc'])) {
    $str = " and c.idlotelocalizacao=".$_OBJ['idloc'];
}else{
      $str = "";
}

$idtipounidade= traduzid('unidade', 'idunidade', 'idtipounidade', $idunidadepadrao);

$res=EtiquetaController::buscarInfosEtiquetaImpetiquetaLoteAlmox( $_OBJ["idlote"], $str, $idtipounidade);

if(count($res) > 0){
    
	foreach($res as $k => $row){
            
            $_CONTEUDOIMPRESSAO .= EtiquetaController::$cabecalhoTSPL58x30;     
            $_CONTEUDOIMPRESSAO.='
            QRCODE 90,10,L,2,A,0,"https://sislaudo.laudolab.com.br/?_modulo=lotelogistica&_acao=u&idlote='.$_OBJ['idlote'].'"';
            $_CONTEUDOIMPRESSAO.='
            TEXT 175,10,"3",0,1,1,"'.retira_acentos($row['descr']).' "';
            $_CONTEUDOIMPRESSAO.='
            TEXT 15,10,"5.EFT",0,1,1,"'.$row["sigla"].'"';
            $_CONTEUDOIMPRESSAO.='
            TEXT 175,60,"2",0,1,1,"V: '.$row['vencimento'].' "';
		$_CONTEUDOIMPRESSAO.='
            TEXT 15,90,"1",0,1,1,"'.retira_acentos($row['nomeinicio']).' "';
	      $_CONTEUDOIMPRESSAO.='
            TEXT 15,110,"1",0,1,1,"'.retira_acentos($row['nomemeio']).' "';	
	      $_CONTEUDOIMPRESSAO.='
            TEXT 15,130,"1",0,1,1,"'.retira_acentos($row['nomefim']).' "';						
            $_CONTEUDOIMPRESSAO.='
            TEXT 15,160,"1",0,1,1,"DATA LOTE:'.retira_acentos(dma($row['criadoem'])).' "';				
            $_CONTEUDOIMPRESSAO.='
            TEXT 15,180,"1",0,1,1,"LOCAL:'.retira_acentos(substr($row['campo'],0,40)).' "';				
            $_CONTEUDOIMPRESSAO.='
            TEXT 15,200,"1",0,1,1,"'.retira_acentos(substr($row['campo'],40)).' "';	                  
		$_CONTEUDOIMPRESSAO.="
            PRINT 1
		%_quebrapagina_%";

	}
}
?>