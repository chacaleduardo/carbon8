<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$idcliente   	= $_GET["idpessoa"];
$idagencia = $_GET["idagencia"];

if(!empty($_GET["idcontapagarcp"]) and empty($_GET["idcontapagar"])){
    $_GET["idcontapagar"] = $_GET["idcontapagarcp"];
    $_GET["_acao"] ='u';
    $idcontapagarcp='Y';
}

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os par&acirc;metros GET que devem ser validados para compor o select principal
 *                pk: indica par&acirc;metro chave para o select inicial
 *                vnulo: indica par&acirc;metros secund&aacute;rios que devem somente ser validados se nulo ou n&atilde;o
 */
$pagvaltabela = "contapagar";
$pagvalcampos = array(
	"idcontapagar" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as vari&aacute;veis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from contapagar where idcontapagar = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das vari&aacute;veis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if($idcontapagarcp=='Y'){   
    $_acao='i';
    $_1_u_contapagar_status='PENDENTE';    
}

if( $_1_u_contapagar_saldook=='Y'){
	$disabled = "disabled='disabled' ";
	$readonly = "readonly='readonly'";
}elseif($_1_u_contapagar_status == "QUITADO"){
	$dtdisabled = " disabled='disabled' ";
	$mostracal='N';
}
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15){ 
    $disabled = "disabled='disabled' ";
    $readonly = "readonly='readonly'";
    $dtdisabled = " disabled='disabled' ";
}

if($_acao==u){
	$campdisabled = "disabled='disabled' ";
	$campreadonly = "readonly='readonly'";
}

function getClientesnf(){

    $sql= "SELECT
                p.idpessoa,
                p.nome,
                CASE p.idtipopessoa
		WHEN 1 THEN 'FUNCIONÁRIO'
		WHEN 2 THEN 'EMPRESA'
		WHEN 5 THEN 'FORNECEDOR'
		WHEN 6 THEN 'FABRICANTE'
		WHEN 7 THEN 'TERCEIRO'
		WHEN 9 THEN 'PRESTADOR'
		WHEN 11 THEN 'TRANSPORTADOR'
		WHEN 12 THEN 'REPRESENTANTE'
                END as tipo
        FROM pessoa p			
        WHERE p.status = 'ATIVO'
                AND p.idtipopessoa  in (1,2,5,6,7,9,11,12)
          ORDER BY p.nome";

    $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
        $arrret[$r["idpessoa"]]["tipo"]=$r["tipo"];
    }
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formaliza&ccedil;&atilde;o
$arrCli=getClientesnf();
//print_r($arrCli); die;
$jCli=$JSON->encode($arrCli);
?>

<style>
.respreto{
	font-size:11px;
}
.planilha td{
    font-size: 10px;
}
</style>
<script>
	
<?if($_1_u_contapagar_status=='QUITADO'){?> 
$("#cbModuloForm").find('input').not('[name*="contapagar_obs"],[name*="contapagar_idcontapagar"],[name*="contapagar_idpessoa"]').prop( "disabled", true );
$("#cbModuloForm").find("select" ).prop( "disabled", true );
$("#cbModuloForm").find("textarea").prop( "disabled", true );
<?}?>


</script>
<div class="row">
    <div class="col-md-8" >	
    <div class="panel panel-default" >
        <div class="panel-heading">Contas a Pagar / Receber</div>
        <div  class="panel-body">
	    <div class="row ">
                <div class="col-md-2">
                <?IF($idcontapagarcp=="Y"){?>  
                    <input  name="_1_<?=$_acao?>_contapagar_idcontapagar" type="hidden"	value=""	readonly='readonly'>
                    
                <?}else{?>
                    <input  name="contapagar_idtipopessoa" type="hidden" value="<?=$_SESSION["SESSAO"]["IDTIPOPESSOA"]?>">
                    <input  name="_1_<?=$_acao?>_contapagar_idcontapagar" type="hidden" value="<?=$_1_u_contapagar_idcontapagar?>">
                    <input  name="_1_<?=$_acao?>_contapagar_idobjeto" type="hidden" value="<?=$_1_u_contapagar_idobjeto?>">
                    <input  name="_1_<?=$_acao?>_contapagar_tipoobjeto" type="hidden" value="<?=$_1_u_contapagar_tipoobjeto?>">
                <?}?>    
                </div>
            </div> 
	    <?IF($_acao=='i' or !empty($_1_u_contapagar_tipoespecifico)){?>
	    <div class="row ">
                <div class="col-md-2">
                 Tipo:
                </div>
                <div class="col-md-6">
		    
		    <?if($_acao=='i'){?>
		    <select   name="_1_<?=$_acao?>_contapagar_tipoespecifico" <?=$disabled?> vnulo >
			<?fillselect("select 'NORMAL','Normal' union select 'AGRUPAMENTO','Agrupamento' union select 'REPRESENTACAO','Representa&ccedil;&atilde;o' ",$_1_u_contapagar_tipoespecifico);?>
		    </select>
		    <?}else{?>
		    <label class="idbox"><?=$_1_u_contapagar_tipoespecifico?></label>
		    <?}?>
                </div>
            </div>  
	    <?}?>
	
<?
/*
if(!empty($idcontapagar)){
		
		
		 *maf271010: permitir edicao mesmo que a descricao nao tenha sido informada
		

		if(!empty($_1_u_contapagar_idcontadesc)){
			$res = d::b()->query("SELECT * FROM contadesc WHERE idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." and idcontadesc = ".$_1_u_contapagar_idcontadesc." and status = 'ATIVO'") or die("Erro ao retornar desc: ".mysqli_error());

			while($r = mysqli_fetch_array($res)) {

				$iditem =  $r["idcontaitem"];      
	   			$descricao = $r["contadesc"];
	   			
			}
		}
}else{
	$iditem = "";      
	$descricao = "";
		
}*/
		
	if(!empty($_1_u_contapagar_idobjeto) and $_1_u_contapagar_tipoobjeto == "nf"){

			$sqlex = "select *
					from nf 
					where idnf =".$_1_u_contapagar_idobjeto;
			
			$qrex = d::b()->query($sqlex) or die("Erro ao buscar dados da nota:".mysqli_error());
			$rowr = mysqli_fetch_assoc($qrex);
			if($rowr["tiponf"]=='V'){ $vtiponf = "Venda";  $link="pedido";}
			if($rowr["tiponf"]=='C'){ $vtiponf = "Compra"; $link="nfentrada";}			
			if($rowr["tiponf"]=='S'){ $vtiponf = "Servi&ccedil;o";  $link="nfentrada";}
			if($rowr["tiponf"]=='T'){ $vtiponf = "Cte";  $link="nfentrada";}
			if($rowr["tiponf"]=='E'){ $vtiponf = "Consession&aacute;ria"; $link="nfentrada";}
			if($rowr["tiponf"]=='M'){ $vtiponf = "Manual/Cupom"; $link="nfentrada";}
                        if($rowr["tiponf"]=='R'){ $vtiponf = "PJ"; $link="comprasrh";}
			if($rowr["tiponf"]=='F'){ $vtiponf = "Fatura"; $link="nfentrada"; $tipo='F';}
?>		
            <div class="row ">
                <div class="col-md-2">
                   NF:
                </div>
                <div class="col-md-6"><?=$vtiponf?></div>
            </div>
            <div class="row ">
                <div class="col-md-2">
                  ID:
                </div>
                <div class="col-md-6">
                    <a class="pointer hoverazul" title="Nota Fiscal" onclick="janelamodal('?_modulo=<?=$link?>&_acao=u&idnf=<?=$_1_u_contapagar_idobjeto?>')"><?=$_1_u_contapagar_idobjeto?></a>
                </div>
            </div>
<?
	}elseif(!empty($_1_u_contapagar_idobjeto) and $_1_u_contapagar_tipoobjeto == "notafiscal"){
?>
            <div class="row ">
                <div class="col-md-2">
                  Tipo:
                </div>
                <div class="col-md-6">Venda</div>
            </div>
            <div class="row ">
                <div class="col-md-2">
                 ID:
                </div>
                <div class="col-md-6">
                <a class="pointer hoverazul" title="NFs" onclick="janelamodal('?_modulo=nfs&_acao=u&idnotafiscal=<?=$_1_u_contapagar_idobjeto?>')"><?=$_1_u_contapagar_idobjeto?></a>
                </div>
            </div>            
	
<?	
        }elseif($_1_u_contapagar_tipoespecifico=="REPRESENTACAO" and !empty($_1_u_contapagar_idpessoa)){
?>
            <div class="row ">
                <div class="col-md-2">
                 Gerar NF:
                </div>
                <div class="col-md-6">
                       <button id="" type="button" class="btn btn-success btn-xs" onclick="gerarnota(<?=$_1_u_contapagar_idcontapagar?>,<?=$_1_u_contapagar_idpessoa?>);">
                        <i class="fa fa-plus"></i>Novo
                        </button>
                </div>
            </div> 

<?            
        }
	
	if(!empty($_1_u_contapagar_idpessoa) or $_acao=='i'){
?>
	    <div class="row ">
                <div class="col-md-2">
                 Pessoa:
                </div>
                <div class="col-md-6">
		     
		    <?if($_1_u_contapagar_status=='QUITADO' OR $_1_u_contapagar_status=='INATIVO' ){?>
			<input id="idpessoa" name="_1_<?=$_acao?>_contapagar_idpessoa" type="hidden" cbvalue="<?=$_1_u_contapagar_idpessoa?>"	value="<?=$_1_u_contapagar_idpessoa?>">
		    <?
			ECHO traduzid("pessoa","idpessoa","nome",$_1_u_contapagar_idpessoa);
		    }else{?>
                        <input id="idpessoa" <?=$disabled?> type="text" name="_1_<?=$_acao?>_contapagar_idpessoa" cbvalue="<?=$_1_u_contapagar_idpessoa?>" value="<?=$arrCli[$_1_u_contapagar_idpessoa]["nome"]?>" style="width: 30em;" >
		    <?}?>               
                </div>
            </div> 
	    <?
	}
	 //if($_1_u_contapagar_tipo!='C'){
	    ?>
            <div class="row ">
                <div class="col-md-2">
                 Conta Item:
                </div>
                <div class="col-md-6">	    
		    <?
		    if(empty($_1_u_contapagar_idpessoa) and empty($_1_u_contapagar_idcontaitem)){
		    ?>	
			<select  <?=$disabled?>  name="_1_<?=$_acao?>_contapagar_idcontaitem" id="idcontaitem" vnulo>
				<option value=""></option>
			</select>
		    <?		
		    }elseif(!empty($_1_u_contapagar_idpessoa)){
		    ?>       
			<select <?=$disabled?>  name="_1_<?=$_acao?>_contapagar_idcontaitem" id="idcontaitem" vnulo>
			    <?fillselect("select c.idcontaitem,c.contaitem
					from pessoacontaitem i,contaitem c
					where c.idcontaitem = i.idcontaitem
					-- and c.status='ATIVO'					
					and i.idpessoa=".$_1_u_contapagar_idpessoa." order by c.contaitem",$_1_u_contapagar_idcontaitem);?>
			</select>	
		    <?
		    }elseif(!empty($_1_u_contapagar_idcontaitem)){
		?>       
			<select <?=$disabled?>  name="_1_<?=$_acao?>_contapagar_idcontaitem" id="idcontaitem" vnulo>
			    <?fillselect("select c.idcontaitem,c.contaitem
					from pessoacontaitem i,contaitem c
					where c.idcontaitem = i.idcontaitem
					-- and c.status='ATIVO'					
					 order by c.contaitem",$_1_u_contapagar_idcontaitem);?>
			</select>	
		    <?
		    }
		    ?>
                </div>
            </div> 
<?
	//}
	if($_1_u_contapagar_idcontadesc){
	    
?>
	     <div class="row ">
                <div class="col-md-2">
                 Conta Desc.:
                </div>
                <div class="col-md-6">
	    <?echo(traduzid('contadesc', 'idcontadesc', 'contadesc', $_1_u_contapagar_idcontadesc));?>
		</div>
            </div> 
    <?
	}
    ?>
          <div class="row ">
                <div class="col-md-2">
                 Observa&ccedil;&atilde;o:
                </div>
                <div class="col-md-10">
                  <input SIZE="60" <?=$readonly?>  name="_1_<?=$_acao?>_contapagar_obs" type="text" <?=$readonly?>
			autocomplete="off" value="<?=$_1_u_contapagar_obs?>">
                </div>
            </div>
            <div class="row ">
                <div class="col-md-2">
                 Valor:
                </div>
                <div class="col-md-4">
                  <input SIZE="10" <?=$readonly?> id="basicCalculator" name="_1_<?=$_acao?>_contapagar_valor" type="text" <?=$readonly?>
			autocomplete="off" value="<?=$_1_u_contapagar_valor?>">
                </div>
		<?if($_1_u_contapagar_tipoespecifico!='NORMAL' and !empty($_1_u_contapagar_idcontapagar)){
		    $sqlci="select sum(valor) as valori from contapagaritem where  status!='INATIVO' and idcontapagar=".$_1_u_contapagar_idcontapagar;
		    $resci=d::b()->query($sqlci) or die("Erro ao buscar valor da contapagaritem sql=".$sqlci);
		    $rowci= mysqli_fetch_assoc($resci);
		    if(tratanumero($_1_u_contapagar_valor)!=$rowci['valori']){
			$dif=$rowci['valori']- tratanumero($_1_u_contapagar_valor);
		?>
		<div class="col-md-2"><label class="idbox"><?=$dif?></label><i class="fa fa-exclamation-triangle laranja pointer" title="Valor dos itens difere da parcela!!!"></i></div>
		<?
		    }
		?>
		<?}?>
            </div>
	    <?
	    if($_1_u_contapagar_formapagto=="BOLETO" and $_1_u_contapagar_tipo=='C'){
	    ?>
	    <div class="row ">
                <div class="col-md-2">
                 <font color="red">Venc. Boleto:</font>
                </div>
                <div class="col-md-4 nowrap">
                 <input <?=$dtdisabled?> name="_1_<?=$_acao?>_contapagar_datapagto" size="8" class="calendario" id="vencimento_2" <?=$readonly?> <?=$dtdisabled?>
			value="<?=$_1_u_contapagar_datapagto?>"  vnulo> 
		</div>
            </div>
	    <?
	    }
	    ?>
            <div class="row ">
                <div class="col-md-2">
                 Recebimento:
                </div>
                <div class="col-md-4">
                 <input <?=$dtdisabled?> name="_1_<?=$_acao?>_contapagar_datareceb" size="8" class="calendario" id="vencimento_2" <?=$readonly?> <?=$dtdisabled?>
			value="<?=$_1_u_contapagar_datareceb?>"  vnulo> 
                </div>
            </div>
            
       
            <div class="row ">
                <div class="col-md-2">
                 Pagamento:
                </div>
                <div class="col-md-4">
                    <select <?=$disabled?> id="formapagto" name="_1_<?=$_acao?>_contapagar_formapagto">
                                   <option value=""></option>
                        <?fillselect("select 'BOLETO','Boleto' 
			    union select 'BOL AGRUPADO','Boleto Agrupado' 
			    union select 'C.CREDITO','Cart&atilde;o de Cr&eacute;dito'
			    union select 'C.DEBITO','Cart&atilde;o de D&eacute;bito'
			    union select 'CHEQUE','Cheque' 
			    union select 'DEPOSITO','Depósito'
			    union select 'TRANSFERENCIA','Transfer&ecirc;ncia'
			    ",$_1_u_contapagar_formapagto);?>		
                    </select>   
                </div>
            </div>
          

	<?
	
	
	if(!empty($_1_u_nf_idcartao) or $_1_u_nf_formapgto =="C.CREDITO" ){
	    $displaycartao='display: block;';
	}else{
	    $displaycartao='display: none;';
	}
?>
	 <div class="row ">
	     <div class="col-md-2" id="lbcartao" style="<?=$displaycartao?>"> Cart&atilde;o:</div> 	   
                <div class="col-md-4" id="cartao" style="<?=$displaycartao?>">		   
		<select id="idcartao" name="_1_<?=$_acao?>_contapagar_idcartao" vnulo>
		     <option value=""></option>
		    <?fillselect("select idcartao,cartao from cartao where status = 'ATIVO'",$_1_u_contapagar_idcartao);?>		
		</select>
		</div>
	</div>
	
<?
	 
	

if(empty($_1_u_contapagar_idformapagamento) and !empty($idformapagamento)){
	$_1_u_contapagar_idformapagamento = $idformapagamento;
}


?>	
            <div class="row ">
                <div class="col-md-2">
                    Forma Pagamento:
                </div>
                <div class="col-md-6">

                <select <?=$disabled?>  name="_1_<?=$_acao?>_contapagar_idformapagamento" vnulo>
                       <option></option>
                       <?fillselect("select idformapagamento,descricao 
                                       from formapagamento 
                                       where status='ATIVO'  order by descricao",$_1_u_contapagar_idformapagamento);?>		
                </select>
                </div>
            </div>
            <!--
            <div class="row ">
                <div class="col-md-2">
               Ag&ecirc;ncia:
                </div>
                <div class="col-md-6">
               	<select <?=$disabled?> name="_1_<?=$_acao?>_contapagar_idagencia"  id="idagencia"  <?=$disabled?> vnulo>
	       		<?fillselect("select idagencia,agencia from agencia where  status = 'ATIVO' order by ord",$_1_u_contapagar_idagencia);?>
                </select>     
                </div>
            </div>
            -->
            <div class="row ">
                <div class="col-md-2">
               Tipo:
                </div>
                <div class="col-md-4">
                <select  <?=$disabled?> name="_1_<?=$_acao?>_contapagar_tipo" id="tipo" <?=$disabled?>>
		<?
		fillselect("select 'D','D&eacute;bito' union select 'C','Cr&eacute;dito'",$_1_u_contapagar_tipo);
		?>
		</select>   
                </div>
            </div>

	
<?if(empty($_1_u_contapagar_parcela)){
$_1_u_contapagar_parcela = 1;
}?>	            
            <div class="row ">
                <div class="col-md-2">
               Parcelas:
                </div>
                <div class="col-md-4">
                 <?=$_1_u_contapagar_parcela?> de <input  name="_1_<?=$_acao?>_contapagar_parcela" <?=$readonly?>  <?=$campreadonly?>
			type="hidden" value="<?=$_1_u_contapagar_parcela?>">
			<select name="_1_<?=$_acao?>_contapagar_parcelas" id="status" <?=$disabled?> <?=$campdisabled?>>
		<?
			fillselect("select 1,'1x' union select 2,'2x' union select 3,'3x' union select 4,'4x' union select 5,'5x' union
			select 6,'6x' union select 7,'7x' union select 8,'8x' union select 9,'9x' union select 10,'10x' union
			select 11,'11x' union select 12,'12x' union select 13,'13x' union select 14,'14x' union select 15,'15x' union
			select 16,'16x' union select 17,'17x' union select 18,'18x' union select 19,'19x' union select 20,'20x' union
			select 21,'21x' union select 22,'22x' union select 23,'23x' union select 24,'24x' union select 25,'25x' union
			select 26,'26x' union select 27,'27x' union select 28,'28x' union select 29,'29x' union select 30,'30x' union
			select 31,'31x' union select 32,'32x' union select 33,'33x' union select 34,'34x' union select 35,'35x' union
			select 36,'36x' union select 37,'37x' union select 38,'38x' union select 39,'39x' union select 40,'40x' union
			select 41,'41x' union select 42,'42x' union select 43,'43x' union select 44,'44x' union select 45,'45x' union
			select 46,'46x' union select 47,'47x' union select 48,'48x' union select 49,'49x' union select 50,'50x' union
			select 51,'51x' union select 52,'52x' union select 53,'53x' union select 54,'54x' union select 55,'55x' union
			select 56,'56x' union select 57,'57x' union select 58,'58x' union select 59,'59x' union select 60,'60x'",$_1_u_contapagar_parcelas);
		?>
                    </select>  
                </div>
            </div>

<?
 	if(empty($_1_u_contapagar_intervalo)){
 		$_1_u_contapagar_intervalo = 30;
 	}
?>	
            <div class="row ">
                <div class="col-md-2">
               Tipo Intervalo:
                </div>
                <div class="col-md-4">
               <select <?=$disabled?> name="_1_<?=$_acao?>_contapagar_tipointervalo"  <?=$disabled?>>
			<?
			fillselect("select 'D','Dias' union select 'M','M&ecirc;s' union select 'Y','Ano'",$_1_u_contapagar_tipointervalo);
			?>
                </select>   
                </div>
            </div>
            <div class="row ">
                <div class="col-md-2">
               Intervalo:
                </div>
                <div class="col-md-4">
                <input SIZE="2" <?=$readonly?> name="_1_<?=$_acao?>_contapagar_intervalo" type="text" value="<?=$_1_u_contapagar_intervalo?>" <?=$readonly?> <?=$campreadonly?>>
                </div>
            </div>
            <div class="row ">
                <div class="col-md-2">
              Programado:
                </div>
                <div class="col-md-4">
                <select <?=$disabled?> name="_1_<?=$_acao?>_contapagar_progpagamento" id="status1" <?=$disabled?>>
			<?
			fillselect("select 'N','N&atilde;o' union select 'S','Sim'",$_1_u_contapagar_progpagamento);
			?>
                </select>
                </div>
            </div>
            <div class="row ">
                <div class="col-md-2">
                    Visualizar:
                </div>
                <div class="col-md-4">
                    <select <?=$disabled?> name="_1_<?=$_acao?>_contapagar_visivel" <?=$disabled?>>
                    <?
                    fillselect("select 'N','N&atilde;o' union select 'S','Sim'",$_1_u_contapagar_visivel);
                    ?>
                    </select>
                </div>
            </div>
            <div class="row ">
                <div class="col-md-2">
                    Status:
                </div>
                <div class="col-md-4">
                   <?
                if($_1_u_contapagar_status == "QUITADO"){
                        $sqlstatus="select 'PENDENTE','Pendente' union select 'QUITADO','Quitado'";
                }elseif($_1_u_contapagar_status == "PENDENTE"){
                        $sqlstatus="select 'PENDENTE','Pendente' union select 'ABERTO','Aberto'  union select 'INATIVO','Inativo'";
                }elseif($_1_u_contapagar_status == "INATIVO"){
                        $sqlstatus="select 'INATIVO','Inativo' union select 'PENDENTE','Pendente'";
                }elseif($_1_u_contapagar_status == "ABERTO"){
                        $sqlstatus="select 'PENDENTE','Pendente' union select 'ABERTO','Aberto' union select 'INATIVO','Inativo'";
                }elseif(empty($_1_u_contapagar_status)){
                        $sqlstatus="select 'PENDENTE','Pendente' union select 'ABERTO','Aberto'  ";
                }
                ?>		
		<select <?=$disabled?> name="_1_<?=$_acao?>_contapagar_status" id="status1" >
		<?
		fillselect($sqlstatus,$_1_u_contapagar_status);
		?>
		</select>
            <?
            //}
            ?>		
                </div>
            </div>	 

	

        </div>
    </div>
<?
	if(!empty($_1_u_contapagar_idcontapagar)){
	 
	    if($_1_u_contapagar_tipoespecifico=='REPRESENTACAO'){
		$linknf="pedido";
		$sqlp ="select c.idcontapagaritem,c.idcontapagar,c.status,c.valor,cli.nome,n.dtemissao,n.formapgto,n.nnfe,n.idnf
			 from contapagaritem c,pessoa p, contapagar cp,nf n,pessoa cli
			 where c.idcontapagar =".$_1_u_contapagar_idcontapagar."
			    and n.idnf =cp.idobjeto
			    and cli.idpessoa = n.idpessoa
			    and cp.tipoobjeto = 'nf'
			    and cp.idcontapagar = c.idobjetoorigem
                            and c.status!='INATIVO'
			    and c.tipoobjetoorigem  = 'contapagar'
			   
			    and c.idpessoa = p.idpessoa order by n.dtemissao";
	    }else{
		$linknf="nfentrada";
		$sqlp ="select c.idcontapagaritem,c.idcontapagar,c.status,c.valor,p.nome,n.dtemissao,n.formapgto,n.nnfe,n.idnf,ca.cartao
			from pessoa p,contapagaritem c , nf n left join cartao ca on(n.idcartao= ca.idcartao)
			where c.idcontapagar =".$_1_u_contapagar_idcontapagar."	
			    and n.idnf= c.idobjetoorigem 
			    and c.tipoobjetoorigem  = 'nf'
                            and c.status!='INATIVO'			   
			    and n.idpessoa = p.idpessoa  order by n.dtemissao";
	    }	
	
	
	 $resp=d::b()->query($sqlp) or die("Erro ao buscar outras parcelas de comissao sql=".$sqlp);
	 $qtdp=mysqli_num_rows($resp);
         $i=1;
            if($qtdp>0){
	 ?>
	
        
        <div class="panel panel-default" >
        <div class="panel-heading"><input placeholder="Parcelas Agrupadas" type="text" class="size20" onkeyup="findcontaitem(this)"> <i class="fa fa-search azul"></i></div>
        <div  class="panel-body">
            <table class="table table-striped planilha">
                <tr class="header">
		    <th>Danfe - NNFe</th>
		    <th>Emiss&atilde;o</th>		    
		    <th>Nome</th>
		    <th>Forma Pagto</th>
		    <th>Status</th>		    
		    <th>Valor</th> 
                    <?if($_1_u_contapagar_status =='ABERTO'  and $_1_u_contapagar_tipoespecifico =='REPRESENTACAO'){?>
                    <th>Conta Vinc.</th>
                    <?}?>
                </tr>
	<?
		$valor=0;			
	    while($rowp=mysqli_fetch_assoc($resp)){
		$i=$i+1;
		$valor=$valor+$rowp['valor'];
		if(empty($rowp['nnfe'])){
		    $nnfe=$rowp['idnf'];
		}else{
		    $nnfe=$rowp['nnfe'];
		}
	?>				
		<tr class="respreto">   
		    
		    <td class="col-md-1">
			<a class="fa fa-print fa-1x pointer hoverazul" title="Danfe" onclick="janelamodal('../inc/nfe/sefaz3/func/printDANFE.php?idnotafiscal=<?=$rowp['idnf']?>')"></a>
			-
			<a class="pointer" onclick="janelamodal('?_modulo=<?=$linknf?>&_acao=u&idnf=<?=$rowp['idnf']?>');">
			<?=$nnfe?>
			</a>			
		    </td>
		     <td class="col-md-2"><?=dma($rowp['dtemissao'])?></td>
		    <td class="col-md-3"><?=$rowp['nome']?></td>	
		     <td class="col-md-3"><?//=$rowp['formapgto']?> <?=$rowp['cartao']?></td>
		    <td class="col-md-2"><?=$rowp['status']?></td>		    
		    <td align="right" class="col-md-1"><?=$rowp['valor']?></td>
		   
		    <td>
			<?if($_1_u_contapagar_status =='ABERTO'  and $_1_u_contapagar_tipoespecifico !='REPRESENTACAO'){?>
                            <i class="fa fa-trash cinzaclaro hoververmelho btn-lg pointer" idcontapagaritem="<?=$rowp['idcontapagaritem']?>" onclick="altcheck(this,'D',<?=$_1_u_contapagar_idcontapagar?>)" title="Retirar da Conta"></i>
			<?}elseif($_1_u_contapagar_status =='ABERTO'  and $_1_u_contapagar_tipoespecifico =='REPRESENTACAO'){?>
                            
                       		<select  name="contapagaritem_idcontapagar" onchange="atualizacontaitem(this,<?=$rowp['idcontapagaritem']?>)">
                                <?
                                fillselect("select idcontapagar, dma(datareceb) as dtreceb
                                    from contapagar 
                                    where tipoespecifico ='REPRESENTACAO' 
                                    and status='ABERTO' 
                                    and idpessoa=".$_1_u_contapagar_idpessoa." order by datareceb",$rowp['idcontapagar']);
                                ?>
                                </select> 
                       <?}?>
		    </td>
		</tr>
	<?
	    }//while($rowp=mysqli_fetch_assoc($resp)){				
	?>	
		<tr>
		    <td colspan="5"></td>
		    <th align="right"><?=$valor?></th>
		</tr>
	    </table>
        </div>
        </div>
        
 
	<?
            }//if($qtdp>0){
	}//if(!empty($_1_u_contapagar_idcontapagar)){
	?>
	
    </div>
    <div class="col-md-4" >
	<div class="panel panel-default" >
	    <div class="panel-heading"> <input placeholder="Parcelas Para Serem Agrupadas" type="text" class="size20" onkeyup="findcontaitem(this)"> <i class="fa fa-search azul"></i></div>
        <div  class="panel-body">
	<?IF($_1_u_contapagar_tipoespecifico=="AGRUPAMENTO" and $_1_u_contapagar_status == 'ABERTO' and !empty($_1_u_contapagar_idagencia)){
	   // if($tipo!='F'){
	//	$sql = "SELECT * FROM vwcontapagaritem where idpessoa=".$_1_u_contapagar_idpessoa." and (idcontapagar =0 or idcontapagar is null) and  idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." order by datapagto asc";
	   // }else{
		$sql ="SELECT * FROM (
				    SELECT i.idcontapagaritem,i.idcontapagar,n.idnf,n.idnf as idobjetoorigem,n.nnfe,i.valor,n.dtemissao,p.nome, n.formapgto,ca.cartao
					FROM 
					    contapagaritem i					    
					    JOIN pessoa p
					    JOIN nf n left join cartao ca on (n.idcartao = ca.idcartao)
					WHERE i.idobjetoorigem = n.idnf
					    and p.idpessoa = n.idpessoa
					    and i.tipoobjetoorigem = 'nf'
					    and i.status = 'ABERTO'
					    -- and n.tiponf !='T'
					    and i.idagencia =".$_1_u_contapagar_idagencia."
					    and n.formapgto = 'C.CREDITO' 
				    UNION             
					SELECT 
					   i.idcontapagaritem,i.idcontapagar,n.idnf,n.idnf as idobjetoorigem,n.nnfe,i.valor,n.dtemissao,p.nome, n.formapgto,ca.cartao
					FROM
					    contapagaritem i
					    JOIN pessoa p
					    JOIN nf n left join cartao ca on (n.idcartao = ca.idcartao)
					WHERE i.idobjetoorigem = n.idnf
					    and i.tipoobjetoorigem = 'nf'
                                            and i.status != 'INATIVO'
					    and n.idpessoa = p.idpessoa
					    and i.idagencia =".$_1_u_contapagar_idagencia."
					    and p.idpessoa=".$_1_u_contapagar_idpessoa."
					    and (idcontapagar =0 or idcontapagar is null) 					   
			)as u order by u.dtemissao";
	   // }
	    echo "<!--";
	    echo $sql;
	    echo "-->";
	    if (!empty($sql)){

		$res = d::b()->query($sql) or die("Falha ao pesquisar contasITEM: " . mysqli_error(d::b()) . "<p>SQL: $sql");
		$ires = mysqli_num_rows($res);
	    }
	    
	?>
      
	<?
		if($ires>0){
	?>
	   <table class="table table-striped planilha">
	    <tr class="header">			
		<th>NFe</th>
		<th>Emiss&atilde;o</th>				
		<th>Nome</th>
		<th>Forma Pagto.</th>
		<th>Valor</th>	
		<th></th>		
	    </tr>
		<?
		$valor=0;
		    while ($row = mysqli_fetch_array($res)){
			$valor=$valor+$row['valor'];
			if(empty($row['nnfe'])){
			    $nnfe=$row['idnf'];
			}else{
			    $nnfe=$row['nnfe'];
			}	    
?>
	<tr class="header" id="<?=$row['idnf']?>">
	   	   
	    <td align="center" class="col-md-1">
		<a class="pointer" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$row['idobjetoorigem']?>');">
		<?=$nnfe?>
		</a>	
	    </td>
	    <td align="center" class="col-md-2"><?=dma($row['dtemissao'])?></td>
	   
	    <td align="center" class="col-md-3"><?=$row['nome']?></td>
	    <td align="center" class="col-md-3"><?//$row['formapgto']?><?=$row['cartao']?></td>
	    <td align="center" class="col-md-2"><?=$row['valor']?></td>
	    <td class="col-md-1">
		<input title="Adicionar a conta" type="checkbox" <?=$checked?> name="namenfec" idcontapagaritem="<?=$row['idcontapagaritem']?>" onclick="altcheckR(this,'U',<?=$_1_u_contapagar_idcontapagar?>)"> 
	    </td>
	</tr>

<? 
		    }//while ($row = mysqli_fetch_array($res)){
?>		<tr>
		    <td colspan="4"></td>
		    <th><?=$valor?></th>
		</tr>
	</table>
	<?
		}

		
	}
	?>
	</div>
	</div>
    </div>
   
</div>





	<?
	if(!empty($_1_u_contapagar_idobjeto) and !empty($_1_u_contapagar_tipoobjeto)){
	 $sqlp="select dma(datareceb) as dmadatareceb,datareceb,valor,idcontapagar,tipo,status,parcela,parcelas from contapagar where idcontapagar!=".$_1_u_contapagar_idcontapagar." and idobjeto = ".$_1_u_contapagar_idobjeto." and tipoobjeto ='".$_1_u_contapagar_tipoobjeto."'";
	 $resp=d::b()->query($sqlp) or die("Erro ao buscar outras parcelas sql=".$sqlp);
	 $qtdp=mysqli_num_rows($resp);
	 ?>
	<div class="row ">
        <div class="col-md-12" >
        <div class="panel panel-default" >
        <div class="panel-heading">Parcelas</div>
        <div  class="panel-body">
            <table class="table table-striped planilha">
                <tr class="header">
                        <th>Id</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th colspan="2">Parcela</th>

                        <th>Tipo</th>
                        <th>Status</th>
                </tr>
	<?
					
				while($rowp=mysqli_fetch_assoc($resp)){
					$i=$i+1;
	?>				
					<tr class="respreto">
						<td>
						<input  name="_<?=$i?>_u_contapagar_idcontapagar" type="hidden" value="<?=$rowp['idcontapagar']?>">
                                                
						<a class="fa pointer hoverazul" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$rowp["idcontapagar"]?>')" ><?=$rowp['idcontapagar']?></a></td>
						<td>
						<?if($rowp['status']=='PENDENTE'){?>
						<input  name="_<?=$i?>_u_contapagar_valor" type="text" size="5" value="<?=$rowp['valor']?>">
						<?}else{?>
						<?=$rowp['valor']?>
						<?}?>
						</td>
						<td>
						<?if($rowp['status']=='PENDENTE'){?>
							<input  name="_<?=$i?>_u_contapagar_datareceb" type="text" class="datad" size="8" value="<?=$rowp['dmadatareceb']?>">
						<?}else{?>
						<?=$rowp['dmadatareceb']?>
						<?}?>
						</td>
						<td><?=$rowp['parcela']?></td>
						<td><?=$rowp['parcelas']?></td>
						<td><?=$rowp['tipo']?></td>
						<td><?=$rowp['status']?></td>
					</tr>
	<?
				}
				$parcela=$_1_u_contapagar_parcelas+1;
				$parcelas=$_1_u_contapagar_parcelas+1;
	?>
					<tr class="respreto">
						<td title="NOVA PARCELA!"><font color="green">Nova:</font></td>
						<td  title="Digite o valor da nova parcela!">
                                                    <input name="contapagarvalor" style="width: 80px" type="text" placeholder="0.00" size="5" value="">
						</td>
                                                <td><a class="fa fa-plus-circle verde pointer hoverazul" title="Criar nova parcela!" onclick="geraparcela('<?=$_1_u_contapagar_datapagto?>','<?=$_1_u_contapagar_datareceb?>','<?=$_1_u_contapagar_intervalo?>',<?=$parcela?>,<?=$parcelas?>)"></a></td>
					</tr>
					
					
				</table>
        </div>
        </div>
        </div>
        </div>
	<?
	}
	?>
<?
    if(!empty($_1_u_contapagar_idcontapagar)){
	$sql="select i.idremessaitem,
		    i.idremessa,
		    i.idcontapagar,
		    i.status as remessa,
		    r.dataenvio,
		    r.status
		from remessaitem i,remessa r 
		where i.idremessa = r.idremessa 
		and i.idcontapagar =".$_1_u_contapagar_idcontapagar;
	$res=d::b()->query($sql) or die("Erro ao buscar remessa sql=".$sql);
	$qtd=mysqli_num_rows($res);
	if($qtd>0){
?> 
    <div class="row ">
        <div class="col-md-12" >
        <div class="panel panel-default" >
        <div class="panel-heading">Remessa/Boleto</div>
        <div  class="panel-body">
            <table class="table table-striped planilha">
                <tr>
		    <th>ID</th>
		    <th>Status</th>
		    <th>Remessa Item</th>
		    <th>Boleto</th>
                </tr>
<?					
	    while($row=mysqli_fetch_assoc($res)){
		$i=$i+1;
?>
		<tr>
		    <td>
			<a  class="pointer hoverazul" title="Parcela" onclick="janelamodal('?_modulo=remessa&_acao=u&idremessa=<?=$row['idremessa']?>')">
			<?=$row['idremessa']?>
			</a>
		    </td>
		    <td><?=$row['status']?></td>
		    <td>
			<input  name="_<?=$i?>_u_remessaitem_idremessaitem" type="hidden" value="<?=$row['idremessaitem']?>">
			<select name="_<?=$i?>_u_remessaitem_status"  >
			<?
			fillselect("select 'P','Pendente' 
				    union select 'E','Erro'
				    union select 'C','Concluido' 
				    union select 'A','Alterado'",$row['remessa']);
			?>
			</select>
		    </td>
		    <TD>
			 <a class="fa fa-wpforms pointer hoverazul btn-lg pointer" title="Boleto" onclick="janelamodal('inc/boletophp/boleto_itau.php?idcontapagar=<?=$row['idcontapagar']?>')"></a>
		    </td>
		</tr>
<?
	    }
?>
	    </table>
	</div>
	</div>
	</div>
    </div>

<?    
	}
?>
<div class="row ">
      <div class="panel panel-default">
       <div class="panel-heading">Arquivos Anexos</div>
       <div class="panel-body">
	    <div class="cbupload" title="Clique ou arraste arquivos para c&aacute;" style="width:50%;height:100%;">
		    <i class="fa fa-cloud-upload fonte18"></i>
	    </div>
	</div> 
      </div>
</div>
<?
        $sql = "select p.idpessoa
                    ,p.nome 
                ,CASE
                    WHEN c.status ='ATIVO' THEN dma(c.alteradoem)
                    ELSE ''
                END as dataassinatura 
                ,CASE
                    WHEN c.status ='ATIVO' THEN 'ASSINADO'
                    ELSE 'PENDENTE'
                END as status
                from carrimbo c ,pessoa p 
                where c.idpessoa = p.idpessoa
                and c.status IN ('ATIVO','PENDENTE')
                and c.tipoobjeto IN ('contapagar','contapagaritem')
                and c.idobjeto =".$_1_u_contapagar_idcontapagar."  order by nome";

        $res = d::b()->query($sql) or die("A Consulta de assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql); 
        $existe = mysqli_num_rows($res);
        if($existe>0){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Assinaturas</div>
        <div class="panel-body">
            <table class="planilha grade compacto">
               <tr>
                    <th >Funcion&aacute;rios</th>
                    <th >Data Assinatura</th>
                    <th >Status</th>	
                </tr>			
<?			
        while($row = mysqli_fetch_assoc($res)){			
?>	
                <tr class="res">
                    <td nowrap><?=$row["nome"]?></td>
                    <td nowrap><?=$row["dataassinatura"]?></td>
                    <td nowrap><?=$row["status"]?></td>
                </tr>				
<?							
        }
?>	
            </table>
        </div>
    </div>
    </div>
</div>
<?
        }//if($existe>0){ 

    }
?>

<?
if(!empty($_1_u_contapagar_idcontapagar)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_contapagar_idcontapagar; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "contapagar"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

<script>
    
    
jCli=<?=$jCli?>;// autocomplete cliente

//mapear autocomplete de clientes
jCli = jQuery.map(jCli, function(o, id) {
    return {"label": o.nome, value:id+"" ,"tipo":o.tipo}
});
   
    if($("[name=contapagar_idtipopessoa]").val()==12 ){
	document.getElementById("cbSalvar").style.display="none";
    }
//autocomplete de clientes
$("[name*=_contapagar_idpessoa]").autocomplete({
    source: jCli
    ,delay: 0
    ,select: function(event, ui){
        preenchecontaitem(ui.item.value);		
    },create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.tipo+"</span></a>").appendTo(ul);
        };
    }	
});
// FIM autocomplete cliente

function geraparcela(datapagto,datareceb,intervalo,parcela,parcelas){

    var str2="";
    var str3="";
    if ($("[name=_1_u_contapagar_idcontadesc]").val() != null && $("[name=_1_u_contapagar_idcontadesc]").val() !== 'undefined') {
        str2="_x_i_contapagar_idcontadesc="+$("[name=_1_u_contapagar_idcontadesc]").val();
    }
    if ($("[name=_1_u_contapagar_idcontaitem]").val() != null && $("[name=_1_u_contapagar_idcontaitem]").val() !== 'undefined') {
        str3="_x_i_contapagar_idcontaitem="+$("[name=_1_u_contapagar_idcontaitem]").val();
    }
   if($("#idpessoa").attr('cbvalue')!= null && $("#idpessoa").attr('cbvalue') !== 'undefined'){
        str4="&_x_i_contapagar_idpessoa="+$("#idpessoa").attr('cbvalue');
   }
    
     var str1="&_x_i_contapagar_idformapagamento="+$("[name=_1_u_contapagar_idformapagamento]").val()+
             "&_x_i_contapagar_tipoobjeto="+$("[name=_1_u_contapagar_tipoobjeto]").val()+
             "&_x_i_contapagar_idobjeto="+$("[name=_1_u_contapagar_idobjeto]").val()+
             ""+str4+"&_x_i_contapagar_parcela="+parcela+
             "&_x_i_contapagar_parcelas="+parcelas+
             "&_x_i_contapagar_valor="+$("[name=contapagarvalor]").val()+
             "&_x_i_contapagar_intervalo="+intervalo+
            "&_x_i_contapagar_datapagto="+datapagto+
            "&_x_i_contapagar_datareceb="+datareceb+
            "&_x_i_contapagar_status=PENDENTE&_x_i_contapagar_formapagto="+$("[name=_1_u_contapagar_formapagto]").val()+
            "&_x_i_contapagar_tipo="+$("[name=_1_u_contapagar_tipo]").val();
    

    var str = str1.concat(str2).concat(str3);

    CB.post({
      objetos: str
    });
}
$().ready(function() {
    $("#formapagto").change(function(){
	if($("#formapagto").val()=="C.CREDITO"){
	    $("#lbcartao").show();
	    $("#cartao").show();
	} else{
	    $("#lbcartao").hide();
	    $("#cartao").hide();
	}
    });
 
});
/*
$().ready(function() {
	 $("#idcontaitem").change(function(){
		$("#idcontadesc").html("<option value='sda'>Procurando....</option>");
		
		$.post("ajax/dropdesc.php", 
			{ idcontaitem : $("#idcontaitem").val() }, 
			function(resposta){
				$("#idcontadesc").html(resposta);
			}
		);
	}
	);
});
*/
function preenchecontaitem(inidpessoa){	
    vIdPessoa = $(":input[name=_1_"+CB.acao+"_contapagar_idpessoa]").cbval();

    if(vIdPessoa){
        $("#idcontaitem").html("<option value=''>Procurando....</option>");
        //alert($("#idpessoa").val());	
        $.ajax({
            type: "get",
            url : "ajax/dropdesc.php?idpessoa="+vIdPessoa,
            success: function(data){
                $("#idcontaitem").html(data);
            },
            error: function(objxmlreq){
                alert('Erro:<br>'+objxmlreq.status); 
            }
        })//$.ajax

    }else{
        console.warn("js: preencheendereco: Erro: idIdpessoa n&atilde;o informado;")
    }  
}//function preencheendereco(){

function altcheckR(vthis,vop,inidcontapagar){ 
     
     vthis.disabled=true;
     if(vop=='D'){
         var str ="_x_u_contapagaritem_idcontapagaritem="+$(vthis).attr('idcontapagaritem')+"&_x_u_contapagaritem_idcontapagar=0&_x_u_contapagaritem_status=ABERTO";
     }else{     
        var str ="_x_u_contapagaritem_idcontapagaritem="+$(vthis).attr('idcontapagaritem')+"&_x_u_contapagaritem_idcontapagar="+inidcontapagar+"&_x_u_contapagaritem_status=PENDENTE";
     }
     
    CB.post({
        objetos: str 
        ,refresh: false	
    }); 
}

function altcheck(vthis,vop,inidcontapagar){ 
     
    
     if(vop=='D'){
         var str ="_x_u_contapagaritem_idcontapagaritem="+$(vthis).attr('idcontapagaritem')+"&_x_u_contapagaritem_idcontapagar=0&_x_u_contapagaritem_status=ABERTO";
     }else{     
        var str ="_x_u_contapagaritem_idcontapagaritem="+$(vthis).attr('idcontapagaritem')+"&_x_u_contapagaritem_idcontapagar="+inidcontapagar+"&_x_u_contapagaritem_status=PENDENTE";
     }
     
    CB.post({
        objetos: str 
    }); 
}

function findcontaitem(vthis){
    
    
    var insstr=$(vthis).val();
    if(insstr!='' && insstr.length > 0){
	vtr= $(vthis).parent().parent().children().children().children().children();
	var instrucase = insstr.toUpperCase();//transform parameter to upper case
	for (var i=0; i<vtr.length; i++){			
	    var contentucase = vtr[i].textContent.toUpperCase();//transform UL textcontent to upper case
	    //console.log(contentucase.indexOf(instrucase));
	    if(contentucase.indexOf(instrucase)>=0){
		    vtr[i].style.display="table";
			vtr[i].style.width="100%";
	    }else{
		    vtr[i].style.display="none";
	    }
	}
    }else{
	vtr= $(vthis).parent().parent().children().children().children().children();
	for (var i=0; i<vtr.length; i++){			

		vtr[i].style.display="table";
		vtr[i].style.width="100%";
	} 
	
    }
}

if( $("[name=_1_u_contapagar_idcontapagar]").val() ){
	$(".cbupload").dropzone({
		idObjeto: $("[name=_1_u_contapagar_idcontapagar]").val()
		,tipoObjeto: 'contapagar'
	});
}
<?
if(!empty($_1_u_contapagar_idcontapagar)){
    $sqla="select * from carrimbo 
	    where status='PENDENTE' 
	    and idobjeto = ".$_1_u_contapagar_idcontapagar." 
	    and tipoobjeto in ('contapagar','contapagaritem')
	    and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
    $resa=d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
    $qtda= mysqli_num_rows($resa);
    if($qtda>0){
	$rowa=mysqli_fetch_assoc($resa);

?>    
        botaoAssinar(<?=$rowa['idcarrimbo']?>);  
<?  }// if($qtda>0){
}//if(!empty($_1_u_sgdoc_idsgdoc)){
?>
function botaoAssinar(inidcarrimbo){
    $bteditar = $("#btAssina");
    if($bteditar.length==0){
	CB.novoBotaoUsuario({
	    id:"btAssina"
	    ,rotulo:"Assinar"
	    ,class:"verde"
	    ,icone:"fa fa-pencil"
	    ,onclick:function(){
                CB.post({
		    objetos: "_x_u_carrimbo_idcarrimbo="+inidcarrimbo+"&_x_u_carrimbo_status=ATIVO"
		    ,parcial:true  
                    ,posPost: function(data, textStatus, jqXHR){
                        $('#btAssina').hide(); 
                    }
		});
	    }
	});
    }
}

function atualizacontaitem(vthis,inidcontapagaritem){
    if(confirm('Deseja alterar item de conta?')) {
        CB.post({
            objetos: `_x_u_contapagaritem_idcontapagaritem=`+inidcontapagaritem+`&_x_u_contapagaritem_idcontapagar=`+$(vthis).val()
            ,parcial: true	
        });
    }
}

function gerarnota(inidcontapagar,inidpessoa){ basicCalculator
    CB.post({
          objetos: `_x_i_nf_idobjetosolipor=`+inidcontapagar+`&_x_i_nf_tiponf=S&_x_i_nf_tipoobjetosolipor=contapagar&_x_i_nf_idpessoa=`+inidpessoa+`&_x_i_nf_total=`+$('#basicCalculator').val()
          ,parcial: true	
    });
}



function  shcontapagar(inidcontapagar){
    janelamodal('?_modulo=contapagar&_acao=u&idcontapagar='+inidcontapagar+'');
}

/*
 * Duplicar bioensaio [ctrl]+[d]
 */
$(document).keydown(function(event) {

    if (!((event.ctrlKey || event.altKey) && event.keyCode == 68)) return true;

	if(!teclaLiberada(event)) return;//Evitar repeti&ccedil;&atilde;o do comando abaixo

	//janelamodal('?_modulo=contapagar&_acao=i&idcontapagarcp=<?=$_1_u_contapagar_idcontapagar?>');
        
        CB.post({
            objetos: "_x_i_contapagar_idobjeto=<?=$_1_u_contapagar_idcontapagar?>&_x_i_contapagar_formapagto="+$("[name=_1_u_contapagar_formapagto]").val()+"&_x_i_contapagar_obs="+$("[name=_1_u_contapagar_obs]").val()+"&_x_i_contapagar_datareceb="+$("[name=_1_u_contapagar_datareceb]").val()+"&_x_i_contapagar_tipoobjeto=contapagar&_x_i_contapagar_valor="+$("[name=_1_u_contapagar_valor]").val()+"&_x_copiar=Y&_x_i_contapagar_idcontaitem="+$("[name=_1_u_contapagar_idcontaitem]").val()+"&_x_i_contapagar_idpessoa="+$("[name=_1_u_contapagar_idpessoa]").attr("cbvalue")
            ,parcial:true
            ,posPost: function(data, textStatus, jqXHR){					
                shcontapagar(CB.lastInsertId);
            }
        })


    return false;
});

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>

<?
require_once '../inc/php/readonly.php';
?>