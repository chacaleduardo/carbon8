<?
require_once("../inc/php/functions.php");

$idnf= $_GET['idnf']; 

if(empty($idnf)){
	die("Identificação da nota não enviada");
}
//echo($idnf);
?>


    <?

	$sqlx="SELECT  
                   l2.partida as partida2,l2.exercicio as exercicio2,l2.idlote as idlote2,l.partida,l.exercicio,l.idlote,x.idnfitemxml,x.prodservdescr as descr,x.qtd,x.un,x.valor,x.des as desconto,x.vst,x.cst,x.cfop,x.aliqicms as aliq_icms,x.valicms as vicms,x.valipi as vipi,x.frete,x.basecalc,i.idnfitem,x.outro
                FROM nfitemxml x  join nfitem i on(i.idnfitemxml=x.idnfitemxml)
                left join lote l on(l.idnfitem=i.idnfitem)
                left join lotecons lc on(lc.idobjetoconsumoespec=i.idnfitem and lc.tipoobjetoconsumoespec='nfitem' and lc.qtdc>0)
				left join lote l2 on(l2.idlote=lc.idlote)  
                where x.status='Y' and x.idnf=".$idnf;
	$resx=d::b()->query($sqlx) or die("erro ao buscar itens do xml no banco de dados sql=".$sqlx);
	$qtdx=mysqli_num_rows($resx);
	//$i=1;
	if($qtdx>0){
?>	    
        <div class="row">
            <div class="col-md-12">
	    <div class="panel panel-default" >
	    <div class="panel-heading">Itens</div>
	    <div class="panel-body"  >	

	    <table  class="table table-striped planilha" id='itensdev' >	
		<tr >
		    <th>Lote</th>
		    <th>Produto</th>
		    <th style="text-align: right !important;">Qtd</th>
                    <th style="text-align: right !important;">Un</th>
                    <th style="text-align: right !important;">Valor Un</th>		    
		    <!--th style="text-align: right !important;">Desc</th>
		    <th style="text-align: right !important;">CFOP</th>
		    <th style="text-align: right !important;">BC</th>
		    <th style="text-align: right !important;">ICMS %</th>
		    <th style="text-align: right !important;">ICMS R$</th>
                    <th style="text-align: right !important;">IPI</th>
                    <th style="text-align: right !important;">Frete</th>
                    <th style="text-align: right !important;">Outras Desp</th-->
		    <th style="text-align: right !important;">Valor</th>
                    
		</tr>
	<?
	while($rowx=mysqli_fetch_assoc($resx)){
            $i = $i+1;
	?>	
		<tr class="respreto" >
            <td>
                <?if(!empty($rowx['idlote'])){?>
                <input title="Devolver?" type="checkbox" checked="" name="<?=$i?>_nfitemxml_idnfitemxml" idnfitemxml="<?=$rowx["idnfitemxml"];?>">
                <?=$rowx['partida']?>/ <?=$rowx['exercicio']?>
                <?}elseif(!empty($rowx['idlote2'])){?>
                <input title="Devolver?" type="checkbox" checked="" name="<?=$i?>_nfitemxml_idnfitemxml" idnfitemxml="<?=$rowx["idnfitemxml"];?>">
                <?=$rowx['partida2']?>/ <?=$rowx['exercicio2']?>
                <?}else{?>
                    <a title="É necessário criar o lote e vincular com o item do xml." class="fa fa-exclamation-triangle laranja btn-lg pointer" ></a>
                <?}?>
            </td>
		    <td>
                       
                        <?=$rowx['descr']?>
                    </td>
		    <td align="right">
                        <?=number_format(tratanumero($rowx['qtd']), 2, ',', '.'); ?>                        
                    </td>
                    <td  align="right">
                        <?=$rowx['un']?>                        
                    </td>
                    <td align="right">
                        <?=number_format(tratanumero($rowx['valor']/$rowx['qtd']), 2, ',', '.'); ?>
                    </td>		    
		    <!--td align="right"><?=number_format(tratanumero($rowx['desconto']), 2, ',', '.'); ?></td>		  
		    <td align="right"><?=$rowx['cfop']?></td>
		    <td align="right"><?=number_format(tratanumero($rowx['basecalc']), 2, ',', '.'); ?></td>
		    <td align="right"><?=number_format(tratanumero($rowx['aliq_icms']), 2, ',', '.'); ?></td>
		    <td align="right"><?=number_format(tratanumero($rowx['vicms']), 2, ',', '.'); ?></td>
                    <td align="right"><?=number_format(tratanumero($rowx['vipi']), 2, ',', '.'); ?></td>		   
		    <td align="right"><?=number_format(tratanumero($rowx['frete']), 2, ',', '.'); ?></td>
                    <td align="right"><?=number_format(tratanumero($rowx['outro']), 2, ',', '.'); ?></td-->
		    <td align="right">
                        <?=number_format(tratanumero($rowx['valor']), 2, ',', '.'); ?>
                    </td>
                </tr>
	<?}?>	
		
               
            
		</table>
<?
                    $sqle="  select e.idendereco,concat(e.endereco,'-',e.uf) as endereco,
                                        CASE
                                                                WHEN e.uf ='MG' THEN 'DENTRO'					
                                                                ELSE 'FORA'
                                                        END as destino
                                        from endereco e join nf n on(e.idpessoa=n.idpessoa and n.idnf=".$idnf.")
                                        where e.idtipoendereco = 2 
                                        and e.status='ATIVO' ;";
                    $rese=d::b()->query($sqle) or die("erro ao buscar informacoes do endereço sql=".$sqle);
                    $qtde=mysqli_num_rows($rese);
                    //$i=1;
                    if($qtde>0){
                        $rowe=mysqli_fetch_assoc($rese);
?>               
               
            <table>
                <tr>                                
                    <td nowrap align="right">Nat. Oper.:</td>
                    <td>
                        <select  name="_dev_i_nf_idnatop">
                            <option value=""></option>
                            <?fillselect("SELECT n.idnatop,concat ( n.natop,' - CFOP [',GROUP_CONCAT(c.cfop , ''),']') as natop
                            FROM natop n  join cfop c on(c.idnatop = n.idnatop   ".getidempresa('c.idempresa','natop')." and origem='".$rowe['destino']."' )
                            where n.status='ATIVO'
                            -- and n.finnfe=4
                            ".getidempresa('n.idempresa','natop')."
                            group by n.idnatop order by natop                    
                                    ");?>		
                        </select>

                    </td>        
                </tr>
                <tr>
		    <td align="right">A/C:</td>
                    <td class="nowrap">
                        <select  name="_dev_i_nf_idcontato" id="idcontato"  >
                        <option value=""></option>
                                <?fillselect("select 
                                            c.idcontato,nome			
                                            from pessoa p,pessoacontato c,nf n
                                            where p.status in ('ATIVO','PENDENTE')
                                            and  p.idpessoa = c.idcontato
                                          ".getidempresa('p.idempresa','pessoa')."
                                            and c.idpessoa = n.idpessoa
                                            and n.idnf=".$idnf." order by nome");?>		
                        </select> 		
                   
                       
		    </td> 
                </tr>
            </table>
                <?
                 }else{
                        echo "E necessário cadastrar o endereco de faturamento do fornecedor.";
                    }
                    ?>
                </div>
	    </div>
        </div>
        </div>
<?
                   
	}else{
        echo('É necessário relacionar os lotes gerados com os itens do XML.');
    }//if($qtdx>0){       
        
?>