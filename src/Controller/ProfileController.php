<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Service\BillingClient;  
use App\Security\BillingAuthenticator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Security\User;

class ProfileController extends AbstractController
{
    private $httpClient;
    private $billingClient;

    public function __construct(HttpClientInterface $httpClient, BillingClient $billingClient)
    {
        $this->httpClient = $httpClient;
        $this->billingClient = $billingClient;
        
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
    
        if (!$user instanceof User) {
            throw new \LogicException('Пользователь не найден.');
        }

        $apiToken = $user->getApiToken(); 

        $data = $this->billingClient->current($apiToken);
        $balance = $data['balance'] ?? 0;

        return $this->render('profile/profile.html.twig', [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'balance' => $balance,
        ]);
    }
}
