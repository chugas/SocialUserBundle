<?php

namespace BIT\SocialUserBundle\Entity\Repository;
use Doctrine\ORM\EntityRepository;

class SocialUserRepository extends EntityRepository
{
  
  public function findByIds( $ids )
  {
    if ( is_array( $ids ) && !empty( $ids ) )
    {
      $idsString = '';
      foreach ( $ids as $friendId )
        $idsString .= "'" . $friendId . "',";
      $idsString = substr( $idsString, 0, strlen( $idsString ) - 1 );
      
      $qb = $this->getEntityManager( )->createQueryBuilder( );
      
      $qb->select( 'su' )->from( 'UserBundle:SocialUser', 'su' );
      $where = $qb->expr( )->in( 'su.social_id', $idsString );
      $qb->where( $where );
      return $qb->getQuery( )->getResult( );
    }
    return array( );
  }
}
