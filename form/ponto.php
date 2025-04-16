<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}



$idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;

$sql="select * from "._DBCARBON."._lpmodulo where modulo ='aprovaponto' and idlp in(".getModsUsr("LPS").")";
$res=d::b()->query($sql) or die("erro ao buscar supervisor sql=".$sql);
$qtdsup= mysqli_num_rows($res);


/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "ponto";
$pagvalmodulo=$_GET['_modulo'];
$pagvalcampos = array(
	"idponto" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from ponto where idponto = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
$idpessoa=$_GET['idpessoa'];
$ausencia=$_GET['ausencia'];
$data=$_GET['data'];
if(empty($_1_u_ponto_idpessoa) and !empty($idpessoa)){
    $_1_u_ponto_idpessoa=$idpessoa;
    
    if($ausencia=="Y"){
        $sqlt="select horaini,addtime(horaini,'08:00:00') as horafim from pessoahorario where idpessoa=6494 order by horaini asc limit 1";
        $rest= d::b()->query($sqlt) or die("erro ao buscar os horarios padrao sql=".$sqlt);
        $rowt=mysqli_fetch_assoc($rest);
        $adausencia='Y';
        $_1_u_ponto_hora=$rowt['horaini'];
        $_1_u_ponto_hora2=$rowt['horafim'];
        $_1_u_ponto_status='L';
        $_1_u_ponto_status2 ='D' ;    
    }
}

if (empty($_1_u_ponto_data)){
    if(!empty($data)){
        $_1_u_ponto_data= $data;
    }else{
        $_1_u_ponto_data= date("d/m/Y");
    }
    
}	

if($qtdsup<1 and  $_1_u_ponto_idpessoa != $idusuario and !empty($_1_u_ponto_idpessoa)){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Sem Permissão para Visualizar!</div>
    </div>
    </div>
</div>
<?
 die();
}elseif(empty($_1_u_ponto_idpessoa) and !empty ($idusuario) and $_acao=='i' and $qtdsup<1){

    $_1_u_ponto_idpessoa=$idusuario;
}
?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Ponto <?if($adausencia=='Y'){?>Ausàªncia Inà­cio<?}?></div>
        <div class="panel-body">
		<table>
		<tr> 
			<td></td> 
			<td><input name="_1_<?=$_acao?>_ponto_idponto" type="hidden" value="<?=$_1_u_ponto_idponto?>" readonly='readonly'></td> 
		</tr>
		<tr> 
			<td align="right">Funcionário:</td> 
			<td>
                            <?if(empty($_1_u_ponto_idpessoa)){?>
                            <select  name="_1_<?=$_acao?>_ponto_idpessoa">
                                <option value=""></option>
                                    <?fillselect("select idpessoa,nomecurto from pessoa where idtipopessoa = 1 and status='ATIVO'",$_1_u_ponto_idpessoa);?>		
                            </select>                            
                            <?}else{?>
                            <input name="_1_<?=$_acao?>_ponto_idpessoa" type="hidden" value="<?=$_1_u_ponto_idpessoa?>" vnulo>
				<?=traduzid("pessoa","idpessoa","nome",$_1_u_ponto_idpessoa)?>
                            <?}?>
			</td> 
		</tr>
		<tr> 
			<td align="right">Data:</td> 
			<td><input  class="calendario size8" size="8" name="_1_<?=$_acao?>_ponto_data" type="text" value="<?=$_1_u_ponto_data?>" vnulo></td> 
		</tr>
		<tr> 
			<td align="right">Hora:</td> 
			<td><input  class="size8" size="8" name="_1_<?=$_acao?>_ponto_hora" type="text" value="<?=$_1_u_ponto_hora?>" vnulo></td> 
		</tr>
		<tr> 
			<td align="right">Tipo:</td> 
			<td>
                            <select class="size8" name="_1_<?=$_acao?>_ponto_status">
                                    <?fillselect("select 'L','Entrada' union select 'D','Saà­da'",$_1_u_ponto_status);?>		
                            </select>
			</td> 
		</tr>
                <tr> 
			<td align="right">Obs. Ponto:</td> 
                        <td><font color='red'><?=$_1_u_ponto_obsponto?></font></td> 
		</tr>
		<tr> 
			<td align="right">Obs.</td> 
			<td><textarea name="_1_<?=$_acao?>_ponto_obs" 	 style=" width: 433px; height: 100px;"><?=$_1_u_ponto_obs?></textarea></td> 
		</tr>
		<tr> 
			<td align="right">Status:</td> 
			<td>
                            <?if($qtdsup<1){?>
                            <input  class="size8" size="8" name="_1_<?=$_acao?>_ponto_batida" type="hidden" value="PENDENTE" vnulo>
                            <span class="alert-warning">PENDENTE</span>
                            <?}else{?>
                            <select class="size8" name="_1_<?=$_acao?>_ponto_batida">
                                    <?fillselect("select 'PENDENTE','Pendente' union select 'ATIVO','Ativo'",$_1_u_ponto_batida);?>		
                            </select>
                            <?}?>
			</td> 
		</tr>

		</table>	
        </div>
    </div>
<?
        if($adausencia=="Y"){
?>
    <div class="panel panel-default" >
        <div class="panel-heading">Ponto Ausàªncia Fim</div>
        <div class="panel-body">

		<table>
		<tr> 
			<td></td> 
			<td><input name="_2_<?=$_acao?>_ponto_idponto" type="hidden" value="<?=$_1_u_ponto_idponto?>" readonly='readonly'></td> 
		</tr>
		<tr> 
			<td align="right">Funcionário:</td> 
			<td>
                            <?if(empty($_1_u_ponto_idpessoa)){?>
                             <select  name="_2_<?=$_acao?>_ponto_idpessoa">
                                    <?fillselect("select idpessoa,nomecurto from pessoa where idtipopessoa = 1 and status='ATIVO'",$_1_u_ponto_idpessoa);?>		
                            </select>                            
                            <?}else{?>
                            <input name="_2_<?=$_acao?>_ponto_idpessoa" type="hidden" value="<?=$_1_u_ponto_idpessoa?>" vnulo>
				<?=traduzid("pessoa","idpessoa","nome",$_1_u_ponto_idpessoa)?>
                            <?}?>
			</td> 
		</tr>
		<tr> 
			<td align="right">Data:</td> 
			<td><input  class="calendario size8" size="8" name="_2_<?=$_acao?>_ponto_data" type="text" value="<?=$_1_u_ponto_data?>" vnulo></td> 
		</tr>
		<tr> 
			<td align="right">Hora:</td> 
			<td><input  class="size8" size="8" name="_2_<?=$_acao?>_ponto_hora" type="text" value="<?=$_1_u_ponto_hora2?>" vnulo></td> 
		</tr>
		<tr> 
			<td align="right">Tipo:</td> 
			<td>
                            <select class="size8" name="_2_<?=$_acao?>_ponto_status">
                                    <?fillselect("select 'L','Entrada' union select 'D','Saà­da'",$_1_u_ponto_status2);?>		
                            </select>
			</td> 
		</tr>
		<tr> 
			<td align="right">Obs.</td> 
			<td><textarea name="_2_<?=$_acao?>_ponto_obs" 	 style=" width: 433px; height: 100px;"><?=$_1_u_ponto_obs?></textarea></td> 
		</tr>
		<tr> 
			<td align="right">Status:</td> 
			<td>
                             <?if($qtdsup<1){?>
                            <input  class="size8" size="8" name="_2_<?=$_acao?>_ponto_batida" type="hidden" value="PENDENTE" vnulo>
                            <span class="alert-warning">PENDENTE</span>
                            <?}else{?>
                            <select class="size8" name="_2_<?=$_acao?>_ponto_batida">
                                    <?fillselect("select 'PENDENTE','Pendente' union select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_ponto_batida);?>		
                            </select>
                            <?}?>                           					
			</td> 
		</tr>

		</table>
			
        </div>
    </div>
  <?
  
        }//if($adausencia=="Y"){
  ?>       
    </div>
</div>
<p>
    <?
if(!empty($_1_u_ponto_idponto)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_ponto_idponto; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "ponto"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

	

		<?
			$sql="select idponto,dma(data) as data,hora,batida,if(status='L','Entrada','Saà­da') as status from ponto where batida ='PENDENTE' and idpessoa=".$idusuario;
			$res= d::b()->query($sql) or die("erro ao buscar os pontos pendentes sql=".$sql);
			$qtdrow =mysqli_num_rows($res);
			if($qtdrow>0 and $adausencia!="Y"){			
		?>
	<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Pontos Pendentes</div>
        <div class="panel-body">
		 <table class="table table-striped planilha">
			<tr >
				<th >Dia</th>
				<th >Hora</th>
				<th >Tipo</th>
				<th >Status</th>
                                <th></th>
				
			</tr>
			<?
			while($row=mysqli_fetch_assoc($res)){
			?>
			<tr>
				<td><?=$row["data"]?></td>
				<td><?=$row["hora"]?></td>
				<td><?=$row["status"]?></td>
				<td><?=$row["batida"]?></td>
				<td><a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=ponto&_acao=u&idponto=<?=$row["idponto"]?>');"></a></td>
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

