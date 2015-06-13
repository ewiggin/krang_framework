<?php 
/*
Relations
==========
belongs_to
has_one
has_many
has_many :through
has_one :through
has_and_belongs_to_many
*/

/**
* User Object
*/
class Users extends ActiveRecord {
	
	var $table = 'users';

	var $attributes = array(
		id => array(
			type => 'int(12)',
			primary_key => true,
			ignore => true
		),
		name 	=> 'string',
		password => 'string(6)',
		
		updated_at => 'timestamp',
		active => array(
			type => 'boolean',
			defaults_to => false
		),
		img_avatar => 'string',
	);

}



?>