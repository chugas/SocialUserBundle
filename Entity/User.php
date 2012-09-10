<?php

namespace BIT\BITSocialBITUserBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="social_user")
 */
class User
{
  /**
   * @ORM\Id
   * @ORM\Column(type="string")
   */
  protected $id;

  /**
   * @ORM\Id
   * @ORM\ManyToOne(targetEntity="BIT\BITUserBundle\Entity\User" )
   * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="CASCADE")
   */
  protected $user;
  
  /**
   * @ORM\Column(type="string")
   */
  protected $social_name;

  public function setId( $id )
  {
    $this->id = $id;
  }

  public function getId( )
  {
    return $this->id;
  }

  public function setUser( \BIT\BITUserBundle\Entity\User $user )
  {
    $this->user = $user;
  }

  public function getUser( )
  {
    return $this->user;
  }
  
  public function setSocialName( $socialName )
  {
    $this->social_name = $socialName;
  }
  
  public function getSocialName( )
  {
    return $this->social_name;
  }
}
