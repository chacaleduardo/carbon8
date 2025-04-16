<?
require_once("../inc/php/functions.php");

class NF
{   
    // retorna o historico de um consumo especifico
    function listanfitem($idnfitem){

            $s="select n.idnf,dmahms(n.dtemissao) as emissao,n.tiponf,ifnull(i.un,p.un) as un ,ifnull(n.nnfe,n.idnf) as nnfe,ifnull(i.prodservdescr,p.descr) as descr ,i.qtd,i.total,n.criadopor,n.criadoem,n.alteradopor,n.alteradoem
             from nfitem i join nf n on(n.idnf=i.idnf)
            left join prodserv p on(p.idprodserv=i.idprodserv)
            left join rateioitem ri on(ri.idobjeto = i.idnfitem)
            left join rateioitemdest rd on(rd.idrateioitem = ri.idrateioitem)
            where i.idnfitem= ".$idnfitem." group by i.idnfitem";
            $r = d::b()->query($s) or die(" listanfitem - A consulta da nota falhou!!! : ". mysqli_error() . "<p>SQL: $s");
            $rw=mysqli_fetch_assoc($r);
            $link="nfentrada";
            $vtiponf = "Compra";
            if($rw["tiponf"]=='V'){ $vtiponf = "Venda";  $link="pedido";}
			if($rw["tiponf"]=='C'){ $vtiponf = "Compra"; $link="nfentrada";}	
            if($rw["tiponf"]=='O'){ $vtiponf = "Compra"; $link="nfentrada";}		
			if($rw["tiponf"]=='S'){ $vtiponf = "Servi&ccedil;o";  $link="nfentrada";}
			if($rw["tiponf"]=='T'){ $vtiponf = "Cte";  $link="nfentrada";}
			if($rw["tiponf"]=='E'){ $vtiponf = "Consession&aacute;ria"; $link="nfentrada";}
            if($rw["tiponf"]=='M'){ $vtiponf = "Manual/Cupom"; $link="nfentrada";}
            if($rw["tiponf"]=='B'){ $vtiponf = "Recibo"; $link="nfentrada";}
            if($rw["tiponf"]=='R'){ $vtiponf = "PJ"; $link="comprasrh";}
			if($rw["tiponf"]=='F'){ $vtiponf = "Fatura"; $link="nfentrada"; $tipo='F';}
            if($rw["tiponf"]=='D'){ $vtiponf = "Sócios"; $link="comprassocios"; $tipo='D';}
     ?>
         <div class="row">
             <div class="col-md-12">
                 <div class="panel panel-default">
                 <div class="panel-heading"> <?=$vtiponf?>: 
                     <a  class="hoverazul pointer" onclick="janelamodal('?_modulo=<?=$link?>&_acao=u&idnf=<?=$rw['idnf']?>')" title="Numero NF">
                     <?
                     if(empty($rw['nnfe'])){
                        echo($rw['idnf']);
                     }else{
                        echo($rw['nnfe']);
                     }  
                     ?>
                     </a> &nbsp;&nbsp;&nbsp; Emissão: <?=$rw['emissao']?>
                 </div>
                 <div class="panel-body" >		                           
                 <table class="table table-striped planilha" >
     
                        <tr> 		
                            <th>Quantidade</th>
                            <th>Unidade</th>			
                            <th>Descrição</th>
                            <th style="text-align: right !important;">Valor R$</th>
                        </tr> 
                        <tr>
                            <td><?=$rw['qtd']?></td>
                            <td><?=$rw['un']?></td> 
                            <td><?=$rw['descr']?></td>                        
                            <td align="right"><?=$rw['total']?></td>
                     </tr>
 
                 </table>
                 </div>
                 </div>
             </div>
         </div>
        
        <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">		
                        <div class="panel-body" style="padding-top: 8px !important;background:#e6e6e6">
                            <div class="row col-md-6">		
                                <div class="col-md-6" style="text-align:right">
                                    <span style="padding: 6px; text-transform: uppercase; font-size: 11px;">
                                        Criação:
                                    </span>
                                </div>     
                                <div class="col-md-6" style="text-align:left">
                                    <span style="border: 1px solid #ddd; background: #e1e1e1; padding: 6px; text-transform: uppercase; font-size: 11px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                                    <?=$rw['criadopor']?>	
                                    </span> 
                                    <span style="text-transform:uppercase;border: 1px solid #ddd; background: #e1e1e1; padding: 6px; font-size: 11px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                                    <?=dmahms($rw['criadoem'])?>
                                    </span>
                                </div>
                            </div>
                            <div class="row col-md-6">            
                                <div class="col-md-6" style="text-align:right">
                                <span style="padding: 6px; text-transform: uppercase; font-size: 11px;">
                                        Alteração:
                                    </span>
                                </div>     
                                <div class="col-md-6" style="text-align:left">
                                    <span style="border: 1px solid #ddd; background: #e1e1e1; padding: 6px; text-transform: uppercase; font-size: 11px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                                    <?=$rw['alteradopor']?>		
                                        </span> 
                                        <span style="text-transform:uppercase;border: 1px solid #ddd; background: #e1e1e1; padding: 6px; font-size: 11px; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                                        <?=dmahms($rw['alteradoem'])?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

     <?  
         }
}
?>