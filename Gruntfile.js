var fs = require('fs');

module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		prompt: {

			install: {
				options: {
					questions: [
						{ config: 'dbHost', type: 'input', message: 'DB host', default: '127.0.0.1' },
						{ config: 'dbUser', type: 'input', message: 'DB username', default: 'root' },
						{ config: 'dbPass', type: 'password', message: 'DB password', default: '' },
						{ config: 'dbName', type: 'input', message: 'DB database', default: /\w*$/.exec(__dirname) }
					]
				}
			},

		},

		exec: {

			// Create project database
			createDB: {
				cmd: function() {
					return 'mysql' +
						' -h ' + this.config('dbHost') +
						' -u ' + this.config('dbUser') +
						(this.config('dbPass') ? ('-p' + this.config('dbPass')) : '') +
						' -e "CREATE DATABASE ' + this.config('dbName') + '"';
				}
			},

			// Create config file using example config
			createConfig: {
				cmd: function() {
					var example = 'app/config/config.local.example.neon';
					var local = 'app/config/config.local.neon';
					var content = fs.readFileSync(example, {encoding: 'utf8'});
					// Fill local config values
					content = content.replace(/host:.*/g, 'host: ' + this.config('dbHost'));
					content = content.replace(/username:.*/g, 'username: ' + this.config('dbUser'));
					content = content.replace(/password:.*/g, 'password: ' + this.config('dbPass'));
					content = content.replace(/database:.*/g, 'database: ' + this.config('dbName'));
					// Write config
					fs.writeFileSync(local, content);

					return '';
				}
			}

		}

	});

	// Load plugins
	grunt.loadNpmTasks('grunt-prompt');
	grunt.loadNpmTasks('grunt-exec');

	// Register tasks
	grunt.registerTask('default', ['']);
	grunt.registerTask('install', 'Project semi-automatic installation', ['prompt:install', 'exec:createDB', 'exec:createConfig']);

};