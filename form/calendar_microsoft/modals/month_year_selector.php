<body>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/pt-br.min.js"></script>

    <div id="monthYearSelectorModal" class="modal-selector-year">
        <div class="modal-content-selector">
            <div class="modal-header-select">
                <button id="prevYear" onclick="changeYear(-1)">
                    <img src="/form/calendar_microsoft/img/arrow-prev.svg" alt="Ano anterior">
                </button>
                <span id="modalYear" class="modal-year"></span>
                <button id="nextYear" onclick="changeYear(1)">
                    <img src="/form/calendar_microsoft/img/arrow-next.svg" alt="Ano próximo">
                </button>
                <span class="close" onclick="closeMonthYearSelector()">
                    <img src="/form/calendar_microsoft/img/close.svg" alt="fechar">
                </span>
            </div>
            <div id="monthGrid" class="month-grid"></div>
        </div>
    </div>
</body>


<script>
    const today = moment();
    let currentMonth = today.month();
    let currentYear = today.year();
    let firstDayOfWeek = moment().startOf('week').format('d');

    function openMonthYearSelector() {
        var monthYear = document.getElementById("monthYearSelectorModal");
        var year = document.getElementById("modalYear");
        monthYear.style.display = 'block';
        year.innerText = currentYear;
        generateMonthGrid();
    }

    function closeMonthYearSelector() {
        var monthYear = document.getElementById("monthYearSelectorModal");
        monthYear.style.display = 'none';
    }

    function changeYear(direction) {
        var modalYear = document.getElementById("modalYear");
        currentYear = parseInt(modalYear.textContent) + direction;

        modalYear.classList.add('fade-out');

        setTimeout(function() {
            modalYear.textContent = currentYear;

            modalYear.classList.remove('fade-out');
            modalYear.classList.add('fade-in');

            setTimeout(function() {
                modalYear.classList.remove('fade-in');
            }, 300);
        }, 300);
    }

    function selectMonth(month) {
        currentMonth = month;
        showCalendar(currentMonth, currentYear);
        closeMonthYearSelector();
    }

    function generateMonthGrid() {
        const monthGrid = document.getElementById("monthGrid");
        monthGrid.innerHTML = "";
        for (let i = 0; i < 12; i++) {
            let monthName = moment().month(i).format('MMM');
            monthName = monthName.charAt(0).toUpperCase() + monthName.slice(1);
            const monthDiv = document.createElement("div");
            monthDiv.className = "month-item";
            monthDiv.innerText = monthName;
            monthDiv.onclick = () => selectMonth(i);
            monthGrid.appendChild(monthDiv);
        }
    }
</script>
<style>
    .modal-selector-year {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background: none;
    }

    .modal-selector-year>.modal-content-selector {
        position: absolute;
        background-color: #fefefe;
        left: 50%;
        top: 24%;
        transform: translate(-83%, -2%);
        border: 1px solid #888;
        width: 100%;
        max-width: 22rem;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .modal-selector-year>.modal-content-selector>.modal-header-select {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 16px;
        background-color: #337AB7;
        color: #f1f1f1;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
    }

    .modal-selector-year>.modal-content-selector>.modal-header-select>button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
    }

    .modal-selector-year>.modal-content-selector>.modal-header-select>button>img {
        width: 12px;
        height: 12px;
    }

    .modal-selector-year>.modal-content-selector>.modal-header-select>.modal-year {
        padding: 5px;
        text-align: center;
        cursor: pointer;
        font-size: 10px;
        font-weight: bold;
        transition: opacity 0.3s ease-in-out;
    }

    .modal-selector-year>.modal-content-selector>.modal-header-select>.modal-year.fade-out {
        opacity: 0;
    }

    .modal-selector-year>.modal-content-selector>.modal-header-select>.modal-year.fade-in {
        opacity: 1;
    }

    .modal-selector-year>.modal-content-selector>.month-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-gap: 10px;
        padding: 20px;
    }

    .modal-selector-year>.modal-content-selector>.month-grid>.month {
        text-align: center;
        padding: 10px;
        background-color: #f9f9f9;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .modal-selector-year>.modal-content-selector>.month-grid>.month:hover {
        background-color: #f1f1f1;
    }

    .modal-selector-year>.modal-content-selector>.modal-header-select>.close {
        color: white;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
</style>