<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/permissao.php");

// CONTROLLER  
require_once(__DIR__ . "/controllers/controleviagem_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "controleviagem";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idcontroleviagem" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from controleviagem where idcontroleviagem = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$controleViagem = [];

if($_acao == 'i') {
    if(ControleViagemController::existeViagemEmAndamentoPorUsuario($_SESSION['SESSAO']['USUARIO'])) die('Já existe uma viagem em andamento.');
}

if($_1_u_controleviagem_idcontroleviagem) {
    $controleViagem = ControleViagemController::buscarViagem($_1_u_controleviagem_idcontroleviagem);
}

?>
<link rel="stylesheet" href="/form/css/controleviagem_css.css">
<div class="row" id="controle-viagem"></div>
<script src="./inc/js/qr-scanner/qr-scanner.legacy.min.js" type="text/javascript"></script>
<?
require_once(__DIR__ . "/js/controleviagem_js.php");

$tabaud = "controleviagem"; //pegar a tabela do criado/alterado em antigo
$_disableDefaultDropzone = true;
require 'viewCriadoAlterado.php';
?>