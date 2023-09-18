BX.ready(function () {
    function setupCityItems() {
        let all_cities = document.getElementById('cities-list');
        [...document.querySelectorAll('.city-item')].forEach(item => {
            item.addEventListener('click', function () {
                let city_selected = item.textContent;
                let city_search = document.getElementById('city-search');
                city_search.setAttribute('chosen-city-code', item.getAttribute('city-code'));
                city_search.value = city_selected;
                document.getElementById('choose-city-btn').removeAttribute('disabled');
                all_cities.style.display = 'none';
            });
        });
    }

    function setLocationsListContent() {
        var locations_array = BX.localStorage.get('city_choosers_locations_array');
        var moscowItem = locations_array.find(element => element.name == 'Москва')
        var saintPetersburgItem = locations_array.find(element => element.name == 'Санкт-Петербург');
        var nizhnyNovgorodItem = locations_array.find(element => element.name == 'Нижний Новгород');
        var ekaterinburgItem = locations_array.find(element => element.name == 'Екатеринбург');
        var permItem = locations_array.find(element => element.name == 'Пермь');
        var novosibirskItem = locations_array.find(element => element.name == 'Новосибирск');
        var kazanItem = locations_array.find(element => element.name == 'Казань');
        var placeModalTemplate = `<input id="city-search" class="form-control search" type="text" name="cityother"
                                           placeholder="Ваш город ..." value=""
                                           autocomplete="off" required>
                                           <div class="cities-list-wrap mb-3">
                <ul id="big-cities-list">
                    <li>
                        <span class="city-item" city-code="${moscowItem.code}">${moscowItem.name}</span>
                    </li>
                    <li>
                        <span class="city-item" city-code="${saintPetersburgItem.code}">${saintPetersburgItem.name}</span>
                    </li>
                    <li>
                        <span class="city-item" city-code="${nizhnyNovgorodItem.code}">${nizhnyNovgorodItem.name}</span>
                    </li>
                    <li>
                        <span class="city-item" city-code="${ekaterinburgItem.code}">${ekaterinburgItem.name}</span>
                    </li>
                    <li>
                        <span class="city-item" city-code="${permItem.code}">${permItem.name}</span>
                    </li>
                    <li>
                        <span class="city-item" city-code="${novosibirskItem.code}">${novosibirskItem.name}</span>
                    </li>
                    <li>
                        <span class="city-item" city-code="${kazanItem.code}">${kazanItem.name}</span>
                    </li>
                </ul>
                <ul id="cities-list" class="list" style="display: none">`

        locations_array.forEach((element) => {
            placeModalTemplate += `<li>
                    <span class="city-item" city-code="${element.code}">${element.name}</span>
                    </li>`
        })
        placeModalTemplate += `</ul></div>`
        document.getElementById('locations').innerHTML = placeModalTemplate;
        let all_cities = document.getElementById('cities-list'),
            big_cities = document.getElementById('big-cities-list');
        document.getElementById("city-search").addEventListener('keyup', function () {
            all_cities.style.display = 'block';
            big_cities.style.display = 'none';
            let length = this.value;
            if (length.length === 0) {
                all_cities.style.display = 'none';
                big_cities.style.display = 'block';
            }
            if (all_cities.matches(':empty')) {
                document.getElementById('choose-city-btn').setAttribute('disabled', 'disabled');
                big_cities.style.display = 'block';
            }
        });
        setupCityItems();

        let input = document.getElementById('city-search');
        let interval;
        input.addEventListener('input', function () {
            clearInterval(interval);
            interval = setInterval(() => {
                BX.ajax({
                    url: '/local/templates/Oshisha/geolocation/location_ajax.php',
                    method: 'POST',
                    data: {
                        action: 'locationsListSearch',
                        searchText: input.value
                    },
                    onsuccess: function (result) {
                        if (result) {
                            let cities_list = '';
                            JSON.parse(result).forEach((element) => {
                                cities_list += `<li>
                            <span class="city-item" city-code="${element.code}">${element.name}</span>
                            </li>`
                            });
                            document.getElementById('cities-list').innerHTML = cities_list;
                            setupCityItems();
                        }
                    }.bind(this)
                })
                clearInterval(interval);
            }, 800);
        })
    }

    $("#formofcity").submit(function (event) {
        let search_input = document.getElementById('city-search');
        BX.ajax({
            url: '/local/templates/Oshisha/geolocation/location_ajax.php',
            method: 'POST',
            data: {
                action: 'locationsListSubmit',
                searchCode: search_input.getAttribute('chosen-city-code'),
                searchText: search_input.value
            },
            onsuccess: function (result) {
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

    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.target.classList.contains('show')) {
                if (BX.localStorage.get('city_choosers_locations_array')) {
                    if (!document.getElementById('locations').innerHTML) {
                        setLocationsListContent()
                    }
                } else {
                    setCitiesList();
                }
            }
        });
    });

    observer.observe(document.getElementById('placeModal'), {
        attributes: true,
        attributeFilter: ['class']
    });

    function setCitiesList() {
        BX.ajax({
            url: '/local/templates/Oshisha/geolocation/location_ajax.php',
            method: 'POST',
            data: {
                action: 'setLocationsListStorage'
            },
            onsuccess: function (result) {
                if (result) {
                    BX.localStorage.set("city_choosers_locations_array", result, 86400)
                    if (!document.getElementById('locations').innerHTML) {
                        setLocationsListContent()
                    }
                }
            }.bind(this)
        })
    }
});