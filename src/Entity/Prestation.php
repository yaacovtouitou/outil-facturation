<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:"App\Repository\PrestationRepository")]
class Prestation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255)]
    private string $description;

    #[ORM\Column(type:"float")]
    private float $prixUnitaire;

    #[ORM\Column(type:"integer")]
    private int $quantite;

    #[ORM\ManyToOne(targetEntity:"Facture", inversedBy:"prestations")]
    #[ORM\JoinColumn(nullable:false)]
    private Facture $facture;

    public function getId(): ?int { return $this->id; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $desc): self { $this->description = $desc; return $this; }
    public function getPrixUnitaire(): float { return $this->prixUnitaire; }
    public function setPrixUnitaire(float $prix): self { $this->prixUnitaire = $prix; return $this; }
    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $q): self { $this->quantite = $q; return $this; }
    public function getFacture(): Facture { return $this->facture; }
    public function setFacture(Facture $f): self { $this->facture = $f; return $this; }

    public function getTotal(): float { return $this->prixUnitaire * $this->quantite; }
}
