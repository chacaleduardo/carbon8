<? 
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../api/prodserv/index.php");
//ini_set("display_errors",1);
//error_reporting(E_ALL);

if($_POST){
    require_once("../inc/php/cbpost.php");
}
################################################## Atribuindo o resultado do metodo GET
$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idtipoprodserv = $_GET["idtipoprodserv"];
$idprodserv=$_GET["idprodserv"];
$pesquisa = $_GET["pesquisa"];
$idempresa = $_GET['idempresa'];

if (empty($idempresa)) {
    $idempresa = cb::idempresa();
}

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
    @media print {
        .ocultar{
            display:none;
        }
        .impressao{
            width: 1000px;
        }
        .fa-arrows-v{
            display:none;
        }
        .cabecalho{
            border-bottom: 1px dotted black;
        }
    }
    ul.c {list-style-type: circle;}

    li div:hover{
        background:#DCDCDC !important;
        color: black ;
       
    }

    table.linktipoprodserv:hover {
	background:#DCDCDC !important;
	color: black ;
	box-shadow: 2px 2px 5px 0px rgba(0,0,0,0.45);
}
</style>
<div class="row ocultar">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
	<table>
	    <tr>
		<td class="rotulo">Período entre</td>
		
		<td>
            <input autocomplete="off" name="vencimento_1" vpar="" id="vencimento_1" class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_1?>" autocomplete="off">
		    <font class="9graybold">&nbsp;e&nbsp;</font>
		    <input autocomplete="off" name="vencimento_2" vpar="" id="vencimento_2"class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_2?>" autocomplete="off">
        </td>
	    </tr>
     
	    <tr>
		<td align="right">Empresa:</td> 
                
		<td colspan="10">
		    <select class="size20" name="idempresa"  id="idempresa" onchange='selecionartipo(this)' >
			<?
               $sql ='SELECT idempresa,nomefantasia from empresa where idempresa in (select idempresa from matrizconf where idmatriz='.cb::idempresa().')
               UNION
               SELECT idempresa,nomefantasia from empresa where idempresa ='.cb::idempresa().';';

                fillselect($sql,$idempresa);?>
		    </select>	
		</td>
	    </tr>
	    <tr>
		<td align="right">Tipo Produto:</td> 
                
		<td colspan="10">
		    <select class="size20" name="idtipoprodserv"  id="idtipoprodserv" >
                        <option value=""></option>
			<?fillselect("select idtipoprodserv,tipoprodserv from tipoprodserv where status='ATIVO'   and idempresa = ".$idempresa."  order by tipoprodserv",$idtipoprodserv);?>
		    </select>	
		</td>
	    </tr>
        
            <tr>
		<td align="right">Produto:</td> 
               
		<td colspan="10">
		    <select class="size50" name="idprodserv"  id="idprodserv" >
                    <option value=""></option>
			<?fillselect("select idprodserv,descr from prodserv where (comprado='Y' or fabricado='Y') and status='ATIVO' and tipo='PRODUTO'  and idempresa = ".$idempresa."  order by descr",$idprodserv);?>
		    </select>	
		</td>
	    </tr>  
            <tr>
		<td align="right">Tipo:</td>
		
		<td><select class="size20" name="pesquisa">
			<?
			$sql2 = "SELECT 'TRANSFERIDO','Transferido do Almoxarifado' UNION SELECT 'ESTOQUE','Estoque' UNION SELECT 'CONSUMIDO','Consumido' UNION SELECT 'DESCARTE','Descartado'  ";
			fillselect($sql2,$pesquisa);
			?>
		</select></td>
	    </tr>
	</table>	
	<div class="row"> 
	    <div class="col-md-8">
                
	    </div>
	    <div class="col-md-2">
		<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
		    <span class="fa fa-search"></span>
		</button> 
        
	    </div>
        <div class="col-md-2">
        <?/*if(!empty($vencimento_1)){?>
            <i class="fa fa-print fa-2x  fade pointer hoverazul  btn-lg pointer" onclick="imprimir()" title="Imprimir"></i>
        <?}*/?>
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
                $clausaulai .=" and n.dtemissao between '" . $dataini ." 00:00:00' and '" .$datafim ." 00:00:00'  ";
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
        $sql="select t.idtipoprodserv,t.tipoprodserv,
		p.idprodserv,p.descr,p.un,l.idlote,l.partida,l.exercicio,lf.idunidade,u.unidade,ifnull(lf.qtd,0) as consumido,l.qtdprod,l.qtdprod_exp
               ,round(ifnull(l.vlrlote,0),2) as valoritem,
                round((ifnull(l.vlrlote,0)*(lf.qtd)),2) as valorconsumo,l.vencimento,
                p.fabricado,ifnull(f.vlrcusto,0) as vlrcusto , ifnull(f.qtdpadraof,1) as  qtdpadraof                    
                    from lote l 
                   join lotefracao lf on(lf.idlote = l.idlote and lf.status='DISPONIVEL' and lf.qtd>0) 
                   Join prodserv p on(l.idprodserv=p.idprodserv)
                   join tipoprodserv t on(t.idtipoprodserv = p.idtipoprodserv)
                   join unidade u on (u.idunidade=lf.idunidade)  
                   left join prodservformula f on(f.idprodservformula=l.idprodservformula)                 
                   where  1  and p.idempresa = ".$idempresa." 
                        and p.tipo='PRODUTO' 
                        and (p.comprado='Y' or p.fabricado='Y')
                         and p.status='ATIVO'
                        and l.status not in ('CANCELADO','REPROVADO')
                         ".$stridprodserv."
                        ".$stridtipoprodserv."
                    group by l.idlote,lf.idunidade order by unidade,t.tipoprodserv,p.descr,idlote";
    }elseif($pesquisa=='CONSUMIDO'){
       

            $sql=" select 
                    'FABRICACAO' as tipo,t.idtipoprodserv,t.tipoprodserv , 1 as idlotecons,l.idunidade,p.idprodserv,p.descr,l.idlote,l.partida,l.exercicio,l.qtdprod as qtdproduzida, 0  consumido, 0 as qtdprod,
                    round(ifnull(l.vlrlote,0),2) as valoritem,
                    round((ifnull(l.vlrlote,0)*(l.qtdprod)),2) as valorconsumo,
                    concat(l.partida,'/',l.exercicio) as loteconsumido,l.idlote as idloteconsumido,
                    p.idprodserv as idprodservconsumido,p.descr as produtoconsumido,p.un,p.fabricado
                    ,l.criadopor,l.fabricacao as criadoem
            from prodserv p 
                join lote l on(l.idprodserv = p.idprodserv)                     
            join tipoprodserv t on(t.idtipoprodserv = p.idtipoprodserv)
                where 1 ".$clausulaf." and p.idempresa = ".$idempresa." 
                and p.tipo='PRODUTO'
                and p.venda='Y'                
                ".$stridprodserv."
                ".$stridtipoprodserv."
				-- and l.idlote=77054
				-- and lc.idlote  in(90548)
                -- and p.status ='ATIVO' 
                union
                SELECT 
                'CONSUMO' AS `tipo`,
                 `t`.`idtipoprodserv` AS `idtipoprodserv`,
                 `t`.`tipoprodserv` AS `tipoprodserv`,
                 `c`.`idlotecons` AS `idlotecons`,
                 `lf`.`idunidade` AS `idunidade`,
                 `p`.`idprodserv` AS `idprodserv`,
                 `p`.`descr` AS `descr`,
                 `l`.`idlote` AS `idlote`,
                 `l`.`partida` AS `partida`,
                 `l`.`exercicio` AS `exercicio`,
                 `l`.`qtdprod` AS `qtdproduzida`,
                 `c`.`qtdd` AS `consumido`,
                 `lc`.`qtdprod` AS `qtdprod`,
                 ROUND(IFNULL(`lc`.`vlrlote`, 0), 2) AS `valoritem`,
                 ROUND((IFNULL(`lc`.`vlrlote`, 0) * `c`.`qtdd`),
                         2) AS `valorconsumo`,
                 CONCAT(`lc`.`partida`, '/', `lc`.`exercicio`) AS `loteconsumido`,
                 `lc`.`idlote` AS `idloteconsumido`,
                 `pc`.`idprodserv` AS `idprodservconsumido`,
                 `pc`.`descr` AS `produtoconsumido`,
                 `pc`.`un` AS `un`,
                 `pc`.`fabricado` AS `fabricado`,
                 `c`.`criadopor` AS `criadopor`,
                 `c`.`criadoem` AS `criadoem`
             FROM `lotecons` `c`		
                 JOIN `lote` `l`  ON (`c`.`idlote` = `l`.`idlote`)
                 join lotefracao f on(f.idlotefracao=c.idlotefracao)
                 join  `prodserv` `p` ON (`l`.`idprodserv` = `p`.`idprodserv` and `p`.`tipo` = 'PRODUTO') 
                 JOIN `lotefracao` `lf` ON (`lf`.`idlotefracao` = `c`.`idobjeto`)
                  JOIN `lote` `lc` ON (`lc`.`idlote` = `lf`.`idlote`)
                 JOIN `prodserv` `pc` ON (`pc`.`idprodserv` = `lc`.`idprodserv`)
                 JOIN `tipoprodserv` `t` ON (`t`.`idtipoprodserv` = `p`.`idtipoprodserv`)
                 
                 join unidade u on(f.idunidade=u.idunidade and u.idtipounidade=3)
                 
             WHERE
                          `c`.`tipoobjeto` = 'lotefracao'
                       AND `c`.`qtdd` > 0
                     AND `c`.`status` <> 'INATIVO'
                     ".$stridprodserv."
                     ".$stridtipoprodserv."
                     ".$clausulac."
                     and p.idempresa = ".$idempresa."
                order by idunidade,tipoprodserv,descr,idlote,produtoconsumido,idlotecons";

/*
        $sql="select 'FABRICACAO' as tipo,t.idtipoprodserv,t.tipoprodserv ,c.idlotecons,l.idunidade,p.idprodserv,p.descr,l.idlote,l.partida,l.exercicio,l.qtdprod as qtdproduzida,c.qtdd as consumido,lc.qtdprod,
                    ifnull(ni.total,0) as total,                   
                    round(ifnull(lc.vlrlote,0),2) as valoritem,
                    round((ifnull(lc.vlrlote,0)*(c.qtdd)),2) as valorconsumo,
                    concat(lc.partida,'/',lc.exercicio) as loteconsumido,lc.idlote as idloteconsumido,
                    pc.idprodserv as idprodservconsumido,pc.descr as produtoconsumido,pc.un,pc.fabricado
                    ,c.criadopor,c.criadoem
                from prodserv p 
                        join lote l on(l.idprodserv = p.idprodserv)
                        join lotecons c on(c.idobjeto=l.idlote and c.tipoobjeto ='lote' and c.qtdd>0 and c.status!='INATIVO') 
                    join lote lc on(lc.idlote=c.idlote)
                    join prodserv pc on(pc.idprodserv = lc.idprodserv)
                    join tipoprodserv t on(t.idtipoprodserv = p.idtipoprodserv)
                    left join nfitem ni on(ni.idnfitem = lc.idnfitem)
                where 1 ".$clausulaf." ".getidempresa('p.idempresa','prodserv')." 
                and p.tipo='PRODUTO'
                and p.venda='Y'                
                ".$stridprodserv."
                ".$stridtipoprodserv."
				-- and l.idlote=77054
				-- and lc.idlote  in(90548)
                -- and p.status ='ATIVO' 
                union
                select 'CONSUMO' as tipo,t.idtipoprodserv,t.tipoprodserv,c.idlotecons,lf.idunidade,p.idprodserv,p.descr,l.idlote,l.partida,l.exercicio,l.qtdprod as qtdproduzida,c.qtdd as consumido,lc.qtdprod,
                ifnull(ni.total,0) as total,                   
                round(ifnull(lc.vlrlote,0),2) as valoritem,
                round((ifnull(lc.vlrlote,0)*(c.qtdd)),2) as valorconsumo,
                concat(lc.partida,'/',lc.exercicio) as loteconsumido,lc.idlote as idloteconsumido,
                pc.idprodserv as idprodservconsumido,pc.descr as produtoconsumido,pc.un,pc.fabricado
                ,c.criadopor,c.criadoem
            from prodserv p 
                    join lote l on(l.idprodserv = p.idprodserv)
                    join lotecons c on(c.idlote=l.idlote and c.tipoobjeto='lotefracao'
                                    and c.idobjeto = c.idlotefracao and c.qtdd>0 and c.status!='INATIVO') 
                join lote lc on(lc.idlote=c.idlote)
                join lotefracao lf on(lf.idlotefracao =c.idlotefracao)
                join prodserv pc on(pc.idprodserv = lc.idprodserv)
                join tipoprodserv t on(t.idtipoprodserv = p.idtipoprodserv)
                left join nfitem ni on(ni.idnfitem = lc.idnfitem)
            where 1 ".$clausulac."  and p.idempresa=1  
            and p.tipo='PRODUTO'
            ".$stridprodserv."
            ".$stridtipoprodserv."
                order by idunidade,tipoprodserv,descr,idlote,produtoconsumido,idlotecons";
*/
  
    }elseif($pesquisa=='DESCARTE'){
        
        $sql="select c.idlotecons,t.idtipoprodserv,t.tipoprodserv,p.idprodserv,p.descr,l.idlote,l.partida,l.exercicio,f.idunidade,ifnull(l.vlrlote,0) as vlrlote,
                CASE
                    WHEN l.qtdprod = null THEN 1
                    WHEN l.qtdprod = 0 THEN 1
                    ELSE l.qtdprod
                END as qtdprod,
                u.unidade,c.qtdd,c.qtdc,c.qtdd_exp,c.qtdc_exp,c.obs,c.criadopor,c.criadoem,pf.qtdpadraof,p.fabricado,l.idprodservformula,p.un,p.fabricado,p.comprado               
                from lotecons c join lotefracao f on(f.idlotefracao=c.idlotefracao )
                join lote l on(l.idlote =f.idlote)
                join prodserv p on(p.idprodserv=l.idprodserv)
                join tipoprodserv t on(t.idtipoprodserv = p.idtipoprodserv)
                join unidade u on(u.idunidade=f.idunidade)
                left join prodservformula pf on(pf.idprodservformula=l.idprodservformula)
                where 1 ".$clausulac." and p.idempresa = ".$idempresa." 
                ".$stridprodserv."
                ".$stridtipoprodserv."
                and (c.qtdd>0
                or c.qtdc>0)
                and c.status!='INATIVO'
                and c.tipoobjeto is null 
                and c.idobjeto is null
                --  and l.idlote=95117
                order by f.idunidade,t.tipoprodserv,p.descr,l.idlote";
      

    }else{        
        $sql="select p.idprodserv,t.idtipoprodserv,t.tipoprodserv,p.descr,p.un,l.idlote,l.partida,l.exercicio,r.idunidade,ur.unidade,l.qtdprod,sum(c.qtdd) as consumido
                ,ifnull(ni.total,0) as total,
                round(ifnull(l.vlrlote,0),2) as valoritem,
                round((ifnull(l.vlrlote,0)*sum(c.qtdd)),2) as valorconsumo,p.fabricado, c.criadoem,c.criadopor
                from prodserv p 
                join lote l on(l.idprodserv=p.idprodserv and l.status='APROVADO')
                join lotefracao lf on(lf.idlote=l.idlote)
                join unidade u on(u.idunidade=lf.idunidade and u.idtipounidade=3)
                join lotecons c on(c.idlote=l.idlote and c.idlotefracao = lf.idlotefracao and c.tipoobjeto ='lotefracao' and c.qtdd>0 and c.status!='INATIVO')
                join lotefracao r on(r.idlotefracao=c.idobjeto)
                join unidade ur on(ur.idunidade=r.idunidade)
                join tipoprodserv t on(t.idtipoprodserv = p.idtipoprodserv)
                left join nfitem ni on(ni.idnfitem = l.idnfitem)
                where 1 ".$clausulac." and p.idempresa = ".$idempresa." 
                and p.tipo='PRODUTO' 
                and p.comprado='Y'
                ".$stridprodserv."
                ".$stridtipoprodserv."
                -- and p.status='ATIVO' 
                -- and l.idlote=93262 
                group by l.idlote,r.idunidade order by unidade,t.tipoprodserv,p.descr,idlote";
        
    }
 


    echo "<!--";
    echo $sql;
    echo "-->";
    if (!empty($sql)){
        $res =  d::b()->query($sql) or die("Falha ao pesquisar consumos: " . mysqli_error() . "<p>SQL: $sql");
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
<div class="row  impressao" >
    <div class="col-md-12 " >
<?
if($_GET and $ires>0){
    
    if($pesquisa=='CONSUMIDO'){
        consumo($res);
      
    }elseif($pesquisa=='DESCARTE'){
        descarte($res);  
    }elseif($pesquisa=='ESTOQUE'){
        estoque($res);
    }else{    
        almoxarifado($res);
    }
              
}elseif( $ires<1 and !empty($vencimento_1) and !empty($vencimento_2)){
    echo 'Não foram encontrados valores com estas configurações';
}
?>
</div>
</div>
<?
function estoque($res){
    $arr=array();
    $arrun=array();
    $arruntipoprod=array();
    $arrunprod=array();   
    $tunidade=0;
    $tunidadetipoprod=0;
    $tunidadeprod=0;
    $total=0;
    while($r = mysqli_fetch_assoc($res)){
        $valorlote=0;
        $valor=0;
    /*
        if($r['fabricado']=='Y'){
            
            $valorlote=cprod::buscavalorlote($r['idlote'],1,'Y');
            
          //echo($r['idlote'].'=('.$valorlote.'/'.$r['qtdprod'].') <br>');
            //$valorx = (( $valorlote/$r['qtdprod']) *$r['consumido']);   
            $r['valoritem']=( $valorlote/$r['qtdprod']);
            $r['valorconsumo']=(( $valorlote/$r['qtdprod']) *$r['consumido']);   
            if(empty($r['valoritem']) or $r['valoritem']<0){$r['valoritem']=0;}
            if(empty($r['valorconsumo']) or $r['valorconsumo']<0){$r['valorconsumo']=0;}
           // $r['valoritem']=($r['vlrcusto']/$r['qtdpadraof']);
           // $r['valorconsumo']=($r['vlrcusto']/$r['qtdpadraof'])*$r['consumido'];
         
        }*/

        if(empty($idunidade)){$idunidade=$r['idunidade']; $idprodserv=$r['idprodserv'];$idtipoprodserv=$r['idtipoprodserv'];}
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['idlote']=$r['idlote'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['partida']=$r['partida'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['exercicio']=$r['exercicio'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['consumido']=$r['consumido'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['qtdprod_exp']=$r['qtdprod_exp'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['vencimento']=$r['vencimento'];            
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['un']=$r['un'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['valoritem']=$r['valoritem'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['valorconsumo']=$r['valorconsumo'];
        
        if($r['idunidade']!=$idunidade){
            $arrun[$idunidade]=$tunidade;
            $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $idunidade=$r['idunidade'];
            $tunidade=0;
            $tunidadeprod=0;
            $tunidadetipoprod=0;
        }
        

        if($r['idtipoprodserv']!=$idtipoprodserv){
            $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $idtipoprodserv=$r['idtipoprodserv'];
            $tunidadetipoprod=0;
            
        } 
       // $tunidadetipoprod=$tunidadetipoprod+$r['valorconsumo'];
        $tunidade=$tunidade+$r['valorconsumo'];
        
        if($r['idprodserv']!=$idprodserv){
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $idprodserv=$r['idprodserv'];
            $tunidadeprod=0;
        }
        $tunidadeprod=$tunidadeprod+$r['valorconsumo'];
        $tunidadetipoprod=$tunidadetipoprod+$r['valorconsumo'];
        $total=$total+$r['valorconsumo'];
      
    } 
    $arrun[$idunidade]=$tunidade;
    $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
    $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
    
?>
     
        <div class="panel panel-default" >            
            <div class="panel-body">
<?
        
	 while (list($idunidade, $arrtprod) = each($arr)){  
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
            <div class="panel-heading cabecalho" style="background-color: #d4d4d4 !important;">
                <table>
                    <tr>
                        <td style="width: 100%"><?= traduzid('unidade', 'idunidade', 'unidade', $idunidade)?></td>
                        <td style="text-align: right;" class="nowrap">R$ <?=number_format(tratanumero($arrun[$idunidade]), 2, ',', '.'); ?></td>
                        <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>" aria-expanded="false"></i></td>
                    </tr>
                </table>
             
            
                
            </div>
            <div class="panel-body collapse" id="unInfo<?=$idunidade?>">
              
            
            <?
            $sqlmd="select o.idobjeto from unidadeobjeto o
                        join carbonnovo._modulo m 
                        on (m.modulo = o.idobjeto 
                            and m.ready='FILTROS' 
                            and m.modulotipo = 'lote')
                        where (o.tipoobjeto='modulo' 	and o.idunidade = ".$idunidade.")";

            $rmd = d::b()->query($sqlmd) or die("Falha ao link lote da unidade:".mysqli_error(d::b()));
            $rowmd=mysqli_fetch_assoc($rmd);
            $row_idobjeto=$rowmd['idobjeto'];

            if(empty($row_idobjeto)){
                $sqlmd="select o.idobjeto from unidadeobjeto o
                        join "._DBCARBON."._modulo m 
                        on (m.modulo = o.idobjeto 
                            and m.ready='FILTROS' 
                            and m.modulotipo = 'lote')
                        where (o.tipoobjeto='modulo' 	and o.idunidade =8)";

                $rmd = d::b()->query($sqlmd) or die("Falha ao link lote da unidade:".mysqli_error(d::b()));
                $rowmd=mysqli_fetch_assoc($rmd);
                $row_idobjeto=$rowmd['idobjeto'];

            }
            while (list($idtipoprodserv,$arrprod ) = each($arrtprod)){
                $tipoprodserv=traduzid('tipoprodserv','idtipoprodserv','tipoprodserv',$idtipoprodserv);
    ?>
                <div class="panel panel-default" >
                <div class="panel-heading cabecalho" style="background-color: #c5c5c587 !important;">
                    <table>
                        <tr >
                            <td style="width: 100%"><?=$tipoprodserv?></td>
                            <td style="text-align: right;" class='nowrap'>R$ <?=number_format(tratanumero($arruntipoprod[$idunidade][$idtipoprodserv]), 2, ',', '.'); ?></td>
                            <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>_<?=$idtipoprodserv?>" aria-expanded="false"></i></td>
                        </tr>
                    </table>   
                </div>
                <div class="panel-body collapse" style="padding-top:2px !important"  id="unInfo<?=$idunidade?>_<?=$idtipoprodserv?>">
    <?
            while (list($idprodserv,$arrlote ) = each($arrprod)){ 
                
?>
           
	
           <div class='teste' style="border: 1px solid #ccc;
                display: flow-root;
                border-radius: 4px;
                background: #e6e6e6;
                padding: 0px 8px 0px 0px;
                margin: 4px !important;">
            <table class="planilha linktipoprodserv pointer" style="width:100%; margin:4px; background: #d4d4d426; height:40px">
                <tr >
                    <th style="width: 100%"><?=traduzid('prodserv', 'idprodserv', 'descr', $idprodserv)?></th><th style="text-align: right;" class="nowrap">   R$ <?=number_format(tratanumero($arrunprod[$idunidade][$idprodserv]), 2, ',', '.'); ?></th>
                    <th><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#prInfo<?=$idunidade?>_<?=$idprodserv?>" aria-expanded="false"></i></th>
                </tr>
            </table>
            <table style="width: 99%; float: right;" class="table table-striped planilha collapse" id="prInfo<?=$idunidade?>_<?=$idprodserv?>">
            <tr>
                <th class="">Qtd</th>
                <th class="col-md-1">Un</th>
                <th class="col-md-4" style="width:80%">Lote</th>
                <th class="col-md-2">Vencimento</th>
                <th class="col-md-2 nowrap">Valor Un</th>
                <th class="col-md-2">Valor</th>
            </tr>
                
<?                     while (list($idlote,$value ) = each($arrlote)){ 
        
?>
                <tr>
                    <td style="text-align: right;">
                    <?=recuperaExpoente(tratanumero($value['consumido']),$value['qtdprod_exp'])?>
                    </td>
                    <td ><?=$value['un']?></td>
                    <td >
                        <a title="Cadastro do produto" target="_blank" href="./?_modulo=<?=$row_idobjeto?>&_acao=u&idlote=<?=$idlote?>">
                        <?=$value['partida'].'/'.$value['exercicio']?>
                        </a>                       
                    </td> 
                    <td >
                        <?
                        if(empty($value['vencimento'])){
                        ?>
                          <span title='Data de vencimento vazia'>  <?="- - - - - - - -"?></span>
                        <?
                        }else{
                            echo(dma($value['vencimento']));
                        }
                    ?></td>
                    <td style="text-align: right;" class="nowrap">R$ <?=number_format(tratanumero($value['valoritem']), 2, ',', '.');?></td>
                    <td style="text-align: right;" class="nowrap">R$ <?= number_format(tratanumero($value['valorconsumo']), 2, ',', '.');?></td>
                </tr>
   
<?         
                } 
?>           
                </table>
            </div>
<?                
            }   
?>  
            </div>
        </div>
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
                            <th style="text-align: right;" class="nowrap">R$ <?=number_format(tratanumero($total), 2, ',', '.');?></th>
                            <th></th>
                        </tr>
                        </table>
                    </div>
                </div>                
            </div>
        </div>
     </div>
<?  
}//function estoque($res){

function almoxarifado($res){
    $arr=array();
    $arrun=array();
    $arrunprod=array(); 
    $arruntipoprod =array();
    $tunidade=0;
    $tunidadeprod=0;
    $tunidadetipoprod=0;
    $total=0;
    while($r = mysqli_fetch_assoc($res)){
        if(empty($idunidade)){$idunidade=$r['idunidade'];$idtipoprodserv=$r['idtipoprodserv']; $idprodserv=$r['idprodserv'];}
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['idlote']=$r['idlote'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['partida']=$r['partida'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['exercicio']=$r['exercicio'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['consumido']=$r['consumido'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['criadopor']=$r['criadopor'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['criadoem']=$r['criadoem'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['un']=$r['un'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['valoritem']=$r['valoritem'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['valorconsumo']=$r['valorconsumo'];
        
        if($r['idunidade']!=$idunidade){
            $arrun[$idunidade]=$tunidade;           
            $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $idunidade=$r['idunidade'];
            $tunidade=0;           
            $tunidadetipoprod=0;
            $tunidadeprod=0;
        }
        $tunidade=$tunidade+$r['valorconsumo'];


        if($r['idtipoprodserv']!=$idtipoprodserv){
            $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $idtipoprodserv=$r['idtipoprodserv'];
            $tunidadetipoprod=0;
            
        } 
        $tunidadetipoprod=$tunidadetipoprod+$r['valorconsumo'];
        
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
    $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
    
?>
     
        <div class="panel panel-default" >            
            <div class="panel-body">
<?
        
	 while (list($idunidade, $arrtprod) = each($arr)){  
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
            <div class="panel-heading cabecalho" style="background-color: #d1cfcf !important;">
                <table>
                    <tr>
                        <td style="width: 100%"><?= traduzid('unidade', 'idunidade', 'unidade', $idunidade)?></td>
                        <td style="text-align: right;" class='nowrap'>R$ <?=number_format(tratanumero($arrun[$idunidade]), 2, ',', '.'); ?></td>
                        <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>" aria-expanded="false"></i></td>
                    </tr>
                </table>
            </div>
            <div class="panel-body collapse" id="unInfo<?=$idunidade?>">
              
        
            <?
            $sqlmd="select o.idobjeto from unidadeobjeto o
                        join carbonnovo._modulo m 
                        on (m.modulo = o.idobjeto 
                            and m.ready='FILTROS' 
                            and m.modulotipo = 'lote')
                        where (o.tipoobjeto='modulo' 	and o.idunidade = ".$idunidade.")";

            $rmd = d::b()->query($sqlmd) or die("Falha ao link lote da unidade:".mysqli_error(d::b()));
            $rowmd=mysqli_fetch_assoc($rmd);
            $row_idobjeto=$rowmd['idobjeto'];

            if(empty($row_idobjeto)){
                $sqlmd="select o.idobjeto from unidadeobjeto o
                        join "._DBCARBON."._modulo m 
                        on (m.modulo = o.idobjeto 
                            and m.ready='FILTROS' 
                            and m.modulotipo = 'lote')
                        where (o.tipoobjeto='modulo' 	and o.idunidade =8)";

                $rmd = d::b()->query($sqlmd) or die("Falha ao link lote da unidade:".mysqli_error(d::b()));
                $rowmd=mysqli_fetch_assoc($rmd);
                $row_idobjeto=$rowmd['idobjeto'];

            }

        while (list($idtipoprodserv,$arrprod ) = each($arrtprod)){
            $tipoprodserv=traduzid('tipoprodserv','idtipoprodserv','tipoprodserv',$idtipoprodserv);
?>
            <div class="panel panel-default" >
            <div class="panel-heading cabecalho" style="background-color: #c5c5c587 !important;">
                <table>
                    <tr >
                        <td style="width: 100%"><?=$tipoprodserv?></td>
                        <td style="text-align: right;" class='nowrap' >R$ <?=number_format(tratanumero($arruntipoprod[$idunidade][$idtipoprodserv]), 2, ',', '.'); ?></td>
                        <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>_<?=$idtipoprodserv?>" aria-expanded="false"></i></td>
                    </tr>
                </table>   
            </div>
            <div class="panel-body collapse" style="padding-top:2px !important"  id="unInfo<?=$idunidade?>_<?=$idtipoprodserv?>">
<?
            while (list($idprodserv,$arrlote ) = each($arrprod)){ 
                
?>
           
           <div class='teste' style="border: 1px solid #ccc;
                        display: flow-root;
                        border-radius: 4px;
                        background: #e6e6e60a;
                        padding: 0px 8px 0px 0px;
                        margin: 4px !important;">
            <table class="planilha linktipoprodserv pointer" style="width:100%; margin:4px; background: #d4d4d426; height:40px">
            <tr>   
                <th style="width: 100%"><?=traduzid('prodserv', 'idprodserv', 'descr', $idprodserv)?></th><th style="text-align: right;" class="nowrap">R$ <?=number_format(tratanumero($arrunprod[$idunidade][$idprodserv]), 2, ',', '.'); ?></th>
                    <th><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#prInfo<?=$idunidade?>_<?=$idprodserv?>" aria-expanded="false"></i></th>
                </tr>
            </table>
            <table style="width: 99%; float: right;"  class="table table-striped planilha collapse" id="prInfo<?=$idunidade?>_<?=$idprodserv?>">
            <tr>
                <th>Qtd</th>
                <th>Un</th>
                <th style="width: 100%">
                <div class="col-md-6">
                    Lote
                </div>
                <div class="col-md-3">Transferido Por</div>
                <div class="col-md-3">Transferido Em</div>

                </th>
                <th class="nowrap">Valor Un</th>
                <th>Valor</th>
            </tr>
                
<?                     while (list($idlote,$value ) = each($arrlote)){ 
        
?>
                <tr>
                        <td  style="text-align: right;">
                            <?=$value['consumido']?>
                        </td>
                        <td  style="text-align: right;">
                            <?=$value['un']?>
                        </td>
                        <td style="width: 100%" style="text-align: right;">
                            <div class="col-md-6">
                                <a title="Cadastro do produto" target="_blank" href="./?_modulo=<?=$row_idobjeto?>&_acao=u&idlote=<?=$idlote?>">
                                <?=$value['partida'].'/'.$value['exercicio']?>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <?=$value['criadopor']?>
                            </div>
                            <div class="col-md-3">
                            <?=dmahms($value['criadoem'])?>
                            </div>
                        </td>
                        <td  style="text-align: right;" class='nowrap'>
                           R$ <?=number_format(tratanumero($value['valoritem']), 2, ',', '.');?>
                        </td> 
                    <td style="text-align: right;" class='nowrap' >R$ <?=number_format(tratanumero($value['valorconsumo']), 2, ',', '.');?></td>
                </tr>
   
<?         
                } 
?>           
                </table>
            </div>
<?                
            }// while (list($idprodserv,$arrlote ) = each($arrprod)){ 
?>
            </div>
            </div>
        
<?           

        }//while (list($idtipoprodserv,$arrprod ) = each($arrtprod)){  
?>     
           </div>
        </div>  
           <?  		
	}// while (list($idunidade, $arrtprod) = each($arr)){
?>               
                <div class="panel panel-default" >
                    <div class="panel-heading" style='background-color: wheat;'>                       
                        <table style="width:100%">
                        <tr>
                            <th style="width: 100%">Total:</th>
                            <th style="text-align: right;" class='nowrap'>R$ <?=number_format(tratanumero($total), 2, ',', '.');?></th>
                            <th></th>
                        </tr>
                        </table>
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
    $arruntipoprod=array();
    $arrloteprod=array(); 
    $arrqtdlote=array(); 
    $arrqtd=array(); 
    $tunidade=0;
    $tunidadeprod=0;
    $$tunidadetipoprod=0;
    $qtdunidadecons=0;
    $tlote=0;
    $qtdlote=0;
    $total=0;
   global $r,$arr,$tunidade,$tunidadeprod,$tunidadetipoprod,$qtdunidadecons,$tlote,$total,$qtdlote ;
    while($r = mysqli_fetch_assoc($res)){
       
        if(empty($idunidade)){$idunidade=$r['idunidade']; $idtipoprodserv=$r['idtipoprodserv']; $idprodserv=$r['idprodserv']; $idlote=$r['idlote']; }
        if($r['tipo']=='FABRICACAO'){
            //$arrqtd[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']]['qtdproduzida']=$r['qtdproduzida'];
            $arrqtd[$r['idunidade']][$r['idprodserv']][$r['idlote']]['qtdproduzida']=$r['qtdproduzida'];
        }
      //  if($r['fabricado']=='N'){
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['idlote']=$r['idlote'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['partida']=$r['partida'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['exercicio']=$r['exercicio'];
           //$arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idloteconsumido']]['qtdprod']=$r['qtdprod'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['loteconsumido']=$r['loteconsumido'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['consumido']=$r['consumido'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['produtoconsumido']=$r['produtoconsumido'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['un']=$r['un'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['valoritem']=$r['valoritem'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['valorconsumo']=$r['valorconsumo'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['criadoem']=$r['criadoem'];  
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['criadopor']=$r['criadopor'];  
           
       /* }else{

            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['idlote']=$r['idlote'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['loteconsumido']=$r['loteconsumido'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['consumido']=$r['consumido'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['produtoconsumido']=$r['produtoconsumido'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['un']=$r['un'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['valoritem']=$r['valoritem'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['valorconsumo']=$r['valorconsumo'];
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['criadoem']=$r['criadoem'];  
            $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]['criadopor']=$r['criadopor']; 

            $percentualcon=$r['consumido']/$r['qtdprod'];
            //$idlotegrupo=$r['idloteconsumido'];
            buscainsumos($r['idloteconsumido'],$percentualcon); 
            // buscainsumos($r['idloteconsumido'],$r['consumido'],$r['qtdprod']);           
           
        }*/
        
        if($r['idunidade']!=$idunidade){
            $arrun[$idunidade]=$tunidade;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
            if($tipo=='CONSUMO'){
                $arrqtd[$idunidade][$idprodserv][$idlote]['qtdproduzida']=$qtdunidadecons;
               
            }           
            $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
            $arrqtdlote[$idunidade][$idprodserv][$idlote]=$qtdlote;
            $idunidade=$r['idunidade'];
            $tipo=$r['tipo'];
            $tunidade=0;
            $tunidadetipoprod=0;
            $tunidadeprod=0;
            $qtdunidadecons=0;
            $tqtd=0;
            $tlote=0;
            $qtdlote=0;
        }
        
        if($r['idtipoprodserv']!=$idtipoprodserv){
            $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
            $idtipoprodserv=$r['idtipoprodserv'];
            $tunidadetipoprod=0;
        } 
        
        if($r['idprodserv']!=$idprodserv){
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            if($tipo=='CONSUMO'){
                $arrqtd[$idunidade][$idprodserv][$idlote]['qtdproduzida']=$qtdunidadecons;
            }
            $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
            $arrqtdlote[$idunidade][$idprodserv][$idlote]=$qtdlote;
            $idprodserv=$r['idprodserv'];
            $tipo=$r['tipo'];
            $tunidadeprod=0;
            $qtdunidadecons=0;
            $tqtd=0;
            $tlote=0;
        }
        
        if($r['idlote']!=$idlote){
            $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
            $arrqtdlote[$idunidade][$idprodserv][$idlote]=$qtdlote;
            $idlote=$r['idlote'];
            $tipo=$r['tipo'];
            $tlote=0;
            $qtdlote=0;
        }
        
        $tunidade=$tunidade+$r['valorconsumo'];
        $tunidadeprod=$tunidadeprod+$r['valorconsumo'];
        $tunidadetipoprod=$tunidadetipoprod+$r['valorconsumo'];
        $qtdunidadecons=$qtdunidadecons+$r['consumido'];
        $tlote=$tlote+$r['valorconsumo'];
        if($r['tipo']=='CONSUMO'){
            $qtdlote=$qtdlote+$r['consumido']; 
        }else{
            $qtdlote=$r['qtdproduzida']; 
        }
            
        $total=$total+$r['valorconsumo'];
     
      
    } 
    $arrun[$idunidade]=$tunidade;
    $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
    $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
    if($tipo=='CONSUMO'){
        $arrqtd[$idunidade][$idprodserv][$idlote]['qtdproduzida']=$qtdunidadecons;
    }else{
        $arrqtd[$idunidade][$idprodserv][$idlote]['qtdproduzida']=$qtdunidadecons;
    }
    $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
    $arrqtdlote[$idunidade][$idprodserv][$idlote]=$qtdlote;
   
    
?>
     
        <div class="panel panel-default" >            
            <div class="panel-body">
<?
        //print_r($arr); die();
    $totalfinal=0;
    while (list($idunidade, $arrtprod) = each($arr)){ 
            $totalfinal=$totalfinal+$arrun[$idunidade];
?>
    
        <div class="panel panel-default" >
            <div class="panel-heading cabecalho" style="background-color: #d1cfcf !important;">
                <table>
                    <tr>
                        <td style="width: 100%"><?= traduzid('unidade', 'idunidade', 'unidade', $idunidade)?></td>
                        <td class="nowrap">R$ <?=number_format(tratanumero($arrun[$idunidade]), 2, ',', '.'); ?></td>
                        <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>" aria-expanded="false"></i></td>
                    </tr>
                </table>
            <div class="hide"><?=print_r($arr)?></div>             
            
                
            </div>
            <div class="panel-body collapse" id="unInfo<?=$idunidade?>">
            <?
            while (list($idtipoprodserv,$arrprod ) = each($arrtprod)){
            $tipoprodserv=traduzid('tipoprodserv','idtipoprodserv','tipoprodserv',$idtipoprodserv);
?>
            <div class="panel panel-default" >
            <div class="panel-heading cabecalho" style="background-color: #c5c5c587 !important;">
                <table>
                    <tr >
                        <td style="width: 100%"><?=$tipoprodserv?></td>
                        <td style="text-align: right;" class='nowrap'>R$ <?=number_format(tratanumero($arruntipoprod[$idunidade][$idtipoprodserv]), 2, ',', '.'); ?></td>
                        <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>_<?=$idtipoprodserv?>" aria-expanded="false"></i></td>
                    </tr>
                </table>   
            </div>
            <div class="panel-body collapse" id="unInfo<?=$idunidade?>_<?=$idtipoprodserv?>" style="padding-top:2px !important" >
<?
              
             while (list($idprodserv,$arrlote ) = each($arrprod)){ 
                $qtdproduzida=0;
                while (list($idlotev,$arrvqtd ) = each($arrqtd[$idunidade][$idprodserv])){ 
                    $qtdproduzida=$qtdproduzida+$arrvqtd['qtdproduzida']; 
                }
?>
           
           <div class="panel panel-default" >
            <div class="panel-heading">
            <table  style="width:100%"  style="width:100%; margin:4px; background: #d4d4d426; height:40px">
                <tr>
                    <th style="width: 100%"><?=number_format(tratanumero($qtdproduzida), 2, ',', '.');?>  <?=traduzid('prodserv', 'idprodserv', 'un', $idprodserv)?> - <?= traduzid('prodserv', 'idprodserv', 'descr', $idprodserv)?> </td><td style="text-align: right;" class="nowrap">R$ <?=number_format(tratanumero($arrunprod[$idunidade][$idprodserv]), 2, ',', '.'); ?> </th>
                    <th><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#prInfo<?=$idunidade?>_<?=$idprodserv?>" aria-expanded="false"></i></th>
                </tr>
            </table>
            </div>
            <div class="panel-body collapse"  id="prInfo<?=$idunidade?>_<?=$idprodserv?>">
            <div class='teste' style="border: 1px solid #ccc;
                    display: flow-root;
                    border-radius: 4px;
                    background: #e6e6e6;
                    padding: 0px 8px 0px 0px;
                    margin: 4px !important;">
            <table   class="table table-striped planilha"  >

        <?     
                $p=0;       
                while (list($idlote,$arrcons ) = each($arrlote)){ 
                   // $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$r['idlotecons']][$r['idloteconsumido']]

                    $sqf="select 
                            l.partida,
                            l.exercicio,
                            l.qtdprod,
                            p.un,
                            p.fabricado,
                            l.idprodservformula,
                            concat(f.rotulo,' ',ifnull(f.dose,' '),' ',p.conteudo,' ',' (',f.volumeformula,' ',f.un,')') as rotulo
                        from lote l 
                            left join prodservformula f on(f.idprodservformula = l.idprodservformula) 
                            join prodserv p on(p.idprodserv=l.idprodserv)
                      where l.idlote=".$idlote;
                    
                    $resif=d::b()->query($sqf) or die("Erro ao buscar  informações do lote consumido: <br>".mysqli_error(d::b()));
                    $rowst=mysqli_fetch_assoc($resif);

                    $prodservun =$rowst['un'];
                    $fabricado =$rowst['fabricado'];
                    $idprodservformula = $rowst['idprodservformula'];
                    $partida=$rowst['partida'];
                    $exercicio=$rowst['exercicio'];
                    $qtdprod=$rowst['qtdprod'];

                    /*
                    $prodservun =traduzid('prodserv', 'idprodserv', 'un', $idprodserv);
                    $fabricado =traduzid('prodserv', 'idprodserv', 'fabricado', $idprodserv);
                    $idprodservformula = traduzid('lote', 'idlote', 'idprodservformula', $idlote);
                    $partida=traduzid('lote', 'idlote', 'partida', $idlote);
                    $exercicio=traduzid('lote', 'idlote', 'exercicio', $idlote);
                    $qtdprod=traduzid('lote', 'idlote', 'qtdprod', $idlote);
                    */
                    $sqli="select o.idobjeto 
                            from lote l 
                             join unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE)
                             ON (o.tipoobjeto = 'modulo' AND l.idunidade = o.idunidade)
                            JOIN "._DBCARBON."._modulo m on m.modulo = o.idobjeto and m.modulotipo = 'lote'
                            where l.idlote=".$idlote;
                            $resi=d::b()->query($sqli) or die("Erro ao buscar  modulo do lote consumido: <br>".mysqli_error(d::b()));
                            $rowi=mysqli_fetch_assoc($resi);
                            if(empty($rowi['idobjeto'])){
                                $link1 = 'lotealmoxarifado';
                            }else{
                                $link1 = $rowi['idobjeto'];
                            }   

                    if(!empty($idprodservformula)){
                       
                        $formula=' - '.$rowst['rotulo'];
                    }else{
                        $formula='';
                    }
                    
                    if($p>0 and  $fabricado=='N'){
?>
                <tr  style='background-color: white;'>
                        <td colspan="5">
                            <br>
                        </td>
                </tr>
                <?
                    }
                    if($fabricado=='N'){
                        $back="background-color: #d4d4d46b;";
                    }else{
                        $back="";
                    }
                ?>
                 <tr style='<?=$back?> font-weight: bold;' >
                    <td style="width: 100%" colspan="6" >
                        <?=number_format(tratanumero($arrqtdlote[$idunidade][$idprodserv][$idlote]), 2, ',', '.'); ?>
                        &nbsp;
                        <?=$prodservun?>&nbsp;&nbsp;-
                        <a title="Cadastro do produto" target="_blank" href="./?_modulo=<?=$link1?>&_acao=u&idlote=<?=$idlote?>">
                          <?=$partida?>/<?=$exercicio?>
                        </a>  
                        <?=$formula?>
               
                        <?if( $fabricado=='Y'){?>                        
                            <i class="fa fa-money hoverazul btn-lg pointer" title="Ver insumos descartados do produto" onclick="mostraval(<?=$idlote?>)"></i>
                        <div id="htmlmodal_<?=$idlote?>" style="display: none">
                        <div id="valaorform<?=$idlote?>" style="display: none">
                        <div class="row">
                           <div class="col-md-12">
                           
                           <div class="panel panel-default" style="margin-top: 0px !important;">
                               <div class="panel-heading">
                                <a title="Cadastro do produto" target="_blank" href="./?_modulo=<?=$link1?>&_acao=u&idlote=<?=$idlote?>">
                                   <?=$partida?>/<?=$exercicio?>
                                </a>
                               </div>
                                   <div class="panel-body" style="font-size:12px;padding-top: 0px !important;">
                               <?                               
                                   cprod::listavalorlote($idlote,1);
                               ?>	
                                    <div class="col-md-12" style="font-weight: bold;">
                                        <div class="col-md-2">
                                            Valor Unitário R$:
                                        </div>
                                        <div class="col-md-2"></div>
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <span style="float:right">
                                                <?echo number_format(($arrloteprod[$idunidade][$idprodserv][$idlote]/$qtdprod), 2, ',', '.');?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="font-weight: bold;">
                                        <div class="col-md-2">
                                            Valor Total R$:
                                        </div>
                                        <div class="col-md-2"></div>
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <span style="float:right">
                                                <?echo number_format(tratanumero($arrloteprod[$idunidade][$idprodserv][$idlote]), 2, ',', '.');?>
                                            </span>
                                        </div>
                                    </div>
                               </div>
                               </div>	
                           </div>
                        </div>
                        </div>
                        </div>
                        <?}?>
                    </td> 
                    <td style="text-align: right; " class="nowrap">
                        R$ <?=number_format(tratanumero($arrloteprod[$idunidade][$idprodserv][$idlote]), 2, ',', '.'); ?>                   
                    </td>
                </tr>
<?
if( $fabricado=='N'){
    ?>
        <tr> 
            <th>Qtd</th>
            <th>Un</th>          
            <th>Partida</th> 
            <th>Consumido Por</th>
            <th>Consumido Em</th>          
            <th class="nowrap">Valor Un</th>
            <th>Valor</th>
        </tr>

    <?
                        while (list($idlotecons,$arrvalue ) = each($arrcons)){

                            if($idlote!=$idlotegrupo){
                                $idlotegrupo=$idlote;
                                $partidagrupo =traduzid('lote', 'idlote', 'partida', $idlote);
                                $exerciciogrupo =traduzid('lote', 'idlote', 'exercicio', $idlote);
                                $_idprodserv =traduzid('lote', 'idlote', 'idprodserv', $idlote);
                                $_produto =traduzid('prodserv', 'idprodserv', 'descr', $idprodserv);
                                   
                            }
                        while (list($idloteconsumido,$value ) = each($arrvalue)){     
                            $sqli="select o.idobjeto from lote l join unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND l.idunidade = o.idunidade)
                            JOIN "._DBCARBON."._modulo m on m.modulo = o.idobjeto and m.modulotipo = 'lote'
                            where l.idlote=".$idloteconsumido;
                            $resi=d::b()->query($sqli) or die("Erro ao buscar  modulo do lote consumido: <br>".mysqli_error(d::b()));
                            $rowi=mysqli_fetch_assoc($resi);
                            if(empty($rowi['idobjeto'])){
                                $link = 'lotealmoxarifado';
                            }else{
                                $link = $rowi['idobjeto'];
                            }   
    ?>
                    <tr >
                        <td    title="<?=$value['strcon']?>">
                        <?=number_format(tratanumero($value['consumido']), 2, ',', '.'); ?>
                            
                        </td>
                        <td><?=$value['un']?></td>
                        <!--td> <?=$value['produtoconsumido']?></td -->
                        <td> 
                            <a title="Cadastro do produto" target="_blank" href="./?_modulo=<?=$link?>&_acao=u&idlote=<?=$idloteconsumido?>">
                                <?=$value['loteconsumido']?>
                            </a>
                        </td>
                        <td> <?=$value['criadopor']?></td>
                        <td> <?=dmahms($value['criadopor'])?></td>
                        <td class="nowrap">R$ <?=number_format(tratanumero($value['valoritem']), 2, ',', '.');?></td>
                        
                        <td style="text-align: right;"class="nowrap" >R$ <?= number_format(tratanumero($value['valorconsumo']), 2, ',', '.');?></td>
                    </tr>
    
    <?         
                            }
                        }
                    }
                    $p++;
                } 
?>
                </table>
            </div>
            </div>
            </div>
<?                
            }   
?>
         
         </div>
        </div>

<?}?>
            
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
                            <th style="text-align: right;" class="nowrap">R$ <?=number_format(tratanumero($totalfinal), 2, ',', '.');?></th>
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

function descarte($res){

    $arr=array();
    $arrun=array();
    $arruntipoprod=array();
    $arrunprod=array();
    $arrloteprod=array();  
    $arrqtd=array(); 
    $tunidade=0;
    $tunidadetipoprod=0;
    $tunidadeprod=0;    
    $tlote=0;
    $total=0;
   
    global $arr,$valor ;
    while($r = mysqli_fetch_assoc($res)){ 
        $valor=0;          

        if(empty($idunidade)){$idunidade=$r['idunidade'];  $idtipoprodserv=$r['idtipoprodserv']; $idprodserv=$r['idprodserv']; $idlote=$r['idlote']; }

        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['idlote']=$r['idlote'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['partida']=$r['partida'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['exercicio']=$r['exercicio'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['descr']=$r['descr'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['unidade']=$r['unidade'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['qtdd']=$r['qtdd'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['qtdd_exp']=$r['qtdd_exp'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['qtdprod']=$r['qtdprod'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['qtdc']=$r['qtdc'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['qtdc_exp']=$r['qtdc_exp'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['obs']=$r['obs'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['un']=$r['un'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['fabricado']=$r['fabricado'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['comprado']=$r['comprado'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['qtdpadraof']=$r['qtdpadraof'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['criadopor']=$r['criadopor'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['criadoem']=$r['criadoem'];
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['idprodservformula']=$r['idprodservformula'];
        
        if($r['qtdd']>0){
            $qtd=$r['qtdd'];
            $percentual=$r['qtdd']/$r['qtdprod'];
        }else{
            $qtd=$r['qtdc'];
            $percentual=$r['qtdc']/$r['qtdprod'];
        }
        

       
/*
        if($r['fabricado']=='Y'){       
           
            $valorlote=cprod::buscavalorlote($r['idlote'],1,'Y');
           // echo($r['idlote'].'='.$valorlote.'<br>');
            $valorx = (( $valorlote/$r['qtdprod']) * $qtd);   
        }else{
*/
            
            $valorx=$r['vlrlote'] * $qtd;
           // cprod::buscavaloritem($r['idprodserv'], $qtd);
 //       }
   
        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlotecons']][$r['idlote']]['valor']=$valorx;
        
       // print_r($arr);
        if($r['idunidade']!=$idunidade){
            $arrun[$idunidade]=$tunidade;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
            $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
            $idunidade=$r['idunidade'];
            $tunidade=0;
            $tunidadeprod=0;
            $tqtd=0;
            $tlote=0;
        }   
        if($r['idtipoprodserv']!=$idtipoprodserv){
            $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;
            $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
            $arrloteprod[$idunidade][$idprodserv][$idlote]=$tlote;
            $idtipoprodserv=$r['idtipoprodserv'];
            $tunidadetipoprod=0;
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
        if($r['qtdd']>0){
            $tunidade=$tunidade-$valorx;
            $tunidadeprod=$tunidadeprod-$valorx;
            $tunidadetipoprod=$tunidadetipoprod-$valorx;
            $tlote=$tlote-$valorx;        
            $total=$total-$valorx;
        }else{
            $tunidade=$tunidade+$valorx;
            $tunidadeprod=$tunidadeprod+$valorx;
            $tunidadetipoprod=$tunidadetipoprod+$valorx;
            $tlote=$tlote+$valorx;        
            $total=$total+$valorx;
        }


    }
    $arrun[$idunidade]=$tunidade;
    $arrunprod[$idunidade][$idprodserv]=$tunidadeprod;
    $arruntipoprod[$idunidade][$idtipoprodserv]=$tunidadetipoprod;


    ?>

<?
    
 while (list($idunidade, $arrtprod) = each($arr)){ 
//print_r($arrprod);
    $sl="select o.idobjeto from unidadeobjeto o 
                join "._DBCARBON."._modulo m on (m.modulo = o.idobjeto and m.modulotipo = 'lote' and m.status = 'ATIVO')                                            
            where (o.tipoobjeto='modulo' 				
            and o.idunidade = ".$idunidade.")";
    $qr1 = d::b()->query($sl);
    $rowq=mysqli_fetch_assoc($qr1);
    $lkmodulo=$rowq['idobjeto'];
      
?>

    <div class="panel panel-default" >
        <div class="panel-heading cabecalho" style="background: #d4d4d4;">
            <table>
                <tr>
                    <td style="width: 100%"><b><?= traduzid('unidade', 'idunidade', 'unidade', $idunidade)?></td>
                    <td></td>
                    <td class="nowrap">R$ <?=number_format(tratanumero($arrun[$idunidade]), 2, ',', '.');?></td>
                    <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>" aria-expanded="false"></i></td>
                </tr>
            </table> 
        </div>
        <div class="panel-body collapse" id="unInfo<?=$idunidade?>">         
        
        <?
    while (list($idtipoprodserv,$arrprod ) = each($arrtprod)){
            $tipoprodserv=traduzid('tipoprodserv','idtipoprodserv','tipoprodserv',$idtipoprodserv);
?>
            <div class="panel panel-default" >
            <div class="panel-heading cabecalho" style="background-color: #c5c5c587 !important;">
                <table>
                    <tr >
                        <td style="width: 100%"><?=$tipoprodserv?></td>
                        <td style="text-align: right;" class='nowrap'>R$ <?=number_format(tratanumero($arruntipoprod[$idunidade][$idtipoprodserv]), 2, ',', '.'); ?></td>
                        <td><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#unInfo<?=$idunidade?>_<?=$idtipoprodserv?>" aria-expanded="false"></i></td>
                    </tr>
                </table>   
            </div>
            <div class="panel-body collapse" id="unInfo<?=$idunidade?>_<?=$idtipoprodserv?>" style="padding-top:2px !important" >
<?   

        while (list($idprodserv,$arrlotecons ) = each($arrprod)){ 
           // print_r($arrlotecons);
?>
       
       <div class="panel panel-default" >
        <div class="panel-heading">
        <table  style="width:100%">
            <tr>
                <th style="width: 100%">  <?= traduzid('prodserv', 'idprodserv', 'descr', $idprodserv)?> </td><td style="text-align: right;" class="nowrap">R$ <?=number_format(tratanumero($arrunprod[$idunidade][$idprodserv]), 2, ',', '.'); ?> </th>
                <th><i class="fa fa-arrows-v fa-2x cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#prInfo<?=$idunidade?>_<?=$idprodserv?>" aria-expanded="false"></i></th>
            </tr>
        </table>
        </div>
        <div class="panel-body collapse"  id="prInfo<?=$idunidade?>_<?=$idprodserv?>"> 
        <table   class="table table-striped planilha" >
         
                    
                    <th class="col-md-1" >Crédito</th>
                    <th class="col-md-1" >Débito</th>     
                    <th class="col-md-1" >Un</th> 
                    <th class="col-md-2">Partida</th>
                    <th class="col-md-3" >Obs</th>
                    <th class="col-md-1" >Descartado Por</th>
                    <th class="col-md-2" >Descartado Em</th>
                    <th class="col-md-1" style="text-align: right;" >Valor</th>
<?              
            $p=0;       
            while (list($idlotecons,$arrcons ) = each($arrlotecons)){
                //print_r($arrcons); 
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
                
                if($p>0){
?>
            <tr  style='background-color: white;'>
                    <td colspan="5">
                        <br>
                    </td>
            </tr>
            <?
                }
           
                while (list($idlotegrupo,$arrvalue ) = each($arrcons)){ 
                   // print_r($arrvalue);

                   // if($idlote!=$idlotegrupo){
                    $partidagrupo =traduzid('lote', 'idlote', 'partida', $idlotegrupo);
                    $exerciciogrupo =traduzid('lote', 'idlote', 'exercicio', $idlotegrupo);
                    $_idprodserv =traduzid('lote', 'idlote', 'idprodserv', $idlotegrupo);
                    $_produto =traduzid('prodserv', 'idprodserv', 'descr', $_idprodserv);
                    if($arrvalue['qtdc']>0){
                        $percentual=$arrvalue['qtdc']/$arrvalue['qtdprod'];
                        $qtdx=$arrvalue['qtdc'];
                    }else{
                        $percentual=$arrvalue['qtdd']/$arrvalue['qtdprod'];
                        $qtdx=$arrvalue['qtdd'];
                    }
                    

?>
                <tr style="background-color:white">
                   
 
                    <td class="col-md-1" ><?if($arrvalue['qtdc']>0){echo recuperaExpoente(tratanumero($arrvalue["qtdc"]),$arrvalue['qtdc_exp']); $sinal=" ";}else{ echo '';}?></td>
                    <td class="col-md-1" ><?if($arrvalue['qtdd']>0){echo "-".recuperaExpoente(tratanumero($arrvalue["qtdd"]),$arrvalue['qtdd_exp']); $sinal="-";}else{ echo '';}?></td>
                  
                    <td class="col-md-1" ><?=$arrvalue['un']?></td>
                    <td class="col-md-1" >
                        <a title="Lote" class="pointer" onclick="janelamodal('?_modulo=<?=$lkmodulo?>&_acao=u&idlote=<?=$arrvalue["idlote"]?>')">
                            <?=$arrvalue['partida']?>/<?=$arrvalue['exercicio']?>
                        </a>
                        <?if($arrvalue['fabricado']=='Y'){?>
                        
                             <i class="fa fa-money hoverazul btn-lg pointer" title="Ver insumos descartados do produto" onclick="mostraval(<?=$idlotecons?>)"></i>
                        <div id="htmlmodal_<?=$idlotecons?>" style="display: none">
                        <div id="valaorform<?=$idlotecons?>" style="display: none">
                            <div class="row">
                                <div class="col-md-12">
                                
                                <div class="panel panel-default" style="margin-top: 0px !important;">
                                    <div class="panel-heading">
                                        <?=$arrvalue['partida']?>/<?=$arrvalue['exercicio']?>
                                    </div>
                                        <div class="panel-body" style="font-size:12px;padding-top: 0px !important;">
                                    <?                               
                                        cprod::listavalorlote($arrvalue['idlote'],$percentual);
                                    ?>
                                    <div class="col-md-12" style="font-weight: bold;">
                                        <div class="col-md-2">
                                            Valor Unitário R$:
                                        </div>
                                        <div class="col-md-2"></div>
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <span style="float:right">
                                                <?echo number_format(tratanumero($arrvalue['valor']/$qtdx), 2, ',', '.');?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="font-weight: bold;">
                                        <div class="col-md-2">
                                            Valor Total R$:
                                        </div>
                                        <div class="col-md-2"></div>
                                        <div class="col-md-6"></div>
                                        <div class="col-md-2">
                                            <span style="float:right">
                                                <?echo number_format(tratanumero($arrvalue['valor']), 2, ',', '.');?>
                                            </span>
                                        </div>
                                    </div>	
                                    </div>
                                    </div>	
                                </div>
                            </div>
                            </div>
                        </div>
                        <?}?>
                    </td>                   
                    <td class="col-md-3" ><?=$arrvalue['obs']?></td>
                    <td class="col-md-1" ><?=$arrvalue['criadopor']?></td>
                    <td class="col-md-1" ><?=dmahms($arrvalue['criadoem'])?></td>
                    <td class="col-md-1"class="nowrap" style="text-align: right;" >R$ <?=$sinal?> <?=number_format(tratanumero($arrvalue['valor']), 2, ',', '.');?></td>
                </tr>
                <?
                   // } //if($idlote!=$idlotegrupo){
                    
                }
                $p++;
            } 
?>
            </table>
        </div>
        </div>
<?                
        }//   while (list($idprodserv,$arrlotecons ) = each($arrprod)){    
?>
 </div>
    </div>
    <?                
        } 
?>   
            
        </div>
    </div>
<?
 }// while (list($idunidade, $arrprod) = each($arr)){ 
?>         
        <div class="panel panel-default" >
            <div class="panel-heading" style='background-color: wheat;'>                       
                <table style="width:100%">
                <tr>
                    <th style="width: 100%">Total:</th>
                    <th style="text-align: right;" class="nowrap">R$ <?=number_format(tratanumero($total), 2, ',', '.');?></th>
                    <th></th>
                </tr>
                </table>
            </div>
        </div>                
   
<?

}//fim descarte


function buscainsumos($idloteconsumido,$percentual){
    global $r,$arr,$tunidade,$tunidadeprod,$tunidadetipoprod,$tlote,$total ;
     $sqlx="select lc.idlote,t.idtipoprodserv,t.tipoprodserv,lc.partida,lc.exercicio,ifnull(l.qtdprod,0) as qtdprod,sum(c.qtdd) as consumido,p.un,concat(l.partida,'/',l.exercicio) as loteconsumido,l.idlote as idloteconsumido,
                    p.descr as produtoconsumido,
                    ifnull(ni.total,0) as total,
                    round(ifnull(l.vlrlote,0),2) as valoritem
                    ,p.fabricado
                from lotecons c 
                join lote l on(l.idlote=c.idlote)
                left join nfitem ni on(ni.idnfitem = l.idnfitem)
                join prodserv p on(p.idprodserv=l.idprodserv)
                join tipoprodserv t on(p.idtipoprodserv=t.idtipoprodserv)
                join lote lc on(lc.idlote=c.idobjeto)
                    where c.idobjeto= ".$idloteconsumido." 
                    and c.tipoobjeto='lote' 
                    and c.qtdd>0 group by t.tipoprodserv,l.idlote order by p.descr";
            $resx = d::b()->query($sqlx) or die("A Consulta dos consumos do insumo falhou :".mysql_error()."<br>Sql:".$sqlx); 
            $qtdx= mysqli_num_rows($resx);
            if($qtdx>0){
                $valoritcons=0;
                $consumido=0;
               
                while($rowx=mysqli_fetch_assoc($resx)){
                    if($rowx['fabricado']=='N'){
                        
                        $consumido=round(($percentual*$rowx['consumido']),4);                    
                        $strcon=$consumido."=".$percentual."*".$rowx['consumido'];
                        $valoritcons=round($consumido*$rowx['valoritem'],2);
  
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['idlote']=$rowx['idlote'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['partida']=$rowx['partida'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['exercicio']=$rowx['exercicio'];
                        //$arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['qtdprod']=$rowx['qtdprod'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['loteconsumido']=$rowx['loteconsumido'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['consumido']=$consumido;
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['strcon']=$strcon;
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['produtoconsumido']=$rowx['produtoconsumido'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['un']=$rowx['un'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['valoritem']=$rowx['valoritem'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['valorconsumo']=$valoritcons;

                        $tunidade=$tunidade+$valoritcons;
                        $tunidadeprod=$tunidadeprod+$valoritcons;
                        $tunidadetipoprod=$tunidadetipoprod+$valoritcons;
                        $tlote=$tlote+$valoritcons;        
                        $total=$total+$valoritcons;
                    }else{
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['idlote']=$rowx['idlote'];
                        //$arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idloteconsumido']]['qtdprod']=$rowx['qtdprod'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['loteconsumido']=$rowx['loteconsumido'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['consumido']=0;
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['strcon']=0;
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['produtoconsumido']=$rowx['produtoconsumido'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['un']=$rowx['un'];
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['valoritem']=0;
                        $arr[$r['idunidade']][$r['idtipoprodserv']][$r['idprodserv']][$r['idlote']][$rowx['idlotecons']][$rowx['idloteconsumido']]['valorconsumo']=0;
                       
                        
                        $percentualcon=$rowx['consumido']/$rowx['qtdprod'];
                        $percentualcon=$percentual*$percentualcon;
                        //$idlotegrupo=$rowx['idloteconsumido'];
                        buscainsumos($rowx['idloteconsumido'],$percentualcon);                       
                    }

                }
            }
}//function buscainsumos($idloteconsumido){

   function buscavalorloterateio($inidlote,$percentual,$zerar){ 
       if($zerar=='Y'){
            global  $valor;
            $valor = 0;
       }else{
           global $valor;
        }
      
    $sql="select l.idlote,c.qtdd,ifnull(l.vlrlote,0) as vlrlote, 
            CASE
                WHEN ll.qtdprod  < 1 THEN 1   
                ELSE ll.qtdprod 
            END as qtdprod,
            CASE
                WHEN l.qtdprod  < 1 THEN 1   
                ELSE l.qtdprod 
            END as qtdproduzido,
            l.idprodservformula,p.comprado,p.fabricado,p.descr
            from lotecons c 
            join lote l on(l.idlote= c.idlote) 
            join prodserv p on(p.idprodserv=l.idprodserv)  
            join lote ll on(ll.idlote=c.idobjeto)    
        where c.idobjeto =".$inidlote." and c.tipoobjeto ='lote' and c.qtdd>0";
    $res= d::b()->query($sql);
    $valorc=0;
    while($row=mysqli_fetch_assoc($res)){
        if($row['fabricado']=='Y'){     
            $percentualcon=$row['qtdd']/$row['qtdproduzido'];
            $percent=$percentual*$percentualcon;
            $valorform=buscavalorloterateio($row['idlote'],$percent,'N');
           // echo($row['idlote']." ". $valorlote."<br>");
           //$valorf =$valorf + (( $valorform/$row['qtdprod']) * $row['qtdd']);                 
        }elseif($row['fabricado']=='N' and $row['vlrlote']>0){
           
            $valorcp=($row['vlrlote']*$row['qtdd'])*$percentual;              	 
           // $valoritem=$valoritem+$valor;
            $valorc=$valorc+$valorcp;
            // echo($valoritem.'<br>');
        }                    
    }//while($row=mysqli_fetch_assoc($res)){   
        $valor=$valor+  ($valorc);    
    return $valor;


      }//function buscarvalorform($inidprodservformula,$inidplantel){
?>
</div>
</div>
<script>
    
function pesquisar(vthis){
    //$(vthis).addClass( "blink" );
    $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>');
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var idtipoprodserv = $("[name=idtipoprodserv]").val();
    var idprodserv = $("[name=idprodserv]").val();
    var pesquisa = $("[name=pesquisa]").val();
    var idempresa = $("[name=idempresa]").val();
    
    
    var str="idempresa="+idempresa+"&vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&idtipoprodserv="+idtipoprodserv+"&idprodserv="+idprodserv+"&pesquisa="+pesquisa ;
    CB.go(str);
}

$(document).keypress(function(e) {
    if(e.which == 13){
        pesquisar();
    }
});


CB.preLoadUrl = function(){
	//Como o carregamento é via ajax, os popups ficavam aparecendo após o load
	$(".webui-popover").remove();
}

$(".oEmailorc").webuiPopover({
	trigger: "hover"
	,placement: "right"
	,delay: {
        show: 800,
        hide: 0
    }
});
function imprimir(){
    
    window.print();
   
}
function selecionartipo(vthis) {
    var empresa = $(vthis).val()
    $.ajax({
        type: "get",
        url : "ajax/atulizatipoprod.php",
        data: {empresa:empresa},

        success: function(data){
            $("[name=idtipoprodserv]").empty(); 
            $("[name=idtipoprodserv]").append("<option></option>"); 
            try {
                var json = JSON.parse(data)
                $.each(json, function(key,value) {
                $("[name=idtipoprodserv]").append($("<option value='"+value.idtipoprodserv+"'>"+value.tipoprodserv+"</option>"));
                });
            } catch (err) {
                console.log('erro no ajax')
            }
        },

        error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status); 
        }
    });
}
function mostraval(inid){
  
  let strCabecalho = "</strong>Iten(s) utilizado(s)&nbsp;&nbsp;</strong>";
  let htmloriginal = $("#valaorform"+inid).html();
  let objfrm = $(htmloriginal);

  CB.modal({
    titulo: strCabecalho,
    corpo: objfrm.html(),
    classe: 'noventa',
    aoAbrir: function(vthis){
        $("#valaorform"+inid).remove();
        CB.oModal.find('span[href^="#collapse-vallote-"]').on('click', function(){
            let vth = $(this);
            let kHref = CB.oModal.find(vth.attr('href'));

            if(kHref.hasClass('hidden')){
                kHref.removeClass('hidden');
                vth.children('i').removeClass('fa-angle-right').addClass('fa-angle-down');
                vth.parent().parent().css('font-weight', 'bold');
            }else{
                kHref.addClass('hidden');
                vth.children('i').removeClass('fa-angle-down').addClass('fa-angle-right');
                vth.parent().parent().css('font-weight', 'normal');
            }
        });
        let vTotal = calculaValorAcumuladoPorIdlotecons();
		console.log(`Valor Total Acumulado: ${vTotal}`);
	},
    aoFechar: function(data){
        $("#htmlmodal_"+inid).append(`
            <div id="valaorform${inid}" style="display:none;">
                <div class="row">
                    ${data.corpo}
                </div>
            </div>
        `);
    }
  });
	  
}//function financeiro(inidnfitem,inlinha){

function calculaValorAcumuladoPorIdlotecons (idlotecons = 0, $objPai = null, lvl = 0) {
	let $objFilho = ($objPai === null) 
						? CB.oModal.find(`div[lvl='${lvl}']`)
						: $objPai.siblings(`#collapse-vallote-${idlotecons}`).find(`div[lvl='${lvl}']`);

	var vTotal = 0;

	$objFilho.each(function(i,o){
		let $o = $(o);
		
		if($o.children('[vallote]').length > 0){
			vTotal += parseFloat($o.children('[vallote]').attr('vallote')) || 0;

		}else if($o.children('[idlotecons-valloteacumulado]').length > 0){

			let idlotecons = $o.children('[idlotecons-valloteacumulado]').attr('idlotecons-valloteacumulado');
			vTotal += calculaValorAcumuladoPorIdlotecons(idlotecons, $o, lvl + 1);
		}

	});

	if($objPai != null){
		let vTotalFormatado = parseFloat(vTotal.toFixed(2))
					.toLocaleString('en')
					.replace('.', '_')
					.replaceAll(',', '.')
					.replace('_', ',');
		if(vTotalFormatado == "0")
			vTotalFormatado += ",00";
		$objPai.children('[idlotecons-valloteacumulado]').html(`
			<span style="float:right" title=" R$: ${vTotalFormatado}">
				R$: ${vTotalFormatado}
			</span>
		`);
	}

	return vTotal;
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>