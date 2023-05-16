$(function() {
    let start = moment()
    start = start.subtract(60, 'days')
    start = start.format('DD.MM.YYYY')
    let end = moment().format('DD.MM.YYYY')
    $('#date-range').daterangepicker({
        startDate: start,
        endDate: end,
        locale: {
            format: 'DD.MM.YYYY',
        }
    });
});