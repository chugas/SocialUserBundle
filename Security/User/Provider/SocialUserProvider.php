<?php
namespace BIT\SocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use BIT\SocialUserBundle\Controller\SocialUserControllerService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class SocialUserProvider implements UserProviderInterface
{
  protected $userManager;
  protected $validator;
  protected $em;
  protected $providerName;
  protected $groupRepository;
  
  public function __construct( Validator $validator, UserManager $userManager,
      SocialUserControllerService $socialUserManager )
  {
    $this->validator = $validator;
    $this->userManager = $userManager;
    $this->socialUserManager = $socialUserManager;
    $this->objectManager = $this->socialUserManager->getObjectManager( );
    $this->providerName = '';
    $this->groupRepository = $this->objectManager->getRepository( "BITUserBundle:Group" );
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
  
  abstract protected function getData( );
  abstract protected function setPhoto( $photoFunction, $user, $data );
  
  private function setName( $user, $name, $lastname, $lastname2 )
  {
    $firstnameFunction = $this->socialUserManager->getFunctionName( "firstname" );
    if ( !empty( $firstnameFunction ) )
    {
      $reflectionMethod = new \ReflectionMethod( get_class( $user ), $firstnameFunction);
      $reflectionMethod->invoke( $user, $name );
    }
    
    $lastnameFunction = $this->socialUserManager->getFunctionName( "lastname" );
    if ( !empty( $lastnameFunction ) )
    {
      $reflectionMethod = new \ReflectionMethod( get_class( $user ), $lastnameFunction);
      $reflectionMethod->invoke( $user, $lastname );
    }
    
    $lastname2Function = $this->socialUserManager->getFunctionName( "lastname2" );
    if ( !empty( $lastname2Function ) )
    {
      $reflectionMethod = new \ReflectionMethod( get_class( $user ), $lastname2Function);
      $reflectionMethod->invoke( $user, $lastname2 );
    }
  }
  
  public function loadUserByUsername( $username )
  {
    $data = $this->getData( );
    
    if ( !empty( $data ) )
    {
      $email = $data[ 'email' ];
      $user = $this->findUserBySocialIdOrEmail( $username, isset( $email ) ? $email : null );
      
      if ( empty( $user ) )
      {
        $user = $this->userManager->createUser( );
        $user->setPassword( '' );
        $user->setEnabled( true );
        $defaultGroupName = $this->socialUserManager->getDefaultGroup( );
        $defaultGroup = $this->groupRepository->findOneBy( array( "name" => $defaultGroupName ) );
        $user->addGroup( $defaultGroup );
      }
      
      $name = $data[ 'name' ];
      if ( isset( $name ) )
      {
        $nameAndLastNames = explode( " ", $name );
        $firstname = $nameAndLastNames[ 0 ];
        $lastname = "";
        $lastname2 = "";
        
        if ( count( $nameAndLastNames ) > 1 )
          $lastname = $nameAndLastNames[ 1 ];
        
        if ( count( $nameAndLastNames ) > 2 )
          $lastname2 = $nameAndLastNames[ 2 ];
        
        $this->setName( $user, $name, $lastname, $lastname2 );
      }
      
      if ( isset( $email ) )
      {
        $emailFunction = $this->socialUserManager->getFunctionName( "email" );
        $reflectionMethod = new \ReflectionMethod( get_class( $user ), $emailFunction);
        $reflectionMethod->invoke( $user, $email );
        
        $usernameFunction = $this->socialUserManager->getFunctionName( "username" );
        $reflectionMethod = new \ReflectionMethod( get_class( $user ), $usernameFunction);
        $reflectionMethod->invoke( $user, $email );
      }
      else
      {
        throw new NotFoundHttpException( "the user dont have email");
      }
      
      $photoFunction = $this->socialUserManager->getFunctionName( "photo" );
      if ( !empty( $photoFunction ) )
        $this->setPhoto( $photoFunction, $user, $data );
      
      if ( count( $this->validator->validate( $user, $this->providerName ) ) )
      {
        // TODO: the user was found obviously, but doesnt match our expectations, do something smart
        throw new UsernameNotFoundException( sprintf( 'The %s user could not be stored', $this->providerName ));
      }
      
      $this->userManager->updateUser( $user );
      
      $socialUserRepository = $this->objectManager->getRepository( "BITSocialUserBundle:User" );
      $socialUser = $socialUserRepository->findOneBy( array( "social_id" => $data[ 'id' ] ) );
      
      if ( !is_object( $socialUser ) )
      {
        $socialUser = $this->socialUserManager->create( );
        $socialUser->setSocialId( $data[ 'id' ] );
        $socialUser->setUser( $user );
        $socialUser->setSocialName( strtoupper( $this->providerName ) );
        
        if ( $this->socialUserManager->getSetGroupAsSocialName( ) )
        {
          $socialGroup = $this->groupRepository->findOneBy( array( "name" => strtoupper( $this->providerName ) ) );
          $user->addGroup( $socialGroup );
        }
        
        $this->objectManager->persist( $socialUser );
        $this->objectManager->flush( );
      }
    }
    
    if ( empty( $user ) )
      throw new UsernameNotFoundException( sprintf( 'The user is not authenticated on ', $this->providerName ));
    
    return $user;
  }
  
  public function refreshUser( UserInterface $user )
  {
    if ( !$this->supportsClass( get_class( $user ) ) )
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not supported.', get_class( $user ) ));
    
    $userRepository = $this->objectManager->getRepository( "BITSocialUserBundle:User" );
    $entity = $userRepository->findOneBy( array( "user" => $user->getId( ), "social_name" => $this->providerName ) );
    
    if ( !is_object( $entity ) )
    {
      $message = sprintf( 'Instances of "%s" are not %s.', get_class( $user ), $this->providerName );
      throw new UnsupportedUserException( $message);
    }
    
    return $entity->getUser( );
  }
}

