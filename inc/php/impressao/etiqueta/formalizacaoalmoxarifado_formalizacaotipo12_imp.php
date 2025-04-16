<?
require_once(__DIR__."/../../../../form/controllers/etiqueta_controller.php");

$row=EtiquetaController::buscarInfosEtiquetaFormalizacaoTipo12( $_OBJ["idlote"] );
if(!empty($row)){

    $_CONTEUDOIMPRESSAO .= EtiquetaController::$cabecalhoTSPL40x20;
    $altura = 10;

    if(strlen($row['nomeinicio']) > 22){
        $_CONTEUDOIMPRESSAO.='
            TEXT 8,'.$altura.',"2",0,1,1,"'.retira_acentos(substr($row['nomeinicio'],0,22)).'"';
        $altura += 20;
        $_CONTEUDOIMPRESSAO.='
            TEXT 8,'.$altura.',"2",0,1,1,"'.retira_acentos(substr($row['nomeinicio'],22)).'"';
    }else{
        $_CONTEUDOIMPRESSAO.='
            TEXT 8,'.$altura.',"2",0,1,1,"'.retira_acentos($row['nomeinicio']).'"';
    }

    $altura += 30;
    
    $_CONTEUDOIMPRESSAO.='
        TEXT 8,'.$altura.',"2",0,1,1,"'.retira_acentos($row['partida']).'"';
    $altura += 30;

    $dataSomada2 = strtotime('+6 month'); // Exemplo de saída: 1570116272
    $vencimento = date('M/Y', $dataSomada2);
    $_CONTEUDOIMPRESSAO.='
        TEXT 8,'.$altura.',"2",0,1,1,"'.'FAB:'.traduzMes(date('M/Y')).'"';
    $altura += 30;
    $_CONTEUDOIMPRESSAO.='
        TEXT 8,'.$altura.',"2",0,1,1,"'.'VENC:'.traduzMes($vencimento).'"';
    
    $_CONTEUDOIMPRESSAO.="
    PRINT 1
            ";

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