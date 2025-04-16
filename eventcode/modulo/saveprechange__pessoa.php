<?
$status = $_SESSION['arrpostbuffer']['1']['u']['pessoa']['status'];
$idpessoa = $_SESSION['arrpostbuffer']['1']['u']['pessoa']['idpessoa'];
//@524151 - NAO PERMITIR INATIVAÇÃO DE FORNECEDOR COM PEDIDO EM ANDAMENTO
if($status=='INATIVO' and !empty($idpessoa)){
    $sql = "select * from nf where idpessoa=".$idpessoa." and status not in('CONCLUIDO','CANCELADO','REPROVADO','DEVOLVIDO','RECUSADO')";
    $res=d::b()->query($sql) or die('Erro ao buscar se tem nf em aberto sql='.$sql);
    $qtdaberto=mysqli_num_rows($res);
    if($qtdaberto>0){
            while($row=mysqli_fetch_assoc($res)){
                    echo("NF ID:".$row['idnf']." tipo:".$row['tiponf']." status:".$row['tiponf']." \n");
            }

        die('Não é possivel inativar por o mesmo possui Nota Fiscal em Aberto');
    }
}

?>