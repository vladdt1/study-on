<?php 
namespace App\Tests\Mock;

use App\Service\BillingClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Exception\BillingUnavailableException;
use Symfony\Component\HttpFoundation\Response;

class BillingClientMock extends BillingClient
{
    // Переопределяем конструктор для предотвращения необходимости передачи аргументов
    public function __construct()
    {
        // Не вызываем конструктор родителя, так как он ожидает аргументы, не нужные для мок-объекта
    }

    public function getCourse(string $code): array
    {
        if ($code === '99999') {
            throw new \Exception('Не удалось загрузить информацию о курсе. HTTP/1.1 404 Not Found returned for "http://billing.study-on.local/api/v1/courses/99999".', Response::HTTP_NOT_FOUND);
        }

        return parent::getCourse($code);
    }

    // Переопределение методов для возвращения фиктивных данных
    public function authorize(string $username, string $password): array
    {
        if ($username === "test@example.com" && $password === "123456") {
            return [
                'email' => $username,
                'token' => '1-7654589076545678909876545678',
                'roles' => ['ROLE_USER']
            ];
        }elseif ($username === "new@example.com" && $password === "123456") {
            return [
                'email' => $username,
                'token' => '2-7654589076545678909876545678',
                'roles' => ['ROLE_USER']
            ];
        }elseif ($username === "admin@gmail.com" && $password === "password") {
            return [
                'email' => $username,
                'token' => '3-7654589076545678909876545678',
                'roles' => ['ROLE_SUPER_ADMIN']
            ];
        }

        throw new \Exception("Сервис временно недоступен. Попробуйте позднее.");
    }

    public function getUserInfo(string $token): array
    {
        // Возвращаем заранее определенный ответ
        if ($token == '1-7654589076545678909876545678') {
            return [
                'email' => 'new@example.com',
                'token' => '1-7654589076545678909876545678',
                'roles' => ['ROLE_USER']
            ];
        }elseif ($token == '2-7654589076545678909876545678') {
            return [
                'email' => 'test@example.com',
                'token' => '2-7654589076545678909876545678',
                'roles' => ['ROLE_USER']
            ];
        }elseif ($token == '3-7654589076545678909876545678') {
            return [
                'email' => 'admin@gmail.com',
                'token' => '3-7654589076545678909876545678',
                'roles' => ['ROLE_SUPER_ADMIN']
            ];
        }

        throw new \Exception("Неверные учетные данные");
    }

    public function refreshToken(string $refreshToken): array
    {
        // Возвращаем заранее определенный ответ
        if ($token == '1-7654589076545678909876545678') {
            return [
                'email' => 'new@example.com',
                'token' => '1-7654589076545678909876545678',
                'roles' => ['ROLE_SUPER_ADMIN']
            ];
        }elseif ($token == '2-7654589076545678909876545678') {
            return [
                'email' => 'test@example.com',
                'token' => '2-7654589076545678909876545678',
                'roles' => ['ROLE_USER']
            ];
        }elseif ($token == '3-7654589076545678909876545678') {
            return [
                'email' => 'admin@gmail.com',
                'token' => '3-7654589076545678909876545678',
                'roles' => ['ROLE_SUPER_ADMIN']
            ];
        }

        throw new \Exception("Неверные учетные данные");
    }

    public function register(string $email, string $password): array
    {
        if ($username === "end@example.com" && $password === "123456") {
            return [
                'token' => '1-7654589076545678909876545678',
                'email' => $email,
                'roles' => ['ROLE_USER']
            ];
        }
    }
}
