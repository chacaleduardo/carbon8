<?require_once("../inc/php/validaacesso.php");
$idrhtipoevento=$_GET['idrhtipoevento'];
$idpessoa=$_GET['idpessoa'];
$idrhfolha=$_GET['idrhfolha'];

$dataevento2=$_GET['dataevento2']; 

$statusfolha= traduzid('rhfolha', 'idrhfolha', 'status', $idrhfolha);
$_tipofolha= traduzid('rhfolha', 'idrhfolha', 'tipofolha', $idrhfolha);


if($_tipofolha=='DECIMO TERCEIRO 2' and !empty($dataevento2)){

    $sl="select * from rhfolha f 
    where f.idrhfolha !=".$idrhfolha." ".getidempresa('idempresa','rhfolha')." 
    and datafim < '".$dataevento2."' and tipofolha='DECIMO TERCEIRO' and status!='FECHADA' order by datafim desc limit 1";
    $resl=d::b()->query($sl);
    //die($sl);
    $qtdl=mysqli_num_rows($resl);
    if($qtdl>0){
        $rl=mysqli_fetch_assoc($resl);
        $strdatafim="  and e.dataevento > '".$rl['datafim']."'";

    }else{
        $strdatafim='';
    }
  
}




if($_POST) require_once("../inc/php/cbpost.php");
if(!empty($dataevento2)){
    $str=" and e.dataevento <='".$dataevento2."' ";
    $tit=" até ".dma($dataevento2)."";
}

if($statusfolha=='FECHADA'){
    $str.=" and e.idrhfolha=".$idrhfolha;
}else{
     $str.=" and e.situacao='A'
            and e.status='PENDENTE' ";
}
?>
<script>
<?if($statusfolha=='FECHADA'){?>

$("#cbModuloForm").find('input').prop( "disabled", true );
$("#cbModuloForm").find("select" ).prop( "disabled", true );
$("#cbModuloForm").find("textarea").prop( "disabled", true );
 
<?}?>
    
 </script> 

 <link href="../inc/css/rep.css" media="all" rel="stylesheet" type="text/css">
<?

	$sql = "select e.idrhevento,t.evento,e.dataevento,e.hora,e.valor,e.parcelas,e.parcela,e.status,e.situacao,t.formato,t.tipo,e.idobjetoori,e.tipoobjetoori
                from rhevento e left join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
            where e.idrhtipoevento = ".$idrhtipoevento."
            and e.valor!=0
            -- ".getidempresa('e.idempresa','rhevento')."
            and e.idpessoa = ".$idpessoa."
            ".$strdatafim."
            ".$str." 
           
            order by e.dataevento,e.hora";
//DIE($sql);
	$res = d::b()->query($sql) or die("Erro ao buscar eventos: ".mysqli_error(d::b()));
        
        $evento= traduzid('rhtipoevento', 'idrhtipoevento', 'evento', $idrhtipoevento); 
        $nomecurto= traduzid('pessoa', 'idpessoa', 'nomecurto', $idpessoa);
        
        $sql1 = "select e.idrhevento,t.evento,e.dataevento,e.hora,e.valor,e.parcelas,e.parcela,e.status,e.situacao,t.formato,t.tipo,e.idobjetoori,e.tipoobjetoori
                from rhevento e left join rhtipoevento t on(t.idrhtipoevento=e.idrhtipoevento)
            where e.idrhtipoevento = ".$idrhtipoevento."
            and e.idpessoa = ".$idpessoa."            
            and e.situacao='P'
            and e.valor!=0
            and e.status='PENDENTE'
           -- ".getidempresa('e.idempresa','rhevento')."
            order by e.dataevento,e.hora";

	$res1 = d::b()->query($sql1) or die("Erro ao buscar eventos: ".mysqli_error(d::b()));
?>

<div class="row">
<div class="col-md-6">
<div class="panel panel-default">
    <div class="panel-heading"><?echo("<!--".$sql." -->")?> </div>
    <div class="panel-body">
        <table class="normal">
        <thead>
        <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase; height:20px;">
            <td colspan="8" style="font-size:11px;"  align="center"><?=$nomecurto?> - <?=$evento?> -  <?=$tit?> - <span class="alert-warning bold">APROVADO</span></td>
        </tr>
       <tr class="header">
                <td class="tdtit grrot">Data</td>
                <td class="tdtit grrot">Hora</td>
                <td class="tdtit grrot">Parcela</td>
                <td class="tdtit grrot">Parcelas</td>                
                <td class="tdtit grrot">Status</td>   
                <td class="tdtit grrot">Valor</td>
                <?if($statusfolha!='FECHADA'){?>
                <td class="tdtit grrot">
                    <a class="hoverazul pointer" onclick="transfevtodos(this)" title="Transferir todos">
                        <b> Transferir Todos</b>           
                    </a>                  
                </td>
               
               <?}?>
        </tr>
        </thead>
        <tbody id="eventospendentes">
            <?
            $val=0;
            while($row=mysqli_fetch_assoc($res)){
                $val=$val+$row['valor'];
            ?>
            <tr id="eventopendente" class="res" idrhevento="<?=$row['idrhevento']?>">
                <td ><?=dma($row['dataevento'])?></td>
                <td ><?=$row['hora']?></td>
                <td ><?=$row['parcela']?></td>
                <td ><?=$row['parcelas']?></td>                
                <td ><?=$row['status']?></td>
                <td >
                <?
                if($row['formato']=='H'){
                    if($row['valor']<0){echo "-" ;}
                    echo(convertHoras(abs($row['valor'])));
                }else{
                ?>
                <input name="" type="text" class="size5"	value="<?=$row['valor']?>" onchange="atualizavalor(<?=$row['idrhevento']?>,this)">
                <?
                } 
                ?>
                </td>
                <?if($statusfolha!='FECHADA'){?>
                <td class="tdtit grrot">
                    
                    <div class="btn-group" role="group" aria-label="Basic example">                
                        <button title="Transferir" type="button" class="btn btn-success" onclick="transfev(<?=$row['idrhevento']?>)"><i class="fa fa-share"></i></button>
                         <?if($row['tipoobjetoori']=='rhevento' and !empty($row['idobjetoori'])){?>
                        <button title='Desfazer Transferencia' type="button" class="btn btn-danger" onclick="destransfev(<?=$row['idrhevento']?>,<?=$row['idobjetoori']?>)"><i class="fa fa-minus"></i></button>
                         <?}else{?>
                        <button disabled title='Desfazer Transferencia' type="button" class="btn btn-secondary" onclick="alert('Evento não tem origem de transferência')"><i class="fa fa-minus"></i></button>
                        <?}?>
                        <button title='Reprovar' type="button" class="btn btn-secondary" situacao='<?=$row['situacao']?>' idrhevento='<?=$row['idrhevento']?>'  onclick='AlteraStatus(this)'><i class="fa fa-money"></i></button>
                        <button title='Inativar' type="button" class="btn btn-danger" situacao='<?=$row['situacao']?>' idrhevento='<?=$row['idrhevento']?>'  onclick='inativarev(<?=$row['idrhevento']?>)'><i class="fa fa-trash"></i></button>                        
                    </div>
                    <!--
                    <a title="Transferir" class="fa fa-check-circle-o  fa-1x vermelho hoververmelho btn-lg pointer ui-droppable" onclick="transfev(<?=$row['idrhevento']?>)"></a>
                    <?if($row['tipoobjetoori']=='rhevento' and !empty($row['idobjetoori'])){?>
                        <a title="Desfazer Transferencia" class="fa fa-check-circle-o  fa-1x vermelho hoververmelho btn-lg pointer ui-droppable" onclick="destransfev(<?=$row['idrhevento']?>,<?=$row['idobjetoori']?>)"></a>
                    <?}?>
                    <i title="Reprovar" class="fa fa-check-circle-o  fa-1x vermelho hoververmelho btn-lg pointer ui-droppable" situacao='<?=$row['situacao']?>' idrhevento='<?=$row['idrhevento']?>'  onclick='AlteraStatus(this)'></i>
                    -->
                </td>
                <?}?>
            </tr>
            <?
            }
            ?>
            <tr class="res ">
                <td  colspan="5" >Total</td>
                <td><?=$val?></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
        </table>
    </div>
</div>
</div>
<div class="col-md-6">
<div class="panel panel-default">
    <div class="panel-heading"><?echo("<!--".$sql1." -->")?> </div>
    <div class="panel-body">
        <table class="normal">
        <thead>
        <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase; height:20px;">
            <td colspan="8" style="font-size:11px;"  align="center"><?=$nomecurto?> - <?=$evento?> -  <?=$tit?> - <span class="alert-warning bold">NÃO APROVADO</span></td>
        </tr>
       <tr class="header">
            <td class="tdtit grrot">Data</td>
            <td class="tdtit grrot">Hora</td>
            <td class="tdtit grrot">Parcela</td>
            <td class="tdtit grrot">Parcelas</td>                
            <td class="tdtit grrot">Status</td>   
            <td class="tdtit grrot">Valor</td>
            <td class="tdtit grrot">Aprovar</td>
        </tr>
        </thead>
        <tbody>
            <?
            $val=0;
            while($row=mysqli_fetch_assoc($res1)){
                $val=$val+$row['valor'];
            ?>
            <tr class="res ">
                <td ><?=dma($row['dataevento'])?></td>
                <td ><?=$row['hora']?></td>
                <td ><?=$row['parcela']?></td>
                <td ><?=$row['parcelas']?></td>                
                <td ><?=$row['status']?></td>
                <td >
<?
                if($row['formato']=='H'){
                    if($row['valor']<0){echo "-" ;}
                    echo(convertHoras(abs($row['valor'])));
                }else{
                ?>
                <input name="" type="text" class="size5"	value="<?=$row['valor']?>" onchange="atualizavalor(<?=$row['idrhevento']?>,this)">
                <?
                } 
?>
                </td>
                <td>
                    
                    <div class="btn-group" role="group" aria-label="Basic example">                
                        <button title='Aprovar' type="button" class="btn btn-success" situacao='<?=$row['situacao']?>' idrhevento='<?=$row['idrhevento']?>'  onclick='AlteraStatus(this)'><i class="fa fa-money"></i></button>
                        <button title='Inativar' type="button" class="btn btn-danger" situacao='<?=$row['situacao']?>' idrhevento='<?=$row['idrhevento']?>'  onclick='inativarev(<?=$row['idrhevento']?>)'><i class="fa fa-trash"></i></button>                        
                    </div>
                </td>
            </tr>
            <?
            }
            ?>
            <tr class="res ">
                <td  colspan="5" >Total</td>
                <td><?=$val?></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
        </table>
    </div>
</div>
</div>
</div>

<div id="transfevtodos" style="display: none"> 
<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">    
   <div class="row">
      <div class="col-md-12">
         <div class="panel panel-default">
             <div class="panel-body"> 
                 <div class="modal-body">
<?
            if($idrhtipoevento==432){//dia trabalhado vai para he ou he r$
                $sql="select * from rhtipoevento where idrhtipoevento in (6,435)";
            }else{
                $sql="select idrhtipoevento, evento from rhtipoevento where  valorconv>0 and formato='D'  ".getidempresa('idempresa','rhtipoevento')." and status='ATIVO' order by evento";
            } 
           
            $res=d::b()->query($sql) or die("Erro ao carregar eventos sql=".$sql);
            while($row=mysqli_fetch_assoc($res)){
                if($row['idrhtipoevento']==6){
                    $row['evento']="BANCO DE HORAS";//EVENTO HORAS EXTRAS
                }
?>                 
                
                    <div class="row" id="optionsTipos" style="padding: 2px;">
                        <span style="background-color: #337ab7; margin-top: 2px;" class="list-group-item btn btn-light">
                            <a class="selectTipo pointer" id="eventoTipo13" style="color: #FFF; font-size: 16px; text-align: center; width: 100%; padding: 5px"  onclick="transferirtodos(<?=$idrhfolha?>,<?=$row['idrhtipoevento']?>)"><?=$row['evento']?>
                            </a>
                        </span>
                    </div>
               
<?
            }
?>                </div>  
            </div>
         </div>
      </div> 
   </div> 
</div>
</div>


<div id="transfev" style="display: none"> 
<div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">    
   <div class="row">
      <div class="col-md-12">
         <div class="panel panel-default">
             <div class="panel-body"> 
                 <div class="modal-body">
                    <table>
                        <tr>

                            <td>                            
                                <input id="rhevento_idrhevento"  type="hidden" value="" >
                                
                            </td>
                        </tr>
                    </table>
<?
            if($idrhtipoevento==432){//dia trabalhado vai para he ou he r$
                $sql="select * from rhtipoevento where idrhtipoevento in (6,435)";
            }else{
                $sql="select idrhtipoevento, evento from rhtipoevento where  valorconv>0 and formato='D'  ".getidempresa('idempresa','rhtipoevento')." and status='ATIVO'  order by evento";
            }                   
            
            $res=d::b()->query($sql) or die("Erro ao carregar eventos sql=".$sql);
            while($row=mysqli_fetch_assoc($res)){
                if($row['idrhtipoevento']==6){
                    $row['evento']="BANCO DE HORAS";//EVENTO HORAS EXTRAS
                }
?>                 
                
                    <div class="row" id="optionsTipos" style="padding: 2px;">
                        <span style="background-color: #337ab7; margin-top: 2px;" class="list-group-item btn btn-light">
                            <a class="selectTipo pointer" id="eventoTipo13" style="color: #FFF; font-size: 16px; text-align: center; width: 100%; padding: 5px"  onclick="transferirind(<?=$idrhfolha?>,<?=$row['idrhtipoevento']?>)"><?=$row['evento']?>
                            </a>
                        </span>
                    </div>
               
<?
            }
?>                </div>  
            </div>
         </div>
      </div> 
   </div> 
</div>
</div>
 
<script>
function AlteraStatus(vthis){	

	var idrhevento  = $(vthis).attr('idrhevento');
	var situacao = $(vthis).attr('situacao');	
	var  cor, novacor;  

        if (situacao == 'A'){
            cor = 'verde hoververde';
            novacor = 'vermelho hoververmelho';
            CB.post({
                    objetos: "_x_u_rhevento_idrhevento="+idrhevento+"&_x_u_rhevento_situacao=P"
                    ,parcial:true
                    ,msgSalvo: "Situação Alterada"
                    ,posPost: function(){
                        $(vthis).removeClass(cor);
                        $(vthis).addClass(novacor);
                    } 
                });

        }else{

            cor = 'vermelho hoververmelho';
            novacor = 'verde hoververde';
            CB.post({
                        objetos: "_x_u_rhevento_idrhevento="+idrhevento+"&_x_u_rhevento_situacao=A"
                        ,parcial:true
                        ,msgSalvo: "Situação Alterado"
                        ,posPost: function(){
                            $(vthis).removeClass(cor);
                            $(vthis).addClass(novacor);
                        } 
                    });
        }
}
//INICIO TRANSFERENCIA INDIVIDUAL
 function transfev(inidrhevento){
    var strCabecalho = "</strong>Gerar novo evento</strong>";
    $("#cbModalTitulo").html((strCabecalho));

    var  htmloriginal =$("#transfev").html();
    var objfrm= $(htmloriginal);
    
    objfrm.find("#rhevento_idrhevento").attr("name", "x_rhevento_idrhevento");
    objfrm.find("#rhevento_idrhevento").attr("value",  inidrhevento); 
    
    $("#cbModalCorpo").html(objfrm.html());
    $('#cbModal').modal('show');   		
} 
    
function transferirind(inidrhfolha,idrhtipoevento){
    debugger;
    //alert($(vthis).val());
    if(confirm("Transferir valor?")){
        CB.post({
             objetos:  "_x_u_rhfolha_idrhfolha="+inidrhfolha+"&x_stridrhevento="+$("[name=x_rhevento_idrhevento]").val()+"&x_idrhtipoevento="+idrhtipoevento
             ,parcial:true            
         });
    }else{
        document.location.reload(true);
    }     
}
 //FIM TRANSFERENCIA INDIVIDUAL
 
 //INICIO TRANSFERENCIA TODOS
 function transfevtodos(){
    var strCabecalho = "</strong>Gerar novo evento</strong>";
    $("#cbModalTitulo").html((strCabecalho));

    var  htmloriginal =$("#transfevtodos").html();
    var objfrm= $(htmloriginal);

    
    $("#cbModalCorpo").html(objfrm.html());
    $('#cbModal').modal('show');   		
} 
 
 function transferirtodos(inidrhfolha,inidrhtipoevento){
    debugger;
     
    var obtr = $('#eventospendentes').children();
    
    var stridrhevento = ''; 
    var virg=''
    obtr.each(function( ) { 
        if( $(this).attr("idrhevento")!=undefined){
            var tridev = virg + '' + $(this).attr("idrhevento"); 
            stridrhevento += tridev; 
            virg=',';
        }
    });
   
    transferir(inidrhfolha,inidrhtipoevento,stridrhevento);
}
function transferir(inidrhfolha,inidrhtipoevento,stridrhevento){
    if(confirm("Transferir valor?")){
        //alert(stridrhevento);
  
        CB.post({
             objetos:  "_x_u_rhfolha_idrhfolha="+inidrhfolha+"&x_idrhtipoevento="+inidrhtipoevento+"&x_stridrhevento="+stridrhevento
             ,parcial:true            
         });
            
    }else{
        document.location.reload(true);
    }     
}


//FIM TRANSFERENCIA TODOS

function destransfev(idrhevento,idobjetoori){
    CB.post({
        objetos:  "_x_u_rhevento_idrhevento="+idrhevento+"&_x_u_rhevento_status=INATIVO&_y_u_rhevento_idrhevento="+idobjetoori+"&_y_u_rhevento_status=PENDENTE"
        ,parcial:true           
    });
}

function inativarev(idrhevento){    		
	CB.post({
	    objetos:  "_x_u_rhevento_idrhevento="+idrhevento+"&_x_u_rhevento_status=INATIVO"
	    ,parcial:true
	});    
}
function atualizavalor(idrhevento,vthis){
    CB.post({
	    objetos:  "_x_u_rhevento_idrhevento="+idrhevento+"&_x_u_rhevento_valor="+$(vthis).val()
	    ,parcial:true
	});
}
 
 //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>