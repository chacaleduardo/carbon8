<?
require_once("../inc/php/functions.php");

$idnf= $_GET['idnf']; 

if(empty($idnf)){
	die("Identificação da nota não enviada");
}
//echo($idnf);
?>



<?
$sqlx="select qtd,un,prodservdescr,idnfitem
                from nfitem 
               where idnf=".$idnf." 
                   -- and nfe='Y' 
                and tiponf='V' order by prodservdescr";
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
		    <th style="text-align: center !important;">Qtd</th>
                    <th style="text-align: center !important;">Un</th>
                    <th>Produto</th>
		</tr>
	<?
	while($rowx=mysqli_fetch_assoc($resx)){
            $i = $i+1;
	?>	
		<tr class="respreto" >
		   
		    <td align="center" class="size10">                       
                        <input name="_<?=$i?>_u_nfitem_qtd" class="size8" type="text"  value="<?=$rowx['qtd']?>" >
                        <input name="_<?=$i?>_u_nfitem_idnfitem"  type="hidden"  value="<?=$rowx['idnfitem']?>" >
                    </td>
                    <td  align="center" class="size5">
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
        
?>
<script>
 
</script>