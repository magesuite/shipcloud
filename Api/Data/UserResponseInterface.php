<?php

namespace MageSuite\Shipcloud\Api\Data;

interface UserResponseInterface
{
    const ID = 'id';
    const EMAIL = 'email';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const CUSTOMER_NO = 'customer_no';
    const ENVIROMENT = 'environment';
    const SUBSCRIPTION = 'subscription';

    public function getId(): string;

    public function getEmail(): string;

    public function getFirstName(): string;

    public function getLastName(): string;

    public function getCustomerNo(): string;

    public function getEnvironment(): string;

    public function getSubscription();
}
