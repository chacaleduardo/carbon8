<?
require_once(__DIR__."/../inc/php/validaacesso.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/menurelatorio_controller.php");
if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

?>
<link rel="stylesheet" href="./inc/css/dashboard.css"/>
<link rel="stylesheet" href="./inc/css/menurelatorio.css?version=1.1"/>

<script src="./inc/js/amcharts/amcharts.js"></script>
<script src="./inc/js/amcharts/serial.js"></script>
<script src="./inc/js/amcharts/pie.js"></script>

<?
function formatarArrModulosUsuario(){
    $modulosUsuario = getModsUsr('MODULOS');

    $virgulaP = "";
    $virgulaF = ""; 
    $virgulaFV = "";
    $modsPai = ""; 
    $modsFilho = ""; 
    $modsFilhoVinc = "";
    
    foreach($modulosUsuario as $m => $value){
        switch($value["tipo"]){
            case 'DROP':
                $modsPai .=  $virgulaP."'".$m."'"; 
                $virgulaP = ",";
                break;
            case 'BTINV':
                $modsFilhoVinc .=  $virgulaFV."'".$m."'"; 
                $virgulaFV = ",";
                break;
            case 'LINK':
            case 'MODVINC':
                $modsFilho .=  $virgulaF."'".$m."'"; 
                $virgulaF = ",";
                break;
            default: break;
        }
    }

    return [
        "modulosPai" => $modsPai,
        "modulosFilhos" => $modsFilho,
        "modulosFilhosV" => $modsFilhoVinc,
    ];
}

$mods = formatarArrModulosUsuario();

if($_GET['_menulateral'] != 'N' || !isset($_GET['_menulateral'])){
    

    $jsonReps = json_encode(MenuRelatorioController::buscarRelatoriosPorLps(
        $mods['modulosPai'], $mods['modulosFilhos'], $mods['modulosFilhosV'], cb::idempresa(), getModsUsr("LPS"))
    );

    $userPref = MenuRelatorioController::buscarPreferenciaPessoa($_GET["_modulo"], $_SESSION["SESSAO"]["IDPESSOA"]);
}else{
    $jsonReps = json_encode(MenuRelatorioController::buscarRelatoriosPorLps(
        $mods['modulosPai'], $mods['modulosFilhos'], $mods['modulosFilhosV'], cb::idempresa(), getModsUsr("LPS"), "'Y','N'", "WHERE u.idrep IN (".$_GET["_idrep"].")"
    ));
    $userPref = '{}';
}

$logoSistema = MenuRelatorioController::buscarLogoRelatorioBase64(cb::idempresa());

if(!empty($logoSistema)){?>
    <div id="logo_relatorio" class="hidden">
        <img src="data:image/png;base64,<?=$logoSistema?>"/>
    </div>
<?}?>

<? require_once(__DIR__."/../form/js/menurelatorio_js.php"); ?>