<?php

class Loewenstark_Freeshippinghint_Block_Hint
extends Mage_Core_Block_Template
{
    const XML_CONFIG_FREE_SHIPPING_HINT_ENABLED   = 'freeshippinghint/general/enabled';
    const XML_CONFIG_FREE_SHIPPING_HINT_AMOUNT    = 'freeshippinghint/general/amount';
    const XML_CONFIG_FREE_SHIPPING_HINT_MIN_VALUE = 'freeshippinghint/general/min_value';

    const XML_CONFIG_FREE_SHIPPING_HINT_MSG_BELOW = 'freeshippinghint/general/msg_below';
    const XML_CONFIG_FREE_SHIPPING_HINT_MSG_ABOVE = 'freeshippinghint/general/msg_above';

    private $_totals     = array();

    protected function _toHtml()
    {
        if (!Mage::getStoreConfig(self::XML_CONFIG_FREE_SHIPPING_HINT_ENABLED))
        {
            return '';
        }

        // Do not display hint if cart total < configured value
        if ($this->getSubtotal() < (float) Mage::getStoreConfig(self::XML_CONFIG_FREE_SHIPPING_HINT_MIN_VALUE))
        {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @see Mage_Checkout_Block_Cart_Sidebar
     */
    public function getSubtotal($skipTax = true)
    {
        $totals = $this->getTotals();
        $config = Mage::getSingleton('tax/config');
        if (isset($totals['subtotal'])) {
            if ($config->displayCartSubtotalBoth()) {
                if ($skipTax) {
                    $subtotal = $totals['subtotal']->getValueExclTax();
                } else {
                    $subtotal = $totals['subtotal']->getValueInclTax();
                }
            } elseif($config->displayCartSubtotalInclTax()) {
                $subtotal = $totals['subtotal']->getValueInclTax();
            } else {
                $subtotal = $totals['subtotal']->getValue();
                if (!$skipTax && isset($totals['tax'])) {
                    $subtotal+= $totals['tax']->getValue();
                }
            }
        }
        return $subtotal;
    }

    protected function getTotals()
    {
        if (empty($this->_totals))
        {
            $this->_totals = $this->getQuote()->getTotals();
        }
        return $this->_totals;
    }

    protected function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function getDifference()
    {
        return (float) Mage::getStoreConfig(self::XML_CONFIG_FREE_SHIPPING_HINT_AMOUNT) - $this->getSubtotal();
    }

    public function isBelow()
    {
        return $this->getDifference() < 0 ? false : true;
    }

    public function getBelowMsg()
    {
        return sprintf(Mage::getStoreConfig(self::XML_CONFIG_FREE_SHIPPING_HINT_MSG_BELOW), Mage::helper('core')->formatCurrency($this->getDifference()));
    }

    public function getAboveMsg()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_FREE_SHIPPING_HINT_MSG_ABOVE);
    }
}
