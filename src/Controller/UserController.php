<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods:['GET'])]
    public function getAllUser(UserRepository $userRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllUser-" . $page . "-" . $limit;
        $userList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit) {
            echo("l'element n'est pas encore en cache !\n");
            $item->tag("userCache");
            return $userRepository->findAllWithPagination($page, $limit);
        });
        
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUserList = $serializer->serialize($userList, 'json', $context);
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }
    #[Route('/api/user/{id}', name: 'user', methods:['GET'])]
    public function getUserById(UserRepository $userRepository, SerializerInterface $serializer, int $id): JsonResponse
    {
        $user = $userRepository->find($id);
        if($user){
            $context = SerializationContext::create()->setGroups(["getUsers"]);
            $jsonUser = $serializer->serialize($user, 'json', $context);
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/user/{id}', name: 'deleteUser', methods:['DELETE'])]
    public function deleteUser(UserRepository $userRepository, EntityManagerInterface $em, TagAwareCacheInterface $cache, int $id): JsonResponse
    {
        $user = $userRepository->find($id);
        $cache->invalidateTags(["userCache"]);
        $em->remove($user);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users', name: 'addUser', methods:['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, SerializerInterface $serializer,
    UrlGeneratorInterface $urlGeneratoer, CustomerRepository $customerRepo, ValidatorInterface $validator): JsonResponse
    {
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class, 'json');
        $content = $request->toArray();
        $idCustomer = $content['idCustomer'];
        $user->setCustomer($customerRepo->find($idCustomer));

          // We check for errors
        $errors = $validator->validate($user);
            if ($errors->count() > 0) {
                return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            }

        $em->persist($user); 
        $em->flush();
        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        $location = $urlGeneratoer->generate('user', ['id' => $user->getId()],  UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["location" => $location], true);
    }
}
