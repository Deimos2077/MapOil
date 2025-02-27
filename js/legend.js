document.addEventListener('DOMContentLoaded', function () {
    const legend = document.getElementById('legend');
    const legendHeader = document.getElementById('legend-header');
    const legendContent = document.getElementById('legend-content');
    const toggleIcon = document.getElementById('legend-toggle');

    function collapseLegend() {
        legendContent.style.maxHeight = "0px";
        legendContent.style.opacity = "0";
        toggleIcon.style.transform = "rotate(180deg)";
    }

    function expandLegend() {
        legendContent.style.maxHeight = legendContent.scrollHeight + "px";
        legendContent.style.opacity = "1";
        toggleIcon.style.transform = "rotate(0deg)";
    }

    // Проверяем текущее состояние
    if (legend.classList.contains('collapsed')) {
        collapseLegend();
    } else {
        expandLegend();
    }

    legendHeader.addEventListener('click', function () {
        if (legend.classList.contains('collapsed')) {
            expandLegend();
        } else {
            collapseLegend();
        }

        legend.classList.toggle('collapsed');
    });
});

