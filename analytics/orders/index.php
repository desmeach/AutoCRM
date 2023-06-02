<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Аналитика");

use lib\Controllers\CarsController;
use lib\Controllers\ClientsController;
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
$clients = ClientsController::getList();
$cars = CarsController::getList();
?>
    <script src='/local/scripts/date_picker.min.js'></script>
    <script>
        let table,
            defaultDateRange;
        function resetFilters() {
            $('#date-range').val(defaultDateRange)
            $(".selectpicker").val('default');
            $(".selectpicker").selectpicker("refresh");
            $('#manager-filter').val('Все')
            $('#master-filter').val('Все')
            setTableData();
        }
        async function setTableData() {
            table.clear().draw()
            let startDate = $('#date-range').val().split('-')[0]
            let endDate = $('#date-range').val().split('-')[1]
            let status = $('#status-filter').val()
            let vin = $('#vin-filter').val()
            let client = $('#client-filter').val()
            let products = $('#products-filter').val()
            let branches = $('#branch-filter').val()
            let manager = $('#manager-filter').val()
            let master = $('#master-filter').val()
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
                    'vin': vin,
                    'client': client,
                    'products': products,
                    'branch': branches,
                    'manager': manager,
                    'master': master,
                },
            }).done(function(response) {
                let totalPrice = 0
                response.forEach(e => {
                    e['date'] = e['date_receive'] ?? e['date_accept'] ?? e['date_start'] ?? e['date_end']
                    e['date'] = e['date'].split(' ')[0]
                    table.row.add(e).draw();
                    totalPrice += parseInt(e['total_price'])
                })
                $('#total-price').text('Сумма: ' + totalPrice + 'руб.')
            });
        }
        $(document).ready(function() {
            defaultDateRange = $('#date-range').val()
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
                    {name: 'date', data: 'date'},
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
                let data = {
                    'table-body': tableHTML,
                    'headers': JSON.stringify(headersNames)
                }
                let entity = $('#entity').val()
                $.ajax({
                    type: 'POST',
                    url: '/local/scripts/getAnalyticReport.php',
                    cache: false,
                    data: {
                        data: data,
                        entity: entity,
                    },
                }).done(response => {
                    window.location.href = "/local/scripts/downloadOrderReport.php?FILE=" + response
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
    <form class="row d-flex py-3 mb-3 mx-1 align-items-end bg-light" onsubmit="return false;">
        <input type="hidden" value="orders" name="entity" id="entity">
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
                <option>Новая</option>
                <option>Отклонена</option>
                <option>Запланирована</option>
                <option>В работе</option>
                <option>Рекламация</option>
                <option>Завершена</option>
            </select>
        </div>
        <div class='col-auto mb-2'>
            <label for='vin-filter' class='form-label'>VIN</label>
            <input id='vin-filter' class='form-control' list="carsDataList" type="text" placeholder="Введите VIN">
            <datalist id="carsDataList">
                <?php foreach ($cars as $car):?>
                    <option><?=$car['NAME']?></option>
                <?php endforeach;?>
            </datalist>
        </div>
        <div class='col-auto mb-2'>
            <label for='client-filter' class='form-label'>Клиент</label>
            <input id='client-filter' class='form-control' list="clientsDataList" type="text" placeholder="Введите ФИО">
            <datalist id="clientsDataList">
                <?php foreach ($clients as $client):?>
                    <option><?=$client['NAME']?></option>
                <?php endforeach;?>
            </datalist>
        </div>
        <div class='col-auto mb-2'>
            <label for='products-filter' class='form-label d-block'>Услуга</label>
            <select multiple name="products-filter" id='products-filter' class='d-block selectpicker'>
                <?php foreach ($products as $product): ?>
                    <option value="<?=$product['ID']?>"><?=$product['NAME']?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class='col-auto mb-2'>
            <label for='branch-filter' class='form-label d-block'>Автосервис</label>
            <select multiple name="branch-filter" id='branch-filter' class='d-block selectpicker'>
                <?php foreach ($branches as $branch): ?>
                    <option value="<?=$branch['ID']?>"><?= $branch['NAME'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class='col-auto mb-2'>
            <label for='manager-filter' class='form-label d-block'>Менеджер</label>
            <select name="manager-filter" id='manager-filter' class='d-block form-select'>
                <option>Все</option>
                <?php foreach ($managers as $manager): ?>
                    <option value="<?=$manager['ID']?>"><?=$manager['LAST_NAME'] . " " . $manager['NAME']?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class='col-auto mb-2'>
            <label for='master-filter' class='form-label d-block'>Мастер</label>
            <select name="master-filter" id='master-filter' class='d-block form-select'>
                <option>Все</option>
                <?php foreach ($masters as $master): ?>
                    <option value="<?=$master['ID']?>"><?=$master['NAME']?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col text-center mt-2">
            <button id="filter-reset" onclick="resetFilters()" type="button" class="btn btn-danger mb-2">Сбросить</button>
            <button id="filter-submit" type="submit" class="btn btn-primary mb-2">Применить</button>
        </div>
    </form>
    <div class="text-center">
        <h3 id="total-price"></h3>
    </div>
    <table id="data-table" class="table table-hover">
        <thead class="bg-light">
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Клиент</th>
            <th scope="col">Статус</th>
            <th scope="col">Дата</th>
            <th scope="col">Услуги</th>
            <th scope="col">Стоимость</th>
        </tr>
        </thead>
        <tbody id="table-body">
        </tbody>
    </table>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>