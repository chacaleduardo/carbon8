<script>
    function removeVinculo(idPessoaObjeto){
        CB.post({
            objetos: {
                _x_d_pessoaobjeto_idpessoaobjeto: idPessoaObjeto
            },
            parcial: true
        })
    }

    function atualizaContato(idpessoacontato, input){
        CB.post({
            objetos: {
                _x_u_pessoacontato_idpessoacontato: idpessoacontato,
                _x_u_pessoacontato_idcontato: input.value,
            },
            parcial: true
        })
    }

    $('.selectpicker').selectpicker();
</script>