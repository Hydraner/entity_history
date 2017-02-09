<?php

namespace Drupal\entity_history;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides history repository service.
 */
class EntityHistoryRepository implements EntityHistoryRepositoryInterface {

  use DependencySerializationTrait;

  /**
   * Array of history keyed by entity type and entity id.
   *
   * @var array
   */
  protected $history = [];

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs the history repository service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastViewed($entity_type, $entity_ids, AccountInterface $account) {
    $return = [];

    $entities_to_read = [];
    foreach ($entity_ids as $entity_id) {
      if (isset($this->history[$entity_type][$account->id()][$entity_id])) {
        $return[$entity_id] = $this->history[$entity_type][$account->id()][$entity_id];
      }
      else {
        $entities_to_read[$entity_id] = 0;
      }
    }

    if (empty($entities_to_read)) {
      return $return;
    }

    $result = $this->connection->select('entity_history', 'h')
      ->fields('h', ['entity_id', 'timestamp'])
      ->condition('uid', $account->id())
      ->condition('entity_type', $entity_type)
      ->condition('entity_id', array_keys($entities_to_read), 'IN')
      ->execute();

    foreach ($result as $row) {
      $entities_to_read[$row->entity_id] = (int) $row->timestamp;
    }
    if (!isset($this->history[$entity_type][$account->id()])) {
      $this->history[$entity_type][$account->id()] = [];
    }
    $this->history[$entity_type][$account->id()] += $entities_to_read;

    return $return + $entities_to_read;
  }

  /**
   * {@inheritdoc}
   */
  public function updateLastViewed(EntityInterface $entity, AccountInterface $account) {
    if ($account->isAuthenticated()) {
      $this->connection->merge('entity_history')
        ->keys([
          'uid' => $account->id(),
          'entity_id' => $entity->id(),
          'entity_type' => $entity->getEntityTypeId(),
        ])
        ->fields(['timestamp' => REQUEST_TIME])
        ->execute();
      // Update cached value.
      $this->history[$entity->getEntityTypeId()][$account->id()][$entity->id()] = REQUEST_TIME;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function purge() {
    $this->connection->delete('entity_history')
      ->condition('timestamp', ENTITY_HISTORY_READ_LIMIT, '<')
      ->execute();
    // Clean static cache.
    $this->resetCache();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByUser(AccountInterface $account) {
    $this->connection->delete('entity_history')
      ->condition('uid', $account->id())
      ->execute();
    // Clean static cache.
    $this->resetCache();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByEntity(EntityInterface $entity) {
    $this->connection->delete('entity_history')
      ->condition('entity_id', $entity->id())
      ->condition('entity_type', $entity->getEntityTypeId())
      ->execute();
    // Clean static cache.
    $this->resetCache();
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    $this->history = [];
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $vars = $this->__sleep();
    // Do not serialize static cache.
    unset($vars['entity_history']);
    return $vars;
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    $this->__wakeup();
    // Initialize static cache.
    $this->history = [];
  }

}