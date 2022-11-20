(function ($) {

    'use strict';

    ymaps.ready(function () {

        var $maps = $('.yandex-map');
        $maps.each(function (index, value) {
            var $mapElement = $(value),
                id = $mapElement.attr('id');

            if ( id !== undefined && window[id] !== undefined ) {

                var $params = $.parseJSON(window[id]['params']);   
                var zoom_controll = $mapElement.data('zoom-controll'); 
                var scroll_zoom = $mapElement.data('scroll-zoom'); 

                var $map = new ymaps.Map(id, {
                    zoom: $params.zoom,
                    center: [$params.center_lat, $params.center_lng],
                    type: 'yandex#' + $params.type,
                    behaviors: ['dblClickZoom', 'multiTouch', 'drag']
                }, {
                    minZoom: 0
                });

                $map.controls.remove('trafficControl');
                $map.controls.remove('searchControl');
                $map.controls.remove('geolocationControl');

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
                
                $($params.marks).each(function (index, mark) {
                    var place_mark = null;
                    place_mark = new ymaps.Placemark(mark.coords, {
                        balloonContent: mark.content
                    });

                    $map.geoObjects.add(place_mark);
                });

            }
        });

    });

})(jQuery);