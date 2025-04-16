<?
if(!empty($_GET["_fts"])){
    userPref("u", $_modulo."._fts", $_GET["_fts"]);
    $arrFk = retPkFullTextSearch($_tabfull, $_GET["_fts"]/*, $_GET["_pagina"],$_arrModConf["limite"]*/);
    $countArrFk=$arrFk["foundRows"];
    $aspa = "'";
    if($countArrFk>0){
        
        $strPkFts = implode(",", $arrFk["arrPk"]);
        $strPkFts = $aspa . implode(($aspa.",".$aspa), $arrFk["arrPk"]) . $aspa;
        $str_fts = " and ".$_chavefts . " in (".$strPkFts.")";
    }
}
?>