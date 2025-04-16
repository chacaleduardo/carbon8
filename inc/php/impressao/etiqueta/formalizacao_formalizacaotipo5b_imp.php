<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row = EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo5b( $_OBJ["idlote"] );

if(!empty($row)){

    if(empty($row["idpessoa"])){
        $row['nomeinicio']="INATA PRODUTOS - BIOLOGICOS";
    }

    $valprod=recuperaExpoente(tratanumero($row['qtdprod']),$row['qtdprod_exp']);
    
    if(!empty($row['nomeinicio'])){
        $nInicio=retira_acentos($row['nomeinicio']);			
    }

    if(!empty($row['descrinicio'])){
        $descInicio=retira_acentos($row['descrinicio']);			
    }
    if(!empty($row['descrfim'])){
        $descFim=retira_acentos($row['descrfim']);
    }
    if(!empty($row['partida'])){
        $partida=$row['partida'].'^FS';
    }
    if(!empty($row['fabricacao'])){
        $fab=$row['fabricacao'];
        $venc=$row['vencimento'];
    }
    if(!empty($valprod)){
        $und=$row['un'];
    }
    $margemStr=90;
    $nSquare=20;
    $sc=1;
    $prinSquare = "";
    $squareMarginTopLine1 = 255;
    $squareMarginTopLine2 = 284;
    $squareMarginLeft = 100;
    $squareWide = 30;
    while($nSquare >= $sc){
        $prinSquare .= "^FX^FO".$squareMarginLeft.",".$squareMarginTopLine1."^GB32,32,3^FS";
        $prinSquare .= "^FX^FO".$squareMarginLeft.",".$squareMarginTopLine2."^GB32,32,3^FS";
        $squareMarginLeft = $squareMarginLeft+$squareWide;
        $sc++;
    }

    $_CONTEUDOIMPRESSAO .= "
    ^XA^CF0,20
            
    ^FO".$margemStr.",25^FD".$nInicio."^FS		
    ^FB620,20^FO".$margemStr.",55^FD".$descInicio." ".$descFim."^FS		
    ^FO".$margemStr.",130^FD".$partida."^FS
    ^FO".$margemStr.",155^FD".$fab." - ".$venc."^FS
    ^FO".$margemStr.",180^FDQUANTIDADE PRODUZIDA:  ".$valprod." ".$und."^FS

    ^FO225,225^FDQUANTIDADE DE DESCONGELAMENTO:^FS
    ".$prinSquare.
    "^XZ";
}
?>