<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceListener {

    public function __construct(
        private $maintenance,
        private Environment $twig,
        private RequestStack $request,
    )
    {

    }

    public function onKernelRequest(RequestEvent $event) {

        $ip = $this->request->getMainRequest()->getClientIp();

        if(!file_exists($this->maintenance)) {
            return;
        }


        if ($ip === '82.65.195.66') {

            return;
        }
        else {
            $event->setResponse(
                new Response(
                    $this->twig->render('bundles/TwigBundle/Exception/error503.html.twig'),
                    Response::HTTP_SERVICE_UNAVAILABLE
                )
            );
            $event->stopPropagation();
        }
    }
}