<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}

$geraarquivo=$_GET['geraarquivo'];
$gravaarquivo=$_GET['gravaarquivo'];

$concentradosmesatual = 0;
$concentradosmespassado = 0;
//error_reporting(E_ALL);

//die();

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "nf";
$pagvalcampos = array(
	"idnf" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from "._DBAPP.".nf where idnf = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */

ob_start();
?>

<?

//FUNÇÃO QUE LISTA OS CONCENTRADOS
function listaConcentrados($idpessoa = null, $idprodserv = null, $status = null){
	
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


	$sqlv="select pf.idprodserv as idprodservvacina,v.descr as vacina,pf.idpessoa,CASE WHEN sum(l.qtdprod)>sum(pf.qtd) THEN sum(l.qtdprod) ELSE sum(pf.qtd) END as qtd,
		p.nome, concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose,
		fi.idprodserv,ps.descr,fi.qtdi,fi.qtdi_exp,ifnull(v.qtdpadrao,1) as qtdpadrao,ps.qtdpadrao as padraoconcentrado,ps.qtdpadrao_exp
            from  
                           prodservforn pf 
                   join prodserv v on(v.idprodserv = pf.idprodserv)
                   join pessoa p on(pf.idpessoa=p.idpessoa and p.status='ATIVO' )
                   join prodservformula f on(pf.idprodservformula=f.idprodservformula)
                   join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
                   join prodserv ps on(ps.idprodserv=fi.idprodserv and ps.especial='Y')
                    left join lote l on(l.idprodserv=pf.idprodserv 
                                                and l.idpessoa=pf.idpessoa and l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA'))
           where pf.status='ATIVO' 	
           and fi.status='ATIVO'
           and pf.qtd>0
           AND v.tipo='PRODUTO' 
           and v.venda ='Y' 
           and v.especial='Y'
           ".getidempresa('pf.idempresa','prodserv')."
           ".$clausulalote."                            
            ".$clausulad."  
           group by pf.idpessoa,fi.idprodserv,fi.qtdi,fi.qtdi_exp
           
            union  
           
            select  p.idprodserv as idprodservvacina,p.descr as vacina,l.idpessoa, sum(l.qtdpedida) as qtd,
                    pf.nome, concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose,
                    fi.idprodserv,psi.descr,fi.qtdi,fi.qtdi_exp,ifnull(p.qtdpadrao,1) as qtdpadrao,psi.qtdpadrao as padraoconcentrado,psi.qtdpadrao_exp
                from  lote l 
                    join prodserv p on(p.idprodserv=l.idprodserv and p.venda='Y' and p.tipo='PRODUTO' and p.especial='Y')
                    join unidade u on(l.idunidade=u.idunidade and u.producao='Y')  
                    join pessoa pf on(pf.idpessoa = l.idpessoa)
                    join prodservformula f on(f.idprodservformula=l.idprodservformula)
                    join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
                    join prodserv psi on(psi.idprodserv=fi.idprodserv and psi.especial='Y')
                where  l.status not in('APROVADO','ESGOTADO','CANCELADO','QUARENTENA')
                and not exists (select 1 from prodservforn pf where pf.idprodserv=l.idprodserv and pf.idpessoa =l.idpessoa 
                                and pf.idprodservformula=l.idprodservformula and pf.qtd>0 )
  
                ".getidempresa('p.idempresa','prodserv')."
               ".$clausulalote."                            
                ".$clausulad."  
               group by l.idpessoa,fi.idprodserv,fi.qtdi,fi.qtdi_exp order by descr";
    

    $resv = d::b()->query($sqlv) or die("erro ao buscar programacoes: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlv);
    
	return($resv);
}


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



function listarsementes($idpessoa,$idprodserv,$preciso,$qtddisp_exp){
    global $arrS,$arrPool;
    
    $sqlx="select  distinct(l.idlote),lp.idpool,c.descr,case when l.vencimento < (DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 MONTH),'%Y-%m-%d')) then 'Y' else 'N' end as vencido,l.*          
           from prodservformula f
           join prodservformulains fi on(fi.idprodservformula=f.idprodservformula)
           join prodserv p on(p.idprodserv=fi.idprodserv and p.especial ='Y')
           join lote l on(l.idprodserv=fi.idprodserv and l.status not in ('ESGOTADO','CANCELADO','REPROVADO') 
           and l.situacao='APROVADO'
            and l.tipoobjetosolipor='resultado')
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
       $sqlc="select l.idlote,l.qtddisp,l.qtddisp_exp,l.qtdprod,l.qtdprod_exp,p.qtdpadrao,p.qtdpadrao_exp,l.status
                from lotecons c 
                join lote l on(l.idlote = c.idobjeto and c.tipoobjeto ='lote' and l.status not in ('ESGOTADO','CANCELADO','REPROVADO'))
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
    
 
       $linhadiv="<div style='border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;'>";
       $flinhadiv="</div>";
       $aberto='Y'; 
       if($qtdx<1){
     //       echo($linhadiv."Cliente não possui semente Aprovada.");
     //       echo($flinhadiv);
        }
    
        foreach($arrS as $key => $value) {
        
            if(empty($arrS[$key]['idpool'])){
                if($idlotepool){
                   // echo($flinhadiv); 
                }
                $idlotepool='';
        //        echo($linhadiv);                    
            }elseif($arrS[$key]['idpool']!=$idlotepool){
                if($idlotepool){
                //    echo($flinhadiv); 
                }
               // echo($linhadiv);
                $idlotepool=$arrS[$key]['idpool'];
                 $aberto='Y';  
            }
            
  
        if(empty($arrS[$key]['idpool'])){
        //    echo($flinhadiv);
             $aberto='N';  
        }
     }
     if($aberto=='Y'){
        //   echo($flinhadiv);
     }

}


function listaconcentradoProd($idpessoa,$idprodserv,$Vqtddisp_exp=''){
    global $semestoque,$arrS, $arrPool;
	$haproduzir = 0;

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
		$sproduzir=0;
		foreach($lote as $idlote =>$value) {
			$sproduzir=$sproduzir+$value['produzir'];
		}
                   
    }

	return $haproduzir;
	
}


//CONCENTRADOS A PRODUZIR
//INICIO
$resv = listaConcentrados($idpessoa,$idprodserv, $status);
$idprodserv = '';
$produzidos = 0;
 while($rowp= mysqli_fetch_assoc($resv)){
	if ($rowp['idprodserv'] != $idprodserv){
		$preciso = 0;
		$rowp['idprodserv'] == $idprodserv;
	}
	$preciso=$preciso+calculapreciso($rowp['qtdi'],$rowp['qtdpadrao_exp'],$rowp['qtd'],$rowp['qtdpadrao']);
	$Vqtddisp_exp=$rowp['qtdpadrao_exp'];
	//echo $rowp["descr"].'<br>Preciso: '.$preciso.'<Br>Vqtddisp_exp: '.$Vqtddisp_exp.'<br><hr><br>'; 		
			
			
	listarsementes($rowp['idpessoa'],$rowp['idprodserv'],$preciso,$Vqtddisp_exp);		
	$total = $total + listaconcentradoProd($rowp['idpessoa'],$rowp['idprodserv'],$Vqtddisp_exp);
	
}
echo mysqli_num_rows($resv).'<br>';
echo $total;
//CONCENTRADOS A PRODUZIR
//FIM
?>