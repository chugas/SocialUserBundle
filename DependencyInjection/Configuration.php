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
        ->arrayNode( 'functions_name' )->canBeUnset( )->addDefaultsIfNotSet( )->children( ) // childrens
        ->scalarNode( 'firstname' )->cannotBeEmpty( )->defaultValue( 'setFirstname' )->end( ) // first name
        ->scalarNode( 'lastname' )->cannotBeEmpty( )->defaultValue( 'setLastname' )->end( ) // last name
        ->scalarNode( 'lastname2' )->defaultValue( 'setLastname2' )->end( ) // last name 2
        ->scalarNode( 'email' )->cannotBeEmpty( )->defaultValue( 'setEmail' )->end( ) // email
        ->scalarNode( 'username' )->cannotBeEmpty( )->defaultValue( 'setUsername' )->end( ) // username
        ->scalarNode( 'photo' )->defaultValue( 'setPhoto' )->end( ) // photo
        ->end( )->end( ) // end array
        ->variableNode( 'default_values' )->end( ) // default values
        ->scalarNode( 'default_role' )->cannotBeEmpty( )->defaultValue( 'ROLE_USER' )->end( ) // default group
        ->booleanNode( 'set_role_as_social_name' )->cannotBeEmpty( )->defaultValue( true )->end( ) // group as social name
        ->scalarNode( 'mapping_fqcn' )->defaultNull( )->end( ) // class fqcn
        ->booleanNode( 'use_google' )->defaultTrue( )->end( ) // use google
        ->booleanNode( 'use_facebook' )->defaultTrue( )->end( ) // use facebook
        ->booleanNode( 'use_twitter' )->defaultTrue( )->end( ) // use twitter
        ->end( );
    
    return $treeBuilder;
  }
}
