module.exports = function(grunt) {
    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        makepot: {
            target: {
                options: {
                    domainPath: '/languages',
                    mainFile: 'learndash_gravityforms.php',
                    potFilename: 'learndash-gravity-forms.pot',
                    processPot: function( pot, options ) {
                        pot.headers['report-msgid-bugs-to'] = 'https://www.learndash.com/help/';
                        pot.headers['language-team'] = 'LearnDash <support@learndash.com>';

                        var translation,
                        excluded_meta = [
                            'Plugin Name of the plugin/theme',
                            'Plugin URI of the plugin/theme',
                            'Author of the plugin/theme',
                            'Author URI of the plugin/theme'
                        ];

                        for ( translation in pot.translations[''] ) {
                            if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
                                if ( excluded_meta.indexOf( pot.translations[''][ translation ].comments.extracted ) >= 0 ) {
                                    console.log( 'Excluded meta: ' + pot.translations[''][ translation ].comments.extracted );
                                    delete pot.translations[''][ translation ];
                                }
                            }
                        }

                        return pot;
                    },
                    type: 'wp-plugin'
                }
            }
        }
    });
    
    // Load tasks.
    grunt.loadNpmTasks( 'grunt-wp-i18n' );
};