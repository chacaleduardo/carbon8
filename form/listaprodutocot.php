<?

require_once("../inc/php/functions.php");


$nome= $_GET['nome'];
$descri= $_GET['descri'];
$idcotacao= $_GET['idcotacao'];


	if(empty($descri)  and empty($nome)){
		die("Favor preencher um dos campos para realizar a busca");
	}

 
	if(!empty($descri)){
		$cwhere = " where ps.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
				and ps.descr like('%".$descri."%')
				and ps.comprado = 'Y' 
				and exists (select 1
				    from prodservforn psf
				    where  ps.idprodserv = psf.idprodserv
					and psf.idpessoa is not null
				    	and psf.status='ATIVO')
				and ps.status='ATIVO'";
		
	}elseif(empty($codprod) and empty($descri) and !empty($nome)){
		$cwhere = " where  ps.idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"]."
		and ps.comprado = 'Y'
		and exists (select 1
					from prodservforn psf,pessoa p
					where  ps.idprodserv = psf.idprodserv
					and psf.idpessoa = p.idpessoa
					and p.nome like '%".$nome."%'
						and psf.status='ATIVO')
		and ps.status='ATIVO'";
		
	}
	else{
		die("Parametros insuficientes para consulta");
	}

	$_sql = "SELECT 
				ps.* , ( select count(*) 
					    from nf f,prodservforn p
					    where f.idobjetosolipor =".$idcotacao."
                                            and f.tipoobjetosolipor='cotacao'
					    and f.idpessoa = p.idpessoa
					    and p.idprodserv = ps.idprodserv
					) as existegrupoforn,
					(select sum(l.qtddisp) from lote l
					where l.status = 'APROVADO'
                                         and l.idunidade=8
					and l.idprodserv = ps.idprodserv
					) as qtddisp,
					(select sum(ifnull(l.qtddisp,0)) AS total from lote l
					where l.status = 'APROVADO'
                                         and l.idunidade=8
					and l.idprodserv = ps.idprodserv
					) as total,
					(select ifnull(sum(q.qtdprod),0) from lote q where q.idprodserv = ps.idprodserv and q.status='QUARENTENA') as quar
			FROM prodserv ps
			
		
			".$cwhere."
			
			order by ps.descr";
	//die($_sql);
	
		
		$res = d::b()->query($_sql) or die("Erro ao retornar produto: ".mysqli_error(d::b()).$_sql);
		$qtdrows1= mysqli_num_rows($res);
	
	
	?>
		<!-- ITEM -->
	
		<table>	
	<?	
		
		if($qtdrows1 > 0){	
	?>	
			<tr bgcolor="#CFCFCF">
				<td align="center">Qtd.</td>
				<td align="center">Descrição</td>
				<td align="center">Sigla</td>
				<td align="center">Estoque</td>	
				<td align="center">Min.</td>
				<td align="center">Ideal</td>	
				<td align="center">Max.</td>
				<td align="center">Qtd Sol</td>
				<td align="center">Orç</td>
			</tr>	
			
	<?	
			
			$j=99977;
			$t=0;
			while($row = mysqli_fetch_array($res)) {
				
				
				 $sqlcot="select distinct(c.idcotacao) as idcotacao,c.prazo,c.status as statusorc,n.status as statuscot,i.qtd
					from nfitem i, nf n,cotacao c
					where i.idprodserv =".$row["idprodserv"]."
					and i.idnf = n.idnf
					and n.idobjetosolipor = c.idcotacao 
					and n.tipoobjetosolipor = 'cotacao' 
					and n.status not in('PREVISAO','DIVERGENCIA','CONCLUIDO','CANCELADO','REPROVADO')";
				$rescot=d::b()->query($sqlcot) or die("Erro ao buscar cotação existente sql".$sqlcot);
				$rowcot=mysqli_fetch_assoc($rescot);
				$quarentena='N';
	
			$j=$j+1;
			$i=$j;
			$t=$t+1;
			
			if(empty($rowcot['idcotacao']) AND ($row['total']+$row['quar']<= $row['estmin'])){
				$cortr='#FF8491';//vermelho
			}else{
			if($rowcot["statuscot"]=="ABERTO" OR $rowcot["statuscot"]=="ENVIADO" OR $rowcot["statuscot"]=="RESPONDIDO"){
					$cortr="#FF8C00";//laranja
				}elseif($rowcot['statusorc']=='CONCLUIDA' or $rowcot['statuscot']=='APROVADO'){
					$cortr="#FFFFFF";//branco
				}elseif($row['total'] > $row['estmin']){
					$cortr='#90EE90';//verde
				}elseif($row['total']+$row['quar']> $row['estmin']){
					$cortr='#9DDBFF';//azul
					$quarentena='Y';
				}else{            
					$cortr='yellow';//amarelo
				}	
			}
			
			
			
						
//NÃO MUDAR A ESTRUTURA TR E TD ABAIXO POR CAUSA DA FUNÇÃO INSERIR NA PAGINA COTACAO
?>	
	
		<tr  bgcolor="<?=$cortr?>">
				<td align="center">
                                    <input name="_<?=$i?>_i_nfitem_qtd" type="text" onkeypress="inserir(event.keyCode,this);"   size ="6" tabindex=<?=$t?> value="">
                                    <input name="_<?=$i?>_i_nfitem_idprodserv" type="hidden" value="<?=$row["idprodserv"]?>">
				</td>
				<td align="left">
                                    <a onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row["idprodserv"]?>')" ><font color="Blue" style="font-weight: bold;"><?=$row["descr"]?></font></a>
				</td>
				<td align="center"><?=$row["codprodserv"]?></td>	
				<td align="center"><?=$row["qtddisp"]?></td>
				<td align="center"><?=$row["estmin"]?></td>				
				<td align="center"><?=$row["estideal"]?>
<?
if($row["existegrupoforn"]==0){
?>
                                   
<?
}
?>					
				</td>  
				<td align="center"><?=$row["estmax"]?></td>
				<td align="center"><?=$rowcot["qtd"]?></td>
				<td align="center">
					<a onclick="janelamodal('?_modulo=cotacao&_acao=u&idcotacao=<?=$rowcot["idcotacao"]?>')" ><font color="Blue" style="font-weight: bold;"><?=$rowcot["idcotacao"]?></font></a>
				</td>
			</tr>
	<?	
			}
		}else{
	?>		
			<tr>
			    <td> <label class="alert-warning">Não Encontrado!!! <br><p> É possível que o produto pesquisado, não possua um fornecedor configurado.</label></td>
			</tr>
	<?		
		}	
	?>
	<tr><td colspan="7" style="border:1px solid #eee;">
	<div style="background-color:#e6e6e6; padding:6px;">Legenda</div>
	<div id="cbPanelLegendaBody" class="panel-body collapse in" aria-expanded="true" style="">
	<ul class="cbLegenda">
		<li><i style="background-color:#90EE90;"></i>Produto acima estoque mínimo.</li>
	<li><i style="background-color:#FF8491;"></i>Produto estoque mínimo sem orçamento.</li>
	<li><i style="background-color:#FF8C00;"></i>Produto estoque mínimo com orçamento em Andamento.</li>
	<li><i style="background-color:#FFFFFF;"></i>Produto estoque mínimo com orçamento Concluido.</li>
	<li><i style="background-color:#9DDBFF;"></i>Produto recebido mas em Quarentena.</li>
	<li><i style="background-color:yellow;"></i>Erro: Favor comunicar setor TI</li>
	</ul>
	</div>

	
	</td></tr>
	</table>
	
<!-- FIM ITEM -->

