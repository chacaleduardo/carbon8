<?
foreach($_SESSION['arrpostbuffer'] as $linha => $acao){
    $cols=(array_values($acao)[0]["_mtotabcol"]);//Recupera a primeira acao com array_values
    foreach($cols as $col => $val){
        re::dis()->hMSet("_cb:tabdef:_mtotabcol:".$cols["col"], [$col => $val]);
    }

}

?>