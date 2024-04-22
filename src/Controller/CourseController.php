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
use App\Security\BillingAuthenticator;
use App\Service\BillingClient;
use App\Security\User;

#[Route('/courses')]
class CourseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private BillingClient $billingClient;

    public function __construct(BillingClient $billingClient, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->billingClient = $billingClient;
    }

    #[Route('/{code}/pay', name: 'pay_course', methods: ['POST'])]
    public function payForCourse(BillingClient $billingClient, Request $request, string $code): JsonResponse
    {
        $user = $this->getUser();

        // Получение API токена, если пользователь авторизован
        $userToken = $user ? $user->getApiToken() : null;
        
        if (!$userToken) {
            return $this->json(['error' => 'Вы не авторезированы'], Response::HTTP_UNAUTHORIZED);
        }

        $paymentResult = $billingClient->payForCourse($userToken, $code);

        if (isset($paymentResult['error'])) {
            return $this->json(['error' => $paymentResult['error']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($paymentResult);
    }

    #[Route('/', name: 'app_course_index', methods: ['GET'])]
    public function index(Request $request, CourseRepository $courseRepository): Response
    {
        // Получение текущего пользователя
        $user = $this->getUser();

        // Получение API токена, если пользователь авторизован
        $userToken = $user ? $user->getApiToken() : null;
        $userInfo = [];
        $courses = [];

        if ($userToken) {
            $userInfo = $this->billingClient->getUserInfo($userToken);
        }
        try {
            $courses = $this->billingClient->getCourses();
        } catch (BillingUnavailableException $e) {
            $this->addFlash('error', 'Не удалось получить информацию о списке курсов.');
        }

        return $this->render('course/index.html.twig', [
            'courses' => $courseRepository->findAll(),
            'userInfo' => $userInfo,
            'courseInfo' => $courses,
            'userToken' => $userToken
        ]);
    }

    #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedHttpException();
        }
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($course);
            $this->entityManager->flush();

            $this->addFlash('success', 'Курс успешно создан.');

            return $this->redirectToRoute('app_course_index');
        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(Course $course): Response
    {
        // Получение текущего пользователя
        $user = $this->getUser();

        // Получение API токена, если пользователь авторизован
        $userToken = $user ? $user->getApiToken() : null;
        $userInfo = [];
        $courseInfo = [];

        if ($userToken) {
            $userInfo = $this->billingClient->getUserInfo($userToken);
        }
        try {
            $code = $course->getCode();
            $courseInfo = $this->billingClient->getCourse($code);
        } catch (BillingUnavailableException $e) {
            $this->addFlash('error', 'Не удалось получить информацию о списке курсов.');
        }

        return $this->render('course/show.html.twig', [
            'course' => $course,
            'userInfo' => $userInfo,
            'courseInfo' => $courseInfo,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Course $course): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Доступ запрещен.');
        }
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Изменения курса сохранены.');

            return $this->redirectToRoute(
                'app_course_show', ['id' => $course->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_delete', methods: ['POST'])]
    public function delete(Request $request, Course $course): Response
    {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($course);
            $this->entityManager->flush();

            $this->addFlash('success', 'Курс успешно удален.');
        } else {
            $this->addFlash('error', 'Неверный CSRF токен.');
        }

        return $this->redirectToRoute('app_course_index');
    }

    #[Route('{id}/new/lesson', name: 'app_lesson_new', methods: ['GET', 'POST'])]
    public function newLesson(Request $request, Course $course, LessonRepository $lessonRepository): Response
    {
        $lesson = new Lesson();
        $lesson->setCourse($course);
        $form = $this->createForm(LessonType::class, $lesson, [
            'course' => $course,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->save($lesson, true);

            return $this->redirectToRoute(
                'app_course_show', ['id' => $course->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'course' => $course,
        ]);
    }
}
