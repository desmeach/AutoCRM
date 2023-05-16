<?php
/**
 * Created: 06.04.2023, 17:30
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
?>
<script>
    $(document).ready(function() {
        $('#edit').on('click', function() {
            window.location.href = '../edit/index.php?ID=<?=$arResult['ELEMENT']['ID']?>';
        })
    })
</script>
<h3 class="text-center">Детальная информация</h3>
<input type="hidden" name="ID" value="<?=$arResult['ELEMENT']['ID']?>">
<div class="w-50 mx-auto mt-5">
    <table class="mx-auto">
        <tbody>
        <?php if ($arResult['IBLOCK_ID'] != 3): ?>
            <tr>
                <td><?=$arResult['NAME_FIELD']?>:</td>
                <td><?=$arResult['ELEMENT']['NAME']?></td>
            </tr>
        <?php endif;?>
        <?php foreach ($arResult['PROPS'] as $prop): ?>
            <?php if ($prop['CODE'] == 'KEY') continue; ?>
            <tr>
                <td><?=$prop['NAME']?>:</td>
                <td>
                    <?=$arResult['ELEMENT'][$prop['CODE']]['VALUE'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="text-center mt-3">
    <button id="edit" class="btn btn-primary mt-2 mx-auto" style="width: 100px;">Изменить</button>
</div>

