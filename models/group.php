<?php
class Group extends AppModel {
	
/**
 * Name
 *
 * @var string
 */
	public $name = 'Group';
	
/**
 * Displayfield
 *
 * @var string $displayField
 */
	public $displayField = 'name';
/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array(
											'Containable',

									    'SuperAuth.Acl' => array(
									        'type' => 'requester',
									        'parentClass'=> null,
									        'foreignKey' => null
									    )
	);

/**
 * Validation parameters
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'alphanumeric' => array(
				'rule' => array('alphanumeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'group_id',
			'dependent' => false
		)
	);

}
?>