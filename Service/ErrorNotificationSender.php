<?php

namespace MageSuite\Shipcloud\Service;

class ErrorNotificationSender
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->transportBuilder = $transportBuilder;
    }

    public function sendNotification($emailTemplateVariables, $storeAdminName, $adminEmails)
    {
        $templateVariables = new \Magento\Framework\DataObject();
        $templateVariables->setData($emailTemplateVariables);

        $sender = [
            'name' => $storeAdminName,
            'email' => array_shift($adminEmails),
        ];

        $transport = $this->transportBuilder->setTemplateIdentifier('shipcloud_error_notification')
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
            ->setTemplateVars(['data' => $templateVariables])
            ->setFromByScope($sender)
            ->addTo($sender['email'])
            ->setReplyTo($sender['email']);

        if (count($adminEmails)) {
            foreach ($adminEmails as $email) {
                $transport->addBcc($email);
            }
        }

        $transport->getTransport()->sendMessage();
    }
}
