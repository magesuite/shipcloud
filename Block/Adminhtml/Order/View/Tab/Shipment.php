<?php

namespace MageSuite\Shipcloud\Block\Adminhtml\Order\View\Tab;

class Shipment extends \Magento\Framework\View\Element\Text\ListText implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    public function getTabLabel()
    {
        return __('Shipcloud');
    }

    public function getTabTitle()
    {
        return __('Shipcloud');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
