<?

require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

if(empty($_SESSION['arrpostbuffer']['99']['i']['modulocom']['descricao'])){
    unset($_SESSION['arrpostbuffer']['99']);
}

//se estiver ativando uma tag na solicitação de material, remover tag da sala atual e salvar esse dado na tabela auxiliar [solmatitemobj]
if(isset($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtag']))
{    
    //pegando a tag
    $idtag = $_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtag'];
    //verificar se ela está em alguma sala
    $tagSalas = SolmatController::buscarTagPorIdTag($idtag);  
    if($tagSalas['qtdLinhas'] > 0)
    {
        //salvar a tagpai antiga na tabela auxiliar para o caso de precisar retornar ela ao estado anterir
        $tagPai = $tagSalas['dados'];
        $_SESSION['arrpostbuffer']['w']['i']['solmatitemobj']['idtagpaianterior'] = $tagPai['idtagpai'];
        SolmatController::apagarTagSalaPorIdTag($idtag); 
    }

    if($_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtagpai'] == ''){
        $_SESSION['arrpostbuffer']['s']['i']['tagsala']['idtagpai'] == NULL;
    }
}

$_arrtabdef = retarraytabdef('solmatitem');
/*
 * INSERIR ITENS NA TELA DE SOLICITAÇÂO DE MATERIAS
 */
function debug($inDebug)
{
    $sm = "call debug('solmat_php', '#".$inDebug."')";
    d::b()->query($sm);
}

if(!empty($_POST["_qtdmaximo_"]) && !empty($_POST["_qtdcmaximo_"]))
{
    $qtdcmaximo = floatval($_POST["_qtdcmaximo_"]);
    $qtdmaximo =  floatval($_POST["_qtdmaximo_"]);
    $qtd =  floatval($_SESSION["arrpostbuffer"]["x"]["i"]["lotefracao"]["qtd"]);
    if($qtd > $qtdmaximo )
    {
        cbSetPostHeader("0","erro");
        die("O valor informado é maior que o valor disponível no lote.");
    }

    if( $qtd <= 0)
    {
        cbSetPostHeader("0","erro");
        die("O valor informado é inválido.");
    }

}

$arrsolmatitem = array();
foreach($_POST as $k=>$v) {
	if(preg_match("/_x(\d)_u_solmatitem_(.*)/", $k,$res)){
        $arrsolmatitem[$res[1]][$res[2]] = $v;
	}
}

if(!empty($arrsolmatitem))
{
   // LOOP NAS QTDC DA TELA
   foreach($arrsolmatitem as $k => $v)
   {
        //Verifica se ultrapassou a quantidade limitada por produto
        $idUnidade = $_SESSION["arrpostbuffer"]["1"]["u"]["solmat"]["idunidade"];
        $criadoem = explode(" ", traduzid("solmat", "idsolmat", "criadoem", $_SESSION["arrpostbuffer"]["1"]["u"]["solmat"]["idsolmat"]));
        $dataCriadoEm = explode("-", $criadoem[0]);
        $planejamentoProdserv = SolmatController::buscarPlanejamentoPorIdProdservMesExercioUnidadePorProdserv($v['idprodserv'], $idUnidade, $dataCriadoEm[0], $dataCriadoEm[1]);
        $somaConsumoMes = SolmatController::buscarConsumoLoteMes($dataCriadoEm[0], $dataCriadoEm[1], $idUnidade, $v['idprodserv']);
        $totalDisponivel =  $planejamentoProdserv['planejado'];
        $adicional = ($totalDisponivel * $planejamentoProdserv['adicional']) / 100;
        $totalDisponivelComAdicional = $adicional + $totalDisponivel - ($somaConsumoMes['totalconsumomes'] == NULL ? 0 : $somaConsumoMes['totalconsumomes']);
        $del = explode('_', '_x_d_solmatitem_idsolmatitem');
        if($v['qtdc'] > $totalDisponivelComAdicional && $planejamentoProdserv['planejado'] > 0 && $del[2] != 'd')
        {
            die('O produto '.traduzid("prodserv", "idprodserv", "descr", $v['idprodserv']). ' ultrapassou a quantidade máxima no mês.');
        }

        $qtdc = $v['qtdc'];
        if(empty($qtdc) && empty($planejamentoProdserv['planejado']))
        {
            die("Preencha a quantidade(Qtd)");
        } 
   }
}

$arrInsProd = array();
foreach($_POST as $k=>$v) 
{
	if(preg_match("/_(\d*)#(.*)/", $k, $res))
    {
		$arrInsProd[$res[1]][$res[2]] = $v;
	}
}

if(!empty($arrInsProd))
{
    $i = 99977;
    // LOOP NOS ITENS DO + DA TELA
    foreach($arrInsProd as $k => $v)
    {
       $i = $i + 1;
        $idprodserv = $v['idprodserv'];
	    $prodservdescr = $v['prodservdescr'];
        if(empty($_SESSION["arrpostbuffer"]["1"]["u"]["solmat"]["idsolmat"])){
            $idsolmat = $_SESSION["_pkid"];
        } else {
            $idsolmat = $_SESSION["arrpostbuffer"]["1"]["u"]["solmat"]["idsolmat"];
        }

        if(!empty($idprodserv) OR !empty($prodservdescr))
        {
            if(empty($idsolmat)){die("[saveprechange_solmat]-Não foi possivel identificar o ID da solicitacao!!!");}   

            // montar o item para insert
            $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['qtdc'] = $v["quantidade"];
            $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['obs'] = $v["obs"];
            $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['idempresa'] = $_SESSION["SESSAO"]["IDEMPRESA"];
            if(!empty($v["idprodserv"])){
                $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['idprodserv'] = $v["idprodserv"];
                
            }else{
                $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['descr'] = $v["prodservdescr"];
            }
            $_SESSION['arrpostbuffer'][$i]['i']['solmatitem']['idsolmat'] = $idsolmat;
        }
   } //foreach($arrInsProd as $k=>$v){  
}//if(!empty($arrInsProd)){

// tira a session dos comentarios
if(empty( $_SESSION['arrpostbuffer']['xa']['i']['solmaticoment']['comentario']) ){
    unset($_SESSION['arrpostbuffer']['xa']['i']['solmaticoment']['comentario']);
}

$idtransacao = SolmatController::buscarRandomico()['idtransacao'];
$idunidade = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idunidade'];
$qtdpedida = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['qtd'];
$idloteorigem = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlote'];
$idlotefracaoori = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idlotefracaoorigem'];
$idsolmatitem = $_SESSION['arrpostbuffer']['x']['i']['lotefracao']['idobjeto'];
   
if(!empty($idlotefracaoori))
{
    $consomeun = traduzid('unidade', 'idunidade', 'consomeun', $idunidade);  
    $listarLoteFracao = SolmatController::buscarLotefracaoPorIdloteIdunidade($idloteorigem, $idunidade);  
    $qtdr = count($listarLoteFracao);
    if($qtdr>0)
    {
        // se ja tiver fracao 
        unset($_SESSION['arrpostbuffer']);
        $_SESSION['arrpostbuffer']['1']['u']['solmat']['idsolmat']= $_GET['idsolmat'];
           
        $qtdpedidaori = $qtdpedida;
        $qtdpedidadest = $qtdpedida;
        //ALTERAÇÕES DO PROJETO: CRIAÇÃO DE CHECK IMOBILOZADO NA PRODSERV -> LINK DO EVENTO: sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=467416  -- ALBT 11/06/2021.
       // $rowconv = SolmatController::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idlotefracaoori);        

        //Inserir Débito
        $arrayInsertLoteConsDebito = [
            "idempresa" => cb::idempresa(),
            "idlote" => $idloteorigem,
            "idlotefracao" => $idlotefracaoori,
            "idobjeto" => $listarLoteFracao['idlotefracao'],
            "tipoobjeto" => 'lotefracao',
            "obs" => 'Lote Fracionado.',
            "idtransacao" => $idtransacao,
            "idobjetoconsumoespec" => $idsolmatitem,
            "tipoobjetoconsumoespec" => 'solmatitem',
            "qtdd" => str_replace(",", ".", $qtdpedidaori),
            "status" => 'PENDENTE',
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ];
        SolmatController::inserirLoteCons($arrayInsertLoteConsDebito);

         //Inserir Crédito
         $arrayInsertLoteConsCredito = [
            "idempresa" => cb::idempresa(),
            "idlote" => $idloteorigem,
            "idlotefracao" =>$listarLoteFracao['idlotefracao'],
            "idobjeto" =>  $idlotefracaoori,
            "tipoobjeto" => 'lotefracao',
            "obs" => 'Crédito via solicitação de materiais.',
            "idtransacao" => $idtransacao,
            "idobjetoconsumoespec" => $idsolmatitem,
            "tipoobjetoconsumoespec" => 'solmatitem',
            "qtdc" =>0,
            "status" => 'PENDENTE',
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ];
        SolmatController::inserirLoteCons($arrayInsertLoteConsCredito);
            
        // debug(1);
        montatabdef();
        // debug(2);
    }else{ //se não tiver fracao
        $qtdpedidaori = $qtdpedida;
        $qtdpedidadest = $qtdpedida;
        unset($_SESSION['arrpostbuffer']);                       
       // $rowconv = SolmatController::buscarInformacoesDaUnidadeDeOrigemPorIdLoteFracao($idlotefracaoori);
        debug(3);
        $_idempresa = traduzid('unidade', 'idunidade', 'idempresa', $idunidade);
        $arrayInsertLoteFracao = [
            "idempresa" => $_idempresa,
            "idunidade" => $idunidade,
            "qtd" => 0,
            "qtdini" => 0,         
            "idlote" => $idloteorigem,
            "idtransacao" => $idtransacao,
            "idlotefracaoorigem" => $idlotefracaoori,         
            "usuario" => $_SESSION["SESSAO"]["USUARIO"],
            "status" => 'PENDENTE'
        ];
        $_idlotefracao = SolmatController::inserirLoteFracaoStatus($arrayInsertLoteFracao);

        debug(4);
        $_SESSION['arrpostbuffer']['ulc']['u']['lotefracao']['idlotefracaoorigem'] = $idlotefracaoori;
        $_SESSION['arrpostbuffer']['ulc']['u']['lotefracao']['idlotefracao']= $_idlotefracao;

        $arrayInsertLoteCons = [
            "idempresa" => cb::idempresa(),
            "idlote" => $idloteorigem,
            "idlotefracao" => $idlotefracaoori,
            "idobjeto" => $_idlotefracao,
            "tipoobjeto" => 'lotefracao',
            "obs" => 'Transferência na solicitacão de materiais.',
            "idtransacao" => $idtransacao,
            "idobjetoconsumoespec" => $idsolmatitem,
            "tipoobjetoconsumoespec" => 'solmatitem',
            "qtdd" => str_replace(",", ".", $qtdpedidaori),
            "qtdc" => 0,
            'status' => 'PENDENTE',
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ];
        SolmatController::inserirLoteCons($arrayInsertLoteCons);
        //gerar rateio

        // debug(5);
        montatabdef();
        // debug(6);
    }        
}

if($_SESSION['arrpostbuffer']['conspend']['u']['solmat']['idsolmat']){
    $lotecons = SolmatController::buscarConsumoPendenteDaSolmat($_SESSION['arrpostbuffer']['conspend']['u']['solmat']['idsolmat']);

    if (count($lotecons) == 0){
        die("Sem lotes pendentes para ser consumido para esta Solmat");
    }
    unset($_SESSION['arrpostbuffer']);                       
    foreach($lotecons as $k =>$lote){
        $_SESSION['arrpostbuffer']['conspend'.$k]['u']['lotecons']['idlotecons'] = $lote['idlotecons'];
        $_SESSION['arrpostbuffer']['conspend'.$k]['u']['lotecons']['status'] = 'ABERTO';
        SolmatController::atualizarLoteFracaoPorIdTransacao($lote['qtdd'], $lote['idtransacao'], 'DISPONIVEL');
        SolmatController::atualizarLoteConsPorIdTransacaoDebito($lote['qtdd'], $lote['idtransacao'], 'ABERTO');  
        SolmatController::atualizarLoteConsPorIdTransacaoCredito($lote['qtdd'], $lote['idtransacao'], 'ABERTO');
         

        $consomeun = traduzid('unidade', 'idunidade', 'consomeun', $lote['idunidade']);  
       
        $listarLoteFracao = SolmatController::buscarLotefracaoPorIdloteIdunidade($lote['idlote'], $lote['idunidade']);  
        $qtdr = count($listarLoteFracao);
        if($qtdr>0){
            $consomeTransferencia = SolmatController::buscarSeLoteConsomeTransferencia($listarLoteFracao['idlotefracao']);
            if(($consomeun == 'Y' || $consomeTransferencia["consometransf"] == 'Y') & $consomeTransferencia["imobilizado"] != 'Y')
            {
                $_SESSION['arrpostbuffer']['ulc2'.$k]['i']['lotecons']['idlote'] = $lote['idlote'];
                $_SESSION['arrpostbuffer']['ulc2'.$k]['i']['lotecons']['idlotefracao'] = $listarLoteFracao['idlotefracao'];
                $_SESSION['arrpostbuffer']['ulc2'.$k]['i']['lotecons']['idobjeto'] = $listarLoteFracao['idlotefracao'];
                $_SESSION['arrpostbuffer']['ulc2'.$k]['i']['lotecons']['tipoobjeto'] = 'lotefracao';
                $_SESSION['arrpostbuffer']['ulc2'.$k]['i']['lotecons']['status'] = 'ABERTO';
                $_SESSION['arrpostbuffer']['ulc2'.$k]['i']['lotecons']['idtransacao'] = $lote['idtransacao'];
                $_SESSION['arrpostbuffer']['ulc2'.$k]['i']['lotecons']['obs'] = 'Lote consumido na transferência da solicitacão de materiais.';
                $_SESSION['arrpostbuffer']['ulc2'.$k]['i']['lotecons']['qtdd'] = $lote['qtdd'];
            }
        }
            
        // debug(1);
        // montatabdef();
        // debug(2);

        unset($lote);
        unset($listarLoteFracao);
    }
    montatabdef();
}

$_idsolmatitem = $_SESSION['arrpostbuffer']['apv']['u']['solmatitem']['idsolmatitem'];
$_status = $_SESSION['arrpostbuffer']['apv']['u']['solmatitem']['status'];
$aprovadopor = $_SESSION['arrpostbuffer']['apv']['u']['solmatitem']['aprovadopor'];
if( !empty($_idsolmatitem) and !empty($_status)){

    $_idsolmat =  traduzid("solmatitem", "idsolmatitem", "idsolmat",$_idsolmatitem);
    if(!empty($_idsolmat)){

        $pend = SolmatController::buscarSolmatitemPedente($_idsolma,$_idsolmatitem);

        if($pend['qtd']<1){

            $idfluxostatus = FluxoController::getIdFluxoStatus('solmat', 'SOLICITADO'); 

            $_SESSION['arrpostbuffer']['x']['u']['solmat']['status']='SOLICITADO';
            $_SESSION['arrpostbuffer']['x']['u']['solmat']['idsolmat']= $_idsolmat;
            $_SESSION['arrpostbuffer']['x']['u']['solmat']['idfluxostatus']= $idfluxostatus;
            montatabdef();
            FluxoController::inserirFluxoStatusHist('solmat',$_idsolmat, $idfluxostatus, 'PENDENTE');

        }
      
    }   
}

?>