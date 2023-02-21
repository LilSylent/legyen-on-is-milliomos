const MAX_KOR = 15;
var JELENLEGI_KOR;

LoginEllenorzes();

document.addEventListener("DOMContentLoaded", () => {
  jatszottE();
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

  Lekeredezes();
}

function Lekeredezes() {
  JELENLEGI_KOR++;
  let hovaEl = document.querySelector("#valaszok");
  hovaEl.innerHTML = "";

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
        Generalas(data, hovaEl);
      });
  } else {
    H1General("Győztél! :)", hovaEl);
  }
}

function Generalas(data, hova) {
  document.querySelector("#kerdes").innerHTML = data["kerdes"].kerdes;

  //Létrehozzuk a válaszoknak a gombokat
  for (let i = 0; i < data["valasz"].length; i++) {
    let button = document.createElement("button");

    button.innerHTML = data["valasz"][i].valasz;
    button.setAttribute("type", "button");
    button.classList.add("valasz", "gomb");
    button.addEventListener("click", () => {
      ValaszEllenorzes(data["valasz"][i].id, i);
    });

    hova.appendChild(button);
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
        //Ha a játékos eltalálta a választ
        gombok[index].style.backgroundColor = "green";
        gombok[index].style.color = "white";

        //Gombokról leveszzük az eventListener-eket
        for (let i = 0; i < gombok.length; i++) {
          gombok[i].replaceWith(gombok[i].cloneNode(true));
        }

        //1 másodperc után generálunk a felhasználónak új kérdést.
        setTimeout(() => {
          Lekeredezes();
        }, 1000);
      } else {
        //Ha a játékos nem találta el a választ
        gombok[index].style.backgroundColor = "red";
        gombok[index].style.color = "white";

        for (let i = 0; i < gombok.length; i++) {
          gombok[i].replaceWith(gombok[i].cloneNode(true));
        }

        //1 másodperc után kiirjuk, hogy vesztett a felhasználó.
        setTimeout(() => {
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
  hova.innerHTML = "";
  document.querySelector("#kerdes").innerHTML = "";
  document.querySelector("#kor").innerHTML = "";
  let h1 = document.createElement("h1");
  h1.innerHTML = mit;
  hova.appendChild(h1);
}

function jatszottE() {
  let formData = new FormData();
  formData.append("f", "jatszott");

  fetch("jatek.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((request) => {
      if (request) {
        alert("Szeretnéd folytatni a játékot?");
        JELENLEGI_KOR = request - 1;
        Lekeredezes();
      } else {
        Alapbeallitas();
      }
    });
}
