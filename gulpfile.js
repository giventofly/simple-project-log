const gulp = require("gulp");
const uglify = require("gulp-uglify-es").default;
const babel = require("gulp-babel");
const rename = require("gulp-rename");

gulp.task("minjs", function () {
  // Minify and transpile
  return gulp
    .src("source.js")
    .pipe(babel({ presets: ["@babel/env"] }))
    .pipe(uglify())
    .pipe(rename("app.min.js"))  // Rename the minified file
    .pipe(gulp.dest("./"));      // Output to the current folder
});

