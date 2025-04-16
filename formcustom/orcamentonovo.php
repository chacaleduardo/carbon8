<?
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/cotacao_controller.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 * pk: indica parâmetro chave para o select inicial
 * vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "cotacao";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idcotacao"  =>  "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "SELECT * FROM cotacao WHERE idcotacao = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$_modulo = $_GET["_modulo"];

$titulo = "Novo Orçamento de Compra";
$titulomodulo = "Orçamento de Compra";

//Retorna Formas de Pagamento
$jPag = CotacaoController::listarFormaPagamentoAtivo();

/**
 * Retorna as pessoas:
 * Tipo 1: Funcionários - NomeCurto
 * Tipo 5: Fornecedor - Nome
 */
$jFunc = CotacaoController::listarPessoaPorIdTipoPessoa(1);
$jForn = CotacaoController::listarPessoaPorIdTipoPessoa(5);

//sql que mostra o valor total do orçamento  
if(!empty($_1_u_cotacao_idcotacao)){ $valorTotalCotacao = CotacaoController::buscarValorTotalCotacao($_1_u_cotacao_idcotacao); }

$qtdempresaemail = CotacaoController::buscarQuantidadeTipoEnvioEmpesaEmails($_1_u_cotacao_idempresa);

?>
<link href="../form/css/cotacao_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
<div class="row">
    <div class="col-md-12" >
        <div class="panel panel-default" >
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-2 text-center nowrap"><strong>Orçamento:</strong>
                        <?if(!empty($_1_u_cotacao_idcotacao)){?>
                            <label class="alert-warning"><?=$_1_u_cotacao_idcotacao?></label>
                        <?}?>
                            <input id="idcotacao" name="_1_<?=$_acao?>_cotacao_idcotacao" type="hidden"	value="<?=$_1_u_cotacao_idcotacao?>">
                        </div>

                        <div class="col-md-4 text-center nowrap">Título:
                            <input name="_1_<?=$_acao?>_cotacao_titulo" class="size30" type="text" value="<?=strtoupper($_1_u_cotacao_titulo);?>" <?=$vreadonly?> vnulo>
                        </div>                  
                        <div class="col-md-2 text-center nowrap">Prazo For:
                            <?
                            if(empty($_1_u_cotacao_prazo)){
                                ?>                     
                                <input name="_1_<?=$_acao?>_cotacao_prazo" id="_1_<?=$_acao?>_cotacao_prazo" class="calendario size8" type="text" value="<?=$_1_u_cotacao_prazo?>" vnulo>
                                <?  
                            }else{
                                ?>
                                <input name="_1_<?=$_acao?>_cotacao_prazo" id="_1_<?=$_acao?>_cotacao_prazo" class="size8" readonly="readonly" style="background-color: #f2f2f2;" type="text" <?=$vnulo?> value="<?=dma($_1_u_cotacao_prazo)?>"> 
                                <i class="fa fa-pencil btn-lg pointer" title='Editar Prazo' onclick="alterarValor('prazo','<?=dma($_1_u_cotacao_prazo)?>', 'modulohistorico', <?=$_1_u_cotacao_idcotacao?>, 'Prazo')"></i>
                                <?
                            }
                            ?>          
                            
                            <input name="cotacao_prazo_old" type="hidden"  value="<?=$_1_u_cotacao_prazo?>" >

                            <?if($_acao == 'u'){ $_1_u_cotacao_status = $_1_u_cotacao_status; } else { $_1_u_cotacao_status = 'INICIO';}?>
                            <input name="_1_<?=$_acao?>_cotacao_status" type="hidden" style="width: 10px;"	value="<?=$_1_u_cotacao_status?>">

                            <?
                            if(!empty($_1_u_cotacao_idcotacao))
                            {
                                $buscarHistoricoAlteracaoPrazoCotacao = CotacaoController::buscarHistoricoAlteracaoPrazoCotacao($_1_u_cotacao_idcotacao, 'prazo');
                                if($buscarHistoricoAlteracaoPrazoCotacao != ""){
                                ?>
                                    <i title="Histórico do Prazo" class="fa btn-sm fa-info-circle preto pointer hoverazul tip" onclick="historico('prazo',<?=$_1_u_cotacao_idcotacao?>,'de Prazo');"></i>
                                <?
                                }
                                ?>
                                <div id="prazo<?=$_1_u_cotacao_idcotacao?>" style="display: none">
                                    <table class="table table-hover">
                                        <?                                                
                                        if($buscarHistoricoAlteracaoPrazoCotacao != ""){
                                        ?>      
                                            <thead>          
                                                <tr> 
                                                    <th scope="col">De</th>
                                                    <th scope="col">Para</th>
                                                    <th scope="col">Justificativa</th>
                                                    <th scope="col">Por</th>
                                                    <th scope="col">Em</th>
                                                </tr> 
                                            </thead>
                                            <tbody>
                                                <?
                                                foreach($buscarHistoricoAlteracaoPrazoCotacao as $dadosHistorico)
                                                {											
                                                    ?>
                                                    <tr> 
                                                        <td><?=$dadosHistorico['valor_old']?></td>
                                                        <td><?=$dadosHistorico['valor']?></td>
                                                        <td>
                                                        <?
                                                        if($dadosHistorico['justificativa'] == 'ATRASO') echo 'Atraso na Entrega';
                                                        elseif($dadosHistorico['justificativa'] == 'PEDIDOFORNECEDOR') echo 'A Pedido do Fornecedor';
                                                        elseif($dadosHistorico['justificativa'] == 'NOVACOTACAO') echo 'Inserida Nova Cotação';
                                                        elseif($dadosHistorico['justificativa'] == 'ATRASORESPOSTAFORNECEDOR') echo 'Fornecedor Demorou Responder';
                                                        else echo $dadosHistorico['justificativa'];
                                                        ?>
                                                        </td> 
                                                        <td><?=$dadosHistorico['nomecurto']?></td>
                                                        <td><?=dmahms($dadosHistorico['criadoem'])?></td>
                                                    </tr>
                                                    <?
                                                } ?>
                                            </tbody>
                                        <? } ?>                                    
                                    </table>
                                </div>
                            <?                        
                            }
                            ?> 
                        </div> 
                        <div class="col-md-2 text-center nowrap">Prazo Orc:
                            <?
                            if(empty($_1_u_cotacao_prazointerno)){
                                ?>                     
                                <input name="_1_<?=$_acao?>_cotacao_prazointerno" id="_1_<?=$_acao?>_cotacao_prazointerno" class="calendario size8" type="text" value="<?=$_1_u_cotacao_prazointerno?>" vnulo>
                                <?  
                            }else{
                                ?>
                                <input name="_1_<?=$_acao?>_cotacao_prazointerno" id="_1_<?=$_acao?>_cotacao_prazointerno" class="size8" readonly="readonly" style="background-color: #f2f2f2;" type="text" <?=$vnulo?> value="<?=dma($_1_u_cotacao_prazointerno)?>"> 
                                <i class="fa fa-pencil btn-lg pointer" title='Editar Prazo Orçamento' onclick="alterarValor('prazointerno','<?=dma($_1_u_cotacao_prazointerno)?>', 'modulohistorico', <?=$_1_u_cotacao_idcotacao?>, 'Prazo Orçamento')"></i>
                                <?
                            }
                            ?>         
                            
                            <input name="cotacao_prazointerno_old" type="hidden"  value="<?=$_1_u_cotacao_prazointerno?>" >

                            <?
                            if(!empty($_1_u_cotacao_idcotacao))
                            {
                                $buscarHistoricoAlteracaoPrazoInternoCotacao = CotacaoController::buscarHistoricoAlteracaoPrazoCotacao($_1_u_cotacao_idcotacao, 'prazointerno');
                                if($buscarHistoricoAlteracaoPrazoInternoCotacao != ""){
                                ?>
                                    <i title="Histórico do Prazo Orçamento" class="fa btn-sm fa-info-circle preto pointer hoverazul tip" onclick="historico('prazointerno', <?=$_1_u_cotacao_idcotacao?>, 'de Prazo Orçamento');"></i>
                                <?
                                }
                                ?>
                                <div id="prazo<?=$_1_u_cotacao_idcotacao?>" style="display: none">
                                    <table class="table table-hover">
                                        <?                                                
                                        if($buscarHistoricoAlteracaoPrazoInternoCotacao != ""){
                                        ?>      
                                            <thead>          
                                                <tr> 
                                                    <th scope="col">De</th>
                                                    <th scope="col">Para</th>
                                                    <th scope="col">Justificativa</th>
                                                    <th scope="col">Por</th>
                                                    <th scope="col">Em</th>
                                                </tr> 
                                            </thead>
                                            <tbody>
                                                <?
                                                foreach($buscarHistoricoAlteracaoPrazoInternoCotacao as $dadosHistoricoPrazoInterno)
                                                {											
                                                    ?>
                                                    <tr> 
                                                        <td><?=$dadosHistoricoPrazoInterno['valor_old']?></td>
                                                        <td><?=$dadosHistoricoPrazoInterno['valor']?></td>
                                                        <td>
                                                        <?
                                                        if($dadosHistoricoPrazoInterno['justificativa'] == 'ATRASO') echo 'Atraso na Entrega';
                                                        elseif($dadosHistoricoPrazoInterno['justificativa'] == 'PEDIDOFORNECEDOR') echo 'A Pedido do Fornecedor';
                                                        elseif($dadosHistoricoPrazoInterno['justificativa'] == 'NOVACOTACAO') echo 'Inserida Nova Cotação';
                                                        elseif($dadosHistoricoPrazoInterno['justificativa'] == 'ATRASORESPOSTAFORNECEDOR') echo 'Fornecedor Demorou Responder';
                                                        else echo $dadosHistoricoPrazoInterno['justificativa'];
                                                        ?>
                                                        </td> 
                                                        <td><?=$dadosHistoricoPrazoInterno['nomecurto']?></td>
                                                        <td><?=dmahms($dadosHistoricoPrazoInterno['criadoem'])?></td>
                                                    </tr>
                                                    <?
                                                } ?>
                                            </tbody>
                                        <? } ?>                                    
                                    </table>
                                </div>
                            <?                        
                            }
                            ?>
                        </div> 
                        <div id="modulohistorico<?=$_1_u_cotacao_idcotacao?>" style="display: none">
                            <table class="table table-hover">
                                <tr> 
                                    <td>#namerotulo:</td> 
                                    <td>
                                        <input name="#name_idobjeto" value="<?=$_1_u_cotacao_idcotacao?>" type="hidden">
                                        <input name="#name_auditcampo" value="prazo" type="hidden">
                                        <input name="#name_tipoobjeto" value="cotacao" type="hidden">
                                        <input name="#name_valorold" value="#valor_campo_old" type="hidden">
                                        <input name="#name_campo" value="#valor_campo calendario" class="size10 <?=$calendario?>" type="text">
                                    </td> 
                                </tr>						
                                <tr> 
                                    <td>Justificativa:</td> 
                                    <td >
                                        <select name="#name_justificativa" vnulo class="size50">
                                            <?fillselect(CotacaoController::$justificativaAlteraPrazo);?>
                                        </select>											
                                    </td> 
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-1 text-center nowrap" title="Valor total do Orçamento">
                        <?if (!empty($valorTotalCotacao)){?>                        
                            Total R$: <?=number_format(tratanumero($valorTotalCotacao), 2, ',', '.');?>  
                        <?}?>  
                        </div>
                        <div class="col-md-2 text-center nowrap" style="text-align-last: end;">                   
                            <span>               
                                <? $rotulo = CotacaoController::buscarRotuloStatusFluxo($pagvaltabela, 'idcotacao', $_1_u_cotacao_idcotacao); ?>                                              
                                <label class="alert-warning" id="statusButton" title="<?=$_1_u_cotacao_status?>" id="statusButton"><?=$rotulo['rotulo']?> </label>                
                            </span>
                        </div>
                    </div>                
                </div>            
            </div>
            <div class="panel-body">
                <table>
                    <tr>  
                        <td align="right">Responsável:</td>
                        <td>
                            <input id="idresponsavel" type="text"  name="_1_<?=$_acao?>_cotacao_idresponsavel"  cbvalue="<?=$_1_u_cotacao_idresponsavel?>" value="<?=$jFunc[$_1_u_cotacao_idresponsavel]["nomecurto"]?>" style="width: 20em;" vnulo>
                        </td>
                        <?
                        $visualizacao = CotacaoController::buscarPreferenciaPessoa($_GET["_modulo"].".visualizacao", $_SESSION["SESSAO"]["IDPESSOA"]);
                        if(!empty($_1_u_cotacao_idcotacao))
                        { 
                            ?>
                            <td>Exibir:</td>
                            <td>
                                <select name="Lista"  id="Lista" onChange="visualizacao(this);">
                                    <?fillselect(CotacaoController::$exibirVisualizacao, $visualizacao);?>	
                                </select>
                            </td>
                            <td>Categoria: </td>
                            <td>
                                <select id="picker_grupoes" class="selectpicker valoresselect" data-actions-box="true" multiple="multiple" data-live-search="true" vnulo>
                                    <?
                                    $resContaItem = CotacaoController::buscarGrupoES($_1_u_cotacao_idcotacao, $_GET["_modulo"]);
                                    foreach($resContaItem as $dadosContaItem)
                                    {
                                        if(!empty($dadosContaItem['idobjetovinc'])){
                                            $selected = 'selected';
                                            $valuepicker .= $dadosContaItem['idcontaitem'].',';
                                            $selecionadoContaItem = TRUE;
                                        }else{
                                            $selected = '';
											if($selecionadoContaItem == FALSE){
												$selecionadoContaItem = FALSE;
											}                                            
                                        }
                                        echo '<option data-tokens="'.retira_acentos($dadosContaItem['contaitem']).'" value="'.$dadosContaItem['idcontaitem'].'" '.$selected.' >'.$dadosContaItem['contaitem'].'</option>'; 
                                    }
                                    ?>
                                </select>
                                <? $idcontaitemSelected = substr($valuepicker, 0, -1); ?>
                                <input type="hidden" name="sel_picker_idcontaitem" id="sel_picker_idcontaitem" value="<?=$idcontaitemSelected?>">
                            </td>
                            <td> Subcategoria:</td>
                            <td>
                                <select id="picker_contaitemprodserv" class="selectpicker valoresselect" data-actions-box="true" multiple="multiple" data-live-search="true" vnulo>
                                </select>
                                <input type="hidden" name="sel_picker_idcontaitemtipoprodserv" id="sel_picker_idcontaitemtipoprodserv">
                                <sapn class="altertaSalvar hidden"><a title="Salvar para gravar as informações selecionadas" style="text-align: end;" class="fa fa-exclamation-triangle fa-1x laranja pointer"></a></sapn>
                            </td>
                        <?}?>
                    </tr>
                </table>            
            </div>
        </div>
    </div>
</div>

<?
if(!empty($_1_u_cotacao_idcotacao))
{
	if($visualizacao == 2)
    {
        // POR fornecedor
        if($_acao == 'u')
        {
            ?>
            <div class="row">
                <div class="col-md-12" >
                    <div class="panel panel-default panelAbas" id="mainPanel">
                        <ul class="nav nav-tabs" id="Tab_lp" role="tablist">
                            <li role="presentation panel-heading" class="tabs-container li_cotacao" value="cotacao_solcom">
                                <a href="#cotacao_solcom" class="<?=$row['status'] == 'ATIVO'?'':'cinzaclaro'?>" tab="<?=$row['idlpgrupo']?>" role="tab" data-toggle="tab">
                                    Solicitação de Compras
                                </a>
                                <span class="bg-secundary badge badgesolcom pointer" titulo="Solicitação de Compras"></span>
                            </li>
                            <li role="presentation panel-heading" class="tabs-container li_cotacao" value="cotacao_sugestao">
                                <a href="#cotacao_sugestao" class="<?=$row['status'] == 'ATIVO'?'':'cinzaclaro'?>" tab="<?=$row['idlpgrupo']?>" role="tab" data-toggle="tab">
                                    Sugestão de Compras
                                </a>
                                <span class="bg-secundary badge badgeorc pointer" titulo="Sugestão de Compras"></span>
                            </li>
                            <li role="presentation panel-heading" class="tabs-container li_cotacao" value="cotacao_todos">
                                <a href="#cotacao_todos" class="<?=$row['status'] == 'ATIVO'?'':'cinzaclaro'?>" tab="<?=$row['idlpgrupo']?>" role="tab" data-toggle="tab">
                                    Todos
                                </a>
                                <span class="bg-secundary badge badgetodos pointer" titulo="Todos"></span>
                            </li>
                        </ul>
                        <div class="row">
                            <div class="col-md-4"  style="margin-top: -3%; float: right; margin-right: 0.5%; margin-bottom: -7%;">
                                <input style="margin-right:10px;" type="text" class="form-control tipotext" autocomplete="off" id="inputFiltro" placeholder="Filtrar Dados">
                            </div>
                        </div>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade" role="tab" id="cotacao_solcom">
                                <?
                                $listarSolcom = CotacaoController::listarSolicitacaoCompraVincultadaCotacao($_1_u_cotacao_idcotacao, 'cotacao', $_1_u_cotacao_idempresa);
                                if(count($listarSolcom) > 0) 
                                {
                                    ?>
                                    <div class="panel-body">
                                        <div class="panel panel-default " style="margin-top: -15px !important;">
                                            <div class="panel-heading nowrap hgt_trintacinco">
                                                <span class="qtdProdSolicitacao"></span> produto(s) encontrado(s)
                                                <i class="fa fa-arrows-v fa-2x cinzaclaro pointer" data-toggle="collapse" href="#cotacao_todos_itens_solicitacao_compras" title="Esconder/Mostrar Todos Itens" style="float: right; padding: 0 10px 0 10px;"></i>
                                                <i class="fa fa-arrows-v fa-1x cinzaclaro pointer" id="esconderMostrarTodosFornecedores" title="Esconder/Mostrar Todos Fornecedores" onclick="esconderMostrarTodosFornecedores('prodservsolcom')" style="float: right; padding: 6px 10px 0 10px;"></i>
                                            </div>
                                            <div class="cotacao_todos_itens" id="cotacao_todos_itens_solicitacao_compras">
                                                <table class="table table-striped planilha cotacaoabas" style="width: 100%;">
                                                    <thead>
                                                        <tr>
                                                            <td colspan="12">
                                                                <button id="Adicionar" type="button" class="btn btn-xs" style="background-color: #A9A9A9; color: #FFFFFF" onclick="addSolcom('true')" title="Adicionar Todos">
                                                                    <i class="fa fa-plus"></i> Adicionar Selecionados
                                                                </button>
                                                                <button id="Remover" type="button" class="btn btn-danger btn-xs" onclick="cancelarItemSolcom('true')" title="Remover Todos">
                                                                    <i class="fa fa-trash"></i> Remover Selecionados
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="wdt_dois">
                                                                <input name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="checkallSolcom(this)">
                                                            </th>
                                                            <th class="wdt_cinco">Qtd</th>
                                                            <th class="wdt_dois">Un</th>
                                                            <th class="wdt_oito">Sigla</th>
                                                            <th class="wdt_vintecinco" colspan="2">Descrição</th>
                                                            <th class="wdt_doze">Tipo</th>                                        
                                                            <th class="wdt_oito">Observação</th>   
                                                            <th class="wdt_oito">Solicitado Em</th>
                                                            <th class="wdt_oito">Solicitado Por</th>                                   
                                                            <th class="wdt_sete">Data Previsão</th>
                                                            <th class="wdt_sete">Solicitação</th>                              
                                                            <th class="wdt_sete">Ação</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?     
                                                        $qtdProdSolcom = 0;  
                                                        $totalItens = 0;                
                                                        foreach($listarSolcom AS $_listarLinhasSolcom) 
                                                        { 
                                                            $totalItens++;
                                                            if($idprodservOld != $_listarLinhasSolcom["idprodserv"] && $qtdProdSolcom > 0)
                                                            {
                                                                ?>
                                                                </table>
                                                                </td>
                                                                </tr>	
                                                                <?
                                                            }

                                                            if($idprodservOld != $_listarLinhasSolcom["idprodserv"])
                                                            {
                                                                $qtdProdSolcom++;
                                                                ?>
                                                                <tr <?if($_listarLinhasSolcom['urgencia'] == 'Y') {?>style="background-color: mistyrose;" <? } ?>>
                                                                    <td>
                                                                        <input title="Solcom" type="checkbox" class="itemsolcom itemTodosProduto<?=$_listarLinhasSolcom['idprodserv']?>" id="itemsolcom" idprodserv="<?=$_listarLinhasSolcom['idprodserv']?>" idsolcomitem="<?=$_listarLinhasSolcom['idsolcomitem']?>">
                                                                    </td>
                                                                    <td><?=$_listarLinhasSolcom['qtdc']?></td>
                                                                    <td><?=$_listarLinhasSolcom['un']?></td>
                                                                    <td>
                                                                        <?=$_listarLinhasSolcom['codprodserv']?>
                                                                        <a title="Produto" class="fa fa-bars fade pointer modalProdServ" idprodserv="<?=$_listarLinhasSolcom['idprodserv']?>" modulo="prodserv"></a>
                                                                    </td>
                                                                    <td>
                                                                        <?=mb_strtoupper($_listarLinhasSolcom['descr'],'UTF-8')?>
                                                                        <a title="Produto" class="fa fa-bars fade pointer modalProdServ" idprodserv="<?=$_listarLinhasSolcom['idprodserv']?>" modulo="calculosestoque"></a>
                                                                    </td>
                                                                    <td>
                                                                        <? if($_listarLinhasSolcom['qtdfornecedor'] == 0){ ?>
                                                                            <a title="Não possui Fornecedor" style="text-align: end;" class="fa fa-exclamation-triangle fa-1x laranja btn-lg pointer"></a>
                                                                        <? } ?>
                                                                    </td>  
                                                                    <td><?=$_listarLinhasSolcom['contaitem']?></td>                                                 
                                                                    <td>
                                                                        <? if(empty($_listarLinhasSolcom['obs'])) {$corurgente = 'cinza';} else {$corurgente = 'azul'; } ?>
                                                                        <i title="Observação" idsolcomitem="<?=$_listarLinhasSolcom['idsolcomitem']?>" class="fa btn-sm fa-info-circle <?=$corurgente?> pointer hoverazul tip modalObsInternaClique"></i>
                                                                        <div class="panel panel-default" hidden>
                                                                            <div class="modalobsinterna<?=$_listarLinhasSolcom['idsolcomitem']?>" class="panel-body">
                                                                                <div style="word-break: break-word;">
                                                                                    <?=$_listarLinhasSolcom['obs']?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td><?=dma($_listarLinhasSolcom['criadoem'])?></td>
                                                                    <td><?=$_listarLinhasSolcom['nomecurto']?></td>
                                                                    <td>
                                                                        <? if($_listarLinhasSolcom['dataprevisao']){
                                                                            echo dma($_listarLinhasSolcom['dataprevisao']);
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                        
                                                                        ?>
                                                                    </td>
                                                                    <td align="center">
                                                                        <label class="alert-warning">
                                                                            <?=$_listarLinhasSolcom['siglaidsolcom']?>
                                                                            <a title="Cotação" class="fa fa-bars fade pointer hoverazul" href="?_modulo=solcom&_acao=u&idsolcom=<?=$_listarLinhasSolcom['idsolcom']?>" target="_blank"></a>
                                                                        </label>
                                                                    </td>                                          
                                                                    <td>
                                                                        <a class="fa fa-plus-square fa-x verde btn-lg pointer" onclick="addSolcom('false', '<?=$_listarLinhasSolcom['idsolcomitem']?>')" title="Adicionar Solicitação Compras"></a>
                                                                        <a class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable excluir" onclick="cancelarItemSolcom('false', '<?=$_listarLinhasSolcom['idsolcomitem']?>')" title="Cancelar Item"></a>
                                                                        <i class="fa fa-arrows-v cinzaclaro colpaddingsolcom pointer cotacao_todos_fornecedores" title="Produto" data-toggle="collapse" idprodserv="<?=$_listarLinhasSolcom['idprodserv']?>" href="#prodservsolcom<?=$_listarLinhasSolcom['idprodserv']?>"></i>
                                                                    </td>
                                                                </tr>
                                                                <tr class="prodservsolcom<?=$_listarLinhasSolcom['idprodserv']?> collapse" style="height:40px;" id="prodservsolcom<?=$_listarLinhasSolcom['idprodserv']?>" data-text="<?=$_listarLinhasSolcom['descr']?>">
                                                                    <td colspan="15">
                                                                        <table class="table table-striped planilha" style="width: 100%;">
                                                                            <tr data-text="'.$row['descr'].'">
                                                                                <input name="itemalerta_forn_<?=$_listarLinhasSolcom['idprodserv']?>" id="itemalerta_forn_<?=$_listarLinhasSolcom["idprodserv"]?>" type="hidden" value="">
                                                                                <td style="width: 2%;"></td>
                                                                                <td style="width: 3%;"></td>
                                                                                <td style="width: 36%;">
                                                                                    Nome  
                                                                                    <a title="Produto" class="fa fa-bars fade pointer modalProdServ" idprodserv="<?=$_listarLinhasSolcom['idprodserv']?>" modulo="prodservfornecedor"></a>
                                                                                </td>
                                                                                <td style="width: 30%;">Descrição</td>
                                                                                <td style="width: 10%; text-align: center;">Unidade Compra</td>
                                                                                <td style="width: 7%; text-align: right;">Conversão</td>
                                                                                <td style="width: 10%; text-align: center;">Unidade Padrão</td>
                                                                            </tr>
                                                            <?
                                                            }
                                                            ?>                                                        
                                                            <tr>
                                                                <td></td>
                                                                <td><input type="checkbox" name="fornecedor" idprodservforn="<?=$_listarLinhasSolcom["idprodservforn"]?>" class="checkTodosProduto<?=$_listarLinhasSolcom["idprodserv"]?>" onclick="selecionaFornecedor(<?=$_listarLinhasSolcom['idprodserv']?>, 'cotacao_solcom');"></td>
                                                                <td><?=strtoupper($_listarLinhasSolcom['nome'])?></td>
                                                                <td><?=strtoupper($_listarLinhasSolcom['codforn'])?></td>
                                                                <td align="center"><?=strtoupper($_listarLinhasSolcom['unidadedescr'])?></td>
                                                                <td align="right"><?=$_listarLinhasSolcom['valconv']?></td>
                                                                <td align="center"><?=strtoupper($_listarLinhasSolcom['unidadeprod'])?></td>
                                                            </tr>
                                                            <? 
                                                            $idprodservOld = $_listarLinhasSolcom["idprodserv"];
                                                        } 

                                                        if(count($listarSolcom) == $totalItens)
                                                        {
                                                            ?>
                                                            </table>
                                                            </td>
                                                            </tr>	
                                                            <?
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <? } elseif(($selecionadoContaItem) || (count($listarSolcom) == 0 && $selecionadoContaItem == TRUE)) { ?> 
                                    <div class="col-md-6" style="float: none;"><div class="alert alert-warning" role="alert">Não existe Solicitação de Compras para a Categoria e  Subcategoria selecionadas.</div></div>
                                <? } ?>                 
                            </div>
                            
                            <div role="tabpanel" class="tab-pane fade" role="tab" id="cotacao_sugestao">
                                <div class="panel-body mostraProdutosSugestao">
                                    <div class="panel panel-default" style="margin-top: -15px !important;">
                                        <div class="panel-heading nowrap hgt_trintacinco">
                                            <span class="qtdProdSugestao"></span> produto(s) encontrado(s)
                                            <i class="fa fa-arrows-v fa-2x cinzaclaro pointer" data-toggle="collapse" href="#cotacao_todos_itens_sugestao_compras" title="Esconder/Mostrar Todos Itens" style="float: right; padding: 0 10px 0 10px;"></i>
                                            <i class="fa fa-arrows-v fa-1x cinzaclaro pointer" id="esconderMostrarTodosFornecedores" title="Esconder/Mostrar Todos Fornecedores" onclick="esconderMostrarTodosFornecedores('prodservprodalerta')" style="float: right; padding: 6px 10px 0 10px;"></i>
                                        </div>
                                        <div class="cotacao_todos_itens" id="cotacao_todos_itens_sugestao_compras">
                                            <table class="table table-striped planilha cotacaoabas fixarthtable table_sugestao_compras" style="width: 100%;">
                                                <thead>
                                                    <tr style="background-color: whitesmoke;">
                                                        <td colspan="12">
                                                        <button id="Adicionar" type="button" class="btn btn-xs" style="background-color: #A9A9A9; color: #FFFFFF;" onclick="addProdutoAlerta('true', 'cotacao_sugestao')" title="Adicionar Todos">
                                                        <i class="fa fa-plus"></i> Adicionar Selecionados
                                                        </button>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="sol_un">
                                                            <input name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="marcarTodosProdutoAlerta(this)">
                                                        </th>
                                                        <th class="wdt_cinco qtd" title="Quantidade">Qtd</th>
                                                        <th class="wdt_cinco" title="Unidade" class="unidade">Un</th>
                                                        <th class="wdt_oito">Sigla</th>
                                                        <th class="wdt_trintaoito" class="descricao">Descrição</th>	
                                                        <th class="wdt_quatro_right">Estoque</th> 
                                                        <th class="wdt_oito_right">Sug. Compra</th> 
                                                        <th class="wdt_seis_right">Est. Mín.</th> 
                                                        <th class="wdt_oito_right">Est. Mín. Aut.</th>                                                               
                                                        <th class="wdt_oito_right">Dias Estoque</th>                             
                                                        <th class="wdt_quatorze">Orçamento</th>
                                                        <th class="wdt_um" style="width: 1%;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane fade" role="tab" id="cotacao_todos">
                                <div class="panel-body">
                                    <div class="panel panel-default" style="margin-top: -15px !important;">
                                        <div class="panel-heading">Pesquisar Todos Produtos</div>
                                        <table class="table table-striped planilha cotacaoabas" style="width: 100%;">
                                            <tr>
                                                <td align="right" class="col-md-1">
                                                    Pesquisa:
                                                </td>
                                                <td class="col-md-12">
                                                    <div class="col-md-6">
                                                        <div class="input-group" style="width: 40%; padding: 10px 0 10px 0;">
                                                            <input placeholder="Pesquise um item" class="form-control" name="descri" id="descri" size="15" type="text" value="" style="width: 40em;">
                                                            <span class="input-group-addon" style="background: #337ab7; padding: 0px;">
                                                                <i id="pesquisaritem" style="padding: 6px 12px;" class="fa fa-search pointer branco fa-blink pesquisaritem" title="Pesquisa itens que apenas possuem estoque mínimo da mesma  Subcategoria"  onclick="getDadosATualizaInfoAbas('cotacao_todos')"></i>
                                                            </span>
                                                            <div id="circularProgressPesquisa" class="circularProgressPesquisa" style="display: none;"></div>                                                                                                                
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mostraOcultos" hidden>      
                                                        <button class="btn btn-secondary" title="Ocultar Estoque Mínimo Menor que Zero" style="margin-left: -40px; border: 1px solid #ccc; border-radius: 4px; margin-top: 10px;" cboculto="N" onclick="mostrarOcultarProdutoEstoqueMinimo(this)">
                                                            <span>Est. Mín.</span>
                                                        </button> 
                                                    </div>                                                                                                                                                     
                                                </td>
                                            </tr>
                                        </table>  
                                        <div class="panel-body mostraProdutosTodos" style="display: none;">
                                            <div class="panel panel-default" style="margin-top: -15px !important;">
                                                <div class="panel-heading nowrap" style="height: 35px;">
                                                    <span class="qtdProdTodos" qtd=""></span> produto(s) encontrado(s)
                                                    <i class="fa fa-arrows-v fa-2x cinzaclaro pointer" data-toggle="collapse" href="#cotacao_todos_itens_todos_produtos" title="Esconder/Mostrar Todos Itens" style="float: right; padding: 0 10px 0 10px;"></i>
                                                    <i class="fa fa-arrows-v fa-1x cinzaclaro pointer" id="esconderMostrarTodosFornecedores" title="Esconder Todos" onclick="esconderMostrarTodosFornecedores('prodservtodos')" style="float: right; padding: 0 10px 0 10px;"></i>
                                                </div>
                                                <div class="cotacao_todos_itens_todos_produtos" id="cotacao_todos_itens_todos_produtos">
                                                    <table class="table table-striped planilha cotacaoabas fixarthtable table_cotacao_todos" style="width: 100%;">
                                                        <thead>
                                                            <tr style="background-color: whitesmoke;" style="top: 0">
                                                                <td colspan="15">
                                                                    <button id="Adicionar" type="button" class="btn btn-xs" style="background-color: #A9A9A9; color: #FFFFFF;" onclick="addProdutoAlerta('true', 'cotacao_todos')" title="Adicionar Todos">
                                                                    <i class="fa fa-plus"></i>Adicionar Selecionados
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            <tr style="background-color: #f0f0f0" class="linha-fixa">
                                                                <th class="wdt_um">
                                                                    <input name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="marcarTodosProdutoAlerta(this)">
                                                                </th>
                                                                <th class="wdt_cinco qtd" title="Quantidade">Qtd</th>
                                                                <th class="wdt_cinco" title="Unidade" class="unidade">Un</th>
                                                                <th class="wdt_oito">Sigla</th>
                                                                <th class="wdt_trintaoito" class="descricao">Descrição</th>	
                                                                <th class="wdt_quatro_right">Estoque</th> 
                                                                <th class="wdt_oito_right">Sug. Compra</th> 
                                                                <th class="wdt_seis_right">Est. Mín.</th> 
                                                                <th class="wdt_oito_right">Est. Mín. Aut.</th>                                                               
                                                                <th class="wdt_oito_right">Dias Estoque</th>                             
                                                                <th class="wdt_quatorze">Orçamento</th>
                                                                <th class="wdt_um" style="width: 1%;"></th>
                                                            </tr>	
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>                                                                                        
                                    </div> 
                                </div>
                            </div>
                            <div id="circularProgressIndicator" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>  
            <?            
        }

        //------- Serão reduzidas a quantidade de Vezes das Consultas ---------------------------------------
        //------- Os dados serão inseridos em array para depois montar o HTML    
        $infoNf = CotacaoController::buscarNfPorTipoObjetoSoliPor($_1_u_cotacao_idcotacao, $_1_u_cotacao_idempresa);
        
        //Os fillselect estão neste lugar para que rodem apenas uma vez ao carregar a página, pois existem Orçamentos com várias cotações.
        $fillSelectUnidadeVolume = CotacaoController::listarUnidadeVolume();
        $fillSelectContaItem = CotacaoController::listarContaItemAtivoShare();
        $fillSelectTipoProdserv = CotacaoController::listarProdservTipoProdServPorEmpresa($_1_u_cotacao_idempresa);
        $fillSelectTransportadora = CotacaoController::listarFornecedorPessoaPorIdTipoPessoa(11);
        $dominio = CotacaoController::buscarDominio($_1_u_cotacao_idempresa);
        $controleCabecarioReprovadosCancelados = TRUE; //CRIAÇAO DO HEADER DO COLAPSE DE CANCELADOS E REPROVADOS        
        $i = 1;
        $l = 0;  
        $m = 0;

        foreach($infoNf['nf'] as $_nf)
        {   
            $i = $i + 1;
            $l = $l + 1;
            
            if($_nf['status'] == 'APROVADO' || $_nf['status'] == 'PREVISAO' || $_nf['status'] == 'RESPONDIDO' || $_nf['status'] == 'AUTORIZADO' || $_nf['status'] == 'AUTORIZADA'){
                $vnulo = "vnulo";
            }else{
                $vnulo = "";
            }                                      
                                        
            if($_nf['status'] == "INICIO" || $_nf['status'] == "RESPONDIDO" || $_nf['status'] == "AUTORIZADO" || $_nf['status'] == "ENVIADO" || $_nf['status'] == 'AUTORIZADA'){
                $nfreadonly = "";
                $nfdesabled = "";
            }else{
                $nfreadonly = "readonly='readonly'";
                $nfdesabled = "disabled='disabled'";
            }

            if($_nf['pedidoentrega'] == 'atrasado'){
                $statusent = 'ABERTO';
            }else{
                $statusent = $_nf['status'];
            }

            if(in_array($_nf['status'], array('CANCELADO', 'REPROVADO')))
            {
                $stylePanelDefault = 'style="margin-top: -7px !important"';
                $stylePanelHeading = 'style="height: 34px; padding: 0px;"';

                if($controleCabecarioReprovadosCancelados)
                {
                    $controleCabecarioReprovadosCancelados = FALSE;
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading" style="height: 30px;"> 
                                    REPROVADOS / CANCELADOS
                                    <i class='fa fa-arrows-v fa-2x cinzaclaro pointer' title='Detalhar' data-toggle='collapse' href='#canceladosreprovados' aria-expanded='true' style='float: right;'></i>
                                </div>
                                <div class="panel-heading collapse" id="canceladosreprovados"> 
           
                    <?  
                }
            }

            $descrItensNf = "";
            $taxaConversao = FALSE;
            foreach($infoNf['nfitens'][$_nf['idnf']] as $_itens)
            {
                if(empty($_itens["codforn"])){
                    $descrProduto = $_itens["descr"]." - ".$_itens["codprodserv"];                                               
                }else{
                    $descrProduto = $_itens["codforn"];
                }
                $descrItensNf .= " ".$descrProduto;

                if(in_array($_itens["moedaext"], array('USD', 'EUR')) && !empty($_itens["vlritemext"]))
                {   
                    $taxaConversao = TRUE;
                } elseif($taxaConversao == FALSE) {
                    $taxaConversao = FALSE;
                }
            }
            
            ?>
            <div class="row" style="display: block;">
                <div class="col-md-12 cotacaoabas cotacaoItensNota">
                    <div class="panel panel-default" <?=$stylePanelDefault?> id="nftable<?=$_nf['idnf']?>" data-text="<?=$descrItensNf?>">
                        <div class="panel-heading <?=$statusent?>" <?=$stylePanelHeading?> id="divcor<?=$_nf['idnf']?>" data-text="<?=$descrItensNf?>">  
                            <table style="width: 100%">
                                <tr data-text="<?=$descrItensNf?>">
                                    <td>
                                        <? if(in_array($_nf['status'], array('CANCELADO', 'REPROVADO'))){ ?>
                                            <i class="fa fa-arrows-v cinzaclaro pointer" title="Estoque" data-toggle="collapse" href="#cotacao<?=$_nf['idnf']?>" aria-expanded="false" style="padding-right: 10px;"></i>
                                        <? } ?>
                                    </td>
                                    <td class="nowrap" style="text-align: end;">
                                        <input type="hidden" id="cotacao<?=$_nf['idnf']?>iddiv" class="cotacao<?=$_nf['idnf']?>iddiv" value="<?=$statusent?>">
                                        <label class="idbox"><?=$l?></label>-
                                        <a class="fa fa-clone  pointer hoverazul" title="Duplicar Cotação" onclick="duplicarcompra(<?=$_nf['idnf']?>)"></a>
                                        <br>
                                        <div type="text" style="width: 35px; height: 0px"></div>
                                    </td>
                                    <td>Cotação:</td>
                                    <td colspan="2" class="nowrap" style="padding-right: 0px!important;">
                                        <label class="idbox">
                                            <?=$_nf['idnf']?>
                                            <a title="Cotação Fornecedor" class="fa fa-bars fade pointer hoverazul" href="?_modulo=cotacaoforn&_acao=u&idnf=<?=$_nf['idnf']?>" target="_blank"></a>
                                        </label>
                                        <?
                                        if($_nf['idnforigem'])
                                        {                          
                                            ?>
                                            <i class="fa fa-info-circle azul pointer hoverpreto tip" >
                                                <span class="infoNfDuplicada">
                                                    <p>Cotação duplicada REF: <b><?=$_nf['idnforigem']?></b></p>
                                                    <p>Criado em: <b><?=dmahms($_nf['criadoem'])?></b></p>
                                                    <p>Criado por: <b><?=$_nf['criadopor']?></b></p>
                                                </span>
                                            </i>
                                        <?}?>
                                        <br>
                                        <div type="text" style="width: 85px; height: 0px"></div>
                                    </td>
                                    <td>Fornecedor:</td>
                                    <td class="col-md-5">
                                        <label class="idbox">
                                            <?=$_nf['nome']?>
                                            <a title="Fornecedor" class="fa fa-bars fade pointer hoverazul" href="?_modulo=pessoa&_acao=u&idpessoa=<?=$_nf['idpessoa']?>" target="_blank"></a>
                                        </label>
                                        
                                        <?
                                        $resultado = $infoNf['resultadoavaliacaofornecedor'][$_nf['idpessoa']]['resultado'];
                                        if(COUNT($resultado) > 0 )
                                        {
                                            if($row["resultado"] == 'REPROVADO')
                                            {
                                                ?>
                                                <a title="Fornecedor <?=$resultado?>" style="text-align: end;" class="fa fa-exclamation-triangle fa-1x vermelho btn-lg pointer " ></a> 
                                                <?
                                            } 
                                        }else{
                                            ?>
                                            <a title="Avaliação do fornecedor PENDENTE"  style="text-align: end;"  class="fa fa-exclamation-triangle fa-1x laranja btn-lg pointer" ></a> 
                                            <?
                                        }   
                                        ?>                                                            
                                    </td>
                                    <td align="right" class="nowrap">Emissão NF:</td> 
                                    <td>					
                                        <input name="_<?=$i?>_<?=$_acao?>_nf_dtemissao"  class="calendario size10 dtemissao<?=$_nf['idnf']?> alterarDtEmissao" idnf="<?=$_nf['idnf']?>" id="fdata<?=$i?>" type="text" value="<?=dma($_nf["dtemissao"])?>" <?=$nfdesabled?>>
                                    </td>
                                    <td>
                                        <?if($_nf['status'] != "INICIO" && $_nf['status'] != "RESPONDIDO" && $_nf['status'] != "AUTORIZADO" && $_nf['status'] != 'AUTORIZADA' && $_nf['status'] != "ENVIADO" && $_nf['status'] != "REPROVADO"){?>
                                            NF:
                                        <? } ?>
                                    </td>
                                    <td> 
                                        <?if($_nf['status'] != "INICIO" && $_nf['status'] != "RESPONDIDO" && $_nf['status'] != "AUTORIZADO" && $_nf['status'] != 'AUTORIZADA' && $_nf['status'] != "ENVIADO" && $_nf['status'] != "REPROVADO"){?>
                                            <? $modulo = 'nfentrada';  ?>
                                            <a class="fa fa-bars pointer hoverazul idbox" title="NF" onclick="janelamodal('?_modulo=<?=$modulo?>&_acao=u&idnf=<?=$_nf['idnf']?>')"></a>
                                        <?}?>                    
                                        <div type="text" style="width: 20px; height: 0px"></div>
                                    </td>
                                    <td align="right">Status:</td>
                                    <td>
                                        <input name="_<?=$i?>_<?=$_acao?>_nf_idnf"  type="hidden" value="<?=$_nf['idnf']?>">
                                        <?
                                        if($_nf['status'] == "INICIO" || $_nf['status'] == "RESPONDIDO" || $_nf['status'] == "AUTORIZADO" || $_nf['status'] == 'AUTORIZADA' || $_nf['status'] == "ENVIADO" )
                                        {                                       
                                            ?> 
                                            <input type="hidden" id="nfstatus<?=$_nf['idnf']?>" value="<?=$_nf['status']?>">
                                            <select class="size10" id="nfstatus" onchange="validarCamposPreenchidos(this, <?=$_nf['idnf']?>, <?=$i?>);">
                                                <?fillselect(CotacaoController::$statusCotacao, $_nf['status']);?>		
                                            </select>
                                        <?}else{
                                            ?>
                                            <label class="idbox"><input type="hidden" id="nfstatus" value="<?=$_nf['status']?>"><?=$_nf['status']?></label>
                                        <?} ?>                                    
                                    </td>
                                    <td class="nowrap">
                                        <? 
                                        if($_nf['status'] == "DIVERGENCIA" || $_nf['status'] == "APROVADO" || $_nf['status'] == "PREVISAO" || $_nf['status'] == "REPROVADO" || $_nf['status'] == "CANCELADO")
                                        {
                                            $tipoemail='emailaprovacao';
                                            $tipoenvio = 'COTACAOAPROVADA';
                                            $rotemail='Aprovação';
                                            if($_nf["emailaprovacao"]=='Y'){
                                                $classtdemail="amarelo";
                                                $varalt='N';
                                            }elseif($_nf["emailaprovacao"] == 'O' || $_nf["emailaprovacao"] == 'R'){
                                                $classtdemail="verde";
                                                $varalt='N';
                                            }elseif($_nf["emailaprovacao"] == 'E'){
                                                $classtdemail = "vermelho";
                                                $varalt = 'Y';
                                            }else{
                                                $classtdemail = "cinza";
                                                $varalt = 'Y';
                                            }
                                        }else{
                                            $tipoemail='envioemailorc';
                                            $tipoenvio = 'COTACAO';
                                            $rotemail='Cotação';
                                            if($_nf["envioemailorc"] == 'Y'){
                                                $classtdemail = "amarelo";
                                                $varalt = 'N';
                                            }elseif($_nf["envioemailorc"] == 'O' or $_nf["envioemailorc"] == 'R'){
                                                $classtdemail = "verde";
                                                $varalt = 'N';
                                            }elseif($_nf["envioemailorc"] == 'E'){
                                                $classtdemail = "vermelho";
                                                $varalt = 'Y';
                                            }else{
                                                $classtdemail = "cinza";
                                                $varalt = 'Y';
                                            }
                                        }

                                        if($qtdempresaemail[$tipoenvio] == 1){
                                            $nemails = 1;
                                        }else{
                                            if($qtdempresaemail[$tipoenvio] > 1){
                                                $nemails = 2;
                                            }else{
                                                $nemails = 0;
                                            }
                                        }
                                        
                                        if(count($infoNf['empresaemailobjeto'][$_nf['idnf']][$tipoenvio]) < 1){
                                            $setemail = 1;
                                        }else{
                                            $setemail = 0;
                                        }
                                        
                                        if($nemails == 1){?>                                           
                                            <input id="emailunico" type="hidden" value="<?=$dominio["idemailvirtualconf"]?>">
                                            <input id="idempresaemail" type="hidden" value="<?=$dominio["idempresa"]?>">
                                        <?}

                                        $formatadata = explode('/', $_1_u_cotacao_prazo);
                                        $date = $formatadata[2].'-'.$formatadata[1].'-'.$formatadata[0]. ' 23:59:59';
                                        $timeStampPrazo = strtotime($date);
                                        $timeStampNow = strtotime(date('Y-m-d'));

                                        if($timeStampPrazo < $timeStampNow) {
                                            $fdel="alert('O prazo para envio do e-mail venceu no dia ". $_1_u_cotacao_prazo.".')";
                                        }else{
                                            $fdel="altflagemail(".$_nf["idnf"].",'nf','".$tipoemail."','".$varalt."',".$nemails.");"; 
                                        }
                                        ?>
                                        <table>
                                            <tr>
                                                <td>
                                                    <input id="setemail" type="hidden" value="<?=$setemail?>">
                                                    <i class="fa fa-envelope pointer <?=$classtdemail?> hoverazul" title="Enviar email <?=$rotemail?> " onclick="<?=$fdel?>"></i>
                                                    <br>
                                                    <div type="text" style="width: 13px; height: 0px"></div>
                                                </td>
                                                <? 
                                                $idmailfila = $infoNf['mailfila'][$_nf['idnf']]['idmailfila'];
                                                if(count($idmailfila) > 0)
                                                {
                                                    ?>
                                                    <td>                                                     
                                                        <a class="pull-right" title="Ver emails enviados cinza hoverazul" onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?=$idmailfila?>')"><i class="fa fa-envelope-o cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza cinza pointer"></i></a>
                                                        <br>
                                                        <div type="text" style="width: 18px; height: 0px"></div>
                                                    </td> 
                                                <? 
                                                } 
                                                
                                                $nomeAnexoProposta = $infoNf['anexocotacao'][$_nf['idnf']];
                                                if(count($nomeAnexoProposta) > 0)
                                                {
                                                    $arrprop .= $arrvirg . $_nf["idnf"];
                                                    $arrvirg = ",";
                                                    ?>                       
                                                    <td>                                                    
                                                        <i class="fa fa-paperclip pointer cinza hoverazul" id="propostaanexa_<?=$_nf['idnf']?>" title="Propostas Anexas"></i>
                                                        <div class="webui-popover-content" id="content_<?=$_nf['idnf']?>">
                                                            <table>
                                                                <? 
																foreach($nomeAnexoProposta  AS $_nomeAnexoProposta)
                                                                {
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <a class="pointer" onclick="janelamodal('upload/<?=$_nomeAnexoProposta['caminho']?>');"><?=$_nomeAnexoProposta['nome']?></a>
                                                                        </td>
                                                                    </tr>
                                                                <?}?>
                                                            </table>
                                                        </div>
                                                    
                                                        <br>
                                                        <div type="text" style="width: 10px; height: 0px"></div>
                                                    </td> 
                                                <?
                                                }
                                                
                                                if(count($infoNf['nfitens']['migrarCotacao'][$_nf['idnf']]) > 0) 
                                                {
                                                    ?> 
                                                    <td>                                                                                
                                                        <a id="altorcamento" class="fa fa-chain-broken pointer modalMigrarCotacaoClique cinza hoverazul" idnf="<?=$_nf['idnf']?>" title="Migrar Cotação"></a>
                                                        <div class="panel panel-default" hidden>
                                                            <div class="modalmigrarcotacao<?=$_nf['idnf']?>" class="panel-body">
                                                                Selecionar Cotação: 
                                                                <select id="val_idobjetosolipor<?=$_nf['idnf']?>" class="size25" onchange="alterarCotacao(<?=$_nf['idnf']?>, this)">
                                                                    <option value=""></option>
                                                                    <?fillselect($infoNf['nfitens']['migrarCotacao'][$_nf['idnf']]);?>		
                                                                </select>
                                                            </div>
                                                        </div>                                            
                                                    </td> 
                                                <?
                                                }
                                                ?>                                             
                                                <td>
                                                    <button  type="button" class="btn btn-success btn-xs" onclick="salvarNf(<?=$_nf['idnf']?>,<?=$i?>)" title="Salvar Este">
                                                        <i class="fa fa-circle"></i>Salvar
                                                    </button>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>                                
                            </table>
                        </div>
                        <div class="panel-body" id="cotacao<?=$_nf['idnf']?>">
                            <div class="row esconderdiv"> 
                                <div class="col-md-7">
                                    <table>
                                        <tr> 
                                            <td align="right">Tipo NF:</td>
                                            <td>
                                                <?
                                                if(empty($rownf['tiponf']))
                                                {
                                                    $_tiponf = 'C';
                                                } else {
                                                    $_tiponf = $rownf['tiponf'];
                                                }
                                                ?>
                                                <select class="size10" id="tiponf" name="_<?=$i?>_<?=$_acao?>_nf_tiponf" vnulo>
                                                    <?fillselect(CotacaoController::$tipoNf, $_tiponf);?>		
                                                </select>
                                            </td>                                           
                                            <td align="right">Finalidade:</td>
                                            <td>
                                                <?                            
                                                if(count($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]) == 0){
                                                    $option = "<option value=''>ALERTA: Configurar Finalidade no Fornecedor</option>";
                                                }elseif(count($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]) > 1){
                                                    $option = "<option value=''></option>";
                                                }else{
                                                    $option = "";
                                                }

                                                if(count($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]) == 1)
                                                {
                                                    $idfinalidadeprodserv = array_keys($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']]);
                                                    $idfinalidadeprodserv = $idfinalidadeprodserv[0];
                                                } elseif(!empty($_nf['idfinalidadeprodserv'])) {
                                                    $idfinalidadeprodserv = $_nf['idfinalidadeprodserv'];
                                                } else {
                                                    $idfinalidadeprodserv = "";
                                                }                                               
                                                ?>                       
                                                        
                                                <select class="size25" id="idfinalidadeprodserv" name="_<?=$i?>_<?=$_acao?>_nf_idfinalidadeprodserv" class='size20 fillCheck' <?if($_nf['status'] != "CANCELADO"){?> vnulo <?}?>  <?if($_nf['status'] == "CONCLUIDO"){?>disabled='disabled'<?}?>>
                                                    <?=$option?>
                                                    <?fillselect($infoNf['fillSelectFinalidadeProdserv'][$_nf['idpessoa']], $idfinalidadeprodserv);?>
                                                </select>
                                            </td>  
                                        <tr>                                            
                                        </tr>                                      
                                            <td align="right">Nº Orçamento:</td>
                                            <td>
                                                <input setdisable<?=$_nf['idnf']?> <?=$infoStatus?> class="size10"  name="_<?=$i?>_<?=$_acao?>_nf_pedidoext" type="text" value="<?=$_nf["pedidoext"]?>" <?=$nfdesabled?>>
                                            </td>
                                            <? 
                                            if($_nf['status'] == 'ENVIADO')
                                            { 
                                                $infoStatus = "disabled title='Não é possivel editar enquanto o status for ENVIADO'"; 
                                            } else {
                                                $infoStatus = ""; 
                                            } ?>
                                            <td align="right">Vendedor(a):</td>
                                            <td>
                                                <input  setdisable<?=$_nf['idnf']?> <?=$infoStatus?> class="size25" name="_<?=$i?>_<?=$_acao?>_nf_aoscuidados" type="text" value="<?=$_nf["aoscuidados"]?>" <?=$nfdesabled?>>
                                            </td>
                                            <td align="right">Telefone:</td>
                                            <td>
                                                <input  setdisable<?=$_nf['idnf']?> <?=$infoStatus?> class="size10" name="_<?=$i?>_<?=$_acao?>_nf_telefone" type="text" value="<?=$_nf["telefone"]?>" <?=$nfdesabled?>>
                                            </td>
                                        </tr>
                                    </table>                                    
                                </div>
                                <?
                                if(!empty($_nf['observacaore']))
                                {
                                    ?>
                                    <div class="col-md-5">
                                        <div class="alert alert-warning" role="alert" >
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <b>Observação:</b>
                                                    <br /><br />
                                                    <?=str_replace(chr(13),"<br>", $_nf['observacaore'])?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <? } ?>
                            </div>
                            <br />
                            <?                                
                            if($_nf['marcartodosnfitem'] == 'Y'){
                                $checked = 'checked';
                            }else{
                                $checked = '';
                            }
                            ?>	
                            <table class="table table-striped planilha">
                                <thead>
                                    <tr>
                                        <!--th>NF</th-->
                                        <th><input setdisable<?=$_nf['idnf']?> <?=$infoStatus?> name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" id="<?=$_nf['idnf']?>" <?=$checked?> onclick="checkall(<?=$_nf['idnf']?>,this)" <?=$nfdesabled?>></th>
                                        <th>Qtd Sol</th>
                                        <th style="width: 65px;">Sug Com</th>
                                        <th>Un</th>
                                        <th>Descrição</th>
                                        <th class="nowrap">Categoria</th>                                        
                                        <?if($taxaConversao == TRUE){?>
                                            <th class="nowrap" title="Taxa de Conversão">Tx Cv</th>
                                        <?}?>
                                        <th style="text-align: -webkit-center;" >Valor Un</th>
                                        <th>Desc Un</th>
                                        <th>ICMS ST</th>
                                        <th>IPI %</th>
                                        <th>Total</th>
                                        <th>Validade</th>
                                        <th>Prev Entrega</th>
                                        <th>Obs</th>
                                        <th colspan="4"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    $total = 0;
                                    $desconto = 0;
                                    $totalsemdesc = 0;
                                    $moeda = "";
                                    $iobs = $i;
                                    $first = '';
                                    $infe = 0;
                                    $qtdItens = 1;
                                    $qtdnfitem = COUNT($infoNf['nfitens'][$_nf['idnf']]);
                                    foreach($infoNf['nfitens'][$_nf['idnf']] as $_itens)
                                    {
                                        $i = $i + 1;
                                        if(!empty($first) and !empty($_itens['idprodserv']) and empty($second))
                                        {
                                            $second = 1;
                                            ?>
                                            <tr>
                                                <td colspan="20"><hr></td>
                                            </tr>
                                            <?
                                        }

                                        if(empty($_itens['idprodserv']))
                                        {
                                            $first = 1;
                                        }
                                        
                                        if(in_array('Y', $infoNf['nfitens']['itenssolcom'][$_itens['idprodserv']]['urgencia']))
                                        {
                                            $corSolcom  = "style='background-color: mistyrose'";
                                        } else {
                                            $corSolcom = "";
                                        }
                                        ?>  
                                        <tr <?=$corSolcom?>>
                                            <?
                                            //Esconde os itens que não estão checados                                
                                            if(in_array($_nf['status'], array('APROVADO', 'PREVISAO', 'DIVERGENCIA', 'CONCLUIDO', 'CONFERIDO')) && $_itens['nfe'] == 'N')
                                            {
                                                if($infe == 0)
                                                {
                                                    ?>
                                                    <tr>
                                                        <td colspan="20" style="height:40px;" data-toggle="collapse" href="#itemnf<?=$_itens['idnf']?>" aria-expanded="false" class="collapsed">
                                                            Itens Não Selecionados
                                                            <i class="fa fa-arrows-v cinzaclaro pointer cotacao_todos_item" title="Produto"></i>
                                                        </td>
                                                    </tr>
                                                    <tr class="collapse" id="itemnf<?=$_itens['idnf']?>">
                                                        <td colspan="20">
                                                            <table style="width: 100%;">
                                                    <?
                                                }
                                                $infe++;
                                            }
                                            ?>
                                            <td>
                                                <? 
                                                if($_itens["nfe"] == 'Y')
                                                {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';	
                                                    if($_itens['moeda'] == "BRL"){
                                                        $totalsemdesc += $_itens['total'] + $_itens['valipi'] + ($_itens['des'] * $_itens['qtd']);
                                                        $total = $total + $_itens['total'] + $_itens['valipi'];
                                                        $desconto += $_itens['des'] * $_itens['qtd'];
                                                        $moeda = $_itens['moeda'];
                                                    }else{
                                                        $total = $total + $_itens['totalext'];
                                                        $moeda = $_itens['moeda'];
                                                    }
                                                }else{
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                    if($_itens['moeda'] == "BRL"){
                                                        
                                                        $moeda = $_itens['moeda'];
                                                    }else{
                                                        
                                                        $moeda = $_itens['moeda'];
                                                    }
                                                }				
                                                ?>
                                                <input setdisable<?=$_nf['idnf']?> <?=$infoStatus?> title="Nfe" type="checkbox" <?=$nfdesabled?> <?=$checked?> name="namenfec" class="<?=$_nf['idnf']?>" id="<?=$_itens["idnfitem"]?>" onclick="alterarCamposNf(<?=$_itens['idnfitem']?>, 'nfitem', 'nfe', '<?=$vchecked?>',this, <?=$_nf['idnf']?>)">
                                            </td>
                                            <td>
                                                <input setdisable<?=$_nf['idnf']?> <?=$infoStatus?> name="_<?=$i?>_<?=$_acao?>_nfitem_idnfitem" type="hidden" value="<?=$_itens['idnfitem']?>">
                                                <input setdisable<?=$_nf['idnf']?> <?=$infoStatus?> name="_<?=$i?>_<?=$_acao?>_nfitem_tiponf" type="hidden" value="C">
                                                <input style="text-align: right;" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfreadonly?> class="size5" name="_<?=$i?>_<?=$_acao?>_nfitem_qtdsol" onchange="atualizarQtd(this, <?=$_itens['idnfitem']?>)" id="qtdsol<?=$_itens['idnfitem']?>" type="text" value="<?=$_itens['qtdsol']?>">
                                            </td>
                                            <td align="right">
                                                <? 
                                                if($_itens['converteest'] == "Y")
                                                {
                                                    $sugestaocompra2 = $_itens['sugestaocompra2']/$_itens['valconv'];
                                                } else {
                                                    $sugestaocompra2 = $_itens['sugestaocompra2'];
                                                }
                                                ?>
                                                <label class="idbox"><?=number_format(tratanumero($sugestaocompra2), 2, ',', '.')?></label>
                                            </td>
                                            <td>
                                                <?
                                                if(empty($_itens["unidade"]))
                                                {
                                                    if(empty($_itens['idprodserv']) && $_nf['status'] == 'INICIO')
                                                    {
                                                        ?>
                                                        <select  setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  name="_<?=$i?>_<?=$_acao?>_nfitem_un"> 
                                                            <option value=""></option>
                                                            <?fillselect($fillSelectUnidadeVolume, $_itens["unidade"]);?>
                                                        </select>
                                                        <?
                                                    } else {
                                                        $unidade = $_itens["unidade"];
                                                    }
                                                }else{
                                                    $unidade = $_itens["unidade"];
                                                }                                                    
                                                ?>    
                                                <a class="cinza hoverazul pointer modalProdServ" idprodserv="<?=$_itens['idprodserv']?>" modulo="prodservfornecedor">
                                                    <?=$unidade?>
                                                </a>                               
                                            </td>
                                            <td>    
                                                <?if(!empty($_itens['idprodserv']))
                                                {                        
                                                    if(empty($_itens["codforn"])){
                                                        $descrProduto = $_itens["descr"];                                               
                                                    }else{
                                                        $descrProduto = $_itens["codforn"];
                                                    }
                                                    ?>
                                                    <a class="hoverazul pointer modalProdServ" title="Produto-<?=$_itens['tipoprodserv']?>" idprodserv="<?=$_itens['idprodserv']?>" modulo="calculosestoque">
                                                        <?=$descrProduto?>
                                                    </a>
                                                <?} else {
                                                    $descrProduto = $_itens["prodservdescr"];
                                                    ?>
                                                    <input setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  <?=$nfreadonly?> class="size20" name="_<?=$i?>_<?=$_acao?>_nfitem_prodservdescr"  type="text" value="<?=$_itens['prodservdescr']?>" onchange="salvarDescr(this,<?=$_itens['idnfitem']?>)" >    
                                                <? 
                                                }
                                                //Mostra o Vínculo com Solcom
                                                if(!empty($infoNf['nfitens']['itenssolcom'][$_itens['idprodserv']]))
                                                {
                                                    foreach($infoNf['nfitens']['itenssolcom'][$_itens['idprodserv']] AS $chave => $_idsolcom)
                                                    {   
                                                        if($chave != 'urgencia')
                                                        {
                                                            ?>
                                                            <label class="idbox" style="margin-right: 5px;">
                                                                <a title="Solicitação Compras" class="fade pointer hoverazul solcomvalida" idprodserv="<?=$_itens['idprodserv']?>" idnf="<?=$_itens['idnf']?>" idnfitem="<?=$_itens['idnfitem']?>" descr="<?=$descrProduto?>" idsolcom="<?=$_idsolcom?>" nfe="<?=$_itens["nfe"]?>" href="?_modulo=solcom&_acao=u&idsolcom=<?=$_idsolcom?>" target="_blank">
                                                                    <?=$_idsolcom?>
                                                                </a>
                                                            </label>
                                                            <?
                                                        }
                                                    }
                                                }
                                                ?>
                                            </td> 
                                            <td align="center">
                                                <?
                                                if(!empty($_itens['idcontaitem'])){
                                                    $idcontaitem = $_itens['idcontaitem'];
                                                }elseif(!empty($_itens['idprodserv'])) {

                                                    if(count($infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']]) == 1)
                                                    {
                                                        foreach($infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']] AS $key => $_idcontaitem)
                                                        {
                                                            $idcontaitem = $key;     
                                                        }                                                         
                                                    } 						
                                                } else {
                                                    $idcontaitem = "";
                                                }
                                                ?>
                                                <div id="tb_<?=$_itens["idnfitem"]?>" class="grupo_es_oculto" style="display: none;">
                                                    <table style="width: 100%">
                                                        <tr style="padding: 15px;">
                                                            <th>Categoria</th>
                                                            <th></th>
                                                            <th>Tipo</th>                        
                                                        </tr>
                                                        <tr>                                
                                                            <td style="width: 45%;" class="cp_grupoes" >
                                                                <?if(empty($_itens['idprodserv']))
                                                                {
                                                                ?>
                                                                    <input type="hidden" nomodal id="iidcontaitem<?=$_itens["idnfitem"]?>" name="_<?=$i?>_u_nfitem_idcontaitem" value="<?=$_itens['idcontaitem']?>" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfdesabled?> >
                                                                    <select id="idcontaitem<?=$_itens["idnfitem"]?>" name="" class='size25'setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfdesabled?> <?=$vnulo?> onchange="alterarContaItem(this, <?=$_itens['idnfitem']?>)">
                                                                        <option value=""></option>
                                                                        <?fillselect($fillSelectContaItem, $idcontaitem)?>
                                                                    </select> 
                                                                <?
                                                                }elseif(!empty($_itens['idcontaitem'])){                                  
                                                                    echo $infoNf['nfitens']['traduzirContaItem'][$_itens['idcontaitem']];
                                                                    ?>
                                                                    <input type="hidden" nomodal id="iidcontaitem<?=$_itens["idnfitem"]?>" name="_<?=$i?>_u_nfitem_idcontaitem" value="<?=$idcontaitem?>" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfdesabled?> >
                                                                    <?
                                                                }else{                            
                                                                    ?>
                                                                    <input type="hidden" nomodal id="iidcontaitem<?=$_itens["idnfitem"]?>" name="_<?=$i?>_u_nfitem_idcontaitem" value="<?=$_itens['idcontaitem']?>" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfdesabled?> >
                                                                    <select id="idcontaitem<?=$_itens["idnfitem"]?>" name="" class="size20"  setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfdesabled?> <?=$vnulo?>>                               
                                                                        <option value=""></option>
                                                                        <?fillselect($infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']], $idcontaitem);?>
                                                                    </select>    
                                                                <?
                                                                }
                                                                ?>
                                                            </td>
                                                            <td  style="width: 10%;"></td>
                                                            <td style="width: 45%;" id="td<?=$row["idnfitem"]?>" class="cp_tipo 1">
                                                                <?
                                                                if(!empty($_itens['idprodserv']) && !empty($_itens['idtipoprodserv']))
                                                                {                                                                        
                                                                    ?>
                                                                    <input type="hidden" nomodal name="_<?=$i?>_u_nfitem_idtipoprodserv" value="<?=$_itens['idtipoprodserv']?>" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfdesabled?> >
                                                                    <?
                                                                    echo $_itens['tipoprodserv'];
                                                                    
                                                                } else {
                                                                    if($_nf['status'] == 'CONCLUIDO' )
                                                                    {                        
                                                                        $arrIdTipoProdserv = $fillSelectTipoProdserv;
                                                                    
                                                                    }elseif($_itens['idcontaitem']){
                                                                        $arrIdTipoProdserv = $infoNf['nfitens']['fillSelectTipoProdservIdContaItem'][$_itens['idcontaitem']];
                                                                    
                                                                    }else{
                                                                        if(!empty($_itens['idprodserv'])){
                                                                            $arrIdTipoProdserv = $infoNf['nfitens']['fillSelectContaItemProdserv'][$_itens['idprodserv']];
                                                                        }else{
                                                                            $arrIdTipoProdserv = array('' => '');
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <input type="hidden" nomodal id="iidtipoprodserv<?=$row["idnfitem"]?>" name="_<?=$i?>_u_nfitem_idtipoprodserv" value="<?=$_itens['idtipoprodserv']?>" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfdesabled?> >
                                                                    <select id="idtipoprodserv<?=$_itens["idnfitem"]?>" name="" style="width: 100%" vnulo setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfdesabled?>>
                                                                        <option value=""></option>
                                                                        <?fillselect($arrIdTipoProdserv, $_itens['idtipoprodserv']);?>
                                                                    </select>
                                                                <?
                                                                }
                                                                ?>                            
                                                            </td> 
                                                        </tr>
                                                    </table>
                                                </div>
                                                
                                                <?if(empty($_itens['idcontaitem']) ){ ?>
                                                    <i class="btn fa fa-info-circle laranja" title="Categoria e/ou Subcategoria não Atribuídas" id="btn_<?=$_itens["idnfitem"]?>" onclick="mostrarModalGrupoES(<?=$_itens['idnfitem']?>,'<?=addslashes($descrProduto)?>',<?=$i?>)"></i>
                                                <?}else{?>
                                                    <i class="btn fa fa-info-circle" id="btn_<?=$_itens["idnfitem"]?>" onclick="mostrarModalGrupoES(<?=$_itens['idnfitem']?>,'<?=addslashes($descrProduto)?>',<?=$i?>)"></i>
                                                <?}?>
                                            </td>
                                            <?
					                        if(!empty($_itens['moedaext']) && $_itens['moedaext'] != "BRL" )
                                            {
                                            ?>
                                                <td class="nowrap">
                                                    <label class="alert-warning">
                                                        <?echo($_itens['moedaext']);?>
                                                    </label>
                                                    <?
                                                    if($_itens['moeda'] == "BRL"){
                                                        ?>
                                                        <input setdisable<?=$_itens['idnf']?> <?=$infoStatus?>  vnulo <?=$nfreadonly?> style="width: 40px;" title="Câmbio BRL" placeholder="Câmbio"	name="_<?=$i?>_<?=$_acao?>_nfitem_convmoeda"  type="text" value="<?=$_itens['convmoeda']?>" onkeyup="setvalnfitem(this, <?=$_itens['idnfitem']?>, <?=$_itens['vlritemext']?>)">
                                                        <?
                                                    }
                                                    ?>
                                                </td>
                                            <?
                                            } elseif($taxaConversao == TRUE) {
                                                ?> <td></td> <?
                                            }
                                            ?>		
                                            <td class="nowrap">
                                                <?
                                                if($_itens['vlritemext']>1)   
                                                {
                                                    if($_itens['vlritem'] < 1 || empty($_itens['convmoeda'])){
                                                        $cbt="btn-danger";
                                                        $_vlritem = 0;
                                                    }else{							 
                                                        $cbt="btn-primary ";
                                                        $_vlritem = $_itens['vlritem'];							 
                                                    }
                                                }else{
                                                    $cbt="btn-success";
                                                    $_vlritem = $_itens['vlritem'];		
                                                }
                                                ?>
                                                <button setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  <?=$nfdesabled?> title="Moeda" moeda="<?=$_itens['moeda']?>"  type="button" class="btn <?=$cbt?>  btn-xs pointer" onclick="alterarMoeda(this,<?=$_itens['idnfitem']?>,'<?=$_itens['moedaext']?>')">                           
                                                    <?=$_itens['moeda']?>
                                                </button>
                                                <?if($_itens['moeda'] == "BRL"){?>
                                                    <input style="text-align: right; width: 80px;" setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  <?=$nfreadonly?> onchange="alterarCamposNf(<?=$_itens['idnfitem']?>,'nfitem','vlritem','<?=$vchecked?>',this, '<?=$_nf['idnf']?>')" id="nfitem<?=$_itens['idnfitem']?>" name="_<?=$i?>_<?=$_acao?>_nfitem_vlritem"  type="text" value="<?=$_vlritem?>">
                                                <?						
                                                }else{							
                                                    ?>							
                                                    <input style="text-align: right;" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfreadonly?> name="_<?=$i?>_<?=$_acao?>_nfitem_moedaext"  type="hidden" value="<?=$_itens['moeda']?>">
                                                    <input style="text-align: right; width: 80px;" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfreadonly?> name="_<?=$i?>_<?=$_acao?>_nfitem_vlritemext" type="text" value="<?=$_itens['vlritemext']?>">
                                                <?}?>
                                            </td>
                                            <td><input style="text-align: right;" setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  <?=$nfreadonly?> class="size6"  name="_<?=$i?>_<?=$_acao?>_nfitem_des" id="des<?=$_itens['idnfitem']?>" onchange="alterarCamposNf(<?=$_itens['idnfitem']?>, 'nfitem', 'des', '<?=$vchecked?>', this, '<?=$_nf['idnf']?>')"  type="text" value="<?=$_itens['des']?>"></td>
                                            <td><input style="text-align: right;" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfreadonly?> class="size5"  name="_<?=$i?>_<?=$_acao?>_nfitem_vst" onchange="alterarCamposNf(<?=$_itens['idnfitem']?>, 'nfitem', 'vst', '<?=$vchecked?>', this, '<?=$_nf['idnf']?>')"  type="text" value="<?=$_itens['vst']?>"></td>
                                            <td><input style="text-align: right;" setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfreadonly?> class="size5" id="ipi<?=$_itens['idnfitem']?>" name="_<?=$i?>_<?=$_acao?>_nfitem_aliqipi" onchange="alterarCamposNf(<?=$_itens['idnfitem']?>, 'nfitem', 'aliqipi', '<?=$vchecked?>', this, '<?=$_nf['idnf']?>')" type="text" value="<?=$_itens['aliqipi']?>" title="<?=number_format(tratanumero($_itens['valipi']), 2, ',', '.')?>"></td>
                                            <td  align="right">
                                                <span id="totalext<?=$_itens['idnfitem']?>">
                                                    <?
                                                    if($_itens['moeda'] == "BRL"){
                                                        echo number_format(tratanumero($_itens['total'] + $_itens['valipi']), 2, ',', '.');
                                                    }else{
                                                        echo $_itens['totalext'] + $_itens['valipi'];
                                                    }
                                                    ?>    
                                                </span>                                        
                                            </td>
                                            <td><input  setdisable<?=$_nf['idnf']?> <?=$infoStatus?> <?=$nfreadonly?> class="calendario size7" style="width: 100px;"	name="_<?=$i?>_<?=$_acao?>_nfitem_validade"  type="text" value="<?=dma($_itens['validade'])?>"></td>
                                            <td>
                                                <input  setdisable<?=$_nf['idnf']?> <?=$infoStatus?> id="nfitem_previsaoentrega<?=$_itens["idnfitem"]?>" <?=$nfdesabled?> name="_<?=$i?>_<?=$_acao?>_nfitem_previsaoentrega" _idnfitem="<?=$_itens["idnfitem"]?>" _idnf="<?=$_nf["idnf"]?>" class="calendario size7" type="text" value="<?=dma($_itens['previsaoentrega'])?>">
                                            </td>
                                            <td title="<?=$_itens['obs']?>">
                                                <? 
                                                if($_itens['obs'])
                                                {
                                                    $corIObsv = "azul";
                                                } else {
                                                    $corIObsv = "cinza";
                                                }
                                                ?>

                                                <i title="Observação" class="fa btn-sm fa-info-circle <?=$corIObsv?> pointer hoverazul tip modalObservacaoClique" idnfitem="<?=$_itens['idnfitem']?>"></i>

                                                <div class="panel panel-default" hidden>
                                                    <div id="modalObservacao<?=$_itens['idnfitem']?>" class="panel-body">
                                                        <div class="row" style="width: 100%;">
                                                            <div class="col-md-2 head" style="color:#333; text-align: right;">Observação:</div>
                                                            <div class="col-md-10">
                                                                <textarea setdisable<?=$_nf['idnf']?> onkeyup="atualizarCampo(this, '<?=$_itens['idnfitem']?>')" <?=$infoStatus?> <?=$nfreadonly?> style="height: 80px;" name="_<?=$i?>_u_nfitem_obs" id="_<?=$_itens['idnfitem']?>_nfitem_obs" type="text"><?=$_itens['obs']?></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td title="Compra Atual">
                                                <?
                                                if($_itens["vlr"] > 0){?>
                                                    <a class="pointer hoverazul"  title="Compra Atual" style="float: right;"><?=number_format(tratanumero($_itens['vlr']), 2, ',', '.')?> </a>
                                                <?}
                                                ?>
                                            </td>
                                            <td title="Última Compra" align="right">
                                                <?
                                                if(!empty($_itens['ultimacompra']))
                                                {
                                                    $dadosUltimaCompra = explode('#', $_itens['ultimacompra']);
                                                    ?>
                                                    <a class="pointer hoverazul" title="Última Compra"  style="color:green;" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$dadosUltimaCompra[0]?>')"><?=number_format(tratanumero($dadosUltimaCompra[1]), 2, ',', '.')?> </a>
                                                <?}?>
                                            </td>                                      
                                            <td>
                                                <?
                                                if(!empty($_itens['idprodserv']))
                                                {
                                                    ?>
                                                    <div class="itensSemelhantes" idnfitem="<?=$_itens["idnfitem"]?>" id="semelhantes<?=$_itens["idnfitem"]?>">
                                                        <a class="fa fa-search azul pointer btn-lg hoverazul" title="Ver Cotações" data-target="webuiPopover0"></a>
                                                    </div>
                                                    <div class="webui-popover-content">
                                                        <br />
                                                        <table class="table table-striped planilha">
                                                            <tr>
                                                                <th></th>
                                                                <th>Cotação</td>
                                                                <th>Valor Item</th>
                                                                <th>Fornecedor</th>
                                                                <th>Status</th>
                                                            </tr>                                                            
                                                            <?
                                                            foreach($infoNf['nfitens']['semelhantes'][$_itens['idprodserv']] as $_semelhantes)
                                                            {
                                                                if($_semelhantes["nfe"] == 'Y') 
                                                                {
                                                                    $checked = 'checked'; 
                                                                    $vchecked = 'N';
                                                                } else {
                                                                    $checked = ''; 
                                                                    $vchecked = 'Y';
                                                                }
                                                                ?>
                                                                <tr>
                                                                    <td><input setdisable<?=$_semelhantes['idnf']?> title="Nfe" type="checkbox" <?=$checked?> class="<?=$_semelhantes['idnf']?> inputsemelhante" id="<?=$_semelhantes["idnfitem"]?>" onclick="alterarCamposNf(<?=$_semelhantes['idnfitem']?>, 'nfitem', 'nfe', '<?=$vchecked?>', this, <?=$_nf['idnf']?>)"></td>
                                                                    <td><a href="#nftable<?=$_semelhantes["idnf"]?>"><?=$_semelhantes['idnf']?></a></td>
                                                                    <td><b>R$ <?=number_format(tratanumero($_semelhantes['vlritem']), 2, ',', '.')?></b></td>
                                                                    <td><?=$_semelhantes['nome']?></td>
                                                                    <td><?=strtoupper($_semelhantes['rotulo'])?></td>
                                                                </tr>   
                                                            <?
                                                            }
                                                            ?> 
                                                        </table>
                                                    </div>
                                                <? } ?>
                                            </td>
                                            <td>
                                                <?if(empty($nfreadonly)){?>
                                                    <a class="fa fa-download verde pointer btn-lg hoverazul" id="btdelocaitem<?=$_itens["idnfitem"]?>" title="Deslocar item" onclick="deslocar(<?=$_itens['idnfitem']?>,this)"></a> 
                                                <? } ?>
                                            </td>
                                            <td>
                                                <?if(empty($nfreadonly)){?>
                                                    <a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirItem(<?=$_itens['idnfitem']?>)" title="Excluir Item!"></a>
                                                <?}?>
                                            </td>
                                        </tr>
                                        <?    
                                        if($qtdnfitem == $qtdItens && in_array($_nf['status'], array('APROVADO', 'PREVISAO', 'DIVERGENCIA', 'CONCLUIDO', 'CONFERIDO')) && $_itens['nfe'] == 'N')
                                        {
                                            ?>
                                                    </tr>
                                                </td>
                                            </table>
                                            <? 
                                        }

                                        $qtdItens++;                                            
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="19">
                                            <table class="adicionarNovoItem<?=$_nf['idnf']?>">
                                                <tr class="trNovoItem"></tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <?
                                    if(empty($nfreadonly))
                                    {
                                    ?>
                                        <tr class="hidden" id="modeloNovoItem<?=$_nf['idnf']?>">
                                            <td></td>
                                            <td colspan="2">
                                                <input type="text" size="60" class="ui-autocomplete-input autocompletenovoitem" idnf="<?=$_nf['idnf']?>" idpessoa="<?=$_nf['idpessoa']?>" placeholder="**Novo item**" setdisable<?=$_nf['idnf']?> <?=$infoStatus?>>
                                            </td>
                                            <td colspan="13"></td>
                                        </tr>

                                        <tr class="esconderdiv">
                                            <td colspan="8">
                                                <i id="novoitem" class="fa fa-plus-circle fa-1x verde btn-lg pointer" onclick="inserirNovoItem(<?=$_nf['idnf']?>)" title="Inserir novo Item"></i>                        			
                                            </td>
                                            <td colspan="8"></td>                    
                                        </tr>
                                    <? }?>
                                    <tr class="esconderdiv">
                                        <?
                                        if($taxaConversao == TRUE){
                                            $colspanmodfrete = 9;
                                            $colspanfrete = 2;
                                        } else{
                                            $colspanmodfrete = 8;
                                            $colspanfrete = 2;
                                        } 
                                        ?>

                                        <td colspan="<?=$colspanmodfrete?>"></td>
                                        <td title="<?=CotacaoController::$tituloFrete?>" align="right" colspan="<?=$colspanfrete?>">
                                            Frete:
                                            <select setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  class="size6" name="_<?=$iobs?>_<?=$_acao?>_nf_modfrete" <?=$nfdesabled?>>
                                                <?fillselect(CotacaoController::$tipoFrete, $_nf['modfrete']);?>
                                            </select>
                                    
                                            <? 
                                            if($_nf['modfrete'] == '1')
                                            {
                                                if(!empty($_nf['idnfe']))
                                                {
                                                    $cte = CotacaoController::buscarCtePorIdNfe($_nf['idnfe'], $_nf['idnf']);
                                                }else{
                                                    $cte = CotacaoController::buscarCte($_nf['idnf']);
                                                }

                                                if(count($cte) > 0)
                                                {
                                                    ?>
                                                    <a title="CTE" class="fa fa-bars fade pointer hoverazul" href="?_modulo=nfcte&_acao=u&idnf=<?=$cte[0]['idnf']?>" target="_blank"></a>
                                                    <?
                                                } elseif($_nf['status'] == "APROVADO" || $_nf['status'] == "PREVISAO"){
                                                    ?>
                                                    <i  class="fa fa-plus-circle fa-1x verde  pointer" onclick="inserirNovoCte(<?=$_nf['idnf']?>)" title="Gerar Programação de CTe "></i>
                                                    <?
                                                }
                                            }
                                            ?>
                                        </td>
                                        <?
                                        if(empty($_nf['frete']))
                                        { 
                                            $frete = 0.00; 
                                        }else{ 
                                            $frete = $_nf['frete']; 
                                        }
                                        ?>
                                        <td>
                                            <input setdisable<?=$_nf['idnf']?> <?=$infoStatus?> style="text-align-last: end;" name="_<?=$iobs?>_<?=$_acao?>_nf_frete"	size="8"  value="<?=number_format(tratanumero($frete), 2, ',', '.');?>" vdecimal onchange="atualizarFrete(this,<?=$_nf['idnf']?>)">
                                        </td>
                                        <td colspan="7"></td>
                                    </tr>
                                    <tr class="esconderdiv">
                                        <?if($taxaConversao == TRUE){?>
                                            <td align="right" colspan="11">Subtotal: <b><?=$moeda?> </b></td>
                                        <?}else{?>
                                            <td  align="right" colspan="10">Subtotal: <b><?=$moeda?> </b></td>
                                        <?}?>
                                        <td  align="right">
                                            <b id="totalsemdesc<?=$_itens['idnf']?>">
                                                <?=number_format(tratanumero($totalsemdesc), 2, ',', '.');?>
                                            </b>
                                        </td>
                                        <td colspan="10"></td>
                                    </tr>
                                    <tr class="esconderdiv">
                                        <?if($taxaConversao == TRUE){?>
                                            <td align="right" colspan="11">Desconto: <b><?=$moeda?> </b></td>
                                        <?}else{?>
                                            <td  align="right" colspan="10">Desconto: <b><?=$moeda?> </b></td>
                                        <?}?>
                                        <td  align="right">
                                            <b id="desconto<?=$_itens['idnf']?>">
                                                <?=number_format(tratanumero($desconto), 2, ',', '.');?>
                                            </b>
                                        </td>
                                        <td colspan="10"></td>
                                    </tr>

                                    <tr class="esconderdiv">
                                        <?if($taxaConversao == TRUE){?>
                                            <td align="right" colspan="11">Total: <b><?=$moeda?> </b></td>
                                        <?}else{?>
                                            <td  align="right" colspan="10">Total: <b><?=$moeda?> </b></td>
                                        <?}?>
                                        <td  align="right">
                                            <b id="totalcomdesc<?=$_itens['idnf']?>">
                                                <? $vtotal=  $total + tratanumero($_nf['frete']); ?>
                                                <?=number_format(tratanumero($vtotal), 2, ',', '.');?>
                                            </b>
                                            <input setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  name="_<?=$iobs?>_<?=$_acao?>_nf_total" type="hidden" value="<?=$vtotal?>" vdecimal>
                                        </td>
                                        <td colspan="10"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row esconderdiv"> 	    
                                <div class="col-md-6">
                                    <div class="" >
                                        <div class="" href="#transporte<?=$iobs?>"></div>
                                        <div class="panel-body">   	    
                                            <table id="transporte<?=$iobs?>">
                                                <tr>
                                                    <td align="right">Transportadora:</td> 
                                                    <td class="nowrap">
                                                        <select setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  class="size25"  name="_<?=$iobs?>_<?=$_acao?>_nf_idtransportadora" <?=$nfdesabled?>>
                                                            <option value=""></option>
                                                            <?fillselect($fillSelectTransportadora, $_nf['idtransportadora']);?>		
                                                        </select>
                                                        <?if(!empty($_nf['idtransportadora'])){?>
                                                            <a title="Transportadora" class="fa fa-bars fade pointer hoverazul" href="?_modulo=pessoa&_acao=u&idpessoa=<?=$_nf['idtransportadora']?>" target="_blank"></a>
                                                        <?}?>
                                                    </td> 
                                                </tr> 		
                                            </table>		
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="" >
                                        <div class="" ></div>
                                        <div class="panel-body">   
                                            <table  id="Pagamento<?=$iobs?>">
                                                <?if($_nf['formapgto']){?>
                                                    <tr>
                                                        <td align="right" class="nowrap">Pag. Fornecedor:</td>
                                                        <td style="color: red;"><?=$_nf['formapgto']?></td>
                                                    </tr>
                                                <?}?>
                                                <tr>
                                                    <td align="right">Pagto:</td> 
                                                    <td >
                                                        <input setdisable<?=$_nf['idnf']?> <?=$infoStatus?> cbvalue="<?=$_nf['idformapagamento']?>"  class="size15 forma_pagamento" id="formapagamento<?=$_nf['idnf']?>" name="_<?=$iobs?>_<?=$_acao?>_nf_idformapagamento" <?=$nfdesabled?> value="<?=$jPag[$_nf['idformapagamento']]['descricao']?>">
                                                    </td>
                                        
                                                    <td align="right" class="nowrap">1º Venc:</td>	
                                                    <td><input setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  name="_<?=$iobs?>_<?=$_acao?>_nf_diasentrada"	size="2" type="text" value="<?=$_nf['diasentrada']?>" vdecimal <?=$nfreadonly?>></td>				
                                                    <td>Dias</td>                            
                                                    <td align="right">Parcelas:</td>
                                                    <td>
                                                        <select setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  class="size5" id="parcelas" name="_<?=$iobs?>_<?=$_acao?>_nf_parcelas" <?=$nfdesabled?> onchange="atualizarParcelas(this, <?=$_nf['idnf']?>)">
                                                            <option value=""></option>
                                                            <?
                                                                for($isel = 1; $isel <= 60; $isel++)
                                                                {
                                                                    if($isel == 1){
                                                                        $arrayParcelas[$isel] = $isel."x";
                                                                    } else {
                                                                        $arrayParcelas[$isel] = $isel."x";
                                                                    }
                                                                }
                                                                fillselect($arrayParcelas, $_nf['parcelas']);
                                                            ?>
                                                        </select>					
                                                    </td>
                                                    <?if($_nf['parcelas'] > 1){$strdivtab="style='display:block;'";}else{$strdivtab="style='display:none;'";}?>
                                                    <td align="right"><div class="divtab" <?=$strdivtab?> id="divtab1">Intervalo Parcelas:</div></td>
                                                    <td class='nowrap'>
                                                        <div class="divtab  nowrap" <?=$strdivtab?> id="divtab2">
                                                            <input setdisable<?=$_nf['idnf']?> <?=$infoStatus?>  <?=$nfdesabled?>  class="size4" name="_<?=$iobs?>_<?=$_acao?>_nf_intervalo" type="text" value="<?=$_nf['intervalo']?>" vdecimal  >  
                                                            Dias
                                                        </div>
                                                    </td>
                                                </tr>		
                                            </table>
                                        </div>
                                    </div>	
                                </div>
                                <div class="col-md-12">
                                    <div class="panel-body">
                                        <table>                
                                            <tr>
                                                <td align="right">Obs:</td>
                                                <td><textarea setdisable<?=$_nf['idnf']?> <?=$infoStatus?> class="caixa" <?=$nfdesabled?> name="_<?=$iobs?>_<?=$_acao?>_nf_obs" style="width: 560px; height: 50px;"><?=$_nf['obs']?></textarea></td>
                                                <td align="right">Obs. Interna:</td>
                                                <td><textarea class="caixa" name="_<?=$iobs?>_<?=$_acao?>_nf_obsinterna" style="width: 560px; height: 50px;"><?=$_nf['obsinterna']?></textarea></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>                        
                </div>
            </div>

            <div id="compra<?=$_nf['idnf']?>" style="display: none">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">			                           
                            <table>
                                <tr id="editforn">
                                    <td align="right">Fornecedor:</td>
                                    <td title="Pesquisar e inserir item exclusivo por fornecedor.">
                                        <div class="input-group" >
                                            <input id="_f_nome<?=$_nf['idnf']?>" type="text" value="<?=$_nf['nome']?>" cbvalue="<?=$_nf['idpessoa']?>" name="x_f_nome"  class="size12" disabled style="background-color: #e6e6e6; width:180px; " >
                                            <a id="editar_fornecedor" class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarFornecedor();" title="Editar fornecedor"></a>
                                        </div>
                                    </td>
                                </tr>
                                <?
                                $b = 0;
                               foreach($infoNf['nfitens'][$_nf['idnf']] as $_duplicarCompra)
                               {
                                    $b = $b + 1;
                                    ?>
                                    <tr id="tr<?=$_duplicarCompra['idnfitem']?>">                             
                                        <td ><?=$_duplicarCompra['qtdsol']?>
                                            <input  id ="quantidade<?=$_nf['idnf']?>" name="_<?=$b?>__quantidade"  type="hidden" style="width: 80px;" value="<?=$_duplicarCompra['qtdsol']?>"  >
                                            <input  id="idnfitem<?=$_nf['idnf']?>" name="_<?=$b?>__idnfitem" type="hidden" value="<?=$_duplicarCompra['idnfitem']?>" >          
                                        </td>
                                        <td colspan="2">
                                            <?if(!empty($rowiy['prodservdescr'])){?>
                                                <?=$_duplicarCompra['prodservdescr']?>                                            
                                            <?}else{?>                                            
                                                <?=$_duplicarCompra['descr']?>                                        
                                            <?}?>
                                        </td>  
                                        <td><i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluirItemDuplicarCompra(this)" alt="Excluir item!"></i></td>
                                    </tr>
                                <?}?>
                            </table>
                        </div>	
                    </div>
                </div>
            </div>
        </div>
        <?
        } 
        if(!$controleCabecarioReprovadosCancelados){
            echo "</div></div></div></div>";
        }
    }  else {// visualizacao por produto

        $prodservNf = CotacaoController::buscarProdservPelaNf($_1_u_cotacao_idcotacao);
        $qtdNf = count($prodservNf);
        $i = 1;
        $l = 0;

        if($qtdNf > 0)
        {
            foreach($prodservNf as $_nf)
            {   
                $i = $i++;
                $l = $l++;
                
                $itensNf = CotacaoController::buscarItensNfPorIdProdserv($_1_u_cotacao_idcotacao, $_nf['idprodserv']);
                ?>
                <div class="row">
                    <div class="col-md-12" >
                        <div class="panel panel-default" >
                            <div class="panel-heading"> 
                                <?=traduzid("prodserv","idprodserv","codprodserv",$_nf['idprodserv'])?>-<?=traduzid("prodserv","idprodserv","descr",$_nf['idprodserv'])?>
                            </div>
                            <div class="panel-body"> 
                                <table class="table table-striped planilha">
                                    <tr>
                                        <th style="text-align: center;width: 2%;">NF.</th>
                                        <th style="text-align: center;width: 5%;">Cotação</th>
                                        <th style="text-align: center;width: 33%;">Fornecedor</th>
                                        <th style="text-align: center;width: 5%;">Qtd</th>
                                        <th style="text-align: center;width: 10%;">Valor Un.</th>
                                        <th style="text-align: center;width: 10%;">Total</th>
                                        <th style="text-align: center;width: 5%;">Previsão Entrega</th>    
                                        <th style="text-align: center;width: 15%;">Obs.</th>
                                        <th style="text-align: center;">Status</th>
                                    </tr>
                                    <?
                                    $vtotalp = 0;
                                    foreach($itensNf as $_itens)
                                    {
                                        if($_itens['status'] == 'ENVIADO' || $_itens['status'] == 'APROVADO' || $_itens['status'] == 'PREVISAO'){
                                            $stcolor = 'background-color:#9DDBFF !important;';
                                        }else if($_itens['status'] == 'RESPONDIDO'){
                                            $stcolor = 'background-color:#f6c23e !important;';
                                        }else if($_itens['status'] == 'AUTORIZADO' || $_itens['status'] == 'AUTORIZADA' || $_itens['status'] == 'ABERTO'){
                                            $stcolor = 'background-color:mistyrose !important;';
                                        }else if($_itens['status'] == 'CONCLUIDO'){
                                            $stcolor = 'background-color:#9dffb2 !important';
                                        }else{
                                            $stcolor = 'background-color:#e6e6e6 !important;'; 
                                        }
                                        $i = $i++;
                                        $vtotalp = $vtotalp + (!empty($_itens['total']) ? $_itens['total'] : $_itens['totalext']);?>
                                        <tr>
                                            <td style="text-align: center;">
                                            <?if($_itens['status'] != 'AUTORIZADO' && $_itens['status'] != 'AUTORIZADA' && $_itens['status'] != 'ABERTO' && $_itens['status'] != 'RESPONDIDO'){
                                                $disabled = 'disabled';
                                            }else{
                                                $disabled = '';
                                            }if($_itens["nfe"] == 'Y'){
                                                    $checked = 'checked';
                                                    $vchecked = 'N';	
                                                    $total = $total + $_itens['total'];
                                                }else{
                                                    $checked = '';
                                                    $vchecked ='Y';
                                                }?>
                                                <input <?=$disabled?> title="Nfe" type="checkbox" <?=$nfdesabled?> <?=$checked?> name="namenfec" id="<?=$_itens["idnfitem"]?>" onclick="alterarCamposNf(<?=$_itens['idnfitem']?>, 'nfitem', 'nfe', '<?=$vchecked?>', this, <?=$_nf['idnf']?>)">
                                            
                                            </td>
                                            <td style="text-align: center;">
                                                <?=$_itens['idnf']?>
                                            </td>
                                            <td>
                                                <input	name="_<?=$i?>_<?=$_acao?>_nfitem_idnfitem"  type="hidden" value="<?=$_itens['idnfitem']?>">
                                                <input	name="_<?=$i?>_<?=$_acao?>_nfitem_tiponf"  type="hidden" value="C">
                                                <?=$_itens['nome']?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?=number_format(tratanumero($_itens["qtd"]), 2, ',', '.');?>
                                            </td>                                           
                                            <td  style="text-align: center;">
                                                <? 
                                                if($_itens['vlritemext'] > 1)   
                                                {
                                                    $_vlritem = $_itens['vlritemext'];							
                                                }else{
                                                    $cbt="btn-success";
                                                    $_vlritem = $_itens['vlritem'];		
                                                }
                                                echo number_format(tratanumero($_vlritem), 2, ',', '.');
                                                ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <? if($_itens['totalext'] > 1) { $totalitem = $_itens['totalext']; } else { $totalitem = $_itens['total']; }?>
                                                <?=number_format(tratanumero($totalitem), 2, ',', '.');?></td>
                                            <td style="text-align: center;">
                                                <?=dma($_itens['previsaoentrega'])?>
                                            </td>
                                            <td style="text-align: center;word-break: break-all;"><?=$_itens['obs']?></td>  
                                            <td style="text-align: center;">
                                            <label style="color: black;<?=$stcolor?>;border-color: black;font-size: 11px;padding: 3px 3px;border-radius: 3px;-webkit-box-shadow: 2px 2px 1px rgb(0 0 0 / 5%);box-shadow: 2px 2px 1px rgb(0 0 0 / 5%);">
                                                <?if($_itens['status'] == 'AUTORIZADO'){$_itens['status'] ='AUTORIZAÇÃO DIRETORIA';} elseif($_itens['status'] == 'AUTORIZADA'){$_itens['status'] ='EM APROVAÇÃO';}elseif($_itens['status'] == 'INICIO'){$_itens['status'] ='ABERTO';}?>    
                                                <?=$_itens['status']?>
                                            </label>
                                            </td> 
                                        </tr>
                                    <?}//while($_itens=mysqli_fetch_assoc($resi)){
                                    ?>
                                        <tr>
                                            <td colspan="4"></td>
                                            <td style="text-align: center;">Total:</td>
                                            <td style="text-align: center;"><b><?=number_format(tratanumero($vtotalp), 2, ',', '.');?></b></td>
                                            <td colspan="3"></td>
                                        </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?
            }//while($_nf=mysqli_fetch_assoc($resnf)){ 
        }//}else{// visualizacao por produto
    }
}

if(!empty($_1_u_cotacao_idcotacao))
{
    $solcomAssociadaCotacao = CotacaoController::buscarSolicitacaoComprasAssociadoCotacao($_1_u_cotacao_idcotacao);
    $qtdSolcom = count($solcomAssociadaCotacao);
} else {
    $qtdSolcom = 0;
}


if($qtdSolcom > 0)
{
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default" >
                <div class="panel-heading">
                    <div class="row">
                        <div class="panel-heading"  data-toggle="collapse" href="#gpOrigemTag">Origem Solicitação de Orçamento</div>
                        <div class="panel-body collapse" id="gpOrigemTag" style="padding-top: 8px !important;">
                            <table  class="table table-striped planilha"> 
                                <tr>
                                    <td>Sol. Compras</td>
                                    <td>Produto</td>
                                    <td>Cotação</td>
                                    <td>Unidade</td>
                                    <td>Status</td>
                                    <td>Criado Em</td>
                                    <td>Solicitado Por</td>
                                </tr>
                                <?
                                foreach($solcomAssociadaCotacao as $rowSolcom){?>
                                    <tr>
                                        <td>
                                            <label class="idbox">
                                                <?=$rowSolcom['idsolcom']?>
                                                <a class="fa fa-bars pointer fade" target="_blank" href="?_modulo=solcom&_acao=u&idsolcom=<?=$rowSolcom['idsolcom']?>"></a>
                                            </label>
                                        </td>
                                        <td><?=$rowSolcom["descrcurta"]?></td>
                                        <td><?=$rowSolcom["idnf"]?></td>
                                        <td><?=$rowSolcom["unidade"]?></td>
                                        <td><?=$rowSolcom["status"]?></td>
                                        <td><?=dma($rowSolcom["criadoem"])?></td>
                                        <td><?=$rowSolcom["nomecurto"]?></td>
                                    </tr>
                                <?}?>
                            </table>
                        </div>
                    </div> 
                </div>           
            </div>   
        </div>
    </div>
    <?
}    

if(!empty($_1_u_cotacao_idcotacao)){// trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_cotacao_idcotacao; // trocar p/ cada tela o id da tabela
    require '../form/viewAssinaturas.php';
}

$tabaud = "cotacao"; //pegar a tabela do criado/alterado em antigo
require '../form/viewCriadoAlterado.php';

require_once('../form/js/cotacao_js.php');
?>