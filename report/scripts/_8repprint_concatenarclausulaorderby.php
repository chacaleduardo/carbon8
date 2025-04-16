<?
if(!empty($arrRep["_orderby"])){
    //Ordenar pelo valor indicado no campo 'ordseq', que é a KEY deste array
    ksort($arrRep["_orderby"]);

    //Transformar em string de 'Order By' para o banco
    // while (list($ko, $vo) = each($arrRep["_orderby"])) {
    foreach($arrRep["_orderby"] as $ko => $vo)
    {
        $strord .= $strvirg.$vo;
        $strvirg = ", ";
    }

    //Concatena a ultima parte da string
    $strord = " order by ".$strord; 
}
?>