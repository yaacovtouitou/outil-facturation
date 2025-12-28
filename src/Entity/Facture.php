<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass:"App\Repository\FactureRepository")]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity:"Client", inversedBy:"factures")]
    #[ORM\JoinColumn(nullable:false)]
    private Client $client;

    #[ORM\Column(type:"datetime")]
    private \DateTime $dateEmission;

    #[ORM\Column(type:"string", length:50)]
    private string $statut;

    #[ORM\OneToMany(mappedBy:"facture", targetEntity:"Prestation", cascade:["persist", "remove"])]
    private Collection $prestations;

    public function __construct()
    {
        $this->prestations = new ArrayCollection();
        $this->statut = 'en_attente';
        $this->dateEmission = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getClient(): Client { return $this->client; }
    public function setClient(Client $client): self { $this->client = $client; return $this; }
    public function getDateEmission(): \DateTime { return $this->dateEmission; }
    public function setDateEmission(\DateTime $date): self { $this->dateEmission = $date; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): self { $this->statut = $statut; return $this; }

    /** @return Collection|Prestation[] */
    public function getPrestations(): Collection { return $this->prestations; }

    public function getTotal(): float
    {
        return array_reduce($this->prestations->toArray(), fn($carry, $p) => $carry + $p->getTotal(), 0);
    }
}
