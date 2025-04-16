<?
require_once("../inc/php/functions.php");
require_once("../form/controllers/farmacovigilancia_controller.php");

$idpessoa = $_GET['idpessoa']; 
$dados = $_GET['dados']; 

if(empty($idpessoa)){
	die("CLIENTE OU FORNECEDOR NAO ENVIADO");
}

if($dados == 'Y'){
        $enderecos = FarmacovigilanciaController::buscarEnderecoPorIdpessoaOption($idpessoa);
        foreach($enderecos as $_endereco) {
                $array['endereco'] .= "<option value='".$_endereco["idendereco"]."'>".$_endereco["endereco"]."</option>";
                $telefone = $_endereco["telefone"];
                $email = $_endereco["email"];
        }
        
        $array['telefone'] = $telefone;
        $array['email'] = $email;
        echo json_encode($array);

} else {
        $sql= "SELECT e.idendereco,concat(t.tipoendereco,'-',e.endereco,'-',e.uf) as endereco 
                from endereco e,tipoendereco t 
                where t.idtipoendereco = e.idtipoendereco 
                and (e.idtipoendereco =3 or t.faturamento='Y')
                and e.status = 'ATIVO'
                and e.idpessoa =".$idpessoa;
        $res =  d::b()->query($sql) or die("Erro ao buscar endereco: ".mysqli_error(d::b()));
        echo "<option value='' selected></option>";
        while($r = mysqli_fetch_assoc($res)) {
                echo "<option value='".$r["idendereco"]."'>".$r["endereco"]."</option>"; 
        }
}
?>


