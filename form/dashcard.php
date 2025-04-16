<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
	require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "dashcard";
$pagvalcampos = array(
	"iddashcard" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql       = "select * from dashcard where iddashcard = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

// CONTROLLERS
require_once(__DIR__."/controllers/dashcard_controller.php");
require_once(__DIR__."/controllers/_modulo_controller.php");
require_once(__DIR__."/controllers/_mtotabcol_controller.php");

if($_acao == "u")
{
    $lps = DashCardController::BuscarLpsVinculadasPorIdDashCard($_1_u_dashcard_iddashcard);
    $lpsDisponiveisParaVinculo = DashCardController::buscarLpsDisponiveisParaVinculoPorIdDashCard($_1_u_dashcard_iddashcard, true);
    $modulosComChavePrimaria = _moduloController::buscarModuloETabComPK(true);
}

//Recupera os modulos a serem selecionados
$tabelas = _MtoTabColController::buscarTabelas(true);

if (!empty($_1_u_dashcard_tab)) {
	$clausula = "and tc.tab ='" . $_1_u_dashcard_tab . "'";
}
?>
<link rel="stylesheet" href="/form/css/padrao.css?version=1" />
<link rel="stylesheet" href="/form/css/dashcard_css.css" />
<div class="row w-100 m-0">
    <div class="col-xs-12">
        <div class="panel panel-default" >
            <div class="panel-heading row w-100 d-flex flex-wrap">
                <? if($_acao == "u") { ?>
                    <input name="_1_<?= $_acao ?>_dashcard_iddashcard" type="hidden" value="<?= $_1_u_dashcard_iddashcard ?>" readonly='readonly'/>
                <?}?>
                <!-- Rotulo -->
                <div class="col-xs-6 col-lg-3">
                    <label for="" class="text-gray-10">Rótulo</label>
                    <input name="_1_<?= $_acao ?>_dashcard_cardtitle" type="text" value="<?= $_1_u_dashcard_cardtitle ?>" class="form-control" />
                </div> 
                <!-- Sub Rótulo (inferior) -->
                <div class="col-xs-6 col-lg-3">
                    <label for="" class="text-gray-10">Sub Rótulo (inferior)</label>
                    <input name="_1_<?= $_acao ?>_dashcard_cardtitlesub" type="text" value="<?= $_1_u_dashcard_cardtitlesub ?>" class="form-control" />
                </div> 
                <!-- Ordem -->
                <div class="col-xs-6 col-lg-2">
                    <label for="" class="text-gray-10">Ordem</label>
                    <input name="_1_<?= $_acao ?>_dashcard_ordem" type="text" value="<?= $_1_u_dashcard_ordem ?>" class="form-control" />
                </div> 
                <!-- Tipo -->
                <div class="col-xs-6 col-lg-2">
                    <label for="" class="text-gray-10">Tipo</label>
                    <select name="_1_<?= $_acao ?>_dashcard_tipoobjeto" class="form-control">
                        <?fillselect(DashCardController::$tipos, $_1_u_dashcard_tipoobjeto);?>		
                    </select>
                </div> 
                <!-- Cron -->
                <div class="col-xs-6 col-lg-2">
                    <label for="" class="text-gray-10">Cron</label>
                    <select name="_1_<?= $_acao ?>_dashcard_cron" class="form-control">
                        <?fillselect(DashCardController::$simNao, $_1_u_dashcard_cron);?>
                    </select>
                </div> 
                <!-- Executado em -->
                <div class="col-xs-6 col-lg-3">
                    <label for="" class="text-gray-10">Executado em</label>
                    <input name="_1_<?= $_acao ?>_dashcard_executadoem" readonly type="text" value="<?= $_1_u_dashcard_executadoem ?>" class="form-control" />
                </div> 
                <!-- Período ETL -->
                <div class="col-xs-6 col-lg-3">
                    <label for="" class="text-gray-10">Período ETL</label>
                    <select name="_1_<?= $_acao ?>_dashcard_periodoetl" class="form-control">
                        <?fillselect(DashCardController::$periodoEtl, $_1_u_dashcard_periodoetl);?>	
                    </select>
                </div> 
                <!-- Módulo Filtros -->
                <div class="col-xs-6 col-lg-2">
                    <label for="" class="text-gray-10">Módulo Filtros</label>
                    <select name="_1_<?= $_acao ?>_dashcard_modulofiltros" class="form-control">
                        <?
                            fillselect(DashCardController::$simNao, $_1_u_dashcard_modulofiltros);
                        ?>		
                    </select>
                </div> 
                <!-- Executar a cada -->
                <div class="col-xs-6 col-lg-2">
                    <label for="" class="text-gray-10">Executar a cada</label>
                    <select name="_1_<?= $_acao ?>_dashcard_execucao" class="form-control">
                        <option value="">Selecionar intervalo</option>
                        <?fillselect(DashCardController::$intervaloDeTempo, $_1_u_dashcard_execucao);?>
                    </select>
                </div>  
                <!-- Status -->
                <div class="col-xs-6 col-lg-2">
                    <label for="" class="text-gray-10">Status</label>
                    <select name="_1_<?= $_acao ?>_dashcard_status" class="form-control">
                        <?fillselect(DashCardController::$status, $_1_u_dashcard_status);?>		
                    </select>
                </div> 
            </div>
            <div class="panel-body"> 
                <div class="row m-0 w-100 d-flex flex-wrap">
                    <!-- Panel -->
                    <div class="col-xs-6 col-lg-4 form-group">
                        <label for="">Panel</label>
                        <div class="w-100 d-flex">
                            <div class="col-xs-11 px-0 input-group">
                                <select name="_1_<?= $_acao ?>_dashcard_iddashpanel" class="form-control">
                                    <? fillselect("select iddashpanel,concat(e.sigla, ' - ',paneltitle) as paneltitle from dashpanel p join empresa e on e.idempresa = p.idempresa where p.status = 'ATIVO' and e.status = 'ATIVO' order by paneltitle", $_1_u_dashcard_iddashpanel);?>
                                </select>
                                <div class="d-none"></div>
                            </div>
                            <? if ($_1_u_dashcard_iddashpanel) {?>
                                <div class="col-xs-1 px-0">
                                    <a class="pointer hoverazul d-block d-flex align-items-center justify-content-center btn-primary h-100" title="Panel" onclick="janelamodal('?_modulo=dashpanel&_acao=u&iddashpanel=<?= $_1_u_dashcard_iddashpanel ?>')">
                                        <i class="fa fa-bars "></i>
                                    </a>
                                </div>                                
                            <?}?>
                        </div>
                    </div>
                    <!-- Módulo -->
                    <? if($_acao == "u") { ?>
                        <div class="col-xs-6 col-lg-4 form-group">
                            <label for="">Módulo</label>
                            <div class="w-100 d-flex">
                                <div class="col-xs-11 px-0 input-group">
                                    <input type="text" name="_1_<?= $_acao ?>_dashcard_modulo" cbvalue="<?= $_1_u_dashcard_modulo ?>" value="<?= $modulosComChavePrimaria[$_1_u_dashcard_modulo]["modulo"] ?>" class="form-control" />
                                    <div class="d-none"></div>
                                </div>
                                <?if ($_1_u_dashcard_modulo) {?>
                                    <div class="col-xs-1 px-0">
                                        <a class="pointer hoverazul d-block d-flex align-items-center justify-content-center btn-primary h-100" title="Módulo" onclick="janelamodal('?_modulo=_modulo&_acao=u&idmodulo=<?= $modulosComChavePrimaria[$_1_u_dashcard_modulo]['modulo'] ?>')">
                                            <i class="fa fa-bars "></i>
                                        </a>
                                    </div>
                                <?}?>
                            </div>                        
                        </div>
                        <!-- Tabela -->
                        <div class="col-xs-6 col-lg-4 form-group">
                            <label for="">Tabela</label>
                            <div class="w-100 d-flex">
                                <div class="col-xs-11 px-0 input-group">
                                    <input type="text" name="_1_<?= $_acao ?>_dashcard_tab" cbvalue="<?= $_1_u_dashcard_tab ?>" value="<?= $tabelas[$_1_u_dashcard_tab]["tab"] ?>"  class="form-control" />
                                    <div class="d-none"></div>
                                </div>
                                <?if ($_1_u_dashcard_tab) {?>
                                    <div class="col-xs-1 px-0">
                                        <a class="pointer hoverazul d-block d-flex align-items-center justify-content-center btn-primary h-100" id="tabela" title="Tabela" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?= _DBAPP ?>.<?= $tabelas[$_1_u_dashcard_tab]['tab'] ?>')">
                                            <i class="fa fa-bars "></i>
                                        </a>
                                    </div>
                                <?}?>
                            </div>
                        </div>
                    <?}?>
                    <!-- Code -->
                    <div class="col-xs-6 col-lg-4 form-group">
                        <label for="">Code</label>
                        <textarea name="_1_<?=$_acao?>_dashcard_code" rows="3" cols="50" class="form-control default"><?= addslashes($_1_u_dashcard_code); ?></textarea>
                    </div>
                    <!-- Card URL -->
                    <div class="col-xs-6 col-lg-4 form-group">
                        <label for="">Card URL</label>
                        <textarea name="_1_<?=$_acao?>_dashcard_cardurl" rows="3" cols="50" class="form-control default"><?= addslashes($_1_u_dashcard_cardurl); ?></textarea>
                    </div>
                    <!-- Ordenação -->
                    <div class="col-xs-6 col-lg-4 form-group">
                        <label for="">Ordenação</label>
                        <div class="w-100 d-flex align-items-center">
                            <div class="col-xs-10 px-0">
                                <select name="_1_<?= $_acao ?>_dashcard_cardordenacao" class="form-control">
                                    <option value="">Selecionar Ordenação</option>
                                    <?fillselect(_MtoTabColController::buscarFiltrosPorIdDashCardEClausula($_1_u_dashcard_iddashcard, $clausula, true), $_1_u_dashcard_cardordenacao);?>
                                </select>	 
                            </div>
                            <div class="col-xs-2">
                                <select name="_1_<?= $_acao ?>_dashcard_cardsentido" class="form-control">
                                    <option value=""></option>
                                        <?fillselect("select 'asc','asc' union select 'desc','desc'", $_1_u_dashcard_cardsentido);?>        
                                </select>	
                            </div>
                        </div>
                    </div>
                    <!-- Card JS -->
                    <div class="col-xs-6 col-lg-6 form-group">
                        <label for="">Card JS</label>
                        <textarea name="_1_<?=$_acao?>_dashcard_cardurljs" rows="3" cols="50" class="form-control"><?= ($_1_u_dashcard_cardurljs); ?></textarea>
                    </div>
                    <!-- Tipo URL -->
                    <div class="col-xs-6 col-lg-6 form-group">
                        <label for="">Tipo URL</label>
                        <select name="_1_<?= $_acao ?>_dashcard_cardurltipo"  class="tipocalc">
                            <option value="">Selecionar tipo de URL</option>
                            <?fillselect("select 'LINK','Link' union select 'JS','Js'", $_1_u_dashcard_cardurltipo);?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<? if($_acao == "u") { ?>
    <div class="col-md-7">
        <div class="panel panel-default" id="layout">
            <div class="panel-heading">Layout</div>
            <div class="panel-body row w-100 d-flex flex-wrap">   
                <!-- Cor do Texto -->
                <div class="col-xs-4 form-group">
                    <label for="">Cor do Texto</label>
                    <select name="_1_<?= $_acao ?>_dashcard_cardcolor"  class="tipocalc form-control">
                            <option value="if (count(1) > 0,'danger','secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "if (count(1) > 0,'danger','secondary')"){ echo "selected";}?>>0 - Cinza/ 1 - Vermelho</option>
                            <option value="if (count(1) > 0,'primary','secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "if (count(1) > 0,'primary','secondary')"){ echo "selected";}?>>0 - Cinza/ 1 - Azul</option>
                            <option value="if (count(1) > 0,'success','secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "if (count(1) > 0,'success','secondary')"){ echo "selected";}?>>0 - Cinza/ 1 - Verde</option>
                            <option value="if (count(1) > 0,'warning','secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "if (count(1) > 0,'warning','secondary')"){ echo "selected";}?>>0 - Cinza/ 1 - Amarelo</option>
                            <option value="concat('danger')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('danger')"){ echo "selected";}?>>Vermelho</option>
                            <option value="concat('primary')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('primary')"){ echo "selected";}?>>Azul</option>
                            <option value="concat('success')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('success')"){ echo "selected";}?>>Verde</option>
                            <option value="concat('warning')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('warning')"){ echo "selected";}?>>Amarelo</option>
                            <option value="concat('secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('secondary')"){ echo "selected";}?>>Cinza</option>
                    </select>
                </div>
                <!-- Cor da Borda -->
                <div class="col-xs-4 form-group">
                    <label for="">Cor da Borda</label>
                    <select name="_1_<?= $_acao ?>_dashcard_cardbordercolor"  class="tipocalc form-control">
                        <option value="if (count(1) > 0,'danger','secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "if (count(1) > 0,'danger','secondary')"){ echo "selected";}?>>0 - Cinza/ 1 - Vermelho</option>
                        <option value="if (count(1) > 0,'primary','secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "if (count(1) > 0,'primary','secondary')"){ echo "selected";}?>>0 - Cinza/ 1 - Azul</option>
                        <option value="if (count(1) > 0,'success','secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "if (count(1) > 0,'success','secondary')"){ echo "selected";}?>>0 - Cinza/ 1 - Verde</option>
                        <option value="if (count(1) > 0,'warning','secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "if (count(1) > 0,'warning','secondary')"){ echo "selected";}?>>0 - Cinza/ 1 - Amarelo</option>
                        
                        <option value="concat('danger')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('danger')"){ echo "selected";}?>>Vermelho</option>
                        <option value="concat('primary')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('primary')"){ echo "selected";}?>>Azul</option>
                        <option value="concat('success')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('success')"){ echo "selected";}?>>Verde</option>
                        <option value="concat('warning')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('warning')"){ echo "selected";}?>>Amarelo</option>
                        <option value="concat('secondary')" <? if ( $_1_u_dashcard_cardbordercolor == "concat('secondary')"){ echo "selected";}?>>Cinza</option>	
                    </select>
                </div>
                <!-- Separador ao Final -->
                <div class="col-xs-4 form-group">
                    <label for="">Separador ao Final</label>
                    <select name="_1_<?= $_acao ?>_dashcard_cardrow" class="form-control">
                        <?fillselect(DashCardController::$simNao, $_1_u_dashcard_cardrow);?>			
                    </select>
                </div>
            </div>
        </div>
    </div>
<?}?>
<div class="col-md-5">
    <div class="panel panel-default" >
        <div class="panel-heading">Cálculo</div>
        <div class="panel-body row w-100 d-flex flex-wrap">
            <!-- Cálculo -->
            <div class="col-xs-4 form-group">
                <label for="">Cálculo</label>
                <select name="_1_<?= $_acao ?>_dashcard_calculo" id="calculo" class="form-group">
                    <option value=""></option>
                    <?fillselect(DashCardController::$simNao, $_1_u_dashcard_calculo);?>			
                </select>
            </div>
            <!-- Tipo de Cálculo -->
            <? if($_acao == "u") { ?>
                <div class="col-xs-4 form-group">
                    <label for="">Tipo de Cálculo</label>
                    <select name="_1_<?= $_acao ?>_dashcard_tipocalculo"  style="display:none;" class="tipocalc form-control">
                        <option value=""></option>
                        <?fillselect(DashCardController::$tipoCalculo, $_1_u_dashcard_tipocalculo);?>			
                    </select>
                </div>
                <!-- Coluna de Cálculo -->
                <div class="col-xs-4 form-group">
                    <label for="">Coluna de Cálculo</label>
                    <select name="_1_<?= $_acao ?>_dashcard_colcalc"  style="display:none;" class="tipocalc">
                        <option value=""></option>
                        <?fillselect(_MtoTabColController::buscarFiltrosPorIdDashCardEClausula($_1_u_dashcard_iddashcard, $clausula, true), $_1_u_dashcard_colcalc);?>
                    </select>
                </div>
            <?}?>
            <!-- Máscara de valor -->
            <div class="col-xs-4 form-group">
                <label for="">Máscara de valor</label>
                <select name="_1_<?= $_acao ?>_dashcard_mascararotulo">
                    <option value=""></option>
                    <?fillselect(DashCardController::$mascaraValor, $_1_u_dashcard_mascararotulo);?>	       
                </select>	
            </div>
        </div>
	 </div>
</div>
<div class="col-md-7">
    <div class="panel panel-default" id="modal">
        <div class="panel-heading">Modal Card</div>
        <div class="panel-body row w-100 d-flex flex-wrap">
            <!-- Card Title Modal -->
            <div class="form-group col-xs-4">
                <label for="">Card Title Modal</label>
                <input name="_1_<?= $_acao ?>_dashcard_cardtitlemodal" type="text" value="<?= $_1_u_dashcard_cardtitlemodal ?>" />
            </div>
            <!-- Card URL Modal -->
            <div class="form-group col-xs-4">
                <label for="">Card URL Modal</label>
                <input name="_1_<?= $_acao ?>_dashcard_cardurlmodal" type="text" value="<?= $_1_u_dashcard_cardurlmodal ?>" />
            </div>
        </div>
    </div>
</div>
<? if($_acao == "u") { ?>
    <div class="col-md-5">
        <div class="panel panel-default" >
            <div class="panel-heading">LP's Vinculadas</div>
            <div class="panel-body">
                <table>
                    <tr>
                        <td><input id="lpvinc" class="compacto" type="text" autocomplete="new-password" cbvalue placeholder="Selecione"></td>
                    </tr>
                </table>
                <table class='table-hover'>
                    <tbody>
                        <? foreach($lps as $lp) { ?>
                            <tr>
                                <td>
                                    <a title='Editar LP' target='_blank' href='?_modulo=_lp&_acao=u&idlp=<?=$lp["idlp"] ?>'><?= $lp["descricao"] ?></a>
                                </td>
                                <td align='center'>	
                                    <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='desvincularLp(<?= $lp["idlpobjeto"] ?>)' title='Excluir!'></i>
                                </td>
                            </tr>
                        <?}?>
                    </tbody>
                </table>
                <hr>
            </div>
        </div>
    </div>
<? } 
$tabelaDashCard = DashCardController::buscarDashCardPorTabela($_1_u_dashcard_tab);

if (empty($tabelaDashCard['tab'])){
    if($_acao == "u") { ?>
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-heading"> <span class="alert-error">Modulo não possui tabela vinculada.</span></div>
            </div>
        </div>
    <? } ?>
<? } else {
    $tabelaMtoTabCol = _MtoTabColController::buscarMtoTabColPorTabela($tabelaDashCard['tab']);
	
	if (!count($tabelaMtoTabCol)) {?>
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-heading"> <span class="alert-error">Modulo [<a class="pointer" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?= _DBAPP ?>.<?= $tabelaDashCard['tab'] ?>')"><?= $tabelaDashCard['tab'] ?></a>] não possui campo alteradopor. <br> Favor informar ao setor de TI para criação do mesmo.</span></div>
            </div>
        </div>
    <?} else {
        DashCardController::deletarFiltrosPorIdDashCardETabela($_1_u_dashcard_iddashcard, $tabelaDashCard['tab']);
        DashCardController::inserirObjetosVinculados($_1_u_dashcard_iddashcard, $tabelaDashCard['tab'], $_SESSION["SESSAO"]["IDEMPRESA"]);

        $filtrosMtoTabCol = _MtoTabColController::buscarFiltrosPorIdDashCardEClausulaDaTabela($_1_u_dashcard_iddashcard, $clausula);
?>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">Filtros Tabela            
            <a class="pointer" onclick="janelamodal('?_modulo=_mtotabcol&_acao=u&PK=<?= _DBAPP ?>.<?= $tabelaDashCard['tab'] ?>')"><?= $tabelaDashCard['tab'] ?></a>
       </div>
        <div class="panel-body">
        <?if (count($filtrosMtoTabCol)) {?>
            <table class="planilha grade compacto">
                <tr>
                    <th>Rótulo</th>
                    <th>Tipo</th>
                    <th>Where</th>
                    <th>Valor</th>
                </tr>
                <?
			$l = 99;
            foreach($filtrosMtoTabCol as $row)
            {
				$l = $l + 1;
                $bcolor = "white";
				
				if (!empty($row["valor"])) $bcolor = "#99cc99"; 
            ?>
                <tr style="background-color:<?= $bcolor ?>;">
                  
                    <td><?= $row["rotcurto"] ?>
                     <input id="iddashcard" name="_<?= $l ?>_u_dashcardfiltros_iddashcardfiltros" type="hidden" value="<?= $row["iddashcardfiltros"] ?>">
                    </td>     
                    <td><?= $row["datatype"] ?></td>                
                                        
                    <?
				if (!empty($row['dropsql'])) {
					$empresa  = explode("getidempresa('", $row["dropsql"]);
					$empresa  = explode("').", $empresa[1]);
					$empresa  = explode("','", $empresa[0]);
					$arrvalor = explode(',', $row["valor"]);
					$sqlm     = str_replace("sessao_idempresa", " idempresa=" . $_SESSION["SESSAO"]["IDEMPRESA"], $row['dropsql']);
					$sqlm     = str_replace('".getidempresa(\'' . $empresa[0] . '\',\'' . $empresa[1] . '\')."', getidempresa($empresa[0], $empresa[1]), $row['dropsql']);?>

                    <td align="center"> 
                        <select name="_<?= $l ?>_u_dashcardfiltros_sinal" vnulo>
						<option value=""></option>
                            <?fillselect(DashCardController::$sinaisSQL, $row["sinal"]);?>		
                        </select>
                    </td>
                    <td>
                        <select class="selectpicker valoresselect" multiple="multiple" data-live-search="true" onchange="atualizavalor(this,<?= $row['iddashcardfiltros'] ?>);">
                            <?$resm = SQL::ini($sqlm)::exec()->data or die("Erro configura _mtotabcol campo 	Prompt Drop sql:" . $sqlm);
                            foreach($resm as $item)
                            {
                                $selected = '';
                                if (in_array($item['id'], $arrvalor)) $selected = 'selected';
                                echo '<option data-tokens="' . retira_acentos($item['valor']) . '" value="' . $item['id'] . '" ' . $selected . ' >' . $item['valor'] . '</option>';
                            }?>
                        </select> 
                    </td> 
                    <?
				} elseif ($row["datatype"] == 'date' or $row["datatype"] == 'datetime') {?> 
                    <td>                    
                        <select   name="_<?= $l ?>_u_dashcardfiltros_sinal" vnulo>
						<option value=""></option>
                            <?fillselect(DashCardController::$sinaisSQL, $row["sinal"]);?>		
                        </select>
                    </td>
                    <td class="nowrap"> 
                        <select class="size10"  name="_<?= $l ?>_u_dashcardfiltros_valor">
						<option value=""></option>
                            <?fillselect(DashCardController::$valorsFiltro, $row["valor"]);?>		
                        </select>
                        <input class="size5" placeholder="Dia" name="_<?= $l ?>_u_dashcardfiltros_nowdias" type="text" value="<?= $row["nowdias"] ?>"> Dias
                    </td>
                <?} else {?> 
                    <td>                    
                        <select   name="_<?= $l ?>_u_dashcardfiltros_sinal" vnulo>
                            <?fillselect(DashCardController::$sinaisSQL, $row["sinal"]);?>		
                        </select>
                    </td>
                    <td>
                        <input name="_<?= $l ?>_u_dashcardfiltros_valor" type="text" value="<?= $row["valor"] ?>">  
                    </td>
 <?
				}
				
				if ($_1_u_dashcard_tipo == 'E' or $_1_u_dashcard_tipo == 'ET' or $_1_u_dashcard_tipo == 'EP') {
?>
                    <td>
                        <input name="_<?= $l ?>_u_dashcardfiltros_substituir" type="text" value="<?= $row["substituir"] ?>">  
                    </td>
                    <td>
                        <input name="_<?= $l ?>_u_dashcardfiltros_substituirtit" type="text" value="<?= $row["substituirtit"] ?>">  
                    </td>
                    <td>
                        <input name="_<?= $l ?>_u_dashcardfiltros_nomearq" type="text" value="<?= $row["nomearq"] ?>">  
                    </td>
                    <td>
                        <input name="_<?= $l ?>_u_dashcardfiltros_extensaoarq" type="text" value="<?= $row["extensaoarq"] ?>">  
                    </td>
<?
				}
?>
                </tr>
                <?
				
			} //while($row=mysqli_fetch_assoc($res)){
			
?>
            </table>
            <?
		} else {
?>
            <?
		} //if($qtd>0){
?>
        </div>
    </div>
</div>
            <?
	}
}
$tabaud = "dashcard"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>
<? if ($_GET['_acao'] == 'u') require_once(__DIR__."/js/dashcard_js.php") ?>