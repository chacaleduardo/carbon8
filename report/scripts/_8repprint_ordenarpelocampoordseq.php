<?
if(!empty($arrRep["_groupby"])){
    //Ordenar pelo valor indicado no campo 'ordseq', que é a KEY deste array
    ksort($arrRep["_groupby"]);

    $strvirg = "";

    //Transformar em string de 'Order By' para o banco
    // while (list($ko, $vo) = each($arrRep["_groupby"])) {
    foreach($arrRep["_groupby"] as $ko => $vo)
    {
        $strgrp .= $strvirg.$vo;
        $strvirg = ", ";
    }

    //Concatena a ultima parte da string
    $strgrp = " group by ".$strgrp; 
}
?>