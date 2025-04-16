<script>
    function gerarCobranca(idnf){
        CB.post({
            objetos: `_x_u_nf_idnf=` + idnf + `&_x_u_nf_status=APROVADO`,
            parcial: true
        })
    }
    
</script>