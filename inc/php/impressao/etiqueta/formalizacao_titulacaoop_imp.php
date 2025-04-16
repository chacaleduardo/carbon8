<?
require_once(__DIR__."./../../../../form/controllers/formalizacao_controller.php");

$valor = $_OBJ;

$idRegistros = FormalizacaoController::buscarIdRegistroTitulacaoPorIdLote($valor['idlote']);

if(!$idRegistros) die('ID REGISTRO NÃO INFORMADO!');

foreach($idRegistros as $registro)
{
    $_CONTEUDOIMPRESSAO = "^XA";
    // Cabecalho
    $_CONTEUDOIMPRESSAO .= "^CF0,18
    ^FO0,20
    ^FB413,2,5,C
    ^FDINATA PRODUTOS BIOLOGICOS LTDA\&^FS";
    // Exercicio
    $_CONTEUDOIMPRESSAO .= "^FO0,70
    ^FB413,2,5,C
    ^FD{$_OBJ['partida']}/{$_OBJ['exercicio']}\&^FS";
    // Registro
    $_CONTEUDOIMPRESSAO .= "^FO0,115
    ^FB413,2,5,C
    ^FDREGISTRO: {$registro['idregistro']}\&^FS";
    // Rodape
    $_CONTEUDOIMPRESSAO .= "^FO0,140
    ^FB413,2,5,C
    ^FDTITULACAO\&^FS";
    $_CONTEUDOIMPRESSAO .= "^XZ";
}
