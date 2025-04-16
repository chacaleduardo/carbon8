<?
require_once("../inc/php/validaacesso.php");

$pagvaltabela = "carrimbo";
$pagvalcampos = array(
	"idcarrimbo" => "pk"
);

$pagsql = "select * from carrimbo where idcarrimbo = '#pkid'";

include_once("../inc/php/controlevariaveisgetpost.php");

$qr = "SELECT mt.col as primarykey
        FROM carbonnovo._modulo m JOIN carbonnovo._mtotabcol mt ON (m.tab = mt.tab)
        WHERE mt.primkey = 'Y' AND m.modulo = '".$_1_u_carrimbo_tipoobjeto."'";
$rs = d::b()->query($qr) or die("Erro ao buscar formulário do módulo [".$_1_u_carrimbo_tipoobjeto."]");

if(mysqli_num_rows($rs) > 1){
    echo "Foram encontrados mais de um formulário para [".$_1_u_carrimbo_tipoobjeto."]";
    die;
}

$rw = mysqli_fetch_assoc($rs);
if(mysqli_num_rows($rs) == 0){
    echo "Não foi possível encontrar o formulário do módulo [".$_1_u_carrimbo_tipoobjeto."]";
    die;
}

?>

<script>
    // GVT - 24/08/2021 - Função p/ redirecionamento do módulo carrimbo p/ o módulo de destino
    (function(){
        let inParGet = "<?=$rw["primarykey"]?>=<?=$_1_u_carrimbo_idobjeto?>"
        CB.modulo = "<?=$_1_u_carrimbo_tipoobjeto?>"

        let hiddenElement = document.createElement('a');
		hiddenElement.href = `?_modulo=${CB.modulo}&_acao=u&${inParGet}`
		hiddenElement.click();
        
    })();
</script>