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

class GoogleProvider implements UserProviderInterface
{
  /**
   * @var \GoogleApi
   */
  protected $googleApi;
  protected $validator;
  protected $userManager;
  protected $socialUserManager;
  protected $objectManager;

  public function __construct( GoogleSessionPersistence $googleApi, Validator $validator, UserManager $userManager, SocialUserControllerService $socialUserManager )
  {
    $this->googleApi = $googleApi;
    $this->validator = $validator;
    $this->userManager = $userManager;
    $this->socialUserManager = $socialUserManager;
    $this->objectManager = $this->socialUserManager->getObjectManager( );
  }

  public function supportsClass( $class )
  {
    return $this->userManager->supportsClass( $class );
  }

  public function findUserByGIdOrEmail( $gId, $email = null )
  {
    $user = $this->userManager->findUserByUsernameOrEmail( $email );

    if ( !$user )
    {
      $entity = $this->socialUserManager->getRepository( )->findOneBy( array( "social_id" => $gId ) );

      if ( is_object( $entity ) )
        $user = $entity->getUser( );
    }
    return $user;
  }

  public function loadUserByUsername( $username )
  {
    try
    {
      $gData = $this->googleApi->getOAuth( )->userinfo->get( );
    }
    catch ( \Exception $e )
    {
      $gData = null;
    }

    $email = $gData->getEmail( );
    $user = $this->findUserByGIdOrEmail( $username, isset( $email ) ? $email : null );

    if ( !empty( $gData ) )
    {
      if ( empty( $user ) )
      {
        $user = $this->userManager->createUser( );
        $user->setPassword( '' );
        $user->setEnabled( true );
        $user->addGroup( $this->objectManager->getRepository( "BITUserBundle:Group" )->findOneBy( array( "name" => "USER" ) ) );
      }

      $name = $gData->getName( );
      if ( isset( $name ) )
      {
        $nameAndLastNames = explode( " ", $name );

        if ( count( $nameAndLastNames ) > 1 )
        {
          $user->setFirstname( $nameAndLastNames[0] );
          $user->setLastname( $nameAndLastNames[1] );
          $user->setLastname2( ( count( $nameAndLastNames ) > 2 ) ? $nameAndLastNames[2] : "" );
        }
        else
        {
          $user->setFirstname( $nameAndLastNames[0] );
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
        $user->setEmail( $gData->getId( ) . "@google.com" );
        $user->setUsername( $gData->getId( ) . "@google.com" );
      }

      if ( count( $this->validator->validate( $user, 'Google' ) ) )
      {
        // TODO: the user was found obviously, but doesnt match our expectations, do something smart
        throw new UsernameNotFoundException( 'The google user could not be stored');
      }

      $this->userManager->updateUser( $user );

      $socialUser = $this->objectManager->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "social_id" => $gData->getId( ) ) );

      if ( !is_object( $socialUser ) )
      {
        $socialUser = $this->socialUserManager->create( );
        $socialUser->setSocialId( $gData->getId( ) );
        $socialUser->setUser( $user );
        $socialUser->setSocialName( 'GOOGLE' );
        $user->addGroup( $this->objectManager->getRepository( "BITUserBundle:Group" )->findOneBy( array( "name" => "GOOGLE" ) ) );
        $this->objectManager->persist( $socialUser );
        $this->objectManager->flush( );
      }
    }

    if ( empty( $user ) )
    {
      throw new UsernameNotFoundException( 'The user is not authenticated on google');
    }

    return $user;
  }

  public function refreshUser( UserInterface $user )
  {
    if ( !$this->supportsClass( get_class( $user ) ) )
    {
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not supported.', get_class( $user ) ));
    }

    $entity = $this->objectManager->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "user" => $user->getId( ), "social_name" => "GOOGLE" ) );

    if ( !is_object( $entity ) )
    {
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not google.', get_class( $user ) ));
    }

    return $entity->getUser( );
  }
}
