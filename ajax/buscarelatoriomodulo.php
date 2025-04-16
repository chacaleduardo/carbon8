<?
require_once("../inc/php/functions.php");

// CONTROLLERS
require_once(__DIR__."/../form/controllers/dashboardsnippet_controller.php");
require_once(__DIR__."/../form/controllers/menurelatorio_controller.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

if(!empty($_GET["getrepnames"]) && $_GET["getrepnames"] == 'Y'){
    $modulos = explode(",",$_GET["modulos"]);
    $modulosConcatenados = "";
    $virg = "";
    foreach ($modulos as $value) {
        $modulosConcatenados .= $virg."'".$value."'";
        $virg = ",";
    }
    if(!empty($modulosConcatenados)){
        if($_GET["dashboard"] == 'Y'){
            $dashboardClause = "AND r.dashboard = 'Y'";
        }else{
            $dashboardClause = "";
        }

        // $qr = "SELECT mr.idrep,r.rep,mr.modulo,r.url,r.tipograph,r.titlebutton,ifnull(m1.idmodulo,m.idmodulo) as idmodpai
        //     FROM "._DBCARBON."._modulorep mr 
        //     JOIN "._DBCARBON."._rep r ON (mr.idrep = r.idrep)
        //     JOIN "._DBCARBON."._modulo m ON (mr.modulo = m.modulo)
        //     LEFT JOIN "._DBCARBON."._modulo m1 ON (m1.modulo = m.modulopar)
        //     WHERE mr.modulo IN (".$modulosConcatenados.") 
        //     AND EXISTS(select 1 from "._DBCARBON."._lprep ep where  ep.idlp in (".getModsUsr("LPS").") and ep.idrep=r.idrep)
        //     AND r.tab <> '' ".$dashboardClause."
        //     AND r.status = 'ATIVO'
        //     GROUP BY mr.idrep";
        // $rs = d::b()->query($qr) or die('{"erro":"Na procura dos relatórios"}');
        $relatorios = DashboardSnippetController::buscarRelatoriosPorModuloELp($modulosConcatenados, getModsUsr("LPS"), $dashboardClause);
        // if(mysqli_num_rows($rs) > 0){
        //     $i = 0;
        //     $arrtmp = array();
        //     while($rw = mysqli_fetch_assoc($rs)){
        //         $arrtmp[$i]["idrep"] = $rw["idrep"];
        //         $arrtmp[$i]["rep"] = $rw["rep"];
        //         $arrtmp[$i]["modulo"] = $rw["modulo"];
        //         $arrtmp[$i]["idmodpai"] = $rw["idmodpai"];
        //         $arrtmp[$i]["url"] = $rw["url"];
        //         $arrtmp[$i]["tipograph"] = $rw["tipograph"];
        //         $arrtmp[$i]["titlebutton"] = empty($rw["titlebutton"]) ? '' : $rw["titlebutton"];
        //         $i++;
        //     }
        //     echo json_encode($arrtmp, true);
        // }else{
        //     echo "[]";
        // }

        echo json_encode($relatorios);
    }else{
        echo '{"erro":"Parâmetro inválido"}';
    }
    
    die;
}

//
if(empty($_GET["idrep"])){
    echo '{"erro":"Parâmetro inválido"}';
    die;
}

$modulo = explode(",",$_GET["modulos"]);
$idrep = $_GET["idrep"];
$arr = [];
foreach ($modulo as $value) {
    $resposta = MenuRelatorioController::buscarConfiguracaoDoModuloRelatorio($value, true, $idrep, 'tc.ordpos');
    if(!empty($resposta)){
        $aux = [];
        foreach($resposta as $k => $v){
            foreach($v["_filtros"] as $a => $b){
                if($b["psqkey"] == "Y"){
                    $aux[$k]["rep"] = $v["rep"];
                    $aux[$k]["url"] = $v["url"];
                    $aux[$k]["idrep"] = $v["idrep"];
                    $aux[$k]["tipograph"] = $v["tipograph"];
                    $aux[$k]["flgunidade"] = $v["flgunidade"];
                    $aux[$k]["_filtros"][$a] = $b;
                }
            }
        }
        $arr[$value] = $aux;
    }
}

echo json_encode($arr, true);
?>
