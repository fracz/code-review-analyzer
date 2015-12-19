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

	"accepted"             => "Atrybut musi zostać zaakceptowany.",
	"active_url"           => "Atrybut nie jest prawidłowym adresem internetowym.",
	"after"                => "Atrybut musi być po :date.",
	"alpha"                => "Atrybut może zawierać tylko litery.",
	"alpha_dash"           => "Atrybut może zawierać tylko litery, cyfry i pauzy.",
	"alpha_num"            => "Atrybut może zawierać tylko litery i cyfry.",
	"array"                => "Atrybut musi być tablicą.",
	"before"               => "Atrybut musi być przed :date.",
	"between"              => [
		"numeric" => "Atrybut musi być pomiędzy :min oraz :max.",
		"file"    => "Atrybut musi mieć więcej niż :min oraz mniej niż :max kilobajtów.",
		"string"  => "Atrybut musi mieć minimalnie :min oraz maksymalnie :max znaków.",
		"array"   => "Atrybut musi zawierać minimalnie :min oraz maksymalnie :max elementów.",
	],
	"boolean"              => "Atrybut musi być prawdą lub fałszem.",
	"confirmed"            => "Potwierdzenie Atrybut nie pasuje.",
	"date"                 => "Atrybut nie jest poprawną datą.",
	"date_format"          => "Atrybut nie pasuje do formatu :format.",
	"different"            => "Atrybut i :other muszą być różne.",
	"digits"               => "Atrybut musi mieć :digits cyfr.",
	"digits_between"       => "Atrybut musi mieć pomiędzy :min i :max cyfr.",
	"email"                => "Atrybut musi być poprawnym adresem e-mail.",
	"filled"               => "Atrybut jest wymagany.",
	"exists"               => "Atrybut jest nieprawidłowy.",
	"image"                => "Atrybut musi być obrazkiem.",
	"in"                   => "Atrybut jest nieprawidłowy.",
	"integer"              => "Atrybut musi być liczbą.",
	"ip"                   => "Atrybut musi być poprawnym adresem IP.",
	"max"                  => [
		"numeric" => "Atrybut nie może być większy niż :max.",
		"file"    => "Atrybut nie może być większy niż :max kilobajtów.",
		"string"  => "Atrybut nie może być dłuższy niż :max znaków.",
		"array"   => "Atrybut nie może mieć więcej niż :max elementów.",
	],
	"mimes"                => "Atrybut musi być plikiem o jednym z typów: :values.",
	"min"                  => [
		"numeric" => "Atrybut nie może być mniejszy niż :min.",
		"file"    => "Atrybut nie może być mniejszy niż :min kilobajtów.",
		"string"  => "Atrybut nie może być krótszy niż :min znaków.",
		"array"   => "Atrybut nie może mieć mniej niż :min elementów.",
	],
	"not_in"               => "Atrybut jest nieprawidłowy.",
	"numeric"              => "Atrybut musi być liczbą.",
	"regex"                => "Atrybut ma nieprawidłowy format.",
	"required"             => "Atrybut jest wymagany.",
	"required_if"          => "Atrybut jest wymagany, gdy :other ma wartość :value.",
	"required_with"        => "Atrybut jest wymagany gdy :values są wybrane.",
	"required_with_all"    => "Atrybut jest wymagany gdy wszystkie wartości :values są wybrane.",
	"required_without"     => "Atrybut jest wymagany gdy :values nie został wybrany.",
	"required_without_all" => "Atrybut jest wymagany gdy żadna z wartości :values nie zostały wybrane.",
	"same"                 => "Atrybut i :other muszą być identyczne.",
	"size"                 => [
		"numeric" => "Atrybut musi mieć wartość :size.",
		"file"    => "Atrybut musi mieć rozmiar :size kilobajtów.",
		"string"  => "Atrybut musi mieć długość :size znaków.",
		"array"   => "Atrybut musi zawierać :size elementów.",
	],
	"unique"               => "Atrybut został już wybrany.",
	"url"                  => "Atrybut ma nieprawidłowy format.",
	"timezone"             => "Atrybut musi być prawidłową strefą czasową.",

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| Here you may specify custom validation messages for attributes using the
	| convention "attribute.rule" to name the lines. This makes it quick to
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
	| of "email". This simply helps us make messages a little cleaner.
	|
	*/

	'attributes' => [],

];
