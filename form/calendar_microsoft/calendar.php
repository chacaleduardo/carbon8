<?php
require_once 'config_auth_microsoft.php';
require_once 'auth_microsoft.php';
require_once 'calendar_actions_js.php';
require_once 'utils.php';

require_once 'modals/month_year_selector.php';
require_once 'modals/create_and_edit_form.php';
require_once 'modals/view_event_organizer.php';
require_once 'modals/create_event_microsoft.php';

require_once 'microsoft_functions/list_users.php';
require_once 'microsoft_functions/services.php';
require_once 'microsoft_functions/create_event.php';
require_once 'microsoft_functions/update_event.php';
require_once 'microsoft_functions/schedule.php';


$_SESSION['state'] = session_id();


if (isset($_GET['code'])) {
    handleAuthorizationCode($_GET['code']);
}

if (isset($_GET['logout'])) {
    handleLogout($appid, $logout_url, $intermediate_redirect_uri);
}
?>
<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css' rel='stylesheet'>


<body>
    <div class="main-content">
        <div class="calendar-container">
            <nav class="navtop">
                <div class="container-type-calendar">
                    <!-- <button class="button-printer">
                        <svg width="14" height="12" viewBox="0 0 14 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.6666 3.33333C12.7733 3.33333 13.6666 4.22667 13.6666 5.33333V9.33333H11V12H2.99998V9.33333H0.333313V5.33333C0.333313 4.22667 1.22665 3.33333 2.33331 3.33333H2.99998V0H11V3.33333H11.6666ZM4.33331 1.33333V3.33333H9.66665V1.33333H4.33331ZM9.66665 10.6667V8H4.33331V10.6667H9.66665ZM11 8H12.3333V5.33333C12.3333 4.96667 12.0333 4.66667 11.6666 4.66667H2.33331C1.96665 4.66667 1.66665 4.96667 1.66665 5.33333V8H2.99998V6.66667H6.99998H11V8ZM11.6666 5.66667C11.6666 6.03333 11.3666 6.33333 11 6.33333C10.6333 6.33333 10.3333 6.03333 10.3333 5.66667C10.3333 5.3 10.6333 5 11 5C11.3666 5 11.6666 5.3 11.6666 5.66667Z" fill="#176292" />
                        </svg>
                    </button>
                    <select class="select-type-calendar">
                        <option value="1">Mês</option>
                        <option value="2">Semana</option>
                        <option value="3">Dia</option>
                    </select> -->
                    <section>
                        <div class="bsk-container">
                            <button class="bsk-btn bsk-btn-default">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 48 48">
                                    <path fill="#ff5722" d="M6 6H22V22H6z" transform="rotate(-180 14 14)"></path>
                                    <path fill="#4caf50" d="M26 6H42V22H26z" transform="rotate(-180 34 14)"></path>
                                    <path fill="#ffc107" d="M26 26H42V42H26z" transform="rotate(-180 34 34)"></path>
                                    <path fill="#03a9f4" d="M6 26H22V42H6z" transform="rotate(-180 14 34)"></path>
                                </svg>
                                <?php if (isset($_SESSION['access_token'])) : ?>
                                    <a href="javascript:handleLogout()">Deslogar da Conta</a>
                                <?php else : ?>
                                    <a href="javascript:handleLogin()">Login com Microsoft</a>
                                <?php endif; ?>
                            </button>
                        </div>
                    </section>
                </div>
                <div class="container-event-scheduling">
                    <button class="btn-secondary" id="scheduleMeetingBtn">
                        <svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.349 2.00033H10.6823V0.666992H9.34898V2.00033H4.01565V0.666992H2.68231V2.00033H2.01565C1.28231 2.00033 0.682312 2.60033 0.682312 3.33366V12.667C0.682312 13.407 1.28231 14.0003 2.01565 14.0003H11.349C12.089 14.0003 12.6823 13.407 12.6823 12.667V3.33366C12.6823 2.60033 12.089 2.00033 11.349 2.00033ZM11.349 12.667H2.01565V6.00033H11.349V12.667ZM11.349 4.66699H2.01565V3.33366H11.349M6.68231 6.66699C8.01565 6.66699 8.68231 8.28033 7.74231 9.22699C6.79565 10.1737 5.18231 9.50033 5.18231 8.16699C5.18231 7.33366 5.84898 6.66699 6.68231 6.66699ZM9.68231 11.9203V12.0003H3.68231V11.9203C3.68231 11.087 5.01565 10.4203 6.68231 10.4203C8.34898 10.4203 9.68231 11.087 9.68231 11.9203Z" fill="#337AB7" />
                        </svg>
                        <span class="btn-text">
                            Agendar reunião
                        </span>
                    </button>
                </div>
            </nav>
            <div class="calendar">
                <div class="header">
                    <button id="previous" onclick="previous()">&#10094;</button>
                    <div id="monthAndYear" class="month-year" onclick="openMonthYearSelector()"></div>
                    <button id="next" onclick="next()">&#10095;</button>
                </div>
                <table class="table-calendar" id="calendar" data-lang="en">
                    <thead id="thead-month"></thead>
                    <tbody id="calendar-body"></tbody>
                </table>
            </div>
        </div>
        <div class="sidebar">
            <div class="events-container">
                <div class="date-title-container">
                    <span class="date-title" id="eventDetailsHeader"></span>
                </div>
                <ul class="event-list" id="eventDetailsBody"></ul>
            </div>
            
            <div class="filters-container">
                <div class="container-btn-filters" id="listatagtipo">
                    <button class='btn-primary'>
                        <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.4507 5.33159L1.65492 9.12735C1.47186 9.31042 1.17506 9.31042 0.992013 9.12735L0.549298 8.68463C0.366544 8.50188 0.366192 8.20569 0.548516 8.0225L3.55674 5.00012L0.548516 1.97776C0.366192 1.79458 0.366544 1.49838 0.549298 1.31563L0.992013 0.872915C1.17508 0.689849 1.47188 0.689849 1.65492 0.872915L5.4507 4.66866C5.63375 4.85172 5.63375 5.14852 5.4507 5.33159Z" fill="#626262" />
                        </svg>
                    </button>
                    <button id="limpar-filtro" class="btn-clear" id="limpar-filtro">LIMPAR FILTROS</button>
                </div>
                <nav class="filter-nav">
                    <!-- EVENTOS -->
                    <div class="row d-flex flex-wrap nivel-1">
                        <div class="col-sm-12 px-0 w-100">
                            <div class="relative w-100 text-center">
                                <a class='pointer list-group-item tagtip text-uppercase' id='eventos-btevento' onclick="altpsq('eventos','btevento');"> Eventos</a>
                                <i class="arrow-icon fa fa-chevron-right"></i>
                            </div>
                            <? carregaEventoTipo("menu"); ?>
                        </div>
                    </div>
                    <!-- TAGS -->
                    <? if ($equipamentos || $salas || $veiculos || $prateleiras) { ?>
                        <div class="row d-flex flex-wrap nivel-1">
                            <div class="col-sm-12 px-0 w-100 text-center">
                                <a class='pointer list-group-item tagtip' id='tag-bttag' onclick="altpsq('tag','bttag');"> TAGs</a>
                                <i class="arrow-icon fa fa-chevron-right"></i>
                            </div>
                            <div class="row flex-wrap flex-col lista-tag">
                                <? if ($equipamentos) { ?>
                                    <div class="col-md-12 tag-equipamento hidden nivel-2">
                                        <div class="relative w-100 text-center">
                                            <a class='pointer list-group-item tagtip' id='tag-btequip' onclick="altpsq('tag','btequip');"> EQUIPAMENTO</a>
                                            <i class="arrow-icon fa fa-chevron-right"></i>
                                        </div>
                                        <? carregaTagTipo1($equipamentos); ?>
                                    </div>
                                <? } ?>
                                <? if ($salas) { ?>
                                    <div class="col-md-12 tag-sala hidden nivel-2">
                                        <div class="w-100 relative text-center">
                                            <a class='pointer list-group-item tagtip' id='tag-btsala' onclick="altpsq('tag','btsala')" ;> SALA</a>
                                            <i class="arrow-icon fa fa-chevron-right"></i>
                                        </div>
                                        <? carregaTagTipo2($salas); ?>
                                    </div>
                                <? } ?>
                                <? if ($veiculos) { ?>
                                    <div class="col-md-12 tag-veiculo hidden nivel-2">
                                        <div class="w-100 relative text-center">
                                            <a class='pointer list-group-item tagtip' id='tag-btveiculo' onclick="altpsq('tag','btveiculo')" ;> VEÍCULO</a>
                                            <i class="arrow-icon fa fa-chevron-right"></i>
                                        </div>
                                        <? carregaTagTipo3($veiculos); ?>
                                    </div>
                                <? } ?>
                                <? if ($prateleiras) { ?>
                                    <div class="col-md-12 tag-prateleira hidden nivel-2">
                                        <div class="relative w-100 text-center">
                                            <a class='pointer list-group-item tagtip' id='tag-btprateleira' onclick="altpsq('tag','btprateleira')"> PRATELEIRA</a>
                                            <i class="arrow-icon fa fa-chevron-right"></i>
                                        </div>
                                        <? carregaTagTipo4($prateleiras); ?>
                                    </div>
                                <? } ?>
                            </div>
                        </div>
                    <? } ?>
                    <!-- OP -->
                    <? if ($ops) { ?>
                        <div class="row d-flex flex-wrap nivel-1">
                            <div class="col-sm-12 px-0 w-100">
                                <div class="w-100 relative text-center">
                                    <a class='pointer list-group-item tagtip' id='op-btop' onclick="altpsq('op','btop');"> OP</a>
                                    <i class="arrow-icon fa fa-chevron-right"></i>
                                </div>
                                <div class="list-group lista-op hidden" id="listaop">
                                    <? carregaTipoOp($ops); ?>
                                </div>
                            </div>
                        </div>
                    <? } ?>
                    <!-- Categorias -->
                    <!-- <button class="filter-category">
                        <span class="filter-category-text">COMPROMISSOS</span>
                        <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.4507 5.33159L1.65492 9.12735C1.47186 9.31042 1.17506 9.31042 0.992013 9.12735L0.549298 8.68463C0.366544 8.50188 0.366192 8.20569 0.548516 8.0225L3.55674 5.00012L0.548516 1.97776C0.366192 1.79458 0.366544 1.49838 0.549298 1.31563L0.992013 0.872915C1.17508 0.689849 1.47188 0.689849 1.65492 0.872915L5.4507 4.66866C5.63375 4.85172 5.63375 5.14852 5.4507 5.33159Z" fill="#626262" />
                        </svg>
                    </button>
                    <button class="filter-category">
                        <span class="filter-category-text">CATEGORIAS</span>
                        <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.4507 5.33159L1.65492 9.12735C1.47186 9.31042 1.17506 9.31042 0.992013 9.12735L0.549298 8.68463C0.366544 8.50188 0.366192 8.20569 0.548516 8.0225L3.55674 5.00012L0.548516 1.97776C0.366192 1.79458 0.366544 1.49838 0.549298 1.31563L0.992013 0.872915C1.17508 0.689849 1.47188 0.689849 1.65492 0.872915L5.4507 4.66866C5.63375 4.85172 5.63375 5.14852 5.4507 5.33159Z" fill="#626262" />
                        </svg>
                    </button>
                    <button class="filter-category">
                        <span class="filter-category-text">RECORRÊNCIA</span>
                        <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.4507 5.33159L1.65492 9.12735C1.47186 9.31042 1.17506 9.31042 0.992013 9.12735L0.549298 8.68463C0.366544 8.50188 0.366192 8.20569 0.548516 8.0225L3.55674 5.00012L0.548516 1.97776C0.366192 1.79458 0.366544 1.49838 0.549298 1.31563L0.992013 0.872915C1.17508 0.689849 1.47188 0.689849 1.65492 0.872915L5.4507 4.66866C5.63375 4.85172 5.63375 5.14852 5.4507 5.33159Z" fill="#626262" />
                        </svg>
                    </button> -->
                </nav>
                <?
                if ($exibirTodos == 'Y') {
                    $classe = 'active';
                    $exibir = 'N';
                } else {
                    $classe = '';
                    $exibir = 'Y';
                }
                ?>
            </div>

            <!-- Visualização de eventos container aqui -->
        </div>
        <button class='btn-primary' id="openSidebarButton" style="display: none;">
            <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5.4507 5.33159L1.65492 9.12735C1.47186 9.31042 1.17506 9.31042 0.992013 9.12735L0.549298 8.68463C0.366544 8.50188 0.366192 8.20569 0.548516 8.0225L3.55674 5.00012L0.548516 1.97776C0.366192 1.79458 0.366544 1.49838 0.549298 1.31563L0.992013 0.872915C1.17508 0.689849 1.47188 0.689849 1.65492 0.872915L5.4507 4.66866C5.63375 4.85172 5.63375 5.14852 5.4507 5.33159Z" fill="#626262" />
            </svg>
        </button>
    </div>
</body>