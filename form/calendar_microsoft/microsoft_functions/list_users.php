<script>
  const participantInput = document.getElementById("eventParticipantsInput");
  const suggestionsContainer = document.getElementById("suggestions");
  let debounceTimeout;

  participantInput.addEventListener("input", function(event) {
    console.log("Input de participantes alterado");
    clearTimeout(debounceTimeout);
    const query = participantInput.value.trim();
    if (query.length >= 3) {
      debounceTimeout = setTimeout(() => {
        searchUsers(query);
      }, 300); // Aguarde 300ms antes de fazer a pesquisa
    } else {
      suggestionsContainer.style.display = "none";
    }
  });

  participantInput.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault();
      const email = participantInput.value.trim();
      if (email) {
        addParticipant(email, participantInput, false);
        participantInput.value = "";
      }
    }
  });

  async function searchUsers(query) {
    const filter =
      "endsWith(mail,'biofy.tech') or endsWith(mail,'eng.laudolab.com.br') or endsWith(mail,'hubioagro.com.br') or endsWith(mail,'hubiobiopar.com.br') or endsWith(mail,'inata.com.br') or endsWith(mail,'laudolab.com.br') or endsWith(mail,'m7meios.com.br') or endsWith(mail,'maf.tec.br') or endsWith(mail,'mbiotech.com.br') or endsWith(mail,'nash.mobi') or endsWith(mail,'nashsolucoes.com.br') or endsWith(mail,'projectlaudo.onmicrosoft.com') or endsWith(mail,'rdbiologicos.com.br')";
    try {
      const response = await fetch(
        `https://graph.microsoft.com/v1.0/users?$count=true&$filter=${encodeURIComponent(
          filter
        )}&$search="displayName:${query}"&$orderby=displayName&$select=id,displayName,mail`, {
          method: "GET",
          headers: {
            Authorization: `Bearer ${access_token}`,
            "Content-Type": "application/json",
            ConsistencyLevel: "eventual",
          },
        }
      );

      if (response.ok) {
        const result = await response.json();
        displaySuggestions(result.value);
      } else {
        const error = await response.json();
        console.error("Erro ao buscar usuários:", error);
      }
    } catch (error) {
      console.error("Erro ao buscar usuários:", error);
    }
  }

  function displaySuggestions(users) {
    console.log("Exibindo sugestões de usuários");
    suggestionsContainer.innerHTML = "";

    const currentParticipants = Array.from(
      document.querySelectorAll("#selectedParticipants .participant-email")
    ).map((el) => el.textContent);

    users.forEach((user) => {
      if (!currentParticipants.includes(user.mail) && user.mail !== userLoggedInEmail.mail) {
        const suggestionItem = document.createElement("div");
        suggestionItem.classList.add("suggestion-item");
        suggestionItem.textContent = user.displayName !== user.mail ? `${user.displayName} (${user.mail})` : `${user.mail}`;
        suggestionItem.addEventListener("click", function() {
          addParticipant(user.mail);
          suggestionsContainer.innerHTML = "";
          participantInput.value = "";
        });
        suggestionsContainer.appendChild(suggestionItem);
      }
    });

    suggestionsContainer.style.display = "block";
  }

  function addParticipant(email) {
    console.log("Adicionando participante", email);
    const participantList = document.getElementById("selectedParticipants");
    const newParticipant = document.createElement("li");
    newParticipant.innerHTML = `<span class="participant-email">${email}</span> <span class="remove-participant" style="cursor:pointer;"><i class="fas fa-times"></i></span>`;
    participantList.appendChild(newParticipant);

    newParticipant.querySelector(".remove-participant").addEventListener("click", function() {
      newParticipant.remove();
    });
  }


  document.addEventListener("click", function(event) {
    if (
      !suggestionsContainer.contains(event.target) &&
      event.target !== participantInput
    ) {
      suggestionsContainer.style.display = "none";
    }
  });

  // Adicionando funcionalidade de arrastar para #selectedParticipants
  const selectedParticipants = document.getElementById('selectedParticipants');
  let isDown = false;
  let startX;
  let scrollLeft;

  selectedParticipants.addEventListener('mousedown', (e) => {
    isDown = true;
    selectedParticipants.classList.add('active');
    startX = e.pageX - selectedParticipants.offsetLeft;
    scrollLeft = selectedParticipants.scrollLeft;
  });

  selectedParticipants.addEventListener('mouseleave', () => {
    isDown = false;
    selectedParticipants.classList.remove('active');
  });

  selectedParticipants.addEventListener('mouseup', () => {
    isDown = false;
    selectedParticipants.classList.remove('active');
  });

  selectedParticipants.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - selectedParticipants.offsetLeft;
    const walk = (x - startX) * 3; // 3 é a velocidade do scroll
    selectedParticipants.scrollLeft = scrollLeft - walk;
  });
</script>