<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
    include_once("../inc/php/cbpost.php");
}

$idpessoa=$_GET["idpessoa"];
$idprodserv=$_GET["idprodserv"];
$status=$_GET["status"];
$idplantel=$_GET['idplantel'];
$validacao=$_GET['validacao'];

function getcliente(){
	
	$sql= "select p.idpessoa,p.nome 
			from pessoa p
			where p.idtipopessoa=2 
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
	
	$sql= "select p.idprodserv,p.descr
			from prodserv p
			where p.status='ATIVO' 
			AND p.tipo='PRODUTO' 
			".getidempresa('p.idempresa','prodserv')." 
			AND p.venda ='Y' 
			AND exists (select 1 from lote l where l.idprodserv = p.idprodserv)
			AND p.especial='Y' order by p.descr";

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
	text-align: right;
	margin: 3px;
}
.itemestoque.especial{
	display:none;
}
.itemestoque.especial.especialvisivel{
	display:inline-block !important;
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
						   <?fillselect("select 'ATIVO','Ativo' union select 'TODAS','Todas'",$status);?>
				   </select>
			   </div>
            </div>
			<div class="row">      
                <div class="col-md-1">Produto:</div>
                <div class="col-md-5">	
					<input id="idprodserv"  type="text" name="idprodserv"  cbvalue="<?=$idprodserv?>" value="<?=$arrProd[$idprodserv]["descr"]?>" style="width: 60em;" vnulo>
				
				</div>
               
                <div class="col-md-2"></div>
				<div class="col-md-4"> 
				 
			   </div>
            </div>
			<div class="row">
				<div class="col-md-1">Tipo/Especie:</div>
                <div class="col-md-2">
					<select class='size10' name="idplantel" id="idplantel"  >
						<option value=""></option>
						   <?fillselect("select idplantel,plantel from plantel where status='ATIVO' and idplantel in(3,2,4,6) order by plantel",$idplantel);?>
				   </select>
				</div>
               
                <div class="col-md-1">Validação:</div>
				<div class="col-md-5"> 
				   <select class='size10' name="validacao" id="validacao"  >	
					   <option value=""></option>
						   <?fillselect("select 'O','Validado' union select 'V','Pendente'",$validacao);?>
				   </select>
			   </div>
				
			</div>

            <div class="row"> 
				<div class="col-md-8"></div>
				<div class="col-md-1 nowrap">
				   <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
					   <span class="fa fa-search"></span>
				   </button> 
					&nbsp;&nbsp;&nbsp;&nbsp;
					<i id="cbCompartilharItem" class="fa fa-comment-o fa-2x fade pointer hoverlaranja compartilhar" title="Compartilhar este item" onclick="compartilharItem()"></i>
				</div>
            </div>
        </div>
    </div>
    </div>
</div>
<?


if(!empty($idpessoa)){
    $clausulalote .= " and l.idpessoa =".$idpessoa." ";
}

if(!empty($idprodserv)){
    $clausulad .= " and p.idprodserv =  ".$idprodserv."";
}

if($status=='ATIVO'){
	$clausulals .= " and ls.status not in ('CANCELADO','ESGOTADO')";
}else{
	$clausulals .= " and ls.status not in ('CANCELADO') ";
}

if($idplantel){
	$strplantel=" and f.idplantel=".$idplantel;
	$strinplantel=" and exists (select 1 from prodservformula f where f.idprodserv= p.idprodserv and f.idplantel = ".$idplantel.") ";
}else{
	$strplantel='';
	$strinplantel='';
}

if($validacao=='V'){
	$strvalidacao=" and not exists (select 1 from prodservforn pf 
						where pf.idprodserv=l.idprodserv 
						and pf.idprodservformula= f.idprodservformula
						and pf.idpessoa=l.idpessoa and pf.validadoem > DATE_SUB(now(), INTERVAL 1 MONTH))";
	
	$invalidacao=" and not exists (select 1 from prodservforn pf 
						where pf.idprodserv=p.idprodserv 
						and pf.validadoem > DATE_SUB(now(), INTERVAL 1 MONTH))";
	
}elseif ($validacao=='O') {
		$strvalidacao=" and exists (select 1 from prodservforn pf 
						where pf.idprodserv=l.idprodserv 
						and pf.idprodservformula= f.idprodservformula
						and pf.idpessoa=l.idpessoa and pf.validadoem > DATE_SUB(now(), INTERVAL 1 MONTH))";
		
		$invalidacao=" and exists (select 1 from prodservforn pf 
						where pf.idprodserv=p.idprodserv 
						and pf.validadoem > DATE_SUB(now(), INTERVAL 1 MONTH))";
}else{
	$strvalidacao=" ";
	$invalidacao=" ";
}

if($_GET and (!empty($idpessoa) or !empty($idprodserv) or !empty($idplantel) or $validacao=='V' or $validacao=='O')){

$sql="select p.idprodserv,p.codprodserv,p.descr 
	from prodserv p 
	where p.status='ATIVO' 
	AND p.tipo='PRODUTO' 
	and p.venda ='Y' 
	and p.especial='Y' 
	".$invalidacao."
	".$strinplantel."
	".$clausulad."
	and exists (select 1 from  lote l 
					where l.idprodserv = p.idprodserv ".$clausulalote."
						and l.idpessoa is not null
		) order by p.descr";

 $res=d::b()->query($sql) or die("Erro ao buscar PRODUTOS sql=".$sql);
 $qtdrows=mysqli_num_rows($res);


?>  <!-- <?=$sql?>-->
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-body">Resultado da pequisa<span id="cbResultadosInfo" numrows="<?=$qtdrows?>">  <?=$qtdrows?> produto(s) encontrado(s)</span></div>
    </div>
	</div>
</div>
	
<?
    if($qtdrows>0){

	
        while($row=mysqli_fetch_assoc($res)){
           $produto=$row["descr"];
?>

<?/*
			$sqlv="select l.idprodserv,p.nome,p.idpessoa,l.idprodservformula, concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose
					from lote l 
						join pessoa p on(l.idpessoa=p.idpessoa ".$clausulalote.")
						join prodservformula f on(l.idprodservformula=f.idprodservformula and f.status='ATIVO' ".$strplantel.")
					where l.idprodserv=".$row['idprodserv']."  ".$strvalidacao."
					group by l.idprodserv,p.idpessoa,l.idprodservformula order by p.nome";
	*/		
			$sqlv="select l.idprodserv,p.nome,p.idpessoa,l.idprodservformula, concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,f.dose,
					if (pf.validadoem < DATE_SUB(now(), INTERVAL 1 MONTH),'V','O') as validade,pf.idprodservforn,pf.validadopor,pf.validadoem,pf.qtd
					from lote l 
						join pessoa p on(l.idpessoa=p.idpessoa ".$clausulalote.")
						join prodservformula f on(l.idprodservformula=f.idprodservformula and f.status='ATIVO' ".$strplantel." )
                        left join prodservforn pf on(pf.status='ATIVO' 
														and p.idpessoa = pf.idpessoa
														AND pf.idprodservformula=f.idprodservformula
                                                        and pf.idprodserv=l.idprodserv)
					where l.idprodserv=".$row['idprodserv']."  ".$strvalidacao." 
					group by l.idprodserv,p.idpessoa,l.idprodservformula order by p.nome";
			$resv = d::b()->query($sqlv) or die("erro ao buscar partidas: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlv);
?>
  <!-- <?=$sqlv?>-->
<?
			while($r= mysqli_fetch_assoc($resv)){
			/*	
				$sp="select 
						if (p.validadoem < DATE_SUB(now(), INTERVAL 1 MONTH),'V','O') as validade,p.*
						from prodservforn p
						where p.idpessoa=".$r['idpessoa']." 
						and p.idprodserv=".$row['idprodserv']." 
						and p.idprodservformula=".$r['idprodservformula']." 
						and p.status='ATIVO'";
				$rp=d::b()->query($sp) or die("erro ao buscar prodservforn: " . mysqli_error(d::b()) . "<p>SQL: ".$sp);
				$rowpf=mysqli_fetch_assoc($rp);
			*/
				if(!empty($r['idprodservforn'])){
					$strurl="_acao=u&idprodservforn=".$r['idprodservforn'];
					if($r['validade']=='V' or empty($r['validadoem'])){
						$cor='vermelho';
						$texto="<font color='red'>VALIDAÇÃO PENDENTE</font>";
						
						$funcaovalida="validaproduto(".$r['idprodservforn'].",'".$_SESSION["SESSAO"]["USUARIO"]."','".date("d/m/Y")."')";
						if(!empty($r['validadopor'])){
							$rotulovalida=" por: ".$r['validadopor']." em: ".dma($r['validadoem']);
						}else{
							$rotulovalida="";
						}
					}else{
						$cor='verde';
						$texto="<font color='green'>VALIDADO</font>";
						$funcaovalida="retiravalidaproduto(".$r['idprodservforn'].")";
						$rotulovalida=" por: ".$r['validadopor']." em: ".dma($r['validadoem']);
					}
				}else{
					$strurl="_acao=i&idprodserv=".$row['idprodserv']."&idpessoa=".$r['idpessoa']."&idprodservformula=".$r['idprodservformula'];
					$cor='vermelho';
					$texto="<font color='red'>VALIDAÇÃO PENDENTE</font>";
					$rotulovalida=" ";
					$funcaovalida="geravalidaproduto(".$row['idprodserv'].",".$r['idpessoa'].",".$r['idprodservformula'].",'".$_SESSION["SESSAO"]["USUARIO"]."','".date("d/m/Y")."')";
				}
				
				$sqx="select ps.idprodserv,ps.descr,ls.partida,ls.exercicio,ls.vencimento,ls.idlote,ls.status,ls.situacao,lp.idpool,lp.idlotepool,ls.observacao
					,case when ls.vencimento < (DATE_FORMAT(DATE_ADD(now(), INTERVAL 3 MONTH),'%Y-%m-%d')) then 'Y' else 'N' end as vencido,
					 a.idregistro,a.exercicio,st.subtipoamostra,ls.tipificacao,ls.orgao,ls.idprodserv as idprodservsemente
				from prodservformula f join prodservformulains i on(i.idprodservformula= f.idprodservformula)
				join prodserv c on (i.idprodserv=c.idprodserv and c.especial='Y')
				join prodservformula fc on(fc.idprodserv =i.idprodserv)
				join prodservformulains ic on(ic.idprodservformula= fc.idprodservformula)
				join prodserv ps on (ps.idprodserv = ic.idprodserv and ps.especial='Y' )
				join lote ls on(ls.idprodserv = ic.idprodserv and ls.tipoobjetosolipor='resultado' ".$clausulals." )
				join  resultado r on(r.idresultado =ls.idobjetosolipor)
				join amostra a on(a.idamostra=r.idamostra and a.idpessoa =".$r['idpessoa'].")
				left join lotepool lp on(lp.idlote=ls.idlote and lp.status='ATIVO')
				left join subtipoamostra st on(st.idsubtipoamostra=a.idsubtipoamostra)
				where f.idprodserv= ".$r['idprodserv']." ".$strplantel."
				and ic.status = 'ATIVO'
						 group by ls.idlote order by ps.idprodserv,lp.idpool,ls.status,ls.npartida asc";
				
				$resx = d::b()->query($sqx) or die("erro ao buscar sementes: " . mysqli_error(d::b()) . "<p>SQL: ".$sqx);
				$qtdx=mysqli_num_rows($resx);
				if($qtdx>0){
?>
	<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
			<i onclick="<?=$funcaovalida?>" title="Validar Produto." class="fa fa-check pointer <?=$cor?>">
			&nbsp;<label class="alert-warning"><?=$texto?></label>&nbsp;</i><?=$rotulovalida?>&nbsp;&nbsp;&nbsp;&nbsp;
			<strong><a href="?_modulo=prodservforn&<?=$strurl?>" target="_blank" style="color: inherit;a"><?=$r['nome']?> - <?=$produto?> - <?=$r['rotulo']?> </a></strong>  
<?
			if($r['idprodservforn']){
					$fatqtd="geraprodservforn(this,'u',".$row['idprodserv'].",".$r['idpessoa'].",".$r['idprodservformula'].",".$r['idprodservforn'].")";	
			}else{
					$fatqtd="geraprodservforn(this,'i',".$row['idprodserv'].",".$r['idpessoa'].",".$r['idprodservformula'].",0)";					
			}
?>		
			
			<input value="<?=$r['qtd']?>" onchange="<?=$fatqtd?>" class="size7" type="text">
		</div>
        <div class="panel-body">	

			<table class="table table-striped planilha">           

			<tr>
				<td>
					<div class="row">
<?				

				$idprodserv='';
?>
						<!--<?=$sqx?>-->
<?
				while($rx= mysqli_fetch_assoc($resx)){
					$idprodservsemente=$rx['idprodservsemente'];
					if($rx['situacao']=='APROVADO'){
						$botao='btn-success';
					}elseif($rx['situacao']=='REPROVADO'){
						$botao='btn-danger';
					}else{
						$botao='btn-warning';
					}
					if($rx['idprodserv']!=$idprodserv){
						if(!empty($idprodserv)){
							$abriuprod='N';
							if($divaberto=='Y'){
								$divaberto='N';
								echo("</div>");
							}
							
							//Listar os concentrados
							listaconcentrados($r['idpessoa'],$idprodservsementeant,$row['idprodserv'],$r['idprodservformula'],$strplantel,$r['qtd']);

?>
						
					</div>
					</div>			
<?						
							
						}//if(!empty($idprodserv)){
?>
					<div class="col-md-6" >
					<div class="panel panel-default" >
						<div class="panel-heading"><?=$rx['descr']?></div>
<?
						$idprodserv=$rx['idprodserv'];
						$idprodservsementeant=$rx['idprodservsemente'];
						$idpool="";
						$existepoll='';
						$abriuprod='Y';
					}//if($rx['idprodserv']!=$idprodserv){
		
					if($rx['idpool']>0){
							
						$tempool='Y';
						$classdrag="soltavel";
						$existepoll='Y';
						
					}else{
					
						$tempool='N';
						$classdrag="dragInsumo";
					}//if($rx['idpool']>0){
					
					
					if(!empty($rx['idpool'])){
						if(!empty($idpool) and $idpool!=$rx['idpool']){
							$divaberto='Y';
?>
							</div>
							<div style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;">
<?
						}elseif(empty($idpool)){
							$divaberto='Y';
?>
						<div style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;">
<?
						}
						$idpool=$rx['idpool'];
					}elseif(!empty($idpool) and empty($rx['idpool'])){
						$idpool='';
						$divaberto='N';
?>
						</div>
<?						
					}//if(!empty($rx['idpool'])){
					if(empty($rx['orgao'])){
						$rx['orgao']=$rx['observacao'];
					}
?>
						<div class="panel-body" <?if($tempool=='N'){?> style="border: 1px silver dotted; margin: 4px; background-color: #ffffffd6;"<?}?>>
							<table id='tblotes'>
								
								<tr class="soltavel dragInsumo" idloteins="<?=$rx['idlote']?>" idpool="<?=$rx['idpool']?>">
									<td>
										<a class="fa fa-bars hoverazul pointer fade" href="?_modulo=semente&_acao=u&idlote=<?=$rx['idlote']?>" target="_blank" title="Lote"></a>
									</td>
									<td>
										<button  title="<?=$rx['subtipoamostra']?> <?=$rx['tipificacao']?> <?=$rx['orgao']?>" status="<?=$rx['status']?>" id="<?=$rx['idlote']?>" situacao="<?=$rx['situacao']?>"  type="button" class="btn <?=$botao?> btn-xs" onclick="alterast(this,<?=$rx['idlote']?>)"> 
											<?if($rx['vencido']=='Y'){?><i class="fa fa-exclamation-triangle vermelho fa-1x pointer" title="Vencida"></i><?}?>	
											<?=$rx['partida']?>/<?=$rx['exercicio']?> <?=$rx['status']?> - <?=dma($rx['vencimento'])?>
										 </button>
											
									</td>

									<?if($tempool=='N'){?>
									<td title="Arrastar para um Poll.">
										<i class="fa fa-arrows hoverazul cinzaclaro hover move"></i>
									</td>
									<?}elseif($tempool=='Y'){?>
									<td >
										<i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="inativapool(<?=$rx['idlotepool']?>)" title="Retirar do Pool "></i>	
									</td>
									<?}?>
								</tr>
							</table>							
						</div>	

<?						
				}//while($rx= mysqli_fetch_assoc($resx)){
					//Listar os concentrados da ultima semente do loop

				if(!empty($idprodservsemente)){
					
					//Listar os concentrados
					listaconcentrados($r['idpessoa'],$idprodservsemente,$row['idprodserv'],$r['idprodservformula'],$strplantel,$r['qtd']);

				}//if(!empty($idprodservsemente)){	
				
				if($divaberto=='Y'){
					$divaberto='N';
					echo("</div>");
				}
				if($abriuprod=='Y'){
					echo("</div></div>");
					$abriuprod='N';
				}
				
?>	
							</div>
						</div>
					</div>
				</td>
			</tr>
		</table>
			
		</div>
    </div>
    </div>
</div>
<?				}
			}//while($r= mysqli_fetch_assoc($resv)){
  ?>

<?
	    //print_r($arrvisita);
        }// while($row=mysql_fetch_assoc($res)){ 

  }else{//if($qtdrows>0){

    echo("Não foram encadas produtos nestas condições.");
      
  }//if($qtdrows>0){
  ?>
        </div>
    </div>
    </div>
</div>
<?
}//if($_GET and !empty($clausulad)){

//listar concentrados
function listaconcentrados($idpessoa,$idprodservsementeant,$idprodserv,$idprodservformula,$strplantel,$qtdprod=0){
						//Listar os concentrados
					$sqq="select p.idprodserv,p.descr,l.idlote,l.partida,l.exercicio,l.status,l.vencimento,l.qtddisp,l.qtddisp_exp
						,GROUP_CONCAT(DISTINCT(concat(ls.partida,'/',ls.exercicio)) SEPARATOR ' ') as sementes,i.qtdi,i.qtdi_exp,ifnull(pf.qtdpadrao,1) as qtdpadrao
						from prodservformula f 
						join prodserv pf on (pf.idprodserv=f.idprodserv)
						join prodservformulains i on (i.idprodservformula=f.idprodservformula)
						join prodserv p on (i.idprodserv=p.idprodserv and p.especial='Y')
						join lote l on(l.idprodserv=p.idprodserv and l.status not in ('ESGOTADO','CANCELADO','REPROVADO') and l.idpessoa=".$idpessoa.")
						join lotecons c on(c.idobjeto=l.idlote and c.tipoobjeto ='lote' and c.qtdd>0)
						join lote ls on(ls.idlote =c.idlote and ls.idprodserv=".$idprodservsementeant.")
						join prodserv ps on(ps.idprodserv=ls.idprodserv and ps.especial='Y')
						where f.idprodserv=".$idprodserv."
						and f.idprodservformula=".$idprodservformula."
						and f.status ='ATIVO' 
						and i.status = 'ATIVO'
						".$strplantel."
						group by l.idlote
						order by p.descr,l.partida";
			
					$rqq= d::b()->query($sqq) or die("erro ao buscar concentrados : " . mysqli_error(d::b()) . "<p>SQL: ".$sqq);
					$qtdcon= mysqli_num_rows($rqq);

					if($qtdcon>0){
?>			
<!--<?=$sqq?>!-->
			<table class="table table-striped planilha">
				
<?
					$lin=0;
					while($roq=mysqli_fetch_assoc($rqq)){
						
						
						if(strpos(strtolower(recuperaExpoente(tratanumero($roq['qtdi']),$roq['qtdi_exp'])),"d")){
							$arrExp=explode('d', strtolower(recuperaExpoente(tratanumero($roq['qtdi']),$roq['qtdi_exp'])));
							$vqtdpadrao= $arrExp[0];
							$varde='d';

							$v1=(floatval($qtdprod)* floatval($vqtdpadrao))/floatval($roq['qtdpadrao']);
							$v2=$v1*$arrExp[1];	
							if(strpos(strtolower(recuperaExpoente(tratanumero($roq['qtddisp']),$roq['qtddisp_exp'])),"d")){
								$arrExplt=explode('d', strtolower(recuperaExpoente(tratanumero($roq['qtddisp']),$roq['qtddisp_exp'])));
								$preciso=round($v2/$arrExplt[1],2);
								$rotpreciso=$preciso.'d'.$arrExplt[1];
								
								$tenho=$arrExplt[0];
								
								//$preciso=recuperaExpoente($preciso,$roq['qtddisp_exp'])
							}else{
								$preciso=$v2;
								$rotpreciso=$v2;
								$tenho=$roq['qtddisp'];
							}
						
					    }elseif(strpos(strtolower(recuperaExpoente(tratanumero($roq['qtddisp']),$roq['qtddisp_exp'])),"e")){
							$arrExp=explode('e', strtolower(recuperaExpoente(tratanumero($roq['qtddisp']),$roq['qtddisp_exp'])));
							$vqtdpadrao=  $arrExp[0];
							$varde='e';
						}else{
							$vqtdpadrao=(empty($roq['qtdi']) or $roq['qtdi']==0)?1:$roq['qtdi']; 
							$varde='';

							$preciso=(floatval($qtdprod)* floatval($vqtdpadrao))/floatval($roq['qtdpadrao']);
							$rotpreciso=$preciso;
							if(strpos(strtolower(recuperaExpoente(tratanumero($roq['qtddisp']),$roq['qtddisp_exp'])),"d")){
									$arrExplt=explode('d', strtolower(recuperaExpoente(tratanumero($roq['qtddisp']),$roq['qtddisp_exp'])));
									$tenho=$arrExplt[0]/$arrExplt[1];
							}else{
								$tenho=$roq['qtddisp'];
							}
					    }
						if($tenho<$preciso){
							$btn='danger';
						}else{
							$btn='success';
						}
						
						
					if($lin==0){
?>
				<tr>
					<th >Concentrado</th>					
				</tr>
<?				
					}	
						
?>
				<tr>
					
					<td title="<?=dma($roq['vencimento'])?>	<?=$roq['status']?>">
						<span class="label label-<?=$btn?> fonte10 itemestoque  especial especialvisivel">
							<a href="?_modulo=formalizacao&_acao=u&idlote=<?=$roq['idlote']?>" target="_blank" style="color: inherit;">
								<?=$roq['partida']?>/<?=$roq['exercicio']?>
							</a>
							<?=recuperaExpoente(tratanumero($roq['qtddisp']),$roq['qtddisp_exp'])?>
							<div class="insumosEspeciais">
								<i class="fa fa-star amarelo bold" ></i>
								<?=$roq['sementes']?>								
							</div>	
<?
							if($qtdprod>0){
							echo("Usar: ".$rotpreciso);
							}
?>
						</span>
					</td>
				</tr>
<?
					}
?>
				
			</table>
<?
			}//if($qtdcon>0){

}//function listaconcentrados(){
?>
<script>
	
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
	var idplantel = $("[name=idplantel]").val();
	var validacao = $("[name=validacao]").val();

    var str="idprodserv="+idprodserv+"&idpessoa="+idpessoa+"&status="+status+"&validacao="+validacao+"&idplantel="+idplantel;
  
        CB.go(str);
}

function alterast(vthis,inidlote){
	debugger;
    // alert(inidrhevento);
    var situacao = $(vthis).attr('situacao');
	var status = $(vthis).attr('status');
    var nova_st;
    var ant_bt;
    var bt;
   
    if(situacao=='APROVADO'){
        nova_st='REPROVADO';
		ant_bt="btn-success";
        bt="btn-danger";

    }else if(situacao=='REPROVADO' && status!='APROVADO'){
        nova_st='PENDENTE';
		ant_bt="btn-danger";
        bt="btn-warning";
	}else if(situacao=='REPROVADO' && status=='APROVADO'){
		nova_st='APROVADO';
		ant_bt="btn-danger";
        bt="btn-success";
	}else{
		nova_st='REPROVADO';
		ant_bt="btn-warning";
        bt="btn-danger";
	}
  
    CB.post({
        objetos: "_x_u_lote_idlote="+inidlote+"&_x_u_lote_situacao="+nova_st        
		,parcial:true
		,posPost: function(data, textStatus, jqXHR){
			$(vthis).attr('situacao',nova_st);
            $(vthis).removeClass( ant_bt ).addClass( bt );
		}
    });
}    

function inativapool(inidpool){
		CB.post({
        objetos: "_x_u_lotepool_idlotepool="+inidpool+"&_x_u_lotepool_status=INATIVO"
   		,parcial:true
		
    });	
	
}

function gerapool(inidlote){
    CB.post({
        objetos: "_x_i_pool_status=ATIVO"
        ,refresh:false
		,parcial:true
		,posPost: function(data, textStatus, jqXHR){
		  geralotepool(jqXHR.getResponseHeader("x-cb-pkid"),inidlote);	
		}
    });	
	
}
function geralotepool(inidpool,inidlote){
	CB.post({
        objetos: "_x_i_lotepool_idpool="+inidpool+"&_x_i_lotepool_idlote="+inidlote
   		,parcial:true
		
    });	
}
function criapool(inidlote,inidlote2){
    CB.post({
        objetos: "_x_i_pool_status=ATIVO"
        ,refresh:false
		,parcial:true
		,posPost: function(data, textStatus, jqXHR){
		  geralotepoollote(jqXHR.getResponseHeader("x-cb-pkid"),inidlote,inidlote2);	
		}
    });	
	
}

function geralotepoollote(inidpool,inidlote,inidlote2){
	CB.post({
        objetos: "_x_i_lotepool_idpool="+inidpool+"&_x_i_lotepool_idlote="+inidlote+"&_y_i_lotepool_idpool="+inidpool+"&_y_i_lotepool_idlote="+inidlote2
   		,parcial:true
		
    });	
}

//Permitir ordenar/arrastar os TR de insumos
$("#tblotes tbody").sortable({
	update: function(event, objUi){
		ordenaInsumos();
	}
});

//Permitir dropar o insumo
$(".soltavel").droppable({
	drop: function( event, ui ) {
		$this=$(this);//TR
		var idlote=ui.draggable.attr("idloteins");
		var idpool=$this.attr("idpool");
		var idlote2=$this.attr("idloteins");
		debugger;
		if(idpool==""){
			criapool(idlote,idlote2);
		}else{
			geralotepool(idpool,idlote);
		}
	}
});

function validaproduto(idprodservforn,usuario,vdata){
	CB.post({
        objetos: "_x_u_prodservforn_idprodservforn="+idprodservforn+"&_x_u_prodservforn_validadopor="+usuario+"&_x_u_prodservforn_validadoem="+vdata
   		,parcial:true		
    });	
}

function retiravalidaproduto(idprodservforn){
		CB.post({
        objetos: "_x_u_prodservforn_idprodservforn="+idprodservforn+"&_x_u_prodservforn_validadopor=' '&_x_u_prodservforn_validadoem=' '"
   		,parcial:true		
    });	
}

function geravalidaproduto(idprodserv,idpessoa,idprodservformula,usuario,vdata){
	CB.post({
        objetos: "_x_i_prodservforn_idprodserv="+idprodserv+"&_x_i_prodservforn_idpessoa="+idpessoa+"&_x_i_prodservforn_idprodservformula="+idprodservformula+"&_x_i_prodservforn_validadopor="+usuario+"&_x_i_prodservforn_validadoem="+vdata
   		,parcial:true		
    });	
}

function geraprodservforn(vthis,acao,idprodserv,idpessoa,idprodservformula,idprodservforn){
	$(vthis).val();
	CB.post({
        objetos: "_x_"+acao+"_prodservforn_idprodserv="+idprodserv+"&_x_"+acao+"_prodservforn_idpessoa="+idpessoa+"&_x_"+acao+"_prodservforn_idprodservformula="+idprodservformula+"&_x_"+acao+"_prodservforn_qtd="+$(vthis).val()+"&_x_"+acao+"_prodservforn_idprodservforn="+idprodservforn
   		,parcial:true		
    });	
}

//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
</script>