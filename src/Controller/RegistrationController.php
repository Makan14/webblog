<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\RegistrationType;
use App\Service\RegistrationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Première inscription : crée l'unique compte administrateur puis se ferme.
 */
final class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, RegistrationManager $manager): Response
    {
        if ($manager->compteExiste()) {
            $this->addFlash('warning', 'registration.already_closed');
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $manager->creerAdministrateur(
                    $form->get('nom')->getData(),
                    $form->get('email')->getData(),
                    $form->get('plainPassword')->getData(),
                );
                $this->addFlash('success', 'registration.success');
                return $this->redirectToRoute('app_login');
            } catch (\RuntimeException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }
        return $this->render('security/register.html.twig', ['form' => $form]);
    }
}
