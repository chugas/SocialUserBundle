<?php
namespace BIT\BITSocialUserBundle\Security\User\Provider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator;
use FOS\UserBundle\Doctrine\UserManager;
use FOS\GoogleBundle\Google\GoogleSessionPersistence;
use BIT\BITSocialUserBundle\Controller\SocialUserControllerService;
use BIT\BITSocialUserBundle\Security\User\Provider\SocialUserProvider;

class GoogleProvider extends SocialUserProvider
{
  /**
   * @var \GoogleApi
   */
  protected $googleApi;

  public function __construct( GoogleSessionPersistence $googleApi, Validator $validator, UserManager $userManager, SocialUserControllerService $socialUserManager )
  {
    parent::__construct( $validator, $userManager, $socialUserManager );
    $this->googleApi = $googleApi;
    $this->providerName = "Google";
  }

  protected function getData( )
  {
    try
    {
      $gData = $this->googleApi->getOAuth( )->userinfo->get( );
    }
    catch ( \Exception $e )
    {
      $gData = null;
    }

    $data = array( );
    $data['email'] = $gData->getEmail( );
    $data['name'] = $gData->getName( );
    $data['id'] = $gData->getId( );

    return $data;
  }
}
