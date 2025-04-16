<script>
  // Função para criar um novo evento no Microsoft Graph
  async function createEvent(newEvent) {
    try {
      const response = await fetch("https://graph.microsoft.com/v1.0/me/events", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${access_token}`,
          "Content-Type": "application/json",
          Prefer: 'outlook.timezone="E. South America Standard Time"',
        },
        body: JSON.stringify(newEvent),
      });

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error.message);
      }

      return await response.json();
    } catch (error) {
      console.error("Erro ao criar o evento:", error);
      return null;
    }
  };

  // Função para preparar o objeto de novo evento
  async function prepareNewEvent() {
    const newEvent = {};

    const eventTitle = document.getElementById("eventTitle").value;
    const eventStartDate = document.getElementById("eventStartDate").value;
    const eventEndDate = document.getElementById("eventEndDate").value;
    const eventStartHour = document.getElementById("eventStartHour").value;
    const eventEndHour = document.getElementById("eventEndHour").value;
    const eventRoomSelect = document.getElementById("eventRoom");
    const selectedRoom = eventRoomSelect.options[eventRoomSelect.selectedIndex].text;
    const selectedRoomEmail = eventRoomSelect.value;
    const description = document.getElementById("editEventDescription").value;
    const participantElements = document.querySelectorAll("#selectedParticipants .participant-email");

    const teamsMeeting = document.getElementById("teamsMeeting").checked;

    newEvent.subject = eventTitle;

    // Valida dirença entre data de inicio e fim
    if (new Date(eventStartDate) > new Date(eventEndDate)) {
      alert("A data de início não pode ser maior que a data de fim.");
      return null;
    }

    // Valida se a reunião tem duração de mais de 5 minutos
    if (eventStartHour && eventEndHour) {
      const start = new Date(`${eventStartDate}T${eventStartHour}:00`);
      const end = new Date(`${eventEndDate}T${eventEndHour}:00`);
      const diff = (end - start) / 60000;
      if (diff < 5) {
        alert("A reunião deve ter duração de no mínimo 5 minutos.");
        return null;
      }
    }

    //Valida se existe outra reunião no mesmo horário na sala selecionada
    if (selectedRoom !== "Nenhuma sala") {
      conflicts = await fetchEventConflicts(
        `${eventStartDate}T${eventStartHour}:00`,
        `${eventEndDate}T${eventEndHour}:00`,
        [selectedRoomEmail]
      );

      if (conflicts.length > 0) {
        // Apaga horario de inicio e fim
        document.getElementById("eventStartHour").value = "";
        document.getElementById("eventEndHour").value = "";
        alert("Já existe uma reunião marcada para esse horário nesta sala.");
        return null;
      }
    }

    const startDateTime = `${eventStartDate}T${eventStartHour}:00`;
    newEvent.start = {
      dateTime: startDateTime,
      timeZone: "E. South America Standard Time",
    };

    const endDateTime = `${eventEndDate}T${eventEndHour}:00`;
    newEvent.end = {
      dateTime: endDateTime,
      timeZone: "E. South America Standard Time",
    };

    if (selectedRoom !== "Nenhuma sala") {
      newEvent.location = {
        displayName: selectedRoom,
        locationType: "default",
        uniqueId: selectedRoomEmail,
        uniqueIdType: "private",
      };
      newEvent.attendees = newEvent.attendees || [];
      newEvent.attendees.push({
        emailAddress: {
          address: selectedRoomEmail
        },
        type: "resource",
      });
    }

    newEvent.body = {
      contentType: "HTML",
      content: description,
    };

    newEvent.attendees = newEvent.attendees || [];
    participantElements.forEach(element => {
      const email = element.textContent.trim();
      if (email) {
        newEvent.attendees.push({
          emailAddress: {
            address: email
          },
          type: "required",
        });
      }
    });

    if (teamsMeeting) {
      newEvent.isOnlineMeeting = true;
      newEvent.onlineMeetingProvider = "teamsForBusiness";
    }

    return newEvent;
  }

  // Função para lidar com o envio do formulário de criação de evento
  async function handleCreateEventFormSubmit(event) {
    event.preventDefault();

    const newEvent = await prepareNewEvent();

    if (!newEvent) {
      return;
    }

    const createdEventData = await createEvent(newEvent);

    if (createdEventData) {
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

  function handleCreateEventSislaudoFormSubmit(event){
    event.preventDefault();
    
  }
</script>