<?
require_once("../model/evento.php");
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

//print_r($_POST);//die;
//rint_r($_SESSION["arrpostbuffer"]);die;

//while (list($chave, $vlr) = each($arrpilha)) {

$arrInsTestes=array();

foreach($_POST as $k=>$v) {
	if(preg_match("/_(\d*)#(.*)/", $k, $res)){
		$arrInsTestes[$res[1]][$res[2]]=$v;
	}
}

//print_r($arrInsTestes); die;

foreach($arrInsTestes as $k=>$v){
	if(empty($v["identificacao"])){
		//VAlidado para inserir N quando o campo vier vazio - Lidiane (26/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=328394
		if(empty($v["cobrar"])){
			$v["cobrar"]='N';
		}

		//LTM (08-04-2021): Retorna o idfluxostatus de acordo com cada módulo.
		$idamostra = $_SESSION["_pkid"];	
		$rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'ABERTO', 'resultado', '', '');	

		$res = AmostraController::criarTestesDaAmostraERetorna(cb::idempresa(),$idamostra,($v["idtipoteste"]?:"null"),($v["quantidade"]?:"0"),($v["idsecretaria"]?:"null"),($v["loteetiqueta"]?:"0"),($v["npedido"]?:"null"),($v["ord"]?:"0"),$rowFluxo['idfluxostatus'],$_SESSION["SESSAO"]["USUARIO"],($v["cobrar"]));

		if(empty($res)){
			echo("Erro inserindo teste: ");
		} else {
			//LTM (08-04-2021): Retorna o idfluxostatus de acordo com cada módulo.
			FluxoController::inserirFluxoStatusHist($rowFluxo['modulo'], $res['idresultado'], $rowFluxo['idfluxostatus'], 'PENDENTE');
			$vinculados = AmostraController::buscarProdservVinculada($idamostra,$v["idtipoteste"]);
			$contvinculo = count($vinculados);
			if($contvinculo > 0)
			{
				$i=0;
			$vinculados = AmostraController::buscarProdservVinculada($idamostra,$v["idtipoteste"]);
				foreach($vinculados as $k => $row) 
				{
					$res1 = AmostraController::criarTestesDaAmostraERetorna(cb::idempresa(),
							$idamostra,
							($row["idobjeto"]?:"null"),
							1,
							0,
							($v["loteetiqueta"]?:"0"),
							"null",
							0,
							$rowFluxo['idfluxostatus'],
							$_SESSION["SESSAO"]["USUARIO"],
							"N");


					FluxoController::inserirFluxoStatusHist($rowFluxo['modulo'], $newnIdResultado, $rowFluxo['idfluxostatus'], 'PENDENTE');
				}

				cbSetCustomHeader('vinculados',json_encode($vinculados));
			}
		}
	}elseif(!empty($v["identificacao"])){
		$res2 = AmostraController::inserirIndetificador(cb::idempresa(),$_SESSION["_pkid"],($v["identificacao"]?:"null"),$_SESSION["SESSAO"]["USUARIO"]);
		if(!$res2){
			echo("Erro inserindo identificacao: ");
		}
	}
}

if(!empty($_SESSION['arrpostbuffer']['1']['u']['amostra']['idamostra']) and $_SESSION['arrpostbuffer']['1']['u']['amostra']['idunidade']!=6 and $_SESSION['arrpostbuffer']['1']['u']['amostra']['idunidade']!=9 and $_SESSION['arrpostbuffer']['1']['u']['amostra']['status']!='FECHADO'){
    $idamostra = $_SESSION['arrpostbuffer']['1']['u']['amostra']['idamostra'];

	//LTM (08-04-2021): Retorna as informações do módulo de acordo com o tipo de unidade (modulo)
	$rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'FECHADO', 'amostra', '', 'ASSINADO');	
	if(!empty($rowFluxo['modulo'])){
		FluxoController::restaurarFluxo($rowFluxo['modulo'], 'idamostra', $rowFluxo["idamostra"], $rowFluxo['statustipo'], $rowFluxo['idfluxostatus']);
	}

	$idfluxostatus = FluxoController::getIdFluxoStatus('resultaves','FECHADO');

    $resr = AmostraController::buscarResultadosAssinadosDaAmostra($idamostra);       
    $res = AmostraController::fecharResultadosAssinados($idamostra,$idfluxostatus);
    if(!$res){
        echo("[saveposchange__amostraaves]-Erro ao atualizar resultados assinados para fechado");
    }else{
        $res = AmostraController::deletarResultadoAssinaturaPorIdamostra($idamostra); 
        
        foreach($resr as $k => $rowr){    
            $resaud = AmostraController::inserirRegistroAuditoria(cb::idempresa(),'1','u','resultado',$rowr['idresultado'],'status','FECHADO',$_SESSION["SESSAO"]["USUARIO"],$_SERVER["HTTP_REFERER"]);
        }
    }
}

if($_SESSION["arrpostbuffer"]["x"]["u"]["amostra"]["status"] == 'ABERTO')
{
	$idamostra = $_SESSION["arrpostbuffer"]["x"]["u"]["amostra"]["idamostra"];
	$motivo = $_POST['_fluxostatushistobs_motivo_'];
	$obs = $_POST['_fluxostatushistobs_motivoobs_'];
	//LTM (08-04-2021): Retorna as informações do módulo de acordo com o tipo de unidade (modulo)
	$rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'ABERTO', 'amostra', '', '');
	if(!empty($rowFluxo['modulo'])){	
		FluxoController::restaurarFluxo($rowFluxo['modulo'], 'idamostra', $idamostra, $rowFluxo['statustipo'], $rowFluxo['idfluxostatus'],$motivo,$obs);
	}

	$idfluxostatus = FluxoController::getIdFluxoStatus('resultaves','FECHADO');

	//LTM (08-04-2021): Retorna as informações do módulo de acordo com o tipo de unidade (modulo)
    $resr = AmostraController::buscarResultadosAssinadosDaAmostra($idamostra);       
    $res = AmostraController::fecharResultadosAssinados($idamostra,$idfluxostatus);
    if(!$res){
        echo("[saveposchange__amostraaves]-Erro ao atualizar resultados assinados para fechado");
    }else{
        $res = AmostraController::deletarResultadoAssinaturaPorIdamostra($idamostra); 
        
        foreach($resr as $k => $rowr){    
            $resaud = AmostraController::inserirRegistroAuditoria(cb::idempresa(),'1','u','resultado',$rowr['idresultado'],'status','FECHADO',$_SESSION["SESSAO"]["USUARIO"],$_SERVER["HTTP_REFERER"]);
            AmostraController::inserirComentarioDeRestauracaoResultado(cb::idempresa(),$rowr['idresultado'],'resultaves',$motivo,$obs,'FECHADO',$idfluxostatus,$rowr['versao']+1,$rowr['versao'],$_SESSION['SESSAO']['USUARIO'],$_SESSION['SESSAO']['USUARIO']);
        }
    }
}


if($_SESSION['arrpostbuffer']['1']['u']['amostra']['idunidade'] == 9 and $_SESSION['arrpostbuffer']['2']['u']['amostra']['status'] == 'ASSINADO'){
	$idamostra = $_SESSION['arrpostbuffer']['1']['u']['amostra']['idamostra'];
	
	
	$res = AmostraController::solicitarAssinaturaAmostra($idamostra);
	if(!$res){
        echo("[saveposchange__amostraaves]-Erro ao atualizar amostra ASSINADO para ASSINAR : ".mysqli_error(d::b()));
    }

	$idamostra = $_SESSION["arrpostbuffer"]["x"]["u"]["amostra"]["idamostra"];

	//LTM (08-04-2021): Retorna as informações do módulo de acordo com o tipo de unidade (modulo)
	$rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, 'ASSINAR', 'amostra', '', '');	
	if(!empty($rowFluxo['modulo'])){
		FluxoController::restaurarFluxo($rowFluxo['modulo'], 'idamostra', $idamostra, $rowFluxo['statustipo'], $rowFluxo['idfluxostatus']);
	}
}

//LTM (23/04/2021): Insere o nome do Representante 
//Verifica se tem pessoa inserida para assinatura
$idamostra = $_SESSION["_pkid"];
$resCarrimbo = AmostraController::verificarAssinaturaAmostra($_SESSION['arrpostbuffer']['1']['u']['amostra']['idamostra'],$_GET["_modulo"]); 
$rowCarrimbo = $resCarrimbo[0]; 
$total = count($resCarrimbo);

$iu = $_SESSION['arrpostbuffer']['1']['i']['amostra']['idpessoaresponsavel'] ? 'i' : 'u';
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavel']))
{
	updateAmostra($idamostra, 'responsavel', getNomePessoa($_SESSION['arrpostbuffer']['1']['u']['amostra']['idpessoaresponsavel']));        

	if($rowCarrimbo['idpessoa'] != $_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavel']){
		AmostraController::deletarAssinaturasPendentesAmostra($idamostra,882,'idfluxostatus');
	}

	if($rowCarrimbo['idpessoa'] != $_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavel'] || $total == 0)
	{
	    
    	$data = explode("/", $_SESSION['arrpostbuffer']['1'][$iu]['amostra']['datacoleta']);
    	$data = $data[2]."-".$data[1]."-".$data[0]." ".date('H:i:s');
		AmostraController::inserirAssinaturaAmostra(cb::idempresa(),$_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavel'],$idamostra,$_GET["_modulo"],'882','idfluxostatus','PENDENTE',$_SESSION['SESSAO']['USUARIO'],$data,$_SESSION['SESSAO']['USUARIO'],$data);
	}	
}

//Seta o Nome do Responsável Oficial
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelof']))
{  
	updateAmostra($idamostra, 'responsavelof', getNomePessoa($_SESSION['arrpostbuffer']['1']['u']['amostra']['idpessoaresponsavelof']));    
}

//Seta o responsável Coleta CRMV
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelcrmv']))
{
	updateAmostra($idamostra, 'responsavelcolcrmv', getCRMV($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelcrmv']));
}

//Seta o responsável Oficial CRMV
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelcrmvof']))
{
	updateAmostra($idamostra, 'responsavelofcrmv', getCRMV($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['idpessoaresponsavelcrmvof']));
}

function getCRMV($idpessoacrmv){
	$row = AmostraController::buscarCRMV($idpessoacrmv);
	return $row['crmv'];
}

function getNomePessoa($idpessoa)
{
	$row = AmostraController::buscarNomePessoa($idpessoa);  
	return $row['nome'];
}

function updateAmostra($idamostra, $campo, $setCampo)
{  
    AmostraController::atualizarCampoDaAmostra($idamostra,$campo,$setCampo); 
}

//Altera a Data do Carrimbo de acordo com a data 
if(!empty($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['datacoleta']) && dma($_SESSION['arrpostbuffer']['1'][$iu]['amostra']['datacoleta']) != dma($row['alteradoem']) && !empty($row['idcarrimbo']))
{
	date_default_timezone_set('America/Sao_Paulo');
	$data = explode("/", $_SESSION['arrpostbuffer']['1'][$iu]['amostra']['datacoleta']);
	$data = $data[2]."-".$data[1]."-".$data[0]." ".date('H:i:s');
	AmostraController::alterarDataAssinatura($row['idcarrimbo'],$data);
}


if($_GET['_acao']=='i' && !empty($_POST['_d1_i_dadosamostra_valorobjeto'])){
    AmostraController::inserirDadosAmostra(cb::idempresa(),$_SESSION['_pkid'],'temperaturarecebimento',$_POST['_d1_i_dadosamostra_valorobjeto'],$_SESSION['SESSAO']['USUARIO']); 

}

if(!empty($_SESSION["arrpostbuffer"]["x"]["i"]["identificador"]["idobjeto"]) && ($_POST['qtdidentificador']>0)){
	header_remove("X-CB-PKID");
	$_SESSION["headergetretorno"] = false;
}

?>