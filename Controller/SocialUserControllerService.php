<?php

namespace BIT\BITSocialUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BIT\BITSocialUserBundle\Form\EmailType;
use BIT\BITSocialUserBundle\Form\MongoEmailType;

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

  public function getType( )
  {
    switch ( $this->container->getParameter( 'fos_user.storage' ) )
    {
      case 'orm':
        {
          return new EmailType( );
        }
      case 'mongodb':
        {
          return new MongoEmailType( );
        }
    }
  }
}
