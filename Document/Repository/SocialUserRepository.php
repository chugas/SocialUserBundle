<?php

namespace BIT\SocialUserBundle\Document\Repository;
use Doctrine\ODM\MongoDB\DocumentRepository;

class SocialUserRepository extends DocumentRepository
{
  
  public function findByIds( $ids )
  {
    if ( is_array( $ids ) && !empty( $ids ) )
    {
      $qb = $this->createQueryBuilder( );
      $qb->field( 'social_id' )->in( $ids );
      return $qb->getQuery( )->execute( );
    }
    return array( );
  }
}
