<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
	include_once("../inc/php/cbpost.php");
}

//Parametros mandatarios para o carbon
$pagvaltabela = "sgdoctipo";
$pagvalcampos = array(
	"idsgdoctipo" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from sgdoctipo where  idsgdoctipo = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");
function getJSetorvinc() 
{
	global $JSON,$_1_u_sgdoctipo_idsgdoctipo;
	$sql = "SELECT i.idimgrupo, concat(e.sigla,' - ',i.grupo) as grupo, (SELECT fx.idfluxo from fluxo fx where fx.modulo = 'documento' and fx.idobjeto='".$_1_u_sgdoctipo_idsgdoctipo."') as idfluxo
            FROM imgrupo i
			JOIN empresa e on (e.idempresa = i.idempresa)
            WHERE  i.status='ATIVO'
			-- ".getidempresa('i.idempresa', 'imgrupo')."
                AND NOT EXISTS(
                    SELECT 1
                        FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
                        WHERE ms.idobjeto = '".$_1_u_sgdoctipo_idsgdoctipo."' AND ms.tipoobjeto = 'idsgdoctipo'
                        AND r.tipoobjeto ='imgrupo'
                        AND i.idimgrupo = r.idobjeto)
            ORDER BY grupo ASC";

	$rts = d::b()->query($sql) or die("getJSetorvinc: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["idimgrupo"];
		$arrtmp[$i]["label"] = $r["grupo"];
		$arrtmp[$i]["fluxo"] = $r["idfluxo"];
		$i++;
	}

	return $JSON->encode($arrtmp);
}

function getJfuncionario() 
{	
	global $JSON, $_1_u_sgdoctipo_idsgdoctipo;
	
	$sql = "SELECT a.idpessoa, concat(e.sigla, ' - ',ifnull(a.nomecurto,a.nome)) as nomecurto, (SELECT fx.idfluxo from fluxo fx where fx.modulo = 'documento' and fx.idobjeto='".$_1_u_sgdoctipo_idsgdoctipo."') as idfluxo
			  FROM pessoa a 
			  JOIN objempresa oe on oe.idobjeto = a.idpessoa 
			  JOIN empresa e on e.idempresa = a.idempresa
			 WHERE a.status ='ATIVO'
			-- ".getidempresa('oe.empresa', 'pessoa')."
			   AND (a.idtipopessoa = 1)
               AND NOT a.usuario is null
               AND NOT EXISTS(
					SELECT 1
						FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
						WHERE ms.idobjeto = '".$_1_u_sgdoctipo_idsgdoctipo."' AND ms.tipoobjeto = 'idsgdoctipo'
						 AND r.tipoobjeto ='pessoa'
						 AND a.idpessoa = r.idobjeto)
			UNION
                SELECT p.idpessoa, concat(ee.sigla, ' - ',ifnull(p.nomecurto,p.nome)) as nomecurto, (SELECT fx.idfluxo from fluxo fx where fx.modulo = 'documento' and fx.idobjeto='".$_1_u_sgdoctipo_idsgdoctipo."') as idfluxo
				  FROM pessoa p
				  JOIN empresa ee on (ee.idempresa = p.idempresa)
				 WHERE p.status ='ATIVO'
				-- ".getidempresa('p.idempresa','pessoa')."
				  AND p.idtipopessoa in (15, 16, 113)
				  AND NOT p.usuario is null
				  AND NOT EXISTS(
					    SELECT 1
						  FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
						 WHERE ms.idobjeto = '".$_1_u_sgdoctipo_idsgdoctipo."' AND ms.tipoobjeto = 'idsgdoctipo'
						   AND r.tipoobjeto ='pessoa'
						   AND p.idpessoa = r.idobjeto)						
			ORDER BY nomecurto asc";

	$rts = d::b()->query($sql) or die("oioi: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"]=$r["idpessoa"];
		$arrtmp[$i]["label"]= $r["nomecurto"];
		$arrtmp[$i]["fluxo"] = $r["idfluxo"];
		$i++;
	}
	
	return $JSON->encode($arrtmp);    
}
function listaobjeto()
{   
    global $_1_u_sgdoctipo_idsgdoctipo;
    $s = "SELECT 
	d.idfluxoobjeto,
	CASE 
	WHEN d.tipoobjeto = 'imgrupo' then concat (ee.sigla,' - ',g.grupo)
	else concat (e.sigla,' - ',p.nomecurto) end as nome,       
	gp.idimgrupo,
	d.criadopor,
	d.criadoem
	FROM
	fluxoobjeto d
		JOIN
	fluxo f ON (f.idfluxo = d.idfluxo)
		JOIN
	sgdoctipo st ON (st.idsgdoctipo = f.idobjeto)
		LEFT JOIN
	imgrupopessoa gp on((gp.idimgrupo = d.idobjeto and d.tipoobjeto = 'imgrupo') or (gp.idpessoa = d.idobjeto and d.tipoobjeto = 'pessoa'))
	LEFT JOIN imgrupo g on (g.idimgrupo =gp.idimgrupo)
	LEFT JOIN pessoa p on (p.idpessoa = gp.idpessoa)
	LEFT JOIN empresa e on((e.idempresa = p.idempresa))
	LEFT JOIN empresa ee on((ee.idempresa = g.idempresa))
	WHERE st.idsgdoctipo ='$_1_u_sgdoctipo_idsgdoctipo'
	group by d.idobjeto";

    $rts = d::b()->query($s) or die("listaPessoa: ". mysql_error(d::b()));
   
    while ($r = mysqli_fetch_assoc($rts)) 
    {
       
		$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='retiraeventotiporesp(".$r["idfluxoobjeto"].")'></i>";   
        $title='Vinculado por: '.$r["criadopor"].' - '.dmahms($r["criadoem"],true);    
        
        echo "<tr id=".$r["idfluxoobjeto"]."  title='".$title."'> 
                <td style='min-width: 10px;' id='statuses'><span class='circle button-blue'></span></td><td>".$r["nome"]."</td><td>".$botao."</td>
            </tr>";                                                                
    }
}

?>


<style>
	#editor1Container {
		height: 90vh;
	}

	#editor1 {
		height: 90vh;
		width: 100%;
		overflow-y: scroll;
		background-color: white;
	}

	.transparente {
		opacity: 0;
		transition: opacity .25s ease-in-out;
		-moz-transition: opacity .25s ease-in-out;
		-webkit-transition: opacity .25s ease-in-out;
	}

	.opaco {
		opacity: 1;
		transition: opacity .25s ease-in-out;
		-moz-transition: opacity .25s ease-in-out;
		-webkit-transition: opacity .25s ease-in-out;
	}

	.config-status {
		display: flex;
	}

	.panel-status {
		height: auto;
		padding: 5px;
		flex-direction: column;
	}
</style>
<?
if (!empty($_1_u_sgdoctipo_idsgdoctipo)) {
	$readonly='readonly="readonly"';
}else {
	$readonly='';
}
?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<table>
					<tr>
						<td>Tipo:</td>
						<td>
							<input <?=$readonly?> name="_1_<?= $_acao ?>_sgdoctipo_idsgdoctipo" type="text" value="<?= $_1_u_sgdoctipo_idsgdoctipo ?>" vnulo>
						</td>
						<td>Rotulo:</td>
						<td>
							<input name="_1_<?= $_acao ?>_sgdoctipo_rotulo" value="<?= $_1_u_sgdoctipo_rotulo ?>" vnulo>
						</td>
						<td>Status:</td>
						<td>
							<select name="_1_<?= $_acao ?>_sgdoctipo_status">
								<?fillselect("select 'ATIVO','ATIVO' union select 'INATIVO','INATIVO'",$_1_u_sgdoctipo_status);?>
							</select>
						</td>
					</tr>
				</table>

			</div>
			<div class="panel-body">
				<table>
					<tr>
						<td>
							<div class="input-group" style="margin-top: 5px;" >
							<?if($_1_u_sgdoctipo_fltreinamento=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                                    <span class="input-group-addon">
                                            <input title="Treinamento" type="checkbox" aria-label="..." value="" onchange="alteraflg('<?=$_1_u_sgdoctipo_idsgdoctipo?>','<?=$valp?>','fltreinamento');" <?=$ck?>>
                                    </span>
                                    <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Treinamento" readonly="">
                            </div>
						</td>
						<td>
							<div class="input-group" style="margin-top: 5px;" >
							<?if($_1_u_sgdoctipo_flavaliacao=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                                    <span class="input-group-addon">
                                            <input title="Avaliação" type="checkbox" aria-label="..." value="" onchange="alteraflg('<?=$_1_u_sgdoctipo_idsgdoctipo?>','<?=$valp?>','flavaliacao');" <?=$ck?>>
                                    </span>
                                    <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Avaliação" readonly="">
                            </div>
						</td>
						<td>
							<div class="input-group" style="margin-top: 5px;" >
							<?if($_1_u_sgdoctipo_flversao=='N'){$valp='Y'; $ck="";  }else{$valp='N'; $ck="checked='checked'";}?>
                                    <span class="input-group-addon">
                                            <input title="Versão" type="checkbox" aria-label="..." value="" onchange="alteraflg('<?=$_1_u_sgdoctipo_idsgdoctipo?>','<?=$valp?>','flversao');" <?=$ck?>>
                                    </span>
                                    <input style="background: #eee;"  type="text" class="form-control" aria-label="..." value="Versão" readonly="">
                            </div>
						</td>
					</tr>
				</table>
			</div>
			<?if ($_1_u_sgdoctipo_idsgdoctipo) {
			$sqlfluxo='select * from fluxo where idobjeto="'.$_1_u_sgdoctipo_idsgdoctipo.'" and tipoobjeto="idsgdoctipo" and modulo="documento"';
			$rts = d::b()->query($sqlfluxo) or die("Consulta do fluxo: ". mysql_error(d::b()));
			$numfluxo = mysqli_num_rows($rts);
			if ($numfluxo > 0) {?>
				<div class="panel-body" style="margin-left: 0px;">
                    <div class="col-lg-4">
                        <div class=" panel-status participantes">                                                            
                            <table>
                                <tr id="menuPermissoes">
                                    <td>Criador:</td>
                                    <td id="tdfuncionario2">
                                        <input id="eventoresp2" class="compacto" type="text" cbvalue placeholder="Selecione">
                                    </td>
                                    <td id="tdsgsetor2">
                                        <input id="sgsetorvinc2" class="compacto" type="text" cbvalue placeholder="Selecione">
                                    </td>
                                    <td class="nowrap" style="width: 110px">
                                        <div class="btn-group nowrap" role="group" aria-label="...">
                                            <button onclick="showfuncionario2()" type="button" class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright selecionado" title="Selecionar Funcionário" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
                                            <button onclick="showsgsetor2()" type="button" class=" btn btn-default fa fa-users hoverlaranja pointer floatright " title="Selecionar Setor" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>										
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <table class='table table-striped planilha'>
                                <?=listaobjeto()?>							
                            </table>								
                        </div>
                    </div>
				</div>
			<?}else {?>
				<div class="alert alert-warning" role="alert" style="text-transform:uppercase;">
					<div class="row">
						<div class="col-md-12 center"><b>Nenhum fluxo cadastrado</b><br>Favor solicitar fluxo via SUPORTE TI</div>
					</div>
				</div>
				<!-- <div class="panel-body" style="margin-left: 0px;">
                    <div class="col-lg-4">
                        <div class=" panel-status participantes">                                                            
                            <table>
                                <tr id="menuPermissoes">
                                    <td>
										Nenhum fluxo cadastrado
                                    </td>
                                </tr>
                            </table>								
                        </div>
                    </div>
				</div> -->
			<?}?>

	<?}?>
		</div>
	</div>
</div>
<?
$tabaud = "sgdoctipo"; 
require 'viewCriadoAlterado.php';

$jSgsetorvinc 		= getJSetorvinc();
$jFuncionario   	= getJfuncionario();
?>
<script>
<? if($_1_u_sgdoctipo_idsgdoctipo){ ?>
	
    $('#tdsgsetor2').hide();
    $('#tdfuncionario2').show();

    jSgsetorvinc    	= <?=$jSgsetorvinc?>;
    jFuncionario    	= <?= $jFuncionario ?>;
//Autocomplete de Setores vinculados
$("#eventoresp2").autocomplete({
	source: jFuncionario,
	delay: 0,
	create: function() {
		$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
			lbItem = item.label;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	},
	select: function(event, funcionario) {            
		CB.post({
			objetos: {
				"_x_i_fluxoobjeto_idfluxo":funcionario.item.fluxo
				,"_x_i_fluxoobjeto_idobjeto": funcionario.item.value
				,"_x_i_fluxoobjeto_tipoobjeto": 'pessoa'
				,"_x_i_fluxoobjeto_tipo": 'CRIADOR'
			}
			,parcial: true
		});
	}
});
$("#sgsetorvinc2").autocomplete({
	source: jSgsetorvinc,
	delay: 0,
	create: function() {
		$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
			lbItem = item.label;
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	},
	select: function(event, setor) {
		
			CB.post({
				objetos: {
					"_x_i_fluxoobjeto_idfluxo":setor.item.fluxo
					,"_x_i_fluxoobjeto_idobjeto": setor.item.value
					,"_x_i_fluxoobjeto_tipoobjeto": 'imgrupo'
					,"_x_i_fluxoobjeto_tipo": 'CRIADOR'
				}
			,parcial: true
		});
	}
});
<?}?>
function alteraflg(inid,invalor,incampo){
	
    CB.post({
        objetos: "_x_u_sgdoctipo_idsgdoctipo="+inid+"&_x_u_sgdoctipo_"+incampo+"="+invalor
		,parcial: true        
    });
}
function showfuncionario2() {
        $('#tdsgsetor2').hide();
        $('#tdfuncionario2').show();
    }

function showsgsetor2() {
	$('#tdsgsetor2').show();
	$('#tdfuncionario2').hide();
}
function retiraeventotiporesp(inid){
	CB.post({
		objetos: {
			"_x_d_fluxoobjeto_idfluxoobjeto":inid
		}
		,parcial: true
	});
}
</script>