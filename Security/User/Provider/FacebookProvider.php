<?php
namespace BIT\SocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Model\UserManager;
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
    $data = array( );
    
    try
    {
      $fData = $this->facebook->api( '/me' );
    }
    catch ( FacebookApiException $e )
    {
      return $data;
    }
    
    $data[ 'id' ] = $fData[ 'id' ];
    
    if ( isset( $fData[ 'name' ] ) )
    {
      $nameAndLastNames = explode( " ", $fData[ 'name' ] );
      $data[ 'firstname' ] = $nameAndLastNames[ 0 ];
      
      if ( count( $nameAndLastNames ) > 1 )
        $data[ 'lastname' ] = $nameAndLastNames[ 1 ];
      
      if ( count( $nameAndLastNames ) > 2 )
        $data[ 'lastname2' ] = $nameAndLastNames[ 2 ];
    }
    
    if ( isset( $fData[ 'email' ] ) )
    {
      $data[ 'email' ] = $fData[ 'email' ];
      $data[ 'username' ] = $fData[ 'email' ];
    }
    
    $data[ 'photo' ] = "https://graph.facebook.com/" . $data[ "id" ] . "/picture";
    
    return $data;
  }
}
