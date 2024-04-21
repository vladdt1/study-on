<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Exception\BillingUnavailableException;

class BillingClient
{
    private HttpClientInterface $client;
    private string $billingUrl;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $client, string $billingUrl, HttpClientInterface $httpClient)
    {
        $this->client = $client;
        $this->billingUrl = $billingUrl;
        $this->httpClient = $httpClient;
    }

    public function authorize(string $username, string $password): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->billingUrl . '/api/v1/auth', [
                'json' => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]);
            return $response->toArray();
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Сервис временно недоступен. Повторите попытку авторизации.');
        }
    }

    public function getUserInfo(string $token): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->billingUrl . '/api/v1/users/current', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);
            return $response->toArray();
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Ошибка получения информации о пользователе. ' . $e->getMessage());
        }
    }

}