<?php

namespace BIT\SocialUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class SocialUserController extends Controller
{
  
  public function getOnlineUser( )
  {
    $token = $this->get( 'security.context' )->getToken( "user" );
    if ( $token )
    {
      $user = $token->getUser( $token );
      if ( is_object( $user ) )
        return $user;
    }
    return null;
  }
  
  /**
   * @Route("/login_check", name="login_check")
   * @Method("GET")
   */
  
  public function checkAction( )
  {
    return new Response('');
  }
}
