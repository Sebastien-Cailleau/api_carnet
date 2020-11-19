<?php

namespace App\Entity;

use App\Repository\TravelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity(repositoryClass=TravelRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Travel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"travel:read","travel:list"})
     * @Groups({"step:show"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"travel:read","travel:list"})
     * @Groups({"step:show"})
     * @Assert\NotBlank
     * @Assert\Length(max=255, maxMessage="Cette valeur est trop longue (maximum {{ limit }} caractères)")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({"travel:read","travel:list"})
     * @Groups({"step:show"})
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"travel:read","travel:list"})
     * @Groups({"step:show"})
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"travel:read","travel:list"})
     * @Groups({"step:show"})
     * @Assert\Length(max=255, maxMessage="Cette valeur est trop longue (maximum {{ limit }} caractères)")
     */
    private $pictureUrl;

    /**
     * @ORM\Column(type="date")
     * @Groups({"travel:read"})
     */
    private $creationDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Type("\DateTime") 
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type("\DateTime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="travel")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"travel:read"})
     * @Groups({"step:show"})
     */
    private $creator;

    /**
     * @ORM\OneToMany(targetEntity=Step::class, mappedBy="travel", orphanRemoval=true)
     * @Groups({"travel:read"})
     */
    private $steps;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="follower")
     * @Groups({"travel:read"})
     */
    private $followers;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenCreation;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
        $this->followers = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function onPersist()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }

    public function setPictureUrl(?string $pictureUrl): self
    {
        $this->pictureUrl = $pictureUrl;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection|Step[]
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Step $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps[] = $step;
            $step->setTravel($this);
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
            // set the owning side to null (unless already changed)
            if ($step->getTravel() === $this) {
                $step->setTravel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(User $follower): self
    {
        if (!$this->followers->contains($follower)) {
            $this->followers[] = $follower;
        }

        return $this;
    }

    public function removeFollower(User $follower): self
    {
        if ($this->followers->contains($follower)) {
            $this->followers->removeElement($follower);
        }

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenCreation(): ?\DateTimeInterface
    {
        return $this->tokenCreation;
    }

    public function setTokenCreation(?\DateTimeInterface $tokenCreation): self
    {
        $this->tokenCreation = $tokenCreation;

        return $this;
    }
}
