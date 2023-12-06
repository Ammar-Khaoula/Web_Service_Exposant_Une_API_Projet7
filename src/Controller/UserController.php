<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods:['GET'])]
    public function getAllUser(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $userList = $userRepository->findAll();

        $jsonUserList = $serializer->serialize($userList, 'json', ['groups' => "getUsers"]);


        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }
    #[Route('/api/user/{id}', name: 'user', methods:['GET'])]
    public function getUserById(UserRepository $userRepository, SerializerInterface $serializer, int $id): JsonResponse
    {
        $user = $userRepository->find($id);
        if($user){
            $jsonUser = $serializer->serialize($user, 'json', [ 'groups' => "getUsers"]);
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/user/{id}', name: 'deleteUser', methods:['DELETE'])]
    public function deleteUser(UserRepository $userRepository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $user = $userRepository->find($id);
        $em->remove($user);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
