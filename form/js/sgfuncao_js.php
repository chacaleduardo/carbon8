<script>
	function excluir(tab, inid) {
		if (confirm("Deseja retirar este?")) {
			CB.post({
				objetos: "_x_d_" + tab + "_id" + tab + "=" + inid
			});
		}

	}

	function novo(inobj) {
		CB.post({
			objetos: "_x_i_" + inobj + "_idsgfuncao=" + $("[name=_1_u_sgfuncao_idsgfuncao]").val()
		});

	}
	//# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>