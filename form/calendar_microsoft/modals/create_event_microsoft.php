<script>
    let conflicts = [];
    let currentEvent = {};

    const eventForm = document.getElementById("eventForm");
    const eventModal = document.getElementById("eventModal");
    const closeModal = document.getElementById("closeEditButtonParticipant");
    const discardButton = document.getElementById("discardEventButton");

    const eventRoomSelect = document.getElementById("eventRoom");
    const editModal = document.getElementById("eventModal");

    const modalHeader = document.querySelector(".modal-header h2");
    const eventTitle = document.getElementById("eventTitle");
    const eventStartDate = document.getElementById("eventStartDate");
    const eventEndDate = document.getElementById("eventEndDate");
    const eventStartHour = document.getElementById("eventStartHour");
    const eventEndHour = document.getElementById("eventEndHour");
    const participantEmail = document.getElementById("eventParticipantsInput");
    const description = document.getElementById("editEventDescription");
    const teamsMeetingCheckbox = document.getElementById("teamsMeeting");
    const inPersonMeetingCheckbox = document.getElementById("inPersonMeeting");
    const microsoftContent = document.getElementById("microsoftContent");
    const sislaudoContent = document.getElementById("sislaudoContent");

    const scheduleMeetingBtn = document.getElementById("scheduleMeetingBtn");

    // Função para abrir o modal de eventos Microsoft ao clicar no botão
    function openMicrosoftEventModalFromButton() {
        if (isAuthenticated) { // Verifica se o usuário está autenticado
            eventModal.style.display = "block";
            modalHeader.innerHTML = '<h2>Evento Microsoft</h2>';
            microsoftContent.style.display = "block";
            sislaudoContent.style.display = "none";
            clearModalContent();
            prepareEventFormForMicrosoft();
        } else {
            // Desabilita o botão e exibe uma mensagem de erro
            scheduleMeetingBtn.disabled = true;
            alert("Você precisa estar autenticado na Microsoft para criar um evento.");
        }
    }

    // Função para abrir o modal de eventos quando uma célula do calendário é clicada duas vezes
    async function openEventModal(cell) {
        eventModal.style.display = "block";

        if (isAuthenticated) {
            modalHeader.innerHTML = '<select id="eventTypeSelect"><option value="microsoft">Evento Microsoft</option><option value="sislaudo">Evento Sislaudo</option></select>';
            const eventTypeSelect = document.getElementById("eventTypeSelect");
            eventTypeSelect.addEventListener("change", toggleEventType);
        } else {
            modalHeader.innerHTML = '<h2>Evento Sislaudo</h2>';
        }

        const year = cell.getAttribute('data-year');
        const month = String(cell.getAttribute('data-month')).padStart(2, '0');
        const date = String(cell.getAttribute('data-date')).padStart(2, '0');
        const selectedDate = `${year}-${month}-${date}`;

        sislaudoContent.children.optionsTipos.children.dateParaSislaudo.value = selectedDate;

        clearModalContent();
        eventTitle.value = "";
        eventStartHour.value = "";
        eventEndHour.value = "";
        description.value = "";
        participantEmail.value = "";

        eventStartDate.value = selectedDate;
        eventEndDate.value = selectedDate;
        eventEndDate.setAttribute('min', selectedDate);

        eventStartDate.addEventListener('change', function() {
            const startDate = new Date(eventStartDate.value);
            const endDate = new Date(eventEndDate.value);

            if (startDate > endDate) {
                eventEndDate.value = eventStartDate.value;
            }

            eventEndDate.setAttribute('min', eventStartDate.value);
        });

        eventEndDate.addEventListener('change', function() {
            const startDate = new Date(eventStartDate.value);
            const endDate = new Date(eventEndDate.value);

            if (endDate < startDate) {
                eventStartDate.value = eventEndDate.value;
            }
        });

        if (isAuthenticated) {
            const rooms = await fetchRooms();
            populateRoomsSelect(rooms, "");

            const schedules = [userLoggedInEmail.mail];
            const selectedRoomEmail = eventRoomSelect.value;
            if (selectedRoomEmail) {
                schedules.push(selectedRoomEmail);
            }

            await updateAndDisplayConflicts(selectedDate, schedules);
            currentEvent = {};
            eventForm.onsubmit = handleCreateEventFormSubmit;
        }

        toggleEventType(); // Inicia com o tipo selecionado
    }

    // Função para alternar entre os tipos de evento
    function toggleEventType() {
        const eventTypeSelect = document.getElementById("eventTypeSelect");
        if (isAuthenticated && eventTypeSelect) {
            if (eventTypeSelect.value === "microsoft") {
                microsoftContent.style.display = "block";
                sislaudoContent.style.display = "none";
                prepareEventFormForMicrosoft();
            } else {
                microsoftContent.style.display = "none";
                sislaudoContent.style.display = "block";
                prepareEventFormForSislaudo();
            }
        } else {
            microsoftContent.style.display = "none";
            sislaudoContent.style.display = "block";
        }
    }

    // Limpa o conteúdo do modal
    function clearModalContent() {
        eventTitle.value = "";
        eventStartHour.value = "";
        eventEndHour.value = "";
        description.value = "";
        participantEmail.value = "";
        eventStartDate.value = "";
        eventEndDate.value = "";
    }

    function prepareEventFormForMicrosoft() {
        //verifica se há algum dia selecionado
        var dataSelecionada = document.querySelector('.selected');
        if (dataSelecionada) {
            var data = dataSelecionada.getAttribute('data-date').padStart(2, '0');
            var mes = dataSelecionada.getAttribute('data-month').padStart(2, '0');
            var ano = dataSelecionada.getAttribute('data-year');
            var today = ano + '-' + mes + '-' + data;
            eventStartDate.value = today;
            eventEndDate.value = today;
        }else{
            const today = new Date().toISOString().split('T')[0];
            if (eventStartDate.value === "") {
                eventStartDate.value = today;
            }
            if (eventEndDate.value === "") {
                eventEndDate.value = today;
            }
        }

        
        // eventEndDate.setAttribute('min', today);

        eventStartDate.addEventListener('change', function() {
            const startDate = new Date(eventStartDate.value);
            const endDate = new Date(eventEndDate.value);

            if (startDate > endDate) {
                eventEndDate.value = eventStartDate.value;
            }

            eventEndDate.setAttribute('min', eventStartDate.value);
        });

        eventEndDate.addEventListener('change', function() {
            const startDate = new Date(eventStartDate.value);
            const endDate = new Date(eventEndDate.value);

            if (endDate < startDate) {
                eventStartDate.value = eventEndDate.value;
            }
        });

        if (isAuthenticated) {
            fetchRooms().then(rooms => populateRoomsSelect(rooms, ""));

            const schedules = [userLoggedInEmail.mail];
            const selectedRoomEmail = eventRoomSelect.value;
            if (selectedRoomEmail) {
                schedules.push(selectedRoomEmail);
            }

            updateAndDisplayConflicts(today, schedules);
            currentEvent = {};
            eventForm.onsubmit = handleCreateEventFormSubmit;
        }
    }

    function prepareEventFormForSislaudo(){
        eventForm.onsubmit = handleCreateEventSislaudoFormSubmit;
    }

    function populateRoomsSelect(rooms, selectedRoom) {
        eventRoomSelect.innerHTML = `<option value="">Nenhuma sala</option>`;
        rooms.forEach((room) => {
            const option = document.createElement("option");
            option.value = room.emailAddress;
            option.text = room.displayName;
            if (room.displayName === selectedRoom) {
                option.selected = true;
            }
            eventRoomSelect.appendChild(option);
        });
    }

    [discardButton, closeModal].forEach((element) => {
        element.onclick = function() {
            eventModal.style.display = "none";
        };
    });

    window.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
            eventModal.style.display = "none";
        }
    });

    const expandButton = document.getElementById("expandButton");
    const collapsibleElements = document.querySelectorAll(".collapsible");

    collapsibleElements.forEach((element) => {
        element.classList.remove("expanded");
    });

    expandButton.onclick = function() {
        collapsibleElements.forEach((element) => {
            element.classList.toggle("expanded");
        });

        const icon = expandButton.querySelector("i");
        if (icon.classList.contains("fa-expand")) {
            icon.classList.remove("fa-expand");
            icon.classList.add("fa-compress");
        } else {
            icon.classList.remove("fa-compress");
            icon.classList.add("fa-expand");
        }
    };

    closeModal.onclick = function() {
        eventModal.style.display = "none";
    };

    teamsMeetingCheckbox.addEventListener("change", function() {
        if (teamsMeetingCheckbox.checked) {
            inPersonMeetingCheckbox.checked = false;
        } else {
            inPersonMeetingCheckbox.checked = true;
        }
    });

    inPersonMeetingCheckbox.addEventListener("change", function() {
        if (inPersonMeetingCheckbox.checked) {
            teamsMeetingCheckbox.checked = false;
        } else {
            teamsMeetingCheckbox.checked = true;
        }
    });

    // Adiciona ouvintes de evento aos botões para abrir os modais
    scheduleMeetingBtn.addEventListener("click", openMicrosoftEventModalFromButton);
 </script>