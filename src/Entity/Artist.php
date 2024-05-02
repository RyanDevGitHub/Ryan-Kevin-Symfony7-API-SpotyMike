<?php

namespace App\Entity;

use App\Repository\ArtistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistRepository::class)]

class Artist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: "App\Entity\User", inversedBy: "artist")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private  $User_idUser;

    #[ORM\Column(length: 90)]
    private ?string $fullname = null;

    #[ORM\Column(length: 90)]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;


    #[ORM\ManyToMany(targetEntity: Song::class, mappedBy: 'Artist_idUser')]
    private Collection $songs;

    #[ORM\OneToMany(targetEntity: Album::class, mappedBy: 'artist_User_idUser')]
    private Collection $albums;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $date_begin = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_end = null;

    #[ORM\Column(nullable: true)]
    private ?int $active = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->songs = new ArrayCollection();
        $this->albums = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdUser(): ?User
    {
        return $this->User_idUser;
    }

    public function setUserIdUser(User $User_idUser): static
    {
        $this->User_idUser = $User_idUser;

        return $this;
    }
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }
    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }
    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSongs(): Collection
    {
        return $this->songs;
    }

    public function addSong(Song $song): static
    {
        if (!$this->songs->contains($song)) {
            $this->songs->add($song);
            $song->addArtistIdUser($this);
        }

        return $this;
    }

    public function removeSong(Song $song): static
    {
        if ($this->songs->removeElement($song)) {
            $song->removeArtistIdUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Album>
     */
    public function getAlbums(): Collection
    {
        return $this->albums;
    }

    public function addAlbum(Album $album): static
    {
        if (!$this->albums->contains($album)) {
            $this->albums->add($album);
            $album->setArtistUserIdUser($this);
        }

        return $this;
    }

    public function removeAlbum(Album $album): static
    {
        if ($this->albums->removeElement($album)) {
            // set the owning side to null (unless already changed)
            if ($album->getArtistUserIdUser() === $this) {
                $album->setArtistUserIdUser(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    public function getUserId(): ?int
    {
        return $this->User_idUser ? $this->User_idUser->getId() : null;
    }

    public function getFirstName(): ?string
    {
        return $this->User_idUser ? $this->User_idUser->getFirstname() : null;
    }

    public function getLastName(): ?string
    {
        return $this->User_idUser ? $this->User_idUser->getLastname() : null;
    }

    public function getUserDateOfBirth(): ?\DateTimeInterface
    {
        return $this->User_idUser ? $this->User_idUser->getDateOfBirth() : null;
    }


    public function getUserSex(): ?string
    {
        return $this->User_idUser ? $this->User_idUser->getSex() : null;
    }

    public function serializer(): array
    {
        return [
            "fullname" => $this->getFullname() ?: "",
            "firstname" => $this->getFirstname() ?: "",
            "lastname" => $this->getLastname() ?: "",
            "label" => $this->getLabel() ?: "",
            "description" => $this->getDescription() ?: "",
            "date_begin" => $this->getDateBegin() ?: "",
            "date_end" => $this->getDateEnd() ?: "",
            "active" => $this->getActive() ?: "",
            "created_At" => $this->getCreatedAt() ?: "",
            "albums" => $this->getAlbums() ?: "",
            "songs" => $this->getSongs() ?: "",
        ];
    }

    public function getDateBegin(): ?\DateTimeInterface
    {
        return $this->date_begin;
    }

    public function setDateBegin(\DateTimeInterface $date_begin): static
    {
        $this->date_begin = $date_begin;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(?\DateTimeInterface $date_end): static
    {
        $this->date_end = $date_end;

        return $this;
    }

    public function getActive(): ?int
    {
        return $this->active;
    }

    public function setActive(?int $active): static
    {
        $this->active = $active;

        return $this;
    }
}
