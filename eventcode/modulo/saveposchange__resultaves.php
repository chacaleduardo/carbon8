<?
$modulo = 'resultprod';
$idamostra = $_POST["#nameidamostra"];
$status = $_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["status"];
$idresultado = $_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["idresultado"];
$pessoapost = $_SESSION['SESSAO']['USUARIO'];
$statusanterior = $_POST['_statusant_'];

if($status == 'PROCESSANDO' && !in_array($statusanterior, ['ASSINADO' , 'FECHADO' , 'CONFERIDO'])){

	$unidade = FluxoController::CountUnidadeResultado($idresultado); 
	$categoria = InclusaoResultadoController::BuscaCategoriaIdtipoteste($idresultado);

if($categoria['fabricado'] == "Y" && count($unidade) >= 1 && in_array($categoria['tipoprodserv'], ["MEIOS FORMULADOS" , "CONCENTRADOS" , "MATERIAL LABORATÓRIO E PRODUÇÃO"])){
		$statusloteativ = 'PROCESSANDO';
		LoteController::AlteraStatusLoteAtivPorResultado($categoria['idloteativ'], $pessoapost, $statusloteativ);
	}
}

if($status == 'FECHADO' && !in_array($statusanterior, ['ASSINADO' , 'FECHADO' , 'CONFERIDO'])){

	if(!empty($idresultado)){//se o resultado for 1fechado deletar assinaturas da resultadoassinatura
		InclusaoResultadoController::deletarResultadoAssinaturaPorIdresultado($idresultado);
	}

	congelaResultado($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["idresultado"], false);

	//inserir custo no resultado
	$custoTeste=InclusaoResultadoController::buscarCustoTeste($idresultado);
	$rv =InclusaoResultadoController::atualizarCustoIdresultado($custoTeste['custo'],$idresultado);

	geraRateioResultado($_SESSION["arrpostbuffer"]["1"]["u"]["resultado"]["idresultado"]);

	//Busca tipounidade e categoria para IF
	$unidade = FluxoController::CountUnidadeResultado($idresultado); 
	$categoria = InclusaoResultadoController::BuscaCategoriaIdtipoteste($idresultado);

	// Confere e assina resultado em todos os casos que são controle de qualidade
	if(count($unidade) >= 1){
		AlteraStatusResultado('resultprod', $idresultado, 'CONFERIDO');
		AlteraStatusResultado('resultprod', $idresultado, 'ASSINADO');
		
		//Verifica tipounidade e subcategoria
		if($categoria['fabricado'] == "Y" && in_array($categoria['tipoprodserv'], ["MEIOS FORMULADOS" , "CONCENTRADOS" , "MATERIAL LABORATÓRIO E PRODUÇÃO"])){
		
			$statusloteativ = 'CONCLUIDO';
			processaResultado($categoria, $idresultado, $idamostra, $modulo, $pessoapost, $statusloteativ);
		}
	}
}

function processaResultado($categoria, $idresultado, $idamostra, $modulo, $pessoapost, $statusloteativ){

	/* SELECT's PARA function */ 

	//Verifica se existe mais algum resultado vinculado a loteativ que não esteja fechado
	$quantidaderesultado = LoteController::BuscaResultadosVinculados($categoria['idloteativ'], $idresultado);

	//Verifica se todos os resultados vinculados ao lote estão aprovados ou reprovados
	$resultadogeral = InclusaoResultadoController::BuscarConformidadeResultado($categoria['idlote']);

	//Atualiza loteativ e formalizacao
	if($quantidaderesultado['resultados'] == 0){
		LoteController::AlteraStatusLoteAtivPorResultado($categoria['idloteativ'], $pessoapost, $statusloteativ);
		FluxoController::alterarStatusFormalizacao('formalizacao' , $categoria['idformalizacao'],$categoria['idloteativ']);
	}

	//Aprova/Reprova formalizacao/lote por resultado
	if($resultadogeral['status'] == 'APROVADO' && !in_array($categoria['statusformalizacao'], ['APROVADO' , 'REPROVADO'])){

		$moduloupdateformalizacao = InclusaoResultadoController::BuscaModuloPorIdunidadeFormalizacao($categoria['idformalizacao'], $categoria['idunidadeformalizacao'], 'formalizacao');
		$moduloupdatelote = InclusaoResultadoController::BuscaModuloPorIdunidadeLote($categoria['idlote'], $categoria['idunidadelote'], 'lote');

		//Update para novo status
		AlteraStatusFormalizacaoLote($moduloupdateformalizacao['modulo'], 'idformalizacao', $categoria['idformalizacao'], 'APROVADO');
		AlteraStatusFormalizacaoLote($moduloupdatelote['modulo'], 'idlote', $categoria['idlote'], 'APROVADO');

	} elseif($resultadogeral['status'] == 'REPROVADO' && !in_array($categoria['statusformalizacao'], ['APROVADO' , 'REPROVADO'])){

		$moduloupdateformalizacao = InclusaoResultadoController::BuscaModuloPorIdunidadeFormalizacao($categoria['idformalizacao'], $categoria['idunidadeformalizacao'], 'formalizacao');
		$moduloupdatelote = InclusaoResultadoController::BuscaModuloPorIdunidadeLote($categoria['idlote'], $categoria['idunidadelote'], 'lote');

		//Update para novo status
		AlteraStatusFormalizacaoLote($moduloupdateformalizacao['modulo'], 'idformalizacao', $categoria['idformalizacao'], 'REPROVADO');
		AlteraStatusFormalizacaoLote($moduloupdatelote['modulo'], 'idlote', $categoria['idlote'], 'REPROVADO');
	}

	// Fecha amostra
	if($quantidaderesultado['resultados'] == 0 && $categoria['statusamostra'] <> 'FECHADO'){

		$idamostra = $categoria['idamostra'];

		//Update para novo status
		AlteraStatusAmostra('amostraprod', $idamostra, 'CONFERIDO');
		AlteraStatusAmostra('amostraprod', $idamostra, 'FECHADO');
	}
}

//Altera status de resultado ao fechar
function AlteraStatusResultado($modulo, $idresultado, $statusNovo){
	$rowFluxo = FluxoController::getDadosResultadoAmostra('resultado', 'idresultado', $idresultado, $statusNovo, 'resultado', '', '');		
	FluxoController::alterarStatus($modulo, 'idresultado', $idresultado, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], '', 'Y', '', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
}

//Altera status de amostra ao fechar
function AlteraStatusAmostra($modulo, $idamostra, $statusNovo){
	$rowFluxo = FluxoController::getDadosResultadoAmostra('amostra', 'idamostra', $idamostra, $statusNovo, 'amostra', '', '');		
	FluxoController::alterarStatus($modulo, 'idamostra', $idamostra, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], '', 'Y', '', $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
}

// Altera status formalizacao e lote
function AlteraStatusFormalizacaoLote($tabela, $idtabela, $id, $status){
	$rowFluxo = FluxoController::getFluxoStatusHist($tabela, $idtabela, $id, $status);
		FluxoController::alterarStatus($tabela, $idtabela , $id, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);
}

//@533127 - CORRIGIR ERRO DE INSERÇÃO NO FLUXO INICIAL DOS TESTES
if(!empty($_SESSION["arrscriptsql"])){
	require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
	require_once("../model/evento.php");
	
		
	$idresultado = $_SESSION["_pkid"];
	$rowFluxo = FluxoController::getDadosResultadoAmostra('resultado', 'idresultado', $idresultado, 'ABERTO', 'resultado', '', '');	
	foreach($_SESSION["arrscriptsql"] as $array){
		foreach($array as $key=>$resultado){
			if($key=='resultado'&&$resultado['acao']=='i')
				FluxoController::inserirFluxoStatusHist($rowFluxo['modulo'], $resultado['insertid'], $rowFluxo['idfluxostatus'], 'PENDENTE');
		}
	}
}


/* retirado semente criada não entra direto em pool 25-02-2021 hermesp
$id_lote =$_SESSION["_pkid"];
$_idprodserv = $_SESSION["arrpostbuffer"]['x']["i"]["lote"]["idprodserv"];
$_idobjetosolipor = $_SESSION["arrpostbuffer"]['x']["i"]["lote"]["idobjetosolipor"];
if(!empty($_idprodserv) and !empty($id_lote) and !empty($_idobjetosolipor) ){
	
		$sqlp="select lp.idpool,l.vencimento 
					from resultado rr join amostra aa on (aa.idamostra = rr.idamostra)
					join amostra a  on(aa.idpessoa = a.idpessoa)
					join resultado r on (r.idamostra = a.idamostra)
					join lote l  on (r.idresultado=l.idobjetosolipor 
									and l.tipoobjetosolipor = 'resultado' 
									and l.status <> 'ESGOTADO' 
									and l.idprodserv = ".$_idprodserv.")
					 join lotepool lp on(lp.idlote=l.idlote and lp.status='ATIVO')
					where rr.idresultado=".$_idobjetosolipor." order by l.vencimento asc limit 1 ";
		
		$resp =d::b()->query($sqlp) or die("saveprechange_resultaves: Falha ao buscar pool de sementes.  ".mysqli_error());
		$qtdpool=mysqli_num_rows($resp);
		if($qtdpool>0){
			$rowp=mysqli_fetch_assoc($resp);

			$sqlin="INSERT INTO `lotepool` (idempresa,idpool,idlote,criadopor,criadoem,alteradopor,alteradoem)	
				values(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$rowp['idpool'].",".$id_lote.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

			$resin = d::b()->query($sqlin) or die("Erro ao inserir semente no pool ".$sqlin);
			if(!$resin){
				d::b()->query("ROLLBACK;");
				die("1-Falha ao inserir semente no pool: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlin);
			}
		}else{//if($qtdpool>0){}//cria um pool
                    
                    $sqli="INSERT INTO `pool` (idempresa,status,criadopor,criadoem,alteradopor,alteradoem)	
				values(".$_SESSION["SESSAO"]["IDEMPRESA"].",'ATIVO','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

                    $resi = d::b()->query($sqli) or die("Erro ao GERAR  pool ".$sqlin);
                    $inidpool= mysqli_insert_id(d::b());
                    
                    
                    $sqlin="INSERT INTO `lotepool` (idempresa,idpool,idlote,criadopor,criadoem,alteradopor,alteradoem)	
				values(".$_SESSION["SESSAO"]["IDEMPRESA"].",".$inidpool.",".$id_lote.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

                    $resin = d::b()->query($sqlin) or die("Erro ao inserir semente no pool ".$sqlin);
                    if(!$resin){
                            d::b()->query("ROLLBACK;");
                            die("1-Falha ao inserir semente no pool 2: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlin);
                    }

                }
		
}//if(!empty($_idprodserv) and !empty($id_lote) and !empty($_idobjetosolipor) ){
*/