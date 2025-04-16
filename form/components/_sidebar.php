<aside id="sidebar" class="px-3 py-4 bg-light transition"></aside>
<? if($_GET['_modulo'] && $_GET['_modulo'] == 'menulateralapp') { ?>
    <style type="text/Css">
        #sidebar{height: 100vh !important;top: 0 !important}
        #btn-menu{display: none !important;}
    </style>
<?}?>
<script type="text/Javascript">
    const JQmodalSnippetAcao = $('#modal-snippet');
    let JQmenuLateral = $('#sidebar');

    JQmenuLateral.on('click', '.open-menu', abrirMenu);
    JQmenuLateral.on('click', '.close-menu', fecharMenu);
    $(`.overlay`).on('click', fecharMenu);

    function abrirMenu(e)
    {
        let JQbtnMenu = $('#btn-menu');

        if(e){
            e.preventDefault();
            let JQelement = $(e.currentTarget);
        } 


        JQmenuLateral.addClass('opened');

        JQbtnMenu.addClass('close-menu').removeClass('open-menu');
        JQbtnMenu.find('i').addClass('fa-chevron-left').removeClass('fa-chevron-right');

        // JQmenuLateral.find('ul:has( .ativo)').each((key, element) => {
        //     $(element).collapse('show');
        // });
    }    

    function fecharMenu(e)
    {
        let JQbtnMenu = $('#btn-menu');

        let collapsingInterval;

        e.preventDefault();

        collapsingInterval = setInterval(_ => {
            if(!JQmenuLateral.find('ul.collapsing').get().length)
            {
                let JQelement = $(e.currentTarget);

                JQmenuLateral.removeClass('opened');

                JQbtnMenu.addClass('open-menu').removeClass('close-menu');
                JQbtnMenu.find('i').addClass('fa-chevron-right').removeClass('fa-chevron-left');

                JQmenuLateral.find('ul[aria-expanded="true"]').each((key, element) => {
                    $(element).collapse('hide');
                });

                clearInterval(collapsingInterval);
            }
        }, 200);
    };

    //Removido para melhor usabilidade do sistema - PHOL:10/11/23
    // $('#sidebar').on('mouseenter', e => {
    //     clearInterval(delayHover);

    //     delayHover = setTimeout(_ => abrirMenu(e), 100);
    // });
    // $('#sidebar').on('mouseleave', e => {
    //     clearInterval(delayHover);

    //     delayHover = setTimeout(_ => fecharMenu(e), 100);
    // });

    $('#cbMenuSuperior').on('click', '[cbmodulo="snippetacao"]', function()
    {
        ocultarModalEmpresa();
        mostrarBlocoModalSnippetAcao();

        mostrarModalSnippetAcao();
    });

    JQmodalSnippetAcao.on('click', '.close-modal', ocultarModalSnippetAcao);
</script>
<? if($_GET['_modulo'] && $_GET['_modulo'] == 'menulateralapp') { ?>
    <script type="text/Javascript" defer>
        console.log('sidebar');
        (_ => {
            console.log('sidebarfuncao');
            montaMenuCarbon(true, false, false);

            if(getUrlParameter('cb-canal') == 'app'){
                $('#btn-menu').click();
            }
        })();
    </script>
<?}?>