<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\AppFixtures;

class BuyCourseTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    public function testRegisterr(): void
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
            'registration_form[email]' => 'buy1@example.com',
            'registration_form[plainPassword][first]' => '123456',
            'registration_form[plainPassword][second]' => '123456'
        ]);        

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');
    }

    public function testDepositForm(): void
    {
        $client = static::createTestClient();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        ); 

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Войти')->form([
            'email' => 'buy1@example.com',
            'password' => '123456'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу курсов
        $this->assertResponseRedirects('/courses/');
        $crawler = $client->followRedirect();

        // Переход на страницу профиля
        $crawler = $client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();

        // Проверка наличия формы пополнения баланса
        $this->assertSelectorExists('form[action="/profile/deposit"]');

        // Заполнение и отправка формы
        $form = $crawler->selectButton('Пополнить')->form([
            'amount' => 100
        ]);
        $client->submit($form);

        // Проверка редиректа после успешного пополнения
        $this->assertResponseRedirects('/profile');
        $crawler = $client->followRedirect();

        // Проверка обновленного баланса пользователя
        // Это можно сделать, если страница профиля отображает текущий баланс
        $this->assertSelectorTextContains('.balance', 'Баланс: 100 руб.');
    }

    public function testBuyCourse(): void
    {
        $client = static::createTestClient();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        ); 

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Войти')->form([
            'email' => 'buy1@example.com',
            'password' => '123456'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');
        $crawler = $client->followRedirect();

        // Проверка наличия кнопки покупки курса
        $this->assertSelectorExists('.btn-success.buy-course');

        // Заполнение и отправка формы покупки курса
        $form = $crawler->filter('.btn-success.buy-course')->form();
        $client->submit($form);

        // Проверка редиректа после успешной покупки
        $this->assertResponseRedirects('/courses/code4');
        $crawler = $client->followRedirect();
    }
}