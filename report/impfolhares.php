<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}

$az=$_GET['az'];
/*
 * $pagvaltabela: tabela principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "folha";
$pagvalcampos = array(
	"idfolha" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from folha where idfolha = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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
		<table>
		<tr>	
			<td align="right" class="rotulo">ID:</td> 
			<td nowrap class="texto"><?=$_1_u_folha_idfolha?></td>		
			<td align="right"class="rotulo">Título:</td>
			<td class="texto" nowrap><?=$_1_u_folha_titulo?></td>  	
		</tr>
		</table>
<p>
<?
if(!empty($_1_u_folha_idfolha)){

	$linha2=9;
	$linha1=7;
	$clausula=" ";

?>

<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>Lista de Pagamento</legend>
</fieldset>
<?
	$sqli="select  p.nomecurto,p.nome,
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
					i.farmacia,
 					i.unimedmens,
					i.unimed,
					i.alimentacao,
					i.emprestimo,
					i.outros,
					i.imprime ".$clausula."
				from pessoa p,folhaitem i
				where p.idpessoa = i.idpessoa
				-- and i.imprime = 'Y'
 				
				and i.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and i.idfolha=".$_1_u_folha_idfolha." order by p.nomecurto";
	$resi=d::b()->query($sqli) or die("Erro ao buscar items da folha sql=".$sqli);
	$i=1;
	$n=0;
	$salariof=0;
	$inssf=0;
	$irrff=0;
	while($rowi=mysqli_fetch_assoc($resi)){
		$i=$i+1;
		$n=$n+1;

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
		

	}
	
	
?>
	<table class="normal">
		<tr class="header">
		
			<td align="center">Salário</td>
			<td align="center">Insalubridade</td>
			<td align="center">Hora<br> Extra</td>
			<td align="center">Adic.<br>Notur.</td>
			<?if($_1_u_folha_ferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.ferias ";
			?>
			<td align="center">Férias</td>
			<?}?>
			<?if($_1_u_folha_ferias13=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.ferias13 ";
			?>
			<td align="center">1/3<br>Férias</td>
			<?}?>
			<?if($_1_u_folha_differias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.differias ";
			?>
			<td align="center">Dif.<br>Férias</td> 
			<?}?>
			<?if($_1_u_folha_trferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.trferias ";
			?>
			<td align="center">Tr.<br>Férias.</td> 
			<?}?>
			<?if($_1_u_folha_estouromes=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.estouromes ";
			?>
			<td align="center">Estouro<br>Mês</td>		
			<?}?>
			<?if($_1_u_folha_trmes=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.trocomes ";
			?>		
			<td align="center">Tr.<br>Mês</td>
			<?}?>
			<?if($_1_u_folha_trant=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.trocomesant ";
			?>	
			<td align="center">T.M.<br>Ant.</td>
			<?}?>					
		</tr>
		<tr class="res">
			<td align="center" title="SALÁRIO"><?=number_format($salario, 2, ',', '.');?></td>
			<td align="center" title="INSALUBRIDADE"><?=number_format($insalubridade, 2, ',', '.');?></td>
			<td align="center" title="HORA EXTRA"><?=number_format($he, 2, ',', '.');?></td>
			<td align="center" title="ADIC. NOTURNO"><?=number_format($adicnot, 2, ',', '.');?></td>
			<?if($_1_u_folha_ferias=="Y"){?>
			<td align="center" title="FÉRIAS"><?=number_format($ferias, 2, ',', '.');?></td>
			<?}?>
			<?if($_1_u_folha_ferias13=="Y"){?>
			<td align="center" title=" 1/3 FÉRIAS"><?=number_format($ferias13, 2, ',', '.');?></td>	
			<?}?>
			<?if($_1_u_folha_differias=="Y"){?>
			<td align="center" title=" DIF. FÉRIAS"><?=number_format($differias, 2, ',', '.');?></td>
			<?}?>
			<?if($_1_u_folha_trferias=="Y"){?>
			<td align="center" title=" TR. FÉRIAS"><?=number_format($trferias, 2, ',', '.');?></td>
			<?}?>
			<?if($_1_u_folha_estouromes=="Y"){?>
			<td align="center" title="ESTOURO MÊS"><?=number_format($estouromes, 2, ',', '.');?></td>	
			<?}?>	
			<?if($_1_u_folha_trmes=="Y"){?>				
			<td align="center" title="TR. MÊS"><?=number_format($trocomes, 2, ',', '.');?></td>
			<?}?>
			<?if($_1_u_folha_trant=="Y"){?>
			<td align="center" title="T.M. ANT."><?=number_format($trocomesant, 2, ',', '.');?></td>
			<?}?>
					
		</tr>	
		<tr>
			<td><br></td>
		</tr>
		<tr class="header">
			<td align="center">INSS</td>
			<?if($_1_u_folha_insssferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.insssferias ";
			?>
			<td align="center">INSS S/<br>Férias</td>
			<?}?>
			<td align="center">IRRF</td>
			<?if($_1_u_folha_irferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.irferias ";
			?>
			<td align="center">IRRF<br>S/Férias</td>
			<?}?>	 	 
			<?if($_1_u_folha_adiantferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.adiantferias ";
			?>
			<td align="center">Adiant.<br>Férias</td>
			<?}?>
			<?if($_1_u_folha_adiantdt=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.adiantdt ";
			?>
			<td align="center">Adiant.<br> 13º</td>
			<?}?>
			<?if($_1_u_folha_estouromesant=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.estouromesant ";
			?>
			<td align="center">Estouro<br>Mês Ant.</td>
			<?}?>
			<td align="center">Vale<br>(30%)</td>
			<td align="center">VT</td>
			<td align="center">Farmácia</td>
			<td align="center">Unimed <br> Mens.</td>
			<td align="center">Unimed</td>
			<td align="center">Aliment.</td>
			<td align="center">Emprést.</td>
			<?if($_1_u_folha_contsindical=="Y"){
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

		<tr class="res">
		<td align="center" title="INSS"><?=number_format($inss, 2, ',', '.');?></td>
			<?if($_1_u_folha_insssferias=="Y"){?>
			<td align="center" title="INSS S/ FÉRIAS"><?=number_format($insssferias, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="IRRF"><?=number_format($irrf, 2, ',', '.');?></td>
			<?if($_1_u_folha_irferias=="Y"){?>
			<td align="center" title="IRRF S/FÉRIAS"><?=number_format($irferias, 2, ',', '.');?></td>
			<?}?>		
			<?if($_1_u_folha_adiantferias=="Y"){?>
			<td align="center" title="ADIANT. FÉRIAS"><?=number_format($adiantferias, 2, ',', '.');?></td>
			<?}?>
			<?if($_1_u_folha_adiantdt=="Y"){?>
			<td align="center" title="ADIANT. 13º"><?=number_format($adiantdt, 2, ',', '.');?></td>
			<?}?>
			<?if($_1_u_folha_estouromesant=="Y"){?>
			<td align="center" title="ESTOURO MÊS ANT."><?=number_format($estouromesant, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="VALE"><?=number_format($vale, 2, ',', '.');?></td>
			<td align="center" title="VALE TRANSPORTE"><?=number_format($valetransp, 2, ',', '.');?></td>
			<td align="center" title="FARMÁCIA"><?=number_format($farmacia, 2, ',', '.');?></td>
			<td align="center" title="UNIMED MENS"><?=number_format($unimedmens, 2, ',', '.');?></td>
			<td align="center" title="UNIMED"><?=number_format($unimed, 2, ',', '.');?></td>
			<td align="center" title="ALIMENTAÇÃO"><?=number_format($alimentacao, 2, ',', '.');?></td>
			<td align="center" title="EMPRÉSTIMO"><?=number_format($emprestimo, 2, ',', '.');?></td>
			<?if($_1_u_folha_contsindical=="Y"){?>		
			<td align="center" title="CONTR. SINDICAL"><?=number_format($contsindical, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="HABIT."><?=number_format($habit, 2, ',', '.');?></td>
			<td align="center" title="OUTROS"><?=number_format($outros, 2, ',', '.');?></td>
			<td align="center"></td>
			<td title="TOTAL"><?=number_format($vsoma, 2, ',', '.');?></td>	
		
		</tr>	
	</table>	
	<p>	
<?

}
$inss1=$salario+$he+$insalubridade+$adicnot;
?>
<fieldset style="border: none; border-top: 2px solid silver;">
	<legend>Impostos</legend>
</fieldset>

<table class="normal">
	<tr class="header">
		<td colspan="2" align="center">INSS</td>
	</tr>
	<tr class="res">
		<td>Salário</td><td><?=$salario?></td>
	</tr>
	<tr class="res">
		<td>Hora Extra</td><td><?=$he?></td>
	</tr>
	<tr class="res">
		<td>Insalubridade</td><td><?=$insalubridade?></td>
	</tr>
	<tr class="res">
		<td>Adic. Notur.</td><td><?=$adicnot?></td>
	</tr>
	<tr class="header">
		<td>INSS Empresa</td><td><?=$inss1?></td>
	</tr>
</table>
<p>
<table class="normal">
		<tr class="header">
				<td align="center" colspan="3">FÉRIAS</td>			
		</tr>
		<tr class="header">
			
			<td align="center" title="SALÁRIO">Salário</td>
		 	<td align="center" title="INSS">INSS</td>
			<td align="center" title="IRRF">IRRF</td>
		</tr>
		<tr class="res">
			
			<td align="center" title="SALÁRIO"><?=number_format($salariof, 2, ',', '.');?> </td>
		 	<td align="center" title="INSS"><?=number_format($inssf, 2, ',', '.');?> </td>
			<td align="center" title="IRRF"><?=number_format($irrff, 2, ',', '.');?> </td>
		</tr>
</table>

	


</body>
</html>
