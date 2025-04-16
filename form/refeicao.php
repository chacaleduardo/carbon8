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
$pagvaltabela = "refeicao";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idrefeicao" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from refeicao where idrefeicao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php")

?>

<script >

			
function colorir(vthis){
	
	$(".colorido").removeClass("colorido");		

	$(vthis).children().addClass("colorido");
	
}

</script>
<style>

input{
font-size: 10px;
}
textarea{
font-size: 10px;
}
select{
font-size: 10px;
}

.colorido{
background-color: #CAE1FF;
}

</style>

    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
            <table>
            <tr> 
                <td align="right">ID:</td> 
                <td><label class="idbox"><?=$_1_u_refeicao_idrefeicao?></label>
                <input name="_1_<?=$_acao?>_refeicao_idrefeicao" 	type="hidden" 	value="<?=$_1_u_refeicao_idrefeicao?>" readonly='readonly'>
                </td> 
                <td align="right">Valor:</td> 
                <td><input name="_1_<?=$_acao?>_refeicao_valor" size="4" type="text" value="<?=$_1_u_refeicao_valor?>" 		vdecimal></td> 	
            </tr>
            </table>
        </div>
        <div class="panel-body">
<?
if(!empty($_1_u_refeicao_idrefeicao)){
	$sqli="select p.tipopagamento,p.nomecurto,p.nome,i.* 
		from refeicaoitem i left join pessoa p on (p.idpessoa = i.idpessoa)
		where".getidempresa('i.idempresa','refeicao')."
		and i.idrefeicao=".$_1_u_refeicao_idrefeicao." order by i.obs,p.nomecurto";
	$resi=d::b()->query($sqli) or die("Erro ao buscar items da refeicao sql=".$sqli);
	
	$resf=d::b()->query("select f.titulo,f.idfolha from refeicao r,folha f where r.idfolha=f.idfolha and r.idrefeicao = ".$_1_u_refeicao_idrefeicao) or die("erro ao buscar tà­tulo");
	$rowf=mysqli_fetch_assoc($resf);
?>

	
	<table class="table table-striped planilha">
		<tr>
			<td align="center" colspan="34">
                            <a title="<?=$rowf['titulo']?>" href="javascript:janelamodal('?_modulo=folha&_acao=u&idfolha=<?=$rowf["idfolha"]?>')">
                                <font color="Blue" style="font-size:10px;">Folha - <?=$rowf['titulo']?></font>
                            </a> 
                        </td>
		</tr>
		<tr class="header">
			<td></td>
			<td align="center">Nome</td>	
			<td align="center">01</td>
			<td align="center">02</td>
			<td align="center">03</td>
			<td align="center">04</td>
			<td align="center">05</td>
			<td align="center">06</td>
			<td align="center">07</td>
			<td align="center">08</td>
			<td align="center">09</td>	
			<td align="center">10</td>	
			<td align="center">11</td>
			<td align="center">12</td>
			<td align="center">13</td>
			<td align="center">14</td>
			<td align="center">15</td>
			<td align="center">16</td>
			<td align="center">17</td>
			<td align="center">18</td>
			<td align="center">19</td>	
			<td align="center">20</td>	
			<td align="center">21</td>
			<td align="center">22</td>
			<td align="center">23</td>
			<td align="center">24</td>
			<td align="center">25</td>
			<td align="center">26</td>
			<td align="center">27</td>
			<td align="center">28</td>
			<td align="center">29</td>
			<td align="center">30</td>
			<td align="center">31</td>
			<td align="center">Ref.</td>
			<td align="center">Total(R$)</td>
		</tr>
<?
	$i=1;
	$n=0;
	while($rowi=mysqli_fetch_assoc($resi)){
		$i=$i+1;
		$n=$n+1;
		$d1=$d1+$rowi['d01'];
		$d2=$d2+$rowi['d02'];
		$d3=$d3+$rowi['d03'];
		$d4=$d4+$rowi['d04'];
		$d5=$d5+$rowi['d05'];
		$d6=$d6+$rowi['d06'];
		$d7=$d7+$rowi['d07'];
		$d8=$d8+$rowi['d08'];
		$d9=$d9+$rowi['d09'];
		$d10=$d10+$rowi['d10'];
		$d11=$d11+$rowi['d11'];
		$d12=$d12+$rowi['d12'];
		$d13=$d13+$rowi['d13'];
		$d14=$d14+$rowi['d14'];
		$d15=$d15+$rowi['d15'];
		$d16=$d16+$rowi['d16'];
		$d17=$d17+$rowi['d17'];
		$d18=$d18+$rowi['d18'];
		$d19=$d19+$rowi['d19'];
		$d20=$d20+$rowi['d20'];
		$d21=$d21+$rowi['d21'];
		$d22=$d22+$rowi['d22'];
		$d23=$d23+$rowi['d23'];
		$d24=$d24+$rowi['d24'];
		$d25=$d25+$rowi['d25'];
		$d26=$d26+$rowi['d26'];
		$d27=$d27+$rowi['d27'];
		$d28=$d28+$rowi['d28'];
		$d29=$d29+$rowi['d29'];
		$d30=$d30+$rowi['d30'];
		$d31=$d31+$rowi['d31'];
		
		$d=$rowi['d01']+$rowi['d02']+$rowi['d03']+$rowi['d04']+$rowi['d05']+$rowi['d06']+$rowi['d07']+$rowi['d08']+$rowi['d09']+$rowi['d10']+$rowi['d11']+$rowi['d12']+$rowi['d13']+$rowi['d14']+$rowi['d15']+$rowi['d16']+$rowi['d17']+$rowi['d18']+$rowi['d19']+$rowi['d20']+$rowi['d21']+$rowi['d22']+$rowi['d23']+$rowi['d24']+$rowi['d25']+$rowi['d26']+$rowi['d27']+$rowi['d28']+$rowi['d29']+$rowi['d30']+$rowi['d31'];
		$sd=$sd+$d;
		$sv=$sv+($d*tratanumero($_1_u_refeicao_valor));
		
		
?>	
		<tr class="respreto"  onmouseover="colorir(this);">
			<td><?=$n?></td>
			<td><input name="_<?=$i?>_u_refeicaoitem_idrefeicaoitem"	type="hidden" value="<?=$rowi['idrefeicaoitem']?>">
<?
			if(!empty($rowi["idpessoa"])){
?>				
                            <a title="<?=$rowi['nome']?>" href="javascript:janelamodal('?_modulo=funcionario&_acao=u&idpessoa=<?=$rowi["idpessoa"]?>')">
                                <font color="Blue" style="font-size:10px;"><?=$rowi['nomecurto']?></font>
                            </a> 
				
<?
			}else{
				echo($rowi['obs']);
?>		
<?
			}
?>		
			</td>
		
			<td align="center"><input tabindex="<?=$i+1200?>" style="text-align: center;" title="01"  name="_<?=$i?>_u_refeicaoitem_d01" type="text" size="1" value="<?=$rowi['d01']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+1300?>" style="text-align: center;" title="02"  name="_<?=$i?>_u_refeicaoitem_d02" type="text" size="1" value="<?=$rowi['d02']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+1400?>" style="text-align: center;" title="03"  name="_<?=$i?>_u_refeicaoitem_d03" type="text" size="1" value="<?=$rowi['d03']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+1500?>" style="text-align: center;" title="04"  name="_<?=$i?>_u_refeicaoitem_d04" type="text" size="1" value="<?=$rowi['d04']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+1600?>" style="text-align: center;" title="05"  name="_<?=$i?>_u_refeicaoitem_d05" type="text" size="1" value="<?=$rowi['d05']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+1700?>" style="text-align: center;" title="06"  name="_<?=$i?>_u_refeicaoitem_d06" type="text" size="1" value="<?=$rowi['d06']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+1800?>" style="text-align: center;" title="07"  name="_<?=$i?>_u_refeicaoitem_d07" type="text" size="1" value="<?=$rowi['d07']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+1900?>" style="text-align: center;" title="08"  name="_<?=$i?>_u_refeicaoitem_d08" type="text" size="1" value="<?=$rowi['d08']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2000?>" style="text-align: center;" title="09"  name="_<?=$i?>_u_refeicaoitem_d09" type="text" size="1" value="<?=$rowi['d09']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2100?>" style="text-align: center;" title="10"  name="_<?=$i?>_u_refeicaoitem_d10" type="text" size="1" value="<?=$rowi['d10']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2200?>" style="text-align: center;" title="11"  name="_<?=$i?>_u_refeicaoitem_d11" type="text" size="1" value="<?=$rowi['d11']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2300?>" style="text-align: center;" title="12"  name="_<?=$i?>_u_refeicaoitem_d12" type="text" size="1" value="<?=$rowi['d12']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2400?>" style="text-align: center;" title="13"  name="_<?=$i?>_u_refeicaoitem_d13" type="text" size="1" value="<?=$rowi['d13']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2500?>" style="text-align: center;" title="14"  name="_<?=$i?>_u_refeicaoitem_d14" type="text" size="1" value="<?=$rowi['d14']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2600?>" style="text-align: center;" title="15"  name="_<?=$i?>_u_refeicaoitem_d15" type="text" size="1" value="<?=$rowi['d15']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2700?>" style="text-align: center;" title="16"  name="_<?=$i?>_u_refeicaoitem_d16" type="text" size="1" value="<?=$rowi['d16']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2800?>" style="text-align: center;" title="17"  name="_<?=$i?>_u_refeicaoitem_d17" type="text" size="1" value="<?=$rowi['d17']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+2900?>" style="text-align: center;" title="18"  name="_<?=$i?>_u_refeicaoitem_d18" type="text" size="1" value="<?=$rowi['d18']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+3000?>" style="text-align: center;" title="19"  name="_<?=$i?>_u_refeicaoitem_d19" type="text" size="1" value="<?=$rowi['d19']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+3100?>" style="text-align: center;" title="20"  name="_<?=$i?>_u_refeicaoitem_d20" type="text" size="1" value="<?=$rowi['d20']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+4100?>"  style="text-align: center;" title="21" name="_<?=$i?>_u_refeicaoitem_d21" type="text" size="1" value="<?=$rowi['d21']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+4200?>" style="text-align: center;" title="22"  name="_<?=$i?>_u_refeicaoitem_d22" type="text" size="1" value="<?=$rowi['d22']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+4300?>" style="text-align: center;" title="23"  name="_<?=$i?>_u_refeicaoitem_d23" type="text" size="1" value="<?=$rowi['d23']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+4400?>" style="text-align: center;" title="24"  name="_<?=$i?>_u_refeicaoitem_d24" type="text" size="1" value="<?=$rowi['d24']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+4500?>" style="text-align: center;" title="25"  name="_<?=$i?>_u_refeicaoitem_d25" type="text" size="1" value="<?=$rowi['d25']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+4600?>" style="text-align: center;" title="26"  name="_<?=$i?>_u_refeicaoitem_d26" type="text" size="1" value="<?=$rowi['d26']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+4700?>" style="text-align: center;" title="27"  name="_<?=$i?>_u_refeicaoitem_d27" type="text" size="1" value="<?=$rowi['d27']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+4800?>" style="text-align: center;" title="28"  name="_<?=$i?>_u_refeicaoitem_d28" type="text" size="1" value="<?=$rowi['d28']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+4900?>" style="text-align: center;" title="29"  name="_<?=$i?>_u_refeicaoitem_d29" type="text" size="1" value="<?=$rowi['d29']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+5000?>" style="text-align: center;" title="30"  name="_<?=$i?>_u_refeicaoitem_d30" type="text" size="1" value="<?=$rowi['d30']?>" vdecimal></td>
			<td align="center"><input tabindex="<?=$i+5100?>" style="text-align: center;" title="31"  name="_<?=$i?>_u_refeicaoitem_d31" type="text" size="1" value="<?=$rowi['d31']?>" vdecimal></td>
			<td align="center"><?=$d?></td>
			<td align="center"><?=number_format(($d*tratanumero($_1_u_refeicao_valor)), 2, ',', '.');?></td>
<?
		//if(empty($rowi['idpessoa'])){
?>			
			<td align="center">
                            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="CB.post({objetos:'_ajax_d_refeicaoitem_idrefeicaoitem=<?=$rowi['idrefeicaoitem']?>'})" title="Retirar"></i>
			</td>
<?
	//	}
?>			
		</tr>
<?
	}
?>
	<tr class="respreto">
		<td></td>
		<td>Total</td>		
		
		<td><?=$d1?></td>
		<td><?=$d2?></td>
		<td><?=$d3?></td>
		<td><?=$d4?></td>
		<td><?=$d5?></td>
		<td><?=$d6?></td>
		<td><?=$d7?></td>
		<td><?=$d8?></td>
		<td><?=$d9?></td>
		<td><?=$d10?></td>
		<td><?=$d11?></td>
		<td><?=$d12?></td>
		<td><?=$d13?></td>
		<td><?=$d14?></td>
		<td><?=$d15?></td>
		<td><?=$d16?></td>
		<td><?=$d17?></td>
		<td><?=$d18?></td>
		<td><?=$d19?></td>
		<td><?=$d20?></td>
		<td><?=$d21?></td>
		<td><?=$d22?></td>
		<td><?=$d23?></td>
		<td><?=$d24?></td>
		<td><?=$d25?></td>
		<td><?=$d26?></td>
		<td><?=$d27?></td>
		<td><?=$d28?></td>
		<td><?=$d29?></td>
		<td><?=$d30?></td>
		<td><?=$d31?></td>
		<td><?=$sd?></td>
		<td><?=number_format($sv, 2, ',', '.');?></td>
	</tr>
	</table>	
		

<?
}
?>

        </div>
    </div>
    </div>
	
