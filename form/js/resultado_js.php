<script>
    const resultados = <?= json_encode($resultados ?? []) ?>;

    $('#btn-search').on('click', function() {
        filtrarValores();
    });

    $("#animal").autocomplete({
        source: buscarResultados(),
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                lbItem = item.label;
                return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
            };
        }
    });

    $("#tutor").autocomplete({
        source: buscarTutor(),
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                lbItem = item.label;
                return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
            };
        },
    });

    $("#animal, #tutor").off('blur');
    $("#animal, #tutor").on('blur', function() {
        $(this).autocomplete('close');
    });

    $('#periodo').daterangepicker({
        "showDropdowns": true,
        "minDate": moment("01012006", "DDMMYYYY"),
        "locale": CB.jDateRangeLocale,
        ranges: {
            'Hoje': [moment(), moment()],
            'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Amanhã': [moment().add(1, 'days'), moment().add(1, 'days')],
            'Esta Semana': [moment().subtract(new Date().getDay(), 'days'), moment().endOf('week')],
            'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
            'Próximos 7 dias': [moment(), moment().add(6, 'days')],
            'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
            'Próximos 30 dias': [moment(), moment().add(29, 'days')],
            'Este mês': [moment().startOf('month'), moment().endOf('month')],
            'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Próximo mês': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
            'Este Ano': [moment().startOf('year'), moment().endOf('year')],
            'Ano passado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            'Próximo Ano': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
        },
        opens: 'center'
    }).on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('').attr('cbdata', '').addClass('cinzaclaro');
        CB.oDaterangeTexto.html("");
        CB.oDaterange.find('#cbCloseDaterange').off('click').addClass('hide');
        CB.limparResultados = true;
        CB.resetVarPesquisa();
    }).on('apply.daterangepicker', function(ev, picker) {
        CB.setIntervaloDataPesquisa(picker.startDate, picker.endDate);
        CB.limparResultados = true;
        CB.resetVarPesquisa();
    });

    function buscarResultados() {
        const resultadosMap = resultados.map(resultado => {
            return {
                label: resultado.paciente,
                value: resultado.paciente
            }
        });
        return resultadosMap
            .filter((item, index) => resultadosMap.map((itemMap) => itemMap.value).indexOf(item.value) === index);
    }

    function buscarTutor() {
        const resultadosMap = resultados.map(resultado => {
            return {
                label: resultado.tutor,
                value: resultado.tutor
            }
        });
        return resultadosMap
            .filter((item, index) => resultadosMap.map((itemMap) => itemMap.value).indexOf(item.value) === index);
    }

    function filtrarValores() {
        debugger
        const animal = $('#animal').val(),
            tutor = $('#tutor').val(),
            periodo = $('#periodo').val();

        $(`#tabela-resultados tr`).addClass('hidden');

        $(`#tabela-resultados tr`).each((indice, element) => {
            if (
                ($(element).find(`.paciente`).text().includes(animal) || !animal) &&
                ($(element).find(`.tutor`).text().includes(tutor) || !tutor)
            ) {
                if (periodo) {
                    let [dataInicial, dataFinal] = periodo.split('-');

                    if (isDateInRange($(element).find(`.dataamostra`).text(), dataInicial, dataFinal))
                        $(element).removeClass('hidden');
                } else
                    $(element).removeClass('hidden');
            }
        });
    }

    // Função para converter a data no formato 'DD/MM/AAAA' para 'AAAA-MM-DD'
    function formatDateToISO(dateStr) {
        const [day, month, year] = dateStr.split('/');
        return `${year}-${month}-${day}`;
    }

    // Verifica se a data está no período entre duas outras datas
    function isDateInRange(dateStr, startDateStr, endDateStr) {
        const date = new Date(formatDateToISO(dateStr));
        const startDate = new Date(formatDateToISO(startDateStr.trim()));
        const endDate = new Date(formatDateToISO(endDateStr.trim()));

        // Retorna true se a data estiver entre a data de início e fim
        return date >= startDate && date <= endDate;
    }

    function abrirPdf(idResultado, modeloResultado) {
        let moduloResultado = 'emissaoresultadodinamicoreferencia';

        if(modeloResultado !== 'DINAMICOREFERENCIA')
            moduloResultado = 'emissaoresultadopet';

        window.open(`/report/${moduloResultado}.php?idresultado=${idResultado}`, '_blank');
    }
</script>