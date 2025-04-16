<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
	require_once("../inc/php/cbpost.php");
}
?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Conferência de Amostra</div>
        <div class="panel-body" >
            <table>
                <tbody>
                    <tr>
                        <td>Cliente</td><td></td>
                        <td colspan="3">
                        <input name="cliente" id="cliente" class="" col="cliente" >
                        </td>
                    </tr>
                    <tr>
                        <td>Exercício</td><td></td>
                        <td colspan="3">
                        <input name="exercicio" id="exercicio" class="ui-autocomplete-input" col="exercicio" value="<?=date('Y');?>" autocomplete="off">
                        </td>
                    </tr>
                    <tr>
                        <td>Registro</td><td></td>
                        <td colspan="3" class="nowrap">
                        <input name="registro_1" class="size10" id="registro_1" class="" col="registro_1" >  e
                        <input name="registro_2" class="size10" id="registro_2" class="" col="registro_2" > 
                        </td>
                    </tr>
                    <tr>
                        <td>Registro Provisório</td><td></td>
                        <td colspan="3" class="nowrap">
                        <input name="registrop_1" class="size10" id="registro_1" class="" col="registrop_1" >  e
                        <input name="registrop_2" class="size10" id="registro_2" class="" col="registrop_2" > 
                        </td>
                    </tr>
                    <tr>
                        <td>Status</td><td></td>
                        <td colspan="3">
                            <select name="status" id="status" >
                              <option value=""></option>
                            <?
                            fillselect("SELECT 'CONFERIDO','Conferido' ");
                            ?>
                            
                            </select>                       
                    </tr>
					<tr>
                        <td>Oficial</td><td></td>
                        <td colspan="3">
                            <select name="flgoficial" id="flgoficial" >
                              <option value=""></option>
							  <option value="Y">Sim</option>
							  <option value="N">Não</option>
                            </select>                       
                    </tr>
                </tbody>
            </table>
	<div class="row"> 
	    <div class="col-md-8">
		
	    </div>
	    <div class="col-md-2">
		<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
		    <span class="fa fa-search"></span>
		</button> 
	    </div>	   
	</div>
	</div>
    </div>
    </div>
</div>
<script>
//ler teclas digitas
$(document).ready(function()
{// listens for any navigation keypress activity
    $(document).keypress(function(e)
    {
        if(e.keyCode==13){//Enter 
            pesquisar();									
        }
    });
});    
    
    
    
function pesquisar(){
    var str='form/confereamostra.php?registro_1='+$('#registro_1').val()+'&registro_2='+$('#registro_2').val()+'&cliente='+$('#cliente').val()+'&status='+$('#status').val()+'&exercicio='+$('#exercicio').val()+'&flgoficial='+$('#flgoficial').val()
    janelamodal(str);
}
</script>
