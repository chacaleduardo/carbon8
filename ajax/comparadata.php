<?require_once("../inc/php/functions.php");

$data1= $_GET['data1']; 
$data2= $_GET['data2'];




if(empty($data1) ){
    die("Preencher início da atividade anterior.");
}
if( empty($data2)){
    die("Preencher início da atividade.");
}

	$inInicio  = validadate($data1);
        $inFim = validadate($data2);

if(strtotime($inInicio) > strtotime($inFim)){
   echo('true');
}else{
    echo('false');
}
    

		