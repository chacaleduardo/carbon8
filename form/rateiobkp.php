<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}
################################################## Atribuindo o resultado do metodo GET
$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idtipoprodserv = $_GET["idtipoprodserv"];
$idprodserv=$_GET["idprodserv"];
$pesquisa = $_GET["pesquisa"];


?>
<style>
   .divbody   th {
        font-size: 12px;
       
    }  
    td {
        font-size: 12px;
    }
    .divbody .panel-heading  {
        font-size: 12px;
        text-transform: uppercase !important; 
         color:black !important;
    }
    .divtotal{
        border: 20px;
        font-size: 12px;
        color:black !important;
    }
</style>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
	<table>
	    <tr>
		<td class="rotulo">Período</td>
		<td><font class="9graybold">entre</font></td>
		<td><input autocomplete="off" name="vencimento_1" vpar="" id="vencimento_1" class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_1?>" autocomplete="off"></td>
		<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
		<td><input autocomplete="off" name="vencimento_2" vpar="" id="vencimento_2"class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_2?>" autocomplete="off"></td>
	    </tr>
     
	    <tr>
		<td align="right">Tipo Produto:</td> 
                <td></td>
		<td colspan="10">
		    <select name="idtipoprodserv"  id="idtipoprodserv" >
                        <option value=""></option>
			<?fillselect("select idtipoprodserv,tipoprodserv from tipoprodserv where status='ATIVO'   ".getidempresa('idempresa','contapagar')." order by tipoprodserv",$idtipoprodserv);?>
		    </select>	
		</td>
	    </tr>
        
            <tr>
		<td align="right">Produto:</td> 
                <td></td>
		<td colspan="10">
		    <select name="idprodserv"  id="idprodserv" >
                    <option value=""></option>
			<?fillselect("select idprodserv,descr from prodserv where comprado='Y' and status='ATIVO' and tipo='PRODUTO'  ".getidempresa('idempresa','prodserv')." order by descr",$idprodserv);?>
		    </select>	
		</td>
	    </tr>  
            <tr>
		<td align="right">Tipo:</td>
		<td></td>
		<td><select name="pesquisa">
			<?
			$sql2 = " SELECT 'TRANSFERIDO','Transferido' UNION SELECT 'ESTOQUE','Estoque' UNION SELECT 'CONSUMIDO','Consumido' ";
			fillselect($sql2,$pesquisa);
			?>
		</select></td>
	    </tr>
	</table>	
	<div class="row"> 
	    <div class="col-md-8">
                
	    </div>
	    <div class="col-md-2">
		<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
		    <span class="fa fa-search"></span>
		</button> 
	    </div>	   
	</div>
        </div>
    </div>
    </div>
</div>
<?
/*
 * colocar condição para executar select
 */
if($_GET and (!empty($vencimento_1) and !empty($vencimento_2)) or (!empty($idprodserv))){
    


    if (!empty($vencimento_1) or !empty($vencimento_2)){
        $dataini = validadate($vencimento_1);
        $datafim = validadate($vencimento_2);

        if ($dataini and $datafim){
                $clausulac .= " and (c.criadoem  BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:59')"."  ";
                $clausulaf .= " and (l.fabricacao  BETWEEN '" . $dataini ."' and '" .$datafim ."')"."  ";
        }else{
                die ("Datas n&atilde;o V&aacute;lidas!");
        }
    }

    if(!empty($idcontaitem)){
          $stridcontaitem = " and ni.idcontaitem =" . $idcontaitem ." ";
    }else{
            $stridcontaitem="";
    }

    if(!empty($idtipoprodserv)){
        $stridtipoprodserv = " and p.idtipoprodserv =" . $idtipoprodserv ." ";
    }else{
        $stridtipoprodserv="";
    }
    
    if(!empty($idprodserv)){
        $stridprodserv = " and p.idprodserv =" . $idprodserv ." ";
    }else{
        $stridprodserv="";
    }
         

       
    
    
    if($pesquisa=='ESTOQUE'){
        $sql="select 
		p.idprodserv,p.descr,p.un,l.idlote,l.partida,l.exercicio,lf.idunidade,u.unidade,lf.qtd as consumido
               ,round(ifnull((ni.total/(l.qtdprod/l.valconvori)),0),2) as valoritem,
                round((ifnull((ni.total/l.qtdprod),0)*(lf.qtd/l.valconvori)),2) as valorconsumo               
                    from lote l 
                   join lotefracao lf on(lf.idlote = l.idlote and lf.status='DISPONIVEL') 
                   Join prodserv p on(l.idprodserv=p.idprodserv)
                   join unidade u on (u.idunidade=lf.idunidade)
                   join nfitem ni on(l.idnfitem = ni.idnfitem)
                   where  1  ".getidempresa('p.idempresa','prodserv')." 
                        and p.tipo='PRODUTO' 
                        and p.comprado='Y'
                        and p.status='ATIVO'
                        and l.status not in ('CANCELADO','REPROVADO')
                         ".$stridprodserv."
                        ".$stridtipoprodserv."
                    group by l.idlote,lf.idunidade order by unidade,p.descr,idlote";
    }elseif($pesquisa=='CONSUMIDO'){
        /*
        $sql="select 
                    p.idprodserv,p.descr,p.un,l.idlote,c.idlotecons,l.partida,l.exercicio,u.idunidade,u.unidade,l.qtdprod,c.qtdd as consumido,
                    ifnull(ni.total,0) as total,
                    round(ifnull(((ni.total/l.qtdprod)/l.valconvori),0),2) as valoritem,
                    round(ifnull(c.qtdd*((ni.total/l.qtdprod)/l.valconvori),0),2) as valorconsumo,
                    concat(r.partida,'/',r.exercicio) as destino,r.idlote as iddestino
                 from prodserv p 
                        join lote l on(l.idprodserv=p.idprodserv and l.status='APROVADO')
                        join lotefracao lf on(lf.idlote=l.idlote)
                        join unidade u on(u.idunidade=lf.idunidade)
                        join lotecons c on(c.idlote=l.idlote and c.idlotefracao = lf.idlotefracao and c.tipoobjeto ='lote' and c.idobjeto is not null)
                        join lote r on(r.idlote=c.idobjeto)
                        left join nfitem ni on(ni.idnfitem = l.idnfitem)
                    where 1  ".$clausulac." ".getidempresa('p.idempresa','prodserv')." 
                        and p.tipo='PRODUTO' 
                        and p.comprado='Y'
                        ".$stridprodserv."
                        ".$stridtipoprodserv."
                        and p.status='ATIVO' 
                               
                union			
                select 
                        p.idprodserv,p.descr,p.un,l.idlote,c.idlotecons,l.partida,l.exercicio,u.idunidade,u.unidade,l.qtdprod,c.qtdd as consumido,
                        ifnull(ni.total,0) as total,
                        round(ifnull(((ni.total/l.qtdprod)/l.valconvori),0),2) as valoritem,
                        round(ifnull(c.qtdd*((ni.total/l.qtdprod)/l.valconvori),0),2) as valorconsumo,
                        concat(a.idregistro,'/',a.exercicio) as destino,
                        a.idamostra  as iddestino
                    from prodserv p 
                            join lote l on(l.idprodserv=p.idprodserv and l.status='APROVADO')
                            join lotefracao lf on(lf.idlote=l.idlote)
                            join unidade u on(u.idunidade=lf.idunidade)
                            join lotecons c on(c.idlote=l.idlote and c.idlotefracao = lf.idlotefracao and c.tipoobjeto ='resultado')
                            join resultado r on(r.idresultado=c.idobjeto)
                            join amostra a on(a.idamostra=r.idamostra)
                            join unidade ur on(ur.idunidade=a.idunidade)
                            left join nfitem ni on(ni.idnfitem = l.idnfitem)
                        where 1  ".$clausulac." ".getidempresa('p.idempresa','prodserv')."
                            and p.tipo='PRODUTO' 
                            and p.comprado='Y'
                            ".$stridprodserv."
                            ".$stridtipoprodserv."
                            and p.status='ATIVO' 
                union
                select 
                        p.idprodserv,p.descr,p.un,l.idlote,c.idlotecons,l.partida,l.exercicio,u.idunidade,u.unidade,l.qtdprod,c.qtdd as consumido,
                        ifnull(ni.total,0) as total,round(ifnull(((ni.total/l.qtdprod)/l.valconvori),0),2) as valoritem
                        ,round(ifnull(c.qtdd*((ni.total/l.qtdprod)/l.valconvori),0),2) as valorconsumo, 
                        c.obs as destino,c.idlotecons as iddestino     
                    from prodserv p 
                        join lote l on(l.idprodserv=p.idprodserv and l.status='APROVADO')
                        join lotefracao lf on(lf.idlote=l.idlote)
                        join unidade u on(u.idunidade=lf.idunidade)
                        join lotecons c on(c.idlote=l.idlote and c.idlotefracao = lf.idlotefracao and c.tipoobjeto is  null and c.idobjeto is  null)                          
                        left join nfitem ni on(ni.idnfitem = l.idnfitem)
                    where 1  ".$clausulac." ".getidempresa('p.idempresa','prodserv')."
                            and p.tipo='PRODUTO' 
                            and p.comprado='Y'
                            ".$stridprodserv."
                            ".$stridtipoprodserv."
                            and p.status='ATIVO'                 
                order by unidade,descr,idlote";
         * */

  $sql="select l.idunidade,p.idprodserv,p.descr,l.idlote,l.partida,l.exercicio,l.qtdprod as qtdproduzida,c.qtdd as consumido,lc.qtdprod,
                    ifnull(ni.total,0) as total,
                    round(ifnull(((ni.total/lc.qtdprod)/lc.valconvori),0),2) as valoritem,
                    round(ifnull(c.qtdd*((ni.total/lc.qtdprod)/lc.valconvori),0),2) as valorconsumo,
                    concat(lc.partida,'/',lc.exercicio) as loteconsumido,lc.idlote as idloteconsumido,
                    pc.idprodserv as idprodservconsumido,pc.descr as produtoconsumido,pc.un,pc.fabricado
                from prodserv p 
                        join lote l on(l.idprodserv = p.idprodserv)
                        join lotecons c on(c.idobjeto=l.idlote and c.tipoobjeto ='lote' and c.qtdd>0) 
                    join lote lc on(lc.idlote=c.idlote)
                    join prodserv pc on(pc.idprodserv = lc.idprodserv)
                    left join nfitem ni on(ni.idnfitem = lc.idnfitem)
                where 1 ".$clausulaf." ".getidempresa('p.idempresa','prodserv')." 
                and p.tipo='PRODUTO'
                and p.venda='Y'                
                ".$stridprodserv."
                ".$stridtipoprodserv."
				 and l.idlote=95710
				-- and lc.idlote  in(90548)
                and p.status ='ATIVO' order by descr,idlote,produtoconsumido";
  
    }else{        
        $sql="select p.idprodserv,p.descr,p.un,l.idlote,l.partida,l.exercicio,r.idunidade,ur.unidade,l.qtdprod,sum(c.qtdd) as consumido
                ,ifnull(ni.total,0) as total,
                 round(ifnull(((ni.total/l.qtdprod)/l.valconvori),0),2) as valoritem,
                        round(sum(ifnull(c.qtdd*((ni.total/l.qtdprod)/l.valconvori),0)),2) as valorconsumo
                from prodserv p 
                join lote l on(l.idprodserv=p.idprodserv and l.status='APROVADO')
                join lotefracao lf on(lf.idlote=l.idlote)
                join unidade u on(u.idunidade=lf.idunidade and u.idtipounidade=3)
                join lotecons c on(c.idlote=l.idlote and c.idlotefracao = lf.idlotefracao and c.tipoobjeto ='lotefracao' and c.qtdd>0)
                join lotefracao r on(r.idlotefracao=c.idobjeto)
                join unidade ur on(ur.idunidade=r.idunidade)
                left join nfitem ni on(ni.idnfitem = l.idnfitem)
                where 1 ".$clausulac." ".getidempresa('p.idempresa','prodserv')."
                and p.tipo='PRODUTO' 
                and p.comprado='Y'
                ".$stridprodserv."
                ".$stridtipoprodserv."
                and p.status='ATIVO' -- and l.idlote=93262 
                group by l.idlote,r.idunidade order by unidade,p.descr,idlote";
        
    }



    echo "<!--";
    echo $sql;
    echo "-->";
    if (!empty($sql)){
        $res =  d::b()->query($sql) or die("Falha ao pesquisar consumos: " . mysqli_error() . "<p>SQL: $sqlgrupo");
        $ires = mysqli_num_rows($res);           
    }
    
}
?>

<!-- Mostrar mensagem de Aguarde e bloquear tela  -->

<script >

</script>
<style>
    .backbranco{
	background-color:white !important;
    }
    .panel {
        margin-top: 1px !important;
    }
</style>
<div class='row'>
<div class="divbody">
<?
if($_GET and $ires>0){
    
    if($pesquisa=='CONSUMIDO'){
        consumo($res);
    }else{    
        almoxarifado($res);
    }
              
}elseif( $ires<1 and !empty($vencimento_1) and !empty($vencimento_2)){
    echo 'Não foram encontrados valores com estas configurações';
}
function almoxarifado($res){
    $arr=array();
    $arrun=array();
    $arrunprod=array();   
    $tunidade=0;
    $tunidadeprod=0;
    $total=0;
    while($r = mysqli_fetch_assoc($res)){
        if(empty($idunidade)){$idunidade=$r['idunidade']; $idprodserv=$r['idprodserv'];}
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']]['idlote']=$r['idlote'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']]['partida']=$r['partida'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']]['exercicio']=$r['exercicio'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']]['consumido']=$r['consumido'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']]['un']=$r['un'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']]['valoritem']=$r['valoritem'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']]['valorconsumo']=$r['valorconsumo'];
        
        if($r['idunidade']!=$idunidade){
            $arrun[$idunidade]=$tunidade;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $idunidade=$r['idunidade'];
            $tunidade=0;
            $tunidadeprod=0;
        }
        $tunidade=$tunidade+$r['valorconsumo'];
        
        if($r['idprodserv']!=$idprodserv){
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $idprodserv=$r['idprodserv'];
            $tunidadeprod=0;
        }
        $tunidadeprod=$tunidadeprod+$r['valorconsumo'];
        $total=$total+$r['valorconsumo'];
      
    } 
    $arrun[$idunidade]=$tunidade;
    $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
    
?>
     
        <div class="panel panel-default" >            
            <div class="panel-body">
<?
        
	 while (list($idunidade, $arrprod) = each($arr)){  
             /*
            $sqlo="select o.* from "._DBCARBON."._modulo m 
                    JOIN unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND  o.idunidade=".$idunidade.")
                    where m.modulo = o.idobjeto 
                    and m.modulotipo = 'lote'";
            $reso = d::b()->query($sqlo) or die("Falha ao recuperar modulo lote da unidade:".mysqli_error(d::b()));
            $rowo=mysqli_fetch_assoc($reso);              
              */
?>
    
        <div class="panel panel-default" >
            <div class="panel-heading">
                <table>
                    <tr>
                        <td style="width: 100%"><?= traduzid('unidade', 'idunidade', 'unidade', $idunidade)?></td>
                        <td> <?=number_format(tratanumero($arrun[$idunidade]), 2, ',', '.'); ?></td>
                        <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>"></i></td>
                    </tr>
                </table>
             
            
                
            </div>
            <div class="panel-body collapse" id="unInfo<?=$idunidade?>">
              
        
            <?
            while (list($idprodserv,$arrlote ) = each($arrprod)){ 
                
?>
           
	
            <table class="table table-striped planilha" style="width:100%">
                <tr>
                    <th style="width: 100%"><?= traduzid('prodserv', 'idprodserv', 'descr', $idprodserv)?></td><td>   <?=number_format(tratanumero($arrunprod[$idunidade][$idprodserv]), 2, ',', '.'); ?></th>
                    <th><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#prInfo<?=$idunidade?>_<?=$idprodserv?>"></i></th>
                </tr>
            </table>
            <table  class="collapse" id="prInfo<?=$idunidade?>_<?=$idprodserv?>">
                
<?                     while (list($idlote,$value ) = each($arrlote)){ 
        
?>
                <tr style="background-color: white;" >
                    
                    <td style="width: 100%">
                        <a title="Cadastro do produto" target="_blank" href="./?_modulo=lotealmoxarifado&_acao=u&idlote=<?=$idlote?>">
                        <?=$value['partida'].'/'.$value['exercicio']?>
                        </a>
                        <?=" ".$value['consumido']." ".$value['un']." * ". number_format(tratanumero($value['valoritem']), 2, ',', '.');?>
                    </td> 
                    <td><?= number_format(tratanumero($value['valorconsumo']), 2, ',', '.');?></td>
                </tr>
   
<?         
                } 
?>
                
                </table>
           
<?                
            }   
?>
        


            
            </div>
        </div>
           <?  		
	}//while ($row = mysqli_fetch_assoc($res)){
?>               
                <div class="panel panel-default" >
                    <div class="panel-heading" style='background-color: wheat;'>                       
                        <table style="width:100%">
                        <tr>
                            <th style="width: 100%">Total:</th>
                            <th><?=number_format(tratanumero($total), 2, ',', '.');?></th>
                            <th></th>
                        </tr>
                        </table>
                    </div>
                </div>                
            </div>
        </div>
     </div>
<?  
}//function almoxarifado($res){

    function consumo($res){
    $arr=array();
    $arrun=array();
    $arrunprod=array();
    $arrloteprod=array();  
    $arrqtd=array(); 
    $tunidade=0;
    $tunidadeprod=0;
    $tlote=0;
    $total=0;
   
    while($r = mysqli_fetch_assoc($res)){
        if(empty($idunidade)){$idunidade=$r['idunidade']; $idprodserv=$r['idprodserv']; $idlote=$r['idlote']; }
        $arrqtd[$r['idunidade']][$r['idprodserv']][$r['idlote']]['qtdproduzida']=$r['qtdproduzida'];
        if($r['fabricado']=='N'){
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['idlote']=$r['idlote'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['partida']=$r['partida'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['exercicio']=$r['exercicio'];
           //$arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['qtdprod']=$r['qtdprod'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['loteconsumido']=$r['loteconsumido'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['consumido']=$r['consumido'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['produtoconsumido']=$r['produtoconsumido'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['un']=$r['un'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['valoritem']=$r['valoritem'];
            $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['valorconsumo']=$r['valorconsumo'];
        }else{
            
            $sqlx="select l.idlote,l.partida,l.exercicio,sum(c.qtdd) as consumido,p.un,concat(l.partida,'/',l.exercicio) as loteconsumido,l.idlote as idloteconsumido,
                        p.descr as produtoconsumido,
                        ifnull(ni.total,0) as total,
                        round(ifnull(((ni.total/l.qtdprod)/l.valconvori),0),2) as valoritem
                        -- ,round(ifnull(c.qtdd*((ni.total/l.qtdprod)/l.valconvori),0),2) as valorconsumo
                    from lotecons c 
                    join lote l on(l.idlote=c.idlote)
                     left join nfitem ni on(ni.idnfitem = l.idnfitem)
                     join prodserv p on(p.idprodserv=l.idprodserv)
                    where c.idobjeto= ".$r['idloteconsumido']." 
                    and c.tipoobjeto='lote' 
                    and c.qtdd>0 group by l.idlote";
            $resx = d::b()->query($sqlx) or die("A Consulta dos consumos do insumo falhou :".mysql_error()."<br>Sql:".$sqlx); 
            $qtdx= mysqli_num_rows($resx);
            if($qtdx>0){
                $valoritcons=0;
                $consumido=0;
               
                while($rowx=mysqli_fetch_assoc($resx)){
                    $consumido=round(($r['consumido']*$rowx['consumido'])/$r['qtdprod'],4);                    
                    $strcon="(".$r['consumido']."*".$rowx['consumido'].")/".$r['qtdprod'];
                    $valoritcons=round($consumido*$rowx['valoritem'],2);
                    
                    $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['idlote']=$rowx['idlote'];
                    $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['partida']=$rowx['partida'];
                    $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['exercicio']=$rowx['exercicio'];
                    //$arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['qtdprod']=$rowx['qtdprod'];
                    $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['loteconsumido']=$rowx['loteconsumido'];
                    $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['consumido']=$consumido;
					 $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['strcon']=$strcon;
                    $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['produtoconsumido']=$rowx['produtoconsumido'];
                    $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['un']=$rowx['un'];
                    $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['valoritem']=$rowx['valoritem'];
                    $arr[$r['idunidade']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['valorconsumo']=$valoritcons;
                    
                    $tunidade=$tunidade+$valoritcons;
                    $tunidadeprod=$tunidadeprod+$valoritcons;
                    $tlote=$tlote+$valoritcons;        
                    $total=$total+$valoritcons;

                }
            }
        }
        
        if($r['idunidade']!=$idunidade){
            $arrun[$idunidade]=$tunidade;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
            $idunidade=$r['idunidade'];
            $tunidade=0;
            $tunidadeprod=0;
            $tqtd=0;
            $tlote=0;
        }    
        
        if($r['idprodserv']!=$idprodserv){
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
            $idprodserv=$r['idprodserv'];
            $tunidadeprod=0;
            $tqtd=0;
            $tlote=0;
        }
        
        if($r['idlote']!=$idlote){
            $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
            $idlote=$r['idlote'];
            $tlote=0;
        }
        
        $tunidade=$tunidade+$r['valorconsumo'];
        $tunidadeprod=$tunidadeprod+$r['valorconsumo'];
        $tlote=$tlote+$r['valorconsumo'];        
        $total=$total+$r['valorconsumo'];
     
      
    } 
    $arrun[$idunidade]=$tunidade;
    $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;

    $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
   
    
?>
     
        <div class="panel panel-default" >            
            <div class="panel-body">
<?
        //print_r($arr); die();
            $totalfinal=0;
	 while (list($idunidade, $arrprod) = each($arr)){ 
            $totalfinal=$totalfinal+$arrun[$idunidade];
?>
    
        <div class="panel panel-default" >
            <div class="panel-heading">
                <table>
                    <tr>
                        <td style="width: 100%"><?= traduzid('unidade', 'idunidade', 'unidade', $idunidade)?></td>
                        <td> <?=number_format(tratanumero($arrun[$idunidade]), 2, ',', '.'); ?></td>
                        <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>"></i></td>
                    </tr>
                </table>
             
            
                
            </div>
            <div class="panel-body collapse" id="unInfo<?=$idunidade?>">
              
        
            <?
            while (list($idprodserv,$arrlote ) = each($arrprod)){ 
                $qtdproduzida=0;
                while (list($idlotev,$arrvqtd ) = each($arrqtd[$idunidade][$idprodserv])){ 
                    $qtdproduzida=$qtdproduzida+$arrvqtd['qtdproduzida']; 
                }
?>
           
	
            <table class="table table-striped planilha" style="width:100%">
                <tr>
                    <th style="width: 100%"><?=$qtdproduzida?> <?= traduzid('prodserv', 'idprodserv', 'un', $idprodserv)?> - <?= traduzid('prodserv', 'idprodserv', 'descr', $idprodserv)?> </td><td>   <?=number_format(tratanumero($arrunprod[$idunidade][$idprodserv]), 2, ',', '.'); ?> </th>
                    <th><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#prInfo<?=$idunidade?>_<?=$idprodserv?>"></i></th>
                </tr>
            </table>
            <table  class="collapse" id="prInfo<?=$idunidade?>_<?=$idprodserv?>">
   
<?                     
                while (list($idlote,$arrcons ) = each($arrlote)){ 
                    $prodservun =traduzid('prodserv', 'idprodserv', 'un', $idprodserv);
                    $idprodservformula = traduzid('lote', 'idlote', 'idprodservformula', $idlote);
                    if(!empty($idprodservformula)){
                        $sqlf="select concat(f.rotulo,' ',ifnull(f.dose,' '),' ',p.conteudo,' ',' (',f.volumeformula,' ',f.un,')') as rotulo
                                from prodservformula f join prodserv p on(p.idprodserv=f.idprodserv)
                                where  f.idprodservformula = ".$idprodservformula;
                        $rts = d::b()->query($sqlf) or die("Falha ao buscar informações sobre a formula: ". mysqli_error(d::b()));
                        $rowst=mysqli_fetch_assoc($rts);
                        $formula=' - '.$rowst['rotulo'];
                    }else{
                        $formula='';
                    }
                    
                    
?>
                 <tr style='background-color: #80808038' >
                      <td style="width: 100%" >
                        <?=traduzid('lote', 'idlote', 'partida', $idlote)?>/<?=traduzid('lote', 'idlote', 'exercicio', $idlote)?>  <?=$formula?> - <?=traduzid('lote', 'idlote', 'qtdprod', $idlote)?>-<?=$prodservun?>
						
                      </td> 
                      <td> <?=$arrloteprod[$idunidade][$idprodserv][$idlote]?></td>
                </tr>
<?                
                    while (list($idlotecons,$value ) = each($arrcons)){        
?>
                <tr style='background-color: white' >
                      <td style="width: 100%"  title="<?=$value['strcon']?>">
                          <?=$value['produtoconsumido']?>
                        <a title="Cadastro do produto" target="_blank" href="./?_modulo=lotealmoxarifado&_acao=u&idlote=<?=$idlotecons?>">
                            <?=$value['loteconsumido']?>
                        </a>
                        <?=" ".$value['consumido']." ".$value['un']." * ". number_format(tratanumero($value['valoritem']), 2, ',', '.');?>
                         
                      </td> 
                      <td><?= number_format(tratanumero($value['valorconsumo']), 2, ',', '.');?></td>
                </tr>
   
<?         
                        }
                } 
?>
                </table>
           
<?                
            }   
?>
        


            
            </div>
        </div>
           <?  		
	}//while ($row = mysqli_fetch_assoc($res)){
?>               
                <div class="panel panel-default" >
                    <div class="panel-heading" style='background-color: wheat;'>                       
                        <table style="width:100%">
                        <tr>
                            <th style="width: 100%">Total:</th>
                            <th><?=number_format(tratanumero($totalfinal), 2, ',', '.');?></th>
                            <th></th>
                        </tr>
                        </table>
                    </div>
                </div>                
            </div>
        </div>
     </div>
<?  
}//function consumo($res){
?>
</div>
</div>
<script>
    
function pesquisar(){
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var idtipoprodserv = $("[name=idtipoprodserv]").val();
    var idprodserv = $("[name=idprodserv]").val();
    var pesquisa = $("[name=pesquisa]").val();
    
    var str="vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&idtipoprodserv="+idtipoprodserv+"&idprodserv="+idprodserv+"&pesquisa="+pesquisa ;
    CB.go(str);
}

$(document).keypress(function(e) {
    if(e.which == 13){
        pesquisar();
    }
});




//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>