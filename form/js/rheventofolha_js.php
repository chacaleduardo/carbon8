<script>
    function irheventofolha(idrheventofolha) 
	{
		CB.post({
			objetos: "_x_i_rheventofolhaitem_idrheventofolha=" + idrheventofolha,
			parcial: true			
		})
	}

    function drheventofolha(idrheventofolhaitem) 
	{
		CB.post({
			objetos: "_x_u_rheventofolhaitem_idrheventofolhaitem=" + idrheventofolhaitem+"&_x_u_rheventofolhaitem_status=INATIVO",
			parcial: true			
		})
	}

	function preencheti(inid) {

		$("#idtipoprodserv" + inid).html("<option value=''>Procurando....</option>");

		$.ajax({
			type: "get",
			url: "ajax/buscacontaitem.php",
			data: {
				idcontaitem: $("#idcontaitem" + inid).val()
			},

			success: function(data) {
				$("#idtipoprodserv" + inid).html(data);
			},

			error: function(objxmlreq) {
				alert('Erro:<br>' + objxmlreq.status);

			}
		}) //$.ajax

	}

</script>