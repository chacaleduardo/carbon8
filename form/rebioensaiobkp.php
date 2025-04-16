<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
	include_once("../inc/php/cbpost.php");
}

?>

<style>
i{
    padding-left: 0px !important; 
    padding-right: 5px !important;    
    padding-top: 3px !important;
    padding-bottom: 3px !important;

}
.linha{  
    padding:0;  
    clear:both; 

}        
.coluna{      
     height:100%;  
     border-bottom: 1px dashed #000000;  
     width:100%;        
     float:left; 
     margin:0;  
     text-align: center;
     background-color :white;
 } 
.linhacab{  
    padding:0;  
    clear:both;            
    display: inline-block;
    vertical-align: top;
    margin: 0px;
    background-color :white;
    margin-right: 1px;
    width: 12%;
    border: 1px dashed #A9A9A9;
    height: 100%;
    min-height: 550px;
} 
.colunainv{      
    height:100%;  
    border: 1px solid #A9A9A9;  
    width:95%; 
    float:left;  
    position:relative;
    text-align: center;
    background-color :#E0E0E0;           
} 

</style>
</style>
<div class="row">
    <div class="col-md-12" >
        <div class="panel panel-default" >  
          <div class="panel-body">    
            <div class="col-md-2">
		<div class="col-md-12">
                <div class="panel panel-default" >
                   <div class="panel-heading">
                        <a  class="fa fa-bar-chart pointer hoverazul"  onclick="ShowHideDIV(5)"> GERAL</a>
                   </div>
                </div>
		</div>
		<div class="col-md-12">
                    <div class="panel panel-default" >
                       <div class="panel-heading">
                                    <a  class="fa fa-user pointer hoverazul"  onclick="ShowHideDIV(8)"> TERCEIRO</a>  
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <div class="col-md-2">
                    <div class="panel panel-default" >
                       <div class="panel-heading">
                                    <a  class="fa fa-home pointer hoverazul"  onclick="ShowHideDIV(7)"> INCUBADORA</a>  
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="panel panel-default" >
                       <div class="panel-heading">
                                    <a  class="fa fa-home pointer hoverazul"  onclick="ShowHideDIV(1)"> PINTEIRO</a>                                
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="panel panel-default" >
                       <div class="panel-heading">
                                     <a  class="fa fa-home pointer hoverazul"  onclick="ShowHideDIV(2)"> INATIVADA</a> 
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="panel panel-default" >
                       <div class="panel-heading">
                                    <a  class="fa fa-home pointer hoverazul"  onclick="ShowHideDIV(3)"> VIVA</a>                                 
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="panel panel-default" >
                       <div class="panel-heading">
                                    <a  class="fa fa-home pointer hoverazul"  onclick="ShowHideDIV(4)"> BIOCONTIDO</a>                               
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="panel panel-default" >
                       <div class="panel-heading">
                                    <a  class="fa fa-home pointer hoverazul"  onclick="ShowHideDIV(6)"> CAMUNDONGOS</a>  
                        </div>
                    </div>
                </div>
		
            </div>
          </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12" >
        <div class="panel panel-default" >  
	     <div id="servicos" style=" display: block; font-size:12px;" >
            <div class="col-md-2">
                <div class="panel panel-default" >
		    <div class="panel-heading">SERVIàOS BIOTàRIO</div>
                   <div class="panel-body">

                    <?
                    //buscar os serviços
                    servicospendentes();
                    ?>
                   </div>
               </div> 
            </div> 
	     </div>
            <div id="nascedouro" style=" display: block; font-size:12px;" >
                <div class="col-md-5">
                   <div class="panel panel-default" >
                       <div class="panel-heading">INCUBADORA</div>
                       <div class="panel-body">
<?
                        incubadora();
?>                         
                       </div>
                   </div>                    
                </div>  
                <div class="col-md-5">
                   <div class="panel panel-default" >
                       <div class="panel-heading">PINTEIRO</div>
                       <div class="panel-body">
<?
                        pinteiro();
?>
                       </div>
                   </div>                    
                </div> 
            </div>           
            <div id="inativada" style=" display: none; font-size:12px;" >
                <div class="col-md-10">
                   <div class="panel panel-default" >
                       <div class="panel-heading">INATIVADA</div>
                       <div class="panel-body">
<?
                    listalocacoes(4,11);
?>
                       </div>
                   </div>                    
                </div>                
            </div>
               
            
            <div id="viva" style=" display: none; font-size:12px;" >
                <div class="col-md-10">
                   <div class="panel panel-default" >
                       <div class="panel-heading">VIVA</div>
                       <div class="panel-body">
<?
                    listalocacoes(12,19);
?>
                       </div>
                   </div>                    
                </div>                
            </div>
            
            <div id="biocontido" style="display: none; font-size:12px;" >
                <div class="col-md-10">
                   <div class="panel panel-default" >
                       <div class="panel-heading">BIOCONTIDO</div>
                       <div class="panel-body">
<?
                    listalocacoes(20,24);
?>
                       </div>
                   </div>                    
                </div>                
            </div>
            
            <div id="Roedores" style="display: none; font-size:12px;" >
                <div class="col-md-4">
                   <div class="panel panel-default" >
                       <div class="panel-heading">REPRODUààO</div>
                       <div class="panel-body">
<?
                       reproducao();
?>                         
                       </div>
                   </div>                    
                </div>  
                <div class="col-md-6">
                   <div class="panel panel-default" >
                       <div class="panel-heading">ESTUDOS</div>
                       <div class="panel-body">
<?
                        estcamundongo();
?>
                       </div>
                   </div>                    
                </div> 
            </div>
	    
	    <div id="terceiro" style="display: none; font-size:12px;" >
		<div class="col-md-3">
                   <div class="panel panel-default" >
                       <div class="panel-heading">SERVIàOS TERCEIRO</div>
                       <div class="panel-body">
<?
                        servicospendentesterc();
?>
                       </div>
                   </div>                    
                </div> 
                <div class="col-md-7">
                   <div class="panel panel-default" >
                       <div class="panel-heading">ESTUDOS</div>
                       <div class="panel-body">
<?
                        estterceiro();
?>
                       </div>
                   </div>                    
                </div> 
            </div>
            
            <div id="geral" style="display: none; font-size:12px;" >
                <div class="col-md-10">
                   <div class="panel panel-default" >
                       <div class="panel-heading">GERAL</div>
                       <div class="panel-body">
<?
                        geral();
?>
                       </div>
                   </div>                    
                </div>                
            </div> 
            
            <div id="incubadoradp" style="display: none; font-size:12px;" >
                <div class="col-md-10">
                   <div class="panel panel-default" >
                       <div class="panel-heading">INCUBADORA</div>
                       <div class="panel-body">
<?
                        incubadoradp();
?>
                       </div>
                   </div>                    
                </div>                
            </div>            
        </div> 
        </div>        
    </div>
</div>

<?
//Listar locaçàµes
function listalocacoes($l1,$l2){
    $sql0="select concat(tipo,' ',right(local, 2)) as rot,l.* 
            from local l 
            where idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." 
            and idlocal between ".$l1." and ".$l2."";
    $res0=d::b()->query($sql0) or die("erro ao buscar locais biobox sql=".$sql0);
    $l=0;	
    while($row0=mysqli_fetch_assoc($res0)){        
?>
        <div class="linhacab">
                <div class="colunainv" ><?=$row0['rot']?></div>		
	<?
		$sqlb="select
                        b.idregistro,
                        e.idlocalensaio,
                        b.tipo,
                        b.idbioensaio,
                        concat(UPPER(LEFT(b.estudo,20)),' ',UPPER(b.partida)) as bioensaio,
                        b.cor,
                        b.qtd,
                        UPPER(LEFT(e.ensaio,20)) as ensaio,				
                        l.local,
                        e.idlocal,
                        dma(r.iniciobio) as inicio1,
                        dma(r.fimbio) as fim1,
                        if(CURDATE()>=r.iniciobio,'S','N') as mmin,
                        if(CURDATE()<=r.fimbio,'S','N') as mmax,
                        (DATEDIFF(curdate(),r.fiminc)) AS diasvida,
                        e.obs,
                        e.gaiola,
                        b.status				
                        from bioensaio b,localensaio e,local l,vwreservabioensaio r
                        where b.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                        and r.fimbio >= CURDATE()
                        and e.status !='PENDENTE'
                        and b.status  not in('CANCELADO','FINALIZADO')
                        and r.idbioensaio=b.idbioensaio
                        and b.idbioensaio = e.idbioensaio
                        and e.idlocal = l.idlocal	
                        and l.idlocal=".$row0['idlocal']." order by e.idlocal,e.status desc,r.iniciobio";
		
		$resb=d::b()->query($sqlb) or die("Erro ao buscar locaçàµes dos biobox de aves sql=".$sqlb);
		$qtdb=mysqli_num_rows($resb);
		if($qtdb<1){
?>
                <div class="linha"  >
                    <div class="coluna" style="opacity: 0.4; border:none;" >
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>		
                </div>	
<?
		}
		while($rowb=mysqli_fetch_assoc($resb)){
                    if($rowb['mmin']=='S' and $rowb['mmax']=='S'){
                        $stropacity="";
                    }else{
                        $stropacity="opacity: 0.4; ";
                    }
                    if($rowb['diasvida']>0){
                        $diasvida=$rowb['diasvida']." dias";
                    }else{
                        $diasvida="";
                    }
                    if($rowb['status']=='ATIVO'){
                        $cor="black";
			$corf="#aefdb46b";//verde
                    }elseif($rowb['status']=='DISPONIVEL'){
                        $cor="red";
			$corf="#f8d2b76b;";//vermelho
                    }elseif($rowb['status']=='RESERVADO'){
                        $cor="green";
			$corf="#eaeea86b";//
                    }		   
	?>		
            <div class="linha">
                <div class="coluna" style="<?=$stropacity?> background-color:<?=$corf?>; cursor: pointer;" title="<?=$rowb['obs']?>">
                    <table>
                        <tr >
                            <td style="font-size: 11px;" align="center">
                                 
                                <a title="Ver Bioensaio" href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&reload=Y&idbioensaio=<?=$rowb['idbioensaio']?>')">
                                    B<?=$rowb['idregistro']?>-<?=$rowb['bioensaio']?>
                                </a>
                            </td>
                        </tr>
                        
                        <tr style="color:<?=$cor?>" >
                            <td style="font-size: 11px;" align="center" nowrap >
                                <i title="<?=$rowb['status']?>" class="fa fa-bookmark <?=$cor?>"></i>
                            <?if(!empty($rowb['gaiola'])){?>
                                Gaiola <?=$rowb['gaiola']?>	
                             <?}?>
                            </td>
                        </tr>
                       
                        <tr  style="color:<?=$cor?>">
                            <td style="font-size: 11px;" align="center">
                            <?echo($rowb['qtd']." Animais ")?> <?echo($rowb['tipo'])?> <font color="red"><?=$diasvida?></font> 
                            </td>
                        </tr>		
                        <tr  style="color:<?=$cor?>" >
                            <td style="font-size: 10px;" align="center" nowrap >
                            <?echo($rowb['inicio1']." - ".$rowb['fim1']);?>					
                            </td>
                        </tr>
                    </table>
                </div>		
            </div>
<?			
		}//while($rowb=mysqli_fetch_assoc($resb)){
	?>	
        </div>	
<?
    }// while($row0=mysqli_fetch_assoc($res0)){    
}//function listalocacoes()




function incubadoradp(){
    $sql3="SELECT l.exercicio,l.partida,f.idficharep,f.inicio,f.fim,f.qtd,f.obs,f.idespeciefinalidade,
            DATEDIFF(sysdate(),f.inicio) as diasinc, 
            case f.idespeciefinalidade
                    when 27 then DATE_SUB(f.fim, INTERVAL 3 DAY)
                    else f.fim 
            end  as nfim 
            FROM ficharep f, lote l,especiefinalidade e
            where f.status = 'EM ANDAMENTO'
            and f.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
            and l.idlote = f.idlote
            and f.idespeciefinalidade =e.idespeciefinalidade
            and e.especie like('%Aves%')  order by nfim";	
	$res3=d::b()->query($sql3) or die("Erro ao buscar incubaçàµes da produção e do diagnostico sql=".$sql3);
	while($rowb=mysqli_fetch_assoc($res3)){

                if($rowb['diasinc']>0){
                        $diasvida=$rowb['diasinc']." dias";
                }else{
                        $diasvida="";
                }
                $data= date("Y/m/d");
               
              
	?>		
		<div class="linha"  >
                    <div class="coluna"  title="<?=$rowb['obs']?>">					
                        <table>
                            <tr >
                                <td style="font-size: 12px;" align="center">
                                    <input title="Concluir Incubação" type="checkbox" name="nameconcluirinc" onclick="concluificha(<?=$rowb['idficharep']?>);">
                                   <a title="Ver Incubação" href="javascript:janelamodal('?_modulo=ficharep&_acao=u&reload=Y&idficharep=<?=$rowb['idficharep']?>')">
                                     <?=$rowb['idficharep']?>-<?=$rowb['partida']?> / <?=$rowb['exercicio']?>
                                    </a>
                                </td>
                                <td style="font-size: 12px; " align="center">					
                                    <?echo($rowb['qtd']." Ovos ")?>  <font color="red"><?=$diasvida?></font> 
                                </td>
                                <td style="font-size: 12px; " align="center">					
                                    <?echo(dma($rowb['inicio'])." - ".dma($rowb['nfim']));?>
                                
                                <?
                                if(strtotime($rowb['nfim'])<= strtotime($data)){
                                ?>
                                   <i title="<?=$rowb['status']?>" class="fa fa-exclamation-triangle vermelho"></i>
                                <?
                                }
                                ?>
                                </td>
                            </tr>
                        </table>		
                    </div>		
		</div>
	<?
	}//while($rowb=mysqli_fetch_assoc($res3)){    
}//function incubadoradp(){


function estterceiro(){
    $sql3="select
                b.idregistro,
                e.idlocalensaio,
                b.tipo,
                b.idbioensaio,
                concat(UPPER(b.estudo),' ',UPPER(b.partida)) as bioensaio,
                b.cor,
                b.qtd,
                UPPER(LEFT(e.ensaio,20)) as ensaio,				
                l.local,
                e.idlocal,
                dma(r.iniciobio) as inicio1,
                dma(r.fimbio) as fim1,
                if(CURDATE()>=r.iniciobio,'S','N') as mmin,
                if(CURDATE()<=r.fimbio,'S','N') as mmax,
                (DATEDIFF(curdate(),r.fiminc)) AS diasvida,               
                e.obs,
                e.gaiola,
                b.status				
                from bioensaio b,localensaio e,local l,vwreservabioensaio r
                where b.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                and r.fimbio >= CURDATE()
                and e.status !='PENDENTE'
                and b.status  not in('CANCELADO','FINALIZADO')
                and r.idbioensaio=b.idbioensaio
                and b.idbioensaio = e.idbioensaio
                and r.especie like '%suino%'
                and e.idlocal = l.idlocal	
                and l.idlocal=26 order by e.idlocal,e.status desc,r.iniciobio";	
	$res3=d::b()->query($sql3) or die("Erro ao buscar reproducao suino ativos sql=".$sql3);
	while($rowb=mysqli_fetch_assoc($res3)){

                if($rowb['diasvida']>0){
                        $diasvida=$rowb['diasvida']." dias";
                }else{
                        $diasvida="";
                }
                if($rowb['status']=='ATIVO'){
                        $cor="black";
                        $cband="preto";
                }elseif($rowb['status']=='DISPONIVEL'){
                        $cor="red";
                        $cband="vermelho";
                }elseif($rowb['status']=='RESERVADO'){
                        $cor="green";
                        $cband="verde";
                }
	?>		
		<div class="linha"  >
                    <div class="coluna" title="<?=$row3['obs']?>">					
                        <table>
                            <tr >
                                <td><i title="<?=$rowb['status']?>" class="fa fa-bookmark <?=$cband?>"></i></td>
                                <td style="font-size: 12px;" align="center">
                                    
                                    <a title="Ver Bioensaio" href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&reload=Y&idbioensaio=<?=$rowb['idbioensaio']?>')">
                                     B<?=$rowb['idregistro']?>-<?=$rowb['bioensaio']?> - PIC(<?=$rowb['gaiola']?>)
                                    </a>
                                </td>
                                <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                                    <?echo($rowb['qtd']." Animais ")?> <?echo($rowb['tipo'])?> <?=$diasvida?>
                                </td>
                                <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                                    <?echo($rowb['inicio1']." - ".$rowb['fim1']);?>
                                </td>
                            </tr>
                        </table>		
                    </div>		
		</div>
	<?
	}//while($rowb=mysqli_fetch_assoc($res3)){    
}//function estterceiro(){



function estcamundongo(){
    $sql3="select
                b.idregistro,
                e.idlocalensaio,
                b.tipo,
                b.idbioensaio,
                concat(UPPER(LEFT(b.estudo,20)),' ',UPPER(b.partida)) as bioensaio,
                b.cor,
                b.qtd,
                UPPER(LEFT(e.ensaio,20)) as ensaio,				
                l.local,
                e.idlocal,
                dma(r.iniciobio) as inicio1,
                dma(r.fimbio) as fim1,
                if(CURDATE()>=r.iniciobio,'S','N') as mmin,
                if(CURDATE()<=r.fimbio,'S','N') as mmax,
                (DATEDIFF(curdate(),r.fiminc)) AS diasvida,               
                e.obs,
                e.gaiola,
                b.status				
                from bioensaio b,localensaio e,local l,vwreservabioensaio r
                where b.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
                and r.fimbio >= CURDATE()
                and e.status !='PENDENTE'
                and b.status  not in('CANCELADO','FINALIZADO')
                and r.idbioensaio=b.idbioensaio
                and b.idbioensaio = e.idbioensaio
                and r.especie like '%Roedores%'
                and e.idlocal = l.idlocal	
                and l.idlocal=25 order by e.idlocal,e.status desc,r.iniciobio";	
	$res3=d::b()->query($sql3) or die("Erro ao buscar reproducao ativos sql=".$sql3);
	while($rowb=mysqli_fetch_assoc($res3)){

                if($rowb['diasvida']>0){
                        $diasvida=$rowb['diasvida']." dias";
                }else{
                        $diasvida="";
                }
                if($rowb['status']=='ATIVO'){
                        $cor="black";
                        $cband="preto";
                }elseif($rowb['status']=='DISPONIVEL'){
                        $cor="red";
                        $cband="vermelho";
                }elseif($rowb['status']=='RESERVADO'){
                        $cor="green";
                        $cband="verde";
                }
	?>		
		<div class="linha"  >
                    <div class="coluna" title="<?=$row3['obs']?>">					
                        <table>
                            <tr >
                                <td><i title="<?=$rowb['status']?>" class="fa fa-bookmark <?=$cband?>"></i></td>
                                <td style="font-size: 12px;" align="center">
                                    
                                    <a title="Ver Bioensaio" href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&reload=Y&idbioensaio=<?=$rowb['idbioensaio']?>')">
                                     B<?=$rowb['idregistro']?>-<?=$rowb['bioensaio']?> - CAIXA(<?=$rowb['gaiola']?>)
                                    </a>
                                </td>
                                <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                                    <?echo($rowb['qtd']." Animais ")?> <?echo($rowb['tipo'])?> <?=$diasvida?>
                                </td>
                                <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                                    <?echo($rowb['inicio1']." - ".$rowb['fim1']);?>
                                </td>
                            </tr>
                        </table>		
                    </div>		
		</div>
	<?
	}//while($rowb=mysqli_fetch_assoc($res3)){    
}//function estcamundongo(){

function reproducao(){
    $sql3="select f.idficharep,l.partida,l.exercicio,f.local,f.qtd,f.inicio,f.fim,f.obs,e.especie,f.local,
            dma(f.inicio) as inicio1,
            dma(f.fim) as fim1,
            if(CURDATE()>=f.inicio,'S','N') as mmin,
            if(CURDATE()<=f.fim,'S','N') as mmax,
            (DATEDIFF(curdate(),f.fim)) AS diasvida,
            b.idbioensaio
            from lote l,especiefinalidade e,ficharep f left join bioensaio b on(b.idficharep = f.idficharep)
            where l.idlote = f.idlote 
            and f.status = 'EM ANDAMENTO'
            and e.idespeciefinalidade = f.idespeciefinalidade
            and e.especie like '%Roedores%'";	
    $res3=d::b()->query($sql3) or die("Erro ao buscar reproducao ativos sql=".$sql3);
    while($row3=mysqli_fetch_assoc($res3)){
            if($row3['mmin']=='S' and $row3['mmax']=='S'){
                $stropacity="";
            }else{
                $stropacity="opacity: 0.4;";
            }

            if($row3['diasvida']>0){
                $diasvida=$row3['diasvida']." dias";
            }else{
                $diasvida="";
            }

            if($row3['idbioensaio']){
                $cor="green";
            }else{
                $cor="red";
            }
    ?>		
            <div class="linha"  >
                <div class="coluna" style="<?=$stropacity?>" title="<?=$row3['obs']?>">					
                    <table>
                        <tr >
                            <td style="font-size: 12px;" align="center">
                                <i class="fa fa-bookmark <?=$cor?>"></i>
                                <a title="Ver Ficha" href="javascript:janelamodal('?_modulo=ficharep&_acao=u&reload=Y&idficharep=<?=$row3['idficharep']?>')">
                                    Ficha <?=$row3['idficharep']?>-<?=$row3['partida']?>/<?=$row3['exercicio']?>
                                </a>
                            </td>
                            <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                            <?echo($row3['qtd']." Fàªmeas")?> -CAIXA(<?echo($row3['local'])?>)<font color="red"><?=$diasvida?></font>
                            </td>
                            <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                            <?echo($row3['inicio1']." - ".$row3['fim1']);?>
                            </td>
                        </tr>
                    </table>		
                </div>		
            </div>
    <?
    }//while($row3=mysqli_fetch_assoc($res3)){    
}//function reproducao(){

//Listar pinteiro
function pinteiro(){
    $sql3="select
            b.idregistro,
            b.tipo,
            b.idbioensaio,
            concat(UPPER(LEFT(b.estudo,20)),' ',UPPER(b.partida)) as bioensaio,
            b.cor,
            e.qtd,
           (DATEDIFF(curdate(),e.fiminc)) AS diasvida,
            dma(e.iniciopint) as inicio1,
            dma(e.fimpint) as fim1,
            if(CURDATE()>=e.iniciopint,'S','N') as mmin,
            if(CURDATE()<=e.fimpint,'S','N') as mmax,
            b.status			
            from bioensaio b,vwreservabioensaio e
            where b.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
            and e.especie like '%Aves%'
            and e.fimpint >= CURDATE()
            and b.status  not in('CANCELADO','FINALIZADO')
            and b.idbioensaio = e.idbioensaio				
            order by e.iniciopint";
	
    $res3=d::b()->query($sql3) or die("Erro ao buscar Pinteiro sql=".$sql3);
    while($row3=mysqli_fetch_assoc($res3)){
        if($row3['mmin']=='S' and $row3['mmax']=='S'){
            $stropacity="";
        }else{
            $stropacity="opacity: 0.4;";
        }

        if($row3['diasvida']>0){
            $diasvida=$row3['diasvida']." dias";
        }else{
            $diasvida="";
        }

        if($row3['status']=='ATIVO'){
            $cor="black";
            $cband="preto";
        }elseif($row3['status']=='DISPONIVEL'){
            $cor="red";
            $cband="vermelho";
        }elseif($row3['status']=='RESERVADO'){
            $cor="green";
            $cband="verde";
        }
?>		
        <div class="linha"  >
            <div class="coluna" style="<?=$stropacity?>" title="<?=$row3['obs']?>">					
                <table>
                    <tr >
                        <td style="font-size: 12px;" align="center">
                            <i title="<?=$row3['status']?>" class="fa fa-bookmark <?=$cband?>"></i>	
                        <a title="Ver Bioensaio" href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&reload=Y&idbioensaio=<?=$row3['idbioensaio']?>')">
                        
                        B<?=$row3['idregistro']?>-<?=$row3['bioensaio']?>
                       
                        </a>
                        </td>
                        <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                        <?echo($row3['qtd']." ".$row3['especie'])?> <?echo($row3['tipo'])?> <font color="red"><?=$diasvida?></font>
                        </td>
                        <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                        <?echo($row3['inicio1']." - ".$row3['fim1']);?>
                        </td>
                    </tr>
                </table>		
            </div>		
        </div>
<?
    }//while($row3=mysqli_fetch_assoc($res3)){    
}//function pinteiro(){

function incubadora(){
	
    $sql3="select
            b.idregistro,
            b.tipo,
            b.idbioensaio,
            e.especie,
            concat(UPPER(LEFT(b.estudo,20)),' ',UPPER(b.partida)) as bioensaio,
            b.cor,
            e.qtd,
            dma(e.inicioinc) as inicio1,
            dma(e.fiminc) as fim1,
            if(CURDATE()>=e.inicioinc,'S','N') as mmin,
            if(CURDATE()<=e.fiminc,'S','N') as mmax,
            b.status			
            from bioensaio b,vwreservabioensaio e
            where b.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
            and e.especie='Aves'
            and e.fiminc >= CURDATE()
            and b.status  not in('CANCELADO','FINALIZADO')
            and b.idbioensaio = e.idbioensaio				
            order by e.inicioinc";
    $res3=d::b()->query($sql3) or die("Erro ao buscar incubadora  sql=".$sql3."".mysqli_error(d::b()));
    while($row3=mysqli_fetch_assoc($res3)){
        if($row3['mmin']=='S' and $row3['mmax']=='S'){
            $stropacity="";
        }else{
            $stropacity="opacity: 0.4;";
        }
        
        if($row3['status']=='ATIVO'){
            $cor="black";
            $cband="preto";
        }elseif($row3['status']=='DISPONIVEL'){
            $cor="red";
            $cband="vermelho";
        }elseif($row3['status']=='RESERVADO'){
            $cor="green";
            $cband="verde";
        }
    ?>		
        <div class="linha"  >
            <div class="coluna" style="<?=$stropacity?>" title="<?=$row3['obs']?>">					
                <table>
                    <tr >
                        <td style="font-size: 12px;" align="center">
                            <i title="<?=$row3['status']?>" class="fa fa-bookmark <?=$cband?>"></i>	
                            <a title="Ver Bioensaio" href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&reload=Y&idbioensaio=<?=$row3['idbioensaio']?>')">

                            B<?=$row3['idregistro']?>-<?=$row3['bioensaio']?>

                            </a>
                        </td>
                        <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                        <?echo($row3['qtd']." ".$row3['especie'])?> <?echo($row3['tipo'])?> 
                        </td>
                        <td style="font-size: 12px; color:<?=$cor?>" align="center">					
                        <?echo($row3['inicio1']." - ".$row3['fim1']);?>
                        </td>
                    </tr>
                </table>		
            </div>		
        </div>
    <?
    }//while($row3=mysqli_fetch_assoc($res3)){
}// function incubadora()

 //BUSCAR SERVIàOS PENDENTES
function servicospendentes(){
 
    $sqle="
SELECT 
    *
FROM
    (SELECT 
            f.idficharep AS idregistro,
            UPPER(e.especie) AS estudo,
            UPPER(l.partida) AS partida,
            f.idficharep AS idbioensaio,
            s.idservicoensaio,
            s.servico,
            s.data AS dataserv,
            IF(CURDATE() <= s.data, 'black', 'red') AS cor,
            DMA(s.data) AS dmadata,
            s.dia,
            s.obs,
            s.status,
            '' AS gaiola,
            f.local,
            'ficharep' AS origem
    FROM
        lote l, especiefinalidade e, ficharep f, servicoensaio s
    WHERE
        f.idlote = l.idlote
            AND e.especie LIKE '%Roedores%'
            AND e.idespeciefinalidade = f.idespeciefinalidade
            AND f.status = 'EM ANDAMENTO'
            AND f.idficharep = s.idobjeto
            AND s.tipoobjeto = 'ficharep'
            AND s.status = 'PENDENTE' 
     UNION     
     SELECT 
            b.idregistro,
            UPPER(b.estudo) AS estudo,
            UPPER(b.partida) AS partida,
            b.idbioensaio,
            s.idservicoensaio,
            s.servico,
            s.data AS dataserv,
            IF(CURDATE() <= s.data, 'black', 'red') AS cor,
            DMA(s.data) AS dmadata,
            s.dia,
            s.obs,
            s.status,
            l.gaiola,
            RIGHT(lo.local, 2) AS local,
            'bioensaio' AS origem
    FROM
        servicoensaio s, bioensaio b
    LEFT JOIN (localensaio l) ON (b.idbioensaio = l.idbioensaio
        AND l.idlocal > 3)
    LEFT JOIN (local lo) ON (lo.idlocal = l.idlocal)
    WHERE b.idbioensaio = s.idobjeto
     AND not exists (select 1 from ficharep f where f.idficharep = b.idficharep and f.idespeciefinalidade = 18)
            AND s.tipoobjeto = 'bioensaio'
            AND s.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
            AND s.data BETWEEN CURDATE() - INTERVAL 100 DAY AND CURDATE() + INTERVAL 7 DAY
            AND s.status = 'PENDENTE') AS u
ORDER BY dataserv , servico;";
    $rese=d::b()->query($sqle) or die("Erro ao buscar serviços da semana sql=".$sqle);
    $qtde=mysqli_num_rows($rese);
?>	
    <table style=" font-size:11px;" >
<?		
    while($rowe=mysqli_fetch_assoc($rese)){

        if($rowe['dmadata']!=$data){
            if(!empty($data)){
?>		
        <tr>
            <td nowrap colspan="3" style="vertical-align: top;  padding: 0; border: none;"><div style=" border-bottom: 1px dashed #000000; width: 100%;"></div></td>
        </tr>
<?
            }//if(!empty($data)){
?>
        <tr>
            <td align="center" style="vertical-align: top;  padding: 0; border: none;">
                <font color="<?=$rowe["cor"]?>">
                <?=$rowe['dmadata']?>
                </font>
            </td>			
        </tr>
<?
            $data=$rowe['dmadata'];
        }//if($rowe['dmadata']!=$data){
?>		
        <tr title="<?=$rowe['estudo']?>-<?=$rowe['partida']?> | <?=$rowe['local']?> - <?=$rowe['gaiola']?>">
            <td nowrap align="center" colspan="2" style="vertical-align: top;  padding: 0; border: none;">			
               <input title="Concluir serviço" type="checkbox" name="nameconcluir" onclick="concluiservico(<?=$rowe['idservicoensaio']?>,<?=$rowe['idbioensaio']?>,'<?=$rowe['origem']?>','<?=$rowe['servico']?>');">
               <a href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&idbioensaio=<?=$rowe['idbioensaio']?>')">
                    <?=$rowe['servico']?> - B<?=$rowe['idregistro']?> <?=$rowe['local']?>-<?=$rowe['gaiola']?>
                </a>
            </td>
        </tr>		
<?
    }//while($rowe=mysqli_fetch_assoc($rese)){		
    if($qtde>0){
?>		
        <tr>
            <td nowrap colspan="3" style="vertical-align: top;  padding: 0; border: none;"><div style=" border-bottom: 1px dashed #000000; width: 100%;"></div></td>
        </tr>
<?
    }?>
    </table>
<?
}//function servicospendentes(){

//BUSCAR SERVIàOS PENDENTES terceiros
function servicospendentesterc(){
 
    $sqle="
     SELECT 
            b.idregistro,
            UPPER(b.estudo) AS estudo,
            UPPER(b.partida) AS partida,
            b.idbioensaio,
            s.idservicoensaio,
            s.servico,
            s.data AS dataserv,
            IF(CURDATE() <= s.data, 'black', 'red') AS cor,
            DMA(s.data) AS dmadata,
            s.dia,
            s.obs,
            s.status,
            l.gaiola,
            RIGHT(lo.local, 2) AS local,
            'bioensaio' AS origem
    FROM
        servicoensaio s,ficharep f, bioensaio b
    LEFT JOIN (localensaio l) ON (b.idbioensaio = l.idbioensaio
        AND l.idlocal > 3)
    LEFT JOIN (local lo) ON (lo.idlocal = l.idlocal)
    WHERE b.idbioensaio = s.idobjeto
	AND f.idficharep = b.idficharep and f.idespeciefinalidade = 18
	AND s.tipoobjeto = 'bioensaio'
	AND s.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
	AND s.data BETWEEN CURDATE() - INTERVAL 100 DAY AND CURDATE() + INTERVAL 20 DAY
	AND s.status = 'PENDENTE'
    ORDER BY dataserv , servico";
    $rese=d::b()->query($sqle) or die("Erro ao buscar serviços da semana  para terceiros sql=".$sqle);
    $qtde=mysqli_num_rows($rese);
?>	
    <table style=" font-size:11px;" >
<?		
    while($rowe=mysqli_fetch_assoc($rese)){

        if($rowe['dmadata']!=$data){
            if(!empty($data)){
?>		
        <tr>
            <td nowrap colspan="3" style="vertical-align: top;  padding: 0; border: none;"><div style=" border-bottom: 1px dashed #000000; width: 100%;"></div></td>
        </tr>
<?
            }//if(!empty($data)){
?>
        <tr>
            <td align="center" style="vertical-align: top;  padding: 0; border: none;">
                <font color="<?=$rowe["cor"]?>">
                <?=$rowe['dmadata']?>
                </font>
            </td>			
        </tr>
<?
            $data=$rowe['dmadata'];
        }//if($rowe['dmadata']!=$data){
?>		
        <tr title="<?=$rowe['estudo']?>-<?=$rowe['partida']?> | <?=$rowe['local']?> - <?=$rowe['gaiola']?>">
            <td nowrap align="center" colspan="2" style="vertical-align: top;  padding: 0; border: none;">			
               <input title="Concluir serviço" type="checkbox" name="nameconcluir" onclick="concluiservico(<?=$rowe['idservicoensaio']?>,<?=$rowe['idbioensaio']?>,'<?=$rowe['origem']?>','<?=$rowe['servico']?>');">
               <a href="javascript:janelamodal('?_modulo=bioensaio&_acao=u&idbioensaio=<?=$rowe['idbioensaio']?>')">
                    <?=$rowe['servico']?> - B<?=$rowe['idregistro']?> <?=$rowe['local']?>-<?=$rowe['gaiola']?>
                </a>
            </td>
        </tr>		
<?
    }//while($rowe=mysqli_fetch_assoc($rese)){		
    if($qtde>0){
?>		
        <tr>
            <td nowrap colspan="3" style="vertical-align: top;  padding: 0; border: none;"><div style=" border-bottom: 1px dashed #000000; width: 100%;"></div></td>
        </tr>
<?
    }?>
    </table>
<?
}//function servicospendentesterc(){

function geral(){
   $sql="select 
            sum(l.qtd) as sumqtd  , dma(l.iniciopint) as dmainicio ,DATEDIFF(now(),l.fiminc) as diasvida,l.especie,l.status
        from vwreservabioensaio l
        group by status,dmainicio,diasvida,especie order by dmainicio";

    $res=d::b()->query($sql) or die("Erro ao buscar a idade das aves [geral] sql=".$sql);
    while($row=mysqli_fetch_assoc($res)){
        if($row['diasvida']>0 and $row['especie']=="Aves"){
                $tipoave="Aves";
                $dias=$row['diasvida'];
        }elseif($row['especie']=="Aves"){
                $tipoave="Ovos";
                $dias=  $row['diasvida']+21;
        }else{
            $tipoave="Roedores";
            $dias=$row['diasvida'];
        }
        if($row['status']=='ATIVO'){
                $cor="black";
        }elseif($row['status']=='DISPONIVEL'){
                $cor="red";
        }elseif($row['status']=='RESERVADO'){
                $cor="green";
        }
?>		
        <div class="linha">
            <div class="coluna">
                <table >
                    <tr>
                        <td style="font-size: 12px; color:<?=$cor?>" align="center">
                            <i title="<?=$row['status']?>" class="fa fa-bookmark <?=$cor?>"></i>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                         
                        <?echo($row['sumqtd']." ".$tipoave." ".$dias." dias.");?>
                       

                        </td>
                    </tr>
                </table>	
            </div>		
        </div>
<?
    }	
}
?>


<script type="text/javascript">
function ShowHideDIV(Valor){

    if(Valor=="1")
    {
        document.getElementById('nascedouro').style.display = "block";// 1 
        document.getElementById('inativada').style.display = "none"; // 2
        document.getElementById('viva').style.display = "none"; // 3 
        document.getElementById('biocontido').style.display = "none";// 4
        document.getElementById('geral').style.display = "none"; // 5
        document.getElementById('Roedores').style.display = "none";
	document.getElementById('terceiro').style.display = "none";
        document.getElementById('incubadoradp').style.display = "none"; // 6
	document.getElementById('servicos').style.display = "block";
    }
    if(Valor=="2")
    {		
        document.getElementById('nascedouro').style.display = "none"; // 1 
        document.getElementById('inativada').style.display = "block"; // 2
        document.getElementById('viva').style.display = "none"; // 3 
        document.getElementById('biocontido').style.display = "none"; // 4
        document.getElementById('geral').style.display = "none"; // 5
        document.getElementById('Roedores').style.display = "none";
	document.getElementById('terceiro').style.display = "none";
        document.getElementById('incubadoradp').style.display = "none"; // 6
	document.getElementById('servicos').style.display = "block";
     }
    if(Valor=="3")
    {
        document.getElementById('inativada').style.display = "none"; // 2
        document.getElementById('nascedouro').style.display = "none"; // 1 
        document.getElementById('viva').style.display = "block"; // 3 
        document.getElementById('biocontido').style.display = "none"; // 4
        document.getElementById('geral').style.display = "none"; // 5
        document.getElementById('Roedores').style.display = "none";
	document.getElementById('terceiro').style.display = "none";
        document.getElementById('incubadoradp').style.display = "none"; // 6
	document.getElementById('servicos').style.display = "block";
    }
    if(Valor=="4")
    {
        document.getElementById('inativada').style.display = "none"; // 2
        document.getElementById('nascedouro').style.display = "none"; // 1 
        document.getElementById('viva').style.display = "none"; // 3 
        document.getElementById('biocontido').style.display = "block"; // 4
        document.getElementById('geral').style.display = "none"; // 5
        document.getElementById('Roedores').style.display = "none"; // 6
	document.getElementById('terceiro').style.display = "none";
        document.getElementById('incubadoradp').style.display = "none"; // 6
	document.getElementById('servicos').style.display = "block";
     }
    if(Valor=="5")
    {
        document.getElementById('inativada').style.display = "none"; // 2
        document.getElementById('nascedouro').style.display = "none"; // 1 
        document.getElementById('viva').style.display = "none"; // 3 
        document.getElementById('biocontido').style.display = "none"; // 4
        document.getElementById('geral').style.display = "block"; // 5
        document.getElementById('Roedores').style.display = "none"; // 6
	document.getElementById('terceiro').style.display = "none";
        document.getElementById('incubadoradp').style.display = "none"; // 6
	document.getElementById('servicos').style.display = "block";
     }

    if(Valor=="6")
    {
        document.getElementById('inativada').style.display = "none"; // 2
        document.getElementById('nascedouro').style.display = "none"; // 1 
        document.getElementById('viva').style.display = "none"; // 3 
        document.getElementById('biocontido').style.display = "none"; // 4
        document.getElementById('geral').style.display = "none"; // 5
        document.getElementById('Roedores').style.display = "block"; // 6
	document.getElementById('terceiro').style.display = "none";
        document.getElementById('incubadoradp').style.display = "none"; // 6
	document.getElementById('servicos').style.display = "block";
    }
    if(Valor=="7")
    {
        document.getElementById('inativada').style.display = "none"; // 2
        document.getElementById('nascedouro').style.display = "none"; // 1 
        document.getElementById('viva').style.display = "none"; // 3 
        document.getElementById('biocontido').style.display = "none"; // 4
        document.getElementById('geral').style.display = "none"; // 5
        document.getElementById('Roedores').style.display = "none"; // 6
	document.getElementById('terceiro').style.display = "none";
        document.getElementById('incubadoradp').style.display = "block"; // 6
	document.getElementById('servicos').style.display = "block";
    }
    if(Valor=="8")
    {
        document.getElementById('inativada').style.display = "none"; // 2
        document.getElementById('nascedouro').style.display = "none"; // 1 
        document.getElementById('viva').style.display = "none"; // 3 
        document.getElementById('biocontido').style.display = "none"; // 4
        document.getElementById('geral').style.display = "none"; // 5
        document.getElementById('Roedores').style.display = "none"; // 6
        document.getElementById('incubadoradp').style.display = "none"; // 6
	document.getElementById('terceiro').style.display = "block"; // 6	
	document.getElementById('servicos').style.display = "none"; // 6
    }
}
        
function concluiservico(vidservicoensaio,vidobjeto,vtipoobjeto,vservico){
	
    CB.post({
        objetos: "_x_u_servicoensaio_idservicoensaio="+vidservicoensaio+"&_x_u_servicoensaio_servico="+vservico+"&_x_u_servicoensaio_idobjeto="+vidobjeto+"&_x_u_servicoensaio_tipoobjeto="+vtipoobjeto+"&_x_u_servicoensaio_status=CONCLUIDO"        
    });
}

function concluificha(vidficharep){
	
    CB.post({
        objetos: "_x_u_ficharep_idficharep="+vidficharep+"&_x_u_ficharep_status=CONCLUIDO"        
    });
}

        //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>