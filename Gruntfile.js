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
		purgecss: {
      target: {
        options: {
          content: ["src/Views/**/*.php", "public/js/**/*.js"],
        },
        files: [
          {
            expand: true,
            cwd: "public/css",
            src: ["**/*.css"],
            dest: "public/css",
            ext: ".min.css",
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

        files: [
          {
            expand: true,
            cwd: "svg",
            src: ["**/*.svg"],
            dest: "public/img",
            ext: ".svg",
          },
        ],
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
						"public/css/**/*.min.css",
						"public/js/**/*.min.js",
						"public/img/**/*",
						"public/index.php",
						"src/**/*.php",
					],
				},
				options: {
					watchTask: true,
					port: 8080,
					open: false,
					notify: false,
				},
			},
		},
		watch: {
			scss: {
				files: ["scss/**/*.scss"],
				tasks: ["sass", "purgecss"],
			},
			js: {
				files: ["js/**/*.js"],
				tasks: ["uglify"],
			},
			cssConsumers: {
				files: ["public/js/**/*.js", "src/**/*.php"],
				tasks: ["purgecss"],
			},
			svg: {
				files: ["svg/**/*.svg"],
				tasks: ["svgmin"],
			},
		},
	});

	grunt.loadNpmTasks("grunt-contrib-sass");
	grunt.loadNpmTasks("grunt-contrib-uglify");
	grunt.loadNpmTasks('grunt-purgecss');
	grunt.loadNpmTasks("grunt-svgmin");
	grunt.loadNpmTasks("grunt-contrib-copy");
	grunt.loadNpmTasks("grunt-browser-sync");
	grunt.loadNpmTasks("grunt-contrib-watch");

	grunt.registerTask("default", [
		"sass",
		"uglify",
		"purgecss",
		"svgmin",
		"copy",
		"browserSync",
		"watch",
	]);
};
