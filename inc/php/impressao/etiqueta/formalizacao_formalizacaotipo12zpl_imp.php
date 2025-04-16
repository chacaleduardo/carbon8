<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row=EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo12( $_OBJ["idlote"] );
if(!empty($row)){

    $altura = 23;
    $_CONTEUDOIMPRESSAO.='^XA^CF0,20';
    if(strlen($row['nomeinicio']) > 22){

        $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FD'.strtoupper(retira_acentos(substr($row['nomeinicio'],0,30))).'^FS';
        $altura += 25;
        $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FD'.strtoupper(retira_acentos(substr($row['nomeinicio'],30))).'^FS';

    }else{
        $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FD'.strtoupper(retira_acentos($row['nomeinicio'])).'^FS';
    }

    $altura += 30;
    
    $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FD'.retira_acentos($row['partida']).'^FS';

    $altura += 30;

    $dataSomada2 = strtotime('+6 month'); // Exemplo de saída: 1570116272
    $vencimento = date('M/Y', $dataSomada2);
    $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FDFAB:'.traduzMes(date('M/Y')).'^FS';

    $altura += 30;
    $_CONTEUDOIMPRESSAO.='^FO10,'.$altura.'^FDVENC:'.traduzMes($vencimento).'^FS^XZ';
}

function traduzMes($mesano)
{
	$mesanoex = explode('/',$mesano);
	switch($mesanoex[0])
	{
		case "Jan": $month = "JAN"; break;
		case "Feb": $month = "FEV"; break;
		case "Mar": $month = "MAR"; break;
		case "Apr": $month = "ABR"; break;
		case "May": $month = "MAI"; break;
		case "Jun": $month = "JUN"; break;
		case "Jul": $month = "JUL"; break;
		case "Aug": $month = "AGO"; break;
		case "Sep": $month = "SET"; break;
		case "Oct": $month = "OUT"; break;
		case "Nov": $month = "NOV"; break;
		case "Dec": $month = "DEZ"; break;
	}
	return $month.'/'.$mesanoex[1];
}
?>