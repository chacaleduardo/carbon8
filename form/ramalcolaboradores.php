<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
?>
<style>
    #avatar{
		margin:5px;
		border-radius: 50%;
		cursor: pointer;
		height: 40px;
		width: 40px;
	}
</style>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default" style="margin-left: 1%;width: 98%;">
            <div class="panel-heading">Lista de Ramais</div>
            <div class="panel-body">
                <table style="width: 100%;">
                    <tr>
                        <td  ><input type="text" style="padding-left: 25px;" placeholder="Buscar um colaborador" id="pessoa_nome"/> </td>
                    </tr>
                </table>
                <div  class="fa fa-search" style="margin-top: -56px;margin-left: 10px;"></div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <td style="width: 70px;"></td>
                            <td ><b>Nome</b></td>
                            <td ><b>Email Corporativo</b></td>
                            <td style="width: 200px;text-align:right"><b>Ramal</b></td>
                        </tr>
                    </thead>
                    <tbody id="_corpo_modal_">
                    <?
                        $sql = "SELECT
                        p.idpessoa,
                        e.sigla,
                        IFNULL(p.nomecurto, p.nome) AS nomecurto,
                        IFNULL(sc.conselho, IFNULL(sa.area, IFNULL(sd.departamento, setor))) as setor,
                        -- IFNULL(sc.ramal, IFNULL(sa.ramal, IFNULL(sd.ramal, IFNULL(ss.ramal,ramalfixo)))) as ramalfixo,
                        CONCAT(
                            IFNULL(CONCAT('Usuário: ',NULLIF(p.ramalfixo, '')), ''), 
                            IFNULL(CONCAT(' Setor: ',NULLIF(ss.ramal, '')), ''), 
                            IFNULL(CONCAT(' Dep.: ',NULLIF(sd.ramal, '')), ''), 
                            IFNULL(CONCAT(' Area : ',NULLIF(sa.ramal, '')), ''), 
                            IFNULL(CONCAT(' Conselho : ', NULLIF(sc.ramal, '')), '')
                        ) as ramalfixo,
                        a.nome,
                        if(p.webmailpermissao = 'Y',p.webmailemail,'-') as webmailemail
                        FROM
                        pessoa p
                        LEFT JOIN
                        pessoaobjeto po on(po.idpessoa = p.idpessoa)
                        LEFT JOIN
                        sgsetor ss ON (ss.idsgsetor = po.idobjeto and po.tipoobjeto = 'sgsetor' and ss.status = 'ATIVO')
						LEFT JOIN
                        sgdepartamento sd ON (sd.idsgdepartamento = po.idobjeto and po.tipoobjeto = 'sgdepartamento' and sd.status = 'ATIVO')
                        LEFT JOIN
                        sgarea sa ON (sa.idsgarea = po.idobjeto and po.tipoobjeto = 'sgarea' and sa.status = 'ATIVO')
                        LEFT JOIN
                        sgconselho sc ON (sc.idsgconselho = po.idobjeto and po.tipoobjeto = 'sgconselho' and sc.status = 'ATIVO')
                        LEFT JOIN
                        arquivo a ON (a.idobjeto = p.idpessoa and tipoarquivo = 'avatar')
                        LEFT JOIN
                        empresa e ON (e.idempresa = p.idempresa)
                        WHERE
                        p.status = 'ATIVO'
                        and po.tipoobjeto in ('sgconselho','sgarea','sgdepartamento','sgsetor')
                        ".getidempresa('p.idempresa','evento')."
                        AND p.idtipopessoa = 1
                        group by p.idpessoa
                        ORDER BY nomecurto;";
                        
                        $res=mysql_query($sql) or die($sql." erro ao buscar colaboradores SQL=> ".mysql_error());
                        while($row = mysqli_fetch_assoc($res)){?>
                            <tr>
                                <td style="text-align: center;" > <img src="/upload/avatar/<?=$row['nome']?>" id="avatar" onclick="janelamodal('/upload/avatar/<?=$row['nome']?>')"> </td>
                                <td>
                                    <?echo $nome = $row["sigla"].' - '.$row['nomecurto'];?>
                                    <? if (!empty($row['setor'])){?>
                                        &nbsp;<span style="background: rgb(102, 102, 102);font-size: 10px;color: #fff;padding: 0px 6px;border-radius: 3px;"><?=$row['setor']?></span>
                                    <?}?>
                                </td>
                                <td ><?=$row["webmailemail"]?></td>
                                <td style="text-align:right;"><b><?echo $ramal = (empty($row['ramalfixo'])) ? '-' : '<i class="fa fa-phone-square"></i>&nbsp;'.$row['ramalfixo'];?></b></td>
                            </tr>
                        <?}?>
                    </tbody>
                </table>
                
            </div>
        </div>
    </div>
</div>

<script>
    // Filtro por texto
    $("#pessoa_nome").on('keyup', function() {
    var filter, table, tr, a, i, txtValue;
    filter = normalizeToBase(this.value.toUpperCase());
    table = document.getElementById("_corpo_modal_");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        a = tr[i].getElementsByTagName("td");
        some = true;
        for (ii = 0; ii < a.length; ii++) {
            txtValue =normalizeToBase(a[ii].textContent) || normalizeToBase(a[ii].innerText);
            if (txtValue.toUpperCase().match(filter)) { 
                some = false;
            }
        }
        if (some != true) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
    }
});

</script>