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
function history_legacy_views_data() {
//  // Entity-History table
//
//  // We're actually defining a specific instance of the table, so let's
//  // alias it so that we can later add the real table for other purposes if we
//  // need it.
//  $data['entity_history']['table']['group']  = t('Content');
//
//  // Explain how this table joins to others.
//  // @todo: We need to do this for every entity_type.
//  $data['entity_history']['table']['join'] = [
//     // Directly links to node table.
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
//
//  $data['entity_history']['timestamp'] = [
//    'title' => t('Has new content'),
//    'field' => [
//      'id' => 'history_legacy_user_timestamp',
//      'help' => t('Show a marker if the content is new or updated.'),
//    ],
//    'filter' => [
//      'help' => t('Show only content that is new or updated.'),
//      'id' => 'history_legacy_user_timestamp',
//    ],
//  ];

  return [];
}
