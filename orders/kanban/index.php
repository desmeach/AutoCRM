<?php
global $APPLICATION;

use lib\Controllers\MastersController;
use lib\Controllers\ProductsController;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Канбан");
const HANDLER_PATH = '/local/php_interface/lib/Controllers/';
const HANDLER_NAME = 'ControllerHandler.php';

$products = ProductsController::getList();
$masters = MastersController::getList();
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
                $('.card').draggable({
                    cursor: "grabbing",
                })
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
                $('.card').draggable({
                    cursor: "grabbing",
                })
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
                        '<h5 class="card-title">' +
                        '<a class="card-text order-id" ' +
                        'href="/orders/detail/index.php?ID=' + elem.id + '" ' +
                        'style="text-decoration: none; color: black;">' + '#' + elem.id + '</a>' +
                        '<span style="font-size: 14px;" class="float-end card-text order-car-model">' + elem['car']['BRAND']['VALUE'] +
                        ' </span><br>' + '</h5> ' +
                        '<h6 class="card-subtitle mb-2 text-muted">' + elem['date_receive'] + '</h6> ' +
                        '<a class="card-text order-client" ' +
                        'href="/clients/detail/index.php?ID=' + elem['client']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;"> ' + elem['client']['NAME'] + '</a><br>' +
                        '<a class="card-text order-car" ' +
                        'href="/cars/detail/index.php?ID=' + elem['car']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;">' +
                        'VIN: ' + elem['car']['NAME'] + ' </a><br>' +
                        '<span class="card-text order-products">Услуги: ' + elem['products'].join(', ') + '</span><br> ' +
                        `<a href="" class="card-link reject" data-element-id="${elem.id}">Отклонить</a></div></div>`
                    );
                    break;
                case 'Запланирована':
                    $('#5').append(
                        '<div class="card mb-2" draggable="true" id="' + elem.id + '" style="width: 15rem; font-size: 14px;"> ' +
                        '<div class="card-body"> ' +
                        '<h5 class="card-title">' +
                        '<a class="card-text order-id" ' +
                        'href="/orders/detail/index.php?ID=' + elem.id + '" ' +
                        'style="text-decoration: none; color: black;">' + '#' + elem.id + '</a>' +
                        '<span style="font-size: 14px;" class="float-end card-text order-car-model">' + elem['car']['BRAND']['VALUE'] +
                        ' </span><br>' + '</h5> ' +
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
                        '<span class="card-text order-manager">' +
                        'Менеджер: ' + elem['manager']['LAST_NAME'] + ' ' + elem['manager']['NAME'] + ' </span><br>' +
                        '<span class="card-text order-products">Услуги: ' + elem['products'].join(', ') + '</span><br> '
                    );
                    break;
                case 'В работе':
                    $('#6').append(
                        '<div class="card mb-2" id="' + elem.id + '" style="width: 15rem; font-size: 14px;"> ' +
                        '<div class="card-body"> ' +
                        '<h5 class="card-title">' +
                        '<a class="card-text order-id" ' +
                        'href="/orders/detail/index.php?ID=' + elem.id + '" ' +
                        'style="text-decoration: none; color: black;">' + '#' + elem.id + '</a>' +
                        '<span style="font-size: 14px;" class="float-end card-text order-car-model">' + elem['car']['BRAND']['VALUE'] +
                        ' </span><br>' + '</h5> ' +
                        '<h6 class="card-subtitle mb-2 text-muted">' + elem['date_receive'] + '</h6> ' +
                        '<a class="card-text order-client" ' +
                        'href="/clients/detail/index.php?ID=' + elem['client']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;"> ' + elem['client']['NAME'] + '</a> <br>' +
                        '<a class="card-text order-car" ' +
                        'href="/cars/detail/index.php?ID=' + elem['car']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;">' +
                        'VIN: ' + elem['car']['NAME'] + ' </a><br>' +
                        '<span class="card-text order-manager">' +
                        'Менеджер: ' + elem['manager']['LAST_NAME'] + ' ' + elem['manager']['NAME'] + ' </span><br>' +
                        '<a class="card-text order-master" ' +
                        'href="/masters/detail/index.php?ID=' + elem['master']['ID'] + '" ' +
                        'style="text-decoration: none; color: black;">' +
                        'Мастер: ' + elem['master']['NAME'] + ' </a><br>' +
                        '<span class="card-text order-products">Услуги: ' + elem['products'].join(', ') + '</span><br> ' +
                        `<a href="" class="card-link end-button" data-element-id="${elem.id}">Завершить</a></div></div>`
                    );
                    break;
            }
            $('.card').off( "click", ".reject")
            $('.card').off( "click", ".end-button")
            $('.reject').on('click', function(events) {
                events.preventDefault();
                const id = $(this).data('element-id')
                $('#reject-dialog-title').text('Завершение заказа #' + id)
                $('#manager-message-text').val('');
                $('#reject-dialog').modal('show')
            })
            $('.end-button').on('click', function(events) {
                events.preventDefault();
                let id = $(this).data('element-id')
                $('#end-dialog-title').text('Завершение заказа #' + id)
                $('#products-list').val('default');
                $('#products-list').selectpicker('refresh');
                $('#master-message-text').val('');
                $('#end-dialog').modal('show')
            })
        }
        $(document).ready(function () {
            setData();
            setInterval(setData, 5000);
            $('.status').droppable({
                drop: function (event, ui) {
                    let item = $(ui.draggable)
                    let status = event.target.id
                    let id = item.attr('id')
                    if (!item.attr('class').includes('card')) {
                        return
                    }
                    item.css({
                        'left': 0,
                        'top': 0,
                    })
                    event.target.append(item[0])
                    if (Number(status) === 6) {
                        $('#start-dialog-title').text('Начало работы над заказом #' + id)
                        $('#masters-list').val('default');
                        $('#masters-list').selectpicker('refresh');
                        $('#start-dialog').modal('show')
                        return
                    }
                    $.ajax({
                        method: "POST",
                        url: "/local/scripts/updateOrderStatus.php",
                        data: { ID: id, STATUS: status }
                    }).done(() => {
                        updateCard(id)
                    });
                }
            });
            $('#reject-submit').on('click', function () {
                let id = $('#reject-dialog-title').text().split('#')[1]
                let comment = $('#manager-message-text').val()
                $.ajax({
                    method: "POST",
                    url: "/local/scripts/updateOrderStatus.php",
                    data: {
                        ID: id,
                        STATUS: 4,
                        MANAGER_COMMENT: comment,
                    }
                }).done(function () {
                    $('#reject-dialog').modal('hide')
                    updateCard(id)
                });
            })
            $('#start-submit').on('click', function () {
                let id = $('#start-dialog-title').text().split('#')[1]
                let master = $('#masters-list').val()
                if (!master) {
                    updateCard(id)
                    $('#start-dialog').modal('hide')
                    return
                }
                $.ajax({
                    method: "POST",
                    url: "/local/scripts/updateOrderStatus.php",
                    data: {
                        ID: id,
                        STATUS: 6,
                        MASTER: master,
                    }
                }).done(function () {
                    $('#start-dialog').modal('hide')
                    updateCard(id)
                });
            })
            $('#end-submit').on('click', function () {
                let id = $('#end-dialog-title').text().split('#')[1]
                let comment = $('#master-message-text').val()
                let products = $('#products-list').val()
                let mileage = $('#mileage-text').val()
                var $body = $("body");
                $(document).on({
                    ajaxStart: function() { $body.addClass("loading"); },
                    ajaxStop: function() { $body.removeClass("loading"); }
                });
                $.ajax({
                    method: "POST",
                    url: "/local/scripts/updateOrderStatus.php",
                    data: {
                        ID: id,
                        STATUS: 8,
                        MASTER_COMMENT: comment,
                        PRODUCTS: products,
                        MILEAGE: mileage,
                    }
                }).done(function () {
                    $.ajax({
                        type: 'POST',
                        url: '/local/scripts/getOrderReport.php',
                        cache: false,
                        data: {
                            'ID': id
                        }
                    }).done(function (response) {
                        let path = '/include/docs/'
                        console.log(response)
                        let previewFirst = response + '-0001.jpg'
                        let previewSecond = response + '-0002.jpg'
                        let file = response + '.docx'
                        console.log(path + previewFirst)
                        $('#preview-img-1').attr('src', path + previewFirst)
                        $('#preview-img-2').attr('src', path + previewSecond)
                        $('#filename-download').attr('value', file)
                        updateCard(id)
                        $('#end-dialog').modal('hide')
                        $('#preview-dialog').modal('show')
                        $(document).off('ajaxStart')
                        $(document).off('ajaxEnd')
                    });
                })
            })
            $('#preview-submit').on('click', function () {
                let filename = $('#filename-download').val()
                window.location.href = "/local/scripts/downloadOrderReport.php?FILE=" + filename
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

    <div class="modal fade" id="reject-dialog" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reject-dialog-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reject-form">
                        <p>Подтвердите действие, чтобы отклонить заявку.</p>
                        <div class="form-group">
                            <label for="manager-message-text" class="col-form-label">Оставьте комментарий по отказу:</label>
                            <textarea class="form-control" name="manager-comment" id="manager-message-text"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отклонить</button>
                    <button type="button" class="btn btn-primary" id="reject-submit">Подтвердить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="start-dialog" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-xl-down" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="start-dialog-title">Начало работы над заказ-нарядом</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="start-form">
                        <div class="form-group">
                            <label for="masters-list" class="col-form-label">
                                Укажите мастера, чтобы начать работу над заказ-нарядом:
                            </label>
                            <select class="form-control" id="masters-list">
                                <?php foreach ($masters as $master): ?>
                                    <option value="<?=$master['ID']?>"><?=$master['NAME']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отклонить</button>
                    <button type="button" class="btn btn-primary" id="start-submit">Подтвердить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="end-dialog" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="end-dialog-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="products-list" class="col-form-label">Выберите дополнительные работы (если были проведены):</label>
                            <select multiple class="form-control selectpicker" id="products-list">
                                <?php foreach ($products as $product): ?>
                                    <option value="<?=$product['ID']?>"><?=$product['NAME']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mileage-text" class="col-form-label">Укажите итоговый пробег:</label>
                            <input class="form-control" type="number" id="mileage-text">
                        </div>
                        <div class="form-group">
                            <label for="master-message-text" class="col-form-label">Оставьте комментарий мастера:</label>
                            <textarea class="form-control" id="master-message-text"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отклонить</button>
                    <button type="button" class="btn btn-primary" id="end-submit">Подтвердить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="preview-dialog" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="preview-dialog-title">Предпросмотр отчета</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img class="mx-auto d-block" width="800" id="preview-img-1" alt="Предпросмотр отчета" src="">
                    <img class="mx-auto d-block" width="800" id="preview-img-2" alt="Предпросмотр отчета" src="">
                    <input type="hidden" value="" id="filename-download">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отклонить</button>
                    <button type="button" class="btn btn-primary" id="preview-submit">Подтвердить</button>
                </div>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>