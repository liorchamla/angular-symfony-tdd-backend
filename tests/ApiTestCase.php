<?php

namespace App\Tests;

use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\InvoiceRepository;
use App\Repository\UserRepository;
use Exception;
use LogicException;
use RuntimeException;
use stdClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DomCrawler\Crawler;

class ApiTestCase extends WebTestCase
{


    protected static ?KernelBrowser $client;

    /**
     * @var array<string,string>
     */
    protected static array $headers = ['CONTENT_TYPE' => 'application/json'];

    protected static ?string $jwt = null;

    protected function setUp(): void
    {
        static::$client = static::createClient();
        static::$jwt = null;
    }

    /**
     * Asserts that the response has the good content AND content-type
     */
    protected static function assertJsonResponse(): void
    {
        static::ensureClientIsBooted();

        static::assertJson(static::$client->getResponse()->getContent());

        $availableContentTypes = [
            'application/json; charset=utf-8',
            'application/json'
        ];

        static::assertContains(self::$client->getResponse()->headers->get('Content-Type'), $availableContentTypes);
    }

    /**
     * Get data as decoded with the JSON Response
     *
     * @return array<int,\stdClass>|\stdClass Decoded data
     * @throws LogicException Will throw if we have not client booted
     */
    protected static function getJsonResponseData()
    {
        static::ensureClientIsBooted();

        return json_decode(static::$client->getResponse()->getContent());
    }

    /**
     * Sets up a JWT so the next request will be authenticated with Authorization: Bearer
     *
     * @param User|null $user
     *
     * @return User
     *
     * @throws LogicException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    protected static function actAsAuthenticated(User $user = null): User
    {
        static::ensureClientIsBooted();

        if (!$user) {
            $user = static::getRandomUser();
        }

        static::$jwt = static::$container->get('lexik_jwt_authentication.jwt_manager')->create($user);

        return $user;
    }

    /**
     * Unset the JWT so the next request will be anonymous
     */
    protected static function actAsAnonymous(): void
    {
        static::$jwt = null;
    }

    /**
     * Sends a JSON request
     *
     * @param string $method
     * @param string $url
     * @param array<string,string> $data
     * @param array<string,string> $headers
     *
     * @return Crawler
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    protected static function jsonRequest(string $method, string $url, array $data = [], array $headers = []): Crawler
    {
        static::ensureClientIsBooted();

        if (count($headers) === 0) {
            $headers = array_slice(static::$headers, 0);
        }

        if (!empty(static::$jwt)) {
            $headers['HTTP_Authorization'] = 'Bearer ' . static::$jwt;
        }

        return static::$client->request(
            $method,
            $url,
            [],
            [],
            $headers,
            json_encode($data)
        );
    }

    protected static function getViolation(string $propertyPath): ?stdClass
    {
        static::ensureClientIsBooted();

        $json = static::getJsonResponseData();

        if (!$json) {
            throw new LogicException("No JSON data found, have u really made a JSON Request ?");
        }

        try {
            $violations = $json->violations;

            foreach ($violations as $violation) {
                if ($violation->propertyPath === $propertyPath) {
                    return $violation;
                }
            }

            return null;
        } catch (Exception $e) {
            throw new LogicException("No violations were found in this call");
        }
    }

    /**
     * Retrieve a user from the database with its id
     *
     * @param integer $id
     *
     * @return User|null
     */
    protected static function getUserById(int $id): ?User
    {
        static::ensureClientIsBooted();

        return static::$container->get(UserRepository::class)->find($id);
    }

    /**
     * Retrieve a user in the database with its email
     *
     * @param string $email
     *
     * @return User|null
     */
    protected static function getUserByEmail(string $email): ?User
    {
        static::ensureClientIsBooted();

        return static::$container->get(UserRepository::class)->findOneBy(['email' => $email]);
    }

    /**
     * Retrieve a random user in the database
     *
     * @return User
     */
    protected static function getRandomUser(): User
    {
        static::ensureClientIsBooted();

        $direction = mt_rand(0, 1) ? "ASC" : "DESC";

        return static::$container->get(UserRepository::class)->findOneBy([], ["fullName" => $direction]);
    }

    /**
     * Retrieve a random invoice from the database
     *
     * @return Invoice|null
     */
    protected static function getRandomInvoice(): ?Invoice
    {
        static::ensureClientIsBooted();

        $direction = mt_rand(0, 1) ? "ASC" : "DESC";

        return static::$container->get(InvoiceRepository::class)->findOneBy([], ["createdAt" => $direction]);
    }

    /**
     * Makes sure that the client is booted and throws an exception otherwise
     *
     * @throws LogicException
     */
    protected static function ensureClientIsBooted(): void
    {
        if (!static::$client) {
            throw new LogicException(
                'Vous ne pouvez pas utiliser static::$client. N\'oubliez pas d\'appeler le setUp() !'
            );
        }
    }
}
