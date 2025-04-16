function toggleFiltrarTarefas(filtro) {
	$('.link').attr("href", function () {
		console.log('link');
		$(this).attr('href', $(this).attr('href').replace("vfilter=todas", "vfilter="));
		$(this).attr('href', $(this).attr('href').replace("vfilter=ocultos", "vfilter="));
		$(this).attr('href', $(this).attr('href').replace("vfilter=", "vfilter=" + filtro));
	});

	$("#exibir").show();
	$("#exibidos").text("0");
	$(".eventos").empty();
	$("#exibir").text("Exibir mais");
	loadEventos(0, filtro);
}
