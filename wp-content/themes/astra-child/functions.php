<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {
	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
	// Only enqueue JS if file exists
	if ( file_exists( get_stylesheet_directory() . '/script.js' ) ) {
		wp_enqueue_script( 
			'astra-child-theme-js', 
			get_stylesheet_directory_uri() . '/script.js', 
			array('jquery'), 
			CHILD_THEME_ASTRA_CHILD_VERSION, 
			true 
		);
	}

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

function enqueue_animate_css() {
    wp_enqueue_style(
        'animate-css',
        'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css',
        array(),
        null
    );
}
add_action('wp_enqueue_scripts', 'enqueue_animate_css');

add_action( 'astra_primary_content_top', function() {
    if ( is_search() ) {
        $search_term = get_search_query();
        ?>
        <section class="ast-archive-description">
            <h1 class="page-title ast-archive-title">
                <?php
                    /* translators: %s: search term */
                    printf( esc_html__( 'Search Results for: %s', 'astra' ),
                        '<span>' . esc_html( $search_term ) . '</span>'
                    );
                ?>
            </h1>
            <div class="ast-breadcrumbs-wrapper">
        		<div class="ast-breadcrumbs-inner">
        			<nav role="navigation" aria-label="Breadcrumbs" class="breadcrumb-trail breadcrumbs">
        			    <div class="ast-breadcrumbs">
        			        <ul class="trail-items">
        			            <li class="trail-item trail-begin"><a href="https://auronova.2stallions.network/" rel="home"><span>Home</span></a></li>
        			            <li class="trail-item trail-end"><span><span> 
        			            <?php
                                    /* translators: %s: search term */
                                    printf( esc_html__( 'Search Results for: %s', 'astra' ),
                                        '<span>' . esc_html( $search_term ) . '</span>'
                                    );
                                ?>
        			            </span></span></li>
    			            </ul>
			            </div>
		            </nav>		
    			</div>
        	</div>
        </section>
        <?php
    }
}, 10 );

// Shortcode: Display Both Author + Date Together
function show_post_meta() {
    $author = get_the_author();
    $date   = get_the_date('F j, Y');
    return '<div class="post-meta">
                <span class="post-author" style="display: flex; column-gap: 10px;"><svg style="width: 15px; fill: var(--ast-global-color-0);" aria-hidden="true" class="e-font-icon-svg e-fas-user" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm89.6 32h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-74.2-60.2-134.4-134.4-134.4z"></path></svg>' . $author . '</span>
                <span class="post-date" style="display: flex; column-gap: 10px;"><svg style="width: 15px; fill: var(--ast-global-color-0);"" aria-hidden="true" class="e-font-icon-svg e-fa-calendar" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M12 192h424c6.6 0 12 5.4 12 12v260c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V204c0-6.6 5.4-12 12-12zm436-44v-36c0-26.5-21.5-48-48-48h-48V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H160V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H48C21.5 64 0 85.5 0 112v36c0 6.6 5.4 12 12 12h424c6.6 0 12-5.4 12-12z"></path></svg>' . $date . '</span>
            </div>';
}
add_shortcode('post_meta', 'show_post_meta');

function floating_whatsapp_button() {
    $phone    = '6588002588';      // ← Change to your number (with country code, no + or spaces)
    $message  = 'Hello! I would like to know more.'; // ← Pre-filled message (optional)
    $encoded  = urlencode($message);
    ?>

    <!-- Floating WhatsApp Button -->
    <style>
        .wa-float {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            text-decoration: none;
            transform: translate(0px);
            transition: transform .3s ease-in-out;
        }
        
        .wa-float.jump { transform: translate(5px, -80px); }

        .wa-float .wa-label {
            background: #fff;
            color: #128C7E;
            font-family: sans-serif;
            font-size: 13px;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            opacity: 0;
            transform: translateX(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            white-space: nowrap;
        }

        .wa-float:hover .wa-label {
            opacity: 1;
            transform: translateX(0);
        }

        .wa-float .wa-icon {
            width: 58px;
            height: 58px;
            background: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.5);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            flex-shrink: 0;
        }

        .wa-float:hover .wa-icon {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.65);
        }

        .wa-float .wa-icon svg {
            width: 30px;
            height: 30px;
            fill: #fff;
        }

        /* Pulse ring animation */
        .wa-float .wa-icon::before {
            content: '';
            position: absolute;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: rgba(37, 211, 102, 0.4);
            animation: wa-pulse 2s ease-out infinite;
        }
        
        @media screen and (max-width: 1024px) {}
        @media screen and (max-width: 768px) {
            .wa-float.jump { transform: translate(5px, -80px); }
            .wa-float.jump.active { transform: translate(5px, -180px); }
            .wa-float .wa-label { display: none; }
        }

        @keyframes wa-pulse {
            0%   { transform: scale(1);   opacity: 0.7; }
            100% { transform: scale(1.8); opacity: 0; }
        }
    </style>

    <a class="wa-float"
       href="https://wa.me/<?php echo esc_attr($phone); ?>?text=<?php echo esc_attr($encoded); ?>"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Chat with us on WhatsApp">

        <span class="wa-label">Chat with us</span>

        <div class="wa-icon">
            <!-- Official WhatsApp SVG icon -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                <path d="M16 0C7.163 0 0 7.163 0 16c0 2.833.738 5.49 2.027 7.8L0 32l8.418-2.004A15.93 15.93 0 0 0 16 32c8.837 0 16-7.163 16-16S24.837 0 16 0zm0 29.333a13.267 13.267 0 0 1-6.756-1.843l-.484-.287-5.002 1.191 1.23-4.874-.317-.502A13.224 13.224 0 0 1 2.667 16C2.667 8.636 8.636 2.667 16 2.667S29.333 8.636 29.333 16 23.364 29.333 16 29.333zm7.27-9.862c-.398-.199-2.354-1.161-2.719-1.294-.365-.133-.631-.199-.897.199-.266.398-1.029 1.294-1.261 1.56-.232.266-.465.299-.863.1-.398-.199-1.681-.619-3.203-1.977-1.183-1.057-1.981-2.363-2.214-2.761-.232-.398-.025-.614.175-.812.18-.179.398-.465.597-.698.199-.232.266-.398.398-.664.133-.266.066-.498-.033-.697-.1-.199-.897-2.163-1.229-2.961-.324-.777-.653-.672-.897-.684l-.764-.013c-.266 0-.697.1-1.062.498-.365.398-1.394 1.362-1.394 3.322 0 1.96 1.428 3.854 1.627 4.12.199.266 2.81 4.29 6.808 6.018.951.41 1.694.655 2.272.839.955.303 1.824.26 2.511.158.766-.114 2.354-.963 2.686-1.893.332-.93.332-1.727.232-1.893-.099-.165-.365-.265-.763-.464z"/>
            </svg>
        </div>
    </a>

    <?php
}
add_action('wp_footer', 'floating_whatsapp_button');