export const utils = {
    setBgColor(data, type, row) {
        let bg = 'info';
        if (Number(data) < 0) {
            bg = 'danger';
        } else if (Number(data) == 0) {
            bg = 'warning';
        } else {
            bg = 'info';
        }

        return `<span class="pull-right text-${bg}">${data}</span>`;
    },

    formatCurrency(data) {
        let bg = 'info';
        let value = Number(data).toLocaleString("it-IT", {
            maximumFractionDigits: 2,
            minimumFractionDigits: 2
        });

        if (Number(data) < 0) {
            bg = 'danger';
        } else if (Number(data) == 0) {
            bg = 'warning';
        } else {
            bg = 'success';
        }
        return `<span class="pull-right text-${bg}">${value} EUR</span>`;
    }
};
