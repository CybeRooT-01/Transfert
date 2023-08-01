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
const modalHistorique = document.querySelector("#transactionHistoryList");
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
                            let li = document.createElement("li");
                            if (element.date_expiration === null) {
                                li.textContent = `${element.type_transaction} de  ${element.montant} fcfa le ${element.date_transaction} avec ${element.frais}Fcfa de frais`;
                                modalHistorique.appendChild(li);
                            }
                            else {
                                li.textContent = `${element.type_transaction} de  ${element.montant} fcfa le ${element.date_transaction}, avec ${element.frais}Fcfa de frais, qui vont expirer le ${element.date_expiration}`;
                                modalHistorique.appendChild(li);
                            }
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
