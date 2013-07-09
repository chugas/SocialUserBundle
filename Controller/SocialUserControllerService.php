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
            $this->mappingFQCN = 'BIT\\SocialUserBundle\\Entity\\User';
            break;
          }
        case 'mongodb':
          {
            $this->mappingFQCN = 'BIT\\SocialUserBundle\\Document\\User';
            break;
          }
      }
    
    $data = explode( "#", str_replace( '\\', "#", $this->mappingFQCN ) );
    
    $vendorName = "";
    foreach ( $data as $str )
    {
      if ( strpos( $str, "Bundle" ) )
      {
        $this->mappingBundle = $str;
        break;
      }
      else
        $vendorName .= ucfirst( $str );
    }
    
    $this->mappingClass = $data[ count( $data ) - 1 ];
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
  
  public function getRepository( )
  {
    $this->computeMappingNames( );
    return $this->getObjectManager( )->getRepository( $this->mappingBundle . ":" . $this->mappingClass );
  }
}
