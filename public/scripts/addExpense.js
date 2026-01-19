document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);
  const groupId = urlParams.get('groupId');
  const participantsList = document.getElementById('participants-list');
  const groupIdInput = document.getElementById('group-id-input');

  if (!groupId) return;
  groupIdInput.value = groupId;

  fetch(`/getGroupDetails?id=${groupId}`)
    .then(res => res.json())
    .then(result => {
      if (result.status === 'success') {
        const members = result.data.members;
        participantsList.innerHTML = members.map(m => `
                    <div class="participant-item">
                        <input type="checkbox" name="participants[]" value="${m.id}" id="user-${m.id}" checked>
                        <label class="participant-label" for="user-${m.id}">
                            ${m.firstname} ${m.lastname}
                        </label>
                    </div>
                `).join('');
      }
    });
});