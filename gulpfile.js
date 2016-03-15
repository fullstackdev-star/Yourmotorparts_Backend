var browserify = require('browserify'),
    watchify = require('watchify'),
    gulp = require('gulp'),
    plumber = require('gulp-plumber'),
    gutil = require('gulp-util'),
    source = require('vinyl-source-stream'),
    sass = require('gulp-sass'),
    hbsfy = require("hbsfy"),
    sourceFile = './node/client/js/main.js',
    destFolder = './public/client/js/',
    destFile = 'bundle.js',
    sourceCSS = 'node/client/css/',
    destCSS = 'public/client/css/',
    apidoc = require('gulp-apidoc'),
    fs = require('fs-extra'),
    mocha = require('gulp-mocha'),
    exit = require('gulp-exit'),
    jsdoc = require("gulp-jsdoc3");

var props = {
    entries: sourceFile,
    debug: true,
    cache: {},
    packageCache: {}
};

var infos = {
	name: "MyChat",
	version: "0.0.1"
};

// build for dist
gulp.task('browserify-build', function() {
    var bundler = browserify({
        // Required watchify args
        cache: {},
        packageCache: {}, 
        fullPaths: true,
        // Browserify Options
        entries: sourceFile,
        debug: true
    });
    
    hbsfy.configure({
        extensions: ['hbs']
    });
    
    var bundle = function() {
        return bundler
        .transform(hbsfy)
        .bundle()
        .on('error', function(err){
            console.log(err.message);
            this.emit('end');
        })
        .pipe(source(destFile))
        .pipe(gulp.dest(destFolder));
    };

    return bundle();
});

gulp.task('copy', function() {
    fs.mkdirsSync('public/client/uploads');
    gulp.src('node/client/js/adapter.js').pipe( gulp.dest('public/client/js') );
    gulp.src('node/client/*.html').pipe( gulp.dest('public/client') );
    gulp.src('node/client/img/**/*').pipe( gulp.dest('public/client/img') );
    gulp.src('node_modules/bootstrap-sass/assets/fonts/**/*').pipe( gulp.dest('public/client/fonts') );
    gulp.src('node/client/css/backgroundsize.min.htc').pipe( gulp.dest('public/client') );
    gulp.src('node_modules/jquery-colorbox/example1/images/*').pipe( gulp.dest('public/client/css/images') );

    gulp.src('node/admin/*.html').pipe( gulp.dest('public/admin') );
});

gulp.task('build-css', function() {
  return gulp.src(sourceCSS + '*.scss')
    .pipe(plumber())
    .pipe(sass())
    .pipe(gulp.dest(destCSS));
});

gulp.task('build-apidoc', function() {
    apidoc.exec({
        src: "node/",
        dest: "doc/API"
    });
});

var JSDocTemplate = {
	path: "ink-docstrap",
    systemName: "MyChat Web Client",
    theme: "cosmo",
    linenums: true
};

var JSDocOptions = {
    outputSourceFiles: true
};

gulp.task("jsdoc", function() {
	gulp.src(["./node/client/**/*.js", "README.md"])
		.pipe(jsdoc.parser(infos))
		.pipe(jsdoc.generator("./doc/WebClient", JSDocTemplate, JSDocOptions))
});

gulp.task('build-dist', ['browserify-build', 'build-css', 'build-apidoc', 'copy'],function() {
    console.log('test');
    exit();
});

gulp.task('dev-all',['copy','browserify-build','build-css','build-apidoc'],function(){
    gulp.watch("./node/client/**/*.js", ["jsdoc"]);
    gulp.watch('node/client/**/*', ['build-dist']);
    gulp.watch('node/server/**/*', ['build-dist']);
});

gulp.task('build-dev-fast',['browserify-build'],function(){
    gulp.watch('node/client/**/*',['build-dist']);
    gulp.watch('node/server/**/*',['build-dist']);
});

gulp.task('default',['dev-all'],function(){
});

// tests
gulp.task('server-test', function (done) {
    return gulp.src('node/server/test/**/*.js', { read: false })
    .pipe(mocha({ reporter: 'spec' }))
    .pipe(exit());
});

