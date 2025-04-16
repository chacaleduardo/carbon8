<?
require_once("../inc/php/validaacesso.php");

$ids = implode(',',$_POST['ids']);
$inativos=$_POST['inativos'];

if($inativos=='N'){
    $funcativo=" and status='ATIVO' ";
}else{
    $funcativo=" ";    
}


$arr = array();
$arr['pessoas'];
$arr['departamentos'];
if(!empty($ids)){
    $sqlPessoas = "SELECT 
        idpessoa,concat(nomecurto,if(status='INATIVO',' (INATIVO)','')) as nomecurto
        from pessoa 
        where idtipopessoa=1 
        ".$funcativo." 
        and idempresa in (".$ids.")
        and contrato='CLT' order by nomecurto";
    $resPessoas = d::b()->query($sqlPessoas) or die("Erro buscar funcionarios ajax sql:".$sqlPessoas);
    while ($rowm = mysqli_fetch_assoc($resPessoas)) {
        $arr['pessoas'] .= '<option data-tokens="'.retira_acentos($rowm['nomecurto']).'" value="'.$rowm['idpessoa'].'" >'.$rowm['nomecurto'].'</option>'; 
    }
    $sqlDepartamentos = "SELECT 
        idsgdepartamento,departamento
        from sgdepartamento
        where status='ATIVO' 
        and idempresa in (".$ids.") 
        order by  departamento";
    $resDepartamento = d::b()->query($sqlDepartamentos) or die("Erro buscar Departamentos ajax sql:".$sqlDepartamentos);
    while ($rowm = mysqli_fetch_assoc($resDepartamento)) {
        $arr['departamentos'] .= '<option data-tokens="'.retira_acentos($rowm['departamento']).'" value="'.$rowm['idsgdepartamento'].'" >'.$rowm['departamento'].'</option>'; 
    }
}

echo json_encode($arr);

?>