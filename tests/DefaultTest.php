<?php
declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class DefaultTest
 * @package App\Tests
 */
class DefaultTest extends WebTestCase
{
    public function testLoginRequired(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');
        $response = $client->getResponse();
        self::assertSame(302, $response->getStatusCode());
        $headers = $response->headers;
        self::assertInstanceOf(ResponseHeaderBag::class, $headers);
        $location = $headers->get('location');
        self::assertInternalType('string', $location);
        $location = parse_url($location);
        self::assertArrayHasKey('path', $location);
        self::assertSame('/login', $location['path']);
    }
}
