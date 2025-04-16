<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}
$idpessoa=$_GET["idpessoa"];
$idprodserv=$_GET["idprodserv"];
$status=$_GET["status"];

if(!empty($idpessoa)){
    $clausulalote .= " and pf.idpessoa =".$idpessoa." ";
}

if(!empty($idprodserv)){
    $clausulad .= " and  fi.idprodserv =  ".$idprodserv."";
}

if($status=='ATIVO'){
    $clausulals .= " and ls.status not in ('CANCELADO','ESGOTADO')";
}else{
    $clausulals .= " and ls.status not in ('CANCELADO') ";
}

function getcliente(){
	
	$sql= "select p.idpessoa,p.nome 
			from pessoa p
			where p.idtipopessoa=2 
                        ".getidempresa('p.idempresa','pessoa')."
			and exists (    
                                    select 1
                                    from lote l
                                    where  l.idpessoa = p.idpessoa    
                                )
			and p.status ='ATIVO' order by p.nome";

	$res = d::b()->query($sql) or die("getcliente: Erro: ".mysqli_error(d::b())."\n".$sql);

	$arrret=array();
	while($r = mysqli_fetch_assoc($res)){
		//monta 2 estruturas json para finalidades (loops) diferentes
		$arrret[$r["idpessoa"]]["nome"]=(($r["nome"]));
	}
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli=getcliente();
//print_r($arrCli); die;
$jCli=$JSON->encode($arrCli);


function getproduto(){
	
	$sql= "select idprodserv,descr 
            from prodserv p
            where  p.fabricado='Y' 
            and p.venda='N' 
            and p.especial ='Y' 
            ".getidempresa('p.idempresa','prodserv')." 
            and exists(select 1 from prodservprproc o where o.idprodserv = p.idprodserv)
            and p.status='ATIVO' order by p.descr";
                
            /*               
                "select p.idprodserv,p.descr
			from prodserv p join prodservforn f
			where p.status='ATIVO' 
			AND p.tipo='PRODUTO' 
			".getidempresa('p.idempresa','prodserv')." 
			AND p.venda ='Y' 
			AND exists (select 1 from lote l where l.idprodserv = p.idprodserv)
                        and exists (select 1  from prodservforn f where f.idprodserv=p.idprodserv and f.qtd > 0)
			AND p.especial='Y' order by p.descr";            
             */

	$res = d::b()->query($sql) or die("getproduto: Erro: ".mysqli_error(d::b())."\n".$sql);

	$arrret=array();
	while($r = mysqli_fetch_assoc($res)){
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["idprodserv"]]["descr"]=(($r["descr"]));
	}
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrProd=getproduto();
//print_r($arrCli); die;
$jProd=$JSON->encode($arrProd);
?>
<style>
	
    .insumosEspeciais a i.fa{
            display: inline-block !important;
    }
    .itemestoque{
        Xwidth:100%;
        width:auto;
        display: inline-block;
        /* text-align: right; */
        margin: 3px;
    }
.itemestoque.especial{
	display:none;
}
.itemestoque.especial.especialvisivel{
	display:inline-block !important;
}



a.tip:hover {
    cursor: hand;
    position: relative
}
a.tip span {
    display: none
}
a.tip:hover span {
    border: #c0c0c0 1px dotted;
    padding: 5px 20px 5px 5px;
    display: block;
    z-index: 100;
    background: #f0f0f0 no-repeat 100% 5%;
    left: 0px;
    margin: 10px;
    width: 550px;
    position: absolute;
    top: 10px;
    text-decoration: none
}
</style>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Pesquisar </div>
        <div class="panel-body" >
            <div class="row">      
                <div class="col-md-1">Cliente:</div>
                <div class="col-md-5">
                    <input id="idpessoa"  type="text" name="idpessoa"  cbvalue="<?=$idpessoa?>" value="<?=$arrCli[$idpessoa]["nome"]?>" style="width: 40em;" vnulo>
                </div>               
                <div class="col-md-1">Status:</div>
                <div class="col-md-5"> 
                    <select class='size10' name="status" id="status"  >				
                        <?fillselect("select 'TODOS','Todos' union select 'FALTA','Falta'",$status);?>
                    </select>
                </div>
            </div>
            <div class="row">      
                <div class="col-md-1">Produto:</div>
                <div class="col-md-5">	
                    <input id="idprodserv"  type="text" name="idprodserv"  cbvalue="<?=$idprodserv?>" value="<?=$arrProd[$idprodserv]["descr"]?>" style="width: 60em;" vnulo>
		</div>
            </div>
            <div class="row"> 
                <div class="col-md-8"></div>
                <div class="col-md-1 nowrap">
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
if($_GET and (!empty($idpessoa) or !empty($idprodserv) or !empty($status))){
?>    
<div  id="obsinicio"></div> 
<?    
    
    $haproduzir=0;
    
    $sqlv="select 
                idprodservvacina,vacina,idpessoa,sum(qtd) as qtd,nome,rotulo,dose,idprodserv,descr,qtdi,qtdi_exp, qtdpadrao
            from (
                    select pf.idprodserv as idprodservvacina,v.descr as vacina,pf.idpessoa,
                    CASE WHEN 
                                (select sum(l.qtdprod) from lote l 
                                        where (l.idprodserv=pf.idprodserv 
                                        and l.idpessoa=pf.idpessoa 
                                        and l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO')) 
                                 ) > pf.qtd     
			THEN 
                                (select sum(l.qtdprod) from lote l 
                                        where (l.idprodserv=pf.idprodserv 
                                        and l.idpessoa=pf.idpessoa 
                                        and l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO')) 
                                    )
                        ELSE pf.qtd 
                        END as qtd,                    
                        p.nome, concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose,
                        fi.idprodserv,ps.descr,fi.qtdi,fi.qtdi_exp,ifnull(v.qtdpadrao,1) as qtdpadrao
                        
                    from  
                                   prodservforn pf 
                            join prodserv v on(v.idprodserv = pf.idprodserv)
                            join pessoa p on(pf.idpessoa=p.idpessoa and p.status='ATIVO' )
                            join prodservformula f on(pf.idprodservformula=f.idprodservformula)
                            join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
                            join prodserv ps on(ps.idprodserv=fi.idprodserv and ps.especial='Y')
                            --  left join lote l on(l.idprodserv=pf.idprodserv 
                            --  and l.idpessoa=pf.idpessoa and l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO'))
                    where pf.status='ATIVO' 	
                    and pf.qtd>0
                    ".getidempresa('pf.idempresa','prodserv')."
                    AND v.tipo='PRODUTO' 
                    and v.venda ='Y' 
                    and v.especial='Y'
                    ".$clausulalote."                            
                     ".$clausulad."  
                   
            union
              
                    select p.idprodserv as idprodservvacina,  p.descr as vacina,l.idpessoa,
                    l.qtdpedida as qtd,
                    pf.nome,						
                        concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose,
						fi.idprodserv,psi.descr,fi.qtdi,fi.qtdi_exp,ifnull(p.qtdpadrao,1) as qtdpadrao
                       
              from  lote l 
                    join prodserv p on(p.idprodserv=l.idprodserv and p.venda='Y' and p.tipo='PRODUTO' and p.especial='Y')
                    join unidade u on(l.idunidade=u.idunidade and u.producao='Y')  
                    join pessoa pf on(pf.idpessoa = l.idpessoa)
                    join prodservformula f on(f.idprodservformula=l.idprodservformula)
                    join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
                    join prodserv psi on(psi.idprodserv=fi.idprodserv and psi.especial='Y')
              where  l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO')
               ".getidempresa('p.idempresa','prodserv')."
              and not exists (select 1 from prodservforn pf where pf.idprodserv=l.idprodserv and pf.idpessoa =l.idpessoa 
                                and pf.idprodservformula=l.idprodservformula and pf.qtd>0 )  
                ".$clausulalote."
                ".$clausulad."                    
               
            ) as u group by u.idprodserv,u.idpessoa
             order by descr";
    

    $resv = d::b()->query($sqlv) or die("erro ao buscar programacoes: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlv);
    $qtdprodform= mysqli_num_rows($resv);
     echo("<!-- primeiro: ".$sqlv." -->");
?>
  <!-- sql-2= <?=$sqlv?>-->
<?
        if($qtdprodform>0){
            $semestoque=0;
            while($r= mysqli_fetch_assoc($resv)){
               
                 if($r['idprodserv']!=$idprodservlop and !empty($idprodservlop)){
                     
?>
                            </div>
                    </div> 
                    </div>
                </div>
<?  
                 }
                if($r['idprodserv']!=$idprodservlop){ 
                    
                    if($semestoque==0 and !empty($idprodservlop)){
?>
                        <div class='ocultar_pessoa' id='<?=$r['idprodserv']?>'>
                           
                        </div>
<?                                                    
                    }
                    $semestoque=0;
                   $idprodservlop=$r['idprodserv'];
?> 
            <div class="row <?=$r['idprodserv']?>">
                <div class="col-md-12" >
                <div class="panel panel-default" >
                    <div class="panel-heading">
                        <label class="idbox"> <?=$r['descr']?> </label> 
                    </div>
                    <div class="panel-body">
	<?		
                }

?>
			<table class="table table-striped planilha <?=$r['idpessoa']?>_<?=$r['idprodserv']?>">           

			<tr>
				<td>
					<div class="row">

                                        <div class="col-md-12" >
                                            <div class="panel panel-default" >
                                                <div class="panel-heading"><?=$r['nome']?> 
                                                    <a class="fa fa-search tip" >                                                            
                                                           <span class="<?=$r['idpessoa']?>__<?=$r['idprodserv']?>"></span>
                                                    </a>
                                                </div>
                                                <div class="panel-body">
<?                       
                                            
                                           
                                            $arrS=array();
                                            $arrPool=array();
                                            listaconcentrados($clausulalote,$clausulad,$r['idpessoa'],$r['idprodserv']);
                                           
?>          
                                                    
                                                </div>
                                            </div>
                                        </div>
                                                
<?					
?>	
                                        </div>
						
				</td>
			</tr>
                        </table>
		
                    
 
<?				
			}//while($r= mysqli_fetch_assoc($resv)){
                            if($semestoque==0 and !empty($idprodservlop)){
?>
                        <div class='ocultar_pessoa' id='<?=$idprodservlop?>'>
                           
                        </div>
<?                                                    
                            }                        
		}else{
                    echo("<DIV>Este produto não possui formulação com as caracteristicas da pesquisa.</DIV>");
		}
?>
                 </div>
            </div> 
            </div>
    </div>
    <div style="display: none;" id="obsfim">
        <div class="row">
            <div class="col-md-12" >
                <div class="panel panel-default" >
                    <div class="panel-heading">CONCENTRADOS À PRODUZIR: <?=$haproduzir?></div>
                </div>
            </div>
        </div>
    </div>
                                          
  <?
 }//if($_GET and !empty($clausulad)){

function listarsementes($idpessoa,$idprodserv,$preciso,$qtddisp_exp){
    global $arrS,$arrPool;
    
    $sqlx="select  distinct(l.idlote),lp.idpool,c.descr,case when l.vencimento < (DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 MONTH),'%Y-%m-%d')) then 'Y' else 'N' end as vencido,l.*          
           from prodservformula f
           join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
           join prodserv p on(p.idprodserv=fi.idprodserv and p.especial ='Y')
           join lote l on(l.idprodserv=fi.idprodserv and l.status not in ('ESGOTADO','CANCELADO','REPROVADO') 
                            and l.situacao='APROVADO'
                            and l.tipoobjetosolipor='resultado')
           join lotefracao fr on(fr.idlote=l.idlote and fr.status='DISPONIVEL')
           
           join resultado r on(r.idresultado=l.idobjetosolipor)
           join amostra a on(a.idamostra = r.idamostra and a.idpessoa=".$idpessoa.")
            join prodserv c on(c.idprodserv=f.idprodserv)
           left join lotepool lp on(lp.idlote=l.idlote and lp.status='ATIVO')
           where f.idprodserv=".$idprodserv."
               ".getidempresa('p.idempresa','prodserv')."
           order by lp.idpool desc,l.partida";
    echo("<!-- sementes: ".$sqlx." -->");
    $resx= d::b()->query($sqlx) or die("Erro ao buscar sementes : " . mysqli_error(d::b()) . "<p>SQL: ".$sqlx);
    $qtdx= mysqli_num_rows($resx);
   
    $arrSementes = mysqli_fetch_fields($resx);
    $qtdsementes=0;
    $qtdpool=0;
    $arrS=array();
    $arrPool=array();
    $arrLote=array();
    while($r = mysqli_fetch_assoc($resx)){
        //quais concentrados a semente tem
        $sqlc="select l.idlote,f.qtd as qtddisp,f.qtd_exp as qtddisp_exp,l.qtdprod,l.qtdprod_exp,p.qtdpadrao,p.qtdpadrao_exp,l.status
                from lotecons c 
                join lote l on(l.idlote = c.idobjeto and c.tipoobjeto ='lote' and l.status not in ('ESGOTADO','CANCELADO','REPROVADO'))
                 join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL')
                join prodserv p on(p.idprodserv =l.idprodserv and p.especial='Y')
                 where c.idlote=".$r['idlote']." and c.qtdd > 0";
        $resc= d::b()->query($sqlc) or die("Erro ao buscar quais concentrados a semente tem : " . mysqli_error(d::b()) . "<p>SQL: ".$sqlc);
        $qtdispsem=0;
        while($rowc=mysqli_fetch_assoc($resc)){
            //quantas sementes tem no concentrado
            $sqlqts=" select c.idlote,s.idlote as idsemente
                        from lote l 
                               join lotecons c on(c.idobjeto=l.idlote and c.tipoobjeto ='lote' and c.qtdd>0)
                           join lote s on(s.idlote = c.idlote)
                           join prodserv p on(p.idprodserv=s.idprodserv and p.especial='Y')
                        where l.idlote =".$rowc['idlote'];
            $resqts= d::b()->query($sqlqts) or die("Erro ao buscar quais concentrados a semente tem : " . mysqli_error(d::b()) . "<p>SQL: ".$sqlqts);
            $qtdsementes=mysqli_num_rows($resqts); 
            if($rowc['status']!='APROVADO'){$valdisp=$rowc['qtdprod'];}else{$valdisp=$rowc['qtddisp'];}
            $qtdispsem=$qtdispsem+($valdisp/$qtdsementes);
            $dispsemen_exp=$rowc['qtdpadrao_exp'];
        }
        
        $arrLote[$r['idlote']]=$r['idlote'];
          
        if(($r['idpool']!=$idpool) and (!empty($idpool))){            
            $qtdpool=$qtdpool+1;            
        }elseif(empty($r['idpool'])){
            $qtdpool=$qtdpool+1; 
        }
        $idpool=$r['idpool'];
                
        //para cada coluna resultante do select cria-se um item no array
        foreach ($arrSementes as $col) {
            //$arrret[$i][$col->name]=$robj[$col->name];
            $arrS[$r['idlote']][$col->name]=$r[$col->name];
            $arrS[$r['idlote']]['pool']=$qtdpool;
            $arrS[$r['idlote']]['dispsemen']=$qtdispsem;
            $arrS[$r['idlote']]['dispsemen_exp']=$dispsemen_exp;
        }
        $arrPool[$qtdpool][$r['idlote']]=$r['idlote'];
    }
    //print_r($arrS); die;
    //echo("preciso=".$preciso."/n");
    $nporpool=$preciso/count($arrPool);//necessidade por pool
   // echo("Por pool=".$nporpool);
  
    foreach($arrS as $key => $value) {       
        $keypool =$arrS[$key]['pool'];
        //echo("Numero de pools ".count($arrPool[$keypool], COUNT_RECURSIVE)." ");        
        $arrS[$key]['deficit']=$nporpool/count($arrPool[$keypool], COUNT_RECURSIVE);// necessidade por semente
        $arrS[$key]['perc_deficit']=$arrS[$key]['dispsemen']/($nporpool/count($arrPool[$keypool], COUNT_RECURSIVE));//estoque / demanda
        if($arrS[$key]['perc_deficit']<1){
            $diferenca=1-$arrS[$key]['perc_deficit'];
            
            $produzir=$diferenca*($nporpool/count($arrPool[$keypool], COUNT_RECURSIVE));
            $arrS[$key]['produzir']=$produzir;            
            $arrS[$key]['descrperc_deficit']='Estoque: '.recuperaExpoente(tratanumero($arrS[$key]['dispsemen']),$qtddisp_exp)." Demanda: ".recuperaExpoente(tratanumero(($nporpool/count($arrPool[$keypool], COUNT_RECURSIVE))),$qtddisp_exp)." Produzir: ".recuperaExpoente(tratanumero($produzir),$qtddisp_exp);//estoque / demanda
        }else{
            $arrS[$key]['produzir']='';            
            $arrS[$key]['descrperc_deficit']='Estoque: '.recuperaExpoente(tratanumero($arrS[$key]['dispsemen']),$qtddisp_exp)." Demanda: ".recuperaExpoente(tratanumero(($nporpool/count($arrPool[$keypool], COUNT_RECURSIVE))),$qtddisp_exp);//estoque / demanda
        }
        

        $arrS[$key]['rotdeficit']=recuperaExpoente(tratanumero($nporpool/count($arrPool[$keypool], COUNT_RECURSIVE)),$qtddisp_exp);// necessidade por semente
       
    }
    reset($arrS);
    reset($arrPool);
    
    //print_r($arrS); die;
       
?>
                <div style="width: max-content;">                   
                          
<?     
    
       $linhadiv="<div style='border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;'>";
       $flinhadiv="</div>";
       $aberto='Y'; 
       if($qtdx<1){
            echo($linhadiv."Cliente não possui semente Aprovada.");
            echo($flinhadiv);
        }
     //while($rowx=mysqli_fetch_assoc($resx)){
        foreach($arrS as $key => $value) {
        
            if(empty($arrS[$key]['idpool'])){
                if($idlotepool){
                    echo($flinhadiv); 
                }
                $idlotepool='';
                echo($linhadiv);                    
            }elseif($arrS[$key]['idpool']!=$idlotepool){
                if($idlotepool){
                    echo($flinhadiv); 
                }
                echo($linhadiv);
                $idlotepool=$arrS[$key]['idpool'];
                 $aberto='Y';  
            }
            
     
?>
                    
                    <a  href="javascript:janelamodal('?_modulo=semente&_acao=u&idlote=<?=$arrS[$key]['idlote']?>')" class="nowrap font10 tip">
                            <i class="fa fa-star laranja bold btn-lg" title="<?=$arrS[$key]['descr']?>"></i>
                            <?
                            if($arrS[$key]['vencido']=='Y'){
                            ?>
                            <i class="fa fa-exclamation-triangle vermelho fa-1x pointer" title="Vence em <?=dma($arrS[$key]['vencimento'])?>."></i>
                            <?
                            }
                            ?>
                            <?=$arrS[$key]['partida']?>/<?=$arrS[$key]['exercicio']/*?> -(<?=recuperaExpoente(tratanumero($arrS[$key]['deficit']),$qtddisp_exp)*/?> - <?=round($arrS[$key]['perc_deficit'],2)?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <span><?=$arrS[$key]['descr']?> <br> <?=$arrS[$key]['descrperc_deficit']?></span>
                    </a>

<?    
        if(empty($arrS[$key]['idpool'])){
            echo($flinhadiv);
             $aberto='N';  
        }
     }//while($rowx=mysqli_fetch_assoc($resx)){
     if($aberto=='Y'){
           echo($flinhadiv);
     }
?>
                    </div>
                </div>
<?
}
 
//listar concentrados
function listaconcentrados($clausulalote='',$clausulad='',$idpessoa,$idprodserv){
                //Listar os concentrados
    global $semestoque,$arrS, $arrPool,$haproduzir;
    
     
    $sqp="select pf.idprodserv as idprodservvacina,v.descr as vacina,v.descrcurta,pf.idpessoa,
                CASE WHEN 
                           (select sum(l.qtdprod) from lote l 
                                   where (l.idprodserv=pf.idprodserv 
                                   and l.idpessoa=pf.idpessoa 
                                   and l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO')) 
                            )>sum(pf.qtd)     
                   THEN 
                           (select sum(l.qtdprod) from lote l 
                                   where (l.idprodserv=pf.idprodserv 
                                   and l.idpessoa=pf.idpessoa 
                                   and l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO')) 
                               )
                   ELSE sum(pf.qtd) 
                   END as qtd,                   
                    p.nome, concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose,
                    fi.idprodserv,ps.descr,fi.qtdi,fi.qtdi_exp,ifnull(v.qtdpadrao,1) as qtdpadrao,ps.qtdpadrao as padraoconcentrado,ps.qtdpadrao_exp
            from  
                           prodservforn pf 
                   join prodserv v on(v.idprodserv = pf.idprodserv)
                   join pessoa p on(pf.idpessoa=p.idpessoa and p.status='ATIVO' )
                   join prodservformula f on(pf.idprodservformula=f.idprodservformula)
                   join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
                   join prodserv ps on(ps.idprodserv=fi.idprodserv and ps.especial='Y')
                    -- left join lote l on(l.idprodserv=pf.idprodserv 
                    -- and l.idpessoa=pf.idpessoa and l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO'))
           where pf.status='ATIVO' 	
           and pf.qtd>0
           AND v.tipo='PRODUTO' 
           and v.venda ='Y' 
           and v.especial='Y'
           ".getidempresa('pf.idempresa','prodserv')."
           and pf.idpessoa=".$idpessoa."
            and fi.idprodserv=".$idprodserv."
           ".$clausulalote."                            
            ".$clausulad."  
           group by pf.idpessoa,fi.idprodserv,fi.qtdi,fi.qtdi_exp
           
            union  
           
            select  p.idprodserv as idprodservvacina,p.descr as vacina,p.descrcurta,l.idpessoa, sum(l.qtdpedida) as qtd,
                    pf.nome, concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose,
                    fi.idprodserv,psi.descr,fi.qtdi,fi.qtdi_exp,ifnull(p.qtdpadrao,1) as qtdpadrao,psi.qtdpadrao as padraoconcentrado,psi.qtdpadrao_exp
                from  lote l 
                    join prodserv p on(p.idprodserv=l.idprodserv and p.venda='Y' and p.tipo='PRODUTO' and p.especial='Y')
                    join unidade u on(l.idunidade=u.idunidade and u.producao='Y')  
                    join pessoa pf on(pf.idpessoa = l.idpessoa)
                    join prodservformula f on(f.idprodservformula=l.idprodservformula)
                    join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
                    join prodserv psi on(psi.idprodserv=fi.idprodserv and psi.especial='Y')
                where  l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO')
                and not exists (select 1 from prodservforn pf where pf.idprodserv=l.idprodserv and pf.idpessoa =l.idpessoa 
                                and pf.idprodservformula=l.idprodservformula and pf.qtd>0 )
                and pf.idpessoa=".$idpessoa."
                and fi.idprodserv=".$idprodserv."
                ".getidempresa('p.idempresa','prodserv')."
               ".$clausulalote."                            
                ".$clausulad."  
               group by l.idpessoa,fi.idprodserv,fi.qtdi,fi.qtdi_exp order by descr
                ";
          echo("<!-- concentrados ".$sqp." -->"); 
        $resp= d::b()->query($sqp) or die("erro ao buscar concentrados que preciso: " . mysqli_error(d::b()) . "<p>SQL: ".$sqp);
        $preciso=0;
        $nvacinasprog="";
        while($rowp=mysqli_fetch_assoc($resp)){
            //Buscar quanto e necessario para produzir o concentrado
            $preciso=$preciso+calculapreciso($rowp['qtdi'],$rowp['qtdpadrao_exp'],$rowp['qtd'],$rowp['qtdpadrao']);
            $Vqtddisp_exp=$rowp['qtdpadrao_exp'];
            $nvacinasprog.=$rowp['qtd']." FR: ".$rowp['descrcurta']." - ".$rowp['rotulo'].".<hr>";
        }
        //listar as sementes
        listarsementes($idpessoa,$idprodserv,$preciso,$Vqtddisp_exp);

        $sqq="select l.idlote,pl.descr,l.partida,l.exercicio,l.status,l.vencimento,f.qtd as qtddisp,f.qtd_exp as qtddisp_exp,l.qtdprod,l.qtdprod_exp,     
            GROUP_CONCAT(DISTINCT(concat(s.partida,'/',s.exercicio)) SEPARATOR ' ') as sementes           
            from lote l 
            join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL')
            join prodserv pl on(l.idprodserv=pl.idprodserv and pl.especial='Y')
            join lotecons c on(c.idobjeto=l.idlote and c.tipoobjeto ='lote' and c.qtdd>0)
            join lote s on(c.idlote=s.idlote)
            join prodserv p on(s.idprodserv=p.idprodserv and p.especial='Y')
            where l.idpessoa = ".$idpessoa."
            and l.status not in ('ESGOTADO','CANCELADO','REPROVADO','REPROVADO')
            and l.idprodserv=".$idprodserv."
                ".getidempresa('pl.idempresa','prodserv')." 
             group by l.idlote
             order by l.partida";
        $rqq= d::b()->query($sqq) or die("erro ao buscar concentrados: " . mysqli_error(d::b()) . "<p>SQL: ".$sqq);
        $qtdcon= mysqli_num_rows($rqq);

        if($qtdcon>0){
?>			
<!--<?=$sqq?>!-->
			<table >
				
<?
                                        $tenho=0;
					while($roq=mysqli_fetch_assoc($rqq)){
                                            //$Vqtddisp_exp=$roq['qtddisp_exp'];
                                            
                                                                                      
                                            
                                                if($roq['status']=='APROVADO'){
                                                        $botao='label-success';
                                                        $tenho=$tenho+$roq['qtddisp']; 
                                                        $qtddisp=$roq['qtddisp'];
                                                        $qtddisp_exp=$roq['qtddisp_exp'];
                                                }else{
                                                        $botao='label-primary ';
                                                        $tenho=$tenho+$roq['qtdprod']; 
                                                        $qtddisp=$roq['qtdprod'];
                                                        $qtddisp_exp=$roq['qtdprod_exp'];
                                                }
                                                
                                            $sqls="select s.idlote,concat(s.partida,'/',s.exercicio) as partida 
                                                    from lote l 
                                                    join prodserv pl on(l.idprodserv=pl.idprodserv and pl.especial='Y')
                                                    join lotecons c on(c.idobjeto=l.idlote and c.tipoobjeto ='lote' and c.qtdd>0)
                                                    join lote s on(c.idlote=s.idlote)
                                                    join prodserv p on(s.idprodserv=p.idprodserv and p.especial='Y')
                                                    where l.idlote=".$roq['idlote'];
                                            $ress= d::b()->query($sqls) or die("erro ao buscar sementes do concentrado: " . mysqli_error(d::b()) . "<p>SQL: ".$sqls);
						
?>
				<tr >					
                                    <td title="<?=$roq['descr']?>-<?=$roq['status']?> <?=dma($roq['vencimento'])?>" colspan="2">
                                        <span class="label <?=$botao?> fonte10 itemestoque  especial especialvisivel">
                                            <a href="?_modulo=formalizacao&_acao=u&idlote=<?=$roq['idlote']?>" target="_blank" style="color: inherit;">
                                                <?=$roq['partida']?>/<?=$roq['exercicio']?>
                                            </a>
                                            <?=recuperaExpoente(tratanumero($qtddisp),$qtddisp_exp)?>
                                            <div class="insumosEspeciais" style="font-size: 10px !important;">
                                                
                                                <?
                                                while($rowss=mysqli_fetch_assoc($ress)){
                                                    /*
                                                    if(empty($arrS[$rowss['idlote']]['rotdeficit'])){
                                                        $arrS[$rowss['idlote']]['rotdeficit']='0.00';
                                                    }
                                                     
                                                     */
                                                ?>
                                                <i class="fa fa-star amarelo bold btn-lg" ></i>
                                                <?=$rowss['partida'] /*?>-(<?=$arrS[$rowss['idlote']]['rotdeficit']*/?>	
                                                <?}?>
                                            </div>	
                                        </span>
                                    </td>

				</tr>
<?
					}
                                        if($tenho<$preciso){
                                            //$semestoque=1;
                                           // $descr= traduzid('prodserv', 'idprodserv', 'descr', $idprodserv);
 ?>
                                <!--tr >
                                    <td title='<?=$descr?>'>  
                                        <i class="fa fa-exclamation-triangle laranja btn-lg pointer"></i>
                                        <span class="vermelho"><b>PRODUTO COM ESTOQUE BAIXO.</b></span>    
                                    </td>
                                </tr -->
     <?
                                        }else{
?>
                                <!-- tr class='ocultar' id='<?=$idpessoa?>_<?=$idprodserv?>'>
                                    <td></td>
                                </tr -->
                                <?
                                        }
                                       
                                        if($tenho<0){$tenho='0.00';}
						
?>
                                <tr >
                                    <td>
                                        <span class="demanda hidden" id='<?=$idpessoa?>__<?=$idprodserv?>'>
                                            <?=$nvacinasprog?>
                                            <hr>Demanda: <b><?=recuperaExpoente(tratanumero($preciso),$Vqtddisp_exp)?></b> Estoque:<b><?=recuperaExpoente(tratanumero($tenho),$Vqtddisp_exp)?></b>
                                        </span>
                                    </td>
                                </tr>
<?  
                                listaconcentradoProd($idpessoa,$idprodserv,$Vqtddisp_exp);
?>                                
				
			</table>
<?
        }else{//if($qtdcon>0){
            
            $descr= traduzid('prodserv', 'idprodserv', 'descr', $idprodserv);
?>
                        <table >
				
                            <tbody>
                                <?  listaconcentradoProd($idpessoa,$idprodserv,$Vqtddisp_exp);?>
                                 <tr >
                                     <td>
                                         <span class="demanda hidden" id='<?=$idpessoa?>__<?=$idprodserv?>'>
                                             <?=$nvacinasprog?>
                                            <hr>Demanda: <b><?=recuperaExpoente(tratanumero($preciso),$Vqtddisp_exp)?></b> Estoque:<b>0.00</b>
                                         </span>
                                     </td>
                                </tr>
				
                            </tbody>
                        </table>
<?
        }

}//function listaconcentrados(){

function calculapreciso($qtdi,$qtdi_exp,$qtdprod,$qtdpadrao=1){
    
    if(strpos(strtolower(recuperaExpoente(tratanumero($qtdi),$qtdi_exp)),"d")){
        $arrExp=explode('d', strtolower(recuperaExpoente(tratanumero($qtdi),$qtdi_exp)));
        $vqtdpadrao= $arrExp[0];
        $varde='d';

        $v1=(floatval($qtdprod)* floatval($vqtdpadrao))/floatval($qtdpadrao);
        $v2=$v1*$arrExp[1];	

        $rotpreciso=$v2;
              


    }elseif(strpos(strtolower(recuperaExpoente(tratanumero($qtdi),$qtdi_exp)),"e")){
        $arrExp=explode('e', strtolower(recuperaExpoente(tratanumero($qtdi),$qtdi_exp)));
        $vqtdpadrao=  $arrExp[0];
        $varde='e';
        
        $v1=(floatval($qtdprod)* floatval($vqtdpadrao))/floatval($qtdpadrao);
        $v2=$v1*$arrExp[1];	

        $rotpreciso=$v2;
    }else{
        $vqtdpadrao=(empty($qtdi) or $qtdi==0)?1:$qtdi; 
        $varde='';

        $preciso=(floatval($qtdprod)* floatval($vqtdpadrao))/floatval($qtdpadrao);
        $rotpreciso=$preciso;
       
    }
    
    return $rotpreciso;
}

function listaconcentradoProd($idpessoa,$idprodserv,$Vqtddisp_exp=''){
    global $semestoque,$arrS, $arrPool,$haproduzir; 
    //print_r($arrS);
      $arraProd=array();
      $faltaestoque=0;
      foreach($arrS as $key => $value) {
         if($arrS[$key]['produzir']>0){
            $semestoque=1;
            $arraProd[$arrS[$key]['pool']][$key]['spartida']=$arrS[$key]['partida']."/".$arrS[$key]['exercicio'];
            $arraProd[$arrS[$key]['pool']][$key]['produzir']=$arrS[$key]['produzir'];
            $faltaestoque=1;
         }
      }


      foreach($arraProd as $pool => $lote) {
          $haproduzir=$haproduzir+1;
?>
            <tr >					
           <td title="Concentrado à produzir">
               <span class="label label-danger fonte10 itemestoque  especial especialvisivel">
                   <a href="?_modulo=prodserv&_acao=u&idprodserv=<?=$idprodserv?>" target="_blank" style="color: inherit;  font-size: 12px !important;">
                       Concentrado: <?=traduzid('prodserv', 'idprodserv', 'codprodserv', $idprodserv)?>
                   </a>

                   <div class="insumosEspeciais" style="font-size: 10px !important;">

<?                                              
            $sproduzir=0;
            foreach($lote as $idlote =>$value) {
?>
                    <i class="fa fa-star amarelo bold fa-1x btn-lg" ></i> 
<?
                    echo($value['spartida']);
                    $sproduzir=$sproduzir+$value['produzir'];
            }
?>
                    </div>
                    <span style="font-size: 10px !important">Produzir: <?=recuperaExpoente(tratanumero($sproduzir),$Vqtddisp_exp)?></span>
                </span>
            </td>
            <td>
                 <a class="fa fa-plus-circle pointer fade hoververde fa-2x"
                   href="javascript:janelamodal('?_modulo=formalizacao&_acao=i&idpessoa=<?=$idpessoa?>&idprodserv=<?=$idprodserv?>')"
                    ></a>

            </td>

        </tr> 
<?                                
    }
    if($faltaestoque==0){
?>
        <tr class='ocultar' id='<?=$idpessoa?>_<?=$idprodserv?>'>
            <td></td>
        </tr>
<?
    }
}
?>

<script> 
    <?if($status=='FALTA'){?>
$(document).ready(function(){ 
 
    
    var arrayOfIds = $.map($(".ocultar"), function(n, i){
      return n.id;
    });
    
    jQuery.each( arrayOfIds, function( i, val ) {
        $( "." + val ).hide();
    });
 /*   
    var arrayOfIdsp = $.map($(".ocultar_pessoa"), function(n, i){
      return n.id;
    });
    
    jQuery.each( arrayOfIdsp, function( i, val ) {
        $( "." + val ).hide();
    });
   */ 

});
    <?}?>
// copiar o texto no cabecalho
$(document).ready(function(){ 
    
    var arrayIdD = $.map($(".demanda"), function(n, i){
      return n.id;
    });
    
    jQuery.each( arrayIdD, function( i, val ) {
        var texto = $( "#" + val ).html();
        $( "." + val ).html(texto);
    });
    
    var obs=$("#obsfim").html(); 
     $("#obsinicio").html(obs);
  
});

jCli=<?=$jCli?>;// autocomplete cliente


//mapear autocomplete de clientes
jCli = jQuery.map(jCli, function(o, id) {
    return {"label": o.nome, value:id}
});
//autocomplete de clientes
$("[name*=idpessoa]").autocomplete({
    source: jCli
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});	

jProd=<?=$jProd?>;// autocomplete 


//mapear autocomplete de clientes
jProd = jQuery.map(jProd, function(o, id) {
    return {"label": o.descr, value:id}
});
//autocomplete 
$("[name*=idprodserv]").autocomplete({
    source: jProd
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});    
    
function pesquisar(){
    var idpessoa =  $("[name=idpessoa]").attr("cbvalue");
    var idprodserv = $("[name=idprodserv]").attr("cbvalue");
    var status = $("[name=status]").val();

    var str="idprodserv="+idprodserv+"&idpessoa="+idpessoa+"&status="+status;
  
        CB.go(str);
}

$(document).keypress(function(e) {
  if(e.which == 13) {
    pesquisar();
  }
});



//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
<?die;?>