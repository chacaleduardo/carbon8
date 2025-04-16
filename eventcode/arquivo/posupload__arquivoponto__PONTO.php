<?
$idresultado=$_POST['idobjeto'];

//print_r($_POST);
//echo $arq_final."<br>";

$handle = @fopen($arq_final, "r");

//000003682329012020100401266154998882C5

if ($handle) {
    $linha=0;
    while (!feof($handle)) {
        $linha=$linha+1;
        //cod bat # n bat # data # hora # id funcionario sislaudo
	//000008725 # 3 # 22112012 # 1311 # 000000000785
        $buffer = fgets($handle, 4096);
        if(strlen($buffer)==40 and $linha > 3682){
            echo($buffer);
            
            $comprovante=substr($buffer, 0, 9);  
            //$batida=substr($registro, 9, 1);
            $strdate=substr($buffer, 10,8);
            $newdate = substr($strdate,4,4)."-".substr($strdate,2,2)."-".substr($strdate,0,2);
            $strhora=substr($buffer, 18,4);
            $newhora=substr($strhora,0,2).":".substr($strhora,2,2).":00";
            $vidpessoa=substr($buffer, 23,11);
            
            echo("<br>");
            echo("COMPROVANTE:".$comprovante." DATA:".$newdate." HORA:".$newhora." PIS:".$vidpessoa);
            echo("<br>");
            
        }
    
    }//while (!feof($handle))
                        //die('FIM');
    fclose($handle);
}else{
    echo "Problema ao abrir arquivo!";
}//$handle

