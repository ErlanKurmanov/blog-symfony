<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * @var Collection<int, PostLike>
     */
    #[ORM\OneToMany(targetEntity: PostLike::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $postLikes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'post_images', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'integer')]
    private int $likesCount = 0;

    #[ORM\Column(type: 'integer')]
    private int $dislikesCount = 0;

    // New approval fields
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'pending'])]
    private string $status = 'pending'; // pending, approved, rejected

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $approvedBy = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->postLikes = new ArrayCollection();
        $this->status = 'pending';
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function isAuthor(User $user): bool
    {
        return $this->author && $this->author->getId() === $user->getId();
    }

    /**
     * @return Collection<int, PostLike>
     */
    public function getPostLikes(): Collection
    {
        return $this->postLikes;
    }

    public function addPostLike(PostLike $postLike): static
    {
        if (!$this->postLikes->contains($postLike)) {
            $this->postLikes->add($postLike);
            $postLike->setPost($this);
        }
        return $this;
    }

    public function removePostLike(PostLike $postLike): static
    {
        if ($this->postLikes->removeElement($postLike)) {
            if ($postLike->getPost() === $this) {
                $postLike->setPost(null);
            }
        }
        return $this;
    }

    public function getLikesCount(): int
    {
        return $this->likesCount;
    }

    public function getDislikesCount(): int
    {
        return $this->dislikesCount;
    }

    public function isLikedByUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        foreach ($this->postLikes as $like) {
            if ($like->getUser() === $user && $like->getType() === 'like') {
                return true;
            }
        }
        return false;
    }

    public function isDislikedByUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        foreach ($this->postLikes as $like) {
            if ($like->getUser() === $user && $like->getType() === 'dislike') {
                return true;
            }
        }
        return false;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function setLikesCount(int $likesCount): static
    {
        $this->likesCount = $likesCount;
        return $this;
    }

    public function setDislikesCount(int $dislikesCount): static
    {
        $this->dislikesCount = $dislikesCount;
        return $this;
    }

    public function incrementLikes(): void
    {
        $this->likesCount++;
    }

    public function decrementLikes(): void
    {
        $this->likesCount--;
    }

    public function incrementDislikes(): void
    {
        $this->dislikesCount++;
    }

    public function decrementDislikes(): void
    {
        $this->dislikesCount--;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?User $approvedBy): static
    {
        $this->approvedBy = $approvedBy;
        return $this;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeImmutable $approvedAt): static
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(User $admin): void
    {
        $this->status = 'approved';
        $this->approvedBy = $admin;
        $this->approvedAt = new \DateTimeImmutable();
        $this->rejectionReason = null;
    }

    public function reject(User $admin, ?string $reason = null): void
    {
        $this->status = 'rejected';
        $this->approvedBy = $admin;
        $this->approvedAt = new \DateTimeImmutable();
        $this->rejectionReason = $reason;
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            default => 'secondary'
        };
    }
}
