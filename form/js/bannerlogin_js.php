<script>
    const idBanner = '<?= $_1_u_bannerlogin_idbannerlogin ?>',
        idPessoaLogada = '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>';

    if (idBanner) {
        $("#input-banner").dropzone({
            url: "form/_arquivo.php",
            idObjeto: idBanner,
            tipoObjeto: 'bannerlogin',
            tipoArquivo: 'BANNER',
            idPessoaLogada,
            acceptedFiles: ".jpg,.png,.jpeg",
            accept: function(file, done) {
                const _this = this;
                const reader = new FileReader();

                reader.onload = function(event) {
                    const image = new Image();
                    image.src = event.target.result;

                    image.onload = function() {
                        if (image.width === 750 && image.height === 750) {
                            done(); // Se estiver ok, permite o upload
                        } else {
                            alertAtencao("As dimensões da imagem devem ser 750x750 pixels."); // Caso contrário, exibe erro
                            done();
                            _this.removeFile(file); // Remove o arquivo da fila
                        }
                    };
                };

                reader.readAsDataURL(file);
            },
            sending: function(file, xhr, formData) {
                formData.append("idobjeto", this.options.idObjeto);
                formData.append("tipoobjeto", this.options.tipoObjeto);
                formData.append("tipoarquivo", this.options.tipoArquivo);
            },
            success: function(file, response) {
                this.options.loopArquivos(response);
            },
            init: function() {
                var thisDropzone = this;
            },
            loopArquivos: function(data) {
                jResp = jsonStr2Object(data);
                if (jResp.length > 0) {
                    const arquivo = jResp[jResp.length - 1];
                    const imgBlock = `<div class="w-100 d-flex my-3 flex-between align-items-center img-item">
                                        <div class="img rounded pointer" data-titulo="${arquivo.nomeoriginal}" data-img="${arquivo.caminho}" style="background-image: url(${arquivo.caminho});"></div>
                                        <h4 class="title" title="${arquivo.nomeoriginal}">${arquivo.nomeoriginal}</h4>
                                        <i class="fa fa-trash fa-2x pointer" onclick="removerBanner(${arquivo.idarquivo})"></i>
                                    </div>
                                    <hr>`;

                    $('.nenhuma-img').remove();
                    $('.img-block').prepend(imgBlock);
                }
            }
        });

        $("#input-banner-mobile").dropzone({
            url: "form/_arquivo.php",
            idObjeto: idBanner,
            tipoObjeto: 'bannerlogin',
            tipoArquivo: 'BANNERMOBILE',
            idPessoaLogada,
            acceptedFiles: ".jpg,.png,.jpeg",
            accept: function(file, done) {
                const _this = this;
                const reader = new FileReader();

                reader.onload = function(event) {
                    const image = new Image();
                    image.src = event.target.result;

                    image.onload = function() {
                        if (image.width === 390 && image.height === 224) {
                            done(); // Se estiver ok, permite o upload
                        } else {
                            alertAtencao("As dimensões da imagem devem ser 390x224 pixels."); // Caso contrário, exibe erro
                            done();
                            _this.removeFile(file); // Remove o arquivo da fila
                        }
                    };
                };

                reader.readAsDataURL(file);
            },
            sending: function(file, xhr, formData) {
                formData.append("idobjeto", this.options.idObjeto);
                formData.append("tipoobjeto", this.options.tipoObjeto);
                formData.append("tipoarquivo", this.options.tipoArquivo);
            },
            success: function(file, response) {
                this.options.loopArquivos(response);
            },
            init: function() {
                var thisDropzone = this;
            },
            loopArquivos: function(data) {
                jResp = jsonStr2Object(data);
                if (jResp.length > 0) {
                    const arquivo = jResp[jResp.length - 1];
                    const imgBlock = `<div class="w-100 d-flex my-3 flex-between align-items-center img-item">
                                        <div class="img rounded pointer" data-titulo="${arquivo.nomeoriginal}" data-img="${arquivo.caminho}" style="background-image: url(${arquivo.caminho});"></div>
                                        <h4 class="title" title="${arquivo.nomeoriginal}">${arquivo.nomeoriginal}</h4>
                                        <i class="fa fa-trash fa-2x pointer" onclick="removerBanner(${arquivo.idarquivo})"></i>
                                    </div>
                                    <hr>`;

                    $('.nenhuma-img').remove();
                    $('.img-block-mobile').prepend(imgBlock);
                }
            }
        });

        $('.img-item').on('click', '.img', (e) => {
            const img = $(e.currentTarget).data('img'),
                titulo = $(e.currentTarget).data('titulo'),
                corpo = `<div class="w-100"><img src="${img}" alt="" class="mw-100"></div>`;

            CB.modal({
                titulo,
                corpo
            })
        });

        function removerBanner(idArquivo) {
            if (!idArquivo) return alertAtencao('Id da imagen não informado.');
            if (!confirm('Deseja remover esta imagem?')) return false;

            CB.post({
                objetos: {
                    _1_d_arquivo_idarquivo: idArquivo
                }
            });
        }
    }

    CB.prePost = function() {
        const dataInicio = $('#data-inicio').val();
        const [diaInicio, mesInicio, anoInicio] = dataInicio.split('/');
        
        const dataFim = $('#data-fim').val();

        if(dataFim) {
            const [diaFim, mesFim, anoFim] = dataFim.split('/');
        
            const dataInicioFormatada = new Date(`${anoInicio}-${mesInicio}-${diaInicio}`);
            const dataFimFormatada = new Date(`${anoFim}-${mesFim}-${diaFim}`);

            if (dataInicioFormatada > dataFimFormatada) {
                alertAtencao('Data de fim menor que a data início.');
                return false;
            }
        }
    }
</script>