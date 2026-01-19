let allExpenses = [];
const urlParams = new URLSearchParams(window.location.search);
const groupId = urlParams.get('id');

document.addEventListener("DOMContentLoaded", () => {
  if (!groupId) return;

  fetch(`/getGroupDetails?id=${groupId}`)
    .then(res => res.json())
    .then(result => {
      if (result.status === 'success') {
        const data = result.data;

        document.getElementById('group-title').textContent = `Wydatki: ${data.group.name}`;
        allExpenses = data.expenses;
        renderMembers(data.members);
        renderTable(allExpenses);
        initCustomSelects(data.members);
      }
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

function initCustomSelects(members) {
  const personOptions = document.getElementById('person-options');
  if (!personOptions) return;

  members.forEach(m => {
    const opt = document.createElement('div');
    opt.className = 'option';
    opt.dataset.value = m.firstname;
    opt.textContent = m.firstname;
    personOptions.appendChild(opt);
  });

  document.querySelectorAll('.custom-select').forEach(select => {
    const trigger = select.querySelector('.select-trigger');
    const options = select.querySelectorAll('.option');

    trigger.addEventListener('click', (e) => {
      document.querySelectorAll('.custom-select').forEach(s => {
        if (s !== select) s.classList.remove('active');
      });
      select.classList.toggle('active');
      e.stopPropagation();
    });

    options.forEach(option => {
      option.addEventListener('click', () => {
        select.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        select.querySelector('.select-trigger span').textContent = option.textContent;
        select.classList.remove('active');
        applyFilters();
      });
    });
  });

  window.addEventListener('click', () => {
    document.querySelectorAll('.custom-select').forEach(s => s.classList.remove('active'));
  });
}

function applyFilters() {
  const selectedPerson = document.querySelector('#person-dropdown .option.selected');
  const selectedSort = document.querySelector('#sort-dropdown .option.selected');

  const person = selectedPerson ? selectedPerson.dataset.value : 'all';
  const sort = selectedSort ? selectedSort.dataset.value : 'desc';

  let filtered = [...allExpenses];
  if (person !== 'all') filtered = filtered.filter(e => e.firstname === person);

  filtered.sort((a, b) => sort === 'desc' ?
    new Date(b.created_at) - new Date(a.created_at) :
    new Date(a.created_at) - new Date(b.created_at)
  );

  renderTable(filtered);
}