<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        MailService $mailService,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validation minimale
        if (empty($data['email']) || empty($data['password']) || empty($data['firstName']) || empty($data['lastName']) || empty($data['gender']) || empty($data['promoYear'])) {
            return new JsonResponse(['error' => 'Tous les champs sont obligatoires.'], Response::HTTP_BAD_REQUEST);
        }

        // Empêche les doublons
        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return new JsonResponse(['error' => 'Cet email est déjà utilisé.'], Response::HTTP_CONFLICT);
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setGender($data['gender']);
        $user->setPromoYear((int)$data['promoYear']);
        $user->setRoles(['ROLE_USER']);
        $user->setIsEmailVerified(false);
        $user->setIsValidatedByAdmin(false);

        // Mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Générer un token de vérification email
        $token = bin2hex(random_bytes(32));
        $user->setEmailVerificationToken($token);
        $user->setEmailTokenExpiresAt((new \DateTimeImmutable())->modify('+1 hour'));

        $em->persist($user);
        $em->flush();

        // Envoi du mail
        $mailService->sendVerificationEmail($user);

        return new JsonResponse(['message' => 'Compte créé. Vérifie ton email.'], Response::HTTP_CREATED);
    }

    #[Route('/api/verify-email', name: 'api_verify_email', methods: ['GET'])]
    public function verifyEmail(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): JsonResponse {
        $token = $request->query->get('token');

        if (!$token) {
            return new JsonResponse(['error' => 'Token manquant.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['emailVerificationToken' => $token]);

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        if ($user->getEmailTokenExpiresAt() < new \DateTimeImmutable()) {
            return new JsonResponse(['error' => 'Token expiré.'], Response::HTTP_BAD_REQUEST);
        }

        $user->setIsEmailVerified(true);
        $user->setEmailVerificationToken(null);
        $user->setEmailTokenExpiresAt(null);

        $em->flush();

        return new JsonResponse(['message' => 'Email vérifié. En attente de validation par l’admin.']);
    }
}