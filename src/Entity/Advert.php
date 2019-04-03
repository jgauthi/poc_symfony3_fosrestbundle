<?php

namespace App\Entity;

use App\Validator\Antiflood;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LogicException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\{Groups, MaxDepth};
use Symfony\Component\Validator\{Constraints as Assert, Context\ExecutionContextInterface};

/**
 * Advert.
 *
 * @ORM\Table(name="advert")
 * @ORM\Entity(repositoryClass="App\Repository\AdvertRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="title", message="An advert already exists with this title.")
 */
class Advert
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"advert", "application"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     * @Assert\Length(min=10, max=255)
     * @Assert\Type(type="string")
     * @Groups({"advert", "application"})
     */
    private $title;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"title"}, updatable=true, separator="-")
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     * @Groups({"advert_additional_info"})
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Antiflood()
     * @Groups({"advert"})
     */
    private $content;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Assert\Type(type="\DateTime")
     * @Groups({"advert", "application"})
     */
    private $date;

    /**
     * @ORM\Column(name="published", type="boolean")
     * @Assert\Type(type="bool")
     * @Groups({"advert_additional_info"})
     */
    private $published = true;

    /**
     * @ORM\Column(name="archived", type="boolean")
     * @Assert\Type(type="bool")
     */
    private $archived = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"persist", "remove"})
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="adverts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"advert", "application"})
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", cascade={"persist"})
     * @ORM\JoinTable(name="advert_category")
     * @Assert\Valid()
     * @Groups({"category"})
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Application", mappedBy="advert", cascade={"persist", "remove"})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $applications;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @Groups({"advert_additional_info"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="nb_applications", type="integer")
     * @Groups({"advert"})
     */
    private $nbApplications = 0;

    public function __construct()
    {
        // By default, the date of the announcement is today's date
        $this->date = new Datetime();
        $this->categories = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    /**
     * Get name (for EasyAdminBundle).
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Advert
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Advert
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set date.
     *
     * @param DateTime $date
     *
     * @return Advert
     */
    public function setDate(DateTime $date): self
    {
        $dateCreationPlatform = DateTime::createFromFormat('Y-m-d H:i', '2018-01-23 19:05');
        if ($date < $dateCreationPlatform) {
            throw new LogicException('The advert can\'t be created before the '.$dateCreationPlatform->format('d/m/Y'));
        }
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * Set published.
     *
     * @param bool $published
     *
     * @return Advert
     */
    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published.
     *
     * @return bool
     */
    public function getPublished(): bool
    {
        return $this->published;
    }

    /**
     * Set archived.
     *
     * @param bool $archived
     *
     * @return Advert
     */
    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * Get archived.
     *
     * @return bool
     */
    public function getArchived(): bool
    {
        return (bool) $this->archived;
    }

    /**
     * Set image.
     *
     * @param Image $image
     *
     * @return Advert
     */
    public function setImage(Image $image = null): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return Image
     */
    public function getImage(): ?Image
    {
        return $this->image;
    }

    /**
     * Add category.
     *
     * @param Category $category
     *
     * @return Advert
     */
    public function addCategory(Category $category): self
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category.
     *
     * @param Category $category
     */
    public function removeCategory(Category $category): void
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories.
     *
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * Add application.
     *
     * @param Application $application
     *
     * @return Advert
     */
    public function addApplication(Application $application): self
    {
        $this->applications[] = $application;

        $application->setAdvert($this);

        return $this;
    }

    /**
     * Remove application.
     *
     * @param Application $application
     */
    public function removeApplication(Application $application): void
    {
        $this->applications->removeElement($application);

        // And if our relationship was optional (nullable = true, which is not our case here attention):
        // $application->setAdvert(null);
    }

    /**
     * Get applications.
     *
     * @MaxDepth(1)
     *
     * @return Collection
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDate(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function increaseApplication(): void
    {
        ++$this->nbApplications;
    }

    public function decreaseApplication(): void
    {
        --$this->nbApplications;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Advert
     */
    public function setSlug($slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Set updatedAt.
     *
     * @param DateTime $updatedAt
     *
     * @return Advert
     */
    public function setUpdatedAt($updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return DateTime
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Set nbApplications.
     *
     * @param int $nbApplications
     *
     * @return Advert
     */
    public function setNbApplications(int $nbApplications): self
    {
        $this->nbApplications = $nbApplications;

        return $this;
    }

    /**
     * Get nbApplications.
     *
     * @return int
     */
    public function getNbApplications(): int
    {
        return $this->nbApplications;
    }

    /**
     * @Assert\Callback()
     *
     * @param ExecutionContextInterface $context
     */
    public function isContentValid(ExecutionContextInterface $context): void
    {
        $blacklistWords = ['demotivating', 'abandonment'];

        if (preg_match('#'.implode('|', $blacklistWords).'#i', $this->getContent())) {
            $context
                ->buildViolation('Content with a banned word')  // Error message
                ->atPath('content')                                // Attribute of the object
                ->addViolation();                                       // Trigger error
        }
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
