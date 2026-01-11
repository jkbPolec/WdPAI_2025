const input = document.getElementById('email-input');
const tagsContainer = document.getElementById('email-tags');
const hiddenInput = document.getElementById('members-json');
let emails = [];

input.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    const email = input.value.trim();
    if (email && !emails.includes(email)) {
      emails.push(email);
      renderTags();
      input.value = '';
    }
  }
});

function renderTags() {
  tagsContainer.innerHTML = '';
  emails.forEach(email => {
    const tag = document.createElement('div');
    tag.className = 'tag';
    tag.innerHTML = `<span>${email}</span><span class="remove" onclick="removeEmail('${email}')">×</span>`;
    tagsContainer.appendChild(tag);
  });
  hiddenInput.value = JSON.stringify(emails);
}

function removeEmail(email) {
  emails = emails.filter(e => e !== email);
  renderTags();
}