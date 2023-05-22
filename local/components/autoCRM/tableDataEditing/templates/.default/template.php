<?php
/**
 * Created: 06.04.2023, 17:30
 * Author : Vladislav Naryzhny <desmeach@gmail.com>
 * Company: 34web Studio
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */
$actions = [
  'add' => 'Добавление',
  'edit' => 'Изменение'
];
$buttonActions = [
    'add' => 'Добавить',
    'edit' => 'Изменить'
];
$entityNameField = [
    'clients' => 'ФИО',
    'cars' => 'VIN',
    'products' => 'Наименование',
    'branches' => 'Название',
    'masters' => 'ФИО',
    'managers' => 'ФИО'
];
$actionTitle = $actions[$arResult['ACTION']];
$buttonAction = $buttonActions[$arResult['ACTION']];
$action = $arResult['ACTION'];
$elem = $arResult['ELEMENT'];
$entity = $arResult['ENTITY'];
?>
<script>
    $(document).ready(function() {
        $('#form-edit').submit(function(e) {
            e.preventDefault()
            $.ajax({
                type: 'POST',
                url: '/local/php_interface/lib/Controllers/ControllerHandler.php',
                cache: false,
                data: $(this).serialize()
            }).done((response) => {
                let alert = $('.alert')
                alert.removeClass('d-none')
                if (response.error) {
                    alert.removeClass('alert-success')
                    alert.addClass('alert-danger')
                    alert.html('Ошибка: ' + response.error)
                }
                else {
                    alert.removeClass('alert-danger')
                    alert.addClass('alert-success')
                    alert.html('Операция прошла успешно!')
                }
            })
        })
        $('.add-button').on('click', function() {
            let code = $(this).data('code')
            let parent = $('#' + code)
            parent.attr('name', parent.attr('name') + '[]')
            let clonedNode = parent.clone()
            clonedNode.removeAttr('id')
            clonedNode.addClass('mt-3')
            $("#" + code + "-group").append(clonedNode);
        })
    })
</script>
<div class="row justify-content-center">
    <h3 class="text-center">
        <?=$actionTitle?> элемента
    </h3>
    <form class="text-center w-50" id="form-edit">
        <div class="alert alert-success d-none" role="alert">
        </div>
        <?php if (isset($elem)): ?>
            <input type="hidden" name="ID" value="<?=$elem['ID']?>">
        <?php endif; ?>
        <input type="hidden" name="ENTITY" value="<?=$entity?>">
        <input type="hidden" name="ACTION" value="<?=$action?>">
        <?php if (isset($entityNameField[$entity])): ?>
            <div class="my-2">
                <label for="NAME">
                    <?=$entityNameField[$entity]?>
                </label>
                <input class="form-control"
                       value="<?= isset($elem) ? $elem['NAME'] : ""?>"
                       type="text" id="NAME" name="NAME">
            </div>
        <?php endif;?>
        <?php foreach ($arResult['PROPS'] as $prop): ?>
        <?php if ($prop['CODE'] == 'KEY'):?>
                <input type="hidden" name="<?=$prop['CODE']?>" value="<?=$arResult['KEY']?>">
        <?php elseif ($prop['PROPERTY_TYPE'] == 'S' || $prop['PROPERTY_TYPE'] == 'N'): ?>
                <div class="my-2" id="<?=$prop['CODE']?>-group">
                    <label for="<?=$prop['CODE']?>">
                        <?=$prop['NAME']?>
                    </label>
                    <?php if (isset($elem) && is_array($elem[$prop['CODE']]['VALUE'])):?>
                        <?php foreach ($elem[$prop['CODE']]['VALUE'] as $key => $value):?>
                            <input class="form-control <?= $key > 0 ? 'mt-3' : ''?>"
                                   value="<?=$value?>"
                                   type="text"
                                   id="<?= $key == 0 ? $prop['CODE'] : '' ?>"
                                   name="<?=$prop['CODE']?>">
                        <?php endforeach; ?>
                    <?php else: ?>
                    <input class="form-control"
                           value="<?= isset($elem) ? $elem[$prop['CODE']]['VALUE'] : ""?>"
                           type="text" id="<?=$prop['CODE']?>"
                           name="<?=$prop['CODE']?>">
                    <?php endif; ?>
                </div>
                <?php if ($prop['MULTIPLE'] == 'Y'): ?>
                <div class="my-1">
                    <a style='cursor: pointer; text-decoration: none; color: black;' class='add-button' data-code="<?=$prop['CODE']?>">
                        <img class='mb-3' src='/include/actions_icons/add.png' alt='Add'>
                    </a>
                </div>
                <?php endif;?>
        <?php elseif ($prop['PROPERTY_TYPE'] == 'L' || $prop['PROPERTY_TYPE'] == 'E'):?>
                <div class="my-2" id="<?=$prop['CODE']?>-group">
                    <label for="<?=$prop['CODE']?>">
                        <?=$prop['NAME']?>
                    </label>
                    <?php if ($prop['MULTIPLE'] == 'Y'):?>
                        <?php if (isset($elem)):?>
                            <?php foreach ($elem[$prop['CODE']]['VALUE'] as $key => $element):?>
                            <select class="form-select <?=$key > 0 ? 'mt-3' : ''?>"
                                    id="<?=$key == 0 ? $prop['CODE'] : '' ?>"
                                    name="<?=$prop['CODE']?>[]">
                                <?php foreach ($prop['VALUES'] as $id => $value): ?>
                                    <option <?php
                                    if ($id == $element['ID'])
                                        echo 'selected';
                                    ?>
                                        value="<?=$id?>"><?=$value?></option>
                                <?php endforeach;?>
                            </select>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <select class="form-select"
                                    id="<?=$prop['CODE']?>"
                                    name="<?=$prop['CODE']?>">
                                <?php foreach ($prop['VALUES'] as $id => $value):?>
                                    <option
                                        <?php
                                        if (is_array($elem[$prop['CODE']]['VALUE']))
                                            $elValue = $elem[$prop['CODE']]['VALUE']['NAME'];
                                        else
                                            $elValue = $elem[$prop['CODE']]['VALUE'];
                                        if ($value == $elValue)
                                            echo 'selected';
                                        ?>
                                        value="<?=$id?>">
                                        <?=$value?>
                                    </option>
                                <?php endforeach;?>
                            </select>
                        <?php endif;?>
                    <?php else: ?>
                    <select class="form-select"
                            id="<?=$prop['CODE']?>"
                            name="<?=$prop['CODE']?>">
                        <?php foreach ($prop['VALUES'] as $id => $value):?>
                            <option
                                <?php
                                if (is_array($elem[$prop['CODE']]['VALUE']))
                                    $elValue = $elem[$prop['CODE']]['VALUE']['NAME'];
                                else
                                    $elValue = $elem[$prop['CODE']]['VALUE'];
                                if ($value == $elValue)
                                    echo 'selected';
                                ?>
                                value="<?=$id?>">
                                <?=$value?>
                            </option>
                        <?php endforeach;?>
                    </select>
                    <?php endif; ?>
                </div>
                <?php if ($prop['MULTIPLE'] == 'Y'): ?>
                    <div class="my-1">
                        <a style='cursor: pointer; text-decoration: none; color: black;' class='add-button' data-code="<?=$prop['CODE']?>">
                            <img class='mb-3' src='/include/actions_icons/add.png' alt='Add'>
                        </a>
                    </div>
                <?php endif;?>
        <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit" id="submit" class="btn btn-primary"><?=$buttonAction?></button>
    </form>
</div>

