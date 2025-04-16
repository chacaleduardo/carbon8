<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "pessoa";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idpessoa" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from pessoa where idpessoa = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<style>
.messageboxok{

 width:auto;
 margin-left:30px;
 border:1px solid #349534;
 background:#C9FFCA;
 padding:3px;
 font-weight:bold;
 color:#008000;
}
.messageboxerror{

 width:auto;
 margin-left:30px;
 border:1px solid #CC0000;
 background:#F7CBCA;
 padding:3px;
 font-weight:bold;
 color:#CC0000;
}

</style>
<div class="row ">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading ">
        <table>
        <tr>
            <td align="right">Descrição:</td> 
            <td>
                <input class="upper" name="_1_<?=$_acao?>_pessoa_nome"	type="text" size="48"	value="<?=$_1_u_pessoa_nome?>"	vnulo></td> 
                <input name="_1_<?=$_acao?>_pessoa_idpessoa" type="hidden" value="<?=$_1_u_pessoa_idpessoa?>" readonly='readonly'>
                <input name="_1_<?=$_acao?>_pessoa_idtipopessoa" type="hidden" value="13" readonly='readonly'>               
            </td>
            <td align="right">Status:</td> 
            <td>	
                <select  name="_1_<?=$_acao?>_pessoa_status" id="status" vnulo>
                    <?
                    fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'  ",$_1_u_pessoa_status);
                    ?>
                </select>
            </td>                
        </tr>
        </table>
        </div>
        </div>
    </div>
    </div>
    <?
    if(!empty($_1_u_pessoa_idpessoa)){   
    ?>
    <div class='row'>
    <div class="col-md-12">
    <div class="panel panel-default">   
	<div class="panel-heading" data-toggle="collapse" href="#sistema">Configurações</div>
	<div class="panel-body" >
    <table > 
		<tr>
			<td>Usuário Email:</td>
			<td><input 
					name="_1_<?=$_acao?>_pessoa_webmailusuario"
					id="usernameemail"
					type="text" 
					size="15"
					value="<?=$_1_u_pessoa_webmailusuario?>" 
					  maxlength="30" class="size15">
				</td>
				<td>Exemplo: <font color="red">usuario_inata</font> ou <font color="red">usuario_laudolab</font></td>
		</tr>
		<tr>
			<td> Email:</td>
			<td><input 
					name="_1_<?=$_acao?>_pessoa_webmailemail"
					id="uemail"
					type="text" 
					size="30"
					value="<?=$_1_u_pessoa_webmailemail?>" 
					   class="size15">
				</td>
				<td>Exemplo: <font color="red">usuario@inata.com.br</font> ou <font color="red">usuario@laudolab.com.br</font></td>
		</tr>
		<tr>
			<td>
				Webmail Permissão:
			</td>
			<td>	
				<?if($_1_u_pessoa_webmailpermissao!='Y' and !empty($_1_u_pessoa_webmailusuario) and !empty($_1_u_pessoa_webmailemail)){?>
				<select  name="_1_<?=$_acao?>_pessoa_webmailpermissao"  >
					<option value=""></option>
                    <?
                    fillselect("select 'Y','Sim' union select 'N','Não'  ",$_1_u_pessoa_webmailpermissao);
                    ?>
                </select>
				<?}else{?>
				<label class="alert-warning">SIM</label>
				<?}?>
			</td>
		</tr>
		<tr> 
			
		    <td class="lbr">Usuário:</td> 
		    <td>
<?
				    if(empty($_1_u_pessoa_usuario)){//deixar informar somente se nao tiver sido salvo e a variavel da pagina nao existir?>
			<input 
					name="_1_<?=$_acao?>_pessoa_usuario"
					id="username"
					type="text" 
					size="15"
					value="<?=$_1_u_pessoa_usuario?>" 
					vnulo valfa maxlength="30" class="size15">
			<span id="msgbox" style="display:none"></span>            

<?
				    }else{
                        if(empty($_1_u_pessoa_senha)){
                            //se a senha nao tiver sido informada, deixar a caixa habilitada
                            $strdisabled = "";
                            $cdesab="";
                        }else{
                            $strdisabled = "disabled='disabled'";
                            $cdesab="desabilitado";
                        }
?>
			<label style="font-size:14px;"><?=$_1_u_pessoa_usuario?></label>
			
<?	
					}
?>
		    </td>
		    <td rowspan="3">
			<a class="fa fa-pencil hoverazul btn-lg pointer" onclick="editarpwd(this);" title="Editar"></a>
		    </td>
		</tr>
		<tr> 
		    <td class="lbr"><font color="red">Senha:</font></td> 
		    <td>
			<input 
				name="_1_<?=$_acao?>_pessoa_senha"
				type="password"
				<?=$strdisabled?>
                class="<?=$cdesab?>"
                class="size15"
				value="" 
				vnulo vpwd1>
		    </td>
		    <td></td>
		</tr>
		<tr> 
		    <td class="lbr"><font color="red">Confirmação:</font></td> 
		    <td>
			<input 
				name="confirmacaosenha"
				type="password"
				<?=$strdisabled?>
                class="<?=$cdesab?>"
                class="size15"
				value="" 
				vnulo vpwd2>
		    </td> 
		</tr>
        
        </table>
        </div>
        </div>
    </div>
    </div>
    </div>
<?
 }//
?>
<?
if(!empty($_1_u_pessoa_idpessoa)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_pessoa_idpessoa; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "pessoa"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

<script>

	//Verifica disponibilidade de nome de usuario
	$("#username").blur(function()
	{

		vusuario = this.value; 
		if(vusuario!=""){//validar somente se o campo contiver algum valor

			$.ajax({
				type: 'get',
				url: 'ajax/usr.php',
				data: { user_name: vusuario },
				/*********************************************************************/
				success: function(data){

					if(data=="OK") //se o nome de usuario estiver livre (a pagina php chamada retornou a string OK)
					{
                         CB.post();
                        /*
						$("#msgbox").fadeTo(100,0.1,function() //mostra o messagebox
						{ 
							$(this).html('Ok!').addClass('messageboxok').fadeTo(100,1);
							document.getElementById("cbSalvar").style.display="";
						});	
                        */	
					}else if(data=="ERRO") //se a pagina retornou erro
					{
						$("#msgbox").fadeTo(100,0.1,function() //mostra o erro que a pagina escreveu
						{ 
							$(this).html('Usuário já existente!<br>Informe outro Usuário').addClass('messageboxerror').fadeTo(100,1);
							document.getElementById("cbSalvar").style.display="none";
							document.getElementById("username").focus();
			  			});		
					}else
					{
						$("#msgbox").fadeTo(100,0.1,function()  //mostra qualquer condicao diferente. Ex: erro de php
						{ 
							$(this).html('Erro:<br>'+data).addClass('messageboxerror').fadeTo(100,1);
							document.getElementById("cbSalvar").style.display="none";
							document.getElementById("username").focus();
			
						});
					}
				},
	
				/*********************************************************************/
				error: function(objxmlreq){ 
					$("#msgbox").fadeTo(100,0.1,function()
					{ 
						$(this).html('Erro:<br>'+objxmlreq.status).addClass('messageboxerror').fadeTo(100,1);
							document.getElementById("submit").style.display="none";
							document.getElementById("username").focus();
					});
				}
			})//$.ajax
        }else{
            //se o campo nao contiver valor, esconder a msgbox
        	$("#msgbox").fadeOut();
        }
	});



function editarpwd(){
  
  if(  $("[name=_1_u_pessoa_senha]").attr('class')=="desabilitado"){ 

      $("[name=_1_u_pessoa_senha]").removeClass("desabilitado");
      $("[name=confirmacaosenha]").removeClass("desabilitado");
      $("[name=_1_u_pessoa_senha]").removeAttr("disabled");
      $("[name=confirmacaosenha]").removeAttr("disabled");
  }else{
       $("[name=_1_u_pessoa_senha]").addClass("desabilitado");
      $("[name=confirmacaosenha]").addClass("desabilitado");
      $("[name=_1_u_pessoa_senha]").attr("disabled","disabled");
      $("[name=confirmacaosenha]").attr("disabled","disabled");
  }
}

function confemail(vthis,inusuario){
    var est = $(vthis).val();
    if(confirm("Criar email para este usuário?")){
        
        var webmailusuario=inusuario+'_'+est;
        var webmailemail =inusuario+'@'+est+'.com.br';
        
        CB.post({
            objetos: "_x_u_pessoa_idpessoa="+$("[name=_1_u_pessoa_idpessoa]").val()+"&_x_u_pessoa_webmailpermissao=Y&_x_u_pessoa_webmailusuario="+webmailusuario+"&_x_u_pessoa_webmailemail="+webmailemail
            , parcial:true
        });        
    }
}

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
