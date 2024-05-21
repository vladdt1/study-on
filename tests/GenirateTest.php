<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\AppFixtures;

class CourseTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    public function testSomething(): void
    {
        $client = static::createTestClient();

        // Подменяем реальный сервис биллинга на мок
        $client->getContainer()->set(
            BillingClient::class, 
            new BillingClientMock()
        ); 

        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Войти')->form([
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');

        $url = '/courses/';

        $crawler = $client->request('GET', $url);
        $link = $crawler->selectLink('Продолжить обучение')->link();
        $crawler = $client->click($link);
	
        $this->assertResponseOk();
    }

    public function testSearchTitle(): void
    {
        $client = static::createTestClient();
        $url = '/courses/';

        $checkTitle = $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $checkTitle->filter('h4'));
    }

    public function testSearchCourse(): void
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
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');
        
        $crawler = $client->request('GET', '/courses/');

        // Находим ссылку на курс по названию
        $link = $crawler->filter('h4:contains("Веб разработка")')->closest('.card')->filter('.btn')->link();

        // Переходим по ссылке
        $crawler = $client->click($link);

        // Теперь мы на странице курса, где можем проверить количество уроков
        $lessonsCount = $crawler->filter('ol > li')->count();
        $this->assertSame(5, $lessonsCount, 'Ожидалось 5 уроков на странице курса "Веб разработка"');
    }

    public function testEditCourse(): void
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
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');

        $crawler = $client->request('GET', '/courses/');

        // Находим ссылку на курс по названию
        $link = $crawler->filter('h4:contains("Веб разработка")')->closest('.card')->filter('.btn')->link();

        // Переходим по ссылке
        $crawler = $client->click($link);

        $link = $crawler->selectLink('Редактировать')->link();
        $crawler = $client->click($link);
	
        $this->assertResponseOk();

        // Заполняем форму редактирования
        $form = $crawler->selectButton('Сохранить')->form([
            'course[code]' => 'code1',
            'course[title]' => 'Веб разработка',
            'course[description]' => 'Данный курс создан для начинающих веб разработчиков',
            'course[type]' => 2,
            'course[price]' => 29.9,
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Проверяем наличие только что измененного описания курса
        $this->assertSelectorTextContains('html', 'Данный курс создан для начинающих веб разработчиков');
    }

    public function testEditLesson(): void
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
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');
        
        $crawler = $client->request('GET', '/courses/');

        // Находим ссылку на курс по названию
        $link = $crawler->filter('h4:contains("Веб разработка")')->closest('.card')->filter('.btn')->link();

        // Переходим по ссылке
        $crawler = $client->click($link);

        // Открываем урок
        $link = $crawler->selectLink('Что такое веб разработка?')->link();
        $crawler = $client->click($link);
	
        $this->assertResponseOk();

        // Нажимаем на кнопку редактирования урока
        $link = $crawler->selectLink('Редактировать')->link();
        $crawler = $client->click($link);
	
        $this->assertResponseOk();

        // Меняем описание урока
        $form = $crawler->selectButton('Сохранить')->form([
            'lesson[name]' => 'Что такое веб разработка??',
            'lesson[content]' => 'Что такое сайт и как его создать?',
            'lesson[number]' => 1,
        ]);
        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorTextContains('html', 'Что такое веб разработка??');
    }

    public function testShowNotFound(): void
    {
        $client = static::createTestClient();

        $client->request('GET', '/lessons/99999');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateAndDeleteCourse(): void
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
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);

        $client->submit($form);

        // Проверяем редирект на страницу профиля или курсов
        $this->assertResponseRedirects('/courses/');

        // Шаг 1: Создание курса
        $crawler = $client->request('GET', '/courses/new');
        $form = $crawler->selectButton('Сохранить')->form([
            'course[code]' => 'TEST4',
            'course[title]' => 'Тестовый курс 4',
            'course[description]' => 'Описание тестового курса',
            'course[type]' => 0,
            'course[price]' => 0,
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Проверяем наличие только что созданного курса
        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();
        $this->assertSelectorTextContains('html', 'Тестовый курс 4');

        // Находим ссылку на курс по названию
        $link = $crawler->filter('h4:contains("Тестовый курс 4")')->closest('.card')->filter('.btn')->link();

        // Переходим по ссылке
        $crawler = $client->click($link);

        // Удаление тестового курса
        $form = $crawler->selectButton('Удалить')->form();
        $client->submit($form);

        // Проверка редиректа после удаления
        $this->assertResponseRedirects();

        // После удаления курса
        $crawler = $client->followRedirect();

        // Проверяем, что количество курсов на странице уменьшилось на один
        // предполагаем, что до удаления было 4 курса, после удаления должно быть 3
        $this->assertCount(3, $crawler->filter('.card-body h4'));
    }
}