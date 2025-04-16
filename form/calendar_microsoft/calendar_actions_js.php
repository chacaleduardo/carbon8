<script>
  // Variáveis globais
  var microsoftEvents = <?= '[]' ?>;
  var userLoggedInEmail = <?= json_encode(fetchUserProfile($_SESSION['access_token'] ?? null)) ?>;
  var access_token = <?= json_encode($_SESSION['access_token'] ?? null) ?>;
  var refresh_token = <?= json_encode($_SESSION['refresh_token'] ?? null) ?>;
  var isAuthenticated = "<?= isset($_SESSION['access_token']) ? 'true' : 'false'; ?>" === 'true';
  // Mapeamento para índices relativos
  const indexMap = {
      "first": 1,
      "second": 2,
      "third": 3,
      "fourth": 4,
      "last": -1
  };

  if (!Array.isArray(microsoftEvents)) {
    microsoftEvents = [];
  }

  var jsonEvents = v_calendarioferiados;
  var eventosAux = [];
  var eventoTipoActive = [];
  var eventoTipoConfig = [];
  var strEventoTipoActive = "";

  const calendar = document.getElementById("calendar");

  const today = new Date();
  let currentMonth = today.getMonth();
  let currentYear = today.getFullYear();
  /// use o moment.js para obter o primeiro dia da semana

  const months = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
  const days = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];


  $(document).ready(function() {
    // Inicializa o calendário
    fetchLatestEvents();
    atualizaTipoEvento();
    var initData = $('#calendar').context.lastModified.substr(0, 2);
    var mesCalendario = initData.substr(0, 2);

    if (!eventoTipoActive.length) {
        $('#limpar-filtro').addClass('disabled');
    }

    if ($("#tag-bttag").hasClass('ativo')) {
        $(".lista-tag > div").removeClass("hidden");

        if ($("#tag-btequip").hasClass('ativo')) {
            $('.tag-equipamento, .tag-equipamento .lista-item').removeClass('hidden');
        }

        if ($("#tag-btsala").hasClass('ativo')) {
            $('.tag-sala .lista-item').removeClass('hidden');
        }

        if ($("#tag-btveiculo").hasClass('ativo')) {
            $('.tag-veiculo .lista-item').removeClass('hidden');
        }

        if ($("#tag-btprateleira").hasClass('ativo')) {
            $('.tag-prateleira .lista-item').removeClass('hidden');
        }

    }

    if ($("#op-btop").hasClass('ativo')) {
        $('.lista-op').removeClass('hidden');
    }

    if ($("#eventos-btevento").hasClass('ativo')) {
        $(".lista-eventos").removeClass("hidden");
    }
});

$('#limpar-filtro').on('click', function() {
    eventoTipoActive = [];
    $(this).addClass('disabled');

    $('.active').removeClass('active');
    $('.ativo').removeClass('ativo');
    $('.aberto').removeClass('aberto');
    $('.lista-item').addClass('hidden');
    $('.lista-tag').addClass('hidden');
    $('.lista-op').addClass('hidden');

    salvaConfig(eventoTipoActive);
    removerEventosAtuais();
    // fullcalendar.render();
  
});
  // Inicializa o calendário
  initCalendar();

  // Define os ouvintes de eventos
  setupEventListeners();

  // Funções utilitárias para formatar datas e horas
  function formatTime(date) {
    return date.toLocaleTimeString('pt-BR', {
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function showElement(element) {
    element.style.display = 'block';
  }

  function hideElement(element) {
    element.style.display = 'none';
  }

  // Configurações iniciais do calendário
  function initCalendar() {
    setupDaysHeader();
    showCalendar(currentMonth, currentYear);
  }

  // Configura o cabeçalho com os dias da semana
  function setupDaysHeader() {
    const dataHead = days.map(day => `<th data-days='${day}'>${day}</th>`).join('');
    document.getElementById("thead-month").innerHTML = `<tr>${dataHead}</tr>`;
  }

  // Configura os ouvintes de eventos
  function setupEventListeners() {
    // document.getElementById('eventType').addEventListener('change', () => {
    //   handleModalType();
    // });

    $('#limpar-filtro').on('click', clearFilters);

    let timeoutId;
    $('.coluna-calendario').on('click', '.fc-button-group .fc-prev-button,.fc-button-group .fc-next-button', function() {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => {
        altpsq(0);
      }, 400);
    });


  }

  // Função para mostrar o calendário
  function showCalendar(month, year) {
    const firstDay = new Date(year, month).getDay();
    const lastDatePrevMonth = new Date(year, month, 0).getDate();
    const daysInCurrentMonth = daysInMonth(month, year);
    const tbl = document.getElementById("calendar-body");
    const monthAndYear = document.getElementById("monthAndYear");

    tbl.innerHTML = "";
    monthAndYear.innerHTML = `${months[month]} ${year}`;

    let date = 1;
    let nextMonthDate = 1;
    for (let i = 0; i < 6; i++) {
      const row = document.createElement("tr");
      for (let j = 0; j < 7; j++) {
        const cell = createCell(i, j, firstDay, date, daysInCurrentMonth, month, year, lastDatePrevMonth, nextMonthDate);
        addEventListeners(cell);
        row.appendChild(cell);

        if (i === 0 && j < firstDay) {
          // Dias do mês anterior
        } else if (date > daysInCurrentMonth) {
          // Dias do próximo mês
          nextMonthDate++;
        } else {
          date++;
        }
      }
      tbl.appendChild(row);
    }
    altpsq(0);
    addEventsToCalendar(microsoftEvents, 'microsoft');
    addEventsToCalendar([...eventosAux], 'sislaudo');
  }

  // Cria uma célula do calendário
  function createCell(i, j, firstDay, date, daysInCurrentMonth, month, year, lastDatePrevMonth, nextMonthDate) {
    const cell = document.createElement("td");
    let cellDate, cellMonth, cellYear, isCurrentMonth;

    if (i === 0 && j < firstDay) {
      cellDate = lastDatePrevMonth - firstDay + j + 1;
      cellMonth = month === 0 ? 11 : month - 1;
      cellYear = month === 0 ? year - 1 : year;
      isCurrentMonth = false;
      cell.className = "not-current-month prev-month";
    } else if (date > daysInCurrentMonth) {
      cellDate = nextMonthDate;
      cellMonth = month === 11 ? 0 : month + 1;
      cellYear = month === 11 ? year + 1 : year;
      isCurrentMonth = false;
      cell.className = "not-current-month next-month";
    } else {
      cellDate = date;
      cellMonth = month;
      cellYear = year;
      isCurrentMonth = true;
      cell.className = "date-picker";
    }

    cell.setAttribute("data-date", cellDate);
    cell.setAttribute("data-month", cellMonth + 1);
    cell.setAttribute("data-year", cellYear);
    cell.innerHTML = `<span>${cellDate}</span><div class='events-content'></div>`;

    if (isCurrentMonth && date === today.getDate() && year === today.getFullYear() && month === today.getMonth()) {
      cell.classList.add("selected");
      document.getElementById('eventDetailsHeader').innerText = `${cellDate} ${months[month]} ${year}`;
    }

    return cell;
  }

  // Função para avançar para o próximo mês
  function next() {
    if (currentMonth === 11) currentYear++;
    currentMonth = (currentMonth + 1) % 12;
    showCalendar(currentMonth, currentYear);
  }

  // Função para retroceder para o mês anterior
  function previous() {
    if (currentMonth === 0) currentYear--;
    currentMonth = (currentMonth === 0) ? 11 : currentMonth - 1;
    showCalendar(currentMonth, currentYear);
  }

  // Adiciona ouvintes de eventos às células do calendário
  function addEventListeners(cell) {
    cell.addEventListener('click', function() {
      const cellClass = this.classList;
      if (cellClass.contains('prev-month')) {
        previous();
      } else if (cellClass.contains('next-month')) {
        next();
      } else {
        handleDateSelection(this);
      }
    });

    cell.addEventListener('dblclick', function() {
      openEventModal(this);
    });
  }

  // Função para manipular a seleção de data
  function handleDateSelection(cell) {
    const selectedDate = `${cell.getAttribute('data-date')} ${months[parseInt(cell.getAttribute('data-month')) - 1]} ${cell.getAttribute('data-year')}`;
    // document.getElementById('selectedDate').innerText = `Data selecionada: ${selectedDate}`;
    document.getElementById('eventDetailsHeader').innerText = selectedDate;
    clearSelection();
    cell.classList.add('selected');
    showEventDetails(cell.getAttribute('data-date'), cell.getAttribute('data-month'), cell.getAttribute('data-year'));
  }

  // Função para limpar a seleção de datas
  function clearSelection() {
    document.querySelectorAll('.date-picker.selected').forEach(cell => {
      cell.classList.remove('selected');
    });
  }

  // Função para obter a quantidade de dias em um mês
  function daysInMonth(iMonth, iYear) {
    return 32 - new Date(iYear, iMonth, 32).getDate();
  }

  // Adiciona eventos ao calendário
  function addEventsToCalendar(events, type) {
    let eventoRecorrente = [];
    if (events && Array.isArray(events)) {
      events.forEach(evento => {
        const eventDate = type === 'microsoft' ? new Date(evento.start.dateTime) : new Date(evento.start);
        const eventCell = document.querySelector(`td[data-date="${eventDate.getDate()}"][data-month="${eventDate.getMonth() + 1}"][data-year="${eventDate.getFullYear()}"]`);
        if (eventCell) {
          const eventContainer = eventCell.querySelector('.events-content');
          addEventToContainer(eventContainer, evento, type);
        }
      });
    }
  }

  function calculateRecurrences(recurrence) {
    const pattern = recurrence.pattern;
    const range = recurrence.range;

    const startDate = new Date(range.startDate);
    const endDate = new Date(range.endDate);
    const occurrences = [];

    const daysMap = {
        "sunday": 0, "monday": 1, "tuesday": 2, "wednesday": 3, 
        "thursday": 4, "friday": 5, "saturday": 6
    };

    if (pattern.type === "daily") {
        const interval = pattern.interval;

        let currentDate = new Date(startDate);
        while (currentDate <= endDate) {
            occurrences.push(currentDate.toISOString().split('T')[0]);
            currentDate.setDate(currentDate.getDate() + interval);
        }

    } else if (pattern.type === "weekly") {
        const interval = pattern.interval;
        const daysOfWeek = pattern.daysOfWeek.map(day => day.toLowerCase());

        let currentDate = new Date(startDate);
        let weekCount = 0;

        while (currentDate <= endDate) {
            const currentDay = currentDate.getDay();
            const currentDayName = Object.keys(daysMap).find(key => daysMap[key] === currentDay);

            if (daysOfWeek.includes(currentDayName)) {
                occurrences.push(currentDate.toISOString().split('T')[0]);
            }

            currentDate.setDate(currentDate.getDate() + 1);

            if (currentDay === daysMap[pattern.firstDayOfWeek]) {
                weekCount++;
                if (weekCount === interval) {
                    weekCount = 0;
                }
            }
        }

    } else if (pattern.type === "absoluteMonthly") {
        const interval = pattern.interval;
        const dayOfMonth = pattern.dayOfMonth;

        let currentDate = new Date(startDate);

        while (currentDate <= endDate) {
            if (currentDate.getDate() === dayOfMonth) {
                occurrences.push(currentDate.toISOString().split('T')[0]);
                currentDate.setMonth(currentDate.getMonth() + interval);
                currentDate.setDate(dayOfMonth);
            } else {
                currentDate.setDate(currentDate.getDate() + 1);
            }
        }

    } else if (pattern.type === "relativeMonthly") {
        const interval = pattern.interval;
        const index = pattern.index.toLowerCase();
        const daysOfWeek = pattern.daysOfWeek.map(day => day.toLowerCase());

        let currentDate = new Date(startDate);

        while (currentDate <= endDate) {
            const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            let targetDay = null;
            let count = 0;
            const lastDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();

            for (let day = 1; day <= lastDayOfMonth; day++) {
                const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                if (daysOfWeek.includes(Object.keys(daysMap).find(key => daysMap[key] === date.getDay()))) {
                    count++;
                    if (index === 'last') {
                        targetDay = date;  // always update targetDay to the current one
                        date.setDate(date.getDate() + 1);
                        break;
                    } else if (count === indexMap[index]) {
                        targetDay = date;
                        date.setDate(date.getDate() + 1);
                        break;
                    }
                }
            }

            if (targetDay && targetDay <= endDate) {
                occurrences.push(targetDay.toISOString().split('T')[0]);
            }

            currentDate.setMonth(currentDate.getMonth() + interval);
            currentDate.setDate(1);
        }

    } else if (pattern.type === "absoluteYearly") {
        const interval = pattern.interval;
        const dayOfMonth = pattern.dayOfMonth;
        const monthOfYear = pattern.month;

        let currentDate = new Date(startDate);

        while (currentDate <= endDate) {
            if (currentDate.getDate() === dayOfMonth && currentDate.getMonth() === monthOfYear - 1) {
                occurrences.push(currentDate.toISOString().split('T')[0]);
                currentDate.setFullYear(currentDate.getFullYear() + interval);
                currentDate.setMonth(monthOfYear - 1, dayOfMonth);
            } else {
                currentDate.setDate(currentDate.getDate() + 1);
            }
        }

    } else if (pattern.type === "relativeYearly") {
        const interval = pattern.interval;
        const index = pattern.index.toLowerCase();
        const monthOfYear = pattern.month;
        const daysOfWeek = pattern.daysOfWeek.map(day => day.toLowerCase());

        let currentDate = new Date(startDate);

        while (currentDate <= endDate) {
            const firstDayOfMonth = new Date(currentDate.getFullYear(), monthOfYear - 1, 1);
            let targetDay = null;
            let count = 0;
            const lastDayOfMonth = new Date(currentDate.getFullYear(), monthOfYear, 0).getDate();

            for (let day = 1; day <= lastDayOfMonth; day++) {
                const date = new Date(currentDate.getFullYear(), monthOfYear - 1, day);
                if (daysOfWeek.includes(Object.keys(daysMap).find(key => daysMap[key] === date.getDay()))) {
                    count++;
                    if (index === 'last') {
                        targetDay = date;  // always update targetDay to the current one
                    } else if (count === indexMap[index]) {
                        targetDay = date;
                        break;
                    }
                }
            }

            if (targetDay && targetDay <= endDate) {
                occurrences.push(targetDay.toISOString().split('T')[0]);
            }

            currentDate.setFullYear(currentDate.getFullYear() + interval);
            currentDate.setMonth(monthOfYear - 1, 1);
        }
    }

    return occurrences;
}

  // Função para adicionar eventos ao container
  function addEventToContainer(eventContainer, evento, type) {
    const existingEvents = eventContainer.querySelectorAll('.event');
    if (existingEvents.length < 2) {
      const eventDiv = document.createElement('div');
      eventDiv.className = `event ${type}`;
      eventDiv.setAttribute('data-event-id', evento.id);

      // Defina a cor de fundo para eventos "Sislaudo"
      if (type === 'sislaudo' && evento.color) {
        eventDiv.style.backgroundColor = evento.color;
        //definir cor do texto preto ou branco com base na cor de fundo
        const rgb = evento.color.match(/\d+/g);
        const brightness = Math.round(((parseInt(rgb[0]) * 299) + (parseInt(rgb[1]) * 587) + (parseInt(rgb[2]) * 114)) / 1000);
        eventDiv.style.color = brightness > 125 ? '#000' : '#fff';
        eventDiv.style.borderLeft = '5px solid #28a745'; // Adiciona uma borda à esquerda
      }

      // Defina a cor com base no organizador para eventos Microsoft
      if (type === 'microsoft') {

        if(evento.isCancelled){
          eventDiv.style.backgroundColor = '#FBAEAE';
        }else{
          if (evento.isOrganizer) {
            eventDiv.classList.add('is-organizer');
          } else {
            eventDiv.classList.add('attendee');
          }
        }
      }
      

      // Define se o usuário logado confirmou presença no evento
      if (type === 'microsoft') {
        for (let index = 0; index < evento.attendees.length; index++) {
          const attendee = evento.attendees[index];
          if (attendee.emailAddress.address === userLoggedInEmail.mail) {
            if (attendee.status.response === 'accepted') {
              eventDiv.classList.add('accepted');
            } else if (attendee.status.response === 'declined') {
              eventDiv.classList.add('declined');
            } else if (attendee.status.response === 'tentativelyAccepted') {
              eventDiv.classList.add('tentatively-accepted');
            }
          }
        }
      }

      // Limpa o título do evento "Sislaudo" para remover a tag <br> e adicionar espaço
      let eventName;
      if (type === 'microsoft') {
        eventName = evento.subject || 'Sem título';
      } else {
        // Remove <br>, adiciona espaço entre as partes do título e trata null/undefined
        eventName = evento.title ? evento.title.replace(/<\/?br\s*\/?>/gi, ' ').trim() : 'Novo Evento';
      }
      
      const eventTime = type === 'microsoft' ? formatTime(new Date(evento.start.dateTime)) : formatTime(new Date(evento.start));
      eventDiv.innerHTML = type === 'sislaudo' ? `<span style="color: #fff;">${eventTime}</span>${eventName}` : `<span>${eventTime}</span>${eventName}`;
      eventContainer.appendChild(eventDiv);

      if (type === 'microsoft') {
        eventDiv.addEventListener('click', function() {
          showEventDetailsModalMicrosoft(evento.id);
        });
      } else {
        eventDiv.addEventListener('click', function(event) {
          event.stopPropagation(); // Impede que o evento de clique da célula do calendário seja disparado também
          CB.modal({
            url: `${evento.url}&_modo=form`,
            header: "Evento"
          });
        });
      }
    } else if (existingEvents.length === 2) {
      let moreEventsDiv = eventContainer.querySelector('.more-events');
      if (!moreEventsDiv) {
        moreEventsDiv = document.createElement('div');
        moreEventsDiv.className = 'more-events';
        moreEventsDiv.innerHTML = '+ mais';
        eventContainer.appendChild(moreEventsDiv);
      }
      const moreEventsCount = parseInt(moreEventsDiv.getAttribute('data-count') || '0', 10) + 1;
      moreEventsDiv.setAttribute('data-count', moreEventsCount);
      moreEventsDiv.innerHTML = `+${moreEventsCount}`;
    }
  }

  // Função para adicionar eventos ao container
  // function addEventToContainer(eventContainer, evento, type) {
  //   const existingEvents = eventContainer.querySelectorAll('.event');
  //   if (existingEvents.length < 2) {
  //     const eventDiv = document.createElement('div');
  //     eventDiv.className = `event ${type}`;
  //     eventDiv.setAttribute('data-event-id', evento.id);

  //     // Aplique a cor de fundo diretamente no elemento
  //     if (type === 'sislaudo' && evento.color) {
  //       eventDiv.style.backgroundColor = evento.color;
  //     }

  //     // Limpa o título do evento "Sislaudo" para remover a tag <br> e adicionar espaço
  //     let eventName;
  //     if (type === 'microsoft') {
  //       eventName = evento.subject || 'Sem título';
  //       eventDiv.innerHTML = `${eventName}`;
  //     } else {
  //       // Remove <br>, adiciona espaço entre as partes do título e trata null/undefined
  //       eventName = evento.title ? evento.title.replace(/<\/?br\s*\/?>/gi, ' ').trim() : 'Sem título';
  //       const eventTime = formatTime(new Date(evento.start));
  //       eventDiv.innerHTML = `<span class="event-time">${eventTime}</span>${eventName}`;
  //     }

  //     eventContainer.appendChild(eventDiv);

  //     if (type === 'microsoft') {
  //       eventDiv.addEventListener('click', function() {
  //         showEventDetailsModalMicrosoft(evento.id);
  //       });
  //     } else {
  //       eventDiv.addEventListener('click', function(event) {
  //         event.stopPropagation(); // Impede que o evento de clique da célula do calendário seja disparado também
  //         CB.modal({
  //           url: `${evento.url}&_modo=form`,
  //           header: "Evento"
  //         });
  //       });
  //     }
  //   } else if (existingEvents.length === 2) {
  //     let moreEventsDiv = eventContainer.querySelector('.more-events');
  //     if (!moreEventsDiv) {
  //       moreEventsDiv = document.createElement('div');
  //       moreEventsDiv.className = 'more-events';
  //       moreEventsDiv.innerHTML = '+ mais';
  //       eventContainer.appendChild(moreEventsDiv);
  //     }
  //     const moreEventsCount = parseInt(moreEventsDiv.getAttribute('data-count') || '0', 10) + 1;
  //     moreEventsDiv.setAttribute('data-count', moreEventsCount);
  //     moreEventsDiv.innerHTML = `+${moreEventsCount}`;
  //   }
  // }

  // Mostra os detalhes do evento selecionado
  function showEventDetails(day, month, year) {
    const selectedDate = new Date(year, month - 1, day);
    const eventDetailsBody = document.getElementById('eventDetailsBody');
    eventDetailsBody.innerHTML = '';

    microsoftEvents.forEach(evento => {
      const eventoData = new Date(evento.start.dateTime);
      if (eventoData.toDateString() === selectedDate.toDateString()) {
        const eventEndDate = new Date(evento.end.dateTime);
        const eventDuration = calculateDuration(eventoData, eventEndDate);

        // Cria o elemento de lista para o evento Microsoft
        const eventItem = document.createElement('li');
        // Defina a cor com base no organizador para eventos Microsoft
      
        if (evento.isOrganizer ) {
          classe = "is-organizer";
        } else {
          classe = "attendee";
        }

        if (evento.isCancelled) {
          eventItem.style.backgroundColor = '#FBAEAE';
          classe = "cancelled";
        }
      
        eventItem.className = 'event-card '+classe;
        eventItem.innerHTML = `
          <div class="event-content">
              <span class="event-time">${formatTime(eventoData)}</span>
              <span class="event-title">${evento.subject}</span>
          </div>
          <div class="event-organizer">
               <span class="event-organizer">${evento.organizer.emailAddress.address}</span>
          </div>
          <div class="event-hours-range">
              <span class="event-hours">${eventDuration}</span>
          </div>`;

        // Adiciona o listener para abrir o modal de edição do Microsoft
        eventItem.addEventListener('click', function() {
          showEventDetailsModalMicrosoft(evento.id);
        });

        eventDetailsBody.appendChild(eventItem);
      }
    });

    // Exibe eventos Sislaudo
    eventosAux.forEach(evento => {
      const eventoData = new Date(evento.start);
      if (eventoData.toDateString() === selectedDate.toDateString()) {
        const eventEndDate = new Date(evento.end);
        const eventDuration = calculateDuration(eventoData, eventEndDate);

        // Trata o título do evento
        const eventName = evento.title ? evento.title.replace(/<\/?br\s*\/?>/gi, ' ').trim() : 'Sem título';

        // Cria o elemento de lista para o evento Sislaudo
        const eventItem = document.createElement('li');
        eventItem.className = 'event-card green';
        eventItem.style.backgroundColor = evento.color;
        eventItem.style.color = '#fff';
        eventItem.innerHTML = `
          <div class="event-content">
              <span class="event-time">${formatTime(eventoData)}</span>
              <span class="event-organizer">${eventName}</span>
          </div>
          <div class="event-hours-range">
              <span class="event-hours">${eventDuration}</span>
          </div>`;

        // Adiciona o listener para abrir o modal de edição do Sislaudo
        eventItem.addEventListener('click', function() {
          CB.modal({
            url: `${evento.url}&_modo=form`,
            header: "Evento"
          });
        });

        eventDetailsBody.appendChild(eventItem);
      }
    });
  }

  // Configurações relacionadas ao sidebar
  const btnPrimary = document.querySelector('.sidebar .btn-primary');
  const sidebar = document.querySelector('.sidebar');
  const openSidebarButton = document.getElementById('openSidebarButton');

  btnPrimary.addEventListener('click', function() {
    sidebar.classList.add('collapsing');
    sidebar.classList.add('hide');
    setTimeout(() => {
      sidebar.classList.remove('show');
      hideElement(sidebar);
      openSidebarButton.style.display = 'flex';
      sidebar.classList.remove('collapsing');
      sidebar.classList.remove('hide');
    }, 100);
  });

  openSidebarButton.addEventListener('click', function() {
    sidebar.style.display = 'flex';
    sidebar.classList.add('collapsing');
    sidebar.classList.add('show');
    setTimeout(() => {
      hideElement(openSidebarButton);
      sidebar.classList.remove('collapsing');
      sidebar.classList.remove('show');
    }, 100);
  });

  $(".filter-category").on("click", function() {
    $(this).toggleClass('show');
  });



  // Atualiza eventos do Microsoft Calendar periodicamente
  if ("<?= isset($_SESSION['access_token']) ? 'true' : 'false'; ?>" === 'true') {
    setInterval(fetchLatestEvents, 60000); // 60 segundos
    //Pega mes atual do calendario
    let date = new Date();
    let start = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-01`;
    let end = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate()}`;
    carregaEventos(strEventoTipoActive, start, end).then(eventos => {
      if (eventos !== "error") {
        removerEventosAtuais();
        renderEventos(eventos);
      }
    });
  }
  console.log('Microsoft Calendar events:', microsoftEvents);

  // Busca os eventos mais recentes do Microsoft Calendar
  function fetchLatestEvents() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'https://graph.microsoft.com/v1.0/me/events/?$top=2000', true);
    xhr.setRequestHeader('Authorization', `Bearer ${access_token}`);
    xhr.setRequestHeader('Prefer', 'outlook.timezone="America/Sao_Paulo"');
    xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
    xhr.onreadystatechange = async function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        // Parseia a resposta para obter os novos eventos
        var newEvents = JSON.parse(xhr.responseText).value;
        let eventoInfo;
        let newEventsRecurrents = [];
        for (let i = 0; i < newEvents.length; i++) {
          if (newEvents[i].recurrence !== null) {
            eventoInfo = await fetchRecurrentEventDetails(newEvents[i].id, newEvents[i].recurrence.range.startDate, newEvents[i].recurrence.range.endDate);
            newEventsRecurrents.push(eventoInfo);
          }
        }
        for (let i = 0; i < newEventsRecurrents.length; i++) {

          if (newEventsRecurrents[i]) {
            newEventsRecurrents[i].forEach(evento => {
              newEvents.push(evento);
            });
          }
        }
        
        //Retira eventos duplicados do array
        newEvents = newEvents.filter((evento, index, self) =>
          index === self.findIndex((t) => (
            (t.subject === evento.subject && t.start.dateTime === evento.start.dateTime)
          ))
        );

        // Ordena os eventos por data de início
        newEvents.sort((a, b) => new Date(a.start.dateTime) - new Date(b.start.dateTime));

        updateCalendarEvents(newEvents);
      }
    };
    xhr.send();
  }

  // Função para atualizar apenas os eventos do calendário
  function updateCalendarEvents(newEvents) {

    //Remove div do more events
    document.querySelectorAll('.more-events').forEach(event => event.remove());

    // Remove eventos atuais do Microsoft no calendário
    document.querySelectorAll('.event.microsoft').forEach(event => event.remove());

    // Atualiza a lista de eventos globais
    microsoftEvents = newEvents;

    // Adiciona os novos eventos ao calendário
    addEventsToCalendar(microsoftEvents, 'microsoft');
  }

  // Função para logout
  function handleLogout() {
    window.location.href = "<?= $_SERVER['PHP_SELF']; ?>?logout=true";
  }

  // Funções relacionadas à criação e visualização de eventos Sislaudo
  function criaEvento(vchave) {
    $('#modalEventoTipo').modal('hide');

    let selectedDate = document.querySelector('.date-picker.selected');
    let formattedDate = selectedDate ?
      `${selectedDate.getAttribute('data-date').padStart(2, '0')}/${selectedDate.getAttribute('data-month').padStart(2, '0')}/${selectedDate.getAttribute('data-year')}` :
      formatDate(new Date(), 'dd/mm/yyyy');

    CB.modal({
      url: `?_modulo=evento&_acao=i&inicio=${formattedDate}&fim=${formattedDate}&dataclick=${formattedDate}&eventotipo=${vchave}&calendario=true&_modo=form&datacalendario=true`,
      header: "Evento",
      callback: function(data, textStatus, jqXHR) {
        let lastInsertId = jqXHR.getResponseHeader("X-CB-PKID");
        if (lastInsertId) {
          getEvento(lastInsertId);
        }
      },
    });
  }

  function renderEventos(eventos) {
    eventos.forEach(evento => {
      const eventDate = new Date(evento.start);
      const eventCell = document.querySelector(`td[data-date="${eventDate.getDate()}"][data-month="${eventDate.getMonth() + 1}"][data-year="${eventDate.getFullYear()}"] .events-content`);
      if (eventCell) {
        addEventToContainer(eventCell, evento, 'sislaudo');
      }
    });
  }

  function removerEventosAtuais() {
    $(".event.sislaudo").remove();
  }

  function getEvento(idEvento) {
    var token = Cookies.get('jwt') || localStorage.getItem("jwt") || "";

    fetch(`ajax/evento.php?vopcao=getevento&videvento=${idEvento}`, {
        headers: {
          "authorization": token
        }
      }).then(response => response.json())
      .then(data => {
        if (data.error) {
          return alertAtencao(data.error);
        }

        data.forEach(evento => {
          eventosAux.push({
            "id": evento.idevento,
            "nomecurto": evento.nomecurto,
            "start": `${evento.inicio} ${evento.iniciohms}`,
            "end": `${evento.fim} ${evento.fimhms}`,
            "prazo": evento.prazo,
            "color": evento.cor,
            "allDay": evento.diainteiro,
            "url": `?_modulo=evento&_acao=u&idevento=${evento.idevento}`
          });
        });

        removerEventosAtuais();
        renderEventos([...eventosAux]);
      });
  }

  function carregaEventos(eventoTipo = '', start, end) {
    return new Promise(function(resolve, reject) {
      $.ajax({
        type: "get",
        url: `ajax/carregacal.php?veventotipo=${eventoTipo}&start=${start}&end=${end}`,
        success: function(data) {
          if (data.error) {
            return alertAtencao(data.error);
          }

          let eventos = JSON.parse(data);
          if (eventos !== undefined) {
            eventosAux = [...eventos];
            console.log('Eventos:', eventos);
            resolve(eventos);
          } else {
            resolve("error");
          }
        }
      });
    });
  }

  function salvaConfig(tiposAtivos) {
    if (!tiposAtivos.length) {
      return localStorage.setItem("eventotipoconfig", JSON.stringify(eventoTipoActive));
    }

    return localStorage.setItem("eventotipoconfig", JSON.stringify(tiposAtivos));
  }

  function altpsq(vtipo = false, vchave = false) {
    $('body').css('cursor', 'wait');
    $('#limpar-filtro').removeClass('disabled');

    //pega range de datas do calendário se não pega data atual
    var date =  document.querySelectorAll('.prev-month')[0] ?? document.querySelectorAll('.date-picker')[0];
    if(date == null){

      var date = new Date();
      var start = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-01`;
      var end = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate()}`;

    }else{

      var dateStart = date;
      var dateEnd = document.querySelectorAll('.next-month')[document.querySelectorAll('.next-month').length - 1] ?? document.querySelectorAll('.date-picker')[document.querySelectorAll('.date-picker').length - 1];
      var start = `${date.getAttribute('data-year')}-${date.getAttribute('data-month').padStart(2, '0')}-01`;
      var end = `${dateEnd.getAttribute('data-year')}-${dateEnd.getAttribute('data-month').padStart(2, '0')}-${new Date(dateEnd.getAttribute('data-year'), dateEnd.getAttribute('data-month'), 0).getDate()}`;

    }

    let classe = "";
    let tipos = {
      bttag: 'tag',
      btevento: 'eventos',
      btop: 'op',
      btsala: 'sala',
      btequip: 'equipamento',
      btveiculo: 'veiculo',
      btprateleira: 'prateleira'
    };

    if (['eventos', 'tag', 'op'].includes(vtipo)) {
      classe = "ativo aberto";
    } else {
      classe = "active";
    }

    if (vchave) {
      vchave += "";

      let JQtipoSelecionado = $(`#${vtipo}-${vchave}`);

      if (!JQtipoSelecionado.hasClass(classe)) {
        JQtipoSelecionado.addClass(classe);
        JQtipoSelecionado.addClass(`${classe}`);

        if (vchave == 'bttag' || vchave == 'btevento' || vchave == 'btop') {
          if (vchave == 'bttag') {
            let JQlista = $(".lista-tag, .lista-tag > div");

            if (JQlista.find('.ativo.aberto')) {
              if (JQlista.find('.ativo.aberto').parent().next().hasClass('hidden')) {
                JQlista.find('.ativo.aberto').parent().next().removeClass('hidden');
              }
            }

            JQlista.removeClass("hidden");
          } else {
            $(`.lista-${tipos[vchave]}`).removeClass("hidden");
          }
        } else {
          $(`.tag-${tipos[vchave]} .lista-item`).removeClass("hidden");
        }

        eventoTipoActive = eventoTipoActive.filter(element => element);

        if (!eventoTipoActive.includes(`'${vtipo}-${vchave}'`)) {
          eventoTipoActive.push(`'${vtipo}-${vchave}'`);
        }

        strEventoTipoActive = eventoTipoActive.join(',');
      } else {
        JQtipoSelecionado.removeClass(`${classe}`);

        let JQtipoFilhos = $(`.${vchave}`);

        if (vtipo == 'eventos') {
          JQtipoFilhos = $(`.evento`);
        }

        if (vtipo == 'op') {
          JQtipoFilhos = $(`.listaop`);
        }

        //JQtipoFilhos.removeClass(`${classe} active`);

        for (var i = 0; i < eventoTipoActive.length; i++) {
          if (eventoTipoActive[i] == `'${vtipo}-${vchave}'` ) {
            eventoTipoActive[i] = false;
          }
        }

        if (vchave == 'bttag') {
          $("#eventobttag").removeClass("aberto");
          $(".lista-tag").addClass("hidden");
        } else if (vchave == 'btevento') {
          $('#eventobtevento').removeClass('aberto');
          $(".lista-eventos").addClass("hidden");
        } else if (vchave == 'btop') {
          $('.lista-op').addClass('hidden');
        } else if (vchave == 'btequip') {
          $("#eventobtequip").removeClass(`${classe}`);
          $(".tag-equipamento .lista-item").addClass("hidden");
        } else if (vchave == 'btsala') {
          $("#eventobtsala").removeClass(`${classe}`);
          $(".tag-sala .lista-item").addClass("hidden");
        } else if (vchave == 'btveiculo') {
          $("#eventobtveiculo").removeClass(`${classe}`);
          $(".tag-veiculo .lista-item").addClass("hidden");
        } else if (vchave == 'btprateleira') {
          $("#eventobtprateleira").removeClass(`${classe}`);
          $(".tag-prateleira .lista-item").addClass("hidden");
        }

        eventoTipoActive = eventoTipoActive.filter(element => element);
        strEventoTipoActive = eventoTipoActive.join(',');
      }

      salvaConfig(eventoTipoActive);
    }

    carregaEventos(strEventoTipoActive, start, end).then((eventos) => {
      if (eventos !== "error") {
        removerEventosAtuais();
        renderEventos(eventos);
        if (document.getElementsByClassName('selected') && document.getElementsByClassName('selected')[0]) {
          document.getElementsByClassName('selected')[0].click();
        }
      }
    });

    if (!eventoTipoActive.length) {
      $('#limpar-filtro').addClass('disabled');
    }

    $('body').css('cursor', '');
  }

  function exibirTodos(exibirTodos) {
    CB.loadUrl({
      urldestino: `${CB.urlDestino}?_modulo=calendario&exibirtodos=${exibirTodos}`
    });
  }

  // Atualiza o tipo de evento
  function atualizaTipoEvento() {
    var eventoConfig = false;
    var idDoElemento;
    var JQelemento;

    if (localStorage.getItem("eventotipoconfig")) {
      eventoConfig = JSON.parse(localStorage.getItem("eventotipoconfig"));
    }

    if (eventoConfig && eventoConfig != "") {
      for (let i = 0; i < eventoConfig.length; i++) {
        if (eventoConfig[i].search('-') === -1) continue;

        idDoElemento = eventoConfig[i].replaceAll(["'"], '');
        JQelemento = $(`#${idDoElemento}`);

        if (JQelemento.prop('tagName') == 'LI') {
          JQelemento.addClass("active");
        } else {
          JQelemento.addClass("ativo aberto");
        }

        var chave = JQelemento.attr("id");

        eventoTipoActive.push(eventoConfig[i]);
      }
    } else {
      eventoConfig = [];
    }

    if (eventoTipoActive && eventoTipoActive != "") {
      strEventoTipoActive = eventoTipoActive.join(',');
    }

    showCalendar(currentMonth, currentYear);
  }

  // Função chamada ao document.ready
  $(document).ready(function() {
    if (eventosAux.length) {
      addEventsToCalendar([...eventosAux], 'sislaudo');
    }
    atualizaTipoEvento();
    altpsq();
  });

  // Limpar filtros
  function clearFilters() {
    eventoTipoActive = [];
    $('#limpar-filtro').addClass('disabled');

    $('.active').removeClass('active');
    $('.ativo').removeClass('ativo');
    $('.aberto').removeClass('aberto');
    $('.lista-item').addClass('hidden');
    $('.lista-tag').addClass('hidden');
    $('.lista-op').addClass('hidden');

    salvaConfig(eventoTipoActive);
    removerEventosAtuais();
    showCalendar(currentMonth, currentYear);
  }
</script>

<style>
  /* Estilos para eventos Sislaudo */
  .event.sislaudo {
    color: #fff;
    /* Cor do texto branca */
  }

  .event.sislaudo .event-time {
    display: inline-block;
    margin-right: 5px;
  }
</style>