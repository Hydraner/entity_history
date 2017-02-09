<?php

namespace Drupal\entity_history_forum;

use Drupal\Core\Session\AccountInterface;
use Drupal\forum\ForumManager;

/**
 * Provides and adjusts a forum manager service using entity history.
 */
class EntityHistoryForumManager extends ForumManager {

  /**
   * {@inheritdoc}
   */
  protected function lastVisit($nid, AccountInterface $account) {
    if (empty($this->history[$nid])) {
      $result = $this->connection->select('entity_history', 'eh')
        ->fields('eh', array('entity_id', 'timestamp'))
        ->condition('uid', $account->id())
        ->condition('entity_type', 'node')
        ->execute();
      foreach ($result as $t) {
        $this->history[$t->entity_id] = $t->timestamp > ENTITY_HISTORY_READ_LIMIT ? $t->timestamp : ENTITY_HISTORY_READ_LIMIT;
      }
    }
    return isset($this->history[$nid]) ? $this->history[$nid] : ENTITY_HISTORY_READ_LIMIT;
  }

  /**
   * {@inheritdoc}
   */
  public function unreadTopics($term, $uid) {
    $query = $this->connection->select('forum', 'f');
    $query->join('comment_entity_statistics', 'ces', 'ces.entity_id = f.nid AND ces.entity_type = :entity_type', [':entity_type' => 'node']);
    $query->fields('ces', ['last_comment_timestamp'])
      ->condition('f.tid', $term)
      ->condition('ces.cid', 0, '!=')
      ->condition('ces.last_comment_timestamp', ENTITY_HISTORY_READ_LIMIT, '>');
    $query->join('entity_history', 'eh', 'eh.entity_id = ces.entity_id AND eh.entity_type = :entity_type', [':entity_type' => 'node']);
    $query->fields('eh', ['timestamp']);
    $results = $query->execute()->fetchAll();

    foreach ($results as $result) {
      if ($result->last_comment_timestamp > $result->timestamp) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
