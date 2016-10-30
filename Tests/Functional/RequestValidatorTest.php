<?php

namespace Seferov\RequestValidatorBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Class RequestValidatorTest
 */
class RequestValidatorTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = $this->createClient();
    }

    public function testViolations()
    {
        $this->client->request('GET', '/violations', [
            'name' => 'ab',
            'email' => 'not-email',
            'order' => 'choice',
            'page' => 'ok',
        ]);

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals([
            'ab' => 'This value is too short. It should have 3 characters or more.',
            'not-email' => 'This value is not a valid email address.',
            'choice' => 'The value you selected is not a valid choice.',
            'ok' => 'This value should be a valid number.',
        ], $content);
    }

    public function testDefaultValue()
    {
        $this->client->request('GET', '/default');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals([
            'page' => 1,
            'order' => 'asc',
        ], $content);
    }
}
