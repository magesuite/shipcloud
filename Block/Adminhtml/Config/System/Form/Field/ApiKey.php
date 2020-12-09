<?php

namespace MageSuite\Shipcloud\Block\Adminhtml\Config\System\Form\Field;

class ApiKey extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \MageSuite\Shipcloud\Api\GetUserInterface
     */
    protected $getUser;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageSuite\Shipcloud\Api\GetUserInterface $getUser,
        \Magento\Framework\App\CacheInterface $cache,
        array $data = []
    ) {
        $this->getUser = $getUser;
        $this->cache = $cache;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $html = $element->getElementHtml();
        $html .= $this->getCurrentUserHtml();

        return $html;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getCurrentUserHtml()
    {
        $html = $this->cache->load(\MageSuite\Shipcloud\Model\Config\Backend\ApiKey::CACHE_ID);

        if (!$html) {
            try {
                $user = $this->getUser->execute();
                $params = [];
                $params[] = sprintf('Firstname: <b>%s</b>', $user->getFirstName());
                $params[] = sprintf('Lastname: <b>%s</b>', $user->getLastName());
                $params[] = sprintf('Email: <b>%s</b>', $user->getEmail());
                $params[] = sprintf('Environment: <b>%s</b>', $user->getEnvironment());
                $params[] = sprintf('Plan Name: <b>%s</b>', $user->getSubscription()->getPlanDisplayName());
                $params[] = sprintf('Chargeable: <b>%s</b>', $user->getSubscription()->getChargeable() ? __('Yes') : __('No'));
                $html = implode('<br/>', $params);
            } catch (\Exception $e) {
                $html = __('Invalid <a href="https://app.shipcloud.io/en/users/api_key" target="_blank">API Key</a>.');
            }

            $this->cache->save(
                $html,
                \MageSuite\Shipcloud\Model\Config\Backend\ApiKey::CACHE_ID,
                [],
                86400
            );
        }

        return $html;
    }
}
