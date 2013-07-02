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
    
    // set as array
    $container->setParameter( 'bit_social_user.functionsName', $config["functions_name"] );
    $container->setParameter( 'bit_social_user.defaultGroup', $config["default_group"] );
    $container->setParameter( 'bit_social_user.setGroupAsSocialName', $config["set_group_as_social_name"] );
    $container->setParameter( 'bit_social_user.mappingFQCN', $config["mapping_FQCN"] );
    
    $loader = new YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config'));
    $loader->load( 'services.yml' );
  }
  
  public function getAlias( )
  {
    return 'bit_social_user';
  }
}
