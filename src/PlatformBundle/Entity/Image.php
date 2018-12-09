<?php

namespace PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Table(name="image")
 * @ORM\Entity(repositoryClass="PlatformBundle\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(name="alt", type="string", length=255)
     */
    private $alt;

    private $file;
    private $tmpFileName;

    /**
     * Get name (for EasyAdminBundle)
     */
    public function __toString(): ?string
    {
        return $this->getUrl();
    }

    /**
     * Get id.
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Image
     */
    public function setUrl($url): Image
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getWebPath(): string
    {
        $url = $this->getUrl();
        if (!preg_match('#^http#', $url)) {
            // Cas de figure où l'attribut url ne contient que l'extension de l'image
            if (preg_match('#^[a-z]{2,4}$#i', $url)) {
                $url = $this->getId().'.'.$url;
            }

            $url = $this->getUploadDir().'/'.$url;
        }

        return $url;
    }

    /**
     * Set alt.
     *
     * @param string $alt
     *
     * @return Image
     */
    public function setAlt($alt): Image
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt.
     *
     * @return string
     */
    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null): Image
    {
        $this->file = $file;

        // Fichier déjà existant ?
        if (null !== $this->url) {
            // Sauvegarde du nom de fichier pour le supprimer + tard si nécessaire
            $this->tmpFileName = $this->url;

            // Re-init valeurs
            $this->url = $this->alt = null;
        }
    }

    public function getUploadDir(): string
    {
        return 'uploads/img';
    }

    public function getUploadRootDir(): string
    {
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload(): void
    {
        if (null === $this->file) {
            return;
        }

        // Le nom du fichier est son ID, il faut stocker l'extension
        $this->url = $this->file->guessExtension();
        $this->alt = $this->file->getClientOriginalName();
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload(): void
    {
        if (null === $this->file) {
            return;
        }

        // Delete previous file
        if (null !== $this->tmpFileName) {
            $oldFile = $this->getUploadRootDir()."/{$this->id}.{$this->tmpFileName}";
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        $filename = $this->file->getClientOriginalName();
        $this->file->move(
            $this->getUploadRootDir(),
            $this->id.'.'.$this->url
        );

//        $this->setUrl($filename)->setAlt($filename);
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload(): void
    {
        // Temporary backup the filename
        $this->tmpFileName = $this->getUploadRootDir()."/{$this->id}.{$this->url}";
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload(): void
    {
        // On PostRemove, we don't have access to the id, we use our saved name
        if (file_exists($this->tmpFileName)) {
            unlink($this->tmpFileName);
        }
    }
}
