<?php

namespace Drupal\entity_history;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an interface to store and retrieve a last view timestamp of entities.
 */
interface EntityHistoryRepositoryInterface {

  /**
   * Retrieves the timestamp for the current user's last view of the entities.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $entity_ids
   *   The entity IDs.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account to get the history for.
   *
   * @return array
   *   Array of timestamps keyed by entity ID. If a entity has been previously
   *   viewed by the user, the timestamp in seconds of when the last view
   *   occurred; otherwise, zero.
   */
  public function getLastViewed($entity_type, $entity_ids, AccountInterface $account);

  /**
   * Updates 'last viewed' timestamp of the entity for the user account.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that history should be updated.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account to update the history for.
   */
  public function updateLastViewed(EntityInterface $entity, AccountInterface $account);

  /**
   * Purges outdated history.
   */
  public function purge();

  /**
   * Deletes history of the user account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account to purge history.
   */
  public function deleteByUser(AccountInterface $account);

  /**
   * Deletes history for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that history should be deleted.
   */
  /**
   * Resets the static cache.
   */
  public function resetCache();

}