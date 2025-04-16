<script>
    /* Seleção de horários */
    const startTimeOptions = document.getElementById("startTimeOptions");
    const endTimeOptions = document.getElementById("endTimeOptions");
    const eventRoomSelect = document.getElementById("eventRoom");
    const containerEventConflicts = document.getElementById("conflictsContainer");
    const eventConflicts = document.getElementById("eventConflicts");

    let selectedStartTime = null;
    let conflicts = [];

    async function updateAndDisplayConflicts(date, schedules) {
        const startDateTime = `${date}T06:00:00`;
        const endDateTime = `${date}T23:59:59`;

        conflicts = await fetchEventConflicts(
            startDateTime,
            endDateTime,
            schedules
        );
        const formattedConflicts = conflicts.map((conflict) => ({
            start: conflict.startTime,
            end: conflict.endTime,
            subject: conflict.subject,
            location: conflict.location,
        }));

        conflicts = formattedConflicts;
        displayEventConflicts(formattedConflicts);
    }

    function displayEventConflicts(conflicts) {
        if (conflicts.length > 0) {
            containerEventConflicts.style.display = "flex";
            eventConflicts.innerHTML = conflicts
                .map(
                    (conflict) => `
            <div class="conflict-item">
              <div class="conflict-time">${conflict.start} - ${conflict.end}</div>
              <div class="conflict-title">${conflict.subject}</div>
              <div class="conflict-room">${conflict.location}</div>
            </div>
          `
                )
                .join("");
        } else {
            containerEventConflicts.style.display = "none";
        }
    };

    function showTimeOptions(type) {
        const timeOptions = document.getElementById(`${type}TimeOptions`);
        const otherTimeOptions =
            type === "start" ? endTimeOptions : startTimeOptions;

        otherTimeOptions.style.display = "none";
        const input = document.getElementById(
            `event${capitalizeFirstLetter(type)}Hour`
        );
        timeOptions.style.display = "block";
        timeOptions.style.top = `${input.offsetTop + input.offsetHeight}px`;
        timeOptions.style.left = `${input.offsetLeft}px`;
        timeOptions.style.width = `${input.offsetWidth}px`;

        const times = generateTimeOptions(type);
        timeOptions.innerHTML = "";
        times.forEach((time) => {
            const timeDiv = document.createElement("div");
            if (type === "end") {
                const duration = calcDuration(selectedStartTime, time);
                timeDiv.innerHTML = `<span class="time-option">${time}</span> <span class="duration-option">${duration}</span>`;
            } else {
                timeDiv.textContent = time;
            }
            timeDiv.addEventListener("click", () => selectTime(type, time));
            timeOptions.appendChild(timeDiv);
        });
    };

    function selectTime(type, time) {
        const input = document.getElementById(
            `event${capitalizeFirstLetter(type)}Hour`
        );
        input.value = time;
        if (type === "start") {
            selectedStartTime = time;
            eventEndHour.value = "";
            hideTimeOptions("end");
        }
        hideTimeOptions(type);
    };

    function hideTimeOptions(type) {
        const timeOptions = document.getElementById(`${type}TimeOptions`);
        timeOptions.style.display = "none";
    };

    function capitalizeFirstLetter(string) {
        return `${string.charAt(0).toUpperCase()}${string.slice(1)}`;
    };

    function generateTimeOptions(type) {
        const times = [];
        const occupiedPeriods = conflicts.map((conflict) => ({
            start: conflict.start,
            end: conflict.end,
        }));

        for (let i = 6; i < 24; i++) {
            for (let j = 0; j < 60; j += 5) {
                const hour = String(i).padStart(2, "0");
                const minute = String(j).padStart(2, "0");
                const time = `${hour}:${minute}`;

                if (type === "start") {
                    if (
                        !occupiedPeriods.some((period) =>
                            isTimeWithinPeriod(time, period.start, period.end)
                        )
                    ) {
                        times.push(time);
                    }
                } else if (type === "end" && selectedStartTime) {
                    const [startHour, startMinute] = selectedStartTime
                        .split(":")
                        .map(Number);
                    const startTimeMinutes = startHour * 60 + startMinute;
                    const currentTimeMinutes = i * 60 + j;

                    const nextEventStart = occupiedPeriods
                        .filter((period) => {
                            const [periodStartHour, periodStartMinute] = period.start
                                .split(":")
                                .map(Number);
                            return (
                                periodStartHour * 60 + periodStartMinute > startTimeMinutes
                            );
                        })
                        .reduce((min, period) => {
                            const [periodStartHour, periodStartMinute] = period.start
                                .split(":")
                                .map(Number);
                            const periodStartTimeMinutes =
                                periodStartHour * 60 + periodStartMinute;
                            return periodStartTimeMinutes < min ?
                                periodStartTimeMinutes :
                                min;
                        }, 24 * 60);

                    if (
                        currentTimeMinutes > startTimeMinutes &&
                        currentTimeMinutes <= nextEventStart
                    ) {
                        times.push(time);
                    }
                }
            }
        }
        return times;
    };

    function isTimeWithinPeriod(time, periodStart, periodEnd) {
        const [hour, minute] = time.split(":").map(Number);
        const [startHour, startMinute] = periodStart.split(":").map(Number);
        const [endHour, endMinute] = periodEnd.split(":").map(Number);

        const timeMinutes = hour * 60 + minute;
        const startMinutes = startHour * 60 + startMinute;
        const endMinutes = endHour * 60 + endMinute;

        return timeMinutes >= startMinutes && timeMinutes < endMinutes;
    };

    function calcDuration(start, end) {
        const [startHour, startMinute] = start.split(":").map(Number);
        const [endHour, endMinute] = end.split(":").map(Number);

        const startTimeMinutes = startHour * 60 + startMinute;
        const endTimeMinutes = endHour * 60 + endMinute;

        const durationMinutes = endTimeMinutes - startTimeMinutes;

        const hours = Math.floor(durationMinutes / 60);
        const minutes = durationMinutes % 60;

        return hours === 0 ? `${minutes}min` : `${hours}hr${hours > 1 ? "s" : ""} ${minutes}min`;
    };

    function filterAndDisplayOptions(type) {
        const input = document.getElementById(
            `event${capitalizeFirstLetter(type)}Hour`
        );
        const filterValue = input.value.replace(/:/g, "");
        const filteredTimes = generateTimeOptions(type).filter((time) =>
            time.replace(/:/g, "").startsWith(filterValue)
        );

        displayTimeOptions(type, filteredTimes);
    };

    function displayTimeOptions(type, times) {
        const timeOptions = document.getElementById(`${type}TimeOptions`);
        timeOptions.innerHTML = "";
        times.forEach((time) => {
            const timeDiv = document.createElement("div");
            if (type === "end") {
                const duration = calcDuration(selectedStartTime, time);
                timeDiv.innerHTML = `<span class="time-option">${time}</span> <span class="duration-option">${duration}</span>`;
            } else {
                timeDiv.textContent = time;
            }
            timeDiv.addEventListener("click", () => selectTime(type, time));
            timeOptions.appendChild(timeDiv);
        });
    };

    function applyTimeMask(event) {
        let value = event.target.value.replace(/\D/g, "");
        if (value.length > 4) {
            value = value.substring(0, 4);
        }
        let formattedValue = value;
        if (value.length > 2) {
            formattedValue = `${value.substring(0, 2)}:${value.substring(2, 4)}`;
        }
        event.target.value = formattedValue;
        filterAndDisplayOptions(
            event.target.id.includes("Start") ? "start" : "end"
        );
    };

    if (eventStartHour && eventEndHour) {
        eventStartHour.addEventListener("input", applyTimeMask);
        eventEndHour.addEventListener("input", applyTimeMask);
    }

    if (eventStartHour && eventEndHour) {
        eventStartHour.addEventListener("focus", () => showTimeOptions("start"));
        eventEndHour.addEventListener("focus", () => showTimeOptions("end"));
    } else {
        console.error(
            "Os elementos eventStartHour ou eventEndHour não foram encontrados no DOM."
        );
    }

    document.addEventListener("click", function(event) {
        if (
            !event.target.closest("#eventStartHour") &&
            !event.target.closest("#startTimeOptions")
        ) {
            startTimeOptions.style.display = "none";
        }

        if (
            !event.target.closest("#eventEndHour") &&
            !event.target.closest("#endTimeOptions")
        ) {
            endTimeOptions.style.display = "none";
        }
    });
    // Função para limpar os campos de hora inicial e final
    function clearTimeInputs() {
        eventStartHour.value = "";
        eventEndHour.value = "";
    }

    // Adicionando os eventos de mudança nos campos de data e sala
    const scheduleFields = [eventStartDate, eventEndDate, eventRoomSelect];
    scheduleFields.forEach((field) => {
        field.addEventListener("change", async () => {
            const selectedRoomEmail = eventRoomSelect.value;
            const schedules = [userLoggedInEmail.mail];
            if (selectedRoomEmail) {
                schedules.push(selectedRoomEmail);
            }
            await updateAndDisplayConflicts(eventStartDate.value, schedules);
            clearTimeInputs(); // Limpar os campos de hora
        });
    })
</script>