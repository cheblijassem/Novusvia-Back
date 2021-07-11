<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RecrutementUser
 *
 * @ORM\Table(name="recrutement_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RecrutementUserRepository")
 */
class RecrutementUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="Ad", inversedBy="profiles", cascade={"persist"})
     * @ORM\JoinColumn(name="ad_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $ad;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="users", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $user;


    /**
     * @ORM\OneToOne(targetEntity="Recrutement", inversedBy="job")
     * @ORM\JoinColumn(name="recrutement_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $recrutement;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status.
     *
     * @param string|null $status
     *
     * @return RecrutementUser
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getAd()
    {
        return $this->ad;
    }

    /**
     * @param mixed $ad
     */
    public function setAd($ad)
    {
        $this->ad = $ad;
    }

    /**
     * @return mixed
     */
    public function getRecrutement()
    {
        return $this->recrutement;
    }

    /**
     * @param mixed $recrutement
     */
    public function setRecrutement($recrutement)
    {
        $this->recrutement = $recrutement;
    }



}
