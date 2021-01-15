<?php

namespace MageSuite\Shipcloud\Observer;

class AddPickupToGoShipment implements \Magento\Framework\Event\ObserverInterface
{
    const CARRIER_NAME = 'go';

    const DATE_FORMAT = 'Y-m-d\TH:i:sP';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    public function __construct(\Magento\Framework\Stdlib\DateTime\TimezoneInterface $date)
    {
        $this->date = $date;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $params = $observer->getEvent()->getData('params');

        if ($params->getCarrier() != self::CARRIER_NAME) {
            return;
        }

        $pickup = [
            'pickup' => [
                'pickup_time' => [
                    'earliest' => $this->getEarliest()->format(self::DATE_FORMAT),
                    'latest' => $this->getLatest()->format(self::DATE_FORMAT)
                ]
            ]
        ];
        $params->addData($pickup);
    }

    /**
     * @return \DateTimeInterface
     */
    protected function getEarliest()
    {
        return $this->getNextDayDate()->setTime(10, 0);
    }

    /**
     * @return \DateTimeInterface
     */
    protected function getLatest()
    {
        return $this->getNextDayDate()->setTime(18, 0);
    }

    /**
     * @return \DateTimeInterface
     */
    protected function getNextDayDate()
    {
        return $this->date->date()->setTimestamp(strtotime('+1 day'));
    }
}
