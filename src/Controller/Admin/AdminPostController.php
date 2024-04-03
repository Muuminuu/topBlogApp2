<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Service\FileUploader;
use App\Form\Admin\AdminPostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin/post')]
class AdminPostController extends AbstractController
{
    #[Route('/', name: 'app_admin_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('admin/post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $post = new Post();
        $form = $this->createForm(AdminPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            if ($image) {
                try {
                    $imageToAdd = $fileUploader->upload($image, 'public_image_directory', false, $post->getImage());
                    $entityManager->persist($imageToAdd);
                    $post->setImage($imageToAdd);
                    $this->addFlash('success', 'Image updated successfully!');
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success', 'Post updated successfully!');

            return $this->redirectToRoute('app_admin_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /*
    * SLUGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGG
    */
    #[Route('/{slug}', name: 'app_admin_post_by_slug', methods: ['GET'])]
    public function showBySlug(PostRepository $postRepository, string $slug): Response
    {
        return $this->render('admin/post/show.html.twig', [
            'post' => $postRepository->findOneBy(['slug' => $slug]),
        ]);
    }

    #[Route('/show/{id}', name: 'app_admin_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('admin/post/show.html.twig', [
            'post' => $post,
        ]);

        // if ($post != null && $post->getAuthor() === $this->getUser()) {
        //     $form = $this->createForm(AdminPostType::class, $post);
        //     $form->handleRequest($request);

        //     if ($form->isSubmitted() && $form->isValid()) {
        //         $entityManager->flush();

        //         return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
        //     }

        //     return $this->render('admin/post/edit.html.twig', [
        //         'post' => $post,
        //         'form' => $form,
        //     ]);
        // }

        // return $this->redirectToRoute('app_home', [], Response::HTTP_UNAUTHORIZED)
    }

    #[Route('/{id}/edit', name: 'app_admin_post_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Post $post, 
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {

        $form = $this->createForm(AdminPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            if ($image) {
                try {
                    $imageToAdd = $fileUploader->upload($image, 'public_image_directory', false, $post->getImage());
                    $entityManager->persist($imageToAdd);
                    $post->setImage($imageToAdd);
                    $this->addFlash('success', 'Image updated successfully!');
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Post updated successfully!');
            return $this->redirectToRoute('app_admin_post_show', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
