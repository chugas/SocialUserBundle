<?php
namespace BIT\SocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Model\UserManager;
use BIT\SocialUserBundle\Controller\SocialUserControllerService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class SocialUserProvider implements UserProviderInterface
{
  protected $objectManager;
  protected $userManager;
  protected $socialUserManager;
  protected $validator;
  protected $providerName;
  
  abstract protected function getData( );
  
  public function __construct( Validator $validator, UserManager $userManager,
      SocialUserControllerService $socialUserManager )
  {
    $this->objectManager = $socialUserManager->getObjectManager( );
    $this->userManager = $userManager;
    $this->socialUserManager = $socialUserManager;
    $this->validator = $validator;
    $this->providerName = '';
  }
  
  public function supportsClass( $class )
  {
    return $this->userManager->supportsClass( $class );
  }
  
  public function findUserBySocialIdOrEmail( $socialId, $email = null )
  {
    $user = $this->userManager->findUserByUsernameOrEmail( $email );
    
    if ( !$user )
    {
      $entity = $this->socialUserManager->getRepository( )->findOneBy( array( "social_id" => $socialId ) );
      
      if ( is_object( $entity ) )
        $user = $entity->getUser( );
    }
    
    return $user;
  }
  
  private function createUser( )
  {
    // create user
    $user = $this->userManager->createUser( );
    $user->setPassword( '' );
    $user->setEnabled( true );
    
    // set default
    $user->addRole( $this->socialUserManager->getDefaultRole( ) );
    
    return $user;
  }
  
  private function getUser( $username, $email )
  {
    $user = $this->findUserBySocialIdOrEmail( $username, isset( $email ) ? $email : null );
    
    if ( empty( $user ) )
      $user = $this->createUser( );
    
    return $user;
  }
  
  private function setupUser( $user, $data )
  {
    foreach ( $this->socialUserManager->getFunctionsName( ) as $property => $handler )
    {
      $currentData = $data[ $property ];
      if ( !empty( $handler ) && !empty( $currentData ) )
      {
        $reflectionMethod = new \ReflectionMethod( get_class( $user ), $handler);
        $reflectionMethod->invoke( $user, $currentData );
      }
    }
    
    foreach ( $this->socialUserManager->getDefaultValues( ) as $property => $value )
    {
      $functionName = "set" . ucfirst( $property );
      $reflectionMethod = new \ReflectionMethod( get_class( $user ), $functionName);
      $reflectionMethod->invoke( $user, $value );
    }
    
    return $user;
  }
  
  private function createSocialUser( $user, $data )
  {
    $socialUserRepository = $this->socialUserManager->getRepository( );
    $socialUser = $socialUserRepository->findOneBy( array( "social_id" => $data[ 'id' ] ) );
    
    if ( !is_object( $socialUser ) )
    {
      $socialUser = $this->socialUserManager->create( );
      $socialUser->setSocialId( $data[ 'id' ] );
      $socialUser->setUser( $user );
      $socialUser->setSocialName( strtoupper( $this->providerName ) );
      
      if ( $this->socialUserManager->getSetRoleAsSocialName( ) )
        $user->addRole( "ROLE_" . strtoupper( $this->providerName ) );
      
      $this->objectManager->persist( $socialUser );
      $this->objectManager->flush( );
    }
    
    return $user;
  }
  
  public function loadUserByUsername( $username )
  {
    // get data from specific provider
    $data = $this->getData( );
    
    if ( !empty( $data ) )
    {
      $user = $this->getUser( $username, $data[ 'email' ] );
      $user = $this->setupUser( $user, $data );
      
      if ( count( $this->validator->validate( $user, $this->providerName ) ) )
        throw new UsernameNotFoundException( sprintf( 'The %s user could not be stored', $this->providerName ));
      
      $this->userManager->updateUser( $user );
      $user = $this->createSocialUser( $user, $data );
      $this->userManager->updateUser( $user );
    }
    
    if ( empty( $user ) )
      throw new UsernameNotFoundException( sprintf( 'The user is not authenticated on ', $this->providerName ));
    
    return $user;
  }
  
  public function refreshUser( UserInterface $user )
  {
    if ( !$this->supportsClass( get_class( $user ) ) )
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not supported.', get_class( $user ) ));
    
    $keys = array( "user" => $user->getId( ), "social_name" => $this->providerName );
    $socialUserRepository = $this->socialUserManager->getRepository( );
    $socialUser = $socialUserRepository->findOneBy( $keys );
    
    if ( !is_object( $socialUser ) )
    {
      $message = sprintf( 'Instances of "%s" are not %s.', get_class( $user ), $this->providerName );
      throw new UnsupportedUserException( $message);
    }
    
    return $socialUser->getUser( );
  }
  
  protected function extractFullName( $name, $data )
  {
    $nameAndLastNames = explode( " ", $name );
    $data[ 'firstname' ] = $nameAndLastNames[ 0 ];
    
    $data[ 'lastname' ] = '';
    if ( count( $nameAndLastNames ) > 1 )
      $data[ 'lastname' ] = $nameAndLastNames[ 1 ];
    
    $data[ 'lastname2' ] = '';
    if ( count( $nameAndLastNames ) > 2 )
      $data[ 'lastname2' ] = $nameAndLastNames[ 2 ];
    
    return $data;
  }
}
