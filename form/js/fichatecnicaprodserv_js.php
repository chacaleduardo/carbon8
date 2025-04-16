<script>
    //------- Injeção PHP no Jquery -------
	var idprodserv = '<?=$_GET['idprodserv'] ?>';
    //------- Injeção PHP no Jquery -------

    function buscarKdardex(){
        var dataInicial = $('#data_inicial').val();
        var dataFinal = $('#data_final').val();
        var inicio = moment(dataInicial, 'DD/MM/YYYY');
        var fim = moment(dataFinal, 'DD/MM/YYYY');

        // Calcular a diferença em dias
        var diferencaDias = fim.diff(inicio, 'months');

        //if(diferencaDias <= 6){
            CB.post({
                objetos: {
                    "_1_u_prodserv_idprodserv": idprodserv,
                    "datainicial": dataInicial,
                    "datafinal": dataFinal
                },
                parcial: true,
                msgSalvo: false
            });
        //} else {
           // alert('O intervalo está superior a 6 meses.');
        //}
    }

    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape1
</script>