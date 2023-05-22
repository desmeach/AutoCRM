<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Аналитика");

use lib\Controllers\ManagersController;
use lib\Controllers\MastersController;
use lib\Controllers\ProductsController;
use lib\Statistics\ClientsStatistic;
use lib\Controllers\BranchesController;

const HANDLER_PATH = '/local/php_interface/lib/Controllers/';
const HANDLER_NAME = 'ControllerHandler.php';

$branches = BranchesController::getList();
$products = ProductsController::getList();
$managers = ManagersController::getList();
$masters = MastersController::getList();
$clientsStatistic = ClientsStatistic::getStatistic();
?>
    <script src='/local/scripts/date_picker.min.js'></script>
    <script>
        let table;
        async function setTableData() {
            table.clear().draw()
            let startDate = $('#date-range').val().split('-')[0]
            let endDate = $('#date-range').val().split('-')[1]
            let status = $('.selectpicker').val()
            console.log(status)
            let branch = $('#branch-filter').val()
            $.ajax({
                type: 'POST',
                url: '<?=HANDLER_PATH . HANDLER_NAME?>',
                cache: false,
                data: {
                    'ACTION': 'getTableData',
                    'ENTITY': 'orders',
                    'date-from': startDate,
                    'date-to': endDate,
                    'status': status,
                },
            }).done(function(response) {
                response.forEach(e => {
                    let linkID = e.id.split('>')[1].split('<')[0].trim()
                    table.row.add(e).draw();
                })
                $('.remove-button').on('click', function() {
                    removeElemID =  $(this).data('elem-id')
                    entity = $(this).data('entity')
                    removeConfirmation.dialog('open')
                });
            });
        }
        $(document).ready(function() {
            table = $('#data-table').DataTable({
                searching: false,
                scrollY: '350px',
                scrollCollapse: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.3/i18n/ru.json',
                },
                "columnDefs": [
                    {
                        "data": null,
                        "defaultContent": "-",
                        "targets": -1
                    }
                ],
                columns: [
                    {name: 'id', data: 'id'},
                    {name: 'client', data: 'client'},
                    {name: 'status', data: 'status'},
                    {name: 'products', data: 'products'},
                    {name: 'total_price', data: 'total_price'},
                ],
                order: [[1, 'asc']]
            });
            $('.xlsx-button').on('click', (event) => {
                event.preventDefault();
                let rows = table.rows().data();
                let headers = table.columns().header();
                let headersNames = [];
                for (let i = 0; i < headers.length; i++)
                    headersNames.push(headers[i].innerHTML)
                let tableHTML = document.getElementById('table-body').outerHTML;
                $.ajax({
                    type: 'POST',
                    url: '/test.php',
                    cache: false,
                    data: {
                        'select': select,
                        'table-body': tableHTML,
                        'headers': JSON.stringify(headersNames),
                    },
                }).done(response => {
                    // window.location.href = "/local/scripts/downloadOrderReport.php?FILE=" + response
                })
            })
            $('#submit').on( "click", function() {
                window.location.href = $('#entity-filter').val();
            })
            $('#filter-submit').on( "click", function() {
                setTableData();
            })
            setTableData();
        })
    </script>
    <form class="row py-3 mb-3 mx-1 align-items-end bg-light" onsubmit="return false;">
        <div class="col-auto">
            <label for="entity-filter" class="form-label">Выберите сущность для статистики</label>
            <select id="entity-filter" class="form-select" aria-label="">
                <option value="/analytics/clients/">Клиенты</option>
                <option value="/analytics/products/">Услуги</option>
                <option selected value="/analytics/orders/">Заказы</option>
            </select>
        </div>
        <div class="col-auto">
            <button id="submit" type="submit" class="btn btn-primary">Применить</button>
        </div>
        <div class="col-auto mb-1">
            <a class='xlsx-button' href="">
                <img src='/include/actions_icons/xlsx_icon.png' alt='XLSX'>
            </a>
        </div>
    </form>
    <form class="row py-3 mb-3 mx-1 align-items-end bg-light" id="filter-form" onsubmit="return false;">
        <div class='col-auto mb-2'>
            <label for='date-range' class='form-label'>Выберите дату или период</label>
            <input id='date-range' class='form-control' readonly>
        </div>
        <div class='col-auto mb-2'>
            <label for='status-filter' class='form-label d-block'>Статус заказа</label>
            <select multiple name="status-filter" id='status-filter d-block' class='selectpicker'>
                <option value="all">Все</option>
                <option value="3">Новая</option>
                <option value="4">Отклонена</option>
                <option value="5">Запланирована</option>
                <option value="6">В работе</option>
                <option value="7">Рекламация</option>
                <option value="8">Завершена</option>
            </select>
        </div>
        <div class='col-auto mb-2'>
            <label for='vin-filter' class='form-label'>VIN</label>
            <input id='vin-filter' class='form-control' type="text" placeholder="Введите VIN">
        </div>
        <div class='col-auto mb-2'>
            <label for='client-filter' class='form-label'>Клиент</label>
            <input id='client-filter' class='form-control' type="text" placeholder="Введите ФИО">
        </div>
        <div class='col-auto mb-2'>
            <label for='products-filter' class='form-label d-block'>Услуга</label>
            <select multiple name="products-filter" id='products-filter d-block' class='selectpicker'>
                <option value="all">Все</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?=$product['ID']?>"><?=$product['NAME']?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class='col-auto mb-2'>
        <label for='branch-filter' class='form-label'>Автосервис</label>
        <?php foreach ($branches as $branch): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="branch-filter-<?=$branch['ID']?>">
                <label class="form-check-label" for="flexCheckDefault">
                    <?= $branch['NAME'] ?>
                </label>
            </div>
        <?php endforeach; ?>
        </div>
        <div class='col-auto mb-2'>
            <label for='manager-filter' class='form-label d-block'>Менеджер</label>
            <select name="manager-filter" id='manager-filter d-block' class='form-select'>
                <option value="all">Все</option>
                <?php foreach ($managers as $manager): ?>
                    <option value="<?=$manager['ID']?>"><?=$manager['LAST_NAME'] . " " . $manager['NAME']?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class='col-auto mb-2'>
            <label for='master-filter' class='form-label d-block'>Мастер</label>
            <select name="master-filter" id='master-filter d-block' class='form-select'>
                <option value="all">Все</option>
                <?php foreach ($masters as $master): ?>
                    <option value="<?=$master['ID']?>"><?=$master['NAME']?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button id="filter-submit" type="submit" class="btn btn-primary mb-2">Применить</button>
        </div>
    </form>
        <table id="data-table" class="table table-hover">
            <thead class="bg-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Клиент</th>
                <th scope="col">Статус</th>
                <th scope="col">Услуги</th>
                <th scope="col">Стоимость</th>
            </tr>
            </thead>
            <tbody id="table-body">
<!--            --><?php //foreach ($clientsStatistic as $clientStatistic):?>
<!--                <tr>-->
<!--                    <th scope="row">--><?php //=$clientStatistic['ID']?><!--</th>-->
<!--                    <td>--><?php //=$clientStatistic['NAME']?><!--</td>-->
<!--                    <td>--><?php //=$clientStatistic['PHONE']['VALUE']?><!--</td>-->
<!--                    <td>--><?php //=$clientStatistic['GENDER']['VALUE']?><!--</td>-->
<!--                    <td>--><?php //=$clientStatistic['ORDERS_COUNT']?><!--</td>-->
<!--                    <td>--><?php //=$clientStatistic['ORDERS_PRICE_SUMMARY']?><!--</td>-->
<!--                    <td>-->
<!--                        --><?php //foreach ($clientStatistic['PRODUCTS_COUNT'] as $product => $count):?>
<!--                            <span>--><?php //=$product . ": " . $count?><!--</span>-->
<!--                            <br/>-->
<!--                        --><?php //endforeach;?>
<!--                    </td>-->
<!--                </tr>-->
<!--            --><?php //endforeach;?>
            </tbody>
        </table>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>