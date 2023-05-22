<?php
/**
 * Created: 18.03.2023, 16:50
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
const HANDLER_PATH = '/local/php_interface/lib/Controllers/';
const HANDLER_NAME = 'ControllerHandler.php';

use lib\Controllers\BranchesController;
?>
<script src='/local/scripts/date_picker.min.js'></script>
<script>
    let table, removeConfirmation, removeElemID, entity;
    async function setTableData() {
        table.clear().draw()
        let startDate = $('#date-range').val().split('-')[0]
        let endDate = $('#date-range').val().split('-')[1]
        let status = $('#status-filter').val()
        let branch = $('#branch-filter').val()
        $.ajax({
            type: 'POST',
            url: '<?=HANDLER_PATH . HANDLER_NAME?>',
            cache: false,
            data: {
                'ACTION': 'getTableData',
                'ENTITY': '<?=$arResult['ENTITY']?>',
                'date-from': startDate,
                'date-to': endDate,
                <?php if ($arResult['ENTITY'] == 'orders'): ?>
                'status': status,
                <?php elseif (in_array($arResult['ENTITY'], ['products', 'masters'])):?>
                'branch': branch
                <?php endif;?>
            },
        }).done(function(response) {
            response.forEach(e => {
                 let linkID = e.id.split('>')[1].split('<')[0].trim()
                e.actions =
                    <?php if ($arResult['ENTITY'] != 'managers'):?>
                    `<a style='cursor: pointer' class='remove-button' data-elem-id=${linkID}, data-entity=<?=$arResult['ENTITY']?>><img width="15" height="15" class='mx-4' src='/include/actions_icons/remove.png' alt='Remove'></a>` +
                    `<a style='cursor: pointer' class='edit-button' href='edit/index.php?ID=${linkID}'><img width="20" height="20" src='/include/actions_icons/edit.png' alt='Remove'></a>`
                    <?php else:?>
                    `<a style='cursor: pointer' class='remove-button' data-elem-id=${linkID}, data-entity=<?=$arResult['ENTITY']?>><img width="15" height="15" class='mx-4' src='/include/actions_icons/remove.png' alt='Remove'></a>`
                    <?php endif;?>
                table.row.add(e).draw();
            })
            $('.remove-button').on('click', function() {
                removeElemID =  $(this).data('elem-id')
                entity = $(this).data('entity')
                removeConfirmation.dialog('open')
            });
        });
    }
    function removeElement() {
        $.ajax({
            type: 'POST',
            url: '/local/php_interface/lib/Controllers/ControllerHandler.php',
            cache: false,
            data: {
                'ACTION': 'delete',
                'ID': removeElemID,
                'ENTITY': entity,
            }
        })
        setTableData()
    }
    $(document).ready(function() {
        table = $('#data-table').DataTable({
            scrollY: '300px',
            scrollCollapse: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.3/i18n/ru.json',
            },
            columns: [
                <?php foreach ($arResult['COLUMN_DEFS'] as $columnDef):?>
                {
                    'title': '<?=$columnDef['title']?>',
                    'data': '<?=$columnDef['data']?>',
                    'width': '<?=$columnDef['width']?>',
                },
                <?php endforeach;?>
            ],
            order: [
                <?php foreach ($arResult['COLUMN_ORDER_DEFS'] as $column => $order):?>
                [<?=$column?>, '<?=$order?>'],
                <?php endforeach;?>
            ]
        });
        removeConfirmation = $('.remove-confirmation').dialog({
            autoOpen: false,
            modal: true,
            title: 'Подтвердите удаление',
            resizable: false,
            draggable: false,
            width: 400,
            buttons: [
                {
                    text: 'Да',
                    click: function() {
                        removeElement()
                        $(this).dialog('close');
                    }
                },
                {
                    text: 'Нет',
                    click: function() {
                        $(this).dialog('close');
                    }
                }
            ]
        })
        $('#submit').click(function() {
            setTableData()
        })
        setTableData()
    })
</script>

<form class='row py-3 mb-3 mx-1 align-items-end bg-light' onsubmit='return false;'>
    <div class='col-auto'>
        <label for='date-range' class='form-label'>Выберите дату или период</label>
        <input id='date-range' class='form-control' readonly>
    </div>
    <?php if ($arResult['ENTITY'] == 'orders'):?>
    <div class='col-auto'>
        <label for='status-filter' class='form-label'>Статус заказа</label>
        <select id='status-filter' class='form-select' aria-label='Default select example'>
            <option>Все</option>
            <option>Новая</option>
            <option>Отклонена</option>
            <option>Запланирована</option>
            <option>В работе</option>
            <option>Рекламация</option>
            <option>Завершена</option>
        </select>
    </div>
    <?php endif;?>
    <?php if (in_array($arResult['ENTITY'], ['products', 'masters'])):
        $branches = BranchesController::getList();
        ?>
        <div class='col-auto'>
            <label for='branch-filter' class='form-label'>Автосервис</label>
            <select id='branch-filter' class='form-select' aria-label='Default select example'>
                <option>Все</option>
                <?php foreach ($branches as $branch): ?>
                    <option value="<?=$branch['ID']?>"><?=$branch['NAME']?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif;?>
    <div class='col-auto'>
        <button id='submit' type='submit' class='btn btn-primary'>Применить</button>
    </div>
</form>
<a style='cursor: pointer; text-decoration: none; color: black;' class='add-button' href="add/">
    <img width="26" height="27" class='mb-3 mx-2' src='/include/actions_icons/add.png' alt='Add'>
</a>
<table id='data-table' class='table table-hover'>
    <thead class='bg-light'>
    </thead>
    <tbody>
    </tbody>
</table>

<div class='remove-confirmation'>
    <p>Вы точно хотите удалить элемент?</p>
</div>


