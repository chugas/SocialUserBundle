<?php

namespace BIT\SocialUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SocialUserControllerService extends Controller
{
  private $functionName;
  private $defaultValues;
  private $defaultGroup;
  private $setGroupAsSocialName;
  private $mappingFQCN;
  private $mappingBundle;
  private $mappingClass;
  
  private function computeMappingNames( )
  {
    $data = explode( "#", str_replace( '\\', "#", $this->mappingFQCN ) );
    
    foreach ( $data as $str )
    {
      if ( strpos( $str, "Bundle" ) )
      {
        $this->mappingBundle = $str;
        break;
      }
    }
    
    $this->mappingClass = $data[ count( $data ) - 1 ];
  }
  
  public function __construct( $config )
  {
    $this->functionsName = $config[ 'functionsName' ];
    $this->defaultValues = $config[ 'defaultValues' ];
    $this->defaultGroup = $config[ 'defaultGroup' ];
    $this->setGroupAsSocialName = $config[ 'setGroupAsSocialName' ];
    $this->mappingFQCN = $config[ 'mappingFQCN' ];
    
    $this->computeMappingNames( );
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
    if ( empty( $this->mappingFQCN ) )
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
  
  public function getRepository( )
  {
    return $this->getObjectManager( )->getRepository( $this->mappingBundle . ":" . $this->mappingClass );
  }
}
