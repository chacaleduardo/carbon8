<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$ler=true;//Gerar logs de erro
$rid="\n".rand()." - Sislaudo: ";
if($ler)error_log($rid.basename(__FILE__, '.php'));

session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
	$prefu="stdin_";
	include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
	include_once("../inc/php/functions.php");
}


function getidempresacron($param1, $param2){
	return 'and '.$param1.' = 1 ';
	
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



?>


<?
/*
 * colocar condição para executar select
 */
//if($_GET and (!empty($idpessoa) or !empty($idprodserv) or !empty($status))){
?>    

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
                        fi.idprodserv,ps.descr,fi.qtdi,fi.qtdi_exp,ifnull(f.qtdpadraof,1) as qtdpadrao
                        
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
                    and pf.valido='Y'
                    and fi.status = 'ATIVO'
                    and pf.qtd>0
                    ".getidempresacron('pf.idempresa','prodserv')."
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
						fi.idprodserv,psi.descr,fi.qtdi,fi.qtdi_exp,ifnull(f.qtdpadraof,1) as qtdpadrao
                       
              from  lote l 
                    join prodserv p on(p.idprodserv=l.idprodserv and p.venda='Y' and p.tipo='PRODUTO' and p.especial='Y')
                    join unidade u on(l.idunidade=u.idunidade and u.producao='Y')  
                    join pessoa pf on(pf.idpessoa = l.idpessoa)
                    join prodservformula f on(f.idprodservformula=l.idprodservformula)
                    join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
                    join prodserv psi on(psi.idprodserv=fi.idprodserv and psi.especial='Y')
              where  l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO')
               ".getidempresacron('p.idempresa','prodserv')."
              and fi.status = 'ATIVO'
              and not exists (select 1 from prodservforn pf where pf.idprodserv=l.idprodserv and pf.idpessoa =l.idpessoa 
                                and pf.idprodservformula=l.idprodservformula and pf.qtd>0 and pf.valido='Y' )  
                ".$clausulalote."
                ".$clausulad."                    
               
            ) as u group by u.idprodserv,u.idpessoa
             order by descr";
    

    $resv = d::b()->query($sqlv) or die("erro ao buscar programacoes: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlv);
    $qtdprodform= mysqli_num_rows($resv);
     //echo("<!-- primeiro: ".$sqlv." -->");
?>
  <!-- sql-2= <?=$sqlv?>-->
<?
        if($qtdprodform>0){
            $semestoque=0;
            while($r= mysqli_fetch_assoc($resv)){
               
                 if($r['idprodserv']!=$idprodservlop and !empty($idprodservlop)){
                     
?>
            
<?  
                 }
                if($r['idprodserv']!=$idprodservlop){ 
                    
                    if($semestoque==0 and !empty($idprodservlop)){
?>
         
<?                                                    
                    }
                    $semestoque=0;
                   $idprodservlop=$r['idprodserv'];
?> 

	<?		
                }

?>

<?                       
                                            
                                           
                                            $arrS=array();
                                            $arrPool=array();
                                            listaconcentrados($clausulalote,$clausulad,$r['idpessoa'],$r['idprodserv']);
                                           
?>          
                                                    

		
                    
 
<?				
			}//while($r= mysqli_fetch_assoc($resv)){
                                            
		}
			 $sql = 
		"replace into dashboard (
			iddashboard,
			dashboard,
			dashboard_title,
			panel_id,
			panel_class_col,
			panel_title,
			card_id,
			card_class_col,
			card_url,
			card_notification_bg,
			card_notification,
			card_color,
			card_border_color,
			card_bg_class,
			card_title,
			card_value,
			card_icon,
			card_title_modal,
			card_url_modal,
			url,
			especial,
			idempresa,
			status
		)
		values(
		1,
		'dashproducao',
		'PRODUÇÃO',
		'dashproducaoconcentradosproduzir',
		'col-md-4',
		'AUTÓGENAS CONCENTRADOS A PRODUZIR - ORGANIZACIONAL',
		'dashproducaoconcentradosproduzirtriagem',
		'col-md-6 col-sm-6 col-xs-6',
		'?_modulo=gerconcentrado&_acao=u&idprodserv=&idpessoa=&status=FALTA&novajanela=Y',
		'fundovermelho',
		'0',
		if (".$haproduzir." > 0,'danger','success'),
		if (".$haproduzir." > 0,'danger','success'),
		'',
		'TRIAGEM',
		".$haproduzir.",
		'fa-print',
		'CONCENTRADOS - TRIAGEM',
		'_modulo=formalizacao&_acao=u',
		'report/dashproducao.php',
		'Y',
		1,
		'ATIVO'
		
		
		)";
		d::b()->query($sql	);
?>
CONCENTRADOS À PRODUZIR: <?=$haproduzir?>
                                          
  <?
 //}//if($_GET and !empty($clausulad)){

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
               ".getidempresacron('p.idempresa','prodserv')."
            and fi.status='ATIVO'
           order by lp.idpool desc,l.partida";
    //echo("<!-- sementes: ".$sqlx." -->");
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
        $sqlc="select l.idlote,f.qtd as qtddisp,f.qtd_exp as qtddisp_exp,l.qtdprod,l.qtdprod_exp,pf.qtdpadraof as qtdpadrao,pf.qtdpadraof_exp as qtdpadrao_exp,l.status
                from lotecons c 
                join lote l on(l.idlote = c.idobjeto and c.tipoobjeto ='lote' and l.status not in ('ESGOTADO','CANCELADO','REPROVADO'))
                 join lotefracao f on(f.idlote=l.idlote and f.status='DISPONIVEL')
                join prodserv p on(p.idprodserv =l.idprodserv and p.especial='Y')
				join prodservformula pf on(pf.idprodserv = p.idprodserv and pf.status='ATIVO')
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
                                
                          
<?     
    
    
?>
                  
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
                    fi.idprodserv,ps.descr,fi.qtdi,fi.qtdi_exp,ifnull(f.qtdpadraof,1) as qtdpadrao,fc.qtdpadraof as padraoconcentrado,fc.qtdpadraof_exp as qtdpadrao_exp
            from  
                           prodservforn pf 
                   join prodserv v on(v.idprodserv = pf.idprodserv)
                   join pessoa p on(pf.idpessoa=p.idpessoa and p.status='ATIVO' )
                   join prodservformula f on(pf.idprodservformula=f.idprodservformula and f.status='ATIVO')
                   join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
                   join prodserv ps on(ps.idprodserv=fi.idprodserv and ps.especial='Y')
				   join prodservformula fc on(fc.idprodserv = ps.idprodserv and fc.status='ATIVO')
                    -- left join lote l on(l.idprodserv=pf.idprodserv 
                    -- and l.idpessoa=pf.idpessoa and l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO'))
           where pf.status='ATIVO' 	
           and pf.qtd>0
           and fi.status = 'ATIVO'
           and pf.valido='Y'
           AND v.tipo='PRODUTO' 
           and v.venda ='Y' 
           and v.especial='Y'
           ".getidempresacron('pf.idempresa','prodserv')."
           and pf.idpessoa=".$idpessoa."
            and fi.idprodserv=".$idprodserv."
           ".$clausulalote."                            
            ".$clausulad."  
           group by pf.idpessoa,fi.idprodserv,fi.qtdi,fi.qtdi_exp
           
            union  
           
            select  p.idprodserv as idprodservvacina,p.descr as vacina,p.descrcurta,l.idpessoa, sum(l.qtdpedida) as qtd,
                    pf.nome, concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose,
                    fi.idprodserv,psi.descr,fi.qtdi,fi.qtdi_exp,ifnull(f.qtdpadraof,1) as qtdpadrao,psif.qtdpadraof as padraoconcentrado,psif.qtdpadraof_exp as qtdpadrao_exp
                from  lote l 
                    join prodserv p on(p.idprodserv=l.idprodserv and p.venda='Y' and p.tipo='PRODUTO' and p.especial='Y')
                    join unidade u on(l.idunidade=u.idunidade and u.producao='Y')  
                    join pessoa pf on(pf.idpessoa = l.idpessoa)
                    join prodservformula f on(f.idprodservformula=l.idprodservformula and f.status='ATIVO')
                    join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
                    join prodserv psi on(psi.idprodserv=fi.idprodserv and psi.especial='Y')
					join prodservformula psif on(psi.idprodserv=psif.idprodserv and psif.status='ATIVO')
                where  l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA','REPROVADO')
                and not exists (select 1 from prodservforn pf where pf.idprodserv=l.idprodserv and pf.idpessoa =l.idpessoa 
                                and pf.idprodservformula=l.idprodservformula and pf.qtd>0 and pf.valido='Y' )
                and pf.idpessoa=".$idpessoa."
                and fi.idprodserv=".$idprodserv."
                and fi.status = 'ATIVO'
                ".getidempresacron('p.idempresa','prodserv')."
               ".$clausulalote."                            
                ".$clausulad."  
               group by l.idpessoa,fi.idprodserv,fi.qtdi,fi.qtdi_exp order by descr
                ";
          //echo("<!-- concentrados ".$sqp." -->"); 
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
                ".getidempresacron('pl.idempresa','prodserv')." 
             group by l.idlote
             order by l.partida";
        $rqq= d::b()->query($sqq) or die("erro ao buscar concentrados: " . mysqli_error(d::b()) . "<p>SQL: ".$sqq);
        $qtdcon= mysqli_num_rows($rqq);

        if($qtdcon>0){
?>			
<!--<?=$sqq?>!-->
			
				
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
                                                
                                          
						
?>
			
<?
					}

                                       
                                        if($tenho<0){$tenho='0.00';}
						
?>
                              
<?  
                                listaconcentradoProd($idpessoa,$idprodserv,$Vqtddisp_exp);
?>                                
				
			
<?
        }else{//if($qtdcon>0){
            
            $descr= traduzid('prodserv', 'idprodserv', 'descr', $idprodserv);
?>
                      
                                <?  listaconcentradoProd($idpessoa,$idprodserv,$Vqtddisp_exp);?>
                                 
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
            

                   
<?                                
    }
   
}
?>

<script> 
   
</script>
<?die;?>