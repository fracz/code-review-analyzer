var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
  mix
	  .less('**/*.less')
	  .coffee('**/*.coffee')
	  .styles([
		  '**/bootstrap.min.css',
		  '**/bootstrap-select.min.css',
		  '**/bootstrap.vertical-tabs.min.css',
		  '**/bootstrap-datetimepicker.min.css',
		  '**/c3.min.css'
	  ], 'public/css/vendor.min.css', 'bower_components')
	  .scripts([
		  '**/jquery.min.js',
		  '**/moment.min.js',
		  '**/bootstrap-select.min.js',
		  '**/bootstrap-datetimepicker.min.js',
		  '**/cytoscape.min.js',
		  '**/d3.min.js',
		  '**/c3.min.js'
	  ], 'public/js/vendor.min.js', 'bower_components')
	  .copy('bower_components/prism/prism.js', 'public/js/components/prism.js')
	  .copy('bower_components/prism/themes/prism.css', 'public/css/components/prism.css')
	  .copy(
		  'bower_components/prism/components/*.min.js',
		  'public/js/components/prism/'
    )
	  .copy(
		  'bower_components/prism/plugins/line-numbers/prism-line-numbers.min.js',
		  'public/js/components/prism-line-numbers.min.js'
    )
	  .copy(
		  'bower_components/prism/plugins/line-numbers/prism-line-numbers.css',
		  'public/css/components/prism-line-numbers.css'
    )
	  .copy(
	    'bower_components/prism/plugins/line-highlight/prism-line-highlight.min.js',
	    'public/js/components/prism-line-highlight.min.js'
    )
	  .copy(
	    'bower_components/prism/plugins/line-highlight/prism-line-highlight.css',
	    'public/css/components/prism-line-highlight.css'
    );
});


