<?php

namespace BIT\SocialUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SocialUserController extends Controller
{
  
  /**
   * Lists all User entities.
   *
   * @Route("/login_check", name="bit_socialuser_login_check")
   * @Method("GET")
   */
  
  public function checkAction( )
  {
    return new Response( '');
  }
  
  /**
   * Lists all User entities.
   *
   * @Route("/setFBAccessToken", name="bit_socialuser_set_fb_at")
   */
  
  public function setFBAccessTokenAction( )
  {
    $accessToken = $this->getRequest( )->get( "accessToken" );
    $this->get( "session" )->set( "fb.accessToken", $accessToken );
    $this->get( "bit_facebook.api" )->setAccessToken( $accessToken );
    return new JsonResponse( array( "accessToken" => $accessToken ));
  }
}
