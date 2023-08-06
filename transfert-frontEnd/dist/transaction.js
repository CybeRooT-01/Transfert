const inputCompteExpediteur = document.querySelector(".expedideur");
const nomExpediteur = document.querySelector(".nomExpediteur");
const inputCompteDestinataire = document.querySelector(".destinataire");
const nomDestinataire = document.querySelector(".nomDestinataire");
const btnEnvoie = document.querySelector(".btnSend");
const typeTransaction = document.querySelector(".type");
const fournisseur = document.querySelector(".fournisseur");
const montant = document.querySelector(".montant");
const destinataire = document.querySelector(".destinataireZone");
const codeCheckbox = document.getElementById("codeCheckbox");
const modalHistorique = document.querySelector(".transactionHistoryList");
const info = document.querySelector(".info");
info.addEventListener("click", () => {
    const transactionFilter = document.querySelector("#transactionFilter");
    const transactionRows = document.querySelectorAll(".transactionHistoryList tr");
    console.log(transactionRows);
    transactionFilter.addEventListener("change", () => {
        const filterValue = transactionFilter.value;
        if (filterValue === "montant") {
            const sortedRows = Array.from(transactionRows).sort((rowA, rowB) => {
                var _a, _b, _c, _d;
                const amountA = parseInt(((_b = (_a = rowA.querySelector("td:nth-child(2)")) === null || _a === void 0 ? void 0 : _a.textContent) === null || _b === void 0 ? void 0 : _b.replace("CFA", "")) || "0");
                const amountB = parseInt(((_d = (_c = rowB.querySelector("td:nth-child(2)")) === null || _c === void 0 ? void 0 : _c.textContent) === null || _d === void 0 ? void 0 : _d.replace("CFA", "")) || "0");
                return amountA - amountB;
            });
            transactionRows.forEach(row => row.remove());
            sortedRows.forEach(row => modalHistorique.appendChild(row));
        }
        else if (filterValue === "date") {
            const sortedRows = Array.from(transactionRows).sort((rowA, rowB) => {
                var _a, _b;
                const dateA = ((_a = rowA.querySelector("td:nth-child(3)")) === null || _a === void 0 ? void 0 : _a.textContent) || "";
                const dateB = ((_b = rowB.querySelector("td:nth-child(3)")) === null || _b === void 0 ? void 0 : _b.textContent) || "";
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
    }
    else {
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
function configureInputCompte(inputCompte, nomExpediteur) {
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
                        data2.forEach((element) => {
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
                }
                else {
                    nomExpediteur.value = "";
                }
            })
                .catch((error) => {
                modalHistorique.innerHTML = "";
            });
        }
        else {
            nomExpediteur.value = "";
        }
    });
}
function showNotification(message) {
    const notificationBox = document.getElementById("notificationBox");
    const notificationMessage = document.getElementById("notificationMessage");
    notificationMessage.textContent = message;
    notificationBox.style.display = "block";
}
function showCode(code) {
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
const nomClient = document.querySelector("#clientLastName");
const prenomClient = document.querySelector("#clientFirstName");
const telephoneClient = document.querySelector("#clientPhoneNumber");
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
function validatePhoneNumber(phoneNumber) {
    const phoneNumberPattern = /^(77|70|78|75|76)\d{7}$/;
    return phoneNumberPattern.test(phoneNumber);
}
// creation compte
const numeroTel = document.querySelector("#accountNumber");
const typeCompte = document.querySelector("#accountType");
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
const numeroCompteToClose = document.querySelector("#accountNumberToClose");
const raisonsDeFermeture = document.querySelector("#closingReason");
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
const numeroCompteToBlock = document.querySelector("#accountNumberToBlock");
const btnBloquerCompte = document.querySelector("#blockAccountButton");
const btnDebloquerCompte = document.querySelector("#unblockAccountButton");
numeroCompteToBlock.addEventListener("input", () => {
    let numeroCompte = numeroCompteToBlock.value;
    let url = `http://127.0.0.1:8000/api/compte/${numeroCompte}`;
    fetch(url)
        .then((response) => response.json())
        .then((data) => {
        if (data.bloquer === 1) {
            btnBloquerCompte.style.display = "none";
            btnDebloquerCompte.style.display = "block";
        }
        else {
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
const annulerTransactions = document.querySelector(".annulerTransactions");
const zoneToInsertTransactions = document.querySelector(".zoneToInsertTransactions");
annulerTransactions.addEventListener("click", () => {
    let url = "http://127.0.0.1:8000/api/transactions/annuler";
    fetch(url)
        .then((response) => response.json())
        .then((datas) => {
        let transactions = datas.data;
        let table = "";
        transactions.forEach((transaction, index) => {
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
        const annulerTransactionButtons = document.querySelectorAll(".annulerTransactionButton");
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
