<?php

namespace App\Tests;

use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ApiTestCase extends WebTestCase
{

    protected static KernelBrowser $client;

    protected function setUp(): void
    {
        static::$client = static::createClient();
    }

    protected static function assertJsonResponse()
    {
        static::ensureClientIsBooted();

        static::assertJson(static::$client->getResponse()->getContent());
        static::assertResponseHeaderSame('Content-Type', 'application/json; charset=utf-8');
    }

    protected static function getJsonResponseData()
    {
        static::ensureClientIsBooted();

        return json_decode(static::$client->getResponse()->getContent());
    }

    protected static function jsonRequest(string $method, string $url, array $data = [], array $headers = []): Crawler
    {
        static::ensureClientIsBooted();

        return static::$client->request(
            $method,
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }

    private static function ensureClientIsBooted()
    {
        if (!static::$client) {
            throw new LogicException('Vous ne pouvez pas utiliser static::$client. N\'oubliez pas d\'appeler le setUp() !');
        }
    }
}
