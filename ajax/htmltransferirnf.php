<?
require_once("../inc/php/functions.php");

$idnf= $_GET['idnf']; 

if(empty($idnf)){
	die("Identificação da nota não enviada");
}
//echo($idnf);
?>


    <?
        $sqln="select idpessoatransf,idempresatransf,statustransf,idfinalidadeprodserv,idpessoa from nf
               where idnf=".$idnf." ";
	$resn=d::b()->query($sqln) or die("erro ao buscar dados da nf  no banco de dados sql=".$sqln);
        $row=mysqli_fetch_assoc($resn);
        if(empty($row['idpessoatransf'])){
            $row['idpessoatransf']=$row['idpessoa'];
        }
        if(empty($row['idfinalidadeprodserv'])){
            $row['idfinalidadeprodserv']=3;
        }
        $row['idempresatransf']=cb::idempresa();
?>

<div class="row">
            <div class="col-md-12">
	    <div class="panel panel-default" >
	    <div class="panel-heading">Informações de Entrada</div>
	    <div class="panel-body"  >	
                <table>
                    <?/*if($row['statustransf']=='PENDENTE'){?>
                    <tr>
                        <td>Status Transferência:</td>
                        <td> 
                            <label class="alert-warning">PENDENTE</label>
                          </td>
                    </tr>
                     <?}*/?>  
                <tr>                                
                    <td align="right">Finalidade:</td>
		    <td >
                        <select id="idfinalidadeprodserv"  name="_dev_u_nf_idfinalidadeprodserv" class='size30' vnulo>
                        <option value=""></option>
                        <?fillselect("select c.idfinalidadeprodserv,c.finalidadeprodserv
                                        from finalidadeprodserv c
                                        where c.status='ATIVO'                                       
                                        order by c.finalidadeprodserv",$row['idfinalidadeprodserv']);?>
                        </select>
                        <input name="_dev_u_nf_idempresatransf"	id="idnf"	type="hidden"	value="<?=$row['idempresatransf']?>"	readonly='readonly'>	
                    </td>        
                </tr>
                <!--tr>                                
                    <td align="right">Empresa de Destino:</td>
		            <td >
                        <select id="idempresaenv"  name="_dev_u_nf_idempresatransf" class='size30' vnulo>
                        <option value=""></option>
                        <?fillselect("select idempresa,nomefantasia 
                                from empresa 
                                where status='ATIVO' 
                                -- and idempresa !=".cb::idempresa()."
                                order by nomefantasia",$row['idempresatransf']);?>
                        </select>
                    </td>        
                </tr -->
                <tr>                                
                    <td align="right">Emitente da NF:</td>
		            <td >
                        <?
                        if(empty($row['idempresatransf'])){
                        ?>
                        <select id="idpessoaenv"  name="_dev_u_nf_idpessoatransf" class='size30' vnulo>
                            <option value=""></option>                     
                        </select>
                        <?
                        }else{
                        ?>
                        <select id="idpessoaenv"  name="_dev_u_nf_idpessoatransf" class='size30' vnulo>
                             <?fillselect("select 
                                            p.idpessoa,
                                            if(p.cpfcnpj !='',concat(p.nome,' - ',p.cpfcnpj),p.nome) as nome
                                            from pessoa p
                                            where p.idtipopessoa in (5,2) 
                                                and p.status = 'ATIVO'
                                                and p.idempresa = ".$row['idempresatransf']." order by nome"
                              ,$row['idpessoatransf']);?>                    
                        </select>
                        <?}?>
                    </td>        
                </tr>
                </table>
            </div>
            </div>
            </div>
</div>
<?
/*
$sqlx="select qtd,un,prodservdescr
                from nfitem 
               where idnf=".$idnf." and nfe='Y' and tiponf='V' order by prodservdescr";
	$resx=d::b()->query($sqlx) or die("erro ao buscar itens  no banco de dados sql=".$sqlx);
	$qtdx=mysqli_num_rows($resx);
	//$i=1;
	if($qtdx>0){
?>	    
        <div class="row">
            <div class="col-md-12">
	    <div class="panel panel-default" >
	    <div class="panel-heading">Itens </div>
	    <div class="panel-body"  >	

	    <table  class="table table-striped planilha" id='itensdev' >	
		<tr >
		    <th style="text-align: right !important;">Qtd</th>
                    <th style="text-align: right !important;">Un</th>
                    <th>Produto</th>
		</tr>
	<?
	while($rowx=mysqli_fetch_assoc($resx)){
            $i = $i+1;
	?>	
		<tr class="respreto" >
		   
		    <td align="right">
                        <?=number_format(tratanumero($rowx['qtd']), 2, ',', '.'); ?>                        
                    </td>
                    <td  align="right">
                        <?=$rowx['un']?>                        
                    </td>
                    <td>                        
                        <?=$rowx['prodservdescr']?>
                    </td>
                </tr>
	<?}?>		
               
            
		</table>

           
               
                </div>
	    </div>
        </div>
        </div>
<?
                   
	}//if($qtdx>0){       
    */    
?>
<script>
  $("[name=_dev_u_nf_idempresatransf]").change(function(){
     debugger;
        preencheemitente();
}
);
</script>