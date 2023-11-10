var countryCache;
var countryRequesting = false;
/**
 * @author Yakov Akulov
 * @email jakulov@gmail.com
 * @line jakuov.ru
 */
(function ($) {
    $.widget('custom.phonecode', {
        data: [],
        container: null,
        prefixField: null,
        searchTimeout: null,
        suggestTimeout: null,
        hideTimeout: null,
        options: {
            default_prefix: '',
            phone_val: '',
            prefix: '',
            preferCo: ''
        },
        _create: function () {
            this._loadData();
        },

        _createBox: function () {
            this.element.wrap('<div class="country-phone flex flex-row items-center">');
            var container = this.element.parent('.country-phone');
            var selector = $('<div class="country-phone-selector">' +
                '<div class="country-phone-selected h-full flex items-center"></div>' +
                '<div class="country-phone-options bg-white dark:bg-grayButton">' +
                '<input type="text" class="country-phone-search input-search-in-box bg-textDark p-3 dark:bg-tagFilterGray' +
                ' w-full text-textLight rounded-lg dark:text-white border-0" placeholder="Введите страну" value=""> ' +
                '<div class="options-list mt-2"></div></div></div>');
            $(selector).prependTo(container);

            var prefixName = this.options.prefix ?
                this.options.prefix : '__phone_prefix';
            var hidden = $('<input type="hidden" name="' + prefixName + '" value="' + this.options.default_prefix + '">');
            $(hidden).appendTo(container);
            this.container = container;
            this.prefixField = hidden;
        },

        _loadData: function () {
            var self = this;
            if (!countryCache && !countryRequesting) {
                countryRequesting = $.getJSON('/local/assets/js/flags-mask/countries.json', {})
                    .done(function (json) {
                        self.data = json;
                        countryCache = self.data;
                        self._initSelector();
                    })
                    .fail(function (xhr, status, error) {
                        //alert(status + ' ' + error);
                        self.data = countries;
                        countryCache = self.data;
                        self._initSelector();
                    });

            } else if (countryCache) {
                this.data = countryCache;
                self._initSelector();
            } else if (countryRequesting) {
                countryRequesting.done(function (json) {
                    self.data = json;
                    countryCache = self.data;
                    self._initSelector();
                });
            }
        },

        _initSelector: function () {
            this._createBox();
            var options = this.container.find('.country-phone-options');
            /** Enterego * Выставление страны по номеру тел */
            var bool_init_mask = false;
            var val_country_code = '', code_input = '';
            /** Enterego search country */
            var option_list = this.container.find('.options-list');
            /** Enterego search country */
            var selector = this.container.find('.country-phone-selected');
            var selected = null;
            var self = this;
            var searchInput = this.container.find('.input-search-in-box');
            $(searchInput).bind('keyup', function (e) {
                if (self.suggestTimeout) {
                    window.clearTimeout(self.suggestTimeout);
                }
                var input = this;
                var ev = e;
                self.suggestTimeout = window.setTimeout(function () {
                    var text = $(input).val().toLowerCase();
                    self.suggestCountry(text);
                    if (ev.keyCode == 40) {
                        self._moveSuggestDown(options);
                    }
                    if (ev.keyCode == 38) {
                        self._moveSuggestUp(options);
                    }
                    if (ev.keyCode == 13) {
                        var hovered = $(options).find('.hovered:visible');
                        if (hovered.length) {
                            if (!$(hovered).hasClass('country-phone-search')) {
                                self.setElementSelected(hovered);
                                self._toggleSelector();
                            }
                        }
                        ev.stopPropagation();
                        ev.preventDefault();
                    }
                }, 100);
            }).bind('keypress', function (e) {
                if (e.keyCode == 13) {
                    e.stopPropagation();
                    e.preventDefault();
                    return false;
                }
            });

            for (var i = 0; i < this.data.length; i++) {
                if (i == 0) {
                    selected = this.data[i];
                }
                var country = this.data[i];
                var prefCountry = country.co;

                /** Enterego * Выставление страны по номеру тел */
                if (this.options.phone_val !== '' && this.options.phone_val.search(country.ph) === 1) {
                    bool_init_mask = country.mask;
                    val_country_code = prefCountry.toLowerCase();
                    code_input = country.ph;
                    selected = country;
                    this.options.preferCo = val_country_code;
                }

                var option = $(`<div data-phone="${country.ph}" data-mask="${country.mask}" data-co="${prefCountry.toLowerCase()}" 
                class="country-phone-option"> 
                <span class="flex flex-row items-center justify-content-end">+${country.ph} 
                    <div class="flag flag-${country.ph} ml-1 w-7 h-7 flex"> ${country.ic}</div>
                </span>
                  ${country.na}
                </div>`
                );
                $(option).appendTo(option_list);
                if (this.options.preferCo && (this.options.preferCo != undefined)) {
                    if (prefCountry == this.options.preferCo) {
                        selected = country;
                    }
                } else {
                    /** Enterego * Выставление страны по номеру тел */
                    if (bool_init_mask !== false) {
                        selected = country;
                    }
                }
            }
            if (selected) {
                this.container.find('.country-phone-selected')
                    .html('<div class="flag flag-' + selected.co + '  w-7 h-7"> ' + selected.ic + '</div>');
            }
            $(selector).bind('click', function (e) {
                self._toggleSelector();
            });
            $(option_list).find('.country-phone-option').bind('click', function () {
                self.setElementSelected(this);
                self._toggleSelector();
            });
            $(option_list).hover(function () {
                if (self.hideTimeout) {
                    window.clearTimeout(self.hideTimeout);
                }
            }, function () {
                var select = this;
                self.hideTimeout = window.setTimeout(self._mouseOverHide, 1000, select, self);
                self.hideTimeout = window.setTimeout(self._mouseOverHide, 1000, options, self);
            });

            this._initInput();
            /** Enterego * Выставление страны по номеру тел */
            if (bool_init_mask !== false && val_country_code !== '') {
                var new_str = this.options.phone_val.replace('+' + code_input, '');
                var new_code = String(code_input).split('');
                var str_code = '';
                $.each(new_code, function (i, val) {
                    str_code += '\\' + val;
                });
                this.container.find('input[data-input-type="phone"]').val(new_str);
                this.container.find('input[data-input-type="phone"]').inputmask("+ " + str_code + ' ' + bool_init_mask, {
                    minLength: 10,
                    removeMaskOnSubmit: true,
                    clearMaskOnLostFocus: true,
                    clearMaskOnLostHover: true,
                    clearIncomplete: true,
                });

                this.options.preferCo = val_country_code;
                this.container.find('input[name="__phone_prefix"]').val(code_input);
            }
        },

        _mouseOverHide: function (select, self) {
            if (self.container) {
                var searchInput = self.container.find('.country-phone-search');
                if (!$(searchInput).is(':focus')) {
                    $(select).hide();
                } else {
                    self.hideTimeout = window.setTimeout(self._mouseOverHide, 1000, select, self);
                }
            }
        },

        _moveSuggestDown: function (options) {
            var select = null;
            var hovered = $(options).find('.hovered:visible');
            if (hovered.length) {
                var next = $(hovered).next(':visible');
                if (next.length) {
                    select = next;
                } else {
                    next = $(hovered).nextUntil(':visible').last().next();
                    if (next.length) {
                        select = next;
                    }
                }
            }
            if (!select) {
                select = $(options).find('.country-phone-option:visible').first();
            }
            if (select) {
                $(options).find('.country-phone-option').add('.country-phone-search').removeClass('hovered');
                $(select).addClass('hovered');
            }
        },

        _moveSuggestUp: function (options) {
            var select = null;
            var hovered = $(options).find('.hovered:visible');
            if (hovered.length) {
                var next = $(hovered).prev(':visible');
                if (next.length) {
                    select = next;
                } else {
                    next = $(hovered).prevUntil(':visible').last().prev();
                    if (next.length) {
                        select = next;
                    }
                }
            }
            if (!select) {
                select = $(options).find('.country-phone-option:visible').last();
            }
            if (select) {
                $(options).find('.country-phone-option').add('.country-phone-search').removeClass('hovered');
                $(select).addClass('hovered');
            }
        },

        suggestCountry: function (text, checkCode) {
            /** Enterego search country */
            var options = this.container.find('.options-list');
            /** Enterego search country */
            var self = this;
            $(options).find('.country-phone-option').each(function () {
                if (text !== '') {
                    var match = $(this).text().toLowerCase();
                    if (match.indexOf(text) >= 0) {
                        $(this).show();
                        if (checkCode && checkCode != undefined) {
                            var code = $(this).data('phone');
                            var selCode = self.prefixField.val();
                            if (selCode == code) {
                                self.setElementSelected(this);
                            }
                        }
                    } else {
                        if (!checkCode) {
                            $(this).hide();
                        }
                    }
                } else {
                    $(this).show();
                }
            });
        },

        _toggleSelector: function () {
            var options = this.container.find('.country-phone-options');
            var opt_list = this.container.find('.options-list');
            if ($(options).is(':visible')) {
                $(options).hide('fast');
                $(opt_list).hide('fast');
                $(options).find('.country-phone-search').val('').blur();
                this.element.focus();
                this.suggestCountry('');
            } else {
                $(options).show('fast');
                $(opt_list).show('fast');
                window.setTimeout(function () {
                    var searchInp = $(options).find('.country-phone-search');
                    $(searchInp).val('').focus();
                }, 300);
            }
        },

        setElementSelected: function (el) {
            var selector = this.container.find('.country-phone-selected');
            var code = $(el).data('phone');
            var mask = $(el).data('mask');
            var sel = $(el).html();
            /**
             * Enterego
             * refresh mask user phone  with country code
             */
            var new_box = sel.replace('+' + code, '');
            var flags = new_box.split('</span>');
            $(selector).html(flags[0] + '</span>');

            var new_code = String(code).split('');
            var str_code = '';

            $.each(new_code, function (i, val) {
                str_code += '\\' + val;
            });

            this.container.find('input[data-input-type="phone"]').inputmask("+ " + str_code + ' ' + mask, {
                minLength: 10,
                removeMaskOnSubmit: true,
                clearMaskOnLostFocus: true,
                clearMaskOnLostHover: true,
                clearIncomplete: true,
            });

            this.prefixField.val(code);
            return code;
        },

        _initInput: function () {
            var self = this;
            this.element.bind('keyup', function () {
                var text = $(this).val();
                if (text.length > 1 && text[0] == '+') {
                    var code = text.substring(1);
                    if (self.searchTimeout) {
                        window.clearTimeout(self.searchTimeout);
                    }
                    var input = this;
                    window.setTimeout(function () {
                        var found = self.searchCountryCode(code);
                        if (found) {
                            text = $(input).val();
                            text = text.replace('+' + found, '');
                            $(input).val(text);
                        }
                    }, 1000);
                }
            });

            this.initInputVal();
        },

        initInputVal: function () {
            var text = this.element.val();
            var self = this;
            if (text.length > 1 && text[0] == '+') {
                for (var i = 6; i >= 1; i--) {
                    var code = text.substring(1, i);
                    var found = self.searchCountryCode(code);
                    if (found) {
                        text = this.element.val();
                        text = text.replace('+' + found, '');
                        this.element.val(text);
                        break;
                    }
                }
            } else if (text.length == 1 && text[0] == '+') {
                this.element.val('');
            }
        },

        searchCountryCode: function (code) {
            var options = this.container.find('.option_list');
            var search = code;
            var self = this;
            var found = false;
            var foundItems = [];
            $(options).find('.country-phone-option').each(function () {
                if (search == $(this).data('phone')) {
                    foundItems.push({
                        co: $(this).data('co'),
                        el: this
                    });
                }
            });

            if (foundItems.length == 1) {
                found = self.setElementSelected(foundItems[0].el);
            } else if (foundItems.length > 1) {
                for (var i = 0; i < foundItems.length; i++) {
                    if (self.options.preferCo) {
                        if (self.options.preferCo == foundItems[i].co) {
                            found = self.setElementSelected(foundItems[i].el);
                            break;
                        }
                    } else {
                        found = self.setElementSelected(foundItems[i].el);
                        break;
                    }
                }
                if (!found) {
                    found = self.setElementSelected(foundItems[0].el);
                }
            }

            return found;
        }
    });
})(jQuery);