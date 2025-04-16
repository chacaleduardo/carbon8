<?

//Gerar a configuração das parcelas 
$idnfparc=$_SESSION['arrpostbuffer']['parc']['u']['nf']['idnf'];
$parc = $_SESSION['arrpostbuffer']['parc']['u']['nf']['parcelas'];
$diasentrada = $_SESSION['arrpostbuffer']['parc']['u']['nf']['diasentrada'];
$intervalo = $_SESSION['arrpostbuffer']['parc']['u']['nf']['intervalo'];

if(!empty($idnfparc) and !empty($parc) and !empty($diasentrada))
{    
    $sql="update nfconfpagar set proporcao=null where idnf=".$idnfparc;
    $res=d::b()->query($sql) or die("Falha ao zerar a proporcao de pagamento sql=".$sql);
       
    $sql="select * from nfconfpagar where idnf=".$idnfparc." order by idnfconfpagar desc";
    $res=d::b()->query($sql) or die("Falha ao buscar configuracoes de pagamento sql=".$sql);
    $qtd=mysqli_num_rows($res);
    
    if($qtd > $parc){
        
        while($row=mysqli_fetch_assoc($res)){
            $sqld="delete  from nfconfpagar where idnfconfpagar=".$row['idnfconfpagar'];
            $resd=d::b()->query($sqld) or die("Falha ao retirar configuracao de pagamento excedente sql=".$sqld);
            $qtd=$qtd-1;
            if($qtd==$parc){
                break; 
            }
        }
    }elseif($qtd < $parc){
       
        for($v = 1; $v < $parc; $v++) 
        {
            $strintervalo = 'DAY';
            $vencimentocalc = '';
            $valintervalo = ($v == 1) ? $diasentrada + $intervalo : ($valintervalo + $intervalo); 
            if($v >= $qtd)
            {
                if (($v == 1 && $qtd != 1) || ($v == 1 && $qtd = 0)) {
                    $diareceb = $diasentrada;
                    $vencimentocalc = date('Y-m-d ', strtotime("+".$diareceb." $strintervalo", strtotime(date("Y-m-d H:i:s"))));       
                } elseif(!empty($intervalo)) {
                    $diareceb = $valintervalo;                     
                    $vencimentocalc = date('Y-m-d ', strtotime("+$diareceb $strintervalo", strtotime(date("Y-m-d H:i:s"))));      
                }
                
                if(!empty($vencimentocalc)) {
                    $insnfconfpagar = new Insert();
                    $insnfconfpagar->setTable("nfconfpagar");            
                    $insnfconfpagar->idnf = $idnfparc; 
                    $insnfconfpagar->datareceb = $vencimentocalc; 
                    $idnfconfpagar = $insnfconfpagar->save();
                }                
            } 
        }
         
    }
    
}//if(!empty($idnfparc) and !empty($parc)){

//dividir o frete nos itens da compra
if(!empty($_SESSION['arrpostbuffer']['atfrete']['u']['nf']['idnf'])){

    $idnf = $_SESSION['arrpostbuffer']['atfrete']['u']['nf']['idnf'];
    $frete = $_SESSION['arrpostbuffer']['atfrete']['u']['nf']['frete'];
    $frete = number_format(tratanumero($frete), 4, '.','');

    $sql="select i.idnfitem,round((i.total/n.subtotal)*". $frete.",2)  as novofrete 
        from nfitem i join nf n on(n.idnf=i.idnf)
        where i.nfe ='Y' and  i.idnf=".$idnf;
    $res = d::b()->query($sql) or die("[prechangepedido][3]: Erro ao calcular frete para os itens CotacaoForn. SQL: ".$sql);
    $l=0;
    while($row= mysqli_fetch_assoc($res)){
        $l++;
        $_SESSION['arrpostbuffer']['atfrete'.$l]['u']['nfitem']['idnfitem']=$row['idnfitem'];
        $_SESSION['arrpostbuffer']['atfrete'.$l]['u']['nfitem']['frete']=$row['novofrete'];
    }

}
?>