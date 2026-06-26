<?php
/**
 * UAEL Video_Gallery.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\VideoGallery\Widgets;

// Elementor Classes.
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Repeater;
use Elementor\Widget_Button;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Control_Media;
use Elementor\Modules\DynamicTags\Module as TagsModule;

// UltimateElementor Classes.
use UltimateElementor\Base\Common_Widget;
use UltimateElementor\Classes\UAEL_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Class Video_Gallery.
 */
class Video_Gallery extends Common_Widget {

	/**
	 * Retrieve Video_Gallery Widget name.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return parent::get_widget_slug( 'Video_Gallery' );
	}

	/**
	 * Retrieve Video_Gallery Widget title.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return parent::get_widget_title( 'Video_Gallery' );
	}

	/**
	 * Retrieve Video_Gallery Widget icon.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return parent::get_widget_icon( 'Video_Gallery' );
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.5.1
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return parent::get_widget_keywords( 'Video_Gallery' );
	}

	/**
	 * Retrieve the list of scripts the image carousel widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return array(
			'uael-isotope',
			'uael-frontend-script',
			'uael-fancybox',
			'imagesloaded',
			'uael-slick',
			'uael-element-resize',
		);
	}

	/**
	 * Register Buttons controls.
	 *
	 * @since 1.29.2
	 * @access protected
	 */
	protected function register_controls() {

		// Content Tab.
		$this->register_video_gallery_controls();
		$this->register_video_general_controls();
		$this->register_video_filter_setting_controls();
		$this->register_carousel_controls();
		$this->register_helpful_information();

		// Style Tab.
		$this->register_style_layout_controls();
		$this->register_style_title_filter_controls();
		$this->register_style_filter_controls();
		$this->register_style_video_effects_controls();
		$this->register_style_play_controls();
		$this->register_style_caption_controls();
		$this->register_style_navigation_controls();
	}

	/**
	 * Register Gallery General Controls.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function register_video_gallery_controls() {

		$this->start_controls_section(
			'section_gallery',
			array(
				'label' => __( 'Gallery', 'uael' ),
			)
		);

			$vimeo = apply_filters( 'uael_video_gallery_vimeo_link', 'https://vimeo.com/274860274' );

			$youtube = apply_filters( 'uael_video_gallery_youtube_link', 'https://www.youtube.com/watch?v=HJRzUQMhJMQ' );

			$rumble = apply_filters( 'uael_video_gallery_rumble_link', 'https://rumble.com/v6ze3ru-video-placeholder-brainstorm-force.html' );

			$bunny = apply_filters( 'uael_video_gallery_bunny_link', 'https://iframe.mediadelivery.net/play/432016/13530e19-ff52-4f20-a422-0075cccd73d4' );

			$wistia = apply_filters( 'uael_video_gallery_wistia_link', '<p><a href="https://pratikc.wistia.com/medias/gyvkfithw2?wvideo=gyvkfithw2"><img src="https://embedwistia-a.akamaihd.net/deliveries/53eec5fa72737e60aa36731b57b607a7c0636f52.webp?image_play_button_size=2x&amp;image_crop_resized=960x540&amp;image_play_button=1&amp;image_play_button_color=54bbffe0" width="400" height="225" style="width: 400px; height: 225px;"></a></p><p><a href="https://pratikc.wistia.com/medias/gyvkfithw2?wvideo=gyvkfithw2">Video Placeholder - Brainstorm Force - pratikc</a></p>' );

			$repeater = new Repeater();

			$repeater->start_controls_tabs( 'tabs_for_general_and_schema' );

			// General tab starts.
			$repeater->start_controls_tab( 'tab_video_gallery_general', array( 'label' => __( 'General', 'uael' ) ) );

			$repeater->add_control(
				'type',
				array(
					'label'   => __( 'Video Type', 'uael' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'youtube',
					'options' => array(
						'youtube' => __( 'YouTube Video', 'uael' ),
						'vimeo'   => __( 'Vimeo Video', 'uael' ),
						'wistia'  => __( 'Wistia Video', 'uael' ),
						'rumble'  => __( 'Rumble Video', 'uael' ),
						'bunny'   => __( 'Bunny.net Video', 'uael' ),
						'hosted'  => __( 'Self Hosted', 'uael' ),
					),

				)
			);

			$repeater->add_control(
				'insert_url',
				array(
					'label'     => __( 'External URL', 'uael' ),
					'type'      => Controls_Manager::SWITCHER,
					'condition' => array(
						'type' => 'hosted',
					),
				)
			);

			$repeater->add_control(
				'hosted_url',
				array(
					'label'      => __( 'Choose File', 'uael' ),
					'type'       => Controls_Manager::MEDIA,
					'dynamic'    => array(
						'active'     => true,
						'categories' => array(
							TagsModule::MEDIA_CATEGORY,
						),
					),
					'media_type' => 'video',
					'condition'  => array(
						'type'       => 'hosted',
						'insert_url' => '',
					),
				)
			);

			$repeater->add_control(
				'external_url',
				array(
					'label'        => __( 'URL', 'uael' ),
					'type'         => Controls_Manager::URL,
					'autocomplete' => false,
					'options'      => false,
					'label_block'  => true,
					'show_label'   => false,
					'dynamic'      => array(
						'active'     => true,
						'categories' => array(
							TagsModule::POST_META_CATEGORY,
							TagsModule::URL_CATEGORY,
						),
					),
					'media_type'   => 'video',
					'placeholder'  => __( 'Enter your URL', 'uael' ),
					'condition'    => array(
						'type'       => 'hosted',
						'insert_url' => 'yes',
					),
				)
			);

			$repeater->add_control(
				'video_url',
				array(
					'label'       => __( 'Video URL', 'uael' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'dynamic'     => array(
						'active'     => true,
						'categories' => array(
							TagsModule::POST_META_CATEGORY,
							TagsModule::URL_CATEGORY,
						),
					),
					'condition'   => array(
						'type' => array( 'youtube', 'vimeo', 'rumble' ),
					),
				)
			);

			$repeater->add_control(
				'youtube_link_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '<b>Note:</b> Make sure you add the actual URL of the video and not the share URL.</br></br><b>Valid:</b>&nbsp;https://www.youtube.com/watch?v=HJRzUQMhJMQ</br><b>Invalid:</b>&nbsp;https://youtu.be/HJRzUQMhJMQ', 'uael' ) ),
					'content_classes' => 'uael-editor-doc',
					'condition'       => array(
						'type' => 'youtube',
					),
					'separator'       => 'none',
				)
			);

			$repeater->add_control(
				'vimeo_link_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '<b>Note:</b> Make sure you add the actual URL of the video and not the categorized URL.</br></br><b>Valid:</b>&nbsp;https://vimeo.com/274860274</br><b>Invalid:</b>&nbsp;https://vimeo.com/channels/staffpicks/274860274', 'uael' ) ),
					'content_classes' => 'uael-editor-doc',
					'condition'       => array(
						'type' => 'vimeo',
					),
					'separator'       => 'none',
				)
			);

			$repeater->add_control(
				'rumble_link_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '<b>Note:</b> Use the standard Rumble video URL from the browser address bar.</br></br><b>Valid:</b>&nbsp;https://rumble.com/v6ze3ru-video-placeholder-brainstorm-force.html</br><b>Invalid:</b>&nbsp;https://rumble.com/embed/v1abcd', 'uael' ) ),
					'content_classes' => 'uael-editor-doc',
					'condition'       => array(
						'type' => 'rumble',
					),
					'separator'       => 'none',
				)
			);

			$repeater->add_control(
				'wistia_url',
				array(
					'label'       => __( 'Link & Thumbnail Text', 'uael' ),
					'description' => __( 'Go to your Wistia video, right click, "Copy Link & Thumbnail" and paste here.', 'uael' ),
					'type'        => Controls_Manager::TEXT,
					'label_block' => true,
					'dynamic'     => array(
						'active'     => true,
						'categories' => array(
							TagsModule::POST_META_CATEGORY,
							TagsModule::URL_CATEGORY,
						),
					),
					'condition'   => array(
						'type' => 'wistia',
					),
				)
			);

			$repeater->add_control(
				'bunny_url',
				array(
					'label'       => __( 'Link', 'uael' ),
					'type'        => Controls_Manager::TEXT,
					'dynamic'     => array(
						'active'     => true,
						'categories' => array(
							TagsModule::POST_META_CATEGORY,
							TagsModule::URL_CATEGORY,
						),
					),
					'default'     => $bunny,
					'label_block' => true,
					'condition'   => array(
						'type' => 'bunny',
					),
				)
			);

			$repeater->add_control(
				'bunny_url_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf( __( '<b>Note:</b> Use the Bunny.net embed iframe URL format.</br></br><b>Valid:</b>&nbsp;https://iframe.mediadelivery.net/play/libraryId/videoId</br><b>Example:</b>&nbsp;https://iframe.mediadelivery.net/play/432016/13530e19-ff52-4f20-a422-0075cccd73d4', 'uael' ) ),
					'content_classes' => 'uael-editor-doc',
					'condition'       => array(
						'type' => 'bunny',
					),
					'separator'       => 'none',
				)
			);

			$repeater->add_control(
				'bunny_cdn_prefix',
				array(
					'label'       => __( 'Bunny CDN Prefix', 'uael' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'placeholder' => 'vz-f9672ed3-d10',
					'default'     => 'vz-f9672ed3-d10', // Default value, can be changed by user.
					'condition'   => array(
						'type' => 'bunny',
					),
				)
			);

			$repeater->add_control(
				'bunny_cdn_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf( __( '<b>Note:</b> This is required for default thumbnail to load, found in your Bunny.net Stream library delivery URL before .b-cdn.net or navigate to "Your Video Library >> API >> Pullzone" <br> Please check if the “Block direct URL file access” option is enabled. If it is, try disabling it', 'uael' ) ),
					'content_classes' => 'uael-editor-doc',
					'condition'       => array(
						'type' => 'bunny',
					),
					'separator'       => 'none',
				)
			);

			$repeater->add_control(
				'title',
				array(
					'label'       => __( 'Caption', 'uael' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => '',
					'label_block' => true,
					'dynamic'     => array(
						'active' => true,
					),
					'title'       => __( 'This title will be visible on hover.', 'uael' ),
				)
			);

			$repeater->add_control(
				'tags',
				array(
					'label'       => __( 'Categories', 'uael' ),
					'type'        => Controls_Manager::TEXT,
					'default'     => '',
					'label_block' => true,
					'dynamic'     => array(
						'active' => true,
					),
					'title'       => __( 'Add comma separated categories. These categories will be shown for filteration.', 'uael' ),
				)
			);

			$repeater->add_control(
				'yt_thumbnail_size',
				array(
					'label'     => __( 'Thumbnail Size', 'uael' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'maxresdefault' => __( 'Maximum Resolution', 'uael' ),
						'hqdefault'     => __( 'High Quality', 'uael' ),
						'mqdefault'     => __( 'Medium Quality', 'uael' ),
						'sddefault'     => __( 'Standard Quality', 'uael' ),
					),
					'default'   => 'hqdefault',
					'condition' => array(
						'type' => 'youtube',
					),
				)
			);

			$repeater->add_control(
				'custom_placeholder',
				array(
					'label'        => __( 'Custom Thumbnail', 'uael' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Yes', 'uael' ),
					'label_off'    => __( 'No', 'uael' ),
					'return_value' => 'yes',
					'condition'    => array(
						'type!' => 'hosted',
					),
				)
			);
			
			$repeater->add_control(
				'rumble_note_thumbnail',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '<b>Note:</b> You can upload a custom thumbnail if the video thumbnail is not available or not loading.', 'uael' ) ),
					'content_classes' => 'uael-editor-doc',
					'condition'       => array(
						'type' => 'rumble',
					),
					'separator'       => 'none',
				)
			);

			$repeater->add_control(
				'placeholder_image',
				array(
					'label'       => __( 'Select Image', 'uael' ),
					'type'        => Controls_Manager::MEDIA,
					'default'     => array(
						'url' => Utils::get_placeholder_image_src(),
					),
					'description' => __( 'This image will act as a placeholder image for the video.', 'uael' ),
					'dynamic'     => array(
						'active' => true,
					),
					'conditions'  => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'relation' => 'and',
								'terms'    => array(
									array(
										'name'     => 'type',
										'operator' => '!=',
										'value'    => 'hosted',
									),
									array(
										'name'     => 'custom_placeholder',
										'operator' => '==',
										'value'    => 'yes',
									),
								),
							),
							array(
								'name'     => 'type',
								'operator' => '==',
								'value'    => 'hosted',
							),
						),
					),
				)
			);

			$repeater->end_controls_tab();

			// Schema tab starts.
			$repeater->start_controls_tab( 'tab_video_gallery_schema', array( 'label' => __( 'Schema', 'uael' ) ) );

			$repeater->add_control(
				'schema_title',
				array(
					'label'       => __( 'Title', 'uael' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
				)
			);

			$repeater->add_control(
				'schema_description',
				array(
					'label' => __( 'Description', 'uael' ),
					'type'  => Controls_Manager::TEXTAREA,
					'rows'  => 10,
				)
			);

			$repeater->add_control(
				'schema_thumbnail',
				array(
					'label'     => __( 'Thumbnail', 'uael' ),
					'type'      => Controls_Manager::MEDIA,
					'default'   => array(
						'url' => Utils::get_placeholder_image_src(),
					),
					'condition' => array(
						'custom_placeholder!' => 'yes',
					),
				)
			);

			$repeater->add_control(
				'schema_upload_date',
				array(
					'label'   => __( 'Upload Date & Time', 'uael' ),
					'type'    => Controls_Manager::DATE_TIME,
					'default' => gmdate( 'Y-m-d H:i' ),
				)
			);

			$repeater->end_controls_tab();
			$repeater->end_controls_tabs();

			$this->add_control(
				'gallery_items',
				array(
					'type'        => Controls_Manager::REPEATER,
					'show_label'  => true,
					'fields'      => $repeater->get_controls(),
					'default'     => array(
						array(
							'type'               => 'youtube',
							'video_url'          => $youtube,
							'title'              => __( 'First Video', 'uael' ),
							'schema_title'       => __( 'Title of the video.', 'uael' ),
							'schema_description' => __( 'Description of the video.', 'uael' ),
							'schema_thumbnail'   => '',
							'schema_upload_date' => gmdate( 'Y-m-d H:i' ),
							'tags'               => 'YouTube',
							'placeholder_image'  => '',
						),
						array(
							'type'               => 'vimeo',
							'video_url'          => $vimeo,
							'title'              => __( 'Second Video', 'uael' ),
							'schema_title'       => __( 'Title of the video.', 'uael' ),
							'schema_description' => __( 'Description of the video.', 'uael' ),
							'schema_thumbnail'   => '',
							'schema_upload_date' => gmdate( 'Y-m-d H:i' ),
							'tags'               => 'Vimeo',
							'placeholder_image'  => '',
						),
						array(
							'type'               => 'wistia',
							'wistia_url'         => $wistia,
							'title'              => __( 'Third Video', 'uael' ),
							'schema_title'       => __( 'Title of the video.', 'uael' ),
							'schema_description' => __( 'Description of the video.', 'uael' ),
							'schema_thumbnail'   => '',
							'schema_upload_date' => gmdate( 'Y-m-d H:i' ),
							'tags'               => 'Wistia',
							'placeholder_image'  => '',
						),
						array(
							'type'               => 'youtube',
							'video_url'          => $youtube,
							'title'              => __( 'Fourth Video', 'uael' ),
							'schema_title'       => __( 'Title of the video.', 'uael' ),
							'schema_description' => __( 'Description of the video.', 'uael' ),
							'schema_thumbnail'   => '',
							'schema_upload_date' => gmdate( 'Y-m-d H:i' ),
							'tags'               => 'YouTube',
							'placeholder_image'  => '',
						),
						array(
							'type'               => 'vimeo',
							'video_url'          => $vimeo,
							'title'              => __( 'Fifth Video', 'uael' ),
							'schema_title'       => __( 'Title of the video.', 'uael' ),
							'schema_description' => __( 'Description of the video.', 'uael' ),
							'schema_thumbnail'   => '',
							'schema_upload_date' => gmdate( 'Y-m-d H:i' ),
							'tags'               => 'Vimeo',
							'placeholder_image'  => '',
						),
						array(
							'type'               => 'bunny',
							'bunny_url'          => $bunny,
							'bunny_cdn_prefix'   => 'vz-f9672ed3-d10',
							'title'              => __( 'Sixth Video', 'uael' ),
							'schema_title'       => __( 'Title of the video.', 'uael' ),
							'schema_description' => __( 'Description of the video.', 'uael' ),
							'schema_thumbnail'   => '',
							'schema_upload_date' => gmdate( 'Y-m-d H:i' ),
							'tags'               => 'Bunny',
							'placeholder_image'  => '',
						),

					),
					'title_field' => '{{ title }}',
				)
			);

			$this->add_control(
				'schema_support',
				array(
					'label'       => __( 'Schema Support', 'uael' ),
					'description' => __( 'Note: This option enables the VideoObject Schema.', 'uael' ),
					'type'        => Controls_Manager::SWITCHER,
					'label_on'    => __( 'Yes', 'uael' ),
					'label_off'   => __( 'No', 'uael' ),
					'default'     => 'no',
					'separator'   => 'before',
				)
			);

			$this->end_controls_tab();

		$this->end_controls_section();
	}

	/**
	 * Register Video Gallery General Controls.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function register_video_general_controls() {

		$this->start_controls_section(
			'section_general',
			array(
				'label' => __( 'General', 'uael' ),
			)
		);

			$this->add_control(
				'layout',
				array(
					'label'   => __( 'Layout', 'uael' ),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						'grid'     => __( 'Grid', 'uael' ),
						'carousel' => __( 'Carousel', 'uael' ),
					),
					'default' => 'grid',
				)
			);

			$this->add_responsive_control(
				'gallery_columns',
				array(
					'label'              => __( 'Columns', 'uael' ),
					'type'               => Controls_Manager::SELECT,
					'default'            => '3',
					'tablet_default'     => '2',
					'mobile_default'     => '1',
					'options'            => array(
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
						'6' => '6',
					),
					'prefix_class'       => 'uael-video-gallery%s__column-',
					'render_type'        => 'template',
					'frontend_available' => true,
				)
			);

			$this->add_control(
				'video_ratio',
				array(
					'label'   => __( 'Aspect Ratio', 'uael' ),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						'16_9' => '16:9',
						'4_3'  => '4:3',
						'3_2'  => '3:2',
					),
					'default' => '16_9',
				)
			);

			$this->add_control(
				'click_action',
				array(
					'label'   => __( 'Click Action', 'uael' ),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						'lightbox' => 'Play in Lighbox',
						'inline'   => 'Play Inline',
					),
					'default' => 'lightbox',
				)
			);

			$this->add_control(
				'gallery_rand',
				array(
					'label'   => __( 'Ordering', 'uael' ),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						''     => __( 'Default', 'uael' ),
						'rand' => __( 'Random', 'uael' ),
					),
					'default' => '',
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register Video Filters General Controls.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function register_video_filter_setting_controls() {

		$this->start_controls_section(
			'section_filter_content',
			array(
				'label'     => __( 'Filterable Tabs', 'uael' ),
				'condition' => array(
					'layout' => 'grid',
				),
			)
		);

		$this->add_control(
			'show_filter',
			array(
				'label'        => __( 'Filterable Video Gallery', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'return_value' => 'yes',
				'label_off'    => __( 'No', 'uael' ),
				'label_on'     => __( 'Yes', 'uael' ),
				'condition'    => array(
					'layout' => 'grid',
				),
			)
		);

		if ( parent::is_internal_links() ) {
			$this->add_control(
				'video_filters_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s admin link */
					'raw'             => sprintf( __( 'Learn : %1$sHow to design filterable Video Gallery?%2$s', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/how-to-design-filterable-video-gallery/?utm_source=uael-pro-dashboard&utm_medium=uael-editor-screen&utm_campaign=uael-pro-plugin" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
					'condition'       => array(
						'layout'      => 'grid',
						'show_filter' => 'yes',
					),
				)
			);
		}

			$this->add_control(
				'filters_all_text',
				array(
					'label'     => __( '"All" Tab Label', 'uael' ),
					'type'      => Controls_Manager::TEXT,
					'default'   => __( 'All', 'uael' ),
					'dynamic'   => array(
						'active' => true,
					),
					'condition' => array(
						'layout'      => 'grid',
						'show_filter' => 'yes',
					),
				)
			);

			$this->add_control(
				'default_filter_switch',
				array(
					'label'        => __( 'Default Tab on Page Load', 'uael' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => '',
					'label_off'    => __( 'First', 'uael' ),
					'label_on'     => __( 'Custom', 'uael' ),
					'condition'    => array(
						'layout'      => 'grid',
						'show_filter' => 'yes',
					),
				)
			);
			$this->add_control(
				'default_filter',
				array(
					'label'     => __( 'Enter Category Name', 'uael' ),
					'type'      => Controls_Manager::TEXT,
					'default'   => '',
					'condition' => array(
						'default_filter_switch' => 'yes',
						'layout'                => 'grid',
						'show_filter'           => 'yes',
					),
				)
			);

		if ( parent::is_internal_links() ) {

			$this->add_control(
				'default_filter_doc',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s admin link */
					'raw'             => sprintf( __( 'Note: Enter the category name that you wish to set as a default on page load. Read %1$s this article %2$s for more information.', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/how-to-display-specific-video-category-tab-as-a-default-on-page-load/?utm_source=uael-pro-dashboard&utm_medium=uael-editor-screen&utm_campaign=uael-pro-plugin" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
					'condition'       => array(
						'default_filter_switch' => 'yes',
						'layout'                => 'grid',
						'show_filter'           => 'yes',
					),
				)
			);
		}

		$this->add_control(
			'show_filter_title',
			array(
				'label'        => __( 'Title for Filterable Tab', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'return_value' => 'yes',
				'label_off'    => __( 'No', 'uael' ),
				'label_on'     => __( 'Yes', 'uael' ),
				'condition'    => array(
					'layout'      => 'grid',
					'show_filter' => 'yes',
				),
			)
		);

		$this->add_control(
			'filters_heading_text',
			array(
				'label'     => __( 'Title Text', 'uael' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'My Videos', 'uael' ),
				'dynamic'   => array(
					'active' => true,
				),
				'condition' => array(
					'layout'            => 'grid',
					'show_filter'       => 'yes',
					'show_filter_title' => 'yes',
				),
			)
		);

		$this->add_control(
			'tabs_dropdown',
			array(
				'label'        => __( 'Responsive Support', 'uael' ),
				'description'  => __( 'Enable this option to display Filterable Tabs in a Dropdown on Mobile.', 'uael' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'no',
				'label_on'     => __( 'Yes', 'uael' ),
				'label_off'    => __( 'No', 'uael' ),
				'return_value' => 'yes',
				'condition'    => array(
					'layout'      => 'grid',
					'show_filter' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Slider Controls.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function register_carousel_controls() {

		$this->start_controls_section(
			'section_slider_options',
			array(
				'label'     => __( 'Carousel', 'uael' ),
				'type'      => Controls_Manager::SECTION,
				'condition' => array(
					'layout' => 'carousel',
				),
			)
		);

			$this->add_control(
				'navigation',
				array(
					'label'   => __( 'Navigation', 'uael' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'both',
					'options' => array(
						'both'   => __( 'Arrows and Dots', 'uael' ),
						'arrows' => __( 'Arrows', 'uael' ),
						'dots'   => __( 'Dots', 'uael' ),
						'none'   => __( 'None', 'uael' ),
					),
				)
			);

			$this->add_control(
				'autoplay',
				array(
					'label'        => __( 'Autoplay', 'uael' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'no',
				)
			);

			$this->add_control(
				'autoplay_speed',
				array(
					'label'     => __( 'Autoplay Speed', 'uael' ),
					'type'      => Controls_Manager::NUMBER,
					'default'   => 5000,
					'condition' => array(
						'autoplay' => 'yes',
					),
					'selectors' => array(
						'{{WRAPPER}} .slick-slide-bg' => 'animation-duration: calc({{VALUE}}ms*1.2); transition-duration: calc({{VALUE}}ms)',
					),
				)
			);

			$this->add_control(
				'pause_on_hover',
				array(
					'label'        => __( 'Pause on Hover', 'uael' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
					'condition'    => array(
						'autoplay' => 'yes',
					),
				)
			);

			$this->add_control(
				'infinite',
				array(
					'label'        => __( 'Infinite Loop', 'uael' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'transition_speed',
				array(
					'label'       => __( 'Transition Speed (ms)', 'uael' ),
					'type'        => Controls_Manager::NUMBER,
					'label_block' => true,
					'default'     => 500,
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Helpful Information.
	 *
	 * @since 0.0.1
	 * @access protected
	 */
	protected function register_helpful_information() {

		if ( parent::is_internal_links() ) {

			$this->start_controls_section(
				'section_helpful_info',
				array(
					'label' => __( 'Helpful Information', 'uael' ),
				)
			);

			$this->add_control(
				'help_doc_0',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '%1$s Getting started article » %2$s', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/video-gallery-widget/?utm_source=uael-pro-dashboard&utm_medium=uael-editor-screen&utm_campaign=uael-pro-plugin" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->add_control(
				'help_doc_01',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '%1$s Getting started video » %2$s', 'uael' ), '<a href="https://www.youtube.com/watch?v=88kTeBv4mWY" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->add_control(
				'help_doc_1',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '%1$s Set categories for videos » %2$s', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/how-to-set-categories-for-videos/?utm_source=uael-pro-dashboard&utm_medium=uael-editor-screen&utm_campaign=uael-pro-plugin" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->add_control(
				'help_doc_2',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '%1$s Display specific video category tab as a default on page load » %2$s', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/how-to-display-specific-video-category-tab-as-a-default-on-page-load/?utm_source=uael-pro-dashboard&utm_medium=uael-editor-screen&utm_campaign=uael-pro-plugin" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->add_control(
				'help_doc_3',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '%1$s Design filterable Video Gallery » %2$s', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/how-to-design-filterable-video-gallery/?utm_source=uael-pro-dashboard&utm_medium=uael-editor-screen&utm_campaign=uael-pro-plugin" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->add_control(
				'help_doc_4',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '%1$s Set a custom placeholder image for the video » %2$s', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/how-to-set-a-custom-placeholder-image-for-the-video/?utm_source=uael-pro-dashboard&utm_medium=uael-editor-screen&utm_campaign=uael-pro-plugin" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->add_control(
				'help_doc_5',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '%1$s Set overlay color on the video thumbnail on mouse hover » %2$s', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/how-to-set-overlay-color-on-the-video-thumbnail-on-mouse-hover/?utm_source=uael-pro-dashboard&utm_medium=uael-editor-screen&utm_campaign=uael-pro-plugin" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->add_control(
				'help_doc_6',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '%1$s Show video caption on hover » %2$s', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/how-to-show-video-caption-on-hover/?utm_source=uael-pro-dashboard&utm_medium=uael-editor-screen&utm_campaign=uael-pro-plugin" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->add_control(
				'help_doc_7',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					/* translators: %1$s doc link */
					'raw'             => sprintf( __( '%1$s Get Wistia Link and Thumbnail text » %2$s', 'uael' ), '<a href=' . UAEL_DOMAIN . 'docs/video-gallery-widget/#for-wistia-video-" target="_blank" rel="noopener">', '</a>' ),
					'content_classes' => 'uael-editor-doc',
				)
			);

			$this->end_controls_section();
		}
	}

	/**
	 * Style Tab
	 */
	/**
	 * Register Layout Controls.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function register_style_layout_controls() {

		$this->start_controls_section(
			'section_design_layout',
			array(
				'label' => __( 'Spacing', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_responsive_control(
				'column_gap',
				array(
					'label'              => __( 'Columns Gap', 'uael' ),
					'type'               => Controls_Manager::SLIDER,
					'default'            => array(
						'size' => 10,
					),
					'range'              => array(
						'px' => array(
							'min' => 0,
							'max' => 50,
						),
					),
					'selectors'          => array(
						'{{WRAPPER}} .uael-video__gallery-item' => 'padding-right: calc( {{SIZE}}{{UNIT}}/2 ); padding-left: calc( {{SIZE}}{{UNIT}}/2 );',
						'{{WRAPPER}} .uael-video-gallery-wrap' => 'margin-left: calc( -{{SIZE}}{{UNIT}}/2 ); margin-right: calc( -{{SIZE}}{{UNIT}}/2 );',
						'{{WRAPPER}} .uael-vg__overlay' => 'width: calc(100% - {{SIZE}}{{UNIT}}); left: calc({{SIZE}}{{UNIT}}/2);',
					),
					'frontend_available' => true,
				)
			);

			$this->add_responsive_control(
				'row_gap',
				array(
					'label'              => __( 'Rows Gap', 'uael' ),
					'type'               => Controls_Manager::SLIDER,
					'default'            => array(
						'size' => 10,
					),
					'range'              => array(
						'px' => array(
							'min' => 0,
							'max' => 50,
						),
					),
					'condition'          => array(
						'layout' => 'grid',
					),
					'selectors'          => array(
						'{{WRAPPER}} .uael-video__gallery-item' => 'padding-bottom: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .uael-vg__overlay' => 'height: calc( 100% - {{SIZE}}{{UNIT}} );',
					),
					'frontend_available' => true,
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register Category Filters Controls.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function register_style_title_filter_controls() {
		$this->start_controls_section(
			'section_style_title_filters',
			array(
				'label'     => __( 'Title', 'uael' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_filter'       => 'yes',
					'layout'            => 'grid',
					'show_filter_title' => 'yes',
				),
			)
		);

			$this->add_control(
				'filter_title_tag',
				array(
					'label'     => __( 'HTML Tag', 'uael' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => array(
						'h1'  => __( 'H1', 'uael' ),
						'h2'  => __( 'H2', 'uael' ),
						'h3'  => __( 'H3', 'uael' ),
						'h4'  => __( 'H4', 'uael' ),
						'h5'  => __( 'H5', 'uael' ),
						'h6'  => __( 'H6', 'uael' ),
						'div' => __( 'div', 'uael' ),
						'p'   => __( 'p', 'uael' ),
					),
					'default'   => 'h3',
					'condition' => array(
						'layout'            => 'grid',
						'show_filter'       => 'yes',
						'show_filter_title' => 'yes',
					),
				)
			);

			$this->add_control(
				'filter_title_color',
				array(
					'label'     => __( 'Title Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array(
						'default' => Global_Colors::COLOR_PRIMARY,
					),
					'selectors' => array(
						'{{WRAPPER}} .uael-video-gallery-title-text' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'layout'            => 'grid',
						'show_filter'       => 'yes',
						'show_filter_title' => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'filter_title_typography',
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'selector'  => '{{WRAPPER}} .uael-video-gallery-title-text',
					'condition' => array(
						'layout'            => 'grid',
						'show_filter'       => 'yes',
						'show_filter_title' => 'yes',
					),
				)
			);

			$this->add_control(
				'filters_tab_heading_stack',
				array(
					'label'        => __( 'Stack On', 'uael' ),
					'description'  => __( 'Choose at what breakpoint the Title & Filter Tabs will stack.', 'uael' ),
					'type'         => Controls_Manager::SELECT,
					'default'      => 'mobile',
					'options'      => array(
						'none'   => __( 'None', 'uael' ),
						'tablet' => __( 'Tablet (1023px >)', 'uael' ),
						'mobile' => __( 'Mobile (767px >)', 'uael' ),
					),
					'condition'    => array(
						'layout'            => 'grid',
						'show_filter'       => 'yes',
						'show_filter_title' => 'yes',
					),
					'prefix_class' => 'uael-video-gallery-stack-',
				)
			);
		$this->end_controls_section();
	}

	/**
	 * Register Category Filters Controls.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function register_style_filter_controls() {

		$this->start_controls_section(
			'section_style_cat_filters',
			array(
				'label'     => __( 'Filterable Tabs', 'uael' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_filter' => 'yes',
					'layout'      => 'grid',
				),
			)
		);

			$this->add_responsive_control(
				'cat_filter_align',
				array(
					'label'              => __( 'Tab Alignment', 'uael' ),
					'type'               => Controls_Manager::CHOOSE,
					'options'            => array(
						'left'   => array(
							'title' => __( 'Left', 'uael' ),
							'icon'  => 'fa fa-align-left',
						),
						'center' => array(
							'title' => __( 'Center', 'uael' ),
							'icon'  => 'fa fa-align-center',
						),
						'right'  => array(
							'title' => __( 'Right', 'uael' ),
							'icon'  => 'fa fa-align-right',
						),
					),
					'default'            => 'center',
					'toggle'             => false,
					'render_type'        => 'template',
					'prefix_class'       => 'uael%s-vgallery-filter-align-',
					'selectors'          => array(
						'{{WRAPPER}} .uael-video__gallery-filters' => 'text-align: {{VALUE}};',
						'(mobile){{WRAPPER}} .uael-vgallery-tabs-dropdown .uael-filters-dropdown' => 'text-align: {{VALUE}};',
					),
					'condition'          => array(
						'show_filter_title!' => 'yes',
					),
					'frontend_available' => true,
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'all_typography',
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'selector' => '{{WRAPPER}} .uael-video__gallery-filter,{{WRAPPER}} .uael-vgallery-tabs-dropdown .uael-filters-dropdown-button',
				)
			);
			$this->add_responsive_control(
				'cat_filter_padding',
				array(
					'label'              => __( 'Padding', 'uael' ),
					'type'               => Controls_Manager::DIMENSIONS,
					'size_units'         => array( 'px', 'em', '%' ),
					'selectors'          => array(
						'{{WRAPPER}} .uael-video__gallery-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'frontend_available' => true,
				)
			);

			$this->add_responsive_control(
				'cat_filter_bet_spacing',
				array(
					'label'              => __( 'Spacing Between Tabs', 'uael' ),
					'type'               => Controls_Manager::SLIDER,
					'range'              => array(
						'px' => array(
							'max' => 100,
						),
					),
					'selectors'          => array(
						'{{WRAPPER}} .uael-video__gallery-filter' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
						'(mobile){{WRAPPER}} .uael-vgallery-tabs-dropdown .uael-video__gallery-filter' => 'margin-left: 0px; margin-right: 0px;',
					),
					'frontend_available' => true,
				)
			);
			$this->add_responsive_control(
				'cat_filter_spacing',
				array(
					'label'              => __( 'Tabs Bottom Spacing', 'uael' ),
					'type'               => Controls_Manager::SLIDER,
					'range'              => array(
						'px' => array(
							'max' => 100,
						),
					),
					'selectors'          => array(
						'{{WRAPPER}} .uael-video__gallery-filters' => 'margin-bottom: {{SIZE}}{{UNIT}};',
						'(mobile){{WRAPPER}} .uael-vgallery-tabs-dropdown .uael-filters-dropdown' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					),
					'separator'          => 'after',
					'frontend_available' => true,
				)
			);

			$this->start_controls_tabs( 'cat_filters_tabs_style' );

			$this->start_controls_tab(
				'cat_filters_normal',
				array(
					'label' => __( 'Normal', 'uael' ),
				)
			);

				$this->add_control(
					'cat_filter_color',
					array(
						'label'     => __( 'Text Color', 'uael' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => array(
							'default' => Global_Colors::COLOR_ACCENT,
						),
						'selectors' => array(
							'{{WRAPPER}} .uael-vgallery-tabs-dropdown .uael-filters-dropdown-button, {{WRAPPER}} .uael-video__gallery-filter' => 'color: {{VALUE}};',
						),
					)
				);

				$this->add_control(
					'cat_filter_bg_color',
					array(
						'label'     => __( 'Background Color', 'uael' ),
						'type'      => Controls_Manager::COLOR,
						'selectors' => array(
							'{{WRAPPER}} .uael-vgallery-tabs-dropdown .uael-filters-dropdown-button,{{WRAPPER}} .uael-video__gallery-filter' => 'background-color: {{VALUE}};',
						),
					)
				);

				$this->add_group_control(
					Group_Control_Border::get_type(),
					array(
						'name'     => 'cat_filter_border',
						'label'    => __( 'Border', 'uael' ),
						'selector' => '{{WRAPPER}} .uael-video__gallery-filter,{{WRAPPER}} .uael-vgallery-tabs-dropdown .uael-filters-dropdown-button',
					)
				);

			$this->end_controls_tab();

			$this->start_controls_tab(
				'cat_filters_hover',
				array(
					'label' => __( 'Hover', 'uael' ),
				)
			);

				$this->add_control(
					'cat_filter_hover_color',
					array(
						'label'     => __( 'Text Active / Hover Color', 'uael' ),
						'type'      => Controls_Manager::COLOR,
						'default'   => '#ffffff',
						'selectors' => array(
							'{{WRAPPER}} .uael-video__gallery-filter:hover, {{WRAPPER}} .uael-video__gallery-filter.uael-filter__current' => 'color: {{VALUE}};',
						),
					)
				);

				$this->add_control(
					'cat_filter_bg_hover_color',
					array(
						'label'     => __( 'Background Active / Hover Color', 'uael' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => array(
							'default' => Global_Colors::COLOR_ACCENT,
						),
						'selectors' => array(
							'{{WRAPPER}} .uael-video__gallery-filter:hover, {{WRAPPER}} .uael-video__gallery-filter.uael-filter__current' => 'background-color: {{VALUE}};',
						),
					)
				);

				$this->add_control(
					'cat_filter_border_hover_color',
					array(
						'label'     => __( 'Border Hover Color', 'uael' ),
						'type'      => Controls_Manager::COLOR,
						'global'    => array(
							'default' => Global_Colors::COLOR_ACCENT,
						),
						'selectors' => array(
							'{{WRAPPER}} .uael-video__gallery-filter:hover, {{WRAPPER}} .uael-video__gallery-filter.uael-filter__current' => 'border-color: {{VALUE}};',
						),
						'condition' => array(
							'cat_filter_border_border!' => '',
						),
					)
				);

			$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'cat_hover_effects_heading',
			array(
				'label'     => __( 'Hover Effects', 'uael' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'show_filter' => 'yes',
					'layout'      => 'grid',
				),
			)
		);

		$this->add_control(
			'cat_hover_effect',
			array(
				'label'     => __( 'Effect', 'uael' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'none'        => __( 'None', 'uael' ),
					'underline'   => __( 'Underline', 'uael' ),
					'overline'    => __( 'Overline', 'uael' ),
					'framed'      => __( 'Framed', 'uael' ),
					'double_line' => __( 'Double Line', 'uael' ),
				),
				'default'   => 'none',
				'condition' => array(
					'show_filter' => 'yes',
					'layout'      => 'grid',
				),
			)
		);

		$this->add_responsive_control(
			'cat_hover_effect_thickness',
			array(
				'label'     => __( 'Thickness', 'uael' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'default'   => array(
					'size' => 2,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-video__gallery-filter:after,
					 {{WRAPPER}} .uael-video__gallery-filter:hover:after,
					 {{WRAPPER}} .uael-video__gallery-filter.uael-filter__current:after' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"]:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"]:hover:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"].uael-filter__current:after' => 'border-width: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'show_filter'       => 'yes',
					'layout'            => 'grid',
					'cat_hover_effect!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cat_hover_effect_offset',
			array(
				'label'     => __( 'Offset', 'uael' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 8,
					),
				),
				'default'   => array(
					'size' => 0,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="underline"]:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="underline"]:hover:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="underline"].uael-filter__current:after' => 'bottom: calc(-{{SIZE}}{{UNIT}} - 2px);',
					'{{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="overline"]:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="overline"]:hover:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="overline"].uael-filter__current:after' => 'top: calc(-{{SIZE}}{{UNIT}} - 2px);',
					'{{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"]:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"]:hover:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"].uael-filter__current:after' => 'top: -{{SIZE}}{{UNIT}}; right: -{{SIZE}}{{UNIT}}; bottom: -{{SIZE}}{{UNIT}}; left: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="double_line"]:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="double_line"]:hover:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="double_line"].uael-filter__current:after' => 'bottom: calc(-{{SIZE}}{{UNIT}} - 2px);',
					'{{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="double_line"]:before,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="double_line"]:hover:before,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="double_line"].uael-filter__current:before' => 'top: calc(-{{SIZE}}{{UNIT}} - 2px);',
				),
				'condition' => array(
					'show_filter'       => 'yes',
					'layout'            => 'grid',
					'cat_hover_effect!' => 'none',
				),
			)
		);

		$this->add_responsive_control(
			'cat_hover_effect_corner_radius',
			array(
				'label'      => __( 'Corner Radius', 'uael' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"]:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"]:hover:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"].uael-filter__current:after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'show_filter'      => 'yes',
					'layout'           => 'grid',
					'cat_hover_effect' => 'framed',
				),
			)
		);

		$this->add_control(
			'cat_hover_effect_transition',
			array(
				'label'     => __( 'Transition Duration (ms)', 'uael' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 2000,
					),
				),
				'default'   => array(
					'size' => 300,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-video__gallery-filter:after,
					 {{WRAPPER}} .uael-video__gallery-filter:before' => 'transition: all {{SIZE}}ms ease;',
				),
				'condition' => array(
					'show_filter'       => 'yes',
					'layout'            => 'grid',
					'cat_hover_effect!' => 'none',
				),
			)
		);

		$this->add_control(
			'cat_hover_effect_color',
			array(
				'label'     => __( 'Effect Color', 'uael' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FF6B6B',
				'global'    => array(
					'default' => Global_Colors::COLOR_ACCENT,
				),
				'selectors' => array(
					'{{WRAPPER}} .uael-video__gallery-filter:after,
					 {{WRAPPER}} .uael-video__gallery-filter:before,
					 {{WRAPPER}} .uael-video__gallery-filter:hover:after,
					 {{WRAPPER}} .uael-video__gallery-filter:hover:before,
					 {{WRAPPER}} .uael-video__gallery-filter.uael-filter__current:after,
					 {{WRAPPER}} .uael-video__gallery-filter.uael-filter__current:before' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"]:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"]:hover:after,
					 {{WRAPPER}} .uael-video__gallery-filter[data-hover-effect="framed"].uael-filter__current:after' => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'cat_hover_effect!' => 'none',
				),
			)
		);


		$this->end_controls_section();
	}

	/**
	 * Register Video Effects Controls.
	 *
	 * @since 1.40.2
	 * @access protected
	 */
	protected function register_style_video_effects_controls() {

		$this->start_controls_section(
			'section_video_effects',
			array(
				'label' => __( 'Video Effects', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'video_effect',
				array(
					'label'   => __( 'Effect', 'uael' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'normal',
					'options' => $this->video_filter_options(),
				)
			);

			$this->add_control(
				'video_effect_apply_to',
				array(
					'label'     => __( 'Apply To', 'uael' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'thumbnail',
					'options'   => array(
						'thumbnail' => __( 'Thumbnail', 'uael' ),
						'on_play'   => __( 'On Play', 'uael' ),
						'both'      => __( 'Both', 'uael' ),
					),
					'condition' => array(
						'video_effect!' => 'normal',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register Play Button Controls.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function register_style_play_controls() {

		$this->start_controls_section(
			'section_design_play',
			array(
				'label' => __( 'Play Button', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'play_source',
				array(
					'label'   => __( 'Image/Icon', 'uael' ),
					'type'    => Controls_Manager::CHOOSE,
					'options' => array(
						'img'  => array(
							'title' => __( 'Image', 'uael' ),
							'icon'  => 'fa fa-picture-o',
						),
						'icon' => array(
							'title' => __( 'Icon', 'uael' ),
							'icon'  => 'fa fa-info-circle',
						),
					),
					'default' => 'icon',
				)
			);

			$this->add_control(
				'play_img',
				array(
					'label'     => __( 'Select Image', 'uael' ),
					'type'      => Controls_Manager::MEDIA,
					'default'   => array(
						'url' => Utils::get_placeholder_image_src(),
					),
					'condition' => array(
						'play_source' => 'img',
					),
				)
			);

		if ( UAEL_Helper::is_elementor_updated() ) {

			$this->add_control(
				'new_play_icon',
				array(
					'label'            => __( 'Select Icon', 'uael' ),
					'type'             => Controls_Manager::ICONS,
					'default'          => array(
						'value'   => 'fa fa-play',
						'library' => 'fa-solid',
					),
					'fa4compatibility' => 'play_icon',
					'condition'        => array(
						'play_source' => 'icon',
					),
				)
			);
		} else {

			$this->add_control(
				'play_icon',
				array(
					'label'     => __( 'Select Icon', 'uael' ),
					'type'      => Controls_Manager::ICON,
					'default'   => 'fa fa-play',
					'condition' => array(
						'play_source' => 'icon',
					),
				)
			);
		}

			$this->add_responsive_control(
				'play_icon_size',
				array(
					'label'              => __( 'Size', 'uael' ),
					'type'               => Controls_Manager::SLIDER,
					'default'            => array(
						'size' => 60,
					),
					'tablet_default'     => array(
						'size' => 45,
					),
					'mobile_default'     => array(
						'size' => 35,
					),
					'range'              => array(
						'size' => 30,
					),
					'range'              => array(
						'px' => array(
							'max' => 100,
						),
					),
					'selectors'          => array(
						'{{WRAPPER}} .uael-video__content i, {{WRAPPER}} .uael-video__content svg, {{WRAPPER}} .uael-video__content .uael-vg__play' => 'font-size: {{SIZE}}px;line-height: {{SIZE}}px;height: {{SIZE}}px;width: {{SIZE}}px;',
						'{{WRAPPER}} .uael-video__content img.uael-vg__play-image, {{WRAPPER}} .uael-video__content .uael-vg__play' => 'width: {{SIZE}}px;',
						'{{WRAPPER}} .uael-vg__play .uael-vg__play-icon i' => 'font-size: {{SIZE}}px;line-height: {{SIZE}}px;height: {{SIZE}}px;width: {{SIZE}}px;',
						'{{WRAPPER}} .uael-vg__play img.uael-vg__play-image' => 'width: {{SIZE}}px;',
						'{{WRAPPER}} .uael-vg__play' => 'width: {{SIZE}}px;',
					),
					'frontend_available' => true,
				)
			);

			$this->add_control(
				'hover_animation_img',
				array(
					'label'     => __( 'Hover Animation', 'uael' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => '',
					'options'   => array(
						''                => __( 'None', 'uael' ),
						'grow'            => __( 'Grow', 'uael' ),
						'shrink'          => __( 'Shrink', 'uael' ),
						'pulse'           => __( 'Pulse', 'uael' ),
						'pulse-grow'      => __( 'Pulse Grow', 'uael' ),
						'pulse-shrink'    => __( 'Pulse Shrink', 'uael' ),
						'push'            => __( 'Push', 'uael' ),
						'pop'             => __( 'Pop', 'uael' ),
						'float'           => __( 'Float', 'uael' ),
						'sink'            => __( 'Sink', 'uael' ),
						'wobble-vertical' => __( 'Wobble Vertical', 'uael' ),
					),
					'condition' => array(
						'play_source' => 'img',
					),
				)
			);

			$this->start_controls_tabs( 'tabs_style' );

				$this->start_controls_tab(
					'tab_normal',
					array(
						'label'      => __( 'Normal', 'uael' ),
						'conditions' => array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => UAEL_Helper::get_new_icon_name( 'play_icon' ),
									'operator' => '!=',
									'value'    => '',
								),
								array(
									'name'     => 'play_source',
									'operator' => '==',
									'value'    => 'icon',
								),
							),
						),
					)
				);

					$this->add_control(
						'play_icon_color',
						array(
							'label'      => __( 'Color', 'uael' ),
							'type'       => Controls_Manager::COLOR,
							'selectors'  => array(
								'{{WRAPPER}} .uael-video__content i' => 'color: {{VALUE}};',
								'{{WRAPPER}} .uael-video__content svg, .uael-vg__play .uael-vg__play-icon svg' => 'fill: {{VALUE}};',
								'{{WRAPPER}} .uael-vg__play .uael-vg__play-icon i'   => 'color: {{VALUE}};',
							),
							'default'    => '#ffffff',
							'conditions' => array(
								'relation' => 'and',
								'terms'    => array(
									array(
										'name'     => UAEL_Helper::get_new_icon_name( 'play_icon' ),
										'operator' => '!=',
										'value'    => '',
									),
									array(
										'name'     => 'play_source',
										'operator' => '==',
										'value'    => 'icon',
									),
								),
							),
						)
					);

					$this->add_group_control(
						Group_Control_Text_Shadow::get_type(),
						array(
							'name'       => 'play_icon_text_shadow',
							'label'      => __( 'Icon Shadow', 'uael' ),
							'selector'   => '{{WRAPPER}} .uael-video__content i, {{WRAPPER}} .uael-video__content svg, {{WRAPPER}} .uael-vg__play .uael-vg__play-icon i, {{WRAPPER}} .uael-vg__play .uael-vg__play-icon svg',
							'conditions' => array(
								'relation' => 'and',
								'terms'    => array(
									array(
										'name'     => UAEL_Helper::get_new_icon_name( 'play_icon' ),
										'operator' => '!=',
										'value'    => '',
									),
									array(
										'name'     => 'play_source',
										'operator' => '==',
										'value'    => 'icon',
									),
								),
							),
						)
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_hover',
					array(
						'label'      => __( 'Hover', 'uael' ),
						'conditions' => array(
							'relation' => 'and',
							'terms'    => array(
								array(
									'name'     => UAEL_Helper::get_new_icon_name( 'play_icon' ),
									'operator' => '!=',
									'value'    => '',
								),
								array(
									'name'     => 'play_source',
									'operator' => '==',
									'value'    => 'icon',
								),
							),
						),
					)
				);

					$this->add_control(
						'play_icon_hover_color',
						array(
							'label'      => __( 'Color', 'uael' ),
							'type'       => Controls_Manager::COLOR,
							'selectors'  => array(
								'{{WRAPPER}} .uael-video__gallery-item:hover .uael-vg__play .uael-vg__play-icon i, {{WRAPPER}} .uael-video__gallery-item:hover .uael-video__content i' => 'color: {{VALUE}}',
								'{{WRAPPER}} .uael-video__gallery-item:hover .uael-vg__play .uael-vg__play-icon svg' => 'fill: {{VALUE}}',
							),
							'conditions' => array(
								'relation' => 'and',
								'terms'    => array(
									array(
										'name'     => UAEL_Helper::get_new_icon_name( 'play_icon' ),
										'operator' => '!=',
										'value'    => '',
									),
									array(
										'name'     => 'play_source',
										'operator' => '==',
										'value'    => 'icon',
									),
								),
							),
						)
					);

					$this->add_group_control(
						Group_Control_Text_Shadow::get_type(),
						array(
							'name'       => 'play_icon_hover_text_shadow',
							'label'      => __( 'Icon Shadow', 'uael' ),
							'selector'   => '{{WRAPPER}} .uael-video__gallery-item:hover .uael-vg__play .uael-vg__play-icon i,
							{{WRAPPER}} .uael-video__gallery-item:hover .uael-vg__play .uael-vg__play-icon svg,
							{{WRAPPER}} .uael-video__gallery-item:hover .uael-video__content i',
							'conditions' => array(
								'relation' => 'and',
								'terms'    => array(
									array(
										'name'     => UAEL_Helper::get_new_icon_name( 'play_icon' ),
										'operator' => '!=',
										'value'    => '',
									),
									array(
										'name'     => 'play_source',
										'operator' => '==',
										'value'    => 'icon',
									),
								),
							),
						)
					);

					$this->add_control(
						'hover_animation',
						array(
							'label'      => __( 'Hover Animation', 'uael' ),
							'type'       => Controls_Manager::SELECT,
							'default'    => '',
							'options'    => array(
								''                => __( 'None', 'uael' ),
								'grow'            => __( 'Grow', 'uael' ),
								'shrink'          => __( 'Shrink', 'uael' ),
								'pulse'           => __( 'Pulse', 'uael' ),
								'pulse-grow'      => __( 'Pulse Grow', 'uael' ),
								'pulse-shrink'    => __( 'Pulse Shrink', 'uael' ),
								'push'            => __( 'Push', 'uael' ),
								'pop'             => __( 'Pop', 'uael' ),
								'float'           => __( 'Float', 'uael' ),
								'sink'            => __( 'Sink', 'uael' ),
								'wobble-vertical' => __( 'Wobble Vertical', 'uael' ),
							),
							'conditions' => array(
								'relation' => 'and',
								'terms'    => array(
									array(
										'name'     => UAEL_Helper::get_new_icon_name( 'play_icon' ),
										'operator' => '!=',
										'value'    => '',
									),
									array(
										'name'     => 'play_source',
										'operator' => '==',
										'value'    => 'icon',
									),
								),
							),
						)
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Register Caption Controls.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function register_style_caption_controls() {

		$this->start_controls_section(
			'section_design_caption',
			array(
				'label' => __( 'Content', 'uael' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'overlay_background_color',
				array(
					'label'     => __( 'Overlay Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .uael-vg__overlay' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'overlay_background_hover_color',
				array(
					'label'     => __( 'Overlay Hover Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .uael-video__gallery-item:hover .uael-vg__overlay' => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'caption_typo',
				array(
					'label'     => __( 'Caption', 'uael' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'show_caption',
				array(
					'label'        => __( 'Show Caption', 'uael' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'no',
					'return_value' => 'yes',
					'label_off'    => __( 'No', 'uael' ),
					'label_on'     => __( 'Yes', 'uael' ),
				)
			);

			$this->add_control(
				'video_caption',
				array(
					'label'        => __( 'Action', 'uael' ),
					'type'         => Controls_Manager::SELECT,
					'options'      => array(
						'hover'       => __( 'Hover', 'uael' ),
						'always'      => __( 'Always', 'uael' ),
						'below_video' => __( 'Below Video', 'uael' ),
					),
					'default'      => 'hover',
					'prefix_class' => 'uael-video-gallery-title-',
					'condition'    => array(
						'show_caption' => 'yes',
					),
					'render_type'  => 'template',
				)
			);

			$this->add_control(
				'caption_color',
				array(
					'label'     => __( 'Caption Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => array(
						'{{WRAPPER}} .uael-video__caption' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'show_caption' => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'caption_typography',
					'selector'  => '{{WRAPPER}} .uael-video__caption',
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'condition' => array(
						'show_caption' => 'yes',
					),
				)
			);

			$this->add_control(
				'tag_typo',
				array(
					'label'     => __( 'Category', 'uael' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$this->add_control(
				'show_tag',
				array(
					'label'        => __( 'Show Category on Hover', 'uael' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => 'no',
					'return_value' => 'yes',
					'label_off'    => __( 'No', 'uael' ),
					'label_on'     => __( 'Yes', 'uael' ),
				)
			);

			$this->add_control(
				'tag_color',
				array(
					'label'     => __( 'Category Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#ffffff',
					'selectors' => array(
						'{{WRAPPER}} .uael-video__tags' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'show_tag' => 'yes',
					),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'tag_typography',
					'selector'  => '{{WRAPPER}} .uael-video__tags',
					'global'    => array(
						'default' => Global_Typography::TYPOGRAPHY_ACCENT,
					),
					'condition' => array(
						'show_tag' => 'yes',
					),
				)
			);

		$this->end_controls_section();
	}

	/**
	 * Register Style Navigation Controls.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	public function register_style_navigation_controls() {

		$this->start_controls_section(
			'section_style_navigation',
			array(
				'label'     => __( 'Navigation', 'uael' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'navigation' => array( 'arrows', 'dots', 'both' ),
					'layout'     => 'carousel',
				),
			)
		);

			$this->add_control(
				'heading_style_arrows',
				array(
					'label'     => __( 'Arrows', 'uael' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						'navigation' => array( 'arrows', 'both' ),
					),
				)
			);

			$this->add_control(
				'arrows_size',
				array(
					'label'     => __( 'Arrows Size', 'uael' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min' => 20,
							'max' => 60,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .slick-slider .slick-prev i, {{WRAPPER}} .slick-slider .slick-next i' => 'font-size: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'navigation' => array( 'arrows', 'both' ),
					),
				)
			);

			$this->add_control(
				'arrows_color',
				array(
					'label'     => __( 'Arrows Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .slick-slider .slick-prev:before, {{WRAPPER}} .slick-slider .slick-next:before' => 'color: {{VALUE}};',
						'{{WRAPPER}} .slick-slider .slick-arrow' => 'border-color: {{VALUE}}; border-style: solid;',
						'{{WRAPPER}} .slick-slider .slick-arrow i' => 'color: {{VALUE}};',
					),
					'global'    => array(
						'default' => Global_Colors::COLOR_ACCENT,
					),
					'condition' => array(
						'navigation' => array( 'arrows', 'both' ),
					),
				)
			);

			$this->add_control(
				'arrows_border_size',
				array(
					'label'     => __( 'Arrows Border Size', 'uael' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min' => 1,
							'max' => 10,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .slick-slider .slick-arrow' => 'border-width: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'navigation' => array( 'arrows', 'both' ),
					),
				)
			);

			$this->add_control(
				'arrow_border_radius',
				array(
					'label'      => __( 'Border Radius', 'uael' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( '%' ),
					'default'    => array(
						'top'    => '50',
						'bottom' => '50',
						'left'   => '50',
						'right'  => '50',
						'unit'   => '%',
					),
					'selectors'  => array(
						'{{WRAPPER}} .slick-slider .slick-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'navigation' => array( 'arrows', 'both' ),
					),
				)
			);

			$this->add_control(
				'heading_style_dots',
				array(
					'label'     => __( 'Dots', 'uael' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						'navigation' => array( 'dots', 'both' ),
					),
				)
			);

			$this->add_control(
				'dots_size',
				array(
					'label'     => __( 'Dots Size', 'uael' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min' => 5,
							'max' => 15,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} .slick-dots li button:before' => 'font-size: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'navigation' => array( 'dots', 'both' ),
					),
				)
			);

			$this->add_control(
				'dots_color',
				array(
					'label'     => __( 'Dots Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} .slick-dots li button:before' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'navigation' => array( 'dots', 'both' ),
					),
				)
			);

		$this->end_controls_section();
	}



	/**
	 * Get Wrapper Classes.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	public function get_slider_attr() {

		$settings = $this->get_settings_for_display();

		if ( 'carousel' !== $settings['layout'] ) {
			return;
		}

		$is_rtl      = is_rtl();
		$direction   = $is_rtl ? 'rtl' : 'ltr';
		$show_dots   = ( in_array( $settings['navigation'], array( 'dots', 'both' ), true ) );
		$show_arrows = ( in_array( $settings['navigation'], array( 'arrows', 'both' ), true ) );

		$slick_options = array(
			'slidesToShow'   => ( $settings['gallery_columns'] ) ? absint( $settings['gallery_columns'] ) : 4,
			'slidesToScroll' => 1,
			'autoplaySpeed'  => isset( $settings['autoplay_speed'] ) ? absint( $settings['autoplay_speed'] ) : 5000,
			'autoplay'       => ( 'yes' === $settings['autoplay'] ),
			'infinite'       => ( 'yes' === $settings['infinite'] ),
			'pauseOnHover'   => ( 'yes' === $settings['pause_on_hover'] ),
			'speed'          => ( $settings['transition_speed'] ) ? absint( $settings['transition_speed'] ) : 500,
			'arrows'         => $show_arrows,
			'dots'           => $show_dots,
			'rtl'            => $is_rtl,
			'prevArrow'      => '<button type="button" data-role="none" class="slick-prev" aria-label="Previous" tabindex="0" role="button"><i class="fa fa-angle-left"></i></button>',
			'nextArrow'      => '<button type="button" data-role="none" class="slick-next" aria-label="Next" tabindex="0" role="button"><i class="fa fa-angle-right"></i></button>',
		);

		if ( $settings['gallery_columns_tablet'] || $settings['gallery_columns_mobile'] ) {

			$slick_options['responsive'] = array();

			if ( $settings['gallery_columns_tablet'] ) {

				$tablet_show   = absint( $settings['gallery_columns_tablet'] );
				$tablet_scroll = $tablet_show;

				$slick_options['responsive'][] = array(
					'breakpoint' => 1024,
					'settings'   => array(
						'slidesToShow'   => $tablet_show,
						'slidesToScroll' => $tablet_scroll,
					),
				);
			}

			if ( $settings['gallery_columns_mobile'] ) {

				$mobile_show   = absint( $settings['gallery_columns_mobile'] );
				$mobile_scroll = $mobile_show;

				$slick_options['responsive'][] = array(
					'breakpoint' => 767,
					'settings'   => array(
						'slidesToShow'   => $mobile_show,
						'slidesToScroll' => $mobile_scroll,
					),
				);
			}
		}
		$slick_param = apply_filters( 'uael_video_gallery_slick_options', $slick_options );

		$this->add_render_attribute(
			'uael-vg-slider',
			array(
				'data-vg_slider' => wp_json_encode( $slick_param ),
			)
		);

		return $this->get_render_attribute_string( 'uael-vg-slider' );
	}


	/**
	 * Get masonry script.
	 *
	 * Returns the post masonry script.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	public function render_masonry_script() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {

				$( '.uael-video-gallery-wrap' ).each( function() {

					var $node_id 	= '<?php echo esc_attr( $this->get_id() ); ?>';
					var	scope 		= $( '[data-id="' + $node_id + '"]' );
					var selector 	= $(this);

					if ( selector.closest( scope ).length < 1 ) {
						return;
					}

					if ( ! selector.hasClass( 'uael-video-gallery-filter' ) ) {
						return;
					}

					var filters = scope.find( '.uael-video__gallery-filters' );
					var def_cat = '*';

					if ( filters.length > 0 ) {

						var def_filter = filters.data( 'default' );

						if ( '' !== def_filter ) {

							def_cat 	= def_filter;
							def_cat_sel = filters.find( '[data-filter="' + def_filter + '"]' );

							if ( def_cat_sel.length > 0 ) {
								def_cat_sel.siblings().removeClass( 'uael-filter__current' );
								def_cat_sel.addClass( 'uael-filter__current' );
							}
						}
					}

					var $obj = {};

					selector.imagesLoaded( function( e ) {

						$obj = selector.isotope({
							filter: def_cat,
							layoutMode: 'masonry',
							itemSelector: '.uael-video__gallery-item',
						});

						selector.find( '.uael-video__gallery-item' ).resize( function() {
							$obj.isotope( 'layout' );
						});
					});

				});
			});
		</script>
		<?php
	}

	/**
	 * Render Tag Classes.
	 *
	 * @param Array $item Current video array.
	 * @since 1.5.0
	 * @access public
	 */
	public function get_tag_class( $item ) {
		$tags = explode( ',', $item['tags'] );
		$tags = array_map( 'trim', $tags );
	
		$tags_array = array();
	
		foreach ( $tags as $key => $value ) {
			// Ensure Bunny.net videos are handled properly.
			if ( 'bunny' === $item['type'] && empty( $value ) ) {
				$value = 'Bunny.net'; // Default tag for Bunny.net videos.
			}
	
			$arr_value                                = 'filter-' . $value;
			$tags_array[ $this->clean( $arr_value ) ] = $value;
		}
	
		return $tags_array;
	}

	/**
	 * Render Placeholder Image HTML.
	 *
	 * @param Array $item Current video array.
	 * @since 1.5.0
	 * @access public
	 */
	public function get_placeholder_image( $item ) {

		$url         = '';
		$vid_id      = '';
		$video_url   = '';
		$rumble_data = array(); // Store rumble data to avoid multiple API calls.

		if ( 'wistia' === $item['type'] ) {
			$video_url = $item['wistia_url'];
		} elseif ( 'bunny' === $item['type'] ) {
			$video_url = $item['bunny_url'];
		} else {
			$video_url = $item['video_url'];
		}

		if ( 'youtube' === $item['type'] ) {
			if ( preg_match( '/[\\?\\&]v=([^\\?\\&]+)/', $video_url, $matches ) ) {
				$vid_id = $matches[1];
			}
		} elseif ( 'vimeo' === $item['type'] ) {
			if ( preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $video_url, $regs ) ) {
				$vid_id = $regs[3];
			}
		} elseif ( 'rumble' === $item['type'] ) {
			$rumble_data = $this->get_rumble_oembed_data( $video_url );
			$vid_id      = $rumble_data['video_id'];
		} elseif ( 'wistia' === $item['type'] ) {
			$vid_id = $this->getStringBetween( $video_url, 'wvideo=', '"' );
		} elseif ( 'bunny' === $item['type'] ) {

			if ( preg_match( '/\/embed\/([^\/]+)\/([^\/\?]+)/', $video_url, $matches ) ) {
				$library_id = $matches[1];
				$video_id   = $matches[2];

				if ( 'libraryId' !== $library_id && 'videoId' !== $video_id && is_numeric( $library_id ) ) {
					$vid_id = $library_id . '/' . $video_id;
				}
			} elseif ( preg_match( '/\/play\/([^\/]+)\/([^\/\?]+)/', $video_url, $matches ) ) {
				$library_id = $matches[1];
				$video_id   = $matches[2];

				if ( 'libraryId' !== $library_id && 'videoId' !== $video_id && is_numeric( $library_id ) ) {
					$vid_id = $library_id . '/' . $video_id;
				}
			}
			
			if ( empty( $vid_id ) ) {
				$vid_id = 'bunny-video';
			}       
		}

		if ( ( 'yes' === $item['custom_placeholder'] && 'hosted' !== $item['type'] ) || 'hosted' === $item['type'] ) {
			$url = $item['placeholder_image']['url'];
		} else {
			if ( 'youtube' === $item['type'] ) {

				$url = 'https://i.ytimg.com/vi/' . $vid_id . '/' . apply_filters( 'uael_vg_youtube_image_quality', $item['yt_thumbnail_size'] ) . '.jpg';

			} elseif ( 'vimeo' === $item['type'] ) {

				$vid_id_image = preg_replace( '/[^\/]+[^0-9]|(\/)/', '', rtrim( $video_url, '/' ) );

				if ( '' !== $vid_id_image && 0 !== $vid_id_image ) {

					$response = wp_remote_get( "https://vimeo.com/api/v2/video/$vid_id_image.php" );

					if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
						return;
					}
					$vimeo = maybe_unserialize( $response['body'] );
					// privacy enabled videos don't return thumbnail data in url.
					$url = ( isset( $vimeo[0]['thumbnail_large'] ) && ! empty( $vimeo[0]['thumbnail_large'] ) ) ? str_replace( '_640', '_840', $vimeo[0]['thumbnail_large'] ) : '';
				}
			} elseif ( 'wistia' === $item['type'] ) {
				$url = 'https://embed-ssl.wistia.com/deliveries/' . $this->getStringBetween( $video_url, 'deliveries/', '?' );
			} elseif ( 'rumble' === $item['type'] ) {
				// Use cached rumble data from above.
				if ( empty( $rumble_data ) ) {
					$rumble_data = $this->get_rumble_oembed_data( $video_url );
				}
				$url = $rumble_data['thumbnail_url'];
			} elseif ( 'bunny' === $item['type'] ) {
				$cdn_prefix   = ! empty( $item['bunny_cdn_prefix'] ) ? $item['bunny_cdn_prefix'] : '';
				$vid_id_parts = explode( '/', $vid_id );
				$vid_id       = isset( $vid_id_parts[1] ) ? $vid_id_parts[1] : $vid_id;
				$url          = 'https://' . $cdn_prefix . '.b-cdn.net/' . $vid_id . '/thumbnail.jpg';
			}
		}

		return array(
			'url'      => $url,
			'video_id' => $vid_id,
		);

	}

	/**
	 * Returns Video URL.
	 *
	 * @param string $url Video URL.
	 * @param string $from From compare string.
	 * @param string $to To compare string.
	 * @since 1.17.0
	 * @access protected
	 */
	protected function getStringBetween( $url, $from, $to ) {
		$sub = substr( $url, strpos( $url, $from ) + strlen( $from ), strlen( $url ) );
		$id  = substr( $sub, 0, strpos( $sub, $to ) );

		return $id;
	}

	/**
	 * Get Rumble data using oEmbed API.
	 *
	 * @param string $url Rumble URL.
	 * @return array Rumble oEmbed response data.
	 * @since 1.5.0
	 * @access private
	 */
	private function get_rumble_oembed_data( $url ) {
		$response = array(
			'thumbnail_url' => '',
			'duration'      => '',
			'html'          => '',
			'video_id'      => '',
		);

		$cache_key   = 'uael_rumble_' . md5( $url );
		$cache_value = wp_cache_get( $cache_key );

		if ( is_array( $cache_value ) ) {
			$response = array_merge( $response, $cache_value );
			return $response;
		}

		$api_response = wp_remote_get( 'https://rumble.com/api/Media/oembed.json?url=' . urlencode( $url ), array( 'timeout' => 15 ) );

		if ( is_array( $api_response ) && ! is_wp_error( $api_response ) && 200 === wp_remote_retrieve_response_code( $api_response ) ) {
			$api_response = json_decode( $api_response['body'] );

			if ( isset( $api_response->thumbnail_url ) ) {
				$response['thumbnail_url'] = $api_response->thumbnail_url;
			}

			if ( isset( $api_response->duration ) ) {
				$response['duration'] = $api_response->duration;
			}

			if ( isset( $api_response->html ) ) {
				$response['html'] = $api_response->html;
				
				// Extract video ID from the HTML iframe.
				if ( preg_match( '/rumble\.com\/embed\/([^\/\?"]+)/', $api_response->html, $matches ) ) {
					$response['video_id'] = $matches[1];
				}
			}
		}

		// Fallback: try to extract video ID from URL using improved regex.
		if ( empty( $response['video_id'] ) ) {
			// Handle various Rumble URL formats.
			if ( preg_match( '/rumble\.com\/v([^-\/?]+)/', $url, $matches ) ) {
				$response['video_id'] = 'v' . $matches[1];
			} elseif ( preg_match( '/rumble\.com\/([^-\/?]+)/', $url, $matches ) ) {
				$response['video_id'] = $matches[1];
			}
		}

		if ( ! empty( $response['thumbnail_url'] ) || ! empty( $response['video_id'] ) ) {
			wp_cache_set( $cache_key, $response, '', HOUR_IN_SECONDS );
		}

		return $response;
	}

	/**
	 * Render Play Button.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	public function get_play_button() {

		$settings = $this->get_settings_for_display();

		if ( 'icon' === $settings['play_source'] ) {
			?>
			<?php
			if ( UAEL_Helper::is_elementor_updated() ) {
				if ( ! empty( $settings['play_icon'] ) || ! empty( $settings['new_play_icon'] ) ) :
					$migration_allowed = \Elementor\Icons_Manager::is_migration_allowed();
					$migrated          = isset( $settings['__fa4_migrated']['new_play_icon'] );
					$is_new            = ! isset( $settings['play_icon'] ) && $migration_allowed;
					?>
					<span class="<?php echo 'elementor-animation-' . esc_attr( $settings['hover_animation'] ); ?> uael-vg__play-icon">
						<?php
						if ( $is_new || $migrated ) {
							\Elementor\Icons_Manager::render_icon( $settings['new_play_icon'], array( 'aria-hidden' => 'true' ) );
						} elseif ( ! empty( $settings['play_icon'] ) ) {
							?>
								<i class="<?php echo esc_attr( $settings['play_icon'] ); ?>" aria-hidden="true"></i>
						<?php } ?>
					</span>
				<?php endif; ?>
			<?php } elseif ( ! empty( $settings['play_icon'] ) ) { ?>
				<span class="<?php echo 'elementor-animation-' . esc_attr( $settings['hover_animation'] ); ?> uael-vg__play-icon">
					<i class="<?php echo esc_attr( $settings['play_icon'] ); ?>" aria-hidden="true"></i>
				</span>
			<?php } ?>
		<?php } else { ?>
			<img class="uael-vg__dummy-image" alt="" />
			<img class="uael-vg__play-image <?php echo 'elementor-animation-' . esc_attr( $settings['hover_animation_img'] ); ?>" src="<?php echo esc_url( $settings['play_img']['url'] ); ?>" alt="<?php echo wp_kses_post( Control_Media::get_image_alt( $settings['play_img'] ) ); ?>"/>

			<?php
		}
	}

	/**
	 * Render Gallery Data.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	public function render_gallery_inner_data() {

		$settings          = $this->get_settings_for_display();
		$new_gallery       = array();
		$gallery           = $settings['gallery_items'];
		$vurl              = '';
		static $videocount = 1;

		if ( 'rand' === $settings['gallery_rand'] ) {

			$keys = array_keys( $gallery );
			shuffle( $keys );

			foreach ( $keys as $key ) {
				$new_gallery[ $key ] = $gallery[ $key ];
			}
		} else {
			$new_gallery = $gallery;
		}
		foreach ( $new_gallery as $index => $item ) {

			$url = $this->get_placeholder_image( $item );

			$video_url = $item['video_url'];

			if ( 'bunny' === $item['type'] ) {
				$video_url = $item['bunny_url'];
				$video_url = str_replace( '/play/', '/embed/', $video_url );
				$video_url = strtok( $video_url, '?' );
			}

			if ( 'hosted' === $item['type'] ) {
				if ( ! empty( $item['insert_url'] ) ) {
					$video_url = $item['external_url']['url'];
				} else {
					$video_url = $item['hosted_url']['url'];
				}
			}

			if ( 'wistia' === $item['type'] ) {
				$wistia_id = $this->getStringBetween( $item['wistia_url'], 'wvideo=', '"' );
				$video_url = 'https://fast.wistia.net/embed/iframe/' . $wistia_id . '?videoFoam=true';
			}

			if ( 'rumble' === $item['type'] ) {
				$rumble_id = $url['video_id'];
				$video_url = 'https://rumble.com/embed/' . $rumble_id . '/';
			}

			$this->add_render_attribute( 'grid-item' . $index, 'class', 'uael-video__gallery-item' );
			$this->add_render_attribute( 'grid-item' . $index, 'id', 'uael-video__gallery-item' . ( $videocount++ ) );

			// Render filter / tags classes.
			if ( 'yes' === $settings['show_filter'] && 'grid' === $settings['layout'] ) {

				if ( '' !== $item['tags'] ) {

					$tags = $this->get_tag_class( $item );
					foreach ( $tags as $key => &$value ) {
						$value = preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $value ) );
						$value = 'filter-' . $value;
					}
					$this->add_render_attribute( 'grid-item' . $index, 'class', ( array_values( $tags ) ) );
				}
			}

			// Generate embed video URL for both inline and lightbox modes.
			$vurl = '';
			switch ( $item['type'] ) {
				case 'youtube':
					$vurl = 'https://www.youtube.com/embed/' . $url['video_id'] . '?autoplay=1&version=3&enablejsapi=1';
					break;

				case 'vimeo':
					$vurl = 'https://player.vimeo.com/video/' . $url['video_id'] . '?autoplay=1&version=3&enablejsapi=1';

					/**
					 * Support Vimeo unlisted and private videos
					 */
					$h_param        = array();
					$video_url_temp = $item['video_url'];
					preg_match( '/(?|(?:[\?|\&]h={1})([\w]+)|\d\/([\w]+))/', $video_url_temp, $h_param );

					if ( ! empty( $h_param ) ) {
						$vurl .= '&h=' . $h_param[1];
					}
					break;

				case 'rumble':
					$vurl = 'https://rumble.com/embed/' . $url['video_id'];
					break;
				
				case 'bunny':
					// For Bunny.net, construct URL from library ID and video ID.
					if ( ! empty( $url['video_id'] ) && strpos( $url['video_id'], '/' ) !== false ) {
							// ID format is "libraryId/videoId".
							$vurl = 'https://iframe.mediadelivery.net/embed/' . $url['video_id'];
					} else {
							// Fallback: use the original link but clean it.
							$vurl = $item['bunny_url'];
							// Convert /play/ URLs to /embed/ format if needed.
							$vurl = str_replace( '/play/', '/embed/', $vurl );
							// Remove any existing parameters from the original URL.
							$vurl = strtok( $vurl, '?' );
					}
					break;
					
				case 'wistia':
					$wistia_id = $this->getStringBetween( $item['wistia_url'], 'wvideo=', '"' );
					$vurl      = 'https://fast.wistia.net/embed/iframe/' . $wistia_id . '?videoFoam=true&autoplay=1';
					break;
					
				case 'hosted':
					$vurl = $video_url . '?&autoplay=1';
					break;

				default:
					$vurl = $video_url;
					break;
			}

			// Render video link attributes.
			$this->add_render_attribute(
				'video-grid-item' . $index,
				array(
					'class' => 'uael-vg__play',
				)
			);

			$this->add_render_attribute(
				'video-container-link' . $index,
				array(
					'class' => 'elementor-clickable uael-vg__play_full',
					'href'  => $vurl,
				)
			);

			if ( 'wistia' === $item['type'] || 'bunny' === $item['type'] || 'rumble' === $item['type'] ) {
				$this->add_render_attribute(
					'video-container-link' . $index,
					array(
						'data-type'      => 'iframe',
						'data-fitToView' => 'false',
						'data-autoSize'  => 'false',
					)
				);
			}

			if ( 'inline' !== $settings['click_action'] ) {

				$this->add_render_attribute( 'video-container-link' . $index, 'data-fancybox', 'uael-video-gallery-' . $this->get_id() );

			} else {
				$this->add_render_attribute( 'video-container-link' . $index, 'data-url', $vurl );
			}
			?>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'grid-item' . $index ) ); ?>>

					<?php
						$url = empty( $url['url'] ) ? '' : esc_url( $url['url'] );
					?>
					<div class="uael-video__gallery-iframe" style="background-image:url('<?php echo esc_url( $url ); ?>');">
						<a <?php echo wp_kses_post( $this->get_render_attribute_string( 'video-container-link' . $index ) ); ?>>
							<div class="uael-video__content-wrap">
								<div class="uael-video__content">
									<?php
									if ( 'below_video' !== $settings['video_caption'] ) {
											$this->get_caption( $item );
									}
									?>
									<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'video-grid-item' . $index ) ); ?>>
										<?php $this->get_play_button(); ?>
									</div>

									<?php $this->get_tag( $item ); ?>

								</div>
							</div>
						</a>
					</div>
					<?php
					if ( 'below_video' === $settings['video_caption'] ) {
						$this->get_caption( $item );
					}
					?>
					<div class="uael-vg__overlay"></div>
					<?php do_action( 'uael_video_gallery_after_video', $item, $settings ); ?>
				</div>
				<?php
		}

	}

	/**
	 * Returns the Caption HTML.
	 *
	 * @param Array $item Current video array.
	 * @since 1.5.0
	 * @access public
	 */
	public function get_caption( $item ) {

		$settings = $this->get_settings_for_display();

		if ( '' === $item['title'] ) {
			return;
		}

		if ( 'yes' !== $settings['show_caption'] ) {
			return;
		}
		?>

		<h4 class="uael-video__caption"><?php echo wp_kses_post( $item['title'] ); ?></h4>

		<?php
	}

	/**
	 * Returns the Filter HTML.
	 *
	 * @param Array $item Current video array.
	 * @since 1.5.0
	 * @access public
	 */
	public function get_tag( $item ) {

		$settings = $this->get_settings_for_display();

		if ( '' === $item['tags'] ) {
			return;
		}

		if ( 'yes' !== $settings['show_tag'] ) {
			return;
		}
		?>

		<span class="uael-video__tags"><?php echo wp_kses_post( $item['tags'] ); ?></span>

		<?php
	}

	/**
	 * Clean string - Removes spaces and special chars.
	 *
	 * @since 1.5.0
	 * @param String $string String to be cleaned.
	 * @return array Google Map languages List.
	 */
	public function clean( $string ) {

		// Replaces all spaces with hyphens.
		$string = str_replace( ' ', '-', $string );

		// Removes special chars.
		$string = preg_replace( '/[^A-Za-z0-9\-]/', '', $string );

		// Turn into lower case characters.
		return strtolower( $string );
	}

	/**
	 * Get Filter taxonomy array.
	 *
	 * Returns the Filter array of objects.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	public function get_filter_values() {

		$settings = $this->get_settings_for_display();

		$filters = array();

		if ( ! empty( $settings['gallery_items'] ) ) {

			foreach ( $settings['gallery_items'] as $key => $value ) {

				$tags = $this->get_tag_class( $value );

				if ( ! empty( $tags ) ) {

					$filters = array_unique( array_merge( $filters, $tags ) );
				}
			}
		}

		return $filters;
	}

	/**
	 * Get Filters.
	 *
	 * Returns the Filter HTML.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	public function render_gallery_filters() {

		$settings = $this->get_settings_for_display();

		$filters = $this->get_filter_values();

		$filters = apply_filters( 'uael_video_gallery_filters', $filters );

		$default = '';

		$tab_responsive = ( 'yes' === $settings['tabs_dropdown'] ) ? ' uael-vgallery-tabs-dropdown' : '';

		if ( 'yes' === $settings['default_filter_switch'] && '' !== $settings['default_filter'] ) {
			$default = trim( $settings['default_filter'] );
			$default = preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $default ) );
			$default = '.filter-' . $default;
		}

		$hover_effect      = isset( $settings['cat_hover_effect'] ) ? $settings['cat_hover_effect'] : 'none';
		$hover_effect_attr = ( 'none' !== $hover_effect ) ? ' data-hover-effect="' . esc_attr( $hover_effect ) . '"' : '';

		?>
		<div class="uael-video-gallery-filters-wrap<?php echo esc_attr( $tab_responsive ); ?>">
			<?php
			if ( 'yes' === $settings['show_filter_title'] ) {
				$heading_size_tag = UAEL_Helper::validate_html_tag( $settings['filter_title_tag'] );
				?>
				<div class="uael-video-gallery-title-filters">
					<div class="uael-video-gallery-title">
						<<?php echo wp_kses_post( $heading_size_tag ); ?> class="uael-video-gallery-title-text"><?php echo wp_kses_post( $settings['filters_heading_text'] ); ?></<?php echo wp_kses_post( $heading_size_tag ); ?>>
					</div>
			<?php } ?>
					<ul class="uael-video__gallery-filters" data-default="<?php echo esc_attr( $default ); ?>">
						<li class="uael-video__gallery-filter uael-filter__current" data-filter="*"<?php echo wp_kses_post( $hover_effect_attr ); ?>><?php echo wp_kses_post( $settings['filters_all_text'] ); ?></li>
						<?php
						foreach ( $filters as $key => $value ) {
							$special_char = preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $value ) );
							?>
							<li class="uael-video__gallery-filter" data-filter="<?php echo '.filter-' . esc_attr( $special_char ); ?>"<?php echo wp_kses_post( $hover_effect_attr ); ?>><?php echo esc_html( $value ); ?></li>
						<?php } ?>
					</ul>

					<?php if ( 'yes' === $settings['tabs_dropdown'] ) { ?>
						<div class="uael-filters-dropdown">
							<div class="uael-filters-dropdown-button"><?php echo wp_kses_post( $settings['filters_all_text'] ); ?></div>

							<ul class="uael-filters-dropdown-list uael-video__gallery-filters" data-default="<?php echo esc_attr( $default ); ?>">
								<li class="uael-filters-dropdown-item uael-video__gallery-filter uael-filter__current" data-filter="*"<?php echo wp_kses_post( $hover_effect_attr ); ?>><?php echo wp_kses_post( $settings['filters_all_text'] ); ?></li>
								<?php
								foreach ( $filters as $key => $value ) {
									$special_char = preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $value ) );
									?>
									<li class="uael-filters-dropdown-item uael-video__gallery-filter " data-filter="<?php echo '.filter-' . esc_attr( $special_char ); ?>"<?php echo wp_kses_post( $hover_effect_attr ); ?>><?php echo esc_html( $value ); ?></li>
								<?php } ?>
							</ul>
						</div>
					<?php } ?>
			<?php if ( 'yes' === $settings['show_filter_title'] ) { ?>
				</div>
			<?php } ?>
		</div>
		<?php

	}

	/**
	 * Render Buttons output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function render() {

		$settings               = $this->get_settings_for_display();
		$enable_schema          = $settings['schema_support'];
		$node_id                = $this->get_id();
		$filters                = $this->get_filter_values();
		$is_editor              = \Elementor\Plugin::instance()->editor->is_edit_mode();
		$content_schema_warning = '';
		$is_custom              = '';
		$schema_thumbnail_url   = '';
		$custom_thumbnail_url   = '';
		foreach ( $settings['gallery_items'] as $key => $val ) {

			$is_custom = ( 'yes' === $val['custom_placeholder'] ? true : false );

			foreach ( $val as $image_url => $url_value ) {

				if ( is_array( $url_value ) ) {

					if ( 'placeholder_image' === $image_url ) {
						$custom_image         = $url_value['url'];
						$custom_thumbnail_url = isset( $custom_image ) ? $custom_image : '';
					}

					if ( 'schema_thumbnail' === $image_url ) {
						$schema_image         = $url_value['url'];
						$schema_thumbnail_url = isset( $schema_image ) ? $schema_image : '';
					}

					if ( 'yes' === $enable_schema && ( ( '' === $val['schema_title'] || '' === $val['schema_description'] || '' === $val['schema_upload_date'] || ( ! $is_custom && '' === $schema_thumbnail_url ) || ( $is_custom && '' === $custom_thumbnail_url ) ) ) ) {
						$content_schema_warning = true;
					}
				}
			}
			if ( 'yes' === $enable_schema && true === $content_schema_warning && $is_editor ) {
				?>
				<div class="uael-builder-msg elementor-alert elementor-alert-warning">
					<?php if ( $is_custom && '' === $custom_thumbnail_url ) { ?>
						<span class="elementor-alert-description"><?php esc_html_e( 'Please set a custom thumbnail to display video gallery schema properly.', 'uael' ); ?></span>
					<?php } else { ?>
						<span class="elementor-alert-description"><?php esc_html_e( 'Some fields are empty under the video gallery schema section. Please fill in all required fields.', 'uael' ); ?></span>
					<?php } ?>
				</div>
				<?php
				break;
			}
		}

		$this->add_render_attribute( 'wrap', 'class', 'uael-video-gallery-wrap' );
		$this->add_render_attribute( 'wrap', 'class', 'uael-vg__layout-' . $settings['layout'] );
		$this->add_render_attribute( 'wrap', 'class', 'uael-vg__action-' . $settings['click_action'] );
		$this->add_render_attribute( 'wrap', 'data-action', $settings['click_action'] );
		$this->add_render_attribute( 'wrap', 'data-layout', $settings['layout'] );
		$this->add_render_attribute( 'wrap', 'class', 'uael-aspect-ratio-' . $settings['video_ratio'] );

		// Add video effects data attributes and CSS class.
		if ( ! empty( $settings['video_effect'] ) && 'normal' !== $settings['video_effect'] ) {
			$this->add_render_attribute( 'wrap', 'data-video-effect', $settings['video_effect'] );
			$this->add_render_attribute( 'wrap', 'data-video-effect-apply-to', ! empty( $settings['video_effect_apply_to'] ) ? $settings['video_effect_apply_to'] : 'thumbnail' );
			// Add CSS class for video effect styling.
			$this->add_render_attribute( 'wrap', 'class', 'uael-vgallery-' . $settings['video_effect'] );
		}

		foreach ( $filters as $key => &$value ) {
			$value = preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $value ) );
			$value = 'filter-' . esc_attr( $value );
		}
		$this->add_render_attribute( 'wrap', 'data-all-filters', array_values( $filters ) );

		if ( 'yes' === $settings['show_filter'] && 'grid' === $settings['layout'] ) {

			$this->add_render_attribute( 'wrap', 'class', 'uael-video-gallery-filter' );

			$this->add_render_attribute( 'wrap', 'data-filter-default', $settings['filters_all_text'] );

			$this->render_gallery_filters();
		}

		// Add hover effects CSS.
		$hover_effect = isset( $settings['cat_hover_effect'] ) ? $settings['cat_hover_effect'] : 'none';
		$widget_id    = $this->get_id();
		
		if ( 'none' !== $hover_effect ) {
			echo '<style>
				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter {
					position: relative;
					overflow: hidden;
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect]:before,
				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect]:after {
					content: "";
					position: absolute;
					opacity: 0;
					transition: all 300ms ease;
					pointer-events: none;
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="underline"]:after {
					left: 0;
					right: 0;
					bottom: -2px;
					height: 2px;
					transform: scaleX(0);
					transform-origin: center;
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="underline"]:hover:after,
				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="underline"].uael-filter__current:after {
					opacity: 1;
					transform: scaleX(1);
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="overline"]:after {
					left: 0;
					right: 0;
					top: -2px;
					height: 2px;
					transform: scaleX(0);
					transform-origin: center;
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="overline"]:hover:after,
				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="overline"].uael-filter__current:after {
					opacity: 1;
					transform: scaleX(1);
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="framed"]:after {
					top: 0;
					right: 0;
					bottom: 0;
					left: 0;
					border: 2px solid transparent;
					transform: scale(0.8);
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="framed"]:hover:after,
				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="framed"].uael-filter__current:after {
					opacity: 1;
					transform: scale(1);
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="double_line"]:before {
					left: 0;
					right: 0;
					top: -2px;
					height: 2px;
					transform: scaleX(0);
					transform-origin: center;
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="double_line"]:after {
					left: 0;
					right: 0;
					bottom: -2px;
					height: 2px;
					transform: scaleX(0);
					transform-origin: center;
				}

				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="double_line"]:hover:before,
				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="double_line"]:hover:after,
				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="double_line"].uael-filter__current:before,
				.elementor-element-' . esc_attr( $widget_id ) . ' .uael-video__gallery-filter[data-hover-effect="double_line"].uael-filter__current:after {
					opacity: 1;
					transform: scaleX(1);
				}
			</style>';
		}

		echo '<div ' . wp_kses_post( sanitize_text_field( $this->get_render_attribute_string( 'wrap' ) ) ) . ' ' . wp_kses_post( sanitize_text_field( $this->get_slider_attr() ) ) . '>';

			$this->render_gallery_inner_data();

		echo '</div>';

		$this->render_masonry_script();
	}

	/**
	 * Get filter options for video effects.
	 *
	 * @since 1.40.2
	 * @access protected
	 * @return array Filters.
	 */
	protected function video_filter_options() {
		$filter = array(
			'normal'    => __( 'Normal', 'uael' ),
			'a-1977'    => __( '1977', 'uael' ),
			'aden'      => __( 'Aden', 'uael' ),
			'earlybird' => __( 'Earlybird', 'uael' ),
			'hudson'    => __( 'Hudson', 'uael' ),
			'inkwell'   => __( 'Inkwell', 'uael' ),
			'perpetua'  => __( 'Perpetua', 'uael' ),
			'poprocket' => __( 'Poprocket', 'uael' ),
			'sutro'     => __( 'Sutro', 'uael' ),
			'toaster'   => __( 'Toaster', 'uael' ),
			'willow'    => __( 'Willow', 'uael' ),
		);

		return $filter;
	}

}
