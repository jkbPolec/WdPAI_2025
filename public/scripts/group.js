let allExpenses = [];
const urlParams = new URLSearchParams(window.location.search);
const groupId = urlParams.get('id');

document.addEventListener("DOMContentLoaded", () => {
  if (!groupId) return;

  fetch(`/getGroupDetails?id=${groupId}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById('group-title').textContent = `Wydatki: ${data.group.name}`;
      allExpenses = data.expenses;
      renderMembers(data.members);
      renderTable(allExpenses);
      setupFilters(data.members);
    });
});

function renderMembers(members) {
  const container = document.getElementById('members-container');
  container.innerHTML = members.map(m => `
        <div class="member-status-card">
            <img src="https://img.icons8.com/material-sharp/40/000000/user-male-circle.png" class="member-avatar">
            <div class="member-info">
                <h3>${m.firstname}</h3>
                <p class="status-text ${m.balance < 0 ? 'status-negative' : 'status-positive'}">
                    ${m.balance < 0 ? `Jesteś winny: ${Math.abs(m.balance)} zł` : `Jest Ci winna: ${m.balance} zł`}
                </p>
            </div>
        </div>
    `).join('');
}

function renderTable(expenses) {
  const body = document.getElementById('expenses-body');
  body.innerHTML = expenses.map(e => `
        <tr>
            <td>${e.name}</td>
            <td><strong>${e.amount} PLN</strong></td>
            <td>${new Date(e.created_at).toLocaleDateString()}</td>
            <td>${e.firstname}</td>
            <td><span class="tag ${e.amount > 100 ? 'tag-food' : 'tag-bills'}">Inne</span></td>
        </tr>
    `).join('');
}

function setupFilters(members) {
  const personSelect = document.getElementById('filter-person');
  members.forEach(m => {
    personSelect.innerHTML += `<option value="${m.firstname}">${m.firstname}</option>`;
  });

  [document.getElementById('filter-person'), document.getElementById('filter-category'), document.getElementById('sort-order')]
    .forEach(el => el.addEventListener('change', () => {
      const person = document.getElementById('filter-person').value;
      const sort = document.getElementById('sort-order').value;

      let filtered = [...allExpenses];
      if (person !== 'all') filtered = filtered.filter(e => e.firstname === person);

      filtered.sort((a, b) => sort === 'desc' ?
        new Date(b.created_at) - new Date(a.created_at) :
        new Date(a.created_at) - new Date(b.created_at)
      );

      renderTable(filtered);
    }));
}