<?
$partida    = $_OBJ["partida"];
$exercicio  = $_OBJ["exercicio"];
$idlote     = $_OBJ["idlote"];
$modulo     = $_OBJ["modulo"];
$_CONTEUDOIMPRESSAO='';
$_CONTEUDOIMPRESSAO.="^XA^CF0,30"; 
$_CONTEUDOIMPRESSAO.='^FT360,60^FH\^FD'.$partida.'/'.$exercicio.'^FS';
$_CONTEUDOIMPRESSAO.='^FT100,60^FH\^FD'.$partida.'/'.$exercicio.'^FS';
$_CONTEUDOIMPRESSAO.="^PQ1,0,1,Y";
$_CONTEUDOIMPRESSAO.="^XZ";

?>