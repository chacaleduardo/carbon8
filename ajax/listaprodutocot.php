<?

require_once("../inc/php/functions.php");


$nome= $_GET['nome'];
$descri= $_GET['descri'];
$idcotacao= $_GET['idcotacao'];
$idpessoa= $_GET['idpessoa'];//id do fornecedor]
$Vidtipoprodserv=$_GET["idtipoprodserv"];
$Vidcontaitem=$_GET["idcontaitem"];
$mostrarTodos = $_GET["mostrarTodos"];

require_once("../model/prodserv.php");

//Chama a Classe prodserv
$prodservclass = new PRODSERV();
        
$_idempresa = isset($_GET["_idempresa"])?$_GET["_idempresa"]:$_SESSION["SESSAO"]["IDEMPRESA"];

if(empty($descri) and empty($idpessoa)  and empty($Vidtipoprodserv) and empty($Vidcontaitem)){
	die("Favor preencher um dos campos para realizar a busca");
}
        
$tiponf= traduzid('cotacao', 'idcotacao', 'tiponf', $idcotacao);

IF($tiponf=='S'){
	$idtipoprodserv = '';
	$tipoprodserv='SERVICO';
}else{
	if(empty($Vidtipoprodserv)){
		$idtipoprodserv = "and ps.idtipoprodserv <> ''";
		if (!empty($Vidcontaitem)) {
			$idtipoprodserv = "and ci.idcontaitem =".$Vidcontaitem;
		}
	}else{
			$idtipoprodserv = "and ps.idtipoprodserv =".$Vidtipoprodserv;
	}
	
	$tipoprodserv='PRODUTO';
}
        
if($idpessoa){
	$nomecli=' - <label class="alert-warning">'.traduzid('pessoa', 'idpessoa', 'nome', $idpessoa).'</label>';
}elseif($Vidtipoprodserv){
	$nomecli=' - <label class="alert-warning">'.traduzid('tipoprodserv', 'idtipoprodserv', 'tipoprodserv', $Vidtipoprodserv).'</label>';
}else{
	$nomecli='';
}

 
if(!empty($descri)){
	$cwhere = " where 1 ".getidempresa('ps.idempresa','')."
			and ps.descr like('%".$descri."%')
			and ps.comprado = 'Y' 
			and ps.tipo ='PRODUTO'
			
							".$idtipoprodserv."                         
			and exists (select 1
											from prodservforn psf
											where  ps.idprodserv = psf.idprodserv
											and psf.idpessoa is not null
											and psf.status='ATIVO')
			and ps.status='ATIVO'";
	$cwhereservico=" where 1 ".getidempresa('ps.idempresa','')."
						and ps.descr like('%".$descri."%')
						and ps.comprado = 'Y' 
						and ps.tipo ='SERVICO'	
										".$idtipoprodserv."                         
						and exists (select 1
														from prodservforn psf
														where  ps.idprodserv = psf.idprodserv
														and psf.idpessoa is not null
														and psf.status='ATIVO')
						and ps.status='ATIVO'";
	
}elseif(empty($codprod) and empty($descri) and !empty($idpessoa)){
	$cwhere = " where  1 ".getidempresa('ps.idempresa','')."
							and ps.comprado = 'Y'
							and ps.tipo ='PRODUTO'
						
							".$idtipoprodserv." 
							and exists (select 1
											from prodservforn psf,pessoa p
											where  ps.idprodserv = psf.idprodserv
											and psf.idpessoa = p.idpessoa
											and p.idpessoa = ".$idpessoa."
					and psf.status='ATIVO')
							and ps.status='ATIVO'";

	$cwhereservico=" where  1 ".getidempresa('ps.idempresa','')."
							and ps.comprado = 'Y'
							and ps.tipo ='SERVICO'							
							".$idtipoprodserv." 
							and exists (select 1
											from prodservforn psf,pessoa p
											where  ps.idprodserv = psf.idprodserv
											and psf.idpessoa = p.idpessoa
											and p.idpessoa = ".$idpessoa."
					and psf.status='ATIVO')
							and ps.status='ATIVO'";
	
}elseif(empty($codprod) and empty($descri) and !empty($Vidtipoprodserv)){
	$cwhere = " where  1 ".getidempresa('ps.idempresa','')."
							and ps.comprado = 'Y'
							and ps.tipo ='PRODUTO'
							and ps.estmin > 0 
							".$idtipoprodserv."                                  
							and ps.status='ATIVO'";
	$cwhereservico= " where  1 ".getidempresa('ps.idempresa','')."
						and ps.comprado = 'Y'
						and ps.tipo ='SERVICO'				
						".$idtipoprodserv."                                  
						and ps.status='ATIVO'";
	
}else{
	if(empty($codprod) and empty($descri) and !empty($Vidcontaitem)){
		$cwhere = " join prodservcontaitem pc on(ps.idprodserv = pc.idprodserv) join contaitem ci on(ci.idcontaitem = pc.idcontaitem) 
		where  1 ".getidempresa('ps.idempresa','')."
								and ps.comprado = 'Y'
								and ps.tipo ='PRODUTO'
								and ps.estmin > 0 
								".$idtipoprodserv."                                  
								and ps.status='ATIVO'";
		$cwhereservico=" join prodservcontaitem pc on(ps.idprodserv = pc.idprodserv) join contaitem ci on(ci.idcontaitem = pc.idcontaitem) 
		where  1 ".getidempresa('ps.idempresa','')."
								and ps.comprado = 'Y'
								and ps.tipo= 'SERVICO'
								".$idtipoprodserv."                                  
								and ps.status='ATIVO'";
		
	}else {
		die("Parametros insuficientes para consulta");
	}
}
/*
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
 
 */
        
$_sql="SELECT * FROM (

SELECT  concat(e.sigla,' - ',ps.descr) as descrnova,
		ps.* , ( select count(*) 
				from nf f,prodservforn p 
				where f.idobjetosolipor =".$idcotacao."
									and f.tipoobjetosolipor='cotacao'
				and f.idpessoa = p.idpessoa
				and p.idprodserv = ps.idprodserv
			) as existegrupoforn,
			(select  sum(ifnull(f.qtd,0)) from lote l join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL' and f.qtd>0)
			join unidade u on(f.idunidade=u.idunidade and u.idtipounidade=3 and u.status='ATIVO')
			where l.status in ('APROVADO','QUARENTENA')
			and l.idprodserv = ps.idprodserv
			) as qtddisp,
			(select sum(ifnull(f.qtd,0)) AS total 
			from lote l join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL' and f.qtd>0)
			join unidade u on(f.idunidade=u.idunidade and u.idtipounidade=3 and u.status='ATIVO')
			where l.status = 'APROVADO'                                         
			and l.idprodserv = ps.idprodserv
			) as total,
			(select ifnull(sum(q.qtdprod),0) from lote q where q.idprodserv = ps.idprodserv and q.status='QUARENTENA') as quar,
			CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.qtdest, 0.00), IFNULL(psf.qtdest, 0.00)) AS DECIMAL (10 , 2 )) AS vwqtdest,
			CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.estmin, 0.00), IFNULL(psf.estmin, 0.00)) AS DECIMAL (10 , 2 )) AS vwestmin,
			CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.destoque, 0.00), IFNULL(psf.destoque, 0.00)) AS DECIMAL (10 , 2 )) AS vwdestoque,
			CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.mediadiaria, 0.00), IFNULL(psf.mediadiaria, 0.00)) AS DECIMAL (10 , 2 )) AS vwmediadiaria,
			CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.tempocompra, 0.00), IFNULL(psf.tempocompra, 0.00)) AS DECIMAL (10 , 2 )) AS vwtempocompra,
			(SELECT CAST(((vwmediadiaria * vwtempocompra) + (vwestmin - vwqtdest)) AS DECIMAL (10 , 2 )) AS sugestao_compra) AS vwsugestaocompra,
			ifnull((SELECT 
					criadoem
				FROM
					prodcomprar
				WHERE
					status = 'ATIVO'
						AND idprodserv = ps.idprodserv),sysdate()) AS ultimoconsumo
		FROM prodserv ps LEFT JOIN prodservformula psf ON psf.idprodserv = ps.idprodserv AND psf.status != 'INATIVO'
		join empresa e on e.idempresa = ps.idempresa JOIN prodservcontaitem pi ON pi.idprodserv = ps.idprodserv
		JOIN objetovinculo ov ON ov.idobjeto = '$idcotacao' AND ov.tipoobjeto = 'cotacao' AND ov.idobjetovinc = pi.idcontaitem AND ov.tipoobjetovinc = 'contaitem'
		".$cwhere."	
	union
		SELECT  concat(e.sigla,' - ',ps.descr) as descrnova,
			ps.* , ( select count(*) 
					from nf f,prodservforn p 
					where f.idobjetosolipor =".$idcotacao."
										and f.tipoobjetosolipor='cotacao'
					and f.idpessoa = p.idpessoa
					and p.idprodserv = ps.idprodserv
				) as existegrupoforn,
				(select  sum(ifnull(f.qtd,0)) from lote l join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL' and f.qtd>0)
				join unidade u on(f.idunidade=u.idunidade and u.idtipounidade=3 and u.status='ATIVO')
				where l.status in ('APROVADO','QUARENTENA')
				and l.idprodserv = ps.idprodserv
				) as qtddisp,
				(select sum(ifnull(f.qtd,0)) AS total 
				from lote l join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL' and f.qtd>0)
				join unidade u on(f.idunidade=u.idunidade and u.idtipounidade=3 and u.status='ATIVO')
				where l.status = 'APROVADO'                                         
				and l.idprodserv = ps.idprodserv
				) as total,
				(select ifnull(sum(q.qtdprod),0) from lote q where q.idprodserv = ps.idprodserv and q.status='QUARENTENA') as quar,
				CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.qtdest, 0.00), IFNULL(psf.qtdest, 0.00)) AS DECIMAL (10 , 2 )) AS vwqtdest,
				CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.estmin, 0.00), IFNULL(psf.estmin, 0.00)) AS DECIMAL (10 , 2 )) AS vwestmin,
				CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.destoque, 0.00), IFNULL(psf.destoque, 0.00)) AS DECIMAL (10 , 2 )) AS vwdestoque,
				CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.mediadiaria, 0.00), IFNULL(psf.mediadiaria, 0.00)) AS DECIMAL (10 , 2 )) AS vwmediadiaria,
				CAST(IF((psf.idprodservformula IS NULL), IFNULL(ps.tempocompra, 0.00), IFNULL(psf.tempocompra, 0.00)) AS DECIMAL (10 , 2 )) AS vwtempocompra,
				(SELECT CAST(((vwmediadiaria * vwtempocompra) + (vwestmin - vwqtdest)) AS DECIMAL (10 , 2 )) AS sugestao_compra) AS vwsugestaocompra,
				ifnull((SELECT 
						criadoem
					FROM
						prodcomprar
					WHERE
						status = 'ATIVO'
							AND idprodserv = ps.idprodserv),sysdate()) AS ultimoconsumo
			FROM prodserv ps LEFT JOIN prodservformula psf ON psf.idprodserv = ps.idprodserv AND psf.status != 'INATIVO'
			join empresa e on e.idempresa = ps.idempresa JOIN prodservcontaitem pi ON pi.idprodserv = ps.idprodserv
			JOIN objetovinculo ov ON ov.idobjeto = '$idcotacao' AND ov.tipoobjeto = 'cotacao' AND ov.idobjetovinc = pi.idcontaitem AND ov.tipoobjetovinc = 'contaitem'
			".$cwhereservico."				 
		) AS u
		order by u.descrnova";
//die($_sql);
	
echo "<!--".$_sql."-->";
$res = d::b()->query($_sql) or die("Erro ao retornar produto: ".mysqli_error(d::b()).$_sql);
$qtdrows1= mysqli_num_rows($res);
?>
<!-- ITEM -->

<style>
.tMain table {
  border-collapse: collapse;
  table-layout: fixed;
  width: 100%;
}
.tMain th,td {
  padding: 8px;
  text-align: left;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  text-align: center;
  font-size: 11px;
}

.tMain {
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.tMain .tMain_head{
	overflow-y: scroll;
}

.tMain .tMain_body{
	height: 300px;
	overflow-y: scroll;
}

.tMain_col_un{
	width: 5%;
}

.tMain_col_sigla{
	text-align: left;
	width: 5%;
}
.tMain_col_descr{
	text-align: left;
	width: 20%;
}

th.tMain_col_descr, th.tMain_col_sigla{
	text-align: left;
}

/* Mudando scroll do modal */
#cbModal ::-webkit-scrollbar {
  width: 3px;
}
#cbModal ::-webkit-scrollbar-track {
  background: #f1f1f1; 
}
#cbModal ::-webkit-scrollbar-thumb {
  background: #888; 
}
#cbModal ::-webkit-scrollbar-thumb:hover {
  background: #555; 
}
</style>

<div class="row">
    <div class="col-md-12">
		<div class="panel panel-default">  
			<div class="panel-heading">ITEN(S) CADASTRADOS <?=$nomecli?>
				<span style="float: right;">
					<span class="fa fa-eye-slash cinza" title="Mostrar Todos" id="mostrartodos" onclick="mostrarTodos()" cbcol="ocultar" cbid="N" style="font-size: 15px;font-weight: bold; cursor:pointer; padding: 0px 20px 0px 16px;"></span>
				</span>
			</div>
			<div class="panel-body">
				<?
				if($qtdrows1 > 0){?>
				<div class="tMain">
					<div class="tMain_head">
						<table>
							<thead>
								<tr bgcolor="#CFCFCF">
									<th title="Quantidade">Qtd</th>
									<th title="Unidade" class="tMain_col_un">Un</th>
									<th title="Sigla" class="tMain_col_sigla">Sigla</th>
									<th title="Descrição" class="tMain_col_descr">Descrição</th>
									<th title="Estoque">Estoque</th>	
									<th title="Estoque Mínimo">Est. Mín.</th>
									<th title="Mínimo Automático">Mín. Aut.</th>	
									<!-- th title="Pedido Automático">Ped. Aut.</th -->
									<th title="Sugestão de Compra">Sug. Compra</th>
									<th title="Sugestão de Compra 2">Sug. Compra 2</th>
									<th title="Quantidade Solicitada">Qtd. Sol.</th>
									<th title="Dias em Estoque">Dias Estoque</th>
									<th title="Data de Entrada">Data Entrada</th>
									<th title="Status do Orçamento">Status Orçamento</th>
								</tr>
							</thead>
						</table>
					</div>
					<div class="tMain_body">
						<table>
							<tbody>
								<?
								$j=99977;
								$t=0;
								while($row = mysqli_fetch_array($res)) { 

									$sqlcot="select distinct(c.idcotacao) as idcotacao,c.prazo,c.status as statusorc,n.status as statuscot,i.qtd
										from
										nfitem i 
										join  nf n on(														
												n.tipoobjetosolipor = 'cotacao'
												".getidempresa('n.idempresa','pedido')."									
												and n.tiponf !='V'
												and n.status  in('ABERTO','APROVADO','ENVIADO','INICIO','RECEBIDO','RESPONDIDO','AUTORIZADO', 'AUTORIZADA', 'APROVADO')
											)												
									join  cotacao c on( n.idobjetosolipor = c.idcotacao )
										where i.idprodserv =".$row["idprodserv"]."
										and i.idnf = n.idnf
										and i.nfe='Y'";	
									$rescot=d::b()->query($sqlcot) or die("Erro ao buscar cotação existente sql".$sqlcot);
									$rowcot=mysqli_fetch_assoc($rescot);
									$quarentena='N';
				
									$j=$j+1;
									$i=$j;
									$t=$t+1;
						
									if(empty($rowcot['idcotacao']) AND $row['estmin']>0 and  (($row['total']+$row['quar']<= $row['estmin']) or($row['pedido_automatico'] > $row['pedidoautomatico']))){
										$cortr='#FF8491';//vermelho
									}else{
										if($rowcot["statuscot"]=="ABERTO" OR $rowcot["statuscot"]=="PREVISAO"  OR $rowcot["statuscot"]=="INICIO" OR $rowcot["statuscot"]=="ENVIADO" OR $rowcot["statuscot"]=="RESPONDIDO"){
											$cortr="#FF8C00";//laranja
										}elseif($rowcot['statusorc']=='CONCLUIDO' or $rowcot['statuscot']=='APROVADO'){
											$cortr="#FFFFFF";//branco
										}elseif($row['total'] > $row['estmin'] or $row['estmin']<1){
											$cortr='#90EE90';//verde
										}elseif($row['total']+$row['quar']> $row['estmin']){
											$cortr='#9DDBFF';//azul
											$quarentena='Y';
										}else{            
											$cortr='yellow';//amarelo
										}	
									}
				
									//NÃO MUDAR A ESTRUTURA TR E TD ABAIXO POR CAUSA DA FUNÇÃO INSERIR NA PAGINA COTACAO
									if($mostrarTodos == 'N' && $row["estmin"] == 0)
									{
										$hiddenEscondeMinimo = 'hidden';
										$classEscondeMinimo = 'mostrarEstMin';
									} else {
										$hiddenEscondeMinimo = '';
										$classEscondeMinimo = '';
									}

									?>	

									<tr  bgcolor="<?=$cortr?>" class="<?=$classEscondeMinimo?>" <?=$hiddenEscondeMinimo?>>
										<td title="<?=$row["idprodserv"]?>">
											<input name="_<?=$i?>_i_nfitem_qtd" type="number" onkeypress="inserir(event.keyCode,this);"   class="size7" tabindex=<?=$t?> value="">
											<input name="_<?=$i?>_i_nfitem_idprodserv" type="hidden" value="<?=$row["idprodserv"]?>">
										</td>
										<td title="<?=$row["un"]?>" class="tMain_col_un">
											<?=$row['un']?>
										</td>
										<td title="<?=$row["codprodserv"]?>" class="tMain_col_sigla">
											<?=$row["codprodserv"]?>
										</td>
										<td title="<?=$row["descr"]?>" class="tMain_col_descr">
											<a onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row["idprodserv"]?>')" ><font color="Blue" style="font-weight: bold; cursor:pointer"><?=$row["descr"]?></font></a>
										</td>
										<td title="<?=$row["vwqtdest"]?>">
											<?=number_format(tratanumero($row["vwqtdest"]), 2, ',', '.')?>
										</td>
										<td title="<?=$row["vwestmin"]?>">
											<?=number_format(tratanumero($row["vwestmin"]), 2, ',', '.')?>
										</td>				
										<td title="<?=$row["estminautomatico"]?>">
											<?=$row["estminautomatico"]?>
											<?
											if($row["existegrupoforn"]==0){?>
												
											<?}?>					
										</td>  
										<?if($row['pedidoautomatico']>=$row['pedido_automatico']){
												$pedidoauto = round($row['pedidoautomatico'],2);
											}else{
												$pedidoauto = round($row['pedido_automatico'],2);
											}?>
										<!-- td title="<?=$pedidoauto?>">
											<?=$pedidoauto?>
										</td -->
										<?
										$mediadiaria=$prodservclass->getMediadiaria($row['idprodserv'],$row['idunidadeest']);
										$sugcp=tratanumerovisualizacao(($mediadiaria * $row['tempocompra'] ) + ($row["estmin"] - $row['qtddisp']));
										?>
										<td title="<?=$row['vwsugestaocompra']?>">
											<?=$row['vwsugestaocompra']?>
										</td>
										<?
											$sql_pe="SELECT ifnull(sum(i.qtd * if(pf.valconv>0, pf.valconv,1)),0) as qtdpa		
														FROM nfitem i JOIN nf n ON n.idnf=i.idnf AND n.tiponf ='C'  and i.nfe= 'Y' AND status  IN('APROVADO')
													LEFT JOIN prodservforn pf ON pf.idprodservforn=i.idprodservforn
                                            		WHERE nfe='Y' AND i.idprodserv = ".$row["idprodserv"];         
												           

											$_respe = d::b()->query($sql_pe) or die("Erro ao consultar pedidos em andamento do produto:".mysqli_error(d::b()));
											$_rowpa = mysqli_fetch_assoc($_respe);
											?>
										<td title="<?=$sugcp?>-<?=$_rowpa['qtdpa']?>">
											<a class='pointer' title='Copiar para quantidade' onclick="preencheval(<?=$i?>,'<?=tratanumero($row['vwsugestaocompra']-$_rowpa['qtdpa'])?>')"><?=number_format(round(tratanumero($row['vwsugestaocompra']-$_rowpa['qtdpa'])), 2, ',', '.'); ?></a>
										</td>
										<td title="<?=$rowcot["qtd"]?>"><?=$rowcot["qtd"]?></td>
										<?
										if($mediadiaria>0 and $row["qtddisp"]>0 ){
											$diasestoque=number_format(tratanumero($row["qtddisp"]/$mediadiaria), 2, ',', '.');
										}else{
											$diasestoque='0,00';	
										}?>
										<td title="<?=$diasestoque?>">
											<?=$diasestoque?>
										</td>
										<td title="<?=dma($row['ultimoconsumo'])?>">
											<?=dma($row['ultimoconsumo'])?>
										</td>
										<?
										$rotulo = getStatusFluxo('cotacao','idcotacao', $rowcot["idcotacao"]);
										?>
										<td title="<?=$rowcot["idcotacao"]?> - <?=$rotulo['rotulo']?>">
											<a onclick="janelamodal('?_modulo=cotacao&_acao=u&idcotacao=<?=$rowcot["idcotacao"]?>')" ><font color="Blue" style="font-weight: bold; cursor:pointer"><?=$rowcot["idcotacao"]?> - <?=$rotulo['rotulo']?></font></a>
										</td>
									</tr>
								<?}?>
							</tbody>
						</table>
					</div>
				</div>
				<?}else{?>
					<table>
						<tr>
							<td>
								<label class="alert-warning">Não Encontrado!!! <br><p> É possível que o produto pesquisado, não possua um fornecedor configurado ou não possua relação com a Categoria configurada nesta Cotação.</label>
							</td>
						</tr>
					</table>	
				<?}?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
		<?if(!empty($idpessoa)){?>
			<div class="panel panel-default">  
				<div class="panel-heading">ITEN(S) DESCRITIVO(S)<?=$nomecli?></div>
				<div class="panel-body">
					<input name="_idpessoa_fixo" type="hidden" value="<?=$idpessoa?>">
					<table id="tbItens" style="width: 100%">	
						<tr bgcolor="#CFCFCF">
							<th align="center">Qtd</th>
							<th align="center">Descrição</th>
							<th></th>
						</tr>
					</table>
					<div class="row"> 
						<div class="col-md-1 col-md-offset-0">
							<i id="novoitem" class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="novoItem()" title="Inserir novo Item"></i>
						</div>	 
					</div>
				</div>
			</div>
		<?}?>
		<table class="hidden" id="modeloNovoIten">
			<tr class='dragExcluir nfitem'>
				<td style="text-align: left;width:10%;">
					<input type="hidden" name="#nameidnfitem">
					<input style=" border: 1px solid silver;" name="#namequantidade" title="Qtd" onkeypress="inserir(event.keyCode,this);"  placeholder="Qtd" type="text" >	
				</td>
				<td>
					<input type="text" name="#nameidprodserv" class="idprodserv"  onkeypress="inserir(event.keyCode,this);" placeholder="Informe o produto" >
				</td>
				<td>
					<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable" onclick="excluiritemtemp(this)" alt="Excluir !"></i>
				</td>
			</tr>
		</table>
    </div>
</div>
<script>
	CB.montaLegenda({
		"#90EE90": "Produto acima estoque mínimo.", 
		"#FF8491": "Produto estoque mínimo sem orçamento.",
		"#FF8C00": "Produto estoque mínimo com orçamento em Andamento.",
		"#FFFFFF": "Produto estoque mínimo com orçamento Concluido.",
		"#9DDBFF": "Produto recebido mas em Quarentena.",
		"#FFFF00": "Erro: Favor comunicar setor TI.",
	});
	CB.oPanelLegenda.css( "zIndex", 1100);

	function mostrarTodos()
	{
		if($('#mostrartodos').attr('cbid') == 'N')
		{
			$(`.mostrarEstMin`).removeAttr('hidden');
			$('#mostrartodos').attr('cbid', 'Y'); 
			$('#mostrartodos').addClass("verde");
    		$('#mostrartodos').removeClass("cinza");
		} else {
			$(`.mostrarEstMin`).attr('hidden', 'hidden');
			$('#mostrartodos').attr('cbid', 'N'); 
			$('#mostrartodos').addClass("cinza");
    		$('#mostrartodos').removeClass("verde");
		}
	}
</script>
<!-- FIM ITEM -->

