<div id="eventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle"></h2>
            <div class="btn-group">
                <span class="expand-collapse" id="expandButton">
                    <i class="fas fa-expand"></i>
                </span>
                <span class="close" id="closeEditButtonParticipant">
                    <i class="fas fa-times"></i>
                </span>
            </div>
        </div>
        <div class="modal-body">
            <form id="eventForm" class="form-container">
                <div id="microsoftContent" style="display: none;">
                    <div class="form-group">
                        <label for="eventTitle">Adicionar um Título</label>
                        <input type="text" id="eventTitle" name="title" placeholder="Insira o número de identificação">
                    </div>
                    <div class="form-group">
                        <label for="eventParticipantsInput">Convidar pessoas</label>
                        <div class="input-group">
                            <input type="email" id="eventParticipantsInput" placeholder="Digite um nome ou email para procurar">
                            <span class="optional-text">Opcional</span>
                            <div id="suggestions" class="suggestions">
                                <div class="suggestion-item" style="display: none;"></div>
                            </div>
                        </div>
                        <ul id="selectedParticipants">
                            <li style="display: none;">
                                <span class="participant-email"></span>
                                <span class="remove-participant" style="cursor:pointer;">x</span>
                            </li>
                        </ul>
                    </div>
                    <div class="grid-container">
                        <!-- Coluna de Datas e Sala -->
                        <div class="grid-column">
                            <div class="form-group">
                                <label for="eventRoom">Sala de reunião</label>
                                <select id="eventRoom" name="room">
                                    <option value="none">Nenhuma sala</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="eventStartDate">Data de Início</label>
                                <input type="date" id="eventStartDate" name="startDate">
                            </div>
                            <div class="form-group">
                                <label for="eventEndDate">Data de Término</label>
                                <input type="date" id="eventEndDate" name="endDate">
                            </div>
                            <div class="form-group checkbox-group collapsible" id="allDayGroup">
                                <input type="checkbox" id="allDayEvent" name="allDayEvent">
                                <label for="allDayEvent">Dia Inteiro</label>
                            </div>
                            <div class="form-group collapsible" id="repeatGroup">
                                <select id="repeatOption" name="repeatOption">
                                    <option value="noRepeat">Não repetir</option>
                                    <option value="repeat">Repetir</option>
                                </select>
                            </div>
                            <div class="form-group checkbox-group collapsible" id="teamsGroup">
                                <input type="checkbox" id="teamsMeeting" name="meetingType" checked>
                                <label for="teamsMeeting">Reunião do Teams</label>
                            </div>
                            <div class="form-group checkbox-group collapsible" id="inPersonGroup">
                                <input type="checkbox" id="inPersonMeeting" name="meetingType">
                                <label for="inPersonMeeting">Presencial</label>
                            </div>
                        </div>
                        <div class="grid-column">
                        <div class="form-group" style="height: 46px;"></div>
                            <div class="form-group">
                                <label for="eventStartHour">Horário de Início</label>
                                <input type="text" id="eventStartHour" name="startHour">
                                <div id="startTimeOptions" class="time-options"></div>
                            </div>
                            <div class="form-group">
                                <label for="eventEndHour">Horário de Término</label>
                                <input type="text" id="eventEndHour" name="endHour">
                                <div id="endTimeOptions" class="time-options"></div>
                            </div>
                        </div>
                        <div class="grid-column">
                            <div class="form-group conflicts-container" id="conflictsContainer">
                                <div class="busy-header">Eventos do dia</div>
                                <div id="eventConflicts" class="conflicts-list"></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editEventDescription">Observação</label>
                        <textarea id="editEventDescription" name="body" placeholder="Adicionar uma descrição"></textarea>
                    </div>
                    <div class="button-group">
                        <button type="button" class="btn-discard" id="discardEventButton"><i class="fas fa-times"></i> Descartar</button>
                        <button type="submit" class="btn-save" id="saveEventButton"><i class="fas fa-save"></i> Salvar</button>
                    </div>
                </div>
                <div id="sislaudoContent" style="display: none;">
                    <div class="sislaudo-modal-body" id="optionsTipos">
                        <input type="hidden" id="dateParaSislaudo" name="vdia">
                        <?
                        carregaEventoTipo("modal");
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>