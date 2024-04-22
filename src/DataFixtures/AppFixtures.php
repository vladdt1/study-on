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
        $coursesData = [
            [
                'code' => 'code1',
                'name' => 'Веб разработка',
                'description' => 'Данный курс представляет данные по веб-разработке'
            ],
            [
                'code' => 'code2',
                'name' => 'Java разработка',
                'description' => 'На курсе по Java разработки вы научитесь основам языка, а так же как правильно писать приложения.'
            ]
        ];

        $lessonsData = [
            'code1' => [
                ['name' => 'Что такое веб разработка?', 'content' => 'Что такое сайт?', 'number' => 1],
                ['name' => 'Фронтенд', 'content' => 'Что такое фронтенд?', 'number' => 2],
                ['name' => 'Бэкенд', 'content' => 'Что такое бэкенд?', 'number' => 3],
                ['name' => 'Фреймворки', 'content' => 'Для чего нужны фреймворки?', 'number' => 4],
                ['name' => 'БД', 'content' => 'Какие базы данных существуют?', 'number' => 5]
            ],
            'code2' => [
                ['name' => 'Введение', 'content' => 'Познакомиться с курсом.', 'number' => 1],
                ['name' => 'Основы Java', 'content' => 'Основы языка.', 'number' => 2],
                ['name' => 'Java pro', 'content' => 'Создание приложения на Java.', 'number' => 3],
                ['name' => 'Итоги', 'content' => 'Подведем итоги курса.', 'number' => 4]
            ]
        ];

        foreach ($coursesData as $courseData) {
            $course = new Course();
            $course->setCode($courseData['code'])
                   ->setName($courseData['name'])
                   ->setDescription($courseData['description']);

            // Добавляем уроки к курсу
            if (array_key_exists($course->getCode(), $lessonsData)) {
                foreach ($lessonsData[$course->getCode()] as $lessonData) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonData['name'])
                           ->setContent($lessonData['content'])
                           ->setNumber($lessonData['number']);
                    $course->addLesson($lesson);
                }
            }

            $manager->persist($course);
        }

        $manager->flush();
    }
}
