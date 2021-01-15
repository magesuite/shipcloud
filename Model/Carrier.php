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

    public function __construct($code, $name, $service)
    {
        $this->code = $code;
        $this->name = $name;
        $this->service = $service;
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
}
