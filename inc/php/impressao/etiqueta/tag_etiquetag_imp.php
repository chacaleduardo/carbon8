<?
$idtag        = $_OBJ["idtag"];
$idempresa    = $_OBJ["idempresa"];

$tag = traduzid("tag","idtag","tag",$idtag);
$sigla = traduzid("empresa","idempresa","sigla",$idempresa);

$imp = $sigla."-".$tag;
	$_CONTEUDOIMPRESSAO ="
    ^XA^CF0,80
    ^FT180,140^FH\^FD$imp^FS
    ^XZ";

?>