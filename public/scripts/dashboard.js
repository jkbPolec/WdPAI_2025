document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".groups-container");
  const template = document.querySelector("#group-card-template");

  if (container && template) {
    fetch("/getGroups")
      .then(response => response.json())
      .then(groups => {
        if (groups.length === 0) {
          container.innerHTML = "<p>Nie należysz jeszcze do żadnej grupy.</p>";
          return;
        }
        renderGroups(groups, container, template);
      })
      .catch(error => console.error('Błąd pobierania grup:', error));
  }
});

function renderGroups(groups, container, template) {
  container.innerHTML = "";

  groups.forEach(group => {
    const clone = template.content.cloneNode(true);
    const cardLink = clone.querySelector(".group-card");
    cardLink.href = `/group?id=${group.id}`;

    clone.querySelector(".group-name").textContent = group.name;

    const balanceValue = clone.querySelector(".balance-value");
    const balance = parseFloat(group.balance);

    if (balance > 0) {
      balanceValue.textContent = `Jesteś na plusie: ${balance.toFixed(2)} zł`;
      balanceValue.classList.add("balance-positive");
    } else if (balance < 0) {
      balanceValue.textContent = `Musisz oddać: ${Math.abs(balance).toFixed(2)} zł`;
      balanceValue.classList.add("balance-negative");
    } else {
      balanceValue.textContent = "Rozliczony na zero";
    }

    container.appendChild(clone);
  });

  const addCard = document.createElement("a");
  addCard.href = "/addGroup";
  addCard.className = "group-card add-group-btn";
  addCard.innerHTML = `
        <div class="add-icon">+</div>
        <span class="add-text">Utwórz nową grupę</span>
    `;
  container.appendChild(addCard);
}