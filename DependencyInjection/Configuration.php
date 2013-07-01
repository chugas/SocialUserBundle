<?php

namespace BIT\SocialUserBundle\DependencyInjection;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
  
  public function getConfigTreeBuilder( )
  {
    $treeBuilder = new TreeBuilder( );
    $rootNode = $treeBuilder->root( 'bit_social_user' );
    
    $rootNode->children( ) // childrens
        ->arrayNode( 'functions_name' )->canBeUnset( )->addDefaultsIfNotSet()->children( ) // childrens
        ->scalarNode( 'name' )->cannotBeEmpty( )->defaultValue( 'setFirstname' )->end( ) // first name
        ->scalarNode( 'lastname' )->cannotBeEmpty( )->defaultValue( 'setLastname' )->end( ) // last name
        ->scalarNode( 'lastname2' )->defaultValue( 'setLastname2' )->end( ) // last name 2
        ->scalarNode( 'email' )->cannotBeEmpty( )->defaultValue( 'setEmail' )->end( ) // email
        ->scalarNode( 'username' )->cannotBeEmpty( )->defaultValue( 'setUsername' )->end( ) // username
        ->scalarNode( 'photo' )->defaultValue( 'setPhoto' )->end( ) // photo
        ->end( )->end( ) // end array
        ->scalarNode( 'default_group' )->cannotBeEmpty( )->defaultValue( 'USER' )->end( ) // default group
        ->booleanNode( 'set_group_as_social_name' )->cannotBeEmpty( )->defaultValue( true )->end( ) // group as social name
        ->end( );
    
    return $treeBuilder;
  }
}
