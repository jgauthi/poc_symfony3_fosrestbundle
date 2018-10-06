<?php
namespace PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Table(name="advert_application")
 * @ORM\Entity(repositoryClass="PlatformBundle\Repository\ApplicationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Application
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="author", type="string", length=255)
     */
    private $author;

    /**
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @ORM\Column(name="city", type="string", length=100)
     */
    private $city;

    /**
     * @ORM\Column(name="salaryClaim", type="integer")
     */
    private $salaryClaim;

    /**
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="PlatformBundle\Entity\Advert", inversedBy="applications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $advert;


    public function __construct()
    {
        $this->date = new \Datetime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setAuthor($author): Application
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setContent($content): Application
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setDate(\Datetime $date): Application
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * Set advert
     *
     * @param \PlatformBundle\Entity\Advert $advert
     *
     * @return Application
     */
    public function setAdvert(Advert $advert): Application
    {
        $this->advert = $advert;

        return $this;
    }

    /**
     * Get advert
     *
     * @MaxDepth(1)
     * @return \PlatformBundle\Entity\Advert
     */
    public function getAdvert(): Advert
    {
        return $this->advert;
    }

    /**
     * @ORM\PrePersist
     */
    public function increase(): void
    {
        $this->getAdvert()->increaseApplication();
    }

    /**
     * @ORM\PreRemove
     */
    public function decrease(): void
    {
        $this->getAdvert()->decreaseApplication();
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Application
     */
    public function setCity($city): Application
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Set salaryClaim
     *
     * @param integer $salaryClaim
     *
     * @return Application
     */
    public function setSalaryClaim($salaryClaim): Application
    {
        $this->salaryClaim = $salaryClaim;

        return $this;
    }

    /**
     * Get salaryClaim
     *
     * @return integer
     */
    public function getSalaryClaim(): ?int
    {
        return $this->salaryClaim;
    }
}
