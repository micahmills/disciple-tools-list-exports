<?php

add_action('dt_post_contact_list_sidebar', 'dt_list_exports_filters' );
function dt_list_exports_filters() {
    ?>
    <div class="bordered-box collapsed">
        <div class="section-header"><?php esc_html_e( 'List Exports', 'disciple_tools' )?>
            <button class="help-button float-right" data-section="filters-help-text">
                <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>" alt="help"/>
            </button>
            <button class="section-chevron chevron_down">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>" alt="expand"/>
            </button>
            <button class="section-chevron chevron_up">
                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>" alt="collapse"/>
            </button>
        </div>
        <div class="section-body" style="padding-top:1em;">
            <button type="button" class="button small hollow expanded" id="bcc-email-list" data-open="bcc-email-list">BCC Email List</button>
            <button type="button" class="button small hollow expanded" data-open="phone-list">Phone Number List</button>
            <button type="button" class="button small hollow expanded" data-open="map-list">Map List</button>
            <button type="button" class="button small hollow expanded" data-open="csv-list">CSV Export List</button>
        </div>
    </div>
    <div id="bcc-email-reveal" class="reveal" data-reveal>
        <p class="section-header">BCC Email Lists</p>
        <hr>
        <div id="bcc-email-content"></div>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div id="phone-list-reveal" class="reveal" data-reveal>
        <h2>Title</h2>
        <hr>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div id="map-list-reveal" class="reveal" data-reveal>
        <h2>Title</h2>
        <hr>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div id="csv-list-reveal" class="reveal" data-reveal>
        <h2>Title</h2>
        <hr>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <script>
        jQuery(document).ready(function($){
            let email_list = $('#bcc-email-list')
            /* default email client */
            email_list.on('click', function(){
                let email_list = []
                let count = 0
                let group = 0
                $.each(window.contact_list, function(i,v){
                    if (typeof v.contact_email !== 'undefined' ) {
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
                    window.location.href = "mailto:?subject=group"+index+"&bcc="+string
                })
            })
            /* modal content */
            email_list.on('click', function(){
                let email_list = []
                let count = 0
                let group = 0
                $.each(window.contact_list, function(i,v){
                    if (typeof v.contact_email !== 'undefined' ) {
                        if (typeof email_list[group] === "undefined" ) {
                            email_list[group] = ''
                        }
                        $.each(v.contact_email, function(ii,vv) {
                            email_list[group] += vv.value + ', '
                            count++
                        })
                        if ( count > 50 ) {
                            group++
                            count = 0
                        }
                    }
                })

                // loop 50 each
                let bcc_email_content = jQuery('#bcc-email-content')
                bcc_email_content.empty()
                $.each(email_list, function(index, string) {
                    console.log(string)
                    bcc_email_content.append(string)
                    index++

                })

                $('#bcc-email-reveal').foundation('open')
            })
        })
    </script>
    <?php
}
