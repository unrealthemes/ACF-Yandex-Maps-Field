(function ($) {

    'use strict';

    function initialize_field($el) {

        var $element;

        var $input;

        var $params;

        var $map = null;

        // Init fields
        $element = $($el).find('.map');
        $input = ($el).find('.map-input');

        if ($element == undefined || $input == undefined) {
            console.error(acf_yandex_locale.map_init_fail);
            return false;
        }

        // Init params
        $params = $.parseJSON($($input).val());

        // Init map
        ymaps.ready(function () {
            map_init();
        });

        function map_init() {

            $element.empty();

            if ($map != null) {
                $map.destroy();
                $map = null;
                $input.val('');
            }

            var element = $($element[0]);
            var zoom_controll = element.data('zoom-controll'); //console.log( zoom_control );
            var scroll_zoom = element.data('scroll-zoom'); //console.log( scroll_zoom );
            
            $map = new ymaps.Map($element[0], {
                zoom: $params.zoom,
                center: [$params.center_lat, $params.center_lng],
                type: 'yandex#' + $params.type
            }, {
                minZoom: 0
            });

            $map.controls.remove('fullscreenControl');
            $map.controls.remove('rulerControl');
            $map.controls.remove('trafficControl');
            // $map.controls.remove('searchControl');

            $map.events.add('click', function (e) {
                create_mark(e.get('coords'), null, null, null, null, 'create');
                save_map();
            });

            $map.events.add('typechange', function (e) {
                save_map();
            });

            $map.events.add('boundschange', function () {
                save_map();
            });

            // Search Control
            var search_controll = $map.controls.get('searchControl');
            search_controll.options.set({
                noPlacemark: true,
                useMapBounds: false,
                noSelect: true,
                kind: 'locality',
                width: 250
            });

            search_controll.events.add('resultselect', function () {
                $map.geoObjects.removeAll();
                save_map();
            });

            // Geo location button
            var geo_control = $map.controls.get('geolocationControl');
            geo_control.events.add('locationchange', function () {
                $map.geoObjects.removeAll();
                save_map();
            });

            // Zoom Control
            var zoom_control = new ymaps.control.ZoomControl();
            zoom_control.events.add('zoomchange', function (event) {
                save_map();
            });

            // $map.controls.add(zoom_control, {top: 75, left: 5});

            if ( zoom_controll ) {  
                $map.controls.add('zoomControl');
            } else {    
                $map.controls.remove('zoomControl');
            }
            
            if ( scroll_zoom ) {    
                $map.behaviors.enable('scrollZoom');
            } else {    
                $map.behaviors.disable('scrollZoom');
            }

            // Clear all button
            var clear_button = new ymaps.control.Button({
                data: {
                    content: acf_yandex_locale.btn_clear_all,
                    title: acf_yandex_locale.btn_clear_all_hint
                },
                options: {
                    selectOnClick: false
                }
            });

            clear_button.events.add('click', function () {
                $map.balloon.close();
                $map.geoObjects.removeAll();
                save_map();
            });

            $map.controls.add(clear_button, {top: 5, right: 5});

            // Marks load
            $($params.marks).each(function (index, mark) {
                create_mark(mark.coords, mark.type, mark.circle_size, mark.id, mark.content, 'load');
            });

            // Map balloon
            var center = $map.getCenter();

            $map.balloon.events.add('autopanbegin', function () {
                center = $map.getCenter();
            });

            $map.balloon.events.add('close', function () {
                $map.setCenter(center, $map.getZoom(), {
                    duration: 500
                });
            });

            $map.balloon.events.add('open', function () {
                $($el).find('.ya-import form').submit(function () {
                    import_map($(this).serializeArray());
                    return false;
                });
                $($el).find('.ya-import textarea, .ya-export textarea').focus().select();
            });

            // Mark editor
            $map.events.add('balloonopen', function () {
                $('.ya-editor textarea').focus();

                $('.ya-editor .remove').click(function (event) {
                    var mark_id = $(event.currentTarget).parent('form').children('input[type="hidden"]').val();
                    if (mark_id == undefined) return false;

                    $map.balloon.close();
                    $map.geoObjects.each(function (mark) {
                        if (mark.properties.get('id') == mark_id)
                            $map.geoObjects.remove(mark);
                    });

                    return false;
                });

                $('.ya-editor form').submit(function () {
                    var data = $(this).serializeArray();
                    var form = {};
                    $.map(data, function (n, i) {
                        form[n['name']] = n['value'];
                    });

                    $map.geoObjects.each(function (mark) {
                        if (mark.properties.get('id') == form.id) {
                            mark.properties.set('content', form.content);
                            save_map();
                        }
                    });

                    $map.balloon.close();

                    return false;
                });

            });

        }

        function create_mark(coords, type, size, id, content, event) {

            if ( event == 'create' && $params.marks.length !== 0 ) {
                return;
            }

            var place_mark = null;
            var mark_id = id;

            if (id == undefined && $params.marks.length == 0)
                mark_id = 1;
            else
                mark_id = (id == undefined) ? ($params.marks[$params.marks.length - 1].id + 1) : id;

            var mark_content = (content == undefined) ? '' : content;

            place_mark = new ymaps.Placemark(
                coords,
                {
                    //iconContent: mark_id,
                    hintContent: acf_yandex_locale.mark_hint
                }, {
                    draggable: true
                }
            );

            place_mark.events.add('contextmenu', function () {
                $map.geoObjects.remove(this);
                save_map();
            }, place_mark);

            place_mark.events.add('dragend', function () {
                save_map();
            });

            place_mark.events.add('click', function () {
                if (!this.balloon.isOpen()) {
                    show_mark_editor(this);
                }
            }, place_mark);

            place_mark.properties.set('id', mark_id);
            place_mark.properties.set('content', mark_content);

            $map.geoObjects.add(place_mark);
        }

        // Write map data in hidden field
        function save_map() {

            $params.zoom = $map.getZoom();

            var coords = $map.getCenter();
            $params.center_lat = coords[0];
            $params.center_lng = coords[1];

            var type = $map.getType().split('#');
            $params.type = (type[1]) ? type[1] : 'map';

            var marks = [];
            $map.geoObjects.each(function (mark) {
                var _type = mark.geometry.getType();
                marks.push({
                    id: mark.properties.get('id'),
                    content: mark.properties.get('content'),
                    type: _type,
                    coords: mark.geometry.getCoordinates(),
                    circle_size: (_type == 'Circle') ? mark.geometry.getRadius() : 0
                });
            });
            $params.marks = marks;

            $($input).val(JSON.stringify($params));
        }

        // Import map from json
        function import_map(data) {

            if (data.length == 0)
                return false;

            if (data[0].name != 'import')
                return false;

            try {
                var imported = $.parseJSON(data[0].value);
            }
            catch (err) {
                console.error(err, 'Import map error');
                alert('Import map error');
                return false;
            }

            $params = imported;

            map_init();

            return false;
        }

        function show_mark_editor(mark) {

            var html = '<div class="ya-editor" style="margin: 5px"><form name="mark"><input type="hidden" name="id" value="' +
                mark.properties.get('id') + '"><textarea name="content" rows="5" cols="40">' + mark.properties.get('content') +
                '</textarea><input type="submit" class="button button-primary" value="' + acf_yandex_locale.mark_save +
                '"/>&nbsp;<button class="button remove">' + acf_yandex_locale.mark_remove + '</button></form></div>';

            $map.balloon.open(mark.geometry.getCoordinates(), html);

        }

    }

    if (typeof acf.add_action !== 'undefined') {

        /*
         *  ready append (ACF5)
         *
         *  These are 2 events which are fired during the page load
         *  ready = on page load similar to $(document).ready()
         *  append = on new DOM elements appended via repeater field
         *
         *  @type	event
         *  @date	20/07/13
         *
         *  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
         *  @return	n/a
         */

        acf.add_action('ready append', function ($el) {
            // search $el for fields of type 'FIELD_NAME'
            acf.get_fields({type: 'yandex-map'}, $el).each(function () {

                initialize_field($(this));
            });

        });

    } else {

        /*
         *  acf/setup_fields (ACF4)
         *
         *  This event is triggered when ACF adds any new elements to the DOM.
         *
         *  @type	function
         *  @since	1.0.0
         *  @date	01/01/12
         *
         *  @param	event		e: an event object. This can be ignored
         *  @param	Element		postbox: An element which contains the new HTML
         *
         *  @return	n/a
         */

        $(document).on('acf/setup_fields', function (e, postbox) {

            $(postbox).find('.field[data-field_type="yandex-map"]').each(function () {

                initialize_field($(this));
            });

        });

    }

})(jQuery);