const mockGroups = [
  { id: 1, name: "Mieszkanie", balance: -120.50 },
  { id: 2, name: "Wyjazd góry", balance: 450.00 },
  { id: 3, name: "Prezent dla mamy", balance: 0.00 },
  { id: 4, name: "Zakupy wspólne", balance: -15.00 }
];

document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector(".groups-container");
  const template = document.querySelector("#group-card-template");

  if (container && template) {
    renderGroups(mockGroups, container, template);
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
    if (group.balance > 0) {
      balanceValue.textContent = `Jesteś na plusie: ${group.balance.toFixed(2)} zł`;
      balanceValue.classList.add("balance-positive");
    } else if (group.balance < 0) {
      const amount = Math.abs(group.balance).toFixed(2);
      balanceValue.textContent = `Musisz oddać: ${amount} zł`;
      balanceValue.classList.add("balance-negative");
    } else {
      balanceValue.textContent = "Rozliczony na zero";
      balanceValue.style.color = "var(--small-color)";
    }

    container.appendChild(clone);
  });
}