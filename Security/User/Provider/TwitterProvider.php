<?php

namespace BIT\SocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use BIT\SocialUserBundle\Controller\SocialUserControllerService;
use BIT\SocialUserBundle\Security\User\Provider\SocialUserProvider;
use BIT\SocialUserBundle\Entity\User as SocialUser;
use \TwitterOAuth;

class TwitterProvider extends SocialUserProvider
{
  /**
   * @var \Twitter
   */
  protected $twitter_oauth;
  protected $session;
  
  public function __construct( TwitterOAuth $twitter_oauth, Validator $validator, Session $session,
      UserManager $userManager, SocialUserControllerService $socialUserManager )
  {
    parent::__construct( $validator, $userManager, $socialUserManager );
    $this->session = $session;
    $this->twitter_oauth = $twitter_oauth;
    $this->providerName = "Twitter";
  }
  
  protected function getData( )
  {
    $accessToken = $this->session->get( 'access_token' );
    $accessTokenSecret = $this->session->get( 'access_token_secret' );
    $this->twitter_oauth->setOAuthToken( $accessToken, $accessTokenSecret );
    
    try
    {
      $info = $this->twitter_oauth->get( 'account/verify_credentials' );
    }
    catch ( Exception $e )
    {
      $info = null;
    }
    
    echo "<pre>";
    print_r($info);
    echo "</pre>";
    die();
    
    $data = array( );
    $data[ 'id' ] = strtolower( $info->id );
    $data[ 'email' ] = sprintf( "%s@%s.com", $data[ 'id' ], strtolower( $this->providerName ) );
    $data[ 'name' ] = $info->name;
    
    return $data;
  }
  
  protected function setPhoto( $photoFunction, $user, $data )
  {
    $photo = $data[ 'picture' ];
    if ( isset( $photo ) )
    {
      $photoFunction = $this->socialUserManager->getFunctionName( "photo" );
      $reflectionMethod = new \ReflectionMethod( get_class( $user ), $photoFunction);
      $reflectionMethod->invoke( $user, $photo );
    }
  }
}
