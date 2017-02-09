/**
 * @file
 * JavaScript API for the History module, with client-side caching.
 *
 * May only be loaded for authenticated users, with the History module enabled.
 */

(function ($, Drupal, drupalSettings, storage) {

  'use strict';

  var currentUserID = parseInt(drupalSettings.user.uid, 10);

  // Any comment that is older than 30 days is automatically considered read,
  // so for these we don't need to perform a request at all!
  var thirtyDaysAgo = Math.round(new Date().getTime() / 1000) - 30 * 24 * 60 * 60;

  // Use the data embedded in the page, if available.
  var embeddedLastReadTimestamps = false;
  if (drupalSettings.entityHistory && drupalSettings.entityHistory.lastReadTimestamps) {
    embeddedLastReadTimestamps = drupalSettings.entityHistory.lastReadTimestamps;
  }

  /**
   * @namespace
   */
  Drupal.entityHistory = {

    /**
     * Fetch "last read" timestamps for the given nodes.
     *
     * @param {Array} nodeIDs
     *   An array of node IDs.
     * @param {function} callback
     *   A callback that is called after the requested timestamps were fetched.
     */
    fetchTimestamps: function (entities, callback) {
      // Use the data embedded in the page, if available.
      if (embeddedLastReadTimestamps) {
        callback();
        return;
      }

      $.ajax({
        url: Drupal.url('entity_history/get_node_read_timestamps'),
        type: 'POST',
        data: {
          entities: JSON.stringify(entities)
        },
        dataType: 'json',
        success: function (results) {
          console.log(results);
          for (var entityType in results) {
            if (results.hasOwnProperty(entityType)) {
              for (var entityID in entityType) {
                storage.setItem('Drupal.entityHistory.' + currentUserID + '.' + entityType + '.' + entityID, results[entityType][entityID]);
              }
            }
          }
          callback();
        }
      });
    },

    /**
     * Get the last read timestamp for the given node.
     *
     * @param {number|string} nodeID
     *   A node ID.
     *
     * @return {number}
     *   A UNIX timestamp.
     */
    getLastRead: function (entityID, entityType) {
      // Use the data embedded in the page, if available.
      if (embeddedLastReadTimestamps && embeddedLastReadTimestamps[entityType] && embeddedLastReadTimestamps[entityType][entityID]) {
        return parseInt(embeddedLastReadTimestamps[entityType][entityID], 10);
      }
      return parseInt(storage.getItem('Drupal.entityHistory.' + currentUserID + '.' + entityType + '.' + entityID) || 0, 10);
    },

    /**
     * Marks a node as read, store the last read timestamp client-side.
     *
     * @param {number|string} nodeID
     *   A node ID.
     */
    markAsRead: function (entityID, entityType) {
      $.ajax({
        url: Drupal.url('entity_history/read'),
        data: {
          entity_type: entityType,
          entity_id: entityID
        },
        type: 'POST',
        dataType: 'json',
        success: function (timestamp) {
          console.log(timestamp);
          // If the data is embedded in the page, don't store on the client
          // side.
          if (embeddedLastReadTimestamps && embeddedLastReadTimestamps[entityType] && embeddedLastReadTimestamps[entityType][entityID]) {
            return;
          }

          storage.setItem('Drupal.entityHistory.' + currentUserID + '.' + entityType + '.' + entityID, timestamp);
        }
      });
    },

    /**
     * Determines whether a server check is necessary.
     *
     * Any content that is >30 days old never gets a "new" or "updated"
     * indicator. Any content that was published before the oldest known reading
     * also never gets a "new" or "updated" indicator, because it must've been
     * read already.
     *
     * @param {number|string} nodeID
     *   A node ID.
     * @param {number} contentTimestamp
     *   The time at which some content (e.g. a comment) was published.
     *
     * @return {bool}
     *   Whether a server check is necessary for the given node and its
     *   timestamp.
     */
    needsServerCheck: function (entityID, entityType, contentTimestamp) {
      // First check if the content is older than 30 days, then we can bail
      // early.
      if (contentTimestamp < thirtyDaysAgo) {
        return false;
      }

      // Use the data embedded in the page, if available.
      if (embeddedLastReadTimestamps && embeddedLastReadTimestamps[entityID] && embeddedLastReadTimestamps[entityType][entityID]) {
        return contentTimestamp > parseInt(embeddedLastReadTimestamps[entityType][entityID], 10);
      }

      var minLastReadTimestamp = parseInt(storage.getItem('Drupal.entityHistory.' + currentUserID + '.' + entityType + '.' + entityID) || 0, 10);
      return contentTimestamp > minLastReadTimestamp;
    }
  };

})(jQuery, Drupal, drupalSettings, window.localStorage);
