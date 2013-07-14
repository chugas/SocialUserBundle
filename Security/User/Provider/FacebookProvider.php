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
    
    $data[ 'firstname' ] = $fData[ 'first_name' ];
    if ( array_key_exists( 'middle_name', $fData ) )
      $data[ 'firstname' ] .= $fData[ 'middle_name' ];
    
    $data[ 'lastname' ] = '';
    $data[ 'lastname2' ] = '';
    
    if ( array_key_exists( 'last_name', $fData ) )
    {
      $lastNames = explode( " ", $fData[ 'last_name' ] );
      $data[ 'lastname' ] = $lastNames[ 0 ];
      
      if ( count( $lastNames ) > 1 )
      {
        $skip = true;
        foreach ( $lastNames as $lastName )
        {
          if ( !$skip )
            $data[ 'lastname2' ] .= " " . $lastName;
          $skip = false;
        }
      }
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
