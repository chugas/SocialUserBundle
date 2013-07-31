<?php

namespace BIT\SocialUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SocialUserControllerService extends Controller
{
  private $functionName;
  private $defaultValues;
  private $defaultRole;
  private $setRoleAsSocialName;
  private $mappingFQCN;
  private $mappingBundle;
  private $mappingClass;
  
  public function __construct( $config )
  {
    $this->functionsName = $config[ 'functionsName' ];
    $this->defaultValues = $config[ 'defaultValues' ];
    $this->defaultRole = $config[ 'defaultRole' ];
    $this->setRoleAsSocialName = $config[ 'setRoleAsSocialName' ];
    $this->mappingFQCN = $config[ 'mappingFQCN' ];
  }
  
  private function computeMappingNames( )
  {
    if ( empty( $this->mappingFQCN ) )
      switch ( $this->container->getParameter( 'fos_user.storage' ) )
      {
        case 'orm':
          {
            $this->mappingFQCN = 'BIT\\SocialUserBundle\\Entity\\SocialUser';
            break;
          }
        case 'mongodb':
          {
            $this->mappingFQCN = 'BIT\\SocialUserBundle\\Document\\SocialUser';
            break;
          }
      }
    
    if ( empty( $this->mappingBundle ) || empty( $this->mappingClass ) )
    {
      $data = explode( "#", str_replace( '\\', "#", $this->mappingFQCN ) );
      
      $vendorName = "";
      foreach ( $data as $str )
      {
        if ( strpos( $str, "Bundle" ) )
        {
          $this->mappingBundle = $vendorName . $str;
          break;
        }
        else
          $vendorName .= ucfirst( $str );
      }
      
      $this->mappingClass = $data[ count( $data ) - 1 ];
    }
  }
  
  public function getFunctionsName( )
  {
    return $this->functionsName;
  }
  
  public function getFunctionName( $name )
  {
    return $this->functionsName[ $name ];
  }
  
  public function getDefaultValues( )
  {
    return $this->defaultValues;
  }
  
  public function getDefaultRole( )
  {
    return $this->defaultRole;
  }
  
  public function getSetRoleAsSocialName( )
  {
    return $this->setRoleAsSocialName;
  }
  
  public function create( )
  {
    $this->computeMappingNames( );
    
    $fqcn = $this->mappingFQCN;
    return new $fqcn( );
  }
  
  public function getObjectManager( )
  {
    $managerName = $this->container->getParameter( 'fos_user.model_manager_name' );
    
    switch ( $this->container->getParameter( 'fos_user.storage' ) )
    {
      case 'orm':
        {
          return $this->getDoctrine( )->getManager( $managerName );
        }
      case 'mongodb':
        {
          return $this->get( 'doctrine_mongodb' )->getManager( $managerName );
        }
    }
  }
  
  private function getFQCN( )
  {
    $this->computeMappingNames( );
    return $this->mappingBundle . ":" . $this->mappingClass;
  }
  
  public function getRepository( )
  {
    return $this->getObjectManager( )->getRepository( $this->getFQCN( ) );
  }
  
  public function getQueryBuilder( )
  {
    return $this->getObjectManager( )->createQueryBuilder( $this->getFQCN( ) );
  }
  
  public function getAllFacebookFriends( )
  {
    $rm = $this->getDoctrine( );
    $sc = $this->get( 'security.context' );
    $authUser = $sc->getToken( )->getUser( );
    $facebookFriends = array( );
    
    if ( true === $sc->isGranted( 'ROLE_FACEBOOK' ) )
    {
      $facebook = $this->get( 'bit_facebook.api' );
      
      // get facebook friends
      $parameters = array( );
      $parameters[ 'user' ] = $authUser->getId( );
      $parameters[ 'social_name' ] = "FACEBOOK";
      $socialUser = $rm->getRepository( "UserBundle:SocialUser" )->findOneBy( $parameters );
      $friends = $facebook->getFriends( $socialUser->getSocialId( ), null, "first_name" );
      
      foreach ( $friends as $friend )
      {
        $friend[ 'photo' ] = "https://graph.facebook.com/" . $friend[ 'uid' ] . "/picture";
        $facebookFriends[ $friend[ 'uid' ] ] = $friend;
      }
    }
    
    return $facebookFriends;
  }
  
  public function getSystemFacebookFriends( )
  {
    $allFriends = $this->getAllFacebookFriends( );
    return $this->getRepository( )->findByIds( array_keys( $allFriends ) );
  }
  
  public function getAllGoogleFriends( )
  {
    $rm = $this->getDoctrine( );
    $sc = $this->get( 'security.context' );
    $authUser = $sc->getToken( )->getUser( );
    $googleFriends = array( );
    
    if ( true === $sc->isGranted( 'ROLE_GOOGLE' ) )
    {
      $google = $this->get( 'bit_google.contact' );
      $googleFriends = $google->getContacts( );
    }
    
    return $googleFriends;
  }
  
  public function getSystemGoogleFriends( )
  {
    $allFriends = $this->getAllGoogleFriends( );
    return $this->getRepository( )->findByEmails( array_keys( $allFriends ) );
  }
}
