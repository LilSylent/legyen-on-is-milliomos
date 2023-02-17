LoginEllenorzes();

document.addEventListener("DOMContentLoaded", () => {
  document.querySelector("#login").addEventListener("click", Belepes);
});

function Belepes() {
  let formData = new FormData(document.querySelector("#belepes"));

  fetch("belepes.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((request) => {
      if (request == "OK") {
        window.location.assign("jatek.html");
      } else {
        alert("Ne hagyd üresen a mezőt!");
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
      if (request == "OK") {
        window.location.assign("jatek.html");
      }
    });
}
