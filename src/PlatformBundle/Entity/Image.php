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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Image
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getWebPath()
    {
        $url = $this->getUrl();
        if(!preg_match('#^http#', $url))
        {
            // Cas de figure où l'attribut url ne contient que l'extension de l'image
            if(preg_match('#^[a-z]{2,4}$#i', $url))
                $url = $this->getId().'.'.$url;

            $url = $this->getUploadDir().'/'.$url;
        }

        return $url;
    }

    /**
     * Set alt
     *
     * @param string $alt
     *
     * @return Image
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;

        // Fichier déjà existant ?
        if(null !== $this->url)
        {
            // Sauvegarde du nom de fichier pour le supprimer + tard si nécessaire
            $this->tmpFileName = $this->url;

            // Re-init valeurs
            $this->url = $this->alt = null;
        }
    }

    public function getUploadDir()
    {
        return 'uploads/img';
    }

    public function getUploadRootDir()
    {
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if(null === $this->file)
            return;

        // Le nom du fichier est son ID, il faut stocker l'extension
        $this->url = $this->file->guessExtension();
        $this->alt = $this->file->getClientOriginalName();
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if(null === $this->file)
            return;

        // Supprimer le fichier précédent
        if(null !== $this->tmpFileName)
        {
            $oldFile = $this->getUploadRootDir()."/{$this->id}.{$this->tmpFileName}";
            if(file_exists($oldFile))
                unlink($oldFile);
        }

        $filename = $this->file->getClientOriginalName();
        $this->file->move
        (
            $this->getUploadRootDir(),
            $this->id.'.'.$this->url
        );

//        $this->setUrl($filename)->setAlt($filename);
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        // Sauvegarde temporaire du nom du fichier
        $this->tmpFileName = $this->getUploadRootDir()."/{$this->id}.{$this->url}";
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        // En PostRemove, on n'a pas accès à l'id, on utilise notre nom sauvegardé
        if(file_exists($this->tmpFileName))
            unlink($this->tmpFileName);
    }
}
