<?php

require_once dirname(__FILE__, 2) . '/inc/helpers/WC_JumiaPay_Cleaner.php';

use PHPUnit\Framework\TestCase;

class WC_JumiaPay_Purchase_Test extends TestCase
{
  public function test_filterFN_allEmpty()
  {
    $data = [
      "description" => "",
      "amount" => [
        "value" => "",
        "currency" => ""
      ],
      "merchant" => [
        "referenceId" => "",
        "callbackUrl" => "",
        "returnUrl" => ""
      ],
      "consumer" => [
        "emailAddress" => "",
        "ipAddress" => "",
        "country" => "",
        "mobilePhoneNumber" => "",
        "language" => "",
        "name" => "",
        "firstName" => "",
        "lastName" => ""
      ],
      "shippingAddress" => [
        "addressPrimary" => "",
        "addressSecondary" => "",
        "city" => "",
        "district" => "",
        "province" => "",
        "zip" => "",
        "country" => "",
        "name" => "",
        "firstName" => "",
        "lastName" => "",
        "mobilePhoneNumber" => ""
      ],
      "billingAddress" => [
        "addressPrimary" => null,
        "addressSecondary" => null,
        "city" => null,
        "district" => null,
        "province" => null,
        "zip" => null,
        "country" => null,
        "name" => null,
        "firstName" => null,
        "lastName" => null,
        "mobilePhoneNumber" => null
      ],
      "basket" => [
        "shippingAmount" => "",
        "currency" => "",
        "items" => [
          [
            "name" => "",
            "amount" => "",
            "quantity" => "",
            "currency" => ""
          ]
        ]
      ]
    ];

    $filtered = WC_JumiaPay_Cleaner::filterNotNull($data);
    $this->assertArrayNotHasKey('description', $filtered);
    $this->assertArrayNotHasKey('amount', $filtered);
    $this->assertArrayNotHasKey('merchant', $filtered);
    $this->assertArrayNotHasKey('consumer', $filtered);
    $this->assertArrayNotHasKey('billingAddress', $filtered);
    $this->assertArrayNotHasKey('shippingAddress', $filtered);
    $this->assertArrayNotHasKey('basket', $filtered);
  }

  public function test_filterFN_someEmpty()
  {
    $data = [
      "description" => "description",
      "amount" => [
        "value" => "10",
        "currency" => "EGP"
      ],
      "merchant" => [
        "referenceId" => "reference",
        "callbackUrl" => "",
        "returnUrl" => ""
      ],
      "consumer" => [
        "emailAddress" => "email@mail.com",
        "ipAddress" => "",
        "country" => "",
        "mobilePhoneNumber" => "",
        "language" => "",
        "name" => "",
        "firstName" => "",
        "lastName" => ""
      ],
      "shippingAddress" => [
        "addressPrimary" => "primary",
        "addressSecondary" => "",
        "city" => "",
        "district" => "",
        "province" => "",
        "zip" => "",
        "country" => "",
        "name" => "",
        "firstName" => "",
        "lastName" => "",
        "mobilePhoneNumber" => ""
      ],
      "billingAddress" => [
        "addressPrimary" => "primary",
        "addressSecondary" => null,
        "city" => null,
        "district" => null,
        "province" => null,
        "zip" => null,
        "country" => null,
        "name" => null,
        "firstName" => null,
        "lastName" => null,
        "mobilePhoneNumber" => null
      ],
      "basket" => [
        "shippingAmount" => "",
        "currency" => "",
        "items" => [
          [
            "name" => "",
            "amount" => "10",
            "quantity" => "",
            "currency" => ""
          ]
        ]
      ]
    ];

    $filtered = WC_JumiaPay_Cleaner::filterNotNull($data);
    $this->assertEquals('description', $filtered['description']);
    
    $this->assertEquals('10', $filtered['amount']['value']);
    $this->assertEquals('EGP', $filtered['amount']['currency']);
    
    $this->assertEquals('reference', $filtered['merchant']['referenceId']);
    $this->assertArrayNotHasKey('callbackUrl', $filtered['merchant']);
    $this->assertArrayNotHasKey('returnUrl', $filtered['merchant']);

    $this->assertEquals('email@mail.com', $filtered['consumer']['emailAddress']);
    $this->assertArrayNotHasKey('ipAddress', $filtered['consumer']);
    $this->assertArrayNotHasKey('country', $filtered['consumer']);
    $this->assertArrayNotHasKey('mobilePhoneNumber', $filtered['consumer']);
    $this->assertArrayNotHasKey('language', $filtered['consumer']);
    $this->assertArrayNotHasKey('name', $filtered['consumer']);
    $this->assertArrayNotHasKey('firstName', $filtered['consumer']);
    $this->assertArrayNotHasKey('lastName', $filtered['consumer']);

    $this->assertEquals('primary', $filtered['shippingAddress']['addressPrimary']);
    $this->assertArrayNotHasKey('addressSecondary', $filtered['shippingAddress']);
    $this->assertArrayNotHasKey('city', $filtered['shippingAddress']);
    $this->assertArrayNotHasKey('district', $filtered['shippingAddress']);
    $this->assertArrayNotHasKey('province', $filtered['shippingAddress']);
    $this->assertArrayNotHasKey('zip', $filtered['shippingAddress']);
    $this->assertArrayNotHasKey('country', $filtered['shippingAddress']);
    $this->assertArrayNotHasKey('mobilePhoneNumber', $filtered['shippingAddress']);
    $this->assertArrayNotHasKey('name', $filtered['shippingAddress']);
    $this->assertArrayNotHasKey('firstName', $filtered['shippingAddress']);
    $this->assertArrayNotHasKey('lastName', $filtered['shippingAddress']);

    $this->assertEquals('primary', $filtered['billingAddress']['addressPrimary']);
    $this->assertArrayNotHasKey('addressSecondary', $filtered['billingAddress']);
    $this->assertArrayNotHasKey('city', $filtered['billingAddress']);
    $this->assertArrayNotHasKey('district', $filtered['billingAddress']);
    $this->assertArrayNotHasKey('province', $filtered['billingAddress']);
    $this->assertArrayNotHasKey('zip', $filtered['billingAddress']);
    $this->assertArrayNotHasKey('country', $filtered['billingAddress']);
    $this->assertArrayNotHasKey('mobilePhoneNumber', $filtered['billingAddress']);
    $this->assertArrayNotHasKey('name', $filtered['billingAddress']);
    $this->assertArrayNotHasKey('firstName', $filtered['billingAddress']);
    $this->assertArrayNotHasKey('lastName', $filtered['billingAddress']);

    $this->assertArrayNotHasKey('shippingAmount', $filtered['basket']);
    $this->assertArrayNotHasKey('currency', $filtered['basket']);
    $this->assertEquals('10', $filtered['basket']['items'][0]['amount']);
    $this->assertArrayNotHasKey('name', $filtered['basket']['items'][0]);
    $this->assertArrayNotHasKey('quantity', $filtered['basket']['items'][0]);
    $this->assertArrayNotHasKey('currency', $filtered['basket']['items'][0]);
  }
}