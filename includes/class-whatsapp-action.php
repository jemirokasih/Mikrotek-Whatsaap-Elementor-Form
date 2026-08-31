<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Action_Base;

/**
 * WhatsApp Action for Elementor Form
 */
class Mikrotek_Elementor_WhatsApp_Action extends Action_Base {

	/**
	 * Get action name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mikrotek_whatsapp';
	}

	/**
	 * Get action label
	 *
	 * @return string
	 */
	public function get_label() {
		return esc_html__( 'WhatsApp', 'mikrotek-wa-elementor' );
	}

	/**
	 * Register settings section in form widget
	 *
	 * @param \Elementor\Widget_Base $widget
	 */
	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'section_mikrotek_whatsapp',
			[
				'label'     => esc_html__( 'WhatsApp', 'mikrotek-wa-elementor' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);

		$widget->add_control(
			'mikrotek_wa_number_source',
			[
				'label'       => esc_html__( 'Tipe Nomor Tujuan', 'mikrotek-wa-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'custom',
				'options'     => [
					'custom' => esc_html__( 'Nomor Kustom (Admin/CS)', 'mikrotek-wa-elementor' ),
					'field'  => esc_html__( 'Ambil dari Field Form (User)', 'mikrotek-wa-elementor' ),
				],
				'description' => esc_html__( 'Pilih apakah pesan dikirim ke nomor tetap atau nomor yang diisi pengguna di form.', 'mikrotek-wa-elementor' ),
			]
		);

		$widget->add_control(
			'mikrotek_wa_number',
			[
				'label'       => esc_html__( 'Nomor WhatsApp', 'mikrotek-wa-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '628123456789',
				'default'     => '',
				'description' => esc_html__( 'Gunakan kode negara tanpa tanda "+" (contoh: 628123456789).', 'mikrotek-wa-elementor' ),
				'condition'   => [
					'mikrotek_wa_number_source' => 'custom',
				],
			]
		);

		$widget->add_control(
			'mikrotek_wa_phone_field_id',
			[
				'label'       => esc_html__( 'Field ID Nomor WhatsApp', 'mikrotek-wa-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'phone',
				'description' => esc_html__( 'Masukkan ID Field Form yang berisi nomor telepon (cek tab Form Fields -> Advanced -> ID).', 'mikrotek-wa-elementor' ),
				'condition'   => [
					'mikrotek_wa_number_source' => 'field',
				],
			]
		);

		$widget->add_control(
			'mikrotek_wa_default_country_code',
			[
				'label'       => esc_html__( 'Kode Negara Default', 'mikrotek-wa-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '62',
				'placeholder' => '62',
				'description' => esc_html__( 'Jika user mengetik 08xxx, angka "0" awal akan otomatis diubah ke kode negara ini (contoh: 62).', 'mikrotek-wa-elementor' ),
			]
		);

		$widget->add_control(
			'mikrotek_wa_message',
			[
				'label'       => esc_html__( 'Format Pesan', 'mikrotek-wa-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 8,
				'default'     => "Halo Admin, ada pesan baru dari form:\n\n[all-fields]",
				'description' => esc_html__( 'Gunakan [all-fields] untuk semua input, atau [field id="ID_FIELD"] untuk field tertentu (contoh: [field id="name"]).', 'mikrotek-wa-elementor' ),
			]
		);

		$widget->add_control(
			'mikrotek_wa_target_blank',
			[
				'label'        => esc_html__( 'Buka di Tab Baru', 'mikrotek-wa-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Ya', 'mikrotek-wa-elementor' ),
				'label_off'    => esc_html__( 'Tidak', 'mikrotek-wa-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Buka WhatsApp di tab/jendela baru setelah submit.', 'mikrotek-wa-elementor' ),
			]
		);

		$widget->add_control(
			'mikrotek_wa_api_type',
			[
				'label'   => esc_html__( 'Endpoint WhatsApp', 'mikrotek-wa-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'api_whatsapp',
				'options' => [
					'api_whatsapp' => 'api.whatsapp.com/send',
					'wa_me'        => 'wa.me',
				],
			]
		);

		$widget->end_controls_section();
	}

	/**
	 * Run action after form submit
	 *
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ) {
		$settings = $record->get( 'form_settings' );

		// 1. Sanitize source & country code
		$raw_source   = ! empty( $settings['mikrotek_wa_number_source'] ) ? sanitize_key( $settings['mikrotek_wa_number_source'] ) : 'custom';
		$source       = in_array( $raw_source, [ 'custom', 'field' ], true ) ? $raw_source : 'custom';
		$country_code = ! empty( $settings['mikrotek_wa_default_country_code'] ) ? preg_replace( '/[^0-9]/', '', (string) $settings['mikrotek_wa_default_country_code'] ) : '62';
		if ( empty( $country_code ) ) {
			$country_code = '62';
		}

		$raw_phone = '';
		if ( 'field' === $source ) {
			$field_id = ! empty( $settings['mikrotek_wa_phone_field_id'] ) ? sanitize_key( trim( (string) $settings['mikrotek_wa_phone_field_id'] ) ) : '';
			if ( ! empty( $field_id ) ) {
				$fields = $record->get( 'fields' );
				if ( isset( $fields[ $field_id ]['value'] ) ) {
					$raw_phone = is_scalar( $fields[ $field_id ]['value'] ) ? sanitize_text_field( (string) $fields[ $field_id ]['value'] ) : '';
				}
			}
		} else {
			$raw_phone = ! empty( $settings['mikrotek_wa_number'] ) ? sanitize_text_field( (string) $settings['mikrotek_wa_number'] ) : '';
		}

		$clean_phone = $this->format_phone_number( $raw_phone, $country_code );

		// 2. Build parsed message (preserving line breaks)
		$message_template = ! empty( $settings['mikrotek_wa_message'] ) ? sanitize_textarea_field( (string) $settings['mikrotek_wa_message'] ) : "[all-fields]";
		$parsed_message   = $this->parse_message_template( $message_template, $record );

		// 3. Build WhatsApp URL
		$raw_api_type = ! empty( $settings['mikrotek_wa_api_type'] ) ? sanitize_key( (string) $settings['mikrotek_wa_api_type'] ) : 'api_whatsapp';
		$api_type     = in_array( $raw_api_type, [ 'api_whatsapp', 'wa_me' ], true ) ? $raw_api_type : 'api_whatsapp';
		$wa_url       = $this->build_whatsapp_url( $clean_phone, $parsed_message, $api_type );

		// 4. Send response to frontend
		$open_in_new_tab = ( isset( $settings['mikrotek_wa_target_blank'] ) && 'yes' === sanitize_key( (string) $settings['mikrotek_wa_target_blank'] ) );

		$response_payload = [
			'url'        => $wa_url,
			'target'     => $open_in_new_tab ? '_blank' : '_self',
			'is_enabled' => true,
		];

		$ajax_handler->add_response_data( 'mikrotek_wa_redirect', $response_payload );

		// Fallback standard Elementor redirect if not opened in new tab
		if ( ! $open_in_new_tab ) {
			$ajax_handler->add_response_data( 'redirect_url', $wa_url );
		}
	}

	/**
	 * Format and sanitize phone number to international WhatsApp digits format
	 *
	 * @param string $phone
	 * @param string $default_country_code
	 * @return string
	 */
	private function format_phone_number( $phone, $default_country_code = '62' ) {
		$clean = preg_replace( '/[^0-9]/', '', (string) $phone );

		if ( empty( $clean ) ) {
			return '';
		}

		// Replace leading 0 with country code
		if ( '0' === substr( $clean, 0, 1 ) ) {
			$clean = $default_country_code . substr( $clean, 1 );
		}

		return $clean;
	}

	/**
	 * Parse message placeholders with actual submitted form values and sanitize outputs
	 *
	 * @param string $template
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
	 * @return string
	 */
	private function parse_message_template( $template, $record ) {
		$fields         = (array) $record->get( 'fields' );
		$all_fields_str = [];
		$replacements   = [];

		// Normalize newlines in template
		$template = str_replace( [ "\r\n", "\r" ], "\n", $template );

		foreach ( $fields as $id => $field ) {
			$clean_id = sanitize_key( (string) $id );
			$title    = ! empty( $field['title'] ) ? sanitize_text_field( (string) $field['title'] ) : ucfirst( $clean_id );

			// Sanitize values
			if ( is_array( $field['value'] ) ) {
				$clean_val = implode( ', ', array_map( 'sanitize_text_field', array_map( 'wp_strip_all_tags', $field['value'] ) ) );
			} else {
				$clean_val = sanitize_text_field( wp_strip_all_tags( (string) $field['value'] ) );
			}

			$all_fields_str[] = "*{$title}*: " . $clean_val;

			// Populate replacement keys
			$replacements[ '[field id="' . $clean_id . '"]' ]   = $clean_val;
			$replacements[ '[field id=\'' . $clean_id . '\']' ] = $clean_val;
			$replacements[ '[field name="' . $clean_id . '"]' ] = $clean_val;
			$replacements[ '[' . $clean_id . ']' ]              = $clean_val;
			$replacements[ '[' . $title . ']' ]                 = $clean_val;
		}

		// Form metadata
		$raw_form_name = $record->get_form_settings( 'form_name' );
		$form_name     = ! empty( $raw_form_name ) ? sanitize_text_field( (string) $raw_form_name ) : esc_html__( 'Form Website', 'mikrotek-wa-elementor' );
		$form_id       = sanitize_key( (string) $record->get_form_settings( 'id' ) );

		$replacements['[form:name]'] = $form_name;
		$replacements['[form:id]']   = $form_id;
		$replacements['[page:url]']  = home_url( add_query_arg( [], $GLOBALS['wp']->request ?? '' ) );

		// Replace [all-fields] with newline separation
		$all_fields_rendered         = implode( "\n", $all_fields_str );
		$replacements['[all-fields]'] = $all_fields_rendered;

		// Perform substitution
		$output = str_replace( array_keys( $replacements ), array_values( $replacements ), $template );

		return trim( $output );
	}

	/**
	 * Build full WhatsApp URL safely with encoded newlines
	 *
	 * @param string $phone
	 * @param string $message
	 * @param string $api_type
	 * @return string
	 */
	private function build_whatsapp_url( $phone, $message, $api_type = 'api_whatsapp' ) {
		$encoded_message = rawurlencode( $message );

		if ( 'wa_me' === $api_type ) {
			$base = ! empty( $phone ) ? 'https://wa.me/' . $phone : 'https://wa.me/';
			return $base . '?text=' . $encoded_message;
		}

		// Default api.whatsapp.com
		if ( ! empty( $phone ) ) {
			return 'https://api.whatsapp.com/send?phone=' . $phone . '&text=' . $encoded_message;
		}

		return 'https://api.whatsapp.com/send?text=' . $encoded_message;
	}

	/**
	 * On export cleanup
	 *
	 * @param \Elementor\Widget_Base $element
	 * @return array
	 */
	public function on_export( $element ) {
		$unset_fields = [
			'mikrotek_wa_number',
			'mikrotek_wa_phone_field_id',
			'mikrotek_wa_message',
		];

		foreach ( $unset_fields as $field ) {
			unset( $element['settings'][ $field ] );
		}

		return $element;
	}
}
