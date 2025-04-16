<?
// while (list($_col, $_val) = each($_GET)) {
foreach ($_GET as $_col => $_val) {
    $_between = false;
    $_val = urldecode($_val);
    if(!empty($_val) and ($_col != "_modulo") and ($_col != "_rep") and (substr($_col,-2) != "_2")){

        //Montar clausula para colunas between
        if (substr($_col,-2)=="_1"){
            $_col = substr($_col,0,-2); //Transforma do nome do campo para capturar informacoes de tipo
            $_colval1 = $_GET[$_col."_1"];
            $_colval2 = $_GET[$_col."_2"];
            if (MenuRelatorioController::verificarData($_colval2)){
                $_colval2 = $_colval2.' 23:59:59';
            }
            $_between = true;
        }

        $_datatype 	= 	$arrRep["_filtros"][$_col]["datatype"];
        $_psqkey 	= 	$arrRep["_filtros"][$_col]["psqkey"];
        $_entre 	= 	$arrRep["_filtros"][$_col]["entre"];
        $_insmanual = 	$arrRep["_filtros"][$_col]["inseridomanualmente"];
        $_like 		= 	$arrRep["_filtros"][$_col]["like"];
        $_inval 	= 	$arrRep["_filtros"][$_col]["inval"];
        $_in 		= 	$arrRep["_filtros"][$_col]["in"];
        $_findinset	= 	$arrRep["_filtros"][$_col]["findinset"];

        //Montar clausula somente para campos que estejam marcados como psqkey
        if($_psqkey=="Y" and $_insmanual=="N"){
            if($_between){	
                $_sqlwhere .= $_and . "(" . $_col . " between " . evaltipocoldb($_tab, $_col, $_datatype, $_colval1) . " and " . evaltipocoldb($_tab, $_colval2, $_datatype, $_colval2) . ")";
            }else{
                if ($_like == 'Y'){
                    if ($_datatype == 'text'){
                        $_datatype = 'varchar';
                    }
                    $_sqlwhere .= $_and . $_col . " like '%" . substr(substr(evaltipocoldb($_tab, $_col, $_datatype, $_val), 1), 0, -1)."%'" ;
                }else if ($_findinset == 'Y'){
                    if ($_datatype == 'text'){
                        $_datatype = 'varchar';
                    }
                    $_sqlwhere .= $_and." find_in_set(".$_val." , ".$_col.") ";
                }else if ($_inval == 'Y'){
                    if ($_datatype == 'text'){
                        $_datatype = 'varchar';
                    }
                    $_value=null;
                    $_val=explode(',',$_val);
                    if(count($_val)>=1){
                        $arrlenght=count($_val)-1;
                        foreach ($_val as $key => $value) {
                            if($key==$arrlenght){
                                $virg='';
                            } else {
                                $virg=',';
                            }
                            $_value.="'".$value."'".$virg;
                        }
                    }

                    $_sqlwhere .= $_and . $_col . " in (" . $_value . ")" ;
        
                }else if ($_in == 'Y'){
                    if ($_datatype == 'text'){
                        $_datatype = 'varchar';
                        $_sqlwhere .= $_and . $_col . " in (" . substr(substr(evaltipocoldb($_tab, $_col, $_datatype, $_val), 1), 0, -1).")" ;
                    }else{
                        $_sqlwhere .= $_and . $_col . " in (".$_val.")" ;
                    }
                }else{
                    $_sqlwhere .= $_and . $_col . " = " . evaltipocoldb($_tab, $_col, $_datatype, $_val);
                }
            }

            $_and = " and ";
            $_iclausulas++;
        }else{
            echo "\n<!-- Campo Ignorado: ".$_col." - Manual: ".$_insmanual." -->";
        }
    }
}

?>