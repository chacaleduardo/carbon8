<?
$url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

$parts = explode('&', $url);
foreach ($parts as $url){
    if (preg_match('/id/', $url) && !preg_match('/_idempresa/', $url)){
        $id = explode("=", $url);
        $last = $id[1];
    }
}

$sqlSolAssinaturaLp = "select 1 from carbonnovo._lpmodulo where idlp in (".getModsUsr("LPS").") and modulo = '".$_GET['_modulo']."' and solassinatura = 'Y'";
$resSolAssinaturaLp = d::b()->query($sqlSolAssinaturaLp);

if ((mysqli_num_rows($resSolAssinaturaLp) > 0) and !empty($_GET['_modulo']) and is_numeric($last)){
    $s = "select o.idobjeto,o.tipoobjeto 
    from lpobjeto o 
    where 1 ".getidempresa('o.idempresa', 'evento')." 
    and o.idobjeto <> ''
    and o.tipoobjeto <> ''
    and o.idlp in (select idlp from "._DBCARBON."._lpmodulo lm where lm.modulo = '".$_GET['_modulo']."')
    and not exists (select 1 from carrimbo c 
        where c.idobjetoext = o.idobjeto 
            and c.status = 'PENDENTE'
            and c.tipoobjetoext = o.tipoobjeto 
            and c.tipoobjeto = '".$_GET['_modulo']."' 
            and c.idobjeto = ".$last."
        )
    group by o.idobjeto,o.tipoobjeto
    union
		select p.idpessoa, 'pessoa' as tipo
	from lpobjeto lo 
		join "._DBCARBON."._lpmodulo lm on (lm.idlp = lo.idlp)
		join pessoaobjeto po on (po.idobjeto = lo.idobjeto and po.tipoobjeto = lo.tipoobjeto)
		join pessoa p on (p.idpessoa = po.idpessoa)
	where 
		lo.tipoobjeto in ('sgarea','sgdepartamento','sgsetor')
		and lm.modulo = '".$_GET['_modulo']."' 
		and lo.idlp != 0
		and p.status = 'ATIVO';
    ";

    $rts = d::b()->query($s) or die("Solicitar Assinatura: ".mysqli_error(d::b()));

    $arrtmp = array();
    $i = 0;
    while ($r = mysqli_fetch_assoc($rts)){

        if ($r["tipoobjeto"] != 'pessoa'){

            $s1 = "SELECT GROUP_CONCAT(po.idpessoa) as idpessoa, s.".substr($r["tipoobjeto"], 2)." as rotulo
            FROM pessoaobjeto po
                JOIN ".$r["tipoobjeto"]." s ON (po.idobjeto = s.id".$r["tipoobjeto"].")
            WHERE po.tipoobjeto = '".$r["tipoobjeto"]."'
                AND po.idobjeto = ".$r["idobjeto"]."
                AND s.status = 'ATIVO'
                ".getidempresa('po.idempresa', 'evento');
            $rts1 = d::b()->query($s1) or die("Solicitar Assinatura: ".mysqli_error(d::b()));
            $r1 = mysqli_fetch_assoc($rts1);
            if (!empty($r1["idpessoa"])){
                $arrtmp[$i]["label"] = $r1["rotulo"];
                $arrtmp[$i]["value"] = $r1["idpessoa"];
                $arrtmp[$i]["tipo"] = 'grupo';
                $arrtmp[$i]["subtipo"] = $r["tipoobjeto"];
                $arrtmp[$i]["idsubtipo"] = $r["idobjeto"];
                $i++;
            }
        } else {
            $s1 = "SELECT IF(p.nomecurto is NULL, p.nome, p.nomecurto) AS nome
            FROM pessoa p
            WHERE p.idpessoa = ".$r["idobjeto"]."
            ".getidempresa('p.idempresa', 'evento')."
                AND p.status = 'ATIVO'";
            $rts1 = d::b()->query($s1) or die("Solicitar Assinatura: ".mysqli_error(d::b())." SQL: ".$s1);
            $nrows = mysqli_num_rows($rts1);
            if ($nrows > 0){
                $r1 = mysqli_fetch_assoc($rts1);
                $arrtmp[$i]["value"] = $r["idobjeto"];
                $arrtmp[$i]["label"] = $r1["nome"];
                $arrtmp[$i]["tipo"] = $r["tipoobjeto"];
                $i++;
            }
        }
    }


    $i = 0;
    $sAs = "SELECT idcarrimbo,c.status,IF(p.nomecurto is NULL, p.nome, p.nomecurto) as nome
    FROM carrimbo c 
        JOIN pessoa p ON (c.idpessoa = p.idpessoa) 
    WHERE c.tipoobjeto = '".$_GET['_modulo']."'
        AND c.idobjeto = ".$last."
    ORDER BY nome";

    $reAs = d::b()->query($sAs) or die("Solicitar Assinatura: ".mysqli_error(d::b()));
    $nAs = mysqli_num_rows($reAs);
    if ($nAs > 0){
        $arrtmp1 = array();
        while ($rAs = mysqli_fetch_assoc($reAs)){
            $arrtmp1[$i]["idcarrimbo"] = $rAs["idcarrimbo"];
            $arrtmp1[$i]["nome"] = $rAs["nome"];
            $arrtmp1[$i]["status"] = $rAs["status"];
            $i++;
        }
    } else {
        $arrtmp1 = 0;
    }

    $jarraysolassinatura    = $JSON->encode($arrtmp);
    $jarraysolassinatura1   = $JSON->encode($arrtmp1);
    ?>
    <script>
        var jArraySolAssinatura = <?= $jarraysolassinatura ?>;
        var jArraySolAssinatura1 = <?= $jarraysolassinatura1 ?>;

        function botaoSolicitarAssinatura(){
            CB.novoBotaoUsuario({
                id: "btSolicitaAssinatura",
                rotulo: "Solicitar Assinatura",
                class: "btn btn-primary",
                icone: "fa fa-edit",
                onclick: function(){

                    $tbNovaMsg = $(`
                            <table width="100%">
                                <tr>
                                    <td>
                                        <input id="cbSolicitarAssinaturaComplete" class="compacto" type="text" cbvalue placeholder="Selecione as pessoas para assinatura.">
                                    </td>
                                    <td class="linha" style="width: 10%;"></td>
                                </tr>
                            </table>
                            <hr>
                            <fieldset style="border: 1px solid #eee !important;padding: 8px;margin: 0 0 1.5em 0 !important;">
                                <legend style="border:0 !important;margin:0 !important;">Solicitar para</legend>
                                <table id="listaPessoasAssinatura" width="100%"></table>
                            </fieldset>
                            <hr>
                            <fieldset style="border: 1px solid #eee !important;padding: 8px;margin: 0 0 1.5em 0 !important;">
                                <legend style="border:0 !important;margin:0 !important;">Assinaturas</legend>
                                <table id="listaPessoasAssinaturasPendentes" width="100%"></table>
                            </fieldset>
                            <div class="col-md-5"></div>
                            <div class="col-md-7" style="margin-bottom: 10px;text-align: right;">
                                <button id="cbSolicitarAssinaturaButton" type="button" class="btn btn-success btn-xs" disabled="disabled" title="Solicitar Assinatura"><i class="fa fa-edit"></i>Salvar</button>
                                <button id="removerAssinaturasPendentes" type="button" class="btn btn-danger btn-xs" disabled="disabled" title="Retirar Todas as Assinaturas"><i class="fa fa-trash"></i>Retirar Assinaturas</button>
                            </div>
                        `);

                    if (jArraySolAssinatura1 != 0){
                        jArraySolAssinatura1.forEach((k, m) => {
                            if (k.status == 'PENDENTE'){
                                $tbNovaMsg.find("#removerAssinaturasPendentes").removeAttr('disabled');
                                var classe = "class='btn btn-primary btn-xs' title='Retirar Assinatura'";
                                var labelbutton = "Assinatura";
                                var assinado = "N";
                            } else {
                                var classe = "class='btn btn-success btn-xs' disabled='disabled'";
                                var labelbutton = "Assinado";
                                var assinado = "Y";
                            }
                            var linha = $(`<tr asIdcarrimbo = "${k.idcarrimbo}" assinado="${assinado}"><td><label>${k.nome}</label></td><td class='linha1' style="width:1%;"></td></tr>`);
                            linha.find('td.linha1').append(
                                $(`<button style="width:100%;" type="button" ${classe}><i class="fa fa-check"></i>${labelbutton}</button>`).on('click', function(){
                                    CB.post({
                                        objetos: "_aS_d_carrimbo_idcarrimbo=" + k.idcarrimbo,
                                        parcial: true,
                                        posPost: function(){
                                            $(`tr[asIdcarrimbo="${k.idcarrimbo}"]`).remove();
                                            jArraySolAssinatura1 = jArraySolAssinatura1.filter((i) => {
                                                if (i.idcarrimbo == k.idcarrimbo){
                                                    return false;
                                                } else {
                                                    return true;
                                                }
                                            })
                                        }
                                    });
                                })
                            );
                            $tbNovaMsg.find('#listaPessoasAssinaturasPendentes').append(linha);
                        });
                    } else {
                        $tbNovaMsg.find("#listaPessoasAssinaturasPendentes").parent().hide()
                        $tbNovaMsg.find("#removerAssinaturasPendentes").hide()
                    }

                    $tbNovaMsg.find('#removerAssinaturasPendentes').on('click', function(){
                        var nAssinados = $("#listaPessoasAssinaturasPendentes tr[asidcarrimbo][assinado='N']");
                        if (nAssinados.length > 0){
                            var str = "";
                            var ecomer = "";
                            nAssinados.each(function(i, o){
                                var idcarrimbo = $(o).attr("asidcarrimbo");
                                str += ecomer + `_dS${i}_d_carrimbo_idcarrimbo=${idcarrimbo}`;
                                ecomer = "&";
                                jArraySolAssinatura1 = $.grep(jArraySolAssinatura1, function(value){
                                    return value.idcarrimbo != idcarrimbo;
                                });
                            });
                            CB.post({
                                objetos: str,
                                parcial: true,
                                posPost: function(){
                                    CB.loadUrl({
                                        urldestino: CB.urlDestino + window.location.search
                                    });
                                    $("#cbModal").modal("hide");
                                }
                            });
                        }
                    });

                    $tbNovaMsg.find('#cbSolicitarAssinaturaButton').on('click', function(){
                        var auxArray = Array();

                        $("#listaPessoasAssinatura tr").each((i, o) => {
                            if ($(o).attr('astipo') == "grupo"){
                                var aux = $(o).attr('asvalor').split(',');
                                aux.forEach(t => {
                                    var auxObjs = {
                                        tipo: $(o).attr('assubtipo'),
                                        idtipo: $(o).attr('asidsubtipo'),
                                        valor: t
                                    };
                                    auxArray.push(auxObjs);
                                });
                            } else {
                                var aux = {
                                    tipo: "pessoa",
                                    idtipo: $(o).attr('asvalor'),
                                    valor: $(o).attr('asvalor')
                                };
                                auxArray.push(aux);
                            }
                        });

                        auxArray = auxArray.sort((a, b) => (a.tipo < b.tipo) ? 1 : ((b.tipo < a.tipo) ? -1 : 0));

                        auxArray = [...new Map(auxArray.map(item => [item["valor"], item])).values()];


                        var ecomer = "";
                        var str = "";

                        auxArray.forEach((j, p) => {
                            str += ecomer + `_aS${p}_i_carrimbo_idpessoa=${j.valor}&_aS${p}_i_carrimbo_idobjetoext=${j.idtipo}&_aS${p}_i_carrimbo_tipoobjetoext=${j.tipo}&_aS${p}_i_carrimbo_status=PENDENTE&_aS${p}_i_carrimbo_idobjeto=<?= $last ?>&_aS${p}_i_carrimbo_tipoobjeto=<?= $_GET['_modulo'] ?>`;
                            ecomer = "&";
                        });
                        CB.post({
                            objetos: str,
                            parcial: true,
                            posPost: function(){
                                CB.loadUrl({
                                    urldestino: CB.urlDestino + window.location.search
                                });
                                $("#cbModal").modal("hide");
                            }
                        });
                        //console.log(str);
                    });

                    strCabecalho = "<label class='fa fa-edit'></label>&nbsp;&nbsp;Solicitar Assinatura:";


                    CB.modal({
                        titulo: strCabecalho,
                        corpo: [$tbNovaMsg],
                        classe: 'quarenta',
                        aoAbrir: function(){
                            $("#cbSolicitarAssinaturaComplete").autocomplete({
                                source: jArraySolAssinatura,
                                delay: 0,
                                create: function(){
                                    $(this).data('ui-autocomplete')._renderItem = function(ul, item){
                                        if (item.tipo == "grupo"){
                                            return $('<li>').append("<i class='fa fa-users' style='color:#ddd;font-size:10px;'></i> " + item.label).appendTo(ul);
                                        } else {
                                            return $('<li>').append("<i class='fa fa-user' style='color:#ddd;font-size:10px;'></i> " + item.label).appendTo(ul);
                                        }
                                    };
                                },
                                select: function(event, ui){

                                    jArraySolAssinatura = jArraySolAssinatura.filter(function(elemento){
                                        if (elemento.tipo == ui.item.tipo && elemento.value == ui.item.value){
                                            return false;
                                        } else {
                                            return true;
                                        }
                                    })

                                    $('#cbSolicitarAssinaturaComplete').autocomplete('option', 'source', jArraySolAssinatura);

                                    if (ui.item.tipo == 'grupo'){
                                        var tipo = `aSsubtipo = '${ui.item.subtipo}' aSidsubtipo='${ui.item.idsubtipo}'`;
                                    } else {
                                        var tipo = "";
                                    }

                                    var linhaAssinatura = $(`<tr ${tipo} aStipo="${ui.item.tipo}" aSvalor="${ui.item.value}"><td><label>${ui.item.label}</label></td>
                                            <td class='linha' style="width:1%;"></td></tr>`);

                                    linhaAssinatura.find('td.linha').append(
                                        $(`<button type="button" class="btn btn-secondary btn-xs" title="Retirar Assinatura"><i class="fa fa-check"></i>Assinatura</button>`).on('click', function(){
                                            if (ui.item.tipo == 'grupo'){
                                                jArraySolAssinatura.push({
                                                    label: ui.item.label,
                                                    value: ui.item.value,
                                                    tipo: ui.item.tipo,
                                                    subtipo: ui.item.subtipo,
                                                    idsubtipo: ui.item.idsubtipo
                                                });
                                            } else {
                                                jArraySolAssinatura.push({
                                                    value: ui.item.value,
                                                    label: ui.item.label,
                                                    tipo: ui.item.tipo
                                                });
                                            }
                                            $("tr[astipo='" + ui.item.tipo + "'][asvalor='" + ui.item.value + "']").remove();
                                            if ($("#listaPessoasAssinatura tr").length == 0){
                                                $("#cbSolicitarAssinaturaButton").attr("disabled", true);
                                            }
                                            $('#cbSolicitarAssinaturaComplete').autocomplete('option', 'source', jArraySolAssinatura);
                                        })
                                    );

                                    $("#listaPessoasAssinatura").append(linhaAssinatura);
                                    $("#cbSolicitarAssinaturaButton").attr("disabled", false);

                                    // Apaga o valor do input para a próxima seleção
                                    this.value = "";
                                    return false;
                                }
                            });
                        }
                    });
                }
            });
        }
        if ($("#btSolicitaAssinatura").length){
            $("#btSolicitaAssinatura").remove();
            botaoSolicitarAssinatura();
        } else {
            botaoSolicitarAssinatura();
        }
        //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape_assinatura
    </script>
    <? }

if (!empty($_REQUEST['_modulo']) and is_numeric($last) and $_REQUEST['_modulo'] != 'documento'){
    $sqla = "select idcarrimbo from carrimbo 
            where status='PENDENTE' 
            and idobjeto = '".$last."'
            and tipoobjeto in ('".$_REQUEST['_modulo']."')
            and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
    $resa = d::b()->query($sqla) or die("Erro ao buscar se modulo esta assinado: Erro: ".mysqli_error(d::b())."\n".$sqla);
    $qtda = mysqli_num_rows($resa);
    if ($qtda > 0){
        $rowa = mysqli_fetch_assoc($resa);
    ?>
        <script>
            function botaoAssinarGeral(){
                $bteditar = $("#btAssina");
                //console.log($bteditar);
                if ($bteditar.length == 0){
                    // alert();
                    CB.novoBotaoUsuario({
                        id: "btAssina",
                        rotulo: "Assinar",
                        class: "verde",
                        icone: "fa fa-pencil",
                        onclick: () => {
                            var conteudo = {
                                idpessoa: gIdpessoa || "",
                                modulo: CB.jsonModulo.modulo || "",
                                idpagina: getUrlParameter(CB.jsonModulo.pk) || "",
                                idcarrimbo: "<?= $rowa['idcarrimbo'] ?>" || "",
                                status: "ASSINADO"

                            }
                            signContent({
                                path: "ajax/_certs.php",
                                content: conteudo,
                                selector: "#btAssina",
                                hideButtonOnSign: true,
                                posSing: function(obj, data, textStatus, jqXHR){
                                    $("#btRejeita").hide();
                                }
                            });
                        }
                    });

                    CB.novoBotaoUsuario({
                        id: "btRejeita",
                        rotulo: "Rejeitar",
                        class: "vermelho",
                        icone: "fa fa-ban",
                        onclick: () => {
                            var conteudo = {
                                idpessoa: gIdpessoa || "",
                                modulo: CB.jsonModulo.modulo || "",
                                idpagina: getUrlParameter(CB.jsonModulo.pk) || "",
                                idcarrimbo: "<?= $rowa['idcarrimbo'] ?>" || "",
                                status: "REJEITADO",
                            }
                            signContent({
                                path: "ajax/_certs.php",
                                content: conteudo,
                                selector: "#btRejeita",
                                hideButtonOnSign: true,
                                posSing: function(obj, data, textStatus, jqXHR){
                                    $("#btAssina").hide();
                                }
                            });
                        }
                    });
                }
            }
            if ($("#btAssina").length){
                //$("#btAssina").remove();
                // botaoAssinarGeral();
            } else {
                botaoAssinarGeral();
            }
        </script>

    <?
    } // if($qtda>0){
} //if(!empty($_1_u_sgdoc_idsgdoc)){
$sql = preencheAssinaturas($_GET["_modulo"], $_idModuloParaAssinatura);
$res = d::b()->query($sql) or die("A Consulta de assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql);
$existe = mysqli_num_rows($res);
if ($existe > 0){
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Assinaturas</div>
                <div class="panel-body">
                    <table class="planilha grade compacto">                        
                        <?
                        $removeAssinatura = "Funcionários";
                        while ($row = mysqli_fetch_assoc($res)){

                            if (($anonimo == 'Y') and $row["usuario"] == $_1_u_evento_criadopor){
                                $row["nome"] = '<i><b>ANÔNIMO</i></b>';
                            }

                            $tr .= "<tr class='res'>
                                        <td nowrap>".$row["nome"]."</td>
                                        <td class='hidden' nowrap>".$row["assinaturaanterior"]."</td>
                                        <td nowrap>".$row["dataassinatura"]."</td>
                                        <td nowrap>".$row["status"]."</td>
                                    </tr>";
                            if($row["idobjetoext"] == 882 && $_GET["_modulo"] == 'amostratra' && $row["status"] != 'PENDENTE'){
                                $removeAssinatura = "<a onclick='excluirAssinatura(".$row["idcarrimbo"].")' style='color: gray;'>Funcionários</a>";
                            }   
                        }
                        ?>
                        <tr>
                            <th><?=$removeAssinatura?></th>
                            <th class="hidden">Data Assinatura</th>
                            <th>Data Assinatura</th>
                            <th>Status</th>
                        </tr>
                        <?=$tr?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        function excluirAssinatura(idcarrimbo){
            if(confirm("Deseja realmente reenviar?")){
                CB.post({
                    objetos: `_ec_d_carrimbo_idcarrimbo=${idcarrimbo}`,
                    parcial: true,
                    posPost: function(){
                        CB.loadUrl({
                            urldestino: CB.urlDestino + window.location.search
                        });
                    }
                });
            }
        }
    </script>
<?
} //if($existe>0){ 
?>
