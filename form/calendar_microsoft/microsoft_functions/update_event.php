<script>
    // Função para atualizar um evento no Microsoft Graph
    async function updateEvent(eventId, updatedEvent) {
        try {
            const response = await fetch(
                `https://graph.microsoft.com/v1.0/me/events/${eventId}`, {
                    method: "PATCH",
                    headers: {
                        Authorization: `Bearer ${access_token}`,
                        "Content-Type": "application/json",
                        Prefer: 'outlook.timezone="E. South America Standard Time"',
                    },
                    body: JSON.stringify(updatedEvent),
                }
            );

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error.message);
            }

            return await response.json();
        } catch (error) {
            console.error("Erro ao atualizar o evento:", error);
            return null;
        }
    };

    // Função para preparar o objeto de evento atualizado
    function prepareUpdateEvent(currentEvent) {
        const updatedEvent = {};

        const eventTitle = document.getElementById("eventTitle").value;
        const eventStartDate = document.getElementById("eventStartDate").value;
        const eventEndDate = document.getElementById("eventEndDate").value;
        const eventStartHour = document.getElementById("eventStartHour").value;
        const eventEndHour = document.getElementById("eventEndHour").value;
        const eventRoomSelect = document.getElementById("eventRoom");
        const selectedRoom = eventRoomSelect.options[eventRoomSelect.selectedIndex].text;
        const selectedRoomEmail = eventRoomSelect.options[eventRoomSelect.selectedIndex].value;
        const description = document.getElementById("editEventDescription").value;

        if (eventTitle !== currentEvent.subject) {
            updatedEvent.subject = eventTitle;
        }

        const startDateTime = `${eventStartDate}T${eventStartHour}:00`;
        if (
            eventStartDate !== currentEvent.start.dateTime.split("T")[0] ||
            eventStartHour !== currentEvent.start.dateTime.split("T")[1].slice(0, 5)
        ) {
            updatedEvent.start = {
                dateTime: startDateTime,
                timeZone: "E. South America Standard Time",
            };
        }

        const endDateTime = `${eventEndDate}T${eventEndHour}:00`;
        if (
            eventEndDate !== currentEvent.end.dateTime.split("T")[0] ||
            eventEndHour !== currentEvent.end.dateTime.split("T")[1].slice(0, 5)
        ) {
            updatedEvent.end = {
                dateTime: endDateTime,
                timeZone: "E. South America Standard Time",
            };
        }

        // Remover qualquer attendee do tipo 'resource' antes de adicionar a nova sala
        let updatedAttendees = currentEvent.attendees.filter(attendee => attendee.type !== "resource");

        // Adicionar a nova sala, se selecionada
        if (selectedRoom !== "Nenhuma sala") {
            updatedEvent.location = {
                displayName: selectedRoom,
                locationType: "default",
                uniqueId: selectedRoomEmail,
                uniqueIdType: "private",
            };
            updatedAttendees.push({
                emailAddress: {
                    address: selectedRoomEmail
                },
                type: "resource",
            });
        } else {
            updatedEvent.location = {
                displayName: "",
                locationType: "default",
            };
        }

        if (selectedRoomEmail === "Nenhuma sala") {
            updatedEvent.attendees = updatedAttendees.map(attendee => ({
                emailAddress: attendee.emailAddress,
                type: attendee.type
            }));
        }


        if (description !== currentEvent.body.content) {
            updatedEvent.body = {
                contentType: "HTML",
                content: description,
            };
        }

        const participantElements = document.querySelectorAll("#selectedParticipants .participant-email");
        const currentParticipants = Array.from(participantElements).map(element => element.textContent.trim());
        const newParticipants = currentParticipants.filter(email => !currentEvent.attendees.some(attendee => attendee.emailAddress.address === email));
        const removedParticipants = currentEvent.attendees.filter(attendee => !currentParticipants.includes(attendee.emailAddress.address));

        newParticipants.forEach(email => {
            updatedAttendees.push({
                emailAddress: {
                    address: email
                },
                type: "required",
            });
        });

        updatedAttendees = updatedAttendees.filter(attendee => !removedParticipants.some(removed => removed.emailAddress.address === attendee.emailAddress.address));

        if (updatedAttendees.length !== currentEvent.attendees.length ||
            updatedAttendees.some((attendee, index) => attendee.emailAddress.address !== currentEvent.attendees[index].emailAddress.address)) {
            updatedEvent.attendees = updatedAttendees.map(attendee => ({
                emailAddress: attendee.emailAddress,
                type: attendee.type
            }));
        }

        const teamsMeeting = document.getElementById("teamsMeeting").checked;
        if (teamsMeeting !== currentEvent.isOnlineMeeting) {
            updatedEvent.isOnlineMeeting = teamsMeeting;
            updatedEvent.onlineMeetingProvider = teamsMeeting ? "teamsForBusiness" : null;
        }

        return updatedEvent;
    }
</script>