<?php

namespace modmore\Commerce_PriceTypeTotalQuantity\Pricing\PriceType;

use modmore\Commerce\Pricing\Interfaces\PriceInterface;
use modmore\Commerce\Pricing\Price;
use modmore\Commerce\Pricing\PriceType\Interfaces\ItemPriceTypeInterface;
use modmore\Commerce\Pricing\PriceType\Interfaces\PriceTypeInterface;

class TotalQuantity implements PriceTypeInterface, ItemPriceTypeInterface {
    /**
     * @var \comCurrency
     */
    private $currency;

    /**
     * @var array [int $min, int $max, int $price]
     */
    private $prices = [];

    public function __construct(\comCurrency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param int $minQuantity Integer for the minimum
     * @param int|null $maxQuantity Either an integer or a literal `null` to not have a maximum quantity for the bracket
     * @param int $amount Price in cents
     * @return self
     */
    public function add($minQuantity, $maxQuantity, $amount)
    {
        $this->prices[] = [
            'min' => (int)$minQuantity,
            'max' => $maxQuantity === null ? null : (int)$maxQuantity,
            'amount' => (int)$amount
        ];

        return $this;
    }

    /**
     * @param \comOrderItem $item
     * @return PriceInterface|false
     */
    public function getPriceForItem(\comOrderItem $item)
    {
        $order = $item->getOrder();
        $items = $order->getItems();

        $quantity = 0;
        foreach ($items as $item) {
            $quantity += $item->get('quantity');
        }

        $matchedPrice = false;
        foreach ($this->prices as $option) {
            // Check if there are enough products for this brackets
            if ($quantity < $option['min']) {
                continue;
            }
            // If we have a max, check if we're still below it
            if ($option['max'] !== null && $quantity > $option['max']) {
                continue;
            }
            $matchedPrice = $option['amount'];
        }

        if ($matchedPrice === false) {
            return false;
        }

        return new Price($this->currency, $matchedPrice);
    }

    public function getPrices()
    {
        return $this->prices;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function serialize()
    {
        return json_encode($this->prices);
    }

    public static function unserialize(\comCurrency $currency, $data)
    {
        $instance = new static($currency);

        $prices = json_decode($data, true);
        if (!is_array($prices)) {
            return $instance;
        }

        foreach ($prices as $option) {
            $min = array_key_exists('min', $option) ? (int)$option['min'] : 0;
            $max = array_key_exists('max', $option) ? $option['max'] : 0;
            $max = $max === null ? null : (int)$option['max'];
            $amount = array_key_exists('amount', $option) ? (int)$option['amount'] : 0;

            $instance->add($min, $max, $amount);
        }

        return $instance;
    }

    public static function getTitle()
    {
        return 'commerce.price_type.quantity';
    }

    public static function getFields(\Commerce $commerce)
    {
        return [
            [
                'name' => 'amount',
                'type' => 'currency'
            ],
            [
                'name' => 'min',
                'type' => 'number',
                'min' => 0,
            ],
            [
                'name' => 'max',
                'type' => 'number',
                'min' => 0,
            ],
        ];
    }

    public static function doFieldsRepeat()
    {
        return true;
    }

    public static function allowMultiple()
    {
        return false;
    }

    public function __debugInfo()
    {
        return [
            'currency' => $this->currency->toArray(),
            'prices' => $this->prices,
        ];
    }
}