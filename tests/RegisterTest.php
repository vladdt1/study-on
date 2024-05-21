<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\AppFixtures;

class RegisterTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    public function testNoRegister(): void
    {
        $client = AbstractTest::createTestClient();
        $client->disableReboot();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        );        

        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('Продолжить')->form([
            'registration_form[email]' => 'user@gmail.com',
            'registration_form[plainPassword][first]' => 'password',
            'registration_form[plainPassword][second]' => 'password'
        ]);        

        $client->submit($form);

        // Ожидаем, что останемся на той же странице и получим flash сообщение об ошибке
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('div.alert', 'Сервис временно недоступен. Попробуйте позднее.');
    }

    public function testBadPassRegister(): void
    {
        $client = AbstractTest::createTestClient();
        $client->disableReboot();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        );        

        $crawler = $client->request('GET', '/register');
        $buttonCrawlerNode = $crawler->selectButton('Продолжить');
        
        $form = $buttonCrawlerNode->form([
            'registration_form[email]' => 'new@example.com',
            'registration_form[plainPassword][first]' => '123',
            'registration_form[plainPassword][second]' => '123',
        ], 'POST');

        $client->submit($form);

        $this->assertSelectorTextContains('div.u2', 'Ваш пароль должен содержать не менее 6 символов');
    }

    public function testBadPass2Register(): void
    {
        $client = AbstractTest::createTestClient();
        $client->disableReboot();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        );        

        $crawler = $client->request('GET', '/register');
        $buttonCrawlerNode = $crawler->selectButton('Продолжить');
        
        $form = $buttonCrawlerNode->form([
            'registration_form[email]' => 'new@example.com',
            'registration_form[plainPassword][first]' => '1234567',
            'registration_form[plainPassword][second]' => '123456',
        ], 'POST');

        $client->submit($form);

        $this->assertSelectorTextContains('div.u2', 'The values do not match.');
    }

    public function testRegister(): void
    {
        $client = AbstractTest::createTestClient();
        $client->disableReboot();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        );        

        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('Продолжить')->form([
            'registration_form[email]' => 'end4@example.com',
            'registration_form[plainPassword][first]' => '123456',
            'registration_form[plainPassword][second]' => '123456'
        ]);        

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');
    }
}