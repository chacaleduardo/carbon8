<?
if($_GET['_acao']=='i'){
    getValorTag();

    $_SESSION["arrpostbuffer"]["1"]["i"]["tag"]["obs"] = addslashes($_SESSION["arrpostbuffer"]["1"]["i"]["tag"]["obs"]);
} else {
    $_SESSION["arrpostbuffer"]["1"]["u"]["tag"]["obs"] = addslashes($_SESSION["arrpostbuffer"]["1"]["u"]["tag"]["obs"]);
}

if(isset($_POST['_x_i_tagsala_idtag']))
{
    $atualizarIdTagPai = TagController::atualizarIdTagPaiPorIdTagFilho($_POST['_x_i_tagsala_idtag'], $_POST['_x_i_tagsala_idtagpai']);

    if($atualizarIdTagPai)
    {
        $_SESSION["arrpostbuffer"]["x"]["i"]["tagsala"]["idtag"] = "";
        $_SESSION["arrpostbuffer"]["x"]["i"]["tagsala"]["idtagpai"] = "";
    }
}

if(!empty($_SESSION["arrpostbuffer"]["1"]["u"]["tag"]["idtag"]) and !empty($_SESSION["arrpostbuffer"]["1"]["u"]["tag"]["idtagclass"])
    and $_POST["tag_oldidtagclass"] != $_SESSION["arrpostbuffer"]["1"]["u"]["tag"]["idtagclass"]){

    $_SESSION["arrpostbuffer"]["1"]["u"]["tag"]["idtagtipo"] = "";
}


if($_GET['_acao']=='u'){
$_SESSION["arrpostbuffer"]["1"]["u"]["tag"]["medidor"] = strtoupper($_SESSION["arrpostbuffer"]["1"]["u"]["tag"]["medidor"]);
}