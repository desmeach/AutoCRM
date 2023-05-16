let card = $('.card')
let status = $('.status')
card.data({
    'originalLeft': card.css('left'),
    'originalTop': card.css('top')
});
card.draggable({
    cursor: "grabbing",
    stop: function() {
        $(this).css({
            'left': $(this).data().originalLeft,
            'top': $(this).data().originalTop,
        })
    }
});
status.droppable({
    drop: function (event, ui) {
        let item = $(ui.draggable)
        if (!item.attr('class').includes('card'))
            return
        item.css({
            'left': 0,
            'top': 0,
        })
        event.target.append(item[0])
        $.ajax({
            method: "POST",
            url: "/local/scripts/updateOrderStatus.php",
            data: { ID: item.attr('id'), STATUS: event.target.id }
        });
    }
});