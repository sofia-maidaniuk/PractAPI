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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class TestController extends AbstractController
{
    private string $dataFile = __DIR__ . '/../../var/users.json'; // Файл для збереження користувачів

    private function getUsers(): array
    {
        if (!file_exists($this->dataFile)) {
            return [];
        }
        return json_decode(file_get_contents($this->dataFile), true) ?? [];
    }

    private function saveUsers(array $users): void
    {
        file_put_contents($this->dataFile, json_encode($users, JSON_PRETTY_PRINT));
    }

    private function findUserById(string $id): ?array
    {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }

    #[Route('/users', name: 'app_collection_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getCollection(): JsonResponse
    {
        return $this->json($this->getUsers(), Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'app_item_users', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getItem(string $id): JsonResponse
    {
        $currentUser = $this->getUser();
        if ($currentUser->getId() !== $id && !in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            throw new NotFoundHttpException("You do not have permission to view this user.");
        }

        $user = $this->findUserById($id);
        if (!$user) {
            throw new NotFoundHttpException("User with id $id not found.");
        }

        return $this->json($user, Response::HTTP_OK);
    }

    #[Route('/users', name: 'app_create_users', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createItem(Request $request): JsonResponse
    {
        $users = $this->getUsers();
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData['email'], $requestData['name'])) {
            throw new UnprocessableEntityHttpException("Missing required parameter 'email' or 'name'");
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $requestData['name'])) {
            throw new UnprocessableEntityHttpException("Username is not valid");
        }

        $newId = count($users) + 1;
        $newUser = [
            'id' => (string) $newId,
            'email' => $requestData['email'],
            'name' => $requestData['name']
        ];

        $users[] = $newUser;
        $this->saveUsers($users);

        return new JsonResponse([
            'message' => 'User created successfully',
            'data' => $newUser
        ], Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'app_delete_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
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

    #[Route('/users/{id}', name: 'app_update_user', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function updateItem(string $id, Request $request): JsonResponse
    {
        $users = $this->getUsers();
        $userIndex = $this->findUserIndexById($id);

        if ($userIndex === null) {
            throw new NotFoundHttpException("User with id $id not found.");
        }

        $requestData = json_decode($request->getContent(), true);
        if (!isset($requestData['name']) || !preg_match('/^[a-zA-Z0-9_]{3,20}$/', $requestData['name'])) {
            throw new UnprocessableEntityHttpException("Username is not valid");
        }

        $users[$userIndex]['name'] = $requestData['name'];
        $this->saveUsers($users);

        return $this->json([
            'message' => "User with id $id has been updated.",
            'data' => $users[$userIndex]
        ], Response::HTTP_OK);
    }
}
