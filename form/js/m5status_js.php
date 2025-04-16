<script type="text/Javascript">

    $(document).on('click', '.acaom52', function () { 
        var acaom5 = $(this).data("acaom5");
        var ip = $(this).data("ip");
        $.ajax({
            url: 'ajax/enviarequisicaom5.php',
            type: 'POST',
            data: {ip:ip, status:acaom5},
            beforesend: function(){
                $("#visual").css({'display':'inline'});
                $("#visual").html("Carregando...");
            },
            success: function(data){
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
        var ip = $(this).data("ip");
        var entrar = true;
        $.ajax({
            url: 'ajax/enviarequisicaom5.php',
            type: 'POST',
            data: {ip:ip, status:acaom5},
            beforesend: function(){
                $("#visual").css({'display':'inline'});
                $("#visual").html("Carregando...");
            },
            success: function(data){
                alertAzul("M5 "+acaom5,"",1000);
            },
            error: function(data){
                $("#visual").css({'display':'inline'});
                $("#visual").html("Erro ao carregar");
            }
        }); 
    });

    function altespecial(inval,idtag){
        CB.post({
            objetos: "_x_u_tag_idtag="+idtag+"&_x_u_tag_emuso="+inval
            ,parcial:true  
        });
    }

    function filtra(url, inicio = false){

        $("meta[http-equiv='refresh']").attr('content','60;url='+url);

        $('.CONTROLE').hide();
        $('.MONITORAMENTO').hide();
        $('.DIFERENCIAL').hide();

        if(inicio){
            let subtipo = getUrlParameter('subtipo');
            let tipo = getUrlParameter('itipo');

            if(subtipo != ''){
                Cookies.set('subtipo', subtipo);
            }

            if(tipo != ''){
                Cookies.set('tipo', tipo);
            }
        }

        if (typeof Cookies.get('subtipo') === 'undefined'){ 
            Cookies.set('subtipo', 'CONTROLE');
        }

        if (typeof Cookies.get('tipo') === 'undefined'){ 
            Cookies.set('tipo', 't');
        }

    $("."+Cookies.get('subtipo')+"").filter("."+Cookies.get('tipo')+"").show();
    
        $("#tipom5").text(" "+Cookies.get('subtipo')+"");
    }

    $(document).ready(function($) {


        $('#mySelector').change(function() {
            $('table').show();
            let selection = $(this).val();
            Cookies.set('subtipo', selection);
            let url = removerParametroGet('subtipo');
            url = removerParametroGet('itipo', url);
            window.history.pushState(null, window.document.title, url);
            filtra(url);
        });

        $('#mySelector2').change(function() {
            $('table').show();
            let selection = $(this).val();
            Cookies.set('tipo', selection);
            let url = removerParametroGet('subtipo');
            url = removerParametroGet('itipo', url);
            window.history.pushState(null, window.document.title, url);
            filtra(url);
        });


    });

    (function()
    {
        let JQtabelaPrincipal = $('#myTable tbody'),
            JQlinhasEmAlerta = $('.alerta');

        JQtabelaPrincipal.find('.alerta').remove();

        JQtabelaPrincipal.prepend(JQlinhasEmAlerta);
    })();

    filtra(window.location.href, true);
</script>