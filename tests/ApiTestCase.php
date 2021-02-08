<?php

namespace App\Tests;

use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ApiTestCase extends WebTestCase
{

    protected static ?KernelBrowser $client;
    protected static array $headers = ['CONTENT_TYPE' => 'application/json'];
    protected static ?string $jwt = null;

    protected function setUp(): void
    {
        static::$client = static::createClient();
        static::$jwt = null;
    }

    protected static function assertJsonResponse()
    {
        static::ensureClientIsBooted();

        static::assertJson(static::$client->getResponse()->getContent());

        $availableContentTypes = [
            'application/json; charset=utf-8',
            'application/json'
        ];

        static::assertContains(self::$client->getResponse()->headers->get('Content-Type'), $availableContentTypes);
    }

    protected static function getJsonResponseData()
    {
        static::ensureClientIsBooted();

        return json_decode(static::$client->getResponse()->getContent());
    }

    protected static function getRandomUserEmail(): string
    {
        $emails = [
            'jerome@mail.com',
            'anne@mail.com'
        ];

        return $emails[mt_rand(0, 1)];
    }

    protected static function actAsAuthenticated(User $user = null): User
    {
        static::ensureClientIsBooted();

        if (!$user) {
            $user = self::$container->get(UserRepository::class)->findOneBy(['email' => static::getRandomUserEmail()]);
        }

        static::$jwt = self::$container->get('lexik_jwt_authentication.jwt_manager')->create($user);

        return $user;
    }

    protected static function actAsAnonymous()
    {
        static::$jwt = null;
    }

    protected static function jsonRequest(string $method, string $url, array $data = [], array $headers = []): Crawler
    {
        static::ensureClientIsBooted();

        if (count($headers) === 0) {
            $headers = array_slice(static::$headers, 0);
        }

        if (!empty(static::$jwt)) {
            $headers['HTTP_Authorization'] = 'Bearer ' . static::$jwt;
        }

        $crawler = static::$client->request(
            $method,
            $url,
            [],
            [],
            $headers,
            json_encode($data)
        );

        return $crawler;
    }

    protected static function getUserById(int $id): ?User
    {
        static::ensureClientIsBooted();

        return static::$container->get(UserRepository::class)->find($id);
    }

    protected static function getUserByEmail(string $email): ?User
    {
        static::ensureClientIsBooted();

        return static::$container->get(UserRepository::class)->findOneBy(['email' => $email]);
    }

    protected static function getRandomUser(): User
    {
        static::ensureClientIsBooted();

        $direction = mt_rand(0, 1) ? "ASC" : "DESC";

        return static::$container->get(UserRepository::class)->findOneBy([], ["fullName" => $direction]);
    }

    protected static function getRandomInvoice(): ?Invoice
    {
        static::ensureClientIsBooted();

        $direction = mt_rand(0, 1) ? "ASC" : "DESC";

        return static::$container->get(InvoiceRepository::class)->findOneBy([], ["createdAt" => $direction]);
    }

    private static function ensureClientIsBooted()
    {
        if (!static::$client) {
            throw new LogicException('Vous ne pouvez pas utiliser static::$client. N\'oubliez pas d\'appeler le setUp() !');
        }
    }
}
