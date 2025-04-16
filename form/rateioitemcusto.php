<?
require_once("../inc/php/validaacesso.php");
require_once("controllers/rateioitemdest_controller.php");
require_once("controllers/empresa_controller.php");
require_once("../model/prodserv.php");
require_once("../model/nf.php");
require_once("../api/nf/index.php");
//Chama a Classe prodserv
$prodservclass = new PRODSERV();
$nfclass = new NF();

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$vencimento_1 	= $_GET["dataini"];
$vencimento_2 	= $_GET["datafim"];
$stidrateioitemdest = $_GET['stidrateioitemdest'];
$_idrateiocusto= $_GET['idrateiocusto'];


if (empty($_GET["_idempresa"])) {
	$idempresa = cb::idempresa();
} else {
	$idempresa = $_GET["_idempresa"];
}

?>
<link href="../form/css/rteioitemdest_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
<?
	if (!empty($stidrateioitemdest)) {
		$clausula .= "  and dt.idrateioitemdest in(" . $stidrateioitemdest . ") ";	
	} else {
		die("Não informado o destinos do rateio");
	}


	?>
	<div class="row" id="formulario">
		<div class="col-md-8">	
<?
$arrTipo['PRODUTO']='Produto';
$arrTipo['SERVICO']='Serviço';
?>

			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading cabecalho" style="height: 32px;">
                    <?if(empty($_idrateiocusto)){?>Filtros para Pesquisa<?}else{?>Rateios Custeados<?}?>				
					</div>
					<div class="panel-body"  >
                    <?if(empty($_idrateiocusto)){?>
                        <table>
                            <tr>
                                <td>Tipo</td>
                            </tr>
                            <tr>
                              
                                <td>
                                <select name="tiporateio" id="tiporateio" onchange="vizualizacao(this)" >
                                    <?
                                    fillselect($arrTipo);
                                    ?>				
                                </select>
                                <textarea class="hide" name="stidrateioitemdest" id="stidrateioitemdest" ><?= $stidrateioitemdest ?></textarea>
                                </td> 
                                                            
                            </tr>
                        </table>
                        <hr>
                        <table id="filtrosprodutos" style="display: table; width: 100%;">
                            <tr>
                                <td>Subcategoria</td>
                                <td>Plantel</td>
                                <td>Status do Lote</td> 
                                <td>Tipo do Produto</td>                                
                            </tr>
                            <tr>
                               <td>
                                <select  name="idtipoprodserv_pr"  id="idtipoprodserv_pr" campo="idtipoprodserv_pr"   class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                                              <?
                                            $sqltl="SELECT 
                                                    p.idtipoprodserv,concat(c.contaitem,' - ',p.tipoprodserv) as tipoprodserv
                                                FROM contaitem c
                                                JOIN objetovinculo ov ON ov.idobjetovinc = c.idcontaitem AND ov.tipoobjetovinc = 'contaitem' 
                                                join contaitemtipoprodserv cp on (cp.idcontaitem=c.idcontaitem)
                                                join tipoprodserv p on(p.idtipoprodserv=cp.idtipoprodserv and p.status='ATIVO')
                                                join prodserv ps on(ps.idtipoprodserv=p.idtipoprodserv and ps.fabricado = 'Y' and ps.tipo='PRODUTO')
                                                WHERE c.status = 'ATIVO'
                                                    and c.idempresa = ".cb::idempresa()."
                                                  group by p.idtipoprodserv order by tipoprodserv";
                                             $reslt =  d::b()->query($sqltl); 
                                             $selected='';
                                            while ($rowlt=mysqli_fetch_assoc($reslt)) {
                                       
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowlt['tipoprodserv']) . '" value="' . $rowlt['idtipoprodserv'] . '" >' . $rowlt['tipoprodserv'] . '</option>';
                                            } ?>
                                    </select>
                                </td>
                                <td>
                                    <select  name="idplantel_pr"  id="idplantel_pr" campo="idplantel_pr"   class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                                              <?
                                            $sqltl="select idplantel,plantel from plantel where status='ATIVO' and idempresa= ".cb::idempresa()." order by plantel";
                                             $reslt =  d::b()->query($sqltl); 
                                             $selected='';
                                            while ($rowlt=mysqli_fetch_assoc($reslt)) {
                                       
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowlt['plantel']) . '" value="' . $rowlt['idplantel'] . '" >' . $rowlt['plantel'] . '</option>';
                                            } ?>
                                    </select>
                                </td>
                                <td>
                                    <select  name="statuslote"  id="statuslote" campo="statuslote"   class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                                              <?
                                            $sqltl="SELECT  s.statustipo, s.rotulo
                                                    FROM fluxo f 
                                                    JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
                                                    JOIN carbonnovo._status s ON s.idstatus = fs.idstatus and s.statustipo not in ('CANCELADO','REPROVADO','RETIDO','AGUARDANDO')
                                                    WHERE f.status = 'ATIVO' AND f.modulo = 'loteproducao'
                                                    ORDER BY ordem";
                                             $reslt =  d::b()->query($sqltl); 
                                             $selected='';
                                            while ($rowlt=mysqli_fetch_assoc($reslt)) {
                                       
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowlt['rotulo']) . '" value="' . $rowlt['statustipo'] . '" >' . $rowlt['rotulo'] . '</option>';
                                            } ?>
                                    </select>
                                </td>
                                <td>
                                    <select  name="especificacaoprod"  id="especificacaoprod" campo="especificacaoprod"  class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true"  >
                                        
                                        <?                                                                      
                                          $sqltl="select 'venda' as id,'Venda' as valor union select 'produtoacabado','Produto Acabado'  union select 'fabricado','Formulado'";
                                             $reslt =  d::b()->query($sqltl); 
                                             $selected='';
                                            while ($rowlt=mysqli_fetch_assoc($reslt)) {
                                       
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowlt['valor']) . '" value="' . $rowlt['id'] . '" >' . $rowlt['valor'] . '</option>';
                                            } ?>
                                    </select>
                                </td>
                               
                            </tr>
                            <tr>
                            <td>Produto</td>     
                            </tr>
                            <tr>
                            <td>
                                  <select  name="idprodserv_pr"  id="idprodserv_pr" campo="idprodserv_pr"   class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                                              <?
                                            $sqltl="select 
                                                    idprodserv,
                                                    CASE
                                                        WHEN descrcurta is null THEN descr
                                                        WHEN descrcurta = '' THEN descr
                                                        ELSE descrcurta
                                                    END as descr
                                                from prodserv 
                                                where status='ATIVO' 
                                                and tipo='PRODUTO' 
                                                and idempresa= ".cb::idempresa()." 
                                                and fabricado='Y'
                                                 order by descr";
                                             $reslt =  d::b()->query($sqltl); 
                                             $selected='';
                                            while ($rowlt=mysqli_fetch_assoc($reslt)) {
                                       
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowlt['descr']) . '" value="' . $rowlt['idprodserv'] . '" >' . $rowlt['descr'] . '</option>';
                                            } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                Período Selecionado  
                                <label class="alert-warning">
                               <?=dma($vencimento_1)?> Até  <?=dma($vencimento_2)?>
                                </label>
                                </td>
                                <td>
                                <button id="cbPesquisar" title="Pesquisar" class="btn btn-default btn-primary" onclick="pesquisarproduto(this)">
                                    <span class="fa fa-search"></span>
                                </button> 
                                </td>
                            </tr>
                        </table>
                        <table id="filtroservicos" style="display: none; width: 100%;">
                            <tr>
                                <td>Subcategoria</td>
                                <td>Plantel</td>
                                <td>Servico</td>     
                            </tr>
                            <tr>
                               <td>
                                <select  name="idtipoprodserv_sr"  id="idtipoprodserv_sr" campo="idtipoprodserv_sr"   class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                                              <?
                                            $sqltl="SELECT 
                                                    p.idtipoprodserv,concat(c.contaitem,' - ',p.tipoprodserv) as tipoprodserv
                                                FROM contaitem c
                                                JOIN objetovinculo ov ON ov.idobjetovinc = c.idcontaitem AND ov.tipoobjetovinc = 'contaitem' 
                                                join contaitemtipoprodserv cp on (cp.idcontaitem=c.idcontaitem)
                                                join tipoprodserv p on(p.idtipoprodserv=cp.idtipoprodserv and p.status='ATIVO')
                                                join prodserv ps on(ps.idtipoprodserv=p.idtipoprodserv and ps.venda = 'Y' and ps.tipo='SERVICO')
                                                WHERE c.status = 'ATIVO'
                                                    and c.idempresa = ".cb::idempresa()."
                                                   group by p.idtipoprodserv order by tipoprodserv";
                                             $reslt =  d::b()->query($sqltl); 
                                             $selected='';
                                            while ($rowlt=mysqli_fetch_assoc($reslt)) {
                                       
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowlt['tipoprodserv']) . '" value="' . $rowlt['idtipoprodserv'] . '" >' . $rowlt['tipoprodserv'] . '</option>';
                                            } ?>
                                    </select>
                                </td>
                                <td>
                                    <select  name="idplantel_sr"  id="idplantel_sr" campo="idplantel_sr"   class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                                              <?
                                            $sqltl="select idplantel,plantel from plantel where status='ATIVO' and idempresa= ".cb::idempresa()." order by plantel";
                                             $reslt =  d::b()->query($sqltl); 
                                             $selected='';
                                            while ($rowlt=mysqli_fetch_assoc($reslt)) {
                                       
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowlt['plantel']) . '" value="' . $rowlt['idplantel'] . '" >' . $rowlt['plantel'] . '</option>';
                                            } ?>
                                    </select>
                                </td>
                                <td>                         
                                  <select  name="idprodserv_sr"  id="idprodserv_sr" campo="idprodserv_sr"   class="selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                                              <?
                                            $sqltl="select 
                                                    idprodserv,
                                                    CASE
                                                        WHEN descrcurta is null THEN descr
                                                        WHEN descrcurta = '' THEN descr
                                                        ELSE descrcurta
                                                    END as descr
                                                from prodserv 
                                                where status='ATIVO' 
                                                and tipo='SERVICO' 
                                                and idempresa= ".cb::idempresa()." 
                                                and venda='Y'
                                                 order by descr";
                                             $reslt =  d::b()->query($sqltl); 
                                             $selected='';
                                            while ($rowlt=mysqli_fetch_assoc($reslt)) {
                                       
                                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($rowlt['descr']) . '" value="' . $rowlt['idprodserv'] . '" >' . $rowlt['descr'] . '</option>';
                                            } ?>
                                    </select>
                                </td>
                                <tr>
                                    <td colspan="3"></td>
                                <td>
                                <button id="cbPesquisar" title="Pesquisar" class="btn btn-default btn-primary" onclick="pesquisarservico(this)">
                                    <span class="fa fa-search"></span>
                                </button> 
                                </td>
                            </tr>
                        </table>

                        <hr>
                        <?}//se idrateiocusto?>

<?
	//consulta dos sem rateio para enviar para abrir no modal de edição
	$_nfSemRateio = RateioItemDestController::buscarRateioCusto($clausula);
	echo "<!-- ".$_nfSemRateio['sql']." -->";
    $arrnfs=$_nfSemRateio;
    $y = 0;
    $total = 0;
    foreach($arrnfs['dados'] as $_Rateio){
        $_total = $_total + $_Rateio['rateio'];
        $y = $y + 1;
    }
?>


                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading cabecalho" style="height: 32px;">
                                ITENS DO RATEIO 
                                <div style="float: right;">                        
                                <input value="<?=$vencimento_1?>" type="hidden" name="dataini" id="dataini">
                                <input value="<?=$vencimento_2?>" type="hidden" name="datafim" id="datafim">   
                                <input value="<?=$_Rateio['idobjeto']?>" type="hidden" name="idunidade" id="idunidade">   
                                <input value="<?=$_total?>" id="valorrateiototal" type="hidden">                 
                                    <i style="float:right" class="azul pointer" title="Detalhar" data-toggle="collapse" href="#rateioitemcorpo" aria-expanded="">VALOR PARA RATEIO R$:  <?=number_format(tratanumero($_total), 2, ',', '.'); ?>  </i>                           
                                </div>
                            </div>
                            <div class="panel-body collapse"  id="rateioitemcorpo">
                                <div><i>Selecione os iten(s) para edição do rateio.</i></div>
                                <div>
                                    <!-- input placeholder="Filtrar Itens do Rateio" class="size20" style="height: 22px;" type="text" id="inputFiltro2" --> 
                                </div>
                                
                                <div class="table table-striped planilha panel panel-default " style="width:100%;font-size:9px;" >
                                    <div class="col-md-12 row rowcab panel-heading" style="margin:0px; font-size:9px;">
                                        <div class="col-md-6">
                                            <div class="col-md-1"><!-- input type="checkbox" name="marcardesmarcar"  checked class="pointer" title="Marcar/Desmarcar todos" onclick="selecionar(this,'inputcheckbox')" --> </div>
                                            <div class="col-md-2">QTD</div>
                                            <div class="col-md-2">UN</div>
                                            <div class="col-md-7">ITEM</div>
                                        </div>
                                        <div class="col-md-1 text-al-r">VLR. R$</div>
                                        <div class="col-md-2 text-al-r">DESTINO</div>
                                        <div class="col-md-1 text-al-r">RATEIO %</div>
                                        <div class="col-md-1 text-al-r">DETALHES</div>
                                        <div class="col-md-1 text-al-r">                                   
                                        </div>					

                                    </div>
                                    <?
                                    $i = 0;
                                    $total = 0;
                                    $semrateio='N';
                                    foreach($_nfSemRateio['dados'] as $_semRateio)
                                    {
                                        $i = $i + 1;
                                        $total = $total + $_semRateio['rateio'];

                                        if ($_semRateio['tipoobjeto'] == 'unidade') {
                                            $rateio = $_semRateio['empresa'];
                                            $rateiostr= $_semRateio['empresa'];
                                        } /*elseif ($_semRateio['tipoobjeto'] == 'sgdepartamento') {
                                            $rateio = RateioItemDestController::buscarDepartamentoSgDepartamentoPorIdSgDepartamento($_semRateio['idobjeto']);
                                        } elseif ($_semRateio['tipoobjeto'] == 'pessoa') {
                                            $rateio = RateioItemDestController::buscarPessoaPorIdPessoa($_semRateio['idobjeto']);
                                        } elseif ($_semRateio['tipoobjeto'] == 'empresa') {
                                            $rateio = RateioItemDestController::buscarEmpresaPorIdEmpresa($_semRateio['idobjeto']);
                                        }*/ else {
                                            $semrateio='Y';
                                            $rateio = "<font color='red'>Sem rateio</font>";
                                            $rateiostr="Sem rateio";
                                        }

                                        //Itens relacionados	
                                        ?>
                                        <div class="col-md-12 row rowitem itemrateio" title="Rateio selecionado na tela Rateio Item" style="margin:0px;" data-text="<?=$_semRateio['descr']?> <?=$rateiostr?>">
                                            <div class="col-md-6 inputcheckbox">
                                                <div class="col-md-1">
                                                    <!-- input type="checkbox" checked class="changeacao" acao="i" atname="checked[<?=$i ?>]" value="<?=$_semRateio['idrateioitemdest'] ?>" style="border:0px" -->
                                                    <input class="rateioitem idrateioitemdest" name="_<?=$i ?>_u_rateioitemdest_idrateioitemdest" type="hidden" value="<?=$_semRateio['idrateioitemdest'] ?>">
                                                    <input class="rateioitem" name="_<?=$i ?>_u_rateioitemdest_idrateioitem" type="hidden" value="<?=$_semRateio['idrateioitem'] ?>">
                                               									
                                                </div>
                                                <div class="col-md-2"><?=$_semRateio['qtd'] ?></div>
                                                <div class="col-md-2"><?=$_semRateio['un'] ?></div>
                                                <div class="col-md-7"><?=$_semRateio['descr'] ?> </div>
                                            </div>
                                            <div class="col-md-1 " style="text-align: right;">

                                                <? if (!empty($_semRateio['idnf']) and $_semRateio['tipo']=='nfitem') { ?>
                                                    <a class="hoverazul pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$_semRateio['idnf'] ?>')" title="Compra">
                                                        <?=number_format(tratanumero($_semRateio['rateio']), 2, ',', '.'); ?>
                                                    </a>
                                                <? } else { ?>
                                                    <?=number_format(tratanumero($_semRateio['rateio']), 2, ',', '.'); ?>
                                                <? } ?>

                                            </div>
                                            <div class="col-md-2" >
                                                <?
                                                if (!empty($_semRateio['idpessoa'])) {
                                                    echo traduzid("pessoa", "idpessoa", "nome", $_semRateio['idpessoa']);
                                                }
                                                
                                                echo $rateio;
                                                ?>
                                            </div>
                                            <div class="col-md-1" style="text-align: right;">
                                                <?=$_semRateio['valor'] ?>%
                                            </div>
                                            <div class="col-md-1 nowrap" style="text-align: center;">
                                                <?
                                                if ($_semRateio['tipo'] == 'nfitem') { ?>											
                                                    <a title="Compra" class="fa fa-search fa-1x hoverazul pointer" onclick="showhistoricoitem(<?=$_semRateio['idtipo'] ?>);"></a>
                                                <? } ?>
                                            </div>
                                            <div class="col-md-1 nowrap" style="text-align: right;">
                                                <? if ($_semRateio['valor'] < 100 and $_semRateio['valor'] !=0) { ?>
                                                    <i class="fa fa-arrows-v fa-1x cinzaclaro pointer" title="Ocultar/Desocultar <?=$qtdx ?> iten(s) relacionado(s)" data-toggle="collapse" href="#col<?=$_semRateio['idrateioitem'] ?>" onclick="carregasub(<?=$_semRateio['idrateioitem'] ?>,<?=$_semRateio['idrateioitemdest'] ?>)"></i>
                                                <? } ?>
                                            </div>
                                        </div>
                                        <div class="collapse" id="col<?=$_semRateio['idrateioitem'] ?>"></div>
                                        <?
                                    }
                                    ?>
                                    <div class="col-md-12 row rowitem"  style="margin:0px; font-size:9px;background:#ddd;font-weight:bold;">
                                        <div class="col-md-6">TOTAL:</div>
                                        <div class="col-md-1 " style="text-align: right;">
                                            <?=number_format(tratanumero($total), 2, ',', '.'); ?>
                                        </div>
                                        <div class="col-md-2" style="text-align: right;"></div>
                                        <div class="col-md-1" style="text-align: right;"></div>
                                        <div class="col-md-1" style="text-align: right;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                        <div class="panel panel-default">
                            <div  class="panel-body"  id="resultado">
                                    <!-- RETORNO DA PESQUISA AJAX -->
                            </div>
                        </div>
                    </div>
                </div>  
			</div>	
      

		</div>
		<div class="col-md-4">
		  	<div class="panel panel-default">
				<div class="panel-heading cabecalho" style="height: 32px;">
					TIPO DE RATEIO	
                    <div style="float: right;">
                    <?if(empty($_idrateiocusto)){?>
						<button id="cbalterar" type="button" class="btn btn-success btn-xs hidden" onclick="custeartodos()" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
						<button id="cbalterar2" style="border-color:#5cb85c1f !important; background-color:#5cb85c1f !important;" type="button" class="btn btn-success btn-xs" onclick="alert('É necessário completar o valor de 100% para o rateio.')" title="Salvar">
							<i class="fa fa-circle"></i>Salvar
						</button>
                    <?}else{ ?>
                        <button id="cbalterar" type="button" class="btn btn-success btn-xs" onclick="limparcustos()" title="Salvar">
							<i class="fa fa-circle"></i>Limpar
						</button>
                    <?}?>
					</div>			
				</div>
				<div class="panel-body">
					<div class="col-md-12 ">
                    <?if(empty($_idrateiocusto)){?>
                        <div><i>Selecione abaixo o tipo de rateio que será realizado.</i></div>
						<br>
                        <div class="table table-striped planilha">
                           
                                <div class="col-md-12">   
                                    Tipo de Rateio
                                </div>
                           
                                <div class="col-md-12">
                                   
                                <select name="tipo" id="tipo" onchange="gerarrateio(this)" >
                                    <?
                                    fillselect(" select 'SELECIONE','Selecione o Tipo de Rateio' union select 'QUANTIDADE','Quantidade produzida' union select 'CUSTO','Custo de Produção' union select 'VALOR VENDA','Valor de Venda' union select 'VOLUME','Volume de Produção'");
                                    ?>				
                                </select>	
                                </div>
                            
                        </div>
						<table class="table table-striped planilha " id="tbrateio" >
							<thead>
								<tr class="rowcab">
									<td colspan="4">
										<input placeholder="Filtrar" style="height: 22px; width: 90%;" type="text" id="inputFiltroempresa"> 
									</td>
								</tr>
							</thead>
							<tbody id="resultadolotes">
															
							</tbody>
						</table>
                    <?}else{// ja está custeado

                        $sqlc="select * from rateiocusto o where idrateiocusto=".$_idrateiocusto;
                        $resc=  d::b()->query($sqlc) or die("Falha ao buscar rateio custo: <p>SQL: $sql"); 
                        $rowc = mysqli_fetch_assoc($resc);


                        if($rowc['tiporateio']=='QUANTIDADE'){
                            $strrateio='Quantidade Produzida';
                        }elseif($rowc['tiporateio']=='CUSTO'){
                            $strrateio='Custo de Produção';
                        }elseif($rowc['tiporateio']=='VALOR VENDA'){
                            $strrateio='Valor de Venda';
                        }elseif($rowc['tiporateio']=='VOLUME'){
                            $strrateio='Volume de Produção';
                        }
?>
                    <div class="table table-striped planilha">
                           
                           <div class="col-md-12">   
                               Tipo do Rateio
                           </div>
                      
                           <div class="col-md-12">
                              
                           <select name="tipo" id="tipo" disabled='disable' >
                               <?
                               fillselect(" select 'SELECIONE','Selecione o Tipo de Rateio' union select 'QUANTIDADE','Quantidade produzida' union select 'CUSTO','Custo de Produção' union select 'VALOR VENDA','Valor de Venda' union select 'VOLUME','Volume de Produção'",$rowc['tiporateio']);
                               ?>				
                           </select>	
                           </div>
                       
                   </div>
                   <table class="table table-striped planilha " id="tbrateio" >
                       <thead>
                           <tr class="rowcab">
                               <td colspan="4">
                                   <input placeholder="Filtrar" style="height: 22px; width: 90%;" type="text" id="inputFiltroempresa"> 
                               </td>
                           </tr>
                       </thead>
                       <tbody>

<?  
                       
                        $sql=" select l.idlote,concat(l.partida,'/',l.exercicio) as partida,l.vlrlote,l.vlrlotetotal,l.qtdprod,l.qtdprod_exp,p.descr,l.unlote,l.status,o.valor,o.criadopor,o.criadoem,c.idlotecusto,c.valor,o.tiporateio,o.valorun
                        from lotecusto c 
                            join rateiocusto o on(o.idrateiocusto=c.idrateiocusto)
                            join lote l on ( l.idlote =c.idlote)
                            join prodserv p on(p.idprodserv=l.idprodserv )
                            join prodservformula f on(f.idprodservformula = l.idprodservformula )
                        where c.idrateiocusto in (".$_idrateiocusto.") order by l.vlrlotetotal,partida desc";

                        $res=  d::b()->query($sql) or die("Falha ao buscar lotes que ja estão na rateio custo: <p>SQL: $sql");  
                        $qtd=mysqli_num_rows($res);
                        $li = 10;
                        $valorRatear=$rowc['valor'];
                        ?>
                        <tr class="rowcab unidade" style="background:#ddd;">
                            <td colspan="5" style="height: 40px; text-align-last: center;">
                                <b>CUSTOS RATEADOS</b>
                                <input value="<?=$_idrateiocusto?>" type="hidden" name="idrateiocusto" id="idrateiocusto">
                            </td>
                        </tr>
                        <tr class="rowcab unidade" style="background:#ddd;">
                            <td colspan="5" style="text-align-last: center;">
                               Valor para Rateio: <b><?=number_format(tratanumero($rowc['valor']), 2, ',', '.');?></b>
                            </td>
                        </tr>
                        <tr class="rowcab unidade" style="background:#ddd;">
                            <td colspan="5" style="text-align-last: center;">
                                <input value="<?=$valoRateioUn?>" type="hidden" name="valorrateioun" id="valorrateioun">
                                Valor Rateio por <?=$strrateio?>:<b><?=number_format(tratanumero($rowc['valorun']), 2, ',', '.');?></b>
                                <!-- <?=$sqlQ?> -->
                            </td>
                        </tr>
                        <tr class="" style="background:#ddd;">
                            <td colspan="5" style="text-align-last: center;">
                               Criado por: <?=$rowc['criadopor']?> em: <?=dmahms($rowc['criadoem'])?>
                            </td>
                        </tr>
                        <tr class="rowcab unidade" style="background:#ddd;">
                            <td colspan="5" style="height: 40px; text-align-last: center;">
                             
                            </td>
                        </tr>
                        <tr>
                            <th>
                                PARTIDA
                            </th>
                            <th>
                                QTD
                            </th>
                            <th>
                                UN
                            </th>
                            <th title="VALOR RATEADO PARA O LOTE">
                                R$ RATEADO 
                            </th>
                            <th class="nowrap" title="CUSTO ATUAL DO LOTE">
                                CUSTO LOTE     
                            </th>
                        </tr>

                        <?
                        $li=0;
                        while($row = mysqli_fetch_assoc($res)) {
                            $li = $li + 1;
                            if(empty($row['vlrlote'])){
                                $row['vlrlote']=0.00;
                            }
                            $valoRateioItem = $row[$campo] * $valoRateioUn;
                            $totalprod=$totalprod+$row['qtdprod'];
                        ?>
                            <tr class="empresa" style="width:100%;" data-text="<?=$row['partida']?>">								
                                <td >
                                    <?=$row['partida']?>
                                </td>
                                <td style="text-align-last: right;" >
                                    <?=$row['qtdprod']?>
                                </td>
                                <td  >
                                    <?=$row['unlote']?>
                                </td>
                                <td style="text-align-last: right;" title="VALOR RATEADO PARA ESTE LOTE">        
                                <?=number_format(tratanumero($row['valor']), 2, ',', '.');?>  
                              </td>
                                <td style="text-align-last: right;" class="nowrap" title="CUSTO ATUAL DO LOTE">
                                    <?=number_format(tratanumero($row['vlrlotetotal']), 2, ',', '.');?>     
                                </td>
                                
                            </tr>

                        <?
                        }
                        $rateioporun=$valorRatear/$totalprod;
                        ?>                            
                       <tr class="rowcab unidade" style="background:#ddd;">
                            <td></td>
                            <td  style="text-align-last: right;" title="SOMA DA QUANTIDADE">
                            <b><?=number_format(tratanumero($totalprod), 2, ',', '.');?></b>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        </tbody>
                    </table>
                       <?
                    }// else idrateiocusto?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?

require_once('../form/js/rateioitemdest_js.php');
require_once('../form/js/rateioitemcusto_js.php');
?>