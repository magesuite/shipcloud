<?php

namespace MageSuite\Shipcloud\Observer;

class ShipmentErrorMailNotificationSender implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\Shipcloud\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\Shipcloud\Service\ErrorNotificationSender
     */
    protected $errorNotificationSender;

    public function __construct(
        \MageSuite\Shipcloud\Helper\Configuration $configuration,
        \MageSuite\Shipcloud\Service\ErrorNotificationSender $errorNotificationSender
    ) {
        $this->configuration = $configuration;
        $this->errorNotificationSender = $errorNotificationSender;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeAdminName = $this->configuration->getErrorNotificationName();
        $storeAdminEmail = $this->configuration->getErrorNotificationEmail();

        if ($storeAdminEmail == null) {
            return;
        }

        $orderId = $observer->getData('orderId');
        $error = $observer->getData('error');

        if (!isset($error) && !isset($orderId)) {
            return;
        }

        $adminEmails = ($storeAdminEmail) ? array_map('trim', explode("\n", $storeAdminEmail)) : [$this->configuration->getGeneralIdentEmail()];

        $emailTemplateVariables = ['orderId' => $orderId, 'error' => $error];

        $this->errorNotificationSender->sendNotification($emailTemplateVariables, $storeAdminName, $adminEmails);
    }
}
