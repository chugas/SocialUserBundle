<?php
namespace BIT\SocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Model\UserManager;
use BIT\GoogleBundle\Google\GoogleSessionPersistence;
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
    $data = array( );
    
    try
    {
      $gData = $this->googleApi->getOAuth( )->userinfo->get( );
    }
    catch ( \Exception $e )
    {
      return $data;
    }
    
    $data[ 'id' ] = $gData[ 'id' ];
    
    if ( isset( $gData[ 'name' ] ) )
      $data = $this->extractFullName( $gData[ 'name' ], $data );
    
    if ( isset( $gData[ 'email' ] ) )
    {
      $data[ 'email' ] = $gData[ 'email' ];
      $data[ 'username' ] = $gData[ 'email' ];
    }
    
    $data[ 'photo' ] = '';
    
    if ( array_key_exists( 'picture', $gData ) )
      $data[ 'photo' ] = $gData[ 'picture' ];
    
    return $data;
  }
}
