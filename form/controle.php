<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/controle_controller.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "controle";
$pagvalcampos = array(
    "idcontrole" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from controle where idcontrole = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */

include_once("../inc/php/controlevariaveisgetpost.php");

$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"]);

if(empty($_1_u_lote_idunidade)){
   $_1_u_controle_idunidade=$idunidadepadrao; 
}
?>

<?if($_1_u_controle_status=='VALIDADO' or $_1_u_controle_status=='CANCELADO'){
 $readonly= "readonly='readonly'";
 $disabled = "disabled='disabled'";
}?>
<div class='row'> 	
    <div class="col-md-9">
    <div class="panel panel-default" >
        <div class="panel-heading ">Controle de Ensaio</div>
        <div class="panel-body">
	<table>
	<tr> 
		<td class="lbr">Vacina:</td> 
		<td>
					<input  size ="8"  name="_1_<?=$_acao?>_controle_idunidade"  type="hidden" value="<?=$_1_u_controle_idunidade?>" readonly='readonly'>
                    <input <?=$readonly?> name="_1_<?=$_acao?>_controle_vacina"	type="text"	value="<?=$_1_u_controle_vacina?>" vnulo>
                    <input name="_1_<?=$_acao?>_controle_idcontrole"	type="hidden"	value="<?=$_1_u_controle_idcontrole?>"	readonly='readonly'	>
                </td> 
		<td class="lbr">Lote:</td> 
		<td><input <?=$readonly?> name="_1_<?=$_acao?>_controle_lote" type="text" value="<?=$_1_u_controle_lote?>" vnulo></td> 
		<td class="lbr">Fabricante:</td> 
		<td ><input <?=$readonly?> name="_1_<?=$_acao?>_controle_fabricante" type="text"	value="<?=$_1_u_controle_fabricante?>"></td> 
		
	</tr>
	<tr>	
		<td class="lbr">Fabricação:</td> 
		<td>
			<input  <?=$disabled?> name="_1_<?=$_acao?>_controle_fabricacao" class="calendario" id ="fdata" type="text" size ="8" value="<?=$_1_u_controle_fabricacao?>">
											
		</td> 
		<td class="lbr">Vencimento:</td> 
		<td>
			<input  <?=$disabled?> name="_1_<?=$_acao?>_controle_vencimento"   class="calendario"  id ="fdata2" type="text" size ="8" value="<?=$_1_u_controle_vencimento?>">
		</td> 
		<td class="lbr">Status:</td> 
		<td>
			<select <?=$disabled?> name="_1_<?=$_acao?>_controle_status">
				<?fillselect("select 'EM ANDAMENTO','Em Andamento' union select 'VALIDADO','Validado' union select 'CANCELADO','Cancelado'",$_1_u_controle_status);?>		</select>
		</td> 
	</tr>
	</table>
        </div>
    </div>
    </div>
    <div class="col-md-3">
    <div class="panel panel-default" >
        
<?
	if(!empty($_1_u_controle_idcontrole)){

		$res5=ControleController::buscarMediasControle($_1_u_controle_idcontrole);
		$qtd5=count($res5);
		foreach($res5 as $k => $row5){
			$soma5=$soma5+$row5['media'];
		}
		$media5=$soma5/$qtd5;
		
		$minimod=$media5-((20*$media5)/100);
		$maximod=$media5+((20*$media5)/100);
?>		
            <div class="panel-heading ">Referências</div>
            <div class="panel-body">		
                <table>
                        <tr>
                                <td nowrap>Média dos Títulos:</td><td><font color="red"><?=number_format($media5,2,'.','');?></font></td>				
                        </tr>
                        <tr>
                                <td nowrap>Título Mínimo Permitido:</td><td><font color="red"><?=number_format($minimod,2,'.','');?></font></td>					
                        </tr>
                        <tr>
                                <td nowrap>Título Máximo Permitido:</td><td><font color="red"><?=number_format($maximod,2,'.','');?></font></td>	
                        </tr>
                </table>
            </div>
            
<?
	}
?>	
		
    </div>
    </div>
</div>    
<?
if(!empty($_1_u_controle_idcontrole)){
?>
<div class='row'> 	
    <div class="col-md-12">
    <div class="panel panel-default" >
        <div class="panel-heading ">Validação</div>
        <div class="panel-body">

<?
		$res0=ControleController::buscarControles($_1_u_controle_idcontrole,1);
		$qtd0=count($res0);
		if($qtd0>0){
		?>
		
		<?	
			$i=1;
			foreach($res0 as $k => $row0){
				$i=$i+1;
		?>		
                    <div class="col-md-3">
                        <div class="panel panel-default" >
                        <div class="panel-body">
			<table class="normal">
				<tr class="header">
					<td nowrap>Data</td>
					<td nowrap>
						<input name="_<?=$i?>_u_controleteste_idcontroleteste"	type="hidden"	value="<?=$row0["idcontroleteste"]?>"	readonly='readonly'	>
						<input <?=$readonly?> name="_<?=$i?>_u_controleteste_data" class="calendario" size="10" type="text" <?if($_1_u_controle_status!='VALIDADO'){?>class="classdt" <?}?>value="<?=$row0["dmadata"]?>" vnulo>
				<?$res1=ControleController::buscarTitulosPorControles($row0["idcontroleteste"]);
				$qtd1=count($res1);
				if($qtd1==0){?>
					<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="removevalidacao(<?=$row0["idcontroleteste"]?>)" title="Excluir"></i>
				<?}?>
					</td>					
				</tr>
				<?				
				$soma=0;
				foreach($res1 as $k1 => $row1){
					$i=$i+1;
					$soma=$soma+$row1["titulo"];
				?>
					<tr class="header">
					<td nowrap>Título Obtido</td>
					<td nowrap>
						<input name="_<?=$i?>_u_controletitulo_idcontroletitulo"	type="hidden"	value="<?=$row1["idcontroletitulo"]?>"	readonly='readonly'	>
						<input <?=$readonly?> name="_<?=$i?>_u_controletitulo_titulo"	size="6" type="text" value="<?=$row1["titulo"]?>" vnulo>
						<?if($_1_u_controle_status!='VALIDADO'){?>
							<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="removeteste(<?=$row1["idcontroletitulo"]?>)" title="Excluir"></i>
						<?}?>
					</td>					
				</tr>
				<?
				}
				?>
				<tr class="respreto">
					<td >Média</td>
					<td><font color="red">
					<?
						$media=$soma/$qtd1;		
						echo(number_format($media,2,'.',''));	
					?>
						</font>
					</td>
				</tr>
				<?if($_1_u_controle_status!='VALIDADO'){?>
				<tr class="respreto">
					<td colspan="2">
						<i class="fa fa-plus-circle  pull-right fa-lg cinza hoververde pointer" onclick="novoteste(<?=$row0["idcontroleteste"]?>)" title="Inserir novo titulo">
							&nbsp;&nbsp;Novo Título
						</i>
					</td>
				</tr>
				<?}?>
			</table>
                        </div>
                        </div>
                    </div>
		
		<?
			}//while($row0=mysqli_fetch_assoc($res0))
		}//if($qtd0>0)
		?>

		<?if($_1_u_controle_status!='VALIDADO'){?>
                <div class="col-md-3">
                    <div class="panel panel-default" >
                    <div class="panel-body">
                    <table>
                    <tr>
			<td>
                            <i class="fa fa-plus-circle  pull-right fa-lg cinza hoververde pointer" onclick="novavalidacao(<?=$_1_u_controle_idcontrole?>,1)" title="Inserir Nova Validação">
                                &nbsp;&nbsp;;Nova Validação
                            </i>
                        </td>	 
                    </tr>
                    </table>
                    </div>
                    </div>
                </div>
		<?}?>

        </div>
    </div>
    </div>
</div>

<?
	if($_1_u_controle_status=='VALIDADO'){
?>
<div class='row'> 	
    <div class="col-md-12">
		<div class="panel panel-default" >
			<div class="panel-heading ">Histórico</div>
			<div class="panel-body">
			
			<?
			$res2=ControleController::buscarControles($_1_u_controle_idcontrole,2);
			$qtd2=count($res2);
			if($qtd2>0){
				$tr=0;	
				foreach($res2 as $k2 => $row2){
					$i=$i+1;?>		
						<div class="col-md-3">
							<div class="panel panel-default" >
							<div class="panel-body">
								<table class="normal">
									<tr class="header">
										<td nowrap>Data</td>
										<td nowrap>
											<input name="_<?=$i?>_u_controleteste_idcontroleteste"	type="hidden"	value="<?=$row2["idcontroleteste"]?>"	readonly='readonly'	>
											<input name="_<?=$i?>_u_controleteste_data" class="calendario"	size="10" type="text" class="classdt" value="<?=$row2["dmadata"]?>" vnulo>
											<?$res3=ControleController::buscarTitulosPorControles($row2["idcontroleteste"]);
											$qtd3=count($res3);
											if($qtd3==0){?>					
												<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="removevalidacao(<?=$row2["idcontroleteste"]?>)" title="Excluir"></i>
											<?}?>					
										</td>					
									</tr>
									<?$soma=0;
									foreach($res3 as $k3 => $row3){
										$i=$i+1;
										$soma=$soma+$row3["titulo"];
										if($row3["titulo"]>$maximod or $row3["titulo"] < $minimod){					
											$cortd = "bgcolor='#FF6347'";
										}else{
											$cortd = " ";
										}
									?>
										<tr class="header" <?=$cortd?>>
										<td nowrap>Título Obtido</td>
										<td nowrap>
											<input name="_<?=$i?>_u_controletitulo_idcontroletitulo"	type="hidden"	value="<?=$row3["idcontroletitulo"]?>"	readonly='readonly'	>
											<input name="_<?=$i?>_u_controletitulo_titulo"	size="6" type="text" value="<?=$row3["titulo"]?>" vnulo>
																	<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="removeteste(<?=$row3["idcontroletitulo"]?>)" title="Excluir"></i>
										</td>					
									</tr>
									<?}
									$media=$soma/$qtd3;
									if($media>$maximod or $media < $minimod){
										$cortd = "bgcolor='#FF6347'";
									}else{
										$cortd = " ";
									}?>
									<tr class="respreto" <?=$cortd?>>
										<td >Média</td>
										<td><?echo(number_format($media,2,'.',''));?></td>
									</tr>
									<tr class="respreto">
										<td colspan="2">
											<i class="fa fa-plus-circle  pull-right fa-lg cinza hoververde pointer" onclick="novoteste(<?=$row2["idcontroleteste"]?>)" title="Inserir Novo Título">
											&nbsp;&nbsp;Novo Título
											</i>
										</td>
									</tr>
								</table>
							</div>
							</div>
						</div>
			
			
			<?
				}//while($row2=mysqli_fetch_assoc($res2))

			}//if($qtd2>0)
			?>
			<div class="col-md-3">
				<div class="panel panel-default" >
					<div class="panel-body">
					<table>
						<tr>
							<td>
								<i class="fa fa-plus-circle  pull-right fa-lg cinza hoververde pointer" onclick="novavalidacao(<?=$_1_u_controle_idcontrole?>,2)" title="Inserir Novo Histà³rico">
									&nbsp;&nbsp;Novo Histórico
								</i>
							</td>	 
						</tr>
			</table>
					</div>
				</div>
			</div>
		</div>
		</div>
    </div>
</div>
<?
	}//if($_1_u_controle_status=='VALIDADO'){
}
require_once(__DIR__."/js/controle_js.php");
?>

<?
if(!empty($_1_u_controle_idcontrole)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_controle_idcontrole; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "controle"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
