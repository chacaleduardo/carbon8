<?
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");
require_once("../api/nf/index.php");
require_once("../form/controllers/cotacao_controller.php");

//$_idempresa = isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];
$_idempresa = cb::idempresa();

/*
 * ao alterar o status de um orçamento para aprovado  este presave cria uma nova nf como pedido copia
 * os item da tabela nfitem e feita a baixa no estoque pela trigger no banco de dados
 */
$status = $_SESSION['arrpostbuffer']['1']['u']['cotacao']['status'];
$idcotacao = $_SESSION['arrpostbuffer']['1']['u']['cotacao']['idcotacao'];
$tiponf = $_SESSION['arrpostbuffer']['1']['u']['cotacao']['tiponf'];
$statusNf = $_POST['_x_u_nf_status'];
$idnotafiscal = $_POST['_x_u_nf_idnf'];
$usuario = $_SESSION["SESSAO"]["USUARIO"];

reset($_SESSION['arrpostbuffer']);
retarraytabdef('nfitem');

//retira o primeiro nivel do array
$j = 400;
$i = 600; 		 
$arrcontroleforn = array();//array para guardar os fornecedores
$arrpostbuffer = $_SESSION['arrpostbuffer'];
$_idpessoa_fixo = $_POST['_idpessoa_fixo'];// passado pela ajax/listaprodutocot quando pesquisado por fornecedor
$_idpessoa_novo = $_POST['idpessoa'];

if($statusNf == 'APROVADO')
{
    $nfDataRecb = CotacaoController::buscarIdNfConfPagar($idnotafiscal);
    $nfConfPagarQtd = count($nfDataRecb);
    if($nfConfPagarQtd == 0 || ($nfConfPagarQtd == 1 && ($nfDataRecb[0]['datareceb'] == '0000-00-00' || empty($nfDataRecb[0]['datareceb']))))
    {
        //gerar as configurações
        geranfconfpagar($idnotafiscal);

        $nfDataRecb = CotacaoController::buscarIdNfConfPagar($idnotafiscal);
        $nfConfPagarQtd = count($nfDataRecb);
        if($nfConfPagarQtd == 0 || ($nfConfPagarQtd == 1 && ($nfDataRecb[0]['datareceb'] == '0000-00-00' || empty($nfDataRecb[0]['datareceb']))))
        {
            echo 'Favor Gerar as Parcelas. Não foram criadas corretamente.';
            die();
        }
    }
}

if(($statusNf == 'APROVADO' || $statusNf == 'PREVISAO') AND !empty($idnotafiscal) AND !empty($idformapagamento))
{
    //Criar o lote automaticamente
    if(NfEntradaController::buscarItemValorNulo($idnotafiscal) > 0){
        die('O "Valor Un" não foi preenchido. Favor preencher!');
    }

	$produtos = CotacaoController::buscarProdutoPorNfItem($idnotafiscal);
	$criaLote = 'N';
    foreach($produtos as  $_produto) {
        if($_produto['geraloteautomatico'] == 'Y' && empty($_produto['idlote'])){
            if($_produto['un_item'] == $_produto['un_prod']) {
                $criaLote = 'Y';
            } else {
                $parteCnpj = substr($_produto['cpfcnpj'], 0, 10);
                $qtdUnConv = CotacaoController::buscarConversaoFornecedorPorCnpj($parteCnpj, $_produto['idprodserv'], $_produto['un_item']);
                if(count($qtdUnConv) == 0){
                    $criaLote = 'N';
                } else {
                    $criaLote = 'Y';
                }
            }

            if($_produto['geraloteautomatico'] == 'Y' && $criaLote == 'Y'){
                $data = $_produto['criadoem'];
                $dateTime = new DateTime($data);
                $exercicio = $dateTime->format("Y");		
                criarlotecompra($_produto['idprodserv'], $_produto['idnfitem'], $_produto['qtd'], $exercicio);
            } else if($_produto['geraloteautomatico'] == 'Y' && $criaLote == 'N') {
                die('O produto <b>'.$_produto['descr'].'</b> tem a unidade ('.$_produto['un_item'].') diferente da Prodserv ('.$_produto['un_prod'].').
                    <br /> Não tem configuração no Fornecedor.
                    <br /> Favor acrescentar a conversão deste item.');
            }
        }
    }
}

// PREPARA PARA INSERIR ITENS DESCRITIVOS
$arrInsProd=array();
foreach($_POST as $k => $v) {
	if(preg_match("/_(\d*)#(.*)/", $k, $res)){
		$arrInsProd[$res[1]][$res[2]]=$v;
	}
}

foreach($arrpostbuffer as $key => $value)
{
    $iu = $value['u']['nfitem']['idnfitem'] ? 'u' : 'i';
    if($iu == 'u' and $value['u']['nfitem']['validade'] == '0000-00-00')
    {
        unset($_SESSION['arrpostbuffer'][$key]['u']['nfitem']['validade']);
    }
}
reset($arrpostbuffer);

//IF PARA INSERIR OS ITENS DESCRITIVOS
if(!empty($arrInsProd) && empty($_POST['duplicar']))
{
    $i = 999999;   
    $nf = CotacaoController::buscarIdNfPorTipoObjetoStatusIdpessoa($idcotacao, 'cotacao', 'INICIO', $_idpessoa_fixo);
    
    if(empty($nf['idnf']))
    {   
        $idtipounidade = CotacaoController::buscarIdTipoUnidade($tiponf);        
        $rwUnid = CotacaoController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);

        //LTM - 31-03-2021: Retorna o Idfluxo nf
        $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');

        if($tiponf != 'V'){
            $idunidade = $rwUnid["idunidade"]; 
        } else {
            $idunidade = 'NULL';
        }

        $newidnf = CotacaoController::inserirNf($_idpessoa_fixo, $_idempresa, $idcotacao, $idfluxostatus, $idunidade, 'cotacao', 'INICIO', '0', $tiponf, $usuario);
        $arrayLog = [
            "idempresa" => cb::idempresa(),
            "sessao" => '',
            "tipoobjeto" => 'nf',
            "idobjeto" => $newidnf,
            "tipolog" => 'unidadeNf',
            "log" => $idunidade,
            "status" => 'ERRO',
            "info" => 'Inserção Nos Itens Descritivos',
            "criadoem" => SYSDATE(),
            "data" => SYSDATE(),
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ];
        CotacaoController::inserirLog($arrayLog);

        if($newidnf)
        {
            //LTM - 31-03-2021: Insere o fluxo
            FluxoController::inserirFluxoStatusHist('nfentrada', $newidnf, $idfluxostatus, 'PENDENTE');            
            CotacaoController::inserirIdNfContaPagar($newidnf, $_idempresa, $usuario);
        }

    }else{
       $newidnf = $nf['idnf'];
    }
    
    // LOOP NOS ITENS DO + DA TELA
    foreach($arrInsProd as $k => $v)
    {
        $i = $i++;
	    $obs = $v['prodservdescr'];
        $idnf = $newidnf;       

        if(empty($idprodserv) and empty($obs) ){die("[saveprechange_nf]-Não foi possivel identificar o produto!!!");}
        if(empty($idnf)){die("[saveprechange_nf]-Não foi possivel identificar o ID do Pedido!!!");}   
		
        // montar o item para insert
        $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['qtd'] = $v["quantidade"];
        $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['qtdsol'] = $v["quantidade"];
        $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idempresa'] = $_idempresa;
        $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['prodservdescr'] = $v["prodservdescr"];
        $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['idnf'] = $idnf;
        $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['tiponf'] = 'C';
        $_SESSION['arrpostbuffer'][$i]['i']['nfitem']['nfe'] = 'Y';
 
   } //foreach($arrInsProd as $k=>$v){  
}//FIM INSERIR ITEMS DESCRITIVO
   
//INSERIR OS ITENS CADASTRADOS
unset($value);
foreach($arrpostbuffer as $key => $value)
{
    $iu = $value['i']['nfitem']['idprodserv'] ? 'i' : 'u';

    if($iu == 'i' && !empty($value['i']['nfitem']['qtd'] && empty($_SESSION['arrpostbuffer']['x']['i']['nfitem']['idnf'])))
    {
       $idprodserv = $value['i']['nfitem']['idprodserv'];
       $qtd = $value['i']['nfitem']['qtd'];

        /*
         * Verificar quantos fornecedores o produto possui para inserir na cotacaoforn
         */
        if(empty($_idpessoa_fixo)){
            $cond_where = " AND p.idpessoa is not null ";
        }else{
            $cond_where = " AND p.idpessoa = ".$_idpessoa_fixo;
        }

        $fornecedores = CotacaoController::buscarFornecedoresPertencentesCotacao($idcotacao, 'cotacao', $cond_where, $idprodserv);

        $idpessoa = 0;
        foreach($fornecedores as $rowd) 
        {
            /* hermesp - 21-10-2021
            @487471 -Impedir que o sistema gere cotações separadas para o mesmo fornecedor,
            quando este possui mais de uma conversão na prodservforn           
            */
            if($idpessoa == $rowd['idpessoa'] && !empty($newidnf)){
                $rowd["idnf"] = $newidnf;
            }else{
                $idpessoa = $rowd['idpessoa'];
            }
            
			//LTM 27-10-2020 - Alterado para não fazer calculo quando for produto. Na divisão estava retornando "INF"
            if($rowd['converteest'] == 'Y' && $rowd['tipo'] == 'PRODUTO'){
                $un = $rowd['unforn'];
                if(empty($rowd['valconv'])){
                    die("[Erro] Verifique o valor de conversão do produto ".$rowd['descr']."<br><a href='?_modulo=prodserv&_acao=u&idprodserv=".$idprodserv."' target='_blank'>Ajustar</a>");
                }
                $_qtd = $qtd/$rowd['valconv'];
            }else{
                $un = $rowd['un'];
                $_qtd = $qtd;
            }

            if(!empty($rowd["idnf"]))
            {
                $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idnf'] = $rowd["idnf"];

            }else{
                $idtipounidade = CotacaoController::buscarIdTipoUnidade($tiponf);
                $rwUnid = CotacaoController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);

                //LTM - 31-03-2021: Retorna o Idfluxo nf
                $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');

                if($tiponf != 'V'){
                    $idunidade = $rwUnid["idunidade"]; 
                } else {
                    $idunidade = 'NULL';
                }

                $newidnf = CotacaoController::inserirNf($rowd['idpessoa'], $_idempresa, $idcotacao, $idfluxostatus, $idunidade, 'cotacao', 'INICIO', '0', $tiponf, $usuario);
                $arrayLog = [
                    "idempresa" => cb::idempresa(),
                    "sessao" => '',
                    "tipoobjeto" => 'nf',
                    "idobjeto" => $newidnf,
                    "tipolog" => 'unidadeNf',
                    "log" => $idunidade,
                    "status" => 'ERRO',
                    "info" => 'Inserção de Itens Cadastrados',
                    "criadoem" => SYSDATE(),
                    "data" => SYSDATE(),
                    "usuario" => $_SESSION["SESSAO"]["USUARIO"]
                ];
                CotacaoController::inserirLog($arrayLog);

                if($newidnf)
                {
                    //LTM - 31-03-2021: Insere o fluxo
                    FluxoController::inserirFluxoStatusHist('nfentrada', $newidnf, $idfluxostatus, 'PENDENTE');      
                    $recebcalc = date('Y-m-d', strtotime("+".$rowd['diasentrada']." days", strtotime($rowd['dtemissaoorig'])));
                    CotacaoController::inserirIdNfContaPagarDataReceb($newidnf, $_idempresa, $usuario, $recebcalc, 1);
                }

                $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idnf'] = $newidnf; 
            }

            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['tiponf'] = 'C';
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idprodservforn'] = $rowd["idprodservforn"]; 
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idempresa'] = $_idempresa;                
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['un'] = $un; 
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['nfe'] = 'Y';
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['qtd'] = $_qtd;
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['qtdsol'] = $_qtd;
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idprodserv'] = $idprodserv;
            if(!empty($idprodserv)){
                $idtipoprodserv = traduzid('prodserv', 'idprodserv', 'idtipoprodserv', $idprodserv);
                $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idtipoprodserv'] = $idtipoprodserv;
            }
            if(!empty($rowd['idgrupoes']))
            {
                $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idcontaitem'] = $rowd['idgrupoes'];
            }
            $i = $i++;
            $key = $key + $i;
        }      

    }//if($iu=='i' and !empty($value['i']['nfitem']['qtd'])){ 

    montatabdef();   
}//while (list($key, $value) = each($arrpostbuffer)) {

//Duplicar Itens
if(!empty($_SESSION['arrpostbuffer']['x']['u']['nf']['idnf']) && $_POST['duplicar'] == 'Y')
{  
    $arrNfitem = array();
    foreach($_POST as $k => $v) 
    {
        if(preg_match("/_(\d*)#(.*)/", $k, $res))
        {           
            $arrNfitem[$res[1]][$res[2]]=$v;
        }
    }  
  
    $_idnf = $_SESSION['arrpostbuffer']['x']['u']['nf']['idnf'];
    
    $nfs = CotacaoController::buscarNfPorIdnf($_idnf);
    $idtipounidade = CotacaoController::buscarIdTipoUnidade($nfs['tiponf']);
    $rwUnid = CotacaoController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);

    //LTM - 31-03-2021: Retorna o Idfluxo nf
    $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');
    $arrayInsertNf = [
        "idpessoa" => $_idpessoa_novo,
        "idempresa" => $_idempresa,
        "idobjetosolipor" => $nfs['idobjetosolipor'],
        "tipoobjetosolipor" => $nfs['tipoobjetosolipor'],
        "status" => 'INICIO',
        "idfluxostatus" => $idfluxostatus,
        "tpnf" => $nfs['tpnf'],
        "tiponf" => $nfs['tiponf'],
        "idunidade" => $rwUnid["idunidade"],
        "usuario" => $usuario 
    ];

    $newidnf = CotacaoController::inserirNfDuplicada($arrayInsertNf);

    $arrayLog = [
        "idempresa" => cb::idempresa(),
        "sessao" => '',
        "tipoobjeto" => 'nf',
        "idobjeto" => $newidnf,
        "tipolog" => 'unidadeNf',
        "log" => $idunidade,
        "status" => 'ERRO',
        "info" => 'Inserção de Itens Duplicados',
        "criadoem" => SYSDATE(),
        "data" => SYSDATE(),
        "usuario" => $_SESSION["SESSAO"]["USUARIO"]
    ];
    CotacaoController::inserirLog($arrayLog);
    
    if($newidnf)
    {
        //LTM - 31-03-2021: Insere o fluxo
        FluxoController::inserirFluxoStatusHist('nfentrada', $newidnf, $idfluxostatus, 'PENDENTE');        
        $recebcalc = date('Y-m-d', strtotime("+".$nfs['diasentrada']." days", strtotime($nfs['dtemissaoorig'])));
        CotacaoController::inserirIdNfContaPagarDataReceb($newidnf, $_idempresa, $usuario, $recebcalc, 1);
    }
    
    //$newidnf = mysqli_insert_id(d::b());
    $key = 999;
    foreach($arrNfitem as $k => $v)
    {          
        $nfItem = CotacaoController::buscarDadosNfItemPorIdNfItem($v['idnfitem']);
        $key = $key + 1;

        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idnf'] = $newidnf; 
        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idempresa'] = $_idempresa;  
        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['tiponf'] = 'C'; 
        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['qtd'] = $v['quantidade'];
        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['qtdsol'] = $v['quantidade'];

        //Validação realizada para quando o Produto for cadastrado manual e não tem idprodserv. Será cadastrado apenas o nome prodservdescr ao invés do ID
        //Lidiane (26/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=328433
        if($nfItem['idprodserv'] == NULL){
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['prodservdescr'] = $nfItem['prodservdescr'];                       
        } else {
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idprodservforn'] = $nfItem['idprodservforn'];
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idprodserv'] = $nfItem['idprodserv'];
            $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idtipoprodserv'] = $nfItem['idtipoprodserv'];
        }
        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['un'] = $nfItem['un'];
        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['idcontaitem'] = $nfItem['idcontaitem'];
        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['vlritem'] = $nfItem['vlritem'];
        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['obs'] = $nfItem['obs'];
        $_SESSION['arrpostbuffer'][$key]['i']['nfitem']['nfe'] = 'Y';
            
    }
//Insere o item na Cotação correspondente.
}elseif(!empty($_SESSION['arrpostbuffer']['x']['i']['nfitem']['idprodserv']) && !empty($_SESSION['arrpostbuffer']['x']['i']['nfitem']['idnf'])){
    $vidprodserv = $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idprodserv'];
    $vidnf = $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idnf'];
   
    if(!empty($vidprodserv))
    {
		$idtipoprodserv = traduzid('prodserv', 'idprodserv', 'idtipoprodserv', $vidprodserv);
		$_SESSION['arrpostbuffer']['x']['i']['nfitem']['idtipoprodserv'] = $idtipoprodserv;
    }

    $rowProdserv = CotacaoController::buscarFornecedoresPertencentesIdnf($vidnf, $vidprodserv);
    $qtd = count($rowProdserv);

    if($rowProdserv['converteest'] == 'Y' && $rowProdserv['tipo'] == 'PRODUTO'){
        $un = $rowProdserv['unforn'];
        if(empty($rowProdserv['valconv'])){
            die("[Erro] Verifique o valor de conversão do produto ".$rowd['descr']."<br><a href='?_modulo=prodserv&_acao=u&idprodserv=".$vidprodserv."' target='_blank'>Ajustar</a>");
        }
    }else{
        $un = $rowProdserv['un'];
    }
   
    if($qtd > 0)
    {
        if(empty($_SESSION['arrpostbuffer']['x']['i']['nfitem']['idprodservforn'])){
            $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idprodservforn'] = $rowProdserv['idprodservforn'];
        }
        if(empty($_SESSION['arrpostbuffer']['x']['i']['nfitem']['un'])){
            $_SESSION['arrpostbuffer']['x']['i']['nfitem']['un'] = $un;
        }
        $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idcontaitem'] = $rowProdserv['idgrupoes'];
        $_SESSION['arrpostbuffer']['x']['i']['nfitem']['tiponf'] = 'C';
    }
}

//Deslocar Item
if(!empty($_SESSION['arrpostbuffer']['nova']['u']['nfitem']['idnfitem']))
{    
    $_idnfitem = $_SESSION['arrpostbuffer']['nova']['u']['nfitem']['idnfitem'];
    
    $nfAtual = CotacaoController::buscarDadosNfPorIdNfItem($_idnfitem);

    //procura uma cotação mais antiga
    $novaIdNf = CotacaoController::buscarNfPorIdNfDeslocamento($nfAtual['idobjetosolipor'], $nfAtual['tipoobjetosolipor'], $nfAtual['idpessoa'], '<', $nfAtual['idnf']);
    $qtdnf = (count($novaIdNf) > 0 && $novaIdNf != "") ? count($novaIdNf) : 0;
    //se nçai achar uma antiga ele busca uma mais nova
    if($qtdnf < 1)
    {
        $novaIdNf = CotacaoController::buscarNfPorIdNfDeslocamento($nfAtual['idobjetosolipor'], $nfAtual['tipoobjetosolipor'], $nfAtual['idpessoa'], '>', $nfAtual['idnf']);
        $qtdnf = (count($novaIdNf) > 0 && $novaIdNf != "") ? count($novaIdNf) : 0;
    }

    if($qtdnf < 1)
    {
        $idtipounidade = CotacaoController::buscarIdTipoUnidade($nf['tiponf']);
        $rwUnid = CotacaoController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);

        //LTM - 31-03-2021: Retorna o Idfluxo nf
        $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'INICIO');

        if($nfAtual["idunidade"] != 'V'){
            $idunidade = $nfAtual["idunidade"]; 
        } else {
            $idunidade = 'NULL';
        }

        $newidnf = CotacaoController::inserirNf($nfAtual['idpessoa'], $_idempresa, $nfAtual['idobjetosolipor'], $idfluxostatus, $idunidade, $nfAtual['tipoobjetosolipor'], 'INICIO', $nfAtual['tpnf'], $nfAtual['tiponf'], $usuario);

        $arrayLog = [
            "idempresa" => cb::idempresa(),
            "sessao" => '',
            "tipoobjeto" => 'nf',
            "idobjeto" => $newidnf,
            "tipolog" => 'unidadeNf',
            "log" => $idunidade,
            "status" => 'ERRO',
            "info" => 'Inserção de Itens Deslocados',
            "criadoem" => SYSDATE(),
            "data" => SYSDATE(),
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ];
        CotacaoController::inserirLog($arrayLog);

        if($newidnf)
        {
            //LTM - 31-03-2021: Insere o fluxo
            FluxoController::inserirFluxoStatusHist('nfentrada', $newidnf, $idfluxostatus, 'PENDENTE');  
            $recebcalc = date('Y-m-d', strtotime("+".$nfAtual['diasentrada']." days", strtotime($nfAtual['dtemissaoorig'])));
            if(!empty($recebcalc))
            {
                CotacaoController::inserirIdNfContaPagarDataReceb($newidnf, $_idempresa, $usuario, $recebcalc, 1);    
            }                  
        }

    }else {
        $newidnf = $novaIdNf['idnf'];
    }

    $_SESSION['arrpostbuffer']['nova']['u']['nfitem']['idnf'] = $newidnf;    
}

if($status == 'CANCELADO' && !empty($idcotacao))
{
    $idfluxostatus = FluxoController::getIdFluxoStatus('nfentrada', 'CANCELADO');
    CotacaoController::atualizarNfParaCanceladoComStatusDiferenteConcluido($idfluxostatus, $idcotacao, 'cotacao');
}

//Gerar a configuração das parcelas 
$idnfparc = $_SESSION['arrpostbuffer']['parc']['u']['nf']['idnf'];
$parc = $_SESSION['arrpostbuffer']['parc']['u']['nf']['parcelas'];
$dtemissao = $_SESSION['arrpostbuffer']['parc']['u']['nf']['dtemissao'];
$diasentrada = $_SESSION['arrpostbuffer']['parc']['u']['nf']['diasentrada'];
$intervalo = $_SESSION['arrpostbuffer']['parc']['u']['nf']['intervalo'];
$intervaloant = $_POST['intervaloant'];
if(!empty($idnfparc) && !empty($parc) && (($parc == 1) || ($parc > 1 && !empty($intervalo))))
{
    CotacaoController::atualizarProporcaoNfConfPagar($idnfparc);
    $confPagar = CotacaoController::buscarIdNfConfPagar($idnfparc);
    $qtd = count($confPagar);
    $difdias = 0;
    $strintervalo = 'days';
    $dtemissaoAm = explode("/", $dtemissao);
    $dtemissaoAm = $dtemissaoAm[2]."-".$dtemissaoAm[1]."-".$dtemissaoAm[0];
    
    if($qtd > $parc)
    {
        foreach($confPagar as $_dadosConfPagar)
        {
            CotacaoController::apagarNfConfPagar($_dadosConfPagar['idnfconfpagar']);
            $qtd = $qtd - 1;
            if($qtd == $parc)
            {
                break; 
            }
        }
    }elseif($qtd < $parc){
        if(($qtd > 0) || $intervalo != $intervaloant)
        {
            CotacaoController::apagarNfConfPagarPorIdnf($idnfparc);
        }

        for($index = 1; $index <= $parc; $index++) 
        {
            if ($index == 1) {
                $valintervalo = $diasentrada;
                $diareceb = $diasentrada + $difdias;
                $vencimentocalc = date('Y-m-d', strtotime("+".$diasentrada." $strintervalo", strtotime($dtemissaoAm)));                    
                $recebcalc = date('Y-m-d', strtotime("+$diareceb $strintervalo", strtotime($dtemissaoAm)));
            } else {
                $valintervalo = $valintervalo + $intervalo;
                $diareceb = $valintervalo + $difdias;                                       
                $recebcalc = date('Y-m-d', strtotime("+$diareceb $strintervalo", strtotime($dtemissaoAm)));
                $diaSemana = date('w', strtotime($recebcalc));
                if ($diaSemana == 0) { //Se for domingo aumenta 1 dia
                    $timestemp = strtotime(date('Y-m-d', strtotime($recebcalc)) . "+1 days");
                    $recebcalc = date('Y-m-d', $timestemp);
                } elseif ($diaSemana == 6) { //Se for sabado aumenta 2 dias
                    $timestemp = strtotime(date('Y-m-d', strtotime($recebcalc)) . "+2 days");
                    $recebcalc = date('Y-m-d', $timestemp);
                }
            }

            CotacaoController::inserirIdNfContaPagarDataReceb($idnfparc, $_idempresa, $usuario, $recebcalc, $index);
        }
    } 
} elseif($parc > 1 && empty($intervalo)) {
    echo 'Favor Definir o Intervalo das Parcelas:';
    die();
}//if(!empty($idnfparc) and !empty($parc)){


if($_POST['solcom'] == 'Y')
{
    $i = 0;
    $arrSolcom = $_SESSION["arrpostbuffer"];
    reset($arrSolcom);
    foreach($arrSolcom as $linha => $arrlinha)
    {
        foreach($arrlinha as $acao => $arracao)  
        {
            foreach($arracao as $tab => $arrtab)
            {
                $idsolcomitem = $arrtab['idsolcomitem'];
                $rowSolcom = CotacaoController::buscarQuantidadeItensSolcomPorIdSolcolmItem($idsolcomitem);
                $qtdSolcom = count($rowSolcom);

                if($qtdSolcom == 1)
                {
                    $_SESSION['arrpostbuffer']['solcom'.$i]['u']['solcom']['idsolcom'] = $rowSolcom[0]['idsolcom'];
                    $_SESSION['arrpostbuffer']['solcom'.$i]['u']['solcom']['status'] = 'REPROVADO';

                    $rowFluxo = FluxoController::getFluxoStatusHist('solcom', 'idsolcom', $rowSolcom[0]['idsolcom'],'REPROVADO');
                    FluxoController::alterarStatus('solcom', 'idsolcom', $rowSolcom[0]['idsolcom'], $rowFluxo['idfluxostatushist'], $rowFluxo['idfluxostatus'], $rowFluxo['statustipo'], null, 0, $rowFluxo['idfluxostatus'], $rowFluxo['idfluxo'], $rowFluxo['ordem'], $rowFluxo['tipobotao']);	
                }

                $_SESSION['arrpostbuffer']['modulocom'.$i]['i']['modulocom']['idmodulo'] = $idsolcomitem;
                $_SESSION['arrpostbuffer']['modulocom'.$i]['i']['modulocom']['modulo'] = 'solcomitem';
                $_SESSION['arrpostbuffer']['modulocom'.$i]['i']['modulocom']['descricao'] = 'CANCELAMENTO DE ITEM DE COMPRA';
                $_SESSION['arrpostbuffer']['modulocom'.$i]['i']['modulocom']['idempresa'] = $rowSolcom[0]['idempresa'];
                $_SESSION['arrpostbuffer']['modulocom'.$i]['i']['modulocom']['status'] = 'ATIVO';

                $i++;
            }
        }
    }
}

$idobjetosolipor = $_SESSION['arrpostbuffer']['x']['i']['nf']['idobjetosolipor'];
$tipoobjetosolipor = $_SESSION['arrpostbuffer']['x']['i']['nf']['tipoobjetosolipor'];
$tiponf = $_SESSION['arrpostbuffer']['x']['i']['nf']['tiponf'];
if(!empty($idobjetosolipor) && $tipoobjetosolipor == 'nf' && $tiponf == 'T') // gerar CTe vinculado
{
    cnf::$idempresa = $_idempresa;
    $modulo = 'nfcte';
    $idtipounidade = 21;
    $unidade = CotacaoController::buscarIdunidadePorTipoUnidade($idtipounidade, $_idempresa);
    $nf = CotacaoController::buscarNfPessoaPorIdNf($idobjetosolipor)[0];

    if($nf['frete'] < 1)
    {
        $nf['frete'] = $nf['total'] * 0.02;
    }

    if($nf['tiponf'] == 'V'){
        $arrconfCP = cnf::getDadosConfContapagar('CTE-ENVIO');
    }else{
        $arrconfCP = cnf::getDadosConfContapagar('CTE-SUPRIMENTOS');
    }

    $idfluxostatus = FluxoController::getIdFluxoStatus($modulo, 'PREVISAO');
    $_SESSION['arrpostbuffer']['x']['i']['nf']['status'] = 'PREVISAO';

    //LTM - 31-03-2021: Retorna o Idfluxo nf
    
    $idunidade = 21;

    if(!empty($nf['idtransportadora'])){
        $idpessoa = $nf['idtransportadora'];
    }elseif(!empty($arrconfCP['idpessoa'])){
        $idpessoa = $arrconfCP['idpessoa'];
    }else{
        $idpessoa = $nf['idpessoa'];
    }

    $newidnf = CotacaoController::inserirNfTransportadora($idpessoa, $_idempresa, $idobjetosolipor, $idfluxostatus, $unidade['idunidade'], 'nf', 'PREVISAO', 'T', $usuario, $nf['previsaoentrega'], $arrconfCP['idformapagamento'], $nf['frete'], $nf['frete'], 1, $nf['dtemissao']);
    if($newidnf)
    {
        //LTM - 31-03-2021: Insere o fluxo
        FluxoController::inserirFluxoStatusHist($modulo, $newidnf, $idfluxostatus, 'PENDENTE');

        $arrayLog = [
            "idempresa" => cb::idempresa(),
            "sessao" => '',
            "tipoobjeto" => 'nf',
            "idobjeto" => $newidnf,
            "tipolog" => 'unidadeNf',
            "log" => $idunidade,
            "status" => 'ERRO',
            "info" => 'Inserção de CTE',
            "criadoem" => SYSDATE(),
            "data" => SYSDATE(),
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ];
        CotacaoController::inserirLog($arrayLog);
    }

    unset($_SESSION["arrpostbuffer"]["x"]);

    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idnf'] = $newidnf;
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['qtd'] = 1;
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idempresa'] = $_idempresa;
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['vlritem'] = $nf['frete'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['total'] = $nf['frete'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['obs'] = $nf['idnfe'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['nfe'] = 'Y';
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['tiponf'] = 'T';
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['prodservdescr'] = $nf['nome'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idcontaitem'] = $arrconfCP['idcontaitem'];
    $_SESSION['arrpostbuffer']['x']['i']['nfitem']['idtipoprodserv'] = $arrconfCP['idtipoprodserv'];

    $arrinsnfcp[1]['idnf'] = $newidnf;	
    $arrinsnfcp[1]['parcela'] = 1;
    $arrinsnfcp[1]['idformapagamento'] = $arrconfCP['idformapagamento'];
    $arrinsnfcp[1]['proporcao'] = 100;
    $arrinsnfcp[1]['datareceb'] = $nf['dtemissao'];    

    $idnfconfpagar = cnf::inseredb($arrinsnfcp, 'nfconfpagar');

    cnf::atualizafat($newidnf, $arrconfCP['idformapagamento']);
}// gerar CTe vinculado

$prazo=$_SESSION['arrpostbuffer']['1']['u']['cotacao']['prazo'];
$prazo_old=$_POST['cotacao_prazo_old'];
if(!empty($prazo) and !empty($prazo_old) and $prazo_old != $prazo )
{
    $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['valor'] = $prazo;
    $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['valor_old'] = $prazo_old;
    $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'] = $_SESSION['arrpostbuffer']['1']['u']['cotacao']['idcotacao'];
    $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['tipoobjeto'] = 'cotacao';
 
    montatabdef();   
}

//dividir o frete nos itens da compra
if(!empty($_SESSION['arrpostbuffer']['atfrete']['u']['nf']['idnf']))
{
    $idnf = $_SESSION['arrpostbuffer']['atfrete']['u']['nf']['idnf'];
    $frete = $_SESSION['arrpostbuffer']['atfrete']['u']['nf']['frete'];
    $frete = number_format(tratanumero($frete), 4, '.','');

    $sql="select i.idnfitem,round((i.total/n.subtotal)*". $frete.",2)  as novofrete 
        from nfitem i join nf n on(n.idnf=i.idnf)
        where i.nfe ='Y' and  i.idnf=".$idnf;
    $res = d::b()->query($sql) or die("[prechangepedido][3]: Erro ao calcular frete para os itens. SQL: ".$sql);
    $l = 0;
    while($row = mysqli_fetch_assoc($res))
    {
        $l++;
        $_SESSION['arrpostbuffer']['atfrete'.$l]['u']['nfitem']['idnfitem'] = $row['idnfitem'];
        $_SESSION['arrpostbuffer']['atfrete'.$l]['u']['nfitem']['frete'] = $row['novofrete'];
    }
}

$idnfitemx = $_SESSION['arrpostbuffer']['x']['d']['nfitem']['idnfitem'];
$parametros = $_POST['parametros'];
$statusCancelamento = ['CANCELADO', 'REPROVADO'];
if((!empty($idnfitemx) && !empty($_POST['idsolcom'])) || (in_array($_POST['_status'], $statusCancelamento) && !empty($parametros)))
{   
    $idcotacao = $_POST['idcotacao'];
    if($parametros)
    {
        $solcomProdserv = explode(",", $parametros);
        foreach($solcomProdserv AS $_dados)
        {
            $_dados = explode("-", $_dados);
            $idsolcom = $_dados[0];
            $idprodserv = $_dados[1];
            $verificaItemSolcom = CotacaoController::buscarSolcomQuantidadeItensSolcomCotacao($idsolcom, $idprodserv, $idcotacao);
            foreach($verificaItemSolcom as $_solcom)
            {   
                if($_solcom['count'] == 1)
                {
                    CotacaoController::atualizarStatusIdcotacaoSolcomItem(NULL, 'PENDENTE', $_solcom['idsolcomitem'], $_SESSION["SESSAO"]["USUARIO"]);   
                }
            }  
        }

    } else {
        $idsolcom = $_POST['idsolcom'];
        $idprodserv = $_POST['idprodserv'];

        $verificaItemSolcom = CotacaoController::buscarSolcomQuantidadeItensSolcomCotacao($idsolcom, $idprodserv, $idcotacao);
        foreach($verificaItemSolcom as $_solcom)
        {   
            if($_solcom['count'] == 1)
            {
                CotacaoController::atualizarStatusIdcotacaoSolcomItem(NULL, 'PENDENTE', $_solcom['idsolcomitem'], $_SESSION["SESSAO"]["USUARIO"]);   
            }
        }   
    }      
}

$idCotacaoMigracao = $_SESSION['arrpostbuffer']['1']['u']['nf']['idobjetosolipor'];
if($_POST['_migrar_cotacao'] == 'Y' && !empty($idCotacaoMigracao))
{
	$idnf = $_SESSION['arrpostbuffer']['1']['u']['nf']['idnf'];    
    $nfItens = CotacaoController::buscarProdutoNfPorIdNf($idnf);
    foreach($nfItens as $rwNfItens)
    {
        CotacaoController::atualizarSolcomItensAssociados($idCotacaoMigracao, $rwNfItens['idobjetosolipor'], $rwNfItens['idprodserv'], $_SESSION["SESSAO"]["USUARIO"]);
        CotacaoController::aturalizarMailFilaPorSubTipoIdSubTipoObjeto($idCotacaoMigracao, $idnf, 'nf', 'cotacao');
    }  
}



//Gerar a configuração das parcelas 
function geranfconfpagar($idnfparc)
{
    $usuario = $_SESSION["SESSAO"]["USUARIO"];
    $arrNF=getObjeto("nf",$idnfparc,"idnf");

    $parc = $arrNF['parcelas'];
    $dtemissao = $arrNF['dtemissao'];
    $diasentrada = $arrNF['diasentrada'];
    $intervalo = $arrNF['intervalo'];
    $_idempresa = $arrNF['idempresa'];
    $valintervalo = 0;   
  
    $difdias = 0;
    $strintervalo = 'days';
    $dtemissaoAm =$dtemissao;

        for($index = 1; $index <= $parc; $index++) 
        {
            if ($index == 1) {
                $valintervalo = $diasentrada;
                $diareceb = $diasentrada + $difdias;                
                $recebcalc = date('Y-m-d', strtotime("+$diareceb $strintervalo", strtotime($dtemissaoAm)));
                $eFeriado = 1;

                WHILE ($eFeriado >= 1) {
                    
                    $rowdia =  NFController::verificaFeriadoFds(date('Y-m-d', $recebcalc));
                                                        
                    IF($rowdia['eFeriado'] == 1) {
                        $timestemp = strtotime(date('Y-m-d', strtotime($recebcalc)) . "+1 days");
                        $recebcalc = date('Y-m-d', $timestemp);
                        $eFeriado = 1;
                    }else{
                        $eFeriado = 0;
                    }                      
                }
                
            } else {
                $valintervalo = $valintervalo + $intervalo;
                $diareceb = $valintervalo + $difdias;                                       
                $recebcalc = date('Y-m-d', strtotime("+$diareceb $strintervalo", strtotime($dtemissaoAm)));

                $eFeriado = 1;

                WHILE ($eFeriado >= 1) {
                    
                    $rowdia =  NFController::verificaFeriadoFds(date('Y-m-d', $recebcalc));
                                                        
                    IF($rowdia['eFeriado'] == 1) {
                        $timestemp = strtotime(date('Y-m-d', strtotime($recebcalc)) . "+1 days");
                        $recebcalc = date('Y-m-d', $timestemp);
                        $eFeriado = 1;
                    }else{
                        $eFeriado = 0;
                    }                      
                }
            }

            CotacaoController::inserirIdNfContaPagarDataReceb($idnfparc, $_idempresa, $usuario, $recebcalc, $index);
        }
    
}

?>