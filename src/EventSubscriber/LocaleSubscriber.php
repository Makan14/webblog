<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Applique la langue choisie à chaque requête.
 */
final class LocaleSubscriber implements EventSubscriberInterface
{
    private const LOCALES = ['fr', 'en', 'es', 'pt'];

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest()) {
            return;
        }
        $locale = $request->getSession()->get('_locale', $request->cookies->get('_webblog_locale', 'fr'));
        if (!in_array($locale, self::LOCALES, true)) {
            $locale = 'fr';
        }
        $request->setLocale($locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => [['onKernelRequest', 20]]];
    }
}
