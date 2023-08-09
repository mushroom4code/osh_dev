$(document).ready(function () {
    function setupCityItems() {
        let all_cities = $('#cities-list');
        $('.city-item').each(function () {
            $(this).click(function () {
                let city_selected = $(this).text();
                $('#city-search').val(city_selected);
                $('#choose-city-btn').removeAttr('disabled');
                all_cities.hide();
            });
        });
    }
    function setLocationsListContent() {
        var locations_array = BX.localStorage.get('city_choosers_locations_array');
        var placeModalTemplate = `<input id="city-search" class="form-control search" type="text" name="cityother"
                                           placeholder="Ваш город ..." value=""
                                           autocomplete="off" required>
                                           <div class="cities-list-wrap mb-3">
                <ul id="big-cities-list">
                    <li>
                        <span class="city-item">${locations_array.find(element => element == 'Москва')}</span>
                    </li>
                    <li>
                        <span class="city-item">${locations_array.find(element => element == 'Санкт-Петербург')}</span>
                    </li>
                    <li>
                        <span class="city-item">${locations_array.find(element => element == 'Нижний Новгород')}</span>
                    </li>
                    <li>
                        <span class="city-item">${locations_array.find(element => element == 'Екатеринбург')}</span>
                    </li>
                    <li>
                        <span class="city-item">${locations_array.find(element => element == 'Пермь')}</span>
                    </li>
                    <li>
                        <span class="city-item">${locations_array.find(element => element == 'Новосибирск')}</span>
                    </li>
                    <li>
                        <span class="city-item">${locations_array.find(element => element == 'Казань')}</span>
                    </li>
                </ul>
                <ul id="cities-list" class="list" style="display: none">`

        locations_array.forEach((element) => {
            placeModalTemplate += `<li>
                    <span class="city-item">${element}</span>
                    </li>`
        })
        placeModalTemplate += `</ul></div>`
        $('#locations').html(placeModalTemplate);
        let all_cities = $('#cities-list'),
            big_cities = $('#big-cities-list');
        $("#city-search").keyup(function () {
            all_cities.show();
            big_cities.hide();
            let length = $(this).val();
            if (length.length === 0) {
                all_cities.hide();
                big_cities.show();
            }
            if (all_cities.is(':empty')) {
                $('#choose-city-btn').attr('disabled', 'disabled');
                big_cities.show();
            }
        });
        setupCityItems();

        let input = $('#city-search');
        input.on('input', function() {
            $.ajax({
                url: window.location.origin + '/local/templates/Oshisha/geolocation/location_ajax.php',
                method: 'POST',
                data: {
                    action: 'locationsListSearch',
                    searchText: input.val()
                },
                success: function (result) {
                    if (result) {
                        let cities_list = '';
                        JSON.parse(result).forEach((element) => {
                            cities_list += `<li>
                            <span class="city-item">${element}</span>
                            </li>`
                        });
                        $('#cities-list').html(cities_list);
                        setupCityItems();
                    }
                }.bind(this)
            })
        })
    }

    $('#placeModal').on('shown.bs.modal', function () {
        if (BX.localStorage.get('city_choosers_locations_array')) {
            if (!$('#locations').html()) {
                setLocationsListContent()
            }
        } else {
            $.ajax({
                url: window.location.origin + '/local/templates/Oshisha/geolocation/location_ajax.php',
                method: 'POST',
                data: {
                    action: 'setLocationsListStorage'
                },
                success: function (result) {
                    if (result) {
                        BX.localStorage.set("city_choosers_locations_array", result)
                        if (!$('#locations').html()) {
                            setLocationsListContent()
                        }
                    }
                }.bind(this)
            })
        }
    })

    $("#formofcity").submit(function (event) {
        $.ajax({
            url: window.location.origin + '/local/templates/Oshisha/geolocation/location_ajax.php',
            method: 'POST',
            data: {
                action: 'locationsListSubmit',
                searchText: $("#city-search").val()
            },
            success: function (result) {
                if (result) {
                    if (JSON.parse(result)['status'] == 'success') {
                        location.reload();
                    } else {
                        console.log(result);
                    }
                }
            }.bind(this)
        });

        event.preventDefault();
    });
});