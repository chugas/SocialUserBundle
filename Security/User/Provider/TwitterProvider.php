<?php

namespace BIT\BITSocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\GoogleBundle\Google\GoogleSessionPersistence;
use BIT\BITSocialUserBundle\Controller\SocialUserControllerService;
use BIT\BITSocialUserBundle\Entity\User as SocialUser;
use \TwitterOAuth;

class TwitterProvider implements UserProviderInterface
{
  /**
   * @var \Twitter
   */
  protected $twitter_oauth;
  protected $validator;
  protected $session;
  protected $socialUserManager;
  protected $objectManager;

  public function __construct( TwitterOAuth $twitter_oauth, Validator $validator, Session $session, UserManager $userManager, SocialUserControllerService $socialUserManager )
  {
    $this->twitter_oauth = $twitter_oauth;
    $this->validator = $validator;
    $this->session = $session;
    $this->userManager = $userManager;
    $this->socialUserManager = $socialUserManager;
    $this->objectManager = $this->socialUserManager->getObjectManager( );
  }

  public function supportsClass( $class )
  {
    return $this->userManager->supportsClass( $class );
  }

  public function findUserByTwitterId( $twitterID )
  {
    // TODO: search by name, and other data
    $user = null;

    $entity = $this->socialUserManager->getRepository( )->findOneBy( array( "social_id" => $twitterID ) );

    if ( is_object( $entity ) )
      $user = $entity->getUser( );

    return $user;
  }

  public function loadUserByUsername( $username )
  {
    $this->twitter_oauth->setOAuthToken( $this->session->get( 'access_token' ), $this->session->get( 'access_token_secret' ) );

    try
    {
      $info = $this->twitter_oauth->get( 'account/verify_credentials' );
    }
    catch ( Exception $e )
    {
      $info = null;
    }

    if ( !empty( $info ) )
    {
      $user = $this->findUserByTwitterId( $info->id );

      if ( empty( $user ) )
      {
        $user = $this->userManager->createUser( );
        $user->setEnabled( true );
        $user->setPassword( '' );
        $user->setEmail( '' );
        $user->addGroup( $this->objectManager->getRepository( "BITUserBundle:Group" )->findOneBy( array( "name" => "USER" ) ) );
      }

      if ( isset( $info->id ) )
      {
        if ( !$user->getUsername( ) )
          $user->setUsername( $info->screen_name );

        $user->addGroup( $this->objectManager->getRepository( "BITUserBundle:Group" )->findOneBy( array( "name" => "TWITTER" ) ) );
      }

      if ( isset( $info->name ) )
      {
        $nameAndLastNames = explode( " ", $info->name );
        if ( count( $nameAndLastNames ) > 1 )
        {
          $user->setFirstname( $nameAndLastNames[0] );
          $user->setLastname( $nameAndLastNames[1] );
          $user->setLastname( ( count( $nameAndLastNames ) > 2 ) ? $nameAndLastNames[1] : "" );
        }
        else
        {
          $user->setFirstname( $nameAndLastNames[0] );
          $user->setLastname( "" );
          $user->setLastname2( "" );
        }
      }

      $this->userManager->updateUser( $user );

      $socialUser = $this->objectManager->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "social_id" => $info->id ) );

      if ( isset( $info->id ) && !is_object( $socialUser ) )
      {
        $socialUser = $this->socialUserManager->create( );
        $socialUser->setSocialId( $info->id );
        $socialUser->setUser( $user );
        $socialUser->setSocialName( 'TWITTER' );
        $user->addGroup( $this->objectManager->getRepository( "BITUserBundle:Group" )->findOneBy( array( "name" => "TWITTER" ) ) );
        $this->objectManager->persist( $socialUser );
        $this->objectManager->flush( );
      }
    }

    if ( empty( $user ) )
    {
      throw new UsernameNotFoundException( 'The user is not authenticated on twitter');
    }

    return $user;
  }

  public function refreshUser( UserInterface $user )
  {
    if ( !$this->supportsClass( get_class( $user ) ) )
    {
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not supported.', get_class( $user ) ));
    }

    $entity = $this->objectManager->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "user" => $user->getId( ), "social_name" => "TWITTER" ) );

    if ( !is_object( $entity ) )
    {
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not twitter.', get_class( $user ) ));
    }

    return $entity->getUser( );
  }
}
