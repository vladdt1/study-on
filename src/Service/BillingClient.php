<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Exception\BillingUnavailableException;
use App\Entity\Course;

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

    // Авторизация пользователя
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
            throw new BillingUnavailableException('Сервис авторизации недоступен. Попробуйте позже.');
        }
    }

    // Получение информации о текущем пользователе
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
            throw new BillingUnavailableException('Не удалось получить информацию о пользователе. ' . $e->getMessage());
        }
    }

    // Алиас для getUserInfo
    public function current(string $token): array
    {
        return $this->getUserInfo($token);
    }

    // Регистрация нового пользователя
    public function register(string $email, string $password): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->billingUrl . '/api/v1/register', [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
            ]);
            return $response->toArray();
            
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Регистрация временно недоступна. Попробуйте позже.');
        }
    }

    // Обновление токена доступа
    public function refreshToken(string $refreshToken): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->billingUrl . '/api/v1/token/refresh', [
                'json' => [
                    'refresh_token' => $refreshToken,
                ],
            ]);
            return $response->toArray();
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Не удалось обновить токен. ' . $e->getMessage());
        }
    }

    // Получение списка доступных курсов
    public function getCourses(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->billingUrl . '/api/v1/courses');
            return $response->toArray();
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Ошибка загрузки списка курсов. ' . $e->getMessage());
        }
    }

    // Получение информации о конкретном курсе
    public function getCourse(string $code): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->billingUrl . "/api/v1/courses/$code");
            return $response->toArray();
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Не удалось загрузить информацию о курсе. ' . $e->getMessage());
        }
    }

    // Оплата за курс
    public function payForCourse(string $token, string $courseCode): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->billingUrl . "/api/v1/courses/$courseCode/pay", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ]);
            return $response->toArray();
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Оплата не прошла. ' . $e->getMessage());
        }
    }

    // Получение истории транзакций пользователя
    public function getTransactions(string $token, array $filters = []): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->billingUrl . '/api/v1/transactions', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
                'query' => $filters,
            ]);
            return $response->toArray();
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Ошибка загрузки истории транзакций. ' . $e->getMessage());
        }
    }

    // Получение курсов пользователя
    public function getUserCourses(string $token): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->billingUrl . '/api/v1/user/courses', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ]);

            $data = $response->toArray();
            $courses = [];

            foreach ($data as $item) {
                $courses[$item['code']] = [
                    'type' => $item['type'],
                    'expires_at' => $item['expires_at'] ?? null,
                ];
            }

            return $courses;
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Не удалось загрузить курсы пользователя.');
        }
    }

    // Пополнение баланса пользователя
    public function deposit(string $token, float $amount): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->billingUrl . '/api/v1/deposit', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
                'json' => [
                    'amount' => $amount,
                ],
            ]);
            return $response->toArray();
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Пополнение баланса не удалось. ' . $e->getMessage());
        }
    }

    // Создание нового курса
    public function createCourse(Course $course): void
    {
        try {
            $response = $this->httpClient->request('POST', $this->billingUrl . '/api/v1/courses/create', [
                'json' => [
                    'type' => $course->getType(),
                    'title' => $course->getTitle(),
                    'code' => $course->getCode(),
                    'price' => $course->getPrice(),
                    'description' => $course->getDescription(),
                ],
            ]);

            if ($response->getStatusCode() !== 201) {
                throw new \Exception('Создание курса не удалось.');
            }
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Ошибка при создании курса: ' . $e->getMessage());
        }
    }

    // Обновление информации о курсе
    public function updateCourse(Course $course): void
    {
        try {
            $response = $this->httpClient->request('POST', $this->billingUrl . '/api/v1/courses/' . $course->getCode() . '/update', [
                'json' => [
                    'type' => $course->getType(),
                    'title' => $course->getTitle(),
                    'code' => $course->getCode(),
                    'price' => $course->getPrice(),
                    'description' => $course->getDescription(),
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Обновление курса не удалось.');
            }
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Ошибка при обновлении курса: ' . $e->getMessage());
        }
    }

    // Удаление курса
    public function deleteCourse(string $courseCode): void
    {
        try {
            $response = $this->httpClient->request('DELETE', $this->billingUrl . '/api/v1/courses/' . $courseCode . '/delete');

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Удаление курса не удалось.');
            }
        } catch (\Exception $e) {
            throw new BillingUnavailableException('Ошибка при удалении курса: ' . $e->getMessage());
        }
    }
}
