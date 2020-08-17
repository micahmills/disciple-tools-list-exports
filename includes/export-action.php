<?php

add_action( 'dt_post_contact_list_sidebar', 'dt_list_exports_filters' );
function dt_list_exports_filters() {
    ?>
    <div class="bordered-box collapsed">
        <div class="section-header"><?php esc_html_e( 'List Exports', 'disciple_tools' )?>
<!--            <button class="help-button float-right" data-section="export-help-text">-->
<!--                <img class="help-icon" src="--><?php //echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?><!--" alt="help"/>-->
<!--            </button>-->
            <button class="section-chevron chevron_down">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>" alt="expand"/>
            </button>
            <button class="section-chevron chevron_up">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>" alt="collapse"/>
            </button>
        </div>
        <div class="section-body" style="padding-top:1em;">
            <a id="bcc-email-list">bcc email list</a><br>
            <a id="phone-list">phone number list</a><br>
            <a id="csv-list">csv list</a><br>
            <a id="map-list">map list</a><br>
        </div>
        <div class="help-section" id="export-help-text" style="display: none">
            <h3><?php echo esc_html_x( "Export List", 'Optional Documentation', 'disciple_tools' ) ?></h3>
            <p><?php echo esc_html_x( "These links build exports from the current list. If the list is longer than show, you must extend the list to include all list items past 100. Emails are broken into groups of 50 because of common BCC limits and email service send limits.", 'Optional Documentation', 'disciple_tools' ) ?></p>
        </div>
    </div>
    <div id="export-reveal" class="reveal" data-reveal>
        <p class="section-header" id="export-title"></p>
        <hr>
        <div id="export-content"></div>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div id="export-reveal-map" class="full reveal" data-reveal>
        <p class="section-header" id="export-title-full"></p>
        <div id="export-content-full">
            <div id="dynamic-styles"></div>
            <div id="map-wrapper">
                <div id='map'></div>
            </div>
        </div>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <script>
        jQuery(document).ready(function($){
            window.mapbox_key = '<?php echo ( class_exists( 'DT_Mapbox_API' ) && DT_Mapbox_API::get_key() ) ? esc_attr( DT_Mapbox_API::get_key() ) : '' ; ?>'
            window.export_list = []

            /* BCC EXPORT **************************************/
            let email_list_button = $('#bcc-email-list')
            email_list_button.on('click', function(){
                $.when( $.ajax(export_contacts( 0, 'name' ) ) ).then(function() {
                    generate_emails()
                    generate_email_modal()
                })
            })
            function generate_emails() {
                let email_list = []
                let count = 0
                let group = 0
                $.each(window.export_list, function (i, v) {
                    if (typeof v.contact_email !== 'undefined' && v.contact_email !== '') {
                        if (typeof email_list[group] === "undefined") {
                            email_list[group] = ''
                        }
                        $.each(v.contact_email, function (ii, vv) {
                            email_list[group] += vv.value + ','
                            count++
                        })
                        if (count > 50) {
                            group++
                            count = 0
                        }
                    }
                })

                // loop 50 each
                $.each(email_list, function (index, string) {
                    index++
                    window.location.href = "mailto:?subject=group" + index + "&bcc=" + string
                })
            }
            function generate_email_modal(){
                jQuery('#export-title').html('BCC Email List')
                let bcc_email_content = jQuery('#export-content')
                bcc_email_content.empty()

                bcc_email_content.append(`
                    <div class="grid-x">
                        <div class="cell small-6">
                            <strong>No Addresses (<span id="list-count-without"></span>)</strong>
                            <div id="contacts-without"></div>
                        </div>
                        <div class="cell small-6">
                            <strong>With Additional Addresses (<span id="list-count-with"></span>)</strong>
                            <div id="contacts-with"></div>
                        </div>
                    </div>
                    <hr>
                    <div class="grid-x">
                        <a onclick="jQuery('#email-list-print').show();"><strong>Full List (<span id="list-count-full"></span>)</strong></a>
                        <div class="cell" id="email-list-print" style="display:none;"></div>
                    </div>
                `)

                let email_list = []
                let list_count = {
                    with: 0,
                    without: 0,
                    full: 0
                }
                let count = 0
                let group = 0
                let contacts_with = jQuery('#contacts-with')
                let contacts_without = jQuery('#contacts-without')

                $.each(window.export_list, function (i, v) {
                    if (typeof v.contact_email !== 'undefined' && v.contact_email !== '') {
                        if (typeof email_list[group] === "undefined") {
                            email_list[group] = ''
                        }
                        $.each(v.contact_email, function (ii, vv) {
                            email_list[group] += vv.value + ', '
                            count++
                            list_count['full']++
                        })
                        if (typeof v.contact_email[1] !== "undefined") {
                            contacts_with.append(`<a href="/contacts/${v.ID}">${v.post_title}</a><br>`)
                            list_count['with']++
                        }
                        if (count > 50) {
                            group++
                            count = 0
                        }
                    } else {
                        contacts_without.append(`<a href="/contacts/${v.ID}">${v.post_title}</a><br>`)
                        list_count['without']++
                    }
                })

                let list_print = jQuery('#email-list-print')
                $.each(email_list, function (index, string) {
                    list_print.append(string)
                    index++
                })

                // console.log(list_count)
                jQuery('#list-count-with').html(list_count['with'])
                jQuery('#list-count-without').html(list_count['without'])
                jQuery('#list-count-full').html(list_count['full'])

                $('#export-reveal').foundation('open')
            }
            email_list_button.on('click', function(){

                $.when( $.ajax(export_contacts( 0, 'name' ) ) ).then(function() {


                }) /* end when */
            })


            /* PHONE EXPORT **************************************/
            let phone_list = $('#phone-list')
            phone_list.on('click', function(){

                $.when( $.ajax(export_contacts( 0, 'name' ) ) ).then(function() {

                    jQuery('#export-title').html('Phone List')
                    let bcc_email_content = jQuery('#export-content')
                    bcc_email_content.empty()

                    bcc_email_content.append(`
                    <div class="grid-x">
                        <strong>Full List (<span id="list-count-full"></span>)</strong>
                        <div class="cell" id="email-list-print"></div>
                    </div>
                    <hr>
                    <div class="grid-x">
                        <div class="cell small-6">
                            <strong>No Phone Numbers (<span id="list-count-without"></span>)</strong>
                            <div id="contacts-without"></div>
                        </div>
                        <div class="cell small-6">
                            <strong>With Additional Phone Numbers (<span id="list-count-with"></span>)</strong>
                            <div id="contacts-with"></div>
                        </div>
                    </div>
                `)

                    let email_list = []
                    let list_count = {
                        with: 0,
                        without: 0,
                        full: 0
                    }
                    let count = 0
                    let group = 0
                    let contacts_with = jQuery('#contacts-with')
                    let contacts_without = jQuery('#contacts-without')

                    $.each(window.export_list, function (i, v) {
                        if (typeof v.contact_phone !== 'undefined' && v.contact_phone !== '') {
                            if (typeof email_list[group] === "undefined") {
                                email_list[group] = ''
                            }
                            $.each(v.contact_phone, function (ii, vv) {
                                email_list[group] += vv.value + ', '
                                count++
                                list_count['full']++
                            })
                            if (typeof v.contact_phone[1] !== "undefined") {
                                contacts_with.append(`<a href="/contacts/${v.ID}">${v.post_title}</a><br>`)
                                list_count['with']++
                            }
                            if (count > 50) {
                                group++
                                count = 0
                            }
                        } else {
                            contacts_without.append(`<a href="/contacts/${v.ID}">${v.post_title}</a><br>`)
                            list_count['without']++
                        }
                    })

                    let list_print = jQuery('#email-list-print')
                    $.each(email_list, function (index, string) {
                        list_print.append(string)
                        index++
                    })

                    // console.log(list_count)
                    jQuery('#list-count-with').html(list_count['with'])
                    jQuery('#list-count-without').html(list_count['without'])
                    jQuery('#list-count-full').html(list_count['full'])

                    $('#export-reveal').foundation('open')
                })
            })



            /* CSV LIST EXPORT **************************************/
            let csv_list = $('#csv-list')
            csv_list.on('click', function(){

                $.when( $.ajax(export_contacts( 0, 'name' ) ) ).then(function() {

                    window.csv_export = []

                    $.each(window.export_list, function (i, v) {

                        window.csv_export[i] = {}
                        window.csv_export[i]['title'] = v.post_title

                        if (typeof v.contact_phone !== 'undefined' && v.contact_phone !== '') {
                            window.csv_export[i]['phone'] = v.contact_phone[0].value
                        } else {
                            window.csv_export[i]['phone'] = ''
                        }
                        if (typeof v.contact_email !== 'undefined' && v.contact_email !== '') {
                            window.csv_export[i]['email'] = v.contact_email[0].value
                        } else {
                            window.csv_export[i]['email'] = ''
                        }

                    })


                    DownloadJSON2CSV(window.csv_export);


                }) /*end when*/

                function DownloadJSON2CSV(objArray)
                {
                    var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;

                    var str = '';

                    for (var i = 0; i < array.length; i++) {
                        var line = '';

                        for (var index in array[i]) {
                            line += '"' + array[i][index] + '",';
                        }

                        line.slice(0,line.Length-1);

                        str += line + '\r\n';
                    }
                    window.open( "data:text/csv;charset=utf-8," + escape(str))
                }

            })


            /* MAP LIST EXPORT **************************************/
            if ( window.mapbox_key ) {
                let map_content = jQuery('#dynamic-styles')
                map_content.append(`
                            <style>
                                #map-wrapper {
                                    height: ${window.innerHeight - 100}px !important;
                                    position:relative;
                                }
                                #map {
                                    height: ${window.innerHeight - 100}px !important;
                                }

                            </style>
                    `)
                let map_list = $('#map-list')
                map_list.on('click', function(){

                    $.when( $.ajax(export_contacts( 0, 'name' ) ) ).then(function() {

                        jQuery('#export-title-full').html('Map of List')
                        $('#export-reveal-map').foundation('open')

                        mapboxgl.accessToken = window.mapbox_key;
                        var map = new mapboxgl.Map({
                            container: 'map',
                            style: 'mapbox://styles/mapbox/light-v10',
                            center: [-30, 20],
                            minZoom: 1,
                            maxZoom: 8,
                            zoom: 1
                        });

                        // disable map rotation using right click + drag
                        map.dragRotate.disable();
                        map.touchZoomRotate.disableRotation();

                        // load sources
                        map.on('load', function () {

                            let features = []
                            $.each(window.export_list, function(i,v){
                                if ( typeof v.location_grid_meta !== 'undefined') {
                                    features.push({
                                        'type': 'Feature',
                                        'geometry': {
                                            'type': 'Point',
                                            'coordinates': [v.location_grid_meta[0].lng, v.location_grid_meta[0].lat]
                                        },
                                        'properties': {
                                            'title': v.post_title,
                                            'label': v.location_grid_meta[0].label
                                        }
                                    })
                                }
                            })

                            let geojson = {
                                'type': 'FeatureCollection',
                                'features': features
                            }


                            map.addSource('pointsSource', {
                                'type': 'geojson',
                                'data': geojson
                            });
                            map.addLayer({
                                id: 'points',
                                type: 'circle',
                                source: 'pointsSource',
                                paint: {
                                    'circle-radius': {
                                        'base': 6,
                                        'stops': [
                                            [1, 6],
                                            [3, 6],
                                            [4, 6],
                                            [5, 8],
                                            [6, 10],
                                            [7, 12],
                                            [8, 14],
                                        ]
                                    },
                                    'circle-color': '#2CACE2'
                                }
                            });

                            if ( window.contact_list.total < 100 ) {
                                $.each(window.export_list, function(i,v){
                                    if ( typeof v.location_grid_meta !== 'undefined') {
                                        new mapboxgl.Popup()
                                            .setLngLat([v.location_grid_meta[0].lng, v.location_grid_meta[0].lat])
                                            .setHTML(v.post_title + '<br>' + v.location_grid_meta[0].label)
                                            .addTo(map);
                                    }
                                })
                            }

                            map.on('mouseenter', 'points', function(e) {
                                map.getCanvas().style.cursor = 'pointer';
                                var coordinates = e.features[0].geometry.coordinates.slice();
                                var description = e.features[0].properties.title + '<br>' + e.features[0].properties.label;

                                while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                                    coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                                }

                                new mapboxgl.Popup()
                                    .setLngLat(coordinates)
                                    .setHTML(description)
                                    .addTo(map);
                            });

                            map.on('mouseleave', 'points', function() {
                                map.getCanvas().style.cursor = '';
                            });


                            var bounds = new mapboxgl.LngLatBounds();
                            geojson.features.forEach(function(feature) {
                                bounds.extend(feature.geometry.coordinates);
                            });
                            map.fitBounds(bounds);

                        })
                    })
                })
            }

            function export_contacts( offset, sort ) {
                window.export_list = []
                let items = []
                let loading_spinner = $("#list-loading-spinner")
                let getContactsPromise = null
                let cachedFilter = window.SHAREDFUNCTIONS.get_json_cookie("last_view")
                let closedSwitch = $(".show-closed-switch");
                let showClosedCheckbox = $('#show_closed')
                let currentFilter = window.current_filter
                let customFilters = []
                let checked = $(".js-list-view:checked")
                let currentView = checked.val()
                let filterId = checked.data("id") || currentView
                let query = {}
                let filter = {
                    type:"default",
                    ID:currentView,
                    query:{},
                    labels:[{ id:"all", name:wpApiListSettings.translations.filter_all, field: "assigned"}]
                }
                customFilters.push(JSON.parse(JSON.stringify(currentFilter)))
                if ( currentView === "custom_filter"){
                    let filterId = checked.data("id")
                    filter = _.find(customFilters, {ID:filterId})
                    filter.type = currentView
                    query = filter.query
                } else if ( currentView ) {
                    filter = _.find(wpApiListSettings.filters.filters, {ID:filterId}) || _.find(wpApiListSettings.filters.filters, {ID:filterId.toString()}) || filter
                    if ( filter ){
                        filter.type = 'default'
                        filter.labels =  filter.labels || [{ id:filterId, name:filter.name}]
                        query = filter.query
                    }
                }

                if (currentView === "custom_filter" || currentView === "saved-filters" ){
                    closedSwitch.show()
                } else {
                    closedSwitch.hide()
                }

                filter.query = query
                let sortField = _.get(currentFilter, "query.sort", "overall_status")
                filter.query.sort = _.get(currentFilter, "query.sort", "overall_status");
                if ( _.get( cachedFilter, "query.sort") ){
                    filter.query.sort = cachedFilter.query.sort;
                    sortField = _.get(cachedFilter, "query.sort", "overall_status")
                }
                currentFilter = JSON.parse(JSON.stringify(filter))


                let data = currentFilter.query

                if ( offset ){
                    data.offset = offset
                }
                if ( sort ){
                    data.sort = sort
                    data.offset = 0
                } else if (!data.sort) {
                    data.sort = 'name';
                    if ( wpApiListSettings.current_post_type === "contacts" ){
                        data.sort = 'overall_status'
                    } else if ( wpApiListSettings.current_post_type === "groups" ){
                        data.sort = "group_type";
                    }
                }

                currentFilter.query = data
                document.cookie = `last_view=${JSON.stringify(currentFilter)}`


                let showClosed = showClosedCheckbox.prop("checked")
                if ( !showClosed && ( currentView === 'custom_filter' || currentView === 'saved-filters' ) ){
                    if ( wpApiListSettings.current_post_type === "contacts" ){
                        if ( !data.overall_status ){
                            data.overall_status = [];
                        }
                        if ( !data.overall_status.includes("-closed") ){
                            data.overall_status.push( "-closed" )
                        }
                    }
                }
                //abort previous promise if it is not finished.
                if (getContactsPromise && _.get(getContactsPromise, "readyState") !== 4){
                    getContactsPromise.abort()
                }
                let fields = [];
                fields = [ 'location_grid_meta', 'contact_phone', 'contact_email' ]
                data.fields_to_return = fields

                let required = 0
                let complete = 0

                while( window.contact_list.total > data.offset ) {
                    required++

                    getContactsPromise = $.ajax({
                        url: wpApiListSettings.root + "dt-posts/v2/" + wpApiListSettings.current_post_type + "/",
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', wpApiListSettings.nonce);
                        },
                        data: data,
                    })
                    getContactsPromise.then((data)=>{
                        if (offset){
                            items = _.unionBy(items, data.posts || [], "ID")
                        } else  {
                            items = data.posts || []
                        }
                        if (typeof window.export_list === 'undefined' ) {
                            window.export_list = items
                        } else {
                            let arr = $.merge( [], window.export_list )
                            window.export_list = $.merge( arr, items );
                        }

                        complete++
                        if ( required === complete ) {
                            return window.export_list;
                        }

                        loading_spinner.removeClass("active")
                    }).catch(err => {
                        if ( _.get( err, "statusText" ) !== "abort" ) {
                            console.error(err)
                            complete++
                            if ( required === complete ) {
                                return true;
                            }
                        }
                    })

                    data.offset = data.offset + 100
                }
            }

        })
    </script>
    <?php
}
