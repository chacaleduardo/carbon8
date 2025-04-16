<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/amostra_controller.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

//Parâmetros mandatórios para o carbon
$pagvaltabela = "amostra";
$pagvalcampos = array(
	"idamostra" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from amostra where idamostra = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");

//echo var_dump($this);

require_once "./amostra.php";
