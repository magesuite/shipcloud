<?php

namespace MageSuite\Shipcloud\Block\Checkout;

class HouseNumberFieldLayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    public function __construct(\Magento\Framework\Stdlib\ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout): array
    {
        $streetPaths = $this->arrayManager->findPaths('street', $jsLayout);

        foreach ($streetPaths as $streetPath) {
            $streetField = $this->arrayManager->get($streetPath, $jsLayout);

            if (isset($streetField['children'])) {
                $streetField['children'][0]['label'] = __('Street');
                $streetField['children'][1]['label'] = __('House number');
                $streetField['children'][1]['validation']['required-entry'] = true;

                $jsLayout = $this->arrayManager->set($streetPath, $jsLayout, $streetField);
            }

        }

        return $jsLayout;
    }
}
