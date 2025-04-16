<script>
    $('.selectpicker').selectpicker('render');
    const formulaRotuloHist = <?= json_encode(array_map(function($item) {
        $objetoRetorno = unserialize(base64_decode($item['jobjeto']));
        $objetoRetorno['versao'] =  $item['versaoobjeto'];

        return $objetoRetorno;
    }, $versoesFormulaRotulo)) ?>;
    
    CB.prePost = function() {
        if($('#indicacao').data('modificado')) $('#indicacao').attr('name', `_1_${CB.acao}_formularotulo_indicacao`)
        if($('#formula').data('modificado')) $('#formula').attr('name', `_1_${CB.acao}_formularotulo_formula`)
        if($('#cepas').data('modificado')) $('#cepas').attr('name', `_1_${CB.acao}_formularotulo_cepas`)
    }

    $('.alter').on('keydown', function() {
        if($(this).val() != '' && !$(this).data('modificado')) $(this).data('modificado', 'true');
    })

    $('#input-rotulo').on('change', function(e) {
        const idProdServFormulaRotulo = $(this).val();

        $.ajax({
            method: 'GET',
            url: '../ajax/prodservformularotulo.php',
            dataType: 'json',
            data: {
                action: 'buscarPorChavePrimaria',
                params: idProdServFormulaRotulo
            },
            success: res => {
                const {indicacao, formula, idfrasco, modousar, cepas, descricao, conteudo, programa, error} = res;

                if(error) {
                    alertaAtencao('Ocorreu um erro!');
                    return;
                }

                $('#input-frasco').selectpicker('val', idfrasco ?? '');
                $('#indicacao').val(indicacao ?? '');
                $('#formula').val(formula ?? '');
                $('#modousar-elemento').text(modousar ?? '');
                $('#modousar').val(modousar ?? '');
                $('#cepas').val(cepas ?? '');
                $('#descricao-elemento').text(descricao ?? '');
                $('#descricao').val(descricao ?? '');
                $('#descricao-elemento').text(descricao ?? '');
                $('#descricao').val(descricao ?? '')
                $('#conteudo-elemento').text(conteudo ?? '');
                $('#conteudo').val(conteudo ?? '');
                $('#programa-elemento').text(programa ?? '');
                $('#programa').val(programa ?? '');
            },
            error: err => {
                alertaAtencao('Ocorreu um erro!');
                console.log(err)
            }
        })
    });

   function detalhesVersao(posicao) {
    const formulaRotuloHistSelecionada = formulaRotuloHist[posicao];

    if(!formulaRotuloHistSelecionada) return;
    
    let modalFormulaRotulo = `<table border='1'>
                                <thead style='background-color: #c0c0c0;'>
                                    <tr>
                                        <th>Frasco</th>
                                        <th>Rótulo</th>
                                        <th>Inidicações</th>
                                        <th>Cepas</th>
                                        <th>Fórmula</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>${formulaRotuloHistSelecionada.frasco ?? '-'}</td>
                                        <td>${formulaRotuloHistSelecionada.rotulo ?? '-' }</td>
                                        <td>${formulaRotuloHistSelecionada.indicacao ?? '-'}</td>
                                        <td>${formulaRotuloHistSelecionada.cepas ?? '-'}</td>
                                        <td>${formulaRotuloHistSelecionada.formula ?? '-'}</td>
                                    </tr>
                                </tbody>
                                <thead style='background-color: #c0c0c0;' >
                                   <tr>
                                        <th>Descrição</th>
                                        <th colspan='1'>Conteúdo</th>
                                        <th colspan='2'>Modo de usar</th>
                                        <th>Programa de utilização</th>
                                   </tr>
                                </thead>
                                <tbody>
                                   <tr>
                                        <td>${formulaRotuloHistSelecionada.descricao ?? '-'}</td>
                                        <td colspan='1'>${formulaRotuloHistSelecionada.conteudo ?? '-'}</td>
                                        <td colspan='2'>${formulaRotuloHistSelecionada.modousar ?? '-'}</td>
                                        <td>${formulaRotuloHistSelecionada.programa ?? '-'}</td>
                                   </tr>
                                </tbody>
                            </table>`;

        CB.modal({
            corpo: modalFormulaRotulo,
            titulo: `Versão ${formulaRotuloHistSelecionada.versao}.0`,
            classe: 'oitenta',
        });	
   }
</script>