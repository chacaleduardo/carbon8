<?
$partida    = $_OBJ["partida"];
$exercicio  = $_OBJ["exercicio"];
$idlote     = $_OBJ["idlote"];
$modulo     = $_OBJ["modulo"];
$tamanho = strlen($partida);

if($tamanho > 5){
	if($tamanho > 9){
		$_CONTEUDOIMPRESSAO.="^XA^CF0,30"; 
		$_CONTEUDOIMPRESSAO.='^FO10,20 
					^BQN,2,3
					^FDQA,https://sislaudo.laudolab.com.br/?_modulo='.$modulo.'&_acao=u&idlote='.$idlote.'^FS
					^FX';
		$_CONTEUDOIMPRESSAO.='^FT440,50^FH\^FD'.$partida.'^FS';
		$_CONTEUDOIMPRESSAO.='^FT500,80^FH\^FD'.'/'.$exercicio.'^FS';
		$_CONTEUDOIMPRESSAO.='^FT170,50^FH\^FD'.$partida.'^FS';
		$_CONTEUDOIMPRESSAO.='^FT230,80^FH\^FD'.'/'.$exercicio.'^FS';
		$_CONTEUDOIMPRESSAO.="^PQ1,0,1,Y";
		$_CONTEUDOIMPRESSAO.="^XZ";
	}else{
		$_CONTEUDOIMPRESSAO.="^XA^CF0,30"; 
		$_CONTEUDOIMPRESSAO.='^FO10,20 
					^BQN,2,3
					^FDQA,https://sislaudo.laudolab.com.br/?_modulo='.$modulo.'&_acao=u&idlote='.$idlote.'^FS
					^FX';
		$_CONTEUDOIMPRESSAO.='^FT470,50^FH\^FD'.$partida.'^FS';
		$_CONTEUDOIMPRESSAO.='^FT500,80^FH\^FD'.'/'.$exercicio.'^FS';
		$_CONTEUDOIMPRESSAO.='^FT200,50^FH\^FD'.$partida.'^FS';
		$_CONTEUDOIMPRESSAO.='^FT230,80^FH\^FD'.'/'.$exercicio.'^FS';
		$_CONTEUDOIMPRESSAO.="^PQ1,0,1,Y";
		$_CONTEUDOIMPRESSAO.="^XZ";
	}
}else{
	$_CONTEUDOIMPRESSAO.="^XA^CF0,40"; 
	$_CONTEUDOIMPRESSAO.='^FT440,70^FH\^FD'.$partida.'/'.$exercicio.'^FS';
	$_CONTEUDOIMPRESSAO.='^FT160,70^FH\^FD'.$partida.'/'.$exercicio.'^FS';
	$_CONTEUDOIMPRESSAO.="^PQ1,0,1,Y";
	$_CONTEUDOIMPRESSAO.="^XZ";
}
?>