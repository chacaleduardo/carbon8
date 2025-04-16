
<?php

//Exemplo do objeto arrpostbuffer $_SESSION['arrpostbuffer']["1"]["i"]["nometabela"]["nomecampo"]){

$pastaEventosModulo =_CARBON_ROOT."eventcode/modulo/";
$pastaEventosSearch =_CARBON_ROOT."eventcode/modulofiltrospesquisa/";

if(!file_exists($pastaEventosModulo)){
    die("saveprechange: Pasta de eventos para Modulos [".$pastaEventosModulo."] nao existe");    

}elseif(!is_writable($pastaEventosModulo)){
       // die("saveprechange: Pasta de eventos para Modulos [".$pastaEventosModulo."] nao possui permissoes de escrita");    
}

if(!file_exists($pastaEventosSearch)){
    die("saveprechange: Pasta de eventos para Search [".$pastaEventosSearch."] nao existe");    

}elseif(!is_writable($pastaEventosSearch)){
        //die("saveprechange: Pasta de eventos para Search [".$pastaEventosSearch."] nao possui permissoes de escrita");    
}

//print_r($_SESSION["arrpostbuffer"]["1"]);

//Inicia o loop e para apos a primeira iteracao, somente para capturar a ACAO enviada e abrir a variavel acaotmp
foreach($_SESSION["arrpostbuffer"]["1"] as $acaotmp=>$foo) break;


/*
 * Verifica cada evento na tela, e de acordo com o preenchimento, cria/substitui ou deleta o arquivo
 */
//SAVE PRE CHANGE
$nomearq="";
$nomearq = $pastaEventosModulo . "saveprechange__". $_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["modulo"];
if(!empty($_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_saveprechange"])){
	//cria ou substitui o arquivo
	file_put_contents($nomearq.".php", $_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_saveprechange"]);
	//altera as permissoes
	chmod($nomearq.".php", 0750);
}else{
	if(isset($_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_saveprechange"])){
		//a funcao glob procura por um arquivo utilizando wildcards (curingas).
		//Isto eh necessario, visto que caso o usuario retire o valor existente no campo [prompt] neste ponto nao se sabe exatamente o valor contido nele, portanto sera efetuado um loop com o nome da coluna para encontrar o arquivo
		$resun = $nomearq."*";
		foreach (glob($resun) as $arqtmp) {
			if(filesize($arqtmp)>0){
				unlink($arqtmp);
				//echo $arqtmp;
			}
		}
	}
}

//SAVE POS CHANGE
$nomearq="";
$nomearq = $pastaEventosModulo . "saveposchange__". $_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["modulo"];
if(!empty($_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_saveposchange"])){
	//cria ou substitui o arquivo
	file_put_contents($nomearq.".php", $_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_saveposchange"]);
	//altera as permissoes
	chmod($nomearq.".php", 0750);
}else{
	if(isset($_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_saveposchange"])){
		//a funcao glob procura por um arquivo utilizando wildcards (curingas).
		//Isto eh necessario, visto que caso o usuario retire o valor existente no campo [prompt] neste ponto nao se sabe exatamente o valor contido nele, portanto sera efetuado um loop com o nome da coluna para encontrar o arquivo
		$resun = $nomearq."*";
		foreach (glob($resun) as $arqtmp) {
			if(filesize($arqtmp)>0){
				unlink($arqtmp);
				//echo $arqtmp;
			}
		}
	}
}

//PRE SEARCH
$nomearq="";
$nomearq = $pastaEventosSearch . "presearchexec__". $_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["modulo"];
if(!empty($_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_presearch"])){
	//cria ou substitui o arquivo
	file_put_contents($nomearq.".php", $_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_presearch"]);
	//altera as permissoes
	chmod($nomearq.".php", 0750);
}else{
	if(isset($_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_presearch"])){
		//a funcao glob procura por um arquivo utilizando wildcards (curingas).
		//Isto eh necessario, visto que caso o usuario retire o valor existente no campo [prompt] neste ponto nao se sabe exatamente o valor contido nele, portanto sera efetuado um loop com o nome da coluna para encontrar o arquivo
		$resun = $nomearq."*";
		foreach (glob($resun) as $arqtmp) {
			if(filesize($arqtmp)>0){
				unlink($arqtmp);
				//echo $arqtmp;
			}
		}
	}
}

//POS SEARCH
$nomearq="";
$nomearq = $pastaEventosSearch . "possearchexec__". $_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["modulo"];
if(!empty($_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_possearch"])){
	//cria ou substitui o arquivo
	file_put_contents($nomearq.".php", $_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_possearch"]);
	//altera as permissoes
	chmod($nomearq.".php", 0750);
}else{
	if(isset($_SESSION["arrpostbuffer"]["1"][$acaotmp]["_modulo"]["evento_possearch"])){
		//a funcao glob procura por um arquivo utilizando wildcards (curingas).
		//Isto eh necessario, visto que caso o usuario retire o valor existente no campo [prompt] neste ponto nao se sabe exatamente o valor contido nele, portanto sera efetuado um loop com o nome da coluna para encontrar o arquivo
		$resun = $nomearq."*";
		foreach (glob($resun) as $arqtmp) {
			if(filesize($arqtmp)>0){
				unlink($arqtmp);
				//echo $arqtmp;
			}
		}
	}
}

$modulo=$_POST['_1_u__modulo_modulo'];
$status=$_POST['_1_u__modulo_status'];



if(!empty($modulo) and $status=='INATIVO'){

	$mp= _moduloController::buscarModparAtivo($modulo);

	if($mp['existe']>0){
		die('Não é possivel inativar o modulo pois este possui modulo filho ativo, segue lista:<br>'.$mp['modulos']);
	}
}

$gerandohistorico = $_POST['_h1_i_modulohistorico_idobjeto'];
if (!empty($gerandohistorico)) 
{
    $campo = $_POST['_h1_i_modulohistorico_campo'];
    $valor = $_POST['_h1_i_modulohistorico_valor'];
    $tabela = $_POST['_h1_i_modulohistorico_tipoobjeto'];
    $_id = $_POST['_h1_i_modulohistorico_idobjeto'];

    $_SESSION['arrpostbuffer']['parc']['u'][$tabela]['idmodulo'] = $_id;
    $_SESSION['arrpostbuffer']['parc']['u'][$tabela][$campo] = $valor;

	montatabdef();
}

//if(!empty($_SESSION['arrpostbuffer']['1'][''])	echo $campos;

?>
