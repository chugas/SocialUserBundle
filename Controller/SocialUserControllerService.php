<?php

namespace BIT\SocialUserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BIT\SocialUserBundle\Form\EmailType;
use BIT\SocialUserBundle\Form\MongoEmailType;

class SocialUserControllerService extends Controller
{
  
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
