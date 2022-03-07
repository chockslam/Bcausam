<?php
/**
 * Branda SMTP class.
 *
 * @package Branda
 * @subpackage Emails
 */
if ( ! class_exists( 'Branda_SMTP' ) ) {

	class Branda_SMTP extends Branda_Helper {
		protected $option_name = 'ub_smtp';
		private $is_ready      = false;

		/**
		 * Conflicted plugins list
		 *
		 * @since 3.1.0
		 */
		private $plugins_list = array();

		public function __construct() {
			parent::__construct();
			$this->check();
			$this->module = 'smtp';
			/**
			 * hooks
			 */
			if ( $this->is_network ) {
				add_action( 'network_admin_notices', array( $this, 'configure_credentials_notice' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'configure_credentials_notice' ) );
			}
			add_action( 'phpmailer_init', array( $this, 'init_smtp' ), 999 );
			add_filter( 'ultimatebranding_settings_smtp', array( $this, 'admin_options_page' ) );
			add_filter( 'ultimatebranding_settings_smtp_process', array( $this, 'update' ) );
			add_filter( 'ultimatebranding_settings_smtp_reset', array( $this, 'reset_module' ) );
			/**
			 * AJAX
			 */
			add_action( 'wp_ajax_' . $this->get_name( 'send' ), array( $this, 'ajax_send_test_email' ) );
			/**
			 * @since 3.1.0
			 */
			add_action( 'wp_ajax_' . $this->get_name( 'deactivate' ), array( $this, 'ajax_deactivate_coflicted_plugin' ) );
			/**
			 * upgrade options
			 *
			 * @since 3.0.0
			 */
			add_action( 'init', array( $this, 'upgrade_options' ) );
			/**
			 * Add "Send Test Email" button.
			 *
			 * @since 3.0.0
			 */
			add_filter( 'branda_settings_after_box_title', array( $this, 'add_button_after_title' ), 10, 2 );
			/**
			 * Add dialog
			 *
			 * @since 3.0,0
			 */
			add_filter( 'branda_get_module_content', array( $this, 'add_dialog' ), 10, 2 );
			/**
			 * add to javascript messages
			 *
			 * @since 3.0.0
			 */
			add_filter( 'branda_admin_messages_array', array( $this, 'add_messages' ) );
		}

		/**
		 * Add messages to js localize
		 */
		public function add_messages( $array ) {
			$array['messages']['smtp'] = array(
				'empty'   => __( 'Field "To" can not be empty!', 'ub' ),
				'sending' => __( 'Sending message, please wait...', 'ub' ),
				'send'    => __( 'The test message was send successful.', 'ub' ),
			);
			return $array;
		}

		/**
		 * Upgrade option
		 *
		 * @since 2.1.0
		 */
		public function upgrade_options() {
			$value = $this->get_value();
			if ( empty( $value ) || ! is_array( $value ) || ! isset( $value['settings'] ) ) {
				return;
			}
			$data = array(
				'header'              => array(
					'from_email'      => '',
					'from_name_force' => 'on',
					'from_name'       => '',
				),
				'server'              => array(
					'smtp_host'            => '',
					'smtp_type_encryption' => 'ssl',
					'smtp_port'            => '25',
					'smtp_insecure_ssl'    => 'on',
				),
				'smtp_authentication' => array(
					'smtp_authentication' => 'on',
					'smtp_username'       => '',
					'smtp_password'       => '',
				),
			);
			foreach ( $data as $g => $keys ) {
				foreach ( $keys as $k => $v ) {
					if ( isset( $value['settings'][ $k ] ) ) {
						$data[ $g ][ $k ] = $value['settings'][ $k ];
					}
				}
			}
			$this->update_value( $data );
		}

		/**
		 * Add "add feed" button.
		 *
		 * @since 3.0.0
		 */
		public function add_button_after_title( $content, $module ) {
			if ( $this->module !== $module['module'] ) {
				return $content;
			}
			$args = array(
				'data' => array(
					'modal-open' => $this->get_name( 'send' ),
				),
				'text' => __( 'Send Test Email', 'ub' ),
				'sui'  => 'ghost',
			);
			if ( is_wp_error( $this->is_ready ) ) {
				$args['disabled'] = true;
			}
			$content .= '<div class="sui-actions-left">';
			$content .= $this->button( $args );
			$content .= '</div>';
			return $content;
		}

		/**
		 * Send test email
		 *
		 * @since 2.0.0
		 */
		public function ajax_send_test_email() {
			global $wp_version;
			$nonce_action = $this->get_nonce_action( 'send' );
			$this->check_input_data( $nonce_action, array( 'email' ) );
			if ( is_wp_error( $this->is_ready ) ) {
				$this->json_error( $this->is_ready->get_error_message() );
			}
			$email = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_STRING );
			$email = sanitize_email( $email );
			if ( ! is_email( $email ) ) {
				$this->json_error( __( 'Unable to send: wrong email address.', 'ub' ) );
			}
			$errors = '';
			$config = $this->get_value();
			if ( version_compare( $wp_version, '5.5', '<' ) ) {
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				$mail          = new PHPMailer();
			} else {
				require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
				require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
				require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
				$mail          = new PHPMailer\PHPMailer\PHPMailer();
			}
			$charset       = get_bloginfo( 'charset' );
			$mail->CharSet = $charset;
			$from_name     = $this->get_value( 'header', 'from_name' );
			$from_email    = $this->get_value( 'header', 'from_email' );
			$mail->IsSMTP();
			// send plain text test email
			$mail->ContentType = 'text/plain';
			$mail->IsHTML( false );
			/* If using smtp auth, set the username & password */
			$use_auth = $this->get_value( 'smtp_authentication', 'smtp_authentication' );
			if ( 'on' === $use_auth ) {
				$mail->SMTPAuth = true;
				$mail->Username = $this->get_value( 'smtp_authentication', 'smtp_username' );
				$mail->Password = $this->get_value( 'smtp_authentication', 'smtp_password' );
			}
			/* Set the SMTPSecure value, if set to none, leave this blank */
			$type = $this->get_value( 'server', 'smtp_type_encryption' );
			if ( 'none' !== $type ) {
				$mail->SMTPSecure = $type;
			}
			/* PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate. */
			$mail->SMTPAutoTLS = false;
			$insecure_ssl      = $this->get_value( 'server', 'smtp_insecure_ssl' );
			if ( 'on' === $insecure_ssl ) {
				// Insecure SSL option enabled
				$mail->SMTPOptions = array(
					'ssl' => array(
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true,
					),
				);
			}
			/* Set the other options */
			$mail->Host = $this->get_value( 'server', 'smtp_host' );
			$mail->Port = $this->get_value( 'server', 'smtp_port' );
			$mail->SetFrom( $from_email, $from_name );

			// Set Reply To header
			$reply_to_email = $this->get_value( 'reply-to', 'email' );
			if ( ! empty( $reply_to_email ) && is_email( $reply_to_email ) ) {
				$reply_to_name = $this->get_value( 'reply-to', 'name', '' );
				$mail->addReplyTo( $reply_to_email, $reply_to_name );
			}
			$mail->Subject = sprintf( __( 'This is test email sent from "%s"', 'ub' ), get_bloginfo( 'name' ) );
			$mail->Body    = __( 'This is a test mail...', 'ub' );
			$mail->Body   .= PHP_EOL;
			$mail->Body   .= PHP_EOL;
			$mail->Body   .= sprintf( __( 'Send date: %s.', 'ub' ), date( 'c' ) );
			$mail->Body   .= PHP_EOL;
			$mail->Body   .= PHP_EOL;
			$mail->Body   .= '-- ';
			$mail->Body   .= PHP_EOL;
			$mail->Body   .= sprintf( __( 'Site: %s.', 'ub' ), get_bloginfo( 'url' ) );
			$mail->AddAddress( $email );
			if ( Branda_Helper::is_debug() ) {
				$mail->SMTPDebug = 1;
				ob_start();
			}
			/* Send mail and return result */
			if ( ! $mail->Send() ) {
				$errors = $mail->ErrorInfo;
			}
			if ( Branda_Helper::is_debug() ) {
				$debug = ob_get_contents();
				ob_end_clean();
				error_log( $debug );
			}
			$mail->ClearAddresses();
			$mail->ClearAllRecipients();
			if ( ! empty( $errors ) ) {
				$data = array(
					'message' => __( 'Failed to send the test email. Please check your SMTP credentials and try again.', 'ub' ),
					'errors'  => $errors,
				);
				wp_send_json_error( $data );
			}
			$success_message = sprintf( __( 'Test email sent to %s.', 'ub' ), '<strong>' . $email . '</strong>' );
			wp_send_json_success( array( 'message' => $success_message ) );
		}

		/**
		 * Check required credentials
		 *
		 * @since 2.0.0
		 */
		private function check() {
			if (
				isset( $_POST['simple_options'] )
				&& isset( $_POST['simple_options']['server'] )
				&& isset( $_POST['simple_options']['server']['smtp_host'] )
				&& ! empty( $_POST['simple_options']['server']['smtp_host'] )
			) {
				$this->is_ready = true;
				return $this->is_ready;
			}
			$this->is_ready = new WP_Error( 'credentials', __( 'Please configure credentials first.', 'ub' ) );
			$config         = $this->get_value();
			if ( empty( $config ) ) {
				return $this->is_ready;
			}
			if ( ! isset( $config['header'] ) ) {
				return $this->is_ready;
			}
			$config = $this->get_value( 'server', 'smtp_host', false );
			if ( empty( $config ) ) {
				return $this->is_ready;
			}
			$this->is_ready = true;
			return $this->is_ready;
		}

		/**
		 * Init SMTP
		 *
		 * @since 2.0.0
		 */
		public function init_smtp( &$phpmailer ) {
			/**
			 * check if SMTP credentials have been configured.
			 */
			if ( is_wp_error( $this->is_ready ) ) {
				return $this->is_ready->get_error_message();
			}
			/* Set the mailer type as per config above, this overrides the already called isMail method */
			$phpmailer->IsSMTP();
			/**
			 * from name
			 */
			$from_name = $this->get_value( 'header', 'from_name' );
			$force     = $this->get_value( 'header', 'from_name_force' );
			if ( 'off' === $force && ! empty( $phpmailer->FromName ) ) {
				$from_name = $phpmailer->FromName;
			}
			/**
			 * from email
			 */
			$from_email = $this->get_value( 'header', 'from_email' );
			/**
			 * set PHPMailer
			 */
			$phpmailer->From     = $from_email;
			$phpmailer->FromName = $from_name;
			$phpmailer->SetFrom( $phpmailer->From, $phpmailer->FromName );
			/* Set the SMTPSecure value */
			$type = $this->get_value( 'server', 'smtp_type_encryption' );
			if ( 'none' !== $type ) {
				$phpmailer->SMTPSecure = $type;
			}
			/* Set the other options */
			$phpmailer->Host = $this->get_value( 'server', 'smtp_host' );
			$phpmailer->Port = $this->get_value( 'server', 'smtp_port' );
			/* If we're using smtp auth, set the username & password */
			$use_auth = $this->get_value( 'smtp_authentication', 'smtp_authentication' );
			if ( 'on' === $use_auth ) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = $this->get_value( 'smtp_authentication', 'smtp_username' );
				$phpmailer->Password = $this->get_value( 'smtp_authentication', 'smtp_password' );
			}
			// PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate.
			$phpmailer->SMTPAutoTLS = false;
			/* Set the SMTPSecure value, if set to none, leave this blank */
			$insecure_ssl = $this->get_value( 'server', 'smtp_insecure_ssl' );
			if ( 'on' === $insecure_ssl ) {
				$phpmailer->SMTPOptions = array(
					'ssl' => array(
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true,
					),
				);
			}

			// Save Email history if Email Logs module is enabled.
			if ( branda_is_active_module( 'emails/email-logs.php' ) && class_exists( 'Branda_Email_Logs_CPT' ) && method_exists( 'Branda_Email_Logs_CPT', 'save_email_history' ) ) {
				$phpmailer->action_function = function ( ...$args ) use ( $phpmailer ) {
					call_user_func( array( 'Branda_Email_Logs_CPT', 'save_email_history' ), $phpmailer, ...$args );
				};
			}
		}

		/**
		 * modify option name
		 *
		 * @since 2.0.0
		 */
		public function get_module_option_name( $option_name, $module ) {
			if ( is_string( $module ) && $this->module == $module ) {
				return $this->option_name;
			}
			return $option_name;
		}

		/**
		 * Set options
		 *
		 * @since 2.0.0
		 */
		protected function set_options() {
			$options = array(
				'reset-module'        => true,
				'plugins'             => array(),
				'header'              => array(
					'title'       => __( 'From Headers', 'ub' ),
					'description' => __( 'Choose the default from email id and from name for all of your WordPress outgoing emails.', 'ub' ),
					'fields'      => array(
						'from_email'      => array(
							'label'       => __( 'Sender email address', 'ub' ),
							'description' => __( 'You can specify the email address that emails should be sent from.', 'ub' ),
							'default'     => get_bloginfo( 'admin_email' ),
						),
						'from_name'       => array(
							'label'        => __( 'Sender name', 'ub' ),
							'placeholder'  => esc_attr__( 'Enter the sender email', 'ub' ),
							'description'  => array(
								'content'  => __( 'For example, you can use your website’s title as the default sender name.', 'ub' ),
								'position' => 'bottom',
							),
							'master'       => 'from-name',
							'master-value' => 'on',
							'display'      => 'sui-tab-content',
						),
						'from_name_force' => array(
							'type'        => 'sui-tab',
							'label'       => __( 'From name replacement', 'ub' ),
							'description' => __( 'Set your own from name for each email sent from your website. Be carefully since it will override the from name provided by other plugins such as Contact Form.', 'ub' ),
							'options'     => array(
								'on'  => __( 'Enable', 'ub' ),
								'off' => __( 'Disable', 'ub' ),
							),
							'default'     => 'on',
							'slave-class' => 'from-name',
						),
					),
				),
				'server'              => array(
					'title'       => __( 'SMTP Server', 'ub' ),
					'description' => __( 'Choose the SMTP server options such as host, port details, encryption etc.', 'ub' ),
					'fields'      => array(
						'smtp_host'            => array(
							'label'       => __( 'Host', 'ub' ),
							'description' => __( 'Enter the host name of your mail server.', 'ub' ),
							'placeholder' => esc_attr__( 'E.g. smtp.example.com', 'ub' ),
						),
						'smtp_type_encryption' => array(
							'type'        => 'sui-tab',
							'label'       => __( 'Encryption', 'ub' ),
							'options'     => array(
								'none' => __( 'None', 'ub' ),
								'ssl'  => __( 'SSL', 'ub' ),
								'tls'  => __( 'TLS', 'ub' ),
							),
							'default'     => 'ssl',
							'description' => __( 'Choose the encryption for your mail server. For most servers, SSL is recommended.', 'ub' ),
						),
						'smtp_port'            => array(
							'type'        => 'number',
							'label'       => __( 'Port', 'ub' ),
							'description' => __( 'Choose the SMTP port as recommended by your mail server.', 'ub' ),
							'default'     => 25,
							'min'         => 1,
						),
						'smtp_insecure_ssl'    => array(
							'type'        => 'sui-tab',
							'label'       => __( 'Insecure SSL certificates', 'ub' ),
							'description' => __( 'You can enable the insecure and self-signed SSL certificates on SMTP server. However, it\'s highly recommended to keep this option disabled.', 'ub' ),
							'options'     => array(
								'on'  => __( 'Enable', 'ub' ),
								'off' => __( 'Disable', 'ub' ),
							),
							'default'     => 'off',
						),
					),
				),
				'smtp_authentication' => array(
					'title'       => __( 'SMTP Authentication', 'ub' ),
					'description' => __( 'Choose whether you want to use SMTPAuth or not. It is recommended to keep this enabled.', 'ub' ),
					'fields'      => array(
						'smtp_username'       => array(
							'label'        => __( 'Username', 'ub' ),
							'placeholder'  => esc_attr__( 'Enter your SMTP username here', 'ub' ),
							'master'       => 'smtp-authentication',
							'master-value' => 'on',
							'display'      => 'sui-tab-content',
						),
						'smtp_password'       => array(
							'type'         => 'password',
							'label'        => __( 'Password', 'ub' ),
							'placeholder'  => esc_attr__( 'Enter your SMTP password here', 'ub' ),
							'master'       => 'smtp-authentication',
							'master-value' => 'on',
							'display'      => 'sui-tab-content',
							'class'        => 'large-text',
						),
						'smtp_authentication' => array(
							'type'        => 'sui-tab',
							'options'     => array(
								'on'  => __( 'Enable', 'ub' ),
								'off' => __( 'Disable', 'ub' ),
							),
							'default'     => 'on',
							'slave-class' => 'smtp-authentication',
						),
					),
				),
			);
			/**
			 * check other SMTP plugin, only on admin page
			 *
			 * @since 3.1.0
			 */
			if ( is_admin() ) {
				$this->check_plugins();
				if ( ! empty( $this->plugins_list ) ) {
					$options['plugins'] = array(
						'title'       => __( 'Conflicted Plugins', 'ub' ),
						'description' => __( 'Branda has detected the following plugins are activated. Please deactivate them to prevent conflicts.', 'ub' ),
						'fields'      => array(
							'message' => array(
								'type'  => 'description',
								'value' => Branda_Helper::sui_notice( esc_html__( 'Branda has detected the following plugins are activated. Please deactivate them to prevent conflicts.', 'ub' ) ),
							),
							'plugins' => array(
								'type'     => 'callback',
								'callback' => array( $this, 'get_list_of_active_plugins' ),
							),
						),
					);
				}
			}
			$this->options = $options;
		}

		/**
		 * Add admin notice about configuration.
		 *
		 * @since 2.0.0
		 */
		public function configure_credentials_notice() {
			if ( true === $this->is_ready ) {
				return;
			}
			if ( ! is_a( $this->uba, 'Branda_Admin' ) ) {
				return;
			}
			/**
			 * Only show in Branda plugin
			 */
			if ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'branding' ) === false ) {
				return;
			}
			$module_data  = $this->uba->get_module_by_module( $this->module );
			$settings_url = add_query_arg(
				array(
					'page'   => 'branding_group_' . $module_data['group'],
					'module' => $this->module,
				),
				network_admin_url( 'admin.php' )
			);
			$message      = array(
				'can_dismiss' => true,
				'message'     => sprintf(
					__( 'Please configure your <a href="%s">SMTP credentials</a> in order to send email using SMTP module.', 'ub' ),
					esc_url( $settings_url )
				),
			);
			$this->uba->add_message( $message );
		}

		/**
		 * Add SUI dialog
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Current module content.
		 * @param array  $module Current module.
		 */
		public function add_dialog( $content, $module ) {
			if ( $this->module !== $module['module'] ) {
				return $content;
			}
			$template = '/admin/common/dialogs/test-email';
			$args     = array(
				'id'          => $this->get_name( 'send' ),
				'description' => __( 'Send a dummy email to test the SMTP configurations.', 'ub' ),
				'nonce'       => $this->get_nonce_value( 'send' ),
				'action'      => $this->get_name( 'send' ),
			);
			$content .= $this->render( $template, $args, true );
			/**
			 * reset module
			 */
			$template = '/admin/common/dialogs/reset-module';
			$title    = __( 'Unknown', 'ub' );
			if ( isset( $module['name_alt'] ) ) {
				$title = $module['name_alt'];
			} elseif ( isset( $module['name'] ) ) {
				$title = $module['name'];
			}
			$args     = array(
				'module' => $this->module,
				'title'  => $title,
				'nonce'  => wp_create_nonce( 'reset-module-' . $this->module ),
			);
			$content .= $this->render( $template, $args, true );
			return $content;
		}

		private function check_plugins() {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$list = array(
				'wp-smtp/wp-smtp.php'                     => array(
					'name'  => 'WP SMTP',
					'class' => 'Branda_SMTP_Importer_WP_SMTP',
				),
				'wp-mail-smtp/wp_mail_smtp.php'           => array(
					'name'  => 'WP Mail SMTP by WPForms',
					'class' => 'Branda_SMTP_Importer_WP_Mail_SMTP',
				),
				'post-smtp/postman-smtp.php'              => array(
					'name' => 'Post SMTP Mailer/Email Log',
				),
				'easy-wp-smtp/easy-wp-smtp.php'           => array(
					'name'  => 'Easy WP SMTP',
					'class' => 'Branda_SMTP_Importer_Easy_WP_SMTP',
				),
				'gmail-smtp/main.php'                     => array(
					'name' => 'Gmail SMTP',
				),
				'smtp-mailer/main.php'                    => array(
					'name' => 'SMTP Mailer',
				),
				'wp-email-smtp/wp_email_smtp.php'         => array(
					'name' => 'WP Email SMTP',
				),
				'bws-smtp/bws-smtp.php'                   => array(
					'name' => 'SMTP by BestWebSoft',
				),
				'wp-sendgrid-smtp/wp-sendgrid-smtp.php'   => array(
					'name' => 'WP SendGrid SMTP',
				),
				'cimy-swift-smtp/cimy_swift_smtp.php'     => array(
					'name' => 'Cimy Swift SMTP',
				),
				'sar-friendly-smtp/sar-friendly-smtp.php' => array(
					'name' => 'SAR Friendly SMTP',
				),
				'wp-easy-smtp/wp-easy-smtp.php'           => array(
					'name' => 'WP Easy SMTP',
				),
				'wp-gmail-smtp/wp-gmail-smtp.php'         => array(
					'name' => 'WP Gmail SMTP',
				),
				'email-log/email-log.php'                 => array(
					'name' => 'Email Log',
				),
				'sendgrid-email-delivery-simplified/wpsendgrid.php' => array(
					'name' => 'SendGrid',
				),
				'mailgun/mailgun.php'                     => array(
					'name' => 'Mailgun for WordPress',
				),
				'wp-mail-bank/wp-mail-bank.php'           => array(
					'name'  => 'WP Mail Bank',
					'class' => 'Branda_SMTP_Importer_WP_Mail_Bank',
				),
			);
			foreach ( $list as $path => $data ) {
				if ( is_plugin_active( $path ) ) {
					$data['file']                = basename( $path );
					$this->plugins_list[ $path ] = $data;
				}
			}
			return;
		}

		public function get_list_of_active_plugins() {
			foreach ( $this->plugins_list as $path => $data ) {
				$this->plugins_list[ $path ]['nonce'] = $this->get_nonce_value( $path );
			}
			$template = sprintf( '/admin/modules/%s/plugins-list', $this->module );
			$args     = array(
				'plugins' => $this->plugins_list,
				'action'  => $this->get_name( 'deactivate' ),
			);
			$content  = $this->render( $template, $args, true );
			return $content;
		}

		public function ajax_deactivate_coflicted_plugin() {
			$id           = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_STRING );
			$nonce_action = $this->get_nonce_action( $id );
			$this->check_input_data( $nonce_action, array( 'mode' ) );
			$mode = filter_input( INPUT_POST, 'mode', FILTER_SANITIZE_STRING );
			switch ( $mode ) {
				case 'deactivate':
					$plugin = wp_unslash( $id );
					deactivate_plugins( $plugin );
					wp_send_json_success();
					break;
				case 'import':
					if ( ! isset( $this->plugins_list[ $id ] ) ) {
						$this->json_error();
					}
					$plugin = $this->plugins_list[ $id ];
					$file   = dirname( __FILE__ ) . '/importers/' . $plugin['file'];
					if ( ! is_file( $file ) ) {
						$this->json_error();
					}
					include_once $file;
					if ( ! class_exists( $plugin['class'] ) ) {
						$this->json_error();
					}
					$importer = new $plugin['class']();
					$importer->import( $this );
					$plugin = wp_unslash( $id );
					deactivate_plugins( $plugin );
					wp_send_json_success();
					break;
				default:
					break;
			}
			$this->json_error();
		}

		public function smtp_get_value() {
			return $this->get_value();
		}

		public function smtp_update_value( $value ) {
			return $this->update_value( $value );
		}
	}
}
new Branda_SMTP();
