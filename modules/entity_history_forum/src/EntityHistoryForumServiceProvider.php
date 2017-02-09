<?php

namespace Drupal\entity_history_forum;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the forum service to use entity history instead of history.
 */
class EntityHistoryForumServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Alter forum_manager service.
    $definition = $container->getDefinition('forum_manager');
    $definition->setClass('Drupal\entity_history_forum\EntityHistoryForumManager');
  }

}
