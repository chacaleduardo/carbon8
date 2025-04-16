<?
$partida    = $_OBJ["partida"];
$exercicio  = $_OBJ["exercicio"];
$idlote     = $_OBJ["idlote"];
$modulo     = $_OBJ["modulo"];
$produto    = $_OBJ["produto"];
$vencimento = $_OBJ["vencimento"];
$fabricacao = $_OBJ["fabricacao"];
$tamanho = strlen($produto);

$_CONTEUDOIMPRESSAO = "^XA";

// if($tamanho > 5){
// 	if($tamanho > 9){
// 		// $_CONTEUDOIMPRESSAO.="^XA^CF0,30"; 
// 		// $_CONTEUDOIMPRESSAO.='^FO10,20 
// 		// 			^BQN,2,3
// 		// 			^FDQA,https://sislaudo.laudolab.com.br/?_modulo='.$modulo.'&_acao=u&idlote='.$idlote.'^FS
// 		// 			^FX';
// 		// $_CONTEUDOIMPRESSAO.='^FT440,50^FH\^FD'.$partida.'^FS';
// 		// $_CONTEUDOIMPRESSAO.='^FT500,80^FH\^FD'.'/'.$exercicio.'^FS';
// 		// $_CONTEUDOIMPRESSAO.='^FT170,50^FH\^FD'.$partida.'^FS';
// 		// $_CONTEUDOIMPRESSAO.='^FT230,80^FH\^FD'.'/'.$exercicio.'^FS';
// 		// $_CONTEUDOIMPRESSAO.="^PQ1,0,1,Y";
// 		// $_CONTEUDOIMPRESSAO.="^XZ";
// 	}else{
// 		// $_CONTEUDOIMPRESSAO.="^XA^CF0,30"; 
// 		// $_CONTEUDOIMPRESSAO.='^FO10,20 
// 		// 			^BQN,2,3
// 		// 			^FDQA,https://sislaudo.laudolab.com.br/?_modulo='.$modulo.'&_acao=u&idlote='.$idlote.'^FS
// 		// 			^FX';
// 		// $_CONTEUDOIMPRESSAO.='^FT470,50^FH\^FD'.$partida.'^FS';
// 		// $_CONTEUDOIMPRESSAO.='^FT500,80^FH\^FD'.'/'.$exercicio.'^FS';
// 		// $_CONTEUDOIMPRESSAO.='^FT200,50^FH\^FD'.$partida.'^FS';
// 		// $_CONTEUDOIMPRESSAO.='^FT230,80^FH\^FD'.'/'.$exercicio.'^FS';
// 		// $_CONTEUDOIMPRESSAO.="^PQ1,0,1,Y";
// 		// $_CONTEUDOIMPRESSAO.="^XZ";
// 	}
// }else{
// 	$_CONTEUDOIMPRESSAO.="^XA^CF0,40"; 
// 	$_CONTEUDOIMPRESSAO.='^FT440,70^FH\^FD'.$partida.'/'.$exercicio.'^FS';
// 	$_CONTEUDOIMPRESSAO.='^FT160,70^FH\^FD'.$partida.'/'.$exercicio.'^FS';
// 	$_CONTEUDOIMPRESSAO.="^PQ1,0,1,Y";
// 	// $_CONTEUDOIMPRESSAO.="^XZ";
// }
	// $_CONTEUDOIMPRESSAO.="^XA^CF0,30"; 
	// $_CONTEUDOIMPRESSAO.='^FO10,20 
	// 			^BQN,2,3
	// 			^FDQA,https://sislaudo.laudolab.com.br/?_modulo='.$modulo.'&_acao=u&idlote='.$idlote.'^FS
	// 			^FX';
	// $_CONTEUDOIMPRESSAO.='^FT470,50^FH\^FD'.$partida.'^FS';
	// $_CONTEUDOIMPRESSAO.='^FT500,80^FH\^FD'.'/'.$exercicio.'^FS';
	// $_CONTEUDOIMPRESSAO.='^FT200,50^FH\^FD'.$partida.'^FS';
	// $_CONTEUDOIMPRESSAO.='^FT230,80^FH\^FD'.'/'.$exercicio.'^FS';
	// $_CONTEUDOIMPRESSAO.="^PQ1,0,1,Y";
	// $_CONTEUDOIMPRESSAO.="^XZ";
	$_CONTEUDOIMPRESSAO .= "^CF0,17";
	$_CONTEUDOIMPRESSAO .= "^FO5,2";
	$_CONTEUDOIMPRESSAO .= "^BQN,2,3";
	$_CONTEUDOIMPRESSAO .= "^FDQA,https://sislaudo.laudolab.com.br/?_modulo=$modulo&_acao=u&idlote=$idlote";
	$_CONTEUDOIMPRESSAO .= "^FS";
	$_CONTEUDOIMPRESSAO .= "^FX";
	$_CONTEUDOIMPRESSAO .= "^FT145,28";
	$_CONTEUDOIMPRESSAO .= "^FD$partida/$exercicio ^FS";
	$_CONTEUDOIMPRESSAO .= "^FT145,50";
	$_CONTEUDOIMPRESSAO .= "^FDF: $fabricacao ^FS";
	$_CONTEUDOIMPRESSAO .= "^FT145,72";
	$_CONTEUDOIMPRESSAO .= "^FDV: $vencimento ^FS";
	$_CONTEUDOIMPRESSAO .= "^CF0,15";
	$_CONTEUDOIMPRESSAO .= "^FT145,120";
	$_CONTEUDOIMPRESSAO .= "^FB290,2";
	// $_CONTEUDOIMPRESSAO .= "^A@N,7,7";
	$_CONTEUDOIMPRESSAO .= "^FD".substr($produto,0,22)."^FS";
	$_CONTEUDOIMPRESSAO .= "^FT145,135";
	$_CONTEUDOIMPRESSAO .= "^FB290,2";
	// $_CONTEUDOIMPRESSAO .= "^A@N,7,7";
	$_CONTEUDOIMPRESSAO .= "^FD".substr($produto,22,44)."^FS";
	$_CONTEUDOIMPRESSAO .= "^FT145,150";
	$_CONTEUDOIMPRESSAO .= "^FB290,2";
	// $_CONTEUDOIMPRESSAO .= "^A@N,7,7";
	$_CONTEUDOIMPRESSAO .= "^FD".substr($produto,44)."^FS";


$_CONTEUDOIMPRESSAO .= "^XZ";
?>