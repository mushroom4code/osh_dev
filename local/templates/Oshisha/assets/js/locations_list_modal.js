BX.ready(function () {
    function setupCityItems() {
        [...document.querySelectorAll('.city-item')].forEach(item => {
            item.addEventListener('click', onSelectCity);
        });
    }

    function onSelectCity (event) {
        const item = event.target;
        let city_selected = item.textContent;
        let city_search = document.getElementById('city-search');
        city_search.setAttribute('chosen-city-code', item.getAttribute('data-city-code'));
        city_search.value = city_selected;
        document.getElementById('choose-city-btn').removeAttribute('disabled');
    }
    function setLocationsListContent() {
        document.getElementById('locations').innerHTML = `<input id="city-search" class="form-control search" 
                                           type="text" name="cityother"
                                           placeholder="Ваш город ..." value=""
                                           autocomplete="off" required>
                                           <div class="cities-list-wrap mb-3">
                                                 <ul id="cities-list"></ul>
                                           </div>`
        showCitiesList([])

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

                            const locations = JSON.parse(result)
                            showCitiesList(locations)

                        }
                    }.bind(this)
                })
                clearInterval(interval);
            }, 800);
        })
    }

    function showCitiesList (locations) {
        let cities_list = '';
        let search = document.getElementById('city-search').value;

        let currentLocations = locations;
        if (search === '') {
            const locations_array = BX.localStorage.get('city_choosers_locations_array');

            currentLocations = [];
            const city = ['Москва', 'Санкт-Петербург', 'Нижний Новгород',
                'Екатеринбург', 'Пермь', 'Новосибирск', 'Казань'];
            city.forEach(item => {
                const location = locations_array.find(element => element.name === item)
                if (location !== undefined) {
                    currentLocations.push(location)
                }
            })
        }
        currentLocations.forEach((element) => {
            cities_list += `<li>
                    <span class="city-item" data-city-code="${element.code}">${element.name}</span>
                </li>`

        });
        document.getElementById('cities-list').innerHTML = cities_list;
        setupCityItems();
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
                    if (JSON.parse(result)['status'] === 'success') {
                        location.reload();
                    } else {
                        console.log(result);
                    }
                }
            }.bind(this)
        });

        event.preventDefault();
    });

    const observer = new MutationObserver(function (mutations) {
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