/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * Main field class.
 *
 * @since 1.4.0
 */
var Field = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     * @param {string} type
     */
    function Field($field, type) {
        _classCallCheck(this, Field);

        this.$field = $field;
        this.$wrapper = $field.closest('.fieldhelpers-field');
        this.type = type;
        this.name = this.$wrapper.attr('data-fieldhelpers-name');
        this.instance = this.$wrapper.attr('data-fieldhelpers-instance');

        this.getRepeater();

        this.getOptions();

        if (this.repeater) {

            this.repeaterSupport();
        }

        // Put in global scope for other methods to interact with it
        if (typeof RBM_FieldHelpers[this.instance]['fieldObjects'] == 'undefined') {
            RBM_FieldHelpers[this.instance]['fieldObjects'] = {};
        }

        if (typeof RBM_FieldHelpers[this.instance]['fieldObjects'][this.type] == 'undefined') {
            RBM_FieldHelpers[this.instance]['fieldObjects'][this.type] = {};
        }

        RBM_FieldHelpers[this.instance]['fieldObjects'][this.type][this.name] = this;
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     */


    _createClass(Field, [{
        key: 'initField',
        value: function initField() {}

        /**
         * Gets field options.
         *
         * @since 1.4.0
         */

    }, {
        key: 'getOptions',
        value: function getOptions() {

            this.options = {};

            if (typeof RBM_FieldHelpers[this.instance] === 'undefined') {

                console.error('Field Helpers Error: Data for ' + this.instance + ' instance cannot be found.');
                return;
            }

            if (this.repeater) {

                if (typeof RBM_FieldHelpers[this.instance]['repeaterFields'][this.repeater] === 'undefined') {

                    console.error('Field Helpers Error: Data for repeater ' + this.type + ' sub-fields cannot be found.');
                    return;
                }

                if (typeof RBM_FieldHelpers[this.instance]['repeaterFields'][this.repeater][this.name] === 'undefined') {

                    console.error('Field Helpers Error: Cannot find field options for repeater ' + this.type + ' sub-field with name: ' + this.name + '.');
                    return;
                }

                this.options = RBM_FieldHelpers[this.instance]['repeaterFields'][this.repeater][this.name];
            } else {

                if (typeof RBM_FieldHelpers[this.instance][this.type] === 'undefined') {

                    console.error('Field Helpers Error: Data for ' + this.type + ' fields cannot be found.');
                    return;
                }

                if (typeof RBM_FieldHelpers[this.instance][this.type][this.name] === 'undefined') {

                    console.error('Field Helpers Error: Cannot find field options for ' + this.type + ' field with name: ' + this.name + '.');
                    return;
                }

                this.options = RBM_FieldHelpers[this.instance][this.type][this.name];
            }
        }

        /**
         * If field is in a Repeater, it will need support.
         *
         * @since 1.4.0
         */

    }, {
        key: 'getRepeater',
        value: function getRepeater() {

            if (this.$field.closest('[data-fieldhelpers-field-repeater]').length) {

                this.$repeater = this.$field.parent().closest('[data-fieldhelpers-field-repeater]');
                this.repeater = this.$repeater.closest('.fieldhelpers-field-repeater').attr('data-fieldhelpers-name');
            }
        }

        /**
         * Runs some functions if inside a Repeater.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterSupport',
        value: function repeaterSupport() {
            var _this = this;

            // Triggers fields can utilize. Wrapped in anonymous to utilize self access.
            this.$repeater.on('repeater-before-init', function (event, $repeater, options) {
                _this.repeaterBeforeInit($repeater, options);
            });
            this.$repeater.on('repeater-init', function (event, $repeater, options) {
                _this.repeaterOnInit($repeater, options);
            });
            this.$repeater.on('repeater-before-add-item', function () {
                _this.repeaterBeforeAddItem();
            });
            this.$repeater.on('repeater-add-item', function () {
                _this.repeaterOnAddItem();
            });
            this.$field.closest('[data-repeater-item]').on('repeater-before-delete-item', function () {
                _this.repeaterBeforeDeleteSelf();
            });
            this.$repeater.on('repeater-before-delete-item', function () {
                _this.repeaterBeforeDeleteItem();
            });
            this.$repeater.on('repeater-delete-item', function () {
                _this.repeaterOnDeleteItem();
            });
            this.$repeater.find('.fieldhelpers-field-repeater-list').on('list-update', function () {
                _this.repeaterOnSort();
            });

            this.repeaterSetID();
            this.fieldCleanup();
        }

        /**
         * Fires before Repeater init.
         *
         * @since 1.5.0
         */

    }, {
        key: 'repeaterBeforeInit',
        value: function repeaterBeforeInit($repeater, options) {}

        /**
         * Fires on Repeater init.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterOnInit',
        value: function repeaterOnInit($repeater, options) {}

        /**
         * Fires before Repeater add item.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterBeforeAddItem',
        value: function repeaterBeforeAddItem() {}

        /**
         * Fires on Repeater add item.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterOnAddItem',
        value: function repeaterOnAddItem() {}

        /**
         * Fires before Repeater delete item (localized to self).
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterBeforeDeleteSelf',
        value: function repeaterBeforeDeleteSelf() {}

        /**
         * Fires before Repeater delete item.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterBeforeDeleteItem',
        value: function repeaterBeforeDeleteItem() {}

        /**
         * Fires on Repeater delete item.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterOnDeleteItem',
        value: function repeaterOnDeleteItem() {}

        /**
         * Fires on Repeat sort item.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterOnSort',
        value: function repeaterOnSort() {}

        /**
         * Sets the ID to be unique, based off the repeater item index.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterSetID',
        value: function repeaterSetID() {

            var index = this.$field.closest('[data-repeater-item]').index();
            var newID = this.options.id + '_' + index;

            this.$field.attr('id', newID);
        }

        /**
         * Cleans up after a repeater add/init.
         *
         * @since 1.4.0
         */

    }, {
        key: 'fieldCleanup',
        value: function fieldCleanup() {}

        /**
         * Sets the field to default. Override in child class if need different method.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setDefault',
        value: function setDefault() {

            if (this.options.default) {

                this.$field.val(this.options.default).change();
            }
        }
    }]);

    return Field;
}();

exports.default = Field;

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _fieldNumber = __webpack_require__(6);

var _fieldNumber2 = _interopRequireDefault(_fieldNumber);

var _fieldColorpicker = __webpack_require__(7);

var _fieldColorpicker2 = _interopRequireDefault(_fieldColorpicker);

var _fieldDatepicker = __webpack_require__(8);

var _fieldDatepicker2 = _interopRequireDefault(_fieldDatepicker);

var _fieldTimepicker = __webpack_require__(9);

var _fieldTimepicker2 = _interopRequireDefault(_fieldTimepicker);

var _fieldDatetimepicker = __webpack_require__(10);

var _fieldDatetimepicker2 = _interopRequireDefault(_fieldDatetimepicker);

var _fieldTable = __webpack_require__(11);

var _fieldTable2 = _interopRequireDefault(_fieldTable);

var _fieldMedia = __webpack_require__(12);

var _fieldMedia2 = _interopRequireDefault(_fieldMedia);

var _fieldList = __webpack_require__(13);

var _fieldList2 = _interopRequireDefault(_fieldList);

var _fieldRepeater = __webpack_require__(14);

var _fieldRepeater2 = _interopRequireDefault(_fieldRepeater);

var _fieldSelect = __webpack_require__(15);

var _fieldSelect2 = _interopRequireDefault(_fieldSelect);

var _fieldTextarea = __webpack_require__(16);

var _fieldTextarea2 = _interopRequireDefault(_fieldTextarea);

var _fieldCheckbox = __webpack_require__(17);

var _fieldCheckbox2 = _interopRequireDefault(_fieldCheckbox);

var _fieldRadio = __webpack_require__(18);

var _fieldRadio2 = _interopRequireDefault(_fieldRadio);

var _fieldToggle = __webpack_require__(19);

var _fieldToggle2 = _interopRequireDefault(_fieldToggle);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

/**
 * Handles all field initializations.
 *
 * @since 1.4.0
 */
var FieldsInitialize =

/**
 * Class constructor.
 *
 * @since 1.4.0
 *
 * @param {jQuery} $root Root element to initialize fields inside.
 */
function FieldsInitialize($root) {
    _classCallCheck(this, FieldsInitialize);

    this.fields = {
        checkbox: new _fieldCheckbox2.default($root),
        toggle: new _fieldToggle2.default($root),
        radio: new _fieldRadio2.default($root),
        select: new _fieldSelect2.default($root),
        textarea: new _fieldTextarea2.default($root),
        number: new _fieldNumber2.default($root),
        colorpicker: new _fieldColorpicker2.default($root),
        datepicker: new _fieldDatepicker2.default($root),
        timepicker: new _fieldTimepicker2.default($root),
        datetimepicker: new _fieldDatetimepicker2.default($root),
        table: new _fieldTable2.default($root),
        media: new _fieldMedia2.default($root),
        list: new _fieldList2.default($root),
        repeater: new _fieldRepeater2.default($root)
    };
};

exports.default = FieldsInitialize;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(3);


/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

__webpack_require__(4);

__webpack_require__(5);

var _fieldsInit = __webpack_require__(1);

var _fieldsInit2 = _interopRequireDefault(_fieldsInit);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

// Initialize app on jQuery Ready.
jQuery(function () {

	var Fields = new _fieldsInit2.default(jQuery(document));
	jQuery(document).trigger('rbm-field-helpers-ready');
});

// Re-init any fields within a container
window.rbmFHinitField = function ($root) {

	new _fieldsInit2.default($root);
};

// Grab Field Object from the RBM_FieldHelpers global in order to run methods on them
window.rbmFHgetFieldObject = function (name) {
	var instance = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	var fieldType = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;


	if (instance === null) {

		instance = jQuery('.fieldhelpers-field[data-fieldhelpers-name="' + name + '"]').data('fieldhelpers-instance');
	}

	if (fieldType === null) {

		var classNames = jQuery('.fieldhelpers-field[data-fieldhelpers-name="' + name + '"][data-fieldhelpers-instance="' + instance + '"]').first().attr('class');

		var test = /fieldhelpers-field-(\S*)/.exec(classNames);

		if (test !== null && test[1].length > 0) {

			fieldType = test[1];
		}
	}

	try {

		return RBM_FieldHelpers[instance]['fieldObjects'][fieldType][name];
	} catch (error) {

		if (instance === null || fieldType === null || typeof RBM_FieldHelpers[instance] == 'undefined' || typeof RBM_FieldHelpers[instance]['fieldObjects'] == 'undefined' || typeof RBM_FieldHelpers[instance]['fieldObjects'][fieldType] == 'undefined' || typeof RBM_FieldHelpers[instance]['fieldObjects'][fieldType][name] == 'undefined' || _typeof(RBM_FieldHelpers[instance]['fieldObjects'][fieldType][name].length) < 0) {
			console.error('Field Helpers Error: Field Object for "' + name + '" Not Found');
		}

		return false;
	}
};

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


// jquery.repeater version 1.2.1
// https://github.com/DubFriend/jquery.repeater
// (MIT) 09-10-2016
// Brian Detering <BDeterin@gmail.com> (http://www.briandetering.net/)
(function ($) {
    'use strict';

    var identity = function identity(x) {
        return x;
    };

    var isArray = function isArray(value) {
        return $.isArray(value);
    };

    var isObject = function isObject(value) {
        return !isArray(value) && value instanceof Object;
    };

    var isNumber = function isNumber(value) {
        return value instanceof Number;
    };

    var isFunction = function isFunction(value) {
        return value instanceof Function;
    };

    var indexOf = function indexOf(object, value) {
        return $.inArray(value, object);
    };

    var inArray = function inArray(array, value) {
        return indexOf(array, value) !== -1;
    };

    var foreach = function foreach(collection, callback) {
        for (var i in collection) {
            if (collection.hasOwnProperty(i)) {
                callback(collection[i], i, collection);
            }
        }
    };

    var last = function last(array) {
        return array[array.length - 1];
    };

    var argumentsToArray = function argumentsToArray(args) {
        return Array.prototype.slice.call(args);
    };

    var extend = function extend() {
        var extended = {};
        foreach(argumentsToArray(arguments), function (o) {
            foreach(o, function (val, key) {
                extended[key] = val;
            });
        });
        return extended;
    };

    var mapToArray = function mapToArray(collection, callback) {
        var mapped = [];
        foreach(collection, function (value, key, coll) {
            mapped.push(callback(value, key, coll));
        });
        return mapped;
    };

    var mapToObject = function mapToObject(collection, callback, keyCallback) {
        var mapped = {};
        foreach(collection, function (value, key, coll) {
            key = keyCallback ? keyCallback(key, value) : key;
            mapped[key] = callback(value, key, coll);
        });
        return mapped;
    };

    var map = function map(collection, callback, keyCallback) {
        return isArray(collection) ? mapToArray(collection, callback) : mapToObject(collection, callback, keyCallback);
    };

    var pluck = function pluck(arrayOfObjects, key) {
        return map(arrayOfObjects, function (val) {
            return val[key];
        });
    };

    var filter = function filter(collection, callback) {
        var filtered;

        if (isArray(collection)) {
            filtered = [];
            foreach(collection, function (val, key, coll) {
                if (callback(val, key, coll)) {
                    filtered.push(val);
                }
            });
        } else {
            filtered = {};
            foreach(collection, function (val, key, coll) {
                if (callback(val, key, coll)) {
                    filtered[key] = val;
                }
            });
        }

        return filtered;
    };

    var call = function call(collection, functionName, args) {
        return map(collection, function (object, name) {
            return object[functionName].apply(object, args || []);
        });
    };

    //execute callback immediately and at most one time on the minimumInterval,
    //ignore block attempts
    var throttle = function throttle(minimumInterval, callback) {
        var timeout = null;
        return function () {
            var that = this,
                args = arguments;
            if (timeout === null) {
                timeout = setTimeout(function () {
                    timeout = null;
                }, minimumInterval);
                callback.apply(that, args);
            }
        };
    };

    var mixinPubSub = function mixinPubSub(object) {
        object = object || {};
        var topics = {};

        object.publish = function (topic, data) {
            foreach(topics[topic], function (callback) {
                callback(data);
            });
        };

        object.subscribe = function (topic, callback) {
            topics[topic] = topics[topic] || [];
            topics[topic].push(callback);
        };

        object.unsubscribe = function (callback) {
            foreach(topics, function (subscribers) {
                var index = indexOf(subscribers, callback);
                if (index !== -1) {
                    subscribers.splice(index, 1);
                }
            });
        };

        return object;
    };

    // jquery.input version 0.0.0
    // https://github.com/DubFriend/jquery.input
    // (MIT) 09-04-2014
    // Brian Detering <BDeterin@gmail.com> (http://www.briandetering.net/)
    (function ($) {
        'use strict';

        var createBaseInput = function createBaseInput(fig, my) {
            var self = mixinPubSub(),
                $self = fig.$;

            self.getType = function () {
                throw 'implement me (return type. "text", "radio", etc.)';
            };

            self.$ = function (selector) {
                return selector ? $self.find(selector) : $self;
            };

            self.disable = function () {
                self.$().prop('disabled', true);
                self.publish('isEnabled', false);
            };

            self.enable = function () {
                self.$().prop('disabled', false);
                self.publish('isEnabled', true);
            };

            my.equalTo = function (a, b) {
                return a === b;
            };

            my.publishChange = function () {
                var oldValue;
                return function (e, domElement) {
                    var newValue = self.get();
                    if (!my.equalTo(newValue, oldValue)) {
                        self.publish('change', { e: e, domElement: domElement });
                    }
                    oldValue = newValue;
                };
            }();

            return self;
        };

        var createInput = function createInput(fig, my) {
            var self = createBaseInput(fig, my);

            self.get = function () {
                return self.$().val();
            };

            self.set = function (newValue) {
                self.$().val(newValue);
            };

            self.clear = function () {
                self.set('');
            };

            my.buildSetter = function (callback) {
                return function (newValue) {
                    callback.call(self, newValue);
                };
            };

            return self;
        };

        var inputEqualToArray = function inputEqualToArray(a, b) {
            a = isArray(a) ? a : [a];
            b = isArray(b) ? b : [b];

            var isEqual = true;
            if (a.length !== b.length) {
                isEqual = false;
            } else {
                foreach(a, function (value) {
                    if (!inArray(b, value)) {
                        isEqual = false;
                    }
                });
            }

            return isEqual;
        };

        var createInputButton = function createInputButton(fig) {
            var my = {},
                self = createInput(fig, my);

            self.getType = function () {
                return 'button';
            };

            self.$().on('change', function (e) {
                my.publishChange(e, this);
            });

            return self;
        };

        var createInputCheckbox = function createInputCheckbox(fig) {
            var my = {},
                self = createInput(fig, my);

            self.getType = function () {
                return 'checkbox';
            };

            self.get = function () {
                var values = [];
                self.$().filter(':checked').each(function () {
                    values.push($(this).val());
                });
                return values;
            };

            self.set = function (newValues) {
                newValues = isArray(newValues) ? newValues : [newValues];

                self.$().each(function () {
                    $(this).prop('checked', false);
                });

                foreach(newValues, function (value) {
                    self.$().filter('[value="' + value + '"]').prop('checked', true);
                });
            };

            my.equalTo = inputEqualToArray;

            self.$().change(function (e) {
                my.publishChange(e, this);
            });

            return self;
        };

        var createInputEmail = function createInputEmail(fig) {
            var my = {},
                self = createInputText(fig, my);

            self.getType = function () {
                return 'email';
            };

            return self;
        };

        var createInputFile = function createInputFile(fig) {
            var my = {},
                self = createBaseInput(fig, my);

            self.getType = function () {
                return 'file';
            };

            self.get = function () {
                return last(self.$().val().split('\\'));
            };

            self.clear = function () {
                // http://stackoverflow.com/questions/1043957/clearing-input-type-file-using-jquery
                this.$().each(function () {
                    $(this).wrap('<form>').closest('form').get(0).reset();
                    $(this).unwrap();
                });
            };

            self.$().change(function (e) {
                my.publishChange(e, this);
                // self.publish('change', self);
            });

            return self;
        };

        var createInputHidden = function createInputHidden(fig) {
            var my = {},
                self = createInput(fig, my);

            self.getType = function () {
                return 'hidden';
            };

            self.$().change(function (e) {
                my.publishChange(e, this);
            });

            return self;
        };
        var createInputMultipleFile = function createInputMultipleFile(fig) {
            var my = {},
                self = createBaseInput(fig, my);

            self.getType = function () {
                return 'file[multiple]';
            };

            self.get = function () {
                // http://stackoverflow.com/questions/14035530/how-to-get-value-of-html-5-multiple-file-upload-variable-using-jquery
                var fileListObject = self.$().get(0).files || [],
                    names = [],
                    i;

                for (i = 0; i < (fileListObject.length || 0); i += 1) {
                    names.push(fileListObject[i].name);
                }

                return names;
            };

            self.clear = function () {
                // http://stackoverflow.com/questions/1043957/clearing-input-type-file-using-jquery
                this.$().each(function () {
                    $(this).wrap('<form>').closest('form').get(0).reset();
                    $(this).unwrap();
                });
            };

            self.$().change(function (e) {
                my.publishChange(e, this);
            });

            return self;
        };

        var createInputMultipleSelect = function createInputMultipleSelect(fig) {
            var my = {},
                self = createInput(fig, my);

            self.getType = function () {
                return 'select[multiple]';
            };

            self.get = function () {
                return self.$().val() || [];
            };

            self.set = function (newValues) {
                self.$().val(newValues === '' ? [] : isArray(newValues) ? newValues : [newValues]);
            };

            my.equalTo = inputEqualToArray;

            self.$().change(function (e) {
                my.publishChange(e, this);
            });

            return self;
        };

        var createInputPassword = function createInputPassword(fig) {
            var my = {},
                self = createInputText(fig, my);

            self.getType = function () {
                return 'password';
            };

            return self;
        };

        var createInputRadio = function createInputRadio(fig) {
            var my = {},
                self = createInput(fig, my);

            self.getType = function () {
                return 'radio';
            };

            self.get = function () {
                return self.$().filter(':checked').val() || null;
            };

            self.set = function (newValue) {
                if (!newValue) {
                    self.$().each(function () {
                        $(this).prop('checked', false);
                    });
                } else {
                    self.$().filter('[value="' + newValue + '"]').prop('checked', true);
                }
            };

            self.$().change(function (e) {
                my.publishChange(e, this);
            });

            return self;
        };

        var createInputRange = function createInputRange(fig) {
            var my = {},
                self = createInput(fig, my);

            self.getType = function () {
                return 'range';
            };

            self.$().change(function (e) {
                my.publishChange(e, this);
            });

            return self;
        };

        var createInputSelect = function createInputSelect(fig) {
            var my = {},
                self = createInput(fig, my);

            self.getType = function () {
                return 'select';
            };

            self.$().change(function (e) {
                my.publishChange(e, this);
            });

            return self;
        };

        var createInputText = function createInputText(fig) {
            var my = {},
                self = createInput(fig, my);

            self.getType = function () {
                return 'text';
            };

            self.$().on('change keyup keydown', function (e) {
                my.publishChange(e, this);
            });

            return self;
        };

        var createInputTextarea = function createInputTextarea(fig) {
            var my = {},
                self = createInput(fig, my);

            self.getType = function () {
                return 'textarea';
            };

            self.$().on('change keyup keydown', function (e) {
                my.publishChange(e, this);
            });

            return self;
        };

        var createInputURL = function createInputURL(fig) {
            var my = {},
                self = createInputText(fig, my);

            self.getType = function () {
                return 'url';
            };

            return self;
        };

        var buildFormInputs = function buildFormInputs(fig) {
            var inputs = {},
                $self = fig.$;

            var constructor = fig.constructorOverride || {
                button: createInputButton,
                text: createInputText,
                url: createInputURL,
                email: createInputEmail,
                password: createInputPassword,
                range: createInputRange,
                textarea: createInputTextarea,
                select: createInputSelect,
                'select[multiple]': createInputMultipleSelect,
                radio: createInputRadio,
                checkbox: createInputCheckbox,
                file: createInputFile,
                'file[multiple]': createInputMultipleFile,
                hidden: createInputHidden
            };

            var addInputsBasic = function addInputsBasic(type, selector) {
                var $input = isObject(selector) ? selector : $self.find(selector);

                $input.each(function () {
                    var name = $(this).attr('name');
                    inputs[name] = constructor[type]({
                        $: $(this)
                    });
                });
            };

            var addInputsGroup = function addInputsGroup(type, selector) {
                var names = [],
                    $input = isObject(selector) ? selector : $self.find(selector);

                if (isObject(selector)) {
                    inputs[$input.attr('name')] = constructor[type]({
                        $: $input
                    });
                } else {
                    // group by name attribute
                    $input.each(function () {
                        if (indexOf(names, $(this).attr('name')) === -1) {
                            names.push($(this).attr('name'));
                        }
                    });

                    foreach(names, function (name) {
                        inputs[name] = constructor[type]({
                            $: $self.find('input[name="' + name + '"]')
                        });
                    });
                }
            };

            if ($self.is('input, select, textarea')) {
                if ($self.is('input[type="button"], button, input[type="submit"]')) {
                    addInputsBasic('button', $self);
                } else if ($self.is('textarea')) {
                    addInputsBasic('textarea', $self);
                } else if ($self.is('input[type="text"]') || $self.is('input') && !$self.attr('type')) {
                    addInputsBasic('text', $self);
                } else if ($self.is('input[type="password"]')) {
                    addInputsBasic('password', $self);
                } else if ($self.is('input[type="email"]')) {
                    addInputsBasic('email', $self);
                } else if ($self.is('input[type="url"]')) {
                    addInputsBasic('url', $self);
                } else if ($self.is('input[type="range"]')) {
                    addInputsBasic('range', $self);
                } else if ($self.is('select')) {
                    if ($self.is('[multiple]')) {
                        addInputsBasic('select[multiple]', $self);
                    } else {
                        addInputsBasic('select', $self);
                    }
                } else if ($self.is('input[type="file"]')) {
                    if ($self.is('[multiple]')) {
                        addInputsBasic('file[multiple]', $self);
                    } else {
                        addInputsBasic('file', $self);
                    }
                } else if ($self.is('input[type="hidden"]')) {
                    addInputsBasic('hidden', $self);
                } else if ($self.is('input[type="radio"]')) {
                    addInputsGroup('radio', $self);
                } else if ($self.is('input[type="checkbox"]')) {
                    addInputsGroup('checkbox', $self);
                } else {
                    //in all other cases default to a "text" input interface.
                    addInputsBasic('text', $self);
                }
            } else {
                addInputsBasic('button', 'input[type="button"], button, input[type="submit"]');
                addInputsBasic('text', 'input[type="text"]');
                addInputsBasic('password', 'input[type="password"]');
                addInputsBasic('email', 'input[type="email"]');
                addInputsBasic('url', 'input[type="url"]');
                addInputsBasic('range', 'input[type="range"]');
                addInputsBasic('textarea', 'textarea');
                addInputsBasic('select', 'select:not([multiple])');
                addInputsBasic('select[multiple]', 'select[multiple]');
                addInputsBasic('file', 'input[type="file"]:not([multiple])');
                addInputsBasic('file[multiple]', 'input[type="file"][multiple]');
                addInputsBasic('hidden', 'input[type="hidden"]');
                addInputsGroup('radio', 'input[type="radio"]');
                addInputsGroup('checkbox', 'input[type="checkbox"]');
            }

            return inputs;
        };

        $.fn.inputVal = function (newValue) {
            var $self = $(this);

            var inputs = buildFormInputs({ $: $self });

            if ($self.is('input, textarea, select')) {
                if (typeof newValue === 'undefined') {
                    return inputs[$self.attr('name')].get();
                } else {
                    inputs[$self.attr('name')].set(newValue);
                    return $self;
                }
            } else {
                if (typeof newValue === 'undefined') {
                    return call(inputs, 'get');
                } else {
                    foreach(newValue, function (value, inputName) {
                        inputs[inputName].set(value);
                    });
                    return $self;
                }
            }
        };

        $.fn.inputOnChange = function (callback) {
            var $self = $(this);
            var inputs = buildFormInputs({ $: $self });
            foreach(inputs, function (input) {
                input.subscribe('change', function (data) {
                    callback.call(data.domElement, data.e);
                });
            });
            return $self;
        };

        $.fn.inputDisable = function () {
            var $self = $(this);
            call(buildFormInputs({ $: $self }), 'disable');
            return $self;
        };

        $.fn.inputEnable = function () {
            var $self = $(this);
            call(buildFormInputs({ $: $self }), 'enable');
            return $self;
        };

        $.fn.inputClear = function () {
            var $self = $(this);
            call(buildFormInputs({ $: $self }), 'clear');
            return $self;
        };
    })(jQuery);

    $.fn.repeaterVal = function () {
        var parse = function parse(raw) {
            var parsed = [];

            foreach(raw, function (val, key) {
                var parsedKey = [];
                if (key !== "undefined") {
                    parsedKey.push(key.match(/^[^\[]*/)[0]);
                    parsedKey = parsedKey.concat(map(key.match(/\[[^\]]*\]/g), function (bracketed) {
                        return bracketed.replace(/[\[\]]/g, '');
                    }));

                    parsed.push({
                        val: val,
                        key: parsedKey
                    });
                }
            });

            return parsed;
        };

        var build = function build(parsed) {
            if (parsed.length === 1 && (parsed[0].key.length === 0 || parsed[0].key.length === 1 && !parsed[0].key[0])) {
                return parsed[0].val;
            }

            foreach(parsed, function (p) {
                p.head = p.key.shift();
            });

            var grouped = function () {
                var grouped = {};

                foreach(parsed, function (p) {
                    if (!grouped[p.head]) {
                        grouped[p.head] = [];
                    }
                    grouped[p.head].push(p);
                });

                return grouped;
            }();

            var built;

            if (/^[0-9]+$/.test(parsed[0].head)) {
                built = [];
                foreach(grouped, function (group) {
                    built.push(build(group));
                });
            } else {
                built = {};
                foreach(grouped, function (group, key) {
                    built[key] = build(group);
                });
            }

            return built;
        };

        return build(parse($(this).inputVal()));
    };

    $.fn.repeater = function (fig) {
        fig = fig || {};

        var setList;

        $(this).each(function () {

            var $self = $(this);

            var show = fig.show || function () {
                $(this).show();
            };

            var hide = fig.hide || function (removeElement) {
                removeElement();
            };

            var $list = $self.find('[data-repeater-list]').first();

            var $filterNested = function $filterNested($items, repeaters) {
                return $items.filter(function () {
                    return repeaters ? $(this).closest(pluck(repeaters, 'selector').join(',')).length === 0 : true;
                });
            };

            var $items = function $items() {
                return $filterNested($list.find('[data-repeater-item]'), fig.repeaters);
            };

            var $itemTemplate = $list.find('[data-repeater-item]').first().clone().hide();

            var $firstDeleteButton = $filterNested($filterNested($(this).find('[data-repeater-item]'), fig.repeaters).first().find('[data-repeater-delete]'), fig.repeaters);

            if (fig.isFirstItemUndeletable && $firstDeleteButton) {
                $firstDeleteButton.remove();
            }

            var getGroupName = function getGroupName() {
                var groupName = $list.data('repeater-list');
                return fig.$parent ? fig.$parent.data('item-name') + '[' + groupName + ']' : groupName;
            };

            var initNested = function initNested($listItems) {
                if (fig.repeaters) {
                    $listItems.each(function () {
                        var $item = $(this);
                        foreach(fig.repeaters, function (nestedFig) {
                            $item.find(nestedFig.selector).repeater(extend(nestedFig, { $parent: $item }));
                        });
                    });
                }
            };

            var $foreachRepeaterInItem = function $foreachRepeaterInItem(repeaters, $item, cb) {
                if (repeaters) {
                    foreach(repeaters, function (nestedFig) {
                        cb.call($item.find(nestedFig.selector)[0], nestedFig);
                    });
                }
            };

            var setIndexes = function setIndexes($items, groupName, repeaters) {
                $items.each(function (index) {
                    var $item = $(this);
                    $item.data('item-name', groupName + '[' + index + ']');
                    $filterNested($item.find('[name]'), repeaters).each(function () {
                        var $input = $(this);
                        // match non empty brackets (ex: "[foo]")
                        var matches = $input.attr('name').match(/\[[^\]]+\]/g);

                        var name = matches ?
                        // strip "[" and "]" characters
                        last(matches).replace(/\[|\]/g, '') : $input.attr('name');

                        var newName = groupName + '[' + index + '][' + name + ']' + ($input.is(':checkbox') || $input.attr('multiple') ? '[]' : '');

                        $input.attr('name', newName);

                        $foreachRepeaterInItem(repeaters, $item, function (nestedFig) {
                            var $repeater = $(this);
                            setIndexes($filterNested($repeater.find('[data-repeater-item]'), nestedFig.repeaters || []), groupName + '[' + index + ']' + '[' + $repeater.find('[data-repeater-list]').first().data('repeater-list') + ']', nestedFig.repeaters);
                        });
                    });
                });

                $list.find('input[name][checked]').removeAttr('checked').prop('checked', true);
            };

            setIndexes($items(), getGroupName(), fig.repeaters);
            initNested($items());
            if (fig.initEmpty) {
                $items().remove();
            }

            if (fig.ready) {
                fig.ready(function () {
                    setIndexes($items(), getGroupName(), fig.repeaters);
                });
            }

            var appendItem = function () {
                var setItemsValues = function setItemsValues($item, data, repeaters) {
                    if (data || fig.defaultValues) {
                        var inputNames = {};
                        $filterNested($item.find('[name]'), repeaters).each(function () {
                            var key = $(this).attr('name').match(/\[([^\]]*)(\]|\]\[\])$/)[1];
                            inputNames[key] = $(this).attr('name');
                        });

                        $item.inputVal(map(filter(data || fig.defaultValues, function (val, name) {
                            return inputNames[name];
                        }), identity, function (name) {
                            return inputNames[name];
                        }));
                    }

                    $foreachRepeaterInItem(repeaters, $item, function (nestedFig) {
                        var $repeater = $(this);
                        $filterNested($repeater.find('[data-repeater-item]'), nestedFig.repeaters).each(function () {
                            var fieldName = $repeater.find('[data-repeater-list]').data('repeater-list');
                            if (data && data[fieldName]) {
                                var $template = $(this).clone();
                                $repeater.find('[data-repeater-item]').remove();
                                foreach(data[fieldName], function (data) {
                                    var $item = $template.clone();
                                    setItemsValues($item, data, nestedFig.repeaters || []);
                                    $repeater.find('[data-repeater-list]').append($item);
                                });
                            } else {
                                setItemsValues($(this), nestedFig.defaultValues, nestedFig.repeaters || []);
                            }
                        });
                    });
                };

                return function ($item, data) {
                    $list.append($item);
                    setIndexes($items(), getGroupName(), fig.repeaters);
                    $item.find('[name]').each(function () {
                        $(this).inputClear();
                    });
                    setItemsValues($item, data || fig.defaultValues, fig.repeaters);
                };
            }();

            var addItem = function addItem(data) {
                var $item = $itemTemplate.clone();
                appendItem($item, data);
                if (fig.repeaters) {
                    initNested($item);
                }
                show.call($item.get(0));
            };

            setList = function setList(rows) {
                $items().remove();
                foreach(rows, addItem);
            };

            $filterNested($self.find('[data-repeater-create]'), fig.repeaters).click(function () {
                addItem();
            });

            $list.on('click', '[data-repeater-delete]', function () {
                var self = $(this).closest('[data-repeater-item]').get(0);
                hide.call(self, function () {
                    $(self).remove();
                    setIndexes($items(), getGroupName(), fig.repeaters);
                });
            });
        });

        this.setList = setList;

        return this;
    };
})(jQuery);

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_RESULT__;

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/* flatpickr v4.6.2, @license MIT */
(function (global, factory) {
    ( false ? 'undefined' : _typeof(exports)) === 'object' && typeof module !== 'undefined' ? module.exports = factory() :  true ? !(__WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.call(exports, __webpack_require__, exports, module)) :
				__WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__)) : (global = global || self, global.flatpickr = factory());
})(undefined, function () {
    'use strict';

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation. All rights reserved.
    Licensed under the Apache License, Version 2.0 (the "License"); you may not use
    this file except in compliance with the License. You may obtain a copy of the
    License at http://www.apache.org/licenses/LICENSE-2.0
      THIS CODE IS PROVIDED ON AN *AS IS* BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    KIND, EITHER EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION ANY IMPLIED
    WARRANTIES OR CONDITIONS OF TITLE, FITNESS FOR A PARTICULAR PURPOSE,
    MERCHANTABLITY OR NON-INFRINGEMENT.
      See the Apache Version 2.0 License for specific language governing permissions
    and limitations under the License.
    ***************************************************************************** */

    var _assign = function __assign() {
        _assign = Object.assign || function __assign(t) {
            for (var s, i = 1, n = arguments.length; i < n; i++) {
                s = arguments[i];
                for (var p in s) {
                    if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
                }
            }
            return t;
        };
        return _assign.apply(this, arguments);
    };

    var HOOKS = ["onChange", "onClose", "onDayCreate", "onDestroy", "onKeyDown", "onMonthChange", "onOpen", "onParseConfig", "onReady", "onValueUpdate", "onYearChange", "onPreCalendarPosition"];
    var defaults = {
        _disable: [],
        _enable: [],
        allowInput: false,
        altFormat: "F j, Y",
        altInput: false,
        altInputClass: "form-control input",
        animate: (typeof window === 'undefined' ? 'undefined' : _typeof(window)) === "object" && window.navigator.userAgent.indexOf("MSIE") === -1,
        ariaDateFormat: "F j, Y",
        clickOpens: true,
        closeOnSelect: true,
        conjunction: ", ",
        dateFormat: "Y-m-d",
        defaultHour: 12,
        defaultMinute: 0,
        defaultSeconds: 0,
        disable: [],
        disableMobile: false,
        enable: [],
        enableSeconds: false,
        enableTime: false,
        errorHandler: function errorHandler(err) {
            return typeof console !== "undefined" && console.warn(err);
        },
        getWeek: function getWeek(givenDate) {
            var date = new Date(givenDate.getTime());
            date.setHours(0, 0, 0, 0);
            // Thursday in current week decides the year.
            date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
            // January 4 is always in week 1.
            var week1 = new Date(date.getFullYear(), 0, 4);
            // Adjust to Thursday in week 1 and count number of weeks from date to week1.
            return 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
        },
        hourIncrement: 1,
        ignoredFocusElements: [],
        inline: false,
        locale: "default",
        minuteIncrement: 5,
        mode: "single",
        monthSelectorType: "dropdown",
        nextArrow: "<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M13.207 8.472l-7.854 7.854-0.707-0.707 7.146-7.146-7.146-7.148 0.707-0.707 7.854 7.854z' /></svg>",
        noCalendar: false,
        now: new Date(),
        onChange: [],
        onClose: [],
        onDayCreate: [],
        onDestroy: [],
        onKeyDown: [],
        onMonthChange: [],
        onOpen: [],
        onParseConfig: [],
        onReady: [],
        onValueUpdate: [],
        onYearChange: [],
        onPreCalendarPosition: [],
        plugins: [],
        position: "auto",
        positionElement: undefined,
        prevArrow: "<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M5.207 8.471l7.146 7.147-0.707 0.707-7.853-7.854 7.854-7.853 0.707 0.707-7.147 7.146z' /></svg>",
        shorthandCurrentMonth: false,
        showMonths: 1,
        static: false,
        time_24hr: false,
        weekNumbers: false,
        wrap: false
    };

    var english = {
        weekdays: {
            shorthand: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            longhand: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
        },
        months: {
            shorthand: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            longhand: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
        },
        daysInMonth: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
        firstDayOfWeek: 0,
        ordinal: function ordinal(nth) {
            var s = nth % 100;
            if (s > 3 && s < 21) return "th";
            switch (s % 10) {
                case 1:
                    return "st";
                case 2:
                    return "nd";
                case 3:
                    return "rd";
                default:
                    return "th";
            }
        },
        rangeSeparator: " to ",
        weekAbbreviation: "Wk",
        scrollTitle: "Scroll to increment",
        toggleTitle: "Click to toggle",
        amPM: ["AM", "PM"],
        yearAriaLabel: "Year",
        hourAriaLabel: "Hour",
        minuteAriaLabel: "Minute",
        time_24hr: false
    };

    var pad = function pad(number) {
        return ("0" + number).slice(-2);
    };
    var int = function int(bool) {
        return bool === true ? 1 : 0;
    };
    /* istanbul ignore next */
    function debounce(func, wait, immediate) {
        if (immediate === void 0) {
            immediate = false;
        }
        var timeout;
        return function () {
            var context = this,
                args = arguments;
            timeout !== null && clearTimeout(timeout);
            timeout = window.setTimeout(function () {
                timeout = null;
                if (!immediate) func.apply(context, args);
            }, wait);
            if (immediate && !timeout) func.apply(context, args);
        };
    }
    var arrayify = function arrayify(obj) {
        return obj instanceof Array ? obj : [obj];
    };

    function toggleClass(elem, className, bool) {
        if (bool === true) return elem.classList.add(className);
        elem.classList.remove(className);
    }
    function createElement(tag, className, content) {
        var e = window.document.createElement(tag);
        className = className || "";
        content = content || "";
        e.className = className;
        if (content !== undefined) e.textContent = content;
        return e;
    }
    function clearNode(node) {
        while (node.firstChild) {
            node.removeChild(node.firstChild);
        }
    }
    function findParent(node, condition) {
        if (condition(node)) return node;else if (node.parentNode) return findParent(node.parentNode, condition);
        return undefined; // nothing found
    }
    function createNumberInput(inputClassName, opts) {
        var wrapper = createElement("div", "numInputWrapper"),
            numInput = createElement("input", "numInput " + inputClassName),
            arrowUp = createElement("span", "arrowUp"),
            arrowDown = createElement("span", "arrowDown");
        if (navigator.userAgent.indexOf("MSIE 9.0") === -1) {
            numInput.type = "number";
        } else {
            numInput.type = "text";
            numInput.pattern = "\\d*";
        }
        if (opts !== undefined) for (var key in opts) {
            numInput.setAttribute(key, opts[key]);
        }wrapper.appendChild(numInput);
        wrapper.appendChild(arrowUp);
        wrapper.appendChild(arrowDown);
        return wrapper;
    }
    function getEventTarget(event) {
        if (typeof event.composedPath === "function") {
            var path = event.composedPath();
            return path[0];
        }
        return event.target;
    }

    var doNothing = function doNothing() {
        return undefined;
    };
    var monthToStr = function monthToStr(monthNumber, shorthand, locale) {
        return locale.months[shorthand ? "shorthand" : "longhand"][monthNumber];
    };
    var revFormat = {
        D: doNothing,
        F: function F(dateObj, monthName, locale) {
            dateObj.setMonth(locale.months.longhand.indexOf(monthName));
        },
        G: function G(dateObj, hour) {
            dateObj.setHours(parseFloat(hour));
        },
        H: function H(dateObj, hour) {
            dateObj.setHours(parseFloat(hour));
        },
        J: function J(dateObj, day) {
            dateObj.setDate(parseFloat(day));
        },
        K: function K(dateObj, amPM, locale) {
            dateObj.setHours(dateObj.getHours() % 12 + 12 * int(new RegExp(locale.amPM[1], "i").test(amPM)));
        },
        M: function M(dateObj, shortMonth, locale) {
            dateObj.setMonth(locale.months.shorthand.indexOf(shortMonth));
        },
        S: function S(dateObj, seconds) {
            dateObj.setSeconds(parseFloat(seconds));
        },
        U: function U(_, unixSeconds) {
            return new Date(parseFloat(unixSeconds) * 1000);
        },
        W: function W(dateObj, weekNum, locale) {
            var weekNumber = parseInt(weekNum);
            var date = new Date(dateObj.getFullYear(), 0, 2 + (weekNumber - 1) * 7, 0, 0, 0, 0);
            date.setDate(date.getDate() - date.getDay() + locale.firstDayOfWeek);
            return date;
        },
        Y: function Y(dateObj, year) {
            dateObj.setFullYear(parseFloat(year));
        },
        Z: function Z(_, ISODate) {
            return new Date(ISODate);
        },
        d: function d(dateObj, day) {
            dateObj.setDate(parseFloat(day));
        },
        h: function h(dateObj, hour) {
            dateObj.setHours(parseFloat(hour));
        },
        i: function i(dateObj, minutes) {
            dateObj.setMinutes(parseFloat(minutes));
        },
        j: function j(dateObj, day) {
            dateObj.setDate(parseFloat(day));
        },
        l: doNothing,
        m: function m(dateObj, month) {
            dateObj.setMonth(parseFloat(month) - 1);
        },
        n: function n(dateObj, month) {
            dateObj.setMonth(parseFloat(month) - 1);
        },
        s: function s(dateObj, seconds) {
            dateObj.setSeconds(parseFloat(seconds));
        },
        u: function u(_, unixMillSeconds) {
            return new Date(parseFloat(unixMillSeconds));
        },
        w: doNothing,
        y: function y(dateObj, year) {
            dateObj.setFullYear(2000 + parseFloat(year));
        }
    };
    var tokenRegex = {
        D: "(\\w+)",
        F: "(\\w+)",
        G: "(\\d\\d|\\d)",
        H: "(\\d\\d|\\d)",
        J: "(\\d\\d|\\d)\\w+",
        K: "",
        M: "(\\w+)",
        S: "(\\d\\d|\\d)",
        U: "(.+)",
        W: "(\\d\\d|\\d)",
        Y: "(\\d{4})",
        Z: "(.+)",
        d: "(\\d\\d|\\d)",
        h: "(\\d\\d|\\d)",
        i: "(\\d\\d|\\d)",
        j: "(\\d\\d|\\d)",
        l: "(\\w+)",
        m: "(\\d\\d|\\d)",
        n: "(\\d\\d|\\d)",
        s: "(\\d\\d|\\d)",
        u: "(.+)",
        w: "(\\d\\d|\\d)",
        y: "(\\d{2})"
    };
    var formats = {
        // get the date in UTC
        Z: function Z(date) {
            return date.toISOString();
        },
        // weekday name, short, e.g. Thu
        D: function D(date, locale, options) {
            return locale.weekdays.shorthand[formats.w(date, locale, options)];
        },
        // full month name e.g. January
        F: function F(date, locale, options) {
            return monthToStr(formats.n(date, locale, options) - 1, false, locale);
        },
        // padded hour 1-12
        G: function G(date, locale, options) {
            return pad(formats.h(date, locale, options));
        },
        // hours with leading zero e.g. 03
        H: function H(date) {
            return pad(date.getHours());
        },
        // day (1-30) with ordinal suffix e.g. 1st, 2nd
        J: function J(date, locale) {
            return locale.ordinal !== undefined ? date.getDate() + locale.ordinal(date.getDate()) : date.getDate();
        },
        // AM/PM
        K: function K(date, locale) {
            return locale.amPM[int(date.getHours() > 11)];
        },
        // shorthand month e.g. Jan, Sep, Oct, etc
        M: function M(date, locale) {
            return monthToStr(date.getMonth(), true, locale);
        },
        // seconds 00-59
        S: function S(date) {
            return pad(date.getSeconds());
        },
        // unix timestamp
        U: function U(date) {
            return date.getTime() / 1000;
        },
        W: function W(date, _, options) {
            return options.getWeek(date);
        },
        // full year e.g. 2016
        Y: function Y(date) {
            return date.getFullYear();
        },
        // day in month, padded (01-30)
        d: function d(date) {
            return pad(date.getDate());
        },
        // hour from 1-12 (am/pm)
        h: function h(date) {
            return date.getHours() % 12 ? date.getHours() % 12 : 12;
        },
        // minutes, padded with leading zero e.g. 09
        i: function i(date) {
            return pad(date.getMinutes());
        },
        // day in month (1-30)
        j: function j(date) {
            return date.getDate();
        },
        // weekday name, full, e.g. Thursday
        l: function l(date, locale) {
            return locale.weekdays.longhand[date.getDay()];
        },
        // padded month number (01-12)
        m: function m(date) {
            return pad(date.getMonth() + 1);
        },
        // the month number (1-12)
        n: function n(date) {
            return date.getMonth() + 1;
        },
        // seconds 0-59
        s: function s(date) {
            return date.getSeconds();
        },
        // Unix Milliseconds
        u: function u(date) {
            return date.getTime();
        },
        // number of the day of the week
        w: function w(date) {
            return date.getDay();
        },
        // last two digits of year e.g. 16 for 2016
        y: function y(date) {
            return String(date.getFullYear()).substring(2);
        }
    };

    var createDateFormatter = function createDateFormatter(_a) {
        var _b = _a.config,
            config = _b === void 0 ? defaults : _b,
            _c = _a.l10n,
            l10n = _c === void 0 ? english : _c;
        return function (dateObj, frmt, overrideLocale) {
            var locale = overrideLocale || l10n;
            if (config.formatDate !== undefined) {
                return config.formatDate(dateObj, frmt, locale);
            }
            return frmt.split("").map(function (c, i, arr) {
                return formats[c] && arr[i - 1] !== "\\" ? formats[c](dateObj, locale, config) : c !== "\\" ? c : "";
            }).join("");
        };
    };
    var createDateParser = function createDateParser(_a) {
        var _b = _a.config,
            config = _b === void 0 ? defaults : _b,
            _c = _a.l10n,
            l10n = _c === void 0 ? english : _c;
        return function (date, givenFormat, timeless, customLocale) {
            if (date !== 0 && !date) return undefined;
            var locale = customLocale || l10n;
            var parsedDate;
            var dateOrig = date;
            if (date instanceof Date) parsedDate = new Date(date.getTime());else if (typeof date !== "string" && date.toFixed !== undefined // timestamp
            )
                // create a copy
                parsedDate = new Date(date);else if (typeof date === "string") {
                // date string
                var format = givenFormat || (config || defaults).dateFormat;
                var datestr = String(date).trim();
                if (datestr === "today") {
                    parsedDate = new Date();
                    timeless = true;
                } else if (/Z$/.test(datestr) || /GMT$/.test(datestr) // datestrings w/ timezone
                ) parsedDate = new Date(date);else if (config && config.parseDate) parsedDate = config.parseDate(date, format);else {
                    parsedDate = !config || !config.noCalendar ? new Date(new Date().getFullYear(), 0, 1, 0, 0, 0, 0) : new Date(new Date().setHours(0, 0, 0, 0));
                    var matched = void 0,
                        ops = [];
                    for (var i = 0, matchIndex = 0, regexStr = ""; i < format.length; i++) {
                        var token_1 = format[i];
                        var isBackSlash = token_1 === "\\";
                        var escaped = format[i - 1] === "\\" || isBackSlash;
                        if (tokenRegex[token_1] && !escaped) {
                            regexStr += tokenRegex[token_1];
                            var match = new RegExp(regexStr).exec(date);
                            if (match && (matched = true)) {
                                ops[token_1 !== "Y" ? "push" : "unshift"]({
                                    fn: revFormat[token_1],
                                    val: match[++matchIndex]
                                });
                            }
                        } else if (!isBackSlash) regexStr += "."; // don't really care
                        ops.forEach(function (_a) {
                            var fn = _a.fn,
                                val = _a.val;
                            return parsedDate = fn(parsedDate, val, locale) || parsedDate;
                        });
                    }
                    parsedDate = matched ? parsedDate : undefined;
                }
            }
            /* istanbul ignore next */
            if (!(parsedDate instanceof Date && !isNaN(parsedDate.getTime()))) {
                config.errorHandler(new Error("Invalid date provided: " + dateOrig));
                return undefined;
            }
            if (timeless === true) parsedDate.setHours(0, 0, 0, 0);
            return parsedDate;
        };
    };
    /**
     * Compute the difference in dates, measured in ms
     */
    function compareDates(date1, date2, timeless) {
        if (timeless === void 0) {
            timeless = true;
        }
        if (timeless !== false) {
            return new Date(date1.getTime()).setHours(0, 0, 0, 0) - new Date(date2.getTime()).setHours(0, 0, 0, 0);
        }
        return date1.getTime() - date2.getTime();
    }
    var isBetween = function isBetween(ts, ts1, ts2) {
        return ts > Math.min(ts1, ts2) && ts < Math.max(ts1, ts2);
    };
    var duration = {
        DAY: 86400000
    };

    if (typeof Object.assign !== "function") {
        Object.assign = function (target) {
            var args = [];
            for (var _i = 1; _i < arguments.length; _i++) {
                args[_i - 1] = arguments[_i];
            }
            if (!target) {
                throw TypeError("Cannot convert undefined or null to object");
            }
            var _loop_1 = function _loop_1(source) {
                if (source) {
                    Object.keys(source).forEach(function (key) {
                        return target[key] = source[key];
                    });
                }
            };
            for (var _a = 0, args_1 = args; _a < args_1.length; _a++) {
                var source = args_1[_a];
                _loop_1(source);
            }
            return target;
        };
    }

    var DEBOUNCED_CHANGE_MS = 300;
    function FlatpickrInstance(element, instanceConfig) {
        var self = {
            config: _assign({}, defaults, flatpickr.defaultConfig),
            l10n: english
        };
        self.parseDate = createDateParser({ config: self.config, l10n: self.l10n });
        self._handlers = [];
        self.pluginElements = [];
        self.loadedPlugins = [];
        self._bind = bind;
        self._setHoursFromDate = setHoursFromDate;
        self._positionCalendar = positionCalendar;
        self.changeMonth = changeMonth;
        self.changeYear = changeYear;
        self.clear = clear;
        self.close = close;
        self._createElement = createElement;
        self.destroy = destroy;
        self.isEnabled = isEnabled;
        self.jumpToDate = jumpToDate;
        self.open = open;
        self.redraw = redraw;
        self.set = set;
        self.setDate = setDate;
        self.toggle = toggle;
        function setupHelperFunctions() {
            self.utils = {
                getDaysInMonth: function getDaysInMonth(month, yr) {
                    if (month === void 0) {
                        month = self.currentMonth;
                    }
                    if (yr === void 0) {
                        yr = self.currentYear;
                    }
                    if (month === 1 && (yr % 4 === 0 && yr % 100 !== 0 || yr % 400 === 0)) return 29;
                    return self.l10n.daysInMonth[month];
                }
            };
        }
        function init() {
            self.element = self.input = element;
            self.isOpen = false;
            parseConfig();
            setupLocale();
            setupInputs();
            setupDates();
            setupHelperFunctions();
            if (!self.isMobile) build();
            bindEvents();
            if (self.selectedDates.length || self.config.noCalendar) {
                if (self.config.enableTime) {
                    setHoursFromDate(self.config.noCalendar ? self.latestSelectedDateObj || self.config.minDate : undefined);
                }
                updateValue(false);
            }
            setCalendarWidth();
            self.showTimeInput = self.selectedDates.length > 0 || self.config.noCalendar;
            var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            /* TODO: investigate this further
                       Currently, there is weird positioning behavior in safari causing pages
              to scroll up. https://github.com/chmln/flatpickr/issues/563
                       However, most browsers are not Safari and positioning is expensive when used
              in scale. https://github.com/chmln/flatpickr/issues/1096
            */
            if (!self.isMobile && isSafari) {
                positionCalendar();
            }
            triggerEvent("onReady");
        }
        function bindToInstance(fn) {
            return fn.bind(self);
        }
        function setCalendarWidth() {
            var config = self.config;
            if (config.weekNumbers === false && config.showMonths === 1) return;else if (config.noCalendar !== true) {
                window.requestAnimationFrame(function () {
                    if (self.calendarContainer !== undefined) {
                        self.calendarContainer.style.visibility = "hidden";
                        self.calendarContainer.style.display = "block";
                    }
                    if (self.daysContainer !== undefined) {
                        var daysWidth = (self.days.offsetWidth + 1) * config.showMonths;
                        self.daysContainer.style.width = daysWidth + "px";
                        self.calendarContainer.style.width = daysWidth + (self.weekWrapper !== undefined ? self.weekWrapper.offsetWidth : 0) + "px";
                        self.calendarContainer.style.removeProperty("visibility");
                        self.calendarContainer.style.removeProperty("display");
                    }
                });
            }
        }
        /**
         * The handler for all events targeting the time inputs
         */
        function updateTime(e) {
            if (self.selectedDates.length === 0) {
                setDefaultTime();
            }
            if (e !== undefined && e.type !== "blur") {
                timeWrapper(e);
            }
            var prevValue = self._input.value;
            setHoursFromInputs();
            updateValue();
            if (self._input.value !== prevValue) {
                self._debouncedChange();
            }
        }
        function ampm2military(hour, amPM) {
            return hour % 12 + 12 * int(amPM === self.l10n.amPM[1]);
        }
        function military2ampm(hour) {
            switch (hour % 24) {
                case 0:
                case 12:
                    return 12;
                default:
                    return hour % 12;
            }
        }
        /**
         * Syncs the selected date object time with user's time input
         */
        function setHoursFromInputs() {
            if (self.hourElement === undefined || self.minuteElement === undefined) return;
            var hours = (parseInt(self.hourElement.value.slice(-2), 10) || 0) % 24,
                minutes = (parseInt(self.minuteElement.value, 10) || 0) % 60,
                seconds = self.secondElement !== undefined ? (parseInt(self.secondElement.value, 10) || 0) % 60 : 0;
            if (self.amPM !== undefined) {
                hours = ampm2military(hours, self.amPM.textContent);
            }
            var limitMinHours = self.config.minTime !== undefined || self.config.minDate && self.minDateHasTime && self.latestSelectedDateObj && compareDates(self.latestSelectedDateObj, self.config.minDate, true) === 0;
            var limitMaxHours = self.config.maxTime !== undefined || self.config.maxDate && self.maxDateHasTime && self.latestSelectedDateObj && compareDates(self.latestSelectedDateObj, self.config.maxDate, true) === 0;
            if (limitMaxHours) {
                var maxTime = self.config.maxTime !== undefined ? self.config.maxTime : self.config.maxDate;
                hours = Math.min(hours, maxTime.getHours());
                if (hours === maxTime.getHours()) minutes = Math.min(minutes, maxTime.getMinutes());
                if (minutes === maxTime.getMinutes()) seconds = Math.min(seconds, maxTime.getSeconds());
            }
            if (limitMinHours) {
                var minTime = self.config.minTime !== undefined ? self.config.minTime : self.config.minDate;
                hours = Math.max(hours, minTime.getHours());
                if (hours === minTime.getHours()) minutes = Math.max(minutes, minTime.getMinutes());
                if (minutes === minTime.getMinutes()) seconds = Math.max(seconds, minTime.getSeconds());
            }
            setHours(hours, minutes, seconds);
        }
        /**
         * Syncs time input values with a date
         */
        function setHoursFromDate(dateObj) {
            var date = dateObj || self.latestSelectedDateObj;
            if (date) setHours(date.getHours(), date.getMinutes(), date.getSeconds());
        }
        function setDefaultHours() {
            var hours = self.config.defaultHour;
            var minutes = self.config.defaultMinute;
            var seconds = self.config.defaultSeconds;
            if (self.config.minDate !== undefined) {
                var minHr = self.config.minDate.getHours();
                var minMinutes = self.config.minDate.getMinutes();
                hours = Math.max(hours, minHr);
                if (hours === minHr) minutes = Math.max(minMinutes, minutes);
                if (hours === minHr && minutes === minMinutes) seconds = self.config.minDate.getSeconds();
            }
            if (self.config.maxDate !== undefined) {
                var maxHr = self.config.maxDate.getHours();
                var maxMinutes = self.config.maxDate.getMinutes();
                hours = Math.min(hours, maxHr);
                if (hours === maxHr) minutes = Math.min(maxMinutes, minutes);
                if (hours === maxHr && minutes === maxMinutes) seconds = self.config.maxDate.getSeconds();
            }
            setHours(hours, minutes, seconds);
        }
        /**
         * Sets the hours, minutes, and optionally seconds
         * of the latest selected date object and the
         * corresponding time inputs
         * @param {Number} hours the hour. whether its military
         *                 or am-pm gets inferred from config
         * @param {Number} minutes the minutes
         * @param {Number} seconds the seconds (optional)
         */
        function setHours(hours, minutes, seconds) {
            if (self.latestSelectedDateObj !== undefined) {
                self.latestSelectedDateObj.setHours(hours % 24, minutes, seconds || 0, 0);
            }
            if (!self.hourElement || !self.minuteElement || self.isMobile) return;
            self.hourElement.value = pad(!self.config.time_24hr ? (12 + hours) % 12 + 12 * int(hours % 12 === 0) : hours);
            self.minuteElement.value = pad(minutes);
            if (self.amPM !== undefined) self.amPM.textContent = self.l10n.amPM[int(hours >= 12)];
            if (self.secondElement !== undefined) self.secondElement.value = pad(seconds);
        }
        /**
         * Handles the year input and incrementing events
         * @param {Event} event the keyup or increment event
         */
        function onYearInput(event) {
            var year = parseInt(event.target.value) + (event.delta || 0);
            if (year / 1000 > 1 || event.key === "Enter" && !/[^\d]/.test(year.toString())) {
                changeYear(year);
            }
        }
        /**
         * Essentially addEventListener + tracking
         * @param {Element} element the element to addEventListener to
         * @param {String} event the event name
         * @param {Function} handler the event handler
         */
        function bind(element, event, handler, options) {
            if (event instanceof Array) return event.forEach(function (ev) {
                return bind(element, ev, handler, options);
            });
            if (element instanceof Array) return element.forEach(function (el) {
                return bind(el, event, handler, options);
            });
            element.addEventListener(event, handler, options);
            self._handlers.push({
                element: element,
                event: event,
                handler: handler,
                options: options
            });
        }
        /**
         * A mousedown handler which mimics click.
         * Minimizes latency, since we don't need to wait for mouseup in most cases.
         * Also, avoids handling right clicks.
         *
         * @param {Function} handler the event handler
         */
        function onClick(handler) {
            return function (evt) {
                evt.which === 1 && handler(evt);
            };
        }
        function triggerChange() {
            triggerEvent("onChange");
        }
        /**
         * Adds all the necessary event listeners
         */
        function bindEvents() {
            if (self.config.wrap) {
                ["open", "close", "toggle", "clear"].forEach(function (evt) {
                    Array.prototype.forEach.call(self.element.querySelectorAll("[data-" + evt + "]"), function (el) {
                        return bind(el, "click", self[evt]);
                    });
                });
            }
            if (self.isMobile) {
                setupMobile();
                return;
            }
            var debouncedResize = debounce(onResize, 50);
            self._debouncedChange = debounce(triggerChange, DEBOUNCED_CHANGE_MS);
            if (self.daysContainer && !/iPhone|iPad|iPod/i.test(navigator.userAgent)) bind(self.daysContainer, "mouseover", function (e) {
                if (self.config.mode === "range") onMouseOver(e.target);
            });
            bind(window.document.body, "keydown", onKeyDown);
            if (!self.config.inline && !self.config.static) bind(window, "resize", debouncedResize);
            if (window.ontouchstart !== undefined) bind(window.document, "touchstart", documentClick);else bind(window.document, "mousedown", onClick(documentClick));
            bind(window.document, "focus", documentClick, { capture: true });
            if (self.config.clickOpens === true) {
                bind(self._input, "focus", self.open);
                bind(self._input, "mousedown", onClick(self.open));
            }
            if (self.daysContainer !== undefined) {
                bind(self.monthNav, "mousedown", onClick(onMonthNavClick));
                bind(self.monthNav, ["keyup", "increment"], onYearInput);
                bind(self.daysContainer, "mousedown", onClick(selectDate));
            }
            if (self.timeContainer !== undefined && self.minuteElement !== undefined && self.hourElement !== undefined) {
                var selText = function selText(e) {
                    return e.target.select();
                };
                bind(self.timeContainer, ["increment"], updateTime);
                bind(self.timeContainer, "blur", updateTime, { capture: true });
                bind(self.timeContainer, "mousedown", onClick(timeIncrement));
                bind([self.hourElement, self.minuteElement], ["focus", "click"], selText);
                if (self.secondElement !== undefined) bind(self.secondElement, "focus", function () {
                    return self.secondElement && self.secondElement.select();
                });
                if (self.amPM !== undefined) {
                    bind(self.amPM, "mousedown", onClick(function (e) {
                        updateTime(e);
                        triggerChange();
                    }));
                }
            }
        }
        /**
         * Set the calendar view to a particular date.
         * @param {Date} jumpDate the date to set the view to
         * @param {boolean} triggerChange if change events should be triggered
         */
        function jumpToDate(jumpDate, triggerChange) {
            var jumpTo = jumpDate !== undefined ? self.parseDate(jumpDate) : self.latestSelectedDateObj || (self.config.minDate && self.config.minDate > self.now ? self.config.minDate : self.config.maxDate && self.config.maxDate < self.now ? self.config.maxDate : self.now);
            var oldYear = self.currentYear;
            var oldMonth = self.currentMonth;
            try {
                if (jumpTo !== undefined) {
                    self.currentYear = jumpTo.getFullYear();
                    self.currentMonth = jumpTo.getMonth();
                }
            } catch (e) {
                /* istanbul ignore next */
                e.message = "Invalid date supplied: " + jumpTo;
                self.config.errorHandler(e);
            }
            if (triggerChange && self.currentYear !== oldYear) {
                triggerEvent("onYearChange");
                buildMonthSwitch();
            }
            if (triggerChange && (self.currentYear !== oldYear || self.currentMonth !== oldMonth)) {
                triggerEvent("onMonthChange");
            }
            self.redraw();
        }
        /**
         * The up/down arrow handler for time inputs
         * @param {Event} e the click event
         */
        function timeIncrement(e) {
            if (~e.target.className.indexOf("arrow")) incrementNumInput(e, e.target.classList.contains("arrowUp") ? 1 : -1);
        }
        /**
         * Increments/decrements the value of input associ-
         * ated with the up/down arrow by dispatching an
         * "increment" event on the input.
         *
         * @param {Event} e the click event
         * @param {Number} delta the diff (usually 1 or -1)
         * @param {Element} inputElem the input element
         */
        function incrementNumInput(e, delta, inputElem) {
            var target = e && e.target;
            var input = inputElem || target && target.parentNode && target.parentNode.firstChild;
            var event = createEvent("increment");
            event.delta = delta;
            input && input.dispatchEvent(event);
        }
        function build() {
            var fragment = window.document.createDocumentFragment();
            self.calendarContainer = createElement("div", "flatpickr-calendar");
            self.calendarContainer.tabIndex = -1;
            if (!self.config.noCalendar) {
                fragment.appendChild(buildMonthNav());
                self.innerContainer = createElement("div", "flatpickr-innerContainer");
                if (self.config.weekNumbers) {
                    var _a = buildWeeks(),
                        weekWrapper = _a.weekWrapper,
                        weekNumbers = _a.weekNumbers;
                    self.innerContainer.appendChild(weekWrapper);
                    self.weekNumbers = weekNumbers;
                    self.weekWrapper = weekWrapper;
                }
                self.rContainer = createElement("div", "flatpickr-rContainer");
                self.rContainer.appendChild(buildWeekdays());
                if (!self.daysContainer) {
                    self.daysContainer = createElement("div", "flatpickr-days");
                    self.daysContainer.tabIndex = -1;
                }
                buildDays();
                self.rContainer.appendChild(self.daysContainer);
                self.innerContainer.appendChild(self.rContainer);
                fragment.appendChild(self.innerContainer);
            }
            if (self.config.enableTime) {
                fragment.appendChild(buildTime());
            }
            toggleClass(self.calendarContainer, "rangeMode", self.config.mode === "range");
            toggleClass(self.calendarContainer, "animate", self.config.animate === true);
            toggleClass(self.calendarContainer, "multiMonth", self.config.showMonths > 1);
            self.calendarContainer.appendChild(fragment);
            var customAppend = self.config.appendTo !== undefined && self.config.appendTo.nodeType !== undefined;
            if (self.config.inline || self.config.static) {
                self.calendarContainer.classList.add(self.config.inline ? "inline" : "static");
                if (self.config.inline) {
                    if (!customAppend && self.element.parentNode) self.element.parentNode.insertBefore(self.calendarContainer, self._input.nextSibling);else if (self.config.appendTo !== undefined) self.config.appendTo.appendChild(self.calendarContainer);
                }
                if (self.config.static) {
                    var wrapper = createElement("div", "flatpickr-wrapper");
                    if (self.element.parentNode) self.element.parentNode.insertBefore(wrapper, self.element);
                    wrapper.appendChild(self.element);
                    if (self.altInput) wrapper.appendChild(self.altInput);
                    wrapper.appendChild(self.calendarContainer);
                }
            }
            if (!self.config.static && !self.config.inline) (self.config.appendTo !== undefined ? self.config.appendTo : window.document.body).appendChild(self.calendarContainer);
        }
        function createDay(className, date, dayNumber, i) {
            var dateIsEnabled = isEnabled(date, true),
                dayElement = createElement("span", "flatpickr-day " + className, date.getDate().toString());
            dayElement.dateObj = date;
            dayElement.$i = i;
            dayElement.setAttribute("aria-label", self.formatDate(date, self.config.ariaDateFormat));
            if (className.indexOf("hidden") === -1 && compareDates(date, self.now) === 0) {
                self.todayDateElem = dayElement;
                dayElement.classList.add("today");
                dayElement.setAttribute("aria-current", "date");
            }
            if (dateIsEnabled) {
                dayElement.tabIndex = -1;
                if (isDateSelected(date)) {
                    dayElement.classList.add("selected");
                    self.selectedDateElem = dayElement;
                    if (self.config.mode === "range") {
                        toggleClass(dayElement, "startRange", self.selectedDates[0] && compareDates(date, self.selectedDates[0], true) === 0);
                        toggleClass(dayElement, "endRange", self.selectedDates[1] && compareDates(date, self.selectedDates[1], true) === 0);
                        if (className === "nextMonthDay") dayElement.classList.add("inRange");
                    }
                }
            } else {
                dayElement.classList.add("flatpickr-disabled");
            }
            if (self.config.mode === "range") {
                if (isDateInRange(date) && !isDateSelected(date)) dayElement.classList.add("inRange");
            }
            if (self.weekNumbers && self.config.showMonths === 1 && className !== "prevMonthDay" && dayNumber % 7 === 1) {
                self.weekNumbers.insertAdjacentHTML("beforeend", "<span class='flatpickr-day'>" + self.config.getWeek(date) + "</span>");
            }
            triggerEvent("onDayCreate", dayElement);
            return dayElement;
        }
        function focusOnDayElem(targetNode) {
            targetNode.focus();
            if (self.config.mode === "range") onMouseOver(targetNode);
        }
        function getFirstAvailableDay(delta) {
            var startMonth = delta > 0 ? 0 : self.config.showMonths - 1;
            var endMonth = delta > 0 ? self.config.showMonths : -1;
            for (var m = startMonth; m != endMonth; m += delta) {
                var month = self.daysContainer.children[m];
                var startIndex = delta > 0 ? 0 : month.children.length - 1;
                var endIndex = delta > 0 ? month.children.length : -1;
                for (var i = startIndex; i != endIndex; i += delta) {
                    var c = month.children[i];
                    if (c.className.indexOf("hidden") === -1 && isEnabled(c.dateObj)) return c;
                }
            }
            return undefined;
        }
        function getNextAvailableDay(current, delta) {
            var givenMonth = current.className.indexOf("Month") === -1 ? current.dateObj.getMonth() : self.currentMonth;
            var endMonth = delta > 0 ? self.config.showMonths : -1;
            var loopDelta = delta > 0 ? 1 : -1;
            for (var m = givenMonth - self.currentMonth; m != endMonth; m += loopDelta) {
                var month = self.daysContainer.children[m];
                var startIndex = givenMonth - self.currentMonth === m ? current.$i + delta : delta < 0 ? month.children.length - 1 : 0;
                var numMonthDays = month.children.length;
                for (var i = startIndex; i >= 0 && i < numMonthDays && i != (delta > 0 ? numMonthDays : -1); i += loopDelta) {
                    var c = month.children[i];
                    if (c.className.indexOf("hidden") === -1 && isEnabled(c.dateObj) && Math.abs(current.$i - i) >= Math.abs(delta)) return focusOnDayElem(c);
                }
            }
            self.changeMonth(loopDelta);
            focusOnDay(getFirstAvailableDay(loopDelta), 0);
            return undefined;
        }
        function focusOnDay(current, offset) {
            var dayFocused = isInView(document.activeElement || document.body);
            var startElem = current !== undefined ? current : dayFocused ? document.activeElement : self.selectedDateElem !== undefined && isInView(self.selectedDateElem) ? self.selectedDateElem : self.todayDateElem !== undefined && isInView(self.todayDateElem) ? self.todayDateElem : getFirstAvailableDay(offset > 0 ? 1 : -1);
            if (startElem === undefined) return self._input.focus();
            if (!dayFocused) return focusOnDayElem(startElem);
            getNextAvailableDay(startElem, offset);
        }
        function buildMonthDays(year, month) {
            var firstOfMonth = (new Date(year, month, 1).getDay() - self.l10n.firstDayOfWeek + 7) % 7;
            var prevMonthDays = self.utils.getDaysInMonth((month - 1 + 12) % 12);
            var daysInMonth = self.utils.getDaysInMonth(month),
                days = window.document.createDocumentFragment(),
                isMultiMonth = self.config.showMonths > 1,
                prevMonthDayClass = isMultiMonth ? "prevMonthDay hidden" : "prevMonthDay",
                nextMonthDayClass = isMultiMonth ? "nextMonthDay hidden" : "nextMonthDay";
            var dayNumber = prevMonthDays + 1 - firstOfMonth,
                dayIndex = 0;
            // prepend days from the ending of previous month
            for (; dayNumber <= prevMonthDays; dayNumber++, dayIndex++) {
                days.appendChild(createDay(prevMonthDayClass, new Date(year, month - 1, dayNumber), dayNumber, dayIndex));
            }
            // Start at 1 since there is no 0th day
            for (dayNumber = 1; dayNumber <= daysInMonth; dayNumber++, dayIndex++) {
                days.appendChild(createDay("", new Date(year, month, dayNumber), dayNumber, dayIndex));
            }
            // append days from the next month
            for (var dayNum = daysInMonth + 1; dayNum <= 42 - firstOfMonth && (self.config.showMonths === 1 || dayIndex % 7 !== 0); dayNum++, dayIndex++) {
                days.appendChild(createDay(nextMonthDayClass, new Date(year, month + 1, dayNum % daysInMonth), dayNum, dayIndex));
            }
            //updateNavigationCurrentMonth();
            var dayContainer = createElement("div", "dayContainer");
            dayContainer.appendChild(days);
            return dayContainer;
        }
        function buildDays() {
            if (self.daysContainer === undefined) {
                return;
            }
            clearNode(self.daysContainer);
            // TODO: week numbers for each month
            if (self.weekNumbers) clearNode(self.weekNumbers);
            var frag = document.createDocumentFragment();
            for (var i = 0; i < self.config.showMonths; i++) {
                var d = new Date(self.currentYear, self.currentMonth, 1);
                d.setMonth(self.currentMonth + i);
                frag.appendChild(buildMonthDays(d.getFullYear(), d.getMonth()));
            }
            self.daysContainer.appendChild(frag);
            self.days = self.daysContainer.firstChild;
            if (self.config.mode === "range" && self.selectedDates.length === 1) {
                onMouseOver();
            }
        }
        function buildMonthSwitch() {
            if (self.config.showMonths > 1 || self.config.monthSelectorType !== "dropdown") return;
            var shouldBuildMonth = function shouldBuildMonth(month) {
                if (self.config.minDate !== undefined && self.currentYear === self.config.minDate.getFullYear() && month < self.config.minDate.getMonth()) {
                    return false;
                }
                return !(self.config.maxDate !== undefined && self.currentYear === self.config.maxDate.getFullYear() && month > self.config.maxDate.getMonth());
            };
            self.monthsDropdownContainer.tabIndex = -1;
            self.monthsDropdownContainer.innerHTML = "";
            for (var i = 0; i < 12; i++) {
                if (!shouldBuildMonth(i)) continue;
                var month = createElement("option", "flatpickr-monthDropdown-month");
                month.value = new Date(self.currentYear, i).getMonth().toString();
                month.textContent = monthToStr(i, self.config.shorthandCurrentMonth, self.l10n);
                month.tabIndex = -1;
                if (self.currentMonth === i) {
                    month.selected = true;
                }
                self.monthsDropdownContainer.appendChild(month);
            }
        }
        function buildMonth() {
            var container = createElement("div", "flatpickr-month");
            var monthNavFragment = window.document.createDocumentFragment();
            var monthElement;
            if (self.config.showMonths > 1 || self.config.monthSelectorType === "static") {
                monthElement = createElement("span", "cur-month");
            } else {
                self.monthsDropdownContainer = createElement("select", "flatpickr-monthDropdown-months");
                bind(self.monthsDropdownContainer, "change", function (e) {
                    var target = e.target;
                    var selectedMonth = parseInt(target.value, 10);
                    self.changeMonth(selectedMonth - self.currentMonth);
                    triggerEvent("onMonthChange");
                });
                buildMonthSwitch();
                monthElement = self.monthsDropdownContainer;
            }
            var yearInput = createNumberInput("cur-year", { tabindex: "-1" });
            var yearElement = yearInput.getElementsByTagName("input")[0];
            yearElement.setAttribute("aria-label", self.l10n.yearAriaLabel);
            if (self.config.minDate) {
                yearElement.setAttribute("min", self.config.minDate.getFullYear().toString());
            }
            if (self.config.maxDate) {
                yearElement.setAttribute("max", self.config.maxDate.getFullYear().toString());
                yearElement.disabled = !!self.config.minDate && self.config.minDate.getFullYear() === self.config.maxDate.getFullYear();
            }
            var currentMonth = createElement("div", "flatpickr-current-month");
            currentMonth.appendChild(monthElement);
            currentMonth.appendChild(yearInput);
            monthNavFragment.appendChild(currentMonth);
            container.appendChild(monthNavFragment);
            return {
                container: container,
                yearElement: yearElement,
                monthElement: monthElement
            };
        }
        function buildMonths() {
            clearNode(self.monthNav);
            self.monthNav.appendChild(self.prevMonthNav);
            if (self.config.showMonths) {
                self.yearElements = [];
                self.monthElements = [];
            }
            for (var m = self.config.showMonths; m--;) {
                var month = buildMonth();
                self.yearElements.push(month.yearElement);
                self.monthElements.push(month.monthElement);
                self.monthNav.appendChild(month.container);
            }
            self.monthNav.appendChild(self.nextMonthNav);
        }
        function buildMonthNav() {
            self.monthNav = createElement("div", "flatpickr-months");
            self.yearElements = [];
            self.monthElements = [];
            self.prevMonthNav = createElement("span", "flatpickr-prev-month");
            self.prevMonthNav.innerHTML = self.config.prevArrow;
            self.nextMonthNav = createElement("span", "flatpickr-next-month");
            self.nextMonthNav.innerHTML = self.config.nextArrow;
            buildMonths();
            Object.defineProperty(self, "_hidePrevMonthArrow", {
                get: function get() {
                    return self.__hidePrevMonthArrow;
                },
                set: function set(bool) {
                    if (self.__hidePrevMonthArrow !== bool) {
                        toggleClass(self.prevMonthNav, "flatpickr-disabled", bool);
                        self.__hidePrevMonthArrow = bool;
                    }
                }
            });
            Object.defineProperty(self, "_hideNextMonthArrow", {
                get: function get() {
                    return self.__hideNextMonthArrow;
                },
                set: function set(bool) {
                    if (self.__hideNextMonthArrow !== bool) {
                        toggleClass(self.nextMonthNav, "flatpickr-disabled", bool);
                        self.__hideNextMonthArrow = bool;
                    }
                }
            });
            self.currentYearElement = self.yearElements[0];
            updateNavigationCurrentMonth();
            return self.monthNav;
        }
        function buildTime() {
            self.calendarContainer.classList.add("hasTime");
            if (self.config.noCalendar) self.calendarContainer.classList.add("noCalendar");
            self.timeContainer = createElement("div", "flatpickr-time");
            self.timeContainer.tabIndex = -1;
            var separator = createElement("span", "flatpickr-time-separator", ":");
            var hourInput = createNumberInput("flatpickr-hour", {
                "aria-label": self.l10n.hourAriaLabel
            });
            self.hourElement = hourInput.getElementsByTagName("input")[0];
            var minuteInput = createNumberInput("flatpickr-minute", {
                "aria-label": self.l10n.minuteAriaLabel
            });
            self.minuteElement = minuteInput.getElementsByTagName("input")[0];
            self.hourElement.tabIndex = self.minuteElement.tabIndex = -1;
            self.hourElement.value = pad(self.latestSelectedDateObj ? self.latestSelectedDateObj.getHours() : self.config.time_24hr ? self.config.defaultHour : military2ampm(self.config.defaultHour));
            self.minuteElement.value = pad(self.latestSelectedDateObj ? self.latestSelectedDateObj.getMinutes() : self.config.defaultMinute);
            self.hourElement.setAttribute("step", self.config.hourIncrement.toString());
            self.minuteElement.setAttribute("step", self.config.minuteIncrement.toString());
            self.hourElement.setAttribute("min", self.config.time_24hr ? "0" : "1");
            self.hourElement.setAttribute("max", self.config.time_24hr ? "23" : "12");
            self.minuteElement.setAttribute("min", "0");
            self.minuteElement.setAttribute("max", "59");
            self.timeContainer.appendChild(hourInput);
            self.timeContainer.appendChild(separator);
            self.timeContainer.appendChild(minuteInput);
            if (self.config.time_24hr) self.timeContainer.classList.add("time24hr");
            if (self.config.enableSeconds) {
                self.timeContainer.classList.add("hasSeconds");
                var secondInput = createNumberInput("flatpickr-second");
                self.secondElement = secondInput.getElementsByTagName("input")[0];
                self.secondElement.value = pad(self.latestSelectedDateObj ? self.latestSelectedDateObj.getSeconds() : self.config.defaultSeconds);
                self.secondElement.setAttribute("step", self.minuteElement.getAttribute("step"));
                self.secondElement.setAttribute("min", "0");
                self.secondElement.setAttribute("max", "59");
                self.timeContainer.appendChild(createElement("span", "flatpickr-time-separator", ":"));
                self.timeContainer.appendChild(secondInput);
            }
            if (!self.config.time_24hr) {
                // add self.amPM if appropriate
                self.amPM = createElement("span", "flatpickr-am-pm", self.l10n.amPM[int((self.latestSelectedDateObj ? self.hourElement.value : self.config.defaultHour) > 11)]);
                self.amPM.title = self.l10n.toggleTitle;
                self.amPM.tabIndex = -1;
                self.timeContainer.appendChild(self.amPM);
            }
            return self.timeContainer;
        }
        function buildWeekdays() {
            if (!self.weekdayContainer) self.weekdayContainer = createElement("div", "flatpickr-weekdays");else clearNode(self.weekdayContainer);
            for (var i = self.config.showMonths; i--;) {
                var container = createElement("div", "flatpickr-weekdaycontainer");
                self.weekdayContainer.appendChild(container);
            }
            updateWeekdays();
            return self.weekdayContainer;
        }
        function updateWeekdays() {
            var firstDayOfWeek = self.l10n.firstDayOfWeek;
            var weekdays = self.l10n.weekdays.shorthand.slice();
            if (firstDayOfWeek > 0 && firstDayOfWeek < weekdays.length) {
                weekdays = weekdays.splice(firstDayOfWeek, weekdays.length).concat(weekdays.splice(0, firstDayOfWeek));
            }
            for (var i = self.config.showMonths; i--;) {
                self.weekdayContainer.children[i].innerHTML = "\n      <span class='flatpickr-weekday'>\n        " + weekdays.join("</span><span class='flatpickr-weekday'>") + "\n      </span>\n      ";
            }
        }
        /* istanbul ignore next */
        function buildWeeks() {
            self.calendarContainer.classList.add("hasWeeks");
            var weekWrapper = createElement("div", "flatpickr-weekwrapper");
            weekWrapper.appendChild(createElement("span", "flatpickr-weekday", self.l10n.weekAbbreviation));
            var weekNumbers = createElement("div", "flatpickr-weeks");
            weekWrapper.appendChild(weekNumbers);
            return {
                weekWrapper: weekWrapper,
                weekNumbers: weekNumbers
            };
        }
        function changeMonth(value, isOffset) {
            if (isOffset === void 0) {
                isOffset = true;
            }
            var delta = isOffset ? value : value - self.currentMonth;
            if (delta < 0 && self._hidePrevMonthArrow === true || delta > 0 && self._hideNextMonthArrow === true) return;
            self.currentMonth += delta;
            if (self.currentMonth < 0 || self.currentMonth > 11) {
                self.currentYear += self.currentMonth > 11 ? 1 : -1;
                self.currentMonth = (self.currentMonth + 12) % 12;
                triggerEvent("onYearChange");
                buildMonthSwitch();
            }
            buildDays();
            triggerEvent("onMonthChange");
            updateNavigationCurrentMonth();
        }
        function clear(triggerChangeEvent, toInitial) {
            if (triggerChangeEvent === void 0) {
                triggerChangeEvent = true;
            }
            if (toInitial === void 0) {
                toInitial = true;
            }
            self.input.value = "";
            if (self.altInput !== undefined) self.altInput.value = "";
            if (self.mobileInput !== undefined) self.mobileInput.value = "";
            self.selectedDates = [];
            self.latestSelectedDateObj = undefined;
            if (toInitial === true) {
                self.currentYear = self._initialDate.getFullYear();
                self.currentMonth = self._initialDate.getMonth();
            }
            self.showTimeInput = false;
            if (self.config.enableTime === true) {
                setDefaultHours();
            }
            self.redraw();
            if (triggerChangeEvent)
                // triggerChangeEvent is true (default) or an Event
                triggerEvent("onChange");
        }
        function close() {
            self.isOpen = false;
            if (!self.isMobile) {
                if (self.calendarContainer !== undefined) {
                    self.calendarContainer.classList.remove("open");
                }
                if (self._input !== undefined) {
                    self._input.classList.remove("active");
                }
            }
            triggerEvent("onClose");
        }
        function destroy() {
            if (self.config !== undefined) triggerEvent("onDestroy");
            for (var i = self._handlers.length; i--;) {
                var h = self._handlers[i];
                h.element.removeEventListener(h.event, h.handler, h.options);
            }
            self._handlers = [];
            if (self.mobileInput) {
                if (self.mobileInput.parentNode) self.mobileInput.parentNode.removeChild(self.mobileInput);
                self.mobileInput = undefined;
            } else if (self.calendarContainer && self.calendarContainer.parentNode) {
                if (self.config.static && self.calendarContainer.parentNode) {
                    var wrapper = self.calendarContainer.parentNode;
                    wrapper.lastChild && wrapper.removeChild(wrapper.lastChild);
                    if (wrapper.parentNode) {
                        while (wrapper.firstChild) {
                            wrapper.parentNode.insertBefore(wrapper.firstChild, wrapper);
                        }wrapper.parentNode.removeChild(wrapper);
                    }
                } else self.calendarContainer.parentNode.removeChild(self.calendarContainer);
            }
            if (self.altInput) {
                self.input.type = "text";
                if (self.altInput.parentNode) self.altInput.parentNode.removeChild(self.altInput);
                delete self.altInput;
            }
            if (self.input) {
                self.input.type = self.input._type;
                self.input.classList.remove("flatpickr-input");
                self.input.removeAttribute("readonly");
                self.input.value = "";
            }
            ["_showTimeInput", "latestSelectedDateObj", "_hideNextMonthArrow", "_hidePrevMonthArrow", "__hideNextMonthArrow", "__hidePrevMonthArrow", "isMobile", "isOpen", "selectedDateElem", "minDateHasTime", "maxDateHasTime", "days", "daysContainer", "_input", "_positionElement", "innerContainer", "rContainer", "monthNav", "todayDateElem", "calendarContainer", "weekdayContainer", "prevMonthNav", "nextMonthNav", "monthsDropdownContainer", "currentMonthElement", "currentYearElement", "navigationCurrentMonth", "selectedDateElem", "config"].forEach(function (k) {
                try {
                    delete self[k];
                } catch (_) {}
            });
        }
        function isCalendarElem(elem) {
            if (self.config.appendTo && self.config.appendTo.contains(elem)) return true;
            return self.calendarContainer.contains(elem);
        }
        function documentClick(e) {
            if (self.isOpen && !self.config.inline) {
                var eventTarget_1 = getEventTarget(e);
                var isCalendarElement = isCalendarElem(eventTarget_1);
                var isInput = eventTarget_1 === self.input || eventTarget_1 === self.altInput || self.element.contains(eventTarget_1) ||
                // web components
                // e.path is not present in all browsers. circumventing typechecks
                e.path && e.path.indexOf && (~e.path.indexOf(self.input) || ~e.path.indexOf(self.altInput));
                var lostFocus = e.type === "blur" ? isInput && e.relatedTarget && !isCalendarElem(e.relatedTarget) : !isInput && !isCalendarElement && !isCalendarElem(e.relatedTarget);
                var isIgnored = !self.config.ignoredFocusElements.some(function (elem) {
                    return elem.contains(eventTarget_1);
                });
                if (lostFocus && isIgnored) {
                    self.close();
                    if (self.config.mode === "range" && self.selectedDates.length === 1) {
                        self.clear(false);
                        self.redraw();
                    }
                }
            }
        }
        function changeYear(newYear) {
            if (!newYear || self.config.minDate && newYear < self.config.minDate.getFullYear() || self.config.maxDate && newYear > self.config.maxDate.getFullYear()) return;
            var newYearNum = newYear,
                isNewYear = self.currentYear !== newYearNum;
            self.currentYear = newYearNum || self.currentYear;
            if (self.config.maxDate && self.currentYear === self.config.maxDate.getFullYear()) {
                self.currentMonth = Math.min(self.config.maxDate.getMonth(), self.currentMonth);
            } else if (self.config.minDate && self.currentYear === self.config.minDate.getFullYear()) {
                self.currentMonth = Math.max(self.config.minDate.getMonth(), self.currentMonth);
            }
            if (isNewYear) {
                self.redraw();
                triggerEvent("onYearChange");
                buildMonthSwitch();
            }
        }
        function isEnabled(date, timeless) {
            if (timeless === void 0) {
                timeless = true;
            }
            var dateToCheck = self.parseDate(date, undefined, timeless); // timeless
            if (self.config.minDate && dateToCheck && compareDates(dateToCheck, self.config.minDate, timeless !== undefined ? timeless : !self.minDateHasTime) < 0 || self.config.maxDate && dateToCheck && compareDates(dateToCheck, self.config.maxDate, timeless !== undefined ? timeless : !self.maxDateHasTime) > 0) return false;
            if (self.config.enable.length === 0 && self.config.disable.length === 0) return true;
            if (dateToCheck === undefined) return false;
            var bool = self.config.enable.length > 0,
                array = bool ? self.config.enable : self.config.disable;
            for (var i = 0, d = void 0; i < array.length; i++) {
                d = array[i];
                if (typeof d === "function" && d(dateToCheck) // disabled by function
                ) return bool;else if (d instanceof Date && dateToCheck !== undefined && d.getTime() === dateToCheck.getTime())
                    // disabled by date
                    return bool;else if (typeof d === "string" && dateToCheck !== undefined) {
                    // disabled by date string
                    var parsed = self.parseDate(d, undefined, true);
                    return parsed && parsed.getTime() === dateToCheck.getTime() ? bool : !bool;
                } else if (
                // disabled by range
                (typeof d === 'undefined' ? 'undefined' : _typeof(d)) === "object" && dateToCheck !== undefined && d.from && d.to && dateToCheck.getTime() >= d.from.getTime() && dateToCheck.getTime() <= d.to.getTime()) return bool;
            }
            return !bool;
        }
        function isInView(elem) {
            if (self.daysContainer !== undefined) return elem.className.indexOf("hidden") === -1 && self.daysContainer.contains(elem);
            return false;
        }
        function onKeyDown(e) {
            // e.key                      e.keyCode
            // "Backspace"                        8
            // "Tab"                              9
            // "Enter"                           13
            // "Escape"     (IE "Esc")           27
            // "ArrowLeft"  (IE "Left")          37
            // "ArrowUp"    (IE "Up")            38
            // "ArrowRight" (IE "Right")         39
            // "ArrowDown"  (IE "Down")          40
            // "Delete"     (IE "Del")           46
            var isInput = e.target === self._input;
            var allowInput = self.config.allowInput;
            var allowKeydown = self.isOpen && (!allowInput || !isInput);
            var allowInlineKeydown = self.config.inline && isInput && !allowInput;
            if (e.keyCode === 13 && isInput) {
                if (allowInput) {
                    self.setDate(self._input.value, true, e.target === self.altInput ? self.config.altFormat : self.config.dateFormat);
                    return e.target.blur();
                } else {
                    self.open();
                }
            } else if (isCalendarElem(e.target) || allowKeydown || allowInlineKeydown) {
                var isTimeObj = !!self.timeContainer && self.timeContainer.contains(e.target);
                switch (e.keyCode) {
                    case 13:
                        if (isTimeObj) {
                            e.preventDefault();
                            updateTime();
                            focusAndClose();
                        } else selectDate(e);
                        break;
                    case 27:
                        // escape
                        e.preventDefault();
                        focusAndClose();
                        break;
                    case 8:
                    case 46:
                        if (isInput && !self.config.allowInput) {
                            e.preventDefault();
                            self.clear();
                        }
                        break;
                    case 37:
                    case 39:
                        if (!isTimeObj && !isInput) {
                            e.preventDefault();
                            if (self.daysContainer !== undefined && (allowInput === false || document.activeElement && isInView(document.activeElement))) {
                                var delta_1 = e.keyCode === 39 ? 1 : -1;
                                if (!e.ctrlKey) focusOnDay(undefined, delta_1);else {
                                    e.stopPropagation();
                                    changeMonth(delta_1);
                                    focusOnDay(getFirstAvailableDay(1), 0);
                                }
                            }
                        } else if (self.hourElement) self.hourElement.focus();
                        break;
                    case 38:
                    case 40:
                        e.preventDefault();
                        var delta = e.keyCode === 40 ? 1 : -1;
                        if (self.daysContainer && e.target.$i !== undefined || e.target === self.input) {
                            if (e.ctrlKey) {
                                e.stopPropagation();
                                changeYear(self.currentYear - delta);
                                focusOnDay(getFirstAvailableDay(1), 0);
                            } else if (!isTimeObj) focusOnDay(undefined, delta * 7);
                        } else if (e.target === self.currentYearElement) {
                            changeYear(self.currentYear - delta);
                        } else if (self.config.enableTime) {
                            if (!isTimeObj && self.hourElement) self.hourElement.focus();
                            updateTime(e);
                            self._debouncedChange();
                        }
                        break;
                    case 9:
                        if (isTimeObj) {
                            var elems = [self.hourElement, self.minuteElement, self.secondElement, self.amPM].concat(self.pluginElements).filter(function (x) {
                                return x;
                            });
                            var i = elems.indexOf(e.target);
                            if (i !== -1) {
                                var target = elems[i + (e.shiftKey ? -1 : 1)];
                                e.preventDefault();
                                (target || self._input).focus();
                            }
                        } else if (!self.config.noCalendar && self.daysContainer && self.daysContainer.contains(e.target) && e.shiftKey) {
                            e.preventDefault();
                            self._input.focus();
                        }
                        break;
                    default:
                        break;
                }
            }
            if (self.amPM !== undefined && e.target === self.amPM) {
                switch (e.key) {
                    case self.l10n.amPM[0].charAt(0):
                    case self.l10n.amPM[0].charAt(0).toLowerCase():
                        self.amPM.textContent = self.l10n.amPM[0];
                        setHoursFromInputs();
                        updateValue();
                        break;
                    case self.l10n.amPM[1].charAt(0):
                    case self.l10n.amPM[1].charAt(0).toLowerCase():
                        self.amPM.textContent = self.l10n.amPM[1];
                        setHoursFromInputs();
                        updateValue();
                        break;
                }
            }
            if (isInput || isCalendarElem(e.target)) {
                triggerEvent("onKeyDown", e);
            }
        }
        function onMouseOver(elem) {
            if (self.selectedDates.length !== 1 || elem && (!elem.classList.contains("flatpickr-day") || elem.classList.contains("flatpickr-disabled"))) return;
            var hoverDate = elem ? elem.dateObj.getTime() : self.days.firstElementChild.dateObj.getTime(),
                initialDate = self.parseDate(self.selectedDates[0], undefined, true).getTime(),
                rangeStartDate = Math.min(hoverDate, self.selectedDates[0].getTime()),
                rangeEndDate = Math.max(hoverDate, self.selectedDates[0].getTime());
            var containsDisabled = false;
            var minRange = 0,
                maxRange = 0;
            for (var t = rangeStartDate; t < rangeEndDate; t += duration.DAY) {
                if (!isEnabled(new Date(t), true)) {
                    containsDisabled = containsDisabled || t > rangeStartDate && t < rangeEndDate;
                    if (t < initialDate && (!minRange || t > minRange)) minRange = t;else if (t > initialDate && (!maxRange || t < maxRange)) maxRange = t;
                }
            }
            for (var m = 0; m < self.config.showMonths; m++) {
                var month = self.daysContainer.children[m];
                var _loop_1 = function _loop_1(i, l) {
                    var dayElem = month.children[i],
                        date = dayElem.dateObj;
                    var timestamp = date.getTime();
                    var outOfRange = minRange > 0 && timestamp < minRange || maxRange > 0 && timestamp > maxRange;
                    if (outOfRange) {
                        dayElem.classList.add("notAllowed");
                        ["inRange", "startRange", "endRange"].forEach(function (c) {
                            dayElem.classList.remove(c);
                        });
                        return "continue";
                    } else if (containsDisabled && !outOfRange) return "continue";
                    ["startRange", "inRange", "endRange", "notAllowed"].forEach(function (c) {
                        dayElem.classList.remove(c);
                    });
                    if (elem !== undefined) {
                        elem.classList.add(hoverDate <= self.selectedDates[0].getTime() ? "startRange" : "endRange");
                        if (initialDate < hoverDate && timestamp === initialDate) dayElem.classList.add("startRange");else if (initialDate > hoverDate && timestamp === initialDate) dayElem.classList.add("endRange");
                        if (timestamp >= minRange && (maxRange === 0 || timestamp <= maxRange) && isBetween(timestamp, initialDate, hoverDate)) dayElem.classList.add("inRange");
                    }
                };
                for (var i = 0, l = month.children.length; i < l; i++) {
                    _loop_1(i, l);
                }
            }
        }
        function onResize() {
            if (self.isOpen && !self.config.static && !self.config.inline) positionCalendar();
        }
        function setDefaultTime() {
            self.setDate(self.config.minDate !== undefined ? new Date(self.config.minDate.getTime()) : new Date(), true);
            setDefaultHours();
            updateValue();
        }
        function open(e, positionElement) {
            if (positionElement === void 0) {
                positionElement = self._positionElement;
            }
            if (self.isMobile === true) {
                if (e) {
                    e.preventDefault();
                    e.target && e.target.blur();
                }
                if (self.mobileInput !== undefined) {
                    self.mobileInput.focus();
                    self.mobileInput.click();
                }
                triggerEvent("onOpen");
                return;
            }
            if (self._input.disabled || self.config.inline) return;
            var wasOpen = self.isOpen;
            self.isOpen = true;
            if (!wasOpen) {
                self.calendarContainer.classList.add("open");
                self._input.classList.add("active");
                triggerEvent("onOpen");
                positionCalendar(positionElement);
            }
            if (self.config.enableTime === true && self.config.noCalendar === true) {
                if (self.selectedDates.length === 0) {
                    setDefaultTime();
                }
                if (self.config.allowInput === false && (e === undefined || !self.timeContainer.contains(e.relatedTarget))) {
                    setTimeout(function () {
                        return self.hourElement.select();
                    }, 50);
                }
            }
        }
        function minMaxDateSetter(type) {
            return function (date) {
                var dateObj = self.config["_" + type + "Date"] = self.parseDate(date, self.config.dateFormat);
                var inverseDateObj = self.config["_" + (type === "min" ? "max" : "min") + "Date"];
                if (dateObj !== undefined) {
                    self[type === "min" ? "minDateHasTime" : "maxDateHasTime"] = dateObj.getHours() > 0 || dateObj.getMinutes() > 0 || dateObj.getSeconds() > 0;
                }
                if (self.selectedDates) {
                    self.selectedDates = self.selectedDates.filter(function (d) {
                        return isEnabled(d);
                    });
                    if (!self.selectedDates.length && type === "min") setHoursFromDate(dateObj);
                    updateValue();
                }
                if (self.daysContainer) {
                    redraw();
                    if (dateObj !== undefined) self.currentYearElement[type] = dateObj.getFullYear().toString();else self.currentYearElement.removeAttribute(type);
                    self.currentYearElement.disabled = !!inverseDateObj && dateObj !== undefined && inverseDateObj.getFullYear() === dateObj.getFullYear();
                }
            };
        }
        function parseConfig() {
            var boolOpts = ["wrap", "weekNumbers", "allowInput", "clickOpens", "time_24hr", "enableTime", "noCalendar", "altInput", "shorthandCurrentMonth", "inline", "static", "enableSeconds", "disableMobile"];
            var userConfig = _assign({}, instanceConfig, JSON.parse(JSON.stringify(element.dataset || {})));
            var formats = {};
            self.config.parseDate = userConfig.parseDate;
            self.config.formatDate = userConfig.formatDate;
            Object.defineProperty(self.config, "enable", {
                get: function get() {
                    return self.config._enable;
                },
                set: function set(dates) {
                    self.config._enable = parseDateRules(dates);
                }
            });
            Object.defineProperty(self.config, "disable", {
                get: function get() {
                    return self.config._disable;
                },
                set: function set(dates) {
                    self.config._disable = parseDateRules(dates);
                }
            });
            var timeMode = userConfig.mode === "time";
            if (!userConfig.dateFormat && (userConfig.enableTime || timeMode)) {
                var defaultDateFormat = flatpickr.defaultConfig.dateFormat || defaults.dateFormat;
                formats.dateFormat = userConfig.noCalendar || timeMode ? "H:i" + (userConfig.enableSeconds ? ":S" : "") : defaultDateFormat + " H:i" + (userConfig.enableSeconds ? ":S" : "");
            }
            if (userConfig.altInput && (userConfig.enableTime || timeMode) && !userConfig.altFormat) {
                var defaultAltFormat = flatpickr.defaultConfig.altFormat || defaults.altFormat;
                formats.altFormat = userConfig.noCalendar || timeMode ? "h:i" + (userConfig.enableSeconds ? ":S K" : " K") : defaultAltFormat + (" h:i" + (userConfig.enableSeconds ? ":S" : "") + " K");
            }
            if (!userConfig.altInputClass) {
                self.config.altInputClass = self.input.className + " " + self.config.altInputClass;
            }
            Object.defineProperty(self.config, "minDate", {
                get: function get() {
                    return self.config._minDate;
                },
                set: minMaxDateSetter("min")
            });
            Object.defineProperty(self.config, "maxDate", {
                get: function get() {
                    return self.config._maxDate;
                },
                set: minMaxDateSetter("max")
            });
            var minMaxTimeSetter = function minMaxTimeSetter(type) {
                return function (val) {
                    self.config[type === "min" ? "_minTime" : "_maxTime"] = self.parseDate(val, "H:i");
                };
            };
            Object.defineProperty(self.config, "minTime", {
                get: function get() {
                    return self.config._minTime;
                },
                set: minMaxTimeSetter("min")
            });
            Object.defineProperty(self.config, "maxTime", {
                get: function get() {
                    return self.config._maxTime;
                },
                set: minMaxTimeSetter("max")
            });
            if (userConfig.mode === "time") {
                self.config.noCalendar = true;
                self.config.enableTime = true;
            }
            Object.assign(self.config, formats, userConfig);
            for (var i = 0; i < boolOpts.length; i++) {
                self.config[boolOpts[i]] = self.config[boolOpts[i]] === true || self.config[boolOpts[i]] === "true";
            }HOOKS.filter(function (hook) {
                return self.config[hook] !== undefined;
            }).forEach(function (hook) {
                self.config[hook] = arrayify(self.config[hook] || []).map(bindToInstance);
            });
            self.isMobile = !self.config.disableMobile && !self.config.inline && self.config.mode === "single" && !self.config.disable.length && !self.config.enable.length && !self.config.weekNumbers && /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            for (var i = 0; i < self.config.plugins.length; i++) {
                var pluginConf = self.config.plugins[i](self) || {};
                for (var key in pluginConf) {
                    if (HOOKS.indexOf(key) > -1) {
                        self.config[key] = arrayify(pluginConf[key]).map(bindToInstance).concat(self.config[key]);
                    } else if (typeof userConfig[key] === "undefined") self.config[key] = pluginConf[key];
                }
            }
            triggerEvent("onParseConfig");
        }
        function setupLocale() {
            if (_typeof(self.config.locale) !== "object" && typeof flatpickr.l10ns[self.config.locale] === "undefined") self.config.errorHandler(new Error("flatpickr: invalid locale " + self.config.locale));
            self.l10n = _assign({}, flatpickr.l10ns["default"], _typeof(self.config.locale) === "object" ? self.config.locale : self.config.locale !== "default" ? flatpickr.l10ns[self.config.locale] : undefined);
            tokenRegex.K = "(" + self.l10n.amPM[0] + "|" + self.l10n.amPM[1] + "|" + self.l10n.amPM[0].toLowerCase() + "|" + self.l10n.amPM[1].toLowerCase() + ")";
            var userConfig = _assign({}, instanceConfig, JSON.parse(JSON.stringify(element.dataset || {})));
            if (userConfig.time_24hr === undefined && flatpickr.defaultConfig.time_24hr === undefined) {
                self.config.time_24hr = self.l10n.time_24hr;
            }
            self.formatDate = createDateFormatter(self);
            self.parseDate = createDateParser({ config: self.config, l10n: self.l10n });
        }
        function positionCalendar(customPositionElement) {
            if (self.calendarContainer === undefined) return;
            triggerEvent("onPreCalendarPosition");
            var positionElement = customPositionElement || self._positionElement;
            var calendarHeight = Array.prototype.reduce.call(self.calendarContainer.children, function (acc, child) {
                return acc + child.offsetHeight;
            }, 0),
                calendarWidth = self.calendarContainer.offsetWidth,
                configPos = self.config.position.split(" "),
                configPosVertical = configPos[0],
                configPosHorizontal = configPos.length > 1 ? configPos[1] : null,
                inputBounds = positionElement.getBoundingClientRect(),
                distanceFromBottom = window.innerHeight - inputBounds.bottom,
                showOnTop = configPosVertical === "above" || configPosVertical !== "below" && distanceFromBottom < calendarHeight && inputBounds.top > calendarHeight;
            var top = window.pageYOffset + inputBounds.top + (!showOnTop ? positionElement.offsetHeight + 2 : -calendarHeight - 2);
            toggleClass(self.calendarContainer, "arrowTop", !showOnTop);
            toggleClass(self.calendarContainer, "arrowBottom", showOnTop);
            if (self.config.inline) return;
            var left = window.pageXOffset + inputBounds.left - (configPosHorizontal != null && configPosHorizontal === "center" ? (calendarWidth - inputBounds.width) / 2 : 0);
            var right = window.document.body.offsetWidth - inputBounds.right;
            var rightMost = left + calendarWidth > window.document.body.offsetWidth;
            var centerMost = right + calendarWidth > window.document.body.offsetWidth;
            toggleClass(self.calendarContainer, "rightMost", rightMost);
            if (self.config.static) return;
            self.calendarContainer.style.top = top + "px";
            if (!rightMost) {
                self.calendarContainer.style.left = left + "px";
                self.calendarContainer.style.right = "auto";
            } else if (!centerMost) {
                self.calendarContainer.style.left = "auto";
                self.calendarContainer.style.right = right + "px";
            } else {
                var doc = document.styleSheets[0];
                // some testing environments don't have css support
                if (doc === undefined) return;
                var bodyWidth = window.document.body.offsetWidth;
                var centerLeft = Math.max(0, bodyWidth / 2 - calendarWidth / 2);
                var centerBefore = ".flatpickr-calendar.centerMost:before";
                var centerAfter = ".flatpickr-calendar.centerMost:after";
                var centerIndex = doc.cssRules.length;
                var centerStyle = "{left:" + inputBounds.left + "px;right:auto;}";
                toggleClass(self.calendarContainer, "rightMost", false);
                toggleClass(self.calendarContainer, "centerMost", true);
                doc.insertRule(centerBefore + "," + centerAfter + centerStyle, centerIndex);
                self.calendarContainer.style.left = centerLeft + "px";
                self.calendarContainer.style.right = "auto";
            }
        }
        function redraw() {
            if (self.config.noCalendar || self.isMobile) return;
            updateNavigationCurrentMonth();
            buildDays();
        }
        function focusAndClose() {
            self._input.focus();
            if (window.navigator.userAgent.indexOf("MSIE") !== -1 || navigator.msMaxTouchPoints !== undefined) {
                // hack - bugs in the way IE handles focus keeps the calendar open
                setTimeout(self.close, 0);
            } else {
                self.close();
            }
        }
        function selectDate(e) {
            e.preventDefault();
            e.stopPropagation();
            var isSelectable = function isSelectable(day) {
                return day.classList && day.classList.contains("flatpickr-day") && !day.classList.contains("flatpickr-disabled") && !day.classList.contains("notAllowed");
            };
            var t = findParent(e.target, isSelectable);
            if (t === undefined) return;
            var target = t;
            var selectedDate = self.latestSelectedDateObj = new Date(target.dateObj.getTime());
            var shouldChangeMonth = (selectedDate.getMonth() < self.currentMonth || selectedDate.getMonth() > self.currentMonth + self.config.showMonths - 1) && self.config.mode !== "range";
            self.selectedDateElem = target;
            if (self.config.mode === "single") self.selectedDates = [selectedDate];else if (self.config.mode === "multiple") {
                var selectedIndex = isDateSelected(selectedDate);
                if (selectedIndex) self.selectedDates.splice(parseInt(selectedIndex), 1);else self.selectedDates.push(selectedDate);
            } else if (self.config.mode === "range") {
                if (self.selectedDates.length === 2) {
                    self.clear(false, false);
                }
                self.latestSelectedDateObj = selectedDate;
                self.selectedDates.push(selectedDate);
                // unless selecting same date twice, sort ascendingly
                if (compareDates(selectedDate, self.selectedDates[0], true) !== 0) self.selectedDates.sort(function (a, b) {
                    return a.getTime() - b.getTime();
                });
            }
            setHoursFromInputs();
            if (shouldChangeMonth) {
                var isNewYear = self.currentYear !== selectedDate.getFullYear();
                self.currentYear = selectedDate.getFullYear();
                self.currentMonth = selectedDate.getMonth();
                if (isNewYear) {
                    triggerEvent("onYearChange");
                    buildMonthSwitch();
                }
                triggerEvent("onMonthChange");
            }
            updateNavigationCurrentMonth();
            buildDays();
            updateValue();
            if (self.config.enableTime) setTimeout(function () {
                return self.showTimeInput = true;
            }, 50);
            // maintain focus
            if (!shouldChangeMonth && self.config.mode !== "range" && self.config.showMonths === 1) focusOnDayElem(target);else if (self.selectedDateElem !== undefined && self.hourElement === undefined) {
                self.selectedDateElem && self.selectedDateElem.focus();
            }
            if (self.hourElement !== undefined) self.hourElement !== undefined && self.hourElement.focus();
            if (self.config.closeOnSelect) {
                var single = self.config.mode === "single" && !self.config.enableTime;
                var range = self.config.mode === "range" && self.selectedDates.length === 2 && !self.config.enableTime;
                if (single || range) {
                    focusAndClose();
                }
            }
            triggerChange();
        }
        var CALLBACKS = {
            locale: [setupLocale, updateWeekdays],
            showMonths: [buildMonths, setCalendarWidth, buildWeekdays],
            minDate: [jumpToDate],
            maxDate: [jumpToDate]
        };
        function set(option, value) {
            if (option !== null && (typeof option === 'undefined' ? 'undefined' : _typeof(option)) === "object") {
                Object.assign(self.config, option);
                for (var key in option) {
                    if (CALLBACKS[key] !== undefined) CALLBACKS[key].forEach(function (x) {
                        return x();
                    });
                }
            } else {
                self.config[option] = value;
                if (CALLBACKS[option] !== undefined) CALLBACKS[option].forEach(function (x) {
                    return x();
                });else if (HOOKS.indexOf(option) > -1) self.config[option] = arrayify(value);
            }
            self.redraw();
            updateValue(false);
        }
        function setSelectedDate(inputDate, format) {
            var dates = [];
            if (inputDate instanceof Array) dates = inputDate.map(function (d) {
                return self.parseDate(d, format);
            });else if (inputDate instanceof Date || typeof inputDate === "number") dates = [self.parseDate(inputDate, format)];else if (typeof inputDate === "string") {
                switch (self.config.mode) {
                    case "single":
                    case "time":
                        dates = [self.parseDate(inputDate, format)];
                        break;
                    case "multiple":
                        dates = inputDate.split(self.config.conjunction).map(function (date) {
                            return self.parseDate(date, format);
                        });
                        break;
                    case "range":
                        dates = inputDate.split(self.l10n.rangeSeparator).map(function (date) {
                            return self.parseDate(date, format);
                        });
                        break;
                    default:
                        break;
                }
            } else self.config.errorHandler(new Error("Invalid date supplied: " + JSON.stringify(inputDate)));
            self.selectedDates = dates.filter(function (d) {
                return d instanceof Date && isEnabled(d, false);
            });
            if (self.config.mode === "range") self.selectedDates.sort(function (a, b) {
                return a.getTime() - b.getTime();
            });
        }
        function setDate(date, triggerChange, format) {
            if (triggerChange === void 0) {
                triggerChange = false;
            }
            if (format === void 0) {
                format = self.config.dateFormat;
            }
            if (date !== 0 && !date || date instanceof Array && date.length === 0) return self.clear(triggerChange);
            setSelectedDate(date, format);
            self.showTimeInput = self.selectedDates.length > 0;
            self.latestSelectedDateObj = self.selectedDates[self.selectedDates.length - 1];
            self.redraw();
            jumpToDate();
            setHoursFromDate();
            if (self.selectedDates.length === 0) {
                self.clear(false);
            }
            updateValue(triggerChange);
            if (triggerChange) triggerEvent("onChange");
        }
        function parseDateRules(arr) {
            return arr.slice().map(function (rule) {
                if (typeof rule === "string" || typeof rule === "number" || rule instanceof Date) {
                    return self.parseDate(rule, undefined, true);
                } else if (rule && (typeof rule === 'undefined' ? 'undefined' : _typeof(rule)) === "object" && rule.from && rule.to) return {
                    from: self.parseDate(rule.from, undefined),
                    to: self.parseDate(rule.to, undefined)
                };
                return rule;
            }).filter(function (x) {
                return x;
            }); // remove falsy values
        }
        function setupDates() {
            self.selectedDates = [];
            self.now = self.parseDate(self.config.now) || new Date();
            // Workaround IE11 setting placeholder as the input's value
            var preloadedDate = self.config.defaultDate || ((self.input.nodeName === "INPUT" || self.input.nodeName === "TEXTAREA") && self.input.placeholder && self.input.value === self.input.placeholder ? null : self.input.value);
            if (preloadedDate) setSelectedDate(preloadedDate, self.config.dateFormat);
            self._initialDate = self.selectedDates.length > 0 ? self.selectedDates[0] : self.config.minDate && self.config.minDate.getTime() > self.now.getTime() ? self.config.minDate : self.config.maxDate && self.config.maxDate.getTime() < self.now.getTime() ? self.config.maxDate : self.now;
            self.currentYear = self._initialDate.getFullYear();
            self.currentMonth = self._initialDate.getMonth();
            if (self.selectedDates.length > 0) self.latestSelectedDateObj = self.selectedDates[0];
            if (self.config.minTime !== undefined) self.config.minTime = self.parseDate(self.config.minTime, "H:i");
            if (self.config.maxTime !== undefined) self.config.maxTime = self.parseDate(self.config.maxTime, "H:i");
            self.minDateHasTime = !!self.config.minDate && (self.config.minDate.getHours() > 0 || self.config.minDate.getMinutes() > 0 || self.config.minDate.getSeconds() > 0);
            self.maxDateHasTime = !!self.config.maxDate && (self.config.maxDate.getHours() > 0 || self.config.maxDate.getMinutes() > 0 || self.config.maxDate.getSeconds() > 0);
            Object.defineProperty(self, "showTimeInput", {
                get: function get() {
                    return self._showTimeInput;
                },
                set: function set(bool) {
                    self._showTimeInput = bool;
                    if (self.calendarContainer) toggleClass(self.calendarContainer, "showTimeInput", bool);
                    self.isOpen && positionCalendar();
                }
            });
        }
        function setupInputs() {
            self.input = self.config.wrap ? element.querySelector("[data-input]") : element;
            /* istanbul ignore next */
            if (!self.input) {
                self.config.errorHandler(new Error("Invalid input element specified"));
                return;
            }
            // hack: store previous type to restore it after destroy()
            self.input._type = self.input.type;
            self.input.type = "text";
            self.input.classList.add("flatpickr-input");
            self._input = self.input;
            if (self.config.altInput) {
                // replicate self.element
                self.altInput = createElement(self.input.nodeName, self.config.altInputClass);
                self._input = self.altInput;
                self.altInput.placeholder = self.input.placeholder;
                self.altInput.disabled = self.input.disabled;
                self.altInput.required = self.input.required;
                self.altInput.tabIndex = self.input.tabIndex;
                self.altInput.type = "text";
                self.input.setAttribute("type", "hidden");
                if (!self.config.static && self.input.parentNode) self.input.parentNode.insertBefore(self.altInput, self.input.nextSibling);
            }
            if (!self.config.allowInput) self._input.setAttribute("readonly", "readonly");
            self._positionElement = self.config.positionElement || self._input;
        }
        function setupMobile() {
            var inputType = self.config.enableTime ? self.config.noCalendar ? "time" : "datetime-local" : "date";
            self.mobileInput = createElement("input", self.input.className + " flatpickr-mobile");
            self.mobileInput.step = self.input.getAttribute("step") || "any";
            self.mobileInput.tabIndex = 1;
            self.mobileInput.type = inputType;
            self.mobileInput.disabled = self.input.disabled;
            self.mobileInput.required = self.input.required;
            self.mobileInput.placeholder = self.input.placeholder;
            self.mobileFormatStr = inputType === "datetime-local" ? "Y-m-d\\TH:i:S" : inputType === "date" ? "Y-m-d" : "H:i:S";
            if (self.selectedDates.length > 0) {
                self.mobileInput.defaultValue = self.mobileInput.value = self.formatDate(self.selectedDates[0], self.mobileFormatStr);
            }
            if (self.config.minDate) self.mobileInput.min = self.formatDate(self.config.minDate, "Y-m-d");
            if (self.config.maxDate) self.mobileInput.max = self.formatDate(self.config.maxDate, "Y-m-d");
            self.input.type = "hidden";
            if (self.altInput !== undefined) self.altInput.type = "hidden";
            try {
                if (self.input.parentNode) self.input.parentNode.insertBefore(self.mobileInput, self.input.nextSibling);
            } catch (_a) {}
            bind(self.mobileInput, "change", function (e) {
                self.setDate(e.target.value, false, self.mobileFormatStr);
                triggerEvent("onChange");
                triggerEvent("onClose");
            });
        }
        function toggle(e) {
            if (self.isOpen === true) return self.close();
            self.open(e);
        }
        function triggerEvent(event, data) {
            // If the instance has been destroyed already, all hooks have been removed
            if (self.config === undefined) return;
            var hooks = self.config[event];
            if (hooks !== undefined && hooks.length > 0) {
                for (var i = 0; hooks[i] && i < hooks.length; i++) {
                    hooks[i](self.selectedDates, self.input.value, self, data);
                }
            }
            if (event === "onChange") {
                self.input.dispatchEvent(createEvent("change"));
                // many front-end frameworks bind to the input event
                self.input.dispatchEvent(createEvent("input"));
            }
        }
        function createEvent(name) {
            var e = document.createEvent("Event");
            e.initEvent(name, true, true);
            return e;
        }
        function isDateSelected(date) {
            for (var i = 0; i < self.selectedDates.length; i++) {
                if (compareDates(self.selectedDates[i], date) === 0) return "" + i;
            }
            return false;
        }
        function isDateInRange(date) {
            if (self.config.mode !== "range" || self.selectedDates.length < 2) return false;
            return compareDates(date, self.selectedDates[0]) >= 0 && compareDates(date, self.selectedDates[1]) <= 0;
        }
        function updateNavigationCurrentMonth() {
            if (self.config.noCalendar || self.isMobile || !self.monthNav) return;
            self.yearElements.forEach(function (yearElement, i) {
                var d = new Date(self.currentYear, self.currentMonth, 1);
                d.setMonth(self.currentMonth + i);
                if (self.config.showMonths > 1 || self.config.monthSelectorType === "static") {
                    self.monthElements[i].textContent = monthToStr(d.getMonth(), self.config.shorthandCurrentMonth, self.l10n) + " ";
                } else {
                    self.monthsDropdownContainer.value = d.getMonth().toString();
                }
                yearElement.value = d.getFullYear().toString();
            });
            self._hidePrevMonthArrow = self.config.minDate !== undefined && (self.currentYear === self.config.minDate.getFullYear() ? self.currentMonth <= self.config.minDate.getMonth() : self.currentYear < self.config.minDate.getFullYear());
            self._hideNextMonthArrow = self.config.maxDate !== undefined && (self.currentYear === self.config.maxDate.getFullYear() ? self.currentMonth + 1 > self.config.maxDate.getMonth() : self.currentYear > self.config.maxDate.getFullYear());
        }
        function getDateStr(format) {
            return self.selectedDates.map(function (dObj) {
                return self.formatDate(dObj, format);
            }).filter(function (d, i, arr) {
                return self.config.mode !== "range" || self.config.enableTime || arr.indexOf(d) === i;
            }).join(self.config.mode !== "range" ? self.config.conjunction : self.l10n.rangeSeparator);
        }
        /**
         * Updates the values of inputs associated with the calendar
         */
        function updateValue(triggerChange) {
            if (triggerChange === void 0) {
                triggerChange = true;
            }
            if (self.mobileInput !== undefined && self.mobileFormatStr) {
                self.mobileInput.value = self.latestSelectedDateObj !== undefined ? self.formatDate(self.latestSelectedDateObj, self.mobileFormatStr) : "";
            }
            self.input.value = getDateStr(self.config.dateFormat);
            if (self.altInput !== undefined) {
                self.altInput.value = getDateStr(self.config.altFormat);
            }
            if (triggerChange !== false) triggerEvent("onValueUpdate");
        }
        function onMonthNavClick(e) {
            var isPrevMonth = self.prevMonthNav.contains(e.target);
            var isNextMonth = self.nextMonthNav.contains(e.target);
            if (isPrevMonth || isNextMonth) {
                changeMonth(isPrevMonth ? -1 : 1);
            } else if (self.yearElements.indexOf(e.target) >= 0) {
                e.target.select();
            } else if (e.target.classList.contains("arrowUp")) {
                self.changeYear(self.currentYear + 1);
            } else if (e.target.classList.contains("arrowDown")) {
                self.changeYear(self.currentYear - 1);
            }
        }
        function timeWrapper(e) {
            e.preventDefault();
            var isKeyDown = e.type === "keydown",
                input = e.target;
            if (self.amPM !== undefined && e.target === self.amPM) {
                self.amPM.textContent = self.l10n.amPM[int(self.amPM.textContent === self.l10n.amPM[0])];
            }
            var min = parseFloat(input.getAttribute("min")),
                max = parseFloat(input.getAttribute("max")),
                step = parseFloat(input.getAttribute("step")),
                curValue = parseInt(input.value, 10),
                delta = e.delta || (isKeyDown ? e.which === 38 ? 1 : -1 : 0);
            var newValue = curValue + step * delta;
            if (typeof input.value !== "undefined" && input.value.length === 2) {
                var isHourElem = input === self.hourElement,
                    isMinuteElem = input === self.minuteElement;
                if (newValue < min) {
                    newValue = max + newValue + int(!isHourElem) + (int(isHourElem) && int(!self.amPM));
                    if (isMinuteElem) incrementNumInput(undefined, -1, self.hourElement);
                } else if (newValue > max) {
                    newValue = input === self.hourElement ? newValue - max - int(!self.amPM) : min;
                    if (isMinuteElem) incrementNumInput(undefined, 1, self.hourElement);
                }
                if (self.amPM && isHourElem && (step === 1 ? newValue + curValue === 23 : Math.abs(newValue - curValue) > step)) {
                    self.amPM.textContent = self.l10n.amPM[int(self.amPM.textContent === self.l10n.amPM[0])];
                }
                input.value = pad(newValue);
            }
        }
        init();
        return self;
    }
    /* istanbul ignore next */
    function _flatpickr(nodeList, config) {
        // static list
        var nodes = Array.prototype.slice.call(nodeList).filter(function (x) {
            return x instanceof HTMLElement;
        });
        var instances = [];
        for (var i = 0; i < nodes.length; i++) {
            var node = nodes[i];
            try {
                if (node.getAttribute("data-fp-omit") !== null) continue;
                if (node._flatpickr !== undefined) {
                    node._flatpickr.destroy();
                    node._flatpickr = undefined;
                }
                node._flatpickr = FlatpickrInstance(node, config || {});
                instances.push(node._flatpickr);
            } catch (e) {
                console.error(e);
            }
        }
        return instances.length === 1 ? instances[0] : instances;
    }
    /* istanbul ignore next */
    if (typeof HTMLElement !== "undefined" && typeof HTMLCollection !== "undefined" && typeof NodeList !== "undefined") {
        // browser env
        HTMLCollection.prototype.flatpickr = NodeList.prototype.flatpickr = function (config) {
            return _flatpickr(this, config);
        };
        HTMLElement.prototype.flatpickr = function (config) {
            return _flatpickr([this], config);
        };
    }
    /* istanbul ignore next */
    var flatpickr = function flatpickr(selector, config) {
        if (typeof selector === "string") {
            return _flatpickr(window.document.querySelectorAll(selector), config);
        } else if (selector instanceof Node) {
            return _flatpickr([selector], config);
        } else {
            return _flatpickr(selector, config);
        }
    };
    /* istanbul ignore next */
    flatpickr.defaultConfig = {};
    flatpickr.l10ns = {
        en: _assign({}, english),
        "default": _assign({}, english)
    };
    flatpickr.localize = function (l10n) {
        flatpickr.l10ns["default"] = _assign({}, flatpickr.l10ns["default"], l10n);
    };
    flatpickr.setDefaults = function (config) {
        flatpickr.defaultConfig = _assign({}, flatpickr.defaultConfig, config);
    };
    flatpickr.parseDate = createDateParser({});
    flatpickr.formatDate = createDateFormatter({});
    flatpickr.compareDates = compareDates;
    /* istanbul ignore next */
    if (typeof jQuery !== "undefined" && typeof jQuery.fn !== "undefined") {
        jQuery.fn.flatpickr = function (config) {
            return _flatpickr(this, config);
        };
    }
    // eslint-disable-next-line @typescript-eslint/camelcase
    Date.prototype.fp_incr = function (days) {
        return new Date(this.getFullYear(), this.getMonth(), this.getDate() + (typeof days === "string" ? parseInt(days, 10) : days));
    };
    if (typeof window !== "undefined") {
        window.flatpickr = flatpickr;
    }

    return flatpickr;
});

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Number Field functionality.
 *
 * @since 1.4.0
 */
var FieldNumber = function (_Field) {
    _inherits(FieldNumber, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldNumber($field) {
        _classCallCheck(this, FieldNumber);

        var _this = _possibleConstructorReturn(this, (FieldNumber.__proto__ || Object.getPrototypeOf(FieldNumber)).call(this, $field, 'number'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the Number field.
     *
     * @since 1.4.0
     */


    _createClass(FieldNumber, [{
        key: 'initField',
        value: function initField() {

            this.$ui = {
                container: this.$field,
                input: this.$field.find('.fieldhelpers-field-input'),
                increase: this.$field.find('[data-number-increase]'),
                decrease: this.$field.find('[data-number-decrease]')
            };

            this.intervals = {
                increase: {
                    normal: parseFloat(this.options.increaseInterval),
                    alt: parseFloat(this.options.altIncreaseInterval)
                },
                decrease: {
                    normal: parseFloat(this.options.decreaseInterval),
                    alt: parseFloat(this.options.altDecreaseInterval)
                }
            };

            var constrainMax = this.options.max;
            var constrainMin = this.options.min;

            this.constraints = {
                max: constrainMax !== 'none' ? parseFloat(constrainMax) : false,
                min: constrainMin !== 'none' ? parseFloat(constrainMin) : false
            };

            this.shiftKeyUtility();
            this.setupHandlers();

            var initialValue = this.$ui.input.val();
            this.value = !initialValue ? 0 : parseFloat(initialValue);

            // Initializes the field
            this.validateInput();
        }

        /**
         * Helps determine shift key press status.
         *
         * @since 1.4.0
         */

    }, {
        key: 'shiftKeyUtility',
        value: function shiftKeyUtility() {
            var _this2 = this;

            this.shiftKeyDown = false;

            jQuery(document).on('keydown', function (e) {

                if (e.which === 16) {

                    _this2.shiftKeyDown = true;
                }
            });

            jQuery(document).on('keyup', function (e) {

                if (e.which === 16) {

                    _this2.shiftKeyDown = false;
                }
            });
        }

        /**
         * Sets up the class handlers.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setupHandlers',
        value: function setupHandlers() {
            var _this3 = this;

            this.$ui.increase.click(function (e) {

                _this3.increaseNumber(e);
            });

            this.$ui.decrease.click(function (e) {

                _this3.decreaseNumber(e);
            });

            this.$ui.input.change(function (e) {

                _this3.inputExternalChange(e);
            });
        }

        /**
         * Increases the input number.
         *
         * @since 1.4.0
         */

    }, {
        key: 'increaseNumber',
        value: function increaseNumber() {

            var amount = this.shiftKeyDown ? this.intervals.increase.alt : this.intervals.increase.normal;
            var newNumber = this.value + amount;

            this.$ui.input.val(newNumber);
            this.$ui.input.trigger('change');
        }

        /**
         * Decreases the input number.
         *
         * @since 1.4.0
         */

    }, {
        key: 'decreaseNumber',
        value: function decreaseNumber() {

            var amount = this.shiftKeyDown ? this.intervals.decrease.alt : this.intervals.decrease.normal;
            var newNumber = this.value - amount;

            this.$ui.input.val(newNumber);
            this.$ui.input.trigger('change');
        }

        /**
         * Fires on the input change. Typically from user typing or other scripts modifying.
         *
         * @since 1.4.0
         */

    }, {
        key: 'inputExternalChange',
        value: function inputExternalChange() {

            this.validateInput();
        }

        /**
         * Runs number through constrains.
         *
         * @param {int} number
         *
         * @return {Object}
         */

    }, {
        key: 'constrainNumber',
        value: function constrainNumber(number) {

            var status = 'unmodified';

            if (this.constraints.max !== false && number > this.constraints.max) {

                status = 'max';
                number = this.constraints.max;
            } else if (this.constraints.min !== false && number < this.constraints.min) {

                status = 'min';
                number = this.constraints.min;
            }

            return {
                status: status,
                number: number
            };
        }

        /**
         * Runs input value through constraints to ensure it is accurate.
         *
         * @since 1.4.0
         */

    }, {
        key: 'validateInput',
        value: function validateInput() {

            var currentValue = this.$ui.input.val();

            // Constrain to numbers
            var matches = currentValue.match(/^-?[0-9]\d*(\.\d+)?$/);
            currentValue = matches && parseFloat(matches[0]) || 0;

            var constraints = this.constrainNumber(currentValue);

            switch (constraints.status) {

                case 'max':

                    this.toggleDecreaseDisabledUI(true);
                    this.toggleIncreaseDisabledUI(false);
                    break;

                case 'min':

                    this.toggleIncreaseDisabledUI(true);
                    this.toggleDecreaseDisabledUI(false);
                    break;

                default:

                    this.toggleIncreaseDisabledUI(true);
                    this.toggleDecreaseDisabledUI(true);

            }

            this.value = constraints.number;
            this.$ui.input.val(this.value);

            if (currentValue !== this.value) {

                this.$ui.input.trigger('change');
            }
        }

        /**
         * Disables/Enables the increase button.
         *
         * @since 1.4.0
         *
         * @param {bool} enable True to set to enabled, false to set to disabled
         */

    }, {
        key: 'toggleIncreaseDisabledUI',
        value: function toggleIncreaseDisabledUI(enable) {

            this.$ui.increase.prop('disabled', !enable);
        }

        /**
         * Disables/Enables the decrease button.
         *
         * @since 1.4.0
         *
         * @param {bool} enable True to set to enabled, false to set to disabled
         */

    }, {
        key: 'toggleDecreaseDisabledUI',
        value: function toggleDecreaseDisabledUI(enable) {

            this.$ui.decrease.prop('disabled', !enable);
        }
    }]);

    return FieldNumber;
}(_field2.default);

/**
 * Finds and initializes all Number fields.
 *
 * @since 1.4.0
 */


var FieldNumberInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldNumberInitialize($root) {
        _classCallCheck(this, FieldNumberInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-number]');

        if ($fields.length) {

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldNumberInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldNumber($field)
            });
        }
    }]);

    return FieldNumberInitialize;
}();

exports.default = FieldNumberInitialize;

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Color Picker Field functionality.
 *
 * @since 1.4.0
 */
var FieldColorPicker = function (_Field) {
    _inherits(FieldColorPicker, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldColorPicker($field) {
        _classCallCheck(this, FieldColorPicker);

        var _this = _possibleConstructorReturn(this, (FieldColorPicker.__proto__ || Object.getPrototypeOf(FieldColorPicker)).call(this, $field, 'colorpicker'));

        _this.initializeColorpicker();
        return _this;
    }

    /**
     * Initializes the Color Picker.
     *
     * @since 1.4.0
     */


    _createClass(FieldColorPicker, [{
        key: 'initializeColorpicker',
        value: function initializeColorpicker() {

            if (!this.$field.val()) {
                this.setDefault();
            }

            this.$field.wpColorPicker(this.options.colorpickerOptions);
        }

        /**
         * Cleans up after a repeater add/init.
         *
         * @since 1.4.0
         */

    }, {
        key: 'fieldCleanup',
        value: function fieldCleanup() {

            this.$wrapper.find('[data-fieldhelpers-field-colorpicker]').appendTo(this.$wrapper.find('.fieldhelpers-field-content'));

            this.$wrapper.find('.wp-picker-container').remove();
        }
    }]);

    return FieldColorPicker;
}(_field2.default);

/**
 * Finds and initializes all Color Picker fields.
 *
 * @since 1.4.0
 */


var FieldColorPickerInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldColorPickerInitialize($root) {
        _classCallCheck(this, FieldColorPickerInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-colorpicker]');

        if ($fields.length) {

            if (!jQuery.isFunction(jQuery.fn.wpColorPicker)) {

                console.error('Field Helpers Error: Trying to initialize Color Picker field but "wp-color-picker" is ' + 'not enqueued.');
                return;
            }

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldColorPickerInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldColorPicker($field)
            });
        }
    }]);

    return FieldColorPickerInitialize;
}();

exports.default = FieldColorPickerInitialize;

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Date Picker Field functionality.
 *
 * Also includes Date/Time Picker and Time Picker.
 *
 * @since 1.4.0
 */
var FieldDatePicker = function (_Field) {
    _inherits(FieldDatePicker, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldDatePicker($field) {
        _classCallCheck(this, FieldDatePicker);

        var _this = _possibleConstructorReturn(this, (FieldDatePicker.__proto__ || Object.getPrototypeOf(FieldDatePicker)).call(this, $field, 'datepicker'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the Date Picker.
     *
     * @since 1.4.0
     */


    _createClass(FieldDatePicker, [{
        key: 'initField',
        value: function initField() {
            var _this2 = this;

            var option_functions = ['onChange', 'onOpen', 'onClose', 'onMonthChange', 'onYearChange', 'onReady', 'onValueUpdate', 'onDayCreate'];

            // Function support
            jQuery.each(this.options.datepickerOptions, function (name, value) {

                if (option_functions.indexOf(name) !== -1 && !jQuery.isFunction(_this2.options.datepickerOptions[name]) && jQuery.isFunction(window[value])) {

                    _this2.options.datepickerOptions[name] = window[value];
                }
            });

            // We need to ensure that the field instance for our specific field loads its default date in properly
            this.options.datepickerOptions.defaultDate = this.$field.data('defaultdate');

            var value = this.$field.val();

            if (!value) value = this.options.datepickerOptions.defaultDate;

            this.flatpickr = this.$field.flatpickr(this.options.datepickerOptions);

            this.flatpickr.setDate(value, true);
        }

        /**
         * Cleans up after a repeater add/init.
         *
         * @since 1.4.0
         */

    }, {
        key: 'fieldCleanup',
        value: function fieldCleanup() {

            var value = this.$field.val();

            if (!value) value = this.options.datepickerOptions.defaultDate;

            if (typeof this.flatpickr !== 'undefined') {

                this.flatpickr.destroy();
            }

            this.$field.val(value);
        }

        /**
         * Runs cleanup before the Repeater creates a dummy row to ensure we do not get weird double inputs
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.0
         * @return  void
         */

    }, {
        key: 'repeaterBeforeInit',
        value: function repeaterBeforeInit($repeater, options) {

            this.fieldCleanup();
        }

        /**
         * Ensure that the purposefully unloaded Flatpickr reloads
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.0
         * @return  void
         */

    }, {
        key: 'repeaterOnInit',
        value: function repeaterOnInit($repeater, options) {

            this.initField();
        }
    }]);

    return FieldDatePicker;
}(_field2.default);

/**
 * Finds and initializes all Date Picker fields.
 *
 * @since 1.4.0
 */


var FieldDatePickerInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldDatePickerInitialize($root) {
        _classCallCheck(this, FieldDatePickerInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-datepicker]');

        if ($fields.length) {

            if (!jQuery.isFunction(jQuery.fn.flatpickr)) {

                console.error('Field Helpers Error: Trying to initialize Date Picker field but "flatpickr" ' + 'is not enqueued.');
                return;
            }

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldDatePickerInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldDatePicker($field)
            });
        }
    }]);

    return FieldDatePickerInitialize;
}();

exports.default = FieldDatePickerInitialize;

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

var _fieldsInit = __webpack_require__(1);

var _fieldsInit2 = _interopRequireDefault(_fieldsInit);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Time Picker Field functionality.
 *
 * Also includes Date/Time Picker and Time Picker.
 *
 * @since 1.4.0
 */
var FieldTimePicker = function (_Field) {
    _inherits(FieldTimePicker, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldTimePicker($field) {
        _classCallCheck(this, FieldTimePicker);

        var _this = _possibleConstructorReturn(this, (FieldTimePicker.__proto__ || Object.getPrototypeOf(FieldTimePicker)).call(this, $field, 'timepicker'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the Time Picker.
     *
     * @since 1.4.0
     */


    _createClass(FieldTimePicker, [{
        key: 'initField',
        value: function initField() {
            var _this2 = this;

            var option_functions = ['onChange', 'onOpen', 'onClose', 'onMonthChange', 'onYearChange', 'onReady', 'onValueUpdate', 'onDayCreate'];

            // Function support
            jQuery.each(this.options.timepickerOptions, function (name, value) {

                if (option_functions.indexOf(name) !== -1 && !jQuery.isFunction(_this2.options.timepickerOptions[name]) && jQuery.isFunction(window[value])) {

                    _this2.options.timepickerOptions[name] = window[value];
                }
            });

            // We need to ensure that the field instance for our specific field loads its default date in properly
            this.options.timepickerOptions.defaultDate = this.$field.data('defaultdate');

            var value = this.$field.val();

            if (!value) value = this.options.timepickerOptions.defaultDate;

            this.flatpickr = this.$field.flatpickr(this.options.timepickerOptions);

            this.flatpickr.setDate(value, true);
        }

        /**
         * Cleans up after a repeater add/init.
         *
         * @since 1.4.0
         */

    }, {
        key: 'fieldCleanup',
        value: function fieldCleanup() {

            var value = this.$field.val();

            if (!value) value = this.options.timepickerOptions.defaultDate;

            if (typeof this.flatpickr !== 'undefined') {

                this.flatpickr.destroy();
            }

            this.$field.val(value);
        }

        /**
         * Runs cleanup before the Repeater creates a dummy row to ensure we do not get weird double inputs
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.0
         * @return  void
         */

    }, {
        key: 'repeaterBeforeInit',
        value: function repeaterBeforeInit($repeater, options) {

            this.fieldCleanup();
        }

        /**
         * Ensure that the purposefully unloaded Flatpickr reloads
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.0
         * @return  void
         */

    }, {
        key: 'repeaterOnInit',
        value: function repeaterOnInit($repeater, options) {

            this.initField();
        }
    }]);

    return FieldTimePicker;
}(_field2.default);

/**
 * Finds and initializes all Time Picker fields.
 *
 * @since 1.4.0
 */


var FieldTimePickerInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldTimePickerInitialize($root) {
        _classCallCheck(this, FieldTimePickerInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-timepicker]');

        if ($fields.length) {

            if (!jQuery.isFunction(jQuery.fn.flatpickr)) {

                console.error('Field Helpers Error: Trying to initialize Time Picker field but ' + '"flatpickr" is not enqueued.');
                return;
            }

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldTimePickerInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldTimePicker($field)
            });
        }
    }]);

    return FieldTimePickerInitialize;
}();

exports.default = FieldTimePickerInitialize;

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Date Time Picker Field functionality.
 *
 * Also includes Date/Time Picker and Time Picker.
 *
 * @since 1.4.0
 */
var FieldDateTimePicker = function (_Field) {
    _inherits(FieldDateTimePicker, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldDateTimePicker($field) {
        _classCallCheck(this, FieldDateTimePicker);

        var _this = _possibleConstructorReturn(this, (FieldDateTimePicker.__proto__ || Object.getPrototypeOf(FieldDateTimePicker)).call(this, $field, 'datetimepicker'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the Date Time Picker.
     *
     * @since 1.4.0
     */


    _createClass(FieldDateTimePicker, [{
        key: 'initField',
        value: function initField() {
            var _this2 = this;

            var option_functions = ['onChange', 'onOpen', 'onClose', 'onMonthChange', 'onYearChange', 'onReady', 'onValueUpdate', 'onDayCreate'];

            // Function support
            jQuery.each(this.options.datetimepickerOptions, function (name, value) {

                if (option_functions.indexOf(name) !== -1 && !jQuery.isFunction(_this2.options.datetimepickerOptions[name]) && jQuery.isFunction(window[value])) {

                    _this2.options.datetimepickerOptions[name] = window[value];
                }
            });

            // We need to ensure that the field instance for our specific field loads its default date in properly
            this.options.datetimepickerOptions.defaultDate = this.$field.data('defaultdate');

            var value = this.$field.val();

            if (!value) value = this.options.datetimepickerOptions.defaultDate;

            this.flatpickr = this.$field.flatpickr(this.options.datetimepickerOptions);

            this.flatpickr.setDate(value, true);
        }

        /**
         * Cleans up after a repeater add/init.
         *
         * @since 1.4.0
         */

    }, {
        key: 'fieldCleanup',
        value: function fieldCleanup() {

            var value = this.$field.val();

            if (!value) value = this.options.datetimepickerOptions.defaultDate;

            if (typeof this.flatpickr !== 'undefined') {

                this.flatpickr.destroy();
            }

            this.$field.val(value);
        }

        /**
         * Runs cleanup before the Repeater creates a dummy row to ensure we do not get weird double inputs
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.0
         * @return  void
         */

    }, {
        key: 'repeaterBeforeInit',
        value: function repeaterBeforeInit($repeater, options) {

            this.fieldCleanup();
        }

        /**
         * Ensure that the purposefully unloaded Flatpickr reloads
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.0
         * @return  void
         */

    }, {
        key: 'repeaterOnInit',
        value: function repeaterOnInit($repeater, options) {

            this.initField();
        }
    }]);

    return FieldDateTimePicker;
}(_field2.default);

/**
 * Finds and initializes all Date Time Picker fields.
 *
 * @since 1.4.0
 */


var FieldDateTimePickerInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldDateTimePickerInitialize($root) {
        _classCallCheck(this, FieldDateTimePickerInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-datetimepicker]');

        if ($fields.length) {

            if (!jQuery.isFunction(jQuery.fn.flatpickr)) {

                console.error('Field Helpers Error: Trying to initialize Date Time Picker field but ' + '"flatpickr" is not enqueued.');
                return;
            }

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldDateTimePickerInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldDateTimePicker($field)
            });
        }
    }]);

    return FieldDateTimePickerInitialize;
}();

exports.default = FieldDateTimePickerInitialize;

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Table Field functionality.
 *
 * @since 1.4.0
 */
var FieldTable = function (_Field) {
    _inherits(FieldTable, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldTable($field) {
        _classCallCheck(this, FieldTable);

        var _this = _possibleConstructorReturn(this, (FieldTable.__proto__ || Object.getPrototypeOf(FieldTable)).call(this, $field, 'table'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the Table field.
     *
     * @since 1.4.0
     */


    _createClass(FieldTable, [{
        key: 'initField',
        value: function initField() {

            this.$ui = {
                actions: this.$field.find('.fieldhelpers-field-table-actions'),
                loading: this.$field.find('.fieldhelpers-field-table-loading'),
                table: this.$field.find('table'),
                thead: this.$field.find('thead'),
                tbody: this.$field.find('tbody'),
                addRow: this.$field.find('[data-table-create-row]'),
                addColumn: this.$field.find('[data-table-create-column]')
            };

            this.l10n = RBM_FieldHelpers.l10n['field_table'] || {};

            this.name = this.$field.attr('data-table-name');

            var data = JSON.parse(this.$ui.table.attr('data-table-data'));

            this.data = {};
            this.data.head = data.head || [];
            this.data.body = data.body || [];

            this.setupHandlers();

            // Initial build
            this.buildTable();

            // Show initially
            this.$ui.table.show();
            this.$ui.actions.show();
            this.$ui.loading.hide();
        }

        /**
         * Sets up the class handlers.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setupHandlers',
        value: function setupHandlers() {
            var _this2 = this;

            var api = this;

            this.$ui.addRow.click(function (e) {

                e.preventDefault();
                _this2.addRow();
            });

            this.$ui.addColumn.click(function (e) {

                e.preventDefault();
                _this2.addColumn();
            });

            this.$ui.table.on('click', '[data-delete-row]', function (e) {

                var index = jQuery(this).closest('tr').index();

                api.deleteRow(index);
            });

            this.$ui.table.on('click', '[data-delete-column]', function (e) {

                var index = jQuery(this).closest('td').index();

                api.deleteColumn(index);
            });

            this.$ui.table.on('change', 'input[type="text"]', function (e) {

                _this2.updateTableData();
            });
        }

        /**
         * Gathers all data from the table.
         */

    }, {
        key: 'updateTableData',
        value: function updateTableData() {

            var api = this;

            // Head
            var $headCells = this.$ui.table.find('thead th');
            var dataHead = [];
            var currentCell = 0;

            $headCells.each(function () {

                var $input = jQuery(this).find('input[name="' + api.name + '[head][' + currentCell + ']"]');

                if (!$input.length) {

                    console.error('Field Helpers Error: Table head data corrupted.');
                    return false;
                }

                dataHead.push($input.val());

                currentCell++;
            });

            this.data.head = dataHead;

            // Body
            var $bodyRows = this.$ui.table.find('tbody tr');
            var dataBody = [];
            var currentRow = 0;

            $bodyRows.each(function () {

                // Skip delete row
                if (jQuery(this).hasClass('fieldhelpers-field-table-delete-columns')) {

                    return true;
                }

                var rowData = [];
                var $cells = jQuery(this).find('td');
                var currentCell = 0;

                $cells.each(function () {

                    // Skip delete cell
                    if (jQuery(this).hasClass('fieldhelpers-field-table-delete-row')) {

                        return true;
                    }

                    var $input = jQuery(this).find('input[name="' + api.name + '[body][' + currentRow + '][' + currentCell + ']"]');

                    if (!$input.length) {

                        console.error('Field Helpers Error: Table body data corrupted.');
                        return false;
                    }

                    rowData.push($input.val());

                    currentCell++;
                });

                dataBody.push(rowData);

                currentRow++;
            });

            this.data.body = dataBody;
        }

        /**
         * Adds a row to the table.
         *
         * @since 1.4.0
         */

    }, {
        key: 'addRow',
        value: function addRow() {

            if (!this.data.head.length) {

                this.data.head.push('');
            }

            if (!this.data.body.length) {

                // Push 1 empty row with 1 empty cell
                this.data.body.push(['']);
            } else {

                var columns = this.data.body[0].length;
                var row = [];

                for (var i = 0; i < columns; i++) {
                    row.push('');
                }

                this.data.body.push(row);
            }

            this.buildTable();
        }

        /**
         * Adds a column to the table.
         *
         * @since 1.4.0
         */

    }, {
        key: 'addColumn',
        value: function addColumn() {

            if (!this.data.body.length) {

                // Push 1 empty row with 1 empty cell
                this.data.head.push(['']);
                this.data.body.push(['']);
            } else {

                this.data.head.push('');

                this.data.body.map(function (row) {
                    row.push('');
                });
            }

            this.buildTable();
        }

        /**
         * Deletes a row from the table.
         *
         * @since 1.4.0
         *
         * @param {int} index Index of row to delete.
         */

    }, {
        key: 'deleteRow',
        value: function deleteRow(index) {

            // Decrease to compensate for "delete row" at top
            index--;

            if (this.data.body.length === 1) {

                this.data.head = [];
                this.data.body = [];
            } else {

                this.data.body.splice(index, 1);
            }

            this.buildTable();
        }

        /**
         * Deletes a column from the table.
         *
         * @since 1.4.0
         *
         * @param {int} index Index of column to delete.
         */

    }, {
        key: 'deleteColumn',
        value: function deleteColumn(index) {

            if (this.data.body[0].length === 1) {

                this.data.head = [];
                this.data.body = [];
            } else {

                this.data.head.splice(index, 1);

                this.data.body.map(function (row) {
                    return row.splice(index, 1);
                });
            }

            this.buildTable();
        }

        /**
         * Builds the table based on the table data.
         *
         * @since 1.4.0
         */

    }, {
        key: 'buildTable',
        value: function buildTable() {
            var _this3 = this;

            this.$ui.thead.html('');
            this.$ui.tbody.html('');

            if (this.data.head.length) {

                var $row = jQuery('<tr />');

                this.data.head.map(function (cell, cell_i) {

                    var $cell = jQuery('<th />');

                    $cell.append('<input type="text" name="' + _this3.name + '[head][' + cell_i + ']" />');
                    $cell.find('input[type="text"]').val(cell);

                    $row.append($cell);
                });

                this.$ui.thead.append($row);
            }

            if (this.data.body.length) {

                var $deleteRow = jQuery('<tr class="fieldhelpers-field-table-delete-columns"></tr>');

                for (var i = 0; i < this.data.body[0].length; i++) {

                    $deleteRow.append('<td>' + ('<button type="button" data-delete-column aria-label="' + this.l10n['delete_column'] + '">') + '<span class="dashicons dashicons-no" />' + '</button>' + '</td>');
                }

                this.$ui.tbody.append($deleteRow);

                this.data.body.map(function (row, row_i) {

                    var $row = jQuery('<tr/>');

                    row.map(function (cell, cell_i) {

                        var $cell = jQuery('<td/>');

                        $cell.append('<input type="text" name="' + _this3.name + '[body][' + row_i + '][' + cell_i + ']" />');
                        $cell.find('input[type="text"]').val(cell);

                        $row.append($cell);
                    });

                    $row.append('<td class="fieldhelpers-field-table-delete-row">' + ('<button type="button" data-delete-row aria-label="' + _this3.l10n['delete_row'] + '">') + '<span class="dashicons dashicons-no" />' + '</button>' + '</td>');

                    _this3.$ui.tbody.append($row);
                });
            }
        }
    }]);

    return FieldTable;
}(_field2.default);

/**
 * Finds and initializes all Table fields.
 *
 * @since 1.4.0
 */


var FieldTableInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldTableInitialize($root) {
        _classCallCheck(this, FieldTableInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-table]');

        if ($fields.length) {

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldTableInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldTable($field)
            });
        }
    }]);

    return FieldTableInitialize;
}();

exports.default = FieldTableInitialize;

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Media Field functionality.
 *
 * @since 1.4.0
 */
var FieldMedia = function (_Field) {
    _inherits(FieldMedia, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldMedia($field) {
        _classCallCheck(this, FieldMedia);

        var _this = _possibleConstructorReturn(this, (FieldMedia.__proto__ || Object.getPrototypeOf(FieldMedia)).call(this, $field, 'media'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the Media field.
     *
     * @since 1.4.0
     */


    _createClass(FieldMedia, [{
        key: 'initField',
        value: function initField() {

            this.$ui = {
                input: this.$field.find('[data-media-input]'),
                addButton: this.$field.find('[data-add-media]'),
                imagePreview: this.$field.find('[data-image-preview]'),
                mediaPreview: this.$field.find('[data-media-preview]'),
                removeButton: this.$field.find('[data-remove-media]')
            };

            this.mediaFrame = wp.media.frames.meta_image_frame = wp.media({
                title: this.options.l10n['window_title']
            });

            this.placeholder = this.options.placeholder;
            this.type = this.options.type;

            this.imageProperties = {
                previewSize: this.options.previewSize
            };

            this.setupHandlers();
        }

        /**
         * Sets up the class handlers.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setupHandlers',
        value: function setupHandlers() {
            var _this2 = this;

            this.$ui.addButton.click(function (e) {

                e.preventDefault();
                _this2.addMedia();
            });

            this.$ui.removeButton.click(function (e) {

                e.preventDefault();
                _this2.removeMedia();
            });

            this.mediaFrame.on('select', function (e) {

                _this2.selectMedia();
            });
        }

        /**
         * Opens the media frame to add media.
         *
         * @since 1.4.0
         */

    }, {
        key: 'addMedia',
        value: function addMedia() {

            this.mediaFrame.open();
        }

        /**
         * Removes the currently selected media.
         *
         * @since 1.4.0
         */

    }, {
        key: 'removeMedia',
        value: function removeMedia() {

            this.$ui.addButton.show();
            this.$ui.removeButton.hide();
            this.$ui.input.val('');

            // Reset preview
            switch (this.type) {

                case 'image':

                    this.$ui.imagePreview.attr('src', this.placeholder || '');

                    break;

                default:

                    this.$ui.mediaPreview.html(this.placeholder || '&nbsp;');
            }
        }

        /**
         * Fires on selecting a piece of media.
         *
         * @since 1.4.0
         */

    }, {
        key: 'selectMedia',
        value: function selectMedia() {

            var mediaAttachment = this.mediaFrame.state().get('selection').first().toJSON();

            this.$ui.input.val(mediaAttachment.id);

            this.$ui.addButton.hide();
            this.$ui.removeButton.show();

            // Preview
            switch (this.type) {

                case 'image':

                    var previewUrl = mediaAttachment.url;

                    if (mediaAttachment.sizes[this.imageProperties.previewSize]) {

                        previewUrl = mediaAttachment.sizes[this.imageProperties.previewSize].url;
                    }

                    this.$ui.imagePreview.attr('src', previewUrl);
                    break;

                default:

                    this.$ui.mediaPreview.html(mediaAttachment.url);
            }
        }
    }]);

    return FieldMedia;
}(_field2.default);

/**
 * Finds and initializes all Media fields.
 *
 * @since 1.4.0
 */


var FieldMediaInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldMediaInitialize($root) {
        _classCallCheck(this, FieldMediaInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-media]');

        if ($fields.length) {

            if (!wp.media) {

                console.error('Field Helpers Error: Trying to initialize Media field but media is not enqueued.');
                return;
            }

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldMediaInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldMedia($field)
            });
        }
    }]);

    return FieldMediaInitialize;
}();

exports.default = FieldMediaInitialize;

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * List Field functionality.
 *
 * @since 1.4.0
 */
var FieldList = function (_Field) {
    _inherits(FieldList, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldList($field) {
        _classCallCheck(this, FieldList);

        var _this = _possibleConstructorReturn(this, (FieldList.__proto__ || Object.getPrototypeOf(FieldList)).call(this, $field, 'list'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the list.
     *
     * @since 1.4.0
     */


    _createClass(FieldList, [{
        key: 'initField',
        value: function initField() {

            this.$field.sortable(this.options);
        }
    }]);

    return FieldList;
}(_field2.default);

/**
 * Finds and initializes all List fields.
 *
 * @since 1.4.0
 */


var FieldListInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldListInitialize($root) {
        _classCallCheck(this, FieldListInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-list]');

        if ($fields.length) {

            if (!jQuery.isFunction(jQuery.fn.sortable)) {

                console.error('Field Helpers Error: Trying to initialize List field but "jquery-ui-sortable" ' + 'is not enqueued.');
                return;
            }

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldListInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldList($field)
            });
        }
    }]);

    return FieldListInitialize;
}();

exports.default = FieldListInitialize;

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

var _fieldsInit = __webpack_require__(1);

var _fieldsInit2 = _interopRequireDefault(_fieldsInit);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Repeater Field functionality.
 *
 * @since 1.4.0
 */
var FieldRepeater = function (_Field) {
    _inherits(FieldRepeater, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldRepeater($field) {
        _classCallCheck(this, FieldRepeater);

        var _this = _possibleConstructorReturn(this, (FieldRepeater.__proto__ || Object.getPrototypeOf(FieldRepeater)).call(this, $field, 'repeater'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the Repeater.
     *
     * @since 1.4.0
     */


    _createClass(FieldRepeater, [{
        key: 'initField',
        value: function initField() {
            var _this2 = this;

            if (this.$field.data('rbmFHeventListenersCreated')) return;

            this.$repeaterList = this.$field.find('.fieldhelpers-field-repeater-list');

            var api = this;

            this.$field.trigger('repeater-before-init', [this.$field, this.options]);

            this.repeater = this.$field.repeater({
                show: function show() {
                    api.repeaterShow(jQuery(this));
                },
                hide: function hide(deleteItem) {
                    api.repeaterHide(jQuery(this), deleteItem);
                },
                ready: function ready(setIndexes) {
                    api.$repeaterList.on('sortupdate', setIndexes);
                },
                isFirstItemUndeletable: api.options.isFirstItemUndeletable
            });

            // Delete first item if allowed and empty
            if (!this.options.isFirstItemUndeletable && this.options.empty) {
                this.$repeaterList.find('.fieldhelpers-field-repeater-row').remove();
            }

            if (this.options.collapsable) {

                this.initCollapsable();
            }

            if (this.options.sortable) {

                if (!jQuery.isFunction(jQuery.fn.sortable)) {

                    console.error('Field Helpers Error: Trying to initialize sortable Repeater field but "jquery-ui-sortable" ' + 'is not enqueued.');
                    return;
                } else {

                    this.initSortable();
                }
            }

            this.$field.data('rbmFHeventListenersCreated', true);

            // Delay for other plugins
            setTimeout(function () {
                _this2.$field.trigger('repeater-init', [_this2.$field]);
            }, 1);
        }

        /**
         * Initializes the Collapsable feature, if enabled.
         *
         * @since 1.4.0
         */

    }, {
        key: 'initCollapsable',
        value: function initCollapsable() {

            var api = this;

            this.$field.on('click touchend', '[data-repeater-collapsable-handle]', function () {
                console.log('click');
                api.toggleCollapse(jQuery(this).closest('.fieldhelpers-field-repeater-row'));
            });
        }

        /**
         * Initializes the Sortable feature, if enabled.
         *
         * @since 1.4.0
         */

    }, {
        key: 'initSortable',
        value: function initSortable() {

            var api = this;

            this.$repeaterList.sortable({
                axis: 'y',
                handle: '.fieldhelpers-field-repeater-handle',
                forcePlaceholderSize: true,
                placeholder: 'fieldhelpers-sortable-placeholder',
                stop: function stop(e, ui) {

                    api.$repeaterList.trigger('list-update', [api.$repeaterList]);
                }
            });
        }

        /**
         * Toggles a repeater item collapse.
         *
         * @since 1.4.0
         *
         * @param {jQuery} $item
         */

    }, {
        key: 'toggleCollapse',
        value: function toggleCollapse($item) {

            var $content = $item.find('.fieldhelpers-field-repeater-content').first();
            var status = $item.hasClass('opened') ? 'closing' : 'opening';

            if (status === 'opening') {

                $content.stop().slideDown();
                $item.addClass('opened');
                $item.removeClass('closed');
            } else {

                $content.stop().slideUp();
                $item.addClass('closed');
                $item.removeClass('opened');
            }
        }

        /**
         * Shows a new repeater item.
         *
         * @since 1.4.0
         *
         * @param {jQuery} $item Repeater item row.
         */

    }, {
        key: 'repeaterShow',
        value: function repeaterShow($item) {

            this.$field.trigger('repeater-before-add-item', [$item]);

            $item.slideDown();

            if (this.$repeaterList.hasClass('collapsable')) {

                $item.addClass('opened').removeClass('closed');

                // Hide current title for new item and show default title
                $item.find('.fieldhelpers-field-repeater-header span.collapsable-title').html($item.find('.fieldhelpers-field-repeater-header span.collapsable-title').data('collapsable-title-default'));

                $item.find('.collapse-icon').css({ 'transform': 'rotate(-180deg)' });
            }

            // Re-initialize fields in new row
            new _fieldsInit2.default($item);

            this.$field.trigger('repeater-add-item', [$item]);
        }

        /**
         * Removes a repeater item.
         *
         * @since 1.4.0
         *
         * @param {jQuery} $item Repeater item row.
         * @param {function} deleteItem Callback for deleting the item.
         */

    }, {
        key: 'repeaterHide',
        value: function repeaterHide($item, deleteItem) {
            var _this3 = this;

            if (confirm(this.options.l10n['confirm_delete_text'])) {

                this.$field.trigger('repeater-before-delete-item', [$item]);

                $item.slideUp(400, function () {

                    deleteItem();
                    _this3.$field.trigger('repeater-delete-item', [$item]);
                });
            }
        }
    }]);

    return FieldRepeater;
}(_field2.default);

/**
 * Finds and initializes all Repeater fields.
 *
 * @since 1.4.0
 */


var FieldRepeaterInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldRepeaterInitialize($root) {
        _classCallCheck(this, FieldRepeaterInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-repeater]');

        if ($fields.length) {

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldRepeaterInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldRepeater($field)
            });
        }
    }]);

    return FieldRepeaterInitialize;
}();

exports.default = FieldRepeaterInitialize;

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Select Field functionality.
 *
 * @since 1.4.0
 */
var FieldSelect = function (_Field) {
    _inherits(FieldSelect, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldSelect($field) {
        _classCallCheck(this, FieldSelect);

        var _this = _possibleConstructorReturn(this, (FieldSelect.__proto__ || Object.getPrototypeOf(FieldSelect)).call(this, $field, 'select'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the select.
     *
     * @since 1.4.0
     */


    _createClass(FieldSelect, [{
        key: 'initField',
        value: function initField() {

            if (!this.options.select2Disabled) {

                if (!jQuery.isFunction(jQuery.fn.rbmfhselect2)) {

                    console.error('Field Helpers Error: Trying to initialize Select field but "select2" ' + 'is not enqueued.');
                    return;
                }

                this.setupSelect2Options();

                this.$field.rbmfhselect2(this.options.select2Options);
            }
        }

        /**
         * Sets up languages.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setupL10n',
        value: function setupL10n() {
            var _this2 = this;

            if (Object.keys(this.options.select2Options.language).length > 0) {

                Object.keys(this.options.select2Options.language).map(function (id) {

                    var text = _this2.options.select2Options.language[id];

                    // All languages must be functions. Turn all into functions.
                    _this2.options.select2Options.language[id] = function (args) {
                        return text;
                    };
                });
            }
        }

        /**
         * Sets up Select2 arguments, allowing for callback arguments.
         *
         * @since 1.4.2
         */

    }, {
        key: 'setupSelect2Options',
        value: function setupSelect2Options() {
            var _this3 = this;

            this.setupL10n();

            // List of available Select2 options that are callbacks
            var callbackOptions = ['escapeMarkup', 'initSelection', 'matcher', 'query', 'sorter', 'templateResult', 'templateSelection', 'tokenizer'];

            Object.keys(this.options.select2Options).map(function (name) {

                if (callbackOptions.indexOf(name) !== -1) {

                    var callbackName = _this3.options.select2Options[name];

                    if (typeof window[callbackName] === 'function') {

                        _this3.options.select2Options[name] = window[callbackName];
                    }
                }
            });

            // Automatically prefix selected items with optgroup label, if using optgroups
            if (this.options.optGroups && this.options.optGroupSelectionPrefix && typeof this.options.select2Options.templateSelection === 'undefined') {

                this.options.select2Options.templateSelection = function (item) {

                    var $optgroup = jQuery(item.element).closest('optgroup');

                    if ($optgroup.length <= 0) return item.text;

                    return $optgroup.attr('label').trim() + ': ' + item.text;
                };
            }
        }

        /**
         * Resets the field.
         *
         * @since 1.4.0
         */

    }, {
        key: 'fieldCleanup',
        value: function fieldCleanup() {

            if (this.options.select2Disabled) {

                return;
            }

            var $oldSelect = this.$field.next('.select2');

            if ($oldSelect.length) {

                $oldSelect.remove();
            }

            this.$field.removeClass('select2-hidden-accessible').removeAttr('tablindex aria-hidden');
        }

        /**
         * Sets the field to default. Override in child class if need different method.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setDefault',
        value: function setDefault() {

            this.$field.find('option:selected').prop('selected', false);
            this.$field.trigger('change');
        }
    }]);

    return FieldSelect;
}(_field2.default);

/**
 * Finds and initializes all Select fields.
 *
 * @since 1.4.0
 */


var FieldSelectInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldSelectInitialize($root) {
        _classCallCheck(this, FieldSelectInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-select]');

        if ($fields.length) {

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldSelectInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldSelect($field)
            });
        }
    }]);

    return FieldSelectInitialize;
}();

exports.default = FieldSelectInitialize;

/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * TextArea Field functionality.
 *
 * @since 1.4.0
 */
var FieldTextArea = function (_Field) {
    _inherits(FieldTextArea, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldTextArea($field) {
        _classCallCheck(this, FieldTextArea);

        var _this = _possibleConstructorReturn(this, (FieldTextArea.__proto__ || Object.getPrototypeOf(FieldTextArea)).call(this, $field, 'textarea'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the WYSIWYG.
     *
     * @since 1.4.0
     */


    _createClass(FieldTextArea, [{
        key: 'initField',
        value: function initField() {

            if (this.options.wysiwyg) {

                if (!wp.editor) {

                    console.error('Field Helpers Error: Trying to initialize a WYSIWYG Text Area field but "wp_editor" ' + 'is not enqueued.');
                    return;
                }

                var settings = jQuery.extend(this.getDefaultEditorSettings(), this.options.wysiwygOptions);

                wp.editor.initialize(this.$field.attr('id'), settings);
            }
        }

        /**
         * Resets the field.
         *
         * @since 1.4.0
         */

    }, {
        key: 'fieldCleanup',
        value: function fieldCleanup() {

            if (this.options.wysiwyg) {

                var id = this.$field.attr('id');

                if (window.tinymce.get(id)) {

                    wp.editor.remove(id);
                } else {

                    this.$field.appendTo(this.$wrapper.find('.fieldhelpers-field-content'));
                    this.$wrapper.find('.wp-editor-wrap').remove();
                }
            }
        }

        /**
         * Fires before deleting the item from a repeater.
         *
         * Removes from wp.editor.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterBeforeDeleteSelf',
        value: function repeaterBeforeDeleteSelf() {

            this.fieldCleanup();
        }

        /**
         * Fires on Repeat delete item.
         *
         * Adds slight delay to field re-initialization.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterOnDeleteItem',
        value: function repeaterOnDeleteItem() {
            var _this2 = this;

            this.fieldCleanup();
            this.repeaterSetID();

            // Add slight delay because all repeater item WYSIWYG's must be unitialized before re-initializing to prevent
            // ID overlap.
            setTimeout(function () {
                _this2.initField();
            }, 1);
        }

        /**
         * Fires on Repeat sort item.
         *
         * Adds slight delay to field re-initialization.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterOnSort',
        value: function repeaterOnSort() {
            var _this3 = this;

            this.fieldCleanup();
            this.repeaterSetID();

            // Add slight delay because all repeater item WYSIWYG's must be unitialized before re-initializing to prevent
            // ID overlap.
            setTimeout(function () {
                _this3.initField();
            }, 1);
        }

        /**
         * Tries to get default editor settings.
         *
         * @since 1.4.0
         *
         * @return {{}}
         */

    }, {
        key: 'getDefaultEditorSettings',
        value: function getDefaultEditorSettings() {

            if (!jQuery.isFunction(wp.editor.getDefaultSettings)) {

                return {};
            } else {

                return wp.editor.getDefaultSettings();
            }
        }
    }]);

    return FieldTextArea;
}(_field2.default);

/**
 * Finds and initializes all TextArea fields.
 *
 * @since 1.4.0
 */


var FieldTextAreaInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldTextAreaInitialize($root) {
        _classCallCheck(this, FieldTextAreaInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-textarea]');

        if ($fields.length) {

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldTextAreaInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldTextArea($field)
            });
        }
    }]);

    return FieldTextAreaInitialize;
}();

exports.default = FieldTextAreaInitialize;

/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Checkbox Field functionality.
 *
 * @since 1.4.0
 */
var FieldCheckbox = function (_Field) {
    _inherits(FieldCheckbox, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldCheckbox($field) {
        _classCallCheck(this, FieldCheckbox);

        var _this = _possibleConstructorReturn(this, (FieldCheckbox.__proto__ || Object.getPrototypeOf(FieldCheckbox)).call(this, $field, 'checkbox'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the select.
     *
     * @since 1.4.0
     */


    _createClass(FieldCheckbox, [{
        key: 'initField',
        value: function initField() {

            this.$ui = {
                checkboxes: this.$field.find('input[type="checkbox"]')
            };

            this.setupHandlers();

            this.$field.find('input:checked').trigger('change');
        }

        /**
         * Sets up class handlers.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setupHandlers',
        value: function setupHandlers() {

            var api = this;

            this.$ui.checkboxes.change(function () {
                api.handleChange(jQuery(this));
            });
        }

        /**
         * Fires on checkbox change.
         *
         * @since 1.4.0
         *
         * @param {jQuery} $input Checkbox input.
         */

    }, {
        key: 'handleChange',
        value: function handleChange($input) {

            if ($input.prop('checked')) {

                this.setActive($input.closest('.fieldhelpers-field-checkbox-row'));
            } else {

                this.setInactive($input.closest('.fieldhelpers-field-checkbox-row'));
            }
        }

        /**
         * Sets the checkbox row as active.
         *
         * @since 1.4.0
         *
         * @param {jQuery} $row
         */

    }, {
        key: 'setActive',
        value: function setActive($row) {

            $row.addClass('fieldhelpers-field-checkbox-row-active');
        }

        /**
         * Sets the checkbox row as inactive.
         *
         * @since 1.4.0
         *
         * @param {jQuery} $row
         */

    }, {
        key: 'setInactive',
        value: function setInactive($row) {

            $row.removeClass('fieldhelpers-field-checkbox-row-active');
        }

        /**
         * Sets the ID to be unique, based off the repeater item index.
         *
         * For checkboxes, there will be multiple IDs in each, and need to be set accordingly.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterSetID',
        value: function repeaterSetID() {

            var ID = this.options.id;
            var $rows = this.$field.find('.fieldhelpers-field-checkbox-row');
            var index = this.$field.closest('[data-repeater-item]').index();

            $rows.each(function () {

                var $field = jQuery(this).find('input[type="checkbox"]');
                var $label = $field.next('label');
                var fieldIndex = jQuery(this).index();
                var newID = ID + '_' + index + '_' + fieldIndex;

                $field.attr('id', newID);
                $label.attr('for', newID);
            });
        }

        /**
         * Cleans up after a repeater add/init.
         *
         * @since 1.5.8
         */

    }, {
        key: 'fieldCleanup',
        value: function fieldCleanup() {

            var api = this;

            this.$field.find('input').each(function (index, input) {
                api.setInactive(jQuery(input).closest('.fieldhelpers-field-checkbox-row'));
            });
        }

        /**
         * Ensure that our styling is reapplied
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.8
         * @return  void
         */

    }, {
        key: 'repeaterOnInit',
        value: function repeaterOnInit($repeater, options) {

            this.initField();
        }

        /**
         * Runs cleanup before the Repeater creates a dummy row to clear out selected items
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.8
         * @return  void
         */

    }, {
        key: 'repeaterBeforeInit',
        value: function repeaterBeforeInit($repeater, options) {

            this.fieldCleanup();
        }

        /**
         * Sets the field to default.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setDefault',
        value: function setDefault() {

            if (this.options.default) {

                this.$field.find('[value="' + this.options.default + '"]').prop('checked', true).change();
            }
        }
    }]);

    return FieldCheckbox;
}(_field2.default);

/**
 * Finds and initializes all Checkbox fields.
 *
 * @since 1.4.0
 */


var FieldCheckboxInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldCheckboxInitialize($root) {
        _classCallCheck(this, FieldCheckboxInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-checkbox]');

        if ($fields.length) {

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldCheckboxInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldCheckbox($field)
            });
        }
    }]);

    return FieldCheckboxInitialize;
}();

exports.default = FieldCheckboxInitialize;

/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Radio Field functionality.
 *
 * @since 1.4.0
 */
var FieldRadio = function (_Field) {
    _inherits(FieldRadio, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldRadio($field) {
        _classCallCheck(this, FieldRadio);

        var _this = _possibleConstructorReturn(this, (FieldRadio.__proto__ || Object.getPrototypeOf(FieldRadio)).call(this, $field, 'radio'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the select.
     *
     * @since 1.4.0
     */


    _createClass(FieldRadio, [{
        key: 'initField',
        value: function initField() {

            this.$ui = {
                radios: this.$field.find('input[type="radio"]')
            };

            this.setupHandlers();

            this.$field.find('input:checked').trigger('change');
        }

        /**
         * Sets up class handlers.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setupHandlers',
        value: function setupHandlers() {

            var api = this;

            this.$ui.radios.change(function () {
                api.handleChange(jQuery(this));
            });
        }

        /**
         * Fires on radio change.
         *
         * @since 1.4.0
         *
         * @param {jQuery} $input Checkbox input.
         */

    }, {
        key: 'handleChange',
        value: function handleChange($input) {

            this.setInactive(this.$ui.radios.closest('.fieldhelpers-field-radio-row'));
            this.setActive($input.closest('.fieldhelpers-field-radio-row'));
        }

        /**
         * Sets the radio row as active.
         *
         * @since 1.4.0
         *
         * @param {jQuery} $row
         */

    }, {
        key: 'setActive',
        value: function setActive($row) {

            $row.addClass('fieldhelpers-field-radio-row-active');
        }

        /**
         * Sets the radio row as inactive.
         *
         * @since 1.4.0
         *
         * @param {jQuery} $row
         */

    }, {
        key: 'setInactive',
        value: function setInactive($row) {

            $row.removeClass('fieldhelpers-field-radio-row-active');
        }

        /**
         * Sets the ID to be unique, based off the repeater item index.
         *
         * For radios, there will be multiple IDs in each, and need to be set accordingly.
         *
         * @since 1.4.0
         */

    }, {
        key: 'repeaterSetID',
        value: function repeaterSetID() {

            var ID = this.options.id;
            var $rows = this.$field.find('.fieldhelpers-field-radio-row');
            var index = this.$field.closest('[data-repeater-item]').index();

            $rows.each(function () {

                var $field = jQuery(this).find('input[type="radio"]');
                var $label = $field.next('label');
                var fieldIndex = jQuery(this).index();
                var newID = ID + '_' + index + '_' + fieldIndex;

                $field.attr('id', newID);
                $label.attr('for', newID);
            });
        }

        /**
         * Cleans up after a repeater add/init.
         *
         * @since 1.5.8
         */

    }, {
        key: 'fieldCleanup',
        value: function fieldCleanup() {

            var api = this;

            this.$field.find('input').each(function (index, input) {
                api.setInactive(jQuery(input).closest('.fieldhelpers-field-radio-row'));
            });
        }

        /**
         * Ensure that our styling is reapplied
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.8
         * @return  void
         */

    }, {
        key: 'repeaterOnInit',
        value: function repeaterOnInit($repeater, options) {

            this.initField();
        }

        /**
         * Runs cleanup before the Repeater creates a dummy row to clear out selected items
         *
         * @param   {object}  $repeater  jQuery DOM Object
         * @param   {array}  options     Array of Field Options
         *
         * @since   1.5.8
         * @return  void
         */

    }, {
        key: 'repeaterBeforeInit',
        value: function repeaterBeforeInit($repeater, options) {

            this.fieldCleanup();
        }

        /**
         * Sets the field to default.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setDefault',
        value: function setDefault() {

            if (this.options.default) {

                this.$field.find('[value="' + this.options.default + '"]').prop('checked', true).change();
            }
        }
    }]);

    return FieldRadio;
}(_field2.default);

/**
 * Finds and initializes all Radio fields.
 *
 * @since 1.4.0
 */


var FieldRadioInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldRadioInitialize($root) {
        _classCallCheck(this, FieldRadioInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-radio]');

        if ($fields.length) {

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldRadioInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldRadio($field)
            });
        }
    }]);

    return FieldRadioInitialize;
}();

exports.default = FieldRadioInitialize;

/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _field = __webpack_require__(0);

var _field2 = _interopRequireDefault(_field);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Toggle Field functionality.
 *
 * @since 1.4.0
 */
var FieldToggle = function (_Field) {
    _inherits(FieldToggle, _Field);

    /**
     * Class constructor.
     *
     * @since 1.4.0
     */
    function FieldToggle($field) {
        _classCallCheck(this, FieldToggle);

        var _this = _possibleConstructorReturn(this, (FieldToggle.__proto__ || Object.getPrototypeOf(FieldToggle)).call(this, $field, 'toggle'));

        _this.initField();
        return _this;
    }

    /**
     * Initializes the select.
     *
     * @since 1.4.0
     */


    _createClass(FieldToggle, [{
        key: 'initField',
        value: function initField() {
            var _this2 = this;

            this.getUI();

            // Initial change trigger to help other plugins
            setTimeout(function () {
                _this2.$field.trigger('change', [_this2.$ui.input.val()]);
            }, 1);

            this.setupHandlers();
        }

        /**
         * Retrieves the UI.
         *
         * @since 1.4.0
         */

    }, {
        key: 'getUI',
        value: function getUI() {

            this.$ui = {
                slider: this.$field.find('.fieldhelpers-field-toggle-slider'),
                input: this.$field.find('input[type="hidden"]')
            };
        }

        /**
         * Sets up class handlers.
         *
         * @since 1.4.0
         */

    }, {
        key: 'setupHandlers',
        value: function setupHandlers() {

            if (this.$field.data('rbmFHeventListenersCreated')) return;

            var api = this;

            this.$ui.slider.click(function () {
                api.handleClick();
            });

            this.$field.data('rbmFHeventListenersCreated', true);
        }

        /**
         * Return if field is checked or not.
         *
         * @since 1.4.0
         *
         * @returns {*}
         */

    }, {
        key: 'isChecked',
        value: function isChecked() {

            return this.$field.hasClass('checked');
        }

        /**
         * Fires on toggle change.
         *
         * @since 1.4.0
         */

    }, {
        key: 'handleClick',
        value: function handleClick() {

            if (this.isChecked()) {

                this.$ui.input.val(this.options.uncheckedValue);
                this.$field.removeClass('checked');
            } else {

                this.$ui.input.val(this.options.checkedValue);
                this.$field.addClass('checked');
            }

            this.$field.trigger('change', [this.$ui.input.val()]);
        }
    }]);

    return FieldToggle;
}(_field2.default);

/**
 * Finds and initializes all Toggle fields.
 *
 * @since 1.4.0
 */


var FieldToggleInitialize = function () {

    /**
     * Class constructor.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $root Root element to initialize fields inside.
     */
    function FieldToggleInitialize($root) {
        _classCallCheck(this, FieldToggleInitialize);

        var api = this;

        this.fields = [];

        var $fields = $root.find('[data-fieldhelpers-field-toggle]');

        if ($fields.length) {

            $fields.each(function () {

                api.initializeField(jQuery(this));
            });
        }
    }

    /**
     * Initializes the field.
     *
     * @since 1.4.0
     *
     * @param {jQuery} $field
     */


    _createClass(FieldToggleInitialize, [{
        key: 'initializeField',
        value: function initializeField($field) {

            this.fields.push({
                $field: $field,
                api: new FieldToggle($field)
            });
        }
    }]);

    return FieldToggleInitialize;
}();

exports.default = FieldToggleInitialize;

/***/ })
/******/ ]);