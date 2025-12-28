<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Entity\Prestation;
use App\Repository\FactureRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Client;

class FactureController extends AbstractController
{
    #[Route('/factures', name:'facture_list')]
    public function list(FactureRepository $repo): Response
    {
        $factures = $repo->findAll();
        return $this->render('facture/list.html.twig', ['factures'=>$factures]);
    }

    #[Route('/factures/new', name:'facture_new')]
    public function new(Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $facture = new Facture();
        $clients = $clientRepo->findAll();

        if ($request->isMethod('POST')) {
            $clientId = $request->request->getInt('client');
            $client = $clientRepo->find($clientId);
            if(!$client) { $this->addFlash('error','Client invalide'); return $this->redirectToRoute('facture_new'); }

            $facture->setClient($client);
            $facture->setDateEmission(new \DateTime());
            $facture->setStatut($request->request->get('statut','en_attente'));

            $prestationsData = $request->request->all('prestations');
            foreach($prestationsData as $p) {
                if(isset($p['description'],$p['prix'],$p['quantite'])) {
                    $prestation = new Prestation();
                    $prestation->setDescription($p['description']);
                    $prestation->setPrixUnitaire((float)$p['prix']);
                    $prestation->setQuantite((int)$p['quantite']);
                    $prestation->setFacture($facture);
                    $em->persist($prestation);
                }
            }

            $em->persist($facture);
            $em->flush();
            $this->addFlash('success','Facture créée');
            return $this->redirectToRoute('facture_list');
        }

        return $this->render('facture/new.html.twig',['clients'=>$clients]);
    }

    #[Route('/factures/{id}/edit', name:'facture_edit')]
    public function edit(Facture $facture, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($facture)
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nom',
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'en_attente',
                    'Payée' => 'payee',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Facture modifiée');
            return $this->redirectToRoute('facture_list');
        }

        return $this->render('facture/edit.html.twig', [
            'form' => $form->createView(),
            'facture' => $facture
        ]);
    }

    #[Route('/factures/{id}/delete', name:'facture_delete')]
    public function delete(Facture $facture, EntityManagerInterface $em): Response
    {
        $em->remove($facture);
        $em->flush();
        $this->addFlash('success', 'Facture supprimée');
        return $this->redirectToRoute('facture_list');
    }

    #[Route('/factures/{id}/pdf', name:'facture_pdf')]
    public function pdf(Facture $facture): Response
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        try {
            // Configuration PDF
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator('Outil Facturation');
            $pdf->SetAuthor('Mon Entreprise');
            $pdf->SetTitle('Facture #'.$facture->getId());

            // Suppression des en-têtes et pieds de page par défaut pour un design custom
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);
            $pdf->AddPage();

            // --- DONNÉES ---
            $client = $facture->getClient();
            $nomClient = $client->getNom() ?? '';
            $prenomClient = $client->getPrenom() ?? '';
            $adresseClient = $client->getAdresse() ?? 'Adresse non renseignée';
            $telClient = $client->getTelephone() ?? '';
            $emailClient = $client->getEmail() ?? '';

            $dateFacture = $facture->getDateEmission()->format('d/m/Y');
            $numeroFacture = str_pad($facture->getId(), 5, '0', STR_PAD_LEFT); // Ex: 00012
            $total = number_format($facture->getTotal(), 2, ',', ' ');

            // --- STYLES CSS ---
            $style = <<<EOF
<style>
    h1 { color: #2c3e50; font-family: helvetica; font-size: 24pt; font-weight: bold; }
    .company-name { color: #2c3e50; font-size: 14pt; font-weight: bold; }
    .text-gray { color: #7f8c8d; font-size: 9pt; }
    .text-dark { color: #2c3e50; font-size: 10pt; }
    .header-line { border-bottom: 2px solid #2c3e50; }

    .invoice-box { cellpadding: 5px; }

    .table-header { background-color: #2c3e50; color: #ffffff; font-weight: bold; text-align: center; }
    .table-row { border-bottom: 1px solid #ecf0f1; color: #2c3e50; }
    .amount { text-align: right; }
    .center { text-align: center; }

    .total-box { font-size: 12pt; font-weight: bold; color: #2c3e50; }
    .footer { font-size: 8pt; color: #95a5a6; text-align: center; border-top: 1px solid #bdc3c7; padding-top: 10px; }
</style>
EOF;

            $html = $style;

            $html .= '
            <table border="0" cellpadding="5">
                <tr>
                    <td width="50%">
                        <div class="company-name">MON ENTREPRISE</div>
                        <div class="text-gray">
                            123 Avenue des Champs-Élysées<br>
                            75008 Paris, France<br>
                            contact@monentreprise.com<br>
                            SIRET: 123 456 789 00012
                        </div>
                    </td>
                    <td width="50%" align="right">
                        <h1>FACTURE</h1>
                        <div class="text-dark"><b>N° F-'.$numeroFacture.'</b></div>
                        <div class="text-gray">Date : '.$dateFacture.'</div>
                    </td>
                </tr>
            </table>
            <div class="header-line"></div>
            <br><br>';

            $html .= '
            <table border="0" cellpadding="5">
                <tr>
                    <td width="55%"></td>
                    <td width="45%" style="background-color: #f8f9fa; border-left: 4px solid #2c3e50;">
                        <span class="text-gray">Facturé à :</span><br>
                        <b style="font-size: 11pt;">'.$nomClient.' '.$prenomClient.'</b><br>
                        <span class="text-dark">'.$adresseClient.'</span><br>
                        <span class="text-dark">'.$emailClient.'</span><br>
                        <span class="text-dark">'.$telClient.'</span>
                    </td>
                </tr>
            </table>
            <br><br>';

            $html .= '
            <table border="0" cellpadding="8" cellspacing="0">
                <thead>
                    <tr class="table-header">
                        <th width="50%" align="left">Description</th>
                        <th width="15%">Prix Unit.</th>
                        <th width="10%">Qté</th>
                        <th width="25%" align="right">Total</th>
                    </tr>
                </thead>
                <tbody>';

            $fill = false;
            foreach($facture->getPrestations() as $p) {
                $bg = $fill ? '#f2f2f2' : '#ffffff'; // Alternance gris clair / blanc
                $html .= '
                    <tr style="background-color: '.$bg.';">
                        <td width="50%" class="text-dark">'.$p->getDescription().'</td>
                        <td width="15%" class="center text-dark">'.number_format($p->getPrixUnitaire(), 2, ',', ' ').' €</td>
                        <td width="10%" class="center text-dark">'.$p->getQuantite().'</td>
                        <td width="25%" class="amount text-dark">'.number_format($p->getTotal(), 2, ',', ' ').' €</td>
                    </tr>';
                $fill = !$fill;
            }

            // Remplissage si vide (optionnel)
            if (count($facture->getPrestations()) == 0) {
                $html .= '<tr><td colspan="4" align="center">Aucune prestation</td></tr>';
            }

            $html .= '</tbody></table>';

            // 4. TOTAUX
            $html .= '
            <br><br>
            <table border="0" cellpadding="5">
                <tr>
                    <td width="60%"></td>
                    <td width="40%">
                        <table border="0" cellpadding="5">
                            <tr>
                                <td align="right" class="text-gray">Total HT :</td>
                                <td align="right" class="text-dark">'.$total.' €</td>
                            </tr>
                            <tr>
                                <td align="right" class="text-gray">TVA (20%) :</td>
                                <td align="right" class="text-dark">'.number_format($facture->getTotal() * 0.20, 2, ',', ' ').' €</td>
                            </tr>
                            <tr>
                                <td colspan="2"><hr></td>
                            </tr>
                            <tr class="total-box">
                                <td align="right" style="background-color: #2c3e50; color: white;">TOTAL TTC :</td>
                                <td align="right" style="background-color: #2c3e50; color: white;">'.number_format($facture->getTotal() * 1.20, 2, ',', ' ').' €</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>';

            // 5. PIED DE PAGE (Mentions légales, banque)
            $html .= '
            <br><br><br><br>
            <div class="footer">
                Conditions de paiement : Paiement à réception de facture, à 30 jours.<br>
                Banque : Crédit Mutuel - IBAN : FR76 1234 5678 9012 3456 7890 123 - BIC : CMCIFR2A<br>
                Mon Entreprise SAS au capital de 10 000€ - RCS Paris B 123 456 789
            </div>';

            // Écriture du HTML dans le PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Sortie
            $pdfContent = $pdf->Output('facture_'.$facture->getId().'.pdf', 'S');

            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="facture_'.$facture->getId().'.pdf"'
            ]);

        } catch (\Exception $e) {
            return new Response("Erreur lors de la génération du PDF : " . $e->getMessage(), 500);
        }
    }
}
