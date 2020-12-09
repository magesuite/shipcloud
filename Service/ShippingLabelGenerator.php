<?php

namespace MageSuite\Shipcloud\Service;

class ShippingLabelGenerator
{
    /**
     * @var \Magento\Framework\HTTP\Client\CurlFactory
     */
    protected $curlFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \MageSuite\Shipcloud\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\Shipcloud\Model\ResourceModel\Shipment\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \MageSuite\Shipcloud\Model\ResourceModel\Shipment
     */
    protected $shipmentResourceModel;

    /**
     * @var \MageSuite\Shipcloud\Model\CarrierList
     */
    protected $carrierList;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    public function __construct(
        \Magento\Framework\HTTP\Client\CurlFactory $curlFactory,
        \Magento\Framework\Filesystem\Io\File $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \MageSuite\Shipcloud\Helper\Configuration $configuration,
        \MageSuite\Shipcloud\Model\ResourceModel\Shipment\CollectionFactory $collectionFactory,
        \MageSuite\Shipcloud\Model\ResourceModel\Shipment $shipmentResourceModel,
        \MageSuite\Shipcloud\Model\CarrierList $carrierList,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->curlFactory = $curlFactory;
        $this->filesystem = $filesystem;
        $this->dateTime = $dateTime;
        $this->configuration = $configuration;
        $this->collectionFactory = $collectionFactory;
        $this->shipmentResourceModel = $shipmentResourceModel;
        $this->carrierList = $carrierList;
        $this->directoryList = $directoryList;
    }

    public function execute(\Magento\Sales\Model\Order $order)
    {
        $this->checkFilesystem($order);
        $carrierName = $this->getCarrierName($order);
        $collection = $this->collectionFactory->create()
            ->addOrderFilter($order->getId())
            ->addFieldToFilter('label_filename', ['null' => true]);

        /** @var \MageSuite\Shipcloud\Model\Shipment $shipment */
        foreach ($collection as $shipment) {
            $shipment->setOrder($order);
            $url = $shipment->getLabelUrl();
            $curl = $this->curlFactory->create();
            $curl->get($url);

            if ($curl->getStatus() !== 200) {
                throw new \Exception('Unable to download print label!');
            }

            $fileName = sprintf(
                '%s_%s_%s_%s.pdf',
                $order->getId(),
                $carrierName,
                $shipment->getPackageNumber(),
                $this->dateTime->gmtDate('YmdHis')
            );
            $shipment->setLabelFilename($fileName);
            $tmpFilePath = $this->getFilePathTmp() . DIRECTORY_SEPARATOR . $fileName;
            $this->filesystem->write(
                $tmpFilePath,
                $curl->getBody()
            );
            $this->filesystem->mv($tmpFilePath, $shipment->getLabelFilePath());
            $this->shipmentResourceModel->save($shipment);
        }
    }

    public function getFilePathTmp()
    {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . DIRECTORY_SEPARATOR . 'shipcloud';
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getFilePath($storeId)
    {
        return $this->configuration->getExportPath($storeId);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool|true
     * @throws \Exception
     */
    protected function checkFilesystem(\Magento\Sales\Model\Order $order)
    {
        $paths = [
            $this->getFilePath($order->getStoreId()),
            $this->getFilePathTmp()
        ];

        foreach ($paths as $path) {
            $this->filesystem->checkAndCreateFolder($path);

            if (!$this->filesystem->isWriteable($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     * @throws \Exception
     */
    protected function getCarrierName(\Magento\Sales\Model\Order $order)
    {
        $carrier = $this->carrierList->getOrderCarrier($order);

        if (!$carrier instanceof \MageSuite\Shipcloud\Model\Carrier) {
            throw new \Exception(__('Unable to retrieve carrier.'));
        }

        return strtoupper($carrier->getName());
    }
}
