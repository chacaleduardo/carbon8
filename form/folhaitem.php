<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$idfolhaitem=$_GET['idfolhaitem'];

if(!empty($idfolhaitem) and empty($_GET["idfolha"])){
	$sqli="select * from folhaitem where idfolhaitem=".$idfolhaitem;
	$resi=d::b()->query($sqli) or die("Erro ao buscar folha sql=".$sqli);
	$rowi=mysqli_fetch_assoc($resi);
	//die($sqli);
	$_GET["idfolha"]=$rowi['idfolha'];
	
	$stritem =" and i.idfolhaitem =".$idfolhaitem." ";
}

if($_POST){
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "folha";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idfolha" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from folha where idfolha = '".$_GET["idfolha"]."' ";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")

?>

<style type="text/css">

.divverde{
	width:16px;
	height:16px;
	background-image: url(../img/accept.png);
	background-repeat: no-repeat;
	cursor:pointer;
	cursor:hand;
	float:left;
}
.divvermelho{
	width:16px;
	height:16px;
	background-image: url(../img/rejected.png);
	background-repeat: no-repeat;
	cursor:pointer;
	cursor:hand;
	float:left;
}


</style>


<?
 $sql="select p.nome,i.* from folhaitem i,pessoa p where tipo!='F' and p.idpessoa = i.idpessoa and i.idfolha=".$_1_u_folha_idfolha." ".$stritem." order by p.nome";

 $res=d::b()->query($sql) or die("Erro ao buscar itens da folha sql=".$sql);
 $i=0;
 while($row=mysqli_fetch_assoc($res)){
	//CALCULOS
    $i=$i+1;
	
	$soma=(($row['salario']+$row['insalubridade']+$row['horaextra'] + $row['ferias'] + $row['ferias13']+$row['trocomes']+$row['adicnot']+$row['differias']+$row['trferias']+$row['estouromes']+$row['dsr']+$row['dsr_hr_extra']+$row['hr_auxilio_doenca']+$row['insal_s_sal_min_aux_doenca']+$row['med_hrs_ext_aux_doenca']+$row['13_abono_pec']+$row['insal_s_sal_min_13_sal_adto']+$row['med_hrs_ext_s_ferias']+$row['hrs_abono_pec_diurna']+$row['med_hrs_ext_abono_pec_diurno']+$row['insal_s_sal_min_abono_pec'])-$row['desc_adiant_ferias']-$row['trocomesant']-$row['irrf']-$row['irferias']-$row['vale']-$row['valetransp']-$row['inss']-$row['farmacia']-$row['unimedmens']-$row['unimed']-$row['alimentacao']-$row['emprestimo']-$row['outros']-$row['insssferias']-$row['adiantferias']-$row['adiantdt']-$row['estouromesant']-$row['contsindical']-$row['habit']);

?>
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Holerite</div>
        <div class="panel-body">

<table>
	<tr>
		<td>
		<table >
		<tr> 
			<td></td> 
			<td><input name="_<?=$i?>_u_folhaitem_idfolhaitem" type="hidden"	value="<?=$row['idfolhaitem']?>" readonly='readonly'	></td> 
		</tr>
		<tr> 
			<td></td> 
			<td><input name="_<?=$i?>_u_folhaitem_idfolha"	type="hidden"		value="<?=$row['idfolha']?>"></td> 
		</tr>
		<tr> 
			<td align="right">Funcionário:</td> 
			<td colspan="7"><input	name="_<?=$i?>_u_folhaitem_idpessoa"	type="hidden"	value="<?=$row['idpessoa']?>">
			<?=traduzid("pessoa","idpessoa","nome",$row['idpessoa'])?>
			</td> 
			<td>Ferias?</td>			
			<td align="center"  nowrap>
			
			
				<?
				$sqlf="select p.nome,i.* from folhaitem i,pessoa p where p.idpessoa = i.idpessoa and i.tipo='F' and i.idfolha=".$_1_u_folha_idfolha." and i.idpessoa=".$row['idpessoa']." order by p.nome";
				
				$resf=d::b()->query($sqlf) or die("Erro ao buscar itens da folha de ferias sql=".$sqlf);
				$qtdf=mysql_num_rows($resf);
				if($qtdf<1){?>
                                    <i class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="CB.post({objetos:'_ajax_i_folhaitem_idpessoa=<?=$row["idpessoa"]?>&_ajax_i_folhaitem_tipo=F&_ajax_i_folhaitem_idfolha=<?=$_1_u_folha_idfolha?>',parcial:true})" title="Retirar da folha"></i>
				<?}else{
					$rowf=mysqli_fetch_assoc($resf);
				?>
                                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="CB.post({objetos:'_ajax_d_folhaitem_idfolhaitem=<?=$rowf['idfolhaitem']?>'})" title="Retirar da folha"></i>
				<?}?>
				
				
				
			</td>
		</tr>
		<tr>
			<td align="right">Dias tr.</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_dia"	 size="5" type="text"	value="<?=$row['dia']?>"></td> 
			<td align="right">insal. S. Sal. min aux. doenca:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_insal_s_sal_min_aux_doenca" size="5"	type="text"	value="<?=$row['insal_s_sal_min_aux_doenca']?>"></td> 
			<td align="right">13 Abono Pec.:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_13_abono_pec" size="5"	type="text"		value="<?=$row['13_abono_pec']?>"></td> 			
		</tr>
		<tr>
			<td align="right">Salário</td>  
			<td><input	name="_<?=$i?>_u_folhaitem_salario"	 size="5" type="text" readonly='readonly'	value="<?=$row['salario']?>"></td> 
			<td align="right">Med. ext. aux. doença:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_med_hrs_ext_aux_doenca" size="5"	type="text"		value="<?=$row['med_hrs_ext_aux_doenca']?>"></td> 
			<td align="right">Adic. Noturno:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_adicnot" size="5"	type="text"	value="<?=$row['adicnot']?>"></td> 					
		</tr>
		

		<tr> 
			<td align="right">Insalubridade:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_insalubridade" size="5"	type="text"		value="<?=$row['insalubridade']?>"></td> 
			<td align="right">Med. Hrs. Ext. S. Férias:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_med_hrs_ext_s_ferias" size="5"	type="text"	value="<?=$row['med_hrs_ext_s_ferias']?>"></td> 
			<td align="right">Estouro Màªs:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_estouromes" size="5"	type="text"	value="<?=$row['estouromes']?>"></td> 
		</tr>
		<tr> 
			<td align="right">Hora Extra:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_horaextra" size="5"	type="text"		value="<?=$row['horaextra']?>"></td> 
			<td align="right">Hrs. Abono Pec. Diurna:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_hrs_abono_pec_diurna" size="5"	type="text"		value="<?=$row['hrs_abono_pec_diurna']?>"></td>				
			<td align="right">Insal. S. Sal. Min. Abono Pec.:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_insal_s_sal_min_abono_pec" size="5"	type="text"	value="<?=$row['insal_s_sal_min_abono_pec']?>"></td> 			
			 	
		</tr>	
		<tr> 
			<td align="right">Férias 13</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_ferias13" size="5"	type="text"	value="<?=$row['ferias13']?>"></td> 
			<td align="right">Tr. Férias:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_trferias" size="5"	type="text"	value="<?=$row['trferias']?>"></td>		
			<td align="right">Dif. Férias:</td> 
			<td><input name="_<?=$i?>_u_folhaitem_differias" size="5" type="text"	value="<?=$row['differias']?>"></td>
			
			
		</tr>

		<tr> 
			<td align="right">Med. Hrs. Ext. Abono Pec. Diurno:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_med_hrs_ext_abono_pec_diurno" size="5"	type="text"	value="<?=$row['med_hrs_ext_abono_pec_diurno']?>"	></td> 	 
			<td align="right">Insal. S. Sal min. 13 sal adto:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_insal_s_sal_min_13_sal_adto" size="5"	type="text"	value="<?=$row['insal_s_sal_min_13_sal_adto']?>"	></td> 
			<td align="right">HR. aux. Doença:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_hr_auxilio_doenca" size="5"	type="text"	value="<?=$row['hr_auxilio_doenca']?>"	></td> 
			
		</tr>
		
		
		
		<tr> 
			<td align="right">DSR:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_dsr" size="5"	type="text"	value="<?=$row['dsr']?>"></td> 
			<td align="right">DSR HR. Extra:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_dsr_hr_extra" size="5"	type="text"		value="<?=$row['dsr_hr_extra']?>"></td> 			
			
		</tr>
		<tr>
			<td colspan="6"><hr></td>
		</tr>
	
		<tr>
			<td align="right">Desc. Adiant. Ferias:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_desc_adiant_ferias" size="5"	type="text"	value="<?=$row['desc_adiant_ferias']?>"	></td> 			
			<td align="right">Unimed Màªs:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_unimedmens" size="5"	type="text"	value="<?=$row['unimedmens']?>"	></td> 
			<td align="right">Emprestimo:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_emprestimo" size="5"	type="text"	value="<?=$row['emprestimo']?>"></td> 
		</tr>
		<tr>
			<td align="right">Vale Transp.:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_valetransp" size="5" type="text"	value="<?=$row['valetransp']?>"></td> 
			<td align="right">IRRF</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_irrf" size="5"	type="text"	value="<?=$row['irrf']?>"></td> 			
			<td align="right">Alimentação</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_alimentacao" size="5"	type="text"		value="<?=$row['alimentacao']?>"></td>		
		</tr>
		<tr>
			<td align="right">Desc. Diversos:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_outros" type="text" size="5"	value="<?=$row['outros']?>"></td> 
			<td align="right">Est. Màªs Ant.</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_estouromesant" size="5"	type="text"	value="<?=$row['estouromesant']?>"></td> 
			<td align="right">Adiant. 13</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_adiantdt" size="5"	type="text"-	value="<?=$row['adiantdt']?>"></td>
		</tr>
		<tr>
			<td align="right">Adiant. Férias:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_adiantferias" size="5"	type="text"	value="<?=$row['adiantferias']?>"></td> 	
			<td align="right">Unimed:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_unimed" size="5"	type="text"	value="<?=$row['unimed']?>"></td> 	
			<td align="right">Cont. Sindical:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_contsindical" size="5"	type="text"	value="<?=$row['contsindical']?>"></td> 
		</tr>
		<tr>	
			<td align="right">INSS:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_inss" size="5"	type="text"	value="<?=$row['inss']?>"></td> 
			<td align="right">Vale:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_vale" size="5"	type="text"	value="<?=$row['vale']?>"></td>
			<td align="right">FGTS:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_fgts" size="5"	type="text"	value="<?=$row['fgts']?>"></td> 
		</tr>
		<tr> 
			<td align="right">Habitação:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_habit" size="5"	type="text"	value="<?=$row['habit']?>"></td> 
			<td align="right">Farmácia:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_farmacia" size="5"	type="text"		value="<?=$row['farmacia']?>"></td> 
			<td align="right">FGTS S. 13 Sal.:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_fgts_s_13_sal" size="5"	type="text"		value="<?=$row['fgts_s_13_sal']?>"></td> 
		</tr>
		
		<tr> 
			<td align="right">Obs.:</td> 	
			<td colspan="4">
			<textarea <?=$readonly?> class="caixa"  style="width: 350px; height: 30px; font-size:medium;"  name="_<?=$i?>_u_folhaitem_obs"><?=$row['obs']?></textarea>
			</td>
		</tr>
		<tr> 
			<td align="right">Obs. Interna:</td> 
			<td colspan="4">
				<textarea <?=$readonly?> class="caixa"  style="width: 350px; height: 30px; font-size:medium;"  name="_<?=$i?>_u_folhaitem_obsint" ><?=$row['obsint']?></textarea>
			</td>
			
		</tr>
		<tr>
			<td align="right">Soma:</td> 
			<td align="left"><?=number_format($soma, 2, ',', '.');?></td>
		</tr>
		</table>
		</td>
                <td style="vertical-align: top;">
<?
$sql1="select p.nome,i.* from folhaitem i,pessoa p where p.idpessoa = i.idpessoa and i.tipo='F' and i.idfolha=".$_1_u_folha_idfolha." and i.idpessoa=".$row['idpessoa']." order by p.nome";

$res1=d::b()->query($sql1) or die("Erro ao buscar itens da folha de ferias sql=".$sql1);
while($row1=mysqli_fetch_assoc($res1)){
    //CALCULOS
    $i=$i+1;
    $soma=(($row1['salario']+$row1['insalubridade']+$row1['horaextra'] + $row1['ferias'] + $row1['ferias13']+$row1['trocomes']+$row1['adicnot']+$row1['differias']+$row1['trferias']+$row1['estouromes'])-$row1['trocomesant']-$row1['irrf']-$row1['irferias']-$row1['vale']-$row1['valetransp']-$row1['inss']-$row1['farmacia']-$row1['unimedmens']-$row1['unimed']-$row1['alimentacao']-$row1['emprestimo']-$row1['outros']-$row1['insssferias']-$row1['adiantferias']-$row1['adiantdt']-$row1['estouromesant']-$row1['contsindical']-$row1['habit']);

?>
		
		<table style="background-color: #00FF7F; display: inline; vertical-align: top;">
		<tr> 
			<td></td> 
			<td><input name="_<?=$i?>_u_folhaitem_idfolhaitem" type="hidden"	value="<?=$row1['idfolhaitem']?>" readonly='readonly'	></td> 
		</tr>
		<tr> 
			<td></td> 
			<td><input name="_<?=$i?>_u_folhaitem_idfolha"	type="hidden"		value="<?=$row1['idfolha']?>"></td> 
		</tr>
		<tr> 
			<td colspan="4" align="center">FàRIAS<input	name="_<?=$i?>_u_folhaitem_idpessoa"	type="hidden"	value="<?=$row1['idpessoa']?>">			
			</td> 
		</tr>
		<tr>
			<td align="right">Salário Férias</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_salario" size="5"	type="text"	value="<?=$row1['salario']?>"></td> 
			<td align="right">Alimentação</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_alimentacao" size="5"	type="text"		value="<?=$row1['alimentacao']?>"></td> 
		</tr>
		<tr> 
			<td align="right">Insalubridade:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_insalubridade" size="5"	type="text"		value="<?=$row1['insalubridade']?>"></td> 
			<td align="right">INSS Férias:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_inss" size="5"	type="text"	value="<?=$row1['inss']?>"></td> 
				
		</tr>
		<tr> 
			<td align="right">Hora Extra:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_horaextra" size="5"	type="text"		value="<?=$row1['horaextra']?>"></td>
			<td align="right">IRRF Férias:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_irrf" size="5"	type="text"	value="<?=$row1['irrf']?>"></td> 
			
		</tr>
		<tr> 
			<td align="right">Estouro Màªs:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_estouromes" size="5"	type="text"	value="<?=$row1['estouromes']?>"></td> 
			<td align="right">Emprestimo:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_emprestimo" size="5"	type="text"	value="<?=$row1['emprestimo']?>"></td>  
						
			
		</tr>
		<tr> 
			<td></td> 
			<td></td> 
			<td align="right">Unimed:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_unimed" size="5"	type="text"	value="<?=$row1['unimed']?>"></td> 
			
		</tr>
		
		<tr> 
			<td align="right">Adic. Noturno:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_adicnot" size="5"	type="text"	value="<?=$row1['adicnot']?>"></td> 
			<td align="right">Adiant. Férias:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_adiantferias" size="5"	type="text"	value="<?=$row1['adiantferias']?>"></td> 	
		</tr>

		<tr> 
			<td align="right">Férias 13</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_ferias13"	size="5" type="text"	value="<?=$row1['ferias13']?>"></td> 
			<td align="right">Adiant. 13</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_adiantdt" size="5"	type="text"-	value="<?=$row1['adiantdt']?>"></td>
			
		</tr>

		<tr> 
			<td align="right">Tr. Férias:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_trferias"	 size="5" type="text"	value="<?=$row1['trferias']?>"></td>			 
			<td align="right">Est. Màªs Ant.</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_estouromesant" size="5"	type="text"	value="<?=$row1['estouromesant']?>"></td> 
			
		</tr>
		<tr> 
			<td align="right">Dif. Férias:</td> 
			<td><input name="_<?=$i?>_u_folhaitem_differias" size="5" type="text"	value="<?=$row1['differias']?>"></td> 
			<td align="right">Farmácia:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_farmacia" size="5"	type="text"		value="<?=$row1['farmacia']?>"></td> 			
		</tr>

		
		<tr> 
			<td align="right">Desc. Diversos:</td> 
			<td><input	name="_<?=$i?>_u_folhaitem_outros" size="5" type="text"	value="<?=$row1['outros']?>"></td> 
			<td align="right">Soma:</td> 
			<td align="center"><?=number_format($soma, 2, ',', '.');?></td>
		</tr>
		</table>
		
	
<?}?>
                    </td>
</tr>
</table>

        </div>
    </div>
    </div>
<?
}
?>
