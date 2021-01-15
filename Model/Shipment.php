<?php

namespace MageSuite\Shipcloud\Model;

class Shipment extends \Magento\Framework\Model\AbstractModel implements \MageSuite\Shipcloud\Api\Data\ShipmentInterface
{
    const CACHE_TAG = 'shipcloud_shipment';

    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \MageSuite\Shipcloud\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \MageSuite\Shipcloud\Helper\Configuration $configuration,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->orderFactory = $orderFactory;
        $this->configuration = $configuration;
    }

    protected function _construct()
    {
        $this->_init(\MageSuite\Shipcloud\Model\ResourceModel\Shipment::class);
    }

    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    public function getShipcloudId()
    {
        return $this->getData(self::SHIPCLOUD_ID);
    }

    public function setShipcloudId($shipcloudId)
    {
        return $this->setData(self::SHIPCLOUD_ID, $shipcloudId);
    }

    public function getCarrierTrackingNo()
    {
        return $this->getData(self::CARRIER_TRACKING_NO);
    }

    public function setCarrierTrackingNo($carrierTrackingNo)
    {
        return $this->setData(self::CARRIER_TRACKING_NO, $carrierTrackingNo);
    }

    public function getTrackingUrl()
    {
        return $this->getData(self::TRACKING_URL);
    }

    public function setTrackingUrl($trackingUrl)
    {
        return $this->setData(self::TRACKING_URL, $trackingUrl);
    }

    public function getLabelUrl()
    {
        return $this->getData(self::LABEL_URL);
    }

    public function setLabelUrl($labelUrl)
    {
        return $this->setData(self::LABEL_URL, $labelUrl);
    }

    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    public function getLabelFilename()
    {
        return $this->getData(self::LABEL_FILENAME);
    }

    public function setLabelFilename($labelFilename)
    {
        return $this->setData(self::LABEL_FILENAME, $labelFilename);
    }

    public function getPackageNumber()
    {
        return $this->getData(self::PACKAGE_NUMBER);
    }

    public function setPackageNumber($packageNumber)
    {
        return $this->setData(self::PACKAGE_NUMBER, $packageNumber);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function getLabelFilePath()
    {
        if (empty($this->getLabelFilename())) {
            throw new \InvalidArgumentException('Label File name must be specified');
        }

        $storeId = $this->getOrder()->getStoreId();

        return $this->configuration->getExportPath($storeId) . DIRECTORY_SEPARATOR . $this->getLabelFilename();
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->order === null) {
            $this->order = $this->orderFactory->create();

            if ($this->getOrderId()) {
                $this->order->load($this->getOrderId());
            }
        }

        return $this->order;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->order = $order;

        return $this;
    }
}
