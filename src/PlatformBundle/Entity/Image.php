<?php
namespace PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * @ORM\Table(name="image")
 * @ORM\Entity(repositoryClass="PlatformBundle\Repository\ImageRepository")
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
        $url = $this->url;
        if(!preg_match('#^http#', $url))
            $url = str_replace(array('app.php', 'app_dev.php'), 'uploads/img/', $_SERVER['SCRIPT_NAME']).$url;

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
    }

    public function getUploadDir()
    {
        return 'uploads/img';
    }

    public function getUploadRootDir()
    {
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    public function upload()
    {
        if(null === $this->file)
            return;

        $filename = $this->file->getClientOriginalName();
        $this->file->move($this->getUploadRootDir(), $filename);

        $this->setUrl($filename)->setAlt($filename);
    }

}
