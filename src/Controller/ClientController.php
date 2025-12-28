<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    #[Route('/clients', name: 'client_list')]
    public function list(ClientRepository $clientRepo): Response
    {
        $clients = $clientRepo->findAll();
        return $this->render('client/list.html.twig', [
            'clients' => $clients
        ]);
    }

    #[Route('/clients/new', name: 'client_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();

        $form = $this->createFormBuilder($client)
            ->add('nom')
            ->add('prenom')
            ->add('adresse')
            ->add('email')
            ->add('telephone')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($client);
            $em->flush();
            return $this->redirectToRoute('client_list');
        }

        return $this->render('client/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/clients/{id}/edit', name: 'client_edit')]
    public function edit(Client $client, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($client)
            ->add('nom')
            ->add('prenom')
            ->add('adresse')
            ->add('email')
            ->add('telephone')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('client_list');
        }

        return $this->render('client/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/clients/{id}/delete', name: 'client_delete')]
    public function delete(Client $client, EntityManagerInterface $em): Response
    {
        $em->remove($client);
        $em->flush();
        return $this->redirectToRoute('client_list');
    }
}
