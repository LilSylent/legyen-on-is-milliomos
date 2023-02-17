LoginEllenorzes();

document.addEventListener("DOMContentLoaded", () => {
  Beallitas();
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

function Beallitas() {
  let formData = new FormData();

  fetch("jatek.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
      document.querySelector("#kerdes").innerHTML = data["kerdes"].kerdes;
      let gombok = document.querySelectorAll(".valasz");

      for (let i = 0; i < gombok.length; i++) {
        gombok[i].innerHTML = data["valasz"][i].valasz;
        gombok[i].setAttribute("data-id", data["valasz"][i].id);
      }
    });
}
