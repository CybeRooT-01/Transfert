const inputCompteExpediteur = document.querySelector(
  ".expedideur"
) as HTMLInputElement;
const nomExpediteur = document.querySelector(
  ".nomExpediteur"
) as HTMLInputElement;

const inputCompteDestinataire = document.querySelector(
  ".destinataire"
) as HTMLInputElement;
const nomDestinataire = document.querySelector(
  ".nomDestinataire"
) as HTMLInputElement;

const btnEnvoie = document.querySelector(".btnSend") as HTMLButtonElement;

const typeTransaction = document.querySelector(".type") as HTMLSelectElement;
const fournisseur = document.querySelector(".fournisseur") as HTMLSelectElement;

const montant = document.querySelector(".montant") as HTMLInputElement;

btnEnvoie.addEventListener("click", () => {
  let data = {
    montant: montant.value,
    fournisseur: fournisseur.value,
    avec_code: true,
    type: typeTransaction.value,
    numero_compte_desti: inputCompteDestinataire.value,
    permanent: false,
    envoyeur:inputCompteExpediteur.value
  };
  console.log(data);

  const url = "http://127.0.0.1:8000/api/transaction";

  fetch(url, {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json",
    },
  })
    .then((response) => {
    
      return response.json();
    })
    .then((datas) => {
      showNotification(datas.message)
      console.log(datas);
    });
});

function configureInputCompte(
  inputCompte: HTMLInputElement,
  nomExpediteur: HTMLInputElement
) {
  inputCompte.addEventListener("input", () => {
    const numeroCompte = inputCompte.value;
    nomExpediteur.value = "";
    if (numeroCompte.trim() !== "") {
      let url = `http://127.0.0.1:8000/api/compte/${numeroCompte}/client`;
      fetch(url)
        .then((response) => response.json())
        .then((data) => {
          if (data.nom && data.prenom) {
            nomExpediteur.value = `${data.nom} ${data.prenom}`;
          } else {
            nomExpediteur.value = "";
          }
        })
        .catch((error) => {});
    } else {
      nomExpediteur.value = "";
    }
  });
}
function showNotification(message: string) {
  const notificationBox = document.getElementById('notificationBox');
  const notificationMessage = document.getElementById('notificationMessage');
  notificationMessage.textContent = message;
  notificationBox.style.display = 'block';
}

function closeNotification() {
  const notificationBox = document.getElementById('notificationBox');
  notificationBox.style.display = 'none';
}


configureInputCompte(inputCompteExpediteur, nomExpediteur);
configureInputCompte(inputCompteDestinataire, nomDestinataire);
