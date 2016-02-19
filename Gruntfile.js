//Gruntfile
module.exports = function(grunt) {

    require('load-grunt-tasks')(grunt);

    //Initializing the configuration object
    grunt.initConfig({

        // Task configuration
        php: {
            dist: {
                options: {
                    hostname: '127.0.0.1',
                    port: 8080,
                    base: 'public',
                    keepalive: false,
                    open: false
                }
            }
        },
        browserSync: {
            dist: {
                bsFiles: {
                    src : [
                        'public/assets/stylesheets/*.css',
                        'public/assets/javascript/*.js',
                        'public/*.php',
                        'public/**/*.php',
                        'templates/*.html',
                        'templates/**/*.html'
                    ]
                },
                options: {
                    proxy: '<%= php.dist.options.hostname %>:<%= php.dist.options.port %>',
                    watchTask: true,
                    notify: true,
                    open: true,
                    logLevel: 'silent',
                    ghostMode: {
                        clicks: true,
                        scroll: true,
                        links: true,
                        forms: true
                    }
                }
            }
        },
        sass: {
            dist: {
                options: {
                    style: 'compressed' // change to 'compressed' for minification
                },
                files: {
                    //compiling main.scss into main.css
                    "./public/assets/stylesheets/main.css":"./assets/stylesheets/styles.scss"
                }
            }
        },
        copy: {
            main: {
                files: [
                    // includes files within path
                    {
                        expand: true,
                        flatten: true,
                        src: ['./assets/fonts/**'],
                        dest: './public/assets/fonts',
                        filter: 'isFile'
                    },
                    {
                        expand: true,
                        flatten: true,
                        src: ['./assets/images/**'],
                        dest: './public/assets/images',
                        filter: 'isFile'
                    },
                ]
            }
        },
        concat: {
            options: {
                separator: ';',
            },
            main_js: {
                src: ['./assets/javascript/main.js'],
                dest: './public/assets/javascript/main.js'
            }
        },
        uglify: {
            options: {
                mangle: true  // Use if you want the names of your functions and variables unchanged
            },
            main_js: {
                files: {
                    './public/assets/javascript/main.js': './public/assets/javascript/main.js',
                }
            }
        },
        phpunit: {
            classes: {
                dir: 'tests/'   //location of the tests
            },
            options: {
                bin: 'vendor/bin/phpunit',
                colors: true,
                coverageHtml: 'coverage'
            }
        },
        watch: {
            main_js: {
                files: [
                    //watched files
                    './assets/javascript/main.js'
                ],
                tasks: ['concat:main_js','uglify:frontend'],     //tasks to run
                options: {
                    livereload: true                        //reloads the browser
                }
            },
            sass: {
                files: ['./app/assets/stylesheets/*.scss', './app/assets/stylesheets/**/*.scss'],  //watched files
                tasks: ['sass'],                          //tasks to run
                options: {
                    livereload: true                        //reloads the browser
                }
            },
            tests: {
                files: ['public/src/*.php'],  //the task will run only when you save files in this location
                tasks: ['phpunit']
            }
        }
    });


    // Task definition
    grunt.registerTask('default', ['watch']);
    grunt.registerTask('build', [
        'phpunit',
        'copy',
        'sass',
        'concat',
        'uglify'
    ]);
    grunt.registerTask('serve', [
        'phpunit',
        'copy',
        'sass',
        'concat',
        'uglify',
        'php:dist',         // Start PHP Server
        'browserSync:dist', // Using the php instance as a proxy
        'watch'             // Any other watch tasks you want to run
    ]);
};
