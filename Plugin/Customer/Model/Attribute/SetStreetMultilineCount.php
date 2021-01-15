<?php

namespace MageSuite\Shipcloud\Plugin\Customer\Model\Attribute;

class SetStreetMultilineCount
{
    /**
     * API requires a separate field for house number
     *
     * @param \Magento\Customer\Model\Attribute $subject
     * @param $result
     * @return int
     */
    public function afterGetMultilineCount(
        \Magento\Customer\Model\Attribute $subject,
        $result
    ) {
        if ($subject->getAttributeCode() == 'street') {
            return 2;
        }

        return $result;
    }
}
