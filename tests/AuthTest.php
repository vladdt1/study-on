<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\Mock\BillingClientMock;

class AuthTest extends WebTestCase
{
    public function testAuth(): void
    {
        $client = static::createClient();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        ); 

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Войти')->form([
            'email' => 'user@gmail.com',
            'password' => 'password'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');
    }

    public function testNoAuth(): void
    {
        $client = static::createClient();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        ); 

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Войти')->form([
            'email' => 'notest@example.com',
            'password' => '123456'
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(401);
    }
}