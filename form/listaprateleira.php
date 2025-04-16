<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}


$idobjeto = $_GET['idobjeto'];
$objeto = $_GET['objeto'];
?>


<!-- Mostrar mensagem de Aguarde e bloquear tela  -->

<script >


</script>
<style>
.bticon{
	
	height: 50px; 
	width: 50px;
	border: 1px solid #DCDCDC;
	display: block;
    float: left;
	
	font-size: 12px;
	font-weight: bold;
	text-decoration: none;
	text-align:center;
	line-height:50px;
	color:white;
			
	margin:2px;

		
	 /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
	
}

</style>

<input id="idobjeto" name="" type="hidden"	value="<?=$idobjeto?>">
<input id="objeto" name="" type="hidden"	value="<?=$objeto?>">
<?
$sql = "select * from prateleira where  status='ATIVO'  ".getidempresa('idempresa','prateleira')." order by ord,prateleira;";
		
		$res = d::b()->query($sql) or die("A Consulta das prateleiraS falhou :".mysqli_error()."<br>Sql:".$sql1); 
		$qtdrows= mysqli_num_rows($res);

if($qtdrows > 0){
	while($row = mysqli_fetch_array($res)){
		if($row["tipo"]=="PRATELEIRA"){
			$strtipo="Prateleira";
			$mostralinha="S";
		}else{
			$strtipo="Botijão";
			$mostralinha="N";
		}

		$vcol="";
		$sql1 = "select * from prateleiradim where idprateleira=".$row['idprateleira']." order by coluna;";
		
		$res1 = d::b()->query($sql1) or die("A Consulta das dimensàµes da prateleira falhou :".mysqli_error()."<br>Sql:".$sql1); 
		$qtdrows1= mysqli_num_rows($res1);

		if($qtdrows1 > 0){	
?>
<p>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading"><?=$row["prateleira"]?></div>
        <div  class="panel-body">
	
			
<?			while($row1 = mysqli_fetch_array($res1)){
				
			if($vcol!=$row1["coluna"]){
			 	$vcol=$row1["coluna"];
			 	if($vcol==1){$strcol="A";}elseif($vcol==2){$strcol="B";}elseif($vcol==3){$strcol="C";}elseif($vcol==4){$strcol="D";}elseif($vcol==5){$strcol="E";}elseif($vcol==6){$strcol="F";}
				elseif($vcol==7){$strcol="G";}elseif($vcol==8){$strcol="H";}elseif($vcol==9){$strcol="I";}elseif($vcol==10){$strcol="J";}elseif($vcol==11){$strcol="K";}elseif($vcol==12){$strcol="L";}
				elseif($vcol==13){$strcol="M";}elseif($vcol==14){$strcol="N";}elseif($vcol==15){$strcol="O";}elseif($vcol==16){$strcol="P";}elseif($vcol==17){$strcol="Q";}elseif($vcol==18){$strcol="R";}
			elseif($vcol==19){$strcol="S";}elseif($vcol==20){$strcol="T";}elseif($vcol==21){$strcol="U";}elseif($vcol==22){$strcol="V";}elseif($vcol==23){$strcol="X";}elseif($vcol==24){$strcol="Z";}
			 	
	if($mostralinha=="S"){
?>	
		<div style="float: left; min-width: 130px; border: #BEBEBE solid 1px; display: inline; margin: 1px;">
				<div style="background-color:#BEBEBE; text-align: center; font-size: 12px;"><?=$strcol?></div>
		
<?
	}
		$sql2 = "select * from prateleiradim where idprateleira=".$row['idprateleira']." and coluna = ".$row1["coluna"]." order by linha;";
		
		$res2 = d::b()->query($sql2) or die("A Consulta das dimensàµes da prateleira falhou :".mysqli_error()."<br>Sql:".$sql1); 
		$qtdrows2= mysqli_num_rows($res2);

		if($qtdrows2 > 0){	
			while($row2 = mysqli_fetch_array($res2)){											
				
?>
		
			<div style=" border: #BEBEBE solid 1px;  display: table; min-height: 40px; margin: 2px; width: -moz-available;">						
				<div style= "background-color:#BEBEBE; float: left; border: #BEBEBE solid 1px; margin: 1px; font-size: 12px;  min-height: 40px;">
					<div>
					<?if($row2["linha"]<10){echo("0");}?><?=$row2["linha"]?>
						
						
						
					</div>
				</div>
				<div style= "float: left;  min-width: 110px;">
<?
				$sqll="select l.idlotelocalizacao,lt.qtddisp as qtd,lt.*
						from lotelocalizacao l, lote lt
						where lt.idlote = l.idlote
						and lt.qtddisp>0
						and l.idobjeto = ".$row2["idprateleiradim"]."
						and l.tipoobjeto = 'prateleiradim'";
			
				$resl = d::b()->query($sqll) or die("A Consulta das dimensàµes da prateleira falhou :".mysqli_error()."<br>Sql:".$sql1);
				$qtdrowsl= mysqli_num_rows($resl);
				
				if($qtdrowsl > 0){
					while($rowl = mysqli_fetch_array($resl)){
						
						if($rowl["status"]=="APROVADO"){
							$cor = "#90EE90";
						}elseif($rowl["status"]=="QUARENTENA"){
							$cor = "#FFA500";
						}elseif($rowl["status"]=="REPROVADO"){
							$cor = "#FF6347";
						}else{
							$cor = "#CFCFCF";
						}

?>							
				    <div style= "background-color:<?=$cor?>; border: #BEBEBE solid 1px; margin: 1px; min-height: 40px; text-align: center;" >
					    <font color="red" style="font-size: 12px;"><?=$rowl["qtd"]?></font><br>
					<a title="" onclick="janelamodal('?_modulo=lote&_acao=u&idlote=<?=$rowl["idlote"]?>')">		    
					    <font color="Blue" style="font-size: 12px; font-weight: bold;"> <?=$rowl["partida"]?>/<?=$rowl["exercicio"]?></font></a>
				    </div>
<?
					}
				}
?>							
						
								
				</div>	
					
			</div>
			
				
<?		
			}		
		}
?>		
	</div>			
<?				 	
			 }
?>			
						
<?				
			}
			
		}
		?>		
	</div>
    </div>
    </div>
</div>
	
<?
	}	
}		
?>

