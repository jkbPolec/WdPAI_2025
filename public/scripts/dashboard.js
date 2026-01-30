document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".groups-container");
  const template = document.querySelector("#group-card-template");

  if (container && template) {
    fetch("/getGroups")
      .then(response => response.json())
      .then(result => {
        const groups = result.data;

        if (!groups || groups.length === 0) {
          container.innerHTML = "<p>Nie należysz jeszcze do żadnej grupy.</p>";
          renderAddButton(container);
          return;
        }
        renderGroups(groups, container, template);
      })
      .catch(error => console.error('Błąd pobierania grup:', error));
  }
});

function renderGroups(groups, container, template) {
  container.innerHTML = "";
  let totalDebt = 0;
  let totalCredit = 0;

  groups.forEach(group => {
    const balance = parseFloat(group.balance);
    if (balance < 0) totalDebt += Math.abs(balance);
    else if (balance > 0) totalCredit += balance;

    const clone = template.content.cloneNode(true);
    const cardLink = clone.querySelector(".group-card");
    cardLink.href = `/group?id=${group.id}`;

    clone.querySelector(".group-name").textContent = group.name;
    const balanceValue = clone.querySelector(".balance-value");

    if (balance > 0) {
      balanceValue.textContent = `Jesteś na plusie: ${balance.toFixed(2).replace('.', ',')} zł`;
      balanceValue.classList.add("balance-positive");
    } else if (balance < 0) {
      balanceValue.textContent = `Musisz oddać: ${Math.abs(balance).toFixed(2).replace('.', ',')} zł`;
      balanceValue.classList.add("balance-negative");
    } else {
      balanceValue.textContent = "Wszystko rozliczone";
    }

    container.appendChild(clone);
  });

  const netBalance = totalCredit - totalDebt;
  const formattedDebt = `${totalDebt.toFixed(2).replace('.', ',')} zł`;
  const formattedCredit = `${totalCredit.toFixed(2).replace('.', ',')} zł`;
  const formattedNet = `${netBalance > 0 ? '+' : ''}${netBalance.toFixed(2).replace('.', ',')} zł`;

  // Aktualizacja Desktop
  document.querySelector('.desk-debt').textContent = formattedDebt;
  document.querySelector('.desk-credit').textContent = formattedCredit;

  // Aktualizacja Mobile
  document.querySelector('.net-balance-value').textContent = formattedNet;
  document.querySelector('.mob-debt').textContent = formattedDebt;
  document.querySelector('.mob-credit').textContent = formattedCredit;

  renderAddButton(container);
}

function renderAddButton(container) {
  const addCard = document.createElement("a");
  addCard.href = "/addGroup";
  addCard.className = "group-card add-group-btn";
  addCard.innerHTML = `
        <div class="add-icon">+</div>
        <span class="add-text">Utwórz nową grupę</span>
    `;
  container.appendChild(addCard);
}