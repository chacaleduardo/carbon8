<?
require_once("../inc/php/functions.php");
require_once("../model/nf.php");

$nfclass= new NF();
$idnfitem= $_GET['idnfitem'];
if(!empty($idnfitem)){?>
    <?=$nfclass->listanfitem($idnfitem);?>
    <?
}else{

    
			$sqlx="select 'nfitem' as tipo,dt.idpessoa,i.qtd,ifnull(i.un,pr.un) as un,i.idnfitem as idtipo,r.idrateio,
			r.idrateioitem,dt.idrateioitemdest,n.idnf,n.nnfe,dt.idobjeto,dt.tipoobjeto,i.idtipoprodserv,
			tp.tipoprodserv,ifnull(pr.descr,i.prodservdescr) as descr,
			round(((i.total+ifnull(i.valipi,0)+ifnull(n.frete,0))*(dt.valor/100))/ifnull(n.parcelas,1),2) as rateio,
			dt.valor,e.unidade as empresa,n.dtemissao,
			GROUP_CONCAT(rdn.idrateioitemdestnf,'@',dt.idrateioitemdest) as idrateioitemdestnf,dt.status
            from  rateioitem r 	
                join nfitem i on(r.tipoobjeto='nfitem' and r.idobjeto =i.idnfitem ) 
                join nf n on(i.idnf=n.idnf )
                join rateioitemdest dt on(dt.idrateioitem=r.idrateioitem and dt.tipoobjeto = 'unidade')
                join unidade e on(e.idunidade=dt.idobjeto )                     
                left join prodserv pr on(pr.idprodserv=i.idprodserv )
                left join tipoprodserv tp on(tp.idtipoprodserv = i.idtipoprodserv)
				LEFT JOIN rateioitemdestnf rdn ON(dt.idrateioitemdest=rdn.idrateioitemdest)
            where n.tiponf in ('T','S','M','E','R','D','B','C')
            and dt.idrateioitemdest != ".$_GET['idrateioitemdest']."
            and r.idrateioitem=".$_GET['idrateioitem']."	
            and dt.valor>0			
           -- and n.status='CONCLUIDO' 
		   GROUP BY i.idnfitem , dt.idrateioitemdest  
        order by tipoobjeto,empresa,idobjeto,tipoprodserv,descr";

                echo "<!--";
                echo $sqlx;
                echo "-->";

                $resx =  d::b()->query($sqlx) or die("Falha ao pesquisar itens relacionados: " . mysqli_error() . "<p>SQL: $sqlx");
                $qtdx=mysqli_num_rows($resx);

				$i=9911;
					while($rowx=mysqli_fetch_assoc($resx)){
						$i=$i+1;
							$total=$total+$rowx['rateio'];
							
							if ($rowx['tipoobjeto'] == 'unidade') {										
								$rateiostr=$rowx["empresa"];
							} else {
								$rateiostr="Sem Rateio";
							}


						?>
						<div class="col-md-12 row rowitem2" title="Rateio relacionado ao rateio selecionado"  >
							  
							<div class="col-md-6 inputcheckbox2">    								         
								<div class="col-md-1">
								<?if(!empty($rowx['idrateioitemdestnf']) and $rowx['status']=='COBRADO'){?>
										<i class="fa fa-money verde pointer" title="Rateio em Cobrança" onclick="editardestnf(<?=$rowx['idrateioitemdest']?>,'<?=$rowx['descr']?>','<?=$rateiostr?>')" ></i>										
										<div class="hide" id="destnf<?=$rowx['idrateioitemdest']?>">
											<table class="table table-striped planilha">
												<tr>
													<th>Cobrança %</th>
													<th>Valor R$</th>
													<th>Nome</th>
													<th>Status</th>
												</tr>
												<?
												$cobranca=RateioItemDestController::listarRateioitemdestnfPorIdrateioitemdest($rowx['idrateioitemdest']);
												$totalrt=0;
												$deletar='Y';
												foreach($cobranca as $linha) {
													$totalrt=$totalrt+$linha['valor'];
													$rotulo = getStatusFluxo('nf', 'idnf', $linha['idnf']);
													if( $linha['status'] != 'INICIO' ){
														$deletar='N';
													}
												?>
												<tr>
													<td><?=number_format(tratanumero($linha['rateio']), 2, ',', '.');?></td>
													<td><?=number_format(tratanumero($linha['valor']), 2, ',', '.');?></td>
													<td><?=$linha['nome']?></td>
													<td>
														<a target="_blank" href="?_modulo=nfentrada&_acao=u&idnf=<?=$linha['idnf']?>"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?></a>
													</td>
												</tr>
												<?
												}
												?>
												<tr>
													<th>Total</th>
													<th><?=number_format(tratanumero($totalrt), 2, ',', '.'); ?></th>
													<th></th>
													<th style="text-align-last: center;" >
													<?if($deletar=='Y'){?>
														<i title="Excluir os Itens" class="fa fa-trash vermelho hoverpreto pointer" onclick="excluirdestnf('<?=$rowx['idrateioitemdestnf']?>')"></i>
													<?}?>
													</th>
												</tr>
											</table>
										</div>
										<?}elseif($rowx['status']=='PENDENTE' or  empty($rowx['status'])){?>
									<input type="checkbox"  class="changeacao" acao="i" atname="checked[<?=$i?>]"  value="<?=$rowx['idrateioitemdest']?>"  style="border:0px">
									<input class="rateioitem" name="_<?=$i?>_u_rateioitemdest_idrateioitemdest" type="hidden" value="<?=$rowx['idrateioitemdest']?>">   
										<?}elseif($rowx['status']=="EDITADO"){
											echo($rowx['EDITADO']);
										}else{
										?>
										<span title="Por: <?=$rowx['alteradopor']?> Em:  <?=dmahms($rowx['alteradoem'])?>">
											<?=$rowx['status']?>
										</span>	
										<?}?> 
								</div>
								<div class="col-md-2"><?=$rowx['qtd']?></div>
								<div class="col-md-2"><?=$rowx['un']?></div>
								<div class="col-md-7"><?=$rowx['descr']?> </div> 
							</div>
							<div class="col-md-1 " style="text-align: right;">
						
							<?if(!empty($rowx['idnf'])){?>
								<a  class="hoverazul pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$rowx['idnf']?>')" title="Compra">
								<?=number_format(tratanumero($rowx['rateio']), 2, ',', '.');?>
								</a>
							<?}else{?>
								<?=number_format(tratanumero($rowx['rateio']), 2, ',', '.');?>
							<?}?>
						
							</div>
							<div class="col-md-2" >
							<?
							if($rowx['tipoobjeto']=='unidade'){
								echo $rowx['empresa'];
								//listaunidade($rowx);
							}else{
								echo("Sem rateio");
							}
							?>
							</div>
							<div class="col-md-1" style="text-align: right;">                
									<?=$rowx['valor']?>%    
							</div> 
							<div class="col-md-1 nowrap" style="text-align: right;">  
							<?/*if($rowx['tipo']=='lotecons'){?>				 				
								<div id="consumolote_<?=$rowx['idrateioitemdest']?>" style="display: none">
									<?=$prodservclass->listalotecons($rowx['idtipo']);?>
								</div>
								<a title="Histórico" class="fa fa-search fa-1x  hoverazul  pointer" onclick="showhistoricolote(<?=$rowx['idrateioitemdest']?>);">  Consumo</a>
							<?}else
							*/
							if($rowx['tipo']=='nfitem'){?>
								<!--div id="consumolote_<?=$rowx['idrateioitemdest']?>" style="display: none">
									<?//=$nfclass->listanfitem($rowx['idtipo']);
									?>
								</div-->
								<a title="Compra" class="fa fa-search fa-1x  hoverazul  pointer" onclick="showhistoricoitem(<?=$rowx['idtipo']?>);"> </a>
					
							<?}?>
							</div> 
						</div>      
					<?
							//$vtipo=$vtipo+$row['rateio'];
						}//while($rowx=mysqli_fetch_assoc($resx)){

}
?>
