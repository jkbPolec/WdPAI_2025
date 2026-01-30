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
        renderMembers(data.members, data.current_user_id);
        renderTable(allExpenses);
        initCategoryOptions(allExpenses);
        initCustomSelects(data.members);
        initPaymentForm(data.members);
        initMemberManagement(data);
      } else {
        alert(result.message);
        window.location.href = "/dashboard";
      }
    });
});

function renderMembers(members, currentUserId) {
  const container = document.getElementById('members-container');
  container.innerHTML = members
      .filter(m => String(m.id) !== String(currentUserId))
      .map(m => `
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
            <td><span class="tag ${getCategoryTagClass(e.category)}">${getCategoryLabel(e.category)}</span></td>
        </tr>
    `).join('');
}

function getCategoryLabel(category) {
  return category && category.trim().length > 0 ? category : 'Inne';
}

function getCategoryTagClass(category) {
  const label = getCategoryLabel(category).toLowerCase();
  if (label.includes('jedzenie') || label.includes('zakupy')) return 'tag-food';
  if (label.includes('rachunki')) return 'tag-bills';
  return 'tag-bills';
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

function initCategoryOptions(expenses) {
  const select = document.querySelector('#category-dropdown .select-options');
  if (!select) return;

  const categories = new Set();
  expenses.forEach(e => {
    categories.add(getCategoryLabel(e.category));
  });

  const options = ['Wszystkie kategorie', ...Array.from(categories).sort()];
  select.innerHTML = options.map((opt, idx) => `
    <div class="option ${idx === 0 ? 'selected' : ''}" data-value="${opt}">
      ${opt}
    </div>
  `).join('');
}

function initPaymentForm(members) {
  const toggle = document.getElementById('toggle-payment');
  const form = document.getElementById('payment-form');
  const userSelect = document.getElementById('payment-user');
  const submitBtn = document.getElementById('payment-submit');
  const amountInput = document.getElementById('payment-amount');

  if (!toggle || !form || !userSelect || !submitBtn || !amountInput) return;

  toggle.addEventListener('click', () => {
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
  });

  userSelect.innerHTML = '<option value=\"\">Wybierz użytkownika</option>';
  members.forEach(m => {
    const opt = document.createElement('option');
    opt.value = m.id;
    opt.textContent = `${m.firstname} ${m.lastname}`;
    userSelect.appendChild(opt);
  });

  submitBtn.addEventListener('click', () => {
    const amount = parseFloat(amountInput.value);
    const toUser = parseInt(userSelect.value, 10);

    if (!groupId) {
      alert('Brak ID grupy.');
      return;
    }
    if (!amount || amount <= 0 || !toUser) {
      alert('Uzupełnij kwotę i wybierz użytkownika.');
      return;
    }

    const body = new URLSearchParams();
    body.append('group_id', groupId);
    body.append('amount', amount.toFixed(2));
    body.append('to_user', toUser);

    submitBtn.disabled = true;
    fetch('/addPayment', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: body.toString()
    })
      .then(res => res.json())
      .then(result => {
        if (result.status === 'success') {
          alert('Płatność dodana.');
          amountInput.value = '';
          userSelect.value = '';
          window.location.reload();
        } else {
          alert(result.message || 'Błąd podczas zapisu płatności.');
        }
      })
      .catch(() => alert('Błąd połączenia z serwerem.'))
      .finally(() => {
        submitBtn.disabled = false;
      });
  });
}

function initMemberManagement(data) {
  const section = document.getElementById('member-management');
  const list = document.getElementById('member-list');
  const addBtn = document.getElementById('member-add-btn');
  const emailInput = document.getElementById('member-email-input');

  if (!section || !list || !addBtn || !emailInput) return;

  const currentUserId = data.current_user_id;
  const isOwner = String(data.group.owner) === String(currentUserId);
  if (!isOwner) return;

  section.style.display = 'block';

  const members = data.all_members || [];
  list.innerHTML = members.map(m => `
    <div class="member-row">
      <div class="member-meta">
        <strong>${m.firstname} ${m.lastname}</strong>
        <span>${m.email}</span>
      </div>
      ${String(m.id) === String(data.group.owner) ? '' : `<button class="member-remove" data-id="${m.id}">Usuń</button>`}
    </div>
  `).join('');

  addBtn.addEventListener('click', () => {
    const email = emailInput.value.trim();
    if (!email) {
      alert('Wpisz email.');
      return;
    }

    const body = new URLSearchParams();
    body.append('group_id', groupId);
    body.append('email', email);

    addBtn.disabled = true;
    fetch('/addMember', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: body.toString()
    })
      .then(res => res.json())
      .then(result => {
        if (result.status === 'success') {
          window.location.reload();
        } else {
          alert(result.message || 'Błąd dodawania użytkownika.');
        }
      })
      .catch(() => alert('Błąd połączenia z serwerem.'))
      .finally(() => {
        addBtn.disabled = false;
      });
  });

  list.addEventListener('click', (e) => {
    const target = e.target;
    if (!target.classList.contains('member-remove')) return;

    const memberId = target.dataset.id;
    const body = new URLSearchParams();
    body.append('group_id', groupId);
    body.append('member_id', memberId);

    target.disabled = true;
    fetch('/removeMember', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: body.toString()
    })
      .then(res => res.json())
      .then(result => {
        if (result.status === 'success') {
          window.location.reload();
        } else {
          alert(result.message || 'Błąd usuwania użytkownika.');
        }
      })
      .catch(() => alert('Błąd połączenia z serwerem.'))
      .finally(() => {
        target.disabled = false;
      });
  });
}

function applyFilters() {
  const selectedPerson = document.querySelector('#person-dropdown .option.selected');
  const selectedSort = document.querySelector('#sort-dropdown .option.selected');
  const selectedCategory = document.querySelector('#category-dropdown .option.selected');

  const person = selectedPerson ? selectedPerson.dataset.value : 'all';
  const sort = selectedSort ? selectedSort.dataset.value : 'desc';
  const category = selectedCategory ? selectedCategory.dataset.value : 'Wszystkie kategorie';

  let filtered = [...allExpenses];
  if (person !== 'all') filtered = filtered.filter(e => e.firstname === person);
  if (category !== 'Wszystkie kategorie') {
    filtered = filtered.filter(e => getCategoryLabel(e.category) === category);
  }

  filtered.sort((a, b) => sort === 'desc' ?
    new Date(b.created_at) - new Date(a.created_at) :
    new Date(a.created_at) - new Date(b.created_at)
  );

  renderTable(filtered);
}
