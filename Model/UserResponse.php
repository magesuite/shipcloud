<?php

namespace MageSuite\Shipcloud\Model;

class UserResponse extends \Magento\Framework\DataObject implements \MageSuite\Shipcloud\Api\Data\UserResponseInterface
{
    /**
     * @var \MageSuite\Shipcloud\Model\UserResponse\SubscriptionFactory
     */
    protected $subscriptionFactory;

    public function __construct(
        \MageSuite\Shipcloud\Model\UserResponse\SubscriptionFactory $subscriptionFactory,
        array $data = []
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        parent::__construct($data);
    }

    public function getId(): string
    {
        return (string)$this->getData(self::ID);
    }

    public function getEmail(): string
    {
        return (string)$this->getData(self::EMAIL);
    }

    public function getFirstName(): string
    {
        return (string)$this->getData(self::FIRST_NAME);
    }

    public function getLastName(): string
    {
        return (string)$this->getData(self::LAST_NAME);
    }

    public function getCustomerNo(): string
    {
        return (string)$this->getData(self::CUSTOMER_NO);
    }

    public function getEnvironment(): string
    {
        return (string)$this->getData(self::ENVIROMENT);
    }

    public function getSubscription()
    {
        $subscription = $this->getData(self::SUBSCRIPTION);

        if (is_array($subscription)) {
            $subscription = $this->subscriptionFactory
                ->create()
                ->addData($subscription);
            $this->setData(self::SUBSCRIPTION, $subscription);
        }

        return $subscription;
    }
}
