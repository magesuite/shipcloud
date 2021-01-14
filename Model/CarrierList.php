<?php

namespace MageSuite\Shipcloud\Model;

class CarrierList
{
    /**
     * @var \MageSuite\Shipcloud\Model\CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var Carrier[]
     */
    protected $carriers = [];

    public function __construct(
        \MageSuite\Shipcloud\Model\CarrierFactory $carrierFactory,
        array $carriers = []
    ) {
        foreach ($carriers as $carrier) {
            $this->carriers[] = $carrierFactory->create([
                'code' => $carrier['code'],
                'name' => $carrier['name'],
                'service' => $carrier['service'],
            ]);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \MageSuite\Shipcloud\Model\Product\Export\Column|null
     */
    public function getOrderCarrier(\Magento\Sales\Model\Order $order)
    {
        $shippingMethod = $order->getShippingMethod();

        foreach ($this->carriers as $carrier) {
            $carrierName = sprintf('%1$s_%1$s', $carrier->getCode());

            if ($shippingMethod == $carrierName) {
                return $carrier;
            }
        }

        return null;
    }

    public function getCarriers()
    {
        return $this->carriers;
    }
}
