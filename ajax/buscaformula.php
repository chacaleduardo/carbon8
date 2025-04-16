<? require_once("../inc/php/functions.php");

$idprodserv= $_GET['idprodserv']; 

if(empty($idprodserv)){
	die("PRODUTO NAO ENVIADO");
}

        $sql= "select idprodservformula,Concat(rotulo,' (',volumeformula,' ',un,')') as rotulo
                from prodservformula 
                where status='ATIVO' and idprodserv = ".$idprodserv." order by rotulo";
        $res =  d::b()->query($sql) or die("Erro ao buscar formula: ".mysqli_error(d::b()));
        $qtdres=mysqli_num_rows( $res);
        if($qtdres>0){
                echo "<option value='' selected></option>";
                while($r = mysqli_fetch_assoc($res)) {
                echo "<option value='".$r["idprodservformula"]."'>".$r["rotulo"]."</option>"; 
                } 
        }else{
                echo "VAZIO";
        }
                        
      
?>


