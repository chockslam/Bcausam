/*! Branda - v3.4.4
 * https://wpmudev.com/project/ultimate-branding/
 * Copyright (c) 2021; * Licensed GPLv2+ */
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Create the defaults once

  var pluginName = 'SUIAccordion',
      defaults = {}; // The actual plugin constructor

  function SUIAccordion(element, options) {
    this.element = element;
    this.$element = $(this.element);
    this.settings = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;
    this.init();
  } // Avoid Plugin.prototype conflicts


  $.extend(SUIAccordion.prototype, {
    init: function init() {
      var self = this;
      this.$element.on('click', 'div.sui-accordion-item-header, tr.sui-accordion-item', function (event) {
        var getItem = $(this).closest('.sui-accordion-item'),
            getContent = getItem.nextUntil('.sui-accordion-item').filter('.sui-accordion-item-content'),
            getParent = getItem.closest('.sui-accordion'),
            getChart = getItem.find('.sui-chartjs-animated');
        var clickedTarget = $(event.target);
        var flexHeader = $(this),
            flexItem = flexHeader.parent(),
            flexChart = flexItem.find('.sui-chartjs-animated'),
            flexParent = flexItem.parent();
        var tableItem = $(this),
            tableContent = tableItem.nextUntil('.sui-accordion-item').filter('.sui-accordion-item-content');

        if (clickedTarget.closest('.sui-accordion-item-action').length) {
          return true;
        } // CHECK: Flexbox


        if (flexHeader.hasClass('sui-accordion-item-header')) {
          if (flexItem.hasClass('sui-accordion-item--disabled')) {
            flexItem.removeClass('sui-accordion-item--open');
          } else {
            if (flexItem.hasClass('sui-accordion-item--open')) {
              flexItem.removeClass('sui-accordion-item--open');
            } else {
              flexItem.addClass('sui-accordion-item--open');
            }
          } // CHECK: Accordion Blocks


          if (flexParent.hasClass('sui-accordion-block') && 0 !== flexChart.length) {
            flexItem.find('.sui-accordion-item-data').addClass('sui-onload');
            flexChart.removeClass('sui-chartjs-loaded');

            if (flexItem.hasClass('sui-accordion-item--open')) {
              setTimeout(function () {
                flexItem.find('.sui-accordion-item-data').removeClass('sui-onload');
                flexChart.addClass('sui-chartjs-loaded');
              }, 1200);
            }
          }
        } // CHECK: Table


        if (tableItem.hasClass('sui-accordion-item')) {
          if (tableItem.hasClass('sui-accordion-item--disabled')) {
            tableContent.removeClass('sui-accordion-item--open');
          } else {
            if (tableItem.hasClass('sui-accordion-item--open')) {
              tableItem.removeClass('sui-accordion-item--open');
              tableContent.removeClass('sui-accordion-item--open');
            } else {
              tableItem.addClass('sui-accordion-item--open');
              tableContent.addClass('sui-accordion-item--open');
            }
          }
        }

        event.stopPropagation();
      });
    }
  }); // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations

  $.fn[pluginName] = function (options) {
    return this.each(function () {
      // instance of SUIAccordion can be called with $(element).data('SUIAccordion')
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new SUIAccordion(this, options));
      }
    });
  };
})(jQuery, window, document);

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.suiAccordion = function (el) {
    var accordionTable = $(el);

    function init() {
      accordionTable.SUIAccordion({});
    }

    init();
    return this;
  };

  if (0 !== $('.sui-2-9-6 .sui-accordion').length) {
    $('.sui-2-9-6 .sui-accordion').each(function () {
      SUI.suiAccordion(this);
    });
  }
})(jQuery);

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;

(function ($, ClipboardJS, window, document, undefined) {
  'use strict'; // undefined is used here as the undefined global variable in ECMAScript 3 is
  // mutable (ie. it can be changed by someone else). undefined isn't really being
  // passed in so we can ensure the value of it is truly undefined. In ES5, undefined
  // can no longer be modified.
  // window and document are passed through as local variables rather than global
  // as this (slightly) quickens the resolution process and can be more efficiently
  // minified (especially when both are regularly referenced in your plugin).
  // Create the defaults once

  var pluginName = 'SUICodeSnippet',
      defaults = {
    copyText: 'Copy',
    copiedText: 'Copied!'
  }; // The actual plugin constructor

  function SUICodeSnippet(element, options) {
    this.element = element;
    this.$element = $(this.element); // jQuery has an extend method which merges the contents of two or
    // more objects, storing the result in the first object. The first object
    // is generally empty as we don't want to alter the default options for
    // future instances of the plugin

    this.settings = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;
    this._clipboardJs = null;
    this._clipboardId = '';
    this.init();
  } // Avoid Plugin.prototype conflicts


  $.extend(SUICodeSnippet.prototype, {
    init: function init() {
      var self = this,
          button = ''; // check if its already wrapped

      if (0 === this.$element.parent('sui-code-snippet-wrapper').length) {
        // build markup
        this.$element.wrap('<div class="sui-code-snippet-wrapper"></div>');
        this._clipboardId = this.generateUniqueId();
        button = '<button class="sui-button" id="sui-code-snippet-button-' + this._clipboardId + '" data-clipboard-target="#sui-code-snippet-' + this._clipboardId + '">' + this.settings.copyText + '</button>';
        this.$element.attr('id', 'sui-code-snippet-' + this._clipboardId).after(button);
        this._clipboardJs = new ClipboardJS('#sui-code-snippet-button-' + this._clipboardId); // attach events

        this._clipboardJs.on('success', function (e) {
          e.clearSelection();
          self.showTooltip(e.trigger, self.settings.copiedText);
        });

        $('#sui-code-snippet-button-' + this._clipboardId).on('mouseleave.SUICodeSnippet', function () {
          $(this).removeClass('sui-tooltip');
          $(this).removeAttr('aria-label');
          $(this).removeAttr('data-tooltip');
        });
      }
    },
    getClipboardJs: function getClipboardJs() {
      return this._clipboardJs;
    },
    showTooltip: function showTooltip(e, msg) {
      $(e).addClass('sui-tooltip');
      $(e).attr('aria-label', msg);
      $(e).attr('data-tooltip', msg);
    },
    generateUniqueId: function generateUniqueId() {
      // Math.random should be unique because of its seeding algorithm.
      // Convert it to base 36 (numbers + letters), and grab the first 9 characters
      // after the decimal.
      return '_' + Math.random().toString(36).substr(2, 9);
    },
    destroy: function destroy() {
      if (null !== this._clipboardJs) {
        this._clipboardJs.destroy();

        this.$element.attr('id', '');
        this.$element.unwrap('.sui-code-snippet-wrapper');
        $('#sui-code-snippet-button-' + this._clipboardId).remove();
      }
    }
  }); // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations

  $.fn[pluginName] = function (options) {
    return this.each(function () {
      // instance of SUICodeSnippet can be called with $(element).data('SUICodeSnippet')
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new SUICodeSnippet(this, options));
      }
    });
  };
})(jQuery, ClipboardJS, window, document);

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.suiCodeSnippet = function () {
    // Convert all code snippet.
    $('.sui-2-9-6 .sui-code-snippet:not(.sui-no-copy)').each(function () {
      // backward compat of instantiate new accordion
      $(this).SUICodeSnippet({});
    });
  }; // wait document ready first


  $(document).ready(function () {
    SUI.suiCodeSnippet();
  });
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.sliderBack = function (el) {
    var slider = $(el),
        dialog = slider.closest('.sui-dialog'),
        slides = slider.find('.sui-slider-content > li');
    var navigation = slider.find('.sui-slider-navigation'),
        navButtons = navigation.find('button'),
        btnBack = navigation.find('.sui-prev'),
        btnNext = navigation.find('.sui-next');

    if (!dialog.hasClass('sui-dialog-onboard')) {
      return;
    }

    function init() {
      var currSlide = slider.find('.sui-slider-content > li.sui-current'),
          prevSlide = currSlide.prev();

      if (!prevSlide.length) {
        if (slider.hasClass('sui-infinite')) {
          prevSlide = slider.find('.sui-slider-content > li:last');
          currSlide.removeClass('sui-current');
          currSlide.removeClass('sui-loaded');
          prevSlide.addClass('sui-current');
          prevSlide.addClass('fadeInLeft');
          navButtons.prop('disabled', true);
          setTimeout(function () {
            prevSlide.addClass('sui-loaded');
            prevSlide.removeClass('fadeInLeft');
          }, 600);
          setTimeout(function () {
            navButtons.prop('disabled', false);
          }, 650);
        }
      } else {
        currSlide.removeClass('sui-current');
        currSlide.removeClass('sui-loaded');
        prevSlide.addClass('sui-current');
        prevSlide.addClass('fadeInLeft');
        navButtons.prop('disabled', true);

        if (!slider.hasClass('sui-infinite')) {
          btnNext.removeClass('sui-hidden');

          if (slides.first().data('slide') === prevSlide.data('slide')) {
            btnBack.addClass('sui-hidden');
          }
        }

        setTimeout(function () {
          prevSlide.addClass('sui-loaded');
          prevSlide.removeClass('fadeInLeft');
        }, 600);
        setTimeout(function () {
          navButtons.prop('disabled', false);
        }, 650);
      }
    }

    init();
    return this;
  };

  SUI.sliderNext = function (el) {
    var slider = $(el),
        dialog = slider.closest('.sui-dialog'),
        slides = slider.find('.sui-slider-content > li');
    var navigation = slider.find('.sui-slider-navigation'),
        navButtons = navigation.find('button'),
        btnBack = navigation.find('.sui-prev'),
        btnNext = navigation.find('.sui-next');

    if (!dialog.hasClass('sui-dialog-onboard')) {
      return;
    }

    function init() {
      var currSlide = slider.find('.sui-slider-content > li.sui-current'),
          nextSlide = currSlide.next();

      if (!nextSlide.length) {
        if (slider.hasClass('sui-infinite')) {
          nextSlide = slider.find('.sui-slider-content > li:first');
          currSlide.removeClass('sui-current');
          currSlide.removeClass('sui-loaded');
          nextSlide.addClass('sui-current');
          nextSlide.addClass('fadeInRight');
          navButtons.prop('disabled', true);
          setTimeout(function () {
            nextSlide.addClass('sui-loaded');
            nextSlide.removeClass('fadeInRight');
          }, 600);
          setTimeout(function () {
            navButtons.prop('disabled', false);
          }, 650);
        }
      } else {
        currSlide.removeClass('sui-current');
        currSlide.removeClass('sui-loaded');
        nextSlide.addClass('sui-current');
        nextSlide.addClass('fadeInRight');
        navButtons.prop('disabled', true);

        if (!slider.hasClass('sui-infinite')) {
          btnBack.removeClass('sui-hidden');

          if (slides.length === nextSlide.data('slide')) {
            btnNext.addClass('sui-hidden');
          }
        }

        setTimeout(function () {
          nextSlide.addClass('sui-loaded');
          nextSlide.removeClass('fadeInRight');
        }, 600);
        setTimeout(function () {
          navButtons.prop('disabled', false);
        }, 650);
      }
    }

    init();
    return this;
  };

  SUI.sliderStep = function (el) {
    var slider = $(el),
        dialog = slider.closest('.sui-dialog');
    var slides = slider.find('.sui-slider-content'),
        slide = slides.find('> li');
    var steps = slider.find('.sui-slider-steps'),
        step = steps.find('li'),
        button = step.find('button');
    var navigation = slider.find('.sui-slider-navigation'),
        navButtons = navigation.find('button'),
        navBack = navigation.find('.sui-prev'),
        navNext = navigation.find('.sui-next');

    if (!dialog.hasClass('sui-dialog-onboard') && !steps.hasClass('sui-clickable')) {
      return;
    }

    function reset() {
      // Remove current class
      slide.removeClass('sui-current'); // Remove loaded state

      slide.removeClass('sui-loaded');
    }

    function load(element) {
      var button = $(element),
          index = button.data('slide');
      var curSlide = button.closest('li[data-slide]'),
          newSlide = slides.find('> li[data-slide="' + index + '"]');
      newSlide.addClass('sui-current');

      if (curSlide.data('slide') < newSlide.data('slide')) {
        newSlide.addClass('fadeInRight');
      } else {
        newSlide.addClass('fadeInLeft');
      }

      navButtons.prop('disabled', true);

      if (!slider.hasClass('sui-infinite')) {
        if (1 === newSlide.data('slide')) {
          navBack.addClass('sui-hidden');
          navNext.removeClass('sui-hidden');
        }

        if (slide.length === newSlide.data('slide')) {
          navBack.removeClass('sui-hidden');
          navNext.addClass('sui-hidden');
        }
      }

      setTimeout(function () {
        newSlide.addClass('sui-loaded');

        if (curSlide.data('slide') < newSlide.data('slide')) {
          newSlide.removeClass('fadeInRight');
        } else {
          newSlide.removeClass('fadeInLeft');
        }
      }, 600);
      setTimeout(function () {
        navButtons.prop('disabled', false);
      }, 650);
    }

    function init() {
      if (button.length) {
        button.on('click', function (e) {
          reset();
          load(this);
          e.preventDefault();
          e.stopPropagation();
        });
      }
    }

    init();
    return this;
  };

  SUI.dialogSlider = function (el) {
    var slider = $(el),
        dialog = slider.closest('.sui-dialog'),
        btnBack = slider.find('.sui-slider-navigation .sui-prev'),
        btnNext = slider.find('.sui-slider-navigation .sui-next'),
        tourBack = slider.find('*[data-a11y-dialog-tour-back]'),
        tourNext = slider.find('*[data-a11y-dialog-tour-next]'),
        steps = slider.find('.sui-slider-steps');

    if (!dialog.hasClass('sui-dialog-onboard') || slider.hasClass('sui-slider-off')) {
      return;
    }

    function init() {
      if (btnBack.length) {
        btnBack.on('click', function (e) {
          SUI.sliderBack(slider);
          e.preventDefault();
        });
      }

      if (tourBack.length) {
        tourBack.on('click', function (e) {
          SUI.sliderBack(slider);
          e.preventDefault();
        });
      }

      if (btnNext.length) {
        btnNext.on('click', function (e) {
          SUI.sliderNext(slider);
          e.preventDefault();
        });
      }

      if (tourNext.length) {
        tourNext.on('click', function (e) {
          SUI.sliderNext(slider);
          e.preventDefault();
        });
      }

      if (steps.length) {
        SUI.sliderStep(slider);
      }
    }

    init();
    return this;
  };

  $('.sui-2-9-6 .sui-slider').each(function () {
    SUI.dialogSlider(this);
  });
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.linkDropdown = function () {
    function closeAllDropdowns($except) {
      var $dropdowns = $('.sui-2-9-6 .sui-dropdown');

      if ($except) {
        $dropdowns = $dropdowns.not($except);
      }

      $dropdowns.removeClass('open');
    }

    $('body').on('click', '.sui-dropdown-anchor', function (e) {
      var $button = $(this),
          $parent = $button.parent();
      closeAllDropdowns($parent);

      if ($parent.hasClass('sui-dropdown')) {
        $parent.toggleClass('open');
      }

      e.preventDefault();
    });
    $('body').mouseup(function (e) {
      var $anchor = $('.sui-2-9-6 .sui-dropdown-anchor');

      if (!$anchor.is(e.target) && 0 === $anchor.has(e.target).length) {
        closeAllDropdowns();
      }
    });
  };

  SUI.linkDropdown();
})(jQuery);
// This file is to be used for fixing up issues with IE11.
(function ($) {
  var colorpickers = $('.sui-colorpicker-wrap'); // If IE11 remove SUI colorpicker styles.

  if (!!navigator.userAgent.match(/Trident\/7\./) && colorpickers[0]) {
    colorpickers.find('.sui-colorpicker').hide();
    colorpickers.removeClass('sui-colorpicker-wrap');
  }
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function () {
  // Enable strict mode.
  'use strict';

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }
  /**
   * @namespace aria
   */


  var aria = aria || {}; // REF: Key codes.

  aria.KeyCode = {
    BACKSPACE: 8,
    TAB: 9,
    RETURN: 13,
    ESC: 27,
    SPACE: 32,
    PAGE_UP: 33,
    PAGE_DOWN: 34,
    END: 35,
    HOME: 36,
    LEFT: 37,
    UP: 38,
    RIGHT: 39,
    DOWN: 40,
    DELETE: 46
  };
  aria.Utils = aria.Utils || {}; // UTILS: Remove function.

  aria.Utils.remove = function (item) {
    if (item.remove && 'function' === typeof item.remove) {
      return item.remove();
    }

    if (item.parentNode && item.parentNode.removeChild && 'function' === typeof item.parentNode.removeChild) {
      return item.parentNode.removeChild(item);
    }

    return false;
  }; // UTILS: Verify if element can be focused.


  aria.Utils.isFocusable = function (element) {
    if (0 < element.tabIndex || 0 === element.tabIndex && null !== element.getAttribute('tabIndex')) {
      return true;
    }

    if (element.disabled) {
      return false;
    }

    switch (element.nodeName) {
      case 'A':
        return !!element.href && 'ignore' != element.rel;

      case 'INPUT':
        return 'hidden' != element.type && 'file' != element.type;

      case 'BUTTON':
      case 'SELECT':
      case 'TEXTAREA':
        return true;

      default:
        return false;
    }
  };
  /**
   * Simulate a click event.
   * @public
   * @param {Element} element the element to simulate a click on
   */


  aria.Utils.simulateClick = function (element) {
    // Create our event (with options)
    var evt = new MouseEvent('click', {
      bubbles: true,
      cancelable: true,
      view: window
    }); // If cancelled, don't dispatch our event

    var canceled = !element.dispatchEvent(evt);
  }; // When util functions move focus around, set this true so
  // the focus listener can ignore the events.


  aria.Utils.IgnoreUtilFocusChanges = false;
  aria.Utils.dialogOpenClass = 'sui-has-modal';
  /**
   * @desc Set focus on descendant nodes until the first
   * focusable element is found.
   *
   * @param element
   * DOM node for which to find the first focusable descendant.
   *
   * @returns
   * true if a focusable element is found and focus is set.
   */

  aria.Utils.focusFirstDescendant = function (element) {
    for (var i = 0; i < element.childNodes.length; i++) {
      var child = element.childNodes[i];

      if (aria.Utils.attemptFocus(child) || aria.Utils.focusFirstDescendant(child)) {
        return true;
      }
    }

    return false;
  }; // end focusFirstDescendant

  /**
   * @desc Find the last descendant node that is focusable.
   *
   * @param element
   * DOM node for which to find the last focusable descendant.
   *
   * @returns
   * true if a focusable element is found and focus is set.
   */


  aria.Utils.focusLastDescendant = function (element) {
    for (var i = element.childNodes.length - 1; 0 <= i; i--) {
      var child = element.childNodes[i];

      if (aria.Utils.attemptFocus(child) || aria.Utils.focusLastDescendant(child)) {
        return true;
      }
    }

    return false;
  }; // end focusLastDescendant

  /**
   * @desc Set Attempt to set focus on the current node.
   *
   * @param element
   * The node to attempt to focus on.
   *
   * @returns
   * true if element is focused.
   */


  aria.Utils.attemptFocus = function (element) {
    if (!aria.Utils.isFocusable(element)) {
      return false;
    }

    aria.Utils.IgnoreUtilFocusChanges = true;

    try {
      element.focus();
    } catch (e) {// Done.
    }

    aria.Utils.IgnoreUtilFocusChanges = false;
    return document.activeElement === element;
  }; // end attemptFocus
  // Modals can open modals. Keep track of them with this array.


  aria.OpenDialogList = aria.OpenDialogList || new Array(0);
  /**
   * @returns the last opened dialog (the current dialog)
   */

  aria.getCurrentDialog = function () {
    if (aria.OpenDialogList && aria.OpenDialogList.length) {
      return aria.OpenDialogList[aria.OpenDialogList.length - 1];
    }
  };

  aria.closeCurrentDialog = function () {
    var currentDialog = aria.getCurrentDialog();

    if (currentDialog) {
      currentDialog.close();
      return true;
    }

    return false;
  };

  aria.handleEscape = function (event) {
    var key = event.which || event.keyCode;

    if (key === aria.KeyCode.ESC && aria.closeCurrentDialog()) {
      event.stopPropagation();
    }
  };
  /**
   * @constructor
   * @desc Dialog object providing modal focus management.
   *
   * Assumptions: The element serving as the dialog container is present in the
   * DOM and hidden. The dialog container has role='dialog'.
   *
   * @param dialogId
   * The ID of the element serving as the dialog container.
   *
   * @param focusAfterClosed
   * Either the DOM node or the ID of the DOM node to focus when the
   * dialog closes.
   *
   * @param focusFirst
   * Optional parameter containing either the DOM node or the ID of the
   * DOM node to focus when the dialog opens. If not specified, the
   * first focusable element in the dialog will receive focus.
   *
   * @param hasOverlayMask
   * Optional boolean parameter that when is set to "true" will enable
   * a clickable overlay mask. This mask will fire close modal function
   * when you click on it.
   *
   * @param isCloseOnEsc
   * Default: true
   * Optional boolean parameter that when it's set to "true", it will enable closing the
   * dialog with the Esc key.
   */


  aria.Dialog = function (dialogId, focusAfterClosed, focusFirst, hasOverlayMask) {
    var isCloseOnEsc = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : true;
    this.dialogNode = document.getElementById(dialogId);

    if (null === this.dialogNode) {
      throw new Error('No element found with id="' + dialogId + '".');
    }

    var validRoles = ['dialog', 'alertdialog'];
    var isDialog = (this.dialogNode.getAttribute('role') || '').trim().split(/\s+/g).some(function (token) {
      return validRoles.some(function (role) {
        return token === role;
      });
    });

    if (!isDialog) {
      throw new Error('Dialog() requires a DOM element with ARIA role of dialog or alertdialog.');
    }

    this.isCloseOnEsc = isCloseOnEsc; // Trigger the 'open' event at the beginning of the opening process.
    // After validating the modal's attributes.

    var openEvent = new Event('open');
    this.dialogNode.dispatchEvent(openEvent); // Wrap in an individual backdrop element if one doesn't exist
    // Native <dialog> elements use the ::backdrop pseudo-element, which
    // works similarly.

    var backdropClass = 'sui-modal';

    if (this.dialogNode.parentNode.classList.contains(backdropClass)) {
      this.backdropNode = this.dialogNode.parentNode;
    } else {
      this.backdropNode = document.createElement('div');
      this.backdropNode.className = backdropClass;
      this.backdropNode.setAttribute('data-markup', 'new');
      this.dialogNode.parentNode.insertBefore(this.backdropNode, this.dialogNodev);
      this.backdropNode.appendChild(this.dialogNode);
    }

    this.backdropNode.classList.add('sui-active'); // Disable scroll on the body element

    document.body.parentNode.classList.add(aria.Utils.dialogOpenClass);

    if ('string' === typeof focusAfterClosed) {
      this.focusAfterClosed = document.getElementById(focusAfterClosed);
    } else if ('object' === _typeof(focusAfterClosed)) {
      this.focusAfterClosed = focusAfterClosed;
    } else {
      throw new Error('the focusAfterClosed parameter is required for the aria.Dialog constructor.');
    }

    if ('string' === typeof focusFirst) {
      this.focusFirst = document.getElementById(focusFirst);
    } else if ('object' === _typeof(focusFirst)) {
      this.focusFirst = focusFirst;
    } else {
      this.focusFirst = null;
    } // Bracket the dialog node with two invisible, focusable nodes.
    // While this dialog is open, we use these to make sure that focus never
    // leaves the document even if dialogNode is the first or last node.


    var preDiv = document.createElement('div');
    this.preNode = this.dialogNode.parentNode.insertBefore(preDiv, this.dialogNode);
    this.preNode.tabIndex = 0;

    if ('boolean' === typeof hasOverlayMask && true === hasOverlayMask) {
      this.preNode.classList.add('sui-modal-overlay');

      this.preNode.onclick = function () {
        aria.getCurrentDialog().close();
      };
    }

    var postDiv = document.createElement('div');
    this.postNode = this.dialogNode.parentNode.insertBefore(postDiv, this.dialogNode.nextSibling);
    this.postNode.tabIndex = 0; // If this modal is opening on top of one that is already open,
    // get rid of the document focus listener of the open dialog.

    if (0 < aria.OpenDialogList.length) {
      aria.getCurrentDialog().removeListeners();
    }

    this.addListeners();
    aria.OpenDialogList.push(this);
    this.dialogNode.classList.add('sui-content-fade-in'); // make visible

    this.dialogNode.classList.remove('sui-content-fade-out');

    if (this.focusFirst) {
      this.focusFirst.focus();
    } else {
      aria.Utils.focusFirstDescendant(this.dialogNode);
    }

    this.lastFocus = document.activeElement; // Trigger the 'afteropen' event at the end of the opening process.

    var afterOpenEvent = new Event('afterOpen');
    this.dialogNode.dispatchEvent(afterOpenEvent);
  }; // end Dialog constructor.

  /**
   * @desc Hides the current top dialog, removes listeners of the top dialog,
   * restore listeners of a parent dialog if one was open under the one that
   * just closed, and sets focus on the element specified for focusAfterClosed.
   */


  aria.Dialog.prototype.close = function () {
    var self = this; // Trigger the 'close' event at the beginning of the closing process.

    var closeEvent = new Event('close');
    this.dialogNode.dispatchEvent(closeEvent);
    aria.OpenDialogList.pop();
    this.removeListeners();
    this.preNode.parentNode.removeChild(this.preNode);
    this.postNode.parentNode.removeChild(this.postNode);
    this.dialogNode.classList.add('sui-content-fade-out');
    this.dialogNode.classList.remove('sui-content-fade-in');
    this.focusAfterClosed.focus();
    setTimeout(function () {
      self.backdropNode.classList.remove('sui-active');
    }, 300);
    setTimeout(function () {
      var slides = self.dialogNode.querySelectorAll('.sui-modal-slide');

      if (0 < slides.length) {
        // Hide all slides.
        for (var i = 0; i < slides.length; i++) {
          slides[i].setAttribute('disabled', true);
          slides[i].classList.remove('sui-loaded');
          slides[i].classList.remove('sui-active');
          slides[i].setAttribute('tabindex', '-1');
          slides[i].setAttribute('aria-hidden', true);
        } // Change modal size.


        if (slides[0].hasAttribute('data-modal-size')) {
          var newDialogSize = slides[0].getAttribute('data-modal-size');

          switch (newDialogSize) {
            case 'sm':
            case 'small':
              newDialogSize = 'sm';
              break;

            case 'md':
            case 'med':
            case 'medium':
              newDialogSize = 'md';
              break;

            case 'lg':
            case 'large':
              newDialogSize = 'lg';
              break;

            case 'xl':
            case 'extralarge':
            case 'extraLarge':
            case 'extra-large':
              newDialogSize = 'xl';
              break;

            default:
              newDialogSize = undefined;
          }

          if (undefined !== newDialogSize) {
            // Remove others sizes from dialog to prevent any conflicts with styles.
            self.dialogNode.parentNode.classList.remove('sui-modal-sm');
            self.dialogNode.parentNode.classList.remove('sui-modal-md');
            self.dialogNode.parentNode.classList.remove('sui-modal-lg');
            self.dialogNode.parentNode.classList.remove('sui-modal-xl'); // Apply the new size to dialog.

            self.dialogNode.parentNode.classList.add('sui-modal-' + newDialogSize);
          }
        } // Show first slide.


        slides[0].classList.add('sui-active');
        slides[0].classList.add('sui-loaded');
        slides[0].removeAttribute('disabled');
        slides[0].removeAttribute('tabindex');
        slides[0].removeAttribute('aria-hidden'); // Change modal label.

        if (slides[0].hasAttribute('data-modal-labelledby')) {
          var newDialogLabel, getDialogLabel;
          newDialogLabel = '';
          getDialogLabel = slides[0].getAttribute('data-modal-labelledby');

          if ('' !== getDialogLabel || undefined !== getDialogLabel) {
            newDialogLabel = getDialogLabel;
          }

          self.dialogNode.setAttribute('aria-labelledby', newDialogLabel);
        } // Change modal description.


        if (slides[0].hasAttribute('data-modal-describedby')) {
          var newDialogDesc, getDialogDesc;
          newDialogDesc = '';
          getDialogDesc = slides[0].getAttribute('data-modal-describedby');

          if ('' !== getDialogDesc || undefined !== getDialogDesc) {
            newDialogDesc = getDialogDesc;
          }

          self.dialogNode.setAttribute('aria-describedby', newDialogDesc);
        }
      }
    }, 350); // If a dialog was open underneath this one, restore its listeners.

    if (0 < aria.OpenDialogList.length) {
      aria.getCurrentDialog().addListeners();
    } else {
      document.body.parentNode.classList.remove(aria.Utils.dialogOpenClass);
    } // Trigger the 'afterclose' event at the end of the closing process.


    var afterCloseEvent = new Event('afterClose');
    this.dialogNode.dispatchEvent(afterCloseEvent);
  }; // end close.

  /**
   * @desc Hides the current dialog and replaces it with another.
   *
   * @param newDialogId
   * ID of the dialog that will replace the currently open top dialog.
   *
   * @param newFocusAfterClosed
   * Optional ID or DOM node specifying where to place focus when the new dialog closes.
   * If not specified, focus will be placed on the element specified by the dialog being replaced.
   *
   * @param newFocusFirst
   * Optional ID or DOM node specifying where to place focus in the new dialog when it opens.
   * If not specified, the first focusable element will receive focus.
   *
   * @param hasOverlayMask
   * Optional boolean parameter that when is set to "true" will enable a clickable overlay
   * mask to the new opened dialog. This mask will fire close dialog function when you click it.
   *
   * @param isCloseOnEsc
   * Default: true
   * Optional boolean parameter that when it's set to "true", it will enable closing the
   * dialog with the Esc key.
   */


  aria.Dialog.prototype.replace = function (newDialogId, newFocusAfterClosed, newFocusFirst, hasOverlayMask) {
    var isCloseOnEsc = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : true;
    var self = this;
    aria.OpenDialogList.pop();
    this.removeListeners();
    aria.Utils.remove(this.preNode);
    aria.Utils.remove(this.postNode);
    this.dialogNode.classList.remove('sui-content-fade-in');
    this.backdropNode.classList.remove('sui-active');
    setTimeout(function () {
      var slides = self.dialogNode.querySelectorAll('.sui-modal-slide');

      if (0 < slides.length) {
        // Hide all slides.
        for (var i = 0; i < slides.length; i++) {
          slides[i].setAttribute('disabled', true);
          slides[i].classList.remove('sui-loaded');
          slides[i].classList.remove('sui-active');
          slides[i].setAttribute('tabindex', '-1');
          slides[i].setAttribute('aria-hidden', true);
        } // Change modal size.


        if (slides[0].hasAttribute('data-modal-size')) {
          var newDialogSize = slides[0].getAttribute('data-modal-size');

          switch (newDialogSize) {
            case 'sm':
            case 'small':
              newDialogSize = 'sm';
              break;

            case 'md':
            case 'med':
            case 'medium':
              newDialogSize = 'md';
              break;

            case 'lg':
            case 'large':
              newDialogSize = 'lg';
              break;

            case 'xl':
            case 'extralarge':
            case 'extraLarge':
            case 'extra-large':
              newDialogSize = 'xl';
              break;

            default:
              newDialogSize = undefined;
          }

          if (undefined !== newDialogSize) {
            // Remove others sizes from dialog to prevent any conflicts with styles.
            self.dialogNode.parentNode.classList.remove('sui-modal-sm');
            self.dialogNode.parentNode.classList.remove('sui-modal-md');
            self.dialogNode.parentNode.classList.remove('sui-modal-lg');
            self.dialogNode.parentNode.classList.remove('sui-modal-xl'); // Apply the new size to dialog.

            self.dialogNode.parentNode.classList.add('sui-modal-' + newDialogSize);
          }
        } // Show first slide.


        slides[0].classList.add('sui-active');
        slides[0].classList.add('sui-loaded');
        slides[0].removeAttribute('disabled');
        slides[0].removeAttribute('tabindex');
        slides[0].removeAttribute('aria-hidden'); // Change modal label.

        if (slides[0].hasAttribute('data-modal-labelledby')) {
          var newDialogLabel, getDialogLabel;
          newDialogLabel = '';
          getDialogLabel = slides[0].getAttribute('data-modal-labelledby');

          if ('' !== getDialogLabel || undefined !== getDialogLabel) {
            newDialogLabel = getDialogLabel;
          }

          self.dialogNode.setAttribute('aria-labelledby', newDialogLabel);
        } // Change modal description.


        if (slides[0].hasAttribute('data-modal-describedby')) {
          var newDialogDesc, getDialogDesc;
          newDialogDesc = '';
          getDialogDesc = slides[0].getAttribute('data-modal-describedby');

          if ('' !== getDialogDesc || undefined !== getDialogDesc) {
            newDialogDesc = getDialogDesc;
          }

          self.dialogNode.setAttribute('aria-describedby', newDialogDesc);
        }
      }
    }, 350);
    var focusAfterClosed = newFocusAfterClosed || this.focusAfterClosed;
    var dialog = new aria.Dialog(newDialogId, focusAfterClosed, newFocusFirst, hasOverlayMask, isCloseOnEsc);
  }; // end replace

  /**
   * @desc Uses the same dialog to display different content that will slide to show.
   *
   * @param newSlideId
   * ID of the slide that will replace the currently active slide content.
   *
   * @param newSlideFocus
   * Optional ID or DOM node specifying where to place focus in the new slide when it shows.
   * If not specified, the first focusable element will receive focus.
   *
   * @param newSlideEntrance
   * Determine if the new slide will show up from "left" or "right" of the screen.
   * If not specified, the slide entrance animation will be "fade in".
   */


  aria.Dialog.prototype.slide = function (newSlideId, newSlideFocus, newSlideEntrance) {
    var animation = 'sui-fadein',
        currentDialog = aria.getCurrentDialog(),
        getAllSlides = this.dialogNode.querySelectorAll('.sui-modal-slide'),
        getNewSlide = document.getElementById(newSlideId);

    switch (newSlideEntrance) {
      case 'back':
      case 'left':
        animation = 'sui-fadein-left';
        break;

      case 'next':
      case 'right':
        animation = 'sui-fadein-right';
        break;

      default:
        animation = 'sui-fadein';
        break;
    } // Hide all slides.


    for (var i = 0; i < getAllSlides.length; i++) {
      getAllSlides[i].setAttribute('disabled', true);
      getAllSlides[i].classList.remove('sui-loaded');
      getAllSlides[i].classList.remove('sui-active');
      getAllSlides[i].setAttribute('tabindex', '-1');
      getAllSlides[i].setAttribute('aria-hidden', true);
    } // Change modal size.


    if (getNewSlide.hasAttribute('data-modal-size')) {
      var newDialogSize = getNewSlide.getAttribute('data-modal-size');

      switch (newDialogSize) {
        case 'sm':
        case 'small':
          newDialogSize = 'sm';
          break;

        case 'md':
        case 'med':
        case 'medium':
          newDialogSize = 'md';
          break;

        case 'lg':
        case 'large':
          newDialogSize = 'lg';
          break;

        case 'xl':
        case 'extralarge':
        case 'extraLarge':
        case 'extra-large':
          newDialogSize = 'xl';
          break;

        default:
          newDialogSize = undefined;
      }

      if (undefined !== newDialogSize) {
        // Remove others sizes from dialog to prevent any conflicts with styles.
        this.dialogNode.parentNode.classList.remove('sui-modal-sm');
        this.dialogNode.parentNode.classList.remove('sui-modal-md');
        this.dialogNode.parentNode.classList.remove('sui-modal-lg');
        this.dialogNode.parentNode.classList.remove('sui-modal-xl'); // Apply the new size to dialog.

        this.dialogNode.parentNode.classList.add('sui-modal-' + newDialogSize);
      }
    } // Change modal label.


    if (getNewSlide.hasAttribute('data-modal-labelledby')) {
      var newDialogLabel, getDialogLabel;
      newDialogLabel = '';
      getDialogLabel = getNewSlide.getAttribute('data-modal-labelledby');

      if ('' !== getDialogLabel || undefined !== getDialogLabel) {
        newDialogLabel = getDialogLabel;
      }

      this.dialogNode.setAttribute('aria-labelledby', newDialogLabel);
    } // Change modal description.


    if (getNewSlide.hasAttribute('data-modal-describedby')) {
      var newDialogDesc, getDialogDesc;
      newDialogDesc = '';
      getDialogDesc = getNewSlide.getAttribute('data-modal-describedby');

      if ('' !== getDialogDesc || undefined !== getDialogDesc) {
        newDialogDesc = getDialogDesc;
      }

      this.dialogNode.setAttribute('aria-describedby', newDialogDesc);
    } // Show new slide.


    getNewSlide.classList.add('sui-active');
    getNewSlide.classList.add(animation);
    getNewSlide.removeAttribute('tabindex');
    getNewSlide.removeAttribute('aria-hidden');
    setTimeout(function () {
      getNewSlide.classList.add('sui-loaded');
      getNewSlide.classList.remove(animation);
      getNewSlide.removeAttribute('disabled');
    }, 600);

    if ('string' === typeof newSlideFocus) {
      this.newSlideFocus = document.getElementById(newSlideFocus);
    } else if ('object' === _typeof(newSlideFocus)) {
      this.newSlideFocus = newSlideFocus;
    } else {
      this.newSlideFocus = null;
    }

    if (this.newSlideFocus) {
      this.newSlideFocus.focus();
    } else {
      aria.Utils.focusFirstDescendant(this.dialogNode);
    }
  }; // end slide.


  aria.Dialog.prototype.addListeners = function () {
    document.addEventListener('focus', this.trapFocus, true);

    if (this.isCloseOnEsc) {
      this.dialogNode.addEventListener('keyup', aria.handleEscape);
    }
  }; // end addListeners.


  aria.Dialog.prototype.removeListeners = function () {
    document.removeEventListener('focus', this.trapFocus, true);
  }; // end removeListeners.


  aria.Dialog.prototype.trapFocus = function (event) {
    if (aria.Utils.IgnoreUtilFocusChanges) {
      return;
    }

    var currentDialog = aria.getCurrentDialog();

    if (currentDialog.dialogNode.contains(event.target)) {
      currentDialog.lastFocus = event.target;
    } else {
      aria.Utils.focusFirstDescendant(currentDialog.dialogNode);

      if (currentDialog.lastFocus == document.activeElement) {
        aria.Utils.focusLastDescendant(currentDialog.dialogNode);
      }

      currentDialog.lastFocus = document.activeElement;
    }
  }; // end trapFocus.


  SUI.openModal = function (dialogId, focusAfterClosed, focusFirst, dialogOverlay) {
    var isCloseOnEsc = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : true;
    var dialog = new aria.Dialog(dialogId, focusAfterClosed, focusFirst, dialogOverlay, isCloseOnEsc);
  }; // end openModal.


  SUI.closeModal = function () {
    var topDialog = aria.getCurrentDialog();
    topDialog.close();
  }; // end closeDialog.


  SUI.replaceModal = function (newDialogId, newFocusAfterClosed, newFocusFirst, hasOverlayMask) {
    var isCloseOnEsc = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : true;
    var topDialog = aria.getCurrentDialog();
    /**
     * BUG #1:
     * When validating document.activeElement it always returns "false" but
     * even when "false" on Chrome function is fired correctly while on Firefox
     * and Safari this validation prevents function to be fired on click.
     *
     * if ( topDialog.dialogNode.contains( document.activeElement ) ) { ... }
     */

    topDialog.replace(newDialogId, newFocusAfterClosed, newFocusFirst, hasOverlayMask, isCloseOnEsc);
  }; // end replaceModal.


  SUI.slideModal = function (newSlideId, newSlideFocus, newSlideEntrance) {
    var topDialog = aria.getCurrentDialog();
    topDialog.slide(newSlideId, newSlideFocus, newSlideEntrance);
  }; // end slideModal.

})();

(function ($) {
  // Enable strict mode.
  'use strict';

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.modalDialog = function () {
    function init() {
      var button, buttonOpen, buttonClose, buttonReplace, buttonSlide, overlayMask, modalId, slideId, closeFocus, newFocus, animation;
      buttonOpen = $('[data-modal-open]');
      buttonClose = $('[data-modal-close]');
      buttonReplace = $('[data-modal-replace]');
      buttonSlide = $('[data-modal-slide]');
      overlayMask = $('.sui-modal-overlay');
      buttonOpen.on('click', function (e) {
        button = $(this);
        modalId = button.attr('data-modal-open');
        closeFocus = button.attr('data-modal-close-focus');
        newFocus = button.attr('data-modal-open-focus');
        overlayMask = button.attr('data-modal-mask');
        var isCloseOnEsc = 'false' === button.attr('data-esc-close') ? false : true;

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) === _typeof(closeFocus) || false === closeFocus || '' === closeFocus) {
          closeFocus = this;
        }

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) === _typeof(newFocus) || false === newFocus || '' === newFocus) {
          newFocus = undefined;
        }

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) !== _typeof(overlayMask) && false !== overlayMask && 'true' === overlayMask) {
          overlayMask = true;
        } else {
          overlayMask = false;
        }

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) !== _typeof(modalId) && false !== modalId && '' !== modalId) {
          SUI.openModal(modalId, closeFocus, newFocus, overlayMask, isCloseOnEsc);
        }

        e.preventDefault();
      });
      buttonReplace.on('click', function (e) {
        button = $(this);
        modalId = button.attr('data-modal-replace');
        closeFocus = button.attr('data-modal-close-focus');
        newFocus = button.attr('data-modal-open-focus');
        overlayMask = button.attr('data-modal-replace-mask');
        var isCloseOnEsc = 'false' === button.attr('data-esc-close') ? false : true;

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) === _typeof(closeFocus) || false === closeFocus || '' === closeFocus) {
          closeFocus = undefined;
        }

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) === _typeof(newFocus) || false === newFocus || '' === newFocus) {
          newFocus = undefined;
        }

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) !== _typeof(overlayMask) && false !== overlayMask && 'true' === overlayMask) {
          overlayMask = true;
        } else {
          overlayMask = false;
        }

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) !== _typeof(modalId) && false !== modalId && '' !== modalId) {
          SUI.replaceModal(modalId, closeFocus, newFocus, overlayMask, isCloseOnEsc);
        }

        e.preventDefault();
      });
      buttonSlide.on('click', function (e) {
        button = $(this);
        slideId = button.attr('data-modal-slide');
        newFocus = button.attr('data-modal-slide-focus');
        animation = button.attr('data-modal-slide-intro');

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) === _typeof(newFocus) || false === newFocus || '' === newFocus) {
          newFocus = undefined;
        }

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) === _typeof(animation) || false === animation || '' === animation) {
          animation = '';
        }

        if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) !== _typeof(slideId) && false !== slideId && '' !== slideId) {
          SUI.slideModal(slideId, newFocus, animation);
        }

        e.preventDefault();
      });
      buttonClose.on('click', function (e) {
        SUI.closeModal();
        e.preventDefault();
      });
    }

    init();
    return this;
  };

  SUI.modalDialog();
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.modalBack = function (el) {
    var slider = $(el),
        dialog = slider.closest('.sui-modal'),
        slides = slider.find('.sui-slider-content > li');
    var navigation = slider.find('.sui-slider-navigation'),
        navButtons = navigation.find('button'),
        btnBack = navigation.find('.sui-prev'),
        btnNext = navigation.find('.sui-next');

    if (!dialog.hasClass('sui-modal-onboard')) {
      return;
    }

    function init() {
      var currSlide = slider.find('.sui-slider-content > li.sui-current'),
          prevSlide = currSlide.prev();

      if (!prevSlide.length) {
        if (slider.hasClass('sui-infinite')) {
          prevSlide = slider.find('.sui-slider-content > li:last');
          currSlide.removeClass('sui-current');
          currSlide.removeClass('sui-loaded');
          prevSlide.addClass('sui-current');
          prevSlide.addClass('fadeInLeft');
          navButtons.prop('disabled', true);
          setTimeout(function () {
            prevSlide.addClass('sui-loaded');
            prevSlide.removeClass('fadeInLeft');
          }, 600);
          setTimeout(function () {
            navButtons.prop('disabled', false);
          }, 650);
        }
      } else {
        currSlide.removeClass('sui-current');
        currSlide.removeClass('sui-loaded');
        prevSlide.addClass('sui-current');
        prevSlide.addClass('fadeInLeft');
        navButtons.prop('disabled', true);

        if (!slider.hasClass('sui-infinite')) {
          btnNext.removeClass('sui-hidden');

          if (slides.first().data('slide') === prevSlide.data('slide')) {
            btnBack.addClass('sui-hidden');
          }
        }

        setTimeout(function () {
          prevSlide.addClass('sui-loaded');
          prevSlide.removeClass('fadeInLeft');
        }, 600);
        setTimeout(function () {
          navButtons.prop('disabled', false);
        }, 650);
      }
    }

    init();
    return this;
  };

  SUI.modalNext = function (el) {
    var slider = $(el),
        dialog = slider.closest('.sui-modal'),
        slides = slider.find('.sui-slider-content > li');
    var navigation = slider.find('.sui-slider-navigation'),
        navButtons = navigation.find('button'),
        btnBack = navigation.find('.sui-prev'),
        btnNext = navigation.find('.sui-next');

    if (!dialog.hasClass('sui-modal-onboard')) {
      return;
    }

    function init() {
      var currSlide = slider.find('.sui-slider-content > li.sui-current'),
          nextSlide = currSlide.next();

      if (!nextSlide.length) {
        if (slider.hasClass('sui-infinite')) {
          nextSlide = slider.find('.sui-slider-content > li:first');
          currSlide.removeClass('sui-current');
          currSlide.removeClass('sui-loaded');
          nextSlide.addClass('sui-current');
          nextSlide.addClass('fadeInRight');
          navButtons.prop('disabled', true);
          setTimeout(function () {
            nextSlide.addClass('sui-loaded');
            nextSlide.removeClass('fadeInRight');
          }, 600);
          setTimeout(function () {
            navButtons.prop('disabled', false);
          }, 650);
        }
      } else {
        currSlide.removeClass('sui-current');
        currSlide.removeClass('sui-loaded');
        nextSlide.addClass('sui-current');
        nextSlide.addClass('fadeInRight');
        navButtons.prop('disabled', true);

        if (!slider.hasClass('sui-infinite')) {
          btnBack.removeClass('sui-hidden');

          if (slides.length === nextSlide.data('slide')) {
            btnNext.addClass('sui-hidden');
          }
        }

        setTimeout(function () {
          nextSlide.addClass('sui-loaded');
          nextSlide.removeClass('fadeInRight');
        }, 600);
        setTimeout(function () {
          navButtons.prop('disabled', false);
        }, 650);
      }
    }

    init();
    return this;
  };

  SUI.modalStep = function (el) {
    var slider = $(el),
        dialog = slider.closest('.sui-modal');
    var slides = slider.find('.sui-slider-content'),
        slide = slides.find('> li');
    var steps = slider.find('.sui-slider-steps'),
        step = steps.find('li'),
        button = step.find('button');
    var navigation = slider.find('.sui-slider-navigation'),
        navButtons = navigation.find('button'),
        navBack = navigation.find('.sui-prev'),
        navNext = navigation.find('.sui-next');

    if (!dialog.hasClass('sui-modal-onboard') && !steps.hasClass('sui-clickable')) {
      return;
    }

    function reset() {
      // Remove current class
      slide.removeClass('sui-current'); // Remove loaded state

      slide.removeClass('sui-loaded');
    }

    function load(element) {
      var button = $(element),
          index = button.data('slide');
      var curSlide = button.closest('li[data-slide]'),
          newSlide = slides.find('> li[data-slide="' + index + '"]');
      newSlide.addClass('sui-current');

      if (curSlide.data('slide') < newSlide.data('slide')) {
        newSlide.addClass('fadeInRight');
      } else {
        newSlide.addClass('fadeInLeft');
      }

      navButtons.prop('disabled', true);

      if (!slider.hasClass('sui-infinite')) {
        if (1 === newSlide.data('slide')) {
          navBack.addClass('sui-hidden');
          navNext.removeClass('sui-hidden');
        }

        if (slide.length === newSlide.data('slide')) {
          navBack.removeClass('sui-hidden');
          navNext.addClass('sui-hidden');
        }
      }

      setTimeout(function () {
        newSlide.addClass('sui-loaded');

        if (curSlide.data('slide') < newSlide.data('slide')) {
          newSlide.removeClass('fadeInRight');
        } else {
          newSlide.removeClass('fadeInLeft');
        }
      }, 600);
      setTimeout(function () {
        navButtons.prop('disabled', false);
      }, 650);
    }

    function init() {
      if (button.length) {
        button.on('click', function (e) {
          reset();
          load(this);
          e.preventDefault();
          e.stopPropagation();
        });
      }
    }

    init();
    return this;
  };

  SUI.modalSlider = function (el) {
    var slider = $(el),
        dialog = slider.closest('.sui-modal'),
        btnBack = slider.find('.sui-slider-navigation .sui-prev'),
        btnNext = slider.find('.sui-slider-navigation .sui-next'),
        tourBack = slider.find('*[data-tour="back"]'),
        tourNext = slider.find('*[data-tour="next"]'),
        steps = slider.find('.sui-slider-steps');

    if (!dialog.hasClass('sui-modal-onboard') || slider.hasClass('sui-slider-off')) {
      return;
    }

    function init() {
      if (btnBack.length) {
        btnBack.on('click', function (e) {
          SUI.modalBack(slider);
          e.preventDefault();
        });
      }

      if (tourBack.length) {
        tourBack.on('click', function (e) {
          SUI.modalBack(slider);
          e.preventDefault();
        });
      }

      if (btnNext.length) {
        btnNext.on('click', function (e) {
          SUI.modalNext(slider);
          e.preventDefault();
        });
      }

      if (tourNext.length) {
        tourNext.on('click', function (e) {
          SUI.modalNext(slider);
          e.preventDefault();
        });
      }

      if (steps.length) {
        SUI.modalStep(slider);
      }
    }

    init();
    return this;
  };

  $('.sui-2-7-0 .sui-slider').each(function () {
    SUI.modalSlider(this);
  });
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  document.addEventListener('DOMContentLoaded', function () {
    var mainEl = $('.sui-wrap');

    if (undefined === SUI.dialogs) {
      SUI.dialogs = {};
    }

    $('.sui-2-7-0 .sui-dialog').each(function () {
      if (!SUI.dialogs.hasOwnProperty(this.id)) {
        SUI.dialogs[this.id] = new A11yDialog(this, mainEl);
      }
    });
  });
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exists.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.multistrings = function () {
    function buildWrapper(textarea, uniqid) {
      var parent = textarea.parent(),
          label = parent.find('> .sui-label'),
          description = parent.find('> .sui-description');
      /**
       * Build main wrapper for the whole multistring element.
       */

      parent.wrap('<div class="sui-multistrings-wrap"></div>');
      /**
       * Build ARIA-ready element.
       */
      // Hide field.

      parent.addClass('sui-multistrings-aria').removeClass('sui-form-field');
      /**
       * Build visual-ready element.
       */
      // Build a new field.

      $('<div class="sui-form-field sui-multistrings" tabindex="-1" aria-hidden="true" />').insertAfter(parent);
      var newParent = parent.next('.sui-multistrings');

      if (label.length) {
        newParent.append(label.clone());

        if ('' !== newParent.find('.sui-label').attr('for')) {
          newParent.find('.sui-label').attr('for', newParent.find('.sui-label').attr('for') + '-input-multistrings');
        }

        if ('' !== newParent.find('.sui-label').attr('id')) {
          newParent.find('.sui-label').attr('id', newParent.find('.sui-label').attr('id') + '-input-multistrings');
        }
      }

      newParent.append('<ul class="sui-multistrings-list" />');

      if (description.length) {
        newParent.append(description.clone());
        var $childDescription = newParent.find('.sui-description');

        if ('' !== $childDescription.attr('id')) {
          var newId = $childDescription.attr('id') + '-input-multistrings';
          $childDescription.attr('id', newId);
        }
      }
    }

    function bindFocus($mainWrapper) {
      var $listWrapper = $mainWrapper.find('.sui-multistrings');
      $listWrapper.on('click', function (e) {
        var $this = $(e.target);

        if ('sui-multistrings-list' !== $this.attr('class')) {
          return;
        }

        $listWrapper.find('.sui-multistrings-input input').focus();
      });
      var $input = $listWrapper.find('.sui-multistrings-input input'),
          $textarea = $mainWrapper.find('textarea'),
          $stringList = $mainWrapper.find('.sui-multistrings-list');

      var addSuiFocus = function addSuiFocus($element) {
        $element.on('focus', function () {
          $stringList.addClass('sui-focus');
          $element.off('blur').on('blur', function () {
            $stringList.removeClass('sui-focus');
          });
        });
      };

      addSuiFocus($input);
      addSuiFocus($textarea);
    }

    function buildInput(textarea, uniqid) {
      var html = '',
          placeholder = '',
          ariaLabel = '',
          ariaDescription = '';

      if ('undefined' !== typeof textarea.attr('placeholder') && '' !== textarea.attr('placeholder')) {
        placeholder = ' placeholder="' + textarea.attr('placeholder') + '"';
      }

      if ('undefinded' !== typeof textarea.attr('data-field-label') && '' !== textarea.attr('data-field-label')) {
        ariaLabel = ' aria-labelledby="' + uniqid + '-label"';
        textarea.attr('aria-labelledby', uniqid + '-label');
      } else {
        if (textarea.closest('.sui-form-field').find('.sui-label').length) {
          ariaLabel = ' aria-labelledby="' + uniqid + '-label"';
        }

        textarea.attr('aria-labelledby', uniqid + '-label');
      }

      if ('undefinded' !== typeof textarea.attr('data-field-label') && '' !== textarea.attr('data-field-label')) {
        ariaDescription = ' aria-describedby="' + uniqid + '-description"';
      } else {
        if (textarea.closest('.sui-form-field').find('.sui-label').length) {
          ariaDescription = ' aria-ariaDescription="' + uniqid + '-description"';
        }
      }

      html += '<li class="sui-multistrings-input">';
      html += '<input type="text" autocomplete="off"' + placeholder + ' id="' + uniqid + '"' + ariaLabel + ariaDescription + ' aria-autocomplete="none" />';
      html += '</li>';
      return html;
    }

    function buildItem(itemName) {
      var html = '';
      html += '<li title="' + itemName + '">';
      html += '<i class="sui-icon-page sui-sm" aria-hidden="true"></i>';
      html += itemName;
      html += '<button class="sui-button-close">';
      html += '<i class="sui-icon-close" aria-hidden="true"></i>';
      html += '<span class="sui-screen-reader-text">Delete</span>';
      html += '</button>';
      html += '</li>';
      return html;
    }

    function bindRemoveTag($mainWrapper) {
      var $removeButtons = $mainWrapper.find('.sui-multistrings-list .sui-button-close');
      $removeButtons.off('click').on('click', removeTag);
    }

    function insertStringOnLoad(textarea, uniqid, disallowedCharsArray) {
      var html = '',
          $mainWrapper = textarea.closest('.sui-multistrings-wrap'),
          $entriesList = $mainWrapper.find('.sui-multistrings-list'),
          forbiddenRemoved = cleanTextarea(textarea.val(), disallowedCharsArray, true); // Split lines for inserting the tags and cleaning the new textarea value.

      var splitStrings = forbiddenRemoved.split(/[\r\n]/gm),
          cleanStringsArray = []; // Insert the tags and add clean values to the cleanStringsArray.

      for (var i = 0; i < splitStrings.length; i++) {
        var stringLine = splitStrings[i].trim();

        if (0 === stringLine.length) {
          continue;
        }

        html += buildItem(stringLine);
        cleanStringsArray.push(stringLine);
      } // Clean-up textarea value with the cleanStringsArray joined by newlines.


      var newTextareaValue = cleanStringsArray.join('\n');
      textarea.val(newTextareaValue); // Build input to insert strings.

      html += buildInput(textarea, uniqid);
      $entriesList.append(html);
      bindRemoveTag($mainWrapper);
    }

    function getDisallowedChars($mainWrapper) {
      var $textarea = $mainWrapper.find('textarea.sui-multistrings'),
          disallowedCharsArray = [];
      var customDisallowedKeys = $textarea.data('disallowedKeys');

      if (customDisallowedKeys) {
        if ('number' === typeof customDisallowedKeys) {
          customDisallowedKeys = customDisallowedKeys.toString();
        } // Make an array from the user defined keys.


        var customKeysArray = customDisallowedKeys.split(',');
        var _iteratorNormalCompletion = true;
        var _didIteratorError = false;
        var _iteratorError = undefined;

        try {
          for (var _iterator = customKeysArray[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
            var key = _step.value;
            // Convert to integer.
            var intKey = parseInt(key, 10); // And filter out any NaN.

            if (!isNaN(intKey)) {
              // Convert ascii code to character.
              disallowedCharsArray.push(String.fromCharCode(intKey));
            }
          }
        } catch (err) {
          _didIteratorError = true;
          _iteratorError = err;
        } finally {
          try {
            if (!_iteratorNormalCompletion && _iterator["return"] != null) {
              _iterator["return"]();
            }
          } finally {
            if (_didIteratorError) {
              throw _iteratorError;
            }
          }
        }
      }

      return disallowedCharsArray;
    }

    function getRegexPatternForDisallowedChars(disallowedCharsArray) {
      // Regex for removing the disallowed keys from the inserted strings.
      var escapeRegExp = function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      },
          disallowedPattern = escapeRegExp(disallowedCharsArray.join(''));

      return disallowedPattern;
    }

    function handleInsertTags($mainWrapper, disallowedCharsArray) {
      var $tagInput = $mainWrapper.find('.sui-multistrings-input input'),
          $textarea = $mainWrapper.find('textarea.sui-multistrings'),
          disallowedString = getRegexPatternForDisallowedChars(disallowedCharsArray),
          regex = new RegExp("[\r\n".concat(disallowedString, "]"), 'gm'); // Sanitize the values on keydown.

      $tagInput.on('keydown', function (e) {
        // Do nothing if the key is from the disallowed ones.
        if (disallowedCharsArray.includes(e.key)) {
          e.preventDefault();
          return;
        }

        var input = $(this),
            oldValue = $textarea.val(),
            newValue = input.val(); // Get rid of new lines, commas, and any chars passed by the admin from the newly entered value.

        var newTrim = newValue.replace(regex, ''),
            isEnter = 13 === e.keyCode;

        if (isEnter) {
          e.preventDefault();
          e.stopPropagation();
        } // If there's no value to add, don't insert any new value.


        if (0 !== newTrim.length && 0 !== newTrim.trim().length) {
          if (isEnter) {
            var newTextareaValue = oldValue.length ? "".concat(oldValue, "\n").concat(newTrim) : newTrim; // Print new value on textarea.

            $textarea.val(newTextareaValue); // Print new value on the list.

            var html = buildItem(newTrim);
            $(html).insertBefore($mainWrapper.find('.sui-multistrings-input')); // Clear input value.

            input.val(''); // Bid the event to remove the tags.

            bindRemoveTag($mainWrapper);
          } else {
            input.val(newTrim);
          }
        } else {
          input.val('');
        }
      });
    }

    function handleTextareaChange($mainWrapper, disallowedCharsArray) {
      var textarea = $mainWrapper.find('textarea.sui-multistrings');
      var oldValue = textarea.val(),
          isTabTrapped = true; // Keep tab trapped when focusing on the textarea.

      textarea.on('focus', function () {
        return isTabTrapped = true;
      });
      textarea.on('keydown', function (e) {
        // Do nothing if the key is from the disallowed ones.
        if (disallowedCharsArray.includes(e.key)) {
          e.preventDefault();
          return;
        } // If it's tab...


        if (9 === e.keyCode) {
          // Add a new line if it's trapped.
          if (isTabTrapped) {
            e.preventDefault();
            var start = this.selectionStart,
                end = this.selectionEnd; // Insert a new line where the caret is.

            $(this).val($(this).val().substring(0, start) + '\n' + $(this).val().substring(end)); // Put caret at right position again.

            this.selectionStart = start + 1;
            this.selectionEnd = this.selectionStart;
          } // Release the tab.

        } else if (27 === e.keyCode) {
          isTabTrapped = false;
        }
      }).on('keyup change', function (e) {
        var currentValue = textarea.val(); // Nothing has changed, do nothing.

        if (currentValue === oldValue) {
          return;
        } // Clear up the content.


        var cleanedCurrentValue = cleanTextarea(currentValue, disallowedCharsArray); // Set the current value as the old one for future iterations.

        textarea.val(cleanedCurrentValue);
        oldValue = cleanedCurrentValue;
        var textboxValuesArray = cleanedCurrentValue.split(/[\r\n]+/gm),
            tags = $mainWrapper.find('.sui-multistrings-list li:not(.sui-multistrings-input)'),
            tagsTitles = [];
        var _iteratorNormalCompletion2 = true;
        var _didIteratorError2 = false;
        var _iteratorError2 = undefined;

        try {
          for (var _iterator2 = tags[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
            var tag = _step2.value;
            tagsTitles.push($(tag).attr('title'));
          }
        } catch (err) {
          _didIteratorError2 = true;
          _iteratorError2 = err;
        } finally {
          try {
            if (!_iteratorNormalCompletion2 && _iterator2["return"] != null) {
              _iterator2["return"]();
            }
          } finally {
            if (_didIteratorError2) {
              throw _iteratorError2;
            }
          }
        }

        var areEqual = compareArrays(textboxValuesArray, tagsTitles); // The existing elements changed, update the existing tags.

        if (!areEqual) {
          $mainWrapper.find('.sui-multistrings-list li:not(.sui-multistrings-input)').remove();
          var _iteratorNormalCompletion3 = true;
          var _didIteratorError3 = false;
          var _iteratorError3 = undefined;

          try {
            for (var _iterator3 = textboxValuesArray[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
              var value = _step3.value;
              value = value.trim();

              if (value.length) {
                // Print new value on the list.
                var html = buildItem(value);
                $(html).insertBefore($mainWrapper.find('.sui-multistrings-input'));
              }
            } // Bind the event to remove the tags.

          } catch (err) {
            _didIteratorError3 = true;
            _iteratorError3 = err;
          } finally {
            try {
              if (!_iteratorNormalCompletion3 && _iterator3["return"] != null) {
                _iterator3["return"]();
              }
            } finally {
              if (_didIteratorError3) {
                throw _iteratorError3;
              }
            }
          }

          bindRemoveTag($mainWrapper);
        }
      });
    }

    function compareArrays(firstArray, secondArray) {
      if (!Array.isArray(firstArray) || !Array.isArray(secondArray)) {
        return false;
      }

      if (firstArray.length !== secondArray.length) {
        return false;
      }

      return firstArray.every(function (value, index) {
        return value === secondArray[index];
      });
    }

    function cleanTextarea(string, disallowedCharsArray) {
      var isLoad = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
      var disallowedString = getRegexPatternForDisallowedChars(disallowedCharsArray),
          regex = new RegExp("[".concat(disallowedString, "]+|((\\r\\n|\\n|\\r)$)|^\\s*$"), 'gm');
      var clearedString = string.replace(regex, '');

      if (!isLoad) {
        // Avoid removing the last newline if it existed.
        var endsInNewline = string.match(/\n$/);

        if (endsInNewline) {
          clearedString += '\n';
        }
      }

      return clearedString;
    }

    function removeTag(e) {
      var $removeButton = $(e.currentTarget),
          $tag = $removeButton.closest('li');
      var $hiddenTextarea = $removeButton.closest('.sui-multistrings-wrap').find('textarea.sui-multistrings'),
          textareaValue = $hiddenTextarea.val(),
          removedTag = $tag.attr('title'),
          escapedRemovedTag = removedTag.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'),
          regex = new RegExp("^".concat(escapedRemovedTag, "\\s|^").concat(escapedRemovedTag, "$"), 'm'),
          newTextareaValue = textareaValue.replace(regex, ''); // Remove the string from the hidden textarea.

      $hiddenTextarea.val(newTextareaValue);
      $hiddenTextarea.trigger('change'); // Remove the tag the close button belongs to.

      $tag.remove();
    }

    function init() {
      var multistrings = $('.sui-multistrings');

      if (0 !== multistrings.length) {
        multistrings.each(function () {
          multistrings = $(this);
          var uniqueId = '';
          var hasUniqueId = 'undefined' !== typeof multistrings.attr('id') && '' !== multistrings.attr('id');
          var isTextarea = multistrings.is('textarea');
          var isWrapped = multistrings.parent().hasClass('sui-form-field');

          if (!hasUniqueId) {
            throw new Error('Multistrings field require an ID attribute.');
          } else {
            uniqueId = multistrings.attr('id') + '-strings';
          }

          if (!isTextarea) {
            throw new Error('Multistrings field with id="' + multistrings.attr('id') + '" needs to be "textarea".');
          }

          if (!isWrapped) {
            throw new Error('Multistrings field needs to be wrapped inside "sui-form-field" div.');
          }

          buildWrapper(multistrings, uniqueId);
          var $mainWrapper = multistrings.closest('.sui-multistrings-wrap'),
              disallowedCharsArray = getDisallowedChars($mainWrapper);
          insertStringOnLoad(multistrings, uniqueId, disallowedCharsArray);
          handleInsertTags($mainWrapper, disallowedCharsArray);
          handleTextareaChange($mainWrapper, disallowedCharsArray);
          bindFocus($mainWrapper);
        });
      }
    }

    init();
    return this;
  };

  SUI.multistrings();
})(jQuery);
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it does not exist.

  var _this = this;

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }
  /**
   * @desc Notifications function to show when alert.
   *
   * Assumptions: The element serving as the alert container is present in the
   * DOM and hidden. The alert container has role='alert'.
   *
   * @param noticeId
   * The ID of the element serving as the alert container.
   *
   * @param noticeMessage
   * The content to be printed when the alert shows up. It accepts HTML.
   *
   * @param noticeOptions
   * An object with different paramethers to modify the alert appearance.
   */


  SUI.openNotice = function (noticeId, noticeMessage, noticeOptions) {
    // Get notification node by ID.
    var noticeNode = $('#' + noticeId);
    var nodeWrapper = noticeNode.parent(); // Check if element ID exists.

    if (null === typeof noticeNode || 'undefined' === typeof noticeNode) {
      throw new Error('No element found with id="' + noticeId + '".');
    } // Check if element has correct attribute.


    if ('alert' !== noticeNode.attr('role')) {
      throw new Error('Notice requires a DOM element with ARIA role of alert.');
    } // Check if notice message is empty.


    if (null === typeof noticeMessage || 'undefined' === typeof noticeMessage || '' === noticeMessage) {
      throw new Error('Notice requires a message to print.');
    }

    var utils = utils || {};
    /**
     * @desc Allowed types for notification.
     */

    utils.allowedNotices = ['info', 'blue', 'green', 'success', 'yellow', 'warning', 'red', 'error', 'purple', 'upsell'];
    /**
     * @desc Verify if property is an array.
     */

    utils.isObject = function (obj) {
      if ((null !== obj || 'undefined' !== obj) && $.isPlainObject(obj)) {
        return true;
      }

      return false;
    };
    /**
     * @desc Deep merge two objects.
     * Watch out for infinite recursion on circular references.
     */


    utils.deepMerge = function (target) {
      for (var _len = arguments.length, sources = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        sources[_key - 1] = arguments[_key];
      }

      if (!sources.length) {
        return target;
      }

      var source = sources.shift();

      if (utils.isObject(target) && utils.isObject(source)) {
        for (var key in source) {
          if (utils.isObject(source[key])) {
            if (!target[key]) {
              Object.assign(target, _defineProperty({}, key, {}));
            }

            utils.deepMerge(target[key], source[key]);
          } else {
            Object.assign(target, _defineProperty({}, key, source[key]));
          }
        }
      }

      return utils.deepMerge.apply(utils, [target].concat(sources));
    };
    /**
     * @desc Declare default styling options for notifications.
     */


    utils.setProperties = function () {
      var incomingOptions = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      utils.options = [];
      var defaults = {
        type: 'default',
        icon: 'info',
        dismiss: {
          show: false,
          label: 'Close this notice',
          tooltip: ''
        },
        autoclose: {
          show: true,
          timeout: 3000
        }
      };
      utils.options[0] = utils.deepMerge(defaults, incomingOptions);
    };

    utils.setProperties(noticeOptions);
    /**
     * @desc Build notice dismiss.
     */

    utils.buildDismiss = function () {
      var html = '';
      var dismiss = utils.options[0].dismiss;

      if (true === dismiss.show) {
        html = document.createElement('div');
        html.className = 'sui-notice-actions';
        var innerHTML = '';

        if ('' !== dismiss.tooltip) {
          if (nodeWrapper.hasClass('sui-floating-notices')) {
            innerHTML += '<div class="sui-tooltip sui-tooltip-bottom" data-tooltip="' + dismiss.tooltip + '">';
          } else {
            innerHTML += '<div class="sui-tooltip" data-tooltip="' + dismiss.tooltip + '">';
          }
        }

        innerHTML += '<button class="sui-button-icon">';
        innerHTML += '<i class="sui-icon-check" aria-hidden="true"></i>';

        if ('' !== dismiss.label) {
          innerHTML += '<span class="sui-screen-reader-text">' + dismiss.label + '</span>';
        }

        innerHTML += '</button>';

        if ('' !== dismiss.tooltip) {
          innerHTML += '</div>';
        }

        html.innerHTML = innerHTML;
      }

      return html;
    };
    /**
     * @desc Build notice icon.
     */


    utils.buildIcon = function () {
      var html = '';
      var icon = utils.options[0].icon;

      if ('' !== icon || 'undefined' !== typeof icon || null !== typeof icon) {
        html = document.createElement('span');
        html.className += 'sui-notice-icon sui-icon-' + icon + ' sui-md';
        html.setAttribute('aria-hidden', true);

        if ('loader' === icon) {
          html.classList.add('sui-loading');
        }
      }

      return html;
    };
    /**
     * @desc Build notice message.
     */


    utils.buildMessage = function () {
      var html = document.createElement('div');
      html.className = 'sui-notice-message';
      html.innerHTML = noticeMessage;
      html.prepend(utils.buildIcon());
      return html;
    };
    /**
     * @desc Build notice markup.
     */


    utils.buildNotice = function () {
      var html = document.createElement('div');
      html.className = 'sui-notice-content';
      html.append(utils.buildMessage(), utils.buildDismiss());
      return html;
    };
    /**
     * @desc Show notification message.
     */


    utils.showNotice = function (animation) {
      var timeout = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 300;
      var type = utils.options[0].type;
      var dismiss = utils.options[0].dismiss;
      var autoclose = utils.options[0].autoclose; // Add active class.

      noticeNode.addClass('sui-active'); // Check for allowed notification types.

      $.each(utils.allowedNotices, function (key, value) {
        if (value === type) {
          noticeNode.addClass('sui-notice-' + value);
        }
      }); // Remove tabindex.

      noticeNode.removeAttr('tabindex'); // Print notification message.

      noticeNode.html(utils.buildNotice()); // Show animation.

      if ('slide' === animation) {
        noticeNode.slideDown(timeout, function () {
          // Check if dismiss button enabled.
          if (true === dismiss.show) {
            // Focus dismiss button.
            noticeNode.find('.sui-notice-actions button').focus(); // Dismiss button.

            noticeNode.find('.sui-notice-actions button').on('click', function () {
              SUI.closeNotice(noticeId);
            });
          } else {
            // Check if notice auto-closes.
            if (true === autoclose.show) {
              setTimeout(function () {
                return SUI.closeNotice(noticeId);
              }, parseInt(autoclose.timeout));
            }
          }
        });
      } else if ('fade' === animation) {
        noticeNode.fadeIn(timeout, function () {
          // Check if dismiss button enabled.
          if (true === dismiss.show) {
            // Focus dismiss button.
            noticeNode.find('.sui-notice-actions button').focus(); // Dismiss button.

            noticeNode.find('.sui-notice-actions button').on('click', function () {
              SUI.closeNotice(noticeId);
            });
          } else {
            // Check if notice auto-closes.
            if (true === autoclose.show) {
              setTimeout(function () {
                return SUI.closeNotice(noticeId);
              }, parseInt(autoclose.timeout));
            }
          }
        });
      } else {
        noticeNode.show(timeout, function () {
          // Check if dismiss button enabled.
          if (true === dismiss.show) {
            // Focus dismiss button.
            noticeNode.find('.sui-notice-actions button').focus(); // Dismiss button.

            noticeNode.find('.sui-notice-actions button').on('click', function () {
              SUI.closeNotice(noticeId);
            });
          } else {
            // Check if notice auto-closes.
            if (true === autoclose.show) {
              setTimeout(function () {
                return SUI.closeNotice(noticeId);
              }, parseInt(autoclose.timeout));
            }
          }
        });
      }
    };
    /**
     * @desc Open notification message.
     */


    utils.openNotice = function (animation) {
      var timeout = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 300;

      if (noticeNode.hasClass('sui-active')) {
        if ('slide' === animation) {
          noticeNode.slideUp(timeout, function () {
            utils.showNotice('slide', timeout);
          });
        } else if ('fade' === animation) {
          noticeNode.fadeOut(timeout, function () {
            utils.showNotice('fade', timeout);
          });
        } else {
          noticeNode.hide(timeout, function () {
            utils.showNotice(null, timeout);
          });
        }
      } else {
        // Show notice.
        utils.showNotice(animation, timeout);
      }
    };
    /**
     * @desc Initialize function.
     */


    var init = function init() {
      /**
       * When notice should float, it needs to be wrapped inside:
       * <div class="sui-floating-notices"></div>
       *
       * IMPORTANT: This wrapper goes before "sui-wrap" closing tag
       * and after modals markup.
       */
      if (nodeWrapper.hasClass('sui-floating-notices')) {
        utils.openNotice('slide');
      } else {
        utils.openNotice('fade');
      }
    };

    init();
    return _this;
  };
  /**
   * @desc Close and clear the alert.
   *
   * Assumptions: The element that will trigger this function is part of alert content.
   *
   * @param noticeId
   * The ID of the element serving as the alert container.
   *
   */


  SUI.closeNotice = function (noticeId) {
    // Get notification node by ID.
    var noticeNode = $('#' + noticeId);
    var nodeWrapper = noticeNode.parent(); // Check if element ID exists.

    if (null === typeof noticeNode || 'undefined' === typeof noticeNode) {
      throw new Error('No element found with id="' + noticeId + '".');
    }

    var utils = utils || {};
    /**
     * @desc Allowed types for notification.
     */

    utils.allowedNotices = ['info', 'blue', 'green', 'success', 'yellow', 'warning', 'red', 'error', 'purple', 'upsell'];
    /**
     * @desc Destroy notification.
     */

    utils.hideNotice = function () {
      // Remove active class.
      noticeNode.removeClass('sui-active'); // Remove styling classes.

      $.each(utils.allowedNotices, function (key, value) {
        noticeNode.removeClass('sui-notice-' + value);
      }); // Prevent TAB key from accessing the element.

      noticeNode.attr('tabindex', '-1'); // Remove all content from notification.

      noticeNode.empty();
    };
    /**
     * @desc Close notification message.
     */


    utils.closeNotice = function (animation) {
      var timeout = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 300;

      // Close animation.
      if ('slide' === animation) {
        noticeNode.slideUp(timeout, function () {
          return utils.hideNotice();
        });
      } else if ('fade' === animation) {
        noticeNode.fadeOut(timeout, function () {
          return utils.hideNotice();
        });
      } else {
        noticeNode.hide(timeout, function () {
          return utils.hideNotice();
        });
      }
    };
    /**
     * @desc Initialize function.
     */


    var init = function init() {
      /**
       * When notice should float, it needs to be wrapped inside:
       * <div class="sui-floating-notices"></div>
       *
       * IMPORTANT: This wrapper goes before "sui-wrap" closing tag
       * and after modals markup.
       */
      if (nodeWrapper.hasClass('sui-floating-notices')) {
        utils.closeNotice('slide');
      } else {
        utils.closeNotice('fade');
      }
    };

    init();
    return _this;
  };
  /**
   * @desc Trigger open and close alert notification functions through element attributes.
   *
   * Assumptions: Elements in charge of triggering the actions will be a button or a non-hyperlink element.
   *
   */


  SUI.notice = function () {
    var notice = notice || {};
    notice.Utils = notice.Utils || {};
    /**
     * @desc Click an element to open a notification.
     */

    notice.Utils.Open = function (element) {
      element.on('click', function () {
        self = $(this); // Define main variables for open function.

        var noticeId = self.attr('data-notice-open');
        var noticeMessage = '';
        var noticeOptions = {}; // Define index to use on for loops.

        var i; // Define maximum number of paragraphs.

        var numbLines = 4; // Check if `data-notice-message` exists.

        if (self.is('[data-notice-message]') && '' !== self.attr('data-notice-message')) {
          noticeMessage += self.attr('data-notice-message'); // If `data-notice-message` doesn't exists, look for `data-notice-paragraph-[i]` attributes.
        } else {
          for (i = 0; i < numbLines; i++) {
            var index = i + 1;
            var paragraph = 'data-notice-paragraph-' + index;

            if (self.is('[' + paragraph + ']') && '' !== self.attr(paragraph)) {
              noticeMessage += '<p>' + self.attr(paragraph) + '</p>';
            }
          }
        } // Check if `data-notice-type` exists.


        if (self.is('[data-notice-type]') && '' !== self.attr('data-notice-dismiss-type')) {
          noticeOptions.type = self.attr('data-notice-type');
        } // Check if `data-notice-icon` exists.


        if (self.is('[data-notice-icon]')) {
          noticeOptions.icon = self.attr('data-notice-icon');
        } // Check if `data-notice-dismiss` exists.


        if (self.is('[data-notice-dismiss]')) {
          noticeOptions.dismiss = {};

          if ('true' === self.attr('data-notice-dismiss')) {
            noticeOptions.dismiss.show = true;
          } else if ('false' === self.attr('data-notice-dismiss')) {
            noticeOptions.dismiss.show = false;
          }
        } // Check if `data-notice-dismiss-label` exists.


        if (self.is('[data-notice-dismiss-label]') && '' !== self.attr('data-notice-dismiss-label')) {
          noticeOptions.dismiss.label = self.attr('data-notice-dismiss-label');
        } // Check if `data-notice-dismiss-label` exists.


        if (self.is('[data-notice-dismiss-tooltip]') && '' !== self.attr('data-notice-dismiss-tooltip')) {
          noticeOptions.dismiss.tooltip = self.attr('data-notice-dismiss-tooltip');
        } // Check if `data-notice-autoclose` exists.


        if (self.is('[data-notice-autoclose]')) {
          noticeOptions.autoclose = {};

          if ('true' === self.attr('data-notice-autoclose')) {
            noticeOptions.autoclose.show = true;
          } else if ('false' === self.attr('data-notice-autoclose')) {
            noticeOptions.autoclose.show = false;
          }
        } // Check if `data-notice-autoclose-timeout` exists.


        if (self.is('[data-notice-autoclose-timeout]')) {
          noticeOptions.autoclose = noticeOptions.autoclose || {};
          noticeOptions.autoclose.timeout = parseInt(self.attr('data-notice-autoclose-timeout'));
        }

        SUI.openNotice(noticeId, noticeMessage, noticeOptions);
      });
    };
    /**
     * @desc Close a notification.
     */


    notice.Utils.Close = function (element) {
      element.on('click', function () {
        self = $(this);
        var noticeId;

        if (self.is('[data-notice-close]')) {
          noticeId = self.closest('.sui-notice').attr('id');

          if ('' !== self.attr('[data-notice-close]')) {
            noticeId = self.attr('data-notice-close');
          }

          SUI.closeNotice(noticeId);
        }
      });
    };

    var init = function init() {
      // Open a notification.
      var btnOpen = $('[data-notice-open]');
      notice.Utils.Open(btnOpen); // Close a notification.

      var btnClose = $('[data-notice-close]');
      notice.Utils.Close(btnClose);
    };

    init();
    return _this;
  };

  SUI.notice();
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.showHidePassword = function () {
    $('.sui-2-9-6 .sui-form-field').each(function () {
      var $this = $(this);

      if (0 !== $this.find('input[type="password"]').length) {
        $this.find('[class*="sui-button"], .sui-password-toggle').off('click.toggle-password').on('click.toggle-password', function () {
          var $button = $(this),
              $input = $button.parent().find('input'),
              $icon = $button.find('i');
          $button.parent().toggleClass('sui-password-visible');
          $button.find('.sui-password-text').toggleClass('sui-hidden');

          if ($button.parent().hasClass('sui-password-visible')) {
            $input.prop('type', 'text');
            $icon.removeClass('sui-icon-eye').addClass('sui-icon-eye-hide');
          } else {
            $input.prop('type', 'password');
            $icon.removeClass('sui-icon-eye-hide').addClass('sui-icon-eye');
          }
        });
      }
    });
  };

  SUI.showHidePassword();
})(jQuery);
(function ($) {
  var endpoint = 'https://api.reviews.co.uk/merchant/reviews?store=wpmudev-org'; // Update the reviews with the live stats.

  $('.sui-2-9-6 .sui-reviews').each(function () {
    var review = $(this);
    $.get(endpoint, function (data) {
      var stars = Math.round(data.stats.average_rating);
      var starsBlock = review.find('.sui-reviews__stars')[0];
      var i;

      for (i = 0; i < stars; i++) {
        starsBlock.innerHTML += '<i class="sui-icon-star" aria-hidden="true"></i> ';
      }

      review.find('.sui-reviews-rating')[0].innerHTML = data.stats.average_rating;
      review.find('.sui-reviews-customer-count')[0].innerHTML = data.stats.total_reviews;
    });
  });
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.loadCircleScore = function (el) {
    var dial = $(el).find('svg circle:last-child'),
        score = $(el).data('score'),
        radius = 42,
        circumference = 2 * Math.PI * radius,
        dashLength = circumference / 100 * score,
        gapLength = dashLength * 100 - score,
        svg = '<svg viewbox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">\n' + '<circle stroke-width="16" cx="50" cy="50" r="42" />\n' + '<circle stroke-width="16" cx="50" cy="50" r="42" stroke-dasharray="0,' + gapLength + '" />\n' + '</svg>\n' + '<span class="sui-circle-score-label">' + score + '</span>\n'; // Add svg to score element, add loaded class, & change stroke-dasharray to represent target score/percentage.

    $(el).prepend(svg).addClass('loaded').find('circle:last-child').css('animation', 'sui' + score + ' 3s forwards');
  };

  $('.sui-2-9-6 .sui-circle-score').each(function () {
    SUI.loadCircleScore(this);
  });
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.suiSelect = function (el) {
    var jq = $(el),
        wrap,
        handle,
        list,
        value,
        items;

    if (!jq.is('select')) {
      return;
    }

    if (jq.closest('.select-container').length || jq.data('select2') || jq.is('.sui-select') || jq.is('.sui-search') || jq.is('.sui-variables') || jq.is('.none-sui')) {
      return;
    } // Add the DOM elements to style the select list.


    function setupElement() {
      // Wrap select
      jq.wrap('<div class="select-container">'); // Hide select

      jq.attr('aria-hidden', true);
      jq.attr('hidden', true);
      jq.hide();
      wrap = jq.parent();
      handle = $('<span class="dropdown-handle" aria-hidden="true"><i class="sui-icon-chevron-down"></i></span>').prependTo(wrap);
      list = $('<div class="select-list-container"></div>').appendTo(wrap);
      value = $('<button type="button" class="list-value" aria-haspopup="listbox">&nbsp;</button>').appendTo(list);
      items = $('<ul tabindex="-1" role="listbox" class="list-results"></ul>').appendTo(list);
      wrap.addClass(jq.attr('class'));
      value.attr('id', jq.attr('id') + '-button');
      value.attr('aria-labelledby', jq.attr('aria-labelledby') + ' ' + value.attr('id'));
      items.attr('id', jq.attr('id') + '-list');
      items.attr('aria-labelledby', jq.attr('aria-labelledby'));
    } // When changing selection using JS, you need to trigger a 'sui:change' event
    // eg: $('select').val('4').trigger('sui:change')


    function handleSelectionChange() {
      jq.on('sui:change', function () {
        // We need to re-populateList to handle dynamic select options added via JS/ajax.
        populateList();
        items.find('li').not('.optgroup-label').on('click', function onItemClick(ev) {
          var opt = $(ev.target);
          selectItem(opt, false, opt.data('color'));
          handleValue();
        });
      });
    } // Add all the options to the new DOM elements.


    function populateList() {
      var children = jq.children();
      items.empty();
      children.each(function () {
        var opt = $(this),
            item,
            optgroup = $(this),
            optGroupItem,
            $label;

        if ('OPTION' == $(this).prop('tagName')) {
          item = $('<li></li>').appendTo(items);
          item.attr('role', 'option');

          if (opt.data('content')) {
            item.addClass('sui-element-flex');
            item.html('<span>' + opt.text() + '</span><span>' + opt.data('content') + '</span>');
          } else if (opt.data('icon')) {
            item.html('<i class="sui-icon-' + opt.data('icon') + '" aria-hidden="true"></i> ' + opt.text());
          } else if (opt.data('color')) {
            item.html('<span style="background-color: ' + opt.data('color') + '" data-color="' + opt.data('color') + '" aria-hidden="true"></span>' + opt.text());
          } else {
            item.text(opt.text());
          }

          if (opt.is(':disabled')) {
            item.addClass('sui-disabled');
          }

          items.attr('aria-activedescendant', jq.attr('id') + '-option-' + opt.val());
          item.attr('id', jq.attr('id') + '-option-' + opt.val());
          item.data('value', opt.val());
          item.data('color', opt.data('color'));

          if (opt.val() == jq.val()) {
            selectItem(item, true, opt.data('color'));
          }
        } else {
          optGroupItem = $('<ul></ul>').appendTo(items);
          $label = $('<li class="optgroup-label"></li>').text(optgroup.prop('label'));
          optGroupItem.html($label);
          optGroupItem.addClass('optgroup');
          optgroup.find('option').each(function onPopulateLoop() {
            var opt = $(this),
                item;
            item = $('<li></li>').appendTo(optGroupItem);
            item.text(opt.text());
            item.data('value', opt.val());

            if (opt.val() == jq.val()) {
              selectItem(item);
            }
          });
        }
      });
    } // Checks the option value for a link.


    function handleValue() {
      var val = jq[0].value; // If option is link, navigate to it.

      if (val.match('^https?:\/\/|#')) {
        window.location.href = val;
      }
    } // Toggle the dropdown state between open/closed.


    function stateToggle() {
      if (wrap.find('select').is(':disabled')) {
        return;
      }

      if (!wrap.hasClass('active')) {
        stateOpen();
      } else {
        stateClose();
      }
    } // Close the dropdown list.


    function stateClose(item) {
      if (!item) {
        item = wrap;
      }

      item.removeClass('active');
      item.closest('tr').removeClass('select-open');
      item.find('.list-value').removeAttr('aria-expanded');
    } // Open the dropdown list.


    function stateOpen() {
      $('.select-container.active').each(function () {
        stateClose($(this));
      });
      wrap.addClass('active');
      wrap.closest('tr').addClass('select-open');
      wrap.find('.list-value').attr('aria-expanded', true);
    } // Visually mark the specified option as "selected".


    function selectItem(opt, isInit, optColor) {
      isInit = 'undefined' === typeof isInit ? false : isInit;

      if (undefined !== optColor && '' !== optColor) {
        value.html('<span style="background-color: ' + optColor + '" data-color="' + optColor + '"></span>' + opt.text());
      } else {
        value.text(opt.text());
      }

      $('.current', items).removeAttr('aria-selected');
      $('.current', items).removeClass('current');
      opt.addClass('current');
      opt.attr('aria-selected', true);
      items.attr('aria-activedescendant', opt.attr('id'));
      stateClose(); // Also update the select list value.

      jq.val(opt.data('value'));

      if (!isInit) {
        jq.trigger('change');
      }
    } // Element constructor.


    function init() {
      var selectID;
      setupElement();
      populateList();
      handleSelectionChange();
      items.find('li').not('.optgroup-label').on('click', function onItemClick(ev) {
        var opt = $(ev.target);
        selectItem(opt, false, opt.data('color'));
        handleValue();
      });
      handle.on('click', stateToggle);
      value.on('click', stateToggle);
      jq.on('focus', stateOpen);
      $(document).click(function onOutsideClick(ev) {
        var jq = $(ev.target),
            selectID;

        if (jq.closest('.select-container').length) {
          return;
        }

        if (jq.is('label') && jq.attr('for')) {
          selectID = jq.attr('for');

          if ($('select#' + selectID).length) {
            return;
          }
        }

        stateClose();
      });
      selectID = jq.attr('id');

      if (selectID) {
        $('label[for=' + selectID + ']').on('click', stateOpen);
      }

      jq.addClass('sui-styled');
    }

    init();
    return this;
  }; // Convert all select lists to fancy sui Select lists.


  $('.sui-2-9-6 select:not([multiple])').each(function () {
    SUI.suiSelect(this);
  });
})(jQuery);
(function ($) {
  // Convert all select lists to fancy sui Select lists.
  if ($('.sui-color-accessible')[0]) {
    $('.sui-select').SUIselect2({
      placeholder: function placeholder() {
        $(this).data('placeholder');
      },
      dropdownCssClass: 'sui-select-dropdown sui-color-accessible'
    });
    $('.sui-search').SUIselect2({
      placeholder: function placeholder() {
        $(this).data('placeholder');
      },
      minimumInputLength: 2,
      maximumSelectionLength: 1,
      dropdownCssClass: 'sui-search-dropdown sui-color-accessible'
    });
    $('.sui-variables').SUIselect2({
      dropdownCssClass: 'sui-variables-dropdown sui-color-accessible'
    });
  } else {
    $('.sui-select').SUIselect2({
      placeholder: function placeholder() {
        $(this).data('placeholder');
      },
      dropdownCssClass: 'sui-select-dropdown'
    });
    $('.sui-search').SUIselect2({
      placeholder: function placeholder() {
        $(this).data('placeholder');
      },
      minimumInputLength: 2,
      maximumSelectionLength: 1,
      dropdownCssClass: 'sui-search-dropdown'
    });
    $('.sui-variables').SUIselect2({
      dropdownCssClass: 'sui-variables-dropdown'
    });
  }
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode
  'use strict'; // Define global SUI object if it doesn't exist

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.sideTabs = function (element) {
    var $this = $(element),
        $label = $this.parent('label'),
        $data = $this.data('tab-menu'),
        $wrapper = $this.closest('.sui-side-tabs'),
        $alllabels = $wrapper.find('>.sui-tabs-menu .sui-tab-item'),
        $allinputs = $alllabels.find('input'),
        newContent;
    $this.on('click', function (e) {
      $alllabels.removeClass('active');
      $allinputs.removeProp('checked');
      $wrapper.find('.sui-tabs-content>div[data-tab-content]').removeClass('active');
      $label.addClass('active');
      $this.prop('checked', true);
      newContent = $wrapper.find('.sui-tabs-content div[data-tab-content="' + $data + '"]');

      if (newContent.length) {
        newContent.addClass('active');
      }
    });
  };

  $('.sui-2-9-6 .sui-side-tabs label.sui-tab-item input').each(function () {
    SUI.sideTabs(this);
  });
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.floatInput = function () {
    $('body').ready(function () {
      var $moduleName = $('.sui-sidenav .sui-with-floating-input'),
          $pageHeader = $('.sui-header-inline'),
          $pageTitle = $pageHeader.find('.sui-header-title');
      var $titleWidth = $pageTitle.width(),
          $navWidth = $pageHeader.next().find('.sui-sidenav').width();

      if ($titleWidth > $navWidth) {
        $moduleName.each(function () {
          $(this).css({
            'left': $titleWidth + 20 + 'px'
          });
        });
      }
    });
  };

  SUI.floatInput();
})(jQuery);
(function ($) {
  // Enable strict mode.
  'use strict';

  var _$stickies = [].slice.call(document.querySelectorAll('.sui-box-sticky'));

  _$stickies.forEach(function (_$sticky) {
    if (CSS.supports && CSS.supports('position', 'sticky')) {
      if (null !== _$sticky.offsetParent) {
        apply_sticky_class(_$sticky);
      }

      window.addEventListener('scroll', function () {
        if (null !== _$sticky.offsetParent) {
          apply_sticky_class(_$sticky);
        }
      });
    }
  });

  function apply_sticky_class(_$sticky) {
    var currentOffset = _$sticky.getBoundingClientRect().top;

    var stickyOffset = parseInt(getComputedStyle(_$sticky).top.replace('px', ''));
    var isStuck = currentOffset <= stickyOffset;

    if (isStuck) {
      _$sticky.classList.add('sui-is-sticky');
    } else {
      _$sticky.classList.remove('sui-is-sticky');
    }
  }
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.suiTabs = function (config) {
    var data;
    var types = ['tab', 'pane'];
    var type;
    var groups = [];
    var activeGroups = [];
    var activeChildren = [];
    var activeItems = [];
    var indexGroup;
    var indexItem;
    var memory = [];

    function init(options) {
      var groupIndex;
      var tabItems;
      var itemIndex;
      var hashId;
      data = options;
      setDefaults();
      groups.tab = document.querySelectorAll(data.tabGroup);
      groups.pane = document.querySelectorAll(data.paneGroup);

      for (groupIndex = 0; groupIndex < groups.tab.length; groupIndex++) {
        tabItems = groups.tab[groupIndex].children;

        for (itemIndex = 0; itemIndex < tabItems.length; itemIndex++) {
          tabItems[itemIndex].addEventListener('click', onClick.bind(this, groupIndex, itemIndex), false);
          indexGroup = groupIndex;
          indexItem = itemIndex;

          if (window.location.hash) {
            hashId = window.location.hash.replace(/[^\w-_]/g, '');

            if (hashId === tabItems[itemIndex].id) {
              setNodes(groupIndex, itemIndex);
            }
          }
        }
      }
    }

    function onClick(groupIndex, itemIndex) {
      setNodes(groupIndex, itemIndex);
      setCallback(indexGroup, indexItem);
    }

    function setNodes(groupIndex, itemIndex) {
      var i;
      indexGroup = groupIndex;
      indexItem = itemIndex;

      for (i = 0; i < types.length; i++) {
        type = types[i];
        setActiveGroup();
        setActiveChildren();
        setActiveItems();
        putActiveClass();
      }

      memory[groupIndex] = [];
      memory[groupIndex][itemIndex] = true;
    }

    function putActiveClass() {
      var i;

      for (i = 0; i < activeChildren[type].length; i++) {
        activeChildren[type][i].classList.remove(data[type + 'Active']);
      }

      activeItems[type].classList.add(data[type + 'Active']);
    }

    function setDefaults() {
      var i;

      for (i = 0; i < types.length; i++) {
        type = types[i];
        setOption(type + 'Group', '[data-' + type + 's]');
        setOption(type + 'Active', 'active');
      }
    }

    function setOption(key, value) {
      data = data || [];
      data[key] = data[key] || value;
    }

    function setActiveGroup() {
      activeGroups[type] = groups[type][indexGroup];
    }

    function setActiveChildren() {
      activeChildren[type] = activeGroups[type].children;
    }

    function setActiveItems() {
      activeItems[type] = activeChildren[type][indexItem];
    }

    function setCallback() {
      if ('function' === typeof data.callback) {
        data.callback(activeItems.tab, activeItems.pane);
      }
    }

    return init(config);
  };

  SUI.tabsOverflow = function ($el) {
    var tabs = $el.closest('.sui-tabs').find('[data-tabs], [role="tablist"]'),
        leftButton = $el.find('.sui-tabs-navigation--left'),
        rightButton = $el.find('.sui-tabs-navigation--right');

    function overflowing() {
      if (tabs[0].scrollWidth > tabs.width()) {
        if (0 === tabs.scrollLeft()) {
          leftButton.addClass('sui-tabs-navigation--hidden');
        } else {
          leftButton.removeClass('sui-tabs-navigation--hidden');
        }

        reachedEnd(0);
        return true;
      } else {
        leftButton.addClass('sui-tabs-navigation--hidden');
        rightButton.addClass('sui-tabs-navigation--hidden');
        return false;
      }
    }

    overflowing();

    function reachedEnd(offset) {
      var newScrollLeft, width, scrollWidth;
      newScrollLeft = tabs.scrollLeft() + offset;
      width = tabs.outerWidth();
      scrollWidth = tabs.get(0).scrollWidth;

      if (scrollWidth - newScrollLeft <= width) {
        rightButton.addClass('sui-tabs-navigation--hidden');
      } else {
        rightButton.removeClass('sui-tabs-navigation--hidden');
      }
    }

    leftButton.click(function () {
      rightButton.removeClass('sui-tabs-navigation--hidden');

      if (0 >= tabs.scrollLeft() - 150) {
        leftButton.addClass('sui-tabs-navigation--hidden');
      }

      tabs.animate({
        scrollLeft: '-=150'
      }, 400, function () {});
      return false;
    });
    rightButton.click(function () {
      leftButton.removeClass('sui-tabs-navigation--hidden');
      reachedEnd(150);
      tabs.animate({
        scrollLeft: '+=150'
      }, 400, function () {});
      return false;
    });
    $(window).resize(function () {
      overflowing();
    });
    tabs.scroll(function () {
      overflowing();
    });
  };

  SUI.tabs = function (config) {
    var tablist = $('.sui-tabs > div[role="tablist"]');
    var data = config; // For easy reference.

    var keys = {
      end: 35,
      home: 36,
      left: 37,
      up: 38,
      right: 39,
      down: 40,
      "delete": 46,
      enter: 13,
      space: 32
    }; // Add or substract depending on key pressed.

    var direction = {
      37: -1,
      38: -1,
      39: 1,
      40: 1
    }; // Prevent function from running if tablist does not exist.

    if (!tablist.length) {
      return;
    } // Deactivate all tabs and tab panels.


    function deactivateTabs(tabs, panels) {
      tabs.removeClass('active');
      tabs.attr('tabindex', '-1');
      tabs.attr('aria-selected', false);
      panels.removeClass('active');
      panels.attr('hidden', true);
    } // Activate current tab panel.


    function activateTab(tab) {
      var tabs = $(tab).closest('[role="tablist"]').find('[role="tab"]'),
          panels = $(tab).closest('.sui-tabs').find('> .sui-tabs-content > [role="tabpanel"]'),
          controls = $(tab).attr('aria-controls'),
          panel = $('#' + controls);
      deactivateTabs(tabs, panels);
      $(tab).addClass('active');
      $(tab).removeAttr('tabindex');
      $(tab).attr('aria-selected', true);
      panel.addClass('active');
      panel.attr('hidden', false);
      panel.removeAttr('hidden');
    } // When a "tablist" aria-orientation is set to vertical,
    // only up and down arrow should function.
    // In all other cases only left and right should function.


    function determineOrientation(event, index, tablist) {
      var key = event.keyCode || event.which,
          vertical = 'vertical' === $(tablist).attr('aria-orientation'),
          proceed = false; // Check if aria orientation is set to vertical.

      if (vertical) {
        if (keys.up === key || keys.down === key) {
          event.preventDefault();
          proceed = true;
        }
      } else {
        if (keys.left === key || keys.right === key) {
          proceed = true;
        }
      }

      if (true === proceed) {
        switchTabOnArrowPress(event, index);
      }
    } // Either focus the next, previous, first, or last tab
    // depending on key pressed.


    function switchTabOnArrowPress(event, index) {
      var pressed, target, tabs;
      pressed = event.keyCode || event.which;

      if (direction[pressed]) {
        target = event.target;
        tabs = $(target).closest('[role="tablist"]').find('> [role="tab"]');

        if (undefined !== index) {
          if (tabs[index + direction[pressed]]) {
            tabs[index + direction[pressed]].focus();
          } else if (keys.left === pressed || keys.up === pressed) {
            tabs[tabs.length - 1].focus();
          } else if (keys.right === pressed || keys.down === pressed) {
            tabs[0].focus();
          }
        }
      }
    } // Callback function.


    function setCallback(currentTab) {
      var tab = $(currentTab),
          controls = tab.attr('aria-controls'),
          panel = $('#' + controls);

      if ('function' === typeof data.callback) {
        data.callback(tab, panel);
      }
    } // When a tab is clicked, activateTab is fired to activate it.


    function clickEventListener(event) {
      var tab = event.target;
      activateTab(tab);

      if (undefined !== data && 'undefined' !== data) {
        setCallback(tab);
      }

      event.preventDefault();
      event.stopPropagation();
    }

    function keydownEventListener(event, index, tablist) {
      var key = event.keyCode || event.which;

      switch (key) {
        case keys.end:
          event.preventDefault(); // Actiavte last tab.
          // focusLastTab();

          break;

        case keys.home:
          event.preventDefault(); // Activate first tab.
          // focusFirstTab();

          break;
        // Up and down are in keydown
        // because we need to prevent page scroll.

        case keys.up:
        case keys.down:
          determineOrientation(event, index, tablist);
          break;
      }
    }

    function keyupEventListener(event, index, tablist) {
      var key = event.keyCode || event.which;

      switch (key) {
        case keys.left:
        case keys.right:
          determineOrientation(event, index, tablist);
          break;

        case keys.enter:
        case keys.space:
          activateTab(event);
          break;
      }
    }

    function init() {
      var tabgroup = tablist.closest('.sui-tabs'); // Run the function for each group of tabs to prevent conflicts
      // when having child tabs.

      tabgroup.each(function () {
        var tabs, index;
        tabgroup = $(this);
        tablist = tabgroup.find('> [role="tablist"]');
        tabs = tablist.find('> [role="tab"]'); // Trigger events on click.

        tabs.on('click', function (e) {
          clickEventListener(e); // Trigger events when pressing key.
        }).keydown(function (e) {
          index = $(this).index();
          keydownEventListener(e, index, tablist); // Trigger events when releasing key.
        }).keyup(function (e) {
          index = $(this).index();
          keyupEventListener(e, index, tablist);
        });
      });
    }

    init();
    return this;
  };

  if (0 !== $('.sui-2-9-6 .sui-tabs').length) {
    // Support tabs new markup.
    SUI.tabs(); // Support legacy tabs.

    SUI.suiTabs();
    $('.sui-2-9-6 .sui-tabs-navigation').each(function () {
      SUI.tabsOverflow($(this));
    });
  }
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.treeOnLoad = function (element) {
    var tree = $(element),
        leaf = tree.find('li[role="treeitem"]'),
        branch = leaf.find('> ul[role="group"]'); // Hide sub-groups

    branch.slideUp();
    leaf.each(function () {
      var leaf = $(this),
          openLeaf = leaf.attr('aria-expanded'),
          checkLeaf = leaf.attr('aria-selected'),
          node = leaf.find('> .sui-tree-node'),
          checkbox = node.find('> .sui-node-checkbox'),
          button = node.find('> span[role="button"], > button'),
          icon = node.find('> span[aria-hidden]'),
          branch = leaf.find('> ul[role="group"]'),
          innerLeaf = branch.find('> li[role="treeitem"]'),
          innerCheck = innerLeaf.find('> .sui-tree-node > .sui-node-checkbox'); // FIX: Remove unnecessary elements for leafs

      if (('selector' === tree.data('tree') || 'selector' === tree.attr('data-tree')) && 0 !== icon.length) {
        button.remove();
      }

      if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) !== _typeof(openLeaf) && false !== openLeaf) {
        // Open sub-groups
        if ('true' === openLeaf) {
          branch.slideDown();
        }
      } else {
        if (0 !== branch.length) {
          leaf.attr('aria-expanded', 'false');
        } else {
          // FIX: Remove unnecessary elements for leafs
          if (0 !== button.length) {
            button.remove();
          }
        }
      }

      if ((typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) !== _typeof(checkLeaf) && false !== checkLeaf) {
        // Checked leafs
        if ('true' === checkLeaf && 0 < branch.length) {
          innerLeaf.attr('aria-selected', 'true');

          if (0 !== checkbox.length && checkbox.is('label')) {
            checkbox.find('input').prop('checked', true);
          }

          if (0 !== innerCheck.length && innerCheck.is('label')) {
            innerCheck.find('input').prop('checked', true);
          }
        }
      } else {
        // Unchecked leafs
        leaf.attr('aria-selected', 'false');

        if (0 !== checkbox.length && checkbox.is('label')) {
          checkbox.find('input').prop('checked', false);
        }
      }
    });
  };

  SUI.treeButton = function (element) {
    var button = $(element);
    button.on('click', function (e) {
      var button = $(this),
          leaf = button.closest('li[role="treeitem"]'),
          branch = leaf.find('> ul[role="group"]');

      if (0 !== branch.length) {
        branch.slideToggle(250);

        if ('true' === leaf.attr('aria-expanded')) {
          leaf.attr('aria-expanded', 'false');
        } else {
          leaf.attr('aria-expanded', 'true');
        }
      }

      e.preventDefault();
    });
  };

  SUI.treeCheckbox = function (element) {
    var checkbox = $(element);
    checkbox.on('click', function () {
      var checkbox = $(this),
          leaf = checkbox.closest('li[role="treeitem"]'),
          branches = leaf.find('ul[role="group"]'),
          leafs = branches.find('> li[role="treeitem"]'),
          checks = leafs.find('> .sui-tree-node > .sui-node-checkbox input'),
          topBranch = leaf.parent('ul'),
          topLeaf = topBranch.parent('li');
      var countIndex = 0,
          countTopBranches = topLeaf.parents('ul').length - 1;

      if ('true' === leaf.attr('aria-selected')) {
        // Unselect current leaf
        leaf.attr('aria-selected', 'false'); // Unselect current checkbox

        if (checkbox.is('input')) {
          checkbox.prop('checked', false);
        } // Unselect child leafs


        if (0 !== branches.length) {
          leafs.attr('aria-selected', 'false');
        } // Unselect child checkboxes


        if (0 !== checks.length) {
          checks.prop('checked', false);
        } // Unselect branch(es) when not all leafs are selected


        if (leaf.parent().is('ul') && 'group' === leaf.parent().attr('role')) {
          leaf.parents('ul').each(function () {
            var branch = $(this),
                leaf = branch.parent('li'),
                check = leaf.find('> .sui-tree-node > .sui-node-checkbox input');

            if ('treeitem' === leaf.attr('role')) {
              leaf.attr('aria-selected', 'false');

              if (0 !== check.length) {
                check.prop('checked', false);
              }
            }
          });
        }
      } else {
        // Select current leaf
        leaf.attr('aria-selected', 'true'); // Select current checkbox

        if (checkbox.is('input')) {
          checkbox.prop('checked', true);
        } // Select child leafs


        if (0 !== branches.length) {
          leafs.attr('aria-selected', 'true');
        } // Select child checkboxes


        if (0 !== checks.length) {
          checks.prop('checked', true);
        } // Select top branch(es) when all leafs are selected


        if (0 === topLeaf.find('li[aria-selected="false"]').length) {
          topLeaf.attr('aria-selected', 'true');

          for (countIndex = 0; countTopBranches >= countIndex; countIndex++) {
            topLeaf.parent('ul').eq(countIndex).each(function () {
              var branch = $(this),
                  leafFalse = branch.find('> li[aria-selected="false"]');

              if (0 === leafFalse.length) {
                branch.parent('li').attr('aria-selected', 'true');
                branch.parent('li').find('> .sui-tree-node > .sui-node-checkbox input').prop('checked', true);
              }
            });
          }
        }
      }
    });
  };

  SUI.treeForm = function (element) {
    var button = $(element);

    if ('add' === button.attr('data-button')) {
      button.on('click', function () {
        var button = $(this),
            leaf = button.closest('li[role="treeitem"]'),
            node = leaf.find('> .sui-tree-node'),
            expand = node.find('span[data-button="expander"]'),
            branch = leaf.find('> ul[role="group"]'),
            content = branch.find('> span[role="contentinfo"]');

        if (0 !== content.length) {
          // Hide button
          button.hide();
          button.removeAttr('tabindex');
          button.attr('aria-hidden', 'true'); // Show content

          content.addClass('sui-show');
          content.removeAttr('aria-hidden'); // FIX: Open tree if it's closed

          if ('true' !== leaf.attr('aria-expanded')) {
            expand.click();
          } // Focus content


          content.focus();
          content.attr('tabindex', '-1');
        }
      });
    }

    if ('remove' === button.attr('data-button')) {
      button.on('click', function () {
        var button = $(this),
            content = button.closest('span[role="contentinfo"]'),
            leaf = content.closest('li[role="treeitem"]'),
            node = leaf.find('> .sui-tree-node'),
            btnAdd = node.find('> span[data-button="add"]'); // Hide content

        content.removeClass('sui-show');
        content.removeAttr('tabindex');
        content.attr('aria-hidden', 'true'); // Show button

        btnAdd.show();
        btnAdd.removeAttr('aria-hidden');
        btnAdd.focus();
        btnAdd.attr('tabindex', '-1');
      });
    }
  };

  SUI.suiTree = function (element, dynamic) {
    var tree = $(element);

    if (!tree.hasClass('sui-tree') || (typeof undefined === "undefined" ? "undefined" : _typeof(undefined)) === tree.attr('data-tree')) {
      return;
    }

    function button() {
      var leaf = tree.find('li[role="treeitem"]'),
          node = leaf.find('> .sui-tree-node'),
          button = node.find('> [data-button="expander"]'),
          label = node.find('> span.sui-node-text');
      button.each(function () {
        var button = $(this);
        SUI.treeButton(button);
      });
      label.each(function () {
        var label = $(this);
        SUI.treeButton(label);
      });
    }

    function checkbox() {
      var leaf = tree.find('li[role="treeitem"]'),
          node = leaf.find('> .sui-tree-node'),
          checkbox = node.find('> .sui-node-checkbox');
      checkbox.each(function () {
        var checkbox = $(this).is('label') ? $(this).find('input') : $(this);
        SUI.treeCheckbox(checkbox);
      });
    }

    function add() {
      var leaf = tree.find('li[role="treeitem"]'),
          node = leaf.find('> .sui-tree-node'),
          button = node.find('> [data-button="add"]');
      button.each(function () {
        var button = $(this);
        SUI.treeForm(button);
      });
    }

    function remove() {
      var button = tree.find('[data-button="remove"]');
      button.each(function () {
        var button = $(this);
        SUI.treeForm(button);
      });
    }

    function init() {
      if ('selector' === tree.data('tree') || 'directory' === tree.data('tree') || 'selector' === tree.attr('data-tree') || 'directory' === tree.atrr('data-tree')) {
        // Initial setup
        SUI.treeOnLoad(tree); // Expand action

        button(); // Select action

        checkbox(); // Add folder action

        if (true === dynamic || 'true' === dynamic) {
          add();
          remove();
        }
      } // TEST: Verify if input is checked on load
      // if ( 'selector' === tree.data( 'tree' ) ) {
      //
      // 	if ( 0 !== tree.find( 'input' ).length ) {
      //
      // 		tree.find( 'input' ).each( function() {
      //
      // 			console.log( '#' + $( this ).attr( 'id' ) + ': ' + $( this ).prop( 'checked' ) );
      //
      // 			// Output:
      // 			// #input-id: value
      //
      // 		});
      // 	}
      // }

    }

    init();
    return this;
  };

  if (0 !== $('.sui-2-9-6 .sui-tree').length) {
    $('.sui-2-9-6 .sui-tree').each(function () {
      SUI.suiTree($(this), true);
    });
  }
})(jQuery);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function ($) {
  // Enable strict mode.
  'use strict'; // Define global SUI object if it doesn't exist.

  if ('object' !== _typeof(window.SUI)) {
    window.SUI = {};
  }

  SUI.upload = function () {
    $('.sui-2-9-6 .sui-upload-group input[type="file"]').on('change', function (e) {
      var file = $(this)[0].files[0],
          message = $(this).find('~ .sui-upload-message');

      if (file) {
        message.text(file.name);
      }
    });
  };

  SUI.upload();
})(jQuery);