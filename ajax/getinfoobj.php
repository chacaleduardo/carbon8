<?
require_once("../inc/php/validaacesso.php");
if($_POST['idobjeto'] and $_POST['tipoobjeto'] and $_POST["acao"] == 'MontaInput'){

    if ($_POST['tipoobjeto'] == 'pessoa') {
        $sl='SELECT e.* from objempresa o JOIN empresa e on (e.idempresa = o.empresa and e.status="ATIVO") where idobjeto='.$_POST['idobjeto'].' and objeto="pessoa"';
        $rs = d::b()->query($sl) or die("Erro ao buscar empresa objempresa: ". mysqli_error(d::b()));
        if ($rs) {
            $i=0;
            while($rowemp = mysqli_fetch_assoc($rs)){
                $arr[$i]['sigla'] = $rowemp['sigla'];
                $arr[$i]['idempresa'] = $rowemp['idempresa'];
                $i++;
            }
            $json = json_encode($arr);
            cbSetPostHeader('1','buscaempresa');
            echo($json);
        }
    }else {
        $sl='SELECT e.* from  empresa e where status="ATIVO"';
        $rs = d::b()->query($sl) or die("Erro ao buscar empresa objempresa: ". mysqli_error(d::b()));
        if ($rs) {
            $i=0;
            while($rowemp = mysqli_fetch_assoc($rs)){
                $arr[$i]['sigla'] = $rowemp['sigla'];
                $arr[$i]['idempresa'] = $rowemp['idempresa'];
                $i++;
            }
            $json = json_encode($arr);
            cbSetPostHeader('1','buscaempresa');
            echo($json);
        }
    }
}


if (!empty($_POST['copia_lp_empresa']) and !empty($_POST['copia_lp_descr'])
and !empty($_POST['lp_copiar_id']) and !empty($_POST['copia_lp_rot']) and $_POST['acao'] == 'copiaLP') {
    
    $sigla=traduzid('empresa','idempresa','sigla',$_POST['copia_lp_empresa'],false);
    $inslp = new Insert();
    $inslp->setTable("carbonnovo._lp");
    $inslp->idempresa=$_POST['copia_lp_empresa'];
    $inslp->descricao=$_POST['copia_lp_descr']." ".$sigla." - ".$_POST['copia_lp_rot'];
    $idnewlp=$inslp->save();

    $s1="SELECT * from lpobjeto where idlp=".$_POST['lp_copiar_id']." and tipoobjeto!='lpgrupo'";
    $rs1 = d::b()->query($s1) or die("Erro ao buscar lpobjeto: ". mysqli_error(d::b()));

    while($row1 = mysqli_fetch_assoc($rs1)){
        $inslpobj = new Insert();
        $inslpobj->setTable("lpobjeto");
        $inslpobj->idobjeto=$row1['idobjeto'];
        $inslpobj->tipoobjeto=$row1['tipoobjeto'];
        $inslpobj->idempresa=$_POST['copia_lp_empresa'];
        $inslpobj->idlp=$idnewlp;
        $idnewlpobj=$inslpobj->save();
    }
   

    $inslpobjvinc = new Insert();
    $inslpobjvinc->setTable("lpobjeto");
    $inslpobjvinc->idobjeto=$_POST['idlpgrupo'];
    $inslpobjvinc->tipoobjeto="lpgrupo";
    $inslpobjvinc->idempresa=$_POST['copia_lp_empresa'];
    $inslpobjvinc->idlp=$idnewlp;
    $idnewlpobjvinc=$inslpobjvinc->save();


    $arr = array();
    $i=0;
    $sql="SELECT v.*,e.empresa
    FROM lpobjeto o
    JOIN lpobjeto v ON (v.idlp = o.idlp and v.tipoobjeto !='lpgrupo')
    JOIN empresa e ON (e.idempresa = v.idempresa)
    where o.idobjeto = ".$_POST['idlpgrupo']." and o.tipoobjeto = 'lpgrupo';";
    $res1 = d::b()->query($sql) or die("Erro ao buscar lpobjetos: ". mysqli_error(d::b()));

    while($rowv1 = mysqli_fetch_assoc($res1)){
        $arr[$i]['empresa']=$rowv1['empresa'];
        $arr[$i]['idobj']=$rowv1['idobjeto'];
        $arr[$i]['tobj']=$rowv1['tipoobjeto'];
        $arr[$i]['idempresa']=$rowv1['idempresa'];
        $arr[$i]['idlp']=$rowv1['idlp'];
        $i++;
    }
    $json = json_encode($arr);
    cbSetPostHeader('1','buscaempresa');
    echo($json);


    
}

?>