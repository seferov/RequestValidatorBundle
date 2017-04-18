<?php

namespace Seferov\RequestValidatorBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Class RequestValidatorTest.
 *
 * @author Farhad Safarov <farhad.safarov@gmail.com>
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
            'name'  => 'ab',
            'email' => 'not-email',
            'order' => 'choice',
            'page'  => 'ok',
        ]);

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals([
            'ab'        => 'This value is too short. It should have 3 characters or more.',
            'not-email' => 'This value is not a valid email address.',
            'choice'    => 'The value you selected is not a valid choice.',
            'ok'        => 'This value should be a valid number.',
        ], $content);
    }

    public function testDefaultValue()
    {
        $this->client->request('GET', '/default');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals([
            'page'  => 1,
            'order' => 'asc',
        ], $content);
    }

    public function testNotRequired()
    {
        $this->client->request('GET', '/not-required');

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $content);
    }

    public function testRequired()
    {
        $this->client->request('GET', '/required');

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('firstname', $content);
        $this->assertArrayHasKey('email', $content);
    }

    public function testNotBlankConstraint()
    {
        $this->client->request('GET', '/not-blank-constraint');

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $content);
    }

    public function testNoValidatorAnnotation()
    {
        $this->client->request('GET', '/no-validator-annotation');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
