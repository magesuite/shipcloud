<?php

namespace MageSuite\Shipcloud\Model\Config\Source;

class LabelFormat implements \Magento\Framework\Option\ArrayInterface
{
    const DIN_A5 = 'pdf_a5';
    const DIN_A6 = 'pdf_a6';

    public function toOptionArray()
    {
        return [
            ['value' => self::DIN_A5, 'label' => __('DIN A5')],
            ['value' => self::DIN_A6, 'label' => __('DIN A6')]
        ];
    }
}
