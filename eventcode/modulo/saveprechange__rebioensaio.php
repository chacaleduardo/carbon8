<?
//print_r($_SESSION['arrpostbuffer']['x']['u']['servicoensaio']['idservicoensaio']); die();


$idservicoensaio=$_SESSION['arrpostbuffer']['x']['u']['servicoensaio']['idservicoensaio'];
$status=$_SESSION['arrpostbuffer']['x']['u']['servicoensaio']['status'];
$servico=$_SESSION['arrpostbuffer']['x']['u']['servicoensaio']['servico'];
$idbioensaio=$_SESSION['arrpostbuffer']['x']['u']['servicoensaio']['idobjeto'];
$tipoobjeto=$_SESSION['arrpostbuffer']['x']['u']['servicoensaio']['tipoobjeto'];



//finalizar servicos e bioensaio
if($servico=="ABATE" and $status=="CONCLUIDO" and !empty($idservicoensaio) and !empty($idbioensaio) and $tipoobjeto=="bioensaio" ){
	$sqlup="update bioensaio 
                set status = 'FINALIZADO',alteradopor='".$_SESSION["SESSAO"]["USUARIO"]."',alteradoem=sysdate()  
                where idbioensaio=".$idbioensaio;
	$resup=d::b()->query($sqlup) or die("Erro ao finalizar o bioensaio sql=".$sqlup);

	$sqlup1="update servicoensaio 
                set status = 'CONCLUIDO',alteradopor='".$_SESSION["SESSAO"]["USUARIO"]."',alteradoem=sysdate() 
                 where status='PENDENTE' 
                 and tipoobjeto = 'bioensaio'
                 and idobjeto=".$idbioensaio;
	$resup1=d::b()->query($sqlup1) or die("Erro ao finalizar servicoensaio  sql=".$sqlup1);
        
        $sqlup2="UPDATE localensaio l set l.status ='FINALIZADO'
                where l.status!='FINALIZADO'
                and l.idbioensaio=".$idbioensaio;
	$resup2=d::b()->query($sqlup2) or die("Erro liberar localensaio  sql=".$sqlup2);
}
?>
