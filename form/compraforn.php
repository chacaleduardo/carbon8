<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

################################################## Atribuindo o resultado do metodo GET
$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idtipoprodserv = $_GET["idtipoprodserv"];
$idfornecedor   = $_GET["idfornecedor"];
$idprodserv 	= $_GET["idprodserv"];
$orderby        = $_GET["orderby"];
$ascdesc        = $_GET["ascdesc"];

$andini = "";
$andfim = "";
$flg = false;

if(!empty($idtipoprodserv)){
    $virg = "";
    foreach ($idtipoprodserv as $value) {
        $iIdtipoprodserv .= $virg . $value;
        $virg = ",";
    }

    $cIdtipoprodserv = " (t.idtipoprodserv IN (" . $iIdtipoprodserv . ")) ";

    $andini = "AND (";
    $andfim = ")";

    $flg = true;
}else{
    $cIdtipoprodserv = "";
}

if(!empty($idfornecedor)){
    $virg = "";
    foreach ($idfornecedor as $value) {
        $iIdfornecedor .= $virg . $value;
        $virg = ",";
    }

    ($flg) ? $or = " OR " : $or = "";
    
    $cIdfornecedor = $or." (p.idpessoa IN (" . $iIdfornecedor . ")) ";

    $andini = "AND (";
    $andfim = ")";
    $flg = true;
}else{
    $cIdfornecedor = "";
}

if(!empty($idprodserv)){
    $virg = "";
    foreach ($idprodserv as $value) {
        $iIdprodserv .= $virg . $value;
        $virg = ",";
    }

    ($flg) ? $or = " OR " : $or = "";

    $cIdprodserv = $or." (i.idprodserv IN (" . $iIdprodserv . "))";

    $andini = "AND (";
    $andfim = ")";
}else{
    $cIdprodserv = "";
}

if(empty($orderby) OR empty($ascdesc)){
    $orderby = "qtdprod";
    $ascdesc = "desc";
}

?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Filtros para Listagem </div>
            <div class="panel-body">

                <table>

                    <tr>
                        <td>Período entre:</td>
                        <td><input autocomplete="off" id="vencimento_1" class="calendario" value="<?=$vencimento_1?>" autocomplete="off"></td>
                        <td><font>e</font></td>
                        <td><input autocomplete="off" id="vencimento_2" class="calendario" value="<?=$vencimento_2?>" autocomplete="off"></td>
                    </tr>
                
                    <tr>
                        <td align="right">Tipo:</td> 
                        <td colspan="3">
                            <select class='selectpicker' id="_idtipoprodserv_" multiple data-live-search='true' data-selected-text-format="count > 0" data-count-selected-text= "{0} Selecionados">
                                <?fillselect("select idtipoprodserv,tipoprodserv from tipoprodserv where status='ATIVO' ".getidempresa('idempresa','compraforn')." order by tipoprodserv");?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td align="right">Fornecedor:</td>
                        <td colspan="3">
                            <select class='selectpicker' id="_idfornecedor_" multiple data-live-search='true' data-selected-text-format="count > 0" data-count-selected-text= "{0} Selecionados">
                                <?fillselect("SELECT 
                                                idpessoa, nome
                                            FROM
                                                (SELECT 
                                                    p.idpessoa,
                                                        IF(p.cpfcnpj != '', CONCAT(p.nome, ' - ', p.cpfcnpj), p.nome) AS nome
                                                FROM
                                                    pessoa p, tipopessoa t
                                                WHERE
                                                    p.idtipopessoa IN (2 , 5, 6, 7, 9, 11, 12)
                                                        AND p.status = 'ATIVO'
                                                        ".getidempresa('p.idempresa','compraforn')."
                                                        AND p.idtipopessoa = t.idtipopessoa UNION SELECT 
                                                    p.idpessoa,
                                                        IF(p.cpfcnpj != '', CONCAT(p.nome, ' - ', p.cpfcnpj), p.nome) AS nome
                                                FROM
                                                    pessoa p, tipopessoa t
                                                WHERE
                                                    p.flagobrigatoriocontato = 'Y'
                                                        AND p.idtipopessoa = t.idtipopessoa
                                                        AND p.idtipopessoa = 1
                                                        ".getidempresa('p.idempresa','compraforn')."
                                                        AND p.status = 'ATIVO') AS u
                                            ORDER BY nome");?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td align="right">Produto:</td>
                        <td colspan="3">
                            <select class='selectpicker' id="_idprodserv_" multiple data-live-search='true' data-selected-text-format="count > 0" data-count-selected-text= "{0} Selecionados">
                                <?fillselect("SELECT 
                                                idprodserv, descr
                                            FROM
                                                prodserv
                                            WHERE
                                                1 
                                                ".getidempresa('idempresa','compraforn')."
                                                    AND comprado = 'Y'
                                                    AND status = 'ATIVO'
                                            ORDER BY descr");?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td></td>
                        <td colspan="3">
                            <button id="cbPesquisar" class="btn btn-default btn-primary" style="float:left;" onclick="pesquisar()">
                                <i class="fa fa-search"> Pesquisar</i>
                            </button>
                            <?if($_GET and !empty($vencimento_1) and !empty($vencimento_2)){?>
                                <button class="btn btn-default btn-dark" style="float:right;" onclick="tableToExcel()">
                                    <i class="fa fa-file-excel-o"> Exportar Excel</i>
                                </button>
                            <?}?>
                        </td>
                    </tr>

                </table>

            </div>
        </div>
    </div>
</div>

<?
if($_GET and !empty($vencimento_1) and !empty($vencimento_2)){
    $dataini = validadate($vencimento_1);
    $datafim = validadate($vencimento_2);

    if ($dataini and $datafim){
        $cVencimento .= " AND (n.dtemissao  BETWEEN '" . $dataini ." 00:00:00' AND '" .$datafim ." 23:59:59')"."  ";
    }else{
        die ("Datas Inválidas!");
    }
    
    $sql = "SELECT u.* 
            FROM (
                SELECT 
                    n.idempresa,
                    p.idpessoa,
                    p.nome,
                    ifnull(po.descr, i.prodservdescr) as descr,
                    t.tipoprodserv,
                    i.idprodserv,
                    SUM(i.qtd) as qtdprod,
                    SUM(i.total) as totalprod
                FROM
                    nf n
                        JOIN
                    nfitem i ON (n.idnf = i.idnf)
                        JOIN
                    pessoa p ON (n.idpessoa = p.idpessoa)
                        LEFT JOIN
                    prodserv po ON (i.idprodserv = po.idprodserv)
                        LEFT JOIN
                    tipoprodserv t ON (t.idtipoprodserv = i.idtipoprodserv)
                WHERE
                    n.tiponf = 'C'
                        ".$cVencimento."
                        AND n.status = 'CONCLUIDO'
                        ".$andini.$cIdtipoprodserv.$cIdfornecedor.$cIdprodserv.$andfim."
                GROUP BY p.idpessoa , i.idprodserv) as u
            WHERE u.qtdprod <> '' ".getidempresa('u.idempresa','compraforn')."
            ORDER BY u.".$orderby." ".$ascdesc;
    $res = d::b()->query($sql) or die("<b>Erro na consulta de produtos 'COMPRAFORN'. SQL: </b>".$sql);
echo "<!--".$sql."-->";
    if(mysql_num_rows($res) > 0){?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Resultados Listagem </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th id="_nome" style="width: 35%;">Nome <i class="fa fa-arrow-down hoverazul pointer"></i> <i class="fa fa-arrow-up hoverazul pointer"></i></th>
                                    <th id="_descr" style="width: 35%;">Produto <i class="fa fa-arrow-down hoverazul pointer"></i> <i class="fa fa-arrow-up hoverazul pointer"></i></th>
                                    <th id="_qtdprod" style="width: 15%;">Qtd Produto <i class="fa fa-arrow-down hoverazul pointer"></i> <i class="fa fa-arrow-up hoverazul pointer"></i></th>
                                    <th id="_totalprod" style="width: 15%;">Total Produto (R$) <i class="fa fa-arrow-down hoverazul pointer"></i> <i class="fa fa-arrow-up hoverazul pointer"></i></th>
                                </tr>
                            </thead>
                            <tbody class="planilha">
                            <?
                            $excel = "";
                            while($row = mysql_fetch_assoc($res)){
                                if(empty($row["idprodserv"])){
                                    $link = $row["descr"];
                                }else{
                                    $link = "<a href='?_modulo=prodserv&_acao=u&idprodserv=".$row["idprodserv"]."' target='_blank'>".$row["descr"]."</a>";
                                }?>
                                <tr>
                                    <td><a href="?_modulo=pessoa&_acao=u&idpessoa=<?=$row["idpessoa"]?>" target="_blank"><?=$row["nome"]?></a></td>
                                    <td><?=$link?></td>
                                    <td><?=number_format($row["qtdprod"], 2, ',','.')?></td>
                                    <td><?=number_format($row["totalprod"], 2, ',','.')?></td>
                                </tr>
                            <?
                                $excel .= "
                                    <tr>
                                        <td>".$row["nome"]."</td>
                                        <td>".$row["descr"]."</td>
                                        <td>".number_format($row["qtdprod"], 2, ',','.')."</td>
                                        <td>".number_format($row["totalprod"], 2, ',','.')."</td>
                                    </tr>
                                ";
                            }?>
                            </tbody>
                        </table>
                        <table id="_tableresult" class="hidden">
                            <tr>
                                <td>Nome</td>
                                <td>Produto</td>
                                <td>Qtd Produto</td>
                                <td>Total Produto</td>
                            </tr>
                            <?echo $excel;?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?}else{?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Resultados Listagem </div>
                    <div class="panel-body">
                    Não foram encontrados resultados para essa pesquisa.
                    </div>
                </div>
            </div>
        </div>
    <?}
?>

<?}?>


<script>

    [
        {selector:"#_idtipoprodserv_",value:[<?=$iIdtipoprodserv?>]},
        {selector:"#_idfornecedor_",value:[<?=$iIdfornecedor?>]},
        {selector:"#_idprodserv_",value:[<?=$iIdprodserv?>]}
    ]
    .forEach((item) => $(item.selector).selectpicker('val',item.value));

    $(".bootstrap-select").css('width','100%');

    function pesquisar(vobj){
        var vencimento_1    = $("#vencimento_1").val()      || "";
        var vencimento_2    = $("#vencimento_2").val()      || "";
        var idtipoprodserv  = $("#_idtipoprodserv_").val()  || [];
        var idfornecedor    = $("#_idfornecedor_").val()    || [];
        var idprodserv      = $("#_idprodserv_").val()      || [];

        if(!vencimento_1 || !vencimento_2){
            alert("Por favor insira uma data");
            return;
        }

        var str = "vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2;

        idtipoprodserv.forEach((i) => str += "&idtipoprodserv[]="+i);
        idfornecedor.forEach((i) => str += "&idfornecedor[]="+i);
        idprodserv.forEach((i) => str += "&idprodserv[]="+i);

        

        if(vobj && typeof vobj == "object"){
            str += "&orderby="+vobj.orderby+"&ascdesc="+vobj.ascdesc
        }
        console.log(str);
        CB.go(str);
    }

    $(".fa-arrow-down").on('click', function(){
        pesquisar(
            {
                orderby:this.parentElement.id.replace("_",""),
                ascdesc:'asc'
            }
        );
    });

    $(".fa-arrow-up").on('click', function(){
        pesquisar(
            {
                orderby:this.parentElement.id.replace("_",""),
                ascdesc:'desc'
            }
        );
    });

    function tableToExcel() {
        var table = '_tableresult';
        var name = "compraforn-"+new Date().toLocaleDateString().replaceAll('/','-');

        var uri = 'data:application/vnd.ms-excel;base64,'
        , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
        , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
        , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }

        if (!table.nodeType) table = document.getElementById(table)
        var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
        var a = document.createElement('a');
        a.href = uri + base64(format(template, ctx));
        a.download = name;
        a.click();
    }

    CB.montaLegenda({"#333": "Produto sem cadastro.", "#337ab7": "Produto com cadastro."});
    CB.oPanelLegenda.css("zIndex", 901);
</script>