<?php
namespace BIT\SocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\GoogleBundle\Google\GoogleSessionPersistence;
use BIT\SocialUserBundle\Controller\SocialUserControllerService;
use BIT\SocialUserBundle\Security\User\Provider\SocialUserProvider;

class GoogleProvider extends SocialUserProvider
{
  /**
   * @var \GoogleApi
   */
  protected $googleApi;
  
  public function __construct( GoogleSessionPersistence $googleApi, Validator $validator, UserManager $userManager,
      SocialUserControllerService $socialUserManager )
  {
    parent::__construct( $validator, $userManager, $socialUserManager );
    $this->googleApi = $googleApi;
    $this->providerName = "Google";
  }
  
  protected function getData( )
  {
    try
    {
      $data = $this->googleApi->getOAuth( )->userinfo->get( );
    }
    catch ( \Exception $e )
    {
      $data = null;
    }
    
    return $data;
  }
  
  protected function setPhoto( $user, $data )
  {
    $reflectionMethod = new \ReflectionMethod( get_class( $user ), $photoFunction);
    $reflectionMethod->invoke( $user, $data[ 'picture' ] );
  }
}
