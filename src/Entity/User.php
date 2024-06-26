<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[ORM\Id]
    #[ORM\Column(length: 90, unique: true)]
    private ?string $idUser = null;

    #[ORM\Column(length: 55)]
    private ?string $name = null;

    #[ORM\Column(length: 80, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 90)]
    private ?string $encrypte = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $updateAt = null;

    #[ORM\OneToOne(mappedBy: 'User_idUser', cascade: ['persist', 'remove'])]
    private ?Artist $artist = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $dateBirth = null;

    #[ORM\Column(nullable: true)]
    private ?int $sexe = null;

    #[ORM\Column]
    private ?int $disable = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser(): ?string
    {
        return $this->idUser;
    }

    public function setIdUser(string $idUser): static
    {
        $this->idUser = $idUser;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->encrypte;
    }

    public function setPassword(string $encrypte): static
    {
        $this->encrypte = $encrypte;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTimeInterface $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(Artist $artist): static
    {
        // set the owning side of the relation if necessary
        if ($artist->getUserIdUser() !== $this) {
            $artist->setUserIdUser($this);
        }

        $this->artist = $artist;

        return $this;
    }

    public function getRoles(): array
    {

        return ['PUBLIC_ACCESS'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function serializerRegister()
    {
        $dateOfBirth = $this->getDateBirth();
        $dateBirth = $dateOfBirth->format("d-m-Y");
        $telValue = $this->getTel();

        $sexe  = $this->getSexe() !== null ? $this->getSexe() : 'Homme';
        if ($sexe == 0) {
            $sexe = 'Femme';
        } else {
            $sexe = 'Homme';
        }
        return [
            "firstname" => $this->getFirstName(),
            "lastname" => $this->getLastName(),
            "email" => $this->getEmail(),
            "tel" => $telValue = $telValue !== null ? $telValue : "",
            "sexe" => $sexe,
            "dateBirth" => $dateBirth,
            "createAt" => $this->getCreateAt(),
            "updateAt" => $this->getUpdateAt(),
        ];
    }

    public function serializerLogin()
    {
        $dateOfBirth = $this->getDateBirth();
        $dateBirth = $dateOfBirth->format("d-m-Y");
        $telValue = $this->getTel();
        $artist = $this->getArtist() !==  null ? $this->getArtist() : new Artist();


        $sexe  = $this->getSexe() !== null ? $this->getSexe() : 'Homme';
        if ($sexe == 0) {
            $sexe = 'Femme';
        } else {
            $sexe = 'Homme';
        }
        return [
            "firstname" => $this->getFirstName(),
            "lastname" => $this->getLastName(),
            "email" => $this->getEmail(),
            "tel" => $telValue = $telValue !== null ? $telValue : "",
            "sexe" => $sexe,
            "artist" => $artist->serializer(),
            "dateBirth" => $dateBirth,
            "createAt" => $this->getCreateAt(),
        ];
    }



    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getDateBirth(): ?\DateTimeInterface
    {
        return $this->dateBirth;
    }

    public function setDateBirth(\DateTimeInterface $dateBirth): static
    {
        $this->dateBirth = $dateBirth;

        return $this;
    }

    public function getSexe(): ?int
    {
        return $this->sexe;
    }

    public function setSexe(int $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getDisable(): ?int
    {
        return $this->disable;
    }

    public function setDisable(int $disable): static
    {
        $this->disable = $disable;

        return $this;
    }
}
