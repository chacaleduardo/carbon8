<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");



$row = EtiquetaController::buscarinfosEtiquetaFormalizacaoTipo1($_OBJ["idlote"]);

if(!empty($row)){

    $_CONTEUDOIMPRESSAO .= "^XA^CF0,24";

    if(empty($row["idpessoa"])){
        $row['nomeinicio']="  ".$row['nomefantasia']." ";
    }

    $valprod=recuperaExpoente(tratanumero($row['qtdprod']),$row['qtdprod_exp']);
    $_CONTEUDOIMPRESSAO.='^FO60,20^FD'.retira_acentos($row['nomeinicio']).'^FS';	
    $_CONTEUDOIMPRESSAO.='^FO60,60^FD'.retira_acentos($row['nomefim']).'^FS';	
    $_CONTEUDOIMPRESSAO.='^FO60,100^FD'.$row['partida'].' '.$row['fabricacao'].' '.$row['vencimento'].'^FS';
    $_CONTEUDOIMPRESSAO.='^FO60,140^FD'.retira_acentos($row['descr']).'^FS';
    $_CONTEUDOIMPRESSAO.='^FO60,180^FDQUANTIDADE PRODUZIDA: '.$valprod.' SF.: '.$row['solfab'].'^FS';

    $resl = EtiquetaController::buscarSementesEtiquetaFormalizacaoTipo1($_OBJ["idlote"]);

    if(count($resl)>0){

        $_CONTEUDOIMPRESSAO.='^FO90,250^FD';	
        $sem=0;
        $tamanho=250;

        foreach($resl as $k => $row1){
            $sem=$sem+1;
            if($sem==6){
                $sem=0;
                $tamanho=$tamanho+40;
                $_CONTEUDOIMPRESSAO.='^FS^FO90,'.$tamanho.'^FD';
            }

            $_CONTEUDOIMPRESSAO.=''.retira_acentos($row1['semente']).' ';	
        }
        
        $_CONTEUDOIMPRESSAO.='^FS';	
    }

    $_CONTEUDOIMPRESSAO .= "^XZ";
}
?>