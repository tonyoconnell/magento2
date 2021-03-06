<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price as CatalogPrice;

/**
 * Bundle Base Price model
 */
class BasePrice extends CatalogPrice\BasePrice
{
    /**
     * Get Base Price Value
     *
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->value === null) {
            $this->value = $this->applyDiscount(parent::getValue());
        }
        return $this->value;
    }

    /**
     * Apply discount type prices
     *
     * @param float $price
     * @return float
     */
    public function applyDiscount($price)
    {
        $discount = [
            0,
            $this->priceInfo
                ->getPrice(CatalogPrice\TierPriceInterface::PRICE_TYPE_TIER, $this->quantity)
                ->getValue(),
            $this->priceInfo
                ->getPrice(CatalogPrice\GroupPriceInterface::PRICE_TYPE_GROUP, $this->quantity)
                ->getValue(),
            $this->priceInfo
                ->getPrice(CatalogPrice\SpecialPriceInterface::PRICE_TYPE_SPECIAL, $this->quantity)
                ->getValue()
        ];
        $discount = max($discount);
        if ($discount) {
            $price = $price - $price * ($discount / 100);
        }
        return $price;
    }
}
