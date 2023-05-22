<?php
global $APPLICATION;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Канбан");
const HANDLER_PATH = '/local/php_interface/lib/Controllers/';
const HANDLER_NAME = 'ControllerHandler.php';
?>
    <script src="/local/scripts/date_picker.min.js"></script>
    <script>
        async function setData() {
            let startDate = $('#date-range').val().split('-')[0]
            let endDate = $('#date-range').val().split('-')[1]
            let status = $('#status-filter').val()
            $.ajax({
                type: 'POST',
                url: '<?=HANDLER_PATH . HANDLER_NAME?>',
                cache: false,
                data: {
                    'ACTION': 'getKanbanList',
                    'ENTITY': 'orders',
                    'date-from': startDate,
                    'date-to': endDate,
                    'status': status,
                },
            }).done(function(response) {
                if (Object.keys(response).length === $('.card').length) {
                    return;
                }
                $('.card').remove()
                response.forEach(e => {
                    renderCard(e);
                })
                $('.card').draggable()
            })
        }
        async function updateCard(id) {
            $.ajax({
                type: 'POST',
                url: '<?=HANDLER_PATH . HANDLER_NAME?>',
                cache: false,
                data: {
                    'ACTION': 'getKanbanCard',
                    'ENTITY': 'orders',
                    'ID': id,
                },
            }).done(function(response) {
                let elem = response[0]
                renderCard(elem)
                $('.card').draggable()
            })
        }
        function renderCard(elem) {
            $(`#${elem.id}`).remove()
            if (!['Новая', 'Запланирована', 'В работе'].includes(elem['status']))
                return;
            let products = []
            elem['products'].forEach(product => {
                products.push('<a class="card-text order-product" ' +
                    'href="/products/detail/index.php?ID=' + product['ID'] +
                    '" style="text-decoration: none; color: black;">'+
                    product['NAME'] + '</a>')
            })
            elem['products'] = products;
            switch (elem['status']) {
                case 'Новая':
                    $('#3').append(
                        '<div class="card mb-2" id="' + elem.id + '" style="width: 15rem; font-size: 14px;"> ' +
                        '<div class="card-body"> ' +
                        '<h5 class="card-title">#' + elem.id + '</h5> ' +
                        '<h6 class="card-subtitle mb-2 text-muted">' + elem['date_receive'] + '</h6> ' +
                        '<a class="card-text order-client" ' +
                        'href="/clients/detail/index.php?ID=' + elem['client']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;"> ' + elem['client']['NAME'] + '</a><br>' +
                        '<a class="card-text order-car" ' +
                        'href="/cars/detail/index.php?ID=' + elem['car']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;">' +
                        'VIN: ' + elem['car']['NAME'] + ' </a><br>' +
                        '<span class="card-text order-products">Услуги: ' + elem['products'].join(', ') + '</span><br> ' +
                        '<a href="" class="card-link reject">Отклонить</a></div></div>'
                    );
                    break;
                case 'Запланирована':
                    $('#5').append(
                        '<div class="card mb-2" draggable="true" id="' + elem.id + '" style="width: 15rem; font-size: 14px;"> ' +
                        '<div class="card-body"> ' +
                        '<h5 class="card-title">#' + elem.id + '</h5> ' +
                        '<h6 class="card-subtitle mb-2 text-muted">' + elem['date_receive'] + '</h6> ' +
                        '<a class="card-text order-client" ' +
                        'href="/clients/detail/index.php?ID=' + elem['client']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;"> ' +
                        elem['client']['NAME'] +
                        '</a> <br>' +
                        '<a class="card-text order-car" ' +
                        'href="/cars/detail/index.php?ID=' + elem['car']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;">' +
                        'VIN: ' + elem['car']['NAME'] + ' </a><br>' +
                        '<span class="card-text order-products">Услуги: ' + elem['products'].join(', ') + '</span><br> '
                    );
                    break;
                case 'В работе':
                    $('#6').append(
                        '<div class="card mb-2" id="' + elem.id + '" style="width: 15rem; font-size: 14px;"> ' +
                        '<div class="card-body"> ' +
                        '<h5 class="card-title">#' + elem.id + '</h5> ' +
                        '<h6 class="card-subtitle mb-2 text-muted">' + elem['date_receive'] + '</h6> ' +
                        '<a class="card-text order-client" ' +
                        'href="/clients/detail/index.php?ID=' + elem['client']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;"> ' + elem['client']['NAME'] + '</a> <br>' +
                        '<a class="card-text order-car" ' +
                        'href="/cars/detail/index.php?ID=' + elem['car']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;">' +
                        'VIN: ' + elem['car']['NAME'] + ' </a><br>' +
                        '<span class="card-text order-products">Услуги: ' + elem['products'].join(', ') + '</span><br> ' +
                        `<a href="" class="card-link end-button" data-element-id="${elem.id}">Завершить</a></div></div>`
                    );
                    break;
            }
            $('.card').off( "click", ".reject")
            $('.card').off( "click", ".end-button")
            $('.reject').on('click', function(events) {
                events.preventDefault();
                const orderID = $(this).parent().parent().attr('id')
                $('.reject-dialog').dialog('option', 'title', 'Отклонение заявки #' + orderID)
                $('.reject-dialog').dialog('open')
            })
            $('.end-button').on('click', function(events) {
                events.preventDefault();
                let id = $(this).data('element-id')
                $('.end-dialog').dialog('option', 'title', 'Завершение заказа #' + id);
                $('.end-dialog').dialog('open')
            })
        }
        $(document).ready(function () {
            setData();
            setInterval(setData, 30000);
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
                    }).done(() => {
                        updateCard(item.attr('id'))
                    });
                }
            });
            $('.reject-dialog').dialog({
                autoOpen: false,
                modal: true,
                resizable: false,
                draggable: false,
                width: 500,
                maxHeight: 'auto',
                buttons: [
                    {
                        text: 'Подтвердить',
                        click: function() {
                            let id = $(this).dialog('option', 'title').split('#')[1]
                            $.ajax({
                                method: "POST",
                                url: "/local/scripts/updateOrderStatus.php",
                                data: { ID: id, STATUS: 4 }
                            });
                            updateCard(id)
                            $(this).dialog('close');
                        }
                    },
                    {
                        text: 'Отменить',
                        click: function() {
                            $(this).dialog('close');
                        }
                    }
                ]
            })
            $('.end-dialog').dialog({
                autoOpen: false,
                modal: true,
                resizable: false,
                draggable: false,
                width: 500,
                maxHeight: 'auto',
                buttons: [
                    {
                        text: 'Подтвердить',
                        click: function() {
                            let id = $(this).dialog('option', 'title').split('#')[1]
                            $.ajax({
                                type: 'POST',
                                url: '/local/scripts/getOrderReport.php',
                                cache: false,
                                data: {
                                    'ID': id
                                }
                            }).done(function(response) {
                                let path = '/include/docs/'
                                let preview = response.slice(0, -4) + '.png'
                                let file = response.slice(0, -4) + '.docx'
                                $('#preview-img').attr('src', path + preview)
                                $('#filename-download').attr('value', file)
                                $.ajax({
                                    method: "POST",
                                    url: "/local/scripts/updateOrderStatus.php",
                                    data: { ID: id, STATUS: 8 }
                                });
                                updateCard(id)
                                $('.preview-dialog').dialog('open')
                            })
                            $(this).dialog('close');
                        }
                    },
                    {
                        text: 'Отменить',
                        click: function() {
                            $(this).dialog('close');
                        }
                    }
                ]
            })
            $('.preview-dialog').dialog({
                autoOpen: false,
                modal: true,
                resizable: false,
                draggable: false,
                width: 800,
                position:["center",20],
                minHeight:"auto",
                buttons: [
                    {
                        text: 'Сохранить',
                        click: function() {
                            let filename = $('#filename-download').val()
                            window.location.href = "/local/scripts/downloadOrderReport.php?FILE=" + filename
                        }
                    },
                    {
                        text: 'Отменить',
                        click: function() {
                            $(this).dialog('close');
                        }
                    }
                ]
            })
        })
    </script>
    <ul class="list-inline mx-1">
        <li class="list-inline-item"><a class="text-center" style="text-decoration: none;" href="/orders">Список</a></li>
        <li class="list-inline-item">
            <a class="text-center"
            <?php if ($_SERVER['REQUEST_URI'] != '/orders/kanban/' ): ?>
               style="text-decoration: none;"
            <?php endif;?>
               href="/orders/kanban">
                Канбан
            </a>
        </li>
    </ul>
    <form class="row py-3 mb-3 align-items-end bg-light" onsubmit="return false;">
        <div class="col-auto">
            <label for="date-range" class="form-label">Выберите дату или период</label>
            <input id="date-range" class="form-control" readonly>
        </div>
        <div class="col-auto">
            <label for="status-filter" class="form-label">Статус заказа</label>
            <select id="status-filter" class="form-select" aria-label="Default select example">
                <option disabled selected>Статус заказа</option>
                <option>Новая</option>
                <option>Отклонена</option>
                <option>Запланирована</option>
                <option>В работе</option>
                <option>Рекламация</option>
                <option>Завершена</option>
            </select>
        </div>
        <div class="col-auto">
            <button id="submit" type="submit" class="btn btn-primary">Применить</button>
        </div>
    </form>

    <div class="container row justify-content-center kanban" style="min-height: 100vh;">
        <div class="col status" id="3">
            <h5>Новые</h5>
        </div>
        <div class="col status" id="5">
            <h5>Запланированы</h5>
        </div>
        <div class="col status" id="6">
            <h5>В работе</h5>
        </div>
    </div>

    <div class="reject-dialog">
        <p>Подтвердите действие, чтобы отклонить заявку.</p>
<!--        <p>Оставьте для клиента комментарий по отказу:</p>-->
<!--        <textarea id="comment" class="form-control" placeholder="Напишите комментарий к заказу"></textarea>-->
    </div>
    <div class="end-dialog">
        <p>Подтвердите действие, чтобы завершить работу над заявкой.</p>
<!--        <p>Оставьте для клиента комментарий по отказу:</p>-->
<!--        <textarea id="comment" class="form-control" placeholder="Напишите комментарий к заказу"></textarea>-->
    </div>
    <div class="preview-dialog">
        <img id="preview-img" alt="Предпросмотр отчета" src="">
        <input type="hidden" value="" id="filename-download">
    </div>
    <script src="/local/scripts/kanban_dnd.js"></script>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>