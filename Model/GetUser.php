<?php

namespace MageSuite\Shipcloud\Model;

class GetUser extends AbstractClient implements \MageSuite\Shipcloud\Api\GetUserInterface
{
    /**
     * @var \MageSuite\Shipcloud\Api\Data\UserResponseInterfaceFactory
     */
    protected $userResponseFactory;

    public function __construct(
        \Magento\Framework\HTTP\Client\CurlFactory $curlFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \MageSuite\Shipcloud\Helper\Configuration $configuration,
        \Psr\Log\LoggerInterface $logger,
        \MageSuite\Shipcloud\Api\Data\UserResponseInterfaceFactory $userResponseFactory
    ) {
        $this->userResponseFactory = $userResponseFactory;
        parent::__construct($curlFactory, $json, $configuration, $logger);
    }

    /**
     * @return \MageSuite\Shipcloud\Api\Data\UserResponseInterface
     * @throws \MageSuite\Shipcloud\Exception\ForbiddenException
     * @throws \MageSuite\Shipcloud\Exception\PaymentRequiredException
     * @throws \MageSuite\Shipcloud\Exception\ShipcloudException
     * @throws \MageSuite\Shipcloud\Exception\UnauthorizedException
     * @throws \MageSuite\Shipcloud\Exception\UnprocessableEntityException
     */
    public function execute()
    {
        $response = $this->call('me');
        $userResponse = $this->userResponseFactory
            ->create(['data' => $response]);

        return $userResponse;
    }
}
