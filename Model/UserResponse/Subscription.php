<?php

namespace MageSuite\Shipcloud\Model\UserResponse;

class Subscription extends \Magento\Framework\DataObject
{
    const PLAN_NAME = 'plan_name';
    const PLAN_DISPLAY_NAME = 'plan_display_name';
    const CHARGEABLE = 'chargeable';

    /**
     * @return string
     */
    public function getPlanName()
    {
        return $this->getData(self::PLAN_NAME);
    }

    /**
     * @return string
     */
    public function getPlanDisplayName()
    {
        return $this->getData(self::PLAN_DISPLAY_NAME);
    }

    /**
     * @return bool
     */
    public function getChargeable()
    {
        return $this->getData(self::CHARGEABLE);
    }
}
