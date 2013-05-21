<?php

namespace BIT\BITSocialUserBundle\Controller;
use BIT\BITSocialUserBundle\Entity\User;
use BIT\BITUserBundle\Form\EmailType;
use BIT\BITUserBundle\Form\ProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;

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
   * @Route("/connectTwitter", name="connect_twitter")
   * @Secure(roles="IS_AUTHENTICATED_ANONYMOUSLY")
   */
  
  public function connectTwitterAction( )
  {
    $request = $this->get( 'request' );
    $twitter = $this->get( 'fos_twitter.service' );
    $authURL = $twitter->getLoginUrl( $request );
    $response = new RedirectResponse( $authURL);
    return $response;
  }
}
