<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
    include_once("../inc/php/cbpost.php");
}
$emissao1=$_GET["emissao1"];
$emissao2=$_GET["emissao2"];
$cliente=$_GET["cliente"];
$idrepresentante=$_GET["idrepresentante"];
$idtipopessoa=$_GET["idtipopessoa"];
$campoplantel=$_GET["campoplantel"];
$dataSelecionada=$_GET["dataSelecionada"];
$statuscrm=$_GET["statuscrm"];
$ordenacao=$_GET["ordenacao"];
if(empty($ordenacao)){$ordenacao='nome';}
if($ordenacao=='nome'){
    $sentido='asc';
}else{
    $sentido='desc';
}

if(!empty($dataSelecionada)){
    $databusca = explode("-", $dataSelecionada);
    $dataini = validadate($databusca[0]);
    $datafim = validadate($databusca[1]);
    if ($dataini and $datafim){
        $dataini=$dataini;
        $datafim=$datafim;
    }else{
        $dataini='';
        $datafim='';
    }
}

function getRepresentante(){
    
    if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){
       
        $sqlrep="select p.idpessoa,p.nome, p.idtipopessoa from pessoacontato c ,pessoa p
                    where c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"]."
                    and p.idpessoa = c.idpessoa
                   ".getidempresa('p.idempresa','pessoa')."
                    and p.idtipopessoa = 12";
    }elseif(array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"])){
        
               $sqlrep="select idpessoa,nome, idtipopessoa from (
                            select p.idpessoa,p.nome, p.idtipopessoa
                            from pessoa p
                            where  p.idtipopessoa = 12
                            and p.idpessoa in ( ".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].",".$_SESSION["SESSAO"]["IDPESSOA"].")
                            ".getidempresa('p.idempresa','pessoa')."
                            union 
                            select f.idpessoa,f.nome, f.idtipopessoa
                            from pessoa f 
                            where f.idtipopessoa = 1
                            and f.idpessoa in ( ".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].",".$_SESSION["SESSAO"]["IDPESSOA"].")
                            ".getidempresa('f.idempresa','pessoa')."
                            and f.status in ('ATIVO','PENDENTE')
                            and exists (select 1 from pessoacontato c join pessoa p on(p.idpessoa=c.idpessoa and p.status='ATIVO' and p.idtipopessoa = 2)
                            where c.idcontato = f.idpessoa)
                        ) as u order by nome";    
        
    }else{
        
        $sqlrep="select idpessoa,nome, idtipopessoa from (
            select p.idpessoa,p.nome, p.idtipopessoa
            from pessoa p
            where  p.idtipopessoa = 12          
            ".getidempresa('p.idempresa','pessoa')."
            union 
            select f.idpessoa,f.nome, f.idtipopessoa 
            from pessoa f 
            where f.idtipopessoa = 1          
            ".getidempresa('f.idempresa','pessoa')."
            and f.status in ('ATIVO','PENDENTE')
            and exists (select 1 from pessoacontato c join pessoa p on(p.idpessoa=c.idpessoa and p.status='ATIVO' and p.idtipopessoa = 2)
            where c.idcontato = f.idpessoa)
        ) as u order by nome";    


    }

    $res = d::b()->query($sqlrep) or die("getRepresentante: Falha: ".mysqli_error(d::b())."\n".$sqlrep);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
        $arrret[$r["idpessoa"]]["idtipopessoa"]=$r["idtipopessoa"];
        $arrret[$r["idpessoa"]]["idpessoa"]=$r["idpessoa"];
    }
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrRep=getRepresentante();
//print_r($arrCli); die;
$jRep=$JSON->encode($arrRep);

?>
<style>
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
    width: 200px;
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
                <div class="col-md-4"> <input name="cliente" class="size30"  value="<?=$cliente?>"></div>
                <div class="col-md-1">Plantel:</div>
                <div class="col-md-3"> 
                <select class="size10" name="campoplantel" id="campoplantel"  >
                       <option value=""></option>
                   <?fillselect("select idplantel,plantel 
                                   from plantel 
                                   where status='ATIVO' 
                                   and idempresa=".cb::idempresa()."
                                   and prodserv='Y' order by plantel",$campoplantel);?>
                   </select>
                </div> 
                <div class="col-md-1">Status CRM:</div>
                <div class="col-md-2"> 
                    <select class="size10" name="statuscrm" id="statuscrm"  >
                       <option value=""></option>
                    <?
                    fillselect("select 'VISITAR','Visitar' union select 'EFETIVO','Comprador Efetivo' union select 'ESPORADICO','Comprador Esporádico' union select 'NAO COMPRADOR','Não Comprador'",$statuscrm);
                    ?>
                   </select>
                </div>
            </div>
           <?  if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){$idrepresentante=$_SESSION["SESSAO"]["IDPESSOA"]; $readonly="readonly='readonly'";}?>
            
            <div class="row">
                <div class="col-md-1">Representante:</div>
                <div class="col-md-4"> 
                    <input <?=$readonly?>  type="text" name="idrepresentante"  id="idrepresentante" cbvalue="<?=$idrepresentante?>" cbvalue="<?=$idrepresentante?>" value="<?=$arrRep[$idrepresentante]["nome"]?>" style="width: 30em;" >
		</div>                

                <div class="col-md-1">Intervalo:</div>
                <div class="col-md-3">
                    <button id="btSelecaoData" class="btn btn-default">
                        <i class="fa fa-calendar"></i>
                        <?if(empty($dataSelecionada)){?>
                        <span id="dataSelecionada"><span class="cinza">Selecione a data</span></span>
                        <?}else{?>
                        <span id="dataSelecionada"><?=$dataSelecionada?></span>
                        <?}?>
                    </button>
                </div>
            </div>
               <div class="row">
                <div class="col-md-1">Ordenação:</div>
                <div class="col-md-4"> 
                     <select class="size10" name="ordenacao" id="ordenacao"  >
                           <?
                            fillselect("select 'nome','Cliente' union select 'statuscrm','Status' union select 'ultimoevento','Ultimo Evento' union select 'ultimavenda','Ultima Venda' union select 'dosesvendidas','Doses Vendidas'  union select 'total','Total Venda'",$ordenacao);
                            ?>
                   </select>
		</div>
                <div class="col-md-1"></div>
                <div class="col-md-3"></div>
            </div>
<script>
$("#btSelecaoData").daterangepicker({
    "autoUpdateInput": false,
    //"singleDatePicker": true,
    "showDropdowns": true,
    "linkedCalendars": false,
    "opens": "left",
    "locale": CB.jDateRangeLocale
}).on("apply.daterangepicker", function(e, picker) {
    $out = $("#dataSelecionada");
    //Exemplo:
    //Se $out for um elemento html, utilizar o metodo html().
    //Se for um input, utilizar val() conforme a necessidade
    //Outras opcoes: http://www.daterangepicker.com/
    let strIntervalo=picker.startDate.format(picker.locale.format) + "-" + picker.endDate.format(picker.locale.format);
    $out.html(strIntervalo);
   // alert("Data selecionada");
});
</script>
            <div class="row"> 
		<div class="col-md-10"></div>
		<div class="col-md-2">
		   <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
		       <span class="fa fa-search"></span>
		   </button> 
		</div>	   
            </div>
        </div>
    </div>
    </div>
</div>
<?


if(!empty($cliente)){
    $clausulad .= " and (p.nome like   '%".$cliente."%')";
}
if(!empty($campoplantel)){
     $clausulad .= " and exists(select 1 from plantelobjeto o where o.idobjeto =p.idpessoa and o.tipoobjeto ='pessoa' and o.idplantel = ".$campoplantel.") ";
}
if(!empty($statuscrm)){
    $clausulad .= " and (p.statuscrm =   '".$statuscrm."')";
}

if(!empty($idrepresentante)){
    
    	   
	    $sqlc=" 
                SELECT 
                    pc.idpessoa , pc.idtipopessoa, c.idpessoa as idrepresentante, p.idtipopessoa as idtipopessoarepresentante
                FROM
                    pessoacontato c
                        LEFT JOIN
                    pessoa pc ON pc.idpessoa = c.idcontato
                        LEFT JOIN
                    pessoa p ON p.idpessoa = c.idpessoa
                WHERE
                    idcontato = ".$idrepresentante." limit 1";
	    
	    
	    $resc = d::b()->query($sqlc) or die("erro ao buscar contatos: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlc);
        echo("<!-- ".$sqlc." -->");
	    $y=0;
	    while($rc= mysqli_fetch_assoc($resc)){
	        if(!empty($rc['idrepresentante']) and $rc['idtipopessoa'] == 15){
	            $idrepresentante = $rc['idrepresentante'];
	        }
		    
	    }    
   /*
	$clausularep=" join pessoacontato c on(c.idcontato =".$idrepresentante.")
                        join pessoacontato c2 on (c2.idcontato =c.idpessoa and c2.idpessoa = p.idpessoa)";
	*/
	  $clausularep=" join pessoacontato c2 on (c2.idcontato =".$idrepresentante." and c2.idpessoa = p.idpessoa)";
    
    //$clausularepcp=" and cc.idpessoa=".$idrepresentante." ";
}

if($_GET and (!empty($idrepresentante) or !empty($campoplantel)) and !empty($dataini) and !empty($datafim)){

$sql="select *  from (
                    SELECT 
                        p.idpessoa,p.nome,p.statuscrm,
                            (select DATEDIFF(now(),e.inicio) 
                                            from evento e            
                                       where                        
                                            e.idempresa = ".cb::idempresa()."
                                            and e.ideventotipo in (52,51,120,92,43)
                                            and e.idpessoaev=p.idpessoa     
                                            and e.inicio between '".$dataini." 00:00:00' and '".$datafim." 23:59:59' order by e.inicio desc limit 1
                               )as ultimoevento,
                           (select DATEDIFF(now(),dtemissao) 
                                        from nf a
                                        where a.tiponf='V' 
                                        and a.geracontapagar='Y'
                                        and a.idpessoa =p.idpessoa  
                                        and dtemissao between '".$dataini." 00:00:00' and '".$datafim." 23:59:59'
                                        order by dtemissao desc limit 1) as ultimavenda,
                            round((select sum(i.qtd*f.dose)
                                            from nf a join nfitem i on(i.idnf=a.idnf and i.nfe='Y')
                                            join prodservformula f on(f.idprodservformula=i.idprodservformula)
                                              where a.tiponf='V' 
                                            and a.geracontapagar='Y'
                                            and a.idpessoa =p.idpessoa
                                            and a.dtemissao between '".$dataini." 00:00:00' and '".$datafim." 23:59:59'))  as dosesvendidas,
                                            (select sum(total)
                                            from nf a
                                            where a.tiponf='V' 
                                             and a.geracontapagar='Y'
                                            and a.idpessoa =p.idpessoa
                                            and dtemissao between '".$dataini." 00:00:00' and '".$datafim." 23:59:59'
                                            ) as total
                            FROM
                                pessoa p ".$clausularep."
                            WHERE
                                p.idtipopessoa = 2
                             AND p.status in ('ATIVO','PENDENTE')
                                    ".$clausulad."  
                     ) as u 
                    ORDER BY ".$ordenacao." ".$sentido;

 $res=d::b()->query($sql) or die("Erro ao buscar Clientes sql=".$sql);
 $qtdrows=mysqli_num_rows($res);


?>  <!-- <?=$sql?>-->
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Resultado da pequisa<span id="cbResultadosInfo" numrows="<?=$qtdrows?>"> (<?=$qtdrows?> resultados encontrados)</span>
         
                    <i  title="Mostrar Ocultos" ver="oculto" class="fa fa-eye pointer verde hoververmelho fa-1x" onclick="mostraocultos(this)"></i>
              
        </div>
        <div class="panel-body">
<?
    if($qtdrows>0){
?>
            <table class="table table-striped planilha">
            <thead>
            <tr>   
                <th></th>
                <th >Cliente</th>
                <th >Status</th>
                <th style="text-align: right !important;">Faturamento</th>
                <th style="text-align: right !important;">Doses Vendidas</th>
                <th style="text-align: right !important;">Última Venda</th>
                           
                <th style="text-align: right !important;">Úlltimo Evento</th>
                <th></th>  
                 
                <th style="width: 100px;"></th>
            </tr>
            </thead>
            <tbody>
<?
        $i=0;
	$arrvisita=array();
        $arrpl=array();
	$comissao=0;
	$fatacumulado=0;
        $total=0;
        $doses=0;
        while($row=mysqli_fetch_assoc($res)){
            $i=$i+1;
            
	   
	    $sqlv="select t.eventotipo,e.idevento,dma(e.inicio) as dmacriadoem,e.inicio,p.idpessoa,DATEDIFF(now(),e.inicio) as ultimoevento
                        from pessoa p 
                       join evento e on(e.idpessoaev=p.idpessoa)
                       join eventotipo t on(e.ideventotipo=t.ideventotipo)
                       where     
                            e.ideventotipo in (52,51,120,92,43) and
                            p.idpessoa=".$row['idpessoa']."
                            and e.idempresa = ".cb::idempresa()."
                            and e.inicio between '".$dataini." 00:00:00' and '".$datafim." 23:59:59'
                        order by e.inicio desc limit 1";
	    $resv = d::b()->query($sqlv) or die("erro ao buscar eventos: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlv);
echo("<!-- ".$sqlv." -->");
	    $y=0;
	    while($r= mysqli_fetch_assoc($resv)){
		    $arrvisita[$r["idpessoa"]][$y]["idevento"]=$r["idevento"];
		    $arrvisita[$r["idpessoa"]][$y]["eventotipo"]=$r["eventotipo"];
                    $arrvisita[$r["idpessoa"]][$y]["prazo"]=$r["dmacriadoem"];
                    $arrvisita[$r["idpessoa"]][$y]["ultimoevento"]=$r["ultimoevento"];
		$y=$y+1;
	    }
         
          
	
            $sqlc="select DATEDIFF(now(),dtemissao) as ultimaemissao, dma(dtemissao) as dmadtemissao,dtemissao,idnf 
                    from nf a
                    where a.tiponf='V' 
                    and a.geracontapagar='Y'
                    and a.idpessoa =".$row['idpessoa']."
                    and dtemissao between '".$dataini." 00:00:00' and '".$datafim." 23:59:59'
                    order by dtemissao desc limit 1";
	    $resc = d::b()->query($sqlc) or die("erro ao buscar ultima venda: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlc);
echo("<!-- ".$sqlc." -->");          
            $rowc=mysqli_fetch_assoc($resc);
            
            
        
            
            if($row['statuscrm']=='OCULTO'){
                $statuscrm='VISITAR'; 
                $corst="vermelho";
                $title='Desocultar';
                $classv='oculto';
                $display='display:none';
            }else{
                $statuscrm='OCULTO'; 
                $corst="verde";
                $title='Ocultar';
                $classv='visivel';
                $display='display:';
            }
            
?>
            <tr id="<?=$row['idpessoa']?>" class="<?=$classv?>"  style="<?=$display?>" > 
                <td>
                    <i statuscrm="<?=$statuscrm?>" title="<?=$title?>" class="fa fa-eye pointer <?=$corst?> hoververde fa-1x" onclick="alterastatus(this,<?=$row['idpessoa']?>)"></i>
                </td>
                <td>
                    <a class="pointer" title="cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row['idpessoa']?>')">
                        <?=$row["nome"]?>
                    
                  
                    <?
                    $sqle="select t.idtipoplantel,pl.idplantel,pl.plantel,t.tipoplantel,sum(p.qtd) as qtd
                                    from tipoplantel t 
                                     join tipoplantelpessoa p on(p.idtipoplantel = t.idtipoplantel and p.idpessoa = ".$row['idpessoa']."  and p.status='ATIVO' and p.qtd > 0)
                             join plantel pl on(pl.idplantel=t.idplantel)
                             group by t.idtipoplantel,pl.idplantel";
                    $rese = d::b()->query($sqle) or die("A Consulta de busca das especies falhou : " . mysqli_error(d::b()) . "<p>SQL:".$sqle);
                    $qtdesp=mysqli_num_rows($rese);
                    if($qtdesp>0){echo('<br>');}
                    while($rowe=mysqli_fetch_assoc($rese)){
                        echo($rowe["plantel"]." ".$rowe["tipoplantel"].": ".$rowe["qtd"]."&nbsp;&nbsp;&nbsp;");
                        $i=$rowe['idtipoplantel'];
                        $qtdp=intval(($arrpl[$i]['qtd'] > 0 ? $arrpl[$i]['qtd'] : 0));

                        $arrpl[$i]['idtipoplantel']=$rowe['idtipoplantel'];
                        $arrpl[$i]['plantel']=$rowe['plantel'];
                        $arrpl[$i]['tipoplantel']=$rowe['tipoplantel'];
                        $arrpl[$i]['qtd']=$qtdp + intval($rowe["qtd"]);
                        
                              
                    ?>
                      
                    <?
                    }
                    ?>
                    </a>
                </td> 
		<td>
                    <?if($row['statuscrm']=='OCULTO'){
                        $row['statuscrm']='';
                    }?>
		    <select  name="<?=$i?>_statuscrm" onchange="alteracrm(this,<?=$row['idpessoa']?>)" class="size10">
                        <?
                        fillselect("select 'VISITAR','Visitar' union select 'EFETIVO','Comprador Efetivo' union select 'ESPORADICO','Comprador Esporádico' union select 'NAO COMPRADOR','Não Comprador'",$row['statuscrm']);
                        ?>
                    </select>
		
		</td>
                <td  align="right">
                    
<?
                $sqlt="select total,nnfe,dma(dtemissao) as dtemissao, idnf, idempresa
                        from nf a
                        where a.tiponf='V' 
                         and a.geracontapagar='Y'
                        and a.idpessoa =".$row['idpessoa']."
                        and dtemissao between '".$dataini." 00:00:00' and '".$datafim." 23:59:59'
                        order by dtemissao ";
                $rest = d::b()->query($sqlt) or die("erro ao buscar total da venda: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlt);
                $strnf='';
                $sntotal=0;
                
                if(mysqli_num_rows($rest) >  0){
                     $strnf.="<tr><td>NFe:</td><td>Emissão:</td><td>R$:</td></tr>";
                }
                while($rowt=mysqli_fetch_assoc($rest)){
                    $sntotal=$sntotal+$rowt['total'];

                    $strnf.="
                       <tr>
                        <td>
                           ".$rowt['nnfe']."
                        </td>
                        <td> ".$rowt['dtemissao']."</td>
                        <td>
                            <a target='_blank' href='?_modulo=pedido&_acao=u&idnf=".$rowt['idnf']."&_idempresa=".$rowt['idempresa']."'>".number_format(tratanumero($rowt['total']), 2, ',', '.')."</a>
                        </td>
                        </tr>
                    ";	    

                }
                $total=$total+$sntotal;
		    ?>
                    
                    <div class="oVisita">
                        <a class="pointer" title=" " data-target="webuiPopover0" >                   
                          <?if($sntotal>0){?>
                        <?=number_format(tratanumero($sntotal), 0, ',', '.'); ?>
                         <?}?>
                        </a>
		    </div>
		    <div class="webui-popover-content">
			<table>
                               <?=$strnf?>
			</table>
		    </div>  
     
                </td>
                <td align="center" class="nowrap"> 
                    <?
                    $sqld="select round((i.qtd*f.dose)) as dose,p.descrcurta
                        from nf a join nfitem i on(i.idnf=a.idnf and i.nfe='Y')
                        join prodservformula f on(f.idprodservformula=i.idprodservformula)
                        join prodserv p on(i.idprodserv=p.idprodserv)
                        where a.tiponf='V' 
                        and a.geracontapagar='Y'
                        and a.idpessoa =".$row['idpessoa']."
                        and a.dtemissao between '".$dataini." 00:00:00' and '".$datafim." 23:59:59'";

			 $resd = d::b()->query($sqld) or die("erro ao buscar quantidade de doses: " . mysqli_error(d::b()) . "<p>SQL: ".$sqld);
                          $ndoses=0;   
                          $strd='';
                        while($rowd=mysqli_fetch_assoc($resd)){ 
                            $ndoses=$ndoses+$rowd['dose'];
			    
			   $strd.="<tr>
				<td>
				   ".$rowd['descrcurta']."
				</td>
				<td>
                                    ".$rowd['dose']."
                                </td>
			    </tr>";	    

			 }
                         $doses=$doses+$ndoses;
		    ?>
                    <div class="oVisita">
                        <a class="pointer" title=" " data-target="webuiPopover0" >                   
                          <?if($ndoses>0){?>
                        <?=number_format(tratanumero($ndoses), 0, ',', '.'); ?>
                         <?}?>
                        </a>
		    </div>
		    <div class="webui-popover-content">
			<table>
                               <?=$strd?>
			</table>
		    </div>  
                </td>
                <td align="center">		
                    <a class="fa azul pointer hoverazul" title="Última venda <?=$rowc['dmadtemissao']?>" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$rowc["idnf"]?>')">
                        <?=$rowc['ultimaemissao']?><?if(!empty($rowc['dmadtemissao'])){
                            echo " Dias";
                        }?>
                    </a>
                </td>
		
		

                <td align="center" title="<?=$arrvisita[$row["idpessoa"]][0]["inicio"]?>">
		    <?=$arrvisita[$row["idpessoa"]][0]["ultimoevento"]?>
                    <?if(!empty($arrvisita[$row["idpessoa"]][0]["inicio"])){echo "Dias";}?>
		</td>
		<td align="center">
		    <div class="oVisita">
		    <a class="fa fa-search azul pointer hoverazul" title=" Ver Eventos" data-target="webuiPopover0" ></a>
		    
		    <a target="_blank" href="?_modulo=menurelatorio&menupai=217&_menu=N&_menulateral=N&_novajanela=Y&_idrep=286&_fds=<?=$_REQUEST['dataSelecionada'];?>&idpessoa=<?=$_REQUEST['idrepresentante'];?>&idcliente=<?=$row['idpessoa'];?>&idempresa=<?=$_REQUEST['_idempresa'];?>&_idempresa=<?=$_REQUEST['_idempresa'];?>"
		            <i class="fa fa-bar-chart snippet" title="Atendimento: Comercial"></i>
            </a>
          
		    </div>
		    <div class="webui-popover-content">
			<table>
<?
			foreach ($arrvisita[$row["idpessoa"]] as $key => $value) {			     
?>			    
			    <tr>
				<td>Evento:</td>
				<td>
				    <a class="fa azul pointer hoverazul" title="Evento" onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?=$arrvisita[$row["idpessoa"]][$key]["idevento"]?>')">
					<?=$arrvisita[$row["idpessoa"]][$key]["idevento"]?>
				    </a>
				</td>
				<td>
                                        <?=$arrvisita[$row["idpessoa"]][$key]["eventotipo"];?><br>
                                        <?=$arrvisita[$row["idpessoa"]][$key]["inicio"];?>
                                </td>
			    </tr>	    
<?
			 }
		    ?>
			</table>
		    </div>
		</td>                
                <td>		
		    <button class="btn btn-xs btn-primary" onclick="novaTarefa()">Novo</button>
		</td>
            </tr>
<?
        }// while($row=mysql_fetch_assoc($res)){ 
?>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td title="Total Faturado" align="right"><b><?=number_format(tratanumero($total), 2, ',', '.'); ?></b></td>
                <td title="Total de Doses" align="center"><b><?=number_format(tratanumero($doses), 0, ',', '.'); ?></b></td>
                <th ></th>
                <th></th>               
                <th ></th>
                 <th></th>
            </tr>
            </tbody>
            </table>
            <br>
            <p>
<?
//print_r($arrpl);
//Array ( [5] => Array ( [idtipoplantel] => 5 [plantel] => Suínos [tipoplantel] => Leitões [qtd] => 148900 )
?>
   <div class="row">
            <div class="col-md-6" >
            <div class="panel panel-default" >
                <div class="panel-heading">Somatório</div>
                <div class="panel-body"> 
                    <table class="table table-striped planilha">
                        <tr>
                            <th>Espécie</th>
                            <th>Tipo</th>
                            <th  style="text-align: right !important;" >Quatidade</th>
                        </tr>
<?
foreach ($arrpl as &$value) {
    $value['plantel'];
?>
                        <tr>
                            <td>
                                <?=$value['plantel']?>
                            </td>
                            <td>
                                <?=$value['tipoplantel']?>
                            </td>
                            <td align="right">
                                 <?=number_format(tratanumero($value['qtd']), 0, ',', '.'); ?>
                            </td>
                        </tr>                        
<?
}
?>

                    </table>    
<?


  }else{//if($qtdrows>0){

    echo("Não foram encadas parcelas nestas condiçàµes.");
      
  }//if($qtdrows>0){
  ?>
        </div>
    </div>
    </div>
</div>
<?
}elseif(empty($idrepresentante) or empty($dataini) or empty($datafim)){
?>
<link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
	<br>
	<div class="row">
		<div class="col-md-12">
			<div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

			<strong><i class="glyphicon glyphicon-info-sign"></i> Para pesquisa favor preencher os campos.
			<br/>
                        <br/><li>Representante</li>
			<br/><li>Intervalo</li>
                        <br/>
			</div>
		</div>
	</div>
<?
}
?>
<script>
    
jRep=<?=$jRep?>;// autocomplete cliente

//mapear autocomplete de clientes
jRep = jQuery.map(jRep, function(o, id) {
    return {"label": o.nome, value:id+""}
});

//autocomplete 
$("[name*=idrepresentante]").autocomplete({
    source: jRep
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});

function mostraocultos(vthis){
    var statusver=$(vthis).attr('ver');
    if(statusver=='oculto'){
        $(".oculto").show();
        $(".visivel").hide();
        $(vthis).attr('ver',"visivel");
        $(vthis).removeClass('verde');
        $(vthis).addClass('vermelho');
    }else{
        $(".visivel").show();
        $(".oculto").hide();
        $(vthis).attr('ver',"oculto");
        $(vthis).removeClass('vermelho');
        $(vthis).addClass('verde');
    }
}
    
function alteracrm(vthis,idpessoa){    
    CB.post({
	 objetos: "_x_u_pessoa_idpessoa="+idpessoa+"&_x_u_pessoa_statuscrm="+$(vthis).val()
	 ,parcial: true
	 ,refresh: false
     });    
}
function alterastatus(vthis,idpessoa){
    var statuscrm=$(vthis).attr('statuscrm');
    
    CB.post({
	 objetos: "_x_u_pessoa_idpessoa="+idpessoa+"&_x_u_pessoa_statuscrm="+statuscrm
	 ,parcial: true
	 ,refresh: false
         ,msgSalvo: "Salvo"
        ,posPost: function(data, textStatus, jqXHR){
            if(statuscrm=="OCULTO"){
                $(vthis).attr('statuscrm',"VISITAR");
                $(vthis).attr('title',"Desocultar");
                $(vthis).removeClass('verde');
                $(vthis).addClass('vermelho');
                document.getElementById(idpessoa).style.display = "none";
                $("#"+idpessoa).removeClass('visivel');
                $("#"+idpessoa).addClass('oculto');
            }else{
                $(vthis).attr('statuscrm',"OCULTO");
                 $(vthis).attr('title',"Ocultar");
                $(vthis).removeClass('vermelho');
                $(vthis).addClass('verde');
                document.getElementById(idpessoa).style.display = "none";
                $("#"+idpessoa).removeClass('oculto');
                $("#"+idpessoa).addClass('visivel');
            }

        }
    });    
}
    
function pesquisar(vthis){

    $(vthis).toggleClass('blink');

    var emissao1 = $("[name=emissao1]").val();
    var emissao2 = $("[name=emissao2]").val();
    var cliente = $("[name=cliente]").val();
    var ordenacao = $("[name=ordenacao]").val();
    var statuscrm = $("[name=statuscrm]").val();
    var campoplantel = $("[name=campoplantel]").val();
    var idrepresentante = $('#idrepresentante').attr('cbvalue');
    var dataSelecionada=$("#dataSelecionada").html();
    
    var avescort = $("[avescort]").attr('avescort');;
    var avespost = $("[avespost]").attr('avespost');;
    var bovinos = $("[bovinos]").attr('bovinos');;
    var suinos = $("[suinos]").attr('suinos');;

    var str="emissao1="+emissao1+"&emissao2="+emissao2+"&cliente="+cliente+"&statuscrm="+statuscrm+"&idrepresentante="+idrepresentante+"&campoplantel="+campoplantel+"&dataSelecionada="+dataSelecionada+"&ordenacao="+ordenacao;
  
        CB.go(str);
}

function limpar(){
    var idremessa =$('#idremessa').val();
    CB.go("idremessa="+idremessa);
}

CB.preLoadUrl = function(){
	//Como o carregamento é via ajax, os popups ficavam aparecendo apà³s o load
	$(".webui-popover").remove();
}

$(".oVisita").webuiPopover({
	trigger: "hover"
	,placement: "right"
	,delay: {
        show: 300,
        hide: 0
    }
});

function altplantel(vthis,vcampo){  
    
    if($(vthis).attr(vcampo)=="N"){
        $(vthis).attr(vcampo,"Y");
        $(vthis).removeClass('fa-square-o');
        $(vthis).addClass('fa-check-square-o');
    }else{
        $(vthis).attr(vcampo,"N");
        $(vthis).removeClass('fa-check-square-o');
        $(vthis).addClass('fa-square-o');
    }
}

function novaTarefa() {

    CB.compartilharAlerta('carregaTiposAlerta', function callback(lastInsertId) {
        getEvento(lastInsertId);
    });

}

function getEvento(idEvento) {
    var token = Cookies.get('jwt') || localStorage.getItem("jwt") || "";
        
    fetch('ajax/evento.php?vopcao=getevento&videvento=' + idEvento, {
        headers: {
            "authorization": token
        }
    }).then(function(response) {
        return response.json();
    }).then(function(data) {
        if(data.error)
        {
            return alertAtencao(data.error);
        }

        if (data && data.length > 0) {
            criaEventos(data);
        }
    });
}
function criaEventos(eventos) {

    if (eventos && eventos.length > 0) {

        eventos.forEach(function(evento) {


            $("#totais").text(evento.totalResultados);

            let verify = $("#idevento_" + evento.idevento);

            if (!verify.length) {

                let apenasOcultos = $("#btn-3").hasClass('selecionado') ? true : false;
                let clone = $(cloneEvento).clone();
                let prazo = "";
                let color = "";
                let descricao = "";

                let corLinha = 'button-blue';


                if (evento.configprazo == 'N') {
                    dataTarefa = '<span class="dataTarefa">' + evento.prazo + '</span>';
                    $(clone).find('.eventoRow #boxdata').css(JSON.parse(evento.coricone));


                } else {
                    dataTarefa = '<span class="dataTarefa">' + evento.prazorestante + '</span>';
                    if (evento.prazorestante == '<i>venc.</i>') {
                        $(clone).find('.eventoRow .dataTarefa').css("background", "#ac202e");
                        $(clone).find('.eventoRow .dataTarefa').css("color", "#fff");
                        $(clone).find('.eventoRow .dataTarefa').css("box-shadow", "none");

                    }
                }


                if (evento.visualizado == 1) {
                    $(clone).find('.eventoRow').addClass("");
                } else {
                    $(clone).find('.eventoRow').addClass("naoVisualizado");
                }
                $(clone).find('.eventoRow [mostraprazo]').addClass(evento.mostraprazo);
                $(clone).find('.eventoRow [mostradata]').addClass(evento.mostradata);
                $(clone).find('.eventoRow').attr("modulo", evento.modulo);
                $(clone).find('.eventoRow').attr("idmodulo", evento.idmodulo);
                $(clone).find('.eventoRow').attr("mostradata", evento.mostradata);
                $(clone).find('.eventoRow').attr("travasala", evento.travasala);
                $(clone).find('.eventoRow').attr("diainteiro", evento.diainteiro);
                $(clone).find('.eventoRow').attr("duracaohms", evento.duracaohms);
                $(clone).find('.eventoRow').attr("idequipamento", evento.idequipamento);
                $(clone).find('.eventoRow').attr("iniciodata", evento.iniciodata);
                $(clone).find('.eventoRow').attr("inicio", evento.inicio);
                $(clone).find('.eventoRow').attr("iniciohms", evento.iniciohms);
                $(clone).find('.eventoRow').attr("prazo", evento.prazo);
                $(clone).find('.eventoRow').attr("configprazo", evento.configprazo);
                $(clone).find('.eventoRow').attr("posicao", evento.posicao);
                $(clone).find('.eventoRow').attr("id", "idevento_" + evento.id);
                $(clone).find('.eventoRow').attr("idfluxostatuspessoa", evento.idfluxostatuspessoa);
                $(clone).find('.eventoRow').attr("eventotipo", evento.eventotipo);
                $(clone).find('.eventoRow').attr("cor", evento.cor);
                $(clone).find('.eventoRow').attr("corstatus", evento.corstatus);
                $(clone).find('.eventoRow').attr("cortextostatus", evento.cortextostatus);
                $(clone).find('.eventoRow').attr("corstatusresp", evento.corstatusresp);
                $(clone).find('.eventoRow').attr("rotuloresp", evento.rotuloresp);
                $(clone).find('.eventoRow').attr("status", evento.status);
                $(clone).find('.eventoRow').attr("evento", evento.evento);
                $(clone).find('.eventoRow').attr("descricao", evento.descricao);
                $(clone).find('.eventoRow').attr("fluxo", evento.fluxo);
                $(clone).find('.eventoRow').attr("criadoempor", evento.criadoempor);
                $(clone).find('.eventoRow').attr("alteradoempor", evento.alteradoempor);
                $(clone).find('.eventoRow').attr("idevento", evento.idevento);
                $(clone).find('.eventoRow').attr("slaprazo", evento.slaprazo);
                $(clone).find('.eventoRow').attr("ideventos", evento.idevento);

                $(clone).find('.progress-wrap').attr("data-progresspercent", evento.slaprazo);
                $(clone).find('.progress-bar').css("width", evento.slaprazo);

                $(clone).find('.eventotipo').css("border", "1px solid " + evento.cor);
                $(clone).find('.eventotipo').css("color", evento.cor);
                $(clone).find('.ideventos').text(evento.idevento);
                $(clone).find('.ideventos').css("cursor", "pointer");
                // $(clone).find('.ideventos').attr("onclick", "javascript:modalEvento(this,'?_modulo=evento&_acao=u&idevento="+evento.idevento+"')");
                $(clone).find('.origem').append(evento.nomecurto);
                $(clone).find('.descricao').html(evento.evento);
                $(clone).find('.eventotipo').html(evento.eventotipo +
                    '<div style="text-align: center;font-size: 9px; padding: 0px 4px; color: #333; background-color: transparent; width: auto;" class="ideventos alert-warning">' +
                    evento.idevento + '</div>');



                //$(clone).find('.dataAlerta').empty();
                $(clone).find('.dataTarefa').append(dataTarefa);
                $(clone).find('.hoverlaranja').attr("onclick", "visualizarComentarios(" + evento.idevento +
                    "," + evento.idpessoa + ")");
                $(clone).find('.hoververde').attr("onclick", "visualizarResponsaveis(" + evento.idevento + "," +
                    evento.idpessoa + ")");

                // $(clone).find('.hrefs').attr("onclick", "javascript:modalEvento(this,'?_modulo=evento&_acao=u&idevento="+evento.idevento+"')");
                $(clone).find('.hrefs').css("background", evento.corstatus);
                $(clone).find('.hrefs').css("color", evento.cortextostatus);
                $(clone).find('.hrefs').append(evento.rotulo);

                if (evento.modulo != undefined && evento.modulo != '' && evento.modulo != 'evento') {

                    //$(clone).find('.hrefmodulo').attr("onclick", "javascript:janelamodal('"+evento.modulo+"')");
                    $(clone).find('.hrefmodulo').attr("onclick", "javascript:janelamodal('?_modulo=" + evento
                        .modulo + "&_acao=u&" + evento.chavemodulo + "=" + evento.idmodulo + "')");
                } else {

                    $(clone).find(".linkmodulo").hide();
                }

                if (apenasOcultos) {
                    $(clone).find('.hoververmelho').attr("onclick", "verificaDesocultar(" + evento.idevento +
                        ")");
                    $(clone).find('.hoververmelho').attr("title", "Desocultar tarefa");
                    $(clone).find('.hoververmelho').removeClass("fa-eye-slash");
                    $(clone).find('.hoververmelho').addClass("fa-eye");
                } else {
                    $(clone).find('.hoververmelho').attr("onclick", "verificaOcultar(" + evento.idevento + ")");
                }


                $(clone).find('.checker').attr("data-idevento", evento.idevento);
                $(clone).find('.vertarefa').attr("onclick",
                    "modalEvento(this,'?_modulo=evento&_acao=u&idevento=" + evento.idevento + "')");
                //$(clone).find('.hoverlaranja').attr("onClick", "popupCompartilharTarefa("+evento.idevento+")");
                //$(clone).attr("id", "idevento_"+evento.idevento);

                if (evento.visualizado == undefined || evento.visualizado == "" || evento.visualizado == "0" ||
                    evento.visualizado == 0) {

                  //  $(clone).css("background-color", "#c4ebf5");
                    $(clone).addClass("naoVisualizado")
                } else {

                    $(clone).css('background-color', corLinha);
                }

                $(clone).appendTo(".eventos");

                $("#exibidos").text($(".eventoRow").length);

                if ($("#exibidos").text() == $("#totais").text()) {
                    $("#exibir").text("");
                } else {
                    $("#exibir").text("Exibir mais");
                }
            } else {
                $ev = $("#idevento_" + evento.idevento);

                if (evento.configprazo == 'N') {
                    dataTarefa = '<span class="dataTarefa">' + evento.prazo + '</span>';
                    $ev.find('.eventoRow #boxdata').css(JSON.parse(evento.coricone));


                } else {
                    dataTarefa = '<span class="dataTarefa">' + evento.prazorestante + '</span>';
                    if (evento.prazorestante == '<i>venc.</i>') {
                    //    $ev.css("background", "#ac202e");
                    //    $ev.css("color", "#fff");
                    //    $ev.css("box-shadow", "none");

                    }
                }


                if (evento.visualizado == 1) {
                    $ev.addClass("");
                } else {
                    $ev.addClass("naoVisualizado");
                }

                if (evento.oculto == 1) {
                    $ev.hide();
                }

                $ev.find('.eventoRow [mostraprazo]').addClass(evento.mostraprazo);
                $ev.find('.eventoRow [mostradata]').addClass(evento.mostradata);
                $ev.attr("modulo", evento.modulo);
                $ev.attr("idmodulo", evento.idmodulo);
                $ev.attr("mostradata", evento.mostradata);
                $ev.attr("travasala", evento.travasala);
                $ev.attr("diainteiro", evento.diainteiro);
                $ev.attr("duracaohms", evento.duracaohms);
                $ev.attr("idequipamento", evento.idequipamento);
                $ev.attr("iniciodata", evento.iniciodata);
                $ev.attr("inicio", evento.inicio);
                $ev.attr("iniciohms", evento.iniciohms);
                $ev.attr("prazo", evento.prazo);
                $ev.attr("configprazo", evento.configprazo);
                $ev.attr("posicao", evento.posicao);
                $ev.attr("id", "idevento_" + evento.id);
                $ev.attr("idfluxostatuspessoa", evento.idfluxostatuspessoa);
                $ev.attr("eventotipo", evento.eventotipo);
                $ev.attr("cor", evento.cor);
                $ev.attr("corstatus", evento.corstatus);
                $ev.attr("cortextostatus", evento.cortextostatus);
                $ev.attr("corstatusresp", evento.corstatusresp);
                $ev.attr("rotuloresp", evento.rotuloresp);
                $ev.attr("status", evento.status);
                $ev.attr("evento", evento.evento);
                $ev.attr("descricao", evento.descricao);
                $ev.attr("fluxo", evento.fluxo);
                $ev.attr("criadoempor", evento.criadoempor);
                $ev.attr("alteradoempor", evento.alteradoempor);
                $ev.attr("idevento", evento.idevento);
                $ev.attr("slaprazo", evento.slaprazo);
                $ev.attr("ideventos", evento.idevento);
                $ev.find('.progress-wrap').attr("data-progresspercent", evento.slaprazo);
                $ev.find('.progress-bar').css("width", evento.slaprazo);
                $ev.find('.eventotipo').css("border", "1px solid " + evento.cor);
                $ev.find('.eventotipo').css("color", evento.cor);
                $ev.find('.ideventos').text(evento.idevento);
                $ev.find('.ideventos').css("cursor", "pointer");
                $ev.find('.origem').html(evento.nomecurto);
                $ev.find('.descricao').html(evento.evento);
                $ev.find('.eventotipo').html(evento.eventotipo +
                    '<div style="text-align: center;font-size: 9px; padding: 0px 4px; color: #333; background-color: transparent; width: auto;" class="ideventos alert-warning">' +
                    evento.idevento + '</div>');
                $ev.find('.dataTarefa').html(dataTarefa);
                $ev.find('.hoverlaranja').attr("onclick", "visualizarComentarios(" + evento.idevento + "," +
                    evento.idpessoa + ")");
                $ev.find('.hoververde').attr("onclick", "visualizarResponsaveis(" + evento.idevento + "," +
                    evento.idpessoa + ")");
                $ev.find('.hrefs').css("background", evento.corstatus);
                $ev.find('.hrefs').css("color", evento.cortextostatus);
                $ev.find('.hrefs').html(evento.rotulo);

                if (evento.modulo != undefined && evento.modulo != '') {

                    //$ev.find('.hrefmodulo').attr("onclick", "javascript:janelamodal('"+evento.modulo+"')");
                    $ev.find('.hrefmodulo').attr("onclick", "javascript:janelamodal('?_modulo=" + evento
                        .modulo + "&_acao=u&" + evento.chavemodulo + "=" + evento.idmodulo + "')");
                } else {

                    $ev.find(".linkmodulo").hide();
                }


                $ev.find('.checker').attr("data-idevento", evento.idevento);
                $ev.find('.vertarefa').attr("onclick", "modalEvento(this,'?_modulo=evento&_acao=u&idevento=" +
                    evento.idevento + "')");
  
                if (evento.visualizado == undefined || evento.visualizado == "" || evento.visualizado == "0" ||
                    evento.visualizado == 0) {

                    //$ev.css("background-color", "#c4ebf5");
                    $ev.addClass("naoVisualizado")
                } else {

                    // $ev.css('background-color', corLinha);
                }

                $ev.appendTo(".eventos");

                $("#exibidos").text($(".eventoRow").length);

                if ($("#exibidos").text() == $("#totais").text()) {
                    $("#exibir").text("");
                } else {
                    $("#exibir").text("Exibir mais");
                }
            }
        });
    }


    $('.atalhoPart').click(function(e) {
        //abrirAtalho($(this).parent(), 'participantes');
		 modalEvento($(this).parent(), '?_modulo=evento&_acao=u&idevento=' + $(this).parent().attr("idevento"));
    });

    $('.atalhoHist').click(function(e) {
        //abrirAtalho($(this).parent(), 'conteudo');
		 modalEvento($(this).parent(), '?_modulo=evento&_acao=u&idevento=' + $(this).parent().attr("idevento"));
    });

    $('.atalhoEvento').click(function(e) {
        modalEvento($(this).parent(), '?_modulo=evento&_acao=u&idevento=' + $(this).parent().attr("idevento"));
        $('#example').removeClass('is-visible');
    });
}
$(document).ready(function(){
    $(".cancelBtn").click(function()
    {
      $("#dataSelecionada").html("<span class='cinza'>Selecione a data</span>");
    });
});
//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
</script>