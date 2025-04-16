<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

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
$pagsql = "select * from folha where idfolha = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")
?>
				
<style type="text/css">
.colorido{
background-color: #CAE1FF;
}
input{
font-size: 10px;
}
textarea{
font-size: 10px;
}
select{
font-size: 10px;
}

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
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">

        <table>
<?
    if(!empty($_1_u_folha_idfolha)){
        $read = "readonly='readonly'";
    }
    if($_1_u_folha_status=="Fechada"){
        $readonly = "readonly='readonly'";
        $disabled = "disabled='disabled'";
    }
?>
        <tr> 
            <td align="right">ID:</td> 
            <td><label class="idbox"><?=$_1_u_folha_idfolha?></label>
            <input name="_1_<?=$_acao?>_folha_idfolha" 	type="hidden" 	value="<?=$_1_u_folha_idfolha?>" readonly='readonly'>
            </td> 
            <td align="right">Tà­tulo:</td> 
            <td><input <?=$readonly?> name="_1_<?=$_acao?>_folha_titulo" size="50" type="text" value="<?=$_1_u_folha_titulo?>" vnulo></td> 
            <td align="right">Data:</td> 
            <td><?if (empty($_1_u_folha_data)){$_1_u_folha_data= date("d/m/Y");}	?>						
                <input	<?=$disabled?> name="_1_<?=$_acao?>_folha_data"  class="calendario" type="text" size ="8" value="<?=$_1_u_folha_data?>" >							
            </td>
            <?if(empty($_1_u_folha_idfolha)){?>
            <td align="right">Copiar De:</td> 
            <td>
                <select  name="_1_<?=$_acao?>_folha_idfolhacopia">
                    <option value=""></option>
                    <?fillselect("select idfolha,titulo 
                                    from folha 
                                    where 1 ".getidempresa('idempresa','rhfolha')." order by data desc limit 6",$_1_u_folha_idfolhacopia);?>		
                </select>
            </td>
            <?}?>
            <td align="right">Status:</td> 
            <td>
                <select <?=$disabled?> name="_1_<?=$_acao?>_folha_status" vnulo>
                        <?fillselect("select 'Aberta','ABERTA' union select 'Em Andamento','EM ANDAMENTO' union select 'Fechada','FECHADA' ",$_1_u_folha_status);?>		
                </select>
            </td>
            <td>
                 <a class="fa fa-bars pointer hoverazul" title="Bioensaio Ctr" onclick="janelamodal('?_modulo=folhaitem&_acao=u&idfolha=<?=$_1_u_folha_idfolha?>')"></a>
            </td>
            
                <?
	if(!empty($_1_u_folha_idfolha)){		
?>	
            <td>
                <a title="IMPRIMIR" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/impfolha.php?_acao=u&idfolha=<?=$_1_u_folha_idfolha?>')">  &nbsp;&nbsp;Imprimir</a>     
            </td>
            <td>
                <a title="IMPRIMIR A/Z" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/impfolha.php?_acao=u&idfolha=<?=$_1_u_folha_idfolha?>&az=Y')">  &nbsp;&nbsp;Imprimir A/Z</a>
            </td>
            <td>
                <a title="RELATàRIO" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/impfolhares.php?_acao=u&idfolha=<?=$_1_u_folha_idfolha?>')">  &nbsp;&nbsp;Relatà³rio</a>
            </td>
<?
	}
?>	
            
        </tr>
        </table>    
        </div>
        <div class="panel-body">
<?
if(!empty($_1_u_folha_idfolha)){
		
		$linha2=8;
		$linha1=8;
		$clausula=" ";
		// BUSCAR A REFEICAO DA FOLHA
		$sql0="select idrefeicao from refeicao where status='ATIVA' and idfolha=".$_1_u_folha_idfolha;
		$res0=d::b()->query($sql0) or die('erro ao buscar refeição correspondente da folha'.$sql0);
		$row0=mysqli_fetch_assoc($res0);
		
		
		$sqlcamp="select
					`salario`,
 					dia,
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
			where  1 ".getidempresa('idempresa','rhfolhaitem')."
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


	<table class="table table-striped planilha">
		<tr >
			<th align="center" >#</th>
			<th>Nome</th>
			<th style="background-color: #87CEFF;" align="center">Dias Tr.</th>
			<th style="background-color: #87CEFF;" align="center">Salário</th>
			<th style="background-color: #87CEFF;" align="center">Hora<br> Extra</th>
			<th style="background-color: #87CEFF;" align="center">Adic.<br>Notur.</th>
			<?if($_1_u_folha_ferias=="Y"){//desativado
				$linha1=$linha1+1;
				$clausula.=",i.ferias ";
			?>
			<th style="background-color: #87CEFF;" align="center">Horas<br> Férias</th>
			<?}?>
		
			<?if($camp_differias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.differias ";
			?>
			<th style="background-color: #87CEFF;" align="center">Dif.<br>Férias</th>  
			<?}?>
			<?if($camp_trferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.trferias ";
			?>
			<th style="background-color: #87CEFF;" align="center">Tr.<br>Férias.</th>
			<?}?>
			<?if($camp_trocomes=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.trocomes ";
			?>			
			<th style="background-color: #87CEFF;" align="center">Tr.<br>Màªs</th>
			<?}?>
			<?if($camp_trocomesant=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.trocomesant ";
			?>	
			<th style="background-color: #FF6A6A;" align="center">T.M.<br>Ant.</th>
			<?}?>
			<th style="background-color: #FF6A6A;" align="center">INSS</th> 
			<?if($_1_u_folha_insssferias=="Y"){//desativado
				$linha1=$linha1+1;
				$clausula.=",i.insssferias ";
			?>
			<th style="background-color: #FF6A6A;" align="center">INSS S/<br>Férias</th>
			<?}?>
			<th style="background-color: #FF6A6A;" align="center">IRRF</th>
			<?if($_1_u_folha_irferias=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.irferias ";
			?>
			<th style="background-color: #FF6A6A;" align="center">IRRF<br>S/Férias</th>  
			<?}?>	 	 
			<?if($camp_adiantferias=="Y"){//desativado
				$linha1=$linha1+1;
				$clausula.=",i.adiantferias ";
			?>
			<th style="background-color: #FF6A6A;" align="center">Adiant.<br>Férias</th>
			<?}?>
			<?if($camp_adiantdt=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.adiantdt ";
			?>
			<th style="background-color: #FF6A6A;" align="center">Adiant.<br> 13º</th>
			<?}?>
			<?if($camp_estouromesant=="Y"){
				$linha1=$linha1+1;
				$clausula.=",i.estouromesant ";
			?>
			<th style="background-color: #FF6A6A;" align="center">Estouro<br>Màªs Ant.</th>
			<?}?>
			<th style="background-color: #FF6A6A;" align="center">Vale<br>(30%)</th>
			<th style="background-color: #FF6A6A;" align="center">VT</th>
			<th style="background-color: #FF6A6A;" align="center">Odonto</th>
			<th style="background-color: #FF6A6A;" align="center">Unimed <br> Mens.</th>
			<th style="background-color: #FF6A6A;" align="center">Unimed</th>
			<th style="background-color: #FF6A6A;" align="center">
                            <a title="Ficha de Refeição" href="javascript:janelamodal('?_modulo=refeicao&_acao=u&idrefeicao=<?=$row0['idrefeicao']?>')">
                                <font color="Blue" style="font-size:10px;">Aliment.</font>
                            </a> 
                        </th>
			<th style="background-color: #FF6A6A;" align="center">Emprést.</th>
			<?if($camp_contsindical=="Y"){
				$linha2=$linha2+1;
				$clausula.=",i.contsindical ";
			?>
			<th style="background-color: #FF6A6A;" align="center">Contr.<br>Sindical</th> 
			<?}?>
			<th style="background-color: #FF6A6A;" align="center">Habit.</th>
			<th style="background-color: #FF6A6A;" align="center">Desc.<br>Diversos</th>
			<th align="center">Soma</th>
			<th align="center">Obs.</th>
			<th align="center">Obs. <br>Interna</th>
			<th align="center">#</th>
		</tr>
<?

	$sqli="select	CASE p.tipopagamento
						WHEN 'TRANSFERENCIA' THEN 'TRANSFERàNCIA'
						WHEN 'CONVENIO' THEN 'CONVàNIO'
						WHEN 'CHEQUE' THEN 'CHEQUE'
					ELSE 'OUTROS'
					END AS tipopagamento
					,p.nomecurto,p.nome,
					i.dia,
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
					i.imprime ".$clausula."
			from pessoa p,folhaitem i
			where p.idpessoa = i.idpessoa
 			 ".getidempresa('i.idempresa','rhfolha')."
			and i.idfolha=".$_1_u_folha_idfolha." order by tipopagamento desc,p.nomecurto";
	$resi=d::b()->query($sqli) or die("Erro ao buscar items da folha sql=".$sqli);
	


	$i=1;
	$n=0;
	while($rowi=mysqli_fetch_assoc($resi)){
		$i=$i+1;
		if($rowi['tipo']!='F'){
		$n=$n+1;
		}
		//controla a linha do tipopagamento
		if(empty($tipopagamento)){
			$tipopagamento=$rowi['tipopagamento'];
		}elseif($tipopagamento!=$rowi['tipopagamento']){					
?>		
		<tr>
			<td colspan="<?=$linha1?>"></td>
			<td><?=number_format($svale, 2, ',', '.');?></td>
			<td colspan="<?=$linha2?>" align="right"><?=$tipopagamento?></td>
			<td><?=number_format($tipopgsoma, 2, ',', '.');?></td>
			<td></td>
		</tr>
<?			$tipopgsoma=0;
			$svale=0;
			$tipopagamento=$rowi['tipopagamento'];
		}
		//troca a cor da linha
		if(!empty($bgcolor)){
			$bgcolor = "";
		}else{
			$bgcolor = "white";
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
		
		
		//CALCULAR ALIMENTAààO
		$sqla="select (ifnull(d01,0)+ifnull(d02,0)+ifnull(d03,0)+ifnull(d04,0)+ifnull(d05,0)+ifnull(d06,0)+ifnull(d07,0)+ifnull(d08,0)+ifnull(d09,0)+ifnull(d10,0)
			+ifnull(d11,0)+ifnull(d12,0)+ifnull(d13,0)+ifnull(d14,0)+ifnull(d15,0)+ifnull(d16,0)+ifnull(d17,0)+ifnull(d18,0)+ifnull(d19,0)+ifnull(d20,0)
			+ifnull(d21,0)+ifnull(d22,0)+ifnull(d23,0)+ifnull(d24,0)+ifnull(d25,0)+ifnull(d26,0)+ifnull(d27,0)+ifnull(d28,0)+ifnull(d29,0)+ifnull(d30,0)+ifnull(d31,0)) * ifnull(r.valor,0)  as valimentacao			
			 from folhaitem fi,refeicao r,refeicaoitem ri
			where ri.idpessoa = fi.idpessoa
			and fi.tipo != 'F'
			and ri.idrefeicao =r.idrefeicao
			and r.idfolha = fi.idfolha
			and fi.idfolhaitem=".$rowi['idfolhaitem'];
		$resa=d::b()->query($sqla) or die('erro ao buscar valor da alimentação sql'.$sqla);
		$rowa = mysqli_fetch_assoc($resa);
		
		$rowi['alimentacao'] = $rowa['valimentacao'];
		

		$sqlf="select * from folhaitem where idpessoa=".$rowi["idpessoa"]." and tipo ='F' and idfolha=".$_1_u_folha_idfolha;
		$resf=d::b()->query($sqlf) or die("Erro ao buscar se funcionario esta em ferias sql=".$sqlf);
		$qtdf=mysql_num_rows($resf);
				
?>		
<?if($qtdf<1){$corf="";}else{$corf="#00FF7F";}?>
		<tr class="respreto"  onmouseover="colorir(this);" style="background-color: <?=$corf?>">
			<td>
				<?if($rowi['imprime']=="Y"){?>
				<div  class="divverde" id="<?=$rowi['idfolhaitem']?>" onclick="selecionaitem('imprime','folhaitem',<?=$rowi['idfolhaitem']?>);"></div>
				<?}else{?>		
				<div class="divvermelho" id="<?=$rowi['idfolhaitem']?>" onclick="selecionaitem('imprime','folhaitem',<?=$rowi['idfolhaitem']?>);"></div>
				<?}?>
			</td>
			<td nowrap align="left">
			<?if($rowi['tipo']=='F'){?>
			<font color="red">Férias</font>
			
			<?}else{?>
				<?=$n?>-                                
                            <a title="<?=$rowi['nome']?>" href="javascript:janelamodal('?_modulo=funcionario&_acao=u&idpessoa=<?=$rowi["idpessoa"]?>')">
                                <font color="Blue" style="font-size:10px;"><?=$rowi['nomecurto']?></font>
                            </a> 
			<?}?>
				<input name="_<?=$i?>_u_folhaitem_idfolhaitem"	type="hidden" value="<?=$rowi['idfolhaitem']?>">
			</td>
			<td  align="center"><input <?=$readonly?> title="Dias Tr"  placeholder="Dias Tr" name="_<?=$i?>_u_folhaitem_dia" type="text" size="5" value="<?=$rowi['dia']?>" vdecimal></td>
			<td  align="center"><input <?=$readonly?> title="salario"  placeholder="salario" name="_<?=$i?>_u_folhaitem_salario" type="text" size="5" value="<?=$rowi['salario']?>" vdecimal></td>
			
			<td align="center"><input <?=$readonly?> title="Hora Extra" placeholder="Hr.Extra" name="_<?=$i?>_u_folhaitem_horaextra" type="text" size="5" value="<?=$rowi['horaextra']?>" vdecimal></td>			
			<td align="center"><input <?=$readonly?> title="Adic. Noturno"  placeholder="Ad. Not." name="_<?=$i?>_u_folhaitem_adicnot" type="text" size="5" value="<?=$rowi['adicnot']?>" vdecimal></td>
			<?if($_1_u_folha_ferias=="Y"){//desativado
				?>
			<td align="center"><input <?=$readonly?> title="Férias"  placeholder="Férias" name="_<?=$i?>_u_folhaitem_ferias" type="text" size="5" value="<?=$rowi['ferias']?>" vdecimal></td>
			<?}?>
			
			<?if($camp_differias=="Y"){?>
			<td align="center"><input <?=$readonly?> title="Dif. Férias"  placeholder="Dif. Férias" name="_<?=$i?>_u_folhaitem_differias" type="text" size="5" value="<?=$rowi['differias']?>" vdecimal></td>
			<?}?>
			<?if($camp_trferias=="Y"){?>
			<td align="center"><input <?=$readonly?> title="Tr. Férias"  placeholder="Tr. Férias" name="_<?=$i?>_u_folhaitem_trferias" type="text" size="5" value="<?=$rowi['trferias']?>" vdecimal></td>
			<?}?>
			
			<?if($camp_trocomes=="Y"){?>
			<td align="center"><input <?=$readonly?> title="Troco Màªs"  placeholder="Tr. Màªs" name="_<?=$i?>_u_folhaitem_trocomes" type="text" size="2" value="<?=$rowi['trocomes']?>" vdecimal></td>
			<?}?>
			<?if($camp_trocomesant=="Y"){?>
			<td align="center"><input <?=$readonly?> title="Troco Màªs Anterior"  placeholder="T.M. Ant." name="_<?=$i?>_u_folhaitem_trocomesant" type="text" size="2" value="<?=$rowi['trocomesant']?>" vdecimal></td>
			<?}?>
			<td align="center"><input <?=$readonly?> title="INSS"  placeholder="INSS" name="_<?=$i?>_u_folhaitem_inss" type="text" size="5" value="<?=$rowi['inss']?>" vdecimal></td>
			<?if($_1_u_folha_insssferias=="Y"){//desativado
			?>
			<td align="center"><input <?=$readonly?> title="INSS S/ Férias"  placeholder="INSS S/ Fér" name="_<?=$i?>_u_folhaitem_insssferias" type="text" size="5" value="<?=$rowi['insssferias']?>" vdecimal></td>
			<?}?>
			<td align="center"><input <?=$readonly?> title="IRRF"  placeholder="IRRF" name="_<?=$i?>_u_folhaitem_irrf" type="text" size="5" value="<?=$rowi['irrf']?>" vdecimal></td>
			<?if($_1_u_folha_irferias=="Y"){//desativado
			
			?>
			<td align="center"><input <?=$readonly?> title="IR S/ Férias"  placeholder="IR S/ Fer" name="_<?=$i?>_u_folhaitem_irferias" type="text" size="5" value="<?=$rowi['irferias']?>" vdecimal></td>
			<?}?>
			<?if($camp_adiantferias=="Y"){?>
			<td align="center"><input <?=$readonly?> title="Adiant. Férias"  placeholder="Ad. Férias" name="_<?=$i?>_u_folhaitem_adiantferias" type="text" size="5" value="<?=$rowi['adiantferias']?>" vdecimal></td>
			<?}?>
			<?if($camp_adiantdt=="Y"){?>
			<td align="center"><input <?=$readonly?> title="Adiant. 13º"  placeholder="Ad. 13º" name="_<?=$i?>_u_folhaitem_adiantdt" type="text" size="5" value="<?=$rowi['adiantdt']?>" vdecimal></td>
			<?}?>
			<?if($camp_estouromesant=="Y"){?>
			<td align="center"><input <?=$readonly?> title="Estouro Màªs Ant"  placeholder="Est. M. Ant" name="_<?=$i?>_u_folhaitem_estouromesant" type="text" size="5" value="<?=$rowi['estouromesant']?>" vdecimal></td>
			<?}?>
			<td align="center"><input <?=$readonly?> title="Vale"  placeholder="Vale" name="_<?=$i?>_u_folhaitem_vale" type="text" size="5" value="<?=$rowi['vale']?>" vdecimal></td>
			<td align="center"><input <?=$readonly?> title="VT"  placeholder="VT" name="_<?=$i?>_u_folhaitem_valetransp" type="text" size="5" value="<?=$rowi['valetransp']?>" vdecimal></td>
			<td align="center"><input <?=$readonly?> title="Odonto"  placeholder="Odonto" name="_<?=$i?>_u_folhaitem_farmacia" type="text" size="5" value="<?=$rowi['farmacia']?>" vdecimal></td>
			<td align="center"><input <?=$readonly?> title="Unimed Mens."  placeholder="Unimed Mens." name="_<?=$i?>_u_folhaitem_unimedmens" type="text" size="5" value="<?=$rowi['unimedmens']?>" vdecimal></td>
			<td align="center"><input <?=$readonly?> title="Unimed"  placeholder="Unimed" name="_<?=$i?>_u_folhaitem_unimed" type="text" size="5" value="<?=$rowi['unimed']?>" vdecimal></td>
			<td align="center"><input <?=$readonly?> title="Alimentação"  placeholder="Alimen." name="_<?=$i?>_u_folhaitem_alimentacao" type="text" size="5" value="<?=$rowi['alimentacao']?>" vdecimal></td>
			<td align="center"><input <?=$readonly?> title="Empréstimo"  placeholder="Emprést." name="_<?=$i?>_u_folhaitem_emprestimo" type="text" size="5" value="<?=$rowi['emprestimo']?>" vdecimal></td>
			<?if($camp_contsindical=="Y"){?>
			<td align="center"><input <?=$readonly?> title="Contr. Sindical"  placeholder="Contr. S." name="_<?=$i?>_u_folhaitem_contsindical" type="text" size="5" value="<?=$rowi['contsindical']?>" vdecimal></td>
			<?}?>
			<td align="center"><input <?=$readonly?> title="Habit."  placeholder="Habit." name="_<?=$i?>_u_folhaitem_habit" type="text" size="5" value="<?=$rowi['habit']?>" vdecimal></td>
			<td align="center"><input <?=$readonly?> title="Outros"  placeholder="Outros" name="_<?=$i?>_u_folhaitem_outros" type="text" size="5" value="<?=$rowi['outros']?>" vdecimal></td>
			<td align="center"><?=number_format($soma, 2, ',', '.');?></td>
			<td align="center">
			<?if(!empty($rowi['obs'])){?>
			<img src="../img/doc16.png" title="<?=$rowi['obs']?>"></img>
		 	<?}?>			
			</td>
			<td align="center">
			<?if(!empty($rowi['obsint'])){?>
			<img src="../img/doc16.png" title="<?=$rowi['obsint']?>"></img>
		 	<?}?>
			</td>
			<td align="center">
			<?if($_1_u_folha_status!="Fechada"){?>
                             <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="CB.post({objetos:'_ajax_d_folhaitem_idfolhaitem=<?=$rowi['idfolhaitem']?>'})" title="Retirar da folha"></i>
                   
			<?}?>	
			</td>
			<td>
			<?if($rowi['tipo']!='F'){?>
			<a class="fa fa-bars pointer hoverazul" title="Editar" onclick="janelamodal('?_modulo=folhaitem&_acao=u&idfolhaitem=<?=$rowi['idfolhaitem']?>')"></a>
			<?}?>
			</td>
		</tr>

<?
	}

?>

		<tr class="respreto">
			<td colspan="<?=$linha1?>"></td>
			<td><?=number_format($svale, 2, ',', '.');?></td>
			<td colspan="<?=$linha2?>" align="right"><?=$tipopagamento?></td>
			<td><?=number_format($tipopgsoma, 2, ',', '.');?></td>
			
		</tr>
		<tr class="respreto">
			<td align="right"  colspan="3"> TOTAL</td>
			
		<td align="center" title="HORA EXTRA"><?=number_format($salario, 2, ',', '.');?></td>
			<td align="center" title="HORA EXTRA"><?=number_format($he, 2, ',', '.');?></td>
			<td align="center" title="ADIC. NOTURNO"><?=number_format($adicnot, 2, ',', '.');?></td>
			<?if($_1_u_folha_ferias=="Y"){//desativado?>
			<td align="center" title="FàRIAS"><?=number_format($ferias, 2, ',', '.');?></td>
			<?}?>
			
			<?if($camp_differias=="Y"){?>
			<td align="center" title=" DIF. FàRIAS"><?=number_format($differias, 2, ',', '.');?></td>
			<?}?>
			<?if($camp_trferias=="Y"){?>
			<td align="center" title=" TR. FàRIAS"><?=number_format($trferias, 2, ',', '.');?></td>
			<?}?>
			
			<?if($camp_trocomes=="Y"){?>
			<td align="center" title="TR. MàS"><?=number_format($trocomes, 2, ',', '.');?></td>
			<?}?>
			<?if($camp_trocomesant=="Y"){?>
			<td align="center" title="T.M. ANT."><?=number_format($trocomesant, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="INSS"><?=number_format($inss, 2, ',', '.');?></td>
			<?if($_1_u_folha_insssferias=="Y"){//desativado?>
			<td align="center" title="INSS S/ FàRIAS"><?=number_format($insssferias, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="IRRF"><?=number_format($irrf, 2, ',', '.');?></td>
			<?if($_1_u_folha_irferias=="Y"){//desativado?>
			<td align="center" title="IRRF S/FàRIAS"><?=number_format($irferias, 2, ',', '.');?></td>	
			<?}?>		
			<?if($camp_adiantferias=="Y"){?>
			<td align="center" title="ADIANT. FàRIAS"><?=number_format($adiantferias, 2, ',', '.');?></td>
			<?}?>
			<?if($camp_adiantdt=="Y"){?>
			<td align="center" title="ADIANT. 13º"><?=number_format($adiantdt, 2, ',', '.');?></td>
			<?}?>
			<?if($camp_estouromesant=="Y"){?>
			<td align="center" title="ESTOURO MàS ANT."><?=number_format($estouromesant, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="VALE"><?=number_format($vale, 2, ',', '.');?></td>
			<td align="center" title="VALE TRANSPORTE"><?=number_format($valetransp, 2, ',', '.');?></td>
			<td align="center" title="FARMàCIA"><?=number_format($farmacia, 2, ',', '.');?></td>
			<td align="center" title="UNIMED MENS."><?=number_format($unimedmens, 2, ',', '.');?></td>
			<td align="center" title="UNIMED"><?=number_format($unimed, 2, ',', '.');?></td>
			<td align="center" title="ALIMENTAààO"><?=number_format($alimentacao, 2, ',', '.');?></td>
			<td align="center" title="EMPRàSTIMO"><?=number_format($emprestimo, 2, ',', '.');?></td>	
			<?if($camp_contsindical=="Y"){?>		
			<td align="center" title="CONTR. SINDICAL"><?=number_format($contsindical, 2, ',', '.');?></td>
			<?}?>
			<td align="center" title="HABIT."><?=number_format($habit, 2, ',', '.');?></td>
			<td align="center" title="OUTROS"><?=number_format($outros, 2, ',', '.');?></td>
			<td title="TOTAL"><?=number_format($vsoma, 2, ',', '.');?></td>
		</tr>
		
		
		<tr class="respreto">
			<td align="right"  colspan="3"> FàRIAS</td>
			<td align="center" title="SALàRIO"><?=number_format($salariof, 2, ',', '.');?></td>
			
			<td align="center" title="HORA EXTRA"></td>
			<td align="center" title="ADIC. NOTURNO"></td>
			<?if($_1_u_folha_ferias=="Y"){//desativado?>
			<td align="center" title="FàRIAS"></td>
			<?}?>
			
			<?if($camp_differias=="Y"){?>
			<td align="center" title=" DIF. FàRIAS"></td>
			<?}?>
			<?if($camp_trferias=="Y"){?>
			<td align="center" title=" TR. FàRIAS"></td>
			<?}?>
			
			<?if($camp_trocomes=="Y"){?>
			<td align="center" title="TR. MàS"></td>
			<?}?>
			<?if($camp_trocomesant=="Y"){?>
			<td align="center" title="T.M. ANT."></td>
			<?}?>
			<td align="center" title="INSS"><?=number_format($inssf, 2, ',', '.');?></td>
			<?if($_1_u_folha_insssferias=="Y"){//desativado?>
			<td align="center" title="INSS S/ FàRIAS"></td>
			<?}?>
			<td align="center" title="IRRF"><?=number_format($irrff, 2, ',', '.');?></td>
			<?if($_1_u_folha_irferias=="Y"){//desativado?>
			<td align="center" title="IRRF S/FàRIAS"></td>	
			<?}?>		
			<?if($camp_adiantferias=="Y"){?>
			<td align="center" title="ADIANT. FàRIAS"></td>
			<?}?>
			<?if($camp_adiantdt=="Y"){?>
			<td align="center" title="ADIANT. 13º"></td>
			<?}?>
			<?if($camp_estouromesant=="Y"){?>
			<td align="center" title="ESTOURO MàS ANT."></td>
			<?}?>
			<td align="center" title="VALE"></td>
			<td align="center" title="VALE TRANSPORTE"></td>
			<td align="center" title="FARMàCIA"></td>
			<td align="center" title="UNIMED MENS."></td>
			<td align="center" title="UNIMED"></td>
			<td align="center" title="ALIMENTAààO"></td>
			<td align="center" title="EMPRàSTIMO"></td>	
			<?if($camp_contsindical=="Y"){?>		
			<td align="center" title="CONTR. SINDICAL"></td>
			<?}?>
			<td align="center" title="HABIT."></td>
			<td align="center" title="OUTROS"></td>
			<td title="TOTAL"></td>
			
		</tr>		
		<TR>
			<TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>
		</TR>	
	</table>

<?
}
?>

        <table >
            <tr >
                <td>Funcinário:</td>
                <td align="center">                       
                       
                        <select <?=$disabled?> name="folhaitem_idpessoa"  onchange="inserirpessoa(this,<?=$_1_u_folha_idfolha?>);" style="background-color: #EFEFEE; font-weight:bold; font-size:8px;">
                                        <option value=""></option>
                                         <?fillselect("select p.idpessoa,p.nomecurto 
                                                                        from pessoa p
                                                                        where p.idtipopessoa = 1 
                                                                         ".getidempresa('p.idempresa','pessoa')."
                                                                        and p.status = 'ATIVO'
                                                                        and not exists (select 1 from folhaitem i where i.idfolha = ".$_1_u_folha_idfolha." and i.idpessoa = p.idpessoa) order by p.nomecurto");?>
                        </select>
                </td>
            </tr>
        </table>
        </div>
    </div>
</div>
<?
if(!empty($_1_u_folha_idfolha)){
	?>

<p>
<!--div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">Arquivos Anexos</div>
      <div class="panel-body">
           <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:50%;height:100%;">
                   <i class="fa fa-cloud-upload fonte18"></i>
           </div>
       </div> 
     </div>
</div-->  
<?

	}	
?>
<p>	

<?
if(!empty($_1_u_folha_idfolha)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_folha_idfolha; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "folha"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

            
<script>

			
//salva os flag baixa e tempo do item
function selecionaitem(vcampo,vtabela,vidcampo){
		

	//alert(vidcampo);
	 $.ajax({
			type: "post",
			url : "../ajax/checkitem.php",
			data: {idcampo : vidcampo,
		 			campo:vcampo,
		 			tabela:vtabela},
			success: function(data){// retorno 200 do servidor apache
				vdata = data.replace(/(\r\n|\r|\n)/g, "");
				
					if(vdata=='Y'){
						  $("#"+vidcampo).removeClass("divvermelho");
						  $("#"+vidcampo).addClass("divverde");
					}else{
						if(vdata=='N'){
							  $("#"+vidcampo).removeClass("divverde");
							  $("#"+vidcampo).addClass("divvermelho");						
						}else{
							alert(data);
	                        document.body.style.cursor = "default";
						}
					}
				},
			error: function(objxml){ // nao retornou com sucesso do apache
	                        document.body.style.cursor = "default";
				alert('Erro: '+objxml.status);
			}
		})//$.ajax
}

function selecionacampo(vcampo,vtabela,vidcampo){
	

	//alert(vidcampo);
	 $.ajax({
			type: "post",
			url : "../ajax/checkitem.php",
			data: {idcampo : vidcampo,
		 			campo:vcampo,
		 			tabela:vtabela},
			success: function(data){
				vdata = data.replace(/(\r\n|\r|\n)/g, "");
					if(vdata=='Y' || vdata=='N'){
						document.location.reload();
					}else{
						alert(data);
                        document.body.style.cursor = "default";
					}
				},
			error: function(objxml){ // nao retornou com sucesso do apache
	                        document.body.style.cursor = "default";
				alert('Erro: '+objxml.status);
			}
		})//$.ajax
}

function colorir(vthis){
	
	$(".colorido").removeClass("colorido");		

	$(vthis).children().addClass("colorido");
	
}

if( $("[name=_1_u_folha_idfolha]").val() ){
    $(".cbupload").dropzone({
        idObjeto: $("[name=_1_u_folha_idfolha]").val()
        ,tipoObjeto: 'folha'
		,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
    });
}


function inserirpessoa(vthis,inidfolha){
   
     CB.post({
        objetos: "_x_i_folhaitem_idpessoa="+$(vthis).val()+"&_x_i_folhaitem_idfolha="+inidfolha
        ,parcial:true
    });
}
</script>