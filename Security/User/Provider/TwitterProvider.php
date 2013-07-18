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
use BIT\TwitterBundle\Twitter\TwitterSessionPersistence;

class TwitterProvider extends SocialUserProvider
{
  /**
   * @var \Twitter
   */
  protected $twitter;
  protected $session;
  
  public function __construct( TwitterSessionPersistence $twitter, Validator $validator, Session $session,
      UserManager $userManager, SocialUserControllerService $socialUserManager )
  {
    parent::__construct( $validator, $userManager, $socialUserManager );
    $this->session = $session;
    $this->twitter = $twitter;
    $this->providerName = "Twitter";
  }
  
  protected function getData( )
  {
    $data = array( );
    
    try
    {
      $info = $this->twitter->account_verifyCredentials( );
      $data[ 'id' ] = strtolower( $info->id );
      
      $nameAndLastNames = explode( " ", $info->name );
      $data[ 'firstname' ] = $nameAndLastNames[ 0 ];
      
      $data[ 'lastname' ] = " ";
      if ( count( $nameAndLastNames ) > 1 )
        $data[ 'lastname' ] = $nameAndLastNames[ 1 ];
      
      $data[ 'lastname2' ] = " ";
      if ( count( $nameAndLastNames ) > 2 )
        $data[ 'lastname2' ] = $nameAndLastNames[ 2 ];
      
      $data[ 'email' ] = sprintf( "%s@%s.com", $info->id, strtolower( $this->providerName ) );
      $data[ 'username' ] = $info->screen_name;
      
      try
      {
        $data[ 'photo' ] = $info->profile_image_url;
      }
      catch ( Exception $e )
      {
        $data[ 'photo' ] = '';
      }
    }
    catch ( Exception $e )
    {
      $data = null;
    }
    
    return $data;
  }
}
