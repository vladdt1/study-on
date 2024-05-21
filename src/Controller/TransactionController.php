<?php

namespace App\Controller;

use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends AbstractController
{
    #[Route('/api/v1/transactions', name: 'transaction_history', methods: ['GET'])]
    public function fetchTransactionHistory(BillingClient $billingService, Request $httpRequest): JsonResponse
    {
        $authToken = $httpRequest->headers->get('Authorization', '');

        // Проверяем наличие токена авторизации
        if (empty($authToken)) {
            return $this->json(['error' => 'Необходима аутентификация'], Response::HTTP_UNAUTHORIZED);
        }

        // Извлекаем параметры фильтров из запроса
        $queryParams = $httpRequest->query->all();
        $transactionData = $billingService->getTransactions($authToken, $queryParams);

        // Проверяем наличие ошибки в данных транзакций
        if (array_key_exists('error', $transactionData)) {
            return $this->json(['error' => $transactionData['error']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($transactionData);
    }
}
