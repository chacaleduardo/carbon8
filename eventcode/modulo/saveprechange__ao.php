<?
// CRIACAO DO AGENTE
//abre variavel com a acao que veio da tela
$iu = $_SESSION['arrpostbuffer']['x']['i']['lote']['idprodserv'] ? 'i' : 'u';

//se for um insert, o prodserv tiver sido informado e o lote estiver vazio
if($iu == "i" and (!empty($_SESSION["arrpostbuffer"]['x']["i"]["lote"]["idprodserv"]) and empty($_SESSION["arrpostbuffer"]['x']["i"]["lote"]["partida"]))){

	$_idprodserv = $_SESSION["arrpostbuffer"]['x']["i"]["lote"]["idprodserv"];

	$_arrlote = geraLote($_idprodserv,'loteao');

	if(strlen($_arrlote[0])==0 or strlen($_arrlote[1])==0){
		die("Falha na geração da Partida (sequence). [".$_arrlote[0]."][".$_arrlote[1]."]");
	}else{
		$_numlote = $_arrlote[0].$_arrlote[1];

		//Enviar o campo para a pagina de submit
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["partida"] = $_numlote;
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["spartida"] = $_arrlote[0];
		$_SESSION["arrpostbuffer"]['x']["i"]["lote"]["npartida"] = $_arrlote[1];

		//Atribuir o valor para retorno por session['post'] ah pagina anterior. OBS: o decode eh necessario porque o PHP pode forcar automaticamente caracteres diferentes
		$_SESSION["post"]["_x_u_lote_partida"] = $_numlote;		
		
		d::b()->query("COMMIT") or die("seqmeiolote: Falha ao efetuar COMMIT [sequence]: ".mysqli_error());
	}
}


?>