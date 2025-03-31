// document.addEventListener("DOMContentLoaded", function () {
//     let inputs = document.querySelectorAll("input");

//     // Process input: recalculate on focus loss or Enter press
//     inputs.forEach(input => {
//         input.addEventListener("keypress", function (event) {
//             if (event.key === "Enter") {
//                 handleInput(this.id);
//             }
//         });
//     });

//     calculateFrom(); // Initial calculation when page loads
// });

// function calculateLoss(percentage, volume) {
//     return Math.round((percentage / 100) * volume);
// }

// function handleInput(inputId) {
//     if (["loss-pkopP", "loss-kenkiyakP", "loss-kenkiyakTransferP", "loss-pspP", "loss-zhanazholP"].includes(inputId)) {
//         calculateFrom(inputId);
//     } else {
//         calculateFrom();
//     }
// }

// function calculateFrom(startId = null) {
//     // Get initial values
//     let pkop = parseFloat(document.getElementById("volume2-pkop").value) || 0;
//     let zhanazholedit = parseFloat(document.getElementById("volume2-zhanazholedit").value) || 0;

//     // Get percentages
//     let psp45PP = parseFloat(document.getElementById("percent-psp45PP").value) || 0;
//     let zhanazholPP = parseFloat(document.getElementById("percent-zhanazholPP").value) || 0;
//     let kenkiyakTransferPP = parseFloat(document.getElementById("percent-kenkiyakTransferPP").value) || 0;
//     let kenkiyakPP = parseFloat(document.getElementById("percent-kenkiyakPP").value) || 0;
//     let pkopPP = parseFloat(document.getElementById("percent-pkopPP").value) || 0;

//     // Get saved values or calculate using formula
//     let pkopP = parseFloat(document.getElementById("loss-pkopP").value) || calculateLoss(pkopPP, pkop);
//     let kumkol = pkop + pkopP;
//     let kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || calculateLoss(kenkiyakPP, kumkol);
//     let kenkiyak = kumkol + kenkiyakP;
//     let kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || calculateLoss(kenkiyakTransferPP, kenkiyak);
//     let kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
//     let zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || calculateLoss(zhanazholPP, zhanazholedit);
//     let zhanazhol = zhanazholedit + zhanazholP;
//     let psp45first = kenkiyakTransfer - zhanazhol;
//     let pspP = parseFloat(document.getElementById("loss-pspP").value) || calculateLoss(psp45PP, psp45first);
//     let psp45end = psp45first + pspP;

//     // 🔹 Dynamic update of values along the chain
//     if (startId === "loss-pkopP") {
//         pkopP = parseFloat(document.getElementById("loss-pkopP").value) || 0;
//         kumkol = pkop + pkopP;

//         // Update percentage
//         let newPercent = (pkopP / pkop) * 100;
//         document.getElementById("percent-pkopPP").value = newPercent.toFixed(2);

//         kenkiyakP = calculateLoss(kenkiyakPP, kumkol);
//         kenkiyak = kumkol + kenkiyakP;
//         kenkiyakTransferP = calculateLoss(kenkiyakTransferPP, kenkiyak);
//         kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
//         psp45first = kenkiyakTransfer - zhanazhol;
//         pspP = calculateLoss(psp45PP, psp45first);
//         psp45end = psp45first + pspP;
//     }

//     if (startId === "loss-kenkiyakP") {
//         kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || 0;
//         kenkiyak = kumkol + kenkiyakP;

//         // Update percentage
//         let newPercent = (kenkiyakP / kumkol) * 100;
//         document.getElementById("percent-kenkiyakPP").value = newPercent.toFixed(2);

//         kenkiyakTransferP = calculateLoss(kenkiyakTransferPP, kenkiyak);
//         kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
//         psp45first = kenkiyakTransfer - zhanazhol;
//         pspP = calculateLoss(psp45PP, psp45first);
//         psp45end = psp45first + pspP;
//     }

//     if (startId === "loss-kenkiyakTransferP") {
//         kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || 0;
//         kenkiyakTransfer = kenkiyak + kenkiyakTransferP;

//         // Update percentage
//         let newPercent = (kenkiyakTransferP / kenkiyak) * 100;
//         document.getElementById("percent-kenkiyakTransferPP").value = newPercent.toFixed(2);

//         psp45first = kenkiyakTransfer - zhanazhol;
//         pspP = calculateLoss(psp45PP, psp45first);
//         psp45end = psp45first + pspP;
//     }

//     if (startId === "loss-zhanazholP") {
//         zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || 0;
//         zhanazhol = zhanazholedit + zhanazholP;

//         // Update percentage
//         let newPercent = (zhanazholP / zhanazholedit) * 100;
//         document.getElementById("percent-zhanazholPP").value = newPercent.toFixed(2);

//         psp45first = kenkiyakTransfer - zhanazhol;
//         pspP = calculateLoss(psp45PP, psp45first);
//         psp45end = psp45first + pspP;
//     }

//     if (startId === "loss-pspP") {
//         pspP = parseFloat(document.getElementById("loss-pspP").value) || 0;
//         psp45end = psp45first + pspP;

//         // Update percentage
//         let newPercent = (pspP / psp45first) * 100;
//         document.getElementById("percent-psp45PP").value = newPercent.toFixed(2);
//     }

//     // 🔹 Fill in only changed values
//     document.getElementById("volume-psp45end").value = psp45end;
//     document.getElementById("loss-pspP").value = pspP;
//     document.getElementById("volume2-psp45first").value = psp45first;
//     document.getElementById("volume-zhanazhol").value = zhanazhol;
//     document.getElementById("loss-zhanazholP").value = zhanazholP;
//     document.getElementById("volume-kenkiyakTransfer").value = kenkiyakTransfer;
//     document.getElementById("loss-kenkiyakTransferP").value = kenkiyakTransferP;
//     document.getElementById("volume-kenkiyak").value = kenkiyak;
//     document.getElementById("volume2-kenkiyak").value = kenkiyak;
//     document.getElementById("volume-kumkol").value = kumkol;
//     document.getElementById("volume2-kumkol").value = kumkol;
//     document.getElementById("loss-kenkiyakP").value = kenkiyakP;
//     document.getElementById("loss-pkopP").value = pkopP;

//         function updateValues() {
//         const km1235 = 21000;
//         let zhanazholedit3 = 1599;
//         const samaraPP = parseFloat(document.getElementById("percent-samaraPP").value) || 0;
//         const samaraTransferPP = parseFloat(document.getElementById("percent-samaraTransferPP").value) || 0;
//         const klinPP = parseFloat(document.getElementById("percent-klinPP").value) || 0;
//         const nikolskiPP = parseFloat(document.getElementById("percent-nikolskiPP").value) || 0;
//         const unechaPP = parseFloat(document.getElementById("percent-unechaPP").value) || 0;
//         const ustlugaTransferPP = parseFloat(document.getElementById("percent-ustlugaTransferPP").value) || 0;
//         const km1235PP = parseFloat(document.getElementById("percent-km1235PP").value) || 0;
//         const kasimovaPP = parseFloat(document.getElementById("percent-kasimovaPP").value) || 0;
//         const shmanovaPP = parseFloat(document.getElementById("percent-shmanovaPP").value) || 0;
//         const kenkiyakTransferPP = parseFloat(document.getElementById("percent-kenkiyakTransferPP").value) || 0;
//         const zhanazholPP = parseFloat(document.getElementById("percent-zhanazholPP").value) || 0;
//         const psp45PP = parseFloat(document.getElementById("percent-psp45PP").value) || 0;

//         const samaraP = calculateLoss(samaraPP, km1235);
//         const samara = km1235 - samaraP;
//         const samaraTransferP = calculateLoss(samaraTransferPP, samara);
//         const samaraTransfer = samara - samaraTransferP;
//         const klinP = calculateLoss(klinPP, samaraTransfer);
//         const klin = samaraTransfer - klinP;
//         const nikolskiP = calculateLoss(nikolskiPP, klin);
//         const nikolski = klin - nikolskiP;
//         const unechaP = calculateLoss(unechaPP, nikolski);
//         const unecha = nikolski - unechaP;
//         const ustlugaTransferP = calculateLoss(ustlugaTransferPP, unecha);
//         const ustlugaTransfer = unecha - ustlugaTransferP;

//         const kasimovaP = calculateLoss(km1235PP, km1235);
//         const kasimova = km1235 + kasimovaP;
//         const shmanovaP = calculateLoss(kasimovaPP, kasimova);
//         const shmanova = kasimova + shmanovaP;
//         const kenkiyak3P = calculateLoss(shmanovaPP, shmanova);
//         const kenkiyak3 = shmanova + kenkiyak3P;
//         const kenkiyakTransfer3P = calculateLoss(kenkiyakTransferPP, kenkiyak3);
//         const kenkiyakTransfer3 = kenkiyak3 + kenkiyakTransfer3P;
//         const zhanazhol3P = calculateLoss(zhanazholPP, zhanazholedit3);
//         const zhanazhol3 = zhanazholedit3 + zhanazhol3P;
//         const psp45first3 = kenkiyakTransfer3 - zhanazhol3;
//         const psp3P = calculateLoss(psp45PP, psp45first3);
//         const psp45end3 = psp45first3 + psp3P;

//         document.getElementById("volume-samara").value = samara; 
//         document.getElementById("loss-samaraP").value = samaraP;
//         document.getElementById("volume2-samara").value = samara;
//         document.getElementById("volume-samaraTransfer").value = samaraTransfer;
//         document.getElementById("loss-samaraTransferP").value = samaraTransferP;
//         document.getElementById("volume2-samaraTransfer").value = samaraTransfer;
//         document.getElementById("volume-klin").value = klin;
//         document.getElementById("loss-klinP").value = klinP;
//         document.getElementById("volume2-klin").value = klin;
//         document.getElementById("volume-nikolski").value = nikolski;
//         document.getElementById("loss-nikolskiP").value = nikolskiP;
//         document.getElementById("volume2-nikolski").value = nikolski;
//         document.getElementById("volume-unecha").value = unecha;
//         document.getElementById("loss-unechaP").value = unechaP;
//         document.getElementById("volume2-unecha").value = unecha;
//         document.getElementById("loss-ustlugaTransferP").value = ustlugaTransferP;
//         document.getElementById("volume2-ustlugaTransfer").value = ustlugaTransfer;
//         document.getElementById("volume-km1235").value = km1235;
    
//         document.getElementById("volume-kasimova").value = kasimova;
//         document.getElementById("loss-kasimovaP").value = kasimovaP;
//         document.getElementById("volume2-kasimova").value = kasimova;
//         document.getElementById("volume-shmanova").value = shmanova;
//         document.getElementById("loss-shmanovaP").value = shmanovaP;
//         document.getElementById("volume2-shmanova").value = shmanova;
//         document.getElementById("volume-kenkiyak3").value = kenkiyak3;
//         document.getElementById("loss-kenkiyak3P").value = kenkiyak3P;
//         document.getElementById("volume2-kenkiyak3").value = kenkiyak3;
//         document.getElementById("volume-kenkiyakTransfer3").value = kenkiyakTransfer3;
//         document.getElementById("loss-kenkiyakTransfer3P").value = kenkiyakTransfer3P;
//         document.getElementById("loss-zhanazhol3P").value = zhanazhol3P;
//         document.getElementById("volume-zhanazhol3").value = zhanazhol3;
//         document.getElementById("loss-psp3P").value = psp3P;
//         document.getElementById("volume2-psp45first3").value = psp45first3;
//         document.getElementById("volume-psp45end3").value = psp45end3;
//     }
// }

document.addEventListener("DOMContentLoaded", function () {
    let inputs = document.querySelectorAll("input");

    // Process input: recalculate on focus loss or Enter press
    inputs.forEach(input => {
        input.addEventListener("keypress", function (event) {
            if (event.key === "Enter") {
                handleInput(this.id);
            }
        });
    });

    calculateFrom(); // Initial calculation when page loads
});

function calculateLoss(percentage, volume) {
    return Math.round((percentage / 100) * volume);
}

function handleInput(inputId) {
    if (["loss-pkopP", "loss-kenkiyakP", "loss-kenkiyakTransferP", "loss-pspP", "loss-zhanazholP",
         "loss-samaraP", "loss-samaraTransferP", "loss-klinP", "loss-nikolskiP", "loss-unechaP", "loss-ustlugaTransferP",
         "loss-kasimovaP", "loss-shmanovaP", "loss-kenkiyak3P", "loss-kenkiyakTransfer3P", "loss-zhanazhol3P", "loss-psp3P"].includes(inputId)) {
        calculateFrom(inputId);
    } else {
        calculateFrom();
    }
}

function calculateFrom(startId = null) {
    // Get initial values
    let pkop = parseFloat(document.getElementById("volume2-pkop").value) || 0;
    let zhanazholedit = parseFloat(document.getElementById("volume2-zhanazholedit").value) || 0;

    // Get percentages
    let psp45PP = parseFloat(document.getElementById("percent-psp45PP").value) || 0;
    let zhanazholPP = parseFloat(document.getElementById("percent-zhanazholPP").value) || 0;
    let kenkiyakTransferPP = parseFloat(document.getElementById("percent-kenkiyakTransferPP").value) || 0;
    let kenkiyakPP = parseFloat(document.getElementById("percent-kenkiyakPP").value) || 0;
    let pkopPP = parseFloat(document.getElementById("percent-pkopPP").value) || 0;

    // Get saved values or calculate using formula
    let pkopP = parseFloat(document.getElementById("loss-pkopP").value) || calculateLoss(pkopPP, pkop);
    let kumkol = pkop + pkopP;
    let kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || calculateLoss(kenkiyakPP, kumkol);
    let kenkiyak = kumkol + kenkiyakP;
    let kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || calculateLoss(kenkiyakTransferPP, kenkiyak);
    let kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
    let zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || calculateLoss(zhanazholPP, zhanazholedit);
    let zhanazhol = zhanazholedit + zhanazholP;
    let psp45first = kenkiyakTransfer - zhanazhol;
    let pspP = parseFloat(document.getElementById("loss-pspP").value) || calculateLoss(psp45PP, psp45first);
    let psp45end = psp45first + pspP;

    // ========== NEW CALCULATIONS ==========
    // Constants and initial values
    let km1235 = parseFloat(document.getElementById("volume2-km1235").value) || 0;
    let zhanazholedit3 = parseFloat(document.getElementById("volume2-zhanazholedit3").value) || 0;
    
    // Get additional percentages
    let samaraPP = parseFloat(document.getElementById("percent-samaraPP").value) || 0;
    let samaraTransferPP = parseFloat(document.getElementById("percent-samaraTransferPP").value) || 0;
    let klinPP = parseFloat(document.getElementById("percent-klinPP").value) || 0;
    let nikolskiPP = parseFloat(document.getElementById("percent-nikolskiPP").value) || 0;
    let unechaPP = parseFloat(document.getElementById("percent-unechaPP").value) || 0;
    let ustlugaTransferPP = parseFloat(document.getElementById("percent-ustlugaTransferPP").value) || 0;
    let km1235PP = parseFloat(document.getElementById("percent-km1235PP").value) || 0;
    let kasimovaPP = parseFloat(document.getElementById("percent-kasimovaPP").value) || 0;
    let shmanovaPP = parseFloat(document.getElementById("percent-shmanovaPP").value) || 0;

    // First calculation chain
    let samaraP = parseFloat(document.getElementById("loss-samaraP").value) || calculateLoss(samaraPP, km1235);
    let samara = km1235 - samaraP;
    let samaraTransferP = parseFloat(document.getElementById("loss-samaraTransferP").value) || calculateLoss(samaraTransferPP, samara);
    let samaraTransfer = samara - samaraTransferP;
    let klinP = parseFloat(document.getElementById("loss-klinP").value) || calculateLoss(klinPP, samaraTransfer);
    let klin = samaraTransfer - klinP;
    let nikolskiP = parseFloat(document.getElementById("loss-nikolskiP").value) || calculateLoss(nikolskiPP, klin);
    let nikolski = klin - nikolskiP;
    let unechaP = parseFloat(document.getElementById("loss-unechaP").value) || calculateLoss(unechaPP, nikolski);
    let unecha = nikolski - unechaP;
    let ustlugaTransferP = parseFloat(document.getElementById("loss-ustlugaTransferP").value) || calculateLoss(ustlugaTransferPP, unecha);
    let ustlugaTransfer = unecha - ustlugaTransferP;

    // Second calculation chain
    let kasimovaP = parseFloat(document.getElementById("loss-kasimovaP").value) || calculateLoss(km1235PP, km1235);
    let kasimova = km1235 + kasimovaP;
    let shmanovaP = parseFloat(document.getElementById("loss-shmanovaP").value) || calculateLoss(kasimovaPP, kasimova);
    let shmanova = kasimova + shmanovaP;
    let kenkiyak3P = parseFloat(document.getElementById("loss-kenkiyak3P").value) || calculateLoss(shmanovaPP, shmanova);
    let kenkiyak3 = shmanova + kenkiyak3P;
    let kenkiyakTransfer3P = parseFloat(document.getElementById("loss-kenkiyakTransfer3P").value) || calculateLoss(kenkiyakTransferPP, kenkiyak3);
    let kenkiyakTransfer3 = kenkiyak3 + kenkiyakTransfer3P;
    let zhanazhol3P = parseFloat(document.getElementById("loss-zhanazhol3P").value) || calculateLoss(zhanazholPP, zhanazholedit3);
    let zhanazhol3 = zhanazholedit3 + zhanazhol3P;
    let psp45first3 = kenkiyakTransfer3 - zhanazhol3;
    let psp3P = parseFloat(document.getElementById("loss-psp3P").value) || calculateLoss(psp45PP, psp45first3);
    let psp45end3 = psp45first3 + psp3P;

    // 🔹 Dynamic update of values along the chain
    if (startId === "loss-pkopP") {
        pkopP = parseFloat(document.getElementById("loss-pkopP").value) || 0;
        kumkol = pkop + pkopP;

        // Update percentage
        let newPercent = (pkopP / pkop) * 100;
        document.getElementById("percent-pkopPP").value = newPercent.toFixed(2);

        kenkiyakP = calculateLoss(kenkiyakPP, kumkol);
        kenkiyak = kumkol + kenkiyakP;
        kenkiyakTransferP = calculateLoss(kenkiyakTransferPP, kenkiyak);
        kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = calculateLoss(psp45PP, psp45first);
        psp45end = psp45first + pspP;
    }

    if (startId === "loss-kenkiyakP") {
        kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || 0;
        kenkiyak = kumkol + kenkiyakP;

        // Update percentage
        let newPercent = (kenkiyakP / kumkol) * 100;
        document.getElementById("percent-kenkiyakPP").value = newPercent.toFixed(2);

        kenkiyakTransferP = calculateLoss(kenkiyakTransferPP, kenkiyak);
        kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = calculateLoss(psp45PP, psp45first);
        psp45end = psp45first + pspP;
    }

    if (startId === "loss-kenkiyakTransferP") {
        kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || 0;
        kenkiyakTransfer = kenkiyak + kenkiyakTransferP;

        // Update percentage
        let newPercent = (kenkiyakTransferP / kenkiyak) * 100;
        document.getElementById("percent-kenkiyakTransferPP").value = newPercent.toFixed(2);

        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = calculateLoss(psp45PP, psp45first);
        psp45end = psp45first + pspP;
    }

    if (startId === "loss-zhanazholP") {
        zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || 0;
        zhanazhol = zhanazholedit + zhanazholP;

        // Update percentage
        let newPercent = (zhanazholP / zhanazholedit) * 100;
        document.getElementById("percent-zhanazholPP").value = newPercent.toFixed(2);

        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = calculateLoss(psp45PP, psp45first);
        psp45end = psp45first + pspP;
    }

    if (startId === "loss-pspP") {
        pspP = parseFloat(document.getElementById("loss-pspP").value) || 0;
        psp45end = psp45first + pspP;

        // Update percentage
        let newPercent = (pspP / psp45first) * 100;
        document.getElementById("percent-psp45PP").value = newPercent.toFixed(2);
    }

    // You would add more handlers for the new input fields similarly
    // For example:
    if (startId === "loss-samaraP") {
        // Handle manual change of samaraP value
        // Update other values in the chain
    }

    // 🔹 Fill in only changed values - original values
    document.getElementById("volume-psp45end").value = psp45end;
    document.getElementById("loss-pspP").value = pspP;
    document.getElementById("volume2-psp45first").value = psp45first;
    document.getElementById("volume-zhanazhol").value = zhanazhol;
    document.getElementById("loss-zhanazholP").value = zhanazholP;
    document.getElementById("volume-kenkiyakTransfer").value = kenkiyakTransfer;
    document.getElementById("loss-kenkiyakTransferP").value = kenkiyakTransferP;
    document.getElementById("volume-kenkiyak").value = kenkiyak;
    document.getElementById("volume2-kenkiyak").value = kenkiyak;
    document.getElementById("volume-kumkol").value = kumkol;
    document.getElementById("volume2-kumkol").value = kumkol;
    document.getElementById("loss-kenkiyakP").value = kenkiyakP;
    document.getElementById("loss-pkopP").value = pkopP;

    // 🔹 Fill in new values - first chain
    document.getElementById("volume-samara").value = samara; 
    document.getElementById("loss-samaraP").value = samaraP;
    document.getElementById("volume2-samara").value = samara;
    document.getElementById("volume-samaraTransfer").value = samaraTransfer;
    document.getElementById("loss-samaraTransferP").value = samaraTransferP;
    document.getElementById("volume2-samaraTransfer").value = samaraTransfer;
    document.getElementById("volume-klin").value = klin;
    document.getElementById("loss-klinP").value = klinP;
    document.getElementById("volume2-klin").value = klin;
    document.getElementById("volume-nikolski").value = nikolski;
    document.getElementById("loss-nikolskiP").value = nikolskiP;
    document.getElementById("volume2-nikolski").value = nikolski;
    document.getElementById("volume-unecha").value = unecha;
    document.getElementById("loss-unechaP").value = unechaP;
    document.getElementById("volume2-unecha").value = unecha;
    document.getElementById("loss-ustlugaTransferP").value = ustlugaTransferP;
    document.getElementById("volume2-ustlugaTransfer").value = ustlugaTransfer;
    document.getElementById("volume-km1235").value = km1235;
    
    // 🔹 Fill in new values - second chain
    document.getElementById("volume-kasimova").value = kasimova;
    document.getElementById("loss-kasimovaP").value = kasimovaP;
    document.getElementById("volume2-kasimova").value = kasimova;
    document.getElementById("volume-shmanova").value = shmanova;
    document.getElementById("loss-shmanovaP").value = shmanovaP;
    document.getElementById("volume2-shmanova").value = shmanova;
    document.getElementById("volume-kenkiyak3").value = kenkiyak3;
    document.getElementById("loss-kenkiyak3P").value = kenkiyak3P;
    document.getElementById("volume2-kenkiyak3").value = kenkiyak3;
    document.getElementById("volume-kenkiyakTransfer3").value = kenkiyakTransfer3;
    document.getElementById("loss-kenkiyakTransfer3P").value = kenkiyakTransfer3P;
    document.getElementById("loss-zhanazhol3P").value = zhanazhol3P;
    document.getElementById("volume-zhanazhol3").value = zhanazhol3;
    document.getElementById("loss-psp3P").value = psp3P;
    document.getElementById("volume2-psp45first3").value = psp45first3;
    document.getElementById("volume-psp45end3").value = psp45end3;
}


