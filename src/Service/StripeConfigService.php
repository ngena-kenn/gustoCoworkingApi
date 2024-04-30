<?php

// src/Service/StripeConfigService.php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StripeConfigService
{
    private string $stripeSecretKey;

    public function __construct(ParameterBagInterface $params)
    {
        $this->stripeSecretKey = $params->get('stripe_secret_key');
    }

    public function getStripeSecretKey(): string
    {
        return $this->stripeSecretKey;
    }
}
