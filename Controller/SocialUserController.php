<?php

namespace BIT\SocialUserBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use BIT\SocialUserBundle\Entity\User;
use BIT\UserBundle\Form\EmailType;
use BIT\UserBundle\Form\ProfileType;

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
    
  }
}
