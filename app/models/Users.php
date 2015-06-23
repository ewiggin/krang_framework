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

class Usuaris extends ActiveRecord {
	
	var $table = 'users';

	var $attributes = array(

		id => array(
			type 		=> 'int(12)',
			primary_key => true,
			ignore 		=> true
		),
		name 		=> 'string(255)',
		email 		=> 'string(255)',
		role 		=> 'string(6)'
	);
}



?>