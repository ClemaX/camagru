module.exports = function (grunt) {
	grunt.initConfig({
		sass: {
			dist: {
				options: {
					style: "compressed",
				},
				files: [
					{
						expand: true,
						cwd: "scss",
						src: ["**/*.scss"],
						dest: "public/css",
						ext: ".css",
					},
				],
			},
		},
		uglify: {
			options: {
				compress: true,
				mangle: true,
			},
			dist: {
				files: [
					{
						expand: true,
						cwd: "js",
						src: ["**/*.js"],
						dest: "public/js",
						ext: ".min.js",
					},
				],
			},
		},
		svgmin: {
			options: {
				plugins: [
					{
						name: "preset-default",
						params: {
							overrides: {
								inlineStyles: false,
							},
						},
					},
				],
			},
			dist: {
				files: {
					"public/img/icon.svg": "svg/icon.svg",
				},
			},
		},
		copy: {
			fonts: {
				expand: true,
				cwd: "node_modules/bootstrap-icons/font/fonts/",
				src: "*",
				dest: "public/css/fonts/",
			},
		},
		browserSync: {
			default_options: {
				bsFiles: {
					src: [
						"public/css/**/*.css",
						"public/js/**/*.js",
						"public/index.php",
						"src/**/*.php",
					],
				},
				options: {
					watchTask: true,
					port: 80,
					open: false,
					notify: false,
				},
			},
		},
		watch: {
			scss: {
				files: ["scss/**/*.scss"],
				tasks: ["sass"],
			},
			js: {
				files: ["js/**/*.js"],
				tasks: ["uglify"],
			},
			svg: {
				files: ["svg/**/*.svg"],
				tasks: ["svgmin"],
			},
		},
	});

	grunt.loadNpmTasks("grunt-contrib-sass");
	grunt.loadNpmTasks("grunt-contrib-uglify");
	grunt.loadNpmTasks("grunt-svgmin");
	grunt.loadNpmTasks("grunt-contrib-copy");
	grunt.loadNpmTasks("grunt-browser-sync");
	grunt.loadNpmTasks("grunt-contrib-watch");

	grunt.registerTask("default", [
		"sass",
		"uglify",
		"svgmin",
		"copy",
		"browserSync",
		"watch",
	]);
};
