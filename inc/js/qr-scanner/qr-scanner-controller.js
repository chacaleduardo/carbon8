class qrScannerController{
    constructor () {
        this.init = ( selector ) => {

            let $scanner = $(selector);

            if(!selector || $scanner.length < 1 || $scanner.length > 1){
                console.error("Nenhum elemento válido encontrado para iniciar o scanner");
                return null;
            }

            $scanner.append(this._layout());

            this.onScann = null;
            this.onStartCamera = null;
            this.onStopCamera = null;

            this.video = $(`${selector} #qr-scanner-video`);
            this.no_video = $(`${selector} #qr-scanner-no-video`);
            this.no_camera = $(`${selector} #qr-scanner-no-camera`);
            this.startBtn = $(`${selector} #qr-scanner-start-button`);
            this.camList = $(`${selector} #qr-scanner-cam-list`);
            this.flashToggle = $(`${selector} #qr-scanner-flash-toggle`);

            this.scanner = new QrScanner(this.video[0], this._onScann, {
                highlightScanRegion: true,
                highlightCodeOutline: true,
            });

            this.startBtn.on('click', this._toogleCamera);
            this.flashToggle.on('click', this._toogleFlash);
            this.camList.on('change', this._setCamera);

            this._verificaCamera();

            return this;
        }

        this._toogleCamera = ( event ) => {
            let $e = $(event.target);

            if($e.hasClass('qr-inativo')){
                this.startBtn.addClass('qr-ativo').removeClass('qr-inativo');
                this.no_video.addClass('hidden');
                this.video.addClass('qr-ativo').removeClass('qr-inativo');
                this._startCamera();
            }else{
                this.startBtn.addClass('qr-inativo').removeClass('qr-ativo');
                this.no_video.removeClass('hidden');
                this.video.addClass('qr-inativo').removeClass('qr-ativo');
                this._stopCamera();
            }
        }

        this._startCamera = async () => {
            await this.scanner.start();
            this._updateFlashAvailability();
            let cameras = await QrScanner.listCameras(true);

            for(let camera of cameras){
                this.camList.append(`
                    <option value="${camera.id}">${camera.label}</option>
                `);
            }

            this.camList.removeAttr('disabled');

            if(this.onStartCamera && typeof this.onStartCamera == "function")
                this.onStartCamera();
        }

        this._stopCamera = async () => {
            await this.scanner.stop();

            // ESCONDE OPÇÃO DE FLASH E RESETA OPÇÕES DE CÂMERAS
            this.flashToggle.hide();
            this.camList.html(this._camListOptions());
            this.camList.attr('disabled','');

            if(this.onStopCamera && typeof this.onStopCamera == "function")
                this.onStopCamera();
        }

        this._setCamera = async ( event ) => {
            await this.scanner.setCamera(event.target.value);
            this._updateFlashAvailability();
        }

        this._toogleFlash = async () => {
            await this.scanner.toggleFlash();
            if(this.scanner.isFlashOn()){
                this.flashToggle.addClass('qr-ativo').removeClass('qr-inativo');
                this.flashToggle.text('📸');
            }else{
                this.flashToggle.addClass('qr-inativo').removeClass('qr-ativo');
                this.flashToggle.text('📷');
            }
        }

        this._onScann = ( result ) => {
            // NESSE PONTO É POSSÍVEL ARMAZENAR LOGS DE LEITURAS

            if(result.data){
                if(this.onScann && typeof this.onScann == "function")
                    this.onScann(result);
            }
        }

        this._verificaCamera = async () => {
            let hasCamera = await QrScanner.hasCamera();
            if(!hasCamera){
                this.video.addClass('hidden');
                this.no_video.addClass('hidden');
                this.no_camera.removeClass('hidden');
            }
        }

        this._updateFlashAvailability = async () => {
            let hasFlash = await this.scanner.hasFlash();
            hasFlash ? this.flashToggle.show() : this.flashToggle.hide();
        };

        this._layout = () => {
            return `
                <div id="qr-scanner">
                    <div class="qr-scanner-layout" id="qr-scanner-video-content">
                        <video id="qr-scanner-video" class="qr-inativo"></video>
                        <div id="qr-scanner-no-camera" class="hidden">
                            <i class="fa icon-no-camera"></i>
                            <span>Não existem câmeras disponíveis</span>
                        </div>
                        <div id="qr-scanner-no-video">
                            <i class="fa icon-no-video"></i>
                            <span>A câmera está desligada</span>
                        </div>
                    </div>
                    <div id="qr-scanner-actions">
                        <div id="qr-scanner-cameras">
                            <span>Câmeras:</span>
                            <select id="qr-scanner-cam-list" disabled>
                                ${this._camListOptions()}
                            </select>
                            <button id="qr-scanner-flash-toggle" class="qr-inativo">📷</button>
                        </div>
                        <div id="qr-scanner-button">
                            <button id="qr-scanner-start-button" class="qr-inativo">ESCANEAR</button>
                        </div>
                    </div>
                </div>
            `;
        }

        this._camListOptions = () => {
            return `
                <option value="environment" selected>Padrão</option>
                <option value="user">Frontal</option>
            `;
        }
    }
}

var QrScannerController = new qrScannerController();