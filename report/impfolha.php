<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}
$az=$_GET['az'];
/*
 * $pagvaltabela: tabela principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os par?etros GET que devem ser validados para compor o select principal
 *                pk: indica par?etro chave para o select inicial
 *                vnulo: indica par?etros secund?ios que devem somente ser validados se nulo ou n?
 */
$pagvaltabela = "folha";
$pagvalcampos = array(
	"idfolha" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as vari?eis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from folha where idfolha = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das vari?eis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")

?>
<html>
<head>
<title>Folha de Pagamento</title>

<link href="../inc/css/mtorep.css" media="all" rel="stylesheet" type="text/css" />
<style>
.rotulo{
font-weight: bold;
font-size: 10px;
}
.texto{
font-size: 12px;
}
.textoitem{
font-size: 10px;
}
</style>
</head>			
<body>

<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>Folha de Pagamento</legend>
</fieldset>
<table class="normal">
<tr class="res" > 
	<td align="right">ID:</td> 
	<td><?=$_1_u_folha_idfolha?>
	</td> 
	<td align="right">T?ulo:</td> 
	<td><?=$_1_u_folha_titulo?></td> 
	<td align="right">Data:</td> 
	<td><?=$_1_u_folha_data?></td> 
	<td align="right">Status:</td> 
	<td><?=$_1_u_folha_status?></td> 
</tr>
</table>
<?
if(!empty($_1_u_folha_idfolha)){

	$linha2=9;
	$linha1=7;
	$clausula=" ";
	
	$sqlcamp="select
					`salario`,
					`insalubridade`,
					`horaextra`,
					`inss`,
					`ferias`,
					`ferias13`,
					`trocomes`,
					`trocomesant`,
					`adicnot`,
					`irrf`,
					`irferias`,
					`vale`,
					`valetransp`,
					`differias`,
					`trferias`,
					`estouromes`,
					`insssferias`,
					`adiantferias`,
					`adiantdt`,
					`estouromesant`,
					`contsindical`,
					`habit`,
					`obs`,
					`obsint`,
					`farmacia`,
					`unimedmens`,
					`unimed`,
					`alimentacao`,
					`emprestimo`,
					`outros`,
					`imprime`,
					`tipo`
			from folhaitem
			where  idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
			and idfolha=".$_1_u_folha_idfolha;
	$rescamp=d::b()->query($sqlcamp) or die("Erro ao buscar itens da folha sql=".$sqlcamp);
	while($rowcamp = mysqli_fetch_assoc($rescamp)){
	
		if(!empty($rowcamp['differias'])){
			$camp_differias="Y";
		}
		if(!empty($rowcamp['trferias'])){
			$camp_trferias="Y";
		}
		if(!empty($rowcamp['trocomes'])){
			$camp_trocomes="Y";
		}
		if(!empty($rowcamp['trocomesant'])){
			$camp_trocomesant="Y";
		}
		if(!empty($rowcamp['estouromes'])){
			$camp_estouromes="Y";
		}
		if(!empty($rowcamp['adiantferias'])){
			$camp_adiantferias="Y";
		}
		if(!empty($rowcamp['adiantdt'])){
			$camp_adiantdt="Y";
		}
		if(!empty($rowcamp['estouromesant'])){
			$camp_estouromesant="Y";
		}
		if(!empty($rowcamp['contsindical'])){
			$camp_contsindical="Y";
		}
	
	}
		

?>

<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>Lista de Pagamento</legend>
</fieldset>
	<table class="normal">
		<tr class="header">
			<td>Nome</td>
			<td align="center">Sal?io</td>
			<td align="center">Insalubridade</td>
			<td align="center">Hora<br> Extra</td>
			<td align="center">Adic.<br>Notur.</td>
			<?if($_1_u_folha_ferias=="Y"){//desativado
				$linha1=$linha1+1;
				$clausula.=",i.ferias ";
			?>
			<td align="center">F?ias</td>
			<?}?>
			<?//if($_1_u_folha_ferias13=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.ferias13 ";
			?>
			<td align="center">1/3<br>F?ias</td>
			<?//}?>
			<?if($camp_differias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.differias ";
			?>
			<td align="center">Dif.<br>F?ias</td> 
			<?}?>
			<?if($camp_trferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.trferias ";
			?>
			<td align="center">Tr.<br>F?ias.</td> 
			<?}?>
			<?if($camp_estouromes=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.estouromes ";
			?>
			<td align="center">Estouro<br>M?</td>		
			<?}?>
			<?if($camp_trocomes=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.trocomes ";
			?>		
			<td align="center">Tr.<br>M?</td>
			<?}?>
			<?if($camp_trocomesant=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.trocomesant ";
			?>	
			<td align="center">T.M.<br>Ant.</td>
			<?}?>
			<td align="center">INSS</td>
			<?if($_1_u_folha_insssferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.insssferias ";
			?>
			<td align="center">INSS S/<br>F?ias</td>
			<?}?>
			<td align="center">IRRF</td>
			<?if($_1_u_folha_irferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.irferias ";
			?>
			<td align="center">IRRF<br>S/F?ias</td>
			<?}?>	 	 
			<?if($camp_adiantferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.adiantferias ";
			?>
			<td align="center">Adiant.<br>F?ias</td>
			<?}?>
			<?if($camp_adiantdt=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.adiantdt ";
			?>
			<td align="center">Adiant.<br> 13?</td>
			<?}?>
			<?if($camp_estouromesant=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.estouromesant ";
			?>
			<td align="center">Estouro<br>M? Ant.</td>
			<?}?>
			<td align="center">Vale<br>(30%)</td>
			<td align="center">VT</td>
			<td align="center">Odonto</td>
			<td align="center">Unimed <br> Mens.</td>
			<td align="center">Unimed</td>
			<td align="center">Aliment.</td>
			<td align="center">Empr?t.</td>
			<?if($camp_contsindical=="Y"){
				$linha2=$linha2+1;
				$clausula.=",i.contsindical ";
			?>
			<td align="center">Contr.<br>Sindical</td> 
			<?}?>
			<td align="center">Habit.</td>
			<td align="center">Desc.<br>Diversos</td>
			<td align="center">Obs.</td>
			<td align="center">Soma</td>			
		</tr>
<?
	if($az=='Y'){
		$campos=" ";
		$order=" ";
	}else{
		$campos=" CASE p.tipopagamento
							WHEN 'TRANSFERENCIA' THEN 'TRANSFER?CIA'
							WHEN 'CONVENIO' THEN 'CONV?IO'
							WHEN 'CHEQUE' THEN 'CHEQUE'
						ELSE 'OUTROS'
						END AS tipopagamento
						, ";
		$order=" tipopagamento desc, ";
	}
	$sqli="select ".$campos." p.nomecurto,p.nome,
				i.idfolhaitem,
					i.idfolha,
					i.idpessoa,
					i.salario,
					i.insalubridade,
					i.horaextra,
					i.inss,
					i.trocomes,
					i.trocomesant,
					i.adicnot,
					i.irrf,
					i.vale,
					i.valetransp,
					i.habit,
					i.obs,
					i.obsint,
					i.farmacia,
					i.unimedmens,
					i.unimed,
					i.alimentacao,
					i.emprestimo,
					i.outros,
					i.tipo,
					i.imprime".$clausula."
				from pessoa p,folhaitem i
				where p.idpessoa = i.idpessoa
				and i.imprime = 'Y'
				and i.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and i.idfolha=".$_1_u_folha_idfolha." order by ".$order." p.nomecurto";
	$resi=d::b()->query($sqli) or die("Erro ao buscar items da folha sql=".$sqli);
	$i=1;
	$n=0;
	while($rowi=mysqli_fetch_assoc($resi)){
		$i=$i+1;
		if($rowi['tipo']!='F'){
			$n=$n+1;
		}
		//controla a linha do tipopagamento
		if($az!='Y'){
			if(empty($tipopagamento)){
				$tipopagamento=$rowi['tipopagamento'];
			}elseif($tipopagamento!=$rowi['tipopagamento']){					
?>		
		<tr class="header">
			<td colspan="<?=$linha1?>"></td>
			<td><?=number_format($svale, 2, ',', '.');?></td>
			<td colspan="<?=$linha2?>" align="right"><?=$tipopagamento?></td>
			<td><?=number_format($tipopgsoma, 2, ',', '.');?></td>			
		</tr>
	<?			$tipopgsoma=0;
				$svale=0;
				$tipopagamento=$rowi['tipopagamento'];
			}
		}	
		//troca a cor da linha
		if(!empty($bgcolor)){
			$bgcolor = "";
		}else{
			$bgcolor = "#EEE9E9";
		}
		//CALCULOS
		$soma=(($rowi['salario']+$rowi['insalubridade']+$rowi['horaextra'] + $rowi['ferias'] + $rowi['ferias13']+$rowi['trocomes']+$rowi['adicnot']+$rowi['differias']+$rowi['trferias']+$rowi['estouromes'])-$rowi['trocomesant']-$rowi['irrf']-$rowi['irferias']-$rowi['vale']-$rowi['valetransp']-$rowi['inss']-$rowi['farmacia']-$rowi['unimedmens']-$rowi['unimed']-$rowi['alimentacao']-$rowi['emprestimo']-$rowi['outros']-$rowi['insssferias']-$rowi['adiantferias']-$rowi['adiantdt']-$rowi['estouromesant']-$rowi['contsindical']-$rowi['habit']);
		$vsoma = $vsoma+ $soma;//Soma total
		$tipopgsoma=$tipopgsoma+$soma;//soma total por tipo de pagamento
	
		
		if($rowi['tipo']!='F'){
			$salario=$salario+$rowi['salario'];
			$inss=$inss+$rowi['inss'];
			$irrf=$irrf+$rowi['irrf'];
		}else{
			$salariof=$salariof+$rowi['salario'];
			$inssf=$inssf+$rowi['inss'];
			$irrff=$irrff+$rowi['irrf'];
		}
		$insalubridade=$insalubridade+$rowi['insalubridade'];
		$he=$he+$rowi['horaextra'];
		$ferias=$ferias+$rowi['ferias'];
		$ferias13=$ferias13+$rowi['ferias13'];
		$trocomes=$trocomes+$rowi['trocomes'];
		$adicnot=$adicnot+$rowi['adicnot'];
		$trocomesant=$trocomesant+$rowi['trocomesant'];
		
		$irferias=$irferias+$rowi['irferias'];
		$vale=$vale+$rowi['vale'];
		$svale=$svale+$rowi['vale'];
		$valetransp=$valetransp+$rowi['valetransp'];
		
		$farmacia=$farmacia+$rowi['farmacia'];
		$unimedmens=$unimedmens+$rowi['unimedmens'];
		$unimed=$unimed+$rowi['unimed'];
		$alimentacao=$alimentacao+$rowi['alimentacao'];
		$emprestimo=$emprestimo+$rowi['emprestimo'];
		$outros=$outros+$rowi['outros'];
		
		$differias=$differias+$rowi['differias'];
		$trferias=$trferias+$rowi['trferias'];
		$estouromes=$estouromes+$rowi['estouromes'];
		$insssferias=$insssferias+$rowi['insssferias'];
		$adiantferias=$adiantferias+$rowi['adiantferias'];
		$adiantdt=$adiantdt+$rowi['adiantdt'];
		$estouromesant=$estouromesant+$rowi['estouromesant'];
		$contsindical=$contsindical+$rowi['contsindical'];
		$habit=$habit+$rowi['habit'];
		
?>		
		<tr class="res"  bgcolor="<?=$bgcolor?>">
			<td>
			<?if($rowi['tipo']=='F'){?>
			<font color="red">F?ias</font>
			
			<?}else{?>
				<?=$n?>-<?=$rowi['nomecurto']?>	
			<?}?>
			
			</td>
			<td align="center"><?=number_format($rowi['salario'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['insalubridade'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['horaextra'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['adicnot'], 2, ',', '.');?></td>	
			<?if($_1_u_folha_ferias=="Y"){?>		
			<td align="center"><?=number_format($rowi['ferias'], 2, ',', '.');?></td>
			<?}?>
			<?//if($_1_u_folha_ferias13=="Y"){?>
			<td align="center"><?=number_format($rowi['ferias13'], 2, ',', '.');?></td>
			<?//}?>
			<?if($camp_differias=="Y"){?>		
			<td align="center"><?=number_format($rowi['differias'], 2, ',', '.');?></td>
			<?}?>
			<?if($camp_trferias=="Y"){?>
			<td align="center"><?=number_format($rowi['trferias'], 2, ',', '.');?></td>	
			<?}?>
			<?if($camp_estouromes=="Y"){?>
			<td align="center"><?=number_format($rowi['estouromes'], 2, ',', '.');?></td>
			<?}?>	
			<?if($camp_trocomes=="Y"){?>
			<td align="center"><?=number_format($rowi['trocomes'], 2, ',', '.');?></td>
			<?}?>
			<?if($camp_trocomesant=="Y"){?>
			<td align="center"><?=number_format($rowi['trocomesant'], 2, ',', '.');?></td>	
			<?}?>		
			<td align="center"><?=number_format($rowi['inss'], 2, ',', '.');?></td>
			<?if($_1_u_folha_insssferias=="Y"){?>
			<td align="center"><?=number_format($rowi['insssferias'], 2, ',', '.');?></td>
			<?}?>
			<td align="center"><?=number_format($rowi['irrf'], 2, ',', '.');?></td>
			<?if($_1_u_folha_irferias=="Y"){?>
			<td align="center"><?=number_format($rowi['irferias'], 2, ',', '.');?></td>
			<?}?>
			<?if($camp_adiantferias=="Y"){?>
			<td align="center"><?=number_format($rowi['adiantferias'], 2, ',', '.');?></td>
			<?}?>
			<?if($camp_adiantdt=="Y"){?>
			<td align="center"><?=number_format($rowi['adiantdt'], 2, ',', '.');?></td>
			<?}?>
			<?if($camp_estouromesant=="Y"){?>
			<td align="center"><?=number_format($rowi['estouromesant'], 2, ',', '.');?></td>
			<?}?>
			<td align="center"><?=number_format($rowi['vale'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['valetransp'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['farmacia'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['unimedmens'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['unimed'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['alimentacao'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['emprestimo'], 2, ',', '.');?></td>
			<?if($camp_contsindical=="Y"){?>
			<td align="center"><?=number_format($rowi['contsindical'], 2, ',', '.');?></td>
			<?}?>
			<td align="center"><?=number_format($rowi['habit'], 2, ',', '.');?></td>
			<td align="center"><?=number_format($rowi['outros'], 2, ',', '.');?></td>
			<td align="center"><?=$rowi['obs']?></td>
			<td align="center"><?=number_format($soma, 2, ',', '.');?></td>			
		</tr>

<?
	}
?>

		<tr class="header">
			<td colspan="<?=$linha1?>"></td>
			<td><?=number_format($svale, 2, ',', '.');?></td>
			<td colspan="<?=$linha2?>" align="right"><?=$tipopagamento?></td>
			<td><?=number_format($tipopgsoma, 2, ',', '.');?></td>			
		</tr>
		<tr class="header">
			<td align="right"> TOTAL</td>
			<td align="center" title="SAL?RIO"><?=number_format($salario, 2, ',', '.');?></td>
			<td align="center" title="INSALUBRIDADE"><?=number_format($insalubridade, 2, ',', '.');?></td>
			<td align="center" title="HORA EXTRA"><?=number_format($he, 2, ',', '.');?></td>
			<td align="center" title="ADIC. NOTURNO"><?=number_format($adicnot, 2, ',', '.');?></td>
			<?if($_1_u_folha_ferias=="Y"){?>
			<td align="center" title="F?IAS"><?=number_format($ferias, 2, ',', '.');?></td>
			<?}?>
			<?if($_1_u_folha_ferias13=="Y"){?>
			<td align="center" title=" 1/3 F?IAS"><?=number_format($ferias13, 2, ',', '.');?></td>	
			<?}?>
			<?if($camp_differias=="Y"){?>
			<td align="center" title=" DIF. F?IAS"><?=number_format($differias, 2, ',', '.');?></td>
			<?}?>
			<?if($camp_trferias=="Y"){?>
			<td align="center" title=" TR. F?IAS"><?=number_format($trferias, 2, ',', '.');?></td>
			<?}?>
			<?if($camp_estouromes=="Y"){?>
			<td align="center" title="ESTOURO M?"><?=number_format($estouromes, 2, ',', '.');?></td>	
			<?}?>	
			<?if($camp_trocomes=="Y"){?>				
			<td align="center" title="TR. M?"><?=number_format($trocomes, 2, ',', '.');?></td>
			<?}?>
			<?if($camp_trocomesant=="Y"){?>
			<td align="center" title="T.M. ANT."><?=number_format($trocomesant, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="INSS"><?=number_format($inss, 2, ',', '.');?></td>
			<?if($_1_u_folha_insssferias=="Y"){?>
			<td align="center" title="INSS S/ F?IAS"><?=number_format($insssferias, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="IRRF"><?=number_format($irrf, 2, ',', '.');?></td>
			<?if($_1_u_folha_irferias=="Y"){?>
			<td align="center" title="IRRF S/F?IAS"><?=number_format($irferias, 2, ',', '.');?></td>
			<?}?>		
			<?if($camp_adiantferias=="Y"){?>
			<td align="center" title="ADIANT. F?IAS"><?=number_format($adiantferias, 2, ',', '.');?></td>
			<?}?>
			<?if($camp_adiantdt=="Y"){?>
			<td align="center" title="ADIANT. 13?"><?=number_format($adiantdt, 2, ',', '.');?></td>
			<?}?>
			<?if($camp_estouromesant=="Y"){?>
			<td align="center" title="ESTOURO M? ANT."><?=number_format($estouromesant, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="VALE"><?=number_format($vale, 2, ',', '.');?></td>
			<td align="center" title="VALE TRANSPORTE"><?=number_format($valetransp, 2, ',', '.');?></td>
			<td align="center" title="ODONTO"><?=number_format($farmacia, 2, ',', '.');?></td>
			<td align="center" title="UNIMED MENS"><?=number_format($unimedmens, 2, ',', '.');?></td>
			<td align="center" title="UNIMED"><?=number_format($unimed, 2, ',', '.');?></td>
			<td align="center" title="ALIMENTA?O"><?=number_format($alimentacao, 2, ',', '.');?></td>
			<td align="center" title="EMPR?TIMO"><?=number_format($emprestimo, 2, ',', '.');?></td>
			<?if($camp_contsindical=="Y"){?>		
			<td align="center" title="CONTR. SINDICAL"><?=number_format($contsindical, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="HABIT."><?=number_format($habit, 2, ',', '.');?></td>
			<td align="center" title="OUTROS"><?=number_format($outros, 2, ',', '.');?></td>
			<td align="center"></td>
			<td title="TOTAL"><?=number_format($vsoma, 2, ',', '.');?></td>			
		</tr>
		<tr class="res">
			<td align="right"  > F?IAS</td>
			<td align="center" title="SAL?RIO"><?=number_format($salariof, 2, ',', '.');?></td>
			<td align="center" title="INSALUBRIDADE"></td>
			<td align="center" title="HORA EXTRA"></td>
			<td align="center" title="ADIC. NOTURNO"></td>
			<?if($_1_u_folha_ferias=="Y"){?>
			<td align="center" title="F?IAS"></td>
			<?}?>
			<?//if($_1_u_folha_ferias13=="Y"){?>
			<td align="center" title=" 1/3 F?IAS"></td>		
			<?//}?>
			<?if($camp_differias=="Y"){?>
			<td align="center" title=" DIF. F?IAS"></td>
			<?}?>
			<?if($camp_trferias=="Y"){?>
			<td align="center" title=" TR. F?IAS"></td>
			<?}?>
			<?if($camp_estouromes=="Y"){?>
			<td align="center" title="ESTOURO M?"></td>	
			<?}?>		
			<?if($camp_trocomes=="Y"){?>
			<td align="center" title="TR. M?"></td>
			<?}?>
			<?if($camp_trocomesant=="Y"){?>
			<td align="center" title="T.M. ANT."></td>
			<?}?>
			<td align="center" title="INSS"><?=number_format($inssf, 2, ',', '.');?></td>
			<?if($_1_u_folha_insssferias=="Y"){?>
			<td align="center" title="INSS S/ F?IAS"></td>
			<?}?>
			<td align="center" title="IRRF"><?=number_format($irrff, 2, ',', '.');?></td>
			<?if($_1_u_folha_irferias=="Y"){?>
			<td align="center" title="IRRF S/F?IAS"></td>	
			<?}?>		
			<?if($camp_adiantferias=="Y"){?>
			<td align="center" title="ADIANT. F?IAS"></td>
			<?}?>
			<?if($camp_adiantdt=="Y"){?>
			<td align="center" title="ADIANT. 13?"></td>
			<?}?>
			<?if($camp_estouromesant=="Y"){?>
			<td align="center" title="ESTOURO M? ANT."></td>
			<?}?>
			<td align="center" title="VALE"></td>
			<td align="center" title="VALE TRANSPORTE"></td>
			<td align="center" title="ODONTO"></td>
			<td align="center" title="UNIMED MENS."></td>
			<td align="center" title="UNIMED"></td>
			<td align="center" title="ALIMENTA?O"></td>
			<td align="center" title="EMPR?TIMO"></td>	
			<?if($camp_contsindical=="Y"){?>		
			<td align="center" title="CONTR. SINDICAL"></td>
			<?}?>
			<td align="center" title="HABIT."></td>
			<td align="center" title="OUTROS"></td>
			<td title="TOTAL"></td>
			
		</tr>			
	</table>	
<?
}
?>


	<p>	


</body>
</html>
