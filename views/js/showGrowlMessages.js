function showSuccessGrowl(message, title = "Successo") {
    $.growl.notice({
        title: title,
        size: "large",
        message: message
    });
}

function showErrorGrowl(message, title = "Errore") {
    $.growl.error({
        title: title,
        size: "large",
        message: message
    });
}

function showWarningGrowl(message, title = "Attenzione") {
    $.growl.warning({
        title: title,
        size: "large",
        message: message
    });
}

function showInfoGrowl(message, title = "Info") {
    $.growl.info({
        title: title,
        size: "large",
        message: message
    });
}
