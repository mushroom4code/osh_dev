<?php
namespace Ipol\Fivepost\Bitrix\Adapter;

use Ipol\Fivepost\Bitrix\Entity\DefaultGabarites;
use Ipol\Fivepost\Bitrix\Entity\Options;
use Ipol\Fivepost\Bitrix\Handler\GoodsPicker;
use Ipol\Fivepost\Core\Order\Item;
use Ipol\Fivepost\Core\Order\ItemCollection;
use \Ipol\Fivepost\Core\Entity\Money;

class OrderItems
{
    protected $coreItems;
    protected $options;

    public function __construct(Options $options)
    {
        $this->options   = $options;
        $this->coreItems = new ItemCollection();
        return $this;
    }

    public function fromOrder($bitrixId)
    {
        $arGoods = GoodsPicker::fromOrder($bitrixId);

        $articul = $this->options->fetchArticul();
        $barcode = $this->options->fetchBarcode();

        GoodsPicker::addBasketGoodProperties($arGoods, array($articul, $barcode));
        GoodsPicker::addGoodsQRs($arGoods, $bitrixId);

        $defGabarites = new DefaultGabarites();

        foreach ($arGoods as $arGood) {
            $arDimensions = array(
                'WEIGHT' => ($defGabarites->getMode() == 'G' && !floatval($arGood['WEIGHT'])) ? $defGabarites->getWeight() : $arGood['WEIGHT'],
                'HEIGHT' => ($defGabarites->getMode() == 'G' && !floatval($arGood['HEIGHT'])) ? $defGabarites->getHeight() : $arGood['HEIGHT'],
                'WIDTH'  => ($defGabarites->getMode() == 'G' && !floatval($arGood['WIDTH']))  ? $defGabarites->getWidth() : $arGood['WIDTH'],
                'LENGTH' => ($defGabarites->getMode() == 'G' && !floatval($arGood['LENGTH'])) ? $defGabarites->getLength() : $arGood['LENGTH']
            );

            $obItem = new Item();
            $obItem->setName($arGood['NAME'])
                ->setQuantity($arGood['QUANTITY'])
                ->setId($arGood['PRODUCT_ID'])
                ->setWeight($arDimensions['WEIGHT'])
                ->setHeight($arDimensions['HEIGHT'])
                ->setWidth($arDimensions['WIDTH'])
                ->setLength($arDimensions['LENGTH']);

            // Some VAT magic
            $vatRate = intval($arGood['VAT_RATE'] * 100);
            if ($vatRate > 0 && $arGood['VAT_INCLUDED'] !== 'Y') {
                // VAT not included in good's price, add it, cause API does not know this BX differences
                $realPrice   = new Money($arGood['PRICE']);
                $realVat     = Money::multiply($realPrice, floatval($arGood['VAT_RATE']));
                $resultPrice = Money::sum($realPrice, $realVat);

                $obItem->setPrice($resultPrice->getAmount())->setCost($resultPrice->getAmount());
            } else {
                $obItem->setPrice($arGood['PRICE'])->setCost($arGood['PRICE']);
            }
            $obItem->setVatRate($vatRate);

            if ($articul) {
                $obItem->setArticul(trim($arGood['PROPERTIES'][$articul]));
            }
            if ($barcode) {
                $obItem->setBarcode(trim($arGood['PROPERTIES'][$barcode]));
            }
            if ($arGood['QR']) {
                $obItem->setField('QR', $arGood['QR']);
            }

            $this->getCoreItems()->add($obItem);
        }

        return $this;
    }

    public function fromArray($arItems)
    {
        foreach ($arItems as $item) {
            $obItem = new Item();
            $obItem->setName($item['name'])
                ->setPrice($item['price'])
                ->setCost($item['cost'])
                ->setQuantity($item['quantity'])
                ->setId($item['id'])
                ->setWeight($item['weight'])
                ->setHeight($item['height'])
                ->setWidth($item['width'])
                ->setLength($item['length'])
                ->setVatRate(intval($item['vatRate']))
                ->setArticul($item['articul'])
                ->setBarcode($item['barcode']);

            $obItem->setFields(
                [
                    'oc' => $item['oc'],
                    'ccd' => $item['ccd'],
                    'tnved' => $item['tnved'],
                ]
            );

            $this->getCoreItems()->add($obItem);
        }

        return $this;
    }

    /**
     * @return ItemCollection
     */
    public function getCoreItems()
    {
        return $this->coreItems;
    }

    /**
     * @param mixed $coreItems
     * @return $this
     */
    public function setCoreItems($coreItems)
    {
        $this->coreItems = $coreItems;

        return $this;
    }
}