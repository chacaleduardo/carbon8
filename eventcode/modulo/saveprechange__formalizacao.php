<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

//abre variavel com a acao que veio da tela
$iu = $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['idformalizacao'] ? 'u' : 'i';
$pagvalmodulo = $_GET['_modulo'];

$_idprodserv = $_SESSION['arrpostbuffer']['2'][$iu]['lote']['idprodserv'];
$idformalizacao = $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['idformalizacao'];

if($_POST['oldstatus'] == 'FORMALIZACAO' and $_SESSION["arrpostbuffer"]["1"]["u"]["formalizacao"]["status"] == 'PROCESSANDO')
{
	$_SESSION["arrpostbuffer"]["2"][$iu]["lote"]["qtdprod"] = $_SESSION["arrpostbuffer"]["2"][$iu]["lote"]["qtdpedida"];
	$_SESSION["arrpostbuffer"]["2"][$iu]["lote"]["qtdprod_exp"] = $_SESSION["arrpostbuffer"]["2"][$iu]["lote"]["qtdpedida_exp"];
}

if($_SESSION["arrpostbuffer"]["1"]["u"]["formalizacao"]["status"] == 'TRIAGEM' and empty($_SESSION["arrpostbuffer"]["2"]["u"]["lote"]["inicioprod"]))
{
    $_SESSION["arrpostbuffer"]["2"]["u"]["lote"]["inicioprod"] = dma(date("Y-m-d"));       
}

$arrpb = $_SESSION["arrpostbuffer"];
reset($arrpb);
//Gerar PARTIDA para qualquer linha que realize insert na lote
foreach($arrpb as $linha => $arrlinha)
{
	foreach($arrlinha as $acao => $arracao) 
    {
		if($acao == "i")
        {  
			foreach($arracao as $tab => $arrtab)
            {
                //Se for tabela de lote, gerar incondicionalmente a Partida
                if($tab == "lote" && empty($_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["partida"]))
                {
                    $_arrlote = geraLote($arrtab["idprodserv"]);
                    $_numlote = $_arrlote[0].$_arrlote[1];
                    $modoprod = traduzid("prodserv", "idprodserv", "modopart", $arrtab["idprodserv"]);
                    if($modoprod == 'PP'){  
                        $_numlote='PP '.$_numlote;            
                        $part_piloto = 'Y';            
                    }else{
                        $part_piloto = 'N';
                    }

                    $fabricante=traduzid('empresa','idempresa','razaosocial',cb::idempresa());
                    
                    //Enviar o campo para a pagina de submit
                    $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["piloto"] = $part_piloto;
                    $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["partida"] = $_numlote;
                    $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["fabricante"] = $fabricante;
                    $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["idpartida"] = $_numlote;
                    $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["spartida"] = $_arrlote[0];
                    $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["npartida"] = $_arrlote[1];
                    $_SESSION["arrpostbuffer"][$linha][$acao]["lote"]["infprod"] = $_arrlote[2];
                }
			}
		} elseif($acao == "u"){
            foreach($arracao as $tab => $arrtab)
            {
                //Se for tabela de lote, gerar incondicionalmente a Partida
                $idloteativ = $_SESSION["arrpostbuffer"][$linha][$acao]["loteativ"]["idloteativ"];
                if($tab == "loteativ" && $_SESSION["arrpostbuffer"][$linha][$acao]["loteativ"]["status"] == "CONCLUIDO")
                {
                    $idsolfab = $_SESSION["arrpostbuffer"]["2"]["u"]["lote"]["idsolfab"];                    
                    if(!empty($idsolfab))
                    {
                        $rowSolfab = FormalizacaoController::buscarStatusSolfabPorIdSolfabEIdEmpresa($idsolfab);
                        if($rowSolfab['status'] != 'APROVADO')
                        {
                            die('É necessário Aprovar a Solicitação de Fabricação antes de Concluir a Atividade.');
                        }                        
                    }

                    //Atualiza o status do Hist para Ativo
                    $idfluxostatushist = FormalizacaoController::buscarFluxoHistoricoIdFormalizacao($idformalizacao, $idloteativ, $_GET["_modulo"], 'PENDENTE');
                    //Valida se tem Hist para atualizar o Fluxo Atual
                    if(!empty($idfluxostatushist['idfluxostatushist']))
                    {
                        FluxoController::atualizaFluxoHist($idfluxostatushist['idfluxostatushist']); //Atualiza o anterior para Ativo
                    }   
                } 
			}
        }
	}
}

//Todas as atividades concluidas lote irá para QUARENTENA
if($iu == "u" && $_POST['concluido'] == 'Y' and $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['status'] == "PROCESSANDO")
{     
    $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['status'] = 'QUARENTENA'; 
}

$status = $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['status'];
//muda a situacao do lote para permitir ou não a impressão e a edição
if($iu == "u" && ($status == 'QUARENTENA' || $status == 'PROCESSANDO' || $status == 'ESGOTADO' || $status == 'APROVADO' || $status == 'REPROVADO')){
    $_SESSION['arrpostbuffer']['2']['u']['lote']['situacao']='CONCLUIDO';
    
}elseif($iu == "u" && ($status == 'FORMALIZACAO' || $status == 'TRIAGEM' || $status == 'AGUARDANDO' || $status == 'ABERTO'))
{
    $_SESSION['arrpostbuffer']['2']['u']['lote']['situacao'] = 'PENDENTE';
}

$ridlote = $_SESSION['arrpostbuffer']['restaurar']['u']['lote']['idlote'];
$rstatus = $_SESSION['arrpostbuffer']['restaurar']['u']['lote']['status'];
if(!empty($ridlote) && $rstatus == 'ABERTO')
{
    $_SESSION['arrpostbuffer']['restaurar']['u']['lote']['idprodservformula'] = null;
    $_SESSION['arrpostbuffer']['restaurar']['u']['lote']['idsolfab'] = null;
    $_SESSION['arrpostbuffer']['restaurar']['u']['lote']['idpessoa'] = null;
    FormalizacaoController::apagarLoteConsRestauracaoPorIdLote($ridlote);
    FormalizacaoController::apagarLoteAtivPorIdLote($ridlote);
    FormalizacaoController::deletarLoteObjPorLote($ridlote);
}

$vidlote = $_SESSION['arrpostbuffer']['2']['u']['lote']['idlote'];
if($iu == "u" && !empty($vidlote))
{
	$etapa = FormalizacaoController::buscarEtapaLote($vidlote);
	if($etapa['qtdLinhas'] > 0)
    {
		$_SESSION['arrpostbuffer']['2']['u']['lote']['idetapa'] = $etapa['dados']['idetapa'];
	}
}

if($_SESSION['arrpostbuffer']['1']['u']['formalizacao']['status'] == 'APROVADO' || $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['status'] == 'REPROVADO')
{
    $statusFor = $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['status'];
    if(!empty($_SESSION['arrpostbuffer']['1']['u']['formalizacao']['idprproc']))
    {
        $idfluxostatus = FluxoController::getIdFluxoStatus($pagvalmodulo, $statusFor, $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['idprproc']);
        $_SESSION['arrpostbuffer']['1']['u']['formalizacao']['idfluxostatus'] = $idfluxostatus;
    }

    //LTM (02/06/2021): Caso a OP seja aprovada ou reprovada, alterará tb o status do Lote
    $idlote = $_SESSION['arrpostbuffer']['2']['u']['lote']['idlote'];
	$modulo = FluxoController::getDadosModuloPrincipal($idlote);

	$rowFluxo = FluxoController::getFluxoStatusHist($modulo, 'idlote', $idlote, $statusFor);
	FluxoController::alterarStatus($modulo, 'idlote', $idlote, $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);	

    if($_SESSION['arrpostbuffer']['1']['u']['formalizacao']['status'] == 'REPROVADO'){
        $lotesConsumidos = FormalizacaoController::buscarConsumoProdutoRetornarEstoque($idlote, 'lote');
        foreach($lotesConsumidos as $_lotesConsumido){
            FormalizacaoController::atualizarStatusLoteCons($_lotesConsumido['idlotecons'], 'INATIVO');
        }
    }
}

unset($fluxo);

if($_POST['fabricacao_old'] <> $_SESSION['arrpostbuffer']['2']['u']['lote']['fabricacao'] && !empty($_POST['validade']))
{
    $fabricacao = explode("/", ($_SESSION['arrpostbuffer']['2']['u']['lote']['fabricacao'] ? $_SESSION['arrpostbuffer']['2']['u']['lote']['fabricacao'] : $_POST['_h1_i_modulohistorico_valor']));
    $fabricacao = $fabricacao[2]."-".$fabricacao[1]."-".$fabricacao[0];
    $vencimento = date('d/m/Y', strtotime("+".$_POST['validade']." MONTH", strtotime($fabricacao)));
    $_SESSION['arrpostbuffer']['2']['u']['lote']['vencimento'] = $vencimento;
}


$gerandohistorico = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'];
if (!empty($gerandohistorico)) 
{
    $campo = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['campo'];
    $valor = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['valor'];
    $_SESSION['arrpostbuffer']['2']['u']['lote'][$campo] = $valor;

}