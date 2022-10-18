<?php
define('ABSPATH', dirname(__FILE__, 2));
define('JPAY_DIR', dirname(__FILE__, 2).'/');

require_once dirname(__FILE__, 2) . '/inc/WC_JumiaPay_Client.php';

use PHPUnit\Framework\TestCase;

class WC_JumiaPay_Client_Test extends TestCase
{
  private $client;

  protected function setUp(): void
  {
    if (!function_exists('sanitize_text_field')) 
    {
      function sanitize_text_field($value)
      {
        return $value;
      }
      function is_wp_error($value)
      {
        return false;
      }
      function update_post_meta($v1, $v2, $v3)
      {
        return true;
      }
    }
    $this->client = new WC_JumiaPay_Client("", "", "", "", "", "", "", "", "", "");
  }

  public function test_getErrorMessage_generalResponse()
  {
    $response = [];
    $message = $this->client->getErrorMessage($response);
    $this->assertEquals("Error Connecting to JumiaPay", $message);
  }

  public function test_getErrorMessage_invalidCredentialsResponse()
  {
    $response = ['message' => 'Invalid authentication credentials'];
    $message = $this->client->getErrorMessage($response);
    $this->assertEquals("Error Connecting to JumiaPay Invalid authentication credentials", $message);
  }

  public function test_getErrorMessage_v1PayloadResponse()
  {
    $response = [
      'internal_code' => 20110,
      'details' => [
        ['message' => 'General Error']
      ]
    ];
    $message = $this->client->getErrorMessage($response);
    $this->assertEquals("Error Connecting to JumiaPay With code [20110] General Error", $message);
  }

  public function test_getErrorMessage_v2PayloadResponse()
  {
    $response = [
      'payload' => [
        [
          'description' => 'General Error',
          'code' => 20110
        ]
      ]
    ];
    $message = $this->client->getErrorMessage($response);
    $this->assertEquals("Error Connecting to JumiaPay With code [20110] General Error", $message);
  }
}
