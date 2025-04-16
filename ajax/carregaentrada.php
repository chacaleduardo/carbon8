<?
require_once("../inc/php/functions.php");

// Verifique se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receba o corpo da requisição
    $dados = file_get_contents("php://input");

    // Agora você pode trabalhar com os dados do corpo da requisição
    // Por exemplo, você pode convertê-los para um array associativo
    $POST = json_decode($dados, true);

    // Faça algo com os dados, como exibir ou processar
    print_r($POST);
} else {
    // Se a requisição não for 'POST', você pode lidar com isso de acordo com seus requisitos
    echo "Essa página aceita apenas requisições POST.";
}

$caminho='';
$funcao=$POST['funcao'];
$error=$POST['error'];

$arquivo=$POST['arquivo'];
$status=$POST['status'];
$cstatus=$POST['cstatus'];
$chave=$POST['chave'];
$chaveref=$POST['chaveref'];
$valor=$POST['valor'];
$dtemissao=$POST['dtemissao'];
$cnpjemitente=$POST['cnpjemitente'];
$cnpjempresa=$POST['cnpjempresa'];
$xml=$POST['xml'];
$natop=$POST['natop'];

$postenviado= implode(" ",$POST);

$xml = str_replace("'", "",$xml);



$grupo = rstr(8);

if(empty($chave) or empty($cnpjempresa) or empty($cnpjemitente) or empty($funcao) or empty($status) or empty($xml)){    

    if(empty($chave)){

        $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
        VALUES ('1', '".$grupo."', 'carregaentrada', 'carregaentrada', 'status', 'A chave do danfe não enviada. - ".$error."', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

        header("HTTP/1.1 400 A chave do danfe não enviada.");
        http_response_code(400);
        die('A chave do danfe não enviada.');

    }
    if(empty($cnpjempresa)){

        $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
        VALUES ('1', '".$grupo."', 'carregaentrada', 'carregaentrada', 'status', 'A cnpjempresa do danfe não enviada ".$chave."- ".$error.".', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

        header("HTTP/1.1 400 A cnpjempresa do danfe não enviada.");
        http_response_code(400); 
        die('A cnpjempresa do danfe não enviada.');
    }
    if(empty($cnpjemitente)){
        $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
        VALUES ('1', '".$grupo."', 'carregaentrada', 'carregaentrada', 'status', 'A cnpjemitente do danfe não enviada ".$chave."- ".$error.".', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

        header("HTTP/1.1 400 A cnpjemitente do danfe não enviada.");
        http_response_code(400); 
        die('A cnpjemitente do danfe não enviada.');
    }
    if(empty($funcao)){
        $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
        VALUES ('1', '".$grupo."', 'carregaentrada', 'carregaentrada', 'status', 'A funcao do danfe não enviada ".$chave."- ".$error.".', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);

        header("HTTP/1.1 400 A funcao do danfe não enviada.");
        http_response_code(400); 
        die('A funcao do danfe não enviada.');
    }
    if(empty($status)){

        $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
        VALUES ('1', '".$grupo."', 'carregaentrada', 'carregaentrada', 'status', 'A status do danfe não enviada ".$chave."- ".$error.".', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
        header("HTTP/1.1 400 A status do danfe não enviada.");
        http_response_code(400); 
        die('A status do danfe não enviada.');
    }
    if(empty($xml)){

        $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
        VALUES ('1', '".$grupo."', 'carregaentrada', 'carregaentrada', 'status', 'A xml do danfe não enviada ".$chave."- ".$error.".', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
        header("HTTP/1.1 400 A xml do danfe não enviada.");
        http_response_code(400); 
        die('A xml do danfe não enviada.');
    }


 

}else{
   // $xml =file_get_contents("../".$arquivo);

    $sqle="select * from empresa where status='ATIVO' and cnpj='".$cnpjempresa."' limit 1";
    $rese=d::b()->query($sqle) ;
    $rowe=mysqli_fetch_assoc($rese);
    $qtde=mysqli_num_rows($rese);

    if($qtde>0 and !empty($rowe['idempresa'])){
        $idempresa=$rowe['idempresa'];  
    }else{
      
        $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
        VALUES ('1','".$grupo."', 'carregaentrada', 'carregaentrada', 'status', 'Erro ao identificar a empresa.- ".$error."', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
        header("HTTP/1.1 400 Erro ao identificar a empresa");
        echo('Erro ao identificar a empresa');
        http_response_code(400);
        die;
    }

    $sql="select * from nfentradaxml where chave='".$chave."' and tipo='".$funcao."'";
    $res=d::b()->query($sql);
    $row=mysqli_fetch_assoc($res);
    $qtdxml=mysqli_num_rows($res);

    if($qtdxml<1){
        $sqlex="INSERT INTO nfentradaxml
        (idempresa,tipo,chave,chaveref,dtemissao,cpfcnpj,valor,xml,cstatus,status,mensagem_erro,natop,criadopor,criadoem,alteradopor,alteradoem)
        VALUES
        (".$idempresa.",'".$funcao."','".$chave."','".$chaveref."','".$dtemissao."','".$cnpjemitente."','".$valor."','".$xml."','".$cstatus."','".$status."','".$error."','".$natop."','carregaentrada',now(),'carregaentrada',now())";
    }else{
        if($row['status'] == 'CANCELADO' or  $row['status'] == 'RECUSADO'){
            $sqlex="update  nfentradaxml 
            set dtemissao='".$dtemissao."'
            ,valor='".$valor."'
            ,xml='".$xml."'      
            ,cpfcnpj='".$cnpjemitente."'  
            ,cstatus='".$cstatus."'            
            ,mensagem_erro='".$error."'
            ,chaveref='".$chaveref."'
            ,natop='".$natop."'
            ,alteradoem=now() 
            where idnfentradaxml=".$row['idnfentradaxml'];

        }else{

            $sqlex="update  nfentradaxml 
            set dtemissao='".$dtemissao."'
            ,valor='".$valor."'
            ,xml='".$xml."'      
            ,cpfcnpj='".$cnpjemitente."'   
            ,cstatus='".$cstatus."' 
            ,status='".$status."'
            ,mensagem_erro='".$error."'
            ,chaveref='".$chaveref."'
            ,natop='".$natop."'
            ,alteradoem=now() 
            where idnfentradaxml=".$row['idnfentradaxml'];

        }
      
    }

    $resex=d::b()->query($sqlex);

    if(!$resex){
      
        $sqli = "INSERT INTO laudo.log (idempresa, sessao, tipoobjeto, idobjeto, tipolog, log, status, criadoem, data) 
        VALUES ('1', '".$grupo."', 'carregaentrada', 'carregaentrada', 'status', 'Erro ao identificar a empresa. - ".$funcao." ".$chave." ', 'ERRO', now(), DATE_FORMAT(NOW(), '%Y-%m-%d'))";
        d::b()->query($sqli) or die("erro ao inserir log: ".mysqli_error(d::b())."<br>".$sqli);
        header("HTTP/1.1 400 Erro ao executar SQL.");
        echo("Erro ao executar SQL: ".$sqlex);
        http_response_code(400);
        die;
    }else{

        // VINCULAR NFE SE JA LANÇADA
			$sqlx="select * from nfentradaxml x  where tipo='".$funcao."' and chave ='".$chave."' and idnf is null";
			$resx =  d::b()->query($sqlx);  
			$qtdbx = mysqli_num_rows($resx);

			if($qtdbx > 0){
				$rowx=mysqli_fetch_assoc($resx);

                $sql="select * from nf n where  replace(n.idnfe,' ','') ='".$chave."' ";
                $res =  d::b()->query($sql);  
                $qtdb = mysqli_num_rows($res);
        
                if($qtdb > 0){
                    $row=mysqli_fetch_assoc($res);
        
                    $sqlU="update nfentradaxml set idnf='".$row['idnf']."'where idnfentradaxml= ".$rowx['idnfentradaxml'];
                    $resU =  d::b()->query($sqlU);  
                }    
			}




        echo("Atualizado com sucesso");
    }
    
}




?>