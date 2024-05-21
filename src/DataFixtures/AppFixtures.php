<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Service\BillingClient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private BillingClient $billingService;

    public function __construct(BillingClient $billingService)
    {
        $this->billingService = $billingService;
    }

    public function load(ObjectManager $objectManager): void
    {
        // Запрашиваем курсы через API
        $courseList = $this->billingService->getCourses();

        foreach ($courseList as $courseInfo) {
            $course = new Course();
            $course
                ->setCode($courseInfo['code'])
                ->setTitle($courseInfo['title'])
                ->setDescription($courseInfo['description'])
                ->setType($courseInfo['type'])
                ->setPrice($courseInfo['price']);

            $this->attachLessonsToCourse($objectManager, $course, $courseInfo['code']);

            $objectManager->persist($course);
        }

        $objectManager->flush();
    }

    private function attachLessonsToCourse(ObjectManager $objectManager, Course $course, string $courseCode): void
    {
        $lessonDetails = $this->retrieveLessonData($courseCode);

        foreach ($lessonDetails as $lessonInfo) {
            $lesson = new Lesson();
            $lesson
                ->setName($lessonInfo['name'])
                ->setContent($lessonInfo['content'])
                ->setNumber($lessonInfo['number'])
                ->setCourse($course);
            $objectManager->persist($lesson);
        }
    }

    private function retrieveLessonData(string $courseCode): array
    {
        $lessonArray = [];

        if ($courseCode === 'code1') {
            $lessonArray = [
                [
                    'name' => 'Что такое веб разработка?',
                    'content' => 'Что такое сайт?',
                    'number' => 1,
                ],
                [
                    'name' => 'Фронтенд',
                    'content' => 'Что такое фронтенд?',
                    'number' => 2,
                ],
                [
                    'name' => 'Бэкенд',
                    'content' => 'Что такое бэкенд?',
                    'number' => 3,
                ],
                [
                    'name' => 'Фреймворки',
                    'content' => 'Для чего нужны фреймворки?',
                    'number' => 4,
                ],
                [
                    'name' => 'БД',
                    'content' => 'Что такое бэкенд??',
                    'number' => 5,
                ]
            ];
        } elseif ($courseCode === 'code2') {
            $lessonArray = [
                [
                    'name' => 'Введение',
                    'content' => 'Познакомиться с курсом.',
                    'number' => 1,
                ],
                [
                    'name' => 'Основы Java',
                    'content' => 'Основы языка.',
                    'number' => 2,
                ],
                [
                    'name' => 'Java pro',
                    'content' => 'Создание приложения на Java.',
                    'number' => 3,
                ],
                [
                    'name' => 'Итоги',
                    'content' => 'Подведем итоги курса.',
                    'number' => 4,
                ],
            ];
        }

        return $lessonArray;
    }
}
