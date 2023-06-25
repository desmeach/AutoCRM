$(document).ready(function () {
    function timer() {
        $('#timer').html(moment().format('HH:mm'));
    }
    timer()
    setInterval(timer, 1000)
});