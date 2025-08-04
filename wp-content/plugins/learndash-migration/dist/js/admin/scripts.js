/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/isset-php/src/index.js":
/*!*********************************************!*\
  !*** ./node_modules/isset-php/src/index.js ***!
  \*********************************************/
/***/ (function(module) {

/**
 * Safe and simple PHP isset() for JavaScript.
 *
 * Determine if a variable is considered set, this means if a variable is
 * declared and is different than null or undefined.
 *
 * If a variable has been unset with the delete keyword, it is no longer
 * considered to be set.
 *
 * isset() will return false when checking a variable that has been assigned to
 * null or undefined. Also note that a null character ("\0") is not equivalent
 * to the JavaScript null constant.
 *
 * If multiple parameters are supplied then isset() will return true only if
 * all of the parameters are considered set. Evaluation goes from left to right
 * and stops as soon as an unset variable is encountered.
 *
 * @param {Function} accessor Accessor functions returning the variable to be checked
 * @param {...Function} accessors Further accessors functions
 * @returns {Boolean} False if any accessor returns null or undefined
 */
module.exports = function isset () {
  if (!arguments.length) throw new TypeError('isset requires at least one accessor function')
  return Array.prototype.slice.call(arguments).every((accessor) => {
    if (typeof accessor !== 'function') throw new TypeError('isset requires accessors to be functions')
    try {
      const value = accessor()
      return value !== undefined && value !== null
    } catch (e) {
      return false
    }
  })
}


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!****************************************!*\
  !*** ./src/assets/js/admin/scripts.js ***!
  \****************************************/
const isset = __webpack_require__(/*! isset-php */ "./node_modules/isset-php/src/index.js");
(function ($) {
  const migrate = document.getElementById('migrate'),
    courseEl = $('#learndash_settings_migration_course'),
    sourceEl = $('#learndash_settings_migration_source');
  migrate.addEventListener('click', function (e) {
    e.preventDefault();
    if (sourceEl.val() === null || sourceEl.val().length < 1) {
      alert(LearnDashMigrationAdmin.text.error_empty_source);
      return false;
    }
    if (courseEl.val() === null || courseEl.val().length < 1) {
      alert(LearnDashMigrationAdmin.text.error_empty_course);
      return false;
    }
    const loaderHTML = '<div class="loader"></div>';
    this.setAttribute('disabled', true);
    this.innerHTML = loaderHTML;
    this.classList.add('in-progress');
    const status = this.parentElement.querySelector('.status');
    if (status !== null) {
      status.remove();
    }
    this.insertAdjacentHTML('afterend', '<span class="status">' + LearnDashMigrationAdmin.text.progress_status_init + '</span>');

    // Init AJAX request for migration.
    initMigration();
  });
  window.addEventListener('load', function () {
    courseEl.select2({
      ajax: {
        url: LearnDashMigrationAdmin.ajaxurl,
        delay: 300,
        dataType: 'json',
        data: params => {
          return {
            action: LearnDashMigrationAdmin.action.get_courses,
            nonce: LearnDashMigrationAdmin.nonce.get_courses,
            post_type: LearnDashMigrationAdmin.course_post_types[sourceEl.val()],
            keyword: params.term,
            page: params.page
          };
        },
        processResults: response => {
          return {
            results: response.data.results,
            pagination: response.data.pagination
          };
        }
      },
      dropdownAutoWidth: true,
      placeholder: LearnDashMigrationAdmin.text.select_a_course,
      width: '100%'
    });
  });
  $('#learndash_settings_migration_source').on('change', function () {
    courseEl.val(null).trigger('change');
    if ($(this).val().length > 0) {
      $('#learndash_settings_migration_course_field').show();
    } else {
      $('#learndash_settings_migration_course_field').hide();
    }
  });
  const initMigration = (step = 1) => {
    const formData = new FormData(),
      source = document.getElementById('learndash_settings_migration_source').value,
      courseId = document.getElementById('learndash_settings_migration_course').value;
    formData.append('action', LearnDashMigrationAdmin.action.migrate);
    formData.append('nonce', LearnDashMigrationAdmin.nonce.migrate);
    formData.append('source', source);
    formData.append('course_id', courseId);
    formData.append('step', step);
    fetch(LearnDashMigrationAdmin.ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    }).then(response => response.json()).then(response => {
      if (isset(() => response.data.completed) && response.data.completed) {
        migrate.removeAttribute('disabled');
        migrate.innerHTML = LearnDashMigrationAdmin.text.migrate_course;
        migrate.classList.remove('in-progress');
        migrate.parentElement.querySelector('.status').innerHTML = LearnDashMigrationAdmin.text.progress_status_end;
        migrate.parentElement.querySelector('.new-course-url').href = decodeURI(response.data.new_course_url);
        setTimeout(() => {}, 200);
      } else {
        initMigration(source, step + 1);
      }
    }).catch(() => {});
  };
})(jQuery);
}();
/******/ })()
;