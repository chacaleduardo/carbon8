<?
if(!empty($arrRep["_colvisiveis"])){
    //Transformar em string de 'Select n,...'
    // while (list($ko, $vo) = each($arrRep["_colvisiveis"])) {
    foreach($arrRep["_colvisiveis"] as $coluna)
    {
        if ($arrRep["_filtros"][$coluna]["tsum"] == 'Y'){
            $strselectfields .= $strvirg.'round(sum('.$coluna.'),2) as '.$coluna;
        }else{
            $strselectfields .= $strvirg.$coluna;
        }
        $strvirg = ", ";
    }
    $strselectfields = "select ".$strselectfields." "; 
    //Reseta Variaveis de controle de virgula
    $strvirg = "";
}
?>