<?
if(!empty($arrRep["_colvisiveis"]))
{
    // while (list($ko, $vo) = each($arrRep["_colvisiveis"])) {
    foreach($arrRep["_colvisiveis"] as $coluna) {
        if ($arrRep["_filtros"][$coluna]["tsum"] == 'Y'){
                if (MenuRelatorioController::contemDecimal($coluna)){
                    $strselectfields .= $strvirg.'round(sum('.$coluna.'),2) as '.$coluna;
                }else{
                    $strselectfields .= $strvirg.'round(sum('.$coluna.'),2) as '.$coluna;
                }
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