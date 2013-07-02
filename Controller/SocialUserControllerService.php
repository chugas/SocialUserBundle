<?php

namespace BIT\SocialUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SocialUserControllerService extends Controller
{
  private $functionName;
  private $defaultGroup;
  private $setGroupAsSocialName;
  private $mappingFQCN;
  
  public function __construct( $config )
  {
    $this->functionsName = $config[ 'functionsName' ];
    $this->defaultGroup = $config[ 'defaultGroup' ];
    $this->setGroupAsSocialName = $config[ 'setGroupAsSocialName' ];
    $this->mappingFQCN = $config[ 'mappingFQCN' ];
  }
  
  public function getFunctionsName( )
  {
    return $this->functionsName;
  }
  
  public function getFunctionName( $name )
  {
    return $this->functionsName[ $name ];
  }
  
  public function getDefaultGroup( )
  {
    return $this->defaultGroup;
  }
  
  public function getSetGroupAsSocialName( )
  {
    return $this->setGroupAsSocialName;
  }
  
  public function create( )
  {
    if ( empty( $this->mappingClassFQCN ) )
      switch ( $this->container->getParameter( 'fos_user.storage' ) )
      {
        case 'orm':
          {
            $this->mappingFQCN = 'BIT\\SocialUserBundle\\Entity\\User';
            break;
          }
        case 'mongodb':
          {
            $this->mappingFQCN = 'BIT\\SocialUserBundle\\Document\\User';
            break;
          }
      }
    
    return new $this->mappingFQCN;
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
