<?php

namespace MageSuite\Shipcloud\Model;

class Carrier
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $service;

    /**
     * @var string
     */
    protected $additionalServices;

    public function __construct($code, $name, $service, $additionalServices = null)
    {
        $this->code = $code;
        $this->name = $name;
        $this->service = $service;
        $this->additionalServices = $additionalServices;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return array
     */
    public function getAdditionalServices()
    {
        if (empty($this->additionalServices)) {
            return [];
        }

        $result = [];

        foreach (explode(',', $this->additionalServices) as $additionalService) {
            $result[] = [
                'name' => trim($additionalService)
            ];
        }

        return $result;
    }
}
