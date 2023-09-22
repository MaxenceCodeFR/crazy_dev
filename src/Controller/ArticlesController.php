<?php

namespace App\Controller;


use App\Entity\Articles;
use App\Entity\User;
use App\Form\ArticlesType;
use Symfony\Component\Mime\Email;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ArticlesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/articles')]
class ArticlesController extends AbstractController
{


    #[Route('/', name: 'app_articles_index', methods: ['GET'])]
    public function index(ArticlesRepository $articlesRepository): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articlesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {

        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $email = (new Email())
                //On fixe l'expéditeur définitivement
                ->from($this->getParameter('mailer_from'))
                //On fixe le destinataire (pourquoi pas trouver une astuce pour que ce soit dynamique)
                ->to('your_email@example.com')
                //On fixe le sujet du mail
                ->subject('Ajout d\'une nouvelle Craaaaaazy Box')
                //On crée le contenu du mail
                ->html($this->renderView('Mail/newEmail.html.twig', [
                    'article' => $article,
                ]));
            //On envoie le mail
            $mailer->send($email);


            /////////////////////////////////////////////////////

            /* Recupéré l'image */
            $file = $form->get("images")->getData();
            /* Créer un nom unique pour l'image et recuperer l'extension*/
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $article->setImages($fileName);
            /* Deplacer l'image dans le dossier public/images */
            $file->move($this->getParameter('upload_directory'), $fileName);

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_articles_show', methods: ['GET'])]
    public function show(Articles $article): Response
    {
        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_articles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Articles $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Recupéré l'image */
            $file = $form->get("images")->getData();
            /* Créer un nom unique pour l'image et recuperer l'extension*/
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $article->setImages($fileName);
            /* Deplacer l'image dans le dossier public/images */
            $file->move($this->getParameter('upload_directory'), $fileName);

            $entityManager->flush();

            return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_articles_delete', methods: ['POST'])]
    public function delete(Request $request, Articles $article, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $email = (new Email())
            //On fixe l'expéditeur définitivement
            ->from($this->getParameter('mailer_from'))
            //On fixe le destinataire (pourquoi pas trouver une astuce pour que ce soit dynamique)
            ->to('your_email@example.com')
            //On fixe le sujet du mail
            ->subject('Nous sommes triste de vous annoncer la suppression d\'une Craaaaaazy Box')
            //On crée le contenu du mail
            ->html($this->renderView('Mail/deleteEmail.html.twig', [
                'article' => $article,
            ]));
        //On envoie le mail
        $mailer->send($email);
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
    }
}
