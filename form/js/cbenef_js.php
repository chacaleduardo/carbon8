<script>
    const acao = '<?= $_acao ?? 'i' ?>';
    const idCbenef = '<?= $_1_u_cbenef_idcbenef ?>';
    let index = ($('#cbenef-items > div').length + 1) ?? 1;

    $('#add-item').on('click', adicionarItem);
    $('#cbenef-container').on('click', '.btn-remove', function() {
        removerItem(this);
    });

    function adicionarItem() {
        const indiceAtual = gerarIndice();
        $('#sem-itens').hide();

        let item = `
            <div class="d-flex form-group">
                <input type="text" name="_c${indiceAtual}_i_cbenefitem_idcbenef" class="form-control hidden" value="${idCbenef}" hidden />
                <div class="col-xs-3">
                    <input type="text" name="_c${indiceAtual}_i_cbenefitem_cst" class="form-control" value="" placeholder="Digite o CST" vnulo />
                </div>
                <div class="col-xs-4">
                    <input type="text" name="_c${indiceAtual}_i_cbenefitem_ncm" class="form-control" value="" placeholder="Digite o NCM" vnulo />
                </div>
                <div class="col-xs-4">
                    <input type="text" name="_c${indiceAtual}_i_cbenefitem_cbenef" class="form-control" value="" placeholder="Digite o CBENEF" vnulo />
                </div>
                <div class="col-xs-1">
                    <i class="fa fa-trash vermelho pointer fa-2x btn-remove" title="Remover item"></i>
                </div>
            </div>
        `;
        $('#cbenef-items').append(item);
    }

    function removerItem(element) {
        let idCbenefItem = $(element).data('idcbenefitem');

        if(idCbenefItem) {
            if(!confirm('Deseja realmente remover este item?')) return false;

            alertAguarde();

            // Remover do banco
            CB.post({
                objetos: {
                    $_d1_d_cbenefitem_idcbenefitem: idCbenefItem
                },
                parcial: true,
                posPost: () => {
                    $(element).closest('.form-group').remove();

                    if(!$('#cbenef-items > div:not(#sem-itens)').length) $('#sem-itens').show();
                }
            })
        } else {
            $(element).closest('.form-group').remove();
            if(!$('#cbenef-items > div:not(#sem-itens)').length) $('#sem-itens').show();
        }
    }

    function gerarIndice() {
        return index++;
    }
</script>