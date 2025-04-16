<!-- Modal de Visualização de Evento -->
<div id="eventDetailsModalOrganizer" class="modal view-modal">
    <div class="modal-content">
        <div class="modal-header view-header">
            <span class="viewEventTitle" id="viewEventTitle"></span>
            <div class="btn-group">
                <span class="close" id="closeViewButton"><i class="fas fa-times"></i></span>
            </div>
        </div>
        <div class="modal-body">
            <!-- Data deve ser nesse formato Quinta, 04/07/2024 08:30 - 09:30 -->
            <div class="container-event-time">
                <div class="time-event">
                    <i class="far fa-calendar-alt"></i>
                    <span id="viewEventDate"></span>
                </div>
            </div>
            <!-- Localização -->
            <div class="container-location" id="viewLocationContainer">
                <div class="container-event-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <span id="viewEventLocation"></span>
                </div>

                <div class="container-btn-team">
                    <button class="btn-join-team" id="joinTeamsMeeting">
                        <i class="fas fa-video"></i>
                        Entrar na Reunião
                    </button>
                </div>
            </div>
            <!-- Organizador -->
            <div class="container-event-organizer">
                <span class="organizer">Organizador</span>
                <div class="organizer-email">
                    <i class="fas fa-user"></i>
                    <span id="viewEventOrganizer"></span>
                </div>
            </div>
            <!-- Não Responderam -->
            <div class="container-event-no-response" id="viewNoResponseContainer">
                <span class="no-response">Não Responderam</span>
                <div class="container-no-response-list">
                    <i class="fas fa-user-slash"></i>
                    <ul id="viewNoResponseList" class="no-response-item"></ul>
                </div>
            </div>

            <!-- Aceitaram -->
            <div class="container-event-response" id="viewAcceptedListContainer">
                <span class="response">Responderam</span>
                <div class="container-response-list">
                    <i class="fas fa-user-check"></i>
                    <ul id="viewAcceptedList" class="response-item"></ul>
                </div>
            </div>

            <!-- Container dos botoes de resposta da reuniao -->
            <div class="container-event-buttons" id="viewResponseButtons">
                <span class="response-buttons">Responder</span>
                <div id="userResponseContainer" class="container-btn-change-response">
                    <span id="userResponseText"></span>
                    <button class="btn-change-response" id="changeResponseButton">Alterar</button>
                </div>
                <div class="button-response-group">
                    <button class="btn-accept" id="acceptEventButton">
                        <i class="fas fa-check"></i>
                        Sim
                    </button>
                    <button class="btn-tentative" id="tentativeEventButton">
                        <i class="fas fa-question"></i>
                        Talvez
                    </button>
                    <button class="btn-decline" id="declineEventButton">
                        <i class="fas fa-times"></i>
                        Recusar
                    </button>
                </div>
            </div>
            <div class="button-edit-modal" id="viewEditButtons">
                <button type="button" class="btn-delete" id="deleteEventButton">
                    <i class="fas fa-trash"></i>
                    Excluir
                </button>
                <button type="button" class="btn-edit" id="editEventButton">
                    <i class="fas fa-edit"></i>
                    Editar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentEvent = {};

    const eventDetailsModal = document.getElementById('eventDetailsModalOrganizer');
    const editEventButton = document.getElementById('editEventButton');
    const editModal = document.getElementById("eventModal");

    const viewEventTitle = document.getElementById('viewEventTitle');
    const viewEventDate = document.getElementById('viewEventDate');
    const viewEventLocation = document.getElementById('viewEventLocation');
    const viewEventOrganizer = document.getElementById('viewEventOrganizer');
    const joinTeamsMeeting = document.getElementById('joinTeamsMeeting');
    const viewNoResponseList = document.getElementById('viewNoResponseList');
    const viewAcceptedList = document.getElementById('viewAcceptedList');
    const closeViewButton = document.getElementById('closeViewButton');
    const deleteEventButton = document.getElementById("deleteEventButton");
    const acceptEventButton = document.getElementById('acceptEventButton');
    const tentativeEventButton = document.getElementById('tentativeEventButton');
    const declineEventButton = document.getElementById('declineEventButton');
    const viewJoinTeamsButton = document.getElementById("joinTeamsMeeting");

    const modalHeader = document.getElementById("modalTitle");
    const eventTitle = document.getElementById("eventTitle");
    const eventStartDate = document.getElementById("eventStartDate");
    const eventEndDate = document.getElementById("eventEndDate");
    const eventStartHour = document.getElementById("eventStartHour");
    const eventEndHour = document.getElementById("eventEndHour");
    const participantElements = document.querySelectorAll("#selectedParticipants .participant-email");

    const eventRoomSelect = document.getElementById("eventRoom");
    const eventConflicts = document.getElementById("eventConflicts");
    const containerEventConflicts = document.getElementById("conflictsContainer");

    const microsoftContent = document.getElementById("microsoftContent");
    const sislaudoContent = document.getElementById("sislaudoContent");

    deleteEventButton.onclick = async function() {
        const eventId = this.getAttribute('data-event-id');
        const deletedEventData = await deleteEvent(eventId);
        if (deletedEventData) {
            eventDetailsModal.style.display = "none";
            const events = await fetchLatestEvents();
            const eventUpdate = new CustomEvent("eventsUpdated", {
                detail: {
                    events
                },
            });
            document.dispatchEvent(eventUpdate);
        }
    };

    // Função para lidar com o envio do formulário de edição de evento
    async function handleEditEventFormSubmit(event) {
        event.preventDefault();

        const updatedEvent = prepareUpdateEvent(currentEvent);

        // Verificar se há alguma alteração antes de enviar a atualização
        if (Object.keys(updatedEvent).length === 0) {
            console.log("Nenhuma alteração detectada");
            return;
        }



        const updatedEventData = await updateEvent(currentEvent.id, updatedEvent);

        if (updatedEventData) {
            document.getElementById("eventModal").style.display = "none";
            const events = await fetchLatestEvents();
            const eventUpdate = new CustomEvent("eventsUpdated", {
                detail: {
                    events
                },
            });
            document.dispatchEvent(eventUpdate);
        }
    };
    async function openEditEventModal(eventId) {
        // Limpa o conteúdo anterior do modal
        clearModalContent();

        const eventDetails = await fetchEventDetails(eventId);

        if (eventDetails) {
            modalHeader.innerText = "Editar Evento";
            // Mostrar apenas o conteúdo do evento Microsoft
            microsoftContent.style.display = "block";
            sislaudoContent.style.display = "none";

            // Usar as informações extraídas do eventDetails
            eventTitle.value = eventDetails.subject || "";
            eventStartDate.value = eventDetails.startDate || "";
            eventEndDate.value = eventDetails.endDate || "";
            eventStartHour.value = eventDetails.startTime || "";
            eventEndHour.value = eventDetails.endTime || "";

            const rooms = await fetchRooms();
            populateRoomsSelect(rooms, eventDetails.location);

            const schedules = [userLoggedInEmail.mail];
            const eventRoomSelect = document.getElementById("eventRoom");

            const selectedRoomEmail = eventRoomSelect.value;
            if (selectedRoomEmail) {
                schedules.push(selectedRoomEmail);
            }

            await updateAndDisplayConflicts(eventDetails.startDate, schedules);

            // Adicionar participantes existentes à lista
            const selectedParticipantsList = document.getElementById("selectedParticipants");
            selectedParticipantsList.innerHTML = "";
            eventDetails.attendees.forEach(attendee => {
                if (attendee.email) {
                    addParticipant(attendee.email);
                }
            });

            currentEvent = {
                id: eventDetails.id,
                subject: eventDetails.subject,
                start: {
                    dateTime: eventDetails.startDateTime,
                },
                end: {
                    dateTime: eventDetails.endDateTime,
                },
                location: {
                    displayName: eventDetails.location,
                },
                body: {
                    content: eventDetails.description,
                },
                attendees: eventDetails.attendees.map(attendee => ({
                    emailAddress: {
                        address: attendee.email,
                        name: attendee.name,
                    },
                    status: attendee.status,
                })),
                isOnlineMeeting: eventDetails.isOnlineMeeting,
                onlineMeetingUrl: eventDetails.onlineMeetingUrl,
            };

            console.log(currentEvent, "Evento atual");

            editModal.style.display = "block"; // Abre o modal de edição
            eventForm.onsubmit = handleEditEventFormSubmit;
        }
    }

    function clearModalContent() {
        eventTitle.value = "";
        eventStartDate.value = "";
        eventEndDate.value = "";
        eventStartHour.value = "";
        eventEndHour.value = "";
        eventRoomSelect.innerHTML = '<option value="none">Nenhuma sala</option>';
        document.getElementById("selectedParticipants").innerHTML = "";
        containerEventConflicts.style.display = "none";
        eventConflicts.innerHTML = "";
    }

    // Função para exibir o modal com os detalhes do evento da Microsoft
    function showEventDetailsModalMicrosoft(eventId) {
        fetchEventDetails(eventId).then(event => {
                if (event) {
                    displayEventDetails(event);
                    document.getElementById('deleteEventButton').setAttribute('data-event-id', eventId);
                }
            })
            .catch(err => console.error(err));
    }

    const userResponseContainer = document.getElementById('userResponseContainer');
    const userResponseText = document.getElementById('userResponseText');
    const changeResponseButton = document.getElementById('changeResponseButton');
    const responseButtonsGroup = document.querySelector('.button-response-group');

    // Função para exibir os detalhes do evento no modal
    function handleUserResponse(userResponse) {
        if (userResponse && userResponse.status !== "none") {
            let responseText = "";
            switch (userResponse.status) {
                case "accepted":
                    responseText = "Sim, comparecerei";
                    break;
                case "tentativelyAccepted":
                    responseText = "Talvez, eu compareça";
                    break;
                case "declined":
                    responseText = "Recusei o convite";
                    break;
                default:
                    responseText = "Nenhuma resposta";
            }
            userResponseText.innerText = responseText;
            responseButtonsGroup.style.display = "none";
            userResponseContainer.style.display = "flex";
        } else {
            userResponseText.innerText = "";
            responseButtonsGroup.style.display = "flex";
            userResponseContainer.style.display = "none";
        }
    }

    async function displayEventDetails(eventDetails) {
        // Atualiza o evento atual
        currentEvent = eventDetails;

        // Atualiza os campos de detalhes do evento
        viewEventTitle.innerText = eventDetails.subject || "";
        viewEventDate.innerText = formatEventDate(eventDetails);
        viewEventLocation.innerText = eventDetails.location || "";
        viewEventOrganizer.innerText = eventDetails.organizer.email || "";
        viewJoinTeamsButton.href = eventDetails.onlineMeetingUrl;

        viewJoinTeamsButton.style.display = eventDetails.isOnlineMeeting ? "block" : "none";
        const hasAttendees = eventDetails.attendees;
        viewNoResponseContainer.style.display = hasAttendees.some(attendee => attendee.status === "none") ? "flex" : "none";
        viewAcceptedListContainer.style.display = hasAttendees.some(attendee => attendee.status === "accepted") ? "flex" : "none";
        const userResponse = eventDetails.attendees.find(attendee => attendee.email === userLoggedInEmail.mail);

        handleUserResponse(userResponse);

        changeResponseButton.onclick = () => {
            responseButtonsGroup.style.display = "flex";
            userResponseContainer.style.display = "none";
        };

        // Exibe ou oculta os botões de resposta e edição baseado no status de organizador
        if (eventDetails.isOrganizer) {
            viewResponseButtons.style.display = "none";
            viewEditButtons.style.display = "flex";
        } else {
            viewResponseButtons.style.display = "flex";
            viewEditButtons.style.display = "none";
        }

        // Filtra os participantes que não responderam
        const noResponseList = eventDetails.attendees.filter(attendee => attendee.status === "none").map(attendee => `<li>${attendee.email}</li>`).join("");
        viewNoResponseList.innerHTML = noResponseList;

        // Filtra os participantes que aceitaram
        const acceptedList = eventDetails.attendees.filter(attendee => attendee.status === "accepted").map(attendee => `<li>${attendee.email}</li>`).join("");
        viewAcceptedList.innerHTML = acceptedList;

        // Adiciona evento ao botão de editar para abrir o modal de edição
        editEventButton.onclick = () => {
            eventDetailsModal.style.display = "none";
            openEditEventModal(eventDetails.id);
        };

        // Exibe o modal de visualização
        eventDetailsModal.style.display = "block";
    }

    async function respondToEvent(eventId, action) {
        const url = `https://graph.microsoft.com/v1.0/me/events/${eventId}/${action}`;

        const responseBody = {
            comment: "",
            sendResponse: true
        };

        const result = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${access_token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(responseBody)
        });

        if (result.ok) {
            console.log("Resposta enviada com sucesso");
            // Atualize a exibição dos detalhes do evento
            showEventDetailsModalMicrosoft(eventId);
            eventDetailsModalOrganizer.style.display = 'none'
        } else {
            console.error("Erro ao enviar a resposta");
        }
    }

    acceptEventButton.onclick = () => respondToEvent(currentEvent.id, 'accept');
    tentativeEventButton.onclick = () => respondToEvent(currentEvent.id, 'tentativelyAccept');
    declineEventButton.onclick = () => respondToEvent(currentEvent.id, 'decline');


    viewJoinTeamsButton.addEventListener('click', function() {
        window.open(viewJoinTeamsButton.href, "_blank")
    })

    // Fechar o modal ao clicar fora dele
    window.addEventListener('click', function(event) {
        if (event.target === eventDetailsModalOrganizer) {
            eventDetailsModalOrganizer.style.display = 'none';
        }
    });

    closeViewButton.onclick = function() {
        eventDetailsModal.style.display = "none";
    };

    // Fechar o modal ao pressionar a tecla ESC
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            eventDetailsModalOrganizer.style.display = 'none';
        }
    });

    document.querySelectorAll('.modal-body').forEach(container => {
        const visibleItems = Array.from(container.querySelectorAll('.container-event-visualization')).filter(item => {
            return window.getComputedStyle(item).display !== 'none';
        });

        visibleItems.forEach((item, index) => {
            if (index === visibleItems.length - 1) {
                item.style.borderBottom = 'none';
            } else {
                item.style.borderBottom = '1px solid #E8E8E8';
            }
        });
    });

    // Sincronizar as datas inicial e final
    eventStartDate.addEventListener("change", function() {
        const startDate = eventStartDate.value;
        eventEndDate.value = startDate;
        clearTimeInputs();
    });

    eventEndDate.addEventListener("change", function() {
        const endDate = eventEndDate.value;
        eventStartDate.value = endDate;
        clearTimeInputs();
    });

    eventRoomSelect.addEventListener("change", function() {
        clearTimeInputs();
    });
</script>