<script>
    function capitalizeFirstLetter(string) {
        return `${string.charAt(0).toUpperCase()}${string.slice(1)}`;
    }

    function formatEventDate(event) {
        const start = new Date(event.startDateTime);
        const end = new Date(event.endDateTime);

        const optionsDate = {
            weekday: "long",
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        };

        const optionsTime = {
            hour: "2-digit",
            minute: "2-digit",
        };

        const startDate = capitalizeFirstLetter(
            start.toLocaleDateString("pt-BR", optionsDate)
        );
        const startTime = start.toLocaleTimeString("pt-BR", optionsTime);
        const endTime = end.toLocaleTimeString("pt-BR", optionsTime);

        return `${startDate} ${startTime} - ${endTime}`;
    }

    function formatDateTime(dateTime) {
        const [date, time] = dateTime.split("T");
        return {
            date,
            time: time.substring(0, 5),
        };
    };

    /* Formatação de data do calendario */
    function formatDate(date, format) {
        const d = new Date(date);
        const day = (`0${d.getDate()}`).slice(-2);
        const month = (`0${d.getMonth() + 1}`).slice(-2);
        const year = d.getFullYear();

        return format === 'yyyy-mm-dd' ? `${year}-${month}-${day}` : `${day}/${month}/${year}`;
    }

    /* Formatação de duração do evento */
    function calculateDuration(start, end) {
        const startTime = new Date(start);
        const endTime = new Date(end);
        const durationInMinutes = (endTime - startTime) / 60000; // Diferença em minutos
        if (durationInMinutes < 60) {
            return `${durationInMinutes} min`;
        } else {
            const hours = Math.floor(durationInMinutes / 60);
            const minutes = durationInMinutes % 60;
            return hours === 0 ? `${minutes}min` : `${hours}hr${hours > 1 ? "s" : ""} ${minutes}min`;
        }
    };
</script>