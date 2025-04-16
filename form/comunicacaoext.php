<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetros chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "comunicacaoext";
$pagvalcampos = array(
	"idcomunicacaoext" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from comunicacaoext where idcomunicacaoext = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

?>
<div class="row">
<div class="col-md-12">
<div class="panel panel-default">
    <div class="panel-heading">	
        <table>
        <tr>
            <td class="lbr">COMUNICAÇÃO</td> 
            <td>
                <input name="_1_<?=$_acao?>_comunicacaoext_idcomunicacaoext" type="hidden" value="<?=$_1_u_comunicacaoext_idcomunicacaoext?>" readonly='readonly'>
              
            </td>                     
        </tr>
        </table>
    </div>
    <div class="panel-body">
        <table>
            <tr>
                 <?
                if($_1_u_comunicacaoext_tipoobjeto=='immsgconf'){
                    $sql1="select titulo,tabela from immsgconf where idimmsgconf = ".$_1_u_comunicacaoext_idobjeto;
                    $res1 = d::b()->query($sql1) or die("A Consulta da configuração do alerta email falhou :".mysqli_error()."<br>Sql:".$sql1); 
                    $row1=mysqli_fetch_assoc($res1);
                ?>
                <td>Titulo:</td>
                <td><b><?=$row1["titulo"]?></b></td>
                <?
                }//if($_1_u_comunicacaoext_tipoobjeto=='immsgconf'){
                ?>
                <td class="lbr" color="red">Status:</td> 
                <?if($_1_u_comunicacaoext_status=="SUCESSO"){$sty="color: green;";}else{$sty="color: red;";}?>
                <td style="<?=$sty?>"><b><?=$_1_u_comunicacaoext_status?></b></td>  
            </tr>
            <?if(!empty($_1_u_comunicacaoext_conteudo)){?>
            <tr>
                <td>Mensagem:</td>
                <td colspan="3" style="color: red;"><?=$_1_u_comunicacaoext_conteudo?></td>
            </tr>
            <?}?>
        </table>
        
    </div>
</div>
</div>
</div>
          <?
            $sql=" select * from comunicacaoextitem where  idcomunicacaoext=".$_1_u_comunicacaoext_idcomunicacaoext;
            $res = d::b()->query($sql) or die("A Consulta dos itens da comunicacao falhou :".mysqli_error()."<br>Sql:".$sql); 
            $i=1;
            while($row=mysqli_fetch_assoc($res)){
               $i=$i+1;
        ?>
<div class="row">
<div class="col-md-12">
<div class="panel panel-default">
    <div class="panel-heading">

        <table >  
            <tr>
                <TD>ITENS</TD>
                <td>ID:</td>
                <th><?=$row['idobjeto']?></th>
                <td>Modulo:</td>
                <th><?=$row['tipoobjeto']?></th>
                <td>Status:</td>
                <th>
                    <input name="_<?=$i?>_u_comunicacaoextitem_idcomunicacaoextitem" type="hidden" value="<?=$row['idcomunicacaoextitem']?>" readonly='readonly'>
                    <select  name="_<?=$i?>_u_comunicacaoextitem_status">
                        <?fillselect("select 'ERRO','ERRO' union select 'SUCESSO','SUCESSO' union select 'REENVIAR','Reenviar'",$row['status']);?>		
                    </select>
                </th>
            </tr>
        </table>    
        
    </div>
    <div class="panel-body">
        
        <?if(!empty($row["conteudo"])){?>
        <table>
           
            <tr>
                <td>Mensagem:</td>
                <td style="color: red;"><?=$row["conteudo"]?></td>
            </tr>
        </table>

        <?
         }
        ?>
    </div>
</div>
</div>
</div>
        <?

            $sql2="select p.nome,d.idobjeto,d.destino,d.conteudo,d.status 
                from comunicacaoextitem i join comunicacaoextdest d left 
                join pessoa p on(p.idpessoa=d.idobjeto and d.tipoobjeto='pessoa')
                where  i.idcomunicacaoext=".$_1_u_comunicacaoext_idcomunicacaoext."
                and i.idcomunicacaoextitem=d.idcomunicacaoextitem
                and i.idcomunicacaoextitem=".$row["idcomunicacaoextitem"];
            $res2 = d::b()->query($sql2) or die("A Consulta dos itens da comunicacao falhou :".mysqli_error()."<br>Sql:".$sql2);
       
            while($row2=mysqli_fetch_assoc($res2)){
        ?>
<div class="row">
<div class="col-md-12">
<div class="panel panel-default">
    <div class="panel-body">
        <table>
            <tr>
                <td>Para:</td>
                <td><?=$row2["nome"]?></td>
                <td>Email:</td>
                <td><?=$row2["destino"]?></td>
                <td>Status:</td>
                <?if($row2["status"]=="SUCESSO"){$sty="color: green;";}else{$sty="color: red;";}?>
                <td style="<?=$sty?>"><?=$row2["status"]?></td>
            </tr>
            <tr>
                <td>Conteúdo:</td>
                <td colspan="5"><?=$row2["conteudo"]?></td>
            </tr>
        </table>
         </div>
</div>
</div>
</div>
            <?}?>

<?
            }
            ?>

<?
if(!empty($_1_u_comunicacaoext_idcomunicacaoext)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_comunicacaoext_idcomunicacaoext; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "comunicacaoext"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>
<?
require_once '../inc/php/readonly.php';
?>