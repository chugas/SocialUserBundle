<?php
namespace BIT\SocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use BIT\SocialUserBundle\Controller\SocialUserControllerService;
use BIT\SocialUserBundle\Security\User\Provider\SocialUserProvider;
use \BaseFacebook;
use \FacebookApiException;

class FacebookProvider extends SocialUserProvider
{
  /**
   * @var \Facebook
   */
  protected $facebook;
  
  public function __construct( BaseFacebook $facebook, Validator $validator, UserManager $userManager,
      SocialUserControllerService $socialUserManager )
  {
    parent::__construct( $validator, $userManager, $socialUserManager );
    $this->facebook = $facebook;
    $this->providerName = "Facebook";
  }
  
  protected function getData( )
  {
    try
    {
      $data = $this->facebook->api( '/me' );
    }
    catch ( FacebookApiException $e )
    {
      $data = null;
    }
    
    return $data;
  }
  
  protected function setPhoto( $data )
  {
    $reflectionMethod = new \ReflectionMethod( get_class( $user ), $photoFunction);
    $reflectionMethod->invoke( $user, "https://graph.facebook.com/" . $data[ "id" ] . "/picture" );
  }
}
