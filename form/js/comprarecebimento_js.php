<script>
	//------- Injeção PHP no Jquery -------
	//------- Injeção PHP no Jquery -------

	//------- Funções JS -------
	//------- Funções JS -------

	//------- Funções Módulo -------
	function verificatransp(vthis, inidtranspr, innome) 
    {
        var idtransportadora = $("[name=_1_u_nf_idtransportadora]").val();

        if (confirm("Transportadora padrão ( " + innome + " ), Deseja realmente alterar a transportadora?") == true) {
            $("[name=_1_u_nf_idtransportadora] option[value='" + idtransportadora + "']").attr("selected", "selected");
        } else {
            location.reload();
        }
    }

	function atualizaobsint(vthis) 
    {
        CB.post({
            objetos: "_x_u_nf_idnf=" + $("[name=_1_u_nf_idnf]").val() + "&_x_u_nf_obsinterna=" + $(vthis).val(),
            parcial: true
        })
    }
	//------- Funções Módulo -------
</script>