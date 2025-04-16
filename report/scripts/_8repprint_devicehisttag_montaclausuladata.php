<?
if(!empty($arrRep["_datas"])){
	
    if ($_REQUEST['_fds']){
            $data = explode('-',$_REQUEST['_fds']);
            $data1 = $data[0];
            $data2 = $data[1];
    } else {
        $data2 = date("d/m/Y");
        $data1 = date('d/m/Y', time()-60*60*24*7);
    }

    if (MenuRelatorioController::verificarData($data2)){
        $data2 = $data2.' 23:59:59';
       
   }

    if ($data1 and $data2){
        foreach($arrRep["_datas"] as $ko => $vo)
        {
            //echo '<br>';
            $_sqldata .= $_or . "(" . $vo . " between " . evaltipocoldb($_tab, $vo, 'datetime', $data1) . " and " . evaltipocoldb($_tab, $data2, 'datetime', $data2) . ")";	
            $_or = " or ";
        }
    }
    
    $_sqldata = ' and ('.$_sqldata.') ';	

}
?>