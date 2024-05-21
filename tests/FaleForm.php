<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\AbstractTest;
use App\Tests\Mock\BillingClientMock;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use App\DataFixtures\AppFixtures;

class FaleForm extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [
            AppFixtures::class,
        ];
    }

    public function testNoNameEditCourse(): void
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
            'course[title]' => '',
            'course[description]' => 'Данный курс создан для начинающих веб разработчиков',
        ]);
        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Название не может быть пустым');
    }

    public function testNoCodeEditCourse(): void
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
            'course[code]' => '',
            'course[title]' => 'Веб разработка',
            'course[description]' => 'Данный курс создан для начинающих веб разработчиков',
            'course[type]' => 2,
            'course[price]' => 29.9,
        ]);
        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Символьный код не может быть пустым');
    }

    public function testNoDescriptionEditCourse(): void
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
            'course[code]' => 'CODE 1',
            'course[title]' => 'Веб разработка',
            'course[description]' => '',
            'course[type]' => 2,
            'course[price]' => 29.9,
        ]);
        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Описание не может быть пустым');
    }

    public function testNameEditCourse(): void
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
            'course[code]' => 'CODE 1',
            'course[title]' => '123456789101112131415161718192021222324252728293031323334353637383940',
            'course[description]' => 'Данный курс создан для начинающих веб разработчиков',
            'course[type]' => 2,
            'course[price]' => 29.9,
        ]);
        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Название должно быть не более 55 символов');
    }

    public function testNoDescriptionCreateCourse(): void
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
            'course[code]' => 'TEST1',
            'course[title]' => 'Тестовый курс 1',
            'course[description]' => '',
            'course[type]' => 2,
            'course[price]' => 29.9,
        ]);
        $client->submit($form);
        $this->assertSelectorTextContains('html', 'Описание не может быть пустым');
    }

    public function testNoCodeCreateCourse(): void
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
            'course[code]' => '',
            'course[title]' => 'Тестовый курс 1',
            'course[description]' => 'Описание тестового курса',
            'course[type]' => 2,
            'course[price]' => 29.9,
        ]);
        $client->submit($form);
        $this->assertSelectorTextContains('html', 'Символьный код не может быть пустым');
    }

    public function testNoNameCreateCourse(): void
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
            'course[code]' => 'TEST1',
            'course[title]' => '',
            'course[description]' => 'Описание тестового курса',
            'course[type]' => 2,
            'course[price]' => 29.9,
        ]);
        $client->submit($form);
        $this->assertSelectorTextContains('html', 'Название не может быть пустым');
    }

    public function testNoNameEditLesson(): void
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
            'lesson[name]' => '',
            'lesson[content]' => 'Что такое сайт и как его создать?',
            'lesson[number]' => 1,
        ]);
        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Название не может быть пустым');
    }

    public function testNoContentEditLesson(): void
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
            'lesson[name]' => 'Что такое',
            'lesson[content]' => '',
            'lesson[number]' => 1,
        ]);
        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Содержимое урока не может быть пустым');
    }

    public function testNoNumberEditLesson(): void
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
            'lesson[name]' => 'Что такое',
            'lesson[content]' => 'Что васвас ыса',
            'lesson[number]' => 100000
        ]);
        $client->submit($form);

        $this->assertSelectorTextContains('html', 'Значение поля должно быть в пределах от 1 до 10000');
    }
}