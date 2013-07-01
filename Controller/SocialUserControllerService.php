<?php

namespace BIT\SocialUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SocialUserControllerService extends Controller
{
  private $functionName;
  private $defaultGroup;
  private $setGroupAsSocialName;
  
  public function __construct(Array $functionsName, $defaultGroup, $setGroupAsSocialName)
  {
    $this->functionsName = $functionsName;
    $this->defaultGroup = $defaultGroup;
    $this->setGroupAsSocialName = $setGroupAsSocialName;
  }
  
  public function getFunctionsName()
  {
    return $this->functionsName;
  }
  
  public function getFunctionName($name)
  {
    return $this->functionsName[$name];
  }
  
  public function getDefaultGroup()
  {
    return $this->defaultGroup;
  }
  
  public function getSetGroupAsSocialName()
  {
    return $this->setGroupAsSocialName;
  }
  
  public function create( )
  {
    switch ( $this->container->getParameter( 'fos_user.storage' ) )
    {
      case 'orm':
        {
          $fqcn = 'BIT\\SocialUserBundle\\Entity\\User';
          break;
        }
      case 'mongodb':
        {
          $fqcn = 'BIT\\SocialUserBundle\\Document\\User';
          break;
        }
    }
    
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
  
  public function getRepository( )
  {
    return $this->getObjectManager( )->getRepository( "BITSocialUserBundle:User" );
  }
}
