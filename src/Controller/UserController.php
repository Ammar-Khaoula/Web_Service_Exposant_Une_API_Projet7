<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Pagination;
use OpenApi\Annotations as OA;
use App\Repository\UserRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends AbstractController
{
    /**
     * @OA\Post(
     *   tags={"Users"},
     *   summary="Create a new user",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         type="object",
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created user",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     *   ),
     *   @OA\Response(response=400, description="JSON field validation failed"),
     *   @OA\Response(
     *     response=401,
     *     description="JWT unauthorized error"
     *   ),
     *   @OA\Response(response=500, description="JSON syntax error or no JSON sent in the request body"),
     * )
     */
    #[Route('/api/users', name: 'addUser', methods: ['POST'])]
    public function createUser(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGeneratoer,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class, 'json');
        $user->setCustomer($this->getUser());
        $user->setCreatedAt(new \DatetimeImmutable());

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
    /**
     * @OA\Get(
     *   tags={"Users"},
     *   summary="Get all users owned by the current customer",
     *   @OA\Response(response=200, description="All users owned by the current customer"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No user found"),
     *   @OA\Parameter(
     *     name="page",
     *     description="Current page number",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="limit",
     *     description="Limit items per page",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   )
     * )
     */
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getAllUser(Pagination $paginator, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $result = $paginator->paginate(
            'SELECT user
            FROM App\Entity\User user
            WHERE user.customer = :id
            ORDER BY user.id DESC',
            ['id' => $this->getUser()->getId()]
        );

        $context = SerializationContext::create()->setGroups(["getUsers"]);
        $jsonUserList = $serializer->serialize($result, 'json', $context);
        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }
    /**
     * @OA\Get(
     *   tags={"Users"},
     *   summary="Get a user by ID",
     *   @OA\Response(response=200, description="User details"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No user found with this ID"),
     *   @OA\PathParameter(
     *     name="id",
     *     description="ID of the user you want to recover"
     *   )
     * )
     */
    #[Route('/api/user/{id}', name: 'user', methods: ['GET'])]
    public function getUserById(?User $user, SerializerInterface $serializer): JsonResponse
    {
        
        $this->userNotExist($user);
        $this->isNotOwner('USER_SHOW', $user, 'You are not authorized to see this content');
        if ($user) {
            $context = SerializationContext::create()->setGroups(["getUsers"]);
            $jsonUser = $serializer->serialize($user, 'json', $context);
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    /**
     * @OA\Delete(
     *   tags={"Users"},
     *   summary="Delete a user by ID",
     *   @OA\Response(response=204, description="User successfully deleted"),
     *   @OA\Response(response=401, description="JWT unauthorized error"),
     *   @OA\Response(response=404, description="No user found with this ID"),
     *   @OA\PathParameter(
     *     name="id",
     *     description="ID of the user you want to delete"
     *   )
     * )
     */
    #[Route('/api/user/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(?User $user, EntityManagerInterface $em, TagAwareCacheInterface $cache, int $id): JsonResponse
    {
        $cache->invalidateTags(["userCache"]);
        $this->userNotExist($user);
        $this->isNotOwner('USER_DELETE', $user, 'You are not authorized to delete this content');

        $em->remove($user);
        $em->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function userNotExist(?User $user)
    {
        if (!$user) {
            throw new NotFoundHttpException("No user found with this ID");
        }
    }

    public function isNotOwner(string $attribute, User $user, string $message)
    {
        // If current customer is not the owner return an exception
        if (!$this->isGranted($attribute, $user)) {
            throw new HttpException(JsonResponse::HTTP_UNAUTHORIZED, $message);
        }
    }
}
