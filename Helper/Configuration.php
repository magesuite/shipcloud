<?php

namespace MageSuite\Shipcloud\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_CONFIG_PATH_ENABLED = 'shipcloud/general/enabled';
    const XML_CONFIG_PATH_API_KEY = 'shipcloud/general/api_key';
    const XML_CONFIG_PATH_STORE_SHIPPING_LABEL = 'shipcloud/general/store_shipping_label';
    const XML_CONFIG_PATH_EXPORT_PATH = 'shipcloud/general/export_path';
    const XML_CONFIG_PATH_DEBUG = 'shipcloud/general/debug';
    const XML_CONFIG_PATH_RETRY_LIMIT = 'shipcloud/general/retry_limit';
    const XML_CONFIG_PATH_LABEL_FORMAT = 'shipcloud/general/label_format';
    const XML_CONFIG_PATH_ORIGIN_ADDRESS = 'shipcloud/origin_address';
    const XML_CONFIG_PATH_PACKAGE_DESCRIPTION = 'shipcloud/package/description';
    const XML_CONFIG_PATH_PACKAGE_LENGTH = 'shipcloud/package/length';
    const XML_CONFIG_PATH_PACKAGE_WIDTH = 'shipcloud/package/width';
    const XML_CONFIG_PATH_PACKAGE_HEIGHT = 'shipcloud/package/height';
    const XML_CONFIG_PATH_MAXIMUM_PACKAGE_WEIGHT = 'shipcloud/package/maximum_package_weight';
    const XML_CONFIG_PATH_SHIPCLOUD_NOTIFICATION_NAME = 'trans_email/shipcloud_email/name';
    const XML_CONFIG_PATH_SHIPCLOUD_NOTIFICATION_EMAIL = 'trans_email/shipcloud_email/email';
    const XML_CONFIG_PATH_GENERAL_IDENT_EMAIL = 'trans_email/ident_general/email';
    const XML_CONFIG_PATH_EU_COUNTRIES = 'general/country/eu_countries';

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $originAddressConfig;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_ENABLED);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH_API_KEY);
    }

    /**
     * @return bool
     */
    public function shouldStoreShippingLabel()
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_STORE_SHIPPING_LABEL);
    }

    /**
     * @return string
     */
    public function getExportPath($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_EXPORT_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isDebugModeEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_DEBUG);
    }

    /**
     * @return int
     */
    public function getRetryLimit()
    {
        return (int)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_RETRY_LIMIT);
    }

    /**
     * @return string
     */
    public function getLabelFormat()
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_LABEL_FORMAT);
    }

    /**
     * @return float
     */
    public function getPackageLength()
    {
        return (float)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_PACKAGE_LENGTH);
    }

    /**
     * @return float
     */
    public function getPackageWidth()
    {
        return (float)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_PACKAGE_WIDTH);
    }

    /**
     * @return float
     */
    public function getPackageHeight()
    {
        return (float)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_PACKAGE_HEIGHT);
    }

    /**
     * @return float
     */
    public function getMaximumPackageWeight()
    {
        return (float)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_MAXIMUM_PACKAGE_WEIGHT);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getOriginAddress()
    {
        if (!$this->originAddressConfig) {
            $this->originAddressConfig = new \Magento\Framework\DataObject(
                $this->scopeConfig->getValue(self::XML_CONFIG_PATH_ORIGIN_ADDRESS) ?? []
            );
        }

        return $this->originAddressConfig;
    }

    /**
     * @return string
     */
    public function getPackageDescription()
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_PACKAGE_DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getErrorNotificationName()
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_SHIPCLOUD_NOTIFICATION_NAME);
    }

    /**
     * @return string
     */
    public function getErrorNotificationEmail()
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_SHIPCLOUD_NOTIFICATION_EMAIL);
    }

    /**
     * @return string
     */
    public function getGeneralIdentEmail()
    {
        return (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_GENERAL_IDENT_EMAIL);
    }

    /**
     * @param $countryCode
     * @return bool
     */
    public function isCountryInEU($countryCode)
    {
        $euCountries = explode(",", (string)$this->scopeConfig->getValue(self::XML_CONFIG_PATH_EU_COUNTRIES));

        return in_array($countryCode , $euCountries);
    }
}
