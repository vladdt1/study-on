<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\AppFixtures;

class NoAccessTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    public function testNoAuthAddCourseNoAccess(): void
    {
        $client = AbstractTest::createTestClient();
        $client->disableReboot();

        $crawler = $client->request('GET', '/courses/new');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAddCourseNoAccess(): void
    {
        $client = AbstractTest::createTestClient();
        $client->disableReboot();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        ); 

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Войти')->form([
            'email' => 'new@example.com',
            'password' => '123456'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');
        $client->disableReboot();

        $crawler = $client->request('GET', '/courses/new');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testNoAuthLessonsNoAccess(): void
    {
        $client = AbstractTest::createTestClient();
        $client->disableReboot();

        $crawler = $client->request('GET', '/lessons/');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testLessonsNoAccess(): void
    {
        $client = AbstractTest::createTestClient();
        $client->disableReboot();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        ); 

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Войти')->form([
            'email' => 'new@example.com',
            'password' => '123456'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');

        $client->disableReboot();

        $crawler = $client->request('GET', '/lessons/');

        $this->assertResponseStatusCodeSame(403);
    }
}