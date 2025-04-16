<?
require_once("../inc/php/functions.php");
$inidlote = $_POST['inidlote']; 
$valorRatear = $_POST['valor']; 
$tipo = $_POST['tipo']; 

if($tipo=="QUANTIDADE"){

    $sqlQ="select sum(l.qtdprod) as valor, 'qtdprod' as campo   
            from lote l 
                join prodserv p on(p.idprodserv=l.idprodserv)
            where l.idlote in (".$inidlote.")";
    $resQ=  d::b()->query($sqlQ) or die("Falha ao buscar QUANTIDADE produzida: <p>SQL: $sqlQ");  
    $rowQ=mysqli_fetch_assoc($resQ);
    $valor=$rowQ['valor'];
    $campo=$rowQ['campo'];
    $valoRateioUn=$valorRatear/$valor;   
    $strrateio='Quantidade Produzida';
   
}elseif($tipo=="VALOR VENDA"){

    $sqlQ="select sum(ifnull(f.vlrvenda,1)) as valor, 'vlrvenda' as campo   
            from lote l 
                join prodserv p on(p.idprodserv=l.idprodserv and p.venda='Y')
                  join prodservformula f on(f.idprodservformula = l.idprodservformula and f.vlrvenda is not null and f.vlrvenda!=''  )
            where l.idlote in (".$inidlote.")";
    $resQ=  d::b()->query($sqlQ) or die("Falha ao buscar Valor de Venda: <p>SQL: $sqlQ"); 
    $rowQ=mysqli_fetch_assoc($resQ);
    if(empty($rowQ['valor'])){
        die("<tr class='rowcab unidade' style='background:#ddd;'>
                <td colspan='5' style='height: 40px; text-align-last: center;'>
                    <b>Não encontrado produtos com valor de venda configurado.</b>
                </td>
            </tr>");
    }  
   
    $valor=$rowQ['valor'];
    $campo=$rowQ['campo'];
    $valoRateioUn=$valorRatear/$valor;   
    $andporemformula=" and f.vlrvenda is not null and f.vlrvenda!='' ";
    $strrateio='Valor de Venda';
   
}elseif($tipo=="CUSTO"){
    $sqlQ="select sum(ifnull(f.vlrcusto,1)) as valor, 'vlrcusto' as campo   
    from lote l 
        join prodserv p on(p.idprodserv=l.idprodserv)
          join prodservformula f on(f.idprodservformula = l.idprodservformula and f.vlrcusto is not null and f.vlrcusto!=''  )
    where l.idlote in (".$inidlote.")";
    $resQ=  d::b()->query($sqlQ) or die("Falha ao buscar Custo de Produção: <p>SQL: $sqlQ"); 
    $rowQ=mysqli_fetch_assoc($resQ);
    if(empty($rowQ['valor'])){
        die("<tr class='rowcab unidade' style='background:#ddd;'>
                <td colspan='5' style='height: 40px; text-align-last: center;'>
                    <b>Não encontrado produtos com valor de custo configurado.</b>
                </td>
            </tr>");
    }   
    
    $valor=$rowQ['valor'];
    $campo=$rowQ['campo'];
    $valoRateioUn=$valorRatear/$valor;   
    $andporemformula=" and f.vlrcusto is not null and f.vlrcusto!='' ";
    $strrateio='Custo de Produção';
}elseif($tipo=="VOLUME"){
    $sqlQ="select sum(ifnull(f.volumeformula,1)) as valor, 'volumeformula' as campo   
    from lote l 
        join prodserv p on(p.idprodserv=l.idprodserv )
          join prodservformula f on(f.idprodservformula = l.idprodservformula and f.volumeformula is not null and f.volumeformula!=''  )
    where l.idlote in (".$inidlote.")";
    $resQ=  d::b()->query($sqlQ) or die("Falha ao buscar Volume de Produção: <p>SQL: $sqlQ"); 
    $rowQ=mysqli_fetch_assoc($resQ);
    if(empty($rowQ['valor'])){
        die("<tr class='rowcab unidade' style='background:#ddd;'>
                <td colspan='5' style='height: 40px; text-align-last: center;'>
                    <b>Não encontrado produtos com valor de custo configurado.</b>
                </td>
            </tr>");
    }   
    
    $valor=$rowQ['valor'];
    $campo=$rowQ['campo'];
    $valoRateioUn=$valorRatear/$valor;   
    $andporemformula=" and f.volumeformula is not null and f.volumeformula!='' ";
    $strrateio='Volume de Produção';
}else{
    die('Ainda não configurado');
}

$sql=" select l.idlote,concat(l.partida,'/',l.exercicio) as partida,ifnull(l.vlrlote,0) as vlrlote,ifnull(l.vlrlotetotal,0) as vlrlotetotal,l.qtdprod,l.qtdprod_exp,p.descr,l.unlote,l.status,ifnull(f.vlrcusto,1) as vlrcusto,ifnull(f.vlrvenda,1) as vlrvenda,f.volumeformula
from lote l 
    join prodserv p on(p.idprodserv=l.idprodserv)
    join prodservformula f on(f.idprodservformula = l.idprodservformula ".$andporemformula." )
where l.idlote in (".$inidlote.") order by l.vlrlotetotal,partida desc";

$res=  d::b()->query($sql) or die("Falha ao buscar lotes custo: <p>SQL: $sql");  
$qtd=mysqli_num_rows($res);
$li = 10;
?>
<tr class="rowcab unidade" style="background:#ddd;">
    <td colspan="7" style="height: 40px; text-align-last: center;">
        <b>PRÉVIA DO RATEIO</b>
    </td>
</tr>
<tr class="rowcab unidade" style="background:#ddd;">
    <td colspan="7" style=" text-align-last: center;">
        Valor para Rateio: <b><?=number_format(tratanumero($valorRatear), 2, ',', '.');?></b>
    </td>
</tr>
<tr class="rowcab unidade" style="background:#ddd;">
    <td colspan="7" style=" height: 40px; text-align-last: center;">
        <input value="<?=$valoRateioUn?>" type="hidden" name="valorrateioun" id="valorrateioun">
        Valor Rateio por <b><?=$strrateio?>: <?=number_format(tratanumero($valoRateioUn), 2, ',', '.');?></b>
        <!-- <?=$sqlQ?> -->
    </td>
</tr>
<tr>
    <th>
        PARTIDA
    </th>
    <th>
        QTD
    </th>
    <th>
        UN
    </th>
    <th title="VALOR RATEADO POR UN">
        R$ ITEM
    </th>
    <th title="VALOR RATEADO PARA O LOTE">
        R$ RATEADO
    </th>
    <th class="nowrap" title="VALOR ANTERIOR DO LOTE">
        R$ ANTERIOR     
    </th>
    <th class="nowrap"  title="NOVO VALOR DO LOTE">
        R$ NOVO       
    </th>
</tr>

<?
$li=0;
while($row = mysqli_fetch_assoc($res)) {
    $li = $li + 1;
    if(empty($row['vlrlote'])){
        $row['vlrlote']=0.00;
    }
    $valoRateioItem = $row[$campo] * $valoRateioUn;
    $novovalor=$row['vlrlotetotal']+$valoRateioItem;
    $row['vlrlote']=  $novovalor / $row['qtdprod'];
    $totalprod=$totalprod+$row['qtdprod'];
    $valorporitem = $valoRateioItem / $row['qtdprod'];

    if(empty($unlote)){
        $unlote=$row['unlote'];
    }elseif($unlote!=$row['unlote']){
        $unalerta='red';
    }else{
        $unalerta='';
    }
    
?>
    <tr class="empresa" style="width:100%;" data-text="<?=$row['partida']?>">								
        <td >
            <?=$row['partida']?>
        </td>
        <td style=" text-align-last: right;" title="QTD" >
            <?=$row['qtdprod']?>
        </td>
        <td style="color:<?=$unalerta?>" title="UN" >
            <?=$row['unlote']?>
        </td>
        <td style="text-align-last: right;" class="nowrap" title="VALOR RATEADO POR UN">
            <?=number_format(tratanumero($valorporitem), 2, ',', '.');?>     
        </td>
        <td style=" text-align-last: right;" title="VALOR RATEADO PARA ESTE LOTE">        
        <?=number_format(tratanumero($valoRateioItem), 2, ',', '.');?>  
            <input class="rateioitem" name="_<?=$li?>_u_lote_idlote" type="hidden" value="<?=$row['idlote'] ?>">
            <input class="rateioitem" name="_<?=$li?>_u_lote_vlrlotetotal" type="hidden" value="<?=$novovalor?>">
            <input class="rateioitem" name="_<?=$li?>_u_lote_vlrlote" type="hidden" value="<?=$row['vlrlote']?>">
			<input class="rateioitem" name="_<?=$li?>_u_lote_vlrlotecusto" type="hidden" value="<?=$valoRateioItem?>">
        </td>
        <td style="text-align-last: right;" class="nowrap" title="VALOR ANTERIOR DO LOTE">
            <?=number_format(tratanumero($row['vlrlotetotal']), 2, ',', '.');?>     
        </td>
        
        <td style="text-align-last: right;" class="nowrap" title="NOVO VALOR DO LOTE">
            <?=number_format(tratanumero($novovalor), 2, ',', '.');?>     
        </td>
        
    </tr>

<?
}
?>
	
<tr class="rowcab unidade" style="background:#ddd;">
    <td></td>
    <td  style="text-align-last: right;"  title="SOMA DA QUANTIDADE">
      <b><?=number_format(tratanumero($totalprod), 2, ',', '.');?></b>
    </td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
