<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
 
if(AmostraController::contarResultadosAssinados($_SESSION["arrpostbuffer"]["1"]["u"]["amostra"]['idamostra']) >= 1) {
	if(cb::idempresa() == 1){
		die("Não é possível salvar a amostra, há resultados assinados");
	}
}
//print_r($_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]);die;

/*
 * MAF29062011 : Considerar o Id da Empresa para gerar IDs de Registro Separados: 
 *					and idempresa = ".$_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idempresa"]
 */

//print_r($_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]);

if(array_key_exists("i", $_SESSION["arrpostbuffer"]["1"]) and
	empty($_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["exercicio"]) and
	empty($_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idregistro"]) and 
	empty($_SESSION["arrpostbuffer"]["1"]["u"]["amostra"]["exercicio"]) and
	empty($_SESSION["arrpostbuffer"]["1"]["u"]["amostra"]["idregistro"])){
	
	//conectabanco();
	
	$idunidade = $_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idunidade"];
	
	if(empty($idunidade)){
		die("Não foi possivel identificar a Unidade para gerar o Registro!!!");
	}
	
	d::b()->query("START TRANSACTION;");
	
	$status = $_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["status"];
	
	if($status=='PROVISORIO'){
		$exercicio = date('Y').'PROVISORIO';
	} else {
		$exercicio = date('Y');
	}
	
	
	//Função para Atualizar e Inserir o próximo registra
	$rowexercicio = geraIdregistro(cb::idempresa(), $idunidade, $exercicio);
	
	//se o idnucleo vier vazio o valor do mesmo e informado como 0 (zero) para atender a questoes de relatorios(OUTROS) do site
	if(empty($_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idnucleo"])){
		$_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idnucleo"]=0;
	}
	$_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["exercicio"] = $rowexercicio["exercicio"];
	$_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idregistro"] = $rowexercicio["idregistro"];
	
	$_SESSION["post"]["_1_u_amostra_exercicio"] = $rowexercicio["exercicio"];
	$_SESSION["post"]["_1_u_amostra_idregistro"] = $rowexercicio["idregistro"];
}
//print_r($_SESSION["post"]); die("fim");


// SE ESTIVER ASSSINADA A AMOSTRA TRA ENQUANTO A TELA DO USUÁRIO ESTIVER ABERTA COM STATUS DEVOLVIDO NÃO SALVAR COM STATUS DEVOLVIDO E SIM ASSINADO;
$_status=$_SESSION["arrpostbuffer"]["2"]["u"]["amostra"]["status"];
$_idamostra=$_SESSION["arrpostbuffer"]["2"]["u"]["amostra"]["idamostra"];

if(!empty($_idamostra) and $_status=='DEVOLVIDO'){
    $qtdas= AmostraController::verificarSeAmostraFoiAssinada($_idamostra,cb::idempresa());
    if($qtdas>0){
        $_SESSION["arrpostbuffer"]["2"]["u"]["amostra"]["status"]=="ASSINADO"; 

		$idamostra = $_SESSION["arrpostbuffer"]["x"]["u"]["amostra"]["idamostra"];

		//LTM (13-04-2021): Atualiza o Status do Resultado (tabela, pk, valorpk, $statustipo, $modulotipo, $_primaryhist, status)
		$rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'ASSINADO', 'amostra', '', '');
		FluxoController::restaurarFluxo($rowFluxo['modulo'], 'idamostra', $idamostra, $rowFluxo['statustipo'], $rowFluxo['idfluxostatus']);
    }
}

//gerar os identificadores da amostra
if(!empty($_SESSION["arrpostbuffer"]["x"]["i"]["identificador"]["idobjeto"]) and ($_POST['qtdidentificador']>0)){
    $qtd=$_POST['qtdidentificador'];
    $idamostra=$_SESSION["arrpostbuffer"]["x"]["i"]["identificador"]["idobjeto"];
    $tipoobjeto=$_SESSION["arrpostbuffer"]["x"]["i"]["identificador"]["tipoobjeto"];
    
    for ($i = 2; $i <= $qtd; $i++) {
       // echo $i;
        $_SESSION["arrpostbuffer"][$i]["i"]["identificador"]["idobjeto"]=$idamostra;
        $_SESSION["arrpostbuffer"][$i]["i"]["identificador"]["tipoobjeto"]=$tipoobjeto;
    }
    montatabdef();
}

//LTM (30-07-2021) - Verifica se já tem o idregistroprovisório salva para não deixar criar novos registros

$rowAmostra = traduzid('amostra','idamostra','idregistroprovisorio',$_SESSION["arrpostbuffer"]["1"]["u"]["amostra"]["idamostra"]);

//LTM (30-07-2021): Transfere a AmostraTEA para AmostraTRA
if($_SESSION['arrpostbuffer']['1']['u']['amostra']['status'] == 'ABERTO' && $_SESSION['arrpostbuffer']['1']['u']['amostra']['idunidade'] == 9 && empty($rowAmostra))
{	
	//Função para Atualizar e Inserir o próximo registra
	$exercicio = date('Y');
	$rowexercicio = geraIdregistro(cb::idempresa(), $_SESSION['arrpostbuffer']['1']['u']['amostra']['idunidade']);
	
	//Salvar o Idregistro Provisório na tabela Amostra - Lidiane (17-06-2020)
	$idregistro = traduzid("amostra","idamostra","idregistro", $_SESSION["arrpostbuffer"]['1']['u']['amostra']['idamostra']);
	$_SESSION["arrpostbuffer"]['1']['u']["amostra"]["idregistroprovisorio"] = $idregistro;

	//se o idnucleo vier vazio o valor do mesmo e informado como 0 (zero) para atender a questoes de relatorios(OUTROS) do site
	$_SESSION["arrpostbuffer"]['1']['u']["amostra"]["exercicio"] = $rowexercicio["exercicio"];
	$_SESSION["arrpostbuffer"]['1']['u']["amostra"]["idregistro"] = $rowexercicio["idregistro"];
}


if($_GET['_acao']=='i'){	
	unset($_SESSION["arrpostbuffer"]['d1']);
}


if($_GET['_acao']=='u' && empty($_POST['_d1_u_dadosamostra_iddadosamostra'])){	
	unset($_SESSION["arrpostbuffer"]['d1']["u"]);
}


?>