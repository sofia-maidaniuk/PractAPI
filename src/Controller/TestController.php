<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[Route('/api')]
class TestController extends AbstractController
{
    private string $dataFile = __DIR__ . '/../../var/users.json'; // Файл для збереження користувачів

    // Метод для отримання всіх користувачів (зчитування з файлу)
    private function getUsers(): array
    {
        if (!file_exists($this->dataFile)) {
            return [];
        }
        return json_decode(file_get_contents($this->dataFile), true) ?? [];
    }

    // Метод для збереження користувачів у файл
    private function saveUsers(array $users): void
    {
        file_put_contents($this->dataFile, json_encode($users, JSON_PRETTY_PRINT));
    }

    // Метод для пошуку користувача за ID
    private function findUserById(string $id): ?array
    {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null; // Якщо користувача не знайдено
    }

    // Метод для отримання індексу користувача за ID
    private function findUserIndexById(string $id): ?int
    {
        foreach ($this->getUsers() as $index => $user) {
            if ($user['id'] === $id) {
                return $index;
            }
        }
        return null;
    }

    // GET: Отримати всіх користувачів
    #[Route('/users', name: 'app_collection_users', methods: ['GET'])]
    public function getCollection(): JsonResponse
    {
        return $this->json($this->getUsers(), Response::HTTP_OK);
    }

    // GET: Отримати одного користувача за ID
    #[Route('/users/{id}', name: 'app_item_users', methods: ['GET'])]
    public function getItem(string $id): JsonResponse
    {
        $user = $this->findUserById($id);
        if (!$user) {
            throw new NotFoundHttpException("User with id $id not found.");
        }

        return $this->json($user, Response::HTTP_OK);
    }

    // POST: Створити нового користувача
    #[Route('/users', name: 'app_create_users', methods: ['POST'])]
    public function createItem(Request $request): JsonResponse
    {
        $users = $this->getUsers();
        $requestData = json_decode($request->getContent(), true);

        // Перевірка на обов'язкові параметри
        if (!isset($requestData['email'], $requestData['name'])) {
            throw new UnprocessableEntityHttpException("Missing required parameter 'email' or 'name'");
        }

        $email = trim($requestData['email']);
        $name = trim($requestData['name']);

        // Перевірка коректності email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BadRequestHttpException("Invalid email format.");
        }

        // Перевірка чи email або ім'я вже існують
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                throw new ConflictHttpException("User with email '$email' already exists.");
            }
            if ($user['name'] === $name) {
                throw new ConflictHttpException("User with name '$name' already exists.");
            }
        }

        // Генерація нового ID
        $newId = count($users) + 1;

        $newUser = [
            'id'    => (string) $newId,
            'email' => $email,
            'name'  => $name
        ];

        $users[] = $newUser;
        $this->saveUsers($users);

        return new JsonResponse([
            'message' => 'User created successfully',
            'data'    => $newUser
        ], Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'app_delete_user', methods: ['DELETE'])]
    public function deleteItem(string $id): JsonResponse
    {
        $users = $this->getUsers();
        $userIndex = $this->findUserIndexById($id);

        if ($userIndex === null) {
            throw new NotFoundHttpException("User with id $id not found.");
        }

        unset($users[$userIndex]);
        $this->saveUsers(array_values($users));

        return new JsonResponse(['message' => "User with id $id has been deleted."], Response::HTTP_NO_CONTENT);
    }


    // PATCH: Оновлення користувача за ID
    #[Route('/users/{id}', name: 'app_update_user', methods: ['PATCH'])]
    public function updateItem(string $id, Request $request): JsonResponse
    {
        $users = $this->getUsers();
        $userIndex = $this->findUserIndexById($id);

        if ($userIndex === null) {
            throw new NotFoundHttpException("User with id $id not found.");
        }

        $requestData = json_decode($request->getContent(), true);

        if (!is_array($requestData)) {
            throw new BadRequestHttpException("Invalid JSON format.");
        }

        $updatedUser = $users[$userIndex];

        //Оновлення email (якщо передано)
        if (!empty($requestData['email'])) {
            $newEmail = trim($requestData['email']);

            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                throw new BadRequestHttpException("Invalid email format.");
            }

            foreach ($users as $user) {
                if ($user['email'] === $newEmail && $user['id'] !== $id) {
                    throw new ConflictHttpException("User with email '$newEmail' already exists.");
                }
            }

            $updatedUser['email'] = $newEmail;
        }

        // Оновлення імені (якщо передано)
        if (!empty($requestData['name'])) {
            $newName = trim($requestData['name']);

            foreach ($users as $user) {
                if ($user['name'] === $newName && $user['id'] !== $id) {
                    throw new ConflictHttpException("User with name '$newName' already exists.");
                }
            }

            $updatedUser['name'] = $newName;
        }

        $users[$userIndex] = $updatedUser;
        $this->saveUsers($users);

        return $this->json([
            'message' => "User with id $id has been updated.",
            'data'    => $updatedUser
        ], Response::HTTP_OK);
    }

}
