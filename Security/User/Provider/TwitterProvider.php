<?php

namespace BIT\SocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Model\UserManager;
use FOS\UserBundle\Model\GroupManager;
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
      UserManager $userManager, GroupManager $groupManager, SocialUserControllerService $socialUserManager )
  {
    parent::__construct( $validator, $userManager, $groupManager, $socialUserManager );
    $this->session = $session;
    $this->twitter_oauth = $twitter_oauth;
    $this->providerName = "Twitter";
  }
  
  protected function getData( )
  {
    $accessToken = $this->session->get( 'access_token' );
    $accessTokenSecret = $this->session->get( 'access_token_secret' );
    $this->twitter_oauth->setOAuthToken( $accessToken, $accessTokenSecret );
    
    $data = array( );
    
    try
    {
      $tData = $this->twitter_oauth->get( 'account/verify_credentials' );
    }
    catch ( Exception $e )
    {
      return $data;
    }
    
    $data[ 'id' ] = strtolower( $tData->id );
    
    if ( isset( $tData->name ) )
    {
      $nameAndLastNames = explode( " ", $tData->name );
      $data[ 'firstname' ] = $nameAndLastNames[ 0 ];
      
      if ( count( $nameAndLastNames ) > 1 )
        $data[ 'lastname' ] = $nameAndLastNames[ 1 ];
      
      if ( count( $nameAndLastNames ) > 2 )
        $data[ 'lastname2' ] = $nameAndLastNames[ 2 ];
    }
    
    if ( isset( $gData[ 'email' ] ) )
    {
      $data[ 'email' ] = sprintf( "%s@%s.com", $data[ 'id' ], strtolower( $this->providerName ) );
      $data[ 'username' ] = $tData->username;
    }
    
    $data[ 'photo' ] = "";
    
    return $data;
  }
}
