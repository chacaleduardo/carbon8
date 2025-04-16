<?

require_once("../inc/php/functions.php");

$idnotafiscal= $_GET['idnotafiscal'];
$idcontroleemissao= $_GET['idcontroleemissao'];
$idcliente= $_GET['idcliente'];
$nucleo =$_GET['nucleo'];
$lote =$_GET['lote'];
$exercicio = $_GET['exercicio'];
$idregistro = $_GET['idregistro'];
$idregistro2 = $_GET['idregistro2'];






if(empty($idcontroleemissao) ){
	die("O ID Email deve ser informado!!!");
}


	if(!empty($idcliente)){
		
		$tpessoa=traduzid("pessoa","idpessoa","idtipopessoa",$idcliente);
		
		if($tpessoa==10){
			if(empty($idregistro)){
				die("Favor informar o IDREGISTRO para a busca pois se trata de um contato oficial");
			}
			
			$stridbusca=' r.idsecretaria ';
		}else{
			$stridbusca=' a.idpessoa ';
		}

		if(!empty($exercicio)){
			$strexercicio = "and v.exercicio =".$exercicio." "; 
		}
		//FABIO BERNARDES ROSSI - SOLICITOU PARA BUSCAR TODOS OS RESULTADOS ATE OS ASSINADOS - 22082014
		if(!empty($idnotafiscal)){
			$_sql = "select v.idresultado,v.idregistro,v.exercicio,v.lote,v.nucleo,v.nome,v.descr,v.idpessoa,v.idnotafiscal 
					from vwbuscaresultadopessoa v
				 where v.idnotafiscal = ".$idnotafiscal." ".$strexercicio." 
				 and not exists (select 1 from controleemissaoitem e where e.idcontroleemissao = ".$idcontroleemissao." and e.idobjeto=v.idresultado and e.tipoobjeto='resultado') order by v.idregistro";	
		}elseif(!empty($idregistro)){
			$_sql = "select r.idresultado,a.idregistro,a.exercicio,a.lote,nu.nucleo,p.nome,pr.descr,p.idpessoa        
   					from resultado r, pessoa p, prodserv pr, amostra a left join nucleo nu ON (a.idnucleo = nu.idnucleo)
					    where pr.idprodserv = r.idtipoteste
					    and p.idpessoa = a.idpessoa
						and r.status = 'ASSINADO'
					    and r.idamostra = a.idamostra
					    and ".$stridbusca." = ".$idcliente."
						and a.exercicio = ".$exercicio."
           				and a.idregistro between ".$idregistro." and ".$idregistro2." 
                                        and not exists (select 1 from controleemissaoitem e where e.idcontroleemissao = ".$idcontroleemissao." and e.idobjeto=v.idresultado and e.tipoobjeto='resultado') order by a.idregistro";
		}elseif(!empty($nucleo)){
			$_sql = "select v.idresultado,v.idregistro,v.exercicio,v.lote,v.nucleo,v.nome,v.descr,v.idpessoa,v.idnotafiscal 
                                from vwbuscaresultadopessoa v
					where v.idpessoa = ".$idcliente."
					and v.nucleo like ('%".$nucleo."%')".$strexercicio." 
				 	and not exists (select 1 from controleemissaoitem e where e.idcontroleemissao = ".$idcontroleemissao." and e.idobjeto=v.idresultado and e.tipoobjeto='resultado') order by v.idregistro";
		}elseif(!empty($lote)){
			$_sql = "select v.idresultado,v.idregistro,v.exercicio,v.lote,v.nucleo,v.nome,v.descr,v.idpessoa,v.idnotafiscal 
                                from vwbuscaresultadopessoa v
					where v.idpessoa = ".$idcliente."
					and v.lote like ('%".$lote."%')".$strexercicio." 
				 	and not exists (select 1 from controleemissaoitem e where e.idcontroleemissao = ".$idcontroleemissao." and e.idobjeto=v.idresultado and e.tipoobjeto='resultado') order by v.idregistro";
		}else{
			echo("<br>");
		}
		
		//EXECUÇÃO DOS SELECTS
		if(!empty($_sql)){
			$res = d::b()->query($_sql) or die($_sql."Erro ao retornar resultado: ".mysql_error());
			$qtdrows= mysqli_num_rows($res);
		}
		
		
		$sql1="select e.idcontroleemissaoitem,a.idregistro,a.exercicio,a.lote,n.nucleo,p.nome,pr.descr
			from controleemissaoitem e,resultado r,pessoa p,prodserv pr,amostra a left join nucleo n on(n.idnucleo = a.idnucleo)
			where pr.idprodserv = r.idtipoteste 
			and p.idpessoa = a.idpessoa
			and a.idamostra =r.idamostra
			and r.idresultado = e.idobjeto
                        and e.tipoobjeto='resultado'
			and e.idcontroleemissao =".$idcontroleemissao;
			$res1=d::b()->query($sql1);
			$qtdrows1=mysqli_num_rows($res1);
			if($qtdrows1==0 and empty($_sql)){
				die();
			}
		
	?>
	<!-- ITEM -->
	
		<table id="tabx" style="min-width: 100%;" class="table table-striped planilha" >
			<tr bgcolor="#CFCFCF" id="tabx">				
				<th align="center">Registro</th>
				<th align="center">Exercicio</th>	
				<th align="center">Teste</th>
				<th align="center">Nucleo</th>							
				<th align="center">Lote</th>
				<th align="center">Cliente</th>
				<th align="center"></th>
			</tr>
	
	<?	
		
		if($qtdrows > 0){				
		$i=99977;
			$t=5;
			while($row = mysql_fetch_assoc($res)) {	
			$i=$i+1;	
			$t=$t+1;
				
	?>	
			<tr >				
                            <td ><?=$row["idregistro"]?></td>
                            <td ><?=$row["exercicio"]?></td>
                            <td ><?=$row["descr"]?></td>								    	
                            <td><?=$row["nucleo"]?></td>
                            <td ><?=$row["lote"]?></td>
                            <td><?=$row["nome"]?></td>
                            <td align="center">
                                <i class='fa fa-check-circle-o vermelho hoververmelho pointer btn-lg'id="item<?=$row["idresultado"]?>" onclick="inserir(<?=$row["idresultado"]?>);" tabindex=<?=$t?>> </i>
                            </td>	
			</tr>
	<?	
		
			}
		}elseif(!empty($_sql)){
	?>		
			<tr>
                            <td colspan="3"><?="Não há registros para adicionar com estas opções."?></td>
			</tr>
	<?		
		}
	
	?>

	
<?

				while($row=mysql_fetch_assoc($res1)){
?>	
				 <tr  class="respreto" >
					<td><?=$row["idregistro"]?></td>
					<td><?=$row["exercicio"]?></td>
					<td><?=$row["descr"]?></td>
					<td><?=$row["nucleo"]?></td>
					<td><?=$row["lote"]?></td>
					<td><?=$row["nome"]?></td>
<?
					//if($»1»u»email»status=="AGUARDANDO"){
?>					
					<td align="center">				 
				 		<i class='fa fa-check-circle-o verde hoververde pointer btn-lg' id="item<?=$row["idcontroleemissaoitem"]?>" onclick="retirar(<?=$row["idcontroleemissaoitem"]?>);" tabindex=<?=$t?>> </i>
				 	</td>
<?	
					//}
?>				 	
				</tr>
<?
				}
?>			
			</table>
		

<?
	}	
?>