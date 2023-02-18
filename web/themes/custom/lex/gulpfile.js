var gulp        = require('gulp'),
    browserify  = require('browserify'),
    source      = require('vinyl-source-stream'),
    buffer      = require('vinyl-buffer'),
    sass        = require('gulp-sass')(require('sass')),
    prefix      = require('gulp-autoprefixer'),
    sourcemaps  = require('gulp-sourcemaps')
    rename      = require('gulp-rename'),
    cleanCSS    = require('gulp-clean-css'),
    browserSync = require('browser-sync').create(),

/**
 * @task sass
 * Compile files from scss
 */
gulp.task('sass', function(done) {
return gulp.src('./scss/**/*.scss')
    .pipe(sass())
    .pipe(prefix(['last 3 versions', '> 1%', 'ie 10'], { cascade: true }))
    .pipe(cleanCSS({compatibility: 'ie10'}))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('dist'))
    .on('close', done);
});

/**
 * @task old_sass
 * Compile files from scss
 */
 gulp.task('old_sass', function(done) {
    return gulp.src('./old_scss/**/*.*')
        .pipe(sass())
        .pipe(prefix(['last 3 versions', '> 1%', 'ie 10'], { cascade: true }))
        .pipe(cleanCSS({compatibility: 'ie10'}))
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('dist'))
        .on('close', done);
    });

/**
 * @task scripts
 * Compile files from js
 */
gulp.task('scripts', function() {
    return browserify('js/main.js', {debug:true})
      .transform('babelify', {
        presets: ['@babel/preset-env']
      })
      .plugin('tinyify')
      .bundle()
      .pipe(source('main.min.js'))
      .pipe(buffer())
      .pipe(sourcemaps.init({loadMaps: true}))
      .pipe(sourcemaps.write('.'))
      .pipe(gulp.dest('dist'))
});

/**
 * @task refresh
 * Refresh browser
 */
gulp.task('refresh', function(done) {
    browserSync.reload();
    done();
});

/**
 * @task build
 * Compile sass / js
 */
 gulp.task('build', gulp.series('sass', 'old_sass', 'scripts'));

/**
 * @task watch
 * Watch scss/js files for changes & recompile
 * Clear cache when Drupal related files are changed
 */
gulp.task('watch', function(){
    browserSync.init({
        socket: {
          domain: 'lex-sync.lndo.site'
        },
        logLevel: 'silent',
        notify: false,
        cors: true,
        server: {
          baseDir: '.',
        },
        open: false,
    });

    gulp.watch('./scss/**/*.scss', gulp.series('sass', 'refresh'));
    gulp.watch('./old_scss/**/*.s[ac]ss', gulp.series('old_sass', 'refresh'));
    gulp.watch(['js/*.js'], gulp.series('scripts', 'refresh'));
    gulp.watch([
        '**/*.twig',
        '**/*.yml',
        '**/*.theme'
    ], gulp.series('refresh'));
});

/**
 * Default task, running just `gulp` will
 * Watch scss/js files for changes & recompile
 * Clear cache when Drupal related files are changed
 */
 gulp.task('default', gulp.series('build', 'watch'));