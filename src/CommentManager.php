<?php

namespace Drupal\entity_history;

use Drupal\comment\CommentInterface;
use Drupal\comment\CommentManager as OriginalCommentManager;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Comment manager, using entity_history.
 */
class CommentManager extends OriginalCommentManager {

  /**
   * The entity_history repository.
   *
   * @var \Drupal\entity_history\EntityHistoryRepositoryInterface
   */
  private $entityHistoryRepository;

  /**
   * Construct the CommentManager object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\entity_history\EntityHistoryRepositoryInterface
   *   The entity_history repository.
   */
  public function __construct(EntityManagerInterface $entity_manager, QueryFactory $query_factory, ConfigFactoryInterface $config_factory, TranslationInterface $string_translation, UrlGeneratorInterface $url_generator, ModuleHandlerInterface $module_handler, AccountInterface $current_user, EntityHistoryRepositoryInterface $entity_history_repository) {
    $this->entityManager = $entity_manager;
    $this->queryFactory = $query_factory;
    $this->userConfig = $config_factory->get('user.settings');
    $this->stringTranslation = $string_translation;
    $this->urlGenerator = $url_generator;
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
    $this->entityHistoryRepository= $entity_history_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function getCountNewComments(EntityInterface $entity, $field_name = NULL, $timestamp = 0) {
    if ($this->currentUser->isAuthenticated()) {
      // Retrieve the timestamp at which the current user last viewed this entity.
      if (!$timestamp) {
        $comment_ids = $this->queryFactory->get('comment')->condition('entity_id', $entity->id())->execute();
        $history = $this->entityHistoryRepository->getLastViewed('comment', $comment_ids, $this->currentUser);
        // Take the latest timestamp as a reference.
        $timestamp = end($history);
      }
      $timestamp = ($timestamp > ENTITY_HISTORY_READ_LIMIT ? $timestamp : ENTITY_HISTORY_READ_LIMIT);

      // Use the timestamp to retrieve the number of new comments.
      $query = $this->queryFactory->get('comment')
        ->condition('entity_type', $entity->getEntityTypeId())
        ->condition('entity_id', $entity->id())
        ->condition('created', $timestamp, '>')
        ->condition('status', CommentInterface::PUBLISHED);
      if ($field_name) {
        // Limit to a particular field.
        $query->condition('field_name', $field_name);
      }

      return $query->count()->execute();
    }
    return FALSE;
  }

}
