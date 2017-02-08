<?php

//Register shortcodes
function register_shortcodes() {
    add_shortcode('providers', 'shortcode_providers');
}
add_action('init', 'register_shortcodes');

//run loop in shortcode
function shortcode_providers($atts) {
    global $loop, $content, $content_3;
    //set attributes
    $atts = shortcode_atts(array(
        'location' => '',
        'type' => ''
    ), $atts);

    $location = $atts['location'];
    $type     = $atts['type'];

    //if both parameters exist
    if ($location && $type) {
        //find posts with both taxonomies
        $loop = new WP_Query(array(
            'posts_per_page' => 200,
            'post_type' => 'providers',
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'providers_location',
                    'field' => 'name',
                    'terms' => $atts['location']
                ),

                array(
                    'taxonomy' => 'providers_type',
                    'field' => 'name',
                    'terms' => $atts['type']
                )
            )
        ));

    } else if ($location || $type) {
        //else if one or the other exists find posts with one or the other
        $loop = new WP_Query(array(
            'posts_per_page' => 200,
            'post_type' => 'providers',
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'tax_query' => array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'providers_location',
                    'field' => 'name',
                    'terms' => $atts['location']
                ),

                array(
                    'taxonomy' => 'providers_type',
                    'field' => 'name',
                    'terms' => $atts['type']
                )
            )
        ));

    } else {
        //or if neither exists, display warning/tip
        echo '<p>You must provide at least one parameter for location or employee type! Parameters are case sensitive.</p>';
    }


    if (!$loop->have_posts()) {
        return false;
    }

    while ($loop->have_posts()) {
        $loop->the_post();
        //post content
        //this section checks the type of provider and changes the style's based on that
        if (has_term( 'Associate', 'providers_type' )) {
            //variables
            //get terms
            $terms =  get_the_terms(get_the_ID(), 'providers_location' );

                foreach ( $terms as $term ) {
                    $termID[] = $term->term_id;
                    }
            $loc_ID = $termID[0];
            $has_loc_phone = get_field('phone_number', 'providers_location_' . $loc_ID);
            $format_phone = get_field('telephone');
            //if provider has phone number, use that, if not use the location one.
            if ($format_phone) {
                $prov_phone = preg_replace('/\D+/', '', $format_phone);
            } else {
                $format_phone = $has_loc_phone;
                $prov_phone = preg_replace('/\D+/', '', $format_phone);
            }

            $review_tag = get_field('review_tag');
            $second_location = get_field('second_location');
            $first_name = get_field('first_name');
            $last_name = get_field('last_name');
            $thumbnail = get_the_post_thumbnail( get_the_ID(), 'medium',
                               array ('itemprop' => 'image',
                                      'alt' => $first_name . ' ' . $last_name,
                                     ));
            $booking_id = get_field('booking_id');

            $title = get_the_title();
            $prof_title = get_field('professional_title');
            $post_content = get_the_content();
            $post_content = do_shortcode( $post_content);
            $post_content = wpautop( $post_content );
            $review_snippet = do_shortcode('[RICH_REVIEWS_SNIPPET category="' . $review_tag . '"]');
            $review_provider = do_shortcode('">[learn_more caption="Review This Provider"][RICH_REVIEWS_FORM            category="' . $review_tag . '"]' . '[/learn_more]');
            $review_show = do_shortcode('[learn_more caption="See Reviews"][RICH_REVIEWS_SHOW num="3" category="' .     $review_tag . '"]' . '[/learn_more]</div></div><!--associate-profile-->');

            $address = get_field('street_address') . '<br>' . get_field('city') . ', ' .                        get_field('state') . ' ' . get_field('postal_code');
            //content
            $content = $content .=
                 '<div class="associate-profile" id="' . $first_name . '_' . $last_name . '><div class="associate-left alignleft">' .
                    $thumbnail .
                '<button>
                    <a href="https://thriveworks.gettimely.com/book?staff=' . $booking_id .
                '">Book Now</a>
                </button>
                <button>
                    <a href="tel:+1' . $prov_phone . '">Call Now</a>
                </button>
                <div class="at_phone">at
                <p><strong><span itemprop="telephone">' . $format_phone . '</strong></p></span>
                </div>
                </div>
                <div class="associate-right">
                <div class="assoc-title">
                <h4>' . $title . '</h4><em>' . $prof_title . '</em></div>
                <div class="associate-rev-snippet">' . $review_snippet .
                '</div>
                <div class="associate-address"><strong>Address:</strong></br>
                    <div itemscope itemtype="http://schema.org/LocalBusiness" style="font-size:1.1rem;">
                        <div style="display: none;" itemprop="name"><strong>Thriveworks Associates</strong></div><span itemprop="address">' . $address .
                '</div></div>';
            //address end
            //second address start
            if ($second_location) {

                $address_2 = get_field('street_address_2') . '<br>' . get_field('city_2') . ', ' .                    get_field('state_2') . ' ' . get_field('postal_code_2');

                $content = $content .= '<div class="associate-address"><strong>Address 2:</strong></br>
                    <div itemscope itemtype="http://schema.org/LocalBusiness" style="font-size:1.1rem;">
                        <div style="display: none;" itemprop="name"><strong>Thriveworks Associates</strong></div><span itemprop="address">'. $address_2 . '</div></div>';
            }
            //second address end
            $content =  $content .= '</div><hr /><div class="associate-desc">' . $post_content .
                            '</div><!--associate-desc-->' .
                '<div class="associate-spec "><h5>Helps with...</h5><ul>' . get_field('specialties') . '</ul></div><div class="associate-pay"><h5>Payment Options...</h5><ul>' . get_field('insurance') .  '</ul></div>
                <div id="reviews_' . get_the_ID() . $review_provider . $review_show;
            wp_reset_postdata();

        } else if (has_term( 'Corporate/Franchise', 'providers_type' )) {
            $first_name = get_field('first_name');
            $last_name = get_field('last_name');
            $title = get_the_title();
            $pro_title = get_field('professional_title');
            $thumbnail = get_the_post_thumbnail( get_the_ID(), 'medium',
                               array ('itemprop' => 'image',
                                      'alt' => $first_name . ' ' . $last_name,
                                      'class' => ''
                                     ));
            $intro_video = get_field('intro_video');
            $post_content = get_the_content();
            $post_content = do_shortcode( $post_content);
            $post_content = wpautop( $post_content );
            $content = $content .=
                '<div class="corp-profile" id="' . $first_name . '_' . $last_name . '">
                    <div class="corp-profile-left">';
                if ($intro_video) {
                    $content= $content .= '
                        <a data-lity data-lity-desc="' . $first_name . ' ' .$last_name . ' Introduction Video" href="' . $intro_video . '">' . '
                            <span class="play">&#9658;</span>'
                . $thumbnail .
                    '</a>';
                } else {
                    $content = $content .= $thumbnail;
                }
            $content = $content .= '
            </div>
                    <div class="corp-profile-right"><h2>' . $title . ', ' . $pro_title . '</h2>' . $post_content .
                    '</div>
                    <hr />
                    </div>';
            wp_reset_postdata();

        }
    }
    return $content;
}
