<?
require_once("../inc/php/validaacesso.php");
if ($_POST['idnfitem'] or $_POST['idnf']) {
    if ($_POST['idnf']) {
        $sql='SELECT 
            sum(i.total+i.valipi+(i.des*i.qtd)) as totalsemdesc,
            sum((i.des*i.qtd)) as desconto,
            sum((i.total+i.valipi)) as totalcomdesc
        from nfitem i
        where 1
        '.getidempresa('i.idempresa','cotacao').'
        and i.nfe="Y" 
        and i.idnf='.$_POST['idnf'];
    }else {
        $sql='SELECT 
   sum(i1.total+i1.valipi+(i1.des*i1.qtd)) as totalsemdesc,
   sum((i1.des*i1.qtd)) as desconto,
   sum((i1.total+i1.valipi)) as totalcomdesc
   from nfitem i join nfitem i1 on(i1.idnf=i.idnf) 
   where 1
   '.getidempresa('i.idempresa','cotacao').'
   and i1.nfe="Y" 
   and i.idnfitem='.$_POST['idnfitem'];;
    }
   $res=d::b()->query($sql) or die("Erro ao buscar total da NF: SQL=> ".mysqli_error(d::b()).$sql);
   if(mysqli_num_rows($res)>0){
       $row = mysqli_fetch_assoc($res);
       echo json_encode($row);
   }
}
?>