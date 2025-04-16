<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("controllers/fluxo_controller.php");
if($_POST){
    include_once("../inc/php/cbpost.php");
}


$dataevento_1 	= $_GET["dataevento_1"];
$dataevento_2 	= $_GET["dataevento_2"];
$idrhtipoevento = $_GET["idrhtipoevento"];
$idempresa      = $_GET["idempresa"];
$idpessoa       = $_GET["idpessoa"];

function getValor(){

    $sql= "select idrhtipoevento,valor from rhtipoevento where status='ATIVO' ".getidempresa('idempresa','rhtipoevento')." ";

    $res = d::b()->query($sql) or die("getValor: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idrhtipoevento"]]=$r["valor"];
    }
	return $arrret;
}

$arrVal=getValor();
//print_r($arrCli); die;
$jVal=$JSON->encode($arrVal);


    if (!empty($dataevento_1) and  !empty($dataevento_2) and !empty($idrhtipoevento)){
        $data1 = validadate($dataevento_1);
        $data2 = validadate($dataevento_2);

        if ($data1 and $data2){
            $strin .= " and (dataevento  BETWEEN '" . $data1 ."' and '" .$data2 ."')";
        }else{
            die ("Datas n&atilde;o V&aacute;lidas!");
        }
        $sql="select idrhevento,idpessoa,dataevento,situacao,status,valor,entsaida
                    from rhevento 
                where (dataevento  BETWEEN '" . $data1 ."' and '" .$data2 ."') 
                    and status!='INATIVO' -- ".getidempresa('idempresa','rhevento')."
                and idrhtipoevento = ".$idrhtipoevento." order by dataevento";
        $res=d::b()->query($sql) or die("erro ao buscar eventos sql=".$sql);
        $arrayp=array();
        while($row=mysqli_fetch_assoc($res)){
            // $arrayp[$r['idpessoa']]['semana'][$rw['diabusca']]=$r['semana'];  
            $arrayp[$row['idpessoa']][$row['dataevento']][$row['idrhevento']]['dataevento']=$row['dataevento']; 
            $arrayp[$row['idpessoa']][$row['dataevento']][$row['idrhevento']]['situacao']=$row['situacao'];  
            $arrayp[$row['idpessoa']][$row['dataevento']][$row['idrhevento']]['status']=$row['status'];  
            $arrayp[$row['idpessoa']][$row['dataevento']][$row['idrhevento']]['valor']=$row['valor'];
            $arrayp[$row['idpessoa']][$row['dataevento']][$row['idrhevento']]['entsaida']=$row['entsaida'];
        }  
        
        $arrdia=array();
        $arrqtdevdia = array();
        for ($i=0;;$i++) {
           
            $s="SELECT DATE_ADD('".$data1."', INTERVAL ".$i." DAY) as diabusca,
                DATE_FORMAT( DATE_ADD('".$data1."', INTERVAL ".$i." DAY),'%W') as semana,
                 case  when DATE_ADD('".$data1."', INTERVAL ".$i." DAY) > '".$data2."' then 'Y' 
                 else 'N' end  as maior";
            $re= d::b()->query($s) or die("erro ao buscar os pontos pendentes sql=".$s);
            $rw=mysqli_fetch_assoc($re);

             if ($rw['maior'] =='Y') {
                 break;
             }else{

               $arrdia[$rw['diabusca']] =$rw['semana'];  
               $arrqtdevdia[$rw['diabusca']]['qtd']=0;
               $arrqtdevdia[$rw['diabusca']]['semana'] =$rw['semana'];  

             }
        } 
        
        
    }//if (!empty($vencimento_1) or !empty($vencimento_2)){
//print_r($arrdia);


//maf160420: idevento 305682: Lançar eventos EXTRA, sem a pessoa e sem incidir na folha de pagamento, somente para lançar custos conforme a necessidade
function eventosSemPessoa($inTipo, $inRot){
	global $arrdia;
	global $dataevento_1;
	global $dataevento_2;
	global $idrhtipoevento;
	
	$data1 = validadate($dataevento_1);
	$data2 = validadate($dataevento_2);
	
	if (empty($dataevento_1) or  empty($dataevento_2) or empty($idrhtipoevento)){
        return false;
    }

	$sqlter="select idrhevento,idpessoa,dataevento,situacao,status,valor
                from rhevento 
            where (dataevento  BETWEEN '" . $data1 ."' and '" .$data2 ."') and tipo = '".$inTipo."'
                and status!='INATIVO' ".getidempresa('idempresa','rhevento')."
            and idrhtipoevento = ".$idrhtipoevento." order by dataevento";

	$rester=d::b()->query($sqlter) or die("#2 erro ao buscar eventos sql=".$sqlter);
    $arrayp=array();
    while($row=mysqli_fetch_assoc($rester)){
		$arraypter['terceiro'][$row['dataevento']][$row['idrhevento']]['dataevento']=$row['dataevento']; 
        $arraypter['terceiro'][$row['dataevento']][$row['idrhevento']]['situacao']=$row['situacao'];  
        $arraypter['terceiro'][$row['dataevento']][$row['idrhevento']]['status']=$row['status'];  
        $arraypter['terceiro'][$row['dataevento']][$row['idrhevento']]['valor']=$row['valor'];
    }  
	
	$arrdiater=array();
	$arrqtdevdiater = array();
	for ($i=0;;$i++) {
	   
		$s="SELECT DATE_ADD('".$data1."', INTERVAL ".$i." DAY) as diabusca,
			DATE_FORMAT( DATE_ADD('".$data1."', INTERVAL ".$i." DAY),'%W') as semana,
			 case  when DATE_ADD('".$data1."', INTERVAL ".$i." DAY) > '".$data2."' then 'Y' 
			 else 'N' end  as maior";
		$re= d::b()->query($s) or die("erro ao buscar os pontos pendentes sql=".$s);
		$rw=mysqli_fetch_assoc($re);

		if ($rw['maior'] =='Y') {
			break;
		}else{
			$arrdiater[$rw['diabusca']] =$rw['semana'];  
			$arrqtdevdiater[$rw['diabusca']]['qtd']=0;
			$arrqtdevdiater[$rw['diabusca']]['semana'] =$rw['semana'];  
		}
	}
	?>
	<tr class="espacador"><td></td></tr>
		<tr class="separador">
			<td colspan="999999" class="vermelhoescuro">EXTRA: <b><?=$inRot?></b>*</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
            <td></td>
			<?            
			$ev=0;
			foreach ($arrdia as $dataev => $arrdias){
				$ev=$ev+1;
				?>         	
				<td class="tv">    
					<? foreach ($arraypter['terceiro'][$dataev] as $idrhevento => $arrarhev) {
						$arrqtdevdiatertdevdia[$dataev]['qtd']=$arrqtdevdiater[$dataev]['qtd']+1;
						$qtdevdia=$qtdevdia+1;
						// print_r($arrarhev);
						$y=$y+1;
						$inids=$inids.$vir.$idrhevento;
						$vir=',';
						if($arrarhev['status']=='PENDENTE'){
							// $cor="#c2f5c1";
							 $cbt="label-primary";
						}else{
						   // $cor="#dfdfe8"; 
							$cbt="label-success ";
						} ?>
						
						<span class="label <?=$cbt?> fonte10" title="<?=dma($dataev)?>">
							<input type="text" name="" idrhevento="<?=$idrhevento?>" value="<?=$arrarhev['valor']?>" onchange="rheventovalor(this,<?=$idrhevento?>)" class="reset screen" style="width: 40px !important; background-color: white;" >
							<a class="fa fa-bars pointer branco hoverazul fa-1x" title="Evento" onclick="janelamodal('?_modulo=rhevento&_acao=u&idrhevento=<?=$idrhevento?>')"></a>	
							<a class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="inativarev(<?=$idrhevento?>)" title="Inativar"></a>						
						</span>     
					<? } ?>
					<a class="fa fa-plus-circle pointer verde hoververmelho fa-1x fade" onclick="geraeventoSemPessoa('<?=dma($dataev)?>',<?=$ev?>, '<?=$inTipo?>')" title="Gerar em <?=dma($dataev,true)?>"></a>
				</td>      
			<? } ?>
		</tr>
	</tr>
<? } ?>

<style>

#planlanc > tbody > tr:not(.separador):not(.espacador){
    height: 28px;
}

#planlanc > tbody > tr > td{
    white-space: nowrap;
    padding: 0px 2px;
}
#planlanc > tbody > tr > td.tv{
    text-align: right;
}
#planlanc > tbody > tr > td > a{
    padding:0px 4px;
}

tr#linha:hover {
    background:#DCDCDC; 
    color: black;
}
    
</style>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Dados para Criação do(s) Evento(s)</div>
        <div class="panel-body" >   
        <table>
            <tr>
                <td align="right">Data:</td>
                <td>
                    <input name="rhevento_dataevento" id="dataevento" class="calendario" type="text" style="width: 100px;" value="<?=$dataevento_1?>" vnulo autocomplete="off" >
                Entre
                    <input name="dataevento2" id="dataevento2" class="calendario" type="text" style="width: 100px;" value="<?=$dataevento_2?>" vnulo autocomplete="off" >
                </td>
            </tr>
            <tr>
                <td align="right">Evento:</td>
                <td>
                    <select name="_1_i_rhevento_idrhtipoevento" style="width: 250px;" id="idrhtipoevento"  vnulo>
                        <option value=""></option>
                        <?
                        fillselect("select idrhtipoevento,evento from rhtipoevento 
                                where flgmanual ='Y' 
                                and flgferias !='Y'
                                and formato in('D','H') ".getidempresa('idempresa','rhtipoevento')."
                                and status='ATIVO' order by evento asc",$idrhtipoevento);
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Empresa:</td>
                <td>
                    <select name="idempresa" id="idempresa"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
                        <option value=""></option>
                         <?
                       $sqlm = "SELECT idempresa, nomefantasia FROM empresa 
                                WHERE status = 'ATIVO'
                                ORDER BY nomefantasia ASC";
                        $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 2 sql:".$sqlm);
                        $arrempresa = explode(',',$idempresa); 
                        while ($rowm = mysqli_fetch_assoc($resm)) 
                        {
                            if (in_array($rowm['idempresa'], $arrempresa)){
                                    $selected= 'selected';
                            }else{
                                    $selected= '';
                            }

                            echo '<option data-tokens="'.retira_acentos($rowm['nomefantasia']).'" value="'.$rowm['idempresa'].'" '.$selected.' >'.$rowm['nomefantasia'].'</option>'; 
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="right">Funcionário:</td>
                <td colspan="10">      
                    <select name="idpessoa"  id="picker"  class="selectpicker valoresselect"  data-actions-box="true" multiple="multiple" data-live-search="true">
                        <? 
                       $sqlm = "SELECT p.idpessoa, p.nomecurto FROM pessoa p WHERE p.idtipopessoa = 1 AND p.status in('ATIVO','AFASTADO')
                              GROUP BY p.idpessoa 
                              ORDER BY p.nomecurto;";                    
                        $resm =  d::b()->query($sqlm)  or die("Erro buscar funcionarios 2 sql:".$sqlm);
                        $arrvalor = explode(',',$idpessoa); 
                        while ($rowm = mysqli_fetch_assoc($resm)) 
                        {
                            if (in_array($rowm['idpessoa'], $arrvalor)){
                                    $selected= 'selected';
                            }else{
                                    $selected= '';
                            }

                            echo '<option data-tokens="'.retira_acentos($rowm['nomecurto']).'" value="'.$rowm['idpessoa'].'" '.$selected.' >'.$rowm['nomecurto'].'</option>'; 
                        }		
                        ?>
                    </select> 
		        </td>
            </tr>
            <tr><td colspan="999"></td></tr>
            <tr>
                <td></td>
                <td>
                    <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
                        <span class="fa fa-search"></span>
                    </button> 
                </td>
            </tr>
        </table>
    </div>
    </div>
    </div>
</div>
<?
if (!empty($dataevento_1) and !empty($dataevento_2) and !empty($idrhtipoevento)){
?>
<div class="row">
    <div class="col-md-12" >
        <table id="planlanc" class="table planilha grade">
<?
    $sx="SELECT distinct(contrato) as contrato
        from pessoa 
                where status in('ATIVO','AFASTADO') and idtipopessoa = 1 and contrato is not null ";
    $rex=d::b()->query($sx) or die("Erro ao buscar tipos de contrado dos funcionarios : " . mysqli_error(d::b()) . "<p>SQL:".$sx);

    $y=50;
    $lcont=0;
    while($rx=mysqli_fetch_assoc($rex)){
        $lcont=$lcont+1;


        if($lcont>1){
?>
            <tr class="espacador">
                <td style="border: 0px;"></td>
            </tr>
<?
        }
?>
           <tr class="separador">
               <td>Regime: <b><?=$rx['contrato']?></b></td>
               <td></td>
               <td></td>
               <?
        $ev=0;
        if($lcont==1){
            foreach ($arrdia as $dataev => $arrdias) {
                $ev=$ev+1;
               ?>
               <td align="center" style="padding-top: 5px;padding-bottom: 5px;">
                  <b><?=dma($dataev)?></b><br>
                  <span class="label label-success  fonte10" title="" data-original-title="<?=$arrarhev['status']?>">
                        <input type="text" id="valor<?=$ev?>" name="valor<?=$ev?>" value="<?=$arrVal[$idrhtipoevento]?>"  class="reset screen valor" style="width: 40px !important; background-color: white;" >
                      <!--  <input type="hidden" name="_ev<?=$y?>_u_rhevento_idrhevento" value="<?=$idrhevento?>">	-->
                        <a class="fa fa-plus-circle pointer branco hoververmelho fa-1x" onclick="geraevento('<?=dma($dataev)?>',<?=$ev?>)" title="Gerar"></a>
                        <!--
                        <a class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="inativarevt(348259)" title="Inativar"></a>
                        -->
                    </span>
               </td>
               <?
            }//foreach ($arrdia as $dataev => $arrdias) {
        }else{
            foreach ($arrdia as $dataev => $arrdias) {
?>
                <td></td>
<?
            }
        }// if($lcont==1){
?>
               <td></td>
            </tr>   
            
        <?  
        $sqlAnd = '';  
        if(!empty($idpessoa) && $idpessoa != 'null')   {
            $sqlAnd .= " AND idpessoa IN (".$idpessoa.")";
        }   
        if(!empty($idempresa))   {
            $sqlAnd .= " AND idempresa IN (".$idempresa.")";
        }else{
            $sqlAnd .=' '.getidempresa('idempresa','pessoa');
        }                   
        $sqlm="SELECT idpessoa, nomecurto, salario, (salario*0.3) AS vale 
                 FROM pessoa WHERE idtipopessoa = 1  
                  AND contrato = '".$rx['contrato']."' 
                  AND status in ('ATIVO','AFASTADO')
                  $sqlAnd
             ORDER BY nomecurto";
            echo "<!--$sqlm-->";
        $resm =  d::b()->query($sqlm)  or die("Erro configura _mtotabcol campo 	Prompt Drop sql:".$sqlm);
        $l=0;

        while ($rowm = mysqli_fetch_assoc($resm)) {
            $l=$l+1;
            ?>           
            <tr id="linha">
                <td>
                    <input class="size7 valor" placeholder="Valor" type="hidden" value="<?=$rowm['idpessoa']?>" name="funcionario_<?=$rowm['idpessoa']?>">
                    <a title="Editar Funcionario" class="pointer" onclick="javascript:janelamodal('./?_modulo=funcionario&_acao=u&idpessoa=<?=$rowm['idpessoa']?>')">
                        <?=$rowm['nomecurto']?>
                    </a>
                </td>
                <td>
                    <span class="label label-success  fonte10" title="" data-original-title="<?=$arrarhev['status']?>">
                        <input type="text" id="valor<?=$rowm['idpessoa']?>" name="valor<?=$rowm['idpessoa']?>" value="<?=$arrVal[$idrhtipoevento]?>"  class="reset screen valor" style="width: 40px !important; background-color: white;" >
                        <a class="fa fa-plus-circle pointer branco hoververmelho fa-1x" onclick="geraEventoInicioFim('<?=validadate($dataevento_1)?>',<?=$rowm['idpessoa']?>)" title="Gerar"></a>
                    </span>
                </td>
                <td align="right">
                    <?if($idrhtipoevento==18){?>
                        <span title="Salário"><?=$rowm['salario']?></span>
                    <?}?>
                    <?if($idrhtipoevento==22){?>
                        <span title="Vale"><?=$rowm['vale']?></span>
                    <?}?>
                </td>

            <?
            $inids='';
            $vir='';
            reset($arrdia);   
            $ev=0;
            foreach ($arrdia as $dataev => $arrdias){
                $ev=$ev+1;
                ?>              

                <td class="tv">
<? 
                foreach ($arrayp[$rowm['idpessoa']][$dataev] as $idrhevento => $arrarhev) {
                    $arrqtdevdia[$dataev]['qtd']=$arrqtdevdia[$dataev]['qtd']+1;
                    $qtdevdia=$qtdevdia+1;
                    // print_r($arrarhev);
                    $y=$y+1;
                    $inids=$inids.$vir.$idrhevento;
                    $vir=',';
                    if($arrarhev['status']=='PENDENTE'){
                        // $cor="#c2f5c1";
                         $cbt="label-primary";
                    }else{
                       // $cor="#dfdfe8"; 
                        $cbt="label-success ";
                    }
                    ?>       
                    <span class="label <?=$cbt?> fonte10"  data-toggle="tooltip" title="<?=dma($dataev)?>">                        
                        <input type="text" name="" idrhevento="<?=$idrhevento?>" value="<?=$arrarhev['valor']?>" onchange="rheventovalor(this,<?=$idrhevento?>)" class="reset screen" style="width: 40px !important; background-color: white;" >
                      <!--  <input type="hidden" name="_ev<?=$y?>_u_rhevento_idrhevento" value="<?=$idrhevento?>">	-->
                        <a class="fa fa-bars pointer branco hoverazul fa-1x" title="Evento" onclick="janelamodal('?_modulo=rhevento&_acao=u&idrhevento=<?=$idrhevento?>')"></a>
                        <a class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="inativarev(<?=$idrhevento?>)" title="Inativar"></a>
                    </span>  <br/>     
                  
<?             
                }// foreach ($arrayp[$rowm['idpessoa']] as $idrhevento => $arrarhev) {
?>  
                   
                       <a class="fa fa-plus-circle pointer verde hoververmelho fa-1x fade" onclick="geraeventoind('<?=dma($dataev)?>',<?=$ev?>,<?=$rowm['idpessoa']?>)" title="Gerar em <?=dma($dataev,true)?> para:&#13;&#10;<?=$rowm['nomecurto']?>"></a>
                    
                </td>
<?                    
            }// foreach ($arrdia as $dataev => $arrdias){
            reset($arrdia);
?>                

                <td class="center">
                    <?if(!empty($inids)){?>
                    <a class="fa fa-2x fa-minus-circle pointer cinza hoververmelho " onclick="inativartodos('<?=$inids?>')" title="Inativar Todos"></a>
                    <?}?>
                </td>
            </tr>
  <?    
        }//while ($rowm = mysqli_fetch_assoc($resm)) {
    }//while($rx=mysqli_fetch_assoc($rex)){

//maf160420: Foi criado para gerar eventos impessoais, somente para controle financeiro
eventosSemPessoa("ALMOCO_TERCEIRO", "Almoço Terceiros");

//maf160420: Segue abaixo exemplo para criação de mais 1 seção com tipo diferente para controle:
//eventosSemPessoa("TESTE_DE_TIPO", "Teste de Tipo para controle");

?>
            <tr class="espacador"><td></td></tr>
            <tr>
                <td colspan="2">Eventos Total:</td>
                <td></td>
<?            
    foreach ($arrdia as $dataev => $arrdias){
  ?>         
                <td align="center">    
                    <?=$arrqtdevdia[$dataev]['semana']?> - 
                    <?=$arrqtdevdia[$dataev]['qtd']?>
                </td>
<?
    }
?>

        </table>    
        <div class="vermelhoescuro fonte07">* Somente para controle. Não incidirá em folha de pagamento.</div>
    </div>
</div>
<?
}
?>
<script>
jVal=<?=$jVal?>;// valores
    
$("#idrhtipoevento").change(function() {
  var idrhtev=$("#idrhtipoevento").val();
     $(".valor").val(jVal[idrhtev]); 
});

 function pesquisar(vthis){
     $(vthis).html('<span class="fa fa-spinner fa-pulse"></span>')
    var dataevento_1 = $("[name=rhevento_dataevento]").val();
    var dataevento_2 = $("[name=dataevento2]").val();
    var idrhtipoevento = $("[name=_1_i_rhevento_idrhtipoevento]").val();
    var idempresa = $("[name=idempresa]").val();
    var idpessoa = $("[name=idpessoa]").val();
  
    var str="dataevento_1="+dataevento_1+"&dataevento_2="+dataevento_2+"&idrhtipoevento="+idrhtipoevento+"&idempresa="+idempresa+"&idpessoa="+idpessoa
    CB.go(str);
} 

function inativartodos(inidrhevento){
 debugger;
  var res = inidrhevento.toString().split(",");

  var arrayLength = res.length;
  var str;
    for (var i = 0; i < arrayLength; i++) {
        console.log(res[i]);
          str=str+"&_x"+i+"_u_rhevento_idrhevento="+res[i]+"&_x"+i+"_u_rhevento_status=INATIVO"
    }
    console.log(str);
    
    CB.post({
	    objetos:  str
	    ,parcial:true
	});   
}
function inativarev(idrhevento){    		
	CB.post({
	    objetos:  "_x_u_rhevento_idrhevento="+idrhevento+"&_x_u_rhevento_status=INATIVO"
	    ,parcial:true
	});    
}
function rheventovalor(vthis,idrhevento){
    CB.post({
	    objetos:  "_x_u_rhevento_idrhevento="+idrhevento+"&_x_u_rhevento_valor="+$(vthis).val()
	    ,parcial:true
	}); 
}


function geraevento(dataev,inev)
{   
    var idempresa = (getUrlParameter("idempresa")) ? "&idempresa="+getUrlParameter("idempresa") : '';
    var idpessoa = (getUrlParameter("idpessoa") && getUrlParameter("idpessoa") != 'null') ? "&idpessoamulti="+getUrlParameter("idpessoa") : '';
    CB.post({
	    objetos:  "_1_i_rhevento_dataevento="+dataev+"&_1_i_rhevento_valor="+$('#valor'+inev).val()+idempresa+idpessoa   
	}); 
}

function geraEventoInicioFim(dataevinicio,idpessoa)
{
    CB.post({
	    objetos: "_1_i_rhevento_dataevento="+dataevinicio+"&_1_i_rhevento_valor="+$('#valor'+idpessoa).val()+"&datafim=<?=$dataevento_2?>&_1_i_rhevento_idpessoa="+idpessoa
	}); 
}

function geraeventoind(dataev,inev,idpessoa)
{
    <? $idfluxostatus = FluxoController::getIdFluxoStatus('rhevento', 'PENDENTE'); ?>
	CB.post({
		objetos: `_1_i_rhevento_idrhtipoevento=${$('#idrhtipoevento').val()}&_1_i_rhevento_dataevento=${dataev}&_1_i_rhevento_valor=${$('#valor'+inev).val()}&_1_i_rhevento_idpessoa=${idpessoa}&_1_i_rhevento_idfluxostatus=${<?=$idfluxostatus?>}`		
		,parcial:true
	});
}

function geraeventoSemPessoa(dataev,inev,tipo)
{
    CB.post({
        objetos:  {
            "_1_i_rhevento_dataevento": dataev,
            "_1_i_rhevento_valor": $('#valor'+inev).val(),
            "_1_i_rhevento_tipo": tipo,
            "_1_i_rhevento_idrhtipoevento": $('#idrhtipoevento').val(),
        }
        ,parcial:true       
    });
}

$('.selectpicker').selectpicker('render');
function altcheck(vthis){
    if($(vthis).is(":checked")){
        $('#selctfunc option').attr("selected","selected");
        $('#selctfunc').selectpicker('refresh');
    }else{
        $('#selctfunc option').attr("selected",false);
        $('#selctfunc').selectpicker('refresh');
    }
}

/*
document.getElementById("cbSalvar").setAttribute( "onClick", "geraevento();" );


function geraevento(){
    $('#selctfunc').val();
    $('#idrhtipoevento').val();
    $('#dataevento').val();
    $('#valor').val();
    
    CB.post({
        "objetos":"_1_i_rhevento_idrhtipoevento="+$('#idrhtipoevento').val()+"&_1_i_rhevento_dataevento="+$('#dataevento').val()+"&rhevento_idpessoa="+$('#selctfunc').val()+"&_1_i_rhevento_valor="+$('#valor').val()
        ,parcial:true
    });
}

*/
</script>
