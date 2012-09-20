<?php
namespace BIT\BITSocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\GoogleBundle\Google\GoogleSessionPersistence;
use BIT\BITSocialUserBundle\Controller\SocialUserControllerService;
use \BaseFacebook;
use \FacebookApiException;

class FacebookProvider implements UserProviderInterface
{
  /**
   * @var \Facebook
   */
  protected $facebook;
  protected $userManager;
  protected $validator;
  protected $em;

  public function __construct( BaseFacebook $facebook, Validator $validator, UserManager $userManager, SocialUserControllerService $socialUserManage )
  {
    $this->facebook = $facebook;
    $this->validator = $validator;
    $this->userManager = $userManager;
    $this->socialUserManager = $socialUserManager;
    $this->objectManager = $this->socialUserManager->getObjectManager( );
  }

  public function supportsClass( $class )
  {
    return $this->userManager->supportsClass( $class );
  }

  public function findUserByFbIdOrEmail( $fbId, $email = null )
  {
    $user = $this->userManager->findUserByUsernameOrEmail( $email );

    if ( !$user )
    {
      $entity = $this->socialUserManager->getRepository( )->findOneBy( array( "social_id" => $fbId ) );

      if ( is_object( $entity ) )
        $user = $entity->getUser( );
    }

    return $user;
  }

  public function loadUserByUsername( $username )
  {
    die( $username );
    try
    {
      $fbdata = $this->facebook->api( '/me' );
    }
    catch ( FacebookApiException $e )
    {
      $fbdata = null;
    }

    $user = $this->findUserByFbIdOrEmail( $username, isset( $fbdata['email'] ) ? $fbdata['email'] : null );

    if ( !empty( $fbdata ) )
    {
      if ( empty( $user ) )
      {
        $user = $this->userManager->createUser( );
        $user->setEnabled( true );
        $user->setPassword( '' );
        $user->addGroup( $this->objectManager->getRepository( "BITUserBundle:Group" )->findOneBy( array( "name" => "USER" ) ) );
      }

      if ( isset( $fbdata['id'] ) )
      {
        $user->addGroup( $this->objectManager->getRepository( "BITUserBundle:Group" )->findOneBy( array( "name" => "FACEBOOK" ) ) );
      }

      if ( isset( $fbdata['name'] ) )
      {
        $nameAndLastNames = explode( " ", $fbdata['name'] );
        if ( count( $nameAndLastNames ) > 1 )
        {
          $user->setFirstname( $nameAndLastNames[0] );
          $user->setLastname( $nameAndLastNames[1] );
          $user->setLastname2( ( count( $nameAndLastNames ) > 2 ) ? $nameAndLastNames[2] : "" );
        }
        else
        {
          $user->setFirstname( $fbdata["name"] );
          $user->setLastname( "" );
          $user->setLastname2( "" );
        }
      }

      if ( isset( $fbdata['email'] ) )
      {
        $user->setEmail( $fbdata['email'] );
        $user->setUsername( $fbdata['email'] );
      }
      else
      {
        $user->setEmail( $fbdata['id'] . "@facebook.com" );
        $user->setUsername( $fbdata['id'] . "@facebook.com" );
      }

      if ( count( $this->validator->validate( $user, 'Facebook' ) ) )
      {
        // TODO: the user was found obviously, but doesnt match our expectations, do something smart
        throw new UsernameNotFoundException( 'The facebook user could not be stored');
      }

      $this->userManager->updateUser( $user );

      $socialUser = $this->objectManager->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "social_id" => $fbdata['id'] ) );

      if ( isset( $fbdata['id'] ) && !is_object( $socialUser ) )
      {
        $socialUser = $this->socialUserManager->create( );
        $socialUser->setSocialId( $fbdata['id'] );
        $socialUser->setUser( $user );
        $socialUser->setSocialName( 'FACEBOOK' );
        $user->addGroup( $this->objectManager->getRepository( "BITUserBundle:Group" )->findOneBy( array( "name" => "FACEBOOK" ) ) );
        $this->objectManager->persist( $socialUser );
        $this->objectManager->flush( );
      }
    }

    if ( empty( $user ) )
    {
      throw new UsernameNotFoundException( 'The user is not authenticated on facebook');
    }

    return $user;
  }

  public function refreshUser( UserInterface $user )
  {
    if ( !$this->supportsClass( get_class( $user ) ) )
    {
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not supported.', get_class( $user ) ));
    }

    $entity = $this->objectManager->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "user" => $user->getId( ), "social_name" => "FACEBOOK" ) );

    if ( !is_object( $entity ) )
    {
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not facebook.', get_class( $user ) ));
    }

    return $entity->getUser( );
  }
}
