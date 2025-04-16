<?
require_once(__DIR__."/../../form/controllers/tag_controller.php");
// $mapaEquipamentoModel = new MapaEquipamento();

if($_POST['_idtagsala_'] && stripos($_POST['_idtagsala_'], 'sem') === false || $_POST['_idtagequipamento_'])
{
    $isSala = $_POST['_idtagequipamento_'] ? false : true;

    TagController::atualizarTagPaiOuFilhoNaTagSala(($isSala ? 'idtagpai' : 'idtag'), $isSala ? $_POST['_idtagsala_'] : $_POST['_idtagequipamento_']);

    // $sql = "SELECT idtagsala
    //         FROM tagsala
    //         WHERE ".($isSala ? 'idtagpai' : 'idtag')." = ".($isSala ? $_POST['_idtagsala_'] : $_POST['_idtagequipamento_']);

    // $result = d::b()->query($sql) or die("Erro: atualização de salas falhou. ".mysql_error(d::b()));

    // $i = 0;

    // while($item = mysql_fetch_assoc($result))
    // {
    //     $_SESSION["arrpostbuffer"][$i]["d"]["tagsala"]["idtagsala"] = $item['idtagsala'];

    //     $i++;
    // }

    // unset($_SESSION["arrpostbuffer"]["x"]["u"]["mapaequipamento"]["idtagsala"]);
}
