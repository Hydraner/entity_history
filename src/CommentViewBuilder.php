<?php

namespace Drupal\entity_history;

use Drupal\comment\CommentViewBuilder as SourceCommentViewBuilder;

/**
 * View builder handler for comments.
 */
class CommentViewBuilder extends SourceCommentViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    /** @var \Drupal\comment\CommentInterface[] $entities */
    if (empty($entities)) {
      return;
    }
    parent::buildComponents($build, $entities, $displays, $view_mode);

    // Rewrite the libraries information.
    foreach ($entities as $id => $entity) {
      // Commented entities already loaded after self::getBuildDefaults().
      $commented_entity = $entity->getCommentedEntity();

      $build[$id]['#attached']['library'] = [];
      $build[$id]['#attached']['library'][] = 'comment/drupal.comment-by-viewer';
      if ($this->moduleHandler->moduleExists('entity_history') && $this->currentUser->isAuthenticated()) {
        $build[$id]['#attached']['library'][] = 'entity_history/comment-new-indicator';

        // Embed the metadata for the comment "new" indicators on this node.
        $build[$id]['history'] = [
          '#lazy_builder' => [
            'entity_history_attach_timestamp',
            [
              'entity_type' => $commented_entity->getEntityTypeId(),
              'entity_id' => $commented_entity->id()
            ],
          ],
          '#create_placeholder' => TRUE,
        ];
      }
    }
  }

}
