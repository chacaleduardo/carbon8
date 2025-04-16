<script>
	//------- Injeção PHP no Jquery -------
	var idsolcomitem = <?=$_idsolcomitem?>;
	//------- Injeção PHP no Jquery -------
	$("#fotoproduto").dropzone({
		url: "form/_arquivo.php",
		idObjeto: idsolcomitem,
		tipoObjeto: 'solcomitem',
		tipoArquivo: 'FOTOPRODUTO',
		caminho: 'upload/fotoproduto/',
		sending: function(file, xhr, formData) {
			formData.append("idobjeto", this.options.idObjeto);
			formData.append("tipoobjeto", this.options.tipoObjeto);
			formData.append("tipoarquivo", this.options.tipoArquivo);
			formData.append("caminho", this.options.caminho);
		},
		success: function(file, response) {
			this.options.loopArquivos(response);
		},
		init: function() {
			var thisDropzone = this;
			$.ajax({
				url: this.options.url + "?caminho="+this.options.caminho+"&tipoobjeto=" + this.options.tipoObjeto + "&idobjeto=" + this.options.idObjeto + "&tipoarquivo=" + this.options.tipoArquivo
			}).done(function(data, textStatus, jqXHR) {
				thisDropzone.options.loopArquivos(data);
			})
		},
		loopArquivos: function(data) {
			jResp = jsonStr2Object(data);
			if (jResp.length > 0) {
				nomeArquivo = jResp[jResp.length - 1].nome;
				if (nomeArquivo) {
					$("#fotoproduto").attr("src", "upload/fotoproduto/" + nomeArquivo);
				}
			}
		}
	});

	function removerImagem(idarquivo)
	{
		CB.post({
			objetos:{
				"_s_d_arquivo_idarquivo": idarquivo,
			},
			parcial: false            
		});
	}

	function InserirImagemPadrao()
	{
		let objeto;
		i = 0;
		//Executa Loop entre todas as Radio buttons com o name de valor
		$('input:radio[name=imagempadrao]').each(function() {
			//Verifica qual está selecionado
			if ($(this).is(':checked'))
			{
				objeto += `&_s${i}_u_arquivo_idarquivo=${$(this).attr('idarquivo')}&_s${i}_u_arquivo_imagempadrao=Y`;
			} else{
				objeto += `&_s${i}_u_arquivo_idarquivo=${$(this).attr('idarquivo')}&_s${i}_u_arquivo_imagempadrao=N`;
			}
			i++;
		});

		CB.post({
			objetos: objeto,
			parcial: false            
		});
	}
</script>