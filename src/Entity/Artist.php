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

    #[ORM\OneToOne(targetEntity:"App\Entity\User", inversedBy:"artist")]
    #[ORM\JoinColumn(name:"user_id", referencedColumnName:"id", nullable:false)]
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column]
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

  
    public function getUserDateBirth(): ?\DateTimeInterface
    {
        return $this->User_idUser ? $this->User_idUser->getDateBirth() : null;
    }

  
    public function getUserSexe(): ?string
    {
        return $this->User_idUser ? $this->User_idUser->getSexe() : null;
    }

    public function serializer(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->getUserId(),
            'firstname' => $this->getFirstName(),
            'lastname' => $this->getLastName(),
            'fullname' => $this->fullname,
            'label' => $this->label,
            'description' => $this->description,
            'songs' => $this->songs->toArray(),
            'albums' => $this->albums->toArray(),
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : '',
            'avatar' => $this->avatar,
        ];
    }
    public function miniSerializer(): array 
    {
        return[
            'id' => $this->id,
            'firstname' => $this->getFirstName(),
            'lastname' => $this->getLastName(),
            'label' => $this->label,
            'date of birth' => $this->getUserDateBirth(),
            'sex'=> $this->getUserSexe(),
            'created_at' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : '',
            'avatar' => $this->avatar,
        ];
    }

}