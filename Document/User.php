<?php

namespace BIT\BITSocialUserBundle\Document;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="social_user")
 */
class User
{
  /**
   * @MongoDB\Id
   */
  protected $id;

  /**
   * @MongoDB\String
   */
  protected $social_id;

  /**
   * @MongoDB\ReferenceOne(targetDocument="BIT\BITUserBundle\Document\User", cascade={"all"})
   */
  protected $user;

  /**
   * @MongoDB\String
   */
  protected $social_name;

  public function getId( )
  {
    return $this->id;
  }

  public function setSocialId( $socialId )
  {
    $this->social_id = $socialId;
  }

  public function getSocialId( )
  {
    return $this->social_id;
  }

  public function setUser( \BIT\BITUserBundle\Document\User $user )
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
