<?php

class DoorkeeperChangesFeedWorker extends DoorkeeperFeedWorker {
/* -(  Publishing Stories  )------------------------------------------------- */
  public function isEnabled() {
      return true;
  }

  private $_diffKeywordHandlers = null;

  /**
   * Add more handlers here
   */
  protected function getDiffKeywordHandlers() {
      if ($this->_diffKeywordHandlers == null) {
          $this->_diffKeywordHandlers = [
              new ChangesDiffRetryKeywordHandler()
          ];
      }
      return $this->_diffKeywordHandlers;
  }


  /**
   * Check the comment against the keywords to see if it matches. If any matches,
   * call the appropriate handlers.
   */
  protected function publishFeedStory() {
      $object = $this->getStoryObject();
      if (is_a($object, 'DifferentialRevision')) {
          $comment = $this->getComment();
          if ($comment == null) {
              return;
          }
          foreach ($this->getDiffKeywordHandlers() as $handler) {
              if (preg_match($handler->getKeyword(), $comment) === 1) {
                  $handler->runOnObject($this, $object, $this->getViewer(), $this->getPublisher());
              }
          }
      }
  }

  protected function getComment() {
      $story = $this->getFeedStory();
      if (!is_a($story, 'PhabricatorApplicationTransactionFeedStory')) {
          return null;
      }
      $primary = $story->getPrimaryTransaction();
      if (!is_a($primary, "PhabricatorApplicationTransaction")) {
          return null;
      }
      return $primary->getComment()->getContent();
  }
}