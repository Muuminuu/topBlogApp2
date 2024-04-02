<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PasswordController extends AbstractController
{
    




#[Route('/password', name: 'app_forgot_password_request', methods: ['GET','POST'])]
    public function edit(
        Request $request, 
        User $user, 
        EntityManagerInterface $entityManager
        ): Response {


        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('password/index.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}