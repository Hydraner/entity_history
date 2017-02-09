<?php

namespace Drupal\entity_history;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the comment service to use entity_history.
 */
class EntityHistoryServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Alter comment.manager service.
    $definition = $container->getDefinition('comment.manager');
    $definition->setClass('Drupal\entity_history\CommentManager');
    $definition->addArgument(new Reference('entity_history.repository'));
  }

}
