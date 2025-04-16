<script>
    //------- Injeção PHP no Jquery -------
    var idpessoa = '<?=$_SESSION["SESSAO"]["IDPESSOA"] ?>';
    var qtd = '<?=$_1_u_nf_qtd?>';
    var total = '<?=str_replace(",", ".", str_replace(".", "", $_1_u_nf_total))?>';
    <? if($_1_u_nf_idnf){ ?>
        var qtdArquivo = '<?=CompraAppController::buscarArquivoPorTipoObjetoEIdObjeto('nf', $_1_u_nf_idnf)?>';
    <? } else { ?>
        var qtdArquivo = 0 ;
    <? } ?>
    var status = '<?=$_1_u_nf_status?>';
    
    //------- Injeção PHP no Jquery -------

    //------- Funções JS -------
    var date = new Date();
    var dateHoje = date.getFullYear()+"-"+((date.getMonth()+1).toString().padStart(2, '0'))+"-"+date.getDate();
    var dateOntem = date.getFullYear()+"-"+((date.getMonth()+1).toString().padStart(2, '0'))+"-"+(date.getDate()-1);
    var dtemissaoapp = $(".dtemissaoapp").val().replace(/(\d*)\/(\d*)\/(\d*).*/, '$3-$2-$1');
    var dtemissaoappBrasil = $(".dtemissaoapp").val().substr(0, 10);

    $("#anexo").dropzone({
        idObjeto: $("[name=_1_u_nf_idnf]").val(),
        tipoObjeto: 'nfentrada',
        idPessoaLogada: idpessoa
    });

    if(status != 'CONCLUIDO' || status != 'CANCELADO' || status != 'REPROVADO')
    {
        $('.btn-calendario').daterangepicker({
            "autoUpdateInput": false,
            "singleDatePicker": true,
            "showDropdowns": true,
            "linkedCalendars": false,
            "opens": "left",
            "locale": CB.jDateRangeLocale
        }).on("click", function(e, picker) {
            e.stopPropagation();
        }).on("apply.daterangepicker", function(e, picker) {
            picker.element.val(picker.startDate.format(picker.locale.format));
            $('#date-input').val(picker.startDate.format('YYYY-MM-DD'));
            $('.btn-outros').html(picker.startDate.format("DD/MM/YYYY") || "");
            $(".dtemissaoapp").val(picker.startDate.format('YYYY-MM-DD'));
            $(this).closest(".eventoRow").attr("prazo", picker.startDate.format("YYYY-MM-DD"));
            $('.btn-hoje').addClass('bg-secondary').removeClass('bg-primary');
            $('.btn-ontem').addClass('bg-secondary').removeClass('bg-primary');
            $('.btn-calendario').addClass('bg-primary').removeClass('bg-secondary');
        });

        $('.date-options button').on('click', function(e){
            $('#date-input').val($(this).data('value'));
            if(e.target.className == 'btn-outros')
            {
                $('.btn-outros').html(dtemissaoappBrasil);
            } else {
                $('.btn-outros').html('');
            }
            $('.date-options .bg-primary').addClass('bg-secondary').removeClass('bg-primary');
            $(this).removeClass('bg-secondary').addClass('bg-primary');
            $(".dtemissaoapp").val($(this).data('value'));
        });
    }

    if(moment(dtemissaoapp).isAfter(dateHoje) || (moment(dtemissaoapp).isBefore(dateHoje) && !moment(dtemissaoapp).isSame(dateOntem)))
    {
        $('.btn-hoje').addClass('bg-secondary').removeClass('bg-primary');
        $('.btn-ontem').addClass('bg-secondary').removeClass('bg-primary');
        $('.btn-outros').html(dtemissaoappBrasil);
        $('.btn-calendario').addClass('bg-primary').removeClass('bg-secondary');
    } else if(moment(dtemissaoapp).isSame(dateHoje)) {
        $('.btn-hoje').addClass('bg-primary').removeClass('bg-secondary');
        $('.btn-ontem').addClass('bg-secondary').removeClass('bg-primary');
        $('.btn-calendario').addClass('bg-secondary').removeClass('bg-primary');
    } else if(moment(dtemissaoapp).isSame(dateOntem)) {
        $('.btn-hoje').addClass('bg-secondary').removeClass('bg-primary');
        $('.btn-ontem').addClass('bg-primary').removeClass('bg-secondary');
        $('.btn-calendario').addClass('bg-secondary').removeClass('bg-primary');
    }

    if ($("[name=_1_u_nf_idnf]").val()) 
    {
        $(".cbupload").dropzone({
            idObjeto: $("[name=_1_u_nf_idnf]").val(),
            tipoObjeto: 'nf',
            idPessoaLogada: '<?=$_SESSION["SESSAO"]["IDPESSOA"] ?>'
        });
    }

    ST.on('posCarregaFluxo', function(){               
        $('#cbSteps').hide();
    }); 

    //------- Funções JS -------

    //------- Funções Módulo -------
    function alterarFluxo(status)
    {   
        if(thisDropzone.files.length > 0)
        {
            qtdArquivo = thisDropzone.files.length;
        }
        //Valida se existe anexo.
        if(qtd == 0 && status != 'CANCELADO') {
            alertAtencao("É necessário Preencher a Quantidade.");
        } else if(qtdArquivo == 0 && status != 'CANCELADO') {  
            alertAtencao("É necessário Anexar o Cupom.");
        } else {
            //Atualiza o Fluxo
            CB.post({
                objetos: `_fl_u_nf_idnf=${$("[name=_1_u_nf_idnf]").val()}&_atualizar_fluxo=Y&valorapp=${$("[name=valorapp]").val()}&_status_fluxo=${status}&dtemissaoapp=${$("[name=dtemissaoapp]").val()}&prodservdescr=${$("[name=prodservdescr]").val()}`,
                parcial: true
            });
        }
    }

    function atualizarValor(vthis)
    {
        valorUnitario = total / $(vthis).val();
        $('#valor_unitario').html(valorUnitario);
    }

    if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
        //mobile
        var facingMode = { exact: "environment" }
    }else{
        //desktop
        var facingMode = 'user';
    }

    if(JSON.stringify(Object.keys(navigator)).length == 0 || navigator.mediaDevices == undefined){
        $('.div_camera').remove();
    }

    function loadCamera(vthis){
        $(vthis).addClass("hidden");
        $("#img_taken").attr('src','');
        $("#deleteShot").addClass('hidden');
        $("#sendShot").addClass('hidden');
        //Captura elemento de vídeo
        var video = document.querySelector("#webCamera");
        //As opções abaixo são necessárias para o funcionamento correto no iOS
        $(video).removeClass("hidden");
        $("#takeSnapShot").removeClass("hidden");
        video.setAttribute('autoplay', '');
        video.setAttribute('muted', '');
        video.setAttribute('playsinline', '');
        
        //Verifica se o navegador pode capturar mídia
        if (JSON.stringify(Object.keys(navigator)).length > 0 || navigator.mediaDevices != undefined) {
            navigator.mediaDevices.getUserMedia({audio: false, video: {facingMode: facingMode}})
            .then( function(stream) {
                //Definir o elemento vídeo a carregar o capturado pela webcam
                video.srcObject = stream;
            })
            .catch(function(error) {
                alert("Oooopps... Falhou :'( "+error);
            });
        } 
    }

    function takeSnapShot(){
        //Captura elemento de vídeo
        var video = document.querySelector("#webCamera");
        
        //Criando um canvas que vai guardar a imagem temporariamente
        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        var ctx = canvas.getContext('2d');
        
        //Desenhando e convertendo as dimensões
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        //Criando o JPG
        var dataURI = canvas.toDataURL('image/jpeg'); //O resultado é um BASE64 de uma imagem.
        // document.querySelector("#base_img").value = dataURI;
        $("#img_taken").attr('src',dataURI);
        $("#takeSnapShot").addClass('hidden');
        $("#webCamera").addClass('hidden');
        $("#deleteShot").removeClass('hidden');
        $("#sendShot").removeClass('hidden');
        
        //sendSnapShot(dataURI); //Gerar Imagem e Salvar Caminho no Banco
    }

    async function enviaFoto(){
        let dataUrl = $("#img_taken").attr("src");
        var fd = new FormData();
        fd.append("file",dataUrl);
        fd.append("idobjeto",$("[name=_1_u_nf_idnf]").val());
        fd.append("tipoobjeto",'nf');
        fd.append("idpessoalogada",idpessoa);
        $.ajax({
                url: "ajax/upload_arq.php",
                method:"POST",
                contentType: false,
                processData: false,
                data: fd
            }).done(function(data, textStatus, jqXHR) {
                alertSalvo("Salvo");
                document.location.reload();
            })
    }
    
    //------- Funções Módulo -------
    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
</script>