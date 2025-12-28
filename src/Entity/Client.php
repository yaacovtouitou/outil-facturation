<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: "App\Repository\ClientRepository")]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    private string $nom;

    #[ORM\Column(type:"string", length:255, nullable:false)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    private string $prenom;

    #[ORM\Column(type:"string", length:255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    private string $adresse;

    #[ORM\Column(type:"string", length:50)]
    #[Assert\NotBlank(message: "Le téléphone est obligatoire")]
    private string $telephone;

    #[ORM\Column(type:"string", length:255)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email n'est pas valide")]
    private string $email;

    #[ORM\OneToMany(mappedBy: "client", targetEntity: "Facture", cascade:["persist", "remove"])]
    private Collection $factures;

    public function __construct()
    {
        $this->factures = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom ?? null; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }
    public function getPrenom(): ?string { return $this->prenom ?? null; }
    public function setPrenom(string $prenom): self { $this->prenom = $prenom; return $this; }
    public function getAdresse(): ?string { return $this->adresse ?? null; }
    public function setAdresse(string $adresse): self { $this->adresse = $adresse; return $this; }
    public function getTelephone(): ?string { return $this->telephone ?? null; }
    public function setTelephone(string $telephone): self { $this->telephone = $telephone; return $this; }
    public function getEmail(): ?string { return $this->email ?? null; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    /** @return Collection|Facture[] */
    public function getFactures(): Collection { return $this->factures; }
}
