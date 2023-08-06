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

const destinataire = document.querySelector(
  ".destinataireZone"
) as HTMLDivElement;

const codeCheckbox = document.getElementById(
  "codeCheckbox"
) as HTMLInputElement;

const modalHistorique = document.querySelector(
  ".transactionHistoryList"
) as HTMLTableElement;


const info = document.querySelector(".info");
info.addEventListener("click", () => {
  const transactionFilter = document.querySelector("#transactionFilter") as HTMLSelectElement;
  const transactionRows = document.querySelectorAll(".transactionHistoryList tr");
console.log(transactionRows);


transactionFilter.addEventListener("change", () => {
  const filterValue = transactionFilter.value;
  if (filterValue === "montant") {
      const sortedRows = Array.from(transactionRows).sort((rowA, rowB) => {
      const amountA = parseInt(rowA.querySelector("td:nth-child(2)")?.textContent?.replace("CFA", "") || "0");
      const amountB = parseInt(rowB.querySelector("td:nth-child(2)")?.textContent?.replace("CFA", "") || "0");
      return amountA - amountB;
    });
    transactionRows.forEach(row => row.remove());
    sortedRows.forEach(row => modalHistorique.appendChild(row));
  } else if (filterValue === "date") {
      const sortedRows = Array.from(transactionRows).sort((rowA, rowB) => {
      const dateA = rowA.querySelector("td:nth-child(3)")?.textContent || "";
      const dateB = rowB.querySelector("td:nth-child(3)")?.textContent || "";
      return dateA.localeCompare(dateB);
    });
    transactionRows.forEach(row => row.remove());
    sortedRows.forEach(row => modalHistorique.appendChild(row));
  }
});
});



  

let typeTransactionValue = typeTransaction.value;

typeTransaction.addEventListener("change", () => {
  typeTransactionValue = typeTransaction.value;
  inputCompteDestinataire.value = "a";
  if (typeTransactionValue === "retrait") {
    destinataire.style.display = "none";
  } else {
    destinataire.style.display = "block";
  }
});

btnEnvoie.addEventListener("click", () => {
  let isChecked = codeCheckbox.checked;
  let data = {
    montant: montant.value,
    fournisseur: fournisseur.value,
    avec_code: isChecked,
    type: typeTransaction.value,
    numero_compte_desti: inputCompteDestinataire.value,
    permanant: false,
    numCompteEnvoyeur: inputCompteExpediteur.value,
  };
  console.log(data);

  const url = "http://127.0.0.1:8000/api/transaction";
  fetch(url, {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  })
    .then((response) => {
      return response.json();
    })
    .then((datas) => {
      console.log(data);

      showNotification(`${datas.message}`);
      if (datas.code !== undefined) {
        showCode(`le code est:${datas.code}`);
      }
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
            let url2 = `http://127.0.0.1:8000/api/clients/${data.id}/transaction`;
            fetch(url2)
              .then((response) => response.json())
              .then((data2) => {
                modalHistorique.innerHTML = "";
                data2.forEach((element: any) => {
                  modalHistorique.innerHTML += `
                  <tr>
                  <td>${element.type_transaction}</td>
                  <td>${element.montant}CFA</td>
                  <td>${element.date_transaction}</td>
                  <td>${element.frais}CFA</td>
                  <td>${element.date_expiration !== null ? element.date_expiration : '--'}</td>
                </tr>`;
                });
              });
          } else {
            nomExpediteur.value = "";
          }
        })
        .catch((error) => {
          modalHistorique.innerHTML = "";
        });
    } else {
      nomExpediteur.value = "";
    }
  });
}

function showNotification(message: string) {
  const notificationBox = document.getElementById("notificationBox");
  const notificationMessage = document.getElementById("notificationMessage");
  notificationMessage.textContent = message;
  notificationBox.style.display = "block";
}

function showCode(code: string) {
  const codebox = document.getElementById("codebox");
  const codeText = document.getElementById("codeText");
  codeText.textContent = code;
  codebox.style.display = "block";
}

function closeNotification() {
  const notificationBox = document.getElementById("notificationBox");
  notificationBox.style.display = "none";
}

function closeCode() {
  const codebox = document.getElementById("codebox");
  codebox.style.display = "none";
}

configureInputCompte(inputCompteExpediteur, nomExpediteur);
configureInputCompte(inputCompteDestinataire, nomDestinataire);

// ajouter un client
const nomClient = document.querySelector("#clientLastName") as HTMLInputElement;
const prenomClient = document.querySelector(
  "#clientFirstName"
) as HTMLInputElement;
const telephoneClient = document.querySelector(
  "#clientPhoneNumber"
) as HTMLInputElement;
const btnAjouterClient = document.querySelector(".saveClient");

btnAjouterClient.addEventListener("click", () => {
  let data = {
    nom: nomClient.value,
    prenom: prenomClient.value,
    numero_telephone: telephoneClient.value,
  };
  const url = "http://127.0.0.1:8000/api/clients/create";
  fetch(url, {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((datas) => {
      console.log(datas);
      showNotification(`${datas.message}`);
    });
});

function validatePhoneNumber(phoneNumber: string): boolean {
  const phoneNumberPattern = /^(77|70|78|75|76)\d{7}$/;
  return phoneNumberPattern.test(phoneNumber);
}

// creation compte
const numeroTel = document.querySelector("#accountNumber") as HTMLInputElement;
const typeCompte = document.querySelector("#accountType") as HTMLSelectElement;
const btnCreerCompte = document.querySelector(".ouvrirCompte");

btnCreerCompte.addEventListener("click", () => {
  let data = {
    numero_telephone: numeroTel.value,
    fournisseur: typeCompte.value,
  };
  // console.log(data);
  let url = "http://127.0.0.1:8000/api/compte/create";
  fetch(url, {
    method: "POST",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((datas) => {
      showNotification(`${datas.message}`);
    });
});
//fermer compte
const numeroCompteToClose = document.querySelector(
  "#accountNumberToClose"
) as HTMLInputElement;
const raisonsDeFermeture = document.querySelector(
  "#closingReason"
) as HTMLInputElement;
const btnFermerCompte = document.querySelector(".fermerCompte");

btnFermerCompte.addEventListener("click", () => {
  let data = {
    numero_compte: numeroCompteToClose.value,
    raisons: raisonsDeFermeture.value,
  };
  // console.log(data);
  let url = "http://127.0.0.1:8000/api/compte/fermer";
  fetch(url, {
    method: "PUT",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((datas) => {
      showNotification(`${datas.message}`);
    });
});

//bloquer debloquer compte
const numeroCompteToBlock = document.querySelector(
  "#accountNumberToBlock"
) as HTMLInputElement;
const btnBloquerCompte = document.querySelector(
  "#blockAccountButton"
) as HTMLButtonElement;
const btnDebloquerCompte = document.querySelector(
  "#unblockAccountButton"
) as HTMLButtonElement;

numeroCompteToBlock.addEventListener("input", () => {
  let numeroCompte = numeroCompteToBlock.value;
  let url = `http://127.0.0.1:8000/api/compte/${numeroCompte}`;
  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (data.bloquer === 1) {
        btnBloquerCompte.style.display = "none";
        btnDebloquerCompte.style.display = "block";
      } else {
        btnBloquerCompte.style.display = "block";
        btnDebloquerCompte.style.display = "none";
      }
    })
    .catch((error) => {
      btnBloquerCompte.style.display = "block";
      btnDebloquerCompte.style.display = "block";
      console.log(error);
    });
});
function blockUnBlockAccount() {
  let data = {
    numero_compte: numeroCompteToBlock.value,
  };
  let url = "http://127.0.0.1:8000/api/compte/bloquer/debloquer";
  fetch(url, {
    method: "PUT",
    body: JSON.stringify(data),
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((datas) => {
      showNotification(`${datas.message}`);
    });
}
btnBloquerCompte.addEventListener("click", blockUnBlockAccount);
btnDebloquerCompte.addEventListener("click", blockUnBlockAccount);

//annuler transaction
const annulerTransactions = document.querySelector(
  ".annulerTransactions"
) as HTMLButtonElement;
const zoneToInsertTransactions = document.querySelector(
  ".zoneToInsertTransactions"
) as HTMLTableElement;
annulerTransactions.addEventListener("click", () => {
  let url = "http://127.0.0.1:8000/api/transactions/annuler";
  fetch(url)
    .then((response) => response.json())
    .then((datas) => {
      let transactions = datas.data;
      let table = "";
      transactions.forEach((transaction: any, index: number) => {
        table += `
        <tr>
          <td>${transaction.envoyeur_nom}</td>
          <td>${transaction.envoyeur_prenom}</td>
          <td>${transaction.date_transaction}</td>
          <td>${transaction.montant}</td>
          <td>
            <button class="btn btn-danger annulerTransactionButton" data-id="${transaction.id}">Annuler</button>
          </td>
        </tr>`;
      });
      zoneToInsertTransactions.innerHTML = table;
      const annulerTransactionButtons = document.querySelectorAll(
        ".annulerTransactionButton"
      );
      annulerTransactionButtons.forEach((button) => {
        button.addEventListener("click", () => {
          let data = {
            id: button.getAttribute("data-id"),
          };
          let url = `http://localhost:8000/api/transactions/${data.id}/annuler`;
          fetch(url, {
            method: "PUT",
            body: JSON.stringify(data),
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          })
            .then((response) => response.json())
            .then((datas) => {
              if (datas.status === "success") {
                showNotification(`${datas.message}`);
                button.parentElement.parentElement.remove();
              }
            });
        });
      });
    });
});
