<?
//$ret=AtualizaServicoensaio($iu,$_SESSION["_pkid"],'ficharep',$idespeciefinalidade,'especiefinalidade',$rowper['datafim']);
$idficharep = $_SESSION['arrpostbuffer']['x']['i']['bioensaio']['idficharep'];
$idbioensaio = $_SESSION['arrscriptsql']['x']['bioensaio']['insertid'];
$ficharep = $_SESSION['arrpostbuffer']['1']['u']['ficharep']['idficharep'];
$inicio = $_SESSION['arrpostbuffer']['1']['u']['ficharep']['inicio'];
$status = $_SESSION['arrpostbuffer']['x']['u']['ficharep']['status'] ?? $_SESSION['arrpostbuffer']['1']['i']['ficharep']['status'];
$oldinicio = $_POST['_ficharep_oldinicio'];

if(!empty($idficharep) and !empty($idbioensaio)){
    $ins = new Insert();
    $ins->setTable("analise");
    $ins->idempresa= cb::idempresa(); 
    $ins->idobjeto=$idbioensaio; 
    $ins->objeto='bioensaio';
    $idanalise=$ins->save();
    
    unset($_SESSION['arrpostbuffer']);
    //insere no localensaio
    $sqlin3="INSERT INTO localensaio
                            (idempresa,status,idanalise,criadopor,criadoem,alteradopor,alteradoem)
                    VALUES
                    (".cb::idempresa().",'PENDENTE',".$idanalise.",'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate(),'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

    $resin3=d::b()->query($sqlin3) or die("[saveposchange_bioensaio]-Erro ao inserir no localensaio sql=".$sqlin3);
}