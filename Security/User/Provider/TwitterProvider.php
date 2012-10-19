<?php

namespace BIT\BITSocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use BIT\BITSocialUserBundle\Controller\SocialUserControllerService;
use BIT\BITSocialUserBundle\Security\User\Provider\SocialUserProvider;
use BIT\BITSocialUserBundle\Entity\User as SocialUser;
use \TwitterOAuth;

class TwitterProvider extends SocialUserProvider
{
  /**
   * @var \Twitter
   */
  protected $twitter_oauth;
  protected $session;

  public function __construct( TwitterOAuth $twitter_oauth, Validator $validator, Session $session, UserManager $userManager, SocialUserControllerService $socialUserManager )
  {
    parent::__construct( $validator, $userManager, $socialUserManager );
    $this->session = $session;
    $this->twitter_oauth = $twitter_oauth;
    $this->providerName = "Twitter";
  }

  protected function getData( )
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

    $data = array( );
    $data['id'] = strtolower( $info->id );
    $data['email'] = sprintf( "%s@%s.com", $data['id'], strtolower( $this->providerName ) );
    $data['name'] = $info->name;

    return $data;
  }
}
