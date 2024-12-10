<script type="text/javascript">
    let totalRows = 0;
    let currentRows = 0;

    function showImportPanel() {
        $("#importOrderProductsModal").modal('show');
    }

    function initProgressBar(total_rows = 0) {
        totalRows = total_rows;
        currentRows = 0;

        let progressBar = document.getElementById('importProgressBar');
        let currentFetch = document.getElementById('currentFetch');
        let totalFetch = document.getElementById('totalFetch');
        progressBar.style.width = '0%';
        progressBar.setAttribute('aria-valuemin', "0");
        progressBar.setAttribute('aria-valuemax', "100");
        progressBar.setAttribute('aria-valuenow', 0);
        progressBar.textContent = '0%';
        currentFetch.textContent = '0';
        totalFetch.textContent = '0';
    }

    function updateProgressBar(value) {
        currentRows += value;
        let progressBar = document.getElementById('importProgressBar');

        let percentComplete = Math.round((currentRows / totalRows) * 100, 2);
        progressBar.style.width = percentComplete + '%';
        progressBar.setAttribute('aria-valuenow', percentComplete);
        progressBar.textContent = percentComplete + ' %';

        let currentFetch = document.getElementById('currentFetch');
        currentFetch.textContent = currentRows;
        let totalFetch = document.getElementById('totalFetch');
        totalFetch.textContent = totalRows;
    }

    function endProcess() {
        let progressBar = document.getElementById('importProgressBar');
        progressBar.style.width = '100%';
        progressBar.setAttribute('aria-valuenow', 100);
        progressBar.textContent = '100 %';
        $.growl.notice({ title: "Importazione completata", message: "Importazione completata con successo" });
    }

    function hideImportPanel() {
        $("#importOrderProductsModal").modal('hide');
    }

    async function startImport() {
        console.log('--- startImport');
        const data = await getOrdersDetails();
        initProgressBar(data.total);
        importOrdersDetails(data.ordersDetails);
    }

    async function continueImport() {
        console.log('--- continueImport');
        const data = await getOrdersDetails();
        if (data.total == 0 || data.ordersDetails.length == 0) {
            endProcess();
            return;
        }
        importOrdersDetails(data.ordersDetails);
    }

    async function getOrdersDetails() {
        console.log('--- getOrdersDetails');
        const data = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'getOrdersDetails',
            })
        };
        const response = await fetch("{$ajax_controller}&fetch",data);
        const json = await response.json();
        return json;
    }

    async function importOrdersDetails(ordersDetails) {
        console.log('--- importOrdersDetails');
        if (ordersDetails.length === 0) {
            continueImport();
            return;
        }
        const chunk = ordersDetails.splice(0, 250);
        const data_request = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'importOrdersDetails',
                ordersDetails: chunk
            })
        };
        const response = await fetch("{$ajax_controller}&fetch",data_request);
        const json = await response.json();
        const data_response = json;

        updateProgressBar(chunk.length);
        importOrdersDetails(ordersDetails);
    }

    $(function() {
        $("#startImportButton").on('click', function() {
            if (!confirm('Iniziare il processo di importazione ordini (potrebbe durare molto tempo)?')) {
                return false;
            }
            startImport();
        });

        $("#importOrderProductsModal").on('show.bs.modal', function() {
            initProgressBar(0);
        });
    });
</script>