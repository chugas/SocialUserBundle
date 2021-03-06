<?php

namespace BIT\SocialUserBundle\DependencyInjection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class BITSocialUserExtension extends Extension
{
  
  public function load( array $configs, ContainerBuilder $container )
  {
    $processor = new Processor( );
    $configuration = new Configuration( );
    $config = $processor->processConfiguration( $configuration, $configs );
    
    $defaults = ( array_key_exists( "default_values", $config ) ) ? $config[ "default_values" ] : array( );
    
    $container->setParameter( 'bit_social_user.functionsName', $config[ "functions_name" ] );
    $container->setParameter( 'bit_social_user.defaultValues', $defaults );
    $container->setParameter( 'bit_social_user.defaultRole', $config[ "default_role" ] );
    $container->setParameter( 'bit_social_user.setRoleAsSocialName', $config[ "set_role_as_social_name" ] );
    $container->setParameter( 'bit_social_user.mappingFQCN', $config[ "mapping_fqcn" ] );
    
    $loader = new YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config'));
    $loader->load( 'services.yml' );
    
    if ( $config[ 'use_google' ] )
      $loader->load( 'google.yml' );
    
    if ( $config[ 'use_facebook' ] )
      $loader->load( 'facebook.yml' );
    
    if ( $config[ 'use_twitter' ] )
      $loader->load( 'twitter.yml' );
  }
  
  public function getAlias( )
  {
    return 'bit_social_user';
  }
}
