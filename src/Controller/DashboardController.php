<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ClientRepository;
use App\Repository\FactureRepository;

class DashboardController extends AbstractController
{
    public function index(ClientRepository $clientRepo, FactureRepository $factureRepo)
    {
        $clients = $clientRepo->findAll();
        $factures = $factureRepo->findBy([], ['dateEmission'=>'DESC'], 10);

        return $this->render('dashboard/index.html.twig', [
            'clients' => $clients,
            'factures' => $factures
        ]);
    }
}
