window.Osh = window.Osh || {};

window.Osh.oshMkadDistance = (function () {
    let instance;

    return {
        getInstance: function () {
            return instance;
        },
        init: function (param) {
            BX.addClass(BX('saveBTN'), 'popup-window-button-disable');
            if (instance === undefined) {
                instance = new window.Osh.oshMkadDistanceObject(param);
            }
            return instance;
        }
    };
}());

window.Osh.oshMkadDistanceObject = function oshMkadDistanceObject(param) {
    var $ = $ || jQuery;
    var selfObj = this;
    selfObj.isInited = false;
    selfObj.configuredPromise = false;
    selfObj.mobile_api_cache = {};
    selfObj.oUrls = {
        selectPVZ: "/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajaxOshishaPricePVZ.php",
        setPriceDelivery: "/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajaxOshishaSetPricePVZ.php",
    };
    selfObj.afterSave = null;

    var mkad_poly = null,
        msk_center_point = [55.75119082121071, 37.61699737548825],
        msk_250km_boundedBy = [[53.45159731566762, 33.63312692442719], [57.938891029311215, 41.58884573182265]],
        myMap = null,

        //Для отображения на яндекс карты полигона МКАД
        mkad_points = [[55.77682929150693, 37.8427186924053], [55.77271261339107, 37.843152686304705],
            [55.738276896644805, 37.84134161820584], [55.71399689835854, 37.83813880871875],
            [55.699921267680175, 37.83078428272048], [55.6962950504132, 37.82954151435689],
            [55.6928207993758, 37.82931794772561], [55.6892209716432, 37.829854389528585],
            [55.66165146026852, 37.83966290527148], [55.658376283618054, 37.8394483285503],
            [55.65605007409182, 37.838791290011436], [55.6531141363056, 37.8370746762419],
            [55.65145113826342, 37.83568956934368], [55.64812656859308, 37.8314409502641],
            [55.644824797922006, 37.82628977266418], [55.625585595616016, 37.79678983996685],
            [55.62124956968963, 37.78912615774818], [55.60391627214637, 37.75711862597196],
            [55.59919459324873, 37.74706053825473], [55.59180719241245, 37.72946947797549],
            [55.588836348363664, 37.7225364780563], [55.575884202346515, 37.68793829096614],
            [55.57326575851499, 37.679926824757885], [55.57229316496271, 37.67458386440024],
            [55.571916278457984, 37.66924090404256], [55.57203486325925, 37.66469310778763],
            [55.576012618166274, 37.59661654265479], [55.576997275315456, 37.58977417112674],
            [55.593461027106216, 37.52076943829923], [55.5950406236937, 37.51480420545011],
            [55.59619490389248, 37.51175721600919], [55.597166902872914, 37.509675821813644],
            [55.59866130413232, 37.50692923978237], [55.59992481831982, 37.505169710668625],
            [55.60066420884299, 37.50419141558768], [55.61116763612223, 37.491928885586624],
            [55.638875974823236, 37.459586882490854], [55.659861822998046, 37.43484779763937],
            [55.66403637567329, 37.43088149929608], [55.68274170580392, 37.41690766704496],
            [55.68445104083821, 37.41598498714383], [55.68864009415873, 37.41437258409716],
            [55.69086356292832, 37.41284823307507], [55.69271798296722, 37.41115307697766],
            [55.694411609835676, 37.40906103948314], [55.69633857479258, 37.40646466115671],
            [55.70821582138647, 37.39042283284293], [55.709960382334486, 37.388470184680074],
            [55.71100223559, 37.387526047106846], [55.714297215701556, 37.38550902592765],
            [55.74299678995391, 37.37085040270776], [55.74737891548303, 37.3693383084583],
            [55.749835763080554, 37.36897352803228], [55.78212184948561, 37.36975523402037],
            [55.78471424142089, 37.370104443868414], [55.7865400068638, 37.370812547048324],
            [55.789647237893845, 37.37287248357179], [55.80029924148098, 37.38296043585071],
            [55.804902293956964, 37.38656302639442], [55.80873309836682, 37.38838692852456],
            [55.83469933158447, 37.39616684582014], [55.838100191970035, 37.39588770506112],
            [55.84068411346117, 37.394943567487864], [55.844347068377, 37.39240249367216],
            [55.84601308639975, 37.391908967213396], [55.847449667553015, 37.39193042488553],
            [55.84921212285334, 37.39242395134426], [55.85763645302826, 37.39690455309926],
            [55.860737839006916, 37.39879032715197], [55.862584159418496, 37.40035673721667],
            [55.864949251589444, 37.40273853882189], [55.86706126571094, 37.40537841047629],
            [55.869498474258364, 37.40936953749045], [55.871054829060206, 37.412373611587114],
            [55.87204410730281, 37.41473395552023], [55.87320337129219, 37.41764120434771],
            [55.875543687912774, 37.424979728212456], [55.8813305362832, 37.44392953059815],
            [55.88207002762898, 37.44778576813208], [55.882588650864065, 37.452763948063726],
            [55.88275750343904, 37.46081057510839], [55.88292635527642, 37.464286717991705],
            [55.883384663688354, 37.46735516510474], [55.88551934442368, 37.47628155670629],
            [55.888075982000466, 37.48647395096288], [55.88926982558072, 37.49010029755102],
            [55.89215178082288, 37.496623429875235], [55.904441104424826, 37.52475156556294],
            [55.90586346265124, 37.529643914806094], [55.90676747666915, 37.53442897568867],
            [55.90726166205295, 37.538141152965274], [55.910865408147124, 37.57275237809345],
            [55.911022085130945, 37.57652892838642], [55.91097387689595, 37.579554460155215],
            [55.91063641756565, 37.58356704484148], [55.90998559481434, 37.587579629527774],
            [55.9092021825094, 37.5910986877553], [55.90847901858254, 37.593480489360545],
            [55.901901172883115, 37.6180182383294], [55.89891144249577, 37.63301715114069],
            [55.89687395332799, 37.64762982585381], [55.89576474245468, 37.659367172502996],
            [55.89456572248885, 37.69416117435827], [55.89393874366838, 37.699139354289926],
            [55.89328763950915, 37.70195030933754], [55.89247977280019, 37.70471834904089],
            [55.89140661030458, 37.70757221943274], [55.880130573679516, 37.73042464023962],
            [55.8304865952908, 37.8268977445699], [55.829001074066674, 37.82968724194538],
            [55.82757588633297, 37.831725720796705], [55.82488607061184, 37.834775327717445],
            [55.822361493423664, 37.836706518208175], [55.82024748644772, 37.8376291981093],
            [55.816165064041414, 37.83857287182817], [55.81242284003345, 37.83903585464755],
            [55.803139424516395, 37.839775801016756], [55.77682929150693, 37.8427186924053]],
        //Координаты сеъздов со мкада для расчета стоиомости доставки
        b_junctions = [[55.77682626803085, 37.84269989967345], [55.76903191638017, 37.84318651588698],
            [55.74392477931212, 37.84185519957153], [55.73052122580085, 37.84037898416108],
            [55.71863531207276, 37.83895012458452], [55.711831272333605, 37.83713368900962],
            [55.707901422046966, 37.8350106548768], [55.6869523798766, 37.83057993978087],
            [55.65692789667629, 37.83910426510268], [55.640528720308474, 37.819652386266085],
            [55.617789410062215, 37.782276430404394], [55.59175631830074, 37.72929474857808],
            [55.57581125568298, 37.687799514747375], [55.57272629492449, 37.65277241112271],
            [55.57605719591829, 37.59643530860042], [55.58106457666858, 37.57265144016032],
            [55.59150701569656, 37.52902190629794], [55.61120819157864, 37.49189413873337],
            [55.638972144200956, 37.45948542596951], [55.66189360804507, 37.432824164364256],
            [55.68278581583797, 37.416807425418966], [55.668026850906536, 37.42778473861195],
            [55.70188946767468, 37.39895204348993], [55.713602586285944, 37.38589295731531],
            [55.72348037785042, 37.38078139017449], [55.73175585229489, 37.37657178200628],
            [55.76508406345848, 37.36928736556715], [55.76996256764349, 37.36942982797446],
            [55.789736950483615, 37.3728868615282], [55.808798087528174, 37.388344151047676],
            [55.83260998737753, 37.39560097816893], [55.851747102850375, 37.39376480087579],
            [55.87090570963696, 37.41209100527676], [55.87659696295345, 37.42839459978549],
            [55.88161130650381, 37.445221243317135], [55.88711708090231, 37.482644383447834],
            [55.89207427475143, 37.49649435563702], [55.90782224163112, 37.54371914983502],
            [55.90978840669936, 37.58858112800599], [55.89518876022445, 37.67325996719509],
            [55.82959228057486, 37.82861019557688], [55.8822323534685, 37.72592724800108],
            [55.8138082895938, 37.83884777073161]],
        //Координаты сеъздов со мкада для расчета стоиомости доставки
        s_junctions = [[55.75481214376632, 37.84267307758329], [55.70418787329251, 37.8332852107992],
            [55.702989401989484, 37.83263932754], [55.65047653581307, 37.83493949978359],
            [55.64502320468091, 37.82690675054945], [55.62614603220174, 37.798215117726585],
            [55.59582667642601, 37.73945441049923], [55.587464115886156, 37.71946951925047],
            [55.58141301775248, 37.70325579370606], [55.57362538548569, 37.63521054231301],
            [55.57456040522403, 37.619314897938175], [55.58056831268785, 37.573856505131964],
            [55.58749528969654, 37.5451094875984], [55.593784581287494, 37.51884952838902],
            [55.60589190143268, 37.49776326563821], [55.61577037337298, 37.48617693805733],
            [55.62588555827154, 37.47443845687327], [55.63159809915896, 37.46778063484318],
            [55.65207693603693, 37.4436689941094], [55.65663799228618, 37.43816060545844],
            [55.66590855944432, 37.42912931533752], [55.68849971417, 37.4141437197791],
            [55.707656747292155, 37.39082356976081], [55.70992858606593, 37.38822422159842],
            [55.75188787932283, 37.366333001041205], [55.79604144033229, 37.37852370112031],
            [55.81331234523823, 37.38954092451], [55.81568484607161, 37.390191395766784],
            [55.82131114715086, 37.391900629017584], [55.825072975139875, 37.393084859162826],
            [55.830495842317646, 37.39451898008863], [55.8339338725267, 37.39594735722236],
            [55.85865656090271, 37.397073365517734], [55.86699779674642, 37.40492948497198],
            [55.87821893534327, 37.43308640028372], [55.88949415675149, 37.48972351315925],
            [55.90681458164319, 37.53369071576891], [55.910830265189425, 37.57059586873433],
            [55.911011046432726, 37.581529228009686], [55.89964948588706, 37.629701188337705],
            [55.895716922397085, 37.66346711671403], [55.89505379117015, 37.68453970149422],
            [55.894105661911894, 37.699083186567655], [55.89178148825972, 37.70718435431336],
            [55.87839320587734, 37.734177892950065], [55.82543390489343, 37.83464260085545],
            [55.81012946042399, 37.83951226232321], [55.80418173177062, 37.83998433110984],
            [55.802423269353746, 37.840209636667076], [55.90738403567146, 37.5979956303702]],
        collection = null,
        bjGq = null,
        jGq = null,
        searchControl = true,
        html_map_id = 'map',
        balloon = null,
        apikey = param.YA_API_KEY ?? '',
        cost = parseFloat(param.START_COST),
        costKm = parseFloat(param.DELIVERY_COST),
        limitBasket = parseFloat(param.LIMIT_BASKET),
        currentBasket = parseFloat(param.CURRENT_BASKET),
        distKm = 0,
        is_mobile_api = true,
        delivery_address,
        delivery_price;

    if (!apikey) {
        console.log('Ошибка, отсутствует API ключ яндекс!');
        alert('Ошибка подключения к карте!');
        return false;
    }

    selfObj.getDistanceCache = {};

    selfObj.init = function () {
        if (myMap!=null) {
            return
        }

        myMap = new ymaps.Map(html_map_id, {
            center: msk_center_point,
            zoom: 9,
            controls: ["zoomControl", "typeSelector"]
        });

        selfObj.initCircleControl();
        selfObj.initGeoLocationControl();
        selfObj.initSearchControls();
        selfObj.prepareData();

        if (is_mobile_api) {
            $('#adr').on('change', function () {
                $('#rez').val('{"loading:true"}');
                var mkad_address = ($(this).val()).replace(/\s+/g, ' ');
                if (typeof (selfObj.mobile_api_cache[mkad_address]) == 'undefined') {
                    selfObj.update_order_form(mkad_address, function (calculatedObj) {
                        selfObj.mobile_api_cache[mkad_address] = calculatedObj;
                        $('#rez').val(JSON.stringify(selfObj.mobile_api_cache[mkad_address]));
                    });
                } else {
                    $('#rez').val(JSON.stringify(selfObj.mobile_api_cache[mkad_address]));
                }
            });

        }

        selfObj.configuredPromise.resolve('config end');
    };

    selfObj.initCircleControl = function (){
        let myCircle = new ymaps.Circle([myMap.getCenter(), 100000], {}, {
            opacity: 0,
            penBalloonOnClick: false
        });
        myCircle.events.add('click', function (e) {
            selfObj.showByGeo(e.get('coords'));
        });
        myMap.geoObjects.add(myCircle);
    };

    selfObj.initGeoLocationControl = function (){
        let geolocationControl = new ymaps.control.GeolocationControl({
            options: {noPlacemark: true}
        });
        geolocationControl.events.add('locationchange', function (event) {
            selfObj.showByGeo(event.get('position'));
        });
        myMap.controls.add(geolocationControl);
    };

    selfObj.initSearchControls = function () {
        var opts = {
            placeholderContent: "Введите адрес доставки",
            noPlacemark: true,
            kind: 'house',
            strictBounds: true,
            provider: 'yandex#map',
            results: 1,
            maxWidth: [50, 90, 650],
            fitMaxWidth: true
        };

        opts['boundedBy'] = msk_250km_boundedBy;

        var d = new ymaps.control.SearchControl({options: opts});
        searchControl = d;
        myMap.controls.add(d);
        var b = new ymaps.GeoObjectCollection(null, {
            hintContentLayout: ymaps.templateLayoutFactory.createClass("$[properties.name]")
        });
        myMap.geoObjects.add(b);
        d.events.add("resultselect", function (a) {
            a = a.get("index");

            d.getResult(a).then(function (e) {
                selfObj.getDistance(e.geometry.getCoordinates());
                b.add(e);
            });
        }).add("submit", function () {
            b.removeAll();
        });
    };

    selfObj.showByGeo = function (selectGeo){
        let address = selfObj.getAddress(selectGeo);
        if (address) {
            searchControl.search(address);
        }
    };

    selfObj.show = function (){
        if (delivery_address) {
            searchControl.search(delivery_address);
        }
    };

    selfObj.setMobileApiState = function (status) {
        is_mobile_api = (!!status);
    };

    selfObj.setDisabled = function () {
        BX.addClass(BX('saveBTN'), "popup-window-button-disable");
    }

    selfObj.removeDisabled = function () {
        setTimeout(function () {
            BX.removeClass(BX('saveBTN'), "popup-window-button-disable");
        }, 800)
    };

    /**
     * Рассчитать тип доставки (за мкад, внутри мкад) и расстояние от мкад
     * @param selectGeo - координаты точки для расчета
     * @param saveDelivery
     */
    selfObj.getDistance = function (selectGeo, saveDelivery=false) {
        collection.removeAll();

        selectGeo[0] = Number('' + selectGeo[0]).toPrecision(6);
        selectGeo[1] = Number('' + selectGeo[1]).toPrecision(6);

        // Местоположение
        delivery_address = selfObj.getAddress(selectGeo);
        selfObj.setDisabled();

        if (selfObj.getDistanceCache[selectGeo] !== undefined) {
            selfObj.showResults(selfObj.getDistanceCache[selectGeo], selectGeo, delivery_address, saveDelivery);
            return;
        } else {
            selfObj.getDistanceCache[selectGeo] = {inMkad: 0, geometry: undefined}
        }

        selfObj.getDistanceCache[selectGeo].inMkad = selfObj.checkIn(selectGeo);
        if (selfObj.getDistanceCache[selectGeo].inMkad) {
            selfObj.showResults(selfObj.getDistanceCache[selectGeo], selectGeo, delivery_address, saveDelivery);
        } else {
            balloon.close();

            if (typeof (selfObj.getDistanceCache[selectGeo].geometry) == 'undefined') {
                var b = [];
                selfObj.routeFromCenter(selectGeo, function (c, e) {
                    b.push(e[0]);
                    var f = selfObj.findNearest(jGq, selectGeo, 1),
                        a = 3500 > selfObj.getPointDistance(f[0], selectGeo) ? selfObj.findNearest(jGq, selectGeo, 7)
                            : selfObj.findNearest(jGq, selectGeo, 5);
                    a.forEach(function (g) {
                        g[0] == e[0][0] && g[1] == e[0][1] || b.push(g);
                    });

                    async.map(b, function (g, i) {
                        selfObj.getRoute(g, selectGeo, i);
                    }, function (i, j) {
                        let g = j.map(function (h) {
                            return h.getLength();
                        });

                        g = selfObj.indexOfSmallest(g);

                        selectGeo[0] = Number('' + selectGeo[0]).toPrecision(6);
                        selectGeo[1] = Number('' + selectGeo[1]).toPrecision(6);

                        selfObj.getDistanceCache[selectGeo].geometry = j[g];
                        selfObj.showResults(selfObj.getDistanceCache[selectGeo], selectGeo, delivery_address, saveDelivery);
                    });
                });
            }
        }
        selfObj.removeDisabled();
    };

    /**
     * Отправляет результаты расчета доставки
     */
    selfObj.saveDelivery = function () {
        var sessid = BX.bitrix_sessid();
        BX.ajax.post(selfObj.oUrls.setPriceDelivery, {
            address: delivery_address,
            price: delivery_price,
            distance: distKm,
            sessid: sessid
        }, function () {
            if (selfObj.afterSave!=null) {
                selfObj.afterSave(delivery_address);
            }
            BX.onCustomEvent('onDeliveryExtraServiceValueChange');
        });
    };

    selfObj.getAddress = function (geocode) {

        let address = '';
        geocode[0] = geocode[0].length > 8 ? geocode[0].toFixed(6) : geocode[0];
        geocode[1] = geocode[1].length > 8 ? geocode[1].toFixed(6) : geocode[1];

        $.ajax({
            type: "GET",
            url: "https://geocode-maps.yandex.ru/1.x/",
            data: 'geocode=' + geocode[1] + ',' + geocode[0] + '&format=json&&results=1&apikey=' + apikey,
            dataType: "JSON", timeout: 30000, async: false,
            error: function (xhr) {
                address = 'Ошибка геокодирования: ' + xhr.status + ' ' + xhr.statusText;
            },
            success: function (html) {
                res = html;
                var geores = res.response.GeoObjectCollection.featureMember;
                if (geores.length > 0) {
                    address = geores[0].GeoObject.description + ', ' + geores[0].GeoObject.name;
                } else {
                    alert('Ошибка при расчетах доставки.');
                }
            }
        });

        return address;

    };

    selfObj.checkIn = function (d) {
        d = new ymaps.Placemark(d);

        var b = ymaps.geoQuery(d).setOptions("visible", 0).addToMap(myMap).searchInside(mkad_poly).getLength();
        myMap.geoObjects.remove(d);
        return b;
    };

    selfObj.routeFromCenter = function (d, b) {

        selfObj.getRoute(msk_center_point, d, function (h, route) {
            var g = [];
            ymaps.geoQuery(route.getPaths()).each(function (i) {
                i = i.geometry.getCoordinates();
                if (i.length < 3000)
                    for (var k = 1, j = i.length; k < j; k++) {
                        g.push({
                            type: "LineString",
                            coordinates: [i[k], i[k - 1]]
                        })
                    }
            });
            var a = ymaps.geoQuery(g).setOptions("visible", 0).addToMap(myMap),
                e = a.searchInside(mkad_poly),
                e = (g.length ? a.remove(e).get(0).geometry.getCoordinates()[1] : []);
            a.removeFromMap(myMap);
            a = selfObj.findNearest(jGq, e, 1);
            b(null, a);
        })
    };

    selfObj.getRoute = function (e, b, d) {
        var opts = {boundedBy: msk_250km_boundedBy, routingMode: 'auto', strictBounds: true, multiRoute: false};
        ymaps.route([e, b], opts).done(function (c) {
            d(null, c);
        })
    };

    selfObj.findNearest = function (f, b, d) {
        f = f.sortByDistance(b);
        b = [];
        for (var e = 0; e < d; e++) {
            b.push(f.get(e).geometry.getCoordinates());
        }
        return b;
    };

    selfObj.getPointDistance = function (d, b) {
        return myMap.options.get("projection").getCoordSystem().getDistance(d, b);
    };

    selfObj.indexOfSmallest = function (a) {
        return a.indexOf(Math.min.apply(Math, a));
    };

    selfObj.showText = function (str) {
        $('#osh_delivery_ya_map_address').val(str)
    };

    selfObj.calculateCost = function (dist) {
        const dist_m = Math.ceil(dist-0.8);
        return currentBasket>=limitBasket ? Math.max(dist_m - 5, 0) * costKm : cost + dist_m * costKm;
    };

    selfObj.showResults = function (result, d, delivery_address = '', saveDelivery=false) {

        if (result.inMkad) {
            distKm = 0
            delivery_price = this.calculateCost(0);
            let str = `В пределах МКАД - ${delivery_price}.`;
            selfObj.showText(delivery_address);

            balloon.open(d, str);
        } else {
            var i = result.geometry;
            distKm = i.getLength() / 1000;
            delivery_price = this.calculateCost(distKm);

            let g, b;
            i.getPaths().options.set({
                strokeColor: "F55F5C"
            });
            i.getWayPoints().each(function (c) {
                if ("1" == c.properties.get("iconContent") || "МКАД" == c.properties.get("iconContent")) {
                    c.properties.set("iconContent", "МКАД");
                    c.options.set("preset", "islands#redStretchyIcon");
                    b = c.geometry.getCoordinates();
                    c.properties.set("balloonContent", "");
                } else {
                    c.options.set("preset", "islands#redStretchyIcon");

                    c.properties.set("iconContent", '' + distKm.toFixed(1) + ' км, '
                        + delivery_price.toFixed() + ' руб');

                    g = c.geometry.getCoordinates();
                    c.properties.set("balloonContent", "");

                    selfObj.showText(delivery_address);
                }
            });

            collection.add(i);
        }

        if (saveDelivery){
            selfObj.saveDelivery();
        }
    };

    selfObj.prepareData = function () {
        mkad_poly = new ymaps.Polygon([mkad_points], {}, {
            // цвет заливки.
            fillColor: 'rgba(255,94,89,0.12)',
            // цвет обводки.
            strokeColor: 'rgba(255,94,89,0.22)',
            // Прозрачность.
            opacity: 1,
            // ширина обводки.
            strokeWidth: 0.1,
            zIndex: -999,
            zIndexActive: -999,
        });

        balloon = new ymaps.Balloon(myMap, {
            closeButton: false
        });
        balloon.options.setParent(myMap.options);

        collection = new ymaps.GeoObjectCollection({});
        myMap.geoObjects.add(collection);

        ymaps.geoQuery(mkad_poly).addToMap(myMap);

        let d = new ymaps.GeoObjectCollection({}),
            b = new ymaps.GeoObjectCollection({});

        b_junctions.forEach(function (a) {
            d.add(new ymaps.Placemark(a));
            b.add(new ymaps.Placemark(a));
        });
        s_junctions.forEach(function (a) {
            b.add(new ymaps.Placemark(a));
        });

        bjGq = ymaps.geoQuery(d).setOptions("visible", 0).addToMap(myMap);
        jGq = ymaps.geoQuery(b).setOptions("visible", 0).addToMap(myMap);
        mkad_points = b = d = s_junctions = b_junctions = null;
    };

    selfObj.update_order_form = function (user_address, callback_func) {

        ///is_update_order_form=function(obj) { callback_func(obj); is_update_order_form=false; };

        var goSearch = function () {
            selfObj.configuredPromise = true;

            if (!searchControl) {
                callback_func({'km': 0, 'cost': 0, 'is_msk': 0});
                return false;
            }
            searchControl.clear();
            if (user_address.length > 1) {

                searchControl.search('Россия ' + user_address.replace(/^\s*Росс?ия/i, '')).then(function () {

                    var geoObjectsArray = searchControl.getResultsArray();
                    if (geoObjectsArray.length) {
                        selfObj.getDistance(geoObjectsArray[0].geometry.getCoordinates(), callback_func);
                    } else {
                        callback_func({'km': 0, 'cost': 0, 'is_msk': 0});
                    }
                }, function () {

                    callback_func({'km': 0, 'cost': 0, 'is_msk': 0});
                });
            } else {
                callback_func({'km': 0, 'cost': 0, 'is_msk': 0});
            }
        };

        if (typeof (selfObj.configuredPromise) == 'object') {
            selfObj.configuredPromise.done(goSearch);
        } else if (!selfObj.configuredPromise) {
            setTimeout(function () {
                selfObj.update_order_form(user_address, callback_func);
            }, 1000);
        } else {
            goSearch();
        }

    };

    selfObj.initWObj = function () {

        if (!selfObj.configuredPromise) {
            selfObj.configuredPromise = $.Deferred();
        }
        if (!selfObj.isInited) {
            if (typeof ymaps === "undefined" )
                return null;

            ymaps.ready(selfObj.init);
            selfObj.isInited = true;


        }
        return selfObj;
    };

    return selfObj.initWObj();
};

window.Osh.Map = {
    instance: null,
    arMarkers: null,
    isMobile: false,
    arIcons: {
        osh: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMDBweCIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PHBhdGggZmlsbD0iI0JBMDAyMiIgZD0iTTUwLDNjMjUuOTE2LDAsNDcsMjEuMDg0LDQ3LDQ3Uzc1LjkxNiw5Nyw1MCw5N1MzLDc1LjkxNiwzLDUwUzI0LjA4NCwzLDUwLDMgTTUwLDBDMjIuMzg2LDAsMCwyMi4zODYsMCw1MHMyMi4zODYsNTAsNTAsNTBzNTAtMjIuMzg2LDUwLTUwUzc3LjYxNCwwLDUwLDBMNTAsMHoiLz48L2c+PHBvbHlnb24gZmlsbD0iI0I5MEYyRiIgcG9pbnRzPSI4NS43MjksMzYuMDk3IDI2LjI4NywzNi4wNzQgMzcuMDE1LDUxLjYzMyAzNy4wMTUsNTEuNjMzICIvPjxwb2x5Z29uIGZpbGw9IiNFRTc5MDAiIHBvaW50cz0iMjguOTE4LDU0LjIxMSAyOC44ODMsNzcuMTI1IDM2Ljk5OCw3MS4yNzEgMzcuMDE1LDUxLjYzMyAiLz48cG9seWdvbiBmaWxsPSIjRjVBMzAwIiBwb2ludHM9IjI2LjI4NywzNi4wNzQgMTUuOTg3LDM2LjA3MSAyOC45MTgsNTQuMjExIDM3LjAxNSw1MS42MzMgMzcuMDE1LDUxLjYzMyAiLz48cG9seWdvbiBmaWxsPSIjODQyMjFDIiBwb2ludHM9IjM3LjAxNSw1MS42MzMgMzcuMDE1LDUxLjYzMyAzNi45OTgsNzEuMjcxIDg1LjcyOSwzNi4wOTcgIi8+PC9zdmc+',
        hermes: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjxzdmcKICAgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIgogICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIgogICB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9zb2RpcG9kaS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIgogICB4bWxuczppbmtzY2FwZT0iaHR0cDovL3d3dy5pbmtzY2FwZS5vcmcvbmFtZXNwYWNlcy9pbmtzY2FwZSIKICAgc29kaXBvZGk6ZG9jbmFtZT0iaGVybWVzMi5zdmciCiAgIGlua3NjYXBlOnZlcnNpb249IjEuMCAoNDAzNWE0ZmI0OSwgMjAyMC0wNS0wMSkiCiAgIGlkPSJzdmc5MTEiCiAgIHZlcnNpb249IjEuMSIKICAgdmlld0JveD0iMCAwIDI2LjQ1ODMzMyAyNi40NTgzMzQiCiAgIGhlaWdodD0iMTAwIgogICB3aWR0aD0iMTAwIj4KICA8ZGVmcwogICAgIGlkPSJkZWZzOTA1Ij4KICAgIDxsaW5lYXJHcmFkaWVudAogICAgICAgZ3JhZGllbnRUcmFuc2Zvcm09InRyYW5zbGF0ZSgtOTAuNDEwMjEyLC00OC40OTc5NSkiCiAgICAgICBpZD0iYSIKICAgICAgIHgxPSIxNTkuMDYiCiAgICAgICB4Mj0iMTU3LjQ5MDAxIgogICAgICAgeTE9IjkxLjgyIgogICAgICAgeTI9IjgyLjkxOTk5OCIKICAgICAgIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIj4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIwIgogICAgICAgICBzdG9wLWNvbG9yPSIjNjM2MzY5IgogICAgICAgICBpZD0ic3RvcDIiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjQ2IgogICAgICAgICBzdG9wLWNvbG9yPSIjYTlhOWFlIgogICAgICAgICBpZD0ic3RvcDQiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjgyIgogICAgICAgICBzdG9wLWNvbG9yPSIjZDlkOWRlIgogICAgICAgICBpZD0ic3RvcDYiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iMSIKICAgICAgICAgc3RvcC1jb2xvcj0iI2ViZWJmMCIKICAgICAgICAgaWQ9InN0b3A4IiAvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICAgIDxsaW5lYXJHcmFkaWVudAogICAgICAgZ3JhZGllbnRUcmFuc2Zvcm09InRyYW5zbGF0ZSgtOTAuNDEwMjEyLC00OC40OTc5NSkiCiAgICAgICBpZD0iYiIKICAgICAgIHgxPSIxMjIuNTgiCiAgICAgICB4Mj0iMTQ3LjgxIgogICAgICAgeTE9Ijk1LjYyMDAwMyIKICAgICAgIHkyPSI3NC40MTk5OTgiCiAgICAgICBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iMCIKICAgICAgICAgc3RvcC1jb2xvcj0iI2U2ZjBmNSIKICAgICAgICAgaWQ9InN0b3AxMSIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuMDYiCiAgICAgICAgIHN0b3AtY29sb3I9IiNjYmUzZWYiCiAgICAgICAgIGlkPSJzdG9wMTMiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjIiCiAgICAgICAgIHN0b3AtY29sb3I9IiM5NmNkZTMiCiAgICAgICAgIGlkPSJzdG9wMTUiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjM0IgogICAgICAgICBzdG9wLWNvbG9yPSIjNjhiOWRhIgogICAgICAgICBpZD0ic3RvcDE3IiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9Ii40NyIKICAgICAgICAgc3RvcC1jb2xvcj0iIzQxYTlkMSIKICAgICAgICAgaWQ9InN0b3AxOSIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuNjEiCiAgICAgICAgIHN0b3AtY29sb3I9IiMyNTljY2IiCiAgICAgICAgIGlkPSJzdG9wMjEiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjc0IgogICAgICAgICBzdG9wLWNvbG9yPSIjMTE5M2M3IgogICAgICAgICBpZD0ic3RvcDIzIiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9Ii44NyIKICAgICAgICAgc3RvcC1jb2xvcj0iIzA0OGVjMyIKICAgICAgICAgaWQ9InN0b3AyNSIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIxIgogICAgICAgICBzdG9wLWNvbG9yPSIjMDA4Y2MzIgogICAgICAgICBpZD0ic3RvcDI3IiAvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICAgIDxsaW5lYXJHcmFkaWVudAogICAgICAgZ3JhZGllbnRUcmFuc2Zvcm09InRyYW5zbGF0ZSgtOTAuNDEwMjEyLC00OC40OTc5NSkiCiAgICAgICBpZD0iYyIKICAgICAgIHgxPSIxMDkuNjMiCiAgICAgICB4Mj0iMTE5Ljg2IgogICAgICAgeTE9IjgxLjk0MDAwMiIKICAgICAgIHkyPSI5Mi41NDAwMDEiCiAgICAgICBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iMCIKICAgICAgICAgc3RvcC1jb2xvcj0iI2U2ZjBmNSIKICAgICAgICAgaWQ9InN0b3AzMCIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuMDkiCiAgICAgICAgIHN0b3AtY29sb3I9IiNkZGVjZjMiCiAgICAgICAgIGlkPSJzdG9wMzIiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjI0IgogICAgICAgICBzdG9wLWNvbG9yPSIjYzVlMWVlIgogICAgICAgICBpZD0ic3RvcDM0IiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9Ii40MyIKICAgICAgICAgc3RvcC1jb2xvcj0iIzlkZDBlNSIKICAgICAgICAgaWQ9InN0b3AzNiIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuNjUiCiAgICAgICAgIHN0b3AtY29sb3I9IiM2NmI4ZDkiCiAgICAgICAgIGlkPSJzdG9wMzgiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjkiCiAgICAgICAgIHN0b3AtY29sb3I9IiMyMDlhY2EiCiAgICAgICAgIGlkPSJzdG9wNDAiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iMSIKICAgICAgICAgc3RvcC1jb2xvcj0iIzAwOGNjMyIKICAgICAgICAgaWQ9InN0b3A0MiIgLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICA8bGluZWFyR3JhZGllbnQKICAgICAgIGdyYWRpZW50VHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTkwLjQxMDIxMiwtNDguNDk3OTUpIgogICAgICAgaWQ9ImQiCiAgICAgICB4MT0iMTU2LjIzIgogICAgICAgeDI9IjE1NC42NyIKICAgICAgIHkxPSIxMDIuOTgiCiAgICAgICB5Mj0iOTQuMDgwMDAyIgogICAgICAgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9IjAiCiAgICAgICAgIHN0b3AtY29sb3I9IiM2MzYzNjkiCiAgICAgICAgIGlkPSJzdG9wNDUiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjQ2IgogICAgICAgICBzdG9wLWNvbG9yPSIjYTlhOWFlIgogICAgICAgICBpZD0ic3RvcDQ3IiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9Ii44MiIKICAgICAgICAgc3RvcC1jb2xvcj0iI2Q5ZDlkZSIKICAgICAgICAgaWQ9InN0b3A0OSIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIxIgogICAgICAgICBzdG9wLWNvbG9yPSIjZWJlYmYwIgogICAgICAgICBpZD0ic3RvcDUxIiAvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICAgIDxsaW5lYXJHcmFkaWVudAogICAgICAgZ3JhZGllbnRUcmFuc2Zvcm09InRyYW5zbGF0ZSgtOTAuNDEwMjEyLC00OC40OTc5NSkiCiAgICAgICBpZD0iZSIKICAgICAgIHgxPSIxMjMuNSIKICAgICAgIHgyPSIxNTIuMiIKICAgICAgIHkxPSI5OC41MTAwMDIiCiAgICAgICB5Mj0iOTguNTEwMDAyIgogICAgICAgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9IjAiCiAgICAgICAgIHN0b3AtY29sb3I9IiNlNmYwZjUiCiAgICAgICAgIGlkPSJzdG9wNTQiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjExIgogICAgICAgICBzdG9wLWNvbG9yPSIjYzBkZWVhIgogICAgICAgICBpZD0ic3RvcDU2IiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9Ii4yOCIKICAgICAgICAgc3RvcC1jb2xvcj0iIzg2YzJkYSIKICAgICAgICAgaWQ9InN0b3A1OCIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuNDUiCiAgICAgICAgIHN0b3AtY29sb3I9IiM1NWFiY2MiCiAgICAgICAgIGlkPSJzdG9wNjAiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjYyIgogICAgICAgICBzdG9wLWNvbG9yPSIjMzA5OWMyIgogICAgICAgICBpZD0ic3RvcDYyIiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9Ii43NyIKICAgICAgICAgc3RvcC1jb2xvcj0iIzE2OGRiYSIKICAgICAgICAgaWQ9InN0b3A2NCIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuOSIKICAgICAgICAgc3RvcC1jb2xvcj0iIzA1ODViNiIKICAgICAgICAgaWQ9InN0b3A2NiIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIxIgogICAgICAgICBzdG9wLWNvbG9yPSIjMDA4MmIzIgogICAgICAgICBpZD0ic3RvcDY4IiAvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICAgIDxsaW5lYXJHcmFkaWVudAogICAgICAgZ3JhZGllbnRUcmFuc2Zvcm09InRyYW5zbGF0ZSgtOTAuNDEwMjEyLC00OC40OTc5NSkiCiAgICAgICBpZD0iZiIKICAgICAgIHgxPSIxMjEuOTciCiAgICAgICB4Mj0iMTMxLjYwMDAxIgogICAgICAgeTE9Ijk0LjAxOTk5NyIKICAgICAgIHkyPSIxMDMuMDEiCiAgICAgICBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iMCIKICAgICAgICAgc3RvcC1jb2xvcj0iI2U2ZjBmNSIKICAgICAgICAgaWQ9InN0b3A3MSIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuMTYiCiAgICAgICAgIHN0b3AtY29sb3I9IiNjN2UxZWMiCiAgICAgICAgIGlkPSJzdG9wNzMiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjUxIgogICAgICAgICBzdG9wLWNvbG9yPSIjNzdiYmQ2IgogICAgICAgICBpZD0ic3RvcDc1IiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9IjEiCiAgICAgICAgIHN0b3AtY29sb3I9IiMwMDgyYjMiCiAgICAgICAgIGlkPSJzdG9wNzciIC8+CiAgICA8L2xpbmVhckdyYWRpZW50PgogICAgPGxpbmVhckdyYWRpZW50CiAgICAgICBncmFkaWVudFRyYW5zZm9ybT0idHJhbnNsYXRlKC05MC40MTAyMTIsLTQ4LjQ5Nzk1KSIKICAgICAgIGlkPSJnIgogICAgICAgeDE9IjE1My4zOTk5OSIKICAgICAgIHgyPSIxNTEuODQiCiAgICAgICB5MT0iMTE0LjEyIgogICAgICAgeTI9IjEwNS4yMiIKICAgICAgIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIj4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIwIgogICAgICAgICBzdG9wLWNvbG9yPSIjNjM2MzY5IgogICAgICAgICBpZD0ic3RvcDgwIiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9Ii40NiIKICAgICAgICAgc3RvcC1jb2xvcj0iI2E5YTlhZSIKICAgICAgICAgaWQ9InN0b3A4MiIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuODIiCiAgICAgICAgIHN0b3AtY29sb3I9IiNkOWQ5ZGUiCiAgICAgICAgIGlkPSJzdG9wODQiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iMSIKICAgICAgICAgc3RvcC1jb2xvcj0iI2ViZWJmMCIKICAgICAgICAgaWQ9InN0b3A4NiIgLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICA8bGluZWFyR3JhZGllbnQKICAgICAgIGdyYWRpZW50VHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTkwLjQxMDIxMiwtNDguNDk3OTUpIgogICAgICAgaWQ9ImgiCiAgICAgICB4MT0iMTM0Ljg1MDAxIgogICAgICAgeDI9IjE0OC4yNDAwMSIKICAgICAgIHkxPSIxMDcuMDciCiAgICAgICB5Mj0iMTEwLjE2IgogICAgICAgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9IjAiCiAgICAgICAgIHN0b3AtY29sb3I9IiNlNmYwZjUiCiAgICAgICAgIGlkPSJzdG9wODkiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjEiCiAgICAgICAgIHN0b3AtY29sb3I9IiNjNmUwZWIiCiAgICAgICAgIGlkPSJzdG9wOTEiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjM1IgogICAgICAgICBzdG9wLWNvbG9yPSIjODBiZGQ2IgogICAgICAgICBpZD0ic3RvcDkzIiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9Ii41NyIKICAgICAgICAgc3RvcC1jb2xvcj0iIzQ5YTJjNSIKICAgICAgICAgaWQ9InN0b3A5NSIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuNzYiCiAgICAgICAgIHN0b3AtY29sb3I9IiMyMDhlYjkiCiAgICAgICAgIGlkPSJzdG9wOTciIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjkiCiAgICAgICAgIHN0b3AtY29sb3I9IiMwOTgxYjIiCiAgICAgICAgIGlkPSJzdG9wOTkiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjk5IgogICAgICAgICBzdG9wLWNvbG9yPSIjMDA3Y2FmIgogICAgICAgICBpZD0ic3RvcDEwMSIgLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICA8bGluZWFyR3JhZGllbnQKICAgICAgIGdyYWRpZW50VHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTkwLjQxMDIxMiwtNDguNDk3OTUpIgogICAgICAgaWQ9ImkiCiAgICAgICB4MT0iMTM0LjEzIgogICAgICAgeDI9IjE0NC4wNSIKICAgICAgIHkxPSIxMDUuNTQiCiAgICAgICB5Mj0iMTE0LjgxIgogICAgICAgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9IjAiCiAgICAgICAgIHN0b3AtY29sb3I9IiNlNmYwZjUiCiAgICAgICAgIGlkPSJzdG9wMTA0IiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9Ii4zMyIKICAgICAgICAgc3RvcC1jb2xvcj0iIzkwYzVkYiIKICAgICAgICAgaWQ9InN0b3AxMDYiIC8+CiAgICAgIDxzdG9wCiAgICAgICAgIG9mZnNldD0iLjY2IgogICAgICAgICBzdG9wLWNvbG9yPSIjNDM5ZWMzIgogICAgICAgICBpZD0ic3RvcDEwOCIgLz4KICAgICAgPHN0b3AKICAgICAgICAgb2Zmc2V0PSIuODkiCiAgICAgICAgIHN0b3AtY29sb3I9IiMxMzg2YjUiCiAgICAgICAgIGlkPSJzdG9wMTEwIiAvPgogICAgICA8c3RvcAogICAgICAgICBvZmZzZXQ9IjEiCiAgICAgICAgIHN0b3AtY29sb3I9IiMwMDdjYWYiCiAgICAgICAgIGlkPSJzdG9wMTEyIiAvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICA8L2RlZnM+CiAgPHNvZGlwb2RpOm5hbWVkdmlldwogICAgIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjEiCiAgICAgaW5rc2NhcGU6d2luZG93LXk9IjAiCiAgICAgaW5rc2NhcGU6d2luZG93LXg9IjAiCiAgICAgaW5rc2NhcGU6d2luZG93LWhlaWdodD0iMTEzNiIKICAgICBpbmtzY2FwZTp3aW5kb3ctd2lkdGg9IjE5MjAiCiAgICAgdW5pdHM9InB4IgogICAgIHNob3dncmlkPSJmYWxzZSIKICAgICBpbmtzY2FwZTpkb2N1bWVudC1yb3RhdGlvbj0iMCIKICAgICBpbmtzY2FwZTpjdXJyZW50LWxheWVyPSJsYXllcjEiCiAgICAgaW5rc2NhcGU6ZG9jdW1lbnQtdW5pdHM9Im1tIgogICAgIGlua3NjYXBlOmN5PSI0Ny40NDYzNjQiCiAgICAgaW5rc2NhcGU6Y3g9Ii02Mi4yOTkzNTYiCiAgICAgaW5rc2NhcGU6em9vbT0iMy4xNTcyMzE4IgogICAgIGlua3NjYXBlOnBhZ2VzaGFkb3c9IjIiCiAgICAgaW5rc2NhcGU6cGFnZW9wYWNpdHk9IjAuMCIKICAgICBib3JkZXJvcGFjaXR5PSIxLjAiCiAgICAgYm9yZGVyY29sb3I9IiM2NjY2NjYiCiAgICAgcGFnZWNvbG9yPSIjZmZmZmZmIgogICAgIGlkPSJiYXNlIiAvPgogIDxtZXRhZGF0YQogICAgIGlkPSJtZXRhZGF0YTkwOCI+CiAgICA8cmRmOlJERj4KICAgICAgPGNjOldvcmsKICAgICAgICAgcmRmOmFib3V0PSIiPgogICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2Uvc3ZnK3htbDwvZGM6Zm9ybWF0PgogICAgICAgIDxkYzp0eXBlCiAgICAgICAgICAgcmRmOnJlc291cmNlPSJodHRwOi8vcHVybC5vcmcvZGMvZGNtaXR5cGUvU3RpbGxJbWFnZSIgLz4KICAgICAgICA8ZGM6dGl0bGU+PC9kYzp0aXRsZT4KICAgICAgPC9jYzpXb3JrPgogICAgPC9yZGY6UkRGPgogIDwvbWV0YWRhdGE+CiAgPGcKICAgICBpZD0ibGF5ZXIxIgogICAgIGlua3NjYXBlOmdyb3VwbW9kZT0ibGF5ZXIiCiAgICAgaW5rc2NhcGU6bGFiZWw9ItCh0LvQvtC5IDEiPgogICAgPGNpcmNsZQogICAgICAgcj0iMTIuODg5OTM0IgogICAgICAgY3k9IjEzLjIyOTE2NyIKICAgICAgIGN4PSIxMy4yMjkxNjciCiAgICAgICBpZD0icGF0aDE0NzYiCiAgICAgICBzdHlsZT0iZmlsbDojZmZmZmZmO3N0cm9rZTojMWE4YmI3O3N0cm9rZS13aWR0aDowLjc5Mzc1O3N0cm9rZS1taXRlcmxpbWl0OjQ7c3Ryb2tlLWRhc2hhcnJheTpub25lO3N0cm9rZS1vcGFjaXR5OjEiIC8+CiAgICA8ZwogICAgICAgdHJhbnNmb3JtPSJtYXRyaXgoMC4zNzgwOTQyNCwwLDAsMC4zNzgwOTQyNCwtNC4zMTYyMTY0LC00LjYzNTAwMTMpIgogICAgICAgaWQ9Imc5MTQiPgogICAgICA8cGF0aAogICAgICAgICBpZD0icGF0aDExNyIKICAgICAgICAgZD0ibSA2OS4wODk3ODgsNDMuMTkyMDUgMi4xMywtOC42OSBoIC00LjcyIGwgLTIuMTMsOC42OSIKICAgICAgICAgZmlsbD0idXJsKCNhKSIKICAgICAgICAgc3R5bGU9ImZpbGw6dXJsKCNhKSIgLz4KICAgICAgPHBhdGgKICAgICAgICAgaWQ9InBhdGgxMTkiCiAgICAgICAgIGQ9Im0gNjIuMzg5Nzg4LDQzLjE5MjA1IDIuMTMsLTguNjkgaCAtNDIuOTMgbCA3LjQ2LDguNjkiCiAgICAgICAgIGZpbGw9InVybCgjYikiCiAgICAgICAgIHN0eWxlPSJmaWxsOnVybCgjYikiIC8+CiAgICAgIDxwYXRoCiAgICAgICAgIGlkPSJwYXRoMTIxIgogICAgICAgICBkPSJtIDI5LjA0OTc4OCw0My4xOTIwNSAtNy40NiwtOC42OSB2IDIuNzQgbCA2LjI0LDUuOTUiCiAgICAgICAgIGZpbGw9InVybCgjYykiCiAgICAgICAgIHN0eWxlPSJmaWxsOnVybCgjYykiIC8+CiAgICAgIDxwYXRoCiAgICAgICAgIGlkPSJwYXRoMTIzIgogICAgICAgICBkPSJtIDY2LjE4OTc4OCw1NC40NzIwNSAyLjMsLTguNjkgaCAtNC43MyBsIC0yLjI4LDguNjkiCiAgICAgICAgIGZpbGw9InVybCgjZCkiCiAgICAgICAgIHN0eWxlPSJmaWxsOnVybCgjZCkiIC8+CiAgICAgIDxwYXRoCiAgICAgICAgIGlkPSJwYXRoMTI1IgogICAgICAgICBkPSJtIDU5LjQ4OTc4OCw1NC4zMDIwNSAyLjMsLTguNjggaCAtMjguNjMgbCA3LjYxLDguNjkiCiAgICAgICAgIGZpbGw9InVybCgjZSkiCiAgICAgICAgIHN0eWxlPSJmaWxsOnVybCgjZSkiIC8+CiAgICAgIDxwYXRoCiAgICAgICAgIGlkPSJwYXRoMTI3IgogICAgICAgICBkPSJtIDQwLjc2OTc4OCw1NC4zMDIwNSAtNy42MSwtOC42OCB2IDIuNTkgbCA2LjU1LDYuMjUiCiAgICAgICAgIGZpbGw9InVybCgjZikiCiAgICAgICAgIHN0eWxlPSJmaWxsOnVybCgjZikiIC8+CiAgICAgIDxwYXRoCiAgICAgICAgIGlkPSJwYXRoMTI5IgogICAgICAgICBkPSJtIDYzLjQ1OTc4OCw2NS41OTIwNSAyLjI4LC04LjY5IGggLTQuNzIgbCAtMi4yOCw4LjY5IgogICAgICAgICBmaWxsPSJ1cmwoI2cpIgogICAgICAgICBzdHlsZT0iZmlsbDp1cmwoI2cpIiAvPgogICAgICA8cGF0aAogICAgICAgICBpZD0icGF0aDEzMSIKICAgICAgICAgZD0ibSA1Ni43NTk3ODgsNjUuNTkyMDUgMi4xMywtOC42OSBoIC0xNC4xNiBsIDcuNjEsOC42OSIKICAgICAgICAgZmlsbD0idXJsKCNoKSIKICAgICAgICAgc3R5bGU9ImZpbGw6dXJsKCNoKSIgLz4KICAgICAgPHBhdGgKICAgICAgICAgaWQ9InBhdGgxMzMiCiAgICAgICAgIGQ9Im0gNTIuNDg5Nzg4LDY1LjU5MjA1IC03LjYxLC04LjY5IHYgMi40NCBsIDYuNTUsNi4yNSIKICAgICAgICAgZmlsbD0idXJsKCNpKSIKICAgICAgICAgc3R5bGU9ImZpbGw6dXJsKCNpKSIgLz4KICAgIDwvZz4KICA8L2c+Cjwvc3ZnPgo=',
        dpd: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMDBweCIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PHBhdGggZmlsbD0iI0JBMDAyMiIgZD0iTTUwLDNjMjUuOTE2LDAsNDcsMjEuMDg0LDQ3LDQ3Uzc1LjkxNiw5Nyw1MCw5N1MzLDc1LjkxNiwzLDUwUzI0LjA4NCwzLDUwLDMgTTUwLDBDMjIuMzg2LDAsMCwyMi4zODYsMCw1MHMyMi4zODYsNTAsNTAsNTBzNTAtMjIuMzg2LDUwLTUwUzc3LjYxNCwwLDUwLDBMNTAsMHoiLz48L2c+PGcgaWQ9Il94MjNfZTExNjQyZmYiPjxwYXRoIGZpbGw9IiNFMjE3NDIiIGQ9Ik0yNS4wNSw2Ni43MDFjOC4xLDQuNzg1LDE2LjIxMiw5LjU1MywyNC4zMTgsMTQuMzM2YzAuNzQ5LDAuNDk2LDEuNzIyLDAuNDgsMi40NjYtMC4wMThjOC4wNDUtNC43NSwxNi4wOTQtOS40OTQsMjQuMTQxLTE0LjIzN2MwLjgyNi0wLjQyNSwxLjM2OS0xLjI3MSwxLjI2Ni0yLjIxOGMtMC4wMTYtOS4wMDIsMC4wMjctMTguMDAyLTAuMDIyLTI3LjAwMmMtNS41OTksMy4xNDYtMTEuMDY1LDYuNTI4LTE2LjY5LDkuNjI1Yy0wLjg4Ny0wLjU3NS0yLjQxNC0wLjg4LTIuMzc1LTIuMTk2Yy0wLjAwOC0wLjc3OC0wLjI0Mi0xLjg1MSwwLjYzOC0yLjI0MmM1LjQ3LTMuMTgsMTAuOTI5LTYuMzc3LDE2LjQwNy05LjUzOGMwLjIwMS0wLjEwNCwwLjczNi0wLjQ1OCwwLjMzMi0wLjY0NmMtNy45MDItNC4zMi0xNS44MTktOC42MTgtMjMuNzI3LTEyLjkzMWMtMC43NDgtMC40NS0xLjY2OC0wLjQ1OS0yLjQxOC0wLjAwN2MtNy44NSw0LjI3LTE1LjY5LDguNTYtMjMuNTQ2LDEyLjgyYy0wLjQxOSwwLjA4OS0wLjM3OSwwLjU2NywwLjAwMiwwLjY3OUMzNC41ODUsMzguMTkxLDQzLjMwNCw0My4zLDUyLjA1Myw0OC4zNmMwLjU1NywwLjI3MiwxLjAyLDAuNzU3LDAuOTI0LDEuNDI4Yy0wLjAxLDcuNTQ5LDAuMDMzLDE1LjEwNC0wLjAyNCwyMi42NTNjLTAuNTY3LDIuMDc4LTMuODQzLDIuMTQ4LTQuNzA3LDAuMjRjLTAuMTA5LTYuOTk2LTAuMDA5LTE0LjAwNC0wLjA1MS0yMS4wMDdjLTguMDY3LTQuNzQyLTE2LjEzOS05LjQ4NC0yNC4yNzMtMTQuMTA3Yy0wLjA4Nyw4LjgyNSwwLDE3LjY1My0wLjA0MiwyNi40NzhDMjMuODIyLDY1LjA1NywyNC4wNjMsNjYuMjAyLDI1LjA1LDY2LjcwMXoiLz48L2c+PC9zdmc+',
        boxberry: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMDBweCIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PHBhdGggZmlsbD0iI0JBMDAyMiIgZD0iTTUwLDNjMjUuOTE2LDAsNDcsMjEuMDg0LDQ3LDQ3Uzc1LjkxNiw5Nyw1MCw5N1MzLDc1LjkxNiwzLDUwUzI0LjA4NCwzLDUwLDMgTTUwLDBDMjIuMzg2LDAsMCwyMi4zODYsMCw1MHMyMi4zODYsNTAsNTAsNTBzNTAtMjIuMzg2LDUwLTUwUzc3LjYxNCwwLDUwLDBMNTAsMHoiLz48L2c+PGc+PHBhdGggZmlsbD0iIzg2QkIyRCIgZD0iTTQ4LjU3MiwxNS44OTljLTQuNjk5LDEuMzctNy4wNjEsNy42MjktMy44ODgsMTEuNTM4YzMuMTQ5LDQuNzkxLDExLjQ4LDMuNTI5LDEyLjk3NS0yLjA1M0M1OS43NTgsMjAuMDc2LDUzLjk2NCwxNC4wNyw0OC41NzIsMTUuODk5eiIvPjxwYXRoIGZpbGw9IiNERDIxNEIiIGQ9Ik00My41NDIsNTAuNzkxYzEuMDQ1LDMuODMsNS41MTcsNi4yMjksOS4yODQsNC45MzhjMy43ODktMS4wMzksNi4yMjktNS41LDQuODc5LTkuMjMyYy0xLjEzMy0zLjc0Mi01LjQ1Ny02LjA2OC05LjIwNi00Ljg0OUM0NC43ODgsNDIuNzMzLDQyLjI3LDQ3LjA0OSw0My41NDIsNTAuNzkxeiIvPjxwYXRoIGZpbGw9IiNERDIxNEIiIGQ9Ik02My4zNTQsNDMuMzI3YzQuMDIyLDAuMTI4LDcuNzEzLTMuNTY3LDcuNDE0LTcuNjI1YzAuMTEzLTQuNDQ0LTQuNTUtOC4xMzUtOC44NTEtNy4wNWMtMy41NTgsMC43Ni02LjM3OCw0LjQyNS01Ljc2LDguMUM1Ni40NDcsNDAuMzU0LDU5Ljc1Nyw0My4zMjcsNjMuMzU0LDQzLjMyN3oiLz48cGF0aCBmaWxsPSIjREQyMTRCIiBkPSJNNzQuMzI0LDQxLjYxNmMtMy4yNTgsMC44NTktNS43MTcsNC4xNDYtNS40MDcsNy41NDJjMC4wOTksMy43ODUsMy41NTIsNy4wMSw3LjMxNyw2LjkxN2M0LjE2MiwwLjEyMyw3Ljg2OS0zLjgxNSw3LjM3Ny03Ljk3N0M4My4zOTYsNDMuNjUyLDc4LjU3Miw0MC4zMjQsNzQuMzI0LDQxLjYxNnoiLz48cGF0aCBmaWxsPSIjREQyMTRCIiBkPSJNNDQuNDk5LDM5LjEzOWMyLjgzOC01LjI1NS0yLjY1Mi0xMi4xMzYtOC40MDEtMTAuNDRjLTQuODI4LDEuMDAzLTcuMTksNy4yNDItNC40MjQsMTEuMjUzQzM0LjQ5Nyw0NC43MDIsNDIuMjg2LDQ0LjE4NSw0NC40OTksMzkuMTM5eiIvPjxwYXRoIGZpbGw9IiNERDIxNEIiIGQ9Ik0zMi4zNTYsNDguMDk5Yy0wLjE3Ny00LjM5NC01LjAyOS03Ljc0NC05LjIxMi02LjQ4M2MtMy41MDUsMC44MTItNS45OSw0LjUxMy01LjQzMyw4LjA2OWMwLjM5OCwzLjk1OSw0LjQ5Nyw2Ljk3OSw4LjQsNi4zMTdDMjkuODU5LDU1LjU0NywzMi44MDUsNTEuODYxLDMyLjM1Niw0OC4wOTl6Ii8+PHBhdGggZmlsbD0iI0REMjE0QiIgZD0iTTYxLjM4Nyw1NC40NTZjLTMuNjA5LDEuMDEzLTYuMTMyLDUuMTQtNS4wMzUsOC43OTdjMC43MTMsMy4zNyw0LjA0Nyw1Ljg5Nyw3LjQ4OSw1LjY1NWM0LjE4My0wLjA2Myw3LjU2My00LjMxNSw2Ljg2OS04LjQwNEM3MC4yMyw1Ni4yMDgsNjUuNTMxLDUzLjA5Myw2MS4zODcsNTQuNDU2eiIvPjxwYXRoIGZpbGw9IiNERDIxNEIiIGQ9Ik00Ny45OTMsNjcuNDczYy01LjYyNSwxLjgyMy02LjQxNSwxMC4zNTMtMS4xOTksMTMuMTY0YzUuMTY0LDMuNTg4LDEyLjg0MS0xLjkxLDExLjA3Mi03LjkzOEM1Ny4wMDIsNjguNDM0LDUyLjAwNSw2NS44MjgsNDcuOTkzLDY3LjQ3M3oiLz48cGF0aCBmaWxsPSIjREQyMTRCIiBkPSJNNDUuMDU3LDYwLjA1OWMtMC44MDItNC4xNDktNS40NDMtNi45NzMtOS40ODUtNS41NDVjLTMuMTQ4LDAuOTY3LTUuMzM1LDQuMjk3LTUuMDM0LDcuNTY2YzAuMTcsMy43NywzLjY1NCw3LjA2Myw3LjQ2Myw2LjgzOUM0Mi40MDUsNjguOTQzLDQ2LjA4Niw2NC4zNTUsNDUuMDU3LDYwLjA1OXoiLz48L2c+PC9zdmc+',
        cdek: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMDBweCIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PHBhdGggZmlsbD0iI0JBMDAyMiIgZD0iTTUwLDNjMjUuOTE2LDAsNDcsMjEuMDg0LDQ3LDQ3Uzc1LjkxNiw5Nyw1MCw5N1MzLDc1LjkxNiwzLDUwUzI0LjA4NCwzLDUwLDMgTTUwLDBDMjIuMzg2LDAsMCwyMi4zODYsMCw1MHMyMi4zODYsNTAsNTAsNTBzNTAtMjIuMzg2LDUwLTUwUzc3LjYxNCwwLDUwLDBMNTAsMHoiLz48L2c+PHBhdGggZmlsbD0iIzU3QTUyQyIgZD0iTTU0LjYzMyw3Ni41MjFjOC41NjMtMS4xMjMsMTYuMjk1LTYuODAzLDIwLjA1My0xNC41NTVjNC4xMDUtOC4xMzIsMy41NTktMTguMzgyLTEuMzc3LTI2LjAzOGMtNS42NDYtOS4xNi0xNy4wOTYtMTMuOTIxLTI3LjYwMi0xMS44MDNjLTguMDkxLDEuNTI3LTE1LjIyOCw3LjEzLTE4Ljc1NSwxNC41NmMtNC4yNjUsOC42NDUtMy4yMzksMTkuNjA2LDIuNTU1LDI3LjMxNUMzNS4xNTEsNzMuNzA0LDQ1LjE1OCw3OC4wMDgsNTQuNjMzLDc2LjUyMXogTTI5Ljk3MywzNC40NzRjMTMuODA2LDQuNzI2LDI4LjU4NSwxLjU5OSw0Mi43MzksMy4zMTJjLTMuNjc2LDEuODY3LTcuOTExLDIuMzUxLTExLjQ0NCw0LjUxNmM0Ljg3NSwwLjMzNSw5Ljc2OC0wLjQ3MSwxNC42MzcsMC4xNzRjLTIuNzQ1LDEuNjY1LTUuOTI2LDIuMzA0LTguODk2LDMuNDEzYy05LjE4OCwzLjI1Mi0xOC4wOTcsNy45NzctMjQuNzUsMTUuMjM1Yy0yLjgyMiwyLjg4My00LjkxNiw2LjM1NC03LjUyNCw5LjQxNGMwLjYyNy0zLjgxMywxLjg2Mi03LjUyNCwyLjA5NC0xMS4zOTdjLTIuMDg4LDIuMTUzLTMuNjg3LDQuNjk1LTUuNTE5LDcuMDUzYzEuNjgzLTYuNzcxLDIuNzMzLTEzLjcwNSw1LjA3Mi0yMC4yOTJDMzQuNzcsNDEuODcyLDMxLjcwOSwzOC41LDI5Ljk3MywzNC40NzR6Ii8+PC9zdmc+',
        ruspost: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMDBweCIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PHBhdGggZmlsbD0iI0JBMDAyMiIgZD0iTTUwLDNjMjUuOTE2LDAsNDcsMjEuMDg0LDQ3LDQ3Uzc1LjkxNiw5Nyw1MCw5N1MzLDc1LjkxNiwzLDUwUzI0LjA4NCwzLDUwLDMgTTUwLDBDMjIuMzg2LDAsMCwyMi4zODYsMCw1MHMyMi4zODYsNTAsNTAsNTBjMjcuNjEzLDAsNTAtMjIuMzg2LDUwLTUwUzc3LjYxMywwLDUwLDBMNTAsMHoiLz48L2c+PHBhdGggZmlsbD0iIzAwNkY4NyIgZD0iTTc2LDc2LjM0OGMtMC4wMjMtMTcuMjI5LTAuMDIzLTM0LjQ2MywwLTUxLjY5NGMtMTcuMzU0LTAuMDM1LTM0LjcwOC0wLjAzNS01Mi4wNjIsMGMwLjAyNywxNy4yMzEsMC4wMjcsMzQuNDYzLDAsNTEuNjk2QzQxLjI5Miw3Ni4zNzksNTguNjQ2LDc2LjM4MSw3Niw3Ni4zNDh6IE0zMC44NTksMzYuMzEyYzYuMDkxLTAuMDQ5LDEyLjE5Ny0wLjE3NywxOC4yODksMC4wOTVjLTAuMDc2LDEwLjE0LTAuMDU2LDIwLjI3MS0wLjAyMSwzMC40MTFjLTEuODczLDAuMDc1LTMuNzQ1LDAuMTI2LTUuNjIxLDAuMTU5Yy0wLjEyLTguNTA1LTAuMDc1LTE3LjAxMy0wLjAyNi0yNS41MTdjLTIuMzE3LTAuMDgyLTQuNjM4LTAuMTA0LTYuOTU0LTAuMDljLTAuMDc1LDguNS0wLjAzOCwxNy4wMDctMC4wMTksMjUuNTA4Yy0xLjg4NiwwLjAzOS0zLjc2OSwwLjA2LTUuNjQ5LDAuMDc2QzMwLjgwMyw1Ni43NDMsMzAuODA4LDQ2LjUyOCwzMC44NTksMzYuMzEyeiBNNTYuMTg1LDM2LjAzN2MzLjg2OSwwLDguNzIzLTAuNzQ5LDExLjQxNywyLjc0NWMzLjYzMSw0Ljg1MywzLjMyOCwxMS44NiwxLjExMSwxNy4yMzFjLTEuMjkyLDMuMDMyLTQuNzQsMy4yMTgtNy40NzYsMi4zMDNjLTAuMDMzLDIuNjc2LTAuMDMzLDUuMzUsMCw4LjAyOWMtMS42NjUsMC4wMDUtMy4zMjksMC4wMTktNC45OTMsMC4wMzhDNTYuMDE0LDU2LjI3Miw1Ni4xMjksNDYuMTUyLDU2LjE4NSwzNi4wMzd6Ii8+PHBhdGggZmlsbD0iIzAwNkY4NyIgZD0iTTY0LjcxOCw1Mi42NGMxLjM4Ni00LjI2OCwxLjI5NS0xMC42NzYtMy40OTEtMTIuNzk3Yy0wLjI2Niw0LjA3OS0wLjU5Nyw4LjIzMiwwLjA3OSwxMi4yODFDNjEuNjQ4LDUzLjk5Myw2NC4wNTUsNTQuNzQyLDY0LjcxOCw1Mi42NHoiLz48L3N2Zz4=',
        pickpoint: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMDBweCIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PHBhdGggZmlsbD0iI0JBMDAyMiIgZD0iTTUwLDNjMjUuOTE2LDAsNDcsMjEuMDg0LDQ3LDQ3Uzc1LjkxNiw5Nyw1MCw5N1MzLDc1LjkxNiwzLDUwUzI0LjA4NCwzLDUwLDMgTTUwLDBDMjIuMzg2LDAsMCwyMi4zODYsMCw1MHMyMi4zODYsNTAsNTAsNTBzNTAtMjIuMzg2LDUwLTUwUzc3LjYxNCwwLDUwLDBMNTAsMHoiLz48L2c+PGc+PGc+PHBhdGggZmlsbD0iIzRGNUE1QiIgZD0iTTUwLjEyNyw3Ni44NjFjLTcuNDQ4LDAtMTMuODI2LTIuNjA0LTE5LjA4Mi03Ljg1NGMtNS4zMDgtNS4yMDYtNy45MDktMTEuNTgyLTcuOTA5LTE4Ljk4MWMwLTcuMjk3LDIuNjUzLTEzLjYyNCw3Ljg1Ny0xOC45MjljNS4zMDgtNS4zMDUsMTEuNjM0LTcuOTU4LDE5LjEzMy03Ljk1OGM3LjM1MSwwLDEzLjYyMywyLjY1MywxOC44NzksNy45MDVjNS4yNTcsNS4zMDYsNy44NTgsMTEuNTgyLDcuODU4LDE4LjkyOXYyNi44OUg1MC4xMjdWNzYuODYxeiBNNTAuMTI3LDM2LjU1NWMtMy43NzUsMC02Ljk4OSwxLjMyNy05LjU5MiwzLjk3OWMtMi42MDIsMi42NTYtMy44NzYsNS44MTctMy44NzYsOS40MzhjMCwzLjc3NiwxLjI3NCw2Ljk0MSwzLjg3Niw5LjU0MmMyLjYwMywyLjYwNSw1LjgxNywzLjg3OSw5LjU5MiwzLjg3OWMzLjY3NiwwLDYuODM4LTEuMjcyLDkuNDkxLTMuODc5YzIuNjUxLTIuNjAxLDMuOTc5LTUuODE0LDMuOTc5LTkuNTQyYzAtMy42NjktMS4zMjYtNi43ODEtMy45NzktOS40MzhDNTYuOTY1LDM3Ljg4MSw1My44MDMsMzYuNTU1LDUwLjEyNywzNi41NTV6Ii8+PC9nPjwvZz48cGF0aCBmaWxsPSIjREI1MDJDIiBkPSJNNTUuMTMxLDUwLjAyNWMwLDIuODA2LTIuMyw1LjEwMy01LjEwNSw1LjEwM2MtMi44MDUsMC01LjEwMy0yLjI5Ny01LjEwMy01LjEwM3MyLjI5Ny01LjEwMyw1LjEwMy01LjEwM0M1Mi44MzIsNDQuOTIyLDU1LjEzMSw0Ny4xNjYsNTUuMTMxLDUwLjAyNXoiLz48L3N2Zz4=',
        euroset: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMDBweCIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PHBhdGggZmlsbD0iI0JBMDAyMiIgZD0iTTUwLDNjMjUuOTE2LDAsNDcsMjEuMDg0LDQ3LDQ3Uzc1LjkxNiw5Nyw1MCw5N1MzLDc1LjkxNiwzLDUwUzI0LjA4NCwzLDUwLDMgTTUwLDBDMjIuMzg2LDAsMCwyMi4zODYsMCw1MHMyMi4zODYsNTAsNTAsNTBzNTAtMjIuMzg2LDUwLTUwUzc3LjYxNCwwLDUwLDBMNTAsMHoiLz48L2c+PHBvbHlnb24gZmlsbD0iIzRDMUU4NyIgcG9pbnRzPSI0MC42OTksNjEuNDMyIDQwLjY5OSwzNC41MjQgMzEuNDc5LDM0LjUyNCAzMS40NzksNzYuMDc5IDQwLjg0Niw3Ni4wNzkgNTkuMTQxLDQ5LjI1MyA1OS4xNDEsNzYuMDc5IDY4LjUwOCw3Ni4wNzkgNjguNTA4LDM0LjUyNCA1OS4wMDcsMzQuNTI0ICIvPjxwYXRoIGZpbGw9IiNFNDFEOTQiIGQ9Ik00OS4yMiwyNS4zNDhjLTMuMjE4LDAtNS43ODEtMi4xNjktNS43ODEtNC45OTNsLTcuNTA1LDAuOTRjMC41OTUsNi41MjksNi4wODYsMTAuOTgsMTMuMjg2LDEwLjk4YzcuMTk3LDAsMTIuNjg4LTQuNDUxLDEzLjI4MS0xMC45OGwtNy41MDYtMC45NEM1NC45OTgsMjMuMTgsNTIuNDM5LDI1LjM0OCw0OS4yMiwyNS4zNDh6Ii8+PC9zdmc+',
        pec: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMDBweCIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PHBhdGggZmlsbD0iI0JBMDAyMiIgZD0iTTUwLDNjMjUuOTE2LDAsNDcsMjEuMDg0LDQ3LDQ3Uzc1LjkxNiw5Nyw1MCw5N1MzLDc1LjkxNiwzLDUwUzI0LjA4NCwzLDUwLDMgTTUwLDBDMjIuMzg2LDAsMCwyMi4zODYsMCw1MHMyMi4zODYsNTAsNTAsNTBjMjcuNjEzLDAsNTAtMjIuMzg2LDUwLTUwUzc3LjYxMywwLDUwLDBMNTAsMHoiLz48L2c+PGcgaWQ9IlBhZ2UtMSI+PGcgaWQ9ImxvZ28iPjxwYXRoIGlkPSJSZWN0YW5nbGUiIGZpbGw9IiNEMDJENDIiIGQ9Ik04MS42NDksNDEuMDY3YzEuMDU0LTAuMTU0LDIuMTU1LTAuMjMxLDMuMzA0LTAuMjMxczIuMjUsMC4wNzcsMy4zMDQsMC4yMzF2NS41NjRoLTYuNjA3VjQxLjA2N3oiLz48cGF0aCBpZD0iUmVjdGFuZ2xlLUNvcHkiIGZpbGw9IiNEMDJENDIiIGQ9Ik04MS42NDksNTkuMjdjMS4wNTQsMC4xNTMsMi4xNTUsMC4yMywzLjMwNCwwLjIzczIuMjUtMC4wNzcsMy4zMDQtMC4yM3YtNS41NjVoLTYuNjA3VjU5LjI3eiIvPjxwb2x5Z29uIGlkPSJQYXRoLTQiIGZpbGw9IiMyNDIyNjUiIHBvaW50cz0iNTcuOTY0LDQxLjI2NyA1Ny45NjQsNTkuMTIgNjQuMjI4LDU5LjEyIDY0LjIyOCw1Mi4yNzYgNjYuNTk4LDUyLjI3NiA3MS44MDMsNTkuMTIgNzkuNTY2LDU5LjEyIDcxLjgwMyw0OS42NDcgNzkuMjE0LDQxLjI2NyA3MS44MDMsNDEuMjY3IDY2LjU5OCw0Ny44NDYgNjQuMjI4LDQ3Ljg0NiA2NC4yMjgsNDEuMjY3ICIvPjxwYXRoIGlkPSJQYXRoLTMiIGZpbGw9IiMyNDIyNjUiIGQ9Ik0zNS43ODMsNDEuMTg4bDAuODIxLDQuNzI3YzIuNzk1LTAuMTg4LDUuMTE0LTAuMjgyLDYuOTU3LTAuMjgyYzEuODQzLDAsMy42NzEsMC4wOTQsNS40ODYsMC4yODJsMC4zMTYsMS44MjNoLTguMjU5djQuNjk5aDguMjU5bC0wLjMxNiwyLjA0NmMtMS41ODEsMC4xMzMtMy40ODgsMC4xOTktNS43MjEsMC4xOTlzLTQuNDc0LTAuMDY2LTYuNzIyLTAuMTk5bC0wLjgyMSw0LjYwNGMzLjMyNywwLjE4MSw2LjQzOCwwLjI3Miw5LjMzMywwLjI3MnM1Ljc3MS0wLjA5Miw4LjYyOC0wLjI3MmMxLjA1OS0yLjg1MywxLjU4OS01Ljc3NiwxLjU4OS04Ljc3NGMwLTIuOTk2LTAuNTMtNi4wMzgtMS41ODktOS4xMjRjLTIuMjA2LTAuMTY1LTQuOTI4LTAuMjQ4LTguMTY3LTAuMjQ4QzQyLjMzOCw0MC45NCwzOS4wNzMsNDEuMDIzLDM1Ljc4Myw0MS4xODh6Ii8+PHBvbHlnb24gaWQ9IlBhdGgtMiIgZmlsbD0iIzI0MjI2NSIgcG9pbnRzPSIxMy4zNDQsNDEuMjU3IDEzLjM0NCw1OS4wNTYgMTkuNzQ2LDU5LjA1NiAxOS43NDYsNDYuMTg1IDI3LjEwMiw0Ni4xODUgMjcuMTAyLDU5LjA1NiAzMy41NjUsNTkuMDU2IDMzLjU2NSw0MS4yNTcgIi8+PC9nPjwvZz48L3N2Zz4=',
        iml: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHdpZHRoPSIxMDBweCIgaGVpZ2h0PSIxMDBweCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnPjxjaXJjbGUgZmlsbD0iI0ZGRkZGRiIgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIi8+PHBhdGggZmlsbD0iI0JBMDAyMiIgZD0iTTUwLDNjMjUuOTE2LDAsNDcsMjEuMDg0LDQ3LDQ3Uzc1LjkxNiw5Nyw1MCw5N1MzLDc1LjkxNiwzLDUwUzI0LjA4NCwzLDUwLDMgTTUwLDBDMjIuMzg2LDAsMCwyMi4zODYsMCw1MHMyMi4zODYsNTAsNTAsNTBzNTAtMjIuMzg2LDUwLTUwUzc3LjYxNCwwLDUwLDBMNTAsMHoiLz48L2c+PGc+PGcgaWQ9Il94MjNfZmZiOTRhZmYiPjxwYXRoIGZpbGw9IiNGOEI4NEUiIGQ9Ik03NS4xMiwzMS4yMDdjLTUuOTQ1LTAuMzcyLTExLjg5LTAuNzQyLTE3LjgzNC0xLjExNWMtMS44NjEtMC4xOTQtMy43MjktMC4yNTQtNS41OTktMC4zNDFoLTEuMzcydjAuMDY0YzAsMC44OTktMC4wMDEsMS43OTYsMC4wMDEsMi42OTVjMS45ODgsMC4wMzQsMy45NzYsMC4wNjIsNS45NTcsMC4yMjNjNS44MjQsMC4yMTEsMTEuNjQ4LDAuNDM3LDE3LjQ3NCwwLjY0NmMwLjM5MiwwLjAxOSwwLjgwNi0wLjAwNywxLjE2NSwwLjE3NGMwLjUxLDAuMjUxLDAuODQsMC44MTksMC43OTcsMS4zODljLTAuMzk2LDguODY5LTAuNzg1LDE3Ljc0LTEuMTc3LDI2LjYwOGMtMC4wMTcsMC44LTAuNDM3LDEuNTc1LTEuMDk5LDIuMDI0Yy0wLjM2MywwLjI1Ny0wLjgsMC4zNzEtMS4yMTgsMC41MTFjLTYuODg2LDIuMjc5LTEzLjc3MSw0LjU2NS0yMC42NTUsNi44NDNjLTAuNDAxLDAuMTQxLTAuODEzLDAuMjg0LTEuMjQ0LDAuMjgyYzAuMDU4LDAuNjczLTAuMTMzLDEuMzg4LDAuMTA4LDIuMDI4YzAuOTYsMC4wMDUsMS45MTUtMC4xODIsMi44MTktMC40OTRjNy4xOTctMi40MDQsMTQuNC00Ljc4NSwyMS41OTctNy4xOTNjMS4wNi0wLjM2NSwxLjgyNi0xLjMxLDIuMTQ5LTIuMzYxYzAuMjU2LTQuMjE3LDAuMzUzLTguNDQxLDAuNTY2LTEyLjY2YzAuMTc4LTMuOTc4LDAuMzMyLTcuOTU0LDAuNS0xMS45MzFjMC4wMDQtMC45LDAuMDc1LTEuNzk2LDAuMDkyLTIuNjk2YzAuMDM3LTAuOCwwLjE0Ni0xLjYyOC0wLjEwNC0yLjQwNkM3Ny42NDMsMzIuMjQzLDc2LjQ0MSwzMS4yOCw3NS4xMiwzMS4yMDd6Ii8+PHBhdGggZmlsbD0iI0Y4Qjg0RSIgZD0iTTU0LjAwMSwzNi4xMzRjMC4wMDEtMC4wMDIsMC4wMDUtMC4wMDksMC4wMDgtMC4wMTFDNTQuMDA2LDM2LjEyNSw1NC4wMDIsMzYuMTMxLDU0LjAwMSwzNi4xMzR6Ii8+PHBhdGggZmlsbD0iI0Y4Qjg0RSIgZD0iTTUwLjE1NCwzOC44OGMwLjAwMywwLjAwMiwwLjAwNiwwLjAwOSwwLjAwOCwwLjAxMUM1MC4xNiwzOC44ODksNTAuMTU2LDM4Ljg4Miw1MC4xNTQsMzguODh6Ii8+PHBhdGggZmlsbD0iI0Y4Qjg0RSIgZD0iTTMzLjM2MSwzOS45MTZjMC4wMDItMC4wMDMsMC4wMDUtMC4wMDksMC4wMDgtMC4wMTJDMzMuMzY2LDM5LjkwNywzMy4zNjMsMzkuOTEzLDMzLjM2MSwzOS45MTZ6Ii8+PHBhdGggZmlsbD0iI0Y4Qjg0RSIgZD0iTTYxLjMwNywzOS45MTZjMC4wMDQtMC4wMDMsMC4wMDgtMC4wMDgsMC4wMS0wLjAxMkM2MS4zMTQsMzkuOTA3LDYxLjMxMSwzOS45MTMsNjEuMzA3LDM5LjkxNnoiLz48cGF0aCBmaWxsPSIjRjhCODRFIiBkPSJNNTEuMDUzLDQwLjA0NWMwLjAwMi0wLjAwMywwLjAwNi0wLjAwOSwwLjAwOC0wLjAxMkM1MS4wNTksNDAuMDM1LDUxLjA1NSw0MC4wNDIsNTEuMDUzLDQwLjA0NXoiLz48cGF0aCBmaWxsPSIjRjhCODRFIiBkPSJNNTAuOTIxLDQwLjI5OGMwLjAwMS0wLjAwNCwwLjAwNC0wLjAxLDAuMDA3LTAuMDE0QzUwLjkyNSw0MC4yODgsNTAuOTIyLDQwLjI5NCw1MC45MjEsNDAuMjk4eiIvPjxwYXRoIGZpbGw9IiNGOEI4NEUiIGQ9Ik01MC4zMTYsNDIuNDc5YzAuMDA5LDIuNTU4LTAuMDI0LDUuMTE2LDAuMDE5LDcuNjcyYzAuNjQ0LTEuMzk1LDEuMjY1LTIuODAxLDEuODk2LTQuMjAxYzAuMTIxLTAuMjU0LDAuMjA1LTAuNTM5LDAuNDA2LTAuNzRjMC4yMjEtMC4yMDgsMC41NDEtMC4yMDcsMC44MjItMC4yMDhjMC45NDEsMC4wMTMsMS44ODQtMC4wMDcsMi44MjQsMC4wMDljMC40MTgtMC4wMTEsMC44MjIsMC4zMjYsMC44MDUsMC43NjFjMC4wMDQsNC45OCwwLjAwMiw5Ljk2MiwwLjAwMiwxNC45NDFjMC4wMjksMC40MTUtMC4zMjgsMC43OTUtMC43NDQsMC43ODdjLTAuNzcxLDAuMDA4LTEuNTQxLDAuMDA1LTIuMzEyLDAuMDAxYy0wLjM2NiwwLjAxLTAuNzI5LTAuMjg5LTAuNzQxLTAuNjY0Yy0wLjAyMS0zLjI2OSwwLjAyMS02LjUzNy0wLjAyMS05LjgwNGMtMC4wNjksMC4wMzMtMC4xMzEsMC4wNzYtMC4xODIsMC4xMzJjLTEuMDA2LDIuMDU4LTEuODcyLDQuMTgzLTIuODUzLDYuMjU0YzAuMDM4LDAuMTA4LDAuMDgxLDAuMjIyLDAuMDgxLDAuMzRjLTAuMDA4LDQuMDkzLTAuMDAyLDguMTg1LTAuMDAzLDEyLjI3NmMwLjI5MS0wLjAzNCwwLjU2OC0wLjEyLDAuODM5LTAuMjIxYzcuMDMxLTIuMzI4LDE0LjA2LTQuNjY2LDIxLjA5Mi02Ljk4NmMwLjM1OS0wLjEwMiwwLjY5Ny0wLjMxMSwwLjg5OC0wLjYzM2MwLjI2My0wLjQsMC4yMTMtMC44OTksMC4yMzEtMS4zNTRjMC4yNjUtNi4xMTgsMC41NDUtMTIuMjM4LDAuODA0LTE4LjM1OEM2Ni4yMjksNDIuNDY1LDU4LjI3MSw0Mi40NzMsNTAuMzE2LDQyLjQ3OXogTTYyLjcxMiw0NS43MTFjMC4wMDMsNC4wMzksMCw4LjA3OCwwLDEyLjExOWMtMC4wMDIsMC40MTUsMC40MDcsMC43NDksMC44MTIsMC43MjVjMS42NjgsMC4wMDIsMy4zMzYtMC4wMDYsNS4wMDQsMC4wMDJjMC40LTAuMDA3LDAuOCwwLjMyNywwLjc4MywwLjc0M2MwLjAwNiwwLjUxMywwLjAxNywxLjAyNy0wLjAwNiwxLjUzOGMtMC4wMTYsMC4zOTUtMC40MDQsMC42ODYtMC43ODMsMC42NjZjLTMuMDEyLDAtNi4wMjIsMC4wMDEtOS4wMzctMC4wMDJjLTAuNDUzLDAuMDIxLTAuODEyLTAuNDExLTAuNzY5LTAuODUyYy0wLjAwMS00Ljk2MS0wLjAwMi05LjkyLDAtMTQuODc5Yy0wLjAyOC0wLjM4OCwwLjI4LTAuNzUxLDAuNjY1LTAuNzkzYzAuODQ2LTAuMDMzLDEuNjk1LTAuMDA2LDIuNTQzLTAuMDE0QzYyLjMyNyw0NC45NSw2Mi43MzQsNDUuMjkxLDYyLjcxMiw0NS43MTF6Ii8+PC9nPjxnIGlkPSJfeDIzX2ZmYzU1MmZmIj48cGF0aCBmaWxsPSIjRkFDMzU1IiBkPSJNNTAuMzE3LDcxLjIxYy0wLjUwNCwwLjAwNy0wLjk3Ni0wLjE4OC0xLjQ0OC0wLjMzN2MtNi43ODItMi4yNTktMTMuNTcxLTQuNDk4LTIwLjM1NC02Ljc1MWMtMC4zNDgtMC4xMjEtMC43MDgtMC4yMTUtMS4wNC0wLjM3NmMtMC43ODQtMC4zODEtMS4zMjctMS4yMDItMS4zODYtMi4wN2MtMC4zODQtOC43Mi0wLjc3MS0xNy40MzgtMS4xNTctMjYuMTU4Yy0wLjAxLTAuMzcxLTAuMDY1LTAuNzUzLDAuMDQxLTEuMTE2YzAuMTc1LTAuNTYsMC43MTctMC45NzksMS4zMDMtMC45OThjNS45NjUtMC4yMjIsMTEuOTMtMC40MzksMTcuODk1LTAuNjY4YzIuMDQ2LTAuMTM5LDQuMDk2LTAuMjA2LDYuMTQ2LTAuMjI1Yy0wLjAwMi0wLjg5OS0wLjAwMS0xLjc5Ni0wLjAwMS0yLjY5NWMtMi4yODUsMC4wMzYtNC41NzIsMC4xMTMtNi44NDksMC4zMzljLTYuMDA3LDAuMzc2LTEyLjAxNCwwLjc0NS0xOC4wMjEsMS4xMjljLTEuNzI0LDAuMTA5LTMuMTI3LDEuNzQ1LTMuMDY5LDMuNDUzYzAuMDUsMC4zOTQsMC4wNTQsMC43OTEsMC4wNDEsMS4xODdjMC4wMDcsMC4xMTYsMC4wMTYsMC4yMzIsMC4wMjMsMC4zNDhjMC4wNSwwLjM5NSwwLjA1OCwwLjc5MywwLjA0MSwxLjE5YzAuMDEsMC4zNzcsMC4wMDcsMC43NTcsMC4wMzEsMS4xMzVjMC4xMzksMi4yNzgsMC4xNzcsNC41NiwwLjMsNi44MzhjMC4wODIsMC40MTYsMC4wNiwwLjgzOSwwLjA1MSwxLjI2MWMwLjAwNCwwLjA3MSwwLjAwOSwwLjE0MiwwLjAxNCwwLjIxNGMwLjA4NCwwLjQ1OCwwLjA2LDAuOTIzLDAuMDU0LDEuMzg1YzAuMDAyLDAuMDM5LDAuMDA3LDAuMTE3LDAuMDEsMC4xNTZjMC4wODMsMC40NTgsMC4wNjQsMC45MjMsMC4wNSwxLjM4NmMwLjAwNywwLjIzMSwwLjAxNiwwLjQ2MywwLjAxOCwwLjY5NGMtMC4wMDQsMC4yOTQsMC4wMTUsMC41ODcsMC4wNiwwLjg4YzAuMDg0LDAuNDg5LDAuMDcxLDAuOTkxLDAuMDY4LDEuNDg5bDAuMDAzLDAuMDUxYzAuMTUxLDIuMDUyLDAuMTc4LDQuMTA5LDAuMjkxLDYuMTYzYzAuMDE2LDEuMDMsMC4xMjksMi4wNiwwLjA4NywzLjA5MWMwLjAwMiwwLjAzOSwwLjAwNywwLjExNSwwLjAxLDAuMTUyYzAuMDQ5LDEuMzk0LDAuOTI3LDIuNzQ2LDIuMjY5LDMuMTk5YzcuMTQ4LDIuMzg1LDE0LjMwMiw0Ljc1NiwyMS40NTIsNy4xNDJjMS4wMTYsMC4zNywyLjA5NiwwLjU2NSwzLjE3OCwwLjU0MkM1MC4xODQsNzIuNTk4LDUwLjM3NSw3MS44ODMsNTAuMzE3LDcxLjIxeiIvPjxwYXRoIGZpbGw9IiNGQUMzNTUiIGQ9Ik01Ny44NCwzNi4yNjhjMC4wODUtMC4xODEsMC4xMjQtMC4zNzcsMC4xNjctMC41N2MtMC4xMDMsMC4xOTQtMC4xNzksMC40MDMtMC4xOSwwLjYyNUw1Ny44NCwzNi4yNjh6Ii8+PHBhdGggZmlsbD0iI0ZBQzM1NSIgZD0iTTU5LjA5LDM2Ljk5N2MtMC4wMTQtMC4wMzItMC4wMzktMC4wOTUtMC4wNTMtMC4xMjdDNTkuMDUxLDM2LjkwMiw1OS4wNzYsMzYuOTY1LDU5LjA5LDM2Ljk5N3oiLz48cGF0aCBmaWxsPSIjRkFDMzU1IiBkPSJNNTIuNzc1LDQwLjA0OWMwLjA4Ni0wLjE4LDAuMTI1LTAuMzc3LDAuMTY4LTAuNTcxYy0wLjEwMywwLjE5NC0wLjE4LDAuNDA0LTAuMTkxLDAuNjI1TDUyLjc3NSw0MC4wNDl6Ii8+PHBhdGggZmlsbD0iI0ZBQzM1NSIgZD0iTTU2LjYyMiw0MC4wNDljMC4wODYtMC4xOCwwLjEyNS0wLjM3NywwLjE2Ny0wLjU3MWMtMC4xMDQsMC4xOTQtMC4xOCwwLjQwNC0wLjE5MSwwLjYyNUw1Ni42MjIsNDAuMDQ5eiIvPjxwYXRoIGZpbGw9IiNGQUMzNTUiIGQ9Ik01My45NzUsNDAuNjUzYzAuMDEyLDAuMDMxLDAuMDM5LDAuMDk0LDAuMDUzLDAuMTI2QzU0LjAxNCw0MC43NDcsNTMuOTg0LDQwLjY4NCw1My45NzUsNDAuNjUzeiIvPjxwYXRoIGZpbGw9IiNGQUMzNTUiIGQ9Ik01Ny44Miw0MC42NTJjMC4wMTIsMC4wMzIsMC4wMzcsMC4wOTUsMC4wNTEsMC4xMjZDNTcuODU3LDQwLjc0Nyw1Ny44MzIsNDAuNjg0LDU3LjgyLDQwLjY1MnoiLz48cGF0aCBmaWxsPSIjRkFDMzU1IiBkPSJNNDEuNDY4LDQ1LjAwOGMwLjk0MS0wLjAwOSwxLjg4MywwLjAwNSwyLjgyNS0wLjAwNWMwLjI5NS0wLjAwMiwwLjYzNCwwLjAxMiwwLjg1MiwwLjI0MmMwLjE5MiwwLjIxNywwLjI3NSwwLjUsMC4zOTksMC43NTdjMS4wOTQsMi4zOTcsMi4xNTcsNC44MDksMy4zMTksNy4xNzNjMC41MzctMC45ODMsMC45OTgtMi4wMDgsMS40NzItMy4wMjRjLTAuMDQyLTIuNTU3LTAuMDA4LTUuMTE1LTAuMDE5LTcuNjcyYy03Ljk1OC0wLjAwOS0xNS45MTYtMC4wMS0yMy44NzMsMGMwLjIzOSw2LjMzNiwwLjU2LDEyLjY2OSwwLjgxOSwxOS4wMDVjLTAuMDAyLDAuNTkxLDAuNDE4LDEuMTM3LDAuOTgyLDEuMzAxYzcuMjA2LDIuMzg3LDE0LjQxMiw0Ljc3MywyMS42MTcsNy4xNjRjMC4xNDksMC4wNDIsMC4zMDMsMC4wNjMsMC40NTYsMC4wODZjMC00LjA5Mi0wLjAwNS04LjE4NCwwLjAwMy0xMi4yNzZjMC0wLjExOC0wLjA0My0wLjIyOS0wLjA4MS0wLjM0Yy0wLjQzMywwLjQwNC0xLjA1OCwwLjIxNy0xLjU4NywwLjI1OWMtMC40NSwwLjAyNC0xLjA0OSwwLjAzNS0xLjI1Mi0wLjQ2NWMtMC45MDktMi4wMTgtMS43OS00LjA0OC0yLjcyLTYuMDU1Yy0wLjAzOS0wLjA4Ni0wLjEzMS0wLjEyMy0wLjE5Ny0wLjE4MmMtMC4wNSwzLjI4NywwLjAwMSw2LjU3My0wLjAyNiw5Ljg2Yy0wLjAxNywwLjM5OS0wLjQxNywwLjY5Ni0wLjgwNCwwLjY2OGMtMC43Ny0wLjAwNy0xLjU0LDAuMDExLTIuMzEtMC4wMDhjLTAuNDA2LTAuMDA0LTAuNzQyLTAuMzgyLTAuNzE1LTAuNzg1Yy0wLjAwNC00Ljc2NSwwLTkuNTMtMC4wMDItMTQuMjk1YzAuMDA0LTAuMjkyLTAuMDI3LTAuNTg5LDAuMDQxLTAuODc1QzQwLjc2Myw0NS4xOTksNDEuMTI3LDQ1LjAwNSw0MS40NjgsNDUuMDA4eiBNMzguNzk4LDYwLjk4NmMtMC4xMDMsMC4zMjYtMC40NDksMC41NDMtMC43ODcsMC41MTljLTAuODEyLTAuMDA1LTEuNjI0LDAuMDAzLTIuNDM2LTAuMDA0Yy0wLjM4NSwwLjAwNC0wLjc1LTAuMzMzLTAuNzI5LTAuNzI4YzAuMDAxLTUuMDIxLTAuMDAyLTEwLjA0MywwLjAwMS0xNS4wNjNjLTAuMDEzLTAuMzA3LDAuMjEzLTAuNTc1LDAuNDkzLTAuNjc0YzAuODYxLTAuMTAzLDEuNzM5LTAuMDE0LDIuNjA2LTAuMDQ3YzAuNDIyLTAuMDUyLDAuODg1LDAuMjcxLDAuODc2LDAuNzE3YzAsNC44NTItMC4wMDEsOS43MDIsMCwxNC41NTRDMzguODE3LDYwLjUwMiwzOC44NDYsNjAuNzQ5LDM4Ljc5OCw2MC45ODZ6Ii8+PC9nPjxnIGlkPSJfeDIzXzNiNDY1NmZmIj48cGF0aCBmaWxsPSIjM0M0NjU2IiBkPSJNNzQuOTEyLDMzLjU1M2MtMC4zNTktMC4xODEtMC43NzMtMC4xNTUtMS4xNjUtMC4xNzRjLTUuODI0LTAuMjA5LTExLjY0OS0wLjQzNS0xNy40NzQtMC42NDZjLTEuOTgtMC4xNjEtMy45NjktMC4xODgtNS45NTctMC4yMjNjLTIuMDUsMC4wMTktNC4xLDAuMDg2LTYuMTQ2LDAuMjI1Yy01Ljk2NSwwLjIyOS0xMS45MywwLjQ0Ni0xNy44OTUsMC42NjhjLTAuNTg2LDAuMDItMS4xMjgsMC40MzktMS4zMDMsMC45OThjLTAuMTA2LDAuMzYzLTAuMDUsMC43NDUtMC4wNDEsMS4xMTZjMC4zODYsOC43MiwwLjc3MiwxNy40MzgsMS4xNTcsMjYuMTU4YzAuMDU5LDAuODY4LDAuNjAyLDEuNjg5LDEuMzg2LDIuMDdjMC4zMzMsMC4xNjEsMC42OTIsMC4yNTUsMS4wNCwwLjM3NmM2Ljc4MywyLjI1MywxMy41NzIsNC40OTIsMjAuMzU0LDYuNzUxYzAuNDczLDAuMTQ3LDAuOTQ0LDAuMzQ0LDEuNDQ4LDAuMzM3YzAuNDMxLDAuMDAyLDAuODQyLTAuMTQzLDEuMjQ0LTAuMjgyYzYuODg1LTIuMjc4LDEzLjc3MS00LjU2MywyMC42NTUtNi44NDNjMC40MTgtMC4xNDEsMC44NTQtMC4yNTQsMS4yMTgtMC41MTFjMC42NjItMC40NDksMS4wODItMS4yMjgsMS4wOTktMi4wMjRjMC4zOTItOC44NjksMC43OC0xNy43MzksMS4xNzctMjYuNjA4Qzc1Ljc1MiwzNC4zNzIsNzUuNDIyLDMzLjgwNCw3NC45MTIsMzMuNTUzeiBNNjYuNzcxLDM4Ljc5YzAuNDM4LDAuMDIzLDAuODkxLTAuMDYxLDEuMzE2LDAuMDUzYzAuMzgzLDAuMTM0LDAuMzgzLDAuNjc0LDAuMDY2LDAuODgzYzAuNDQzLDAuMjA4LDAuMzk2LDAuOTA3LTAuMDYzLDEuMDYyYy0wLjQzMSwwLjEwOS0wLjg4MywwLjAzLTEuMzIyLDAuMDVDNjYuNzY0LDQwLjE1NSw2Ni43NjQsMzkuNDczLDY2Ljc3MSwzOC43OXogTTY1LjIxLDM4LjgxNmMwLjM4LTAuMDk1LDAuODQ3LDAuMDA1LDEuMDYyLDAuMzYxYzAuMjA3LDAuMzcyLDAuMjE3LDAuODU0LDAuMDIxLDEuMjMzYy0wLjMyMiwwLjYwNS0xLjM3MSwwLjU4NS0xLjY0My0wLjA2QzY0LjQyNSwzOS44MTEsNjQuNTQ0LDM4Ljk2NCw2NS4yMSwzOC44MTZ6IE02Mi40NzksMzguNzg4YzAuMTc0LDAsMC4zNDksMC4wMDEsMC41MjMsMC4wMDRjLTAuMDAzLDAuMjUzLTAuMDAyLDAuNTA3LDAuMDAxLDAuNzZjMC4yNCwwLjAwNywwLjQ3OSwwLjAwNywwLjcyMiwwLjAwMmMwLjAwMi0wLjI1NCwwLjAwNC0wLjUwOCwwLjAwMi0wLjc2MWMwLjE3NC0wLjAwMywwLjM1MS0wLjAwNSwwLjUyNC0wLjAwNWMwLjAxMSwwLjY4NSwwLjAxMSwxLjM2OSwwLDIuMDUyYy0wLjE3NSwwLTAuMzUxLTAuMDAyLTAuNTI2LTAuMDA0YzAuMDA0LTAuMjc1LDAuMDA0LTAuNTUtMC4wMDItMC44MjVjLTAuMjM4LTAuMDA3LTAuNDc5LTAuMDA3LTAuNzE4LDBjLTAuMDA1LDAuMjc1LTAuMDA2LDAuNTUtMC4wMDMsMC44MjVjLTAuMTc1LDAuMDAxLTAuMzUsMC4wMDMtMC41MjMsMC4wMDNDNjIuNDY5LDQwLjE1Nyw2Mi40NjksMzkuNDcyLDYyLjQ3OSwzOC43ODh6IE02MC4xNywzOC43ODhjMC4xNzIsMCwwLjM0NiwwLjAwMiwwLjUxOCwwLjAwNmMwLjAwMiwwLjM5My0wLjAxOCwwLjc4OSwwLjAxNCwxLjE4MWMwLjI5Ni0wLjM2OSwwLjUyMS0wLjc4OCwwLjc4My0xLjE4YzAuMTc1LTAuMDA1LDAuMzQ5LTAuMDA3LDAuNTIyLTAuMDA4YzAuMDExLDAuNjg2LDAuMDExLDEuMzcsMCwyLjA1NWMtMC4xNzQtMC4wMDEtMC4zNDYtMC4wMDQtMC41MTktMC4wMDdjMC0wLjM3NiwwLjAxNC0wLjc1Mi0wLjAxMi0xLjEyOGMtMC4wNTcsMC4wNjQtMC4xMDksMC4xMy0wLjE2MiwwLjE5N2MwLDAuMDA0LTAuMDA2LDAuMDA5LTAuMDA4LDAuMDEyYy0wLjIwMSwwLjMwOS0wLjQxMiwwLjYxMi0wLjYxNSwwLjkxOWMtMC4xNzQsMC4wMDMtMC4zNDksMC4wMDYtMC41MjEsMC4wMDdDNjAuMTYyLDQwLjE1Nyw2MC4xNjIsMzkuNDcyLDYwLjE3LDM4Ljc4OHogTTU5LjU0OSwzOS43NTdjMC4zMDksMC4yMDYsMC4zNDgsMC43MDIsMC4wMjUsMC45MTVjLTAuNDQ4LDAuMjgxLTEuMDk4LDAuMjIyLTEuNDgtMC4xNWMwLjA5Ni0wLjExLDAuMTkyLTAuMjE4LDAuMjk1LTAuMzIzYzAuMjUsMC4xNTksMC42ODgsMC4zMzcsMC44NzMsMGMwLjA0My0wLjMxNC0wLjM3MS0wLjI0OS0wLjU2OC0wLjI2NGMwLTAuMTIzLDAtMC4yNDYsMC0wLjM2OGMwLjE4Ny0wLjAwMSwwLjM3My0wLjAyMSwwLjU1MS0wLjA3M2MtMC4wMTgtMC40MzItMC41NjMtMC4yMTgtMC44MDEtMC4wOTdjLTAuMDg0LTAuMTEyLTAuMTY0LTAuMjIzLTAuMjQ0LTAuMzM2YzAuMzktMC4yOTEsMC45NzItMC4zOTMsMS4zOTgtMC4xMjdDNTkuODk4LDM5LjEzLDU5LjgxNCwzOS41NjgsNTkuNTQ5LDM5Ljc1N3ogTTU3Ljc2NCwzNS4wMTNjMC4yMDctMC4wMDgsMC40MTItMC4wMDgsMC42MiwwLjAwMWMwLjIwNiwwLjYyMywwLjQ4NCwxLjIyMywwLjY1MywxLjg1NWMwLjAxNCwwLjAzMiwwLjAzOSwwLjA5NSwwLjA1MywwLjEyN2wwLjA1OSwwLjA0MmMtMC4xODgsMC4wMjEtMC4zNzksMC4wMjItMC41NjcsMC4wMTljLTAuMDQ4LTAuMTI1LTAuMDk1LTAuMjQ4LTAuMTQ0LTAuMzcyYy0wLjI1LTAuMDE2LTAuNS0wLjAxNy0wLjc0OC0wLjAwN2MtMC4wMzcsMC4xMjItMC4wNzIsMC4yNDQtMC4xMDcsMC4zNjZjLTAuMTg2LDAuMDEyLTAuMzcyLDAuMDE2LTAuNTU5LDAuMDEzQzU3LjI1NSwzNi4zNzEsNTcuNTIxLDM1LjY5Nyw1Ny43NjQsMzUuMDEzeiBNNTQuODU5LDQwLjgzNmMtMC4xNzQsMC4wMDEtMC4zNDgsMC4wMDMtMC41MjIsMC4wMDRjLTAuMDA5LTAuNjg0LTAuMDA5LTEuMzY4LDAtMi4wNTFjMC41MDctMC4wMDIsMS4wMTMtMC4wMDEsMS41MjEsMGMwLDAuMTQ5LDAsMC4yOTgsMCwwLjQ0OGMtMC4zMywwLjAwMS0wLjY2Mi0wLjAwMi0wLjk5NCwwLjAwNUM1NC44NTQsMzkuNzczLDU0Ljg1OSw0MC4zMDUsNTQuODU5LDQwLjgzNnogTTU2LjU0NSwzOC43OTVjMC4yMDYtMC4wMDgsMC40MTQtMC4wMDgsMC42MiwwLjAwMmMwLjIwNywwLjYyMiwwLjQ4NSwxLjIyMiwwLjY1NSwxLjg1NWMwLjAxMywwLjAzMiwwLjAzOSwwLjA5NSwwLjA1MSwwLjEyNmwwLjA2MSwwLjA0M2MtMC4xOSwwLjAyMS0wLjM3OSwwLjAyMS0wLjU3LDAuMDE4Yy0wLjA0Ny0wLjEyNS0wLjA5NC0wLjI0OC0wLjE0MS0wLjM3MmMtMC4yNS0wLjAxNi0wLjUtMC4wMTctMC43NS0wLjAwN2MtMC4wMzUsMC4xMjItMC4wNzIsMC4yNDQtMC4xMDcsMC4zNjZjLTAuMTg2LDAuMDExLTAuMzcxLDAuMDE1LTAuNTU5LDAuMDEzQzU2LjAzNyw0MC4xNTMsNTYuMzA1LDM5LjQ3OSw1Ni41NDUsMzguNzk1eiBNNTUuMDQyLDM1LjAwNmMwLjE3MywwLDAuMzQ1LDAuMDAyLDAuNTE5LDAuMDA1Yy0wLjAwNCwwLjMwMi0wLjAwOCwwLjYwNSwwLjAxLDAuOTA5YzAuMjQtMC4yODYsMC40MzktMC42MDQsMC42Ni0wLjkwNmMwLjIwOS0wLjAwOCwwLjQyMi0wLjAwOSwwLjYzMS0wLjAwNGMtMC4yMTksMC4zNDYtMC41MzksMC42NDEtMC43MDIsMS4wMTRjMC4yMDQsMC4zNiwwLjQ3MywwLjY4MSwwLjY5OCwxLjAzYy0wLjIyNC0wLjAwOS0wLjQ2MywwLjA0Ni0wLjY3Mi0wLjAzMWMtMC4yMTktMC4yOS0wLjM2OS0wLjYyNS0wLjYxMS0wLjg5N2MtMC4wMjQsMC4zMDktMC4wMTgsMC42MTktMC4wMTQsMC45MjhjLTAuMTc0LDAuMDAyLTAuMzQ2LDAuMDA0LTAuNTE5LDAuMDA2QzU1LjAzMywzNi4zNzUsNTUuMDMzLDM1LjY5LDU1LjA0MiwzNS4wMDZ6IE01Mi44NjMsMzUuMDA1YzAuMTcxLDAuMDAxLDAuMzQ1LDAuMDAzLDAuNTE3LDAuMDA2YzAuMDAyLDAuMzk0LTAuMDE3LDAuNzg5LDAuMDEzLDEuMTgyYzAuMjk3LTAuMzcsMC41MjEtMC43ODksMC43ODUtMS4xODFjMC4xNzQtMC4wMDUsMC4zNDgtMC4wMDcsMC41MjItMC4wMDhjMC4wMDksMC42ODYsMC4wMDksMS4zNywwLDIuMDU1Yy0wLjE3NS0wLjAwMS0wLjM0Ny0wLjAwNC0wLjUyMS0wLjAwN2MwLTAuMzc2LDAuMDE2LTAuNzUyLTAuMDExLTEuMTI3Yy0wLjA1OCwwLjA2NC0wLjExLDAuMTMtMC4xNjEsMC4xOTdjLTAuMDAzLDAuMDAzLTAuMDA3LDAuMDA5LTAuMDA4LDAuMDEyYy0wLjIwMywwLjMwOC0wLjQxMSwwLjYxMi0wLjYxMywwLjkxOGMtMC4xNzYsMC4wMDMtMC4zNTEsMC4wMDUtMC41MjMsMC4wMDdDNTIuODU0LDM2LjM3NSw1Mi44NTQsMzUuNjksNTIuODYzLDM1LjAwNXogTTUzLjMxOCwzOC43OTZjMC4yMDcsMC42MjMsMC40ODYsMS4yMjMsMC42NTQsMS44NTZjMC4wMTIsMC4wMzEsMC4wMzksMC4wOTQsMC4wNTMsMC4xMjZsMC4wNTksMC4wNDJjLTAuMTg4LDAuMDIxLTAuMzc4LDAuMDIxLTAuNTY4LDAuMDE4Yy0wLjA0Ny0wLjEyNS0wLjA5NS0wLjI0OC0wLjE0Mi0wLjM3MmMtMC4yNS0wLjAxNi0wLjUtMC4wMTctMC43NS0wLjAwN2MtMC4wMzUsMC4xMjItMC4wNzEsMC4yNDQtMC4xMDYsMC4zNjZjLTAuMTg3LDAuMDExLTAuMzczLDAuMDE1LTAuNTYxLDAuMDEzYzAuMjM2LTAuNjg2LDAuNS0xLjM2MSwwLjc0LTIuMDQ1QzUyLjkwNiwzOC43ODcsNTMuMTExLDM4Ljc4Nyw1My4zMTgsMzguNzk2eiBNNTAuODU0LDM1LjAxNWMwLjU2Ni0wLjAxNiwxLjEzNS0wLjAxLDEuNzAxLTAuMDA0Yy0wLjAwMiwwLjE0Ni0wLjAwMiwwLjI5MywwLDAuNDRjLTAuMTk1LDAuMDAyLTAuMzkxLDAuMDA1LTAuNTg0LDAuMDA3Yy0wLjAwOSwwLjUzMi0wLjAwMywxLjA2NC0wLjAwMiwxLjU5NWMtMC4xNzMsMC4wMDItMC4zNDYsMC4wMDQtMC41MTcsMC4wMDZjLTAuMDExLTAuNTMyLTAuMDA3LTEuMDY0LTAuMDAzLTEuNTk2Yy0wLjE5OC0wLjAwNi0wLjM5Ni0wLjAxMS0wLjU5Ni0wLjAxOEM1MC44NTUsMzUuMzAyLDUwLjg1NSwzNS4xNTksNTAuODU0LDM1LjAxNXogTTUxLjE1NCwzOS45MTFjLTAuMDIyLDAuMDMtMC4wNywwLjA5MS0wLjA5NCwwLjEyMmMtMC4wMDIsMC4wMDMtMC4wMDYsMC4wMDktMC4wMSwwLjAxMmMtMC4wNDQsMC4wNzgtMC4wODYsMC4xNTgtMC4xMjUsMC4yNGMtMC4wMDIsMC4wMDMtMC4wMDUsMC4wMS0wLjAwNiwwLjAxNGMtMC4wODQsMC4xNzgtMC4xNzgsMC4zNTMtMC4yODEsMC41MjFjLTAuMTk4LTAuMzA3LTAuMzIzLTAuNjUxLTAuNTIxLTAuOTU3Yy0wLjAyOSwwLjMyMy0wLjAxOSwwLjY0Ny0wLjAxMywwLjk3MWMtMC4xNjksMC4wMDQtMC4zMzcsMC4wMDUtMC41MDYsMC4wMDdjLTAuMDA3LTAuNjg0LTAuMDA3LTEuMzY4LDAtMi4wNTJjMC4xNzksMC4wMjQsMC40MjgtMC4wNzYsMC41NTQsMC4wOTFjMC4wMDMsMC4wMDIsMC4wMDYsMC4wMDksMC4wMDgsMC4wMTFjMC4xNywwLjMwOCwwLjMwNSwwLjYzMywwLjQ3OCwwLjkzOWMwLjE5MS0wLjMzNiwwLjM1Mi0wLjY4OSwwLjUyNC0xLjAzNWMwLjE3NS0wLjAwNiwwLjM0OS0wLjAwOCwwLjUyMi0wLjAxYzAuMDEsMC42ODUsMC4wMSwxLjM3LDAsMi4wNTVjLTAuMTc0LTAuMDAyLTAuMzQ2LTAuMDA0LTAuNTIxLTAuMDA4QzUxLjE3LDQwLjUyNyw1MS4xODIsNDAuMjE4LDUxLjE1NCwzOS45MTF6IE00OS4xMjQsMzUuNTMzYzAuMjM0LTAuNjI0LDEuMjI3LTAuNzMzLDEuNTg3LTAuMTc1Yy0wLjExMywwLjA5Mi0wLjIyOSwwLjE4Ni0wLjM0MiwwLjI4Yy0wLjE3Ni0wLjExNC0wLjQxOS0wLjI4NC0wLjYxLTAuMWMtMC4yNTIsMC4yNDgtMC4yNDUsMC43MzgsMC4wMDIsMC45ODhjMC4yMDEsMC4xODgsMC40NTEsMC4wMSwwLjYyNi0wLjExOWMwLjEyNSwwLjA5MywwLjI1LDAuMTg5LDAuMzcyLDAuMjg2Yy0wLjMyNywwLjQxNC0wLjk4NywwLjUtMS4zOTYsMC4xNjZDNDguOTk4LDM2LjUzNCw0OC45NzYsMzUuOTY5LDQ5LjEyNCwzNS41MzN6IE00OS4xNzUsMzkuNTYzYzAsMC4xNDYsMCwwLjI5MiwwLDAuNDM5Yy0wLjMwOCwwLjAwNy0wLjYxNiwwLjAwNy0wLjkyMywwLjAwMWMwLTAuMTQ3LDAtMC4yOTQsMC0wLjQ0MkM0OC41NTksMzkuNTU1LDQ4Ljg2NywzOS41NTUsNDkuMTc1LDM5LjU2M3ogTTQ2Ljc3NCwzNS4wMDVjMC4xNzIsMC4wMDEsMC4zNDUsMC4wMDMsMC41MTcsMC4wMDZjMC4wMDIsMC4zOTQtMC4wMTYsMC43ODgsMC4wMTQsMS4xODNjMC4yOTYtMC4zNywwLjUyMS0wLjc4OSwwLjc4My0xLjE4MWMwLjE3NC0wLjAwNSwwLjM1LTAuMDA3LDAuNTI0LTAuMDA4YzAuMDA5LDAuNjg2LDAuMDA5LDEuMzcsMCwyLjA1NWMtMC4xNzQtMC4wMDItMC4zNDctMC4wMDQtMC41Mi0wLjAwN2MwLTAuMzc2LDAuMDE3LTAuNzUyLTAuMDE0LTEuMTI3Yy0wLjI5LDAuMzU0LTAuNTE5LDAuNzUzLTAuNzgxLDEuMTI3Yy0wLjE3NSwwLjAwMy0wLjM1LDAuMDA1LTAuNTIzLDAuMDA3QzQ2Ljc2NiwzNi4zNzUsNDYuNzY2LDM1LjY5LDQ2Ljc3NCwzNS4wMDV6IE00Ny4zNTQsNDAuODM2Yy0wLjE3MiwwLjAwMi0wLjM0NCwwLjAwNC0wLjUxNywwLjAwNWMtMC4wMTEtMC41MzEtMC4wMDUtMS4wNjMtMC4wMDEtMS41OTRjLTAuMTk5LTAuMDA3LTAuMzk5LTAuMDEzLTAuNTk4LTAuMDE5YzAuMDAxLTAuMTQ0LDAuMDAxLTAuMjg3LDAtMC40MzFjMC41NjYtMC4wMTYsMS4xMzMtMC4wMDksMS43LTAuMDAzYy0wLjAwMSwwLjE0Ni0wLjAwMSwwLjI5MiwwLDAuNDM5Yy0wLjE5NSwwLjAwMi0wLjM4OSwwLjAwNi0wLjU4MywwLjAwN0M0Ny4zNDgsMzkuNzcyLDQ3LjM1NCw0MC4zMDQsNDcuMzU0LDQwLjgzNnogTTQ0LjkxNSwzNS4wMDhjMC41MDctMC4wMDMsMS4wMTMtMC4wMDIsMS41MjEtMC4wMDFjMCwwLjE1LDAsMC4yOTksMCwwLjQ0OGMtMC4zMzEsMC0wLjY2My0wLjAwMi0wLjk5NCwwLjAwNWMtMC4wMDgsMC41MzEtMC4wMDEsMS4wNjMtMC4wMDEsMS41OTRjLTAuMTc2LDAuMDAxLTAuMzUxLDAuMDA0LTAuNTI1LDAuMDA0QzQ0LjkwNiwzNi4zNzUsNDQuOTA2LDM1LjY5MSw0NC45MTUsMzUuMDA4eiBNNDQuOTksMzkuNTUyYzAuMjMyLDAuMDA0LDAuNDY2LDAuMDA3LDAuNjk5LDAuMDExYy0wLjAwMSwwLjE0Ni0wLjAwMSwwLjI5MiwwLDAuNDM4Yy0wLjIzMywwLjAwNS0wLjQ2NiwwLjAwNy0wLjY5OSwwLjAxYy0wLjAwMSwwLjEyNS0wLjAwMSwwLjI1LDAsMC4zNzVjMC4zNDgsMC4wMDcsMC42OTUsMC4wMDEsMS4wNDMsMC4wMDhjMCwwLjE0NiwwLDAuMjkzLDAuMDAxLDAuNDRjLTAuNTI0LDAuMDA5LTEuMDQ2LDAuMDA0LTEuNTY4LDAuMDAyYy0wLjAwOS0wLjY4My0wLjAwOS0xLjM2NSwwLTIuMDQ4YzAuNTEyLDAsMS4wMjQtMC4wMDksMS41MzgsMC4wMDdjLTAuMDAyLDAuMTQ1LTAuMDAyLDAuMjg5LTAuMDAxLDAuNDM0Yy0wLjMzOCwwLjAxMS0wLjY3NiwwLjAwNC0xLjAxNCwwLjAxMkM0NC45OSwzOS4zNDUsNDQuOTksMzkuNDQ5LDQ0Ljk5LDM5LjU1MnogTTQzLjM1MiwzNS4wMzVjMC4zNjgtMC4wOSwwLjgxNi0wLjAwNiwxLjAzOCwwLjMyN2MwLjIyOCwwLjM1OSwwLjI0LDAuODM5LDAuMDcsMS4yMjNjLTAuMjk0LDAuNjQ3LTEuMzksMC42NDYtMS42NjctMC4wMTVDNDIuNTY4LDM2LjAyOSw0Mi42ODYsMzUuMTgyLDQzLjM1MiwzNS4wMzV6IE00Mi44MTEsMzguNzkyYy0wLjAwMywwLjI1NC0wLjAwMSwwLjUwOCwwLjAwMiwwLjc2MWMwLjI0LDAuMDA3LDAuNDgsMC4wMDgsMC43MiwwLjAwMmMwLjAwMy0wLjI1NCwwLjAwNC0wLjUwOCwwLjAwMS0wLjc2MmMwLjE3NS0wLjAwMiwwLjM1MS0wLjAwNCwwLjUyNi0wLjAwNGMwLjAxLDAuNjg0LDAuMDEsMS4zNjksMCwyLjA1M2MtMC4xNzUtMC4wMDEtMC4zNTEtMC4wMDMtMC41MjctMC4wMDVjMC4wMDQtMC4yNzUsMC4wMDItMC41NTEtMC4wMDEtMC44MjZjLTAuMjQtMC4wMDYtMC40NzktMC4wMDYtMC43MiwwLjAwMWMtMC4wMDQsMC4yNzUtMC4wMDQsMC41NS0wLjAwMiwwLjgyNWMtMC4xNzQsMC4wMDEtMC4zNSwwLjAwMy0wLjUyNCwwLjAwM2MtMC4wMDgtMC42ODQtMC4wMDgtMS4zNjgsMC0yLjA1MkM0Mi40NjEsMzguNzg4LDQyLjYzNiwzOC43ODksNDIuODExLDM4Ljc5MnogTTQwLjQ5MywzNi42NzhjMC4xMjEtMC4wNjEsMC4zMTUtMC4wOTYsMC4zMTEtMC4yNjljMC4wMzEtMC40NjYtMC4wMDMtMC45MzMsMC4wMTYtMS40YzAuNDg5LTAuMDAyLDAuOTc5LTAuMDA5LDEuNDY3LDAuMDA2Yy0wLjAwNiwwLjY3OS0wLjAwNiwxLjM1OSwwLDIuMDM4Yy0wLjE3OCwwLjAwMy0wLjM1NiwwLjAwMy0wLjUzNC0wLjAwMWMwLjAwNi0wLjUzNCwwLjAxLTEuMDY3LDAtMS42Yy0wLjE1LDAuMDA0LTAuMjk5LDAuMDA3LTAuNDQ4LDAuMDExYy0wLjAxMywwLjQyOCwwLjA3MiwwLjg3Ni0wLjA4NCwxLjI4NWMtMC4xMjEsMC4yODctMC40NTQsMC4zMzEtMC43MjgsMC4zMTVDNDAuNDkxLDM2LjkzNiw0MC40OTEsMzYuODA2LDQwLjQ5MywzNi42Nzh6IE00MC4zNjQsMzguNzljMC40MDMsMC4wMTQsMC44MTQtMC4wNCwxLjIxMywwLjAzM2MwLjQ5MSwwLjExNSwwLjU5OCwwLjgyNywwLjIzNSwxLjEzN2MtMC4yNTEsMC4yMzEtMC42MTcsMC4xNjUtMC45MjksMC4xNzhjLTAuMDA1LDAuMjMyLTAuMDA1LDAuNDY1LTAuMDAxLDAuNjk4Yy0wLjE3MywwLjAwMi0wLjM0NiwwLjAwNC0wLjUxOCwwLjAwNUM0MC4zNTYsNDAuMTU3LDQwLjM1NiwzOS40NzMsNDAuMzY0LDM4Ljc5eiBNMzguNTA0LDM4Ljc5YzAuNTEyLDAsMS4wMjUtMC4wMDksMS41MzgsMC4wMDdjLTAuMDAyLDAuMTQ1LTAuMDAyLDAuMjg5LDAsMC40MzRjLTAuMzM4LDAuMDExLTAuNjc1LDAuMDA0LTEuMDE0LDAuMDEyYzAsMC4xMDMsMCwwLjIwNywwLDAuMzFjMC4yMzMsMC4wMDQsMC40NjYsMC4wMDcsMC43LDAuMDExYy0wLjAwMiwwLjE0Ni0wLjAwMiwwLjI5MiwwLDAuNDM4Yy0wLjIzNCwwLjAwNS0wLjQ2NywwLjAwNy0wLjcsMC4wMWMwLDAuMTI1LDAsMC4yNSwwLDAuMzc1YzAuMzQ5LDAuMDA3LDAuNjk2LDAuMDAxLDEuMDQ0LDAuMDA4YzAsMC4xNDYsMCwwLjI5MywwLDAuNDRjLTAuNTIzLDAuMDA5LTEuMDQ1LDAuMDA0LTEuNTY5LDAuMDAyQzM4LjQ5Nyw0MC4xNTUsMzguNDk3LDM5LjQ3MywzOC41MDQsMzguNzl6IE0zNi41NjEsMzguNzk3YzAuNTY2LTAuMDE2LDEuMTMzLTAuMDA5LDEuNjk5LTAuMDAzYzAsMC4xNDYsMCwwLjI5MiwwLjAwMSwwLjQzOGMtMC4xOTQsMC4wMDMtMC4zODksMC4wMDctMC41ODMsMC4wMDhjLTAuMDA5LDAuNTMyLTAuMDAzLDEuMDY0LTAuMDAyLDEuNTk2Yy0wLjE3MiwwLjAwMi0wLjM0NCwwLjAwNC0wLjUxNywwLjAwNWMtMC4wMS0wLjUzMS0wLjAwNS0xLjA2MywwLTEuNTk0Yy0wLjItMC4wMDctMC4zOTktMC4wMTMtMC41OTgtMC4wMTlDMzYuNTYxLDM5LjA4NCwzNi41NjEsMzguOTQxLDM2LjU2MSwzOC43OTd6IE0zNC40NjcsMzguNzg4YzAuMTc0LDAsMC4zNDksMC4wMDEsMC41MjQsMC4wMDNjLTAuMDA0LDAuMjU0LTAuMDAyLDAuNTA3LDAuMDAyLDAuNzYxYzAuMjM5LDAuMDA3LDAuNDc5LDAuMDA4LDAuNzE5LDAuMDAyYzAuMDAzLTAuMjU0LDAuMDA1LTAuNTA4LDAuMDAxLTAuNzYyYzAuMTc1LTAuMDAyLDAuMzUxLTAuMDA0LDAuNTI3LTAuMDA0YzAuMDEsMC42ODMsMC4wMSwxLjM2OCwwLDIuMDUyYy0wLjE3NiwwLTAuMzUyLTAuMDAyLTAuNTI3LTAuMDA0YzAuMDA0LTAuMjc1LDAuMDAyLTAuNTUxLTAuMDAxLTAuODI2Yy0wLjIzOS0wLjAwNi0wLjQ3OS0wLjAwNi0wLjcxOSwwLjAwMWMtMC4wMDUsMC4yNzUtMC4wMDUsMC41NS0wLjAwMiwwLjgyNWMtMC4xNzUsMC4wMDEtMC4zNSwwLjAwMy0wLjUyNCwwLjAwM0MzNC40NTgsNDAuMTU3LDM0LjQ1OCwzOS40NzIsMzQuNDY3LDM4Ljc4OHogTTMyLjIyNCwzOC43ODhjMC4xNzIsMCwwLjM0NCwwLjAwMSwwLjUxNywwLjAwNWMwLjAwMywwLjM5NC0wLjAxNiwwLjc4OSwwLjAxNCwxLjE4M2MwLjI5Ni0wLjM3LDAuNTItMC43ODksMC43ODMtMS4xODFjMC4xNzUtMC4wMDUsMC4zNS0wLjAwNywwLjUyNC0wLjAwOGMwLjAwOSwwLjY4NSwwLjAwOSwxLjM3LDAsMi4wNTRjLTAuMTczLTAuMDAxLTAuMzQ3LTAuMDAzLTAuNTItMC4wMDZjMC0wLjM3NiwwLjAxNS0wLjc1Mi0wLjAxMS0xLjEyN2MtMC4wNTYsMC4wNjQtMC4xMSwwLjEzLTAuMTYxLDAuMTk3Yy0wLjAwMywwLjAwMy0wLjAwNiwwLjAwOC0wLjAwOCwwLjAxMmMtMC4yMDIsMC4zMDgtMC40MDksMC42MTItMC42MTQsMC45MThjLTAuMTc1LDAuMDAzLTAuMzQ5LDAuMDA1LTAuNTIzLDAuMDA2QzMyLjIxNSw0MC4xNTcsMzIuMjE1LDM5LjQ3MiwzMi4yMjQsMzguNzg4eiBNNzMuMzc4LDYwLjg0MWMtMC4wMjEsMC40NTUsMC4wMywwLjk1NC0wLjIzMSwxLjM1NGMtMC4yMDEsMC4zMjItMC41MzksMC41MzEtMC44OTgsMC42MzVjLTcuMDMyLDIuMzItMTQuMDYxLDQuNjU2LTIxLjA5MSw2Ljk4NGMtMC4yNzEsMC4xMDEtMC41NSwwLjE4Ny0wLjg0LDAuMjIxYy0wLjE1My0wLjAyNC0wLjMwNi0wLjA0NC0wLjQ1Ny0wLjA4N2MtNy4yMDQtMi4zOS0xNC40MS00Ljc3Ni0yMS42MTYtNy4xNjNjLTAuNTY0LTAuMTY0LTAuOTg0LTAuNzEtMC45ODItMS4zMDFjLTAuMjU5LTYuMzM2LTAuNTc5LTEyLjY2OS0wLjgxOC0xOS4wMDVjNy45NTgtMC4wMTEsMTUuOTE2LTAuMDA5LDIzLjg3NCwwYzcuOTU2LTAuMDA1LDE1LjkxMS0wLjAxNCwyMy44NjYsMC4wMDRDNzMuOTIzLDQ4LjYwMyw3My42NDMsNTQuNzIzLDczLjM3OCw2MC44NDF6Ii8+PHBhdGggZmlsbD0iIzNDNDY1NiIgZD0iTTQzLjUzOSwzNS40NjRjLTAuNDI0LDAuMTc3LTAuNDIyLDAuOTU0LDAsMS4xMzZDNDQuMTk1LDM2LjcwMyw0NC4yMDgsMzUuMzU3LDQzLjUzOSwzNS40NjR6Ii8+PHBhdGggZmlsbD0iIzNDNDY1NiIgZD0iTTU4LjAwNywzNS42OTdjLTAuMDQxLDAuMTk0LTAuMDgyLDAuMzktMC4xNjcsMC41N2MwLjE1MiwwLjAxNiwwLjMwNywwLjAyMiwwLjQ2MSwwLjAyNGMtMC4wNy0wLjI2MS0wLjE1Ni0wLjUxOC0wLjI1NS0wLjc3QzU4LjAzNywzNS41NjYsNTguMDE4LDM1LjY1Myw1OC4wMDcsMzUuNjk3eiIvPjxwYXRoIGZpbGw9IiMzQzQ2NTYiIGQ9Ik0yMi45NDEsNDguNDQ5Yy0wLjAwMi0wLjAzOS0wLjAwOC0wLjExNy0wLjAxLTAuMTU1Yy0wLjA0Ny0wLjQ2MS0wLjA3NS0wLjkyMy0wLjA1NC0xLjM4NmMtMC4wMDUtMC4wNzItMC4wMS0wLjE0My0wLjAxNC0wLjIxNGMtMC4wNDEtMC40MTktMC4wNjgtMC44NC0wLjA1MS0xLjI2MWMtMC4xMjMtMi4yNzgtMC4xNjEtNC41Ni0wLjMtNi44MzhjLTIuMDM0LTAuMDAxLTQuMDY4LDAuMDA3LTYuMTAzLTAuMDA0djAuMDM4YzEuMDA4LDIuMDA3LDEuOTUxLDQuMDQ1LDIuOTQsNi4wNjFjLTAuOTUzLDEuOTU0LTEuOTY4LDMuODc2LTIuOTA4LDUuODM1YzIuMTg5LDAuMDEzLDQuMzc4LDAuMDA0LDYuNTY4LDAuMDAyYy0wLjAwMi0wLjIzLTAuMDExLTAuNDYyLTAuMDE4LTAuNjkzQzIyLjk1Miw0OS4zNzQsMjIuOTE3LDQ4LjkxMiwyMi45NDEsNDguNDQ5eiIvPjxwYXRoIGZpbGw9IiMzQzQ2NTYiIGQ9Ik04NC4xOTEsMzguNjAxYy0yLjA0NS0wLjAxLTQuMDktMC4wMDItNi4xMzUtMC4wMDJjLTAuMTY4LDMuOTc3LTAuMzIyLDcuOTU0LTAuNSwxMS45M2MyLjIwNywwLjAwMyw0LjQxNiwwLjAxMSw2LjYyMy0wLjAwMmMtMC44ODMtMS44NzUtMS43OTMtMy43MzYtMi42OTEtNS42MDRjLTAuMDc2LTAuMTk2LTAuMjc1LTAuNDA4LTAuMTUtMC42MjNDODIuMjkxLDQyLjQwMSw4My4yNTIsNDAuNTA2LDg0LjE5MSwzOC42MDF6Ii8+PHBhdGggZmlsbD0iIzNDNDY1NiIgZD0iTTQwLjg4MSwzOS4yMzRjMCwwLjE1MSwwLDAuMzA0LDAsMC40NTVjMC4xOTUtMC4wMjEsMC41NTIsMC4wNzMsMC41OTktMC4xOTZDNDEuNTA2LDM5LjE2OSw0MS4wODcsMzkuMjQ2LDQwLjg4MSwzOS4yMzR6Ii8+PHBhdGggZmlsbD0iIzNDNDY1NiIgZD0iTTY1LjM5NiwzOS4yNDZjLTAuNDI0LDAuMTc4LTAuNDIyLDAuOTU0LDAsMS4xMzZDNjYuMDUzLDQwLjQ4NCw2Ni4wNjQsMzkuMTM4LDY1LjM5NiwzOS4yNDZ6Ii8+PHBhdGggZmlsbD0iIzNDNDY1NiIgZD0iTTY3LjI5NiwzOS4yNGMwLDAuMTA0LDAsMC4yMSwwLDAuMzE1YzAuMTc1LTAuMDI1LDAuNDc0LDAuMDg4LDAuNTUyLTAuMTI4QzY3Ljg1NSwzOS4xMzksNjcuNDc1LDM5LjI2LDY3LjI5NiwzOS4yNHoiLz48cGF0aCBmaWxsPSIjM0M0NjU2IiBkPSJNNTIuOTQzLDM5LjQ3OWMtMC4wNDMsMC4xOTQtMC4wODIsMC4zOTEtMC4xNjgsMC41NzFjMC4xNTQsMC4wMTYsMC4zMDgsMC4wMjIsMC40NjEsMC4wMjRjLTAuMDctMC4yNjEtMC4xNTYtMC41MTgtMC4yNTQtMC43N0M1Mi45NzUsMzkuMzQ5LDUyLjk1NCwzOS40MzYsNTIuOTQzLDM5LjQ3OXoiLz48cGF0aCBmaWxsPSIjM0M0NjU2IiBkPSJNNTYuNzg5LDM5LjQ3OWMtMC4wNDIsMC4xOTQtMC4wODEsMC4zOTEtMC4xNjYsMC41N2MwLjE1LDAuMDE2LDAuMzA1LDAuMDIyLDAuNDYsMC4wMjVjLTAuMDcxLTAuMjYxLTAuMTU4LTAuNTE4LTAuMjU0LTAuNzY5QzU2LjgyLDM5LjM0OSw1Ni44LDM5LjQzNSw1Ni43ODksMzkuNDc5eiIvPjxwYXRoIGZpbGw9IiMzQzQ2NTYiIGQ9Ik02Ny43OTUsMzkuOTcyYy0wLjE2MS0wLjA1MS0wLjMzMy0wLjAyNS0wLjQ5OS0wLjAyN2MwLDAuMTQ3LDAsMC4yOTUsMCwwLjQ0M2MwLjE2Ni0wLjAwMiwwLjMzOCwwLjAyNCwwLjUtMC4wMjVDNjcuOTY2LDQwLjMxLDY3Ljk3MSw0MC4wMjIsNjcuNzk1LDM5Ljk3MnoiLz48cGF0aCBmaWxsPSIjM0M0NjU2IiBkPSJNNjguNTI3LDU4LjU1N2MtMS42NjgtMC4wMDgtMy4zMzgsMC01LjAwNi0wLjAwMmMtMC40MDIsMC4wMjQtMC44MTMtMC4zMS0wLjgxMS0wLjcyNWMwLTQuMDQxLDAuMDAzLTguMDgsMC0xMi4xMTljMC4wMjMtMC40Mi0wLjM4NS0wLjc2MS0wLjc4OC0wLjc0N2MtMC44NDgsMC4wMDgtMS42OTYtMC4wMTktMi41NDMsMC4wMTRjLTAuMzg1LDAuMDQyLTAuNjk0LDAuNDA1LTAuNjY1LDAuNzkzYy0wLjAwMiw0Ljk1OS0wLjAwMSw5LjkxOCwwLDE0Ljg3OWMtMC4wNDMsMC40MzksMC4zMTQsMC44NzEsMC43NywwLjg1MmMzLjAxNCwwLjAwMyw2LjAyNCwwLjAwMiw5LjAzNywwLjAwMmMwLjM3OSwwLjAyLDAuNzY4LTAuMjcxLDAuNzgxLTAuNjY2YzAuMDIyLTAuNTExLDAuMDE0LTEuMDI1LDAuMDA4LTEuNTM4QzY5LjMyNiw1OC44ODQsNjguOTI4LDU4LjU1LDY4LjUyNyw1OC41NTd6Ii8+PHBhdGggZmlsbD0iIzNDNDY1NiIgZD0iTTM4LjgyMyw2MC4yNjRjLTAuMDAxLTQuODU0LDAtOS43MDUsMC0xNC41NTZjMC4wMS0wLjQ0Ny0wLjQ1NC0wLjc2OS0wLjg3Ni0wLjcxN2MtMC44NjcsMC4wMzMtMS43NDUtMC4wNTYtMi42MDYsMC4wNDdjLTAuMjc5LDAuMDk5LTAuNTA1LDAuMzY3LTAuNDkzLDAuNjc0Yy0wLjAwMyw1LjAyMSwwLDEwLjA0Mi0wLjAwMSwxNS4wNjNjLTAuMDIyLDAuMzk2LDAuMzQzLDAuNzI5LDAuNzI5LDAuNzI3YzAuODExLDAuMDA4LDEuNjIzLTAuMDAxLDIuNDM1LDAuMDA0YzAuMzM4LDAuMDI1LDAuNjg0LTAuMTkxLDAuNzg3LTAuNTE4QzM4Ljg0Niw2MC43NDksMzguODE3LDYwLjUwMiwzOC44MjMsNjAuMjY0eiIvPjxwYXRoIGZpbGw9IiMzQzQ2NTYiIGQ9Ik01Ny4wODgsNDUuNzcyYzAuMDItMC40MzUtMC4zODctMC43NzItMC44MDUtMC43NjFjLTAuOTQtMC4wMTUtMS44ODMsMC4wMDQtMi44MjQtMC4wMDhjLTAuMjgxLDAuMDAxLTAuNjAyLDAuMDAxLTAuODIsMC4yMDhjLTAuMjAzLDAuMjAyLTAuMjg3LDAuNDg3LTAuNDA4LDAuNzRjLTAuNjMxLDEuNC0xLjI1LDIuODA3LTEuODk2LDQuMjAxYy0wLjQ3NCwxLjAxNi0wLjkzNCwyLjA0Mi0xLjQ3MiwzLjAyNGMtMS4xNjMtMi4zNjUtMi4yMjUtNC43NzYtMy4zMTktNy4xNzNjLTAuMTI1LTAuMjU3LTAuMjA4LTAuNTQxLTAuNC0wLjc1N2MtMC4yMTgtMC4yMy0wLjU1Ni0wLjI0NS0wLjg1Mi0wLjI0MmMtMC45NDIsMC4wMTEtMS44ODQtMC4wMDMtMi44MjUsMC4wMDVjLTAuMzQtMC4wMDItMC43MDUsMC4xOTEtMC44MDIsMC41MzRjLTAuMDY3LDAuMjg2LTAuMDM2LDAuNTgzLTAuMDQxLDAuODc1YzAuMDAzLDQuNzY2LTAuMDAyLDkuNTMsMC4wMDIsMTQuMjk1Yy0wLjAyNywwLjQwMywwLjMwOSwwLjc4MSwwLjcxNSwwLjc4NWMwLjc3LDAuMDE4LDEuNTQxLDAuMDAxLDIuMzEsMC4wMDhjMC4zODgsMC4wMjgsMC43ODctMC4yNjgsMC44MDQtMC42NjhjMC4wMjctMy4yODctMC4wMjQtNi41NzQsMC4wMjYtOS44NTljMC4wNjUsMC4wNTcsMC4xNTgsMC4wOTUsMC4xOTYsMC4xODFjMC45MywyLjAwNiwxLjgxMiw0LjAzOCwyLjcyLDYuMDU1YzAuMjAzLDAuNSwwLjgwMiwwLjQ4OSwxLjI1MiwwLjQ2NmMwLjUyOS0wLjA0NSwxLjE1NSwwLjE0NiwxLjU4Ny0wLjI2YzAuOTgtMi4wNywxLjg0OC00LjE5NSwyLjg1My02LjI1NGMwLjA1Mi0wLjA1NSwwLjExLTAuMSwwLjE4MS0wLjEzMmMwLjA0MywzLjI2NywwLDYuNTM2LDAuMDIxLDkuODA0YzAuMDEyLDAuMzc1LDAuMzc1LDAuNjc0LDAuNzQxLDAuNjY0YzAuNzcxLDAuMDA0LDEuNTQyLDAuMDA3LDIuMzEyLTAuMDAxYzAuNDE4LDAuMDA5LDAuNzc0LTAuMzcyLDAuNzQ0LTAuNzg3QzU3LjA5LDU1LjczMiw1Ny4wOTIsNTAuNzUyLDU3LjA4OCw0NS43NzJ6Ii8+PC9nPjxnIGlkPSJfeDIzX2I3N2YyZWZmIj48cGF0aCBmaWxsPSIjQjY3RjJFIiBkPSJNMjIuNDE4LDM1LjkyMmMwLjAxMy0wLjM5NiwwLjAxLTAuNzkzLTAuMDQxLTEuMTg3QzIyLjM2NywzNS4xMzIsMjIuMzc0LDM1LjUyOCwyMi40MTgsMzUuOTIyeiIvPjxwYXRoIGZpbGw9IiNCNjdGMkUiIGQ9Ik0yMi40ODIsMzcuNDZjMC4wMTctMC4zOTcsMC4wMDktMC43OTUtMC4wNDEtMS4xODlDMjIuNDI5LDM2LjY2OCwyMi40MzksMzcuMDY1LDIyLjQ4MiwzNy40NnoiLz48cGF0aCBmaWxsPSIjQjY3RjJFIiBkPSJNMjIuODYzLDQ2LjY5NGMwLjAwOS0wLjQyMSwwLjAzLTAuODQ1LTAuMDUxLTEuMjYxQzIyLjc5NSw0NS44NTQsMjIuODIyLDQ2LjI3NSwyMi44NjMsNDYuNjk0eiIvPjxwYXRoIGZpbGw9IiNCNjdGMkUiIGQ9Ik0yMi45MzEsNDguMjk0YzAuMDA2LTAuNDYzLDAuMDMtMC45MjgtMC4wNTQtMS4zODZDMjIuODU2LDQ3LjM3MSwyMi44ODQsNDcuODMzLDIyLjkzMSw0OC4yOTR6Ii8+PHBhdGggZmlsbD0iI0I2N0YyRSIgZD0iTTIyLjk5Miw0OS44MzVjMC4wMTMtMC40NjIsMC4wMzItMC45MjgtMC4wNTEtMS4zODZDMjIuOTE3LDQ4LjkxMiwyMi45NTIsNDkuMzc0LDIyLjk5Miw0OS44MzV6Ii8+PHBhdGggZmlsbD0iI0I2N0YyRSIgZD0iTTIzLjEzNyw1Mi44OThjMC4wMDMtMC40OTgsMC4wMTctMS0wLjA2OC0xLjQ4OUMyMy4wNzgsNTEuOTA0LDIzLjA1Miw1Mi40MDYsMjMuMTM3LDUyLjg5OHoiLz48cGF0aCBmaWxsPSIjQjY3RjJFIiBkPSJNMjMuMTQsNTIuOTQ5YzAuMDM0LDIuNDE4LDAuMTk4LDQuODM1LDAuMjY5LDcuMjUzYzAuMDY0LDAuNjY1LDAuMDIxLDEuMzM3LDAuMTA5LDIuMDAxYzAuMDQyLTEuMDMxLTAuMDcyLTIuMDYxLTAuMDg3LTMuMDkyQzIzLjMxNyw1Ny4wNjEsMjMuMjkxLDU1LjAwMSwyMy4xNCw1Mi45NDl6Ii8+PHBhdGggZmlsbD0iI0I2N0YyRSIgZD0iTTc0Ljg0MSw2NS41NTFjLTcuMTk2LDIuNDA4LTE0LjM5OSw0Ljc4OS0yMS41OTcsNy4xOTNjLTAuOTA0LDAuMzEzLTEuODU3LDAuNDk5LTIuODE5LDAuNDk0Yy0xLjA4MiwwLjAyMy0yLjE2Mi0wLjE3Mi0zLjE3OC0wLjU0M2MtNy4xNS0yLjM4NS0xNC4zMDQtNC43NTYtMjEuNDUyLTcuMTQxYy0xLjM0Mi0wLjQ1My0yLjIyLTEuODA3LTIuMjY5LTMuMTk5Yy0wLjAxNiwwLjY0Ni0wLjAyLDEuMzEsMC4yMDUsMS45MjJjMC4zMjQsMC45MzUsMS4wODcsMS42OTksMi4wMTYsMi4wMzFjNy4yNjksMi41MTgsMTQuNTM3LDUuMDM0LDIxLjgwNSw3LjU1M2MxLjgzNywwLjYwNiwzLjg3NSwwLjU3Niw1LjY5My0wLjA5MmM3LjE3NC0yLjQ4NCwxNC4zNDgtNC45NjksMjEuNTIxLTcuNDU1YzEuMjk5LTAuNDQsMi4yNDEtMS43NTQsMi4yMjUtMy4xMjdDNzYuNjY3LDY0LjI0MSw3NS44OTgsNjUuMTg2LDc0Ljg0MSw2NS41NTF6Ii8+PC9nPjwvZz48L3N2Zz4=',
        sberlogistics: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNS4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMTYxLjE4OHB4IiBoZWlnaHQ9IjE2MS4xODhweCIgdmlld0JveD0iMTA3Ny42NzYgNTYuMTg1IDE2MS4xODggMTYxLjE4OCINCgkgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAxMDc3LjY3NiA1Ni4xODUgMTYxLjE4OCAxNjEuMTg4IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiNGRkZGRkYiIGQ9Ik0xMjM4LjU5MywxMzYuNzgyYzAsNDQuMzU3LTM1Ljk1OCw4MC4zMi04MC4zMjEsODAuMzJjLTQ0LjM2MywwLTgwLjMyNC0zNS45NjItODAuMzI0LTgwLjMyDQoJYzAtNDQuMzYyLDM1Ljk2MS04MC4zMjUsODAuMzI0LTgwLjMyNUMxMjAyLjYzNSw1Ni40NTcsMTIzOC41OTMsOTIuNDIsMTIzOC41OTMsMTM2Ljc4MnoiLz4NCjxwYXRoIGZpbGw9IiMyRjlBNDEiIGQ9Ik0xMTU4LjI3MSwxMTYuNjQ2Yy0xMS4xMjEsMC0yMC4xMzUsOS4wMTQtMjAuMTM1LDIwLjEzNGMwLDExLjExOSw5LjAxNCwyMC4xMzQsMjAuMTM1LDIwLjEzNA0KCWMxMS4xMiwwLDIwLjEzMy05LjAxNSwyMC4xMzMtMjAuMTM0QzExNzguNDA0LDEyNS42NiwxMTY5LjM5MiwxMTYuNjQ2LDExNTguMjcxLDExNi42NDZ6IE0xMjI4LjUzMywxMzYuNzgyDQoJYzAsMzguODAyLTMxLjQ1NSw3MC4yNjEtNzAuMjYyLDcwLjI2MWMtMzguODA3LDAtNzAuMjY1LTMxLjQ1OC03MC4yNjUtNzAuMjYxYzAtMzguODA2LDMxLjQ1OC03MC4yNjYsNzAuMjY1LTcwLjI2Ng0KCUMxMTk3LjA3OCw2Ni41MTYsMTIyOC41MzMsOTcuOTc2LDEyMjguNTMzLDEzNi43ODJ6IE0xMjE1Ljg5MiwxMzYuNzgxYzAtMzEuODI1LTI1Ljc5Ni01Ny42MjQtNTcuNjIyLTU3LjYyNA0KCWMtMzEuODI0LDAtNTcuNjIyLDI1Ljc5OS01Ny42MjIsNTcuNjI0YzAsMzEuODIxLDI1Ljc5OSw1Ny42MTksNTcuNjIyLDU3LjYxOUMxMTkwLjA5NiwxOTQuNDAxLDEyMTUuODkyLDE2OC42MDMsMTIxNS44OTIsMTM2Ljc4MXoNCgkiLz4NCjwvc3ZnPg0K',
        sberlogistics_pickpoint: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNS4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMTYxLjE4OHB4IiBoZWlnaHQ9IjE2MS4xODhweCIgdmlld0JveD0iMTA3Ny42NzYgNTYuMTg1IDE2MS4xODggMTYxLjE4OCINCgkgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAxMDc3LjY3NiA1Ni4xODUgMTYxLjE4OCAxNjEuMTg4IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiNGRkZGRkYiIGQ9Ik0xMjM4LjU5MywxMzYuNzgyYzAsNDQuMzU3LTM1Ljk1OCw4MC4zMi04MC4zMjEsODAuMzJjLTQ0LjM2MywwLTgwLjMyNC0zNS45NjItODAuMzI0LTgwLjMyDQoJYzAtNDQuMzYyLDM1Ljk2MS04MC4zMjUsODAuMzI0LTgwLjMyNUMxMjAyLjYzNSw1Ni40NTcsMTIzOC41OTMsOTIuNDIsMTIzOC41OTMsMTM2Ljc4MnoiLz4NCjxwYXRoIGZpbGw9IiMyRjlBNDEiIGQ9Ik0xMTU4LjI3MSwxMTYuNjQ2Yy0xMS4xMjEsMC0yMC4xMzUsOS4wMTQtMjAuMTM1LDIwLjEzNGMwLDExLjExOSw5LjAxNCwyMC4xMzQsMjAuMTM1LDIwLjEzNA0KCWMxMS4xMiwwLDIwLjEzMy05LjAxNSwyMC4xMzMtMjAuMTM0QzExNzguNDA0LDEyNS42NiwxMTY5LjM5MiwxMTYuNjQ2LDExNTguMjcxLDExNi42NDZ6IE0xMjI4LjUzMywxMzYuNzgyDQoJYzAsMzguODAyLTMxLjQ1NSw3MC4yNjEtNzAuMjYyLDcwLjI2MWMtMzguODA3LDAtNzAuMjY1LTMxLjQ1OC03MC4yNjUtNzAuMjYxYzAtMzguODA2LDMxLjQ1OC03MC4yNjYsNzAuMjY1LTcwLjI2Ng0KCUMxMTk3LjA3OCw2Ni41MTYsMTIyOC41MzMsOTcuOTc2LDEyMjguNTMzLDEzNi43ODJ6IE0xMjE1Ljg5MiwxMzYuNzgxYzAtMzEuODI1LTI1Ljc5Ni01Ny42MjQtNTcuNjIyLTU3LjYyNA0KCWMtMzEuODI0LDAtNTcuNjIyLDI1Ljc5OS01Ny42MjIsNTcuNjI0YzAsMzEuODIxLDI1Ljc5OSw1Ny42MTksNTcuNjIyLDU3LjYxOUMxMTkwLjA5NiwxOTQuNDAxLDEyMTUuODkyLDE2OC42MDMsMTIxNS44OTIsMTM2Ljc4MXoNCgkiLz4NCjwvc3ZnPg0K'
    },
    init: function (params) {
        var arControls = ['zoomControl'],
            clientSizes = window.Osh.bxPopup.getClientSizes();

        this.isMobile = Boolean(clientSizes.width < 993);
        this.arMarkers = [];
        params.elem.innerHTML = "";
        if (!params.isFullscreenControlDisabled) {
            arControls.push('fullscreenControl');
        }
        this.instance = new ymaps.Map(params.elem,
            {
                // center: [parseFloat(params.latitude), parseFloat(params.longitude)],
                zoom: 14,
                controls: arControls
            }, {
                searchControlProvider: 'yandex#search'
            });
        if (params.search) {
            this.instance.controls.add(new ymaps.control.SearchControl({
                options: {
                    float: 'left',
                    floatIndex: 100,
                    noPlacemark: true,
                    provider: 'yandex#map'
                }
            }));
        }
    },

    setMarkers: function (arPvzList, callback) {
        var iMapLength = arPvzList.length, markerSettings = {};
        for (var i = 0; i < iMapLength; i++) {
            markerSettings = this.getMarkerSettings(arPvzList[i], i);
            if (!arPvzList[i].consistent) {
                continue;
            }
            this.arMarkers[i] = new ymaps.Placemark(
                [parseFloat(arPvzList[i].gps_location.latitude), parseFloat(arPvzList[i].gps_location.longitude)],
                this.getMarkerSettings(arPvzList[i], i),
                this.getMarkerOptions(arPvzList[i].courier)
            );
            this.arMarkers[i].events.add('click', callback);

            this.instance.geoObjects.add(this.arMarkers[i]);
        }
    },

    getMarkerSettings: function (pvzData, i) {
        var balloonFooter = '',
            markerSettings = {
                hintContent: pvzData.address,
                balloonContentHeader: pvzData.address,
                oshElemIndex: i
            };
        if (this.isMobile) {
            if (pvzData.card) {
                balloonFooter += '<i class="fa fa-credit-card sh_pvz_pay_icon" aria-hidden="true" title="'
                    + BX.message("CARD_TEXT") + BX.message("DA_TEXT") + '"></i>';
            }
            if (pvzData.cod) {
                balloonFooter += '<i class="fa fa-database sh_pvz_pay_icon" aria-hidden="true" title="'
                    + BX.message("COD_TEXT") + BX.message("DA_TEXT") + '"></i>';
            }
            balloonFooter += '<span style="float:right">' + (!!BX.message("NAME_" + pvzData.type) ?
                BX.message("NAME_" + pvzData.type) : '') + (!!BX.message("COURIER_" + pvzData.courier) ? '&nbsp;<b>'
                + BX.message("COURIER_" + pvzData.courier) + '</b>' : '') + '</span>';
            markerSettings.balloonContentBody = '<p style="font-size: 10px;line-height: 12px;">'
                + pvzData.trip_description + '</p>';
            if (balloonFooter.length > 0) {
                markerSettings.balloonContentFooter = balloonFooter;
            }
        }
        return markerSettings;
    },

    getMarkerOptions: function (courrier) {
        var markerOptions =
            (this.arIcons.hasOwnProperty(courrier)) ? {
                iconLayout: 'default#image',
                iconImageHref: this.arIcons[courrier],
                iconImageSize: [30, 30],
                iconImageOffset: [-15, -8],
                balloonCloseButton: true,
                hideIconOnBalloonOpen: false
            } : {
                preset: "islands#blueCircleDotIcon",
                iconColor: '#2b7788',
                balloonCloseButton: false,
                hideIconOnBalloonOpen: false
            }, clientSizes = window.Osh.bxPopup.getClientSizes();
        if (this.isMobile) {
            markerOptions.balloonMaxWidth = Math.round(clientSizes.width * 0.8);
            markerOptions.balloonMinWidth = Math.round(clientSizes.width * 0.4);
        }
        return markerOptions;
    },
    changeMarker: function (index) {
        var isMarkerLength = this.arMarkers.length;
        for (var i = 0; i < isMarkerLength; i++) {
            if (!this.arMarkers[i]) {
                continue;
            }
            this.arMarkers[i].options.set("preset", "islands#blueCircleDotIcon");
            this.arMarkers[i].options.set("iconColor", "#2b7788");
        }
        this.arMarkers[index].options.set("preset", "default#truckIcon");
        this.arMarkers[index].options.set("iconColor", "#ba0022");
        this.instance.setCenter(this.arMarkers[index].geometry.getCoordinates(), 14, {
            duration: 500,
            checkZoomRange: true
        });
    },
    centerCoords: function (pvzList) {
        var center = {lat: null, lon: null},
            current = {lat: null, lon: null},
            total = 0;
        for (var i = 0; i < pvzList.length; i++) {
            current.lat = parseFloat(pvzList[i].gps_location.latitude);
            current.lon = parseFloat(pvzList[i].gps_location.longitude);
            if (!pvzList[i].consistent) {
                console.warn('OSH_ERROR_GEOLOCATION', pvzList[i]);
                continue;
            }
            center.lat = (center.lat * total + current.lat) / (total + 1);
            center.lon = (center.lon * total + current.lon) / (total + 1);
            total++;
        }
        return center;
    },
    get: function () {
        return this.instance;
    },
    openBalloon: function (marker) {
        var currentMarker = this.arMarkers[marker];
        if (!!currentMarker) {
            setTimeout(function () {
                currentMarker.balloon.open();
            }, 500);
        }
    },
    fitBalloon: function (marker) {
        var currentMarker = this.arMarkers[marker];
        if (!!currentMarker) {
            setTimeout(function () {
                currentMarker.balloon.autoPan();
            }, 500);
        }
    }
};

window.Osh.bxPopup = {
    instance: null,
    containerId: "ModalPVZ",
    oshMkadDelivery: null,

    oContainers: {
        PVZ_ID: "shd_pvz_pick",
        PVZ_INFO: "shd_pvz_info"
    },

    oUrls: {
        selectPVZ: "/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajaxOshishaSetPricePVZ.php",
        setPriceDelivery: "/bitrix/modules/enterego.pvz/lib/CommonPVZ/ajaxOshishaSetPricePVZ.php",
    },

    init: function () {
        if (this.instance !== null)
            return;

        const nodeOshOverlay = BX.create("DIV", {
            props: {
                className: 'osh_map_overlay',
                id: 'osh_map_overlay',
            },
        })
        const nodeYaMapContainer = BX.create("DIV", {
            props: {
                id: 'osh-map-container',
            },
            children: [
                BX.create('span', {
                    props: {id: "osh-close-icon"},
                    events: {
                        click: this.onPopupWindowClose,
                    },
                })
            ]
        })
        nodeOshOverlay.append(nodeYaMapContainer)
        const nodeYaMap = BX.create("DIV", {
            props: {
                id: 'map',
            },
        })
        nodeYaMapContainer.append(nodeYaMap);

        const nodeYaAction = BX.create("DIV", {
            props: {
                id: 'osh-map-action',
            },
            children: [
                BX.create("input", {
                    props: {
                        className: 'form-control',
                        id: 'osh_delivery_ya_map_address',
                    },
                }),
                BX.create("button", {
                    props: {
                        className: 'btn btn_red mt-2',
                        id: 'saveBTN',
                    },
                    text: BX.message("SAVE"),
                    events: {
                        click: BX.proxy(this.onPopupSave, this),
                    }
                })
            ]
        })

        nodeYaMapContainer.append(nodeYaAction)

        document.body.append(nodeOshOverlay)

        this.instance = nodeOshOverlay;
    },

    onPickerClick: function () {
        this.init();
        document.body.style.overflow = "hidden";
        BX('osh_map_overlay').style.display = "flex";

        this.oshMkadDelivery = window.Osh.oshMkadDistance.getInstance();
        this.oshMkadDelivery.show();

        return false;
    },

    onPopupWindowClose: function () {
        document.body.style.overflow = 'auto';
        BX('osh_map_overlay').style.display = "none";
        return true;
    },

    onPopupSave: function (e) {
        if (e.target.className.indexOf('popup-window-button-disable') === -1) {
            this.oshMkadDelivery.saveDelivery();
            this.onPopupWindowClose();
        }
    },

    getClientSizes: function () {
        let windowSizes = BX.GetWindowInnerSize(),
            clientSizes = {};

        clientSizes.width = windowSizes.innerWidth * 1;
        clientSizes.height = windowSizes.innerHeight * 1;

        return clientSizes;
    },
};

window.Osh.checkPvz = function (result) {

    switch (typeof result) {
        case "object":
            let sJson = null,
                ePicker = null,
                button = null,
                activeDelivery = null,
                description = '',
                oJson = null;

            if (!result.order) {
                return true;
            }
            if (!result.order.DELIVERY) {
                return true;
            }
            var arDeliveries = result.order.DELIVERY,
                iDeliveryLength = arDeliveries.length;
            if (iDeliveryLength <= 0) {
                return true;
            }
            for (var i = 0; i < iDeliveryLength; i++) {
                if (!arDeliveries[i].hasOwnProperty('CHECKED')) {
                    continue;
                }
                if (arDeliveries[i].CHECKED == 'Y') {
                    activeDelivery = arDeliveries[i].ID;
                    description = arDeliveries[i].DESCRIPTION;
                }
            }
            if (!activeDelivery) {
                return true;
            }

            ePicker = document.querySelector('#shd_pvz_pick[data-delivery="' + activeDelivery + '"][data-force="1"]');
            if (!ePicker) {
                return true;
            }

            //Вынуждаем обновить информацию о доставке, при выборе адреса
            // (форма заказа переделана в одностраничную и обновление блока доставки не всегда срабатывает)
            if (description) {
                ePicker.parentElement.innerHTML = description;

                ePicker = document.querySelector('#shd_pvz_pick[data-delivery="' + activeDelivery + '"][data-force="1"]');
            }

            button = ePicker.querySelector("button");
            if (!button) {
                return true;
            }
            sJson = ePicker.getAttribute("data-json");
            oJson = JSON.parse(sJson);

            let addressProp = document.querySelector('[name="ORDER_PROP_' + oJson.address_prop_id + '"]');
            if (!!addressProp) {
                addressProp.value = oJson.pvz_address;
            }
            break;

        case "string":
        default:
            break;
    }
    return true;
};

BX.addCustomEvent('onAjaxSuccess', window.Osh.checkPvz);