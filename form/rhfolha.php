<?
require_once("../inc/php/validaacesso.php");
if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "rhfolha";
$pagvalcampos = array(
	"idrhfolha" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from rhfolha where idrhfolha = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<style>
    tr#linha:hover {
	background:#DCDCDC; 
	color: black;
	box-shadow: 2px 2px 5px 0px rgba(0,0,0,0.45);
	}
    
</style>
<?
$idrhevento=$_GET['idrhevento'];
function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}
?>
<script>
	<?if($_1_u_rhfolha_status=='FECHADA' ){?>
//$("#cbModuloForm:input").not('[name*="nf_idnf"],[name*="statusant"],[id*="cbTextoPesquisa"]').prop( "disabled", true );
$("#cbModuloForm").find('input').prop( "disabled", true );
$("#cbModuloForm").find("select" ).prop( "disabled", true );
$("#cbModuloForm").find("button" ).prop( "disabled", true );
$("#cbModuloForm").find("textarea").prop( "disabled", true );
 /*
 $("#cbSalvar").prop("disabled", true); 
 
$(document).keydown(function(event) {

 if((event.keyCode==17) || (event.keyCode==83)){
     alert("Desabilitada função salvar.");
     return false;
 }
   
});
    */
<?}?>
</script>
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">
	    <table  style="width: 100%;">
                <tr>
                    <td><strong>ID.:</strong></td>
                    <td>
                    <?if(!empty($_1_u_rhfolha_idrhfolha)){?>
                        <label class="alert-warning"><?=$_1_u_rhfolha_idrhfolha?></label>
                    <?}?>
                        <input name="_1_<?=$_acao?>_rhfolha_idrhfolha" type="hidden"	value="<?=$_1_u_rhfolha_idrhfolha?>">
                    </td>
		    <td>Título:</td>
		    <td><input class="size70" name="_1_<?=$_acao?>_rhfolha_titulo" type="text"	value="<?=$_1_u_rhfolha_titulo?>" vnulo> </td>
            <td>Periodo Final:</td>
		    <td><input class=" calendario size7" name="_1_<?=$_acao?>_rhfolha_datafim" type="text"	value="<?=$_1_u_rhfolha_datafim?>" vnulo> </td>
		   <!--
                    <td class="nowrap">Dias Utéis:</td>
		    <td><input class="size5" name="_1_<?=$_acao?>_rhfolha_diasuteis" type="text"	value="<?=$_1_u_rhfolha_diasuteis?>" </td>		    
		    -->
            <td align="right">Tipo:</td> 
            <td>
                <?if($_1_u_rhfolha_tipofolha){?>
                <label class="idbox"><?=$_1_u_rhfolha_tipofolha?></label>
                 <input name="_1_<?=$_acao?>_rhfolha_tipofolha" type="hidden"	value="<?=$_1_u_rhfolha_tipofolha?>">
                <?}else{?>
                <select   name="_1_<?=$_acao?>_rhfolha_tipofolha" vnulo>
                    <option value=""></option>
                    <?fillselect("select 'FOLHA','Folha' union select 'FOLHA FERIAS','Folha Férias' union select 'DECIMO TERCEIRO','Décimo Terceiro (Parc. 1)' union  select 'DECIMO TERCEIRO 2','Décimo Terceiro  (Parc. 2)'",$_1_u_rhfolha_tipofolha);?>		
                </select>
                <?}?>
            </td>
            <td align="right">Status:</td> 
            <td>
                <? $rotulo = getStatusFluxo($pagvaltabela, 'idrhfolha', $_1_u_rhfolha_idrhfolha)?>                                              
                <label class="alert-warning" title="<?=$_1_u_rhfolha_status?>" id="statusButton"><?=mb_strtoupper($rotulo['rotulo'],'UTF-8')?> </label></td>
                <input type="hidden" name="_1_<?=$_acao?>_rhfolha_status" id="_1_<?=$_acao?>_rhfolha_status" value="<?=$_1_u_rhfolha_status?>">
            </td>
		</tr>
	    </table>
	</div>	
    </div>
    </div>
<?
if(!empty($_1_u_rhfolha_idrhfolha)){

    $data2 = validadate($_1_u_rhfolha_datafim);
    $avadd='';
    if($_1_u_rhfolha_tipofolha=='FOLHA'){
       $strtipo="  t.flgfolha='Y'  ";
     }elseif($_1_u_rhfolha_tipofolha=='FOLHA FERIAS'){
       $strtipo=" t.flgferias ='Y' ";
     
    }elseif($_1_u_rhfolha_tipofolha=='DECIMO TERCEIRO'){
        $strtipo=" t.flgdecimoterc ='Y' ";
        if($_1_u_rhfolha_status!='FECHADA'){
        $avadd= "SELECT t.idrhtipoevento ,t.evento,t.eventocurto,t.ord,t.formato,'N' as tipo,
                    (select f.idrhfolhaconf from rhfolhaconf f where f.idrhtipoevento=t.idrhtipoevento and f.idrhfolha=".$_1_u_rhfolha_idrhfolha.") as idrhfolhaconf 
                    FROM rhtipoevento t
                    where  idrhtipoevento in (18,33)  and status = 'ATIVO' 
                union ";
        }
    }else{
        $strtipo=" t.flgdecimoterc2 ='Y' ";
        if($_1_u_rhfolha_status!='FECHADA'){
            $avadd= "SELECT t.idrhtipoevento ,t.evento,t.eventocurto,t.ord,t.formato,'N' as tipo,
                        (select f.idrhfolhaconf from rhfolhaconf f where f.idrhtipoevento=t.idrhtipoevento and f.idrhfolha=".$_1_u_rhfolha_idrhfolha.") as idrhfolhaconf 
                        FROM rhtipoevento t
                        where  idrhtipoevento in (18,33)  and status = 'ATIVO' 
                    union ";
        }
    }	   
    $sr= $avadd." SELECT t.idrhtipoevento ,t.evento,t.eventocurto,t.ord,t.formato,t.tipo,(select f.idrhfolhaconf from rhfolhaconf f where f.idrhtipoevento=t.idrhtipoevento and f.idrhfolha=".$_1_u_rhfolha_idrhfolha.") as idrhfolhaconf
            FROM rhtipoevento t
            where ".$strtipo."
		
            and status = 'ATIVO'  order by ord";
           //echo($sr);
    $rrot=d::b()->query($sr) or die("Erro ao buscar eventos : " . mysqli_error(d::b()) . "<p>SQL:".$sr);    
    $arrev=array();
    $arrevento=array();
    while($r = mysqli_fetch_assoc($rrot)){
        if(empty($r['ord'])){
            $r['ord']=9999999;
        }
        $arrev[$r['idrhtipoevento']]['evento']=$r['evento'];
        $arrev[$r['idrhtipoevento']]['eventocurto']=$r['eventocurto'];
        $arrev[$r['idrhtipoevento']]['formato']=$r['formato'];
        $arrev[$r['idrhtipoevento']]['tipo']=$r['tipo'];
        $arrev[$r['idrhtipoevento']]['idrhfolhaconf']=$r['idrhfolhaconf'];
        $arrev[$r['idrhtipoevento']]['ord']=$r['ord'];
    }     
    $arrevento=(array_sort($arrev, 'ord', SORT_ASC));


    if($_1_u_rhfolha_status=='FECHADA'){
        $sqlv="select e.idpessoa,e.idrhtipoevento,p.contrato,t.tipo,t.formato,ifnull(sum(e.valor),0) as valor
                from rhevento e force index(idrhfolha_idpessoa) join pessoa p on (p.idpessoa= e.idpessoa)
                join rhtipoevento t on(t.idrhtipoevento= e.idrhtipoevento)
                where  e.status='QUITADO'
                and ".$strtipo."
               
                and e.idpessoa is not null and e.idrhtipoevento is not null              
                and e.idrhfolha=".$_1_u_rhfolha_idrhfolha." group by e.idpessoa,e.idrhtipoevento";            
    }else{

        if($_1_u_rhfolha_tipofolha=='DECIMO TERCEIRO 2'){

            $sl="select * from rhfolha f 
            where f.idrhfolha !=".$_1_u_rhfolha_idrhfolha." ".getidempresa('idempresa','rhfolha')." 
            and datafim < '".$data2."' and tipofolha='DECIMO TERCEIRO' and status!='FECHADA' order by datafim desc limit 1";
          
        }else{
            $sl="select * from rhfolha f 
            where f.idrhfolha !=".$_1_u_rhfolha_idrhfolha." ".getidempresa('idempresa','rhfolha')." 
            and datafim < '".$data2."' and tipofolha='".$_1_u_rhfolha_tipofolha."' and status!='FECHADA' order by datafim desc limit 1";

        }
       
        $resl=d::b()->query($sl);
        //die($sl);
        $qtdl=mysqli_num_rows($resl);
        if($qtdl>0){
            $rl=mysqli_fetch_assoc($resl);
            $strdatafim="  and e.dataevento > '".$rl['datafim']."'";

        }else{
            $strdatafim='';
        }

        $slp="select group_concat(idpessoa) as lsfunc from rhfolhaitem where idrhfolha = ".$_1_u_rhfolha_idrhfolha;
        $relp=d::b()->query($slp);
        $rlpes=mysqli_fetch_assoc($relp);

        if(!empty($rlpes['lsfunc'])){
                $sqlv="select e.idpessoa,e.idrhtipoevento,t.tipo,t.formato,ifnull(sum(e.valor),0) as valor
                from rhevento e   join rhtipoevento t on(t.idrhtipoevento= e.idrhtipoevento)
                where ".$strtipo."
               
                and e.status='PENDENTE' 
                and e.situacao='A' 
                ".$strdatafim."
                -- and e.dataevento between DATE_SUB('".$data2."', INTERVAL 1 month) and  '".$data2."'          
                and e.dataevento <= '".$data2."'  
                and e.idpessoa in(".$rlpes['lsfunc'].")
                group by e.idpessoa,e.idrhtipoevento";
                
        /*
                $sqlv="select e.idpessoa,e.idrhtipoevento,p.contrato,t.tipo,t.formato,ifnull(sum(e.valor),0) as valor
                        from rhevento e force index(status_idpessoa_data) join pessoa p on (p.idpessoa= e.idpessoa and p.status='ATIVO')
                        join rhtipoevento t on(t.idrhtipoevento= e.idrhtipoevento)
                        where ".$strtipo."
                        ".getidempresa('e.idempresa','rhevento')."
                        and e.status='PENDENTE' 
                        and e.situacao='A'
                        and exists (select 1 from rhfolhaitem i where i.idpessoa = e.idpessoa and i.idrhfolha = ".$_1_u_rhfolha_idrhfolha.")
                        and e.idpessoa is not null and e.idrhtipoevento is not null
                        ".$strdatafim."
                        and e.dataevento between DATE_SUB('".$data2."', INTERVAL 1 month) and  '".$data2."'
                        group by e.idpessoa,e.idrhtipoevento";
        */
                        
            }
        }
            $resevf=d::b()->query($sqlv) or die("Erro ao buscar eventos da folha: " . mysqli_error(d::b()) . "<p>SQL:".$sqlv);    
            $arrEventoF=array();
            $arrvcont=array();   
            while($rowv = mysqli_fetch_assoc($resevf)){  
                $contrato=traduzid('pessoa','idpessoa', 'contrato',$rrowvownf['idpessoa'] );     
                $arrEventoF[$rowv['idpessoa']][$rowv['idrhtipoevento']]=$rowv['valor'];
                $arrvcont[$contrato][$rowv['idrhtipoevento']]['valor']= $arrvcont[$contrato][$rowv['idrhtipoevento']]['valor']+$rowv['valor'];
                $arrvcont[$contrato][$rowv['idrhtipoevento']]['formato']=$rowv['formato'];
                $arrvcont[$contrato][$rowv['idrhtipoevento']]['tipo']=$rowv['tipo'];
                    
            }     
    
?>


<div class="hide" id="fmrhtipoevento">
<div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-body" > 
            <div class="col-md-12" >
            <div class="row">
                <div class="col-md-6" >
                <i class="fa fa-eye fa-1x preto listartodos hoverazul pointer"  title="Listar Todos" onclick="listarocultartodos(this,'listar')"></i>
                Listar Todos
                </div>
               
            </div>   
            <hr>        
<?
 while (list($idrhtipoevento, $evento) = each($arrevento)){
?>
            <div class="row">
                <div class="col-md-12" >
                    <?
                    $facor='preto';
                    $colho='fa-eye';
                    if($evento['idrhfolhaconf']>0){
                        $facor='cinza';
                        $colho='fa-eye-slash';
                    }?>
                    <i class="fa <?=$colho?> fa-1x <?=$facor?>  hoverazul pointer icone<?=$idrhtipoevento?>" idrhtipoevento="<?=$idrhtipoevento?>" idrhfolhaconf="<?=$evento['idrhfolhaconf']?>" cor="<?=$facor?>" title="Ocultar/Listar Evento" onclick="ocultarevento(this,<?=$idrhtipoevento?>)"></i>
                
                    <?=$evento['evento']?>
                </div>
                        
            </div>
<?
 }
 reset($arrevento);//reseta o array de eventos 
?>
            </div>
        </div>
    </div>
</div>
</div>
<style>
.btnRefresh {
    position: absolute;
    top: 35px;
    right: 15px;
    font-size: 24px;
    color: #bbbbbb;
}
.btnRefresh:hover {
    color: #747474;
    animation: fa-spin 2s infinite linear;
}
.atualizando{
    color: #747474;
    animation: fa-spin 2s infinite linear;
}
    </style>

<div class="col-md-12" >
    <div class="panel panel-default" >
	<div class="panel-body" >
<?
    if($_1_u_rhfolha_tipofolha!='DECIMO TERCEIRO' and $_1_u_rhfolha_status !='FECHADA'){
    ?> 
    <i title="Atualizar Valores de INSS/FGTS/IRRF" class="fa fa-refresh pointer btnRefresh" onclick="atualizaimp(this,<?=$_1_u_rhfolha_idrhfolha?>)"></i>
    <?}?>
	<table class="table table-striped planilha tabelaevento">
	<tr>
	    <td>FUNCIONÁRIO</td>
        <td>
            <?if($_1_u_rhfolha_tipofolha=='DECIMO TERCEIRO' or $_1_u_rhfolha_tipofolha=='DECIMO TERCEIRO 2'){ echo("M");}else{echo("DT");}?>            
        </td>
<?
   while (list($idrhtipoevento, $evento) = each($arrevento)){
            $idrhtipoeventosum= traduzid('rhtipoevento', 'idrhtipoevento', 'idrhtipoeventosum', $idrhtipoevento);
            if(empty($idrhtipoeventosum)){
                $idrhtipoeventosum=$idrhtipoevento;
            }
            if(empty($evento['eventocurto'])){
                $vevento=$evento['evento'];
                $strev="";
            }else{
                $vevento=$evento['eventocurto'];
                $strev=$evento['evento'];
            }
            if($evento['idrhfolhaconf']>0){
                $oculto='hide';
            }else{
                $oculto='';
            }
?>
	    <td align="center" class="<?=$oculto?> campoevento trevento<?=$idrhtipoevento?>">            
            <div class="row">
                <div class="col">
                    <a class="hoverazul pointer" onclick="janelamodal('?_modulo=rhtipoevento&_acao=u&idrhtipoevento=<?=$idrhtipoevento?>')" title="<?=$strev?>">
                        <b style="font-size:10PX;"> <?=$vevento?></b>
                    </a>
                </div>
            </div>
            <div class="btn-group" role="group" aria-label="Basic example">                
                <button style="padding: 2px" title="Aprovar Todos" type="button" class="btn btn-success" onclick="AlteraStatus(<?=$idrhtipoeventosum?>,'<?=validadate($_1_u_rhfolha_datafim)?>','A')"><i class="fa fa-money"></i></button>
                <button style="padding: 2px" title='Reprovar Todos' type="button" class="btn btn-secondary" onclick="AlteraStatus(<?=$idrhtipoeventosum?>,'<?=validadate($_1_u_rhfolha_datafim)?>','P')"><i class="fa fa-money"></i></button>
            </div>
        </td>
<?
    }//while (list($idevento, $evento) = each($arrevento)){
    //reset($arrevento);//reseta o array de eventos 
    $sqlconf="select * from rhfolhaconf where idrhfolha=".$_1_u_rhfolha_idrhfolha;
    $resconf= d::b()->query($sqlconf);
    $qtdconfnf=mysqli_num_rows($resconf);
    if($qtdconfnf<1){
        $confolho='fa-eye';
        $corconfolho='preto';
    }else{
        $confolho='fa-eye-slash';
        $corconfolho='cinza';
    }

?>
            <td align="center">Total</td>
            <td></td>
            <td><i class="fa <?=$confolho?> fa-2x <?=$corconfolho?> hoverazul pointer" id="olhogeral" title="Ocultar/Listar Evento" onclick="rhfolhaconf()" ></i></td>
            
	</tr>                
<?   
$sx="select   distinct(i.regime) as contrato
from rhfolhaitem i,pessoa p	
where p.idpessoa = i.idpessoa  
and i.idrhfolha=".$_1_u_rhfolha_idrhfolha."  and p.contrato is not null order by p.contrato asc";

$rex=d::b()->query($sx) or die("Erro ao buscar tipos de contrado dos funcionarios : " . mysqli_error(d::b()) . "<p>SQL:".$sx);

while($rx=mysqli_fetch_assoc($rex)){
$i = 0;
    $sql="select  i.idrhfolhaitem,concat(p.nomecurto) as nomecurto,p.nome,p.idpessoa,i.diastrab,i.inicio,
    (select count(*)  from vw8PessoaUnidadeRateio i where i.idpessoa=p.idpessoa) as qtdun
	    from rhfolhaitem i,pessoa p	, empresa e
	    where p.idpessoa = i.idpessoa  and e.idempresa = p.idempresa
       and i.idrhfolha=".$_1_u_rhfolha_idrhfolha." and i.regime='".$rx['contrato']."' order by nomecurto";
    $res=d::b()->query($sql) or die("Erro ao buscar funcionarios : " . mysqli_error(d::b()) . "<p>SQL:".$sql);
    
    $valtotal=0;
    while($row=mysqli_fetch_assoc($res)){
		$i++;
        $valor=0;
        $idnfforn='';
        $qtdnf=0;

        if($rx['contrato'] == 'PJ' or $rx['contrato'] == 'ES' or $rx['contrato'] == 'PD'){
            $sqlx = "select 
            c.idpessoacontato
            ,p.idpessoa      
            ,concat(e.sigla,' - ',p.nome) as nome
             from pessoa p,pessoacontato c, empresa e
            where p.idpessoa = c.idpessoa
            and e.idempresa = p.idempresa
			and p.idtipopessoa=5
            and p.status='ATIVO'			
            and c.idcontato = ".$row['idpessoa']." order by nome";
            $resx = d::b()->query($sqlx) or die("A Consulta do fornecedor falhou :".mysqli_error()."<br>Sql:".$sqlx);
            $rownumx= mysqli_num_rows($resx);
            
            if($rownumx>0){ 
                $rowx=mysqli_fetch_assoc($resx);                
                $nome = $rowx['nome'];
                $idpessoa = $rowx['idpessoa'];
            }else{
                $nome = $row['nomecurto'];
                $idpessoa = $row['idpessoa'];
            }
        }else{
            $nome = $row['nomecurto'];
            $idpessoa = $row['idpessoa'];
        }
?>		
	<tr id="linha">   
	    <td class='nowrap'>
            <?
            if($row['qtdun'] < 1 ){
        ?>
        <i class="fa fa-warning laranja" title="Funcionário sem unidade relacionada, não é permitido a folha gerar NF nesta condições, favor corrigir o cadastro do funcionário."></i>
        <?
            }
            ?>

		<a title="<?=$row['nome']?>" class="hoverazul pointer" onclick="janelamodal('?_modulo=funcionario&_acao=u&idpessoa=<?=$row['idpessoa']?>')" title="Funcionário">
            <b><?=substr($nome,0,20) ?></b>						
		</a>
                <?
                $anexonanf='';
                if(($rx['contrato']=='PJ' or $rx['contrato']=='ES'  or $rx['contrato']=='PD') and $_1_u_rhfolha_tipofolha=='FOLHA'){
                    $sqln="select ( select count(*) from arquivo a where a.idobjeto =  n.idnf and a.tipoobjeto ='nf') as temarquivo, n.*
                    from nf n 
                    where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha." and n.tipoobjetosolipor='rhfolha'  and n.idpessoa =".$idpessoa;
                    $resn=d::b()->query($sqln) or die("Erro ao buscar NF PJ: Erro: ".mysqli_error(d::b())."\n".$sqln);
                    $qtdnf=mysqli_num_rows($resn);
                    if($qtdnf<1){
                        
 ?>
                <a title="Novo NF-PJ/Estagiário" class="fa fa-plus-circle fa-1x verde pointer"  onclick="geranotapj(this,<?=$row['idpessoa']?>)" >
                        
<?                       
                    }else{
                        $rown=mysqli_fetch_assoc($resn);
                        $idnfforn=$rown['idnf'];
                        if($rown['temarquivo']>0){
                            $anexonanf=" <a title='Anexo na NF-PJ' class='fa fa-paperclip fa-1x cinza pointer'  >";
                        }
?>
                        <a title="NF-PJ" class="fa fa-bars fa-1x verde pointer"  onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')" >
                        <?if($_1_u_rhfolha_status!='FECHADA'){?>
                        <a title="Atualizar NF" class="fa fa-refresh btn-lg pointer azul" onclick="atualizarnfPJ(this,<?=$rown['idnf']?>,<?=$row['idpessoa']?>,'PJ')"></a>
<?        
                        }
                    }
                }
                if($_1_u_rhfolha_tipofolha=='FOLHA FERIAS'){
               
                   $sqln=" select * from nfitem where idobjetoitem = ".$_1_u_rhfolha_idrhfolha." and tipoobjetoitem='rhfolha' and idpessoa = ".$idpessoa." and not exists (Select 1 from nf n where n.idnf = nfitem.idnf and n.status != 'CANCELADO')";
    
                   $resn=d::b()->query($sqln) or die("Erro ao buscar NF de ferias: Erro: ".mysqli_error(d::b())."\n".$sqln);
                   $qtdnf=mysqli_num_rows($resn);
                   if($qtdnf>0){
                       if ($row["inicio"] != ''){
                            $desablepr="disabled='disabled'";
                       }else{
                            $desablepr="";
                       }
                    
                
                        $rown=mysqli_fetch_assoc($resn);
                        $idnfforn=$rown['idnf']
                    ?>
                        <a title="NF-Ferias" class="fa fa-bars fa-1x verde pointer"  onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')" ></a>
                   <?
                 }else{ $desablepr=""; }
                    ?>
                    <br>                   
                    <input <?=$desablepr?> title="Início das Férias"  id="datainicio<?=$row['idrhfolhaitem']?>" class=" size10 calendario datainicio" name="datainicio<?=$row['idrhfolhaitem']?>" type="text" 	value="<?=dma($row["inicio"])?>">
                    <script>
                    $('#datainicio<?=$row['idrhfolhaitem']?>').on('apply.daterangepicker', function(ev, picker) {
                            // console.log(picker.startDate.format('YYYY-MM-DD'));

                            setanalisedt(<?=$row['idrhfolhaitem']?>,picker.startDate.format('DD/MM/YYYY'));
                        });
                    </script>
                <?
                  

                }?>
	    </td>
        <td>
            <input name="" type="text" class="size5" value="<?=$row['diastrab']?>" onchange="atualizavalor(<?=$row['idrhfolhaitem']?>,this)">
        </td>
<?
    
    $valloop=0;
    reset($arrevento);//reseta o array de eventos 
	while (list($idrhtipoevento, $evento) = each($arrevento)){          
        $valloop=$valloop+1;     
        /*
        if($_1_u_rhfolha_status=='FECHADA'){
            $sqlv="select sum(valor) as valor
                    from rhevento force index(idrhfolha_idpessoa)
                    where idrhtipoevento=".$idrhtipoevento."
                    and status='QUITADO'
                   ".getidempresa('idempresa','rhevento')."
                    and idpessoa = ".$row['idpessoa']."
                    and idrhfolha=".$_1_u_rhfolha_idrhfolha." group by idrhtipoevento";            
        }else{
            $sqlv="select sum(valor) as valor
                    from rhevento force index(status_idpessoa_data)
                    where idrhtipoevento=".$idrhtipoevento."
					 ".getidempresa('idempresa','rhevento')."
                    and idpessoa = ".$row['idpessoa']."
                  
                    and status='PENDENTE' 
                    and situacao='A'
                    and dataevento <= '".$data2."' group by idrhtipoevento";
        }
 	     // echo($sqlv);
	    $resv=d::b()->query($sqlv) or die("Erro ao buscar eventos do funcionário: " . mysqli_error(d::b()) . "<p>SQL:".$sqlv);  
        $rowv= mysqli_fetch_assoc($resv);
        */
        if($evento['tipo']=='N'){//evento neim credito ou debito pega um valor do fixo
            $sqlv="select valor from rheventopessoa ep where ep.idrhtipoevento=".$idrhtipoevento." and status = 'ATIVO' and idpessoa =".$row['idpessoa'];
            $resv=d::b()->query($sqlv) or die("Erro ao buscar valor fixo do funcionário: " . mysqli_error(d::b()) . "<p>SQL:".$sqlv);  
            $rowv= mysqli_fetch_assoc($resv);
            if(empty($rowv['valor'])){$rowv['valor']='0.00';}
          
        }else{
            $rowv['valor']=$arrEventoF[$row['idpessoa']][$idrhtipoevento];
        }
        

        		
         
            $idrhtipoeventosum= traduzid('rhtipoevento', 'idrhtipoevento', 'idrhtipoeventosum', $idrhtipoevento);
            if(empty($idrhtipoeventosum)){
                $idrhtipoeventosum=$idrhtipoevento;
            }
            if($evento['formato']=='D'){
                if($evento['tipo']=='C'){
                    $valor=$valor+$rowv['valor'];   
                }elseif($evento['tipo']=='D'){
                    $valor=$valor-$rowv['valor'];   
                }
            }
            if($evento['idrhfolhaconf']>0){
                $oculto='hide';
            }else{
                $oculto='';
            }

          ?>	 
            <td  align="center" title="<?=$evento['evento']?>" class="<?=$oculto?> campoevento trevento<?=$idrhtipoevento?>"> 
                <?
				echo "<!--";
				echo $sqlv;
				echo "-->";
                if(!empty($rowv['valor'])){?>
                    <a class="hoverazul pointer" onclick="janelamodal('?_modulo=rhtipoeventofolha&idrhfolha=<?=$_1_u_rhfolha_idrhfolha?>&idrhtipoevento=<?=$idrhtipoeventosum?>&idpessoa=<?=$row['idpessoa']?>&dataevento2=<?=$data2?>')" title="<?=$evento['evento']?>">
                    <?
                    if($evento['formato']=='H'){
                        if($rowv['valor']<0){echo "-" ;}
                        echo(convertHoras(abs($rowv['valor'])));
                    }else{
                        echo $rowv['valor']; 
                    }
?>
                    </a>
<?                        
                }else{
					 if($_1_u_rhfolha_tipofolha!='FOLHA'){
?>
                      <a class="fa fa-plus-circle btn-lg pointer verde hoververmelho fa-1x" onclick="ativainput(this,<?=$idrhtipoeventosum?>,<?=$row['idpessoa']?>)" title="<?=$evento['evento']?>"></a>  
                      <input title="<?=$evento['evento']?>" disabled="disabled" type="text" id="<?=$idrhtipoeventosum?>novo<?=$row['idpessoa']?>" name="" idpessoa="<?=$row['idpessoa']?>" dtev="<?=dma($data2)?>" idrhtipoevento="<?=$idrhtipoeventosum?>"  onchange="gerarhevento(this)" class="screen valor hidden" style="width: 40px !important; background-color: white;" >
<?         
					 }elseif($idrhtipoeventosum==62 or $idrhtipoeventosum==6){
?>
                    <a class="hoverazul pointer" onclick="janelamodal('?_modulo=rhtipoeventofolha&idrhfolha=<?=$_1_u_rhfolha_idrhfolha?>&idrhtipoevento=<?=$idrhtipoeventosum?>&idpessoa=<?=$row['idpessoa']?>&dataevento2=<?=$data2?>')" title="<?=$evento['evento']?>">
                        00:00
                    </a>
<?
                    }
                }
                    ?>
                
                
            </td>
<?
         
	}//while (list($idevento, $evento) = each($arrevento)){
    $valtotal=$valtotal+$valor;
	
    ?>          
            <td align="center"> 
                <?if($qtdnf<1){?>
                <b><?=number_format(tratanumero($valor), 2, ',', '.');?></b>
                <?}else{
                    //$rownf=mysqli_fetch_assoc($resn);
?>
                <a title="NF-PJ" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$idnfforn?>')"><?=number_format(tratanumero($valor), 2, ',', '.');?></a>
    <?
                }
?>
            </td>
            <td align="center">
                <? if($_1_u_rhfolha_status!='FECHADA'){?>
                <i class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer" onclick="CB.post({objetos:'_ajax_d_rhfolhaitem_idrhfolhaitem=<?=$row["idrhfolhaitem"]?>'})" title="Excluir"></i>
                <?}?>
            </td>
            <td align="center" class="nowrap">
                <a class="fa fa-print pointer hoverazul" title="Holerite" onclick="janelamodal('report/rhholerite.php?_acao=u&idrhfolha=<?=$_1_u_rhfolha_idrhfolha?>&idpessoa=<?=$row['idpessoa']?>')"></a>
                    <?echo($anexonanf);?>
            </td>
	</tr>
<?
    }// while($row=mysqli_fetch_assoc($res)){
    $valloop=$valloop+1;
    
    if($rx['contrato']=="CLT"){
        $scontrato="CLT";
    }elseif($rx['contrato']=="PJ"){
        $scontrato="PJ";
    }elseif($rx['contrato']=="TERC"){
        $scontrato="TERCEIRO";
    }elseif($rx['contrato']=="SO"){
        $scontrato="SÓCIO";
    }elseif($rx['contrato']=="ES"){
        $scontrato="ESTAGIÁRIO";
    }elseif($rx['contrato']=="PD"){
        $scontrato="CONTRATO POR PRAZO DETERMINADO";
    }
    
   
?>		
    <tr>
        <td >
            <b>
            <?
        IF($rx['contrato']=='SO'){
           echo("Sócio").' ('.$i.')';
        }elseif($rx['contrato']=='ES'){
            echo("Estagiário").' ('.$i.')';
         }elseif($rx['contrato']=='PD'){
            echo("Contrato Por Prazo Determinado").' ('.$i.')';
         }else{
            echo $rx['contrato'].' ('.$i.')';
        }       
            ?>
            </b>
        </td>
        <td></td>
<?
    $valorf=0;
    reset($arrevento);//reseta o array de eventos 
	while (list($idrhtipoevento, $evento) = each($arrevento)){ 
       /*
        if($_1_u_rhfolha_status=='FECHADA'){
            $sqlv="select e.idrhtipoevento,t.evento,t.tipo,t.formato,sum(e.valor) as valor
                    from rhevento e force index(idrhfolha_idpessoa),rhtipoevento t,pessoa p
                    where t.idrhtipoevento=e.idrhtipoevento
                    and t.idrhtipoevento=".$idrhtipoevento."
					and e.status='QUITADO'
                    and e.idpessoa  =p.idpessoa
                    ".getidempresa('e.idempresa','rhevento')."
                    and p.contrato='".$rx['contrato']."'
                    and e.idrhfolha=".$_1_u_rhfolha_idrhfolha." group by e.idrhtipoevento,t.evento,t.tipo order by t.evento";            
        }else{
            $sqlv="select e.idrhtipoevento,t.evento,t.tipo,t.formato,sum(e.valor) as valor
                    from rhevento e force index(status_idpessoa_data),rhtipoevento t,pessoa p
                    where t.idrhtipoevento=e.idrhtipoevento
                     and t.idrhtipoevento=".$idrhtipoevento."
                    and e.idpessoa =p.idpessoa
                    and p.contrato='".$rx['contrato']."'
                    ".getidempresa('e.idempresa','rhevento')."
                    and e.status='PENDENTE' 
                    and e.situacao='A'
                    and e.dataevento <= '".$data2."' group by e.idrhtipoevento,t.evento,t.tipo,t.formato order by t.formato,t.tipo,t.evento";   
        }
 	     // echo($sqlv);
	    $resv=d::b()->query($sqlv) or die("Erro ao buscar o valor dos eventos : " . mysqli_error(d::b()) . "<p>SQL:".$sqlv); 
        $rowv= mysqli_fetch_assoc($resv);
        */
        $rowv['valor']=$arrvcont[$rx['contrato']][$idrhtipoevento]['valor'];
        $rowv['tipo']=$arrvcont[$rx['contrato']][$idrhtipoevento]['tipo'];
        $rowv['formato']= $arrvcont[$rx['contrato']][$idrhtipoevento]['formato'];

        if($rowv['formato']=='D'){
            if($rowv['tipo']=='C'){
                $trsinal='+';
                $valorf=$valorf+tratanumero($rowv['valor']);   
            }elseif($rowv['tipo']=='D'){
                $trsinal='-';
                $valorf=$valorf-tratanumero($rowv['valor']);   
            }  
            $trtipo='R$';
        }else{
            if($rowv['tipo']=='C'){
                $trsinal='+';
               // $valor=$valor+$rowv['valor'];   
            }elseif($rowv['tipo']=='D'){
                $trsinal='-';
               // $valor=$valor-$rowv['valor'];   
            }  
            $trtipo='HR';
        }

        if($evento['idrhfolhaconf']>0){
            $oculto='hide';
        }else{
            $oculto='';
        }

?>      
        <td align="center" class="<?=$oculto?> campoevento trevento<?=$idrhtipoevento?>">
            <span title="<?=$trsinal?> <?=$trtipo?> ">
        <?
            if($evento['formato']=='H'){
                if($rowv['valor']<0){echo "-" ;}
                echo(convertHoras(abs($rowv['valor'])));
            }else{
                echo  number_format(tratanumero($rowv['valor']), 2, ',', '.');                
            }  
        ?>
            </span> 

        </td>
<?

    }//while (list($idrhtipoevento, $evento) = each($arrevento)){ 
?>       
        
        
        <td align="center">
            <b><?=number_format(tratanumero($valtotal), 2, ',', '.');?> </b>
        </td>
    </tr>
<?
}//while($rx=mysqli_fetch_assoc($rex)){
?>  
    <tr>
        <td colspan="100">
            <input style="width: 20em;" id="addfuncionario" class="compacto" type="text" cbvalue placeholder="Adicionar Funcionario">
        </td>
    </tr>
	</table>  
<?

     if($_1_u_rhfolha_status=='FECHADA'){
            $sqlv="select e.idrhtipoevento,t.evento,t.tipo,t.formato,sum(e.valor) as valor
                    from rhevento e force index(idrhfolha_idpessoa),rhtipoevento t
                    where t.idrhtipoevento=e.idrhtipoevento
                    and ".$strtipo."
                    and t.status='ATIVO'
					and e.status='QUITADO'
                    and e.idpessoa  is not null
                    
                    and e.idrhfolha=".$_1_u_rhfolha_idrhfolha." group by e.idrhtipoevento,t.evento,t.tipo order by t.evento";            
        }else{
            $slp="select group_concat(idpessoa) as lsfunc from rhfolhaitem where idrhfolha = ".$_1_u_rhfolha_idrhfolha;
            $relp=d::b()->query($slp);
            $rlpes=mysqli_fetch_assoc($relp);
            if(!empty($rlpes['lsfunc'])){
            
            $sqlv="select e.idrhtipoevento,t.evento,t.tipo,t.formato,sum(e.valor) as valor
                    from rhevento e force index(status_idpessoa_data) join rhtipoevento t
                    where t.idrhtipoevento=e.idrhtipoevento
                    and ".$strtipo."
                    and t.status='ATIVO'
                    and t.formato='D'
                    and e.idpessoa  is not null
                    and e.status='PENDENTE' 
                   
                    and e.situacao='A'
                    and e.dataevento between DATE_SUB('".$data2."', INTERVAL 1 month) and  '".$data2."'             
                    and e.idpessoa in(".$rlpes['lsfunc'].")
                    -- and e.dataevento between DATE_SUB('2021-11-30', INTERVAL 1 month) and  '2021-11-30' 
                    group by e.idrhtipoevento,t.evento,t.tipo,t.formato order by t.formato,t.tipo,t.evento";   
        
            }else{
                $sqlv='';
            }   
            
                  
        
        }
        if(!empty($sqlv)){
 	     // echo($sqlv);
	    $resv=d::b()->query($sqlv) or die("Erro ao buscar o valor dos eventos : " . mysqli_error(d::b()) . "<p>SQL:".$sqlv); 
?>
        <table class="table table-striped planilha" >
            <tr>
                <th>Evento</th>
                <th>Valor</th>
            </tr>
<?      $valor=0;
        $trtipo='';
        $trsinal='';
        while($rowv= mysqli_fetch_assoc($resv)){
            if($rowv['formato']=='D'){
                if($rowv['tipo']=='C'){
                    $trsinal='+';
                    $valor=$valor+$rowv['valor'];   
                }elseif($rowv['tipo']=='D'){
                    $trsinal='-';
                    $valor=$valor-$rowv['valor'];   
                }  
                $trtipo='R$';
            }else{
                if($rowv['tipo']=='C'){
                    $trsinal='+';
                   // $valor=$valor+$rowv['valor'];   
                }elseif($rowv['tipo']=='D'){
                    $trsinal='-';
                   // $valor=$valor-$rowv['valor'];   
                }  
                $trtipo='HR';
            }
?>
            <tr>
                <td><?=$rowv['evento']?></td>
                <td><?=$trsinal?> <?=$trtipo?>  <?=$rowv['valor']?></td>
            </tr>
            
<?           
        }
?>
            <tr>
                <td>Total:</td>
                <td>R$ <?=$valor?></td>
            </tr>
        </table>
      <?}?>
	</div>
    </div>
</div>


<div class="col-md-12">
        <div class="panel panel-default">  
        <div class="panel-heading">Gerar Notas CLT Por Tipo</div>    
            <div class="panel-body">    
           
 <? 
                if($_1_u_rhfolha_tipofolha=='FOLHA'){
?>      
                 <div class="col-md-12">
                             <div class="col-md-2">        
                                      
                                       Salário <?                                       
                                  $sqln="SELECT n.idnf,n.tipoorc,n.status,n.total,n.dtemissao,concat(e.sigla,' - ',p.nome) as nome
                                  from nf n join pessoa p on(p.idpessoa=n.idpessoa)
                                  join empresa e on e.idempresa = p.idempresa
                                  where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha."
                                  and n.status !='CANCELADO'
                                  and n.tipoobjetosolipor='rhfolha' and n.tipoorc='SALARIO'";
                                          
                                      $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                                      $qtdn=mysqli_num_rows($resn);
                                      if($qtdn>0){
                                          $rown=mysqli_fetch_assoc($resn);
                                          echo("R$:");  
                      ?>
                                          <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>                    
                                          <?if($_1_u_rhfolha_status!='FECHADA'){?>
                                          <a title="Atualizar NF" class="fa fa-refresh btn-lg pointer azul" onclick="atualizarnf(this,<?=$rown['idnf']?>,'SALARIO')"></a>
                      <?
                                          }
                                      }else{
                              ?>       
                               <?         if($_1_u_rhfolha_status!='FECHADA'){?>            
                                      <button id="cbNovo" type="button" class="btn btn-primary btn-xs" onclick="geranota(this,'SALARIO')" title="Novo item">
                                          <i class="fa fa-plus"></i>Novo
                                      </button>  
                              <?
                                          }
                                  }
                              ?>
                                 
                              </div>
         
               <div class="col-md-2">        
                                       
                         Vale  
   <?                                       
                    $sqln="SELECT n.idnf,n.tipoorc,n.status,n.total,n.dtemissao,concat(e.sigla, ' - ',p.nome) as nome
                    from nf n join pessoa p on(p.idpessoa=n.idpessoa)
                    join empresa e on e.idempresa = p.idempresa
                    where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha."
                    and n.status !='CANCELADO'
                    and n.tipoobjetosolipor='rhfolha' and n.tipoorc='VALE'";
                    
                $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                $qtdn=mysqli_num_rows($resn);
                if($qtdn>0){
                    $rown=mysqli_fetch_assoc($resn);
                    echo("R$:");  
?>
                    <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>                    
                    <?if($_1_u_rhfolha_status!='FECHADA'){?>
                    <a title="Atualizar NF" class="fa fa-refresh btn-lg pointer azul" onclick="atualizarnf(this,<?=$rown['idnf']?>,'VALE')"></a>
<?
                    }
                }else{
                ?>
                 <?if($_1_u_rhfolha_status!='FECHADA'){?>
                    <button id="cbNovo" type="button" class="btn btn-primary btn-xs" onclick="geranota(this,'VALE')" title="Gera nota">
                        <i class="fa fa-plus"></i>Novo
                    </button>
                <?
                 }
                }
                ?>                  
                       
                    
                </div>

                <div class="col-md-2 nowrap">        
                     FGTS Menor Aprendiz
            <?                                       
                    $sqln="SELECT n.idnf,n.tipoorc,n.status,n.total,n.dtemissao,concat(e.sigla, ' - ',p.nome) as nome
                    from nf n join pessoa p on(p.idpessoa=n.idpessoa)
                    join empresa e on e.idempresa = p.idempresa
                    where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha."
                    and n.status !='CANCELADO'
                    and n.tipoobjetosolipor='rhfolha' and n.tipoorc='FGTSMA'";
                    
                    $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                    $qtdn=mysqli_num_rows($resn);
                    if($qtdn>0){
                        $rown=mysqli_fetch_assoc($resn);
                        echo("R$:");  
                    ?>
                            <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>                    
                            <?if($_1_u_rhfolha_status!='FECHADA'){?>
                            <a title="Atualizar NF" class="fa fa-refresh btn-lg pointer azul" onclick="atualizarnf(this,<?=$rown['idnf']?>,'FGTSMA')"></a>
                    <?
                            }
                    }else{
                    ?>
                     <?if($_1_u_rhfolha_status!='FECHADA'){?>
                        <button id="cbNovo" type="button" class="btn btn-primary btn-xs" onclick="geranota(this,'FGTSMA')" title="Gera nota">
                            <i class="fa fa-plus"></i>Novo
                        </button>
                    <?
                     }
                    }
                    ?>               
                </div>

                <div class="col-md-2 nowrap">        
                    Consignado  
            <?                                       
                    $sqln="SELECT n.idnf,n.tipoorc,n.status,n.total,n.dtemissao,concat(e.sigla, ' - ',p.nome) as nome
                    from nf n join pessoa p on(p.idpessoa=n.idpessoa)
                    join empresa e on e.idempresa = p.idempresa
                    where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha."
                    and n.status !='CANCELADO'
                    and n.tipoobjetosolipor='rhfolha' and n.tipoorc='CONSIGNADO'";
                    
                    $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                    $qtdn=mysqli_num_rows($resn);
                    if($qtdn>0){
                        $rown=mysqli_fetch_assoc($resn);
                        echo("R$:");  
                    ?>
                        <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>                    
                        <?if($_1_u_rhfolha_status!='FECHADA'){?>
                        <a title="Atualizar NF" class="fa fa-refresh btn-lg pointer azul" onclick="atualizarnf(this,<?=$rown['idnf']?>,'CONSIGNADO')"></a>
                    <?
                        }
                    }else{
                    ?>
                     <?if($_1_u_rhfolha_status!='FECHADA'){?>
                        <button id="cbNovo" type="button" class="btn btn-primary btn-xs" onclick="geranota(this,'CONSIGNADO')" title="Gerar nota">
                            <i class="fa fa-plus"></i>Novo
                        </button>
                    <?
                     }
                    }
                    ?>               
                </div>
                <? 
                }
                if($_1_u_rhfolha_tipofolha=='DECIMO TERCEIRO' or $_1_u_rhfolha_tipofolha=='DECIMO TERCEIRO 2' or $_1_u_rhfolha_tipofolha=='FOLHA'){
                    
                    if($_1_u_rhfolha_tipofolha=='DECIMO TERCEIRO' or $_1_u_rhfolha_tipofolha=='DECIMO TERCEIRO 2'){   
                ?>


                <div class="col-md-2">        
                                
                                 13 Salário <?                                       
                            $sqln="SELECT n.idnf,n.tipoorc,n.status,n.total,n.dtemissao,concat(e.sigla, ' - ',p.nome) as nome
                            from nf n join pessoa p on(p.idpessoa=n.idpessoa)
                            join empresa e on e.idempresa = p.idempresa
                            where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha."
                            and n.status !='CANCELADO'
                            and n.tipoobjetosolipor='rhfolha' and n.tipoorc='13SALARIO'";
                                    
                                $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                                $qtdn=mysqli_num_rows($resn);
                                if($qtdn>0){
                                    $rown=mysqli_fetch_assoc($resn);
                                    echo("R$:");  
                ?>
                                    <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>                    
                                    <?if($_1_u_rhfolha_status!='FECHADA'){?>
                                    <a title="Atualizar NF" class="fa fa-refresh btn-lg pointer azul" onclick="atualizarnf(this,<?=$rown['idnf']?>,'13SALARIO')"></a>
                <?
                                    }
                                }else{
                        ?>             
                        <?
                                if($_1_u_rhfolha_status!='FECHADA'){?>      
                                <button id="cbNovo" type="button" class="btn btn-primary btn-xs" onclick="geranota(this,'13SALARIO')" title="Novo item">
                                    <i class="fa fa-plus"></i>Novo
                                </button>  
                        <?
                                }
                            }
                        ?>
                                    
                </div>
<?
                    }
?>
                <div class="col-md-2">        
                                       
                     INSS  
        <?                                       
                $sqln="SELECT n.idnf,n.tipoorc,n.status,n.total,n.dtemissao,concat(e.sigla, ' - ',p.nome) as nome
                from nf n join pessoa p on(p.idpessoa=n.idpessoa)
                join empresa e on e.idempresa = p.idempresa
                where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha."
                and n.status !='CANCELADO'
                and n.tipoobjetosolipor='rhfolha' and n.tipoorc='INSS'";
                
                $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                $qtdn=mysqli_num_rows($resn);
                if($qtdn>0){
                    $rown=mysqli_fetch_assoc($resn);
                    echo("R$:");  
               ?>
                    <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>                    
                    <?if($_1_u_rhfolha_status!='FECHADA'){?>
                    <a title="Atualizar NF" class="fa fa-refresh btn-lg pointer azul" onclick="atualizarnf(this,<?=$rown['idnf']?>,'INSS')"></a>
               <?
                    }
                }else{
                    if($_1_u_rhfolha_status!='FECHADA'){
                ?>
                    <button id="cbNovo" type="button" class="btn btn-primary btn-xs" onclick="geranota(this,'INSS')" title="Gera nota">
                        <i class="fa fa-plus"></i>Novo
                    </button>
                <?
                    }
                }
                ?>               
                </div>
                <div class="col-md-2 nowrap">        
                     FGTS  
            <?                                       
                    $sqln="SELECT n.idnf,n.tipoorc,n.status,n.total,n.dtemissao,concat(e.sigla, ' - ',p.nome) as nome
                    from nf n join pessoa p on(p.idpessoa=n.idpessoa)
                    join empresa e on e.idempresa = p.idempresa
                    where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha."
                    and n.status !='CANCELADO'
                    and n.tipoobjetosolipor='rhfolha' and n.tipoorc='FGTS'";
                    
                    $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                    $qtdn=mysqli_num_rows($resn);
                    if($qtdn>0){
                        $rown=mysqli_fetch_assoc($resn);
                        echo("R$:");  
                    ?>
                            <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>                    
                            <?if($_1_u_rhfolha_status!='FECHADA'){?>
                            <a title="Atualizar NF" class="fa fa-refresh btn-lg pointer azul" onclick="atualizarnf(this,<?=$rown['idnf']?>,'FGTS')"></a>
                    <?  
                            }
                    }else{

                    ?>
                     <?if($_1_u_rhfolha_status!='FECHADA'){?>
                        <button id="cbNovo" type="button" class="btn btn-primary btn-xs" onclick="geranota(this,'FGTS')" title="Gera nota">
                            <i class="fa fa-plus"></i>Novo
                        </button>
                    <?
                     }
                    }
                    ?>               
                </div>
                <div class="col-md-2 nowrap">        
                     IRRF  
            <?                                       
                    $sqln="SELECT n.idnf,n.tipoorc,n.status,n.total,n.dtemissao,concat(e.sigla, ' - ',p.nome) as nome
                    from nf n join pessoa p on(p.idpessoa=n.idpessoa)
                    join empresa e on e.idempresa = p.idempresa
                    where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha."
                    and n.status !='CANCELADO'
                    and n.tipoobjetosolipor='rhfolha' and n.tipoorc='IRRF'";
                    
                    $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas IRRF');
                    $qtdn=mysqli_num_rows($resn);
                    if($qtdn>0){
                        $rown=mysqli_fetch_assoc($resn);
                        echo("R$:");  
                    ?>
                        <a title="NF IRRF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>                    
                        <?if($_1_u_rhfolha_status!='FECHADA'){?>
                        <a title="Atualizar NF IRRF" class="fa fa-refresh btn-lg pointer azul" onclick="atualizarnf(this,<?=$rown['idnf']?>,'IRRF')"></a>
                    <?  
                        }
                    }else{
                    ?>
                     <?if($_1_u_rhfolha_status!='FECHADA'){?>
                        <button id="cbNovo" type="button" class="btn btn-primary btn-xs" onclick="geranota(this,'IRRF')" title="Gera nota">
                            <i class="fa fa-plus"></i>Novo
                        </button>
                    <?  
                        }
                    }
                    ?>               
                </div>


                </div>
               <?
                 }
                
?>
               
               <div class="col-md-12 ">
<?
               if($_1_u_rhfolha_tipofolha=='FOLHA FERIAS'){
                    ?>  
                    <div class="panel panel-default row">  
                    <div class="panel-heading"> Férias:</div>    
                        <div class="panel-body">         
                            
                                          
                        <?      
                        
                        $sql1="SELECT i.inicio
                                    from rhfolhaitem i                         				
                                where i.idrhfolha=".$_1_u_rhfolha_idrhfolha." 
                                and i.inicio is not null                         
                                group by i.inicio";
                        $res1=d::b()->query($sql1) or die('Erro a buscar data com inicio');
                        while($row1=mysqli_fetch_assoc($res1)){
                            $sqln="  SELECT i.inicio,n.idnf,n.tipoorc,n.status,n.total,n.dtemissao
                            from rhfolhaitem i 
                            left join nf n on(n.idobjetosolipor=i.idrhfolha
                                            and n.status !='CANCELADO'
                                            and n.tipoobjetosolipor='rhfolha' and n.tipoorc='FERIAS'
                                            and exists(select 1 from nfitem ni where ni.idnf=n.idnf and i.idpessoa = ni.idpessoa)
                                            )						
                                   where   i.idrhfolha=".$_1_u_rhfolha_idrhfolha." and i.inicio ='".$row1['inicio']."'   
                            group by i.inicio";
                                
                            $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                            $qtdn=mysqli_num_rows($resn);
                            
                            if($qtdn>0){
                                while($rown=mysqli_fetch_assoc($resn)){
                                   if($rown['idnf']){
                                     echo("R$: ");
                                     ?>
                                        <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>   
                                       <?if($rown['status']!='CONCLUIDO'){?>
                                        <a title="Atualizar NF dos funcionarios desta data" class="fa fa-refresh btn-lg pointer azul" onclick="geranotaferias(this,'FERIAS','<?=$rown['inicio']?>')">&nbsp;&nbsp;<?=dma($rown['inicio'])?></a>                 
                                  <?
                                       }else{
                                        ?>
                                          &nbsp;&nbsp;<?=dma($rown['inicio'])?>
                                        <?
                                       }
                                   }else{
                                    ?>
                                            
                                    <a title="Gerar NF dos funcionarios nesta data" class="fa fa-plus-circle btn-lg pointer verde" onclick="geranotaferias(this,'FERIAS','<?=$rown['inicio']?>')">&nbsp;&nbsp;<?=dma($rown['inicio'])?></a>
                                    <?
                                   }

                                }
                            }
                            }
                    ?>
                       
                        </div>
                    </div>
                    <div class="panel panel-default row">          
                    <div class="panel-heading"> Decimo terceiro:</div>    
                        <div class="panel-body">  
                      
                            <?    
                            $sql1="SELECT i.inicio
                                            from rhfolhaitem i                         				
                                        where i.idrhfolha=".$_1_u_rhfolha_idrhfolha." 
                                        and i.inicio is not null                         
                                        group by i.inicio";
                            $res1=d::b()->query($sql1) or die('Erro a buscar data com inicio');
                        while($row1=mysqli_fetch_assoc($res1)){

                            $sqln="SELECT i.inicio,n.idnf,n.tipoorc,n.status,n.total,n.dtemissao, i.idpessoa 
                                        from rhfolhaitem i 
                                        join nf n on(n.idobjetosolipor=i.idrhfolha
                                                        and n.status !='CANCELADO'
                                                        and n.tipoobjetosolipor='rhfolha' and n.tipoorc='13SALARIO'
                                                        and exists(select 1 from nfitem ni where ni.idnf=n.idnf and i.idpessoa = ni.idpessoa)
                                                        
                                                        )						
                                        where i.idrhfolha=".$_1_u_rhfolha_idrhfolha." and i.inicio ='".$row1['inicio']."'                    
                                    group by i.inicio";

                            $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                            $qtdn=mysqli_num_rows($resn);
                            
                            if($qtdn>0){
                                while($rown=mysqli_fetch_assoc($resn)){
                                  
                                     echo("R$: ");
                                     ?>
                                        <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>   
                                       <?if($rown['status']!='CONCLUIDO'){?>
                                        <a title="Atualizar NF dos funcionarios desta data" class="fa fa-refresh btn-lg pointer azul" onclick="geranotaferias(this,'13SALARIO','<?=$rown['inicio']?>')">&nbsp;&nbsp;<?=dma($rown['inicio'])?></a>                 
                                  <?
                                       }
                                

                                }
                              
                            }else{
                                ?>
                                        
                                <a title="Gerar NF dos funcionarios nesta data" class="fa fa-plus-circle btn-lg pointer verde" onclick="geranotaferias(this,'13SALARIO','<?=$row1['inicio']?>')">&nbsp;&nbsp;<?=dma($row1['inicio'])?></a>
                                <?
                               }

                        }                            
                           
                
                                
                            
                    ?>
                        </div>
                    </div>
                    <div class="panel panel-default row">              
                    <div class="panel-heading"> IRRF:</div>    
                        
    <?
                        $slp="select i.idpessoa,concat(e.sigla, ' - ',ifnull(p.nomecurto,p.nome)) as nome 
                        from rhfolhaitem i join pessoa p on(p.idpessoa=i.idpessoa and p.contrato='CLT')
                        join empresa e on e.idempresa = p.idempresa
                        where i.idrhfolha = ".$_1_u_rhfolha_idrhfolha;
                        $relp=d::b()->query($slp);
                        while($rlpes=mysqli_fetch_assoc($relp)){
                            ?>
                             <div class="col-md-2 nowrap"> 
                            <?
                            echo($rlpes['nome']);
                            $sqln="SELECT i.inicio,n.idnf,n.tipoorc,n.status,n.total,n.dtemissao, i.idpessoa 
                                    from rhfolhaitem i 
                                    join nf n on(n.idobjetosolipor=i.idrhfolha
                                                    and n.status !='CANCELADO'
                                                    and n.tipoobjetosolipor='rhfolha' and n.tipoorc='IRRF'
                                                    and exists(select 1 from nfitem ni where ni.idnf=n.idnf and i.idpessoa = ni.idpessoa)
                                                    
                                                    )						
                                    where i.idrhfolha=".$_1_u_rhfolha_idrhfolha." and i.idpessoa ='".$rlpes['idpessoa']."'";

                            $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                            $qtdn=mysqli_num_rows($resn);
                            
                            if($qtdn>0){
                                $rown=mysqli_fetch_assoc($resn);
                                echo("R$:");  
            ?>
                                <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')"><?=$rown['total']?></a>                    
                                <?if($_1_u_rhfolha_status!='FECHADA'){?>
                                <a title="Atualizar NF" class="fa fa-refresh pointer azul" onclick="atualizarnf(this,<?=$rown['idnf']?>,'IRRF')"></a>
            <?
                                }
                            }else{
                    ?>            
                     <?         if($_1_u_rhfolha_status!='FECHADA'){?>       
                             <a title="Gerar NF IRRF <?=$rlpes['nome']?>" class="fa fa-plus-circle pointer verde" onclick="geranota_ferias(this,<?=$rlpes['idpessoa']?>,'IRRF')">&nbsp;&nbsp;Novo</a>
                    <?
                                }  
                            }
?>
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
        </div>


        <div class="col-md-12">
        <div class="panel panel-default">  
        <div class="panel-heading">Nota(s) Fiscal(is) Geradas CLT</div>    
            <div class="panel-body">    
            <?                
                    $sqln="SELECT n.idnf,n.tipoorc,n.status,n.total,n.dtemissao,concat(e.sigla,' - ',p.nome) as nome
                        from nf n join pessoa p on(p.idpessoa=n.idpessoa)
                        join empresa e on e.idempresa = p.idempresa
                        where n.idobjetosolipor=".$_1_u_rhfolha_idrhfolha."
                        and n.status !='CANCELADO'
                        and n.tipoobjetosolipor='rhfolha'";
                        
                    $resn=d::b()->query($sqln) or die('Erro a buscar notas geradas');
                    $qtdn=mysqli_num_rows($resn);
                    if($qtdn>0){
                ?>  
            <div class="col-md-12">
 
                    <table class="table table-striped planilha">
                    <tr>
                        <th>Emissão</th>
                        <th>Emitente</th>
                        <th>Tipo</th>                        
                        <th>Valor</th>                       
                        <th>Status</th>
                    </tr>
                        
                <?
                        while($rown=mysqli_fetch_assoc($resn)){
            ?>
                    <tr>
                        <td><?=dma($rown['dtemissao'])?></td>
                        <td><?=$rown['nome']?></td>
                        <td><?=$rown['tipoorc']?></td>                        
                        <td>
                        <a title="NF" class="pointer" onclick="janelamodal('?_modulo=comprasrh&_acao=u&idnf=<?=$rown['idnf']?>')">
                            <?=number_format(tratanumero($rown['total']), 2, ',', '.');?>
                        </a>
                        </td>
                       
                        <td><?=$rown['status']?></td>
                    </tr>
            <?
                        }
                ?>        
                    </table>

               </div>
               <div class="col-md-12">
               <hr>
               </div>
               <?
                    }                    
?>
            </div>
        </div>
        </div>

    </div>
    <?
if(!empty($_1_u_rhfolha_idrhfolha)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_rhfolha_idrhfolha; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "rhfolha"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<?
}//if(!empty($_1_u_rhfolha_idrhfolha)){

function getJFuncionario(){
	global $JSON, $_1_u_rhfolha_idrhfolha,$_1_u_rhfolha_idempresa;
	/*
    $s = "select 
				a.idpessoa
				,concat(e.sigla,' - ',ifnull(a.nomecurto,a.nome)) as nomecurto		
			from pessoa a
            join empresa e on a.idempresa = e.idempresa
			where
                a.status='ATIVO'
               and a.idempresa = ".$_1_u_rhfolha_idempresa."
                and a.idtipopessoa =1
				and not exists(
					SELECT 1
					FROM rhfolhaitem v
					where  v.idrhfolha=  ".$_1_u_rhfolha_idrhfolha." 
						and a.idpessoa = v.idpessoa				
				)
			union 
            select 
                p.idpessoa
                ,concat(e.sigla,' - ',ifnull(p.nomecurto,p.nome)) as nomecurto	
            from 
                    objempresa o 
                    join pessoa p on(p.idpessoa=o.idobjeto and p.status='ATIVO' and p.idtipopessoa =1)   
                    join empresa e on o.idempresa = e.idempresa
                where  o.objeto='pessoa' 
                and o.empresa = ".$_1_u_rhfolha_idempresa."
                and not exists(
                        SELECT 1
                        FROM rhfolhaitem v
                        where  v.idrhfolha=  ".$_1_u_rhfolha_idrhfolha." 
                            and p.idpessoa = v.idpessoa				
				)
			order by nomecurto desc ";
*/
            $s ="select 
                        a.idpessoa
                        ,concat(e.sigla,' - ',ifnull(a.nomecurto,a.nome)) as nomecurto		
                    from pessoa a
                    join empresa e on a.idempresa = e.idempresa
                    where
                        a.status in ('ATIVO','AFASTADO')
                        and a.idempresa = ".$_1_u_rhfolha_idempresa."
                        and a.idtipopessoa =1
                        and not exists(
                            SELECT 1
                            FROM rhfolhaitem v
                            where  v.idrhfolha=  ".$_1_u_rhfolha_idrhfolha." 
                                and a.idpessoa = v.idpessoa				
                        ) order by nomecurto desc";

	$rts = d::b()->query($s) or die("getJSetorvinc: ". mysqli_error(d::b()));

	$arrtmp=array();
	$i=0;
	while ($r = mysqli_fetch_assoc($rts)) {
	    $arrtmp[$i]["value"]=$r["idpessoa"];
	    $arrtmp[$i]["label"]= $r["nomecurto"];
	    $i++;
	}

	return $JSON->encode($arrtmp);    
}

$jFuncionario="null";

if(!empty($_1_u_rhfolha_idrhfolha)){
    $jFuncionario=getJFuncionario();
}
?>

<script>

jFuncionario = <?=$jFuncionario?>;
//Autocomplete de funcionarios vinculados
$("#addfuncionario").autocomplete({
	source: jFuncionario
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {

			lbItem = item.label;
			
			return $('<li>')
				.append('<a>' + lbItem + '</a>')
				.appendTo(ul);
		};
	}
	,select: function(event, ui){
		CB.post({
			objetos: {
				"_x_i_rhfolhaitem_idrhfolha":		$(":input[name=_1_"+CB.acao+"_rhfolha_idrhfolha]").val()
                ,"_x_i_rhfolhaitem_idpessoa":	ui.item.value
			}
			,parcial: true
		});
		  //AtualizaBim();
	}
});    
    
    
function abrirevento(inid){
    
 
    var str="_acao=u&idrhfolha="+$("[name=_1_u_rhfolha_idrhfolha]").val()+"&idrhevento="+inid
    
	CB.go(str);
}

function AlteraStatus(idrhtipoevento,datafim,situacao){
    var idrhfolha= $("[name=_1_u_rhfolha_idrhfolha]").val();
    var status= $("[name=_rhfolha_status]").val();
    CB.post({
        objetos: "_x_u_rhfolha_idrhfolha="+idrhfolha+"&_x_u_rhfolha_status="+status+"&rhfolha_idrhtipoevento="+idrhtipoevento+"&rhfolha_datafim="+datafim+"&rhfolha_situacao="+situacao
        ,parcial:true       
    });
}

function alterafolha(){
    var idrhfolha= $("[name=_1_u_rhfolha_idrhfolha]").val();
    var status= $("[name=_rhfolha_status]").val();
    var tipofolha= $("[name=_1_u_rhfolha_tipofolha]").val();
    
    CB.post({
        objetos: "_x_u_rhfolha_idrhfolha="+idrhfolha+"&_x_u_rhfolha_status="+status+"&_x_u_rhfolha_tipofolha="+tipofolha
        ,parcial:true       
    });
}

function AtualizaFolha(){
    $.ajax({
            type: "get",
            url : "inc/php/ajustaeventosrh.php",                           
            data: { idrhfolha : $("[name=_1_u_rhfolha_idrhfolha]").val() },
            success: function(data){
                    //alert('OK');
                     alertSalvo('Folha Atualizada');
                     document.location.reload(true);
            },

            error: function(objxmlreq){
                    alert('Erro:<br>'+objxmlreq.status); 
            }
    });
	
}
if(!$("#AtualizarFolha").length){
	$( "#cbSalvar" ).after( '<button id="AtualizarFolha" type="button" class="btn btn-info btn-xs" onclick=" AtualizaFolha()" title="Atualizar Folha"><i class="fa fa-refresh"></i>Atualizar Folha</button>' );
}

$(document).keyup(function(e) {
     if (e.key === "Escape") { // escape key maps to keycode `27`
        // <DO YOUR WORK HERE>
		$( "#AtualizarFolha" ).remove();
    }
});

function atualizavalor(idrhfolhaitem,vthis){
    CB.post({
	    objetos:  "_x_u_rhfolhaitem_idrhfolhaitem="+idrhfolhaitem+"&_x_u_rhfolhaitem_diastrab="+$(vthis).val()
	    ,parcial:true
	});
}

function ativainput(vthis,idrhtipoevento,idpessoa){
    $('#'+idrhtipoevento+"novo"+idpessoa).removeAttr("disabled");
    $('#'+idrhtipoevento+"novo"+idpessoa).removeClass("hidden");
    $(vthis).addClass("hidden");    
}

function gerarhevento(vthis){
   var idpessoa =  $(vthis).attr("idpessoa");
   var idrhtipoevento = $(vthis).attr("idrhtipoevento");
   var dataevento = $(vthis).attr("dtev");
   var valor = $(vthis).val();

     // $('#valor'+inev).val();
    CB.post({
	    objetos:  "_1_i_rhevento_dataevento="+dataevento+"&_1_i_rhevento_valor="+valor+"&_1_i_rhevento_idpessoa="+idpessoa+"&_1_i_rhevento_idrhtipoevento="+idrhtipoevento	    
        ,posPost: function(){
		// AtualizaFolha();
		}
    });
}

function geranota_ferias(vthis,$inidpessoa,intipo){
    var vidrhfolha= $("[name=_1_u_rhfolha_idrhfolha]").val();

    $(vthis).toggleClass('blink');

    $.ajax({
        type: "get",
        url : "ajax/geranotarh.php",                          
        data: { idrhfolha : vidrhfolha,tipo:intipo,idpessoa:$inidpessoa},
        success: function(data){
            if(data=='ok'){
                location.reload();
            }else{
                alertErro(data);
            }
        },
        error: function(objxmlreq){
            alert('Erro:<br>'+objxmlreq.status);
        }
    });
}

function geranota(vthis,intipo){

    var vidrhfolha= $("[name=_1_u_rhfolha_idrhfolha]").val();

    $(vthis).toggleClass('blink');

    $.ajax({
        type: "get",
        url : "ajax/geranotarh.php",                          
        data: { idrhfolha : vidrhfolha,tipo:intipo },
        success: function(data){
            if(data=='ok'){
                location.reload();
            }else{
                alertErro(data);
            }
        },
        error: function(objxmlreq){
            alert('Erro:<br>'+objxmlreq.status);
        }
    });
}

function geranotaferias(vthis,intipo,vinicio){

var vidrhfolha= $("[name=_1_u_rhfolha_idrhfolha]").val();

$(vthis).toggleClass('blink');

$.ajax({
    type: "get",
    url : "ajax/geranotarh.php",                          
    data: { idrhfolha : vidrhfolha,tipo:intipo,inicio:vinicio },
    success: function(data){
        if(data=='ok'){
            location.reload();
        }else{
            alertErro(data);
        }
    },
    error: function(objxmlreq){
        alert('Erro:<br>'+objxmlreq.status);
    }
});
}

function geranotapj(vthis,idpessoa){

    var vidrhfolha= $("[name=_1_u_rhfolha_idrhfolha]").val();

    $(vthis).toggleClass('blink');

    $.ajax({
        type: "get",
        url : "ajax/geranotarh.php",                          
        data: { idrhfolha : vidrhfolha,tipo:'PJ',idpessoa:idpessoa },
        success: function(data){
            if(data=='ok'){
                location.reload();
            }else{
                alertErro(data);
            }
        },
        error: function(objxmlreq){
            alert('Erro:<br>'+objxmlreq.status);
        }
    });
}

function atualizarnf(vthis,inidnf,intipo){
    
    var vidrhfolha= $("[name=_1_u_rhfolha_idrhfolha]").val();

    $(vthis).toggleClass('blink');

    $.ajax({
        type: "get",
        url : "ajax/geranotarh.php",                          
        data: { idrhfolha : vidrhfolha,tipo:intipo,idnf:inidnf,call:'atualizar' },
        success: function(data){
            if(data=='ok'){
                location.reload();
            }else{
                alert(data);
                location.reload();
            }
        },
        error: function(objxmlreq){
            alert('Erro:<br>'+objxmlreq.status);
        }
    });

}
function atualizarnfPJ(vthis,inidnf,inidpessoa,intipo){
    
    var vidrhfolha= $("[name=_1_u_rhfolha_idrhfolha]").val();

    $(vthis).toggleClass('blink');

    $.ajax({
        type: "get",
        url : "ajax/geranotarh.php",                          
        data: { idrhfolha : vidrhfolha,tipo:intipo,idnf:inidnf,idpessoa:inidpessoa,call:'atualizar' },
        success: function(data){
            if(data=='ok'){
                location.reload();
            }else{
                alert(data);
                location.reload();
            }
        },
        error: function(objxmlreq){
            alert('Erro:<br>'+objxmlreq.status);
        }
    });
}

function setanalisedt(inI,indate){ 
    //debugger;
    CB.post({
            objetos: "_x_u_rhfolhaitem_idrhfolhaitem="+inI+"&_x_u_rhfolhaitem_inicio="+indate,
            parcial:true
    });   
}

function rhfolhaconf(){
    debugger;
  
  
	htmlTrModelo =$("#fmrhtipoevento").html();
	/*
	
		htmlTrModelo = htmlTrModelo.replace("#name_campo", "_1_i_rhjustificativa_idpessoa");
        htmlTrModelo = htmlTrModelo.replace("#valor_campo", inidpessoa);
		htmlTrModelo = htmlTrModelo.replace("#name_justificativa", "_1_i_rhjustificativa_justificativa");
	
        htmlTrModelo = htmlTrModelo.replace("#name_data", "_1_i_rhjustificativa_dataevento");
		htmlTrModelo = htmlTrModelo.replace("#data_campo",  vdataevento);
*/
		var objfrm= $(htmlTrModelo);
		
		
		
		
		strCabecalho = "</strong>Ocultar/Listar Evento da Folha</strong>";

		CB.modal({
				titulo: strCabecalho,
				corpo: 	"<table>"+objfrm.html()+"</table>",
				classe: 'vinte',
		});


}

function ocultarevento(vthis,idrhtipoevento){
    debugger;
    cor =$(vthis).attr('cor');
   
    if(cor=='preto'){
       
        $('.icone'+idrhtipoevento).attr('cor','cinza');
        $('.icone'+idrhtipoevento).removeClass('preto');
        $('.icone'+idrhtipoevento).addClass('cinza');
        $('.trevento'+idrhtipoevento).addClass('hide');
        $('.icone'+idrhtipoevento).removeClass('fa-eye');
        $('.icone'+idrhtipoevento).addClass('fa-eye-slash');

      

        CB.post({
					objetos: '&_x_i_rhfolhaconf_idrhtipoevento='+idrhtipoevento+'&_x_i_rhfolhaconf_idrhfolha='+ $("[name=_1_u_rhfolha_idrhfolha]").val()
					,parcial:true
					,refresh: false
					,msgSalvo: "Coluna oculta"
					,posPost: function(data, textStatus, jqXHR){
						$('.icone'+idrhtipoevento).attr('idrhfolhaconf',CB.lastInsertId); 
					
					}
				});

    }else{
        $('.icone'+idrhtipoevento).attr('cor','preto');
        $('.icone'+idrhtipoevento).removeClass('cinza');
        $('.icone'+idrhtipoevento).addClass('preto');
        $('.trevento'+idrhtipoevento).removeClass('hide');
        $('.icone'+idrhtipoevento).addClass('fa-eye');
        $('.icone'+idrhtipoevento).removeClass('fa-eye-slash');
        CB.post({
					objetos: '&_x_d_rhfolhaconf_idrhfolhaconf='+$('.icone'+idrhtipoevento).attr('idrhfolhaconf')
					,parcial:true
					,refresh: false
					,msgSalvo: "Coluna amostra"
					
				});

    } 
    qtdoculto =$('#cbModalCorpo').find('.fa-eye-slash').length; 
    
    if(qtdoculto<1){
        $('#olhogeral').removeClass('fa-eye-slash');
        $('#olhogeral').removeClass('cinza');
        $('#olhogeral').addClass('fa-eye');
        $('#olhogeral').addClass('preto');

    }else{
        $('#olhogeral').removeClass('fa-eye');
        $('#olhogeral').removeClass('preto');
        $('#olhogeral').addClass('fa-eye-slash');
        $('#olhogeral').addClass('cinza');
    }

}

function listarocultartodos(vthis,vfuncao){    

    debugger;
    

    if(vfuncao=='listar'){
        var tabevento=$('.tabelaevento').find('.campoevento');    

        var obj2=$('#fmrhtipoevento').find('.fa-eye-slash').not('.listartodos');
        var obj=$('#cbModalCorpo').find('.fa-eye-slash').not('.listartodos');

        obj.removeClass('fa-eye-slash');
        obj.addClass('fa-eye');
        obj.removeClass('cinza');
        obj.addClass('preto');
        obj.attr('cor','preto');

        obj2.removeClass('fa-eye-slash');
        obj2.addClass('fa-eye');
        obj2.removeClass('cinza');
        obj2.addClass('preto');
        obj2.attr('cor','preto');

        tabevento.removeClass('hide');

        $('#olhogeral').removeClass('fa-eye-slash');
        $('#olhogeral').removeClass('cinza');
        $('#olhogeral').addClass('fa-eye');
        $('#olhogeral').addClass('preto');


        CB.post({
					objetos: '&_xx_u_rhfolha_idrhfolha='+ $("[name=_1_u_rhfolha_idrhfolha]").val()+"&listarocutar=listar"
					,parcial:true
					,refresh: false
					,msgSalvo: "Colunas amostra"
				});
      /*  
        obj2.each(function( index ) {
            console.log( index + ": " + $( this ).attr('idrhtipoevento') );
        });
        */

    }

}
function atualizaimp(vthis,inidrhfolha){

    if(confirm("Deseja realmente atualizar IRRF/INSS/FGTS?")){
        var vidrhfolha= $("[name=_1_u_rhfolha_idrhfolha]").val();
        debugger;
    
        $(vthis).toggleClass('atualizando');

        $.ajax({
            type: "get",
            url : "ajax/atualizaimprhfolha.php",                          
            data: { idrhfolha : vidrhfolha },
            success: function(data){
                if(data=='ok'){
                    location.reload();
                }else{
                    alertErro(data);
                }
            },
            error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status);
            }
        });

    }
}

</script>