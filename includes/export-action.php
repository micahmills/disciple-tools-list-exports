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
            <a id="bcc-email-list" data-open="bcc-email-list">bcc email list</a><br>
            <a id="phone-list" data-open="phone-list">phone number list</a><br>
<!--            <a id="csv-list" data-open="csv-list">csv list</a><br>-->
<!--            <button type="button" class="button small hollow expanded" data-open="map-list">Map List</button>-->
<!--            <button type="button" class="button small hollow expanded" data-open="csv-list">CSV Export List</button>-->
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

    <script>
        jQuery(document).ready(function($){
            /* BCC FUNCTIONS */
            let email_list = $('#bcc-email-list')
            email_list.on('click', function(){
                let email_list = []
                let count = 0
                let group = 0
                $.each(window.contact_list, function(i,v){
                    if (typeof v.contact_email !== 'undefined' && v.contact_email !== '' ) {
                        if (typeof email_list[group] === "undefined" ) {
                            email_list[group] = ''
                        }
                        $.each(v.contact_email, function(ii,vv) {
                            email_list[group] += vv.value + ','
                            count++
                        })
                        if ( count > 50 ) {
                            group++
                            count = 0
                        }
                    }
                })

                // loop 50 each
                $.each(email_list, function(index, string) {
                    index++
                    window.location.href = "mailto:?subject=group"+index+"&bcc="+string // @todo reenable for production
                })
            })
            email_list.on('click', function(){
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

                $.each(window.contact_list, function(i,v){
                    if (typeof v.contact_email !== 'undefined' && v.contact_email !== '' ) {
                        if (typeof email_list[group] === "undefined" ) {
                            email_list[group] = ''
                        }
                        $.each(v.contact_email, function(ii,vv) {
                            email_list[group] += vv.value + ', '
                            count++
                            list_count['full']++
                        })
                        if ( typeof v.contact_email[1] !== "undefined"){
                            contacts_with.append(`<a href="/contacts/${v.ID}">${v.post_title}</a><br>`)
                            list_count['with']++
                        }
                        if ( count > 50 ) {
                            group++
                            count = 0
                        }
                    }
                    else {
                        contacts_without.append(`<a href="/contacts/${v.ID}">${v.post_title}</a><br>`)
                        list_count['without']++
                    }
                })

                let list_print = jQuery('#email-list-print')
                $.each(email_list, function(index, string) {
                    list_print.append(string)
                    index++
                })

                // console.log(list_count)
                jQuery('#list-count-with').html(list_count['with'])
                jQuery('#list-count-without').html(list_count['without'])
                jQuery('#list-count-full').html(list_count['full'])

                $('#export-reveal').foundation('open')
            })

            /* PHONE FUNCTIONS */
            let phone_list = $('#phone-list')
            phone_list.on('click', function(){
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

                $.each(window.contact_list, function(i,v){
                    if (typeof v.contact_phone !== 'undefined' && v.contact_phone !== '' ) {
                        if (typeof email_list[group] === "undefined" ) {
                            email_list[group] = ''
                        }
                        $.each(v.contact_phone, function(ii,vv) {
                            email_list[group] += vv.value + ', '
                            count++
                            list_count['full']++
                        })
                        if ( typeof v.contact_phone[1] !== "undefined"){
                            contacts_with.append(`<a href="/contacts/${v.ID}">${v.post_title}</a><br>`)
                            list_count['with']++
                        }
                        if ( count > 50 ) {
                            group++
                            count = 0
                        }
                    }
                    else {
                        contacts_without.append(`<a href="/contacts/${v.ID}">${v.post_title}</a><br>`)
                        list_count['without']++
                    }
                })

                let list_print = jQuery('#email-list-print')
                $.each(email_list, function(index, string) {
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
    </script>
    <?php
}
