<?php

namespace Drupal\Tests\search_api_solr\Unit;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\search_api\Plugin\search_api\data_type\value\TextToken;
use Drupal\search_api\Plugin\search_api\data_type\value\TextValue;
use Drupal\search_api\Utility\DataTypeHelperInterface;
use Drupal\search_api\Utility\FieldsHelperInterface;
use Drupal\search_api_solr\Controller\AbstractSolrEntityListBuilder;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Drupal\search_api_solr\Plugin\search_api\data_type\value\DateRangeValue;
use Drupal\search_api_solr\SolrConnector\SolrConnectorPluginManager;
use Drupal\Tests\search_api_solr\Traits\InvokeMethodTrait;
use Drupal\Tests\UnitTestCase;
use Solarium\Core\Query\Helper;
use Solarium\QueryType\Update\Query\Document;

/**
 * Tests functionality of the backend.
 *
 * @coversDefaultClass \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend
 *
 * @group search_api_solr
 */
class SearchApiBackendUnitTest extends UnitTestCase {

  use InvokeMethodTrait;

  /**
   * @var \Drupal\search_api_solr\Controller\AbstractSolrEntityListBuilder|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $listBuilder;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $entityTypeManager;

  /**
   * @var \Solarium\Core\Query\Helper
   */
  protected $queryHelper;

  /**
   * @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend
   */
  protected $backend;

  /**
   *
   */
  public function setUp() {
    parent::setUp();

    $this->listBuilder = $this->prophesize(AbstractSolrEntityListBuilder::class);
    $this->listBuilder->getAllNotRecommendedEntities()->willReturn([]);
    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityTypeManager->getListBuilder('solr_field_type')->willReturn($this->listBuilder->reveal());
    $this->entityTypeManager->getListBuilder('solr_cache')->willReturn($this->listBuilder->reveal());
    $this->entityTypeManager->getListBuilder('solr_request_handler')->willReturn($this->listBuilder->reveal());
    $this->entityTypeManager->getListBuilder('solr_request_dispatcher')->willReturn($this->listBuilder->reveal());

    // This helper is actually used.
    $this->queryHelper = new Helper();

    $this->backend = new SearchApiSolrBackend([], NULL, [],
      $this->prophesize(ModuleHandlerInterface::class)->reveal(),
      $this->prophesize(Config::class)->reveal(),
      $this->prophesize(LanguageManagerInterface::class)->reveal(),
      $this->prophesize(SolrConnectorPluginManager::class)->reveal(),
      $this->prophesize(FieldsHelperInterface::class)->reveal(),
      $this->prophesize(DataTypeHelperInterface::class)->reveal(),
      $this->queryHelper,
      $this->entityTypeManager->reveal(),
      $this->prophesize(ContainerAwareEventDispatcher::class)->reveal());
  }

  /**
   * @covers       ::addIndexField
   *
   * @dataProvider addIndexFieldDataProvider
   *
   * @param mixed $input
   *   Field value.
   *
   * @param string $type
   *   Field type.
   *
   * @param mixed $expected
   *   Expected result.
   */
  public function testIndexField($input, $type, $expected) {
    $field = 'testField';
    $document = $this->prophesize(Document::class);

    if (NULL !== $expected) {
      if (is_array($expected)) {
        $document
          ->addField($field, $expected[0], $expected[1])
          ->shouldBeCalled();
      }
      else {
        $document
          ->addField($field, $expected)
          ->shouldBeCalled();
      }
    }
    else {
      $document
        ->addField($field, $expected)
        ->shouldNotBeCalled();
    }

    $boost_terms = [];
    $args = [
      $document->reveal(),
      $field,
      [$input],
      $type,
      &$boost_terms,
    ];

    // addIndexField() should convert the $input according to $type and call
    // Document::addField() with the correctly converted $input.
    $this->invokeMethod(
      $this->backend,
      'addIndexField',
      $args,
      []
    );
  }

  /**
   *
   */
  public function testFormatDate() {
    $this->assertFalse($this->backend->formatDate('asdf'));
    $this->assertEquals('1992-08-27T00:00:00Z', $this->backend->formatDate('1992-08-27'));
  }

  /**
   * Data provider for testIndexField method.
   */
  public function addIndexFieldDataProvider() {
    return [
      // addIndexField() should be called.
      ['0', 'boolean', 'false'],
      ['1', 'boolean', 'true'],
      [0, 'boolean', 'false'],
      [1, 'boolean', 'true'],
      [FALSE, 'boolean', 'false'],
      [TRUE, 'boolean', 'true'],
      ['2016-05-25T14:00:00+10', 'date', '2016-05-25T04:00:00Z'],
      ['1465819200', 'date', '2016-06-13T12:00:00Z'],
      [
        new DateRangeValue('2016-05-25T14:00:00+10', '2017-05-25T14:00:00+10'),
        'solr_date_range',
        '[2016-05-25T04:00:00Z TO 2017-05-25T04:00:00Z]',
      ],
      [-1, 'integer', -1],
      [0, 'integer', 0],
      [1, 'integer', 1],
      [-1.0, 'decimal', -1.0],
      [0.0, 'decimal', 0.0],
      [1.3, 'decimal', 1.3],
      ['foo', 'string', 'foo'],
      [new TextValue('foo bar'), 'text', 'foo bar'],
      [(new TextValue(''))->setTokens([new TextToken('bar')]), 'text', 'bar'],
      // addIndexField() should not be called.
      [NULL, 'boolean', NULL],
      [NULL, 'date', NULL],
      [NULL, 'solr_date_range', NULL],
      [NULL, 'integer', NULL],
      [NULL, 'decimal', NULL],
      [NULL, 'string', NULL],
      ['', 'string', NULL],
      [new TextValue(''), 'text', NULL],
      [(new TextValue(''))->setTokens([new TextToken('')]), 'text', NULL],
    ];
  }

}
