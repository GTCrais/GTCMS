<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines contain the default error messages used by
	| the validator class. Some of these rules have multiple versions such
	| as the size rules. Feel free to tweak each of these messages here.
	|
	*/

	'accepted'		       => 'Polje :attribute mora biti prihvaćeno.',
	'active_url'	       => 'Polje :attribute nije validan URL.',
	'after'			       => 'Polje :attribute mora biti datum nakon :date.',
	'after_or_equal'       => 'Polje :attribute mora biti datum nakon datuma :date, ili jednako tom datumu.',
	'alpha'			       => 'Polje :attribute može sadržavati samo slova.',
	'alpha_dash'	       => 'Polje :attribute može sadržavati slova, brojeve, i minuse.',
	'alpha_num'		       => 'Polje :attribute može sadržavati slova i brojeve.',
	'array'			       => ':attribute mora biti "polje".',
	'before'		       => 'Polje :attribute mora biti datum prije :date',
	'before_or_equal'      => 'Polje :attribute mora biti datum prije datuma :date, ili jednako tom datumu.',
	'between'		       => [
		'numeric' => 'The :attribute must be between :min and :max.',
		'file'	  => 'The :attribute must be between :min and :max kilobytes.',
		'string'  => 'The :attribute must be between :min and :max characters.',
		'array'   => 'The :attribute must have between :min and :max items.',
	],
	'boolean'			   => 'The :attribute field must be true or false.',
	'confirmed'		       => 'Potvrda za polje :attribute nije točna.',
	'date'			       => 'Polje :attribute nije validan datum.',
	'date_format'	       => 'The :attribute does not match the format :format.',
	'different'		       => 'The :attribute and :other must be different.',
	'digits'		       => 'Polje se mora imati točno :digits znamenki.',
	'digits_between'       => ':attribute mora imati između :min i :max znamenki.',
	'dimensions'           => 'The :attribute has invalid image dimensions.',
	'distinct'		       => ':attribute s ovom vrijednosti već postoji.',
	'email'			       => 'Netočan format email-a.',
	'exists'		       => 'The selected :attribute is invalid.',
	'file'                 => 'The :attribute must be a file.',
	'filled'		       => 'Polje je obvezno.',
	'image'			       => 'The :attribute must be an image.',
	'in'			       => 'The selected :attribute is invalid.',
	'in_array'		       => 'The :attribute field does not exist in :other.',
	'integer'		       => 'The :attribute must be an integer.',
	'ip'			       => 'The :attribute must be a valid IP address.',
	'json'			       => 'The :attribute must be a valid JSON string.',
	'max'			       => [
		'numeric' => 'The :attribute may not be greater than :max.',
		'file'	  => 'The :attribute may not be greater than :max kilobytes.',
		'string'  => 'The :attribute may not be greater than :max characters.',
		'array'   => 'The :attribute may not have more than :max items.',
	],
	'mimes'			       => 'The :attribute must be a file of type: :values.',
	'mimetypes'            => 'The :attribute must be a file of type: :values.',
	'min'			       => [
		'numeric' => 'The :attribute must be at least :min.',
		'file'	  => 'The :attribute must be at least :min kilobytes.',
		'string'  => 'Polje mora imati minimalno :min znakova.',
		'array'   => 'The :attribute must have at least :min items.',
	],
	'not_in'		       => 'The selected :attribute is invalid.',
	'numeric'		       => 'The :attribute must be a number.',
	'present'		       => 'The :attribute field must be present.',
	'regex'			       => 'The :attribute format is invalid.',
	'required'		       => 'Polje je obvezno.',
	'required_if'	       => 'The :attribute field is required when :other is :value.',
	'required_unless'      => 'The :attribute field is required unless :other is in :values.',
	'required_with'	       => 'Polje je obvezno.',
	'required_with_all'    => 'The :attribute field is required when :values is present.',
	'required_without'     => 'The :attribute field is required when :values is not present.',
	'required_without_all' => 'The :attribute field is required when none of :values are present.',
	'same'			       => 'The :attribute and :other must match.',
	'size'			       => [
		'numeric' => 'The :attribute must be :size.',
		'file'	  => 'The :attribute must be :size kilobytes.',
		'string'  => 'The :attribute must be :size characters.',
		'array'   => 'The :attribute must contain :size items.',
	],
	'string'		       => 'The :attribute must be a string.',
	'timezone'		       => 'The :attribute must be a valid zone.',
	'unique'		       => 'Vrijednost polja mora biti jedinstvena.',
	'uploaded'             => 'The :attribute failed to upload.',
	'url'			       => 'The :attribute format is invalid.',

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| Here you may specify custom validation messages for attributes using the
	| convention 'attribute.rule' to name the lines. This makes it quick to
	| specify a specific custom language line for a given attribute rule.
	|
	*/

	'custom' => [
		'attribute-name' => [
			'rule-name' => 'custom-message',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Attributes
	|--------------------------------------------------------------------------
	|
	| The following language lines are used to swap attribute place-holders
	| with something more reader friendly such as E-Mail Address instead
	| of 'email'. This simply helps us make messages a little cleaner.
	|
	*/

	'attributes' => AdminHelper::getValidatorAttributes(),

];
