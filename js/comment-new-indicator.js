/**
 * @file
 * Attaches behaviors for the Comment module's "new" indicator.
 *
 * May only be loaded for authenticated users, with the Entity-History module
 * installed.
 */

(function ($, Drupal, window) {

  'use strict';

  /**
   * Renders "new" comment indicators wherever necessary.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches "new" comment indicators behavior.
   */
  Drupal.behaviors.commentNewIndicator = {
    attach: function (context) {
      // Collect all "new" comment indicator placeholders (and their
      // corresponding node IDs) newer than 30 days ago that have not already
      // been read after their last comment timestamp.
      var entities = {};
      var $placeholders = $(context)
        .find('[data-comment-timestamp]')
        .once('entity_history')
        .filter(function () {
          var $placeholder = $(this);
          var commentTimestamp = parseInt($placeholder.attr('data-comment-timestamp'), 10);
          var entityID = $placeholder.closest('[data-entity-history-entity-id]').attr('data-entity-history-entity-id');
          var entityType = $placeholder.closest('[data-entity-history-entity-type]').attr('data-entity-history-entity-type');
          if (Drupal.entityHistory.needsServerCheck(entityID, entityType, commentTimestamp)) {
            if (!entities[entityType]) {
              entities[entityType] = [];
            }
            entities[entityType].push(entityID);
            return true;
          }
          else {
            return false;
          }
        });

      if ($placeholders.length === 0) {
        return;
      }

      // Fetch the node read timestamps from the server.
      Drupal.entityHistory.fetchTimestamps(entities, function () {
        processCommentNewIndicators($placeholders);
      });
    }
  };

  /**
   * Processes the markup for "new comment" indicators.
   *
   * @param {jQuery} $placeholders
   *   The elements that should be processed.
   */
  function processCommentNewIndicators($placeholders) {
    console.log('processCommentNewIndicators');
    var isFirstNewComment = true;
    var newCommentString = Drupal.t('new');
    var $placeholder;

    $placeholders.each(function (index, placeholder) {
      $placeholder = $(placeholder);
      var timestamp = parseInt($placeholder.attr('data-comment-timestamp'), 10);
      var $node = $placeholder.closest('[data-entity-history-entity-id]');
      var entityID = $node.attr('data-entity-history-entity-id');
      var entityType = $node.attr('data-entity-history-entity-type');
      var lastViewTimestamp = Drupal.entityHistory.getLastRead(entityID, entityType);
console.log(timestamp);
console.log(lastViewTimestamp);
console.log( (timestamp > lastViewTimestamp));
      if (timestamp > lastViewTimestamp) {
        // Turn the placeholder into an actual "new" indicator.
        var $comment = $(placeholder)
          .removeClass('hidden')
          .text(newCommentString)
          .closest('.js-comment')
          // Add 'new' class to the comment, so it can be styled.
          .addClass('new');

        // Insert "new" anchor just before the "comment-<cid>" anchor if
        // this is the first new comment in the DOM.
        if (isFirstNewComment) {
          isFirstNewComment = false;
          $comment.prev().before('<a id="new" />');
          // If the URL points to the first new comment, then scroll to that
          // comment.
          if (window.location.hash === '#new') {
            window.scrollTo(0, $comment.offset().top - Drupal.displace.offsets.top);
          }
        }
      }
    });
  }

})(jQuery, Drupal, window);
