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
  document.querySelector("#grafikon-container").innerHTML = "";

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
  document.querySelector("#kerdes").setAttribute("data-id", data["kerdes"].id);

  hova.innerHTML = "";
  document.querySelector("#segitsegek").innerHTML = "";
  segitsegGeneralas("50/50", OtvenOtven, "otvenotven");
  segitsegGeneralas("Közönség", kozonseg, "kozonseg");

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

  //A gombról levesszük az event listener-t
  let otvenotvenEl = document.querySelector("#otvenotven");
  otvenotvenEl.removeEventListener("click", OtvenOtven);

  let kozonsegEl = document.querySelector("#kozonseg");
  kozonsegEl.removeEventListener("click", kozonseg);

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
        }, 1000);
      }
    });
}

function H1General(felirat, hova) {
  hova.innerHTML = "";
  document.querySelector("#kerdes").innerHTML = "";
  document.querySelector("#kor").innerHTML = "";
  document.querySelector("#segitsegek").innerHTML = "";
  document.querySelector("#grafikon-container").innerHTML = "";

  let h1 = document.createElement("h1");
  h1.innerHTML = felirat;

  hova.appendChild(h1);

  let button = document.createElement("button");
  button.innerHTML = "Újrakezdés";
  button.classList.add("gomb");
  button.addEventListener("click", Alapbeallitas);

  hova.appendChild(button);
}

function jatszottE() {
  let formData = new FormData();
  formData.append("f", "jatszott");
  let hova = document.querySelector("#valaszok");
  hova.innerHTML = "";
  document.querySelector("#kor").innerHTML = "";

  fetch("jatek.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((kor) => {
      if (kor) {
        JELENLEGI_KOR = kor - 1;
        folytatjaE(hova);
      } else {
        Alapbeallitas();
      }
    });
}

function folytatjaE(hova) {
  document.querySelector("#kerdes").innerHTML =
    "Szeretnéd folytatni az előző játékot?";

  let igen = document.createElement("button");
  igen.innerHTML = "Igen";
  igen.classList.add("gomb", "zold");
  igen.setAttribute("type", "button");
  igen.addEventListener("click", Lekeredezes);
  hova.appendChild(igen);

  let nem = document.createElement("button");
  nem.innerHTML = "Nem";
  nem.classList.add("gomb", "piros");
  nem.setAttribute("type", "button");
  nem.addEventListener("click", Alapbeallitas);
  hova.appendChild(nem);
}

function OtvenOtven() {
  let formData = new FormData();
  formData.append("f", "otven");
  formData.append("id", document.querySelector("#kerdes").dataset.id);

  fetch("jatek.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      Generalas(data, document.querySelector("#valaszok"));
    });
}

function kozonseg() {
  let formData = new FormData();
  formData.append("f", "kozonseg");
  formData.append("id", document.querySelector("#kerdes").dataset.id);

  fetch("jatek.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      GrafikonGeneralas(data);
    });
}

function GrafikonGeneralas(adat) {
  let hova = document.querySelector("#grafikon-container");
  hova.innerHTML = "";
  let canvas = document.createElement("canvas");
  canvas.setAttribute("id", "grafikon");
  hova.appendChild(canvas);

  const ctx = document.querySelector("#grafikon");
  var grafikon = new Chart(ctx, {
    type: "bar",
    data: {
      labels: adat["valasz"].map((sor) => sor.valasz),
      datasets: [
        {
          label: "Szavazatok száma (%)",
          data: adat["valasz"].map((sor) => sor.szazalek),
          borderWidth: 1,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

function segitsegGeneralas(felirat, funkcio, id) {
  let hova = document.querySelector("#segitsegek");

  let button = document.createElement("button");
  button.innerHTML = felirat;
  button.setAttribute("type", "button");
  button.setAttribute("id", id);
  button.addEventListener("click", funkcio);

  hova.appendChild(button);
}
