<?
if(!empty($arrRep["_datas"])){
		
    if ($_REQUEST['_fds']){
        //echo 'aqui';
        $data = explode('-',$_REQUEST['_fds']);
        $data1 = $data[0];
        $data2 = $data[1];
        if (MenuRelatorioController::verificarData($data2))
        {
            $data2 = $data2.' 23:59:59';
            
        }

        if ($data1 and $data2)
        {
            // while (list($ko, $vo) = each($arrRep["_datas"])) {
            foreach($arrRep["_datas"] as $ko => $vo)
            {
                $dataFormat1 = evaltipocoldb($_tab, $vo, 'datetime', $data1);
                $dataFormat2  = evaltipocoldb($_tab, $data2, 'datetime', $data2);

                if($vo == 'aniversario')
                {
                    $dataFormat1 = "'".date('m-d', strtotime(str_replace("'", "", evaltipocoldb($_tab, $vo, 'datetime', $data1))))."'";
                    $dataFormat2 = "'".date('m-d', strtotime(str_replace("'", "", evaltipocoldb($_tab, $data2, 'datetime', $data2))))."'";
                }

                //echo '<br>';
                $_sqldata .= $_or . "(" . $vo . " between $dataFormat1 and $dataFormat2)";	
                $_or = " or ";
            }
        }

        $_sqldata = ' and ('.$_sqldata.') ';	
    }
}
?>