<?php

/**
 * @file
 * Provide views data for history_legacy.module.
 */

/**
 * Implements hook_views_data().
 *
 * @todo: Fix views implementation.
 */
function entity_history_views_data() {
  // Entity-History table

  // We're actually defining a specific instance of the table, so let's
  // alias it so that we can later add the real table for other purposes if we
  // need it.
  $data['entity_history']['table']['group']  = t('Content');

  // Explain how this table joins to others.
  // @todo: We need to do this for every entity_type.
  $entity_type_manager = \Drupal::getContainer()->get('entity_type.manager');
  $debug = 1;


  $data['entity_history']['table']['join'] = [];
  foreach ($entity_type_manager->getDefinitions() as $entity_type) {
  $debug = 1;
    if ($entity_type->getGroup() == 'content') {
      $data_table = $entity_type->getDataTable();
      $data['entity_history']['table']['join'][$data_table] = [
        'table' => 'entity_history',
        'left_field' => $entity_type->getKey('id'),
        'field' => 'entity_id',
        'extra' => [
          ['field' => 'entity_type', 'value' => $entity_type->id()],
          ['field' => 'uid', 'value' => '***CURRENT_USER***', 'numeric' => TRUE],
        ],
      ];
    }
  }

//  $data['entity_history']['table']['join'] = [
//     // Directly links to node table.
//    // @todo: Every entity type here.
//    'node_field_data' => [
//      'table' => 'entity_history',
//      'left_field' => 'nid',
//      'field' => 'entity_id',
//      'extra' => [
//        ['field' => 'entity_type', 'value' => 'node'],
//        ['field' => 'uid', 'value' => '***CURRENT_USER***', 'numeric' => TRUE],
//      ],
//    ],
//  ];

  $data['entity_history']['timestamp'] = [
    'title' => t('Has new content'),
    'field' => [
      'id' => 'entity_history_user_timestamp',
      'help' => t('Show a marker if the content is new or updated.'),
    ],
    'filter' => [
      'help' => t('Show only content that is new or updated.'),
      'id' => 'entity_history_user_timestamp',
    ],
  ];

  return $data;
}
