<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as FosUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends FosUser implements UserInterface
{
    public const ROLE_CANDIDATE = 'ROLE_USER';
    public const ROLE_EDITOR = 'ROLE_EDITOR';
    public const ROLE_API = 'ROLE_API_ACCESS';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"user", "user-simple", "preference", "auth-token"})
     */
    protected $id;

    /**
     * @var string
     * @Groups({"user", "user-simple", "preference", "auth-token"})
     */
    protected $username;

    /**
     * @var string
     * @Groups({"user", "preference", "auth-token"})
     */
    protected $email;

    /**
     * @var array
     * @Groups({"user"})
     */
    protected $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Advert", mappedBy="author", cascade={"persist", "remove"})
     */
    private $adverts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Application", mappedBy="author", cascade={"persist", "remove"})
     */
    private $applications;

    public function __construct()
    {
        parent::__construct();
        $this->adverts = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    /**
     * @return Collection|Advert[]
     */
    public function getAdverts(): Collection
    {
        return $this->adverts;
    }

    public function addAdvert(Advert $advert): self
    {
        if (!$this->adverts->contains($advert)) {
            $this->adverts[] = $advert;
            $advert->setAuthor($this);
        }

        return $this;
    }

    public function removeAdvert(Advert $advert): self
    {
        if ($this->adverts->contains($advert)) {
            $this->adverts->removeElement($advert);
            // set the owning side to null (unless already changed)
            if ($advert->getAuthor() === $this) {
                $advert->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Application[]
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
            $application->setAuthor($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): self
    {
        if ($this->applications->contains($application)) {
            $this->applications->removeElement($application);
            // set the owning side to null (unless already changed)
            if ($application->getAuthor() === $this) {
                $application->setAuthor(null);
            }
        }

        return $this;
    }
}
