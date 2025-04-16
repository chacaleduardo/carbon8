<?
if(!empty($arrRep["_colvisiveis"]))
{
    foreach($arrRep["_colvisiveis"] as $ko => $vo)
    {
        if ($arrRep["_filtros"][$vo]["tsum"] == 'Y'){
            $strselectfields .= $strvirg.'sum('.$vo.') as '.$vo;
        }else{
            $strselectfields .= $strvirg.$vo;
        }
        $strvirg = ", ";
    }

    $strselectfields = "select ".$strselectfields." "; 
    
    //Reseta Variaveis de controle de virgula
    $strvirg = "";
}
?>