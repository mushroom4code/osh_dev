const {src, dest, task} = require('gulp');
const minifyCss = require('gulp-clean-css');
const rename = require('gulp-rename');
const gulp = require("gulp");
const uglify = require('gulp-uglify');


const cssSourse = ['local/templates/Oshisha/**/*.css', '!local/templates/Oshisha/**/*.min.css']
const jsSourse = ['local/templates/Oshisha/**/*.js', '!local/templates/Oshisha/**/*.min.js', '!bitrix/templates/Oshisha/**/*.map.js']

task('minCSS', function () {
    return src(cssSourse)
        .pipe(minifyCss())
        .pipe(rename({suffix: '.min'}))
        .pipe(dest(function (file) {
            return file.base
        }));
})

task('minJS', function () {
    return src(jsSourse)
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(dest(function (file) {
            return file.base
        }));
})

gulp.task('default', gulp.parallel('minCSS', 'minJS'));
