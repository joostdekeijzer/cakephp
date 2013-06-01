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
require_once CORE_TEST_CASES . DS . 'Model' . DS . 'models.php';

/**
 * TranslateBehaviorTest class
 *
 * @package       Cake.Test.Case.Model.Behavior
 */
class TranslateBehaviorRelatedTest extends CakeTestCase {

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
		'core.translate_with_prefix',
		'translate_article_alias', 'translated_article_alias'
	);

/**
 * The original test from CakePHP
 *
 * @return void
 */
	public function testSaveAllTranslatedAssociations() {
		$this->loadFixtures('Translate', 'TranslateArticle', 'TranslatedArticle', 'TranslatedItem');

		$Model = new TranslatedArticle();
		$Model->locale = 'eng';

		$data = array(
			'TranslatedArticle' => array(
				'id' => 4,
				'user_id' => 1,
				'published' => 'Y',
				'title' => 'Title (eng) #1',
				'body' => 'Body (eng) #1'
			),
			'TranslatedItem' => array(
				array(
					'slug' => '',
					'title' => 'Nuevo leyenda #1',
					'content' => 'Upraveny obsah #1'
				),
				array(
					'slug' => '',
					'title' => 'New Title #2',
					'content' => 'New Content #2'
				),
			)
		);
		$result = $Model->saveAll($data);
		$this->assertTrue($result);

		$result = $Model->TranslatedItem->find('all', array(
			'conditions' => array('translated_article_id' => $Model->id)
		));
		$this->assertCount(2, $result);
		$this->assertEquals($data['TranslatedItem'][0]['title'], $result[0]['TranslatedItem']['title']);
		$this->assertEquals($data['TranslatedItem'][1]['title'], $result[1]['TranslatedItem']['title']);
	}

/**
 * Test that saveAll() works with hasMany associations that contain
 * translations and an alias is used for the relation.
 *
 * @return void
 */
	public function testSaveAllTranslatedAssociationsWithAliasses() {
		$this->loadFixtures('Translate', 'TranslatedItem', 'TranslateArticleAlias', 'TranslatedArticleAlias');
		$Model = new TranslatedArticleAlias();
		$Model->locale = $Model->TranslatedItemAlias->locale = 'eng';

		$data = array(
			'TranslatedArticleAlias' => array(
				'id' => 4,
				'user_id' => 1,
				'published' => 'Y',
				'title' => 'Title (eng) #1',
				'body' => 'Body (eng) #1'
			),
			'TranslatedItemAlias' => array(
				array(
					'slug' => '',
					'title' => 'Nuevo leyenda #1',
					'content' => 'Upraveny obsah #1'
				),
				array(
					'slug' => '',
					'title' => 'New Title #2',
					'content' => 'New Content #2'
				),
			)
		);
		$result = $Model->saveAll($data);
		$this->assertTrue($result);

		$result = $Model->TranslatedItemAlias->find('all', array(
			'conditions' => array('translated_article_id' => $Model->id)
		));
		$this->assertCount(2, $result);
		$this->assertEquals($data['TranslatedItemAlias'][0]['title'], $result[0]['TranslatedItemAlias']['title']);
		$this->assertEquals($data['TranslatedItemAlias'][1]['title'], $result[1]['TranslatedItemAlias']['title']);

		// check model fieldvalue in i18n table
		$expected = array(
			$result[0]['TranslatedItemAlias']['id'] => array(
				'title' => $result[0]['TranslatedItemAlias']['title'],
				'content' => $result[0]['TranslatedItemAlias']['content']
			),
			$result[1]['TranslatedItemAlias']['id'] => array(
				'title' => $result[1]['TranslatedItemAlias']['title'],
				'content' => $result[1]['TranslatedItemAlias']['content']
			),
		);
		$i18n = new TranslateTestModel();
		foreach($expected as $foreignKey => $fields) {
			foreach($fields as $field => $content ) {
				$result = $i18n->find('first', array( 'conditions' => array(
					'foreign_key' => $foreignKey,
					'model' => 'TranslatedItem',
					'field' => $field
				)));
				$this->assertEquals($content, $result['TranslateTestModel']['content']);
			}
		}
	}

/**
 * Delete should also delete the translations
 */
	public function testDelete() {
		$this->loadFixtures('Translate', 'TranslatedItem');
		$Model = new TranslatedItem();
		$Model->locale = 'eng';

		$result = $Model->delete(1);
		$this->assertTrue($result);

		$i18n = new TranslateTestModel();
		$this->assertEquals(0, $i18n->find('count', array('conditions' => array(
			'model'       => 'TranslatedItem',
			'foreign_key' => 1,
		))));
	}

/**
 * Delete on Model Alias should also delete the translations.
 *
 * @return void
 */
	public function testDeleteTranslatedWithAlias() {
		$this->loadFixtures('Translate', 'TranslatedItem');
		$Model = new TranslatedItemAlias();
		$Model->locale = 'eng';

		$this->assertEquals('TranslatedItemAlias', $Model->alias);

		$result = $Model->delete(1);
		$this->assertTrue($result);

		$i18n = new TranslateTestModel();
		$this->assertEquals(0, $i18n->find('count', array('conditions' => array(
			'model'       => 'TranslatedItem',
			'foreign_key' => 1,
		))));
	}

/**
 * Associated delete on alias
 */
	public function testDeleteTranslatedAssociationsWithAliasses() {
		$this->loadFixtures('Translate', 'TranslatedItem', 'TranslateArticleAlias', 'TranslatedArticleAlias');
		$Model = new TranslatedArticleAlias();
		$Model->locale = $Model->TranslatedItemAlias->locale = 'eng';

		$result = $Model->TranslatedItemAlias->delete(1);
		$this->assertTrue($result);

		$result = $Model->TranslatedItemAlias->find('count', array('conditions' => array(
			$Model->TranslatedItemAlias->escapeField('id') => 1
		)));
		$this->assertEquals(0, $result);

		// check for in case we forget to set the locale on the associated item
		$result = $Model->TranslatedItemAlias->find('count');
		$this->assertEquals(2, $result);

		$i18n = new TranslateTestModel();
		$result = $i18n->find('count', array('conditions' => array(
			'model'       => 'TranslatedItem',
			'foreign_key' => 1,
		)));
		$this->assertEquals(0, $result);
	}

}

class TranslatedArticleAlias extends TranslatedArticle {
	public $name = 'TranslatedArticleAlias';
	public $hasMany = array('TranslatedItemAlias' => array(
		'className' => 'TranslatedItem',
		'foreignKey' => 'translated_article_id'
	) );
}

class TranslatedItemAlias extends TranslatedItem {
	public $name = 'TranslatedItem';
	public $alias = 'TranslatedItemAlias';
}
