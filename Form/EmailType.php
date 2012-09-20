<?php

namespace BIT\BITUserBundle\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class EmailType extends AbstractType
{

  public function buildForm( FormBuilderInterface $builder, array $options )
  {
    $builder->add( 'email', 'email' );
  }

  public function getDefaultOptions( array $options )
  {
    return array( 'data_class' => 'BIT\BITUserBundle\Entity\User' );
  }

  public function getName( )
  {
    return "EmailType";
  }
}
