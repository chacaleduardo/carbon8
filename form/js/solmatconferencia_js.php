<script src="./inc/js/qr-scanner/qr-scanner.legacy.min.js" type="text/javascript"></script>
<script src="./inc/js/qr-scanner/qr-scanner-controller.js" type="text/javascript"></script>
<script>
    $('.abrir-camera').on('click', async function() {
		var btnCamera = $(this);

		let idLoteFracao = btnCamera.data('idlotefracao');
		let idUnidade = btnCamera.data('idunidade');
		var idLoteCons = btnCamera.data('idlotecons'),
			escaneando = false;

		await CB.modal({
			titulo: "<strong>Escanear lote</strong>",
			corpo: `<div class="col-sm-12 d-flex center justify-content-center">
						<div class="col-md-5 center">
							<h1>Selecione a câmera ou dispositivo de leitura</h1>
							<select id="trocadiv" class="form-control">
								<option value="camera">Câmera</option>
								<option value="bluetooth">Dispositivo Bluetooth</option>
							</select>
							<br>
							<div id="scanner"></div>
							<div id="leitorbt">
								<div id="qr-scanner">
									<div class="qr-scanner-layout" id="qr-scanner-video-content">
										<div id="qr-scanner-no-video" class="">
											<img src="inc/img/leitor-anelar.png" alt="bluetooth reader" width="230">
											<h2 style="color: white" id="txt-apt">Aponte o dispositivo para leitura</h2>
											<div id="qr-scanner-cameras" class="col-md-8 justify-content-center">
												<input type="text" id="codigo" autocomplete="off" placeholder="Escaneando...">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>`,
			classe: 'sessenta',
		});
		$(`#leitorbt`).hide();
		$('#trocadiv').on('change', function() {
			if($(this).val() == 'camera')
			{
				$('#scanner').show();
				$('#leitorbt').hide();
			}
			else
			{
				$(this).focusout()
				$('#scanner').hide();
				$('#leitorbt').show();
				$(`#codigo`).focus();
			}
		});

		$(`#codigo`).on("focus",function(e){
			$(this).addClass('blink');
			$(`#txt-apt`).text('Aponte o dispositivo para leitura');
		}).on("focusout",function(e){
			$(this).removeClass('blink');
			$(`#txt-apt`).text('Selecione o campo abaixo para iniciar a leitura');
		}).on('keyup', async function(e) {
			if(e.keyCode == 13)
			{
				let idLote = $(this).val();
				$(this).val('');
				idLote.split("&").forEach((i,e)=>{
					if(i.indexOf('idlote') > -1)
					{
						idLote = i.split("=")[1];
					}
				})

				if(idLote != btnCamera.data('idlote')) return alertAtencao('Lote incorreto!');

				// Buscar buscar fração consumida
				/**
				 * @param status
				 * @param idLoteCons
				 * @param idUnidade
				 */
				await $.ajax({
					type: "GET",
					url : "ajax/solmat.php",
					data: {
						action: 'atualizarStatusLoteCons',
						params: [idLoteCons, 'ABERTO',idUnidade]
					},
					success: function(data){
						if(data.error)
						{
							escaneando = false;
							return alertAtencao(data.error);
						}

						alertAzul("Lote conferido","",1000);

						btnCamera
							.attr('disabled', true)
							.removeClass('btn-primary')
							.addClass('btn-success');

						btnCamera
							.parent()
							.addClass('border border-success');

						CB.oModal.modal('hide');
						QrScannerController.scanner.destroy();
						escaneando = false;
					}
				});

				// Atualizar frações consumidas para status ATIVO

				console.log(result)
			}
		});
		QrScannerController.init()
		QrScannerController.init("#scanner").onScann = async ( result ) => {
			if(escaneando) return;
			escaneando = true;

			try
			{
				let idLote = getUrlParameter('idlote', result.data);

				if(idLote != $(this).data('idlote')) return alertAtencao('Lote incorreto!');
				// Buscar buscar fração consumida
				/**
				 * @param status
				 * @param idLoteCons
				 * @param idUnidade
				 */
				await $.ajax({
					type: "GET",
					url : "ajax/solmat.php",
					data: {
						action: 'atualizarStatusLoteCons',
						params: [idLoteCons, 'ABERTO',idUnidade]
					},
					success: function(data){
						if(data.error)
						{
							escaneando = false;
							return alertAtencao(data.error);
						}

						alertAzul("Lote conferido","",1000);

						btnCamera
							.attr('disabled', true)
							.removeClass('btn-primary')
							.addClass('btn-success');

						btnCamera
							.parent()
							.addClass('border border-success');

						CB.oModal.modal('hide');
						QrScannerController.scanner.destroy();
						escaneando = false;
					}
				});

				// Atualizar frações consumidas para status ATIVO

				console.log(result)
			}catch(e){
				console.log(e.toString());
			}
		}
	});
</script>