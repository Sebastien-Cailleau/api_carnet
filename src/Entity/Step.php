<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\StepRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity(repositoryClass=StepRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Step
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"travel:read"})
     * @Groups({"step:show"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"travel:read"})
     * @Groups({"step:show"})
     * @Assert\NotBlank
     * @Assert\Length(max=255, maxMessage="Cette valeur est trop longue (maximum {{ limit }} caractères)")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"step:show","travel:read"})
     * @Assert\NotBlank
     * @Assert\Length(max=255, maxMessage="Cette valeur est trop longue (maximum {{ limit }} caractères)")
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     * @Groups({"travel:read"})
     * @Groups({"step:show"})
     */
    private $latitude;

    /**
     * @ORM\Column(type="float")
     * @Groups({"travel:read"})
     * @Groups({"step:show"})
     */
    private $longitude;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"travel:read"})
     * @Groups({"step:show"})
     */
    private $stepLike;

    /**
     * @ORM\Column(type="date")
     * @Groups({"travel:read"})
     * @Groups({"step:show"})
     */
    private $stepDate;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"step:show"})
     * @Assert\Type("\DateTime") 
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"step:show"})
     * @Assert\Type("\DateTime") 
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Travel::class, inversedBy="steps")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"step:show"})
     */
    private $travel;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="step", orphanRemoval=true)
     * @Groups({"step:show", "travel:read"})
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Picture::class, mappedBy="step", orphanRemoval=true)
     * @Groups({"step:show", "travel:read"})
     */
    private $pictures;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->pictures = new ArrayCollection();
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

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getStepLike(): ?int
    {
        return $this->stepLike;
    }

    public function setStepLike(?int $stepLike): self
    {
        $this->stepLike = $stepLike;

        return $this;
    }

    public function getStepDate(): ?\DateTimeInterface
    {
        return $this->stepDate;
    }

    public function setStepDate(\DateTimeInterface $stepDate): self
    {
        $this->stepDate = $stepDate;

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

    public function getTravel(): ?Travel
    {
        return $this->travel;
    }

    public function setTravel(?Travel $travel): self
    {
        $this->travel = $travel;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setStep($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getStep() === $this) {
                $comment->setStep(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Picture[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setStep($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->contains($picture)) {
            $this->pictures->removeElement($picture);
            // set the owning side to null (unless already changed)
            if ($picture->getStep() === $this) {
                $picture->setStep(null);
            }
        }

        return $this;
    }
}
