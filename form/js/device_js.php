<script type="text/Javascript">
	var permissao = '<?= getModsUsr("MODULOS")[$_GET["_modulo"]]["permissao"] ?>';
	var idDevice = <?= $_1_u_device_iddevice ?? "null" ?>;
	var versaoFirm = '<?= DeviceController::buscarDeviceFirmPorModelo($_1_u_device_modelo)[0]['versao']; ?>';
	var versaoDevice = '<?=$_1_u_device_versao?>';
	
	var versaoAtualizada = (versaoFirm == versaoDevice) ? 1 : 0;

	var ip = '<?= DeviceController::buscarDevicePorMacAddress($_1_u_device_mac_address)[0]['ip'] ?>';

	if(permissao != 'w')
	{
		$("#cbModuloForm").find('input').prop("disabled", true);
		$("#cbModuloForm").find("select").prop("disabled", true);
		$('#divconfiguracoes').hide();
		$('#divciclosesensores').hide();
		$('#divoffsets').hide();
		$('.divacao').hide();
	}


    function abrirmodal(iddevice,nomeciclo,grupo){
        var strCabecalho = "</strong><label>Ciclo " + nomeciclo + "</label></strong>";
        //Altera o cabeçalho da janela modal
        $("#cbModalTitulo").html(strCabecalho);

        var data = $("#atividadeinfo"+iddevice+grupo)[0].innerHTML;
        $('#cbModal').addClass('oitenta');
        $('#cbModal').addClass('fade');
        $('#cbModal').attr("modal", "modalatividade"+iddevice+grupo);
        $('#cbModal').addClass('hideshowtable');
        $("#cbModalCorpo").html(data);
        $('#cbModal').modal('show');
    }

	function printativ(titulo,iddevice) {
		let titleAnt = document.title;
		document.title = titulo;
		window.print();
		document.title = titleAnt;
	}

	function excluir(tab,inid){
		if(confirm("Deseja retirar este?")){
			CB.post({
				objetos: `_x_d_${tab}_id${tab}=${inid}`
			});
		}
	}

	function novo(inobj){
		CB.post({
			objetos: `_x_i_${inobj}_iddevice=${$("[name=_1_u_device_iddevice]").val()}`
		});
		
	}


	function inserirCiclo(vthis){
		CB.post({
			objetos: {
				"_x_i_deviceobj_iddevice": $("[name=_1_u_device_iddevice]").val(),
				"_x_i_deviceobj_objeto": $(vthis).val(),
				"_x_i_deviceobj_tipoobjeto": 'deviceciclo'
			},
			refresh:"refresh"
			
		});
	}

	function inserirSensor(vthis){
		CB.post({
			objetos: {
				"_x_u_devicesensor_iddevice": $("[name=_1_u_device_iddevice]").val(),
				"_x_u_devicesensor_iddevicesensor": $(vthis).val()
			},
			refresh:"refresh"
			
		});
	}

	/*function removerCiclo(iniddeviceobj){
		CB.post({
			objetos: "_x_d_deviceobj_iddeviceobj="+iniddeviceobj
		});
	}*/

	if(idDevice)
	{
		if(versaoAtualizada == 0)
		{
			$("#certanexo").show();
		}
		if(versaoDevice)
		{
			$("#certanexo").show();
		}
		if(versaoAtualizada == 1 )
		{
			$("#certanexo").hide();
		}
	}

	$('#certanexo').click(function(){
		var atualizar = "atualizar";
		$.ajax({
			url: 'ajax/enviarequisicaom5.php',
			type: 'POST',
			data: {ip:ip, status:atualizar},
			beforesend: function(){
				$("#visual").css({'display':'inline'});
				$("#visual").html("Carregando...");
			},
			success: function(data){
				if(data.error)
				{
					return alertAtencao(data.error);
				}

				alertAzul("Sincronizado com Sucesso","",1000);
				setTimeout(function(){// wait for 5 secs(2)
					location.reload(); // then reload the page.(3)
				}, 2000);
				
			},
			error: function(data){
				$("#visual").css({'display':'inline'});
				$("#visual").html("Erro ao carregar");
			}
		});
	});

	$(document).on('click', '.acaom5', function () { 
		var acaom5 = $(this).data("acaom5");
		var entrar = true;
		if(acaom5 == 'trocaciclo'){
			var nciclo = prompt("Digite o número do ciclo desejado\n\n<?=$ciclo;?>", '');

			if (nciclo != null) {
			//
			}else{
				entrar = false;
			}
		}else if (acaom5 == 'log'){

		}
		if (entrar){
			$.ajax({
				url: 'ajax/enviarequisicaom5.php',
				type: 'POST',
				data: {
					ip: ip, 
					status: acaom5,
					iddeviceciclo: nciclo
				},
				beforesend: function(){
					$("#visual").css({'display':'inline'});
					$("#visual").html("Carregando...");
				},
				success: function(data){
					if(data.error)
					{
						return alertAtencao(data.error);
					}

					alertAzul(`M5 ${acaom5}`,"",1000);
				},
				error: function(data){
					$("#visual").css({'display':'inline'});
					$("#visual").html("Erro ao carregar");
				}
			}); 

			function downloadFile(urlToSend) {
				var req = new XMLHttpRequest();
				req.open("GET", urlToSend, true);
				req.responseType = "blob";
				req.onload = function (event) {
					var blob = req.response;
					var fileName = req.getResponseHeader("fileName") //if you have the fileName header available
					var link=document.createElement('a');
					link.href=window.URL.createObjectURL(blob);
					link.download=fileName;
					link.click();
				};

				req.send();
			}
		}
	});

	function flglog(vthis){
		var atval=$(vthis).attr('atval');
		var iddevice=$(vthis).attr('iddevice');
		if(vthis.checked){
			mensagem = " Log Ativado!";
		} else {
			mensagem = " Log Desativado!";
		}
		CB.post({
			objetos: {
				"_x_u_device_iddevice": iddevice,
				"_x_u_device_log": atval
			},
			parcial:true,
			refresh: false,
			msgSalvo: mensagem,
			posPost: function(){
				if(atval=='Y'){
					$(vthis).attr('atval','N');
				}else{
					$(vthis).attr('atval','Y');
				}
			}
		});
		var acaom5 = 'statuslog';
		$.ajax({
			url: 'ajax/enviarequisicaom5.php',
			type: 'POST',
			data: {
				ip: ip,
				status: acaom5,
				statuslog: atval},
			beforesend: function(){
				$("#visual").css({'display':'inline'});
				$("#visual").html("Carregando...")
			},
			success: function(data){
				if(data.error)
				{
					return alertAtencao(data.error);
				}

				alertAzul(`M5 ${acaom5}`,"",1000);
			},
			error: function(data){
				$("#visual").css({'display':'inline'});
				$("#visual").html("Erro ao carregar");
			}
		}); 
	}

	function removerSensor(iddevicesensor){
		if(confirm("Deseja realmente remover esse sensor?")){
			CB.post({
				objetos: {
					"_ajax_u_devicesensor_iddevicesensor": iddevicesensor,
					"_ajax_u_devicesensor_iddevice": null
				},
				parcial:true
			});
		}
	}

	function removerCiclo(iddeviceobj){
		if(confirm("Deseja realmente remover esse ciclo?")){
			CB.post({
				objetos:{
					"_ajax_d_deviceobj_iddeviceobj": iddeviceobj
				},
				parcial:true
			});
		}
	}

	$('.select-picker').selectpicker('render');
	//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>