const MAX_KOR = 15;
var JELENLEGI_KOR;

LoginEllenorzes();

document.addEventListener("DOMContentLoaded", () => {
  Alapbeallitas();
  document.querySelector("#logout").addEventListener("click", Kilepes);
});

function Kilepes() {
  let formData = new FormData();

  fetch("kilepes.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((request) => {
      if (request == "OK") {
        window.location.assign("index.html");
      }
    });
}

function LoginEllenorzes() {
  let formData = new FormData();

  fetch("belepes.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((request) => {
      if (request != "OK") {
        window.location.assign("index.html");
      }
    });
}

function Alapbeallitas() {
  JELENLEGI_KOR = 0;

  let hovaEl = document.querySelector("#valaszok");
  hovaEl.innerHTML = "";

  let kerdesEl = document.querySelector("#kerdes");
  kerdesEl.innerHTML = "";

  Generalas();
}

function Generalas() {
  JELENLEGI_KOR++;
  let hovaEl = document.querySelector("#valaszok");
  hovaEl.innerHTML = "";

  let kerdesEl = document.querySelector("#kerdes");
  kerdesEl.innerHTML = "";

  if (JELENLEGI_KOR <= MAX_KOR) {
    document.querySelector("#kor").innerHTML = JELENLEGI_KOR + "/" + MAX_KOR;

    let formData = new FormData();
    formData.append("f", "lekerdezes");
    formData.append("k", JELENLEGI_KOR);

    fetch("jatek.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        //console.log(data);
        kerdesEl.innerHTML = data["kerdes"].kerdes;

        for (let i = 0; i < data["valasz"].length; i++) {
          let button = document.createElement("button");

          button.innerHTML = data["valasz"][i].valasz;
          button.classList.add("valasz", "gomb");
          button.addEventListener("click", () => {
            ValaszEllenorzes(data["valasz"][i].id, i);
          });

          hovaEl.appendChild(button);
        }
      });
  } else {
    H1General("Győztél! :)", hovaEl);
  }
}

function ValaszEllenorzes(valaszId, index) {
  let formData = new FormData();
  formData.append("f", "valasz");
  formData.append("i", JSON.stringify(valaszId));

  let gombok = document.querySelectorAll(".valasz");
  let hovaEl = document.querySelector("#valaszok");

  fetch("jatek.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((request) => {
      if (request == "OK") {
        gombok[index].style.backgroundColor = "green";

        for (let i = 0; i < gombok.length; i++) {
          gombok[i].replaceWith(gombok[i].cloneNode(true));
        }

        setTimeout(() => {
          Generalas();
        }, 1000);
      } else {
        gombok[index].style.backgroundColor = "red";

        for (let i = 0; i < gombok.length; i++) {
          gombok[i].replaceWith(gombok[i].cloneNode(true));
        }

        setTimeout(() => {
          hovaEl.innerHTML = "";
          H1General("Vesztettél! :(", hovaEl);
          let button = document.createElement("button");

          button.innerHTML = "Újrakezdés";
          button.classList.add("gomb");
          button.addEventListener("click", () => {
            Alapbeallitas();
          });

          hovaEl.appendChild(button);
        }, 1000);
      }
    });
}

function H1General(mit, hova) {
  let h1 = document.createElement("h1");
  h1.innerHTML = mit;
  hova.appendChild(h1);
}
