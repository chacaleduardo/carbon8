<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row = EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo5( $_OBJ["idlote"] );

if(!empty($row)){

    $_CONTEUDOIMPRESSAO .= "^XA^CF0,24"; 

    if(empty($row["idpessoa"])){
        $row['nomeinicio']="  ".$row['nomefantasia']." ";
    }

    $valprod=recuperaExpoente(tratanumero($row['qtdprod']),$row['qtdprod_exp']);
    $pos = 20;
    if(!empty($row['nomeinicio'])){
        $_CONTEUDOIMPRESSAO.='^FO90,'.$pos.'^FD'.retira_acentos($row['nomeinicio']).'^FS';
        $pos = $pos + 40;
    }
    if(!empty($row['nomefim'])){
        $_CONTEUDOIMPRESSAO.='^FO90,'.$pos.'^FD'.retira_acentos($row['nomefim']).'^FS';
        $pos = $pos + 40;
    }
    if(!empty($row['descrinicio'])){
        $_CONTEUDOIMPRESSAO.='^FO90,'.$pos.'^FD'.retira_acentos($row['descrinicio']).'^FS';
        $pos = $pos + 40;
    }
    if(!empty($row['descrfim'])){
        $_CONTEUDOIMPRESSAO.='^FO90,'.$pos.'^FD'.retira_acentos($row['descrfim']).'^FS';
        $pos = $pos + 40;
    }
    if(!empty($row['partida'])){
        $_CONTEUDOIMPRESSAO.='^FO90,'.$pos.'^FD'.$row['partida'].'^FS';
        $pos = $pos + 40;
    }
    if(!empty($row['fabricacao'])){
        $_CONTEUDOIMPRESSAO.='^FO90,'.$pos.'^FD'.$row['fabricacao'].' '.$row['vencimento'].'^FS';
        $pos = $pos + 40;
    }
    if(!empty($valprod)){
        $_CONTEUDOIMPRESSAO.='^FO90,'.$pos.'^FDQUANTIDADE PRODUZIDA: '.$valprod.' '.$row['un'].'^FS';
        $pos = $pos + 40;
    }

    $_CONTEUDOIMPRESSAO .= "^XZ";	
}
?>