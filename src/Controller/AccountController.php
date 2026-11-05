<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\AccountDeleteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Consultation et suppression du compte connecté.
 */
#[Route('/compte')]
#[IsGranted('ROLE_USER')]
final class AccountController extends AbstractController
{
    #[Route('', name: 'app_account', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'delete_form' => $this->createForm(AccountDeleteType::class)->createView(),
        ]);
    }

    #[Route('/suppression', name: 'app_account_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        TokenStorageInterface $tokens,
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(AccountDeleteType::class);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('danger', 'account.delete_invalid');
            return $this->redirectToRoute('app_account');
        }
        if (!$hasher->isPasswordValid($user, $form->get('password')->getData())) {
            $this->addFlash('danger', 'account.bad_password');
            return $this->redirectToRoute('app_account');
        }
        $em->remove($user);
        $em->flush();
        $tokens->setToken(null);
        $request->getSession()->invalidate();
        $this->addFlash('success', 'account.deleted');
        $response = $this->redirectToRoute('app_home');
        $response->headers->clearCookie('REMEMBERME');
        return $response;
    }
}
