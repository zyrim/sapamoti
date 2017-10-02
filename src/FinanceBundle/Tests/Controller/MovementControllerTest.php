<?php

namespace FinanceBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MovementControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/finance/movements');
    }

    public function testEdit()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/finance/movements/edit/{financeMovementId}');
    }

}
