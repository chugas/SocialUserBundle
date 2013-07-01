<?php
namespace BIT\SocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use BIT\SocialUserBundle\Controller\SocialUserControllerService;

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
        $defaultGroup = $this->groupRepository->findOneBy( array( "name" => "USER" ) );
        $user->addGroup( $defaultGroup );
      }
      
      $name = $data[ 'name' ];
      if ( isset( $name ) )
      {
        $nameAndLastNames = explode( " ", $name );
        
        if ( count( $nameAndLastNames ) > 1 )
        {
          $user->setFirstname( $nameAndLastNames[ 0 ] );
          $user->setLastname( $nameAndLastNames[ 1 ] );
          $user->setLastname2( ( count( $nameAndLastNames ) > 2 ) ? $nameAndLastNames[ 2 ] : "" );
        }
        else
        {
          $user->setFirstname( $nameAndLastNames[ 0 ] );
          $user->setLastname( "" );
          $user->setLastname2( "" );
        }
      }
      
      if ( isset( $email ) )
      {
        $user->setEmail( $email );
        $user->setUsername( $email );
      }
      else
      {
        $user->setEmail( '' );
        $user->setUsername( '' );
      }
      
      print_r($data);
      die();
      
      if ( count( $this->validator->validate( $user, $this->providerName ) ) )
      {
        // TODO: the user was found obviously, but doesnt match our expectations, do something smart
        throw new UsernameNotFoundException( sprintf( 'The %s user could not be stored', $this->providerName ));
      }
      
      $this->userManager->updateUser( $user );
      
      $socialUser = $this->objectManager->getRepository( "BITSocialUserBundle:User" )
          ->findOneBy( array( "social_id" => $data[ 'id' ] ) );
      
      if ( !is_object( $socialUser ) )
      {
        $socialUser = $this->socialUserManager->create( );
        $socialUser->setSocialId( $data[ 'id' ] );
        $socialUser->setUser( $user );
        $socialUser->setSocialName( strtoupper( $this->providerName ) );
        $socialGroup = $this->groupRepository->findOneBy( array( "name" => strtoupper( $this->providerName ) ) );
        $user->addGroup( $socialGroup );
        $this->objectManager->persist( $socialUser );
        $this->objectManager->flush( );
      }
    }
    
    if ( empty( $user ) )
    {
      throw new UsernameNotFoundException( sprintf( 'The user is not authenticated on google', $this->providerName ));
    }
    
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

