<?php

namespace MageSuite\Shipcloud\Model\Config\Backend;

class ApiKey extends \Magento\Framework\App\Config\Value
{
    const CACHE_ID = 'shipcloud_me_response_adminhtml';

    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_cacheManager->remove(self::CACHE_ID);
        }

        parent::beforeSave();
        return $this;
    }
}
