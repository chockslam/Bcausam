<?php
/**
 * Author: Hoang Ngo
 */


namespace WP_Defender\Component\Audit;

use WP_Defender\Traits\User;

class Users_Audit extends Audit_Event {
	use User;

	const ACTION_LOGIN = 'login', ACTION_LOGOUT = 'logout', ACTION_REGISTERED = 'registered', ACTION_LOST_PASS = 'lost_password',
		ACTION_RESET_PASS = 'reset_password';

	const CONTEXT_SESSION = 'session', CONTEXT_USERS = 'users', CONTEXT_PROFILE = 'profile';
	private $type = 'user';

	public function get_hooks() {
		return array(
			'wp_login_failed'       => array(
				'args'        => array( 'username' ),
				'text'        => sprintf( esc_html__( "%s User login fail. Username: %s", 'wpdef' ), '{{blog_name}}', '{{username}}' ),
				'event_type'  => $this->type,
				'context'     => self::CONTEXT_SESSION,
				'action_type' => self::ACTION_LOGIN,
			),
			'wp_login'              => array(
				'args'        => array( 'userlogin', 'user' ),
				'text'        => sprintf( esc_html__( "%s User login success: %s", 'wpdef' ), '{{blog_name}}', '{{userlogin}}' ),
				'event_type'  => $this->type,
				'context'     => self::CONTEXT_SESSION,
				'action_type' => self::ACTION_LOGIN,
			),
			'wpmu_2fa_login'        => array(
				'args'        => array( 'user_id' ),
				'text'        => sprintf( esc_html__( "%s 2fa user login success: %s", 'wpdef' ), '{{blog_name}}', '{{username}}' ),
				'event_type'  => $this->type,
				'context'     => self::CONTEXT_SESSION,
				'action_type' => self::ACTION_LOGIN,
				'program_args' => array(
					'username'  => array(
						'callable'        => 'get_user_by',
						'params'          => array(
							'id',
							'{{user_id}}',
						),
						'result_property' => 'user_login',
					),
				),
			),
			'wp_logout'             => array(
				'args'        => array(),
				'text'        => sprintf( esc_html__( "%s User logout success: %s", 'wpdef' ), '{{blog_name}}', '{{username}}' ),
				'event_type'  => $this->type,
				'action_type' => self::ACTION_LOGOUT,
				'context'     => self::CONTEXT_SESSION,
				'custom_args' => array(
					// In this state, current user should be the one who log out.
					'username' => $this->get_user_display( get_current_user_id() )
				)
			),
			'user_register'         => array(
				'args'         => array( 'user_id' ),
				'text'         => is_admin() ? sprintf( esc_html__( "%s %s added a new user: Username: %s, Role: %s", 'wpdef' ), '{{blog_name}}', '{{wp_user}}', '{{username}}', '{{user_role}}' )
					: sprintf( esc_html__( "%s A new user registered: Username: %s, Role: %s", 'wpdef' ), '{{blog_name}}', '{{username}}', '{{user_role}}' ),
				'event_type'   => $this->type,
				'context'      => self::CONTEXT_USERS,
				'action_type'  => self::ACTION_REGISTERED,
				'program_args' => array(
					'username'  => array(
						'callable'        => 'get_user_by',
						'params'          => array(
							'id',
							'{{user_id}}'
						),
						'result_property' => 'user_login'
					),
					'user_role' => array(
						'callable' => array( self::class, 'get_user_role' ),
						'params'   => array(
							'{{user_id}}'
						),
					),
				),
			),
			'delete_user'           => array(
				'args'         => array( 'user_id' ),
				'text'         => sprintf( esc_html__( "%s %s deleted a user: ID: %s, username: %s", 'wpdef' ), '{{blog_name}}', '{{wp_user}}', '{{user_id}}', '{{username}}' ),
				'context'      => self::CONTEXT_USERS,
				'action_type'  => self::ACTION_DELETED,
				'event_type'   => $this->type,
				'program_args' => array(
					'username' => array(
						'callable'        => 'get_user_by',
						'params'          => array(
							'id',
							'{{user_id}}'
						),
						'result_property' => 'user_login'
					),
				),
			),
			'remove_user_from_blog' => array(
				'args'         => array( 'user_id', 'blog_id' ),
				'context'      => self::CONTEXT_USERS,
				'action_type'  => self::ACTION_DELETED,
				'event_type'   => $this->type,
				'callback'     => array( self::class, 'remove_user_from_blog_callback' ),
			),
			'wpmu_delete_user'      => array(
				'args'         => array( 'user_id' ),
				'text'         => sprintf( esc_html__( "%s %s deleted a user: ID: %s, username: %s", 'wpdef' ), '{{blog_name}}', '{{wp_user}}', '{{user_id}}', '{{username}}' ),
				'context'      => self::CONTEXT_USERS,
				'action_type'  => self::ACTION_DELETED,
				'event_type'   => $this->type,
				'program_args' => array(
					'username' => array(
						'callable'        => 'get_user_by',
						'params'          => array(
							'id',
							'{{user_id}}'
						),
						'result_property' => 'user_login'
					),
				),
			),
			'profile_update'        => array(
				'args'        => array( 'user_id', 'old_user_data' ),
				'action_type' => self::ACTION_UPDATED,
				'event_type'  => $this->type,
				'context'     => self::CONTEXT_PROFILE,
				'callback'    => array( self::class, 'profile_update_callback' ),
			),
			'retrieve_password'     => array(
				'args'        => array( 'username' ),
				'text'        => sprintf( esc_html__( "%s Password requested to reset for user: %s", 'wpdef' ), '{{blog_name}}', '{{username}}' ),
				'action_type' => self::ACTION_LOST_PASS,
				'event_type'  => $this->type,
				'context'     => self::CONTEXT_PROFILE,
			),
			'after_password_reset'  => array(
				'args'        => array( 'user' ),
				'text'        => sprintf( esc_html__( "%s Password reset for user: %s", 'wpdef' ), '{{blog_name}}', '{{user_login}}' ),
				'event_type'  => $this->type,
				'action_type' => self::ACTION_RESET_PASS,
				'context'     => self::CONTEXT_PROFILE,
				'custom_args' => array(
					'user_login' => '{{user->user_login}}'
				)
			),
			'set_user_role'         => array(
				'args'         => array( 'user_ID', 'new_role', 'old_role' ),
				'text'         => sprintf( __( "%s %s changed user %s's role from %s to %s", 'wpdef' ), '{{blog_name}}', '{{wp_user}}', '{{username}}', '{{from_role}}', '{{new_role}}' ),
				'action_type'  => self::ACTION_UPDATED,
				'event_type'   => $this->type,
				'context'      => self::CONTEXT_PROFILE,
				'custom_args'  => array(
					'from_role' => '{{old_role->0}}',
				),
				'program_args' => array(
					'username' => array(
						'callable'        => 'get_user_by',
						'params'          => array(
							'id',
							'{{user_ID}}'
						),
						'result_property' => 'user_login'
					),
				),
				'false_when'   => array(
					array(
						'{{old_role}}',
						array(),
						'=='
					),
				),
			),
		);
	}

	/**
	 * Log when user is removed from a blog.
	 *
	 * @return bool|array
	 */
	public function remove_user_from_blog_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );
		if ( 'createuser' === $action ) {
			return false;
		}

		$args                 = func_get_args();
		$user_id              = $args[1]['user_id'];
		$blog_id              = $args[1]['blog_id'];
		$user                 = get_user_by( 'id', $user_id );
		$username             = isset( $user->user_login ) ? $user->user_login : '';
		$current_user_display = $this->get_user_display( get_current_user_id() );
		$blog_name            = is_multisite() ? '[' . get_bloginfo( 'name' ) . ']' : '';

		return array(
			sprintf(
			/* translators: */
				esc_html__( '%1$s %2$s removed a user: ID: %3$s, username: %4$s from blog %5$s', 'wpdef' ),
				$blog_name,
				$current_user_display,
				$user_id,
				$username,
				$blog_id
			),
			self::ACTION_DELETED,
		);
	}

	public function profile_update_callback() {
		$args         = func_get_args();
		$user_id      = $args[1]['user_id'];
		$current_user = get_user_by( 'id', $user_id );
		$blog_name    = is_multisite() ? '[' . get_bloginfo( 'name' ) . ']' : '';

		if ( get_current_user_id() === $user_id ) {
			return array(
				sprintf( esc_html__( "%s User %s updated his/her profile", 'wpdef' ), $blog_name, $current_user->user_nicename ),
				self::ACTION_UPDATED
			);
		} else {
			return array(
				sprintf( __( "%s %s updated user %s's profile information", 'wpdef' ), $blog_name, $this->get_user_display( get_current_user_id() ), $current_user->user_nicename ),
				self::ACTION_UPDATED
			);
		}
	}

	public function dictionary() {
		return array(
			self::ACTION_LOST_PASS  => esc_html__( "lost password", 'wpdef' ),
			self::ACTION_REGISTERED => esc_html__( "registered", 'wpdef' ),
			self::ACTION_LOGIN      => esc_html__( "login", 'wpdef' ),
			self::ACTION_LOGOUT     => esc_html__( "logout", 'wpdef' ),
			self::ACTION_RESET_PASS => esc_html__( "password reset", 'wpdef' ),
		);
	}

	public static function get_user_role( $user_id ) {
		$user = get_user_by( 'id', $user_id );

		return ucfirst( $user->roles[0] );
	}
}
