<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}

$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$executor 	= $_GET["executor"];
$vidtarefa 	= $_GET["idtarefa"];
$idusuario=$_SESSION["SESSAO"]["IDPESSOA"];
//echo $idusuario;

if (!empty($vencimento_1) or !empty($vencimento_2)){
    $dataini = validadate($vencimento_1);
    $datafim = validadate($vencimento_2);

    if ($dataini and $datafim){
        $clausulad .= " and (dia  BETWEEN '" . $dataini ."' and '" .$datafim ."') ";
    }else{
        die ("Datas n&atilde;o V&aacute;lidas!");
    }
    if(!empty($executor)){
        $clausulad .=" and idpessoa =".$executor;
    }	
}
if($clausulad){

    if(empty($vencimento_1) and empty($vencimento_2) and empty($vidtarefa)){
        die("Informar o campo data ou tarefa para a busca");
    }

    $sql = "SELECT * from apontamento where status = 'ATIVO' ". $clausulad."   order by dia";	
    echo "<!--";
    echo $sql;
    echo "-->";
 
    if (!empty($sql)){
        $res = d::b()->query($sql) or die("Falha ao pesquisar horas: " . mysql_error() . "<p>SQL: $sql");
        $ires = mysqli_num_rows($res);		
    }
}
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
        <table>
	<tr>
            <td>Executor</td>
            <td></td>
            <td>
                <select name="executor" id ="executor" vnulo>
                    <option value=""></option>
                        <?fillselect("select idpessoa,usuario from pessoa where  idtipopessoa in (1,8)  and usuario is not null ".getidempresa('idempresa','pessoa')."  order by usuario",$executor);?>
                </select>
            </td>
	</tr>
	<tr>
            <td class="rotulo">Período</td>
            <td><font class="9graybold">entre</font></td>
            <td>
                <input class="calendario" name="vencimento_1" id="vencimento_1" value="<?=$vencimento_1?>"  type="text"  autocomplete="off">
            </td>
            <td><font class="9graybold">&nbsp;e&nbsp;</font></td>
            <td>
                <input class="calendario" name="vencimento_2" id="vencimento_2" value="<?=$vencimento_2?>"  type="text"  autocomplete="off">
            </td>
	</tr>
	<tr>
            <td></td>
            <td></td>
            <td>
                <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
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
if($clausulad){
?>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Apontamentos</div>
        <div class="panel-body" >
        <table class="table table-striped planilha" >
            <tr class="header">
                <th >Dia</th> 
                <th >Início</th>
                <th >Fim</th>
                <th >Projeto</th>
                <th >Tarefa</th> 
                <th >Executor</th> 
                <th >Local</th> 
                <th >Total</th>
            </tr>
<?
if($ires > 0){

    $i=1;
    while ($row = mysqli_fetch_assoc($res)){
    $i=$i+1;
    $diferenca = 0;

	if($idusuario <> $row["idpessoa"] and $_SESSION["SESSAO"]["IDLP"]!="GER"){
            $disabled = "disabled='disabled' ";
            $permitealt ="NAO";
            $readonly ="readonly='readonly'"; 	
	}else{
            $disabled = " ";
            $permitealt =" ";
            $readonly =" "; 	
	}
?>
            <tr class="respreto"> 	
                <td >
                    <?=dma($row["dia"])?>
                </td> 
                <td>
                    <?=$row["inicio"]?>
                </td> 
                <td>
                        <?=$row["fim"]?>
                </td> 
                <td><?=traduzid('projeto', 'idprojeto', 'projeto', $row["idprojeto"])?></td> 
                <td>
                    <?=traduzid('tarefa', 'idtarefa', 'tarefa',$row["idtarefa"])?>
                </td> 
                <td >
<?
                    echo traduzid("pessoa","idpessoa","usuario",$row["idpessoa"]);
?>              </td>	
                <td>
                    <?=$row["local"]?>
                </td>
                <td bgcolor="#FF7F50">	
<?
		$arrinicio = explode(":",$row["inicio"]);
		$arrfim = explode(":",$row["fim"]);
		
		$difhora =  $arrfim[0] - $arrinicio[0] ;
		
		$mdifhora= $difhora * 60;
		
		$difmin =  $arrfim[1] - $arrinicio[1] ;
		
		$ress = $mdifhora + $difmin;
		
		$ressfim = $ress / 60;
		
		echo round($ressfim,2);
							
		$somaressfim= $ressfim + $somaressfim;

?>	
                </td>
<?
	if($_SESSION["SESSAO"]["USUARIO"]==$row["criadopor"]){
?>	
                <td>
                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer ui-droppable" onclick="CB.post({objetos:'_ajax_d_apontamento_idapontamento=<?=$row["idapontamento"]?>',parcial:true})" title="Excluir apontamento"></i>
                </td>		
<?
	}
?>
            </tr> 
<?
    }//while ($row = mysql_fetch_array($res))
?>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td align="center" bgcolor="#00BFFF">SOMA</td>
                <td bgcolor="#00BFFF"><?=round($somaressfim,2)?></td>
            </tr>
<?
}//if($ires > 0)
?>
            <tr>
                <td colspan="50">&nbsp;</td>
            </tr>
            <tr class="header">
                <th align="center">Dia</th> 
                <th align="center">Início</th>
                <th align="center">Fim</th>
                <th align="center">Projeto</th>
                <th align="center">Tarefa</th> 
                <th align="center">Executor</th> 
                <th align="center">Local</th> 
                <th align="center">Total</th>
            </tr>
            <tr class="respreto"> 	
                <td>
                    <input name="apontamento_dia" placeholder="00/00/0000"	class="calendario" type="text"	size ="8"	value="" vnulo>
                </td> 
                <td>
                    <input name="apontamento_inicio" placeholder="00:00" class="classhora" type="text"	size = "5"	value="" vnulo>
                </td> 
                <td>
                    <input name="apontamento_fim" placeholder="00:00" class="classhora" type="text" size = "5"	value="" vnulo>
                </td> 
                <td>
                    <select name="apontamento_idprojeto">
                        <option value=""></option>
                        <?fillselect("SELECT idprojeto,projeto FROM projeto
                        where status in ('ANDAMENTO') order by projeto");?>
                    </select>
                </td> 
                <td>
                    <select name="apontamento_idtarefa">
                        <option value=""></option>
                        <?fillselect("SELECT idtarefa,tarefa FROM tarefa
                        where status in ('ANDAMENTO','AGUARDANDO') order by tarefa ");?>
                    </select>
                </td> 
                <td align="center">
                    <select name="apontamento_idpessoa" vnulo>
                        <option value=""></option>
                        <?fillselect("SELECT idpessoa,usuario FROM pessoa
                        where idtipopessoa = 1 and status = 'ATIVO'  order by usuario ");?>
                    </select>	
                </td>
                <td>
                    <input name="apontamento_local"	type="text"	value="" vnulo>		
                </td>
                <td>
                    <button id="cbSalvar" type="button" class="btn btn-danger btn-xs" onclick="salvarinf()" title="Salvar">
                        <i class="fa fa-circle"></i>Salvar
                    </button>
                </td>		
            </tr> 	
        </table>
        </div>
    </div>
    </div>
</div>
<?
}//if($clausulad){
?>
<script>    
function salvarinf(){    
    var str="_x_i_apontamento_dia="+$("[name=apontamento_dia]").val()
            +"&_x_i_apontamento_inicio="+$("[name=apontamento_inicio]").val()    
            +"&_x_i_apontamento_fim="+$("[name=apontamento_fim]").val()
            +"&_x_i_apontamento_idprojeto="+$("[name=apontamento_idprojeto]").val()
            +"&_x_i_apontamento_idtarefa="+$("[name=apontamento_idtarefa]").val()
            +"&_x_i_apontamento_idpessoa="+$("[name=apontamento_idpessoa]").val()
            +"&_x_i_apontamento_local="+$("[name=apontamento_local]").val();
    
    CB.post({
        objetos: str       
    }); 
}
        
function pesquisar(){
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var executor = $("[name=executor]").val();  
 
    var str="vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&executor="+executor;
    
    CB.go(str);
}

$(document).keypress(function(e) {
  if(e.which == 13) {
    pesquisar();
  }
});
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>


