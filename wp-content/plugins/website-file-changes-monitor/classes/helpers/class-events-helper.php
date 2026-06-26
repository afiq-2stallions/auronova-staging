<?php
/**
 * Handle logging during scans.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility file and directory functions.
 *
 * @since 2.0.0
 */
class Events_Helper {

	/**
	 * Get event labels.
	 *
	 * @return array - Labels.
	 *
	 * @since 2.3.0
	 */
	public static function get_event_label_array() {
		$event_labels = array(
			'file-scan-started'                    => __( 'File scan started ', 'website-file-changes-monitor' ),
			'core-file-modified'                   => __( 'Core file modified ', 'website-file-changes-monitor' ),
			'core-file-renamed'                    => __( 'Core file renamed ', 'website-file-changes-monitor' ),
			'core-file-permissions-changed'        => __( 'Core file permissions modified', 'website-file-changes-monitor' ),
			'core-directory-permissions-changed'   => __( 'Core directory permissions modified', 'website-file-changes-monitor' ),
			'core-file-added'                      => __( 'Core file added ', 'website-file-changes-monitor' ),
			'core-file-removed'                    => __( 'Core file removed ', 'website-file-changes-monitor' ),
			'core-directory-added'                 => __( 'Core Directory added ', 'website-file-changes-monitor' ),
			'core-directory-modified'              => __( 'Core Directory modified ', 'website-file-changes-monitor' ),
			'core-directory-removed'               => __( 'Core Directory removed ', 'website-file-changes-monitor' ),
			'file-scan-complete'                   => __( 'File scan complete ', 'website-file-changes-monitor' ),
			'file-scan-aborted'                    => __( 'File scan aborted ', 'website-file-changes-monitor' ),
			'other-file-added'                     => __( 'File(s) added ', 'website-file-changes-monitor' ),
			'other-file-modified'                  => __( 'File(s) modified ', 'website-file-changes-monitor' ),
			'other-file-removed'                   => __( 'File(s) removed ', 'website-file-changes-monitor' ),
			'other-file-renamed'                   => __( 'File(s) renamed ', 'website-file-changes-monitor' ),
			'other-file-permissions-changed'       => __( 'Other file permissions modified', 'website-file-changes-monitor' ),
			'other-directory-permissions-changed'  => __( 'Other directory permissions modified', 'website-file-changes-monitor' ),
			'other-directory-added'                => __( 'Directory added ', 'website-file-changes-monitor' ),
			'other-directory-modified'             => __( 'Directory modified ', 'website-file-changes-monitor' ),
			'other-directory-removed'              => __( 'Directory removed ', 'website-file-changes-monitor' ),
			'plugin-file-added'                    => __( 'Plugin file(s) added ', 'website-file-changes-monitor' ),
			'plugin-file-modified'                 => __( 'Plugin file(s) modified ', 'website-file-changes-monitor' ),
			'plugin-file-renamed'                  => __( 'Plugin file(s) renamed ', 'website-file-changes-monitor' ),
			'plugin-file-permissions-changed'      => __( 'Plugin file permissions modified', 'website-file-changes-monitor' ),
			'plugin-directory-permissions-changed' => __( 'Plugin directory permissions modified', 'website-file-changes-monitor' ),
			'plugin-directory-removed'             => __( 'Plugin removed ', 'website-file-changes-monitor' ),
			'plugin-directory-added'               => __( 'Plugin added ', 'website-file-changes-monitor' ),
			'plugin-updated'                       => __( 'Plugin updated ', 'website-file-changes-monitor' ),
			'theme-file-added'                     => __( 'Theme file(s) added ', 'website-file-changes-monitor' ),
			'theme-file-modified'                  => __( 'Theme file(s) modified ', 'website-file-changes-monitor' ),
			'theme-file-renamed'                   => __( 'Theme file(s) renamed ', 'website-file-changes-monitor' ),
			'theme-directory-permissions-changed'  => __( 'Theme directory permissions modified', 'website-file-changes-monitor' ),
			'theme-directory-removed'              => __( 'Theme removed ', 'website-file-changes-monitor' ),
			'theme-directory-added'                => __( 'Theme added ', 'website-file-changes-monitor' ),
			'theme-updated'                        => __( 'Theme updated ', 'website-file-changes-monitor' ),
		);

		return $event_labels;
	}

	/**
	 * Create and output neat label for use in events view.
	 *
	 * @param string $types - Lookup type.
	 * @param string $current_view - View.
	 * @param bool   $return_instead_of_echo - Return or echo markup.
	 *
	 * @return void|string.
	 *
	 * @since 2.0.0
	 */
	public static function create_event_type_label( $types, $current_view = 'all', $return_instead_of_echo = false ) {
		$event_labels = self::get_event_label_array();

		$label = $types;
		if ( strpos( $types, ',' ) !== false ) {
			$label     = '';
			$types_arr = explode( ',', $types );
			if ( 'all' !== $current_view ) {
				$label = ( isset( $event_labels[ $types_arr[0] ] ) ) ? $event_labels[ $types_arr[0] ] : $types;
				$label = preg_replace( '/\W\w+\s*(\W*)$/', '$1', $label ) . ' ' . $current_view;
			} else {
				foreach ( $types_arr as $key => $value ) {
					if ( isset( $event_labels[ $value ] ) ) {
						$label = ( '' === $label || ! $label ) ? $event_labels[ $value ] : $label . ' & ' . $event_labels[ $value ];
					} else {
						$label = ( '' === $label || ! $label ) ? $value : $label . ' & ' . $value;
					}
				}
			}
		} elseif ( isset( $event_labels[ $types ] ) ) {
				$label = $event_labels[ $types ];
		}

		if ( $return_instead_of_echo ) {
			return $label;
		} else {
			echo '<strong>' . esc_html__( 'Event Type:', 'website-file-changes-monitor' ) . '</strong> ' . esc_attr( $label ) . '<br>';
		}
	}

	/**
	 * Create and output distinguishing label markup for use in events list.
	 *
	 * @param string $path - Current path.
	 * @param array  $plugin_list - Current plugin list.
	 * @param array  $core_file_keys - Core files list.
	 * @param string $event_type - Current event type.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function create_list_label( $path, $plugin_list, $core_file_keys, $event_type ) {
		$event['path'] = $path;
		$theme_dir     = dirname( get_template_directory() );
		if ( in_array( $event['path'], $plugin_list, true ) || strpos( $event['path'], WP_PLUGIN_DIR ) !== false ) {
			?>
				<strong style="color: blue;"><?php echo esc_html__( 'PLUGIN', 'website-file-changes-monitor' ); ?></strong><br>
			<?php
		} elseif ( strpos( $event['path'], $theme_dir ) !== false ) {
			?>
				<strong style="color: orange;"><?php echo esc_html__( 'THEME', 'website-file-changes-monitor' ); ?></strong><br>
			<?php
		} elseif ( str_contains( strtolower( $event_type ), 'core' ) || in_array( $event['path'], $core_file_keys, true ) || ABSPATH === trim( trailingslashit( $event['path'] ) ) || ABSPATH === (string) $path || ABSPATH . 'wp-includes/' === trailingslashit( (string) $path ) || ABSPATH . 'wp-admin/' === trailingslashit( (string) $path ) ) {
			?>
				<strong style="color: red;"><?php echo esc_html__( 'CORE', 'website-file-changes-monitor' ); ?></strong><span class="mfm-info-hint hint--right" aria-label="<?php echo esc_html__( 'This event indicates a core file was has been modified from its expected hash', 'website-file-changes-monitor' ); ?>"><span class="dashicons dashicons-warning"></span></span><br>
			<?php
		} else {
			?>
				<strong style="color: grey;"><?php echo esc_html__( 'OTHER', 'website-file-changes-monitor' ); ?></strong><br>
			<?php
		}
	}

	/**
	 * Create and output HTML for file list, shown in each event in the events viewer.
	 *
	 * @param array  $items - Event items.
	 * @param string $current_view - Current view.
	 * @param int    $event_id - Event ID.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function create_file_list( $items, $current_view, $event_id ) {
		foreach ( $items as $type => $item ) {
			$file_array = maybe_unserialize( $item );

			if ( ! is_array( $file_array ) ) {
				$file_array = maybe_unserialize( str_replace( ABSPATH, '', $item ) );
			}

			if ( is_array( $file_array ) && ! empty( $file_array ) ) {
				$expand_string = '';
				if ( isset( $file_array['permissions_changed'] ) ) {
					if ( ! empty( $file_array['permissions_changed'] ) ) {
						$count          = ( count( $file_array['permissions_changed'] ) > 500 ) ? '500+' : count( $file_array['permissions_changed'] );
						$expand_string .= $count . ' ' . esc_html__( 'permissions changed', 'website-file-changes-monitor' );
					}
				}
				if ( isset( $file_array['modified'] ) ) {
					if ( ! empty( $file_array['modified'] ) ) {
						$count          = ( count( $file_array['modified'] ) > 500 ) ? '500+' : count( $file_array['modified'] );
						$expand_string .= $count . ' ' . esc_html__( 'modified files', 'website-file-changes-monitor' );
					}
				}
				if ( isset( $file_array['added'] ) ) {
					if ( ! empty( $file_array['added'] ) ) {
						$count          = ( count( $file_array['added'] ) > 500 ) ? '500+' : count( $file_array['added'] );
						$expand_string .= ( empty( $expand_string ) ) ? $count . ' ' . esc_html__( 'added files', 'website-file-changes-monitor' ) : ', ' . $count . ' ' . esc_html__( 'added files', 'website-file-changes-monitor' );
					}
				}
				if ( isset( $file_array['removed'] ) ) {
					if ( ! empty( $file_array['removed'] ) ) {
						$count          = ( count( $file_array['removed'] ) > 500 ) ? '500+' : count( $file_array['removed'] );
						$expand_string .= ( empty( $expand_string ) ) ? $count . ' ' . esc_html__( 'removed files', 'website-file-changes-monitor' ) : ', ' . $count . ' ' . esc_html__( 'removed files', 'website-file-changes-monitor' );
					}
				}

				$expander_needed = true;
				$item_class      = '';
				foreach ( $file_array as $change_type => $changes ) {
					if ( count( $changes ) < 3 ) {
						$expander_needed = false;
						$item_class      = 'expanded';
					}
				}

				if ( $expander_needed ) {
					?>
						<span data-expand-list-wrapper><a href="#" data-expand-list class="hint--right" aria-label="<?php echo esc_html__( 'Click to view changes', 'website-file-changes-monitor' ); ?>"><span class="dashicons dashicons-arrow-right-alt"></span></a><?php echo esc_attr( $expand_string ); ?></span>
					<?php
				}

				foreach ( $file_array as $change_type => $changes ) {
					if ( ! is_array( $changes ) ) {
								continue;
					}

					foreach ( $changes as $file ) {
						if ( 'additional_external_data' === $file ) {
							?>
								<span><span data-additional-prefix-holder><?php echo esc_html__( 'Additional changes found:', 'website-file-changes-monitor' ); ?></span> <a href="#" data-mfm-load-further-changes="<?php echo esc_attr( $event_id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'load_extra_metadata' ) ); ?>"><?php echo esc_html__( 'Load additional changes', 'website-file-changes-monitor' ); ?></a> <span class="mfm-action-spinner"><div class="icon-spin"><span class="dashicons dashicons-admin-generic"></span></div></span></span>
							<?php
						} else {
							if ( 'all' !== $current_view ) {
								if ( $change_type !== $current_view ) {
									continue;
								}
							}
							?>
								<?php
								if ( is_array( $file ) && isset( $file['file'] ) ) {
									$file_path = (string) $file['file'];

									$display_text = str_replace( ABSPATH, '', $file_path ) . ' (' . esc_html__( 'Previous: ', 'website-file-changes-monitor' ) . '<b>' . esc_html( $file['previous'] ) . '</b> ' . esc_html__( 'Current: ', 'website-file-changes-monitor' ) . '<b>' . esc_html( $file['current'] ) . '</b>)';
								} else {
									$file_path    = (string) $file;
									$display_text = str_replace( ABSPATH, '', $file );
								}
								?>
								<span class="mfm-list-item <?php echo esc_attr( $item_class ); ?>"><?php echo esc_html__( 'File', 'website-file-changes-monitor' ); ?> <?php echo esc_attr( ucfirst( str_replace( '_', ' ', $change_type ) ) ); ?>: <?php echo wp_kses_post( $display_text ); ?> <div class="mfm_file_actions_panel"><a href="#" data-mfm-update-setting data-exclude-file="<?php echo esc_attr( $file_path ); ?>" class="hint--left" aria-label="Ignore file from future scans"><span class="dashicons dashicons-insert"></span></a></div></span>
							<?php
						}
					}
				}
			} else {
				?>
				<span class="mfm-list-item">
					<?php echo wp_kses_post( $file_array ); ?>
				</span>
				<?php
			}
		}
	}
}
