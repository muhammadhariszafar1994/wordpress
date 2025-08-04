/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!***********************************************!*\
  !*** ./src/assets/js/prevent-content-copy.js ***!
  \***********************************************/
jQuery(document).ready(function ($) {
  var Prevent_Content_Copy = Prevent_Content_Copy || {};
  Prevent_Content_Copy.init = function () {
    $('body').bind('contextmenu cut copy', function (e) {
      e.preventDefault();
      return false;
    });
    $('body').bind('paste', function (e) {
      const is_password_field = $(e.target).is('[type="password"]');
      if (!is_password_field) {
        e.preventDefault();
        return false;
      }
    });
  };
  Prevent_Content_Copy.init();
});
/******/ })()
;