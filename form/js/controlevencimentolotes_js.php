<script>
    const idUnidadePadrao = '<?= $idunidadepadrao ?>';
    var gIdEmpresaModulo = '<?= $_GET['_idempresa'] ?>'

    $('.selectpicker').selectpicker();

    // Buscar lotes
    $('#btn-buscar').on('click', buscarLancamentos);

    // Marcar todos produtos
    $('#check-todos').on('click', function() {
        $('.check-produto').click();
    });

    // Retirar / Dar baixa
    $('#btn-retirar').on('click', inserirConsumo);

    $('#data-vencimento').selectpicker();

    $('#tabela-produtos').on('change', 'select.descricao', function() {
        $(this).closest('tr').removeClass('lote-sem-descricao');
    })

    function buscarLancamentos() {
        const dataVencimento = $('#data-vencimento').val(),
            tabelaLoteJQ = $('#tabela-produtos'),
            tabelaCorpoJQ = tabelaLoteJQ.find('tbody'),
            qtdJQ = $('#qtd-registros'),
            idUnidade = $('#unidade').val() ? $('#unidade').val().join('|') : null;

        if (!dataVencimento) return alertAtencao('Informe a data de vencimento!');

        $.ajax({
            url: './../../ajax/lote.php',
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'buscarLotesVencidosOuProximos',
                params: [dataVencimento, idUnidade]
            },
            success: res => {
                if (res.error) return alertAtencao(res.error);

                const quantidade = res.length;
                qtdJQ.text(quantidade);

                if (Array.isArray(res) && !res.length) return alertAtencao('Nenhum lote encontrado!');

                tabelaCorpoJQ.html('');

                let produtosHTML = "";
                for (indice in res) produtosHTML += montarLinhaProduto(res[indice]);

                tabelaCorpoJQ.html($(produtosHTML));

                mostrarTabelaLotes();
            }
        })
    }

    function mostrarTabelaLotes() {
        $('#lotes').removeClass('hide');
    }

    function ocultarTabelaLotes() {
        $('#lotes').addClass('hide');
    }

    function montarLinhaProduto(produto) {
        return `<tr class="text-center">
                            <td>${produto.sigla}</td>
                            <td>${produto.idlote}</td>
                            <td>${produto.cliente}</td>
                            <td>${produto.descr}</td>
                            <td>
                                <a href="/?_modulo=lotelogistica&_acao=u&idlote=${produto.idlote}&_idempresa=${gIdEmpresa}" target="_blank">
                                    ${produto.partidainterna}
                                </a>
                            </td>
                            <td>${produto.partida}</td>
                            <td>${produto.exercicio}</td>
                            <td>${formatarData(produto.vencimento)}</td>
                            <td>${produto.estoque}</td>
                            <td>
                                <select name="" id="" class="form-control descricao">
                                    <option value=""></option>
                                    <option value="Vencimento">Vencimento</option>
                                    <option value="Perda - Desaparecido ">Perda - Desaparecido</option>
                                    <option value="Perda - Formulação Incorreta">Perda - Formulação Incorreta</option>
                                    <option value="Perda - Contaminado">Perda - Contaminado</option>
                                    <option value="Perda - Semente Cancelada">Perda - Semente Cancelada</option>
                                    <option value="Perda - Inventario">Perda - Inventario</option>
                                </select>
                            </td>
                            <td>
                                <input id="lote-${produto.idprodserv}" data-idlote="${produto.idlote}" data-idlotefracao="${produto.idlotefracao}" data-estoque="${produto.estoque}" type="checkbox" name="" class="check-produto" />
                            </td>
                        </tr>`
    }

    // Dar baixa
    function inserirConsumo() {
        const lotesMarcadosJQ = $('.check-produto:checked');
        let objetos = {},
            i = 1,
            linhaLote = [];

        if (lotesMarcadosJQ.length) {
            lotesMarcadosJQ.each((index, element) => {
                let linhaJQ = $(element).closest('tr'),
                    descricaoSelect = linhaJQ.find(' .descricao');

                if(!descricaoSelect.val()) {
                    linhaJQ.addClass('lote-sem-descricao');

                    return alertAtencao('Selecione a descrição para os itens destacados!');
                }

                let idLote = $(element).data('idlote'),
                    idLoteFracao = $(element).data('idlotefracao'),
                    estoque = $(element).data('estoque');

                objetos[`_${i}_i_lotecons_idlote`] = idLote;
                objetos[`_${i}_i_lotecons_idlotefracao`] = idLoteFracao;
                objetos[`_${i}_i_lotecons_qtdd`] = estoque;
                objetos[`_${i}_i_lotecons_obs`] = descricaoSelect.val();

                linhaLote.push($(element).parent().parent());

                i++;
            });

            if (!Object.keys(objetos).length) return alertAtencao('Ocorreu um erro ao realizar a baixa!');

            CB.post({
                objetos,
                refresh: false
            });

            linhaLote.forEach(item => item.remove());

            alertAzul('Baixa realizada com sucesso!');
        }
    }

    function abrirAbaLote(idLote) {
        let link = `/?_modulo=lotelogistica&_acao=u&idlote=${idLote}&_idempresa=${gIdEmpresa}`;

        window.open(link, "_blank");

        return false;
    }
</script>