<?php

namespace MageSuite\Shipcloud\Console\Command;

class CreateShipment extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \MageSuite\Shipcloud\Service\OrderShipmentGeneratorFactory
     */
    protected $orderShipmentGeneratorFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\Shipcloud\Service\OrderShipmentGeneratorFactory $orderShipmentGeneratorFactory
    ) {
        parent::__construct();

        $this->state = $state;
        $this->orderShipmentGeneratorFactory = $orderShipmentGeneratorFactory;
    }

    protected function configure()
    {
        $this->addArgument(
            'order_id',
            \Symfony\Component\Console\Input\InputArgument::REQUIRED,
            'Order Id'
        );

        $this->setName('shipcloud:create:shipment')
            ->setDescription('Create shipment for specific order');
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $orderId = $input->getArgument('order_id');

        /** @var \MageSuite\Shipcloud\Service\OrderShipmentGenerator $orderShipmentGenerator */
        $orderShipmentGenerator = $this->orderShipmentGeneratorFactory->create();
        $orderShipmentGenerator->execute($orderId);

        return true;
    }
}
