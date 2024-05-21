<?php
namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Service\BillingClient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private BillingClient $billingClient;

    public function __construct(BillingClient $billingClient)
    {
        $this->billingClient = $billingClient;
    }

    public function load(ObjectManager $manager): void
    {
        $courses = $this->billingClient->getCourses();

        foreach ($courses as $courseData) {
            $course = new Course();
            $course
                ->setCode($courseData['code'])
                ->setTitle($courseData['title'])
                ->setDescription($courseData['description'])
                ->setType($courseData['type'])
                ->setPrice($courseData['price']);

            $this->addLessonsToCourse($manager, $course, $courseData['code']);

            $manager->persist($course);
        }

        $manager->flush();
    }

    private function addLessonsToCourse(ObjectManager $manager, Course $course, string $courseCode): void
    {
        $lessonsData = $this->getLessonsDataForCourse($courseCode);

        foreach ($lessonsData as $lessonData) {
            $lesson = new Lesson();
            $lesson
                ->setName($lessonData['name'])
                ->setContent($lessonData['content'])
                ->setNumber($lessonData['number'])
                ->setCourse($course);
            $manager->persist($lesson);
        }
    }

    private function getLessonsDataForCourse(string $courseCode): array
    {
        $lessons = [];

        if ($courseCode === 'code1') {
            $lessons = [
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
            $lessons = [
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

        return $lessons;
    }
}