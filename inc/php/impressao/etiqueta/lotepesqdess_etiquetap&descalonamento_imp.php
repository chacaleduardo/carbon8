<?
require_once(__DIR__."/../../../../form/controllers/lote_controller.php");
require_once(__DIR__."/../../../../form/controllers/prodserv_controller.php");
require_once(__DIR__."/../../../../form/controllers/prodservformula_controller.php");

$lote = LoteController::buscarPorChavePrimaria($_OBJ['idlote']);
$prodServ = ProdServController::buscarPorChavePrimaria($lote['idprodserv']);
$prodServFormula = ProdservformulaController::buscarPorChavePrimaria($lote['idprodservformula']);

$_CONTEUDOIMPRESSAO = "^XA";
$_CONTEUDOIMPRESSAO .= "^CF0,20";

$_CONTEUDOIMPRESSAO .= "^FO220,20";
// Cabecalho
$_CONTEUDOIMPRESSAO .= "^FB200,2,5,C ^FDPESQUISA E DESENVOLVIMENTO\&^FS";

// Prodserv
$_CONTEUDOIMPRESSAO .= "^CF0,25 ^FO80, 100 ^FB500,2,5,C ^FD".retira_acentos($prodServ['descr'])."\&^FS";
// Tipo (formula)
$_CONTEUDOIMPRESSAO .= "^CF0,20 ^FO73, 190 ^FB500,2,5,C ^FDTIPO: ".retira_acentos($prodServFormula['rotulo'])."\&^FS";
// Partida
$_CONTEUDOIMPRESSAO .= "^FO76, 212 ^FB500,2,5,C ^FDPARTIDA: {$lote['partida']}/{$lote['exercicio']}\&^FS";
// Diluicao (qtdprod)
$_CONTEUDOIMPRESSAO .= "^FO73, 236 ^FB500,2,5,C ^FDDILUICAO: ".recuperaExpoente(tratanumero($lote['qtdprod']),$lote['qtdprod_exp'])."\&^FS";
// Rodape
$_CONTEUDOIMPRESSAO .= "^CF0,10 ^FO75, 290 ^FB500,2,5,C ^FDARMAZENAR EM LOCAL PARA TESTE DO P&D, CASO HOUVER ALGUMA DUVIDA ENTRAR EM CONTATO COM O SETOR VENDA PROIBIDA\&^FS";
$_CONTEUDOIMPRESSAO .= "^XZ";

?>