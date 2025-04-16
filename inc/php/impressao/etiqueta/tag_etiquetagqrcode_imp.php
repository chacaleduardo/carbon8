<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$res=EtiquetaController::buscarInfosEtiquetaTagQrCode( $_OBJ["idtag"], $_OBJ['filhos'] );

foreach($res as $k => $row){
$_CONTEUDOIMPRESSAO.="^XA
            ^FO15,5
            ^BQN,2,4
            ^FDLA,sislaudo.laudolab.com.br/?_modulo=tag&_acao=u&idtag=".$row['idtag']."^FS
            ^CF0,23
            ^FB610,4,5^FO160,20^FD".strtoupper($row['tagoriginal'])."^FS
            ^CF0,50,30
            ^FB610,4,5^FO160,105^FD".strtoupper($row['tag'])."^FS
            ^XZ
    %_quebrapagina_%"; 
}


?>