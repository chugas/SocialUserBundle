<?php
namespace BIT\BITSocialUserBundle\Security\User\Provider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\GoogleBundle\Google\GoogleSessionPersistence;
use BIT\BITSocialUserBundle\Entity\User as SocialUser;

class GoogleProvider implements UserProviderInterface
{
  /**
   * @var \GoogleApi
   */
  protected $googleApi;
  protected $userManager;
  protected $validator;
  protected $em;

  public function __construct( GoogleSessionPersistence $googleApi, UserManager $userManager, Validator $validator, EntityManager $em )
  {
    $this->googleApi = $googleApi;
    $this->userManager = $userManager;
    $this->validator = $validator;
    $this->em = $em;
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
      $entity = $this->em->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "id" => $gId ) );

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

    $user = $this->findUserByGIdOrEmail( $username, isset( $gData['email'] ) ? $gData['email'] : null );

    if ( !empty( $gData ) )
    {
      if ( empty( $user ) )
      {
        $user = $this->userManager->createUser( );
        $user->setPassword( '' );
        $user->setEnabled( true );
        $user->addGroup( $this->em->getRepository( "BIT\BITUserBundle\Entity\Group" )->findOneBy( array( "name" => "USER" ) ) );
      }

      if ( isset( $gData['name'] ) )
      {
        $nameAndLastNames = explode( " ", $gData['name'] );

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

      if ( isset( $gData['email'] ) )
      {
        $user->setEmail( $gData['email'] );
        $user->setUsername( $gData['email'] );
      }
      else
      {
        $user->setEmail( $gData['id'] . "@google.com" );
        $user->setUsername( $gData['id'] . "@google.com" );
      }

      if ( count( $this->validator->validate( $user, 'Google' ) ) )
      {
        // TODO: the user was found obviously, but doesnt match our expectations, do something smart
        throw new UsernameNotFoundException( 'The google user could not be stored');
      }

      $this->userManager->updateUser( $user );

      $socialUser = $this->em->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "id" => $gData['id'] ) );

      if ( isset( $gData['id'] ) && !is_object( $socialUser ) )
      {
        $socialUser = new SocialUser( );
        $socialUser->setId( $gData['id'] );
        $socialUser->setUser( $user );
        $socialUser->setSocialName( 'GOOGLE' );
        $user->addGroup( $this->em->getRepository( "BIT\BITUserBundle\Entity\Group" )->findOneBy( array( "name" => "GOOGLE" ) ) );
        $this->em->persist( $socialUser );
        $this->em->flush( );
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

    $entity = $this->em->getRepository( "BITSocialUserBundle:User" )->findOneBy( array( "user" => $user->getId( ), "social_name" => "GOOGLE" ) );

    if ( !is_object( $entity ) )
    {
      throw new UnsupportedUserException( sprintf( 'Instances of "%s" are not google.', get_class( $user ) ));
    }

    return $entity->getUser( );
  }
}
