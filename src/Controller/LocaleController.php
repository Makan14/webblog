<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Changement de langue : mémorisé en session et dans un cookie.
 */
final class LocaleController extends AbstractController
{
    #[Route('/locale/{locale}', name: 'app_locale_switch', requirements: ['locale' => 'fr|en|es|pt'], methods: ['GET'])]
    public function switch(string $locale, Request $request): Response
    {
        $request->getSession()->set('_locale', $locale);
        $cible = $request->query->get('redirect', $this->generateUrl('app_home'));
        if (!str_starts_with($cible, '/') || str_starts_with($cible, '//')) {
            $cible = $this->generateUrl('app_home');
        }
        $response = $this->redirect($cible);
        $response->headers->setCookie(Cookie::create('_webblog_locale', $locale, time() + 31536000, '/', null, false, true, false, 'lax'));
        return $response;
    }
}
