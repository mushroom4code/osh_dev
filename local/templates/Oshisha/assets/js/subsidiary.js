BX.ready(
    function () {
        const boxSelect = $('#subsidiary_link');
        if (boxSelect.length > 0) {
            $(boxSelect).select2({
                minimumResultsForSearch: -1,
            })

            $(boxSelect).on('select2:open', function () {
                $('.select2-container--default .select2-selection--single .select2-selection__arrow b')
                    .attr('style', 'transform:rotate(180deg)')
            }).on('select2:close', function () {
                $('.select2-container--default .select2-selection--single .select2-selection__arrow b')
                    .removeAttr('style')
            });

            let storeAccess = localStorage.getItem("store_access");
            if (!storeAccess) {
                let boxStory = document.querySelector('.filial-popup');
                if (!BX.findChildByClassName(boxStory, 'popup-message-filial')) {
                    let style = 'position-absolute ml-5'
                    if ($(document).width() <= 768) {
                        style = 'position-fixed left-0'
                    }

                    boxStory.appendChild(BX.create('DIV', {
                        props: {
                            className: 'popup-message-filial p-3 ' + style
                        },
                        children: [
                            BX.create('DIV', {
                                props: {
                                    className: 'font-14 mb-1 d-flex flex-row align-items-center justify-between'
                                },
                                children: [
                                    BX.create('DIV', {
                                        html: '<svg width="25" height="25" viewBox="0 0 20 20" fill="none" class="mr-2"' +
                                            ' xmlns="http://www.w3.org/2000/svg"> <path opacity="0.9"' +
                                            ' d="M2.5 10.864C2.5 9.94484 2.5 9.48525 2.8272 9.21725C2.94337 9.12208 3.08456 9.04592 3.24191 8.9935C3.68511 8.84592 4.26758 8.99125 5.43251 9.28192C6.32188 9.50383 6.76657 9.61475 7.21592 9.60325C7.38095 9.59908 7.54501 9.58258 7.70571 9.55417C8.14326 9.47675 8.53325 9.28217 9.31333 8.89292L10.4653 8.31801C11.4645 7.81939 11.9641 7.57008 12.5376 7.51259C13.1111 7.45511 13.6807 7.59723 14.8199 7.88149L15.7906 8.12368C16.6156 8.32953 17.0281 8.4325 17.2641 8.6775C17.5 8.9225 17.5 9.248 17.5 9.899V14.9693C17.5 15.8885 17.5 16.3481 17.1728 16.6161C17.0567 16.7113 16.9154 16.7874 16.7581 16.8398C16.3149 16.9874 15.7324 16.8421 14.5675 16.5514C13.6781 16.3295 13.2334 16.2186 12.7841 16.2301C12.6191 16.2343 12.455 16.2508 12.2943 16.2792C11.8568 16.3566 11.4668 16.5512 10.6867 16.9404L9.53475 17.5153C8.5355 18.0139 8.03593 18.2633 7.46244 18.3208C6.88895 18.3783 6.31933 18.2361 5.18008 17.9518L4.20943 17.7097C3.38441 17.5038 2.97189 17.4008 2.73595 17.1558C2.5 16.9108 2.5 16.5853 2.5 15.9343V10.864Z"' +
                                            ' fill="#BFBFBF"/> <path fill-rule="evenodd" clip-rule="evenodd"' +
                                            ' d="M10 1.66663C7.23857 1.66663 5 3.79338 5 6.41688C5 9.01979 6.59582 12.0572 9.08567 13.1434C9.66608 13.3966 10.3339 13.3966 10.9143 13.1434C13.4042 12.0572 15 9.01979 15 6.41688C15 3.79338 12.7614 1.66663 10 1.66663ZM10 8.33329C10.9205 8.33329 11.6667 7.5871 11.6667 6.66663C11.6667 5.74615 10.9205 4.99996 10 4.99996C9.0795 4.99996 8.33333 5.74615 8.33333 6.66663C8.33333 7.5871 9.0795 8.33329 10 8.33329Z"' +
                                            ' fill="#FF0504"/></svg>' +
                                            '<span class="font-weight-500">Уважаемые покупатели!</span>'
                                    }),
                                    BX.create('span', {
                                        props: {
                                            className: 'close_photo storyClose'
                                        },
                                        events: {
                                            click: () => {
                                                localStorage.setItem("store_access", "1");
                                                document.querySelector('.popup-message-filial').remove();
                                            }
                                        }
                                    }),
                                ]
                            }), BX.create('P', {
                                props: {
                                    className: 'font-11 mb-1'
                                },
                                text: 'Мы добавили для вашего удобства филиалы, теперь вы можете выбрать нужный товар '
                                    + ' в своем городе!'
                            }),

                        ]
                    }))
                }
            }
        }
        $(boxSelect).on('select2:select', function (event) {
            $('body').append('<div class="position-fixed width-100 height-100 top-0 remove-class d-flex justify-content-center ' +
                'align-items-center" style="background: rgba(60, 60, 60, 0.81); z-index:1000">' +
                '<div class="loader" style="width: 107px;height: 107px;">' +
                '<div class="inner one" style="border-bottom: 4px solid #ffffff"></div>' +
                '<div class="inner two" style="border-bottom: 4px solid #ffffff"></div>' +
                '<div class="inner three" style="border-bottom: 4px solid #ffffff"></div>' +
                '</div></div>');
            BX.ajax({
                url: '/local/ajax/subsidiary.php',
                method: 'POST',
                data: {subsidiary: event.target.value},
                onsuccess: function (response) {
                    if (response === 'success') {
                        location.reload()
                        document.addEventListener("DOMContentLoaded", () => {
                            $('body').find('.remove-class').remove()
                        });
                    }
                }
            })
        })
    }
)