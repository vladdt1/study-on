<?php

 namespace App\DataFixtures;

 use App\Entity\Course;
 use App\Entity\Lesson;
 use Doctrine\Bundle\FixturesBundle\Fixture;
 use Doctrine\Persistence\ObjectManager;

 class AppFixtures extends Fixture
 {
     public function load(ObjectManager $manager): void
     {
         $phpCourse = new Course();
         $phpCourse
             ->setCode('CODE1')
             ->setName('Веб разработка')
             ->setDescription('Данный курс представляет данные по веб-разработки');

         $lesson = new Lesson();
         $lesson
             ->setName('Что такое веб разработка?')
             ->setContent('Что такое сайт?')
             ->setNumber(1);
         $phpCourse->addLesson($lesson);

         $lesson = new Lesson();
         $lesson
             ->setName('Фронтенд')
             ->setContent('Что такое фронтенд?')
             ->setNumber(2);
         $phpCourse->addLesson($lesson);

         $lesson = new Lesson();
         $lesson
             ->setName('Бэкенд')
             ->setContent('Что такое бэкенд?')
             ->setNumber(3);
         $phpCourse->addLesson($lesson);

         $lesson = new Lesson();
         $lesson
             ->setName('Фреймворки')
             ->setContent('Для чего нужны фреймворки?')
             ->setNumber(4);
         $phpCourse->addLesson($lesson);

         $lesson = new Lesson();
         $lesson
             ->setName('БД')
             ->setContent('Какие базы данных существуют?')
             ->setNumber(5);
         $phpCourse->addLesson($lesson);

         $manager->persist($phpCourse);

         $jsCourse = new Course();
         $jsCourse
             ->setCode('CODE2')
             ->setName('Java')
             ->setDescription('На курсе по Java разработки вы научитесьосновам языка, а так же как правильно писать приложения.');

         $lesson = new Lesson();
         $lesson
             ->setName('Введение')
             ->setContent('Познакомиться с курсом.')
             ->setNumber(1);
         $jsCourse->addLesson($lesson);

         $lesson = new Lesson();
         $lesson
             ->setName('Основы Java')
             ->setContent('Основы языка.')
             ->setNumber(2);
         $jsCourse->addLesson($lesson);

         $lesson = new Lesson();
         $lesson
             ->setName('Java pro')
             ->setContent('Создание приложения на Java.')
             ->setNumber(3);
         $jsCourse->addLesson($lesson);

         $lesson = new Lesson();
         $lesson
             ->setName('Итоги')
             ->setContent('Подведем итоги курса.')
             ->setNumber(4);
         $jsCourse->addLesson($lesson);

         $manager->persist($jsCourse);

         $manager->flush();
     }
 }