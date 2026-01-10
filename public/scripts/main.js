const header = document.querySelector('h1');
console.log(header);
header.addEventListener('click', () => {
  header.style.color = 'green';
});

const search = document.querySelector('input[placeholder="search card"]');
const cardsContainer = document.querySelector(".cards");

search.addEventListener("keyup", function (event) {
  if (event.key === "Enter") {
    event.preventDefault();

    const data = { search: this.value };

    fetch("/search-cards", {
      method: "POST",
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    })
      .then(response => response.json())
      .then(cards => {
        cardsContainer.innerHTML = ""; // Czyścimy stare wyniki
        loadCards(cards);
      });
  }
});

function loadCards(cards) {
  cards.forEach(card => {
    createCard(card);
  });
}

function createCard(card) {
  const template = document.querySelector("#card-template");
  const clone = template.content.cloneNode(true);

  // Wypełniamy klona danymi z bazy (JSON)
  const div = clone.querySelector("div");
  div.id = card.id;

  const image = clone.querySelector("img");
  //image.src = `/public/img/${card.image}`; // Upewnij się, że masz ten folder i pliki!
  image.src = card.image;

  const title = clone.querySelector("h2");
  title.innerHTML = card.title;

  const description = clone.querySelector("p");
  description.innerHTML = card.description;

  cardsContainer.appendChild(clone);
}