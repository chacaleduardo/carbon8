<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/gerconcentradols_controller.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

$idpessoa = $_GET["idpessoa"];
$idprodserv = $_GET["idprodserv"];
$status = $_GET["status"];
$tipo = $_GET["tipo"];

$_modulo = $_GET['_modulo'];

if (!empty($idpessoa)) {
    $clausulalote .= " AND pf.idpessoa = ".$idpessoa;
} else {
    $clausulalote = "";
}

if (!empty($idprodserv)) {
    $clausulad .= " AND  fi.idprodserv =  ".$idprodserv;
} else {
    $clausulad = "";
}

$jCli = GerconcentradolsController::listarPessoaVinculadaLote();
$jProd = GerconcentradolsController::buscarProdutoServicoEspecialVinculadoProdservPrProc();
?>

<link href="../form/css/gerconcentradols_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Pesquisar</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-1">Cliente:</div>
                    <div class="col-md-5">
                        <input id="idpessoa" type="text" name="idpessoa" cbvalue="<?=$idpessoa?>" value="<?=$jCli[$idpessoa]["nome"]?>" style="width: 40em;" vnulo>
                    </div>
                    <div class="col-md-1">Status:</div>
                    <div class="col-md-5">
                        <select class='size10' name="status" id="status">
                            <? fillselect(GerconcentradolsController::$_status, $status); ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-1">Produto:</div>
                    <div class="col-md-5">
                        <input id="idprodserv" type="text" name="idprodserv" cbvalue="<?=$idprodserv ?>" value="<?=$jProd[$idprodserv]["descr"] ?>" style="width: 40em;" vnulo>
                    </div>
                    <div class="col-md-1">Tipo:</div>
                    <div class="col-md-5">
                        <select class='size10' name="tipo" id="tipo">
                            <? fillselect(GerconcentradolsController::$_tipo, $tipo); ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><br>
                        <p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-1">
                        <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
                            <span class="fa fa-search"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?
/*
 * colocar condição para executar select
 */
if ($_GET && (!empty($idpessoa) || !empty($idprodserv) || !empty($status))) 
{
?>
    <div id="obsinicio"></div>
    <?
    $haproduzir = 0;
    $preto = 0;
    $roxo = 0;
    $azul = 0;
    $laranja = 0;

    if ($tipo == 'PROGRAMADO') {
        $listarProdserv = GerconcentradolsController::buscarProdutosProgramadosGerenciamentoConcentrados($clausulalote, $clausulad);
    } elseif ($tipo == 'PEDIDO') {
        $listarProdserv = GerconcentradolsController::buscarProdutosPedidosGerenciamentoConcentrados($clausulalote, $clausulad);
    } else {
        $listarProdserv = GerconcentradolsController::buscarProdutosProgramadosPedidosGerenciamentoConcentrados($clausulalote, $clausulad);
    }
    echo ("<!-- ProdServ: <br/> ".$listarProdserv['sql']." -->");

    if($listarProdserv['qtdLinhas'] > 0) 
    {
        $semestoque = 0;
        foreach ($listarProdserv['dados'] as $_prodserv) 
        {
            if ($_prodserv['idprodserv'] != $idprodservlop and !empty($idprodservlop)) 
            {
                echo ('<span>Total Demanda: <b>' . recuperaExpoente(tratanumero($_SESSION['preciso'][$idprodservlop]), $_SESSION['preciso_exp'][$idprodservlop]) . '</b></span>');
                ?>
                </div>
                </div>
                </div>
                </div>
                <?
            }
            
            if ($_prodserv['idprodserv'] != $idprodservlop) 
            {
                $_SESSION['preciso'][$_prodserv['idprodserv']] = 0;
                if ($semestoque == 0 && !empty($idprodservlop)) 
                {
                    ?>
                    <div class='ocultar_pessoa' id='<?=$_prodserv['idprodserv'] ?>'></div>
                    <?
                }
                $semestoque = 0;
                $idprodservlop = $_prodserv['idprodserv'];
                ?>
                <div class="row <?=$_prodserv['idprodserv'] ?>">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <label class="idbox"><?=$_prodserv['descr']?></label>
                            </div>
                            <div class="panel-body">
            <?
            }
            ?>
            <table class="table table-striped planilha <?=$_prodserv['idpessoa'] ?>_<?=$_prodserv['idprodserv'] ?>">
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default topPanel">
                                    <div class="panel-heading"><?=$_prodserv['plantel'] ?> - <?=$_prodserv['nome'] ?> - <?=dma($_prodserv['envio'])?>
                                        <? if ($_prodserv['idnf'] != ''){ ?>
                                            - <a  href="javascript:janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$_prodserv['idnf']?>')">PEDIDO: <?=$_prodserv['idnf']?></a>
                                        <? } 
                                        if ($_prodserv['idformalizacao'] != ''){ ?>
                                            - <a  href="javascript:janelamodal('?_modulo=formalizacao&_acao=u&idformalizacao=<?=$_prodserv['idformalizacao']?>')">OP: <?=$_prodserv['idformalizacao']?></a>
                                        <? } ?>
                                        <a class="fa fa-search tip">
                                            <span class="<?=$_prodserv['idpessoa'] ?>__<?=$_prodserv['idprodserv'] ?> informacoesLote"></span>
                                        </a>
                                    </div>
                                    <!-- Listar Concentrados -->
                                    <div class="panel-body">
                                        <?
                                        if ($tipo == 'PROGRAMADO') {
                                            $listarVacinas = GerconcentradolsController::buscarConcenteradosProgramado($_prodserv['idpessoa'], $_prodserv['idprodserv'], $clausulalote, $clausulad);
                                        } elseif ($tipo == 'PEDIDO') {
                                            $listarVacinas = GerconcentradolsController::buscarConcenteradosPedido($_prodserv['idpessoa'], $_prodserv['idprodserv'], $clausulalote, $clausulad);
                                        } else {
                                            $listarVacinas = GerconcentradolsController::buscarConcenteradosProgramadoPedido($_prodserv['idpessoa'], $_prodserv['idprodserv'], $clausulalote, $clausulad);
                                        }
                                        echo ("<!-- Concentrados: <br />".$listarVacinas['sql']." -->");
                                        $preciso = 0;
                                        $nvacinasprog = "";
                                        foreach($listarVacinas['dados'] as $_vacinas)
                                        {
                                            if($tipo == 'TODOS') 
                                            {
                                                $qtdLote = GerconcentradolsController::buscarQtdLoteAbertoAutorizada($_vacinas["idpessoa"], $_vacinas["idprodservvacina"], $_vacinas["idprodservformula"]);
                                                echo ("<!-- qtdLote ".$qtdLote['sql']." -->");
                                                if ($qtdLote['qtd'] > $_vacinas['qtd']) 
                                                {
                                                    $_vacinas['qtd'] = $qtdLote['qtd'];
                                                    $_vacinas['tipopendencia'] = 'PEDIDO';
                                                }
                                            }

                                            //$_vacinas['qtd'] = $_vacinas['qtd'] + ($_vacinas['qtd'] * 0.1); // ADICIONAR 10 POR CENTO
                                            
                                            //Buscar quanto e necessario para produzir o concentrado
                                            $preciso = $preciso + GerconcentradolsController::calculapreciso($_vacinas['qtdi'], $_vacinas['qtdi_exp'], $_vacinas['qtd'], $_vacinas['qtdpadrao']);
                                            $Vqtddisp_exp = $_vacinas['qtdpadrao_exp'];
                                            $_SESSION['preciso_exp'][$_prodserv['idprodserv']] = $Vqtddisp_exp;
                                            $nvacinasprog .= $_vacinas['qtd'] . " FR: ".$_vacinas['descrcurta'] . " - ".$_vacinas['rotulo'] . " &emsp;&emsp; <font color='red'>".$_vacinas['tipopendencia'] . "</font><hr>";
                                        }

                                        //---- Listar as Sementes
                                        $listarSementes = GerconcentradolsController::buscarSementes($_prodserv['idpessoa'], $_prodserv['idprodserv']);
                                        echo ("<!-- Sementes: <br /> ".$listarSementes['sql']." -->");
                                        $qtdsementes = 0;
                                        $qtdpool = 0;
                                        $arrSementesLote = array();
                                        $arrPool = array();
                                        $arrLote = array();
                                        foreach($listarSementes['dados'] as $_sementes)
                                        {
                                            //quais concentrados a semente tem
                                            $qtdispsem = 0;
                                            $concentrados = GerconcentradolsController::buscarConcentradosSementes($_sementes["idlote"]);
                                            foreach($concentrados['dados'] as $_dadosConcentrados)
                                            {
                                                //quantas sementes tem no concentrado
                                                $qtdConcentrados = GerconcentradolsController::buscarQuantidadeConcentradosSementes($_dadosConcentrados["idlote"]);
                                                $qtdsementes = $qtdConcentrados['qtdLinhas'];

                                                $valDisp = $_dadosConcentrados['qtddisp']; 
                                                $qtdispsem = $qtdispsem + ($valDisp / $qtdsementes);
                                                $dispsemen_exp = $_dadosConcentrados['qtdpadrao_exp'];
                                            }

                                            $arrLote[$_sementes['idlote']] = $_sementes['idlote'];
          
                                            if(($_sementes['idpool'] != $idpool) && (!empty($idpool))){            
                                                $qtdpool = $qtdpool + 1;            
                                            }elseif(empty($_sementes['idpool'])){
                                                $qtdpool = $qtdpool + 1; 
                                            }
                                            $idpool = $_sementes['idpool'];
                                                    
                                            //para cada coluna resultante do select cria-se um item no array
                                            //Esta parte traz as sementes ("Estrela: Alerta) partida/exercicio - qtd)
                                            $arrSementes = $listarSementes['dados'];
                                            foreach($arrSementes as $sementes) 
                                            {
                                                foreach($sementes as $chave => $_dados)
                                                {
                                                    $arrSementesLote[$_sementes['idlote']][$chave] = $_sementes[$chave];
                                                }
                                               
                                                $arrSementesLote[$_sementes['idlote']]['pool'] = $qtdpool;
                                                $arrSementesLote[$_sementes['idlote']]['dispsemen'] = $qtdispsem;
                                                $arrSementesLote[$_sementes['idlote']]['dispsemen_exp'] = $dispsemen_exp;                                            
                                            }
                                            $arrPool[$qtdpool][$_sementes['idlote']] = $_sementes['idlote'];
                                        }

                                        $nporpool = $preciso / count($arrPool);//necessidade por pool

                                        foreach($arrSementesLote as $key => $value) 
                                        {       
                                            $keypool = $arrSementesLote[$key]['pool'];    
                                            $arrSementesLote[$key]['deficit'] = $nporpool / count($arrPool[$keypool], COUNT_RECURSIVE);// necessidade por semente
                                            $calc = $arrSementesLote[$key]['deficit'];
                                            if($calc == 0){
                                                $calc = 1;
                                            }
                                            $arrSementesLote[$key]['perc_deficit'] = $arrSementesLote[$key]['dispsemen'] / ($calc);//estoque / demanda
                                            if($arrSementesLote[$key]['perc_deficit'] < 1){
                                                $diferenca = 1 - $arrSementesLote[$key]['perc_deficit'];                                                
                                                $produzir = $diferenca * ($nporpool / count($arrPool[$keypool], COUNT_RECURSIVE));
                                                $arrSementesLote[$key]['produzir'] = $produzir;            
                                                $arrSementesLote[$key]['descrperc_deficit'] = 'Estoque: '.recuperaExpoente(tratanumero($arrSementesLote[$key]['dispsemen']), $Vqtddisp_exp)." &emsp;&emsp; Demanda: ".recuperaExpoente(tratanumero(($nporpool / count($arrPool[$keypool], COUNT_RECURSIVE))), $Vqtddisp_exp)." &emsp;&emsp; Produzir: ".recuperaExpoente(tratanumero($produzir), $Vqtddisp_exp);//estoque / demanda
                                            }else{
                                                $arrSementesLote[$key]['produzir'] = '';            
                                                $arrSementesLote[$key]['descrperc_deficit'] = 'Estoque: '.recuperaExpoente(tratanumero($arrSementesLote[$key]['dispsemen']), $Vqtddisp_exp)." &emsp;&emsp; Demanda: ".recuperaExpoente(tratanumero(($nporpool / count($arrPool[$keypool], COUNT_RECURSIVE))), $Vqtddisp_exp);//estoque / demanda
                                            }                    

                                            $arrSementesLote[$key]['rotdeficit'] = recuperaExpoente(tratanumero($nporpool/count($arrPool[$keypool], COUNT_RECURSIVE)), $Vqtddisp_exp);// necessidade por semente
                                        }
                                        reset($arrSementesLote);
                                        reset($arrPool);
                                        ?>
                                        <div style="width: max-content;">
                                            <?
                                            if($listarSementes['qtdLinhas'] < 1)
                                            {
                                                ?>
                                                <div style='border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;'>
                                                    <?
                                                    echo   "Cliente não possui semente Aprovada.";
                                                    ?>
                                                </div>
                                                <?
                                            }

                                            foreach($arrSementesLote as $key => $value) 
                                            {            
                                                if($arrSementesLote[$key]['flgalerta'] == 'P'){ 
                                                    $estrela = 'preto';
                                                }elseif($arrSementesLote[$key]['flgalerta'] == 'R'){ 
                                                    $estrela = 'roxo';
                                                }elseif($arrSementesLote[$key]['flgalerta'] == 'A'){ 
                                                    $estrela = 'azul';
                                                }else{
                                                    $estrela = 'laranja';
                                                }
                                                ?>
                                                <div style='border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;'>
                                                    <a  href="javascript:janelamodal('?_modulo=semente&_acao=u&idlote=<?=$arrSementesLote[$key]['idlote']?>')" class="font10 tip">
                                                        <i class="fa fa-star <?=$estrela?> bold btn-lg" title="<?=$arrSementesLote[$key]['descr']?>"></i>
                                                        <?
                                                        if($arrSementesLote[$key]['vencido'] == 'Y')
                                                        {
                                                            ?>
                                                            <i class="fa fa-exclamation-triangle vermelho fa-1x pointer" title="Vence em <?=dma($arrSementesLote[$key]['vencimento'])?>."></i>
                                                            <?
                                                        }
                                                        ?>
                                                        <?=$arrSementesLote[$key]['partida']?>/<?=$arrSementesLote[$key]['exercicio']?> - <?=round($arrSementesLote[$key]['perc_deficit'],4)?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        <span class="informacoesLote"><?=$arrSementesLote[$key]['descr']?> <br> <?=$arrSementesLote[$key]['descrperc_deficit']?></span>
                                                    </a>
                                                </div>
                                                <?    
                                            }
                                            ?>
                                        </div>                                                                                                      
                                        <?
                                        //---- Listar as Sementes

                                        //---- Listar Concentrados 
                                        $lotesDisponiveis = GerconcentradolsController::buscarLotesDisponiveis($_prodserv['idpessoa'], $_prodserv['idprodserv']);
                                        echo ("<!-- qtdDisponivelLote: <br /> ".$lotesDisponiveis['sql']." -->");
                                        if($lotesDisponiveis["qtdLinhas"] > 0)
                                        {   
                                            ?>
                                            <table>
                                                <?  
                                                $tenho = 0;
                                                foreach($lotesDisponiveis['dados'] as $_lotes)
                                                {
                                                    if($_lotes['status'] == 'APROVADO' || $_lotes['status'] == 'QUARENTENA'){
                                                        $botao = 'label-success';                                                        
                                                    }else{
                                                        $botao = 'label-primary ';
                                                    }

                                                    $tenho = $tenho + $_lotes['qtddisp']; 
                                                    $qtddisp = $_lotes['qtddisp'];
                                                    $qtddisp_exp = $_lotes['qtddisp_exp'];
                                                    ?>
                                                    <tr>
                                                        <td title="<?=$_lotes['descr']?>-<?=$_lotes['status']?> <?=dma($_lotes['vencimento'])?>" colspan="2">
                                                            <span class="label <?=$botao?> fonte10 itemestoque  especial especialvisivel">
                                                                <? $idformalizacao = traduzid('formalizacao', 'idlote', 'idformalizacao', $_lotes['idlote']); ?>
                                                                <a href="?_modulo=formalizacao&_acao=u&idformalizacao=<?=$idformalizacao?>" target="_blank" style="color: inherit;">
                                                                    <?=$_lotes['partida']?>/<?=$_lotes['exercicio']?>
                                                                </a>
                                                                <?=recuperaExpoente(tratanumero($qtddisp),$qtddisp_exp)?>
                                                                <div class="insumosEspeciais" style="font-size: 10px !important;">                                                            
                                                                    <?
                                                                    $alertaTipificacao = GerconcentradolsController::buscarAlertaTipificacaoLote($_lotes["idlote"]);
                                                                    foreach($alertaTipificacao['dados'] as $_dadosAlertaTipificacao)
                                                                    {
                                                                        if(empty($_dadosAlertaTipificacao['tipificacao'])){
                                                                            $_dadosAlertaTipificacao['tipificacao'] = "SEM TIPIFICAÇÃO";
                                                                        }
                                                                        if($_dadosAlertaTipificacao['flgalerta'] == 'P'){ 
                                                                            $estrela = 'preto';                                                      
                                                                        }elseif($_dadosAlertaTipificacao['flgalerta'] == 'A'){ 
                                                                            $estrela = 'azul';                                                      
                                                                        }elseif($_dadosAlertaTipificacao['flgalerta'] == 'R'){ 
                                                                            $estrela = 'roxo';                                                      
                                                                        }else{
                                                                            $estrela = 'laranja';                                                      
                                                                        }

                                                                        if($_dadosAlertaTipificacao['status'] == "APROVADO" ){
                                                                            $idel = '';
                                                                            $fdel = '';
                                                                        }else{
                                                                            $idel = '<del>';
                                                                            $fdel = '</del>';					
                                                                        }
                                                                        ?>
                                                                        <i class="fa fa-star <?=$estrela?> bold btn-lg" title="<?=$_dadosAlertaTipificacao['tipificacao']?>"></i>
                                                                        <?=$idel?><?=$_dadosAlertaTipificacao['partida']?><?=$fdel?>	
                                                                    <?}?>
                                                                </div>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?
                                                }

                                                if($tenho < 0){
                                                    $tenho = '0.00';
                                                }

                                                if(tratanumero($tenho) < tratanumero($preciso))
                                                {
                                                    $falta = tratanumero($preciso) - tratanumero($tenho);
                                                    $_SESSION['preciso'][$_prodserv['idprodserv']] = $_SESSION['preciso'][$_prodserv['idprodserv']] + $falta;
                                                }
                                                ?>

                                                <tr>
                                                    <td>
                                                        <span class="demanda hidden" id='<?=$_prodserv['idpessoa']?>__<?=$_prodserv['idprodserv']?>'>
                                                            <?=$nvacinasprog?>
                                                            <hr>
                                                            Demanda: <b><?=recuperaExpoente(tratanumero($preciso), $Vqtddisp_exp)?></b>&emsp;&emsp;
                                                            Estoque: <b><?=recuperaExpoente(tratanumero($tenho), $Vqtddisp_exp)?></b>
                                                        </span>
                                                    </td>
                                                </tr>

                                                <? listarProdutosConcentrados($_prodserv['idpessoa'], $_prodserv['idprodserv'], $Vqtddisp_exp); ?>

                                            </table>
                                        <? } else { 
                                            $listarSementes = $arrSementes = GerconcentradolsController::buscarSementes($_prodserv['idpessoa'], $_prodserv['idprodserv']);
                                            if($listarSementes['qtdLinhas'] > 0)
                                            {
                                                $_SESSION['preciso'][$_prodserv['idprodserv']] = $_SESSION['preciso'][$_prodserv['idprodserv']] + $preciso;
                                            }

                                            $descr = traduzid('prodserv', 'idprodserv', 'descr', $_prodserv['idprodserv']);
                                            ?>
                                            <table >				
                                                <tbody>
                                                    <? listarProdutosConcentrados($_prodserv['idpessoa'], $_prodserv['idprodserv'], $Vqtddisp_exp); ?>
                                                    <tr >
                                                        <td>
                                                            <span class="demanda hidden" id='<?=$_prodserv['idpessoa']?>__<?=$_prodserv['idprodserv']?>'>
                                                                <?=$nvacinasprog?>
                                                                <hr>Demanda: <b><?=recuperaExpoente(tratanumero($preciso), $Vqtddisp_exp)?>&emsp;&emsp;
                                                                </b> Estoque:<b>0.00</b>
                                                            </span>
                                                        </td>
                                                    </tr>                                    
                                                </tbody>
                                            </table>
                                        <? } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        <?
        } //while($r= mysqli_fetch_assoc($resv)){

        if ($semestoque == 0 and !empty($idprodservlop)) 
        {
            ?>
            <div class='ocultar_pessoa' id='<?=$idprodservlop?>'></div>
            <?
        }
        echo ('<span>Total Demanda: <b>' . recuperaExpoente(tratanumero($_SESSION['preciso'][$idprodservlop]), $_SESSION['preciso_exp'][$idprodservlop]) . '</b></span>');
    } else {
        echo ("<DIV>Este produto não possui formulação com as caracteristicas da pesquisa.</DIV>");
    }
                    
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div style="display: none;" id="obsfim">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading" title="Pretas: <?=$preto?> Azuis: <?=$azul?> Laranjas: <?=$laranja?> Roxas: <?=$roxo?>">CONCENTRADOS À PRODUZIR: <?=$haproduzir?></div>
                </div>
            </div>
        </div>
    </div>
<?
} //if($_GET and !empty($clausulad)){

require_once('../form/js/gerconcentradols_js.php');
?>