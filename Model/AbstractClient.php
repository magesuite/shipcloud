<?php

namespace MageSuite\Shipcloud\Model;

abstract class AbstractClient
{
    const API_URL = 'https://api.shipcloud.io/v1/';

    /**
     * @var \Magento\Framework\HTTP\Client\CurlFactory
     */
    protected $curlFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \MageSuite\Shipcloud\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var string[]
     */
    protected $requiredParams = [];

    public function __construct(
        \Magento\Framework\HTTP\Client\CurlFactory $curlFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \MageSuite\Shipcloud\Helper\Configuration $configuration,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->curlFactory = $curlFactory;
        $this->json = $json;
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    /**
     * @param string $endpoint
     * @param array $params
     * @return array|bool|float|int|mixed|string|null
     * @throws \MageSuite\Shipcloud\Exception\ForbiddenException
     * @throws \MageSuite\Shipcloud\Exception\PaymentRequiredException
     * @throws \MageSuite\Shipcloud\Exception\ShipcloudException
     * @throws \MageSuite\Shipcloud\Exception\UnauthorizedException
     * @throws \MageSuite\Shipcloud\Exception\UnprocessableEntityException
     * @see https://developers.shipcloud.io/concepts/
     */
    public function call(string $endpoint, $params = [])
    {
        $curl = $this->curlFactory->create();
        $curl->addHeader('Content-Type', 'application/json');
        $curl->setCredentials($this->configuration->getApiKey(), '');

        $url = self::API_URL . $endpoint;
        $debugData = [];
        $debugData['url'] = $url;
        $debugData['params'] = $params;
        $this->debugLog($debugData);

        if (!empty($params)) {
            $this->validateRequest($params, $this->requiredParams);
            $curl->post($url, $this->json->serialize($params));
        } else {
            $curl->get($url);
        }

        $debugData = [];
        $debugData['url'] = $url;
        $debugData['response'] =  $curl->getBody();
        $debugData['http_code'] = $curl->getStatus();
        $this->debugLog($debugData);

        if (!in_array($curl->getStatus(), [200, 204])) {
            $this->handleError($curl);
        }

        return $this->jsonDecode($curl->getBody());
    }

    /**
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @throws \MageSuite\Shipcloud\Exception\BadRequestException
     * @throws \MageSuite\Shipcloud\Exception\ForbiddenException
     * @throws \MageSuite\Shipcloud\Exception\NotFoundException
     * @throws \MageSuite\Shipcloud\Exception\PaymentRequiredException
     * @throws \MageSuite\Shipcloud\Exception\ShipcloudException
     * @throws \MageSuite\Shipcloud\Exception\UnauthorizedException
     * @throws \MageSuite\Shipcloud\Exception\UnprocessableEntityException
     */
    protected function handleError(\Magento\Framework\HTTP\Client\Curl $curl)
    {
        $errorMessage = $this->getErrorFromResponse($curl);

        switch ($curl->getStatus()) {
            case 400:
                $e = new \MageSuite\Shipcloud\Exception\BadRequestException(__($errorMessage));
                break;
            case 401:
                $e = new \MageSuite\Shipcloud\Exception\UnauthorizedException(__($errorMessage));
                break;
            case 402:
                $e = new \MageSuite\Shipcloud\Exception\PaymentRequiredException(__($errorMessage));
                break;
            case 403:
                $e = new \MageSuite\Shipcloud\Exception\ForbiddenException(__($errorMessage));
                break;
            case 404:
                $e = new \MageSuite\Shipcloud\Exception\NotFoundException(__($errorMessage));
                break;
            case 422:
                $e = new \MageSuite\Shipcloud\Exception\UnprocessableEntityException(__($errorMessage));
                break;
            case 500:
            case 502:
            case 504:
                $e = new \MageSuite\Shipcloud\Exception\ShipcloudException(__($errorMessage));
                break;
        }

        throw $e;
    }

    /**
     * @param array $params
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateRequest(array $params, $requiredParams = [])
    {
        if (empty($requiredParams)) {
            return;
        }

        foreach ($requiredParams as $paramName => $requiredParam) {
            if (is_array($requiredParam)) {
                if (!isset($params[$paramName]) || empty($params[$paramName])) {
                    throw new \MageSuite\Shipcloud\Exception\MissingParameterException(
                        __('Missing required parameter "%1"', $paramName)
                    );
                }

                $this->validateRequest($params[$paramName], $requiredParam);
            } elseif (!isset($params[$requiredParam]) || empty($params[$requiredParam])) {
                throw new \MageSuite\Shipcloud\Exception\MissingParameterException(
                    __('Missing required parameter "%1"', $requiredParam)
                );
            }
        }
    }

    /**
     * @param array $response
     * @return string
     */
    protected function getErrorFromResponse(\Magento\Framework\HTTP\Client\Curl $curl)
    {
        $response = $this->jsonDecode($curl->getBody());

        if (isset($response['errors']) && is_array($response['errors'])) {
            return implode(' ', $response['errors']);
        }

        return $response;
    }

    /**
     * @param $type
     * @param $data
     * @return $this
     */
    protected function debugLog($debugData)
    {
        if (!$this->configuration->isDebugModeEnabled()) {
            return $this;
        }

        if (isset($debugData['response']) && !empty($debugData['response'])) {
            $debugData['response'] = $this->jsonDecode($debugData['response']);
        }

        $this->logger->debug(var_export($debugData, true));

        return $this;
    }

    protected function jsonDecode($string)
    {
        try {
            $string = $this->json->unserialize($string);
        } catch (\InvalidArgumentException $e) {
            // do nothing
        }

        return $string;
    }
}
