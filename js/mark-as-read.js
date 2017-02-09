/**
 * @file
 * Marks the nodes listed in drupalSettings.history.nodesToMarkAsRead as read.
 *
 * Uses the History module JavaScript API.
 *
 * @see Drupal.history
 */

(function (window, Drupal, drupalSettings) {

  'use strict';

  // When the window's "load" event is triggered, mark all enumerated nodes as
  // read. This still allows for Drupal behaviors (which are triggered on the
  // "DOMContentReady" event) to add "new" and "updated" indicators.
  window.addEventListener('load', function () {
    if (drupalSettings.entityHistory && drupalSettings.entityHistory.entitiesToMarkAsRead) {
      for (var entity_type in drupalSettings.entityHistory.entitiesToMarkAsRead) {
        for(var entity_id in drupalSettings.entityHistory.entitiesToMarkAsRead[entity_type]) {
          Drupal.entityHistory.markAsRead(entity_id, entity_type);
        }
      }
    }
  });

})(window, Drupal, drupalSettings);
