<?php

namespace Drupal\multiversion\Tests;

/**
 * Test the MultiversionManager class.
 *
 * @group multiversion
 */
class MultiversionManagerTest extends MultiversionWebTestBase {

  const REVISION_HASH_REGEX = '[0-9a-f]{32}';

  /**
   * @var \Drupal\multiversion\MultiversionManager
   */
  protected $multiversionManager;

  protected function setUp() {
    parent::setUp();
    $this->multiversionManager = \Drupal::service('multiversion.manager');
  }

  protected function extractRevisionHash($rev) {
    preg_match('/\d\-(' . self::REVISION_HASH_REGEX . ')/', $rev, $matches);
    return isset($matches[1]) ? $matches[1] : FALSE;
  }

  public function assertRevisionId($index, $value, $message) {
    $this->assertTrue(preg_match('/' . $index . '\-' . self::REVISION_HASH_REGEX . '/', $value), $message);
  }

  public function testRequiredFields() {
    $entity = entity_create('entity_test_rev');
    $this->assertTrue(isset($entity->_deleted), 'Deleted flag field was attached');
    $this->assertTrue(isset($entity->_revs_info), 'Revision info field was attached');
  }

  public function testRevisionIdGeneration() {
    $entity = entity_create('entity_test_rev');
    $first_rev = $this->multiversionManager->newRevisionId($entity, 0);
    $this->assertRevisionId(1, $first_rev, 'First revision ID was generated correctly.');

    $new_rev = $this->multiversionManager->newRevisionId($entity, 0);
    $this->assertEqual($first_rev, $new_rev, 'Identical revision IDs with same input parameters.');

    $second_rev = $this->multiversionManager->newRevisionId($entity, 1);
    $this->assertRevisionId(2, $second_rev, 'Second revision ID was generated correctly.');

    $this->assertEqual($this->extractRevisionHash($first_rev), $this->extractRevisionHash($second_rev), 'First and second revision hashes was identical (entity did not change).');

    $revs = array($first_rev);

    $test_entity = clone $entity;
    $test_entity->_revs_info->rev = $first_rev;
    $revs[] = $this->multiversionManager->newRevisionId($test_entity, 0);
    $this->assertTrue(count($revs) == count(array_unique($revs)), 'Revision ID varies on old revision.');

    $test_entity = clone $entity;
    $test_entity->name = $this->randomMachineName();
    $revs[] = $this->multiversionManager->newRevisionId($test_entity, 0);
    $this->assertTrue(count($revs) == count(array_unique($revs)), 'Revision ID varies on entity fields.');

    $test_entity = clone $entity;
    $test_entity->_deleted->value = TRUE;
    $revs[] = $this->multiversionManager->newRevisionId($test_entity, 0);
    $this->assertTrue(count($revs) == count(array_unique($revs)), 'Revision ID varies on deleted flag.');
  }
}
