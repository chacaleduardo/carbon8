<??>
<script>
	function pesquisar(){
		var idregistro_1 = $("[name=idregistro_1]").val();
		var idregistro_2 = $("[name=idregistro_2]").val();
		var vencimento_1 = $("[name=vencimento_1]").val();
		var vencimento_2 = $("[name=vencimento_2]").val();
		var exercicio = $("[name=exercicio]").val();
		var str="vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&exercicio="+exercicio+"&idregistro_1="+idregistro_1+"&idregistro_2="+idregistro_2;
		CB.go(str);
	}

	function imprimir(){
		var idregistro_1 = $("[name=idregistro_1]").val();
		var idregistro_2 = $("[name=idregistro_2]").val();
		var vencimento_1 = $("[name=vencimento_1]").val();
		var vencimento_2 = $("[name=vencimento_2]").val();
		var exercicio = $("[name=exercicio]").val();
		var str="report/lanagro.php?_acao=u&vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&exercicio="+exercicio+"&idregistro_1="+idregistro_1+"&idregistro_2="+idregistro_2;
		janelamodal(str);
	}

	$(document).keypress(function(e) {
	if(e.which == 13) {
		pesquisar();
	}
	});



	function altstatus(inid,val){
		CB.post({
			objetos: "_x_u_plpositivo_idplpositivo="+inid+"&_x_u_plpositivo_status"+val
			,refresh:"refresh"
		});
	}

	function altcss(){
		window.location.href=window.location.href+'&cssp=screen';
	}
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>