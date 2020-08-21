 /*CODIGO ADICIONADO*/
            
			$_POST['log'] = $_POST['email'];
                $_POST['pwd'] = $_POST['password'];
                $_POST['wp-submit'] = 'Acessar';
                $_POST['testcookie'] = 1;
                $secure_cookie   = '';
		$customize_login = isset( $_REQUEST['customize-login'] );

		if ( $customize_login ) {
			wp_enqueue_script( 'customize-base' );
		}

		// If the user wants SSL but the session is not SSL, force a secure cookie.
		if ( ! empty( $_POST['log'] ) && ! force_ssl_admin() ) {
			$user_name = sanitize_user( wp_unslash( $_POST['log'] ) );
			$user      = get_user_by( 'login', $user_name );

			if ( ! $user && strpos( $user_name, '@' ) ) {
				$user = get_user_by( 'email', $user_name );
			}

			if ( $user ) {
				if ( get_user_option( 'use_ssl', $user->ID ) ) {
					$secure_cookie = true;
					force_ssl_admin( true );
				}
			}
		}

		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
			// Redirect to HTTPS if user wants SSL.
			if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) ) {
				$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
			}
		} else {
			$redirect_to = admin_url();
		}

		$reauth = empty( $_REQUEST['reauth'] ) ? false : true;

		$user = wp_signon( array(), $secure_cookie );

		if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
			if ( headers_sent() ) {
				$user = new WP_Error(
					'test_cookie',
					sprintf(
						/* translators: 1: Browser cookie documentation URL, 2: Support forums URL. */
						__( '<strong>Error</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.' ),
						__( 'https://wordpress.org/support/article/cookies/' ),
						__( 'https://wordpress.org/support/forums/' )
					)
				);
			} elseif ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) {
				// If cookies are disabled, we can't log in even with a valid user and password.
				$user = new WP_Error(
					'test_cookie',
					sprintf(
						/* translators: %s: Browser cookie documentation URL. */
						__( '<strong>Error</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.' ),
						__( 'https://wordpress.org/support/article/cookies/#enable-cookies-in-your-browser' )
					)
				);
			}
		}

		$requested_redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
		/**
		 * Filters the login redirect URL.
		 *
		 * @since 3.0.0
		 *
		 * @param string           $redirect_to           The redirect destination URL.
		 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
		 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
		 */
		$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );

		if ( ! is_wp_error( $user ) && ! $reauth ) {
			if ( $interim_login ) {
				$message       = '<p class="message">' . __( 'You have logged in successfully.' ) . '</p>';
				$interim_login = 'success';
				login_header( '', $message );

				?>
				</div>
				<?php

				/** This action is documented in wp-login.php */
				do_action( 'login_footer' );

				if ( $customize_login ) {
					?>
					<script type="text/javascript">setTimeout( function(){ new wp.customize.Messenger({ url: '<?php echo wp_customize_url(); ?>', channel: 'login' }).send('login') }, 1000 );</script>
					<?php
				}

				?>
				</body></html>
				<?php

				exit;
			}

			// Check if it is time to add a redirect to the admin email confirmation screen.
			if ( is_a( $user, 'WP_User' ) && $user->exists() && $user->has_cap( 'manage_options' ) ) {
				$admin_email_lifespan = (int) get_option( 'admin_email_lifespan' );

				// If `0` (or anything "falsey" as it is cast to int) is returned, the user will not be redirected
				// to the admin email confirmation screen.
				/** This filter is documented in wp-login.php */
				$admin_email_check_interval = (int) apply_filters( 'admin_email_check_interval', 6 * MONTH_IN_SECONDS );

				if ( $admin_email_check_interval > 0 && time() > $admin_email_lifespan ) {
					$redirect_to = add_query_arg(
						array(
							'action'  => 'confirm_admin_email',
							'wp_lang' => get_user_locale( $user ),
						),
						wp_login_url( $redirect_to )
					);
				}
			}

			if ( ( empty( $redirect_to ) || 'wp-admin/' === $redirect_to || admin_url() === $redirect_to ) ) {
				// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
				if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) ) {
					$redirect_to = user_admin_url();
				} elseif ( is_multisite() && ! $user->has_cap( 'read' ) ) {
					$redirect_to = get_dashboard_url( $user->ID );
				} elseif ( ! $user->has_cap( 'edit_posts' ) ) {
					$redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();
				}

				wp_redirect( $redirect_to );
				exit;
			}

			wp_safe_redirect( $redirect_to );
			exit;
		}

                /*FIM CODIGO ADICIONADO*/
