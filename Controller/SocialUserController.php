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
   */

  public function connectTwitterAction( )
  {
    $request = $this->get( 'request' );
    $twitter = $this->get( 'fos_twitter.service' );
    $authURL = $twitter->getLoginUrl( $request );
    $response = new RedirectResponse( $authURL);
    return $response;
  }

  /**
   * @Route("/email")
   * @Template()
   * @Secure(roles="ROLE_TWITTER")
   */

  public function emailAction( )
  {
    // get user
    $user = $this->getOnlineUser( );
    // create form
    $form = $this->get( 'form.factory' )->create( $this->get( 'bit_social_user' )->getType( ), $user );

    if ( $this->get( 'request' )->getMethod( ) == 'POST' )
    {
      $form->bindRequest( $this->get( 'request' ) );
      if ( $form->isValid( ) )
      {
        $user->setConfirmationToken( $this->get( 'fos_user.util.token_generator' )->generateToken( ) );

        $em = $this->getDoctrine( )->getEntityManager( );
        $em->flush( );

        $urlParameters = array( 'email' => $user->getEmail( ), 'token' => $user->getConfirmationToken( ) );
        $url = $this->get( 'router' )->generate( "_confirmEmail", $urlParameters, true );
        $parameters = array( 'name' => $user->getFullName( ), 'url' => $url, 'token' => $user->getConfirmationToken( ) );
        $body = $this->renderView( 'BITSocialUserBundle:SocialUser:confirmationEmail.html.twig', $parameters );
        $message = \Swift_Message::newInstance( );
        $message->setContentType( "text/html" );
        $message->setSubject( 'Email Confirmation' );
        $message->setFrom( 'no-reply@sunscious.com' );
        $message->setTo( $user->getEmail( ) );
        $message->setBody( $body );
        $this->get( 'mailer' )->send( $message );

        // set session flag to send user to email confirmation page until the email is confirmed
        $this->get( "session" )->set( "confirmation", true );

        return $this->get( 'templating' )->renderResponse( 'FOSUserBundle:Registration:checkEmail.html.twig', array( 'user' => $user, ) );
      }
    }

    return array( "form" => $form->createView( ) );
  }

  /**
   * @Route("/confirm-email", name="_confirmEmail")
   * @Template()
   * @Secure(roles="ROLE_TWITTER")
   */

  public function confirmEmailAction( )
  {
    // get user
    $user = $this->getOnlineUser( );
    if ( !empty( $user ) )
    {
      if ( !$this->get( "request" )->query->has( "email" ) && !$this->get( "request" )->query->has( "token" ) )
        return array( );
      else
      {
        $email = $this->get( "request" )->query->get( "email" );
        $token = $this->get( "request" )->query->get( "token" );
        if ( $user->getConfirmationToken( ) === $token )
        {
          $em = $this->getDoctrine( )->getEntityManager( );
          $repo = $em->getRepository( User::getFQCN( ) );
          // twitter id of the authenticated user
          $twitterID = $repo->find( $user->getId( ) )->getTwitterID( );
          // user to merge with
          $dbUserToMerge = $repo->findBy( array( "email" => $email ) );
          if ( is_array( $dbUserToMerge ) && !empty( $dbUserToMerge ) )
          {
            $dbUserToMerge = $dbUserToMerge[0];
            // mergin
            $dbUserToMerge->setTwitterID( $twitterID );
            // delete previous user
            $em->remove( $repo->find( $user->getId( ) ) );
          }
          else
            $repo->find( $user->getId( ) )->setEmail( $email );
          // flushing changes
          $em->flush( );
          // clean the session
          $this->get( "session" )->remove( "confirm" );
          // redirect to twitter login to refresh the user
          return new RedirectResponse( $this->get( 'router' )->generate( "connect_twitter" ));
        }
        else
          return new RedirectResponse( $this->get( 'router' )->generate( "_confirmEmail" ));
      }
    }

    return new RedirectResponse( $this->get( 'router' )->generate( "home" ));
  }
}
