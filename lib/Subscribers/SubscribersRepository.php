<?php

namespace MailPoet\Subscribers;

use MailPoet\Doctrine\Repository;
use MailPoet\Entities\SubscriberEntity;

/**
 * @method SubscriberEntity[] findBy(array $criteria, array $order_by = null, int $limit = null, int $offset = null)
 * @method SubscriberEntity|null findOneBy(array $criteria, array $order_by = null)
 * @method SubscriberEntity|null findOneById(mixed $id)
 * @method void persist(SubscriberEntity $entity)
 * @method void remove(SubscriberEntity $entity)
 */
class SubscribersRepository extends Repository {
  protected function getEntityClassName() {
    return SubscriberEntity::class;
  }

  /**
   * @return int
   */
  public function getTotalSubscribers() {
    $query = $this->entityManager
      ->createQueryBuilder()
      ->select('count(n.id)')
      ->from(SubscriberEntity::class, 'n')
      ->where('n.deleted_at IS NULL AND n.status IN (:statuses)')
      ->setParameter('statuses', [
        SubscriberEntity::STATUS_SUBSCRIBED,
        SubscriberEntity::STATUS_UNCONFIRMED,
        SubscriberEntity::STATUS_INACTIVE,
      ])
      ->getQuery();
    return (int)$query->getSingleScalarResult();
  }
}