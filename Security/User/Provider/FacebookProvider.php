<?php
namespace BIT\BITSocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use BIT\BITSocialUserBundle\Controller\SocialUserControllerService;
use BIT\BITSocialUserBundle\Security\User\Provider\SocialUserProvider;
use \BaseFacebook;
use \FacebookApiException;

class FacebookProvider extends SocialUserProvider
{
  /**
   * @var \Facebook
   */
  protected $facebook;
  
  public function __construct( BaseFacebook $facebook, Validator $validator, UserManager $userManager,
      SocialUserControllerService $socialUserManager )
  {
    parent::__construct( $validator, $userManager, $socialUserManager );
    $this->facebook = $facebook;
    $this->providerName = "Facebook";
  }
  
  protected function getData( )
  {
    try
    {
      $data = $this->facebook->api( '/me' );
    }
    catch ( FacebookApiException $e )
    {
      $data = null;
    }
    
    return $data;
  }
}
