<script>
    const jsonPref = <?= $jsonPref ?>;
    const eventosAlerta = <?= json_encode($eventosAlerta) ?>;
    const lps = <?= json_encode($lps) ?>;
    const habilitarMatriz = '<?= cb::habilitarMatriz() ?>';
    const jsLps = '<?= $jsLps ?>';
    const idPessoa = <?= $_SESSION["SESSAO"]["IDPESSOA"] ?>;

    var oPeriodoEtlDashCards;
    var figurasCabecalhosRelatorio = <?= json_encode($figurasCabecalhosRelatorio); ?>;

    (function() {
        let arrIdDashCards = [];

        for (let dashConf of lps) {
            let idLp = dashConf['idlp'];
            let jDash = JSON.parse(dashConf['jsondashboardconf']);

            for (let grupo of jDash["dashgrupo"]) {
                if (!grupo) continue;

                construirDashGrupo('r', grupo["id"], idLp, jDash);

                if (!$(`#grupoBtn-${grupo["id"]}-${idLp}`).hasClass('ativo'))
                    $(`#div-conf-${grupo["id"]}-${idLp}`).hide();

                for (let panel of grupo["dashpanel"]) {
                    construirDashPanel('r', panel["id"], grupo["id"], idLp, jDash);
                    $(`div#div-conf-${panel["id"]}-${grupo["id"]}-${idLp}`).prepend(`
                        <?
                        if (array_key_exists("dashmaster", getModsUsr("MODULOS")) == 1) {
                            echo '<i class="fa fa-rotate-right pointer btnRefresh1" onclick="recarregarIndicadoresCron(this, ${panel["id"]}, ${grupo["id"]}, ${idLp})"></i>';
                        }
                        ?>
                        <i class="fa fa-refresh pointer btnRefresh" onclick="recarregarIndicadores(this, ${panel["id"]}, ${grupo["id"]}, ${idLp})"></i>
                        <i class="fa fa-arrows-v pointer btnCollapse" onclick="hideShowPainel(this, ${panel["id"]}, ${grupo["id"]}, ${idLp})"></i>
                    `);

                    for (let card of panel["dashcard"]) {
                        construirDashCard(card, panel["id"], grupo["id"], idLp, 'w');

                        $(`.dashcard-${card.iddashcard} .card_value`)
                            .not('.circle-load')
                            .addClass('circle-load')
                            .append(`<div class="circularProgressIndicator hidden"></div>`);

                        arrIdDashCards.push(card.iddashcard);
                    }
                }
            }
        }

        mostrarPaineis();
        buscarPeriodoEtlDashCards(arrIdDashCards);
        // Busca o valor dos indicadores de todos os cards visíveis
        // ou seja, os cards disponíveis por conta das prefs do usuário
        $('button[id^="grupoBtn-"].ativo').not('.fixo').each(function(i, o) {
            let splitId = o.id.split('-');

            let idgrupo = splitId[1];
            let idlp = splitId[2];

            $(`div#div-conf-${idgrupo}-${idlp} div.col-md-12[id*=dashpanel-conf-]:not(.hidden) div[class*='dashcard-']`).each(function(j, p) {
                //ajax
                buscaValorCard($(p).attr('iddashcard'));
            });
        });
    })();

    function mostrarPaineis() {
        if (!jsonPref["painel"]) return;

        for (let painel in jsonPref["painel"]) {
            if (jsonPref["painel"][painel] == "Y") {
                $(`div#dashpanel-conf-${painel}.col-md-12.hidden`).removeClass('hidden');
            }
        }
    }

    function buscarPeriodoEtlDashCards(arrIdDashCards = []) {
        if (arrIdDashCards.length < 1) return;

        $.ajax({
            url: "ajax/buscarperiodoetldascards.php",
            type: "POST",
            data: {
                dashcards: arrIdDashCards.join()
            },
            beforeSend: function(inxhr) {
                $("i[onclick^=getEtlRel]").hide();
            },
            success: function(data) {
                try {
                    oPeriodoEtlDashCards = JSON.parse(data);
                } catch (e) {
                    alertErro(e.toString());
                }
                $("i[onclick^=getEtlRel]").show();
            },
            error: function(objxmlreq) {
                $("i[onclick^=getEtlRel]").show();
            }
        });
    }

    function getThisWeek() {
        let curr = new Date();
        let fDay = new Date(curr.setDate(curr.getDate() - curr.getDay()));
        let lDay = new Date(curr.setDate(curr.getDate() - curr.getDay() + 6));

        return {
            "fDate": fDay.toLocaleDateString(),
            "lDate": lDay.toLocaleDateString(),
        }
    }

    function getLastWeek() {
        let lDay = new Date();
        let fDay = new Date(lDay.getFullYear(), lDay.getMonth(), lDay.getDate() - 7);

        return {
            "fDate": fDay.toLocaleDateString(),
            "lDate": lDay.toLocaleDateString(),
        }
    }

    function getThisMonth() {
        let curr = new Date();
        let fDay = new Date(curr.getFullYear(), curr.getMonth(), 1);
        let lDay = new Date(curr.getFullYear(), curr.getMonth() + 1, 0);

        return {
            "fDate": fDay.toLocaleDateString(),
            "lDate": lDay.toLocaleDateString(),
        }
    }

    function getLastMonth() {
        let lDay = new Date();
        let fDay = new Date(lDay.getFullYear(), lDay.getMonth() - 1, lDay.getDate());

        return {
            "fDate": fDay.toLocaleDateString(),
            "lDate": lDay.toLocaleDateString(),
        }
    }

    function getEtlRel(iddash) {
        let oDates;
        let periodoEtlDashCard = oPeriodoEtlDashCards[iddash] || 'LASTMONTH';

        switch (periodoEtlDashCard) {
            case 'THISWEEK':
                oDates = getThisWeek();
                break;
            case 'LASTWEEK':
                oDates = getLastWeek();
                break;
            case 'THISMONTH':
                oDates = getThisMonth();
                break;
            case 'LASTMONTH':
                oDates = getLastMonth();
                break;
            default:
                oDates = getLastMonth();
                break;
        }

        let idempresaUrl = getUrlParameter("_idempresa");

        if (idempresaUrl != '') {
            idempresaUrl = '&_idempresa=' + idempresaUrl;
        }

        let url = "?_modulo=menurelatorio&_menu=N&_menulateral=N&iddash=" + iddash + "&_novajanela=Y&_idrep=200,252&_fds=" + oDates.fDate + "-" + oDates.lDate + idempresaUrl;

        janelamodal(url);
    }


    function hideShowGrupo(vthis, idLp, idGrupo, req = true) {
        let $this = $(vthis);
        if ($this.hasClass('ativo')) {
            $(`#div-conf-${idGrupo}-${idLp}`).hide('fast');
            $this.removeClass('ativo');
            CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"grupo":{"' + idGrupo + '_' + idLp + '":"N"}}}');
        } else {
            $(`#div-conf-${idGrupo}-${idLp}`).show('fast');
            $this.addClass('ativo');

            if (req) {
                $(`div#div-conf-${idGrupo}-${idLp} div.col-md-12[id*=dashpanel-conf-]:not(.hidden) div[class*='dashcard-']`).each(function(j, p) {
                    //ajax
                    buscaValorCard($(p).attr('iddashcard'));
                });
            }

            CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"grupo":{"' + idGrupo + '_' + idLp + '":"Y"}}}');
        }
    }

    function hideShowPainel(vthis, idPainel, idGrupo, idLp) {
        let painel = $(`div#dashpanel-conf-${idPainel}-${idGrupo}-${idLp}.col-md-12`);
        if (painel.hasClass('hidden')) {
            painel.removeClass('hidden');
            CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"painel":{"' + idPainel + '-' + idGrupo + '-' + idLp + '":"Y"}}}');

            painel.find(`div[class*='dashcard-']`).each(function(j, p) {
                buscaValorCard($(p).attr('iddashcard'));
            });
        } else {
            painel.addClass('hidden');
            CB.setPrefUsuario('m', '{"' + CB.modulo + '":{"painel":{"' + idPainel + '-' + idGrupo + '-' + idLp + '":"N"}}}');
        }
    }

    function recarregarIndicadoresCron(vthis, idPainel, idGrupo, idLp) {
        debugger
        if (!$(vthis).hasClass('updating')) {
            $(vthis).addClass('updating');
            var listaCardRequest = [];
            $(`#dashpanel-conf-${idPainel}-${idGrupo}-${idLp} .card-space div[iddashcard]`).each(function(i, o) {
                let $o = $(o);
                if (!listaCardRequest.includes($o.attr('iddashcard'))) {
                    $o.removeClass('load');
                    $o.find("span.card_value").html('<div class="circularProgressIndicator hidden"></div>');
                    $o.siblings('span').remove();
                    atualizaValorCard($o.attr('iddashcard'), $o);
                    listaCardRequest.push($o.attr('iddashcard'));
                }

            });
            $(vthis).removeClass('updating');
        }
    }

    function recarregarIndicadores(vthis, idPainel, idGrupo, idLp) {
        debugger
        if (!$(vthis).hasClass('updating')) {
            $(vthis).addClass('updating');
            var listaCardRequest = [];
            $(`#dashpanel-conf-${idPainel}-${idGrupo}-${idLp} .card-space div[iddashcard]`).each(function(i, o) {
                let $o = $(o);
                if (!listaCardRequest.includes($o.attr('iddashcard'))) {
                    $o.removeClass('load');
                    $o.find("span.card_value").html('<div class="circularProgressIndicator hidden"></div>');
                    $o.siblings('span').remove();
                    buscaValorCard($o.attr('iddashcard'), $o);
                    listaCardRequest.push($o.attr('iddashcard'));
                }

            });
            $(vthis).removeClass('updating');
        }
    }

    function recarregarIndicadoresFixos(vthis, idPainel, idDashCardReal) {
        if (!$(vthis).hasClass('updating')) {
            $(vthis).addClass('updating');
            var listaCardRequest = [];
            $(`.idpainel-${idPainel} div[iddashcardreal='${idDashCardReal}']`).each(function(i, o) {
                let $o = $(o);
                if (!listaCardRequest.includes($o.attr('iddashcard'))) {
                    $o.removeClass('load');
                    $o.find("span.card_value").html('<div class="circularProgressIndicator hidden"></div>');
                    $o.siblings('span').remove();
                    buscaValorCard($o.attr('iddashcard'), $o, 'Y', idDashCardReal);
                }
            });
            $(vthis).removeClass('updating');
        }
    }

    async function atualizaValorCard(idDashCard, $obj, fixo = 'N', idDashCardReal) {
        let $el = ($obj) ? $obj : $('.dashcard-' + idDashCard);

        if (!$el.hasClass('load')) {
            $el.find('.circularProgressIndicator').removeClass('hidden');
            $el.addClass('loading');
            //$el.find('.card-body div.text-xs.mb-1:first-child').text('Carregando...');

            let idempresa = (getUrlParameter("_idempresa")) ? "?_idempresa=" + getUrlParameter("_idempresa") : '';
            let data = (fixo == 'Y') ? {
                iddashcard: idDashCard,
                fixo: fixo,
                idDashCardReal: idDashCardReal,
            } : {
                iddashcard: idDashCard,
                fixo: fixo,
                habilitarMatriz: habilitarMatriz,
                lps: jsLps
            };

            // console.log('atualizaValorCard', idDashCard)
            await fetch("cron/testedash.php?_id=" + idDashCard, {
                method: "GET",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "authorization": (Cookies.get('jwt') || localStorage.getItem("jwt") || "")
                }
            });
            // console.log('atualizaValorCard saiu', idDashCard)

            data = Object.keys(data)
                .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(data[k])}`)
                .join('&');

            let response = await fetch("ajax/buscavalorcarddashboard.php" + idempresa, {
                method: "POST",
                body: data,
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "authorization": (Cookies.get('jwt') || localStorage.getItem("jwt") || "")
                }
            });
            // Cor para quanto n houver registro
            let color = "secondary",
                borderColor = "secondary";

            try {
                let dados = '';
                dados = await response.json();


                let oCard = (fixo == 'Y') ? $obj : $('.dashcard-' + idDashCard + '.loading');

                oCard.find('.circularProgressIndicator')
                    .addClass('hidden');
                if ((dados.statuscard == 'ATIVO' || dados.statuscard == null) && oCard.hasClass('hidden')) {
                    oCard.siblings().removeClass('hidden');
                    oCard.removeClass('hidden');
                }

                if (Math.abs(dados.qtd) > 0) {

                    if (Math.abs(dados.qtd) > 999) {
                        if (dados.masc == "R") {
                            oCard.find('span.card_value').append(`<span style="font-size:11px">R$ </span>${formatToUnits(dados.qtd, 1)}`);
                        } else {
                            oCard.find('span.card_value').append(formatToUnits(dados.qtd, 1));
                        }
                    } else {
                        if (dados.masc == "R") {
                            oCard.find('span.card_value').append(`<div>R$ </div>${dados.qtd}`);
                        } else {
                            oCard.find('span.card_value').append(dados.qtd);
                        }
                    }

                    borderColor = dados.corBorda;
                    color = dados.cor;

                    oCard
                        .attr('url', dados.link)
                        .attr('modulo', dados.modulo)
                        .attr('cor', dados.cor)
                        .attr('titulo', dados.titulo)
                        .attr('urlmodal', dados.urlmodal)
                        .attr('urljs', dados.urljs)
                        .attr('idfluxostatus', dados.idfluxostatus)
                        .attr('onclick', 'popLink(this)')
                        .addClass('pointer');
                    if (fixo) {
                        oCard.attr('fixo', true)
                    }
                } else {
                    oCard.find('span.card_value').append('0');
                }

                oCard.attr('class', oCard.attr('class').replace(/border-left-[\w]+/, `border-left-${borderColor}`));
                $($el).find('#card_title_sub').attr('class', $($el).find('#card_title_sub').attr('class').replace(/bg-[\w]+/, `bg-${color}`));
                $($el).find('.card_value').parent().attr('class', $($el).find('.card_value').parent().attr('class').replace(/titulo-[\w]+/, `titulo-${color}`));

                if (dados.qtdAtraso > 0) {
                    oCard.parent().prepend(`
                        <span url="${dados.linkAtraso}" 
                            modulo="${dados.modulo}"
                            cor="${dados.cor}"
                            titulo="${dados.titulo}"
                            urlmodal="${dados.urlmodal}"
                            urljs=""
                            title="Em atraso"
                            onclick="popLink(this)" 
                            class="bg-danger badge badgedash pointer">
                            ${dados.qtdAtraso}
                        </span>
                    `);
                }

                if (fixo == 'Y' && dados.qtdCinza && dados.qtdCinza > 0) {

                    oCard.parent().prepend(`
                        <span url="${dados.linkCinza}" 
                            modulo="${dados.modulo}"
                            titulo="EVENTOS NÃO LIDOS"
                            onclick="popLink(this)" 
                            cor="primary"
                            urlmodal="_modulo=evento&amp;_acao=u"
                            urljs=""
                            title="Não Lidos"
                            fixo
                            class="cbIBadgeSnippet2 pointer bg-secundary badge badgedash2">
                            ${dados.qtdCinza}
                        </span>
                    `);
                }

                if (oCard.find('.card-body div.text-xs.mb-1:first-child')) {
                    if (oCard.attr("titulopersonalizado") != dados.cardTitulo && (oCard.attr("titulopersonalizado") == '')) {
                        oCard.find('.card-body div.text-xs.mb-1:first-child').text(dados.cardTitulo);
                    }
                }

                if (oCard.find('#card_title_sub')) {
                    oCard.find('#card_title_sub').text(dados.cardSubTitulo);
                }

                oCard.addClass('load')
                    .removeClass('loading');
            } catch (e) {
                console.log(`Erro: Iddashcard ->` + idDashCard + `
                ` + e.message);
            }
        }
    }

    async function buscaValorCard(idDashCard, $obj, fixo = 'N', idDashCardReal) {
        let $el = ($obj) ? $obj : $('.dashcard-' + idDashCard);

        if (!$el.hasClass('load')) {
            $el.find('.circularProgressIndicator').removeClass('hidden');
            $el.addClass('loading');
            //$el.find('.card-body div.text-xs.mb-1:first-child').text('Carregando...');

            let idempresa = (getUrlParameter("_idempresa")) ? "?_idempresa=" + getUrlParameter("_idempresa") : '';
            let data = (fixo == 'Y') ? {
                iddashcard: idDashCard,
                fixo: fixo,
                idDashCardReal: idDashCardReal,
            } : {
                iddashcard: idDashCard,
                fixo: fixo,
                habilitarMatriz: habilitarMatriz,
                lps: jsLps
            };

            data = Object.keys(data)
                .map(k => `${encodeURIComponent(k)}=${encodeURIComponent(data[k])}`)
                .join('&');

            let response = await fetch("ajax/buscavalorcarddashboard.php" + idempresa, {
                method: "POST",
                body: data,
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "authorization": (Cookies.get('jwt') || localStorage.getItem("jwt") || "")
                }
            });
            // Cor para quanto n houver registro
            let color = "secondary",
                borderColor = "secondary";

            try {
                let dados = '';
                dados = await response.json();


                let oCard = (fixo == 'Y') ? $obj : $('.dashcard-' + idDashCard + '.loading');

                oCard.find('.circularProgressIndicator')
                    .addClass('hidden');
                if ((dados.statuscard == 'ATIVO' || dados.statuscard == null) && oCard.hasClass('hidden')) {
                    oCard.siblings().removeClass('hidden');
                    oCard.removeClass('hidden');
                }

                if (Math.abs(dados.qtd) > 0) {

                    if (Math.abs(dados.qtd) > 999) {
                        if (dados.masc == "R") {
                            oCard.find('span.card_value').append(`<span style="font-size:11px">R$ </span>${formatToUnits(dados.qtd, 1)}`);
                        } else {
                            oCard.find('span.card_value').append(formatToUnits(dados.qtd, 1));
                        }
                    } else {
                        if (dados.masc == "R") {
                            oCard.find('span.card_value').append(`<div>R$ </div>${dados.qtd}`);
                        } else {
                            oCard.find('span.card_value').append(dados.qtd);
                        }
                    }

                    borderColor = dados.corBorda;
                    color = dados.cor;

                    oCard
                        .attr('url', dados.link)
                        .attr('modulo', dados.modulo)
                        .attr('cor', dados.cor)
                        .attr('titulo', dados.titulo)
                        .attr('urlmodal', dados.urlmodal)
                        .attr('urljs', dados.urljs)
                        .attr('idfluxostatus', dados.idfluxostatus)
                        .attr('onclick', 'popLink(this)')
                        .addClass('pointer');
                    if (fixo) {
                        oCard.attr('fixo', true)
                    }
                } else {
                    oCard.find('span.card_value').append('0');
                }

                oCard.attr('class', oCard.attr('class').replace(/border-left-[\w]+/, `border-left-${borderColor}`));
                $($el).find('#card_title_sub').attr('class', $($el).find('#card_title_sub').attr('class').replace(/bg-[\w]+/, `bg-${color}`));
                $($el).find('.card_value').parent().attr('class', $($el).find('.card_value').parent().attr('class').replace(/titulo-[\w]+/, `titulo-${color}`));

                if (dados.qtdAtraso > 0) {
                    oCard.parent().prepend(`
                        <span url="${dados.linkAtraso}" 
                            modulo="${dados.modulo}"
                            cor="${dados.cor}"
                            titulo="${dados.titulo}"
                            urlmodal="${dados.urlmodal}"
                            urljs=""
                            title="Em atraso"
                            onclick="popLink(this)" 
                            class="bg-danger badge badgedash pointer">
                            ${dados.qtdAtraso}
                        </span>
                    `);
                }

                //if(fixo == 'Y' && dados.qtdCinza && dados.qtdCinza > 0){ altaerado para mostrar no dash Operações
                if (dados.qtdCinza && dados.qtdCinza > 0) {

                    oCard.parent().prepend(`
                        <span url="${dados.linkCinza}" 
                            modulo="${dados.modulo}"
                            titulo="EVENTOS NÃO LIDOS"
                            onclick="popLink(this)" 
                            cor="primary"
                            urlmodal="_modulo=evento&amp;_acao=u"
                            urljs=""
                            title="Não Lidos"
                            fixo
                            class="cbIBadgeSnippet2 pointer bg-secundary badge badgedash2">
                            ${dados.qtdCinza}
                        </span>
                    `);
                }

                if (oCard.find('.card-body div.text-xs.mb-1:first-child')) {
                    if (oCard.attr("titulopersonalizado") != dados.cardTitulo && (oCard.attr("titulopersonalizado") == '')) {
                        oCard.find('.card-body div.text-xs.mb-1:first-child').text(dados.cardTitulo);
                    }
                }

                if (oCard.find('#card_title_sub')) {
                    oCard.find('#card_title_sub').text(dados.cardSubTitulo);
                }

                oCard.addClass('load')
                    .removeClass('loading');
            } catch (e) {
                console.log(`Erro: Iddashcard ->` + idDashCard + `
                ` + e.message);
            }
        }
    }

    async function popLink(vthis) {
        if (!(vthis.attributes.url && vthis.attributes.url.value)) {
            console.warn('Atributo URL não encontrado!');
            return;
        }

        let url = vthis.attributes.url.value;
        let modulo = vthis.attributes.modulo.value;
        let cor = vthis.attributes.cor.value;
        let titulo = vthis.attributes.titulo.value;
        let urlmodal = vthis.attributes.urlmodal.value;
        let urljs = vthis.attributes.urljs.value;
        let idfluxostatus = vthis.attributes.idfluxostatus ? vthis.attributes.idfluxostatus.value : '';
        let fixo = vthis.hasAttribute("fixo");
        if (fixo) {
            var regex = /idevento=\[(\d+(,\d+)*)\]/;
            var eventos = regex.exec(url);
        }
        if (eventos) {
            eventos = eventos[1].split(",");
        }

        if (urljs != '') {
            eval(urljs);
            return;
        }

        $("#_dashresultscontent").remove();
        $("#cbModal").removeClass('url');
        $("#cbModal").removeClass('titulo');

        let strCabecalho = "</strong><label class='fonte08'><span class='titulo-" + cor + "'>" + titulo + "</span></label></strong>";

        let $oExtrairRelatorios = null;
        if (modulo) {
            $oExtrairRelatorios = $(`<div class="col-md-1 initialstate" style="display:inline-block;">
                                        <i class='fa fa-file-text-o floatright dropdown-toggle rel-icon' href="#" role="button" id="dropdownMenuRelatorios" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title='Relatórios'></i>
                                        <ul class="dropdown-menu dropdown-menu-down" id="dropdownMenuRelatoriosItens" aria-labelledby="dropdownMenuRelatorios">
                                        </ul>
                                    </div>`);
        } else {
            $oExtrairRelatorios = $(`<div class="col-md-1 initialstate" style="display:inline-block;">
                                        <i class='fa fa-file-text-o floatright dropdown-toggle rel-icon' href="#" role="button" id="dropdownMenuRelatorios" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title='Relatórios'></i>
                                        <ul class="dropdown-menu dropdown-menu-down" id="dropdownMenuRelatoriosItens" aria-labelledby="dropdownMenuRelatorios">
                                            <li class="dropdown-item"><span style="white-space: nowrap;" erro="1">Nenhum relatório disponível</span></li>
                                        </ul>
                                    </div>`);
        }


        $oExtrairRelatorios.on('click', async function() {

            let othis = $(this);

            if (othis.hasClass('initialstate')) {
                // Carrega relatórios
                let $itens = othis.find('#dropdownMenuRelatoriosItens');

                othis.addClass('loadingstate').removeClass('initialstate');
                $itens.append(`<li class="dropdown-item blink"><span>Aguarde...<span>`);

                let response = await fetch(`ajax/buscarelatoriomodulo.php?modulos=${modulo}&getrepnames=Y&dashboard=Y`, {
                    method: "GET",
                    headers: {
                        'authorization': (Cookies.get('jwt') || localStorage.getItem("jwt") || "")
                    }
                });
                let r = await response.json();
                if (r["error"]) {
                    othis.addClass('errorstate').removeClass('loadingstate');
                    console.warn(r["error"]);
                    $itens.html("").append(`<li class="dropdown-item"><span style="white-space: nowrap;" erro="2">Erro ao recuperar relatórios</span></li>`);
                    return false;
                }

                let dropItem = "";
                let urlRelatorio = null;
                let addidempresa;
                if (r.length == 0) {
                    dropItem = `<li class="dropdown-item"><span style="white-space: nowrap;" erro="3">Nenhum relatório disponível</span></li>`;
                } else {
                    let urlIdPkPargetList = `${$itens.attr('idpkparget')}=${$itens.attr('pargetlist')}`;

                    for (let i of r) {
                        addidempresa = (getUrlParameter("_idempresa") != "") ? "&_idempresa=" + getUrlParameter("_idempresa") : "";
                        urlRelatorio = `?_modulo=menurelatorio&menupai=${i.idmodpai}&_menu=N&_menulateral=N&_novajanela=Y${addidempresa}&_idrep=${i.idrep}&${urlIdPkPargetList}&idfluxostatus=${idfluxostatus}`;
                        dropItem += `<li class="dropdown-item" style="padding:2px 0px;"><a href="${urlRelatorio}" target="_blank" style="white-space: nowrap;color: #898989 !important;">${i.rep.toUpperCase()}</a></li>`;
                    }
                }

                $itens.html("").append(dropItem);

                othis.addClass('loadedstate').removeClass('loadingstate');
            }

        });
        let btOcultar = $(``);
        let tamDiv = 4;
        if (fixo == 'N' && eventos) {
            btOcultar = $(`<div class="col-md-1 initialstate" style="display:inline-block;">
                                <i class='fa fa-eye-slash pointer floatright dropdown-toggle rel-icon' eventos="[${eventos}]" id='btOcultaConcluido' title='Ocultar concluídos'></i>
                            </div>`);
            tamDiv = 3;
        }
        //Altera o cabeçalho da janela modal
        $("#cbModalTitulo")
            .replaceWith($(`<div id="cbModalTitulo" class="col-xs-12 col-md-11">
                        <div class="col-xs-12 col-md-${tamDiv}">
                            <h4>` +
                strCabecalho + `&nbsp;<label id='resultadosEncontrados' class='fonte08'></label>
                            </h4>
                        </div>
                        <div class="col-xs-12 col-md-5 text-center">
                            <input class="size20 w-100" placeholder="Filtrar Resultados" id="filtraresutado" />
                        </div>
                    </div>`));

        $("#cbModalTitulo")
            .append($oExtrairRelatorios)
            .append(`<div class="col-md-1 initialstate" style="display:inline-block;"><i class='fa fa-file-excel-o floatright dropdown-toggle rel-icon' id='btPrintNucleo' title='CSV' onclick="gerarCsv('${titulo}')"></i></div>
                    <div class="col-md-1 initialstate" style="display:inline-block;"><i class='fa fa-print floatright dropdown-toggle rel-icon' id='btPrintNucleo' title='Impressão' onclick="printNucleo('${titulo}')"></i></div>`)
            .append(btOcultar);;

        $(`#btOcultaConcluido`).on("click", async (element) => {
            if (confirm(`Ao confirmar esta ação, todos os eventos concluídos serão ocultados`)) {
                await $.ajax({
                    tab: modulo,
                    url: "ajax/ocultaeventosconcluidos.php",
                    type: "POST",
                    data: {
                        eventos: eventos
                    },
                    success: function(ret) {
                        r = JSON.parse(ret)
                        r.forEach((element, index) => {
                            $("tr td:contains('" + element.idevento + "'):visible(true)").parent().fadeOut()
                            var index = eventos.indexOf(element.idevento);
                            if (index !== -1) {
                                eventos.splice(index, 1);
                            }
                        });
                        var ideventos = eventos; // Substitua pelos valores desejados
                        // Use o método replace para substituir os valores de idevento na URL
                        var novaUrl = url.replace(/idevento=\[[^\]]*\]/, 'idevento=[' + ideventos.join(',') + ']');

                        vthis.attributes.url.value = novaUrl; // Imprime a nova URL com os valores de idevento substituídos

                    },
                    error: function(objxmlreq) {
                        alert('Erro:<br>' + objxmlreq.status);
                    }
                });
            }
        });

        if (url != '' && url != 'null') {
            //console.log('teste'+url);
            if (url.search("php") >= 0 || url.search("novajanela") >= 0) {
                link = './' + url;
                janelamodal(`${url}&idfluxostatus=${idfluxostatus}`);
            } else {
                let idempresa = (getUrlParameter("_idempresa")) ? "&_idempresa=" + getUrlParameter("_idempresa") : '';
                link = 'form/_modulofiltrospesquisa.php?_pagina=0' + idempresa;

                let response = await fetch(link + "&" + url);

                try {
                    let data = await response.json();
                    //Json contem resultados encontrados?
                    if (!$.isEmptyObject(data)) {
                        //Nos casos onde existia um número muito grande linhas, o browser estava apresentando lentidão. Caso o número de linhas seja > configuracao do Mà³dulo, direcionar para tela de search
                        if (parseInt(data.numrows) > parseInt(CB.jsonModulo.limite) || data.numrows > 2000) {
                            alertAtencao("Mais de " + CB.jsonModulo.limite + " resultados foram encontrados!\n<a href='?" + vGetAutofiltro + "' target='_blank' style='color:#00009a;'><i class='fa fa-filter'></i> Clique aqui para filtrar os resultados encontrados.</a>");
                            janelamodal("?" + vGetAutofiltro);
                        } else {
                            var tblRes = CB.montaTableResultados(data, function(obj, event) {

                                oTr = $(obj);
                                oTr.css("backgroundColor", "transparent");

                                //janelamodal("'"+urlmodal+"'" + oTr.attr("goParam"));

                                janelamodal('?' + urlmodal + '&' + oTr.attr("goParam"));

                            });
                            $("#cbModal #cbModalCorpo").html(tblRes).hide();
                            //$("#cbModal #cbModalCorpo").attr("id", "cbCarregando");

                            $("#cbModal #cbModalCorpo #restbl thead td").each(function(i, o) {
                                $(o).append('&nbsp;&nbsp;<i  class="fa fa-arrow-down pointer" title="Ordenar Crescente" style="font-size:0.8em;opacity:0" attr="desc"></i>&nbsp;<i class="fa fa-arrow-up pointer" title="Ordenar Decrescente" style="font-size:0.8em;opacity:0" attr="asc"></i>')
                                //$(o).addClass('mostra');
                            });

                            await $.ajax({
                                tab: modulo,
                                url: "inc/js/custom-filter-carbon/custom_filter_carbon_empresa.php",
                                type: "POST",
                                data: {
                                    tab: modulo
                                },
                                success: function(ret) {
                                    r = JSON.parse(ret)
                                    let auxList = [];
                                    for (let i of r) {
                                        auxList[Object.keyAt(i)] = i.sigla;
                                    }

                                    let selector = $(`#cbModalCorpo #restbl td[col="idempresa"]`);
                                    let order = selector.children();
                                    selector.text("Empresa").append(order);

                                    let columnIndex = selector.index() + 1;

                                    $('#cbModalCorpo #restbl tbody tr td:nth-child(' + columnIndex + ')').each((i, o) => {
                                        o.textContent = auxList[o.textContent];
                                    });

                                    $(`#cbModalCorpo #restbl`).addClass("modificado");
                                    $("#cbModal").addClass("noventa").modal();
                                    $("#cbModal #cbModalCorpo").show();
                                },
                                error: function(objxmlreq) {
                                    alert('Erro:<br>' + objxmlreq.status);
                                }
                            });

                            $("body").append(`<div id="_dashresultscontent" class="hideshowtable">
                                                <table>${tblRes.html()}</table>
                                            </div>`);

                            if (data.numrows) {
                                $("#resultadosEncontrados").html("(" + data.numrows + " resultados encontrados)").attr("cbnumrows", data.numrows);
                            }

                            $("#dropdownMenuRelatoriosItens").attr('pargetlist', function(index, value) {
                                let pargetList = [];
                                let idPkParget = null;
                                for (let o of Object.keys(data["rows"])) {
                                    if (!idPkParget) idPkParget = Object.keyAt(data["rows"][o]["parget"]);
                                    pargetList.push(Object.values(data["rows"][o]["parget"])[0]);
                                }
                                this.setAttribute('idpkparget', idPkParget);
                                return pargetList.join();
                            });
                            $("#filtraresutado").on('keyup', function() {
                                var filter, table, tr, a, i, txtValue;
                                filter = normalizeToBase(this.value.toUpperCase());
                                table = document.getElementById("restbl");
                                tr = table.getElementsByTagName("tr");
                                var arrffilter = filter.split(" ");
                                for (i = 1; i < tr.length; i++) {
                                    a = tr[i].getElementsByTagName("td");
                                    some = true;
                                    for (ii = 0; ii < a.length; ii++) {
                                        for (iii in arrffilter) {
                                            txtValue = normalizeToBase(a[ii].textContent) || normalizeToBase(a[ii].innerText);
                                            if (txtValue.toUpperCase().match(arrffilter[iii])) {
                                                some = false;
                                            }
                                        }

                                    }
                                    if (some != true) {
                                        tr[i].style.display = "";
                                    } else {
                                        tr[i].style.display = "none";
                                    }
                                }
                            });

                            function sortTable(e) {
                                var th = e.target.parentElement;
                                $(e.target).addClass("azul");
                                $(th).addClass("ativo");
                                $(e.target).siblings().removeClass("azul");
                                $(th).siblings().removeClass("ativo");
                                $(e.target.parentElement).siblings().each((e, o) => {
                                    $(o).children().removeClass('azul').css('opacity', '0')
                                })
                                var ordenacao = $(e.target).attr("attr");
                                switch (ordenacao) {
                                    case 'asc':
                                        colunas = -1;
                                        break;
                                    case 'desc':
                                        colunas = 1;
                                        break;

                                    default:
                                        colunas = 1
                                        break;
                                }

                                var n = 0;
                                while (th.parentNode.cells[n] != th) ++n;
                                var order = th.order || 1;
                                //th.order = -order;
                                var t = this.closest("thead").nextElementSibling;

                                t.innerHTML = Object.keys(t.rows)
                                    .filter(k => !isNaN(k))
                                    .map(k => t.rows[k])
                                    .sort((a, b) => order * (isNaN(typed(a)) && isNaN(typed(b))) ? ((typed(a).localeCompare(typed(b)) > 0) ? colunas : -colunas) : (typed(a) > typed(b) ? colunas : -colunas))
                                    .map(r => r.outerHTML)
                                    .join('')

                                function typed(tr) {
                                    var s = tr.cells[n].innerText;
                                    if (s.match(",")) {
                                        isNaN(s.replaceAll(",", ".")) ? s = s.toString() : s = s.replaceAll(",", ".")
                                    }
                                    if (isNaN(s) && s.match(/^[a-zA-Z]+/)) {
                                        var d = s;
                                        var date = d;
                                    } else {
                                        if (s.match("/") && s.match(/^[a-zA-Z]+/) == null) {
                                            var d = mda(s);
                                            var date = Date.parse(d);
                                        } else {
                                            var d = s;
                                            var date = d;
                                        }
                                    }
                                    if (!isNaN(date)) {
                                        return isNaN(date) ? s.toLowerCase() : Number(date);
                                    } else {
                                        if (!isNaN(s.replaceAll(",", '.'))) {
                                            return Number(s.replaceAll(",", '.'));
                                        } else {

                                            return s.toLowerCase();
                                        }
                                    }
                                }
                            }

                            $('#cbModalCorpo #restbl thead td i').on('click', sortTable);

                            $('#cbModalCorpo #restbl thead td').mouseover(function() {
                                $(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e, o) => {
                                    $(o).css("opacity", "1").addClass('hoverazul')
                                })
                            });
                            $('#cbModalCorpo #restbl thead td').mouseout(function() {
                                $(this).children().not("[id=cbOrdCres], [id=cbOrdDecr]").each((e, o) => {
                                    if (!$(o).hasClass('azul')) {
                                        $(o).css("opacity", "0").removeClass('hoverazul')
                                    }
                                })
                            });
                        }
                    } else {
                        alert("Nenhum resultado encontrado.");
                    }
                } catch (e) {
                    var str = JSON.stringify(e.message);
                    var part = str.substring(str.lastIndexOf("[") + 1, str.lastIndexOf("]"));
                    if (part) {
                        alertAtencao("Sem permissão ao módulo: " + part + "<br>Favor entrar em contato com Departamento de Processos - Ramal: 110");
                    }
                }

                CB.aguarde(false);
                if (CB.limparResultados == true) {
                    CB.resetDadosPesquisa();
                }
            }
        }
    }

    function limpaFiltro() {
        CB.setPrefUsuario('u', CB.modulo, null);
        $(".botao-menu-lateral.ativo").removeClass('ativo');
        $(".panel-primary[id^='div-conf-']:visible").hide();
    }

    function printNucleo(titulo) {
        if ($("#restbl").is(":visible")) {
            let titleAnt = document.title;
            document.title = titulo;
            window.print();
            document.title = titleAnt;
        }
    }

    function gerarCsv(tituloCsv) {
        if ($("#restbl").is(":visible")) {
            var CsvContent = "";
            var virg = "";

            $("#restbl").find("tr").each((i, o) => {
                $(o).find("td").each((j, k) => {
                    $txtsvg = $(k).text().trim().replace("#", "//")
                    CsvContent += virg + $txtsvg;
                    virg = ";";
                });
                CsvContent += "\n";
                virg = "";
            });

            tituloCsv = tituloCsv.toLowerCase().replaceAll(/[^a-zA-Z0-9]/g, '');

            let dt = new Date();
            let csvDate = `${
                dt.getDate().toString().padStart(2, '0')}${
                (dt.getMonth()+1).toString().padStart(2, '0')}${
                dt.getFullYear().toString().padStart(4, '0')}${
                dt.getHours().toString().padStart(2, '0')}${
                dt.getMinutes().toString().padStart(2, '0')}${
                dt.getSeconds().toString().padStart(2, '0')}`;

            let hiddenElement = document.createElement('a');
            hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(CsvContent);
            hiddenElement.target = '_blank';
            hiddenElement.download = tituloCsv + csvDate + '.csv';
            hiddenElement.click();
        }
    }

    function ocultarMenuLateral() {
        $('#_menu_lateral_').hide()
        $('#_col_md_control_').removeAttr('class');
        $('#_col_md_control_').attr('class', 'col-md-12');
        $('#_col_md_control_').prepend(`<a id="_btn_mostrar_menu_" title="Exibir menu lateral" class="tipoItem pointer list-group-item" style="font-size: 16px;width: 32px;text-align:center;padding: 3px!important;margin-bottom: -42px;margin-top: 10px;border-radius: 8px;" href="javascript:mostrarMenuLateral();"><i class="fa fa-angle-right"></i></a>`)

    }

    function mostrarMenuLateral() {
        $('#_menu_lateral_').show()
        $('#_col_md_control_').removeAttr('class');
        $('#_col_md_control_').attr('class', 'col-md-10');
        $('#_btn_mostrar_menu_').remove()

    }

    if ($("#dashNovoEvento").length == 0) {
        $(`#div-conf-27-${idPessoa}`).append(`
            <div style="position: absolute;top: 20px;left: 260px;">
                <a href="javascript:window.location.href ='?_modulo=alerta'" class="fa fa-globe" style="float: left;border: 1px solid #ddd; padding: 4px; border-radius: 8px; background: #eee; margin-left: 4px;" title="Eventos"></a>
                <button class="btn btn-xs btn-primary" onclick="novaTarefa()" style="float: left; position: relative; margin-left: 10px;">
                    Novo Evento
                </button>
            </div>
        `);
    }

    function formatToUnits(number, precision) {
        const abbrev = ['', 'k', 'm', 'b', 't'];
        const unrangifiedOrder = Math.floor(Math.log10(Math.abs(number)) / 3)
        const order = Math.max(0, Math.min(unrangifiedOrder, abbrev.length - 1))
        const suffix = abbrev[order];

        return (number / Math.pow(10, order * 3)).toFixed(precision) + suffix;
    }

    for (let chave in eventosAlerta) {
        $(`
            <span 
                url="${eventosAlerta[chave]['url']}&amp;_pagina=0&amp;_ordcol=prazo&amp;_orddir=asc" 
                onclick="popLink(this)" class="cbIBadgeSnippet2 bg-secundary badge badgedash2"
                modulo="${eventosAlerta[chave]['modulo']}"
                titulo="EVENTOS NÃO LIDOS"
                cor="primary"
                urlmodal="_modulo=evento&amp;_acao=u"
                urljs=""
                fixo
                title="Não Lidos">
                ${eventosAlerta[chave]['value']}
            </span>
        `).insertBefore(`div[modulo="${eventosAlerta[chave]['modulo']}"][tipoobjeto="${eventosAlerta[chave]['tipoobjeto']}"][iddashcard="${eventosAlerta[chave]['objeto']}"][iddashcardreal]`);
    }
    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>