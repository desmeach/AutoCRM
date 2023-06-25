<?php
/**
 * Created: 06.04.2023, 17:30
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
?>
<style>
    table {
        font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif, serif;
        text-align: center;
        border-collapse: collapse;
        border-spacing: 5px;
        border-radius: 20px;
    }
    th {
        font-size: 22px;
        font-weight: 300;
        padding: 12px 10px;
        border-bottom: 2px solid #F56433;
        color: #F56433;
    }
    thead {
        border-bottom: 2px solid #F56433;
    }
    td {
        padding: 10px;
        color: #8D8173;
    }
</style>
<script>
    $(document).ready(function() {
        $('#edit').on('click', function() {
            window.location.href = '../edit/index.php?ID=<?=$arResult['ELEMENT']['ID']?>';
        })
    })
</script>
<h3 class="text-center">Детальная информация</h3>
<input type="hidden" name="ID" value="<?=$arResult['ELEMENT']['ID']?>">
<div class="w-50 mx-auto mt-3">
    <table class="mx-auto">
        <thead>
            <td>Поле</td>
            <td>Значение</td>
        </thead>
        <tbody>
        <?php foreach ($arResult['PROPS'] as $key => $type): ?>
            <?php if ($key == 'KEY') continue; ?>
            <tr>
                <td><?=$key?>:</td>
                <td>
                    <?=$arResult['ELEMENT'][$key] ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="text-center mt-3">
    <button id="edit" class="btn btn-primary mt-2 mx-auto" style="width: 100px;">Изменить</button>
</div>

