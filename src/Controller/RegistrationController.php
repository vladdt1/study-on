<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Service\BillingClient;
use App\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\BillingAuthenticator;

class RegistrationController extends AbstractController
{
    private $billingClient;
    private $entityManager;

    public function __construct(BillingClient $billingClient, EntityManagerInterface $entityManager)
    {
        $this->billingClient = $billingClient;
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserAuthenticatorInterface $authenticator, BillingAuthenticator $billingAuthenticator): Response
    {
        // Проверяем, авторизован ли уже пользователь
        if ($this->getUser()) {
            // Если пользователь авторизован, перенаправляем его на страницу профиля
            return $this->redirectToRoute('app_profile');
        }

        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            try {
                $registrationResponse = $this->billingClient->register($formData['email'], $formData['plainPassword']);
                
                if (isset($registrationResponse['token'])) {
                    $user = new User();
                    $user->setEmail($formData['email']);
                    $user->setRoles($registrationResponse['roles']);
                    $user->setApiToken($registrationResponse['token']);
                    $user->setRefreshToken($registrationResponse['refresh_token']);
                    // Аутентификация пользователя
                    return $authenticator->authenticateUser(
                        $user,
                        $billingAuthenticator,
                        $request
                    );
                } else {
                    $this->addFlash('error', 'Регистрация не удалась: ' . ($registrationResponse['message'] ?? 'Неизвестная ошибка'));
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Сервис временно недоступен. Попробуйте позднее.');
            }
        }

        return $this->render('register/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
