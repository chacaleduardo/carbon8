
<script>

const urlResultados = "<?=$_SERVER['SERVER_NAME']?>";
const nomeCliente = "<?=$nomeCliente?>";


	if(urlResultados == 'resultados.inata.com.br'){
		var logoEmpresa = 'Boas vindas ao Inata Biológicos!';
	} else if(urlResultados == '<?= RESULTADOSURL ?>'){
		var logoEmpresa = 'Boas vindas ao Laudo Laboratório!';
	}

	//Sem o timer o plugin popover não inicializa na hora correta
	setTimeout(function(){
		$("#mascote").webuiPopover({
			placement: "top-right",
			trigger: "sticky",
			animation: "pop",
			content: `Olá, <strong>${nomeCliente}</strong>!<br> 
					<br>${logoEmpresa}<br> 
					<br>Selecione ao lado a opção desejada
					<br>para consulta de resultados.`
		});
	},100);


	//Mostrar imagens relacionadas
	$("#bt1,#bt2,#bt3,#bt4,#bt5,#bt6,#bt7,#bt8").hover(
	function() {
		//console.log($( this ).attr("cbrelacionado"));
		$("#"+$( this ).attr("cbrelacionado")).fadeIn(50);
	}, function() {
		//console.log($( this ).attr("cbrelacionado"));
		$("#"+$( this ).attr("cbrelacionado")).fadeOut(50);
	}
	);
// adicionar rodapé em todos os JS de forms p/ ser possível debuggar em produção
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| 17/02/2021 PEDRO LIMA |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

</script>