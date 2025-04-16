<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$res=EtiquetaController::buscarInfosEtiquetaTagQrCode( $_OBJ["idtag"], $_OBJ['filhos'] );

foreach($res as $k => $row){
$_CONTEUDOIMPRESSAO.="  ^XA
                        ^FO130,70
                        ^BQN,2,10
                        ^FDLA,sislaudo.laudolab.com.br/?_modulo=tag&_acao=u&idtag=".$row['idtag']."^FS
                        ^CF0,65
                        ^FB610,4,5^FO200,440^".strtoupper($row['tagoriginal'])."^FS
                        ^CF0,90
                        ^FB610,4,5^FO110,500^FD".strtoupper($row['tag'])."^FS
                        ^XZ
                        %_quebrapagina_%"; 
}
?>