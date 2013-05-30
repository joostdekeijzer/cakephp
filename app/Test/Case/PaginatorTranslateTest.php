<?php
/**
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @since         CakePHP(tm) v 1.2.0.5669
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Model', 'Model');
App::uses('AppModel', 'Model');

/**
 * TranslateBehaviorTest class
 *
 * @package       Cake.Test.Case.Model.Behavior
 */
class PaginatorTranslateTest extends CakeTestCase {

/**
 * autoFixtures property
 *
 * @var bool false
 */
	public $autoFixtures = false;

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = array(
		'core.translated_item', 'core.translate', 'core.translate_table',
		'core.translated_article', 'core.translate_article', 'core.user', 'core.comment', 'core.tag', 'core.articles_tag',
		'core.translate_with_prefix', 'translate_with_body'
	);

/**
 * Test custom find query on translated content linke Paginator does.
 *
 * Paginator finds the items but also tries to find the total-count with the
 * same criteria.
 *
 * @return void
 */
	public function testCustomFindQueryOnTranslatedContentWithCount() {
		$this->loadFixtures('TranslateWithBody', 'TranslatedItem');
		$Model = new TranslatedItemModified();
		$Model->locale = 'eng';

		$translations = array(
			'title' => 'allTitles',
			'body'
		);

		$Model->bindTranslation($translations, false);
		$conditions = array(
			'I18n__allTitles.content LIKE' => '%#1%'
		);

		$result = $Model->find('all', array( 'conditions' => $conditions ) );
		$expected = array( 0 => array(
			'TranslatedItem' => array(
				'id' => 1,
				'translated_article_id' => 1,
				'slug' => 'first_translated',
				'locale' => 'eng',
				'title' => 'Title #1',
				'body' => 'Content #1'
			),
			'allTitles' => array(
				0 => array(
					'id' => 1,
					'locale' => 'eng',
					'model' => 'TranslatedItem',
					'foreign_key' => 1,
					'field' => 'title',
					'content' => 'Title #1'
				),
				1 => array(
					'id' => 3,
					'locale' => 'deu',
					'model' => 'TranslatedItem',
					'foreign_key' => 1,
					'field' => 'title',
					'content' => 'Titel #1'
				),
				2 => array(
					'id' => 5,
					'locale' => 'cze',
					'model' => 'TranslatedItem',
					'foreign_key' => 1,
					'field' => 'title',
					'content' => 'Titulek #1'
				),
			)
		));
		$this->assertEquals($expected, $result);

		$result = $Model->find('count', array( 'conditions' => $conditions ) );
		$this->assertEquals(1, $result);
	}
}


/**
 * Copied & modified from:
 * require_once CORE_TEST_CASES . DS . 'Model' . DS . 'models.php';
 */

/**
 * AppModel class
 *
 * @package       Cake.Test.Case.Model
 */
class AppModel extends Model {

/**
 * findMethods property
 *
 * @var array
 */
	public $findMethods = array('published' => true);

/**
 * useDbConfig property
 *
 * @var array
 */
	public $useDbConfig = 'test';

/**
 * _findPublished custom find
 *
 * @return array
 */
	protected function _findPublished($state, $query, $results = array()) {
		if ($state === 'before') {
			$query['conditions']['published'] = 'Y';
			return $query;
		}
		return $results;
	}

}

/**
 * TranslateTestModel class.
 *
 * @package       Cake.Test.Case.Model
 */
class TranslateTestModel extends CakeTestModel {

/**
 * name property
 *
 * @var string 'TranslateTestModel'
 */
	public $name = 'TranslateTestModel';

/**
 * useTable property
 *
 * @var string 'i18n'
 */
	public $useTable = 'i18n';

/**
 * displayField property
 *
 * @var string 'field'
 */
	public $displayField = 'field';
}

/**
 * TranslatedItem class.
 *
 * @package       Cake.Test.Case.Model
 */
class TranslatedItemModified extends CakeTestModel {

/**
 * name property
 *
 * @var string 'TranslatedItem'
 */
	public $name = 'TranslatedItem';

/**
 * cacheQueries property
 *
 * @var bool false
 */
	public $cacheQueries = false;

/**
 * actsAs property
 *
 * @var array
 */
	public $actsAs = array('Translate' => array('body', 'title'));

/**
 * translateModel property
 *
 * @var string 'TranslateTestModel'
 */
	public $translateModel = 'TranslateTestModel';

}

/**
 * TranslatedItem class.
 *
 * @package       Cake.Test.Case.Model
 */
class TranslatedItem extends CakeTestModel {

/**
 * name property
 *
 * @var string 'TranslatedItem'
 */
	public $name = 'TranslatedItem';

/**
 * cacheQueries property
 *
 * @var bool false
 */
	public $cacheQueries = false;

/**
 * actsAs property
 *
 * @var array
 */
	public $actsAs = array('Translate' => array('content', 'title'));

/**
 * translateModel property
 *
 * @var string 'TranslateTestModel'
 */
	public $translateModel = 'TranslateTestModel';

}
