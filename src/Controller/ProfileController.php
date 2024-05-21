<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Service\BillingClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Security\User;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends AbstractController
{
    private HttpClientInterface $httpService;
    private BillingClient $billingService;

    public function __construct(HttpClientInterface $httpService, BillingClient $billingService)
    {
        $this->httpService = $httpService;
        $this->billingService = $billingService;
    }

    #[Route('/profile', name: 'app_profile')]
    public function displayProfile(): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            throw new \LogicException('Пользователь не найден.');
        }

        $userToken = $currentUser->getApiToken();
        $profileData = $this->billingService->current($userToken);
        $userBalance = $profileData['balance'] ?? 0;

        try {
            $userTransactions = $this->billingService->getTransactions($userToken);
        } catch (\Exception $e) {
            $userTransactions = [];
            $this->addFlash('error', 'Не удалось загрузить историю транзакций: ' . $e->getMessage());
        }

        return $this->render('profile/profile.html.twig', [
            'email' => $currentUser->getEmail(),
            'roles' => $currentUser->getRoles(),
            'balance' => $userBalance,
            'transactions' => $userTransactions,
        ]);
    }

    #[Route('/profile/deposit', name: 'app_profile_deposit', methods: ['POST'])]
    public function addFunds(Request $httpRequest): Response
    {
        $currentUser = $this->getUser();
        
        if (!$currentUser instanceof User) {
            throw new \LogicException('Пользователь не найден.');
        }

        $depositAmount = $httpRequest->request->get('amount');
        $userToken = $currentUser->getApiToken();

        try {
            $this->billingService->deposit($userToken, (float)$depositAmount);

            return $this->redirectToRoute('app_profile');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Ошибка пополнения: ' . $e->getMessage());

            return $this->redirectToRoute('app_profile');
        }
    }
}
