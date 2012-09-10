<?php

namespace BIT\BITSocialUserBundle\Security\User\Provider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use BIT\BITSocialUserBundle\Entity\User as SocialUser;
use \TwitterOAuth;

class TwitterProvider implements UserProviderInterface
{
  /**
   * @var \Twitter
   */
  protected $twitter_oauth;
  protected $userManager;
  protected $validator;
  protected $session;
  protected $em;

  public function __construct( TwitterOAuth $twitter_oauth, $userManager, Validator $validator, Session $session, EntityManager $em )
  {
    $this->twitter_oauth = $twitter_oauth;
    $this->userManager = $userManager;
    $this->validator = $validator;
    $this->session = $session;
    $this->em = $em;
  }

  public function supportsClass( $class )
  {
    return $this->userManager->supportsClass( $class );
  }

  public function findUserByTwitterId( $twitterID )
  {
    // TODO: search by name, and other data
    $user = null;

    $entity = $this->em->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "id" => $twitterID ) );

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
        $user->addGroup( $this->em->getRepository( "BIT\BITUserBundle\Entity\Group" )->findOneBy( array( "name" => "USER" ) ) );
      }

      if ( isset( $info->id ) )
      {
        if ( !$user->getUsername( ) )
          $user->setUsername( $info->screen_name );

        $user->addGroup( $this->em->getRepository( "BIT\BITUserBundle\Entity\Group" )->findOneBy( array( "name" => "TWITTER" ) ) );
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

      $socialUser = $this->em->getRepository( "SocialUserBundle:User" )->findOneBy( array( "id" => $info->id ) );

      if ( isset( $info->id ) && !is_object( $socialUser ) )
      {
        $socialUser = new SocialUser( );
        $socialUser->setId( $info->id );
        $socialUser->setUser( $user );
        $socialUser->setSocialName( 'TWITTER' );
        $user->addGroup( $this->em->getRepository( "BIT\BITUserBundle\Entity\Group" )->findOneBy( array( "name" => "TWITTER" ) ) );
        $this->em->persist( $socialUser );
        $this->em->flush( );
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

    $entity = $this->em->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "user" => $user->getId( ), "social_name" => "TWITTER" ) );

    if ( !is_object( $entity ) )
    {
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not twitter.', get_class( $user ) ));
    }

    return $entity->getUser( );
  }
}
