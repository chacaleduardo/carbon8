<script src="../inc/js/carbon.js"></script>
<script src="../inc/js/functions.js"></script>
<?

require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$exercicio_1 	= $_GET["exercicio_1"];
$exercicio_2 	= $_GET["exercicio_2"];
$idpessoa=$_GET["idpessoa"];

if (!empty($exercicio_1) or !empty($exercicio_2)){
    $clausulad .= "(exercicio  BETWEEN '" . $exercicio_1 ."' and '" .$exercicio_2 ."') and ";	
}//if (!empty($vencimento_1) or !empty($vencimento_2)){

if($idpessoa){
    $clausulad .= " idpessoa=".$idpessoa." and ";
}

if($nome){
     $clausulad .= " nome like ('%".$nome."%') and ";
}


if($_GET and !empty($clausulad)){
    $sql="select * from vwquantfechcliente where ".$clausulad." ".getidempresa('idempresa','funcionario')." order by nome";
    echo "<!--";
    echo $sql;
    echo "-->";
    $res=d::b()->query($sql) or die("Erro ao buscar resultados sql=".$sql);
    $qtdrows=mysqli_num_rows($res);
 ?>

    <div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Resultado da pequisa - (<?=$qtdrows?>)</div>
        <div class="panel-body">
<?
	if($qtdrows>0){
?>
           <table class="table table-striped planilha">
            <thead>
            <tr>         
                <th>ID Cliente</th>  
                <th >Cliente</th>                
                <th >Fechados</th>
                <th >Abertos</th>
                <th >Menor Data</th>               
                <th >Maior Data</th>
                <th >Exercicio</th>  
		<th></th>
            </tr>
            </thead>
            <tbody>
<?
      
        while($row=mysqli_fetch_assoc($res)){
            $i=$i+1;
 ?>
            <tr> 
                <td>
		    <a  class="pointer hoverazul" title="Nota" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row['idpessoa']?>')">
                        <?=$row["idpessoa"]?>
                    </a>
                </td>   
                <td >		     
		    <?=$row["nome"]?>		   
		</td>                
                <td >		   
		    <?=$row["fechados"]?>		   
		</td>
                <td ><?=$row["abertos"]?></td>
                <td ><?=dma($row["menordata"])?></td>
		<td ><?=dma($row["maiordata"])?></td>
                <td ><?=$row["exercicio"]?></td>
               
                <td>
                    
                </td>		
            </tr>
<?
        }// while($row=mysql_fetch_assoc($res)){ 
?>
            

            </tbody>
            </table>
<?
    }else{//if($qtdrows>0){

      echo("Não foram encontradas parcelas nestas condiçàµes.");

    }//if($qtdrows>0){
  ?>
        </div>
    </div>
    </div>
</div>
<?	
    
}
?>