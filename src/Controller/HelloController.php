<?php

namespace App\Controller;


use App\Message\UserSendEmailMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class HelloController extends AbstractController
{
    public function index(MessageBusInterface $bus): Response
    {
        // will cause the MyMessageHandler to be called

        // ...
        return $this->render('hello/index.html.twig', [
        ]);
    }


    public function helloName(string $name): Response
    {

        return $this->render('hello/index.html.twig', [
            'name' => $name,
        ]);
    }
}
