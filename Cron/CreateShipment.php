<?php

namespace MageSuite\Shipcloud\Cron;

class CreateShipment
{
    /**
    * @var \MageSuite\Shipcloud\Helper\Configuration
    */
    protected $configuration;

    /**
     * @var \MageSuite\Shipcloud\Service\OrderShipmentGenerator
     */
    protected $orderShipmentGenerator;

    public function __construct(
        \MageSuite\Shipcloud\Helper\Configuration $configuration,
        \MageSuite\Shipcloud\Service\OrderShipmentGenerator $orderShipmentGenerator
    ) {
        $this->configuration = $configuration;
        $this->orderShipmentGenerator = $orderShipmentGenerator;
    }

    public function execute()
    {
        if (!$this->configuration->isEnabled()) {
            return;
        }

        $this->orderShipmentGenerator->execute();
    }
}
