<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/controllers/fluxo_controller.php");

$idpessoa =$_GET['idpessoa'];
if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "atendimento";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idatendimento" => "pk"
);



/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from atendimento where idatendimento = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");



if(empty($_1_u_atendimento_idpessoa) and !empty($idpessoa)){
    $_1_u_atendimento_idpessoa=$idpessoa;
}

function getContatoat(){
    $sql="select nome,idpessoa,email,dddfixo,telfixo,dddcel,telcel
	    from pessoa 
	    where idtipopessoa in (3,12) 
	    and status IN ('ATIVO','PENDENTE')
	  order by nome";
    
    //die($_SESSION["IDPESSOA"]);
    $res = d::b()->query($sql) or die("getContatoat: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
	$arrret[$r["idpessoa"]]["idpessoa"]=$r["idpessoa"];
	$arrret[$r["idpessoa"]]["email"]=$r["email"];
	$arrret[$r["idpessoa"]]["dddfixo"]=$r["dddfixo"];
	$arrret[$r["idpessoa"]]["telfixo"]=$r["telfixo"];
	$arrret[$r["idpessoa"]]["dddcel"]=$r["dddcel"];
	$arrret[$r["idpessoa"]]["telcel"]=$r["telcel"];
    }
	return $arrret;
}

//Recupera os contato as serem selecionados
$arrContato=getContatoat();
//print_r($arrCli); die;
$jContato=$JSON->encode($arrContato);


function getClientesnf(){
   
    //Se for um representante sà³ deve listar para o mesmo o clientes representados por ele
    if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15){
        //$str=" join pessoacontato c on(c.idpessoa = p.idpessoa and c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"].") ";
        $str=" join pessoacontato c on(c.idcontato =".$_SESSION["SESSAO"]["IDPESSOA"].")
                    join pessoacontato c2 on (c2.idcontato =c.idpessoa and c2.idpessoa = p.idpessoa)";
    }

    $sql= "SELECT
                p.idpessoa,
                if(p.cpfcnpj !='',concat(p.nome,' - ',p.cpfcnpj),p.nome) as nome,
                CASE p.idtipopessoa
                    WHEN 5 THEN 'FORNECEDOR'
                    WHEN 2 THEN 'CLIENTE'					
                END as tipo
        FROM pessoa p ".$str."			
        WHERE p.status = 'ATIVO'
	    AND p.idtipopessoa  in (2,5,7)
	    ".getidempresa('p.idempresa','pessoa')."
        ORDER BY p.nome";
//die($_SESSION["IDPESSOA"]);
    $res = d::b()->query($sql) or die("getClientes: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
        $arrret[$r["idpessoa"]]["tipo"]=$r["tipo"];
    }
	return $arrret;
}

//Recupera os clientes as serem selecionados
$arrCli=getClientesnf();
//print_r($arrCli); die;
$jCli=$JSON->encode($arrCli);


function jsonMotivo(){

    $sql= "select  t.idmotivo, t.motivo
	    from motivo t
	    where t.status='ATIVO'
	    order by t.motivo";

    $res = d::b()->query($sql) or die("getMotivo: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    $i=0;
    while($r = mysqli_fetch_assoc($res)){
        $arrtmp[$i]["value"]=$r["idmotivo"];
        $arrtmp[$i]["label"]=  ($r["motivo"]);
        $i++;
	
    }
    
    $json = new Services_JSON();
    return $json->encode($arrtmp);
}

//Não listar o cliente se o representado não estiver em sua lista de contatos
 if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==12 and !empty($_1_u_atendimento_idpessoa)){
    $sql2 ="select c2.*
                from pessoa p
            join pessoacontato c on(c.idcontato =".$_SESSION["SESSAO"]["IDPESSOA"].")
            join pessoacontato c2 on (c2.idcontato =c.idpessoa and c2.idpessoa = p.idpessoa)
		    where p.idpessoa=".$_1_u_atendimento_idpessoa." ";

    $res2 = d::b()->query($sql2) or die("A Consulta do representante falhou :".mysqli_error(d::b())."<br>Sql:".$sql2); 
    $qtdrows2= mysqli_num_rows($res2);
    if($qtdrows2<1){
?>
	<div class="alert alert-warning">
	    <span class="alert-warning"><i class="fa fa-exclamation-triangle"></i>&nbsp;O Cliente do atendimento não faz parte da lista de clientes representados!</span>
	</div>
<?
	die();
    }
     
 }
//print_r($_SESSION);
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">		
            <table>
                <tr>
                    <td><strong>ID.:</strong></td>
                    <td>
                        <input  name="_1_<?=$_acao?>_atendimento_idatendimento" type="hidden" value="<?=$_1_u_atendimento_idatendimento?>" readonly='readonly'>
                        <label class="idbox"> <?=$_1_u_atendimento_idatendimento?></label>
                    </td>
                    <td align="right">Empresa:</td> 
                    <td>
                    <?if($_1_u_atendimento_status == "FINALIZADO" and !empty($_1_u_atendimento_idpessoa)){?>
                        <?=traduzid("pessoa","idpessoa","nome",$_1_u_atendimento_idpessoa)?>
                    <?}else{?>	
			
			<input <?if(!empty($_1_u_atendimento_idpessoa)){/*?> readonly="readonly"<?*/}?> type="text" name="_1_<?=$_acao?>_atendimento_idpessoa"  cbvalue="<?=$_1_u_atendimento_idpessoa?>" value="<?=$arrCli[$_1_u_atendimento_idpessoa]["nome"]?>" style="width: 40em;" vnulo>
			
                        
                    <?}?>
			<?if($_1_u_atendimento_idpessoa){?>
                        <a class="fa fa-bars pointer hoverazul" title="Cadastro de Clientes"  onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$_1_u_atendimento_idpessoa?>')"></a>
                        <?}?>
                    </td>
                    <td>
					<td>Data:</td>
                   <td>
                        <?
                        if (empty($_1_u_atendimento_data)){
                            $_1_u_atendimento_data= date("d/m/Y");
                        }
                        if (empty($_1_u_atendimento_exercicio)){
                            $_1_u_atendimento_exercicio= date("Y");
                        }
                        ?>	
       						
                        <input  name="_1_<?=$_acao?>_atendimento_data" class="calendario" type="text" size ="8" value="<?=$_1_u_atendimento_data?>">              
                        <input name="_1_<?=$_acao?>_atendimento_exercicio" type="hidden" value="<?=$_1_u_atendimento_exercicio?>" >	
                    </td>
					<td>Status:</td>		
					<td>                       
						<select <?=$disabled?> class="size10" name="_1_<?=$_acao?>_atendimento_status" onchange="fMostraPrazo(this.value)" style="font-size: 14px; font-weight: bold;" vnulo>
							<?fillselect("select 'RETORNAR','Retornar' union select 'AGUARDAR','Aguardando Retorno' union select 'FINALIZADO','Finalizado' ",$_1_u_atendimento_status);?>
						</select>		
					</td>
                </tr>
            </table>
        </div>
        <div class="panel-body"> 
			
		<div class="row">
			<div class="col-md-1">Contato:</div> 
			<div class="col-md-5">
				<?if($_1_u_atendimento_nome){
					echo $_1_u_atendimento_nome;
				}else{?>
				<input type="text" style="width: 40em;" name="_1_<?=$_acao?>_atendimento_idcontato"  cbvalue="<?=$_1_u_atendimento_idcontato?>" value="<?=$arrContato[$_1_u_atendimento_idcontato]['nome']?>" style="width: 25em;" vnulo>
				<?}?>
			</div> 
			<div class="col-md-3">Tel. 1:

				<input  name="_1_<?=$_acao?>_atendimento_ddd1" style="width: 30px;"  type="text" value="<?=$_1_u_atendimento_ddd1?>"> -
				<input  name="_1_<?=$_acao?>_atendimento_telefone1" style="width: 90px;"  type="text" value="<?=$_1_u_atendimento_telefone1?>">
			</div>
		</div>
	    <div class="row">
		<div class="col-md-1">Email:</div>
		<div class="col-md-5"><input style="width: 40em;"  name="_1_<?=$_acao?>_atendimento_email"  type="text" value="<?=$_1_u_atendimento_email?>"></div>

			<div class="col-md-3">Tel. 2:
		
		    <input  name="_1_<?=$_acao?>_atendimento_ddd2" style="width: 30px;"   type="text" value="<?=$_1_u_atendimento_ddd2?>"> - 
		    <input  name="_1_<?=$_acao?>_atendimento_telefone2" style="width: 90px;"  type="text" value="<?=$_1_u_atendimento_telefone2?>">
			</div>
	    </div>
	     <div class="row">
			 <?if(!empty($_1_u_atendimento_idmotivo)){?>
		 <div class="col-md-1">Motivo:</div>
		 <div class="col-md-5">
		<?
			if($_1_u_atendimento_status != "FINALIZADO"){
		?>		
		    <input type="text" name="_1_<?=$_acao?>_atendimento_idmotivo" cbvalue="<?=$_1_u_atendimento_idmotivo?>" value="<?=traduzid('motivo','idmotivo','motivo',$_1_u_atendimento_idmotivo)?>" style="width: 30em;" vnulo>
		<?
			}else{
				echo(traduzid('motivo','idmotivo','motivo',$_1_u_atendimento_idmotivo));
			}
		?>			
		 </div>
			 <?}?>
		 <div class="col-md-1"></div>
		 <div class="col-md-5">		     
		 </div>		
	    </div>
	    <div class="row">
		 <div class="col-md-1"style="display: none">Prioridade:</div>
		 <div class="col-md-3" style="display: none">
		    <select class="size10" <?=$disabled?> name="_1_<?=$_acao?>_atendimento_prioridade" style="font-size: 14px; font-weight: bold;">
			<?fillselect(" select 'BAIXA','Baixa' union select 'MEDIA','Média' union  select 'ALTA','Alta'",$_1_u_atendimento_prioridade);?>		</select>
               </div>

		
        </div>
	    <div class="row">
		<div class="col-md-1">Descrição:</div>
		<div class="col-md-11">
		    <TEXTAREA  <?=$disabledtextobs?> class="caixa" style="width: 900px; height: 250px;" NAME="_1_<?=$_acao?>_atendimento_informacao" vnulo><?=$_1_u_atendimento_informacao?></TEXTAREA>
		</div>
	    </div>
            
		
		<?
		if($_1_u_atendimento_idmotivo==21 or $_1_u_atendimento_idmotivo==27){//RECLAMACAO
		    if(empty($_1_u_atendimento_idsgdoc)){
		?>
		<div class="row">
		    <div class="col-md-12">
			<i id="novoteste" class="fa fa-plus-circle verde btn-lg pointer" onclick="fnovornc(<?=$_1_u_atendimento_idatendimento?>);" title="Criar novo RNC"></i>
			Novo RNC
			</td>
		    </div>
		</div>
		<?
		    }else{
                        $idregistro= traduzid('sgdoc', 'idsgdoc', 'idregistro', $_1_u_atendimento_idsgdoc);
		?>
		    <div class="row">
			<div class="col-md-1">
			    RNC:
			</div>	
			<div class="col-md-11">
			    <a class="pointer"  onclick="janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?=$_1_u_atendimento_idsgdoc?>')"><?=$idregistro?></a>
			</div>
		    </div>
		<?									
		    }
		}
		?>
		
        </div>
    </div>
    </div>
</div>
<?
if(!empty($_1_u_atendimento_idatendimento)){
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
            and c.tipoobjeto in('atendimento','atendimentorepresentante')
            and c.idobjeto =".$_1_u_atendimento_idatendimento."  order by nome";

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
                    <th >Funcionários</th>
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
?>
<!--div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">Arquivos Anexos</div>
      <div class="panel-body">
           <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:50%;height:100%;">
                   <i class="fa fa-cloud-upload fonte18"></i>
           </div>
       </div> 
     </div>
</div-->    
<?
}//if(!empty($_1_u_atendimento_idatendimento)){
?>
<?
if(!empty($_1_u_atendimento_idatendimento)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_atendimento_idatendimento; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "rotulo"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

<script>

function fnovornc(idatendimento, )
{
    <? $idfluxostatus = FluxoController::getIdFluxoStatus('lote', 'AGUARDANDO', $idunidadelote);?>
    CB.post({
	objetos: {
	    "_x_i_sgdoc_idsgdoctipo":'rnc'
        ,"_x_i_sgdoc_status":'AGUARDANDO'
        ,"_x_i_lote_idfluxostatus": <?=$idfluxostatus?>
	    ,"_x_i_sgdoc_titulo":'<?=traduzid('motivo','idmotivo','motivo',$_1_u_atendimento_idmotivo);?> RNC '+idatendimento
	}
	,parcial: true
	,refresh: false
	,posPost: function(){
	    fvinculadoc(idatendimento, CB.lastInsertId);
	}
	
    }); 
}    
   

function fvinculadoc(idatendimento, idsgdoc){
  
    CB.post({
	objetos: {
	    "_x_u_atendimento_idatendimento":idatendimento
	    ,"_x_u_atendimento_idsgdoc":idsgdoc
	}
	,parcial: true
	,posPost: function(){
	    
	}
	
    }); 
}    

fMostraPrazo('<?=$_1_u_atendimento_status;?>');
function fMostraPrazo(valor){
if (valor == 'RETORNAR' || valor == 'AGUARDAR')
	{
		$('#_1_u_atendimento_prazo').show();
		$('#_span_atendimento_prazo').show();
		
	}else{
		$('#_1_u_atendimento_prazo').hide();
		$('#_span_atendimento_prazo').hide();
	}
}
$(function(){
    $('.caixa').autosize();
});	

jsonMotivo = <?=jsonMotivo()?>;// autocomplete motivo assunto

//autocomplete de motivo
$("[name*=_atendimento_idmotivo]").autocomplete({
    source: jsonMotivo
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }  
});
// FIM autocomplete motivo



jCli=<?=$jCli?>;// autocomplete cliente
//mapear autocomplete de clientes
jCli = jQuery.map(jCli, function(o, id) {
    return {"label": o.nome, value:id+"" ,"tipo":o.tipo}
});

//autocomplete de clientes
$("[name*=_atendimento_idpessoa]").autocomplete({
    source: jCli
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.tipo+"</span></a>").appendTo(ul);
        };
    }	
});
// FIM autocomplete cliente


jContato=<?=$jContato?>;// autocomplete contato
//mapear autocomplete de contato
jContato = jQuery.map(jContato, function(o, id) {
    return {"label": o.nome, value:id+"","idpessoa":o.idpessoa,"email":o.email,"dddfixo":o.dddfixo,"telfixo":o.telfixo,"dddcel":o.dddcel,"telcel":o.telcel}
});

//autocomplete de contato
$("[name*=_atendimento_idcontato]").autocomplete({
    source: jContato
    ,delay: 0
    ,select: function(event, ui) {
	$("[name*=_atendimento_email]").val(ui.item.email);
	$("[name*=_atendimen_ddd1]").val(ui.item.dddfixo);
	$("[name*=_atendimento_telefone1]").val(ui.item.telfixo);
	$("[name*=_atendimento_ddd2]").val(ui.item.dddcel);
	$("[name*=_atendimento_telefone2]").val(ui.item.telcel);
    }
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
         return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }
    
	,noMatch: function(objAc){
			debugger;
    console.log("Executei callback");
	CB.post({
		    objetos: "_x_i_pessoa_status=PENDENTE&_x_i_pessoa_idtipopessoa=3&_x_i_pessoa_nome="+objAc.term
			,refresh: false
			,parcial:true
			,msgSalvo: "Contato criado"
		    ,posPost: function(data, textStatus, jqXHR){
			    //Atualiza source json
			    $("[name*=_atendimento_idcontato]").data('uiAutocomplete').options.source.push({
				    label: $("[name*=_atendimento_idcontato]").val()
				    ,value: CB.lastInsertId
				    ,idpessoa: CB.lastInsertId
			    });
			    //Atualiza o objeto DATA associado ao input
			    //$("[name*=_atendimento_nome]").data("nucleos")[CB.lastInsertId]={"nucleo":$oIdcontato.val()};
			    //Mostra a nova opção
			    $("[name*=_atendimento_idcontato]").autocomplete( "search", $("[name*=_atendimento_idcontato]").val());
			    
		    }
	    });
    }
});
// FIM autocomplete contato
<?
if(!empty($_1_u_atendimento_idatendimento)){
    $sqla="select * from carrimbo 
	    where status='PENDENTE' 
	    and idobjeto = ".$_1_u_atendimento_idatendimento." 
	    and tipoobjeto in ('atendimento','atendimentorepresentante')
	    and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
    $resa=d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
    $qtda= mysqli_num_rows($resa);
    if($qtda>0){
	 $rowa=mysqli_fetch_assoc($resa);

?>    
	    botaoAssinar(<?=$rowa['idcarrimbo']?>);  
<?	    

    }// if($qtda>0){
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
                            escondebotao();  
                    }
		});
	    }
            
	});
    }
}
if( $("[name=_1_u_atendimento_idatendimento]").val() ){
    $(".cbupload").dropzone({
        idObjeto: $("[name=_1_u_atendimento_idatendimento]").val()
        ,tipoObjeto: 'atendimento'
		,idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"]?>'
    });
}

function escondebotao(){
    $('#btAssina').hide();
   // document.location.reload(); 
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
