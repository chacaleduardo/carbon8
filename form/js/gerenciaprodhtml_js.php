<script>
	//------- Injeção PHP no Jquery -------
    var idsnippet = '<?=$idsnippet?>';
	var controleass = '<?=$arridresultado[$controleass]?>';
	var ulrpadrao = `_modulo=gerenciaprodcorpo&idprodserv=${controleass}`;
	//------- Injeção PHP no Jquery -------

    //------- Funções JS -------
	//Permitir ordenar/arrastar os TR de insumos
	$(".tblotes tbody").sortable({
		update: function(event, objUi) {
			ordenaInsumos();
		}
	});

	//Permitir dropar o insumo
	$(".soltavel").droppable({
		drop: function(event, ui) {
			$this = $(this); //TR
			var idlote = ui.draggable.attr("idloteins");
			var idpool = $this.attr("idpool");
			var idlote2 = $this.attr("idloteins");
			if (idpool == "") {
				criapool(idlote, idlote2);
			} else {
				geralotepool(idpool, idlote);
			}
		}
	});
    //------- Funções JS -------

    //------- Funções Módulo -------
   
    //------- Funções Módulo -------

    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape_2
</script>