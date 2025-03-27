document.addEventListener("DOMContentLoaded", function () {
    let inputs = document.querySelectorAll("input");

    // Обрабатываем ввод: при потере фокуса или нажатии Enter пересчитываем
    inputs.forEach(input => {
        // input.addEventListener("blur", function () {
        //     handleInput(this.id);
        // });

        input.addEventListener("keypress", function (event) {
            if (event.key === "Enter") {
                handleInput(this.id);
            }
        });
    });

    calculateFrom(); // Первичный расчёт при загрузке страницы
});

function handleInput(inputId) {
    if (["loss-pkopP", "loss-kenkiyakP", "loss-kenkiyakTransferP", "loss-pspP", "loss-zhanazholP"].includes(inputId)) {
        calculateFrom(inputId);
    } else {
        calculateFrom();
    }
}

function calculateFrom(startId = null) {
    // Получаем исходные значения
    let pkop = parseFloat(document.getElementById("volume2-pkop").value) || 0;
    let zhanazholedit = parseFloat(document.getElementById("volume2-zhanazholedit").value) || 0;

    // Получаем проценты
    let psp45PP = parseFloat(document.getElementById("percent-psp45PP").value) || 0;
    let zhanazholPP = parseFloat(document.getElementById("percent-zhanazholPP").value) || 0;
    let kenkiyakTransferPP = parseFloat(document.getElementById("percent-kenkiyakTransferPP").value) || 0;
    let kenkiyakPP = parseFloat(document.getElementById("percent-kenkiyakPP").value) || 0;
    let pkopPP = parseFloat(document.getElementById("percent-pkopPP").value) || 0;

    // Получаем сохранённые значения или вычисляем по формуле
    let pkopP = parseFloat(document.getElementById("loss-pkopP").value) || Math.round(pkopPP * (pkop / 100));
    let kumkol = pkop + pkopP;
    let kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || Math.round(kenkiyakPP * (kumkol / 100));
    let kenkiyak = kumkol + kenkiyakP;
    let kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || Math.round(kenkiyakTransferPP * (kenkiyak / 100));
    let kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
    let zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || Math.round(zhanazholPP * (zhanazholedit / 100));
    let zhanazhol = zhanazholedit + zhanazholP;
    let psp45first = kenkiyakTransfer - zhanazhol;
    let pspP = parseFloat(document.getElementById("loss-pspP").value) || Math.round(psp45PP * (psp45first / 100));
    let psp45end = psp45first + pspP;

    // 🔹 Динамическое обновление значений по цепочке
    if (startId === "loss-pkopP") {
    pkopP = parseFloat(document.getElementById("loss-pkopP").value) || 0;
    kumkol = pkop + pkopP;

    // Обновляем процент
    let newPercent = (pkopP / pkop) * 100;
    document.getElementById("percent-pkopPP").value = newPercent.toFixed(2);

    kenkiyakP = Math.round(kenkiyakPP * (kumkol / 100));
    kenkiyak = kumkol + kenkiyakP;
    kenkiyakTransferP = Math.round(kenkiyakTransferPP * (kenkiyak / 100));
    kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
    psp45first = kenkiyakTransfer - zhanazhol;
    pspP = Math.round(psp45PP * (psp45first / 100));
    psp45end = psp45first + pspP;
}

if (startId === "loss-kenkiyakP") {
    kenkiyakP = parseFloat(document.getElementById("loss-kenkiyakP").value) || 0;
    kenkiyak = kumkol + kenkiyakP;

    // Обновляем процент
    let newPercent = (kenkiyakP / kumkol) * 100;
    document.getElementById("percent-kenkiyakPP").value = newPercent.toFixed(2);

    kenkiyakTransferP = Math.round(kenkiyakTransferPP * (kenkiyak / 100));
    kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
    psp45first = kenkiyakTransfer - zhanazhol;
    pspP = Math.round(psp45PP * (psp45first / 100));
    psp45end = psp45first + pspP;
}

if (startId === "loss-kenkiyakTransferP") {
    kenkiyakTransferP = parseFloat(document.getElementById("loss-kenkiyakTransferP").value) || 0;
    kenkiyakTransfer = kenkiyak + kenkiyakTransferP;

    // Обновляем процент
    let newPercent = (kenkiyakTransferP / kenkiyak) * 100;
    document.getElementById("percent-kenkiyakTransferPP").value = newPercent.toFixed(2);

    psp45first = kenkiyakTransfer - zhanazhol;
    pspP = Math.round(psp45PP * (psp45first / 100));
    psp45end = psp45first + pspP;
}

if (startId === "loss-zhanazholP") {
    zhanazholP = parseFloat(document.getElementById("loss-zhanazholP").value) || 0;
    zhanazhol = zhanazholedit + zhanazholP;

    // Обновляем процент
    let newPercent = (zhanazholP / zhanazholedit) * 100;
    document.getElementById("percent-zhanazholPP").value = newPercent.toFixed(2);

    psp45first = kenkiyakTransfer - zhanazhol;
    pspP = Math.round(psp45PP * (psp45first / 100));
    psp45end = psp45first + pspP;
}

if (startId === "loss-pspP") {
    pspP = parseFloat(document.getElementById("loss-pspP").value) || 0;
    psp45end = psp45first + pspP;

    // Обновляем процент
    let newPercent = (pspP / psp45first) * 100;
    document.getElementById("percent-psp45PP").value = newPercent.toFixed(2);
}


    // 🔹 Заполняем только изменённые значения
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
}