<?php

namespace MageSuite\Shipcloud\Model;

class GetShipment extends AbstractClient implements \MageSuite\Shipcloud\Api\GetShipmentInterface
{
    /**
     * @var \MageSuite\Shipcloud\Api\Data\ShipmentResponseInterfaceFactory
     */
    protected $shipmentResponseFactory;

    /**
     * @var \MageSuite\Shipcloud\Model\Converter\Order
     */
    protected $converter;

    /**
     * @var string[]
     */
    protected $requiredParams = [
        'to' => [
            'last_name',
            'street',
            'street_no',
            'city',
            'zip_code',
            'country'
        ]
    ];

    public function __construct(
        \Magento\Framework\HTTP\Client\CurlFactory $curlFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \MageSuite\Shipcloud\Helper\Configuration $configuration,
        \Psr\Log\LoggerInterface $logger,
        \MageSuite\Shipcloud\Api\Data\ShipmentResponseInterfaceFactory $shipmentResponseFactory,
        \MageSuite\Shipcloud\Model\Converter\Order $converter
    ) {
        $this->shipmentResponseFactory = $shipmentResponseFactory;
        $this->converter = $converter;
        parent::__construct($curlFactory, $json, $configuration, $logger);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \MageSuite\Shipcloud\Api\Data\ShipmentResponseInterface
     * @throws \MageSuite\Shipcloud\Exception\ForbiddenException
     * @throws \MageSuite\Shipcloud\Exception\PaymentRequiredException
     * @throws \MageSuite\Shipcloud\Exception\ShipcloudException
     * @throws \MageSuite\Shipcloud\Exception\UnauthorizedException
     * @throws \MageSuite\Shipcloud\Exception\UnprocessableEntityException
     */
    public function execute(\Magento\Sales\Model\Order $order)
    {
        $params = $this->converter->toShipment($order);
        $response = $this->call('shipments', $params);
        $shipmentResponse = $this->shipmentResponseFactory
            ->create()
            ->addData($response);

        return $shipmentResponse;
    }
}
