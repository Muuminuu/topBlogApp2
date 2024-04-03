<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\FileUploader;
use App\Form\Admin\AdminUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin/user')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user, ['mode' => 'creation']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatar = $form->get('avatar')->getData();

            if ($avatar) {
                try {
                    $imageToAdd = $fileUploader->upload($avatar, 'private_avatar_directory', true, $user->getAvatar());
                    $entityManager->persist($imageToAdd);
                    $user->setAvatar($imageToAdd);
                    $this->addFlash('success', 'Avatar updated successfully!');
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'User updated successfully!');

            return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(AdminUserType::class, $user, ['mode' => 'edition']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatar = $form->get('avatar')->getData();

            if ($avatar) {
                try {
                    $imageToAdd = $fileUploader->upload($avatar, 'private_avatar_directory', true, $user->getAvatar());
                    $entityManager->persist($imageToAdd);
                    $user->setAvatar($imageToAdd);
                    $this->addFlash('success', 'Avatar updated successfully!');
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'User updated successfully!');

            return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
