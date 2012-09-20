<?php

namespace BIT\BITSocialUserBundle\Listeners;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

class SocialUserListener
{
  private $session;
  private $sc;
  private $em;

  public function __construct( Session $session, SecurityContext $sc, EntityManager $em )
  {
    $this->session = $session;
    $this->sc = $sc;
    $this->em = $em;
  }

  public function register( EventDispatcher $dispatcher, $priority = 0 )
  {
    $dispatcher->connect( 'social_user.event', array( $this, 'handle' ), $priority );
  }

  public function getOnlineUser( )
  {
    $token = $this->sc->getToken( "user" );
    if ( $token )
    {
      $user = $token->getUser( $token );
      if ( is_object( $user ) )
        return $user;
    }
    return null;
  }

  public function onKernelRequest( GetResponseEvent $event )
  {
    if ( is_object( $this->sc->getToken( ) ) && $this->sc->isGranted( "ROLE_TWITTER" ) && $this->getOnlineUser( )->getEmail( ) === ""
        && substr_count( $event->getRequest( )->getRequestUri( ), "/security/email" ) === 0 && substr_count( $event->getRequest( )->getRequestUri( ), "/security/confirm-email" ) === 0 )
    {
      $response = new Response( );

      if ( $this->session->get( "confirm" ) )
        $response->headers->set( "location", $event->getRequest( )->getUriForPath( "/security/confirm-email" ) );
      else
      {
        $user = $this->getOnlineUser( );
        if ( !empty( $user ) && strcmp( $user->getEmail( ), "" ) === 0 )
        {
          $response->headers->set( "location", $event->getRequest( )->getUriForPath( "/security/email" ) );
        }
      }

      $event->setResponse( $response );
    }
  }
}
