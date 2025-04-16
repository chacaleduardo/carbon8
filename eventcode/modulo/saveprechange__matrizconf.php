<?
$idmatrizconf = $_SESSION['arrpostbuffer']['x']['d']['matrizconf']['idmatrizconf'];
$_idmatriz = $_SESSION['arrpostbuffer']['1']['i']['matrizconf']['idmatriz'];
$_idpessoa = $_SESSION['arrpostbuffer']['1']['i']['matrizconf']['idpessoa'];

if(empty($_SESSION['arrpostbuffer']['x']['d']['matrizpermissao']['idmatrizobj']) && !empty($_idmatriz))
{
    unset($_SESSION['arrpostbuffer']);
}

if(!empty($idmatrizconf))
{
    $sqlMatrizConf = "SELECT idmatriz, idempresa
                        FROM matrizconf
                       WHERE idmatrizconf = '$idmatrizconf'";
	$resMatrizConf = d::b()->query($sqlMatrizConf) or die("Erro ao consultar Pessoa Matriz: ".$sqlMatrizConf);
    $rowMatrizConf = mysqli_fetch_assoc($resMatrizConf);

    $sqlDelteMatriz = "DELETE FROM matrizpermissao WHERE idmatriz = ".$rowMatrizConf['idmatriz']." AND idempresa = ".$rowMatrizConf['idempresa'].";";
    $res = d::b()->query($sqlDelteMatriz) or die("Erro ao excluir assinatura: ".mysql_error(d::b())." $sqlDelteMatriz");
}

if(!empty($_idmatriz) && !empty($_idpessoa))
{
    $sqlMatrizConf = "SELECT idmatrizconf, idempresa
                        FROM matrizconf
                       WHERE idmatriz = '$_idmatriz'";
	$resMatrizConf = d::b()->query($sqlMatrizConf) or die("Erro ao consultar Pessoa Matriz Pessoa: ".$sqlMatrizConf);
    while($rowMatrizConf = mysqli_fetch_assoc($resMatrizConf))
    {
         $sqlObjempresa = "SELECT idobjempresa
                             FROM objempresa
                            WHERE idempresa = '".$rowMatrizConf['idempresa']."'
                              AND idobjeto = '$_idpessoa' AND objeto = 'pessoa'";
	    $resObjempresa = d::b()->query($sqlObjempresa) or die("Erro ao consultar objempresa: ".$sqlObjempresa);
        $rowObjempresa = mysqli_fetch_assoc($resObjempresa);
        if(!empty($rowObjempresa['idobjempresa']))
        {
            $_SESSION['arrpostbuffer'][$rowMatrizConf['idmatrizconf']]['i']['matrizpermissao']['idmatriz'] = $_idmatriz;
            $_SESSION['arrpostbuffer'][$rowMatrizConf['idmatrizconf']]['i']['matrizpermissao']['idempresa'] = $rowMatrizConf['idempresa'];
            $_SESSION['arrpostbuffer'][$rowMatrizConf['idmatrizconf']]['i']['matrizpermissao']['idpessoa'] = $_idpessoa;
        }
    }
}
?>