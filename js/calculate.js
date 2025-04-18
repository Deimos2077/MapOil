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
         "loss-kasimovaP", "loss-shmanovaP", "loss-kenkiyak3P", "loss-kenkiyakTransfer3P", "loss-zhanazhol3P", "loss-psp3P", "loss-kenkiyak4P",].includes(inputId)) {
        calculateFrom(inputId);
    } else {
        calculateFrom();
    }
}

function calculateFrom(startId = null) {
    // ========== минус остатки ==========
    let minus_volumePsp = parseFloat(document.getElementById("minus-volumePsp").value) || 0;
    let minus_volumeShmanova = parseFloat(document.getElementById("minus-volumeShmanova").value) || 0;
    let minus_volumeKumkol = parseFloat(document.getElementById("minus-volumeKumkol").value) || 0;
    let minus_volume1 = parseFloat(document.getElementById("minus-volume1").value) || 0;
    let minus_volume2 = parseFloat(document.getElementById("minus-volume2").value) || 0;
    // ========== плюс остатки ==========
    let plus_volumePsp = parseFloat(document.getElementById("plus-volumePsp").value) || 0;
    let plus_volumeShmanova = parseFloat(document.getElementById("plus-volumeShmanova").value) || 0;
    let plus_volumeKumkol = parseFloat(document.getElementById("plus-volumeKumkol").value) || 0;
    let plus_volume1 = parseFloat(document.getElementById("plus-volume1").value) || 0;
    let plus_volume2 = parseFloat(document.getElementById("plus-volume2").value) || 0;

    // ========== ПКОП ==========
    let pkop = parseFloat(document.getElementById("volume2-pkop").value)|| 0; ;
    let zhanazholedit = parseFloat(document.getElementById("volume2-zhanazholedit").value)|| 0 ;

    // Get percentages
    let psp45PP = parseFloat(document.getElementById("percent-psp45PP").value) || 0;
    let zhanazholPP = parseFloat(document.getElementById("percent-zhanazholPP").value) || 0;
    let kenkiyakTransferPP = parseFloat(document.getElementById("percent-kenkiyakTransferPP").value) || 0;
    let kenkiyakPP = parseFloat(document.getElementById("percent-kenkiyakPP").value) || 0;
    let pkopPP = parseFloat(document.getElementById("percent-pkopPP").value) || 0;

    // расчет значений
    let pkopP = parseFloat(document.getElementById("loss-pkopP").value) || calculateLoss(pkopPP, pkop);
    let kumkol = pkop + pkopP-minus_volumeKumkol+plus_volumeKumkol;
    let kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || calculateLoss(kenkiyakPP, kumkol);
    let kenkiyak = kumkol + kenkiyakP;
    let kenkiyakminus = kenkiyak - minus_volume1+plus_volume1;
    let kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || calculateLoss(kenkiyakTransferPP, kenkiyak);
    let kenkiyakTransfer = kenkiyakminus + kenkiyakTransferP;
    let zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || calculateLoss(zhanazholPP, zhanazholedit);
    let zhanazhol = zhanazholedit + zhanazholP;
    let psp45first = kenkiyakTransfer - zhanazholedit;
    let pspP = parseFloat(document.getElementById("loss-pspP").value) || calculateLoss(psp45PP, psp45first);
    let psp45end = psp45first + pspP-minus_volumePsp+plus_volumePsp;

    // ========== КНР ==========
    let atasu = parseFloat(document.getElementById("volume-atasu").value) || 0;
    let zhanazholedit2 = parseFloat(document.getElementById("volume2-zhanazholedit2").value) || 0;
    
    // Получение процентных значений
    let alashankouPP = parseFloat(document.getElementById("percent-alashankouPP").value) || 0;
    let atasuTransferPP = parseFloat(document.getElementById("percent-atasuTransferPP").value) || 0;
    let dzhumagalievaPP = parseFloat(document.getElementById("percent-dzhumagalievaPP").value) || 0;
    let kumkolPP = parseFloat(document.getElementById("percent-kumkolPP").value) || 0;

    
    // Расчет значений
    let alashankouP = parseFloat(document.getElementById("loss-alashankouP").value) || calculateLoss(alashankouPP, atasu);
    let alashankou = atasu - alashankouP;
    let atasuTransferP = parseFloat(document.getElementById("loss-atasuTransferP").value) || calculateLoss(atasuTransferPP, alashankou);
    let atasuTransfer = atasu + atasuTransferP;
    let dzhumagalievaP = parseFloat(document.getElementById("loss-dzhumagalievaP").value) || calculateLoss(dzhumagalievaPP, atasuTransfer);
    let dzhumagalieva = atasuTransfer + dzhumagalievaP;
    let kumkol2P = parseFloat(document.getElementById("loss-kumkol2P").value) || calculateLoss(kumkolPP, dzhumagalieva);
    let kumkol2 = dzhumagalieva + kumkol2P;
    let kenkiyak2P = parseFloat(document.getElementById("loss-kenkiyak2P").value) || calculateLoss(kenkiyakPP, kumkol2);
    let kenkiyak2 = kumkol2 + kenkiyak2P;
    let kenkiyakTransfer2P = parseFloat(document.getElementById("loss-kenkiyakTransfer2P").value) || calculateLoss(kenkiyakTransferPP, kenkiyak2);
    let kenkiyakTransfer2 = kenkiyak2 + kenkiyakTransfer2P;
    let zhanazhol2P = parseFloat(document.getElementById("loss-zhanazhol2P").value) || calculateLoss(zhanazholPP, zhanazholedit2);
    let zhanazhol2 = zhanazholedit2 + zhanazhol2P;
    let psp45first2 = kenkiyakTransfer2 - zhanazholedit2;
    let psp2P = parseFloat(document.getElementById("loss-psp2P").value) || calculateLoss(psp45PP, psp45first2);
    let psp45end2 = psp45first2 + psp2P;

    // ========== УСТ-ЛУГА ==========
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

    // Расчет значений
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

    let kasimovaP = parseFloat(document.getElementById("loss-kasimovaP").value) || calculateLoss(km1235PP, km1235);
    let kasimova = km1235 + kasimovaP;
    let shmanovaP = parseFloat(document.getElementById("loss-shmanovaP").value) || calculateLoss(kasimovaPP, kasimova);
    let shmanova = kasimova + shmanovaP-minus_volumeShmanova+plus_volumeShmanova;
    let kenkiyak3P = parseFloat(document.getElementById("loss-kenkiyak3P").value) || calculateLoss(shmanovaPP, shmanova);
    let kenkiyak3 = shmanova + kenkiyak3P-minus_volume2+plus_volume2;
    let kenkiyakTransfer3P = parseFloat(document.getElementById("loss-kenkiyakTransfer3P").value) || calculateLoss(kenkiyakTransferPP, kenkiyak3);
    let kenkiyakTransfer3 = kenkiyak3 + kenkiyakTransfer3P;
    let zhanazhol3P = parseFloat(document.getElementById("loss-zhanazhol3P").value) || calculateLoss(zhanazholPP, zhanazholedit3);
    let zhanazhol3 = zhanazholedit3 + zhanazhol3P;
    let psp45first3 = kenkiyakTransfer3 - zhanazholedit3;
    let psp3P = parseFloat(document.getElementById("loss-psp3P").value) || calculateLoss(psp45PP, psp45first3);
    let psp45end3 = psp45first3 + psp3P;

    // ========== ответ хранение ==========
    let kenkiyak4PP = parseFloat(document.getElementById("percent-kenkiyak4PP").value) || 0;
    
    let kumkol4 = parseFloat(document.getElementById("volume2-kumkol4").value) || 0;
    let zhanazholedit4 = parseFloat(document.getElementById("volume2-zhanazholedit4").value) || 0;

    let kenkiyak4P = parseFloat(document.getElementById("loss-kenkiyak4P").value) || calculateLoss(kenkiyak4PP, kumkol4);
    let kenkiyak4 = kumkol4 + kenkiyak4P;
    let kenkiyakTransfer4P = parseFloat(document.getElementById("loss-kenkiyakTransfer4P").value) || calculateLoss(kenkiyakTransferPP, kenkiyak4);
    let kenkiyakTransfer4 = kenkiyak4 + kenkiyakTransfer4P;
    let zhanazhol4P = parseFloat(document.getElementById("loss-zhanazhol4P").value) || calculateLoss(zhanazholPP, zhanazholedit4);
    let zhanazhol4 = zhanazholedit4 + zhanazhol4P;
    let psp45first4 = kenkiyakTransfer4 - zhanazholedit4;
    let psp4P = parseFloat(document.getElementById("loss-psp4P").value) || calculateLoss(psp45PP, psp45first4);
    let psp45end4 = psp45first4 + psp4P;

    // ========== ПНХЗ ==========
    let pnhzPP = parseFloat(document.getElementById("percent-pnhzPP").value) || 0;

    let atasu5 = parseFloat(document.getElementById("volume-atasu5").value) || 0;
    let zhanazholedit5 = parseFloat(document.getElementById("volume2-zhanazholedit5").value) || 0;

    let pnhzP = parseFloat(document.getElementById("loss-pnhzP").value) || calculateLoss(pnhzPP, atasu5);
    let pnhz = atasu5 - pnhzP;
    let atasuTransfer5P = parseFloat(document.getElementById("loss-atasuTransfer5P").value) || calculateLoss(atasuTransferPP, pnhz);
    let atasuTransfer5 = atasu5 + atasuTransfer5P;
    let dzhumagalieva5P = parseFloat(document.getElementById("loss-dzhumagalieva5P").value) || calculateLoss(dzhumagalievaPP, atasuTransfer5);
    let dzhumagalieva5 = atasuTransfer5 + dzhumagalieva5P;
    let kumkol5P = parseFloat(document.getElementById("loss-kumkol5P").value) || calculateLoss(kumkolPP, dzhumagalieva5);
    let kumkol5 = dzhumagalieva5 + kumkol5P;
    let kenkiyak5P = parseFloat(document.getElementById("loss-kenkiyak5P").value) || calculateLoss(kenkiyakPP, kumkol5);
    let kenkiyak5 = kumkol5 + kenkiyak5P;
    let kenkiyakTransfer5P = parseFloat(document.getElementById("loss-kenkiyakTransfer5P").value) || calculateLoss(kenkiyakTransferPP, kenkiyak5);
    let kenkiyakTransfer5 = kenkiyak5 + kenkiyakTransfer5P;
    let zhanazhol5P = parseFloat(document.getElementById("loss-zhanazhol5P").value) || calculateLoss(zhanazholPP, zhanazholedit5);
    let zhanazhol5 = zhanazholedit5 + zhanazhol5P;
    let psp45first5 = kenkiyakTransfer5 - zhanazholedit5;
    let psp5P = parseFloat(document.getElementById("loss-psp5P").value) || calculateLoss(psp45PP, psp45first5);
    let psp45end5 = psp45first5 + psp5P;

    // ========== Новороссийск ==========
    let km915PP = parseFloat(document.getElementById("percent-km915PP").value) || 0;
    let radionovskiPP = parseFloat(document.getElementById("percent-radionovskiPP").value) || 0;
    let texareckPP = parseFloat(document.getElementById("percent-texareckPP").value) || 0;
    let grushavaiPP = parseFloat(document.getElementById("percent-grushavaiPP").value) || 0;
    let grushavaiTransferPP = parseFloat(document.getElementById("percent-grushavaiTransferPP").value) || 0;
    let krasnoarmPP = parseFloat(document.getElementById("percent-krasnoarmPP").value) || 0;


    let km12356 = parseFloat(document.getElementById("volume2-km12356").value) || 0;
    let zhanazholedit6 = parseFloat(document.getElementById("volume2-zhanazholedit6").value) || 0;
    let samara6P = parseFloat(document.getElementById("loss-samara6P").value) || calculateLoss(samaraPP, km12356);
    let samara6 = km12356 - samara6P;
    let samaraTransfer6P = parseFloat(document.getElementById("loss-samaraTransfer6P").value) || calculateLoss(samaraTransferPP, samara6);
    let samaraTransfer6 = samara6 - samaraTransfer6P;
    let krasnoarmP = parseFloat(document.getElementById("loss-krasnoarmP").value) || calculateLoss(krasnoarmPP, samaraTransfer6);
    let krasnoarm = samaraTransfer6 - krasnoarmP;
    let km915P = parseFloat(document.getElementById("loss-km915P").value) || calculateLoss(km915PP, krasnoarm);
    let km915 = krasnoarm - km915P;
    let radionovskiP = parseFloat(document.getElementById("loss-radionovskiP").value) || calculateLoss(radionovskiPP, km915);
    let radionovski = km915 - radionovskiP;
    let texareckP = parseFloat(document.getElementById("loss-texareckP").value) || calculateLoss(texareckPP, radionovski);
    let texareck = radionovski - texareckP;
    let grushavaiP = parseFloat(document.getElementById("loss-grushavaiP").value) || calculateLoss(grushavaiPP, texareck);
    let grushavai = texareck - grushavaiP;
    let grushavaiTransferP = parseFloat(document.getElementById("loss-grushavaiTransferP").value) || calculateLoss(grushavaiTransferPP, grushavai);
    let grushavaiTransfer = grushavai - grushavaiTransferP;
    let kasimova6P = parseFloat(document.getElementById("loss-kasimova6P").value) || calculateLoss(km1235PP, km12356);
    let kasimova6 = km12356 + kasimova6P;
    let shmanova6P = parseFloat(document.getElementById("loss-shmanova6P").value) || calculateLoss(kasimovaPP, kasimova6);
    let shmanova6 = kasimova6 + shmanova6P;
    let kenkiyak6P = parseFloat(document.getElementById("loss-kenkiyak6P").value) || calculateLoss(shmanovaPP, shmanova6);
    let kenkiyak6 = shmanova6 + kenkiyak6P;
    let kenkiyakTransfer6P = parseFloat(document.getElementById("loss-kenkiyakTransfer6P").value) || calculateLoss(kenkiyakTransferPP, kenkiyak6);
    let kenkiyakTransfer6 = kenkiyak6 + kenkiyakTransfer6P;
    let zhanazhol6P = parseFloat(document.getElementById("loss-zhanazhol6P").value) || calculateLoss(zhanazholPP, zhanazholedit6);
    let zhanazhol6 = zhanazholedit6 + zhanazhol6P;
    let psp45first6 = kenkiyakTransfer6 - zhanazholedit6;
    let psp6P = parseFloat(document.getElementById("loss-psp6P").value) || calculateLoss(psp45PP, psp45first6);
    let psp45end6 = psp45first6 + psp6P;
    

    // ========== Остатки ==========
    let start_volumePsp=parseFloat(document.getElementById("start-volumePsp").value) || 0;
    let start_volumeShmanova = parseFloat(document.getElementById("start-volumeShmanova").value) || 0;
    let start_volumeKumkol = parseFloat(document.getElementById("start-volumeKumkol").value) || 0;
    let start_volume1 = parseFloat(document.getElementById("start-volume1").value) || 0;
    let start_volume2 = parseFloat(document.getElementById("start-volume2").value) || 0;
    let end_volumePsp = start_volumePsp+psp45end+psp45end2+psp45end3+psp45end4+psp45end5+psp45end6-pspP-psp2P-psp3P-psp4P-psp5P-psp6P-psp45first-psp45first2-psp45first3-psp45first4-psp45first5-psp45first6;
    let end_volume1 = start_volume1+kenkiyakminus+kenkiyak2+kenkiyak4+kenkiyak5-kenkiyakP-kenkiyak2P-kenkiyak4P-kenkiyak5P-kumkol-kumkol2-kumkol4-kumkol5;
    let end_volumeKumkol=start_volumeKumkol+kumkol+kumkol2+kumkol4+kumkol5-pkopP-kumkol2P-dzhumagalievaP-atasuTransferP-kumkol5P-dzhumagalieva5P-pkop-atasu-atasu5;
    let end_volume2=start_volume2+kenkiyak3+kenkiyak6-kenkiyak3P-kenkiyak6P-shmanova-shmanova6;
    let end_volumeShmanova=start_volumeShmanova+shmanova+shmanova6-shmanovaP-shmanova6P-kasimovaP-kasimova6P-km1235-km12356;
    document.getElementById("end-volume1").value = end_volume1;
    document.getElementById("end-volumePsp").value = end_volumePsp;
    document.getElementById("end-volumeKumkol").value = end_volumeKumkol;
    document.getElementById("end-volume2").value = end_volume2;
    document.getElementById("end-volumeShmanova").value = end_volumeShmanova;

    let oilplane= parseFloat(document.getElementById("oilplane").value) || psp45end+zhanazhol+psp45end2+psp45end3+psp45end4+psp45end5+psp45end6
    let oil = parseFloat(document.getElementById("oil").value) || 0;
    let oil_loss = psp45end+zhanazhol+psp45end2+psp45end3+psp45end4+zhanazhol4+psp45end5+psp45end6-oil;
    document.getElementById("oilplane").value = oilplane;
    document.getElementById("oil-loss").value = oil_loss;



    // // 🔹 Dynamic update of values along the chain

    if (startId === "loss-pkopP") {
        pkopP = parseFloat(document.getElementById("loss-pkopP").value) || 0;
        // Update percentage
        let newPercent = (pkopP / pkop) * 100;
        document.getElementById("percent-pkopPP").value = newPercent.toFixed(2);

    }

    if (startId === "loss-kenkiyakP") {
        kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || 0;
        // Update percentage
        let newPercent = (kenkiyakP / kumkol) * 100;
        document.getElementById("percent-kenkiyakPP").value = newPercent.toFixed(2);
    }

    if (startId === "loss-kenkiyakTransferP") {
        kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || 0;
        // Update percentage
        let newPercent = (kenkiyakTransferP / kenkiyak) * 100;
        document.getElementById("percent-kenkiyakTransferPP").value = newPercent.toFixed(2);
    }

    if (startId === "loss-zhanazholP") {
        zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || 0;
        // Update percentage
        let newPercent = (zhanazholP / zhanazholedit) * 100;
        document.getElementById("percent-zhanazholPP").value = newPercent.toFixed(2);
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
        samaraP = parseFloat(document.getElementById("loss-samaraP").value) || 0;
        // Update percentage
        let newPercent = (samaraP / km1235) * 100;
        document.getElementById("percent-samaraPP").value = newPercent.toFixed(2);
    }

    if( startId === "loss-kenkiyak4P"){
        kenkiyak4P = parseFloat(document.getElementById("loss-kenkiyak4P").value) || 0;
        // Update percentage
        let newPercent = (kenkiyak4P / kumkol4) * 100;
        document.getElementById("percent-kenkiyak4PP").value = newPercent.toFixed(2);
    } 

    // ПКОП вывод
    document.getElementById("volume-psp45end").value = psp45end;
    document.getElementById("loss-pspP").value = pspP;
    document.getElementById("volume2-psp45first").value = psp45first;
    document.getElementById("volume-zhanazhol").value = zhanazhol;
    document.getElementById("loss-zhanazholP").value = zhanazholP;
    document.getElementById("volume-kenkiyakTransfer").value = kenkiyakTransfer;
    document.getElementById("loss-kenkiyakTransferP").value = kenkiyakTransferP;
    document.getElementById("volume-kenkiyak").value = kenkiyakminus;
    document.getElementById("volume2-kenkiyak").value = kenkiyakminus;
    document.getElementById("volume-kumkol").value = kumkol;
    document.getElementById("volume2-kumkol").value = kumkol;
    document.getElementById("loss-kenkiyakP").value = kenkiyakP;
    document.getElementById("loss-pkopP").value = pkopP;

    // КНР вывод
    document.getElementById("volume2-atasu").value = atasu;
    document.getElementById("volume2-alashankou").value = alashankou;
    document.getElementById("loss-alashankouP").value = alashankouP;
    document.getElementById("volume2-atasuTransfer").value = atasuTransfer;
    document.getElementById("volume-atasuTransfer").value = atasuTransfer;
    document.getElementById("loss-atasuTransferP").value = atasuTransferP;
    document.getElementById("volume2-dzhumagalieva").value = dzhumagalieva;
    document.getElementById("volume-dzhumagalieva").value = dzhumagalieva;
    document.getElementById("loss-dzhumagalievaP").value = dzhumagalievaP;
    document.getElementById("volume2-kumkol2").value = kumkol2;
    document.getElementById("volume-kumkol2").value = kumkol2;
    document.getElementById("loss-kumkol2P").value = kumkol2P;
    document.getElementById("volume2-kenkiyak2").value = kenkiyak2;
    document.getElementById("volume-kenkiyak2").value = kenkiyak2;
    document.getElementById("loss-kenkiyak2P").value = kenkiyak2P;
    document.getElementById("volume-kenkiyakTransfer2").value = kenkiyakTransfer2;
    document.getElementById("loss-kenkiyakTransfer2P").value = kenkiyakTransfer2P;
    document.getElementById("volume-zhanazhol2").value = zhanazhol2;
    document.getElementById("loss-zhanazhol2P").value = zhanazhol2P;
    document.getElementById("volume2-psp45first2").value = psp45first2;
    document.getElementById("volume-psp45end2").value = psp45end2;
    document.getElementById("loss-psp2P").value = psp2P;
    // Уст-ЛУГА вывод
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

    // Ответ хранение вывод
    document.getElementById("loss-kenkiyak4P").value = kenkiyak4P;
    document.getElementById("volume-kenkiyak4").value = kenkiyak4;
    document.getElementById("volume2-kenkiyak4").value = kenkiyak4;
    document.getElementById("loss-kenkiyakTransfer4P").value = kenkiyakTransfer4P;
    document.getElementById("volume-kenkiyakTransfer4").value = kenkiyakTransfer4;
    document.getElementById("loss-zhanazhol4P").value = zhanazhol4P;
    document.getElementById("volume-zhanazhol4").value = zhanazhol4;
    document.getElementById("loss-psp4P").value = psp4P;
    document.getElementById("volume2-psp45first4").value = psp45first4;
    document.getElementById("volume-psp45end4").value = psp45end4;

    // ПНХЗ вывод
    document.getElementById("volume2-atasu5").value = atasu5;
    document.getElementById("loss-pnhzP").value = pnhzP;
    document.getElementById("volume2-pnhz").value = pnhz;
    document.getElementById("loss-atasuTransfer5P").value = atasuTransfer5P;
    document.getElementById("volume-atasuTransfer5").value = atasuTransfer5;
    document.getElementById("volume2-atasuTransfer5").value = atasuTransfer5;
    document.getElementById("loss-dzhumagalieva5P").value = dzhumagalieva5P;
    document.getElementById("volume-dzhumagalieva5").value = dzhumagalieva5;
    document.getElementById("volume2-dzhumagalieva5").value = dzhumagalieva5;
    document.getElementById("loss-kumkol5P").value = kumkol5P;
    document.getElementById("volume-kumkol5").value = kumkol5;
    document.getElementById("volume2-kumkol5").value = kumkol5;
    document.getElementById("loss-kenkiyak5P").value = kenkiyak5P;
    document.getElementById("volume-kenkiyak5").value = kenkiyak5;
    document.getElementById("volume2-kenkiyak5").value = kenkiyak5;
    document.getElementById("loss-kenkiyakTransfer5P").value = kenkiyakTransfer5P;
    document.getElementById("volume-kenkiyakTransfer5").value = kenkiyakTransfer5;
    document.getElementById("loss-zhanazhol5P").value = zhanazhol5P;
    document.getElementById("volume-zhanazhol5").value = zhanazhol5;
    document.getElementById("loss-psp5P").value = psp5P;
    document.getElementById("volume2-psp45first5").value = psp45first5;
    document.getElementById("volume-psp45end5").value = psp45end5;

    // КНР вывод
    document.getElementById("volume-km12356").value = km12356;
    document.getElementById("loss-samara6P").value = samara6P;
    document.getElementById("volume-samara6").value = samara6;
    document.getElementById("volume2-samara6").value = samara6;
    document.getElementById("loss-samaraTransfer6P").value = samaraTransfer6P;
    document.getElementById("volume2-samaraTransfer6").value = samaraTransfer6;
    document.getElementById("volume-samaraTransfer6").value = samaraTransfer6;
    document.getElementById("volume-krasnoarm").value = krasnoarm;
    document.getElementById("loss-krasnoarmP").value = krasnoarmP;
    document.getElementById("volume2-krasnoarm").value = krasnoarm;
    document.getElementById("loss-km915P").value = km915P;
    document.getElementById("volume-km915").value = km915;
    document.getElementById("volume2-km915").value = km915;
    document.getElementById("loss-radionovskiP").value = radionovskiP;
    document.getElementById("volume-radionovski").value = radionovski;
    document.getElementById("volume2-radionovski").value = radionovski;
    document.getElementById("loss-texareckP").value = texareckP;
    document.getElementById("volume-texareck").value = texareck;
    document.getElementById("volume2-texareck").value = texareck;
    document.getElementById("loss-grushavaiP").value = grushavaiP;
    document.getElementById("volume-texareck").value = texareck;
    document.getElementById("volume2-texareck").value = texareck;
    document.getElementById("loss-grushavaiP").value = grushavaiP;
    document.getElementById("volume-grushavai").value = grushavai;
    document.getElementById("volume2-grushavai").value = grushavai;
    document.getElementById("loss-grushavaiTransferP").value = grushavaiTransferP;
    document.getElementById("volume-grushavaiTransfer").value = grushavaiTransfer;
    document.getElementById("volume2-grushavaiTransfer").value = grushavaiTransfer;

    document.getElementById("loss-kasimova6P").value = kasimova6P;
    document.getElementById("volume2-kasimova6").value = kasimova6;
    document.getElementById("volume-kasimova6").value = kasimova6;

    document.getElementById("loss-shmanova6P").value = shmanova6P;
    document.getElementById("volume-shmanova6").value = shmanova6;
    document.getElementById("volume2-shmanova6").value = shmanova6;
    document.getElementById("loss-kenkiyak6P").value = kenkiyak6P;
    document.getElementById("volume-kenkiyak6").value = kenkiyak6;
    document.getElementById("volume2-kenkiyak6").value = kenkiyak6;
    document.getElementById("loss-kenkiyakTransfer6P").value = kenkiyakTransfer6P;
    document.getElementById("volume-kenkiyakTransfer6").value = kenkiyakTransfer6;
    document.getElementById("loss-zhanazhol6P").value = zhanazhol6P;
    document.getElementById("volume-zhanazhol6").value = zhanazhol6;
    document.getElementById("loss-psp6P").value = psp6P;
    document.getElementById("volume2-psp45first6").value = psp45first6;
    document.getElementById("volume-psp45end6").value = psp45end6;
}


