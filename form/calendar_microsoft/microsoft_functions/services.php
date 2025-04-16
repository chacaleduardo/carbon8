<script>
  let cachedUserEmail = null;

  async function fetchAuthenticatedUser() {
    if (cachedUserEmail) {
      return cachedUserEmail;
    }

    try {
      const response = await fetch("https://graph.microsoft.com/v1.0/me", {
        method: "GET",
        headers: {
          Authorization: `Bearer ${access_token}`,
          "Content-Type": "application/json",
        },
      });

      if (response.ok) {
        const user = await response.json();
        console.log('Usuário autenticado:', user.mail);
        cachedUserEmail = user.mail;
        return cachedUserEmail;
      } else {
        const error = await response.json();
        console.error("Erro ao buscar usuário autenticado:", error);
        return null;
      }
    } catch (error) {
      console.error("Erro ao buscar usuário autenticado:", error);
      return null;
    }
  };

  async function fetchRooms() {
    try {
      const response = await fetch(
        "https://graph.microsoft.com/v1.0/places/microsoft.graph.room", {
          method: "GET",
          headers: {
            Authorization: `Bearer ${access_token}`,
            "Content-Type": "application/json",
            Prefer: 'outlook.timezone="E. South America Standard Time"',
          },
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error.message);
      }

      const data = await response.json();
      let rooms = [];
      if (data.value) {
        rooms = data.value.map((room) => ({
          id: room.id,
          displayName: room.displayName,
          emailAddress: room.emailAddress,
        }));
      }
      console.log("Data from fetchRooms:", rooms);
      return rooms.sort((a, b) => a.displayName.localeCompare(b.displayName));
    } catch (error) {
      console.error("Erro ao buscar salas de reunião:", error);
      return [];
    }
  };

  async function fetchEventDetails(eventId) {
    try {
      const response = await fetch(
        `https://graph.microsoft.com/v1.0/me/events/${eventId}`, {
          method: "GET",
          headers: {
            Authorization: `Bearer ${access_token}`,
            "Content-Type": "application/json",
            Prefer: 'outlook.timezone="E. South America Standard Time"',
          },
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error.message);
      }

      const eventDetails = await response.json();

      const {
        id,
        subject,
        bodyPreview: description,
        location: {
          displayName: location
        },
        start,
        end,
        organizer: {
          emailAddress: organizer
        },
        attendees,
        onlineMeeting,
        isOnlineMeeting,
        onlineMeetingProvider,
        isOrganizer,
      } = eventDetails;

      const startDateTime = formatDateTime(start.dateTime);
      const endDateTime = formatDateTime(end.dateTime);

      const eventInfo = {
        id,
        subject,
        description,
        location,
        startDate: startDateTime.date,
        startTime: startDateTime.time,
        endDate: endDateTime.date,
        endTime: endDateTime.time,
        startDateTime: start.dateTime,
        endDateTime: end.dateTime,
        organizer: {
          name: organizer.name,
          email: organizer.address,
        },
        attendees: attendees.map(({
          emailAddress,
          status
        }) => ({
          name: emailAddress.name,
          email: emailAddress.address,
          status: status.response,
        })),
        onlineMeetingUrl: onlineMeeting?.joinUrl,
        isOnlineMeeting,
        onlineMeetingProvider,
        isOrganizer,
      };

      return eventInfo;
      console.log("Data from fetchEventDetails:", eventInfo);
    } catch (error) {
      console.error("Erro ao buscar detalhes do evento:", error);
      return null;
    }
  };

  async function fetchRecurrentEventDetails(eventId,recstartDateTime, recendDateTime) {
    try {
      const response = await fetch(
        `https://graph.microsoft.com/v1.0/me/events/${eventId}/instances?startDateTime=${recstartDateTime}&endDateTime=${recendDateTime}&$top=1000`, {
          method: "GET",
          headers: {
            Authorization: `Bearer ${access_token}`,
            "Content-Type": "application/json",
            Prefer: 'outlook.timezone="E. South America Standard Time"',
          },
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error.message);
      }

      const eventDetails = await response.json();
      const eventInfo = []
      for (let index = 0; index < eventDetails.value.length; index++) {
        eventDetails.value[index];

        // const startDateTime = formatDateTime(start.dateTime);
        // const endDateTime = formatDateTime(end.dateTime);

        eventInfo.push(eventDetails.value[index]);
        
      }

      return eventInfo;
      console.log("Data from fetchEventDetails:", eventInfo);
    } catch (error) {
      console.error("Erro ao buscar detalhes do evento:", error);
      return null;
    }
  };

  async function createEvent(eventData) {
    try {
      const response = await fetch(
        "https://graph.microsoft.com/v1.0/places/microsoft.graph.room", {
          method: "GET",
          headers: {
            Authorization: `Bearer ${access_token}`,
            "Content-Type": "application/json",
            Prefer: 'outlook.timezone="E. South America Standard Time"',
          },
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error.message);
      }

      const data = await response.json();
      let rooms = [];
      if (data.value) {
        rooms = data.value.map((room) => ({
          id: room.id,
          displayName: room.displayName,
          emailAddress: room.emailAddress,
        }));
      }
      console.log("Data from fetchRooms:", rooms);
      return rooms.sort((a, b) => a.displayName.localeCompare(b.displayName));
    } catch (error) {
      console.error("Erro ao buscar salas de reunião:", error);
      return [];
    }
  };

  async function fetchEventConflicts(startDateTime, endDateTime, schedules) {
    try {
      const response = await fetch(
        "https://graph.microsoft.com/v1.0/me/calendar/getSchedule", {
          method: "POST",
          headers: {
            Authorization: `Bearer ${access_token}`,
            "Content-Type": "application/json",
            Prefer: 'outlook.timezone="E. South America Standard Time"',
          },
          body: JSON.stringify({
            schedules: schedules,
            startTime: {
              dateTime: startDateTime,
              timeZone: "E. South America Standard Time",
            },
            endTime: {
              dateTime: endDateTime,
              timeZone: "E. South America Standard Time",
            },
            availabilityViewInterval: 5,
          }),
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error.message);
      }

      const data = await response.json();

      if (data.value) {
        const conflicts = [];
        for (const schedule of data.value) {
          if (schedule.error) {
            throw new Error(schedule.error.message);
          }

          if (schedule.scheduleItems) {
            schedule.scheduleItems.forEach((item) => {
              if (item.status === "busy") {
                conflicts.push({
                  start: item.start.dateTime,
                  startTime: item.start.dateTime.split("T")[1].substring(0, 5),
                  end: item.end.dateTime,
                  endTime: item.end.dateTime.split("T")[1].substring(0, 5),
                  subject: item.subject,
                  location: item.location,
                });
              }
            });
          }
        }
        return conflicts;
      }

      throw new Error("Resposta da API não contém dados esperados");
    } catch (error) {
      console.error("Erro ao buscar conflitos de reunião:", error);
      return [];
    }
  };

  // Função para enviar a resposta ao evento
  async function sendResponseToEvent(eventId, response) {
    try {
      const responseResult = await fetch(
        `https://graph.microsoft.com/v1.0/me/events/${eventId}/${response}`, {
          method: "POST",
          headers: {
            Authorization: `Bearer ${access_token}`,
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            comment: "Resposta enviada pelo sislaudo",
            sendResponse: true,
            response: response,
          }),
        }
      );

      if (responseResult.ok) {
        await fetchEventDetails(eventId);
      } else {
        const error = await responseResult.json();
        console.error("Erro ao responder ao evento:", error);
        return "Erro ao responder ao evento";
      }
    } catch (error) {
      console.error("Erro ao responder ao evento:", error);
      return "Erro ao responder ao evento";
    }
  }

  async function deleteEvent(eventId) {
    try {
      const response = await fetch(
        `https://graph.microsoft.com/v1.0/me/events/${eventId}`, {
          method: "DELETE",
          headers: {
            Authorization: `Bearer ${access_token}`,
            "Content-Type": "application/json",
          },
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error.message);
      }

      return true;
    } catch (error) {
      console.error("Erro ao deletar o evento:", error);
      return false;
    }
  };
</script>