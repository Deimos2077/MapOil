document.addEventListener("DOMContentLoaded", function () {
    let inputs = document.querySelectorAll("input");

    // Обрабатываем ввод: при потере фокуса или нажатии Enter пересчитываем
    inputs.forEach(input => {
        input.addEventListener("blur", function () {
            handleInput(this.id);
        });

        input.addEventListener("keypress", function (event) {
            if (event.key === "Enter") {
                this.blur();
            }
        });
    });

    calculateFrom(); // Первичный расчёт при загрузке страницы
});

function handleInput(inputId) {
    if (["pkopP", "kenkiyakP", "kenkiyakTransferP", "pspP", "zhanazholP"].includes(inputId)) {
        calculateFrom(inputId);
    } else {
        calculateFrom();
    }
}

function calculateFrom(startId = null) {
    // Получаем исходные значения
    let pkop = parseFloat(document.getElementById("pkop").value) || 0;
    let zhanazholedit = parseFloat(document.getElementById("zhanazholedit").value) || 0;

    // Получаем проценты
    let psp45PP = parseFloat(document.getElementById("psp45PP").value) || 0;
    let zhanazholPP = parseFloat(document.getElementById("zhanazholPP").value) || 0;
    let kenkiyakTransferPP = parseFloat(document.getElementById("kenkiyakTransferPP").value) || 0;
    let kenkiyakPP = parseFloat(document.getElementById("kenkiyakPP").value) || 0;
    let pkopPP = parseFloat(document.getElementById("pkopPP").value) || 0;

    // Получаем сохранённые значения или вычисляем по формуле
    let pkopP = parseFloat(document.getElementById("pkopP").value) || Math.round(pkopPP * (pkop / 100));
    let kumkol = pkop + pkopP;
    let kenkiyakP = parseFloat(document.getElementById("kenkiyakP").value) || Math.round(kenkiyakPP * (kumkol / 100));
    let kenkiyak = kumkol + kenkiyakP;
    let kenkiyakTransferP = parseFloat(document.getElementById("kenkiyakTransferP").value) || Math.round(kenkiyakTransferPP * (kenkiyak / 100));
    let kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
    let zhanazholP = parseFloat(document.getElementById("zhanazholP").value) || Math.round(zhanazholPP * (zhanazholedit / 100));
    let zhanazhol = zhanazholedit + zhanazholP;
    let psp45first = kenkiyakTransfer - zhanazhol;
    let pspP = parseFloat(document.getElementById("pspP").value) || Math.round(psp45PP * (psp45first / 100));
    let psp45end = psp45first + pspP;

    // 🔹 Динамическое обновление значений по цепочке
    if (startId === "pkopP") {
        pkopP = parseFloat(document.getElementById("pkopP").value) || 0;
        kumkol = pkop + pkopP;
        kenkiyakP = Math.round(kenkiyakPP * (kumkol / 100));
        kenkiyak = kumkol + kenkiyakP;
        kenkiyakTransferP = Math.round(kenkiyakTransferPP * (kenkiyak / 100));
        kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = Math.round(psp45PP * (psp45first / 100));
        psp45end = psp45first + pspP;
    }

    if (startId === "kenkiyakP") {
        pkopP = parseFloat(document.getElementById("pkopP").value) || 0;
        kenkiyakP = parseFloat(document.getElementById("kenkiyakP").value) || 0;
        kenkiyak = kumkol + kenkiyakP;
        kenkiyakTransferP = Math.round(kenkiyakTransferPP * (kenkiyak / 100));
        kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = Math.round(psp45PP * (psp45first / 100));
        psp45end = psp45first + pspP;
    }

    if (startId === "kenkiyakTransferP") {
        pkopP = parseFloat(document.getElementById("pkopP").value) || 0;
        kenkiyakP = parseFloat(document.getElementById("kenkiyakP").value) || 0;
        kenkiyakTransferP = parseFloat(document.getElementById("kenkiyakTransferP").value) || 0;
        kenkiyakTransfer = kenkiyak + kenkiyakTransferP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = Math.round(psp45PP * (psp45first / 100));
        psp45end = psp45first + pspP;
    }

    if (startId === "zhanazholP") {
        zhanazholP = parseFloat(document.getElementById("zhanazholP").value) || 0;
        zhanazhol = zhanazholedit + zhanazholP;
        psp45first = kenkiyakTransfer - zhanazhol;
        pspP = Math.round(psp45PP * (psp45first / 100));
        psp45end = psp45first + pspP;
    }

    if (startId === "pspP") {
        pkopP = parseFloat(document.getElementById("pkopP").value) || 0;
        kenkiyakP = parseFloat(document.getElementById("kenkiyakP").value) || 0;
        kenkiyakTransferP = parseFloat(document.getElementById("kenkiyakTransferP").value) || 0;
        pspP = parseFloat(document.getElementById("pspP").value) || 0;
        psp45end = psp45first + pspP;
    }

    // 🔹 Заполняем только изменённые значения
    document.getElementById("psp45end").value = psp45end;
    document.getElementById("pspP").value = pspP;
    document.getElementById("psp45first").value = psp45first;
    document.getElementById("zhanazhol").value = zhanazhol;
    document.getElementById("zhanazholP").value = zhanazholP;
    document.getElementById("kenkiyakTransfer").value = kenkiyakTransfer;
    document.getElementById("kenkiyakTransferP").value = kenkiyakTransferP;
    document.getElementById("kenkiyak-first").value = kenkiyak;
    document.getElementById("kenkiyak-second").value = kenkiyak;
    document.getElementById("kumkol-first").value = kumkol;
    document.getElementById("kumkol-second").value = kumkol;
    document.getElementById("kenkiyakP").value = kenkiyakP;
    document.getElementById("pkopP").value = pkopP;
}