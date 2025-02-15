export const movementHandler = {
    async saveMovement(e, action = 'editMovement', adminControllerUrl) {
        e.preventDefault();
        e.stopImmediatePropagation();
        e.stopPropagation();

        const formElem = document.getElementById('edit-movement-form');
        const formData = new FormData(formElem);
        formData.append('ajax', 1);
        formData.append('action', action);
        formData.append('id_document', $("#edit-id_document").val());
        formData.append('document_number', $("#edit-document_number").val());
        formData.append('document_date', $("#edit-document_date").val());

        const result = await Swal.fire({
            title: 'Conferma modifica movimento?',
            text: "Questo movimento verrà modificato!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'SI',
            cancelButtonText: 'Annulla'
        });

        if (result.isConfirmed) {
            const response = await fetch(
                adminControllerUrl,
                {
                    method: 'POST',
                    body: new URLSearchParams(Object.fromEntries(formData)),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }
            );

            const jsonResult = await response.json();
            if (jsonResult.status == true) {
                await Swal.fire(
                    'Modificato!',
                    'Il movimento è stato salvato correttamente.',
                    'success'
                );
                $('#editMovementModal').modal('hide');
            } else {
                await Swal.fire(
                    'Errore!',
                    jsonResult.error_msg,
                    'error'
                );
            }
        }
    }
};
