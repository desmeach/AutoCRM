<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Услуги");

//use lib\IBlockSelector\ProductsSelector;
//$products = ProductsSelector::getProducts();
?>
<?php
$APPLICATION->IncludeComponent(
	"autoCRM:dataTable", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"ENTITY" => "products",
	),
	false
);?>

<!--    <script src="/local/scripts/date_picker.js"></script>-->
<!--    <script>-->
<!--        let table, startDate, endDate, status;-->
<!--        // async function setTableData(startDate, endDate, status) {-->
<!--        //     table.clear().draw()-->
<!--        //     $.ajax({-->
<!--        //         type: "POST",-->
<!--        //         url: "/local/php_interface/lib/IBlockSelector/OrdersSelector.php",-->
<!--        //         data: {-->
<!--        //             'date-from': startDate,-->
<!--        //             'date-to': endDate,-->
<!--        //             'status': status-->
<!--        //         },-->
<!--        //     }).done(function(response) {-->
<!--        //         response.forEach(e => table.row.add(e).draw())-->
<!--        //     });-->
<!--        // }-->
<!--        $(document).ready(function() {-->
<!--            table = $('#data-table').DataTable({-->
<!--                language: {-->
<!--                    url: '//cdn.datatables.net/plug-ins/1.13.3/i18n/ru.json',-->
<!--                },-->
<!--                columns: [-->
<!--                    {name: 'id', data: 'id'},-->
<!--                    {name: 'date', data: 'date'},-->
<!--                    {name: 'client', data: 'client'},-->
<!--                    {name: 'car', data: 'car'},-->
<!--                    {name: 'status', data: 'status'},-->
<!--                ],-->
<!--            });-->
<!--            // startDate = $('#date-range').val().split('-')[0]-->
<!--            // endDate = $('#date-range').val().split('-')[1]-->
<!--            // status = $('#status-filter').val()-->
<!--            // setTableData(startDate, endDate, status)-->
<!--            // $('#submit').click(function() {-->
<!--            //     startDate = $('#date-range').val().split('-')[0]-->
<!--            //     endDate = $('#date-range').val().split('-')[1]-->
<!--            //     status = $('#status-filter').val()-->
<!--            //     setTableData(startDate, endDate, status);-->
<!--            // })-->
<!--        })-->
<!--    </script>-->
<!--    <form class="row py-3 mb-3 align-items-end bg-light" onsubmit="return false;">-->
<!--        <div class="col-auto">-->
<!--            <label for="date-range" class="form-label">Выберите дату или период</label>-->
<!--            <input id="date-range" class="form-control" readonly>-->
<!--        </div>-->
<!--        <div class="col-auto">-->
<!--            <label for="status-filter" class="form-label">Статус заказа</label>-->
<!--            <select id="status-filter" class="form-select" aria-label="Default select example">-->
<!--                <option>Все</option>-->
<!--                <option>Новая</option>-->
<!--                <option>Отклонена</option>-->
<!--                <option>Запланирована</option>-->
<!--                <option>В работе</option>-->
<!--                <option>Рекламация</option>-->
<!--                <option>Завершена</option>-->
<!--            </select>-->
<!--        </div>-->
<!--        <div class="col-auto">-->
<!--            <button id="submit" type="submit" class="btn btn-primary">Применить</button>-->
<!--        </div>-->
<!--    </form>-->

<!--    <table id="data-table" class="table table-hover">-->
<!--        <thead class="bg-light">-->
<!--        <tr>-->
<!--            <th scope="col">ID</th>-->
<!--            <th scope="col">Наименование</th>-->
<!--            <th scope="col">Автосервисы</th>-->
<!--            <th scope="col">Нормо-час</th>-->
<!--            <th scope="col">Цена</th>-->
<!--        </tr>-->
<!--        </thead>-->
<!--        <tbody>-->
<!--        --><?php //foreach ($products as $product):?>
<!--            <tr>-->
<!--                <th scope="row">--><?php //=$product['ID']?><!--</th>-->
<!--                <td>--><?php //=$product['NAME']?><!--</td>-->
<!--                <td>-->
<!--                    --><?php //foreach ($product['BRANCHES'] as $num => $branch):?>
<!--                        <span>--><?php //=++$num . ") " . $branch;?><!--</span>-->
<!--                        <br/>-->
<!--                    --><?php //endforeach;?>
<!--                </td>-->
<!--                <td>--><?php //=$product['WORKING_HOUR']['VALUE']?><!--</td>-->
<!--                <td>--><?php //=$product['PRICE']['VALUE']?><!--</td>-->
<!--            </tr>-->
<!--        --><?php //endforeach;?>
<!--        </tbody>-->
<!--    </table>-->

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>