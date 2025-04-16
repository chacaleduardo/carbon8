<script>
    const exames = <?= json_encode(array_map(function ($item) {
                        return [
                            'value' => $item['idprodserv'],
                            'label' => $item['descr']
                        ];
                    }, $exames)) ?>;

    $('#btn-search').on('click', function() {
        filtrarValores($('#exames').val());
    });

    $("#exames").autocomplete({
        source: exames,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                lbItem = item.label;
                return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
            };
        },
        select: function(e, ui) {
            filtrarValores(ui.item.label);
        }
    });

    $("#exames").off('blur');
    $("#exames").on('blur', function() {
        $(this).autocomplete('close');
    })

    function filtrarValores(value) {
        $(`#tabela-exames tr`).removeClass('hidden');

        if (value)
            $(`#tabela-exames tr`).each((indice, element) => {
                if (!$(element).find('.descricao').text().includes(value)) $(element).addClass('hidden');
            });
    }
</script>