function insert_init_js(opts) {

    var url_str = opts['url'] || false;
    var obj_name = opts['name'] || 'insert_init_js';
    var callback_func = opts['callback'] || function () {
    };

    var def_js_path = window['insert_init_cfg']['default_js_path'];

    var is_use_def_path = function (url) {
        if ((url + '').indexOf('/') != 0 && !(url + '').match(/^(https?:)?\/\//i))
            return true;
        return false;
    };

    if (!url_str) {
        url_str = '';
        if (typeof (window['insert_init_cfg']['map_js'][obj_name]) != 'undefined') {
            url_str = (is_use_def_path(window['insert_init_cfg']['map_js'][obj_name]) ? def_js_path : '') + window['insert_init_cfg']['map_js'][obj_name];
        } else {
            url_str = def_js_path + obj_name + '.js';
        }
    } else {
        url_str = (is_use_def_path(url_str) ? def_js_path : '') + url_str;
    }

    var is_undefined = false;
    if (obj_name.indexOf('$') == 0) {
        if (eval('typeof(' + obj_name + ')') == 'undefined') {
            is_undefined = true;
        }
        //if(typeof(history)=='undefined' || typeof(history['replaceState'])=='undefined') {
    } else if (obj_name.indexOf('history') == 0) {
        if (typeof (history) == 'undefined' || eval('typeof(' + obj_name + ')') == 'undefined') {
            is_undefined = true;
        }
    } else {
        if (typeof (window[obj_name]) == 'undefined') {
            is_undefined = true;
        }
    }

    var call_callbacks = function (oname) {
        var all_callbacks = window['insert_init_js_objs'][oname];
        window['insert_init_js_objs'][oname] = [];
        for (var i in all_callbacks) {
            var callback_i = all_callbacks[i];
            callback_i();
        }
    };

    if (typeof (window['insert_init_js_objs']) == 'undefined')
        window['insert_init_js_objs'] = {};

    if (typeof (window['insert_init_js_objs'][obj_name]) != 'undefined') {
        var found = 0;
        for (var i in window['insert_init_js_objs'][obj_name]) {
            if (window['insert_init_js_objs'][obj_name][i].toString() == callback_func.toString()) {
                found = 1;
                break;
            }
        }
        if (!found)
            window['insert_init_js_objs'][obj_name].push(callback_func);
        if (!is_undefined) {
            call_callbacks(obj_name);
        }
        return true;
    } else {
        window['insert_init_js_objs'][obj_name] = [];
        window['insert_init_js_objs'][obj_name].push(callback_func);
    }

    if (is_undefined) {
        if (window.navigator.userAgent.indexOf("MSIE ") > 0) {
            if (document.scripts[0].readyState) {
                var s = document.createElement("script"), done = false,
                    head = document.getElementsByTagName("head")[0] || document.documentElement;
                s.type = "text/javascript";
                s.onload = s.onreadystatechange = function () {
                    if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
                        done = true;
                        s.onload = s.onreadystatechange = null;
                        call_callbacks(obj_name);
                        if (head && s.parentNode)
                            head.removeChild(s);
                    }
                };
                s.src = url_str;
                head.insertBefore(s, head.firstChild);
            } else {
                document.write('<script src="' + url_str + '" defer></' + 'script>');
            }
        } else {

            var is_cache = true;
            if ((url_str + '').match(/^(https?:)?\/\//i) && !(url_str + '').match(/(static|img).akusherstvo.ru/i)) {
                is_cache = false;
            }

            $.ajax({
                url: url_str,
                dataType: "script",
                contentType: 'application/javascript; charset=windows-1251', /* for fix encoding */
                beforeSend: function (xhr) {
                    xhr.overrideMimeType("application/javascript; charset=windows-1251");
                },
                cache: is_cache
            }).done(function (js_cont) {
                call_callbacks(obj_name);

                //if(!localStorage || window.navigator.userAgent.indexOf("MSIE ") > 0) {} else {
                //try { localStorage.setItem(lstrg_key+lstrg_ver,js_cont); for(var i in localStorage) if(i!=(lstrg_key+lstrg_ver) && localStorage.hasOwnProperty(i) && (''+i).indexOf(lstrg_key)>=0)localStorage.removeItem(i); } catch (e) {}}
            });
        }
    } else {
        call_callbacks(obj_name);
    }
    return true;
}

var akusherstvoMkadDistance = akusherstvoMkadDistance || (function () {
    var instance;

    function createInstance() {
        var obj = new akusherstvoMkadDistanceObject();
        return obj;
    }

    return {
        getInstance: function () {
            if (!instance) {
                instance = createInstance();
            }
            return instance;
        }
    };
}());

var akusherstvoMkadDistanceObject = akusherstvoMkadDistanceObject || function akusherstvoMkadDistanceObject() {
    var $ = $ || jQuery;
    var selfObj = this;
    selfObj.isInited = false;
    selfObj.configuredPromise = false,
        selfObj.mobile_api_cache = {};

    var mkad_poly = null,
        msk_center_point = [55.75119082121071, 37.61699737548825],
        msk_250km_boundedBy = [[53.45159731566762, 33.63312692442719], [57.938891029311215, 41.58884573182265]],
        myMap = null,
        mkad_points = [[55.77682929150693, 37.8427186924053], [55.77271261339107, 37.843152686304705], [55.738276896644805, 37.84134161820584], [55.71399689835854, 37.83813880871875], [55.699921267680175, 37.83078428272048], [55.6962950504132, 37.82954151435689], [55.6928207993758, 37.82931794772561], [55.6892209716432, 37.829854389528585], [55.66165146026852, 37.83966290527148], [55.658376283618054, 37.8394483285503], [55.65605007409182, 37.838791290011436], [55.6531141363056, 37.8370746762419], [55.65145113826342, 37.83568956934368], [55.64812656859308, 37.8314409502641], [55.644824797922006, 37.82628977266418], [55.625585595616016, 37.79678983996685], [55.62124956968963, 37.78912615774818], [55.60391627214637, 37.75711862597196], [55.59919459324873, 37.74706053825473], [55.59180719241245, 37.72946947797549], [55.588836348363664, 37.7225364780563], [55.575884202346515, 37.68793829096614], [55.57326575851499, 37.679926824757885], [55.57229316496271, 37.67458386440024], [55.571916278457984, 37.66924090404256], [55.57203486325925, 37.66469310778763], [55.576012618166274, 37.59661654265479], [55.576997275315456, 37.58977417112674], [55.593461027106216, 37.52076943829923], [55.5950406236937, 37.51480420545011], [55.59619490389248, 37.51175721600919], [55.597166902872914, 37.509675821813644], [55.59866130413232, 37.50692923978237], [55.59992481831982, 37.505169710668625], [55.60066420884299, 37.50419141558768], [55.61116763612223, 37.491928885586624], [55.638875974823236, 37.459586882490854], [55.659861822998046, 37.43484779763937], [55.66403637567329, 37.43088149929608], [55.68274170580392, 37.41690766704496], [55.68445104083821, 37.41598498714383], [55.68864009415873, 37.41437258409716], [55.69086356292832, 37.41284823307507], [55.69271798296722, 37.41115307697766], [55.694411609835676, 37.40906103948314], [55.69633857479258, 37.40646466115671], [55.70821582138647, 37.39042283284293], [55.709960382334486, 37.388470184680074], [55.71100223559, 37.387526047106846], [55.714297215701556, 37.38550902592765], [55.74299678995391, 37.37085040270776], [55.74737891548303, 37.3693383084583], [55.749835763080554, 37.36897352803228], [55.78212184948561, 37.36975523402037], [55.78471424142089, 37.370104443868414], [55.7865400068638, 37.370812547048324], [55.789647237893845, 37.37287248357179], [55.80029924148098, 37.38296043585071], [55.804902293956964, 37.38656302639442], [55.80873309836682, 37.38838692852456], [55.83469933158447, 37.39616684582014], [55.838100191970035, 37.39588770506112], [55.84068411346117, 37.394943567487864], [55.844347068377, 37.39240249367216], [55.84601308639975, 37.391908967213396], [55.847449667553015, 37.39193042488553], [55.84921212285334, 37.39242395134426], [55.85763645302826, 37.39690455309926], [55.860737839006916, 37.39879032715197], [55.862584159418496, 37.40035673721667], [55.864949251589444, 37.40273853882189], [55.86706126571094, 37.40537841047629], [55.869498474258364, 37.40936953749045], [55.871054829060206, 37.412373611587114], [55.87204410730281, 37.41473395552023], [55.87320337129219, 37.41764120434771], [55.875543687912774, 37.424979728212456], [55.8813305362832, 37.44392953059815], [55.88207002762898, 37.44778576813208], [55.882588650864065, 37.452763948063726], [55.88275750343904, 37.46081057510839], [55.88292635527642, 37.464286717991705], [55.883384663688354, 37.46735516510474], [55.88551934442368, 37.47628155670629], [55.888075982000466, 37.48647395096288], [55.88926982558072, 37.49010029755102], [55.89215178082288, 37.496623429875235], [55.904441104424826, 37.52475156556294], [55.90586346265124, 37.529643914806094], [55.90676747666915, 37.53442897568867], [55.90726166205295, 37.538141152965274], [55.910865408147124, 37.57275237809345], [55.911022085130945, 37.57652892838642], [55.91097387689595, 37.579554460155215], [55.91063641756565, 37.58356704484148], [55.90998559481434, 37.587579629527774], [55.9092021825094, 37.5910986877553], [55.90847901858254, 37.593480489360545], [55.901901172883115, 37.6180182383294], [55.89891144249577, 37.63301715114069], [55.89687395332799, 37.64762982585381], [55.89576474245468, 37.659367172502996], [55.89456572248885, 37.69416117435827], [55.89393874366838, 37.699139354289926], [55.89328763950915, 37.70195030933754], [55.89247977280019, 37.70471834904089], [55.89140661030458, 37.70757221943274], [55.880130573679516, 37.73042464023962], [55.8304865952908, 37.8268977445699], [55.829001074066674, 37.82968724194538], [55.82757588633297, 37.831725720796705], [55.82488607061184, 37.834775327717445], [55.822361493423664, 37.836706518208175], [55.82024748644772, 37.8376291981093], [55.816165064041414, 37.83857287182817], [55.81242284003345, 37.83903585464755], [55.803139424516395, 37.839775801016756], [55.77682929150693, 37.8427186924053]],
        b_junctions = [[55.77682626803085, 37.84269989967345], [55.76903191638017, 37.84318651588698], [55.74392477931212, 37.84185519957153], [55.73052122580085, 37.84037898416108], [55.71863531207276, 37.83895012458452], [55.711831272333605, 37.83713368900962], [55.707901422046966, 37.8350106548768], [55.6869523798766, 37.83057993978087], [55.65692789667629, 37.83910426510268], [55.640528720308474, 37.819652386266085], [55.617789410062215, 37.782276430404394], [55.59175631830074, 37.72929474857808], [55.57581125568298, 37.687799514747375], [55.57272629492449, 37.65277241112271], [55.57605719591829, 37.59643530860042], [55.58106457666858, 37.57265144016032], [55.59150701569656, 37.52902190629794], [55.61120819157864, 37.49189413873337], [55.638972144200956, 37.45948542596951], [55.66189360804507, 37.432824164364256], [55.68278581583797, 37.416807425418966], [55.668026850906536, 37.42778473861195], [55.70188946767468, 37.39895204348993], [55.713602586285944, 37.38589295731531], [55.72348037785042, 37.38078139017449], [55.73175585229489, 37.37657178200628], [55.76508406345848, 37.36928736556715], [55.76996256764349, 37.36942982797446], [55.789736950483615, 37.3728868615282], [55.808798087528174, 37.388344151047676], [55.83260998737753, 37.39560097816893], [55.851747102850375, 37.39376480087579], [55.87090570963696, 37.41209100527676], [55.87659696295345, 37.42839459978549], [55.88161130650381, 37.445221243317135], [55.88711708090231, 37.482644383447834], [55.89207427475143, 37.49649435563702], [55.90782224163112, 37.54371914983502], [55.90978840669936, 37.58858112800599], [55.89518876022445, 37.67325996719509], [55.82959228057486, 37.82861019557688], [55.8822323534685, 37.72592724800108], [55.8138082895938, 37.83884777073161]],
        s_junctions = [[55.75481214376632, 37.84267307758329], [55.70418787329251, 37.8332852107992], [55.702989401989484, 37.83263932754], [55.65047653581307, 37.83493949978359], [55.64502320468091, 37.82690675054945], [55.62614603220174, 37.798215117726585], [55.59582667642601, 37.73945441049923], [55.587464115886156, 37.71946951925047], [55.58141301775248, 37.70325579370606], [55.57362538548569, 37.63521054231301], [55.57456040522403, 37.619314897938175], [55.58056831268785, 37.573856505131964], [55.58749528969654, 37.5451094875984], [55.593784581287494, 37.51884952838902], [55.60589190143268, 37.49776326563821], [55.61577037337298, 37.48617693805733], [55.62588555827154, 37.47443845687327], [55.63159809915896, 37.46778063484318], [55.65207693603693, 37.4436689941094], [55.65663799228618, 37.43816060545844], [55.66590855944432, 37.42912931533752], [55.68849971417, 37.4141437197791], [55.707656747292155, 37.39082356976081], [55.70992858606593, 37.38822422159842], [55.75188787932283, 37.366333001041205], [55.79604144033229, 37.37852370112031], [55.81331234523823, 37.38954092451], [55.81568484607161, 37.390191395766784], [55.82131114715086, 37.391900629017584], [55.825072975139875, 37.393084859162826], [55.830495842317646, 37.39451898008863], [55.8339338725267, 37.39594735722236], [55.85865656090271, 37.397073365517734], [55.86699779674642, 37.40492948497198], [55.87821893534327, 37.43308640028372], [55.88949415675149, 37.48972351315925], [55.90681458164319, 37.53369071576891], [55.910830265189425, 37.57059586873433], [55.911011046432726, 37.581529228009686], [55.89964948588706, 37.629701188337705], [55.895716922397085, 37.66346711671403], [55.89505379117015, 37.68453970149422], [55.894105661911894, 37.699083186567655], [55.89178148825972, 37.70718435431336], [55.87839320587734, 37.734177892950065], [55.82543390489343, 37.83464260085545], [55.81012946042399, 37.83951226232321], [55.80418173177062, 37.83998433110984], [55.802423269353746, 37.840209636667076], [55.90738403567146, 37.5979956303702]],
        collection = null,
        bjGq = null,
        jGq = null,
        searchControl = true,
        html_map_id = 'mkad_distance_map',
        balloon = null,
        is_mobile = (document.cookie.match(/mobile_version=1/i) || 0),
        is_mobile_api = true;

    selfObj.setMobileApiState = function (status) {
        is_mobile_api = (status ? true : false);
    };

    selfObj.getDistanceCache = {};

    selfObj.getDistance = function (d, is_update_order_form) {

        collection.removeAll();
        if (!is_update_order_form)
            $('#' + html_map_id + '_show_results').remove();

        if (typeof (selfObj.getDistanceCache['checkIn']) == 'undefined')
            selfObj.getDistanceCache['checkIn'] = {};

        if (typeof (selfObj.getDistanceCache['routeFromCenter']) == 'undefined')
            selfObj.getDistanceCache['routeFromCenter'] = {};

        d[0] = Number('' + d[0]).toPrecision(6);
        d[1] = Number('' + d[1]).toPrecision(6);

        if (typeof (selfObj.getDistanceCache['checkIn'][d]) == 'undefined')
            selfObj.getDistanceCache['checkIn'][d] = selfObj.checkIn(d);

        if (selfObj.getDistanceCache['checkIn'][d]) {
            if (is_update_order_form) {
                is_update_order_form({'km': 0, 'cost': 0, 'is_msk': 1});
                return true;
            }

            var str = 'В пределах МКАД - 299.';
            selfObj.showText(str);

            if (!is_mobile)
                balloon.open(d, str);
        } else {
            balloon.close();

            if (typeof (selfObj.getDistanceCache['routeFromCenter'][d]) == 'undefined') {
                selfObj.getDistanceCache['routeFromCenter'][d] = false;
                var b = [];
                selfObj.routeFromCenter(d, function (c, e) {
                    b.push(e[0]);
                    var f = selfObj.findNearest(jGq, d, 1),
                        a = [],
                        a = 3500 > selfObj.getPointDistance(f[0], d) ? selfObj.findNearest(jGq, d, 7) : selfObj.findNearest(jGq, d, 5);
                    a.forEach(function (g) {
                        g[0] == e[0][0] && g[1] == e[0][1] || b.push(g);
                    });

                    async.map(b, function (g, i) {
                        selfObj.getRoute(g, d, i);
                    }, function (i, j) {
                        var g = j.map(function (h) {
                                return h.getLength();
                            }),
                            g = selfObj.indexOfSmallest(g);

                        d[0] = Number('' + d[0]).toPrecision(6);
                        d[1] = Number('' + d[1]).toPrecision(6);
                        selfObj.getDistanceCache['routeFromCenter'][d] = [j[g], d];
                        selfObj.showResults(j[g], d, is_update_order_form);
                    });
                });
            } else if (selfObj.getDistanceCache['routeFromCenter'][d]) {
                selfObj.showResults(selfObj.getDistanceCache['routeFromCenter'][d][0], selfObj.getDistanceCache['routeFromCenter'][d][1], is_update_order_form);
            }

        }
    };

    selfObj.getAddress = function (geocode) {
        geocode[0] = geocode[0].toFixed(6);
        geocode[1] = geocode[1].toFixed(6);

        //num.toFixed
        let rescont = '';
        // 55.627070, 37.660943
        // 55.627069649826446,37.66094268798826

        $.ajax({
            type: "GET",
            url: "http://geocode-maps.yandex.ru/1.x/",
            data: 'geocode=' + geocode[1] + ',' + geocode[0] + '&format=json&&results=1&apikey=20ec5a8a-94a3-4acd-abc0-87209dfbf713',
            dataType: "JSON", timeout: 30000, async: false,
            error: function (xhr) {
                rescont += 'Ошибка геокодирования: ' + xhr.status + ' ' + xhr.statusText;
            },
            success: function (html) {
                console.log(html);
                res = html;
                var geores = res.response.GeoObjectCollection.featureMember;
                if (geores.length > 0) {
                    for (k = 0; k < geores.length; k++) {
                        rescont += geores[k].GeoObject.name + '; ';
                    }
                } else {
                    rescont += 'нет данных';
                }
            }
        });
        return rescont;
    };

    selfObj.checkIn = function (d) {
        d = new ymaps.Placemark(d);

        var b = ymaps.geoQuery(d).setOptions("visible", 0).addToMap(myMap).searchInside(mkad_poly).getLength();
        myMap.geoObjects.remove(d);
        return b;
    };

    selfObj.routeFromCenter = function (d, b) {

        selfObj.getRoute(msk_center_point, d, function (h, f) {
            var g = [];
            ymaps.geoQuery(f.getPaths()).each(function (i) {
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

    selfObj.showLink = function (e, b, d) {
        //console.log('click on map');
    };

    selfObj.showText = function (str) {
        $('#' + html_map_id + '_show_results').remove();
        $('#' + html_map_id).after('<div id="' + html_map_id + '_show_results" style="margin-top:10px;background-color:#eaeaf0;display:none;padding:4px;">' + str + '</div>');
        $('#' + html_map_id + '_show_results').toggle('normal', 'swing');

        myMap.setZoom(is_mobile ? 8 : 9);
    };

    selfObj.showResults = function (i, d, is_update_order_form) {
        var f = i.getHumanLength();
        i.getPaths().options.set({
            strokeColor: "F55F5C",
            strokeWidth: '8',
        });
        var g, b;
        i.getWayPoints().each(function (c) {
            if ("1" == c.properties.get("iconContent") || "МКАД" == c.properties.get("iconContent")) {
                c.properties.set("iconContent", "МКАД");
                c.options.set("preset", "islands#redStretchyIcon");
                b = c.geometry.getCoordinates();
                c.properties.set("balloonContent", "");
            } else {
                c.options.set("preset", "islands#redStretchyIcon");


                var dist_m = (i.getLength() / 1000), cost_str = 0, dist_km = 0;
                dist_km = dist_m;
                dist_m = parseFloat(dist_m).toFixed(2);
                let new_const = parseInt(dist_km),new_cost_dop = 0;
                if ((new_const % 10) >= 8) {
                    dist_km = (parseInt(dist_km) + 1);
                } else {
                    if (parseInt(dist_km) + (new_const % 10) >= 5) {
                        new_cost_dop = ((new_const - 5) * 40);
                    }
                }
                if (dist_km <= 0)
                    dist_km = dist_m;
                cost_str = (parseInt(cost) + new_cost_dop);

                c.properties.set("iconContent", '' + dist_km.toFixed(1) + ' км, ' + (cost_str.toFixed() > 0 ?
                    cost_str.toFixed() + ' руб' : '299 руб'));

                g = c.geometry.getCoordinates();
                c.properties.set("balloonContent", "");

                if (is_update_order_form) {
                    is_update_order_form({'km': dist_km, 'cost': cost_str, 'is_msk': 0});
                    return true;
                }

                selfObj.showText('<b>Расстояние от МКАД: ' + dist_km + ' км, Стоимость: ' + (cost_str > 0 ? cost_str + ' руб' : '299 руб') + '</b>');

            }
        });
        collection.add(i);
        //e || selfObj.showLink(g, b, "");
    };

    selfObj.initSearchControls = function () {
        var opts = {
            placeholderContent: "Введите адрес (или кликните на карту)",
            noPlacemark: 0,
            kind: 'house',
            strictBounds: true,
            results: 1
        };

        opts['boundedBy'] = msk_250km_boundedBy;

        var d = new ymaps.control.SearchControl({options: opts});
        searchControl = d;
        myMap.controls.add(d);
        var b = new ymaps.GeoObjectCollection(null, {
            //hintContentLayout: ymaps.templateLayoutFactory.createClass("$[properties.name]")
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

    selfObj.prepareData = function () {
        mkad_poly = new ymaps.Polygon([mkad_points], {}, {
            // цвет заливки.
            fillColor: 'rgba(255,94,89,0.12)',
            // цвет обводки.
            strokeColor: 'rgba(255,94,89,0.22)',
            // Прозрачность.
            opacity: 1,
            // ширина обводки.
            strokeWidth: 0.1
        });


        mkad_poly.events.add("click", function (c) {
            c = c.get("coords");

            console.log("Коррдинаты в переделах мкад: " + c);
            console.log(selfObj.getAddress(c));

            selfObj.getDistance(c);
        });
        ymaps.geoQuery(mkad_poly).setOptions("visible", 1).addToMap(myMap);


        var d = new ymaps.GeoObjectCollection({}),
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

    selfObj.init = function () {
        myMap = new ymaps.Map(html_map_id, {
            center: msk_center_point,
            zoom: 9,
            controls: ["zoomControl", "typeSelector"]
        });

        collection = new ymaps.GeoObjectCollection({});
        myMap.geoObjects.add(collection);

        balloon = new ymaps.Balloon(myMap, {
            // Опция: не показываем кнопку закрытия.
            closeButton: false
        });
        balloon.options.setParent(myMap.options);

        selfObj.initSearchControls();
        selfObj.prepareData();

        // myMap.events.add("click", function(c) {
        //     c = c.get("coords");
        //     console.log("Коррдинаты за переделами мкад: " + c);
        //     selfObj.getDistance(c);
        // });

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

            var d1 = $.Deferred(), d2 = $.Deferred();
            insert_init_js({
                'name': 'ymaps', 'callback': function () {
                    d1.resolve('ymaps loaded');
                }
            });
            insert_init_js({
                'name': 'async', 'callback': function () {
                    d2.resolve('async loaded');
                }
            });

            $.when.apply(null, [d1.promise(), d2.promise()]).always(function (v1, v2) {
                $(function () {
                    selfObj.isInited = true;
                    ymaps3.ready.then(selfObj.init);
                });
            });

        }
        return selfObj;
    };

    return selfObj.initWObj();
};