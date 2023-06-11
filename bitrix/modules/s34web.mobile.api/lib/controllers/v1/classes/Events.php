<?php
/**
 * Created: 08.06.2021, 16:33
 * Author : Evgeniy Cimonov <cimonovevgeniy@34web.ru>
 * Company: 34web Studio
 */

namespace s34web\Mobile\Api\controllers\v1\classes;


use Bitrix\Catalog\EO_Price;
use Bitrix\Catalog\EO_Product;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\EO_Section;
use Bitrix\Iblock\Iblock;
use Bitrix\Iblock\SectionTable;
use Bitrix\Im\Configuration\General;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Objectify\Values;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use CIBlockElement;

class Events
{
    public static function updateElementsInSection($params = [])
    {
        if(
            !empty($params['sectionLeftMargin'])
            && !empty($params['sectionRightMargin'])
        ){
            $iblockId = $params['iblockId'] ?: cGeneral::getIBlockIDByLang('catalog');
            $sectionLeftMargin = $params['sectionLeftMargin'];
            $sectionRightMargin = $params['sectionRightMargin'];

            $iblockEntity = Iblock::wakeUp($iblockId);
            $catalogEntityClass = $iblockEntity->getEntityDataClass();
            $iterator = $catalogEntityClass::getList(
                [
                    'filter' => [
                        '>=SECTION_DATA.LEFT_MARGIN' => $sectionLeftMargin,
                        '<=SECTION_DATA.RIGHT_MARGIN' => $sectionRightMargin,
                    ],
                    'select' => [
                        'ID',
                        'IBLOCK_ID',
                        'NAME',
                    ],
                    'runtime' => [
                        new Reference(
                            'SECTION_DATA',
                            SectionTable::class,
                            Join::on('this.IBLOCK_SECTION_ID', 'ref.ID')
                        ),
                    ],
                ]
            );
            while($row = $iterator->fetch()){
                CIBlockElement::SetPropertyValuesEx($row['ID'], $iblockId, [
                    'EXTERNAL_TIMESTAMP' => new DateTime()
                ]);
            }
        }
    }

    public static function getSectionFields(Event $event)
    {
        /** @var EO_Section $section */
        $section = $event->getParameter('object');
        $section->fill(['LEFT_MARGIN', 'RIGHT_MARGIN']);

        self::updateElementsInSection(
            [
                'iblockId' => $section->getIblockId(),
                'sectionLeftMargin' => $section->getLeftMargin(),
                'sectionRightMargin' => $section->getRightMargin(),
            ]
        );
    }

    public static function onUpdateUserFieldValuesHandler(\Bitrix\Main\Event $event)
    {
        $entityId = $event->getParameter('entityId');

        $catalogIblockID = cGeneral::getIBlockIDByLang('catalog');

        if ($entityId == 'IBLOCK_' . $catalogIblockID . '_SECTION') {
            $sectionId = $event->getParameter('id');
            $fields = $event->getParameter('fields');
            $iblockId = $fields['IBLOCK_ID'];

            $iterator = SectionTable::getList(
                [
                    'filter' => ['ID' => $sectionId, 'IBLOCK_ID' => $iblockId],
                    'select' => ['LEFT_MARGIN', 'RIGHT_MARGIN'],
                    'limit' => 1,
                ]
            );
            if($row = $iterator->fetch()){
                self::updateElementsInSection(
                    [
                        'iblockId' => $iblockId,
                        'sectionLeftMargin' => $row['LEFT_MARGIN'],
                        'sectionRightMargin' => $row['RIGHT_MARGIN'],
                    ]
                );
            }
        }

        //return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS);
    }

    public static function BitrixIblockSectionTableOnAfterUpdateHandler(Event $event)
    {
        self::updateElementsInSection($event);
    }

    public static function BitrixIblockSectionTableOnAfterDeleteHandler(Event $event)
    {
        self::updateElementsInSection($event);
    }

    public static function BitrixCatalogPriceOnAfterUpdateHandler(Event $event)
    {
        /** @var EO_Price $price */
        $price = $event->getParameter('object');
        $productId = $price->fillProductId();

        $product = $price->fillProduct();
        $type = $product->fillType();

        $element = $product->fillIblockElement();
        $iblockId = $element->fillIblockId();

        if(in_array($type, [ProductTable::TYPE_PRODUCT, ProductTable::TYPE_SKU])) {
            //functions::dumpLog([$type, $iblockId], 'logs/' . __FUNCTION__ . '.log');

            CIBlockElement::SetPropertyValuesEx(
                $productId,
                $iblockId,
                [
                    'EXTERNAL_TIMESTAMP' => new DateTime()
                ]
            );
        }
    }

    /**
     * @cimonov save old quantity for after event
     * @param Event $event
     */
    public static function BitrixCatalogProductOnBeforeUpdateHandler(Event $event)
    {
        /** @var EO_Product $product */
        $product = $event->getParameter('object');
        $productId = $product->getId();
        $quantity = $product->fillQuantity();
        $actualValues = $product->collectValues(Values::ACTUAL);
        $quantityOld = $actualValues['QUANTITY'] ?? $quantity;

        $quantityDB = -1;
        $iterator = ProductTable::getList(
            [
                'filter' => ['ID' => $productId],
                'select' => [
                    'ID',
                    'QUANTITY'
                ],
                'limit' => 1,
            ]
        );
        if($row = $iterator->fetch()){
            $quantityDB = $row['QUANTITY'];
        }
        //functions::dumpLog([$actualValues, "quantityOld="=>$quantityOld, "quantity="=>$quantity, "quantityDB="=>$quantityDB], 'logs/BeforeUpdate.log');
    }

    /**
     * @cimonov get old and new quantity values and compare them
     * @cimonov update external timestamp only if quantity changed
     * @param Event $event
     */
    public static function BitrixCatalogProductOnAfterUpdateHandler(Event $event)
    {
        /** @var EO_Product $product */
        $product = $event->getParameter('object');
        $productId = $product->getId();
        $type = $product->fillType();
        $quantity = $product->fillQuantity();

        $actualValues = $product->collectValues(Values::ACTUAL);
        $quantityOld = $actualValues['QUANTITY'] ?? $quantity;

        /*$quantityDB = -1;
        $iterator = ProductTable::getList(
            [
                'filter' => ['ID' => $productId],
                'select' => [
                    'ID',
                    'QUANTITY'
                ],
                'limit' => 1,
            ]
        );
        if($row = $iterator->fetch()){
            $quantityDB = $row['QUANTITY'];
        }*/
        $element = $product->fillIblockElement();

        $iblockId = empty($element) ? false : $element->fillIblockId();

        /*functions::dumpLog([$actualValues,
                               "quantityOld="=>$quantityOld,
                               "quantity="=>$quantity
                           ],
                           'logs/OnAfterUpdate.log');*/
        if($quantityOld != $quantity) {
            /*General::w dumpLog(
                ['Изменился товар '.$quantityOld.'=>'.$quantity.' id='=> $productId,"type"=>$type],
                'logs/OnAfterUpdate_quantity_change.log'
            );*/
        }
        //Если количество стало доступных либо наоборот всё раскупили, то обновим кастомную дату обновления товара,
        // при этом не меняется основная дата обновления товара.
        //Необходимо для корректного кэширования в мобильном приложении
        if(
            in_array($type, [ProductTable::TYPE_PRODUCT, ProductTable::TYPE_SKU])
            && (
                ($quantity > 0 && $quantityOld == 0)
                || ($quantityOld > 0 && $quantity == 0)
            )
        ) {
            //functions::dumpLog([$quantityOld, $quantity], 'logs/OnAfterUpdate_quantity_avail_event.log');

            CIBlockElement::SetPropertyValuesEx(
                $productId,
                $iblockId,
                [
                    'EXTERNAL_TIMESTAMP' => new DateTime()
                ]
            );
        }
    }
}