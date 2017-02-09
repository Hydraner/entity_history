<?php

namespace Drupal\entity_history\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\entity_history\EntityHistoryRepositoryInterface;

/**
 * Returns responses for History module routes.
 */
class EntityHistoryController extends ControllerBase {

  /**
   * The history repository service.
   *
   * @var \Drupal\entity_history\EntityHistoryRepositoryInterface
   */
  protected $entityHistoryRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an EntityHistoryController object.
   *
   * @param \Drupal\entity_history\EntityHistoryRepositoryInterface $entity_history_repository
   *   The entity history repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityHistoryRepositoryInterface $entity_history_repository, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityHistoryRepository = $entity_history_repository;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_history.repository'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns a set of entities' last read timestamps.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request of the page.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function getEntityReadTimestamps(Request $request) {
    $account = $this->currentUser();
    if ($account->isAnonymous()) {
      throw new AccessDeniedHttpException();
    }

    $entities = $request->request->get('entities');
    if (!isset($entities)) {
      throw new NotFoundHttpException();
    }

    $return = [];
    foreach (Json::decode($entities) as $entity_type => $entity_ids) {
      $return[$entity_type] = $this->entityHistoryRepository->getLastViewed($entity_type, $entity_ids, $account);
    }
    return new JsonResponse($return);
  }

  /**
   * Marks an entity as read by the current user right now.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request of the page.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  public function readEntity(Request $request) {
    $account = $this->currentUser();
    if ($account->isAnonymous()) {
      throw new AccessDeniedHttpException();
    }

    $entity_type = $request->request->get('entity_type');
    $entity_id = $request->request->get('entity_id');

    $controller = $this->entityTypeManager->getStorage($entity_type);
    $entity = $controller->load($entity_id);

    // Update the entity_history table, stating that this user viewed this
    // entity.
    $this->entityHistoryRepository->updateLastViewed($entity, $account);

    // Get the updated history.
    $history = $this->entityHistoryRepository->getLastViewed($entity_type, array($entity->id()), $account);
    return new JsonResponse($history[$entity->id()]);
  }

}
