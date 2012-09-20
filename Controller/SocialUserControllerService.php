<?php

namespace BIT\BITSocialUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SocialUserControllerService extends Controller
{

  public function create( )
  {
    switch ( $this->container->getParameter( 'fos_user.storage' ) )
    {
      case 'orm':
        {
          $fqcn = 'BIT\\BITSocialUserBundle\\Entity\\User';
          break;
        }
      case 'mongodb':
        {
          $fqcn = 'BIT\\BITSocialUserBundle\\Document\\User';
          break;
        }
    }

    return new $fqcn( );
  }

  public function getObjectManager( )
  {
    switch ( $this->container->getParameter( 'fos_user.storage' ) )
    {
      case 'orm':
        {
          $doctrineServiceManager = 'doctrine.orm.entity_manager';
          break;
        }
      case 'mongodb':
        {
          $doctrineServiceManager = 'doctrine.odm.mongodb.document_manager';
          break;
        }
    }

    return $this->get( $doctrineServiceManager );
  }

  public function getRepository( )
  {
    return $this->getObjectManager( )->getRepository( "BITSocialUserBundle:User" );
  }
}
