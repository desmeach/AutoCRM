<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Аналитика");

use lib\Statistics\ProductsStatistic;
$productsStatistic = ProductsStatistic::getStatistic();
?>
    <script>
        let table;
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
                    {name: 'name', data: 'name'},
                    {name: 'order_count', data: 'order_count'},
                    {name: 'total_sum', data: 'total_sum'},
                    {name: 'relevant_products', data: 'relevant_products'},
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
            } )
        })
    </script>
    <form class="row py-3 mb-3 mx-1 align-items-end bg-light" onsubmit="return false;">
        <input type="hidden" value="products" name="entity" id="entity">
        <div class="col-auto">
            <label for="entity-filter" class="form-label">Выберите сущность для статистики</label>
            <select id="entity-filter" class="form-select" aria-label="">
                <option value="/analytics/clients/">Клиенты</option>
                <option selected value="/analytics/products/">Услуги</option>
                <option value="/analytics/orders/">Заказы</option>
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
        <table id="data-table" class="table table-hover">
            <thead class="bg-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Наименование</th>
                <th scope="col">Всего заказов</th>
                <th scope="col">Сумма</th>
                <th scope="col">Заказывают с</th>
            </tr>
            </thead>
            <tbody id="table-body">
            <?php foreach ($productsStatistic as $productStatistic):?>
                <tr>
                    <th scope="row"><?=$productStatistic['ID']?></th>
                    <td><?=$productStatistic['NAME']?></td>
                    <td><?=$productStatistic['ORDERS_COUNT']?></td>
                    <td><?=$productStatistic['TOTAL_SUM']?></td>
                    <td>
                        <?php foreach ($productStatistic['RELEVANT_PRODUCTS'] as $value):?>
                            <span><?=$value['product'] . ": " . $value['percantage']?></span>
                            <br/>
                        <?php endforeach;?>
                    </td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>