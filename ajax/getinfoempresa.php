<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    header("HTTP/1.1 401 Unauthorized");
    die;
}

if (!empty($_POST['idempresa']) || $_POST['modulo'] == '_lp') 
{
    if($_POST['idempresa']){
        $sql = 'SELECT idempresa, sigla, corsistema, iconemodal from empresa where idempresa in ('.$_POST['idempresa'].")";
    } else {
        $sql = "SELECT e.idempresa, e.sigla, e.corsistema, e.iconemodal
			      FROM "._DBCARBON."._lp l JOIN empresa e ON e.idempresa = l.idempresa
			     WHERE l.idlp = '".$_POST['idobjeto']."'";
    }
    
    $resinfo = d::b()->query($sql) or die("Falha ao pesquisar empresa");

    if ($resinfo) {
        $i=0;
        while($rowemp = mysqli_fetch_assoc($resinfo)){
            $arr[$rowemp["idempresa"]]['sigla'] = $rowemp['sigla'];
            $arr[$rowemp["idempresa"]]['corsistema'] = $rowemp['corsistema'];
            $arr[$rowemp["idempresa"]]['iconemodal'] = $rowemp['iconemodal'];
            $arr['idempresa'] = $rowemp['idempresa'];
            $i++;
        }
        $json = json_encode($arr);
        cbSetPostHeader('1','buscaempresa');
        echo($json);
    }
}else {
    cbSetPostHeader('1','buscaempresa');
    echo "{}";
}

?>