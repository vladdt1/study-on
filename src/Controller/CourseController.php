<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Form\CourseType;
use App\Form\LessonType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\BillingClient;
use App\Security\User;

#[Route('/courses')]
class CourseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private BillingClient $billingService;
    private LessonRepository $lessonRepo;
    private CourseRepository $courseRepo;

    public function __construct(CourseRepository $courseRepo, BillingClient $billingService, EntityManagerInterface $entityManager, LessonRepository $lessonRepo)
    {
        $this->entityManager = $entityManager;
        $this->billingService = $billingService;
        $this->lessonRepo = $lessonRepo;
        $this->courseRepo = $courseRepo;
    }

    #[Route('/{code}/pay', name: 'app_course_pay', methods: ['POST'])]
    public function purchaseCourse(Request $httpRequest, string $code): Response
    {
        $currentUser = $this->getUser();

        $userToken = $currentUser ? $currentUser->getApiToken() : null;

        if (!$userToken) {
            return $this->redirectToRoute('app_login');
        }
        $token = $httpRequest->request->get('token');
        
        try {
            $result = $this->billingService->payForCourse($token, $code);
            if (isset($result['success']) && $result['success']) {
                $this->addFlash('success', 'Курс был успешно приобретен и доступен для изучения.');
            } else {
                $this->addFlash('error', 'Не удалось приобрести курс: ' . ($result['message'] ?? 'Ошибка сервера'));
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Ошибка при покупке курса: ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_course_show', ['code' => $code]);
    }

    #[Route('/', name: 'app_course_index', methods: ['GET'])]
    public function listCourses(Request $httpRequest, CourseRepository $courseRepo): Response
    {
        $currentUser = $this->getUser();

        $userToken = $currentUser ? $currentUser->getApiToken() : null;
        $courseList = [];

        try {
            $courseList = $this->billingService->getCourses();
        } catch (BillingUnavailableException $e) {
            $this->addFlash('error', 'Не удалось получить список курсов.');
        }

        $transactionHistory = [];
        if ($userToken) {
            $transactions = $this->billingService->getTransactions($userToken);

            foreach ($transactions as $transaction) {
                $transactionHistory[$transaction['course_id']] = $transaction;
            }
        }

        return $this->render('course/index.html.twig', [
            'courses' => $courseList,
            'transactions' => $transactionHistory,
            'userToken' => $userToken,
        ]);
    }

    #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function createCourse(Request $httpRequest, CourseRepository $courseRepo): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('У вас нет доступа к этой операции.');
        }
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($httpRequest);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingCourse = $courseRepo->findOneBy(['code' => $course->getCode()]);
            if ($existingCourse) {
                $this->addFlash('error', 'Курс с таким кодом уже существует.');
                return $this->redirectToRoute('app_course_new');
            }

            try {
                $this->billingService->createCourse($course);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Ошибка при создании курса в биллинге: ' . $e->getMessage());
            }

            $courseRepo->save($course, true);
            return $this->redirectToRoute('app_course_index');
        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{code}', name: 'app_course_show', methods: ['GET'])]
    public function showCourse(string $code, CourseRepository $courseRepo): Response
    {
        $currentUser = $this->getUser();

        $userToken = $currentUser ? $currentUser->getApiToken() : null;

        $transactionHistory = [];
        if ($userToken) {
            $transactions = $this->billingService->getTransactions($userToken);

            foreach ($transactions as $transaction) {
                $transactionHistory[$transaction['course_id']] = $transaction;
            }
        }

        $course = $courseRepo->findOneBy(['code' => $code]);

        if (!$course) {
            $courseData = $this->billingService->getCourse($code);

            $course = new Course();
            $course
                ->setCode($courseData['code'])
                ->setTitle($courseData['title'])
                ->setDescription($courseData['description'])
                ->setType($courseData['type'])
                ->setPrice($courseData['price']);

            $courseRepo->save($course, true);
        }

        try {
            $lessons = $this->lessonRepo->findBy(['course' => $course]);
        } catch (BillingUnavailableException $e) {
            $this->addFlash('error', 'Не удалось получить информацию о курсе.');
        }

        return $this->render('course/show.html.twig', [
            'course' => $course,
            'transactions' => $transactionHistory,
            'lessons' => $lessons,
            'userToken' => $userToken,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    public function editCourse(Request $httpRequest, Course $course, CourseRepository $courseRepo): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Доступ запрещен.');
        }

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($httpRequest);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->billingService->updateCourse($course);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Ошибка при обновлении курса в биллинге: ' . $e->getMessage());
            }
            $courseRepo->save($course, true);

            return $this->redirectToRoute('app_course_show', ['code' => $course->getCode()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_delete', methods: ['POST'])]
    public function deleteCourse(Request $httpRequest, Course $course, CourseRepository $courseRepo): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Доступ запрещен.');
        }

        if ($this->isCsrfTokenValid('delete' . $course->getId(), $httpRequest->request->get('_token'))) {
            try {
                $this->billingService->deleteCourse($course->getCode());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Ошибка при удалении курса в биллинге: ' . $e->getMessage());
            }

            $this->entityManager->remove($course);

            $this->addFlash('success', 'Курс успешно удален.');
        } else {
            $this->addFlash('error', 'Неверный CSRF токен.');
        }

        return $this->redirectToRoute('app_course_index');
    }

    #[Route('/{code}/rent', name: 'app_course_rent', methods: ['POST'])]
    public function rentCourse(Request $httpRequest, string $code): Response
    {
        $currentUser = $this->getUser();

        $userToken = $currentUser ? $currentUser->getApiToken() : null;

        if (!$userToken) {
            return $this->redirectToRoute('app_login');
        }

        $token = $httpRequest->request->get('token');

        try {
            $result = $this->billingService->payForCourse($token, $code);

            if (isset($result['expires_at'])) {
                $this->addFlash('success', 'Курс успешно арендован до ' . $result['expires_at']);
            } else {
                $this->addFlash('error', 'Не удалось арендовать курс: ' . ($result['message'] ?? 'Ошибка сервера'));
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Ошибка аренды курса: ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_course_show', ['code' => $code]);
    }
}
