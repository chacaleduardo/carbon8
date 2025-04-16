<?
require_once("../inc/php/functions.php");

$idEtl = $_GET['idetl'];
$_idrep = $_GET['_idrep'];

if($idEtl)
{
    // ETL - Faturamento
    if($_idrep == 196)
    {
        $query = " select nf.idnf as id, dma(nf.dtemissao) as dmadata, nf.total as valor, p.nome, nf.status,concat('?_modulo=pedido&_acao=u&idnf=',nf.idnf) as url 
            from etlitem et 
            join nf on(et.idobjeto=nf.idnf)
            left join pessoa p on (p.idpessoa = nf.idpessoa)
            where et.idetl = {$idEtl}
            order by p.nome is not null DESC;
        ";
    } elseif($_idrep == 197)
    {
        // ETL - Almoxarifado
        $query = "select l.idlote as id,dma(l.vencimento) as dmadata , l.vlrlote as valor, p.descr as nome, l.status,concat('?_modulo=lotealmoxarifado&_acao=u&idlote=',l.idlote) as url
            from etlitem et 
            join lote l on(et.idobjeto=l.idlote)
            left join prodserv p on (p.idprodserv = l.idprodserv)
            where et.idetl =  {$idEtl} 
            order by p.descr is not null DESC;
        ";
    }
    else 
    {
        $query = "select et.idobjeto as  id, ifnull(dma(c.datareceb),'') as dmadata,et.valor as valor, p.nome, c.status,concat('?_modulo=contapagar&_acao=u&idcontapagar=',c.idcontapagar) as url
            from etlitem et 
             join contapagar c on(et.idobjeto=c.idcontapagar)
             join pessoa p on (p.idpessoa = c.idpessoa)
            where et.idetl = {$idEtl}
            order by p.nome is not null DESC";
    }

    $resultEtl = d::b()->query($query);

    $respJson = [];
    $i = 0;

    while($row = mysql_fetch_assoc($resultEtl))
    {
        $respJson[$i]['id'] = $row['id'];
        $respJson[$i]['valor'] = $row['valor'];
        $respJson[$i]['nome'] = $row['nome'];
        $respJson[$i]['dmadata'] = $row['dmadata'];
        $respJson[$i]['url'] = $row['url'];
        $respJson[$i]['status'] = $row['status'];

        $i++;
    }

    echo json_encode($respJson);
}

?>