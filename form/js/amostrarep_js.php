
<script>

	//Retira o width configurado na prodserv e trata as tabelas para caberem na pagina
	$(document).ready(function() {
		$('pagina table .grval table').each(function(a) {
			$(this).width('100%');
			$(this).css('max-width','19cm');
		})
	});



// adicionar rodapé em todos os JS de forms p/ ser possível debuggar em produção
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| 17/02/2021 PEDRO LIMA |||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

</script>