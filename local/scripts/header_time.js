$(document).ready(function () {
    function timer() {
        $('#timer').html(moment().subtract(6, 'hours').format('HH:mm'));
    }
    timer()
    setInterval(timer, 1000)
});