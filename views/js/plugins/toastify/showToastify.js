function showToastify(title, message, type, duration = 3000) {
    let background_color = null;
    let color = null;
    let node = null;

    if (typeof title === "undefined") {
        title = "Avviso";
    }

    if (typeof message === "undefined") {
        message = "Messaggio non specificato";
        type = "danger";
    }

    if (toast) {
        toast.hideToast();
        $(toast).remove();
    }

    if (type == "error") {
        type = "danger";
    }

    if (message instanceof HTMLElement) {
        node = $("<div>").addClass("panel-body").append($("<h4>").text(title)).append(message);
    } else {
        node = $("<div>").addClass("panel-body").append($("<h4>").text(title)).append($("<p>").text(message));
    }

    toast = Toastify({
        text: "",
        node: node[0],
        duration: duration,
        destination: "",
        newWindow: true,
        className: "",
        close: false,
        gravity: "top", // `top` or `bottom`
        position: "center", // `left`, `center` or `right`
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style: {
            background: "var(--toastify-color-" + type + ")",
            color: "var(--toastify-color-light)"
        },
        offset: {
            x: "0", // horizontal axis - can be a number or a string indicating unity. eg: '2em'
            y: "400" // vertical axis - can be a number or a string indicating unity. eg: '2em'
        },
        onClick: function (e) {
            toast.hideToast();
            $(toast).remove();
        } // Callback after click
    });

    toast.showToast();
}
